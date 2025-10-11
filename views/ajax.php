<?php
require_once __DIR__ . '/../Config/config.php';

$r = $_GET['r'] ?? '';

switch($r) {
    case 'mi-perfil':
        include __DIR__ . '/MiPerfilView.php';
        break;
    case 'otra-vista':
        include __DIR__ . '/OtraVista.php';
        break;
    default:
        http_response_code(404);
        echo "<h3>Página no encontrada</h3>";
        break;
}
