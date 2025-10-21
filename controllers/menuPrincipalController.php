<?php
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class MenuPrincipalController {
    public function index() {

        session_start();

        // 🔹 Evitar cache del navegador
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        // Validar token y obtener datos del usuario
        $usuario = AuthMiddleware::validarToken();

        if (!$usuario) {
            // 🔹 Redirigir siempre al login
            header("Location: /entrevecinos/views/login.php");
            exit;
        }

        $rolUsuario = $usuario['rol'];

        $objSesion = new SesionJWT();
        $menusBase = $objSesion->obtenerOpcionesMenu($rolUsuario);

        // Agregar submenús a cada menú
        $menus = [];
        foreach ($menusBase as $menu) {
            $codigoMenu = $menu['codigo_menu'];
            $submenus = $objSesion->obtenerOpcionesMenuItem($rolUsuario, $codigoMenu);
            $menu['submenus'] = $submenus;
            $menus[] = $menu;
        }

        // Pasamos los menús a la vista
        $menusParaMenuIzquierda = $menus;
        require_once __DIR__ . '/../views/MenuPrincipalView.php';
    }
}
