<?php
// models/Urbanizacion.php
require_once __DIR__ . '/../database/Conexion.php';

class Urbanizacion extends Conexion
{
    public function listarActivas(): array
    {
        $sql = "
            SELECT
                codigo_urbanizacion,
                nombre_urbanizacion
            FROM urbanizacion
            WHERE estado = 1
            ORDER BY nombre_urbanizacion ASC
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
