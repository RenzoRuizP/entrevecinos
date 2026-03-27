<?php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/misPedidosCompradorEstilo.php'; ?>

<div class="container-fluid py-4 ev-mpc-page fade-in">
  <div class="ev-mpc-hero card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4 p-lg-4">
      <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
          <div class="ev-mpc-kicker">COMPRAR</div>
          <h2 class="ev-mpc-title mb-1">Mis pedidos</h2>
          <p class="ev-mpc-subtitle mb-0">
            Revisa tus solicitudes, haz seguimiento al avance de cada pedido y consulta tu historial de compras dentro de Entre Vecinos.
          </p>
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

  <div class="card border-0 shadow-sm rounded-4 ev-mpc-card">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
      <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
          <h5 class="mb-1 ev-mpc-block-title">Gestión de pedidos</h5>
          <p class="mb-0 ev-mpc-block-subtitle">
            Seguimiento claro y ordenado de todas tus compras dentro de tu condominio o urbanización.
          </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
          <button type="button" class="btn ev-mpc-tab active" data-tab="pendientes">Pendientes</button>
          <button type="button" class="btn ev-mpc-tab" data-tab="proceso">En proceso</button>
          <button type="button" class="btn ev-mpc-tab" data-tab="finalizados">Finalizados</button>
          <button type="button" id="btnRefrescarMisPedidosComprador" class="btn ev-mpc-btn-refresh">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
          </button>
        </div>
      </div>
    </div>

    <div class="card-body px-4 pb-4">
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