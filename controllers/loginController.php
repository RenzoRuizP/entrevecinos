<?php
header("Content-Type: application/json; charset=utf-8");

try {
    require_once __DIR__ . '/../models/SesionJWT.php';
    require_once __DIR__ . '/../resources/util/functions/Helper.class.php';

    // ============================
    // 1) Validación / Sanitización
    // ============================
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $clave = trim($_POST['clave'] ?? '');

    // Email vacío / clave vacía
    if (empty($email) || empty($clave)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'ERROR',
            'code'    => 'CAMPOS_REQUERIDOS',
            'title'   => 'Revisa tus datos',
            'message' => 'Completa tu correo y contraseña para continuar.'
        ]);
        exit;
    }

    // Email inválido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'ERROR',
            'code'    => 'EMAIL_INVALIDO',
            'title'   => 'Correo inválido',
            'message' => 'Ingresa un correo válido (por ejemplo: nombre@correo.com).'
        ]);
        exit;
    }

    // ============================
    // 2) Autenticación
    // ============================
    $objSesion = new SesionJWT();
    $objSesion->setEmail($email);
    $objSesion->setClave($clave);

    $resultado = $objSesion->iniciarSesionJWT();
    $status = $resultado['status'] ?? 'ERROR';

    // Detectar si se usa HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    // Helper: armar redirect robusto (evita ../ relativos frágiles)
    $basePath = '/entrevecinos';
    $redirectOk = $basePath . '/views/MenuPrincipalView.php';

    // ============================
    // 3) Respuestas normalizadas
    // ============================
    switch ($status) {

        // Contraseña incorrecta
        case "CI":
            http_response_code(401);
            echo json_encode([
                'status'  => 'CI', // mantener compatibilidad
                'code'    => 'CREDENCIALES_INVALIDAS',
                'title'   => 'Datos incorrectos',
                'message' => 'El correo o la contraseña no coinciden. Verifica tus datos e inténtalo nuevamente.'
            ]);
            exit;

        // Usuario inactivo / bloqueado
        case "IN":
            http_response_code(403);
            echo json_encode([
                'status'  => 'IN',
                'code'    => 'USUARIO_INACTIVO',
                'title'   => 'Cuenta no disponible',
                'message' => 'Tu cuenta está inactiva. Si crees que es un error, contáctanos por Soporte.'
            ]);
            exit;

        // Usuario no existe
        case "NE":
            // Recomendación UX: por seguridad podrías NO revelar si el usuario existe.
            // Pero mantengo tu lógica actual. Si deseas, lo ajustamos luego.
            http_response_code(404);
            echo json_encode([
                'status'  => 'NE',
                'code'    => 'USUARIO_NO_ENCONTRADO',
                'title'   => 'No pudimos iniciar sesión',
                'message' => 'El correo o la contraseña no coinciden. Verifica tus datos e inténtalo nuevamente.'
            ]);
            exit;

        // Login OK
        case "SI":
            // Cookie JWT segura y coherente con el sistema
            $expSeconds = intval($_ENV['JWT_EXPIRATION_SECONDS'] ?? 3600);

            setcookie("auth_token", $resultado['token'], [
                'expires'  => time() + $expSeconds,
                'path'     => $basePath,
                'httponly' => true,
                'secure'   => $isHttps,
                'samesite' => $isHttps ? 'None' : 'Lax'
            ]);

            http_response_code(200);
            echo json_encode([
                'status'   => 'SI',
                'code'     => 'LOGIN_OK',
                'title'    => '¡Bienvenido!',
                'message'  => 'Inicio de sesión exitoso.',
                'redirect' => $redirectOk
            ]);
            exit;

        // Cualquier otro estado inesperado
        default:
            http_response_code(500);
            echo json_encode([
                'status'  => 'ERROR',
                'code'    => 'RESPUESTA_INESPERADA',
                'title'   => 'Estamos teniendo una demora',
                'message' => 'Ocurrió un problema temporal. Intenta nuevamente en unos minutos.'
            ]);
            exit;
    }

} catch (Exception $exc) {
    error_log($exc->getMessage());
    http_response_code(500);
    echo json_encode([
        'status'  => 'ERROR',
        'code'    => 'ERROR_SERVIDOR',
        'title'   => 'Estamos teniendo una demora',
        'message' => 'Nuestro sistema está presentando una falla temporal. Intenta nuevamente en unos minutos.'
    ]);
    exit;
}
