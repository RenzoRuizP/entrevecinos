<?php
// controllers/menuPrincipalController.php

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';

class MenuPrincipalController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $token = $_COOKIE['auth_token'] ?? null;
        $usuario = $token ? SesionJWT::verificarToken($token) : null;

        if (!$usuario || empty($usuario['rol'])) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/login');
            exit;
        }

        $rolUsuario = $usuario['rol'];

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
        exit;
    }
}
