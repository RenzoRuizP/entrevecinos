<?php
// views/cuentaObservadaView.php
// Página COMPLETA (sin menú, sin shell)

if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

$baseUrl = rtrim(BASE_URL, '/');

// Blindaje
$modoVista          = $modoVista ?? 'revision_inicial';
$mensajeObservacion = trim((string)($mensajeObservacion ?? ''));
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

    <!-- Estilos EV -->
    <?php
    if (defined('VIEW_STYLE_PATH')) {
        $styleFile = rtrim(VIEW_STYLE_PATH, '/') . '/cuentaObservadaEstilo.php';
        if (file_exists($styleFile)) {
            require_once $styleFile;
        }
    }
    ?>
</head>

<body class="ev-co-page">

<main class="ev-shell">
    <div class="ev-wrap">

        <div class="ev-brand">
            <div class="ev-brand-badge">
                <i class="bi bi-house-heart"></i>
            </div>
            <div>Entre Vecinos</div>
        </div>

        <div class="ev-card">
            <div class="ev-card-body">

                <!-- =========================
                     HEADER DINÁMICO
                ========================== -->
                <div class="ev-head">
                    <span class="ev-ico <?= $modoVista === 'observado' ? 'is-warning' : 'is-review' ?>">
                        <?php if ($modoVista === 'observado'): ?>
                            <i class="bi bi-exclamation-triangle"></i>
                        <?php else: ?>
                            <i class="bi bi-hourglass-split"></i>
                        <?php endif; ?>
                    </span>

                    <div class="flex-grow-1">
                        <?php if ($modoVista === 'observado'): ?>
                            <h1 class="ev-title">Tu cuenta tiene una observación</h1>
                            <p class="ev-subtitle">
                                Revisamos tu registro y necesitamos que corrijas la información observada para continuar con la validación de tu cuenta.
                            </p>
                        <?php else: ?>
                            <h1 class="ev-title">Estamos validando tu cuenta</h1>
                            <p class="ev-subtitle">
                                Tu registro fue recibido correctamente. Nuestro equipo revisará tu información para activar tu acceso a Entre Vecinos.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- =========================
                     MENSAJES SEGÚN MODO
                ========================== -->
                <?php if ($modoVista === 'observado'): ?>

                    <div id="evObservacionBox" class="ev-alert">
                        <div class="ev-alert-head">
                            <i class="bi bi-chat-left-text"></i>
                            <span>Observación del equipo de soporte</span>
                        </div>

                        <div class="ev-observacion-text">
                            <?= $mensajeObservacion !== ''
                                ? htmlspecialchars($mensajeObservacion, ENT_QUOTES, 'UTF-8')
                                : 'Se encontró una observación en tu registro. Por favor, vuelve a cargar el comprobante corregido para continuar.' ?>
                        </div>
                    </div>

                    <div class="ev-form-wrap">
                        <h2 class="ev-section-title">Sube tu comprobante corregido</h2>
                        <p class="ev-section-subtitle">
                            Cuando envíes el nuevo archivo, nuestro equipo volverá a revisarlo para continuar con la validación de tu cuenta.
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
                                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                                    required
                                >
                                <div class="ev-help mt-2">
                                    Formatos permitidos: PDF, JPG, JPEG, PNG y WEBP. Tamaño máximo: 5 MB.
                                </div>
                            </div>

                            <div class="ev-actions">
                                <button type="submit" class="btn ev-btn-orange">
                                    <i class="bi bi-upload me-1"></i>
                                    Enviar comprobante
                                </button>

                                <a href="<?= $baseUrl ?>/logout" class="btn ev-btn-light">
                                    Cerrar sesión
                                </a>
                            </div>
                        </form>

                        <div id="evGraciasBox" class="ev-success mt-4 d-none">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <div class="ev-success-title">Comprobante enviado correctamente</div>
                                <div class="ev-success-text">
                                    Recibimos tu archivo y será revisado por nuestro equipo a la brevedad.
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>

                    <div class="ev-status-band">
                        <div class="ev-info">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <div class="ev-info-title">Tu registro fue recibido correctamente</div>
                                <div class="ev-info-text">
                                    Podrás ingresar cuando tu cuenta haya sido aprobada.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ev-footnote">
                        Este proceso ayuda a mantener una comunidad más segura y confiable para todos los vecinos.
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</main>

<!-- =========================
     JS
========================== -->
<script>
    window.BASE_URL = "<?= $baseUrl ?>";
    window.EV_MODO_VISTA = "<?= $modoVista ?>";
</script>

<script src="<?= $baseUrl ?>/views/js/cuentaObservada.js?v=<?= defined('EV_APP_VER') ? EV_APP_VER : time() ?>"></script>

</body>
</html>