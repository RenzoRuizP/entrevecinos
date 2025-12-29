<?php
/**
 * Listar productos destacados (pagados) para el menú principal
 * Respuesta: JSON
 *  - ok: bool
 *  - data: [
 *      { codigo_producto, titulo, precio, imagen_portada }, ...
 *    ]
 */

require_once __DIR__ . '/../Config/EnvConfig.php';
require_once __DIR__ . '/../Models/Producto.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $producto = new Producto();
    $lista = $producto->listarDestacadasPagadas(12);

    echo json_encode([
        'ok'   => true,
        'data' => $lista
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'error'   => 'ERROR_LISTAR_DESTACADAS',
        'mensaje' => 'No se pudo listar destacadas.',
        'detalle' => $e->getMessage()
    ]);
    exit;
}
