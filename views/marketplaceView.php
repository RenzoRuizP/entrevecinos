<?php
// views/marketplaceView.php
require_once __DIR__ . '/../Config/config.php';

$esAdminMarketplace = !empty($esAdminMarketplace);
$codigoConjunto = (int)($datosUsuario['conjunto_codigo'] ?? 0);
$conjuntoTipo = strtolower(trim((string)($datosUsuario['conjunto_tipo'] ?? '')));
$condominioNombre = trim((string)($datosUsuario['conjunto_nombre'] ?? ($datosUsuario['condominio'] ?? '')));

if ($esAdminMarketplace && $codigoConjunto <= 0) {
    $condominioNombre = 'Sin comunidad seleccionada';
    $labelConjunto = 'Comunidad por consultar';
    $conjuntoTipo = '';
} else {
    $condominioNombre = $condominioNombre !== '' ? $condominioNombre : 'Tu comunidad';
    $labelConjunto = $esAdminMarketplace
        ? 'Comunidad consultada'
        : (($conjuntoTipo === 'urbanizacion') ? 'Urbanización actual' : 'Condominio actual');
}
?>

<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
  window.EV_CONDOMINIO_NOMBRE = "<?= htmlspecialchars($condominioNombre, ENT_QUOTES, 'UTF-8'); ?>";
  window.EV_CONJUNTO_LABEL = "<?= htmlspecialchars($labelConjunto, ENT_QUOTES, 'UTF-8'); ?>";
  window.EV_MARKETPLACE_ADMIN = <?= !empty($esAdminMarketplace) ? 'true' : 'false' ?>;
  window.EV_MARKETPLACE_TIPO = "<?= htmlspecialchars($conjuntoTipo, ENT_QUOTES, 'UTF-8') ?>";
  window.EV_MARKETPLACE_CODIGO = <?= $codigoConjunto ?>;
</script>

<?php include_once __DIR__ . '/estilos/marketplaceEstilo.php'; ?>

<div class="ev-mp-wrapper fade-in">
  <div class="container-fluid px-2 px-lg-3 py-2 py-lg-3">
    <div class="ev-mp-content">
      <div class="card ev-mp-header mb-3">
        <div class="card-body">
          <div class="ev-mp-hero-row">
            <div class="ev-mp-title-zone">
              <div class="ev-mp-title-icon" aria-hidden="true"><i class="bi bi-shop-window"></i></div>
              <div>
                <div class="ev-mp-kicker">Marketplace vecinal</div>
                <h1 class="ev-mp-title mb-1">Marketplace</h1>
                <p class="ev-mp-subtitle mb-0"><?= !empty($esAdminMarketplace) ? 'Consulta productos y servicios publicados en cada comunidad de Entre Vecinos.' : 'Compra y vende productos y servicios entre vecinos de tu comunidad.' ?></p>
              </div>
            </div>
            <div class="ev-mp-condominio" title="Conjunto actual">
              <div class="ev-mp-condominio-icon"><i class="bi bi-buildings"></i></div>
              <div class="ev-mp-condominio-text">
                <span class="ev-mp-condominio-label" id="mp_conjunto_label"><?= htmlspecialchars($labelConjunto, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="ev-mp-condominio-name" id="mp_conjunto_nombre"><?= htmlspecialchars($condominioNombre, ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            </div>
          </div>
          <?php if (!empty($esAdminMarketplace)): ?>
          <div class="ev-mp-admin-community">
            <div class="ev-mp-admin-community-copy"><span><i class="bi bi-buildings"></i></span><div><strong>Comunidad a consultar</strong><small>Elige el condominio o urbanización cuyo marketplace deseas revisar.</small></div></div>
            <label><span>Condominio o urbanización</span><select id="mp_comunidad_admin" class="ev-searchable-select" data-search-placeholder="Buscar condominio o urbanización">
              <option value="" <?= $codigoConjunto <= 0 ? 'selected' : '' ?>>Buscar condominio o urbanización</option>
              <?php foreach (($comunidadesMarketplaceAdmin ?? []) as $item): ?>
                <option value="<?= htmlspecialchars((string)$item['tipo_conjunto'].'|'.(string)$item['codigo_comunidad'], ENT_QUOTES, 'UTF-8') ?>" <?= ($codigoConjunto > 0 && (string)$item['tipo_conjunto']===$conjuntoTipo && (int)$item['codigo_comunidad']===$codigoConjunto)?'selected':'' ?>><?= htmlspecialchars(($item['tipo_conjunto']==='urbanizacion'?'Urbanización':'Condominio').' · '.$item['nombre'].' · '.($item['nombre_distrito']??''), ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select></label>
          </div>
          <?php endif; ?>

          <div class="ev-mp-toolbar mt-3">
            <div class="ev-mp-search-input-wrapper"><i class="bi bi-search"></i><input type="text" id="mp_busqueda" class="form-control ev-mp-search-input" placeholder="Busca por título o descripción..." autocomplete="off"></div>
            <div class="ev-mp-toolbar-controls">
              <div class="ev-mp-field ev-mp-sort-wrapper"><span class="ev-mp-sort-label">Ordenar</span><select id="mp_orden" class="form-select ev-mp-native-select" aria-label="Ordenar publicaciones" tabindex="-1"><option value="recientes">Más recientes</option><option value="precio_menor">Precio: menor a mayor</option><option value="precio_mayor">Precio: mayor a menor</option></select><div class="ev-mp-select" data-ev-select="mp_orden"><button type="button" class="ev-mp-select-trigger" aria-haspopup="listbox" aria-expanded="false"><span class="ev-mp-select-value">Más recientes</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button><div class="ev-mp-select-menu" role="listbox" aria-label="Opciones de orden"></div></div></div>
              <div class="ev-mp-field ev-mp-cat-producto"><span id="mp_categoria_label" class="ev-mp-scope-label">Categoría</span><select id="mp_categoria_producto" class="form-select ev-mp-native-select" aria-label="Categoría de publicaciones" tabindex="-1"><option value="0">Todas las categorías</option></select><div class="ev-mp-select ev-mp-select-category" data-ev-select="mp_categoria_producto"><button type="button" class="ev-mp-select-trigger" aria-haspopup="listbox" aria-expanded="false"><span class="ev-mp-select-value">Todas las categorías</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button><div class="ev-mp-select-menu" role="listbox" aria-label="Categorías disponibles"></div></div></div>
            </div>
          </div>
          <div class="ev-mp-filters-advanced"><div class="ev-mp-scope"><span class="ev-mp-scope-label">Buscar en:</span><div class="ev-mp-seg" role="group" aria-label="Filtrar publicaciones por tipo"><button type="button" class="ev-mp-seg-btn active" data-scope="todos">Todos</button><button type="button" class="ev-mp-seg-btn" data-scope="servicios">Servicios</button><button type="button" class="ev-mp-seg-btn" data-scope="productos">Productos</button></div></div><div id="mp_resumen_resultados" class="ev-mp-resumen"><?= $esAdminMarketplace && $codigoConjunto <= 0 ? 'Selecciona una comunidad para consultar su marketplace.' : 'Mostrando 0 resultados en ' . htmlspecialchars($condominioNombre, ENT_QUOTES, 'UTF-8'); ?></div></div>
        </div>
      </div>
      <div id="mp_empty_state" class="ev-mp-global-empty">No encontramos publicaciones con los filtros actuales.</div>
      <div id="mp_grid_publicaciones" class="ev-mp-split">
        <section class="ev-mp-section" aria-labelledby="ev-mp-servicios-title"><div class="ev-mp-section-head"><div class="ev-mp-section-title-wrap"><div class="ev-mp-section-icon ev-mp-section-icon-serv" aria-hidden="true"><i class="bi bi-stars"></i></div><div><div class="ev-mp-section-kicker">Sección</div><h2 id="ev-mp-servicios-title" class="ev-mp-section-title">Servicios</h2><div class="ev-mp-section-sub">Encuentra servicios ofrecidos por vecinos.</div></div></div><div class="ev-mp-section-pill"><span id="mp_count_servicios">0</span><small>servicios</small></div></div><div id="mp_empty_servicios" class="ev-mp-section-empty"><i class="bi bi-stars"></i><span><?= $esAdminMarketplace && $codigoConjunto <= 0 ? 'Selecciona una comunidad para consultar sus servicios.' : 'Aún no hay servicios publicados en tu conjunto.' ?></span></div><div id="mp_grid_servicios" class="ev-mp-grid"></div></section>
        <section class="ev-mp-section" aria-labelledby="ev-mp-productos-title"><div class="ev-mp-section-head"><div class="ev-mp-section-title-wrap"><div class="ev-mp-section-icon ev-mp-section-icon-prod" aria-hidden="true"><i class="bi bi-bag-check"></i></div><div><div class="ev-mp-section-kicker">Sección</div><h2 id="ev-mp-productos-title" class="ev-mp-section-title">Productos</h2><div class="ev-mp-section-sub">Compra productos publicados por vecinos.</div></div></div><div class="ev-mp-section-pill"><span id="mp_count_productos">0</span><small>productos</small></div></div><div id="mp_empty_productos" class="ev-mp-section-empty"><i class="bi bi-bag-check"></i><span><?= $esAdminMarketplace && $codigoConjunto <= 0 ? 'Selecciona una comunidad para consultar sus productos.' : 'Aún no hay productos publicados en tu conjunto.' ?></span></div><div id="mp_grid_productos" class="ev-mp-grid"></div></section>
      </div>
    </div>
  </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal fade" id="mp_modal_detalle" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"><div class="modal-dialog ev-mp-modal-dialog modal-dialog-centered"><div class="modal-content ev-mp-modal-content"><div class="modal-header ev-mp-modal-header"><h5 class="modal-title d-flex align-items-center gap-2"><i class="bi bi-image"></i><span>Detalle de publicación</span></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body ev-mp-modal-body"><div class="ev-mp-preview-card"><div class="ev-mp-modal-media"><div class="ev-mp-modal-media-inner"><img id="mp_modal_img_principal" src="" alt="Imagen principal de la publicación"></div></div><div id="mp_modal_thumbs" class="ev-mp-modal-thumbs"></div><div class="d-flex flex-wrap gap-2 mb-2"><span id="mp_modal_tipo" class="badge rounded-pill bg-success-subtle text-success fw-semibold"></span><span id="mp_modal_categoria" class="badge rounded-pill bg-secondary-subtle text-secondary fw-semibold"></span></div><h5 id="mp_modal_titulo_txt" class="ev-mp-modal-title mt-2 mb-1"></h5><div id="mp_modal_precio" class="ev-mp-modal-price mb-2"></div><p id="mp_modal_descripcion" class="ev-mp-modal-desc mb-0"></p></div></div><div class="modal-footer ev-mp-modal-footer d-flex justify-content-end gap-2 flex-wrap"><button type="button" class="btn-ev-neutral" data-bs-dismiss="modal">Cerrar</button><button type="button" id="btnPedirAhoraDetalle" class="btn-ev-primary">Pedir ahora</button></div></div></div></div>

<!-- MODAL SOLICITUD DE PEDIDO (sin cambios de punto 10) -->
<div class="modal fade" id="mp_modal_solicitud" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"><div class="modal-dialog ev-mp-modal-dialog modal-dialog-centered"><div class="modal-content ev-mp-modal-content"><div class="modal-header ev-mp-modal-header"><h5 class="modal-title d-flex align-items-center gap-2"><i class="bi bi-bag-plus"></i><span>Solicitud de pedido</span></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><form id="mp_form_solicitud_pedido"><div class="modal-body ev-mp-modal-body"><div class="ev-mp-preview-card"><input type="hidden" id="mp_sp_codigo_producto"><input type="hidden" id="mp_sp_precio_unitario"><input type="hidden" id="mp_sp_requiere_preparacion"><div class="row g-3"><div class="col-12"><label class="form-label fw-semibold">Nombre del producto</label><input type="text" id="mp_sp_nombre_producto" class="form-control" readonly></div><div class="col-12 col-md-6"><label class="form-label fw-semibold">Cantidad</label><input type="number" id="mp_sp_cantidad" class="form-control" min="1" step="1" value="1"></div><div class="col-12 col-md-6"><label class="form-label fw-semibold">Precio total</label><input type="text" id="mp_sp_total" class="form-control" readonly></div><div class="col-12"><label class="form-label fw-semibold">Entrega</label><select id="mp_sp_tipo_entrega" class="form-select"><option value="inmediata">Inmediata</option><option value="programada">Programada</option></select></div><div class="col-12 d-none" id="mp_sp_wrap_programada"><label class="form-label fw-semibold">Fecha y hora programada</label><input type="datetime-local" id="mp_sp_fecha_programada" class="form-control"><div class="form-text">Puedes programar la entrega hasta 2 días después del registro de la solicitud.</div></div><div class="col-12"><label class="form-label fw-semibold">Dirección</label><textarea id="mp_sp_direccion" class="form-control" rows="2" placeholder="Ej. Torre B, Dpto. 402 / área común / calle y número"></textarea></div><div class="col-12"><label class="form-label fw-semibold">Mensaje al vendedor</label><textarea id="mp_sp_mensaje" class="form-control" rows="3" placeholder="Escribe un mensaje breve para el vendedor."></textarea></div></div></div></div><div class="modal-footer ev-mp-modal-footer d-flex justify-content-end gap-2 flex-wrap"><button type="button" class="btn-ev-neutral" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn-ev-primary">Enviar</button></div></form></div></div></div>

<!-- MODAL SOLICITUD DE SERVICIO — COTIZACIÓN PRIVADA -->
<div class="modal fade" id="mp_modal_solicitud_servicio" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog ev-mp-modal-dialog modal-dialog-centered">
    <div class="modal-content ev-mp-modal-content ev-mp-quote-request-modal">
      <div class="modal-header ev-mp-modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i class="bi bi-chat-square-quote"></i>
          <span>Solicitar cotización</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="mp_form_solicitud_servicio" novalidate>
        <div class="modal-body ev-mp-modal-body">
          <div class="ev-mp-preview-card ev-mp-service-request-card">
            <input type="hidden" id="mp_ss_codigo_producto">

            <section class="ev-mp-quote-request-hero" aria-label="Resumen de solicitud">
              <span class="ev-mp-quote-request-hero-icon"><i class="bi bi-shield-lock"></i></span>
              <div>
                <strong>Negociación privada dentro de EV</strong>
                <p>Cuéntale al vecino qué necesitas. Podrán conversar, intercambiar referencias y acordar una cotización final dentro del sistema.</p>
              </div>
            </section>

            <section class="ev-mp-quote-service-summary" aria-label="Servicio seleccionado">
              <div>
                <span>Servicio seleccionado</span>
                <strong id="mp_ss_nombre_servicio">—</strong>
              </div>
              <div class="ev-mp-quote-price-ref">
                <span>Precio referencial</span>
                <strong id="mp_ss_precio_referencial">—</strong>
              </div>
            </section>

            <div class="row g-3 mt-0">
              <div class="col-12">
                <label class="form-label fw-semibold" for="mp_ss_mensaje_solicitante">¿Qué necesitas? <span class="text-danger">*</span></label>
                <textarea id="mp_ss_mensaje_solicitante" class="form-control" rows="5" maxlength="1500" placeholder="Describe el servicio que necesitas y los detalles que ayuden al proveedor a cotizarte mejor." required></textarea>
                <div class="form-text">Luego podrás compartir ubicación, fecha, horario y cualquier otro detalle dentro de la conversación privada.</div>
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold" for="mp_ss_adjuntos">Imágenes de referencia <span class="ev-mp-optional">(opcional)</span></label>
                <input type="file" id="mp_ss_adjuntos" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                <div class="form-text">JPG, PNG o WEBP. Máximo 5 imágenes de 5 MB cada una.</div>
                <div id="mp_ss_adjuntos_preview" class="ev-mp-service-file-list" aria-live="polite"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer ev-mp-modal-footer d-flex justify-content-end gap-2 flex-wrap">
          <button type="button" class="btn-ev-neutral" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i>Cancelar</button>
          <button type="submit" class="btn-ev-primary">
            <i class="bi bi-send"></i>
            Enviar solicitud
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
