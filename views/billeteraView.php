<?php 
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/billeteraEstilo.php'; ?>

<div class="container-fluid py-4 ev-wallet-wrapper">

  <div class="card ev-wallet-card fade-in">
    <div class="card-body">

      <div class="ev-wallet-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">

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

        <div class="d-flex flex-column align-items-end gap-2 w-100 w-md-auto">

          <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end w-100">
            <button 
              type="button"
              class="btn btn-ev-orange ev-btn-recargar"
              id="btnAbrirNuevaRecarga"
              data-bs-toggle="modal"
              data-bs-target="#modalRecargarSaldo">
              <i class="bi bi-plus-circle"></i> Recargar saldo
            </button>

            <button 
              type="button"
              class="btn btn-ev-outline ev-btn-soporte"
              data-bs-toggle="modal"
              data-bs-target="#modalSoporteBilletera">
              <i class="bi bi-headset"></i> Soporte técnico
            </button>
          </div>

          <div class="ev-wallet-badge mt-1">
            <span class="ev-wallet-badge-label">Saldo disponible</span>
            <span class="ev-wallet-badge-amount" id="ev_wallet_saldo">
              S/ 0.00
            </span>
          </div>

        </div>

      </div>

      <hr class="ev-wallet-divider my-3">

      <div id="ev_wallet_empty_state" class="ev-wallet-empty text-center">
        <div class="ev-wallet-empty-icon mb-2">
          <i class="bi bi-wallet2"></i>
        </div>
        <p class="mb-1">Aún no tienes movimientos en tu billetera.</p>
        <p class="mb-0 text-muted small">
          Cuando compres o vendas dentro del condominio, verás aquí todos los cargos y abonos.
        </p>
      </div>

      <div id="ev_wallet_movimientos" class="ev-wallet-movimientos d-none"></div>

      <hr class="ev-wallet-divider my-3">

      <div class="ev-wallet-recargas">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h3 class="mb-0" style="font-size: 1.05rem; font-weight: 700;">
            <i class="bi bi-receipt-cutoff me-2"></i> Mis recargas
          </h3>
          <button type="button" class="btn btn-sm btn-ev-outline" id="btnRefrescarRecargas">
            <i class="bi bi-arrow-clockwise me-1"></i> Refrescar
          </button>
        </div>

        <div id="ev_recargas_empty" class="text-center text-muted small py-3">
          Aún no registras recargas.
        </div>

        <div id="ev_recargas_table" class="d-none"></div>
      </div>

    </div>
  </div>

</div>

<div class="modal fade ev-modal ev-modal-login" id="modalRecargarSaldo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content ev-modal-content">

      <div class="modal-header ev-login-modal-header">
        <h5 class="modal-title mb-0" id="modalRecargaTitulo">
          <i class="bi bi-plus-circle me-2"></i> Recargar saldo
        </h5>
        <button 
          type="button" 
          class="btn-close btn-close-white" 
          data-bs-dismiss="modal" 
          aria-label="Cerrar">
        </button>
      </div>

      <div class="modal-body ev-login-modal-body">
        <form id="formRecargaSaldo" enctype="multipart/form-data">
          <input type="hidden" id="recarga_codigo" name="recarga_codigo" value="">
          <input type="hidden" id="recarga_modo" name="recarga_modo" value="crear">

          <div id="recarga_alerta_subsanacion" class="alert alert-warning d-none mb-3" role="alert">
            <div class="fw-semibold mb-1">
              <i class="bi bi-exclamation-circle me-1"></i> Recarga observada
            </div>
            <div id="recarga_alerta_subsanacion_texto" class="small mb-0">
              Corrige los datos observados por soporte y vuelve a enviarla.
            </div>
          </div>
          
          <div class="row g-3 align-items-start">
            
            <div class="col-lg-7">
              <div class="row g-3">

                <div class="col-12">
                  <label class="form-label">Comprobante o recibo</label>
                  <input 
                    type="file" 
                    class="form-control" 
                    id="recarga_imagen" 
                    name="recarga_imagen" 
                    accept="image/*">
                  <div class="form-text" id="recarga_imagen_help">
                    Sube una foto clara del voucher o comprobante de la recarga.
                  </div>
                </div>

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

                <div class="col-md-6">
                  <label class="form-label">Tipo de billetera</label>
                  <select class="form-select" id="recarga_tipo" name="recarga_tipo">
                    <option value="">-</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">ID de operación</label>
                  <input
                    type="text"
                    class="form-control"
                    id="recarga_operacion"
                    name="recarga_operacion"
                    placeholder="Ej. AJ5075653">
                  <div class="form-text">
                    Ingresa el código/ID que te da Yape o Plin. Debe ser exactamente el mismo del comprobante.
                  </div>
                </div>

              </div>
            </div>

            <div class="col-lg-5">
              <div class="ev-wallet-qr-card text-center d-none" id="ev_qr_card">
                <img 
                  src="<?= BASE_URL ?>resources/images/plin.jpeg" 
                  alt="QR billetera" 
                  class="ev-wallet-qr-img"
                  id="ev_qr_img">

                <p class="ev-wallet-qr-title mb-1" id="ev_qr_title">Paga tu recarga con Plin</p>
                <p class="ev-wallet-qr-text mb-0" id="ev_qr_text">
                  Escanea este código desde tu app bancaria, ingresa el monto que
                  deseas recargar y luego sube el comprobante en este formulario.
                </p>
              </div>
            </div>

          </div>

        </form>
      </div>

      <div class="modal-footer ev-login-modal-footer justify-content-between">
        <button 
          type="button" 
          class="btn ev-btn-modal-outline"
          data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cerrar
        </button>

        <button type="button" class="btn ev-btn-modal-primary" id="btnEnviarRecarga">
          <i class="bi bi-check-circle-fill me-1"></i> Confirmar recarga
        </button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade ev-modal ev-modal-login" id="modalSoporteBilletera" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content ev-modal-content">

      <div class="modal-header ev-login-modal-header">
        <h5 class="modal-title mb-0">
          <i class="bi bi-headset me-2"></i> Soporte técnico
        </h5>
        <button 
          type="button" 
          class="btn-close btn-close-white" 
          data-bs-dismiss="modal" 
          aria-label="Cerrar">
        </button>
      </div>

      <div class="modal-body ev-login-modal-body text-center">

        <p class="mb-3 text-muted">
          Si tienes dudas sobre tus recargas o movimientos en tu billetera,<br>
          contáctanos:
        </p>

        <div class="p-3 border rounded bg-light mx-auto" style="max-width: 360px;">
          <p class="fw-bold mb-1 text-dark">Soporte técnico</p>
          <p class="mb-2">
            Lunes a Viernes: <strong>8:00 AM – 8:00 PM</strong>
          </p>

          <p class="fs-5 text-success mb-0">
            <i class="bi bi-whatsapp me-1"></i> 956 969 182
          </p>
          <p class="fs-5 text-success mb-0">
            <i class="bi bi-telephone-fill me-1"></i> 956 969 182
          </p>
        </div>

        <p class="text-muted small mb-0 mt-3">
          Nuestro equipo te ayudará a resolver cualquier problema con tu cuenta o billetera.
        </p>

      </div>

      <div class="modal-footer ev-login-modal-footer justify-content-between">
        <button 
          type="button" 
          class="btn ev-btn-modal-outline"
          data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cerrar
        </button>

        <a href="tel:956969182" class="btn ev-btn-modal-primary ev-btn-modal-cta">
          <i class="bi bi-telephone me-1"></i> Llamar ahora
        </a>
      </div>

    </div>
  </div>
</div>