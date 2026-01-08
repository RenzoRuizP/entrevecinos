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

    private function departamentoExiste($codigo_departamento)
    {
        if ($codigo_departamento === null || $codigo_departamento === '') return false;

        $sql = "SELECT 1 FROM departamento WHERE codigo_departamento = :dep LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':dep', $codigo_departamento, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
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
       OBTENER USUARIO (alineado a usuario_residencia + compat condominio)
       - Devuelve campos que tu vista/JS ya consumen:
         tipo_conjunto, codigo_condominio, codigo_urbanizacion, direccion,
         codigo_torre, codigo_departamento, condominio
    ========================== */
    public function obtenerPorCodigo($codigo_usuario)
    {
        try {
            $sql = "SELECT 
                        u.codigo_usuario,
                        u.nombre AS nombre_completo,
                        u.email,
                        u.documento,
                        u.telefono,

                        -- ✅ NUEVO MODELO
                        ur.tipo_conjunto,
                        ur.codigo_condominio AS ur_codigo_condominio,
                        ur.codigo_urbanizacion,
                        ur.direccion,

                        -- ✅ COMPATIBILIDAD CON CONDOMINIO (de la relación antigua)
                        c.codigo_condominio AS rel_codigo_condominio,
                        t.codigo_torre,
                        d.codigo_departamento,
                        c.nombre_condominio AS condominio

                    FROM usuario u
                    LEFT JOIN usuario_residencia ur
                           ON ur.codigo_usuario = u.codigo_usuario

                    LEFT JOIN usuario_departamento ud
                           ON ud.codigo_usuario = u.codigo_usuario
                    LEFT JOIN departamento d
                           ON ud.codigo_departamento = d.codigo_departamento
                    LEFT JOIN torre t
                           ON d.codigo_torre = t.codigo_torre
                    LEFT JOIN condominio c
                           ON t.codigo_condominio = c.codigo_condominio

                    WHERE u.codigo_usuario = :codigo_usuario
                    ORDER BY ur.codigo_usuario_residencia DESC
                    LIMIT 1";

            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return false;

            // ✅ Normalizar codigo_condominio:
            // Si viene del nuevo modelo, lo usamos; si no, caemos a la relación antigua
            $codigoCondominio = $row['ur_codigo_condominio'] ?? null;
            if ($codigoCondominio === null || $codigoCondominio === '') {
                $codigoCondominio = $row['rel_codigo_condominio'] ?? null;
            }

            // Devolver en el formato que tu vista ya usa:
            return [
                'codigo_usuario'       => $row['codigo_usuario'],
                'nombre_completo'      => $row['nombre_completo'],
                'email'                => $row['email'],
                'documento'            => $row['documento'],
                'telefono'             => $row['telefono'],

                'tipo_conjunto'        => $row['tipo_conjunto'] ?? '',
                'codigo_condominio'    => $codigoCondominio ?? '',
                'codigo_urbanizacion'  => $row['codigo_urbanizacion'] ?? '',
                'direccion'            => $row['direccion'] ?? '',

                // compat condominio
                'codigo_torre'         => $row['codigo_torre'] ?? '',
                'codigo_departamento'  => $row['codigo_departamento'] ?? '',
                'condominio'           => $row['condominio'] ?? '',
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* =========================
       ACTUALIZAR DATOS (nuevo modelo)
       - Actualiza usuario (nombre/telefono/documento si viene)
       - Actualiza o inserta usuario_residencia
       - Mantiene usuario_departamento para condominio
       - Limpia usuario_departamento para urbanizacion
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

            if ($nombre === '') {
                throw new Exception("El nombre completo es obligatorio.");
            }

            $tipo = strtolower(trim((string)($data['tipo_conjunto'] ?? '')));
            $direccion = trim((string)($data['direccion'] ?? ''));

            if (!in_array($tipo, ['condominio', 'urbanizacion'], true)) {
                throw new Exception("Residencia no definida o inválida.");
            }
            if ($direccion === '') {
                throw new Exception("La dirección es obligatoria.");
            }

            // 2) Resolver códigos según tipo
            $codigo_condominio = null;
            $codigo_urbanizacion = null;

            $codigo_departamento = null; // solo si es condominio (se guarda en usuario_departamento)

            if ($tipo === 'condominio') {
                $codigo_condominio = $data['codigo_condominio'] ?? null;
                if ($codigo_condominio === null || $codigo_condominio === '') {
                    throw new Exception("Debes seleccionar un condominio.");
                }
                if (!ctype_digit((string)$codigo_condominio)) {
                    throw new Exception("Condominio inválido.");
                }
                $codigo_condominio = (int)$codigo_condominio;

                // compat: comboDepartamento o codigo_departamento
                $dep = $data['comboDepartamento'] ?? ($data['codigo_departamento'] ?? null);
                if ($dep === null || $dep === '') {
                    throw new Exception("Debes seleccionar un departamento.");
                }
                if (!ctype_digit((string)$dep)) {
                    throw new Exception("Departamento inválido.");
                }
                $codigo_departamento = (int)$dep;

                if (!$this->departamentoExiste($codigo_departamento)) {
                    throw new Exception("El departamento seleccionado no existe.");
                }
            } else { // urbanizacion
                $codigo_urbanizacion = $data['codigo_urbanizacion'] ?? null;
                if ($codigo_urbanizacion === null || $codigo_urbanizacion === '') {
                    throw new Exception("Debes seleccionar una urbanización.");
                }
                if (!ctype_digit((string)$codigo_urbanizacion)) {
                    throw new Exception("Urbanización inválida.");
                }
                $codigo_urbanizacion = (int)$codigo_urbanizacion;

                if (!$this->urbanizacionExiste($codigo_urbanizacion)) {
                    throw new Exception("La urbanización seleccionada no existe.");
                }
            }

            // 3) Transacción
            $this->dblink->beginTransaction();

            // 3.1) Actualizar usuario (no tocamos email)
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

            // 3.3) Compatibilidad: usuario_departamento
            // - condominio: insert/update relación con departamento
            // - urbanizacion: borrar relación para evitar inconsistencias
            if ($tipo === 'condominio') {
                // delete y reinsert (simple, seguro, consistente)
                $stmtDel = $this->dblink->prepare("DELETE FROM usuario_departamento WHERE codigo_usuario = :cu");
                $stmtDel->bindValue(':cu', $codigo_usuario, PDO::PARAM_INT);
                $stmtDel->execute();

                $stmtIns = $this->dblink->prepare(
                    "INSERT INTO usuario_departamento (codigo_usuario, codigo_departamento)
                     VALUES (:cu, :dep)"
                );
                $stmtIns->bindValue(':cu', $codigo_usuario, PDO::PARAM_INT);
                $stmtIns->bindValue(':dep', $codigo_departamento, PDO::PARAM_INT);
                $stmtIns->execute();
            } else {
                $stmtDel = $this->dblink->prepare("DELETE FROM usuario_departamento WHERE codigo_usuario = :cu");
                $stmtDel->bindValue(':cu', $codigo_usuario, PDO::PARAM_INT);
                $stmtDel->execute();
            }

            $this->dblink->commit();
            return true;

        } catch (Exception $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            throw $e;
        }
    }
}
