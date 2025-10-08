<?php
require_once __DIR__ . '/../models/Menu.php';
$menu = new Menu();
$resultado = $menu->listar();
header('Content-Type: application/json');
echo json_encode($resultado);