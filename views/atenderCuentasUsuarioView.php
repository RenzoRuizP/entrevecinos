<?php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/atenderCuentasUsuarioEstilo.php'; ?>

<div class="container-fluid px-3 px-md-4 py-3 ev-au-page">

  <!-- HERO CARD -->
  <div class="ev-card ev-hero mb-3">
    <div class="ev-hero-body">
      <div class="ev-hero-top">
        <div class="ev-hero-left">
          <h1 class="ev-au-title mb-1">Atender cuentas de usuario</h1>
          <div class="ev-au-subtitle">Revisa cuentas en estado “En revisión” y solicitudes de cambio de residencia.</div>
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
                <button class="dropdown-item" type="button" id="btnLimpiarFiltros">
                  <i class="bi bi-eraser me-2"></i>Limpiar filtros
                </button>
              </li>
            </ul>
          </div>

        </div>
      </div>

      <div class="ev-hero-bottom">
        <div class="ev-summary-pill">
          <span class="ev-summary-label">Total:</span>
          <span class="ev-summary-count" id="metaInfo">—</span>
        </div>

        <div class="ev-quick-actions">
          <button type="button" class="btn ev-btn-light btn-sm" id="btnEstadoRevision">
            <i class="bi bi-hourglass-split me-1"></i> En revisión
          </button>
          <button type="button" class="btn ev-btn-light btn-sm" id="btnEstadoHabilitado">
            <i class="bi bi-check-circle me-1"></i> Habilitados
          </button>
          <button type="button" class="btn ev-btn-light btn-sm" id="btnEstadoInactivo">
            <i class="bi bi-slash-circle me-1"></i> Inactivos
          </button>
          <button type="button" class="btn ev-btn-light btn-sm" id="btnEstadoTodos">
            <i class="bi bi-list-ul me-1"></i> Todos
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
      <form id="formFiltrosAU" class="row g-3 align-items-end" onsubmit="return false;">
        <div class="col-12 col-lg-3">
          <label class="form-label">Estado</label>
          <select class="form-select ev-input" id="fEstado">
            <option value="1" selected>En revisión</option>
            <option value="2">Habilitado</option>
            <option value="0">Inactivo</option>
            <option value="all">Todos</option>
          </select>
        </div>

        <div class="col-12 col-lg-3">
          <label class="form-label">Conjunto</label>
          <select class="form-select ev-input" id="fTipo">
            <option value="" selected>Todos</option>
            <option value="condominio">Condominio</option>
            <option value="urbanizacion">Urbanización</option>
          </select>
        </div>

        <div class="col-12 col-lg-4">
          <label class="form-label">Condominio / Urbanización</label>
          <select class="form-select ev-input" id="fCodigo" disabled>
            <option value="">Selecciona…</option>
          </select>
          <div class="form-text">Se habilita cuando eliges “Condominio” o “Urbanización”.</div>
        </div>

        <div class="col-12 col-lg-2">
          <label class="form-label">Buscar</label>
          <div class="position-relative">
            <i class="bi bi-search ev-input-icon"></i>
            <input type="text" class="form-control ev-input ps-5" id="fQ" placeholder="Nombre, email, documento" autocomplete="off">
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="ev-card">
    <div class="ev-card-header ev-card-header-row">
      <h2 class="ev-card-title mb-0">Usuarios</h2>
      <div class="ev-table-meta" id="pageInfo">1</div>
    </div>

    <div class="ev-table-wrap">
      <div class="table-responsive">
        <table class="table ev-table mb-0">
          <thead>
            <tr>
              <th>Usuario</th>
              <th>Contacto</th>
              <th>Residencia</th>
              <th>Estado</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyUsuarios">
            <tr>
              <td colspan="5" class="text-center py-4 ev-empty">
                <div class="ev-empty-wrap">
                  <i class="bi bi-inbox ev-empty-ico"></i>
                  <div class="ev-empty-text">Cargando…</div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="ev-card-footer">
      <div class="ev-footer-left" id="lblFooterLeft">—</div>
      <div class="ev-footer-right">
        <button class="btn ev-btn-light btn-sm" id="btnPrev" type="button" disabled>Anterior</button>
        <span class="ev-page-pill" id="lblPagina">1</span>
        <button class="btn ev-btn-light btn-sm" id="btnNext" type="button" disabled>Siguiente</button>
      </div>
    </div>
  </div>

</div>

<!-- MODAL: Detalle de Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content ev-modal">
      <div class="modal-header ev-modal-header">
        <h5 class="modal-title">
          <i class="bi bi-person-vcard me-2"></i> Detalle de usuario
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body ev-modal-body">
        <div class="row g-4">

          <div class="col-12 col-lg-6">
            <div class="ev-kv">
              <div class="ev-kv-item"><span>Nombre:</span> <strong id="mNombre">—</strong></div>
              <div class="ev-kv-item"><span>Email:</span> <strong id="mEmail">—</strong></div>
              <div class="ev-kv-item"><span>Teléfono:</span> <strong id="mTelefono">—</strong></div>
              <div class="ev-kv-item"><span>Documento:</span> <strong id="mDocumento">—</strong></div>
              <div class="ev-kv-item"><span>Residencia:</span> <strong id="mResidencia">—</strong></div>

              <!-- Preparado para cuando tu API devuelva tipo_solicitud -->
              <div class="ev-kv-item"><span>Solicitud:</span> <strong id="mTipoSolicitud">—</strong></div>

              <div class="ev-kv-item"><span>Estado:</span> <span id="mEstadoBadge" class="ev-badge ev-review">—</span></div>
            </div>

            <div class="ev-hint mt-3">
              <i class="bi bi-info-circle me-2"></i>
              Valida el recibo (luz/agua/internet) y confirma que el nombre/dirección coincidan con el registro del usuario.
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="ev-proof">
              <div class="ev-proof-title">
                <i class="bi bi-file-earmark-text me-2"></i> Recibo / Comprobante de domicilio
              </div>

              <div class="ev-proof-box">
                <img id="mDocImg" src="" alt="Documento" class="img-fluid d-none">
                <iframe id="mDocPdf" class="ev-doc-frame d-none" src="" title="Documento PDF"></iframe>

                <div id="mNoDoc" class="ev-proof-empty">
                  No hay documento adjunto.
                </div>
              </div>

              <div class="ev-proof-actions">
                <a href="#" id="mDocLink" target="_blank" rel="noopener" class="btn ev-btn-light btn-sm d-none">
                  <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en nueva pestaña
                </a>
              </div>

              <div class="ev-proof-hint">
                Si el documento es PDF y no se previsualiza en tu navegador, usa “Abrir en nueva pestaña”.
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer ev-modal-footer">
        <button type="button" class="btn ev-btn-light" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cerrar
        </button>
      </div>

    </div>
  </div>
</div>

<script src="<?= rtrim(BASE_URL,'/') ?>/views/js/atenderCuentasUsuario.js"></script>

<script>
  (function(){
    const fEstado = document.getElementById('fEstado');
    if(!fEstado) return;

    const clickSet = (btnId, val) => {
      const b = document.getElementById(btnId);
      if(!b) return;
      b.addEventListener('click', () => {
        fEstado.value = String(val);
        fEstado.dispatchEvent(new Event('change', { bubbles:true }));
      });
    };

    clickSet('btnEstadoRevision', '1');
    clickSet('btnEstadoHabilitado', '2');
    clickSet('btnEstadoInactivo', '0');
    clickSet('btnEstadoTodos', 'all');
  })();
</script>
