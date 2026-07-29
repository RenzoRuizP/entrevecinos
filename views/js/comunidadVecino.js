// views/js/comunidadVecino.js
// Entre Vecinos - Novedades oficiales visibles para el vecino.
// Versión limpia EV:
// - Soporta múltiples destacados en grilla.
// - Separa destacadas de recientes para evitar duplicidad.
// - Unifica CTA: "Leer publicación".
// - Mantiene enfoque desde Dashboard sin etiqueta temporal confusa.
// - Conserva modal detalle estable y botón "Ver documento completo" centrado.

(function () {
  'use strict';

  const BASE = String(window.EV?.baseUrl ?? window.BASE_URL ?? window.EV_BASE_URL ?? '').replace(/\/+$/, '');


  const STORAGE_ID = 'ev_comunidad_publicacion_seleccionada';
  const STORAGE_AT = 'ev_comunidad_publicacion_seleccionada_at';
  const HIGHLIGHT_TTL_MS = 10 * 60 * 1000;

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  function escapeHtml(value) {
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function truncar(value, max) {
    const texto = String(value || '').replace(/\s+/g, ' ').trim();

    return texto.length > max
      ? `${texto.slice(0, max - 3).trim()}...`
      : texto;
  }

  function tipoSeguro(tipo) {
    const valor = String(tipo || '').trim().toLowerCase();

    return ['comunicado', 'noticia', 'evento'].includes(valor)
      ? valor
      : 'comunicado';
  }

  function prioridadSegura(prioridad) {
    const valor = String(prioridad || '').trim().toLowerCase();

    return ['normal', 'importante', 'urgente'].includes(valor)
      ? valor
      : 'normal';
  }

  function tipoLabel(tipo) {
    return ({
      comunicado: 'Comunicado',
      noticia: 'Noticia',
      evento: 'Evento'
    })[tipoSeguro(tipo)];
  }

  function tipoIcono(tipo) {
    return ({
      comunicado: 'bi-megaphone',
      noticia: 'bi-newspaper',
      evento: 'bi-calendar-event'
    })[tipoSeguro(tipo)];
  }

  function prioridadLabel(prioridad) {
    return ({
      normal: 'Normal',
      importante: 'Importante',
      urgente: 'Urgente'
    })[prioridadSegura(prioridad)];
  }

  function formatFecha(valor) {
    if (!valor) {
      return '';
    }

    const fecha = new Date(String(valor).replace(' ', 'T'));

    if (Number.isNaN(fecha.getTime())) {
      return String(valor);
    }

    return new Intl.DateTimeFormat('es-PE', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    }).format(fecha);
  }

  function formatFechaHora(valor) {
    if (!valor) {
      return '';
    }

    const fecha = new Date(String(valor).replace(' ', 'T'));

    if (Number.isNaN(fecha.getTime())) {
      return String(valor);
    }

    return new Intl.DateTimeFormat('es-PE', {
      dateStyle: 'medium',
      timeStyle: 'short'
    }).format(fecha);
  }

  function imagenUrl(ruta) {
    const valor = String(ruta || '').trim();

    if (!valor) {
      return '';
    }

    if (/^https?:\/\//i.test(valor) || valor.startsWith('/')) {
      return valor;
    }

    return `${BASE}/${valor.replace(/^\/+/, '')}`;
  }

  function imagenHtml(item, feature = false) {
    const url = imagenUrl(item.imagen_portada);

    if (url) {
      return `
        <img
          src="${escapeHtml(url)}"
          alt="${escapeHtml(item.titulo || 'Imagen de publicación')}"
          loading="lazy"
        >
      `;
    }

    return `
      <div class="ev-cv-no-img">
        <i class="bi ${tipoIcono(item.tipo_publicacion)}"></i>
        <span>${feature ? 'Información oficial' : tipoLabel(item.tipo_publicacion)}</span>
      </div>
    `;
  }

  async function requestJson(url) {
    const response = await fetch(url, {
      credentials: 'include',
      cache: 'no-store',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.ok === false) {
      const error = new Error(data.mensaje || 'No se pudo completar la operación.');
      error.status = response.status;
      error.payload = data;
      throw error;
    }

    return data;
  }

  function esDestacada(item) {
    return Number(item?.destacado_dashboard || 0) === 1;
  }

  function renderBadge(item) {
    const tipo = tipoSeguro(item.tipo_publicacion);
    const prioridad = prioridadSegura(item.prioridad);

    return `
      <span class="ev-cv-badge">
        <i class="bi ${tipoIcono(tipo)}"></i>
        ${escapeHtml(tipoLabel(tipo))}
      </span>

      <span class="ev-cv-badge ev-cv-badge--${escapeHtml(prioridad)}">
        ${escapeHtml(prioridadLabel(prioridad))}
      </span>
    `;
  }

  function renderDestacada(item) {
    const id = Number(item.codigo_publicacion || 0);

    return `
      <article class="ev-cv-feature-card" data-cv-publicacion-id="${escapeHtml(id)}">
        <div class="ev-cv-feature-img">
          ${imagenHtml(item, true)}
        </div>

        <div class="ev-cv-feature-body">
          <div class="ev-cv-badges">
            ${renderBadge(item)}
            <span class="ev-cv-badge ev-cv-badge--featured">
              <i class="bi bi-star-fill"></i> Destacado
            </span>
          </div>

          <h3>${escapeHtml(item.titulo || 'Publicación')}</h3>

          <p>${escapeHtml(truncar(item.resumen, 170))}</p>

          <div class="ev-cv-meta-row">
            <span>
              <i class="bi bi-calendar3"></i>
              ${escapeHtml(formatFecha(item.fecha_publicacion))}
            </span>

            ${
              item.tipo_publicacion === 'evento' && item.fecha_evento_inicio
                ? `
                  <span>
                    <i class="bi bi-calendar-event"></i>
                    ${escapeHtml(formatFechaHora(item.fecha_evento_inicio))}
                  </span>
                `
                : ''
            }
          </div>

          <button
            type="button"
            class="ev-cv-read"
            data-cv-ver="${escapeHtml(id)}"
          >
            Leer publicación <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </article>
    `;
  }

  function renderCard(item) {
    const id = Number(item.codigo_publicacion || 0);

    return `
      <article class="ev-cv-card" data-cv-publicacion-id="${escapeHtml(id)}">
        <div class="ev-cv-card-img">
          ${imagenHtml(item)}
        </div>

        <div class="ev-cv-card-body">
          <div class="ev-cv-badges">
            ${renderBadge(item)}
          </div>

          <h3>${escapeHtml(truncar(item.titulo, 76))}</h3>

          <p>${escapeHtml(truncar(item.resumen, 116))}</p>

          <div class="ev-cv-card-footer">
            <time>${escapeHtml(formatFecha(item.fecha_publicacion))}</time>

            <button type="button" data-cv-ver="${escapeHtml(id)}">
              Leer publicación <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </article>
    `;
  }

  function renderVacio() {
    return `
      <div class="ev-cv-empty">
        <i class="bi bi-newspaper"></i>
        <strong>No hay novedades disponibles por ahora</strong>
        <p>Cuando tu comunidad publique comunicados, noticias o eventos, aparecerán aquí.</p>
      </div>
    `;
  }

  function renderCounts(root, counts = {}) {
    const todo = $('#evCvCountTodo', root);
    const comunicados = $('#evCvCountComunicados', root);
    const noticias = $('#evCvCountNoticias', root);
    const eventos = $('#evCvCountEventos', root);

    if (todo) {
      todo.textContent = String(Number(counts.total || 0));
    }

    if (comunicados) {
      comunicados.textContent = String(Number(counts.comunicados || 0));
    }

    if (noticias) {
      noticias.textContent = String(Number(counts.noticias || 0));
    }

    if (eventos) {
      eventos.textContent = String(Number(counts.eventos || 0));
    }
  }

  function renderPager(root, state, meta = {}) {
    state.page = Number(meta.page || 1);
    state.pages = Math.max(1, Number(meta.pages || 1));
    state.total = Number(meta.total || 0);

    const pager = $('#evCvPager', root);
    const pagina = $('#evCvPagina', root);
    const anterior = $('#evCvAnterior', root);
    const siguiente = $('#evCvSiguiente', root);

    if (!pager || !pagina || !anterior || !siguiente) {
      return;
    }

    pager.hidden = state.total <= state.size;
    pagina.textContent = `${state.page} / ${state.pages}`;
    anterior.disabled = state.page <= 1;
    siguiente.disabled = state.page >= state.pages;
  }

  function crearModal() {
    const modalEl = document.getElementById('modalDetalleComunidadVecino');

    if (!modalEl || !window.bootstrap?.Modal) {
      return null;
    }

    return bootstrap.Modal.getOrCreateInstance(modalEl, {
      backdrop: 'static',
      keyboard: false
    });
  }

  function configurarTipoModal(item) {
    const tipo = tipoSeguro(item.tipo_publicacion);
    const tipoEl = document.getElementById('evCvModalTipo');
    const iconoEl = document.querySelector('#evCvModalIcon i');

    if (tipoEl) {
      tipoEl.textContent = tipoLabel(tipo);
      tipoEl.className = `ev-cv-modal-type ev-cv-modal-type--${tipo}`;
    }

    if (iconoEl) {
      iconoEl.className = `bi ${tipoIcono(tipo)}`;
    }
  }

  function configurarPrioridadModal(item) {
    const prioridad = prioridadSegura(item.prioridad);
    const prioridadEl = document.getElementById('evCvModalPrioridad');

    if (!prioridadEl) {
      return;
    }

    prioridadEl.textContent = prioridadLabel(prioridad);
    prioridadEl.className = `ev-cv-modal-priority ev-cv-modal-priority--${prioridad}`;
  }

  function asegurarBotonDocumentoCompleto() {
    const modalBody = document.querySelector('#modalDetalleComunidadVecino .ev-cv-modal-body');
    const media = document.getElementById('evCvModalMedia');

    if (!modalBody || !media) {
      return null;
    }

    modalBody.querySelectorAll('.ev-cv-modal-image-open-wrap').forEach((wrap) => {
      if (wrap.id !== 'evCvModalImageOpenWrap') {
        wrap.remove();
      }
    });

    modalBody.querySelectorAll('.ev-cv-modal-image-open').forEach((link) => {
      if (link.id !== 'evCvModalImageOpen') {
        const wrap = link.closest('.ev-cv-modal-image-open-wrap');

        if (wrap) {
          wrap.remove();
        } else {
          link.remove();
        }
      }
    });

    let wrap = document.getElementById('evCvModalImageOpenWrap');
    let link = document.getElementById('evCvModalImageOpen');

    if (!wrap) {
      wrap = document.createElement('div');
      wrap.id = 'evCvModalImageOpenWrap';
      wrap.className = 'ev-cv-modal-image-open-wrap';
      wrap.hidden = true;
    }

    if (!link) {
      link = document.createElement('a');
      link.id = 'evCvModalImageOpen';
      wrap.appendChild(link);
    }

    link.className = 'ev-cv-modal-image-open';
    link.href = '#';
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    link.innerHTML = '<i class="bi bi-arrows-fullscreen"></i><span>Ver documento completo</span>';

    media.insertAdjacentElement('afterend', wrap);

    return link;
  }

  function configurarImagenModal(item) {
    const imagen = imagenUrl(item.imagen_portada);
    const media = document.getElementById('evCvModalMedia');
    const imagenEl = document.getElementById('evCvModalImagen');
    const linkDocumento = asegurarBotonDocumentoCompleto();
    const wrapDocumento = document.getElementById('evCvModalImageOpenWrap');

    if (!media || !imagenEl) {
      return;
    }

    if (imagen) {
      imagenEl.src = imagen;
      imagenEl.alt = item.titulo || 'Imagen de publicación';
      imagenEl.hidden = false;

      media.hidden = false;
      media.classList.add('has-image');

      if (linkDocumento && wrapDocumento) {
        linkDocumento.href = imagen;
        linkDocumento.setAttribute('aria-label', 'Ver documento completo en una pestaña nueva');
        wrapDocumento.hidden = false;
      }

      return;
    }

    imagenEl.removeAttribute('src');
    imagenEl.alt = '';
    imagenEl.hidden = true;

    media.hidden = true;
    media.classList.remove('has-image');

    if (linkDocumento && wrapDocumento) {
      linkDocumento.href = '#';
      wrapDocumento.hidden = true;
    }
  }

  function configurarFechaModal(item) {
    const fechaContenedor = document.getElementById('evCvModalFecha');
    const fechaTexto = fechaContenedor?.querySelector('span');

    if (!fechaContenedor || !fechaTexto) {
      return;
    }

    const fecha = formatFecha(item.fecha_publicacion);

    if (fecha) {
      fechaTexto.textContent = fecha;
      fechaContenedor.hidden = false;
    } else {
      fechaTexto.textContent = 'Fecha no disponible';
      fechaContenedor.hidden = false;
    }
  }

  function configurarEventoModal(item) {
    const evento = document.getElementById('evCvModalEvento');
    const eventoTexto = document.getElementById('evCvModalEventoTexto');

    if (!evento || !eventoTexto) {
      return;
    }

    if (tipoSeguro(item.tipo_publicacion) === 'evento' && item.fecha_evento_inicio) {
      const texto = [
        formatFechaHora(item.fecha_evento_inicio),
        item.ubicacion_evento || ''
      ].filter(Boolean).join(' · ');

      eventoTexto.textContent = texto;
      evento.hidden = false;
      return;
    }

    eventoTexto.textContent = '';
    evento.hidden = true;
  }

  function pintarDetalleModal(item) {
    const titulo = document.getElementById('evCvModalTitle');
    const resumen = document.getElementById('evCvModalResumen');
    const contenido = document.getElementById('evCvModalContenido');
    const destacado = document.getElementById('evCvModalDestacado');

    if (titulo) {
      titulo.textContent = item.titulo || 'Publicación';
    }

    if (resumen) {
      resumen.textContent = item.resumen || 'Sin resumen disponible.';
    }

    if (contenido) {
      contenido.textContent = item.contenido || 'Sin información adicional disponible.';
    }

    if (destacado) {
      destacado.hidden = Number(item.destacado_dashboard || 0) !== 1;
    }

    configurarTipoModal(item);
    configurarPrioridadModal(item);
    configurarImagenModal(item);
    configurarFechaModal(item);
    configurarEventoModal(item);
  }

  async function abrirDetalle(root, id) {
    if (!Number.isInteger(id) || id <= 0) {
      return;
    }

    try {
      const data = await requestJson(
        `${BASE}/api/comunidad/vecino/publicaciones/${encodeURIComponent(id)}`
      );

      const item = data.item || {};
      pintarDetalleModal(item);

      const modalEl = document.getElementById('modalDetalleComunidadVecino');
      const modalBody = modalEl?.querySelector('.ev-cv-modal-body');

      if (modalBody) {
        modalBody.scrollTop = 0;
      }

      const modal = crearModal();
      modal?.show();
    } catch (error) {
      if (window.Swal?.fire) {
        await Swal.fire({
          icon: 'info',
          title: 'Publicación no disponible',
          text: error.message || 'No se pudo abrir esta publicación.',
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#EA7C12',
          allowOutsideClick: false
        });

        return;
      }

      alert(error.message || 'No se pudo abrir esta publicación.');
    }
  }

  function extraerPublicacionDesdeEvGoto() {
    try {
      const qs = new URLSearchParams(window.location.search || '');
      const evGoto = qs.get('ev_goto') || '';

      if (!evGoto) {
        return 0;
      }

      const decoded = decodeURIComponent(evGoto);
      const idx = decoded.indexOf('?');

      if (idx < 0) {
        return 0;
      }

      const inner = new URLSearchParams(decoded.slice(idx + 1));
      return Number(inner.get('publicacion') || 0);
    } catch (_) {
      return 0;
    }
  }

  function obtenerPublicacionSeleccionada() {
    try {
      const qs = new URLSearchParams(window.location.search || '');
      const directa = Number(qs.get('publicacion') || 0);

      if (directa > 0) {
        return directa;
      }
    } catch (_) {}

    const desdeEvGoto = extraerPublicacionDesdeEvGoto();
    if (desdeEvGoto > 0) {
      return desdeEvGoto;
    }

    try {
      const id = Number(sessionStorage.getItem(STORAGE_ID) || 0);
      const at = Number(sessionStorage.getItem(STORAGE_AT) || 0);

      if (id > 0 && (!at || Date.now() - at <= HIGHLIGHT_TTL_MS)) {
        return id;
      }
    } catch (_) {}

    return 0;
  }

  function limpiarPublicacionSeleccionada() {
    try {
      sessionStorage.removeItem(STORAGE_ID);
      sessionStorage.removeItem(STORAGE_AT);
    } catch (_) {}
  }

  function inyectarEstilosEnfoque() {
    if (document.getElementById('ev-cv-focus-dashboard-style')) {
      return;
    }

    const style = document.createElement('style');
    style.id = 'ev-cv-focus-dashboard-style';
    style.textContent = `
      .ev-cv-feature-card[data-cv-publicacion-id],
      .ev-cv-card[data-cv-publicacion-id]{
        scroll-margin-top: 112px;
      }

      .ev-cv-publicacion-seleccionada{
        border-color: rgba(234,124,18,.92) !important;
        box-shadow:
          0 0 0 4px rgba(234,124,18,.13),
          0 24px 55px rgba(234,124,18,.19),
          0 12px 28px rgba(15,23,42,.08) !important;
        animation: evCvPublicacionSeleccionadaPulse 1.35s ease-in-out 0s 3;
      }

      @keyframes evCvPublicacionSeleccionadaPulse{
        0%{ transform:translateY(0); }
        35%{ transform:translateY(-3px); }
        70%{ transform:translateY(0); }
        100%{ transform:translateY(0); }
      }
    `;
    document.head.appendChild(style);
  }

  function enfocarPublicacionSeleccionada(root, idSeleccionado) {
    const id = Number(idSeleccionado || obtenerPublicacionSeleccionada() || 0);

    if (id <= 0 || !root) {
      return false;
    }

    inyectarEstilosEnfoque();

    const selector = `[data-cv-publicacion-id="${CSS.escape(String(id))}"]`;
    const card = root.querySelector(selector);

    if (!card) {
      return false;
    }

    $$('.ev-cv-publicacion-seleccionada', root).forEach((el) => {
      el.classList.remove('ev-cv-publicacion-seleccionada');
    });

    card.classList.add('ev-cv-publicacion-seleccionada');
    card.setAttribute('tabindex', '-1');

    window.setTimeout(() => {
      try {
        card.scrollIntoView({
          behavior: 'smooth',
          block: 'center',
          inline: 'nearest'
        });
      } catch (_) {
        card.scrollIntoView();
      }
    }, 180);

    window.setTimeout(() => {
      try {
        card.focus({ preventScroll: true });
      } catch (_) {}
    }, 760);

    window.setTimeout(() => {
      card.classList.remove('ev-cv-publicacion-seleccionada');
    }, 6200);

    limpiarPublicacionSeleccionada();
    return true;
  }

  function intentarEnfoqueDiferido(root) {
    const id = obtenerPublicacionSeleccionada();

    if (id <= 0) {
      return;
    }

    const intentos = [120, 320, 700, 1100];

    intentos.forEach((delay) => {
      window.setTimeout(() => {
        if (!obtenerPublicacionSeleccionada() && !root.querySelector(`[data-cv-publicacion-id="${CSS.escape(String(id))}"]`)) {
          return;
        }

        enfocarPublicacionSeleccionada(root, id);
      }, delay);
    });
  }

  function actualizarTituloDestacadas(featureSection, totalDestacadas) {
    const titulo = featureSection?.querySelector('.ev-cv-section-heading h2');

    if (!titulo) {
      return;
    }

    titulo.textContent = totalDestacadas === 1
      ? 'Destacado para ti'
      : 'Destacados para ti';
  }

  async function cargar(root, state) {
    const grid = $('#evCvGrid', root);
    const featureSection = $('#evCvDestacadaSection', root);
    const feature = $('#evCvDestacada', root);
    const recientes = $('#evCvRecientesSection', root);
    const error = $('#evCvError', root);
    const meta = $('#evCvMeta', root);

    if (!grid || !featureSection || !feature || !recientes || !error || !meta) {
      return;
    }

    error.classList.add('d-none');

    grid.innerHTML = `
      <div class="ev-cv-skeleton"></div>
      <div class="ev-cv-skeleton"></div>
      <div class="ev-cv-skeleton"></div>
    `;

    const params = new URLSearchParams({
      tipo: state.tipo,
      q: state.q,
      page: String(state.page),
      size: String(state.size)
    });

    try {
      const data = await requestJson(
        `${BASE}/api/comunidad/vecino/publicaciones?${params.toString()}`
      );

      const items = Array.isArray(data.items) ? data.items : [];

      renderCounts(root, data.counts || {});
      renderPager(root, state, data.meta || {});

      const destacadas = state.page === 1
        ? items.filter(esDestacada)
        : [];

      if (destacadas.length) {
        actualizarTituloDestacadas(featureSection, destacadas.length);
        feature.innerHTML = destacadas.map(renderDestacada).join('');
        featureSection.hidden = false;
      } else {
        feature.innerHTML = '';
        featureSection.hidden = true;
      }

      const recientesItems = state.page === 1
        ? items.filter((item) => !esDestacada(item))
        : items;

      meta.textContent = state.total === 1
        ? '1 publicación disponible.'
        : `${state.total} publicaciones disponibles.`;

      if (!recientesItems.length && destacadas.length) {
        recientes.hidden = true;
      } else {
        recientes.hidden = false;
        grid.innerHTML = recientesItems.length
          ? recientesItems.map(renderCard).join('')
          : renderVacio();
      }

      intentarEnfoqueDiferido(root);
    } catch (errorRequest) {
      console.warn('[EV][ComunidadVecino] No se pudieron cargar publicaciones:', errorRequest);

      featureSection.hidden = true;
      recientes.hidden = false;
      grid.innerHTML = renderVacio();
      error.classList.remove('d-none');
      meta.textContent = 'No se pudieron cargar las publicaciones.';
    }
  }

  function vincularModal() {
    const modalEl = document.getElementById('modalDetalleComunidadVecino');

    if (!modalEl || modalEl.dataset.cvBound === '1') {
      return;
    }

    modalEl.dataset.cvBound = '1';

    modalEl.addEventListener('hidden.bs.modal', () => {
      const modalBody = modalEl.querySelector('.ev-cv-modal-body');

      if (modalBody) {
        modalBody.scrollTop = 0;
      }
    });
  }

  function init() {
    const root = document.getElementById('evComunidadVecino');

    if (!root || root.dataset.cvInit === '1') {
      return;
    }

    root.dataset.cvInit = '1';

    const state = {
      tipo: 'all',
      q: '',
      page: 1,
      size: 9,
      pages: 1,
      total: 0
    };

    vincularModal();
    inyectarEstilosEnfoque();

    $$('[data-cv-tipo]', root).forEach((button) => {
      button.addEventListener('click', () => {
        $$('[data-cv-tipo]', root).forEach((item) => {
          item.classList.remove('is-active');
        });

        button.classList.add('is-active');

        state.tipo = button.dataset.cvTipo || 'all';
        state.page = 1;

        cargar(root, state);
      });
    });

    const formularioBusqueda = $('#evCvBuscarForm', root);
    const inputBusqueda = $('#evCvBuscar', root);
    const btnAnterior = $('#evCvAnterior', root);
    const btnSiguiente = $('#evCvSiguiente', root);

    formularioBusqueda?.addEventListener('submit', (event) => {
      event.preventDefault();

      state.q = String(inputBusqueda?.value || '').trim();
      state.page = 1;

      cargar(root, state);
    });

    btnAnterior?.addEventListener('click', () => {
      if (state.page > 1) {
        state.page -= 1;
        cargar(root, state);
      }
    });

    btnSiguiente?.addEventListener('click', () => {
      if (state.page < state.pages) {
        state.page += 1;
        cargar(root, state);
      }
    });

    root.addEventListener('click', (event) => {
      const button = event.target.closest('[data-cv-ver]');

      if (!button) {
        return;
      }

      const id = Number(button.dataset.cvVer || 0);
      abrirDetalle(root, id);
    });

    cargar(root, state);
  }

  document.addEventListener('DOMContentLoaded', init);
  document.addEventListener('ev:content-loaded', init);

  window.EVComunidadVecino = {
    init,
    focusPublicacion: function (id) {
      const root = document.getElementById('evComunidadVecino');
      return enfocarPublicacionSeleccionada(root, Number(id || 0));
    }
  };

  init();
})();
