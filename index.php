<?php
// ============================================================
// index.php — Enrutamiento centralizado (EV)
// Shell único (MenuPrincipal) + parciales para módulos
// + Bloqueo por CUENTA OBSERVADA / CAMBIO DE RESIDENCIA OBSERVADO
// + Expulsión inmediata en siguiente request si SOPORTE bloquea cuenta
// + Endpoint liviano para notificación global de pedidos vendedor
// ============================================================

declare(strict_types=1);

// ------------------------------
// Helpers base
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
    if (!empty($_GET['partial']) && $_GET['partial'] === '1') {
        return true;
    }

    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    if ($xrw && strtolower($xrw) === 'xmlhttprequest') {
        return true;
    }

    $xp = $_SERVER['HTTP_X_PARTIAL'] ?? '';
    if ($xp === '1') {
        return true;
    }

    return false;
}

/**
 * Normaliza BASE_URL para trabajar con rutas locales.
 * Soporta BASE_URL como:
 * - /entrevecinos
 * - /entrevecinos/
 * - http://localhost/entrevecinos
 */
function evBaseUrl(): string
{
    $b = defined('BASE_URL') ? (string)BASE_URL : '/';
    $b = trim($b);

    if ($b === '') {
        return '/';
    }

    if (preg_match('#^https?://#i', $b)) {
        return rtrim($b, '/') . '/';
    }

    if ($b[0] !== '/') {
        $b = '/' . $b;
    }

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

function evJsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ------------------------------
// Render estándar EV para seguridad
// ------------------------------
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
  <link rel="icon" type="image/png" href="<?= rtrim(BASE_URL, '/') ?>/resources/images/logo/logo_ev_transparente_corregido_recortado.png">
        <meta charset="utf-8" />
        <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            :root{
                --ev-verde-oscuro:#0F592F;
                --ev-verde:#16A34A;
                --ev-naranja:#EA7C12;
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

// ------------------------------
// Validaciones contra BD
// ------------------------------
function evObtenerConexionIndex(): ?PDO
{
    try {
        if (!class_exists('Conexion')) {
            return null;
        }

        $cn = new Conexion();

        if (!method_exists($cn, 'getDblink')) {
            return null;
        }

        $db = $cn->getDblink();

        return $db instanceof PDO ? $db : null;
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evObtenerConexionIndex] ' . $e->getMessage());
        return null;
    }
}


/**
 * Apaga la disponibilidad del vendedor de forma centralizada.
 * Se usa en logout, token expirado y estados de cuenta restringidos.
 */
function evApagarDisponibilidadPedidosUsuario(int $codigoUsuario, string $motivo = ''): void
{
    if ($codigoUsuario <= 0) {
        return;
    }

    try {
        $db = evObtenerConexionIndex();

        if (!$db) {
            return;
        }

        $sql = "
            UPDATE usuario
            SET disponibilidad_pedidos = 0
            WHERE codigo_usuario = :id
              AND disponibilidad_pedidos <> 0
            LIMIT 1
        ";

        $st = $db->prepare($sql);
        $st->execute([':id' => $codigoUsuario]);

        if ($st->rowCount() > 0) {
            error_log('[EV][AUTH][DISPONIBILIDAD_OFF] usuario=' . $codigoUsuario . ' motivo=' . $motivo);
        }
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evApagarDisponibilidadPedidosUsuario] ' . $e->getMessage());
    }
}

function evJwtBase64UrlDecode(string $value): string|false
{
    $value = trim($value);
    if ($value === '') {
        return false;
    }

    $value = strtr($value, '-_', '+/');
    $padding = strlen($value) % 4;

    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    return base64_decode($value, true);
}

/**
 * Lee el payload de un JWT expirado sin aceptar tokens falsificados:
 * valida firma HS256 y solo ignora la fecha expirada.
 */
function evDataTokenFirmadoSinValidarExp(?string $token): ?array
{
    $token = trim((string)($token ?? ''));
    if ($token === '') {
        return null;
    }

    $secret = (string)ev_env('JWT_SECRET_KEY', '');
    if ($secret === '') {
        return null;
    }

    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$header64, $payload64, $signature64] = $parts;

    $headerRaw = evJwtBase64UrlDecode($header64);
    $payloadRaw = evJwtBase64UrlDecode($payload64);
    $signatureRaw = evJwtBase64UrlDecode($signature64);

    if ($headerRaw === false || $payloadRaw === false || $signatureRaw === false) {
        return null;
    }

    $header = json_decode($headerRaw, true);
    $payload = json_decode($payloadRaw, true);

    if (!is_array($header) || !is_array($payload)) {
        return null;
    }

    if (strtoupper((string)($header['alg'] ?? '')) !== 'HS256') {
        return null;
    }

    $expected = hash_hmac('sha256', $header64 . '.' . $payload64, $secret, true);

    if (!hash_equals($expected, $signatureRaw)) {
        return null;
    }

    $data = $payload['data'] ?? null;
    return is_array($data) ? $data : null;
}

function evCodigoUsuarioDesdeTokenFirmado(?string $token): int
{
    $data = evDataTokenFirmadoSinValidarExp($token);
    return (int)($data['codigo_usuario'] ?? 0);
}

function evEliminarCookiesAuthIndex(): void
{
    try {
        if (class_exists('SesionJWT') && method_exists('SesionJWT', 'eliminarToken')) {
            SesionJWT::eliminarToken();
        }
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evEliminarCookiesAuthIndex][auth_token] ' . $e->getMessage());
    }

    // Compatibilidad con instalaciones antiguas que aún tenían jwt_token.
    try {
        $path = function_exists('evBaseUrl') ? evBasePathFromBaseUrl(evBaseUrl()) : '/';
        $path = ($path === '/') ? '/' : rtrim($path, '/') . '/';

        if (isset($_COOKIE['jwt_token'])) {
            setcookie('jwt_token', '', [
                'expires'  => time() - 3600,
                'path'     => $path,
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            unset($_COOKIE['jwt_token']);
        }
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evEliminarCookiesAuthIndex][jwt_token] ' . $e->getMessage());
    }
}

function evApagarDisponibilidadPorTokenFirmado(?string $token, string $motivo): void
{
    $codigoUsuario = evCodigoUsuarioDesdeTokenFirmado($token);
    if ($codigoUsuario > 0) {
        evApagarDisponibilidadPedidosUsuario($codigoUsuario, $motivo);
    }

    evEliminarCookiesAuthIndex();
}

function evObtenerEstadoUsuario(int $codigoUsuario): ?int
{
    try {
        $db = evObtenerConexionIndex();

        if (!$db) {
            return null;
        }

        $sql = "
            SELECT estado
            FROM usuario
            WHERE codigo_usuario = :id
            LIMIT 1
        ";

        $st = $db->prepare($sql);
        $st->execute([':id' => $codigoUsuario]);

        $valor = $st->fetchColumn();

        if ($valor === false || $valor === null) {
            return null;
        }

        return (int)$valor;
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evObtenerEstadoUsuario] ' . $e->getMessage());
        return null;
    }
}

function evUsuarioTieneCuentaObservada(int $codigoUsuario): bool
{
    try {
        $db = evObtenerConexionIndex();

        if (!$db) {
            return false;
        }

        $st = $db->prepare("
            SELECT 1
            FROM usuario_revision
            WHERE codigo_usuario = :id
              AND estado_revision = 3
            LIMIT 1
        ");

        $st->execute([':id' => $codigoUsuario]);

        return (bool)$st->fetchColumn();
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evUsuarioTieneCuentaObservada] ' . $e->getMessage());
        return false;
    }
}

function evUsuarioTieneCambioResidenciaObservado(int $codigoUsuario): bool
{
    try {
        $db = evObtenerConexionIndex();

        if (!$db) {
            return false;
        }

        $st = $db->prepare("
            SELECT 1
            FROM usuario_residencia_solicitud
            WHERE codigo_usuario = :id
              AND estado = 'observada'
            ORDER BY codigo_solicitud DESC
            LIMIT 1
        ");

        $st->execute([':id' => $codigoUsuario]);

        return (bool)$st->fetchColumn();
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evUsuarioTieneCambioResidenciaObservado] ' . $e->getMessage());
        return false;
    }
}

/**
 * Observado global EV:
 * - Cuenta observada por validación inicial: usuario_revision.estado_revision = 3
 * - Cambio de residencia observado: usuario_residencia_solicitud.estado = 'observada'
 */
function evUsuarioEstaObservado(int $codigoUsuario): bool
{
    if ($codigoUsuario <= 0) {
        return false;
    }

    return (
        evUsuarioTieneCuentaObservada($codigoUsuario)
        || evUsuarioTieneCambioResidenciaObservado($codigoUsuario)
    );
}

function evUsuarioEstaEnRevisionInicial(int $codigoUsuario): bool
{
    $estado = evObtenerEstadoUsuario($codigoUsuario);
    return ($estado === 1);
}

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
        || $uri === '/aceptacion-legal'
        || str_starts_with($uri, '/api/documentos-legales')
        || $uri === '/logout'
    );
}

/**
 * Rutas permitidas mientras existe una aceptación legal pendiente.
 * Los documentos públicos se resuelven como rutas públicas; se incluyen aquí
 * por claridad y para soportar futuras llamadas autenticadas.
 */
function evRutaPermitidaSinAceptacionLegal(string $uri): bool
{
    return (
        $uri === '/aceptacion-legal'
        || str_starts_with($uri, '/api/documentos-legales')
        || str_starts_with($uri, '/legal/')
        || $uri === '/logout'
    );
}

/**
 * Verifica si el usuario debe aceptar la versión legal vigente.
 * Ante un problema de instalación se registra el error y no se bloquea todo EV;
 * la guía de instalación exige ejecutar primero la migración del punto 12.
 */
function evUsuarioTieneAceptacionLegalPendiente(int $codigoUsuario): bool
{
    if ($codigoUsuario <= 0 || !class_exists('DocumentoLegal')) {
        return false;
    }

    try {
        $model = new DocumentoLegal();
        return $model->tienePendientesUsuario($codigoUsuario);
    } catch (Throwable $e) {
        error_log('[EV][INDEX][evUsuarioTieneAceptacionLegalPendiente] ' . $e->getMessage());
        return false;
    }
}

// ============================================================
// 1) Dependencias
// ============================================================
safeRequire(__DIR__ . '/Config/config.php', true);
safeRequire(__DIR__ . '/models/SesionJWT.php', true);
safeRequire(__DIR__ . '/database/Conexion.php');

if (!defined('EV_ADMIN_ROLE_ID')) {
    define('EV_ADMIN_ROLE_ID', 1);
}

if (!defined('EV_SOPORTE_ROLE_ID')) {
    define('EV_SOPORTE_ROLE_ID', 3);
}

if (!defined('EV_ADMIN_COMUNIDAD_ROLE_ID')) {
    define('EV_ADMIN_COMUNIDAD_ROLE_ID', 4);
}

safeRequire(__DIR__ . '/controllers/authController.php');
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
safeRequire(__DIR__ . '/controllers/notificacionesController.php');
safeRequire(__DIR__ . '/models/DocumentoLegal.php');
safeRequire(__DIR__ . '/controllers/documentosLegalesController.php');
safeRequire(__DIR__ . '/controllers/api/apiDocumentosLegalesController.php');
safeRequire(__DIR__ . '/controllers/comunidadGestionController.php');
safeRequire(__DIR__ . '/controllers/comunidadModeracionController.php');
safeRequire(__DIR__ . '/controllers/comunidadVecinoController.php');
safeRequire(__DIR__ . '/controllers/configuracionPlataformaController.php');

safeRequire(__DIR__ . '/controllers/cuentaObservadaController.php');
safeRequire(__DIR__ . '/controllers/api/apiCuentaObservadaController.php');

safeRequire(__DIR__ . '/controllers/api/usuarioDatosController.php');
safeRequire(__DIR__ . '/controllers/api/apiBilleteraController.php');
safeRequire(__DIR__ . '/models/Dashboard.php');
safeRequire(__DIR__ . '/controllers/api/apiDashboardController.php');
safeRequire(__DIR__ . '/controllers/api/apiPedidoController.php');
safeRequire(__DIR__ . '/controllers/api/apiSolicitudServicioController.php');
safeRequire(__DIR__ . '/controllers/api/apiCalificacionServicioController.php');
safeRequire(__DIR__ . '/models/CalificacionServicio.php');
safeRequire(__DIR__ . '/models/ServicioEjecucion.php');
safeRequire(__DIR__ . '/models/Calificacion.php');
safeRequire(__DIR__ . '/controllers/api/apiCalificacionController.php');
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
safeRequire(__DIR__ . '/controllers/atenderServiciosController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteServiciosController.php');
safeRequire(__DIR__ . '/models/ServicioSoporte.php');

// Libro de Reclamaciones Virtual - gestión interna de soporte
safeRequire(__DIR__ . '/models/LibroReclamacion.php');
safeRequire(__DIR__ . '/controllers/atenderLibroReclamacionesController.php');
safeRequire(__DIR__ . '/controllers/api/apiSoporteLibroReclamacionesController.php');

safeRequire(__DIR__ . '/controllers/api/apiDisponibilidadPedidosController.php');
safeRequire(__DIR__ . '/controllers/api/apiComunidadController.php');
safeRequire(__DIR__ . '/controllers/api/apiComunidadVecinoController.php');
safeRequire(__DIR__ . '/controllers/api/apiConfiguracionPlataformaController.php');

safeRequire(__DIR__ . '/controllers/misPedidosCompradorController.php');
safeRequire(__DIR__ . '/controllers/misPedidosVendedorController.php');
safeRequire(__DIR__ . '/controllers/misSolicitudesServicioVendedorController.php');
safeRequire(__DIR__ . '/controllers/misSolicitudesServicioCompradorController.php');

// ============================================================
// 2) Normalización BASE_URL / basePath
// ============================================================
$baseUrl  = evBaseUrl();
$basePath = evBasePathFromBaseUrl($baseUrl);

// ============================================================
// 3) Parseo URI
// ============================================================
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

// ============================================================
// 4) Rutas públicas
// ============================================================
$publicRoutes = [
    '#^/$#',
    '#^/login$#',
    '#^/usuarios/registrar$#',
    '#^/legal/terminos-y-condiciones$#',
    '#^/legal/politica-de-privacidad$#',

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

// ============================================================
// 5) Rutas del sistema
// ============================================================
$routes = [
    // ---------------------------
    // AUTH / SHELL
    // ---------------------------
    ['GET',  '#^/$#',              [AuthController::class, 'loginForm'], 'html'],
    ['GET',  '#^/login$#',         [AuthController::class, 'loginForm'], 'html'],
    ['POST', '#^/login$#',         [AuthController::class, 'login'],     'json'],
    ['GET',  '#^/MenuPrincipal$#', [MenuPrincipalController::class, 'index'], 'html'],

    ['GET',  '#^/logout$#', [AuthController::class, 'logout'], 'html'],
    ['POST', '#^/logout$#', [AuthController::class, 'logout'], 'json'],

    // ---------------------------
    // PUNTO 12 - DOCUMENTOS LEGALES Y CONSENTIMIENTOS
    // ---------------------------
    ['GET', '#^/legal/terminos-y-condiciones$#', [DocumentosLegalesController::class, 'terminos'], 'html'],
    ['GET', '#^/legal/politica-de-privacidad$#', [DocumentosLegalesController::class, 'privacidad'], 'html'],
    ['GET', '#^/aceptacion-legal$#', [DocumentosLegalesController::class, 'aceptacion'], 'html'],
    ['GET', '#^/api/documentos-legales/pendientes$#', [apiDocumentosLegalesController::class, 'pendientes'], 'json'],
    ['POST', '#^/api/documentos-legales/aceptar-vigentes$#', [apiDocumentosLegalesController::class, 'aceptarVigentes'], 'json'],

    // ---------------------------
    // CUENTA OBSERVADA
    // ---------------------------
    ['GET',  '#^/cuenta-observada$#', [cuentaObservadaController::class, 'index'], 'html'],
    ['POST', '#^/api/cuenta-observada/reenviar$#', [apiCuentaObservadaController::class, 'reenviar'], 'json'],
    ['POST', '#^/api/cuenta-observada/(\d+)/observar$#', [apiCuentaObservadaController::class, 'observar'], 'json'],

    // ---------------------------
    // CATÁLOGOS PÚBLICOS
    // ---------------------------
    ['GET',  '#^/condominios$#',                [CondominioController::class, 'listar'],              'json'],
    ['GET',  '#^/condominios/(\d+)/torres$#',   [CondominioController::class, 'listarTorres'],        'json'],
    ['GET',  '#^/torres/(\d+)/departamentos$#', [CondominioController::class, 'listarDepartamentos'], 'json'],

    ['GET',  '#^/urbanizaciones$#', [UrbanizacionController::class, 'listar'], 'json'],

    ['GET',  '#^/ubigeo/departamentos$#',                  [UbigeoController::class, 'departamentos'], 'json'],
    ['GET',  '#^/ubigeo/departamentos/(\d+)/provincias$#', [UbigeoController::class, 'provincias'],    'json'],
    ['GET',  '#^/ubigeo/provincias/(\d+)/distritos$#',     [UbigeoController::class, 'distritos'],     'json'],

    ['GET',  '#^/tipos$#',                       [tipoController::class, 'listar'],                'json'],
    ['GET',  '#^/tipos/(\d+)/categoria_grupo$#', [tipoController::class, 'listarCategoria_grupo'], 'json'],

    ['POST', '#^/usuarios/registrar$#', [UserController::class, 'registrar'], 'json'],

    // ---------------------------
    // VISTAS VECINO
    // ---------------------------
    ['GET', '#^/mi-perfil$#',   [miPerfilController::class, 'index'],    'html'],
    ['GET', '#^/publicacion$#', [productoController::class, 'index'],    'html'],
    ['GET', '#^/producto$#',    [productoController::class, 'index'],    'html'],
    ['GET', '#^/marketplace$#', [marketplaceController::class, 'index'], 'html'],
    ['GET', '#^/billetera$#',   [billeteraController::class, 'index'],   'html'],
    ['GET', '#^/credencial$#',  [credencialController::class, 'index'],  'html'],

    ['GET', '#^/recibir$#', [recibirPedidosController::class, 'index'], 'html'],

    ['GET', '#^/mis-pedidos-comprador$#', [misPedidosCompradorController::class, 'index'], 'html'],
    ['GET', '#^/mis-pedidos-vendedor$#',  [misPedidosVendedorController::class, 'index'],  'html'],
    ['GET', '#^/mis-solicitudes-servicio-comprador$#', [misSolicitudesServicioCompradorController::class, 'index'], 'html'],
    ['GET', '#^/mis-solicitudes-servicio-vendedor$#', [misSolicitudesServicioVendedorController::class, 'index'], 'html'],

    // ---------------------------
    // COMUNIDAD - VISTA PARA VECINOS
    // ---------------------------
    ['GET', '#^/comunidad$#', [comunidadVecinoController::class, 'index'], 'html'],

    // ---------------------------
    // VISTAS SOPORTE
    // ---------------------------
    ['GET', '#^/atender-recargas$#', [atenderRecargasController::class, 'index'], 'html'],
    ['GET', '#^/atender-publicacion$#', [atenderPublicacionController::class, 'index'], 'html'],
    ['GET', '#^/atender-cuentas$#', [atenderCuentasUsuarioController::class, 'index'], 'html'],
    ['GET', '#^/atender-cuentas-usuario$#', [atenderCuentasUsuarioController::class, 'index'], 'html'],
    ['GET', '#^/atender-servicios$#', [atenderServiciosController::class, 'index'], 'html'],
    ['GET', '#^/atender-libro-reclamaciones$#', [atenderLibroReclamacionesController::class, 'index'], 'html'],

    // ---------------------------
    // COMUNIDAD - GESTIÓN Y MODERACIÓN
    // ---------------------------
    ['GET', '#^/comunidad/gestionar$#', [comunidadGestionController::class, 'index'], 'html'],
    ['GET', '#^/comunidad/moderacion$#', [comunidadModeracionController::class, 'index'], 'html'],

    // ---------------------------
    // ADMINISTRACIÓN - CONFIGURACIÓN DE PLATAFORMA
    // ---------------------------
    ['GET', '#^/configuracion-plataforma$#', [configuracionPlataformaController::class, 'index'], 'html'],

    // ---------------------------
    // COMUNIDAD - API GESTIÓN INSTITUCIONAL
    // ---------------------------
    ['GET',  '#^/api/comunidad/destinos$#', [apiComunidadController::class, 'destinos'], 'json'],
    ['GET',  '#^/api/comunidad/publicaciones$#', [apiComunidadController::class, 'listar'], 'json'],
    ['POST', '#^/api/comunidad/publicaciones$#', [apiComunidadController::class, 'crear'], 'json'],
    ['GET',  '#^/api/comunidad/publicaciones/(\d+)$#', [apiComunidadController::class, 'detalle'], 'json'],
    ['GET',  '#^/api/comunidad/publicaciones/(\d+)/historial$#', [apiComunidadController::class, 'historial'], 'json'],
    ['POST', '#^/api/comunidad/publicaciones/(\d+)/actualizar$#', [apiComunidadController::class, 'actualizar'], 'json'],
    ['POST', '#^/api/comunidad/publicaciones/(\d+)/publicar$#', [apiComunidadController::class, 'publicar'], 'json'],
    ['POST', '#^/api/comunidad/publicaciones/(\d+)/desactivar$#', [apiComunidadController::class, 'desactivar'], 'json'],
    ['POST', '#^/api/comunidad/publicaciones/(\d+)/reactivar$#', [apiComunidadController::class, 'reactivar'], 'json'],

    // ---------------------------
    // COMUNIDAD - API DE SOLO LECTURA PARA VECINOS
    // ---------------------------
    ['GET', '#^/api/comunidad/vecino/publicaciones$#', [apiComunidadVecinoController::class, 'listar'], 'json'],
    ['GET', '#^/api/comunidad/vecino/publicaciones/(\d+)$#', [apiComunidadVecinoController::class, 'detalle'], 'json'],

    // ---------------------------
    // ADMINISTRACIÓN - CONTROL DE FUNCIONALIDADES Y MONETIZACIÓN
    // ---------------------------
    ['GET',  '#^/api/admin/configuracion-plataforma$#', [apiConfiguracionPlataformaController::class, 'obtener'], 'json'],
    ['GET',  '#^/api/admin/configuracion-plataforma/alcances$#', [apiConfiguracionPlataformaController::class, 'buscarAlcances'], 'json'],
    ['POST', '#^/api/admin/configuracion-plataforma/funcionalidad$#', [apiConfiguracionPlataformaController::class, 'guardarFuncionalidad'], 'json'],
    ['POST', '#^/api/admin/configuracion-plataforma/monetizacion$#', [apiConfiguracionPlataformaController::class, 'guardarMonetizacion'], 'json'],
    ['POST', '#^/api/admin/configuracion-plataforma/aplicar-piloto$#', [apiConfiguracionPlataformaController::class, 'aplicarPiloto'], 'json'],

    // ---------------------------
    // USUARIO
    // ---------------------------
    ['GET',  '#^/api/usuario/datos$#', [usuarioDatosController::class, 'obtenerDatos'], 'json'],
    ['POST', '#^/api/usuario/actualizar$#', [usuarioDatosController::class, 'actualizarDatos'], 'json'],
    ['POST', '#^/api/usuario/foto-perfil$#', [usuarioDatosController::class, 'actualizarFotoPerfil'], 'json'],
    ['POST', '#^/api/usuario/cambiar-clave$#', [usuarioDatosController::class, 'cambiarClave'], 'json'],
    ['POST', '#^/api/usuario/solicitar-cambio-residencia$#', [usuarioDatosController::class, 'solicitarCambioResidencia'], 'json'],

    ['POST', '#^/api/usuario/actualizar-telefono$#', [usuarioDatosController::class, 'actualizarTelefono'], 'json'],
    ['POST', '#^/api/usuario/actualizar-residencia$#', [usuarioDatosController::class, 'actualizarResidencia'], 'json'],
    ['POST', '#^/api/usuario/actualizar-cuenta$#', [usuarioDatosController::class, 'cambiarClave'], 'json'],

    ['GET',  '#^/api/usuario/disponibilidad-pedidos$#', [apiDisponibilidadPedidosController::class, 'obtenerEstado'], 'json'],
    ['POST', '#^/api/usuario/disponibilidad-pedidos$#', [apiDisponibilidadPedidosController::class, 'actualizarEstado'], 'json'],

    // ---------------------------
    // DASHBOARD VECINO
    // ---------------------------
    ['GET',  '#^/api/dashboard/vecino$#', [apiDashboardController::class, 'vecino'], 'json'],
    

    // ---------------------------
    // PRODUCTOS / MARKETPLACE
    // ---------------------------
    ['POST', '#^/api/producto/registrar$#', [apiProductoController::class, 'registrarProducto'], 'json'],
    ['GET',  '#^/api/producto/listar$#', [apiProductoController::class, 'listarProductos'], 'json'],
    ['GET',  '#^/api/producto/(\d+)$#', [apiProductoController::class, 'obtenerProducto'], 'json'],
    ['GET',  '#^/api/marketplace/producto/(\d+)$#', [apiProductoController::class, 'obtenerDetalleMarketplace'], 'json'],
    ['POST', '#^/api/producto/(\d+)/actualizar$#', [apiProductoController::class, 'actualizarProducto'], 'json'],
    ['POST', '#^/api/producto/(\d+)/anular$#', [apiProductoController::class, 'anularProducto'], 'json'],
    ['POST', '#^/api/producto/(\d+)/publicar$#', [apiProductoController::class, 'publicarProducto'], 'json'],
    ['GET',  '#^/api/producto/marketplace$#', [apiProductoController::class, 'listarMarketplace'], 'json'],

    // ---------------------------
    // SOLICITUDES DE SERVICIO
    // Flujo propio: no usa pedido, cola, stock ni billetera.
    // ---------------------------
    ['POST', '#^/api/servicios/solicitudes$#', [apiSolicitudServicioController::class, 'registrar'], 'json'],
    ['GET',  '#^/api/servicios/solicitudes/proveedor$#', [apiSolicitudServicioController::class, 'listarProveedor'], 'json'],
    ['GET',  '#^/api/servicios/solicitudes/solicitante$#', [apiSolicitudServicioController::class, 'listarSolicitante'], 'json'],

    // Punto 10: conversación privada, imágenes y punto exacto tras aceptación.
    ['GET',  '#^/api/servicios/solicitudes/(\d+)/conversacion$#', [apiSolicitudServicioController::class, 'obtenerConversacion'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/mensajes$#', [apiSolicitudServicioController::class, 'enviarMensaje'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/compartir-ubicacion$#', [apiSolicitudServicioController::class, 'compartirUbicacion'], 'json'],

    ['POST', '#^/api/servicios/solicitudes/(\d+)/solicitar-informacion$#', [apiSolicitudServicioController::class, 'solicitarInformacion'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/propuesta$#', [apiSolicitudServicioController::class, 'enviarPropuesta'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/rechazar$#', [apiSolicitudServicioController::class, 'rechazar'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/responder-informacion$#', [apiSolicitudServicioController::class, 'responderInformacion'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/aceptar-propuesta$#', [apiSolicitudServicioController::class, 'aceptarPropuesta'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/solicitar-ajuste$#', [apiSolicitudServicioController::class, 'solicitarAjuste'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/cancelar$#', [apiSolicitudServicioController::class, 'cancelar'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/cotizacion-final$#', [apiSolicitudServicioController::class, 'enviarCotizacionFinal'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/aceptar-cotizacion-final$#', [apiSolicitudServicioController::class, 'aceptarCotizacionFinal'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/solicitar-ajuste-cotizacion$#', [apiSolicitudServicioController::class, 'solicitarAjusteCotizacionFinal'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/rechazar-cotizacion-final$#', [apiSolicitudServicioController::class, 'rechazarCotizacionFinal'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/cancelar-proveedor$#', [apiSolicitudServicioController::class, 'cancelarProveedor'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/marcar-realizado$#', [apiSolicitudServicioController::class, 'marcarRealizado'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/confirmar-realizado$#', [apiSolicitudServicioController::class, 'confirmarRealizado'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/reportar-observacion$#', [apiSolicitudServicioController::class, 'reportarObservacion'], 'json'],

    // Punto 11: ejecución, reprogramación, incidencias y calificación.
    ['GET',  '#^/api/servicios/solicitudes/(\d+)/operacion$#', [apiSolicitudServicioController::class, 'obtenerOperacion'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/iniciar$#', [apiSolicitudServicioController::class, 'iniciarServicio'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/reprogramaciones$#', [apiSolicitudServicioController::class, 'proponerReprogramacion'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/reprogramaciones/(\d+)/responder$#', [apiSolicitudServicioController::class, 'responderReprogramacion'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/reprogramaciones/(\d+)/cancelar$#', [apiSolicitudServicioController::class, 'cancelarReprogramacion'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/incidencias$#', [apiSolicitudServicioController::class, 'reportarProblema'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/incidencias/responder$#', [apiSolicitudServicioController::class, 'responderIncidencia'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/incidencias/solucion$#', [apiSolicitudServicioController::class, 'registrarSolucion'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/incidencias/confirmar-solucion$#', [apiSolicitudServicioController::class, 'confirmarSolucion'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/incidencias/persiste$#', [apiSolicitudServicioController::class, 'problemaPersiste'], 'json'],
    ['POST', '#^/api/servicios/solicitudes/(\d+)/incidencias/solicitar-soporte$#', [apiSolicitudServicioController::class, 'solicitarSoporte'], 'json'],

    ['GET',  '#^/api/calificaciones-servicio/pendientes$#', [apiCalificacionServicioController::class, 'listarPendientes'], 'json'],
    ['GET',  '#^/api/calificaciones-servicio/solicitud/(\d+)$#', [apiCalificacionServicioController::class, 'obtenerPorSolicitud'], 'json'],
    ['POST', '#^/api/calificaciones-servicio/(\d+)/enviar$#', [apiCalificacionServicioController::class, 'enviar'], 'json'],


    // ---------------------------
    // BILLETERA
    // ---------------------------
    ['GET',  '#^/api/billetera/saldo$#', [apiBilleteraController::class, 'obtenerSaldo'], 'json'],
    ['GET',  '#^/api/billetera/movimientos$#', [apiBilleteraController::class, 'obtenerMovimientos'], 'json'],
    ['POST', '#^/api/billetera/debitar-publicacion$#', [apiBilleteraController::class, 'debitarPublicacion'], 'json'],
    ['POST', '#^/api/billetera/debitar-producto-destacado$#', [apiBilleteraController::class, 'debitarProductoDestacado'], 'json'],

    // ---------------------------
    // PEDIDOS - COMPRADOR
    // ---------------------------
    ['GET',  '#^/api/pedidos/recibir$#', [apiPedidoController::class, 'listarPedidos'], 'json'],
    ['POST', '#^/api/pedidos/registrar$#', [apiPedidoController::class, 'registrarPedido'], 'json'],
    ['GET',  '#^/api/pedidos/solicitud-activa$#', [apiPedidoController::class, 'obtenerSolicitudActiva'], 'json'],
    ['GET',  '#^/api/pedidos/(\d+)/estado$#', [apiPedidoController::class, 'obtenerEstadoSolicitud'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/cancelar$#', [apiPedidoController::class, 'cancelarSolicitud'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/confirmar-cola$#', [apiPedidoController::class, 'confirmarCola'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/confirmar-entrega$#', [apiPedidoController::class, 'confirmarEntrega'], 'json'],
    ['GET',  '#^/api/pedidos/mis-comprador$#', [apiPedidoController::class, 'listarMisPedidosComprador'], 'json'],

    ['GET',  '#^/api/pedidos/alertas$#', [apiPedidoController::class, 'listarAlertasPedido'], 'json'],
    ['POST', '#^/api/pedidos/alertas/(\d+)/leer$#', [apiPedidoController::class, 'marcarAlertaPedidoLeida'], 'json'],

    ['GET',  '#^/api/pedidos/registrar/?$#', [apiPedidoController::class, 'registrarPedido'], 'json'],
    ['POST', '#^/api/pedidos/registrar/?$#', [apiPedidoController::class, 'registrarPedido'], 'json'],


    // ---------------------------
    // PEDIDOS - VENDEDOR
    // ---------------------------
    ['GET', '#^/api/pedidos/vendedor/nuevas-solicitudes$#', [apiPedidoController::class, 'listarNuevasSolicitudesVendedor'], 'json'],

    ['GET',  '#^/api/pedidos/mis$#', [apiPedidoController::class, 'listarMisPedidos'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/aceptar$#', [apiPedidoController::class, 'aceptarSolicitud'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/rechazar$#', [apiPedidoController::class, 'rechazarSolicitud'], 'json'],
    ['POST', '#^/api/pedidos/(\d+)/estado$#', [apiPedidoController::class, 'actualizarEstadoPedido'], 'json'],



    // ---------------------------
    // CALIFICACIONES - MVP EV
    // ---------------------------
    ['GET',  '#^/api/calificaciones/pendientes$#', [apiCalificacionController::class, 'listarPendientes'], 'json'],
    ['GET',  '#^/api/calificaciones/pedido/(\d+)$#', [apiCalificacionController::class, 'obtenerPendientePedido'], 'json'],
    ['GET',  '#^/api/calificaciones/reputacion-vendedores$#', [apiCalificacionController::class, 'reputacionVendedores'], 'json'],
    ['POST', '#^/api/calificaciones/(\d+)/enviar$#', [apiCalificacionController::class, 'enviar'], 'json'],
    ['POST', '#^/api/calificaciones/(\d+)/reportar$#', [apiCalificacionController::class, 'reportar'], 'json'],

    // ---------------------------
    // SOPORTE - RECARGAS
    // ---------------------------
    ['GET',  '#^/api/soporte/recargas$#', [apiSoporteRecargasController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/recargas/(\d+)/estado$#', [apiSoporteRecargasController::class, 'actualizarEstado'], 'json'],

    // ---------------------------
    // SOPORTE - PRODUCTOS
    // ---------------------------
    ['GET',  '#^/api/soporte/productos$#', [apiSoporteProductosController::class, 'listar'], 'json'],
    ['GET',  '#^/api/soporte/productos/(\d+)$#', [apiSoporteProductosController::class, 'detalle'], 'json'],
    ['POST', '#^/api/soporte/productos/(\d+)/estado$#', [apiSoporteProductosController::class, 'actualizarEstado'], 'json'],
    ['POST', '#^/api/soporte/productos/(\d+)/revisar$#', [apiSoporteProductosController::class, 'revisar'], 'json'],

    ['GET',  '#^/api/soporte-productos/listar$#', [apiSoporteProductosController::class, 'listar'], 'json'],
    ['GET',  '#^/api/soporte-productos/(\d+)$#', [apiSoporteProductosController::class, 'detalle'], 'json'],
    ['POST', '#^/api/soporte-productos/(\d+)/estado$#', [apiSoporteProductosController::class, 'actualizarEstado'], 'json'],
    ['POST', '#^/api/soporte-productos/(\d+)/revisar$#', [apiSoporteProductosController::class, 'revisar'], 'json'],

    // ---------------------------
    // SOPORTE - USUARIOS / RESIDENCIAS / DASHBOARD
    // ---------------------------
    ['GET',  '#^/api/soporte/usuarios$#', [apiSoporteUsuariosController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/usuarios/(\d+)/estado$#', [apiSoporteUsuariosController::class, 'actualizarEstado'], 'json'],

    ['GET',  '#^/api/soporte/residencias$#', [apiSoporteResidenciasController::class, 'listar'], 'json'],
    ['POST', '#^/api/soporte/residencias/(\d+)/estado$#', [apiSoporteResidenciasController::class, 'actualizarEstado'], 'json'],

    ['GET', '#^/api/soporte/dashboard$#', [apiSoporteDashboardController::class, 'resumen'], 'json'],
    ['GET', '#^/api/soporte/servicios$#', [apiSoporteServiciosController::class, 'listar'], 'json'],
    ['GET', '#^/api/soporte/servicios/resumen$#', [apiSoporteServiciosController::class, 'resumen'], 'json'],
    ['GET', '#^/api/soporte/servicios/(\d+)$#', [apiSoporteServiciosController::class, 'detalle'], 'json'],
    ['POST', '#^/api/soporte/servicios/(\d+)/resolver$#', [apiSoporteServiciosController::class, 'resolver'], 'json'],

    // ---------------------------
    // SOPORTE - LIBRO DE RECLAMACIONES
    // ---------------------------
    ['GET',  '#^/api/soporte/libro-reclamaciones$#', [apiSoporteLibroReclamacionesController::class, 'listar'], 'json'],
    ['GET',  '#^/api/soporte/libro-reclamaciones/resumen$#', [apiSoporteLibroReclamacionesController::class, 'resumen'], 'json'],
    ['GET',  '#^/api/soporte/libro-reclamaciones/(\d+)$#', [apiSoporteLibroReclamacionesController::class, 'detalle'], 'json'],
    ['POST', '#^/api/soporte/libro-reclamaciones/(\d+)/atender$#', [apiSoporteLibroReclamacionesController::class, 'atender'], 'json'],

    // ---------------------------
    // RECARGAS
    // ---------------------------
    ['POST', '#^/api/recargas/registrar$#', [apiRecargaSaldoController::class, 'registrar'], 'json'],
    ['POST', '#^/api/recargas/(\d+)/subsanar$#', [apiRecargaSaldoController::class, 'subsanar'], 'json'],
    ['GET',  '#^/api/recargas/mis$#', [apiRecargaSaldoController::class, 'mis'], 'json'],

    // ---------------------------
    // NOTIFICACIONES
    // ---------------------------
    ['GET', '#^/notificaciones-residencia$#', [notificacionesResidenciaController::class, 'index'], 'html'],
    ['GET', '#^/notificaciones$#', [notificacionesController::class, 'index'], 'html'],

    ['GET',  '#^/api/notificaciones$#', [apiNotificacionesController::class, 'listar'], 'json'],
    ['GET',  '#^/api/notificaciones/counts$#', [apiNotificacionesController::class, 'counts'], 'json'],
    ['GET',  '#^/api/notificaciones/resumen$#', [apiNotificacionesController::class, 'resumen'], 'json'],
    ['POST', '#^/api/notificaciones/leer-todas$#', [apiNotificacionesController::class, 'marcarTodasLeidas'], 'json'],
    ['POST', '#^/api/notificaciones/(\d+)/leida$#', [apiNotificacionesController::class, 'marcarLeida'], 'json'],

    ['POST', '#^/api/notificaciones/residencia/(\d+)/reenviar$#', [apiNotificacionesResidenciaController::class, 'reenviar'], 'json'],
];

// ============================================================
// 6) Resolver rutas
// ============================================================
$matched = false;

foreach ($routes as $r) {
    [$httpMethod, $pattern, $handler, $type] = $r;

    if ($method !== $httpMethod) {
        continue;
    }

    if (!preg_match($pattern, $uri, $matches)) {
        continue;
    }

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
            $motivo = ($rTok['error'] ?? '') === 'TOKEN_EXPIRADO'
                ? 'token_expirado'
                : 'token_invalido';

            if ($motivo === 'token_expirado') {
                evApagarDisponibilidadPorTokenFirmado($token, 'token_expirado');
            } else {
                evEliminarCookiesAuthIndex();
            }

            if (esPeticionParcial() || $type === 'json' || str_starts_with($uri, '/api/')) {
                evJsonResponse(401, [
                    'ok'       => false,
                    'error'    => 'UNAUTHORIZED',
                    'motivo'   => $motivo,
                    'mensaje'  => 'Tu sesión expiró o ya no es válida. Vuelve a iniciar sesión.',
                    'redirect' => $loginUrl
                ]);
            }

            evRenderSesionFinalizada($loginUrl);
        }

        $GLOBALS['EV_AUTH'] = $rTok['data'] ?? [];

        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);
        $codigoRol     = (int)($auth['codigo_rol'] ?? 0);

        $adminId          = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId        = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;
        $adminComunidadId = defined('EV_ADMIN_COMUNIDAD_ROLE_ID') ? (int)EV_ADMIN_COMUNIDAD_ROLE_ID : 4;

        $esAdministradorComunidad = (
            $codigoUsuario > 0
            && $codigoRol === $adminComunidadId
        );

        $esVecino = (
            $codigoUsuario > 0
            && $codigoRol !== $adminId
            && $codigoRol !== $soporteId
            && $codigoRol !== $adminComunidadId
        );

        /*
         * Cuenta bloqueada:
         * - Se conserva el control actual para vecinos.
         * - Se incorpora al administrador_comunidad para impedir que una cuenta
         *   institucional bloqueada siga publicando contenido mediante un JWT vigente.
         */
        if (($esVecino || $esAdministradorComunidad) && $codigoUsuario > 0) {
            if ($uri === '/logout' && $esVecino) {
                evApagarDisponibilidadPedidosUsuario($codigoUsuario, 'logout');
            }

            if (evUsuarioEstaBloqueado($codigoUsuario)) {
                if ($esVecino) {
                    evApagarDisponibilidadPedidosUsuario($codigoUsuario, 'cuenta_bloqueada');
                }

                evEliminarCookiesAuthIndex();

                if (esPeticionParcial() || $type === 'json' || str_starts_with($uri, '/api/')) {
                    evJsonResponse(403, [
                        'ok'       => false,
                        'error'    => 'CUENTA_BLOQUEADA',
                        'mensaje'  => 'Tu cuenta fue bloqueada. Por seguridad, debes volver a iniciar sesión.',
                        'redirect' => $loginUrl
                    ]);
                }

                evRenderCuentaBloqueada($loginUrl);
            }
        }

        /*
         * Punto 12 - aceptación legal obligatoria:
         * aplica a todo usuario autenticado (vecino, soporte y administradores).
         * Los usuarios nuevos aceptan durante el registro. Los usuarios ya
         * existentes deben aceptar una sola vez al iniciar sesión después de
         * instalar el módulo, y nuevamente cuando cambie la versión/hash vigente.
         */
        if (
            $codigoUsuario > 0
            && !evRutaPermitidaSinAceptacionLegal($uri)
            && evUsuarioTieneAceptacionLegalPendiente($codigoUsuario)
        ) {
            $redirectLegal = rtrim($baseUrl, '/') . '/aceptacion-legal';

            if (esPeticionParcial() || $type === 'json' || str_starts_with($uri, '/api/')) {
                evJsonResponse(428, [
                    'ok'       => false,
                    'error'    => 'ACEPTACION_LEGAL_REQUERIDA',
                    'mensaje'  => 'Debes revisar y aceptar los documentos legales vigentes para continuar.',
                    'redirect' => $redirectLegal
                ]);
            }

            header('Location: ' . $redirectLegal, true, 302);
            exit;
        }

        /*
         * Observaciones de residencia y disponibilidad de pedidos:
         * aplican únicamente al rol vecino. La administración de comunidad
         * se autoriza mediante administrador_comunidad, no por residencia.
         */
        if ($esVecino && $codigoUsuario > 0) {
            $observado = evUsuarioEstaObservado($codigoUsuario);
            $enRevisionInicial = evUsuarioEstaEnRevisionInicial($codigoUsuario);

            if ($observado || $enRevisionInicial) {
                evApagarDisponibilidadPedidosUsuario(
                    $codigoUsuario,
                    $observado ? 'cuenta_observada' : 'cuenta_en_revision'
                );
            }

            if (($observado || $enRevisionInicial) && !evRutaPermitidaEnObservacion($uri)) {
                $redirect = rtrim($baseUrl, '/') . '/cuenta-observada';

                if (esPeticionParcial() || $type === 'json' || str_starts_with($uri, '/api/')) {
                    evJsonResponse(409, [
                        'ok'       => false,
                        'error'    => 'CUENTA_OBSERVADA',
                        'mensaje'  => $observado
                            ? 'Tienes una observación pendiente. Debes revisar y reenviar tu comprobante.'
                            : 'Tu cuenta está en revisión.',
                        'redirect' => $redirect
                    ]);
                }

                header('Location: ' . $redirect, true, 302);
                exit;
            }
        }
    }

    /*
      Shell único:
      Si se entra directo a una vista HTML protegida,
      redirige a MenuPrincipal con ev_goto.
    */
    if (
        !$isPublic
        && $type === 'html'
        && !esPeticionParcial()
        && $uri !== '/MenuPrincipal'
        && $uri !== '/logout'
        && $uri !== '/login'
        && $uri !== '/'
        && $uri !== '/cuenta-observada'
        && $uri !== '/aceptacion-legal'
    ) {
        $target = rtrim($baseUrl, '/') . '/MenuPrincipal?ev_goto=' . urlencode($uri);
        header('Location: ' . $target, true, 302);
        exit;
    }


    [$controllerClass, $action] = $handler;

    if (!class_exists($controllerClass)) {
        evJsonResponse(500, [
            'ok'      => false,
            'error'   => 'CONTROLADOR_NO_DISPONIBLE',
            'mensaje' => "No se encontró el controlador {$controllerClass}. Revisa require/autoload."
        ]);
    }

    $controller = new $controllerClass();

    if (!method_exists($controller, $action)) {
        evJsonResponse(500, [
            'ok'      => false,
            'error'   => 'ACCION_NO_DISPONIBLE',
            'mensaje' => "No se encontró la acción {$action} en {$controllerClass}."
        ]);
    }

    if (!headers_sent()) {
        if ($type === 'json') {
            header('Content-Type: application/json; charset=utf-8');
        } else {
            header('Content-Type: text/html; charset=utf-8');
        }
    }

    array_shift($matches);

    /*
     * preg_match entrega las capturas de rutas como string. Con declare(strict_types=1),
     * los métodos tipados como int (por ejemplo, solicitudes de servicio) no aceptan
     * directamente esos valores. Convertimos únicamente capturas compuestas solo por dígitos.
     */
    $routeParams = array_map(
        static function ($value) {
            return is_string($value) && ctype_digit($value) ? (int)$value : $value;
        },
        $matches
    );

    call_user_func_array([$controller, $action], $routeParams);
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
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    header('Content-Type: text/html; charset=utf-8');

    echo "<h1 style='font-family:system-ui;padding:24px'>404</h1>";
    echo "<p style='font-family:system-ui;padding:0 24px'>Ruta no encontrada.</p>";
    exit;
}
