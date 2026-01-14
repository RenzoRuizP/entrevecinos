<?php
// models/UsuarioResidenciaSolicitud.php
// EV — Solicitudes de cambio de residencia

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class UsuarioResidenciaSolicitud extends Conexion
{
    public function crearPendiente(int $codigoUsuario, array $data, string $rutaComprobante): int
    {
        $sql = "INSERT INTO usuario_residencia_solicitud
                (codigo_usuario, tipo_conjunto, codigo_condominio, codigo_urbanizacion, direccion, comprobante_domicilio, estado)
                VALUES
                (:cu, :tipo, :cc, :cu2, :dir, :comp, 'pendiente')";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':cu', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':tipo', (string)$data['tipo_conjunto'], PDO::PARAM_STR);

        $cc = $data['codigo_condominio'] ?? null;
        $cu2 = $data['codigo_urbanizacion'] ?? null;

        $st->bindValue(':cc', $cc, $cc === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $st->bindValue(':cu2', $cu2, $cu2 === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $st->bindValue(':dir', (string)$data['direccion'], PDO::PARAM_STR);
        $st->bindValue(':comp', $rutaComprobante, PDO::PARAM_STR);

        $ok = $st->execute();
        if (!$ok) return 0;

        return (int)$this->dblink->lastInsertId();
    }

    /**
     * Si el usuario ya tiene una solicitud PENDIENTE, la actualiza.
     * Caso contrario, crea una nueva.
     */
    public function upsertPendiente(int $codigoUsuario, array $data, string $rutaComprobante): int
    {
        // Buscar pendiente existente
        $sqlFind = "SELECT codigo_solicitud
                    FROM usuario_residencia_solicitud
                    WHERE codigo_usuario = :cu AND estado = 'pendiente'
                    ORDER BY codigo_solicitud DESC
                    LIMIT 1";
        $stF = $this->dblink->prepare($sqlFind);
        $stF->bindValue(':cu', $codigoUsuario, PDO::PARAM_INT);
        $stF->execute();
        $id = (int)($stF->fetchColumn() ?: 0);

        if ($id > 0) {
            $sqlUp = "UPDATE usuario_residencia_solicitud
                      SET tipo_conjunto = :tipo,
                          codigo_condominio = :cc,
                          codigo_urbanizacion = :cu2,
                          direccion = :dir,
                          comprobante_domicilio = :comp,
                          updated_at = CURRENT_TIMESTAMP
                      WHERE codigo_solicitud = :id
                      LIMIT 1";
            $st = $this->dblink->prepare($sqlUp);
            $st->bindValue(':tipo', (string)$data['tipo_conjunto'], PDO::PARAM_STR);

            $cc = $data['codigo_condominio'] ?? null;
            $cu2 = $data['codigo_urbanizacion'] ?? null;

            $st->bindValue(':cc', $cc, $cc === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $st->bindValue(':cu2', $cu2, $cu2 === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $st->bindValue(':dir', (string)$data['direccion'], PDO::PARAM_STR);
            $st->bindValue(':comp', $rutaComprobante, PDO::PARAM_STR);
            $st->bindValue(':id', $id, PDO::PARAM_INT);

            $ok = $st->execute();
            return $ok ? $id : 0;
        }

        return $this->crearPendiente($codigoUsuario, $data, $rutaComprobante);
    }

    public function listarSoporte(array $filtros): array
    {
        $estado = strtolower(trim((string)($filtros['estado'] ?? 'pendiente')));
        $tipo   = strtolower(trim((string)($filtros['tipo'] ?? '')));
        $codigo = (int)($filtros['codigo'] ?? 0);
        $q      = trim((string)($filtros['q'] ?? ''));
        $page   = max(1, (int)($filtros['page'] ?? 1));
        $size   = max(1, min(50, (int)($filtros['size'] ?? 10)));
        $off    = ($page - 1) * $size;

        $where = [];
        $params = [];

        if ($estado !== 'all' && in_array($estado, ['pendiente','observada','aprobada','rechazada'], true)) {
            $where[] = "s.estado = :estado";
            $params[':estado'] = $estado;
        }

        if ($tipo === 'condominio' || $tipo === 'urbanizacion') {
            $where[] = "s.tipo_conjunto = :tipo";
            $params[':tipo'] = $tipo;

            if ($codigo > 0) {
                if ($tipo === 'condominio') {
                    $where[] = "s.codigo_condominio = :codigo";
                } else {
                    $where[] = "s.codigo_urbanizacion = :codigo";
                }
                $params[':codigo'] = $codigo;
            }
        }

        if ($q !== '') {
            $where[] = "(u.nombre LIKE :q OR u.email LIKE :q OR u.documento LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        // Total
        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM usuario_residencia_solicitud s
            INNER JOIN usuario u ON u.codigo_usuario = s.codigo_usuario
            LEFT JOIN condominio c ON c.codigo_condominio = s.codigo_condominio
            LEFT JOIN urbanizacion ub ON ub.codigo_urbanizacion = s.codigo_urbanizacion
            {$whereSql}
        ";

        $stT = $this->dblink->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stT->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stT->execute();
        $total = (int)($stT->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // Data
        $sql = "
            SELECT
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
                s.updated_at,

                u.nombre,
                u.email,
                u.documento,
                u.telefono,

                c.nombre_condominio,
                ub.nombre_urbanizacion

            FROM usuario_residencia_solicitud s
            INNER JOIN usuario u ON u.codigo_usuario = s.codigo_usuario
            LEFT JOIN condominio c ON c.codigo_condominio = s.codigo_condominio
            LEFT JOIN urbanizacion ub ON ub.codigo_urbanizacion = s.codigo_urbanizacion
            {$whereSql}
            ORDER BY s.created_at DESC
            LIMIT :lim OFFSET :off
        ";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':lim', $size, PDO::PARAM_INT);
        $st->bindValue(':off', $off, PDO::PARAM_INT);

        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'ok' => true,
            'data' => $rows,
            'meta' => [
                'page'  => $page,
                'size'  => $size,
                'total' => $total,
            ],
        ];
    }

    /**
     * Admin actualiza estado:
     * - observada/rechazada: solo marca estado + comentario
     * - aprobada: marca estado + comentario y APLICA a usuario_residencia (INSERT nuevo)
     */
    public function actualizarEstadoSoporte(int $codigoSolicitud, string $nuevoEstado, string $comentarioAdmin = ''): bool
    {
        $nuevoEstado = strtolower(trim($nuevoEstado));
        if (!in_array($nuevoEstado, ['pendiente','observada','aprobada','rechazada'], true)) return false;

        try {
            $this->dblink->beginTransaction();

            // Traer solicitud
            $sqlGet = "SELECT *
                       FROM usuario_residencia_solicitud
                       WHERE codigo_solicitud = :id
                       LIMIT 1";
            $stG = $this->dblink->prepare($sqlGet);
            $stG->bindValue(':id', $codigoSolicitud, PDO::PARAM_INT);
            $stG->execute();
            $s = $stG->fetch(PDO::FETCH_ASSOC);

            if (!$s) {
                $this->dblink->rollBack();
                return false;
            }

            // Update solicitud
            $sqlUp = "UPDATE usuario_residencia_solicitud
                      SET estado = :est,
                          comentario_admin = :com
                      WHERE codigo_solicitud = :id
                      LIMIT 1";
            $stU = $this->dblink->prepare($sqlUp);
            $stU->bindValue(':est', $nuevoEstado, PDO::PARAM_STR);
            $stU->bindValue(':com', $comentarioAdmin !== '' ? $comentarioAdmin : null, $comentarioAdmin !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stU->bindValue(':id', $codigoSolicitud, PDO::PARAM_INT);

            if (!$stU->execute()) {
                $this->dblink->rollBack();
                return false;
            }

            // Si aprobada: aplicar cambios a usuario_residencia
            if ($nuevoEstado === 'aprobada') {
                $codigoUsuario = (int)$s['codigo_usuario'];
                $tipo = (string)$s['tipo_conjunto'];

                $cc = $s['codigo_condominio'] !== null ? (int)$s['codigo_condominio'] : null;
                $cu = $s['codigo_urbanizacion'] !== null ? (int)$s['codigo_urbanizacion'] : null;

                $dir = (string)$s['direccion'];
                $comp = (string)$s['comprobante_domicilio'];

                // Insertar nueva residencia (historial)
                $sqlIns = "INSERT INTO usuario_residencia
                           (codigo_usuario, tipo_conjunto, codigo_condominio, codigo_urbanizacion, direccion, comprobante_domicilio)
                           VALUES
                           (:u, :t, :cc, :cu, :d, :c)";
                $stI = $this->dblink->prepare($sqlIns);
                $stI->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
                $stI->bindValue(':t', $tipo, PDO::PARAM_STR);
                $stI->bindValue(':cc', $cc, $cc === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stI->bindValue(':cu', $cu, $cu === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stI->bindValue(':d', $dir, PDO::PARAM_STR);
                $stI->bindValue(':c', $comp !== '' ? $comp : null, $comp !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);

                if (!$stI->execute()) {
                    $this->dblink->rollBack();
                    return false;
                }

                // Asegurar usuario habilitado (sin “castigarlo” por solicitar)
                $sqlUser = "UPDATE usuario SET estado = 2 WHERE codigo_usuario = :u LIMIT 1";
                $stUsr = $this->dblink->prepare($sqlUser);
                $stUsr->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
                $stUsr->execute();
            }

            $this->dblink->commit();
            return true;

        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            return false;
        }
    }
}
