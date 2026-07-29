<?php

require_once __DIR__ . '/../middleware/FuncionalidadGuard.php';
require_once __DIR__ . '/../models/ConfiguracionPlataforma.php';
// controllers/misPedidosVendedorController.php

class misPedidosVendedorController
{
    public function index()
    {
        if (!FuncionalidadGuard::exigirHtml(ConfiguracionPlataforma::FUNC_COMPRAR_PRODUCTOS)) {
            return;
        }
        require_once __DIR__ . '/../views/misPedidosVendedorView.php';
    }
}