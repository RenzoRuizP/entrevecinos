<?php
// controllers/MenuPrincipalController.php

declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/ConfiguracionPlataforma.php';

class MenuPrincipalController
{
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        $token = $_COOKIE['auth_token'] ?? null;
        $usuario = $token ? SesionJWT::verificarToken((string)$token) : null;

        if (!$usuario || empty($usuario['rol'])) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/login');
            exit;
        }

        $codigoUsuario = (int)($usuario['codigo_usuario'] ?? 0);

        if ($codigoUsuario > 0) {
            try {
                $usuarioModel = new Usuario();
                $fotoPerfil = $usuarioModel->obtenerFotoPerfil($codigoUsuario);
                $usuario['foto_perfil'] = $fotoPerfil;
            } catch (Throwable $e) {
                error_log('[EV][MenuPrincipalController][foto_perfil] ' . $e->getMessage());
                $usuario['foto_perfil'] = (string)($usuario['foto_perfil'] ?? '');
            }
        }

        $rolUsuario = strtolower(trim((string)$usuario['rol']));

        $evGotoActual = trim((string)($_GET['ev_goto'] ?? ''));

        if ($rolUsuario === 'administrador_comunidad' && $evGotoActual === '') {
            $destino = rtrim(BASE_URL, '/')
                . '/MenuPrincipal?ev_goto='
                . rawurlencode('/comunidad/gestionar');

            if (trim((string)($_GET['success'] ?? '')) === 'login_exitoso') {
                $destino .= '&success=login_exitoso';
            }

            header('Location: ' . $destino, true, 302);
            exit;
        }

        $objSesion = new SesionJWT();
        $menusBase = $objSesion->obtenerOpcionesMenu($rolUsuario);

        $menus = [];

        foreach ($menusBase as $menu) {
            $codigoMenu = (int)($menu['codigo_menu'] ?? 0);

            if ($codigoMenu <= 0) {
                continue;
            }

            $submenus = $objSesion->obtenerOpcionesMenuItem($rolUsuario, $codigoMenu);

            if (!is_array($submenus) || empty($submenus)) {
                continue;
            }

            $menu['submenus'] = $submenus;
            $menus[] = $menu;
        }

        try {
            $configuracionPlataforma = new ConfiguracionPlataforma();
            $menus = $configuracionPlataforma->filtrarMenus($menus, $usuario);
            $evFuncionalidades = $configuracionPlataforma->listarFuncionalidadesResueltas($usuario, true);
            $evMonetizacion = $configuracionPlataforma->listarMonetizacionResuelta($usuario);
        } catch (Throwable $e) {
            // Compatibilidad segura mientras el script SQL todavía no haya sido ejecutado.
            error_log('[EV][MenuPrincipalController][configuracion_plataforma] ' . $e->getMessage());
            $evFuncionalidades = [];
            $evMonetizacion = [];
        }

        $menusParaMenuIzquierda = $menus;

        require_once __DIR__ . '/../views/MenuPrincipalView.php';
        exit;
    }
}
