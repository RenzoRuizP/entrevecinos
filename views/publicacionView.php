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
              <th data-sort="garante">Garante</th>
              <th data-sort="producto">Producto</th>
              <th data-sort="mecanismo">Mecanismo de pago</th>
              <th class="text-center">Opciones</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td data-label="Código"><span class="ev-code">000001</span></td>
              <td data-label="Garante">Privado</td>
              <td data-label="Producto" class="td-trunc" title="Producto Privado">Privado</td>
              <td data-label="Mecanismo">Px</td>
              <td data-label="Opciones" class="text-center">
                <div class="ev-actions">
                  <button class="ev-chip ev-chip-amber">Finalizar</button>
                  <button class="ev-chip ev-chip-green">Renovar</button>
                  <button class="ev-chip ev-chip-teal">Prestación</button>
                  <button class="ev-chip ev-chip-red">Anular</button>
                </div>
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
        <div class="ev-range small text-muted">1–3 de 3</div>
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
<div class="modal fade" id="modalAgregarPublicacion" tabindex="-1" aria-labelledby="lblAgregarPublicacion" aria-hidden="true">
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

                <!-- 🖼️ Uploader primero -->
                <div class="col-12">
                  <label class="form-label fw-semibold" style="color:#0F592F;">
                    Fotos • <span id="contadorImagenes">0</span>/<span>10</span>
                    <span class="text-muted"> - Puedes agregar un máximo de 10 fotos.</span>
                  </label>

                  <div id="uploaderAgregar" class="ev-uploader">
                    <!-- input real -->
                    <input type="file" id="inputImagenes" name="imagenes[]" accept="image/*" multiple data-max="10" class="visually-hidden" />

                    <!-- área visual -->
                    <div id="evTiles" class="ev-tiles">
                      <div class="ev-tile ev-tile-add" id="tileAgregar" title="Agregar fotos">
                        <div class="ico"><i class="bi bi-plus-lg"></i></div>
                        <div class="t1">Agregar fotos</div>
                        <div class="t2">o arrastra y suelta</div>
                      </div>
                    </div>

                    <div class="mt-2">
                      <button type="button" id="btnLimpiarImagenes" class="btn btn-sm btn-cancelar">Limpiar imágenes</button>
                    </div>

                    <small class="text-muted d-block mt-2">Hasta 10 imágenes (JPG, PNG o WebP) de máximo 5 MB cada una.</small>
                  </div>
                </div>

                <!-- Campos del producto -->
                <div class="col-12">
                  <label class="form-label">Título</label>
                  <input type="text" id="titulo" name="titulo" class="form-control input-premium" placeholder="Escribe un título claro" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Precio (S/)</label>
                  <input type="number" id="precio" name="precio" class="form-control input-premium" step="0.01" min="0" placeholder="0.00" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Estado</label>
                  <select id="estado" name="estado" class="form-select input-premium" required>
                    <option>Nuevo</option>
                    <option>Usado</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Tipo</label>
                  <select id="comboTipo" name="comboTipo" class="form-select input-premium" required>
                    <option value="">-- Seleccione Tipos --</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Categoría</label>
                  <select id="comboCategoria" name="categoria" class="form-select input-premium" required>
                    <option value="" selected disabled>-- Selecciona un tipo primero --</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Descripción</label>
                  <textarea id="descripcion" name="descripcion" class="form-control input-premium" rows="4" placeholder="Describe el producto con detalle" required></textarea>
                </div>
              </div>
            </section>

            <!-- DERECHA: PREVIEW -->
            <aside class="mpm-right">
              <div class="mpm-preview-wrap">
                <div class="col-lg-12" id="previewMount"></div>
              </div>
            </aside>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="btnGuardarPublicacion" class="btn btn-outline-success btn-guardar">Guardar</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- Scripts únicos de esta vista -->
<script src="<?= BASE_URL ?>public/js/combo_tipo.js"></script>
<script src="<?= BASE_URL ?>public/js/publicacion.js"></script>
