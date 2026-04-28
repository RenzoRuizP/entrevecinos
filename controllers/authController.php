<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../Config/config.php';

class AuthController
{
    public function loginForm(): void
    {
        require __DIR__ . '/../views/login.php';
    }

    private function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    }

    private function cookiePath(): string
    {
        $path = defined('BASE_URL') ? (string)BASE_URL : '/';
        $path = trim($path);

        if ($path === '') {
            $path = '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return rtrim($path, '/') . '/';
    }

    private function cookieOptions(int $expiresAt): array
    {
        $isHttps = $this->isHttps();

        return [
            'expires'  => $expiresAt,
            'path'     => $this->cookiePath(),
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => $isHttps ? 'None' : 'Lax',
        ];
    }

    public function login(): void
    {
        ini_set('display_errors', '0');
        ini_set('html_errors', '0');
        error_reporting(E_ALL);

        header('Content-Type: application/json; charset=utf-8');

        try {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: '';
            $email = trim($email);
            $clave = trim((string)($_POST['clave'] ?? ''));

            if ($email === '' || $clave === '') {
                http_response_code(400);
                echo json_encode([
                    'status'  => 'ERROR',
                    'message' => 'Por favor, completa tu correo y contraseña.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $sesion = new SesionJWT();
            $sesion->setEmail($email);
            $sesion->setClave($clave);

            $resultado = $sesion->iniciarSesionJWT();

            if (!is_array($resultado)) {
                $resultado = ['status' => 'ERROR'];
            }

            $status  = (string)($resultado['status'] ?? 'ERROR');
            $message = (string)($resultado['message'] ?? '');

            switch ($status) {
                case 'SI':
                    $token = (string)($resultado['token'] ?? '');

                    if ($token === '') {
                        http_response_code(500);
                        echo json_encode([
                            'status'  => 'ERROR',
                            'message' => 'No se pudo generar el token de sesión. Intenta nuevamente.',
                        ], JSON_UNESCAPED_UNICODE);
                        return;
                    }

                    $expiraEn = (int)($_ENV['JWT_EXPIRATION_SECONDS'] ?? 3600);
                    if ($expiraEn <= 0) {
                        $expiraEn = 3600;
                    }

                    setcookie('auth_token', $token, $this->cookieOptions(time() + $expiraEn));

                    http_response_code(200);
                    echo json_encode([
                        'status'   => 'SI',
                        'message'  => 'Login exitoso',
                        'redirect' => rtrim(BASE_URL, '/') . '/MenuPrincipal',
                    ], JSON_UNESCAPED_UNICODE);
                    return;

                case 'NE':
                case 'CI':
                    http_response_code(401);
                    echo json_encode([
                        'status'  => $status,
                        'message' => 'El correo o la contraseña no coinciden. Verifica tus datos e inténtalo nuevamente.',
                    ], JSON_UNESCAPED_UNICODE);
                    return;

                case 'IN':
                    http_response_code(403);
                    echo json_encode([
                        'status'  => $status,
                        'message' => $message !== ''
                            ? $message
                            : 'Tu cuenta está inactiva. Si crees que es un error, contáctanos por Soporte.',
                    ], JSON_UNESCAPED_UNICODE);
                    return;

                default:
                    http_response_code(500);
                    echo json_encode([
                        'status'  => 'ERROR',
                        'message' => 'Error interno del servidor. Intenta nuevamente en unos minutos.',
                    ], JSON_UNESCAPED_UNICODE);
                    return;
            }
        } catch (Throwable $e) {
            error_log('AuthController@login error: ' . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'status'  => 'ERROR',
                'message' => 'Estamos presentando una falla temporal. Intenta nuevamente en unos minutos.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', $this->cookieOptions(time() - 3600));
            unset($_COOKIE['auth_token']);
        }

        session_destroy();

        $isAjax = (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'success',
                'message' => 'Has cerrado sesión correctamente.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/');
        exit;
    }
}