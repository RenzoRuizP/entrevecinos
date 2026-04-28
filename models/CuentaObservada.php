<?php
// models/CuentaObservada.php

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/UsuarioRevision.php';
require_once __DIR__ . '/Usuario.php';

final class CuentaObservada extends Conexion
{
    private const MAX_FILE_BYTES = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];
    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    public function subsanar(int $codigoUsuario, array $file): array
    {
        if ($codigoUsuario <= 0) {
            return [
                'ok' => false,
                'mensaje' => 'Usuario inválido.'
            ];
        }

        try {
            $revisionModel = new UsuarioRevision();
            $usuarioModel  = new Usuario();

            $revision = $revisionModel->obtenerPorUsuario($codigoUsuario);

            if (!$revision) {
                return [
                    'ok' => false,
                    'mensaje' => 'No se encontró una revisión asociada al usuario.'
                ];
            }

            if ((int)($revision['estado_revision'] ?? 0) !== 3) {
                return [
                    'ok' => false,
                    'mensaje' => 'Tu cuenta no se encuentra en estado observado.'
                ];
            }

            $validacion = $this->validarArchivo($file);
            if (!$validacion['ok']) {
                return $validacion;
            }

            $rutaRelativa = $this->guardarArchivo($codigoUsuario, $file, $validacion['extension']);
            if ($rutaRelativa === '') {
                return [
                    'ok' => false,
                    'mensaje' => 'No se pudo guardar el comprobante.'
                ];
            }

            $this->dblink->beginTransaction();

            $okRevision = $revisionModel->registrarReenvio($codigoUsuario, $rutaRelativa);
            if (!$okRevision) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'mensaje' => 'No se pudo registrar el reenvío del comprobante.'
                ];
            }

            // Mantener al usuario en "En revisión" para que soporte lo vuelva a evaluar
            $usuarioModel->actualizarEstado($codigoUsuario, 1);

            $this->dblink->commit();

            return [
                'ok' => true,
                'mensaje' => 'Comprobante reenviado correctamente. Tu cuenta vuelve a revisión.',
                'data' => [
                    'codigo_usuario'    => $codigoUsuario,
                    'estado_revision'   => 1,
                    'comprobante_path'  => $rutaRelativa
                ]
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][CuentaObservada::subsanar] ' . $e->getMessage());

            return [
                'ok' => false,
                'mensaje' => 'Ocurrió un error al reenviar el comprobante.'
            ];
        }
    }

    private function validarArchivo(array $file): array
    {
        if (
            !isset($file['error'], $file['name'], $file['tmp_name'], $file['size']) ||
            !is_string($file['name']) ||
            !is_string($file['tmp_name'])
        ) {
            return [
                'ok' => false,
                'mensaje' => 'Archivo inválido.'
            ];
        }

        $error = (int)$file['error'];
        if ($error !== UPLOAD_ERR_OK) {
            $mensaje = match ($error) {
                UPLOAD_ERR_INI_SIZE,
                UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido.',
                UPLOAD_ERR_PARTIAL   => 'El archivo se subió solo parcialmente.',
                UPLOAD_ERR_NO_FILE   => 'Debes adjuntar un comprobante.',
                UPLOAD_ERR_NO_TMP_DIR => 'No existe directorio temporal para la carga.',
                UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
                UPLOAD_ERR_EXTENSION => 'La subida del archivo fue detenida por una extensión de PHP.',
                default => 'No se pudo procesar el archivo adjunto.'
            };

            return [
                'ok' => false,
                'mensaje' => $mensaje
            ];
        }

        $size = (int)$file['size'];
        if ($size <= 0) {
            return [
                'ok' => false,
                'mensaje' => 'El archivo adjunto está vacío.'
            ];
        }

        if ($size > self::MAX_FILE_BYTES) {
            return [
                'ok' => false,
                'mensaje' => 'El archivo supera el tamaño máximo permitido de 5 MB.'
            ];
        }

        $originalName = trim((string)$file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return [
                'ok' => false,
                'mensaje' => 'Formato no permitido. Sube un archivo PDF, JPG o PNG.'
            ];
        }

        $tmpPath = (string)$file['tmp_name'];
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return [
                'ok' => false,
                'mensaje' => 'El archivo subido no es válido.'
            ];
        }

        $mime = $this->detectarMime($tmpPath);
        if ($mime === null || !in_array($mime, self::ALLOWED_MIMES, true)) {
            return [
                'ok' => false,
                'mensaje' => 'Tipo de archivo inválido. Solo se permiten PDF, JPG o PNG.'
            ];
        }

        return [
            'ok' => true,
            'extension' => $extension,
            'mime' => $mime
        ];
    }

    private function detectarMime(string $tmpPath): ?string
    {
        if (!is_file($tmpPath)) {
            return null;
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $tmpPath);
                finfo_close($finfo);

                if (is_string($mime) && $mime !== '') {
                    return $mime;
                }
            }
        }

        return null;
    }

    private function guardarArchivo(int $codigoUsuario, array $file, string $extension): string
    {
        $projectRoot = realpath(__DIR__ . '/..');
        if ($projectRoot === false) {
            throw new RuntimeException('No se pudo resolver la raíz del proyecto.');
        }

        $uploadRelDir = 'resources/uploads/comprobantes';
        $uploadAbsDir = $projectRoot . DIRECTORY_SEPARATOR . $uploadRelDir;

        if (!is_dir($uploadAbsDir) && !mkdir($uploadAbsDir, 0775, true) && !is_dir($uploadAbsDir)) {
            throw new RuntimeException('No se pudo crear la carpeta de comprobantes.');
        }

        if (!is_writable($uploadAbsDir)) {
            throw new RuntimeException('La carpeta de comprobantes no tiene permisos de escritura.');
        }

        $safeName = 'comp_obs_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destAbs  = $uploadAbsDir . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file((string)$file['tmp_name'], $destAbs)) {
            throw new RuntimeException('No se pudo guardar el archivo subido.');
        }

        return $uploadRelDir . '/' . $safeName;
    }
}