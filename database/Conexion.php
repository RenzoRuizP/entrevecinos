<?php

declare(strict_types=1);

// Carga el autoload de Composer y las variables del entorno
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class Conexion {
    protected ?PDO $dblink = null; // ✅ Permite null

    public function __construct() {
        $this->abrirConexion();
    }

    public function __destruct() {
        $this->dblink = null; // Cierra la conexión sin error
    }

    protected function abrirConexion(): void {
        $dsn = "mysql:host=" . $_ENV['BD_SERVIDOR'] . 
               ";port=" . $_ENV['BD_PUERTO'] . 
               ";dbname=" . $_ENV['BD_NOMBRE_BD'] . 
               ";charset=utf8mb4";

        $usuario = $_ENV['BD_USUARIO'];
        $clave   = $_ENV['BD_CLAVE'];

        try {
            $this->dblink = new PDO($dsn, $usuario, $clave);
            $this->dblink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dblink->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
}
