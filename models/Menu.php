<?php

require_once '../data/Conexion.class.php';

class Menu extends Conexion {
    private $codigoMenu;
    private $nombre;

    // 🔹 Getters y setters
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

    // 🔹 Listar menús principales
    public function listar() {
        try {
            $sql = "SELECT codigo_menu, nombre, icono 
                    FROM menu 
                    WHERE estado = 'A' 
                    ORDER BY orden ASC";

            $sentencia = $this->dblink->prepare($sql);
            $sentencia->execute();
            $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (Exception $exc) {
            throw $exc;
        }
    }

    // 🔹 Listar submenús o ítems de un menú
    public function listarItems($codigoMenu) {
        try {
            $sql = "SELECT codigo_menu_item, nombre, ruta, icono 
                    FROM menu_item 
                    WHERE codigo_menu = :codigo_menu AND estado = 'A' 
                    ORDER BY orden ASC";

            $sentencia = $this->dblink->prepare($sql);
            $sentencia->bindParam(':codigo_menu', $codigoMenu);
            $sentencia->execute();
            $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (Exception $exc) {
            throw $exc;
        }
    }

    // 🔹 Combina menús y submenús en una sola estructura
    public function listarMenuConSubmenu() {
        try {
            $menus = $this->listar();

            foreach ($menus as &$menu) {
                $menu['items'] = $this->listarItems($menu['codigo_menu']);
            }

            return $menus;
        } catch (Exception $exc) {
            throw $exc;
        }
    }
}
