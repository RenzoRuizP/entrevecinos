<?php
/*
    Modelo Publicacion
    - Crea publicaciones
    - Registra imágenes asociadas
    - Actualiza imagen_portada
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

    /**
     * Crea la publicación en la tabla `publicacion` y devuelve el ID generado.
     */
    public function crearPublicacion() {
      try {
          $sql = "
            INSERT INTO publicacion
              (titulo, imagen_portada, descripcion, estado, precio, visible, codigo_usuario)
            VALUES
              (:titulo, :imagen_portada, :descripcion, :estado, :precio, :visible, :codigo_usuario)
          ";

          $stmt = $this->dblink->prepare($sql);
          $stmt->bindParam(':titulo',         $this->titulo,        PDO::PARAM_STR);
          $stmt->bindValue(':imagen_portada', $this->imagen_portada, PDO::PARAM_STR);
          $stmt->bindParam(':descripcion',    $this->descripcion,   PDO::PARAM_STR);
          $stmt->bindParam(':estado',         $this->estado,        PDO::PARAM_STR);
          $stmt->bindParam(':precio',         $this->precio);
          $stmt->bindParam(':visible',        $this->visible,       PDO::PARAM_INT);
          $stmt->bindParam(':codigo_usuario', $this->codigo_usuario, PDO::PARAM_INT);

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
                (codigo_publicacion, ruta, es_portada, orden, ancho, alto, peso_bytes, mime)
              VALUES
                (:codigo_publicacion, :ruta, :es_portada, :orden, :ancho, :alto, :peso_bytes, :mime)
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

}
