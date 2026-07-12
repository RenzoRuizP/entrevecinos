// views/js/notificaciones.js
// EV — Centro completo de notificaciones.
(function () {
  'use strict';

  const MODULE_KEY = '__EV_CENTRO_NOTIFICACIONES_V1__';
  if (window[MODULE_KEY] === true) {
    window.EVNotificacionesCentro?.init?.();
    return;
  }
  window[MODULE_KEY] = true;

  const BASE = String(window.BASE_URL || window.EV_BASE_URL || '').replace(/\/+$/, '');
  const FETCH_TIMEOUT_MS = 7000;
  const PENDING_SERVICE_KEY = 'ev_notificacion_servicio_pendiente';

  let page = 1;
  let total = 0;
  let size = 10;
  let cargando = false;
  let itemsById = new Map();
  let initialized = false;

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
      root: document.querySelector('.ev-notificaciones-page'),
      estado: document.getElementById('evNotifCentroEstado'),
      categoria: document.getElementById('evNotifCentroCategoria'),
      size: document.getElementById('evNotifCentroSize'),
      refresh: document.getElementById('btnNotifCentroRefresh'),
      markAll: document.getElementById('btnNotifCentroMarkAll'),
      list: document.getElementById('evNotifCentroList'),
      error: document.getElementById('evNotifCentroError'),
      countUnread: document.getElementById('evNotifCentroCountUnread'),
      countTotal: document.getElementById('evNotifCentroCountTotal'),
      pageLabel: document.getElementById('evNotifCentroPageLabel'),
      footerInfo: document.getElementById('evNotifCentroFooterInfo'),
      pagerInfo: document.getElementById('evNotifCentroPagerInfo'),
      prev: document.getElementById('btnNotifCentroPrev'),
      next: document.getElementById('btnNotifCentroNext')
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

    let basePath = '';
    try {
      basePath = new URL(BASE || '/', window.location.origin).pathname.replace(/\/+$/, '');
    } catch (_) {}

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
    if (categoria === 'comunidad') return '/comunidad';

    return '/MenuPrincipal';
  }

  function categoriaLabel(value) {
    const raw = String(value || '').toLowerCase();
    if (raw === 'servicio') return 'Servicios';
    if (raw === 'pedido' || raw === 'pedidos') return 'Pedidos';
    if (raw === 'residencia') return 'Residencia';
    if (raw === 'soporte') return 'Soporte';
    if (raw === 'comunidad') return 'Comunidad';
    return raw ? raw.charAt(0).toUpperCase() + raw.slice(1) : 'General';
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
    if (categoria === 'comunidad') return { icon: 'bi-people', tone: 'success' };
    return { icon: 'bi-bell', tone: 'neutral' };
  }

  function fechaLarga(value) {
    const raw = String(value || '').trim();
    if (!raw) return '—';

    const d = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return raw;

    return d.toLocaleString('es-PE', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function setLoading() {
    const { list } = refs();
    if (!list) return;
    list.innerHTML = `
      <div class="ev-notificaciones-loading">
        <span class="ev-notificaciones-spinner" aria-hidden="true"></span>
        <span>Cargando notificaciones...</span>
      </div>
    `;
  }

  function setEmpty() {
    const { list } = refs();
    if (!list) return;
    list.innerHTML = `
      <div class="ev-notificaciones-empty">
        <i class="bi bi-bell-slash"></i>
        <strong>No hay notificaciones para este filtro</strong>
        <span>Cuando tengas novedades de EV, aparecerán aquí.</span>
      </div>
    `;
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

  async function cargarCounts() {
    const { response, json } = await fetchJson(`${BASE}/api/notificaciones/counts?_=${Date.now()}`);
    if (!response.ok || json?.ok !== true) return 0;
    const data = json?.data || {};
    const totalUnread = Number(data.total ?? (
      Number(data.residencia || 0) +
      Number(data.soporte || 0) +
      Number(data.pedidos || 0) +
      Number(data.servicio || 0)
    ));
    return Math.max(0, totalUnread);
  }

  async function cargar() {
    const r = refs();
    if (!BASE || !r.root || cargando) return;

    cargando = true;
    r.error?.classList.add('d-none');
    setLoading();

    const estado = String(r.estado?.value || 'no_leida');
    const categoria = String(r.categoria?.value || 'all');
    size = Number(r.size?.value || 10);
    if (![10, 20, 30, 50].includes(size)) size = 10;

    try {
      const url = new URL(`${BASE}/api/notificaciones`, window.location.origin);
      url.searchParams.set('estado', estado);
      url.searchParams.set('categoria', categoria);
      url.searchParams.set('page', String(page));
      url.searchParams.set('size', String(size));
      url.searchParams.set('_', String(Date.now()));

      const [{ response, json }, totalUnread] = await Promise.all([
        fetchJson(url.toString()),
        cargarCounts()
      ]);

      if (!response.ok || json?.ok !== true) {
        throw new Error(json?.mensaje || 'No se pudieron cargar tus notificaciones.');
      }

      const rows = Array.isArray(json?.data) ? json.data : [];
      const meta = json?.meta || {};
      total = Number(meta.total || rows.length || 0);
      page = Math.max(1, Number(meta.page || page || 1));
      size = Math.max(1, Number(meta.size || size || 10));

      render(rows, totalUnread);
      actualizarPager();
      document.dispatchEvent(new CustomEvent('ev:notificaciones-globales-actualizar'));
    } catch (error) {
      console.error('[EV][CentroNotificaciones] cargar error:', error);
      r.error?.classList.remove('d-none');
      if (r.list) {
        r.list.innerHTML = `
          <div class="ev-notificaciones-empty">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>No se pudo cargar la bandeja</strong>
            <span>${escapeHtml(error?.message || 'Ocurrió un error inesperado.')}</span>
          </div>
        `;
      }
    } finally {
      cargando = false;
    }
  }

  function render(rows, totalUnread) {
    const r = refs();
    itemsById = new Map();

    if (r.countUnread) r.countUnread.textContent = String(totalUnread || 0);
    if (r.countTotal) r.countTotal.textContent = String(total || 0);
    if (r.pageLabel) r.pageLabel.textContent = String(page || 1);

    if (!Array.isArray(rows) || rows.length === 0) {
      setEmpty();
      return;
    }

    if (!r.list) return;

    r.list.innerHTML = rows.map((item) => {
      const id = Number(item?.codigo_notificacion || 0);
      const payload = parsePayload(item);
      const meta = iconoNotificacion(item);
      const unread = String(item?.estado || '') === 'no_leida';
      const title = String(item?.titulo || 'Nueva notificación').trim();
      const message = String(item?.mensaje || '').trim();
      const context = String(payload?.titulo_servicio || payload?.titulo_producto || payload?.nombre_publicacion || '').trim();
      const categoria = String(item?.categoria || '').trim();
      const sub = String(item?.subcategoria || '').trim();

      if (id > 0) itemsById.set(id, { ...item, payload });

      return `
        <article class="ev-notificaciones-item ${unread ? 'is-unread' : ''}" data-notificacion-id="${id}">
          <div class="ev-notificaciones-icon is-${escapeHtml(meta.tone)}" aria-hidden="true">
            <i class="bi ${escapeHtml(meta.icon)}"></i>
          </div>

          <div class="ev-notificaciones-copy">
            <div class="ev-notificaciones-top">
              <strong>${escapeHtml(title)}</strong>
              <span class="ev-notificaciones-badge ${unread ? 'ev-notificaciones-badge-unread' : 'ev-notificaciones-badge-read'}">
                <i class="bi ${unread ? 'bi-circle-fill' : 'bi-check2'}"></i>
                ${unread ? 'No leída' : 'Leída'}
              </span>
            </div>
            <p class="ev-notificaciones-message">${escapeHtml(message)}</p>
            ${context ? `<span class="ev-notificaciones-context">${escapeHtml(context)}</span>` : ''}
            <div class="ev-notificaciones-meta">
              <span class="ev-notificaciones-chip"><i class="bi bi-folder2-open"></i>${escapeHtml(categoriaLabel(categoria))}</span>
              ${sub ? `<span class="ev-notificaciones-chip"><i class="bi bi-tag"></i>${escapeHtml(sub.replace(/_/g, ' '))}</span>` : ''}
              <span class="ev-notificaciones-chip"><i class="bi bi-clock"></i>${escapeHtml(fechaLarga(item?.created_at))}</span>
            </div>
          </div>

          <div class="ev-notificaciones-row-actions">
            <button type="button" class="btn ev-notificaciones-open" data-accion="abrir" data-id="${id}">
              <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
            </button>
            <button type="button" class="btn ev-notificaciones-read" data-accion="leer" data-id="${id}" ${unread ? '' : 'disabled'}>
              <i class="bi bi-check2 me-1"></i>Marcar leída
            </button>
          </div>
        </article>
      `;
    }).join('');
  }

  function actualizarPager() {
    const r = refs();
    const totalPages = Math.max(1, Math.ceil(total / size));
    if (page > totalPages) page = totalPages;

    const start = total === 0 ? 0 : ((page - 1) * size) + 1;
    const end = Math.min(total, page * size);

    if (r.footerInfo) r.footerInfo.textContent = `Mostrando ${start} - ${end} de ${total}`;
    if (r.pagerInfo) r.pagerInfo.textContent = `${page} / ${totalPages}`;
    if (r.prev) r.prev.disabled = page <= 1 || cargando;
    if (r.next) r.next.disabled = page >= totalPages || cargando;
  }

  async function marcarLeida(id) {
    const codigo = Number(id || 0);
    if (codigo <= 0) return false;

    const { response, json } = await fetchJson(`${BASE}/api/notificaciones/${codigo}/leida`, {
      method: 'POST'
    });

    return response.ok && json?.ok === true;
  }

  async function marcarTodasLeidas() {
    const r = refs();
    const categoria = String(r.categoria?.value || 'all');
    const estado = String(r.estado?.value || 'all');

    if (estado === 'leida') {
      await avisar('info', 'Sin pendientes', 'El filtro actual solo muestra notificaciones leídas.');
      return;
    }

    const confirmado = await confirmar(
      'Marcar todas como leídas',
      categoria === 'all'
        ? 'Se marcarán como leídas todas tus notificaciones pendientes.'
        : 'Se marcarán como leídas las notificaciones pendientes de la categoría seleccionada.'
    );

    if (!confirmado) return;

    try {
      const body = new URLSearchParams();
      body.set('categoria', categoria);

      const { response, json } = await fetchJson(`${BASE}/api/notificaciones/marcar-todas-leidas`, {
        method: 'POST',
        body,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        }
      });

      if (!response.ok || json?.ok !== true) {
        throw new Error(json?.mensaje || 'No se pudieron marcar las notificaciones.');
      }

      await cargar();
      document.dispatchEvent(new CustomEvent('ev:notificaciones-globales-actualizar'));
    } catch (error) {
      await avisar('error', 'No se pudo completar', error?.message || 'Ocurrió un error inesperado.');
    }
  }

  async function avisar(icon, title, text) {
    if (window.Swal?.fire) {
      await Swal.fire({
        icon,
        title,
        text,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: icon === 'error' ? '#DC2626' : '#EA7C12',
        allowOutsideClick: false
      });
      return;
    }
    alert(`${title}\n\n${text}`);
  }

  async function confirmar(title, text) {
    if (!window.Swal?.fire) return window.confirm(`${title}\n\n${text}`);

    const res = await Swal.fire({
      icon: 'question',
      title,
      text,
      showCancelButton: true,
      confirmButtonText: 'Sí, continuar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12',
      cancelButtonColor: '#6B7280',
      allowOutsideClick: false
    });

    return !!res.isConfirmed;
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
    const payload = parsePayload(item);
    const ruta = rutaPorCategoria(item, payload);
    guardarServicioPendiente(item, payload, ruta);

    if (window.EVNav?.loadPage && ruta.startsWith('/')) {
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

  async function onListClick(event) {
    const button = event.target.closest('button[data-accion][data-id]');
    if (!button) return;

    event.preventDefault();

    const id = Number(button.dataset.id || 0);
    const accion = String(button.dataset.accion || '');
    const item = itemsById.get(id);
    if (!id || !item) return;

    button.disabled = true;

    try {
      if (accion === 'leer') {
        await marcarLeida(id);
        await cargar();
        document.dispatchEvent(new CustomEvent('ev:notificaciones-globales-actualizar'));
        return;
      }

      if (accion === 'abrir') {
        if (String(item.estado || '') === 'no_leida') {
          await marcarLeida(id);
        }
        document.dispatchEvent(new CustomEvent('ev:notificaciones-globales-actualizar'));
        await navegarNotificacion(item);
      }
    } catch (error) {
      await avisar('error', 'No se pudo completar', error?.message || 'Ocurrió un error inesperado.');
      button.disabled = false;
    }
  }

  function bind() {
    const r = refs();
    if (!r.root || r.root.dataset.evNotifCentroBound === '1') return;
    r.root.dataset.evNotifCentroBound = '1';

    r.estado?.addEventListener('change', () => { page = 1; cargar(); });
    r.categoria?.addEventListener('change', () => { page = 1; cargar(); });
    r.size?.addEventListener('change', () => { page = 1; cargar(); });
    r.refresh?.addEventListener('click', () => cargar());
    r.markAll?.addEventListener('click', () => marcarTodasLeidas());
    r.prev?.addEventListener('click', () => { if (page > 1) { page -= 1; cargar(); } });
    r.next?.addEventListener('click', () => {
      const totalPages = Math.max(1, Math.ceil(total / size));
      if (page < totalPages) { page += 1; cargar(); }
    });
    r.list?.addEventListener('click', onListClick);
  }

  function init() {
    const r = refs();
    if (!BASE || !r.root) return false;

    bind();
    if (!initialized) {
      initialized = true;
      page = 1;
    }
    cargar();
    window.setTimeout(intentarAbrirServicioPendiente, 350);
    return true;
  }

  window.EVNotificacionesCentro = {
    init,
    refresh: cargar
  };

  document.addEventListener('ev:content-loaded', () => {
    window.setTimeout(() => init(), 120);
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
