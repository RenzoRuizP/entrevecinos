<?php
// controllers/api/apiSolicitudServicioController.php
// Punto 8 y Punto 9 EV: solicitud y coordinación estructurada de servicios.
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SolicitudServicio.php';

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
            $this->json(401, [
                'ok' => false,
                'error' => 'UNAUTHORIZED',
                'mensaje' => 'Tu sesión no es válida. Vuelve a iniciar sesión.',
            ]);
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
            $this->json(403, [
                'ok' => false,
                'error' => 'ROL_NO_AUTORIZADO',
                'mensaje' => 'Esta operación está disponible únicamente para vecinos.',
            ]);
            exit;
        }

        return $codigoUsuario;
    }

    private function leerInput(): array
    {
        if (!empty($_POST) && is_array($_POST)) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    private function validarMetodo(string $esperado): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === $esperado) {
            return true;
        }

        $this->json(405, [
            'ok' => false,
            'error' => 'METHOD_NOT_ALLOWED',
            'mensaje' => 'Método no permitido.',
        ]);
        return false;
    }

    private function statusError(string $error): int
    {
        return match ($error) {
            'PARAMETROS_INVALIDOS',
            'DIRECCION_REQUERIDA',
            'DIRECCION_DEMASIADO_LARGA',
            'MENSAJE_REQUERIDO',
            'MENSAJE_DEMASIADO_LARGO',
            'FECHA_DESEADA_INVALIDA',
            'FECHA_DESEADA_PASADA',
            'MENSAJE_INFORMACION_REQUERIDO',
            'MOTIVO_RECHAZO_REQUERIDO',
            'FECHA_PROPUESTA_INVALIDA',
            'FECHA_PROPUESTA_PASADA',
            'FECHA_PROPUESTA_REQUERIDA',
            'ALCANCE_REQUERIDO',
            'MENSAJE_PROPUESTA_REQUERIDO',
            'MONTO_PROPUESTA_REQUERIDO',
            'MONTO_PROPUESTA_INVALIDO' => 400,

            'SERVICIO_NO_ENCONTRADO',
            'SOLICITUD_NO_ENCONTRADA' => 404,

            'SIN_RESIDENCIA_ACTIVA',
            'PUBLICACION_NO_ES_SERVICIO',
            'SERVICIO_PROPIO',
            'SERVICIO_NO_DISPONIBLE',
            'PROVEEDOR_NO_HABILITADO',
            'SERVICIO_FUERA_DE_RESIDENCIA',
            'SOLICITUD_ACTIVA_EXISTENTE',
            'ESTADO_NO_PERMITE_ACCION' => 409,

            'ROL_NO_AUTORIZADO' => 403,
            default => 500,
        };
    }

    private function responderResultado(array $resultado, int $statusOk = 200): void
    {
        if (!($resultado['ok'] ?? false)) {
            $error = (string)($resultado['error'] ?? 'ERROR_SOLICITUD_SERVICIO');
            $payload = [
                'ok' => false,
                'error' => $error,
                'mensaje' => (string)($resultado['mensaje'] ?? 'No se pudo procesar la solicitud de servicio.'),
            ];

            if ($error === 'SIN_RESIDENCIA_ACTIVA') {
                $payload['redirect'] = rtrim(BASE_URL, '/') . '/mi-perfil';
            }

            $this->json($this->statusError($error), $payload);
            return;
        }

        $this->json($statusOk, [
            'ok' => true,
            'mensaje' => (string)($resultado['mensaje'] ?? 'Operación realizada correctamente.'),
            'data' => $resultado['data'] ?? [],
            'estado' => $resultado['estado'] ?? null,
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
                'fecha_deseada' => $input['fecha_deseada'] ?? null,
                'rango_horario' => $input['rango_horario'] ?? 'a_coordinar',
                'direccion_atencion' => $input['direccion_atencion'] ?? '',
                'mensaje_solicitante' => $input['mensaje_solicitante'] ?? '',
            ]);

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
            $model = new SolicitudServicio();
            $resultado = $model->listarSolicitudesProveedor($codigoProveedor);
            $this->responderResultado($resultado, 200);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][listarProveedor] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'No se pudieron cargar las solicitudes de servicio.',
            ]);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/solicitar-informacion */
    public function solicitarInformacion(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;

        try {
            $codigoProveedor = $this->exigirRolVecino();
            $input = $this->leerInput();
            $model = new SolicitudServicio();
            $resultado = $model->solicitarInformacion(
                $codigoSolicitud,
                $codigoProveedor,
                (string)($input['mensaje'] ?? $input['mensaje_proveedor'] ?? '')
            );
            $this->responderResultado($resultado, 200);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][solicitarInformacion] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'No se pudo solicitar información adicional.',
            ]);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/propuesta */
    public function enviarPropuesta(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;

        try {
            $codigoProveedor = $this->exigirRolVecino();
            $input = $this->leerInput();
            $model = new SolicitudServicio();
            $resultado = $model->enviarPropuesta($codigoSolicitud, $codigoProveedor, $input);
            $this->responderResultado($resultado, 201);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][enviarPropuesta] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'No se pudo enviar la propuesta de coordinación.',
            ]);
        }
    }

    /** POST /api/servicios/solicitudes/{id}/rechazar */
    public function rechazar(int $codigoSolicitud): void
    {
        if (!$this->validarMetodo('POST')) return;

        try {
            $codigoProveedor = $this->exigirRolVecino();
            $input = $this->leerInput();
            $model = new SolicitudServicio();
            $resultado = $model->rechazarSolicitud(
                $codigoSolicitud,
                $codigoProveedor,
                (string)($input['motivo_rechazo'] ?? $input['motivo'] ?? '')
            );
            $this->responderResultado($resultado, 200);
        } catch (Throwable $e) {
            error_log('[EV][apiSolicitudServicioController][rechazar] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'No se pudo rechazar la solicitud de servicio.',
            ]);
        }
    }
}
