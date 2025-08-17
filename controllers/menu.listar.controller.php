<?php

require_once '../logic/Menu.class.php';
require_once '../util/functions/Helper.class.php';

try {
    $objMenu = new Menu();
    $resultado = $objMenu->listar();
    Helper::imprimeJSON(200, "", $resultado);
} catch (Exception $exc) {
    Helper::imprimeJSON(500, $exc->getMessage(), "");
}
