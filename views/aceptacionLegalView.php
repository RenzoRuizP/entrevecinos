<?php
// views/aceptacionLegalView.php
/** @var array $documentos */
/** @var array $pendientes */
/** @var array $legalConfig */

$porTipo = [];
foreach ($documentos as $doc) {
    $porTipo[(string)($doc['tipo'] ?? '')] = $doc;
}
$terminos = $porTipo['terminos_condiciones'] ?? [];
$privacidad = $porTipo['politica_privacidad'] ?? [];
$modoBorrador = !empty($legalConfig['modo_borrador']);
$responsable = $legalConfig['responsable'] ?? [];
$operacion = $legalConfig['operacion'] ?? [];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aceptación de documentos legales | Entre Vecinos</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php include __DIR__ . '/estilos/aceptacionLegalEstilo.php'; ?>
</head>
<body class="ev-al-body">
  <main class="ev-al-page">
    <div class="ev-al-shell">
      <div class="ev-al-brand">
        <img src="<?= BASE_URL ?>resources/images/logo/logo_ev_transparente_corregido_recortado.png" alt="Entre Vecinos">
      </div>

      <section class="ev-al-card" aria-labelledby="evAlTitle">
        <header class="ev-al-header">
          <div class="ev-al-header__icon"><i class="bi bi-file-earmark-check"></i></div>
          <h1 id="evAlTitle">Revisa y acepta los documentos vigentes</h1>
          <p>Actualizamos el marco de uso y privacidad de EV. Para continuar, revisa ambos documentos y registra tu aceptación.</p>
        </header>

        <div class="ev-al-body-content">
          <?php if ($modoBorrador): ?>
            <div class="ev-al-notice">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <div><strong>Versión de prepublicación:</strong> la identidad y los canales oficiales ya fueron incorporados. Antes de habilitar registros reales debe completarse la inscripción del banco de datos personales, consignarse su código y realizarse la validación jurídica final.</div>
            </div>
          <?php endif; ?>

          <div class="ev-al-docs">
            <article class="ev-al-doc">
              <div class="ev-al-doc__top">
                <div class="ev-al-doc__icon"><i class="bi bi-file-text"></i></div>
                <div>
                  <h2><?= htmlspecialchars((string)($terminos['titulo'] ?? 'Términos y Condiciones'), ENT_QUOTES, 'UTF-8') ?></h2>
                  <div class="ev-al-version">Versión <?= htmlspecialchars((string)($terminos['version'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <p>Regula el registro, uso de la cuenta, productos, servicios, pagos, incidencias, calificaciones y responsabilidades dentro de EV.</p>
              <a href="<?= BASE_URL ?>legal/terminos-y-condiciones" target="_blank" rel="noopener">
                Leer documento completo <i class="bi bi-box-arrow-up-right"></i>
              </a>
            </article>

            <article class="ev-al-doc">
              <div class="ev-al-doc__top">
                <div class="ev-al-doc__icon"><i class="bi bi-shield-lock"></i></div>
                <div>
                  <h2><?= htmlspecialchars((string)($privacidad['titulo'] ?? 'Política de Privacidad'), ENT_QUOTES, 'UTF-8') ?></h2>
                  <div class="ev-al-version">Versión <?= htmlspecialchars((string)($privacidad['version'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <p>Explica qué datos utiliza EV, para qué finalidades, cómo se protegen y cómo ejercer los derechos sobre la información personal.</p>
              <a href="<?= BASE_URL ?>legal/politica-de-privacidad" target="_blank" rel="noopener">
                Leer documento completo <i class="bi bi-box-arrow-up-right"></i>
              </a>
            </article>
          </div>

          <details class="ev-al-privacy-summary">
            <summary>
              <span><i class="bi bi-info-circle"></i> Aviso breve de privacidad</span>
              <i class="bi bi-chevron-down"></i>
            </summary>
            <div>
              <p><strong>Responsable:</strong> <?= htmlspecialchars((string)($responsable['nombre_legal'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string)($responsable['documento_tributario'] ?? ''), ENT_QUOTES, 'UTF-8') ?>.</p>
              <p><strong>Uso de tus datos:</strong> cuenta, validación de residencia, operaciones, notificaciones, soporte, seguridad y cumplimiento.</p>
              <p><strong>Alojamiento:</strong> <?= htmlspecialchars((string)($operacion['ubicacion_alojamiento'] ?? 'São Paulo, Brasil'), ENT_QUOTES, 'UTF-8') ?>.</p>
              <p><strong>Derechos y consultas:</strong> <a href="mailto:<?= htmlspecialchars((string)($responsable['correo_privacidad'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($responsable['correo_privacidad'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>.</p>
            </div>
          </details>

          <form id="formAceptacionLegal" novalidate>
            <div class="ev-al-consents">
              <div class="ev-al-check" id="wrapAceptaTerminos">
                <input type="checkbox" id="aceptaTerminosLegal" name="acepta_terminos" value="1" required>
                <label for="aceptaTerminosLegal">
                  <?= htmlspecialchars((string)($terminos['texto_consentimiento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  <a href="<?= BASE_URL ?>legal/terminos-y-condiciones" target="_blank" rel="noopener">Leer Términos</a>.
                </label>
              </div>

              <div class="ev-al-check" id="wrapAceptaPrivacidad">
                <input type="checkbox" id="aceptaPrivacidadLegal" name="acepta_privacidad" value="1" required>
                <label for="aceptaPrivacidadLegal">
                  <?= htmlspecialchars((string)($privacidad['texto_consentimiento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  <a href="<?= BASE_URL ?>legal/politica-de-privacidad" target="_blank" rel="noopener">Leer Política</a>.
                </label>
              </div>
            </div>

            <p class="ev-al-help"><i class="bi bi-info-circle"></i> Ambos consentimientos son obligatorios. Los checkboxes comienzan desmarcados y EV registrará la versión, fecha, hora y evidencia técnica de la aceptación.</p>

            <div class="ev-al-actions">
              <a class="ev-al-btn ev-al-btn--ghost" href="<?= BASE_URL ?>logout">
                <i class="bi bi-box-arrow-left"></i> Cerrar sesión
              </a>
              <button class="ev-al-btn ev-al-btn--primary" type="submit" id="btnAceptarLegal" disabled>
                <i class="bi bi-check2-circle"></i>
                <span>Aceptar y continuar</span>
              </button>
            </div>
          </form>
        </div>
      </section>

      <div class="ev-al-footer">Entre Vecinos · Documentos legales y consentimientos</div>
    </div>
  </main>

  <script>window.BASE_URL = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="<?= BASE_URL ?>views/js/aceptacionLegal.js"></script>
</body>
</html>
