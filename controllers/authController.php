<?php
require_once __DIR__ . '/../models/SesionJWT.php';

class AuthController {

    public function loginForm() {
        require __DIR__ . '/../views/login.php';
    }

    public function login() {
        header('Content-Type: application/json; charset=utf-8');
        $email = $_POST['email'] ?? '';
        $clave = $_POST['clave'] ?? '';

        $sesion = new SesionJWT();
        $sesion->setEmail($email);
        $sesion->setClave($clave);
        $resultado = $sesion->iniciarSesionJWT();

        switch ($resultado['status']) {
            case 'SI':
                $token = $resultado['token'];

                // ✅ Guardar el token en cookie segura
                setcookie('auth_token', $token, [
                    'expires' => time() + 3600, // 1 hora
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']), // ✅ true solo si HTTPS
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                echo json_encode([
                    'status' => 'SI',
                    'message' => 'Login exitoso',
                    'redirect' => '/entrevecinos/MenuPrincipal'
                ]);
                break;

            case 'NE':
            case 'CI':
            case 'IN':
                echo json_encode([
                    'status' => $resultado['status'],
                    'message' => $resultado['message']
                ]);
                break;

            default:
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error interno del servidor.'
                ]);
                break;
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ✅ Eliminar cookie JWT
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
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
                'status' => 'success',
                'message' => 'Has cerrado sesión correctamente.'
            ]);
            exit;
        }

        header('Location: /entrevecinos/');
        exit;
    }
}
