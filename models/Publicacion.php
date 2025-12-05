<?php
/*
    Modelo Publicacion
    - Crea publicaciones
    - Registra imágenes asociadas
    - Actualiza imagen_portada
    - Lista publicaciones por usuario
    - Obtiene detalle + imágenes
    - Actualiza datos base
    - Elimina imágenes
    - Recalcula portada
    - Anula (visible = 0)
    - Publica (visible = 2)
    - Destaca (usa fecha_destacado)
*/

require_once __DIR__ . '/../Config/EnvConfig.php';
require_once __DIR__ . '/../database/Conexion.php';

class Publicacion extends Conexion
{
    private $titulo;
    private $imagen_portada;
    private $descripcion;
    private $precio;
    private $estado;
    private $visible = 1;
    private $codigo_usuario;

    // FKs a tipo / categoría
    private $codigo_tipo;
    private $codigo_categoria;

    // ====== SETTERS ======
    public function setTitulo($titulo) {
        $this->titulo = $titulo;
    }

    public function setImagen_portada($imagen_portada) {
        $this->imagen_portada = $imagen_portada;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setVisible($visible) {
        $this->visible = (int) $visible;
    }

    public function setCodigoUsuario($codigo_usuario) {
        $this->codigo_usuario = (int) $codigo_usuario;
    }

    public function setCodigoTipo($codigo_tipo) {
        $this->codigo_tipo = $codigo_tipo !== null && $codigo_tipo !== '' ? (int)$codigo_tipo : null;
    }

    public function setCodigoCategoria($codigo_categoria) {
        $this->codigo_categoria = $codigo_categoria !== null && $codigo_categoria !== '' ? (int)$codigo_categoria : null;
    }

    /**
     * Crea la publicación en la tabla `publicacion` y devuelve el ID generado.
     * Requiere columnas:
     *   codigo_tipo INT NULL
     *   codigo_categoria INT NULL
     */
    public function crearPublicacion() {
        try {
            $sql = "
                INSERT INTO publicacion
                    (titulo,
                     imagen_portada,
                     descripcion,
                     estado,
                     precio,
                     visible,
                     codigo_usuario,
                     codigo_tipo,
                     codigo_categoria)
                VALUES
                    (:titulo,
                     :imagen_portada,
                     :descripcion,
                     :estado,
                     :precio,
                     :visible,
                     :codigo_usuario,
                     :codigo_tipo,
                     :codigo_categoria)
            ";

            $stmt = $this->dblink->prepare($sql);

            $stmt->bindParam(':titulo', $this->titulo, PDO::PARAM_STR);

            if ($this->imagen_portada !== null) {
                $stmt->bindValue(':imagen_portada', $this->imagen_portada, PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':imagen_portada', null, PDO::PARAM_NULL);
            }

            $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':estado',      $this->estado,      PDO::PARAM_STR);
            $stmt->bindParam(':precio',      $this->precio);
            $stmt->bindParam(':visible',     $this->visible,     PDO::PARAM_INT);
            $stmt->bindParam(':codigo_usuario', $this->codigo_usuario, PDO::PARAM_INT);

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

            return (int) $this->dblink->lastInsertId();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Registra una imagen asociada a una publicación en `publicacion_imagen`.
     */
    public function registrarImagen(
        int $codigoPublicacion,
        string $ruta,
        int $esPortada = 0,
        int $orden = 1,
        ?int $ancho = null,
        ?int $alto = null,
        ?int $pesoBytes = null,
        ?string $mime = null
    ) {
        try {
            $sql = "
                INSERT INTO publicacion_imagen
                    (codigo_publicacion,
                     ruta,
                     es_portada,
                     orden,
                     ancho,
                     alto,
                     peso_bytes,
                     mime)
                VALUES
                    (:codigo_publicacion,
                     :ruta,
                     :es_portada,
                     :orden,
                     :ancho,
                     :alto,
                     :peso_bytes,
                     :mime)
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->bindParam(':ruta',               $ruta,              PDO::PARAM_STR);
            $stmt->bindParam(':es_portada',         $esPortada,         PDO::PARAM_INT);
            $stmt->bindParam(':orden',              $orden,             PDO::PARAM_INT);

            if ($ancho !== null)      $stmt->bindParam(':ancho',      $ancho,      PDO::PARAM_INT); else $stmt->bindValue(':ancho',      null, PDO::PARAM_NULL);
            if ($alto !== null)       $stmt->bindParam(':alto',       $alto,       PDO::PARAM_INT); else $stmt->bindValue(':alto',       null, PDO::PARAM_NULL);
            if ($pesoBytes !== null)  $stmt->bindParam(':peso_bytes', $pesoBytes,  PDO::PARAM_INT); else $stmt->bindValue(':peso_bytes', null, PDO::PARAM_NULL);
            if ($mime !== null)       $stmt->bindParam(':mime',       $mime,       PDO::PARAM_STR); else $stmt->bindValue(':mime',       null, PDO::PARAM_NULL);

            $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Actualiza la ruta de la imagen de portada en `publicacion`.
     */
    public function actualizarImagenPortada(int $codigoPublicacion, string $rutaPortada) {
        try {
            $sql = "
                UPDATE publicacion
                SET imagen_portada = :ruta
                WHERE codigo_publicacion = :codigo_publicacion
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':ruta',               $rutaPortada,      PDO::PARAM_STR);
            $stmt->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Lista publicaciones de un usuario (para la tabla).
     * Solo visibles (visible = 1 ó 2) -> 0 = anulada.
     */
    public function listarPorUsuario(int $codigoUsuario): array
    {
        try {
            $sql = "
                SELECT
                    p.codigo_publicacion,
                    p.titulo,
                    p.descripcion,
                    p.estado,
                    p.precio,
                    p.visible,
                    p.codigo_tipo,
                    p.codigo_categoria,
                    DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS create_at
                FROM publicacion p
                WHERE p.codigo_usuario = :p_codigo_usuario
                  AND p.visible IN (1, 2)
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

    /**
     * Detalle de una publicación de un usuario.
     */
    public function obtenerPorId(int $codigoPublicacion, int $codigoUsuario): ?array
    {
        try {
            $sql = "
                SELECT
                    p.codigo_publicacion,
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
                FROM publicacion p
                WHERE p.codigo_publicacion = :p_codigo_publicacion
                  AND p.codigo_usuario     = :p_codigo_usuario
                LIMIT 1
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->bindParam(':p_codigo_usuario',     $codigoUsuario,    PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtiene TODAS las imágenes de una publicación.
     */
    public function obtenerImagenes(int $codigoPublicacion): array
    {
        try {
            $sql = "
                SELECT
                    codigo_publicacion_imagen,
                    codigo_publicacion,
                    ruta,
                    es_portada,
                    orden,
                    ancho,
                    alto,
                    peso_bytes,
                    mime
                FROM publicacion_imagen
                WHERE codigo_publicacion = :p_codigo_publicacion
                ORDER BY es_portada DESC, orden ASC, codigo_publicacion_imagen ASC
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ==========================================================
       NUEVOS MÉTODOS PARA ACTUALIZAR + IMÁGENES + ANULAR/PUBLICAR
       ========================================================== */

    /**
     * Actualiza los datos base de la publicación (sin tocar imágenes).
     */
    public function actualizarPublicacionBase(int $codigoPublicacion, int $codigoUsuario): void
    {
        try {
            $sql = "
                UPDATE publicacion
                SET
                    titulo          = :titulo,
                    descripcion     = :descripcion,
                    estado          = :estado,
                    precio          = :precio,
                    visible         = :visible,
                    codigo_tipo     = :codigo_tipo,
                    codigo_categoria= :codigo_categoria
                WHERE codigo_publicacion = :codigo_publicacion
                  AND codigo_usuario     = :codigo_usuario
            ";

            $stmt = $this->dblink->prepare($sql);

            $stmt->bindParam(':titulo',      $this->titulo,      PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':estado',      $this->estado,      PDO::PARAM_STR);
            $stmt->bindParam(':precio',      $this->precio);
            $stmt->bindParam(':visible',     $this->visible,     PDO::PARAM_INT);
            $stmt->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->bindParam(':codigo_usuario',     $codigoUsuario,    PDO::PARAM_INT);

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

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Elimina físicamente registros de imágenes (solo BD).
     */
    public function eliminarImagenes(int $codigoPublicacion, array $idsEliminar): void
    {
        if (empty($idsEliminar)) {
            return;
        }

        try {
            $placeholders = [];
            $params = [
                ':p_codigo_publicacion' => $codigoPublicacion
            ];

            foreach ($idsEliminar as $idx => $id) {
                $ph = ':id' . $idx;
                $placeholders[] = $ph;
                $params[$ph] = (int)$id;
            }

            $sql = "
                DELETE FROM publicacion_imagen
                WHERE codigo_publicacion = :p_codigo_publicacion
                  AND codigo_publicacion_imagen IN (" . implode(',', $placeholders) . ")
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute($params);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Devuelve MAX(orden) + 1 para una publicación.
     */
    public function obtenerSiguienteOrdenImagen(int $codigoPublicacion): int
    {
        try {
            $sql = "
                SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente
                FROM publicacion_imagen
                WHERE codigo_publicacion = :p_codigo_publicacion
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($fila['siguiente'] ?? 1);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Recalcula la portada:
     *  - Marca una sola imagen como es_portada = 1
     *  - Actualiza columna imagen_portada en `publicacion`
     */
    public function recalcularPortada(int $codigoPublicacion): void
    {
        try {
            // Obtener la imagen prioritaria
            $sql = "
                SELECT
                    codigo_publicacion_imagen,
                    ruta
                FROM publicacion_imagen
                WHERE codigo_publicacion = :p_codigo_publicacion
                ORDER BY es_portada DESC, orden ASC, codigo_publicacion_imagen ASC
                LIMIT 1
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Poner todas en 0
            $sqlClear = "
                UPDATE publicacion_imagen
                SET es_portada = 0
                WHERE codigo_publicacion = :p_codigo_publicacion
            ";
            $stmtClear = $this->dblink->prepare($sqlClear);
            $stmtClear->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmtClear->execute();

            if ($row) {
                // Marcar esta como portada
                $sqlSet = "
                    UPDATE publicacion_imagen
                    SET es_portada = 1
                    WHERE codigo_publicacion_imagen = :p_id
                ";
                $stmtSet = $this->dblink->prepare($sqlSet);
                $stmtSet->bindParam(':p_id', $row['codigo_publicacion_imagen'], PDO::PARAM_INT);
                $stmtSet->execute();

                // Actualizar en tabla publicacion
                $this->actualizarImagenPortada($codigoPublicacion, $row['ruta']);
            } else {
                // No hay imágenes: limpiar portada
                $sqlNull = "
                    UPDATE publicacion
                    SET imagen_portada = NULL
                    WHERE codigo_publicacion = :p_codigo_publicacion
                ";
                $stmtNull = $this->dblink->prepare($sqlNull);
                $stmtNull->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
                $stmtNull->execute();
            }

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Anula una publicación (visible = 0) del usuario.
     */
    public function anularPublicacion(int $codigoPublicacion, int $codigoUsuario): bool
    {
        try {
            $sql = "
                UPDATE publicacion
                SET visible = 0
                WHERE codigo_publicacion = :p_codigo_publicacion
                  AND codigo_usuario     = :p_codigo_usuario
                  AND visible IN (1, 2)
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->bindParam(':p_codigo_usuario',     $codigoUsuario,    PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Publica una publicación (visible = 2) del usuario.
     * Solo permite publicar si actualmente está visible = 1.
     */
    public function publicarPublicacion(int $codigoPublicacion, int $codigoUsuario): bool
    {
        try {
            $sql = "
                UPDATE publicacion
                SET visible = 2
                WHERE codigo_publicacion = :p_codigo_publicacion
                  AND codigo_usuario     = :p_codigo_usuario
                  AND visible = 1
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->bindParam(':p_codigo_usuario',     $codigoUsuario,    PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Lista publicaciones publicadas (visible = 2) para el Marketplace.
     * Aquí luego puedes filtrar por condominio, etc.
     */
    public function listarPublicadas(): array
    {
        try {
            $sql = "
                SELECT
                    p.codigo_publicacion,
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
                FROM publicacion p
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

    /**
     * Listar publicaciones destacadas (pagadas) para el menú principal.
     *
     * Estrategia: usamos fecha_destacado dentro de las últimas 24 horas.
     * Solo se llenará fecha_destacado cuando se haya cobrado S/ 1 en billetera,
     * así que implícitamente son "pagadas".
     */
    public function listarDestacadasPagadas(): array
    {
        try {
            $sql = "
                SELECT
                    p.codigo_publicacion,
                    p.titulo,
                    p.precio,
                    p.imagen_portada
                FROM publicacion p
                WHERE p.visible = 2
                  AND p.fecha_destacado IS NOT NULL
                  AND p.fecha_destacado <> '0000-00-00 00:00:00'
                  AND p.fecha_destacado >= (NOW() - INTERVAL 24 HOUR)
                ORDER BY p.fecha_destacado DESC, p.created_at DESC
                LIMIT 30
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows ?: [];

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Marca una publicación como destacada, poniendo fecha_destacado = NOW().
     * No cambia el visible (se asume que ya está en 2).
     */
    public function destacarPublicacion(int $codigoPublicacion, int $codigoUsuario): bool
    {
        try {
            $sql = "
                UPDATE publicacion
                SET fecha_destacado = NOW()
                WHERE codigo_publicacion = :p_codigo_publicacion
                  AND codigo_usuario     = :p_codigo_usuario
                  AND visible = 2
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->bindParam(':p_codigo_usuario',     $codigoUsuario,    PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Limpia los destacados vencidos:
     * - fecha_destacado no nula
     * - más de 24 horas de antigüedad
     * Devuelve cuántas filas se actualizaron.
     */
    public function limpiarDestacadosExpirados(): int
    {
        try {
            $sql = "
                UPDATE publicacion
                SET fecha_destacado = NULL
                WHERE fecha_destacado IS NOT NULL
                  AND fecha_destacado <> '0000-00-00 00:00:00'
                  AND fecha_destacado < (NOW() - INTERVAL 24 HOUR)
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute();

            return $stmt->rowCount();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ===============================================================
       NUEVO MÉTODO PARA DETALLE PÚBLICO EN MARKETPLACE
       - Solo visible = 2
       - Devuelve título, precio, tipo, categoría, descripción, portada
       =============================================================== */
    public function obtenerDetalleMarketplace(int $codigoPublicacion): ?array
    {
        try {
            $sql = "
                SELECT
                    p.codigo_publicacion,
                    p.titulo,
                    p.descripcion,
                    p.precio,
                    p.imagen_portada,
                    t.nombre AS tipo_nombre,
                    c.nombre AS categoria_nombre
                FROM publicacion p
                LEFT JOIN tipo t
                    ON t.codigo_tipo = p.codigo_tipo
                LEFT JOIN categoria c
                    ON c.codigo_categoria = p.codigo_categoria
                WHERE p.codigo_publicacion = :id
                  AND p.visible = 2
                LIMIT 1
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':id', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;

        } catch (Exception $e) {
            throw $e;
        }
    }

}
