<?php
require_once __DIR__ . '/../database/Conexion.php';

class CondominioModel extends Conexion {

    public function listarCondominios() {
        try {
            $sql = "SELECT 
                        codigo_condominio, 
                        nombre_condominio
                    FROM 
                        condominio 
                    WHERE 
                        estado = 'A'"; // solo activos

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $resultado;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
