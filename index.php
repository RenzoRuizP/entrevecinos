<?php
// ============================================================
// index.php — Enrutamiento centralizado (EV)
// Shell único (MenuPrincipal) + parciales para módulos
// + Bloqueo por CUENTA OBSERVADA (vecino) con página dedicada
// ============================================================

declare(strict_types=1);

// ------------------------------
// Helpers (blindaje)
// ------------------------------
function safeRequire(string $path, bool $critical = false): void
{
    if (file_exists($path)) {
        require_once $path;
        return;
    }

    error_log("[EV][INDEX] Missing require: {$path}");

    if ($critical) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Error interno: falta dependencia crítica.\n{$path}";
        exit;
    }
}

/**
 * Detecta si la petición es parcial/AJAX.
 */
function esPeticionParcial(): bool
{
    if (!empty($_GET['partial']) && $_GET['partial'] === '1') return true;

    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    if ($xrw && strtolower($xrw) === 'xmlhttprequest') return true;

    $xp = $_SERVER['HTTP_X_PARTIAL'] ?? '';
    if ($xp === '1') return true;

    return false;
}

function evBaseUrl(): string
{
    $b = defined('BASE_URL') ? (string)BASE_URL : '/';
    $b = trim($b);
    if ($b === '') return '/';
    if ($b[0] !== '/') $b = '/' . $b;
    $b = rtrim($b, '/') . '/';
    $b = preg_replace('#/+#', '/', $b);
    return $b ?: '/';
}

function evBasePathFromBaseUrl(string $baseUrl): string
{
    $path = parse_url($baseUrl, PHP_URL_PATH);
    $path = is_string($path) ? $path : '/';
    $path = preg_replace('#/+#', '/', $path);
    $path = rtrim($path, '/');
    return ($path === '') ? '/' : $path;
}

function evRenderSesionFinalizada(string $loginUrl): void
{
    http_response_code(401);
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sesión finalizada | Entre Vecinos</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'info',
                title: 'Tu sesión ha finalizado',
                text: 'Por tu seguridad, la sesión expiró. Vuelve a iniciar sesión.',
                confirmButtonText: 'Ir al login',
                confirmButtonColor: '#EA7C12',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function () {
                window.location.href = <?php echo json_encode($loginUrl); ?>;
            });
        });
    </script>
    </body>
    </html>
    <?php
    exit;
}

/**
 * ✅ Check rápido (router-level) si el vecino está OBSERVADO.
 * Usa tu tabla: usuario_revision (estado_revision = 3).
 *
 * - Retorna true/false
 * - Si no existe Conexion.php o falla DB, "falla abierto" (false) para NO romper navegación.
 */
function evUsuarioEstaObservado(int $codigoUsuario): bool
{
    try {
        if (!class_exists('Conexion')) {
            return false;
        }

        $cn = new Conexion();

        // ✅ NO acceder a $cn->dblink (es protected). Usar getter.
        if (!method_exists($cn, 'getDblink')) {
            return false;
        }

        /** @var PDO|null $db */
        $db = $cn->getDblink();
        if (!$db) return false;

        $sql = "SELECT estado_revision
                FROM usuario_revision
                WHERE codigo_usuario = :id
                LIMIT 1";
        $st = $db->prepare($sql);
        $st->execute([':id' => $codigoUsuario]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;
        return ((int)($row['estado_revision'] ?? 0) === 3);
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evUsuarioEstaObservado] ' . $e->getMessage());
        return false;
    }
}

/**
 * ✅ Check si el vecino está en REVISIÓN INICIAL
 * Tabla: usuario.estado = 1
 */
function evUsuarioEstaEnRevisionInicial(int $codigoUsuario): bool
{
    try {
        if (!class_exists('Conexion')) {
            return false;
        }

        $cn = new Conexion();

        if (!method_exists($cn, 'getDblink')) {
            return false;
        }

        /** @var PDO|null $db */
        $db = $cn->getDblink();
        if (!$db) return false;

        $sql = "SELECT estado
                FROM usuario
                WHERE codigo_usuario = :id
                LIMIT 1";
        $st = $db->prepare($sql);
        $st->execute([':id' => $codigoUsuario]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        return ((int)($row['estado'] ?? 0) === 1);
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evUsuarioEstaEnRevisionInicial] ' . $e->getMessage());
        return false;
    }
}


/**
 * ✅ Bloqueo por cuenta observada:
 * - Si el usuario (vecino) está observado, solo permitimos:
 *   /cuenta-observada (vista)
 *   /api/cuenta-observada/* (reenviar comprobante)
 *   /logout
 */
function evRutaPermitidaEnObservacion(string $uri): bool
{
    return (
        $uri === '/cuenta-observada'
        || str_starts_with($uri, '/api/cuenta-observada')
        || $uri === '/logout'
    );
}

// ------------------------------
// 1) Dependencias
// ------------------------------
safeRequire(__DIR__ . '/Config/config.php', true);
safeRequire(__DIR__ . '/models/SesionJWT.php', true);

// ✅ IMPORTANTE: Conexion (para check observado en router)
safeRequire(__DIR__ . '/models/Conexion.php'); // no crítico para no romper si aún lo mueves

// ✅ rol admin por defecto (si no lo definiste en config)
if (!defined('EV_ADMIN_ROLE_ID')) {
    define('EV_ADMIN_ROLE_ID', 1);
}

// Controllers (Vistas)
safeRequire(__DIR__ . '/controllers/AuthController.php');
safeRequire(__DIR__ . '/controllers/MenuPrincipalController.php');
safeRequire(__DIR__ . '/controllers/CondominioController.php');
safeRequire(__DIR__ . '/controllers/UrbanizacionController.php');
safeRequire(__DIR__ . '/controllers/UbigeoController.php');
safeRequire(__DIR__ . '/controllers/UserController.php');
safeRequire(__DIR__ . '/controllers/miPerfilController.php');
safeRequire(__DIR__ . '/controllers/productoController.php');
safeRequire(__DIR__ . '/controllers/tipoController.php');
safeRequire(__DIR__ . '/controllers/marketplaceController.php');
safeRequire(__DIR__ . '/controllers/billeteraController.php');
safeRequire(__DIR__ . '/controllers/credencialController.php');
safeRequire(__DIR__ . '/controllers/recibirPedidosController.php');
safeRequire(__DIR__ . '/controllers/atenderRecargasController.php');
safeRequire(__DIR__ . '/controllers/atenderPublicacionController.php');
safeRequire(__DIR__ . '/controllers/atenderCuentasUsuarioController.php');
safeRequire(__DIR__ . '/controllers/notificacionesResidenciaController.php');

// ✅ NUEVO: Cuenta Observada (vista + api)
// (respeta exactamente tus nombres de archivo/clase)
safeRequire(__DIR__ . '/controllers/cuentaObservadaController.php');
safeRequire(__DIR__ . '/controllers/api/apiCuentaObservadaController.php');

// API Controllers existentes
safeRequire(__DIR__ . '/controllers/api/usuarioDatosController.php');
safeRequire(__DIR__ . '/controllers/api/apiBilleteraController.php');
safeRequire(__DIR__ . '/controllers/api/apiPedidoController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteRecargasController.php');
safeRequire(__DIR__ . '/controllers/api/apiRecargaSaldoController.php');
safeRequire(__DIR__ . '/controllers/api/apiProductoController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteProductosController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteUsuariosController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteResidenciasController.php');
safeRequire(__DIR__ . '/controllers/api/apiNotificacionesController.php');
safeRequire(__DIR__ . '/controllers/api/apiNotificacionesResidenciaController.php');
safeRequire(__DIR__ . '/models/Notificacion.php');

// Dashboard soporte
safeRequire(__DIR__ . '/controllers/api/apiSoporteDashboardController.php');
safeRequire(__DIR__ . '/models/SoporteDashboard.php');

// ------------------------------
// 2) Normalización BASE_URL / basePath
// ------------------------------
$baseUrl  = evBaseUrl();                      // "/entrevecinos/"
$basePath = evBasePathFromBaseUrl($baseUrl);  // "/entrevecinos"

// ------------------------------
// 3) Parseo URI
// ------------------------------
$uriFull = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uriFull = is_string($uriFull) ? $uriFull : '/';
$uriFull = preg_replace('#/+#', '/', $uriFull);

if ($basePath !== '/' && str_starts_with($uriFull, $basePath)) {
    $uri = substr($uriFull, strlen($basePath));
} else {
    $uri = $uriFull;
}

$uri = preg_replace('#/+#', '/', $uri);
$uri = rtrim($uri, '/');
$uri = ($uri === '') ? '/' : $uri;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ------------------------------
// 4) Rutas públicas (sin token)
// ------------------------------
$publicRoutes = [
    '#^/$#',
    '#^/login$#',
    '#^/usuarios/registrar$#',

    // filtros por distrito (siguen siendo públicos)
    '#^/condominios$#',
    '#^/urbanizaciones$#',

    // combos antiguos
    '#^/condominios/(\d+)/torres$#',
    '#^/torres/(\d+)/departamentos$#',
    '#^/tipos$#',
    '#^/tipos/(\d+)/categoria_grupo$#',

    // ubigeo
    '#^/ubigeo/departamentos$#',
    '#^/ubigeo/departamentos/(\d+)/provincias$#',
    '#^/ubigeo/provincias/(\d+)/distritos$#',
];

// ------------------------------
// 5) Rutas
// ------------------------------
$routes = [

    // --- Auth / Menú ---
    ['GET',  '#^/$#',              [AuthController::class, 'loginForm'], 'html'],
    ['GET',  '#^/login$#',         [AuthController::class, 'loginForm'], 'html'],
    ['POST', '#^/login$#',         [AuthController::class, 'login'],     'json'],
    ['GET',  '#^/MenuPrincipal$#', [MenuPrincipalController::class, 'index'], 'html'],

    // ✅ CUENTA OBSERVADA (vecino)
    ['GET',  '#^/cuenta-observada$#', [cuentaObservadaController::class, 'index'], 'html'],
    // Subir nuevo comprobante desde pantalla de observado
    ['POST', '#^/api/cuenta-observada/reenviar$#', [apiCuentaObservadaController::class, 'reenviar'], 'json'],

    // --- Condominios ---
    ['GET',  '#^/condominios$#',                [CondominioController::class, 'listar'],              'json'],
    ['GET',  '#^/condominios/(\d+)/torres$#',   [CondominioController::class, 'listarTorres'],        'json'],
    ['GET',  '#^/torres/(\d+)/departamentos$#', [CondominioController::class, 'listarDepartamentos'], 'json'],

    // --- Urbanizaciones ---
    ['GET',  '#^/urbanizaciones$#',             [UrbanizacionController::class, 'listar'],            'json'],

    // --- Ubigeo ---
    ['GET',  '#^/ubigeo/departamentos$#',                  [UbigeoController::class, 'departamentos'], 'json'],
    ['GET',  '#^/ubigeo/departamentos/(\d+)/provincias$#', [UbigeoController::class, 'provincias'],    'json'],
    ['GET',  '#^/ubigeo/provincias/(\d+)/distritos$#',     [UbigeoController::class, 'distritos'],     'json'],

    // --- Tipos / Categorías ---
    ['GET',  '#^/tipos$#',                       [tipoController::class, 'listar'],                'json'],
    ['GET',  '#^/tipos/(\d+)/categoria_grupo$#', [tipoController::class, 'listarCategoria_grupo'], 'json'],

    // --- Registro ---
    ['POST', '#^/usuarios/registrar$#', [UserController::class, 'registrar'], 'json'],

    // --- Logout ---
    ['GET',  '#^/logout$#', [AuthController::class, 'logout'], 'html'],
    ['POST', '#^/logout$#', [AuthController::class, 'logout'], 'json'],

    // --- Vistas (módulos) ---
    ['GET', '#^/mi-perfil$#',        [miPerfilController::class, 'index'],        'html'],
    ['GET', '#^/publicacion$#',      [productoController::class, 'index'],        'html'], // legacy
    ['GET', '#^/producto$#',         [productoController::class, 'index'],        'html'],
    ['GET', '#^/marketplace$#',      [marketplaceController::class, 'index'],     'html'],
    ['GET', '#^/billetera$#',        [billeteraController::class, 'index'],       'html'],
    ['GET', '#^/credencial$#',       [credencialController::class, 'index'],      'html'],
    ['GET', '#^/recibir$#',          [recibirPedidosController::class, 'index'],  'html'],
    ['GET', '#^/atender-recargas$#', [atenderRecargasController::class, 'index'], 'html'],
    ['GET', '#^/atender-publicacion$#', [atenderPublicacionController::class, 'index'], 'html'],
    ['GET', '#^/atender-cuentas$#',  [atenderCuentasUsuarioController::class, 'index'], 'html'],
    ['GET', '#^/atender-cuentas-usuario$#', [atenderCuentasUsuarioController::class, 'index'], 'html'],

    // --- API Usuario (legacy) ---
    ['GET',  '#^/api/usuario/datos$#',       [usuarioDatosController::class, 'obtenerDatos'],     'json'],
    ['POST', '#^/api/usuario/actualizar$#',  [usuarioDatosController::class, 'actualizarDatos'],  'json'],
    ['POST', '#^/api/usuario/cambiar-clave$#', [usuarioDatosController::class, 'cambiarClave'], 'json'],
    ['POST', '#^/api/usuario/solicitar-cambio-residencia$#', [usuarioDatosController::class, 'solicitarCambioResidencia'], 'json'],

    // ✅ Guardado por secciones (no rompe legacy)
    ['POST', '#^/api/usuario/actualizar-telefono$#',   [usuarioDatosController::class, 'actualizarTelefono'],   'json'],
    ['POST', '#^/api/usuario/actualizar-residencia$#', [usuarioDatosController::class, 'actualizarResidencia'], 'json'],
    ['POST', '#^/api/usuario/actualizar-cuenta$#', [usuarioDatosController::class, 'cambiarClave'], 'json'],

    // --- API Producto ---
    ['POST', '#^/api/producto/registrar$#',        [apiProductoController::class, 'registrarProducto'],  'json'],
    ['GET',  '#^/api/producto/listar$#',           [apiProductoController::class, 'listarProductos'],    'json'],
    ['GET',  '#^/api/producto/(\d+)$#',            [apiProductoController::class, 'obtenerProducto'],    'json'],
    ['POST', '#^/api/producto/(\d+)/actualizar$#', [apiProductoController::class, 'actualizarProducto'], 'json'],
    ['POST', '#^/api/producto/(\d+)/anular$#',     [apiProductoController::class, 'anularProducto'],     'json'],
    ['POST', '#^/api/producto/(\d+)/publicar$#',   [apiProductoController::class, 'publicarProducto'],   'json'],
    ['GET',  '#^/api/producto/marketplace$#',      [apiProductoController::class, 'listarMarketplace'],  'json'],

    // --- API Billetera ---
    ['GET',  '#^/api/billetera/saldo$#',               [apiBilleteraController::class, 'obtenerSaldo'],        'json'],
    ['GET',  '#^/api/billetera/movimientos$#',         [apiBilleteraController::class, 'obtenerMovimientos'],  'json'],
    ['POST', '#^/api/billetera/debitar-publicacion$#', [apiBilleteraController::class, 'debitarPublicacion'],  'json'],
    ['POST', '#^/api/billetera/debitar-producto-destacado$#', [apiBilleteraController::class, 'debitarPublicacion'], 'json'],

    // --- API Pedidos ---
    ['GET',  '#^/api/pedidos/recibir$#',               [apiPedidoController::class, 'listarPedidos'],           'json'],

    // --- API Soporte Recargas ---
    ['GET',  '#^/api/soporte/recargas$#',              [apiSoporteRecargasController::class, 'listar'],           'json'],
    ['POST', '#^/api/soporte/recargas/(\d+)/estado$#', [apiSoporteRecargasController::class, 'actualizarEstado'], 'json'],

    // --- API Soporte Productos ---
    ['GET',  '#^/api/soporte/productos$#',              [apiSoporteProductosController::class, 'listar'], 'json'],
    ['GET',  '#^/api/soporte/productos/(\d+)$#',        [apiSoporteProductosController::class, 'detalle'], 'json'],
    ['POST', '#^/api/soporte/productos/(\d+)/estado$#', [apiSoporteProductosController::class, 'actualizarEstado'], 'json'],

    // ✅ API Soporte Usuarios (Atender cuentas)
    ['GET',  '#^/api/soporte/usuarios$#',              [apiSoporteUsuariosController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/usuarios/(\d+)/estado$#', [apiSoporteUsuariosController::class, 'actualizarEstado'], 'json'],

    // ✅ API Soporte Residencias (Atender cuentas: cambios de residencia)
    ['GET',  '#^/api/soporte/residencias$#',              [apiSoporteResidenciasController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/residencias/(\d+)/estado$#', [apiSoporteResidenciasController::class, 'actualizarEstado'], 'json'],

    // ✅ API Soporte Dashboard
    ['GET',  '#^/api/soporte/dashboard$#', [apiSoporteDashboardController::class, 'resumen'], 'json'],

    // --- API Recargas ---
    ['POST', '#^/api/recargas/registrar$#', [apiRecargaSaldoController::class, 'registrar'], 'json'],

    // --- Vista ---
    ['GET', '#^/notificaciones-residencia$#', [notificacionesResidenciaController::class, 'index'], 'html'],

    // --- API Notificaciones (vecino) ---
    ['GET',  '#^/api/notificaciones$#',             [apiNotificacionesController::class, 'listar'], 'json'],
    ['GET',  '#^/api/notificaciones/counts$#',      [apiNotificacionesController::class, 'counts'], 'json'],
    ['POST', '#^/api/notificaciones/(\d+)/leida$#', [apiNotificacionesController::class, 'marcarLeida'], 'json'],

    // --- API Reenvío residencia desde notificación ---
    ['POST', '#^/api/notificaciones/residencia/(\d+)/reenviar$#', [apiNotificacionesResidenciaController::class, 'reenviar'], 'json'],
];

// ============================================================
// 6) Resolver rutas
// ============================================================
$matched = false;

foreach ($routes as $r) {
    [$httpMethod, $pattern, $handler, $type] = $r;

    if ($method !== $httpMethod) continue;
    if (!preg_match($pattern, $uri, $matches)) continue;

    $matched = true;

    // ¿Pública?
    $isPublic = false;
    foreach ($publicRoutes as $publicPattern) {
        if (preg_match($publicPattern, $uri)) {
            $isPublic = true;
            break;
        }
    }

    // Validación JWT si NO es pública
    if (!$isPublic) {
        $token = $_COOKIE['auth_token'] ?? null;
        $loginUrl = rtrim($baseUrl, '/') . '/login';

        $rTok = SesionJWT::verificarTokenDetallado($token);

        if (!$rTok['ok']) {
            $motivo = ($rTok['error'] ?? '') === 'TOKEN_EXPIRADO' ? 'token_expirado' : 'token_invalido';

            if (esPeticionParcial() || $type === 'json' || str_starts_with($uri, '/api/')) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED', 'motivo' => $motivo]);
                exit;
            }

            evRenderSesionFinalizada($loginUrl);
        }

        $GLOBALS['EV_AUTH'] = $rTok['data'] ?? [];

        // ============================================================
        // ✅ BLOQUEO POR CUENTA OBSERVADA (solo vecino)
        // - Tabla: usuario_revision (estado_revision = 3)
        // - Solo deja pasar /cuenta-observada y /api/cuenta-observada/*
        // ============================================================
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);
        $codigoRol     = (int)($auth['codigo_rol'] ?? 0);

        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        $esVecino = ($codigoUsuario > 0 && $codigoRol !== $adminId && $codigoRol !== $soporteId);

        if ($esVecino && $codigoUsuario > 0) {
            $observado = evUsuarioEstaObservado($codigoUsuario);
            $enRevisionInicial = evUsuarioEstaEnRevisionInicial($codigoUsuario);

            if (($observado || $enRevisionInicial) && !evRutaPermitidaEnObservacion($uri)) {

                $redirect = rtrim($baseUrl, '/') . '/cuenta-observada';

                if (esPeticionParcial() || $type === 'json' || str_starts_with($uri, '/api/')) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(409);
                    echo json_encode([
                        'ok' => false,
                        'error' => 'CUENTA_OBSERVADA',
                        'mensaje' => 'Tu cuenta está observada. Debes reenviar tu comprobante.',
                        'redirect' => $redirect
                    ]);
                    exit;
                }

                header('Location: ' . $redirect, true, 302);
                exit;
            }
        }
    }

    // ============================================================
    // SOLUCIÓN RAÍZ: Deep link a módulos HTML protegidos
    // ============================================================
    if (
        !$isPublic
        && $type === 'html'
        && !esPeticionParcial()
        && $uri !== '/MenuPrincipal'
        && $uri !== '/logout'
        && $uri !== '/login'
        && $uri !== '/'
        && $uri !== '/cuenta-observada'
    ) {
        $target = rtrim($baseUrl, '/') . '/MenuPrincipal?ev_goto=' . urlencode($uri);
        header('Location: ' . $target, true, 302);
        exit;
    }

    // Ejecutar controlador
    [$controllerClass, $action] = $handler;

    if (!class_exists($controllerClass)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'      => false,
            'error'   => 'CONTROLADOR_NO_DISPONIBLE',
            'mensaje' => "No se encontró el controlador {$controllerClass}. Revisa require/autoload."
        ]);
        exit;
    }

    $controller = new $controllerClass();

    if (!headers_sent()) {
        if ($type === 'json') {
            header('Content-Type: application/json; charset=utf-8');
        } else {
            header('Content-Type: text/html; charset=utf-8');
        }
    }

    array_shift($matches);
    call_user_func_array([$controller, $action], $matches);
    break;
}

// ============================================================
// 7) 404
// ============================================================
if (!$matched) {
    $isJson = esPeticionParcial() || str_starts_with($uri, '/api/');
    http_response_code(404);

    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'       => false,
            'error'    => 'RUTA_NO_ENCONTRADA',
            'uri'      => $uri,
            'basePath' => $basePath
        ]);
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');
    echo "<h1 style='font-family:system-ui;padding:24px'>404</h1><p style='font-family:system-ui;padding:0 24px'>Ruta no encontrada.</p>";
}
