<?php
// controllers/api/apiSoporteDashboardController.php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SoporteDashboard.php';

final class apiSoporteDashboardController
{
    private function rolActual(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_rol'] ?? 0);
    }

    private function puedeAccederSoporte(): bool
    {
        // Admin=1 (EV_ADMIN_ROLE_ID) y Soporte=3
        $adminId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        $rol = $this->rolActual();
        return ($rol === $adminId || $rol === $soporteId);
    }

    private function json(int $code, array $payload): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');

        // Evitar cache de respuesta en navegadores/proxy
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        echo json_encode($payload);
    }

    // GET /api/soporte/dashboard?tiempo=hoy|7d|30d&limit=10
    public function resumen(): void
    {
        if (!$this->puedeAccederSoporte()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $tiempo = strtolower(trim((string)($_GET['tiempo'] ?? 'hoy')));
        $limit  = (int)($_GET['limit'] ?? 10);

        // Blindaje inputs
        if (!in_array($tiempo, ['hoy', '7d', '30d'], true)) $tiempo = 'hoy';
        if ($limit <= 0) $limit = 10;

        try {
            $m = new SoporteDashboard();
            $data = $m->resumen($tiempo, $limit);

            $this->json(200, [
                'ok'   => true,
                'data' => $data
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteDashboardController::resumen] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'Error interno del servidor.']);
        }
    }
}
