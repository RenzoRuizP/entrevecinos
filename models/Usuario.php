<?php
// models/Usuario.php
require_once __DIR__ . '/../database/Conexion.php';

class Usuario extends Conexion
{
    /* =========================
       Helpers
    ========================== */
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

    private function urbanizacionExiste($codigo_urbanizacion)
    {
        if ($codigo_urbanizacion === null || $codigo_urbanizacion === '') return false;

        $sql = "SELECT 1 FROM urbanizacion WHERE codigo_urbanizacion = :u LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':u', $codigo_urbanizacion, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    private function condominioExiste($codigo_condominio)
    {
        if ($codigo_condominio === null || $codigo_condominio === '') return false;

        $sql = "SELECT 1 FROM condominio WHERE codigo_condominio = :c LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':c', $codigo_condominio, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    private function residenciaIdPorUsuario($codigo_usuario)
    {
        $sql = "SELECT codigo_usuario_residencia
                FROM usuario_residencia
                WHERE codigo_usuario = :cu
                ORDER BY codigo_usuario_residencia DESC
                LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':cu', $codigo_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn(); // false si no existe
    }

    /* =========================
       OBTENER USUARIO (alineado a usuario_residencia)
       Devuelve campos que tu vista/JS consumen:
       tipo_conjunto, codigo_condominio, codigo_urbanizacion, direccion, comprobante_domicilio
    ========================== */
    public function obtenerPorCodigo($codigo_usuario)
    {
        $sql = "SELECT 
                    u.codigo_usuario,
                    u.nombre AS nombre_completo,
                    u.email,
                    u.documento,
                    u.telefono,

                    ur.tipo_conjunto,
                    ur.codigo_condominio,
                    ur.codigo_urbanizacion,
                    ur.direccion,
                    ur.comprobante_domicilio

                FROM usuario u
                LEFT JOIN usuario_residencia ur
                       ON ur.codigo_usuario = u.codigo_usuario
                WHERE u.codigo_usuario = :codigo_usuario
                ORDER BY ur.codigo_usuario_residencia DESC
                LIMIT 1";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;

        return [
            'codigo_usuario'       => $row['codigo_usuario'],
            'nombre_completo'      => $row['nombre_completo'],
            'email'                => $row['email'],
            'documento'            => $row['documento'],
            'telefono'             => $row['telefono'],

            'tipo_conjunto'        => $row['tipo_conjunto'] ?? '',
            'codigo_condominio'    => $row['codigo_condominio'] ?? '',
            'codigo_urbanizacion'  => $row['codigo_urbanizacion'] ?? '',
            'direccion'            => $row['direccion'] ?? '',
            'comprobante_domicilio'=> $row['comprobante_domicilio'] ?? '',
        ];
    }

    /* =========================
       ACTUALIZAR DATOS
       - Actualiza usuario (nombre/telefono/documento si viene)
       - Actualiza o inserta usuario_residencia
       - NO usa torre/departamento
    ========================== */
    public function actualizarDatos($codigo_usuario, $data)
    {
        try {
            // 1) Validar duplicado de email (si lo envías)
            if (!empty($data['email']) && $this->emailExiste($data['email'], $codigo_usuario)) {
                throw new Exception("El correo electrónico ingresado ya está registrado por otro usuario.");
            }

            $nombre    = trim((string)($data['nombre_completo'] ?? ''));
            $telefono  = trim((string)($data['telefono'] ?? ''));
            $documento = isset($data['documento']) ? trim((string)$data['documento']) : null;

            if ($nombre === '') throw new Exception("El nombre completo es obligatorio.");

            $tipo = strtolower(trim((string)($data['tipo_conjunto'] ?? '')));
            $direccion = trim((string)($data['direccion'] ?? ''));

            if (!in_array($tipo, ['condominio', 'urbanizacion'], true)) {
                throw new Exception("Residencia no definida o inválida.");
            }
            if ($direccion === '') {
                throw new Exception("La dirección es obligatoria.");
            }

            // 2) Resolver códigos según tipo (SIN departamento)
            $codigo_condominio   = null;
            $codigo_urbanizacion = null;

            if ($tipo === 'condominio') {
                $codigo_condominio = $data['codigo_condominio'] ?? null;
                if ($codigo_condominio === null || $codigo_condominio === '') throw new Exception("Debes seleccionar un condominio.");
                if (!ctype_digit((string)$codigo_condominio)) throw new Exception("Condominio inválido.");

                $codigo_condominio = (int)$codigo_condominio;
                if (!$this->condominioExiste($codigo_condominio)) throw new Exception("El condominio seleccionado no existe.");
            } else {
                $codigo_urbanizacion = $data['codigo_urbanizacion'] ?? null;
                if ($codigo_urbanizacion === null || $codigo_urbanizacion === '') throw new Exception("Debes seleccionar una urbanización.");
                if (!ctype_digit((string)$codigo_urbanizacion)) throw new Exception("Urbanización inválida.");

                $codigo_urbanizacion = (int)$codigo_urbanizacion;
                if (!$this->urbanizacionExiste($codigo_urbanizacion)) throw new Exception("La urbanización seleccionada no existe.");
            }

            // 3) Transacción
            $this->dblink->beginTransaction();

            // 3.1) Actualizar usuario
            $sqlU = "UPDATE usuario
                     SET nombre = :nombre,
                         telefono = :telefono" . ($documento !== null ? ", documento = :documento" : "") . "
                     WHERE codigo_usuario = :codigo_usuario";
            $stmtU = $this->dblink->prepare($sqlU);
            $stmtU->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmtU->bindValue(':telefono', $telefono, PDO::PARAM_STR);
            if ($documento !== null) $stmtU->bindValue(':documento', $documento, PDO::PARAM_STR);
            $stmtU->bindValue(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
            $stmtU->execute();

            // 3.2) Insert/Update usuario_residencia (por codigo_usuario)
            $urId = $this->residenciaIdPorUsuario($codigo_usuario);

            if ($urId) {
                $sqlUR = "UPDATE usuario_residencia
                          SET tipo_conjunto = :tipo,
                              codigo_condominio = :cod_condominio,
                              codigo_urbanizacion = :cod_urbanizacion,
                              direccion = :direccion
                          WHERE codigo_usuario_residencia = :urId";
                $stmtUR = $this->dblink->prepare($sqlUR);
                $stmtUR->bindValue(':tipo', $tipo, PDO::PARAM_STR);
                $stmtUR->bindValue(':cod_condominio', $codigo_condominio, $codigo_condominio === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtUR->bindValue(':cod_urbanizacion', $codigo_urbanizacion, $codigo_urbanizacion === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtUR->bindValue(':direccion', $direccion, PDO::PARAM_STR);
                $stmtUR->bindValue(':urId', $urId, PDO::PARAM_INT);
                $stmtUR->execute();
            } else {
                $sqlUR = "INSERT INTO usuario_residencia
                            (codigo_usuario, tipo_conjunto, codigo_condominio, codigo_urbanizacion, direccion)
                          VALUES
                            (:codigo_usuario, :tipo, :cod_condominio, :cod_urbanizacion, :direccion)";
                $stmtUR = $this->dblink->prepare($sqlUR);
                $stmtUR->bindValue(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
                $stmtUR->bindValue(':tipo', $tipo, PDO::PARAM_STR);
                $stmtUR->bindValue(':cod_condominio', $codigo_condominio, $codigo_condominio === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtUR->bindValue(':cod_urbanizacion', $codigo_urbanizacion, $codigo_urbanizacion === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtUR->bindValue(':direccion', $direccion, PDO::PARAM_STR);
                $stmtUR->execute();
            }

            $this->dblink->commit();
            return true;

        } catch (Exception $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            throw $e;
        }
    }

        /* =========================
        PASSWORD / CLAVE
        - usuario.clave guarda hash
        - exige clave_actual + nueva + confirmar
        ========================== */

        public function obtenerHashClave(int $codigo_usuario): ?string
        {
            $sql = "SELECT clave FROM usuario WHERE codigo_usuario = :cu LIMIT 1";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':cu', $codigo_usuario, PDO::PARAM_INT);
            $st->execute();
            $hash = $st->fetchColumn();
            return $hash ? (string)$hash : null;
        }

        public function actualizarClave(int $codigo_usuario, string $hashNueva): bool
        {
            $sql = "UPDATE usuario SET clave = :h WHERE codigo_usuario = :cu";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':h', $hashNueva, PDO::PARAM_STR);
            $st->bindValue(':cu', $codigo_usuario, PDO::PARAM_INT);
            return $st->execute();
        }

}
