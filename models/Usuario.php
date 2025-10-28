<?php
// models/Usuario.php
require_once __DIR__ . '/../database/Conexion.php';

class Usuario extends Conexion
{
    /**
     * 🔹 Obtener los datos de un usuario por su código
     */
    public function obtenerPorCodigo($codigo_usuario)
    {
        try {
            $sql = "SELECT 
                        u.codigo_usuario,
                        u.nombre AS nombre_completo,
                        u.email,
                        u.documento,
                        u.telefono,
                        u.direccion_condominio,
                        u.codigo_condominio,
                        u.codigo_torre,
                        u.codigo_departamento,
                        r.nombre AS rol,
                        c.nombre AS condominio
                    FROM usuario u
                    INNER JOIN rol r ON u.codigo_rol = r.id_rol
                    LEFT JOIN condominio c ON u.codigo_condominio = c.id_condominio
                    WHERE u.codigo_usuario = :codigo_usuario
                    LIMIT 1";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 🔹 Verificar si un email ya está registrado por otro usuario
     */
    private function emailExiste($email, $codigo_usuario_excluir)
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuario 
                    WHERE email = :email AND codigo_usuario != :codigo_usuario";
            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':codigo_usuario', $codigo_usuario_excluir, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 🔹 Actualizar los datos personales del usuario
     */
    public function actualizarDatos($codigo_usuario, $data)
    {
        try {
            // 1️⃣ Validar si el email ya pertenece a otro usuario
            if ($this->emailExiste($data['email'], $codigo_usuario)) {
                throw new Exception("El correo electrónico ingresado ya está registrado por otro usuario.");
            }

            // 2️⃣ Ejecutar actualización
            $sql = "UPDATE usuario
                    SET 
                        nombre = :nombre,
                        email = :email,
                        telefono = :telefono,
                        direccion_condominio = :direccion,
                        codigo_condominio = :condominio,
                        codigo_torre = :torre,
                        codigo_departamento = :departamento,
                        fecha_actualizacion = NOW()
                    WHERE codigo_usuario = :codigo_usuario";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre_completo']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':telefono', $data['telefono']);
            $stmt->bindParam(':direccion', $data['direccion_condominio']);
            $stmt->bindParam(':condominio', $data['comboCondominio']);
            $stmt->bindParam(':torre', $data['comboTorre']);
            $stmt->bindParam(':departamento', $data['comboDepartamento']);
            $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
