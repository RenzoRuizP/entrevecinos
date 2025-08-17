<?php
try {
    require_once __DIR__ . '/../models/SesionJWT.php';
    require_once __DIR__ . '/../resources/util/functions/Helper.class.php';

    // Evitar que el navegador almacene en caché la página tras logout
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    $email = filter_input(INPUT_POST, 'loginEmail', FILTER_SANITIZE_EMAIL);
    $clave = trim($_POST['loginPassword'] ?? '');

    if (empty($email) || empty($clave)) {
        header("Location: ../views/login-v2.php?error=campos_vacios");
        exit;
    }

    $objSesion = new SesionJWT();
    $objSesion->setEmail($email);
    $objSesion->setClave($clave);

    $resultado = $objSesion->iniciarSesionJWT();

    switch ($resultado['status']) {
        case "CI":
            header("Location: ../views/login-v2.php?error=CI"); // contraseña incorrecta
            exit;
        case "IN":
            header("Location: ../views/login-v2.php?error=IN"); // usuario inactivo
            exit;
        case "NE":
            header("Location: ../views/login-v2.php?error=NE"); // usuario no existe
            exit;
        case "SI":
            // Guardar token en cookie segura (opcional)
            /*
            setcookie("auth_token", $resultado['token'], [
                'expires' => time() + 3600,
                'path' => '/',
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Strict'
            ]);
            */

            setcookie("auth_token", $resultado['token'], [
                'expires' => time() + intval($_ENV['JWT_EXPIRATION_SECONDS']),
                'path' => '/',
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Strict'
            ]);

            // Redirigir a vista principal con éxito
            header("Location: ../views/MenuPrincipalView.php?success=login_exitoso");
            exit;
    }

} catch (Exception $exc) {
    error_log($exc->getMessage());
    header("Location: ../views/login-v2.php?error=token_error");
    exit;
}
