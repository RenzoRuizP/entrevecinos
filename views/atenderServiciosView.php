<?php
// views/atenderServiciosView.php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
window.BASE_URL = window.BASE_URL || <?= json_encode(rtrim(BASE_URL, '/'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php include_once __DIR__ . '/estilos/atenderServiciosEstilo.php'; ?>

<div class="container-fluid ev-as-page fade-in">
  <section class="ev-as-hero mb-4">
    <div class="ev-as-hero-copy">
      <div class="ev-as-icon"><i class="bi bi-clipboard2-pulse"></i></div>
      <div>
        <div class="ev-as-kicker">SOPORTE · SERVICIOS</div>
        <h2>Atención de servicios</h2>
        <p>Revisa incidencias escaladas, la cotización aceptada, reprogramaciones, evidencias y la trazabilidad antes de registrar una resolución.</p>
      </div>
    </div>
    <button type="button" class="btn ev-as-refresh" id="evAsRefresh"><i class="bi bi-arrow-clockwise me-1"></i>Actualizar</button>
  </section>

  <section class="ev-as-summary mb-4" aria-label="Resumen de incidencias de servicios">
    <article><span>Incidencias abiertas</span><strong id="evAsKpiAbiertas">0</strong><small>Requieren seguimiento</small></article>
    <article><span>Pendientes de soporte</span><strong id="evAsKpiPendientes">0</strong><small>Esperan primera atención</small></article>
    <article><span>Resueltas hoy</span><strong id="evAsKpiResueltas">0</strong><small>Cerradas por soporte</small></article>
  </section>

  <section class="ev-as-panel">
    <header class="ev-as-panel-head">
      <div>
        <h5>Bandeja de incidencias</h5>
        <p>El punto 11 permite resolver el flujo operativo. Las sanciones, bloqueos y moderación ampliada corresponden al punto 14.</p>
      </div>
      <div class="ev-as-filters">
        <div class="ev-as-tabs" role="tablist">
          <button type="button" class="ev-as-tab active" data-estado="abiertas">Abiertas</button>
          <button type="button" class="ev-as-tab" data-estado="resueltas">Resueltas</button>
          <button type="button" class="ev-as-tab" data-estado="all">Todas</button>
        </div>
        <div class="ev-as-search">
          <i class="bi bi-search"></i>
          <input type="search" id="evAsSearch" maxlength="120" placeholder="Buscar servicio, vecino o caso">
        </div>
      </div>
    </header>

    <div class="ev-as-panel-body">
      <div id="evAsError" class="ev-as-alert d-none"></div>
      <div id="evAsLoading" class="ev-as-loading"><span></span><p>Cargando incidencias...</p></div>
      <div id="evAsEmpty" class="ev-as-empty d-none"><i class="bi bi-check2-circle"></i><strong>No hay casos en esta bandeja.</strong></div>
      <div id="evAsList" class="ev-as-list"></div>
    </div>
  </section>
</div>
