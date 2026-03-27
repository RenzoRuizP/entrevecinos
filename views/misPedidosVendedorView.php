<?php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/misPedidosVendedorEstilo.php'; ?>

<div class="container-fluid ev-mpv-page fade-in">
  <div class="ev-mpv-card-shell ev-mpv-hero mb-4">
    <div class="ev-mpv-hero-body">
      <div class="ev-mpv-hero-top">
        <div class="ev-mpv-title-wrap">
          <div class="ev-mpv-title-icon">
            <i class="bi bi-bag-check"></i>
          </div>
          <div>
            <div class="ev-mpv-kicker">VENDER</div>
            <h2 class="ev-mpv-title">Mis pedidos</h2>
            <p class="ev-mpv-subtitle">
              Administra tus solicitudes recibidas y da seguimiento a cada pedido con una experiencia clara y amigable.
            </p>
          </div>
        </div>

        <div class="ev-mpv-summary-grid">
          <div class="ev-mpv-summary-card">
            <span class="ev-mpv-summary-label">Pendientes</span>
            <strong id="mpvCountPendientes">0</strong>
          </div>
          <div class="ev-mpv-summary-card">
            <span class="ev-mpv-summary-label">En proceso</span>
            <strong id="mpvCountProceso">0</strong>
          </div>
          <div class="ev-mpv-summary-card">
            <span class="ev-mpv-summary-label">Finalizados</span>
            <strong id="mpvCountFinalizados">0</strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="ev-mpv-panel">
    <div class="ev-mpv-panel-head">
      <div class="ev-mpv-panel-head-row">
        <div>
          <h5 class="ev-mpv-block-title">Panel operativo</h5>
          <p class="ev-mpv-block-subtitle">
            Atiende nuevas solicitudes, avanza el estado de cada pedido y revisa los casos no concretados.
          </p>
        </div>

        <div class="ev-mpv-actions-top">
          <div class="ev-mpv-tab-groups">
            <div class="ev-mpv-tab-group">
              <span class="ev-mpv-tab-group-label">Solicitudes atendidas</span>
              <div class="ev-mpv-tabs">
                <button type="button" class="btn ev-mpv-tab active" data-tab="pendientes">
                  Pendientes
                  <span class="ev-mpv-tab-badge" id="mpvBadgePendientes">0</span>
                </button>
                <button type="button" class="btn ev-mpv-tab" data-tab="proceso">
                  En proceso
                  <span class="ev-mpv-tab-badge" id="mpvBadgeProceso">0</span>
                </button>
                <button type="button" class="btn ev-mpv-tab" data-tab="finalizados">
                  Finalizadas
                  <span class="ev-mpv-tab-badge" id="mpvBadgeFinalizados">0</span>
                </button>
              </div>
            </div>

            <div class="ev-mpv-tab-group">
              <span class="ev-mpv-tab-group-label">Solicitudes no concretadas</span>
              <div class="ev-mpv-tabs">
                <button type="button" class="btn ev-mpv-tab" data-tab="rechazadas">
                  Rechazadas
                  <span class="ev-mpv-tab-badge" id="mpvBadgeRechazadas">0</span>
                </button>
                <button type="button" class="btn ev-mpv-tab" data-tab="sin-respuesta">
                  Sin respuesta
                  <span class="ev-mpv-tab-badge" id="mpvBadgeSinRespuesta">0</span>
                </button>
              </div>
            </div>
          </div>

          <button type="button" id="btnRefrescarMisPedidosVendedor" class="btn ev-mpv-btn-refresh">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
          </button>
        </div>
      </div>
    </div>

    <div class="ev-mpv-panel-body">
      <div id="mpvError" class="ev-mpv-alert ev-mpv-alert-error d-none">
        No se pudieron cargar tus pedidos en este momento.
      </div>

      <div id="mpvTabPendientes" class="ev-mpv-tab-pane">
        <div id="mpvEmptyPendientes" class="ev-mpv-empty d-none">
          No tienes solicitudes pendientes por atender.
        </div>
        <div id="mpvListaPendientes" class="ev-mpv-grid"></div>
      </div>

      <div id="mpvTabProceso" class="ev-mpv-tab-pane d-none">
        <div id="mpvEmptyProceso" class="ev-mpv-empty d-none">
          No tienes pedidos en proceso actualmente.
        </div>
        <div id="mpvListaProceso" class="ev-mpv-grid"></div>
      </div>

      <div id="mpvTabFinalizados" class="ev-mpv-tab-pane d-none">
        <div id="mpvEmptyFinalizados" class="ev-mpv-empty d-none">
          Aún no tienes pedidos finalizados.
        </div>
        <div id="mpvListaFinalizados" class="ev-mpv-grid"></div>
      </div>

      <div id="mpvTabRechazadas" class="ev-mpv-tab-pane d-none">
        <div id="mpvEmptyRechazadas" class="ev-mpv-empty d-none">
          No tienes solicitudes rechazadas.
        </div>
        <div id="mpvListaRechazadas" class="ev-mpv-grid"></div>
      </div>

      <div id="mpvTabSinRespuesta" class="ev-mpv-tab-pane d-none">
        <div id="mpvEmptySinRespuesta" class="ev-mpv-empty d-none">
          No tienes solicitudes sin respuesta.
        </div>
        <div id="mpvListaSinRespuesta" class="ev-mpv-grid"></div>
      </div>
    </div>
  </div>
</div>