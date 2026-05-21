<?php
// ============================================================
// logoutController.php — Cierra sesión del usuario (Entre Vecinos)
// FIX raíz:
// - Apaga disponibilidad_pedidos antes de eliminar cookie.
// - Responde JSON compatible con menuArriba.js.
// ============================================================

declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../models/SesionJWT.php';

header('Content-Type: application/json; charset=utf-8');

function evLogoutCookieSecure(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
}

function evLogoutCookiePath(): string
{
    if (class_exists('SesionJWT') && method_exists('SesionJWT', 'cookiePath')) {
        return SesionJWT::cookiePath();
    }

    $p = defined('BASE_URL') ? (string)BASE_URL : '/';
    $p = trim($p);

    if ($p === '') {
        return '/';
    }

    if ($p[0] !== '/') {
        $p = '/' . $p;
    }

    $p = rtrim($p, '/');
    return $p === '' ? '/' : $p . '/';
}

function evLogoutApagarDisponibilidad(int $codigoUsuario): void
{
    if ($codigoUsuario <= 0) {
        return;
    }

    try {
        $cn = new Conexion();
        $db = method_exists($cn, 'getDblink') ? $cn->getDblink() : null;

        if (!$db instanceof PDO) {
            return;
        }

        $st = $db->prepare("\n            UPDATE usuario\n            SET disponibilidad_pedidos = 0\n            WHERE codigo_usuario = :id\n              AND disponibilidad_pedidos <> 0\n            LIMIT 1\n        ");
        $st->execute([':id' => $codigoUsuario]);
    } catch (Throwable $e) {
        error_log('[EV][logoutController][evLogoutApagarDisponibilidad] ' . $e->getMessage());
    }
}

function evLogoutEliminarCookies(): void
{
    try {
        SesionJWT::eliminarToken();
    } catch (Throwable $e) {
        error_log('[EV][logoutController][SesionJWT::eliminarToken] ' . $e->getMessage());
    }

    // Compatibilidad con cookie antigua jwt_token.
    if (isset($_COOKIE['jwt_token'])) {
        setcookie('jwt_token', '', [
            'expires'  => time() - 3600,
            'path'     => evLogoutCookiePath(),
            'secure'   => evLogoutCookieSecure(),
            'httponly' => true,
            'samesite' => evLogoutCookieSecure() ? 'None' : 'Lax',
        ]);
        unset($_COOKIE['jwt_token']);
    }
}

try {
    $auth = $GLOBALS['EV_AUTH'] ?? [];
    $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);

    if ($codigoUsuario <= 0) {
        $token = $_COOKIE['auth_token'] ?? null;
        $data = $token ? SesionJWT::verificarToken((string)$token) : null;
        $codigoUsuario = is_array($data) ? (int)($data['codigo_usuario'] ?? 0) : 0;
    }

    evLogoutApagarDisponibilidad($codigoUsuario);
    evLogoutEliminarCookies();

    echo json_encode([
        'ok'       => true,
        'success'  => true,
        'message'  => 'Sesión cerrada correctamente.',
        'mensaje'  => 'Sesión cerrada correctamente.',
        'redirect' => rtrim((string)BASE_URL, '/') . '/login',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Throwable $e) {
    error_log('[EV][logoutController] ' . $e->getMessage());

    echo json_encode([
        'ok'      => false,
        'success' => false,
        'message' => 'Error al cerrar sesión.',
        'mensaje' => 'Error al cerrar sesión.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
