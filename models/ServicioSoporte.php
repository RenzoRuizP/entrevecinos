<?php
// models/ServicioSoporte.php
// Punto 11 EV: bandeja y resolución operativa de incidencias de servicios.

declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/Notificacion.php';

final class ServicioSoporte extends Conexion
{
    private function texto($valor, int $maximo): string
    {
        $texto = trim((string)$valor);
        return $texto === '' ? '' : mb_substr($texto, 0, $maximo, 'UTF-8');
    }

    private function jsonDecode($valor): array
    {
        $raw = trim((string)$valor);
        if ($raw === '') return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function registrarInteraccion(int $solicitud, int $usuario, string $tipo, string $mensaje, array $payload = []): void
    {
        $st = $this->dblink->prepare("
            INSERT INTO solicitud_servicio_interaccion
            (codigo_solicitud_servicio, codigo_usuario_autor, rol_autor, tipo_interaccion, mensaje, payload_json)
            VALUES (:solicitud, :usuario, 'soporte', :tipo, :mensaje, :payload)
        ");
        $st->bindValue(':solicitud', $solicitud, PDO::PARAM_INT);
        $st->bindValue(':usuario', $usuario, PDO::PARAM_INT);
        $st->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $st->bindValue(':mensaje', $mensaje, PDO::PARAM_STR);
        $payloadJson = $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $st->bindValue(':payload', $payloadJson, $payloadJson !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $st->execute();
    }

    private function notificar(int $usuario, int $solicitud, string $subcategoria, string $titulo, string $mensaje, string $ruta, string $rol): void
    {
        if ($usuario <= 0 || $solicitud <= 0) return;

        try {
            $notif = new Notificacion($this->dblink);
            $notif->crear([
                'codigo_usuario' => $usuario,
                'categoria' => Notificacion::CAT_SERVICIO,
                'subcategoria' => $subcategoria,
                'referencia_id' => $solicitud,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'payload' => [
                    'codigo_solicitud_servicio' => $solicitud,
                    'ruta' => $ruta,
                    'rol_destino' => $rol,
                ],
            ]);
        } catch (Throwable $e) {
            error_log('[EV][ServicioSoporte::notificar] ' . $e->getMessage());
        }
    }

    private function crearCalificaciones(array $row): void
    {
        $habilitacion = date('Y-m-d H:i:s');
        $limite = (new DateTimeImmutable($habilitacion, new DateTimeZone('America/Lima')))
            ->modify('+7 days')->format('Y-m-d H:i:s');
        $st = $this->dblink->prepare("
            INSERT IGNORE INTO calificacion_servicio
            (codigo_solicitud_servicio, codigo_usuario_calificador, codigo_usuario_calificado,
             rol_calificador, rol_calificado, estado, fecha_habilitacion, fecha_limite)
            VALUES
            (:solicitud1, :comprador1, :proveedor1, 'comprador', 'proveedor', 'pendiente', :h1, :l1),
            (:solicitud2, :proveedor2, :comprador2, 'proveedor', 'comprador', 'pendiente', :h2, :l2)
        ");
        $st->execute([
            ':solicitud1' => (int)$row['codigo_solicitud_servicio'],
            ':comprador1' => (int)$row['codigo_usuario_solicitante'],
            ':proveedor1' => (int)$row['codigo_usuario_proveedor'],
            ':solicitud2' => (int)$row['codigo_solicitud_servicio'],
            ':proveedor2' => (int)$row['codigo_usuario_proveedor'],
            ':comprador2' => (int)$row['codigo_usuario_solicitante'],
            ':h1' => $habilitacion,
            ':l1' => $limite,
            ':h2' => $habilitacion,
            ':l2' => $limite,
        ]);
    }

    public function listar(array $filtros = []): array
    {
        $estado = strtolower(trim((string)($filtros['estado'] ?? 'abiertas')));
        $buscar = $this->texto($filtros['buscar'] ?? '', 120);
        $page = max(1, (int)($filtros['page'] ?? 1));
        $size = max(1, min(50, (int)($filtros['size'] ?? 20)));
        $offset = ($page - 1) * $size;
        $tipoConjunto = strtolower(trim((string)($filtros['tipo_conjunto'] ?? '')));
        $codigoComunidad = max(0, (int)($filtros['codigo_comunidad'] ?? 0));

        $where = ['i.requiere_soporte = 1'];
        $params = [];
        if ($estado === 'abiertas') {
            $where[] = "i.estado IN ('revision_soporte','esperando_informacion')";
        } elseif ($estado === 'resueltas') {
            $where[] = "i.estado IN ('resuelta','cerrada','cancelada')";
        }
        if ($codigoComunidad > 0 && in_array($tipoConjunto, ['condominio','urbanizacion'], true)) {
            $where[] = $tipoConjunto === 'condominio'
                ? "p.tipo_conjunto_publicacion='condominio' AND p.codigo_condominio_publicacion=:codigo_comunidad"
                : "p.tipo_conjunto_publicacion='urbanizacion' AND p.codigo_urbanizacion_publicacion=:codigo_comunidad";
            $params[':codigo_comunidad'] = $codigoComunidad;
        }
        if ($buscar !== '') {
            $where[] = '(p.titulo LIKE :buscar_titulo OR uc.nombre LIKE :buscar_comprador OR up.nombre LIKE :buscar_proveedor OR CAST(i.codigo_incidencia AS CHAR) LIKE :buscar_incidencia)';
            $valorBuscar = '%' . $buscar . '%';
            $params[':buscar_titulo'] = $valorBuscar;
            $params[':buscar_comprador'] = $valorBuscar;
            $params[':buscar_proveedor'] = $valorBuscar;
            $params[':buscar_incidencia'] = $valorBuscar;
        }
        $whereSql = implode(' AND ', $where);

        try {
            $stTotal = $this->dblink->prepare("
                SELECT COUNT(*)
                FROM solicitud_servicio_incidencia i
                INNER JOIN solicitud_servicio ss ON ss.codigo_solicitud_servicio = i.codigo_solicitud_servicio
                INNER JOIN producto p ON p.codigo_producto = ss.codigo_producto
                INNER JOIN usuario uc ON uc.codigo_usuario = ss.codigo_usuario_solicitante
                INNER JOIN usuario up ON up.codigo_usuario = ss.codigo_usuario_proveedor
                WHERE {$whereSql}
            ");
            foreach ($params as $key => $value) $stTotal->bindValue($key, $value, PDO::PARAM_STR);
            $stTotal->execute();
            $total = (int)$stTotal->fetchColumn();

            $sql = "
                SELECT
                    i.codigo_incidencia,
                    i.codigo_solicitud_servicio,
                    i.numero_incidencia,
                    i.categoria,
                    i.descripcion,
                    i.estado,
                    i.fecha_escalamiento_soporte,
                    i.fecha_resolucion_soporte,
                    i.updated_at,
                    ss.estado AS estado_solicitud,
                    p.titulo AS titulo_servicio,
                    p.imagen_portada,
                    uc.nombre AS nombre_comprador,
                    up.nombre AS nombre_proveedor,
                    ur.nombre AS nombre_reporta,
                    i.rol_reporta
                FROM solicitud_servicio_incidencia i
                INNER JOIN solicitud_servicio ss ON ss.codigo_solicitud_servicio = i.codigo_solicitud_servicio
                INNER JOIN producto p ON p.codigo_producto = ss.codigo_producto
                INNER JOIN usuario uc ON uc.codigo_usuario = ss.codigo_usuario_solicitante
                INNER JOIN usuario up ON up.codigo_usuario = ss.codigo_usuario_proveedor
                INNER JOIN usuario ur ON ur.codigo_usuario = i.codigo_usuario_reporta
                WHERE {$whereSql}
                ORDER BY
                    CASE i.estado
                        WHEN 'revision_soporte' THEN 1
                        WHEN 'esperando_informacion' THEN 2
                        ELSE 3
                    END,
                    COALESCE(i.fecha_escalamiento_soporte, i.updated_at) ASC
                LIMIT :lim OFFSET :off
            ";
            $st = $this->dblink->prepare($sql);
            foreach ($params as $key => $value) $st->bindValue($key, $value, PDO::PARAM_STR);
            $st->bindValue(':lim', $size, PDO::PARAM_INT);
            $st->bindValue(':off', $offset, PDO::PARAM_INT);
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return [
                'ok' => true,
                'data' => $rows,
                'meta' => ['page' => $page, 'size' => $size, 'total' => $total],
            ];
        } catch (Throwable $e) {
            error_log('[EV][ServicioSoporte][listar] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_LISTAR_INCIDENCIAS_SERVICIO', 'mensaje' => 'No se pudieron cargar las incidencias de servicios.'];
        }
    }

    public function resumen(array $filtros = []): array
    {
        try {
            $tipoConjunto = strtolower(trim((string)($filtros['tipo_conjunto'] ?? '')));
            $codigoComunidad = max(0, (int)($filtros['codigo_comunidad'] ?? 0));
            $where = '';
            $params = [];
            if ($codigoComunidad > 0 && in_array($tipoConjunto, ['condominio','urbanizacion'], true)) {
                $where = $tipoConjunto === 'condominio'
                    ? " AND p.tipo_conjunto_publicacion='condominio' AND p.codigo_condominio_publicacion=:comunidad"
                    : " AND p.tipo_conjunto_publicacion='urbanizacion' AND p.codigo_urbanizacion_publicacion=:comunidad";
                $params[':comunidad'] = $codigoComunidad;
            }
            $sql = "
                SELECT
                    SUM(CASE WHEN i.requiere_soporte = 1 AND i.estado IN ('revision_soporte','esperando_informacion') THEN 1 ELSE 0 END) AS abiertas,
                    SUM(CASE WHEN i.requiere_soporte = 1 AND i.estado = 'revision_soporte' THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN i.fecha_resolucion_soporte IS NOT NULL AND DATE(i.fecha_resolucion_soporte) = CURDATE() THEN 1 ELSE 0 END) AS resueltas_hoy
                FROM solicitud_servicio_incidencia i
                INNER JOIN solicitud_servicio ss ON ss.codigo_solicitud_servicio=i.codigo_solicitud_servicio
                INNER JOIN producto p ON p.codigo_producto=ss.codigo_producto
                WHERE 1=1 {$where}
            ";
            $st = $this->dblink->prepare($sql);
            foreach ($params as $key => $value) $st->bindValue($key, $value, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
            return ['ok'=>true,'data'=>[
                'abiertas'=>(int)($row['abiertas']??0),
                'pendientes'=>(int)($row['pendientes']??0),
                'resueltas_hoy'=>(int)($row['resueltas_hoy']??0),
            ]];
        } catch (Throwable $e) {
            error_log('[EV][ServicioSoporte][resumen] ' . $e->getMessage());
            return ['ok'=>false,'error'=>'ERROR_RESUMEN_SERVICIOS','mensaje'=>'No se pudo obtener el resumen de servicios.'];
        }
    }

    public function detalle(int $codigoIncidencia): array
    {
        if ($codigoIncidencia <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'Incidencia inválida.'];
        }

        try {
            $st = $this->dblink->prepare("
                SELECT
                    i.*,
                    ss.codigo_producto,
                    ss.codigo_usuario_solicitante,
                    ss.codigo_usuario_proveedor,
                    ss.estado AS estado_solicitud,
                    ss.estado_anterior AS estado_anterior_solicitud,
                    ss.motivo_estado AS motivo_estado_solicitud,
                    ss.fecha_ejecucion_original,
                    ss.hora_inicio_original,
                    ss.hora_fin_original,
                    ss.fecha_ejecucion_vigente,
                    ss.hora_inicio_vigente,
                    ss.hora_fin_vigente,
                    ss.version_operativa,
                    ss.fecha_inicio_servicio,
                    ss.fecha_realizado_proveedor,
                    ss.fecha_limite_confirmacion,
                    ss.fecha_confirmacion_solicitante,
                    ss.fecha_cierre AS fecha_cierre_solicitud,
                    p.titulo AS titulo_servicio,
                    p.descripcion AS descripcion_servicio,
                    p.imagen_portada,
                    uc.nombre AS nombre_comprador,
                    uc.email AS email_comprador,
                    up.nombre AS nombre_proveedor,
                    up.email AS email_proveedor,
                    ur.nombre AS nombre_reporta,
                    uresp.nombre AS nombre_responde,
                    usol.nombre AS nombre_solucion,
                    usp.nombre AS nombre_soporte,
                    cp.version AS version_cotizacion,
                    cp.fecha_propuesta AS fecha_cotizada,
                    cp.hora_inicio AS hora_inicio_cotizada,
                    cp.hora_fin AS hora_fin_cotizada,
                    cp.alcance_confirmado,
                    cp.monto_propuesto,
                    cp.condicion_pago,
                    cp.monto_adelanto,
                    cp.duracion_estimada,
                    cp.requisitos
                FROM solicitud_servicio_incidencia i
                INNER JOIN solicitud_servicio ss ON ss.codigo_solicitud_servicio = i.codigo_solicitud_servicio
                INNER JOIN producto p ON p.codigo_producto = ss.codigo_producto
                INNER JOIN usuario uc ON uc.codigo_usuario = ss.codigo_usuario_solicitante
                INNER JOIN usuario up ON up.codigo_usuario = ss.codigo_usuario_proveedor
                INNER JOIN usuario ur ON ur.codigo_usuario = i.codigo_usuario_reporta
                LEFT JOIN usuario uresp ON uresp.codigo_usuario = i.codigo_usuario_responde
                LEFT JOIN usuario usol ON usol.codigo_usuario = i.codigo_usuario_solucion
                LEFT JOIN usuario usp ON usp.codigo_usuario = i.codigo_usuario_soporte
                LEFT JOIN solicitud_servicio_propuesta cp
                       ON cp.codigo_solicitud_servicio = ss.codigo_solicitud_servicio
                      AND cp.estado = 'aceptada'
                WHERE i.codigo_incidencia = :incidencia
                LIMIT 1
            ");
            $st->bindValue(':incidencia', $codigoIncidencia, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return ['ok' => false, 'error' => 'INCIDENCIA_NO_ENCONTRADA', 'mensaje' => 'No se encontró la incidencia.'];
            }

            $stAdj = $this->dblink->prepare('SELECT * FROM solicitud_servicio_incidencia_adjunto WHERE codigo_incidencia = :incidencia ORDER BY codigo_incidencia_adjunto');
            $stAdj->bindValue(':incidencia', $codigoIncidencia, PDO::PARAM_INT);
            $stAdj->execute();

            $stRep = $this->dblink->prepare("
                SELECT r.*, u.nombre AS nombre_propone, ur.nombre AS nombre_responde
                FROM solicitud_servicio_reprogramacion r
                INNER JOIN usuario u ON u.codigo_usuario = r.codigo_usuario_propone
                LEFT JOIN usuario ur ON ur.codigo_usuario = r.codigo_usuario_responde
                WHERE r.codigo_solicitud_servicio = :solicitud
                ORDER BY r.codigo_reprogramacion DESC
            ");
            $stRep->bindValue(':solicitud', (int)$row['codigo_solicitud_servicio'], PDO::PARAM_INT);
            $stRep->execute();

            $stTimeline = $this->dblink->prepare("
                SELECT i.*, u.nombre AS nombre_autor
                FROM solicitud_servicio_interaccion i
                INNER JOIN usuario u ON u.codigo_usuario = i.codigo_usuario_autor
                WHERE i.codigo_solicitud_servicio = :solicitud
                ORDER BY i.codigo_solicitud_servicio_interaccion DESC
                LIMIT 150
            ");
            $stTimeline->bindValue(':solicitud', (int)$row['codigo_solicitud_servicio'], PDO::PARAM_INT);
            $stTimeline->execute();
            $timeline = array_map(function (array $item): array {
                $item['payload'] = $this->jsonDecode($item['payload_json'] ?? null);
                unset($item['payload_json']);
                return $item;
            }, $stTimeline->fetchAll(PDO::FETCH_ASSOC) ?: []);

            return [
                'ok' => true,
                'data' => [
                    'caso' => $row,
                    'adjuntos' => $stAdj->fetchAll(PDO::FETCH_ASSOC) ?: [],
                    'reprogramaciones' => $stRep->fetchAll(PDO::FETCH_ASSOC) ?: [],
                    'timeline' => $timeline,
                ],
            ];
        } catch (Throwable $e) {
            error_log('[EV][ServicioSoporte][detalle] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_DETALLE_INCIDENCIA', 'mensaje' => 'No se pudo cargar el detalle de la incidencia.'];
        }
    }

    public function resolver(int $codigoIncidencia, int $codigoSoporte, string $accion, string $comentario): array
    {
        $accion = strtolower(trim($accion));
        $comentario = $this->texto($comentario, 3000);
        $permitidas = ['solicitar_informacion', 'reanudar_atencion', 'confirmar_completado', 'cancelar_servicio'];
        if (!in_array($accion, $permitidas, true)) {
            return ['ok' => false, 'error' => 'ACCION_INVALIDA', 'mensaje' => 'Selecciona una resolución válida.'];
        }
        if (mb_strlen($comentario, 'UTF-8') < 8) {
            return ['ok' => false, 'error' => 'RESOLUCION_REQUERIDA', 'mensaje' => 'Registra una explicación de al menos 8 caracteres.'];
        }

        try {
            $this->dblink->beginTransaction();
            $st = $this->dblink->prepare("
                SELECT
                    i.*,
                    ss.codigo_usuario_solicitante,
                    ss.codigo_usuario_proveedor,
                    ss.estado AS estado_solicitud,
                    p.titulo AS titulo_servicio
                FROM solicitud_servicio_incidencia i
                INNER JOIN solicitud_servicio ss ON ss.codigo_solicitud_servicio = i.codigo_solicitud_servicio
                INNER JOIN producto p ON p.codigo_producto = ss.codigo_producto
                WHERE i.codigo_incidencia = :incidencia
                  AND i.requiere_soporte = 1
                LIMIT 1 FOR UPDATE
            ");
            $st->bindValue(':incidencia', $codigoIncidencia, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'INCIDENCIA_NO_ENCONTRADA', 'mensaje' => 'No se encontró el caso de soporte.'];
            }
            if (in_array((string)$row['estado'], ['resuelta','cerrada','cancelada'], true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'CASO_CERRADO', 'mensaje' => 'El caso ya se encuentra cerrado.'];
            }

            $solicitud = (int)$row['codigo_solicitud_servicio'];
            $tituloServicio = (string)$row['titulo_servicio'];
            $estadoIncidencia = 'revision_soporte';
            $estadoSolicitud = 'revision_soporte';
            $resultado = $accion;
            $subcategoria = 'actualizacion_soporte';
            $tituloNotificacion = 'Soporte actualizó el caso';

            if ($accion === 'solicitar_informacion') {
                $estadoIncidencia = 'esperando_informacion';
                $estadoSolicitud = 'revision_soporte';
                $tituloNotificacion = 'Soporte solicita información';
            } elseif ($accion === 'reanudar_atencion') {
                $estadoIncidencia = 'en_atencion';
                $estadoSolicitud = 'incidencia_en_atencion';
                $tituloNotificacion = 'El caso vuelve a coordinación';
            } elseif ($accion === 'confirmar_completado') {
                $estadoIncidencia = 'resuelta';
                $estadoSolicitud = 'servicio_confirmado_solicitante';
                $subcategoria = 'servicio_completado_soporte';
                $tituloNotificacion = 'Servicio cerrado como completado';
            } elseif ($accion === 'cancelar_servicio') {
                $estadoIncidencia = 'cancelada';
                $estadoSolicitud = 'cancelada_soporte';
                $subcategoria = 'servicio_cancelado_soporte';
                $tituloNotificacion = 'Servicio cerrado por soporte';
            }

            $upI = $this->dblink->prepare("
                UPDATE solicitud_servicio_incidencia
                SET estado = :estado,
                    requiere_soporte = :requiere_soporte,
                    codigo_usuario_soporte = :soporte,
                    resultado_soporte = :resultado,
                    resolucion_soporte = :resolucion,
                    fecha_resolucion_soporte = CASE WHEN :cierra = 1 THEN NOW() ELSE fecha_resolucion_soporte END,
                    fecha_cierre = CASE WHEN :cierra2 = 1 THEN NOW() ELSE fecha_cierre END,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_incidencia = :incidencia
            ");
            $cierra = in_array($accion, ['confirmar_completado','cancelar_servicio'], true) ? 1 : 0;
            // Al devolver el caso a las partes, se cierra la intervención actual de soporte.
            // Si el problema continúa, cualquiera de las partes podrá escalarlo nuevamente.
            $requiereSoporte = $accion === 'reanudar_atencion' ? 0 : 1;
            $upI->bindValue(':estado', $estadoIncidencia, PDO::PARAM_STR);
            $upI->bindValue(':requiere_soporte', $requiereSoporte, PDO::PARAM_INT);
            $upI->bindValue(':soporte', $codigoSoporte, PDO::PARAM_INT);
            $upI->bindValue(':resultado', $resultado, PDO::PARAM_STR);
            $upI->bindValue(':resolucion', $comentario, PDO::PARAM_STR);
            $upI->bindValue(':cierra', $cierra, PDO::PARAM_INT);
            $upI->bindValue(':cierra2', $cierra, PDO::PARAM_INT);
            $upI->bindValue(':incidencia', $codigoIncidencia, PDO::PARAM_INT);
            $upI->execute();

            $upS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = :estado,
                    estado_anterior = :anterior,
                    motivo_estado = :motivo,
                    fecha_confirmacion_solicitante = CASE WHEN :completado = 1 THEN NOW() ELSE fecha_confirmacion_solicitante END,
                    fecha_cierre = CASE WHEN :cierra = 1 THEN NOW() ELSE fecha_cierre END,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $upS->bindValue(':estado', $estadoSolicitud, PDO::PARAM_STR);
            $upS->bindValue(':anterior', (string)$row['estado_solicitud'], PDO::PARAM_STR);
            $upS->bindValue(':motivo', 'Resolución de soporte: ' . mb_substr($comentario, 0, 450, 'UTF-8'), PDO::PARAM_STR);
            $upS->bindValue(':completado', $accion === 'confirmar_completado' ? 1 : 0, PDO::PARAM_INT);
            $upS->bindValue(':cierra', $cierra, PDO::PARAM_INT);
            $upS->bindValue(':solicitud', $solicitud, PDO::PARAM_INT);
            $upS->execute();

            if ($accion === 'confirmar_completado') {
                $this->crearCalificaciones($row);
            }

            $this->registrarInteraccion($solicitud, $codigoSoporte, 'resolucion_soporte_' . $accion, $comentario, [
                'codigo_incidencia' => $codigoIncidencia,
                'accion' => $accion,
                'estado_solicitud' => $estadoSolicitud,
            ]);

            $mensaje = $comentario . ' Servicio: “' . $tituloServicio . '”.';
            $this->notificar((int)$row['codigo_usuario_solicitante'], $solicitud, $subcategoria, $tituloNotificacion, $mensaje, '/mis-solicitudes-servicio-comprador', 'solicitante');
            $this->notificar((int)$row['codigo_usuario_proveedor'], $solicitud, $subcategoria, $tituloNotificacion, $mensaje, '/mis-solicitudes-servicio-vendedor', 'proveedor');

            if ($accion === 'confirmar_completado') {
                $this->notificar((int)$row['codigo_usuario_solicitante'], $solicitud, 'calificacion_habilitada', 'Califica tu experiencia', 'Ya puedes calificar al proveedor.', '/mis-solicitudes-servicio-comprador', 'solicitante');
                $this->notificar((int)$row['codigo_usuario_proveedor'], $solicitud, 'calificacion_habilitada', 'Califica tu experiencia', 'Ya puedes calificar al comprador.', '/mis-solicitudes-servicio-vendedor', 'proveedor');
            }

            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La resolución de soporte fue registrada.', 'estado' => $estadoSolicitud];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][ServicioSoporte][resolver] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_RESOLVER_INCIDENCIA', 'mensaje' => 'No se pudo registrar la resolución de soporte.'];
        }
    }
}
