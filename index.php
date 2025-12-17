<?php
// ============================================================
// Enrutamiento centralizado
// ============================================================

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
 * - fetch/ajax clásico
 * - header X-Partial: 1
 * - querystring ?partial=1
 */
function esPeticionParcial(): bool
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }
    if (isset($_SERVER['HTTP_X_PARTIAL']) && $_SERVER['HTTP_X_PARTIAL'] === '1') {
        return true;
    }
    if (isset($_GET['partial']) && $_GET['partial'] === '1') {
        return true;
    }
    return false;
}

// ------------------------------
// 1) Dependencias
// ------------------------------
safeRequire(__DIR__ . '/Config/config.php', true); // BASE_URL (crítica)

safeRequire(__DIR__ . '/models/SesionJWT.php', true);

// Controllers (si falta alguno NO debe tumbar el sistema)
safeRequire(__DIR__ . '/controllers/AuthController.php');
safeRequire(__DIR__ . '/controllers/MenuPrincipalController.php');
safeRequire(__DIR__ . '/controllers/CondominioController.php');
safeRequire(__DIR__ . '/controllers/UserController.php');
safeRequire(__DIR__ . '/controllers/miPerfilController.php');
safeRequire(__DIR__ . '/controllers/publicacionController.php');
safeRequire(__DIR__ . '/controllers/tipoController.php');
safeRequire(__DIR__ . '/controllers/marketplaceController.php');
safeRequire(__DIR__ . '/controllers/billeteraController.php');
safeRequire(__DIR__ . '/controllers/credencialController.php');
safeRequire(__DIR__ . '/controllers/recibirPedidosController.php');
safeRequire(__DIR__ . '/controllers/atenderRecargasController.php');

// API Controllers
safeRequire(__DIR__ . '/controllers/api/usuarioDatosController.php');
safeRequire(__DIR__ . '/controllers/api/apiPublicacionController.php');
safeRequire(__DIR__ . '/controllers/api/apiBilleteraController.php');
safeRequire(__DIR__ . '/controllers/api/apiPedidoController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteRecargasController.php');

// OJO: este es el que te estaba tumbando el sistema si no existía
safeRequire(__DIR__ . '/controllers/api/apiRecargaSaldoController.php');

// ------------------------------
// 2) Normalización de ruta base
// ------------------------------
$basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
if ($basePath === '') {
    $basePath = '/';
}

// ------------------------------
// 3) Parseo de la solicitud
// ------------------------------
$uriFull = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uriFull);
$uri = ($uri === '') ? '/' : $uri;

$method = $_SERVER['REQUEST_METHOD'];

// ------------------------------
// 4) Rutas públicas (no token)
// ------------------------------
$publicRoutes = [
    '#^/$#',
    '#^/login$#',
    '#^/usuarios/registrar$#',
    '#^/condominios$#',
    '#^/condominios/(\d+)/torres$#',
    '#^/torres/(\d+)/departamentos$#',
    '#^/tipos$#',
    '#^/tipos/(\d+)/categoria_grupo$#',
];

// ------------------------------
// 5) Definición de rutas
// ------------------------------
$routes = [

    // --- Autenticación y menú ---
    ['GET',  '#^/$#',                      [AuthController::class, 'loginForm'], 'html'],
    ['GET',  '#^/login$#',                 [AuthController::class, 'loginForm'], 'html'],
    ['POST', '#^/login$#',                 [AuthController::class, 'login'],     'json'],
    ['GET',  '#^/MenuPrincipal$#',         [MenuPrincipalController::class, 'index'], 'html'],

    // --- API REST Condominios/Torres/Departamentos ---
    ['GET',  '#^/condominios$#',                 [CondominioController::class, 'listar'],             'json'],
    ['GET',  '#^/condominios/(\d+)/torres$#',    [CondominioController::class, 'listarTorres'],       'json'],
    ['GET',  '#^/torres/(\d+)/departamentos$#',  [CondominioController::class, 'listarDepartamentos'],'json'],

    // --- Tipos / Categorías ---
    ['GET',  '#^/tipos$#',                        [tipoController::class, 'listar'],                'json'],
    ['GET',  '#^/tipos/(\d+)/categoria_grupo$#',  [tipoController::class, 'listarCategoria_grupo'], 'json'],

    // --- Registro de vecinos ---
    ['POST', '#^/usuarios/registrar$#', [UserController::class, 'registrar'], 'json'],

    // --- Logout ---
    ['GET',  '#^/logout$#', [AuthController::class, 'logout'], 'html'],
    ['POST', '#^/logout$#', [AuthController::class, 'logout'], 'json'],

    // ------------------------------
    //  VISTAS
    // ------------------------------
    ['GET', '#^/mi-perfil$#',        [miPerfilController::class, 'index'],       'html'],
    ['GET', '#^/publicacion$#',      [publicacionController::class, 'index'],    'html'],
    ['GET', '#^/marketplace$#',      [marketplaceController::class, 'index'],    'html'],
    ['GET', '#^/billetera$#',        [billeteraController::class, 'index'],      'html'],
    ['GET', '#^/credencial$#',       [credencialController::class, 'index'],     'html'],
    ['GET', '#^/recibir$#',          [recibirPedidosController::class, 'index'], 'html'],
    ['GET', '#^/atender-recargas$#', [atenderRecargasController::class, 'index'], 'html'],

    // ------------------------------
    //  API (JSON)
    // ------------------------------
    ['GET',  '#^/api/usuario/datos$#',      [usuarioDatosController::class, 'obtenerDatos'],    'json'],
    ['POST', '#^/api/usuario/actualizar$#', [usuarioDatosController::class, 'actualizarDatos'], 'json'],

    ['POST', '#^/api/publicacion/registrar$#',        [apiPublicacionController::class, 'registrarPublicacion'], 'json'],
    ['GET',  '#^/api/publicacion/listar$#',           [apiPublicacionController::class, 'listarPublicaciones'],  'json'],
    ['GET',  '#^/api/publicacion/(\d+)$#',            [apiPublicacionController::class, 'obtenerPublicacion'],   'json'],
    ['POST', '#^/api/publicacion/(\d+)/actualizar$#', [apiPublicacionController::class, 'actualizarPublicacion'],'json'],
    ['POST', '#^/api/publicacion/(\d+)/anular$#',     [apiPublicacionController::class, 'anularPublicacion'],    'json'],
    ['POST', '#^/api/publicacion/(\d+)/publicar$#',   [apiPublicacionController::class, 'publicarPublicacion'],  'json'],
    ['GET',  '#^/api/publicacion/detalle/(\d+)$#',     [apiPublicacionController::class, 'detallePublicacion'],   'json'],
    ['GET',  '#^/api/publicacion/listar-publicadas$#', [apiPublicacionController::class, 'listarPublicadasMarketplace'], 'json'],

    ['GET',  '#^/api/billetera/saldo$#',                [apiBilleteraController::class, 'obtenerSaldo'],       'json'],
    ['GET',  '#^/api/billetera/movimientos$#',          [apiBilleteraController::class, 'obtenerMovimientos'], 'json'],
    ['POST', '#^/api/billetera/debitar-publicacion$#',  [apiBilleteraController::class, 'debitarPublicacion'], 'json'],

    ['GET',  '#^/api/pedidos/recibir$#', [apiPedidoController::class, 'listarPedidos'], 'json'],

    ['GET',  '#^/api/soporte/recargas$#',              [apiSoporteRecargasController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/recargas/(\d+)/estado$#', [apiSoporteRecargasController::class, 'actualizarEstado'], 'json'],

    ['POST', '#^/api/recargas/registrar$#', [apiRecargaSaldoController::class, 'registrar'], 'json'],
];

// ============================================================
// 6) Resolución de rutas
// ============================================================
$matched = false;

foreach ($routes as [$httpMethod, $pattern, $handler, $type]) {
    if ($method === $httpMethod && preg_match($pattern, $uri, $matches)) {
        $matched = true;

        // 6.1 ¿Es pública?
        $isPublic = false;
        foreach ($publicRoutes as $publicPattern) {
            if (preg_match($publicPattern, $uri)) {
                $isPublic = true;
                break;
            }
        }

        // 6.2 Validar token si no es pública
        if (!$isPublic) {
            try {
                $token = $_COOKIE['auth_token'] ?? null;
                if (!$token) {
                    throw new Exception('Token no encontrado');
                }
                $usuario = SesionJWT::verificarToken($token);
                if (!$usuario) {
                    throw new Exception('Token inválido o expirado');
                }
            } catch (Exception $e) {

                // IMPORTANTE:
                // Si es parcial (AJAX/fetch), SIEMPRE responder JSON 401
                if (esPeticionParcial() || $type === 'json') {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(401);
                    echo json_encode([
                        'ok'      => false,
                        'error'   => 'TOKEN_INVALIDO_O_EXPIRADO',
                        'mensaje' => 'Tu sesión ha expirado por seguridad. Vuelve a iniciar sesión.',
                        'motivo'  => $e->getMessage()
                    ]);
                    exit;
                }

                // Navegación normal HTML: SweetAlert + redirección
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
                    <style>
                        .ev-swal-title { font-weight: 700; color: #1A1F36; }
                        .ev-swal-text  { color: #4B5563; font-size: 0.95rem; }
                    </style>
                </head>
                <body>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Tu sesión ha finalizado',
                            text: 'Por tu seguridad, tu sesión en Entre Vecinos ha expirado. Vuelve a iniciar sesión para continuar.',
                            icon: 'info',
                            iconColor: '#198754',
                            confirmButtonText: 'Ir al inicio de sesión',
                            confirmButtonColor: '#198754',
                            background: '#FFFFFF',
                            customClass: { title: 'ev-swal-title', htmlContainer: 'ev-swal-text' },
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(function () {
                            window.location.href = '<?= rtrim(BASE_URL, '/'); ?>/login';
                        });
                    });
                </script>
                </body>
                </html>
                <?php
                exit;
            }
        }

        // 6.3 Ejecutar controlador
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

        // Content-Type
        if ($type === 'json') {
            header('Content-Type: application/json; charset=utf-8');
        } else {
            header('Content-Type: text/html; charset=utf-8');
        }

        array_shift($matches);
        call_user_func_array([$controller, $action], $matches);
        break;
    }
}

// ============================================================
// 7) 404 si ninguna ruta coincidió
// ============================================================
if (!$matched) {
    http_response_code(404);

    // Si es parcial o API, devuelve JSON limpio
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'       => false,
        'error'    => 'RUTA_NO_ENCONTRADA',
        'uri'      => $uri,
        'basePath' => $basePath
    ]);
}
