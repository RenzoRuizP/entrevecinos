<?php
header("Content-Type: application/json"); // 👈 Siempre devolver JSON

try {
    require_once __DIR__ . '/../models/SesionJWT.php';
    require_once __DIR__ . '/../resources/util/functions/Helper.class.php';

    // 🔹 Sanitizar datos recibidos
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $clave = trim($_POST['clave'] ?? '');

    if (empty($email) || empty($clave)) {
        http_response_code(400);
        echo json_encode(['status' => 'ERROR', 'message' => 'Campos vacíos']);
        exit;
    }

    $objSesion = new SesionJWT();
    $objSesion->setEmail($email);
    $objSesion->setClave($clave);

    $resultado = $objSesion->iniciarSesionJWT();
   
    switch ($resultado['status']) {
        case "CI":
            http_response_code(401);
            echo json_encode(['status' => 'CI', 'message' => 'Contraseña incorrecta']);
            exit;

        case "IN":
            http_response_code(403);
            echo json_encode(['status' => 'IN', 'message' => 'Usuario inactivo']);
            exit;

        case "NE":
            http_response_code(404);
            echo json_encode(['status' => 'NE', 'message' => 'Usuario no existe']);
            exit;

        case "SI":
            // 🔹 Detectar si se usa HTTPS
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || $_SERVER['SERVER_PORT'] == 443;

            // 🔹 Crear cookie JWT segura y coherente con todo el sistema
            setcookie("auth_token", $resultado['token'], [
                'expires' => time() + intval($_ENV['JWT_EXPIRATION_SECONDS']),
                'path' => '/entrevecinos',
                'httponly' => true,
                'secure' => $isHttps,
                'samesite' => $isHttps ? 'None' : 'Lax'
            ]);

            echo json_encode([
                'status' => 'SI',
                'message' => 'Inicio de sesión exitoso',
                'redirect' => '../views/MenuPrincipalView.php'
            ]);
            exit;
    }

} catch (Exception $exc) {
    error_log($exc->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'ERROR', 'message' => 'Error en el servidor']);
    exit;
}
