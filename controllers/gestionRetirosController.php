<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';

final class gestionRetirosController
{
    public function index(): void
    {
        $auth = $GLOBALS['EV_AUTH'] ?? null;
        if (!is_array($auth)) {
            $token = $_COOKIE['auth_token'] ?? null;
            $auth = $token ? SesionJWT::verificarToken((string)$token) : null;
        }
        $rol = strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
        $codigoRol = (int)($auth['codigo_rol'] ?? 0);
        $esAdmin = $rol === 'admin' || $codigoRol === (defined('EV_ADMIN_ROLE_ID') ? EV_ADMIN_ROLE_ID : 1);
        $esSoporte = $rol === 'soporte' || $codigoRol === (defined('EV_SOPORTE_ROLE_ID') ? EV_SOPORTE_ROLE_ID : 3);

        if (!is_array($auth) || (!$esAdmin && !$esSoporte)) {
            http_response_code(403);
            require __DIR__ . '/../views/comunidadAccesoDenegadoView.php';
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['ev_retiros_csrf']) || !is_string($_SESSION['ev_retiros_csrf'])) {
            $_SESSION['ev_retiros_csrf'] = bin2hex(random_bytes(32));
        }
        $evRetirosCsrf = $_SESSION['ev_retiros_csrf'];
        $evRetirosEsAdmin = $esAdmin;
        $evRetirosEsSoporte = $esSoporte;

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Partial-Ok: 1');
        require __DIR__ . '/../views/gestionRetirosView.php';
    }
}
