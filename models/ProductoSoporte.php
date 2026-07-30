<?php
// models/ProductoSoporte.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class ProductoSoporte extends Conexion
{
    private const REENVIO_PREFIX = 'REENVIO_CORRECCION|';

    /**
     * Normaliza el tipo de publicación para evitar valores inválidos.
     */
    private function normalizarTipoPublicacion(?string $tipo): string
    {
        $tipo = strtolower(trim((string)$tipo));
        return in_array($tipo, ['producto', 'servicio'], true) ? $tipo : 'producto';
    }

    /**
     * Lista publicaciones para el panel de soporte.
     *
     * visible:
     * 0 = Borrador
     * 1 = Pendiente
     * 2 = Aprobada
     * 3 = Rechazada
     * 4 = Anulada
     */
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
            'anulada'   => 4,
        ];

        $where  = [];
        $params = [];

        if ($estado !== 'todas') {
            $vis = $map[$estado] ?? 1;
            $where[] = "p.visible = :visible";
            $params[':visible'] = $vis;
        }

        if ($q !== '') {
            $where[] = "(
                p.titulo LIKE :q_titulo
                OR p.descripcion LIKE :q_descripcion
                OR p.tipo_publicacion LIKE :q_tipo_publicacion
                OR u.nombre LIKE :q_usuario_nombre
                OR u.email LIKE :q_usuario_email
            )";

            $like = '%' . $q . '%';
            $params[':q_titulo'] = $like;
            $params[':q_descripcion'] = $like;
            $params[':q_tipo_publicacion'] = $like;
            $params[':q_usuario_nombre'] = $like;
            $params[':q_usuario_email'] = $like;
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM producto p
            LEFT JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            {$whereSql}
        ";

        $stTotal = $this->dblink->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stTotal->bindValue($k, $v);
        }
        $stTotal->execute();
        $total = (int)($stTotal->fetchColumn() ?: 0);

        $offset = ($page - 1) * $size;

        /*
         * Se mantiene detección de columnas para compatibilidad con instalaciones
         * que aún no tengan campos opcionales como destacado / fecha_destacado.
         */
        $cols = [];
        $stCols = $this->dblink->prepare("SHOW COLUMNS FROM producto");
        $stCols->execute();
        foreach ($stCols->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string)$c['Field'])] = true;
        }

        $selectDestacado = '';
        if (isset($cols['destacado'])) {
            $selectDestacado .= ", p.destacado";
        }
        if (isset($cols['fecha_destacado'])) {
            $selectDestacado .= ", p.fecha_destacado";
        }
        if (isset($cols['destacado_hasta'])) {
            $selectDestacado .= ", p.destacado_hasta";
        }

        $selectTipoPublicacion = isset($cols['tipo_publicacion'])
            ? "p.tipo_publicacion,"
            : "'producto' AS tipo_publicacion,";

        $sql = "
            SELECT
                p.codigo_producto,
                {$selectTipoPublicacion}
                p.titulo,
                p.descripcion,
                p.precio,
                p.estado,
                p.imagen_portada,
                p.visible,
                p.codigo_tipo,
                p.codigo_categoria,
                p.tipo_atencion_producto,
                p.requiere_preparacion
                {$selectDestacado},
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
            ORDER BY COALESCE(p.updated_at, p.created_at) DESC, p.codigo_producto DESC
            LIMIT :limit OFFSET :offset
        ";

        $st = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':limit', $size, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
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

            $r['tipo_publicacion'] = $this->normalizarTipoPublicacion($r['tipo_publicacion'] ?? 'producto');
            $r['ultima_revision'] = $ultima;
            $items[] = $r;
        }

        $counts = $this->obtenerConteosSoporte();

        return [
            'total'  => $total,
            'page'   => $page,
            'size'   => $size,
            'counts' => $counts,
            'items'  => $items,
        ];
    }

    private function obtenerConteosSoporte(): array
    {
        $counts = [
            'borradores' => 0,
            'pendientes' => 0,
            'aprobadas'  => 0,
            'rechazadas' => 0,
            'anuladas'   => 0,
            'productos'  => 0,
            'servicios'  => 0,
        ];

        try {
            $counts['borradores'] = (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 0")->fetchColumn() ?: 0);
            $counts['pendientes'] = (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 1")->fetchColumn() ?: 0);
            $counts['aprobadas']  = (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 2")->fetchColumn() ?: 0);
            $counts['rechazadas'] = (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 3")->fetchColumn() ?: 0);
            $counts['anuladas']   = (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE visible = 4")->fetchColumn() ?: 0);
            $counts['productos']  = (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE tipo_publicacion = 'producto'")->fetchColumn() ?: 0);
            $counts['servicios']  = (int)($this->dblink->query("SELECT COUNT(*) FROM producto WHERE tipo_publicacion = 'servicio'")->fetchColumn() ?: 0);
        } catch (Throwable $e) {
            error_log('[EV][ProductoSoporte][obtenerConteosSoporte] ' . $e->getMessage());
        }

        return $counts;
    }

    public function actualizarEstadoSoporte(int $codigoProducto, int $nuevoVisible): bool
    {
        $sql = "
            UPDATE producto
            SET visible = :v,
                updated_at = CURRENT_TIMESTAMP
            WHERE codigo_producto = :id
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':v', $nuevoVisible, PDO::PARAM_INT);
        $st->bindValue(':id', $codigoProducto, PDO::PARAM_INT);
        return (bool)$st->execute();
    }

    public function obtenerVisibleActual(int $codigoProducto): ?int
    {
        $sql = "SELECT visible FROM producto WHERE codigo_producto = :id LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':id', $codigoProducto, PDO::PARAM_INT);
        $st->execute();

        $v = $st->fetchColumn();
        if ($v === false) {
            return null;
        }

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
        $st->bindValue(':p',  $codigoProducto, PDO::PARAM_INT);
        $st->bindValue(':ea', $estadoAnterior, PDO::PARAM_INT);
        $st->bindValue(':en', $estadoNuevo, PDO::PARAM_INT);

        if ($comentario !== '') {
            $st->bindValue(':c', $comentario, PDO::PARAM_STR);
        } else {
            $st->bindValue(':c', null, PDO::PARAM_NULL);
        }

        $st->bindValue(':s', $codigoSoporte, PDO::PARAM_INT);
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
        $st->bindValue(':id', $codigoProducto, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function esRevisionReenvioCorreccion(?string $comentario): bool
    {
        $c = trim((string)$comentario);
        if ($c === '') {
            return false;
        }

        return str_starts_with($c, self::REENVIO_PREFIX);
    }

    public function registrarReenvioCorreccion(int $codigoProducto, int $codigoActorVecino, int $estadoAnterior, int $estadoNuevo): void
    {
        $msg = self::REENVIO_PREFIX . ' El usuario corrigió la publicación y la reenvió para revisión.';

        $this->registrarRevisionTablaExistente(
            $codigoProducto,
            $codigoActorVecino,
            $estadoAnterior,
            $estadoNuevo,
            $msg
        );
    }

    public function ultimaRevisionEsObservacionSoporte(int $codigoProducto, int $visibleActual): bool
    {
        if ($visibleActual !== 1) {
            return false;
        }

        $rev = $this->obtenerUltimaRevisionTablaExistente($codigoProducto);
        if (!$rev) {
            return false;
        }

        $comentario = trim((string)($rev['comentario'] ?? ''));
        $estadoNuevo = (int)($rev['estado_nuevo'] ?? -1);

        if ($comentario === '') {
            return false;
        }

        if ($this->esRevisionReenvioCorreccion($comentario)) {
            return false;
        }

        return ($estadoNuevo === 1);
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
        $st->bindValue(':id', $codigoProducto, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $row['tipo_publicacion'] = $this->normalizarTipoPublicacion($row['tipo_publicacion'] ?? 'producto');

        $imgs = [];

        try {
            $sqlImg = "
                SELECT
                    codigo_producto_imagen,
                    ruta,
                    es_portada,
                    orden,
                    ancho,
                    alto,
                    peso_bytes,
                    mime
                FROM producto_imagen
                WHERE codigo_producto = :id
                ORDER BY es_portada DESC, orden ASC, codigo_producto_imagen ASC
            ";

            $st2 = $this->dblink->prepare($sqlImg);
            $st2->bindValue(':id', $codigoProducto, PDO::PARAM_INT);
            $st2->execute();
            $imgs = $st2->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[EV][ProductoSoporte][obtenerDetalle][imagenes] ' . $e->getMessage());
            $imgs = [];
        }

        $row['imagenes'] = $imgs;
        $row['ultima_revision'] = $this->obtenerUltimaRevisionTablaExistente($codigoProducto);

        return $row;
    }
}
