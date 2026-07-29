<?php
// controllers/api/apiSoporteRecargasController.php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SoporteRecargas.php';
require_once __DIR__ . '/../../models/ConfiguracionPlataforma.php';
require_once __DIR__ . '/../../middleware/FuncionalidadGuard.php';

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

    private function ok(array $payload): void
    {
        echo json_encode(['ok' => true] + $payload, JSON_UNESCAPED_UNICODE);
    }

    private function fail(int $code, string $error, string $mensaje, array $extra = []): void
    {
        http_response_code($code);
        echo json_encode(['ok' => false, 'error' => $error, 'mensaje' => $mensaje] + $extra, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Lee data del request tanto si viene:
     * - application/json  (fetch con JSON)
     * - multipart/form-data (FormData)
     * - application/x-www-form-urlencoded
     */
    private function requestData(): array
    {
        $ct = strtolower($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');

        if (str_contains($ct, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        }

        // FormData / urlencoded
        if (!empty($_POST)) return $_POST;

        return [];
    }

    // GET /api/soporte/recargas
    public function listar(): void
    {
        FuncionalidadGuard::exigirMonetizacionBooleanaJson(ConfiguracionPlataforma::MON_RECARGAS, true);
        try {
            if (!$this->isSoporteOrAdmin()) {
                $this->fail(403, 'FORBIDDEN', 'Solo Soporte/Admin');
                return;
            }

            $estado = (string)($_GET['estado'] ?? '');
            $rango  = (string)($_GET['rango'] ?? '7');
            $q      = (string)($_GET['q'] ?? '');

            // JS manda page/size
            $page = (int)($_GET['page'] ?? 1);
            $size = (int)($_GET['size'] ?? 10);

            // Compatibilidad si alguien manda per_page
            if (isset($_GET['per_page']) && !isset($_GET['size'])) {
                $size = (int)$_GET['per_page'];
            }

            $model = new SoporteRecargas();
            $resp  = $model->listar([
                'estado' => $estado,
                'rango'  => $rango,
                'q'      => $q,
                'page'   => $page,
                'size'   => $size,
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
    // OJO: SIN type-hint estricto en parámetro para evitar TypeError (router manda string)
    public function actualizarEstado($codigo_recarga): void
    {
        FuncionalidadGuard::exigirMonetizacionBooleanaJson(ConfiguracionPlataforma::MON_RECARGAS, true);
        try {
            if (!$this->isSoporteOrAdmin()) {
                $this->fail(403, 'FORBIDDEN', 'Solo Soporte/Admin');
                return;
            }

            $codigoRecarga = (int)$codigo_recarga;
            if ($codigoRecarga <= 0) {
                $this->fail(422, 'VALIDATION', 'Código de recarga inválido');
                return;
            }

            $in = $this->requestData();

            $estado = strtolower(trim((string)($in['estado'] ?? '')));
            $comentario = isset($in['comentario']) ? trim((string)$in['comentario']) : null;

            $soporteId = $this->authUserId();

            $model = new SoporteRecargas();
            $resp  = $model->actualizarEstado($codigoRecarga, $estado, $comentario, $soporteId);

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

            $this->ok(['data' => $resp['data'], 'mensaje' => 'Estado actualizado.']);
        } catch (\Throwable $e) {
            error_log('[EV][apiSoporteRecargasController::actualizarEstado] ' . $e->getMessage());
            $this->fail(500, 'SERVER_ERROR', 'Error interno al actualizar estado');
        }
    }
}
