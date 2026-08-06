<?php
// controllers/api/apiDashboardController.php
// Endpoint del dashboard principal del vecino - Entre Vecinos

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/Dashboard.php';
require_once __DIR__ . '/../../models/ConfiguracionPlataforma.php';

final class apiDashboardController
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

    public function vecino(): void
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
            $model = new Dashboard();
            $resultado = $model->obtenerDashboardVecino($codigoUsuario);

            $estadoBilletera = (new ConfiguracionPlataforma())->obtenerEstadoBilleteraUsuario($codigoUsuario);
            $billeteraDisponible = (bool)($estadoBilletera['billetera_disponible'] ?? false);
            if (isset($resultado['data']) && is_array($resultado['data'])) {
                $resultado['data']['configuracion_operativa']['billetera_disponible'] = $billeteraDisponible;
                if (!$billeteraDisponible && isset($resultado['data']['resumen']) && is_array($resultado['data']['resumen'])) {
                    unset($resultado['data']['resumen']['saldo_billetera']);
                }
            }

            $this->json($resultado['ok'] ? 200 : 500, $resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiDashboardController][vecino] ' . $e->getMessage());

            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_DASHBOARD_VECINO',
                'mensaje' => 'No se pudo cargar el dashboard principal.'
            ]);
        }
    }
}
