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

      <!-- Encabezado: título + acciones + saldo -->
      <div class="ev-wallet-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">

        <!-- Título + subtítulo -->
        <div class="flex-grow-1 d-flex flex-column">
          <h2 class="ev-wallet-title mb-1 d-flex align-items-center gap-2">
            <span class="ev-wallet-title-icon">
              <i class="bi bi-wallet2"></i>
            </span>
            <span>Mi billetera</span>
          </h2>
          <p class="ev-wallet-subtitle mb-0">
            Revisa tu saldo disponible y los movimientos de tus compras y ventas dentro del condominio.
          </p>
        </div>

        <!-- Columna derecha: barra de acciones + saldo -->
        <div class="d-flex flex-column align-items-end gap-2 w-100 w-md-auto">

          <!-- Barra de acciones -->
          <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end w-100">
            <!-- Botón Recargar saldo -->
            <button 
              type="button"
              class="btn btn-ev-orange ev-btn-recargar"
              data-bs-toggle="modal"
              data-bs-target="#modalRecargarSaldo">
              <i class="bi bi-plus-circle"></i> Recargar saldo
            </button>

            <!-- Botón Soporte técnico -->
            <button 
              type="button"
              class="btn btn-ev-outline ev-btn-soporte"
              data-bs-toggle="modal"
              data-bs-target="#modalSoporteBilletera">
              <i class="bi bi-headset"></i> Soporte técnico
            </button>
          </div>

          <!-- Badge saldo disponible -->
          <div class="ev-wallet-badge mt-1">
            <span class="ev-wallet-badge-label">Saldo disponible</span>
            <span class="ev-wallet-badge-amount" id="ev_wallet_saldo">
              S/ 0.00
            </span>
          </div>

        </div>

      </div>

      <hr class="ev-wallet-divider my-3">

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


<!-- ===========================================================
     MODAL: RECARGAR SALDO
=========================================================== -->
<div class="modal fade ev-modal" id="modalRecargarSaldo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content ev-modal-content">

      <!-- Header CON botón X a la derecha -->
      <div class="ev-modal-header">
        <h5 class="ev-modal-title mb-0">
          <i class="bi bi-plus-circle"></i>
          <span>Recargar saldo</span>
        </h5>
        <button 
          type="button" 
          class="btn-close btn-close-white ev-modal-close-icon" 
          data-bs-dismiss="modal" 
          aria-label="Cerrar">
        </button>
      </div>

      <!-- Body -->
      <div class="ev-modal-body">
        <form id="formRecargaSaldo" enctype="multipart/form-data">
          
          <div class="row g-3 align-items-start">
            
            <!-- Columna izquierda: formulario -->
            <div class="col-lg-7">
              <div class="row g-3">

                <!-- Comprobante -->
                <div class="col-12">
                  <label class="form-label">Comprobante o recibo</label>
                  <input 
                    type="file" 
                    class="form-control" 
                    id="recarga_imagen" 
                    name="recarga_imagen" 
                    accept="image/*">
                  <div class="form-text">
                    Sube una foto clara del voucher o comprobante de la recarga.
                  </div>
                </div>

                <!-- Monto -->
                <div class="col-md-6">
                  <label class="form-label">Monto a recargar</label>
                  <input 
                    type="number" 
                    min="1" 
                    step="0.1"
                    class="form-control" 
                    id="recarga_monto" 
                    name="recarga_monto" 
                    placeholder="Ej. 10.00">
                </div>

                <!-- Medio de pago -->
                <div class="col-md-6">
                  <label class="form-label">Tipo de billetera</label>
                  <select class="form-select" id="recarga_tipo" name="recarga_tipo">
                    <option value="">-</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                  </select>
                </div>

              </div>
            </div>

            <!-- Columna derecha: QR dinámico -->
            <div class="col-lg-5">
              <div class="ev-wallet-qr-card text-center d-none" id="ev_qr_card">
                <img 
                  src="<?= BASE_URL ?>resources/img/qr_plin_marco.png" 
                  alt="QR billetera" 
                  class="ev-wallet-qr-img"
                  id="ev_qr_img">

                <p class="ev-wallet-qr-title mb-1" id="ev_qr_title">
                  Paga tu recarga con Plin
                </p>
                <p class="ev-wallet-qr-text mb-0" id="ev_qr_text">
                  Escanea este código desde tu app bancaria, ingresa el monto que
                  deseas recargar y luego sube el comprobante en este formulario.
                </p>
              </div>
            </div>

          </div><!-- /.row -->

          <!-- DNI oculto -->
          <input 
            type="hidden" 
            id="recarga_dni" 
            name="recarga_dni" 
            value="<?= $_SESSION['dni'] ?? '' ?>">

        </form>
      </div>

      <!-- Footer -->
      <div class="ev-modal-footer">
        <button 
          type="button" 
          class="btn btn-ev-outline ev-btn-cerrar" 
          data-bs-dismiss="modal">
          <i class="bi bi-x"></i>
          <span>Cerrar</span>
        </button>
        <button type="button" class="btn btn-ev-orange" id="btnEnviarRecarga">
          <i class="bi bi-check-circle-fill"></i>
          <span>Confirmar recarga</span>
        </button>
      </div>

    </div>
  </div>
</div>


<!-- ===========================================================
     MODAL: SOPORTE TÉCNICO
=========================================================== -->
<div class="modal fade ev-modal" id="modalSoporteBilletera" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content ev-modal-content">

      <!-- Header CON botón X a la derecha -->
      <div class="ev-modal-header">
        <h5 class="ev-modal-title mb-0">
          <i class="bi bi-headset"></i>
          <span>Soporte técnico</span>
        </h5>
        <button 
          type="button" 
          class="btn-close btn-close-white ev-modal-close-icon" 
          data-bs-dismiss="modal" 
          aria-label="Cerrar">
        </button>
      </div>

      <!-- Body -->
      <div class="ev-modal-body text-center">

        <p class="mb-3">
          Si tienes dudas sobre tus recargas o movimientos en tu billetera,<br>
          contáctanos:
        </p>

        <div class="ev-support-card mx-auto mb-3">
          <p class="mb-1 ev-support-title">Soporte técnico</p>
          <p class="mb-3 ev-support-subtitle">
            Lunes a Viernes: <strong>8:00 AM – 8:00 PM</strong>
          </p>

          <p class="mb-1">
            <i class="bi bi-whatsapp ev-support-icon"></i>
            <span class="ev-support-phone">956 969 182</span>
          </p>
          <p class="mb-0">
            <i class="bi bi-telephone-fill ev-support-icon"></i>
            <span class="ev-support-phone">956 969 182</span>
          </p>
        </div>

        <p class="text-muted small mb-0">
          Nuestro equipo te ayudará a resolver cualquier problema con tu cuenta o billetera.
        </p>

      </div>

      <!-- Footer -->
      <div class="ev-modal-footer">
        <button 
          type="button" 
          class="btn btn-ev-outline ev-btn-cerrar" 
          data-bs-dismiss="modal">
          <i class="bi bi-x"></i>
          <span>Cerrar</span>
        </button>
        <a href="tel:956969182" class="btn btn-ev-orange ev-btn-cta-soporte">
          <i class="bi bi-telephone-fill"></i>
          <span>Llamar ahora</span>
        </a>
      </div>

    </div>
  </div>
</div>
