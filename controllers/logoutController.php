<?php
// Forzar que no se cachee
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Eliminar la cookie del token JWT
if (isset($_COOKIE['auth_token'])) {
    setcookie("auth_token", "", [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'secure' => true,
        'samesite' => 'Strict'
    ]);
}

// Redirigir al login
header("Location: ../views/login-v2.php");
exit;
