<?php
// views/comunidadVecinoView.php
// Entre Vecinos - Experiencia de Comunidad para vecinos.

declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';

$comunidad = isset($comunidadVecino) && is_array($comunidadVecino)
    ? $comunidadVecino
    : [];

$nombreComunidad = trim((string)($comunidad['nombre_comunidad'] ?? 'Tu comunidad'));
$tipoConjunto = strtolower(trim((string)($comunidad['tipo_conjunto'] ?? '')));
$etiquetaTipo = trim((string)($comunidad['etiqueta_tipo'] ?? 'Comunidad'));

if ($nombreComunidad === '') {
    $nombreComunidad = 'Tu comunidad';
}

$nombreVisible = $nombreComunidad;

if (in_array($tipoConjunto, ['urbanizacion', 'condominio'], true)) {
    $nombreLower = mb_strtolower($nombreComunidad, 'UTF-8');
    $tipoLower = mb_strtolower($etiquetaTipo, 'UTF-8');

    if ($nombreLower !== $tipoLower && !str_starts_with($nombreLower, $tipoLower . ' ')) {
        $nombreVisible = $etiquetaTipo . ' ' . $nombreComunidad;
    }
}

$iconoComunidad = match ($tipoConjunto) {
    'condominio'   => 'bi bi-buildings',
    'urbanizacion' => 'bi bi-houses',
    default        => 'bi bi-house-heart',
};

$baseUrlVista = rtrim(BASE_URL, '/');
$jsPathAbs = __DIR__ . '/js/comunidadVecino.js';
$jsVersion = @filemtime($jsPathAbs) ?: (defined('EV_APP_VER') ? EV_APP_VER : time());
?>

<?php include_once __DIR__ . '/estilos/comunidadVecinoEstilo.php'; ?>

<section
  class="ev-cv-shell fade-in"
  id="evComunidadVecino"
  data-comunidad="<?= htmlspecialchars($nombreVisible, ENT_QUOTES, 'UTF-8') ?>"
  data-tipo-conjunto="<?= htmlspecialchars($tipoConjunto, ENT_QUOTES, 'UTF-8') ?>"
  aria-label="Novedades de mi comunidad"
>
  <header class="ev-cv-hero">
    <div class="ev-cv-hero-copy">
      <span class="ev-cv-kicker">
        <i class="bi bi-people-fill" aria-hidden="true"></i>
        Comunidad
      </span>

      <h1>Novedades de tu comunidad</h1>

      <p>
        Comunicados, noticias y eventos oficiales para mantenerte informado
        sobre lo que sucede cerca de ti.
      </p>
    </div>

    <div class="ev-cv-community-pill" aria-label="Comunidad actual">
      <i class="<?= htmlspecialchars($iconoComunidad, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>

      <div>
        <small>Tu comunidad</small>
        <strong><?= htmlspecialchars($nombreVisible, ENT_QUOTES, 'UTF-8') ?></strong>
      </div>
    </div>
  </header>

  <section class="ev-cv-toolbar" aria-label="Filtros de novedades">
    <div class="ev-cv-tabs" role="tablist" aria-label="Tipo de contenido">
      <button
        type="button"
        class="is-active"
        data-cv-tipo="all"
        role="tab"
        aria-selected="true"
        aria-controls="evCvGrid"
        tabindex="0"
      >
        Todo <span id="evCvCountTodo">0</span>
      </button>

      <button
        type="button"
        data-cv-tipo="comunicado"
        role="tab"
        aria-selected="false"
        aria-controls="evCvGrid"
        tabindex="-1"
      >
        Comunicados <span id="evCvCountComunicados">0</span>
      </button>

      <button
        type="button"
        data-cv-tipo="noticia"
        role="tab"
        aria-selected="false"
        aria-controls="evCvGrid"
        tabindex="-1"
      >
        Noticias <span id="evCvCountNoticias">0</span>
      </button>

      <button
        type="button"
        data-cv-tipo="evento"
        role="tab"
        aria-selected="false"
        aria-controls="evCvGrid"
        tabindex="-1"
      >
        Eventos <span id="evCvCountEventos">0</span>
      </button>
    </div>

    <form id="evCvBuscarForm" class="ev-cv-search" autocomplete="off">
      <i class="bi bi-search" aria-hidden="true"></i>

      <input
        type="search"
        id="evCvBuscar"
        placeholder="Buscar en tu comunidad..."
        aria-label="Buscar publicaciones de la comunidad"
      >

      <button type="submit">Buscar</button>
    </form>
  </section>

  <section
    class="ev-cv-feature"
    id="evCvDestacadaSection"
    hidden
    aria-label="Novedad destacada"
  >
    <div class="ev-cv-section-heading">
      <i class="bi bi-star-fill" aria-hidden="true"></i>
      <h2>Destacado para ti</h2>
    </div>

    <div id="evCvDestacada"></div>
  </section>

  <section
    class="ev-cv-content"
    id="evCvRecientesSection"
    aria-label="Publicaciones recientes"
  >
    <div class="ev-cv-section-head">
      <div class="ev-cv-section-heading">
        <i class="bi bi-newspaper" aria-hidden="true"></i>
        <h2>Publicaciones recientes</h2>
      </div>

      <p id="evCvMeta" aria-live="polite">Cargando novedades...</p>
    </div>

    <div class="ev-cv-grid" id="evCvGrid">
      <div class="ev-cv-skeleton"></div>
      <div class="ev-cv-skeleton"></div>
      <div class="ev-cv-skeleton"></div>
    </div>

    <div class="ev-cv-pager" id="evCvPager" hidden aria-label="Paginación">
      <button type="button" id="evCvAnterior" aria-label="Página anterior">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
      </button>

      <span id="evCvPagina">1 / 1</span>

      <button type="button" id="evCvSiguiente" aria-label="Página siguiente">
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
      </button>
    </div>
  </section>

  <div class="ev-cv-error d-none" id="evCvError" role="alert">
    <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
    No se pudieron cargar las novedades. Intenta nuevamente.
  </div>
</section>

<!-- ============================================================
     MODAL DETALLE DE PUBLICACIÓN - ESTÁNDAR VISUAL EV
============================================================ -->
<div
  class="modal fade ev-cv-modal"
  id="modalDetalleComunidadVecino"
  tabindex="-1"
  aria-labelledby="evCvModalTitle"
  aria-hidden="true"
  data-bs-backdrop="static"
  data-bs-keyboard="false"
>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <article class="modal-content ev-cv-modal-content">

      <header class="ev-cv-modal-head">
        <div class="ev-cv-modal-heading">
          <span class="ev-cv-modal-heading-icon" id="evCvModalIcon" aria-hidden="true">
            <i class="bi bi-megaphone"></i>
          </span>

          <div class="ev-cv-modal-heading-copy">
            <span class="ev-cv-modal-eyebrow">Contenido oficial</span>

            <h2 id="evCvModalTitle">Detalle de publicación</h2>

            <div class="ev-cv-modal-head-meta">
              <span
                class="ev-cv-modal-type ev-cv-modal-type--comunicado"
                id="evCvModalTipo"
              >
                Comunicado
              </span>

              <span class="ev-cv-modal-community">
                <i class="<?= htmlspecialchars($iconoComunidad, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                <?= htmlspecialchars($nombreVisible, ENT_QUOTES, 'UTF-8') ?>
              </span>
            </div>
          </div>
        </div>

        <button
          type="button"
          class="ev-cv-modal-close"
          data-bs-dismiss="modal"
          aria-label="Cerrar publicación"
        >
          <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
      </header>

      <div class="ev-cv-modal-surface">
        <div class="modal-body ev-cv-modal-body">

          <div class="ev-cv-modal-topline">
            <div class="ev-cv-modal-badges">
              <span
                class="ev-cv-modal-priority ev-cv-modal-priority--normal"
                id="evCvModalPrioridad"
              >
                Normal
              </span>

              <span class="ev-cv-modal-featured" id="evCvModalDestacado" hidden>
                <i class="bi bi-star-fill" aria-hidden="true"></i>
                Destacado
              </span>
            </div>

            <time class="ev-cv-modal-date" id="evCvModalFecha">
              <i class="bi bi-calendar3" aria-hidden="true"></i>
              <span>—</span>
            </time>
          </div>

          <div class="ev-cv-modal-media" id="evCvModalMedia" hidden>
            <img
              class="ev-cv-modal-image"
              id="evCvModalImagen"
              src=""
              alt=""
              hidden
            >
          </div>

          <section class="ev-cv-modal-block ev-cv-modal-block--summary">
            <span class="ev-cv-modal-block-label">Resumen</span>
            <p class="ev-cv-modal-summary" id="evCvModalResumen"></p>
          </section>

          <div class="ev-cv-modal-event" id="evCvModalEvento" hidden>
            <i class="bi bi-calendar-event" aria-hidden="true"></i>
            <span id="evCvModalEventoTexto"></span>
          </div>

          <section class="ev-cv-modal-block">
            <span class="ev-cv-modal-block-label">Información</span>
            <div class="ev-cv-modal-text" id="evCvModalContenido"></div>
          </section>

        </div>

        <footer class="ev-cv-modal-footer">
          <span>
            <i class="bi bi-shield-check" aria-hidden="true"></i>
            Contenido oficial de <?= htmlspecialchars($nombreVisible, ENT_QUOTES, 'UTF-8') ?>
          </span>

          <button type="button" data-bs-dismiss="modal">
            Cerrar
          </button>
        </footer>
      </div>

    </article>
  </div>
</div>

<script src="<?= htmlspecialchars($baseUrlVista . '/views/js/comunidadVecino.js?v=' . $jsVersion, ENT_QUOTES, 'UTF-8') ?>"></script>