<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class MenuPrincipalController {
    public function index() {
        // Validar token y obtener datos del usuario
        $usuario = AuthMiddleware::validarToken();

        if (!$usuario) {
            header("Location: loginView.php");
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
