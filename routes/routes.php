<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/Controllers/CondominioController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/entrevecinos/condominios/listar':
        $controller = new CondominioController();
        $controller->obtenerCondominios();
        break;

    default:
        echo json_encode([
            "status" => "error",
            "message" => "Ruta no encontrada",
            "uri" => $uri
        ]);
        break;
}
