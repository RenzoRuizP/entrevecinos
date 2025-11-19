<?php
// controllers/api/apiPublicacionController.php
require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Publicacion.php';

class apiPublicacionController
{
    public function registrarPublicacion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }

        try {
            // ==========================
            // 1) Usuario autenticado
            // ==========================
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                http_response_code(401);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'Token no encontrado'
                ]);
                return;
            }

            $datosToken = SesionJWT::verificarToken($token);
            $codigoUsuario = $datosToken['codigo_usuario'] ?? $datosToken['id_usuario'] ?? null;

            if (!$codigoUsuario) {
                http_response_code(401);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'No se pudo identificar al usuario del token'
                ]);
                return;
            }

            // ==========================
            // 2) Validación de entrada
            // ==========================
            $titulo      = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precioRaw   = $_POST['precio'] ?? null;
            $estado      = $_POST['estado'] ?? 'NoAplica';

            // Campos adicionales
            $tipo      = $_POST['comboTipo'] ?? null;
            $categoria = $_POST['categoria'] ?? null;

            if ($titulo === '' || $descripcion === '') {
                http_response_code(400);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'Título y descripción son obligatorios.'
                ]);
                return;
            }

            $precio = is_numeric($precioRaw) ? (float) $precioRaw : 0;
            if ($precio <= 0) {
                http_response_code(400);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'El precio debe ser mayor a 0.'
                ]);
                return;
            }

            $estadoValido = ['Nuevo','Usado','NoAplica'];
            if (!in_array($estado, $estadoValido, true)) {
                $estado = 'NoAplica';
            }

            // ==========================
            // 3) Crear publicación base
            // ==========================
            $pub = new Publicacion();
            $pub->setTitulo($titulo);
            $pub->setDescripcion($descripcion);
            $pub->setPrecio($precio);
            $pub->setEstado($estado);
            $pub->setCodigoUsuario($codigoUsuario);

            // Guardar tipo / categoría
            $pub->setCodigoTipo($tipo);
            $pub->setCodigoCategoria($categoria);

            $codigoPublicacion = $pub->crearPublicacion();

            // ==========================
            // 4) Manejo de imágenes
            // ==========================
            $imagenesSubidas = 0;
            $primeraRuta = null;

            if (!empty($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {
                $names  = $_FILES['imagenes']['name'];
                $tmp    = $_FILES['imagenes']['tmp_name'];
                $errors = $_FILES['imagenes']['error'];
                $sizes  = $_FILES['imagenes']['size'];
                $types  = $_FILES['imagenes']['type'];

                // Carpeta base: /uploads/publicaciones/{usuario}/{publicacion}
                $rootPath   = realpath(__DIR__ . '/../../'); // raíz del proyecto
                $baseDirRel = 'uploads/publicaciones/' . $codigoUsuario . '/' . $codigoPublicacion;
                $baseDirAbs = $rootPath . '/' . $baseDirRel;

                if (!is_dir($baseDirAbs)) {
                    if (!mkdir($baseDirAbs, 0775, true) && !is_dir($baseDirAbs)) {
                        throw new Exception('No se pudo crear el directorio de imágenes.');
                    }
                }

                $orden = 1;

                foreach ($names as $i => $nombreOriginal) {
                    if ($errors[$i] !== UPLOAD_ERR_OK) {
                        continue;
                    }

                    $tmpName = $tmp[$i];
                    $size    = (int) $sizes[$i];
                    $mime    = $types[$i] ?? null;

                    // Validación básica de imagen
                    $infoImg = @getimagesize($tmpName);
                    if ($infoImg === false) {
                        continue; // no es imagen válida
                    }

                    $ancho = $infoImg[0] ?? null;
                    $alto  = $infoImg[1] ?? null;
                    $mimeReal = $infoImg['mime'] ?? $mime;

                    $ext = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                    $ext = $ext ? strtolower($ext) : 'jpg';

                    $nombreLimpio = 'img_' . $orden . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                    $destinoAbs   = $baseDirAbs . '/' . $nombreLimpio;
                    $destinoRel   = $baseDirRel . '/' . $nombreLimpio; // esto es lo que guardamos en BD

                    if (!move_uploaded_file($tmpName, $destinoAbs)) {
                        continue;
                    }

                    $esPortada = ($orden === 1) ? 1 : 0;

                    $pub->registrarImagen(
                        $codigoPublicacion,
                        $destinoRel,
                        $esPortada,
                        $orden,
                        $ancho,
                        $alto,
                        $size,
                        $mimeReal
                    );

                    if ($esPortada && !$primeraRuta) {
                        $primeraRuta = $destinoRel;
                    }

                    $imagenesSubidas++;
                    $orden++;
                }
            }

            // ==========================
            // 5) Actualizar portada
            // ==========================
            if ($primeraRuta) {
              //  $pub->actualizarImagenPortada($codigoPublicacion, $primeraRuta);
            }

            http_response_code(201);
            echo json_encode([
                'ok'                => true,
                'mensaje'           => 'Publicación registrada correctamente.',
                'codigo_publicacion'=> $codigoPublicacion,
                'imagenes_subidas'  => $imagenesSubidas
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error al registrar la publicación.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    public function listarPublicaciones()
    {
        try {
            // Validar token y obtener usuario
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'No se encontró el token de sesión.']);
                return;
            }

            $usuario = SesionJWT::verificarToken($token);
            if (!$usuario || empty($usuario['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'Token inválido o usuario no encontrado.']);
                return;
            }

            $codigoUsuario = (int)$usuario['codigo_usuario'];

            $pubModel = new Publicacion();
            $lista    = $pubModel->listarPorUsuario($codigoUsuario);

            echo json_encode([
                'ok'   => true,
                'data' => $lista
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok'    => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function obtenerPublicacion($id)
    {
        try {
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                http_response_code(401);
                echo json_encode(['ok'=>false,'error'=>'Token no encontrado']); return;
            }

            $usuario = SesionJWT::verificarToken($token);
            $codigoUsuario = $usuario['codigo_usuario'];

            $pub = new Publicacion();

            $info = $pub->obtenerPorId($id,$codigoUsuario);
         
            if (!$info) {
                http_response_code(404);
                echo json_encode(['ok'=>false,'error'=>'No existe la publicación']); return;
            }

            $imagenes = $pub->obtenerImagenes($id);

            echo json_encode([
                'ok'=>true,
                'data'=>[
                    'publicacion'=>$info,
                    'imagenes'=>$imagenes
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
        }
    }


}
