<?php
// models/ServicioEjecucion.php
// Punto 11 EV: ejecución, reprogramación, incidencias y cierre de servicios.

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

final class ServicioEjecucion extends Conexion
{
    public const MAX_ADJUNTOS = 5;
    public const MAX_BYTES_ADJUNTO = 8388608; // 8 MB

    private const ESTADOS_INCIDENCIA_ACTIVA = [
        'abierta',
        'en_atencion',
        'persiste',
        'solucion_pendiente_confirmacion',
        'revision_soporte',
        'esperando_informacion',
    ];

    private const CATEGORIAS_INCIDENCIA = [
        'servicio_incompleto' => 'Servicio incompleto',
        'resultado_diferente' => 'Resultado diferente a lo acordado',
        'problema_calidad' => 'Problema de calidad',
        'incumplimiento_fecha_hora' => 'Incumplimiento de fecha u hora',
        'servicio_no_realizado' => 'Servicio no realizado / inasistencia',
        'problema_comunicacion_trato' => 'Problema de comunicación o trato',
        'monto_condicion_diferente' => 'Monto o condición diferente a la cotización',
        'dano_durante_servicio' => 'Daño durante el servicio',
        'otro' => 'Otro',
    ];

    private function texto($valor, int $maximo): string
    {
        $texto = trim((string)$valor);
        return $texto === '' ? '' : mb_substr($texto, 0, $maximo, 'UTF-8');
    }

    private function jsonDecode($valor): array
    {
        if (is_array($valor)) {
            return $valor;
        }
        $raw = trim((string)$valor);
        if ($raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function jsonEncode(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function obtenerSolicitudParticipante(int $codigoSolicitud, int $codigoUsuario, bool $forUpdate = false): ?array
    {
        $lock = $forUpdate ? ' FOR UPDATE' : '';
        $sql = "
            SELECT
                ss.*,
                p.titulo AS titulo_servicio,
                p.descripcion AS descripcion_servicio,
                p.imagen_portada,
                us.nombre AS nombre_comprador,
                up.nombre AS nombre_proveedor,
                cp.codigo_solicitud_servicio_propuesta,
                cp.version AS version_cotizacion,
                cp.modalidad,
                cp.fecha_propuesta AS fecha_cotizada,
                cp.hora_inicio AS hora_inicio_cotizada,
                cp.hora_fin AS hora_fin_cotizada,
                cp.alcance_confirmado,
                cp.monto_propuesto,
                cp.condicion_pago,
                cp.monto_adelanto,
                cp.duracion_estimada,
                cp.requisitos,
                cp.mensaje_proveedor AS mensaje_cotizacion
            FROM solicitud_servicio ss
            INNER JOIN producto p ON p.codigo_producto = ss.codigo_producto
            INNER JOIN usuario us ON us.codigo_usuario = ss.codigo_usuario_solicitante
            INNER JOIN usuario up ON up.codigo_usuario = ss.codigo_usuario_proveedor
            LEFT JOIN solicitud_servicio_propuesta cp
                   ON cp.codigo_solicitud_servicio = ss.codigo_solicitud_servicio
                  AND cp.estado = 'aceptada'
            WHERE ss.codigo_solicitud_servicio = :solicitud
              AND (
                    ss.codigo_usuario_solicitante = :usuario_solicitante
                    OR ss.codigo_usuario_proveedor = :usuario_proveedor
                  )
            LIMIT 1{$lock}
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':usuario_solicitante', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':usuario_proveedor', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function rolUsuario(array $solicitud, int $codigoUsuario): string
    {
        return (int)($solicitud['codigo_usuario_solicitante'] ?? 0) === $codigoUsuario
            ? 'solicitante'
            : 'proveedor';
    }

    private function etiquetaRol(string $rol): string
    {
        return $rol === 'proveedor' ? 'Proveedor' : 'Comprador';
    }

    private function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            'coordinacion_confirmada' => 'Pendiente de ejecución',
            'servicio_en_ejecucion' => 'Servicio en ejecución',
            'servicio_realizado_proveedor' => 'Pendiente de confirmación',
            'incidencia_abierta' => 'Problema reportado',
            'incidencia_en_atencion' => 'Problema en atención',
            'solucion_pendiente_confirmacion' => 'Solución pendiente de confirmación',
            'revision_soporte' => 'En revisión por soporte',
            'servicio_confirmado_solicitante' => 'Servicio completado',
            'cancelada_solicitante' => 'Cancelado por comprador',
            'cancelada_proveedor' => 'Cancelado por proveedor',
            'cancelada_soporte' => 'Cancelado por soporte',
            default => str_replace('_', ' ', ucfirst($estado)),
        };
    }

    private function registrarInteraccion(
        int $codigoSolicitud,
        int $codigoUsuario,
        string $rol,
        string $tipo,
        string $mensaje,
        array $payload = []
    ): void {
        $sql = "
            INSERT INTO solicitud_servicio_interaccion
            (
                codigo_solicitud_servicio,
                codigo_usuario_autor,
                rol_autor,
                tipo_interaccion,
                mensaje,
                payload_json
            )
            VALUES
            (:solicitud, :usuario, :rol, :tipo, :mensaje, :payload)
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':rol', $rol, PDO::PARAM_STR);
        $st->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $st->bindValue(':mensaje', $mensaje !== '' ? $mensaje : null, $mensaje !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $st->bindValue(':payload', $payload ? $this->jsonEncode($payload) : null, $payload ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $st->execute();
    }

    private function notificarUsuario(
        int $codigoUsuario,
        int $codigoSolicitud,
        string $subcategoria,
        string $titulo,
        string $mensaje,
        string $ruta,
        array $payloadExtra = []
    ): void {
        if ($codigoUsuario <= 0 || $codigoSolicitud <= 0) {
            return;
        }

        $payload = array_merge([
            'codigo_solicitud_servicio' => $codigoSolicitud,
            'ruta' => $ruta,
        ], $payloadExtra);

        $sql = "
            INSERT INTO notificacion
            (codigo_usuario, canal, categoria, subcategoria, referencia_id, titulo, mensaje, payload_json, estado)
            VALUES
            (:usuario, 'app', 'servicio', :subcategoria, :referencia, :titulo, :mensaje, :payload, 'no_leida')
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':subcategoria', mb_substr($subcategoria, 0, 80, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':referencia', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':titulo', mb_substr($titulo, 0, 160, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':mensaje', mb_substr($mensaje, 0, 1000, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':payload', $this->jsonEncode($payload), PDO::PARAM_STR);
        $st->execute();
    }

    private function notificarContraparte(array $solicitud, int $codigoAutor, string $subcategoria, string $titulo, string $mensaje): void
    {
        $esComprador = (int)$solicitud['codigo_usuario_solicitante'] === $codigoAutor;
        $destino = $esComprador
            ? (int)$solicitud['codigo_usuario_proveedor']
            : (int)$solicitud['codigo_usuario_solicitante'];
        $ruta = $esComprador
            ? '/mis-solicitudes-servicio-vendedor'
            : '/mis-solicitudes-servicio-comprador';
        $rolDestino = $esComprador ? 'proveedor' : 'solicitante';

        $this->notificarUsuario(
            $destino,
            (int)$solicitud['codigo_solicitud_servicio'],
            $subcategoria,
            $titulo,
            $mensaje,
            $ruta,
            ['rol_destino' => $rolDestino, 'titulo_servicio' => (string)$solicitud['titulo_servicio']]
        );
    }

    private function marcarNovedadesServicioRevisadas(int $codigoUsuario, int $codigoSolicitud): int
    {
        if ($codigoUsuario <= 0 || $codigoSolicitud <= 0) {
            return 0;
        }

        $sql = "
            UPDATE notificacion
            SET estado = 'leida',
                read_at = COALESCE(read_at, CURRENT_TIMESTAMP)
            WHERE codigo_usuario = :codigo_usuario
              AND categoria = 'servicio'
              AND referencia_id = :codigo_solicitud
              AND estado = 'no_leida'
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':codigo_solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->execute();

        return (int)$st->rowCount();
    }

    private function notificarSoporte(array $solicitud, int $codigoIncidencia): void
    {
        $sql = "
            SELECT u.codigo_usuario
            FROM usuario u
            INNER JOIN rol r ON r.codigo_rol = u.codigo_rol
            WHERE u.estado = 2
              AND LOWER(TRIM(r.nombre)) IN ('soporte', 'admin', 'administrador')
        ";
        $rows = $this->dblink->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as $row) {
            $this->notificarUsuario(
                (int)$row['codigo_usuario'],
                (int)$solicitud['codigo_solicitud_servicio'],
                'revision_soporte_solicitada',
                'Incidencia de servicio para revisión',
                'Un caso del servicio “' . (string)$solicitud['titulo_servicio'] . '” fue escalado a soporte.',
                '/atender-servicios',
                [
                    'rol_destino' => 'soporte',
                    'codigo_incidencia' => $codigoIncidencia,
                    'titulo_servicio' => (string)$solicitud['titulo_servicio'],
                ]
            );
        }
    }

    private function fechaOperativa(array $solicitud): ?string
    {
        $fecha = trim((string)($solicitud['fecha_ejecucion_vigente'] ?? ''));
        if ($fecha !== '') {
            return substr($fecha, 0, 10);
        }
        $cotizada = trim((string)($solicitud['fecha_cotizada'] ?? ''));
        return $cotizada !== '' ? substr($cotizada, 0, 10) : null;
    }

    private function horaInicioOperativa(array $solicitud): ?string
    {
        $hora = trim((string)($solicitud['hora_inicio_vigente'] ?? ''));
        if ($hora === '') {
            $hora = trim((string)($solicitud['hora_inicio_cotizada'] ?? ''));
        }
        return $hora !== '' ? substr($hora, 0, 8) : null;
    }

    private function horaFinOperativa(array $solicitud): ?string
    {
        $hora = trim((string)($solicitud['hora_fin_vigente'] ?? ''));
        if ($hora === '') {
            $hora = trim((string)($solicitud['hora_fin_cotizada'] ?? ''));
        }
        return $hora !== '' ? substr($hora, 0, 8) : null;
    }

    private function normalizarFecha($valor): string
    {
        $raw = trim((string)$valor);
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $raw, new DateTimeZone('America/Lima'));
        $errors = DateTimeImmutable::getLastErrors();
        $warnings = is_array($errors) ? (int)($errors['warning_count'] ?? 0) : 0;
        $countErrors = is_array($errors) ? (int)($errors['error_count'] ?? 0) : 0;
        if (!$fecha || $warnings > 0 || $countErrors > 0 || $fecha->format('Y-m-d') !== $raw) {
            throw new InvalidArgumentException('FECHA_REPROGRAMACION_INVALIDA');
        }
        return $raw;
    }

    private function normalizarHora($valor, bool $obligatoria = true): ?string
    {
        $raw = trim((string)$valor);
        if ($raw === '' && !$obligatoria) {
            return null;
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $raw)) {
            throw new InvalidArgumentException('HORA_REPROGRAMACION_INVALIDA');
        }
        return $raw . ':00';
    }

    private function validarMomentoFuturo(string $fecha, string $hora): void
    {
        $tz = new DateTimeZone('America/Lima');
        $momento = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $fecha . ' ' . $hora, $tz);
        if (!$momento || $momento <= new DateTimeImmutable('now', $tz)) {
            throw new InvalidArgumentException('FECHA_REPROGRAMACION_PASADA');
        }
    }

    private function obtenerReprogramacionPendiente(int $codigoSolicitud, bool $forUpdate = false): ?array
    {
        $lock = $forUpdate ? ' FOR UPDATE' : '';
        $st = $this->dblink->prepare("
            SELECT r.*, u.nombre AS nombre_propone
            FROM solicitud_servicio_reprogramacion r
            INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario_propone
            WHERE r.codigo_solicitud_servicio = :solicitud
              AND r.estado = 'pendiente'
            ORDER BY r.codigo_reprogramacion DESC
            LIMIT 1{$lock}
        ");
        $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function obtenerIncidenciaActiva(int $codigoSolicitud, bool $forUpdate = false): ?array
    {
        $placeholders = implode(',', array_fill(0, count(self::ESTADOS_INCIDENCIA_ACTIVA), '?'));
        $lock = $forUpdate ? ' FOR UPDATE' : '';
        $sql = "
            SELECT
                i.*,
                ur.nombre AS nombre_reporta,
                uresp.nombre AS nombre_responde,
                usol.nombre AS nombre_solucion,
                usp.nombre AS nombre_soporte
            FROM solicitud_servicio_incidencia i
            INNER JOIN usuario ur ON ur.codigo_usuario = i.codigo_usuario_reporta
            LEFT JOIN usuario uresp ON uresp.codigo_usuario = i.codigo_usuario_responde
            LEFT JOIN usuario usol ON usol.codigo_usuario = i.codigo_usuario_solucion
            LEFT JOIN usuario usp ON usp.codigo_usuario = i.codigo_usuario_soporte
            WHERE i.codigo_solicitud_servicio = ?
              AND i.estado IN ({$placeholders})
            ORDER BY i.codigo_incidencia DESC
            LIMIT 1{$lock}
        ";
        $st = $this->dblink->prepare($sql);
        $params = array_merge([$codigoSolicitud], self::ESTADOS_INCIDENCIA_ACTIVA);
        foreach ($params as $index => $value) {
            $st->bindValue($index + 1, $value, $index === 0 ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function incidenciaPermiteReprogramarOCancelar(?array $incidencia): bool
    {
        if (!$incidencia) {
            return false;
        }

        $categoria = (string)($incidencia['categoria'] ?? '');
        $estado = (string)($incidencia['estado'] ?? '');
        $estadoOrigen = (string)($incidencia['estado_solicitud_origen'] ?? '');

        return in_array($categoria, ['incumplimiento_fecha_hora', 'servicio_no_realizado'], true)
            && in_array($estado, ['abierta', 'en_atencion', 'persiste'], true)
            && (int)($incidencia['requiere_soporte'] ?? 0) === 0
            && $estadoOrigen === 'coordinacion_confirmada';
    }

    private function listarAdjuntosIncidencia(int $codigoIncidencia): array
    {
        $st = $this->dblink->prepare("
            SELECT codigo_incidencia_adjunto, codigo_usuario_autor, contexto, ruta, nombre_original, mime, peso_bytes, created_at
            FROM solicitud_servicio_incidencia_adjunto
            WHERE codigo_incidencia = :incidencia
            ORDER BY codigo_incidencia_adjunto
        ");
        $st->bindValue(':incidencia', $codigoIncidencia, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function normalizarArchivos(array $files): array
    {
        $out = [];
        foreach ($files as $campo => $file) {
            if (!is_array($file) || !isset($file['name'])) {
                continue;
            }
            if (is_array($file['name'])) {
                foreach ($file['name'] as $i => $name) {
                    if ((string)$name === '') {
                        continue;
                    }
                    $out[] = [
                        'name' => $name,
                        'type' => $file['type'][$i] ?? '',
                        'tmp_name' => $file['tmp_name'][$i] ?? '',
                        'error' => $file['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $file['size'][$i] ?? 0,
                    ];
                }
            } elseif ((string)$file['name'] !== '') {
                $out[] = $file;
            }
        }
        return $out;
    }

    private function guardarAdjuntosIncidencia(
        int $codigoIncidencia,
        int $codigoUsuario,
        string $contexto,
        array $files
    ): array {
        $archivos = $this->normalizarArchivos($files);
        if (!$archivos) {
            return [];
        }
        if (count($archivos) > self::MAX_ADJUNTOS) {
            throw new InvalidArgumentException('MAX_ADJUNTOS_EXCEDIDO');
        }

        $permitidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
        ];
        $validados = [];
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        foreach ($archivos as $archivo) {
            $error = (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
            $size = (int)($archivo['size'] ?? 0);
            $tmp = (string)($archivo['tmp_name'] ?? '');
            if ($error !== UPLOAD_ERR_OK || $tmp === '' || !is_uploaded_file($tmp)) {
                throw new InvalidArgumentException('ADJUNTO_INVALIDO');
            }
            if ($size <= 0 || $size > self::MAX_BYTES_ADJUNTO) {
                throw new InvalidArgumentException('ADJUNTO_PESO_INVALIDO');
            }
            $mime = (string)$finfo->file($tmp);
            if (!isset($permitidos[$mime])) {
                throw new InvalidArgumentException('ADJUNTO_FORMATO_INVALIDO');
            }
            if (str_starts_with($mime, 'image/') && @getimagesize($tmp) === false) {
                throw new InvalidArgumentException('ADJUNTO_FORMATO_INVALIDO');
            }
            $validados[] = [
                'tmp' => $tmp,
                'size' => $size,
                'mime' => $mime,
                'ext' => $permitidos[$mime],
                'name' => mb_substr((string)($archivo['name'] ?? 'archivo'), 0, 255, 'UTF-8'),
            ];
        }

        $year = date('Y');
        $month = date('m');
        $dir = __DIR__ . '/../resources/uploads/servicios/incidencias/' . $year . '/' . $month;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo preparar la carpeta de evidencias.');
        }

        $guardados = [];
        try {
            $ins = $this->dblink->prepare("
                INSERT INTO solicitud_servicio_incidencia_adjunto
                (codigo_incidencia, codigo_usuario_autor, contexto, ruta, nombre_original, mime, peso_bytes)
                VALUES (:incidencia, :usuario, :contexto, :ruta, :nombre, :mime, :peso)
            ");
            foreach ($validados as $archivo) {
                $nombre = bin2hex(random_bytes(18)) . '.' . $archivo['ext'];
                $destino = $dir . '/' . $nombre;
                if (!move_uploaded_file($archivo['tmp'], $destino)) {
                    throw new RuntimeException('No se pudo guardar una evidencia.');
                }
                $ruta = '/resources/uploads/servicios/incidencias/' . $year . '/' . $month . '/' . $nombre;
                // Registrar la ruta antes del INSERT permite limpiar el archivo incluso si falla la escritura en BD.
                $guardados[] = ['ruta' => $ruta, 'nombre_original' => $archivo['name'], 'mime' => $archivo['mime']];
                $ins->bindValue(':incidencia', $codigoIncidencia, PDO::PARAM_INT);
                $ins->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
                $ins->bindValue(':contexto', $contexto, PDO::PARAM_STR);
                $ins->bindValue(':ruta', $ruta, PDO::PARAM_STR);
                $ins->bindValue(':nombre', $archivo['name'], PDO::PARAM_STR);
                $ins->bindValue(':mime', $archivo['mime'], PDO::PARAM_STR);
                $ins->bindValue(':peso', $archivo['size'], PDO::PARAM_INT);
                $ins->execute();
            }
            return $guardados;
        } catch (Throwable $e) {
            foreach ($guardados as $guardado) {
                $path = __DIR__ . '/..' . (string)$guardado['ruta'];
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            throw $e;
        }
    }

    private function eliminarAdjuntosFisicos(array $adjuntos): void
    {
        foreach ($adjuntos as $adjunto) {
            $ruta = (string)($adjunto['ruta'] ?? '');
            if ($ruta === '' || !str_starts_with($ruta, '/resources/uploads/servicios/incidencias/')) {
                continue;
            }
            $path = __DIR__ . '/..' . $ruta;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function crearCalificacionesPendientes(array $solicitud): void
    {
        $habilitacion = date('Y-m-d H:i:s');
        $limite = (new DateTimeImmutable($habilitacion, new DateTimeZone('America/Lima')))
            ->modify('+7 days')
            ->format('Y-m-d H:i:s');

        $sql = "
            INSERT IGNORE INTO calificacion_servicio
            (
                codigo_solicitud_servicio,
                codigo_usuario_calificador,
                codigo_usuario_calificado,
                rol_calificador,
                rol_calificado,
                estado,
                fecha_habilitacion,
                fecha_limite
            )
            VALUES
            (:solicitud1, :comprador1, :proveedor1, 'comprador', 'proveedor', 'pendiente', :habilitacion1, :limite1),
            (:solicitud2, :proveedor2, :comprador2, 'proveedor', 'comprador', 'pendiente', :habilitacion2, :limite2)
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':solicitud1', (int)$solicitud['codigo_solicitud_servicio'], PDO::PARAM_INT);
        $st->bindValue(':comprador1', (int)$solicitud['codigo_usuario_solicitante'], PDO::PARAM_INT);
        $st->bindValue(':proveedor1', (int)$solicitud['codigo_usuario_proveedor'], PDO::PARAM_INT);
        $st->bindValue(':solicitud2', (int)$solicitud['codigo_solicitud_servicio'], PDO::PARAM_INT);
        $st->bindValue(':proveedor2', (int)$solicitud['codigo_usuario_proveedor'], PDO::PARAM_INT);
        $st->bindValue(':comprador2', (int)$solicitud['codigo_usuario_solicitante'], PDO::PARAM_INT);
        $st->bindValue(':habilitacion1', $habilitacion, PDO::PARAM_STR);
        $st->bindValue(':limite1', $limite, PDO::PARAM_STR);
        $st->bindValue(':habilitacion2', $habilitacion, PDO::PARAM_STR);
        $st->bindValue(':limite2', $limite, PDO::PARAM_STR);
        $st->execute();
    }

    private function completarServicio(array $solicitud, int $codigoUsuario, string $rol, string $mensaje): void
    {
        $st = $this->dblink->prepare("
            UPDATE solicitud_servicio
            SET estado = 'servicio_confirmado_solicitante',
                estado_anterior = :estado_anterior,
                motivo_estado = :motivo,
                fecha_confirmacion_solicitante = NOW(),
                fecha_cierre = NOW(),
                updated_at = CURRENT_TIMESTAMP
            WHERE codigo_solicitud_servicio = :solicitud
        ");
        $st->bindValue(':estado_anterior', (string)$solicitud['estado'], PDO::PARAM_STR);
        $st->bindValue(':motivo', $mensaje, PDO::PARAM_STR);
        $st->bindValue(':solicitud', (int)$solicitud['codigo_solicitud_servicio'], PDO::PARAM_INT);
        $st->execute();

        $this->registrarInteraccion(
            (int)$solicitud['codigo_solicitud_servicio'],
            $codigoUsuario,
            $rol,
            'servicio_completado',
            $mensaje,
            ['estado_anterior' => (string)$solicitud['estado']]
        );
        $this->crearCalificacionesPendientes($solicitud);
    }

    private function obtenerCalificacionUsuario(int $codigoSolicitud, int $codigoUsuario): ?array
    {
        $st = $this->dblink->prepare("
            SELECT
                cs.*,
                uc.nombre AS nombre_calificado
            FROM calificacion_servicio cs
            INNER JOIN usuario uc ON uc.codigo_usuario = cs.codigo_usuario_calificado
            WHERE cs.codigo_solicitud_servicio = :solicitud
              AND cs.codigo_usuario_calificador = :usuario
            LIMIT 1
        ");
        $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return [
            'codigo_calificacion_servicio' => (int)$row['codigo_calificacion_servicio'],
            'rol_calificador' => (string)$row['rol_calificador'],
            'rol_calificado' => (string)$row['rol_calificado'],
            'nombre_calificado' => (string)$row['nombre_calificado'],
            'puntaje' => $row['puntaje'] !== null ? (int)$row['puntaje'] : null,
            'comentario' => (string)($row['comentario'] ?? ''),
            'estado' => (string)$row['estado'],
            'fecha_limite' => $row['fecha_limite'] ?? null,
            'fecha_envio' => $row['fecha_envio'] ?? null,
        ];
    }

    private function formatearReprogramacion(array $row): array
    {
        return [
            'codigo_reprogramacion' => (int)$row['codigo_reprogramacion'],
            'version_operativa_origen' => (int)$row['version_operativa_origen'],
            'codigo_usuario_propone' => (int)$row['codigo_usuario_propone'],
            'rol_propone' => (string)$row['rol_propone'],
            'nombre_propone' => (string)($row['nombre_propone'] ?? 'Vecino'),
            'fecha_anterior' => $row['fecha_anterior'] ?? null,
            'hora_inicio_anterior' => $row['hora_inicio_anterior'] ?? null,
            'hora_fin_anterior' => $row['hora_fin_anterior'] ?? null,
            'fecha_nueva' => $row['fecha_nueva'] ?? null,
            'hora_inicio_nueva' => $row['hora_inicio_nueva'] ?? null,
            'hora_fin_nueva' => $row['hora_fin_nueva'] ?? null,
            'motivo' => (string)$row['motivo'],
            'comentario' => (string)($row['comentario'] ?? ''),
            'estado' => (string)$row['estado'],
            'respuesta_comentario' => (string)($row['respuesta_comentario'] ?? ''),
            'fecha_respuesta' => $row['fecha_respuesta'] ?? null,
            'created_at' => $row['created_at'] ?? null,
        ];
    }

    private function formatearIncidencia(array $row): array
    {
        $codigo = (int)$row['codigo_incidencia'];
        return [
            'codigo_incidencia' => $codigo,
            'numero_incidencia' => (int)$row['numero_incidencia'],
            'codigo_usuario_reporta' => (int)$row['codigo_usuario_reporta'],
            'rol_reporta' => (string)$row['rol_reporta'],
            'nombre_reporta' => (string)($row['nombre_reporta'] ?? 'Vecino'),
            'categoria' => (string)$row['categoria'],
            'categoria_texto' => self::CATEGORIAS_INCIDENCIA[(string)$row['categoria']] ?? 'Otro',
            'descripcion' => (string)$row['descripcion'],
            'estado_solicitud_origen' => (string)($row['estado_solicitud_origen'] ?? ''),
            'estado' => (string)$row['estado'],
            'respuesta' => (string)($row['respuesta'] ?? ''),
            'nombre_responde' => (string)($row['nombre_responde'] ?? ''),
            'fecha_respuesta' => $row['fecha_respuesta'] ?? null,
            'solucion' => (string)($row['solucion'] ?? ''),
            'nombre_solucion' => (string)($row['nombre_solucion'] ?? ''),
            'fecha_solucion' => $row['fecha_solucion'] ?? null,
            'requiere_soporte' => (int)$row['requiere_soporte'],
            'resultado_soporte' => (string)($row['resultado_soporte'] ?? ''),
            'resolucion_soporte' => (string)($row['resolucion_soporte'] ?? ''),
            'nombre_soporte' => (string)($row['nombre_soporte'] ?? ''),
            'fecha_resolucion_soporte' => $row['fecha_resolucion_soporte'] ?? null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'adjuntos' => $this->listarAdjuntosIncidencia($codigo),
        ];
    }

    private function listarReprogramaciones(int $codigoSolicitud): array
    {
        $st = $this->dblink->prepare("
            SELECT r.*, u.nombre AS nombre_propone
            FROM solicitud_servicio_reprogramacion r
            INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario_propone
            WHERE r.codigo_solicitud_servicio = :solicitud
            ORDER BY r.codigo_reprogramacion DESC
        ");
        $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->execute();
        return array_map(fn(array $row): array => $this->formatearReprogramacion($row), $st->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    private function listarIncidencias(int $codigoSolicitud): array
    {
        $st = $this->dblink->prepare("
            SELECT
                i.*,
                ur.nombre AS nombre_reporta,
                uresp.nombre AS nombre_responde,
                usol.nombre AS nombre_solucion,
                usp.nombre AS nombre_soporte
            FROM solicitud_servicio_incidencia i
            INNER JOIN usuario ur ON ur.codigo_usuario = i.codigo_usuario_reporta
            LEFT JOIN usuario uresp ON uresp.codigo_usuario = i.codigo_usuario_responde
            LEFT JOIN usuario usol ON usol.codigo_usuario = i.codigo_usuario_solucion
            LEFT JOIN usuario usp ON usp.codigo_usuario = i.codigo_usuario_soporte
            WHERE i.codigo_solicitud_servicio = :solicitud
            ORDER BY i.codigo_incidencia DESC
        ");
        $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->execute();
        return array_map(fn(array $row): array => $this->formatearIncidencia($row), $st->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    private function listarTimeline(int $codigoSolicitud): array
    {
        $st = $this->dblink->prepare("
            SELECT
                i.codigo_solicitud_servicio_interaccion,
                i.codigo_usuario_autor,
                i.rol_autor,
                i.tipo_interaccion,
                i.mensaje,
                i.payload_json,
                i.created_at,
                u.nombre AS nombre_autor
            FROM solicitud_servicio_interaccion i
            INNER JOIN usuario u ON u.codigo_usuario = i.codigo_usuario_autor
            WHERE i.codigo_solicitud_servicio = :solicitud
            ORDER BY i.codigo_solicitud_servicio_interaccion DESC
            LIMIT 120
        ");
        $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(function (array $row): array {
            return [
                'codigo_interaccion' => (int)$row['codigo_solicitud_servicio_interaccion'],
                'codigo_usuario_autor' => (int)$row['codigo_usuario_autor'],
                'rol_autor' => (string)$row['rol_autor'],
                'tipo_interaccion' => (string)$row['tipo_interaccion'],
                'mensaje' => (string)($row['mensaje'] ?? ''),
                'payload' => $this->jsonDecode($row['payload_json'] ?? null),
                'nombre_autor' => (string)$row['nombre_autor'],
                'created_at' => $row['created_at'] ?? null,
            ];
        }, $rows);
    }

    private function permisos(array $solicitud, int $codigoUsuario, ?array $reprogramacion, ?array $incidencia, ?array $calificacion): array
    {
        $rol = $this->rolUsuario($solicitud, $codigoUsuario);
        $estado = (string)$solicitud['estado'];
        $esProveedor = $rol === 'proveedor';
        $esComprador = $rol === 'solicitante';
        $incidenciaActiva = $incidencia !== null;
        $incidenciaPermiteRecoordinar = $this->incidenciaPermiteReprogramarOCancelar($incidencia);

        return [
            'iniciar_servicio' => $esProveedor && $estado === 'coordinacion_confirmada' && !$incidenciaActiva,
            'marcar_realizado' => $esProveedor && in_array($estado, ['coordinacion_confirmada', 'servicio_en_ejecucion'], true) && !$incidenciaActiva,
            'proponer_reprogramacion' => $reprogramacion === null
                && (($estado === 'coordinacion_confirmada' && !$incidenciaActiva) || $incidenciaPermiteRecoordinar),
            'responder_reprogramacion' => $reprogramacion !== null && (int)$reprogramacion['codigo_usuario_propone'] !== $codigoUsuario,
            'cancelar_reprogramacion' => $reprogramacion !== null && (int)$reprogramacion['codigo_usuario_propone'] === $codigoUsuario,
            'cancelar_servicio' => ($estado === 'coordinacion_confirmada' && !$incidenciaActiva) || $incidenciaPermiteRecoordinar,
            'confirmar_realizado' => $esComprador && $estado === 'servicio_realizado_proveedor' && !$incidenciaActiva,
            'reportar_problema' => in_array($estado, ['coordinacion_confirmada', 'servicio_en_ejecucion', 'servicio_realizado_proveedor'], true) && !$incidenciaActiva,
            'responder_incidencia' => $incidenciaActiva
                && (
                    (int)$incidencia['codigo_usuario_reporta'] !== $codigoUsuario
                    || (
                        (string)$incidencia['estado'] === 'esperando_informacion'
                        && (int)($incidencia['requiere_soporte'] ?? 0) === 1
                    )
                )
                && in_array((string)$incidencia['estado'], ['abierta', 'persiste', 'esperando_informacion'], true),
            'registrar_solucion' => $esProveedor
                && $incidenciaActiva
                && (int)($incidencia['requiere_soporte'] ?? 0) === 0
                && in_array((string)$incidencia['estado'], ['abierta', 'en_atencion', 'persiste'], true),
            'confirmar_solucion' => $esComprador && $incidenciaActiva && (string)$incidencia['estado'] === 'solucion_pendiente_confirmacion',
            'problema_persiste' => $esComprador && $incidenciaActiva && (string)$incidencia['estado'] === 'solucion_pendiente_confirmacion',
            'solicitar_soporte' => $incidenciaActiva
                && (int)$incidencia['requiere_soporte'] === 0
                && !in_array((string)$incidencia['estado'], ['cerrada', 'resuelta', 'cancelada'], true),
            'calificar' => $calificacion !== null && (string)$calificacion['estado'] === 'pendiente',
        ];
    }

    public function obtenerOperacion(int $codigoSolicitud, int $codigoUsuario): array
    {
        if ($codigoSolicitud <= 0 || $codigoUsuario <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar el servicio.'];
        }

        try {
            $this->sincronizarRecordatoriosSolicitud($codigoSolicitud);
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario);
            if (!$solicitud) {
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $rol = $this->rolUsuario($solicitud, $codigoUsuario);

            // Abrir la gestión equivale a revisar las novedades operativas de este servicio.
            // Solo se marcan como leídas las notificaciones de la persona autenticada.
            $novedadesMarcadas = $this->marcarNovedadesServicioRevisadas($codigoUsuario, $codigoSolicitud);

            $reprogramaciones = $this->listarReprogramaciones($codigoSolicitud);
            $reprogramacion = null;
            foreach ($reprogramaciones as $item) {
                if ((string)$item['estado'] === 'pendiente') {
                    $reprogramacion = $item;
                    break;
                }
            }
            $incidencias = $this->listarIncidencias($codigoSolicitud);
            $incidencia = null;
            foreach ($incidencias as $item) {
                if (in_array((string)$item['estado'], self::ESTADOS_INCIDENCIA_ACTIVA, true)) {
                    $incidencia = $item;
                    break;
                }
            }
            $calificacion = $this->obtenerCalificacionUsuario($codigoSolicitud, $codigoUsuario);

            $fechaOriginal = trim((string)($solicitud['fecha_ejecucion_original'] ?? ''));
            if ($fechaOriginal === '') {
                $fechaOriginal = trim((string)($solicitud['fecha_cotizada'] ?? ''));
                $fechaOriginal = $fechaOriginal !== '' ? substr($fechaOriginal, 0, 10) : '';
            }

            return [
                'ok' => true,
                'data' => [
                    'solicitud' => [
                        'codigo_solicitud_servicio' => (int)$solicitud['codigo_solicitud_servicio'],
                        'codigo_producto' => (int)$solicitud['codigo_producto'],
                        'titulo_servicio' => (string)$solicitud['titulo_servicio'],
                        'descripcion_servicio' => (string)$solicitud['descripcion_servicio'],
                        'imagen_portada' => (string)$solicitud['imagen_portada'],
                        'codigo_usuario_solicitante' => (int)$solicitud['codigo_usuario_solicitante'],
                        'codigo_usuario_proveedor' => (int)$solicitud['codigo_usuario_proveedor'],
                        'nombre_comprador' => (string)$solicitud['nombre_comprador'],
                        'nombre_proveedor' => (string)$solicitud['nombre_proveedor'],
                        'rol_actual' => $rol,
                        'estado' => (string)$solicitud['estado'],
                        'estado_texto' => $this->etiquetaEstado((string)$solicitud['estado']),
                        'motivo_estado' => (string)($solicitud['motivo_estado'] ?? ''),
                        'fecha_ejecucion_original' => $fechaOriginal !== '' ? $fechaOriginal : null,
                        'hora_inicio_original' => $solicitud['hora_inicio_original'] ?? $solicitud['hora_inicio_cotizada'] ?? null,
                        'hora_fin_original' => $solicitud['hora_fin_original'] ?? $solicitud['hora_fin_cotizada'] ?? null,
                        'fecha_ejecucion_vigente' => $this->fechaOperativa($solicitud),
                        'hora_inicio_vigente' => $this->horaInicioOperativa($solicitud),
                        'hora_fin_vigente' => $this->horaFinOperativa($solicitud),
                        'version_operativa' => (int)($solicitud['version_operativa'] ?? 1),
                        'fecha_inicio_servicio' => $solicitud['fecha_inicio_servicio'] ?? null,
                        'fecha_realizado_proveedor' => $solicitud['fecha_realizado_proveedor'] ?? null,
                        'fecha_limite_confirmacion' => $solicitud['fecha_limite_confirmacion'] ?? null,
                        'created_at' => $solicitud['created_at'] ?? null,
                    ],
                    'cotizacion' => [
                        'codigo_solicitud_servicio_propuesta' => (int)($solicitud['codigo_solicitud_servicio_propuesta'] ?? 0),
                        'version' => (int)($solicitud['version_cotizacion'] ?? 0),
                        'modalidad' => (string)($solicitud['modalidad'] ?? ''),
                        'alcance_confirmado' => (string)($solicitud['alcance_confirmado'] ?? ''),
                        'monto_propuesto' => $solicitud['monto_propuesto'] ?? null,
                        'condicion_pago' => (string)($solicitud['condicion_pago'] ?? ''),
                        'monto_adelanto' => $solicitud['monto_adelanto'] ?? null,
                        'duracion_estimada' => (string)($solicitud['duracion_estimada'] ?? ''),
                        'requisitos' => (string)($solicitud['requisitos'] ?? ''),
                        'mensaje_proveedor' => (string)($solicitud['mensaje_cotizacion'] ?? ''),
                    ],
                    'reprogramacion_pendiente' => $reprogramacion,
                    'reprogramaciones' => $reprogramaciones,
                    'incidencia_activa' => $incidencia,
                    'incidencias' => $incidencias,
                    'calificacion' => $calificacion,
                    'timeline' => $this->listarTimeline($codigoSolicitud),
                    'permisos' => $this->permisos($solicitud, $codigoUsuario, $reprogramacion, $incidencia, $calificacion),
                    'categorias_incidencia' => self::CATEGORIAS_INCIDENCIA,
                    'novedades_marcadas_leidas' => $novedadesMarcadas,
                ],
            ];
        } catch (Throwable $e) {
            error_log('[EV][ServicioEjecucion][obtenerOperacion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_OBTENER_OPERACION_SERVICIO', 'mensaje' => 'No se pudo cargar la gestión del servicio.'];
        }
    }

    public function iniciarServicio(int $codigoSolicitud, int $codigoProveedor): array
    {
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoProveedor, true);
            if (!$solicitud || $this->rolUsuario($solicitud, $codigoProveedor) !== 'proveedor') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta coordinación.'];
            }
            if ((string)$solicitud['estado'] !== 'coordinacion_confirmada' || $this->obtenerIncidenciaActiva($codigoSolicitud, true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'El servicio no puede iniciarse desde su estado actual.'];
            }

            $st = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'servicio_en_ejecucion',
                    estado_anterior = :estado_anterior,
                    motivo_estado = 'El proveedor inició la ejecución del servicio.',
                    fecha_inicio_servicio = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $st->bindValue(':estado_anterior', (string)$solicitud['estado'], PDO::PARAM_STR);
            $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $st->execute();

            $this->registrarInteraccion($codigoSolicitud, $codigoProveedor, 'proveedor', 'servicio_iniciado', 'El proveedor inició la ejecución del servicio.');
            $this->notificarContraparte($solicitud, $codigoProveedor, 'servicio_iniciado', 'El servicio fue iniciado', 'El proveedor inició el servicio “' . (string)$solicitud['titulo_servicio'] . '”.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'El servicio fue marcado como iniciado.', 'estado' => 'servicio_en_ejecucion'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][iniciarServicio] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_INICIAR_SERVICIO', 'mensaje' => 'No se pudo iniciar el servicio.'];
        }
    }

    public function marcarRealizado(int $codigoSolicitud, int $codigoProveedor): array
    {
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoProveedor, true);
            if (!$solicitud || $this->rolUsuario($solicitud, $codigoProveedor) !== 'proveedor') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta coordinación.'];
            }
            $estado = (string)$solicitud['estado'];
            if (!in_array($estado, ['coordinacion_confirmada', 'servicio_en_ejecucion'], true) || $this->obtenerIncidenciaActiva($codigoSolicitud, true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'El servicio no puede marcarse como realizado desde su estado actual.'];
            }

            $st = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'servicio_realizado_proveedor',
                    estado_anterior = :anterior,
                    motivo_estado = 'El proveedor indicó que el servicio fue realizado y espera confirmación.',
                    fecha_realizado_proveedor = NOW(),
                    fecha_limite_confirmacion = DATE_ADD(NOW(), INTERVAL 72 HOUR),
                    fecha_revision_soporte_sugerida = DATE_ADD(NOW(), INTERVAL 7 DAY),
                    recordatorio_confirmacion_24_at = NULL,
                    recordatorio_confirmacion_48_at = NULL,
                    recordatorio_confirmacion_72_at = NULL,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $st->bindValue(':anterior', $estado, PDO::PARAM_STR);
            $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $st->execute();

            $this->registrarInteraccion($codigoSolicitud, $codigoProveedor, 'proveedor', 'servicio_marcado_realizado', 'El proveedor marcó el servicio como realizado.', ['estado_anterior' => $estado]);
            $this->notificarContraparte($solicitud, $codigoProveedor, 'servicio_realizado', 'Confirma el servicio realizado', 'El proveedor marcó como realizado el servicio “' . (string)$solicitud['titulo_servicio'] . '”. Confirma el resultado o reporta un problema.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'El servicio fue marcado como realizado. El comprador deberá confirmarlo o reportar un problema.', 'estado' => 'servicio_realizado_proveedor'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][marcarRealizado] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_MARCAR_SERVICIO_REALIZADO', 'mensaje' => 'No se pudo marcar el servicio como realizado.'];
        }
    }

    public function confirmarRealizado(int $codigoSolicitud, int $codigoComprador): array
    {
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoComprador, true);
            if (!$solicitud || $this->rolUsuario($solicitud, $codigoComprador) !== 'solicitante') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta coordinación.'];
            }
            if ((string)$solicitud['estado'] !== 'servicio_realizado_proveedor' || $this->obtenerIncidenciaActiva($codigoSolicitud, true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'El servicio no está disponible para confirmación.'];
            }

            $this->completarServicio($solicitud, $codigoComprador, 'solicitante', 'El comprador confirmó que el servicio fue realizado según lo coordinado.');
            $this->notificarContraparte($solicitud, $codigoComprador, 'servicio_confirmado', 'El servicio fue confirmado', 'El comprador confirmó el servicio “' . (string)$solicitud['titulo_servicio'] . '”. Ya puedes calificar la experiencia.');
            $this->notificarUsuario(
                $codigoComprador,
                $codigoSolicitud,
                'calificacion_habilitada',
                'Califica tu experiencia',
                'Ya puedes calificar al proveedor del servicio “' . (string)$solicitud['titulo_servicio'] . '”.',
                '/mis-solicitudes-servicio-comprador',
                ['rol_destino' => 'solicitante']
            );
            $this->notificarUsuario(
                (int)$solicitud['codigo_usuario_proveedor'],
                $codigoSolicitud,
                'calificacion_habilitada',
                'Califica tu experiencia',
                'Ya puedes calificar al comprador del servicio “' . (string)$solicitud['titulo_servicio'] . '”.',
                '/mis-solicitudes-servicio-vendedor',
                ['rol_destino' => 'proveedor']
            );
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'Servicio confirmado. La calificación quedó habilitada para comprador y proveedor.', 'estado' => 'servicio_confirmado_solicitante'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][confirmarRealizado] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_CONFIRMAR_SERVICIO', 'mensaje' => 'No se pudo confirmar el servicio.'];
        }
    }

    public function proponerReprogramacion(int $codigoSolicitud, int $codigoUsuario, array $data): array
    {
        $motivo = $this->texto($data['motivo'] ?? '', 500);
        $comentario = $this->texto($data['comentario'] ?? '', 1000);
        if (mb_strlen($motivo, 'UTF-8') < 5) {
            return ['ok' => false, 'error' => 'MOTIVO_REPROGRAMACION_REQUERIDO', 'mensaje' => 'Indica el motivo de la reprogramación.'];
        }

        try {
            $fecha = $this->normalizarFecha($data['fecha_nueva'] ?? '');
            $horaInicio = $this->normalizarHora($data['hora_inicio_nueva'] ?? '', true);
            $horaFin = $this->normalizarHora($data['hora_fin_nueva'] ?? '', false);
            $this->validarMomentoFuturo($fecha, (string)$horaInicio);
            if ($horaFin !== null && $horaFin <= $horaInicio) {
                return ['ok' => false, 'error' => 'HORA_FIN_ANTERIOR_INICIO', 'mensaje' => 'La hora de fin debe ser posterior a la hora de inicio.'];
            }
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
            $mensaje = match ($error) {
                'FECHA_REPROGRAMACION_INVALIDA' => 'Indica una fecha válida.',
                'HORA_REPROGRAMACION_INVALIDA' => 'Indica una hora válida.',
                'FECHA_REPROGRAMACION_PASADA' => 'La nueva fecha y hora deben ser futuras.',
                default => 'Revisa los datos de la reprogramación.',
            };
            return ['ok' => false, 'error' => $error, 'mensaje' => $mensaje];
        }

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario, true);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La coordinación no existe o no te pertenece.'];
            }
            $incidenciaActiva = $this->obtenerIncidenciaActiva($codigoSolicitud, true);
            $desdeIncidenciaOperativa = $this->incidenciaPermiteReprogramarOCancelar($incidenciaActiva);
            $puedeReprogramarNormal = (string)$solicitud['estado'] === 'coordinacion_confirmada' && $incidenciaActiva === null;
            if (!$puedeReprogramarNormal && !$desdeIncidenciaOperativa) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La reprogramación solo está disponible antes de iniciar el servicio o para resolver una inasistencia o incumplimiento de fecha.'];
            }
            if ($this->obtenerReprogramacionPendiente($codigoSolicitud, true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'REPROGRAMACION_PENDIENTE_EXISTENTE', 'mensaje' => 'Ya existe una propuesta de reprogramación pendiente de respuesta.'];
            }

            $rol = $this->rolUsuario($solicitud, $codigoUsuario);
            $st = $this->dblink->prepare("
                INSERT INTO solicitud_servicio_reprogramacion
                (
                    codigo_solicitud_servicio,
                    version_operativa_origen,
                    codigo_usuario_propone,
                    rol_propone,
                    fecha_anterior,
                    hora_inicio_anterior,
                    hora_fin_anterior,
                    fecha_nueva,
                    hora_inicio_nueva,
                    hora_fin_nueva,
                    motivo,
                    comentario,
                    estado
                )
                VALUES
                (:solicitud, :version, :usuario, :rol, :fecha_anterior, :hora_inicio_anterior, :hora_fin_anterior,
                 :fecha_nueva, :hora_inicio_nueva, :hora_fin_nueva, :motivo, :comentario, 'pendiente')
            ");
            $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $st->bindValue(':version', (int)($solicitud['version_operativa'] ?? 1), PDO::PARAM_INT);
            $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
            $st->bindValue(':rol', $rol, PDO::PARAM_STR);
            $fechaAnterior = $this->fechaOperativa($solicitud);
            $horaInicioAnterior = $this->horaInicioOperativa($solicitud);
            $horaFinAnterior = $this->horaFinOperativa($solicitud);
            $st->bindValue(':fecha_anterior', $fechaAnterior, $fechaAnterior !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':hora_inicio_anterior', $horaInicioAnterior, $horaInicioAnterior !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':hora_fin_anterior', $horaFinAnterior, $horaFinAnterior !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':fecha_nueva', $fecha, PDO::PARAM_STR);
            $st->bindValue(':hora_inicio_nueva', $horaInicio, PDO::PARAM_STR);
            $st->bindValue(':hora_fin_nueva', $horaFin, $horaFin !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':motivo', $motivo, PDO::PARAM_STR);
            $st->bindValue(':comentario', $comentario !== '' ? $comentario : null, $comentario !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->execute();
            $codigoReprogramacion = (int)$this->dblink->lastInsertId();

            $this->registrarInteraccion($codigoSolicitud, $codigoUsuario, $rol, 'reprogramacion_propuesta', $motivo, [
                'codigo_reprogramacion' => $codigoReprogramacion,
                'fecha_anterior' => $fechaAnterior,
                'hora_inicio_anterior' => $horaInicioAnterior,
                'fecha_nueva' => $fecha,
                'hora_inicio_nueva' => $horaInicio,
                'hora_fin_nueva' => $horaFin,
                'codigo_incidencia' => $incidenciaActiva ? (int)$incidenciaActiva['codigo_incidencia'] : null,
            ]);
            $this->notificarContraparte($solicitud, $codigoUsuario, 'reprogramacion_propuesta', 'Nueva propuesta de reprogramación', $this->etiquetaRol($rol) . ' propuso una nueva fecha para “' . (string)$solicitud['titulo_servicio'] . '”.');
            $this->dblink->commit();

            return ['ok' => true, 'mensaje' => 'La propuesta de reprogramación fue enviada.', 'data' => ['codigo_reprogramacion' => $codigoReprogramacion]];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][proponerReprogramacion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_PROPONER_REPROGRAMACION', 'mensaje' => 'No se pudo registrar la reprogramación.'];
        }
    }

    public function responderReprogramacion(int $codigoSolicitud, int $codigoReprogramacion, int $codigoUsuario, bool $aceptar, string $comentario = ''): array
    {
        $comentario = $this->texto($comentario, 500);
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario, true);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La coordinación no existe o no te pertenece.'];
            }

            $st = $this->dblink->prepare("
                SELECT * FROM solicitud_servicio_reprogramacion
                WHERE codigo_reprogramacion = :reprogramacion
                  AND codigo_solicitud_servicio = :solicitud
                LIMIT 1
                FOR UPDATE
            ");
            $st->bindValue(':reprogramacion', $codigoReprogramacion, PDO::PARAM_INT);
            $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $st->execute();
            $reprogramacion = $st->fetch(PDO::FETCH_ASSOC);

            if (!$reprogramacion) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'REPROGRAMACION_NO_ENCONTRADA', 'mensaje' => 'No se encontró la propuesta de reprogramación.'];
            }
            if ((string)$reprogramacion['estado'] !== 'pendiente') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'REPROGRAMACION_NO_DISPONIBLE', 'mensaje' => 'La propuesta ya fue respondida.'];
            }
            if ((int)$reprogramacion['codigo_usuario_propone'] === $codigoUsuario) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'NO_PUEDE_RESPONDER_PROPIA_PROPUESTA', 'mensaje' => 'La propuesta debe ser respondida por la otra parte.'];
            }
            $incidenciaActiva = $this->obtenerIncidenciaActiva($codigoSolicitud, true);
            $desdeIncidenciaOperativa = $this->incidenciaPermiteReprogramarOCancelar($incidenciaActiva);
            if ((string)$solicitud['estado'] !== 'coordinacion_confirmada' && !$desdeIncidenciaOperativa) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La coordinación cambió de estado y la propuesta ya no puede aplicarse.'];
            }
            if ((int)$reprogramacion['version_operativa_origen'] !== (int)($solicitud['version_operativa'] ?? 1)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'REPROGRAMACION_DESACTUALIZADA', 'mensaje' => 'La fecha vigente cambió. Actualiza la solicitud antes de responder.'];
            }

            $nuevoEstado = $aceptar ? 'aceptada' : 'rechazada';
            $up = $this->dblink->prepare("
                UPDATE solicitud_servicio_reprogramacion
                SET estado = :estado,
                    codigo_usuario_responde = :usuario,
                    respuesta_comentario = :comentario,
                    fecha_respuesta = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_reprogramacion = :reprogramacion
            ");
            $up->bindValue(':estado', $nuevoEstado, PDO::PARAM_STR);
            $up->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
            $up->bindValue(':comentario', $comentario !== '' ? $comentario : null, $comentario !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $up->bindValue(':reprogramacion', $codigoReprogramacion, PDO::PARAM_INT);
            $up->execute();

            if ($aceptar) {
                $upSolicitud = $this->dblink->prepare("
                    UPDATE solicitud_servicio
                    SET fecha_ejecucion_vigente = :fecha,
                        hora_inicio_vigente = :hora_inicio,
                        hora_fin_vigente = :hora_fin,
                        version_operativa = version_operativa + 1,
                        fecha_ultima_reprogramacion = NOW(),
                        estado = :estado,
                        estado_anterior = :estado_anterior,
                        motivo_estado = 'La reprogramación fue aceptada por ambas partes.',
                        updated_at = CURRENT_TIMESTAMP
                    WHERE codigo_solicitud_servicio = :solicitud
                ");
                $upSolicitud->bindValue(':fecha', (string)$reprogramacion['fecha_nueva'], PDO::PARAM_STR);
                $upSolicitud->bindValue(':hora_inicio', (string)$reprogramacion['hora_inicio_nueva'], PDO::PARAM_STR);
                $horaFin = $reprogramacion['hora_fin_nueva'] ?? null;
                $upSolicitud->bindValue(':hora_fin', $horaFin, $horaFin !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $upSolicitud->bindValue(':estado', 'coordinacion_confirmada', PDO::PARAM_STR);
                $upSolicitud->bindValue(':estado_anterior', (string)$solicitud['estado'], PDO::PARAM_STR);
                $upSolicitud->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
                $upSolicitud->execute();

                if ($desdeIncidenciaOperativa && $incidenciaActiva) {
                    $cerrarIncidencia = $this->dblink->prepare("
                        UPDATE solicitud_servicio_incidencia
                        SET estado = 'resuelta',
                            solucion = :solucion,
                            fecha_solucion = NOW(),
                            fecha_cierre = NOW(),
                            updated_at = CURRENT_TIMESTAMP
                        WHERE codigo_incidencia = :incidencia
                    ");
                    $cerrarIncidencia->execute([
                        ':solucion' => 'Las partes aceptaron una nueva fecha de ejecución mediante la reprogramación formal.',
                        ':incidencia' => (int)$incidenciaActiva['codigo_incidencia'],
                    ]);
                }
            }

            $rol = $this->rolUsuario($solicitud, $codigoUsuario);
            $tipo = $aceptar ? 'reprogramacion_aceptada' : 'reprogramacion_rechazada';
            $mensaje = $aceptar
                ? 'La nueva fecha de ejecución fue aceptada.'
                : 'La propuesta de reprogramación fue rechazada; se mantiene la fecha vigente.';
            $this->registrarInteraccion($codigoSolicitud, $codigoUsuario, $rol, $tipo, $mensaje, [
                'codigo_reprogramacion' => $codigoReprogramacion,
                'fecha_nueva' => $reprogramacion['fecha_nueva'],
                'hora_inicio_nueva' => $reprogramacion['hora_inicio_nueva'],
                'comentario' => $comentario,
            ]);
            $this->notificarContraparte(
                $solicitud,
                $codigoUsuario,
                $tipo,
                $aceptar ? 'Reprogramación aceptada' : 'Reprogramación rechazada',
                $mensaje . ' Servicio: “' . (string)$solicitud['titulo_servicio'] . '”.'
            );
            $this->dblink->commit();

            return ['ok' => true, 'mensaje' => $mensaje, 'estado' => $nuevoEstado];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][responderReprogramacion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_RESPONDER_REPROGRAMACION', 'mensaje' => 'No se pudo responder la reprogramación.'];
        }
    }

    public function cancelarReprogramacion(int $codigoSolicitud, int $codigoReprogramacion, int $codigoUsuario): array
    {
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario, true);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La coordinación no existe o no te pertenece.'];
            }
            $st = $this->dblink->prepare("
                SELECT * FROM solicitud_servicio_reprogramacion
                WHERE codigo_reprogramacion = :reprogramacion
                  AND codigo_solicitud_servicio = :solicitud
                LIMIT 1 FOR UPDATE
            ");
            $st->execute([':reprogramacion' => $codigoReprogramacion, ':solicitud' => $codigoSolicitud]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row || (string)$row['estado'] !== 'pendiente' || (int)$row['codigo_usuario_propone'] !== $codigoUsuario) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'REPROGRAMACION_NO_DISPONIBLE', 'mensaje' => 'La propuesta no está disponible para cancelación.'];
            }
            $up = $this->dblink->prepare("UPDATE solicitud_servicio_reprogramacion SET estado = 'cancelada', fecha_respuesta = NOW(), updated_at = CURRENT_TIMESTAMP WHERE codigo_reprogramacion = :id");
            $up->bindValue(':id', $codigoReprogramacion, PDO::PARAM_INT);
            $up->execute();
            $rol = $this->rolUsuario($solicitud, $codigoUsuario);
            $this->registrarInteraccion($codigoSolicitud, $codigoUsuario, $rol, 'reprogramacion_cancelada', 'La propuesta de reprogramación fue retirada.', ['codigo_reprogramacion' => $codigoReprogramacion]);
            $this->notificarContraparte($solicitud, $codigoUsuario, 'reprogramacion_cancelada', 'Reprogramación retirada', 'La propuesta de reprogramación del servicio “' . (string)$solicitud['titulo_servicio'] . '” fue retirada.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La propuesta de reprogramación fue retirada.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][cancelarReprogramacion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_CANCELAR_REPROGRAMACION', 'mensaje' => 'No se pudo retirar la reprogramación.'];
        }
    }

    public function cancelarServicio(int $codigoSolicitud, int $codigoUsuario, string $motivo): array
    {
        $motivo = $this->texto($motivo, 500);
        if (mb_strlen($motivo, 'UTF-8') < 5) {
            return ['ok' => false, 'error' => 'MOTIVO_CANCELACION_REQUERIDO', 'mensaje' => 'Indica un motivo claro para cancelar la coordinación.'];
        }

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario, true);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La coordinación no existe o no te pertenece.'];
            }
            $incidenciaActiva = $this->obtenerIncidenciaActiva($codigoSolicitud, true);
            $desdeIncidenciaOperativa = $this->incidenciaPermiteReprogramarOCancelar($incidenciaActiva);
            $puedeCancelarNormal = (string)$solicitud['estado'] === 'coordinacion_confirmada' && $incidenciaActiva === null;
            if (!$puedeCancelarNormal && !$desdeIncidenciaOperativa) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'Después de iniciar el servicio, cualquier inconveniente debe registrarse y resolverse como problema.'];
            }

            $rol = $this->rolUsuario($solicitud, $codigoUsuario);
            $estado = $rol === 'proveedor' ? 'cancelada_proveedor' : 'cancelada_solicitante';
            $estadoPropuesta = $rol === 'proveedor' ? 'cancelada_proveedor' : 'cancelada_solicitante';

            $upP = $this->dblink->prepare("UPDATE solicitud_servicio_propuesta SET estado = :estado, motivo_estado = :motivo, updated_at = CURRENT_TIMESTAMP WHERE codigo_solicitud_servicio = :solicitud AND estado = 'aceptada'");
            $upP->execute([':estado' => $estadoPropuesta, ':motivo' => $motivo, ':solicitud' => $codigoSolicitud]);

            $upS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = :estado,
                    estado_anterior = :estado_anterior,
                    motivo_estado = :motivo,
                    fecha_cancelacion = NOW(),
                    fecha_cierre = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $upS->execute([
                ':estado' => $estado,
                ':estado_anterior' => (string)$solicitud['estado'],
                ':motivo' => $motivo,
                ':solicitud' => $codigoSolicitud,
            ]);

            if ($desdeIncidenciaOperativa && $incidenciaActiva) {
                $cerrarIncidencia = $this->dblink->prepare("
                    UPDATE solicitud_servicio_incidencia
                    SET estado = 'cancelada',
                        solucion = :solucion,
                        fecha_solucion = NOW(),
                        fecha_cierre = NOW(),
                        updated_at = CURRENT_TIMESTAMP
                    WHERE codigo_incidencia = :incidencia
                ");
                $cerrarIncidencia->execute([
                    ':solucion' => 'La coordinación fue cancelada después del reporte de inasistencia o incumplimiento de fecha. Motivo: ' . $motivo,
                    ':incidencia' => (int)$incidenciaActiva['codigo_incidencia'],
                ]);
            }

            $cancelRep = $this->dblink->prepare("UPDATE solicitud_servicio_reprogramacion SET estado = 'cancelada', fecha_respuesta = NOW(), updated_at = CURRENT_TIMESTAMP WHERE codigo_solicitud_servicio = :solicitud AND estado = 'pendiente'");
            $cancelRep->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $cancelRep->execute();

            $this->registrarInteraccion($codigoSolicitud, $codigoUsuario, $rol, 'servicio_cancelado', $motivo, ['estado' => $estado]);
            $this->notificarContraparte($solicitud, $codigoUsuario, 'servicio_cancelado', 'Coordinación cancelada', $this->etiquetaRol($rol) . ' canceló la coordinación del servicio “' . (string)$solicitud['titulo_servicio'] . '”.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La coordinación fue cancelada y la otra parte fue notificada.', 'estado' => $estado];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][cancelarServicio] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_CANCELAR_SERVICIO', 'mensaje' => 'No se pudo cancelar la coordinación.'];
        }
    }

    public function reportarProblema(int $codigoSolicitud, int $codigoUsuario, array $data, array $files = []): array
    {
        $categoria = strtolower(trim((string)($data['categoria'] ?? '')));
        $descripcion = $this->texto($data['descripcion'] ?? $data['mensaje'] ?? '', 3000);
        if (!isset(self::CATEGORIAS_INCIDENCIA[$categoria])) {
            return ['ok' => false, 'error' => 'CATEGORIA_INCIDENCIA_INVALIDA', 'mensaje' => 'Selecciona una categoría válida.'];
        }
        if (mb_strlen($descripcion, 'UTF-8') < 10) {
            return ['ok' => false, 'error' => 'DESCRIPCION_INCIDENCIA_REQUERIDA', 'mensaje' => 'Describe el problema con al menos 10 caracteres.'];
        }

        $adjuntosGuardados = [];

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario, true);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La coordinación no existe o no te pertenece.'];
            }
            $estado = (string)$solicitud['estado'];
            if (!in_array($estado, ['coordinacion_confirmada', 'servicio_en_ejecucion', 'servicio_realizado_proveedor'], true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'No se puede registrar un problema desde el estado actual.'];
            }
            if ($this->obtenerIncidenciaActiva($codigoSolicitud, true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'INCIDENCIA_ACTIVA_EXISTENTE', 'mensaje' => 'Ya existe un problema abierto para este servicio.'];
            }

            $stNumero = $this->dblink->prepare('SELECT COALESCE(MAX(numero_incidencia), 0) + 1 FROM solicitud_servicio_incidencia WHERE codigo_solicitud_servicio = :solicitud');
            $stNumero->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $stNumero->execute();
            $numero = (int)$stNumero->fetchColumn();
            $rol = $this->rolUsuario($solicitud, $codigoUsuario);

            $ins = $this->dblink->prepare("
                INSERT INTO solicitud_servicio_incidencia
                (codigo_solicitud_servicio, numero_incidencia, codigo_usuario_reporta, rol_reporta, categoria, descripcion, estado_solicitud_origen, estado)
                VALUES (:solicitud, :numero, :usuario, :rol, :categoria, :descripcion, :estado_origen, 'abierta')
            ");
            $ins->execute([
                ':solicitud' => $codigoSolicitud,
                ':numero' => $numero,
                ':usuario' => $codigoUsuario,
                ':rol' => $rol,
                ':categoria' => $categoria,
                ':descripcion' => $descripcion,
                ':estado_origen' => $estado,
            ]);
            $codigoIncidencia = (int)$this->dblink->lastInsertId();
            $adjuntos = $this->guardarAdjuntosIncidencia($codigoIncidencia, $codigoUsuario, 'reporte', $files);
            $adjuntosGuardados = $adjuntos;

            $up = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'incidencia_abierta',
                    estado_anterior = :anterior,
                    motivo_estado = :motivo,
                    fecha_observacion = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $up->execute([
                ':anterior' => $estado,
                ':motivo' => self::CATEGORIAS_INCIDENCIA[$categoria] . ': ' . mb_substr($descripcion, 0, 420, 'UTF-8'),
                ':solicitud' => $codigoSolicitud,
            ]);

            $cancelRep = $this->dblink->prepare("UPDATE solicitud_servicio_reprogramacion SET estado = 'cancelada', fecha_respuesta = NOW(), updated_at = CURRENT_TIMESTAMP WHERE codigo_solicitud_servicio = :solicitud AND estado = 'pendiente'");
            $cancelRep->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $cancelRep->execute();

            $this->registrarInteraccion($codigoSolicitud, $codigoUsuario, $rol, 'incidencia_reportada', $descripcion, [
                'codigo_incidencia' => $codigoIncidencia,
                'categoria' => $categoria,
                'categoria_texto' => self::CATEGORIAS_INCIDENCIA[$categoria],
                'adjuntos' => $adjuntos,
                'estado_solicitud_origen' => $estado,
            ]);
            $this->notificarContraparte($solicitud, $codigoUsuario, 'problema_reportado', 'Se reportó un problema en el servicio', $this->etiquetaRol($rol) . ' reportó un problema en “' . (string)$solicitud['titulo_servicio'] . '”. Revisa el detalle dentro de EV.');
            $this->dblink->commit();

            return ['ok' => true, 'mensaje' => 'El problema fue registrado y la otra parte fue notificada.', 'estado' => 'incidencia_abierta', 'data' => ['codigo_incidencia' => $codigoIncidencia]];
        } catch (InvalidArgumentException $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            $this->eliminarAdjuntosFisicos($adjuntosGuardados);
            $error = $e->getMessage();
            $mensaje = match ($error) {
                'MAX_ADJUNTOS_EXCEDIDO' => 'Puedes adjuntar como máximo 5 archivos.',
                'ADJUNTO_PESO_INVALIDO' => 'Cada archivo debe pesar como máximo 8 MB.',
                'ADJUNTO_FORMATO_INVALIDO' => 'Solo se permiten archivos JPG, PNG, WEBP o PDF.',
                default => 'Uno de los archivos adjuntos no es válido.',
            };
            return ['ok' => false, 'error' => $error, 'mensaje' => $mensaje];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            $this->eliminarAdjuntosFisicos($adjuntosGuardados);
            error_log('[EV][ServicioEjecucion][reportarProblema] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_REPORTAR_PROBLEMA', 'mensaje' => 'No se pudo registrar el problema.'];
        }
    }

    public function responderIncidencia(int $codigoSolicitud, int $codigoUsuario, string $respuesta, array $files = []): array
    {
        $respuesta = $this->texto($respuesta, 3000);
        if (mb_strlen($respuesta, 'UTF-8') < 8) {
            return ['ok' => false, 'error' => 'RESPUESTA_INCIDENCIA_REQUERIDA', 'mensaje' => 'Escribe una respuesta de al menos 8 caracteres.'];
        }

        $adjuntosGuardados = [];

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario, true);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La coordinación no existe o no te pertenece.'];
            }
            $incidencia = $this->obtenerIncidenciaActiva($codigoSolicitud, true);
            if (!$incidencia) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'INCIDENCIA_NO_ENCONTRADA', 'mensaje' => 'No existe un problema activo.'];
            }
            $respuestaSolicitadaPorSoporte = (string)$incidencia['estado'] === 'esperando_informacion'
                && (int)($incidencia['requiere_soporte'] ?? 0) === 1;

            if ((int)$incidencia['codigo_usuario_reporta'] === $codigoUsuario && !$respuestaSolicitadaPorSoporte) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'RESPUESTA_NO_PERMITIDA', 'mensaje' => 'La respuesta debe ser registrada por la otra parte.'];
            }
            if (!in_array((string)$incidencia['estado'], ['abierta', 'persiste', 'esperando_informacion'], true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La incidencia no admite una respuesta desde su estado actual.'];
            }

            $adjuntos = $this->guardarAdjuntosIncidencia((int)$incidencia['codigo_incidencia'], $codigoUsuario, 'respuesta', $files);
            $adjuntosGuardados = $adjuntos;
            $nuevoEstadoIncidencia = $respuestaSolicitadaPorSoporte ? 'revision_soporte' : 'en_atencion';
            $nuevoEstadoSolicitud = $respuestaSolicitadaPorSoporte ? 'revision_soporte' : 'incidencia_en_atencion';
            $motivoSolicitud = $respuestaSolicitadaPorSoporte
                ? 'Se envió información adicional solicitada por soporte.'
                : 'La incidencia recibió una respuesta y se encuentra en atención.';

            $up = $this->dblink->prepare("
                UPDATE solicitud_servicio_incidencia
                SET estado = :estado,
                    codigo_usuario_responde = :usuario,
                    respuesta = :respuesta,
                    fecha_respuesta = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_incidencia = :incidencia
            ");
            $up->execute([
                ':estado' => $nuevoEstadoIncidencia,
                ':usuario' => $codigoUsuario,
                ':respuesta' => $respuesta,
                ':incidencia' => (int)$incidencia['codigo_incidencia'],
            ]);
            $upS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = :estado,
                    estado_anterior = :anterior,
                    motivo_estado = :motivo,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $upS->execute([
                ':estado' => $nuevoEstadoSolicitud,
                ':anterior' => (string)$solicitud['estado'],
                ':motivo' => $motivoSolicitud,
                ':solicitud' => $codigoSolicitud,
            ]);
            $rol = $this->rolUsuario($solicitud, $codigoUsuario);
            $tipoInteraccion = $respuestaSolicitadaPorSoporte ? 'informacion_soporte_enviada' : 'incidencia_respondida';
            $this->registrarInteraccion($codigoSolicitud, $codigoUsuario, $rol, $tipoInteraccion, $respuesta, ['codigo_incidencia' => (int)$incidencia['codigo_incidencia'], 'adjuntos' => $adjuntos]);
            $this->notificarContraparte(
                $solicitud,
                $codigoUsuario,
                $respuestaSolicitadaPorSoporte ? 'informacion_soporte_enviada' : 'respuesta_incidencia',
                $respuestaSolicitadaPorSoporte ? 'Información enviada a soporte' : 'Nueva respuesta sobre el problema',
                $respuestaSolicitadaPorSoporte
                    ? 'Se envió información adicional para la revisión del problema en “' . (string)$solicitud['titulo_servicio'] . '”.'
                    : 'Hay una nueva respuesta sobre el problema reportado en “' . (string)$solicitud['titulo_servicio'] . '”.'
            );
            $this->dblink->commit();
            return [
                'ok' => true,
                'mensaje' => $respuestaSolicitadaPorSoporte ? 'La información fue enviada y el caso volvió a revisión de soporte.' : 'La respuesta fue registrada.',
                'estado' => $nuevoEstadoSolicitud,
            ];
        } catch (InvalidArgumentException $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            $this->eliminarAdjuntosFisicos($adjuntosGuardados);
            $error = $e->getMessage();
            return ['ok' => false, 'error' => $error, 'mensaje' => $error === 'MAX_ADJUNTOS_EXCEDIDO' ? 'Puedes adjuntar como máximo 5 archivos.' : 'Revisa los archivos adjuntos.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            $this->eliminarAdjuntosFisicos($adjuntosGuardados);
            error_log('[EV][ServicioEjecucion][responderIncidencia] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_RESPONDER_INCIDENCIA', 'mensaje' => 'No se pudo registrar la respuesta.'];
        }
    }

    public function registrarSolucion(int $codigoSolicitud, int $codigoProveedor, string $solucion, array $files = []): array
    {
        $solucion = $this->texto($solucion, 3000);
        if (mb_strlen($solucion, 'UTF-8') < 8) {
            return ['ok' => false, 'error' => 'SOLUCION_REQUERIDA', 'mensaje' => 'Describe la solución aplicada con al menos 8 caracteres.'];
        }

        $adjuntosGuardados = [];

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoProveedor, true);
            if (!$solicitud || $this->rolUsuario($solicitud, $codigoProveedor) !== 'proveedor') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta coordinación.'];
            }
            $incidencia = $this->obtenerIncidenciaActiva($codigoSolicitud, true);
            if (
                !$incidencia
                || (int)($incidencia['requiere_soporte'] ?? 0) === 1
                || !in_array((string)$incidencia['estado'], ['abierta', 'en_atencion', 'persiste'], true)
            ) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La incidencia no está disponible para registrar una solución.'];
            }

            $adjuntos = $this->guardarAdjuntosIncidencia((int)$incidencia['codigo_incidencia'], $codigoProveedor, 'solucion', $files);
            $adjuntosGuardados = $adjuntos;
            $up = $this->dblink->prepare("
                UPDATE solicitud_servicio_incidencia
                SET estado = 'solucion_pendiente_confirmacion',
                    codigo_usuario_solucion = :usuario,
                    solucion = :solucion,
                    fecha_solucion = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_incidencia = :incidencia
            ");
            $up->execute([':usuario' => $codigoProveedor, ':solucion' => $solucion, ':incidencia' => (int)$incidencia['codigo_incidencia']]);
            $upS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'solucion_pendiente_confirmacion',
                    estado_anterior = :anterior,
                    motivo_estado = 'El proveedor registró una solución pendiente de confirmación del comprador.',
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $upS->execute([':anterior' => (string)$solicitud['estado'], ':solicitud' => $codigoSolicitud]);
            $this->registrarInteraccion($codigoSolicitud, $codigoProveedor, 'proveedor', 'solucion_registrada', $solucion, ['codigo_incidencia' => (int)$incidencia['codigo_incidencia'], 'adjuntos' => $adjuntos]);
            $this->notificarContraparte($solicitud, $codigoProveedor, 'solucion_registrada', 'Solución pendiente de confirmación', 'El proveedor registró una solución para el problema de “' . (string)$solicitud['titulo_servicio'] . '”. Revísala y confirma si quedó resuelto.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La solución fue registrada y enviada al comprador.', 'estado' => 'solucion_pendiente_confirmacion'];
        } catch (InvalidArgumentException $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            $this->eliminarAdjuntosFisicos($adjuntosGuardados);
            return ['ok' => false, 'error' => $e->getMessage(), 'mensaje' => 'Revisa los archivos adjuntos.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            $this->eliminarAdjuntosFisicos($adjuntosGuardados);
            error_log('[EV][ServicioEjecucion][registrarSolucion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_REGISTRAR_SOLUCION', 'mensaje' => 'No se pudo registrar la solución.'];
        }
    }

    public function confirmarSolucion(int $codigoSolicitud, int $codigoComprador): array
    {
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoComprador, true);
            if (!$solicitud || $this->rolUsuario($solicitud, $codigoComprador) !== 'solicitante') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta coordinación.'];
            }
            $incidencia = $this->obtenerIncidenciaActiva($codigoSolicitud, true);
            if (!$incidencia || (string)$incidencia['estado'] !== 'solucion_pendiente_confirmacion') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'No existe una solución pendiente de confirmación.'];
            }

            $up = $this->dblink->prepare("
                UPDATE solicitud_servicio_incidencia
                SET estado = 'resuelta',
                    fecha_cierre = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_incidencia = :incidencia
            ");
            $up->bindValue(':incidencia', (int)$incidencia['codigo_incidencia'], PDO::PARAM_INT);
            $up->execute();

            $this->completarServicio($solicitud, $codigoComprador, 'solicitante', 'El comprador confirmó la solución y el servicio quedó completado.');
            $this->notificarContraparte($solicitud, $codigoComprador, 'solucion_confirmada', 'Problema resuelto', 'El comprador confirmó la solución del servicio “' . (string)$solicitud['titulo_servicio'] . '”. La calificación quedó habilitada.');
            $this->notificarUsuario($codigoComprador, $codigoSolicitud, 'calificacion_habilitada', 'Califica tu experiencia', 'Ya puedes calificar al proveedor.', '/mis-solicitudes-servicio-comprador', ['rol_destino' => 'solicitante']);
            $this->notificarUsuario((int)$solicitud['codigo_usuario_proveedor'], $codigoSolicitud, 'calificacion_habilitada', 'Califica tu experiencia', 'Ya puedes calificar al comprador.', '/mis-solicitudes-servicio-vendedor', ['rol_destino' => 'proveedor']);
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La solución fue confirmada y el servicio quedó completado.', 'estado' => 'servicio_confirmado_solicitante'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][confirmarSolucion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_CONFIRMAR_SOLUCION', 'mensaje' => 'No se pudo confirmar la solución.'];
        }
    }

    public function problemaPersiste(int $codigoSolicitud, int $codigoComprador, string $detalle): array
    {
        $detalle = $this->texto($detalle, 2000);
        if (mb_strlen($detalle, 'UTF-8') < 8) {
            return ['ok' => false, 'error' => 'DETALLE_REQUERIDO', 'mensaje' => 'Indica por qué el problema continúa.'];
        }

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoComprador, true);
            if (!$solicitud || $this->rolUsuario($solicitud, $codigoComprador) !== 'solicitante') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta coordinación.'];
            }
            $incidencia = $this->obtenerIncidenciaActiva($codigoSolicitud, true);
            if (!$incidencia || (string)$incidencia['estado'] !== 'solucion_pendiente_confirmacion') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'No existe una solución pendiente de evaluación.'];
            }

            $up = $this->dblink->prepare("UPDATE solicitud_servicio_incidencia SET estado = 'persiste', updated_at = CURRENT_TIMESTAMP WHERE codigo_incidencia = :incidencia");
            $up->bindValue(':incidencia', (int)$incidencia['codigo_incidencia'], PDO::PARAM_INT);
            $up->execute();
            $upS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'incidencia_en_atencion',
                    estado_anterior = 'solucion_pendiente_confirmacion',
                    motivo_estado = :motivo,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $upS->execute([':motivo' => 'El comprador indicó que el problema continúa: ' . mb_substr($detalle, 0, 420, 'UTF-8'), ':solicitud' => $codigoSolicitud]);
            $this->registrarInteraccion($codigoSolicitud, $codigoComprador, 'solicitante', 'problema_persiste', $detalle, ['codigo_incidencia' => (int)$incidencia['codigo_incidencia']]);
            $this->notificarContraparte($solicitud, $codigoComprador, 'problema_persiste', 'El problema continúa', 'El comprador indicó que el problema del servicio “' . (string)$solicitud['titulo_servicio'] . '” todavía no fue resuelto.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'Se registró que el problema continúa.', 'estado' => 'incidencia_en_atencion'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][problemaPersiste] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_PROBLEMA_PERSISTE', 'mensaje' => 'No se pudo actualizar la incidencia.'];
        }
    }

    public function solicitarSoporte(int $codigoSolicitud, int $codigoUsuario, string $motivo): array
    {
        $motivo = $this->texto($motivo, 2000);
        if (mb_strlen($motivo, 'UTF-8') < 8) {
            return ['ok' => false, 'error' => 'MOTIVO_SOPORTE_REQUERIDO', 'mensaje' => 'Explica por qué necesitas la intervención de soporte.'];
        }

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario, true);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La coordinación no existe o no te pertenece.'];
            }
            $incidencia = $this->obtenerIncidenciaActiva($codigoSolicitud, true);
            if (!$incidencia) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'INCIDENCIA_NO_ENCONTRADA', 'mensaje' => 'Primero debe existir un problema registrado.'];
            }
            if ((int)$incidencia['requiere_soporte'] === 1) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOPORTE_YA_SOLICITADO', 'mensaje' => 'La incidencia ya fue enviada a soporte.'];
            }

            $up = $this->dblink->prepare("
                UPDATE solicitud_servicio_incidencia
                SET requiere_soporte = 1,
                    estado = 'revision_soporte',
                    fecha_escalamiento_soporte = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_incidencia = :incidencia
            ");
            $up->bindValue(':incidencia', (int)$incidencia['codigo_incidencia'], PDO::PARAM_INT);
            $up->execute();
            $upS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'revision_soporte',
                    estado_anterior = :anterior,
                    motivo_estado = :motivo,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $upS->execute([':anterior' => (string)$solicitud['estado'], ':motivo' => 'Se solicitó la revisión de soporte: ' . mb_substr($motivo, 0, 420, 'UTF-8'), ':solicitud' => $codigoSolicitud]);
            $rol = $this->rolUsuario($solicitud, $codigoUsuario);
            $this->registrarInteraccion($codigoSolicitud, $codigoUsuario, $rol, 'revision_soporte_solicitada', $motivo, ['codigo_incidencia' => (int)$incidencia['codigo_incidencia']]);
            $this->notificarContraparte($solicitud, $codigoUsuario, 'revision_soporte_solicitada', 'El caso fue enviado a soporte', 'La incidencia del servicio “' . (string)$solicitud['titulo_servicio'] . '” fue enviada a soporte para revisión.');
            $this->notificarSoporte($solicitud, (int)$incidencia['codigo_incidencia']);
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La incidencia fue enviada a soporte.', 'estado' => 'revision_soporte'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][solicitarSoporte] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_SOLICITAR_SOPORTE', 'mensaje' => 'No se pudo enviar el caso a soporte.'];
        }
    }

    public function sincronizarRecordatoriosUsuario(int $codigoUsuario): void
    {
        if ($codigoUsuario <= 0) {
            return;
        }
        try {
            $st = $this->dblink->prepare("
                SELECT codigo_solicitud_servicio
                FROM solicitud_servicio
                WHERE estado = 'servicio_realizado_proveedor'
                  AND (codigo_usuario_solicitante = :usuario1 OR codigo_usuario_proveedor = :usuario2)
            ");
            $st->bindValue(':usuario1', $codigoUsuario, PDO::PARAM_INT);
            $st->bindValue(':usuario2', $codigoUsuario, PDO::PARAM_INT);
            $st->execute();
            foreach ($st->fetchAll(PDO::FETCH_COLUMN) ?: [] as $codigoSolicitud) {
                $this->sincronizarRecordatoriosSolicitud((int)$codigoSolicitud);
            }
        } catch (Throwable $e) {
            error_log('[EV][ServicioEjecucion][sincronizarRecordatoriosUsuario] ' . $e->getMessage());
        }
    }

    public function sincronizarRecordatoriosSolicitud(int $codigoSolicitud): void
    {
        if ($codigoSolicitud <= 0) {
            return;
        }

        try {
            $this->dblink->beginTransaction();
            $st = $this->dblink->prepare("
                SELECT ss.*, p.titulo AS titulo_servicio
                FROM solicitud_servicio ss
                INNER JOIN producto p ON p.codigo_producto = ss.codigo_producto
                WHERE ss.codigo_solicitud_servicio = :solicitud
                LIMIT 1 FOR UPDATE
            ");
            $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $st->execute();
            $solicitud = $st->fetch(PDO::FETCH_ASSOC);
            if (!$solicitud || (string)$solicitud['estado'] !== 'servicio_realizado_proveedor' || empty($solicitud['fecha_realizado_proveedor'])) {
                $this->dblink->rollBack();
                return;
            }

            $realizado = new DateTimeImmutable((string)$solicitud['fecha_realizado_proveedor'], new DateTimeZone('America/Lima'));
            $ahora = new DateTimeImmutable('now', new DateTimeZone('America/Lima'));
            $horas = ($ahora->getTimestamp() - $realizado->getTimestamp()) / 3600;
            $campo = null;
            $subcategoria = null;
            $titulo = null;
            $mensaje = null;

            if ($horas >= 72 && empty($solicitud['recordatorio_confirmacion_72_at'])) {
                $campo = 'recordatorio_confirmacion_72_at';
                $subcategoria = 'confirmacion_pendiente_72h';
                $titulo = 'Confirmación de servicio pendiente';
                $mensaje = 'Han transcurrido 72 horas. Confirma el servicio o reporta un problema; EV no cerrará el caso automáticamente.';
            } elseif ($horas >= 48 && empty($solicitud['recordatorio_confirmacion_48_at'])) {
                $campo = 'recordatorio_confirmacion_48_at';
                $subcategoria = 'confirmacion_pendiente_48h';
                $titulo = 'Recuerda confirmar el servicio';
                $mensaje = 'El servicio sigue pendiente de tu confirmación o del reporte de un problema.';
            } elseif ($horas >= 24 && empty($solicitud['recordatorio_confirmacion_24_at'])) {
                $campo = 'recordatorio_confirmacion_24_at';
                $subcategoria = 'confirmacion_pendiente_24h';
                $titulo = 'Confirma el servicio realizado';
                $mensaje = 'Revisa el servicio y confirma si se realizó según lo acordado o reporta un problema.';
            }

            if ($campo !== null) {
                // Si el usuario vuelve después de varias horas, se envía solo el recordatorio más reciente
                // y se marcan como cubiertos los hitos anteriores para evitar avisos fuera de orden.
                $setRecordatorios = match ($campo) {
                    'recordatorio_confirmacion_72_at' => "recordatorio_confirmacion_24_at = COALESCE(recordatorio_confirmacion_24_at, NOW()), recordatorio_confirmacion_48_at = COALESCE(recordatorio_confirmacion_48_at, NOW()), recordatorio_confirmacion_72_at = NOW()",
                    'recordatorio_confirmacion_48_at' => "recordatorio_confirmacion_24_at = COALESCE(recordatorio_confirmacion_24_at, NOW()), recordatorio_confirmacion_48_at = NOW()",
                    default => "recordatorio_confirmacion_24_at = NOW()",
                };
                $up = $this->dblink->prepare("UPDATE solicitud_servicio SET {$setRecordatorios}, updated_at = CURRENT_TIMESTAMP WHERE codigo_solicitud_servicio = :solicitud");
                $up->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
                $up->execute();
                $this->notificarUsuario(
                    (int)$solicitud['codigo_usuario_solicitante'],
                    $codigoSolicitud,
                    (string)$subcategoria,
                    (string)$titulo,
                    (string)$mensaje . ' Servicio: “' . (string)$solicitud['titulo_servicio'] . '”.',
                    '/mis-solicitudes-servicio-comprador',
                    ['rol_destino' => 'solicitante']
                );
                $this->registrarInteraccion($codigoSolicitud, (int)$solicitud['codigo_usuario_solicitante'], 'sistema', (string)$subcategoria, (string)$mensaje);
            }

            if (!empty($solicitud['fecha_revision_soporte_sugerida']) && $ahora >= new DateTimeImmutable((string)$solicitud['fecha_revision_soporte_sugerida'])) {
                $check = $this->dblink->prepare("SELECT COUNT(*) FROM solicitud_servicio_interaccion WHERE codigo_solicitud_servicio = :solicitud AND tipo_interaccion = 'revision_soporte_sugerida'");
                $check->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
                $check->execute();
                if ((int)$check->fetchColumn() === 0) {
                    $this->registrarInteraccion($codigoSolicitud, (int)$solicitud['codigo_usuario_solicitante'], 'sistema', 'revision_soporte_sugerida', 'La confirmación continúa pendiente después de 7 días. El caso puede requerir revisión de soporte.');
                    $this->notificarUsuario((int)$solicitud['codigo_usuario_solicitante'], $codigoSolicitud, 'revision_soporte_sugerida', 'Confirmación pendiente por más de 7 días', 'Revisa el servicio y confirma el resultado o registra un problema para solicitar soporte.', '/mis-solicitudes-servicio-comprador', ['rol_destino' => 'solicitante']);
                }
            }

            $this->dblink->commit();
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }
            error_log('[EV][ServicioEjecucion][sincronizarRecordatoriosSolicitud] ' . $e->getMessage());
        }
    }
}
