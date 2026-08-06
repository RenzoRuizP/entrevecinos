<?php
// controllers/atenderCuentasUsuarioController.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/Producto.php';

final class atenderCuentasUsuarioController
{
    private function isSoporteOrAdmin(): bool
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $rol  = (int)($auth['codigo_rol'] ?? 0);

        // Reglas de negocio:
        // 1 = administrador, 3 = soporte
        return in_array($rol, [1, 3], true);
    }

    public function index(): void
    {
        if (!$this->isSoporteOrAdmin()) {
            http_response_code(403);
            echo "<h1 style='font-family:system-ui;padding:24px'>403</h1>
                  <p style='font-family:system-ui;padding:0 24px'>Acceso restringido.</p>";
            return;
        }

        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $esAdministradorGeneralConsulta = (int)($auth['codigo_rol'] ?? 0) === 1;
        $comunidadesConsultaAdmin = $esAdministradorGeneralConsulta ? (new Producto())->listarComunidadesActivasMarketplace() : [];
        $evAdminScopeModule = 'cuentas';
        $evAdminScopeDescription = 'Filtra cuentas y cambios de residencia por condominio o urbanización, sin cambiar tu sesión administrativa.';

        require __DIR__ . '/../views/atenderCuentasUsuarioView.php';
    }
}
