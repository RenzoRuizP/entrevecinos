<?php
/*
    Modelo Producto (EV)
    Estados (visible):
      0 = borrador (recién creado por vendedor; puede "Publicar")
      1 = pendiente (vendedor ya publicó; espera aprobación admin)
      2 = aprobado  (aparece en marketplace)
      3 = anulado   (no disponible)
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

    // 0 borrador por defecto
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
       CREAR PRODUCTO (visible=0 borrador)
    ========================================================== */
    public function crearProducto(): int
    {
        try {
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

        } catch (Exception $e) {
            throw $e;
        }
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
        try {
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

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function actualizarImagenPortada(int $codigoProducto, string $rutaPortada): void
    {
        try {
            $sql = "
                UPDATE producto
                SET imagen_portada = :ruta
                WHERE codigo_producto = :codigo_producto
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':ruta', $rutaPortada, PDO::PARAM_STR);
            $stmt->bindParam(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function obtenerImagenes(int $codigoProducto): array
    {
        try {
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

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminarImagenes(int $codigoProducto, array $idsEliminar): void
    {
        if (empty($idsEliminar)) return;

        try {
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

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function obtenerSiguienteOrdenImagen(int $codigoProducto): int
    {
        try {
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

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function recalcularPortada(int $codigoProducto): void
    {
        try {
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

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ==========================================================
       LISTADOS / DETALLE
    ========================================================== */
    public function listarPorUsuario(int $codigoUsuario): array
    {
        try {
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

            return $sentencia->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $exc) {
            throw $exc;
        }
    }

    public function obtenerPorId(int $codigoProducto, int $codigoUsuario): ?array
    {
        try {
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
            return $fila ?: null;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function actualizarProductoBase(int $codigoProducto, int $codigoUsuario): void
    {
        try {
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

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ==========================================================
       ESTADOS (acciones)
    ========================================================== */

    // Borrador (0) -> Pendiente (1)
    public function publicarProducto(int $codigoProducto, int $codigoUsuario): bool
    {
        try {
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

        } catch (Exception $e) {
            throw $e;
        }
    }

    // Pendiente (1) -> Aprobado (2) (para Admin)
    public function aprobarProducto(int $codigoProducto): bool
    {
        try {
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

        } catch (Exception $e) {
            throw $e;
        }
    }

    // Anular -> 3
    public function anularProducto(int $codigoProducto, int $codigoUsuario): bool
    {
        try {
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

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ==========================================================
       MARKETPLACE (solo aprobados: visible = 2)
    ========================================================== */
    public function listarAprobadosMarketplace(): array
    {
        try {
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ==========================================================
       DESTACADAS PAGADAS (solo aprobados: visible = 2)
    ========================================================== */
    public function listarDestacadasPagadas(int $limit = 12): array
    {
        try {
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
