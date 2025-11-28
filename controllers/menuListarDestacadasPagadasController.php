<?php
/**
 * Listar publicaciones destacadas (pagadas) para el menú principal
 * Respuesta: JSON
 *  - ok: bool
 *  - data: [
 *      { codigo_publicacion, titulo, precio, imagen_portada }, ...
 *    ]
 */

require_once __DIR__ . '/../Config/EnvConfig.php';
require_once __DIR__ . '/../Models/Publicacion.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $publicacion = new Publicacion();

    // Obtiene las publicaciones destacadas/pagadas
    $lista = $publicacion->listarDestacadasPagadas();

    echo json_encode([
        'ok'   => true,
        'data' => $lista
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage()
    ]);
    exit;
}
