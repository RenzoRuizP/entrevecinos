<?php
// models/User.php
require_once __DIR__ . '/../database/Conexion.php';

class User extends Conexion {

    public function registrar($data) {
        try {
            $hash = password_hash($data['clave'], PASSWORD_BCRYPT);

            $sql = "CALL sp_registrar_usuario(
                        :nombre,
                        :documento,
                        :telefono,
                        :email,
                        :clave,
                        :codigo_rol,
                        :codigo_departamento,
                        :fecha_inicio
                    )";

            $stmt = $this->dblink->prepare($sql);

            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':documento', $data['documento'], PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $data['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':clave', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':codigo_rol', $data['codigo_rol'], PDO::PARAM_INT);
            $stmt->bindParam(':codigo_departamento', $data['codigo_departamento'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $data['fecha_inicio'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function buscarPorEmail($email) {
        $sql = "SELECT * FROM usuario WHERE email = :email LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
