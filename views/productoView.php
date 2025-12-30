<?php
// views/productoView.php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/productoEstilo.php'; ?>

<div class="ev-pubs-wrapper fade-in">
  <div class="card ev-pubs-card">
    <div class="card-body">

      <!-- Header -->
      <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 ev-pubs-header">
        <div class="d-flex align-items-start gap-3">
          <div class="ev-pubs-title-icon"><i class="bi bi-journal-text"></i></div>
          <div>
            <div class="ev-pubs-title">Mis Productos</div>
            <div class="ev-pubs-subtitle">Gestiona tus productos y controla cuáles se muestran en el marketplace.</div>
          </div>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2">
          <button id="btnBuscarPublicacion" type="button" class="btn btn-ev-outline">
            <i class="bi bi-search"></i> Buscar
          </button>
          <button id="btnAgregarPublicacion" type="button" class="btn btn-ev-orange">
            <i class="bi bi-plus-circle"></i> Agregar
          </button>
        </div>
      </div>

      <hr class="ev-pubs-divider">

      <!-- Tabla -->
      <div class="ev-pubs-table-wrapper">
        <div class="table-responsive">
          <table id="tablaPublicaciones" class="table ev-pubs-table align-middle">
            <thead>
              <tr>
                <th>Código</th>
                <th>Título</th>
                <th>Precio (S/)</th>
                <th>Estado</th>
                <th>Tipo</th>
                <th>Categoría</th>
                <th>Descripción</th>
                <th class="text-center">Opciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">Cargando publicaciones…</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Footer -->
      <div class="ev-pubs-footer mt-3">
        <div class="ev-pubs-per-page">
          <label class="form-label mb-1">Registros por página:</label>
          <select class="form-select ev-select-sm" style="max-width:120px;">
            <option selected>10</option>
            <option>20</option>
            <option>50</option>
          </select>
        </div>
        <div class="ev-pubs-pager">
          <!-- si tu paginación la inyecta un plugin, no toques esto -->
        </div>
      </div>

    </div>
  </div>
</div>

<!-- =========================
     MODAL: BUSCAR
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
