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

        $sql = "INSERT INTO usuario_residencia_solicitud
                (codigo_usuario, tipo_conjunto, codigo_condominio, codigo_urbanizacion, codigo_departamento, direccion, comprobante_domicilio, estado)
                VALUES
                (:u, :tipo, :cc, :cu, NULL, :dir, :file, 'pendiente')";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':tipo', $tipo, PDO::PARAM_STR);

        // cc
        if ($codCondominio !== null && $codCondominio !== '' && (int)$codCondominio > 0) {
            $st->bindValue(':cc', (int)$codCondominio, PDO::PARAM_INT);
        } else {
            $st->bindValue(':cc', null, PDO::PARAM_NULL);
        }

        // cu
        if ($codUrbanizacion !== null && $codUrbanizacion !== '' && (int)$codUrbanizacion > 0) {
            $st->bindValue(':cu', (int)$codUrbanizacion, PDO::PARAM_INT);
        } else {
            $st->bindValue(':cu', null, PDO::PARAM_NULL);
        }

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

        if ($tipo === 'condominio' && $codigo > 0)   { $where[] = "s.codigo_condominio = :codigo"; $params[':codigo'] = $codigo; }
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
        $st->bindValue(':c', $comentario, $comentario === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $st->bindValue(':id', $codigoSolicitud, PDO::PARAM_INT);
        return $st->execute();
    }

    /**
     * Aplica la residencia aprobada (Opción A: SIN departamento):
     * - upsert usuario_residencia
     * - NO toca usuario_departamento
     */
    public function aplicarResidenciaAprobada(array $sol): void
    {
        $codigoUsuario = (int)$sol['codigo_usuario'];
        $tipo = (string)$sol['tipo_conjunto'];
        $dir  = (string)$sol['direccion'];

        $cc = $sol['codigo_condominio'] !== null ? (int)$sol['codigo_condominio'] : null;
        $cu = $sol['codigo_urbanizacion'] !== null ? (int)$sol['codigo_urbanizacion'] : null;

        // 1) Upsert usuario_residencia (1 registro vigente por usuario)
        $existsSql = "SELECT codigo_usuario_residencia
                      FROM usuario_residencia
                      WHERE codigo_usuario = :u
                      ORDER BY codigo_usuario_residencia DESC
                      LIMIT 1";
        $stE = $this->dblink->prepare($existsSql);
        $stE->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $stE->execute();
        $idUr = $stE->fetchColumn();

        if ($idUr) {
            $sql = "UPDATE usuario_residencia
                    SET tipo_conjunto = :tipo,
                        codigo_condominio = :cc,
                        codigo_urbanizacion = :cu,
                        direccion = :dir,
                        comprobante_domicilio = :comp
                    WHERE codigo_usuario_residencia = :id";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':id', (int)$idUr, PDO::PARAM_INT);
        } else {
            $sql = "INSERT INTO usuario_residencia
                    (codigo_usuario, tipo_conjunto, codigo_condominio, codigo_urbanizacion, direccion, comprobante_domicilio)
                    VALUES (:u, :tipo, :cc, :cu, :dir, :comp)";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        }

        $st->bindValue(':tipo', $tipo, PDO::PARAM_STR);

        if ($tipo === 'condominio' && $cc) $st->bindValue(':cc', $cc, PDO::PARAM_INT);
        else $st->bindValue(':cc', null, PDO::PARAM_NULL);

        if ($tipo === 'urbanizacion' && $cu) $st->bindValue(':cu', $cu, PDO::PARAM_INT);
        else $st->bindValue(':cu', null, PDO::PARAM_NULL);

        $st->bindValue(':dir', $dir, PDO::PARAM_STR);
        $st->bindValue(':comp', (string)($sol['comprobante_domicilio'] ?? ''), PDO::PARAM_STR);
        $st->execute();

        // 2) NO usuario_departamento (Opción A)
    }

    public function aprobarSolicitud(int $codigoSolicitud, ?string $comentario = null): bool
    {
        $sol = $this->obtenerPorId($codigoSolicitud);
        if (!$sol) return false;

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
