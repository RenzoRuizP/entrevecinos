<?php
// models/UsuarioResidenciaSolicitud.php
require_once __DIR__ . '/../database/Conexion.php';

class UsuarioResidenciaSolicitud extends Conexion
{
    public function crear(int $codigoUsuario, array $data, string $rutaRelativaArchivo): int
    {
        $tipo = strtolower(trim((string)($data['tipo_conjunto'] ?? '')));
        $direccion = trim((string)($data['direccion'] ?? ''));

        $codCondominio   = $data['codigo_condominio']   ?? null;
        $codUrbanizacion = $data['codigo_urbanizacion'] ?? null;
        $codDepartamento = $data['codigo_departamento'] ?? null;

        $sql = "INSERT INTO usuario_residencia_solicitud
                (codigo_usuario, tipo_conjunto, codigo_condominio, codigo_urbanizacion, codigo_departamento, direccion, comprobante_domicilio, estado)
                VALUES
                (:u, :tipo, :cc, :cu, :cd, :dir, :file, 'pendiente')";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $st->bindValue(':cc', $codCondominio !== null && $codCondominio !== '' ? (int)$codCondominio : null, PDO::PARAM_INT);
        $st->bindValue(':cu', $codUrbanizacion !== null && $codUrbanizacion !== '' ? (int)$codUrbanizacion : null, PDO::PARAM_INT);
        $st->bindValue(':cd', $codDepartamento !== null && $codDepartamento !== '' ? (int)$codDepartamento : null, PDO::PARAM_INT);
        $st->bindValue(':dir', $direccion, PDO::PARAM_STR);
        $st->bindValue(':file', $rutaRelativaArchivo, PDO::PARAM_STR);

        $st->execute();
        return (int)$this->dblink->lastInsertId();
    }

    public function listarSoporte(array $f): array
    {
        $estado = strtolower(trim((string)($f['estado'] ?? 'pendiente')));
        if (!in_array($estado, ['pendiente','observada','aprobada','rechazada','all'], true)) $estado = 'pendiente';

        $tipo = strtolower(trim((string)($f['tipo'] ?? '')));
        if ($tipo !== '' && !in_array($tipo, ['condominio','urbanizacion'], true)) $tipo = '';

        $codigo = (int)($f['codigo'] ?? 0);
        $q = trim((string)($f['q'] ?? ''));

        $page = max(1, (int)($f['page'] ?? 1));
        $size = max(1, min(50, (int)($f['size'] ?? 10)));
        $off  = ($page - 1) * $size;

        $where = [];
        $params = [];

        if ($estado !== 'all') { $where[] = "s.estado = :estado"; $params[':estado'] = $estado; }
        if ($tipo !== '')      { $where[] = "s.tipo_conjunto = :tipo"; $params[':tipo'] = $tipo; }

        if ($tipo === 'condominio' && $codigo > 0) { $where[] = "s.codigo_condominio = :codigo"; $params[':codigo'] = $codigo; }
        if ($tipo === 'urbanizacion' && $codigo > 0) { $where[] = "s.codigo_urbanizacion = :codigo"; $params[':codigo'] = $codigo; }

        if ($q !== '') {
            $where[] = "(u.nombre LIKE :q OR u.email LIKE :q OR u.documento LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sqlCount = "SELECT COUNT(*)
                     FROM usuario_residencia_solicitud s
                     INNER JOIN usuario u ON u.codigo_usuario = s.codigo_usuario
                     {$whereSql}";

        $stC = $this->dblink->prepare($sqlCount);
        foreach ($params as $k => $v) $stC->bindValue($k, $v);
        $stC->execute();
        $total = (int)$stC->fetchColumn();

        $sql = "SELECT
                    s.codigo_solicitud,
                    s.codigo_usuario,
                    s.tipo_conjunto,
                    s.codigo_condominio,
                    s.codigo_urbanizacion,
                    s.codigo_departamento,
                    s.direccion,
                    s.comprobante_domicilio,
                    s.estado,
                    s.comentario_admin,
                    s.created_at,

                    u.nombre,
                    u.email,
                    u.telefono,
                    u.documento,

                    c.nombre_condominio,
                    ur.nombre_urbanizacion

                FROM usuario_residencia_solicitud s
                INNER JOIN usuario u ON u.codigo_usuario = s.codigo_usuario
                LEFT JOIN condominio c ON c.codigo_condominio = s.codigo_condominio
                LEFT JOIN urbanizacion ur ON ur.codigo_urbanizacion = s.codigo_urbanizacion
                {$whereSql}
                ORDER BY s.created_at DESC
                LIMIT {$size} OFFSET {$off}";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->execute();

        $items = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'ok' => true,
            'data' => $items,
            'meta' => [
                'total' => $total,
                'page'  => $page,
                'size'  => $size
            ]
        ];
    }

    public function obtenerPorId(int $codigoSolicitud): ?array
    {
        $sql = "SELECT * FROM usuario_residencia_solicitud WHERE codigo_solicitud = :id LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':id', $codigoSolicitud, PDO::PARAM_INT);
        $st->execute();
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function actualizarEstado(int $codigoSolicitud, string $estado, ?string $comentario): bool
    {
        $sql = "UPDATE usuario_residencia_solicitud
                SET estado = :e, comentario_admin = :c
                WHERE codigo_solicitud = :id";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':e', $estado, PDO::PARAM_STR);
        $st->bindValue(':c', $comentario, PDO::PARAM_STR);
        $st->bindValue(':id', $codigoSolicitud, PDO::PARAM_INT);
        return $st->execute();
    }

    /**
     * Aplica la residencia aprobada:
     * - upsert usuario_residencia
     * - si condominio: upsert usuario_departamento
     * - si urbanización: elimina usuario_departamento
     */
    public function aplicarResidenciaAprobada(array $sol): void
    {
        $codigoUsuario = (int)$sol['codigo_usuario'];
        $tipo = (string)$sol['tipo_conjunto'];
        $dir  = (string)$sol['direccion'];

        $cc = $sol['codigo_condominio'] !== null ? (int)$sol['codigo_condominio'] : null;
        $cu = $sol['codigo_urbanizacion'] !== null ? (int)$sol['codigo_urbanizacion'] : null;
        $cd = $sol['codigo_departamento'] !== null ? (int)$sol['codigo_departamento'] : null;

        // 1) Upsert usuario_residencia (1 registro vigente por usuario)
        $existsSql = "SELECT codigo_usuario_residencia FROM usuario_residencia WHERE codigo_usuario = :u LIMIT 1";
        $stE = $this->dblink->prepare($existsSql);
        $stE->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $stE->execute();
        $idUr = $stE->fetchColumn();

        if ($idUr) {
            $upd = "UPDATE usuario_residencia
                    SET tipo_conjunto = :tipo,
                        codigo_condominio = :cc,
                        codigo_urbanizacion = :cu,
                        direccion = :dir,
                        comprobante_domicilio = :comp
                    WHERE codigo_usuario = :u";
            $st = $this->dblink->prepare($upd);
        } else {
            $ins = "INSERT INTO usuario_residencia
                    (codigo_usuario, tipo_conjunto, codigo_condominio, codigo_urbanizacion, direccion, comprobante_domicilio)
                    VALUES (:u, :tipo, :cc, :cu, :dir, :comp)";
            $st = $this->dblink->prepare($ins);
        }

        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $st->bindValue(':cc', $tipo === 'condominio' ? $cc : null, PDO::PARAM_INT);
        $st->bindValue(':cu', $tipo === 'urbanizacion' ? $cu : null, PDO::PARAM_INT);
        $st->bindValue(':dir', $dir, PDO::PARAM_STR);
        $st->bindValue(':comp', (string)$sol['comprobante_domicilio'], PDO::PARAM_STR);
        $st->execute();

        // 2) usuario_departamento
        if ($tipo === 'condominio') {
            if (!$cd) throw new Exception("Solicitud aprobada inválida: falta codigo_departamento.");

            $stChk = $this->dblink->prepare("SELECT 1 FROM usuario_departamento WHERE codigo_usuario = :u LIMIT 1");
            $stChk->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
            $stChk->execute();
            $has = (bool)$stChk->fetchColumn();

            if ($has) {
                $stUp = $this->dblink->prepare("UPDATE usuario_departamento SET codigo_departamento = :d WHERE codigo_usuario = :u");
                $stUp->bindValue(':d', $cd, PDO::PARAM_INT);
                $stUp->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
                $stUp->execute();
            } else {
                $stIn = $this->dblink->prepare("INSERT INTO usuario_departamento (codigo_usuario, codigo_departamento) VALUES (:u, :d)");
                $stIn->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
                $stIn->bindValue(':d', $cd, PDO::PARAM_INT);
                $stIn->execute();
            }
        } else {
            $stDel = $this->dblink->prepare("DELETE FROM usuario_departamento WHERE codigo_usuario = :u");
            $stDel->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
            $stDel->execute();
        }
    }

    public function aprobarSolicitud(int $codigoSolicitud, ?string $comentario = null): bool
    {
        $sol = $this->obtenerPorId($codigoSolicitud);
        if (!$sol) return false;

        // Solo aprobable si está pendiente u observada (opcional)
        $estadoActual = strtolower((string)($sol['estado'] ?? 'pendiente'));
        if (!in_array($estadoActual, ['pendiente', 'observada'], true)) {
            return false;
        }

        try {
            $this->dblink->beginTransaction();

            $this->aplicarResidenciaAprobada($sol);
            $this->actualizarEstado($codigoSolicitud, 'aprobada', $comentario);

            $this->dblink->commit();
            return true;
        } catch (Throwable $e) {
            try { $this->dblink->rollBack(); } catch (Throwable $_) {}
            throw $e;
        }
    }

}
