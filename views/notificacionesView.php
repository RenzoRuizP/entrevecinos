<?php
// views/notificacionesView.php
require_once __DIR__ . '/../Config/config.php';
$baseUrl = rtrim(BASE_URL, '/');
?>

<?php include_once __DIR__ . '/estilos/notificacionesEstilo.php'; ?>
<script>
  window.BASE_URL = window.BASE_URL || <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>

<div class="container-fluid ev-notificaciones-page fade-in">
  <section class="ev-notificaciones-hero mb-4">
    <div class="ev-notificaciones-hero-content">
      <div class="ev-notificaciones-title-wrap">
        <div class="ev-notificaciones-title-icon" aria-hidden="true">
          <i class="bi bi-bell-fill"></i>
        </div>
        <div>
          <div class="ev-notificaciones-kicker">CENTRO DE NOTIFICACIONES</div>
          <h2 class="ev-notificaciones-title">Todas tus novedades de EV</h2>
          <p class="ev-notificaciones-subtitle">
            Revisa notificaciones de servicios, pedidos, residencia, soporte y comunidad. Abre cada aviso para ir directamente al módulo relacionado.
          </p>
        </div>
      </div>

      <div class="ev-notificaciones-summary-grid" aria-label="Resumen de notificaciones">
        <article class="ev-notificaciones-summary-card">
          <span>No leídas</span>
          <strong id="evNotifCentroCountUnread">0</strong>
        </article>
        <article class="ev-notificaciones-summary-card">
          <span>Total filtrado</span>
          <strong id="evNotifCentroCountTotal">0</strong>
        </article>
        <article class="ev-notificaciones-summary-card">
          <span>Página</span>
          <strong id="evNotifCentroPageLabel">1</strong>
        </article>
      </div>
    </div>
  </section>

  <section class="ev-notificaciones-panel">
    <header class="ev-notificaciones-panel-head">
      <div>
        <h5>Bandeja de notificaciones</h5>
        <p>Usa los filtros para revisar novedades pendientes o el historial. Las acciones importantes te llevarán al servicio, pedido o módulo correspondiente.</p>
      </div>

      <div class="ev-notificaciones-actions">
        <button type="button" class="btn ev-notificaciones-btn-outline" id="btnNotifCentroRefresh">
          <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
        </button>
        <button type="button" class="btn ev-notificaciones-btn-primary" id="btnNotifCentroMarkAll">
          <i class="bi bi-check2-all me-1"></i>Marcar todas como leídas
        </button>
      </div>
    </header>

    <div class="ev-notificaciones-toolbar">
      <div class="ev-notificaciones-field">
        <label for="evNotifCentroEstado">Estado</label>
        <select id="evNotifCentroEstado" class="form-select">
          <option value="all">Todas</option>
          <option value="no_leida" selected>No leídas</option>
          <option value="leida">Leídas</option>
        </select>
      </div>

      <div class="ev-notificaciones-field">
        <label for="evNotifCentroCategoria">Categoría</label>
        <select id="evNotifCentroCategoria" class="form-select">
          <option value="all" selected>Todas</option>
          <option value="servicio">Servicios</option>
          <option value="pedidos">Pedidos</option>
          <option value="residencia">Residencia</option>
          <option value="soporte">Soporte</option>
          <option value="comunidad">Comunidad</option>
        </select>
      </div>

      <div class="ev-notificaciones-field ev-notificaciones-field-small">
        <label for="evNotifCentroSize">Mostrar</label>
        <select id="evNotifCentroSize" class="form-select">
          <option value="10" selected>10</option>
          <option value="20">20</option>
          <option value="30">30</option>
          <option value="50">50</option>
        </select>
      </div>
    </div>

    <div class="ev-notificaciones-body">
      <div id="evNotifCentroError" class="ev-notificaciones-alert d-none">
        No se pudieron cargar tus notificaciones.
      </div>

      <div id="evNotifCentroList" class="ev-notificaciones-list" aria-live="polite">
        <div class="ev-notificaciones-loading">
          <span class="ev-notificaciones-spinner" aria-hidden="true"></span>
          <span>Cargando notificaciones...</span>
        </div>
      </div>
    </div>

    <footer class="ev-notificaciones-footer">
      <div id="evNotifCentroFooterInfo">Mostrando 0 de 0</div>
      <div class="ev-notificaciones-pager">
        <button type="button" class="btn ev-notificaciones-btn-page" id="btnNotifCentroPrev" aria-label="Página anterior">
          <i class="bi bi-chevron-left"></i>
        </button>
        <span id="evNotifCentroPagerInfo">1 / 1</span>
        <button type="button" class="btn ev-notificaciones-btn-page" id="btnNotifCentroNext" aria-label="Página siguiente">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </footer>
  </section>
</div>

<script src="<?= $baseUrl ?>/views/js/notificaciones.js?v=<?= @filemtime(__DIR__ . '/js/notificaciones.js') ?: time(); ?>"></script>
