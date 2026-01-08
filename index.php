<?php
// ============================================================
// index.php — Enrutamiento centralizado (EV)
// ============================================================

declare(strict_types=1);

// ------------------------------
// Helpers (blindaje)
// ------------------------------
function safeRequire(string $path, bool $critical = false): void
{
    if (file_exists($path)) {
        require_once $path;
        return;
    }

    error_log("[EV][INDEX] Missing require: {$path}");

    if ($critical) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Error interno: falta dependencia crítica.\n{$path}";
        exit;
    }
}

/**
 * Detecta si la petición es parcial/AJAX.
 */
function esPeticionParcial(): bool
{
    if (!empty($_GET['partial']) && $_GET['partial'] === '1') return true;

    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    if ($xrw && strtolower($xrw) === 'xmlhttprequest') return true;

    $xp = $_SERVER['HTTP_X_PARTIAL'] ?? '';
    if ($xp === '1') return true;

    return false;
}

function evBaseUrl(): string
{
    $b = defined('BASE_URL') ? (string)BASE_URL : '/';
    $b = trim($b);
    if ($b === '') return '/';
    if ($b[0] !== '/') $b = '/' . $b;
    $b = rtrim($b, '/') . '/';
    $b = preg_replace('#/+#', '/', $b);
    return $b ?: '/';
}

function evBasePathFromBaseUrl(string $baseUrl): string
{
    $path = parse_url($baseUrl, PHP_URL_PATH);
    $path = is_string($path) ? $path : '/';
    $path = preg_replace('#/+#', '/', $path);
    $path = rtrim($path, '/');
    return ($path === '') ? '/' : $path;
}

function evRenderSesionFinalizada(string $loginUrl): void
{
    http_response_code(401);
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sesión finalizada | Entre Vecinos</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'info',
                title: 'Tu sesión ha finalizado',
                text: 'Por tu seguridad, la sesión expiró. Vuelve a iniciar sesión.',
                confirmButtonText: 'Ir al login',
                confirmButtonColor: '#EA7C12',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function () {
                window.location.href = <?php echo json_encode($loginUrl); ?>;
            });
        });
    </script>
    </body>
    </html>
    <?php
    exit;
}

// ------------------------------
// 1) Dependencias
// ------------------------------
safeRequire(__DIR__ . '/Config/config.php', true);
safeRequire(__DIR__ . '/models/SesionJWT.php', true);

// ✅ rol admin por defecto (si no lo definiste en config)
if (!defined('EV_ADMIN_ROLE_ID')) {
    define('EV_ADMIN_ROLE_ID', 1);
}

// Controllers (Vistas)
safeRequire(__DIR__ . '/controllers/AuthController.php');
safeRequire(__DIR__ . '/controllers/MenuPrincipalController.php');
safeRequire(__DIR__ . '/controllers/CondominioController.php');
safeRequire(__DIR__ . '/controllers/UrbanizacionController.php');
safeRequire(__DIR__ . '/controllers/UbigeoController.php');
safeRequire(__DIR__ . '/controllers/UserController.php');
safeRequire(__DIR__ . '/controllers/miPerfilController.php');
safeRequire(__DIR__ . '/controllers/productoController.php');
safeRequire(__DIR__ . '/controllers/tipoController.php');
safeRequire(__DIR__ . '/controllers/marketplaceController.php');
safeRequire(__DIR__ . '/controllers/billeteraController.php');
safeRequire(__DIR__ . '/controllers/credencialController.php');
safeRequire(__DIR__ . '/controllers/recibirPedidosController.php');
safeRequire(__DIR__ . '/controllers/atenderRecargasController.php');
safeRequire(__DIR__ . '/controllers/atenderPublicacionController.php');
safeRequire(__DIR__ . '/controllers/atenderCuentasUsuarioController.php');

// API Controllers
safeRequire(__DIR__ . '/controllers/api/usuarioDatosController.php');
safeRequire(__DIR__ . '/controllers/api/apiBilleteraController.php');
safeRequire(__DIR__ . '/controllers/api/apiPedidoController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteRecargasController.php');
safeRequire(__DIR__ . '/controllers/api/apiRecargaSaldoController.php');
safeRequire(__DIR__ . '/controllers/api/apiProductoController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteProductosController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteUsuariosController.php');

// ✅ NUEVO: soporte cambios de residencia (admin)
safeRequire(__DIR__ . '/controllers/api/apiSoporteResidenciasController.php');

// ------------------------------
// 2) Normalización BASE_URL / basePath
// ------------------------------
$baseUrl  = evBaseUrl();                      // "/entrevecinos/"
$basePath = evBasePathFromBaseUrl($baseUrl);  // "/entrevecinos"

// ------------------------------
// 3) Parseo URI
// ------------------------------
$uriFull = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uriFull = is_string($uriFull) ? $uriFull : '/';
$uriFull = preg_replace('#/+#', '/', $uriFull);

if ($basePath !== '/' && str_starts_with($uriFull, $basePath)) {
    $uri = substr($uriFull, strlen($basePath));
} else {
    $uri = $uriFull;
}

$uri = preg_replace('#/+#', '/', $uri);
$uri = rtrim($uri, '/');
$uri = ($uri === '') ? '/' : $uri;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ------------------------------
// 4) Rutas públicas (sin token)
// ------------------------------
$publicRoutes = [
    '#^/$#',
    '#^/login$#',
    '#^/usuarios/registrar$#',

    // filtros por distrito (siguen siendo públicos)
    '#^/condominios$#',
    '#^/urbanizaciones$#',

    // combos antiguos
    '#^/condominios/(\d+)/torres$#',
    '#^/torres/(\d+)/departamentos$#',
    '#^/tipos$#',
    '#^/tipos/(\d+)/categoria_grupo$#',

    // ubigeo
    '#^/ubigeo/departamentos$#',
    '#^/ubigeo/departamentos/(\d+)/provincias$#',
    '#^/ubigeo/provincias/(\d+)/distritos$#',
];

// ------------------------------
// 5) Rutas
// ------------------------------
$routes = [

    // --- Auth / Menú ---
    ['GET',  '#^/$#',              [AuthController::class, 'loginForm'], 'html'],
    ['GET',  '#^/login$#',         [AuthController::class, 'loginForm'], 'html'],
    ['POST', '#^/login$#',         [AuthController::class, 'login'],     'json'],
    ['GET',  '#^/MenuPrincipal$#', [MenuPrincipalController::class, 'index'], 'html'],

    // --- Condominios ---
    ['GET',  '#^/condominios$#',                [CondominioController::class, 'listar'],              'json'],
    ['GET',  '#^/condominios/(\d+)/torres$#',   [CondominioController::class, 'listarTorres'],        'json'],
    ['GET',  '#^/torres/(\d+)/departamentos$#', [CondominioController::class, 'listarDepartamentos'], 'json'],

    // --- Urbanizaciones ---
    ['GET',  '#^/urbanizaciones$#',             [UrbanizacionController::class, 'listar'],            'json'],

    // --- Ubigeo ---
    ['GET',  '#^/ubigeo/departamentos$#',                  [UbigeoController::class, 'departamentos'], 'json'],
    ['GET',  '#^/ubigeo/departamentos/(\d+)/provincias$#', [UbigeoController::class, 'provincias'],    'json'],
    ['GET',  '#^/ubigeo/provincias/(\d+)/distritos$#',     [UbigeoController::class, 'distritos'],     'json'],

    // --- Tipos / Categorías ---
    ['GET',  '#^/tipos$#',                       [tipoController::class, 'listar'],                'json'],
    ['GET',  '#^/tipos/(\d+)/categoria_grupo$#', [tipoController::class, 'listarCategoria_grupo'], 'json'],

    // --- Registro ---
    ['POST', '#^/usuarios/registrar$#', [UserController::class, 'registrar'], 'json'],

    // --- Logout ---
    ['GET',  '#^/logout$#', [AuthController::class, 'logout'], 'html'],
    ['POST', '#^/logout$#', [AuthController::class, 'logout'], 'json'],

    // --- Vistas ---
    ['GET', '#^/mi-perfil$#',        [miPerfilController::class, 'index'],        'html'],
    ['GET', '#^/publicacion$#',      [productoController::class, 'index'],        'html'], // legacy
    ['GET', '#^/producto$#',         [productoController::class, 'index'],        'html'],
    ['GET', '#^/marketplace$#',      [marketplaceController::class, 'index'],     'html'],
    ['GET', '#^/billetera$#',        [billeteraController::class, 'index'],       'html'],
    ['GET', '#^/credencial$#',       [credencialController::class, 'index'],      'html'],
    ['GET', '#^/recibir$#',          [recibirPedidosController::class, 'index'],  'html'],
    ['GET', '#^/atender-recargas$#', [atenderRecargasController::class, 'index'], 'html'],
    ['GET', '#^/atender-publicacion$#', [atenderPublicacionController::class, 'index'], 'html'],

    // ✅ IMPORTANTE: ruta real del menú (BD) => /atender-cuentas
    ['GET', '#^/atender-cuentas$#', [atenderCuentasUsuarioController::class, 'index'], 'html'],
    // ✅ Compatibilidad
    ['GET', '#^/atender-cuentas-usuario$#', [atenderCuentasUsuarioController::class, 'index'], 'html'],

    // --- API Usuario ---
    ['GET',  '#^/api/usuario/datos$#',       [usuarioDatosController::class, 'obtenerDatos'],     'json'],
    ['POST', '#^/api/usuario/actualizar$#',  [usuarioDatosController::class, 'actualizarDatos'],  'json'],

    // ✅ NUEVO: Solicitud de cambio de residencia (con upload)
    // Nota: esto lo atiende usuarioDatosController (o el controller que ya uses), pero aquí queda la ruta.
    ['POST', '#^/api/usuario/solicitar-cambio-residencia$#', [usuarioDatosController::class, 'solicitarCambioResidencia'], 'json'],

    // --- API Producto ---
    ['POST', '#^/api/producto/registrar$#',        [apiProductoController::class, 'registrarProducto'],  'json'],
    ['GET',  '#^/api/producto/listar$#',           [apiProductoController::class, 'listarProductos'],    'json'],
    ['GET',  '#^/api/producto/(\d+)$#',            [apiProductoController::class, 'obtenerProducto'],    'json'],
    ['POST', '#^/api/producto/(\d+)/actualizar$#', [apiProductoController::class, 'actualizarProducto'], 'json'],
    ['POST', '#^/api/producto/(\d+)/anular$#',     [apiProductoController::class, 'anularProducto'],     'json'],
    ['POST', '#^/api/producto/(\d+)/publicar$#',   [apiProductoController::class, 'publicarProducto'],   'json'],
    ['GET',  '#^/api/producto/marketplace$#',      [apiProductoController::class, 'listarMarketplace'],  'json'],

    // --- API Billetera ---
    ['GET',  '#^/api/billetera/saldo$#',               [apiBilleteraController::class, 'obtenerSaldo'],        'json'],
    ['GET',  '#^/api/billetera/movimientos$#',         [apiBilleteraController::class, 'obtenerMovimientos'],  'json'],
    ['POST', '#^/api/billetera/debitar-publicacion$#', [apiBilleteraController::class, 'debitarPublicacion'],  'json'],
    ['POST', '#^/api/billetera/debitar-producto-destacado$#', [apiBilleteraController::class, 'debitarPublicacion'], 'json'],

    // --- API Pedidos ---
    ['GET',  '#^/api/pedidos/recibir$#',               [apiPedidoController::class, 'listarPedidos'],           'json'],

    // --- API Soporte Recargas ---
    ['GET',  '#^/api/soporte/recargas$#',              [apiSoporteRecargasController::class, 'listar'],           'json'],
    ['POST', '#^/api/soporte/recargas/(\d+)/estado$#', [apiSoporteRecargasController::class, 'actualizarEstado'], 'json'],

    // --- API Soporte Productos ---
    ['GET',  '#^/api/soporte/productos$#',              [apiSoporteProductosController::class, 'listar'], 'json'],
    ['GET',  '#^/api/soporte/productos/(\d+)$#',        [apiSoporteProductosController::class, 'detalle'], 'json'],
    ['POST', '#^/api/soporte/productos/(\d+)/estado$#', [apiSoporteProductosController::class, 'actualizarEstado'], 'json'],

    // ✅ API Soporte Usuarios (Atender cuentas)
    ['GET',  '#^/api/soporte/usuarios$#',              [apiSoporteUsuariosController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/usuarios/(\d+)/estado$#', [apiSoporteUsuariosController::class, 'actualizarEstado'], 'json'],

    // ✅ NUEVO: API Soporte Residencias (Atender cuentas: cambios de residencia)
    ['GET',  '#^/api/soporte/residencias$#',              [apiSoporteResidenciasController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/residencias/(\d+)/estado$#', [apiSoporteResidenciasController::class, 'actualizarEstado'], 'json'],

    // --- API Recargas ---
    ['POST', '#^/api/recargas/registrar$#',            [apiRecargaSaldoController::class, 'registrar'], 'json'],
];

// ============================================================
// 6) Resolver rutas
// ============================================================
$matched = false;

foreach ($routes as $r) {
    [$httpMethod, $pattern, $handler, $type] = $r;

    if ($method !== $httpMethod) continue;
    if (!preg_match($pattern, $uri, $matches)) continue;

    $matched = true;

    // ¿Pública?
    $isPublic = false;
    foreach ($publicRoutes as $publicPattern) {
        if (preg_match($publicPattern, $uri)) {
            $isPublic = true;
            break;
        }
    }

    // Validación JWT si NO es pública
    if (!$isPublic) {
        $token = $_COOKIE['auth_token'] ?? null;
        $loginUrl = rtrim($baseUrl, '/') . '/login';

        $rTok = SesionJWT::verificarTokenDetallado($token);

        if (!$rTok['ok']) {
            $motivo = ($rTok['error'] ?? '') === 'TOKEN_EXPIRADO' ? 'token_expirado' : 'token_invalido';

            if (esPeticionParcial() || $type === 'json' || str_starts_with($uri, '/api/')) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED', 'motivo' => $motivo]);
                exit;
            }

            evRenderSesionFinalizada($loginUrl);
        }

        $GLOBALS['EV_AUTH'] = $rTok['data'] ?? [];
    }

    // Ejecutar controlador
    [$controllerClass, $action] = $handler;

    if (!class_exists($controllerClass)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'      => false,
            'error'   => 'CONTROLADOR_NO_DISPONIBLE',
            'mensaje' => "No se encontró el controlador {$controllerClass}. Revisa require/autoload."
        ]);
        exit;
    }

    $controller = new $controllerClass();

    if (!headers_sent()) {
        header('Content-Type: ' . ($type === 'json' ? 'application/json' : 'text/html') . '; charset=utf-8');
    }

    array_shift($matches);
    call_user_func_array([$controller, $action], $matches);
    break;
}

// ============================================================
// 7) 404
// ============================================================
if (!$matched) {
    $isJson = esPeticionParcial() || str_starts_with($uri, '/api/');
    http_response_code(404);

    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'       => false,
            'error'    => 'RUTA_NO_ENCONTRADA',
            'uri'      => $uri,
            'basePath' => $basePath
        ]);
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');
    echo "<h1 style='font-family:system-ui;padding:24px'>404</h1><p style='font-family:system-ui;padding:0 24px'>Ruta no encontrada.</p>";
}
