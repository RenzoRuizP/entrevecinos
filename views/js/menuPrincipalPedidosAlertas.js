// views/js/menuPrincipalPedidosAlertas.js
(function () {
  'use strict';

  if (window.__EV_PEDIDOS_ALERTAS_GLOBAL_INIT__ === true) return;
  window.__EV_PEDIDOS_ALERTAS_GLOBAL_INIT__ = true;

  const BASE = (window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/+$/, '');
  const POLLING_MS = 6500;
  const SUPPRESS_KEY = 'ev_pedidos_alertas_suprimidas_v1';
  const SUPPRESS_TTL_MS = 2 * 60 * 1000;

  let timer = null;
  let mostrando = false;
  let cache = new Set();

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function readSuppressStore() {
    try {
      const now = Date.now();
      const raw = sessionStorage.getItem(SUPPRESS_KEY);
      const store = raw ? JSON.parse(raw) : {};

      Object.keys(store || {}).forEach((key) => {
        if ((now - Number(store[key] || 0)) > SUPPRESS_TTL_MS) {
          delete store[key];
        }
      });

      sessionStorage.setItem(SUPPRESS_KEY, JSON.stringify(store));
      return store && typeof store === 'object' ? store : {};
    } catch (_) {
      return {};
    }
  }

  function suprimirPedido(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (!id) return;

    try {
      const store = readSuppressStore();
      store[String(id)] = Date.now();
      sessionStorage.setItem(SUPPRESS_KEY, JSON.stringify(store));
    } catch (_) {}
  }

  function extraerPedidoId(alerta) {
    const payload = alerta?.payload || alerta?.payload_json || {};
    let p = payload;

    if (typeof p === 'string') {
      try { p = JSON.parse(p); } catch (_) { p = {}; }
    }

    return Number(
      p?.codigo_pedido ||
      p?.id_pedido ||
      p?.pedido_id ||
      p?.pedido ||
      p?.referencia_id ||
      alerta?.codigo_pedido ||
      alerta?.id_pedido ||
      alerta?.referencia_id ||
      0
    );
  }

  function estaSuprimidaPorSeguimiento(alerta) {
    const pedidoId = extraerPedidoId(alerta);
    if (!pedidoId) return false;
    const store = readSuppressStore();
    return Boolean(store[String(pedidoId)]);
  }

  function currentRoute() {
    try {
      const qs = new URLSearchParams(window.location.search);
      return qs.get('ev_goto') || window.location.pathname || '';
    } catch (_) {
      return window.location.pathname || '';
    }
  }

  async function irARuta(ruta) {
    if (!ruta) return;

    if (window.EVNav && typeof window.EVNav.loadPage === 'function') {
      await window.EVNav.loadPage(ruta, { pushState: true, replaceState: false });
      return;
    }

    window.location.href = `${BASE}/MenuPrincipal?ev_goto=${encodeURIComponent(ruta)}`;
  }

  async function marcarLeida(id) {
    if (!id) return;
    try {
      await fetch(`${BASE}/api/pedidos/alertas/${encodeURIComponent(id)}/leer`, {
        method: 'POST',
        credentials: 'include',
        headers: { Accept: 'application/json' }
      });
    } catch (_) {}
  }

  async function fetchAlertas() {
    const resp = await fetch(`${BASE}/api/pedidos/alertas`, {
      method: 'GET',
      credentials: 'include',
      cache: 'no-store',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });

    if (!resp.ok) return [];
    const json = await resp.json().catch(() => ({}));
    if (!json?.ok || !Array.isArray(json.data)) return [];
    return json.data;
  }

  async function mostrarAlerta(alerta) {
    if (!alerta || mostrando || !window.Swal?.fire) return;

    const id = Number(alerta.codigo_notificacion || 0);
    if (!id || cache.has(id)) return;

    if (estaSuprimidaPorSeguimiento(alerta)) {
      cache.add(id);
      await marcarLeida(id);
      return;
    }

    cache.add(id);
    mostrando = true;

    const payload = alerta.payload || {};
    const ruta = String(payload.ruta || '').trim();
    const yaEstaEnRuta = ruta && currentRoute() === ruta;

    try {
      const result = await Swal.fire({
        icon: 'info',
        title: alerta.titulo || 'Pedido actualizado',
        html: `
          <div style="text-align:left;max-width:420px;margin:0 auto;line-height:1.55;color:#4B5563;">
            ${escapeHtml(alerta.mensaje || 'Tu pedido cambió de estado.')}
          </div>
        `,
        showCancelButton: !!ruta && !yaEstaEnRuta,
        confirmButtonText: ruta && !yaEstaEnRuta ? 'Ir a Mis compras' : 'Entendido',
        cancelButtonText: 'Luego',
        confirmButtonColor: '#EA7C12',
        cancelButtonColor: '#6B7280',
        allowOutsideClick: false,
        allowEscapeKey: true,
        customClass: {
          container: 'ev-swal-container',
          popup: 'ev-swal-popup',
          title: 'ev-swal-title',
          htmlContainer: 'ev-swal-html',
          confirmButton: 'ev-swal-confirm',
          cancelButton: 'ev-swal-cancel'
        },
        buttonsStyling: false
      });

      await marcarLeida(id);

      if (result.isConfirmed && ruta && !yaEstaEnRuta) {
        await irARuta(ruta);
      }
    } finally {
      mostrando = false;
    }
  }

  async function revisarAlertas() {
    if (document.hidden || mostrando || window.__EV_AUTH_REDIRECTING__ === true) return;
    const alertas = await fetchAlertas();
    if (alertas.length > 0) {
      await mostrarAlerta(alertas[0]);
    }
  }

  function iniciar() {
    if (timer) clearInterval(timer);
    revisarAlertas();
    timer = setInterval(revisarAlertas, POLLING_MS);
  }

  document.addEventListener('DOMContentLoaded', iniciar);
  document.addEventListener('ev:content-loaded', () => setTimeout(revisarAlertas, 700));
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) revisarAlertas();
  });

  window.EVPedidosAlertas = {
    revisar: revisarAlertas,
    iniciar,
    suprimirPedido
  };
})();
