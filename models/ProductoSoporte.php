<?php
// models/ProductoSoporte.php

require_once __DIR__ . '/../database/Conexion.php';

class ProductoSoporte extends Conexion
{
    public function listarSoporte(array $filtros): array
    {
        $estado = strtolower(trim((string)($filtros['estado'] ?? 'pendiente')));
        $q      = trim((string)($filtros['q'] ?? ''));
        $page   = max(1, (int)($filtros['page'] ?? 1));
        $size   = max(1, min(50, (int)($filtros['size'] ?? 10)));

        // visible mapping
        $map = [
            'borrador'  => 0,
            'pendiente' => 1,
            'aprobada'  => 2,
            'rechazada' => 3,
        ];

        $where = [];
        $params = [];

        if ($estado !== 'todas') {
            $vis = $map[$estado] ?? 1;
            $where[] = "p.visible = :visible";
            $params[':visible'] = $vis;
        }

        if ($q !== '') {
            $where[] = "(p.titulo LIKE :q OR p.descripcion LIKE :q OR u.nombre LIKE :q OR u.email LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Total
        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM producto p
            LEFT JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            {$whereSql}
        ";
        $stTotal = $this->dblink->prepare($sqlTotal);
        foreach ($params as $k => $v) $stTotal->bindValue($k, $v);
        $stTotal->execute();
        $total = (int)($stTotal->fetchColumn() ?: 0);

        $offset = ($page - 1) * $size;

        // Items
        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.precio,
                p.estado,
                p.imagen_portada,
                p.visible,
                p.destacado,
                p.fecha_destacado,
                p.created_at,
                p.updated_at,
                u.nombre AS usuario_nombre,
                u.email  AS usuario_email
            FROM producto p
            LEFT JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            {$whereSql}
            ORDER BY p.updated_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':limit', $size, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $st->execute();

        $items = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Contadores
        $counts = [
            'borradores' => (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 0")->fetchColumn() ?: 0),
            'pendientes' => (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 1")->fetchColumn() ?: 0),
            'aprobadas'  => (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 2")->fetchColumn() ?: 0),
            'rechazadas' => (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 3")->fetchColumn() ?: 0),
        ];

        return [
            'total'   => $total,
            'page'    => $page,
            'size'    => $size,
            'counts'  => $counts,
            'items'   => $items,
        ];
    }

    public function actualizarEstadoSoporte(int $codigoProducto, int $nuevoVisible): bool
    {
        $sql = "UPDATE producto
                SET visible = :v, updated_at = CURRENT_TIMESTAMP
                WHERE codigo_producto = :id
                LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':v', $nuevoVisible, \PDO::PARAM_INT);
        $st->bindValue(':id', $codigoProducto, \PDO::PARAM_INT);
        return (bool)$st->execute();
    }

    public function obtenerDetalle(int $codigoProducto): ?array
    {
        // Producto + usuario + imágenes
        $sql = "
            SELECT
                p.*,
                u.nombre AS usuario_nombre,
                u.email  AS usuario_email
            FROM producto p
            LEFT JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            WHERE p.codigo_producto = :id
            LIMIT 1
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':id', $codigoProducto, \PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;

        // imágenes (si tienes producto_imagen)
        $imgs = [];
        try {
            $sqlImg = "
                SELECT codigo_producto_imagen, ruta, es_portada, orden, ancho, alto, peso_bytes, mime
                FROM producto_imagen
                WHERE codigo_producto = :id
                ORDER BY es_portada DESC, orden ASC
            ";
            $st2 = $this->dblink->prepare($sqlImg);
            $st2->bindValue(':id', $codigoProducto, \PDO::PARAM_INT);
            $st2->execute();
            $imgs = $st2->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            // Si no existe tabla o algo, no rompemos
            $imgs = [];
        }

        $row['imagenes'] = $imgs;
        return $row;
    }
}
