<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../controllers/CondominioController.php';

$uri = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$basePath = rtrim((string)(parse_url(BASE_URL, PHP_URL_PATH) ?: ''), '/');

if ($basePath !== '' && ($uri === $basePath || str_starts_with($uri, $basePath . '/'))) {
    $uri = substr($uri, strlen($basePath));
}

$uri = '/' . ltrim($uri, '/');

switch ($uri) {
    case '/condominios/listar':
        $controller = new CondominioController();
        $controller->obtenerCondominios();
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Ruta no encontrada',
            'uri' => $uri,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
}
