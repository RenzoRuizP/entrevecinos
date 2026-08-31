<?php
declare(strict_types=1);

// Procesador CLI de cierres de retiro. Recomendado: ejecutar cada minuto en QA/PROD.
// También existe procesamiento perezoso desde las APIs como respaldo.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../models/Retiro.php';

$lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ev_procesar_retiros.lock';
$lock = fopen($lockPath, 'c');
if (!$lock) {
    fwrite(STDERR, "No se pudo crear el lock de retiros.\n");
    exit(1);
}

if (!flock($lock, LOCK_EX | LOCK_NB)) {
    fclose($lock);
    exit(0);
}

try {
    (new Retiro())->procesarSolicitudesVencidas();
    exit(0);
} catch (Throwable $e) {
    error_log('[EV][cron][procesar_retiros] ' . $e->getMessage());
    fwrite(STDERR, "Error procesando retiros. Revisa el log de EV.\n");
    exit(1);
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
