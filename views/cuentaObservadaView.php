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

$modoVista                  = $modoVista ?? 'revision_inicial';
$tipoObservacion            = trim((string)($tipoObservacion ?? 'cuenta_pendiente'));
$mensajeObservacion         = trim((string)($mensajeObservacion ?? ''));
$fechaObservacion           = $fechaObservacion ?? null;
$nombreComunidad            = trim((string)($nombreComunidad ?? ''));
$esCambioResidencia         = (bool)($esCambioResidencia ?? false);
$codigoSolicitudResidencia  = $codigoSolicitudResidencia ?? null;
$estadoSolicitudResidencia  = strtolower(trim((string)($estadoSolicitudResidencia ?? '')));

if (
    $tipoObservacion === 'cambio_residencia'
    || $tipoObservacion === 'cambio_residencia_pendiente'
    || in_array($estadoSolicitudResidencia, ['observada', 'pendiente'], true)
) {
    $esCambioResidencia = true;
}

$esObservado = ($modoVista === 'observado');

$esCambioResidenciaObservada = (
    $esObservado
    && $esCambioResidencia
    && (
        $tipoObservacion === 'cambio_residencia'
        || $estadoSolicitudResidencia === 'observada'
    )
);

$esCambioResidenciaPendiente = (
    !$esObservado
    && $esCambioResidencia
    && (
        $tipoObservacion === 'cambio_residencia_pendiente'
        || $estadoSolicitudResidencia === 'pendiente'
    )
);

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

/*
 * Textos dinámicos según flujo:
 * - cuenta observada
 * - cambio de residencia observado
 * - revisión inicial
 * - cambio de residencia pendiente
 */
if ($esCambioResidenciaObservada) {
    $eyebrowObservado      = 'ACCIÓN REQUERIDA';
    $tituloObservado       = 'Sube tu recibo corregido';
    $subtituloObservado    = 'Tu cambio de residencia necesita una corrección. Revisa la observación y envía un recibo actualizado para continuar.';
    $tituloObservacionBox  = 'Motivo de observación';
    $fallbackObservacion   = 'Se encontró una observación en tu solicitud. Adjunta un recibo corregido y legible para continuar con la validación.';
    $tituloFormulario      = 'Enviar recibo';
    $textoFormulario       = 'Sube el recibo corregido en PDF, JPG, JPEG o PNG.';
    $step1Titulo           = 'Lee la observación';
    $step1Texto            = 'Revisa el motivo indicado.';
    $step2Titulo           = 'Adjunta el recibo';
    $step2Texto            = 'Sube un archivo válido y legible.';
    $step3Titulo           = 'Nueva revisión';
    $step3Texto            = 'Soporte evaluará tu solicitud.';
    $successTitulo         = 'Recibo enviado correctamente';
    $successTexto          = 'Recibimos tu archivo. El equipo volverá a revisar tu solicitud a la brevedad.';
    $tipoSubsanacionValue  = 'cambio_residencia';
    $heroIcon              = 'bi-file-earmark-arrow-up';
} else {
    $eyebrowObservado      = 'ACCIÓN REQUERIDA';
    $tituloObservado       = 'Sube tu recibo corregido';
    $subtituloObservado    = 'Tu cuenta fue observada por soporte. Revisa el motivo indicado y envía un archivo actualizado para continuar con la validación.';
    $tituloObservacionBox  = 'Motivo de observación';
    $fallbackObservacion   = 'Se encontró una observación en tu registro. Vuelve a cargar un recibo corregido y legible para continuar con la validación.';
    $tituloFormulario      = 'Enviar recibo';
    $textoFormulario       = 'Sube el recibo corregido en PDF, JPG, JPEG o PNG.';
    $step1Titulo           = 'Lee la observación';
    $step1Texto            = 'Revisa el motivo indicado.';
    $step2Titulo           = 'Adjunta el recibo';
    $step2Texto            = 'Sube un archivo válido y legible.';
    $step3Titulo           = 'Nueva revisión';
    $step3Texto            = 'Soporte evaluará tu cuenta.';
    $successTitulo         = 'Recibo enviado correctamente';
    $successTexto          = 'Recibimos tu archivo. El equipo volverá a revisar tu información a la brevedad.';
    $tipoSubsanacionValue  = 'cuenta';
    $heroIcon              = 'bi-file-earmark-arrow-up';
}

if ($esCambioResidenciaPendiente) {
    $eyebrowRevision       = 'SOLICITUD EN REVISIÓN';
    $tituloRevision        = 'Estamos validando tu solicitud';
    $subtituloRevision     = 'Recibimos tu recibo corregido. Soporte lo revisará y te avisaremos cuando haya una respuesta.';
    $bannerTitulo          = 'Revisión en proceso';
    $bannerTexto           = 'Te notificaremos si la solicitud es aprobada o si necesitas corregir algún dato.';
    $timeline1Titulo       = 'Solicitud enviada';
    $timeline2Titulo       = 'Revisión de soporte';
    $timeline3Titulo       = 'Cambio aprobado';
    $cardEstadoStrong      = 'En revisión';
    $cardEstadoTexto       = 'Tu recibo corregido fue recibido correctamente.';
    $cardResultadoStrong   = 'Aprobación u observación';
    $cardResultadoTexto    = 'Verás el resultado cuando soporte finalice la revisión.';
    $revisionIcon          = 'bi-house-check';
} else {
    $eyebrowRevision       = 'SOLICITUD EN REVISIÓN';
    $tituloRevision        = 'Estamos validando tu cuenta';
    $subtituloRevision     = 'Recibimos tu registro. Soporte revisará tus datos y te avisaremos cuando haya una respuesta.';
    $bannerTitulo          = 'Revisión en proceso';
    $bannerTexto           = 'Te notificaremos si tu cuenta es aprobada o si necesitas corregir algún dato.';
    $timeline1Titulo       = 'Registro enviado';
    $timeline2Titulo       = 'Revisión de soporte';
    $timeline3Titulo       = 'Cuenta activada';
    $cardEstadoStrong      = 'En revisión';
    $cardEstadoTexto       = 'Tu información fue recibida correctamente.';
    $cardResultadoStrong   = 'Aprobación u observación';
    $cardResultadoTexto    = 'Verás el resultado cuando soporte finalice la revisión.';
    $revisionIcon          = 'bi-hourglass-split';
}

$documentoVisible = 'recibo';
$documentoVisibleTitulo = ucfirst($documentoVisible);

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
  <script src="<?= BASE_URL ?>views/js/evSweetAlert.js?v=<?= @filemtime(__DIR__ . '/js/evSweetAlert.js') ?: time() ?>"></script>

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
    data-tipo-observacion="<?= htmlspecialchars($tipoObservacion, ENT_QUOTES, 'UTF-8') ?>"
    data-es-cambio-residencia="<?= $esCambioResidencia ? '1' : '0' ?>"
    data-codigo-solicitud-residencia="<?= htmlspecialchars((string)($codigoSolicitudResidencia ?? ''), ENT_QUOTES, 'UTF-8') ?>"
    data-estado-solicitud-residencia="<?= htmlspecialchars($estadoSolicitudResidencia, ENT_QUOTES, 'UTF-8') ?>"
>

<main class="ev-co-shell">
    <div class="ev-co-wrap">

        <div class="ev-brand-pill" aria-label="Entre Vecinos">
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

                    <div class="ev-observed-grid">
                        <section class="ev-observed-main">
                            <div class="ev-co-hero ev-co-hero-compact">
                                <div class="ev-hero-visual">
                                    <div class="ev-hero-orb is-warning">
                                        <i class="bi <?= htmlspecialchars($heroIcon, ENT_QUOTES, 'UTF-8') ?>"></i>
                                    </div>
                                    <div class="ev-hero-check is-warning">
                                        <i class="bi bi-pencil-square"></i>
                                    </div>
                                </div>

                                <div class="ev-hero-copy">
                                    <div class="ev-eyebrow is-warning">
                                        <?= htmlspecialchars($eyebrowObservado, ENT_QUOTES, 'UTF-8') ?>
                                    </div>

                                    <h1 class="ev-title">
                                        <?= htmlspecialchars($tituloObservado, ENT_QUOTES, 'UTF-8') ?>
                                    </h1>

                                    <p class="ev-subtitle">
                                        <?= htmlspecialchars($subtituloObservado, ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="ev-observation-card" id="evObservacionBox">
                                <div class="ev-observation-head">
                                    <span>
                                        <i class="bi bi-chat-left-text"></i>
                                    </span>

                                    <div>
                                        <h2><?= htmlspecialchars($tituloObservacionBox, ENT_QUOTES, 'UTF-8') ?></h2>
                                        <p>
                                            <?php if ($fechaObservacionTexto !== ''): ?>
                                                Revisado el <?= htmlspecialchars($fechaObservacionTexto, ENT_QUOTES, 'UTF-8') ?>
                                            <?php else: ?>
                                                Revisa el detalle antes de volver a enviar tu <?= htmlspecialchars($documentoVisible, ENT_QUOTES, 'UTF-8') ?>.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="ev-observation-message">
                                    <?= $mensajeObservacion !== ''
                                        ? htmlspecialchars($mensajeObservacion, ENT_QUOTES, 'UTF-8')
                                        : htmlspecialchars($fallbackObservacion, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>

                            <div class="ev-next-card">
                                <div class="ev-next-head">
                                    <span><i class="bi bi-list-check"></i></span>
                                    <div>
                                        <h2>Qué debes hacer ahora</h2>
                                        <p>Sigue estos pasos para que soporte revise nuevamente tu solicitud.</p>
                                    </div>
                                </div>

                                <div class="ev-next-steps">
                                    <div class="ev-mini-step is-done">
                                        <span>
                                            <i class="bi bi-check2"></i>
                                        </span>
                                        <div>
                                            <h3><?= htmlspecialchars($step1Titulo, ENT_QUOTES, 'UTF-8') ?></h3>
                                            <p><?= htmlspecialchars($step1Texto, ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                    </div>

                                    <div class="ev-mini-step is-active">
                                        <span>
                                            <i class="bi bi-upload"></i>
                                        </span>
                                        <div>
                                            <h3><?= htmlspecialchars($step2Titulo, ENT_QUOTES, 'UTF-8') ?></h3>
                                            <p><?= htmlspecialchars($step2Texto, ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                    </div>

                                    <div class="ev-mini-step">
                                        <span>
                                            <i class="bi bi-shield-check"></i>
                                        </span>
                                        <div>
                                            <h3><?= htmlspecialchars($step3Titulo, ENT_QUOTES, 'UTF-8') ?></h3>
                                            <p><?= htmlspecialchars($step3Texto, ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <aside class="ev-observed-side">
                            <div class="ev-form-card">
                                <h2><?= htmlspecialchars($tituloFormulario, ENT_QUOTES, 'UTF-8') ?></h2>
                                <p>
                                    <?= htmlspecialchars($textoFormulario, ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <form id="evFormReenviar" enctype="multipart/form-data">
                                    <input
                                        type="hidden"
                                        name="tipo_subsanacion"
                                        value="<?= htmlspecialchars($tipoSubsanacionValue, ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                    <?php if ($codigoSolicitudResidencia !== null && $codigoSolicitudResidencia !== ''): ?>
                                        <input
                                            type="hidden"
                                            name="codigo_solicitud_residencia"
                                            value="<?= htmlspecialchars((string)$codigoSolicitudResidencia, ENT_QUOTES, 'UTF-8') ?>"
                                        >
                                    <?php endif; ?>

                                    <div class="ev-upload-block">
                                        <label class="form-label ev-label" for="evComprobante">
                                            <?= htmlspecialchars($documentoVisibleTitulo, ENT_QUOTES, 'UTF-8') ?> corregido
                                        </label>

                                        <input
                                            type="file"
                                            class="ev-file-native"
                                            name="comprobante"
                                            id="evComprobante"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            required
                                        >

                                        <label class="ev-upload-zone" for="evComprobante">
                                            <span class="ev-upload-icon">
                                                <i class="bi bi-cloud-arrow-up"></i>
                                            </span>

                                            <span class="ev-upload-copy">
                                                <strong>Selecciona tu archivo</strong>
                                                <small>PDF, JPG, JPEG o PNG · Máximo 5 MB</small>
                                            </span>

                                            <span class="ev-upload-action">
                                                Buscar
                                            </span>
                                        </label>

                                        <div class="ev-selected-file" id="evSelectedFileName">
                                            Ningún archivo seleccionado
                                        </div>

                                        <div class="ev-help">
                                            Asegúrate de que el archivo sea legible y responda al motivo indicado.
                                        </div>
                                    </div>

                                    <div class="ev-actions">
                                        <button type="submit" class="btn ev-btn-primary">
                                            <i class="bi bi-upload"></i>
                                            Enviar <?= htmlspecialchars($documentoVisible, ENT_QUOTES, 'UTF-8') ?>
                                        </button>

                                        <a href="<?= htmlspecialchars($baseUrl . '/logout', ENT_QUOTES, 'UTF-8') ?>" class="btn ev-btn-secondary">
                                            <i class="bi bi-box-arrow-right"></i>
                                            Cerrar sesión
                                        </a>
                                    </div>
                                </form>

                                <div id="evGraciasBox" class="ev-success d-none">
                                    <span>
                                        <i class="bi bi-check-lg"></i>
                                    </span>
                                    <div>
                                        <h3><?= htmlspecialchars($successTitulo, ENT_QUOTES, 'UTF-8') ?></h3>
                                        <p>
                                            <?= htmlspecialchars($successTexto, ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="ev-support-mini ev-support-mini-premium">
                                <i class="bi bi-headset"></i>
                                <div>
                                    <strong>¿Necesitas ayuda?</strong>
                                    <div>Lee el motivo indicado y adjunta el <?= htmlspecialchars($documentoVisible, ENT_QUOTES, 'UTF-8') ?> corregido.</div>
                                </div>
                            </div>
                        </aside>
                    </div>

                <?php else: ?>

                    <div class="ev-co-hero">
                        <div class="ev-hero-visual">
                            <div class="ev-hero-orb">
                                <i class="bi <?= htmlspecialchars($revisionIcon, ENT_QUOTES, 'UTF-8') ?>"></i>
                            </div>
                            <div class="ev-hero-check">
                                <i class="bi bi-check2"></i>
                            </div>
                        </div>

                        <div>
                            <div class="ev-eyebrow">
                                <?= htmlspecialchars($eyebrowRevision, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <h1 class="ev-title">
                                <?= htmlspecialchars($tituloRevision, ENT_QUOTES, 'UTF-8') ?>
                            </h1>
                            <p class="ev-subtitle">
                                <?= htmlspecialchars($subtituloRevision, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </div>

                    <div class="ev-status-banner">
                        <div class="ev-status-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h2><?= htmlspecialchars($bannerTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
                            <p><?= htmlspecialchars($bannerTexto, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                    <div class="ev-timeline">
                        <div class="ev-step is-done">
                            <div class="ev-step-dot">
                                <i class="bi bi-check2"></i>
                            </div>
                            <div>
                                <div class="ev-step-title">
                                    <?= htmlspecialchars($timeline1Titulo, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="ev-step-text">Completado</div>
                            </div>
                        </div>

                        <div class="ev-step is-active">
                            <div class="ev-step-dot">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <div class="ev-step-title">
                                    <?= htmlspecialchars($timeline2Titulo, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="ev-step-text">En progreso</div>
                            </div>
                        </div>

                        <div class="ev-step">
                            <div class="ev-step-dot">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div>
                                <div class="ev-step-title">
                                    <?= htmlspecialchars($timeline3Titulo, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="ev-step-text">Pendiente</div>
                            </div>
                        </div>
                    </div>

                    <div class="ev-info-grid">
                        <div class="ev-info-card">
                            <span>
                                <i class="bi bi-clipboard-check"></i>
                            </span>
                            <div>
                                <h3>Estado actual</h3>
                                <strong><?= htmlspecialchars($cardEstadoStrong, ENT_QUOTES, 'UTF-8') ?></strong>
                                <p><?= htmlspecialchars($cardEstadoTexto, ENT_QUOTES, 'UTF-8') ?></p>
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
                                <strong><?= htmlspecialchars($cardResultadoStrong, ENT_QUOTES, 'UTF-8') ?></strong>
                                <p><?= htmlspecialchars($cardResultadoTexto, ENT_QUOTES, 'UTF-8') ?></p>
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