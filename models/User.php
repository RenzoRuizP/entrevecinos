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

    public function DatosUsuario($email) {
        $sql = "
                    SELECT 
							u.nombre AS nombre_completo,
							u.email,
							u.documento,
							u.telefono,
							-- c.direccion_condominio,
							c.codigo_condominio,
							c.nombre_condominio,
							t.codigo_torre,
							t.nombre_torre,
							d.codigo_departamento,
							d.numero_departamento
							 
						FROM 
							usuario u INNER JOIN usuario_departamento ud
						on
							u.codigo_usuario = ud.codigo_usuario INNER JOIN departamento d 
						on
							ud.codigo_departamento = d.codigo_departamento INNER JOIN torre t
						on
							d.codigo_torre = t.codigo_torre INNER JOIN condominio c
						on
							t.codigo_condominio = c.codigo_condominio
						where
							u.email = :email";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
