<?php
// controllers/api/apiSoporteRecargasController.php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SoporteRecargas.php';

final class apiSoporteRecargasController
{
    private function isSoporteOrAdmin(): bool
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $rol  = (int)($auth['codigo_rol'] ?? 0);
        return ($rol === 3 || $rol === 1);
    }

    private function authUserId(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function jsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function ok(array $payload): void
    {
        echo json_encode(['ok' => true] + $payload);
    }

    private function fail(int $code, string $error, string $mensaje, array $extra = []): void
    {
        http_response_code($code);
        echo json_encode(['ok' => false, 'error' => $error, 'mensaje' => $mensaje] + $extra);
    }

    // GET /api/soporte/recargas
    public function listar(): void
    {
        try {
            if (!$this->isSoporteOrAdmin()) {
                $this->fail(403, 'FORBIDDEN', 'Solo Soporte/Admin');
                return;
            }

            $model = new SoporteRecargas();
            $resp  = $model->listar([
                'estado'   => $_GET['estado'] ?? '',
                'q'        => $_GET['q'] ?? '',
                'page'     => $_GET['page'] ?? 1,
                'per_page' => $_GET['per_page'] ?? 10,
            ]);

            if (!($resp['ok'] ?? false)) {
                $error = (string)($resp['error'] ?? 'ERROR');
                $msg   = (string)($resp['mensaje'] ?? 'Solicitud inválida');

                $code = 400;
                if ($error === 'VALIDATION') $code = 422;

                $this->fail($code, $error, $msg);
                return;
            }

            $this->ok(['data' => $resp['data']]);
        } catch (\Throwable $e) {
            error_log('[EV][apiSoporteRecargasController::listar] ' . $e->getMessage());
            $this->fail(500, 'SERVER_ERROR', 'Error interno al listar recargas');
        }
    }

    // POST /api/soporte/recargas/{id}/estado
    public function actualizarEstado(int $codigo_recarga): void
    {
        try {
            if (!$this->isSoporteOrAdmin()) {
                $this->fail(403, 'FORBIDDEN', 'Solo Soporte/Admin');
                return;
            }

            $in = $this->jsonInput();
            $estado = (string)($in['estado'] ?? '');
            $comentario = isset($in['comentario']) ? (string)$in['comentario'] : null;

            $soporteId = $this->authUserId();

            $model = new SoporteRecargas();
            $resp  = $model->actualizarEstado((int)$codigo_recarga, $estado, $comentario, $soporteId);

            if (!($resp['ok'] ?? false)) {
                $error = (string)($resp['error'] ?? 'ERROR');
                $msg   = (string)($resp['mensaje'] ?? 'Solicitud inválida');

                $code = 400;
                if ($error === 'VALIDATION') $code = 422;
                if ($error === 'NOT_FOUND') $code = 404;
                if ($error === 'UNAUTHORIZED') $code = 401;
                if ($error === 'WALLET_INCONSISTENT') $code = 409;

                $this->fail($code, $error, $msg);
                return;
            }

            $this->ok(['data' => $resp['data']]);
        } catch (\Throwable $e) {
            error_log('[EV][apiSoporteRecargasController::actualizarEstado] ' . $e->getMessage());
            $this->fail(500, 'SERVER_ERROR', 'Error interno al actualizar estado');
        }
    }
}
