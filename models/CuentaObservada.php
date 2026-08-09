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

    // ============================================================
    // CONSULTAS PARA VISTA CUENTA OBSERVADA
    // ============================================================

    public function obtenerUsuarioPorCodigo(int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                codigo_usuario,
                nombre,
                email,
                estado
            FROM usuario
            WHERE codigo_usuario = :codigo_usuario
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute([
            ':codigo_usuario' => $codigoUsuario
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Retorna la revisión principal del usuario.
     *
     * Importante:
     * Se prioriza una revisión OBSERVADA si existiera.
     * Esto evita que LIMIT 1 traiga una fila antigua en revisión.
     */
    public function obtenerRevisionUsuario(int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                estado_revision,
                mensaje_observacion,
                fecha_observacion,
                fecha_reenvio,
                fecha_actualizacion,
                comprobante_path
            FROM usuario_revision
            WHERE codigo_usuario = :codigo_usuario
            ORDER BY
                CASE WHEN estado_revision = 3 THEN 1 ELSE 0 END DESC,
                COALESCE(fecha_actualizacion, fecha_observacion, fecha_reenvio, '1970-01-01') DESC
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute([
            ':codigo_usuario' => $codigoUsuario
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Obtiene la última solicitud abierta de cambio de residencia:
     * pendiente u observada.
     */
    public function obtenerSolicitudResidenciaAbierta(int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                s.codigo_solicitud,
                s.codigo_usuario,
                s.tipo_conjunto,
                s.codigo_condominio,
                s.codigo_urbanizacion,
                s.direccion,
                s.comprobante_domicilio,
                s.estado,
                s.comentario_admin,
                s.created_at,
                s.updated_at,

                COALESCE(
                    NULLIF(TRIM(c.nombre_condominio), ''),
                    NULLIF(TRIM(u.nombre_urbanizacion), '')
                ) AS nombre_comunidad

            FROM usuario_residencia_solicitud s
            LEFT JOIN condominio c
                ON c.codigo_condominio = s.codigo_condominio
            LEFT JOIN urbanizacion u
                ON u.codigo_urbanizacion = s.codigo_urbanizacion
            WHERE s.codigo_usuario = :codigo_usuario
              AND s.estado IN ('pendiente', 'observada')
            ORDER BY
                CASE
                    WHEN s.estado = 'observada' THEN 2
                    WHEN s.estado = 'pendiente' THEN 1
                    ELSE 0
                END DESC,
                s.codigo_solicitud DESC
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute([
            ':codigo_usuario' => $codigoUsuario
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Obtiene específicamente la última solicitud OBSERVADA.
     */
    public function obtenerSolicitudResidenciaObservada(int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                s.codigo_solicitud,
                s.codigo_usuario,
                s.tipo_conjunto,
                s.codigo_condominio,
                s.codigo_urbanizacion,
                s.direccion,
                s.comprobante_domicilio,
                s.estado,
                s.comentario_admin,
                s.created_at,
                s.updated_at,

                COALESCE(
                    NULLIF(TRIM(c.nombre_condominio), ''),
                    NULLIF(TRIM(u.nombre_urbanizacion), '')
                ) AS nombre_comunidad

            FROM usuario_residencia_solicitud s
            LEFT JOIN condominio c
                ON c.codigo_condominio = s.codigo_condominio
            LEFT JOIN urbanizacion u
                ON u.codigo_urbanizacion = s.codigo_urbanizacion
            WHERE s.codigo_usuario = :codigo_usuario
              AND s.estado = 'observada'
            ORDER BY s.codigo_solicitud DESC
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute([
            ':codigo_usuario' => $codigoUsuario
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Contexto unificado para que el controlador sepa qué debe mostrar:
     *
     * - observado por cuenta inicial
     * - observado por cambio de residencia
     * - revisión inicial
     * - cambio de residencia pendiente
     */
    public function obtenerContextoVista(int $codigoUsuario): array
    {
        $contexto = [
            'modo_vista'                    => 'revision_inicial',
            'tipo_observacion'              => '',
            'mensaje_observacion'           => '',
            'fecha_observacion'             => null,
            'nombre_comunidad'              => '',
            'codigo_solicitud_residencia'   => null,
            'estado_solicitud_residencia'   => null,
            'es_cambio_residencia'          => false,
        ];

        // 1) Prioridad: cambio de residencia observado/pendiente.
        $solicitud = $this->obtenerSolicitudResidenciaAbierta($codigoUsuario);

        if ($solicitud) {
            $estadoSolicitud = strtolower(trim((string)($solicitud['estado'] ?? '')));

            $contexto['codigo_solicitud_residencia'] = (int)($solicitud['codigo_solicitud'] ?? 0);
            $contexto['estado_solicitud_residencia'] = $estadoSolicitud;
            $contexto['es_cambio_residencia']        = true;
            $contexto['nombre_comunidad']            = trim((string)($solicitud['nombre_comunidad'] ?? ''));

            if ($estadoSolicitud === 'observada') {
                $contexto['modo_vista']          = 'observado';
                $contexto['tipo_observacion']    = 'cambio_residencia';
                $contexto['mensaje_observacion'] = trim((string)($solicitud['comentario_admin'] ?? ''));
                $contexto['fecha_observacion']   = $solicitud['updated_at'] ?? $solicitud['created_at'] ?? null;

                return $contexto;
            }

            if ($estadoSolicitud === 'pendiente') {
                $contexto['modo_vista']        = 'revision_inicial';
                $contexto['tipo_observacion']  = 'cambio_residencia_pendiente';

                return $contexto;
            }
        }

        // 2) Validación inicial observada.
        $revision = $this->obtenerRevisionUsuario($codigoUsuario);

        if ($revision && (int)($revision['estado_revision'] ?? 0) === 3) {
            $contexto['modo_vista']          = 'observado';
            $contexto['tipo_observacion']    = 'cuenta';
            $contexto['mensaje_observacion'] = trim((string)($revision['mensaje_observacion'] ?? ''));
            $contexto['fecha_observacion']   = $revision['fecha_observacion'] ?? $revision['fecha_actualizacion'] ?? null;

            return $contexto;
        }

        // 3) Revisión inicial normal.
        $contexto['modo_vista']       = 'revision_inicial';
        $contexto['tipo_observacion'] = 'cuenta_pendiente';

        return $contexto;
    }

    public function obtenerNombreComunidad(int $codigoUsuario): string
    {
        /*
         * Consulta principal EV:
         * última residencia vigente del usuario.
         */
        try {
            $sql = "
                SELECT
                    COALESCE(
                        NULLIF(TRIM(c.nombre_condominio), ''),
                        NULLIF(TRIM(u.nombre_urbanizacion), '')
                    ) AS nombre_comunidad
                FROM usuario_residencia ur
                LEFT JOIN condominio c
                    ON c.codigo_condominio = ur.codigo_condominio
                LEFT JOIN urbanizacion u
                    ON u.codigo_urbanizacion = ur.codigo_urbanizacion
                WHERE ur.codigo_usuario = :codigo_usuario
                ORDER BY ur.codigo_usuario_residencia DESC
                LIMIT 1
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute([
                ':codigo_usuario' => $codigoUsuario
            ]);

            $valor = $stmt->fetchColumn();

            if ($valor !== false && trim((string)$valor) !== '') {
                return trim((string)$valor);
            }
        } catch (Throwable $e) {
            error_log('[EV][CuentaObservada::obtenerNombreComunidad][usuario_residencia] ' . $e->getMessage());
        }

        /*
         * Fallback defensivo para ambientes antiguos donde usuario todavía
         * pudiera tener codigo_condominio / codigo_urbanizacion.
         */
        try {
            $sql = "
                SELECT
                    COALESCE(
                        NULLIF(TRIM(c.nombre_condominio), ''),
                        NULLIF(TRIM(u.nombre_urbanizacion), '')
                    ) AS nombre_comunidad
                FROM usuario us
                LEFT JOIN condominio c
                    ON c.codigo_condominio = us.codigo_condominio
                LEFT JOIN urbanizacion u
                    ON u.codigo_urbanizacion = us.codigo_urbanizacion
                WHERE us.codigo_usuario = :codigo_usuario
                LIMIT 1
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute([
                ':codigo_usuario' => $codigoUsuario
            ]);

            $valor = $stmt->fetchColumn();

            if ($valor !== false && trim((string)$valor) !== '') {
                return trim((string)$valor);
            }
        } catch (Throwable $e) {
            error_log('[EV][CuentaObservada::obtenerNombreComunidad][usuario] ' . $e->getMessage());
        }

        return '';
    }

    // ============================================================
    // SUBSANACIÓN DE CUENTA OBSERVADA / CAMBIO DE RESIDENCIA
    // ============================================================

    public function subsanar(int $codigoUsuario, array $file): array
    {
        if ($codigoUsuario <= 0) {
            return [
                'ok' => false,
                'mensaje' => 'Usuario inválido.'
            ];
        }

        $rutaRelativa = '';

        try {
            /*
             * Primero detectamos qué tipo de observación tiene el usuario.
             *
             * Prioridad:
             * 1) Cambio de residencia observado.
             * 2) Cuenta inicial observada.
             */
            $solicitudResidencia = $this->obtenerSolicitudResidenciaObservada($codigoUsuario);

            $revision = null;
            $tipoSubsanacion = '';

            if ($solicitudResidencia) {
                $tipoSubsanacion = 'cambio_residencia';
            } else {
                $revision = $this->obtenerRevisionUsuario($codigoUsuario);

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

                $tipoSubsanacion = 'cuenta';
            }

            $validacion = $this->validarArchivo($file);
            if (!$validacion['ok']) {
                return $validacion;
            }

            $prefijoArchivo = $tipoSubsanacion === 'cambio_residencia'
                ? 'comp_res_obs'
                : 'comp_obs';

            $rutaRelativa = $this->guardarArchivo(
                $codigoUsuario,
                $file,
                (string)$validacion['extension'],
                $prefijoArchivo
            );

            if ($rutaRelativa === '') {
                return [
                    'ok' => false,
                    'mensaje' => 'No se pudo guardar el comprobante.'
                ];
            }

            $this->dblink->beginTransaction();

            if ($tipoSubsanacion === 'cambio_residencia') {
                $codigoSolicitud = (int)($solicitudResidencia['codigo_solicitud'] ?? 0);

                if ($codigoSolicitud <= 0) {
                    $this->dblink->rollBack();

                    return [
                        'ok' => false,
                        'mensaje' => 'No se pudo identificar la solicitud de residencia observada.'
                    ];
                }

                $okSolicitud = $this->marcarSolicitudResidenciaPendiente(
                    $codigoUsuario,
                    $codigoSolicitud,
                    $rutaRelativa
                );

                if (!$okSolicitud) {
                    $this->dblink->rollBack();

                    return [
                        'ok' => false,
                        'mensaje' => 'No se pudo registrar el nuevo recibo de residencia.'
                    ];
                }

                /*
                 * Mantiene al usuario en revisión para que soporte evalúe
                 * nuevamente el cambio solicitado.
                 */
                $this->actualizarEstadoUsuarioLocal($codigoUsuario, 1);

                $this->dblink->commit();

                return [
                    'ok' => true,
                    'mensaje' => 'Recibo reenviado correctamente. Tu cambio de residencia vuelve a revisión.',
                    'data' => [
                        'codigo_usuario'                => $codigoUsuario,
                        'tipo_subsanacion'              => 'cambio_residencia',
                        'codigo_solicitud_residencia'   => $codigoSolicitud,
                        'estado_solicitud_residencia'   => 'pendiente',
                        'comprobante_path'              => $rutaRelativa
                    ]
                ];
            }

            /*
             * Caso original:
             * cuenta inicial observada por usuario_revision.estado_revision = 3.
             */
            $okRevision = $this->registrarReenvioCuentaObservada($codigoUsuario, $rutaRelativa);

            if (!$okRevision) {
                $this->dblink->rollBack();

                return [
                    'ok' => false,
                    'mensaje' => 'No se pudo registrar el reenvío del comprobante.'
                ];
            }

            /*
             * Mantener al usuario en En revisión para que soporte lo vuelva a evaluar.
             */
            $this->actualizarEstadoUsuarioLocal($codigoUsuario, 1);

            $this->dblink->commit();

            return [
                'ok' => true,
                'mensaje' => 'Comprobante reenviado correctamente. Tu cuenta vuelve a revisión.',
                'data' => [
                    'codigo_usuario'   => $codigoUsuario,
                    'tipo_subsanacion' => 'cuenta',
                    'estado_revision'  => 1,
                    'comprobante_path' => $rutaRelativa
                ]
            ];
        } catch (Throwable $e) {
            if ($this->dblink instanceof PDO && $this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            if ($rutaRelativa !== '') {
                $this->eliminarArchivoRelativo($rutaRelativa);
            }

            error_log('[EV][CuentaObservada::subsanar] ' . $e->getMessage());

            return [
                'ok' => false,
                'mensaje' => 'Ocurrió un error al reenviar el comprobante.'
            ];
        }
    }

    private function registrarReenvioCuentaObservada(int $codigoUsuario, string $comprobantePath): bool
    {
        $sql = "
            UPDATE usuario_revision
            SET estado_revision = 1,
                comprobante_path = :path,
                fecha_reenvio = NOW(),
                fecha_actualizacion = NOW()
            WHERE codigo_usuario = :id
              AND estado_revision = 3
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':path', $comprobantePath, PDO::PARAM_STR);
        $stmt->bindValue(':id', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    private function marcarSolicitudResidenciaPendiente(
        int $codigoUsuario,
        int $codigoSolicitud,
        string $comprobantePath
    ): bool {
        $sql = "
            UPDATE usuario_residencia_solicitud
            SET estado = 'pendiente',
                comprobante_domicilio = :comprobante,
                comentario_admin = NULL,
                updated_at = CURRENT_TIMESTAMP
            WHERE codigo_solicitud = :codigo_solicitud
              AND codigo_usuario = :codigo_usuario
              AND estado = 'observada'
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':comprobante', $comprobantePath, PDO::PARAM_STR);
        $stmt->bindValue(':codigo_solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $stmt->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    private function actualizarEstadoUsuarioLocal(int $codigoUsuario, int $estado): bool
    {
        if (!in_array($estado, [0, 1, 2], true)) {
            return false;
        }

        $sql = "
            UPDATE usuario
            SET estado = :estado
            WHERE codigo_usuario = :codigo_usuario
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_INT);
        $stmt->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // ============================================================
    // VALIDACIÓN / GUARDADO DE ARCHIVO
    // ============================================================

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
                UPLOAD_ERR_PARTIAL => 'El archivo se subió solo parcialmente.',
                UPLOAD_ERR_NO_FILE => 'Debes adjuntar un comprobante.',
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
                'mensaje' => 'Formato no permitido. Sube un archivo PDF, JPG, JPEG o PNG.'
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
                'mensaje' => 'Tipo de archivo inválido. Solo se permiten PDF, JPG, JPEG o PNG.'
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

    private function guardarArchivo(
        int $codigoUsuario,
        array $file,
        string $extension,
        string $prefijo = 'comp_obs'
    ): string {
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

        $safePrefix = preg_replace('/[^a-zA-Z0-9_\-]/', '', $prefijo);
        if ($safePrefix === '') {
            $safePrefix = 'comp_obs';
        }

        $safeName = $safePrefix
            . '_'
            . $codigoUsuario
            . '_'
            . date('Ymd_His')
            . '_'
            . bin2hex(random_bytes(4))
            . '.'
            . $extension;

        $destAbs = $uploadAbsDir . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file((string)$file['tmp_name'], $destAbs)) {
            throw new RuntimeException('No se pudo guardar el archivo subido.');
        }

        return $uploadRelDir . '/' . $safeName;
    }

    private function eliminarArchivoRelativo(string $rutaRelativa): void
    {
        $rutaRelativa = trim($rutaRelativa);

        if ($rutaRelativa === '') {
            return;
        }

        /*
         * Seguridad defensiva:
         * solo eliminamos archivos dentro de la carpeta esperada.
         */
        if (strpos($rutaRelativa, 'resources/uploads/comprobantes/') !== 0) {
            return;
        }

        $projectRoot = realpath(__DIR__ . '/..');

        if ($projectRoot === false) {
            return;
        }

        $abs = $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaRelativa);

        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}