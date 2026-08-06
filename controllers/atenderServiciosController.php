<?php
// controllers/atenderServiciosController.php

declare(strict_types=1);

require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/Producto.php';

final class atenderServiciosController
{
    public function index(): void
    {
        $token = $_COOKIE['auth_token'] ?? null;
        $usuario = $token ? SesionJWT::verificarToken((string)$token) : null;
        $codigoRol = (int)($usuario['codigo_rol'] ?? 0);

        if (!in_array($codigoRol, [1, 3], true)) {
            http_response_code(403);
            echo "<div class='alert alert-warning m-3'>Acceso restringido.</div>";
            return;
        }

        $esAdministradorGeneralConsulta = $codigoRol === 1;
        $comunidadesConsultaAdmin = $esAdministradorGeneralConsulta ? (new Producto())->listarComunidadesActivasMarketplace() : [];
        $evAdminScopeModule = 'servicios';
        $evAdminScopeDescription = 'Consulta incidencias y atenciones de servicios correspondientes a una comunidad específica.';

        require_once __DIR__ . '/../views/atenderServiciosView.php';
    }
}
