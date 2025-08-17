<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables del archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// (Opcional) Definir constantes si prefieres usarlas en lugar de $_ENV
define('BD_SERVIDOR', $_ENV['BD_SERVIDOR']);
define('BD_PUERTO', $_ENV['BD_PUERTO']);
define('BD_USUARIO', $_ENV['BD_USUARIO']);
define('BD_CLAVE', $_ENV['BD_CLAVE']);
define('BD_NOMBRE_BD', $_ENV['BD_NOMBRE_BD']);
define('JWT_SECRET_KEY', $_ENV['JWT_SECRET_KEY']);
define('JWT_EXPIRATION_SECONDS', $_ENV['JWT_EXPIRATION_SECONDS']);
