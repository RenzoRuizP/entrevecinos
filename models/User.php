<?php
// models/User.php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class User extends Conexion
{
    /**
     * Detecta conflictos de registro por email/documento antes de intentar INSERT.
     * Retorna:
     * [
     *   'has_conflict' => bool,
     *   'error' => 'EMAIL_INACTIVO' | 'DOCUMENTO_INACTIVO' | 'EMAIL_EXISTE' | 'DOCUMENTO_EXISTE'
     *            | 'EMAIL_Y_DOCUMENTO_INACTIVO' | 'EMAIL_Y_DOCUMENTO_EXISTE' | 'CONFLICTO'
     * ]
     */
    public function verificarConflictoRegistro(string $email, string $documento): array
    {
        $email = strtolower(trim($email));
        $documento = trim($documento);

        $sql = "
            SELECT codigo_usuario, email, documento, estado
            FROM usuario
            WHERE email = :email OR documento = :documento
            LIMIT 5
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':documento', $documento, PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $emailRow = null;
        $docRow = null;

        foreach ($rows as $r) {
            if (isset($r['email']) && strtolower((string)$r['email']) === $email) {
                $emailRow = $r;
            }
            if (isset($r['documento']) && (string)$r['documento'] === $documento) {
                $docRow = $r;
            }
        }

        if (!$emailRow && !$docRow) {
            return ['has_conflict' => false];
        }

        // 0 = Inactivo | 1 = En revisión | 2 = Habilitado
        $emailInactivo = $emailRow && (int)($emailRow['estado'] ?? 1) === 0;
        $docInactivo   = $docRow && (int)($docRow['estado'] ?? 1) === 0;

        if ($emailRow && $docRow) {
            if ($emailInactivo && $docInactivo) {
                return ['has_conflict' => true, 'error' => 'EMAIL_Y_DOCUMENTO_INACTIVO'];
            }
            return ['has_conflict' => true, 'error' => 'EMAIL_Y_DOCUMENTO_EXISTE'];
        }

        if ($emailRow) {
            if ($emailInactivo) {
                return ['has_conflict' => true, 'error' => 'EMAIL_INACTIVO'];
            }
            return ['has_conflict' => true, 'error' => 'EMAIL_EXISTE'];
        }

        if ($docRow) {
            if ($docInactivo) {
                return ['has_conflict' => true, 'error' => 'DOCUMENTO_INACTIVO'];
            }
            return ['has_conflict' => true, 'error' => 'DOCUMENTO_EXISTE'];
        }

        return ['has_conflict' => true, 'error' => 'CONFLICTO'];
    }

    public function registrar(array $data): bool
    {
        $stmt = null;

        try {
            $hash = password_hash((string)$data['clave'], PASSWORD_BCRYPT);

            $tipo               = (string)$data['tipo_conjunto'];
            $codigoCondominio   = $data['codigo_condominio'] ?? null;
            $codigoUrbanizacion = $data['codigo_urbanizacion'] ?? null;
            $direccion          = (string)$data['direccion'];
            $comprobante        = $data['comprobante_domicilio'] ?? null;

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

            try {
                $stmt->closeCursor();
            } catch (Throwable $e) {
            }

            return $ok;
        } catch (Throwable $e) {
            if ($stmt) {
                try {
                    $stmt->closeCursor();
                } catch (Throwable $t) {
                }
            }
            throw $e;
        }
    }

    /**
     * Retorna datos del usuario por email tomando la residencia vigente
     * (última fila registrada en usuario_residencia).
     */
    public function DatosUsuario(string $email): array|false
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
                ON ur.codigo_usuario_residencia = (
                    SELECT ur2.codigo_usuario_residencia
                    FROM usuario_residencia ur2
                    WHERE ur2.codigo_usuario = u.codigo_usuario
                    ORDER BY ur2.codigo_usuario_residencia DESC
                    LIMIT 1
                )
            LEFT JOIN condominio c
                ON ur.codigo_condominio = c.codigo_condominio
            LEFT JOIN urbanizacion ub
                ON ur.codigo_urbanizacion = ub.codigo_urbanizacion
            WHERE u.email = :email
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}