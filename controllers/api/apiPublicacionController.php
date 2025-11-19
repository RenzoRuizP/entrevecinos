<?php
// controllers/api/apiPublicacionController.php
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Publicacion.php';

class apiPublicacionController
{
    /**
     * POST /api/publicacion/registrar
     */
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

            $datosToken    = SesionJWT::verificarToken($token);
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

            // Campos adicionales (Tipo / Categoría)
            $tipo      = $_POST['comboTipo'] ?? null;   // codigo_tipo (puede venir null)
            $categoria = $_POST['categoria'] ?? null;   // codigo_categoria (puede venir null)

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

            $estadoValido = ['Nuevo', 'Usado', 'NoAplica'];
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
            // estos setters existen en el modelo que te pasé
            if (method_exists($pub, 'setCodigoTipo')) {
                $pub->setCodigoTipo($tipo);
            }
            if (method_exists($pub, 'setCodigoCategoria')) {
                $pub->setCodigoCategoria($categoria);
            }
            // portada se actualizará luego
            $pub->setImagen_portada(null);

            $codigoPublicacion = $pub->crearPublicacion();

            // ==========================
            // 4) Manejo de imágenes (robusto)
            // ==========================
            $imagenesSubidas = 0;
            $primeraRuta     = null;

            // Log simple para verificar qué llega en _FILES
            if (!empty($_FILES)) {
                error_log("EV DEBUG _FILES registrarPublicacion: " . print_r($_FILES, true));
            }

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

                $orden         = 1;
                $erroresUpload = [];

                foreach ($names as $i => $nombreOriginal) {
                    // 4.1 validar estado de subida
                    if ($errors[$i] !== UPLOAD_ERR_OK) {
                        $erroresUpload[] = "Error código {$errors[$i]} en archivo {$nombreOriginal}";
                        continue;
                    }

                    $tmpName = $tmp[$i];
                    $size    = (int)$sizes[$i];
                    $mime    = $types[$i] ?? null;

                    // 4.2 validar que realmente sea imagen
                    $infoImg = @getimagesize($tmpName);
                    if ($infoImg === false) {
                        $erroresUpload[] = "El archivo {$nombreOriginal} no es una imagen válida.";
                        continue;
                    }

                    $ancho    = $infoImg[0] ?? null;
                    $alto     = $infoImg[1] ?? null;
                    $mimeReal = $infoImg['mime'] ?? $mime;

                    $ext = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                    $ext = $ext ? strtolower($ext) : 'jpg';

                    $nombreLimpio = 'img_' . $orden . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                    $destinoAbs   = $baseDirAbs . '/' . $nombreLimpio;
                    $destinoRel   = $baseDirRel . '/' . $nombreLimpio; // esto va a BD

                    // 4.3 mover al destino final
                    if (!move_uploaded_file($tmpName, $destinoAbs)) {
                        $erroresUpload[] = "No se pudo mover el archivo {$nombreOriginal} al destino final.";
                        continue;
                    }

                    // 4.4 registrar en BD
                    $pub->registrarImagen(
                        $codigoPublicacion,
                        $destinoRel,
                        ($orden === 1) ? 1 : 0,
                        $orden,
                        $ancho,
                        $alto,
                        $size,
                        $mimeReal
                    );

                    if ($orden === 1 && !$primeraRuta) {
                        $primeraRuta = $destinoRel;
                    }

                    $imagenesSubidas++;
                    $orden++;
                }

                // Si llegaron archivos pero ninguna se pudo registrar, deja trazas
                if (!$imagenesSubidas && $erroresUpload) {
                    error_log('EV DEBUG SUBIDA_IMAGENES registrarPublicacion: ' . implode(' | ', $erroresUpload));
                }
            }

            // ==========================
            // 5) Actualizar portada
            // ==========================
            if ($primeraRuta) {
                $pub->actualizarImagenPortada($codigoPublicacion, $primeraRuta);
            }

            http_response_code(201);
            echo json_encode([
                'ok'                 => true,
                'mensaje'            => 'Publicación registrada correctamente.',
                'codigo_publicacion' => $codigoPublicacion,
                'imagenes_subidas'   => $imagenesSubidas
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

    /**
     * GET /api/publicacion/listar
     */
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

    /**
     * GET /api/publicacion/{id}
     * Devuelve:
     * {
     *   ok: true,
     *   data: {
     *     publicacion: { ... },
     *     imagenes: [ { ruta, es_portada, orden, url, ... }, ... ]
     *   }
     * }
     */
    public function obtenerPublicacion($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'Método no permitido'
                ]);
                return;
            }

            // Validar token
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

            $codigoUsuario     = (int)$usuario['codigo_usuario'];
            $codigoPublicacion = (int)$id;

            $pubModel   = new Publicacion();
            $detallePub = $pubModel->obtenerPorId($codigoPublicacion, $codigoUsuario);

            if (!$detallePub) {
                http_response_code(404);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'Publicación no encontrada para este usuario.'
                ]);
                return;
            }

            $imagenes = $pubModel->obtenerImagenes($codigoPublicacion);

            // Armar URL absoluta de cada imagen
            $baseUrl = rtrim(BASE_URL, '/');
            foreach ($imagenes as &$img) {
                $ruta = $img['ruta'] ?? '';
                if ($ruta !== '') {
                    if (preg_match('#^https?://#i', $ruta)) {
                        $img['url'] = $ruta;
                    } else {
                        $img['url'] = $baseUrl . '/' . ltrim($ruta, '/');
                    }
                } else {
                    $img['url'] = '';
                }
            }
            unset($img);

            echo json_encode([
                'ok'   => true,
                'data' => [
                    'publicacion' => $detallePub,
                    'imagenes'    => $imagenes
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok'    => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
