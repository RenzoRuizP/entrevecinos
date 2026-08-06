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
      <a class="ev-al-brand" href="<?= BASE_URL ?>" aria-label="Ir al inicio de Entre Vecinos">
        <img src="<?= BASE_URL ?>resources/images/logo/logo_ev_transparente_corregido_recortado.png" alt="Entre Vecinos">
        <span>Entre Vecinos</span>
      </a>

      <section class="ev-al-card" aria-labelledby="evAlTitle">
        <header class="ev-al-header">
          <div class="ev-al-header__copy">
            <span class="ev-al-header__icon" aria-hidden="true"><i class="bi bi-file-earmark-check"></i></span>
            <div>
              <span class="ev-al-kicker">DOCUMENTOS LEGALES</span>
              <h1 id="evAlTitle">Revisa y acepta los documentos vigentes</h1>
              <p>Antes de continuar en Entre Vecinos, revisa los Términos y Condiciones y la Política de Privacidad. Tu aceptación quedará registrada de forma segura.</p>
            </div>
          </div>
          <div class="ev-al-header__status" aria-label="Dos documentos vigentes">
            <strong>2</strong>
            <span>documentos<br>vigentes</span>
          </div>
        </header>

        <div class="ev-al-body-content">
          <section class="ev-al-section" aria-labelledby="evAlDocsTitle">
            <div class="ev-al-section-head">
              <div>
                <span class="ev-al-section-kicker">PASO 1</span>
                <h2 id="evAlDocsTitle">Consulta los documentos</h2>
                <p>Abre cada documento en una pestaña nueva y revisa su contenido antes de aceptar.</p>
              </div>
            </div>

            <div class="ev-al-docs">
              <article class="ev-al-doc">
                <div class="ev-al-doc__top">
                  <span class="ev-al-doc__icon" aria-hidden="true"><i class="bi bi-file-text"></i></span>
                  <div class="ev-al-doc__heading">
                    <span class="ev-al-version">VERSIÓN <?= htmlspecialchars((string)($terminos['version'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars((string)($terminos['titulo'] ?? 'Términos y Condiciones de Uso de Entre Vecinos'), ENT_QUOTES, 'UTF-8') ?></h3>
                  </div>
                </div>
                <p>Define las reglas de registro, uso de la cuenta, publicaciones, operaciones, incidencias, calificaciones y responsabilidades dentro de EV.</p>
                <a href="<?= BASE_URL ?>legal/terminos-y-condiciones" target="_blank" rel="noopener">
                  <span>Leer documento completo</span>
                  <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                </a>
              </article>

              <article class="ev-al-doc">
                <div class="ev-al-doc__top">
                  <span class="ev-al-doc__icon" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
                  <div class="ev-al-doc__heading">
                    <span class="ev-al-version">VERSIÓN <?= htmlspecialchars((string)($privacidad['version'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars((string)($privacidad['titulo'] ?? 'Política de Privacidad y Tratamiento de Datos Personales'), ENT_QUOTES, 'UTF-8') ?></h3>
                  </div>
                </div>
                <p>Explica qué información utiliza EV, para qué finalidades, cómo se protege y cómo puedes ejercer tus derechos sobre tus datos personales.</p>
                <a href="<?= BASE_URL ?>legal/politica-de-privacidad" target="_blank" rel="noopener">
                  <span>Leer documento completo</span>
                  <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                </a>
              </article>
            </div>
          </section>

          <details class="ev-al-privacy-summary">
            <summary>
              <span><i class="bi bi-info-circle"></i> Aviso breve de privacidad</span>
              <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </summary>
            <div class="ev-al-privacy-grid">
              <p><strong>Responsable</strong><span><?= htmlspecialchars((string)($responsable['nombre_legal'] ?? 'Entre Vecinos'), ENT_QUOTES, 'UTF-8') ?><?= !empty($responsable['documento_tributario']) ? ', ' . htmlspecialchars((string)$responsable['documento_tributario'], ENT_QUOTES, 'UTF-8') : '' ?>.</span></p>
              <p><strong>Uso principal</strong><span>Registro, validación de residencia, operaciones, notificaciones, soporte, seguridad y cumplimiento.</span></p>
              <p><strong>Alojamiento</strong><span><?= htmlspecialchars((string)($operacion['ubicacion_alojamiento'] ?? 'São Paulo, Brasil'), ENT_QUOTES, 'UTF-8') ?>.</span></p>
              <p><strong>Consultas</strong><span><a href="mailto:<?= htmlspecialchars((string)($responsable['correo_privacidad'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($responsable['correo_privacidad'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></span></p>
            </div>
          </details>

          <form id="formAceptacionLegal" novalidate>
            <section class="ev-al-consent-panel" aria-labelledby="evAlConsentTitle">
              <div class="ev-al-section-head ev-al-section-head--consent">
                <div>
                  <span class="ev-al-section-kicker">PASO 2</span>
                  <h2 id="evAlConsentTitle">Confirma tu aceptación</h2>
                  <p>Ambos consentimientos son obligatorios e independientes.</p>
                </div>
                <span class="ev-al-secure-badge"><i class="bi bi-shield-check"></i> Registro seguro</span>
              </div>

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

              <p class="ev-al-help"><i class="bi bi-info-circle"></i><span>EV registrará la versión aceptada, la fecha, la hora y la evidencia técnica correspondiente.</span></p>
            </section>

            <div class="ev-al-actions">
              <a class="ev-al-btn ev-al-btn--ghost" href="<?= BASE_URL ?>logout">
                <i class="bi bi-box-arrow-left"></i>
                <span>Cerrar sesión</span>
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
  <script src="<?= BASE_URL ?>views/js/aceptacionLegal.js?v=<?= @filemtime(__DIR__ . '/js/aceptacionLegal.js') ?: time() ?>"></script>
</body>
</html>
