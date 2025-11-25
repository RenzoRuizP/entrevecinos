<?php
/*
    Modelo Publicacion
    - Crea publicaciones
    - Registra imágenes asociadas
    - Actualiza imagen_portada
    - Lista publicaciones por usuario
    - Obtiene detalle + imágenes
    - SOPORTE COMPLETO PARA EDICIÓN:
        actualizarPublicacionBase
        eliminarImagenes
        recalcularPortada
        obtenerSiguienteOrdenImagen
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

    // FKs
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
        $this->codigo_tipo = ($codigo_tipo !== '' && $codigo_tipo !== null) ? (int)$codigo_tipo : null;
    }

    public function setCodigoCategoria($codigo_categoria) {
        $this->codigo_categoria = ($codigo_categoria !== '' && $codigo_categoria !== null) ? (int)$codigo_categoria : null;
    }

    /* ============================================================
       CREAR PUBLICACIÓN
    ============================================================ */
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

            $stmt->bindParam(':titulo', $this->titulo, PDO::PARAM_STR);

            if ($this->imagen_portada !== null)
                $stmt->bindValue(':imagen_portada', $this->imagen_portada, PDO::PARAM_STR);
            else
                $stmt->bindValue(':imagen_portada', null, PDO::PARAM_NULL);

            $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':estado',      $this->estado,      PDO::PARAM_STR);
            $stmt->bindParam(':precio',      $this->precio);
            $stmt->bindParam(':visible',     $this->visible, PDO::PARAM_INT);
            $stmt->bindParam(':codigo_usuario', $this->codigo_usuario, PDO::PARAM_INT);

            if ($this->codigo_tipo !== null)
                $stmt->bindValue(':codigo_tipo', $this->codigo_tipo, PDO::PARAM_INT);
            else
                $stmt->bindValue(':codigo_tipo', null, PDO::PARAM_NULL);

            if ($this->codigo_categoria !== null)
                $stmt->bindValue(':codigo_categoria', $this->codigo_categoria, PDO::PARAM_INT);
            else
                $stmt->bindValue(':codigo_categoria', null, PDO::PARAM_NULL);

            $stmt->execute();

            return (int)$this->dblink->lastInsertId();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ============================================================
       REGISTRAR IMAGEN
    ============================================================ */
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
                    (:codigo_publicacion, :ruta, :es_portada, :orden,
                     :ancho, :alto, :peso_bytes, :mime)
            ";

            $stmt = $this->dblink->prepare($sql);

            $stmt->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->bindParam(':ruta',               $ruta, PDO::PARAM_STR);
            $stmt->bindParam(':es_portada',         $esPortada, PDO::PARAM_INT);
            $stmt->bindParam(':orden',              $orden, PDO::PARAM_INT);

            ($ancho !== null)     ? $stmt->bindValue(':ancho', $ancho)          : $stmt->bindValue(':ancho', null, PDO::PARAM_NULL);
            ($alto !== null)      ? $stmt->bindValue(':alto', $alto)            : $stmt->bindValue(':alto', null, PDO::PARAM_NULL);
            ($pesoBytes !== null) ? $stmt->bindValue(':peso_bytes', $pesoBytes) : $stmt->bindValue(':peso_bytes', null, PDO::PARAM_NULL);
            ($mime !== null)      ? $stmt->bindValue(':mime', $mime)            : $stmt->bindValue(':mime', null, PDO::PARAM_NULL);

            $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ============================================================
       ACTUALIZAR IMAGEN DE PORTADA
    ============================================================ */
    public function actualizarImagenPortada(int $codigoPublicacion, ?string $rutaPortada) {
        try {
            $sql = "
                UPDATE publicacion
                SET imagen_portada = :ruta
                WHERE codigo_publicacion = :codigo_publicacion
            ";

            $stmt = $this->dblink->prepare($sql);

            if ($rutaPortada !== null)
                $stmt->bindValue(':ruta', $rutaPortada, PDO::PARAM_STR);
            else
                $stmt->bindValue(':ruta', null, PDO::PARAM_NULL);

            $stmt->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);

            $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ============================================================
       LISTAR POR USUARIO
    ============================================================ */
    public function listarPorUsuario(int $codigoUsuario): array
    {
        try {
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
                    DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') AS fecha_creacion
                FROM publicacion
                WHERE codigo_usuario = :p_codigo_usuario
                ORDER BY created_at DESC
            ";

            $sentencia = $this->dblink->prepare($sql);
            $sentencia->bindParam(':p_codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
            $sentencia->execute();

            return $sentencia->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $exc) {
            throw $exc;
        }
    }

    /* ============================================================
       OBTENER POR ID
    ============================================================ */
    public function obtenerPorId(int $codigoPublicacion, int $codigoUsuario): ?array
    {
        try {
            $sql = "
                SELECT
                    codigo_publicacion,
                    titulo,
                    descripcion,
                    estado,
                    precio,
                    visible,
                    codigo_usuario,
                    codigo_tipo,
                    codigo_categoria,
                    DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') AS fecha_creacion
                FROM publicacion
                WHERE codigo_publicacion = :p_codigo_publicacion
                  AND codigo_usuario     = :p_codigo_usuario
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

    /* ============================================================
       OBTENER IMÁGENES
    ============================================================ */
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
                ORDER BY orden ASC, codigo_publicacion_imagen ASC
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':p_codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ============================================================
       1. ACTUALIZAR DATOS BASE (EDITAR)
    ============================================================ */
    public function actualizarPublicacionBase(int $codigoPublicacion, int $codigoUsuario)
    {
        try {
            $sql = "
                UPDATE publicacion
                SET titulo           = :titulo,
                    descripcion      = :descripcion,
                    estado           = :estado,
                    precio           = :precio,
                    visible          = :visible,
                    codigo_tipo      = :codigo_tipo,
                    codigo_categoria = :codigo_categoria
                WHERE codigo_publicacion = :codigo_publicacion
                  AND codigo_usuario     = :codigo_usuario
            ";

            $stmt = $this->dblink->prepare($sql);

            $stmt->bindParam(':titulo',      $this->titulo,      PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':estado',      $this->estado,      PDO::PARAM_STR);
            $stmt->bindParam(':precio',      $this->precio);
            $stmt->bindParam(':visible',     $this->visible,     PDO::PARAM_INT);

            if ($this->codigo_tipo !== null)
                $stmt->bindValue(':codigo_tipo', $this->codigo_tipo, PDO::PARAM_INT);
            else
                $stmt->bindValue(':codigo_tipo', null, PDO::PARAM_NULL);

            if ($this->codigo_categoria !== null)
                $stmt->bindValue(':codigo_categoria', $this->codigo_categoria, PDO::PARAM_INT);
            else
                $stmt->bindValue(':codigo_categoria', null, PDO::PARAM_NULL);

            $stmt->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->bindParam(':codigo_usuario',     $codigoUsuario,     PDO::PARAM_INT);

            $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ============================================================
       2. ELIMINAR IMÁGENES (EDITAR)
    ============================================================ */
    public function eliminarImagenes(int $codigoPublicacion, array $ids)
    {
        if (empty($ids)) return;

        try {
            $in = implode(',', array_fill(0, count($ids), '?'));

            $sql = "
                DELETE FROM publicacion_imagen
                WHERE codigo_publicacion = ?
                  AND codigo_publicacion_imagen IN ($in)
            ";

            $stmt = $this->dblink->prepare($sql);

            $params = array_merge([$codigoPublicacion], $ids);

            foreach ($params as $i => $val) {
                $stmt->bindValue($i + 1, $val, PDO::PARAM_INT);
            }

            $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ============================================================
       3. OBTENER SIGUIENTE ORDEN
    ============================================================ */
    public function obtenerSiguienteOrdenImagen(int $codigoPublicacion): int
    {
        try {
            $sql = "
                SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente
                FROM publicacion_imagen
                WHERE codigo_publicacion = :codigo_publicacion
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);

            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($row['siguiente'] ?? 1);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ============================================================
       4. RE-CALCULAR PORTADA (EDITAR)
    ============================================================ */
    public function recalcularPortada(int $codigoPublicacion)
    {
        try {
            // 1. Poner todas en es_portada = 0
            $sqlClear = "
                UPDATE publicacion_imagen
                SET es_portada = 0
                WHERE codigo_publicacion = :codigo_publicacion
            ";

            $stmt = $this->dblink->prepare($sqlClear);
            $stmt->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmt->execute();

            // 2. Tomar primera imagen por orden
            $sqlFirst = "
                SELECT codigo_publicacion_imagen, ruta
                FROM publicacion_imagen
                WHERE codigo_publicacion = :codigo_publicacion
                ORDER BY orden ASC, codigo_publicacion_imagen ASC
                LIMIT 1
            ";

            $stmtF = $this->dblink->prepare($sqlFirst);
            $stmtF->bindParam(':codigo_publicacion', $codigoPublicacion, PDO::PARAM_INT);
            $stmtF->execute();

            $fila = $stmtF->fetch(PDO::FETCH_ASSOC);
            $rutaPortada = null;

            if ($fila) {
                $imgId = (int)$fila['codigo_publicacion_imagen'];
                $rutaPortada = $fila['ruta'];

                // marcar portada
                $sqlSet = "
                    UPDATE publicacion_imagen
                    SET es_portada = 1
                    WHERE codigo_publicacion_imagen = :id
                ";

                $stmt2 = $this->dblink->prepare($sqlSet);
                $stmt2->bindParam(':id', $imgId, PDO::PARAM_INT);
                $stmt2->execute();
            }

            // 3. Actualiza campo en publicacion
            $this->actualizarImagenPortada($codigoPublicacion, $rutaPortada);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
