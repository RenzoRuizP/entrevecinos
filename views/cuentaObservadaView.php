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

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            <div class="ev-card p-4 p-lg-5">

                <!-- =========================
                     HEADER DINÁMICO
                ========================== -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="ev-ico">
                        <?php if ($modoVista === 'observado'): ?>
                            <i class="bi bi-exclamation-triangle"></i>
                        <?php else: ?>
                            <i class="bi bi-hourglass-split"></i>
                        <?php endif; ?>
                    </span>
                    <div>

                        <?php if ($modoVista === 'observado'): ?>
                            <h1 class="ev-title mb-1">Tu cuenta tiene una observación</h1>
                            <p class="ev-subtitle mb-0">
                                Revisa el mensaje y subsana la observación para continuar.
                            </p>
                        <?php else: ?>
                            <h1 class="ev-title mb-1">Tu cuenta está siendo revisada</h1>
                            <p class="ev-subtitle mb-0">
                                Nuestro equipo está validando tu información.
                            </p>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- =========================
                     MENSAJE OBSERVACIÓN
                ========================== -->
                <?php if ($modoVista === 'observado'): ?>
                    <div id="evObservacionBox" class="ev-alert mb-4">
                        <div class="fw-semibold mb-1">
                            Observación del equipo de soporte:
                        </div>
                        <div class="ev-observacion-text">
                            <?= $mensajeObservacion !== ''
                                ? htmlspecialchars($mensajeObservacion, ENT_QUOTES, 'UTF-8')
                                : 'No se especificó el motivo de la observación.' ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- =========================
                     FORMULARIO (solo observado)
                ========================== -->
                <?php if ($modoVista === 'observado'): ?>
                    <form id="evFormReenviar" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Sube tu nuevo comprobante
                            </label>
                            <input
                                type="file"
                                class="form-control"
                                name="comprobante"
                                id="evComprobante"
                                accept=".pdf,.jpg,.jpeg,.png,.webp"
                                required
                            >
                            <div class="form-text">
                                Formatos permitidos: PDF, JPG, PNG, WEBP. Máx. 5MB.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn ev-btn-orange">
                                <i class="bi bi-upload me-1"></i>
                                Enviar comprobante
                            </button>
                            <a href="<?= $baseUrl ?>/logout" class="btn btn-outline-secondary">
                                Cerrar sesión
                            </a>
                        </div>
                    </form>

                    <!-- =========================
                         MENSAJE FINAL (JS)
                    ========================== -->
                    <div id="evGraciasBox" class="ev-success mt-4 d-none">
                        <i class="bi bi-check-circle"></i>
                        <div>
                            <div class="fw-bold">¡Listo!</div>
                            <div>
                                Tu comprobante fue enviado correctamente.
                                Nuestro equipo lo revisará a la brevedad.
                            </div>
                        </div>
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
