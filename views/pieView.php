<?php
require_once __DIR__ . '/../Config/config.php';
$baseUrlPie = rtrim((string)BASE_URL, '/');
?>
<footer class="app-footer">
  <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
    <strong>
      &copy; <?= date('Y') ?> <span class="text-success">Entre Vecinos</span>. Todos los derechos reservados.
    </strong>

    <nav class="d-flex flex-wrap align-items-center justify-content-center gap-2" aria-label="Documentos legales">
      <a href="<?= htmlspecialchars($baseUrlPie . '/legal/terminos-y-condiciones', ENT_QUOTES, 'UTF-8') ?>"
         target="_blank" rel="noopener" class="text-decoration-none">
        Términos y Condiciones
      </a>
      <span aria-hidden="true">·</span>
      <a href="<?= htmlspecialchars($baseUrlPie . '/legal/politica-de-privacidad', ENT_QUOTES, 'UTF-8') ?>"
         target="_blank" rel="noopener" class="text-decoration-none">
        Política de Privacidad
      </a>
    </nav>
  </div>
</footer>
