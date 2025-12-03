<?php
// views/recibirPedidosView.php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  // Exponer BASE_URL para los fetch del front
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>
<?php require_once __DIR__ . '/estilos/recibirPedidosEstilo.php'; ?>
<div class="container-recibir-pedidos fade-in">
  <div class="card">

    <!-- HEADER -->
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
      <h5 class="mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-bag-check"></i>
        Recibir pedidos
      </h5>

      <span class="badge-estado" id="rpBadgeEstadoTexto">
        <i class="bi bi-toggle-off"></i>
        Desconectado
      </span>
    </div>

    <!-- BODY -->
    <div class="card-body">

      <!-- 1) SLIDER PRINCIPAL -->
      <section class="rp-slider-banner">
        <button
          type="button"
          class="rp-slider-toggle rp-off"
          id="rpToggleEstado"
          aria-pressed="false"
        >
          <span class="rp-slider-arrow">
            <i class="bi bi-arrow-right-short"></i>
          </span>
          <span class="rp-slider-text" id="rpTextoSlider">
            Desliza para conectarte
          </span>
        </button>
      </section>

      <!-- 2) ESTADO DESCONECTADO -->
      <section class="rp-estado-wrapper" id="rpPanelEstado">
        <div class="rp-estado-card">
          <div class="rp-estado-illustration">
            <!-- Ilustración con puntero -->
            <img
              src="<?= BASE_URL ?>/resources/images/deslizar.png"
              alt="Desliza para empezar a recibir pedidos"
              class="rp-estado-img"
            >
          </div>

          <div class="rp-estado-texto">
            <p class="rp-estado-title">
              Conéctate para recibir pedidos en tiempo real
            </p>
            <p class="rp-estado-subtitle">
              Cuando te conectes, solo verás pedidos del condominio en el que vives.
            </p>
          </div>
        </div>
      </section>

      <!-- 3) LISTA DE PEDIDOS (visible solo cuando está conectado) -->
      <section class="rp-lista-wrapper d-none" id="rpListaPedidosWrapper">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-list-ul"></i>
            Pedidos en tiempo real
          </h6>
          <span class="small text-muted" id="rpLastUpdate">
            Actualizado: —
          </span>
        </div>

        <!-- Mensaje cuando está conectado pero sin pedidos -->
        <div class="rp-empty-state d-none" id="rpEmptyState">
          <i class="bi bi-bell-slash rp-empty-icon"></i>
          <p class="mb-0">
            Estás en línea, pero aún no hay pedidos de tus vecinos.
          </p>
        </div>

        <!-- Contenedor donde se pintan los pedidos -->
        <div class="rp-pedidos-list" id="rpPedidosList">
          <!-- Items generados por JS -->
        </div>
      </section>

    </div> <!-- ./card-body -->
  </div> <!-- ./card -->
</div>
