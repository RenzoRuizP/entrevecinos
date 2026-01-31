<?php
// models/SoporteUsuarios.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class SoporteUsuarios extends Conexion
{
    public function listar(array $f): array
    {
        if (!method_exists($this, 'getDblink')) {
            return ['items' => [], 'total' => 0];
        }

        $db = $this->getDblink();
        if (!$db) return ['items' => [], 'total' => 0];

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

        $where  = [];
        $params = [];

        /**
         * 🔐 Consolidado de revisión (1 fila por usuario)
         * Incluye mensaje y comprobante reenviado
         */
        $joinRevision = "
            LEFT JOIN (
                SELECT
                    codigo_usuario,
                    MAX(estado_revision)     AS estado_revision,
                    MAX(mensaje_observacion) AS mensaje_observacion,
                    MAX(comprobante_path)    AS comprobante_path
                FROM usuario_revision
                GROUP BY codigo_usuario
            ) ur ON ur.codigo_usuario = u.codigo_usuario
        ";

        /**
         * 🔐 Última residencia del usuario
         */
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
            ) r ON r.codigo_usuario = u.codigo_usuario
        ";

        // =========================
        // FILTRO DE ESTADO
        // =========================
        switch ($estado) {
            case 'observado':
                $where[] = 'ur.estado_revision = 3';
                break;

            case 'revision':
                $where[] = '(
                    u.estado = 1
                    AND (ur.estado_revision IS NULL OR ur.estado_revision IN (0,1))
                )';
                break;

            case 'habilitado':
                $where[] = 'u.estado = 2';
                break;

            case 'inactivo':
                $where[] = 'u.estado = 0';
                break;

            case 'todos':
            default:
                break;
        }

        if ($q !== '') {
            $where[] = "(u.nombre LIKE :q OR u.email LIKE :q OR u.documento LIKE :q)";
            $params[':q'] = "%{$q}%";
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // =========================
        // TOTAL
        // =========================
        $stTotal = $db->prepare("
            SELECT COUNT(*)
            FROM usuario u
            {$joinRevision}
            {$joinResidencia}
            {$whereSql}
        ");
        $stTotal->execute($params);
        $total = (int)$stTotal->fetchColumn();

        // =========================
        // ITEMS
        // =========================
        $st = $db->prepare("
            SELECT
                u.codigo_usuario,
                u.nombre,
                u.email,
                u.documento,
                u.telefono,
                u.estado AS usuario_estado,

                ur.estado_revision,
                ur.mensaje_observacion,

                r.tipo_conjunto,
                r.direccion,

                COALESCE(
                    ur.comprobante_path,
                    r.comprobante_domicilio
                ) AS comprobante_domicilio

            FROM usuario u
            {$joinRevision}
            {$joinResidencia}
            {$whereSql}
            ORDER BY
                CASE
                    WHEN ur.estado_revision = 3 THEN 3
                    WHEN ur.estado_revision = 1 THEN 2
                    ELSE 1
                END DESC,
                u.codigo_usuario DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
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

        $db = $this->getDblink();
        $st = $db->prepare("
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
}
