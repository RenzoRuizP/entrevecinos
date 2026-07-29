// views/js/notificaciones.js
// EV — Centro completo de notificaciones (Punto 13).
(function () {
  'use strict';

  const MODULE_KEY = '__EV_CENTRO_NOTIFICACIONES_V2__';
  if (window[MODULE_KEY] === true) {
    window.EVNotificacionesCentro?.init?.();
    return;
  }
  window[MODULE_KEY] = true;

  const BASE = String(window.EV?.baseUrl ?? window.BASE_URL ?? window.EV_BASE_URL ?? '').replace(/\/+$/, '');
  const FETCH_TIMEOUT_MS = 8000;
  const PENDING_SERVICE_KEY = 'ev_notificacion_servicio_pendiente';

  const RUTAS_PERMITIDAS = new Set([
    '/MenuPrincipal',
    '/notificaciones',
    '/notificaciones-residencia',
    '/cuenta-observada',
    '/publicacion',
    '/billetera',
    '/comunidad',
    '/mis-pedidos-comprador',
    '/mis-pedidos-vendedor',
    '/mis-solicitudes-servicio-comprador',
    '/mis-solicitudes-servicio-vendedor',
    '/atender-servicios'
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

  const SUBCATEGORIA_LABELS = {
    cuenta_observada: 'Cuenta observada',
    cuenta_aprobada: 'Cuenta aprobada',
    cuenta_inactivada: 'Cuenta inactivada',
    residencia_aprobada: 'Residencia aprobada',
    residencia_observada: 'Residencia observada',
    residencia_rechazada: 'Residencia rechazada',
    publicacion_aprobada: 'Publicación aprobada',
    publicacion_observada: 'Publicación observada',
    publicacion_rechazada: 'Publicación rechazada',
    recarga_aprobada: 'Recarga aprobada',
    recarga_observada: 'Recarga observada',
    recarga_rechazada: 'Recarga rechazada',
    nueva_solicitud: 'Nueva solicitud',
    mensaje_conversacion: 'Nuevo mensaje',
    cotizacion_final_aceptada: 'Cotización aceptada',
    cotizacion_final_rechazada: 'Cotización rechazada',
    reprogramacion_propuesta: 'Reprogramación propuesta',
    reprogramacion_aceptada: 'Reprogramación aceptada',
    reprogramacion_rechazada: 'Reprogramación rechazada',
    servicio_iniciado: 'Servicio iniciado',
    servicio_realizado: 'Servicio realizado',
    servicio_marcado_realizado: 'Servicio realizado',
    servicio_confirmado: 'Servicio confirmado',
    problema_reportado: 'Problema reportado',
    observacion_reportada: 'Observación reportada',
    incidencia_respondida: 'Incidencia respondida',
    solucion_registrada: 'Solución registrada',
    solucion_confirmada: 'Solución confirmada',
    problema_persiste: 'Problema pendiente',
    revision_soporte_solicitada: 'Revisión de soporte',
    revision_soporte_sugerida: 'Revisión recomendada',
    resolucion_soporte: 'Resolución de soporte',
    actualizacion_soporte: 'Actualización de soporte',
    calificacion_habilitada: 'Calificación pendiente',
    servicio_cancelado: 'Servicio cancelado',
    servicio_cancelado_soporte: 'Cancelado por soporte',
    comunidad_comunicado: 'Comunicado',
    comunidad_noticia: 'Noticia',
    comunidad_evento: 'Evento',
    comunidad_urgente: 'Aviso urgente'
  };

  let page = 1;
  let total = 0;
  let size = 10;
  let cargando = false;
  let initialized = false;
  let itemsById = new Map();
  let resumenActual = { total: 0, categorias: {} };

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
      categoryActive: document.getElementById('evNotifCentroCategoryActive'),
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

  function normalizarRutaInterna(route) {
    let value = String(route || '').trim();
    if (!value || /^[a-z][a-z0-9+.-]*:/i.test(value) || value.startsWith('//')) return '';

    if (!value.startsWith('/')) value = `/${value}`;
    let url;
    try {
      url = new URL(value, window.location.origin);
    } catch (_) {
      return '';
    }
    if (url.origin !== window.location.origin) return '';

    let path = url.pathname.replace(/\/{2,}/g, '/');
    let basePath = '';
    try {
      basePath = new URL(BASE || '/', window.location.origin).pathname.replace(/\/+$/, '');
    } catch (_) {}
    if (basePath && basePath !== '/' && path.startsWith(`${basePath}/`)) {
      path = path.slice(basePath.length) || '/';
    }
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
    if (categoria === 'pedido' || categoria === 'pedidos') {
      return rolDestino === 'vendedor' ? '/mis-pedidos-vendedor' : '/mis-pedidos-comprador';
    }
    if (categoria === 'cuenta') {
      return String(item?.subcategoria || '').toLowerCase() === 'cuenta_observada'
        ? '/cuenta-observada'
        : '/MenuPrincipal';
    }
    if (categoria === 'residencia') return '/notificaciones-residencia';
    if (categoria === 'publicacion') return '/publicacion';
    if (categoria === 'billetera') return '/billetera';
    if (categoria === 'comunidad') return '/comunidad';
    if (categoria === 'soporte') return '/MenuPrincipal';
    return '/MenuPrincipal';
  }

  function categoriaLabel(value) {
    const raw = String(value || '').toLowerCase();
    const labels = {
      cuenta: 'Cuenta', residencia: 'Residencia', publicacion: 'Publicaciones',
      billetera: 'Billetera', pedido: 'Pedidos', pedidos: 'Pedidos',
      servicio: 'Servicios', comunidad: 'Comunidad', soporte: 'Soporte'
    };
    return labels[raw] || 'General';
  }

  function subcategoriaLabel(value) {
    const raw = String(value || '').toLowerCase();
    if (!raw) return '';
    if (SUBCATEGORIA_LABELS[raw]) return SUBCATEGORIA_LABELS[raw];
    return raw.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
  }

  function iconoNotificacion(item) {
    const categoria = String(item?.categoria || '').toLowerCase();
    const sub = String(item?.subcategoria || '').toLowerCase();

    if (categoria === 'cuenta') return { icon: sub.includes('aprobada') ? 'bi-person-check' : 'bi-person-exclamation', tone: sub.includes('aprobada') ? 'success' : 'warning' };
    if (categoria === 'residencia') return { icon: sub.includes('rechazada') ? 'bi-house-x' : 'bi-house-check', tone: sub.includes('aprobada') ? 'success' : (sub.includes('rechazada') ? 'danger' : 'warning') };
    if (categoria === 'publicacion') return { icon: sub.includes('aprobada') ? 'bi-file-earmark-check' : 'bi-file-earmark-text', tone: sub.includes('aprobada') ? 'success' : (sub.includes('rechazada') ? 'danger' : 'warning') };
    if (categoria === 'billetera') return { icon: 'bi-wallet2', tone: sub.includes('aprobada') ? 'success' : (sub.includes('rechazada') ? 'danger' : 'warning') };
    if (categoria === 'pedido' || categoria === 'pedidos') return { icon: 'bi-bag-check', tone: sub.includes('cancel') || sub.includes('rechaz') ? 'danger' : 'success' };
    if (categoria === 'comunidad') return { icon: sub.includes('urgente') ? 'bi-megaphone-fill' : 'bi-people', tone: sub.includes('urgente') ? 'danger' : 'info' };
    if (categoria === 'soporte') return { icon: 'bi-headset', tone: 'info' };
    if (categoria === 'servicio') {
      if (sub.includes('reprogramacion') || sub.includes('cotizacion') || sub.includes('calificacion')) return { icon: sub.includes('calificacion') ? 'bi-star' : 'bi-calendar2-week', tone: 'warning' };
      if (sub.includes('problema') || sub.includes('incidencia') || sub.includes('observacion') || sub.includes('cancel')) return { icon: 'bi-exclamation-triangle', tone: 'danger' };
      if (sub.includes('solucion') || sub.includes('realizado') || sub.includes('confirmado')) return { icon: 'bi-clipboard2-check', tone: 'success' };
      if (sub.includes('soporte')) return { icon: 'bi-headset', tone: 'info' };
      return { icon: 'bi-briefcase', tone: 'info' };
    }
    return { icon: 'bi-bell', tone: 'neutral' };
  }

  function fechaLarga(value) {
    const raw = String(value || '').trim();
    if (!raw) return '—';
    const date = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return raw;
    return date.toLocaleString('es-PE', {
      day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
  }

  async function fetchJson(url, options = {}, timeoutMs = FETCH_TIMEOUT_MS) {
    const controller = new AbortController();
    const timer = window.setTimeout(() => controller.abort(), timeoutMs);
    try {
      const response = await fetch(url, {
        credentials: 'include', cache: 'no-store', ...options, signal: controller.signal,
        headers: {
          Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(options.headers || {})
        }
      });
      const json = await response.json().catch(() => ({}));
      return { response, json };
    } finally {
      window.clearTimeout(timer);
    }
  }

  function setLoading() {
    const { list } = refs();
    if (!list) return;
    list.innerHTML = '<div class="ev-notificaciones-loading"><span class="ev-notificaciones-spinner" aria-hidden="true"></span><span>Cargando notificaciones...</span></div>';
  }

  function setEmpty() {
    const r = refs();
    if (!r.list) return;
    const estado = String(r.estado?.value || 'no_leida');
    const contenido = estado === 'no_leida'
      ? ['bi-check2-circle', 'No tienes notificaciones pendientes', 'Estás al día para el filtro seleccionado.']
      : ['bi-bell-slash', 'No existe historial para este filtro', 'Cuando EV registre una novedad, aparecerá en esta bandeja.'];
    r.list.innerHTML = `<div class="ev-notificaciones-empty"><i class="bi ${contenido[0]}"></i><strong>${contenido[1]}</strong><span>${contenido[2]}</span></div>`;
  }

  function pendientesFiltro(categoria) {
    const c = resumenActual?.categorias || {};
    if (categoria === 'all') return Number(resumenActual?.total || 0);
    if (categoria === 'cuenta_residencia') return Number(c.cuenta || 0) + Number(c.residencia || 0);
    if (categoria === 'billetera_recargas') return Number(c.billetera || 0);
    if (categoria === 'pedidos') categoria = 'pedido';
    return Number(c[categoria] || 0);
  }

  function actualizarResumen() {
    const r = refs();
    const totalNoLeidas = Number(resumenActual?.total || 0);
    const categoriasActivas = Object.values(resumenActual?.categorias || {}).filter(v => Number(v) > 0).length;
    const categoria = String(r.categoria?.value || 'all');
    const estado = String(r.estado?.value || 'no_leida');

    if (r.countUnread) r.countUnread.textContent = totalNoLeidas > 99 ? '99+' : String(totalNoLeidas);
    if (r.countTotal) r.countTotal.textContent = String(total || 0);
    if (r.categoryActive) r.categoryActive.textContent = String(categoriasActivas);
    if (r.markAll) r.markAll.disabled = cargando || estado === 'leida' || pendientesFiltro(categoria) <= 0;
  }

  function render(rows) {
    const r = refs();
    itemsById = new Map();
    if (!Array.isArray(rows) || rows.length === 0) {
      setEmpty();
      actualizarResumen();
      return;
    }
    if (!r.list) return;

    r.list.innerHTML = rows.map(item => {
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
          <div class="ev-notificaciones-icon is-${escapeHtml(meta.tone)}" aria-hidden="true"><i class="bi ${escapeHtml(meta.icon)}"></i></div>
          <div class="ev-notificaciones-copy">
            <div class="ev-notificaciones-top">
              <strong>${escapeHtml(title)}</strong>
              <span class="ev-notificaciones-badge ${unread ? 'ev-notificaciones-badge-unread' : 'ev-notificaciones-badge-read'}">
                <i class="bi ${unread ? 'bi-circle-fill' : 'bi-check2'}"></i>${unread ? 'No leída' : 'Leída'}
              </span>
            </div>
            <p class="ev-notificaciones-message">${escapeHtml(message)}</p>
            ${context ? `<span class="ev-notificaciones-context">${escapeHtml(context)}</span>` : ''}
            <div class="ev-notificaciones-meta">
              <span class="ev-notificaciones-chip"><i class="bi bi-folder2-open"></i>${escapeHtml(categoriaLabel(categoria))}</span>
              ${sub ? `<span class="ev-notificaciones-chip ev-notificaciones-subcategory"><i class="bi bi-tag"></i>${escapeHtml(subcategoriaLabel(sub))}</span>` : ''}
              <span class="ev-notificaciones-chip"><i class="bi bi-clock"></i>${escapeHtml(fechaLarga(item?.created_at))}</span>
            </div>
          </div>
          <div class="ev-notificaciones-row-actions">
            <button type="button" class="btn ev-notificaciones-open" data-accion="abrir" data-id="${id}"><i class="bi bi-box-arrow-up-right me-1"></i>Abrir</button>
            <button type="button" class="btn ev-notificaciones-read" data-accion="leer" data-id="${id}" ${unread ? '' : 'disabled'}><i class="bi bi-check2 me-1"></i>Marcar leída</button>
          </div>
        </article>`;
    }).join('');
    actualizarResumen();
  }

  function actualizarPager() {
    const r = refs();
    const totalPages = Math.max(1, Math.ceil(total / size));
    if (page > totalPages) page = totalPages;
    const start = total === 0 ? 0 : ((page - 1) * size) + 1;
    const end = Math.min(total, page * size);
    if (r.footerInfo) r.footerInfo.textContent = total === 0 ? 'Mostrando 0 de 0' : `Mostrando ${start} - ${end} de ${total}`;
    if (r.pagerInfo) r.pagerInfo.textContent = `${page} / ${totalPages}`;
    if (r.prev) r.prev.disabled = page <= 1 || cargando;
    if (r.next) r.next.disabled = page >= totalPages || cargando;
  }

  async function cargar() {
    const r = refs();
    if (!r.root || cargando) return;
    cargando = true;
    r.error?.classList.add('d-none');
    setLoading();
    actualizarResumen();

    const estado = String(r.estado?.value || 'no_leida');
    const categoria = String(r.categoria?.value || 'all');
    size = Number(r.size?.value || 10);
    if (![10, 20, 30, 50].includes(size)) size = 10;

    try {
      const listUrl = new URL(`${BASE}/api/notificaciones`, window.location.origin);
      listUrl.searchParams.set('estado', estado);
      listUrl.searchParams.set('categoria', categoria);
      listUrl.searchParams.set('page', String(page));
      listUrl.searchParams.set('size', String(size));
      listUrl.searchParams.set('_', String(Date.now()));

      const summaryUrl = `${BASE}/api/notificaciones/resumen?incluir_items=0&_=${Date.now()}`;
      const [listRes, summaryRes] = await Promise.all([fetchJson(listUrl.toString()), fetchJson(summaryUrl)]);
      if (!listRes.response.ok || listRes.json?.ok !== true) throw new Error(listRes.json?.mensaje || 'No se pudieron cargar tus notificaciones.');
      if (!summaryRes.response.ok || summaryRes.json?.ok !== true) throw new Error(summaryRes.json?.mensaje || 'No se pudo cargar el resumen.');

      const rows = Array.isArray(listRes.json?.data) ? listRes.json.data : [];
      const meta = listRes.json?.meta || {};
      total = Number(meta.total || 0);
      page = Math.max(1, Number(meta.page || page));
      size = Math.max(1, Number(meta.size || size));
      resumenActual = summaryRes.json?.data || { total: 0, categorias: {} };

      render(rows);
      actualizarPager();
      document.dispatchEvent(new CustomEvent('ev:notificaciones-globales-actualizar'));
    } catch (error) {
      console.error('[EV][CentroNotificaciones] cargar:', error);
      r.error?.classList.remove('d-none');
      if (r.list) r.list.innerHTML = `<div class="ev-notificaciones-empty"><i class="bi bi-exclamation-triangle"></i><strong>No se pudo cargar la bandeja</strong><span>${escapeHtml(error?.message || 'Ocurrió un error inesperado.')}</span></div>`;
    } finally {
      cargando = false;
      actualizarResumen();
      actualizarPager();
    }
  }

  async function marcarLeida(id) {
    const codigo = Number(id || 0);
    if (codigo <= 0) return false;
    const { response, json } = await fetchJson(`${BASE}/api/notificaciones/${codigo}/leida`, { method: 'POST' });
    return response.ok && json?.ok === true;
  }

  async function avisar(icon, title, text) {
    if (window.Swal?.fire) {
      await Swal.fire({
        icon, title, text, confirmButtonText: 'Aceptar',
        confirmButtonColor: icon === 'error' ? '#DC2626' : (icon === 'success' ? '#0E7A43' : '#EA7C12'),
        allowOutsideClick: false
      });
      return;
    }
    window.alert(`${title}\n\n${text}`);
  }

  async function confirmar(title, text) {
    if (!window.Swal?.fire) return window.confirm(`${title}\n\n${text}`);
    const res = await Swal.fire({
      icon: 'question', title, text, showCancelButton: true,
      confirmButtonText: 'Sí, continuar', cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12', cancelButtonColor: '#6B7280', allowOutsideClick: false
    });
    return Boolean(res.isConfirmed);
  }

  async function marcarTodasLeidas() {
    const r = refs();
    const categoria = String(r.categoria?.value || 'all');
    const estado = String(r.estado?.value || 'no_leida');
    if (estado === 'leida' || pendientesFiltro(categoria) <= 0) {
      await avisar('info', 'Sin pendientes', 'No hay notificaciones pendientes para el filtro seleccionado.');
      return;
    }
    const ok = await confirmar('Marcar todas como leídas', categoria === 'all'
      ? 'Se marcarán como leídas todas tus notificaciones pendientes.'
      : 'Se marcarán como leídas las notificaciones pendientes de la categoría seleccionada.');
    if (!ok) return;

    try {
      const body = new URLSearchParams({ categoria });
      const { response, json } = await fetchJson(`${BASE}/api/notificaciones/leer-todas`, {
        method: 'POST', body,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
      });
      if (!response.ok || json?.ok !== true) throw new Error(json?.mensaje || 'No se pudieron marcar las notificaciones.');
      page = 1;
      await cargar();
    } catch (error) {
      await avisar('error', 'No se pudo completar', error?.message || 'Ocurrió un error inesperado.');
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
    if (!pending || !document.querySelector('.ev-ssc-page, .ev-ssv-page')) return false;
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
      await new Promise(resolve => window.setTimeout(resolve, 120));
    }
    return false;
  }

  async function navegarNotificacion(item) {
    const payload = parsePayload(item);
    const ruta = rutaPorCategoria(item, payload);
    guardarServicioPendiente(item, payload, ruta);
    if (window.EVNav && typeof window.EVNav.loadPage === 'function') {
      await window.EVNav.loadPage(ruta, { pushState: true, replaceState: false });
      await intentarAbrirServicioPendiente();
      return;
    }
    window.location.href = `${BASE}/MenuPrincipal?ev_goto=${encodeURIComponent(ruta)}`;
  }

  async function onListClick(event) {
    const button = event.target.closest('button[data-accion][data-id]');
    if (!button) return;
    const id = Number(button.dataset.id || 0);
    const accion = String(button.dataset.accion || '');
    const item = itemsById.get(id);
    if (!item) return;

    button.disabled = true;
    try {
      if (String(item.estado || '') === 'no_leida') {
        const ok = await marcarLeida(id);
        if (!ok) throw new Error('No se pudo marcar la notificación como leída.');
        item.estado = 'leida';
      }
      if (accion === 'abrir') {
        await navegarNotificacion(item);
      } else {
        await cargar();
      }
    } catch (error) {
      button.disabled = false;
      await avisar('error', 'No se pudo completar', error?.message || 'Ocurrió un error inesperado.');
    }
  }

  function bind() {
    const r = refs();
    if (!r.root || r.root.dataset.evNotifBound === '1') return;
    r.root.dataset.evNotifBound = '1';

    r.refresh?.addEventListener('click', () => cargar());
    r.markAll?.addEventListener('click', marcarTodasLeidas);
    r.list?.addEventListener('click', onListClick);

    r.estado?.addEventListener('change', () => { page = 1; cargar(); });
    r.categoria?.addEventListener('change', () => { page = 1; cargar(); });
    r.size?.addEventListener('change', () => { page = 1; cargar(); });
    r.prev?.addEventListener('click', () => { if (page > 1) { page -= 1; cargar(); } });
    r.next?.addEventListener('click', () => {
      const totalPages = Math.max(1, Math.ceil(total / size));
      if (page < totalPages) { page += 1; cargar(); }
    });
  }

  function init() {
    if (!refs().root) return false;
    bind();
    if (!initialized) {
      initialized = true;
      cargar();
    }
    window.setTimeout(intentarAbrirServicioPendiente, 250);
    return true;
  }

  document.addEventListener('ev:content-loaded', () => {
    initialized = false;
    window.setTimeout(init, 80);
  });

  window.EVNotificacionesCentro = { init, reload: cargar, abrirServicioPendiente: intentarAbrirServicioPendiente };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
