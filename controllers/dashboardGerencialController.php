<?php
declare(strict_types=1);
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';

final class dashboardGerencialController
{
    public function index(): void
    {
        $auth = $GLOBALS['EV_AUTH'] ?? null;
        if (!is_array($auth)) {
            $token=$_COOKIE['auth_token']??null;
            $auth=$token?SesionJWT::verificarToken((string)$token):null;
        }
        $rol=strtolower(trim((string)($auth['rol']??$auth['nombre_rol']??'')));
        if(!is_array($auth)||$rol!=='admin'){
            http_response_code(403);
            require __DIR__.'/../views/comunidadAccesoDenegadoView.php';
            return;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['ev_dashboard_csrf']) || !is_string($_SESSION['ev_dashboard_csrf'])) {
            $_SESSION['ev_dashboard_csrf'] = bin2hex(random_bytes(32));
        }
        $evDashboardCsrf = $_SESSION['ev_dashboard_csrf'];
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Partial-Ok: 1');
        require __DIR__.'/../views/dashboardGerencialView.php';
    }
}
