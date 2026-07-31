<?php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/billeteraEstilo.php'; ?>

<div class="container-fluid ev-wallet-page ev-wallet-wrapper fade-in">
  <section class="ev-wallet-hero mb-4">
    <div class="ev-wallet-hero-content">
      <div class="ev-wallet-title-wrap">
        <div class="ev-wallet-title-icon" aria-hidden="true">
          <i class="bi bi-wallet2"></i>
        </div>

        <div>
          <div class="ev-wallet-kicker">VENDER · BILLETERA</div>
          <h2 class="ev-wallet-title">Mi billetera</h2>
          <p class="ev-wallet-subtitle">
            Revisa tu saldo, consulta tus movimientos y gestiona el historial de recargas realizadas dentro de Entre Vecinos.
          </p>
        </div>
      </div>

      <div class="ev-wallet-summary-grid" aria-label="Resumen de billetera">
        <article class="ev-wallet-summary-card ev-wallet-summary-card--balance">
          <span>Saldo disponible</span>
          <strong id="ev_wallet_saldo">S/ 0.00</strong>
          <small>Disponible en tu billetera</small>
        </article>

        <article class="ev-wallet-summary-card">
          <span>Movimientos</span>
          <strong id="ev_wallet_total_movimientos">0</strong>
          <small>Cargos y abonos registrados</small>
        </article>

        <article class="ev-wallet-summary-card">
          <span>Recargas</span>
          <strong id="ev_wallet_total_recargas">0</strong>
          <small>Solicitudes enviadas</small>
        </article>
      </div>
    </div>
  </section>

  <section class="ev-wallet-panel">
    <header class="ev-wallet-panel-head">
      <div>
        <h5>Gestión de billetera</h5>
        <p>
          Consulta la trazabilidad de tus recargas y movimientos. Toda operación queda registrada para facilitar su seguimiento.
        </p>
      </div>

      <div class="ev-wallet-head-actions">
        <button
          type="button"
          class="btn ev-wallet-btn-primary"
          id="btnAbrirNuevaRecarga"
          data-bs-toggle="modal"
          data-bs-target="#modalRecargarSaldo">
          <i class="bi bi-plus-circle"></i>
          <span>Recargar saldo</span>
        </button>

        <button
          type="button"
          class="btn ev-wallet-btn-outline"
          data-bs-toggle="modal"
          data-bs-target="#modalSoporteBilletera">
          <i class="bi bi-headset"></i>
          <span>Soporte técnico</span>
        </button>

      </div>
    </header>

    <div class="ev-wallet-panel-body">
      <section class="ev-wallet-activity-block" aria-labelledby="evWalletRecargasTitle">
        <header class="ev-wallet-activity-head">
          <div class="ev-wallet-activity-title-wrap">
            <span class="ev-wallet-activity-icon" aria-hidden="true">
              <i class="bi bi-receipt-cutoff"></i>
            </span>
            <div>
              <h3 id="evWalletRecargasTitle">Mis recargas</h3>
              <p>Consulta el estado de tus solicitudes y las observaciones registradas por Soporte.</p>
            </div>
          </div>
        </header>

        <div id="ev_recargas_empty" class="ev-wallet-empty">
          <i class="bi bi-receipt"></i>
          <div>
            <strong>Aún no registras recargas.</strong>
            <span>Cuando envíes una solicitud, podrás consultar aquí su estado.</span>
          </div>
        </div>

        <div id="ev_recargas_table" class="d-none"></div>
      </section>

      <section class="ev-wallet-activity-block" aria-labelledby="evWalletMovimientosTitle">
        <header class="ev-wallet-activity-head">
          <div class="ev-wallet-activity-title-wrap">
            <span class="ev-wallet-activity-icon" aria-hidden="true">
              <i class="bi bi-clock-history"></i>
            </span>
            <div>
              <h3 id="evWalletMovimientosTitle">Movimientos recientes</h3>
              <p>Revisa de forma clara los cargos, abonos y saldos resultantes de cada operación.</p>
            </div>
          </div>
        </header>

        <div id="ev_wallet_empty_state" class="ev-wallet-empty">
          <i class="bi bi-wallet2"></i>
          <div>
            <strong>Aún no tienes movimientos en tu billetera.</strong>
            <span>Cuando compres o vendas dentro de tu comunidad, aparecerán aquí los cargos y abonos.</span>
          </div>
        </div>

        <div id="ev_wallet_movimientos" class="ev-wallet-movimientos d-none"></div>
      </section>
    </div>
  </section>
</div>

<div class="modal fade ev-modal ev-modal-login" id="modalRecargarSaldo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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

<div class="modal fade ev-modal ev-modal-login" id="modalSoporteBilletera" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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