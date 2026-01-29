<?php
// models/CuentaObservada.php

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class CuentaObservada extends Conexion
{
    /**
     * Subsanar una observación:
     * - Valida archivo
     * - Verifica estado OBSERVADO (3)
     * - Guarda comprobante
     * - Actualiza usuario_revision + usuario
     */
    public function subsanar(int $codigoUsuario, array $file): array
    {
        // =========================
        // Validaciones de archivo
        // =========================
        $maxBytes = 5 * 1024 * 1024;
        $allowed  = ['pdf','jpg','jpeg','png','webp'];

        $tmp  = $file['tmp_name'] ?? '';
        $name = $file['name'] ?? '';
        $size = (int)($file['size'] ?? 0);

        if ($size <= 0 || $size > $maxBytes) {
            return [
                'ok' => false,
                'mensaje' => 'El archivo es inválido o supera los 5MB.'
            ];
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            return [
                'ok' => false,
                'mensaje' => 'Formato no permitido. Usa PDF, JPG, PNG o WEBP.'
            ];
        }

        // =========================
        // Conexión
        // =========================
        if (!method_exists($this, 'getDblink')) {
            return ['ok'=>false,'mensaje'=>'Conexión no disponible.'];
        }

        $db = $this->getDblink();
        if (!$db) {
            return ['ok'=>false,'mensaje'=>'Sin conexión a la base de datos.'];
        }

        // =========================
        // Verificar estado OBSERVADO
        // =========================
        $sql = "
            SELECT estado_revision
            FROM usuario_revision
            WHERE codigo_usuario = :id
            LIMIT 1
        ";
        $st = $db->prepare($sql);
        $st->execute([':id' => $codigoUsuario]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row || (int)$row['estado_revision'] !== 3) {
            return [
                'ok' => false,
                'mensaje' => 'La cuenta no se encuentra en estado OBSERVADO.'
            ];
        }

        // =========================
        // Directorio de subida
        // =========================
        $dir = __DIR__ . '/../resources/uploads/comprobantes';

        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            return [
                'ok' => false,
                'mensaje' => 'No se pudo crear el directorio de subida.'
            ];
        }

        if (!is_writable($dir)) {
            return [
                'ok' => false,
                'mensaje' => 'El directorio de subida no es escribible.'
            ];
        }

        $fname = 'obs_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest  = $dir . '/' . $fname;

        if (!move_uploaded_file($tmp, $dest)) {
            return [
                'ok' => false,
                'mensaje' => 'No se pudo guardar el archivo.'
            ];
        }

        // Ruta pública
        $publicPath = 'resources/uploads/comprobantes/' . $fname;

        // =========================
        // Transacción
        // =========================
        $db->beginTransaction();

        try {
            // usuario_revision → vuelve a EN REVISIÓN
            $sql1 = "
                UPDATE usuario_revision
                SET
                    estado_revision = 1,
                    comprobante_path = :path,
                    fecha_reenvio = NOW()
                WHERE codigo_usuario = :id
            ";
            $st1 = $db->prepare($sql1);
            $st1->execute([
                ':path' => $publicPath,
                ':id'   => $codigoUsuario
            ]);

            // usuario → sigue en revisión (consistencia)
            $sql2 = "
                UPDATE usuario
                SET estado = 1
                WHERE codigo_usuario = :id
            ";
            $st2 = $db->prepare($sql2);
            $st2->execute([':id' => $codigoUsuario]);

            $db->commit();

        } catch (Throwable $e) {
            $db->rollBack();
            @unlink($dest);
            error_log('[EV][CuentaObservada::subsanar] ' . $e->getMessage());

            return [
                'ok' => false,
                'mensaje' => 'Error interno al actualizar la información.'
            ];
        }

        return [
            'ok' => true,
            'mensaje' => 'Comprobante enviado correctamente.'
        ];
    }
}
