<?php
/*
    Modelo Producto (EV)
    Estados (visible):
      0 = borrador (recién creado por vendedor; puede "Publicar")
      1 = pendiente (vendedor ya publicó; espera aprobación admin)  [y también OBSERVADO se mantiene en 1]
      2 = aprobado  (aparece en marketplace)
      3 = rechazado (no disponible / rechazado por soporte/admin)
*/

require_once __DIR__ . '/../Config/EnvConfig.php';
require_once __DIR__ . '/../database/Conexion.php';

class Producto extends Conexion
{
    private $titulo;
    private $imagen_portada;
    private $descripcion;
    private $precio;
    private $estado;

    private $visible = 0;

    private $codigo_usuario;
    private $codigo_tipo;
    private $codigo_categoria;

    // ====== SETTERS ======
    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function setImagen_portada($imagen_portada) { $this->imagen_portada = $imagen_portada; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function setPrecio($precio) { $this->precio = $precio; }
    public function setEstado($estado) { $this->estado = $estado; }
    public function setVisible($visible) { $this->visible = (int)$visible; }
    public function setCodigoUsuario($codigo_usuario) { $this->codigo_usuario = (int)$codigo_usuario; }

    public function setCodigoTipo($codigo_tipo) {
        $this->codigo_tipo = ($codigo_tipo !== null && $codigo_tipo !== '') ? (int)$codigo_tipo : null;
    }

    public function setCodigoCategoria($codigo_categoria) {
        $this->codigo_categoria = ($codigo_categoria !== null && $codigo_categoria !== '') ? (int)$codigo_categoria : null;
    }

    /* ==========================================================
       ✅ NUEVO (RAÍZ): RESIDENCIA ACTIVA DEL USUARIO
       - Tu regla: 1 residencia activa
       - Como la tabla no tiene "estado/activo", tomamos la última registrada.
    ========================================================== */
    public function obtenerResidenciaActivaUsuario(int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                tipo_conjunto,
                codigo_condominio,
                codigo_urbanizacion
            FROM usuario_residencia
            WHERE codigo_usuario = :u
            ORDER BY codigo_usuario_residencia DESC
            LIMIT 1
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindParam(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $tipo = strtolower(trim((string)($row['tipo_conjunto'] ?? '')));
        $cond = isset($row['codigo_condominio']) ? (int)$row['codigo_condominio'] : 0;
        $urb  = isset($row['codigo_urbanizacion']) ? (int)$row['codigo_urbanizacion'] : 0;

        if ($tipo !== 'condominio' && $tipo !== 'urbanizacion') return null;
        if ($tipo === 'condominio' && $cond <= 0) return null;
        if ($tipo === 'urbanizacion' && $urb <= 0) return null;

        return [
            'tipo_conjunto'      => $tipo,
            'codigo_condominio'  => $cond,
            'codigo_urbanizacion'=> $urb
        ];
    }

    /* ==========================================================
       CREAR PRODUCTO (visible=0 borrador)
    ========================================================== */
    public function crearProducto(): int
    {
        $sql = "
            INSERT INTO producto
                (titulo, imagen_portada, descripcion, estado, precio, visible, codigo_usuario, codigo_tipo, codigo_categoria)
            VALUES
                (:titulo, :imagen_portada, :descripcion, :estado, :precio, :visible, :codigo_usuario, :codigo_tipo, :codigo_categoria)
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':titulo', $this->titulo, PDO::PARAM_STR);

        if ($this->imagen_portada !== null) $stmt->bindValue(':imagen_portada', $this->imagen_portada, PDO::PARAM_STR);
        else $stmt->bindValue(':imagen_portada', null, PDO::PARAM_NULL);

        $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $this->estado, PDO::PARAM_STR);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindParam(':visible', $this->visible, PDO::PARAM_INT);
        $stmt->bindParam(':codigo_usuario', $this->codigo_usuario, PDO::PARAM_INT);

        if ($this->codigo_tipo !== null) $stmt->bindParam(':codigo_tipo', $this->codigo_tipo, PDO::PARAM_INT);
        else $stmt->bindValue(':codigo_tipo', null, PDO::PARAM_NULL);

        if ($this->codigo_categoria !== null) $stmt->bindParam(':codigo_categoria', $this->codigo_categoria, PDO::PARAM_INT);
        else $stmt->bindValue(':codigo_categoria', null, PDO::PARAM_NULL);

        $stmt->execute();
        return (int)$this->dblink->lastInsertId();
    }

    /* ==========================================================
       IMÁGENES
    ========================================================== */
    public function registrarImagen(
        int $codigoProducto,
        string $ruta,
        int $esPortada = 0,
        int $orden = 1,
        ?int $ancho = null,
        ?int $alto = null,
        ?int $pesoBytes = null,
        ?string $mime = null
    ): void
    {
        $sql = "
            INSERT INTO producto_imagen
                (codigo_producto, ruta, es_portada, orden, ancho, alto, peso_bytes, mime)
            VALUES
                (:codigo_producto, :ruta, :es_portada, :orden, :ancho, :alto, :peso_bytes, :mime)
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindParam(':ruta', $ruta, PDO::PARAM_STR);
        $stmt->bindParam(':es_portada', $esPortada, PDO::PARAM_INT);
        $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);

        if ($ancho !== null) $stmt->bindParam(':ancho', $ancho, PDO::PARAM_INT);
        else $stmt->bindValue(':ancho', null, PDO::PARAM_NULL);

        if ($alto !== null) $stmt->bindParam(':alto', $alto, PDO::PARAM_INT);
        else $stmt->bindValue(':alto', null, PDO::PARAM_NULL);

        if ($pesoBytes !== null) $stmt->bindParam(':peso_bytes', $pesoBytes, PDO::PARAM_INT);
        else $stmt->bindValue(':peso_bytes', null, PDO::PARAM_NULL);

        if ($mime !== null) $stmt->bindParam(':mime', $mime, PDO::PARAM_STR);
        else $stmt->bindValue(':mime', null, PDO::PARAM_NULL);

        $stmt->execute();
    }

    public function actualizarImagenPortada(int $codigoProducto, string $rutaPortada): void
    {
        $sql = "
            UPDATE producto
            SET imagen_portada = :ruta
            WHERE codigo_producto = :codigo_producto
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':ruta', $rutaPortada, PDO::PARAM_STR);
        $stmt->bindParam(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function obtenerImagenes(int $codigoProducto): array
    {
        $sql = "
            SELECT
                codigo_producto_imagen,
                codigo_producto,
                ruta,
                es_portada,
                orden,
                ancho,
                alto,
                peso_bytes,
                mime
            FROM producto_imagen
            WHERE codigo_producto = :p_codigo_producto
            ORDER BY es_portada DESC, orden ASC, codigo_producto_imagen ASC
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function eliminarImagenes(int $codigoProducto, array $idsEliminar): void
    {
        if (empty($idsEliminar)) return;

        $placeholders = [];
        $params = [':p_codigo_producto' => $codigoProducto];

        foreach ($idsEliminar as $idx => $id) {
            $ph = ':id' . $idx;
            $placeholders[] = $ph;
            $params[$ph] = (int)$id;
        }

        $sql = "
            DELETE FROM producto_imagen
            WHERE codigo_producto = :p_codigo_producto
              AND codigo_producto_imagen IN (" . implode(',', $placeholders) . ")
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute($params);
    }

    public function obtenerSiguienteOrdenImagen(int $codigoProducto): int
    {
        $sql = "
            SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente
            FROM producto_imagen
            WHERE codigo_producto = :p_codigo_producto
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($fila['siguiente'] ?? 1);
    }

    public function recalcularPortada(int $codigoProducto): void
    {
        $sql = "
            SELECT codigo_producto_imagen, ruta
            FROM producto_imagen
            WHERE codigo_producto = :p_codigo_producto
            ORDER BY es_portada DESC, orden ASC, codigo_producto_imagen ASC
            LIMIT 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $sqlClear = "
            UPDATE producto_imagen
            SET es_portada = 0
            WHERE codigo_producto = :p_codigo_producto
        ";
        $stmtClear = $this->dblink->prepare($sqlClear);
        $stmtClear->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmtClear->execute();

        if ($row) {
            $sqlSet = "
                UPDATE producto_imagen
                SET es_portada = 1
                WHERE codigo_producto_imagen = :p_id
            ";
            $stmtSet = $this->dblink->prepare($sqlSet);
            $stmtSet->bindParam(':p_id', $row['codigo_producto_imagen'], PDO::PARAM_INT);
            $stmtSet->execute();

            $this->actualizarImagenPortada($codigoProducto, $row['ruta']);
        } else {
            $sqlNull = "
                UPDATE producto
                SET imagen_portada = NULL
                WHERE codigo_producto = :p_codigo_producto
            ";
            $stmtNull = $this->dblink->prepare($sqlNull);
            $stmtNull->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $stmtNull->execute();
        }
    }

    /* ==========================================================
       ✅ NUEVO: helper para traer última revisión por varios IDs
       Evita N+1 en listarPorUsuario()
    ========================================================== */
    private function obtenerUltimasRevisionesPorProductos(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (!$ids) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
            SELECT pr.*
            FROM producto_revision pr
            INNER JOIN (
                SELECT codigo_producto, MAX(codigo_revision) AS max_id
                FROM producto_revision
                WHERE codigo_producto IN ($in)
                GROUP BY codigo_producto
            ) x ON x.max_id = pr.codigo_revision
        ";

        $st = $this->dblink->prepare($sql);
        foreach ($ids as $i => $id) {
            $st->bindValue($i + 1, $id, PDO::PARAM_INT);
        }
        $st->execute();

        $map = [];
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as $r) {
            $map[(int)$r['codigo_producto']] = $r;
        }
        return $map;
    }

    /* ==========================================================
       LISTADOS / DETALLE
    ========================================================== */
    public function listarPorUsuario(int $codigoUsuario): array
    {
        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.visible,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
            FROM producto p
            LEFT JOIN tipo t ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            WHERE p.codigo_usuario = :p_codigo_usuario
              AND p.visible IN (0,1,2,3)
            ORDER BY p.created_at DESC
        ";

        $sentencia = $this->dblink->prepare($sql);
        $sentencia->bindParam(':p_codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $sentencia->execute();

        $items = $sentencia->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (!$items) return [];

        $ids = array_map(fn($r) => (int)$r['codigo_producto'], $items);
        $revMap = $this->obtenerUltimasRevisionesPorProductos($ids);

        foreach ($items as &$it) {
            $id = (int)($it['codigo_producto'] ?? 0);
            $it['ultima_revision'] = $revMap[$id] ?? null;
        }
        unset($it);

        return $items;
    }

    public function obtenerPorId(int $codigoProducto, int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
            FROM producto p
            WHERE p.codigo_producto = :p_codigo_producto
              AND p.codigo_usuario  = :p_codigo_usuario
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindParam(':p_codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) return null;

        $revMap = $this->obtenerUltimasRevisionesPorProductos([(int)$codigoProducto]);
        $fila['ultima_revision'] = $revMap[(int)$codigoProducto] ?? null;

        return $fila;
    }

    public function actualizarProductoBase(int $codigoProducto, int $codigoUsuario): void
    {
        $sql = "
            UPDATE producto
            SET
                titulo           = :titulo,
                descripcion      = :descripcion,
                estado           = :estado,
                precio           = :precio,
                codigo_tipo      = :codigo_tipo,
                codigo_categoria = :codigo_categoria
            WHERE codigo_producto = :codigo_producto
              AND codigo_usuario  = :codigo_usuario
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':titulo', $this->titulo, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $this->estado, PDO::PARAM_STR);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindParam(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);

        if ($this->codigo_tipo !== null) $stmt->bindParam(':codigo_tipo', $this->codigo_tipo, PDO::PARAM_INT);
        else $stmt->bindValue(':codigo_tipo', null, PDO::PARAM_NULL);

        if ($this->codigo_categoria !== null) $stmt->bindParam(':codigo_categoria', $this->codigo_categoria, PDO::PARAM_INT);
        else $stmt->bindValue(':codigo_categoria', null, PDO::PARAM_NULL);

        $stmt->execute();
    }

    /* ==========================================================
       ESTADOS (acciones)
    ========================================================== */

    public function publicarProducto(int $codigoProducto, int $codigoUsuario): bool
    {
        $sql = "
            UPDATE producto
            SET visible = 1
            WHERE codigo_producto = :p_codigo_producto
              AND codigo_usuario  = :p_codigo_usuario
              AND visible = 0
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindParam(':p_codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function aprobarProducto(int $codigoProducto): bool
    {
        $sql = "
            UPDATE producto
            SET visible = 2
            WHERE codigo_producto = :p_codigo_producto
              AND visible = 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function anularProducto(int $codigoProducto, int $codigoUsuario): bool
    {
        $sql = "
            UPDATE producto
            SET visible = 3
            WHERE codigo_producto = :p_codigo_producto
              AND codigo_usuario  = :p_codigo_usuario
              AND visible IN (0,1,2)
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindParam(':p_codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /* ==========================================================
       MARKETPLACE (solo aprobados: visible = 2)
    ========================================================== */
    public function listarAprobadosMarketplace(): array
    {
        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
            FROM producto p
            LEFT JOIN tipo t ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            WHERE p.visible = 2
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ==========================================================
       ✅ ORIGINAL: MARKETPLACE FILTRABLE + PAGINADO (visible=2)
       (Se mantiene por compatibilidad interna)
    ========================================================== */
    public function listarMarketplaceFiltrado(?int $tipo, ?int $categoria, string $q, int $page, int $size): array
    {
        $page = max(1, (int)$page);
        $size = max(1, min(50, (int)$size));
        $off  = ($page - 1) * $size;

        $q = trim((string)$q);
        $hasQ = ($q !== '');

        $where = " WHERE p.visible = 2 ";
        $params = [];

        if ($tipo !== null && $tipo > 0) {
            $where .= " AND p.codigo_tipo = :tipo ";
            $params[':tipo'] = $tipo;
        }

        if ($categoria !== null && $categoria > 0) {
            $where .= " AND p.codigo_categoria = :cat ";
            $params[':cat'] = $categoria;
        }

        if ($hasQ) {
            $where .= " AND (p.titulo LIKE :q OR p.descripcion LIKE :q) ";
            $params[':q'] = '%' . $q . '%';
        }

        $sqlTotal = "SELECT COUNT(*) AS total FROM producto p " . $where;
        $stT = $this->dblink->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stT->bindValue($k, $v, ($k === ':tipo' || $k === ':cat') ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stT->execute();
        $total = (int)($stT->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
            FROM producto p
            LEFT JOIN tipo t ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            {$where}
            ORDER BY p.created_at DESC
            LIMIT :lim OFFSET :off
        ";

        $stmt = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, ($k === ':tipo' || $k === ':cat') ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':lim', $size, PDO::PARAM_INT);
        $stmt->bindValue(':off', $off, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => $total,
            'page'  => $page,
            'size'  => $size,
            'items' => $items
        ];
    }

    /* ==========================================================
       ✅ NUEVO (RAÍZ): MARKETPLACE FILTRADO POR RESIDENCIA
       - Visible=2
       - SOLO publicaciones de vecinos de mi mismo condominio/urbanización
       - Filtro server-side (no se puede saltar por JS)
    ========================================================== */
    public function listarMarketplaceFiltradoPorResidencia(
        int $codigoUsuarioViewer,
        ?int $tipo,
        ?int $categoria,
        string $q,
        int $page,
        int $size
    ): array
    {
        $res = $this->obtenerResidenciaActivaUsuario($codigoUsuarioViewer);
        if (!$res) {
            return ['total' => 0, 'page' => max(1, (int)$page), 'size' => max(1, min(50, (int)$size)), 'items' => []];
        }

        $tipoConjunto = (string)$res['tipo_conjunto'];
        $condId = (int)($res['codigo_condominio'] ?? 0);
        $urbId  = (int)($res['codigo_urbanizacion'] ?? 0);

        $page = max(1, (int)$page);
        $size = max(1, min(50, (int)$size));
        $off  = ($page - 1) * $size;

        $q = trim((string)$q);
        $hasQ = ($q !== '');

        // ✅ WHERE base
        $where = " WHERE p.visible = 2 ";
        $params = [];

        // ✅ Scope por residencia (EXISTS evita duplicados)
        if ($tipoConjunto === 'condominio') {
            $where .= "
              AND EXISTS (
                SELECT 1
                FROM usuario_residencia ur
                WHERE ur.codigo_usuario = p.codigo_usuario
                  AND ur.tipo_conjunto = 'condominio'
                  AND ur.codigo_condominio = :cond
              )
            ";
            $params[':cond'] = $condId;
        } else {
            $where .= "
              AND EXISTS (
                SELECT 1
                FROM usuario_residencia ur
                WHERE ur.codigo_usuario = p.codigo_usuario
                  AND ur.tipo_conjunto = 'urbanizacion'
                  AND ur.codigo_urbanizacion = :urb
              )
            ";
            $params[':urb'] = $urbId;
        }

        if ($tipo !== null && $tipo > 0) {
            $where .= " AND p.codigo_tipo = :tipo ";
            $params[':tipo'] = $tipo;
        }

        if ($categoria !== null && $categoria > 0) {
            $where .= " AND p.codigo_categoria = :cat ";
            $params[':cat'] = $categoria;
        }

        if ($hasQ) {
            $where .= " AND (p.titulo LIKE :q OR p.descripcion LIKE :q) ";
            $params[':q'] = '%' . $q . '%';
        }

        // Total
        $sqlTotal = "SELECT COUNT(*) AS total FROM producto p " . $where;
        $stT = $this->dblink->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stT->bindValue($k, $v, ($k === ':tipo' || $k === ':cat' || $k === ':cond' || $k === ':urb') ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stT->execute();
        $total = (int)($stT->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // Items
        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
            FROM producto p
            LEFT JOIN tipo t ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            {$where}
            ORDER BY p.created_at DESC
            LIMIT :lim OFFSET :off
        ";

        $stmt = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, ($k === ':tipo' || $k === ':cat' || $k === ':cond' || $k === ':urb') ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':lim', $size, PDO::PARAM_INT);
        $stmt->bindValue(':off', $off, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => $total,
            'page'  => $page,
            'size'  => $size,
            'items' => $items
        ];
    }

    /* ==========================================================
       DESTACADAS PAGADAS (solo aprobados: visible = 2)
    ========================================================== */
    public function listarDestacadasPagadas(int $limit = 12): array
    {
        $cols = [];
        $stmtCols = $this->dblink->prepare("SHOW COLUMNS FROM producto");
        $stmtCols->execute();
        foreach ($stmtCols->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string)$c['Field'])] = true;
        }

        $hasDestacadoHasta = isset($cols['destacado_hasta']);
        $hasEsDestacado    = isset($cols['es_destacado']);
        $hasDestacado      = isset($cols['destacado']);

        $where = "WHERE p.visible = 2";

        if ($hasDestacadoHasta) {
            $where .= " AND p.destacado_hasta IS NOT NULL AND p.destacado_hasta > NOW()";
        } elseif ($hasEsDestacado) {
            $where .= " AND p.es_destacado = 1";
        } elseif ($hasDestacado) {
            $where .= " AND p.destacado = 1";
        } else {
            return [];
        }

        $limit = max(1, min(50, (int)$limit));

        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.precio,
                p.imagen_portada
            FROM producto p
            {$where}
            ORDER BY p.created_at DESC
            LIMIT {$limit}
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ==========================================================
       ====== SOPORTE / ADMIN: REVISIONES ======
    ========================================================== */

    private function visibleFromEstadoString(string $estado): int
    {
        $e = strtolower(trim($estado));
        if ($e === 'borrador') return 0;
        if ($e === 'pendiente') return 1;
        if ($e === 'aprobada' || $e === 'aprobado') return 2;
        if ($e === 'rechazada' || $e === 'rechazado') return 3;
        return 1;
    }

    public function obtenerUltimaRevision(int $codigoProducto): ?array
    {
        $sql = "
            SELECT
                pr.codigo_revision,
                pr.codigo_producto,
                pr.estado_anterior,
                pr.estado_nuevo,
                pr.comentario,
                pr.codigo_soporte,
                pr.created_at
            FROM producto_revision pr
            WHERE pr.codigo_producto = :p
            ORDER BY pr.created_at DESC, pr.codigo_revision DESC
            LIMIT 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function contarPorVisibles(): array
    {
        $sql = "
            SELECT
                SUM(CASE WHEN visible = 0 THEN 1 ELSE 0 END) AS borradores,
                SUM(CASE WHEN visible = 1 THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN visible = 2 THEN 1 ELSE 0 END) AS aprobadas,
                SUM(CASE WHEN visible = 3 THEN 1 ELSE 0 END) AS rechazadas
            FROM producto
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'borradores' => (int)($row['borradores'] ?? 0),
            'pendientes' => (int)($row['pendientes'] ?? 0),
            'aprobadas'  => (int)($row['aprobadas'] ?? 0),
            'rechazadas' => (int)($row['rechazadas'] ?? 0),
        ];
    }

    public function listarSoporte(string $estado, string $q, int $page, int $size): array
    {
        $visible = $this->visibleFromEstadoString($estado);
        $page = max(1, (int)$page);
        $size = max(1, min(50, (int)$size));
        $offset = ($page - 1) * $size;

        $q = trim((string)$q);
        $hasQ = ($q !== '');

        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            WHERE p.visible = :v
        ";
        if ($hasQ) {
            $sqlTotal .= " AND (
                p.titulo LIKE :q OR p.descripcion LIKE :q OR
                u.nombre LIKE :q OR u.apellido LIKE :q OR u.email LIKE :q
            )";
        }
        $stmtT = $this->dblink->prepare($sqlTotal);
        $stmtT->bindValue(':v', $visible, PDO::PARAM_INT);
        if ($hasQ) $stmtT->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
        $stmtT->execute();
        $total = (int)($stmtT->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.visible,
                p.imagen_portada,
                p.created_at,
                p.updated_at,
                u.codigo_usuario,
                CONCAT(TRIM(COALESCE(u.nombre,'')), ' ', TRIM(COALESCE(u.apellido,''))) AS usuario_nombre,
                u.email AS usuario_email,

                pr.codigo_revision AS rev_id,
                pr.estado_anterior AS rev_estado_anterior,
                pr.estado_nuevo AS rev_estado_nuevo,
                pr.comentario AS rev_comentario,
                pr.codigo_soporte AS rev_codigo_soporte,
                pr.created_at AS rev_created_at
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario

            LEFT JOIN (
                SELECT pr1.*
                FROM producto_revision pr1
                INNER JOIN (
                    SELECT codigo_producto, MAX(codigo_revision) AS max_id
                    FROM producto_revision
                    GROUP BY codigo_producto
                ) x ON x.codigo_producto = pr1.codigo_producto AND x.max_id = pr1.codigo_revision
            ) pr ON pr.codigo_producto = p.codigo_producto

            WHERE p.visible = :v
        ";
        if ($hasQ) {
            $sql .= " AND (
                p.titulo LIKE :q OR p.descripcion LIKE :q OR
                u.nombre LIKE :q OR u.apellido LIKE :q OR u.email LIKE :q
            )";
        }
        $sql .= " ORDER BY p.updated_at DESC, p.created_at DESC LIMIT :lim OFFSET :off";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':v', $visible, PDO::PARAM_INT);
        if ($hasQ) $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
        $stmt->bindValue(':lim', $size, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $items = [];
        foreach ($rows as $r) {
            $it = [
                'codigo_producto' => (int)$r['codigo_producto'],
                'titulo'          => $r['titulo'],
                'descripcion'     => $r['descripcion'],
                'estado'          => $r['estado'],
                'precio'          => $r['precio'],
                'visible'         => (int)$r['visible'],
                'imagen_portada'  => $r['imagen_portada'],
                'created_at'      => $r['created_at'],
                'updated_at'      => $r['updated_at'],
                'usuario_nombre'  => $r['usuario_nombre'],
                'usuario_email'   => $r['usuario_email'],
            ];

            if (!empty($r['rev_id'])) {
                $it['ultima_revision'] = [
                    'codigo_revision' => (int)$r['rev_id'],
                    'estado_anterior' => (int)$r['rev_estado_anterior'],
                    'estado_nuevo'    => (int)$r['rev_estado_nuevo'],
                    'comentario'      => $r['rev_comentario'],
                    'codigo_soporte'  => (int)$r['rev_codigo_soporte'],
                    'created_at'      => $r['rev_created_at'],
                ];
            } else {
                $it['ultima_revision'] = null;
            }

            $items[] = $it;
        }

        return [
            'total' => $total,
            'page'  => $page,
            'size'  => $size,
            'items' => $items
        ];
    }

    public function obtenerDetalleSoporte(int $codigoProducto): ?array
    {
        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.visible,
                p.imagen_portada,
                p.created_at,
                p.updated_at,
                u.codigo_usuario,
                CONCAT(TRIM(COALESCE(u.nombre,'')), ' ', TRIM(COALESCE(u.apellido,''))) AS usuario_nombre,
                u.email AS usuario_email
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            WHERE p.codigo_producto = :p
            LIMIT 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $item = [
            'codigo_producto' => (int)$row['codigo_producto'],
            'titulo'          => $row['titulo'],
            'descripcion'     => $row['descripcion'],
            'estado'          => $row['estado'],
            'precio'          => $row['precio'],
            'visible'         => (int)$row['visible'],
            'imagen_portada'  => $row['imagen_portada'],
            'created_at'      => $row['created_at'],
            'updated_at'      => $row['updated_at'],
            'usuario_nombre'  => $row['usuario_nombre'],
            'usuario_email'   => $row['usuario_email'],
            'imagenes'        => $this->obtenerImagenes($codigoProducto),
            'ultima_revision' => $this->obtenerUltimaRevision($codigoProducto),
        ];

        return $item;
    }

    public function registrarRevisionSoporte(int $codigoProducto, int $estadoAnterior, int $estadoNuevo, string $comentario, int $codigoSoporte): int
    {
        $sql = "
            INSERT INTO producto_revision
                (codigo_producto, estado_anterior, estado_nuevo, comentario, codigo_soporte)
            VALUES
                (:p, :ea, :en, :c, :cs)
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindParam(':ea', $estadoAnterior, PDO::PARAM_INT);
        $stmt->bindParam(':en', $estadoNuevo, PDO::PARAM_INT);
        $stmt->bindParam(':c', $comentario, PDO::PARAM_STR);
        $stmt->bindParam(':cs', $codigoSoporte, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$this->dblink->lastInsertId();
    }

    public function actualizarVisibleSoporte(int $codigoProducto, int $visibleNuevo): bool
    {
        $sql = "UPDATE producto SET visible = :v WHERE codigo_producto = :p";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':v', $visibleNuevo, PDO::PARAM_INT);
        $stmt->bindParam(':p', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}