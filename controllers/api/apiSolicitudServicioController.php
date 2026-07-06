<?php
// controllers/api/apiSolicitudServicioController.php
// Puntos 8, 9 y 10 EV: solicitud, conversación y cotización de servicios.
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SolicitudServicio.php';
require_once __DIR__ . '/../../models/SolicitudServicioChat.php';


class apiSolicitudServicioController
{
    private function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function obtenerAuth(): array
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return is_array($auth) ? $auth : [];
    }

    private function obtenerUsuarioAuth(): int
    {
        $auth = $this->obtenerAuth();
        $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);
        if ($codigoUsuario <= 0) {
            $this->json(401, ['ok'=>false,'error'=>'UNAUTHORIZED','mensaje'=>'Tu sesión no es válida. Vuelve a iniciar sesión.']);
            exit;
        }
        return $codigoUsuario;
    }

    private function exigirRolVecino(): int
    {
        $codigoUsuario = $this->obtenerUsuarioAuth();
        $auth = $this->obtenerAuth();
        $rol = strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
        if ($rol !== 'vecino') {
            $this->json(403, ['ok'=>false,'error'=>'ROL_NO_AUTORIZADO','mensaje'=>'Esta operación está disponible únicamente para vecinos.']);
            exit;
        }
        return $codigoUsuario;
    }

    private function leerInput(): array
    {
        if (!empty($_POST) && is_array($_POST)) return $_POST;
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') return [];
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    private function validarMetodo(string $esperado): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === $esperado) return true;
        $this->json(405, ['ok'=>false,'error'=>'METHOD_NOT_ALLOWED','mensaje'=>'Método no permitido.']);
        return false;
    }

    private function statusError(string $error): int
    {
        return match ($error) {
            'PARAMETROS_INVALIDOS','DIRECCION_REQUERIDA','DIRECCION_DEMASIADO_LARGA','MENSAJE_REQUERIDO',
            'MENSAJE_DEMASIADO_LARGO','FECHA_DESEADA_INVALIDA','FECHA_DESEADA_PASADA',
            'MENSAJE_INFORMACION_REQUERIDO','MENSAJE_RESPUESTA_REQUERIDO','MENSAJE_AJUSTE_REQUERIDO',
            'MOTIVO_RECHAZO_REQUERIDO','MOTIVO_CANCELACION_REQUERIDO','FECHA_PROPUESTA_INVALIDA',
            'FECHA_PROPUESTA_PASADA','FECHA_PROPUESTA_REQUERIDA','ALCANCE_REQUERIDO',
            'MENSAJE_PROPUESTA_REQUERIDO','MONTO_PROPUESTA_REQUERIDO','MONTO_PROPUESTA_INVALIDO',
            'MAX_ADJUNTOS_EXCEDIDO','ADJUNTO_INVALIDO','ADJUNTO_PESO_INVALIDO','ADJUNTO_FORMATO_INVALIDO',
            'DIRECCION_COMPARTIR_REQUERIDA','CONDICION_PAGO_REQUERIDA','MONTO_ADELANTO_REQUERIDO',
            'MONTO_ADELANTO_SUPERA_TOTAL','HORA_INICIO_INVALIDA','HORA_FIN_INVALIDA',
            'HORA_FIN_ANTERIOR_INICIO','MENSAJE_OBSERVACION_REQUERIDO','COTIZACION_VENCIDA' => 400,

            'SERVICIO_NO_ENCONTRADO','SOLICITUD_NO_ENCONTRADA','PROPUESTA_NO_ENCONTRADA' => 404,

            'SIN_RESIDENCIA_ACTIVA','PUBLICACION_NO_ES_SERVICIO','SERVICIO_PROPIO','SERVICIO_NO_DISPONIBLE',
            'PROVEEDOR_NO_HABILITADO','SERVICIO_FUERA_DE_RESIDENCIA','SOLICITUD_ACTIVA_EXISTENTE',
            'ESTADO_NO_PERMITE_ACCION' => 409,
            'ROL_NO_AUTORIZADO' => 403,
            default => 500,
        };
    }

    private function responderResultado(array $resultado, int $statusOk = 200): void
    {
        if (!($resultado['ok'] ?? false)) {
            $error = (string)($resultado['error'] ?? 'ERROR_SOLICITUD_SERVICIO');
            $payload = ['ok'=>false,'error'=>$error,'mensaje'=>(string)($resultado['mensaje'] ?? 'No se pudo procesar la solicitud de servicio.')];
            if ($error === 'SIN_RESIDENCIA_ACTIVA') $payload['redirect'] = rtrim(BASE_URL, '/') . '/mi-perfil';
            $this->json($this->statusError($error), $payload);
            return;
        }

        $this->json($statusOk, [
            'ok'=>true,
            'mensaje'=>(string)($resultado['mensaje'] ?? 'Operación realizada correctamente.'),
            'data'=>$resultado['data'] ?? [],
            'estado'=>$resultado['estado'] ?? null,
            'warnings'=>$resultado['warnings'] ?? [],
        ]);
    }

    /** POST /api/servicios/solicitudes */
    public function registrar(): void
    {
        if (!$this->validarMetodo('POST')) return;

        try {
            $codigoSolicitante = $this->exigirRolVecino();
            $input = $this->leerInput();

            $model = new SolicitudServicio();
            $resultado = $model->registrarSolicitud([
                'codigo_producto' => (int)($input['codigo_producto'] ?? 0),
                'codigo_usuario_solicitante' => $codigoSolicitante,
                // La solicitud inicial no pide fecha, horario, presupuesto ni dirección.
                'fecha_deseada' => null,
                'rango_horario' => 'a_coordinar',
                'direccion_atencion' => '',
                'mensaje_solicitante' => $input['mensaje_solicitante'] ?? '',
            ]);

            if (($resultado['ok'] ?? false) === true) {
                $id = (int)($resultado['data']['codigo_solicitud_servicio'] ?? 0);
                if ($id > 0) {
                    $chat = new SolicitudServicioChat();
                    $contexto = $chat->registrarContextoInicial(
                        $id,
                        $codigoSolicitante,
                        [],
                        $_FILES
                    );

                    if (!($contexto['ok'] ?? false)) {
                        $resultado['warnings'][] = $contexto['mensaje'] ?? 'La solicitud fue creada, pero no se pudieron guardar las referencias.';
                    }
                }
            }

            $this->responderResultado($resultado, 201);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][registrar] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'Ocurrió un error al registrar la solicitud de servicio.',
            ]);
        }
    }

    /** GET /api/servicios/solicitudes/proveedor */
    public function listarProveedor(): void
    {
        if (!$this->validarMetodo('GET')) return;
        try {
            $codigoProveedor = $this->exigirRolVecino();
            $this->responderResultado((new SolicitudServicio())->listarSolicitudesProveedor($codigoProveedor));
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][listarProveedor] ' . $e->getMessage());
            $this->json(500,['ok'=>false,'error'=>'SERVER_ERROR','mensaje'=>'No se pudieron cargar las solicitudes de servicio.']);
        }
    }

    /** GET /api/servicios/solicitudes/solicitante */
    public function listarSolicitante(): void
    {
        if (!$this->validarMetodo('GET')) return;
        try {
            $codigoSolicitante = $this->exigirRolVecino();
            $this->responderResultado((new SolicitudServicio())->listarSolicitudesSolicitante($codigoSolicitante));
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][listarSolicitante] ' . $e->getMessage());
            $this->json(500,['ok'=>false,'error'=>'SERVER_ERROR','mensaje'=>'No se pudieron cargar tus solicitudes de servicio.']);
        }
    }

    /** GET /api/servicios/solicitudes/{id}/conversacion */
    public function obtenerConversacion(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('GET')) return;
        try {
            $this->responderResultado((new SolicitudServicioChat())->obtenerConversacion($codigoSolicitud, $this->exigirRolVecino()));
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][obtenerConversacion] ' . $e->getMessage());
            $this->json(500,['ok'=>false,'error'=>'SERVER_ERROR','mensaje'=>'No se pudo cargar la conversación.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/mensajes */
    public function enviarMensaje(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $codigoUsuario = $this->exigirRolVecino();
            $input = $this->leerInput();
            $chat = new SolicitudServicioChat();
            $accionSistema = (string)($input['accion_sistema'] ?? '');

            if ($accionSistema === 'solicitar_ubicacion_para_cotizar') {
                $r = $chat->solicitarUbicacionParaCotizar($codigoSolicitud, $codigoUsuario);
                $this->responderResultado($r, 201);
                return;
            }

            $r = $chat->enviarMensaje($codigoSolicitud, $codigoUsuario, (string)($input['mensaje'] ?? ''), $_FILES);
            $this->responderResultado($r, 201);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][enviarMensaje] ' . $e->getMessage());
            $this->json(500,['ok'=>false,'error'=>'SERVER_ERROR','mensaje'=>'No se pudo enviar el mensaje.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/compartir-ubicacion */
    public function compartirUbicacion(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $input = $this->leerInput();
            $r = (new SolicitudServicioChat())->compartirDireccion($codigoSolicitud, $this->exigirRolVecino(), (string)($input['direccion_atencion'] ?? $input['direccion'] ?? ''));
            $this->responderResultado($r);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][compartirUbicacion] ' . $e->getMessage());
            $this->json(500,['ok'=>false,'error'=>'SERVER_ERROR','mensaje'=>'No se pudo compartir el punto de atención.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/solicitar-informacion */
    public function solicitarInformacion(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $input=$this->leerInput();
            $r=(new SolicitudServicio())->solicitarInformacion($codigoSolicitud,$this->exigirRolVecino(),(string)($input['mensaje'] ?? $input['mensaje_proveedor'] ?? ''));
            $this->responderResultado($r);
        } catch(Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][solicitarInformacion] '.$e->getMessage());
            $this->json(500,['ok'=>false,'error'=>'SERVER_ERROR','mensaje'=>'No se pudo solicitar información adicional.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/propuesta */
    public function enviarPropuesta(int $codigoSolicitud): void
    {
        $this->enviarCotizacionFinal($codigoSolicitud);
    }

    /** POST /api/servicios/solicitudes/{id}/rechazar */
    public function rechazar(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $input=$this->leerInput();
            $r=(new SolicitudServicio())->rechazarSolicitud($codigoSolicitud,$this->exigirRolVecino(),(string)($input['motivo_rechazo'] ?? $input['motivo'] ?? ''));
            $this->responderResultado($r);
        } catch(Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][rechazar] '.$e->getMessage());
            $this->json(500,['ok'=>false,'error'=>'SERVER_ERROR','mensaje'=>'No se pudo rechazar la solicitud de servicio.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/responder-informacion */
    public function responderInformacion(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $input=$this->leerInput();
            $r=(new SolicitudServicio())->responderInformacionSolicitante($codigoSolicitud,$this->exigirRolVecino(),(string)($input['mensaje'] ?? $input['mensaje_solicitante'] ?? ''));
            $this->responderResultado($r);
        } catch(Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][responderInformacion] '.$e->getMessage());
            $this->json(500,['ok'=>false,'error'=>'SERVER_ERROR','mensaje'=>'No se pudo enviar la información adicional.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/aceptar-propuesta */
    public function aceptarPropuesta(int $codigoSolicitud): void
    {
        $this->aceptarCotizacionFinal($codigoSolicitud);
    }

    /** POST /api/servicios/solicitudes/{id}/solicitar-ajuste */
    public function solicitarAjuste(int $codigoSolicitud): void
    {
        $this->solicitarAjusteCotizacionFinal($codigoSolicitud);
    }

    /** POST /api/servicios/solicitudes/{id}/cancelar */
    public function cancelar(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $input = $this->leerInput();
            $resultado = (new SolicitudServicio())->cancelarSolicitudFlujoFinal(
                $codigoSolicitud,
                $this->exigirRolVecino(),
                (string)($input['motivo_cancelacion'] ?? $input['motivo'] ?? '')
            );
            $this->responderResultado($resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][cancelar] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo cancelar la coordinación.']);
        }
    }


    /** POST /api/servicios/solicitudes/{id}/cotizacion-final */
    public function enviarCotizacionFinal(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;

        try {
            $codigoProveedor = $this->exigirRolVecino();
            $input = $this->leerInput();
            $resultado = (new SolicitudServicio())->enviarCotizacionFinal($codigoSolicitud, $codigoProveedor, $input);

            if (($resultado['ok'] ?? false) && !empty($_FILES['adjuntos_propuesta'])) {
                $codigoPropuesta = (int)($resultado['data']['codigo_solicitud_servicio_propuesta'] ?? 0);
                if ($codigoPropuesta > 0) {
                    $adjuntos = (new SolicitudServicioChat())->guardarAdjuntosPropuesta(
                        $codigoSolicitud,
                        $codigoProveedor,
                        $codigoPropuesta,
                        $_FILES
                    );
                    if (!($adjuntos['ok'] ?? false)) {
                        $resultado['warnings'][] = $adjuntos['mensaje'] ?? 'La cotización fue enviada, pero no se pudieron guardar sus imágenes.';
                    }
                }
            }

            $this->responderResultado($resultado, 201);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][enviarCotizacionFinal] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo emitir la cotización final.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/aceptar-cotizacion-final */
    public function aceptarCotizacionFinal(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $resultado = (new SolicitudServicio())->aceptarCotizacionFinalSolicitante(
                $codigoSolicitud,
                $this->exigirRolVecino()
            );
            $this->responderResultado($resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][aceptarCotizacionFinal] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo aceptar la cotización final.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/solicitar-ajuste-cotizacion */
    public function solicitarAjusteCotizacionFinal(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $input = $this->leerInput();
            $resultado = (new SolicitudServicio())->solicitarAjusteCotizacionFinal(
                $codigoSolicitud,
                $this->exigirRolVecino(),
                (string)($input['mensaje'] ?? $input['motivo_ajuste'] ?? '')
            );
            $this->responderResultado($resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][solicitarAjusteCotizacionFinal] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo solicitar el ajuste de la cotización.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/rechazar-cotizacion-final */
    public function rechazarCotizacionFinal(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $input = $this->leerInput();
            $resultado = (new SolicitudServicio())->rechazarCotizacionFinalSolicitante(
                $codigoSolicitud,
                $this->exigirRolVecino(),
                (string)($input['motivo_rechazo'] ?? $input['motivo'] ?? '')
            );
            $this->responderResultado($resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][rechazarCotizacionFinal] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo rechazar la cotización final.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/cancelar-proveedor */
    public function cancelarProveedor(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $input = $this->leerInput();
            $resultado = (new SolicitudServicio())->cancelarCoordinacionProveedor(
                $codigoSolicitud,
                $this->exigirRolVecino(),
                (string)($input['motivo_cancelacion'] ?? $input['motivo'] ?? '')
            );
            $this->responderResultado($resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][cancelarProveedor] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo cancelar la coordinación.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/marcar-realizado */
    public function marcarRealizado(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $resultado = (new SolicitudServicio())->marcarServicioRealizadoProveedor(
                $codigoSolicitud,
                $this->exigirRolVecino()
            );
            $this->responderResultado($resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][marcarRealizado] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo marcar el servicio como realizado.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/confirmar-realizado */
    public function confirmarRealizado(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $resultado = (new SolicitudServicio())->confirmarServicioRealizadoSolicitante(
                $codigoSolicitud,
                $this->exigirRolVecino()
            );
            $this->responderResultado($resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][confirmarRealizado] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo confirmar el servicio.']);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/reportar-observacion */
    public function reportarObservacion(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;
        try {
            $input = $this->leerInput();
            $resultado = (new SolicitudServicio())->reportarObservacionServicio(
                $codigoSolicitud,
                $this->exigirRolVecino(),
                (string)($input['mensaje'] ?? $input['observacion'] ?? '')
            );
            $this->responderResultado($resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][reportarObservacion] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo registrar la observación.']);
        }
    }

}
