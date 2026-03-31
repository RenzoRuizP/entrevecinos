<?php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/misPedidosCompradorEstilo.php'; ?>

<div class="container-fluid ev-mpc-page fade-in">
  <div class="ev-mpc-card-shell ev-mpc-hero mb-4">
    <div class="ev-mpc-hero-body">
      <div class="ev-mpc-hero-top">
        <div class="ev-mpc-title-wrap">
          <div class="ev-mpc-title-icon">
            <i class="bi bi-bag-check"></i>
          </div>
          <div>
            <div class="ev-mpc-kicker">COMPRAR</div>
            <h2 class="ev-mpc-title">Mis pedidos</h2>
            <p class="ev-mpc-subtitle">
              Revisa tus solicitudes, haz seguimiento a cada compra y confirma la entrega con una experiencia clara y alineada al estilo EV.
            </p>
          </div>
        </div>

        <div class="ev-mpc-summary-grid">
          <div class="ev-mpc-summary-card">
            <span class="ev-mpc-summary-label">Pendientes</span>
            <strong id="mpcCountPendientes">0</strong>
          </div>
          <div class="ev-mpc-summary-card">
            <span class="ev-mpc-summary-label">En proceso</span>
            <strong id="mpcCountProceso">0</strong>
          </div>
          <div class="ev-mpc-summary-card">
            <span class="ev-mpc-summary-label">Finalizados</span>
            <strong id="mpcCountFinalizados">0</strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="ev-mpc-panel">
    <div class="ev-mpc-panel-head">
      <div class="ev-mpc-panel-head-row">
        <div>
          <h5 class="ev-mpc-block-title">Panel operativo</h5>
          <p class="ev-mpc-block-subtitle">
            Consulta tus pedidos activos, revisa el avance de cada compra y accede rápidamente a tu historial.
          </p>
        </div>

        <div class="ev-mpc-actions-top">
          <div class="ev-mpc-tab-groups">
            <div class="ev-mpc-tab-group">
              <span class="ev-mpc-tab-group-label">Pedidos</span>
              <div class="ev-mpc-tabs">
                <button type="button" class="btn ev-mpc-tab active" data-tab="pendientes">
                  Pendientes
                  <span class="ev-mpc-tab-badge" id="mpcBadgePendientes">0</span>
                </button>
                <button type="button" class="btn ev-mpc-tab" data-tab="proceso">
                  En proceso
                  <span class="ev-mpc-tab-badge" id="mpcBadgeProceso">0</span>
                </button>
                <button type="button" class="btn ev-mpc-tab" data-tab="finalizados">
                  Finalizados
                  <span class="ev-mpc-tab-badge" id="mpcBadgeFinalizados">0</span>
                </button>
              </div>
            </div>
          </div>

          <button type="button" id="btnRefrescarMisPedidosComprador" class="btn ev-mpc-btn-refresh">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
          </button>
        </div>
      </div>
    </div>

    <div class="ev-mpc-panel-body">
      <div id="mpcError" class="ev-mpc-alert ev-mpc-alert-error d-none">
        No se pudieron cargar tus pedidos en este momento.
      </div>

      <div id="mpcTabPendientes" class="ev-mpc-tab-pane">
        <div id="mpcEmptyPendientes" class="ev-mpc-empty d-none">
          No tienes solicitudes pendientes actualmente.
        </div>
        <div id="mpcListaPendientes" class="ev-mpc-grid"></div>
      </div>

      <div id="mpcTabProceso" class="ev-mpc-tab-pane d-none">
        <div id="mpcEmptyProceso" class="ev-mpc-empty d-none">
          No tienes pedidos en proceso actualmente.
        </div>
        <div id="mpcListaProceso" class="ev-mpc-grid"></div>
      </div>

      <div id="mpcTabFinalizados" class="ev-mpc-tab-pane d-none">
        <div id="mpcEmptyFinalizados" class="ev-mpc-empty d-none">
          Aún no tienes pedidos finalizados.
        </div>
        <div id="mpcListaFinalizados" class="ev-mpc-grid"></div>
      </div>
    </div>
  </div>
</div>