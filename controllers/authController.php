<?php

require_once __DIR__ . '/../Config/config.php';

class AuthController {
    public function loginForm() {
        $mensaje = '';

        if (isset($_GET['error'])) {
            switch ($_GET['error']) {
                case 'sin_token':
                case 'token_expirado':
                    $mensaje = 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.';
                    break;
                case 'token_error':
                    $mensaje = 'Hubo un problema con tu sesión. Intenta nuevamente.';
                    break;
                case 'campos_vacios':
                    $mensaje = 'Debes llenar todos los campos.';
                    break;
                case 'CI':
                    $mensaje = 'La contraseña ingresada es incorrecta.';
                    break;
                case 'IN':
                    $mensaje = 'Tu usuario está inactivo. Contacta con soporte.';
                    break;
                case 'NE':
                    $mensaje = 'El usuario no existe en el sistema.';
                    break;
            }
        }

        // 👇 Aquí va tu login.php actual
        require 'views/login.php';
    }

    public function login() {
        try {
            require_once __DIR__ . '/../models/SesionJWT.php';
            require_once __DIR__ . '/../resources/util/functions/Helper.class.php';

            // Evitar cache del navegador
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");

            $email = filter_input(INPUT_POST, 'loginEmail', FILTER_SANITIZE_EMAIL);
            $clave = trim($_POST['loginPassword'] ?? '');

            if (empty($email) || empty($clave)) {
                header("Location: /?error=campos_vacios");
                exit;
            }

            $objSesion = new SesionJWT();
            $objSesion->setEmail($email);
            $objSesion->setClave($clave);

            $resultado = $objSesion->iniciarSesionJWT();

            switch ($resultado['status']) {
                case "CI":
                    header("Location: /?error=CI");
                    exit;
                case "IN":
                    header("Location: /?error=IN");
                    exit;
                case "NE":
                    header("Location: /?error=NE");
                    exit;
                case "SI":
                    // ✅ Guardar token en cookie segura
                    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                        || $_SERVER['SERVER_PORT'] == 443;

                    setcookie("auth_token", $resultado['token'], [
                        'expires' => time() + intval($_ENV['JWT_EXPIRATION_SECONDS']),
                        'path' => '/',
                        'httponly' => true,
                        'secure' => $isHttps,
                        'samesite' => $isHttps ? 'Strict' : 'Lax'
                    ]);

                    // Redirigir a vista principal
                    header("Location: /MenuPrincipal");
                    exit;
            }

        } catch (Exception $exc) {
            error_log($exc->getMessage());
            header("Location: /?error=token_error");
            exit;
        }
    }
}
