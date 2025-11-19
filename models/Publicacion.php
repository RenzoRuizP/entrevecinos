<?php
/*
    Modelo Publicacion
    Adaptado a tu estructura real:
    - Guarda tipo y categoría
    - Maneja imágenes
    - Devuelve detalle con tipo/categoría e imágenes
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

    private $codigo_tipo;
    private $codigo_categoria;

    // ===== SETTERS =====
    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function setImagen_portada($imagen_portada) { $this->imagen_portada = $imagen_portada; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function setPrecio($precio) { $this->precio = $precio; }
    public function setEstado($estado) { $this->estado = $estado; }
    public function setVisible($visible) { $this->visible = (int)$visible; }
    public function setCodigoUsuario($codigo_usuario) { $this->codigo_usuario = (int)$codigo_usuario; }

    public function setCodigoTipo($codigo_tipo) {
        $this->codigo_tipo = $codigo_tipo !== null && $codigo_tipo !== ''
            ? (int)$codigo_tipo
            : null;
    }

    public function setCodigoCategoria($codigo_categoria) {
        $this->codigo_categoria = $codigo_categoria !== null && $codigo_categoria !== ''
            ? (int)$codigo_categoria
            : null;
    }

    // ===== Crear publicación =====
    public function crearPublicacion() {
        try {
            $sql = "
                INSERT INTO publicacion
                    (titulo, imagen_portada, descripcion, estado, precio,
                     visible, codigo_usuario, codigo_tipo, codigo_categoria)
                VALUES
                    (:titulo, :imagen_portada, :descripcion, :estado, :precio,
                     :visible, :codigo_usuario, :codigo_tipo, :codigo_categoria)
            ";

            $stmt = $this->dblink->prepare($sql);

            $stmt->bindParam(':titulo', $this->titulo);
            $stmt->bindValue(':imagen_portada', $this->imagen_portada, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':precio', $this->precio);
            $stmt->bindParam(':visible', $this->visible, PDO::PARAM_INT);
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
            return (int)$this->dblink->lastInsertId();

        } catch (Exception $e) {
            throw $e;
        }
    }

    // ===== Registrar imagen =====
    public function registrarImagen(
        int $codigoPublicacion, string $ruta,
        int $esPortada = 0, int $orden = 1,
        ?int $ancho = null, ?int $alto = null,
        ?int $pesoBytes = null, ?string $mime = null
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
            $stmt->bindParam(':ruta', $ruta, PDO::PARAM_STR);
            $stmt->bindParam(':es_portada', $esPortada, PDO::PARAM_INT);
            $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);

            $stmt->bindValue(':ancho', $ancho, $ancho !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':alto', $alto, $alto !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':peso_bytes', $pesoBytes, $pesoBytes !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':mime', $mime, $mime !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

            $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    // ===== Actualizar imagen_portada =====
    public function actualizarImagenPortada(int $codigoPublicacion, string $rutaPortada) {
        try {
            $sql = "
              UPDATE publicacion
              SET imagen_portada = :ruta
              WHERE codigo_publicacion = :codigo_publicacion
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':ruta', $rutaPortada, PDO::PARAM_STR);
            $stmt->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    // ===== Listar por usuario (tabla) =====
    public function listarPorUsuario(int $codigoUsuario): array {
        $sql = "
            SELECT
                codigo_publicacion,
                titulo,
                descripcion,
                estado,
                precio,
                visible,
                DATE_FORMAT(created_at,'%d/%m/%Y %H:%i') AS fecha_creacion
            FROM publicacion
            WHERE codigo_usuario = :u
            ORDER BY created_at DESC
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':u', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== Obtener detalle con tipo/categoría =====
    public function obtenerPorId(int $codigoPublicacion, int $codigoUsuario): ?array {
        $sql = "
            SELECT
                codigo_publicacion,
                titulo,
                descripcion,
                estado,
                precio,
                visible,
                codigo_tipo,
                codigo_categoria,
                DATE_FORMAT(created_at,'%d/%m/%Y %H:%i') AS fecha_creacion
            FROM publicacion
            WHERE codigo_publicacion = :id
              AND codigo_usuario = :u
            LIMIT 1
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':id', $codigoPublicacion, PDO::PARAM_INT);
        $stmt->bindParam(':u', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    // ===== Obtener imágenes de una publicación =====
    public function obtenerImagenes(int $codigoPublicacion): array {
        $sql = "
            SELECT
                codigo_publicacion_imagen,
                ruta,
                es_portada,
                orden
            FROM publicacion_imagen
            WHERE codigo_publicacion = :id
            ORDER BY orden ASC
        ";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':id', $codigoPublicacion, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
