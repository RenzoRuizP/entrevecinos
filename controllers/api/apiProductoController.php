<?php
// controllers/api/apiProductoController.php

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/ProductoSoporte.php'; // ✅ NUEVO: para registrar reenvío trazable

class apiProductoController
{
    private function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

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

    private function toIntOrNull($v): ?int
    {
        if ($v === null) return null;
        $v = trim((string)$v);
        if ($v === '') return null;
        $n = (int)$v;
        return ($n > 0) ? $n : null;
    }

    private function getMimeReal(string $tmpFile): ?string
    {
        if (!is_file($tmpFile)) return null;

        if (function_exists('finfo_open')) {
            $f = finfo_open(FILEINFO_MIME_TYPE);
            if ($f) {
                $mime = finfo_file($f, $tmpFile);
                finfo_close($f);
                return $mime ?: null;
            }
        }

        $info = @getimagesize($tmpFile);
        if ($info && !empty($info['mime'])) return $info['mime'];

        return null;
    }

    private function extFromMime(?string $mime, string $fallbackExt = 'jpg'): string
    {
        $mime = strtolower((string)$mime);
        return match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png'              => 'png',
            'image/webp'             => 'webp',
            default                  => strtolower($fallbackExt ?: 'jpg')
        };
    }

    private function isAllowedImageMime(?string $mime): bool
    {
        $mime = strtolower((string)$mime);
        return in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true);
    }

    /* ======================================================================================
       REGISTRAR PRODUCTO
    ====================================================================================== */
    public function registrarProducto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
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
                $this->json(400, ['ok' => false, 'mensaje' => 'Título y descripción son obligatorios.']);
                return;
            }

            $precio = is_numeric($precioRaw) ? (float)$precioRaw : 0;
            if ($precio <= 0) {
                $this->json(400, ['ok' => false, 'mensaje' => 'El precio debe ser mayor a 0.']);
                return;
            }

            $estadoValido = ['Nuevo', 'Usado', 'NoAplica'];
            if (!in_array($estado, $estadoValido, true)) $estado = 'NoAplica';

            $prod = new Producto();
            $prod->setTitulo($titulo);
            $prod->setDescripcion($descripcion);
            $prod->setPrecio($precio);
            $prod->setEstado($estado);
            $prod->setCodigoUsuario($codigoUsuario);
            $prod->setVisible(0);
            $prod->setCodigoTipo($tipo);
            $prod->setCodigoCategoria($categoria);
            $prod->setImagen_portada(null);

            $codigoProducto = $prod->crearProducto();

            $imagenesIntentadas = 0;
            $imagenesSubidas    = 0;
            $primeraRuta        = null;
            $erroresUpload      = [];

            if (!empty($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {

                $names  = $_FILES['imagenes']['name'];
                $tmp    = $_FILES['imagenes']['tmp_name'];
                $errors = $_FILES['imagenes']['error'];
                $sizes  = $_FILES['imagenes']['size'];

                $rootPath   = realpath(__DIR__ . '/../../');
                if ($rootPath === false) {
                    throw new Exception('No se pudo resolver el path raíz del proyecto.');
                }

                $baseDirRel = 'uploads/productos/' . $codigoUsuario . '/' . $codigoProducto;
                $baseDirAbs = $rootPath . DIRECTORY_SEPARATOR . $baseDirRel;

                if (!is_dir($baseDirAbs)) {
                    if (!mkdir($baseDirAbs, 0775, true) && !is_dir($baseDirAbs)) {
                        throw new Exception('No se pudo crear el directorio de imágenes.');
                    }
                }

                $max = 10;
                $orden = 1;

                foreach ($names as $i => $nombreOriginal) {
                    if ($orden > $max) {
                        $erroresUpload[] = "Se ignoró {$nombreOriginal}: máximo {$max} imágenes.";
                        continue;
                    }

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

                    $tmpName = $tmp[$i] ?? '';
                    $size    = (int)($sizes[$i] ?? 0);

                    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                        $erroresUpload[] = "El archivo {$nombreOriginal} no es un upload válido.";
                        continue;
                    }

                    $infoImg = @getimagesize($tmpName);
                    if ($infoImg === false) {
                        $erroresUpload[] = "El archivo {$nombreOriginal} no es una imagen válida.";
                        continue;
                    }

                    $ancho    = $infoImg[0] ?? null;
                    $alto     = $infoImg[1] ?? null;
                    $mimeReal = $this->getMimeReal($tmpName) ?? ($infoImg['mime'] ?? null);

                    if (!$this->isAllowedImageMime($mimeReal)) {
                        $erroresUpload[] = "Formato no permitido en {$nombreOriginal}. Solo JPG, PNG o WEBP.";
                        continue;
                    }

                    $fallbackExt = pathinfo((string)$nombreOriginal, PATHINFO_EXTENSION);
                    $ext = $this->extFromMime($mimeReal, $fallbackExt ?: 'jpg');

                    $nombreLimpio = 'img_' . $orden . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                    $destinoAbs   = $baseDirAbs . DIRECTORY_SEPARATOR . $nombreLimpio;
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
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'No se pudo guardar ninguna de las imágenes enviadas.',
                    'errores' => $erroresUpload
                ]);
                return;
            }

            if ($primeraRuta) {
                $prod->actualizarImagenPortada($codigoProducto, $primeraRuta);
            }

            $this->json(201, [
                'ok'               => true,
                'mensaje'          => 'Producto registrado como borrador. Presiona "Publicar" para enviarlo a revisión.',
                'codigo_producto'  => $codigoProducto,
                'visible'          => 0,
                'imagenes_subidas' => $imagenesSubidas,
                'warnings'         => $erroresUpload
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al registrar el producto.',
                'error'   => $e->getMessage()
            ]);
            return;
        }
    }

    /* ======================================================================================
       PUBLICAR PRODUCTO
    ====================================================================================== */
    public function publicarProducto($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario  = $this->obtenerUsuarioAuth();
            $codigoProducto = (int)$id;

            if ($codigoProducto <= 0) {
                $this->json(400, ['ok' => false, 'mensaje' => 'Código de producto inválido.']);
                return;
            }

            $model   = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);

            if (!$detalle) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Producto no encontrado para este usuario.']);
                return;
            }

            $visibleActual = (int)($detalle['visible'] ?? -1);

            if ($visibleActual !== 0) {
                $msg = match ($visibleActual) {
                    1 => 'El producto ya está en estado Pendiente de aprobación.',
                    2 => 'El producto ya está Aprobado y visible en el marketplace.',
                    3 => 'El producto está Anulado y no puede publicarse.',
                    default => 'El producto no está en estado publicable.'
                };

                $this->json(409, [
                    'ok' => false,
                    'mensaje' => $msg,
                    'visible' => $visibleActual
                ]);
                return;
            }

            $ok = $model->publicarProducto($codigoProducto, $codigoUsuario);

            if (!$ok) {
                $this->json(400, [
                    'ok' => false,
                    'mensaje' => 'No se pudo publicar el producto. Verifica que esté en borrador.'
                ]);
                return;
            }

            $this->json(200, [
                'ok' => true,
                'mensaje' => 'Producto publicado. Ahora está en revisión (Pendiente).',
                'visible' => 1
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al publicar el producto.',
                'error'   => $e->getMessage()
            ]);
            return;
        }
    }

    /* ======================================================================================
       LISTAR MIS PRODUCTOS
    ====================================================================================== */
    public function listarProductos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new Producto();
            $lista = $model->listarPorUsuario($codigoUsuario);

            $this->json(200, ['ok' => true, 'data' => $lista]);
            return;

        } catch (Exception $e) {
            $this->json(500, ['ok' => false, 'error' => $e->getMessage()]);
            return;
        }
    }

    /* ======================================================================================
       OBTENER PRODUCTO (privado)
    ====================================================================================== */
    public function obtenerProducto($id): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
                return;
            }

            $codigoUsuario  = $this->obtenerUsuarioAuth();
            $codigoProducto = (int)$id;

            if ($codigoProducto <= 0) {
                $this->json(400, ['ok' => false, 'mensaje' => 'Código de producto inválido.']);
                return;
            }

            $model   = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);

            if (!$detalle) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Producto no encontrado para este usuario.']);
                return;
            }

            $imagenes = $model->obtenerImagenes($codigoProducto);

            $baseUrl = rtrim(BASE_URL, '/');
            foreach ($imagenes as &$img) {
                $ruta = (string)($img['ruta'] ?? '');
                $img['url'] = ($ruta !== '') ? ($baseUrl . '/' . ltrim($ruta, '/')) : '';

                $img['codigo_imagen'] = $img['codigo_producto_imagen'] ?? null;
                $img['id_imagen']     = $img['codigo_producto_imagen'] ?? null;
            }
            unset($img);

            $this->json(200, [
                'ok'   => true,
                'data' => [
                    'producto' => $detalle,
                    'imagenes' => $imagenes
                ]
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, ['ok' => false, 'error' => $e->getMessage()]);
            return;
        }
    }

    /* ======================================================================================
       ACTUALIZAR PRODUCTO
       ✅ NUEVO: si estaba OBSERVADO, registramos REENVIO_CORRECCION en producto_revision
    ====================================================================================== */
    public function actualizarProducto($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario  = $this->obtenerUsuarioAuth();
            $codigoProducto = (int)$id;

            if ($codigoProducto <= 0) {
                $this->json(400, ['ok' => false, 'mensaje' => 'Código de producto inválido.']);
                return;
            }

            $model   = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);
            if (!$detalle) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Producto no encontrado para este usuario.']);
                return;
            }

            $visibleAntes = (int)($detalle['visible'] ?? -1);

            $titulo      = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precioRaw   = $_POST['precio'] ?? null;
            $estado      = $_POST['estado'] ?? 'NoAplica';
            $tipo        = $_POST['comboTipo'] ?? null;
            $categoria   = $_POST['categoria'] ?? null;

            if ($titulo === '' || $descripcion === '') {
                $this->json(400, ['ok' => false, 'mensaje' => 'Título y descripción son obligatorios.']);
                return;
            }

            $precio = is_numeric($precioRaw) ? (float)$precioRaw : 0;
            if ($precio <= 0) {
                $this->json(400, ['ok' => false, 'mensaje' => 'El precio debe ser mayor a 0.']);
                return;
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

            $imagenesIntentadas = 0;
            $imagenesSubidas    = 0;
            $erroresUpload      = [];

            if (!empty($_FILES['imagenes_nuevas']) && is_array($_FILES['imagenes_nuevas']['name'])) {

                $names  = $_FILES['imagenes_nuevas']['name'];
                $tmp    = $_FILES['imagenes_nuevas']['tmp_name'];
                $errors = $_FILES['imagenes_nuevas']['error'];
                $sizes  = $_FILES['imagenes_nuevas']['size'];

                $rootPath   = realpath(__DIR__ . '/../../');
                if ($rootPath === false) {
                    throw new Exception('No se pudo resolver el path raíz del proyecto.');
                }

                $baseDirRel = 'uploads/productos/' . $codigoUsuario . '/' . $codigoProducto;
                $baseDirAbs = $rootPath . DIRECTORY_SEPARATOR . $baseDirRel;

                if (!is_dir($baseDirAbs)) {
                    if (!mkdir($baseDirAbs, 0775, true) && !is_dir($baseDirAbs)) {
                        throw new Exception('No se pudo crear el directorio de imágenes (edición).');
                    }
                }

                $maxTotal = 10;
                $existentes = $model->obtenerImagenes($codigoProducto);
                $countExist = is_array($existentes) ? count($existentes) : 0;

                $orden = $model->obtenerSiguienteOrdenImagen($codigoProducto);

                foreach ($names as $i => $nombreOriginal) {
                    if (($countExist + $imagenesSubidas) >= $maxTotal) {
                        $erroresUpload[] = "Se ignoró {$nombreOriginal}: máximo {$maxTotal} imágenes por producto.";
                        continue;
                    }

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

                    $tmpName = $tmp[$i] ?? '';
                    $size    = (int)($sizes[$i] ?? 0);

                    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                        $erroresUpload[] = "El archivo {$nombreOriginal} no es un upload válido.";
                        continue;
                    }

                    $infoImg = @getimagesize($tmpName);
                    if ($infoImg === false) {
                        $erroresUpload[] = "El archivo {$nombreOriginal} no es una imagen válida.";
                        continue;
                    }

                    $ancho    = $infoImg[0] ?? null;
                    $alto     = $infoImg[1] ?? null;
                    $mimeReal = $this->getMimeReal($tmpName) ?? ($infoImg['mime'] ?? null);

                    if (!$this->isAllowedImageMime($mimeReal)) {
                        $erroresUpload[] = "Formato no permitido en {$nombreOriginal}. Solo JPG, PNG o WEBP.";
                        continue;
                    }

                    $fallbackExt = pathinfo((string)$nombreOriginal, PATHINFO_EXTENSION);
                    $ext = $this->extFromMime($mimeReal, $fallbackExt ?: 'jpg');

                    $nombreLimpio = 'img_' . $orden . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                    $destinoAbs   = $baseDirAbs . DIRECTORY_SEPARATOR . $nombreLimpio;
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

            $model->recalcularPortada($codigoProducto);

            $reenviado = false;
            try {
                if ($visibleAntes === 1) {
                    $ps = new ProductoSoporte();
                    if ($ps->ultimaRevisionEsObservacionSoporte($codigoProducto, $visibleAntes)) {
                        $ps->registrarReenvioCorreccion($codigoProducto, $codigoUsuario, 1, 1);
                        $reenviado = true;
                    }
                }
            } catch (Throwable $e) {
                error_log('[EV][apiProductoController][reenvio_correccion] ' . $e->getMessage());
            }

            $this->json(200, [
                'ok' => true,
                'mensaje' => 'Producto actualizado correctamente.',
                'imagenes_subidas' => $imagenesSubidas,
                'warnings' => $erroresUpload,
                'reenviado_correccion' => $reenviado
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al actualizar el producto.',
                'error'   => $e->getMessage()
            ]);
            return;
        }
    }

    /* ======================================================================================
       ANULAR PRODUCTO
    ====================================================================================== */
    public function anularProducto($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario  = $this->obtenerUsuarioAuth();
            $codigoProducto = (int)$id;

            if ($codigoProducto <= 0) {
                $this->json(400, ['ok' => false, 'mensaje' => 'Código de producto inválido.']);
                return;
            }

            $model   = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);
            if (!$detalle) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Producto no encontrado para este usuario.']);
                return;
            }

            $ok = $model->anularProducto($codigoProducto, $codigoUsuario);
            if (!$ok) {
                $this->json(400, ['ok' => false, 'mensaje' => 'No se pudo anular el producto.']);
                return;
            }

            $this->json(200, ['ok' => true, 'mensaje' => 'Producto anulado correctamente.', 'visible' => 3]);
            return;

        } catch (Exception $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al anular el producto.',
                'error'   => $e->getMessage()
            ]);
            return;
        }
    }

    /* ======================================================================================
       ✅ MARKETPLACE (RAÍZ): FILTRADO POR CONDOMINIO/URBANIZACIÓN DEL USUARIO LOGUEADO
    ====================================================================================== */
    public function listarMarketplace(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $tipo      = $this->toIntOrNull($_GET['tipo'] ?? null);
            $categoria = $this->toIntOrNull($_GET['categoria'] ?? null);
            $q         = trim((string)($_GET['q'] ?? ''));

            $page = (int)($_GET['page'] ?? 1);
            $size = (int)($_GET['size'] ?? 12);
            $page = max(1, $page);
            $size = max(1, min(50, $size));

            $model = new Producto();

            // ✅ Validar residencia activa (RAÍZ)
            $resActiva = $model->obtenerResidenciaActivaUsuario($codigoUsuario);
            if (!$resActiva) {
                $this->json(409, [
                    'ok' => false,
                    'error' => 'SIN_RESIDENCIA_ACTIVA',
                    'mensaje' => 'No se encontró una residencia activa para tu usuario. Completa tu residencia para ver el Marketplace.'
                ]);
                return;
            }

            // ✅ Marketplace filtrado por residencia
            $res = $model->listarMarketplaceFiltradoPorResidencia($codigoUsuario, $tipo, $categoria, $q, $page, $size);

            $items = $res['items'] ?? [];
            $total = (int)($res['total'] ?? count($items));

            $baseUrl = rtrim(BASE_URL, '/');

            foreach ($items as &$p) {
                $ruta = (string)($p['imagen_portada'] ?? '');
                $url  = ($ruta !== '') ? ($baseUrl . '/' . ltrim($ruta, '/')) : '';

                $p['imagen_portada_url'] = $url;

                if ($ruta !== '') {
                    $p['imagen_portada'] = $url;
                }
            }
            unset($p);

            $this->json(200, [
                'ok'    => true,
                'total' => $total,
                'page'  => $page,
                'size'  => $size,
                'data'  => $items
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, ['ok' => false, 'error' => $e->getMessage()]);
            return;
        }
    }
}