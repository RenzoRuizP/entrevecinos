<?php
// controllers/AuthController.php
declare(strict_types=1);

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/DisponibilidadPedido.php';
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
            'samesite' => 'Lax',
        ];
    }

    private function responderJson(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function obtenerCodigoUsuarioAuthActual(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];

        if (is_array($auth)) {
            $codigo = (int)($auth['codigo_usuario'] ?? 0);
            if ($codigo > 0) {
                return $codigo;
            }
        }

        return 0;
    }

    private function extraerCodigoUsuarioDesdeResultado(array $resultado): int
    {
        $keysDirectas = [
            'codigo_usuario',
            'codigoUsuario',
            'id_usuario',
            'idUsuario',
            'user_id',
            'id'
        ];

        foreach ($keysDirectas as $key) {
            if (isset($resultado[$key]) && (int)$resultado[$key] > 0) {
                return (int)$resultado[$key];
            }
        }

        $posiblesBloques = [
            $resultado['usuario'] ?? null,
            $resultado['user'] ?? null,
            $resultado['data'] ?? null,
        ];

        foreach ($posiblesBloques as $bloque) {
            if (!is_array($bloque)) {
                continue;
            }

            foreach ($keysDirectas as $key) {
                if (isset($bloque[$key]) && (int)$bloque[$key] > 0) {
                    return (int)$bloque[$key];
                }
            }

            if (isset($bloque['usuario']) && is_array($bloque['usuario'])) {
                foreach ($keysDirectas as $key) {
                    if (isset($bloque['usuario'][$key]) && (int)$bloque['usuario'][$key] > 0) {
                        return (int)$bloque['usuario'][$key];
                    }
                }
            }
        }

        return 0;
    }

    private function desactivarDisponibilidadPedidos(int $codigoUsuario, string $origen): void
    {
        if ($codigoUsuario <= 0) {
            return;
        }

        try {
            $model = new DisponibilidadPedido();

            if ($origen === 'login') {
                $model->desconectarPorLogin($codigoUsuario);
                return;
            }

            $model->desconectarPorLogout($codigoUsuario);
        } catch (Throwable $e) {
            error_log('[EV][AuthController][desactivarDisponibilidadPedidos][' . $origen . '] ' . $e->getMessage());
        }
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

                    $codigoUsuario = $this->extraerCodigoUsuarioDesdeResultado($resultado);
                    if ($codigoUsuario > 0) {
                        $this->desactivarDisponibilidadPedidos($codigoUsuario, 'login');
                    }

                    $expiraEn = (int)ev_env('JWT_EXPIRATION_SECONDS', 7200);
                    if ($expiraEn <= 0) {
                        $expiraEn = 7200;
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

        $codigoUsuario = $this->obtenerCodigoUsuarioAuthActual();
        $this->desactivarDisponibilidadPedidos($codigoUsuario, 'logout');

        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', $this->cookieOptions(time() - 3600));
            unset($_COOKIE['auth_token']);
        }

        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        $isAjax = (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || $method === 'POST'
          || str_contains(strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json');

        $redirect = rtrim(BASE_URL, '/') . '/login';

        if ($isAjax) {
            $this->responderJson(200, [
                'ok'       => true,
                'success'  => true,
                'status'   => 'success',
                'message'  => 'Has cerrado sesión correctamente.',
                'redirect' => $redirect
            ]);
            exit;
        }

        header('Location: ' . $redirect);
        exit;
    }
}