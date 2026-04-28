<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

if (!defined('BD_SERVIDOR')) {
    define('BD_SERVIDOR', $_ENV['BD_SERVIDOR'] ?? '127.0.0.1');
}

if (!defined('BD_PUERTO')) {
    define('BD_PUERTO', $_ENV['BD_PUERTO'] ?? '3306');
}

if (!defined('BD_USUARIO')) {
    define('BD_USUARIO', $_ENV['BD_USUARIO'] ?? 'root');
}

if (!defined('BD_CLAVE')) {
    define('BD_CLAVE', $_ENV['BD_CLAVE'] ?? '');
}

if (!defined('BD_NOMBRE_BD')) {
    define('BD_NOMBRE_BD', $_ENV['BD_NOMBRE_BD'] ?? '');
}

if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', $_ENV['JWT_SECRET_KEY'] ?? '');
}

if (!defined('JWT_EXPIRATION_SECONDS')) {
    define('JWT_EXPIRATION_SECONDS', (int)($_ENV['JWT_EXPIRATION_SECONDS'] ?? 3600));
}