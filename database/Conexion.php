<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';

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
        $host = (string)ev_env('BD_SERVIDOR', 'localhost');
        $port = (string)ev_env('BD_PUERTO', '3306');
        $dbName = (string)ev_env('BD_NOMBRE', ev_env('BD_NOMBRE_BD', ''));
        $user = (string)ev_env('BD_USUARIO', '');
        $pass = (string)ev_env('BD_PASSWORD', ev_env('BD_CLAVE', ''));

        if ($dbName === '') {
            throw new RuntimeException('No se encontró BD_NOMBRE ni BD_NOMBRE_BD en la configuración del entorno.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

        try {
            $this->dblink = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
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
