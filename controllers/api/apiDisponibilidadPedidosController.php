<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/DisponibilidadPedido.php';

class apiDisponibilidadPedidosController
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
                'ok'       => false,
                'error'    => 'UNAUTHORIZED',
                'mensaje'  => 'Tu sesión no es válida.',
                'redirect' => rtrim(BASE_URL, '/') . '/login'
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

    public function obtenerEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new DisponibilidadPedido();
            $estado = $model->obtenerEstadoWidget($codigoUsuario);

            $this->json(200, [
                'ok'   => true,
                'data' => $estado
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiDisponibilidadPedidosController][obtenerEstado] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'error'   => 'ERROR_OBTENER_DISPONIBILIDAD',
                'mensaje' => 'No se pudo obtener tu disponibilidad para recibir pedidos.'
            ]);
            return;
        }
    }

    public function actualizarEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, [
                'ok'      => false,
                'mensaje' => 'Método no permitido.'
            ]);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();
            $input = $this->leerInput();

            $raw = $input['disponibilidad'] ?? null;

            if ($raw === null || $raw === '') {
                $this->json(400, [
                    'ok'      => false,
                    'error'   => 'DISPONIBILIDAD_REQUERIDA',
                    'mensaje' => 'Debes indicar si deseas conectarte o desconectarte.'
                ]);
                return;
            }

            $disponibilidad = ((int)$raw === 1) ? 1 : 0;

            $model = new DisponibilidadPedido();

            if ($disponibilidad === 1 && !$model->usuarioTieneProductosPublicados($codigoUsuario)) {
                $this->json(409, [
                    'ok'      => false,
                    'error'   => 'SIN_PRODUCTOS_PUBLICADOS',
                    'mensaje' => 'No tienes publicaciones aprobadas para activar la recepción de pedidos.'
                ]);
                return;
            }

            $model->actualizarDisponibilidad($codigoUsuario, $disponibilidad);

            $this->json(200, [
                'ok'      => true,
                'mensaje' => $disponibilidad === 1
                    ? 'Ahora estás conectado y disponible para recibir solicitudes.'
                    : 'Ahora estás desconectado y no recibirás nuevas solicitudes.',
                'data' => [
                    'disponibilidad' => $disponibilidad,
                    'estado_texto'   => $disponibilidad === 1 ? 'Conectado' : 'Desconectado'
                ]
            ]);
            return;
        } catch (Throwable $e) {
            error_log('[EV][apiDisponibilidadPedidosController][actualizarEstado] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'error'   => 'ERROR_ACTUALIZAR_DISPONIBILIDAD',
                'mensaje' => 'No se pudo actualizar tu disponibilidad para recibir pedidos.'
            ]);
            return;
        }
    }
}