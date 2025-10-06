<?php
require_once __DIR__ . '/../database/Conexion.php';

class CondominioModel extends Conexion {

    public function listarCondominios() {
        $sql = "SELECT codigo_condominio, nombre_condominio
                FROM condominio
                WHERE estado = 'A'";
        $stmt = $this->dblink->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTorres($condominioId) {
        $sql = "SELECT codigo_torre, nombre_torre
                FROM torre
                WHERE codigo_condominio = :id";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':id', $condominioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarDepartamentos($torreId) {
        $sql = "SELECT codigo_departamento, numero_departamento
                FROM departamento
                WHERE codigo_torre = :id";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':id', $torreId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
