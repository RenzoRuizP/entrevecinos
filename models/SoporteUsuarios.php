<?php
// models/SoporteUsuarios.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class SoporteUsuarios extends Conexion
{
    /**
     * Listado de usuarios para "Atender cuentas"
     */
   public function listar(array $f): array
    {
        if (!method_exists($this, 'getDblink')) {
            return ['items'=>[], 'total'=>0];
        }

        $db = $this->getDblink();
        if (!$db) {
            return ['items'=>[], 'total'=>0];
        }

        $estadoRaw = strtolower(trim((string)($f['estado'] ?? 'revision')));

        $estado = match ($estadoRaw) {
            '1', 'revision', 'en_revision' => 'revision',
            '2', 'habilitado'               => 'habilitado',
            '3', 'observado', 'observados'  => 'observado',
            '0', 'inactivo'                 => 'inactivo',
            'todos', 'all'                  => 'todos',
            default                         => 'revision',
        };


        $q      = trim((string)($f['q'] ?? ''));
        $page   = max(1, (int)($f['page'] ?? 1));
        $limit  = max(1, min((int)($f['limit'] ?? 10), 100));
        $offset = ($page - 1) * $limit;

        $where  = [];
        $params = [];

        // =========================
        // FILTRO DE ESTADO (CORRECTO)
        // =========================
        switch ($estado) {

            case 'revision':
                $where[] = 'u.estado = 1';
                $where[] = '(ur.estado_revision IS NULL OR ur.estado_revision = 1)';
                break;

            case 'observado':
                $where[] = 'u.estado = 1';
                $where[] = 'ur.estado_revision = 3';
                break;

            case 'habilitado':
                $where[] = 'u.estado = 2';
                break;

            case 'inactivo':
                $where[] = 'u.estado = 0';
                break;

            case 'todos':
            default:
                // sin filtro
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
            LEFT JOIN usuario_revision ur
                ON ur.codigo_usuario = u.codigo_usuario
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
                r.comprobante_domicilio
            FROM usuario u
            LEFT JOIN usuario_revision ur
                ON ur.codigo_usuario = u.codigo_usuario
            LEFT JOIN usuario_residencia r
                ON r.codigo_usuario = u.codigo_usuario
            {$whereSql}
            ORDER BY
                COALESCE(ur.estado_revision, 0) DESC,
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


    /**
     * Cambiar estado usuario (0,1,2)
     */
    public function actualizarEstadoUsuario(array $p): bool
    {
        $id     = (int)($p['codigo_usuario'] ?? 0);
        $estado = (int)($p['estado'] ?? -1);

        if ($id <= 0 || !in_array($estado, [0,1,2], true)) return false;

        $db = $this->getDblink();
        $st = $db->prepare("UPDATE usuario SET estado = :e WHERE codigo_usuario = :id");
        $st->execute([':e'=>$estado, ':id'=>$id]);

        return $st->rowCount() > 0;
    }
}
