// views/js/menuPrincipalPedidosAlertas.js
(function () {
  'use strict';

  if (window.__EV_PEDIDOS_ALERTAS_GLOBAL_INIT__ === true) return;
  window.__EV_PEDIDOS_ALERTAS_GLOBAL_INIT__ = true;

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const POLLING_MS = 6500;
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
        allowEscapeKey: true
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
    if (!BASE) return;
    if (timer) clearInterval(timer);
    revisarAlertas();
    timer = setInterval(revisarAlertas, POLLING_MS);
  }

  document.addEventListener('DOMContentLoaded', iniciar);
  document.addEventListener('ev:content-loaded', () => setTimeout(revisarAlertas, 700));
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) revisarAlertas();
  });

  window.EVPedidosAlertas = { revisar: revisarAlertas, iniciar };
})();
