<?php
// models/Ubigeo.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class Ubigeo extends Conexion
{
    public function listarDepartamentos(): array
    {
        $sql = "SELECT codigo_departamento, nombre_departamento
                FROM ubigeo_departamento
                WHERE estado = 1
                ORDER BY nombre_departamento ASC";
        $st = $this->dblink->prepare($sql);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarProvinciasPorDepartamento(int $codigoDepartamento): array
    {
        $sql = "SELECT codigo_provincia, nombre_provincia
                FROM ubigeo_provincia
                WHERE estado = '1' AND codigo_departamento = :dep
                ORDER BY nombre_provincia ASC";
        $st = $this->dblink->prepare($sql);
        $st->bindParam(':dep', $codigoDepartamento, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarDistritosPorProvincia(int $codigoProvincia): array
    {
        $sql = "SELECT codigo_distrito, nombre_distrito
                FROM ubigeo_distrito
                WHERE estado = '1' AND codigo_provincia = :prov
                ORDER BY nombre_distrito ASC";
        $st = $this->dblink->prepare($sql);
        $st->bindParam(':prov', $codigoProvincia, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}