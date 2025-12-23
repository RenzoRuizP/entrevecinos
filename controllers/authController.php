<?php
require_once __DIR__ . '/../models/SesionJWT.php';

class AuthController
{
    public function loginForm()
    {
        require __DIR__ . '/../views/login.php';
    }

    public function login()
    {
        // IMPORTANTE: Evitar que warnings/notices contaminen la salida JSON
        ini_set('display_errors', '0');
        ini_set('html_errors', '0');
        error_reporting(E_ALL);

        header('Content-Type: application/json; charset=utf-8');

        try {
            // Sanitizado mínimo (sin cambiar tu flujo)
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: '';
            $clave = trim($_POST['clave'] ?? '');

            if ($email === '' || $clave === '') {
                http_response_code(400);
                echo json_encode([
                    'status'  => 'ERROR',
                    'message' => 'Por favor, completa tu correo y contraseña.'
                ]);
                return;
            }

            $sesion = new SesionJWT();
            $sesion->setEmail($email);
            $sesion->setClave($clave);

            $resultado = $sesion->iniciarSesionJWT();

            // Blindaje para evitar Undefined index / estructuras inesperadas
            if (!is_array($resultado)) {
                $resultado = ['status' => 'ERROR'];
            }

            $status  = (string)($resultado['status'] ?? 'ERROR');
            $message = (string)($resultado['message'] ?? ''); // <- FIX: ya no revienta si no existe

            switch ($status) {
                case 'SI':
                    $token = (string)($resultado['token'] ?? '');

                    // Detectar HTTPS de forma segura
                    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

                    // Cookie coherente con tu app /entrevecinos
                    // Nota: para localhost normalmente no necesitas SameSite=None.
                    setcookie('auth_token', $token, [
                        'expires'  => time() + 3600,          // 1 hora (igual que tu versión)
                        'path'     => '/entrevecinos',        // <- recomendado para tu app
                        'secure'   => $isHttps,               // true solo si HTTPS real
                        'httponly' => true,
                        'samesite' => $isHttps ? 'None' : 'Lax' // si algún día usas HTTPS + cross-site
                    ]);

                    http_response_code(200);
                    echo json_encode([
                        'status'   => 'SI',
                        'message'  => 'Login exitoso',
                        'redirect' => '/entrevecinos/MenuPrincipal'
                    ]);
                    return;

                case 'NE': // No existe
                case 'CI': // Clave incorrecta
                    // Buenas prácticas: mismo mensaje para NE/CI (evita enumeración de usuarios)
                    http_response_code(401);
                    echo json_encode([
                        'status'  => $status,
                        'message' => 'El correo o la contraseña no coinciden. Verifica tus datos e inténtalo nuevamente.'
                    ]);
                    return;

                case 'IN': // Inactivo
                    http_response_code(403);
                    echo json_encode([
                        'status'  => $status,
                        'message' => $message !== '' ? $message : 'Tu cuenta está inactiva. Si crees que es un error, contáctanos por Soporte.'
                    ]);
                    return;

                default:
                    http_response_code(500);
                    echo json_encode([
                        'status'  => 'ERROR',
                        'message' => 'Error interno del servidor. Intenta nuevamente en unos minutos.'
                    ]);
                    return;
            }

        } catch (Throwable $e) {
            error_log('AuthController@login error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status'  => 'ERROR',
                'message' => 'Estamos presentando una falla temporal. Intenta nuevamente en unos minutos.'
            ]);
            return;
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Detectar HTTPS igual que en login
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        // Eliminar cookie JWT (mismo path usado al crearla)
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', [
                'expires'  => time() - 3600,
                'path'     => '/entrevecinos',       // <- debe coincidir con login()
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => $isHttps ? 'None' : 'Lax'
            ]);
            unset($_COOKIE['auth_token']);
        }

        session_destroy();

        $isAjax = (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || ($_SERVER['REQUEST_METHOD'] === 'POST');

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'success',
                'message' => 'Has cerrado sesión correctamente.'
            ]);
            exit;
        }

        header('Location: /entrevecinos/');
        exit;
    }
}
