<?php

require_once __DIR__ . '/../models/SesionJWT.php';
require_once '../resources/util/functions/Helper.class.php';

try {
    
    // Recibir la variable POST que envía la vista
    if (!isset($_POST["nombreRol"])) {
        echo json_encode([
            "error" => true,
            "mensaje" => "No se recibió el rol del usuario"
        ]);
        exit;
    }

    $nombreRol = $_POST["nombreRol"];
    $codigo_menu  = $_POST["codigo_menu"];
  
    $objSesion = new SesionJWT();
    $resultadoOpcionesMenuItemBD = $objSesion->obtenerOpcionesMenuItem($nombreRol, $codigo_menu);
    
} catch (Exception $exc) {
    echo json_encode([
        "error" => true,
        "mensaje" => $exc->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}