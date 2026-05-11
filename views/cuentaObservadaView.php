<?php
// views/cuentaObservadaView.php
// Página COMPLETA (sin menú, sin shell)
// Solo vista HTML/PHP. No CSS interno. No JS inline.

if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

$baseUrl = isset($baseUrl)
    ? rtrim((string)$baseUrl, '/')
    : rtrim((string)BASE_URL, '/');

$modoVista          = $modoVista ?? 'revision_inicial';
$mensajeObservacion = trim((string)($mensajeObservacion ?? ''));
$fechaObservacion   = $fechaObservacion ?? null;
$nombreComunidad    = trim((string)($nombreComunidad ?? ''));

$textoComunidad = $nombreComunidad !== ''
    ? 'la comunidad ' . $nombreComunidad
    : 'tu comunidad';

$fechaObservacionTexto = '';
if (!empty($fechaObservacion)) {
    $ts = strtotime((string)$fechaObservacion);
    if ($ts) {
        $fechaObservacionTexto = date('d/m/Y H:i', $ts);
    }
}

$esObservado = ($modoVista === 'observado');

/**
 * Logo EV:
 * Guarda tu logo en: resources/images/logo/logo_solitario.jpg
 */
$logoFsPath = __DIR__ . '/../resources/images/logo/logo_solitario.jpg';
$logoUrl    = $baseUrl . '/resources/images/logo/logo_solitario.jpg';
$tieneLogo  = file_exists($logoFsPath);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado de tu cuenta | Entre Vecinos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Estilos -->
    <?php
    if (defined('VIEW_STYLE_PATH')) {
        $styleFile = rtrim(VIEW_STYLE_PATH, '/') . '/cuentaObservadaEstilo.php';
        if (file_exists($styleFile)) {
            require_once $styleFile;
        }
    }
    ?>
</head>

<body
    class="ev-co-page <?= $esObservado ? 'is-observed' : 'is-review' ?>"
    data-base-url="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>"
    data-modo-vista="<?= htmlspecialchars((string)$modoVista, ENT_QUOTES, 'UTF-8') ?>"
>

<main class="ev-co-shell">
    <div class="ev-co-wrap">

        <div class="ev-brand-pill">
            <span class="ev-brand-icon">
                <?php if ($tieneLogo): ?>
                    <img
                        src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Entre Vecinos"
                        class="ev-brand-logo"
                    >
                <?php else: ?>
                    <i class="bi bi-house-heart"></i>
                <?php endif; ?>
            </span>
            <span>Entre Vecinos</span>
        </div>

        <section class="ev-co-card">
            <div class="ev-co-card-inner">

                <?php if ($esObservado): ?>

                    <div class="ev-co-hero">
                        <div class="ev-hero-visual">
                            <div class="ev-hero-orb is-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="ev-hero-check is-warning">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                        </div>

                        <div>
                            <div class="ev-eyebrow is-warning">ACCIÓN REQUERIDA</div>
                            <h1 class="ev-title">
                                Necesitamos corregir tu información
                            </h1>
                            <p class="ev-subtitle">
                                Nuestro equipo revisó tu registro y encontró un detalle pendiente.
                                Sube el comprobante corregido para continuar con la activación de tu cuenta
                                en <?= htmlspecialchars($textoComunidad, ENT_QUOTES, 'UTF-8') ?>.
                            </p>
                        </div>
                    </div>

                    <div class="ev-observation-card" id="evObservacionBox">
                        <div class="ev-observation-head">
                            <span>
                                <i class="bi bi-chat-left-text"></i>
                            </span>

                            <div>
                                <h2>Observación del equipo de soporte</h2>
                                <p>
                                    <?php if ($fechaObservacionTexto !== ''): ?>
                                        Revisado el <?= htmlspecialchars($fechaObservacionTexto, ENT_QUOTES, 'UTF-8') ?>
                                    <?php else: ?>
                                        Revisa el detalle antes de volver a enviar tu comprobante.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="ev-observation-message">
                            <?= $mensajeObservacion !== ''
                                ? htmlspecialchars($mensajeObservacion, ENT_QUOTES, 'UTF-8')
                                : 'Se encontró una observación en tu registro. Por favor, vuelve a cargar el comprobante corregido para continuar con la validación.' ?>
                        </div>
                    </div>

                    <div class="ev-correction-layout">
                        <div class="ev-correction-info">
                            <div class="ev-mini-step is-done">
                                <span>
                                    <i class="bi bi-check2"></i>
                                </span>
                                <div>
                                    <h3>Registro revisado</h3>
                                    <p>Tu solicitud fue evaluada por el equipo de soporte.</p>
                                </div>
                            </div>

                            <div class="ev-mini-step is-active">
                                <span>
                                    <i class="bi bi-exclamation-circle"></i>
                                </span>
                                <div>
                                    <h3>Corrección pendiente</h3>
                                    <p>Adjunta el comprobante corregido para continuar.</p>
                                </div>
                            </div>

                            <div class="ev-mini-step">
                                <span>
                                    <i class="bi bi-shield-lock"></i>
                                </span>
                                <div>
                                    <h3>Nueva revisión</h3>
                                    <p>Validaremos nuevamente tu información enviada.</p>
                                </div>
                            </div>

                            <div class="ev-support-mini">
                                <i class="bi bi-headset"></i>
                                <div>
                                    <strong>¿Necesitas ayuda?</strong>
                                    <div>Si tienes dudas, revisa la observación indicada por soporte.</div>
                                </div>
                            </div>
                        </div>

                        <div class="ev-form-card">
                            <h2>Sube tu comprobante corregido</h2>
                            <p>
                                Aceptamos archivos PDF, JPG, JPEG y PNG. El tamaño máximo permitido es de 5 MB.
                            </p>

                            <form id="evFormReenviar" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label ev-label" for="evComprobante">
                                        Archivo
                                    </label>

                                    <input
                                        type="file"
                                        class="form-control ev-file"
                                        name="comprobante"
                                        id="evComprobante"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        required
                                    >

                                    <div class="ev-help mt-2">
                                        Formatos permitidos: PDF, JPG, JPEG y PNG. Tamaño máximo: 5 MB.
                                    </div>
                                </div>

                                <div class="ev-actions">
                                    <a href="<?= htmlspecialchars($baseUrl . '/logout', ENT_QUOTES, 'UTF-8') ?>" class="btn ev-btn-secondary">
                                        <i class="bi bi-box-arrow-right"></i>
                                        Cerrar sesión
                                    </a>

                                    <button type="submit" class="btn ev-btn-primary">
                                        <i class="bi bi-upload"></i>
                                        Enviar comprobante
                                    </button>
                                </div>
                            </form>

                            <div id="evGraciasBox" class="ev-success d-none">
                                <span>
                                    <i class="bi bi-check-lg"></i>
                                </span>
                                <div>
                                    <h3>Comprobante enviado correctamente</h3>
                                    <p>
                                        Recibimos tu archivo. El equipo volverá a revisar tu información a la brevedad.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>

                    <div class="ev-co-hero">
                        <div class="ev-hero-visual">
                            <div class="ev-hero-orb">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div class="ev-hero-check">
                                <i class="bi bi-check2"></i>
                            </div>
                        </div>

                        <div>
                            <div class="ev-eyebrow">VALIDACIÓN EN CURSO</div>
                            <h1 class="ev-title">
                                Estamos revisando tu cuenta
                            </h1>
                            <p class="ev-subtitle">
                                Gracias por registrarte. Estamos verificando tus datos para activar tu acceso
                                de forma segura en <?= htmlspecialchars($textoComunidad, ENT_QUOTES, 'UTF-8') ?>.
                            </p>
                        </div>
                    </div>

                    <div class="ev-status-banner">
                        <div class="ev-status-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h2>La revisión continuará automáticamente</h2>
                            <p>Cuando finalice, podrás ingresar o ver una observación si necesitamos corregir algo.</p>
                        </div>
                    </div>

                    <div class="ev-timeline">
                        <div class="ev-step is-done">
                            <div class="ev-step-dot">
                                <i class="bi bi-check2"></i>
                            </div>
                            <div class="ev-step-title">Registro enviado</div>
                            <div class="ev-step-text">Completado</div>
                        </div>

                        <div class="ev-step is-active">
                            <div class="ev-step-dot">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="ev-step-title">Revisión en curso</div>
                            <div class="ev-step-text">En progreso</div>
                        </div>

                        <div class="ev-step">
                            <div class="ev-step-dot">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div class="ev-step-title">Cuenta activada</div>
                            <div class="ev-step-text">Pendiente</div>
                        </div>
                    </div>

                    <div class="ev-info-grid">
                        <div class="ev-info-card">
                            <span>
                                <i class="bi bi-clipboard-check"></i>
                            </span>
                            <div>
                                <h3>Estado actual</h3>
                                <strong>Revisión en curso</strong>
                                <p>Estamos verificando que la información enviada sea correcta.</p>
                            </div>
                        </div>

                        <div class="ev-info-card">
                            <span>
                                <i class="bi bi-stopwatch"></i>
                            </span>
                            <div>
                                <h3>Tiempo estimado</h3>
                                <strong>24 – 48 horas</strong>
                                <p>Este plazo puede variar según la revisión del equipo.</p>
                            </div>
                        </div>

                        <div class="ev-info-card">
                            <span>
                                <i class="bi bi-clipboard2-check"></i>
                            </span>
                            <div>
                                <h3>Resultado de revisión</h3>
                                <strong>Aprobación u observación</strong>
                                <p>Te informaremos el resultado cuando el equipo termine de revisar tus datos.</p>
                            </div>
                        </div>
                    </div>

                    <div class="ev-co-footer">
                        <div class="ev-support">
                            <span>
                                <i class="bi bi-headset"></i>
                            </span>
                            <div>
                                <h4>¿Tienes alguna duda?</h4>
                                <p>La revisión ayuda a mantener segura la comunidad.</p>

                                <a href="javascript:void(0)" class="ev-support-link js-ev-soporte-link" id="evBtnInfoSupport">
                                    Más información
                                    <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </div>
                        </div>

                        <div class="ev-footer-actions">
                            <a href="<?= htmlspecialchars($baseUrl . '/logout', ENT_QUOTES, 'UTF-8') ?>" class="btn ev-btn-secondary">
                                <i class="bi bi-box-arrow-right"></i>
                                Cerrar sesión
                            </a>

                            <button type="button" class="btn ev-btn-primary" id="evBtnEntendido">
                                Entendido
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </section>
    </div>
</main>

<script src="<?= htmlspecialchars($baseUrl . '/views/js/cuentaObservada.js?v=' . (defined('EV_APP_VER') ? EV_APP_VER : time()), ENT_QUOTES, 'UTF-8') ?>"></script>

</body>
</html>