<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$pedido = file_get_contents($root . '/models/Pedido.php');
$marketplace = file_get_contents($root . '/views/js/marketplace.js');
$comprador = file_get_contents($root . '/views/js/misPedidosComprador.js');
$billetera = file_get_contents($root . '/models/Billetera.php');
$billeteraJs = file_get_contents($root . '/views/js/billetera.js');

$checks = [
    'Producto preparado usa billetera obligatoriamente' => str_contains($pedido, '$usarBilleteraPreparado = ($requierePrep === 1);'),
    'Débito preparado no depende del toggle de monetización' => !str_contains($pedido, 'debitoBilleteraPreparadoHabilitado'),
    'Ventana de cancelación = 120 segundos' => str_contains($pedido, 'private const SEGUNDOS_CANCELACION = 120;'),
    'Timeout vendedor = 240 segundos' => str_contains($pedido, 'private const SEGUNDOS_TIMEOUT = 240;'),
    'Reembolso obligatorio por rechazo vendedor' => str_contains($pedido, "['rechazado_vendedor', 'cancelado_vendedor', 'sin_respuesta_vendedor']"),
    'Origen unificado de devolución' => str_contains($pedido, "'PEDIDO_SOLICITUD_DEVOLUCION'"),
    'Idempotencia por movimiento existente' => str_contains($pedido, 'existeMovimientoDevolucionPedido'),
    'Preparado aceptado bloquea cancelación comprador' => str_contains($pedido, 'CANCELACION_NO_PERMITIDA_PRODUCTO_PREPARADO'),
    'Cancelación por demora queda registrada' => str_contains($pedido, 'cancelacion_solicitud_por_demora_vendedor'),
    'Protección de vencimiento bajo bloqueo' => str_contains($pedido, 'solicitudPendienteVencida'),
    'Seller cancel limpia sin_reembolso' => str_contains($pedido, 'sin_reembolso = CASE WHEN :es_cancelacion_vendedor = 1 THEN 0 ELSE sin_reembolso END'),
    'Marketplace muestra cancelación desde ventana' => str_contains($marketplace, 'SEGUNDOS_CANCELACION_SOLICITUD = 120'),
    'Marketplace timeout 4 minutos' => str_contains($marketplace, 'SEGUNDOS_TIMEOUT_SOLICITUD = 240'),
    'Saldo insuficiente ofrece acceso a Mi billetera' => str_contains($marketplace, "confirmButtonText: 'Ir a Mi billetera'") && str_contains($marketplace, "const ruta = '/billetera';"),
    'Mis pedidos habilita botón local al llegar a 2 min' => str_contains($comprador, 'data-cancel-enable-ms'),
    'Billetera reconoce ambos orígenes históricos' => str_contains($billetera, "'DEVOLUCION_PEDIDO_SOLICITUD'") && str_contains($billetera, "'PEDIDO_SOLICITUD_DEVOLUCION'"),
    'Mi billetera etiqueta débito preparado' => str_contains($billeteraJs, "PEDIDO_SOLICITUD_PREPARADA: 'Pago de producto preparado'"),
    'Mi billetera etiqueta devolución de pedido' => str_contains($billeteraJs, "PEDIDO_SOLICITUD_DEVOLUCION: 'Devolución de pedido'"),
];

$failed = [];
foreach ($checks as $name => $ok) {
    printf("[%s] %s\n", $ok ? 'OK' : 'FAIL', $name);
    if (!$ok) $failed[] = $name;
}

if ($failed) {
    fwrite(STDERR, "\nFALLARON " . count($failed) . " comprobaciones.\n");
    exit(1);
}

echo "\nTodas las comprobaciones estáticas del flujo de reembolso pasaron.\n";
