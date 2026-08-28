<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$checks = [];

function ev_read(string $path): string {
    $data = @file_get_contents($path);
    return $data === false ? '' : $data;
}

function ev_add_check(array &$checks, array &$errors, string $name, bool $ok, string $detail = ''): void {
    $checks[] = [$name, $ok, $detail];
    if (!$ok) {
        $errors[] = $name . ($detail !== '' ? ': ' . $detail : '');
    }
}

$legacyTokens = [
    'posicion_' . 'cola',
    'cola_' . 'aceptada',
    'cola_' . 'pendiente_confirmacion',
    'confirmacion_' . 'cola',
    'confirmar-' . 'cola',
    'confirmar' . 'Cola',
    'puede_confirmar_' . 'cola',
    'confirmado_' . 'cola',
    'fecha_confirmacion_' . 'cola',
    'sp_pedido_reordenar_' . 'cola_vendedor',
    'sp_pedido_liberar_siguiente_' . 'cola',
];

$scanRoots = [
    $root . '/models',
    $root . '/controllers',
    $root . '/views',
    $root . '/index.php',
    $root . '/database/scriptBd',
];

$hits = [];
foreach ($scanRoots as $scanRoot) {
    $paths = [];
    if (is_file($scanRoot)) {
        $paths[] = $scanRoot;
    } elseif (is_dir($scanRoot)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (!$file->isFile()) continue;
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['php', 'js', 'sql'], true)) continue;
            $paths[] = $file->getPathname();
        }
    }

    foreach ($paths as $path) {
        $text = ev_read($path);
        foreach ($legacyTokens as $token) {
            if (stripos($text, $token) !== false) {
                $hits[] = str_replace($root . DIRECTORY_SEPARATOR, '', $path) . ' -> ' . $token;
            }
        }
    }
}
ev_add_check($checks, $errors, 'Sin referencias legacy en código/esquemas activos', !$hits, implode('; ', array_slice($hits, 0, 12)));

$pedido = ev_read($root . '/models/Pedido.php');
ev_add_check(
    $checks,
    $errors,
    'Solicitudes nuevas inician en pendiente_vendedor',
    strpos($pedido, "\$estadoActual = 'pendiente_vendedor';") !== false
);
ev_add_check(
    $checks,
    $errors,
    'Aceptación valida solo el pedido solicitado',
    strpos($pedido, "estado_actual'] !== 'pendiente_vendedor'") !== false
        && strpos($pedido, 'VENDEDOR_CON_TURNO_ACTIVO') === false
        && strpos($pedido, 'vendedorTieneTurnoActivo') === false
        && strpos($pedido, 'vendedorTieneOtroTurnoActivo') === false
);
ev_add_check(
    $checks,
    $errors,
    'INSERT de pedido no depende de secuenciamiento',
    strpos($pedido, 'INSERT INTO pedido') !== false
        && strpos($pedido, 'posicion_' . 'cola') === false
);

$routes = ev_read($root . '/index.php');
ev_add_check(
    $checks,
    $errors,
    'Ruta legacy eliminada',
    strpos($routes, '/confirmar-' . 'cola') === false
);

$cleanDump = $root . '/database/scriptBd/EV_bk_25082026_limpio_sin_colas.sql';
$dumpText = ev_read($cleanDump);
ev_add_check(
    $checks,
    $errors,
    'Dump limpio incluido',
    $dumpText !== ''
);
if ($dumpText !== '') {
    $dumpHits = [];
    foreach ($legacyTokens as $token) {
        if (stripos($dumpText, $token) !== false) $dumpHits[] = $token;
    }
    ev_add_check($checks, $errors, 'Dump limpio sin identificadores legacy', !$dumpHits, implode(', ', $dumpHits));
}

foreach ($checks as [$name, $ok, $detail]) {
    echo ($ok ? '[OK] ' : '[ERROR] ') . $name;
    if ($detail !== '') echo ' - ' . $detail;
    echo PHP_EOL;
}

if ($errors) {
    echo PHP_EOL . 'Auditoría de pedidos concurrentes: FALLÓ' . PHP_EOL;
    exit(1);
}

echo PHP_EOL . 'Auditoría de pedidos concurrentes: OK' . PHP_EOL;
