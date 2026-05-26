<?php
// controllers/api/apiCalificacionController.php

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/Calificacion.php';

final class apiCalificacionController
{
    private function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function obtenerUsuarioAuth(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);

        if ($codigoUsuario <= 0) {
            $this->json(401, [
                'ok' => false,
                'error' => 'UNAUTHORIZED',
                'mensaje' => 'Tu sesión no es válida.'
            ]);
            exit;
        }

        return $codigoUsuario;
    }

    private function leerInput(): array
    {
        $input = $_POST;

        if (!empty($input)) {
            return is_array($input) ? $input : [];
        }

        $raw = file_get_contents('php://input');

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $json = json_decode($raw, true);

        return is_array($json) ? $json : [];
    }

    public function listarPendientes(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, [
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new Calificacion();
            $resultado = $model->listarPendientesUsuario($codigoUsuario);

            $this->json($resultado['ok'] ? 200 : 500, $resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiCalificacionController][listarPendientes] ' . $e->getMessage());

            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_LISTAR_CALIFICACIONES',
                'mensaje' => 'No se pudieron obtener tus calificaciones pendientes.'
            ]);
        }
    }

    public function obtenerPendientePedido($codigoPedido): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, [
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();
            $codigoPedido = (int)$codigoPedido;

            if ($codigoPedido <= 0) {
                $this->json(400, [
                    'ok' => false,
                    'error' => 'PEDIDO_INVALIDO',
                    'mensaje' => 'Pedido inválido.'
                ]);
                return;
            }

            $model = new Calificacion();
            $pendiente = $model->obtenerPendientePorPedidoUsuario($codigoPedido, $codigoUsuario);

            $this->json(200, [
                'ok' => true,
                'data' => $pendiente
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiCalificacionController][obtenerPendientePedido] ' . $e->getMessage());

            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_OBTENER_CALIFICACION',
                'mensaje' => 'No se pudo obtener la calificación pendiente.'
            ]);
        }
    }

    public function enviar($codigoCalificacion): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();
            $codigoCalificacion = (int)$codigoCalificacion;
            $input = $this->leerInput();

            $puntaje = (int)($input['puntaje'] ?? 0);
            $comentario = trim((string)($input['comentario'] ?? ''));
            $etiquetas = $input['etiquetas'] ?? [];
            $reportarSoporte = (int)($input['reportar_soporte'] ?? 0) === 1;

            if (!is_array($etiquetas)) {
                $etiquetas = [];
            }

            if ($codigoCalificacion <= 0) {
                $this->json(400, [
                    'ok' => false,
                    'error' => 'CALIFICACION_INVALIDA',
                    'mensaje' => 'Calificación inválida.'
                ]);
                return;
            }

            $model = new Calificacion();

            $resultado = $model->enviarCalificacion(
                $codigoCalificacion,
                $codigoUsuario,
                $puntaje,
                $etiquetas,
                $comentario,
                $reportarSoporte
            );

            if (!$resultado['ok']) {
                $error = (string)($resultado['error'] ?? 'ERROR_ENVIAR_CALIFICACION');

                $status = match ($error) {
                    'PARAMETROS_INVALIDOS',
                    'PUNTAJE_INVALIDO',
                    'CALIFICACION_INVALIDA' => 400,

                    'CALIFICACION_NO_ENCONTRADA' => 404,

                    'CALIFICACION_NO_DISPONIBLE',
                    'CALIFICACION_VENCIDA' => 409,

                    default => 500,
                };

                $this->json($status, $resultado);
                return;
            }

            $this->json(200, $resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiCalificacionController][enviar] ' . $e->getMessage());

            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_ENVIAR_CALIFICACION',
                'mensaje' => 'No se pudo registrar la calificación.'
            ]);
        }
    }

    public function reportar($codigoCalificacion): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        $codigoCalificacion = (int)$codigoCalificacion;

        if ($codigoCalificacion <= 0) {
            $this->json(400, [
                'ok' => false,
                'error' => 'CALIFICACION_INVALIDA',
                'mensaje' => 'Calificación inválida.'
            ]);
            return;
        }

        /*
         * MVP EV:
         * El reporte se registra desde enviar() cuando:
         * - puntaje <= 2
         * - reportar_soporte = 1
         *
         * Este endpoint queda reservado para una segunda etapa sin romper rutas futuras.
         */
        $this->json(501, [
            'ok' => false,
            'error' => 'ENDPOINT_RESERVADO',
            'mensaje' => 'El reporte se registra al enviar una calificación baja.'
        ]);
    }
}