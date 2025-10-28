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
                        c.codigo_condominio,
                        t.codigo_torre,
                        d.codigo_departamento,
                        c.nombre_condominio AS condominio
                    FROM 
                        usuario u 
                        LEFT JOIN usuario_departamento ud 
                            ON u.codigo_usuario = ud.codigo_usuario 
                        LEFT JOIN departamento d
                            ON ud.codigo_departamento = d.codigo_departamento 
                        LEFT JOIN torre t
                            ON d.codigo_torre = t.codigo_torre 
                        LEFT JOIN condominio c 
                            ON t.codigo_condominio = c.codigo_condominio
                    WHERE 
                        u.codigo_usuario = :codigo_usuario
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
        $sql = "SELECT COUNT(*) FROM usuario 
                WHERE email = :email AND codigo_usuario != :codigo_usuario";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':codigo_usuario', $codigo_usuario_excluir, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    /**
     * 🔹 Verificar que el departamento exista (para respetar la FK)
     */
    private function departamentoExiste($codigo_departamento)
    {
        if ($codigo_departamento === null || $codigo_departamento === '') return false;

        $sql = "SELECT 1 FROM departamento WHERE codigo_departamento = :dep LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':dep', $codigo_departamento, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /**
     * 🔹 Actualizar los datos personales del usuario usando SP
     * Usa los nombres reales que envía el front:
     * - nombre_completo, documento, telefono, comboDepartamento
     */
    public function actualizarDatos($codigo_usuario, $data)
    {
        try {
            // 1) Validar duplicado de email (email lo envía el front, aunque esté deshabilitado en la vista)
            if ($this->emailExiste($data['email'], $codigo_usuario)) {
                throw new Exception("El correo electrónico ingresado ya está registrado por otro usuario.");
            }

            // 2) Mapear campos desde el payload real del front
            $nombre   = isset($data['nombre_completo']) ? $data['nombre_completo'] : '';
            $documento = isset($data['documento']) ? $data['documento'] : '';
            $telefono  = isset($data['telefono']) ? $data['telefono'] : '';
            // Departamento llega como comboDepartamento
            $codigo_departamento = null;
            if (isset($data['comboDepartamento']) && $data['comboDepartamento'] !== '') {
                $codigo_departamento = (int) $data['comboDepartamento'];
            }

            if ($codigo_departamento === null) {
                throw new Exception("Debes seleccionar un departamento.");
            }

            // 3) Validar existencia del departamento para evitar FK 1452
            if (!$this->departamentoExiste($codigo_departamento)) {
                throw new Exception("El departamento seleccionado no existe.");
            }

            // 4) Ejecutar procedimiento almacenado
            $sql = "CALL sp_actualizar_usuario(
                        :p_nombre,
                        :p_documento,
                        :p_telefono,
                        :p_codigo_departamento,
                        :p_codigo_usuario
                    )";
            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':p_nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':p_documento', $documento, PDO::PARAM_STR);
            $stmt->bindParam(':p_telefono', $telefono, PDO::PARAM_STR);
            $stmt->bindParam(':p_codigo_departamento', $codigo_departamento, PDO::PARAM_INT);
            $stmt->bindParam(':p_codigo_usuario', $codigo_usuario, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (Exception $e) {
            throw $e;
        }
    }
}
