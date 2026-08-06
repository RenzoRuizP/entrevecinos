<?php
// models/SoporteUsuarios.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class SoporteUsuarios extends Conexion
{
    public function listar(array $f): array
    {
        if (!$this->dblink) {
            return ['items' => [], 'total' => 0];
        }

        $db = $this->dblink;

        $estadoRaw = strtolower(trim((string)($f['estado'] ?? 'revision')));
        $estado = match ($estadoRaw) {
            '1', 'revision', 'en_revision', 'en revisión' => 'revision',
            '2', 'habilitado', 'habilitados'              => 'habilitado',
            '3', 'observado', 'observados'                => 'observado',
            '0', 'inactivo', 'inactivos'                  => 'inactivo',
            'todos', 'all'                                => 'todos',
            default                                       => 'revision',
        };

        $q      = trim((string)($f['q'] ?? ''));
        $page   = max(1, (int)($f['page'] ?? 1));
        $limit  = max(1, min((int)($f['limit'] ?? 10), 100));
        $offset = ($page - 1) * $limit;

        $conjunto   = strtolower(trim((string)($f['conjunto'] ?? '')));
        $conjuntoId = (int)($f['conjunto_id'] ?? 0);

        $where  = [];
        $params = [];

        // Última revisión del usuario
        $joinRevision = "
            LEFT JOIN (
                SELECT
                    urx.codigo_usuario,
                    urx.estado_revision,
                    urx.mensaje_observacion,
                    urx.comprobante_path
                FROM usuario_revision urx
            ) rev ON rev.codigo_usuario = u.codigo_usuario
        ";

        // Última residencia vigente
        $joinResidencia = "
            LEFT JOIN (
                SELECT r1.*
                FROM usuario_residencia r1
                INNER JOIN (
                    SELECT codigo_usuario, MAX(codigo_usuario_residencia) AS max_id
                    FROM usuario_residencia
                    GROUP BY codigo_usuario
                ) r2
                  ON r2.codigo_usuario = r1.codigo_usuario
                 AND r2.max_id = r1.codigo_usuario_residencia
            ) rv ON rv.codigo_usuario = u.codigo_usuario
        ";

        // Última solicitud de residencia ABIERTA (pendiente u observada)
        $joinSolicitudResidencia = "
            LEFT JOIN (
                SELECT s1.*
                FROM usuario_residencia_solicitud s1
                INNER JOIN (
                    SELECT codigo_usuario, MAX(codigo_solicitud) AS max_id
                    FROM usuario_residencia_solicitud
                    WHERE estado IN ('pendiente','observada')
                    GROUP BY codigo_usuario
                ) s2
                  ON s2.codigo_usuario = s1.codigo_usuario
                 AND s2.max_id = s1.codigo_solicitud
            ) rs ON rs.codigo_usuario = u.codigo_usuario
        ";

        // Filtro estado visual
        switch ($estado) {
            case 'observado':
                $where[] = "(
                    rev.estado_revision = 3
                    OR rs.estado = 'observada'
                )";
                break;

            case 'revision':
                $where[] = "(
                    u.estado = 1
                    AND (
                        rs.estado = 'pendiente'
                        OR rev.estado_revision IS NULL
                        OR rev.estado_revision IN (0,1)
                    )
                )";
                break;

            case 'habilitado':
                $where[] = "u.estado = 2";
                break;

            case 'inactivo':
                $where[] = "(
                    u.estado = 0
                    AND (rev.estado_revision IS NULL OR rev.estado_revision <> 3)
                )";
                break;

            case 'todos':
            default:
                break;
        }

        if ($q !== '') {
            $where[] = "(u.nombre LIKE :q OR u.email LIKE :q OR u.documento LIKE :q)";
            $params[':q'] = "%{$q}%";
        }

        // Filtro conjunto: prioriza solicitud abierta; si no hay, usa vigente
        if ($conjunto === 'condominio') {
            $where[] = "(
                (rs.codigo_solicitud IS NOT NULL AND rs.tipo_conjunto = 'condominio')
                OR
                (rs.codigo_solicitud IS NULL AND rv.tipo_conjunto = 'condominio')
            )";

            if ($conjuntoId > 0) {
                $where[] = "(
                    (rs.codigo_solicitud IS NOT NULL AND rs.codigo_condominio = :conjunto_solicitud_id)
                    OR
                    (rs.codigo_solicitud IS NULL AND rv.codigo_condominio = :conjunto_vigente_id)
                )";
                $params[':conjunto_solicitud_id'] = $conjuntoId;
                $params[':conjunto_vigente_id'] = $conjuntoId;
            }
        } elseif ($conjunto === 'urbanizacion') {
            $where[] = "(
                (rs.codigo_solicitud IS NOT NULL AND rs.tipo_conjunto = 'urbanizacion')
                OR
                (rs.codigo_solicitud IS NULL AND rv.tipo_conjunto = 'urbanizacion')
            )";

            if ($conjuntoId > 0) {
                $where[] = "(
                    (rs.codigo_solicitud IS NOT NULL AND rs.codigo_urbanizacion = :conjunto_solicitud_id)
                    OR
                    (rs.codigo_solicitud IS NULL AND rv.codigo_urbanizacion = :conjunto_vigente_id)
                )";
                $params[':conjunto_solicitud_id'] = $conjuntoId;
                $params[':conjunto_vigente_id'] = $conjuntoId;
            }
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sqlTotal = "
            SELECT COUNT(*)
            FROM usuario u
            {$joinRevision}
            {$joinResidencia}
            {$joinSolicitudResidencia}
            {$whereSql}
        ";

        $stTotal = $db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stTotal->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stTotal->execute();
        $total = (int)$stTotal->fetchColumn();

        $sql = "
            SELECT
                u.codigo_usuario,
                u.nombre,
                u.email,
                u.documento,
                u.telefono,
                u.estado AS usuario_estado,

                rev.estado_revision,
                rev.mensaje_observacion,
                rev.comprobante_path,

                rv.tipo_conjunto           AS tipo_conjunto_vigente,
                rv.codigo_condominio       AS codigo_condominio_vigente,
                rv.codigo_urbanizacion     AS codigo_urbanizacion_vigente,
                rv.direccion               AS direccion_vigente,
                rv.comprobante_domicilio   AS comprobante_vigente,

                rs.codigo_solicitud,
                rs.tipo_conjunto           AS tipo_conjunto_solicitado,
                rs.codigo_condominio       AS codigo_condominio_solicitado,
                rs.codigo_urbanizacion     AS codigo_urbanizacion_solicitado,
                rs.direccion               AS direccion_solicitada,
                rs.comprobante_domicilio   AS comprobante_solicitado,
                rs.estado                  AS estado_solicitud_residencia,
                rs.comentario_admin        AS comentario_admin_solicitud,

                CASE WHEN rs.codigo_solicitud IS NOT NULL THEN 1 ELSE 0 END AS es_cambio_residencia,

                CASE
                    WHEN rs.codigo_solicitud IS NOT NULL THEN rs.tipo_conjunto
                    ELSE rv.tipo_conjunto
                END AS tipo_conjunto,

                CASE
                    WHEN rs.codigo_solicitud IS NOT NULL THEN rs.direccion
                    ELSE rv.direccion
                END AS direccion,

                CASE
                    WHEN rs.codigo_solicitud IS NOT NULL THEN rs.comprobante_domicilio
                    ELSE COALESCE(rev.comprobante_path, rv.comprobante_domicilio)
                END AS comprobante_domicilio

            FROM usuario u
            {$joinRevision}
            {$joinResidencia}
            {$joinSolicitudResidencia}
            {$whereSql}
            ORDER BY
                CASE
                    WHEN rs.estado = 'pendiente' THEN 4
                    WHEN rs.estado = 'observada' THEN 3
                    WHEN rev.estado_revision = 3 THEN 2
                    WHEN u.estado = 1 THEN 1
                    ELSE 0
                END DESC,
                u.codigo_usuario DESC
            LIMIT :limit OFFSET :offset
        ";

        $st = $db->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();

        return [
            'items' => $st->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total' => $total
        ];
    }

    public function actualizarEstadoUsuario(array $p): bool
    {
        $id     = (int)($p['codigo_usuario'] ?? 0);
        $estado = (int)($p['estado'] ?? -1);

        if ($id <= 0 || !in_array($estado, [0, 1, 2], true)) {
            return false;
        }

        if (!$this->dblink) {
            return false;
        }

        $st = $this->dblink->prepare("
            UPDATE usuario
            SET estado = :e
            WHERE codigo_usuario = :id
        ");
        $st->execute([
            ':e'  => $estado,
            ':id' => $id
        ]);

        return $st->rowCount() > 0;
    }

    public function quitarObservado(int $codigoUsuario): bool
    {
        $codigoUsuario = (int)$codigoUsuario;
        if ($codigoUsuario <= 0 || !$this->dblink) {
            return false;
        }

        try {
            $st = $this->dblink->prepare("
                UPDATE usuario_revision
                SET estado_revision = 1
                WHERE codigo_usuario = :id
                  AND estado_revision = 3
            ");
            $st->execute([':id' => $codigoUsuario]);
            return true;
        } catch (Throwable $e) {
            error_log('[EV][SoporteUsuarios::quitarObservado] ' . $e->getMessage());
            return false;
        }
    }

    public function guardarObservacionRevision(int $codigoUsuario, string $observacion): bool
    {
        $codigoUsuario = (int)$codigoUsuario;
        $observacion = trim($observacion);

        if ($codigoUsuario <= 0 || $observacion === '' || !$this->dblink) {
            return false;
        }

        try {
            $this->dblink->beginTransaction();

            $stExists = $this->dblink->prepare("
                SELECT 1
                FROM usuario_revision
                WHERE codigo_usuario = :id
                LIMIT 1
            ");
            $stExists->execute([':id' => $codigoUsuario]);
            $exists = (bool)$stExists->fetchColumn();

            if ($exists) {
                $stUp = $this->dblink->prepare("
                    UPDATE usuario_revision
                    SET mensaje_observacion = :obs
                    WHERE codigo_usuario = :id
                ");
                $stUp->execute([
                    ':obs' => $observacion,
                    ':id'  => $codigoUsuario
                ]);
            } else {
                $stIns = $this->dblink->prepare("
                    INSERT INTO usuario_revision
                        (codigo_usuario, estado_revision, mensaje_observacion)
                    VALUES
                        (:id, 1, :obs)
                ");
                $stIns->execute([
                    ':id'  => $codigoUsuario,
                    ':obs' => $observacion
                ]);
            }

            $this->dblink->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][SoporteUsuarios::guardarObservacionRevision] ' . $e->getMessage());
            return false;
        }
    }

    public function limpiarRevision(int $codigoUsuario): bool
    {
        $codigoUsuario = (int)$codigoUsuario;
        if ($codigoUsuario <= 0 || !$this->dblink) {
            return false;
        }

        try {
            $st = $this->dblink->prepare("
                UPDATE usuario_revision
                SET estado_revision = 2,
                    mensaje_observacion = NULL
                WHERE codigo_usuario = :id
            ");
            $st->execute([':id' => $codigoUsuario]);
            return true;
        } catch (Throwable $e) {
            error_log('[EV][SoporteUsuarios::limpiarRevision] ' . $e->getMessage());
            return false;
        }
    }
}