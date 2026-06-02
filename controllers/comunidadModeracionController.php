<?php
// controllers/comunidadModeracionController.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';

final class comunidadModeracionController
{
    private function usuarioAuth(): ?array
    {
        $auth = $GLOBALS['EV_AUTH'] ?? null;

        if (is_array($auth) && (int)($auth['codigo_usuario'] ?? 0) > 0) {
            return $auth;
        }

        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token || trim((string)$token) === '') {
            return null;
        }

        $usuario = SesionJWT::verificarToken((string)$token);
        return is_array($usuario) ? $usuario : null;
    }

    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function puedeModerar(array $usuario): bool
    {
        $rol = (int)($usuario['codigo_rol'] ?? 0);
        $adminId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        return in_array($rol, [$adminId, $soporteId], true);
    }

    public function index(): void
    {
        $usuario = $this->usuarioAuth();

        if (!$usuario) {
            $this->json(401, [
                'ok' => false,
                'error' => 'UNAUTHORIZED',
                'mensaje' => 'Tu sesión ha finalizado. Vuelve a iniciar sesión.',
                'redirect' => rtrim(BASE_URL, '/') . '/login',
            ]);
            return;
        }

        if (!$this->puedeModerar($usuario)) {
            http_response_code(403);
            require __DIR__ . '/../views/comunidadAccesoDenegadoView.php';
            return;
        }

        require __DIR__ . '/../views/comunidadModeracionView.php';
    }
}
