<?php
declare(strict_types=1);

// Carga Composer y variables del entorno una sola vez
require_once __DIR__ . '/../vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class)) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();
    }
}

class Conexion
{
    protected ?PDO $dblink = null;

    public function __construct()
    {
        $this->abrirConexion();
    }

    public function __destruct()
    {
        $this->dblink = null;
    }

    public function getDblink(): ?PDO
    {
        return $this->dblink;
    }

    protected function abrirConexion(): void
    {
        $host   = $_ENV['BD_SERVIDOR']  ?? 'localhost';
        $port   = $_ENV['BD_PUERTO']    ?? '3306';
        $dbName = $_ENV['BD_NOMBRE_BD'] ?? '';
        $user   = $_ENV['BD_USUARIO']   ?? '';
        $pass   = $_ENV['BD_CLAVE']     ?? '';

        if ($dbName === '') {
            throw new RuntimeException('No se encontró la variable BD_NOMBRE_BD en el entorno.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

        try {
            $this->dblink = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Error de conexión a la base de datos: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}