<?php
// ============================================================
// 🔐 autenticacion.php — Middleware de validación JWT
// ============================================================

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/SesionJWT.php';

header('Content-Type: application/json');

// 🔹 Nombre coherente de la cookie (mismo que en login y logout)
$cookieName = 'auth_token';

try {
    // 1️⃣ Verificar si la cookie existe
    if (!isset($_COOKIE[$cookieName])) {
        http_response_code(401);
        echo json_encode(['error' => 'Token no encontrado']);
        exit;
    }

    // 2️⃣ Obtener token
    $token = $_COOKIE[$cookieName];

    // 3️⃣ Verificar token JWT
    $jwt = new SesionJWT();
    $datosUsuario = $jwt->verificarToken($token);

    if (!$datosUsuario) {
        // ❌ Token inválido o expirado
        // 🔹 Borrar cookie por seguridad
        setcookie($cookieName, '', time() - 3600, '/entrevecinos', '', true, true);

        http_response_code(401);
        echo json_encode(['error' => 'Token inválido o expirado']);
        exit;
    }

    // ✅ Token válido — opcionalmente puedes devolver los datos
    return $datosUsuario;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al validar token: ' . $e->getMessage()]);
    exit;
}
