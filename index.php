<?php
// ============================================================
// index.php — Enrutamiento centralizado (EV)
// Shell único (MenuPrincipal) + parciales para módulos
// + Bloqueo por CUENTA OBSERVADA (vecino) con página dedicada
// + Expulsión inmediata en siguiente request si SOPORTE bloquea cuenta
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

/**
 * Render estándar EV para alertas de sesión / seguridad.
 */
function evRenderSecurityAlertPage(
    int $statusCode,
    string $pageTitle,
    string $icon,
    string $title,
    string $text,
    string $confirmText,
    string $confirmColor,
    string $redirectUrl
): void {
    http_response_code($statusCode);
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            :root{
                --ev-verde-oscuro:#0F592F;
                --ev-verde:#16A34A;
                --ev-naranja:#EA7C12;
                --ev-naranja-oscuro:#C46B05;
                --ev-texto:#1F2937;
                --ev-texto-suave:#6B7280;
                --ev-fondo:#F3F4F6;
                --ev-borde:#E5E7EB;
                --ev-sombra:0 24px 60px rgba(15,23,42,.18);
                --ev-radio:22px;
            }

            html, body{
                margin:0;
                padding:0;
                min-height:100%;
                background:linear-gradient(180deg, #F8FAFC 0%, #F3F4F6 100%);
                font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
            }

            .swal2-popup.ev-auth-popup{
                width:min(92vw, 560px) !important;
                border-radius:var(--ev-radio) !important;
                padding:28px 28px 24px !important;
                box-shadow:var(--ev-sombra) !important;
                border:1px solid rgba(229,231,235,.9) !important;
            }

            .swal2-icon.swal2-info,
            .swal2-icon.swal2-warning{
                border-width:3px !important;
                margin-top:4px !important;
                margin-bottom:12px !important;
            }

            .swal2-title.ev-auth-title{
                color:var(--ev-verde-oscuro) !important;
                font-weight:800 !important;
                font-size:2rem !important;
                line-height:1.15 !important;
                letter-spacing:-.02em !important;
                margin:0 0 10px 0 !important;
            }

            .swal2-html-container.ev-auth-text{
                color:var(--ev-texto-suave) !important;
                font-size:1.06rem !important;
                line-height:1.55 !important;
                margin:0 auto 14px auto !important;
                max-width:430px !important;
            }

            .swal2-actions{
                margin-top:14px !important;
            }

            .swal2-confirm.ev-auth-confirm{
                background:linear-gradient(135deg, var(--ev-naranja), #F59E0B) !important;
                color:#fff !important;
                border:none !important;
                border-radius:12px !important;
                padding:12px 22px !important;
                min-width:148px !important;
                font-weight:800 !important;
                font-size:1rem !important;
                box-shadow:0 12px 28px rgba(234,124,18,.35) !important;
                transition:transform .15s ease, box-shadow .15s ease, filter .15s ease !important;
            }

            .swal2-confirm.ev-auth-confirm:hover{
                filter:brightness(1.03) !important;
                transform:translateY(-1px) !important;
                box-shadow:0 16px 34px rgba(234,124,18,.42) !important;
            }

            .swal2-confirm.ev-auth-confirm:focus{
                box-shadow:
                    0 16px 34px rgba(234,124,18,.42),
                    0 0 0 4px rgba(234,124,18,.16) !important;
            }

            .swal2-backdrop-show{
                background:rgba(15,23,42,.36) !important;
                backdrop-filter:blur(2px);
            }

            @media (max-width: 575.98px){
                .swal2-popup.ev-auth-popup{
                    padding:24px 18px 20px !important;
                    border-radius:18px !important;
                }

                .swal2-title.ev-auth-title{
                    font-size:1.7rem !important;
                }

                .swal2-html-container.ev-auth-text{
                    font-size:1rem !important;
                    max-width:none !important;
                }

                .swal2-confirm.ev-auth-confirm{
                    width:100% !important;
                    min-width:0 !important;
                }
            }
        </style>
    </head>
    <body>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: <?php echo json_encode($icon); ?>,
                title: <?php echo json_encode($title); ?>,
                text: <?php echo json_encode($text); ?>,
                confirmButtonText: <?php echo json_encode($confirmText); ?>,
                confirmButtonColor: <?php echo json_encode($confirmColor); ?>,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'ev-auth-popup',
                    title: 'ev-auth-title',
                    htmlContainer: 'ev-auth-text',
                    confirmButton: 'ev-auth-confirm'
                }
            }).then(function () {
                window.location.href = <?php echo json_encode($redirectUrl); ?>;
            });
        });
    </script>
    </body>
    </html>
    <?php
    exit;
}

function evRenderSesionFinalizada(string $loginUrl): void
{
    evRenderSecurityAlertPage(
        401,
        'Sesión finalizada | Entre Vecinos',
        'info',
        'Tu sesión ha finalizado',
        'Por tu seguridad, la sesión expiró. Vuelve a iniciar sesión.',
        'Ir al login',
        '#EA7C12',
        $loginUrl
    );
}

/**
 * ✅ NUEVO: render para cuenta bloqueada/inactiva detectada en la siguiente request.
 */
function evRenderCuentaBloqueada(string $loginUrl): void
{
    evRenderSecurityAlertPage(
        403,
        'Cuenta bloqueada | Entre Vecinos',
        'warning',
        'Tu cuenta fue bloqueada',
        'Por seguridad, se cerró tu sesión. Si necesitas más información, comunícate con soporte.',
        'Ir al login',
        '#EA7C12',
        $loginUrl
    );
}

/**
 * ✅ NUEVO: obtiene el estado actual del usuario desde BD.
 * Tabla: usuario.estado
 * 0 = Inactivo
 * 1 = En revisión
 * 2 = Habilitado
 */
function evObtenerEstadoUsuario(int $codigoUsuario): ?int
{
    try {
        if (!class_exists('Conexion')) {
            return null;
        }

        $cn = new Conexion();

        if (!method_exists($cn, 'getDblink')) {
            return null;
        }

        /** @var PDO|null $db */
        $db = $cn->getDblink();
        if (!$db) return null;

        $sql = "SELECT estado
                FROM usuario
                WHERE codigo_usuario = :id
                LIMIT 1";
        $st = $db->prepare($sql);
        $st->execute([':id' => $codigoUsuario]);

        $valor = $st->fetchColumn();
        if ($valor === false || $valor === null) return null;

        return (int)$valor;
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evObtenerEstadoUsuario] ' . $e->getMessage());
        return null;
    }
}

/**
 * ✅ Check rápido (router-level) si el vecino está OBSERVADO.
 * Usa tu tabla: usuario_revision (estado_revision = 3).
 */
function evUsuarioEstaObservado(int $codigoUsuario): bool
{
    try {
        if (!class_exists('Conexion')) return false;

        $cn = new Conexion();
        if (!method_exists($cn, 'getDblink')) return false;

        /** @var PDO|null $db */
        $db = $cn->getDblink();
        if (!$db) return false;

        $st = $db->prepare("
            SELECT MAX(COALESCE(estado_revision, 0)) AS mx
            FROM usuario_revision
            WHERE codigo_usuario = :id
        ");
        $st->execute([':id' => $codigoUsuario]);
        $mx = (int)$st->fetchColumn();

        return ($mx === 3);
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
    $estado = evObtenerEstadoUsuario($codigoUsuario);
    return ($estado === 1);
}

/**
 * ✅ NUEVO: check si el vecino está bloqueado/inactivo.
 * Tabla: usuario.estado = 0
 */
function evUsuarioEstaBloqueado(int $codigoUsuario): bool
{
    $estado = evObtenerEstadoUsuario($codigoUsuario);
    return ($estado === 0);
}

function evRutaPermitidaEnObservacion(string $uri): bool
{
    return (
        $uri === '/cuenta-observada'
        || str_starts_with($uri, '/api/cuenta-observada')
        || str_starts_with($uri, '/api/notificaciones')
        || str_starts_with($uri, '/api/usuario')
        || $uri === '/logout'
    );
}

// ------------------------------
// 1) Dependencias
// ------------------------------
safeRequire(__DIR__ . '/Config/config.php', true);
safeRequire(__DIR__ . '/models/SesionJWT.php', true);

safeRequire(__DIR__ . '/models/Conexion.php');

if (!defined('EV_ADMIN_ROLE_ID')) {
    define('EV_ADMIN_ROLE_ID', 1);
}
if (!defined('EV_SOPORTE_ROLE_ID')) {
    define('EV_SOPORTE_ROLE_ID', 3);
}

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

safeRequire(__DIR__ . '/controllers/cuentaObservadaController.php');
safeRequire(__DIR__ . '/controllers/api/apiCuentaObservadaController.php');

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

safeRequire(__DIR__ . '/controllers/api/apiSoporteDashboardController.php');
safeRequire(__DIR__ . '/models/SoporteDashboard.php');

safeRequire(__DIR__ . '/controllers/api/apiDisponibilidadPedidosController.php');

safeRequire(__DIR__ . '/controllers/misPedidosCompradorController.php');
safeRequire(__DIR__ . '/controllers/misPedidosVendedorController.php');

// ------------------------------
// 2) Normalización BASE_URL / basePath
// ------------------------------
$baseUrl  = evBaseUrl();
$basePath = evBasePathFromBaseUrl($baseUrl);

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
    '#^/condominios$#',
    '#^/urbanizaciones$#',
    '#^/condominios/(\d+)/torres$#',
    '#^/torres/(\d+)/departamentos$#',
    '#^/tipos$#',
    '#^/tipos/(\d+)/categoria_grupo$#',
    '#^/ubigeo/departamentos$#',
    '#^/ubigeo/departamentos/(\d+)/provincias$#',
    '#^/ubigeo/provincias/(\d+)/distritos$#',
];

// ------------------------------
// 5) Rutas
// ------------------------------
$routes = [
    ['GET',  '#^/$#',              [AuthController::class, 'loginForm'], 'html'],
    ['GET',  '#^/login$#',         [AuthController::class, 'loginForm'], 'html'],
    ['POST', '#^/login$#',         [AuthController::class, 'login'],     'json'],
    ['GET',  '#^/MenuPrincipal$#', [MenuPrincipalController::class, 'index'], 'html'],

    ['GET',  '#^/cuenta-observada$#', [cuentaObservadaController::class, 'index'], 'html'],
    ['POST', '#^/api/cuenta-observada/reenviar$#', [apiCuentaObservadaController::class, 'reenviar'], 'json'],

    ['GET',  '#^/condominios$#',                [CondominioController::class, 'listar'],              'json'],
    ['GET',  '#^/condominios/(\d+)/torres$#',   [CondominioController::class, 'listarTorres'],        'json'],
    ['GET',  '#^/torres/(\d+)/departamentos$#', [CondominioController::class, 'listarDepartamentos'], 'json'],

    ['GET',  '#^/urbanizaciones$#',             [UrbanizacionController::class, 'listar'],            'json'],

    ['GET',  '#^/ubigeo/departamentos$#',                  [UbigeoController::class, 'departamentos'], 'json'],
    ['GET',  '#^/ubigeo/departamentos/(\d+)/provincias$#', [UbigeoController::class, 'provincias'],    'json'],
    ['GET',  '#^/ubigeo/provincias/(\d+)/distritos$#',     [UbigeoController::class, 'distritos'],     'json'],

    ['GET',  '#^/tipos$#',                       [tipoController::class, 'listar'],                'json'],
    ['GET',  '#^/tipos/(\d+)/categoria_grupo$#', [tipoController::class, 'listarCategoria_grupo'], 'json'],

    ['POST', '#^/usuarios/registrar$#', [UserController::class, 'registrar'], 'json'],

    ['GET',  '#^/logout$#', [AuthController::class, 'logout'], 'html'],
    ['POST', '#^/logout$#', [AuthController::class, 'logout'], 'json'],

    ['GET', '#^/mi-perfil$#',        [miPerfilController::class, 'index'],        'html'],
    ['GET', '#^/publicacion$#',      [productoController::class, 'index'],        'html'],
    ['GET', '#^/producto$#',         [productoController::class, 'index'],        'html'],
    ['GET', '#^/marketplace$#',      [marketplaceController::class, 'index'],     'html'],
    ['GET', '#^/billetera$#',        [billeteraController::class, 'index'],       'html'],
    ['GET', '#^/credencial$#',       [credencialController::class, 'index'],      'html'],

    // ✅ Mantener ruta actual por compatibilidad. Internamente será "Mis pedidos".
    ['GET', '#^/recibir$#',          [recibirPedidosController::class, 'index'],  'html'],

    ['GET', '#^/atender-recargas$#', [atenderRecargasController::class, 'index'], 'html'],
    ['GET', '#^/atender-publicacion$#', [atenderPublicacionController::class, 'index'], 'html'],
    ['GET', '#^/atender-cuentas$#',  [atenderCuentasUsuarioController::class, 'index'], 'html'],
    ['GET', '#^/atender-cuentas-usuario$#', [atenderCuentasUsuarioController::class, 'index'], 'html'],

    ['GET', '#^/mis-pedidos-comprador$#', [misPedidosCompradorController::class, 'index'], 'html'],
    ['GET', '#^/mis-pedidos-vendedor$#',  [misPedidosVendedorController::class, 'index'],  'html'],

    ['GET',  '#^/api/usuario/datos$#',       [usuarioDatosController::class, 'obtenerDatos'],     'json'],
    ['POST', '#^/api/usuario/actualizar$#',  [usuarioDatosController::class, 'actualizarDatos'],  'json'],
    ['POST', '#^/api/usuario/cambiar-clave$#', [usuarioDatosController::class, 'cambiarClave'], 'json'],
    ['POST', '#^/api/usuario/solicitar-cambio-residencia$#', [usuarioDatosController::class, 'solicitarCambioResidencia'], 'json'],

    ['POST', '#^/api/usuario/actualizar-telefono$#',   [usuarioDatosController::class, 'actualizarTelefono'],   'json'],
    ['POST', '#^/api/usuario/actualizar-residencia$#', [usuarioDatosController::class, 'actualizarResidencia'], 'json'],
    ['POST', '#^/api/usuario/actualizar-cuenta$#', [usuarioDatosController::class, 'cambiarClave'], 'json'],

    ['POST', '#^/api/producto/registrar$#',        [apiProductoController::class, 'registrarProducto'],  'json'],
    ['GET',  '#^/api/producto/listar$#',           [apiProductoController::class, 'listarProductos'],    'json'],
    ['GET',  '#^/api/producto/(\d+)$#',            [apiProductoController::class, 'obtenerProducto'],    'json'],
    ['GET',  '#^/api/marketplace/producto/(\d+)$#', [apiProductoController::class, 'obtenerDetalleMarketplace'], 'json'],
    ['POST', '#^/api/producto/(\d+)/actualizar$#', [apiProductoController::class, 'actualizarProducto'], 'json'],
    ['POST', '#^/api/producto/(\d+)/anular$#',     [apiProductoController::class, 'anularProducto'],     'json'],
    ['POST', '#^/api/producto/(\d+)/publicar$#',   [apiProductoController::class, 'publicarProducto'],   'json'],
    ['GET',  '#^/api/producto/marketplace$#',      [apiProductoController::class, 'listarMarketplace'],  'json'],

    ['GET',  '#^/api/billetera/saldo$#',               [apiBilleteraController::class, 'obtenerSaldo'],        'json'],
    ['GET',  '#^/api/billetera/movimientos$#',         [apiBilleteraController::class, 'obtenerMovimientos'],  'json'],
    ['POST', '#^/api/billetera/debitar-publicacion$#', [apiBilleteraController::class, 'debitarPublicacion'],  'json'],
    ['POST', '#^/api/billetera/debitar-producto-destacado$#', [apiBilleteraController::class, 'debitarProductoDestacado'], 'json'],

    // ===========================
    // PEDIDOS - COMPRADOR
    // ===========================
    ['GET',  '#^/api/pedidos/recibir$#',    [apiPedidoController::class, 'listarPedidos'],   'json'],
    ['POST', '#^/api/pedidos/registrar$#',  [apiPedidoController::class, 'registrarPedido'],  'json'],
    ['GET',  '#^/api/pedidos/solicitud-activa$#', [apiPedidoController::class, 'obtenerSolicitudActiva'], 'json'],
    ['GET',  '#^/api/pedidos/(\d+)/estado$#',    [apiPedidoController::class, 'obtenerEstadoSolicitud'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/cancelar$#',  [apiPedidoController::class, 'cancelarSolicitud'], 'json'],

    ['POST', '#^/api/pedidos/(\d+)/confirmar-cola$#', [apiPedidoController::class, 'confirmarCola'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/confirmar-entrega$#', [apiPedidoController::class, 'confirmarEntrega'], 'json'],
    ['GET',  '#^/api/pedidos/mis-comprador$#', [apiPedidoController::class, 'listarMisPedidosComprador'], 'json'],


    // ===========================
    // PEDIDOS - VENDEDOR (MIS PEDIDOS)
    // ===========================
    ['GET',  '#^/api/pedidos/mis$#',                [apiPedidoController::class, 'listarMisPedidos'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/aceptar$#',      [apiPedidoController::class, 'aceptarSolicitud'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/rechazar$#',     [apiPedidoController::class, 'rechazarSolicitud'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/estado$#',       [apiPedidoController::class, 'actualizarEstadoPedido'], 'json'],

    ['GET',  '#^/api/soporte/recargas$#',              [apiSoporteRecargasController::class, 'listar'],           'json'],
    ['POST', '#^/api/soporte/recargas/(\d+)/estado$#', [apiSoporteRecargasController::class, 'actualizarEstado'], 'json'],

    ['GET',  '#^/api/soporte/productos$#',              [apiSoporteProductosController::class, 'listar'], 'json'],
    ['GET',  '#^/api/soporte/productos/(\d+)$#',        [apiSoporteProductosController::class, 'detalle'], 'json'],
    ['POST', '#^/api/soporte/productos/(\d+)/estado$#', [apiSoporteProductosController::class, 'actualizarEstado'], 'json'],
    ['POST', '#^/api/soporte/productos/(\d+)/revisar$#', [apiSoporteProductosController::class, 'revisar'], 'json'],

    ['GET',  '#^/api/soporte-productos/listar$#',       [apiSoporteProductosController::class, 'listar'], 'json'],
    ['GET',  '#^/api/soporte-productos/(\d+)$#',        [apiSoporteProductosController::class, 'detalle'], 'json'],
    ['POST', '#^/api/soporte-productos/(\d+)/estado$#', [apiSoporteProductosController::class, 'actualizarEstado'], 'json'],
    ['POST', '#^/api/soporte-productos/(\d+)/revisar$#', [apiSoporteProductosController::class, 'revisar'], 'json'],

    ['GET',  '#^/api/soporte/usuarios$#',              [apiSoporteUsuariosController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/usuarios/(\d+)/estado$#', [apiSoporteUsuariosController::class, 'actualizarEstado'], 'json'],

    ['POST', '#^/api/cuenta-observada/(\d+)/observar$#', [apiCuentaObservadaController::class, 'observar'], 'json'],

    ['GET',  '#^/api/soporte/residencias$#',              [apiSoporteResidenciasController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/residencias/(\d+)/estado$#', [apiSoporteResidenciasController::class, 'actualizarEstado'], 'json'],

    ['GET',  '#^/api/soporte/dashboard$#', [apiSoporteDashboardController::class, 'resumen'], 'json'],

    ['POST', '#^/api/recargas/registrar$#', [apiRecargaSaldoController::class, 'registrar'], 'json'],
    ['POST', '#^/api/recargas/(\d+)/subsanar$#', [apiRecargaSaldoController::class, 'subsanar'], 'json'],
    ['GET',  '#^/api/recargas/mis$#',       [apiRecargaSaldoController::class, 'mis'], 'json'],

    ['GET', '#^/notificaciones-residencia$#', [notificacionesResidenciaController::class, 'index'], 'html'],

    ['GET',  '#^/api/notificaciones$#',             [apiNotificacionesController::class, 'listar'], 'json'],
    ['GET',  '#^/api/notificaciones/counts$#',      [apiNotificacionesController::class, 'counts'], 'json'],
    ['POST', '#^/api/notificaciones/(\d+)/leida$#', [apiNotificacionesController::class, 'marcarLeida'], 'json'],

    ['POST', '#^/api/notificaciones/residencia/(\d+)/reenviar$#', [apiNotificacionesResidenciaController::class, 'reenviar'], 'json'],

    ['GET',  '#^/api/usuario/disponibilidad-pedidos$#', [apiDisponibilidadPedidosController::class, 'obtenerEstado'], 'json'],
    ['POST', '#^/api/usuario/disponibilidad-pedidos$#', [apiDisponibilidadPedidosController::class, 'actualizarEstado'], 'json'],

    ['GET',  '#^/api/pedidos/mis-comprador$#',          [apiPedidoController::class, 'listarMisPedidosComprador'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/confirmar-entrega$#', [apiPedidoController::class, 'confirmarEntrega'], 'json'],

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

    $isPublic = false;
    foreach ($publicRoutes as $publicPattern) {
        if (preg_match($publicPattern, $uri)) {
            $isPublic = true;
            break;
        }
    }

    if (!$isPublic) {
        $token = $_COOKIE['auth_token'] ?? null;
        $loginUrl = rtrim($baseUrl, '/') . '/login';

        $rTok = SesionJWT::verificarTokenDetallado($token);

        if (!$rTok['ok']) {
            $motivo = ($rTok['error'] ?? '') === 'TOKEN_EXPIRADO' ? 'token_expirado' : 'token_invalido';

            if (esPeticionParcial() || $type === 'json' || str_starts_with($uri, '/api/')) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode([
                    'ok' => false,
                    'error' => 'UNAUTHORIZED',
                    'motivo' => $motivo,
                    'mensaje' => 'Tu sesión expiró o ya no es válida. Vuelve a iniciar sesión.',
                    'redirect' => $loginUrl
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            evRenderSesionFinalizada($loginUrl);
        }

        $GLOBALS['EV_AUTH'] = $rTok['data'] ?? [];

        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);
        $codigoRol     = (int)($auth['codigo_rol'] ?? 0);

        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        $esVecino = ($codigoUsuario > 0 && $codigoRol !== $adminId && $codigoRol !== $soporteId);

        if ($esVecino && $codigoUsuario > 0) {
            if (evUsuarioEstaBloqueado($codigoUsuario)) {
                if (esPeticionParcial() || $type === 'json' || str_starts_with($uri, '/api/')) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(403);
                    echo json_encode([
                        'ok'       => false,
                        'error'    => 'CUENTA_BLOQUEADA',
                        'mensaje'  => 'Tu cuenta fue bloqueada. Por seguridad, debes volver a iniciar sesión.',
                        'redirect' => $loginUrl
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                evRenderCuentaBloqueada($loginUrl);
            }

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
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                header('Location: ' . $redirect, true, 302);
                exit;
            }
        }
    }

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

    [$controllerClass, $action] = $handler;

    if (!class_exists($controllerClass)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'      => false,
            'error'   => 'CONTROLADOR_NO_DISPONIBLE',
            'mensaje' => "No se encontró el controlador {$controllerClass}. Revisa require/autoload."
        ], JSON_UNESCAPED_UNICODE);
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
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');
    echo "<h1 style='font-family:system-ui;padding:24px'>404</h1><p style='font-family:system-ui;padding:0 24px'>Ruta no encontrada.</p>";
}