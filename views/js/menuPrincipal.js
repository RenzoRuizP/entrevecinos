// views/js/menuPrincipal.js
(function () {
  'use strict';

  if (window.__EV_MENU_PRINCIPAL_INIT__ === true) return;
  window.__EV_MENU_PRINCIPAL_INIT__ = true;

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const ROL = String(window.EV_ROL_USUARIO || '').trim().toLowerCase();
  const params = new URLSearchParams(window.location.search);

  const PEDIDOS_POLLING_MS = 5000;

  let pedidosPollingTimer = null;
  let pedidosSnapshotInicializado = false;
  let pedidosPendientesSnapshot = new Set();
  let pedidosAlertados = new Set();

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

  async function fetchPedidosVendedor() {
    const resp = await fetch(`${BASE}/api/pedidos/mis`, {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Accept': 'application/json'
      }
    });

    const json = await resp.json().catch(() => ({}));

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

    const cantidad = Number(item.cantidad || 0);
    const total = Number(item.monto_total || item.total || 0);
    const titulo = tituloPedidoSeguro(item);

    const r = await Swal.fire({
      icon: 'info',
      title: 'Nueva solicitud de pedido',
      html: `
        <div style="text-align:left; max-width:420px; margin:0 auto;">
          <div style="margin-bottom:8px;">
            <strong>Publicación:</strong> ${titulo}
          </div>
          <div style="margin-bottom:8px;">
            <strong>Comprador:</strong> ${String(item.nombre_vecino || 'Vecino')}
          </div>
          <div style="margin-bottom:8px;">
            <strong>Cantidad:</strong> ${cantidad}
          </div>
          <div>
            <strong>Total:</strong> S/ ${total.toFixed(2)}
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
      console.warn('[EV][Shell][PedidosVendedor] No se pudo revisar nuevas solicitudes:', e);
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

      await revisarNuevasSolicitudesVendedor({ silent: true });
    }, PEDIDOS_POLLING_MS);
  }

  function bindEventosShellPedidos() {
    if (window.__EV_SHELL_PEDIDOS_EVENTS_BOUND__ === true) return;
    window.__EV_SHELL_PEDIDOS_EVENTS_BOUND__ = true;

    document.addEventListener('visibilitychange', async () => {
      if (document.hidden) return;
      await revisarNuevasSolicitudesVendedor({ silent: true });
    });

    document.addEventListener('ev:content-loaded', async () => {
      await revisarNuevasSolicitudesVendedor({ silent: true });
    });
  }

  async function initNotificacionesPedidosVendedorGlobal() {
    obtenerCacheAlertas();
    bindEventosShellPedidos();
    await revisarNuevasSolicitudesVendedor({ silent: false });
    iniciarPollingPedidosVendedor();
  }

  async function initShell() {
    inyectarEstilosSweetAlertGlobales();
    aplicarSweetAlertGlobalFix();
    initOverlayScrollbars();
    await mostrarLoginExitosoSiAplica();
    await restaurarSolicitudActivaGlobal();

    // Solo para vecino
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
    revisarNuevasSolicitudesVendedor: revisarNuevasSolicitudesVendedor
  };
})();