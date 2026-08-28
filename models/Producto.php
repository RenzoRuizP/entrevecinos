<?php
/*
    models/Producto.php — Modelo Producto/Publicación (EV)

    Estados (visible):
      0 = borrador
      1 = pendiente
      2 = aprobado
      3 = rechazado
      4 = anulado

    tipo_publicacion:
      producto = bien físico publicado por un vecino
      servicio = servicio ofrecido por un vecino

    REGLA MARKETPLACE:
      - Una publicación solo puede mostrarse en marketplace si:
        1) producto.visible = 2
        2) producto.activo_publicacion = 1
        3) usuario.estado = 2
        4) producto.estado_residencial_publicacion = 'activa'
        5) producto: usuario.disponibilidad_pedidos = 1
           servicio: permanece visible aunque el usuario esté desconectado
      - La publicación se filtra por la residencia propia con la que fue creada,
        NO por la residencia actual del usuario dueño.
*/

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class Producto extends Conexion
{
    private ?string $titulo = null;
    private ?string $imagen_portada = null;
    private ?string $descripcion = null;
    private float $precio = 0.0;
    private ?string $estado = null;
    private string $tipo_publicacion = 'producto';
    private string $tipo_atencion_producto = 'no_requiere_preparacion';
    private int $requiere_preparacion = 0;

    private int $visible = 0;

    private int $codigo_usuario = 0;
    private ?int $codigo_tipo = null;
    private ?int $codigo_categoria = null;

    // ====== SETTERS ======
    public function setTitulo($titulo): void
    {
        $this->titulo = trim((string)$titulo);
    }

    public function setImagen_portada($imagen_portada): void
    {
        $this->imagen_portada = $imagen_portada !== null ? trim((string)$imagen_portada) : null;
    }

    public function setDescripcion($descripcion): void
    {
        $this->descripcion = trim((string)$descripcion);
    }

    public function setPrecio($precio): void
    {
        $this->precio = (float)$precio;
    }

    public function setEstado($estado): void
    {
        $estado = trim((string)$estado);
        $permitidos = ['Nuevo', 'Usado', 'NoAplica'];
        $this->estado = in_array($estado, $permitidos, true) ? $estado : 'NoAplica';
    }

    public function setVisible($visible): void
    {
        $this->visible = (int)$visible;
    }

    public function setCodigoUsuario($codigo_usuario): void
    {
        $this->codigo_usuario = (int)$codigo_usuario;
    }

    public function setTipoPublicacion($tipo_publicacion): void
    {
        $valor = strtolower(trim((string)$tipo_publicacion));
        $permitidos = ['producto', 'servicio'];
        $this->tipo_publicacion = in_array($valor, $permitidos, true) ? $valor : 'producto';
    }

    public function setTipoAtencionProducto($tipo_atencion_producto): void
    {
        $valor = strtolower(trim((string)$tipo_atencion_producto));
        $permitidos = ['requiere_preparacion', 'no_requiere_preparacion'];

        $this->tipo_atencion_producto = in_array($valor, $permitidos, true)
            ? $valor
            : 'no_requiere_preparacion';

        $this->requiere_preparacion = ($this->tipo_atencion_producto === 'requiere_preparacion') ? 1 : 0;
    }

    public function setCodigoTipo($codigo_tipo): void
    {
        $this->codigo_tipo = ($codigo_tipo !== null && $codigo_tipo !== '') ? (int)$codigo_tipo : null;
    }

    public function setCodigoCategoria($codigo_categoria): void
    {
        $this->codigo_categoria = ($codigo_categoria !== null && $codigo_categoria !== '') ? (int)$codigo_categoria : null;
    }

    /**
     * Fuente de verdad para decidir si una publicación de producto requiere preparación.
     *
     * La categoría se valida contra el tipo principal seleccionado:
     *   Producto => categoria.codigo_tipo = código del tipo Producto
     *   Servicio => categoria.codigo_tipo = código del tipo Servicio
     *
     * Importante: categoria.codigo_grupo representa el agrupador del catálogo
     * (por ejemplo, Eventos y catering o Hogar y mantenimiento). No identifica
     * si la publicación es producto o servicio, por lo que no debe usarse para
     * esta validación.
     */
    public function resolverTipoAtencionPorCategoria(int $codigoCategoria, int $codigoTipo, string $tipoPublicacion = 'producto'): string
    {
        $tipoPublicacion = strtolower(trim($tipoPublicacion));

        if ($codigoCategoria <= 0 || $codigoTipo <= 0) {
            throw new InvalidArgumentException('Tipo o categoría inválida para resolver la publicación.');
        }

        $sql = "
            SELECT
                c.requiere_preparacion_default
            FROM categoria c
            WHERE c.codigo_categoria = :codigo_categoria
              AND c.codigo_tipo = :codigo_tipo
              AND c.estado = 1
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':codigo_categoria', $codigoCategoria, PDO::PARAM_INT);
        $stmt->bindValue(':codigo_tipo', $codigoTipo, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $label = ($tipoPublicacion === 'servicio') ? 'servicio' : 'producto';
            throw new InvalidArgumentException('La categoría seleccionada no corresponde a una publicación de tipo ' . $label . '.');
        }

        // Por ahora los servicios no usan preparación de producto.
        if ($tipoPublicacion === 'servicio') {
            return 'no_requiere_preparacion';
        }

        return ((int)($row['requiere_preparacion_default'] ?? 0) === 1)
            ? 'requiere_preparacion'
            : 'no_requiere_preparacion';
    }

    private function aplicarReglasPorTipoPublicacion(): void
    {
        if ($this->tipo_publicacion === 'servicio') {
            // Un servicio no tiene estado físico ni preparación de producto.
            $this->estado = 'NoAplica';
            $this->tipo_atencion_producto = 'no_requiere_preparacion';
            $this->requiere_preparacion = 0;
        } else {
            $this->requiere_preparacion = ($this->tipo_atencion_producto === 'requiere_preparacion') ? 1 : 0;
        }

        if ($this->estado === null || $this->estado === '') {
            $this->estado = 'NoAplica';
        }
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
       - Se usa al crear publicación
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
                 AND COALESCE({$aliasProducto}.activo_publicacion, 1) = 1
                 AND {$aliasUsuario}.estado = 2
                 AND {$aliasProducto}.estado_residencial_publicacion = 'activa'
                 AND (
                    {$aliasProducto}.tipo_publicacion = 'servicio'
                    OR COALESCE({$aliasUsuario}.disponibilidad_pedidos, 0) = 1
                 ) ";
    }

    /**
     * Consulta administrativa del Marketplace.
     * El administrador puede auditar publicaciones aprobadas aunque el vendedor
     * tenga desactivada temporalmente la recepción de pedidos. Esta excepción no
     * altera lo que ven los vecinos compradores.
     */
    private function whereMarketplaceAdministrativo(string $aliasProducto = 'p', string $aliasUsuario = 'u'): string
    {
        $aliasProducto = preg_replace('/[^a-zA-Z0-9_]/', '', $aliasProducto) ?: 'p';
        $aliasUsuario  = preg_replace('/[^a-zA-Z0-9_]/', '', $aliasUsuario) ?: 'u';

        return " {$aliasProducto}.visible = 2
                 AND COALESCE({$aliasProducto}.activo_publicacion, 1) = 1
                 AND {$aliasUsuario}.estado = 2
                 AND {$aliasProducto}.estado_residencial_publicacion = 'activa' ";
    }

    /* ==========================================================
       CREAR PUBLICACIÓN (visible=0 borrador)
       - Guarda snapshot residencial
    ========================================================== */
    public function crearProducto(): int
    {
        $codigoUsuario = (int)$this->codigo_usuario;
        if ($codigoUsuario <= 0) {
            throw new Exception('Usuario inválido para registrar la publicación.');
        }

        $snap = $this->obtenerSnapshotResidenciaParaPublicacion($codigoUsuario);
        if (!$snap) {
            throw new Exception('No se encontró una residencia activa para registrar la publicación.');
        }

        $this->aplicarReglasPorTipoPublicacion();

        $sql = "
            INSERT INTO producto
            (
                titulo,
                imagen_portada,
                descripcion,
                estado,
                precio,
                tipo_publicacion,
                tipo_atencion_producto,
                requiere_preparacion,
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
                :tipo_publicacion,
                :tipo_atencion_producto,
                :requiere_preparacion,
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
        $stmt->bindValue(':titulo', $this->titulo, PDO::PARAM_STR);

        if ($this->imagen_portada !== null && $this->imagen_portada !== '') {
            $stmt->bindValue(':imagen_portada', $this->imagen_portada, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':imagen_portada', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $stmt->bindValue(':precio', $this->precio);
        $stmt->bindValue(':tipo_publicacion', $this->tipo_publicacion, PDO::PARAM_STR);
        $stmt->bindValue(':tipo_atencion_producto', $this->tipo_atencion_producto, PDO::PARAM_STR);
        $stmt->bindValue(':requiere_preparacion', $this->requiere_preparacion, PDO::PARAM_INT);
        $stmt->bindValue(':visible', $this->visible, PDO::PARAM_INT);
        $stmt->bindValue(':codigo_usuario', $this->codigo_usuario, PDO::PARAM_INT);

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
            $stmt->bindValue(':codigo_tipo', $this->codigo_tipo, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':codigo_tipo', null, PDO::PARAM_NULL);
        }

        if ($this->codigo_categoria !== null) {
            $stmt->bindValue(':codigo_categoria', $this->codigo_categoria, PDO::PARAM_INT);
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
    ): void {
        $sql = "
            INSERT INTO producto_imagen
                (codigo_producto, ruta, es_portada, orden, ancho, alto, peso_bytes, mime)
            VALUES
                (:codigo_producto, :ruta, :es_portada, :orden, :ancho, :alto, :peso_bytes, :mime)
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindValue(':ruta', $ruta, PDO::PARAM_STR);
        $stmt->bindValue(':es_portada', $esPortada, PDO::PARAM_INT);
        $stmt->bindValue(':orden', $orden, PDO::PARAM_INT);

        if ($ancho !== null) $stmt->bindValue(':ancho', $ancho, PDO::PARAM_INT);
        else $stmt->bindValue(':ancho', null, PDO::PARAM_NULL);

        if ($alto !== null) $stmt->bindValue(':alto', $alto, PDO::PARAM_INT);
        else $stmt->bindValue(':alto', null, PDO::PARAM_NULL);

        if ($pesoBytes !== null) $stmt->bindValue(':peso_bytes', $pesoBytes, PDO::PARAM_INT);
        else $stmt->bindValue(':peso_bytes', null, PDO::PARAM_NULL);

        if ($mime !== null && $mime !== '') $stmt->bindValue(':mime', $mime, PDO::PARAM_STR);
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
        $stmt->bindValue(':ruta', $rutaPortada, PDO::PARAM_STR);
        $stmt->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
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
        $stmt->bindValue(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
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
        $stmt->bindValue(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
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
        $stmt->bindValue(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $sqlClear = "
            UPDATE producto_imagen
            SET es_portada = 0
            WHERE codigo_producto = :p_codigo_producto
        ";
        $stmtClear = $this->dblink->prepare($sqlClear);
        $stmtClear->bindValue(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmtClear->execute();

        if ($row) {
            $sqlSet = "
                UPDATE producto_imagen
                SET es_portada = 1
                WHERE codigo_producto_imagen = :p_id
            ";
            $stmtSet = $this->dblink->prepare($sqlSet);
            $stmtSet->bindValue(':p_id', (int)$row['codigo_producto_imagen'], PDO::PARAM_INT);
            $stmtSet->execute();

            $this->actualizarImagenPortada($codigoProducto, (string)$row['ruta']);
        } else {
            $sqlNull = "
                UPDATE producto
                SET imagen_portada = NULL
                WHERE codigo_producto = :p_codigo_producto
            ";
            $stmtNull = $this->dblink->prepare($sqlNull);
            $stmtNull->bindValue(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
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
       LISTADOS / DETALLE DEL VECINO
    ========================================================== */
    public function listarPorUsuario(int $codigoUsuario): array
    {
        $sql = "
            SELECT
                p.codigo_producto,
                p.tipo_publicacion,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.tipo_atencion_producto,
                p.visible,
                p.activo_publicacion,
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
        $sentencia->bindValue(':p_codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
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
                p.tipo_publicacion,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.tipo_atencion_producto,
                p.visible,
                p.activo_publicacion,
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
        $stmt->bindValue(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindValue(':p_codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) return null;

        $revMap = $this->obtenerUltimasRevisionesPorProductos([(int)$codigoProducto]);
        $fila['ultima_revision'] = $revMap[(int)$codigoProducto] ?? null;

        return $fila;
    }


    /**
     * Activa o desactiva una publicación ya aprobada.
     *
     * La moderación (visible = 2) se mantiene separada de la disponibilidad
     * comercial que controla el vecino. Así una publicación puede seguir
     * aprobada por Soporte y, a la vez, quedar temporalmente fuera del Marketplace.
     */
    public function actualizarActividadPublicacionVecino(
        int $codigoProducto,
        int $codigoUsuario,
        bool $activo
    ): array {
        if ($codigoProducto <= 0 || $codigoUsuario <= 0) {
            return [
                'ok' => false,
                'error' => 'PUBLICACION_INVALIDA',
                'mensaje' => 'No se pudo identificar la publicación.'
            ];
        }

        try {
            $this->dblink->beginTransaction();

            $st = $this->dblink->prepare("
                SELECT
                    codigo_producto,
                    tipo_publicacion,
                    visible,
                    activo_publicacion,
                    estado_residencial_publicacion
                FROM producto
                WHERE codigo_producto = :codigo_producto
                  AND codigo_usuario = :codigo_usuario
                LIMIT 1
                FOR UPDATE
            ");
            $st->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();

            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'PUBLICACION_NO_ENCONTRADA',
                    'mensaje' => 'No se encontró la publicación.'
                ];
            }

            if ((int)($row['visible'] ?? -1) !== 2) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'PUBLICACION_NO_APROBADA',
                    'mensaje' => 'Solo puedes activar o desactivar publicaciones aprobadas.'
                ];
            }

            if (
                $activo &&
                (string)($row['estado_residencial_publicacion'] ?? '') !== 'activa'
            ) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'PUBLICACION_RESIDENCIA_NO_ACTIVA',
                    'mensaje' => 'Esta publicación no puede activarse porque pertenece a una residencia que ya no está activa.'
                ];
            }

            $nuevo = $activo ? 1 : 0;
            $actual = (int)($row['activo_publicacion'] ?? 1);

            if ($actual !== $nuevo) {
                $up = $this->dblink->prepare("
                    UPDATE producto
                    SET activo_publicacion = :activo_publicacion,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE codigo_producto = :codigo_producto
                      AND codigo_usuario = :codigo_usuario
                      AND visible = 2
                    LIMIT 1
                ");
                $up->bindValue(':activo_publicacion', $nuevo, PDO::PARAM_INT);
                $up->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
                $up->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
                $up->execute();
            }

            $this->dblink->commit();

            return [
                'ok' => true,
                'activo_publicacion' => $nuevo,
                'tipo_publicacion' => $this->normalizarTipoPublicacionPersistida(
                    $row['tipo_publicacion'] ?? 'producto'
                ),
                'mensaje' => $nuevo === 1
                    ? 'La publicación está activa y disponible según las reglas del Marketplace.'
                    : 'La publicación quedó inactiva y ya no se mostrará en el Marketplace.'
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][Producto][actualizarActividadPublicacionVecino] ' . $e->getMessage());

            return [
                'ok' => false,
                'error' => 'ERROR_ACTUALIZAR_ACTIVIDAD_PUBLICACION',
                'mensaje' => 'No se pudo actualizar el estado activo de la publicación.'
            ];
        }
    }

    /* ==========================================================
       PILOTO DE SERVICIOS
       - Publicar servicios no tiene costo durante el piloto.
       - Cada vecino puede tener como máximo 5 servicios activos.
       - Activos = Pendiente (visible=1) o Aprobado (visible=2).
       - Borrador, Rechazado y Anulado no consumen cupo.
    ========================================================== */
    public const MAX_SERVICIOS_ACTIVOS_PILOTO = 5;

    private function normalizarTipoPublicacionPersistida($valor): string
    {
        $valor = strtolower(trim((string)$valor));
        return in_array($valor, ['producto', 'servicio'], true) ? $valor : 'producto';
    }

    private function crearResumenServiciosPiloto(int $activos): array
    {
        $maximo = self::MAX_SERVICIOS_ACTIVOS_PILOTO;
        $activos = max(0, $activos);

        return [
            'maximo'      => $maximo,
            'activos'     => $activos,
            'disponibles' => max(0, $maximo - $activos),
            'alcanzado'   => $activos >= $maximo,
            'es_gratis'   => true,
        ];
    }

    /**
     * Serializa la validación del cupo por usuario para evitar que dos
     * solicitudes concurrentes permitan registrar un sexto servicio.
     */
    private function bloquearUsuarioParaCupoServicios(int $codigoUsuario): void
    {
        $sql = "
            SELECT codigo_usuario
            FROM usuario
            WHERE codigo_usuario = :codigo_usuario
            FOR UPDATE
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        if (!$stmt->fetchColumn()) {
            throw new RuntimeException('No se encontró el usuario para validar los cupos de servicio.');
        }
    }

    private function contarServiciosActivosPilotoBloqueado(int $codigoUsuario): int
    {
        $sql = "
            SELECT codigo_producto
            FROM producto
            WHERE codigo_usuario = :codigo_usuario
              AND tipo_publicacion = 'servicio'
              AND (visible = 1 OR (visible = 2 AND COALESCE(activo_publicacion, 1) = 1))
            ORDER BY codigo_producto ASC
            FOR UPDATE
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return count($stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    /**
     * Resumen para la interfaz. No bloquea registros porque es solo lectura.
     */
    public function obtenerResumenServiciosPiloto(int $codigoUsuario): array
    {
        if ($codigoUsuario <= 0) {
            return $this->crearResumenServiciosPiloto(0);
        }

        $sql = "
            SELECT COUNT(*)
            FROM producto
            WHERE codigo_usuario = :codigo_usuario
              AND tipo_publicacion = 'servicio'
              AND (visible = 1 OR (visible = 2 AND COALESCE(activo_publicacion, 1) = 1))
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $this->crearResumenServiciosPiloto((int)$stmt->fetchColumn());
    }

    /**
     * Actualiza una publicación y bloquea el cambio producto -> servicio
     * cuando la publicación ya está activa y el vecino alcanzó su cupo.
     */
    public function actualizarProductoBase(int $codigoProducto, int $codigoUsuario): void
    {
        $this->aplicarReglasPorTipoPublicacion();

        $abrioTransaccion = false;

        try {
            if (!$this->dblink->inTransaction()) {
                $this->dblink->beginTransaction();
                $abrioTransaccion = true;
            }

            $sqlActual = "
                SELECT tipo_publicacion, visible
                FROM producto
                WHERE codigo_producto = :codigo_producto
                  AND codigo_usuario = :codigo_usuario
                FOR UPDATE
            ";

            $stmtActual = $this->dblink->prepare($sqlActual);
            $stmtActual->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $stmtActual->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $stmtActual->execute();
            $actual = $stmtActual->fetch(PDO::FETCH_ASSOC);

            if (!$actual) {
                throw new RuntimeException('La publicación no existe o no pertenece al usuario.');
            }

            $tipoAnterior = $this->normalizarTipoPublicacionPersistida($actual['tipo_publicacion'] ?? 'producto');
            $visibleActual = (int)($actual['visible'] ?? -1);
            $convierteServicioActivo = (
                $this->tipo_publicacion === 'servicio'
                && $tipoAnterior !== 'servicio'
                && in_array($visibleActual, [1, 2], true)
            );

            if ($convierteServicioActivo) {
                $this->bloquearUsuarioParaCupoServicios($codigoUsuario);
                $serviciosActivos = $this->contarServiciosActivosPilotoBloqueado($codigoUsuario);

                if ($serviciosActivos >= self::MAX_SERVICIOS_ACTIVOS_PILOTO) {
                    throw new DomainException('LIMITE_SERVICIOS_ALCANZADO');
                }
            }

            $sql = "
                UPDATE producto
                SET
                    tipo_publicacion        = :tipo_publicacion,
                    titulo                  = :titulo,
                    descripcion             = :descripcion,
                    estado                  = :estado,
                    precio                  = :precio,
                    tipo_atencion_producto  = :tipo_atencion_producto,
                    requiere_preparacion    = :requiere_preparacion,
                    codigo_tipo             = :codigo_tipo,
                    codigo_categoria        = :codigo_categoria,
                    updated_at              = CURRENT_TIMESTAMP
                WHERE codigo_producto = :codigo_producto
                  AND codigo_usuario  = :codigo_usuario
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindValue(':tipo_publicacion', $this->tipo_publicacion, PDO::PARAM_STR);
            $stmt->bindValue(':titulo', $this->titulo, PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $stmt->bindValue(':estado', $this->estado, PDO::PARAM_STR);
            $stmt->bindValue(':precio', $this->precio);
            $stmt->bindValue(':tipo_atencion_producto', $this->tipo_atencion_producto, PDO::PARAM_STR);
            $stmt->bindValue(':requiere_preparacion', $this->requiere_preparacion, PDO::PARAM_INT);
            $stmt->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $stmt->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);

            if ($this->codigo_tipo !== null) $stmt->bindValue(':codigo_tipo', $this->codigo_tipo, PDO::PARAM_INT);
            else $stmt->bindValue(':codigo_tipo', null, PDO::PARAM_NULL);

            if ($this->codigo_categoria !== null) $stmt->bindValue(':codigo_categoria', $this->codigo_categoria, PDO::PARAM_INT);
            else $stmt->bindValue(':codigo_categoria', null, PDO::PARAM_NULL);

            $stmt->execute();

            if ($abrioTransaccion && $this->dblink->inTransaction()) {
                $this->dblink->commit();
            }
        } catch (Throwable $e) {
            if ($abrioTransaccion && $this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Compatibilidad interna: ahora aplica la misma regla del piloto.
     */
    public function publicarProducto(int $codigoProducto, int $codigoUsuario): bool
    {
        $resultado = $this->publicarConReglaPilotoServicios($codigoProducto, $codigoUsuario);
        return (bool)($resultado['ok'] ?? false);
    }

    /**
     * Envía un borrador a revisión. Los servicios son gratuitos, pero se
     * reservan dentro del máximo de cinco cuando pasan a Pendiente.
     */
    public function publicarConReglaPilotoServicios(int $codigoProducto, int $codigoUsuario): array
    {
        if ($codigoProducto <= 0 || $codigoUsuario <= 0) {
            return [
                'ok'      => false,
                'codigo'  => 'PARAMETROS_INVALIDOS',
                'mensaje' => 'No se pudo identificar la publicación o el usuario.',
            ];
        }

        $abrioTransaccion = false;

        try {
            if (!$this->dblink->inTransaction()) {
                $this->dblink->beginTransaction();
                $abrioTransaccion = true;
            }

            $sqlProducto = "
                SELECT codigo_producto, codigo_usuario, tipo_publicacion, visible
                FROM producto
                WHERE codigo_producto = :codigo_producto
                  AND codigo_usuario = :codigo_usuario
                FOR UPDATE
            ";

            $stmtProducto = $this->dblink->prepare($sqlProducto);
            $stmtProducto->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $stmtProducto->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $stmtProducto->execute();
            $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                if ($abrioTransaccion && $this->dblink->inTransaction()) $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'codigo'  => 'PUBLICACION_NO_ENCONTRADA',
                    'mensaje' => 'Publicación no encontrada para este usuario.',
                ];
            }

            $visibleActual = (int)($producto['visible'] ?? -1);
            $tipoPublicacion = $this->normalizarTipoPublicacionPersistida($producto['tipo_publicacion'] ?? 'producto');

            if ($visibleActual !== 0) {
                if ($abrioTransaccion && $this->dblink->inTransaction()) $this->dblink->rollBack();
                return [
                    'ok'               => false,
                    'codigo'           => 'ESTADO_NO_PUBLICABLE',
                    'mensaje'          => 'La publicación no está en estado borrador.',
                    'visible_actual'   => $visibleActual,
                    'tipo_publicacion' => $tipoPublicacion,
                ];
            }

            $resumenServicios = null;

            if ($tipoPublicacion === 'servicio') {
                $this->bloquearUsuarioParaCupoServicios($codigoUsuario);
                $serviciosActivos = $this->contarServiciosActivosPilotoBloqueado($codigoUsuario);
                $resumenServicios = $this->crearResumenServiciosPiloto($serviciosActivos);

                if ($serviciosActivos >= self::MAX_SERVICIOS_ACTIVOS_PILOTO) {
                    if ($abrioTransaccion && $this->dblink->inTransaction()) $this->dblink->rollBack();

                    return [
                        'ok'                => false,
                        'codigo'            => 'LIMITE_SERVICIOS_ALCANZADO',
                        'mensaje'           => 'Ya tienes 5 servicios activos o en revisión. Anula uno de ellos para liberar un cupo antes de enviar otro servicio.',
                        'tipo_publicacion'  => 'servicio',
                        'servicios_piloto'  => $resumenServicios,
                    ];
                }
            }

            $sqlUpdate = "
                UPDATE producto
                SET visible = 1,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_producto = :codigo_producto
                  AND codigo_usuario = :codigo_usuario
                  AND visible = 0
            ";
            $stmtUpdate = $this->dblink->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $stmtUpdate->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $stmtUpdate->execute();

            if ($stmtUpdate->rowCount() <= 0) {
                if ($abrioTransaccion && $this->dblink->inTransaction()) $this->dblink->rollBack();
                return [
                    'ok'      => false,
                    'codigo'  => 'NO_SE_PUDO_PUBLICAR',
                    'mensaje' => 'No se pudo enviar la publicación a revisión. Verifica que esté en borrador.',
                ];
            }

            if ($tipoPublicacion === 'servicio') {
                $resumenServicios = $this->crearResumenServiciosPiloto(
                    (int)($resumenServicios['activos'] ?? 0) + 1
                );
            }

            if ($abrioTransaccion && $this->dblink->inTransaction()) {
                $this->dblink->commit();
            }

            return [
                'ok'               => true,
                'codigo'           => 'PUBLICACION_ENVIADA_REVISION',
                'mensaje'          => 'Publicación enviada a revisión. Ahora está en estado Pendiente.',
                'tipo_publicacion' => $tipoPublicacion,
                'visible'          => 1,
                'servicios_piloto' => $resumenServicios,
            ];
        } catch (Throwable $e) {
            if ($abrioTransaccion && $this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    public function aprobarProducto(int $codigoProducto): bool
    {
        $sql = "
            UPDATE producto
            SET visible = 2,
                updated_at = CURRENT_TIMESTAMP
            WHERE codigo_producto = :p_codigo_producto
              AND visible = 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function anularProducto(int $codigoProducto, int $codigoUsuario): bool
    {
        $sql = "
            UPDATE producto
            SET visible = 4,
                updated_at = CURRENT_TIMESTAMP
            WHERE codigo_producto = :p_codigo_producto
              AND codigo_usuario  = :p_codigo_usuario
              AND visible IN (0,1,2,3)
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':p_codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindValue(':p_codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /* ==========================================================
       BLOQUEAR PUBLICACIONES ANTERIORES POR CAMBIO DE RESIDENCIA
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
                p.tipo_publicacion,
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

    public function listarMarketplaceFiltrado(?int $tipo, ?int $categoria, string $q, int $page, int $size, ?string $tipoPublicacion = null): array
    {
        $page = max(1, (int)$page);
        $size = max(1, min(50, (int)$size));
        $off  = ($page - 1) * $size;

        $q = trim((string)$q);
        $hasQ = ($q !== '');
        $tipoPublicacion = $this->normalizarTipoPublicacionFiltro($tipoPublicacion);

        $where = " WHERE " . $this->whereMarketplaceUsuarioHabilitado('p', 'u') . " ";
        $params = [];

        if ($tipoPublicacion !== null) {
            $where .= " AND p.tipo_publicacion = :tipo_publicacion ";
            $params[':tipo_publicacion'] = $tipoPublicacion;
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
            $where .= " AND (p.titulo LIKE :q_titulo OR p.descripcion LIKE :q_descripcion) ";
            $params[':q_titulo'] = '%' . $q . '%';
            $params[':q_descripcion'] = '%' . $q . '%';
        }

        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            {$where}
        ";
        $stT = $this->dblink->prepare($sqlTotal);
        $this->bindMarketplaceParams($stT, $params);
        $stT->execute();
        $total = (int)($stT->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $sql = "
            SELECT
                p.codigo_producto,
                p.tipo_publicacion,
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
        $this->bindMarketplaceParams($stmt, $params);
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
       MARKETPLACE FILTRADO POR RESIDENCIA DEL VISOR
       - Usa snapshot de la publicación, no la residencia actual del dueño
    ========================================================== */
    public function listarMarketplaceFiltradoPorResidencia(
        int $codigoUsuarioViewer,
        ?int $tipo,
        ?int $categoria,
        string $q,
        int $page,
        int $size,
        ?string $tipoPublicacion = null
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
        $tipoPublicacion = $this->normalizarTipoPublicacionFiltro($tipoPublicacion);

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

        if ($tipoPublicacion !== null) {
            $where .= " AND p.tipo_publicacion = :tipo_publicacion ";
            $params[':tipo_publicacion'] = $tipoPublicacion;
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
            $where .= " AND (p.titulo LIKE :q_titulo OR p.descripcion LIKE :q_descripcion) ";
            $params[':q_titulo'] = '%' . $q . '%';
            $params[':q_descripcion'] = '%' . $q . '%';
        }

        $sqlTotal = "
            SELECT COUNT(*) AS total
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            {$where}
        ";
        $stT = $this->dblink->prepare($sqlTotal);
        $this->bindMarketplaceParams($stT, $params);
        $stT->execute();
        $total = (int)($stT->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $sql = "
            SELECT
                p.codigo_producto,
                p.tipo_publicacion,
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
        $this->bindMarketplaceParams($stmt, $params);
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

    public function listarComunidadesActivasMarketplace(): array
    {
        $sql = "SELECT * FROM (
            SELECT 'condominio' tipo_conjunto,c.codigo_condominio codigo_comunidad,c.nombre_condominio nombre,
                   d.nombre_distrito,pr.nombre_provincia,dep.nombre_departamento
            FROM condominio c LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=c.codigo_distrito
            LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia
            LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento WHERE c.estado='A'
            UNION ALL
            SELECT 'urbanizacion',u.codigo_urbanizacion,u.nombre_urbanizacion,d.nombre_distrito,pr.nombre_provincia,dep.nombre_departamento
            FROM urbanizacion u LEFT JOIN ubigeo_distrito d ON d.codigo_distrito=u.codigo_distrito
            LEFT JOIN ubigeo_provincia pr ON pr.codigo_provincia=d.codigo_provincia
            LEFT JOIN ubigeo_departamento dep ON dep.codigo_departamento=pr.codigo_departamento WHERE u.estado='A'
        ) x ORDER BY nombre_departamento,nombre_provincia,nombre_distrito,nombre";
        return $this->dblink->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerComunidadMarketplace(string $tipoConjunto,int $codigoComunidad): ?array
    {
        foreach($this->listarComunidadesActivasMarketplace() as $c){
            if((string)$c['tipo_conjunto']===$tipoConjunto && (int)$c['codigo_comunidad']===$codigoComunidad){
                return ['tipo_conjunto'=>$tipoConjunto,'nombre'=>(string)$c['nombre'],'codigo_comunidad'=>$codigoComunidad,'nombre_distrito'=>(string)($c['nombre_distrito']??'')];
            }
        }
        return null;
    }

    public function listarMarketplaceFiltradoPorComunidad(
        string $tipoConjunto,int $codigoComunidad,?int $tipo,?int $categoria,string $q,int $page,int $size,?string $tipoPublicacion=null
    ): array {
        $tipoConjunto=strtolower(trim($tipoConjunto));$codigoComunidad=max(0,$codigoComunidad);
        $page=max(1,$page);$size=max(1,min(50,$size));$off=($page-1)*$size;
        if(!in_array($tipoConjunto,['condominio','urbanizacion'],true)||$codigoComunidad<=0)return ['total'=>0,'page'=>$page,'size'=>$size,'items'=>[]];
        $q=trim($q);$tipoPublicacion=$this->normalizarTipoPublicacionFiltro($tipoPublicacion);
        $where=" WHERE ".$this->whereMarketplaceAdministrativo('p','u')." ";$params=[];
        if($tipoConjunto==='condominio'){$where.=" AND p.tipo_conjunto_publicacion='condominio' AND p.codigo_condominio_publicacion=:cond ";$params[':cond']=$codigoComunidad;}
        else{$where.=" AND p.tipo_conjunto_publicacion='urbanizacion' AND p.codigo_urbanizacion_publicacion=:urb ";$params[':urb']=$codigoComunidad;}
        if($tipoPublicacion!==null){$where.=" AND p.tipo_publicacion=:tipo_publicacion ";$params[':tipo_publicacion']=$tipoPublicacion;}
        if($tipo!==null&&$tipo>0){$where.=" AND p.codigo_tipo=:tipo ";$params[':tipo']=$tipo;}
        if($categoria!==null&&$categoria>0){$where.=" AND p.codigo_categoria=:cat ";$params[':cat']=$categoria;}
        if($q!==''){$where.=" AND (p.titulo LIKE :q_titulo OR p.descripcion LIKE :q_descripcion) ";$params[':q_titulo']='%'.$q.'%';$params[':q_descripcion']='%'.$q.'%';}
        $st=$this->dblink->prepare("SELECT COUNT(*) FROM producto p INNER JOIN usuario u ON u.codigo_usuario=p.codigo_usuario {$where}");$this->bindMarketplaceParams($st,$params);$st->execute();$total=(int)$st->fetchColumn();
        $sql="SELECT p.codigo_producto,p.tipo_publicacion,p.titulo,p.descripcion,p.estado,p.precio,p.tipo_atencion_producto,p.visible,p.codigo_usuario,p.codigo_tipo,p.codigo_categoria,p.imagen_portada,p.codigo_usuario_residencia,p.tipo_conjunto_publicacion,p.codigo_condominio_publicacion,p.codigo_urbanizacion_publicacion,p.estado_residencial_publicacion,COALESCE(u.disponibilidad_pedidos,0) disponibilidad_pedidos_vendedor,t.nombre tipo_nombre,c.nombre categoria_nombre,DATE_FORMAT(p.created_at,'%d/%m/%Y %H:%i') create_at FROM producto p INNER JOIN usuario u ON u.codigo_usuario=p.codigo_usuario LEFT JOIN tipo t ON t.codigo_tipo=p.codigo_tipo LEFT JOIN categoria c ON c.codigo_categoria=p.codigo_categoria {$where} ORDER BY p.created_at DESC LIMIT :lim OFFSET :off";
        $st=$this->dblink->prepare($sql);$this->bindMarketplaceParams($st,$params);$st->bindValue(':lim',$size,PDO::PARAM_INT);$st->bindValue(':off',$off,PDO::PARAM_INT);$st->execute();
        return ['total'=>$total,'page'=>$page,'size'=>$size,'items'=>$st->fetchAll(PDO::FETCH_ASSOC)?:[]];
    }

    private function normalizarTipoPublicacionFiltro(?string $tipoPublicacion): ?string
    {
        $v = strtolower(trim((string)$tipoPublicacion));
        if ($v === '') return null;
        return in_array($v, ['producto', 'servicio'], true) ? $v : null;
    }

    private function bindMarketplaceParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $k => $v) {
            $stmt->bindValue(
                $k,
                $v,
                in_array($k, [':tipo', ':cat', ':cond', ':urb'], true) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
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
                p.tipo_publicacion,
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
        $stmt->bindValue(':p', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function contarEstadosSoporte(): array
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
        $st = $this->dblink->prepare($sql);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

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
                p.tipo_publicacion LIKE :q OR
                u.nombre LIKE :q OR u.email LIKE :q
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
                p.tipo_publicacion,
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
                TRIM(COALESCE(u.nombre,'')) AS usuario_nombre,
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
                p.tipo_publicacion LIKE :q OR
                u.nombre LIKE :q OR u.email LIKE :q
            )";
        }
        $sql .= " ORDER BY COALESCE(p.updated_at, p.created_at) DESC LIMIT :lim OFFSET :off";

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
                'codigo_producto'                 => (int)$r['codigo_producto'],
                'tipo_publicacion'                => $r['tipo_publicacion'] ?? 'producto',
                'titulo'                          => $r['titulo'],
                'descripcion'                     => $r['descripcion'],
                'estado'                          => $r['estado'],
                'precio'                          => $r['precio'],
                'tipo_atencion_producto'          => $r['tipo_atencion_producto'],
                'visible'                         => (int)$r['visible'],
                'imagen_portada'                  => $r['imagen_portada'],
                'codigo_usuario_residencia'       => $r['codigo_usuario_residencia'],
                'tipo_conjunto_publicacion'       => $r['tipo_conjunto_publicacion'],
                'codigo_condominio_publicacion'   => $r['codigo_condominio_publicacion'],
                'codigo_urbanizacion_publicacion' => $r['codigo_urbanizacion_publicacion'],
                'estado_residencial_publicacion'  => $r['estado_residencial_publicacion'],
                'created_at'                      => $r['created_at'],
                'updated_at'                      => $r['updated_at'],
                'usuario_nombre'                  => $r['usuario_nombre'],
                'usuario_email'                   => $r['usuario_email'],
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
                p.tipo_publicacion,
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
                TRIM(COALESCE(u.nombre,'')) AS usuario_nombre,
                u.email AS usuario_email
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            WHERE p.codigo_producto = :p
            LIMIT 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':p', $codigoProducto, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return [
            'codigo_producto'                 => (int)$row['codigo_producto'],
            'tipo_publicacion'                => $row['tipo_publicacion'] ?? 'producto',
            'titulo'                          => $row['titulo'],
            'descripcion'                     => $row['descripcion'],
            'estado'                          => $row['estado'],
            'precio'                          => $row['precio'],
            'tipo_atencion_producto'          => $row['tipo_atencion_producto'],
            'visible'                         => (int)$row['visible'],
            'imagen_portada'                  => $row['imagen_portada'],
            'codigo_usuario_residencia'       => $row['codigo_usuario_residencia'],
            'tipo_conjunto_publicacion'       => $row['tipo_conjunto_publicacion'],
            'codigo_condominio_publicacion'   => $row['codigo_condominio_publicacion'],
            'codigo_urbanizacion_publicacion' => $row['codigo_urbanizacion_publicacion'],
            'estado_residencial_publicacion'  => $row['estado_residencial_publicacion'],
            'created_at'                      => $row['created_at'],
            'updated_at'                      => $row['updated_at'],
            'usuario_nombre'                  => $row['usuario_nombre'],
            'usuario_email'                   => $row['usuario_email'],
            'imagenes'                        => $this->obtenerImagenes($codigoProducto),
            'ultima_revision'                 => $this->obtenerUltimaRevision($codigoProducto),
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
        $stmt->bindValue(':p', $codigoProducto, PDO::PARAM_INT);
        $stmt->bindValue(':ea', $estadoAnterior, PDO::PARAM_INT);
        $stmt->bindValue(':en', $estadoNuevo, PDO::PARAM_INT);
        $stmt->bindValue(':c', $comentario, PDO::PARAM_STR);
        $stmt->bindValue(':cs', $codigoSoporte, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$this->dblink->lastInsertId();
    }

    public function actualizarVisibleSoporte(int $codigoProducto, int $visibleNuevo): bool
    {
        $sql = "UPDATE producto SET visible = :v, activo_publicacion = CASE WHEN :v = 2 THEN 1 ELSE activo_publicacion END, updated_at = CURRENT_TIMESTAMP WHERE codigo_producto = :p";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':v', $visibleNuevo, PDO::PARAM_INT);
        $stmt->bindValue(':p', $codigoProducto, PDO::PARAM_INT);
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
                p.tipo_publicacion,
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

        $fila['tipo_publicacion'] = $fila['tipo_publicacion'] ?? 'producto';
        $fila['es_producto_propio'] = ((int)$fila['codigo_usuario'] === $codigoUsuarioViewer) ? 1 : 0;
        $fila['requiere_preparacion'] = ((string)($fila['tipo_atencion_producto'] ?? '') === 'requiere_preparacion') ? 1 : 0;

        return $fila;
    }
    public function obtenerDetalleMarketplaceAdmin(int $codigoProducto,int $codigoUsuarioViewer): ?array
    {
        $sql="SELECT p.codigo_producto,p.tipo_publicacion,p.titulo,p.descripcion,p.estado,p.precio,p.tipo_atencion_producto,p.visible,p.codigo_usuario,p.codigo_tipo,p.codigo_categoria,p.imagen_portada,p.codigo_usuario_residencia,p.tipo_conjunto_publicacion,p.codigo_condominio_publicacion,p.codigo_urbanizacion_publicacion,p.estado_residencial_publicacion,COALESCE(u.disponibilidad_pedidos,0) disponibilidad_pedidos_vendedor,p.updated_at,p.created_at,t.nombre tipo_nombre,c.nombre categoria_nombre,u.nombre vendedor_nombre,u.estado vendedor_estado FROM producto p INNER JOIN usuario u ON u.codigo_usuario=p.codigo_usuario LEFT JOIN tipo t ON t.codigo_tipo=p.codigo_tipo LEFT JOIN categoria c ON c.codigo_categoria=p.codigo_categoria WHERE p.codigo_producto=:id AND ".$this->whereMarketplaceAdministrativo('p','u')." LIMIT 1";
        $st=$this->dblink->prepare($sql);$st->execute([':id'=>$codigoProducto]);$fila=$st->fetch(PDO::FETCH_ASSOC);if(!$fila)return null;
        $fila['tipo_publicacion']=$fila['tipo_publicacion']??'producto';$fila['es_producto_propio']=((int)$fila['codigo_usuario']===$codigoUsuarioViewer)?1:0;$fila['requiere_preparacion']=((string)($fila['tipo_atencion_producto']??'')==='requiere_preparacion')?1:0;return $fila;
    }

}
