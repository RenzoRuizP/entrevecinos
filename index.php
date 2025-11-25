<?php
// ============================================================
// Enrutamiento centralizado
// ============================================================

// ------------------------------
// 1) Dependencias
// ------------------------------
require_once __DIR__ . '/Config/config.php'; // Para usar BASE_URL

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MenuPrincipalController.php';
require_once __DIR__ . '/controllers/CondominioController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/miPerfilController.php';
require_once __DIR__ . '/controllers/api/usuarioDatosController.php';
require_once __DIR__ . '/controllers/publicacionController.php';
require_once __DIR__ . '/controllers/api/apiPublicacionController.php';
require_once __DIR__ . '/controllers/tipoController.php';
require_once __DIR__ . '/controllers/marketplaceController.php';

require_once __DIR__ . '/models/SesionJWT.php';

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
//    Formato: [METHOD, PATRÓN, [Controlador, Acción], tipo_respuesta]
//    tipo_respuesta: 'html' | 'json'
// ------------------------------
$routes = [

    // --- Autenticación y menú ---
    ['GET',  '#^/$#',                      [AuthController::class, 'loginForm'], 'html'],
    ['POST', '#^/login$#',                 [AuthController::class, 'login'],     'json'],
    ['GET',  '#^/MenuPrincipal$#',         [MenuPrincipalController::class, 'index'], 'html'],

    // --- API REST (Condominios/Torres/Departamentos) ---
    ['GET',  '#^/condominios$#',                 [CondominioController::class, 'listar'],             'json'],
    ['GET',  '#^/condominios/(\d+)/torres$#',    [CondominioController::class, 'listarTorres'],       'json'],
    ['GET',  '#^/torres/(\d+)/departamentos$#',  [CondominioController::class, 'listarDepartamentos'], 'json'],

    // --- API REST (Tipos / Categorías por Grupo) ---
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
    ['GET', '#^/mi-perfil$#',   [miPerfilController::class, 'index'], 'html'],
    ['GET', '#^/publicacion$#', [publicacionController::class, 'index'], 'html'],
    ['GET', '#^/marketplace$#', [marketplaceController::class, 'index'], 'html'],
    // ------------------------------
    //  FIN VISTAS
    // ------------------------------

    // --- API de datos del usuario autenticado ---
    ['GET',  '#^/api/usuario/datos$#',      [usuarioDatosController::class, 'obtenerDatos'],    'json'],
    ['POST', '#^/api/usuario/actualizar$#', [usuarioDatosController::class, 'actualizarDatos'], 'json'],

    // --- API mis publicaciones ---
    ['POST', '#^/api/publicacion/registrar$#',       [apiPublicacionController::class, 'registrarPublicacion'], 'json'],
    ['GET',  '#^/api/publicacion/listar$#',          [apiPublicacionController::class, 'listarPublicaciones'],  'json'],
    ['GET',  '#^/api/publicacion/(\d+)$#',           [apiPublicacionController::class, 'obtenerPublicacion'],   'json'],
    // *** RUTA NUEVA: ACTUALIZAR PUBLICACIÓN ***
    ['POST', '#^/api/publicacion/(\d+)/actualizar$#',[apiPublicacionController::class, 'actualizarPublicacion'],'json'],


    // *** RUTA NUEVA: DETALLE DE PUBLICACIÓN ***
    ['GET',  '#^/api/publicacion/(\d+)$#',     [apiPublicacionController::class, 'obtenerPublicacion'],   'json'],
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

        // 6.2 Si es protegida, validar token
        if (!$isPublic) {
            try {
                $token = $_COOKIE['auth_token'] ?? null;
                if (!$token) {
                    throw new Exception('Token no encontrado');
                }
                $usuario = SesionJWT::verificarToken($token);
                // $usuario disponible si se requiere
            } catch (Exception $e) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }

        // 6.3 Ejecutar controlador
        [$controllerClass, $action] = $handler;
        $controller = new $controllerClass();

        if ($type === 'json') {
            header('Content-Type: application/json; charset=utf-8');
        } else {
            header('Content-Type: text/html; charset=utf-8');
        }

        array_shift($matches); // descartar coincidencia completa
        call_user_func_array([$controller, $action], $matches);
        break;
    }
}

// ============================================================
// 7) 404 si ninguna ruta coincidió
// ============================================================
if (!$matched) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error'    => 'Ruta no encontrada',
        'uri'      => $uri,
        'basePath' => $basePath
    ]);
}
