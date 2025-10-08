<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../resources/util/functions/Helper.class.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Obtener el rol directamente del token (cookie)
    $rol = AuthMiddleware::obtenerRol();
    
    if (!$rol) {
        Helper::imprimeJSON(401, "No autenticado o token inválido", []);
        exit;
    }

    $objSesion = new SesionJWT();
    $resultado = $objSesion->obtenerOpcionesMenu($rol);
    //Helper::imprimeJSON(200, "", ["menus" => $resultado]);
    //exit;
    //Helper::imprimeJSON(200, "", ["menus" => $resultado]);

} catch (Exception $exc) {
    Helper::imprimeJSON(500, $exc->getMessage(), []);
}
