<?php
// controllers/atenderCuentasUsuarioController.php
require_once __DIR__ . '/../Config/config.php';

class atenderCuentasUsuarioController
{
    private function isAdmin(): bool
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $rol  = (int)($auth['codigo_rol'] ?? 0);
        return $rol === (int)EV_ADMIN_ROLE_ID;
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "<h1 style='font-family:system-ui;padding:24px'>403</h1><p style='font-family:system-ui;padding:0 24px'>Acceso restringido.</p>";
            return;
        }

        include_once __DIR__ . '/../views/atenderCuentasUsuarioView.php';
    }
}
