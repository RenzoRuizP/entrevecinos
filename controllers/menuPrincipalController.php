<?php
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class MenuPrincipalController {
    public function index() {
        session_start();

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $usuario = AuthMiddleware::validarToken();

        if (!$usuario) {
            error_log("⚠️ Token no válido o no encontrado. Redirigiendo al login...");
            header("Location: /entrevecinos/views/login.php");
            exit;
        }

        $rolUsuario = $usuario['rol'] ?? null;

        $objSesion = new SesionJWT();
        $menusBase = $objSesion->obtenerOpcionesMenu($rolUsuario);

        $menus = [];
        foreach ($menusBase as $menu) {
            $codigoMenu = $menu['codigo_menu'];
            $submenus = $objSesion->obtenerOpcionesMenuItem($rolUsuario, $codigoMenu);
            $menu['submenus'] = $submenus;
            $menus[] = $menu;
        }

        $menusParaMenuIzquierda = $menus;
        require_once __DIR__ . '/../views/MenuPrincipalView.php';
    }
}
