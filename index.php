<?php
// ===============================================
// index.php - Router principal con rutas estáticas
// ===============================================

// ✅ Incluir controladores necesarios
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MenuPrincipalController.php';
require_once __DIR__ . '/controllers/CondominioController.php';
// Aquí irás agregando más controladores según crezca el sistema

// Ruta base del proyecto (ajusta si cambia el nombre de la carpeta)
$basePath = '/entrevecinos';

// Obtener la URI solicitada (sin query string y limpia del basePath)
$uri = str_replace($basePath, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// ======================================================
// 1️⃣ Definir rutas estáticas
// ======================================================
$routes = [
    '' => [AuthController::class, 'loginForm'],
    '/' => [AuthController::class, 'loginForm'],
    '/login' => [AuthController::class, 'login'],
    '/MenuPrincipal' => [MenuPrincipalController::class, 'index'],
    '/registro' => [AuthController::class, 'registroForm'],

    // 🚀 Nueva ruta para tu combo condominio
    '/condominios/listar' => [CondominioController::class, 'listar'],
];

// ======================================================
// 2️⃣ Ejecutar ruta si existe
// ======================================================
if (isset($routes[$uri])) {
    [$controllerClass, $method] = $routes[$uri];
    $controller = new $controllerClass();

    // Caso especial: login requiere POST
    if ($uri === '/login' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: $basePath/");
        exit;
    }

    $controller->$method();
    exit;
}

// ======================================================
// 3️⃣ Si no existe la ruta → 404
// ======================================================
http_response_code(404);
echo "404 - Página no encontrada";
