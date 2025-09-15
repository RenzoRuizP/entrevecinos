<?php
/*
    el middleware "une" diferentes partes de un sistema, asegurando que los componentes puedan interactuar de manera eficiente. 
*/
require_once '../models/SesionJWT.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\ExpiredException;

// Obtener el token JWT desde la cookie
$token = $_COOKIE['auth_token'] ?? null;

// Redirigir si no hay token
if (!$token) {
    header("Location: ../views/login.php");
    exit;
}

try {
    // Validar el token
    $usuario = SesionJWT::verificarToken($token);

    if (!$usuario || !is_object($usuario)) {
        header("Location: ../views/login.php?error=token_expirado");
        exit;
    }

    // Extraer datos del usuario del token (validar existencia)
    $nombreUsuario   = $usuario->nombre ?? '';
    $codigoUsuario   = $usuario->codigo_usuario ?? '';
    $codigoCargo     = $usuario->codigo_cargo ?? '';
    $email           = $usuario->email ?? '';

} catch (Exception $e) {
    header("Location: ../views/login.php?error=token_error");
    exit;
}
