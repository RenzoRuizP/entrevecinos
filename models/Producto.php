<?php
/*
    Modelo Producto (EV)

    Estados (visible):
      0 = borrador
      1 = pendiente
      2 = aprobado
      3 = rechazado
      4 = anulado

    ✅ REGLA MARKETPLACE:
      - Una publicación solo puede mostrarse en marketplace si:
        1) producto.visible = 2
        2) usuario.estado = 2
        3) producto.estado_residencial_publicacion = 'activa'
      - La publicación se filtra por la residencia propia con la que fue creada,
        NO por la residencia actual del usuario dueño.
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
    private $tipo_atencion_producto = 'no_requiere_preparacion';

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

    public function setTipoAtencionProducto($tipo_atencion_producto): void
    {
        $valor = strtolower(trim((string)$tipo_atencion_producto));
        $permitidos = ['requiere_preparacion', 'no_requiere_preparacion'];
        $this->tipo_atencion_producto = in_array($valor, $permitidos, true)
            ? $valor
            : 'no_requiere_preparacion';
    }

    public function setCodigoTipo($codigo_tipo) {
        $this->codigo_tipo = ($codigo_tipo !== null && $codigo_tipo !== '') ? (int)$codigo_tipo : null;
    }

    public function setCodigoCategoria($codigo_categoria) {
        $this->codigo_categoria = ($codigo_categoria !== null && $codigo_categoria !== '') ? (int)$codigo_categoria : null;
    }

    /* ==========================================================
       RESIDENCIA ACTIVA DEL USUARIO
       - Fuente de verdad: última fila de usuario_residencia
    ========================================================== */
    public function obtenerResidenciaActivaUsuario(int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                codigo_usuario_residencia,
                tipo_conjunto,
                codigo_condominio,
                codigo_urbanizacion,
                direccion,
                comprobante_domicilio,
                created_at
            FROM usuario_residencia
            WHERE codigo_usuario = :u
            ORDER BY codigo_usuario_residencia DESC
            LIMIT 1
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $tipo = strtolower(trim((string)($row['tipo_conjunto'] ?? '')));
        $cond = isset($row['codigo_condominio']) ? (int)$row['codigo_condominio'] : 0;
        $urb  = isset($row['codigo_urbanizacion']) ? (int)$row['codigo_urbanizacion'] : 0;
        $cur  = isset($row['codigo_usuario_residencia']) ? (int)$row['codigo_usuario_residencia'] : 0;

        if ($cur <= 0) return null;
        if ($tipo !== 'condominio' && $tipo !== 'urbanizacion') return null;
        if ($tipo === 'condominio' && $cond <= 0) return null;
        if ($tipo === 'urbanizacion' && $urb <= 0) return null;

        return [
            'codigo_usuario_residencia' => $cur,
            'tipo_conjunto'             => $tipo,
            'codigo_condominio'         => $cond > 0 ? $cond : null,
            'codigo_urbanizacion'       => $urb > 0 ? $urb : null,
            'direccion'                 => (string)($row['direccion'] ?? ''),
            'comprobante_domicilio'     => (string)($row['comprobante_domicilio'] ?? ''),
            'created_at'                => (string)($row['created_at'] ?? ''),
        ];
    }

    /* ==========================================================
       NOMBRE DEL CONJUNTO ACTIVO
    ========================================================== */
    public function obtenerNombreConjuntoActivoUsuario(int $codigoUsuario): ?array
    {
        $res = $this->obtenerResidenciaActivaUsuario($codigoUsuario);
        if (!$res) return null;

        $tipo = (string)$res['tipo_conjunto'];
        $cond = (int)($res['codigo_condominio'] ?? 0);
        $urb  = (int)($res['codigo_urbanizacion'] ?? 0);

        if ($tipo === 'condominio') {
            $st = $this->dblink->prepare("
                SELECT nombre_condominio AS nombre
                FROM condominio
                WHERE codigo_condominio = :id
                LIMIT 1
            ");
            $st->execute([':id' => $cond]);
            $nombre = (string)($st->fetchColumn() ?: '');
            if ($nombre === '') return null;

            return [
                'tipo_conjunto'     => 'condominio',
                'nombre'            => $nombre,
                'codigo_condominio' => $cond,
            ];
        }

        if ($tipo === 'urbanizacion') {
            $st = $this->dblink->prepare("
                SELECT nombre_urbanizacion AS nombre
                FROM urbanizacion
                WHERE codigo_urbanizacion = :id
                LIMIT 1
            ");
            $st->execute([':id' => $urb]);
            $nombre = (string)($st->fetchColumn() ?: '');
            if ($nombre === '') return null;

            return [
                'tipo_conjunto'       => 'urbanizacion',
                'nombre'              => $nombre,
                'codigo_urbanizacion' => $urb,
            ];
        }

        return null;
    }

    /* ==========================================================
       SNAPSHOT RESIDENCIAL PARA PUBLICACIÓN
       - Se usa al crear producto
    ========================================================== */
    public function obtenerSnapshotResidenciaParaPublicacion(int $codigoUsuario): ?array
    {
        $res = $this->obtenerResidenciaActivaUsuario($codigoUsuario);
        if (!$res) return null;

        return [
            'codigo_usuario_residencia'       => (int)$res['codigo_usuario_residencia'],
            'tipo_conjunto_publicacion'       => (string)$res['tipo_conjunto'],
            'codigo_condominio_publicacion'   => $res['tipo_conjunto'] === 'condominio'
                ? (int)($res['codigo_condominio'] ?? 0)
                : null,
            'codigo_urbanizacion_publicacion' => $res['tipo_conjunto'] === 'urbanizacion'
                ? (int)($res['codigo_urbanizacion'] ?? 0)
                : null,
            'estado_residencial_publicacion'  => 'activa',
        ];
    }

    /* ==========================================================
       CONDICIÓN BASE MARKETPLACE
    ========================================================== */
    private function whereMarketplaceUsuarioHabilitado(string $aliasProducto = 'p', string $aliasUsuario = 'u'): string
    {
        $aliasProducto = preg_replace('/[^a-zA-Z0-9_]/', '', $aliasProducto) ?: 'p';
        $aliasUsuario  = preg_replace('/[^a-zA-Z0-9_]/', '', $aliasUsuario) ?: 'u';

        return " {$aliasProducto}.visible = 2
                 AND {$aliasUsuario}.estado = 2
                 AND {$aliasProducto}.estado_residencial_publicacion = 'activa' ";
    }

    /* ==========================================================
       CREAR PRODUCTO (visible=0 borrador)
       ✅ Guarda snapshot residencial
    ========================================================== */
    public function crearProducto(): int
    {
        $codigoUsuario = (int)$this->codigo_usuario;
        if ($codigoUsuario <= 0) {
            throw new Exception('Usuario inválido para registrar producto.');
        }

        $snap = $this->obtenerSnapshotResidenciaParaPublicacion($codigoUsuario);
        if (!$snap) {
            throw new Exception('No se encontró una residencia activa para registrar el producto.');
        }

        $sql = "
            INSERT INTO producto
            (
                titulo,
                imagen_portada,
                descripcion,
                estado,
                precio,
                tipo_atencion_producto,
                visible,
                codigo_usuario,
                codigo_usuario_residencia,
                tipo_conjunto_publicacion,
                codigo_condominio_publicacion,
                codigo_urbanizacion_publicacion,
                estado_residencial_publicacion,
                codigo_tipo,
                codigo_categoria
            )
            VALUES
            (
                :titulo,
                :imagen_portada,
                :descripcion,
                :estado,
                :precio,
                :tipo_atencion_producto,
                :visible,
                :codigo_usuario,
                :codigo_usuario_residencia,
                :tipo_conjunto_publicacion,
                :codigo_condominio_publicacion,
                :codigo_urbanizacion_publicacion,
                :estado_residencial_publicacion,
                :codigo_tipo,
                :codigo_categoria
            )
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':titulo', $this->titulo, PDO::PARAM_STR);

        if ($this->imagen_portada !== null) {
            $stmt->bindValue(':imagen_portada', $this->imagen_portada, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':imagen_portada', null, PDO::PARAM_NULL);
        }

        $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $this->estado, PDO::PARAM_STR);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindValue(':tipo_atencion_producto', $this->tipo_atencion_producto, PDO::PARAM_STR);
        $stmt->bindParam(':visible', $this->visible, PDO::PARAM_INT);
        $stmt->bindParam(':codigo_usuario', $this->codigo_usuario, PDO::PARAM_INT);

        $stmt->bindValue(':codigo_usuario_residencia', (int)$snap['codigo_usuario_residencia'], PDO::PARAM_INT);
        $stmt->bindValue(':tipo_conjunto_publicacion', (string)$snap['tipo_conjunto_publicacion'], PDO::PARAM_STR);

        if ($snap['codigo_condominio_publicacion'] !== null) {
            $stmt->bindValue(':codigo_condominio_publicacion', (int)$snap['codigo_condominio_publicacion'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':codigo_condominio_publicacion', null, PDO::PARAM_NULL);
        }

        if ($snap['codigo_urbanizacion_publicacion'] !== null) {
            $stmt->bindValue(':codigo_urbanizacion_publicacion', (int)$snap['codigo_urbanizacion_publicacion'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':codigo_urbanizacion_publicacion', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':estado_residencial_publicacion', (string)$snap['estado_residencial_publicacion'], PDO::PARAM_STR);

        if ($this->codigo_tipo !== null) {
            $stmt->bindParam(':codigo_tipo', $this->codigo_tipo, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':codigo_tipo', null, PDO::PARAM_NULL);
        }

        if ($this->codigo_categoria !== null) {
            $stmt->bindParam(':codigo_categoria', $this->codigo_categoria, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':codigo_categoria', null, PDO::PARAM_NULL);
        }

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
       REVISIÓN
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
                p.tipo_atencion_producto,
                p.visible,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                p.codigo_usuario_residencia,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
                p.updated_at,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
            FROM producto p
            LEFT JOIN tipo t ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            WHERE p.codigo_usuario = :p_codigo_usuario
              AND p.visible IN (0,1,2,3,4)
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
                p.tipo_atencion_producto,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                p.codigo_usuario_residencia,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
                p.updated_at,
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
                titulo                  = :titulo,
                descripcion             = :descripcion,
                estado                  = :estado,
                precio                  = :precio,
                tipo_atencion_producto  = :tipo_atencion_producto,
                codigo_tipo             = :codigo_tipo,
                codigo_categoria        = :codigo_categoria
            WHERE codigo_producto = :codigo_producto
              AND codigo_usuario  = :codigo_usuario
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':titulo', $this->titulo, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $this->estado, PDO::PARAM_STR);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindValue(':tipo_atencion_producto', $this->tipo_atencion_producto, PDO::PARAM_STR);
        $stmt->bindParam(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindParam(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);

        if ($this->codigo_tipo !== null) $stmt->bindParam(':codigo_tipo', $this->codigo_tipo, PDO::PARAM_INT);
        else $stmt->bindValue(':codigo_tipo', null, PDO::PARAM_NULL);

        if ($this->codigo_categoria !== null) $stmt->bindParam(':codigo_categoria', $this->codigo_categoria, PDO::PARAM_INT);
        else $stmt->bindValue(':codigo_categoria', null, PDO::PARAM_NULL);

        $stmt->execute();
    }

    /* ==========================================================
       ESTADOS
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
            SET visible = 4
            WHERE codigo_producto = :p_codigo_producto
              AND codigo_usuario  = :p_codigo_usuario
              AND visible IN (0,1,2,3)
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindParam(':p_codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /* ==========================================================
       ✅ BLOQUEAR PUBLICACIONES ANTERIORES POR CAMBIO DE RESIDENCIA
    ========================================================== */
    public function bloquearPublicacionesPorCambioResidencia(int $codigoUsuario, int $codigoUsuarioResidenciaNueva): int
    {
        $sql = "
            UPDATE producto
            SET estado_residencial_publicacion = 'bloqueado_por_cambio',
                updated_at = CURRENT_TIMESTAMP
            WHERE codigo_usuario = :u
              AND codigo_usuario_residencia IS NOT NULL
              AND codigo_usuario_residencia <> :ur_nueva
              AND estado_residencial_publicacion = 'activa'
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':u', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':ur_nueva', $codigoUsuarioResidenciaNueva, PDO::PARAM_INT);
        $st->execute();

        return (int)$st->rowCount();
    }

    /* ==========================================================
       MARKETPLACE GENERAL
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
                p.tipo_atencion_producto,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            LEFT JOIN tipo t ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            WHERE " . $this->whereMarketplaceUsuarioHabilitado('p', 'u') . "
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarMarketplaceFiltrado(?int $tipo, ?int $categoria, string $q, int $page, int $size): array
    {
        $page = max(1, (int)$page);
        $size = max(1, min(50, (int)$size));
        $off  = ($page - 1) * $size;

        $q = trim((string)$q);
        $hasQ = ($q !== '');

        $where = " WHERE " . $this->whereMarketplaceUsuarioHabilitado('p', 'u') . " ";
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

        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            {$where}
        ";
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
                p.tipo_atencion_producto,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
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
       ✅ MARKETPLACE FILTRADO POR RESIDENCIA DEL VISOR
       - Usa snapshot de la publicación, no la residencia actual del dueño
    ========================================================== */
    public function listarMarketplaceFiltradoPorResidencia(
        int $codigoUsuarioViewer,
        ?int $tipo,
        ?int $categoria,
        string $q,
        int $page,
        int $size
    ): array {
        $res = $this->obtenerResidenciaActivaUsuario($codigoUsuarioViewer);
        if (!$res) {
            return [
                'total' => 0,
                'page'  => max(1, (int)$page),
                'size'  => max(1, min(50, (int)$size)),
                'items' => []
            ];
        }

        $tipoConjunto = (string)$res['tipo_conjunto'];
        $condId = (int)($res['codigo_condominio'] ?? 0);
        $urbId  = (int)($res['codigo_urbanizacion'] ?? 0);

        $page = max(1, (int)$page);
        $size = max(1, min(50, (int)$size));
        $off  = ($page - 1) * $size;

        $q = trim((string)$q);
        $hasQ = ($q !== '');

        $where = " WHERE " . $this->whereMarketplaceUsuarioHabilitado('p', 'u') . " ";
        $params = [];

        if ($tipoConjunto === 'condominio') {
            $where .= "
                AND p.tipo_conjunto_publicacion = 'condominio'
                AND p.codigo_condominio_publicacion = :cond
            ";
            $params[':cond'] = $condId;
        } else {
            $where .= "
                AND p.tipo_conjunto_publicacion = 'urbanizacion'
                AND p.codigo_urbanizacion_publicacion = :urb
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

        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            {$where}
        ";
        $stT = $this->dblink->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stT->bindValue(
                $k,
                $v,
                in_array($k, [':tipo', ':cat', ':cond', ':urb'], true) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
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
                p.tipo_atencion_producto,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                p.codigo_usuario_residencia,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
                COALESCE(u.disponibilidad_pedidos, 0) AS disponibilidad_pedidos_vendedor,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            LEFT JOIN tipo t ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            {$where}
            ORDER BY p.created_at DESC
            LIMIT :lim OFFSET :off
        ";

        $stmt = $this->dblink->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(
                $k,
                $v,
                in_array($k, [':tipo', ':cat', ':cond', ':urb'], true) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
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
       DESTACADAS
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

        $where = "WHERE " . $this->whereMarketplaceUsuarioHabilitado('p', 'u');

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
                p.tipo_atencion_producto,
                p.imagen_portada
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            {$where}
            ORDER BY p.created_at DESC
            LIMIT {$limit}
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ==========================================================
       SOPORTE / ADMIN
    ========================================================== */
    private function visibleFromEstadoString(string $estado): int
    {
        $e = strtolower(trim($estado));
        if ($e === 'borrador') return 0;
        if ($e === 'pendiente') return 1;
        if ($e === 'aprobada' || $e === 'aprobado') return 2;
        if ($e === 'rechazada' || $e === 'rechazado') return 3;
        if ($e === 'anulada' || $e === 'anulado') return 4;
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
                SUM(CASE WHEN visible = 3 THEN 1 ELSE 0 END) AS rechazadas,
                SUM(CASE WHEN visible = 4 THEN 1 ELSE 0 END) AS anuladas
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
            'anuladas'   => (int)($row['anuladas'] ?? 0),
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
                p.tipo_atencion_producto,
                p.visible,
                p.imagen_portada,
                p.codigo_usuario_residencia,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
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
                'codigo_producto'                => (int)$r['codigo_producto'],
                'titulo'                         => $r['titulo'],
                'descripcion'                    => $r['descripcion'],
                'estado'                         => $r['estado'],
                'precio'                         => $r['precio'],
                'tipo_atencion_producto'         => $r['tipo_atencion_producto'],
                'visible'                        => (int)$r['visible'],
                'imagen_portada'                 => $r['imagen_portada'],
                'codigo_usuario_residencia'      => $r['codigo_usuario_residencia'],
                'tipo_conjunto_publicacion'      => $r['tipo_conjunto_publicacion'],
                'codigo_condominio_publicacion'  => $r['codigo_condominio_publicacion'],
                'codigo_urbanizacion_publicacion'=> $r['codigo_urbanizacion_publicacion'],
                'estado_residencial_publicacion' => $r['estado_residencial_publicacion'],
                'created_at'                     => $r['created_at'],
                'updated_at'                     => $r['updated_at'],
                'usuario_nombre'                 => $r['usuario_nombre'],
                'usuario_email'                  => $r['usuario_email'],
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
                p.tipo_atencion_producto,
                p.visible,
                p.imagen_portada,
                p.codigo_usuario_residencia,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
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

        return [
            'codigo_producto'                => (int)$row['codigo_producto'],
            'titulo'                         => $row['titulo'],
            'descripcion'                    => $row['descripcion'],
            'estado'                         => $row['estado'],
            'precio'                         => $row['precio'],
            'tipo_atencion_producto'         => $row['tipo_atencion_producto'],
            'visible'                        => (int)$row['visible'],
            'imagen_portada'                 => $row['imagen_portada'],
            'codigo_usuario_residencia'      => $row['codigo_usuario_residencia'],
            'tipo_conjunto_publicacion'      => $row['tipo_conjunto_publicacion'],
            'codigo_condominio_publicacion'  => $row['codigo_condominio_publicacion'],
            'codigo_urbanizacion_publicacion'=> $row['codigo_urbanizacion_publicacion'],
            'estado_residencial_publicacion' => $row['estado_residencial_publicacion'],
            'created_at'                     => $row['created_at'],
            'updated_at'                     => $row['updated_at'],
            'usuario_nombre'                 => $row['usuario_nombre'],
            'usuario_email'                  => $row['usuario_email'],
            'imagenes'                       => $this->obtenerImagenes($codigoProducto),
            'ultima_revision'                => $this->obtenerUltimaRevision($codigoProducto),
        ];
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

    public function obtenerDetalleMarketplacePorResidencia(int $codigoProducto, int $codigoUsuarioViewer): ?array
    {
        $res = $this->obtenerResidenciaActivaUsuario($codigoUsuarioViewer);
        if (!$res) return null;

        $tipoConjunto = (string)$res['tipo_conjunto'];
        $condId = (int)($res['codigo_condominio'] ?? 0);
        $urbId  = (int)($res['codigo_urbanizacion'] ?? 0);

        $whereResidencia = '';
        if ($tipoConjunto === 'condominio') {
            $whereResidencia = "
                AND p.tipo_conjunto_publicacion = 'condominio'
                AND p.codigo_condominio_publicacion = :cond
            ";
        } else {
            $whereResidencia = "
                AND p.tipo_conjunto_publicacion = 'urbanizacion'
                AND p.codigo_urbanizacion_publicacion = :urb
            ";
        }

        $sql = "
            SELECT
                p.codigo_producto,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.tipo_atencion_producto,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                p.codigo_usuario_residencia,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
                COALESCE(u.disponibilidad_pedidos, 0) AS disponibilidad_pedidos_vendedor,
                p.updated_at,
                p.created_at,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                u.nombre AS vendedor_nombre,
                u.estado AS vendedor_estado
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            LEFT JOIN tipo t ON t.codigo_tipo = p.codigo_tipo
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            WHERE p.codigo_producto = :p_codigo_producto
            AND " . $this->whereMarketplaceUsuarioHabilitado('p', 'u') . "
            {$whereResidencia}
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);

        if ($tipoConjunto === 'condominio') {
            $stmt->bindValue(':cond', $condId, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':urb', $urbId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) return null;

        $fila['es_producto_propio'] = ((int)$fila['codigo_usuario'] === $codigoUsuarioViewer) ? 1 : 0;
        $fila['requiere_preparacion'] = ((string)($fila['tipo_atencion_producto'] ?? '') === 'requiere_preparacion') ? 1 : 0;

        return $fila;
    }

}