<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$evDbName = (string)ev_env('BD_NOMBRE', ev_env('BD_NOMBRE_BD', ''));
$evDbPassword = (string)ev_env('BD_PASSWORD', ev_env('BD_CLAVE', ''));

if (!defined('BD_SERVIDOR')) {
    define('BD_SERVIDOR', (string)ev_env('BD_SERVIDOR', '127.0.0.1'));
}

if (!defined('BD_PUERTO')) {
    define('BD_PUERTO', (string)ev_env('BD_PUERTO', '3306'));
}

if (!defined('BD_USUARIO')) {
    define('BD_USUARIO', (string)ev_env('BD_USUARIO', 'root'));
}

if (!defined('BD_PASSWORD')) {
    define('BD_PASSWORD', $evDbPassword);
}

if (!defined('BD_CLAVE')) {
    define('BD_CLAVE', $evDbPassword);
}

if (!defined('BD_NOMBRE')) {
    define('BD_NOMBRE', $evDbName);
}

if (!defined('BD_NOMBRE_BD')) {
    define('BD_NOMBRE_BD', $evDbName);
}

if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', (string)ev_env('JWT_SECRET_KEY', ''));
}

if (!defined('JWT_EXPIRATION_SECONDS')) {
    define('JWT_EXPIRATION_SECONDS', (int)ev_env('JWT_EXPIRATION_SECONDS', 3600));
}
