// views/js/notificacionesGlobales.js
// Centro global de notificaciones EV:
// - Campana con contador de no leídas.
// - Últimas notificaciones de todos los módulos.
// - Navegación al módulo relacionado.
// - Apertura directa de la gestión del servicio cuando corresponde.
(function () {
  'use strict';

  if (window.__EV_NOTIFICACIONES_GLOBALES_INIT__ === true) {
    window.EVNotificacionesGlobales?.refresh?.({ silent: true });
    return;
  }
  window.__EV_NOTIFICACIONES_GLOBALES_INIT__ = true;

  const BASE = String(window.BASE_URL || window.EV_BASE_URL || '').replace(/\/+$/, '');
  const POLLING_MS = 15000;
  const FETCH_TIMEOUT_MS = 7000;
  const MAX_ITEMS = 8;
  const PENDING_SERVICE_KEY = 'ev_notificacion_servicio_pendiente';

  let pollingTimer = null;
  let cargando = false;
  let itemsCache = new Map();
  let totalNoLeidas = 0;

  const SUBCATEGORIAS_GESTION_SERVICIO = new Set([
    'cotizacion_final_aceptada',
    'reprogramacion_propuesta',
    'reprogramacion_aceptada',
    'reprogramacion_rechazada',
    'reprogramacion_cancelada',
    'servicio_iniciado',
    'servicio_realizado',
    'servicio_marcado_realizado',
    'servicio_confirmado',
    'problema_reportado',
    'observacion_reportada',
    'incidencia_respondida',
    'solucion_registrada',
    'solucion_confirmada',
    'problema_persiste',
    'revision_soporte_solicitada',
    'revision_soporte_sugerida',
    'resolucion_soporte',
    'actualizacion_soporte',
    'calificacion_habilitada',
    'servicio_cancelado',
    'servicio_cancelado_soporte'
  ]);

  function refs() {
    return {
      menu: document.getElementById('evNotificationMenu'),
      button: document.getElementById('evNotificationButton'),
      count: document.getElementById('evNotificationCount'),
      list: document.getElementById('evNotificationList'),
      summary: document.getElementById('evNotificationSummary'),
      refresh: document.getElementById('evNotificationRefresh'),
      viewAll: document.querySelector('[data-ev-notification-all="1"]')
    };
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function parsePayload(item) {
    if (item?.payload && typeof item.payload === 'object' && !Array.isArray(item.payload)) {
      return item.payload;
    }

    const raw = String(item?.payload_json || '').trim();
    if (!raw) return {};

    try {
      const parsed = JSON.parse(raw);
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (_) {
      return {};
    }
  }

  function normalizarRuta(route) {
    let value = String(route || '').trim();
    if (!value) return '';

    if (/^https?:\/\//i.test(value)) {
      try {
        const url = new URL(value);
        value = url.pathname + url.search;
      } catch (_) {
        return value;
      }
    }

    if (!value.startsWith('/')) value = `/${value}`;

    const basePath = (() => {
      try {
        return new URL(BASE, window.location.origin).pathname.replace(/\/+$/, '');
      } catch (_) {
        return '';
      }
    })();

    if (basePath && basePath !== '/' && value.startsWith(`${basePath}/`)) {
      value = value.slice(basePath.length) || '/';
    }

    return value.replace(/\/{2,}/g, '/');
  }

  function rutaPorCategoria(item, payload) {
    const directa = normalizarRuta(payload?.ruta || '');
    if (directa) return directa;

    const categoria = String(item?.categoria || '').toLowerCase();
    const rolDestino = String(payload?.rol_destino || '').toLowerCase();

    if (categoria === 'servicio') {
      if (rolDestino === 'proveedor') return '/mis-solicitudes-servicio-vendedor';
      if (rolDestino === 'soporte') return '/atender-servicios';
      return '/mis-solicitudes-servicio-comprador';
    }

    if (categoria === 'residencia') return '/notificaciones-residencia';
    if (categoria === 'pedido' || categoria === 'pedidos') {
      return rolDestino === 'vendedor' ? '/mis-pedidos-vendedor' : '/mis-pedidos-comprador';
    }

    if (categoria === 'soporte') return '/MenuPrincipal';
    return '/MenuPrincipal';
  }

  function iconoNotificacion(item) {
    const categoria = String(item?.categoria || '').toLowerCase();
    const sub = String(item?.subcategoria || '').toLowerCase();

    if (categoria === 'servicio') {
      if (sub.includes('reprogramacion')) return { icon: 'bi-calendar2-week', tone: 'warning' };
      if (sub.includes('problema') || sub.includes('incidencia') || sub.includes('observacion')) return { icon: 'bi-exclamation-triangle', tone: 'danger' };
      if (sub.includes('solucion')) return { icon: 'bi-tools', tone: 'success' };
      if (sub.includes('soporte')) return { icon: 'bi-headset', tone: 'info' };
      if (sub.includes('calificacion')) return { icon: 'bi-star', tone: 'warning' };
      if (sub.includes('cancel')) return { icon: 'bi-x-octagon', tone: 'danger' };
      if (sub.includes('realizado') || sub.includes('confirmado')) return { icon: 'bi-clipboard2-check', tone: 'success' };
      if (sub.includes('iniciado')) return { icon: 'bi-play-circle', tone: 'info' };
      if (sub.includes('cotizacion')) return { icon: 'bi-receipt', tone: 'warning' };
      return { icon: 'bi-briefcase', tone: 'info' };
    }

    if (categoria === 'residencia') return { icon: 'bi-house-check', tone: 'info' };
    if (categoria === 'pedido' || categoria === 'pedidos') return { icon: 'bi-bag-check', tone: 'success' };
    if (categoria === 'soporte') return { icon: 'bi-headset', tone: 'info' };
    return { icon: 'bi-bell', tone: 'neutral' };
  }

  function tiempoRelativo(value) {
    const raw = String(value || '').trim();
    if (!raw) return '';

    const date = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return raw;

    const diff = Math.max(0, Date.now() - date.getTime());
    const minutes = Math.floor(diff / 60000);

    if (minutes < 1) return 'Ahora';
    if (minutes < 60) return `Hace ${minutes} min`;

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `Hace ${hours} ${hours === 1 ? 'hora' : 'horas'}`;

    const days = Math.floor(hours / 24);
    if (days <= 7) return `Hace ${days} ${days === 1 ? 'día' : 'días'}`;

    return date.toLocaleDateString('es-PE', {
      day: '2-digit',
      month: 'short',
      year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
    });
  }

  async function fetchJson(url, options = {}, timeoutMs = FETCH_TIMEOUT_MS) {
    const controller = new AbortController();
    const timer = window.setTimeout(() => controller.abort(), timeoutMs);

    try {
      const response = await fetch(url, {
        credentials: 'include',
        cache: 'no-store',
        ...options,
        signal: controller.signal,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...(options.headers || {})
        }
      });

      const json = await response.json().catch(() => ({}));
      return { response, json };
    } finally {
      window.clearTimeout(timer);
    }
  }

  function setLoading(show = true) {
    const { list } = refs();
    if (!list || !show) return;

    list.innerHTML = `
      <div class="ev-notification-loading">
        <span class="ev-notification-spinner" aria-hidden="true"></span>
        <span>Cargando notificaciones...</span>
      </div>
    `;
  }

  function actualizarContador(total) {
    const { count, summary, button } = refs();
    totalNoLeidas = Math.max(0, Number(total || 0));

    if (count) {
      count.textContent = totalNoLeidas > 99 ? '99+' : String(totalNoLeidas);
      count.classList.toggle('d-none', totalNoLeidas <= 0);
      count.setAttribute('aria-label', `${totalNoLeidas} notificaciones no leídas`);
    }

    if (button) {
      button.classList.toggle('has-unread', totalNoLeidas > 0);
      button.setAttribute('aria-label', totalNoLeidas > 0
        ? `Abrir notificaciones. ${totalNoLeidas} pendientes.`
        : 'Abrir notificaciones');
    }

    if (summary) {
      summary.innerHTML = totalNoLeidas > 0
        ? `<strong>${totalNoLeidas}</strong> ${totalNoLeidas === 1 ? 'novedad pendiente' : 'novedades pendientes'}`
        : 'Estás al día. No tienes notificaciones pendientes.';
      summary.classList.toggle('has-unread', totalNoLeidas > 0);
    }
  }

  function renderLista(items) {
    const { list } = refs();
    if (!list) return;

    const rows = Array.isArray(items) ? items : [];
    itemsCache = new Map();

    if (!rows.length) {
      list.innerHTML = `
        <div class="ev-notification-empty">
          <span><i class="bi bi-bell-slash"></i></span>
          <strong>No hay notificaciones</strong>
          <p>Las novedades de pedidos, servicios y tu comunidad aparecerán aquí.</p>
        </div>
      `;
      return;
    }

    list.innerHTML = rows.map((item) => {
      const id = Number(item?.codigo_notificacion || 0);
      const unread = String(item?.estado || '') === 'no_leida';
      const meta = iconoNotificacion(item);
      const payload = parsePayload(item);
      const tituloServicio = String(payload?.titulo_servicio || '').trim();
      const secondary = tituloServicio && !String(item?.mensaje || '').includes(tituloServicio)
        ? `<span class="ev-notification-context">${escapeHtml(tituloServicio)}</span>`
        : '';

      if (id > 0) itemsCache.set(id, { ...item, payload });

      return `
        <button type="button"
                class="ev-notification-item ${unread ? 'is-unread' : ''}"
                data-ev-notification-id="${id}">
          <span class="ev-notification-icon is-${escapeHtml(meta.tone)}">
            <i class="bi ${escapeHtml(meta.icon)}"></i>
          </span>
          <span class="ev-notification-copy">
            <span class="ev-notification-title-row">
              <strong>${escapeHtml(item?.titulo || 'Nueva notificación')}</strong>
              ${unread ? '<i class="ev-notification-unread-dot" aria-label="No leída"></i>' : ''}
            </span>
            <span class="ev-notification-message">${escapeHtml(item?.mensaje || '')}</span>
            ${secondary}
            <time>${escapeHtml(tiempoRelativo(item?.created_at))}</time>
          </span>
          <i class="bi bi-chevron-right ev-notification-chevron" aria-hidden="true"></i>
        </button>
      `;
    }).join('');
  }

  async function cargarContador() {
    const { response, json } = await fetchJson(`${BASE}/api/notificaciones/counts`);
    if (!response.ok || json?.ok !== true) return;

    const data = json?.data || {};
    const total = Number(data.total ?? (
      Number(data.residencia || 0) +
      Number(data.soporte || 0) +
      Number(data.pedidos || 0) +
      Number(data.servicio || 0)
    ));

    actualizarContador(total);
  }

  async function cargarLista() {
    const url = new URL(`${BASE}/api/notificaciones`, window.location.origin);
    url.searchParams.set('categoria', 'all');
    url.searchParams.set('estado', 'all');
    url.searchParams.set('page', '1');
    url.searchParams.set('size', String(MAX_ITEMS));
    url.searchParams.set('_', String(Date.now()));

    const { response, json } = await fetchJson(url.toString());
    if (!response.ok || json?.ok !== true) {
      throw new Error(json?.mensaje || 'No se pudieron cargar las notificaciones.');
    }

    renderLista(Array.isArray(json?.data) ? json.data : []);
  }

  async function refresh(options = {}) {
    if (!BASE || cargando || !refs().button) return;

    const silent = options.silent === true;
    cargando = true;

    if (!silent) setLoading(true);

    try {
      await Promise.all([cargarContador(), cargarLista()]);
    } catch (error) {
      const { list } = refs();
      if (list && !silent) {
        list.innerHTML = `
          <div class="ev-notification-error">
            <i class="bi bi-exclamation-circle"></i>
            <span>${escapeHtml(error?.message || 'No se pudieron cargar las notificaciones.')}</span>
          </div>
        `;
      }
    } finally {
      cargando = false;
    }
  }

  async function marcarLeida(id) {
    const codigo = Number(id || 0);
    if (codigo <= 0) return false;

    try {
      const { response, json } = await fetchJson(`${BASE}/api/notificaciones/${codigo}/leida`, {
        method: 'POST'
      });
      return response.ok && json?.ok === true;
    } catch (_) {
      return false;
    }
  }

  function cerrarDropdown() {
    const { button } = refs();
    if (!button || !window.bootstrap?.Dropdown) return;

    try {
      window.bootstrap.Dropdown.getOrCreateInstance(button).hide();
    } catch (_) {}
  }

  function guardarServicioPendiente(item, payload, ruta) {
    const categoria = String(item?.categoria || '').toLowerCase();
    const subcategoria = String(item?.subcategoria || '').toLowerCase();
    const codigoSolicitud = Number(
      payload?.codigo_solicitud_servicio ||
      (categoria === 'servicio' ? item?.referencia_id : 0) ||
      0
    );

    if (categoria !== 'servicio' || codigoSolicitud <= 0 || !SUBCATEGORIAS_GESTION_SERVICIO.has(subcategoria)) {
      return false;
    }

    try {
      sessionStorage.setItem(PENDING_SERVICE_KEY, JSON.stringify({
        codigo_solicitud_servicio: codigoSolicitud,
        ruta,
        created_at: Date.now()
      }));
      return true;
    } catch (_) {
      return false;
    }
  }

  function leerServicioPendiente() {
    try {
      const raw = sessionStorage.getItem(PENDING_SERVICE_KEY);
      if (!raw) return null;

      const data = JSON.parse(raw);
      const id = Number(data?.codigo_solicitud_servicio || 0);
      const age = Date.now() - Number(data?.created_at || 0);

      if (id <= 0 || age < 0 || age > 2 * 60 * 1000) {
        sessionStorage.removeItem(PENDING_SERVICE_KEY);
        return null;
      }

      return data;
    } catch (_) {
      return null;
    }
  }

  async function intentarAbrirServicioPendiente() {
    const pending = leerServicioPendiente();
    if (!pending) return false;

    const pageReady = document.querySelector('.ev-ssc-page, .ev-ssv-page');
    if (!pageReady) return false;

    const deadline = Date.now() + 3500;
    while (Date.now() < deadline) {
      if (window.EVServicioOperacion?.open) {
        try {
          sessionStorage.removeItem(PENDING_SERVICE_KEY);
          await window.EVServicioOperacion.open(Number(pending.codigo_solicitud_servicio));
          return true;
        } catch (_) {
          return false;
        }
      }
      await new Promise((resolve) => window.setTimeout(resolve, 120));
    }

    return false;
  }

  async function navegarNotificacion(item) {
    if (!item) return;

    const payload = parsePayload(item);
    const ruta = rutaPorCategoria(item, payload);
    guardarServicioPendiente(item, payload, ruta);
    cerrarDropdown();

    if (window.EVNav && typeof window.EVNav.loadPage === 'function' && ruta.startsWith('/')) {
      await window.EVNav.loadPage(ruta, { pushState: true, replaceState: false });
      await intentarAbrirServicioPendiente();
      return;
    }

    if (/^https?:\/\//i.test(ruta)) {
      window.location.href = ruta;
      return;
    }

    window.location.href = `${BASE}/MenuPrincipal?ev_goto=${encodeURIComponent(ruta)}`;
  }

  async function onNotificationClick(event) {
    const button = event.target.closest('[data-ev-notification-id]');
    if (!button) return;

    event.preventDefault();
    event.stopPropagation();

    const id = Number(button.dataset.evNotificationId || 0);
    const item = itemsCache.get(id);
    if (!item) return;

    button.disabled = true;

    if (String(item.estado || '') === 'no_leida') {
      const ok = await marcarLeida(id);
      if (ok) {
        item.estado = 'leida';
        totalNoLeidas = Math.max(0, totalNoLeidas - 1);
        actualizarContador(totalNoLeidas);
      }
    }

    await navegarNotificacion(item);
    window.setTimeout(() => refresh({ silent: true }), 250);
  }

  async function irCentroNotificaciones(event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    cerrarDropdown();
    const ruta = '/notificaciones';

    if (window.EVNav && typeof window.EVNav.loadPage === 'function') {
      await window.EVNav.loadPage(ruta, { pushState: true, replaceState: false });
      return;
    }

    window.location.href = `${BASE}/MenuPrincipal?ev_goto=${encodeURIComponent(ruta)}`;
  }

  function bind() {
    const r = refs();
    if (!r.button || r.button.dataset.evNotificationsBound === '1') return;

    r.button.dataset.evNotificationsBound = '1';

    r.menu?.addEventListener('show.bs.dropdown', () => {
      refresh({ silent: true });
    });

    r.refresh?.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      refresh({ silent: false });
    });

    r.viewAll?.addEventListener('click', irCentroNotificaciones);

    r.list?.addEventListener('click', onNotificationClick);
  }

  function startPolling() {
    stopPolling();
    pollingTimer = window.setInterval(() => {
      if (!document.hidden) refresh({ silent: true });
    }, POLLING_MS);
  }

  function stopPolling() {
    if (pollingTimer) {
      window.clearInterval(pollingTimer);
      pollingTimer = null;
    }
  }

  function init() {
    if (!BASE || !refs().button) return false;

    bind();
    refresh({ silent: true });
    startPolling();
    window.setTimeout(intentarAbrirServicioPendiente, 300);
    return true;
  }

  document.addEventListener('ev:notificaciones-globales-actualizar', () => {
    refresh({ silent: true });
  });

  document.addEventListener('ev:content-loaded', () => {
    window.setTimeout(intentarAbrirServicioPendiente, 180);
  });

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) refresh({ silent: true });
  });

  window.addEventListener('pageshow', () => {
    refresh({ silent: true });
    window.setTimeout(intentarAbrirServicioPendiente, 250);
  });

  window.EVNotificacionesGlobales = {
    init,
    refresh,
    stop: stopPolling,
    start: startPolling,
    abrirServicioPendiente: intentarAbrirServicioPendiente
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
