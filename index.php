<?php
// Enrutamiento centralizado
// Maneja toda la lógica de enrutamiento de la aplicación (controladores, acciones, API).

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MenuPrincipalController.php';
require_once __DIR__ . '/controllers/CondominioController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/miPerfilController.php';
require_once __DIR__ . '/controllers/api/usuarioDatosController.php';
require_once __DIR__ . '/models/SesionJWT.php'; // 👈 Importante para validar token en rutas protegidas
require_once __DIR__ . '/controllers/publicacionController.php';
require_once __DIR__ . '/controllers/tipoController.php';


// Ruta base
$basePath = '/entrevecinos';

// Obtener URI sin el prefijo base
$uri = str_replace($basePath, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$method = $_SERVER['REQUEST_METHOD'];

// ✅ Rutas públicas (NO requieren token)
$publicRoutes = [
    '#^/$#',         
    '#^/login$#',      
    '#^/usuarios/registrar$#', 
    '#^/condominios$#',            
    '#^/condominios/(\d+)/torres$#', 
    '#^/torres/(\d+)/departamentos$#',
    '#^/tipos$#',
    '#^/tipos/(\d+)/categoria_grupo$#'
];



// Definir rutas
$routes = [
    // Autenticación y menú
    ['GET', '#^/$#', [AuthController::class, 'loginForm'], 'html'],
    ['POST', '#^/login$#', [AuthController::class, 'login'], 'json'],
    ['GET', '#^/MenuPrincipal$#', [MenuPrincipalController::class, 'index'], 'html'],

    // API REST (JSON)
    ['GET', '#^/condominios$#', [CondominioController::class, 'listar'], 'json'],
    ['GET', '#^/condominios/(\d+)/torres$#', [CondominioController::class, 'listarTorres'], 'json'],
    ['GET', '#^/torres/(\d+)/departamentos$#', [CondominioController::class, 'listarDepartamentos'], 'json'],
    ['GET', '#^/tipos$#', [tipoController::class, 'listar'], 'json'],
    ['GET', '#^/tipos/(\d+)/categoria_grupo$#', [tipoController::class, 'listarCategoria_grupo'], 'json'],

    // Registro de vecinos (JSON)
    ['POST', '#^/usuarios/registrar$#', [UserController::class, 'registrar'], 'json'],

    // Logout
    ['GET', '#^/logout$#', [AuthController::class, 'logout'], 'html'],
    ['POST', '#^/logout$#', [AuthController::class, 'logout'], 'json'],

    // ****** VISTAS ******

    // ** mi perfil **
    ['GET', '#^/mi-perfil$#', [miPerfilController::class, 'index'], 'html'],
    // ** publicación **
    ['GET', '#^/publicacion$#', [publicacionController::class, 'index'], 'html'],


    // ****** FIN VISTAS ******

    // API de datos del usuario autenticado
    ['GET', '#^/api/usuario/datos$#', [usuarioDatosController::class, 'obtenerDatos'], 'json'],
    ['POST', '#^/api/usuario/actualizar$#', [usuarioDatosController::class, 'actualizarDatos'], 'json'], // 👈 NUEVA RUTA

    // API mis publicaciones
    //['GET', '#^/api/usuario/datos$#', [usuarioDatosController::class, 'obtenerDatos'], 'json'],
    ['POST', '#^/api/publicacion/registrar$#', [publicacionApiController::class, 'registrarPublicacion'], 'json'], // 👈 NUEVA RUTA
];

// Buscar coincidencia
$matched = false;
foreach ($routes as [$httpMethod, $pattern, $handler, $type]) {
    if ($method === $httpMethod && preg_match($pattern, $uri, $matches)) {
        $matched = true;

        // ✅ Verificar si es pública o protegida
        $isPublic = false;
        foreach ($publicRoutes as $publicPattern) {
            if (preg_match($publicPattern, $uri)) {
                $isPublic = true;
                break;
            }
        }

        // Si no es pública → validar token
        if (!$isPublic) {
            try {
                $token = $_COOKIE['auth_token'] ?? null;
                if (!$token) {
                    throw new Exception('Token no encontrado');
                }

                $usuario = SesionJWT::verificarToken($token);
                // Puedes usar $usuario si necesitas datos del usuario autenticado
            } catch (Exception $e) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }

        // Ejecutar controlador
        [$controllerClass, $action] = $handler;
        $controller = new $controllerClass();

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

// Si no coincide ninguna ruta
if (!$matched) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Ruta no encontrada']);
}
