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

                // ✅ Guardamos el token en una cookie
                setcookie('auth_token', $token, [
                    'expires' => time() + 3600, // cambia 3600 por el tiempo que desees
                    'path' => '/',
                    'secure' => false, // true si usas HTTPS
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                // 🔁 Enviar respuesta JSON, no redirección directa
                echo json_encode([
                    'status' => 'SI',
                    'message' => 'Login exitoso',
                    'redirect' => '/entrevecinos/MenuPrincipal'
                ]);
                break;

            case 'NE':
            case 'CI':
            case 'IN':
                echo json_encode(['status' => $resultado['status'], 'message' => $resultado['message']]);
                break;

            default:
                echo json_encode(['status' => 'error', 'message' => 'Error interno']);
                break;
        }
    }


    public function logout() {
    // ✅ Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // ✅ Eliminar cookie JWT
    if (isset($_COOKIE['auth_token'])) {
        setcookie('auth_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => false, // ⚠️ true solo si usas HTTPS
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE['auth_token']);
    }

    // ✅ Destruir sesión PHP (por si existiera)
    session_destroy();

    // ✅ Si es una solicitud AJAX o fetch → responder con JSON
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

    // ✅ Si accede directamente desde el navegador → redirigir al login
    header('Location: /entrevecinos/');
    exit;
}







}