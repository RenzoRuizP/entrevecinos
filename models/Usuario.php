<?php
// models/Usuario.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class Usuario extends Conexion
{
    /* =========================
       Helpers
    ========================== */

    private function emailExiste(string $email, int $codigo_usuario_excluir): bool
    {
        $sql = "SELECT COUNT(*) FROM usuario
                WHERE email = :email AND codigo_usuario != :codigo_usuario";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':codigo_usuario', $codigo_usuario_excluir, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    private function urbanizacionExiste($codigo_urbanizacion): bool
    {
        if ($codigo_urbanizacion === null || $codigo_urbanizacion === '') {
            return false;
        }

        $sql = "SELECT 1 FROM urbanizacion WHERE codigo_urbanizacion = :u LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':u', $codigo_urbanizacion, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    private function condominioExiste($codigo_condominio): bool
    {
        if ($codigo_condominio === null || $codigo_condominio === '') {
            return false;
        }

        $sql = "SELECT 1 FROM condominio WHERE codigo_condominio = :c LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':c', $codigo_condominio, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Retorna el ID de la residencia vigente del usuario
     * (última fila registrada).
     */
    private function residenciaIdVigentePorUsuario(int $codigo_usuario): int
    {
        $sql = "SELECT codigo_usuario_residencia
                FROM usuario_residencia
                WHERE codigo_usuario = :cu
                ORDER BY codigo_usuario_residencia DESC
                LIMIT 1";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':cu', $codigo_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return (int)($stmt->fetchColumn() ?: 0);
    }

    /* =========================
       ESTADO
    ========================== */

    public function obtenerEstado(int $codigo_usuario): int
    {
        $sql = "SELECT estado FROM usuario WHERE codigo_usuario = :cu LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':cu', $codigo_usuario, PDO::PARAM_INT);
        $st->execute();
        return (int)($st->fetchColumn() ?? 0);
    }

    public function actualizarEstado(int $codigo_usuario, int $estado): bool
    {
        if (!in_array($estado, [0, 1, 2], true)) {
            return false;
        }

        $sql = "UPDATE usuario SET estado = :e WHERE codigo_usuario = :cu LIMIT 1";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':e', $estado, PDO::PARAM_INT);
        $st->bindValue(':cu', $codigo_usuario, PDO::PARAM_INT);
        return $st->execute();
    }

    /* =========================
       OBTENER USUARIO
    ========================== */

    public function obtenerPorCodigo(int $codigo_usuario): array|false
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
                    ON ur.codigo_usuario_residencia = (
                        SELECT ur2.codigo_usuario_residencia
                        FROM usuario_residencia ur2
                        WHERE ur2.codigo_usuario = u.codigo_usuario
                        ORDER BY ur2.codigo_usuario_residencia DESC
                        LIMIT 1
                    )
                WHERE u.codigo_usuario = :codigo_usuario
                LIMIT 1";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        return [
            'codigo_usuario'        => $row['codigo_usuario'],
            'nombre_completo'       => $row['nombre_completo'],
            'email'                 => $row['email'],
            'documento'             => $row['documento'],
            'telefono'              => $row['telefono'],
            'tipo_conjunto'         => $row['tipo_conjunto'] ?? '',
            'codigo_condominio'     => $row['codigo_condominio'] ?? '',
            'codigo_urbanizacion'   => $row['codigo_urbanizacion'] ?? '',
            'direccion'             => $row['direccion'] ?? '',
            'comprobante_domicilio' => $row['comprobante_domicilio'] ?? '',
        ];
    }

    /* =========================
       ACTUALIZAR SOLO TELEFONO
    ========================== */

    public function actualizarTelefono(int $codigo_usuario, string $telefono): bool
    {
        $telefono = preg_replace('/\s+/', '', trim($telefono));

        if ($telefono === '') {
            return false;
        }

        if (!preg_match('/^9\d{8}$/', $telefono)) {
            return false;
        }

        $sql = "UPDATE usuario SET telefono = :telefono WHERE codigo_usuario = :cu";
        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':telefono', $telefono, PDO::PARAM_STR);
        $stmt->bindValue(':cu', $codigo_usuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /* =========================
       ACTUALIZAR DATOS (legacy controlado)
       - Actualiza usuario
       - Actualiza solo la residencia vigente
    ========================== */

    public function actualizarDatos(int $codigo_usuario, array $data): bool
    {
        try {
            if (!empty($data['email']) && $this->emailExiste((string)$data['email'], $codigo_usuario)) {
                throw new Exception("El correo electrónico ingresado ya está registrado por otro usuario.");
            }

            $nombre    = trim((string)($data['nombre_completo'] ?? ''));
            $telefono  = trim((string)($data['telefono'] ?? ''));
            $documento = isset($data['documento']) ? trim((string)$data['documento']) : null;

            if ($nombre === '') {
                throw new Exception("El nombre completo es obligatorio.");
            }

            $tipo      = strtolower(trim((string)($data['tipo_conjunto'] ?? '')));
            $direccion = trim((string)($data['direccion'] ?? ''));

            if (!in_array($tipo, ['condominio', 'urbanizacion'], true)) {
                throw new Exception("Residencia no definida o inválida.");
            }

            if ($direccion === '') {
                throw new Exception("La dirección es obligatoria.");
            }

            $codigo_condominio   = null;
            $codigo_urbanizacion = null;

            if ($tipo === 'condominio') {
                $codigo_condominio = $data['codigo_condominio'] ?? null;
                if ($codigo_condominio === null || $codigo_condominio === '') {
                    throw new Exception("Debes seleccionar un condominio.");
                }
                if (!ctype_digit((string)$codigo_condominio)) {
                    throw new Exception("Condominio inválido.");
                }

                $codigo_condominio = (int)$codigo_condominio;
                if (!$this->condominioExiste($codigo_condominio)) {
                    throw new Exception("El condominio seleccionado no existe.");
                }
            } else {
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

            $this->dblink->beginTransaction();

            $sqlU = "UPDATE usuario
                     SET nombre = :nombre,
                         telefono = :telefono" . ($documento !== null ? ", documento = :documento" : "") . "
                     WHERE codigo_usuario = :codigo_usuario";
            $stmtU = $this->dblink->prepare($sqlU);
            $stmtU->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmtU->bindValue(':telefono', $telefono, PDO::PARAM_STR);
            if ($documento !== null) {
                $stmtU->bindValue(':documento', $documento, PDO::PARAM_STR);
            }
            $stmtU->bindValue(':codigo_usuario', $codigo_usuario, PDO::PARAM_INT);
            $stmtU->execute();

            $urId = $this->residenciaIdVigentePorUsuario($codigo_usuario);

            if ($urId > 0) {
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
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            throw $e;
        }
    }

    /* =========================
       PASSWORD / CLAVE
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