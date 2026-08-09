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
        $rolUsuario = strtolower(trim((string)$usuario['rol']));
        $objSesion = new SesionJWT();

        if ($codigoUsuario > 0) {
            try {
                $usuarioModel = new Usuario();
                $usuario['foto_perfil'] = $usuarioModel->obtenerFotoPerfil($codigoUsuario);
            } catch (Throwable $e) {
                error_log('[EV][MenuPrincipalController][foto_perfil] ' . $e->getMessage());
                $usuario['foto_perfil'] = (string)($usuario['foto_perfil'] ?? '');
            }

            try {
                $comunidadActual = $objSesion->obtenerComunidadActual($codigoUsuario, $rolUsuario);
                $usuario = array_merge($usuario, $comunidadActual);
            } catch (Throwable $e) {
                error_log('[EV][MenuPrincipalController][comunidad_actual] ' . $e->getMessage());
            }
        }

        $evGotoActual = trim((string)($_GET['ev_goto'] ?? ''));
        $codigoRol = (int)($usuario['codigo_rol'] ?? 0);
        $adminRoleId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteRoleId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;
        $adminComunidadRoleId = defined('EV_ADMIN_COMUNIDAD_ROLE_ID') ? (int)EV_ADMIN_COMUNIDAD_ROLE_ID : 4;
        $esAdministradorGeneral = in_array($rolUsuario, ['admin', 'administrador'], true)
            || $codigoRol === $adminRoleId;
        $esAdministradorComunidad = $rolUsuario === 'administrador_comunidad'
            || $codigoRol === $adminComunidadRoleId;
        $esSoporte = $rolUsuario === 'soporte' || $codigoRol === $soporteRoleId;
        $esVecino = !$esAdministradorGeneral && !$esAdministradorComunidad && !$esSoporte;

        /*
         * La configuración operativa se resuelve con la residencia activa real,
         * no solo con los datos incluidos en el JWT. Esto evita que un cambio de
         * comunidad o una regla específica quede ignorada hasta el siguiente login.
         */
        $configuracionPlataforma = new ConfiguracionPlataforma();
        $alcanceConfiguracion = ConfiguracionPlataforma::normalizarAlcance(
            ConfiguracionPlataforma::ALCANCE_GLOBAL,
            0
        );

        if ($codigoUsuario > 0) {
            $alcanceConfiguracion = $configuracionPlataforma->obtenerAlcanceUsuario($codigoUsuario);
            $usuario['tipo_conjunto'] = (string)$alcanceConfiguracion['tipo_alcance'];

            if ($alcanceConfiguracion['tipo_alcance'] === ConfiguracionPlataforma::ALCANCE_CONDOMINIO) {
                $usuario['codigo_condominio'] = (int)$alcanceConfiguracion['codigo_alcance'];
                $usuario['codigo_urbanizacion'] = 0;
            } elseif ($alcanceConfiguracion['tipo_alcance'] === ConfiguracionPlataforma::ALCANCE_URBANIZACION) {
                $usuario['codigo_urbanizacion'] = (int)$alcanceConfiguracion['codigo_alcance'];
                $usuario['codigo_condominio'] = 0;
            }
        }

        $evBilleteraFuncionalidad = $configuracionPlataforma->obtenerFuncionalidadPorAlcance(
            ConfiguracionPlataforma::FUNC_BILLETERA,
            (string)$alcanceConfiguracion['tipo_alcance'],
            (int)$alcanceConfiguracion['codigo_alcance']
        );
        $evBilleteraVisibilidad = $configuracionPlataforma->obtenerMonetizacionPorAlcance(
            ConfiguracionPlataforma::MON_BILLETERA_VISIBLE,
            (string)$alcanceConfiguracion['tipo_alcance'],
            (int)$alcanceConfiguracion['codigo_alcance']
        );
        $evRecargasConfiguracion = $configuracionPlataforma->obtenerMonetizacionPorAlcance(
            ConfiguracionPlataforma::MON_RECARGAS,
            (string)$alcanceConfiguracion['tipo_alcance'],
            (int)$alcanceConfiguracion['codigo_alcance']
        );

        $evBilleteraDisponible = (bool)($evBilleteraFuncionalidad['habilitada'] ?? false)
            && (bool)($evBilleteraVisibilidad['valor_booleano'] ?? false);
        $evRecargasDisponibles = $evBilleteraDisponible
            && (bool)($evRecargasConfiguracion['valor_booleano'] ?? false);

        $resolverFuncionalidad = static function (string $clave) use ($configuracionPlataforma, $alcanceConfiguracion): bool {
            $resuelta = $configuracionPlataforma->obtenerFuncionalidadPorAlcance(
                $clave,
                (string)$alcanceConfiguracion['tipo_alcance'],
                (int)$alcanceConfiguracion['codigo_alcance']
            );
            return (bool)($resuelta['habilitada'] ?? false);
        };

        $evMarketplaceDisponible = $resolverFuncionalidad(ConfiguracionPlataforma::FUNC_MARKETPLACE);
        $evComprarProductosDisponible = $resolverFuncionalidad(ConfiguracionPlataforma::FUNC_COMPRAR_PRODUCTOS);
        $evSolicitarServiciosDisponible = $resolverFuncionalidad(ConfiguracionPlataforma::FUNC_SOLICITAR_SERVICIOS);
        $evPublicarProductosDisponible = $resolverFuncionalidad(ConfiguracionPlataforma::FUNC_PUBLICAR_PRODUCTOS);
        $evPublicarServiciosDisponible = $resolverFuncionalidad(ConfiguracionPlataforma::FUNC_PUBLICAR_SERVICIOS);
        $evCrearPublicacionDisponible = $evPublicarProductosDisponible || $evPublicarServiciosDisponible;

        if ($evGotoActual === '' && ($esAdministradorGeneral || $esAdministradorComunidad)) {
            $rutaInicio = $esAdministradorGeneral
                ? '/dashboard-gerencial'
                : '/comunidad/gestionar';

            $destino = rtrim(BASE_URL, '/')
                . '/MenuPrincipal?ev_goto='
                . rawurlencode($rutaInicio);

            if (trim((string)($_GET['success'] ?? '')) === 'login_exitoso') {
                $destino .= '&success=login_exitoso';
            }

            header('Location: ' . $destino, true, 302);
            exit;
        }

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

        /*
         * El vecino siempre dispone de un punto de retorno claro al panel principal.
         * Se crea en memoria para no depender de una migración de menú y se conserva
         * el menú "Mi cuenta" existente como bloque independiente.
         */
        if ($esVecino) {
            $menus = $configuracionPlataforma->filtrarMenus($menus, $usuario);

            $tieneInicio = false;
            foreach ($menus as $menuExistente) {
                foreach (($menuExistente['submenus'] ?? []) as $submenuExistente) {
                    $rutaExistente = '/' . trim((string)($submenuExistente['ruta'] ?? ''), '/');
                    if (strcasecmp($rutaExistente, '/MenuPrincipal') === 0) {
                        $tieneInicio = true;
                        break 2;
                    }
                }
            }

            if (!$tieneInicio) {
                array_unshift($menus, [
                    'codigo_menu' => 989001,
                    'nombre' => 'Inicio',
                    'icono' => 'bi bi-house-door',
                    'orden' => 0,
                    'submenus' => [[
                        'codigo_menu_item' => 989101,
                        'nombre' => 'Menú principal',
                        'icono' => 'bi bi-speedometer2',
                        'ruta' => '/MenuPrincipal',
                        'orden' => 1,
                    ]],
                ]);
            }
        }

        /*
         * Soporte también dispone de un punto de retorno explícito al Panel de Soporte.
         * Se inyecta en memoria para no alterar la matriz de permisos persistida.
         */
        if ($esSoporte) {
            $tieneInicioSoporte = false;

            foreach ($menus as $menuExistente) {
                foreach (($menuExistente['submenus'] ?? []) as $submenuExistente) {
                    $rutaExistente = '/' . trim((string)($submenuExistente['ruta'] ?? ''), '/');
                    if (strcasecmp($rutaExistente, '/MenuPrincipal') === 0) {
                        $tieneInicioSoporte = true;
                        break 2;
                    }
                }
            }

            if (!$tieneInicioSoporte) {
                array_unshift($menus, [
                    'codigo_menu' => 989002,
                    'nombre' => 'Inicio',
                    'icono' => 'bi bi-house-door',
                    'orden' => 0,
                    'submenus' => [[
                        'codigo_menu_item' => 989102,
                        'nombre' => 'Panel de Soporte',
                        'icono' => 'bi bi-speedometer2',
                        'ruta' => '/MenuPrincipal',
                        'orden' => 1,
                    ]],
                ]);
            }
        }

        /*
         * Menú exclusivo del administrador general del sistema.
         * Este perfil no hereda opciones del administrador de comunidad ni del vecino.
         */
        if ($esAdministradorGeneral) {
            $itemsPorRuta = [];

            foreach ($menus as $menu) {
                foreach (($menu['submenus'] ?? []) as $submenu) {
                    $ruta = trim((string)($submenu['ruta'] ?? ''));
                    $rutaPath = (string)(parse_url($ruta, PHP_URL_PATH) ?? $ruta);
                    $rutaPath = '/' . trim((string)preg_replace('#/+#', '/', $rutaPath), '/');
                    $itemsPorRuta[$rutaPath] = $submenu;
                }
            }

            $idsSinteticos = [
                '/dashboard-gerencial' => 990101,
                '/marketplace' => 990201,
                '/comunidad' => 990301,
                '/atender-cuentas' => 990401,
                '/atender-publicacion' => 990402,
                '/atender-recargas' => 990403,
                '/atender-servicios' => 990404,
                '/configuracion-plataforma' => 990501,
            ];

            $crearItem = static function (string $ruta, string $nombre, string $icono, int $orden) use ($itemsPorRuta, $idsSinteticos): array {
                $item = $itemsPorRuta[$ruta] ?? [];
                return array_merge([
                    'codigo_menu_item' => $idsSinteticos[$ruta] ?? (990900 + $orden),
                    'nombre' => $nombre,
                    'icono' => $icono,
                    'ruta' => $ruta,
                    'orden' => $orden,
                ], $item, [
                    'nombre' => $nombre,
                    'icono' => $icono,
                    'ruta' => $ruta,
                    'orden' => $orden,
                ]);
            };

            $menus = [
                [
                    'codigo_menu' => 990001,
                    'nombre' => 'Inicio',
                    'icono' => 'bi bi-house-door',
                    'orden' => 1,
                    'submenus' => [
                        $crearItem('/dashboard-gerencial', 'Dashboard gerencial', 'bi bi-graph-up-arrow', 1),
                    ],
                ],
                [
                    'codigo_menu' => 990002,
                    'nombre' => 'Comprar',
                    'icono' => 'bi bi-cart',
                    'orden' => 2,
                    'submenus' => [
                        $crearItem('/marketplace', 'Marketplace', 'bi bi-shop', 1),
                    ],
                ],
                [
                    'codigo_menu' => 990003,
                    'nombre' => 'Comunidad',
                    'icono' => 'bi bi-people',
                    'orden' => 3,
                    'submenus' => [
                        $crearItem('/comunidad', 'Comunidad', 'bi bi-people', 1),
                    ],
                ],
                [
                    'codigo_menu' => 990004,
                    'nombre' => 'Soporte',
                    'icono' => 'bi bi-headset',
                    'orden' => 4,
                    'submenus' => [
                        $crearItem('/atender-cuentas', 'Atender cuentas de usuario', 'bi bi-person-check', 1),
                        $crearItem('/atender-publicacion', 'Atender publicaciones', 'bi bi-megaphone', 2),
                        $crearItem('/atender-recargas', 'Atender recargas', 'bi bi-wallet2', 3),
                        $crearItem('/atender-servicios', 'Atención de servicios', 'bi bi-clipboard2-pulse', 4),
                    ],
                ],
                [
                    'codigo_menu' => 990005,
                    'nombre' => 'Administración',
                    'icono' => 'bi bi-sliders2',
                    'orden' => 5,
                    'submenus' => [
                        $crearItem('/configuracion-plataforma', 'Configuración de plataforma', 'bi bi-diagram-3', 1),
                    ],
                ],
            ];
        }

        $menusParaMenuIzquierda = $menus;

        require_once __DIR__ . '/../views/MenuPrincipalView.php';
        exit;
    }
}
