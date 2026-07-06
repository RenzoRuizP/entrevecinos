<?php
// views/misSolicitudesServicioCompradorView.php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/misSolicitudesServicioCompradorEstilo.php'; ?>

<div class="container-fluid ev-ssc-page fade-in">
  <section class="ev-ssc-hero mb-4">
    <div class="ev-ssc-hero-content">
      <div class="ev-ssc-title-wrap">
        <div class="ev-ssc-title-icon" aria-hidden="true">
          <i class="bi bi-chat-square-text"></i>
        </div>
        <div>
          <div class="ev-ssc-kicker">COMPRAR · SERVICIOS</div>
          <h2 class="ev-ssc-title">Mis solicitudes de servicio</h2>
          <p class="ev-ssc-subtitle">
            Mantén toda la negociación dentro de EV, revisa la cotización final y confirma solo cuando las condiciones estén claras.
          </p>
        </div>
      </div>

      <div class="ev-ssc-summary-grid" aria-label="Resumen de solicitudes de servicio">
        <article class="ev-ssc-summary-card">
          <span>Por responder</span>
          <strong id="sscCountResponder">0</strong>
        </article>
        <article class="ev-ssc-summary-card">
          <span>En coordinación</span>
          <strong id="sscCountCoordinacion">0</strong>
        </article>
        <article class="ev-ssc-summary-card">
          <span>Cerradas</span>
          <strong id="sscCountCerradas">0</strong>
        </article>
      </div>
    </div>
  </section>

  <section class="ev-ssc-panel">
    <header class="ev-ssc-panel-head">
      <div>
        <h5>Panel de coordinación</h5>
        <p>La conversación, ubicación, ajustes y cotización final se registran dentro de EV. Abre cada solicitud para continuar la coordinación.</p>
      </div>

      <div class="ev-ssc-head-actions">
        <div class="ev-ssc-tabs" role="tablist" aria-label="Estados de mis solicitudes de servicio">
          <button type="button" class="btn ev-ssc-tab active" data-tab="responder" role="tab">
            Por responder <span id="sscBadgeResponder">0</span>
          </button>
          <button type="button" class="btn ev-ssc-tab" data-tab="coordinacion" role="tab">
            En coordinación <span id="sscBadgeCoordinacion">0</span>
          </button>
          <button type="button" class="btn ev-ssc-tab" data-tab="cerradas" role="tab">
            Cerradas <span id="sscBadgeCerradas">0</span>
          </button>
        </div>

        <button type="button" id="btnRefrescarSolicitudesServicioComprador" class="btn ev-ssc-btn-refresh">
          <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
        </button>
      </div>
    </header>

    <div class="ev-ssc-panel-body">
      <div id="sscError" class="ev-ssc-alert ev-ssc-alert-error d-none">
        No se pudieron cargar tus solicitudes de servicio en este momento.
      </div>

      <div id="sscTabResponder" class="ev-ssc-tab-pane">
        <div id="sscEmptyResponder" class="ev-ssc-empty d-none">
          <i class="bi bi-check2-circle"></i>
          <span>No tienes respuestas pendientes en este momento.</span>
        </div>
        <div id="sscListaResponder" class="ev-ssc-grid"></div>
      </div>

      <div id="sscTabCoordinacion" class="ev-ssc-tab-pane d-none">
        <div id="sscEmptyCoordinacion" class="ev-ssc-empty d-none">
          <i class="bi bi-hourglass-split"></i>
          <span>No tienes solicitudes en coordinación actualmente.</span>
        </div>
        <div id="sscListaCoordinacion" class="ev-ssc-grid"></div>
      </div>

      <div id="sscTabCerradas" class="ev-ssc-tab-pane d-none">
        <div id="sscEmptyCerradas" class="ev-ssc-empty d-none">
          <i class="bi bi-archive"></i>
          <span>Aún no tienes solicitudes de servicio cerradas.</span>
        </div>
        <div id="sscListaCerradas" class="ev-ssc-grid"></div>
      </div>
    </div>
  </section>
</div>

<script src="<?= rtrim(BASE_URL, '/'); ?>/views/js/misSolicitudesServicioComprador.js"></script>
