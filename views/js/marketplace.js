/* views/js/marketplace.js */
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').toString().replace(/\/+$/, '');
  const LOG_PREFIX = '[MARKETPLACE]';

  /*
    Polling Marketplace:
    - Refresca disponibilidad/publicaciones solo cuando la vista Marketplace está activa.
    - No corre mientras el usuario interactúa con el sidebar.
    - Evita llamadas solapadas.
    - Pausa cuando la pestaña está oculta.
  */
  const MARKETPLACE_POLLING_MS = 15000;
  const MARKETPLACE_IDLE_POLLING_MS = 24000;
  const FETCH_TIMEOUT_MS = 8000;
  const UI_PAUSE_MS = 1400;

  const SOLICITUD_POLLING_MS = 5000;
  const SEGUNDOS_CANCELACION_SOLICITUD = 120;
  const SEGUNDOS_TIMEOUT_SOLICITUD = 240;

  const CONDO_NOMBRE_RESUMEN = (typeof window !== 'undefined' && window.EV_CONDOMINIO_NOMBRE)
    ? window.EV_CONDOMINIO_NOMBRE
    : 'tu condominio';

  let refs = {
    gridAllWrapper: null,
    gridServicios: null,
    gridProductos: null,
    resumenResultados: null,
    searchInput: null,
    emptyState: null,
    selectOrdenar: null,
    customOrdenar: null,
    scopeButtons: [],
    selectCategoriaProductos: null,
    customCategoria: null,
    categoriaLabel: null,
    countServicios: null,
    countProductos: null,
    emptyServicios: null,
    emptyProductos: null,
    wrapServicios: null,
    wrapProductos: null,
    wrapCategoriaProductos: null
  };

  let publicaciones = [];
  let textoBusqueda = '';
  let criterioOrden = 'recientes';
  let scope = 'todos';
  let categoriaFiltroValor = '0';

  let tipoIdProducto = 0;
  let tipoIdServicio = 0;

  let pollingTimer = null;
  let pollingEnCurso = false;
  let marketplaceInicializado = false;
  let restaurandoSolicitudActiva = false;
  let ultimaInteraccionUi = 0;
  let ultimoPollingMarketplaceAt = 0;

  let solicitudFlow = {
    codigoPedido: 0,
    pollingTimer: null,
    intervalUi: null,
    activo: false,
    cancelButtonVisible: false,
    modo: '',
    segundosRestantes: 0,
    segundosParaCancelarRestantes: SEGUNDOS_CANCELACION_SOLICITUD
  };


  let reputacionVendedoresCache = new Map();

  function log()  { if (console && console.log)  console.log(LOG_PREFIX, ...arguments); }
  function warn() { if (console && console.warn) console.warn(LOG_PREFIX, ...arguments); }
  function err()  { if (console && console.error) console.error(LOG_PREFIX, ...arguments); }

  function nowMs() {
    return Date.now();
  }

  function marcarInteraccionUi() {
    ultimaInteraccionUi = nowMs();

    if (window.EVPollingControl && typeof window.EVPollingControl.pauseBriefly === 'function') {
      window.EVPollingControl.pauseBriefly();
    }
  }

  function estaPausadoPorUi() {
    return (nowMs() - ultimaInteraccionUi) < UI_PAUSE_MS;
  }

  function estaVistaMarketplaceActiva() {
    return !!document.getElementById('mp_grid_publicaciones');
  }

  async function fetchJsonRobusto(url, opts = {}, timeoutMs = FETCH_TIMEOUT_MS) {
    const ctrl = new AbortController();
    const timeoutId = window.setTimeout(() => ctrl.abort(), timeoutMs);

    try {
      const resp = await fetch(url, {
        ...opts,
        signal: ctrl.signal
      });

      const text = await resp.text();
      let json = null;

      try {
        json = text ? JSON.parse(text) : {};
      } catch (_) {
        json = null;
      }

      return { resp, text, json };
    } finally {
      window.clearTimeout(timeoutId);
    }
  }

  function normalizar(str) {
    return (str || '')
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }

  function formatPrecio(valor) {
    const n = Number(valor || 0);
    if (!isFinite(n)) return 'S/ 0.00';
    return 'S/ ' + n.toFixed(2);
  }


  function normalizarReputacionVendedor(raw = {}) {
    const o = raw && typeof raw === 'object' ? raw : {};

    const total = Number(
      o.reputacion_vendedor_total ??
      o.total_calificaciones_vendedor ??
      o.total_calificaciones ??
      o.calificaciones_vendedor_total ??
      0
    ) || 0;

    const promedio = Number(
      o.reputacion_vendedor_promedio ??
      o.promedio_calificacion_vendedor ??
      o.promedio_vendedor ??
      o.promedio ??
      0
    ) || 0;

    const textoBackend = String(
      o.reputacion_vendedor_texto ??
      o.reputacion_texto ??
      ''
    ).trim();

    const esNuevoBackend = o.reputacion_vendedor_es_nuevo ?? o.es_nuevo_vendedor ?? null;
    const esNuevo = esNuevoBackend !== null && esNuevoBackend !== undefined
      ? Number(esNuevoBackend || 0) === 1
      : total < 5;

    return {
      total,
      promedio,
      promedioTexto: total > 0 ? promedio.toFixed(1) : '',
      esNuevo,
      texto: textoBackend || (esNuevo ? 'Nuevo vendedor' : `${promedio.toFixed(1)} · ${total} ventas`)
    };
  }

  function aplicarReputacionAItem(item, reputacion = null) {
    if (!item || typeof item !== 'object') return item;

    const rep = reputacion || normalizarReputacionVendedor(item);

    return {
      ...item,
      __reputacion_vendedor_total: Number(rep.total || 0),
      __reputacion_vendedor_promedio: Number(rep.promedio || 0),
      __reputacion_vendedor_promedio_texto: String(rep.promedioTexto || ''),
      __reputacion_vendedor_es_nuevo: rep.esNuevo ? 1 : 0,
      __reputacion_vendedor_texto: String(rep.texto || 'Nuevo vendedor')
    };
  }

  function reputacionVendedorHtml(item, opts = {}) {
    const rep = normalizarReputacionVendedor({
      reputacion_vendedor_total: item?.__reputacion_vendedor_total,
      reputacion_vendedor_promedio: item?.__reputacion_vendedor_promedio,
      reputacion_vendedor_texto: item?.__reputacion_vendedor_texto,
      reputacion_vendedor_es_nuevo: item?.__reputacion_vendedor_es_nuevo
    });

    const modifier = opts.detalle ? ' ev-mp-seller-rating-detail' : '';
    const title = rep.esNuevo
      ? 'Este vendedor aún está construyendo reputación en EV.'
      : `Calificación del vendedor: ${rep.promedioTexto} sobre 5, basada en ${rep.total} ventas calificadas.`;

    return `
      <div class="ev-mp-seller-rating${modifier} ${rep.esNuevo ? 'is-new' : 'is-rated'}" title="${escapeHtml(title)}">
        <i class="bi bi-star-fill" aria-hidden="true"></i>
        <span>${escapeHtml(rep.texto)}</span>
      </div>
    `;
  }

  async function fetchReputacionVendedores(ids = []) {
    const unique = Array.from(new Set(ids.map(v => Number(v || 0)).filter(Boolean)));
    const faltantes = unique.filter(id => !reputacionVendedoresCache.has(id));

    if (!faltantes.length) return reputacionVendedoresCache;

    try {
      const url = `${BASE}/api/calificaciones/reputacion-vendedores?ids=${encodeURIComponent(faltantes.join(','))}`;
      const { resp, json } = await fetchJsonRobusto(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        cache: 'no-store'
      });

      if (!resp.ok || !json || json.ok === false) {
        faltantes.forEach((id) => reputacionVendedoresCache.set(id, normalizarReputacionVendedor({})));
        return reputacionVendedoresCache;
      }

      const data = json.data && typeof json.data === 'object' ? json.data : {};

      faltantes.forEach((id) => {
        const repRaw = data[id] || data[String(id)] || {};
        reputacionVendedoresCache.set(id, normalizarReputacionVendedor(repRaw));
      });
    } catch (e) {
      warn('No se pudo cargar reputación de vendedores:', e);
      faltantes.forEach((id) => reputacionVendedoresCache.set(id, normalizarReputacionVendedor({})));
    }

    return reputacionVendedoresCache;
  }

  async function enriquecerPublicacionesConReputacion(items = []) {
    const lista = Array.isArray(items) ? items : [];
    const ids = lista.map(item => Number(item.__codigo_usuario_vendedor || 0)).filter(Boolean);

    await fetchReputacionVendedores(ids);

    return lista.map((item) => {
      const id = Number(item.__codigo_usuario_vendedor || 0);
      const rep = id > 0 ? reputacionVendedoresCache.get(id) : null;
      return aplicarReputacionAItem(item, rep);
    });
  }

  function escapeHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function getBasePath() {
    if (!BASE) return '';
    const u = BASE.startsWith('http') ? new URL(BASE) : null;
    const p = u ? (u.pathname || '') : BASE;
    return p.replace(/\/+$/, '');
  }

  function normalizeRelPath(relPath) {
    const basePath = getBasePath();
    let p = (relPath || '').toString().trim();
    if (!p) return '';

    if (/^https?:\/\//i.test(p)) return p;

    p = p.replace(/\\/g, '/').replace(/\/+/g, '/');

    if (basePath && p.startsWith(basePath + '/')) {
      p = p.slice(basePath.length);
    }

    if (!p.startsWith('/')) p = '/' + p;
    return p;
  }

  function buildImgUrl(relPath) {
    const placeholder = (BASE ? (BASE + '/public/img/placeholder-ev.png') : '/public/img/placeholder-ev.png');
    if (!relPath) return placeholder;

    const p = normalizeRelPath(relPath);
    if (!p) return placeholder;
    if (/^https?:\/\//i.test(p)) return p;

    return (BASE || '') + p;
  }

  function toDateTimeLocalValue(date) {
    const d = (date instanceof Date) ? new Date(date.getTime()) : new Date(date);
    if (Number.isNaN(d.getTime())) return '';

    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  function obtenerFechaMinimaProgramada() {
    const ahora = new Date();
    ahora.setSeconds(0, 0);

    const conBuffer = new Date(ahora.getTime() + 60 * 1000);
    return conBuffer;
  }

  function normalizarFechaProgramadaInput(rawValue) {
    const raw = String(rawValue || '').trim();
    if (!raw) return '';

    const fecha = new Date(raw);
    if (Number.isNaN(fecha.getTime())) return raw;

    fecha.setSeconds(0, 0);
    return toDateTimeLocalValue(fecha);
  }

  function getArrayFromPayload(payload) {
    if (!payload) return [];
    if (Array.isArray(payload)) return payload;

    if (typeof payload === 'object') {
      if (Array.isArray(payload.data)) return payload.data;
      if (Array.isArray(payload.items)) return payload.items;
      if (payload.data && Array.isArray(payload.data.items)) return payload.data.items;
    }

    return [];
  }

  function normalizarListaDesdeAPI(payload) {
    return getArrayFromPayload(payload);
  }

  function normalizarTipoPublicacion(raw) {
    const o = raw && typeof raw === 'object' ? raw : {};

    const directo = normalizar(o.tipo_publicacion || o.tipo_publicacion_nombre || '');
    if (directo === 'servicio' || directo === 'servicios') return 'servicio';
    if (directo === 'producto' || directo === 'productos') return 'producto';

    // Fallback para compatibilidad con registros antiguos o APIs previas.
    const txt = normalizar(
      (o.tipo_nombre || '') + ' ' +
      (o.tipo || '') + ' ' +
      (o.nombre_tipo || '') + ' ' +
      (o.tipo_slug || '')
    );

    if (txt.includes('servicio')) return 'servicio';
    return 'producto';
  }

  function tipoPublicacionLabelFromKey(key) {
    return key === 'servicio' ? 'Servicio' : 'Producto';
  }

  function esServicioPublicacion(item) {
    return String(item?.__tipo_publicacion || normalizarTipoPublicacion(item)) === 'servicio';
  }

  function esProductoPublicacion(item) {
    return !esServicioPublicacion(item);
  }

  function normalizarItem(raw) {
    const o = raw && typeof raw === 'object' ? raw : {};

    const id = o.codigo_producto ?? o.id ?? '';
    const titulo = o.titulo ?? o.nombre ?? '';
    const descripcion = o.descripcion ?? o.detalle ?? '';
    const precio = o.precio ?? 0;
    const tipo_publicacion = normalizarTipoPublicacion(o);

    const codigo_tipo = Number(o.codigo_tipo || 0) || 0;
    const codigo_categoria = Number(o.codigo_categoria || 0) || 0;

    const tipo_nombre = o.tipo_nombre ?? o.tipo ?? '';
    const tipo_slug = o.tipo_slug ?? '';
    const categoria_nombre = o.categoria_nombre ?? o.categoria ?? '';
    const categoria_slug = o.categoria_slug ?? '';

    const es_potenciado = o.es_potenciado ?? o.potenciado ?? o.destacado ?? 0;
    const vendedorDisponible = Number(o.vendedor_disponible ?? o.disponibilidad_pedidos_vendedor ?? 0) === 1 ? 1 : 0;
    const codigoUsuarioVendedor = Number(
      o.codigo_usuario_vendedor ??
      o.codigo_usuario ??
      o.codigo_vendedor ??
      o.codigo_usuario_publicador ??
      0
    ) || 0;

    const reputacionInicial = normalizarReputacionVendedor(o);

    const imagen_portada =
      o.imagen_portada_url ??
      o.imagen_portada ??
      o.ruta_portada ??
      o.imagen ??
      '';

    const orden_reciente =
      o.created_ts ??
      o.orden_reciente ??
      o.created_at ??
      id ??
      0;

    return {
      ...o,
      __id: id,
      __titulo: titulo,
      __descripcion: descripcion,
      __precio: precio,
      __tipo_publicacion: tipo_publicacion,
      __tipo_publicacion_label: tipoPublicacionLabelFromKey(tipo_publicacion),
      __codigo_tipo: codigo_tipo,
      __codigo_categoria: codigo_categoria,
      __tipo_nombre: tipo_nombre,
      __tipo_slug: tipo_slug,
      __categoria_nombre: categoria_nombre,
      __categoria_slug: categoria_slug,
      __es_potenciado: es_potenciado,
      __imagen_portada: imagen_portada,
      __orden_reciente: orden_reciente,
      __vendedor_disponible: vendedorDisponible,
      __codigo_usuario_vendedor: codigoUsuarioVendedor,
      __reputacion_vendedor_total: Number(reputacionInicial.total || 0),
      __reputacion_vendedor_promedio: Number(reputacionInicial.promedio || 0),
      __reputacion_vendedor_promedio_texto: String(reputacionInicial.promedioTexto || ''),
      __reputacion_vendedor_es_nuevo: reputacionInicial.esNuevo ? 1 : 0,
      __reputacion_vendedor_texto: String(reputacionInicial.texto || 'Nuevo vendedor')
    };
  }

  function swalBaseConfig(opts = {}) {
    ensureMarketplaceSwalCleanStyles();
    return Object.assign({
      buttonsStyling: false,
      allowOutsideClick: () => {
        const popup = window.Swal?.getPopup ? Swal.getPopup() : null;
        if (popup) {
          popup.classList.remove('ev-mp-swal-popup-bounce');
          void popup.offsetWidth;
          popup.classList.add('ev-mp-swal-popup-bounce');
        }
        return false;
      },
      allowEscapeKey: false,
      customClass: {
        container: 'ev-mp-swal-container',
        popup: 'ev-mp-swal-popup ev-mp-swal-actions-gap',
        title: 'ev-mp-swal-title',
        htmlContainer: 'ev-mp-swal-html',
        confirmButton: 'ev-mp-swal-confirm',
        cancelButton: 'ev-mp-swal-cancel'
      }
    }, opts || {});
  }

  function swalCloseIfVisible() {
    if (window.Swal?.isVisible && window.Swal.isVisible()) {
      Swal.close();
    }
  }


  const EV_ALERTAS_SUPRIMIDAS_KEY = 'ev_pedidos_alertas_suprimidas_v1';
  const EV_ALERTAS_SUPRIMIDAS_TTL_MS = 2 * 60 * 1000;

  function suprimirAlertaGlobalPedido(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (!id) return;

    try {
      const now = Date.now();
      const raw = sessionStorage.getItem(EV_ALERTAS_SUPRIMIDAS_KEY);
      const store = raw ? JSON.parse(raw) : {};

      Object.keys(store || {}).forEach((key) => {
        if ((now - Number(store[key] || 0)) > EV_ALERTAS_SUPRIMIDAS_TTL_MS) {
          delete store[key];
        }
      });

      store[String(id)] = now;
      sessionStorage.setItem(EV_ALERTAS_SUPRIMIDAS_KEY, JSON.stringify(store));
    } catch (_) {}

    try {
      if (window.EVPedidosAlertas && typeof window.EVPedidosAlertas.suprimirPedido === 'function') {
        window.EVPedidosAlertas.suprimirPedido(id);
      }
    } catch (_) {}
  }

  function ensureMarketplaceSwalCleanStyles() {
    const ID = 'ev-mp-swal-clean-premium-style';
    if (document.getElementById(ID)) return;

    const style = document.createElement('style');
    style.id = ID;
    style.type = 'text/css';
    style.textContent = `
      .swal2-popup.ev-mp-swal-popup,
      .swal2-popup.ev-mp-swal-popup-seguimiento,
      .swal2-popup.ev-mp-toast-cola-popup{
        background:#ffffff !important;
        background-image:none !important;
        border:1px solid rgba(229,231,235,.96) !important;
        box-shadow:0 30px 72px rgba(15,23,42,.20), 0 10px 24px rgba(15,23,42,.08) !important;
      }

      .swal2-popup.ev-mp-swal-popup::before,
      .swal2-popup.ev-mp-swal-popup-seguimiento::before{
        height:5px !important;
        background:linear-gradient(90deg,#0F592F 0%,#16A34A 58%,#EA7C12 100%) !important;
      }

      .ev-mp-swal-status-icon,
      .ev-mp-swal-status-icon--success,
      .ev-mp-swal-status-icon--info{
        background:#ffffff !important;
        background-image:none !important;
        box-shadow:0 10px 26px rgba(15,23,42,.06), inset 0 0 0 1px rgba(255,255,255,.95) !important;
      }

      .ev-mp-swal-status-icon--success{
        border-color:rgba(22,163,74,.24) !important;
      }

      .ev-mp-swal-status-icon--info{
        border-color:rgba(56,189,248,.28) !important;
      }

      .ev-mp-swal-product-card{
        background:#ffffff !important;
        border:1px solid rgba(229,231,235,.96) !important;
        box-shadow:0 8px 22px rgba(15,23,42,.045) !important;
      }

      .ev-mp-swal-note{
        background:#ffffff !important;
        background-image:none !important;
        border:1px solid rgba(234,124,18,.20) !important;
        box-shadow:0 10px 24px rgba(234,124,18,.06) !important;
      }

      .ev-mp-swal-loader{
        border-color:rgba(22,163,74,.14) !important;
        border-top-color:#0F592F !important;
      }
    `;
    document.head.appendChild(style);
  }

  function esperarModalOculto(modalEl) {
    if (!modalEl || !modalEl.classList.contains('show')) {
      return Promise.resolve();
    }

    return new Promise((resolve) => {
      let done = false;
      const finish = () => {
        if (done) return;
        done = true;
        try { modalEl.removeEventListener('hidden.bs.modal', finish); } catch (_) {}
        resolve();
      };

      modalEl.addEventListener('hidden.bs.modal', finish, { once: true });
      window.setTimeout(finish, 460);
    });
  }

  function triggerSwalBounce() {
    const popup = window.Swal?.getPopup ? Swal.getPopup() : null;
    if (!popup) return;

    popup.classList.remove('ev-mp-swal-popup-bounce');
    void popup.offsetWidth;
    popup.classList.add('ev-mp-swal-popup-bounce');
  }

  function attachBounceOutsideBehavior() {
    const container = window.Swal?.getContainer ? Swal.getContainer() : null;
    const popup = window.Swal?.getPopup ? Swal.getPopup() : null;
    if (!container || !popup) return;

    if (container.dataset.evBounceBound === '1') return;
    container.dataset.evBounceBound = '1';

    container.addEventListener('mousedown', (ev) => {
      if (!popup.contains(ev.target)) {
        triggerSwalBounce();
      }
    });
  }

  function htmlPremiumMessage(opts = {}) {
    const {
      variant = 'success',
      subtitle = '',
      text = '',
      productLabel = '',
      productText = '',
      note = '',
      extra = ''
    } = opts;

    const iconHtml = variant === 'info'
      ? `
        <div class="ev-mp-swal-status-icon ev-mp-swal-status-icon--info" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="32" r="30" fill="none"></circle>
            <path d="M32 18.5C34.5 18.5 36.3 20.2 36.3 22.6C36.3 25 34.5 26.8 32 26.8C29.5 26.8 27.7 25 27.7 22.6C27.7 20.2 29.5 18.5 32 18.5Z" fill="#38BDF8"/>
            <path d="M32 31.5V45.5" stroke="#38BDF8" stroke-width="5" stroke-linecap="round"/>
          </svg>
        </div>
      `
      : variant === 'warning'
        ? `
          <div class="ev-mp-swal-status-icon" aria-hidden="true" style="border-color:rgba(234,124,18,.22);background:linear-gradient(180deg, rgba(255,247,237,.92), rgba(255,255,255,.98));">
            <svg viewBox="0 0 64 64" fill="none">
              <path d="M32 12L53 49H11L32 12Z" stroke="#EA7C12" stroke-width="4" fill="rgba(234,124,18,.08)"></path>
              <path d="M32 24V36" stroke="#EA7C12" stroke-width="5" stroke-linecap="round"></path>
              <circle cx="32" cy="43.5" r="2.8" fill="#EA7C12"></circle>
            </svg>
          </div>
        `
        : variant === 'error'
          ? `
            <div class="ev-mp-swal-status-icon" aria-hidden="true" style="border-color:rgba(239,68,68,.20);background:linear-gradient(180deg, rgba(254,242,242,.92), rgba(255,255,255,.98));">
              <svg viewBox="0 0 64 64" fill="none">
                <circle cx="32" cy="32" r="28" stroke="#DC2626" stroke-width="4" fill="rgba(220,38,38,.06)"></circle>
                <path d="M24 24L40 40M40 24L24 40" stroke="#DC2626" stroke-width="5" stroke-linecap="round"></path>
              </svg>
            </div>
          `
          : `
            <div class="ev-mp-swal-status-icon ev-mp-swal-status-icon--success" aria-hidden="true">
              <svg viewBox="0 0 64 64" fill="none">
                <path d="M18 33.5L27.5 43L46 23.5" stroke="#84CC16" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          `;

    const productHtml = productText
      ? `
        <div class="ev-mp-swal-product-card">
          ${productLabel ? `<span class="ev-mp-swal-product-label">${escapeHtml(productLabel)}</span>` : ''}
          <div class="ev-mp-swal-product">${escapeHtml(productText)}</div>
        </div>
      `
      : '';

    const noteHtml = note
      ? `<div class="ev-mp-swal-note">${note}</div>`
      : '';

    return `
      <div style="text-align:center;">
        ${iconHtml}
        ${subtitle ? `<div class="ev-mp-swal-subtitle">${escapeHtml(subtitle)}</div>` : ''}
        ${text ? `<div class="ev-mp-swal-soft-text">${escapeHtml(text)}</div>` : ''}
        ${productHtml}
        ${noteHtml}
        ${extra || ''}
      </div>
    `;
  }

  async function notify(icon, title, text, extra = {}) {
    if (!window.Swal?.fire) {
      alert(title ? `${title}\n\n${text}` : text);
      return;
    }

    swalCloseIfVisible();

    const subtitle = extra.subtitle || title || '';
    const variant =
      icon === 'success' ? 'success' :
      icon === 'warning' ? 'warning' :
      icon === 'error' ? 'error' :
      'info';

    return Swal.fire(swalBaseConfig(Object.assign({
      title,
      html: htmlPremiumMessage({
        variant,
        subtitle,
        text,
        productLabel: extra.productLabel || '',
        productText: extra.productText || '',
        note: extra.note || '',
        extra: extra.htmlExtra || ''
      }),
      confirmButtonText: extra.confirmButtonText || 'Aceptar',
      showCancelButton: !!extra.showCancelButton,
      cancelButtonText: extra.cancelButtonText || 'Cancelar'
    }, extra || {})));
  }

  async function notifySaldoInsuficiente(montoRequerido, saldoActual) {
    return notify(
      'warning',
      'Saldo insuficiente',
      `Este producto requiere preparación. Necesitas ${formatPrecio(montoRequerido)} en tu billetera y actualmente tienes ${formatPrecio(saldoActual)}.`,
      {
        subtitle: 'No se pudo continuar con la solicitud',
        confirmButtonText: 'Entendido',
        productLabel: 'Billetera',
        productText: `Saldo actual: ${formatPrecio(saldoActual)}`,
        note: `Para continuar necesitas al menos <strong>${formatPrecio(montoRequerido)}</strong> disponibles.`
      }
    );
  }

  function showLoadingSolicitud() {
    if (!window.Swal?.fire) return;

    Swal.fire(swalBaseConfig({
      title: 'Registrando solicitud',
      html: `
        <div class="ev-mp-swal-loader" aria-hidden="true"></div>
        <div class="ev-mp-swal-soft-text">
          Estamos validando la disponibilidad y registrando tu pedido. Espera un momento.
        </div>
      `,
      showConfirmButton: false,
      showCancelButton: false,
      allowOutsideClick: false,
      allowEscapeKey: false
    }));
  }

  function setResumen(txt) {
    if (refs.resumenResultados) refs.resumenResultados.textContent = txt;
  }

  function showEmpty(msg) {
    if (refs.gridServicios) refs.gridServicios.innerHTML = '';
    if (refs.gridProductos) refs.gridProductos.innerHTML = '';

    if (refs.emptyState) {
      refs.emptyState.style.display = '';
      refs.emptyState.textContent = msg || 'No encontramos publicaciones con los filtros actuales.';
    }
  }

  function hideEmpty() {
    if (refs.emptyState) refs.emptyState.style.display = 'none';
  }


  function getSelectDisplayText(selectEl) {
    if (!selectEl) return '';
    const opt = selectEl.selectedOptions && selectEl.selectedOptions[0]
      ? selectEl.selectedOptions[0]
      : Array.from(selectEl.options || []).find(o => String(o.value || '') === String(selectEl.value || ''));
    return opt ? String(opt.textContent || '').trim() : '';
  }

  function closeCustomSelects(exceptWrap = null) {
    document.querySelectorAll('.ev-mp-select.open').forEach((wrap) => {
      if (exceptWrap && wrap === exceptWrap) return;
      wrap.classList.remove('open');
      const btn = wrap.querySelector('.ev-mp-select-trigger');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  }

  function buildCustomSelectOptions(selectEl, wrap) {
    if (!selectEl || !wrap) return;

    const menu = wrap.querySelector('.ev-mp-select-menu');
    const valueEl = wrap.querySelector('.ev-mp-select-value');
    if (!menu || !valueEl) return;

    const selectedValue = String(selectEl.value || '');
    valueEl.textContent = getSelectDisplayText(selectEl) || 'Seleccionar';
    menu.innerHTML = '';

    const addOptionButton = (opt) => {
      const value = String(opt.value || '');
      const label = String(opt.textContent || '').trim();
      if (!label) return;

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ev-mp-select-option';
      btn.setAttribute('role', 'option');
      btn.dataset.value = value;
      btn.setAttribute('aria-selected', value === selectedValue ? 'true' : 'false');

      if (value === selectedValue) {
        btn.classList.add('is-active');
      }

      btn.innerHTML = `
        <span class="ev-mp-select-check"><i class="bi bi-check"></i></span>
        <span class="ev-mp-select-option-text"></span>
      `;

      const text = btn.querySelector('.ev-mp-select-option-text');
      if (text) text.textContent = label;

      btn.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        selectEl.value = value;
        categoriaFiltroValor = selectEl.id === 'mp_categoria_producto' ? value : categoriaFiltroValor;
        closeCustomSelects();
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        refreshCustomSelect(wrap);
      });

      menu.appendChild(btn);
    };

    Array.from(selectEl.children).forEach((child) => {
      if (!child) return;

      if (child.tagName && child.tagName.toLowerCase() === 'optgroup') {
        const label = String(child.getAttribute('label') || '').trim();
        if (label) {
          const group = document.createElement('div');
          group.className = 'ev-mp-select-group';
          group.textContent = label;
          menu.appendChild(group);
        }

        Array.from(child.children).forEach(addOptionButton);
        return;
      }

      if (child.tagName && child.tagName.toLowerCase() === 'option') {
        addOptionButton(child);
      }
    });
  }

  function refreshCustomSelect(wrapOrSelectId) {
    let wrap = null;

    if (typeof wrapOrSelectId === 'string') {
      wrap = document.querySelector(`[data-ev-select="${wrapOrSelectId}"]`);
    } else {
      wrap = wrapOrSelectId;
    }

    if (!wrap) return;

    const selectId = wrap.getAttribute('data-ev-select');
    const selectEl = selectId ? document.getElementById(selectId) : null;
    if (!selectEl) return;

    buildCustomSelectOptions(selectEl, wrap);
  }

  function refreshCustomSelects() {
    refreshCustomSelect('mp_orden');
    refreshCustomSelect('mp_categoria_producto');
  }

  function bindCustomSelect(wrap) {
    if (!wrap || wrap.dataset.evSelectBound === '1') return;

    const selectId = wrap.getAttribute('data-ev-select');
    const selectEl = selectId ? document.getElementById(selectId) : null;
    const trigger = wrap.querySelector('.ev-mp-select-trigger');

    if (!selectEl || !trigger) return;

    wrap.dataset.evSelectBound = '1';

    trigger.addEventListener('click', (ev) => {
      ev.preventDefault();
      marcarInteraccionUi();

      const willOpen = !wrap.classList.contains('open');
      closeCustomSelects(wrap);

      if (willOpen) {
        refreshCustomSelect(wrap);
        wrap.classList.add('open');
        trigger.setAttribute('aria-expanded', 'true');
      } else {
        wrap.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
      }
    });

    trigger.addEventListener('keydown', (ev) => {
      if (ev.key === 'Escape') {
        closeCustomSelects();
      }

      if (ev.key === 'Enter' || ev.key === ' ') {
        ev.preventDefault();
        trigger.click();
      }
    });

    selectEl.addEventListener('change', () => {
      refreshCustomSelect(wrap);
    });

    refreshCustomSelect(wrap);
  }

  function initCustomSelects() {
    bindCustomSelect(refs.customOrdenar);
    bindCustomSelect(refs.customCategoria);
    refreshCustomSelects();

    if (!document.body.dataset.boundEvMpSelectClose) {
      document.body.dataset.boundEvMpSelectClose = '1';

      document.addEventListener('click', (ev) => {
        if (!ev.target.closest('.ev-mp-select')) {
          closeCustomSelects();
        }
      }, true);

      document.addEventListener('keydown', (ev) => {
        if (ev.key === 'Escape') {
          closeCustomSelects();
        }
      });

      window.addEventListener('resize', () => closeCustomSelects(), { passive: true });
      window.addEventListener('scroll', () => closeCustomSelects(), { passive: true, capture: true });
    }
  }

  function showLoadingMarketplace() {
    hideEmpty();

    if (refs.wrapServicios) refs.wrapServicios.style.display = 'none';
    if (refs.wrapProductos) refs.wrapProductos.style.display = '';
    if (refs.emptyServicios) refs.emptyServicios.style.display = 'none';
    if (refs.emptyProductos) refs.emptyProductos.style.display = 'none';
    if (refs.countServicios) refs.countServicios.textContent = '0';
    if (refs.countProductos) refs.countProductos.textContent = '...';
    if (refs.gridServicios) refs.gridServicios.innerHTML = '';

    if (refs.gridProductos) {
      refs.gridProductos.innerHTML = `
        <div class="ev-mp-loading-grid" aria-hidden="true">
          ${[1,2,3].map(() => `
            <div class="ev-mp-skeleton-card">
              <div class="ev-mp-skeleton-img"></div>
              <div class="ev-mp-skeleton-body">
                <div class="ev-mp-skeleton-line w70"></div>
                <div class="ev-mp-skeleton-line w90"></div>
                <div class="ev-mp-skeleton-line w45"></div>
                <div class="ev-mp-skeleton-line w90"></div>
              </div>
            </div>
          `).join('')}
        </div>
      `;
    }
  }

  function ensureGridCSS() {
    const ID = 'ev-mp-grid-fix';
    const old = document.getElementById(ID);
    if (old) old.remove();

    const css = `
#mp_grid_servicios,
#mp_grid_productos{
  display:grid !important;
  grid-template-columns:repeat(auto-fill, minmax(280px, 280px)) !important;
  gap:20px !important;
  align-items:stretch !important;
  justify-content:start !important;
  justify-items:stretch !important;
  width:100% !important;
}

#mp_grid_servicios .ev-mp-card,
#mp_grid_productos .ev-mp-card{
  width:280px !important;
  height:486px !important;
  min-height:486px !important;
  max-height:486px !important;
  display:flex !important;
  flex-direction:column !important;
  overflow:hidden !important;
  justify-self:stretch !important;
}

#mp_grid_servicios .ev-mp-card-top-status,
#mp_grid_productos .ev-mp-card-top-status{
  height:40px !important;
  min-height:40px !important;
  max-height:40px !important;
  flex:0 0 40px !important;
}

#mp_grid_servicios .ev-mp-card-media,
#mp_grid_productos .ev-mp-card-media{
  height:184px !important;
  min-height:184px !important;
  max-height:184px !important;
  flex:0 0 184px !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  background:#F8FAFC !important;
  overflow:hidden !important;
}

#mp_grid_servicios .ev-mp-card-media img,
#mp_grid_productos .ev-mp-card-media img{
  width:100% !important;
  height:100% !important;
  object-fit:contain !important;
  object-position:center !important;
  display:block !important;
}

#mp_grid_servicios .ev-mp-card-body,
#mp_grid_productos .ev-mp-card-body{
  flex:1 1 auto !important;
  min-height:0 !important;
  display:flex !important;
  flex-direction:column !important;
  padding:13px 15px 14px !important;
}

#mp_grid_servicios .ev-mp-card-title,
#mp_grid_productos .ev-mp-card-title{
  min-height:40px !important;
  max-height:40px !important;
  overflow:hidden !important;
  display:-webkit-box !important;
  -webkit-line-clamp:2 !important;
  -webkit-box-orient:vertical !important;
  line-height:1.34 !important;
}

#mp_grid_servicios .ev-mp-card-price,
#mp_grid_productos .ev-mp-card-price{
  min-height:23px !important;
  line-height:1.25 !important;
}

#mp_grid_servicios .ev-mp-seller-rating,
#mp_grid_productos .ev-mp-seller-rating{
  flex:0 0 auto !important;
  min-height:26px !important;
  margin:2px 0 5px !important;
  max-width:100% !important;
}

#mp_grid_servicios .ev-mp-card-desc,
#mp_grid_productos .ev-mp-card-desc{
  min-height:36px !important;
  max-height:36px !important;
  overflow:hidden !important;
  display:-webkit-box !important;
  -webkit-line-clamp:2 !important;
  -webkit-box-orient:vertical !important;
  margin-bottom:0 !important;
  line-height:1.42 !important;
}

#mp_grid_servicios .ev-mp-card-actions,
#mp_grid_productos .ev-mp-card-actions{
  margin-top:auto !important;
  display:flex !important;
  gap:10px !important;
}

#mp_grid_servicios .ev-mp-card-actions .btn,
#mp_grid_productos .ev-mp-card-actions .btn{
  flex:1 1 0 !important;
  min-width:0 !important;
  height:40px !important;
  white-space:nowrap !important;
  font-size:13px !important;
}

@media (min-width:1600px){
  #mp_grid_servicios,
  #mp_grid_productos{
    grid-template-columns:repeat(auto-fill, minmax(292px, 292px)) !important;
  }

  #mp_grid_servicios .ev-mp-card,
  #mp_grid_productos .ev-mp-card{
    width:292px !important;
  }
}

@media (max-width:991.98px){
  #mp_grid_servicios,
  #mp_grid_productos{
    grid-template-columns:repeat(auto-fill, minmax(245px, 1fr)) !important;
  }

  #mp_grid_servicios .ev-mp-card,
  #mp_grid_productos .ev-mp-card{
    width:100% !important;
    height:492px !important;
    min-height:492px !important;
    max-height:492px !important;
  }
}

@media (max-width:575.98px){
  #mp_grid_servicios,
  #mp_grid_productos{
    grid-template-columns:1fr !important;
  }

  #mp_grid_servicios .ev-mp-card,
  #mp_grid_productos .ev-mp-card{
    width:100% !important;
    height:auto !important;
    min-height:466px !important;
    max-height:none !important;
  }

  #mp_grid_servicios .ev-mp-card-media,
  #mp_grid_productos .ev-mp-card-media{
    height:190px !important;
    min-height:190px !important;
    max-height:190px !important;
    flex:0 0 190px !important;
  }
}
    `.trim();

    const style = document.createElement('style');
    style.id = ID;
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);
  }

  function capturarRefs() {
    refs.gridAllWrapper       = document.getElementById('mp_grid_publicaciones');
    refs.gridServicios        = document.getElementById('mp_grid_servicios');
    refs.gridProductos        = document.getElementById('mp_grid_productos');
    refs.countServicios       = document.getElementById('mp_count_servicios');
    refs.countProductos       = document.getElementById('mp_count_productos');
    refs.emptyServicios       = document.getElementById('mp_empty_servicios');
    refs.emptyProductos       = document.getElementById('mp_empty_productos');

    refs.resumenResultados    = document.getElementById('mp_resumen_resultados');
    refs.searchInput          = document.getElementById('mp_busqueda');
    refs.emptyState           = document.getElementById('mp_empty_state');
    refs.selectOrdenar        = document.getElementById('mp_orden');
    refs.customOrdenar        = document.querySelector('[data-ev-select="mp_orden"]');

    refs.scopeButtons         = Array.from(document.querySelectorAll('.ev-mp-seg-btn'));
    refs.selectCategoriaProductos = document.getElementById('mp_categoria_producto');
    refs.customCategoria = document.querySelector('[data-ev-select="mp_categoria_producto"]');
    refs.categoriaLabel = document.getElementById('mp_categoria_label');

    refs.wrapServicios = refs.gridServicios ? (refs.gridServicios.closest('.ev-mp-section') || refs.gridServicios.parentElement) : null;
    refs.wrapProductos = refs.gridProductos ? (refs.gridProductos.closest('.ev-mp-section') || refs.gridProductos.parentElement) : null;

    if (refs.selectCategoriaProductos) {
      refs.wrapCategoriaProductos =
        refs.selectCategoriaProductos.closest('.ev-mp-cat-wrap') ||
        refs.selectCategoriaProductos.closest('.col') ||
        refs.selectCategoriaProductos.parentElement;
    }

    return !!refs.gridAllWrapper;
  }

  function applyScopeVisibility() {
    const showServ = (scope === 'todos' || scope === 'servicios');
    const showProd = (scope === 'todos' || scope === 'productos');

    if (refs.wrapServicios) refs.wrapServicios.style.display = showServ ? '' : 'none';
    if (refs.wrapProductos) refs.wrapProductos.style.display = showProd ? '' : 'none';

    // El combo de categoría se mantiene visible en todos los scopes.
    // En "Todos" carga categorías agrupadas de productos y servicios.
    if (refs.wrapCategoriaProductos) {
      refs.wrapCategoriaProductos.style.display = '';
    }
  }

  function extraerDetalleDesdeRespuesta(json) {
    const root = (json && typeof json === 'object') ? json : {};
    const d = root.data && typeof root.data === 'object' ? root.data : null;

    const producto =
      (d && d.producto && typeof d.producto === 'object') ? d.producto :
      (root.producto && typeof root.producto === 'object') ? root.producto :
      (d && !d.producto ? d : null) ||
      {};

    const imagenes =
      (d && Array.isArray(d.imagenes)) ? d.imagenes :
      (root && Array.isArray(root.imagenes)) ? root.imagenes :
      (Array.isArray(producto.imagenes) ? producto.imagenes : []);

    return { producto, imagenes };
  }

  async function manejarRespuestaAuth(resp, json) {
    const error = String(json?.error || '').trim();

    if (resp.status === 401) {
      await notify(
        'info',
        'Sesión finalizada',
        (json && json.mensaje) || 'Tu sesión expiró. Vuelve a iniciar sesión.',
        { confirmButtonText: 'Ir al login', subtitle: 'Debes autenticarte nuevamente' }
      );
      window.location.href = (json && json.redirect) ? json.redirect : `${BASE}/login`;
      return true;
    }

    if (resp.status === 403 && error === 'CUENTA_BLOQUEADA') {
      await notify(
        'warning',
        'Cuenta bloqueada',
        (json && json.mensaje) || 'Tu cuenta fue bloqueada. Debes volver a iniciar sesión.',
        { confirmButtonText: 'Ir al login', subtitle: 'No puedes continuar en este momento' }
      );
      window.location.href = (json && json.redirect) ? json.redirect : `${BASE}/login`;
      return true;
    }

    if (resp.status === 409 && error === 'CUENTA_OBSERVADA') {
      await notify(
        'warning',
        'Cuenta observada',
        (json && json.mensaje) || 'Tu cuenta está observada. Debes revisar tu estado.',
        { confirmButtonText: 'Ir a revisión', subtitle: 'Debes revisar el estado de tu cuenta' }
      );
      window.location.href = (json && json.redirect) ? json.redirect : `${BASE}/cuenta-observada`;
      return true;
    }

    return false;
  }

  async function obtenerDetalleProducto(idProducto) {
    const url = `${BASE}/api/marketplace/producto/${encodeURIComponent(idProducto)}`;

    const { resp, text, json } = await fetchJsonRobusto(url, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    });

    if (await manejarRespuestaAuth(resp, json)) return null;

    if (resp.status === 409) {
      const msg = (json && (json.mensaje || json.error)) ? (json.mensaje || json.error) : 'No tienes residencia activa.';
      await notify('warning', 'Residencia requerida', msg, {
        confirmButtonText: 'Entendido',
        subtitle: 'Necesitas una residencia activa'
      });
      const redir = (json && json.redirect) ? json.redirect : `${BASE}/mi-perfil`;
      window.location.href = redir;
      return null;
    }

    if (!json) {
      err('DETALLE no devolvió JSON:', (text || '').slice(0, 400));
      await notify('error', 'Error', 'La API devolvió una respuesta no válida.', {
        subtitle: 'No se pudo cargar el detalle'
      });
      return null;
    }

    if (!resp.ok || !json.ok) {
      const msg = json.mensaje || json.error || 'No se pudo obtener el detalle.';
      await notify('error', 'Error', msg, { subtitle: 'No se pudo cargar el detalle' });
      return null;
    }

    return extraerDetalleDesdeRespuesta(json);
  }

  async function obtenerSaldoBilleteraActual() {
    const { resp, json, text } = await fetchJsonRobusto(`${BASE}/api/billetera/saldo`, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    });

    if (await manejarRespuestaAuth(resp, json)) return null;

    if (!json) {
      err('SALDO no devolvió JSON:', (text || '').slice(0, 400));
      return null;
    }

    if (!resp.ok || !json.ok) {
      return null;
    }

    return Number(json.saldo_actual || 0);
  }

  function limpiarSeguimientoSolicitud() {
    if (solicitudFlow.pollingTimer) {
      clearInterval(solicitudFlow.pollingTimer);
      solicitudFlow.pollingTimer = null;
    }

    if (solicitudFlow.intervalUi) {
      clearInterval(solicitudFlow.intervalUi);
      solicitudFlow.intervalUi = null;
    }

    solicitudFlow.codigoPedido = 0;
    solicitudFlow.activo = false;
    solicitudFlow.cancelButtonVisible = false;
    solicitudFlow.modo = '';
    solicitudFlow.segundosRestantes = 0;
    solicitudFlow.segundosParaCancelarRestantes = SEGUNDOS_CANCELACION_SOLICITUD;
  }

  function formatDuracionSegundos(segundos) {
    const total = Math.max(0, Number(segundos || 0));
    const min = Math.floor(total / 60);
    const sec = total % 60;
    return `${String(min).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
  }

  function obtenerQueueAckKey(codigoPedido) {
    return `ev_mp_queue_ack_${Number(codigoPedido || 0)}`;
  }

  function marcarQueueAckVisto(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (!id) return;

    try {
      sessionStorage.setItem(obtenerQueueAckKey(id), '1');
    } catch (_) {}
  }

  function limpiarQueueAckVisto(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (!id) return;

    try {
      sessionStorage.removeItem(obtenerQueueAckKey(id));
    } catch (_) {}
  }

  function yaSeMostroQueueAck(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (!id) return false;

    try {
      return sessionStorage.getItem(obtenerQueueAckKey(id)) === '1';
    } catch (_) {
      return false;
    }
  }

  async function abrirVistaMisPedidosComprador() {
    const ruta = '/mis-pedidos-comprador';

    if (window.EVNav && typeof window.EVNav.loadPage === 'function') {
      await window.EVNav.loadPage(ruta, { pushState: true, replaceState: false });
      return;
    }

    window.location.href = `${BASE}/MenuPrincipal?ev_goto=${encodeURIComponent(ruta)}`;
  }

  function htmlSeguimientoCola(opts = {}) {
    const {
      tituloProducto = 'tu solicitud',
      detalle = 'Tu solicitud quedó en cola y avanzará cuando el vendedor termine el pedido anterior.',
      posicionCola = 0
    } = opts;

    const detalleFinal = posicionCola > 0 && !/posici[oó]n/i.test(String(detalle))
      ? `${detalle} Posición actual: ${posicionCola}.`
      : detalle;

    return `
      <div style="text-align:center;">
        <div class="ev-mp-swal-status-icon ev-mp-swal-status-icon--info" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="32" r="30" fill="none"></circle>
            <path d="M32 18.5C34.5 18.5 36.3 20.2 36.3 22.6C36.3 25 34.5 26.8 32 26.8C29.5 26.8 27.7 25 27.7 22.6C27.7 20.2 29.5 18.5 32 18.5Z" fill="#38BDF8"/>
            <path d="M32 31.5V45.5" stroke="#38BDF8" stroke-width="5" stroke-linecap="round"/>
          </svg>
        </div>

        <div class="ev-mp-swal-subtitle">Solicitud en cola de atención</div>

        <div class="ev-mp-swal-soft-text">
          ${escapeHtml(detalleFinal)}
        </div>

        <div class="ev-mp-swal-product-card">
          <span class="ev-mp-swal-product-label">Solicitud</span>
          <div class="ev-mp-swal-product">${escapeHtml(tituloProducto)}</div>
        </div>

        <div class="ev-mp-swal-note">
          Tu solicitud <strong>aún no ha sido aceptada por el vendedor</strong>. Solo quedó registrada en cola y avanzará cuando llegue su turno de atención.
        </div>
      </div>
    `;
  }

  async function mostrarPopupColaNoBloqueante(data = {}, forzar = false) {
    const codigoPedido = Number(data?.codigo_pedido || 0);

    if (!forzar && codigoPedido > 0 && yaSeMostroQueueAck(codigoPedido)) {
      return;
    }

    if (!estaVistaMarketplaceActiva()) {
      if (codigoPedido > 0) marcarQueueAckVisto(codigoPedido);
      return;
    }

    if (!window.Swal?.fire) {
      if (codigoPedido > 0) marcarQueueAckVisto(codigoPedido);
      return;
    }

    swalCloseIfVisible();

    const r = await Swal.fire(swalBaseConfig({
      title: 'Hay una cola en atención',
      html: htmlSeguimientoCola({
        tituloProducto: String(data?.titulo_producto || data?.titulo_publicacion || 'tu solicitud'),
        detalle: data?.mensaje_estado || 'Tu solicitud quedó en cola y avanzará cuando el vendedor termine el pedido anterior.',
        posicionCola: Number(data?.posicion_cola || 0)
      }),
      showConfirmButton: true,
      confirmButtonText: 'Entendido',
      showCancelButton: true,
      cancelButtonText: 'Ir a mis pedidos',
      allowOutsideClick: false,
      allowEscapeKey: true
    }));

    if (codigoPedido > 0) {
      marcarQueueAckVisto(codigoPedido);
    }

    if (r.dismiss === Swal.DismissReason.cancel) {
      await abrirVistaMisPedidosComprador();
    }
  }

  async function mostrarToastColaConfirmada(data = {}) {
    const codigoPedido = Number(data?.codigo_pedido || 0);
    if (codigoPedido > 0) {
      marcarQueueAckVisto(codigoPedido);
    }

    if (!window.Swal?.fire) return;

    await Swal.fire(swalBaseConfig({
      toast: true,
      position: 'bottom-end',
      title: '',
      html: `
        <div class="ev-mp-toast-cola">
          <div class="ev-mp-toast-cola-icon">
            <i class="bi bi-chat-left-text"></i>
          </div>

          <div class="ev-mp-toast-cola-content">
            <div class="ev-mp-toast-cola-title">Solicitud en cola</div>

            <div class="ev-mp-toast-cola-text">
              Se registró correctamente.
            </div>

            <div class="ev-mp-toast-cola-text ev-mp-toast-cola-text--strong">
              ${
                Number(data?.posicion_cola || 0) > 0
                  ? `Posición actual: ${Number(data.posicion_cola)}`
                  : 'En espera de atención'
              }
            </div>

            <div class="ev-mp-toast-cola-actions">
              <button type="button" class="ev-mp-toast-cola-link">Ver mis pedidos</button>
            </div>
          </div>
        </div>
      `,
      showConfirmButton: false,
      timer: 4200,
      timerProgressBar: true,
      allowOutsideClick: true,
      allowEscapeKey: true,
      customClass: {
        container: 'ev-mp-swal-container',
        popup: 'ev-mp-swal-popup ev-mp-toast-cola-popup',
        htmlContainer: 'ev-mp-swal-html ev-mp-toast-cola-html'
      },
      didOpen: (toast) => {
        const btn = toast.querySelector('.ev-mp-toast-cola-link');
        if (btn) {
          btn.addEventListener('click', async (ev) => {
            ev.preventDefault();
            Swal.close();
            await abrirVistaMisPedidosComprador();
          });
        }
      }
    }));
  }

  function iniciarMonitoreoCola(data = {}) {
    limpiarSeguimientoSolicitud();

    const codigoPedido = Number(data?.codigo_pedido || 0);
    if (!codigoPedido) return;

    solicitudFlow.codigoPedido = codigoPedido;
    solicitudFlow.activo = true;
    solicitudFlow.modo = 'cola';
    solicitudFlow.cancelButtonVisible = false;
    solicitudFlow.segundosRestantes = 0;
    solicitudFlow.segundosParaCancelarRestantes = 0;

    solicitudFlow.pollingTimer = setInterval(() => {
      if (document.hidden) return;
      refrescarSeguimientoSolicitud();
    }, SOLICITUD_POLLING_MS);
  }

  function calcularSegundosParaCancelarDesdeRestante(segundosRestantes) {
    const total = Math.max(0, Number(segundosRestantes || 0));
    const ventanaNoCancelable = SEGUNDOS_TIMEOUT_SOLICITUD - SEGUNDOS_CANCELACION_SOLICITUD;
    return Math.max(0, total - ventanaNoCancelable);
  }

  function sincronizarSeguimientoDesdeData(data = {}) {
    if (typeof data.segundos_restantes !== 'undefined') {
      const segundosRestantes = Math.max(0, Number(data.segundos_restantes || 0));
      solicitudFlow.segundosRestantes = segundosRestantes;
      solicitudFlow.segundosParaCancelarRestantes =
        calcularSegundosParaCancelarDesdeRestante(segundosRestantes);
      return;
    }

    if (typeof data.segundos_para_cancelar_restantes !== 'undefined') {
      solicitudFlow.segundosParaCancelarRestantes = Math.max(
        0,
        Number(data.segundos_para_cancelar_restantes || 0)
      );
      return;
    }

    if (typeof data.segundos_para_cancelar !== 'undefined') {
      solicitudFlow.segundosParaCancelarRestantes = Math.max(
        0,
        Number(data.segundos_para_cancelar || 0)
      );
      return;
    }

    solicitudFlow.segundosParaCancelarRestantes = Math.max(
      0,
      Number(solicitudFlow.segundosParaCancelarRestantes || SEGUNDOS_CANCELACION_SOLICITUD)
    );
  }

  function tickSeguimientoSolicitud() {
    if (!solicitudFlow.activo) return;
    if (solicitudFlow.modo !== 'respuesta') return;

    if (solicitudFlow.segundosRestantes > 0) {
      solicitudFlow.segundosRestantes -= 1;
    }

    if (solicitudFlow.segundosParaCancelarRestantes > 0) {
      solicitudFlow.segundosParaCancelarRestantes -= 1;
    }

    actualizarUiSeguimiento({
      segundos_restantes: solicitudFlow.segundosRestantes,
      segundos_para_cancelar_restantes: solicitudFlow.segundosParaCancelarRestantes,
      puede_cancelar: solicitudFlow.segundosParaCancelarRestantes <= 0 ? 1 : 0
    });
  }

  function htmlSeguimientoSolicitud(opts = {}) {
    const {
      tituloProducto = 'tu solicitud',
      estadoTexto = 'Esperando ser atendido...',
      detalle = 'El vendedor aún no responde.',
      segundosRestantes = 0,
      requierePreparacion = false,
      montoDescontado = 0,
      variant = 'success'
    } = opts;

    const iconHtml = variant === 'info'
      ? `
        <div class="ev-mp-swal-status-icon ev-mp-swal-status-icon--info" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="32" r="30" fill="none"></circle>
            <path d="M32 18.5C34.5 18.5 36.3 20.2 36.3 22.6C36.3 25 34.5 26.8 32 26.8C29.5 26.8 27.7 25 27.7 22.6C27.7 20.2 29.5 18.5 32 18.5Z" fill="#38BDF8"/>
            <path d="M32 31.5V45.5" stroke="#38BDF8" stroke-width="5" stroke-linecap="round"/>
          </svg>
        </div>
      `
      : `
        <div class="ev-mp-swal-status-icon ev-mp-swal-status-icon--success" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none">
            <path d="M18 33.5L27.5 43L46 23.5" stroke="#84CC16" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      `;

    const notaBilletera = requierePreparacion && Number(montoDescontado || 0) > 0
      ? `
        <div class="ev-mp-swal-note">
          Se reservó <strong>${formatPrecio(montoDescontado)}</strong> de tu billetera por tratarse de un producto con preparación.
          Si la solicitud no continúa, el saldo se devolverá automáticamente.
        </div>
      `
      : '';

    return `
      <div style="text-align:center;">
        ${iconHtml}

        <div class="ev-mp-swal-subtitle">${escapeHtml(estadoTexto)}</div>

        <div class="ev-mp-swal-soft-text">
          ${escapeHtml(detalle)}
        </div>

        <div class="ev-mp-swal-timer-wrap">
          <div class="ev-mp-swal-timer-pill">
            <i class="bi bi-clock-history"></i>
            <span id="ev_sp_timer_text">Tiempo restante: ${formatDuracionSegundos(segundosRestantes)}</span>
          </div>
        </div>

        <div class="ev-mp-swal-product-card">
          <span class="ev-mp-swal-product-label">Solicitud</span>
          <div class="ev-mp-swal-product">${escapeHtml(tituloProducto)}</div>
        </div>

        <div id="ev_sp_cancel_hint" class="ev-mp-swal-cancel-hint"></div>

        ${notaBilletera}
      </div>
    `;
  }

  async function consultarEstadoSolicitud(codigoPedido) {
    const { resp, json, text } = await fetchJsonRobusto(`${BASE}/api/pedidos/${encodeURIComponent(codigoPedido)}/estado`, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    });

    if (await manejarRespuestaAuth(resp, json)) return null;

    if (!json) {
      err('ESTADO SOLICITUD no devolvió JSON:', (text || '').slice(0, 400));
      return null;
    }

    if (!resp.ok || !json.ok) {
      return null;
    }

    return json.data || null;
  }

  async function obtenerSolicitudActivaActual() {
    const { resp, json, text } = await fetchJsonRobusto(`${BASE}/api/pedidos/solicitud-activa`, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    });

    if (await manejarRespuestaAuth(resp, json)) return null;

    if (!json) {
      err('SOLICITUD ACTIVA no devolvió JSON:', (text || '').slice(0, 400));
      return null;
    }

    if (!resp.ok || !json.ok) {
      return null;
    }

    return json.data || null;
  }

  async function reanudarSeguimientoDespuesDeCancelarFallido(codigoPedido, fallbackData = null) {
    let data = (fallbackData && typeof fallbackData === 'object') ? fallbackData : null;

    if (!data && Number(codigoPedido || 0) > 0) {
      data = await consultarEstadoSolicitud(Number(codigoPedido || 0));
    }

    if (!data) {
      data = await obtenerSolicitudActivaActual();
    }

    if (!data) {
      limpiarSeguimientoSolicitud();
      return;
    }

    const estado = String(data.estado_actual || '').trim();
    const finalizado = Number(data.finalizado || 0) === 1;

    if (finalizado || !estadoSigueEsperandoRespuesta(estado)) {
      await finalizarSeguimientoSolicitud(data);
      return;
    }

    limpiarSeguimientoSolicitud();
    await restoreSolicitudActiva(data);
  }

  async function restoreSolicitudActiva(data = null) {
    if (restaurandoSolicitudActiva) return;
    if (solicitudFlow.activo && solicitudFlow.codigoPedido > 0) return;

    restaurandoSolicitudActiva = true;

    try {
      let solicitud = data;

      if (!solicitud) {
        solicitud = await obtenerSolicitudActivaActual();
      }

      if (!solicitud || !solicitud.codigo_pedido) {
        return;
      }

      if (solicitudFlow.codigoPedido === Number(solicitud.codigo_pedido || 0) && solicitudFlow.activo) {
        return;
      }

      const estadoActual = String(solicitud.estado_actual || '').trim();

      if (estadoActual === 'cola_pendiente_confirmacion') {
        const deseaEsperar = await Swal.fire(swalBaseConfig({
          title: 'Hay una cola en atención',
          html: htmlSeguimientoCola({
            tituloProducto: String(solicitud?.titulo_producto || 'tu solicitud'),
            detalle: solicitud?.mensaje_estado || 'El vendedor tiene otros pedidos en atención. ¿Deseas continuar en cola?',
            posicionCola: Number(solicitud?.posicion_cola || 0)
          }),
          showCancelButton: true,
          confirmButtonText: 'Sí, esperar',
          cancelButtonText: 'No, cancelar'
        }));

        if (deseaEsperar.isConfirmed) {
          const confirmar = await fetchJsonRobusto(`${BASE}/api/pedidos/${encodeURIComponent(solicitud.codigo_pedido)}/confirmar-cola`, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store'
          });

          if (await manejarRespuestaAuth(confirmar.resp, confirmar.json)) return;

          if (!confirmar.json || !confirmar.resp.ok || !confirmar.json.ok) {
            await notify('error', 'Error', confirmar.json?.mensaje || 'No se pudo confirmar tu permanencia en la cola.', {
              subtitle: 'No se pudo registrar tu decisión'
            });
            await reanudarSeguimientoDespuesDeCancelarFallido(solicitud.codigo_pedido, confirmar.json?.data || null);
            return;
          }

          const dataConfirmada = confirmar.json.data || solicitud;
          const estadoConfirmado = String(dataConfirmada.estado_actual || '').trim();

          if (estadoConfirmado === 'cola_aceptada' || estadoConfirmado === 'cola_pendiente_confirmacion') {
            iniciarMonitoreoCola(dataConfirmada);
            await mostrarToastColaConfirmada(dataConfirmada);
            return;
          }

          await iniciarSeguimientoSolicitud(dataConfirmada);
          return;
        }

        const cancelado = await cancelarSolicitudBackend(solicitud.codigo_pedido);

        if (!cancelado || !cancelado.json) {
          await notify('warning', 'Solicitud no actualizada', 'No se pudo cancelar en este momento. Se volverá a sincronizar el seguimiento.', {
            subtitle: 'Se reintentará consultar el estado'
          });
          await reanudarSeguimientoDespuesDeCancelarFallido(solicitud.codigo_pedido);
          return;
        }

        if (!cancelado.resp.ok || !cancelado.json.ok) {
          await notify('warning', 'No se pudo cancelar', cancelado.json?.mensaje || 'La solicitud ya no se puede cancelar.', {
            subtitle: 'La solicitud mantiene su estado actual'
          });
          await reanudarSeguimientoDespuesDeCancelarFallido(solicitud.codigo_pedido, cancelado.json?.data || null);
          return;
        }

        await finalizarSeguimientoSolicitud(cancelado.json.data || {});
        return;
      }

      if (estadoActual === 'cola_aceptada') {
        iniciarMonitoreoCola(solicitud);

        if (!yaSeMostroQueueAck(Number(solicitud?.codigo_pedido || 0))) {
          await mostrarToastColaConfirmada(solicitud);
        }

        return;
      }

      await iniciarSeguimientoSolicitud(solicitud);
    } catch (e) {
      err('EXCEPTION restoreSolicitudActiva', e);
    } finally {
      restaurandoSolicitudActiva = false;
    }
  }

  async function cancelarSolicitudBackend(codigoPedido) {
    const { resp, json, text } = await fetchJsonRobusto(`${BASE}/api/pedidos/${encodeURIComponent(codigoPedido)}/cancelar`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      cache: 'no-store',
      body: JSON.stringify({
        motivo_cancelacion: 'Solicitud cancelada por el comprador durante el tiempo de espera.'
      })
    });

    if (await manejarRespuestaAuth(resp, json)) return null;

    if (!json) {
      err('CANCELAR SOLICITUD no devolvió JSON:', (text || '').slice(0, 400));
      return null;
    }

    return { resp, json };
  }

  function actualizarUiSeguimiento(data) {
    const timerEl = document.getElementById('ev_sp_timer_text');
    const hintEl = document.getElementById('ev_sp_cancel_hint');

    const segundosRestantes = Math.max(
      0,
      Number(
        typeof data?.segundos_restantes !== 'undefined'
          ? data.segundos_restantes
          : solicitudFlow.segundosRestantes
      )
    );

    let segundosParaCancelarRestantes;

    if (typeof data?.segundos_restantes !== 'undefined') {
      segundosParaCancelarRestantes =
        calcularSegundosParaCancelarDesdeRestante(segundosRestantes);
    } else if (typeof data?.segundos_para_cancelar_restantes !== 'undefined') {
      segundosParaCancelarRestantes = Math.max(
        0,
        Number(data.segundos_para_cancelar_restantes || 0)
      );
    } else {
      segundosParaCancelarRestantes = Math.max(
        0,
        Number(solicitudFlow.segundosParaCancelarRestantes || 0)
      );
    }

    const yaPuedeCancelar = segundosParaCancelarRestantes <= 0;

    solicitudFlow.segundosRestantes = segundosRestantes;
    solicitudFlow.segundosParaCancelarRestantes = segundosParaCancelarRestantes;

    if (timerEl) {
      timerEl.textContent = `Tiempo restante: ${formatDuracionSegundos(segundosRestantes)}`;
    }

    if (hintEl) {
      if (yaPuedeCancelar) {
        hintEl.textContent = 'Ya puedes cancelar esta solicitud si ya no deseas continuar.';
      } else {
        hintEl.textContent = `Podrás cancelarla en ${formatDuracionSegundos(segundosParaCancelarRestantes)}.`;
      }
    }

    if (window.Swal?.isVisible()) {
      if (yaPuedeCancelar && !solicitudFlow.cancelButtonVisible) {
        solicitudFlow.cancelButtonVisible = true;
        Swal.update({
          showCancelButton: true,
          cancelButtonText: 'Cancelar solicitud'
        });
      } else if (!yaPuedeCancelar && solicitudFlow.cancelButtonVisible) {
        solicitudFlow.cancelButtonVisible = false;
        Swal.update({
          showCancelButton: false
        });
      }
    }
  }

  function obtenerMensajeCambioEstadoSolicitud(data) {
    const estado = String(data?.estado_actual || '').trim();

    switch (estado) {
      case 'rechazado_vendedor':
        return {
          title: 'Solicitud rechazada',
          subtitle: 'El vendedor no aceptó el pedido',
          text: data?.motivo_estado || 'El vendedor rechazó tu solicitud.'
        };

      case 'cancelado_vendedor':
        return {
          title: 'Pedido cancelado',
          subtitle: 'El vendedor canceló el pedido',
          text: data?.motivo_estado || 'El vendedor canceló el pedido.'
        };

      case 'en_preparacion':
        return {
          title: 'Pedido aceptado',
          subtitle: 'El vendedor ya confirmó la solicitud',
          text: 'El vendedor aceptó tu solicitud y tu pedido está en preparación.'
        };

      case 'despachando':
        return {
          title: 'Pedido aceptado',
          subtitle: 'El vendedor ya confirmó la solicitud',
          text: 'El vendedor aceptó tu solicitud y está preparando el despacho.'
        };

      case 'listo_para_entrega':
        return {
          title: 'Pedido listo',
          subtitle: 'El pedido avanzó de estado',
          text: 'Tu pedido ya se encuentra listo para entrega.'
        };

      case 'en_camino':
        return {
          title: 'Pedido en camino',
          subtitle: 'El pedido avanzó de estado',
          text: 'Tu pedido ya va en camino.'
        };

      case 'en_punto_entrega':
        return {
          title: 'Pedido en punto de entrega',
          subtitle: 'El pedido avanzó de estado',
          text: 'Tu pedido ya llegó al punto de entrega.'
        };

      case 'entregado_vendedor':
        return {
          title: 'Pedido entregado',
          subtitle: 'El vendedor registró la entrega',
          text: 'El vendedor marcó el pedido como entregado.'
        };

      case 'entrega_confirmada_comprador':
        return {
          title: 'Entrega confirmada',
          subtitle: 'El pedido fue cerrado correctamente',
          text: 'La entrega del pedido fue confirmada correctamente.'
        };

      default:
        return {
          title: 'Estado actualizado',
          subtitle: 'Tu solicitud cambió de estado',
          text: data?.mensaje_estado || data?.motivo_estado || 'El estado de tu solicitud cambió.'
        };
    }
  }

  async function finalizarSeguimientoSolicitud(data) {
    const codigoPedidoFinal = Number(data?.codigo_pedido || solicitudFlow.codigoPedido || 0);
    const estado = String(data?.estado_actual || '').trim();
    const tuvoDebito = Number(data?.descuento_billetera_aplicado || 0) === 1;
    const montoDebitado = Number(data?.monto_descontado_billetera || 0);
    const devolvio = Number(data?.devolucion_billetera_aplicada || 0) === 1;
    const tituloProducto = String(data?.titulo_producto || 'tu solicitud');

    suprimirAlertaGlobalPedido(codigoPedidoFinal);
    limpiarQueueAckVisto(codigoPedidoFinal);
    limpiarSeguimientoSolicitud();
    swalCloseIfVisible();

    if (estado === 'cancelado_comprador') {
      const texto = tuvoDebito && devolvio
        ? `Tu solicitud fue cancelada correctamente. Se devolvió ${formatPrecio(montoDebitado)} a tu billetera.`
        : 'Tu solicitud fue cancelada correctamente.';

      await notify('success', 'Solicitud cancelada', texto, {
        subtitle: 'La solicitud fue cerrada correctamente',
        productLabel: 'Solicitud',
        productText: tituloProducto
      });
      return;
    }

    if (estado === 'sin_respuesta_vendedor') {
      const texto = tuvoDebito && devolvio
        ? `El vendedor no respondió dentro del tiempo esperado. Se devolvió ${formatPrecio(montoDebitado)} a tu billetera.`
        : 'El vendedor no respondió dentro del tiempo esperado.';

      await Swal.fire(swalBaseConfig({
        title: 'Solicitud sin respuesta',
        html: htmlSeguimientoSolicitud({
          tituloProducto,
          estadoTexto: 'El vendedor no respondió a tiempo',
          detalle: texto,
          segundosRestantes: 0,
          requierePreparacion: false,
          montoDescontado: 0,
          variant: 'info'
        }),
        showConfirmButton: true,
        confirmButtonText: 'Entendido',
        showCancelButton: false
      }));
      return;
    }

    if (estado === 'rechazado_vendedor' || estado === 'cancelado_vendedor') {
      const base = obtenerMensajeCambioEstadoSolicitud(data);
      const texto = (tuvoDebito && devolvio)
        ? `${base.text} Se devolvió ${formatPrecio(montoDebitado)} a tu billetera.`
        : base.text;

      await notify('info', base.title, texto, {
        subtitle: base.subtitle,
        productLabel: 'Solicitud',
        productText: tituloProducto
      });
      return;
    }

    const cambio = obtenerMensajeCambioEstadoSolicitud(data);
    await notify('success', cambio.title, cambio.text, {
      subtitle: cambio.subtitle,
      productLabel: 'Pedido',
      productText: tituloProducto
    });
  }

  function estadoSigueEsperandoRespuesta(estado) {
    const e = String(estado || '').trim();

    return [
      'pendiente_vendedor',
      'cola_pendiente_confirmacion',
      'cola_aceptada'
    ].includes(e);
  }

  async function refrescarSeguimientoSolicitud() {
    if (!solicitudFlow.activo || !solicitudFlow.codigoPedido) return;

    if (solicitudFlow.modo === 'cola' && !estaVistaMarketplaceActiva()) {
      limpiarSeguimientoSolicitud();
      return;
    }

    const data = await consultarEstadoSolicitud(solicitudFlow.codigoPedido);
    if (!data) return;

    const estado = String(data?.estado_actual || '').trim();
    const finalizado = Number(data?.finalizado || 0) === 1;

    if (solicitudFlow.modo === 'cola') {
      if (estado === 'cola_pendiente_confirmacion') {
        limpiarSeguimientoSolicitud();
        await restoreSolicitudActiva(data);
        return;
      }

      if (estado === 'cola_aceptada') {
        if (!yaSeMostroQueueAck(Number(data?.codigo_pedido || 0))) {
          await mostrarToastColaConfirmada(data);
        }
        return;
      }

      if (estado === 'pendiente_vendedor') {
        await iniciarSeguimientoSolicitud(data);
        return;
      }

      if (finalizado || !estadoSigueEsperandoRespuesta(estado)) {
        await finalizarSeguimientoSolicitud(data);
        return;
      }

      return;
    }

    if (estado === 'cola_pendiente_confirmacion') {
      limpiarSeguimientoSolicitud();
      await restoreSolicitudActiva(data);
      return;
    }

    if (estado === 'cola_aceptada') {
      iniciarMonitoreoCola(data);

      if (!yaSeMostroQueueAck(Number(data?.codigo_pedido || 0))) {
        await mostrarToastColaConfirmada(data);
      }

      return;
    }

    sincronizarSeguimientoDesdeData(data);
    actualizarUiSeguimiento({
      segundos_restantes: solicitudFlow.segundosRestantes,
      segundos_para_cancelar_restantes: solicitudFlow.segundosParaCancelarRestantes
    });

    if (finalizado || !estadoSigueEsperandoRespuesta(estado)) {
      await finalizarSeguimientoSolicitud(data);
    }
  }

  async function iniciarSeguimientoSolicitud(data = {}) {
    limpiarSeguimientoSolicitud();

    const codigoPedido = Number(data.codigo_pedido || 0);
    if (!codigoPedido) return;

    const estadoActual = String(data.estado_actual || '').trim();

    if (estadoActual === 'cola_pendiente_confirmacion') {
      await restoreSolicitudActiva(data);
      return;
    }

    if (estadoActual === 'cola_aceptada') {
      iniciarMonitoreoCola(data);

      if (!yaSeMostroQueueAck(Number(data?.codigo_pedido || 0))) {
        await mostrarToastColaConfirmada(data);
      }

      return;
    }

    limpiarQueueAckVisto(codigoPedido);

    solicitudFlow.codigoPedido = codigoPedido;
    solicitudFlow.activo = true;
    solicitudFlow.modo = 'respuesta';
    solicitudFlow.cancelButtonVisible = false;

    const payloadSync = {};

    if (typeof data.segundos_restantes !== 'undefined') {
      payloadSync.segundos_restantes = data.segundos_restantes;
    } else if (typeof data.segundos_timeout !== 'undefined') {
      payloadSync.segundos_restantes = data.segundos_timeout;
    }

    if (typeof data.segundos_para_cancelar_restantes !== 'undefined') {
      payloadSync.segundos_para_cancelar_restantes = data.segundos_para_cancelar_restantes;
    } else if (typeof data.segundos_para_cancelar !== 'undefined') {
      payloadSync.segundos_para_cancelar = data.segundos_para_cancelar;
    }

    sincronizarSeguimientoDesdeData(payloadSync);

    const tituloProducto = String(data.titulo_producto || 'tu solicitud');
    const requierePreparacion = Number(data.requiere_preparacion || 0) === 1;
    const montoDescontado = Number(data.monto_descontado_billetera || 0);

    const result = await Swal.fire(swalBaseConfig({
      icon: undefined,
      title: 'Solicitud enviada',
      html: htmlSeguimientoSolicitud({
        tituloProducto,
        estadoTexto: 'Esperando ser atendido...',
        detalle: 'Tu solicitud fue registrada correctamente. Estamos esperando la respuesta del vendedor.',
        segundosRestantes: solicitudFlow.segundosRestantes,
        requierePreparacion,
        montoDescontado,
        variant: 'success'
      }),
      showConfirmButton: false,
      showCancelButton: false,
      allowOutsideClick: () => {
        triggerSwalBounce();
        return false;
      },
      allowEscapeKey: false,
      customClass: {
        popup: 'ev-mp-swal-popup ev-mp-swal-popup-seguimiento',
        title: 'ev-mp-swal-title',
        htmlContainer: 'ev-mp-swal-html',
        confirmButton: 'ev-mp-swal-confirm',
        cancelButton: 'ev-mp-swal-cancel'
      },
      didOpen: () => {
        attachBounceOutsideBehavior();

        actualizarUiSeguimiento({
          segundos_restantes: solicitudFlow.segundosRestantes,
          segundos_para_cancelar_restantes: solicitudFlow.segundosParaCancelarRestantes,
          puede_cancelar: 0
        });

        solicitudFlow.intervalUi = setInterval(() => {
          tickSeguimientoSolicitud();
        }, 1000);

        solicitudFlow.pollingTimer = setInterval(() => {
          if (document.hidden) return;
          refrescarSeguimientoSolicitud();
        }, SOLICITUD_POLLING_MS);
      },
      willClose: () => {
        if (solicitudFlow.activo) {
          if (solicitudFlow.intervalUi) {
            clearInterval(solicitudFlow.intervalUi);
            solicitudFlow.intervalUi = null;
          }
          if (solicitudFlow.pollingTimer) {
            clearInterval(solicitudFlow.pollingTimer);
            solicitudFlow.pollingTimer = null;
          }
        }
      }
    }));

    if (result.dismiss === Swal.DismissReason.cancel && solicitudFlow.activo && solicitudFlow.codigoPedido) {
      const codigoPedidoActual = solicitudFlow.codigoPedido;

      if (solicitudFlow.segundosParaCancelarRestantes > 0) {
        await notify(
          'info',
          'Aún no puedes cancelar',
          `Podrás cancelar esta solicitud cuando se cumplan 2 minutos de espera. Tiempo restante: ${formatDuracionSegundos(solicitudFlow.segundosParaCancelarRestantes)}.`,
          {
            subtitle: 'Todavía debes esperar un poco más',
            productLabel: 'Solicitud',
            productText: tituloProducto
          }
        );
        await reanudarSeguimientoDespuesDeCancelarFallido(codigoPedidoActual);
        return;
      }

      const r = await cancelarSolicitudBackend(codigoPedidoActual);

      if (!r || !r.json) {
        await notify('error', 'Error', 'No se pudo cancelar la solicitud. Se volverá a sincronizar el seguimiento.', {
          subtitle: 'No se pudo registrar la cancelación'
        });
        await reanudarSeguimientoDespuesDeCancelarFallido(codigoPedidoActual);
        return;
      }

      if (!r.resp.ok || !r.json.ok) {
        await notify('warning', 'No se pudo cancelar', r.json.mensaje || 'La solicitud ya no se puede cancelar.', {
          subtitle: 'La solicitud mantiene su estado actual'
        });
        await reanudarSeguimientoDespuesDeCancelarFallido(codigoPedidoActual, r.json.data || null);
        return;
      }

      await finalizarSeguimientoSolicitud(r.json.data || {});
    }
  }

  async function abrirModalDetalle(idProducto) {
    if (!idProducto) return;

    const modalEl        = document.getElementById('mp_modal_detalle');
    const imgPrincipalEl = document.getElementById('mp_modal_img_principal');
    const thumbsWrapper  = document.getElementById('mp_modal_thumbs');
    const tituloTxtEl    = document.getElementById('mp_modal_titulo_txt');
    const precioEl       = document.getElementById('mp_modal_precio');
    const catEl          = document.getElementById('mp_modal_categoria');
    const tipoEl         = document.getElementById('mp_modal_tipo');
    const descEl         = document.getElementById('mp_modal_descripcion');

    if (!modalEl || !imgPrincipalEl || !thumbsWrapper ||
        !tituloTxtEl || !precioEl || !catEl || !tipoEl || !descEl) {
      await notify('error', 'Error UI', 'No se encontró el modal de detalle.', {
        subtitle: 'Falta un componente en la vista'
      });
      return;
    }

    try {
      const detalle = await obtenerDetalleProducto(idProducto);
      if (!detalle) return;

      const { producto, imagenes } = detalle;
      const pubBase = normalizarItem(producto);
      const vendedorIdDetalle = Number(pubBase.__codigo_usuario_vendedor || 0);
      if (vendedorIdDetalle > 0) {
        await fetchReputacionVendedores([vendedorIdDetalle]);
      }
      const pub = aplicarReputacionAItem(pubBase, vendedorIdDetalle > 0 ? reputacionVendedoresCache.get(vendedorIdDetalle) : null);

      window.EV_MP_DETALLE_ACTUAL = {
        ...producto,
        imagenes: Array.isArray(imagenes) ? imagenes : [],
        vendedor_disponible: Number(pub.__vendedor_disponible || 0) === 1 ? 1 : 0
      };

      const titulo = pub.__titulo || 'Publicación';
      const precio = pub.__precio || 0;
      const desc   = pub.__descripcion || '—';

      tituloTxtEl.textContent = titulo;
      precioEl.textContent    = formatPrecio(precio);
      descEl.textContent      = desc;

      catEl.textContent  = pub.__categoria_nombre || '—';
      tipoEl.textContent = pub.__tipo_publicacion_label || tipoPublicacionLabelFromKey(pub.__tipo_publicacion);

      const oldRatingEl = modalEl.querySelector('.ev-mp-seller-rating-detail');
      if (oldRatingEl) oldRatingEl.remove();
      tituloTxtEl.insertAdjacentHTML('afterend', reputacionVendedorHtml(pub, { detalle: true }));

      const imgs = (Array.isArray(imagenes) ? imagenes : []).map((x) => {
        if (!x) return null;
        if (typeof x === 'string') return { url: x };
        return { url: x.url || x.ruta || x.path || x.imagen || x.src || '' };
      }).filter(Boolean);

      let portada = pub.__imagen_portada || '';
      if (!portada && imgs.length > 0) portada = imgs[0].url;

      imgPrincipalEl.src = buildImgUrl(portada);
      imgPrincipalEl.alt = titulo;

      thumbsWrapper.innerHTML = '';
      imgs.forEach((imgObj, index) => {
        const urlImg = buildImgUrl(imgObj.url);

        const thumbWrapper = document.createElement('div');
        thumbWrapper.className = 'ev-mp-modal-thumb';

        const thumbImg = document.createElement('img');
        thumbImg.src   = urlImg;
        thumbImg.alt   = `Imagen ${index + 1} de ${titulo}`;

        thumbWrapper.appendChild(thumbImg);

        thumbWrapper.addEventListener('click', () => {
          imgPrincipalEl.src = urlImg;
          document.querySelectorAll('.ev-mp-modal-thumb')
            .forEach(el => el.classList.remove('active'));
          thumbWrapper.classList.add('active');
        });

        thumbsWrapper.appendChild(thumbWrapper);
      });

      const firstThumb = thumbsWrapper.querySelector('.ev-mp-modal-thumb');
      if (firstThumb) firstThumb.classList.add('active');

      if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const modalDetalle = window.bootstrap.Modal.getOrCreateInstance(modalEl, {
          backdrop: 'static',
          keyboard: false
        });
        modalDetalle.show();
      } else if (window.$ && typeof window.$(modalEl).modal === 'function') {
        window.$(modalEl).modal({ backdrop: 'static', keyboard: false });
        window.$(modalEl).modal('show');
      }

    } catch (e) {
      err('EXCEPTION DETALLE', e);
      await notify('error', 'Error inesperado', 'Ocurrió un problema al cargar el detalle.', {
        subtitle: 'No se pudo abrir el detalle'
      });
    }
  }

  function recalcularTotalSolicitud() {
    const cantidadEl = document.getElementById('mp_sp_cantidad');
    const totalEl = document.getElementById('mp_sp_total');
    const precioUnitarioEl = document.getElementById('mp_sp_precio_unitario');

    if (!cantidadEl || !totalEl || !precioUnitarioEl) return;

    const cantidad = Math.max(1, parseInt(cantidadEl.value || '1', 10) || 1);
    const precioUnitario = Number(precioUnitarioEl.value || 0);
    const total = cantidad * precioUnitario;

    cantidadEl.value = String(cantidad);
    totalEl.value = formatPrecio(total);
  }

  function actualizarVisibilidadEntregaProgramada() {
    const tipoEntregaEl = document.getElementById('mp_sp_tipo_entrega');
    const wrapProgramada = document.getElementById('mp_sp_wrap_programada');
    const fechaEl = document.getElementById('mp_sp_fecha_programada');

    if (!tipoEntregaEl || !wrapProgramada || !fechaEl) return;

    const esProgramada = tipoEntregaEl.value === 'programada';
    wrapProgramada.classList.toggle('d-none', !esProgramada);

    if (esProgramada) {
      const minimo = obtenerFechaMinimaProgramada();
      const maximo = new Date(minimo.getTime() + (48 * 60 * 60 * 1000));

      fechaEl.min = toDateTimeLocalValue(minimo);
      fechaEl.max = toDateTimeLocalValue(maximo);

      if (fechaEl.value) {
        const normalizada = normalizarFechaProgramadaInput(fechaEl.value);
        fechaEl.value = normalizada;

        if (normalizada && normalizada < fechaEl.min) {
          fechaEl.value = fechaEl.min;
        } else if (normalizada && normalizada > fechaEl.max) {
          fechaEl.value = fechaEl.max;
        }
      }
    } else {
      fechaEl.value = '';
    }
  }

  function abrirModalSolicitudDesdeProducto(producto) {
    if (!producto || typeof producto !== 'object') {
      notify('error', 'Error', 'No se pudo preparar la solicitud.', {
        subtitle: 'No se pudo abrir el formulario'
      });
      return;
    }

    const tipoPublicacion = normalizarTipoPublicacion(producto);
    const esServicio = tipoPublicacion === 'servicio';
    const labelPublicacion = tipoPublicacionLabelFromKey(tipoPublicacion);

    if (Number(producto.es_producto_propio || 0) === 1) {
      notify('warning', 'Acción no permitida', 'No puedes solicitar un pedido sobre tu propia publicación.', {
        subtitle: 'Esta publicación te pertenece'
      });
      return;
    }

    if (Number(producto.vendedor_disponible || 0) !== 1) {
      notify('info', 'Vendedor no disponible', 'Este vecino no se encuentra disponible para recibir pedidos en este momento.', {
        subtitle: 'Intenta nuevamente más tarde',
        productLabel: 'Publicación',
        productText: producto.titulo || labelPublicacion
      });
      return;
    }

    const modalSolicitudEl = document.getElementById('mp_modal_solicitud');
    const modalDetalleEl   = document.getElementById('mp_modal_detalle');

    if (!modalSolicitudEl) {
      notify('error', 'Error UI', 'No se encontró el modal de solicitud.', {
        subtitle: 'Falta un componente en la vista'
      });
      return;
    }

    const codigoProductoEl   = document.getElementById('mp_sp_codigo_producto');
    const precioUnitarioEl   = document.getElementById('mp_sp_precio_unitario');
    const requierePrepEl     = document.getElementById('mp_sp_requiere_preparacion');
    const nombreProductoEl   = document.getElementById('mp_sp_nombre_producto');
    const cantidadEl         = document.getElementById('mp_sp_cantidad');
    const tipoEntregaEl      = document.getElementById('mp_sp_tipo_entrega');
    const direccionEl        = document.getElementById('mp_sp_direccion');
    const mensajeEl          = document.getElementById('mp_sp_mensaje');
    const fechaProgramadaEl  = document.getElementById('mp_sp_fecha_programada');

    if (codigoProductoEl) codigoProductoEl.value = producto.codigo_producto || '';
    if (precioUnitarioEl) precioUnitarioEl.value = producto.precio || 0;
    if (requierePrepEl) requierePrepEl.value = Number(producto.requiere_preparacion || 0) === 1 ? '1' : '0';
    if (nombreProductoEl) nombreProductoEl.value = producto.titulo || '';

    const labelNombreSolicitud = document.querySelector('label[for="mp_sp_nombre_producto"]') ||
      (nombreProductoEl ? nombreProductoEl.closest('.col-12')?.querySelector('label') : null);
    if (labelNombreSolicitud) {
      labelNombreSolicitud.textContent = esServicio ? 'Nombre del servicio' : 'Nombre del producto';
    }

    if (cantidadEl) cantidadEl.value = '1';
    if (tipoEntregaEl) tipoEntregaEl.value = 'inmediata';
    if (direccionEl) direccionEl.value = '';
    if (mensajeEl) mensajeEl.value = '';
    if (fechaProgramadaEl) fechaProgramadaEl.value = '';

    recalcularTotalSolicitud();
    actualizarVisibilidadEntregaProgramada();

    const abrirSolicitud = () => {
      if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const modalSolicitud = window.bootstrap.Modal.getOrCreateInstance(modalSolicitudEl, {
          backdrop: 'static',
          keyboard: false
        });
        modalSolicitud.show();
      } else if (window.$ && typeof window.$(modalSolicitudEl).modal === 'function') {
        window.$(modalSolicitudEl).modal({
          backdrop: 'static',
          keyboard: false
        });
        window.$(modalSolicitudEl).modal('show');
      }
    };

    if (
      modalDetalleEl &&
      modalDetalleEl.classList.contains('show') &&
      window.bootstrap &&
      typeof window.bootstrap.Modal === 'function'
    ) {
      const modalDetalle = window.bootstrap.Modal.getOrCreateInstance(modalDetalleEl, {
        backdrop: 'static',
        keyboard: false
      });

      const handler = () => {
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        abrirSolicitud();
      };

      modalDetalleEl.addEventListener('hidden.bs.modal', handler, { once: true });
      modalDetalle.hide();
      return;
    }

    abrirSolicitud();
  }

  async function enviarSolicitudPedido() {
    const codigoProductoEl  = document.getElementById('mp_sp_codigo_producto');
    const cantidadEl        = document.getElementById('mp_sp_cantidad');
    const tipoEntregaEl     = document.getElementById('mp_sp_tipo_entrega');
    const fechaProgramadaEl = document.getElementById('mp_sp_fecha_programada');
    const direccionEl       = document.getElementById('mp_sp_direccion');
    const mensajeEl         = document.getElementById('mp_sp_mensaje');
    const requierePrepEl    = document.getElementById('mp_sp_requiere_preparacion');
    const precioUnitarioEl  = document.getElementById('mp_sp_precio_unitario');
    const nombreProductoEl  = document.getElementById('mp_sp_nombre_producto');
    const btnSubmit         = document.querySelector('#mp_form_solicitud_pedido button[type="submit"]');

    const codigoProducto   = Number(codigoProductoEl?.value || 0);
    const cantidad         = Math.max(1, parseInt(cantidadEl?.value || '1', 10) || 1);
    const tipoEntrega      = (tipoEntregaEl?.value || 'inmediata').trim();
    const fechaProgramada  = normalizarFechaProgramadaInput(fechaProgramadaEl?.value || '');
    const direccionEntrega = (direccionEl?.value || '').trim();
    const mensajeComprador = (mensajeEl?.value || '').trim();
    const requierePreparacion = Number(requierePrepEl?.value || 0) === 1;
    const precioUnitario = Number(precioUnitarioEl?.value || 0);
    const totalPedido = Number((precioUnitario * cantidad).toFixed(2));
    const tituloProducto = String(nombreProductoEl?.value || 'tu solicitud');

    if (!codigoProducto) {
      await notify('warning', 'Validación', 'No se encontró la publicación seleccionada.', {
        subtitle: 'Completa correctamente el formulario'
      });
      return;
    }

    if (!direccionEntrega) {
      await notify('warning', 'Validación', 'Debes ingresar la dirección de entrega.', {
        subtitle: 'Completa correctamente el formulario',
        productLabel: 'Solicitud',
        productText: tituloProducto
      });
      return;
    }

    if (tipoEntrega === 'programada' && !fechaProgramada) {
      await notify('warning', 'Validación', 'Debes seleccionar la fecha y hora programada.', {
        subtitle: 'Completa correctamente el formulario',
        productLabel: 'Solicitud',
        productText: tituloProducto
      });
      return;
    }

    if (tipoEntrega === 'programada') {
      const minima = obtenerFechaMinimaProgramada();
      const seleccionada = new Date(fechaProgramada);

      if (Number.isNaN(seleccionada.getTime()) || seleccionada.getTime() < minima.getTime()) {
        await notify('warning', 'Validación', 'La fecha programada debe ser una hora futura válida.', {
          subtitle: 'Revisa la fecha y hora programada',
          productLabel: 'Solicitud',
          productText: tituloProducto
        });
        return;
      }
    }

    try {
      if (btnSubmit) btnSubmit.disabled = true;

      showLoadingSolicitud();

      if (requierePreparacion) {
        const saldoActual = await obtenerSaldoBilleteraActual();

        if (saldoActual !== null && saldoActual < totalPedido) {
          swalCloseIfVisible();
          await notifySaldoInsuficiente(totalPedido, saldoActual);
          return;
        }
      }

      const fd = new FormData();
      fd.append('codigo_producto', String(codigoProducto));
      fd.append('cantidad', String(cantidad));
      fd.append('tipo_entrega', tipoEntrega);
      fd.append('direccion_entrega', direccionEntrega);
      fd.append('mensaje_comprador', mensajeComprador);
      if (tipoEntrega === 'programada') {
        fd.append('fecha_hora_programada', fechaProgramada);
      }

      const { resp, json, text } = await fetchJsonRobusto(`${BASE}/api/pedidos/registrar`, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
      });

      if (await manejarRespuestaAuth(resp, json)) return;

      if (resp.status === 409 && json?.error === 'SIN_RESIDENCIA_ACTIVA' && json?.redirect) {
        swalCloseIfVisible();
        await notify('warning', 'Residencia requerida', json.mensaje || 'Debes completar tu residencia.', {
          subtitle: 'Necesitas una residencia activa'
        });
        window.location.href = json.redirect;
        return;
      }

      if (!json) {
        swalCloseIfVisible();
        err('REGISTRAR PEDIDO no devolvió JSON:', (text || '').slice(0, 400));
        await notify('error', 'Error', 'La respuesta del servidor no fue válida.', {
          subtitle: 'No se pudo registrar la solicitud'
        });
        return;
      }

      if (!resp.ok || !json.ok) {
        swalCloseIfVisible();

        const apiError = String(json?.error || '').trim();
        const apiMsg = json?.mensaje || json?.error || 'No se pudo registrar la solicitud.';

        if (apiError === 'VENDEDOR_NO_DISPONIBLE') {
          await notify('info', 'Vendedor no disponible', apiMsg, {
            subtitle: 'No se pudo continuar con la solicitud',
            productLabel: 'Solicitud',
            productText: tituloProducto
          });
          await refrescarDisponibilidadMarketplace({ force: true });
          return;
        }

        if (apiError === 'PRODUCTO_PROPIO') {
          await notify('warning', 'Acción no permitida', apiMsg, {
            subtitle: 'No puedes pedir tu propia publicación'
          });
          return;
        }

        if (apiError === 'SALDO_INSUFICIENTE_BILLETERA') {
          const saldoActual = Number(json?.saldo_actual || 0);
          const montoRequerido = Number(json?.monto_requerido || totalPedido);
          await notifySaldoInsuficiente(montoRequerido, saldoActual);
          return;
        }

        if (
          apiError === 'PUBLICACION_FUERA_DE_CONJUNTO' ||
          apiError === 'PUBLICACION_FUERA_DE_RESIDENCIA' ||
          apiError === 'PRODUCTO_NO_APROBADO' ||
          apiError === 'PUBLICACION_NO_VIGENTE' ||
          apiError === 'VENDEDOR_NO_HABILITADO'
        ) {
          await notify('warning', 'Publicación no disponible', apiMsg, {
            subtitle: 'Esta publicación ya no está disponible',
            productLabel: 'Solicitud',
            productText: tituloProducto
          });
          await refrescarDisponibilidadMarketplace({ force: true });
          return;
        }

        await notify('error', 'Error', apiMsg, {
          subtitle: 'No se pudo registrar la solicitud',
          productLabel: 'Solicitud',
          productText: tituloProducto
        });
        return;
      }

      const data = json?.data || {};
      const estadoRegistrado = String(data?.estado_actual || '').trim();

      const form = document.getElementById('mp_form_solicitud_pedido');
      try { form?.reset(); } catch (_) {}

      const modalSolicitudEl = document.getElementById('mp_modal_solicitud');
      if (modalSolicitudEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const modalSolicitud = window.bootstrap.Modal.getOrCreateInstance(modalSolicitudEl, {
          backdrop: 'static',
          keyboard: false
        });
        modalSolicitud.hide();
        await esperarModalOculto(modalSolicitudEl);
      }

      recalcularTotalSolicitud();
      actualizarVisibilidadEntregaProgramada();

      swalCloseIfVisible();

      if ((estadoRegistrado === 'cola_pendiente_confirmacion' || estadoRegistrado === 'cola_aceptada') && data?.codigo_pedido) {
        if (estadoRegistrado === 'cola_aceptada') {
          iniciarMonitoreoCola(data);
          await mostrarToastColaConfirmada(data);
          return;
        }

        const deseaEsperar = await Swal.fire(swalBaseConfig({
          title: 'Hay una cola en atención',
          html: htmlSeguimientoCola({
            tituloProducto: String(data?.titulo_producto || tituloProducto),
            detalle: data?.mensaje_estado || 'El vendedor tiene pedidos en atención. ¿Deseas continuar en cola?',
            posicionCola: Number(data?.posicion_cola || 0)
          }),
          showCancelButton: true,
          confirmButtonText: 'Sí, esperar',
          cancelButtonText: 'No, cancelar'
        }));

        if (deseaEsperar.isConfirmed) {
          const confirmar = await fetchJsonRobusto(`${BASE}/api/pedidos/${encodeURIComponent(data.codigo_pedido)}/confirmar-cola`, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store'
          });

          if (await manejarRespuestaAuth(confirmar.resp, confirmar.json)) return;

          if (!confirmar.json || !confirmar.resp.ok || !confirmar.json.ok) {
            await notify('error', 'Error', confirmar.json?.mensaje || 'No se pudo confirmar tu permanencia en la cola.', {
              subtitle: 'No se pudo registrar tu decisión'
            });
            await reanudarSeguimientoDespuesDeCancelarFallido(data.codigo_pedido, confirmar.json?.data || null);
            return;
          }

          const dataConfirmada = confirmar.json.data || data;
          const estadoConfirmado = String(dataConfirmada.estado_actual || '').trim();

          if (estadoConfirmado === 'cola_aceptada' || estadoConfirmado === 'cola_pendiente_confirmacion') {
            iniciarMonitoreoCola(dataConfirmada);
            await mostrarToastColaConfirmada(dataConfirmada);
            return;
          }

          await iniciarSeguimientoSolicitud(dataConfirmada);
          return;
        }

        const cancelado = await cancelarSolicitudBackend(data.codigo_pedido);

        if (!cancelado || !cancelado.json) {
          await notify('warning', 'Solicitud no actualizada', 'No se pudo cancelar en este momento. Se volverá a sincronizar el seguimiento.', {
            subtitle: 'Se reintentará consultar el estado'
          });
          await reanudarSeguimientoDespuesDeCancelarFallido(data.codigo_pedido);
          return;
        }

        if (!cancelado.resp.ok || !cancelado.json.ok) {
          await notify('warning', 'No se pudo cancelar', cancelado.json?.mensaje || 'La solicitud ya no se puede cancelar.', {
            subtitle: 'La solicitud mantiene su estado actual'
          });
          await reanudarSeguimientoDespuesDeCancelarFallido(data.codigo_pedido, cancelado.json?.data || null);
          return;
        }

        await finalizarSeguimientoSolicitud(cancelado.json.data || {});
        return;
      }

      await iniciarSeguimientoSolicitud(data);

      if (window.EVPollingControl && typeof window.EVPollingControl.revisarPedidosVendedor === 'function') {
        window.setTimeout(() => {
          window.EVPollingControl.revisarPedidosVendedor({ silent: true, force: true });
        }, 700);
      }

    } catch (e) {
      err('EXCEPTION registrar pedido', e);
      swalCloseIfVisible();
      await notify('error', 'Error inesperado', 'Ocurrió un problema al registrar la solicitud.', {
        subtitle: 'No se pudo completar el proceso'
      });
    } finally {
      if (btnSubmit) btnSubmit.disabled = false;
    }
  }

  function bindSolicitudModalEvents() {
    const cantidadEl = document.getElementById('mp_sp_cantidad');
    const tipoEntregaEl = document.getElementById('mp_sp_tipo_entrega');
    const fechaProgramadaEl = document.getElementById('mp_sp_fecha_programada');
    const formSolicitud = document.getElementById('mp_form_solicitud_pedido');
    const btnPedirDetalle = document.getElementById('btnPedirAhoraDetalle');

    if (cantidadEl && !cantidadEl.dataset.boundSolicitud) {
      cantidadEl.dataset.boundSolicitud = '1';
      cantidadEl.addEventListener('input', recalcularTotalSolicitud);
      cantidadEl.addEventListener('change', recalcularTotalSolicitud);
    }

    if (tipoEntregaEl && !tipoEntregaEl.dataset.boundSolicitud) {
      tipoEntregaEl.dataset.boundSolicitud = '1';
      tipoEntregaEl.addEventListener('change', actualizarVisibilidadEntregaProgramada);
    }

    if (fechaProgramadaEl && !fechaProgramadaEl.dataset.boundSolicitudNorm) {
      fechaProgramadaEl.dataset.boundSolicitudNorm = '1';
      fechaProgramadaEl.addEventListener('change', () => {
        fechaProgramadaEl.value = normalizarFechaProgramadaInput(fechaProgramadaEl.value);
      });
      fechaProgramadaEl.addEventListener('blur', () => {
        fechaProgramadaEl.value = normalizarFechaProgramadaInput(fechaProgramadaEl.value);
      });
    }

    if (btnPedirDetalle && !btnPedirDetalle.dataset.boundSolicitud) {
      btnPedirDetalle.dataset.boundSolicitud = '1';
      btnPedirDetalle.addEventListener('click', () => {
        if (!window.EV_MP_DETALLE_ACTUAL) {
          notify('warning', 'Detalle no disponible', 'Primero abre una publicación válida.', {
            subtitle: 'No se encontró la publicación'
          });
          return;
        }

        abrirModalSolicitudDesdeProducto(window.EV_MP_DETALLE_ACTUAL);
      });
    }

    if (formSolicitud && !formSolicitud.dataset.boundSolicitud) {
      formSolicitud.dataset.boundSolicitud = '1';
      formSolicitud.addEventListener('submit', async (e) => {
        e.preventDefault();
        await enviarSolicitudPedido();
      });
    }
  }

  function cardHtml(p) {
    const id     = p.__id;
    const titulo = escapeHtml(p.__titulo || '');
    const desc   = escapeHtml(p.__descripcion || '');
    const precio = formatPrecio(p.__precio || 0);
    const imgUrl = buildImgUrl(p.__imagen_portada);

    const esPotenciado = Number(p.__es_potenciado || 0) === 1;
    const vendedorDisponible = Number(p.__vendedor_disponible || 0) === 1;
    const esServicio = esServicioPublicacion(p);
    const tipoLabel = esServicio ? 'Servicio' : 'Producto';
    const textoAccion = esServicio ? 'Solicitar' : 'Pedir ahora';

    let badgesHtml = `<span class="ev-mp-badge" style="background:${esServicio ? '#0EA5E9cc' : '#16A34Acc'}">${tipoLabel}</span>`;
    if (esPotenciado) {
      badgesHtml += `<span class="ev-mp-badge ev-mp-badge-potenciado">Recomendado</span>`;
    }

    const estadoClass = vendedorDisponible
      ? 'ev-mp-card-top-status ev-mp-card-top-status-on'
      : 'ev-mp-card-top-status ev-mp-card-top-status-off';

    const estadoLabel = vendedorDisponible ? 'Disponible' : 'No disponible';
    const pedirAttrs = vendedorDisponible ? '' : 'disabled aria-disabled="true" title="El vendedor no está disponible"';

    return `
      <div class="ev-mp-card" data-id="${escapeHtml(String(id))}">
        <div class="${estadoClass}" title="${estadoLabel}" aria-label="${estadoLabel}">
          <span class="ev-mp-card-top-status-text">${estadoLabel}</span>
        </div>

        <div class="ev-mp-card-media">
          <img src="${imgUrl}" alt="${titulo}" loading="lazy">
          <div class="ev-mp-card-badges">${badgesHtml}</div>
        </div>

        <div class="ev-mp-card-body">
          <h5 class="ev-mp-card-title">${titulo}</h5>
          ${reputacionVendedorHtml(p)}
          <p class="ev-mp-card-price">${precio}</p>
          <p class="ev-mp-card-desc">${desc}</p>

          <div class="ev-mp-card-actions">
            <button type="button" class="btn btn-outline-success ev-mp-btn-detalle">Ver detalle</button>
            <button type="button" class="btn btn-success ev-mp-btn-pedir" ${pedirAttrs}>${textoAccion}</button>
          </div>
        </div>
      </div>
    `;
  }

  function bindCardActions(container) {
    if (!container) return;

    Array.from(container.querySelectorAll('.ev-mp-card')).forEach((card) => {
      const id = card.getAttribute('data-id');
      if (!id) return;

      const btnDetalle = card.querySelector('.ev-mp-btn-detalle');
      const btnPedir = card.querySelector('.ev-mp-btn-pedir');

      if (btnDetalle && !btnDetalle.dataset.boundDetalle) {
        btnDetalle.dataset.boundDetalle = '1';
        btnDetalle.addEventListener('click', () => abrirModalDetalle(id));
      }

      if (btnPedir && !btnPedir.dataset.boundPedir) {
        btnPedir.dataset.boundPedir = '1';
        btnPedir.addEventListener('click', async () => {
          if (btnPedir.disabled) return;

          const detalle = await obtenerDetalleProducto(id);
          if (!detalle) return;

          const { producto, imagenes } = detalle;

          window.EV_MP_DETALLE_ACTUAL = {
            ...producto,
            imagenes: Array.isArray(imagenes) ? imagenes : [],
            vendedor_disponible: Number(producto?.vendedor_disponible ?? producto?.disponibilidad_pedidos_vendedor ?? 0) === 1 ? 1 : 0
          };

          abrirModalSolicitudDesdeProducto(window.EV_MP_DETALLE_ACTUAL);
        });
      }
    });
  }

  async function cargarTiposYDetectar() {
    try {
      const url = `${BASE}/tipos`;
      const { resp, json, text } = await fetchJsonRobusto(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
        cache: 'no-store'
      });

      if (!resp.ok || !json) {
        warn('No se pudo cargar /tipos:', (text || '').slice(0, 200));
        await cargarCategoriasPorScope();
        return;
      }

      const tipos = getArrayFromPayload(json);
      const mapByName = new Map();

      tipos.forEach(t => {
        const id = Number(t.codigo_tipo || 0) || 0;
        const name = (t.nombre || '').toString();
        mapByName.set(normalizar(name), id);
      });

      tipoIdProducto = mapByName.get('producto') || mapByName.get('productos') || 0;
      tipoIdServicio = mapByName.get('servicio') || mapByName.get('servicios') || 0;

      if (!tipoIdProducto) {
        for (const [k, v] of mapByName.entries()) {
          if (k.includes('product')) { tipoIdProducto = v; break; }
        }
      }

      if (!tipoIdServicio) {
        for (const [k, v] of mapByName.entries()) {
          if (k.includes('servic')) { tipoIdServicio = v; break; }
        }
      }

      log('Tipos detectados:', { tipoIdProducto, tipoIdServicio });
      await cargarCategoriasPorScope();
    } catch (e) {
      warn('Error cargando tipos/categorías:', e);
      await cargarCategoriasPorScope();
    }
  }

  async function obtenerCategoriasPorTipo(tipoId) {
    if (!tipoId) return [];

    const url = `${BASE}/tipos/${encodeURIComponent(tipoId)}/categoria_grupo`;
    const { resp, json, text } = await fetchJsonRobusto(url, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    });

    if (!resp.ok || !json) {
      warn('No se pudo cargar categorías:', (text || '').slice(0, 200));
      return [];
    }

    return getArrayFromPayload(json);
  }

  function categoriaOptionHtml(r, tipoKey) {
    const id = Number(r.codigo_categoria || 0) || 0;
    if (!id) return '';

    const grupo = (r.grupo || '').toString().trim();
    const cat = (r.categoria || '').toString().trim();
    const label = grupo ? `${grupo} — ${cat}` : cat;

    return `<option value="${tipoKey}:${id}">${escapeHtml(label || 'Categoría')}</option>`;
  }

  async function cargarCategoriasPorScope() {
    if (!refs.selectCategoriaProductos) return;

    // Cierre UX: en Marketplace las categorías deben corresponder a las publicaciones visibles.
    // Esto evita que en Productos aparezcan categorías de Servicios, y viceversa.
    if (Array.isArray(publicaciones) && publicaciones.length > 0) {
      cargarCategoriasDesdePublicacionesFallback();
      return;
    }

    const previous = String(categoriaFiltroValor || '0');
    let html = '';

    if (scope === 'servicios') {
      if (refs.categoriaLabel) refs.categoriaLabel.textContent = 'Categoría';
      const rowsServicios = await obtenerCategoriasPorTipo(tipoIdServicio);
      html = `<option value="0">Todas las categorías</option>` +
        rowsServicios.map(r => categoriaOptionHtml(r, 'servicio')).join('');
    } else if (scope === 'productos') {
      if (refs.categoriaLabel) refs.categoriaLabel.textContent = 'Categoría';
      const rowsProductos = await obtenerCategoriasPorTipo(tipoIdProducto);
      html = `<option value="0">Todas las categorías</option>` +
        rowsProductos.map(r => categoriaOptionHtml(r, 'producto')).join('');
    } else {
      if (refs.categoriaLabel) refs.categoriaLabel.textContent = 'Categoría';
      const [rowsProductos, rowsServicios] = await Promise.all([
        obtenerCategoriasPorTipo(tipoIdProducto),
        obtenerCategoriasPorTipo(tipoIdServicio)
      ]);

      const optsProductos = rowsProductos.map(r => categoriaOptionHtml(r, 'producto')).join('');
      const optsServicios = rowsServicios.map(r => categoriaOptionHtml(r, 'servicio')).join('');

      html = `<option value="0">Todas las categorías</option>`;
      if (optsProductos) html += `<optgroup label="Productos">${optsProductos}</optgroup>`;
      if (optsServicios) html += `<optgroup label="Servicios">${optsServicios}</optgroup>`;
    }

    refs.selectCategoriaProductos.innerHTML = html || `<option value="0">Todas las categorías</option>`;

    const existsPrevious = Array.from(refs.selectCategoriaProductos.options).some(opt => opt.value === previous);
    categoriaFiltroValor = existsPrevious ? previous : '0';
    refs.selectCategoriaProductos.value = categoriaFiltroValor;
    refreshCustomSelect('mp_categoria_producto');
  }

  function parseCategoriaFiltroValor() {
    const raw = String(categoriaFiltroValor || '0').trim();
    if (!raw || raw === '0') {
      return { tipo: '', id: 0 };
    }

    if (raw.includes(':')) {
      const [tipo, idRaw] = raw.split(':', 2);
      return {
        tipo: normalizar(tipo),
        id: Number(idRaw || 0) || 0
      };
    }

    return {
      tipo: scope === 'servicios' ? 'servicio' : 'producto',
      id: Number(raw || 0) || 0
    };
  }

  function optionCategoriaDesdePublicacion(item) {
    const id = Number(item.__codigo_categoria || 0) || 0;
    if (!id) return null;

    const tipo = esServicioPublicacion(item) ? 'servicio' : 'producto';
    const nombre = String(item.__categoria_nombre || item.categoria_nombre || item.categoria || '').trim();
    const label = nombre || 'Categoría';

    return {
      key: `${tipo}:${id}`,
      tipo,
      id,
      label
    };
  }

  function cargarCategoriasDesdePublicacionesFallback() {
    if (!refs.selectCategoriaProductos) return;
    if (!Array.isArray(publicaciones) || publicaciones.length === 0) return;

    const previous = String(categoriaFiltroValor || '0');
    const mapProductos = new Map();
    const mapServicios = new Map();

    publicaciones.forEach(item => {
      const opt = optionCategoriaDesdePublicacion(item);
      if (!opt) return;

      if (opt.tipo === 'servicio') mapServicios.set(opt.key, opt);
      else mapProductos.set(opt.key, opt);
    });

    const buildOptions = (items) => Array.from(items.values())
      .sort((a, b) => a.label.localeCompare(b.label, 'es'))
      .map(o => `<option value="${o.key}">${escapeHtml(o.label)}</option>`)
      .join('');

    let html = '';

    if (scope === 'servicios') {
      if (refs.categoriaLabel) refs.categoriaLabel.textContent = 'Categoría';
      html = `<option value="0">Todas las categorías</option>` + buildOptions(mapServicios);
    } else if (scope === 'productos') {
      if (refs.categoriaLabel) refs.categoriaLabel.textContent = 'Categoría';
      html = `<option value="0">Todas las categorías</option>` + buildOptions(mapProductos);
    } else {
      if (refs.categoriaLabel) refs.categoriaLabel.textContent = 'Categoría';
      const optsProductos = buildOptions(mapProductos);
      const optsServicios = buildOptions(mapServicios);

      html = `<option value="0">Todas las categorías</option>`;
      if (optsProductos) html += `<optgroup label="Productos">${optsProductos}</optgroup>`;
      if (optsServicios) html += `<optgroup label="Servicios">${optsServicios}</optgroup>`;
    }

    refs.selectCategoriaProductos.innerHTML = html || `<option value="0">Todas las categorías</option>`;

    const existsPrevious = Array.from(refs.selectCategoriaProductos.options).some(opt => opt.value === previous);
    categoriaFiltroValor = existsPrevious ? previous : '0';
    refs.selectCategoriaProductos.value = categoriaFiltroValor;
    refreshCustomSelect('mp_categoria_producto');
  }

  function aplicarFiltros(listaBase) {
    let lista = Array.isArray(listaBase) ? [...listaBase] : [];

    if (scope === 'productos') {
      lista = lista.filter(p => esProductoPublicacion(p));
    } else if (scope === 'servicios') {
      lista = lista.filter(p => esServicioPublicacion(p));
    }

    if (textoBusqueda.trim() !== '') {
      const needle = normalizar(textoBusqueda);
      lista = lista.filter((p) => {
        const hay = normalizar((p.__titulo || '') + ' ' + (p.__descripcion || ''));
        return hay.includes(needle);
      });
    }

    const categoriaFiltro = parseCategoriaFiltroValor();
    if (categoriaFiltro.id > 0) {
      lista = lista.filter((p) => {
        const tipoItem = esServicioPublicacion(p) ? 'servicio' : 'producto';

        if (categoriaFiltro.tipo && categoriaFiltro.tipo !== 'all' && categoriaFiltro.tipo !== tipoItem) {
          return false;
        }

        return Number(p.__codigo_categoria || 0) === Number(categoriaFiltro.id);
      });
    }

    lista.sort((a, b) => {
      const precioA = Number(a.__precio || 0);
      const precioB = Number(b.__precio || 0);
      const recA = Number(a.__orden_reciente || a.__id || 0);
      const recB = Number(b.__orden_reciente || b.__id || 0);

      switch (criterioOrden) {
        case 'precio_menor':
          return precioA - precioB;
        case 'precio_mayor':
          return precioB - precioA;
        case 'recientes':
        default:
          return recB - recA;
      }
    });

    return lista;
  }

  function splitServiciosProductos(lista) {
    const servicios = [];
    const productos = [];

    lista.forEach(p => {
      if (esServicioPublicacion(p)) servicios.push(p);
      else productos.push(p);
    });

    return { servicios, productos };
  }

  function pintarSecciones(listaFiltrada) {
    if (!refs.gridServicios || !refs.gridProductos) return;

    const { servicios, productos } = splitServiciosProductos(listaFiltrada);

    if (refs.countServicios) refs.countServicios.textContent = String(servicios.length);
    if (refs.countProductos) refs.countProductos.textContent = String(productos.length);

    /*
      UX EV premium:
      - En "Todos", si una sección está vacía y la otra sí tiene resultados,
        ocultamos la sección vacía para que el usuario vea contenido útil de inmediato.
      - Si el usuario entra específicamente a "Servicios" o "Productos", sí mostramos
        su empty state contextual.
    */
    const ocultarServiciosVacioEnTodos = (scope === 'todos' && servicios.length === 0 && productos.length > 0);
    const ocultarProductosVacioEnTodos = (scope === 'todos' && productos.length === 0 && servicios.length > 0);

    const mostrarServicios = (scope === 'todos' || scope === 'servicios') && !ocultarServiciosVacioEnTodos;
    const mostrarProductos = (scope === 'todos' || scope === 'productos') && !ocultarProductosVacioEnTodos;

    if (refs.wrapServicios) refs.wrapServicios.style.display = mostrarServicios ? '' : 'none';
    if (refs.wrapProductos) refs.wrapProductos.style.display = mostrarProductos ? '' : 'none';

    refs.gridServicios.innerHTML = mostrarServicios
      ? servicios.map(cardHtml).join('')
      : '';

    refs.gridProductos.innerHTML = mostrarProductos
      ? productos.map(cardHtml).join('')
      : '';

    if (refs.emptyServicios) {
      refs.emptyServicios.style.display = (mostrarServicios && servicios.length === 0) ? 'flex' : 'none';
    }

    if (refs.emptyProductos) {
      refs.emptyProductos.style.display = (mostrarProductos && productos.length === 0) ? 'flex' : 'none';
    }

    bindCardActions(refs.gridServicios);
    bindCardActions(refs.gridProductos);

    const total = (scope === 'servicios')
      ? servicios.length
      : (scope === 'productos')
        ? productos.length
        : (servicios.length + productos.length);

    const etiqueta = total === 1 ? 'publicación disponible' : 'publicaciones disponibles';
    setResumen(`${total} ${etiqueta} en ${CONDO_NOMBRE_RESUMEN}`);
  }

  function aplicarYRedibujar() {
    applyScopeVisibility();

    const lista = aplicarFiltros(publicaciones);
    const total = Array.isArray(lista) ? lista.length : 0;

    if (!total) {
      showEmpty('No encontramos publicaciones con los filtros actuales.');
      setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);

      if (refs.countServicios) refs.countServicios.textContent = '0';
      if (refs.countProductos) refs.countProductos.textContent = '0';

      if (refs.gridServicios) refs.gridServicios.innerHTML = '';
      if (refs.gridProductos) refs.gridProductos.innerHTML = '';

      const mostrarServicios = (scope === 'todos' || scope === 'servicios');
      const mostrarProductos = (scope === 'todos' || scope === 'productos');

      if (refs.wrapServicios) refs.wrapServicios.style.display = mostrarServicios ? '' : 'none';
      if (refs.wrapProductos) refs.wrapProductos.style.display = mostrarProductos ? '' : 'none';

      if (refs.emptyServicios) refs.emptyServicios.style.display = mostrarServicios ? 'flex' : 'none';
      if (refs.emptyProductos) refs.emptyProductos.style.display = mostrarProductos ? 'flex' : 'none';
      return;
    }

    hideEmpty();
    pintarSecciones(lista);
  }

  async function cargarPublicaciones(opciones = {}) {
    const esSilent = opciones.silent === true;

    if (!refs.gridAllWrapper) return;

    if (!esSilent) {
      showLoadingMarketplace();
      setResumen('Cargando publicaciones…');
    }

    const url = `${BASE}/api/producto/marketplace`;

    try {
      const { resp, text, json } = await fetchJsonRobusto(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
        cache: 'no-store'
      });

      if (await manejarRespuestaAuth(resp, json)) return;

      if (resp.status === 409) {
        const msg = (json && (json.mensaje || json.error)) ? (json.mensaje || json.error) : 'No tienes residencia activa.';
        await notify('warning', 'Residencia requerida', msg, {
          subtitle: 'Necesitas una residencia activa'
        });
        const redir = (json && json.redirect) ? json.redirect : `${BASE}/mi-perfil`;
        window.location.href = redir;
        return;
      }

      if (!json) {
        err('API marketplace no devolvió JSON:', (text || '').slice(0, 400));
        publicaciones = [];
        if (!esSilent) {
          showEmpty('No se pudo cargar el Marketplace (respuesta no válida).');
          setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        } else {
          aplicarYRedibujar();
        }
        return;
      }

      if (!resp.ok || !json.ok) {
        const msg = json.mensaje || json.error || 'No se pudo cargar el Marketplace.';
        publicaciones = [];
        if (!esSilent) {
          showEmpty(msg);
          setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        } else {
          aplicarYRedibujar();
        }
        return;
      }

      const rawList = normalizarListaDesdeAPI(json);
      publicaciones = Array.isArray(rawList) ? rawList.map(normalizarItem) : [];
      publicaciones = await enriquecerPublicacionesConReputacion(publicaciones);

      if (refs.selectCategoriaProductos && refs.selectCategoriaProductos.options.length <= 1) {
        cargarCategoriasDesdePublicacionesFallback();
      }

      if (!publicaciones.length) {
        aplicarYRedibujar();
        if (!esSilent) {
          showEmpty(`No hay publicaciones publicadas en ${CONDO_NOMBRE_RESUMEN} todavía.`);
          setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        }
        return;
      }

      aplicarYRedibujar();
    } catch (e) {
      err('EXCEPTION cargarPublicaciones', e);
      publicaciones = [];
      if (!esSilent) {
        showEmpty('Ocurrió un problema al cargar el Marketplace.');
        setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
      } else {
        aplicarYRedibujar();
      }
    }
  }

  async function refrescarDisponibilidadMarketplace(opciones = {}) {
    const force = opciones.force === true;

    if (!force && !estaVistaMarketplaceActiva()) {
      detenerPollingDisponibilidad();
      return;
    }

    if (!force && document.hidden) return;
    if (!force && estaPausadoPorUi()) return;
    if (pollingEnCurso) return;

    pollingEnCurso = true;
    ultimoPollingMarketplaceAt = nowMs();

    try {
      await cargarPublicaciones({ silent: true });
    } catch (e) {
      warn('No se pudo refrescar disponibilidad en segundo plano:', e);
    } finally {
      pollingEnCurso = false;
    }
  }

  function iniciarPollingDisponibilidad() {
    detenerPollingDisponibilidad();

    pollingTimer = window.setInterval(() => {
      if (!estaVistaMarketplaceActiva()) {
        detenerPollingDisponibilidad();
        return;
      }

      if (document.hidden) return;

      const intervalo = estaPausadoPorUi()
        ? MARKETPLACE_IDLE_POLLING_MS
        : MARKETPLACE_POLLING_MS;

      if ((nowMs() - ultimoPollingMarketplaceAt) < intervalo) return;

      refrescarDisponibilidadMarketplace();
    }, 1000);
  }

  function detenerPollingDisponibilidad() {
    if (pollingTimer) {
      window.clearInterval(pollingTimer);
      pollingTimer = null;
    }
  }

  function bindEvents() {
    if (refs.searchInput && !refs.searchInput.dataset.boundMarketplace) {
      refs.searchInput.dataset.boundMarketplace = '1';
      refs.searchInput.addEventListener('input', () => {
        textoBusqueda = refs.searchInput.value || '';
        aplicarYRedibujar();
      });
    }

    if (refs.selectOrdenar && !refs.selectOrdenar.dataset.boundMarketplace) {
      refs.selectOrdenar.dataset.boundMarketplace = '1';
      criterioOrden = refs.selectOrdenar.value || 'recientes';
      refs.selectOrdenar.addEventListener('change', () => {
        criterioOrden = refs.selectOrdenar.value || 'recientes';
        refreshCustomSelect('mp_orden');
        aplicarYRedibujar();
      });
    }

    refs.scopeButtons.forEach(btn => {
      if (btn.dataset.boundMarketplace === '1') return;
      btn.dataset.boundMarketplace = '1';

      btn.addEventListener('click', () => {
        refs.scopeButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        scope = btn.dataset.scope || 'todos';
        categoriaFiltroValor = '0';

        closeCustomSelects();
        Promise.resolve(cargarCategoriasPorScope()).then(() => {
          if (refs.selectCategoriaProductos && refs.selectCategoriaProductos.options.length <= 1) {
            cargarCategoriasDesdePublicacionesFallback();
          }
          refreshCustomSelect('mp_categoria_producto');
          aplicarYRedibujar();
        });
      });
    });

    if (refs.selectCategoriaProductos && !refs.selectCategoriaProductos.dataset.boundMarketplace) {
      refs.selectCategoriaProductos.dataset.boundMarketplace = '1';
      refs.selectCategoriaProductos.addEventListener('change', () => {
        categoriaFiltroValor = String(refs.selectCategoriaProductos.value || '0');
        refreshCustomSelect('mp_categoria_producto');
        aplicarYRedibujar();
      });
    }

    if (!document.body.dataset.boundMarketplaceVisibility) {
      document.body.dataset.boundMarketplaceVisibility = '1';

      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          detenerPollingDisponibilidad();
        } else if (estaVistaMarketplaceActiva()) {
          refrescarDisponibilidadMarketplace({ force: true });
          iniciarPollingDisponibilidad();
        }
      });
    }

    if (!document.body.dataset.boundMarketplaceSidebarPause) {
      document.body.dataset.boundMarketplaceSidebarPause = '1';

      document.addEventListener('click', (e) => {
        if (e.target.closest('#sidebar')) marcarInteraccionUi();
      }, true);

      document.addEventListener('pointerdown', (e) => {
        if (e.target.closest('#sidebar')) marcarInteraccionUi();
      }, true);

      document.addEventListener('transitionstart', (e) => {
        if (e.target.closest && e.target.closest('#sidebar')) marcarInteraccionUi();
      }, true);

      document.addEventListener('ev:nav-start', marcarInteraccionUi);
      document.addEventListener('ev:nav-end', marcarInteraccionUi);
      document.addEventListener('ev:content-loaded', marcarInteraccionUi);
    }
  }

  function initStaticModals() {
    const modalDetalleEl = document.getElementById('mp_modal_detalle');
    const modalSolicitudEl = document.getElementById('mp_modal_solicitud');

    if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
      if (modalDetalleEl) {
        window.bootstrap.Modal.getOrCreateInstance(modalDetalleEl, {
          backdrop: 'static',
          keyboard: false
        });
      }

      if (modalSolicitudEl) {
        window.bootstrap.Modal.getOrCreateInstance(modalSolicitudEl, {
          backdrop: 'static',
          keyboard: false
        });
      }
    }
  }

  async function initMarketplace() {
    ensureMarketplaceSwalCleanStyles();
    if (!capturarRefs()) {
      detenerPollingDisponibilidad();
      return;
    }

    ensureGridCSS();
    bindEvents();
    initCustomSelects();
    bindSolicitudModalEvents();
    initStaticModals();

    if (!marketplaceInicializado) {
      marketplaceInicializado = true;
      await cargarTiposYDetectar();
    } else {
      if ((!tipoIdProducto && !tipoIdServicio)) {
        await cargarTiposYDetectar();
      } else {
        await cargarCategoriasPorScope();
      }
    }

    await cargarPublicaciones();
    await restoreSolicitudActiva();

    ultimoPollingMarketplaceAt = nowMs();
    iniciarPollingDisponibilidad();
  }

  document.addEventListener('DOMContentLoaded', initMarketplace);

  const observer = new MutationObserver(() => {
    const gridWrapper = document.getElementById('mp_grid_publicaciones');

    if (gridWrapper && gridWrapper !== refs.gridAllWrapper) {
      initMarketplace();
      return;
    }

    if (!gridWrapper && refs.gridAllWrapper) {
      detenerPollingDisponibilidad();
      refs.gridAllWrapper = null;
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });

  window.EVMarketplace = {
    init: initMarketplace,
    refreshDisponibilidad: refrescarDisponibilidadMarketplace,
    restoreSolicitudActiva: restoreSolicitudActiva,
    stopPollingDisponibilidad: detenerPollingDisponibilidad,
    pauseBriefly: marcarInteraccionUi
  };

  log('Cargado. BASE:', BASE || '(vacío)', '| Condominio:', CONDO_NOMBRE_RESUMEN);
})();
