<?php 
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  // Exponer BASE_URL para los fetch del front
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/recibirPedidosEstilo.php'; ?>

<div class="container-fluid py-4 ev-recibir-wrapper fade-in">

  <!-- =========================================
       CARD: RECIBIR PEDIDOS (ESTADO + TOGGLE)
  ========================================== -->
  <div class="card ev-recibir-card">

    <!-- HEADER LIMPIO -->
    <div class="card-header ev-recibir-header d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <div class="ev-recibir-icon-wrapper d-flex align-items-center justify-content-center">
          <i class="bi bi-bag-check-fill"></i>
        </div>
        <div>
          <h5 class="mb-0 ev-recibir-title">Recibir pedidos</h5>
          <small class="text-muted ev-recibir-subtitle">
            Activa tu disponibilidad para que los vecinos puedan enviarte pedidos desde el marketplace.
          </small>
        </div>
      </div>

      <!-- BADGE DE ESTADO -->
      <div id="estadoBadge" class="ev-status-pill ev-status-off d-flex align-items-center gap-2">
        <span id="estadoDot" class="ev-status-dot ev-status-dot-off"></span>
        <span id="estadoBadgeText" class="ev-status-text">Desconectado</span>
      </div>
    </div>

    <!-- CUERPO -->
    <div class="card-body ev-recibir-body">

      <!-- BLOQUE TOGGLE PRINCIPAL -->
      <div class="ev-toggle-row d-flex flex-column flex-md-row align-items-md-center gap-3">
        <div class="ev-switch-wrapper">
          <label class="ev-switch">
            <input type="checkbox" id="toggleRecibirPedidos">
            <span class="ev-slider">
              <span class="ev-slider-label" id="evSliderLabel">
                Desliza para conectarte
              </span>
            </span>
          </label>
        </div>

        <div class="ev-estado-secundario">
          <span class="ev-estado-secundario-label" id="estadoTextoSecundario">
            Actualmente: <strong>Desconectado</strong>
          </span>
          <p class="mb-0 ev-estado-secundario-help">
            Conéctate cuando quieras para empezar a recibir pedidos de tus vecinos.
          </p>
        </div>
      </div>

    </div>
  </div>

  <!-- =========================================
       SECCIÓN: PEDIDOS ENTRANTES (TIEMPO REAL)
       CARD INDEPENDIENTE
  ========================================== -->
  <section class="ev-pedidos-section mt-4">
    <div class="card ev-pedidos-card">

      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0 ev-pedidos-title">Pedidos entrantes</h5>
          <small class="text-muted ev-pedidos-subtitle">
            Aquí verás los pedidos que recibas mientras estés conectado.
          </small>
        </div>

        <span id="evPedidosCounter" class="ev-pedidos-counter badge bg-secondary-subtle text-muted">
          0 pedidos activos
        </span>
      </div>

      <div class="card-body">

        <!-- Mensaje cuando está desconectado -->
        <div id="evPedidosDesconectado" class="ev-pedidos-info-alert ev-pedidos-info-alert-off">
          Conéctate usando el botón superior. Los pedidos nuevos aparecerán aquí automáticamente.
        </div>

        <!-- Mensaje cuando está conectado pero no hay pedidos -->
        <div id="evPedidosEmpty" class="ev-pedidos-info-alert ev-pedidos-info-alert-empty d-none">
          Aún no tienes pedidos. Cuando un vecino haga un pedido, lo verás aquí en tiempo real.
        </div>

        <!-- Contenedor de cards de pedidos -->
        <div id="evPedidosLista" class="ev-pedidos-lista">
          <!-- Aquí el JS irá renderizando las cards de pedidos -->
        </div>

      </div>

    </div>
  </section>
</div>
