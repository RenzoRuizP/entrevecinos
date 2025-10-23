<?php
// ============================================================
// 🔹 logoutController.php — Cierra sesión del usuario (Entre Vecinos)
// ============================================================
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';

header('Content-Type: application/json');

try {
    // 🔹 Eliminar cookie JWT (independientemente de su nombre)
    if (isset($_COOKIE['jwt_token'])) {
        setcookie('jwt_token', '', time() - 3600, '/entrevecinos', '', false, true);
    }
    if (isset($_COOKIE['auth_token'])) {
        setcookie('auth_token', '', time() - 3600, '/entrevecinos', '', false, true);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada correctamente.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cerrar sesión: ' . $e->getMessage()
    ]);
    exit;
}
