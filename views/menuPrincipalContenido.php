<?php
// views/menuPrincipalContenido.php
// Dashboard principal del vecino - Entre Vecinos

if (!defined('BASE_URL')) {
  require_once __DIR__ . '/../Config/config.php';
}

$baseUrl = rtrim(BASE_URL, '/');

$nombreUsuarioSafe = 'Vecino(a)';
if (isset($nombreUsuario) && trim((string)$nombreUsuario) !== '') {
  // En MenuPrincipalView.php ya llega escapado.
  $nombreUsuarioSafe = (string)$nombreUsuario;
} elseif (isset($usuario) && is_array($usuario) && trim((string)($usuario['nombre'] ?? '')) !== '') {
  $nombreUsuarioSafe = htmlspecialchars((string)$usuario['nombre'], ENT_QUOTES, 'UTF-8');
}
?>

<div class="container-fluid ev-home-dashboard ev-home-dashboard-v2 fade-in" id="evHomeDashboardV2">

  <!-- HERO -->
  <section class="ev-home-hero" aria-label="Resumen principal de Entre Vecinos">
    <div class="ev-home-hero-copy">
      <div class="ev-home-kicker">Panel principal</div>
      <h1>Hola, <?= $nombreUsuarioSafe ?> <span aria-hidden="true">👋</span></h1>
      <p>
        Este es tu resumen en Entre Vecinos. Revisa tus compras, ventas, calificaciones y novedades de tu comunidad.
      </p>
    </div>

    <div class="ev-home-hero-side" aria-hidden="true">
      <div class="ev-home-hero-art">
        <span class="ev-hero-cloud ev-hero-cloud-1"></span>
        <span class="ev-hero-cloud ev-hero-cloud-2"></span>
        <span class="ev-hero-tree ev-hero-tree-1"></span>
        <span class="ev-hero-tree ev-hero-tree-2"></span>
        <span class="ev-hero-building ev-hero-building-1"></span>
        <span class="ev-hero-building ev-hero-building-2"></span>
        <span class="ev-hero-house ev-hero-house-1"><i></i></span>
        <span class="ev-hero-house ev-hero-house-2"><i></i></span>
        <span class="ev-hero-house ev-hero-house-3"><i></i></span>
        <span class="ev-hero-ground"></span>
      </div>
    </div>
  </section>

  <!-- RESUMEN -->
  <section class="ev-home-summary-grid" aria-label="Resumen operativo">
    <article class="ev-home-summary-card ev-home-summary-green">
      <div class="ev-home-summary-icon"><i class="bi bi-bag-check"></i></div>
      <div class="ev-home-summary-body">
        <span>Compras activas</span>
        <strong id="evDashComprasActivas">0</strong>
        <small id="evDashComprasTexto">Pedidos en proceso</small>
        <button type="button" class="ev-home-link-btn" data-ev-route="/mis-pedidos-comprador">
          Ver mis compras <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </article>

    <article class="ev-home-summary-card ev-home-summary-orange">
      <div class="ev-home-summary-icon"><i class="bi bi-box-seam"></i></div>
      <div class="ev-home-summary-body">
        <span>Ventas pendientes</span>
        <strong id="evDashVentasPendientes">0</strong>
        <small id="evDashVentasTexto">Pedidos por atender</small>
        <button type="button" class="ev-home-link-btn" data-ev-route="/mis-pedidos-vendedor">
          Ver mis ventas <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </article>

    <article class="ev-home-summary-card ev-home-summary-purple">
      <div class="ev-home-summary-icon"><i class="bi bi-star"></i></div>
      <div class="ev-home-summary-body">
        <span>Calificaciones pendientes</span>
        <strong id="evDashCalificacionesPendientes">0</strong>
        <small id="evDashCalificacionesTexto">Opiniones por registrar</small>
        <button type="button" class="ev-home-link-btn" data-ev-route="/mis-pedidos-comprador">
          Calificar ahora <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </article>

    <article class="ev-home-summary-card ev-home-summary-wallet">
      <div class="ev-home-summary-icon"><i class="bi bi-wallet2"></i></div>
      <div class="ev-home-summary-body">
        <span>Saldo en EV</span>
        <strong id="evDashSaldoBilletera">S/ 0.00</strong>
        <small>Disponible en tu billetera</small>
        <button type="button" class="ev-home-link-btn" data-ev-route="/billetera">
          Ver billetera <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </article>
  </section>

  <section class="ev-home-main-grid" aria-label="Actividad y accesos rápidos">

    <!-- ACTIVIDAD -->
    <article class="ev-home-panel ev-home-activity-panel">
      <header class="ev-home-panel-head">
        <div>
          <i class="bi bi-clock-history"></i>
          <h2>Actividad reciente</h2>
        </div>
        <button type="button" class="ev-home-panel-action" data-ev-route="/notificaciones-residencia">
          Ver todas <i class="bi bi-chevron-right"></i>
        </button>
      </header>

      <div id="evDashActividadLista" class="ev-home-activity-list">
        <div class="ev-home-skeleton-line"></div>
        <div class="ev-home-skeleton-line"></div>
        <div class="ev-home-skeleton-line"></div>
      </div>
    </article>

    <!-- ACCIONES -->
    <article class="ev-home-panel ev-home-actions-panel">
      <header class="ev-home-panel-head">
        <div>
          <i class="bi bi-lightning-charge"></i>
          <h2>Acciones rápidas</h2>
        </div>
      </header>

      <div class="ev-home-actions-grid">
        <button type="button" class="ev-home-action-card" data-ev-route="/marketplace">
          <span><i class="bi bi-cart3"></i></span>
          <strong>Ir al Marketplace</strong>
          <small>Explora productos y servicios</small>
          <i class="bi bi-chevron-right ev-home-action-chevron"></i>
        </button>

        <button type="button" class="ev-home-action-card" data-ev-route="/publicacion">
          <span><i class="bi bi-plus-circle"></i></span>
          <strong>Crear publicación</strong>
          <small>Publica un producto o servicio</small>
          <i class="bi bi-chevron-right ev-home-action-chevron"></i>
        </button>

        <button type="button" class="ev-home-action-card" data-ev-route="/mis-pedidos-comprador">
          <span><i class="bi bi-bag"></i></span>
          <strong>Mis compras</strong>
          <small>Ver mis pedidos</small>
          <i class="bi bi-chevron-right ev-home-action-chevron"></i>
        </button>

        <button type="button" class="ev-home-action-card" data-ev-route="/mis-pedidos-vendedor">
          <span><i class="bi bi-box-seam"></i></span>
          <strong>Mis ventas</strong>
          <small>Ver pedidos recibidos</small>
          <i class="bi bi-chevron-right ev-home-action-chevron"></i>
        </button>

        <button type="button" class="ev-home-action-card" data-ev-route="/billetera">
          <span><i class="bi bi-wallet2"></i></span>
          <strong>Billetera</strong>
          <small>Saldo y movimientos</small>
          <i class="bi bi-chevron-right ev-home-action-chevron"></i>
        </button>

        <button type="button" class="ev-home-action-card" data-ev-action="comunidad-proximamente">
          <span><i class="bi bi-people"></i></span>
          <strong>Comunidad</strong>
          <small>Noticias y eventos</small>
          <i class="bi bi-chevron-right ev-home-action-chevron"></i>
        </button>
      </div>
    </article>
  </section>

  <!-- COMUNIDAD -->
  <section class="ev-home-panel ev-home-community-panel" aria-label="Novedades de tu comunidad">
    <header class="ev-home-panel-head">
      <div>
        <i class="bi bi-megaphone"></i>
        <h2>Novedades de tu comunidad</h2>
      </div>
      <button type="button" class="ev-home-panel-action" data-ev-action="comunidad-proximamente">
        Ver más <i class="bi bi-chevron-right"></i>
      </button>
    </header>

    <div id="evDashComunidadLista" class="ev-home-community-strip">
      <article class="ev-home-community-empty">
        <div class="ev-home-empty-icon"><i class="bi bi-newspaper"></i></div>
        <div>
          <strong>Módulo Comunidad en preparación</strong>
          <p>En la siguiente fase aquí verás comunicados, eventos y noticias de tu condominio o urbanización.</p>
        </div>
      </article>
    </div>
  </section>

  <!-- PUBLICACIONES -->
  <section class="ev-home-panel ev-home-publications-panel" aria-label="Publicaciones recientes">
    <header class="ev-home-panel-head">
      <div>
        <i class="bi bi-tags"></i>
        <h2>Publicaciones recientes</h2>
      </div>
      <button type="button" class="ev-home-panel-action" data-ev-route="/marketplace">
        Ver todas <i class="bi bi-chevron-right"></i>
      </button>
    </header>

    <div id="evDashPublicacionesLista" class="ev-home-publications-grid">
      <div class="ev-home-skeleton-card"></div>
      <div class="ev-home-skeleton-card"></div>
      <div class="ev-home-skeleton-card"></div>
    </div>
  </section>

  <div id="evDashError" class="ev-home-error d-none">
    No se pudo cargar el resumen principal. Puedes seguir usando el menú lateral normalmente.
  </div>
</div>
