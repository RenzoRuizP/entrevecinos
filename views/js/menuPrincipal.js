// views/js/menuPrincipal.js
(function () {
  'use strict';

  if (window.__EV_MENU_PRINCIPAL_INIT__ === true) return;
  window.__EV_MENU_PRINCIPAL_INIT__ = true;

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const ROL = String(window.EV_ROL_USUARIO || '').trim().toLowerCase();
  const params = new URLSearchParams(window.location.search);

  const PEDIDOS_POLLING_MS = 5000;
  const PEDIDOS_POLLING_IDLE_MS = 9000;
  const FETCH_TIMEOUT_MS = 6500;
  const UI_PAUSE_MS = 1400;

  let pedidosPollingTimer = null;
  let pedidosPollingEnCurso = false;
  let pedidosSnapshotInicializado = false;
  let pedidosPendientesSnapshot = new Set();
  let pedidosAlertados = new Set();
  let ultimaInteraccionUi = 0;
  let ultimoPollAt = 0;

  let evAudioCtx = null;
  let evAudioListo = false;
  let evAudioPendiente = false;

  function nowMs() {
    return Date.now();
  }

  function estaPausadoPorUi() {
    return (nowMs() - ultimaInteraccionUi) < UI_PAUSE_MS;
  }

  function marcarInteraccionUi() {
    ultimaInteraccionUi = nowMs();
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function getCurrentEvGoto() {
    try {
      const qs = new URLSearchParams(window.location.search);
      return qs.get('ev_goto') || '';
    } catch (_) {
      return '';
    }
  }

  function pathActualShell() {
    const goto = getCurrentEvGoto();
    if (goto) return goto;
    return window.location.pathname || '';
  }

  function crearAudioContextEV() {
    if (evAudioCtx) return evAudioCtx;

    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return null;

    evAudioCtx = new AudioContextClass();
    return evAudioCtx;
  }

  async function desbloquearAudioEV() {
    try {
      const ctx = crearAudioContextEV();
      if (!ctx) return;

      if (ctx.state === 'suspended') {
        await ctx.resume();
      }

      evAudioListo = ctx.state === 'running';

      if (evAudioListo && evAudioPendiente) {
        evAudioPendiente = false;
        reproducirSonidoNuevaSolicitudEV();
      }
    } catch (_) {
      evAudioListo = false;
    }
  }

  function bindDesbloqueoAudioEV() {
    if (window.__EV_AUDIO_UNLOCK_BOUND__ === true) return;
    window.__EV_AUDIO_UNLOCK_BOUND__ = true;

    const eventos = ['pointerdown', 'click', 'keydown', 'touchstart'];

    eventos.forEach((evento) => {
      document.addEventListener(evento, () => {
        desbloquearAudioEV();
      }, {
        passive: true
      });
    });
  }

  function reproducirSonidoNuevaSolicitudEV() {
    try {
      const ctx = crearAudioContextEV();

      if (!ctx) {
        return;
      }

      if (!evAudioListo || ctx.state !== 'running') {
        evAudioPendiente = true;
        return;
      }

      const now = ctx.currentTime;
      const master = ctx.createGain();

      master.gain.setValueAtTime(0.0001, now);
      master.gain.exponentialRampToValueAtTime(0.055, now + 0.025);
      master.gain.exponentialRampToValueAtTime(0.0001, now + 0.72);

      master.connect(ctx.destination);

      const notas = [
        { f: 659.25, start: 0.00, dur: 0.22 },
        { f: 783.99, start: 0.16, dur: 0.24 },
        { f: 987.77, start: 0.34, dur: 0.30 }
      ];

      notas.forEach((nota) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(nota.f, now + nota.start);

        gain.gain.setValueAtTime(0.0001, now + nota.start);
        gain.gain.exponentialRampToValueAtTime(0.8, now + nota.start + 0.025);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + nota.start + nota.dur);

        osc.connect(gain);
        gain.connect(master);

        osc.start(now + nota.start);
        osc.stop(now + nota.start + nota.dur + 0.04);
      });
    } catch (_) {}
  }

  function aplicarSweetAlertGlobalFix() {
    if (!window.Swal || typeof window.Swal.fire !== 'function') return;
    if (window.__EV_SWAL_GLOBAL_FIX__ === true) return;

    window.__EV_SWAL_GLOBAL_FIX__ = true;

    const fireOriginal = window.Swal.fire.bind(window.Swal);

    function aplicarRebotePopup() {
      const popup = window.Swal.getPopup ? window.Swal.getPopup() : null;
      if (!popup) return;

      popup.classList.remove('ev-swal-bounce-premium');
      void popup.offsetWidth;
      popup.classList.add('ev-swal-bounce-premium');

      window.setTimeout(() => {
        popup.classList.remove('ev-swal-bounce-premium');
      }, 520);
    }

    window.Swal.fire = function (opts) {
      const config = (opts && typeof opts === 'object') ? { ...opts } : opts;

      if (config && typeof config === 'object') {
        const userAllowOutsideClick = config.allowOutsideClick;
        const userDidOpen = config.didOpen;

        config.allowOutsideClick = () => {
          aplicarRebotePopup();

          if (typeof userAllowOutsideClick === 'function') {
            try {
              return !!userAllowOutsideClick();
            } catch (_) {
              return false;
            }
          }

          if (typeof userAllowOutsideClick === 'boolean') {
            return userAllowOutsideClick;
          }

          return false;
        };

        config.didOpen = (popup) => {
          if (popup) {
            popup.classList.add('ev-swal-ev-theme');
          }

          if (typeof userDidOpen === 'function') {
            userDidOpen(popup);
          }
        };
      }

      return fireOriginal(config);
    };
  }

  function inyectarEstilosSweetAlertGlobales() {
    if (document.getElementById('ev-swal-global-fix-style')) return;

    const style = document.createElement('style');
    style.id = 'ev-swal-global-fix-style';
    style.textContent = `
      .swal2-popup.ev-swal-bounce-premium{
        transform-origin:center center;
        animation: evSwalBouncePremium .48s cubic-bezier(.22,.9,.3,1);
        will-change: transform, box-shadow;
      }

      @keyframes evSwalBouncePremium{
        0%{
          transform: translate3d(0,0,0) scale(1);
        }
        18%{
          transform: translate3d(-10px,0,0) scale(1.008);
        }
        34%{
          transform: translate3d(9px,0,0) scale(1.01);
        }
        50%{
          transform: translate3d(-6px,0,0) scale(1.006);
        }
        66%{
          transform: translate3d(5px,0,0) scale(1.003);
        }
        82%{
          transform: translate3d(-2px,0,0) scale(1.001);
        }
        100%{
          transform: translate3d(0,0,0) scale(1);
        }
      }
    `;
    document.head.appendChild(style);
  }

  async function mostrarLoginExitosoSiAplica() {
    if (!params.has('success')) return;

    const success = params.get('success');
    if (success !== 'login_exitoso') return;

    if (window.Swal?.fire) {
      await Swal.fire({
        icon: 'success',
        title: 'Bienvenido',
        text: 'Inicio de sesión exitoso',
        timer: 1800,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    }

    const cleanUrl = `${window.location.origin}${window.location.pathname}`;
    window.history.replaceState({}, document.title, cleanUrl);
  }

  function initOverlayScrollbars() {
    const sidebarWrapper = document.querySelector('.sidebar-wrapper');
    if (!sidebarWrapper) return;

    const os = window.OverlayScrollbarsGlobal?.OverlayScrollbars;
    if (!os) return;

    os(sidebarWrapper, {
      scrollbars: {
        theme: 'os-theme-light',
        autoHide: 'leave',
        clickScroll: true
      }
    });
  }

  async function restaurarSolicitudActivaGlobal() {
    try {
      if (window.EVMarketplace && typeof window.EVMarketplace.restoreSolicitudActiva === 'function') {
        await window.EVMarketplace.restoreSolicitudActiva();
      }
    } catch (e) {
      console.warn('[EV][Shell] No se pudo restaurar la solicitud activa:', e);
    }
  }

  function obtenerCacheAlertas() {
    try {
      const raw = sessionStorage.getItem('ev_pedidos_alertados_vendedor');
      const arr = raw ? JSON.parse(raw) : [];
      if (Array.isArray(arr)) {
        pedidosAlertados = new Set(arr.map(v => Number(v || 0)).filter(Boolean));
      }
    } catch (_) {
      pedidosAlertados = new Set();
    }
  }

  function guardarCacheAlertas() {
    try {
      sessionStorage.setItem(
        'ev_pedidos_alertados_vendedor',
        JSON.stringify(Array.from(pedidosAlertados))
      );
    } catch (_) {}
  }

  function limpiarCacheAlertasAntigua() {
    if (pedidosAlertados.size <= 80) return;

    const arr = Array.from(pedidosAlertados).slice(-50);
    pedidosAlertados = new Set(arr);
    guardarCacheAlertas();
  }

  async function fetchJsonConTimeout(url, options = {}, timeoutMs = FETCH_TIMEOUT_MS) {
    const controller = new AbortController();
    const timeoutId = window.setTimeout(() => controller.abort(), timeoutMs);

    try {
      const resp = await fetch(url, {
        ...options,
        signal: controller.signal
      });

      const json = await resp.json().catch(() => ({}));

      return { resp, json };
    } finally {
      window.clearTimeout(timeoutId);
    }
  }

  async function fetchPedidosVendedor() {
    const { resp, json } = await fetchJsonConTimeout(`${BASE}/api/pedidos/mis`, {
      method: 'GET',
      credentials: 'include',
      cache: 'no-store',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (resp.status === 401) {
      throw new Error('UNAUTHORIZED');
    }

    if (resp.status === 403 && json?.error === 'CUENTA_BLOQUEADA') {
      throw new Error('CUENTA_BLOQUEADA');
    }

    if (resp.status === 409 && json?.error === 'CUENTA_OBSERVADA') {
      throw new Error('CUENTA_OBSERVADA');
    }

    if (!resp.ok || json?.ok === false) {
      throw new Error(json?.mensaje || 'No se pudieron consultar los pedidos del vendedor.');
    }

    return json?.data || {};
  }

  function tituloPedidoSeguro(item) {
    return String(item?.titulo_publicacion || item?.titulo_producto || 'tu publicación').trim();
  }

  async function irAMisPedidosVendedor() {
    const ruta = '/mis-pedidos-vendedor';

    if (window.EVNav && typeof window.EVNav.loadPage === 'function') {
      await window.EVNav.loadPage(ruta, { pushState: true, replaceState: false });
      return;
    }

    window.location.href = `${BASE}/MenuPrincipal?ev_goto=${encodeURIComponent(ruta)}`;
  }

  async function mostrarAlertaNuevaSolicitudGlobal(item) {
    if (!window.Swal?.fire || !item) return;

    const id = Number(item.codigo_pedido || item.id_pedido || 0);
    if (!id) return;

    if (pedidosAlertados.has(id)) return;
    if (window.__EV_AUTH_REDIRECTING__ === true) return;

    pedidosAlertados.add(id);
    guardarCacheAlertas();
    limpiarCacheAlertasAntigua();

    reproducirSonidoNuevaSolicitudEV();

    const cantidad = Number(item.cantidad || 0);
    const total = Number(item.monto_total || item.total || 0);
    const titulo = tituloPedidoSeguro(item);
    const comprador = String(item.nombre_vecino || item.nombre_comprador || 'Vecino');

    const r = await Swal.fire({
      icon: 'info',
      title: 'Nueva solicitud de pedido',
      html: `
        <div style="text-align:left; max-width:420px; margin:0 auto;">
          <div style="margin-bottom:8px;">
            <strong>Publicación:</strong> ${escapeHtml(titulo)}
          </div>
          <div style="margin-bottom:8px;">
            <strong>Comprador:</strong> ${escapeHtml(comprador)}
          </div>
          <div style="margin-bottom:8px;">
            <strong>Cantidad:</strong> ${escapeHtml(cantidad)}
          </div>
          <div>
            <strong>Total:</strong> S/ ${escapeHtml(total.toFixed(2))}
          </div>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Ver solicitud',
      cancelButtonText: 'Luego',
      confirmButtonColor: '#EA7C12',
      cancelButtonColor: '#6B7280',
      allowOutsideClick: false,
      allowEscapeKey: true
    });

    if (r.isConfirmed) {
      await irAMisPedidosVendedor();
    }
  }

  async function revisarNuevasSolicitudesVendedor(opts = {}) {
    const silent = opts.silent === true;
    const force = opts.force === true;

    if (pedidosPollingEnCurso) return;
    if (document.hidden && !force) return;
    if (window.__EV_AUTH_REDIRECTING__ === true) return;

    if (!force && estaPausadoPorUi()) return;

    pedidosPollingEnCurso = true;
    ultimoPollAt = nowMs();

    try {
      const data = await fetchPedidosVendedor();
      const pendientes = Array.isArray(data.pendientes) ? data.pendientes : [];

      const nuevoSnapshot = new Set(
        pendientes
          .map(item => Number(item.codigo_pedido || item.id_pedido || 0))
          .filter(Boolean)
      );

      if (!pedidosSnapshotInicializado) {
        pedidosPendientesSnapshot = nuevoSnapshot;
        pedidosSnapshotInicializado = true;
        return;
      }

      const nuevos = pendientes.filter(item => {
        const id = Number(item.codigo_pedido || item.id_pedido || 0);
        return id > 0 && !pedidosPendientesSnapshot.has(id);
      });

      pedidosPendientesSnapshot = nuevoSnapshot;

      if (!silent && nuevos.length > 0) {
        await mostrarAlertaNuevaSolicitudGlobal(nuevos[0]);
        return;
      }

      if (silent && nuevos.length > 0) {
        await mostrarAlertaNuevaSolicitudGlobal(nuevos[0]);
      }
    } catch (e) {
      const msg = String(e?.message || e);

      if (msg === 'UNAUTHORIZED') {
        console.warn('[EV][Shell][PedidosVendedor] Sesión no válida. El router se encargará del cierre en la siguiente request.');
        return;
      }

      if (msg === 'CUENTA_BLOQUEADA' || msg === 'CUENTA_OBSERVADA') {
        console.warn('[EV][Shell][PedidosVendedor] Estado de cuenta restringido:', msg);
        return;
      }

      console.warn('[EV][Shell][PedidosVendedor] No se pudo revisar nuevas solicitudes:', e);
    } finally {
      pedidosPollingEnCurso = false;
    }
  }

  function detenerPollingPedidosVendedor() {
    if (pedidosPollingTimer) {
      clearInterval(pedidosPollingTimer);
      pedidosPollingTimer = null;
    }
  }

  function iniciarPollingPedidosVendedor() {
    detenerPollingPedidosVendedor();

    pedidosPollingTimer = window.setInterval(async () => {
      if (document.hidden) return;
      if (window.__EV_AUTH_REDIRECTING__ === true) return;

      const intervaloMinimo = estaPausadoPorUi()
        ? PEDIDOS_POLLING_IDLE_MS
        : PEDIDOS_POLLING_MS;

      if ((nowMs() - ultimoPollAt) < intervaloMinimo) return;

      await revisarNuevasSolicitudesVendedor({ silent: true });
    }, 1000);
  }

  function bindEventosShellPedidos() {
    if (window.__EV_SHELL_PEDIDOS_EVENTS_BOUND__ === true) return;
    window.__EV_SHELL_PEDIDOS_EVENTS_BOUND__ = true;

    document.addEventListener('visibilitychange', async () => {
      if (document.hidden) return;
      await revisarNuevasSolicitudesVendedor({ silent: true, force: true });
    });

    document.addEventListener('ev:content-loaded', async () => {
      marcarInteraccionUi();

      window.setTimeout(async () => {
        await revisarNuevasSolicitudesVendedor({ silent: true });
      }, 900);
    });

    document.addEventListener('ev:nav-start', () => {
      marcarInteraccionUi();
    });

    document.addEventListener('ev:nav-end', () => {
      marcarInteraccionUi();
    });

    document.addEventListener('click', (e) => {
      if (e.target.closest('#sidebar')) {
        marcarInteraccionUi();
      }
    }, true);

    document.addEventListener('pointerdown', (e) => {
      if (e.target.closest('#sidebar')) {
        marcarInteraccionUi();
      }
    }, true);

    document.addEventListener('transitionstart', (e) => {
      if (e.target.closest && e.target.closest('#sidebar')) {
        marcarInteraccionUi();
      }
    }, true);
  }

  async function initNotificacionesPedidosVendedorGlobal() {
    obtenerCacheAlertas();
    bindEventosShellPedidos();

    await revisarNuevasSolicitudesVendedor({
      silent: false,
      force: true
    });

    iniciarPollingPedidosVendedor();
  }

  function exponerControlPolling() {
    window.EVPollingControl = Object.assign(window.EVPollingControl || {}, {
      pauseBriefly: marcarInteraccionUi,
      revisarPedidosVendedor: revisarNuevasSolicitudesVendedor,
      detenerPedidosVendedor: detenerPollingPedidosVendedor,
      iniciarPedidosVendedor: iniciarPollingPedidosVendedor
    });
  }

  async function initShell() {
    inyectarEstilosSweetAlertGlobales();
    aplicarSweetAlertGlobalFix();
    bindDesbloqueoAudioEV();
    initOverlayScrollbars();
    exponerControlPolling();

    await mostrarLoginExitosoSiAplica();
    await restaurarSolicitudActivaGlobal();

    if (ROL === 'vecino') {
      await initNotificacionesPedidosVendedorGlobal();
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initShell();
  });

  window.EVShell = {
    init: initShell,
    restaurarSolicitudActiva: restaurarSolicitudActivaGlobal,
    revisarNuevasSolicitudesVendedor: revisarNuevasSolicitudesVendedor,
    marcarInteraccionUi: marcarInteraccionUi,
    pathActualShell: pathActualShell,
    reproducirSonidoNuevaSolicitud: reproducirSonidoNuevaSolicitudEV
  };
})();