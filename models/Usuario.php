<?php
// models/Usuario.php
require_once __DIR__ . '/../database/Conexion.php';

class Usuario extends Conexion {

    public function obtenerPorId($id_usuario) {
        try {
            $sql = "SELECT 
                        u.id_usuario,
                        u.nombre,
                        u.email,
                        r.nombre AS rol,
                        c.nombre AS condominio
                    FROM usuario u
                    INNER JOIN rol r ON u.codigo_rol = r.id_rol
                    INNER JOIN condominio c ON u.codigo_condominio = c.id_condominio
                    WHERE u.id_usuario = :id_usuario
                    LIMIT 1";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }

}
