// views/js/comunidadVecino.js
// Entre Vecinos - Novedades oficiales visibles para el vecino.
// Versión final:
// - Vista escritorio y móvil optimizada.
// - Tabs accesibles y sincronizados.
// - Modal detalle con estándar visual EV.
// - Imagen completa sin recortes.
// - Protección ante respuestas AJAX fuera de orden.
// - Modal estable: no se cierra al hacer clic fuera ni con Escape.

(function () {
  'use strict';

  const BASE = String(window.BASE_URL || window.EV_BASE_URL || '').replace(/\/+$/, '');

  if (!BASE) {
    return;
  }

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  let listaRequestId = 0;
  let detalleRequestId = 0;

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
    const tipo = tipoSeguro(item.tipo_publicacion);

    return `
      <article class="ev-cv-feature-card">
        <div class="ev-cv-feature-img">
          ${imagenHtml(item, true)}
        </div>

        <div class="ev-cv-feature-body">
          <div class="ev-cv-badges">
            ${renderBadge(item)}

            <span class="ev-cv-badge ev-cv-badge--featured">
              <i class="bi bi-star-fill"></i>
              Destacado
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
              tipo === 'evento' && item.fecha_evento_inicio
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
            data-cv-ver="${Number(item.codigo_publicacion)}"
            aria-label="Leer publicación ${escapeHtml(item.titulo || '')}"
          >
            Leer publicación <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </article>
    `;
  }

  function renderCard(item) {
    return `
      <article class="ev-cv-card">
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

            <button
              type="button"
              data-cv-ver="${Number(item.codigo_publicacion)}"
              aria-label="Leer publicación ${escapeHtml(item.titulo || '')}"
            >
              Leer más <i class="bi bi-chevron-right"></i>
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

  function renderSkeleton() {
    return `
      <div class="ev-cv-skeleton"></div>
      <div class="ev-cv-skeleton"></div>
      <div class="ev-cv-skeleton"></div>
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

  function actualizarTabActivo(root, tipoSeleccionado) {
    const tipo = String(tipoSeleccionado || 'all');

    $$('[data-cv-tipo]', root).forEach((button) => {
      const activo = String(button.dataset.cvTipo || 'all') === tipo;

      button.classList.toggle('is-active', activo);
      button.setAttribute('aria-selected', activo ? 'true' : 'false');
      button.tabIndex = activo ? 0 : -1;
    });
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

  function configurarImagenModal(item) {
    const imagen = imagenUrl(item.imagen_portada);
    const media = document.getElementById('evCvModalMedia');
    const imagenEl = document.getElementById('evCvModalImagen');

    if (!media || !imagenEl) {
      return;
    }

    if (imagen) {
      imagenEl.src = imagen;
      imagenEl.alt = item.titulo || 'Imagen de publicación';
      imagenEl.hidden = false;

      media.hidden = false;
      media.classList.add('has-image');
      return;
    }

    imagenEl.removeAttribute('src');
    imagenEl.alt = '';
    imagenEl.hidden = true;

    media.hidden = true;
    media.classList.remove('has-image');
  }

  function configurarFechaModal(item) {
    const fechaContenedor = document.getElementById('evCvModalFecha');
    const fechaTexto = fechaContenedor?.querySelector('span');

    if (!fechaContenedor || !fechaTexto) {
      return;
    }

    const fecha = formatFecha(item.fecha_publicacion);

    fechaTexto.textContent = fecha || 'Fecha no disponible';
    fechaContenedor.hidden = false;
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

    const requestActual = ++detalleRequestId;

    try {
      const data = await requestJson(
        `${BASE}/api/comunidad/vecino/publicaciones/${encodeURIComponent(id)}`
      );

      if (requestActual !== detalleRequestId || !root.isConnected) {
        return;
      }

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
      if (requestActual !== detalleRequestId) {
        return;
      }

      if (window.Swal?.fire) {
        await Swal.fire({
          icon: 'info',
          title: 'Publicación no disponible',
          text: error.message || 'No se pudo abrir esta publicación.',
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#EA7C12',
          allowOutsideClick: false,
          allowEscapeKey: false
        });

        return;
      }

      alert(error.message || 'No se pudo abrir esta publicación.');
    }
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

    const requestActual = ++listaRequestId;

    error.classList.add('d-none');
    featureSection.hidden = true;
    feature.innerHTML = '';
    recientes.hidden = false;
    meta.textContent = 'Cargando novedades...';
    grid.innerHTML = renderSkeleton();

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

      if (requestActual !== listaRequestId || !root.isConnected) {
        return;
      }

      const items = Array.isArray(data.items) ? data.items : [];

      renderCounts(root, data.counts || {});
      renderPager(root, state, data.meta || {});

      const destacada = state.page === 1
        ? items.find((item) => Number(item.destacado_dashboard || 0) === 1) || null
        : null;

      if (destacada) {
        feature.innerHTML = renderDestacada(destacada);
        featureSection.hidden = false;
      }

      const restantes = destacada
        ? items.filter(
            (item) => Number(item.codigo_publicacion) !== Number(destacada.codigo_publicacion)
          )
        : items;

      meta.textContent = state.total === 1
        ? '1 publicación disponible.'
        : `${state.total} publicaciones disponibles.`;

      if (!restantes.length && destacada) {
        recientes.hidden = true;
        return;
      }

      recientes.hidden = false;
      grid.innerHTML = restantes.length
        ? restantes.map(renderCard).join('')
        : renderVacio();
    } catch (errorRequest) {
      if (requestActual !== listaRequestId) {
        return;
      }

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

  function vincularTabs(root, state) {
    $$('[data-cv-tipo]', root).forEach((button) => {
      button.addEventListener('click', () => {
        const tipo = String(button.dataset.cvTipo || 'all');

        state.tipo = tipo;
        state.page = 1;

        actualizarTabActivo(root, tipo);
        cargar(root, state);
      });

      button.addEventListener('keydown', (event) => {
        const tabs = $$('[data-cv-tipo]', root);
        const actual = tabs.indexOf(button);

        if (actual < 0 || !['ArrowLeft', 'ArrowRight'].includes(event.key)) {
          return;
        }

        event.preventDefault();

        const direccion = event.key === 'ArrowRight' ? 1 : -1;
        const siguienteIndice = (actual + direccion + tabs.length) % tabs.length;
        tabs[siguienteIndice].focus();
        tabs[siguienteIndice].click();
      });
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
    actualizarTabActivo(root, state.tipo);
    vincularTabs(root, state);

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
    refresh: function () {
      const root = document.getElementById('evComunidadVecino');

      if (!root) {
        return;
      }

      delete root.dataset.cvInit;
      init();
    }
  };

  init();
})();