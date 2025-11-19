<?php
/*
    Modelo Publicacion
    - Crea publicaciones
    - Registra imágenes asociadas
    - Actualiza imagen_portada
    - Lista publicaciones por usuario
    - Obtiene detalle + imágenes
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

    // Nuevos campos para Tipo / Categoría (FKs)
    private $codigo_tipo;
    private $codigo_categoria;

    // ====== SETTERS BÁSICOS ======
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
        $this->codigo_tipo = $codigo_tipo !== null ? (int)$codigo_tipo : null;
    }

    public function setCodigoCategoria($codigo_categoria) {
        $this->codigo_categoria = $codigo_categoria !== null ? (int)$codigo_categoria : null;
    }

    /**
     * Crea la publicación en la tabla `publicacion` y devuelve el ID generado.
     * Requiere que la tabla tenga columnas: codigo_tipo, codigo_categoria (INT NULL).
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

            $stmt->bindParam(':titulo',         $this->titulo,        PDO::PARAM_STR);
            // Puede ir null al inicio; luego se actualiza con actualizarImagenPortada()
            $stmt->bindValue(':imagen_portada', $this->imagen_portada, $this->imagen_portada !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':descripcion',    $this->descripcion,   PDO::PARAM_STR);
            $stmt->bindParam(':estado',         $this->estado,        PDO::PARAM_STR);
            $stmt->bindParam(':precio',         $this->precio);
            $stmt->bindParam(':visible',        $this->visible,       PDO::PARAM_INT);
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
                    (codigo_publicacion, ruta, es_portada, orden,
                     ancho, alto, peso_bytes, mime)
                VALUES
                    (:codigo_publicacion, :ruta, :es_portada, :orden,
                     :ancho, :alto, :peso_bytes, :mime)
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
                    DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS fecha_creacion
                FROM publicacion p
                WHERE p.codigo_usuario = :p_codigo_usuario
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
     * Obtiene una publicación específica de un usuario.
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
                    DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS fecha_creacion
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
}
