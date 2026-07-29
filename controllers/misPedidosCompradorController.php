<?php

require_once __DIR__ . '/../middleware/FuncionalidadGuard.php';
require_once __DIR__ . '/../models/ConfiguracionPlataforma.php';
// controllers/misPedidosCompradorController.php

class misPedidosCompradorController
{
    public function index()
    {
        if (!FuncionalidadGuard::exigirHtml(ConfiguracionPlataforma::FUNC_COMPRAR_PRODUCTOS)) {
            return;
        }
        require_once __DIR__ . '/../views/misPedidosCompradorView.php';
    }
}