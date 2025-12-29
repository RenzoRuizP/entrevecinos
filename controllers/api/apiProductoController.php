<?php
// controllers/api/apiProductoController.php

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Producto.php';

class apiProductoController
{
    private function json(int $statusCode, array $payload)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        return;
    }

    /**
     * Devuelve el código de usuario autenticado o corta con 401.
     * Importante: NO usamos AuthMiddleware (eliminado), usamos SesionJWT.
     */
    private function obtenerUsuarioAuth(): int
    {
        $token = $_COOKIE['auth_token'] ?? null;

        $r = SesionJWT::verificarTokenDetallado($token);

        if (!$r['ok'] || empty($r['data']['codigo_usuario'])) {
            $error = $r['error'] ?? 'TOKEN_INVALIDO';

            $msg = match ($error) {
                'TOKEN_AUSENTE'  => 'Token no encontrado. Vuelve a iniciar sesión.',
                'TOKEN_EXPIRADO' => 'Tu sesión expiró. Vuelve a iniciar sesión.',
                default          => 'Token inválido. Vuelve a iniciar sesión.'
            };

            $this->json(401, ['ok' => false, 'error' => $error, 'mensaje' => $msg]);
            exit;
        }

        return (int)$r['data']['codigo_usuario'];
    }

    /* ======================================================================================
       REGISTRAR PRODUCTO (visible=0 BORRADOR)
       POST /api/producto/registrar
    ====================================================================================== */
    public function registrarProducto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $titulo      = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precioRaw   = $_POST['precio'] ?? null;
            $estado      = $_POST['estado'] ?? 'NoAplica';
            $tipo        = $_POST['comboTipo'] ?? null;
            $categoria   = $_POST['categoria'] ?? null;

            if ($titulo === '' || $descripcion === '') {
                return $this->json(400, ['ok' => false, 'mensaje' => 'Título y descripción son obligatorios.']);
            }

            $precio = is_numeric($precioRaw) ? (float)$precioRaw : 0;
            if ($precio <= 0) {
                return $this->json(400, ['ok' => false, 'mensaje' => 'El precio debe ser mayor a 0.']);
            }

            $estadoValido = ['Nuevo', 'Usado', 'NoAplica'];
            if (!in_array($estado, $estadoValido, true)) $estado = 'NoAplica';

            $prod = new Producto();
            $prod->setTitulo($titulo);
            $prod->setDescripcion($descripcion);
            $prod->setPrecio($precio);
            $prod->setEstado($estado);
            $prod->setCodigoUsuario($codigoUsuario);

            // ✅ MATRIZ: 0=borrador (al crear)
            $prod->setVisible(0);

            $prod->setCodigoTipo($tipo);
            $prod->setCodigoCategoria($categoria);
            $prod->setImagen_portada(null);

            $codigoProducto = $prod->crearProducto();

            // ============== SUBIDA DE IMÁGENES ==============
            $imagenesIntentadas = 0;
            $imagenesSubidas    = 0;
            $primeraRuta        = null;
            $erroresUpload      = [];

            if (!empty($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {

                $names  = $_FILES['imagenes']['name'];
                $tmp    = $_FILES['imagenes']['tmp_name'];
                $errors = $_FILES['imagenes']['error'];
                $sizes  = $_FILES['imagenes']['size'];
                $types  = $_FILES['imagenes']['type'];

                $rootPath   = realpath(__DIR__ . '/../../');
                $baseDirRel = 'uploads/productos/' . $codigoUsuario . '/' . $codigoProducto;
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

                    if ($errorCode !== UPLOAD_ERR_OK) {
                        $msgError = match ($errorCode) {
                            UPLOAD_ERR_INI_SIZE   => "El archivo {$nombreOriginal} excede el tamaño máximo permitido por el servidor.",
                            UPLOAD_ERR_FORM_SIZE  => "El archivo {$nombreOriginal} excede el tamaño máximo permitido por el formulario.",
                            UPLOAD_ERR_PARTIAL    => "El archivo {$nombreOriginal} se subió solo parcialmente.",
                            UPLOAD_ERR_NO_FILE    => "No se envió ningún archivo para {$nombreOriginal}.",
                            UPLOAD_ERR_NO_TMP_DIR => "No existe un directorio temporal.",
                            UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo {$nombreOriginal}.",
                            UPLOAD_ERR_EXTENSION  => "Una extensión de PHP detuvo la subida de {$nombreOriginal}.",
                            default               => "Error desconocido ({$errorCode}) al subir {$nombreOriginal}."
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
                        $erroresUpload[] = "No se pudo mover el archivo {$nombreOriginal}.";
                        continue;
                    }

                    $prod->registrarImagen(
                        $codigoProducto,
                        $destinoRel,
                        ($orden === 1) ? 1 : 0,
                        $orden,
                        $ancho,
                        $alto,
                        $size,
                        $mimeReal
                    );

                    if ($orden === 1 && !$primeraRuta) $primeraRuta = $destinoRel;

                    $imagenesSubidas++;
                    $orden++;
                }
            }

            if ($imagenesIntentadas > 0 && $imagenesSubidas === 0) {
                return $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'No se pudo guardar ninguna de las imágenes enviadas.',
                    'errores' => $erroresUpload
                ]);
            }

            if ($primeraRuta) {
                $prod->actualizarImagenPortada($codigoProducto, $primeraRuta);
            }

            // ✅ Mensaje alineado a tu matriz
            return $this->json(201, [
                'ok'               => true,
                'mensaje'          => 'Producto registrado como borrador. Presiona "Publicar" para enviarlo a revisión.',
                'codigo_producto'  => $codigoProducto,
                'visible'          => 0,
                'imagenes_subidas' => $imagenesSubidas
            ]);

        } catch (Exception $e) {
            return $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al registrar el producto.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /* ======================================================================================
       PUBLICAR PRODUCTO
       POST /api/producto/{id}/publicar
       BORRADOR (0) -> PENDIENTE (1)
    ====================================================================================== */
    public function publicarProducto($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            $codigoUsuario  = $this->obtenerUsuarioAuth();
            $codigoProducto = (int)$id;

            if ($codigoProducto <= 0) {
                return $this->json(400, ['ok' => false, 'mensaje' => 'Código de producto inválido.']);
            }

            $model   = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);

            if (!$detalle) {
                return $this->json(404, ['ok' => false, 'mensaje' => 'Producto no encontrado para este usuario.']);
            }

            $visibleActual = (int)($detalle['visible'] ?? -1);

            // Solo permitir publicar si está en borrador (0)
            if ($visibleActual !== 0) {
                $msg = match ($visibleActual) {
                    1 => 'El producto ya está en estado Pendiente de aprobación.',
                    2 => 'El producto ya está Aprobado y visible en el marketplace.',
                    3 => 'El producto está Anulado y no puede publicarse.',
                    default => 'El producto no está en estado publicable.'
                };

                return $this->json(409, [
                    'ok' => false,
                    'mensaje' => $msg,
                    'visible' => $visibleActual
                ]);
            }

            // Requiere método en modelo: publicarProducto($codigoProducto, $codigoUsuario)
            if (!method_exists($model, 'publicarProducto')) {
                return $this->json(500, [
                    'ok' => false,
                    'mensaje' => 'Falta implementar publicarProducto() en el modelo Producto.'
                ]);
            }

            $ok = $model->publicarProducto($codigoProducto, $codigoUsuario);

            if (!$ok) {
                return $this->json(400, [
                    'ok' => false,
                    'mensaje' => 'No se pudo publicar el producto. Verifica que esté en borrador.'
                ]);
            }

            return $this->json(200, [
                'ok' => true,
                'mensaje' => 'Producto publicado. Ahora está en revisión (Pendiente).',
                'visible' => 1
            ]);

        } catch (Exception $e) {
            return $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al publicar el producto.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /* ======================================================================================
       LISTAR MIS PRODUCTOS
       GET /api/producto/listar
    ====================================================================================== */
    public function listarProductos()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new Producto();
            $lista = $model->listarPorUsuario($codigoUsuario);

            return $this->json(200, ['ok' => true, 'data' => $lista]);

        } catch (Exception $e) {
            return $this->json(500, ['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /* ======================================================================================
       OBTENER PRODUCTO (privado)
       GET /api/producto/{id}
    ====================================================================================== */
    public function obtenerProducto($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            }

            $codigoUsuario  = $this->obtenerUsuarioAuth();
            $codigoProducto = (int)$id;

            $model   = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);

            if (!$detalle) {
                return $this->json(404, ['ok' => false, 'mensaje' => 'Producto no encontrado para este usuario.']);
            }

            $imagenes = $model->obtenerImagenes($codigoProducto);

            $baseUrl = rtrim(BASE_URL, '/');
            foreach ($imagenes as &$img) {
                $ruta = $img['ruta'] ?? '';
                $img['url'] = $ruta !== '' ? $baseUrl . '/' . ltrim($ruta, '/') : '';

                // normalización de ids para tu frontend
                $img['codigo_imagen'] = $img['codigo_producto_imagen'] ?? null;
                $img['id_imagen']     = $img['codigo_producto_imagen'] ?? null;
            }
            unset($img);

            return $this->json(200, [
                'ok'   => true,
                'data' => [
                    'producto' => $detalle,
                    'imagenes' => $imagenes
                ]
            ]);

        } catch (Exception $e) {
            return $this->json(500, ['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /* ======================================================================================
       ACTUALIZAR PRODUCTO
       POST /api/producto/{id}/actualizar
    ====================================================================================== */
    public function actualizarProducto($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            $codigoUsuario  = $this->obtenerUsuarioAuth();
            $codigoProducto = (int)$id;

            $model   = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);
            if (!$detalle) {
                return $this->json(404, ['ok' => false, 'mensaje' => 'Producto no encontrado para este usuario.']);
            }

            $titulo      = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precioRaw   = $_POST['precio'] ?? null;
            $estado      = $_POST['estado'] ?? 'NoAplica';
            $tipo        = $_POST['comboTipo'] ?? null;
            $categoria   = $_POST['categoria'] ?? null;

            if ($titulo === '' || $descripcion === '') {
                return $this->json(400, ['ok' => false, 'mensaje' => 'Título y descripción son obligatorios.']);
            }

            $precio = is_numeric($precioRaw) ? (float)$precioRaw : 0;
            if ($precio <= 0) {
                return $this->json(400, ['ok' => false, 'mensaje' => 'El precio debe ser mayor a 0.']);
            }

            $estadoValido = ['Nuevo', 'Usado', 'NoAplica'];
            if (!in_array($estado, $estadoValido, true)) $estado = 'NoAplica';

            $model->setTitulo($titulo);
            $model->setDescripcion($descripcion);
            $model->setPrecio($precio);
            $model->setEstado($estado);
            $model->setCodigoUsuario($codigoUsuario);
            $model->setCodigoTipo($tipo);
            $model->setCodigoCategoria($categoria);

            $model->actualizarProductoBase($codigoProducto, $codigoUsuario);

            // Eliminar imágenes (ids)
            $eliminadasRaw = $_POST['imagenes_eliminadas'] ?? '[]';
            $idsEliminar   = json_decode($eliminadasRaw, true);
            if (!is_array($idsEliminar)) $idsEliminar = [];

            $idsEliminar = array_values(array_filter(
                array_map('intval', $idsEliminar),
                fn($v) => $v > 0
            ));

            if (!empty($idsEliminar)) {
                $model->eliminarImagenes($codigoProducto, $idsEliminar);
            }

            // Subir nuevas imágenes
            $imagenesIntentadas = 0;
            $imagenesSubidas    = 0;
            $erroresUpload      = [];

            if (!empty($_FILES['imagenes_nuevas']) && is_array($_FILES['imagenes_nuevas']['name'])) {

                $names  = $_FILES['imagenes_nuevas']['name'];
                $tmp    = $_FILES['imagenes_nuevas']['tmp_name'];
                $errors = $_FILES['imagenes_nuevas']['error'];
                $sizes  = $_FILES['imagenes_nuevas']['size'];
                $types  = $_FILES['imagenes_nuevas']['type'];

                $rootPath   = realpath(__DIR__ . '/../../');
                $baseDirRel = 'uploads/productos/' . $codigoUsuario . '/' . $codigoProducto;
                $baseDirAbs = $rootPath . '/' . $baseDirRel;

                if (!is_dir($baseDirAbs)) {
                    if (!mkdir($baseDirAbs, 0775, true) && !is_dir($baseDirAbs)) {
                        throw new Exception('No se pudo crear el directorio de imágenes (edición).');
                    }
                }

                $orden = $model->obtenerSiguienteOrdenImagen($codigoProducto);

                foreach ($names as $i => $nombreOriginal) {
                    $imagenesIntentadas++;

                    $errorCode = (int)($errors[$i] ?? UPLOAD_ERR_NO_FILE);
                    if ($errorCode !== UPLOAD_ERR_OK) {
                        $msgError = match ($errorCode) {
                            UPLOAD_ERR_INI_SIZE   => "El archivo {$nombreOriginal} excede el tamaño máximo permitido.",
                            UPLOAD_ERR_FORM_SIZE  => "El archivo {$nombreOriginal} excede el tamaño máximo permitido por el formulario.",
                            UPLOAD_ERR_PARTIAL    => "El archivo {$nombreOriginal} se subió solo parcialmente.",
                            UPLOAD_ERR_NO_FILE    => "No se envió ningún archivo para {$nombreOriginal}.",
                            UPLOAD_ERR_NO_TMP_DIR => "No existe un directorio temporal.",
                            UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo {$nombreOriginal}.",
                            UPLOAD_ERR_EXTENSION  => "Una extensión de PHP detuvo la subida de {$nombreOriginal}.",
                            default               => "Error desconocido ({$errorCode}) al subir {$nombreOriginal}."
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
                        $erroresUpload[] = "No se pudo mover el archivo {$nombreOriginal}.";
                        continue;
                    }

                    $model->registrarImagen(
                        $codigoProducto,
                        $destinoRel,
                        0,
                        $orden,
                        $ancho,
                        $alto,
                        $size,
                        $mimeReal
                    );

                    $imagenesSubidas++;
                    $orden++;
                }
            }

            // Recalcular portada
            $model->recalcularPortada($codigoProducto);

            return $this->json(200, ['ok' => true, 'mensaje' => 'Producto actualizado correctamente.']);

        } catch (Exception $e) {
            return $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al actualizar el producto.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /* ======================================================================================
       ANULAR PRODUCTO
       POST /api/producto/{id}/anular
       MATRIZ: -> 3 (ANULADO)
    ====================================================================================== */
    public function anularProducto($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            $codigoUsuario  = $this->obtenerUsuarioAuth();
            $codigoProducto = (int)$id;

            $model   = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);
            if (!$detalle) {
                return $this->json(404, ['ok' => false, 'mensaje' => 'Producto no encontrado para este usuario.']);
            }

            $ok = $model->anularProducto($codigoProducto, $codigoUsuario);
            if (!$ok) {
                return $this->json(400, ['ok' => false, 'mensaje' => 'No se pudo anular el producto.']);
            }

            return $this->json(200, ['ok' => true, 'mensaje' => 'Producto anulado correctamente.', 'visible' => 3]);

        } catch (Exception $e) {
            return $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al anular el producto.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /* ======================================================================================
       MARKETPLACE: LISTAR APROBADOS
       GET /api/producto/marketplace
       MATRIZ: visible=2 (APROBADO)
    ====================================================================================== */
    public function listarMarketplace()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            // Si quieres que solo usuarios logueados vean marketplace, descomenta:
            // $this->obtenerUsuarioAuth();

            $model = new Producto();
            $lista = $model->listarAprobadosMarketplace();

            return $this->json(200, ['ok' => true, 'data' => $lista]);

        } catch (Exception $e) {
            return $this->json(500, ['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
