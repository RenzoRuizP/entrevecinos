<?php
// views/marketplaceView.php
require_once __DIR__ . '/../Config/config.php';

$condominioNombre = $datosUsuario['condominio'] ?? 'tu condominio';
?>

<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
  window.EV_CONDOMINIO_NOMBRE = "<?= htmlspecialchars($condominioNombre, ENT_QUOTES, 'UTF-8'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/marketplaceEstilo.php'; ?>

<div class="ev-mp-wrapper fade-in">
  <div class="container-fluid px-2 px-lg-3 py-2 py-lg-3">

    <div class="ev-mp-content">

      <div class="card ev-mp-header mb-3">
        <div class="card-body">

          <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
              <h1 class="ev-mp-title mb-1">Marketplace</h1>
              <p class="ev-mp-subtitle mb-0">
                Compra y vende productos y servicios entre vecinos de tu condominio.
              </p>
            </div>

            <div class="ev-mp-condominio">
              <div class="ev-mp-condominio-icon">
                <i class="bi bi-buildings"></i>
              </div>
              <div class="ev-mp-condominio-text">
                <span class="ev-mp-condominio-label">Condominio actual</span>
                <span class="ev-mp-condominio-name">
                  <?= htmlspecialchars($condominioNombre, ENT_QUOTES, 'UTF-8'); ?>
                </span>
              </div>
            </div>
          </div>

          <div class="ev-mp-search-row mt-3">
            <div class="ev-mp-search-input-wrapper">
              <i class="bi bi-search"></i>
              <input
                type="text"
                id="mp_busqueda"
                class="form-control ev-mp-search-input"
                placeholder="Busca por título o descripción..."
                autocomplete="off"
              >
            </div>

            <div class="ev-mp-search-actions">
              <div class="ev-mp-sort-wrapper">
                <span class="ev-mp-sort-label">Ordenar por:</span>
                <select id="mp_orden" class="form-select ev-mp-sort-select">
                  <option value="recientes">Más recientes</option>
                  <option value="precio_menor">Precio: menor a mayor</option>
                  <option value="precio_mayor">Precio: mayor a menor</option>
                </select>
              </div>
            </div>
          </div>

          <!-- ✅ NUEVO: scope + categoría productos -->
          <div class="ev-mp-filters-advanced">
            <div class="ev-mp-scope">
              <span class="ev-mp-scope-label">Buscar en:</span>
              <div class="ev-mp-seg">
                <button type="button" class="ev-mp-seg-btn active" data-scope="todos">Todos</button>
                <button type="button" class="ev-mp-seg-btn" data-scope="servicios">Servicios</button>
                <button type="button" class="ev-mp-seg-btn" data-scope="productos">Productos</button>
              </div>
            </div>

            <div class="ev-mp-cat-producto">
              <span class="ev-mp-scope-label">Categoría (Productos):</span>
              <select id="mp_categoria_producto" class="form-select ev-mp-cat-select">
                <option value="0">Todas las categorías</option>
              </select>
            </div>
          </div>

          <!-- Compatibilidad visual (opcional) -->
          <div class="ev-mp-chips d-none">
            <button type="button" class="ev-mp-chip active" data-filtro="todos">Todos</button>
            <button type="button" class="ev-mp-chip" data-filtro="recomendados">Recomendados</button>
            <button type="button" class="ev-mp-chip" data-filtro="productos">Productos</button>
            <button type="button" class="ev-mp-chip" data-filtro="servicios">Servicios</button>
          </div>

          <div id="mp_resumen_resultados" class="ev-mp-resumen">
            Mostrando 0 resultados en <?= htmlspecialchars($condominioNombre, ENT_QUOTES, 'UTF-8'); ?>
          </div>

        </div>
      </div>

      <div id="mp_empty_state">
        No encontramos publicaciones con los filtros actuales.
      </div>

      <!-- Wrapper -->
      <div id="mp_grid_publicaciones" class="ev-mp-split">

        <!-- ==========================
             SECCIÓN 1: SERVICIOS
        =========================== -->
        <div class="ev-mp-section">
          <div class="ev-mp-section-head">
            <div>
              <div class="ev-mp-section-kicker">Sección</div>
              <h2 class="ev-mp-section-title"><i class="bi bi-stars"></i> Servicios</h2>
              <div class="ev-mp-section-sub">Encuentra servicios ofrecidos por vecinos.</div>
            </div>
            <div class="ev-mp-section-pill" id="mp_count_servicios">0</div>
          </div>

          <div id="mp_grid_servicios" class="ev-mp-grid"></div>
        </div>

        <!-- ==========================
             SECCIÓN 2: PRODUCTOS
        =========================== -->
        <div class="ev-mp-section">
          <div class="ev-mp-section-head">
            <div>
              <div class="ev-mp-section-kicker">Sección</div>
              <h2 class="ev-mp-section-title"><i class="bi bi-bag-check"></i> Productos</h2>
              <div class="ev-mp-section-sub">Compra productos publicados por vecinos.</div>
            </div>
            <div class="ev-mp-section-pill" id="mp_count_productos">0</div>
          </div>

          <div id="mp_grid_productos" class="ev-mp-grid"></div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- ==========================
     MODAL DETALLE (se mantiene)
========================== -->
<div class="modal fade" id="mp_modal_detalle" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog ev-mp-modal-dialog modal-dialog-centered">
    <div class="modal-content ev-mp-modal-content">

      <div class="modal-header ev-mp-modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i class="bi bi-image"></i>
          <span>Detalle de publicación</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body ev-mp-modal-body">
        <div class="ev-mp-preview-card">
          <div class="ev-mp-modal-media">
            <div class="ev-mp-modal-media-inner">
              <img id="mp_modal_img_principal" src="" alt="Imagen principal de la publicación">
            </div>
          </div>

          <div id="mp_modal_thumbs" class="ev-mp-modal-thumbs"></div>

          <div class="d-flex flex-wrap gap-2 mb-2">
            <span id="mp_modal_tipo" class="badge rounded-pill bg-success-subtle text-success fw-semibold"></span>
            <span id="mp_modal_categoria" class="badge rounded-pill bg-secondary-subtle text-secondary fw-semibold"></span>
          </div>

          <h5 id="mp_modal_titulo_txt" class="ev-mp-modal-title mt-2 mb-1"></h5>
          <div id="mp_modal_precio" class="ev-mp-modal-price mb-2"></div>
          <p id="mp_modal_descripcion" class="ev-mp-modal-desc mb-0"></p>
        </div>
      </div>

      <div class="modal-footer ev-mp-modal-footer d-flex justify-content-end gap-2 flex-wrap">
        <button type="button" class="btn-ev-neutral" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnPedirAhoraDetalle" class="btn-ev-primary">Pedir ahora</button>
      </div>

    </div>
  </div>
</div>
