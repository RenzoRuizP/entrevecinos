<?php
// controllers/api/apiPublicacionController.php
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Publicacion.php';

class apiPublicacionController
{
    /**
     * POST /api/publicacion/registrar
     * Registra:
     *  - publicacion
     *  - publicacion_imagen
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
            // 2) Validación de campos
            // ==========================
            $titulo      = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precioRaw   = $_POST['precio'] ?? null;
            $estado      = $_POST['estado'] ?? 'NoAplica';

            // FKs
            $tipo      = $_POST['comboTipo'] ?? null;   // INT o null
            $categoria = $_POST['categoria'] ?? null;   // INT o null

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
            $pub->setVisible(1);
            if (method_exists($pub, 'setCodigoTipo')) {
                $pub->setCodigoTipo($tipo);
            }
            if (method_exists($pub, 'setCodigoCategoria')) {
                $pub->setCodigoCategoria($categoria);
            }
            $pub->setImagen_portada(null); // se actualizará luego

            $codigoPublicacion = $pub->crearPublicacion();

            // ==========================
            // 4) Manejo de imágenes (de raíz)
            // ==========================
            $imagenesIntentadas = 0;
            $imagenesSubidas    = 0;
            $primeraRuta        = null;
            $erroresUpload      = [];

            // Log mínimo para depuración
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

                $orden = 1;

                foreach ($names as $i => $nombreOriginal) {
                    $imagenesIntentadas++;

                    $errorCode = (int)($errors[$i] ?? UPLOAD_ERR_NO_FILE);

                    // --- Mapeo explícito de errores PHP de upload ---
                    if ($errorCode !== UPLOAD_ERR_OK) {
                        $msgError = match ($errorCode) {
                            UPLOAD_ERR_INI_SIZE =>
                                "El archivo {$nombreOriginal} excede el tamaño máximo permitido por el servidor (upload_max_filesize).",
                            UPLOAD_ERR_FORM_SIZE =>
                                "El archivo {$nombreOriginal} excede el tamaño máximo permitido por el formulario (MAX_FILE_SIZE).",
                            UPLOAD_ERR_PARTIAL =>
                                "El archivo {$nombreOriginal} se subió solo parcialmente.",
                            UPLOAD_ERR_NO_FILE =>
                                "No se envió ningún archivo para {$nombreOriginal}.",
                            UPLOAD_ERR_NO_TMP_DIR =>
                                "No existe un directorio temporal para subir archivos (revisar upload_tmp_dir).",
                            UPLOAD_ERR_CANT_WRITE =>
                                "No se pudo escribir el archivo {$nombreOriginal} en el disco (permisos).",
                            UPLOAD_ERR_EXTENSION =>
                                "Una extensión de PHP detuvo la subida de {$nombreOriginal}.",
                            default =>
                                "Error desconocido ({$errorCode}) al subir el archivo {$nombreOriginal}."
                        };
                        $erroresUpload[] = $msgError;
                        continue;
                    }

                    $tmpName = $tmp[$i];
                    $size    = (int)$sizes[$i];
                    $mime    = $types[$i] ?? null;

                    // Validación de que realmente sea imagen
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
                    $destinoRel   = $baseDirRel . '/' . $nombreLimpio; // ruta relativa que se guarda en BD

                    if (!move_uploaded_file($tmpName, $destinoAbs)) {
                        $erroresUpload[] = "No se pudo mover el archivo {$nombreOriginal} al destino final.";
                        continue;
                    }

                    // Registrar en BD
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
            }

            // ==========================
            // 4.1 Consistencia imágenes
            // ==========================
            if ($imagenesIntentadas > 0 && $imagenesSubidas === 0) {
                error_log('EV DEBUG SUBIDA_IMAGENES registrarPublicacion: ' . implode(' | ', $erroresUpload));

                http_response_code(400);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'No se pudo guardar ninguna de las imágenes enviadas.',
                    'errores' => $erroresUpload
                ]);
                return;
            }

            // ==========================
            // 5) Actualizar portada si hay al menos una imagen
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
     *  - publicacion (incluye codigo_tipo, codigo_categoria)
     *  - imagenes (con url absoluta lista para el <img>)
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

            // Construir URL absoluta y alias de ID para el front
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

                // Alias amigables para JS (evGetFotoId)
                if (isset($img['codigo_publicacion_imagen'])) {
                    $img['codigo_imagen'] = $img['codigo_publicacion_imagen'];
                    $img['id_imagen']     = $img['codigo_publicacion_imagen'];
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

    /**
     * POST /api/publicacion/{id}/actualizar
     * Actualiza datos de publicación + imágenes (eliminar/agregar + portada).
     */
    public function actualizarPublicacion($id)
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

            $pubModel = new Publicacion();

            // Verificar que la publicación pertenezca al usuario
            $detallePub = $pubModel->obtenerPorId($codigoPublicacion, $codigoUsuario);
            if (!$detallePub) {
                http_response_code(404);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'Publicación no encontrada para este usuario.'
                ]);
                return;
            }

            // ==========================
            // Validación de campos
            // ==========================
            $titulo      = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precioRaw   = $_POST['precio'] ?? null;
            $estado      = $_POST['estado'] ?? 'NoAplica';
            $tipo        = $_POST['comboTipo'] ?? null;
            $categoria   = $_POST['categoria'] ?? null;

            if ($titulo === '' || $descripcion === '') {
                http_response_code(400);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'Título y descripción son obligatorios.'
                ]);
                return;
            }

            $precio = is_numeric($precioRaw) ? (float)$precioRaw : 0;
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

            // Setear datos en el modelo
            $pubModel->setTitulo($titulo);
            $pubModel->setDescripcion($descripcion);
            $pubModel->setPrecio($precio);
            $pubModel->setEstado($estado);
            $pubModel->setCodigoUsuario($codigoUsuario);
            $pubModel->setVisible(1); // sigue visible para el usuario (1 = borrador / interno)
            if (method_exists($pubModel, 'setCodigoTipo')) {
                $pubModel->setCodigoTipo($tipo);
            }
            if (method_exists($pubModel, 'setCodigoCategoria')) {
                $pubModel->setCodigoCategoria($categoria);
            }

            // ==========================
            // 1) Actualizar datos base
            // ==========================
            $pubModel->actualizarPublicacionBase($codigoPublicacion, $codigoUsuario);

            // ==========================
            // 2) Procesar imágenes eliminadas
            // ==========================
            $eliminadasRaw = $_POST['imagenes_eliminadas'] ?? '[]';
            $idsEliminar   = json_decode($eliminadasRaw, true);
            if (!is_array($idsEliminar)) {
                $idsEliminar = [];
            }
            $idsEliminar = array_values(array_filter(
                array_map('intval', $idsEliminar),
                fn($v) => $v > 0
            ));

            if (!empty($idsEliminar)) {
                $pubModel->eliminarImagenes($codigoPublicacion, $idsEliminar);
            }

            // ==========================
            // 3) Procesar IMÁGENES NUEVAS
            // ==========================
            $imagenesIntentadas = 0;
            $imagenesSubidas    = 0;
            $erroresUpload      = [];

            if (!empty($_FILES['imagenes_nuevas']) && is_array($_FILES['imagenes_nuevas']['name'])) {

                $names  = $_FILES['imagenes_nuevas']['name'];
                $tmp    = $_FILES['imagenes_nuevas']['tmp_name'];
                $errors = $_FILES['imagenes_nuevas']['error'];
                $sizes  = $_FILES['imagenes_nuevas']['size'];
                $types  = $_FILES['imagenes_nuevas']['type'];

                // Carpeta base: /uploads/publicaciones/{usuario}/{publicacion}
                $rootPath   = realpath(__DIR__ . '/../../');
                $baseDirRel = 'uploads/publicaciones/' . $codigoUsuario . '/' . $codigoPublicacion;
                $baseDirAbs = $rootPath . '/' . $baseDirRel;

                if (!is_dir($baseDirAbs)) {
                    if (!mkdir($baseDirAbs, 0775, true) && !is_dir($baseDirAbs)) {
                        throw new Exception('No se pudo crear el directorio de imágenes (edición).');
                    }
                }

                // Obtener siguiente orden desde BD
                if (method_exists($pubModel, 'obtenerSiguienteOrdenImagen')) {
                    $orden = $pubModel->obtenerSiguienteOrdenImagen($codigoPublicacion);
                } else {
                    $imagenesActuales = $pubModel->obtenerImagenes($codigoPublicacion);
                    $orden = count($imagenesActuales) + 1;
                }

                foreach ($names as $i => $nombreOriginal) {
                    $imagenesIntentadas++;

                    $errorCode = (int)($errors[$i] ?? UPLOAD_ERR_NO_FILE);

                    if ($errorCode !== UPLOAD_ERR_OK) {
                        $msgError = match ($errorCode) {
                            UPLOAD_ERR_INI_SIZE =>
                                "El archivo {$nombreOriginal} excede el tamaño máximo permitido por el servidor (upload_max_filesize).",
                            UPLOAD_ERR_FORM_SIZE =>
                                "El archivo {$nombreOriginal} excede el tamaño máximo permitido por el formulario (MAX_FILE_SIZE).",
                            UPLOAD_ERR_PARTIAL =>
                                "El archivo {$nombreOriginal} se subió solo parcialmente.",
                            UPLOAD_ERR_NO_FILE =>
                                "No se envió ningún archivo para {$nombreOriginal}.",
                            UPLOAD_ERR_NO_TMP_DIR =>
                                "No existe un directorio temporal para subir archivos (revisar upload_tmp_dir).",
                            UPLOAD_ERR_CANT_WRITE =>
                                "No se pudo escribir el archivo {$nombreOriginal} en el disco (permisos).",
                            UPLOAD_ERR_EXTENSION =>
                                "Una extensión de PHP detuvo la subida de {$nombreOriginal}.",
                            default =>
                                "Error desconocido ({$errorCode}) al subir el archivo {$nombreOriginal}."
                        };
                        $erroresUpload[] = $msgError;
                        continue;
                    }

                    $tmpName = $tmp[$i];
                    $size    = (int)$sizes[$i];
                    $mime    = $types[$i] ?? null;

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
                    $destinoRel   = $baseDirRel . '/' . $nombreLimpio;

                    if (!move_uploaded_file($tmpName, $destinoAbs)) {
                        $erroresUpload[] = "No se pudo mover el archivo {$nombreOriginal} al destino final.";
                        continue;
                    }

                    // Nuevas imágenes no se marcan como portada directamente
                    $pubModel->registrarImagen(
                        $codigoPublicacion,
                        $destinoRel,
                        0,          // es_portada
                        $orden,
                        $ancho,
                        $alto,
                        $size,
                        $mimeReal
                    );

                    $imagenesSubidas++;
                    $orden++; // siguiente orden
                }
            }

            if ($imagenesIntentadas > 0 && $imagenesSubidas === 0) {
                error_log('EV DEBUG SUBIDA_IMAGENES actualizarPublicacion: ' . implode(' | ', $erroresUpload));
            }

            // ==========================
            // 4) Recalcular portada
            // ==========================
            $pubModel->recalcularPortada($codigoPublicacion);

            echo json_encode([
                'ok'      => true,
                'mensaje' => 'Publicación actualizada correctamente.'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error al actualizar la publicación.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /api/publicacion/{id}/anular
     * Marca la publicación como no visible (visible = 0).
     */
    public function anularPublicacion($id)
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

            $pubModel = new Publicacion();

            // Verificar que exista y sea del usuario
            $detallePub = $pubModel->obtenerPorId($codigoPublicacion, $codigoUsuario);
            if (!$detallePub) {
                http_response_code(404);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'Publicación no encontrada para este usuario.'
                ]);
                return;
            }

            $ok = $pubModel->anularPublicacion($codigoPublicacion, $codigoUsuario);

            if (!$ok) {
                http_response_code(400);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'No se pudo anular la publicación (podría estar ya anulada).'
                ]);
                return;
            }

            echo json_encode([
                'ok'      => true,
                'mensaje' => 'Publicación anulada correctamente.'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error al anular la publicación.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /api/publicacion/{id}/publicar
     * Marca la publicación como publicada (visible = 2).
     */
    public function publicarPublicacion($id)
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

            $pubModel = new Publicacion();

            // Verificar que exista y sea del usuario
            $detallePub = $pubModel->obtenerPorId($codigoPublicacion, $codigoUsuario);
            if (!$detallePub) {
                http_response_code(404);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'Publicación no encontrada para este usuario.'
                ]);
                return;
            }

            $ok = $pubModel->publicarPublicacion($codigoPublicacion, $codigoUsuario);

            if (!$ok) {
                http_response_code(400);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'No se pudo publicar la publicación (podría estar anulada o ya publicada).'
                ]);
                return;
            }

            echo json_encode([
                'ok'      => true,
                'mensaje' => 'Publicación publicada correctamente.'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error al publicar la publicación.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /* ================================================================
     * ✔ ENDPOINT PARA MODAL DEL MARKETPLACE
     * ---------------------------------------------------------------
     * GET /api/publicacion/detalle/{id}
     * Trae SOLO datos que se pueden mostrar públicamente.
     * ================================================================ */
    public function detallePublicacion($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Método no permitido'
                ]);
                return;
            }

            // Requiere usuario autenticado
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'Token no encontrado.']);
                return;
            }

            $usuario = SesionJWT::verificarToken($token);
            if (!$usuario || empty($usuario['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'Token inválido o usuario no encontrado.']);
                return;
            }

            $codigoPublicacion = (int)$id;
            $pubModel = new Publicacion();

            /* ---------------------------------------------------------
               1) Obtener SOLO publicaciones visibles (visible = 2)
            --------------------------------------------------------- */
            $detalle = $pubModel->obtenerDetalleMarketplace($codigoPublicacion);

            if (!$detalle) {
                http_response_code(404);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Publicación no encontrada o no está publicada.'
                ]);
                return;
            }

            /* ---------------------------------------------------------
               2) Obtener imágenes
            --------------------------------------------------------- */
            $imagenes = $pubModel->obtenerImagenes($codigoPublicacion);

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

            /* ---------------------------------------------------------
               3) Respuesta JSON PARA EL MODAL
            --------------------------------------------------------- */
            echo json_encode([
                'ok' => true,
                'data' => [
                    'titulo'            => $detalle['titulo'],
                    'precio'            => $detalle['precio'],
                    'categoria_nombre'  => $detalle['categoria_nombre'] ?? '',
                    'tipo_nombre'       => $detalle['tipo_nombre'] ?? '',
                    'descripcion'       => $detalle['descripcion'],
                    'imagen_portada'    => $detalle['imagen_portada'] 
                                            ? $baseUrl . '/' . ltrim($detalle['imagen_portada'], '/')
                                            : '',
                    'imagenes'          => $imagenes
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error interno.',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/publicacion/listar-publicadas
     * Listado para el Marketplace (visible = 2).
     */
    public function listarPublicadasMarketplace()
    {
        try {
            // Si quieres que el Marketplace sea público, puedes omitir esta validación.
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

            $pubModel = new Publicacion();
            $lista    = $pubModel->listarPublicadas();

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
}
