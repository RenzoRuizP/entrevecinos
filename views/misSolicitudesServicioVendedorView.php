<?php
// views/misSolicitudesServicioVendedorView.php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/misSolicitudesServicioVendedorEstilo.php'; ?>

<div class="container-fluid ev-ssv-page fade-in">
  <section class="ev-ssv-hero mb-4">
    <div class="ev-ssv-hero-content">
      <div class="ev-ssv-title-wrap">
        <div class="ev-ssv-title-icon" aria-hidden="true">
          <i class="bi bi-clipboard2-check"></i>
        </div>
        <div>
          <div class="ev-ssv-kicker">VENDER · SERVICIOS</div>
          <h2 class="ev-ssv-title">Solicitudes de servicio</h2>
          <p class="ev-ssv-subtitle">
            Atiende solicitudes, emite cotizaciones y gestiona la ejecución, reprogramaciones, incidencias y calificaciones dentro de EV.
          </p>
        </div>
      </div>

      <div class="ev-ssv-summary-grid" aria-label="Resumen de solicitudes">
        <article class="ev-ssv-summary-card">
          <span>Pendientes</span>
          <strong id="ssvCountPendientes">0</strong>
        </article>
        <article class="ev-ssv-summary-card">
          <span>Esperando respuesta</span>
          <strong id="ssvCountEsperando">0</strong>
        </article>
        <article class="ev-ssv-summary-card">
          <span>Cerradas</span>
          <strong id="ssvCountCerradas">0</strong>
        </article>
      </div>
    </div>
  </section>

  <section class="ev-ssv-panel">
    <header class="ev-ssv-panel-head">
      <div>
        <h5>Panel de coordinación</h5>
        <p>La cotización aceptada fija el acuerdo comercial. La ejecución y sus novedades se administran desde Gestionar servicio con trazabilidad completa.</p>
      </div>

      <div class="ev-ssv-head-actions">
        <div class="ev-ssv-tabs" role="tablist" aria-label="Estados de solicitudes de servicio">
          <button type="button" class="btn ev-ssv-tab active" data-tab="pendientes" role="tab">
            Pendientes <span id="ssvBadgePendientes">0</span>
          </button>
          <button type="button" class="btn ev-ssv-tab" data-tab="esperando" role="tab">
            Esperando respuesta <span id="ssvBadgeEsperando">0</span>
          </button>
          <button type="button" class="btn ev-ssv-tab" data-tab="cerradas" role="tab">
            Cerradas <span id="ssvBadgeCerradas">0</span>
          </button>
        </div>

        <button type="button" id="btnRefrescarSolicitudesServicio" class="btn ev-ssv-btn-refresh">
          <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
        </button>
      </div>
    </header>

    <div class="ev-ssv-panel-body">
      <div id="ssvError" class="ev-ssv-alert ev-ssv-alert-error d-none">
        No se pudieron cargar tus solicitudes de servicio en este momento.
      </div>

      <div id="ssvTabPendientes" class="ev-ssv-tab-pane">
        <div id="ssvEmptyPendientes" class="ev-ssv-empty d-none">
          <i class="bi bi-inbox"></i>
          <span>No tienes solicitudes de servicio pendientes por atender.</span>
        </div>
        <div id="ssvListaPendientes" class="ev-ssv-grid"></div>
      </div>

      <div id="ssvTabEsperando" class="ev-ssv-tab-pane d-none">
        <div id="ssvEmptyEsperando" class="ev-ssv-empty d-none">
          <i class="bi bi-hourglass-split"></i>
          <span>No tienes solicitudes esperando respuesta del solicitante.</span>
        </div>
        <div id="ssvListaEsperando" class="ev-ssv-grid"></div>
      </div>

      <div id="ssvTabCerradas" class="ev-ssv-tab-pane d-none">
        <div id="ssvEmptyCerradas" class="ev-ssv-empty d-none">
          <i class="bi bi-archive"></i>
          <span>Aún no tienes solicitudes cerradas.</span>
        </div>
        <div id="ssvListaCerradas" class="ev-ssv-grid"></div>
      </div>
    </div>
  </section>
</div>

<script src="<?= rtrim(BASE_URL, '/'); ?>/views/js/misSolicitudesServicioVendedor.js"></script>
