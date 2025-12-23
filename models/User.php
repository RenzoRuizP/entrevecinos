<?php
// models/User.php
require_once __DIR__ . '/../database/Conexion.php';

class User extends Conexion {

    public function registrar($data) {
        try {
            $hash = password_hash($data['clave'], PASSWORD_BCRYPT);

            $tipo = $data['tipo_conjunto'];
            $codigoCondominio = $data['codigo_condominio'] ?? null;
            $codigoUrbanizacion = $data['codigo_urbanizacion'] ?? null;
            $direccion = $data['direccion'];

            $sql = "CALL sp_registrar_usuario_v2(
                        :nombre,
                        :documento,
                        :telefono,
                        :email,
                        :clave,
                        :codigo_rol,
                        :tipo_conjunto,
                        :codigo_condominio,
                        :codigo_urbanizacion,
                        :direccion,
                        :fecha_creacion
                    )";

            $stmt = $this->dblink->prepare($sql);

            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':documento', $data['documento'], PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $data['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':clave', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':codigo_rol', $data['codigo_rol'], PDO::PARAM_INT);

            $stmt->bindParam(':tipo_conjunto', $tipo, PDO::PARAM_STR);

            // Condicionales nullable
            if ($codigoCondominio) {
                $stmt->bindParam(':codigo_condominio', $codigoCondominio, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':codigo_condominio', null, PDO::PARAM_NULL);
            }

            if ($codigoUrbanizacion) {
                $stmt->bindParam(':codigo_urbanizacion', $codigoUrbanizacion, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':codigo_urbanizacion', null, PDO::PARAM_NULL);
            }

            $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_creacion', $data['fecha_creacion'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function DatosUsuario($email) {
        $sql = "
            SELECT
                u.codigo_usuario AS codigo_usuario,
                u.nombre AS nombre_completo,
                u.email,
                u.documento,
                u.telefono,

                ur.tipo_conjunto,
                ur.direccion,

                c.codigo_condominio,
                c.nombre_condominio,

                ub.codigo_urbanizacion,
                ub.nombre_urbanizacion

            FROM usuario u
            INNER JOIN usuario_residencia ur
                ON u.codigo_usuario = ur.codigo_usuario
            LEFT JOIN condominio c
                ON ur.codigo_condominio = c.codigo_condominio
            LEFT JOIN urbanizacion ub
                ON ur.codigo_urbanizacion = ub.codigo_urbanizacion
            WHERE u.email = :email
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
