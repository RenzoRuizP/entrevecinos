<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/Retiro.php';

final class apiRetiroController
{
    private function json(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function vecino(): ?array
    {
        $auth = $GLOBALS['EV_AUTH'] ?? null;
        if (!is_array($auth) || (int)($auth['codigo_usuario'] ?? 0) <= 0) {
            $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Tu sesión no es válida.']);
            return null;
        }
        $rol = strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
        $codigoRol = (int)($auth['codigo_rol'] ?? 0);
        if ($rol !== 'vecino' && $codigoRol !== 2) {
            $this->json(403, ['ok' => false, 'error' => 'FORBIDDEN', 'mensaje' => 'Los retiros de saldo están disponibles para usuarios vecinos.']);
            return null;
        }
        return $auth;
    }

    private function input(): array
    {
        if (!empty($_POST)) return is_array($_POST) ? $_POST : [];
        $raw = file_get_contents('php://input');
        $json = json_decode((string)$raw, true);
        return is_array($json) ? $json : [];
    }

    private function csrf(): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $esperado = (string)($_SESSION['ev_wallet_csrf'] ?? '');
        $recibido = trim((string)($_SERVER['HTTP_X_EV_CSRF'] ?? ''));
        if ($esperado === '' || $recibido === '' || !hash_equals($esperado, $recibido)) {
            $this->json(419, ['ok' => false, 'error' => 'CSRF', 'mensaje' => 'La sesión de Retirar saldo venció. Vuelve a abrir el módulo.']);
            return false;
        }
        return true;
    }

    public function resumen(): void
    {
        $auth = $this->vecino();
        if (!$auth) return;
        try {
            $data = (new Retiro())->resumenUsuario((int)$auth['codigo_usuario']);
            $this->json(200, ['ok' => true, 'data' => $data]);
        } catch (Throwable $e) {
            error_log('[EV][apiRetiro][resumen] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo cargar la información de retiros.']);
        }
    }

    public function guardarCuenta(): void
    {
        $auth = $this->vecino();
        if (!$auth || !$this->csrf()) return;
        $r = (new Retiro())->guardarCuentaUsuario((int)$auth['codigo_usuario'], $this->input());
        $this->json(($r['ok'] ?? false) ? 200 : 422, $r);
    }

    public function solicitar(): void
    {
        $auth = $this->vecino();
        if (!$auth || !$this->csrf()) return;
        $r = (new Retiro())->solicitarRetiro((int)$auth['codigo_usuario']);
        $error = (string)($r['error'] ?? '');
        $status = ($r['ok'] ?? false) ? 201 : (in_array($error, ['RETIRO_YA_SOLICITADO','SALDO_NO_RETIRABLE','CUENTA_NO_VALIDADA','FUERA_DE_CORTE'], true) ? 409 : 422);
        $this->json($status, $r);
    }
}
