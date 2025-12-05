<?php 
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  // Exponer BASE_URL para los fetch del front
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/recibirPedidosEstilo.php'; ?>

<div class="container-fluid py-4 ev-recibir-wrapper fade-in">
  <div class="card ev-recibir-card">

    <!-- HEADER LIMPIO (sin franja verde gruesa) -->
    <div class="card-header ev-recibir-header d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <div class="ev-recibir-icon-wrapper d-flex align-items-center justify-content-center">
          <i class="bi bi-bag-check-fill"></i>
        </div>
        <div>
          <h5 class="mb-0 ev-recibir-title">Recibir pedidos</h5>
          <small class="text-muted ev-recibir-subtitle">
            Controla si deseas estar disponible para recibir pedidos.
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
      <div class="ev-toggle-row d-flex flex-column flex-md-row align-items-md-center gap-3 mb-4">
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
            Cuando estés conectado, tus vecinos podrán enviarte pedidos desde el marketplace
            de tu condominio.
          </p>
        </div>
      </div>

      <!-- BLOQUE EXPLICATIVO / INFO CARD -->
      <div class="ev-recibir-info-card d-flex align-items-center gap-3">
        <div class="ev-recibir-info-illustration d-flex align-items-center justify-content-center">
          <!-- Usa aquí la ilustración que ya tenías.
               Si la ruta es otra, solo cambia el src. -->
          <img src="<?= BASE_URL ?>/resources/images/deslizar.png"
               alt="Conéctate para recibir pedidos" 
               class="img-fluid">
        </div>
        <div>
          <h6 class="mb-1 ev-recibir-info-title">
            Conéctate para recibir pedidos en tiempo real
          </h6>
          <p class="mb-0 ev-recibir-info-text">
            Al activarte, solo recibirás pedidos de los vecinos del condominio en el que vives.
            Puedes desconectarte cuando quieras.
          </p>
        </div>
      </div>

    </div>
  </div>
</div>
