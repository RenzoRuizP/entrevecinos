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

        $map = [
            'borrador'  => 0,
            'pendiente' => 1,
            'aprobada'  => 2,
            'rechazada' => 3,
        ];

        $where  = [];
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

        /**
         * ✅ FIX: Traer última revisión por producto en la MISMA consulta.
         * Usamos MAX(codigo_revision) por producto.
         */
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
                u.email  AS usuario_email,

                pr.codigo_revision      AS rev_codigo_revision,
                pr.estado_anterior      AS rev_estado_anterior,
                pr.estado_nuevo         AS rev_estado_nuevo,
                pr.comentario           AS rev_comentario,
                pr.codigo_soporte       AS rev_codigo_soporte,
                pr.created_at           AS rev_created_at
            FROM producto p
            LEFT JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            LEFT JOIN (
                SELECT codigo_producto, MAX(codigo_revision) AS max_rev
                FROM producto_revision
                GROUP BY codigo_producto
            ) prm ON prm.codigo_producto = p.codigo_producto
            LEFT JOIN producto_revision pr ON pr.codigo_revision = prm.max_rev
            {$whereSql}
            ORDER BY p.updated_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':limit', $size, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $items = [];

        foreach ($rows as $r) {
            $ultima = null;

            if (!empty($r['rev_codigo_revision'])) {
                $ultima = [
                    'codigo_revision' => (int)$r['rev_codigo_revision'],
                    'codigo_producto' => (int)$r['codigo_producto'],
                    'estado_anterior' => (int)($r['rev_estado_anterior'] ?? 0),
                    'estado_nuevo'    => (int)($r['rev_estado_nuevo'] ?? 0),
                    'comentario'      => $r['rev_comentario'],
                    'codigo_soporte'  => (int)($r['rev_codigo_soporte'] ?? 0),
                    'created_at'      => $r['rev_created_at'],
                ];
            }

            unset(
                $r['rev_codigo_revision'],
                $r['rev_estado_anterior'],
                $r['rev_estado_nuevo'],
                $r['rev_comentario'],
                $r['rev_codigo_soporte'],
                $r['rev_created_at']
            );

            $r['ultima_revision'] = $ultima;
            $items[] = $r;
        }

        $counts = [
            'borradores' => (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 0")->fetchColumn() ?: 0),
            'pendientes' => (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 1")->fetchColumn() ?: 0),
            'aprobadas'  => (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 2")->fetchColumn() ?: 0),
            'rechazadas' => (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 3")->fetchColumn() ?: 0),
        ];

        return [
            'total'  => $total,
            'page'   => $page,
            'size'   => $size,
            'counts' => $counts,
            'items'  => $items,
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

    public function obtenerVisibleActual(int $codigoProducto): ?int
    {
        $sql = "SELECT visible FROM producto WHERE codigo_producto = :id LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':id', $codigoProducto, \PDO::PARAM_INT);
        $st->execute();
        $v = $st->fetchColumn();
        if ($v === false) return null;
        return (int)$v;
    }

    public function registrarRevisionTablaExistente(
        int $codigoProducto,
        int $codigoSoporte,
        int $estadoAnterior,
        int $estadoNuevo,
        string $comentario
    ): void {
        $comentario = trim($comentario);
        if (mb_strlen($comentario) > 500) {
            $comentario = mb_substr($comentario, 0, 500);
        }

        $sql = "
            INSERT INTO producto_revision
                (codigo_producto, estado_anterior, estado_nuevo, comentario, codigo_soporte)
            VALUES
                (:p, :ea, :en, :c, :s)
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':p',  $codigoProducto, \PDO::PARAM_INT);
        $st->bindValue(':ea', $estadoAnterior, \PDO::PARAM_INT);
        $st->bindValue(':en', $estadoNuevo, \PDO::PARAM_INT);

        if ($comentario !== '') $st->bindValue(':c', $comentario, \PDO::PARAM_STR);
        else $st->bindValue(':c', null, \PDO::PARAM_NULL);

        $st->bindValue(':s',  $codigoSoporte, \PDO::PARAM_INT);
        $st->execute();
    }

    public function obtenerUltimaRevisionTablaExistente(int $codigoProducto): ?array
    {
        $sql = "
            SELECT
                codigo_revision,
                codigo_producto,
                estado_anterior,
                estado_nuevo,
                comentario,
                codigo_soporte,
                created_at
            FROM producto_revision
            WHERE codigo_producto = :id
            ORDER BY created_at DESC, codigo_revision DESC
            LIMIT 1
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':id', $codigoProducto, \PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function obtenerDetalle(int $codigoProducto): ?array
    {
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

        // Imágenes
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
            $imgs = [];
        }

        $row['imagenes'] = $imgs;

        // ✅ Última revisión (comentario)
        $row['ultima_revision'] = $this->obtenerUltimaRevisionTablaExistente($codigoProducto);

        return $row;
    }
}
