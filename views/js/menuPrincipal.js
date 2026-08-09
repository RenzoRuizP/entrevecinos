// views/js/menuPrincipal.js
(function () {
  'use strict';

  if (window.__EV_MENU_PRINCIPAL_INIT__ === true) return;
  window.__EV_MENU_PRINCIPAL_INIT__ = true;

  const BASE = (window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/+$/, '');
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

  let alertasCompradorPollingTimer = null;
  let alertasCompradorEnCurso = false;
  let alertasCompradorMostradas = new Set();
  let alertasCompradorEventosMostrados = new Set();
  let ultimoPollAlertasCompradorAt = 0;

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
        0%{ transform: translate3d(0,0,0) scale(1); }
        18%{ transform: translate3d(-10px,0,0) scale(1.008); }
        34%{ transform: translate3d(9px,0,0) scale(1.01); }
        50%{ transform: translate3d(-6px,0,0) scale(1.006); }
        66%{ transform: translate3d(5px,0,0) scale(1.003); }
        82%{ transform: translate3d(-2px,0,0) scale(1.001); }
        100%{ transform: translate3d(0,0,0) scale(1); }
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


  function reclamarAlertaSolicitudCompartida(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (!id) return false;

    const key = `ev_alerta_solicitud_pedido_${id}`;
    const ahora = Date.now();

    try {
      const anterior = Number(sessionStorage.getItem(key) || 0);
      if (anterior > 0 && (ahora - anterior) < 5 * 60 * 1000) {
        return false;
      }
      sessionStorage.setItem(key, String(ahora));
    } catch (_) {}

    return true;
  }

  function obtenerCacheAlertasComprador() {
    try {
      const raw = sessionStorage.getItem('ev_pedidos_alertas_comprador_v3');
      const arr = raw ? JSON.parse(raw) : [];

      if (Array.isArray(arr)) {
        alertasCompradorMostradas = new Set(
          arr.map(v => Number(v || 0)).filter(Boolean)
        );
      }
    } catch (_) {
      alertasCompradorMostradas = new Set();
    }

    try {
      const rawEventos = sessionStorage.getItem('ev_pedidos_alertas_comprador_eventos_v3');
      const arrEventos = rawEventos ? JSON.parse(rawEventos) : [];

      if (Array.isArray(arrEventos)) {
        alertasCompradorEventosMostrados = new Set(
          arrEventos.map(v => String(v || '').trim()).filter(Boolean)
        );
      }
    } catch (_) {
      alertasCompradorEventosMostrados = new Set();
    }
  }

  function guardarCacheAlertasComprador() {
    try {
      sessionStorage.setItem(
        'ev_pedidos_alertas_comprador_v3',
        JSON.stringify(Array.from(alertasCompradorMostradas))
      );
    } catch (_) {}

    try {
      sessionStorage.setItem(
        'ev_pedidos_alertas_comprador_eventos_v3',
        JSON.stringify(Array.from(alertasCompradorEventosMostrados))
      );
    } catch (_) {}
  }

  function limpiarCacheAlertasCompradorAntigua() {
    if (alertasCompradorMostradas.size > 100) {
      const arr = Array.from(alertasCompradorMostradas).slice(-60);
      alertasCompradorMostradas = new Set(arr);
    }

    if (alertasCompradorEventosMostrados.size > 100) {
      const arrEventos = Array.from(alertasCompradorEventosMostrados).slice(-60);
      alertasCompradorEventosMostrados = new Set(arrEventos);
    }

    guardarCacheAlertasComprador();
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

  async function fetchAlertasPedidoComprador() {
    const { resp, json } = await fetchJsonConTimeout(`${BASE}/api/pedidos/alertas`, {
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
      throw new Error(json?.mensaje || 'No se pudieron consultar las alertas del comprador.');
    }

    return Array.isArray(json?.data) ? json.data : [];
  }

  async function fetchPedidosCompradorParaAlertas() {
    const { resp, json } = await fetchJsonConTimeout(`${BASE}/api/pedidos/mis-comprador`, {
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
      throw new Error(json?.mensaje || 'No se pudieron consultar los pedidos del comprador.');
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

  async function irAMisPedidosComprador() {
    const ruta = '/mis-pedidos-comprador';

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
    if (!reclamarAlertaSolicitudCompartida(id)) return;
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

  function estadoAlertaComprador(alerta) {
    const payload = alerta?.payload && typeof alerta.payload === 'object'
      ? alerta.payload
      : {};

    const estadoPayload = String(payload.estado_actual || '').trim().toLowerCase();
    if (estadoPayload) return estadoPayload;

    const subcategoria = String(alerta?.subcategoria || '').trim().toLowerCase();
    return subcategoria.replace(/^avance_estado_/, '');
  }

  function claveEventoAlertaComprador(alerta) {
    const claveManual = String(alerta?._clave_evento || '').trim();
    if (claveManual !== '') return claveManual;

    const idPedido = Number(alerta?.referencia_id || alerta?.payload?.codigo_pedido || 0);
    const estado = estadoAlertaComprador(alerta);

    if (idPedido > 0 && estado) {
      return `${idPedido}:${estado}`;
    }

    return `notificacion:${Number(alerta?.codigo_notificacion || 0)}`;
  }

  function esAlertaModalComprador(alerta) {
    if (!alerta || typeof alerta !== 'object') return false;

    const payload = alerta.payload && typeof alerta.payload === 'object'
      ? alerta.payload
      : {};

    const rolDestino = String(payload.rol_destino || '').trim().toLowerCase();
    const estado = estadoAlertaComprador(alerta);

    return rolDestino === 'comprador' && [
      'en_punto_entrega',
      'entregado_vendedor',
      'rechazado_vendedor',
      'cancelado_vendedor',
      'sin_respuesta_vendedor'
    ].includes(estado);
  }

  function obtenerIconoSwalAlertaComprador(estado) {
    if (['rechazado_vendedor', 'cancelado_vendedor', 'sin_respuesta_vendedor'].includes(estado)) {
      return 'warning';
    }

    if (estado === 'entregado_vendedor') {
      return 'success';
    }

    return 'info';
  }

  function obtenerTextoBotonAlertaComprador(estado) {
    if (estado === 'entregado_vendedor') return 'Confirmar entrega';
    if (estado === 'en_punto_entrega') return 'Ver pedido';
    return 'Revisar pedido';
  }

  function construirAlertaPuntoEntregaDesdePedido(item) {
    const codigoPedido = Number(item?.codigo_pedido || 0);
    if (codigoPedido <= 0) return null;

    const estado = String(item?.estado_actual || '').trim();
    if (estado !== 'en_punto_entrega') return null;

    const segundos = Number(item?.segundos_recojo_restantes || 0);
    if (segundos <= 0) return null;

    const tituloProducto = String(item?.titulo_publicacion || item?.titulo_producto || 'tu pedido').trim();

    return {
      _sintetica: true,
      _clave_evento: `${codigoPedido}:en_punto_entrega`,
      codigo_notificacion: 0,
      subcategoria: 'avance_estado_en_punto_entrega',
      referencia_id: codigoPedido,
      titulo: 'Tu pedido llegó al punto de entrega',
      mensaje: `Tu pedido de ${tituloProducto} ya está esperando recojo. Tienes hasta 6 minutos para recibirlo antes de que el vendedor pueda cancelar por no recepción.`,
      fecha: '',
      payload: {
        codigo_pedido: codigoPedido,
        estado_actual: 'en_punto_entrega',
        rol_destino: 'comprador',
        ruta: '/mis-pedidos-comprador',
        titulo_producto: tituloProducto
      }
    };
  }

  function obtenerAlertasPuntoEntregaDesdePedidos(data) {
    const grupos = [
      ...(Array.isArray(data?.en_proceso) ? data.en_proceso : []),
      ...(Array.isArray(data?.pendientes) ? data.pendientes : [])
    ];

    return grupos
      .map(construirAlertaPuntoEntregaDesdePedido)
      .filter(Boolean);
  }

  async function marcarAlertaPedidoLeida(codigoNotificacion) {
    const id = Number(codigoNotificacion || 0);
    if (!id) return;

    try {
      await fetchJsonConTimeout(`${BASE}/api/pedidos/alertas/${id}/leer`, {
        method: 'POST',
        credentials: 'include',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
    } catch (e) {
      console.warn('[EV][Shell][AlertasComprador] No se pudo marcar como leída:', e);
    }
  }


  function ensureCalificacionShellStyles() {
    const ID = 'ev-shell-calificacion-premium-style';
    if (document.getElementById(ID)) return;

    const style = document.createElement('style');
    style.id = ID;
    style.type = 'text/css';
    style.textContent = `
      .ev-shell-rating-box{text-align:left;display:flex;flex-direction:column;gap:13px;color:#111827;}
      .ev-shell-rating-sub{color:#6B7280;font-size:.94rem;line-height:1.55;text-align:center;margin-top:-4px;max-width:460px;margin-left:auto;margin-right:auto;}
      .ev-shell-rating-target{border:1px solid rgba(229,231,235,.95);border-radius:20px;background:linear-gradient(180deg,#FFFFFF 0%,#FCFDFC 100%);padding:13px 15px;box-shadow:0 10px 22px rgba(15,23,42,.055);}
      .ev-shell-rating-target span{display:block;color:#6B7280;font-size:.73rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;}
      .ev-shell-rating-target strong{display:block;color:#0F592F;font-size:1rem;font-weight:900;line-height:1.25;}
      .ev-shell-rating-stars{display:flex;justify-content:center;gap:8px;margin:2px 0 0;}
      .ev-shell-rating-star{width:44px;height:44px;border-radius:15px;border:1px solid #E5E7EB;background:#fff;color:#CBD5E1;font-size:1.5rem;line-height:1;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 8px 16px rgba(15,23,42,.05);transition:transform .15s ease,box-shadow .15s ease,color .15s ease,background .15s ease,border-color .15s ease;}
      .ev-shell-rating-star:hover,.ev-shell-rating-star.is-active{color:#F59E0B;background:#FFF7ED;border-color:#FCD9BD;transform:translateY(-1px);box-shadow:0 14px 24px rgba(234,124,18,.14);}
      .ev-shell-rating-label{text-align:center;color:#0F592F;font-weight:900;font-size:.96rem;min-height:22px;margin-top:1px;}
      .ev-shell-rating-helper{text-align:center;color:#6B7280;font-size:.82rem;font-weight:750;line-height:1.35;margin-top:-7px;}
      .ev-shell-rating-chips{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;min-height:42px;}
      .ev-shell-rating-chip{border:1px solid rgba(22,163,74,.18);background:#fff;color:#0F592F;border-radius:999px;padding:8px 11px;font-size:.82rem;font-weight:850;box-shadow:0 6px 14px rgba(15,23,42,.04);transition:transform .15s ease,box-shadow .15s ease,background .15s ease,border-color .15s ease,color .15s ease;}
      .ev-shell-rating-chip:hover{transform:translateY(-1px);box-shadow:0 10px 18px rgba(15,23,42,.07);}
      .ev-shell-rating-chip.is-active{background:#ECFDF3;border-color:#BBF7D0;color:#166534;}
      .ev-shell-rating-box.is-low .ev-shell-rating-chip{border-color:#FECACA;color:#991B1B;}
      .ev-shell-rating-box.is-low .ev-shell-rating-chip.is-active{background:#FEF2F2;border-color:#FCA5A5;color:#991B1B;}
      .ev-shell-rating-box.is-mid .ev-shell-rating-chip{border-color:#FCD9BD;color:#9A3412;}
      .ev-shell-rating-box.is-mid .ev-shell-rating-chip.is-active{background:#FFF7ED;border-color:#FDBA74;color:#9A3412;}
      .ev-shell-rating-chip-hint{width:100%;text-align:center;border:1px dashed #D1D5DB;border-radius:16px;background:#F9FAFB;color:#6B7280;font-size:.84rem;font-weight:750;padding:11px 12px;}
      .ev-shell-rating-comment{width:100%;min-height:92px;border-radius:17px;border:1px solid #E5E7EB;background:#fff;padding:12px 13px;color:#111827;outline:none;resize:vertical;line-height:1.45;}
      .ev-shell-rating-comment:focus{border-color:#EA7C12;box-shadow:0 0 0 4px rgba(234,124,18,.10);}
      .ev-shell-rating-warning{border:1px solid #FECACA;background:linear-gradient(180deg,#FEF2F2 0%,#FFF7F7 100%);color:#991B1B;border-radius:16px;padding:11px 12px;font-size:.86rem;line-height:1.45;}
      .ev-shell-rating-check{display:flex;align-items:flex-start;gap:9px;margin-top:8px;color:#7F1D1D;font-weight:850;}
      .ev-shell-rating-check input{margin-top:3px;accent-color:#DC2626;}
      @media(max-width:575.98px){.ev-shell-rating-stars{gap:6px}.ev-shell-rating-star{width:39px;height:39px;border-radius:13px;font-size:1.35rem}.ev-shell-rating-chips{justify-content:flex-start}.ev-shell-rating-chip{font-size:.80rem}}
    `;
    document.head.appendChild(style);
  }

  function etiquetasPorPuntajeRolShell(rolCalificado, puntaje) {
    const rol = String(rolCalificado || '').trim();
    const score = Number(puntaje || 0);

    const vendedor = {
      1: ['Muy mala experiencia', 'No cumplió lo acordado', 'Producto diferente', 'Trato inadecuado', 'No recomiendo'],
      2: ['Demoró demasiado', 'Producto no conforme', 'Mala comunicación', 'No fue claro', 'No recomiendo'],
      3: ['Regular', 'Demoró un poco', 'Comunicación mejorable', 'Producto aceptable', 'Faltó coordinación'],
      4: ['Buena experiencia', 'Producto conforme', 'Comunicación clara', 'Atención correcta', 'Volvería a comprar'],
      5: ['Buena atención', 'Entrega rápida', 'Producto conforme', 'Comunicación clara', 'Lo recomiendo']
    };

    const comprador = {
      1: ['Muy mala experiencia', 'No respetó lo acordado', 'Trato inadecuado', 'No respondió', 'No recomiendo'],
      2: ['Impuntual', 'Mala comunicación', 'No coordinó bien', 'No fue claro', 'No recomiendo'],
      3: ['Regular', 'Demoró en responder', 'Coordinación mejorable', 'Trato aceptable', 'Faltó puntualidad'],
      4: ['Buena experiencia', 'Confirmación correcta', 'Comunicación clara', 'Trato respetuoso', 'Volvería a venderle'],
      5: ['Confirmación sin problemas', 'Puntual', 'Comunicación clara', 'Trato respetuoso', 'Lo recomiendo']
    };

    const fuente = rol === 'comprador' ? comprador : vendedor;
    return fuente[score] || [];
  }

  function textoPuntajeShell(puntaje) {
    const mapa = {
      1: 'Muy mala experiencia',
      2: 'Mala experiencia',
      3: 'Experiencia regular',
      4: 'Buena experiencia',
      5: 'Excelente experiencia'
    };
    return mapa[Number(puntaje || 0)] || 'Selecciona una calificación';
  }

  function ayudaPuntajeShell(puntaje) {
    const mapa = {
      1: 'Selecciona el motivo principal. Puedes reportarlo a soporte.',
      2: 'Cuéntanos qué falló para mejorar la experiencia.',
      3: 'Marca lo que mejor describa esta experiencia.',
      4: 'Selecciona lo que salió bien.',
      5: 'Marca los puntos fuertes de esta experiencia.'
    };
    return mapa[Number(puntaje || 0)] || 'Primero selecciona una cantidad de estrellas.';
  }

  function placeholderComentarioShell(rolCalificado, puntaje) {
    const score = Number(puntaje || 0);
    const rol = String(rolCalificado || '').trim();

    if (score <= 0) return 'Comentario opcional.';

    if (score <= 2) {
      return rol === 'comprador'
        ? 'Comentario opcional. Ejemplo: No respetó lo coordinado o no respondió a tiempo.'
        : 'Comentario opcional. Ejemplo: El producto no era conforme o faltó comunicación.';
    }

    if (score === 3) {
      return 'Comentario opcional. Ejemplo: Fue una experiencia regular, pero puede mejorar.';
    }

    return rol === 'comprador'
      ? 'Comentario opcional. Ejemplo: Coordinó bien y tuvo trato respetuoso.'
      : 'Comentario opcional. Ejemplo: Buena atención y producto conforme.';
  }

  function nivelPuntajeShell(puntaje) {
    const score = Number(puntaje || 0);
    if (score <= 0) return 'empty';
    if (score <= 2) return 'low';
    if (score === 3) return 'mid';
    return 'high';
  }

  function renderEtiquetasRatingShell(popup, rolCalificado, puntaje) {
    const chipsBox = popup.querySelector('#evShellRatingChips');
    const helper = popup.querySelector('#evShellRatingHelper');
    const comment = popup.querySelector('#evShellRatingComment');
    const box = popup.querySelector('.ev-shell-rating-box');

    if (helper) helper.textContent = ayudaPuntajeShell(puntaje);
    if (comment) comment.placeholder = placeholderComentarioShell(rolCalificado, puntaje);

    if (box) {
      box.classList.remove('is-empty', 'is-low', 'is-mid', 'is-high');
      box.classList.add(`is-${nivelPuntajeShell(puntaje)}`);
    }

    if (!chipsBox) return;

    const etiquetas = etiquetasPorPuntajeRolShell(rolCalificado, puntaje);
    if (!etiquetas.length) {
      chipsBox.innerHTML = '<div class="ev-shell-rating-chip-hint">Selecciona estrellas para ver opciones rápidas.</div>';
      return;
    }

    chipsBox.innerHTML = etiquetas
      .map((e) => `<button type="button" class="ev-shell-rating-chip" data-etiqueta="${escapeHtml(e)}">${escapeHtml(e)}</button>`)
      .join('');

    chipsBox.querySelectorAll('.ev-shell-rating-chip').forEach((btn) => {
      btn.addEventListener('click', () => btn.classList.toggle('is-active'));
    });
  }

  async function obtenerCalificacionPendientePedidoShell(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (!id) return null;

    try {
      const { resp, json } = await fetchJsonConTimeout(`${BASE}/api/calificaciones/pedido/${encodeURIComponent(id)}`, {
        method: 'GET',
        credentials: 'include',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!resp.ok || json?.ok === false) return null;

      const pendiente = json?.data || null;
      if (!pendiente || Number(pendiente.codigo_calificacion || 0) <= 0) return null;
      if (String(pendiente.estado || '').trim() !== 'pendiente') return null;

      return pendiente;
    } catch (e) {
      console.warn('[EV][Shell][Calificacion] No se pudo obtener la calificación pendiente:', e);
      return null;
    }
  }

  async function abrirModalCalificacionShell(calificacionManual, fallback = {}) {
    if (!window.Swal?.fire) return;

    const calificacion = calificacionManual || null;
    const codigoCalificacion = Number(calificacion?.codigo_calificacion || 0);
    if (!codigoCalificacion) return;

    ensureCalificacionShellStyles();

    const rolCalificado = String(calificacion.rol_calificado || 'vendedor').trim();
    const nombreCalificado = String(calificacion.nombre_calificado || fallback.nombre_calificado || 'Vecino').trim();
    const tituloPedido = String(calificacion.titulo_publicacion || fallback.titulo_publicacion || fallback.titulo_producto || 'Pedido EV').trim();
    const titulo = rolCalificado === 'vendedor' ? 'Califica al vendedor' : 'Califica al comprador';
    const subtitulo = rolCalificado === 'vendedor'
      ? 'Tu opinión ayuda a que otros vecinos compren con más confianza.'
      : 'Tu opinión ayuda a mantener una comunidad seria y respetuosa.';

    const html = `
      <div class="ev-shell-rating-box">
        <div class="ev-shell-rating-sub">${escapeHtml(subtitulo)}</div>
        <div class="ev-shell-rating-target">
          <span>Pedido</span>
          <strong>${escapeHtml(tituloPedido)}</strong>
          <span style="margin-top:8px">Vecino a calificar</span>
          <strong>${escapeHtml(nombreCalificado)}</strong>
        </div>
        <div class="ev-shell-rating-stars" role="group" aria-label="Calificación de 1 a 5 estrellas">
          ${[1,2,3,4,5].map(n => `<button type="button" class="ev-shell-rating-star" data-rating="${n}" aria-label="${n} estrellas">★</button>`).join('')}
        </div>
        <div class="ev-shell-rating-label" id="evShellRatingLabel">Selecciona una calificación</div>
        <div class="ev-shell-rating-helper" id="evShellRatingHelper">Primero selecciona una cantidad de estrellas.</div>
        <div class="ev-shell-rating-chips" id="evShellRatingChips">
          <div class="ev-shell-rating-chip-hint">Selecciona estrellas para ver opciones rápidas.</div>
        </div>
        <textarea id="evShellRatingComment" class="ev-shell-rating-comment" maxlength="800" placeholder="Comentario opcional."></textarea>
        <div id="evShellRatingLow" class="ev-shell-rating-warning d-none">
          Calificaste con una experiencia baja. Puedes marcar esta experiencia para que soporte la revise.
          <label class="ev-shell-rating-check"><input type="checkbox" id="evShellRatingReport"> Reportar esta experiencia a soporte</label>
        </div>
      </div>
    `;

    const result = await Swal.fire({
      title: titulo,
      html,
      width: 620,
      showCancelButton: true,
      confirmButtonText: 'Enviar calificación',
      cancelButtonText: 'Ahora no',
      confirmButtonColor: '#EA7C12',
      cancelButtonColor: '#6B7280',
      allowOutsideClick: false,
      allowEscapeKey: true,
      didOpen: () => {
        window.__evShellRatingValue = 0;
        const popup = Swal.getPopup();
        const label = popup.querySelector('#evShellRatingLabel');
        const lowBox = popup.querySelector('#evShellRatingLow');

        renderEtiquetasRatingShell(popup, rolCalificado, 0);

        popup.querySelectorAll('.ev-shell-rating-star').forEach((btn) => {
          btn.addEventListener('click', () => {
            const value = Number(btn.dataset.rating || 0);
            window.__evShellRatingValue = value;

            popup.querySelectorAll('.ev-shell-rating-star').forEach((star) => {
              star.classList.toggle('is-active', Number(star.dataset.rating || 0) <= value);
            });

            if (label) label.textContent = textoPuntajeShell(value);
            if (lowBox) lowBox.classList.toggle('d-none', value > 2 || value <= 0);
            renderEtiquetasRatingShell(popup, rolCalificado, value);
          });
        });
      },
      preConfirm: () => {
        const popup = Swal.getPopup();
        const puntaje = Number(window.__evShellRatingValue || 0);
        if (puntaje < 1 || puntaje > 5) {
          Swal.showValidationMessage('Selecciona una calificación del 1 al 5.');
          return false;
        }

        const etiquetasSeleccionadas = Array.from(popup.querySelectorAll('.ev-shell-rating-chip.is-active'))
          .map((btn) => btn.dataset.etiqueta || '')
          .filter(Boolean);
        const comentario = String(popup.querySelector('#evShellRatingComment')?.value || '').trim();
        const reportar = popup.querySelector('#evShellRatingReport')?.checked === true;

        return { puntaje, etiquetas: etiquetasSeleccionadas, comentario, reportar_soporte: reportar ? 1 : 0 };
      }
    });

    if (!result.isConfirmed || !result.value) return;

    try {
      const { resp, json } = await fetchJsonConTimeout(`${BASE}/api/calificaciones/${codigoCalificacion}/enviar`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(result.value)
      });

      if (!resp.ok || json?.ok === false) {
        await Swal.fire({
          icon: 'error',
          title: 'No se pudo calificar',
          text: json?.mensaje || 'No se pudo registrar la calificación.',
          confirmButtonColor: '#EA7C12'
        });
        return;
      }

      await Swal.fire({
        icon: 'success',
        title: 'Calificación enviada',
        text: json?.mensaje || 'Gracias por ayudar a construir confianza en Entre Vecinos.',
        confirmButtonColor: '#EA7C12'
      });
    } catch (e) {
      console.warn('[EV][Shell][Calificacion] No se pudo enviar la calificación:', e);
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo calificar',
        text: 'No se pudo registrar la calificación en este momento.',
        confirmButtonColor: '#EA7C12'
      });
    }
  }

  async function refrescarMisPedidosCompradorSiDisponible() {
    try {
      if (window.EVMisPedidosComprador && typeof window.EVMisPedidosComprador.refresh === 'function') {
        await window.EVMisPedidosComprador.refresh();
      }
    } catch (_) {}
  }

  async function confirmarEntregaDesdeAlertaComprador(codigoPedido, fallback = {}) {
    const id = Number(codigoPedido || 0);
    if (!id || !window.Swal?.fire) return;

    try {
      const { resp, json } = await fetchJsonConTimeout(`${BASE}/api/pedidos/${encodeURIComponent(id)}/confirmar-entrega`, {
        method: 'POST',
        credentials: 'include',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
      });

      if (!resp.ok || json?.ok === false) {
        await Swal.fire({
          icon: 'error',
          title: 'No se pudo confirmar',
          text: json?.mensaje || 'No se pudo confirmar la entrega.',
          confirmButtonColor: '#EA7C12'
        });
        await refrescarMisPedidosCompradorSiDisponible();
        return;
      }

      await Swal.fire({
        icon: 'success',
        title: 'Entrega confirmada',
        text: json?.mensaje || 'La entrega fue confirmada correctamente.',
        confirmButtonColor: '#EA7C12'
      });

      let pendiente = json?.data?.calificacion_pendiente || null;
      if (!pendiente || Number(pendiente.codigo_calificacion || 0) <= 0) {
        pendiente = await obtenerCalificacionPendientePedidoShell(id);
      }

      if (pendiente && Number(pendiente.codigo_calificacion || 0) > 0) {
        await abrirModalCalificacionShell(pendiente, {
          codigo_pedido: id,
          titulo_publicacion: pendiente.titulo_publicacion || fallback.titulo_publicacion || fallback.titulo_producto || 'Pedido EV',
          nombre_calificado: pendiente.nombre_calificado || fallback.nombre_calificado || 'Vecino'
        });
      }

      await refrescarMisPedidosCompradorSiDisponible();
    } catch (e) {
      console.warn('[EV][Shell][ConfirmarEntrega] No se pudo confirmar la entrega:', e);
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo confirmar',
        text: 'No se pudo confirmar la entrega en este momento.',
        confirmButtonColor: '#EA7C12'
      });
    }
  }

  function ensureOrderAlertStyles() {
    const ID = 'ev-shell-order-alert-style';
    if (document.getElementById(ID)) return;
    const style = document.createElement('style');
    style.id = ID;
    style.textContent = `
      .ev-shell-order-alert-popup{width:min(92vw,560px)!important;}
      .ev-shell-order-alert{text-align:left;max-width:450px;margin:0 auto;display:grid;gap:12px;}
      .ev-shell-order-alert__product{border:1px solid #E5E7EB;background:#fff;border-radius:18px;padding:14px 16px;box-shadow:0 10px 24px rgba(15,23,42,.06);}
      .ev-shell-order-alert__product span{display:block;color:#64748B;font-size:.72rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;margin-bottom:5px;}
      .ev-shell-order-alert__product strong{display:block;color:#111827;font-weight:900;line-height:1.35;overflow-wrap:anywhere;}
      .ev-shell-order-alert__status{display:flex;align-items:center;gap:12px;border:1px solid rgba(234,124,18,.24);background:#FFF8F0;border-radius:18px;padding:13px 14px;color:#9A3412;}
      .ev-shell-order-alert__status>i{width:42px;height:42px;display:grid;place-items:center;flex:0 0 42px;border-radius:14px;background:#fff;border:1px solid #FED7AA;color:#EA7C12;font-size:1.12rem;}
      .ev-shell-order-alert__status strong{display:block;color:#0F592F;font-size:.96rem;font-weight:900;margin-bottom:2px;}
      .ev-shell-order-alert__status span{display:block;color:#6B7280;font-size:.84rem;line-height:1.4;}
      .ev-shell-order-alert__message{margin:0;color:#475569;font-size:.94rem;line-height:1.58;}
      .ev-shell-order-alert__date{display:inline-flex;align-items:center;gap:7px;color:#6B7280;font-size:.82rem;font-weight:800;}
      @media(max-width:575.98px){
        .ev-shell-order-alert{gap:10px;}
        .ev-shell-order-alert__product,.ev-shell-order-alert__status{border-radius:15px;padding:12px;}
        .ev-shell-order-alert__status{align-items:flex-start;}
      }
    `;
    document.head.appendChild(style);
  }

  async function mostrarAlertaAvanceCompradorGlobal(alerta) {
    if (!window.Swal?.fire || !alerta) return;
    ensureOrderAlertStyles();

    const id = Number(alerta.codigo_notificacion || 0);
    if (!id && alerta._sintetica !== true) return;

    const estado = estadoAlertaComprador(alerta);
    const claveEvento = claveEventoAlertaComprador(alerta);

    if ((id > 0 && alertasCompradorMostradas.has(id)) || alertasCompradorEventosMostrados.has(claveEvento)) {
      if (id > 0) await marcarAlertaPedidoLeida(id);
      return;
    }

    if (window.__EV_AUTH_REDIRECTING__ === true) return;

    if (!esAlertaModalComprador(alerta)) return;

    if (id > 0) alertasCompradorMostradas.add(id);
    alertasCompradorEventosMostrados.add(claveEvento);
    guardarCacheAlertasComprador();
    limpiarCacheAlertasCompradorAntigua();

    const marcarLeidaPromise = id > 0 ? marcarAlertaPedidoLeida(id) : Promise.resolve();
    reproducirSonidoNuevaSolicitudEV();

    const payload = alerta.payload && typeof alerta.payload === 'object' ? alerta.payload : {};
    const producto = String(payload.titulo_producto || 'Pedido EV').trim();
    const titulo = estado === 'en_punto_entrega'
      ? 'Tu pedido llegó al punto de entrega'
      : String(alerta.titulo || 'Estado actualizado').trim();
    const mensaje = estado === 'en_punto_entrega'
      ? `Tu pedido de ${producto} ya está esperando recojo. Tienes hasta 6 minutos para recibirlo antes de que el vendedor pueda cancelar por no recepción.`
      : String(alerta.mensaje || 'Tu pedido cambió de estado.').trim();
    const fecha = String(alerta.fecha || '').trim();
    const icon = obtenerIconoSwalAlertaComprador(estado);
    const confirmText = obtenerTextoBotonAlertaComprador(estado);

    const esPuntoEntrega = estado === 'en_punto_entrega';
    const r = await Swal.fire({
      icon,
      title: titulo,
      html: `
        <div class="ev-shell-order-alert${esPuntoEntrega ? ' is-pickup' : ''}">
          <div class="ev-shell-order-alert__product">
            <span>Pedido</span>
            <strong>${escapeHtml(producto)}</strong>
          </div>
          ${esPuntoEntrega ? `
            <div class="ev-shell-order-alert__status">
              <i class="bi bi-geo-alt" aria-hidden="true"></i>
              <div>
                <strong>Listo para recoger</strong>
                <span>Tienes hasta 6 minutos para confirmar la recepción.</span>
              </div>
            </div>
          ` : ''}
          <p class="ev-shell-order-alert__message">${escapeHtml(mensaje)}</p>
          ${fecha ? `<div class="ev-shell-order-alert__date"><i class="bi bi-clock" aria-hidden="true"></i>${escapeHtml(fecha)}</div>` : ''}
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: `<i class="bi bi-eye" aria-hidden="true"></i><span>${escapeHtml(confirmText)}</span>`,
      cancelButtonText: '<i class="bi bi-check2-circle" aria-hidden="true"></i><span>Entendido</span>',
      buttonsStyling: false,
      customClass: {
        container: 'ev-swal-container',
        popup: 'ev-swal-popup ev-shell-order-alert-popup',
        title: 'ev-swal-title',
        htmlContainer: 'ev-swal-html',
        confirmButton: 'ev-swal-confirm',
        cancelButton: 'ev-swal-cancel'
      },
      allowOutsideClick: false,
      allowEscapeKey: false
    });

    await marcarLeidaPromise;

    if (r.isConfirmed) {
      const codigoPedido = Number(payload.codigo_pedido || alerta.referencia_id || 0);

      if (estado === 'entregado_vendedor') {
        await confirmarEntregaDesdeAlertaComprador(codigoPedido, {
          titulo_publicacion: producto,
          titulo_producto: producto
        });
        return;
      }

      await irAMisPedidosComprador();
    }
  }

  async function revisarAlertasPedidoComprador(opts = {}) {
    const force = opts.force === true;

    if (alertasCompradorEnCurso) return;
    if (document.hidden && !force) return;
    if (window.__EV_AUTH_REDIRECTING__ === true) return;
    if (!force && estaPausadoPorUi()) return;

    alertasCompradorEnCurso = true;
    ultimoPollAlertasCompradorAt = nowMs();

    try {
      const alertas = await fetchAlertasPedidoComprador();

      const candidatas = [];
      const eventosVistosEnRespuesta = new Set();

      for (const alerta of alertas) {
        if (!esAlertaModalComprador(alerta)) continue;

        const id = Number(alerta.codigo_notificacion || 0);
        const claveEvento = claveEventoAlertaComprador(alerta);

        if (id <= 0) continue;

        if (
          alertasCompradorMostradas.has(id) ||
          alertasCompradorEventosMostrados.has(claveEvento) ||
          eventosVistosEnRespuesta.has(claveEvento)
        ) {
          await marcarAlertaPedidoLeida(id);
          continue;
        }

        eventosVistosEnRespuesta.add(claveEvento);
        candidatas.push(alerta);
      }

      if (candidatas.length > 0) {
        await mostrarAlertaAvanceCompradorGlobal(candidatas[0]);
        return;
      }

      // Fallback EV: si por cualquier motivo la notificación app no fue creada o quedó leída,
      // igual avisamos al comprador cuando exista un pedido activo en punto de entrega.
      // Esto no modifica pedidos ni billetera; solo usa el listado vigente del comprador.
      const pedidosComprador = await fetchPedidosCompradorParaAlertas();
      const alertasSinteticas = obtenerAlertasPuntoEntregaDesdePedidos(pedidosComprador)
        .filter((alerta) => !alertasCompradorEventosMostrados.has(claveEventoAlertaComprador(alerta)));

      if (alertasSinteticas.length > 0) {
        await mostrarAlertaAvanceCompradorGlobal(alertasSinteticas[0]);
      }
    } catch (e) {
      const msg = String(e?.message || e);

      if (msg === 'UNAUTHORIZED') {
        console.warn('[EV][Shell][AlertasComprador] Sesión no válida.');
        return;
      }

      if (msg === 'CUENTA_BLOQUEADA' || msg === 'CUENTA_OBSERVADA') {
        console.warn('[EV][Shell][AlertasComprador] Estado de cuenta restringido:', msg);
        return;
      }

      console.warn('[EV][Shell][AlertasComprador] No se pudieron revisar alertas:', e);
    } finally {
      alertasCompradorEnCurso = false;
    }
  }

  function detenerPollingAlertasPedidoComprador() {
    if (alertasCompradorPollingTimer) {
      clearInterval(alertasCompradorPollingTimer);
      alertasCompradorPollingTimer = null;
    }
  }

  function iniciarPollingAlertasPedidoComprador() {
    detenerPollingAlertasPedidoComprador();

    alertasCompradorPollingTimer = window.setInterval(async () => {
      if (document.hidden) return;
      if (window.__EV_AUTH_REDIRECTING__ === true) return;

      const intervaloMinimo = estaPausadoPorUi()
        ? PEDIDOS_POLLING_IDLE_MS
        : PEDIDOS_POLLING_MS;

      if ((nowMs() - ultimoPollAlertasCompradorAt) < intervaloMinimo) return;

      await revisarAlertasPedidoComprador({ force: false });
    }, 1000);
  }

  function bindEventosAlertasPedidoComprador() {
    if (window.__EV_SHELL_ALERTAS_COMPRADOR_BOUND__ === true) return;
    window.__EV_SHELL_ALERTAS_COMPRADOR_BOUND__ = true;

    document.addEventListener('visibilitychange', async () => {
      if (document.hidden) return;
      await revisarAlertasPedidoComprador({ force: true });
    });

    document.addEventListener('ev:content-loaded', async () => {
      window.setTimeout(async () => {
        await revisarAlertasPedidoComprador({ force: true });
      }, 700);
    });
  }

  async function initNotificacionesPedidosCompradorGlobal() {
    obtenerCacheAlertasComprador();
    bindEventosAlertasPedidoComprador();

    await revisarAlertasPedidoComprador({
      force: true
    });

    iniciarPollingAlertasPedidoComprador();
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
      iniciarPedidosVendedor: iniciarPollingPedidosVendedor,

      revisarAlertasComprador: revisarAlertasPedidoComprador,
      detenerAlertasComprador: detenerPollingAlertasPedidoComprador,
      iniciarAlertasComprador: iniciarPollingAlertasPedidoComprador
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
      await initNotificacionesPedidosCompradorGlobal();
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
    revisarAlertasPedidoComprador: revisarAlertasPedidoComprador,
    marcarInteraccionUi: marcarInteraccionUi,
    pathActualShell: pathActualShell,
    reproducirSonidoNuevaSolicitud: reproducirSonidoNuevaSolicitudEV
  };
})();
