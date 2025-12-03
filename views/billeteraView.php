<?php 
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  // Exponer BASE_URL para los fetch del front
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/billeteraEstilo.php'; ?>

<!-- IMPORTANTE: igual que Marketplace, aquí NO usamos .content-wrapper.
     El main ya es .content-wrapper en MenuPrincipalView.php -->
<div class="container-fluid py-4 ev-wallet-wrapper">

  <div class="card ev-wallet-card fade-in">
    <div class="card-body">

      <!-- Encabezado: título + saldo -->
      <div class="ev-wallet-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div class="flex-grow-1">
          <h2 class="ev-wallet-title mb-1">Mi billetera</h2>
          <p class="ev-wallet-subtitle mb-0">
            Revisa tu saldo disponible y los movimientos de tus compras y ventas dentro del condominio.
          </p>
        </div>

        <div class="ev-wallet-badge mt-2 mt-md-0">
          <span class="ev-wallet-badge-label">Saldo disponible</span>
          <span class="ev-wallet-badge-amount" id="ev_wallet_saldo">
            S/ 0.00
          </span>
        </div>
      </div>

      <hr class="my-3">

      <!-- Estado vacío inicial -->
      <div id="ev_wallet_empty_state" class="ev-wallet-empty text-center">
        <div class="ev-wallet-empty-icon mb-2">
          <i class="bi bi-wallet2"></i>
        </div>
        <p class="mb-1">Aún no tienes movimientos en tu billetera.</p>
        <p class="mb-0 text-muted small">
          Cuando compres o vendas dentro del condominio, verás aquí todos los cargos y abonos.
        </p>
      </div>

      <!-- Contenedor para tabla/listado de movimientos -->
      <div id="ev_wallet_movimientos" class="ev-wallet-movimientos d-none">
        <!-- Luego el JS poblará este bloque con los movimientos -->
      </div>

    </div>
  </div>

</div><!-- /.container-fluid -->
