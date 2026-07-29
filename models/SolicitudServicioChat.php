<?php
// models/SolicitudServicioChat.php
// Punto 10 EV: conversación privada, adjuntos y privacidad de ubicación.
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/Notificacion.php';

class SolicitudServicioChat extends Conexion
{
    public const MAX_ADJUNTOS = 5;
    public const MAX_BYTES_ADJUNTO = 5242880; // 5 MB por imagen.
    public const MAX_MENSAJE = 1500;

    private function texto($valor, int $maximo): string
    {
        $texto = trim((string)$valor);
        return $texto === '' ? '' : mb_substr($texto, 0, $maximo, 'UTF-8');
    }

    private function jsonSeguro($valor): array
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

    private function modalidadPermitida($valor): string
    {
        $valor = strtolower(trim((string)$valor));
        $permitidos = [
            'a_coordinar',
            'domicilio_solicitante',
            'domicilio_proveedor',
            'punto_encuentro',
            'remoto',
            'recojo_entrega',
        ];

        return in_array($valor, $permitidos, true) ? $valor : 'a_coordinar';
    }

    private function etiquetaModalidad(string $modalidad): string
    {
        return match ($modalidad) {
            'domicilio_solicitante' => 'Domicilio del solicitante',
            'domicilio_proveedor' => 'Domicilio o local del proveedor',
            'punto_encuentro' => 'Punto de encuentro',
            'remoto' => 'Atención remota o digital',
            'recojo_entrega' => 'Recojo o entrega',
            default => 'A coordinar',
        };
    }

    private function esEstadoCerrado(string $estado): bool
    {
        return in_array($estado, [
            'rechazada_proveedor',
            'cotizacion_rechazada_solicitante',
            'cancelada_solicitante',
            'cancelada_proveedor',
            'cancelada_soporte',
            'sin_respuesta_proveedor',
            'servicio_confirmado_solicitante',
        ], true);
    }

    private function puedeVerDireccionExacta(array $solicitud): bool
    {
        return in_array((string)($solicitud['estado'] ?? ''), [
            'coordinacion_confirmada',
            'servicio_en_ejecucion',
            'servicio_realizado_proveedor',
            'incidencia_abierta',
            'incidencia_en_atencion',
            'solucion_pendiente_confirmacion',
            'revision_soporte',
        ], true)
        && trim((string)($solicitud['direccion_atencion'] ?? '')) !== '';
    }

    private function calcularDuracionCotizacion(?string $horaInicio, ?string $horaFin): string
    {
        $inicio = trim((string)$horaInicio);
        $fin = trim((string)$horaFin);

        if ($inicio === '' || $fin === '') {
            return '';
        }

        $inicio = substr($inicio, 0, 5);
        $fin = substr($fin, 0, 5);

        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $inicio) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $fin)) {
            return '';
        }

        [$h1, $m1] = array_map('intval', explode(':', $inicio));
        [$h2, $m2] = array_map('intval', explode(':', $fin));
        $minutos = ($h2 * 60 + $m2) - ($h1 * 60 + $m1);

        if ($minutos <= 0) {
            return '';
        }

        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        if ($horas > 0 && $resto > 0) {
            return $horas . ' ' . ($horas === 1 ? 'hora' : 'horas') . ' ' . $resto . ' min';
        }

        if ($horas > 0) {
            return $horas . ' ' . ($horas === 1 ? 'hora' : 'horas');
        }

        return $resto . ' min';
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
                c.nombre AS categoria_nombre,
                cs.nombre AS categoria_grupo_nombre,
                us.nombre AS nombre_solicitante,
                up.nombre AS nombre_proveedor
            FROM solicitud_servicio ss
            INNER JOIN producto p ON p.codigo_producto = ss.codigo_producto
            INNER JOIN usuario us ON us.codigo_usuario = ss.codigo_usuario_solicitante
            INNER JOIN usuario up ON up.codigo_usuario = ss.codigo_usuario_proveedor
            LEFT JOIN categoria c ON c.codigo_categoria = p.codigo_categoria
            LEFT JOIN categoria_grupo cs ON cs.codigo_grupo = c.codigo_grupo
            WHERE ss.codigo_solicitud_servicio = :codigo_solicitud
              AND (
                    :codigo_usuario_solicitante = ss.codigo_usuario_solicitante
                    OR :codigo_usuario_proveedor = ss.codigo_usuario_proveedor
                  )
            LIMIT 1 {$lock}
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_solicitud', $codigoSolicitud, PDO::PARAM_INT);

        /*
         * PDO MySQL con prepares nativos no admite reutilizar el mismo
         * parámetro nombrado más de una vez en una misma consulta.
         * Cada comparación usa su propio placeholder, aunque ambas reciban
         * el mismo código de usuario autenticado.
         */
        $st->bindValue(':codigo_usuario_solicitante', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_proveedor', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function rolDeUsuario(array $solicitud, int $codigoUsuario): string
    {
        return (int)($solicitud['codigo_usuario_solicitante'] ?? 0) === $codigoUsuario
            ? 'solicitante'
            : 'proveedor';
    }

    private function registrarInteraccion(
        int $codigoSolicitud,
        int $codigoUsuario,
        string $rol,
        string $tipo,
        string $mensaje,
        array $payload = []
    ): int {
        $sql = "
            INSERT INTO solicitud_servicio_interaccion
              (codigo_solicitud_servicio, codigo_usuario_autor, rol_autor, tipo_interaccion, mensaje, payload_json)
            VALUES
              (:solicitud, :usuario, :rol, :tipo, :mensaje, :payload)
        ";
        $st = $this->dblink->prepare($sql);
        $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->bindValue(':rol', $rol, PDO::PARAM_STR);
        $st->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $st->bindValue(':mensaje', $mensaje !== '' ? $mensaje : null, $mensaje !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $st->bindValue(':payload', $this->jsonEncode($payload), PDO::PARAM_STR);
        $st->execute();
        return (int)$this->dblink->lastInsertId();
    }

    private function actualizarUltimaInteraccion(int $codigoSolicitud): void
    {
        $st = $this->dblink->prepare(
            'UPDATE solicitud_servicio SET ultima_interaccion_at = NOW(), updated_at = CURRENT_TIMESTAMP WHERE codigo_solicitud_servicio = :id'
        );
        $st->execute([':id' => $codigoSolicitud]);
    }

    private function ubicacionSolicitadaPendiente(int $codigoSolicitud): bool
    {
        $sql = "
            SELECT tipo_interaccion
            FROM solicitud_servicio_interaccion
            WHERE codigo_solicitud_servicio = :solicitud
              AND tipo_interaccion IN ('ubicacion_solicitada_para_cotizar', 'ubicacion_compartida_para_cotizar', 'ubicacion_compartida')
            ORDER BY codigo_solicitud_servicio_interaccion DESC
            LIMIT 1
        ";
        $st = $this->dblink->prepare($sql);
        $st->execute([':solicitud' => $codigoSolicitud]);
        return (string)($st->fetchColumn() ?: '') === 'ubicacion_solicitada_para_cotizar';
    }

    private function getMimeReal(string $tmp): ?string
    {
        if (!is_file($tmp)) return null;
        if (function_exists('finfo_open')) {
            $f = finfo_open(FILEINFO_MIME_TYPE);
            if ($f) {
                $mime = finfo_file($f, $tmp);
                finfo_close($f);
                if ($mime) return (string)$mime;
            }
        }
        $info = @getimagesize($tmp);
        return $info && !empty($info['mime']) ? (string)$info['mime'] : null;
    }

    private function extensionPorMime(string $mime): string
    {
        return match (strtolower($mime)) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
    }

    private function archivosDesdeInput(array $files, string $key): array
    {
        if (empty($files[$key]) || !is_array($files[$key]['name'] ?? null)) {
            return [];
        }

        $salida = [];
        foreach ($files[$key]['name'] as $i => $nombre) {
            $salida[] = [
                'nombre' => (string)$nombre,
                'tmp' => (string)($files[$key]['tmp_name'][$i] ?? ''),
                'error' => (int)($files[$key]['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int)($files[$key]['size'][$i] ?? 0),
            ];
        }
        return $salida;
    }

    private function validarArchivos(array $archivos): array
    {
        if (count($archivos) > self::MAX_ADJUNTOS) {
            throw new InvalidArgumentException('MAX_ADJUNTOS_EXCEDIDO');
        }

        $validados = [];
        foreach ($archivos as $archivo) {
            if ((int)$archivo['error'] !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException('ADJUNTO_INVALIDO');
            }
            if ($archivo['tmp'] === '' || !is_uploaded_file($archivo['tmp'])) {
                throw new InvalidArgumentException('ADJUNTO_INVALIDO');
            }
            if ((int)$archivo['size'] <= 0 || (int)$archivo['size'] > self::MAX_BYTES_ADJUNTO) {
                throw new InvalidArgumentException('ADJUNTO_PESO_INVALIDO');
            }

            $dim = @getimagesize($archivo['tmp']);
            $mime = $this->getMimeReal($archivo['tmp']) ?: (string)($dim['mime'] ?? '');
            if (!in_array(strtolower($mime), ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
                throw new InvalidArgumentException('ADJUNTO_FORMATO_INVALIDO');
            }

            $validados[] = array_merge($archivo, [
                'mime' => strtolower($mime),
                'ancho' => isset($dim[0]) ? (int)$dim[0] : null,
                'alto' => isset($dim[1]) ? (int)$dim[1] : null,
            ]);
        }
        return $validados;
    }

    private function guardarAdjuntos(
        int $codigoSolicitud,
        int $codigoUsuario,
        string $origen,
        array $archivos,
        ?int $codigoInteraccion = null,
        ?int $codigoPropuesta = null
    ): array {
        $archivos = $this->validarArchivos($archivos);
        if (!$archivos) return [];

        $raiz = realpath(__DIR__ . '/..');
        if ($raiz === false) throw new RuntimeException('No se pudo resolver la raíz del proyecto.');

        $sub = 'uploads/servicios/' . $codigoSolicitud . '/' . $origen . '_' . ($codigoInteraccion ?: $codigoPropuesta ?: time());
        $dir = $raiz . DIRECTORY_SEPARATOR . $sub;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear el directorio de adjuntos.');
        }

        $guardados = [];
        foreach ($archivos as $pos => $archivo) {
            $nombre = 'img_' . ($pos + 1) . '_' . bin2hex(random_bytes(6)) . '.' . $this->extensionPorMime($archivo['mime']);
            $abs = $dir . DIRECTORY_SEPARATOR . $nombre;
            $rel = $sub . '/' . $nombre;

            if (!move_uploaded_file($archivo['tmp'], $abs)) {
                foreach ($guardados as $previo) {
                    @unlink($raiz . DIRECTORY_SEPARATOR . $previo['ruta']);
                }
                throw new RuntimeException('No se pudo guardar una imagen adjunta.');
            }

            $sql = "
              INSERT INTO solicitud_servicio_adjunto
                (codigo_solicitud_servicio, codigo_solicitud_servicio_interaccion, codigo_solicitud_servicio_propuesta,
                 codigo_usuario_autor, origen, ruta, nombre_original, mime, peso_bytes, ancho, alto)
              VALUES
                (:solicitud, :interaccion, :propuesta, :autor, :origen, :ruta, :nombre, :mime, :peso, :ancho, :alto)
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':solicitud', $codigoSolicitud, PDO::PARAM_INT);
            $st->bindValue(':interaccion', $codigoInteraccion, $codigoInteraccion ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $st->bindValue(':propuesta', $codigoPropuesta, $codigoPropuesta ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $st->bindValue(':autor', $codigoUsuario, PDO::PARAM_INT);
            $st->bindValue(':origen', $origen, PDO::PARAM_STR);
            $st->bindValue(':ruta', $rel, PDO::PARAM_STR);
            $st->bindValue(':nombre', mb_substr($archivo['nombre'], 0, 255, 'UTF-8'), PDO::PARAM_STR);
            $st->bindValue(':mime', $archivo['mime'], PDO::PARAM_STR);
            $st->bindValue(':peso', (int)$archivo['size'], PDO::PARAM_INT);
            $st->bindValue(':ancho', $archivo['ancho'], $archivo['ancho'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $st->bindValue(':alto', $archivo['alto'], $archivo['alto'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $st->execute();

            $guardados[] = [
                'codigo_solicitud_servicio_adjunto' => (int)$this->dblink->lastInsertId(),
                'ruta' => $rel,
                'nombre_original' => $archivo['nombre'],
            ];
        }
        return $guardados;
    }

    private function notificar(
        int $destino,
        int $codigoSolicitud,
        string $subcategoria,
        string $titulo,
        string $mensaje,
        string $ruta
    ): void
    {
        if ($destino <= 0 || $codigoSolicitud <= 0) return;

        try {
            $notif = new Notificacion($this->dblink);
            $data = [
                'codigo_usuario' => $destino,
                'categoria' => Notificacion::CAT_SERVICIO,
                'subcategoria' => $subcategoria,
                'referencia_id' => $codigoSolicitud,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'payload' => [
                    'codigo_solicitud_servicio' => $codigoSolicitud,
                    'ruta' => $ruta,
                ],
            ];

            if ($subcategoria === 'mensaje_conversacion') {
                $notif->crearOActualizarNoLeida($data);
            } else {
                $notif->crear($data);
            }
        } catch (Throwable $e) {
            error_log('[EV][SolicitudServicioChat::notificar] ' . $e->getMessage());
        }
    }

    public function registrarContextoInicial(int $codigoSolicitud, int $codigoSolicitante, array $input, array $files): array
    {
        if ($codigoSolicitud <= 0 || $codigoSolicitante <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }

        $referencia = $this->texto($input['referencia_ubicacion'] ?? '', 160);
        $modalidad = $this->modalidadPermitida($input['modalidad_preferida'] ?? 'a_coordinar');
        $presupuestoRaw = trim((string)($input['presupuesto_estimado'] ?? ''));
        $presupuesto = ($presupuestoRaw !== '' && is_numeric($presupuestoRaw) && (float)$presupuestoRaw > 0)
            ? round((float)$presupuestoRaw, 2)
            : null;
        $contexto = $this->jsonSeguro($input['datos_contextuales'] ?? []);
        if (count($contexto) > 20) $contexto = array_slice($contexto, 0, 20, true);

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoSolicitante, true);
            if (!$solicitud || $this->rolDeUsuario($solicitud, $codigoSolicitante) !== 'solicitante') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $st = $this->dblink->prepare("\n              UPDATE solicitud_servicio\n              SET modalidad_preferida = :modalidad, referencia_ubicacion = :referencia,\n                  presupuesto_estimado = :presupuesto, datos_contextuales_json = :contexto,\n                  ultima_interaccion_at = NOW(), updated_at = CURRENT_TIMESTAMP\n              WHERE codigo_solicitud_servicio = :id\n            ");
            $st->bindValue(':modalidad', $modalidad, PDO::PARAM_STR);
            $st->bindValue(':referencia', $referencia !== '' ? $referencia : null, $referencia !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':presupuesto', $presupuesto, $presupuesto !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':contexto', $this->jsonEncode($contexto), PDO::PARAM_STR);
            $st->bindValue(':id', $codigoSolicitud, PDO::PARAM_INT);
            $st->execute();

            $textoInicial = $this->texto($solicitud['mensaje_solicitante'] ?? '', self::MAX_MENSAJE);
            $interaccion = $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoSolicitante,
                'solicitante',
                'solicitud_creada',
                $textoInicial,
                [
                    'modalidad_preferida' => $modalidad,
                    'referencia_ubicacion' => $referencia,
                    'presupuesto_estimado' => $presupuesto,
                    'datos_contextuales' => $contexto,
                ]
            );

            $this->guardarAdjuntos(
                $codigoSolicitud,
                $codigoSolicitante,
                'solicitud',
                $this->archivosDesdeInput($files, 'adjuntos_solicitud'),
                $interaccion,
                null
            );

            $this->dblink->commit();
            return ['ok' => true, 'data' => ['codigo_interaccion' => $interaccion]];
        } catch (InvalidArgumentException $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            return ['ok' => false, 'error' => $e->getMessage(), 'mensaje' => 'Revisa las imágenes adjuntas de la solicitud.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicioChat][registrarContextoInicial] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_GUARDAR_CONTEXTO', 'mensaje' => 'La solicitud fue creada, pero no se pudo guardar el detalle adicional.'];
        }
    }

    public function enviarMensaje(int $codigoSolicitud, int $codigoUsuario, string $mensaje, array $files): array
    {
        $mensaje = $this->texto($mensaje, self::MAX_MENSAJE);
        $archivos = $this->archivosDesdeInput($files, 'adjuntos_mensaje');
        if ($mensaje === '' && !$archivos) {
            return ['ok' => false, 'error' => 'MENSAJE_REQUERIDO', 'mensaje' => 'Escribe un mensaje o adjunta al menos una imagen.'];
        }

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario, true);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }
            if ($this->esEstadoCerrado((string)$solicitud['estado'])) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'Esta solicitud ya está cerrada y no admite nuevos mensajes.'];
            }

            $rol = $this->rolDeUsuario($solicitud, $codigoUsuario);
            $interaccion = $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoUsuario,
                $rol,
                'mensaje_' . $rol,
                $mensaje,
                []
            );

            $this->guardarAdjuntos($codigoSolicitud, $codigoUsuario, 'mensaje', $archivos, $interaccion, null);
            $this->actualizarUltimaInteraccion($codigoSolicitud);

            $destino = $rol === 'solicitante'
                ? (int)$solicitud['codigo_usuario_proveedor']
                : (int)$solicitud['codigo_usuario_solicitante'];
            $this->notificar(
                $destino,
                $codigoSolicitud,
                'mensaje_conversacion',
                'Nuevo mensaje sobre un servicio',
                'Tienes un nuevo mensaje sobre “' . (string)$solicitud['titulo_servicio'] . '”.',
                $rol === 'solicitante'
                    ? '/mis-solicitudes-servicio-vendedor'
                    : '/mis-solicitudes-servicio-comprador'
            );

            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'Mensaje enviado correctamente.'];
        } catch (InvalidArgumentException $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            return ['ok' => false, 'error' => $e->getMessage(), 'mensaje' => 'Revisa las imágenes adjuntas antes de enviar.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicioChat][enviarMensaje] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_ENVIAR_MENSAJE', 'mensaje' => 'No se pudo enviar el mensaje.'];
        }
    }

    public function guardarAdjuntosPropuesta(int $codigoSolicitud, int $codigoProveedor, int $codigoPropuesta, array $files): array
    {
        $archivos = $this->archivosDesdeInput($files, 'adjuntos_propuesta');
        if (!$archivos) return ['ok' => true, 'data' => ['adjuntos' => 0]];

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoProveedor, true);
            if (!$solicitud || $this->rolDeUsuario($solicitud, $codigoProveedor) !== 'proveedor') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró la solicitud para adjuntar la cotización.'];
            }

            $st = $this->dblink->prepare("\n              SELECT codigo_solicitud_servicio_propuesta\n              FROM solicitud_servicio_propuesta\n              WHERE codigo_solicitud_servicio_propuesta = :propuesta\n                AND codigo_solicitud_servicio = :solicitud\n                AND codigo_usuario_proveedor = :proveedor\n              LIMIT 1 FOR UPDATE\n            ");
            $st->execute([':propuesta' => $codigoPropuesta, ':solicitud' => $codigoSolicitud, ':proveedor' => $codigoProveedor]);
            if (!$st->fetchColumn()) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'PROPUESTA_NO_ENCONTRADA', 'mensaje' => 'No se encontró la cotización enviada.'];
            }

            $guardados = $this->guardarAdjuntos($codigoSolicitud, $codigoProveedor, 'propuesta', $archivos, null, $codigoPropuesta);
            $this->actualizarUltimaInteraccion($codigoSolicitud);
            $this->dblink->commit();
            return ['ok' => true, 'data' => ['adjuntos' => count($guardados)]];
        } catch (InvalidArgumentException $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            return ['ok' => false, 'error' => $e->getMessage(), 'mensaje' => 'Revisa las imágenes adjuntas de la cotización.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicioChat][guardarAdjuntosPropuesta] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_ADJUNTOS_PROPUESTA', 'mensaje' => 'No se pudieron guardar los adjuntos de la cotización.'];
        }
    }

    public function solicitarUbicacionParaCotizar(int $codigoSolicitud, int $codigoProveedor): array
    {
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoProveedor, true);
            if (!$solicitud || $this->rolDeUsuario($solicitud, $codigoProveedor) !== 'proveedor') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }
            if ($this->esEstadoCerrado((string)$solicitud['estado'])) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'Esta solicitud ya está cerrada.'];
            }
            if ($this->puedeVerDireccionExacta($solicitud)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La ubicación ya fue compartida en esta solicitud.'];
            }
            if ($this->ubicacionSolicitadaPendiente($codigoSolicitud)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La ubicación ya fue solicitada. Espera la respuesta del comprador.'];
            }

            $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoProveedor,
                'proveedor',
                'ubicacion_solicitada_para_cotizar',
                'El proveedor solicitó la ubicación para precisar la cotización final.',
                []
            );
            $this->actualizarUltimaInteraccion($codigoSolicitud);
            $this->notificar(
                (int)$solicitud['codigo_usuario_solicitante'],
                $codigoSolicitud,
                'ubicacion_solicitada_para_cotizar',
                'Ubicación solicitada para cotizar',
                'El proveedor solicitó la ubicación para precisar la cotización de “' . (string)$solicitud['titulo_servicio'] . '”.',
                '/mis-solicitudes-servicio-comprador'
            );
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'Se solicitó la ubicación al comprador.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicioChat][solicitarUbicacionParaCotizar] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_SOLICITAR_UBICACION', 'mensaje' => 'No se pudo solicitar la ubicación.'];
        }
    }

    public function compartirDireccion(int $codigoSolicitud, int $codigoSolicitante, string $direccion): array
    {
        $direccion = $this->texto($direccion, 500);
        if (mb_strlen($direccion, 'UTF-8') < 5) {
            return ['ok' => false, 'error' => 'DIRECCION_COMPARTIR_REQUERIDA', 'mensaje' => 'Indica un punto exacto de atención para compartirlo con el proveedor.'];
        }

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoSolicitante, true);
            if (!$solicitud || $this->rolDeUsuario($solicitud, $codigoSolicitante) !== 'solicitante') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }
            if ($this->esEstadoCerrado((string)$solicitud['estado'])) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'Esta solicitud ya está cerrada.'];
            }

            $ubicacionSolicitada = $this->ubicacionSolicitadaPendiente($codigoSolicitud);
            $estadoPosteriorAceptacion = in_array((string)$solicitud['estado'], ['coordinacion_confirmada', 'servicio_realizado_proveedor'], true);
            if (!$ubicacionSolicitada && !$estadoPosteriorAceptacion) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'Comparte la ubicación solo cuando el proveedor la solicite para cotizar.'];
            }

            $st = $this->dblink->prepare("
              UPDATE solicitud_servicio
              SET direccion_atencion = :direccion, direccion_compartida_at = NOW(), updated_at = CURRENT_TIMESTAMP
              WHERE codigo_solicitud_servicio = :id
            ");
            $st->execute([':direccion' => $direccion, ':id' => $codigoSolicitud]);
            $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoSolicitante,
                'solicitante',
                'ubicacion_compartida_para_cotizar',
                'El comprador compartió la ubicación para precisar la cotización final.',
                []
            );
            $this->actualizarUltimaInteraccion($codigoSolicitud);
            $this->notificar(
                (int)$solicitud['codigo_usuario_proveedor'],
                $codigoSolicitud,
                'ubicacion_compartida_para_cotizar',
                'Punto de atención disponible',
                'El comprador compartió el punto de atención para “' . (string)$solicitud['titulo_servicio'] . '”.',
                '/mis-solicitudes-servicio-vendedor'
            );
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'El punto exacto de atención fue compartido con el proveedor.'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicioChat][compartirDireccion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_COMPARTIR_DIRECCION', 'mensaje' => 'No se pudo compartir la ubicación.'];
        }
    }

    private function adjuntosPorSolicitud(int $codigoSolicitud): array
    {
        $sql = "
          SELECT *
          FROM solicitud_servicio_adjunto
          WHERE codigo_solicitud_servicio = :solicitud
          ORDER BY created_at ASC, codigo_solicitud_servicio_adjunto ASC
        ";
        $st = $this->dblink->prepare($sql);
        $st->execute([':solicitud' => $codigoSolicitud]);
        $porInteraccion = [];
        $porPropuesta = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $item = [
                'codigo_adjunto' => (int)$row['codigo_solicitud_servicio_adjunto'],
                'ruta' => (string)$row['ruta'],
                'nombre_original' => (string)$row['nombre_original'],
                'mime' => (string)$row['mime'],
                'peso_bytes' => (int)$row['peso_bytes'],
            ];
            if (!empty($row['codigo_solicitud_servicio_interaccion'])) {
                $porInteraccion[(int)$row['codigo_solicitud_servicio_interaccion']][] = $item;
            }
            if (!empty($row['codigo_solicitud_servicio_propuesta'])) {
                $porPropuesta[(int)$row['codigo_solicitud_servicio_propuesta']][] = $item;
            }
        }
        return [$porInteraccion, $porPropuesta];
    }

    public function obtenerConversacion(int $codigoSolicitud, int $codigoUsuario): array
    {
        if ($codigoSolicitud <= 0 || $codigoUsuario <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }

        try {
            $solicitud = $this->obtenerSolicitudParticipante($codigoSolicitud, $codigoUsuario, false);
            if (!$solicitud) {
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta conversación de servicio.'];
            }

            $rol = $this->rolDeUsuario($solicitud, $codigoUsuario);
            [$adjuntosInteraccion, $adjuntosPropuesta] = $this->adjuntosPorSolicitud($codigoSolicitud);

            $stI = $this->dblink->prepare("\n              SELECT i.*, u.nombre AS nombre_autor\n              FROM solicitud_servicio_interaccion i\n              INNER JOIN usuario u ON u.codigo_usuario = i.codigo_usuario_autor\n              WHERE i.codigo_solicitud_servicio = :solicitud\n              ORDER BY i.created_at ASC, i.codigo_solicitud_servicio_interaccion ASC\n            ");
            $stI->execute([':solicitud' => $codigoSolicitud]);
            $interacciones = [];
            $ultimaSolicitudUbicacion = 0;
            $ultimaUbicacionCompartida = 0;
            foreach ($stI->fetchAll(PDO::FETCH_ASSOC) ?: [] as $i) {
                $id = (int)$i['codigo_solicitud_servicio_interaccion'];
                $tipoInteraccion = (string)($i['tipo_interaccion'] ?? '');
                if ($tipoInteraccion === 'ubicacion_solicitada_para_cotizar') {
                    $ultimaSolicitudUbicacion = $id;
                }
                if (in_array($tipoInteraccion, ['ubicacion_compartida_para_cotizar', 'ubicacion_compartida'], true)) {
                    $ultimaUbicacionCompartida = $id;
                }
                $interacciones[] = [
                    'codigo_interaccion' => $id,
                    'rol_autor' => (string)$i['rol_autor'],
                    'nombre_autor' => trim((string)$i['nombre_autor']) ?: 'Vecino',
                    'tipo_interaccion' => $tipoInteraccion,
                    'mensaje' => (string)($i['mensaje'] ?? ''),
                    'payload' => $this->jsonSeguro($i['payload_json'] ?? ''),
                    'created_at' => $i['created_at'] ?? null,
                    'adjuntos' => $adjuntosInteraccion[$id] ?? [],
                ];
            }

            $stP = $this->dblink->prepare("\n              SELECT *\n              FROM solicitud_servicio_propuesta\n              WHERE codigo_solicitud_servicio = :solicitud\n              ORDER BY version ASC, codigo_solicitud_servicio_propuesta ASC\n            ");
            $stP->execute([':solicitud' => $codigoSolicitud]);
            $propuestas = [];
            foreach ($stP->fetchAll(PDO::FETCH_ASSOC) ?: [] as $p) {
                $id = (int)$p['codigo_solicitud_servicio_propuesta'];
                $montoTotal = $p['monto_propuesto'] !== null ? round((float)$p['monto_propuesto'], 2) : null;
                $montoAdelanto = isset($p['monto_adelanto']) && $p['monto_adelanto'] !== null
                    ? round((float)$p['monto_adelanto'], 2)
                    : null;

                $propuestas[] = [
                    'codigo_propuesta' => $id,
                    'version' => (int)$p['version'],
                    'fecha_propuesta' => $p['fecha_propuesta'] ?? null,
                    'hora_inicio' => isset($p['hora_inicio']) ? (string)($p['hora_inicio'] ?? '') : '',
                    'hora_fin' => isset($p['hora_fin']) ? (string)($p['hora_fin'] ?? '') : '',
                    'alcance_confirmado' => (string)($p['alcance_confirmado'] ?? ''),
                    'monto_propuesto' => $montoTotal !== null ? number_format($montoTotal, 2, '.', '') : null,
                    'condicion_pago' => (string)($p['condicion_pago'] ?? ''),
                    'monto_adelanto' => $montoAdelanto !== null ? number_format($montoAdelanto, 2, '.', '') : null,
                    'saldo_contra_entrega' => $montoTotal !== null
                        ? number_format(max(0, $montoTotal - (float)($montoAdelanto ?? 0)), 2, '.', '')
                        : null,
                    'fecha_vencimiento' => $p['fecha_vencimiento'] ?? null,
                    'motivo_estado' => (string)($p['motivo_estado'] ?? ''),
                    'duracion_estimada' => trim((string)($p['duracion_estimada'] ?? '')) !== ''
                        ? (string)$p['duracion_estimada']
                        : $this->calcularDuracionCotizacion($p['hora_inicio'] ?? null, $p['hora_fin'] ?? null),
                    'mensaje_proveedor' => (string)($p['mensaje_proveedor'] ?? ''),
                    'estado' => (string)$p['estado'],
                    'created_at' => $p['created_at'] ?? null,
                    'adjuntos' => $adjuntosPropuesta[$id] ?? [],
                ];
            }

            $puedeVerExacta = $this->puedeVerDireccionExacta($solicitud);
            $ubicacionSolicitadaParaCotizar = !$puedeVerExacta
                && $ultimaSolicitudUbicacion > $ultimaUbicacionCompartida
                && !$this->esEstadoCerrado((string)$solicitud['estado']);
            $datos = [
                'codigo_solicitud_servicio' => (int)$solicitud['codigo_solicitud_servicio'],
                'codigo_producto' => (int)$solicitud['codigo_producto'],
                'titulo_servicio' => (string)$solicitud['titulo_servicio'],
                'categoria_nombre' => (string)($solicitud['categoria_nombre'] ?? ''),
                'categoria_grupo_nombre' => (string)($solicitud['categoria_grupo_nombre'] ?? ''),
                'imagen_portada' => (string)($solicitud['imagen_portada'] ?? ''),
                'estado' => (string)$solicitud['estado'],
                'motivo_estado' => (string)($solicitud['motivo_estado'] ?? ''),
                'rol_actual' => $rol,
                'nombre_solicitante' => trim((string)$solicitud['nombre_solicitante']) ?: 'Vecino',
                'nombre_proveedor' => trim((string)$solicitud['nombre_proveedor']) ?: 'Vecino',
                'fecha_deseada' => $solicitud['fecha_deseada'] ?? null,
                'rango_horario' => (string)($solicitud['rango_horario'] ?? 'a_coordinar'),
                'modalidad_preferida' => (string)($solicitud['modalidad_preferida'] ?? 'a_coordinar'),
                'modalidad_preferida_texto' => $this->etiquetaModalidad((string)($solicitud['modalidad_preferida'] ?? 'a_coordinar')),
                'referencia_ubicacion' => (string)($solicitud['referencia_ubicacion'] ?? ''),
                'presupuesto_estimado' => $solicitud['presupuesto_estimado'] !== null ? (string)$solicitud['presupuesto_estimado'] : null,
                'datos_contextuales' => $this->jsonSeguro($solicitud['datos_contextuales_json'] ?? ''),
                'puede_ver_direccion_exacta' => $puedeVerExacta,
                'direccion_atencion' => $puedeVerExacta ? (string)$solicitud['direccion_atencion'] : '',
                // La ubicación se expone solo cuando el proveedor la solicita para ajustar la cotización.
                'ubicacion_solicitada_para_cotizar' => $ubicacionSolicitadaParaCotizar,
                'puede_compartir_direccion' => $rol === 'solicitante' && $ubicacionSolicitadaParaCotizar,
                'puede_solicitar_ubicacion' => $rol === 'proveedor'
                    && !$puedeVerExacta
                    && !$ubicacionSolicitadaParaCotizar
                    && !$this->esEstadoCerrado((string)$solicitud['estado']),
                'puede_enviar_mensajes' => !$this->esEstadoCerrado((string)$solicitud['estado']),
                'interacciones' => $interacciones,
                'propuestas' => $propuestas,
            ];

            return ['ok' => true, 'data' => $datos];
        } catch (Throwable $e) {
            error_log('[EV][SolicitudServicioChat][obtenerConversacion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_CONVERSACION', 'mensaje' => 'No se pudo cargar la conversación.'];
        }
    }
}
