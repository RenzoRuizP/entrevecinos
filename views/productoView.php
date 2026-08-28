<?php
// views/productoView.php
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/productoEstilo.php'; ?>

<div class="ev-mp-page">

  <div class="ev-card ev-mp-hero mb-3">
    <div class="ev-mp-hero-body">

      <div class="ev-mp-hero-top">
        <div class="ev-mp-hero-left">
          <div class="d-flex align-items-start gap-3">
            <div class="ev-mp-title-icon"><i class="bi bi-journal-text"></i></div>
            <div>
              <h1 class="ev-mp-title mb-1">Mis Publicaciones</h1>
              <div class="ev-mp-subtitle">Gestiona tus productos y servicios antes de mostrarlos en el marketplace.</div>
            </div>
          </div>
        </div>

      </div>

      <div class="ev-mp-hero-bottom ev-mp-hero-bottom--meta-only">
        <div class="ev-table-meta" id="evLblMeta">Mostrando 0 registros</div>
      </div>

      <div class="ev-mp-meta-row" aria-hidden="true"></div>

    </div>
  </div>

  <div class="ev-card ev-filters mb-3" id="evFiltrosWrap">
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

        <div class="col-12 col-sm-6 col-lg-2">
          <label class="form-label" for="fEstadoPublicacion">Estado</label>
          <select class="form-select ev-input" id="fEstadoPublicacion" name="estado_publicacion">
            <option value="all" data-label="Todos">Todos (0)</option>
            <option value="aprobado" data-label="Aprobados">Aprobados (0)</option>
            <option value="pendiente" data-label="Pendientes">Pendientes (0)</option>
            <option value="observado" data-label="Observados">Observados (0)</option>
            <option value="rechazado" data-label="Rechazados">Rechazados (0)</option>
            <option value="borrador" data-label="Borradores">Borradores (0)</option>
            <option value="anulado" data-label="Anulados">Anulados (0)</option>
          </select>
        </div>

        <div class="col-12 col-lg-3">
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
          <label class="form-label">Publicación</label>
          <select class="form-select ev-input" id="fTipoPublicacion" name="tipo_publicacion">
            <option value="">Todas</option>
            <option value="producto">Productos</option>
            <option value="servicio">Servicios</option>
          </select>
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

        <div class="col-12 col-lg-1">
          <label class="form-label">Ordenar</label>
          <select class="form-select ev-input" id="fOrden" name="orden">
            <option value="recientes" selected>Recientes</option>
            <option value="precio_asc">Precio ↑</option>
            <option value="precio_desc">Precio ↓</option>
            <option value="titulo_asc">A → Z</option>
            <option value="titulo_desc">Z → A</option>
          </select>
        </div>

        <div class="col-12 d-grid d-lg-none">
          <button class="btn ev-btn-orange" type="submit">
            <i class="bi bi-search me-1"></i> Aplicar filtros
          </button>
        </div>

      </form>

      <div class="ev-hint mt-2">
        Tip: Combina el <strong>estado</strong> con los demás filtros para encontrar publicaciones en segundos.
      </div>
    </div>
  </div>

  <div class="ev-card">
    <div class="ev-card-header ev-card-header-row ev-publicaciones-card-header">
      <h2 class="ev-card-title mb-0">Publicaciones</h2>
      <div class="ev-publicaciones-header-actions">
        <button id="btnAgregarPublicacion" type="button" class="btn ev-btn-orange">
          <i class="bi bi-plus-circle me-1"></i> Agregar
        </button>
      </div>
    </div>

    <div class="ev-table-wrap">
      <div class="ev-table-frame">
        <div class="table-responsive">
          <table id="tablaPublicaciones" class="table ev-table mb-0">
            <thead>
              <tr>
                <th class="ev-col-codigo text-center">Código</th>
                <th class="ev-col-publicacion text-center">Publicación</th>
                <th class="ev-col-titulo text-center">Título</th>
                <th class="ev-col-precio text-center">Precio</th>
                <th class="ev-col-tipo text-center">Tipo</th>
                <th class="ev-col-categoria text-center">Categoría</th>
                <th class="ev-col-desc text-center">Descripción</th>
                <th class="ev-col-mensaje text-center">Mensaje de soporte</th>
                <th class="ev-col-estado-publicacion text-center">Estado de publicación</th>
                <th class="ev-col-acciones text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="10" class="text-center py-4 ev-empty">
                  <div class="ev-empty-wrap">
                    <i class="bi bi-inbox ev-empty-ico"></i>
                    <div class="ev-empty-text">Cargando publicaciones…</div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

</div>

<div class="modal fade ev-modal" id="modalBuscarPublicacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content ev-modal-content">
      <div class="ev-modal-header">
        <div class="ev-modal-title"><i class="bi bi-search"></i> Buscar publicación</div>
        <button type="button" class="btn-close btn-close-white ev-modal-close-icon" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formBuscarPublicacion">
        <div class="ev-modal-body">
          <div class="mb-3">
            <label class="form-label">Texto</label>
            <input type="text" class="form-control" name="q" placeholder="Ej. camiseta, laptop, clases, reparación…">
            <div class="form-text">Busca por título o palabras clave.</div>
          </div>
        </div>

        <div class="ev-modal-footer">
          <button type="button" class="btn btn-ev-outline" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Cancelar</button>
          <button type="submit" class="btn btn-ev-orange"><i class="bi bi-search"></i> Buscar</button>
        </div>
      </form>

    </div>
  </div>
</div>


<div class="modal fade ev-modal" id="modalAgregarPublicacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered ev-modal-xl">
    <div class="modal-content ev-modal-content ev-modal-flex">
      <div class="ev-modal-header">
        <div class="ev-modal-title"><i class="bi bi-plus-circle"></i> Nueva publicación</div>
        <button type="button" class="btn-close btn-close-white ev-modal-close-icon" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formAgregarPublicacion" class="ev-modal-flex">
        <div class="ev-modal-body ev-modal-body-scroll">

          <div class="row g-3 ev-publicacion-modal-grid">
            <div class="col-12 col-lg-7 ev-publicacion-form-col">

              <div class="ev-section ev-kind-section">
                <div class="ev-step-badge">Paso 1</div>
                <div class="ev-section-title">¿Qué deseas publicar?</div>
                <div class="ev-section-subtitle mb-3">
                  Elige una opción para adaptar el formulario a producto o servicio.
                </div>

                <div class="ev-publicacion-switch ev-publicacion-choice-grid">
                  <?php if (($evPublicarProductosDisponible ?? true) === true): ?>
                  <input type="radio" class="btn-check" name="tipo_publicacion" id="tipoPublicacionProducto" value="producto" autocomplete="off" <?= (($evPublicarProductosDisponible ?? true) === true) ? 'checked' : '' ?>>
                  <label class="ev-publicacion-option" for="tipoPublicacionProducto">
                    <span class="ev-publicacion-option-ico"><i class="bi bi-bag-check"></i></span>
                    <span class="ev-publicacion-option-copy">
                      <strong>Producto</strong>
                      <small>Bien físico para vender o entregar.</small>
                    </span>
                    <span class="ev-publicacion-option-check"><i class="bi bi-check2"></i></span>
                  </label>
                  <?php endif; ?>

                  <?php if (($evPublicarServiciosDisponible ?? true) === true): ?>
                  <input type="radio" class="btn-check" name="tipo_publicacion" id="tipoPublicacionServicio" value="servicio" autocomplete="off" <?= (($evPublicarProductosDisponible ?? true) !== true) ? 'checked' : '' ?>>
                  <label class="ev-publicacion-option" for="tipoPublicacionServicio">
                    <span class="ev-publicacion-option-ico"><i class="bi bi-stars"></i></span>
                    <span class="ev-publicacion-option-copy">
                      <strong>Servicio</strong>
                      <small>Servicio ofrecido a tus vecinos.</small>
                    </span>
                    <span class="ev-publicacion-option-check"><i class="bi bi-check2"></i></span>
                  </label>
                  <?php endif; ?>
                </div>
              </div>

              <div class="ev-section mt-3">
                <div class="ev-step-badge">Paso 2</div>
                <div class="ev-section-title" id="tituloImagenesAdd">Imágenes de la publicación</div>
                <div class="ev-section-subtitle">
                  Imágenes • <strong><span id="contadorImagenes">0</span>/10</strong> — Puedes agregar un máximo de 10 imágenes.
                </div>

                <div id="dropZone" class="ev-dropzone mt-2">
                  <div class="ico"><i class="bi bi-cloud-arrow-up"></i></div>
                  <div class="t1" id="dropZoneTituloAdd">Arrastra tus imágenes aquí o haz clic para seleccionarlas</div>
                  <div class="t2">JPG • PNG • WEBP • Máximo 10 imágenes</div>
                </div>

                <input id="inputImagenes" name="imagenes[]" type="file" class="d-none" multiple accept="image/*" data-max="10">

                <div class="ev-tiles mt-3" id="evTiles"></div>

                <div class="d-flex align-items-center justify-content-between mt-2">
                  <button id="btnLimpiarImagenes" type="button" class="btn btn-ev-outline btn-sm">
                    <i class="bi bi-trash"></i> Limpiar imágenes
                  </button>
                  <small class="text-muted"><span id="contadorImagenesToolbar">0</span>/10 imágenes cargadas</small>
                </div>

                <div class="form-text mt-2" id="hintImagenPrincipalAdd">La primera imagen será la portada de tu publicación.</div>
              </div>

              <div class="ev-section mt-3">
                <div class="ev-step-badge">Paso 3</div>
                <div class="ev-section-title">Información principal</div>

                <div class="mb-3 mt-2">
                  <label class="form-label" id="labelTituloAdd">Título <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="titulo" placeholder="Escribe un título claro y atractivo">
                </div>

                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label class="form-label" id="labelPrecioAdd">Precio (S/) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" name="precio" placeholder="0.00">
                    <div class="form-text ev-service-hint d-none" data-ev-service-only>
                      Para servicios, puedes registrar un precio base o referencial.
                    </div>
                  </div>

                  <div class="col-12 col-md-6" id="wrapEstadoProductoAdd" data-ev-product-only>
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
                      <option value="" selected disabled>-- Seleccionar --</option>
                    </select>
                  </div>

                  <div class="col-12" id="wrapTipoAtencionProductoAdd" data-ev-product-only>
                    <label class="form-label" for="tipoAtencionProducto">¿Requiere preparación? <span class="text-danger">*</span></label>
                    <select id="tipoAtencionProducto" class="form-select" name="tipo_atencion_producto" required>
                      <option value="" selected disabled>-- Seleccionar --</option>
                      <option value="requiere_preparacion">Sí</option>
                      <option value="no_requiere_preparacion">No</option>
                    </select>
                    <div class="form-text" id="hintTipoAtencionProductoAdd">
                      Selecciona “Sí” cuando necesites preparar el producto antes de entregarlo.
                    </div>
                  </div>
                </div>
              </div>

              <div class="ev-section mt-3">
                <div class="ev-step-badge">Paso 4</div>
                <div class="ev-section-title">Detalles de la publicación</div>
                <div class="mb-0 mt-2">
                  <label class="form-label" id="labelDescripcionAdd">Descripción <span class="text-danger">*</span></label>
                  <textarea class="form-control" name="descripcion" rows="4" placeholder="Cuenta los detalles más importantes para que tus vecinos se animen a comprar o contactarte."></textarea>
                </div>
              </div>

            </div>

            <div class="col-12 col-lg-5 ev-publicacion-preview-col">
              <div class="ev-preview-sticky">
                <div id="previewMount"></div>
              </div>
            </div>
          </div>

        </div>

        <div class="ev-modal-footer">
          <button type="button" class="btn btn-ev-outline" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Cancelar</button>
          <button type="submit" class="btn btn-ev-orange btn-guardar">
            <i class="bi bi-check2-circle"></i> Guardar
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<div class="modal fade ev-modal" id="modalEditarPublicacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered ev-modal-xl">
    <div class="modal-content ev-modal-content ev-modal-flex">
      <div class="ev-modal-header">
        <div class="ev-modal-title"><i class="bi bi-pencil-square"></i> Editar publicación</div>
        <button type="button" class="btn-close btn-close-white ev-modal-close-icon" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formEditarPublicacion" class="ev-modal-flex">
        <input type="hidden" id="edit_id" name="edit_id" value="">

        <div class="ev-modal-body ev-modal-body-scroll">

          <div class="row g-3 ev-publicacion-modal-grid">
            <div class="col-12 col-lg-7 ev-publicacion-form-col">

              <div class="ev-section ev-kind-section">
                <div class="ev-step-badge">Paso 1</div>
                <div class="ev-section-title">Tipo de publicación</div>
                <div class="ev-section-subtitle mb-3">
                  Puedes ajustar si esta publicación corresponde a un producto o servicio.
                </div>

                <div class="ev-publicacion-switch ev-publicacion-choice-grid">
                  <input type="radio" class="btn-check" name="edit_tipo_publicacion" id="edit_tipoPublicacionProducto" value="producto" autocomplete="off" checked>
                  <label class="ev-publicacion-option" for="edit_tipoPublicacionProducto">
                    <span class="ev-publicacion-option-ico"><i class="bi bi-bag-check"></i></span>
                    <span class="ev-publicacion-option-copy">
                      <strong>Producto</strong>
                      <small>Bien físico para vender o entregar.</small>
                    </span>
                    <span class="ev-publicacion-option-check"><i class="bi bi-check2"></i></span>
                  </label>

                  <input type="radio" class="btn-check" name="edit_tipo_publicacion" id="edit_tipoPublicacionServicio" value="servicio" autocomplete="off">
                  <label class="ev-publicacion-option" for="edit_tipoPublicacionServicio">
                    <span class="ev-publicacion-option-ico"><i class="bi bi-stars"></i></span>
                    <span class="ev-publicacion-option-copy">
                      <strong>Servicio</strong>
                      <small>Servicio ofrecido a tus vecinos.</small>
                    </span>
                    <span class="ev-publicacion-option-check"><i class="bi bi-check2"></i></span>
                  </label>
                </div>
              </div>

              <div class="ev-section mt-3">
                <div class="d-flex align-items-start justify-content-between gap-2">
                  <div>
                    <div class="ev-step-badge">Paso 2</div>
                    <div class="ev-section-title" id="tituloImagenesEdit">Imágenes de la publicación</div>
                    <div class="ev-section-subtitle">
                      Imágenes • <strong><span id="contadorImagenesEdit">0</span>/10</strong> — Puedes agregar un máximo de 10 imágenes.
                    </div>
                  </div>
                  <div class="text-muted small">Cargadas: <strong><span id="contadorImagenesToolbarEdit">0</span></strong></div>
                </div>

                <div id="dropZoneEdit" class="ev-dropzone mt-2">
                  <div class="ico"><i class="bi bi-cloud-arrow-up"></i></div>
                  <div class="t1" id="dropZoneTituloEdit">Arrastra tus imágenes aquí o haz clic para seleccionarlas</div>
                  <div class="t2">JPG • PNG • WEBP • Máximo 10 imágenes</div>
                </div>

                <input id="inputImagenesEdit" type="file" class="d-none" multiple accept="image/*" data-max="10">

                <div class="ev-tiles mt-3" id="evTilesEdit"></div>

                <div class="d-flex align-items-center justify-content-between mt-2">
                  <button id="btnLimpiarImagenesEdit" type="button" class="btn btn-ev-outline btn-sm">
                    <i class="bi bi-trash"></i> Limpiar imágenes
                  </button>
                  <small class="text-muted">La primera imagen será la portada.</small>
                </div>
              </div>

              <div class="ev-section mt-3">
                <div class="ev-step-badge">Paso 3</div>
                <div class="ev-section-title">Información principal</div>

                <div class="mb-3 mt-2">
                  <label class="form-label" id="labelTituloEdit">Título <span class="text-danger">*</span></label>
                  <input id="edit_titulo" type="text" class="form-control" name="edit_titulo">
                </div>

                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label class="form-label" id="labelPrecioEdit">Precio (S/) <span class="text-danger">*</span></label>
                    <input id="edit_precio" type="number" step="0.01" min="0" class="form-control" name="edit_precio">
                    <div class="form-text ev-service-hint d-none" data-ev-service-only>
                      Para servicios, puedes registrar un precio base o referencial.
                    </div>
                  </div>

                  <div class="col-12 col-md-6" id="wrapEstadoProductoEdit" data-ev-product-only>
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
                      <option value="" selected disabled>-- Seleccionar --</option>
                    </select>
                  </div>

                  <div class="col-12" id="wrapTipoAtencionProductoEdit" data-ev-product-only>
                    <label class="form-label" for="edit_tipoAtencionProducto">¿Requiere preparación? <span class="text-danger">*</span></label>
                    <select id="edit_tipoAtencionProducto" class="form-select" name="edit_tipo_atencion_producto" required>
                      <option value="" disabled>-- Seleccionar --</option>
                      <option value="requiere_preparacion">Sí</option>
                      <option value="no_requiere_preparacion">No</option>
                    </select>
                    <div class="form-text" id="hintTipoAtencionProductoEdit">
                      Selecciona “Sí” cuando necesites preparar el producto antes de entregarlo.
                    </div>
                  </div>
                </div>
              </div>

              <div class="ev-section mt-3">
                <div class="ev-step-badge">Paso 4</div>
                <div class="ev-section-title">Detalles de la publicación</div>
                <div class="mb-0 mt-2">
                  <label class="form-label" id="labelDescripcionEdit">Descripción <span class="text-danger">*</span></label>
                  <textarea id="edit_descripcion" class="form-control" name="edit_descripcion" rows="4"></textarea>
                </div>
              </div>

            </div>

            <div class="col-12 col-lg-5 ev-publicacion-preview-col">
              <div class="ev-preview-sticky">
                <div id="evPreviewWrapperEditContainer"></div>
              </div>
            </div>
          </div>

        </div>

        <div class="ev-modal-footer">
          <button type="button" class="btn btn-ev-outline" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Cancelar</button>
          <button type="submit" class="btn btn-ev-orange btn-guardar">
            <i class="bi bi-check2-circle"></i> Guardar
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
