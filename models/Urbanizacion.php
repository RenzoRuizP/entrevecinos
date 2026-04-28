<?php
// models/Urbanizacion.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class Urbanizacion extends Conexion
{
    public function listarActivas(): array
    {
        $sql = "SELECT codigo_urbanizacion, nombre_urbanizacion, direccion_urbanizacion, codigo_distrito
                FROM urbanizacion
                WHERE estado = 'A'
                ORDER BY nombre_urbanizacion ASC";
        $st = $this->dblink->prepare($sql);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarPorDistrito(int $codigoDistrito): array
    {
        $sql = "SELECT codigo_urbanizacion, nombre_urbanizacion, direccion_urbanizacion, codigo_distrito
                FROM urbanizacion
                WHERE estado = 'A' AND codigo_distrito = :dist
                ORDER BY nombre_urbanizacion ASC";
        $st = $this->dblink->prepare($sql);
        $st->bindParam(':dist', $codigoDistrito, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerDireccionPorId(int $codigoUrbanizacion): string
    {
        if ($codigoUrbanizacion <= 0) {
            return '';
        }

        $sql = "SELECT direccion_urbanizacion
                FROM urbanizacion
                WHERE codigo_urbanizacion = :id
                LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindParam(':id', $codigoUrbanizacion, PDO::PARAM_INT);
        $st->execute();

        $dir = $st->fetchColumn();
        return is_string($dir) ? trim($dir) : '';
    }
}