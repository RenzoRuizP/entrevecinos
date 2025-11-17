<?php 
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  // Exponer BASE_URL para los fetch del front
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<div class="container-publicaciones fade-in">
  <div class="card ev-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
      <h5 class="mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-list-ul"></i> Publicaciones
      </h5>

        <button type="button" id="btnBuscar" class="btn btn-ev-outline d-flex align-items-center gap-2"
          data-bs-toggle="modal" data-bs-target="#modalBuscarPublicacion">
          <i class="bi bi-search"></i><span class="d-none d-sm-inline">Buscar</span>
        </button>

        <button type="button" id="btnAgregar" class="btn btn-ev-primary d-flex align-items-center gap-2"
          data-bs-toggle="modal" data-bs-target="#modalAgregarPublicacion">
          <i class="bi bi-plus-lg"></i><span class="d-none d-sm-inline">Agregar</span>
        </button>
      </div>
    </div>

    <div class="ev-subheader sticky-toolbar">
      <div class="ev-kpis">
        <div class="ev-kpi"><span class="ev-kpi-label">Total</span><span class="ev-kpi-value">128</span></div>
        <div class="ev-kpi"><span class="ev-kpi-label">Activas</span><span class="ev-kpi-value text-success">93</span></div>
        <div class="ev-kpi"><span class="ev-kpi-label">Anuladas</span><span class="ev-kpi-value text-danger">35</span></div>
      </div>

      <div class="ev-filters-chips">
        <span class="ev-chip-filter">Estado: Nuevo <button class="btn-close btn-close-white ms-2" aria-label="Quitar"></button></span>
        <span class="ev-chip-filter">Precio &lt; S/ 50 <button class="btn-close btn-close-white ms-2" aria-label="Quitar"></button></span>
        <button class="btn btn-ev-soft btn-sm ms-1">Limpiar todo</button>
      </div>
    </div>

    <div class="card-body p-0">
    <div class="table-responsive ev-table-wrap">
      <table class="table table-hover align-middle mb-0 ev-table" id="tablaPublicaciones">
        <thead>
          <tr>
            <th data-sort="codigo">Código</th>
            <th data-sort="titulo">Título</th>
            <th data-sort="precio">Precio (S/)</th>
            <th data-sort="estado">Estado</th>
            <th data-sort="fecha">Fecha</th>
            <th class="text-center">Opciones</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="6" class="text-center py-4 text-muted">
              Cargando publicaciones…
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="ev-foot">
      <div class="ev-page-size">
        Registros por página:
        <select class="form-select form-select-sm input-premium ev-select">
          <option selected>10</option><option>20</option><option>50</option>
        </select>
      </div>
      <div class="ev-range small text-muted">–</div>
      <ul class="ev-pagination">
        <li><button class="ev-page-btn" title="Primero" disabled><i class="bi bi-chevron-bar-left"></i></button></li>
        <li><button class="ev-page-btn" title="Anterior" disabled><i class="bi bi-chevron-left"></i></button></li>
        <li><button class="ev-page-btn active">1</button></li>
        <li><button class="ev-page-btn">2</button></li>
        <li><button class="ev-page-btn">3</button></li>
        <li><button class="ev-page-btn" title="Siguiente"><i class="bi bi-chevron-right"></i></button></li>
        <li><button class="ev-page-btn" title="Último"><i class="bi bi-chevron-bar-right"></i></button></li>
      </ul>
    </div>
  </div>

  </div>
</div>

<?php include_once __DIR__ . '/estilos/publicacionesEstilo.php'; ?>

<!-- 🔎 Modal Buscar -->
<div class="modal fade" id="modalBuscarPublicacion" tabindex="-1" aria-labelledby="lblBuscarPublicacion" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content border-0 ev-card">
      <div class="modal-header">
        <h5 class="modal-title" id="lblBuscarPublicacion"><i class="bi bi-search me-2"></i>Buscar publicaciones</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formBuscarPublicacion">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Texto</label>
              <input type="text" name="q" class="form-control input-premium" placeholder="Título o descripción…">
            </div>
            <div class="col-md-3">
              <label class="form-label">Categoría</label>
              <select name="categoria" class="form-select input-premium">
                <option value="">Todas</option>
                <option>Electrodomésticos</option>
                <option>Hogar</option>
                <option>Servicios</option>
                <option>Otros</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Estado</label>
              <select name="estado" class="form-select input-premium">
                <option value="">Todos</option>
                <option>Nuevo</option>
                <option>Usado</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Precio mín. (S/)</label>
              <input type="number" name="precio_min" class="form-control input-premium" step="0.01" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Precio máx. (S/)</label>
              <input type="number" name="precio_max" class="form-control input-premium" step="0.01" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Desde</label>
              <input type="date" name="desde" class="form-control input-premium">
            </div>
            <div class="col-md-3">
              <label class="form-label">Hasta</label>
              <input type="date" name="hasta" class="form-control input-premium">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="reset" class="btn btn-cancelar">Limpiar</button>
          <button type="submit" class="btn btn-outline-success">Aplicar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ➕ Modal Agregar -->
<div class="modal fade" id="modalAgregarPublicacion" tabindex="-1" aria-labelledby="lblAgregarPublicacion" aria-hidden="true" >
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-md-down">
    <div class="modal-content border-0 ev-card">
      <div class="modal-header">
        <h5 class="modal-title" id="lblAgregarPublicacion">
          <i class="bi bi-plus-lg me-2"></i>Nueva publicación
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formAgregarPublicacion">
        <div class="modal-body">
          <div class="mpm-grid">
            <!-- IZQUIERDA: FORM -->
            <section class="mpm-left">
              <div class="row g-3">

                <!-- =========================
                     📸 Sección: Fotos
                ========================== -->
                <div class="col-12">
                  <h6 class="ev-section-title mb-1">1. Fotos del producto</h6>
                  <label class="form-label fw-semibold ev-required" style="color:#0F592F;">
                    Fotos • <span id="contadorImagenes">0</span>/<span>10</span>
                    <span class="text-muted"> - Puedes agregar un máximo de 10 fotos.</span>
                  </label>
                  <!-- ---------------------------- -->
                  <!-- 📸 Uploader mejorado (UX/UI) -->
                  <!-- ---------------------------- -->
                  <div id="uploaderAgregar" class="ev-uploader mt-1">

                    <!-- input real oculto -->
                    <input
                      type="file"
                      id="inputImagenes"
                      name="imagenes[]"
                      accept="image/*"
                      multiple
                      data-max="10"
                      class="visually-hidden"
                    />

                    <!-- Zona Drag & Drop -->
                    <div id="dropZone" class="ev-dropzone mb-3">
                      <div class="dz-icon">
                        <i class="bi bi-cloud-arrow-up"></i>
                      </div>
                      <div class="dz-text">
                        <strong>Arrastra tus fotos aquí</strong> o haz clic para seleccionarlas
                      </div>
                      <div class="dz-subtext">
                        JPG • PNG • WEBP • Máximo 10 imágenes
                      </div>
                    </div>

                    <!-- Grid de miniaturas -->
                    <div id="evTiles" class="ev-tiles ev-tiles-grid mb-2">
                      <div class="ev-tile ev-tile-add" id="tileAgregar">
                        <div class="ico"><i class="bi bi-plus-lg"></i></div>
                        <div class="t1">Agregar fotos</div>
                      </div>
                    </div>

                    <!-- Acciones y contador -->
                    <div class="ev-toolbar-uploads">
                      <button id="btnLimpiarImagenes" type="button" class="btn-clear-images">
                        <i class="bi bi-trash3"></i>
                        Limpiar imágenes
                      </button>

                      <div class="ev-toolbar-uploads-count">
                        <span id="contadorImagenesToolbar">0</span>/10 fotos cargadas
                      </div>
                    </div>


                    <small class="text-muted mt-2 d-block">
                      La primera foto será la imagen principal de tu publicación.
                    </small>

                  </div>

                  
                </div>

                <!-- =========================
                     🛒 Sección: Información principal
                ========================== -->
                <div class="col-12 mt-2">
                  <h6 class="ev-section-title mb-1">2. Información principal</h6>
                </div>

                <div class="col-12">
                  <label class="form-label ev-required" for="titulo">Título</label>
                  <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    class="form-control input-premium"
                    placeholder="Escribe un título claro y atractivo"
                    required
                  >
                </div>

                <div class="col-md-6">
                  <label class="form-label ev-required" for="precio">Precio (S/)</label>
                  <input
                    type="number"
                    id="precio"
                    name="precio"
                    class="form-control input-premium"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    required
                  >
                </div>

                <div class="col-md-6">
                  <label class="form-label ev-required" for="estado">Estado</label>
                  <select
                    id="estado"
                    name="estado"
                    class="form-select input-premium"
                    required
                  >
                    <option>Nuevo</option>
                    <option>Usado</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label ev-required" for="comboTipo">Tipo</label>
                  <select
                    id="comboTipo"
                    name="comboTipo"
                    class="form-select input-premium"
                    required
                  >
                    <option value="">-- Seleccione Tipos --</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label ev-required" for="comboCategoria">Categoría</label>
                  <select
                    id="comboCategoria"
                    name="categoria"
                    class="form-select input-premium"
                    required
                  >
                    <option value="" selected disabled>-- Selecciona un tipo primero --</option>
                  </select>
                </div>

                <!-- =========================
                     📄 Sección: Detalles
                ========================== -->
                <div class="col-12 mt-2">
                  <h6 class="ev-section-title mb-1">3. Detalles del producto o servicio</h6>
                </div>

                <div class="col-12">
                  <label class="form-label ev-required" for="descripcion">Descripción</label>
                  <textarea
                    id="descripcion"
                    name="descripcion"
                    class="form-control input-premium"
                    rows="4"
                    placeholder="Cuenta los detalles más importantes para que tus vecinos se animen a comprar."
                    required
                  ></textarea>
                </div>
              </div>
            </section>

            <!-- DERECHA: PREVIEW (solo se oculta en móvil por CSS) -->
            <aside class="mpm-right">
              <div class="mpm-preview-wrap">
                <div class="col-lg-12" id="previewMount"></div>
              </div>
            </aside>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-outline-success btn-guardar">Guardar</button>
        </div>
      </form>

    </div>
  </div>
</div>


<!-- Scripts únicos de esta vista -->
<script src="<?= BASE_URL ?>public/js/combo_tipo.js"></script>
<script src="<?= BASE_URL ?>public/js/publicacion.js"></script>
