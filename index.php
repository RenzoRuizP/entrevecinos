<?php
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MenuPrincipalController.php';

// Ruta base de tu proyecto (ajústala si la carpeta tiene otro nombre)
$basePath = '/entrevecinos';

// Obtener la URI sin el prefijo /entrevecinos
$uri = str_replace($basePath, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

switch ($uri) {
    case '':
    case '/':
        $controller = new AuthController();
        $controller->loginForm();
        break;

    case '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new AuthController();
            $controller->login();
        } else {
            header("Location: /entrevecinos/");
        }
        break;

    case '/MenuPrincipal':
        $controller = new MenuPrincipalController();
        $controller->index();
        break;

    default:
        http_response_code(404);
        echo "404 - Página no encontrada";
        break;
}
