<?php
// models/SoporteUsuarios.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class SoporteUsuarios extends Conexion
{
    /**
     * Lista usuarios para el módulo "Atender cuentas".
     * estado: revision|habilitado|inactivo|todos
     */
    public function listar(array $f): array
    {
        $estado = strtolower(trim((string)($f['estado'] ?? 'revision')));
        $q      = trim((string)($f['q'] ?? ''));
        $page   = max(1, (int)($f['page'] ?? 1));
        $limit  = (int)($f['limit'] ?? 10);
        $limit  = ($limit <= 0) ? 10 : min($limit, 100);
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        // Mapeo estados de UI -> tabla usuario.estado
        if ($estado !== 'todos') {
            if ($estado === 'revision')    { $where[] = "u.estado = 1"; }
            elseif ($estado === 'habilitado') { $where[] = "u.estado = 2"; }
            elseif ($estado === 'inactivo')   { $where[] = "u.estado = 0"; }
            else { $where[] = "u.estado = 1"; } // fallback
        }

        if ($q !== '') {
            $where[] = "(u.nombre LIKE :q OR u.email LIKE :q OR u.documento LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Total
        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM usuario u
            {$whereSql}
        ";
        $st = $this->dblink->prepare($sqlTotal);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->execute();
        $total = (int)$st->fetchColumn();

        // Items
        $sqlItems = "
            SELECT
                u.codigo_usuario,
                u.nombre,
                u.email,
                u.documento,
                u.telefono,
                u.estado,
                ur.tipo_conjunto,
                ur.codigo_condominio,
                ur.codigo_urbanizacion,
                ur.direccion,
                ur.comprobante_domicilio
            FROM usuario u
            LEFT JOIN usuario_residencia ur
                ON ur.codigo_usuario = u.codigo_usuario
            {$whereSql}
            ORDER BY
                u.estado DESC,
                u.fecha_actualizacion DESC,
                u.codigo_usuario DESC
            LIMIT :limit OFFSET :offset
        ";
        $st2 = $this->dblink->prepare($sqlItems);
        foreach ($params as $k => $v) $st2->bindValue($k, $v);
        $st2->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st2->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st2->execute();

        $items = $st2->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'items' => $items,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
        ];
    }

    public function actualizarEstadoUsuario(array $p): bool
    {
        $codigoUsuario = (int)($p['codigo_usuario'] ?? 0);
        $estadoNuevo   = (int)($p['estado'] ?? -1);

        if ($codigoUsuario <= 0) return false;
        if (!in_array($estadoNuevo, [0, 1, 2], true)) return false;

        $sql = "UPDATE usuario SET estado = :estado WHERE codigo_usuario = :id";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':estado', $estadoNuevo, PDO::PARAM_INT);
        $st->bindValue(':id', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        return ($st->rowCount() > 0);
    }
}
