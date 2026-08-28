// views/js/notificacionesGlobales.js
// EV — Campana global optimizada del Centro de Notificaciones.
(function () {
  'use strict';

  if (window.__EV_NOTIFICACIONES_GLOBALES_V2__ === true) {
    window.EVNotificacionesGlobales?.refresh?.({ silent: true, includeItems: false });
    return;
  }
  window.__EV_NOTIFICACIONES_GLOBALES_V2__ = true;

  const BASE = String(window.EV?.baseUrl ?? window.BASE_URL ?? window.EV_BASE_URL ?? '').replace(/\/+$/, '');
  const POLLING_MS = 30000;
  const FETCH_TIMEOUT_MS = 8000;
  const MAX_ITEMS = 8;
  const PENDING_SERVICE_KEY = 'ev_notificacion_servicio_pendiente';
  const PENDING_ORDER_KEY = 'ev_notificacion_pedido_destino';
  const PENDING_PUBLICATION_KEY = 'ev_notificacion_publicacion_destino';
  const PENDING_WALLET_KEY = 'ev_notificacion_billetera_destino';
  const PENDING_SUPPORT_RESIDENCE_KEY = 'ev_notificacion_residencia_soporte_pendiente';

  const RUTAS_PERMITIDAS = new Set([
    '/MenuPrincipal', '/notificaciones', '/notificaciones-residencia', '/cuenta-observada',
    '/publicacion', '/billetera', '/comunidad', '/mis-pedidos-comprador',
    '/mis-pedidos-vendedor', '/mis-solicitudes-servicio-comprador',
    '/mis-solicitudes-servicio-vendedor', '/atender-servicios', '/atender-cuentas',
    '/atender-publicacion', '/atender-recargas'
  ]);

  const SUBCATEGORIAS_GESTION_SERVICIO = new Set([
    'cotizacion_final_aceptada', 'reprogramacion_propuesta', 'reprogramacion_aceptada',
    'reprogramacion_rechazada', 'reprogramacion_cancelada', 'servicio_iniciado',
    'servicio_realizado', 'servicio_marcado_realizado', 'servicio_confirmado',
    'problema_reportado', 'observacion_reportada', 'incidencia_respondida',
    'solucion_registrada', 'solucion_confirmada', 'problema_persiste',
    'revision_soporte_solicitada', 'revision_soporte_sugerida', 'resolucion_soporte',
    'actualizacion_soporte', 'calificacion_habilitada', 'servicio_cancelado',
    'servicio_cancelado_soporte'
  ]);

  let pollingTimer = null;
  let cargandoContador = false;
  let cargandoItems = false;
  let itemsCache = new Map();
  let totalNoLeidas = 0;

  function refs() {
    return {
      menu: document.getElementById('evNotificationMenu'),
      button: document.getElementById('evNotificationButton'),
      count: document.getElementById('evNotificationCount'),
      list: document.getElementById('evNotificationList'),
      summary: document.getElementById('evNotificationSummary'),
      refresh: document.getElementById('evNotificationRefresh'),
      viewAll: document.getElementById('evNotificationViewAll') || document.querySelector('[data-ev-notification-all="1"]')
    };
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  function parsePayload(item) {
    if (item?.payload && typeof item.payload === 'object' && !Array.isArray(item.payload)) return item.payload;
    const raw = String(item?.payload_json || '').trim();
    if (!raw) return {};
    try {
      const parsed = JSON.parse(raw);
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (_) {
      return {};
    }
  }

  function normalizarRutaInterna(route) {
    let value = String(route || '').trim();
    if (!value || /^[a-z][a-z0-9+.-]*:/i.test(value) || value.startsWith('//')) return '';
    if (!value.startsWith('/')) value = `/${value}`;

    let url;
    try { url = new URL(value, window.location.origin); } catch (_) { return ''; }
    if (url.origin !== window.location.origin) return '';

    let path = url.pathname.replace(/\/{2,}/g, '/');
    let basePath = '';
    try { basePath = new URL(BASE || '/', window.location.origin).pathname.replace(/\/+$/, ''); } catch (_) {}
    if (basePath && basePath !== '/' && path.startsWith(`${basePath}/`)) path = path.slice(basePath.length) || '/';
    if (!RUTAS_PERMITIDAS.has(path)) return '';
    return path + url.search;
  }

  function rutaPorCategoria(item, payload) {
    const directa = normalizarRutaInterna(payload?.ruta || '');
    if (directa) return directa;

    const categoria = String(item?.categoria || '').toLowerCase();
    const rolDestino = String(payload?.rol_destino || '').toLowerCase();
    if (categoria === 'servicio') {
      if (rolDestino === 'proveedor') return '/mis-solicitudes-servicio-vendedor';
      if (rolDestino === 'soporte') return '/atender-servicios';
      return '/mis-solicitudes-servicio-comprador';
    }
    if (categoria === 'pedido' || categoria === 'pedidos') return rolDestino === 'vendedor' ? '/mis-pedidos-vendedor' : '/mis-pedidos-comprador';
    if (categoria === 'cuenta') return String(item?.subcategoria || '').toLowerCase() === 'cuenta_observada' ? '/cuenta-observada' : '/MenuPrincipal';
    if (categoria === 'residencia') return '/notificaciones-residencia';
    if (categoria === 'publicacion') return rolDestino === 'soporte' ? '/atender-publicacion' : '/publicacion';
    if (categoria === 'billetera') return rolDestino === 'soporte' ? '/atender-recargas' : '/billetera';
    if (categoria === 'comunidad') return '/comunidad';
    if (categoria === 'soporte') {
      return String(item?.subcategoria || '').toLowerCase() === 'residencia_pendiente_soporte'
        ? '/atender-cuentas'
        : '/MenuPrincipal';
    }
    return '/MenuPrincipal';
  }

  function iconoNotificacion(item) {
    const categoria = String(item?.categoria || '').toLowerCase();
    const sub = String(item?.subcategoria || '').toLowerCase();
    if (categoria === 'cuenta') return { icon: sub.includes('aprobada') ? 'bi-person-check' : 'bi-person-exclamation', tone: sub.includes('aprobada') ? 'success' : 'warning' };
    if (categoria === 'residencia') return { icon: sub.includes('rechazada') ? 'bi-house-x' : 'bi-house-check', tone: sub.includes('aprobada') ? 'success' : (sub.includes('rechazada') ? 'danger' : 'warning') };
    if (categoria === 'publicacion') return { icon: 'bi-file-earmark-text', tone: sub.includes('aprobada') ? 'success' : (sub.includes('rechazada') ? 'danger' : 'warning') };
    if (categoria === 'billetera') return { icon: 'bi-wallet2', tone: sub.includes('aprobada') ? 'success' : (sub.includes('rechazada') ? 'danger' : 'warning') };
    if (categoria === 'pedido' || categoria === 'pedidos') return { icon: 'bi-bag-check', tone: sub.includes('cancel') || sub.includes('rechaz') ? 'danger' : 'success' };
    if (categoria === 'comunidad') return { icon: sub.includes('urgente') ? 'bi-megaphone-fill' : 'bi-people', tone: sub.includes('urgente') ? 'danger' : 'info' };
    if (categoria === 'soporte') return { icon: 'bi-headset', tone: 'info' };
    if (categoria === 'servicio') {
      if (sub.includes('reprogramacion') || sub.includes('cotizacion') || sub.includes('calificacion')) return { icon: sub.includes('calificacion') ? 'bi-star' : 'bi-calendar2-week', tone: 'warning' };
      if (sub.includes('problema') || sub.includes('incidencia') || sub.includes('observacion') || sub.includes('cancel')) return { icon: 'bi-exclamation-triangle', tone: 'danger' };
      if (sub.includes('solucion') || sub.includes('realizado') || sub.includes('confirmado')) return { icon: 'bi-clipboard2-check', tone: 'success' };
      return { icon: 'bi-briefcase', tone: 'info' };
    }
    return { icon: 'bi-bell', tone: 'neutral' };
  }

  function tiempoRelativo(value) {
    const raw = String(value || '').trim();
    if (!raw) return '';
    const date = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return raw;
    const minutes = Math.floor(Math.max(0, Date.now() - date.getTime()) / 60000);
    if (minutes < 1) return 'Ahora';
    if (minutes < 60) return `Hace ${minutes} min`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `Hace ${hours} ${hours === 1 ? 'hora' : 'horas'}`;
    const days = Math.floor(hours / 24);
    if (days <= 7) return `Hace ${days} ${days === 1 ? 'día' : 'días'}`;
    return date.toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined });
  }

  async function fetchJson(url, options = {}, timeoutMs = FETCH_TIMEOUT_MS) {
    const controller = new AbortController();
    const timer = window.setTimeout(() => controller.abort(), timeoutMs);
    try {
      const response = await fetch(url, {
        credentials: 'include', cache: 'no-store', ...options, signal: controller.signal,
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(options.headers || {}) }
      });
      const json = await response.json().catch(() => ({}));
      return { response, json };
    } finally {
      window.clearTimeout(timer);
    }
  }

  function setLoading() {
    const { list } = refs();
    if (list) list.innerHTML = '<div class="ev-notification-loading"><span class="ev-notification-spinner" aria-hidden="true"></span><span>Cargando notificaciones...</span></div>';
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
      button.setAttribute('aria-label', totalNoLeidas > 0 ? `Abrir notificaciones. ${totalNoLeidas} pendientes.` : 'Abrir notificaciones');
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
      list.innerHTML = '<div class="ev-notification-empty"><span><i class="bi bi-bell-slash"></i></span><strong>No hay notificaciones</strong><p>Las novedades de EV aparecerán aquí.</p></div>';
      return;
    }

    list.innerHTML = rows.map(item => {
      const id = Number(item?.codigo_notificacion || 0);
      const unread = String(item?.estado || '') === 'no_leida';
      const meta = iconoNotificacion(item);
      const payload = parsePayload(item);
      const contexto = String(payload?.titulo_servicio || payload?.titulo_producto || payload?.nombre_publicacion || '').trim();
      if (id > 0) itemsCache.set(id, { ...item, payload });
      return `
        <button type="button" class="ev-notification-item ${unread ? 'is-unread' : ''}" data-ev-notification-id="${id}">
          <span class="ev-notification-icon is-${escapeHtml(meta.tone)}"><i class="bi ${escapeHtml(meta.icon)}"></i></span>
          <span class="ev-notification-copy">
            <span class="ev-notification-title-row"><strong>${escapeHtml(item?.titulo || 'Nueva notificación')}</strong>${unread ? '<i class="ev-notification-unread-dot" aria-label="No leída"></i>' : ''}</span>
            <span class="ev-notification-message">${escapeHtml(item?.mensaje || '')}</span>
            ${contexto ? `<span class="ev-notification-context">${escapeHtml(contexto)}</span>` : ''}
            <time>${escapeHtml(tiempoRelativo(item?.created_at))}</time>
          </span>
          <i class="bi bi-chevron-right ev-notification-chevron" aria-hidden="true"></i>
        </button>`;
    }).join('');
  }

  async function cargarContador() {
    if (cargandoContador) return;
    cargandoContador = true;
    try {
      const { response, json } = await fetchJson(`${BASE}/api/notificaciones/resumen?incluir_items=0&_=${Date.now()}`);
      if (response.ok && json?.ok === true) {
        actualizarContador(Number(json?.data?.total || 0));
        window.EVSidebarCommunity?.refresh?.({ silent: true });
      }
    } finally {
      cargandoContador = false;
    }
  }

  async function cargarItems(options = {}) {
    if (cargandoItems) return;
    cargandoItems = true;
    if (options.silent !== true) setLoading();
    try {
      const { response, json } = await fetchJson(`${BASE}/api/notificaciones/resumen?incluir_items=1&limite=${MAX_ITEMS}&_=${Date.now()}`);
      if (!response.ok || json?.ok !== true) throw new Error(json?.mensaje || 'No se pudieron cargar las notificaciones.');
      actualizarContador(Number(json?.data?.total || 0));
      renderLista(Array.isArray(json?.data?.items) ? json.data.items : []);
    } catch (error) {
      const { list } = refs();
      if (list) list.innerHTML = `<div class="ev-notification-error"><i class="bi bi-exclamation-circle"></i><span>${escapeHtml(error?.message || 'No se pudieron cargar las notificaciones.')}</span></div>`;
    } finally {
      cargandoItems = false;
    }
  }

  async function refresh(options = {}) {
    await cargarContador();
    if (options.includeItems === true) await cargarItems({ silent: options.silent === true });
  }

  async function marcarLeida(id) {
    const codigo = Number(id || 0);
    if (codigo <= 0) return false;
    try {
      const { response, json } = await fetchJson(`${BASE}/api/notificaciones/${codigo}/leida`, { method: 'POST' });
      return response.ok && json?.ok === true;
    } catch (_) {
      return false;
    }
  }

  function cerrarDropdown() {
    const { button } = refs();
    if (!button || !window.bootstrap?.Dropdown) return;
    try { window.bootstrap.Dropdown.getOrCreateInstance(button).hide(); } catch (_) {}
  }

  function guardarDestinoEntidad(item, payload, ruta) {
    const categoria = String(item?.categoria || '').trim().toLowerCase();
    const referenciaId = Number(item?.referencia_id || 0);
    const now = Date.now();

    try {
      if (categoria === 'pedido' || categoria === 'pedidos') {
        const codigoPedido = Number(payload?.codigo_pedido || payload?.id_pedido || referenciaId || 0);
        if (codigoPedido > 0) {
          const rolDestino = String(payload?.rol_destino || '').trim().toLowerCase();
          const rol = ruta === '/mis-pedidos-vendedor' || rolDestino === 'vendedor'
            ? 'vendedor'
            : 'comprador';
          sessionStorage.setItem(PENDING_ORDER_KEY, JSON.stringify({
            codigo_pedido: codigoPedido,
            rol,
            created_at: now
          }));
        }
      }

      if (categoria === 'publicacion') {
        const codigoProducto = Number(payload?.codigo_producto || payload?.codigo_publicacion || referenciaId || 0);
        if (codigoProducto > 0 && (ruta === '/publicacion' || ruta === '/atender-publicacion')) {
          sessionStorage.setItem(PENDING_PUBLICATION_KEY, JSON.stringify({
            codigo_producto: codigoProducto,
            ruta,
            created_at: now
          }));
        }
      }

      if (categoria === 'billetera') {
        const codigoRecarga = Number(payload?.codigo_recarga || payload?.recarga_id || referenciaId || 0);
        if (codigoRecarga > 0 && ruta === '/billetera') {
          sessionStorage.setItem(PENDING_WALLET_KEY, JSON.stringify({
            codigo_recarga: codigoRecarga,
            created_at: now
          }));
        }
      }
    } catch (_) {}
  }

  function guardarResidenciaSoportePendiente(item, payload, ruta) {
    const categoria = String(item?.categoria || '').toLowerCase();
    const subcategoria = String(item?.subcategoria || '').toLowerCase();
    const codigoSolicitud = Number(payload?.codigo_solicitud || item?.referencia_id || 0);

    if (
      categoria !== 'soporte'
      || subcategoria !== 'residencia_pendiente_soporte'
      || codigoSolicitud <= 0
    ) {
      return false;
    }

    try {
      sessionStorage.setItem(PENDING_SUPPORT_RESIDENCE_KEY, JSON.stringify({
        codigo_solicitud: codigoSolicitud,
        modo: 'residencias',
        estado: 'revision',
        ruta,
        created_at: Date.now()
      }));
      return true;
    } catch (_) {
      return false;
    }
  }

  function guardarServicioPendiente(item, payload, ruta) {
    const categoria = String(item?.categoria || '').toLowerCase();
    const subcategoria = String(item?.subcategoria || '').toLowerCase();
    const codigoSolicitud = Number(payload?.codigo_solicitud_servicio || (categoria === 'servicio' ? item?.referencia_id : 0) || 0);
    if (categoria !== 'servicio' || codigoSolicitud <= 0 || !SUBCATEGORIAS_GESTION_SERVICIO.has(subcategoria)) return false;
    try {
      sessionStorage.setItem(PENDING_SERVICE_KEY, JSON.stringify({ codigo_solicitud_servicio: codigoSolicitud, ruta, created_at: Date.now() }));
      return true;
    } catch (_) { return false; }
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
    } catch (_) { return null; }
  }

  async function intentarAbrirServicioPendiente() {
    const pending = leerServicioPendiente();
    if (!pending || !document.querySelector('.ev-ssc-page, .ev-ssv-page')) return false;
    const deadline = Date.now() + 3500;
    while (Date.now() < deadline) {
      if (window.EVServicioOperacion?.open) {
        try {
          sessionStorage.removeItem(PENDING_SERVICE_KEY);
          await window.EVServicioOperacion.open(Number(pending.codigo_solicitud_servicio));
          return true;
        } catch (_) { return false; }
      }
      await new Promise(resolve => window.setTimeout(resolve, 120));
    }
    return false;
  }

  async function navegarNotificacion(item) {
    const payload = parsePayload(item);
    const ruta = rutaPorCategoria(item, payload);
    guardarDestinoEntidad(item, payload, ruta);
    guardarServicioPendiente(item, payload, ruta);
    guardarResidenciaSoportePendiente(item, payload, ruta);
    cerrarDropdown();
    if (window.EVNav && typeof window.EVNav.loadPage === 'function') {
      await window.EVNav.loadPage(ruta, { pushState: true, replaceState: false });
      await intentarAbrirServicioPendiente();
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
    if (String(item.estado || '') === 'no_leida' && await marcarLeida(id)) {
      item.estado = 'leida';
      actualizarContador(Math.max(0, totalNoLeidas - 1));
    }
    await navegarNotificacion(item);
    window.setTimeout(() => refresh({ silent: true, includeItems: false }), 250);
  }

  async function irCentroNotificaciones(event) {
    event?.preventDefault();
    event?.stopPropagation();
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
    r.menu?.addEventListener('show.bs.dropdown', () => cargarItems({ silent: true }));
    r.refresh?.addEventListener('click', event => {
      event.preventDefault(); event.stopPropagation(); cargarItems({ silent: false });
    });
    r.viewAll?.addEventListener('click', irCentroNotificaciones);
    r.list?.addEventListener('click', onNotificationClick);
  }

  function startPolling() {
    stopPolling();
    pollingTimer = window.setInterval(() => {
      if (!document.hidden) cargarContador();
    }, POLLING_MS);
  }

  function stopPolling() {
    if (pollingTimer) window.clearInterval(pollingTimer);
    pollingTimer = null;
  }

  function init() {
    if (!refs().button) return false;
    bind();
    cargarContador();
    startPolling();
    window.setTimeout(intentarAbrirServicioPendiente, 300);
    return true;
  }

  document.addEventListener('ev:notificaciones-globales-actualizar', () => refresh({ silent: true, includeItems: false }));
  document.addEventListener('ev:content-loaded', () => window.setTimeout(intentarAbrirServicioPendiente, 180));
  document.addEventListener('visibilitychange', () => { if (!document.hidden) cargarContador(); });
  window.addEventListener('pageshow', () => {
    cargarContador();
    window.setTimeout(intentarAbrirServicioPendiente, 250);
  });

  window.EVNotificacionesGlobales = {
    init, refresh, stop: stopPolling, start: startPolling, abrirServicioPendiente: intentarAbrirServicioPendiente
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
