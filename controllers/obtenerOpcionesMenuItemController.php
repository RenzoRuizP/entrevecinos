<?php
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../resources/util/functions/Helper.class.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST["nombreRol"]) || !isset($_POST["codigo_menu"])) {
        Helper::imprimeJSON(400, "Faltan datos (rol o código de menú)", []);
    }

    $nombreRol = $_POST["nombreRol"];
    $codigo_menu = $_POST["codigo_menu"];

    $objSesion = new SesionJWT();
    $resultado = $objSesion->obtenerOpcionesMenuItem($nombreRol, $codigo_menu);

    Helper::imprimeJSON(200, "", ["menus" => $resultado]);
} catch (Exception $exc) {
    Helper::imprimeJSON(500, $exc->getMessage(), []);
}
