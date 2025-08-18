<?php

require_once '../models/SesionJWT.php';
require_once '../resources/util/functions/Helper.class.php';

try {
    
    /*Recibir la variable POST que le envía la vista*/
    $codigoCargo = $_POST["codigo_cargo_usuario"];
    /*Recibir la variable POST que le envía la vista*/
//    print_r($s_codigoCargo);
    $objSesion = new SesionJWT();
    $resultadoOpcionesMenuBD = $objSesion->obtenerOpcionesMenu($codigoCargo);
            
} catch (Exception $exc) {
    Helper::mensaje($exc->getMessage(), "e");
}
