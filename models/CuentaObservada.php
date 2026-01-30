<?php
// models/CuentaObservada.php

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class CuentaObservada extends Conexion
{
    /**
     * Soporte OBSERVA una cuenta
     */
    public function observarDesdeSoporte(int $codigoUsuario, string $mensaje): array
    {
        if ($mensaje === '') {
            return ['ok' => false, 'mensaje' => 'La observación es obligatoria.'];
        }

        $db = $this->getDblink();
        if (!$db) {
            return ['ok' => false, 'mensaje' => 'Sin conexión a la base de datos.'];
        }

        try {
            $db->beginTransaction();

            // 1️⃣ ¿Existe registro?
            $sqlCheck = "
                SELECT id
                FROM usuario_revision
                WHERE codigo_usuario = :id
                LIMIT 1
            ";
            $stCheck = $db->prepare($sqlCheck);
            $stCheck->execute([':id' => $codigoUsuario]);
            $row = $stCheck->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // 2️⃣ UPDATE
                $sql = "
                    UPDATE usuario_revision
                    SET
                        estado_revision      = 3,
                        mensaje_observacion  = :mensaje,
                        fecha_observacion    = NOW(),
                        fecha_actualizacion  = NOW()
                    WHERE codigo_usuario = :id
                ";
            } else {
                // 3️⃣ INSERT
                $sql = "
                    INSERT INTO usuario_revision
                    (
                        codigo_usuario,
                        estado_revision,
                        mensaje_observacion,
                        fecha_observacion,
                        fecha_actualizacion
                    )
                    VALUES
                    (
                        :id,
                        3,
                        :mensaje,
                        NOW(),
                        NOW()
                    )
                ";
            }

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id'      => $codigoUsuario,
                ':mensaje' => $mensaje
            ]);

            // usuario sigue en revisión
            $stUser = $db->prepare("
                UPDATE usuario
                SET estado = 1
                WHERE codigo_usuario = :id
            ");
            $stUser->execute([':id' => $codigoUsuario]);

            $db->commit();

            return ['ok' => true, 'mensaje' => 'Cuenta observada correctamente.'];

        } catch (Throwable $e) {
            $db->rollBack();
            error_log('[EV][CuentaObservada::observarDesdeSoporte] ' . $e->getMessage());

            return ['ok' => false, 'mensaje' => 'Error interno al observar la cuenta.'];
        }
    }

    /**
     * Vecino subsana observación
     */
    public function subsanar(int $codigoUsuario, array $file): array
    {
        $maxBytes = 5 * 1024 * 1024;
        $allowed  = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

        $tmp  = $file['tmp_name'] ?? '';
        $name = $file['name'] ?? '';
        $size = (int)($file['size'] ?? 0);

        if ($size <= 0 || $size > $maxBytes) {
            return ['ok' => false, 'mensaje' => 'El archivo es inválido o supera los 5MB.'];
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            return ['ok' => false, 'mensaje' => 'Formato no permitido.'];
        }

        $db = $this->getDblink();
        if (!$db) {
            return ['ok' => false, 'mensaje' => 'Sin conexión a BD.'];
        }

        $dir = __DIR__ . '/../public/uploads/comprobantes';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $fname = 'obs_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest  = $dir . '/' . $fname;

        if (!move_uploaded_file($tmp, $dest)) {
            return ['ok' => false, 'mensaje' => 'No se pudo guardar el archivo.'];
        }

        $publicPath = rtrim((string)BASE_URL, '/') . '/public/uploads/comprobantes/' . $fname;

        try {
            $db->beginTransaction();

            // mismo patrón: UPDATE o INSERT
            $sqlCheck = "
                SELECT id
                FROM usuario_revision
                WHERE codigo_usuario = :id
                LIMIT 1
            ";
            $stCheck = $db->prepare($sqlCheck);
            $stCheck->execute([':id' => $codigoUsuario]);
            $row = $stCheck->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $sql = "
                    UPDATE usuario_revision
                    SET
                        estado_revision     = 1,
                        comprobante_path    = :path,
                        fecha_reenvio       = NOW(),
                        fecha_actualizacion = NOW()
                    WHERE codigo_usuario = :id
                ";
            } else {
                $sql = "
                    INSERT INTO usuario_revision
                    (
                        codigo_usuario,
                        estado_revision,
                        comprobante_path,
                        fecha_reenvio,
                        fecha_actualizacion
                    )
                    VALUES
                    (
                        :id,
                        1,
                        :path,
                        NOW(),
                        NOW()
                    )
                ";
            }

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id'   => $codigoUsuario,
                ':path' => $publicPath
            ]);

            $db->commit();

            return ['ok' => true, 'mensaje' => 'Comprobante enviado correctamente.'];

        } catch (Throwable $e) {
            $db->rollBack();
            @unlink($dest);
            error_log('[EV][CuentaObservada::subsanar] ' . $e->getMessage());

            return ['ok' => false, 'mensaje' => 'Error interno.'];
        }
    }
}
