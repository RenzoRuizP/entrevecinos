<?php
// controllers/misSolicitudesServicioCompradorController.php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/FuncionalidadGuard.php';
require_once __DIR__ . '/../models/ConfiguracionPlataforma.php';

class misSolicitudesServicioCompradorController
{
    public function index(): void
    {
        if (!FuncionalidadGuard::exigirHtml(ConfiguracionPlataforma::FUNC_SOLICITAR_SERVICIOS)) {
            return;
        }
        require_once __DIR__ . '/../views/misSolicitudesServicioCompradorView.php';
    }
}
