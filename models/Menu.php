<?php

require_once '../data/Conexion.class.php';

class Menu extends Conexion {
    private $codigoMenu;
    private $nombre;
    
    function getCodigoMenu() {
        return $this->codigoMenu;
    }

    function getNombre() {
        return $this->nombre;
    }

    function setCodigoMenu($codigoMenu) {
        $this->codigoMenu = $codigoMenu;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function listar() {
        try {
            $sql = "select codigo_menu, nombre from menu order by 2";
           // $sentencia = $this->dbLink->prepare($sql);
            //$sentencia->execute();
           // $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
           // return $resultado;
        } catch (Exception $exc) {
            throw $exc;
        }
    }
    
}
