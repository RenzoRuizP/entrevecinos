<?php
// views/AtenderRecargasView.php
require_once __DIR__ . '/../Config/config.php';
?>

<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/atenderRecargasEstilo.php'; ?>

<div class="container-fluid px-3 px-md-4 py-3 ev-recargas-page">

  <!-- HERO CARD -->
  <div class="ev-card ev-hero mb-3">
    <div class="ev-hero-body">
      <div class="ev-hero-top">
        <div class="ev-hero-left">
          <h1 class="ev-recargas-title mb-1">Atender Recargas</h1>
          <div class="ev-recargas-subtitle">Valida comprobantes y acredita saldo a usuarios.</div>
        </div>

        <div class="ev-hero-right">
          <button class="ev-icon-btn" type="button" title="Notificaciones" aria-label="Notificaciones">
            <i class="bi bi-bell"></i>
          </button>

          <div class="dropdown">
            <button class="btn ev-btn-orange dropdown-toggle" data-bs-toggle="dropdown" type="button">
              Acciones
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <button class="dropdown-item" type="button" id="btnRefrescar">
                  <i class="bi bi-arrow-clockwise me-2"></i>Refrescar
                </button>
              </li>
              <li>
                <button class="dropdown-item" type="button" id="btnExportar">
                  <i class="bi bi-download me-2"></i>Exportar CSV
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="ev-hero-bottom">
        <div class="ev-summary-pill">
          <span class="ev-summary-label">Recargas pendientes:</span>
          <span class="ev-summary-count" id="lblPendientes">0</span>
        </div>

        <div class="ev-quick-actions">
          <button type="button" class="btn ev-btn-light btn-sm" id="btnVerPendientes">
            <i class="bi bi-hourglass-split me-1"></i> Pendientes
          </button>
          <button type="button" class="btn ev-btn-light btn-sm" id="btnVerObservadas">
            <i class="bi bi-exclamation-circle me-1"></i> Observadas
          </button>
          <button type="button" class="btn ev-btn-light btn-sm" id="btnVerAprobadas">
            <i class="bi bi-check-circle me-1"></i> Aprobadas
          </button>
          <button type="button" class="btn ev-btn-light btn-sm" id="btnVerRechazadas">
            <i class="bi bi-x-circle me-1"></i> Rechazadas
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="ev-card ev-filters mb-3">
    <div class="ev-card-header">
      <h2 class="ev-card-title">Filtros</h2>
    </div>

    <div class="ev-card-body">
      <form id="formFiltros" class="row g-3 align-items-end">
        <div class="col-12 col-lg-3">
          <label class="form-label">Estado</label>
          <select class="form-select ev-input" id="fEstado" name="estado">
            <option value="pendiente" selected>Pendiente</option>
            <option value="observada">Observada</option>
            <option value="aprobada">Aprobada</option>
            <option value="rechazada">Rechazada</option>
          </select>
        </div>

        <div class="col-12 col-lg-3">
          <label class="form-label">Rango</label>
          <select class="form-select ev-input" id="fRango" name="rango">
            <option value="hoy">Hoy</option>
            <option value="7" selected>Últimos 7 días</option>
            <option value="15">Últimos 15 días</option>
            <option value="30">Últimos 30 días</option>
          </select>
        </div>

        <div class="col-12 col-lg-4">
          <label class="form-label">Buscar</label>
          <div class="position-relative">
            <i class="bi bi-search ev-input-icon"></i>
            <input
              type="text"
              class="form-control ev-input ps-5"
              id="fTexto"
              name="q"
              placeholder="Buscar nombre, N° documento, ID operación..."
              autocomplete="off"
            />
          </div>
        </div>

        <div class="col-12 col-lg-2 d-grid">
          <button class="btn ev-btn-orange" type="submit">
            <i class="bi bi-search me-1"></i> Buscar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="ev-card">
    <div class="ev-card-header ev-card-header-row">
      <h2 class="ev-card-title mb-0">Solicitudes</h2>
      <div class="ev-table-meta" id="lblMeta">Mostrando 0 registros</div>
    </div>

    <div class="ev-table-wrap">
      <div class="table-responsive">
        <table class="table ev-table mb-0">
          <thead>
            <tr>
              <th class="ev-col-fecha">Fecha</th>
              <th class="ev-col-usuario">Usuario</th>
              <th class="text-end ev-col-monto">Monto</th>
              <th class="text-center ev-col-metodo">Método</th>
              <th class="ev-col-operacion">ID Operación</th>
              <th class="ev-col-estado">Estado</th>
              <th class="text-end ev-col-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyRecargas">
            <tr>
              <td colspan="7" class="text-center py-4 ev-empty">
                <div class="ev-empty-wrap">
                  <i class="bi bi-inbox ev-empty-ico"></i>
                  <div class="ev-empty-text">
                    No hay solicitudes de recarga para los filtros seleccionados.
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="ev-card-footer">
      <div class="ev-footer-left" id="lblFooterLeft">Mostrando 0 de 0</div>
      <div class="ev-footer-right">
        <button class="btn ev-btn-light btn-sm" id="btnPrev" type="button" disabled>Anterior</button>
        <span class="ev-page-pill" id="lblPagina">1</span>
        <button class="btn ev-btn-light btn-sm" id="btnNext" type="button" disabled>Siguiente</button>
      </div>
    </div>
  </div>

</div>

<!-- Modal: Revisar recarga -->
<div class="modal fade" id="modalRecarga" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content ev-modal">
      <div class="modal-header ev-modal-header">
        <h5 class="modal-title">
          <i class="bi bi-receipt-cutoff me-2"></i> Revisar solicitud de recarga
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body ev-modal-body">
        <div class="row g-4">
          <div class="col-12 col-lg-6">
          <div id="mReenviadaWrap" class="ev-alert-reenviada d-none">
            <div class="ev-alert-reenviada-icon">
              <i class="bi bi-arrow-repeat"></i>
            </div>
            <div class="ev-alert-reenviada-body">
              <div class="ev-alert-reenviada-title">Solicitud reenviada</div>
              <div class="ev-alert-reenviada-text">
                Esta solicitud fue reenviada por el usuario luego de una observación anterior.
              </div>
            </div>
          </div>

          <div class="ev-kv">
            <div class="ev-kv">
              <div class="ev-kv-item"><span>Usuario:</span> <strong id="mUsuario">—</strong></div>
              <div class="ev-kv-item"><span>DNI:</span> <strong id="mDni">—</strong></div>
              <div class="ev-kv-item"><span>Monto:</span> <strong id="mMonto">—</strong></div>
              <div class="ev-kv-item"><span>Método:</span> <strong id="mMetodo">—</strong></div>
              <div class="ev-kv-item"><span>ID operación:</span> <strong id="mOperacion">—</strong></div>
              <div class="ev-kv-item"><span>Estado actual:</span> <span id="mEstadoBadge" class="ev-badge ev-badge-pendiente">pendiente</span></div>
            </div>

            <div class="mt-3">
              <label class="form-label">Comentario (obligatorio para Observada / Rechazada)</label>
              <textarea class="form-control ev-input" id="mComentario" rows="4"
                        placeholder="Ej. La imagen está borrosa / El ID operación no coincide / Falta monto..."></textarea>
              <div class="form-text">Este comentario se mostrará al usuario.</div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="ev-proof">
              <div class="ev-proof-title"><i class="bi bi-image me-2"></i> Comprobante</div>
              <div class="ev-proof-box">
                <img id="mImagen" src="" alt="Comprobante" class="img-fluid d-none">
                <div id="mNoImagen" class="ev-proof-empty">No hay imagen disponible.</div>
              </div>
              <div class="ev-proof-hint">
                Verifica el pago en tu app (Yape/Plin) y confirma el ID de operación.
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer ev-modal-footer">
        <button type="button" class="btn ev-btn-outline" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cerrar
        </button>

        <div class="ms-auto d-flex gap-2">
          <button type="button" class="btn ev-btn-soft" id="btnObservar">
            <i class="bi bi-exclamation-circle me-1"></i> Observar
          </button>
          <button type="button" class="btn ev-btn-danger" id="btnRechazar">
            <i class="bi bi-x-circle me-1"></i> Rechazar
          </button>
          <button type="button" class="btn ev-btn-success" id="btnAprobar">
            <i class="bi bi-check-circle me-1"></i> Aprobar
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
