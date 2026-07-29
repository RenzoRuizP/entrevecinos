<?php
// controllers/api/apiProductoController.php
// EV — API de Publicaciones (productos / servicios)

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/ProductoSoporte.php';
require_once __DIR__ . '/../../models/ConfiguracionPlataforma.php';
require_once __DIR__ . '/../../middleware/FuncionalidadGuard.php';

class apiProductoController
{
    private const MAX_IMAGENES = 10;

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
                default          => 'Token inválido. Vuelve a iniciar sesión.',
            };

            $this->json(401, [
                'ok'      => false,
                'error'   => $error,
                'mensaje' => $msg,
            ]);
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

    private function normalizarTipoPublicacion($valor): string
    {
        $v = strtolower(trim((string)$valor));
        return in_array($v, ['producto', 'servicio'], true) ? $v : 'producto';
    }

    private function exigirPublicacionHabilitada(string $tipoPublicacion): void
    {
        $clave = $tipoPublicacion === 'servicio'
            ? ConfiguracionPlataforma::FUNC_PUBLICAR_SERVICIOS
            : ConfiguracionPlataforma::FUNC_PUBLICAR_PRODUCTOS;

        FuncionalidadGuard::exigirJson($clave);
    }

    private function etiquetaPublicacion(string $tipoPublicacion): string
    {
        return $tipoPublicacion === 'servicio' ? 'servicio' : 'producto';
    }

    private function etiquetaPublicacionMayus(string $tipoPublicacion): string
    {
        return $tipoPublicacion === 'servicio' ? 'Servicio' : 'Producto';
    }

    private function respuestaLimiteServiciosPiloto(array $resumen = []): array
    {
        $maximo = max(1, (int)($resumen['maximo'] ?? Producto::MAX_SERVICIOS_ACTIVOS_PILOTO));
        $activos = max(0, (int)($resumen['activos'] ?? 0));

        return [
            'maximo'      => $maximo,
            'activos'     => $activos,
            'disponibles' => max(0, (int)($resumen['disponibles'] ?? ($maximo - $activos))),
            'alcanzado'   => (bool)($resumen['alcanzado'] ?? ($activos >= $maximo)),
            'es_gratis'   => true,
        ];
    }

    private function normalizarEstadoPublicacion($valor, string $tipoPublicacion): string
    {
        if ($tipoPublicacion === 'servicio') {
            return 'NoAplica';
        }

        $v = trim((string)$valor);
        $permitidos = ['Nuevo', 'Usado', 'NoAplica'];
        return in_array($v, $permitidos, true) ? $v : 'NoAplica';
    }

    private function normalizarTipoAtencionProducto($valor, string $tipoPublicacion = 'producto'): string
    {
        if ($tipoPublicacion === 'servicio') {
            return 'no_requiere_preparacion';
        }

        $v = strtolower(trim((string)$valor));
        $permitidos = ['requiere_preparacion', 'no_requiere_preparacion'];
        return in_array($v, $permitidos, true) ? $v : 'no_requiere_preparacion';
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
            'image/png'               => 'png',
            'image/webp'              => 'webp',
            default                   => strtolower($fallbackExt ?: 'jpg'),
        };
    }

    private function isAllowedImageMime(?string $mime): bool
    {
        $mime = strtolower((string)$mime);
        return in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true);
    }

    private function validarBasePublicacion(
        string $titulo,
        string $descripcion,
        $precioRaw,
        $codigoTipo,
        $codigoCategoria
    ): ?array {
        if ($titulo === '') {
            return ['campo' => 'titulo', 'mensaje' => 'Debes ingresar un título para la publicación.'];
        }

        if ($descripcion === '') {
            return ['campo' => 'descripcion', 'mensaje' => 'Debes ingresar una descripción para la publicación.'];
        }

        $precio = is_numeric($precioRaw) ? (float)$precioRaw : 0.0;
        if ($precio <= 0) {
            return ['campo' => 'precio', 'mensaje' => 'El precio debe ser mayor a 0.'];
        }

        if ($this->toIntOrNull($codigoTipo) === null) {
            return ['campo' => 'comboTipo', 'mensaje' => 'Debes seleccionar un tipo.'];
        }

        if ($this->toIntOrNull($codigoCategoria) === null) {
            return ['campo' => 'categoria', 'mensaje' => 'Debes seleccionar una categoría.'];
        }

        return null;
    }

    private function prepararDirectorioImagenes(int $codigoUsuario, int $codigoProducto): array
    {
        $rootPath = realpath(__DIR__ . '/../../');
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

        return [$baseDirRel, $baseDirAbs];
    }

    private function mensajeErrorUpload(int $errorCode, string $nombreOriginal): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE   => "El archivo {$nombreOriginal} excede el tamaño máximo permitido por el servidor.",
            UPLOAD_ERR_FORM_SIZE  => "El archivo {$nombreOriginal} excede el tamaño máximo permitido por el formulario.",
            UPLOAD_ERR_PARTIAL    => "El archivo {$nombreOriginal} se subió solo parcialmente.",
            UPLOAD_ERR_NO_FILE    => "No se envió ningún archivo para {$nombreOriginal}.",
            UPLOAD_ERR_NO_TMP_DIR => 'No existe un directorio temporal.',
            UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo {$nombreOriginal}.",
            UPLOAD_ERR_EXTENSION  => "Una extensión de PHP detuvo la subida de {$nombreOriginal}.",
            default               => "Error desconocido ({$errorCode}) al subir {$nombreOriginal}.",
        };
    }

    private function subirImagenesNuevas(
        Producto $model,
        int $codigoUsuario,
        int $codigoProducto,
        string $fileKey,
        int $ordenInicial,
        int $cantidadExistente = 0,
        bool $marcarPrimeraComoPortada = false
    ): array {
        $resultado = [
            'intentadas'     => 0,
            'subidas'        => 0,
            'primera_ruta'   => null,
            'errores'        => [],
            'siguiente_orden'=> $ordenInicial,
        ];

        if (empty($_FILES[$fileKey]) || !is_array($_FILES[$fileKey]['name'] ?? null)) {
            return $resultado;
        }

        [$baseDirRel, $baseDirAbs] = $this->prepararDirectorioImagenes($codigoUsuario, $codigoProducto);

        $names  = $_FILES[$fileKey]['name'];
        $tmp    = $_FILES[$fileKey]['tmp_name'];
        $errors = $_FILES[$fileKey]['error'];
        $sizes  = $_FILES[$fileKey]['size'];

        $orden = max(1, $ordenInicial);

        foreach ($names as $i => $nombreOriginal) {
            $nombreOriginal = (string)$nombreOriginal;

            if (($cantidadExistente + (int)$resultado['subidas']) >= self::MAX_IMAGENES) {
                $resultado['errores'][] = "Se ignoró {$nombreOriginal}: máximo " . self::MAX_IMAGENES . ' imágenes por publicación.';
                continue;
            }

            $resultado['intentadas']++;

            $errorCode = (int)($errors[$i] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_OK) {
                $resultado['errores'][] = $this->mensajeErrorUpload($errorCode, $nombreOriginal);
                continue;
            }

            $tmpName = $tmp[$i] ?? '';
            $size    = (int)($sizes[$i] ?? 0);

            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                $resultado['errores'][] = "El archivo {$nombreOriginal} no es un upload válido.";
                continue;
            }

            $infoImg = @getimagesize($tmpName);
            if ($infoImg === false) {
                $resultado['errores'][] = "El archivo {$nombreOriginal} no es una imagen válida.";
                continue;
            }

            $ancho    = $infoImg[0] ?? null;
            $alto     = $infoImg[1] ?? null;
            $mimeReal = $this->getMimeReal($tmpName) ?? ($infoImg['mime'] ?? null);

            if (!$this->isAllowedImageMime($mimeReal)) {
                $resultado['errores'][] = "Formato no permitido en {$nombreOriginal}. Solo JPG, PNG o WEBP.";
                continue;
            }

            $fallbackExt = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $ext = $this->extFromMime($mimeReal, $fallbackExt ?: 'jpg');

            $nombreLimpio = 'img_' . $orden . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $destinoAbs   = $baseDirAbs . DIRECTORY_SEPARATOR . $nombreLimpio;
            $destinoRel   = $baseDirRel . '/' . $nombreLimpio;

            if (!move_uploaded_file($tmpName, $destinoAbs)) {
                $resultado['errores'][] = "No se pudo mover el archivo {$nombreOriginal}.";
                continue;
            }

            $esPortada = ($marcarPrimeraComoPortada && (int)$resultado['subidas'] === 0) ? 1 : 0;

            $model->registrarImagen(
                $codigoProducto,
                $destinoRel,
                $esPortada,
                $orden,
                $ancho !== null ? (int)$ancho : null,
                $alto !== null ? (int)$alto : null,
                $size,
                $mimeReal
            );

            if ($resultado['primera_ruta'] === null) {
                $resultado['primera_ruta'] = $destinoRel;
            }

            $resultado['subidas']++;
            $orden++;
        }

        $resultado['siguiente_orden'] = $orden;
        return $resultado;
    }

    private function aplicarDatosAProducto(
        Producto $prod,
        int $codigoUsuario,
        string $tipoPublicacion,
        string $titulo,
        string $descripcion,
        float $precio,
        string $estado,
        string $tipoAtencionProducto,
        $codigoTipo,
        $codigoCategoria
    ): void {
        $prod->setTipoPublicacion($tipoPublicacion);
        $prod->setTitulo($titulo);
        $prod->setDescripcion($descripcion);
        $prod->setPrecio($precio);
        $prod->setEstado($estado);
        $prod->setTipoAtencionProducto($tipoAtencionProducto);
        $prod->setCodigoUsuario($codigoUsuario);
        $prod->setCodigoTipo($codigoTipo);
        $prod->setCodigoCategoria($codigoCategoria);
    }

    /* ======================================================================================
       REGISTRAR PUBLICACIÓN
       - tipo_publicacion: producto | servicio
       - visible = 0: borrador
       - crearProducto() guarda snapshot residencial
    ====================================================================================== */
    public function registrarProducto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $tipoPublicacion = $this->normalizarTipoPublicacion($_POST['tipo_publicacion'] ?? 'producto');
            $this->exigirPublicacionHabilitada($tipoPublicacion);
            $label = $this->etiquetaPublicacion($tipoPublicacion);

            $titulo      = trim((string)($_POST['titulo'] ?? ''));
            $descripcion = trim((string)($_POST['descripcion'] ?? ''));
            $precioRaw   = $_POST['precio'] ?? null;
            $tipo        = $_POST['comboTipo'] ?? null;
            $categoria   = $_POST['categoria'] ?? null;

            $errorValidacion = $this->validarBasePublicacion($titulo, $descripcion, $precioRaw, $tipo, $categoria);
            if ($errorValidacion !== null) {
                $this->json(400, [
                    'ok'      => false,
                    'campo'   => $errorValidacion['campo'],
                    'mensaje' => $errorValidacion['mensaje'],
                ]);
                return;
            }

            $precio = (float)$precioRaw;
            $estado = $this->normalizarEstadoPublicacion($_POST['estado'] ?? 'NoAplica', $tipoPublicacion);

            $prod = new Producto();

            try {
                $tipoAtencionProducto = $prod->resolverTipoAtencionPorCategoria(
                    (int)$categoria,
                    (int)$tipo,
                    $tipoPublicacion
                );
            } catch (InvalidArgumentException $e) {
                $this->json(400, [
                    'ok'      => false,
                    'campo'   => 'categoria',
                    'mensaje' => $e->getMessage(),
                ]);
                return;
            }

            $resActiva = $prod->obtenerResidenciaActivaUsuario($codigoUsuario);
            if (!$resActiva) {
                $this->json(409, [
                    'ok'       => false,
                    'error'    => 'SIN_RESIDENCIA_ACTIVA',
                    'mensaje'  => 'No tienes una residencia activa para registrar publicaciones.',
                    'redirect' => rtrim(BASE_URL, '/') . '/mi-perfil',
                ]);
                return;
            }

            $this->aplicarDatosAProducto(
                $prod,
                $codigoUsuario,
                $tipoPublicacion,
                $titulo,
                $descripcion,
                $precio,
                $estado,
                $tipoAtencionProducto,
                $tipo,
                $categoria
            );
            $prod->setVisible(0);
            $prod->setImagen_portada(null);

            $codigoProducto = $prod->crearProducto();

            $upload = $this->subirImagenesNuevas(
                $prod,
                $codigoUsuario,
                $codigoProducto,
                'imagenes',
                1,
                0,
                true
            );

            if ((int)$upload['intentadas'] > 0 && (int)$upload['subidas'] === 0) {
                $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'No se pudo guardar ninguna de las imágenes enviadas.',
                    'errores' => $upload['errores'],
                ]);
                return;
            }

            if (!empty($upload['primera_ruta'])) {
                $prod->actualizarImagenPortada($codigoProducto, (string)$upload['primera_ruta']);
            }

            $this->json(201, [
                'ok'                     => true,
                'mensaje'                => $this->etiquetaPublicacionMayus($tipoPublicacion) . ' registrado como borrador. Presiona "Publicar" para enviarlo a revisión.',
                'codigo_producto'        => $codigoProducto,
                'tipo_publicacion'       => $tipoPublicacion,
                'visible'                => 0,
                'tipo_atencion_producto' => $tipoAtencionProducto,
                'imagenes_subidas'       => (int)$upload['subidas'],
                'warnings'               => $upload['errores'],
                'servicios_piloto'        => $this->respuestaLimiteServiciosPiloto(
                    $prod->obtenerResumenServiciosPiloto($codigoUsuario)
                ),
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al registrar la publicación.',
                'error'   => $e->getMessage(),
            ]);
            return;
        }
    }

    /* ======================================================================================
       PUBLICAR / ENVIAR A REVISIÓN
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
                $this->json(400, ['ok' => false, 'mensaje' => 'Código de publicación inválido.']);
                return;
            }

            $model = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);

            if (!$detalle) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Publicación no encontrada para este usuario.']);
                return;
            }

            $visibleActual = (int)($detalle['visible'] ?? -1);
            $tipoPublicacion = $this->normalizarTipoPublicacion($detalle['tipo_publicacion'] ?? 'producto');
            $this->exigirPublicacionHabilitada($tipoPublicacion);
            $label = $this->etiquetaPublicacion($tipoPublicacion);

            if ($visibleActual !== 0) {
                $msg = match ($visibleActual) {
                    1 => "El {$label} ya está en estado Pendiente de aprobación.",
                    2 => "El {$label} ya está Aprobado y visible en el marketplace.",
                    3 => "El {$label} fue rechazado por soporte. Corrígelo o crea una nueva publicación.",
                    4 => "El {$label} está Anulado y no puede publicarse.",
                    default => 'La publicación no está en estado publicable.',
                };

                $this->json(409, [
                    'ok'      => false,
                    'mensaje' => $msg,
                    'visible' => $visibleActual,
                ]);
                return;
            }

            $resultado = $model->publicarConReglaPilotoServicios($codigoProducto, $codigoUsuario);

            if (!($resultado['ok'] ?? false)) {
                $codigoError = (string)($resultado['codigo'] ?? 'NO_SE_PUDO_PUBLICAR');

                $status = match ($codigoError) {
                    'PUBLICACION_NO_ENCONTRADA'  => 404,
                    'ESTADO_NO_PUBLICABLE',
                    'LIMITE_SERVICIOS_ALCANZADO' => 409,
                    'PARAMETROS_INVALIDOS'       => 400,
                    default                       => 400,
                };

                $this->json($status, [
                    'ok'                => false,
                    'error'             => $codigoError,
                    'codigo'            => $codigoError,
                    'mensaje'           => $resultado['mensaje'] ?? 'No se pudo enviar la publicación a revisión.',
                    'visible'           => $resultado['visible_actual'] ?? $visibleActual,
                    'tipo_publicacion'  => $tipoPublicacion,
                    'servicios_piloto'  => $this->respuestaLimiteServiciosPiloto(
                        $resultado['servicios_piloto'] ?? $model->obtenerResumenServiciosPiloto($codigoUsuario)
                    ),
                ]);
                return;
            }

            $resumenServicios = $this->respuestaLimiteServiciosPiloto(
                $resultado['servicios_piloto'] ?? $model->obtenerResumenServiciosPiloto($codigoUsuario)
            );

            $mensaje = $tipoPublicacion === 'servicio'
                ? 'Servicio enviado a revisión sin costo durante el piloto. Ahora tienes '
                    . $resumenServicios['activos'] . ' de ' . $resumenServicios['maximo'] . ' cupos activos en uso.'
                : 'Publicación enviada a revisión. Ahora está en estado Pendiente.';

            $this->json(200, [
                'ok'               => true,
                'mensaje'          => $mensaje,
                'tipo_publicacion' => $tipoPublicacion,
                'visible'          => 1,
                'servicios_piloto' => $resumenServicios,
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al publicar la publicación.',
                'error'   => $e->getMessage(),
            ]);
            return;
        }
    }

    /* ======================================================================================
       LISTAR MIS PUBLICACIONES
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

            $this->json(200, [
                'ok'                => true,
                'data'              => $lista,
                'servicios_piloto'  => $this->respuestaLimiteServiciosPiloto(
                    $model->obtenerResumenServiciosPiloto($codigoUsuario)
                ),
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, ['ok' => false, 'error' => $e->getMessage()]);
            return;
        }
    }

    /* ======================================================================================
       OBTENER PUBLICACIÓN DEL USUARIO
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
                $this->json(400, ['ok' => false, 'mensaje' => 'Código de publicación inválido.']);
                return;
            }

            $model = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);

            if (!$detalle) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Publicación no encontrada para este usuario.']);
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

            $detalle['tipo_publicacion'] = $this->normalizarTipoPublicacion($detalle['tipo_publicacion'] ?? 'producto');

            $this->json(200, [
                'ok'   => true,
                'data' => [
                    'producto' => $detalle,
                    'imagenes' => $imagenes,
                ],
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, ['ok' => false, 'error' => $e->getMessage()]);
            return;
        }
    }

    /* ======================================================================================
       ACTUALIZAR PUBLICACIÓN
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
                $this->json(400, ['ok' => false, 'mensaje' => 'Código de publicación inválido.']);
                return;
            }

            $model = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);
            if (!$detalle) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Publicación no encontrada para este usuario.']);
                return;
            }

            $visibleAntes = (int)($detalle['visible'] ?? -1);

            $tipoPublicacion = $this->normalizarTipoPublicacion(
                $_POST['tipo_publicacion'] ?? ($detalle['tipo_publicacion'] ?? 'producto')
            );
            $this->exigirPublicacionHabilitada($tipoPublicacion);

            $titulo      = trim((string)($_POST['titulo'] ?? ''));
            $descripcion = trim((string)($_POST['descripcion'] ?? ''));
            $precioRaw   = $_POST['precio'] ?? null;
            $tipo        = $_POST['comboTipo'] ?? null;
            $categoria   = $_POST['categoria'] ?? null;

            $errorValidacion = $this->validarBasePublicacion($titulo, $descripcion, $precioRaw, $tipo, $categoria);
            if ($errorValidacion !== null) {
                $this->json(400, [
                    'ok'      => false,
                    'campo'   => $errorValidacion['campo'],
                    'mensaje' => $errorValidacion['mensaje'],
                ]);
                return;
            }

            $precio = (float)$precioRaw;
            $estado = $this->normalizarEstadoPublicacion($_POST['estado'] ?? 'NoAplica', $tipoPublicacion);

            try {
                $tipoAtencionProducto = $model->resolverTipoAtencionPorCategoria(
                    (int)$categoria,
                    (int)$tipo,
                    $tipoPublicacion
                );
            } catch (InvalidArgumentException $e) {
                $this->json(400, [
                    'ok'      => false,
                    'campo'   => 'categoria',
                    'mensaje' => $e->getMessage(),
                ]);
                return;
            }

            $this->aplicarDatosAProducto(
                $model,
                $codigoUsuario,
                $tipoPublicacion,
                $titulo,
                $descripcion,
                $precio,
                $estado,
                $tipoAtencionProducto,
                $tipo,
                $categoria
            );

            $model->actualizarProductoBase($codigoProducto, $codigoUsuario);

            $eliminadasRaw = $_POST['imagenes_eliminadas'] ?? '[]';
            $idsEliminar = json_decode((string)$eliminadasRaw, true);
            if (!is_array($idsEliminar)) $idsEliminar = [];

            $idsEliminar = array_values(array_filter(
                array_map('intval', $idsEliminar),
                static fn($v) => $v > 0
            ));

            if (!empty($idsEliminar)) {
                $model->eliminarImagenes($codigoProducto, $idsEliminar);
            }

            $existentes = $model->obtenerImagenes($codigoProducto);
            $countExist = is_array($existentes) ? count($existentes) : 0;
            $orden = $model->obtenerSiguienteOrdenImagen($codigoProducto);

            $upload = $this->subirImagenesNuevas(
                $model,
                $codigoUsuario,
                $codigoProducto,
                'imagenes_nuevas',
                $orden,
                $countExist,
                false
            );

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
                'ok'                     => true,
                'mensaje'                => 'Publicación actualizada correctamente.',
                'tipo_publicacion'       => $tipoPublicacion,
                'tipo_atencion_producto' => $tipoAtencionProducto,
                'imagenes_subidas'       => (int)$upload['subidas'],
                'warnings'               => $upload['errores'],
                'reenviado_correccion'   => $reenviado,
                'servicios_piloto'        => $this->respuestaLimiteServiciosPiloto(
                    $model->obtenerResumenServiciosPiloto($codigoUsuario)
                ),
            ]);
            return;

        } catch (DomainException $e) {
            if ($e->getMessage() === 'LIMITE_SERVICIOS_ALCANZADO') {
                $resumen = (new Producto())->obtenerResumenServiciosPiloto((int)($codigoUsuario ?? 0));
                $this->json(409, [
                    'ok'               => false,
                    'error'            => 'LIMITE_SERVICIOS_ALCANZADO',
                    'codigo'           => 'LIMITE_SERVICIOS_ALCANZADO',
                    'mensaje'          => 'No puedes convertir esta publicación en un servicio activo porque ya tienes 5 servicios activos o en revisión.',
                    'servicios_piloto' => $this->respuestaLimiteServiciosPiloto($resumen),
                ]);
                return;
            }

            $this->json(409, [
                'ok'      => false,
                'error'   => 'REGLA_NEGOCIO',
                'mensaje' => $e->getMessage(),
            ]);
            return;
        } catch (Exception $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al actualizar la publicación.',
                'error'   => $e->getMessage(),
            ]);
            return;
        }
    }

    /* ======================================================================================
       ANULAR PUBLICACIÓN
       visible = 4 => anulado por el vecino
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
                $this->json(400, ['ok' => false, 'mensaje' => 'Código de publicación inválido.']);
                return;
            }

            $model = new Producto();
            $detalle = $model->obtenerPorId($codigoProducto, $codigoUsuario);
            if (!$detalle) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Publicación no encontrada para este usuario.']);
                return;
            }

            $ok = $model->anularProducto($codigoProducto, $codigoUsuario);
            if (!$ok) {
                $this->json(400, ['ok' => false, 'mensaje' => 'No se pudo anular la publicación.']);
                return;
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje'           => 'Publicación anulada correctamente.',
                'visible'           => 4,
                'servicios_piloto' => $this->respuestaLimiteServiciosPiloto(
                    $model->obtenerResumenServiciosPiloto($codigoUsuario)
                ),
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al anular la publicación.',
                'error'   => $e->getMessage(),
            ]);
            return;
        }
    }

    /* ======================================================================================
       MARKETPLACE
       - Usa residencia del visor + snapshot de publicación
       - Filtra por tipo_publicacion cuando se envía ?tipo_publicacion=producto|servicio
    ====================================================================================== */
    public function listarMarketplace(): void
    {
        FuncionalidadGuard::exigirJson(ConfiguracionPlataforma::FUNC_MARKETPLACE);

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $tipo      = $this->toIntOrNull($_GET['tipo'] ?? null);
            $categoria = $this->toIntOrNull($_GET['categoria'] ?? null);
            $q         = trim((string)($_GET['q'] ?? ''));

            $tipoPublicacionRaw = $_GET['tipo_publicacion'] ?? ($_GET['publicacion'] ?? '');
            $tipoPublicacion = trim((string)$tipoPublicacionRaw) !== ''
                ? $this->normalizarTipoPublicacion($tipoPublicacionRaw)
                : null;

            $page = max(1, (int)($_GET['page'] ?? 1));
            $size = max(1, min(50, (int)($_GET['size'] ?? 12)));

            $model = new Producto();

            $resActiva = $model->obtenerResidenciaActivaUsuario($codigoUsuario);
            if (!$resActiva) {
                $this->json(409, [
                    'ok'       => false,
                    'error'    => 'SIN_RESIDENCIA_ACTIVA',
                    'mensaje'  => 'No se encontró una residencia activa para tu usuario. Completa tu residencia para ver el Marketplace.',
                    'redirect' => rtrim(BASE_URL, '/') . '/mi-perfil',
                ]);
                return;
            }

            $conjunto = $model->obtenerNombreConjuntoActivoUsuario($codigoUsuario);
            $res = $model->listarMarketplaceFiltradoPorResidencia(
                $codigoUsuario,
                $tipo,
                $categoria,
                $q,
                $page,
                $size,
                $tipoPublicacion
            );

            $items = $res['items'] ?? [];
            $total = (int)($res['total'] ?? count($items));
            $baseUrl = rtrim(BASE_URL, '/');

            foreach ($items as &$p) {
                $ruta = (string)($p['imagen_portada'] ?? '');
                $url = ($ruta !== '') ? ($baseUrl . '/' . ltrim($ruta, '/')) : '';

                $p['tipo_publicacion'] = $this->normalizarTipoPublicacion($p['tipo_publicacion'] ?? 'producto');
                $p['imagen_portada_url'] = $url;

                if ($ruta !== '') {
                    $p['imagen_portada'] = $url;
                }

                $p['vendedor_disponible'] = ((int)($p['disponibilidad_pedidos_vendedor'] ?? 0) === 1) ? 1 : 0;
                $p['requiere_preparacion'] = ((string)($p['tipo_atencion_producto'] ?? '') === 'requiere_preparacion') ? 1 : 0;
            }
            unset($p);

            $this->json(200, [
                'ok'       => true,
                'total'    => $total,
                'page'     => $page,
                'size'     => $size,
                'data'     => $items,
                'conjunto' => $conjunto,
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, ['ok' => false, 'error' => $e->getMessage()]);
            return;
        }
    }

    public function obtenerDetalleMarketplace($id): void
    {
        FuncionalidadGuard::exigirJson(ConfiguracionPlataforma::FUNC_MARKETPLACE);

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
                return;
            }

            $codigoUsuarioViewer = $this->obtenerUsuarioAuth();
            $codigoProducto = (int)$id;

            if ($codigoProducto <= 0) {
                $this->json(400, ['ok' => false, 'mensaje' => 'Código de publicación inválido.']);
                return;
            }

            $model = new Producto();

            $resActiva = $model->obtenerResidenciaActivaUsuario($codigoUsuarioViewer);
            if (!$resActiva) {
                $this->json(409, [
                    'ok'       => false,
                    'error'    => 'SIN_RESIDENCIA_ACTIVA',
                    'mensaje'  => 'No se encontró una residencia activa para tu usuario.',
                    'redirect' => rtrim(BASE_URL, '/') . '/mi-perfil',
                ]);
                return;
            }

            $detalle = $model->obtenerDetalleMarketplacePorResidencia($codigoProducto, $codigoUsuarioViewer);
            if (!$detalle) {
                $this->json(404, [
                    'ok'      => false,
                    'mensaje' => 'La publicación no está disponible para tu marketplace.',
                ]);
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

            $detalle['tipo_publicacion'] = $this->normalizarTipoPublicacion($detalle['tipo_publicacion'] ?? 'producto');
            $detalle['vendedor_disponible'] = ((int)($detalle['disponibilidad_pedidos_vendedor'] ?? 0) === 1) ? 1 : 0;
            $detalle['es_producto_propio'] = ((int)($detalle['codigo_usuario'] ?? 0) === (int)$codigoUsuarioViewer) ? 1 : 0;
            $detalle['requiere_preparacion'] = ((string)($detalle['tipo_atencion_producto'] ?? '') === 'requiere_preparacion') ? 1 : 0;

            $this->json(200, [
                'ok'   => true,
                'data' => [
                    'producto' => $detalle,
                    'imagenes' => $imagenes,
                ],
            ]);
            return;

        } catch (Exception $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al obtener el detalle del marketplace.',
                'error'   => $e->getMessage(),
            ]);
            return;
        }
    }
}
