<?php
// views/productoView.php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/productoEstilo.php'; ?>

<div class="ev-mp-page">

  <!-- HERO PREMIUM -->
  <div class="ev-card ev-mp-hero mb-3">
    <div class="ev-mp-hero-body">

      <div class="ev-mp-hero-top">
        <div class="ev-mp-hero-left">
          <div class="d-flex align-items-start gap-3">
            <div class="ev-mp-title-icon"><i class="bi bi-journal-text"></i></div>
            <div>
              <h1 class="ev-mp-title mb-1">Mis Productos</h1>
              <div class="ev-mp-subtitle">Gestiona tus productos y controla cuáles se muestran en el marketplace.</div>
            </div>
          </div>
        </div>

        <div class="ev-mp-hero-right">
          <button class="ev-icon-btn" type="button" id="btnRefrescarMisProductos" title="Refrescar" aria-label="Refrescar">
            <i class="bi bi-arrow-clockwise"></i>
          </button>

          <button id="btnToggleFiltros" class="btn ev-btn-light" type="button"
                  data-bs-toggle="collapse" data-bs-target="#evFiltrosWrap"
                  aria-expanded="true" aria-controls="evFiltrosWrap">
            <i class="bi bi-funnel me-1"></i> Filtros
          </button>

          <button id="btnAgregarPublicacion" type="button" class="btn ev-btn-orange">
            <i class="bi bi-plus-circle me-1"></i> Agregar
          </button>
        </div>
      </div>

      <div class="ev-mp-hero-bottom">
        <div class="ev-summary-pill">
          <span class="ev-summary-label">Total:</span>
          <span class="ev-summary-count" id="evTabCountAll">0</span>
        </div>

        <div class="ev-mp-tabs" role="tablist" aria-label="Estados de productos">
          <button type="button" class="btn ev-tab ev-btn-light btn-sm active" data-tab="all" aria-selected="true">
            <i class="bi bi-grid-3x3-gap me-1"></i> Todos <span class="ev-pill" id="evTabCountAll2">0</span>
          </button>

          <button type="button" class="btn ev-tab ev-btn-light btn-sm" data-tab="aprobado" aria-selected="false">
            <i class="bi bi-check-circle me-1"></i> Aprobados <span class="ev-pill" id="evTabCountAprobado">0</span>
          </button>

          <button type="button" class="btn ev-tab ev-btn-light btn-sm" data-tab="pendiente" aria-selected="false">
            <i class="bi bi-hourglass-split me-1"></i> Pendientes <span class="ev-pill" id="evTabCountPendiente">0</span>
          </button>

          <button type="button" class="btn ev-tab ev-btn-light btn-sm" data-tab="observado" aria-selected="false">
            <i class="bi bi-exclamation-circle me-1"></i> Observados <span class="ev-pill" id="evTabCountObservado">0</span>
          </button>

          <button type="button" class="btn ev-tab ev-btn-light btn-sm" data-tab="rechazado" aria-selected="false">
            <i class="bi bi-x-circle me-1"></i> Rechazados <span class="ev-pill" id="evTabCountRechazado">0</span>
          </button>

          <button type="button" class="btn ev-tab ev-btn-light btn-sm" data-tab="borrador" aria-selected="false">
            <i class="bi bi-pencil-square me-1"></i> Borradores <span class="ev-pill" id="evTabCountBorrador">0</span>
          </button>

          <button type="button" class="btn ev-tab ev-btn-light btn-sm" data-tab="anulado" aria-selected="false">
            <i class="bi bi-slash-circle me-1"></i> Anulados <span class="ev-pill" id="evTabCountAnulado">0</span>
          </button>
        </div>
      </div>

      <div class="ev-mp-meta-row">
        <div class="ev-table-meta" id="evLblMeta">Mostrando 0 registros</div>
      </div>

    </div>
  </div>

  <!-- FILTROS INLINE -->
  <div class="ev-card ev-filters mb-3 collapse show" id="evFiltrosWrap">
    <div class="ev-card-header">
      <div class="ev-card-header-row">
        <h2 class="ev-card-title mb-0">Filtros</h2>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn ev-btn-outline btn-sm" id="btnLimpiarFiltros">
            <i class="bi bi-eraser me-1"></i> Limpiar
          </button>
        </div>
      </div>
    </div>

    <div class="ev-card-body">
      <form id="formFiltrosMisProductos" class="row g-3 align-items-end">

        <div class="col-12 col-lg-4">
          <label class="form-label">Buscar</label>
          <div class="position-relative">
            <i class="bi bi-search ev-input-icon"></i>
            <input
              type="text"
              class="form-control ev-input ps-5"
              id="fTexto"
              name="q"
              placeholder="Buscar por título, descripción, tipo o categoría..."
              autocomplete="off"
            />
          </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-2">
          <label class="form-label">Tipo</label>
          <select class="form-select ev-input" id="fTipo" name="tipo">
            <option value="">Todos</option>
          </select>
        </div>

        <div class="col-12 col-sm-6 col-lg-2">
          <label class="form-label">Categoría</label>
          <select class="form-select ev-input" id="fCategoria" name="categoria" disabled>
            <option value="">Todas</option>
          </select>
        </div>

        <div class="col-6 col-lg-1">
          <label class="form-label">Min (S/)</label>
          <input type="number" step="0.01" min="0" class="form-control ev-input" id="fPrecioMin" name="min" placeholder="0.00">
        </div>

        <div class="col-6 col-lg-1">
          <label class="form-label">Max (S/)</label>
          <input type="number" step="0.01" min="0" class="form-control ev-input" id="fPrecioMax" name="max" placeholder="999.99">
        </div>

        <div class="col-12 col-lg-2">
          <label class="form-label">Ordenar</label>
          <select class="form-select ev-input" id="fOrden" name="orden">
            <option value="recientes" selected>Más recientes</option>
            <option value="precio_asc">Precio: menor a mayor</option>
            <option value="precio_desc">Precio: mayor a menor</option>
            <option value="titulo_asc">Título: A → Z</option>
            <option value="titulo_desc">Título: Z → A</option>
          </select>
        </div>

        <div class="col-12 d-grid d-lg-none">
          <button class="btn ev-btn-orange" type="submit">
            <i class="bi bi-search me-1"></i> Aplicar filtros
          </button>
        </div>

      </form>

      <div class="ev-hint mt-2">
        Tip: Combina <strong>tabs</strong> + <strong>filtros</strong> para encontrar productos en segundos.
      </div>
    </div>
  </div>

  <!-- TABLA -->
  <div class="ev-card">
    <div class="ev-card-header ev-card-header-row">
      <h2 class="ev-card-title mb-0">Productos</h2>
      <div class="ev-table-meta" id="evLblFooterLeft">Mostrando 0 de 0</div>
    </div>

    <div class="ev-table-wrap">
      <div class="ev-table-frame">
        <div class="table-responsive">
          <table id="tablaPublicaciones" class="table ev-table mb-0">
            <thead>
              <tr>
                <th class="ev-col-codigo">Código</th>
                <th class="ev-col-titulo">Título</th>
                <th class="text-end ev-col-precio">Precio</th>
                <th class="ev-col-estado">Estado</th>
                <th class="ev-col-tipo">Tipo</th>
                <th class="ev-col-categoria">Categoría</th>
                <th class="ev-col-desc">Descripción</th>
                <th class="text-end ev-col-acciones">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="8" class="text-center py-4 ev-empty">
                  <div class="ev-empty-wrap">
                    <i class="bi bi-inbox ev-empty-ico"></i>
                    <div class="ev-empty-text">Cargando productos…</div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="ev-card-footer">
      <div class="ev-footer-left">En móvil, cada fila se muestra como tarjeta (más cómodo).</div>
      <div class="ev-footer-right"></div>
    </div>
  </div>

</div>

<!-- =========================
     MODAL: BUSCAR (LEGACY - por compatibilidad)
     (No se usa como filtro principal, pero lo dejo para no romper tu JS si aún lo llama)
========================= -->
<div class="modal fade ev-modal" id="modalBuscarPublicacion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content ev-modal-content">
      <div class="ev-modal-header">
        <div class="ev-modal-title"><i class="bi bi-search"></i> Buscar producto</div>
        <button type="button" class="btn-close btn-close-white ev-modal-close-icon" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formBuscarPublicacion">
        <div class="ev-modal-body">
          <div class="mb-3">
            <label class="form-label">Texto</label>
            <input type="text" class="form-control" name="q" placeholder="Ej. camiseta, laptop, servicio…">
            <div class="form-text">Busca por título o palabras clave.</div>
          </div>
        </div>

        <div class="ev-modal-footer">
          <button type="button" class="btn btn-ev-outline" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-ev-orange"><i class="bi bi-search"></i> Buscar</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- =========================
     MODAL: AGREGAR
========================= -->
<div class="modal fade ev-modal" id="modalAgregarPublicacion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered ev-modal-xl">
    <div class="modal-content ev-modal-content ev-modal-flex">
      <div class="ev-modal-header">
        <div class="ev-modal-title"><i class="bi bi-plus-circle"></i> Nuevo producto</div>
        <button type="button" class="btn-close btn-close-white ev-modal-close-icon" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formAgregarPublicacion" class="ev-modal-flex">
        <div class="ev-modal-body ev-modal-body-scroll">

          <div class="row g-3">
            <!-- Col izquierda -->
            <div class="col-12 col-lg-7">

              <div class="ev-section">
                <div class="ev-section-title">1. Fotos del producto</div>
                <div class="ev-section-subtitle">
                  Fotos • <strong><span id="contadorImagenes">0</span>/10</strong> — Puedes agregar un máximo de 10 fotos.
                </div>

                <div id="dropZone" class="ev-dropzone mt-2">
                  <div class="ico"><i class="bi bi-cloud-arrow-up"></i></div>
                  <div class="t1">Arrastra tus fotos aquí o haz clic para seleccionarlas</div>
                  <div class="t2">JPG • PNG • WEBP • Máximo 10 imágenes</div>
                </div>

                <input id="inputImagenes" name="imagenes[]" type="file" class="d-none" multiple accept="image/*" data-max="10">

                <div class="ev-tiles mt-3" id="evTiles"></div>

                <div class="d-flex align-items-center justify-content-between mt-2">
                  <button id="btnLimpiarImagenes" type="button" class="btn btn-ev-outline btn-sm">
                    <i class="bi bi-trash"></i> Limpiar imágenes
                  </button>
                  <small class="text-muted"><span id="contadorImagenesToolbar">0</span>/10 fotos cargadas</small>
                </div>

                <div class="form-text mt-2">La primera foto será la imagen principal de tu publicación.</div>
              </div>

              <div class="ev-section mt-3">
                <div class="ev-section-title">2. Información principal</div>

                <div class="mb-3">
                  <label class="form-label">Título <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="titulo" placeholder="Escribe un título claro y atractivo">
                </div>

                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label class="form-label">Precio (S/) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" name="precio" placeholder="0.00">
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                    <select class="form-select" name="estado">
                      <option value="Nuevo" selected>Nuevo</option>
                      <option value="Usado">Usado</option>
                      <option value="NoAplica">No aplica</option>
                    </select>
                  </div>

                  <div class="col-12 col-md-6">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select id="comboTipo" class="form-select" name="comboTipo">
                      <option value="">-- Seleccione Tipos --</option>
                    </select>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label">Categoría <span class="text-danger">*</span></label>
                    <select id="comboCategoria" class="form-select" name="categoria">
                      <option value="">-- Selecciona un tipo primero --</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="ev-section mt-3">
                <div class="ev-section-title">3. Detalles del producto o servicio</div>
                <div class="mb-0">
                  <label class="form-label">Descripción <span class="text-danger">*</span></label>
                  <textarea class="form-control" name="descripcion" rows="4" placeholder="Cuenta los detalles más importantes para que tus vecinos se animen a comprar."></textarea>
                </div>
              </div>

            </div>

            <!-- Col derecha (preview) -->
            <div class="col-12 col-lg-5">
              <div id="previewMount"></div>
            </div>
          </div>

        </div>

        <div class="ev-modal-footer">
          <button type="button" class="btn btn-ev-outline" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-ev-orange btn-guardar">
            <i class="bi bi-check2-circle"></i> Guardar
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- =========================
     MODAL: EDITAR
========================= -->
<div class="modal fade ev-modal" id="modalEditarPublicacion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered ev-modal-xl">
    <div class="modal-content ev-modal-content ev-modal-flex">
      <div class="ev-modal-header">
        <div class="ev-modal-title"><i class="bi bi-pencil-square"></i> Editar producto</div>
        <button type="button" class="btn-close btn-close-white ev-modal-close-icon" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formEditarPublicacion" class="ev-modal-flex">
        <input type="hidden" id="edit_id" name="edit_id" value="">

        <div class="ev-modal-body ev-modal-body-scroll">

          <div class="row g-3">
            <!-- Col izquierda -->
            <div class="col-12 col-lg-7">

              <div class="ev-section">
                <div class="d-flex align-items-center justify-content-between gap-2">
                  <div>
                    <div class="ev-section-title">1. Fotos del producto</div>
                    <div class="ev-section-subtitle">
                      Fotos • <strong><span id="contadorImagenesEdit">0</span>/10</strong> — Puedes agregar un máximo de 10 fotos.
                    </div>
                  </div>
                  <div class="text-muted small">Cargadas: <strong><span id="contadorImagenesToolbarEdit">0</span></strong></div>
                </div>

                <div id="dropZoneEdit" class="ev-dropzone mt-2">
                  <div class="ico"><i class="bi bi-cloud-arrow-up"></i></div>
                  <div class="t1">Arrastra tus fotos aquí o haz clic para seleccionarlas</div>
                  <div class="t2">JPG • PNG • WEBP • Máximo 10 imágenes</div>
                </div>

                <input id="inputImagenesEdit" type="file" class="d-none" multiple accept="image/*" data-max="10">

                <div class="ev-tiles mt-3" id="evTilesEdit"></div>

                <div class="d-flex align-items-center justify-content-between mt-2">
                  <button id="btnLimpiarImagenesEdit" type="button" class="btn btn-ev-outline btn-sm">
                    <i class="bi bi-trash"></i> Limpiar imágenes
                  </button>
                  <small class="text-muted">La primera foto será la imagen principal.</small>
                </div>
              </div>

              <div class="ev-section mt-3">
                <div class="ev-section-title">2. Información principal</div>

                <div class="mb-3">
                  <label class="form-label">Título <span class="text-danger">*</span></label>
                  <input id="edit_titulo" type="text" class="form-control" name="edit_titulo">
                </div>

                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label class="form-label">Precio (S/) <span class="text-danger">*</span></label>
                    <input id="edit_precio" type="number" step="0.01" min="0" class="form-control" name="edit_precio">
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                    <select id="edit_estado" class="form-select" name="edit_estado">
                      <option value="Nuevo">Nuevo</option>
                      <option value="Usado">Usado</option>
                      <option value="NoAplica">No aplica</option>
                    </select>
                  </div>

                  <div class="col-12 col-md-6">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select id="edit_comboTipo" class="form-select" name="edit_comboTipo">
                      <option value="">-- Seleccione Tipos --</option>
                    </select>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label">Categoría <span class="text-danger">*</span></label>
                    <select id="edit_comboCategoria" class="form-select" name="edit_comboCategoria">
                      <option value="">-- Selecciona un tipo primero --</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="ev-section mt-3">
                <div class="ev-section-title">3. Detalles del producto o servicio</div>
                <div class="mb-0">
                  <label class="form-label">Descripción <span class="text-danger">*</span></label>
                  <textarea id="edit_descripcion" class="form-control" name="edit_descripcion" rows="4"></textarea>
                </div>
              </div>

            </div>

            <!-- Col derecha (preview) -->
            <div class="col-12 col-lg-5">
              <div id="evPreviewWrapperEditContainer"></div>
            </div>
          </div>

        </div>

        <div class="ev-modal-footer">
          <button type="button" class="btn btn-ev-outline" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-ev-orange btn-guardar">
            <i class="bi bi-check2-circle"></i> Guardar cambios
          </button>
        </div>
      </form>

    </div>
  </div>
</div>