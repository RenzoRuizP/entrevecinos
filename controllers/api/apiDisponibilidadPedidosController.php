<?php
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
                'ok' => false,
                'error' => 'UNAUTHORIZED',
                'mensaje' => 'Tu sesión no es válida.'
            ]);
            exit;
        }

        return $codigoUsuario;
    }

    public function obtenerEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new DisponibilidadPedido();
            $estado = $model->obtenerEstadoWidget($codigoUsuario);

            $this->json(200, [
                'ok' => true,
                'data' => $estado
            ]);
            return;

        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'No se pudo obtener la disponibilidad.',
                'error' => $e->getMessage()
            ]);
            return;
        }
    }

    public function actualizarEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $raw = $_POST['disponibilidad'] ?? null;

            if ($raw === null) {
                $input = json_decode(file_get_contents('php://input'), true);
                $raw = $input['disponibilidad'] ?? null;
            }

            $disponibilidad = ((int)$raw === 1) ? 1 : 0;

            $model = new DisponibilidadPedido();

            if (!$model->usuarioTieneProductosPublicados($codigoUsuario)) {
                $this->json(409, [
                    'ok' => false,
                    'mensaje' => 'No tienes productos publicados para activar disponibilidad.'
                ]);
                return;
            }

            $model->actualizarDisponibilidad($codigoUsuario, $disponibilidad);

            $this->json(200, [
                'ok' => true,
                'mensaje' => $disponibilidad === 1
                    ? 'Ahora estás disponible para recibir solicitudes.'
                    : 'Ahora no estás disponible para recibir solicitudes.',
                'data' => [
                    'disponibilidad' => $disponibilidad
                ]
            ]);
            return;

        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'No se pudo actualizar la disponibilidad.',
                'error' => $e->getMessage()
            ]);
            return;
        }
    }
}