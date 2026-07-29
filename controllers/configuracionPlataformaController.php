<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';

class configuracionPlataformaController
{
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $_COOKIE['auth_token'] ?? null;
        $usuario = $token ? SesionJWT::verificarToken((string)$token) : null;
        $rol = strtolower(trim((string)($usuario['rol'] ?? $usuario['nombre_rol'] ?? '')));

        if (!is_array($usuario) || $rol !== 'admin') {
            if ($this->esParcial()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'ok' => false,
                    'error' => 'FORBIDDEN',
                    'mensaje' => 'Esta configuración está disponible únicamente para el administrador general de EV.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            header('Location: ' . rtrim(BASE_URL, '/') . '/MenuPrincipal');
            return;
        }

        if (empty($_SESSION['ev_config_csrf']) || !is_string($_SESSION['ev_config_csrf'])) {
            $_SESSION['ev_config_csrf'] = bin2hex(random_bytes(32));
        }
        $evConfigCsrf = $_SESSION['ev_config_csrf'];

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Partial-Ok: 1');
        require __DIR__ . '/../views/configuracionPlataformaView.php';
    }

    private function esParcial(): bool
    {
        return (
            strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
            || (string)($_SERVER['HTTP_X_PARTIAL'] ?? '') === '1'
            || (string)($_GET['partial'] ?? '') === '1'
        );
    }
}
