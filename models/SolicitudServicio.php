<?php
// models/SolicitudServicio.php
// Punto 8 EV: flujo independiente de solicitudes de servicio.
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';

class SolicitudServicio extends Conexion
{
    public const HORAS_LIMITE_RESPUESTA = 24;

    private function obtenerResidenciaActivaUsuario(int $codigoUsuario): ?array
    {
        $sql = "
            SELECT
                codigo_usuario_residencia,
                tipo_conjunto,
                codigo_condominio,
                codigo_urbanizacion,
                direccion
            FROM usuario_residencia
            WHERE codigo_usuario = :codigo_usuario
            ORDER BY codigo_usuario_residencia DESC
            LIMIT 1
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoUsuario, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $tipo = strtolower(trim((string)($row['tipo_conjunto'] ?? '')));
        $condominio = (int)($row['codigo_condominio'] ?? 0);
        $urbanizacion = (int)($row['codigo_urbanizacion'] ?? 0);
        $codigoResidencia = (int)($row['codigo_usuario_residencia'] ?? 0);

        if ($codigoResidencia <= 0) {
            return null;
        }

        if ($tipo === 'condominio' && $condominio > 0) {
            return [
                'codigo_usuario_residencia' => $codigoResidencia,
                'tipo_conjunto' => 'condominio',
                'codigo_condominio' => $condominio,
                'codigo_urbanizacion' => null,
                'direccion' => (string)($row['direccion'] ?? ''),
            ];
        }

        if ($tipo === 'urbanizacion' && $urbanizacion > 0) {
            return [
                'codigo_usuario_residencia' => $codigoResidencia,
                'tipo_conjunto' => 'urbanizacion',
                'codigo_condominio' => null,
                'codigo_urbanizacion' => $urbanizacion,
                'direccion' => (string)($row['direccion'] ?? ''),
            ];
        }

        return null;
    }

    private function coincideResidenciaConPublicacion(array $residencia, array $servicio): bool
    {
        $tipoPublicacion = strtolower(trim((string)($servicio['tipo_conjunto_publicacion'] ?? '')));
        $tipoSolicitante = strtolower(trim((string)($residencia['tipo_conjunto'] ?? '')));

        if ($tipoPublicacion === 'condominio' && $tipoSolicitante === 'condominio') {
            return (int)($servicio['codigo_condominio_publicacion'] ?? 0) > 0
                && (int)($servicio['codigo_condominio_publicacion'] ?? 0) === (int)($residencia['codigo_condominio'] ?? 0);
        }

        if ($tipoPublicacion === 'urbanizacion' && $tipoSolicitante === 'urbanizacion') {
            return (int)($servicio['codigo_urbanizacion_publicacion'] ?? 0) > 0
                && (int)($servicio['codigo_urbanizacion_publicacion'] ?? 0) === (int)($residencia['codigo_urbanizacion'] ?? 0);
        }

        return false;
    }

    private function normalizarFechaDeseada($valor): ?string
    {
        $raw = trim((string)$valor);
        if ($raw === '') {
            return null;
        }

        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $raw, new DateTimeZone('America/Lima'));
        $errores = DateTimeImmutable::getLastErrors();
        $warnings = is_array($errores) ? (int)($errores['warning_count'] ?? 0) : 0;
        $errors = is_array($errores) ? (int)($errores['error_count'] ?? 0) : 0;

        if (!$fecha || $warnings > 0 || $errors > 0 || $fecha->format('Y-m-d') !== $raw) {
            throw new InvalidArgumentException('FECHA_DESEADA_INVALIDA');
        }

        $hoy = new DateTimeImmutable('today', new DateTimeZone('America/Lima'));
        if ($fecha < $hoy) {
            throw new InvalidArgumentException('FECHA_DESEADA_PASADA');
        }

        return $fecha->format('Y-m-d');
    }

    private function normalizarRangoHorario($valor): string
    {
        $valor = strtolower(trim((string)$valor));
        $permitidos = ['a_coordinar', 'manana', 'tarde', 'noche'];
        return in_array($valor, $permitidos, true) ? $valor : 'a_coordinar';
    }

    private function etiquetaRangoHorario(string $rango): string
    {
        return match ($rango) {
            'manana' => 'Mañana (8:00 a 12:00)',
            'tarde' => 'Tarde (12:00 a 18:00)',
            'noche' => 'Noche (18:00 a 21:00)',
            default => 'A coordinar',
        };
    }

    private function obtenerServicioParaSolicitar(int $codigoProducto, bool $forUpdate = false): ?array
    {
        $forUpdateSql = $forUpdate ? ' FOR UPDATE' : '';

        $sql = "
            SELECT
                p.codigo_producto,
                p.codigo_usuario AS codigo_usuario_proveedor,
                p.codigo_usuario_residencia AS codigo_usuario_residencia_proveedor,
                p.tipo_publicacion,
                p.titulo,
                p.precio,
                p.visible,
                p.tipo_conjunto_publicacion,
                p.codigo_condominio_publicacion,
                p.codigo_urbanizacion_publicacion,
                p.estado_residencial_publicacion,
                u.estado AS estado_usuario_proveedor
            FROM producto p
            INNER JOIN usuario u ON u.codigo_usuario = p.codigo_usuario
            WHERE p.codigo_producto = :codigo_producto
            LIMIT 1
            {$forUpdateSql}
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function sincronizarSolicitudesVencidasProveedor(int $codigoProveedor): void
    {
        if ($codigoProveedor <= 0) {
            return;
        }

        $sql = "
            UPDATE solicitud_servicio
            SET
                estado = 'sin_respuesta_proveedor',
                estado_anterior = 'pendiente_proveedor',
                motivo_estado = 'El proveedor no respondió dentro de las 24 horas esperadas.',
                fecha_cierre = NOW(),
                updated_at = CURRENT_TIMESTAMP
            WHERE codigo_usuario_proveedor = :codigo_usuario_proveedor
              AND estado = 'pendiente_proveedor'
              AND fecha_limite_respuesta IS NOT NULL
              AND fecha_limite_respuesta <= NOW()
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario_proveedor', $codigoProveedor, PDO::PARAM_INT);
        $st->execute();
    }

    private function existeSolicitudActivaDuplicada(int $codigoProducto, int $codigoSolicitante): bool
    {
        $sql = "
            SELECT codigo_solicitud_servicio
            FROM solicitud_servicio
            WHERE codigo_producto = :codigo_producto
              AND codigo_usuario_solicitante = :codigo_usuario_solicitante
              AND estado IN (
                    'pendiente_proveedor',
                    'informacion_adicional_solicitada',
                    'propuesta_enviada_solicitante',
                    'ajuste_solicitado',
                    'coordinacion_confirmada',
                    'servicio_realizado_proveedor'
              )
            ORDER BY codigo_solicitud_servicio DESC
            LIMIT 1
            FOR UPDATE
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_solicitante', $codigoSolicitante, PDO::PARAM_INT);
        $st->execute();

        return (bool)$st->fetchColumn();
    }

    private function registrarNotificacionNuevaSolicitud(array $solicitud): void
    {
        $codigoProveedor = (int)($solicitud['codigo_usuario_proveedor'] ?? 0);
        $codigoSolicitud = (int)($solicitud['codigo_solicitud_servicio'] ?? 0);

        if ($codigoProveedor <= 0 || $codigoSolicitud <= 0) {
            return;
        }

        $tituloServicio = trim((string)($solicitud['titulo_servicio'] ?? 'Servicio'));
        $fechaDeseada = trim((string)($solicitud['fecha_deseada'] ?? ''));
        $rango = $this->etiquetaRangoHorario((string)($solicitud['rango_horario'] ?? 'a_coordinar'));

        $mensaje = 'Un vecino solicitó coordinación para tu servicio “' . $tituloServicio . '”.';
        if ($fechaDeseada !== '') {
            $mensaje .= ' Fecha deseada: ' . $fechaDeseada . ' · ' . $rango . '.';
        }

        $payload = [
            'codigo_solicitud_servicio' => $codigoSolicitud,
            'codigo_producto' => (int)($solicitud['codigo_producto'] ?? 0),
            'titulo_servicio' => $tituloServicio,
            'rol_destino' => 'proveedor',
            'ruta' => '/mis-solicitudes-servicio-vendedor',
        ];

        $sql = "
            INSERT INTO notificacion
            (
                codigo_usuario,
                canal,
                categoria,
                subcategoria,
                referencia_id,
                titulo,
                mensaje,
                payload_json,
                estado
            )
            VALUES
            (
                :codigo_usuario,
                'app',
                'servicio',
                'nueva_solicitud',
                :referencia_id,
                :titulo,
                :mensaje,
                :payload_json,
                'no_leida'
            )
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoProveedor, PDO::PARAM_INT);
        $st->bindValue(':referencia_id', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':titulo', mb_substr('Nueva solicitud de servicio', 0, 180, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':mensaje', $mensaje, PDO::PARAM_STR);
        $st->bindValue(':payload_json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PDO::PARAM_STR);
        $st->execute();
    }

    /**
     * Registra una solicitud de coordinación para un servicio publicado.
     * Este flujo no usa pedido, cola, stock, billetera ni disponibilidad_pedidos.
     */
    public function registrarSolicitud(array $data): array
    {
        $codigoProducto = (int)($data['codigo_producto'] ?? 0);
        $codigoSolicitante = (int)($data['codigo_usuario_solicitante'] ?? 0);
        $direccionAtencion = trim((string)($data['direccion_atencion'] ?? ''));
        $mensajeSolicitante = trim((string)($data['mensaje_solicitante'] ?? ''));

        if ($codigoProducto <= 0 || $codigoSolicitante <= 0) {
            return [
                'ok' => false,
                'error' => 'PARAMETROS_INVALIDOS',
                'mensaje' => 'No se pudo identificar el servicio o el usuario solicitante.',
            ];
        }

        // La ubicación exacta se comparte dentro de la conversación cuando sea necesaria
        // para calcular movilidad o condiciones de atención. No es obligatoria al iniciar.
        if (mb_strlen($direccionAtencion, 'UTF-8') > 500) {
            return [
                'ok' => false,
                'error' => 'DIRECCION_DEMASIADO_LARGA',
                'mensaje' => 'El punto de atención no puede superar 500 caracteres.',
            ];
        }

        if (mb_strlen($mensajeSolicitante, 'UTF-8') < 8) {
            return [
                'ok' => false,
                'error' => 'MENSAJE_REQUERIDO',
                'mensaje' => 'Describe brevemente lo que necesitas para que el proveedor pueda responderte.',
            ];
        }

        if (mb_strlen($mensajeSolicitante, 'UTF-8') > 1500) {
            return [
                'ok' => false,
                'error' => 'MENSAJE_DEMASIADO_LARGO',
                'mensaje' => 'El detalle de la solicitud no puede superar 1500 caracteres.',
            ];
        }

        try {
            $fechaDeseada = $this->normalizarFechaDeseada($data['fecha_deseada'] ?? null);
        } catch (InvalidArgumentException $e) {
            return match ($e->getMessage()) {
                'FECHA_DESEADA_PASADA' => [
                    'ok' => false,
                    'error' => 'FECHA_DESEADA_PASADA',
                    'mensaje' => 'La fecha deseada no puede ser anterior a hoy.',
                ],
                default => [
                    'ok' => false,
                    'error' => 'FECHA_DESEADA_INVALIDA',
                    'mensaje' => 'La fecha deseada no tiene un formato válido.',
                ],
            };
        }

        $rangoHorario = $this->normalizarRangoHorario($data['rango_horario'] ?? 'a_coordinar');

        try {
            $this->dblink->beginTransaction();

            $residenciaSolicitante = $this->obtenerResidenciaActivaUsuario($codigoSolicitante);
            if (!$residenciaSolicitante) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'SIN_RESIDENCIA_ACTIVA',
                    'mensaje' => 'Debes tener una residencia activa para solicitar un servicio.',
                ];
            }

            $servicio = $this->obtenerServicioParaSolicitar($codigoProducto, true);
            if (!$servicio) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'SERVICIO_NO_ENCONTRADO',
                    'mensaje' => 'El servicio solicitado ya no existe.',
                ];
            }

            if (strtolower(trim((string)($servicio['tipo_publicacion'] ?? ''))) !== 'servicio') {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'PUBLICACION_NO_ES_SERVICIO',
                    'mensaje' => 'La publicación seleccionada no corresponde a un servicio.',
                ];
            }

            if ((int)($servicio['codigo_usuario_proveedor'] ?? 0) === $codigoSolicitante) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'SERVICIO_PROPIO',
                    'mensaje' => 'No puedes solicitar coordinación para tu propio servicio.',
                ];
            }

            if ((int)($servicio['visible'] ?? 0) !== 2 || (string)($servicio['estado_residencial_publicacion'] ?? '') !== 'activa') {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'SERVICIO_NO_DISPONIBLE',
                    'mensaje' => 'Este servicio ya no se encuentra disponible para solicitar.',
                ];
            }

            if ((int)($servicio['estado_usuario_proveedor'] ?? 0) !== 2) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'PROVEEDOR_NO_HABILITADO',
                    'mensaje' => 'El vecino que ofrece este servicio no está habilitado actualmente.',
                ];
            }

            if (!$this->coincideResidenciaConPublicacion($residenciaSolicitante, $servicio)) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'SERVICIO_FUERA_DE_RESIDENCIA',
                    'mensaje' => 'Solo puedes solicitar servicios publicados en tu mismo condominio o urbanización.',
                ];
            }

            $codigoProveedor = (int)($servicio['codigo_usuario_proveedor'] ?? 0);
            $this->sincronizarSolicitudesVencidasProveedor($codigoProveedor);

            if ($this->existeSolicitudActivaDuplicada($codigoProducto, $codigoSolicitante)) {
                $this->dblink->rollBack();
                return [
                    'ok' => false,
                    'error' => 'SOLICITUD_ACTIVA_EXISTENTE',
                    'mensaje' => 'Ya tienes una solicitud activa para este servicio. Espera la respuesta del proveedor o revisa la coordinación existente.',
                ];
            }

            $fechaLimite = (new DateTimeImmutable('now', new DateTimeZone('America/Lima')))
                ->modify('+' . self::HORAS_LIMITE_RESPUESTA . ' hours')
                ->format('Y-m-d H:i:s');

            $sql = "
                INSERT INTO solicitud_servicio
                (
                    codigo_producto,
                    codigo_usuario_solicitante,
                    codigo_usuario_proveedor,
                    codigo_usuario_residencia_solicitante,
                    codigo_usuario_residencia_proveedor,
                    precio_referencial,
                    fecha_deseada,
                    rango_horario,
                    direccion_atencion,
                    mensaje_solicitante,
                    estado,
                    fecha_limite_respuesta
                )
                VALUES
                (
                    :codigo_producto,
                    :codigo_usuario_solicitante,
                    :codigo_usuario_proveedor,
                    :codigo_usuario_residencia_solicitante,
                    :codigo_usuario_residencia_proveedor,
                    :precio_referencial,
                    :fecha_deseada,
                    :rango_horario,
                    :direccion_atencion,
                    :mensaje_solicitante,
                    'pendiente_proveedor',
                    :fecha_limite_respuesta
                )
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_producto', $codigoProducto, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_solicitante', $codigoSolicitante, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_proveedor', $codigoProveedor, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_residencia_solicitante', (int)$residenciaSolicitante['codigo_usuario_residencia'], PDO::PARAM_INT);

            $codigoResidenciaProveedor = (int)($servicio['codigo_usuario_residencia_proveedor'] ?? 0);
            if ($codigoResidenciaProveedor > 0) {
                $st->bindValue(':codigo_usuario_residencia_proveedor', $codigoResidenciaProveedor, PDO::PARAM_INT);
            } else {
                $st->bindValue(':codigo_usuario_residencia_proveedor', null, PDO::PARAM_NULL);
            }

            $st->bindValue(':precio_referencial', round((float)($servicio['precio'] ?? 0), 2));
            $st->bindValue(':fecha_deseada', $fechaDeseada, $fechaDeseada !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':rango_horario', $rangoHorario, PDO::PARAM_STR);
            if ($direccionAtencion !== '') {
                $st->bindValue(':direccion_atencion', $direccionAtencion, PDO::PARAM_STR);
            } else {
                $st->bindValue(':direccion_atencion', null, PDO::PARAM_NULL);
            }
            $st->bindValue(':mensaje_solicitante', $mensajeSolicitante, PDO::PARAM_STR);
            $st->bindValue(':fecha_limite_respuesta', $fechaLimite, PDO::PARAM_STR);
            $st->execute();

            $codigoSolicitud = (int)$this->dblink->lastInsertId();

            $solicitud = [
                'codigo_solicitud_servicio' => $codigoSolicitud,
                'codigo_producto' => $codigoProducto,
                'codigo_usuario_proveedor' => $codigoProveedor,
                'titulo_servicio' => (string)($servicio['titulo'] ?? 'Servicio'),
                'precio_referencial' => round((float)($servicio['precio'] ?? 0), 2),
                'fecha_deseada' => $fechaDeseada,
                'rango_horario' => $rangoHorario,
                'direccion_atencion' => $direccionAtencion,
                'estado' => 'pendiente_proveedor',
                'fecha_limite_respuesta' => $fechaLimite,
            ];

            $this->registrarNotificacionNuevaSolicitud($solicitud);
            $this->dblink->commit();

            return [
                'ok' => true,
                'data' => $solicitud,
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][SolicitudServicio][registrarSolicitud] ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'ERROR_REGISTRAR_SOLICITUD_SERVICIO',
                'mensaje' => 'No se pudo registrar la solicitud de servicio. Intenta nuevamente.',
            ];
        }
    }
    /* ==========================================================
       PUNTO 9 — PANEL DEL PROVEEDOR Y COORDINACIÓN ESTRUCTURADA
       - No reutiliza pedido, cola, stock ni billetera.
       - La confirmación mutua se implementa en el punto 10.
    ========================================================== */

    private function etiquetaEstadoServicio(string $estado): string
    {
        return match (trim($estado)) {
            'pendiente_proveedor' => 'Pendiente de respuesta',
            'informacion_adicional_solicitada' => 'Información solicitada',
            'propuesta_enviada_solicitante' => 'Propuesta enviada',
            'ajuste_solicitado' => 'Ajuste solicitado',
            'coordinacion_confirmada' => 'Coordinación confirmada',
            'servicio_realizado_proveedor' => 'Pendiente de confirmación del comprador',
            'servicio_confirmado_solicitante' => 'Servicio confirmado',
            'observacion_reportada' => 'Observación reportada',
            'cotizacion_final_enviada' => 'Cotización final enviada',
            'ajuste_cotizacion_solicitado' => 'Ajuste de cotización solicitado',
            'cotizacion_vencida' => 'Cotización vencida',
            'cotizacion_rechazada_solicitante' => 'Cotización rechazada',
            'rechazada_proveedor' => 'Rechazada por proveedor',
            'cancelada_solicitante' => 'Cancelada por solicitante',
            'cancelada_proveedor' => 'Cancelada por proveedor',
            'sin_respuesta_proveedor' => 'Sin respuesta del proveedor',
            default => 'Sin estado',
        };
    }

    private function etiquetaModalidad(string $modalidad): string
    {
        return match (trim($modalidad)) {
            'domicilio_solicitante' => 'Domicilio del solicitante',
            'domicilio_proveedor' => 'Domicilio o local del proveedor',
            'punto_encuentro' => 'Punto de encuentro',
            'remoto' => 'Atención remota o digital',
            'recojo_entrega' => 'Recojo o entrega',
            default => 'Por coordinar',
        };
    }

    private function etiquetaMomento(string $momento): string
    {
        return match (trim($momento)) {
            'fecha_hora' => 'Fecha y horario propuestos',
            'lo_antes_posible' => 'Lo antes posible',
            'fecha_limite' => 'Fecha límite propuesta',
            default => 'A coordinar',
        };
    }

    private function etiquetaTipoPrecio(string $tipo): string
    {
        return match (trim($tipo)) {
            'fijo' => 'Precio fijo',
            'por_hora' => 'Precio por hora',
            'por_sesion' => 'Precio por sesión',
            'por_unidad' => 'Precio por unidad',
            'sin_costo' => 'Sin costo',
            'pendiente_diagnostico' => 'Cotización después del diagnóstico',
            default => 'Cotización por coordinar',
        };
    }

    private function textoLimpio($valor, int $maximo): string
    {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return '';
        }

        return mb_substr($valor, 0, $maximo, 'UTF-8');
    }

    private function normalizarModalidad($valor): string
    {
        $valor = strtolower(trim((string)$valor));
        $permitidos = [
            'domicilio_solicitante',
            'domicilio_proveedor',
            'punto_encuentro',
            'remoto',
            'recojo_entrega',
            'a_coordinar',
        ];

        return in_array($valor, $permitidos, true) ? $valor : 'a_coordinar';
    }

    private function normalizarMomento($valor): string
    {
        $valor = strtolower(trim((string)$valor));
        $permitidos = ['fecha_hora', 'lo_antes_posible', 'fecha_limite', 'a_coordinar'];
        return in_array($valor, $permitidos, true) ? $valor : 'a_coordinar';
    }

    private function normalizarTipoPrecio($valor): string
    {
        $valor = strtolower(trim((string)$valor));
        $permitidos = [
            'fijo',
            'por_hora',
            'por_sesion',
            'por_unidad',
            'sin_costo',
            'pendiente_diagnostico',
            'a_cotizar',
        ];

        return in_array($valor, $permitidos, true) ? $valor : 'a_cotizar';
    }

    private function normalizarFechaPropuesta($valor): ?string
    {
        $raw = trim((string)$valor);
        if ($raw === '') {
            return null;
        }

        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $raw, new DateTimeZone('America/Lima'));
        $errores = DateTimeImmutable::getLastErrors();
        $warnings = is_array($errores) ? (int)($errores['warning_count'] ?? 0) : 0;
        $errors = is_array($errores) ? (int)($errores['error_count'] ?? 0) : 0;

        if (!$fecha || $warnings > 0 || $errors > 0 || $fecha->format('Y-m-d') !== $raw) {
            throw new InvalidArgumentException('FECHA_PROPUESTA_INVALIDA');
        }

        $hoy = new DateTimeImmutable('today', new DateTimeZone('America/Lima'));
        if ($fecha < $hoy) {
            throw new InvalidArgumentException('FECHA_PROPUESTA_PASADA');
        }

        return $fecha->format('Y-m-d');
    }

    private function normalizarPropuesta(array $data): array
    {
        $modalidad = $this->normalizarModalidad($data['modalidad'] ?? 'a_coordinar');
        $momento = $this->normalizarMomento($data['momento_tipo'] ?? 'a_coordinar');
        $tipoPrecio = $this->normalizarTipoPrecio($data['tipo_precio'] ?? 'a_cotizar');

        try {
            $fecha = $this->normalizarFechaPropuesta($data['fecha_propuesta'] ?? null);
        } catch (InvalidArgumentException $e) {
            throw $e;
        }

        if (in_array($momento, ['fecha_hora', 'fecha_limite'], true) && $fecha === null) {
            throw new InvalidArgumentException('FECHA_PROPUESTA_REQUERIDA');
        }

        $horario = $this->textoLimpio($data['horario_propuesto'] ?? '', 120);
        $alcance = $this->textoLimpio($data['alcance_confirmado'] ?? '', 1500);
        $duracion = $this->textoLimpio($data['duracion_estimada'] ?? '', 160);
        $requisitos = $this->textoLimpio($data['requisitos'] ?? '', 1500);
        $mensaje = $this->textoLimpio($data['mensaje_proveedor'] ?? '', 1500);
        $unidadPrecio = $this->textoLimpio($data['unidad_precio'] ?? '', 80);

        if (mb_strlen($alcance, 'UTF-8') < 8) {
            throw new InvalidArgumentException('ALCANCE_REQUERIDO');
        }

        if (mb_strlen($mensaje, 'UTF-8') < 8) {
            throw new InvalidArgumentException('MENSAJE_PROPUESTA_REQUERIDO');
        }

        $requiereMonto = in_array($tipoPrecio, ['fijo', 'por_hora', 'por_sesion', 'por_unidad'], true);
        $montoRaw = trim((string)($data['monto_propuesto'] ?? ''));
        $monto = null;

        if ($tipoPrecio === 'sin_costo') {
            $monto = 0.0;
        } elseif ($requiereMonto) {
            if ($montoRaw === '' || !is_numeric($montoRaw) || (float)$montoRaw <= 0) {
                throw new InvalidArgumentException('MONTO_PROPUESTA_REQUERIDO');
            }
            $monto = round((float)$montoRaw, 2);

            if ($monto > 999999.99) {
                throw new InvalidArgumentException('MONTO_PROPUESTA_INVALIDO');
            }
        }

        if (in_array($tipoPrecio, ['por_hora', 'por_sesion', 'por_unidad'], true) && $unidadPrecio === '') {
            $unidadPrecio = match ($tipoPrecio) {
                'por_hora' => 'por hora',
                'por_sesion' => 'por sesión',
                default => 'por unidad',
            };
        }

        return [
            'modalidad' => $modalidad,
            'momento_tipo' => $momento,
            'fecha_propuesta' => $fecha,
            'horario_propuesto' => $horario,
            'alcance_confirmado' => $alcance,
            'tipo_precio' => $tipoPrecio,
            'monto_propuesto' => $monto,
            'unidad_precio' => $unidadPrecio,
            'duracion_estimada' => $duracion,
            'requisitos' => $requisitos,
            'mensaje_proveedor' => $mensaje,
        ];
    }

    private function registrarInteraccion(
        int $codigoSolicitud,
        int $codigoUsuarioAutor,
        string $rolAutor,
        string $tipoInteraccion,
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
            (
                :codigo_solicitud_servicio,
                :codigo_usuario_autor,
                :rol_autor,
                :tipo_interaccion,
                :mensaje,
                :payload_json
            )
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_autor', $codigoUsuarioAutor, PDO::PARAM_INT);
        $st->bindValue(':rol_autor', $rolAutor, PDO::PARAM_STR);
        $st->bindValue(':tipo_interaccion', $tipoInteraccion, PDO::PARAM_STR);
        $st->bindValue(':mensaje', $mensaje, PDO::PARAM_STR);
        $st->bindValue(':payload_json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PDO::PARAM_STR);
        $st->execute();
    }

    private function registrarNotificacionSolicitante(
        array $solicitud,
        string $subcategoria,
        string $titulo,
        string $mensaje,
        array $payloadExtra = []
    ): void {
        $codigoSolicitante = (int)($solicitud['codigo_usuario_solicitante'] ?? 0);
        $codigoSolicitud = (int)($solicitud['codigo_solicitud_servicio'] ?? 0);

        if ($codigoSolicitante <= 0 || $codigoSolicitud <= 0) {
            return;
        }

        $payload = array_merge([
            'codigo_solicitud_servicio' => $codigoSolicitud,
            'codigo_producto' => (int)($solicitud['codigo_producto'] ?? 0),
            'titulo_servicio' => (string)($solicitud['titulo_servicio'] ?? 'Servicio'),
            'rol_destino' => 'solicitante',
            'ruta' => '/mis-solicitudes-servicio-comprador',
        ], $payloadExtra);

        $sql = "
            INSERT INTO notificacion
            (
                codigo_usuario,
                canal,
                categoria,
                subcategoria,
                referencia_id,
                titulo,
                mensaje,
                payload_json,
                estado
            )
            VALUES
            (
                :codigo_usuario,
                'app',
                'servicio',
                :subcategoria,
                :referencia_id,
                :titulo,
                :mensaje,
                :payload_json,
                'no_leida'
            )
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoSolicitante, PDO::PARAM_INT);
        $st->bindValue(':subcategoria', $subcategoria, PDO::PARAM_STR);
        $st->bindValue(':referencia_id', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':titulo', mb_substr($titulo, 0, 180, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':mensaje', $mensaje, PDO::PARAM_STR);
        $st->bindValue(':payload_json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PDO::PARAM_STR);
        $st->execute();
    }

    private function obtenerSolicitudProveedorBloqueada(int $codigoSolicitud, int $codigoProveedor): ?array
    {
        $sql = "
            SELECT
                ss.*,
                p.titulo AS titulo_servicio,
                p.imagen_portada,
                p.descripcion AS descripcion_servicio,
                p.codigo_tipo,
                p.codigo_categoria,
                c.nombre AS categoria_nombre,
                t.nombre AS tipo_nombre,
                us.nombre AS nombre_solicitante
            FROM solicitud_servicio ss
            INNER JOIN producto p
                ON p.codigo_producto = ss.codigo_producto
            INNER JOIN usuario us
                ON us.codigo_usuario = ss.codigo_usuario_solicitante
            LEFT JOIN categoria c
                ON c.codigo_categoria = p.codigo_categoria
            LEFT JOIN tipo t
                ON t.codigo_tipo = p.codigo_tipo
            WHERE ss.codigo_solicitud_servicio = :codigo_solicitud_servicio
              AND ss.codigo_usuario_proveedor = :codigo_usuario_proveedor
            LIMIT 1
            FOR UPDATE
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_proveedor', $codigoProveedor, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function estadoPermiteRespuestaProveedor(string $estado): bool
    {
        return in_array($estado, [
            'pendiente_proveedor',
            'informacion_adicional_solicitada',
            'ajuste_solicitado',
        ], true);
    }

    private function mapearSolicitudSalida(array $row): array
    {
        $estado = (string)($row['estado'] ?? '');
        $segundos = null;

        if ($estado === 'pendiente_proveedor' && !empty($row['fecha_limite_respuesta'])) {
            $limite = strtotime((string)$row['fecha_limite_respuesta']);
            if ($limite !== false) {
                $segundos = max(0, $limite - time());
            }
        }

        $propuesta = null;
        if (!empty($row['codigo_solicitud_servicio_propuesta'])) {
            $propuesta = [
                'codigo_solicitud_servicio_propuesta' => (int)$row['codigo_solicitud_servicio_propuesta'],
                'version' => (int)($row['version_propuesta'] ?? 0),
                'modalidad' => (string)($row['modalidad_propuesta'] ?? ''),
                'modalidad_texto' => $this->etiquetaModalidad((string)($row['modalidad_propuesta'] ?? '')),
                'momento_tipo' => (string)($row['momento_tipo'] ?? ''),
                'momento_texto' => $this->etiquetaMomento((string)($row['momento_tipo'] ?? '')),
                'fecha_propuesta' => $row['fecha_propuesta'] ?? null,
                'horario_propuesto' => (string)($row['horario_propuesto'] ?? ''),
                'alcance_confirmado' => (string)($row['alcance_confirmado'] ?? ''),
                'tipo_precio' => (string)($row['tipo_precio'] ?? ''),
                'tipo_precio_texto' => $this->etiquetaTipoPrecio((string)($row['tipo_precio'] ?? '')),
                'monto_propuesto' => $row['monto_propuesto'] !== null ? (string)$row['monto_propuesto'] : null,
                'unidad_precio' => (string)($row['unidad_precio'] ?? ''),
                'duracion_estimada' => (string)($row['duracion_estimada'] ?? ''),
                'requisitos' => (string)($row['requisitos'] ?? ''),
                'mensaje_proveedor' => (string)($row['mensaje_proveedor'] ?? ''),
                'created_at' => $row['propuesta_created_at'] ?? null,
            ];
        }

        return [
            'codigo_solicitud_servicio' => (int)($row['codigo_solicitud_servicio'] ?? 0),
            'codigo_producto' => (int)($row['codigo_producto'] ?? 0),
            'titulo_servicio' => (string)($row['titulo_servicio'] ?? 'Servicio'),
            'descripcion_servicio' => (string)($row['descripcion_servicio'] ?? ''),
            'imagen_portada' => (string)($row['imagen_portada'] ?? ''),
            'categoria_nombre' => (string)($row['categoria_nombre'] ?? ''),
            'tipo_nombre' => (string)($row['tipo_nombre'] ?? ''),
            'nombre_solicitante' => trim((string)($row['nombre_solicitante'] ?? '')) ?: 'Vecino',
            'nombre_proveedor' => trim((string)($row['nombre_proveedor'] ?? '')) ?: 'Vecino',
            'precio_referencial' => (string)($row['precio_referencial'] ?? '0.00'),
            'fecha_deseada' => $row['fecha_deseada'] ?? null,
            'rango_horario' => (string)($row['rango_horario'] ?? 'a_coordinar'),
            'rango_horario_texto' => $this->etiquetaRangoHorario((string)($row['rango_horario'] ?? 'a_coordinar')),
            // La dirección exacta no viaja a los paneles resumidos; solo se muestra
            // en la conversación privada a sus dos participantes.
            'direccion_atencion' => '',
            'ubicacion_compartida' => !empty($row['direccion_compartida_at']) ? 1 : 0,
            'mensaje_solicitante' => (string)($row['mensaje_solicitante'] ?? ''),
            'estado' => $estado,
            'estado_texto' => $this->etiquetaEstadoServicio($estado),
            'estado_anterior' => (string)($row['estado_anterior'] ?? ''),
            'motivo_estado' => (string)($row['motivo_estado'] ?? ''),
            'fecha_limite_respuesta' => $row['fecha_limite_respuesta'] ?? null,
            'segundos_restantes' => $segundos,
            'fecha_rechazo' => $row['fecha_rechazo'] ?? null,
            'fecha_cierre' => $row['fecha_cierre'] ?? null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'propuesta' => $propuesta,
        ];
    }

    public function listarSolicitudesProveedor(int $codigoProveedor): array
    {
        if ($codigoProveedor <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar al proveedor.'];
        }

        try {
            $this->sincronizarSolicitudesVencidasProveedor($codigoProveedor);
            $this->sincronizarCotizacionesVencidas(null, $codigoProveedor, 'proveedor');

            $sql = "
                SELECT
                    ss.*,
                    p.titulo AS titulo_servicio,
                    p.descripcion AS descripcion_servicio,
                    p.imagen_portada,
                    p.codigo_tipo,
                    p.codigo_categoria,
                    c.nombre AS categoria_nombre,
                    t.nombre AS tipo_nombre,
                    us.nombre AS nombre_solicitante,
                    pr.codigo_solicitud_servicio_propuesta,
                    pr.version AS version_propuesta,
                    pr.modalidad AS modalidad_propuesta,
                    pr.momento_tipo,
                    pr.fecha_propuesta,
                    pr.horario_propuesto,
                    pr.alcance_confirmado,
                    pr.tipo_precio,
                    pr.monto_propuesto,
                    pr.unidad_precio,
                    pr.duracion_estimada,
                    pr.requisitos,
                    pr.mensaje_proveedor,
                    pr.created_at AS propuesta_created_at
                FROM solicitud_servicio ss
                INNER JOIN producto p
                    ON p.codigo_producto = ss.codigo_producto
                INNER JOIN usuario us
                    ON us.codigo_usuario = ss.codigo_usuario_solicitante
                LEFT JOIN categoria c
                    ON c.codigo_categoria = p.codigo_categoria
                LEFT JOIN tipo t
                    ON t.codigo_tipo = p.codigo_tipo
                LEFT JOIN solicitud_servicio_propuesta pr
                    ON pr.codigo_solicitud_servicio = ss.codigo_solicitud_servicio
                   AND pr.estado IN ('vigente', 'aceptada', 'requiere_actualizacion')
                WHERE ss.codigo_usuario_proveedor = :codigo_usuario_proveedor
                ORDER BY
                    CASE ss.estado
                        WHEN 'pendiente_proveedor' THEN 1
                        WHEN 'ajuste_solicitado' THEN 2
                        WHEN 'informacion_adicional_solicitada' THEN 3
                        WHEN 'propuesta_enviada_solicitante' THEN 4
                        ELSE 5
                    END,
                    ss.created_at DESC,
                    ss.codigo_solicitud_servicio DESC
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario_proveedor', $codigoProveedor, PDO::PARAM_INT);
            $st->execute();

            $pendientes = [];
            $esperando = [];
            $cerradas = [];

            foreach (($st->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
                $item = $this->mapearSolicitudSalida($row);
                $estado = (string)$item['estado'];

                if (in_array($estado, ['pendiente_proveedor', 'ajuste_solicitado', 'ajuste_cotizacion_solicitado', 'cotizacion_vencida'], true)) {
                    $pendientes[] = $item;
                } elseif (in_array($estado, ['informacion_adicional_solicitada', 'propuesta_enviada_solicitante', 'cotizacion_final_enviada', 'coordinacion_confirmada', 'servicio_realizado_proveedor'], true)) {
                    $esperando[] = $item;
                } else {
                    $cerradas[] = $item;
                }
            }

            return [
                'ok' => true,
                'data' => [
                    'pendientes' => $pendientes,
                    'esperando' => $esperando,
                    'cerradas' => $cerradas,
                    'resumen' => [
                        'pendientes' => count($pendientes),
                        'esperando' => count($esperando),
                        'cerradas' => count($cerradas),
                    ],
                ],
            ];
        } catch (Throwable $e) {
            error_log('[EV][SolicitudServicio][listarSolicitudesProveedor] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_LISTAR_SOLICITUDES_SERVICIO', 'mensaje' => 'No se pudieron cargar las solicitudes de servicio.'];
        }
    }

    public function solicitarInformacion(int $codigoSolicitud, int $codigoProveedor, string $mensaje): array
    {
        $mensaje = $this->textoLimpio($mensaje, 1500);
        if ($codigoSolicitud <= 0 || $codigoProveedor <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }
        if (mb_strlen($mensaje, 'UTF-8') < 8) {
            return ['ok' => false, 'error' => 'MENSAJE_INFORMACION_REQUERIDO', 'mensaje' => 'Describe la información que necesitas para continuar.'];
        }

        try {
            $this->dblink->beginTransaction();
            $this->sincronizarSolicitudesVencidasProveedor($codigoProveedor);
            $solicitud = $this->obtenerSolicitudProveedorBloqueada($codigoSolicitud, $codigoProveedor);

            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $estadoActual = (string)($solicitud['estado'] ?? '');
            if (!$this->estadoPermiteRespuestaProveedor($estadoActual)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La solicitud ya no permite pedir información adicional.'];
            }

            $sql = "
                UPDATE solicitud_servicio
                SET
                    estado = 'informacion_adicional_solicitada',
                    estado_anterior = :estado_anterior,
                    motivo_estado = :motivo_estado,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio
                  AND codigo_usuario_proveedor = :codigo_usuario_proveedor
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':estado_anterior', $estadoActual, PDO::PARAM_STR);
            $st->bindValue(':motivo_estado', $mensaje, PDO::PARAM_STR);
            $st->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_proveedor', $codigoProveedor, PDO::PARAM_INT);
            $st->execute();

            $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoProveedor,
                'proveedor',
                'informacion_adicional_solicitada',
                $mensaje,
                ['estado_anterior' => $estadoActual]
            );

            $this->registrarNotificacionSolicitante(
                $solicitud,
                'informacion_adicional',
                'El proveedor necesita más información',
                'El proveedor solicitó información adicional para continuar con “' . (string)$solicitud['titulo_servicio'] . '”.',
                ['mensaje_proveedor' => $mensaje]
            );

            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La solicitud de información fue enviada al vecino.', 'estado' => 'informacion_adicional_solicitada'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][solicitarInformacion] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_SOLICITAR_INFORMACION', 'mensaje' => 'No se pudo enviar la solicitud de información.'];
        }
    }

    public function enviarPropuesta(int $codigoSolicitud, int $codigoProveedor, array $data): array
    {
        if ($codigoSolicitud <= 0 || $codigoProveedor <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }

        try {
            $propuesta = $this->normalizarPropuesta($data);
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
            $map = [
                'FECHA_PROPUESTA_INVALIDA' => 'La fecha propuesta no tiene un formato válido.',
                'FECHA_PROPUESTA_PASADA' => 'La fecha propuesta no puede ser anterior a hoy.',
                'FECHA_PROPUESTA_REQUERIDA' => 'Indica la fecha propuesta o la fecha límite.',
                'ALCANCE_REQUERIDO' => 'Describe el alcance confirmado del servicio.',
                'MENSAJE_PROPUESTA_REQUERIDO' => 'Escribe un mensaje claro para el solicitante.',
                'MONTO_PROPUESTA_REQUERIDO' => 'Indica el monto de la propuesta.',
                'MONTO_PROPUESTA_INVALIDO' => 'El monto de la propuesta no es válido.',
            ];
            return ['ok' => false, 'error' => $error, 'mensaje' => $map[$error] ?? 'Los datos de la propuesta no son válidos.'];
        }

        try {
            $this->dblink->beginTransaction();
            $this->sincronizarSolicitudesVencidasProveedor($codigoProveedor);
            $solicitud = $this->obtenerSolicitudProveedorBloqueada($codigoSolicitud, $codigoProveedor);

            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $estadoActual = (string)($solicitud['estado'] ?? '');
            if (!$this->estadoPermiteRespuestaProveedor($estadoActual)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La solicitud ya no permite enviar una propuesta.'];
            }

            $stVersion = $this->dblink->prepare("SELECT version FROM solicitud_servicio_propuesta WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio ORDER BY version DESC LIMIT 1 FOR UPDATE");
            $stVersion->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $stVersion->execute();
            $ultimaVersion = (int)$stVersion->fetchColumn();
            $version = max(1, $ultimaVersion + 1);

            $stCerrar = $this->dblink->prepare("UPDATE solicitud_servicio_propuesta SET estado = 'reemplazada', updated_at = CURRENT_TIMESTAMP WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio AND estado = 'vigente'");
            $stCerrar->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $stCerrar->execute();

            $sqlPropuesta = "
                INSERT INTO solicitud_servicio_propuesta
                (
                    codigo_solicitud_servicio,
                    codigo_usuario_proveedor,
                    version,
                    modalidad,
                    momento_tipo,
                    fecha_propuesta,
                    horario_propuesto,
                    alcance_confirmado,
                    tipo_precio,
                    monto_propuesto,
                    unidad_precio,
                    duracion_estimada,
                    requisitos,
                    mensaje_proveedor,
                    estado
                )
                VALUES
                (
                    :codigo_solicitud_servicio,
                    :codigo_usuario_proveedor,
                    :version,
                    :modalidad,
                    :momento_tipo,
                    :fecha_propuesta,
                    :horario_propuesto,
                    :alcance_confirmado,
                    :tipo_precio,
                    :monto_propuesto,
                    :unidad_precio,
                    :duracion_estimada,
                    :requisitos,
                    :mensaje_proveedor,
                    'vigente'
                )
            ";
            $st = $this->dblink->prepare($sqlPropuesta);
            $st->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_proveedor', $codigoProveedor, PDO::PARAM_INT);
            $st->bindValue(':version', $version, PDO::PARAM_INT);
            $st->bindValue(':modalidad', $propuesta['modalidad'], PDO::PARAM_STR);
            $st->bindValue(':momento_tipo', $propuesta['momento_tipo'], PDO::PARAM_STR);
            $st->bindValue(':fecha_propuesta', $propuesta['fecha_propuesta'], $propuesta['fecha_propuesta'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':horario_propuesto', $propuesta['horario_propuesto'] !== '' ? $propuesta['horario_propuesto'] : null, $propuesta['horario_propuesto'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':alcance_confirmado', $propuesta['alcance_confirmado'], PDO::PARAM_STR);
            $st->bindValue(':tipo_precio', $propuesta['tipo_precio'], PDO::PARAM_STR);
            $st->bindValue(':monto_propuesto', $propuesta['monto_propuesto'], $propuesta['monto_propuesto'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':unidad_precio', $propuesta['unidad_precio'] !== '' ? $propuesta['unidad_precio'] : null, $propuesta['unidad_precio'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':duracion_estimada', $propuesta['duracion_estimada'] !== '' ? $propuesta['duracion_estimada'] : null, $propuesta['duracion_estimada'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':requisitos', $propuesta['requisitos'] !== '' ? $propuesta['requisitos'] : null, $propuesta['requisitos'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $st->bindValue(':mensaje_proveedor', $propuesta['mensaje_proveedor'], PDO::PARAM_STR);
            $st->execute();

            $codigoPropuesta = (int)$this->dblink->lastInsertId();

            $sqlSolicitud = "
                UPDATE solicitud_servicio
                SET
                    estado = 'propuesta_enviada_solicitante',
                    estado_anterior = :estado_anterior,
                    motivo_estado = :motivo_estado,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio
                  AND codigo_usuario_proveedor = :codigo_usuario_proveedor
            ";
            $stSolicitud = $this->dblink->prepare($sqlSolicitud);
            $stSolicitud->bindValue(':estado_anterior', $estadoActual, PDO::PARAM_STR);
            $stSolicitud->bindValue(':motivo_estado', 'El proveedor envió una propuesta de coordinación.', PDO::PARAM_STR);
            $stSolicitud->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $stSolicitud->bindValue(':codigo_usuario_proveedor', $codigoProveedor, PDO::PARAM_INT);
            $stSolicitud->execute();

            $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoProveedor,
                'proveedor',
                'propuesta_enviada',
                $propuesta['mensaje_proveedor'],
                array_merge($propuesta, ['codigo_solicitud_servicio_propuesta' => $codigoPropuesta, 'version' => $version])
            );

            $precioMensaje = $this->etiquetaTipoPrecio($propuesta['tipo_precio']);
            if ($propuesta['monto_propuesto'] !== null && (float)$propuesta['monto_propuesto'] > 0) {
                $precioMensaje .= ': S/ ' . number_format((float)$propuesta['monto_propuesto'], 2, '.', '');
                if ($propuesta['unidad_precio'] !== '') $precioMensaje .= ' ' . $propuesta['unidad_precio'];
            }

            $this->registrarNotificacionSolicitante(
                $solicitud,
                'propuesta_coordinacion',
                'Tienes una propuesta de coordinación',
                'El proveedor envió una propuesta para “' . (string)$solicitud['titulo_servicio'] . '”. ' . $precioMensaje . '.',
                ['codigo_solicitud_servicio_propuesta' => $codigoPropuesta]
            );

            $this->dblink->commit();
            return [
                'ok' => true,
                'mensaje' => 'La propuesta fue enviada al solicitante.',
                'data' => [
                    'codigo_solicitud_servicio_propuesta' => $codigoPropuesta,
                    'estado' => 'propuesta_enviada_solicitante',
                ],
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][enviarPropuesta] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_ENVIAR_PROPUESTA', 'mensaje' => 'No se pudo enviar la propuesta de coordinación.'];
        }
    }

    public function rechazarSolicitud(int $codigoSolicitud, int $codigoProveedor, string $motivo): array
    {
        $motivo = $this->textoLimpio($motivo, 500);
        if ($codigoSolicitud <= 0 || $codigoProveedor <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }
        if (mb_strlen($motivo, 'UTF-8') < 5) {
            return ['ok' => false, 'error' => 'MOTIVO_RECHAZO_REQUERIDO', 'mensaje' => 'Indica un motivo de rechazo claro y cordial.'];
        }

        try {
            $this->dblink->beginTransaction();
            $this->sincronizarSolicitudesVencidasProveedor($codigoProveedor);
            $solicitud = $this->obtenerSolicitudProveedorBloqueada($codigoSolicitud, $codigoProveedor);

            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $estadoActual = (string)($solicitud['estado'] ?? '');
            if (!$this->estadoPermiteRespuestaProveedor($estadoActual)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La solicitud ya no permite registrar un rechazo.'];
            }

            $sql = "
                UPDATE solicitud_servicio
                SET
                    estado = 'rechazada_proveedor',
                    estado_anterior = :estado_anterior,
                    motivo_estado = :motivo_estado,
                    fecha_rechazo = NOW(),
                    fecha_cierre = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio
                  AND codigo_usuario_proveedor = :codigo_usuario_proveedor
            ";
            $st = $this->dblink->prepare($sql);
            $st->bindValue(':estado_anterior', $estadoActual, PDO::PARAM_STR);
            $st->bindValue(':motivo_estado', $motivo, PDO::PARAM_STR);
            $st->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_proveedor', $codigoProveedor, PDO::PARAM_INT);
            $st->execute();

            $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoProveedor,
                'proveedor',
                'rechazo_proveedor',
                $motivo,
                ['estado_anterior' => $estadoActual]
            );

            $this->registrarNotificacionSolicitante(
                $solicitud,
                'solicitud_rechazada',
                'El proveedor no podrá atender la solicitud',
                'El proveedor respondió que no podrá atender “' . (string)$solicitud['titulo_servicio'] . '”.',
                ['motivo_rechazo' => $motivo]
            );

            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La solicitud fue rechazada y el vecino fue notificado.', 'estado' => 'rechazada_proveedor'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][rechazarSolicitud] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_RECHAZAR_SOLICITUD', 'mensaje' => 'No se pudo registrar el rechazo de la solicitud.'];
        }
    }



    /* ==========================================================
       PUNTO 10 — VISTA DEL SOLICITANTE / COMPRADOR
       - Permite responder información, aceptar propuesta,
         solicitar ajustes y cancelar antes de la confirmación.
       - Mantiene el flujo de servicios separado de pedidos.
    ========================================================== */

    private function sincronizarSolicitudesVencidasSolicitante(int $codigoSolicitante): void
    {
        if ($codigoSolicitante <= 0) {
            return;
        }

        $sql = "
            UPDATE solicitud_servicio
            SET
                estado = 'sin_respuesta_proveedor',
                estado_anterior = 'pendiente_proveedor',
                motivo_estado = 'El proveedor no respondió dentro de las 24 horas esperadas.',
                fecha_cierre = NOW(),
                updated_at = CURRENT_TIMESTAMP
            WHERE codigo_usuario_solicitante = :codigo_usuario_solicitante
              AND estado = 'pendiente_proveedor'
              AND fecha_limite_respuesta IS NOT NULL
              AND fecha_limite_respuesta <= NOW()
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario_solicitante', $codigoSolicitante, PDO::PARAM_INT);
        $st->execute();
    }

    private function registrarNotificacionProveedor(
        array $solicitud,
        string $subcategoria,
        string $titulo,
        string $mensaje,
        array $payloadExtra = []
    ): void {
        $codigoProveedor = (int)($solicitud['codigo_usuario_proveedor'] ?? 0);
        $codigoSolicitud = (int)($solicitud['codigo_solicitud_servicio'] ?? 0);

        if ($codigoProveedor <= 0 || $codigoSolicitud <= 0) {
            return;
        }

        $payload = array_merge([
            'codigo_solicitud_servicio' => $codigoSolicitud,
            'codigo_producto' => (int)($solicitud['codigo_producto'] ?? 0),
            'titulo_servicio' => (string)($solicitud['titulo_servicio'] ?? 'Servicio'),
            'rol_destino' => 'proveedor',
            'ruta' => '/mis-solicitudes-servicio-vendedor',
        ], $payloadExtra);

        $sql = "
            INSERT INTO notificacion
            (
                codigo_usuario,
                canal,
                categoria,
                subcategoria,
                referencia_id,
                titulo,
                mensaje,
                payload_json,
                estado
            )
            VALUES
            (
                :codigo_usuario,
                'app',
                'servicio',
                :subcategoria,
                :referencia_id,
                :titulo,
                :mensaje,
                :payload_json,
                'no_leida'
            )
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_usuario', $codigoProveedor, PDO::PARAM_INT);
        $st->bindValue(':subcategoria', $subcategoria, PDO::PARAM_STR);
        $st->bindValue(':referencia_id', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':titulo', mb_substr($titulo, 0, 180, 'UTF-8'), PDO::PARAM_STR);
        $st->bindValue(':mensaje', $mensaje, PDO::PARAM_STR);
        $st->bindValue(':payload_json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PDO::PARAM_STR);
        $st->execute();
    }

    private function obtenerSolicitudSolicitanteBloqueada(int $codigoSolicitud, int $codigoSolicitante): ?array
    {
        $sql = "
            SELECT
                ss.*,
                p.titulo AS titulo_servicio,
                p.imagen_portada,
                p.descripcion AS descripcion_servicio,
                p.codigo_tipo,
                p.codigo_categoria,
                c.nombre AS categoria_nombre,
                t.nombre AS tipo_nombre,
                up.nombre AS nombre_proveedor
            FROM solicitud_servicio ss
            INNER JOIN producto p
                ON p.codigo_producto = ss.codigo_producto
            INNER JOIN usuario up
                ON up.codigo_usuario = ss.codigo_usuario_proveedor
            LEFT JOIN categoria c
                ON c.codigo_categoria = p.codigo_categoria
            LEFT JOIN tipo t
                ON t.codigo_tipo = p.codigo_tipo
            WHERE ss.codigo_solicitud_servicio = :codigo_solicitud_servicio
              AND ss.codigo_usuario_solicitante = :codigo_usuario_solicitante
            LIMIT 1
            FOR UPDATE
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
        $st->bindValue(':codigo_usuario_solicitante', $codigoSolicitante, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function obtenerPropuestaVigenteBloqueada(int $codigoSolicitud): ?array
    {
        $sql = "
            SELECT *
            FROM solicitud_servicio_propuesta
            WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio
              AND estado = 'vigente'
            ORDER BY version DESC, codigo_solicitud_servicio_propuesta DESC
            LIMIT 1
            FOR UPDATE
        ";

        $st = $this->dblink->prepare($sql);
        $st->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function estadoPermiteRespuestaSolicitante(string $estado): bool
    {
        return $estado === 'informacion_adicional_solicitada';
    }

    private function estadoPermiteAceptarPropuestaSolicitante(string $estado): bool
    {
        return $estado === 'propuesta_enviada_solicitante';
    }

    private function estadoPermiteCancelarSolicitante(string $estado): bool
    {
        return in_array($estado, [
            'pendiente_proveedor',
            'informacion_adicional_solicitada',
            'propuesta_enviada_solicitante',
            'ajuste_solicitado',
        ], true);
    }

    public function listarSolicitudesSolicitante(int $codigoSolicitante): array
    {
        if ($codigoSolicitante <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar al solicitante.'];
        }

        try {
            $this->sincronizarSolicitudesVencidasSolicitante($codigoSolicitante);
            $this->sincronizarCotizacionesVencidas(null, $codigoSolicitante, 'solicitante');

            $sql = "
                SELECT
                    ss.*,
                    p.titulo AS titulo_servicio,
                    p.descripcion AS descripcion_servicio,
                    p.imagen_portada,
                    p.codigo_tipo,
                    p.codigo_categoria,
                    c.nombre AS categoria_nombre,
                    t.nombre AS tipo_nombre,
                    up.nombre AS nombre_proveedor,
                    pr.codigo_solicitud_servicio_propuesta,
                    pr.version AS version_propuesta,
                    pr.modalidad AS modalidad_propuesta,
                    pr.momento_tipo,
                    pr.fecha_propuesta,
                    pr.horario_propuesto,
                    pr.alcance_confirmado,
                    pr.tipo_precio,
                    pr.monto_propuesto,
                    pr.unidad_precio,
                    pr.duracion_estimada,
                    pr.requisitos,
                    pr.mensaje_proveedor,
                    pr.created_at AS propuesta_created_at
                FROM solicitud_servicio ss
                INNER JOIN producto p
                    ON p.codigo_producto = ss.codigo_producto
                INNER JOIN usuario up
                    ON up.codigo_usuario = ss.codigo_usuario_proveedor
                LEFT JOIN categoria c
                    ON c.codigo_categoria = p.codigo_categoria
                LEFT JOIN tipo t
                    ON t.codigo_tipo = p.codigo_tipo
                LEFT JOIN solicitud_servicio_propuesta pr
                    ON pr.codigo_solicitud_servicio = ss.codigo_solicitud_servicio
                   AND pr.estado IN ('vigente', 'aceptada', 'requiere_actualizacion')
                WHERE ss.codigo_usuario_solicitante = :codigo_usuario_solicitante
                ORDER BY
                    CASE ss.estado
                        WHEN 'informacion_adicional_solicitada' THEN 1
                        WHEN 'propuesta_enviada_solicitante' THEN 2
                        WHEN 'pendiente_proveedor' THEN 3
                        WHEN 'ajuste_solicitado' THEN 4
                        WHEN 'coordinacion_confirmada' THEN 5
                        ELSE 6
                    END,
                    ss.updated_at DESC,
                    ss.created_at DESC,
                    ss.codigo_solicitud_servicio DESC
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':codigo_usuario_solicitante', $codigoSolicitante, PDO::PARAM_INT);
            $st->execute();

            $porResponder = [];
            $enCoordinacion = [];
            $cerradas = [];

            foreach (($st->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
                $item = $this->mapearSolicitudSalida($row);
                $estado = (string)$item['estado'];

                if (in_array($estado, ['informacion_adicional_solicitada', 'propuesta_enviada_solicitante', 'cotizacion_final_enviada', 'servicio_realizado_proveedor'], true)) {
                    $porResponder[] = $item;
                } elseif (in_array($estado, [
                    'pendiente_proveedor',
                    'ajuste_solicitado',
                    'ajuste_cotizacion_solicitado',
                    'cotizacion_vencida',
                    'coordinacion_confirmada',
                ], true)) {
                    $enCoordinacion[] = $item;
                } else {
                    $cerradas[] = $item;
                }
            }

            return [
                'ok' => true,
                'data' => [
                    'por_responder' => $porResponder,
                    'en_coordinacion' => $enCoordinacion,
                    'cerradas' => $cerradas,
                    'resumen' => [
                        'por_responder' => count($porResponder),
                        'en_coordinacion' => count($enCoordinacion),
                        'cerradas' => count($cerradas),
                    ],
                ],
            ];
        } catch (Throwable $e) {
            error_log('[EV][SolicitudServicio][listarSolicitudesSolicitante] ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'ERROR_LISTAR_SOLICITUDES_SERVICIO',
                'mensaje' => 'No se pudieron cargar tus solicitudes de servicio.',
            ];
        }
    }

    public function responderInformacionSolicitante(int $codigoSolicitud, int $codigoSolicitante, string $mensaje): array
    {
        $mensaje = $this->textoLimpio($mensaje, 1500);

        if ($codigoSolicitud <= 0 || $codigoSolicitante <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }

        if (mb_strlen($mensaje, 'UTF-8') < 8) {
            return [
                'ok' => false,
                'error' => 'MENSAJE_RESPUESTA_REQUERIDO',
                'mensaje' => 'Describe la información solicitada para que el proveedor pueda continuar.',
            ];
        }

        try {
            $this->dblink->beginTransaction();
            $this->sincronizarSolicitudesVencidasSolicitante($codigoSolicitante);

            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $estadoActual = (string)($solicitud['estado'] ?? '');
            if (!$this->estadoPermiteRespuestaSolicitante($estadoActual)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La solicitud ya no permite responder información adicional.'];
            }

            $detalleOriginal = trim((string)($solicitud['mensaje_solicitante'] ?? ''));
            $detalleActualizado = $detalleOriginal === ''
                ? $mensaje
                : $detalleOriginal . "\n\nInformación adicional enviada por el solicitante:\n" . $mensaje;

            $sql = "
                UPDATE solicitud_servicio
                SET
                    mensaje_solicitante = :mensaje_solicitante,
                    estado = 'pendiente_proveedor',
                    estado_anterior = :estado_anterior,
                    motivo_estado = 'El solicitante respondió la información solicitada.',
                    fecha_limite_respuesta = DATE_ADD(NOW(), INTERVAL 24 HOUR),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio
                  AND codigo_usuario_solicitante = :codigo_usuario_solicitante
            ";

            $st = $this->dblink->prepare($sql);
            $st->bindValue(':mensaje_solicitante', $detalleActualizado, PDO::PARAM_STR);
            $st->bindValue(':estado_anterior', $estadoActual, PDO::PARAM_STR);
            $st->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $st->bindValue(':codigo_usuario_solicitante', $codigoSolicitante, PDO::PARAM_INT);
            $st->execute();

            $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoSolicitante,
                'solicitante',
                'informacion_adicional_respondida',
                $mensaje,
                ['estado_anterior' => $estadoActual]
            );

            $this->registrarNotificacionProveedor(
                $solicitud,
                'informacion_respondida',
                'El vecino respondió la información solicitada',
                'El solicitante envió información adicional para “' . (string)$solicitud['titulo_servicio'] . '”.'
            );

            $this->dblink->commit();

            return [
                'ok' => true,
                'mensaje' => 'Tu información fue enviada al proveedor.',
                'estado' => 'pendiente_proveedor',
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][SolicitudServicio][responderInformacionSolicitante] ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'ERROR_RESPONDER_INFORMACION',
                'mensaje' => 'No se pudo enviar la información adicional.',
            ];
        }
    }

    public function aceptarPropuestaSolicitante(int $codigoSolicitud, int $codigoSolicitante): array
    {
        if ($codigoSolicitud <= 0 || $codigoSolicitante <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }

        try {
            $this->dblink->beginTransaction();
            $this->sincronizarSolicitudesVencidasSolicitante($codigoSolicitante);

            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $estadoActual = (string)($solicitud['estado'] ?? '');
            if (!$this->estadoPermiteAceptarPropuestaSolicitante($estadoActual)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La solicitud ya no permite aceptar una propuesta.'];
            }

            $propuesta = $this->obtenerPropuestaVigenteBloqueada($codigoSolicitud);
            if (!$propuesta) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'PROPUESTA_NO_ENCONTRADA', 'mensaje' => 'No se encontró una propuesta vigente para aceptar.'];
            }

            $sqlPropuesta = "
                UPDATE solicitud_servicio_propuesta
                SET
                    estado = 'aceptada',
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio_propuesta = :codigo_solicitud_servicio_propuesta
                  AND estado = 'vigente'
            ";
            $stPropuesta = $this->dblink->prepare($sqlPropuesta);
            $stPropuesta->bindValue(':codigo_solicitud_servicio_propuesta', (int)$propuesta['codigo_solicitud_servicio_propuesta'], PDO::PARAM_INT);
            $stPropuesta->execute();

            $sqlSolicitud = "
                UPDATE solicitud_servicio
                SET
                    estado = 'coordinacion_confirmada',
                    estado_anterior = :estado_anterior,
                    motivo_estado = 'El solicitante aceptó la propuesta de coordinación.',
                    fecha_aceptacion = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio
                  AND codigo_usuario_solicitante = :codigo_usuario_solicitante
            ";
            $stSolicitud = $this->dblink->prepare($sqlSolicitud);
            $stSolicitud->bindValue(':estado_anterior', $estadoActual, PDO::PARAM_STR);
            $stSolicitud->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $stSolicitud->bindValue(':codigo_usuario_solicitante', $codigoSolicitante, PDO::PARAM_INT);
            $stSolicitud->execute();

            $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoSolicitante,
                'solicitante',
                'propuesta_aceptada',
                'El solicitante aceptó la propuesta de coordinación.',
                [
                    'codigo_solicitud_servicio_propuesta' => (int)$propuesta['codigo_solicitud_servicio_propuesta'],
                    'version' => (int)($propuesta['version'] ?? 1),
                ]
            );

            $this->registrarNotificacionProveedor(
                $solicitud,
                'propuesta_aceptada',
                'Tu propuesta fue aceptada',
                'El solicitante aceptó tu propuesta para “' . (string)$solicitud['titulo_servicio'] . '”.',
                [
                    'codigo_solicitud_servicio_propuesta' => (int)$propuesta['codigo_solicitud_servicio_propuesta'],
                ]
            );

            $this->dblink->commit();

            return [
                'ok' => true,
                'mensaje' => 'La propuesta fue aceptada y la coordinación quedó confirmada.',
                'estado' => 'coordinacion_confirmada',
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][SolicitudServicio][aceptarPropuestaSolicitante] ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'ERROR_ACEPTAR_PROPUESTA',
                'mensaje' => 'No se pudo aceptar la propuesta de coordinación.',
            ];
        }
    }

    public function solicitarAjusteSolicitante(int $codigoSolicitud, int $codigoSolicitante, string $mensaje): array
    {
        $mensaje = $this->textoLimpio($mensaje, 1500);

        if ($codigoSolicitud <= 0 || $codigoSolicitante <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }

        if (mb_strlen($mensaje, 'UTF-8') < 8) {
            return [
                'ok' => false,
                'error' => 'MENSAJE_AJUSTE_REQUERIDO',
                'mensaje' => 'Explica qué condición necesitas ajustar en la propuesta.',
            ];
        }

        try {
            $this->dblink->beginTransaction();
            $this->sincronizarSolicitudesVencidasSolicitante($codigoSolicitante);

            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $estadoActual = (string)($solicitud['estado'] ?? '');
            if (!$this->estadoPermiteAceptarPropuestaSolicitante($estadoActual)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La solicitud ya no permite pedir un ajuste.'];
            }

            $propuesta = $this->obtenerPropuestaVigenteBloqueada($codigoSolicitud);
            if (!$propuesta) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'PROPUESTA_NO_ENCONTRADA', 'mensaje' => 'No se encontró una propuesta vigente para ajustar.'];
            }

            $sqlPropuesta = "
                UPDATE solicitud_servicio_propuesta
                SET
                    estado = 'reemplazada',
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio_propuesta = :codigo_solicitud_servicio_propuesta
                  AND estado = 'vigente'
            ";
            $stPropuesta = $this->dblink->prepare($sqlPropuesta);
            $stPropuesta->bindValue(':codigo_solicitud_servicio_propuesta', (int)$propuesta['codigo_solicitud_servicio_propuesta'], PDO::PARAM_INT);
            $stPropuesta->execute();

            $sqlSolicitud = "
                UPDATE solicitud_servicio
                SET
                    estado = 'ajuste_solicitado',
                    estado_anterior = :estado_anterior,
                    motivo_estado = :motivo_estado,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio
                  AND codigo_usuario_solicitante = :codigo_usuario_solicitante
            ";
            $stSolicitud = $this->dblink->prepare($sqlSolicitud);
            $stSolicitud->bindValue(':estado_anterior', $estadoActual, PDO::PARAM_STR);
            $stSolicitud->bindValue(':motivo_estado', $mensaje, PDO::PARAM_STR);
            $stSolicitud->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $stSolicitud->bindValue(':codigo_usuario_solicitante', $codigoSolicitante, PDO::PARAM_INT);
            $stSolicitud->execute();

            $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoSolicitante,
                'solicitante',
                'ajuste_solicitado',
                $mensaje,
                [
                    'codigo_solicitud_servicio_propuesta' => (int)$propuesta['codigo_solicitud_servicio_propuesta'],
                    'version' => (int)($propuesta['version'] ?? 1),
                ]
            );

            $this->registrarNotificacionProveedor(
                $solicitud,
                'ajuste_solicitado',
                'El vecino solicitó un ajuste',
                'El solicitante pidió ajustar la propuesta para “' . (string)$solicitud['titulo_servicio'] . '”.'
            );

            $this->dblink->commit();

            return [
                'ok' => true,
                'mensaje' => 'Tu solicitud de ajuste fue enviada al proveedor.',
                'estado' => 'ajuste_solicitado',
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][SolicitudServicio][solicitarAjusteSolicitante] ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'ERROR_SOLICITAR_AJUSTE',
                'mensaje' => 'No se pudo enviar la solicitud de ajuste.',
            ];
        }
    }

    public function cancelarSolicitudSolicitante(int $codigoSolicitud, int $codigoSolicitante, string $motivo): array
    {
        $motivo = $this->textoLimpio($motivo, 500);

        if ($codigoSolicitud <= 0 || $codigoSolicitante <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }

        if (mb_strlen($motivo, 'UTF-8') < 5) {
            return [
                'ok' => false,
                'error' => 'MOTIVO_CANCELACION_REQUERIDO',
                'mensaje' => 'Indica un motivo de cancelación claro y breve.',
            ];
        }

        try {
            $this->dblink->beginTransaction();
            $this->sincronizarSolicitudesVencidasSolicitante($codigoSolicitante);

            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $estadoActual = (string)($solicitud['estado'] ?? '');
            if (!$this->estadoPermiteCancelarSolicitante($estadoActual)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'Esta solicitud ya no permite cancelación desde esta etapa.'];
            }

            $sqlPropuesta = "
                UPDATE solicitud_servicio_propuesta
                SET
                    estado = 'cancelada_solicitante',
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio
                  AND estado = 'vigente'
            ";
            $stPropuesta = $this->dblink->prepare($sqlPropuesta);
            $stPropuesta->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $stPropuesta->execute();

            $sqlSolicitud = "
                UPDATE solicitud_servicio
                SET
                    estado = 'cancelada_solicitante',
                    estado_anterior = :estado_anterior,
                    motivo_estado = :motivo_estado,
                    fecha_cancelacion = NOW(),
                    fecha_cierre = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :codigo_solicitud_servicio
                  AND codigo_usuario_solicitante = :codigo_usuario_solicitante
            ";
            $stSolicitud = $this->dblink->prepare($sqlSolicitud);
            $stSolicitud->bindValue(':estado_anterior', $estadoActual, PDO::PARAM_STR);
            $stSolicitud->bindValue(':motivo_estado', $motivo, PDO::PARAM_STR);
            $stSolicitud->bindValue(':codigo_solicitud_servicio', $codigoSolicitud, PDO::PARAM_INT);
            $stSolicitud->bindValue(':codigo_usuario_solicitante', $codigoSolicitante, PDO::PARAM_INT);
            $stSolicitud->execute();

            $this->registrarInteraccion(
                $codigoSolicitud,
                $codigoSolicitante,
                'solicitante',
                'solicitud_cancelada',
                $motivo,
                ['estado_anterior' => $estadoActual]
            );

            $this->registrarNotificacionProveedor(
                $solicitud,
                'solicitud_cancelada',
                'El vecino canceló la solicitud',
                'El solicitante canceló la coordinación para “' . (string)$solicitud['titulo_servicio'] . '”.',
                ['motivo_cancelacion' => $motivo]
            );

            $this->dblink->commit();

            return [
                'ok' => true,
                'mensaje' => 'La solicitud de servicio fue cancelada.',
                'estado' => 'cancelada_solicitante',
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) {
                $this->dblink->rollBack();
            }

            error_log('[EV][SolicitudServicio][cancelarSolicitudSolicitante] ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => 'ERROR_CANCELAR_SOLICITUD',
                'mensaje' => 'No se pudo cancelar la solicitud de servicio.',
            ];
        }
    }



    /* ==========================================================
       PUNTO 10 — FLUJO FINAL DE COTIZACIÓN DE SERVICIOS
       La negociación ocurre dentro de EV. Las cotizaciones son
       documentos formales, versionados y vigentes por 72 horas.
    ========================================================== */

    private function normalizarCondicionPagoFinal($valor): string
    {
        $valor = strtolower(trim((string)$valor));
        $permitidos = ['contra_entrega', 'adelanto_acordado'];

        if (!in_array($valor, $permitidos, true)) {
            throw new InvalidArgumentException('CONDICION_PAGO_REQUERIDA');
        }

        return $valor;
    }

    /**
     * La hora es opcional porque no todos los servicios se prestan por franja
     * horaria. Cuando se informa, se guarda con precisión HH:MM.
     */
    private function normalizarHoraCotizacion($valor, string $error): ?string
    {
        $hora = trim((string)$valor);
        if ($hora === '') {
            return null;
        }

        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $hora)) {
            throw new InvalidArgumentException($error);
        }

        return $hora . ':00';
    }

    private function normalizarMontoAdelanto($valor, string $condicionPago, float $montoTotal): ?float
    {
        if ($condicionPago !== 'adelanto_acordado') {
            return null;
        }

        $raw = trim((string)$valor);
        if ($raw === '' || !is_numeric($raw) || (float)$raw <= 0) {
            throw new InvalidArgumentException('MONTO_ADELANTO_REQUERIDO');
        }

        $monto = round((float)$raw, 2);
        if ($monto > $montoTotal) {
            throw new InvalidArgumentException('MONTO_ADELANTO_SUPERA_TOTAL');
        }

        return $monto;
    }

    /**
     * Cotización final simplificada:
     * - El alcance incorpora el servicio incluido y todas las condiciones acordadas.
     * - El precio total es definitivo e incluye movilidad, materiales, traslado,
     *   instalación u otros conceptos que hubieran sido negociados.
     * - Las horas son opcionales para cubrir servicios por entrega o plazo.
     */
    private function normalizarCotizacionFinal(array $data): array
    {
        $alcance = $this->textoLimpio($data['alcance_confirmado'] ?? '', 2500);
        $duracion = $this->textoLimpio($data['duracion_estimada'] ?? '', 180);
        $mensaje = $this->textoLimpio($data['mensaje_proveedor'] ?? '', 1500);

        if (mb_strlen($alcance, 'UTF-8') < 8) {
            throw new InvalidArgumentException('ALCANCE_REQUERIDO');
        }

        $montoRaw = trim((string)($data['monto_propuesto'] ?? ''));
        if ($montoRaw === '' || !is_numeric($montoRaw) || (float)$montoRaw <= 0) {
            throw new InvalidArgumentException('MONTO_PROPUESTA_REQUERIDO');
        }

        $monto = round((float)$montoRaw, 2);
        if ($monto > 999999.99) {
            throw new InvalidArgumentException('MONTO_PROPUESTA_INVALIDO');
        }

        $condicionPago = $this->normalizarCondicionPagoFinal($data['condicion_pago'] ?? '');
        $montoAdelanto = $this->normalizarMontoAdelanto(
            $data['monto_adelanto'] ?? '',
            $condicionPago,
            $monto
        );

        $fecha = $this->normalizarFechaPropuesta($data['fecha_propuesta'] ?? null);
        if ($fecha === null) {
            throw new InvalidArgumentException('FECHA_PROPUESTA_REQUERIDA');
        }

        $horaInicio = $this->normalizarHoraCotizacion($data['hora_inicio'] ?? '', 'HORA_INICIO_INVALIDA');
        $horaFin = $this->normalizarHoraCotizacion($data['hora_fin'] ?? '', 'HORA_FIN_INVALIDA');

        if ($horaInicio !== null && $horaFin !== null && $horaFin <= $horaInicio) {
            throw new InvalidArgumentException('HORA_FIN_ANTERIOR_INICIO');
        }

        return [
            // Compatibilidad con la tabla actual: columnas antiguas permanecen sin uso activo.
            'modalidad' => 'a_coordinar',
            'fecha_propuesta' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'horario_propuesto' => '',
            'alcance_confirmado' => $alcance,
            'monto_propuesto' => $monto,
            'condicion_pago' => $condicionPago,
            'monto_adelanto' => $montoAdelanto,
            'duracion_estimada' => $duracion,
            'requisitos' => '',
            'mensaje_proveedor' => $mensaje,
        ];
    }

    /**
     * Vence únicamente la cotización vigente. La solicitud vuelve a un estado
     * donde el proveedor puede emitir una nueva versión.
     */
    private function sincronizarCotizacionesVencidas(?int $codigoSolicitud = null, ?int $codigoUsuario = null, ?string $rol = null): void
    {
        $where = " p.estado = 'vigente'
                   AND p.fecha_vencimiento IS NOT NULL
                   AND p.fecha_vencimiento <= NOW()
                   AND ss.estado = 'cotizacion_final_enviada' ";
        $params = [];

        if ($codigoSolicitud !== null && $codigoSolicitud > 0) {
            $where .= ' AND ss.codigo_solicitud_servicio = :codigo_solicitud ';
            $params[':codigo_solicitud'] = $codigoSolicitud;
        }

        if ($codigoUsuario !== null && $codigoUsuario > 0) {
            if ($rol === 'proveedor') {
                $where .= ' AND ss.codigo_usuario_proveedor = :codigo_usuario ';
            } else {
                $where .= ' AND ss.codigo_usuario_solicitante = :codigo_usuario ';
            }
            $params[':codigo_usuario'] = $codigoUsuario;
        }

        $sqlPropuesta = "
            UPDATE solicitud_servicio_propuesta p
            INNER JOIN solicitud_servicio ss
              ON ss.codigo_solicitud_servicio = p.codigo_solicitud_servicio
            SET p.estado = 'vencida',
                p.motivo_estado = 'La cotización venció después de 72 horas sin respuesta.',
                p.updated_at = CURRENT_TIMESTAMP
            WHERE {$where}
        ";
        $st = $this->dblink->prepare($sqlPropuesta);
        $st->execute($params);

        $sqlSolicitud = "
            UPDATE solicitud_servicio ss
            INNER JOIN solicitud_servicio_propuesta p
              ON p.codigo_solicitud_servicio = ss.codigo_solicitud_servicio
            SET ss.estado = 'cotizacion_vencida',
                ss.estado_anterior = 'cotizacion_final_enviada',
                ss.motivo_estado = 'La cotización final venció luego de 72 horas sin respuesta.',
                ss.updated_at = CURRENT_TIMESTAMP
            WHERE p.estado = 'vencida'
              AND ss.estado = 'cotizacion_final_enviada'
        ";
        if ($codigoSolicitud !== null && $codigoSolicitud > 0) {
            $sqlSolicitud .= ' AND ss.codigo_solicitud_servicio = :codigo_solicitud ';
        }
        if ($codigoUsuario !== null && $codigoUsuario > 0) {
            $sqlSolicitud .= ($rol === 'proveedor')
                ? ' AND ss.codigo_usuario_proveedor = :codigo_usuario '
                : ' AND ss.codigo_usuario_solicitante = :codigo_usuario ';
        }
        $st2 = $this->dblink->prepare($sqlSolicitud);
        $st2->execute($params);
    }

    public function enviarCotizacionFinal(int $codigoSolicitud, int $codigoProveedor, array $data): array
    {
        if ($codigoSolicitud <= 0 || $codigoProveedor <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }

        try {
            $cotizacion = $this->normalizarCotizacionFinal($data);
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
            $mensajes = [
                'FECHA_PROPUESTA_INVALIDA' => 'La fecha acordada no tiene un formato válido.',
                'FECHA_PROPUESTA_PASADA' => 'La fecha acordada no puede ser anterior a hoy.',
                'FECHA_PROPUESTA_REQUERIDA' => 'Indica la fecha acordada.',
                'ALCANCE_REQUERIDO' => 'Describe con precisión el servicio o alcance final que entregarás.',
                'MONTO_PROPUESTA_REQUERIDO' => 'Indica el precio final total de la cotización.',
                'MONTO_PROPUESTA_INVALIDO' => 'El precio final de la cotización no es válido.',
                'CONDICION_PAGO_REQUERIDA' => 'Selecciona la condición de pago acordada.',
                'MONTO_ADELANTO_REQUERIDO' => 'Indica el monto de adelanto acordado.',
                'MONTO_ADELANTO_SUPERA_TOTAL' => 'El adelanto no puede superar el precio final total.',
                'HORA_INICIO_INVALIDA' => 'La hora de inicio no tiene un formato válido.',
                'HORA_FIN_INVALIDA' => 'La hora de fin no tiene un formato válido.',
                'HORA_FIN_ANTERIOR_INICIO' => 'La hora de fin debe ser posterior a la hora de inicio.',
            ];
            return ['ok' => false, 'error' => $error, 'mensaje' => $mensajes[$error] ?? 'Revisa los datos de la cotización final.'];
        }

        try {
            $this->sincronizarCotizacionesVencidas($codigoSolicitud);
            $this->dblink->beginTransaction();

            $solicitud = $this->obtenerSolicitudProveedorBloqueada($codigoSolicitud, $codigoProveedor);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }

            $estadoActual = (string)($solicitud['estado'] ?? '');
            $permitidos = ['pendiente_proveedor', 'ajuste_solicitado', 'ajuste_cotizacion_solicitado', 'cotizacion_vencida', 'informacion_adicional_solicitada'];
            if (!in_array($estadoActual, $permitidos, true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La solicitud no está disponible para emitir una cotización final.'];
            }

            $stPrevias = $this->dblink->prepare("
                UPDATE solicitud_servicio_propuesta
                SET estado = 'reemplazada', updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
                  AND estado IN ('vigente', 'requiere_actualizacion')
            ");
            $stPrevias->execute([':solicitud' => $codigoSolicitud]);

            $stVersion = $this->dblink->prepare("
                SELECT COALESCE(MAX(version), 0) + 1
                FROM solicitud_servicio_propuesta
                WHERE codigo_solicitud_servicio = :solicitud
                FOR UPDATE
            ");
            $stVersion->execute([':solicitud' => $codigoSolicitud]);
            $version = max(1, (int)$stVersion->fetchColumn());

            $fechaVencimiento = (new DateTimeImmutable('now', new DateTimeZone('America/Lima')))
                ->modify('+72 hours')
                ->format('Y-m-d H:i:s');

            $sql = "
                INSERT INTO solicitud_servicio_propuesta
                (
                    codigo_solicitud_servicio, codigo_usuario_proveedor, version,
                    modalidad, momento_tipo, fecha_propuesta, horario_propuesto,
                    hora_inicio, hora_fin,
                    alcance_confirmado, tipo_precio, monto_propuesto, unidad_precio,
                    movilidad_tipo, monto_movilidad, condicion_pago, monto_adelanto, fecha_vencimiento,
                    duracion_estimada, requisitos, mensaje_proveedor, estado
                )
                VALUES
                (
                    :solicitud, :proveedor, :version,
                    'a_coordinar', 'fecha_hora', :fecha, NULL,
                    :hora_inicio, :hora_fin,
                    :alcance, 'fijo', :monto, NULL,
                    'no_aplica', NULL, :condicion_pago, :monto_adelanto, :fecha_vencimiento,
                    :duracion, NULL, :mensaje, 'vigente'
                )
            ";
            $st = $this->dblink->prepare($sql);
            $st->execute([
                ':solicitud' => $codigoSolicitud,
                ':proveedor' => $codigoProveedor,
                ':version' => $version,
                ':fecha' => $cotizacion['fecha_propuesta'],
                ':hora_inicio' => $cotizacion['hora_inicio'],
                ':hora_fin' => $cotizacion['hora_fin'],
                ':alcance' => $cotizacion['alcance_confirmado'],
                ':monto' => $cotizacion['monto_propuesto'],
                ':condicion_pago' => $cotizacion['condicion_pago'],
                ':monto_adelanto' => $cotizacion['monto_adelanto'],
                ':fecha_vencimiento' => $fechaVencimiento,
                ':duracion' => $cotizacion['duracion_estimada'] !== '' ? $cotizacion['duracion_estimada'] : null,
                ':mensaje' => $cotizacion['mensaje_proveedor'] !== '' ? $cotizacion['mensaje_proveedor'] : null,
            ]);
            $codigoPropuesta = (int)$this->dblink->lastInsertId();

            $stSolicitud = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'cotizacion_final_enviada',
                    estado_anterior = :anterior,
                    motivo_estado = 'El proveedor emitió una cotización final válida por 72 horas. El precio total incluye todos los costos acordados.',
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
                  AND codigo_usuario_proveedor = :proveedor
            ");
            $stSolicitud->execute([':anterior' => $estadoActual, ':solicitud' => $codigoSolicitud, ':proveedor' => $codigoProveedor]);

            $this->registrarInteraccion(
                $codigoSolicitud, $codigoProveedor, 'proveedor', 'cotizacion_final_enviada',
                $cotizacion['mensaje_proveedor'] !== '' ? $cotizacion['mensaje_proveedor'] : 'El proveedor emitió una cotización final.',
                array_merge($cotizacion, [
                    'codigo_solicitud_servicio_propuesta' => $codigoPropuesta,
                    'version' => $version,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'vigencia_horas' => 72,
                ])
            );

            $this->registrarNotificacionSolicitante(
                $solicitud,
                'cotizacion_final_enviada',
                'Tienes una cotización final por revisar',
                'El proveedor envió una cotización final para “' . (string)$solicitud['titulo_servicio'] . '”.',
                ['codigo_solicitud_servicio_propuesta' => $codigoPropuesta, 'version' => $version]
            );

            $this->dblink->commit();
            return [
                'ok' => true,
                'mensaje' => 'La cotización final fue enviada y estará vigente durante 72 horas.',
                'data' => [
                    'codigo_solicitud_servicio_propuesta' => $codigoPropuesta,
                    'version' => $version,
                    'fecha_vencimiento' => $fechaVencimiento,
                ],
                'estado' => 'cotizacion_final_enviada',
            ];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][enviarCotizacionFinal] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_ENVIAR_COTIZACION_FINAL', 'mensaje' => 'No se pudo enviar la cotización final.'];
        }
    }

    public function aceptarCotizacionFinalSolicitante(int $codigoSolicitud, int $codigoSolicitante): array
    {
        if ($codigoSolicitud <= 0 || $codigoSolicitante <= 0) {
            return ['ok' => false, 'error' => 'PARAMETROS_INVALIDOS', 'mensaje' => 'No se pudo identificar la solicitud.'];
        }

        try {
            $this->sincronizarCotizacionesVencidas($codigoSolicitud);
            $this->dblink->beginTransaction();

            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }
            if ((string)$solicitud['estado'] !== 'cotizacion_final_enviada') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'No hay una cotización final vigente para aceptar.'];
            }

            $propuesta = $this->obtenerPropuestaVigenteBloqueada($codigoSolicitud);
            if (!$propuesta || (!empty($propuesta['fecha_vencimiento']) && strtotime((string)$propuesta['fecha_vencimiento']) <= time())) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'COTIZACION_VENCIDA', 'mensaje' => 'La cotización final venció. Solicita una versión actualizada al proveedor.'];
            }

            $stP = $this->dblink->prepare("
                UPDATE solicitud_servicio_propuesta
                SET estado = 'aceptada', updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio_propuesta = :propuesta
                  AND estado = 'vigente'
            ");
            $stP->execute([':propuesta' => (int)$propuesta['codigo_solicitud_servicio_propuesta']]);

            $stS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'coordinacion_confirmada',
                    estado_anterior = 'cotizacion_final_enviada',
                    motivo_estado = 'El comprador aceptó la cotización final.',
                    fecha_aceptacion = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
                  AND codigo_usuario_solicitante = :solicitante
            ");
            $stS->execute([':solicitud' => $codigoSolicitud, ':solicitante' => $codigoSolicitante]);

            $this->registrarInteraccion(
                $codigoSolicitud, $codigoSolicitante, 'solicitante', 'cotizacion_final_aceptada',
                'El comprador aceptó la cotización final.',
                ['codigo_solicitud_servicio_propuesta' => (int)$propuesta['codigo_solicitud_servicio_propuesta'], 'version' => (int)$propuesta['version']]
            );
            $this->registrarNotificacionProveedor(
                $solicitud,
                'cotizacion_final_aceptada',
                'Tu cotización final fue aceptada',
                'El comprador aceptó la cotización final para “' . (string)$solicitud['titulo_servicio'] . '”.'
            );

            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La cotización final fue aceptada. La coordinación quedó confirmada.', 'estado' => 'coordinacion_confirmada'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][aceptarCotizacionFinalSolicitante] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_ACEPTAR_COTIZACION_FINAL', 'mensaje' => 'No se pudo aceptar la cotización final.'];
        }
    }

    public function solicitarAjusteCotizacionFinal(int $codigoSolicitud, int $codigoSolicitante, string $mensaje): array
    {
        $mensaje = $this->textoLimpio($mensaje, 1500);
        if (mb_strlen($mensaje, 'UTF-8') < 8) {
            return ['ok' => false, 'error' => 'MENSAJE_AJUSTE_REQUERIDO', 'mensaje' => 'Explica qué necesitas ajustar en la cotización final.'];
        }

        try {
            $this->dblink->beginTransaction();
            $this->sincronizarCotizacionesVencidas($codigoSolicitud);
            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }
            if ((string)$solicitud['estado'] !== 'cotizacion_final_enviada') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'No hay una cotización final disponible para solicitar ajuste.'];
            }
            $propuesta = $this->obtenerPropuestaVigenteBloqueada($codigoSolicitud);
            if (!$propuesta) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'PROPUESTA_NO_ENCONTRADA', 'mensaje' => 'No se encontró la cotización final vigente.'];
            }

            $stP = $this->dblink->prepare("
                UPDATE solicitud_servicio_propuesta
                SET estado = 'requiere_actualizacion',
                    motivo_estado = :motivo,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio_propuesta = :propuesta
                  AND estado = 'vigente'
            ");
            $stP->execute([':motivo' => $mensaje, ':propuesta' => (int)$propuesta['codigo_solicitud_servicio_propuesta']]);

            $stS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'ajuste_cotizacion_solicitado',
                    estado_anterior = 'cotizacion_final_enviada',
                    motivo_estado = :motivo,
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $stS->execute([':motivo' => $mensaje, ':solicitud' => $codigoSolicitud]);

            $this->registrarInteraccion(
                $codigoSolicitud, $codigoSolicitante, 'solicitante', 'ajuste_cotizacion_solicitado', $mensaje,
                ['codigo_solicitud_servicio_propuesta' => (int)$propuesta['codigo_solicitud_servicio_propuesta'], 'version' => (int)$propuesta['version']]
            );
            $this->registrarNotificacionProveedor(
                $solicitud, 'ajuste_cotizacion_solicitado', 'El comprador solicitó un ajuste',
                'El comprador solicitó ajustar la cotización final para “' . (string)$solicitud['titulo_servicio'] . '”.'
            );
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'Tu solicitud de ajuste fue enviada al proveedor.', 'estado' => 'ajuste_cotizacion_solicitado'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][solicitarAjusteCotizacionFinal] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_SOLICITAR_AJUSTE_COTIZACION', 'mensaje' => 'No se pudo solicitar el ajuste de la cotización.'];
        }
    }

    public function rechazarCotizacionFinalSolicitante(int $codigoSolicitud, int $codigoSolicitante, string $motivo): array
    {
        $motivo = $this->textoLimpio($motivo, 500);
        if (mb_strlen($motivo, 'UTF-8') < 5) {
            return ['ok' => false, 'error' => 'MOTIVO_RECHAZO_REQUERIDO', 'mensaje' => 'Indica un motivo breve para rechazar la cotización.'];
        }

        try {
            $this->dblink->beginTransaction();
            $this->sincronizarCotizacionesVencidas($codigoSolicitud);
            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }
            if ((string)$solicitud['estado'] !== 'cotizacion_final_enviada') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'No hay una cotización final vigente para rechazar.'];
            }

            $stP = $this->dblink->prepare("
                UPDATE solicitud_servicio_propuesta
                SET estado = 'rechazada_solicitante', motivo_estado = :motivo, updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud AND estado = 'vigente'
            ");
            $stP->execute([':motivo' => $motivo, ':solicitud' => $codigoSolicitud]);

            $stS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'cotizacion_rechazada_solicitante',
                    estado_anterior = 'cotizacion_final_enviada',
                    motivo_estado = :motivo,
                    fecha_cierre = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $stS->execute([':motivo' => $motivo, ':solicitud' => $codigoSolicitud]);

            $this->registrarInteraccion($codigoSolicitud, $codigoSolicitante, 'solicitante', 'cotizacion_final_rechazada', $motivo, []);
            $this->registrarNotificacionProveedor($solicitud, 'cotizacion_final_rechazada', 'La cotización final fue rechazada', 'El comprador rechazó la cotización para “' . (string)$solicitud['titulo_servicio'] . '”.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La cotización final fue rechazada y la solicitud quedó cerrada.', 'estado' => 'cotizacion_rechazada_solicitante'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][rechazarCotizacionFinalSolicitante] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_RECHAZAR_COTIZACION_FINAL', 'mensaje' => 'No se pudo rechazar la cotización final.'];
        }
    }

    public function cancelarSolicitudFlujoFinal(int $codigoSolicitud, int $codigoSolicitante, string $motivo): array
    {
        $motivo = $this->textoLimpio($motivo, 500);
        if (mb_strlen($motivo, 'UTF-8') < 5) {
            return ['ok' => false, 'error' => 'MOTIVO_CANCELACION_REQUERIDO', 'mensaje' => 'Indica un motivo claro para cancelar la coordinación.'];
        }

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }
            $estado = (string)$solicitud['estado'];
            $permitidos = ['pendiente_proveedor', 'ajuste_solicitado', 'ajuste_cotizacion_solicitado', 'cotizacion_final_enviada', 'cotizacion_vencida', 'coordinacion_confirmada'];
            if (!in_array($estado, $permitidos, true)) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'Esta solicitud ya no permite cancelación desde su estado actual.'];
            }
            $stP = $this->dblink->prepare("
                UPDATE solicitud_servicio_propuesta
                SET estado = 'cancelada_solicitante', motivo_estado = :motivo, updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
                  AND estado IN ('vigente', 'aceptada', 'requiere_actualizacion')
            ");
            $stP->execute([':motivo' => $motivo, ':solicitud' => $codigoSolicitud]);

            $stS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'cancelada_solicitante', estado_anterior = :anterior,
                    motivo_estado = :motivo, fecha_cancelacion = NOW(), fecha_cierre = NOW(), updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $stS->execute([':anterior' => $estado, ':motivo' => $motivo, ':solicitud' => $codigoSolicitud]);

            $this->registrarInteraccion($codigoSolicitud, $codigoSolicitante, 'solicitante', 'solicitud_cancelada', $motivo, ['estado_anterior' => $estado]);
            $this->registrarNotificacionProveedor($solicitud, 'solicitud_cancelada', 'El comprador canceló la coordinación', 'El comprador canceló la coordinación para “' . (string)$solicitud['titulo_servicio'] . '”.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La coordinación fue cancelada y el proveedor fue notificado.', 'estado' => 'cancelada_solicitante'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][cancelarSolicitudFlujoFinal] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_CANCELAR_SOLICITUD', 'mensaje' => 'No se pudo cancelar la coordinación.'];
        }
    }

    public function cancelarCoordinacionProveedor(int $codigoSolicitud, int $codigoProveedor, string $motivo): array
    {
        $motivo = $this->textoLimpio($motivo, 500);
        if (mb_strlen($motivo, 'UTF-8') < 5) {
            return ['ok' => false, 'error' => 'MOTIVO_CANCELACION_REQUERIDO', 'mensaje' => 'Indica un motivo claro para cancelar la coordinación.'];
        }

        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudProveedorBloqueada($codigoSolicitud, $codigoProveedor);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'La solicitud no existe o no te pertenece.'];
            }
            if ((string)$solicitud['estado'] !== 'coordinacion_confirmada') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'El proveedor solo puede cancelar una coordinación ya aceptada.'];
            }

            $stP = $this->dblink->prepare("
                UPDATE solicitud_servicio_propuesta
                SET estado = 'cancelada_proveedor', motivo_estado = :motivo, updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud AND estado = 'aceptada'
            ");
            $stP->execute([':motivo' => $motivo, ':solicitud' => $codigoSolicitud]);

            $stS = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'cancelada_proveedor', estado_anterior = 'coordinacion_confirmada',
                    motivo_estado = :motivo, fecha_cancelacion = NOW(), fecha_cierre = NOW(), updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $stS->execute([':motivo' => $motivo, ':solicitud' => $codigoSolicitud]);

            $this->registrarInteraccion($codigoSolicitud, $codigoProveedor, 'proveedor', 'coordinacion_cancelada_proveedor', $motivo, []);
            $this->registrarNotificacionSolicitante($solicitud, 'coordinacion_cancelada_proveedor', 'El proveedor canceló la coordinación', 'El proveedor canceló la coordinación para “' . (string)$solicitud['titulo_servicio'] . '”.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La coordinación fue cancelada y el comprador fue notificado.', 'estado' => 'cancelada_proveedor'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][cancelarCoordinacionProveedor] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_CANCELAR_COORDINACION', 'mensaje' => 'No se pudo cancelar la coordinación.'];
        }
    }

    public function marcarServicioRealizadoProveedor(int $codigoSolicitud, int $codigoProveedor): array
    {
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudProveedorBloqueada($codigoSolicitud, $codigoProveedor);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta coordinación.'];
            }
            if ((string)$solicitud['estado'] !== 'coordinacion_confirmada') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'La coordinación debe estar aceptada antes de marcar el servicio como realizado.'];
            }
            $st = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'servicio_realizado_proveedor',
                    estado_anterior = 'coordinacion_confirmada',
                    motivo_estado = 'El proveedor indicó que el servicio fue realizado y espera confirmación.',
                    fecha_realizado_proveedor = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $st->execute([':solicitud' => $codigoSolicitud]);
            $this->registrarInteraccion($codigoSolicitud, $codigoProveedor, 'proveedor', 'servicio_marcado_realizado', 'El proveedor marcó el servicio como realizado.', []);
            $this->registrarNotificacionSolicitante($solicitud, 'servicio_marcado_realizado', 'Confirma el servicio realizado', 'El proveedor marcó como realizado el servicio “' . (string)$solicitud['titulo_servicio'] . '”.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'El servicio fue marcado como realizado. Espera la confirmación del comprador.', 'estado' => 'servicio_realizado_proveedor'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][marcarServicioRealizadoProveedor] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_MARCAR_SERVICIO_REALIZADO', 'mensaje' => 'No se pudo marcar el servicio como realizado.'];
        }
    }

    public function confirmarServicioRealizadoSolicitante(int $codigoSolicitud, int $codigoSolicitante): array
    {
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta coordinación.'];
            }
            if ((string)$solicitud['estado'] !== 'servicio_realizado_proveedor') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'El proveedor aún no marcó el servicio como realizado.'];
            }
            $st = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'servicio_confirmado_solicitante',
                    estado_anterior = 'servicio_realizado_proveedor',
                    motivo_estado = 'El comprador confirmó que el servicio fue realizado.',
                    fecha_confirmacion_solicitante = NOW(),
                    fecha_cierre = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $st->execute([':solicitud' => $codigoSolicitud]);
            $this->registrarInteraccion($codigoSolicitud, $codigoSolicitante, 'solicitante', 'servicio_confirmado', 'El comprador confirmó la realización del servicio.', []);
            $this->registrarNotificacionProveedor($solicitud, 'servicio_confirmado', 'El servicio fue confirmado', 'El comprador confirmó el servicio “' . (string)$solicitud['titulo_servicio'] . '”.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'El servicio fue confirmado. La calificación se habilitará en el siguiente módulo.', 'estado' => 'servicio_confirmado_solicitante'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][confirmarServicioRealizadoSolicitante] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_CONFIRMAR_SERVICIO', 'mensaje' => 'No se pudo confirmar el servicio.'];
        }
    }

    public function reportarObservacionServicio(int $codigoSolicitud, int $codigoSolicitante, string $mensaje): array
    {
        $mensaje = $this->textoLimpio($mensaje, 1500);
        if (mb_strlen($mensaje, 'UTF-8') < 8) {
            return ['ok' => false, 'error' => 'MENSAJE_OBSERVACION_REQUERIDO', 'mensaje' => 'Describe la observación para dejarla registrada.'];
        }
        try {
            $this->dblink->beginTransaction();
            $solicitud = $this->obtenerSolicitudSolicitanteBloqueada($codigoSolicitud, $codigoSolicitante);
            if (!$solicitud) {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'SOLICITUD_NO_ENCONTRADA', 'mensaje' => 'No se encontró esta coordinación.'];
            }
            if ((string)$solicitud['estado'] !== 'servicio_realizado_proveedor') {
                $this->dblink->rollBack();
                return ['ok' => false, 'error' => 'ESTADO_NO_PERMITE_ACCION', 'mensaje' => 'Solo puedes reportar una observación después de que el proveedor marque el servicio como realizado.'];
            }
            $st = $this->dblink->prepare("
                UPDATE solicitud_servicio
                SET estado = 'observacion_reportada',
                    estado_anterior = 'servicio_realizado_proveedor',
                    motivo_estado = :mensaje,
                    fecha_observacion = NOW(),
                    updated_at = CURRENT_TIMESTAMP
                WHERE codigo_solicitud_servicio = :solicitud
            ");
            $st->execute([':mensaje' => $mensaje, ':solicitud' => $codigoSolicitud]);
            $this->registrarInteraccion($codigoSolicitud, $codigoSolicitante, 'solicitante', 'observacion_reportada', $mensaje, []);
            $this->registrarNotificacionProveedor($solicitud, 'observacion_reportada', 'El comprador reportó una observación', 'El comprador registró una observación sobre “' . (string)$solicitud['titulo_servicio'] . '”.');
            $this->dblink->commit();
            return ['ok' => true, 'mensaje' => 'La observación fue registrada. El historial queda disponible para la revisión correspondiente.', 'estado' => 'observacion_reportada'];
        } catch (Throwable $e) {
            if ($this->dblink->inTransaction()) $this->dblink->rollBack();
            error_log('[EV][SolicitudServicio][reportarObservacionServicio] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'ERROR_REPORTAR_OBSERVACION', 'mensaje' => 'No se pudo registrar la observación.'];
        }
    }

}
