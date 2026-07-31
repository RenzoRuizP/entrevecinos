<?php
// views/notificacionesResidenciaView.php
require_once __DIR__ . '/../Config/config.php';
?>

<?php include_once __DIR__ . '/estilos/notificacionesResidenciaEstilo.php'; ?>

<div class="ev-notif-wrap fade-in">
  <div class="card ev-notif-card">

    <div class="ev-notif-header">
      <div class="ev-notif-titlebox">
        <div class="ev-notif-ico"><i class="bi bi-bell-fill"></i></div>
        <div>
          <h3 class="ev-notif-title">Notificaciones · Residencia</h3>
          <p class="ev-notif-sub">Aquí verás observaciones o rechazos del administrador para reenviar tu solicitud.</p>
        </div>
      </div>
      <div class="text-muted small pt-1" id="evNotifCounter">—</div>
    </div>

    <div class="ev-notif-toolbar">
      <div class="ev-pill">
        <label>Estado</label>
        <select id="fEstadoNotif">
          <option value="no_leida" selected>No leídas</option>
          <option value="leida">Leídas</option>
          <option value="all">Todas</option>
        </select>
      </div>

      <button class="btn ev-btn ev-btn-light" id="btnRefrescarNotif" type="button">
        <i class="bi bi-arrow-repeat me-1"></i> Refrescar
      </button>
    </div>

    <div class="ev-notif-list" id="listNotif">
      <!-- items -->
    </div>

    <div class="ev-notif-footer">
      <div id="evFooterLeft">Mostrando 0 de 0</div>
      <div class="ev-pager">
        <button class="btn ev-btn ev-btn-light" id="btnPrevNotif" type="button"><i class="bi bi-chevron-left"></i></button>
        <span id="pageInfoNotif">1 / 1</span>
        <button class="btn ev-btn ev-btn-light" id="btnNextNotif" type="button"><i class="bi bi-chevron-right"></i></button>
      </div>
    </div>

  </div>
</div>

<!-- Modal detalle / reenviar -->
<div class="modal fade" id="modalNotifResidencia" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content ev-modal-content">

      <div class="modal-header ev-modal-header">
        <h5 class="modal-title ev-modal-title">
          <i class="bi bi-house-check me-2"></i>Detalle de notificación
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
          <span id="mState" class="ev-badge-state">—</span>
          <span class="text-muted small" id="mFecha">—</span>
        </div>

        <div class="mb-2 fw-bold" id="mTitulo">—</div>
        <div class="text-muted" id="mMensaje">—</div>

        <hr>

        <div class="ev-reenvio-box">
          <div class="fw-bold mb-2">Reenviar solicitud</div>
          <div class="text-muted small mb-2">
            Si la solicitud fue observada o rechazada, adjunta un nuevo comprobante para reenviar.
          </div>

          <input type="file" class="form-control" id="mFile"
            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
          <div class="form-text">PDF/JPG/PNG · Máximo 5MB.</div>

          <div class="ev-modal-actions mt-3">
            <button type="button" class="btn ev-btn ev-btn-guardar" id="btnGuardarReenvio">
              <i class="bi bi-check2-circle me-1"></i> Guardar
            </button>
            <button type="button" class="btn ev-btn ev-btn-light" data-bs-dismiss="modal">Cerrar</button>
          </div>

          <div class="text-muted small mt-2 d-none" id="evReenvioLocked">
            Esta notificación ya fue atendida.
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
