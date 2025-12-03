<?php
// controllers/recibirPedidosController.php

class recibirPedidosController
{
    public function index()
    {
        // Solo carga la vista central (sin menú ni header, eso ya lo tienes en MenuPrincipalView)
        require_once __DIR__ . '/../views/recibirPedidosView.php';
    }
}
