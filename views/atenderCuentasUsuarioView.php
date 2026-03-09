<?php
// views/atenderCuentasUsuarioView.php
if (!defined('BASE_URL')) { define('BASE_URL', '/'); }
$baseUrl = rtrim(BASE_URL, '/');
?>

<?php include_once __DIR__ . '/estilos/atenderCuentasUsuarioEstilo.php'; ?>

<div class="container-fluid py-4 ev-au-page">

  <div class="card ev-card ev-hero mb-4">
    <div class="ev-hero-body">
      <div class="ev-hero-top">
        <div class="d-flex align-items-center gap-3">
          <span class="ev-icon-btn" aria-hidden="true">
            <i class="bi bi-person-check"></i>
          </span>

          <div>
            <h2 class="ev-au-title mb-1">Atender cuentas de usuario</h2>
            <div class="ev-au-subtitle">
              Revisa cuentas en estado “En revisión” y solicitudes de cambio de residencia.
            </div>
          </div>
        </div>

        <div class="ev-hero-right">
          <a class="ev-icon-btn" href="<?= $baseUrl ?>/notificaciones-residencia" aria-label="Notificaciones">
            <i class="bi bi-bell"></i>
          </a>
        </div>
      </div>

      <div class="ev-hero-bottom">
        <div class="ev-summary-pill">
          <span class="ev-summary-label">Total:</span>
          <span class="ev-summary-count" id="lblTotal">0</span>
        </div>

        <div class="ev-quick-actions">
          <button type="button" class="btn ev-btn-light ev-chip js-ev-chip ev-chip-active" data-estado="revision" aria-pressed="true">
            <i class="bi bi-hourglass-split"></i> En revisión
          </button>
          <button type="button" class="btn ev-btn-light ev-chip js-ev-chip" data-estado="observado" aria-pressed="false">
            <i class="bi bi-exclamation-triangle"></i> Observados
          </button>
          <button type="button" class="btn ev-btn-light ev-chip js-ev-chip" data-estado="habilitado" aria-pressed="false">
            <i class="bi bi-check2-circle"></i> Habilitados
          </button>
          <button type="button" class="btn ev-btn-light ev-chip js-ev-chip" data-estado="inactivo" aria-pressed="false">
            <i class="bi bi-slash-circle"></i> Inactivos
          </button>
          <button type="button" class="btn ev-btn-light ev-chip js-ev-chip" data-estado="todos" aria-pressed="false">
            <i class="bi bi-list-ul"></i> Todos
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="card ev-card mb-4">
    <div class="ev-card-header">
      <div class="ev-card-header-row">
        <h6 class="ev-card-title mb-0">Filtros</h6>
        <small class="ev-table-meta">Aplica filtros y búsqueda para ubicar usuarios rápidamente</small>
      </div>
    </div>

    <div class="ev-card-body">
      <div class="row g-3 align-items-end">

        <div class="col-12 col-lg-3">
          <label class="form-label fw-semibold">Modo</label>
          <select class="form-select ev-input" id="filtroModo">
            <option value="usuarios" selected>Usuarios</option>
            <option value="residencias">Cambios de residencia</option>
          </select>
        </div>

        <div class="col-12 col-lg-3">
          <label class="form-label fw-semibold">Estado</label>
          <select class="form-select ev-input" id="filtroEstado">
            <option value="revision" selected>En revisión</option>
            <option value="observado">Observados</option>
            <option value="habilitado">Habilitados</option>
            <option value="inactivo">Inactivos</option>
            <option value="todos">Todos</option>
          </select>
        </div>

        <div class="col-12 col-lg-3">
          <label class="form-label fw-semibold">Conjunto</label>
          <select class="form-select ev-input" id="filtroConjunto">
            <option value="todos" selected>Todos</option>
            <option value="condominio">Condominio</option>
            <option value="urbanizacion">Urbanización</option>
          </select>
        </div>

        <div class="col-12 col-lg-3">
          <label class="form-label fw-semibold">Condominio / Urbanización</label>
          <select class="form-select ev-input" id="filtroCondominio">
            <option value="" selected>Selecciona...</option>
          </select>
          <small class="text-muted">Se habilita cuando eliges “Condominio” o “Urbanización”.</small>
        </div>

        <div class="col-12 col-lg-9">
          <label class="form-label fw-semibold">Buscar</label>
          <div class="position-relative">
            <span class="ev-input-icon"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control ev-input ps-5" id="filtroBuscar" placeholder="Nombre, email, documento">
          </div>
        </div>

        <div class="col-6 col-lg-1">
          <button type="button" class="btn ev-btn-light w-100" id="btnBuscarLimpiar">Limpiar</button>
        </div>

        <div class="col-6 col-lg-2">
          <button type="button" class="btn ev-btn-orange w-100" id="btnBuscarAplicar">Aplicar filtros</button>
        </div>

      </div>
    </div>
  </div>

  <div class="card ev-card">
    <div class="ev-card-header">
      <div class="ev-card-header-row">
        <h6 class="ev-card-title mb-0">Usuarios</h6>
        <small class="ev-table-meta">Listado según filtros actuales</small>
      </div>
    </div>

    <div class="table-responsive ev-table-wrap">
      <table class="table ev-table align-middle mb-0">
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Contacto</th>
            <th>Residencia</th>
            <th class="text-center">Estado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>

        <tbody id="evUsuariosBody">
          <tr>
            <td colspan="5" class="text-center py-4 ev-empty">
              <div class="ev-empty-wrap">
                <div class="ev-empty-ico"><i class="bi bi-cloud-arrow-down"></i></div>
                <div class="ev-empty-text">Cargando...</div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="ev-card-footer">
      <div class="ev-footer-left">
        Página <span class="ev-page-pill" id="lblPagNum">1</span>
      </div>

      <div class="ev-footer-right">
        <button type="button" class="btn btn-sm ev-btn-light" id="btnPagPrev">Anterior</button>
        <button type="button" class="btn btn-sm ev-btn-light" id="btnPagNext">Siguiente</button>
      </div>
    </div>
  </div>

</div>

<div class="modal fade" id="modalRevisarCuenta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content ev-modal">
      <div class="modal-header ev-modal-header">
        <div class="d-flex flex-column">
          <h5 class="modal-title mb-1">Revisar registro</h5>
          <small class="text-white-50" id="mModalTipoRevision">Cuenta de usuario</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body ev-modal-body">
        <div class="row g-3">
          <div class="col-12 col-lg-5">
            <div class="ev-kv">
              <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                  <div class="fw-bold" id="mNombre">—</div>
                  <div class="text-muted small" id="mEmail">—</div>
                </div>
                <div id="mBadgeEstado"></div>
              </div>

              <div class="mt-2 ev-kv-item"><span>Documento</span><strong id="mDoc">—</strong></div>
              <div class="ev-kv-item"><span>Teléfono</span><strong id="mTel">—</strong></div>
              <div class="ev-kv-item"><span>Conjunto</span><strong id="mTipoConjunto">—</strong></div>
              <div class="ev-kv-item"><span>Dirección</span><strong id="mDireccion">—</strong></div>
            </div>

            <div class="ev-hint mt-3" id="mHintRevision">
              Verifica que el comprobante coincida con la residencia.
            </div>

            <div class="mt-3">
              <label class="form-label fw-semibold mb-2">Observación</label>
              <textarea id="mObsTexto" class="form-control ev-input" rows="3"
                placeholder="Indica qué debe corregir el usuario (ej: el recibo no coincide con la dirección)."></textarea>

              <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">Se enviará como observación al usuario.</small>
                <button type="button" class="btn btn-outline-warning" id="btnModalObservar">
                  Observar
                </button>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-7">
            <div class="ev-proof">
              <div class="ev-proof-title d-flex align-items-center justify-content-between">
                <span>Comprobante</span>
                <a href="#" target="_blank" id="mLinkComprobante" class="small" style="display:none;">Abrir</a>
              </div>

              <div class="ev-proof-box">
                <img id="mImgComprobante" src="" alt="Comprobante" class="img-fluid rounded-3 border" style="display:none; max-height: 420px;">
                <iframe id="mPdfComprobante" class="ev-doc-frame" style="display:none;" src=""></iframe>
                <div id="mNoComprobante" class="ev-proof-empty">No hay comprobante adjunto.</div>
              </div>

              <div class="ev-proof-hint">
                Si el documento es PDF se mostrará en visor. Si es imagen, se previsualiza.
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer ev-modal-footer">
        <button type="button" class="btn btn-outline-danger" id="btnModalInactivar">Desactivar</button>
        <button type="button" class="btn ev-btn-orange" id="btnModalAprobar">Activar</button>
      </div>
    </div>
  </div>
</div>

<script>
  window.BASE_URL = window.BASE_URL || "<?= $baseUrl ?>";
</script>