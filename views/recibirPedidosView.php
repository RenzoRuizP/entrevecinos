<?php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/recibirPedidosEstilo.php'; ?>

<div class="container-fluid py-4 ev-recibir-wrapper fade-in">

  <div class="card ev-recibir-card">
    <div class="card-header ev-recibir-header d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div class="d-flex align-items-center gap-2">
        <div class="ev-recibir-icon-wrapper d-flex align-items-center justify-content-center">
          <i class="bi bi-bag-check-fill"></i>
        </div>
        <div>
          <h5 class="mb-0 ev-recibir-title">Mis pedidos</h5>
          <small class="ev-recibir-subtitle">
            Activa tu disponibilidad para recibir solicitudes y gestiona tus pedidos como vendedor.
          </small>
        </div>
      </div>

      <div id="estadoBadge" class="ev-status-pill ev-status-off d-flex align-items-center gap-2">
        <span id="estadoDot" class="ev-status-dot ev-status-dot-off"></span>
        <span id="estadoBadgeText" class="ev-status-text">Desconectado</span>
      </div>
    </div>

    <div class="card-body ev-recibir-body">
      <div class="ev-toggle-row d-flex flex-column flex-md-row align-items-md-center gap-3">
        <div class="ev-switch-wrapper">
          <label class="ev-switch">
            <input type="checkbox" id="toggleRecibirPedidos">
            <span class="ev-switch-track">
              <span class="ev-switch-thumb"></span>
            </span>
            <span class="ev-switch-text" id="evSliderLabel">
              Desliza para conectarte
            </span>
          </label>
        </div>

        <div class="ev-estado-secundario">
          <span class="ev-estado-secundario-label" id="estadoTextoSecundario">
            Actualmente: <strong>Desconectado</strong>
          </span>
          <p class="mb-0 ev-estado-secundario-help">
            Cuando estés conectado, los vecinos podrán enviarte solicitudes desde el marketplace.
          </p>
        </div>
      </div>
    </div>
  </div>

  <section class="ev-pedidos-section mt-4">
    <div class="card ev-pedidos-card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
          <div class="ev-pedidos-icon d-flex align-items-center justify-content-center">
            <i class="bi bi-bag-plus-fill"></i>
          </div>
          <div>
            <h5 class="mb-0 ev-pedidos-title">Gestión de pedidos</h5>
            <small class="ev-pedidos-subtitle">
              Revisa solicitudes pendientes, pedidos en proceso y pedidos finalizados.
            </small>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span id="evPedidosCounter" class="ev-pedidos-counter">0 pedidos</span>
          <button type="button" id="btnRefrescarPedidos" class="btn btn-sm ev-btn-detalle">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
          </button>
        </div>
      </div>

      <div class="card-body">

        <div id="evPedidosDesconectado" class="ev-pedidos-info-alert ev-pedidos-info-alert-off">
          Conéctate usando el interruptor superior. Mientras estés desconectado, no podrás recibir nuevas solicitudes.
        </div>

        <div id="evPedidosError" class="ev-pedidos-info-alert ev-pedidos-info-alert-off d-none">
          No se pudieron cargar tus pedidos en este momento.
        </div>

        <div id="evPedidosBloque" class="d-none">

          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="mb-0 ev-pedidos-title">Pendientes</h6>
              <span id="evPendientesCounter" class="ev-pedidos-counter">0</span>
            </div>
            <div id="evPendientesEmpty" class="ev-pedidos-info-alert ev-pedidos-info-alert-empty d-none">
              No tienes solicitudes pendientes por atender.
            </div>
            <div id="evPendientesLista" class="ev-pedidos-lista"></div>
          </div>

          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="mb-0 ev-pedidos-title">En proceso</h6>
              <span id="evProcesoCounter" class="ev-pedidos-counter">0</span>
            </div>
            <div id="evProcesoEmpty" class="ev-pedidos-info-alert ev-pedidos-info-alert-empty d-none">
              No tienes pedidos en proceso en este momento.
            </div>
            <div id="evProcesoLista" class="ev-pedidos-lista"></div>
          </div>

          <div>
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="mb-0 ev-pedidos-title">Finalizados</h6>
              <span id="evFinalizadosCounter" class="ev-pedidos-counter">0</span>
            </div>
            <div id="evFinalizadosEmpty" class="ev-pedidos-info-alert ev-pedidos-info-alert-empty d-none">
              Aún no tienes pedidos finalizados.
            </div>
            <div id="evFinalizadosLista" class="ev-pedidos-lista"></div>
          </div>

        </div>
      </div>
    </div>
  </section>
</div>