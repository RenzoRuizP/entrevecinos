<?php
// views/documentoLegalPublicoView.php
/** @var array $documento */
/** @var array $legalConfig */

$tipo = (string)($documento['tipo'] ?? '');
$titulo = (string)($documento['titulo'] ?? 'Documento legal');
$version = (string)($documento['version'] ?? '');
$contenido = (string)($documento['contenido_html'] ?? '');
$esTerminos = $tipo === 'terminos_condiciones';
$otraRuta = $esTerminos ? '/legal/politica-de-privacidad' : '/legal/terminos-y-condiciones';
$otraEtiqueta = $esTerminos ? 'Política de Privacidad' : 'Términos y Condiciones';
$modoBorrador = !empty($legalConfig['modo_borrador']);
$responsable = $legalConfig['responsable'] ?? [];
$sitioWeb = rtrim((string)($responsable['sitio_web'] ?? 'https://www.entrevecinos.pe'), '/');
$fechaVigenciaRaw = (string)($documento['fecha_vigencia'] ?? '2026-07-12 00:00:00');
$fechaVigenciaTs = strtotime($fechaVigenciaRaw) ?: strtotime('2026-07-12');
$mesesEs = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaVigenciaTexto = date('j', $fechaVigenciaTs) . ' de ' . $mesesEs[(int)date('n', $fechaVigenciaTs)] . ' de ' . date('Y', $fechaVigenciaTs);

$headings = [];
if (preg_match_all('/<section\s+id="([^"]+)"[^>]*>\s*<h2>(.*?)<\/h2>/is', $contenido, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $id = trim((string)($match[1] ?? ''));
        $text = trim(strip_tags((string)($match[2] ?? '')));
        if ($id !== '' && $text !== '') {
            $headings[] = ['id' => $id, 'text' => $text];
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?> | Entre Vecinos</title>
  <meta name="robots" content="index,follow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <?php include __DIR__ . '/estilos/documentosLegalesEstilo.php'; ?>
</head>
<body class="ev-legal-body">
  <header class="ev-legal-topbar">
    <div class="ev-legal-topbar__inner">
      <a class="ev-legal-brand" href="<?= BASE_URL ?>">
        <img src="<?= BASE_URL ?>resources/images/logo/logo_ev_transparente_corregido_recortado.png" alt="Entre Vecinos">
        <span>Entre Vecinos</span>
      </a>
      <div class="ev-legal-actions">
        <a class="ev-legal-btn" href="<?= BASE_URL . ltrim($otraRuta, '/') ?>">
          <i class="bi bi-file-earmark-text"></i>
          <span><?= htmlspecialchars($otraEtiqueta, ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <a class="ev-legal-btn" href="<?= htmlspecialchars($sitioWeb . '/libro-de-reclamaciones/', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
          <i class="bi bi-journal-check"></i>
          <span>Libro de Reclamaciones</span>
        </a>
        <button class="ev-legal-btn ev-legal-btn--primary" type="button" onclick="window.print()">
          <i class="bi bi-printer"></i>
          <span>Imprimir</span>
        </button>
      </div>
    </div>
  </header>

  <section class="ev-legal-hero">
    <div class="ev-legal-hero__inner">
      <span class="ev-legal-kicker"><i class="bi bi-shield-check"></i> Documento legal de EV</span>
      <h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
      <p>Consulta la versión vigente aplicable al registro y uso de Entre Vecinos.</p>
      <div class="ev-legal-meta">
        <span><strong>Versión:</strong> <?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8') ?></span>
        <span><strong>Vigencia:</strong> <?= htmlspecialchars($fechaVigenciaTexto, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
  </section>

  <?php if ($modoBorrador): ?>
    <aside class="ev-legal-draft" role="note">
      <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
      <div>
        <strong>Versión de prepublicación</strong>
        <p>La identidad y los canales oficiales ya fueron incorporados. Antes de habilitar registros reales debe completarse la inscripción del banco de datos personales, consignarse su código y realizarse la validación jurídica final de la versión que será publicada.</p>
      </div>
    </aside>
  <?php endif; ?>

  <main class="ev-legal-layout">
    <nav class="ev-legal-toc" aria-label="Contenido del documento">
      <h2>Contenido</h2>
      <div class="ev-legal-toc__links">
        <?php foreach ($headings as $heading): ?>
          <a href="#<?= htmlspecialchars($heading['id'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($heading['text'], ENT_QUOTES, 'UTF-8') ?>
          </a>
        <?php endforeach; ?>
      </div>
    </nav>

    <article class="ev-legal-card">
      <?= $contenido ?>
    </article>
  </main>

  <footer class="ev-legal-footer">
    <p>
      &copy; <?= date('Y') ?> Entre Vecinos ·
      <a href="<?= BASE_URL ?>legal/terminos-y-condiciones">Términos y Condiciones</a> ·
      <a href="<?= BASE_URL ?>legal/politica-de-privacidad">Política de Privacidad</a> ·
      <a href="<?= htmlspecialchars($sitioWeb . '/libro-de-reclamaciones/', ENT_QUOTES, 'UTF-8') ?>">Libro de Reclamaciones</a>
    </p>
  </footer>
</body>
</html>
