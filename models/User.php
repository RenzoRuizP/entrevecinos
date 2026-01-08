<?php
// models/User.php
require_once __DIR__ . '/../database/Conexion.php';

class User extends Conexion
{
    public function registrar($data)
    {
        $stmt = null;

        try {
            $hash = password_hash($data['clave'], PASSWORD_BCRYPT);

            $tipo = $data['tipo_conjunto'];
            $codigoCondominio = $data['codigo_condominio'] ?? null;
            $codigoUrbanizacion = $data['codigo_urbanizacion'] ?? null;
            $direccion = $data['direccion'];
            $comprobante = $data['comprobante_domicilio'] ?? null;

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
                        :comprobante_domicilio
                    )";

            $stmt = $this->dblink->prepare($sql);

            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':documento', $data['documento'], PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $data['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':clave', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':codigo_rol', $data['codigo_rol'], PDO::PARAM_INT);

            $stmt->bindParam(':tipo_conjunto', $tipo, PDO::PARAM_STR);

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

            if ($comprobante) {
                $stmt->bindParam(':comprobante_domicilio', $comprobante, PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':comprobante_domicilio', null, PDO::PARAM_NULL);
            }

            $ok = $stmt->execute();

            try { $stmt->closeCursor(); } catch (Throwable $e) {}

            return $ok;

        } catch (Throwable $e) {
            if ($stmt) {
                try { $stmt->closeCursor(); } catch (Throwable $t) {}
            }
            throw $e;
        }
    }

    public function DatosUsuario($email)
    {
        $sql = "
            SELECT
                u.codigo_usuario AS codigo_usuario,
                u.nombre AS nombre_completo,
                u.email,
                u.documento,
                u.telefono,
                u.codigo_rol AS codigo_rol,

                ur.tipo_conjunto,
                ur.codigo_condominio AS codigo_condominio,
                ur.codigo_urbanizacion AS codigo_urbanizacion,
                ur.direccion,
                ur.comprobante_domicilio,

                c.nombre_condominio,
                ub.nombre_urbanizacion

            FROM usuario u
            LEFT JOIN usuario_residencia ur
                ON u.codigo_usuario = ur.codigo_usuario
            LEFT JOIN condominio c
                ON ur.codigo_condominio = c.codigo_condominio
            LEFT JOIN urbanizacion ub
                ON ur.codigo_urbanizacion = ub.codigo_urbanizacion
            WHERE u.email = :email
            ORDER BY ur.codigo_usuario_residencia DESC
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
