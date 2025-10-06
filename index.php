<?php
// Maneja toda la lógica de enrutamiento de la aplicación (controladores, acciones, API).

// Cargar controladores
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MenuPrincipalController.php';
require_once __DIR__ . '/controllers/CondominioController.php';
require_once __DIR__ . '/controllers/UserController.php';

// Ruta base
$basePath = '/entrevecinos';

// Obtener URI sin el prefijo base
$uri = str_replace($basePath, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$method = $_SERVER['REQUEST_METHOD'];

// Definir rutas (estáticas y dinámicas)
$routes = [
    // Autenticación y menú (devuelven HTML o JSON según caso)
    ['GET', '#^/$#', [AuthController::class, 'loginForm'], 'html'],
    ['POST', '#^/login$#', [AuthController::class, 'login'], 'json'],
    ['GET', '#^/MenuPrincipal$#', [MenuPrincipalController::class, 'index'], 'html'],

    // API REST (devuelven JSON)
    ['GET', '#^/condominios$#', [CondominioController::class, 'listar'], 'json'],
    ['GET', '#^/condominios/(\d+)/torres$#', [CondominioController::class, 'listarTorres'], 'json'],
    ['GET', '#^/torres/(\d+)/departamentos$#', [CondominioController::class, 'listarDepartamentos'], 'json'],

    // Registro de vecinos (JSON)
    ['POST', '#^/usuarios/registrar$#', [UserController::class, 'registrar'], 'json'],

    // Logout
    ['GET', '#^/logout$#', [AuthController::class, 'logout'], 'html'],
];

// Buscar coincidencia
$matched = false;
foreach ($routes as [$httpMethod, $pattern, $handler, $type]) {
    if ($method === $httpMethod && preg_match($pattern, $uri, $matches)) {
        [$controllerClass, $action] = $handler;
        $controller = new $controllerClass();

        // 🔹 Header según tipo de respuesta
        if ($type === 'json') {
            header('Content-Type: application/json; charset=utf-8');
        } else {
            header('Content-Type: text/html; charset=utf-8');
        }

        // Si hay parámetros dinámicos, se pasan al método
        array_shift($matches);
        call_user_func_array([$controller, $action], $matches);

        $matched = true;
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Ruta no encontrada']);
}
