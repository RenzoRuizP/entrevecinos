<?php
// views/atenderLibroReclamacionesView.php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
window.BASE_URL = window.BASE_URL ?? <?= json_encode(rtrim(BASE_URL, '/'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php include_once __DIR__ . '/estilos/atenderLibroReclamacionesEstilo.php'; ?>

<div class="container-fluid ev-lr-page fade-in">
  <section class="ev-lr-hero mb-4">
    <div class="ev-lr-hero-copy">
      <div class="ev-lr-icon"><i class="bi bi-journal-check"></i></div>
      <div>
        <div class="ev-lr-kicker">SOPORTE · ATENCIÓN AL CONSUMIDOR</div>
        <h2>Libro de Reclamaciones</h2>
        <p>Revisa, responde y conserva la trazabilidad de los reclamos y quejas registrados en la página pública de EV.</p>
      </div>
    </div>
    <div class="ev-lr-hero-actions">
      <a class="btn ev-lr-secondary" href="https://www.entrevecinos.pe/libro-de-reclamaciones/" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i>Ver formulario público</a>
      <button type="button" class="btn ev-lr-refresh" id="evLrRefresh"><i class="bi bi-arrow-clockwise me-1"></i>Actualizar</button>
    </div>
  </section>

  <section class="ev-lr-summary mb-4" aria-label="Resumen del Libro de Reclamaciones">
    <article><span>Nuevos</span><strong id="evLrKpiNuevos">0</strong><small>Pendientes de primera revisión</small></article>
    <article><span>En revisión</span><strong id="evLrKpiRevision">0</strong><small>Actualmente en atención</small></article>
    <article><span>Respondidos</span><strong id="evLrKpiRespondidos">0</strong><small>Con respuesta registrada</small></article>
    <article><span>Recibidos hoy</span><strong id="evLrKpiHoy">0</strong><small>Nuevos registros del día</small></article>
  </section>

  <section class="ev-lr-panel">
    <header class="ev-lr-panel-head">
      <div>
        <h5>Bandeja de reclamos y quejas</h5>
        <p>La respuesta debe ser clara, completa y registrarse dentro del plazo legal. La bandeja conserva el historial de cada actuación.</p>
      </div>
      <div class="ev-lr-filters">
        <div class="ev-lr-tabs" role="tablist" aria-label="Estado">
          <button type="button" class="ev-lr-tab active" data-estado="pendientes">Pendientes</button>
          <button type="button" class="ev-lr-tab" data-estado="respondido">Respondidos</button>
          <button type="button" class="ev-lr-tab" data-estado="cerrado">Cerrados</button>
          <button type="button" class="ev-lr-tab" data-estado="all">Todos</button>
        </div>
        <select id="evLrTipo" class="form-select ev-lr-select" aria-label="Tipo">
          <option value="all">Reclamos y quejas</option>
          <option value="reclamo">Solo reclamos</option>
          <option value="queja">Solo quejas</option>
        </select>
        <div class="ev-lr-search"><i class="bi bi-search"></i><input type="search" id="evLrSearch" maxlength="120" placeholder="Número, vecino, DNI o correo"></div>
      </div>
    </header>

    <div class="ev-lr-panel-body">
      <div id="evLrError" class="ev-lr-alert d-none"></div>
      <div id="evLrLoading" class="ev-lr-loading"><span></span><p>Cargando registros...</p></div>
      <div id="evLrEmpty" class="ev-lr-empty d-none"><i class="bi bi-inbox"></i><strong>No hay registros en esta bandeja.</strong></div>
      <div id="evLrList" class="ev-lr-list"></div>
      <div id="evLrPagination" class="ev-lr-pagination d-none"></div>
    </div>
  </section>
</div>
