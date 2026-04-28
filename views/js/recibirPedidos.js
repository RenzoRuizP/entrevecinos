// views/js/recibirPedidos.js
(function () {
  'use strict';

  const BASE_URL = (window.BASE_URL || '/').replace(/\/+$/, '');

  /*
    EV - Mis pedidos vendedor / Recibir pedidos
    Ajustes principales:
    - Polling controlado para no afectar la fluidez del menú lateral.
    - No se pierde la inmediatez de solicitudes: el shell global sigue revisando solicitudes nuevas.
    - Este módulo refresca la vista del vendedor cuando está activa y conectado.
    - Evita llamadas solapadas y pausa si la pestaña está oculta.
    - Compatible con carga por shell/parciales.
  */

  const POLLING_PEDIDOS_MS = 10000;
  const POLLING_PEDIDOS_IDLE_MS = 18000;
  const FETCH_TIMEOUT_MS = 8000;
  const UI_PAUSE_MS = 1400;

  let state = {
    inicializado: false,
    disponibilidadActual: 0,
    pollingId: null,
    loadingPedidos: false,
    accionEnCurso: false,
    ultimaInteraccionUi: 0,
    ultimoPollingAt: 0,
    cachePedidos: {
      pendientes: [],
      en_proceso: [],
      finalizados: []
    },
    refs: {}
  };

  function $(id) {
    return document.getElementById(id);
  }

  function nowMs() {
    return Date.now();
  }

  function marcarInteraccionUi() {
    state.ultimaInteraccionUi = nowMs();

    if (window.EVPollingControl && typeof window.EVPollingControl.pauseBriefly === 'function') {
      window.EVPollingControl.pauseBriefly();
    }

    if (window.EVMarketplace && typeof window.EVMarketplace.pauseBriefly === 'function') {
      window.EVMarketplace.pauseBriefly();
    }
  }

  function estaPausadoPorUi() {
    return (nowMs() - state.ultimaInteraccionUi) < UI_PAUSE_MS;
  }

  function vistaActiva() {
    return !!$('toggleRecibirPedidos') || !!$('evPendientesLista') || !!$('evPedidosCounter');
  }

  async function fetchJsonRobusto(url, opts = {}, timeoutMs = FETCH_TIMEOUT_MS) {
    const ctrl = new AbortController();
    const timeoutId = window.setTimeout(() => ctrl.abort(), timeoutMs);

    try {
      const response = await fetch(url, {
        ...opts,
        signal: ctrl.signal
      });

      const text = await response.text();
      let data = {};

      try {
        data = text ? JSON.parse(text) : {};
      } catch (_) {
        data = {};
      }

      return { response, data, text };
    } finally {
      window.clearTimeout(timeoutId);
    }
  }

  function capturarRefs() {
    state.refs = {
      toggle: $('toggleRecibirPedidos'),
      sliderLabel: $('evSliderLabel'),
      estadoBadge: $('estadoBadge'),
      estadoBadgeText: $('estadoBadgeText'),
      estadoDot: $('estadoDot'),
      estadoTextoSecundario: $('estadoTextoSecundario'),

      pedidosCounter: $('evPedidosCounter'),
      pedidosDesconectado: $('evPedidosDesconectado'),
      pedidosError: $('evPedidosError'),
      pedidosBloque: $('evPedidosBloque'),

      pendientesLista: $('evPendientesLista'),
      procesoLista: $('evProcesoLista'),
      finalizadosLista: $('evFinalizadosLista'),

      pendientesCounter: $('evPendientesCounter'),
      procesoCounter: $('evProcesoCounter'),
      finalizadosCounter: $('evFinalizadosCounter'),

      pendientesEmpty: $('evPendientesEmpty'),
      procesoEmpty: $('evProcesoEmpty'),
      finalizadosEmpty: $('evFinalizadosEmpty'),

      btnRefrescarPedidos: $('btnRefrescarPedidos')
    };

    return !!state.refs.toggle;
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatoMoneda(valor) {
    const num = Number(valor || 0);
    return num.toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function formatearTiempo(segundos) {
    const s = Number(segundos || 0);
    if (s <= 0) return 'Tiempo agotado';

    const min = Math.floor(s / 60);
    const sec = s % 60;

    if (min <= 0) return `${sec}s`;
    return `${min}m ${sec}s`;
  }

  function formatearFechaProgramada(valor) {
    if (!valor) return '';
    const fecha = new Date(String(valor).replace(' ', 'T'));
    if (Number.isNaN(fecha.getTime())) return String(valor);

    return fecha.toLocaleString('es-PE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function formatearFechaRegistro(valor) {
    if (!valor) return '';
    const fecha = new Date(String(valor).replace(' ', 'T'));
    if (Number.isNaN(fecha.getTime())) return String(valor);

    return fecha.toLocaleString('es-PE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function ensureSwalStyles() {
    const ID = 'ev-rp-swal-premium-style';
    if (document.getElementById(ID)) return;

    const css = `
      .ev-rp-swal-container{
        backdrop-filter: blur(2px);
      }

      .ev-rp-swal-popup-premium{
        border-radius: 28px !important;
        padding: 28px 24px 22px !important;
        box-shadow:
          0 28px 70px rgba(15,23,42,.20),
          0 10px 24px rgba(15,89,47,.08) !important;
        border: 1px solid rgba(229,231,235,.96) !important;
        background:
          radial-gradient(circle at top, rgba(230,244,236,.65) 0%, rgba(255,255,255,1) 26%, rgba(255,255,255,1) 100%) !important;
      }

      .ev-rp-swal-title{
        color: #0F592F !important;
        font-weight: 900 !important;
        letter-spacing: -.03em !important;
        font-size: 2rem !important;
        line-height: 1.05 !important;
        margin: 0 0 8px 0 !important;
      }

      .ev-rp-swal-html{
        color: #6B7280 !important;
        font-size: 1rem !important;
        line-height: 1.55 !important;
        margin-top: 0 !important;
      }

      .ev-rp-swal-confirm{
        background: linear-gradient(135deg, #EA7C12, #F59E0B) !important;
        border: none !important;
        color: #fff !important;
        border-radius: 16px !important;
        padding: 13px 24px !important;
        min-width: 156px !important;
        font-weight: 900 !important;
        font-size: 1rem !important;
        box-shadow: 0 14px 30px rgba(234,124,18,.32) !important;
      }

      .ev-rp-swal-cancel{
        background: #fff !important;
        border: 1.6px solid #E5E7EB !important;
        color: #374151 !important;
        border-radius: 16px !important;
        padding: 13px 24px !important;
        min-width: 156px !important;
        font-weight: 900 !important;
        font-size: 1rem !important;
        box-shadow: 0 8px 18px rgba(15,23,42,.06) !important;
      }

      .ev-rp-swal-loader{
        width: 62px;
        height: 62px;
        border-radius: 50%;
        border: 5px solid rgba(22,163,74,.16);
        border-top-color: rgba(15,89,47,.96);
        margin: 4px auto 16px auto;
        animation: evRpSpin .85s linear infinite;
      }

      @keyframes evRpSpin{
        to{ transform: rotate(360deg); }
      }

      .ev-rp-swal-status-icon{
        width: 94px;
        height: 94px;
        margin: 0 auto 14px auto;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(180deg, rgba(230,244,236,.88), rgba(255,255,255,.98));
        border: 2px solid rgba(22,163,74,.20);
        box-shadow:
          inset 0 1px 0 rgba(255,255,255,.9),
          0 10px 28px rgba(15,89,47,.08);
      }

      .ev-rp-swal-status-icon--info{
        border-color: rgba(59,130,246,.18);
        background: linear-gradient(180deg, rgba(239,246,255,.92), rgba(255,255,255,.98));
      }

      .ev-rp-swal-status-icon--warning{
        border-color: rgba(234,124,18,.22);
        background: linear-gradient(180deg, rgba(255,247,237,.92), rgba(255,255,255,.98));
      }

      .ev-rp-swal-status-icon svg{
        width: 52px;
        height: 52px;
      }

      .ev-rp-swal-subtitle{
        font-weight: 900;
        font-size: 1.1rem;
        color: #0F592F;
        margin-bottom: 8px;
        letter-spacing: -.02em;
        text-align: center;
      }

      .ev-rp-swal-soft-text{
        font-size: 14px;
        color: #6B7280;
        line-height: 1.6;
        text-align: center;
      }

      .ev-rp-swal-note{
        margin-top: 16px;
        padding: 14px 16px;
        border-radius: 18px;
        background: linear-gradient(180deg, #FFF7ED, #FFFDF9);
        border: 1px solid rgba(234,124,18,.22);
        color: #9A3412;
        font-size: 13.5px;
        line-height: 1.55;
        box-shadow: 0 8px 18px rgba(234,124,18,.08);
        text-align: left;
      }

      .ev-rp-swal-note strong{
        font-weight: 900;
      }

      .ev-rp-swal-product-card{
        margin-top: 16px;
        padding: 13px 16px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid rgba(229,231,235,.95);
        box-shadow: 0 8px 22px rgba(15,23,42,.05);
        text-align: left;
      }

      .ev-rp-swal-product-label{
        display: block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9CA3AF;
        margin-bottom: 5px;
      }

      .ev-rp-swal-product{
        font-size: 15px;
        color: #1A1F36;
        font-weight: 800;
        word-break: break-word;
      }

      .ev-rp-swal-danger-note{
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 16px;
        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: #991B1B;
        font-size: 13px;
        line-height: 1.5;
        text-align: left;
      }

      .ev-rp-card-highlight{
        position: relative;
        animation: evRpPulseCard 2.3s ease-in-out 0s 3;
        box-shadow:
          0 0 0 3px rgba(234,124,18,.20),
          0 22px 48px rgba(234,124,18,.18) !important;
        border-color: rgba(234,124,18,.38) !important;
      }

      .ev-rp-card-highlight::after{
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: inherit;
        pointer-events: none;
        border: 2px solid rgba(234,124,18,.38);
        box-shadow: 0 0 0 6px rgba(234,124,18,.08);
      }

      @keyframes evRpPulseCard{
        0%, 100% { transform: translateY(0); }
        20% { transform: translateY(-2px); }
        40% { transform: translateY(0); }
        60% { transform: translateY(-1px); }
        80% { transform: translateY(0); }
      }

      @media (max-width: 575.98px){
        .ev-rp-swal-popup-premium{
          padding: 22px 16px 18px !important;
          border-radius: 22px !important;
        }

        .ev-rp-swal-title{
          font-size: 1.7rem !important;
        }

        .ev-rp-swal-confirm,
        .ev-rp-swal-cancel{
          width: 100% !important;
          min-width: 0 !important;
        }
      }
    `;

    const style = document.createElement('style');
    style.id = ID;
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);
  }

  function swalBaseConfig(opts = {}) {
    ensureSwalStyles();

    return Object.assign({
      buttonsStyling: false,
      allowOutsideClick: false,
      allowEscapeKey: true,
      customClass: {
        container: 'ev-rp-swal-container',
        popup: 'ev-rp-swal-popup-premium',
        title: 'ev-rp-swal-title',
        htmlContainer: 'ev-rp-swal-html',
        confirmButton: 'ev-rp-swal-confirm',
        cancelButton: 'ev-rp-swal-cancel'
      }
    }, opts || {});
  }

  function iconSvg(tipo) {
    if (tipo === 'info') {
      return `
        <div class="ev-rp-swal-status-icon ev-rp-swal-status-icon--info" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="32" r="30" fill="none"></circle>
            <path d="M32 18.5C34.5 18.5 36.3 20.2 36.3 22.6C36.3 25 34.5 26.8 32 26.8C29.5 26.8 27.7 25 27.7 22.6C27.7 20.2 29.5 18.5 32 18.5Z" fill="#38BDF8"/>
            <path d="M32 31.5V45.5" stroke="#38BDF8" stroke-width="5" stroke-linecap="round"/>
          </svg>
        </div>
      `;
    }

    if (tipo === 'warning') {
      return `
        <div class="ev-rp-swal-status-icon ev-rp-swal-status-icon--warning" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none">
            <path d="M32 12L53 49H11L32 12Z" stroke="#EA7C12" stroke-width="4" fill="rgba(234,124,18,.08)"></path>
            <path d="M32 24V36" stroke="#EA7C12" stroke-width="5" stroke-linecap="round"></path>
            <circle cx="32" cy="43.5" r="2.8" fill="#EA7C12"></circle>
          </svg>
        </div>
      `;
    }

    if (tipo === 'error') {
      return `
        <div class="ev-rp-swal-status-icon" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="32" r="28" stroke="#DC2626" stroke-width="4" fill="rgba(220,38,38,.06)"></circle>
            <path d="M24 24L40 40M40 24L24 40" stroke="#DC2626" stroke-width="5" stroke-linecap="round"></path>
          </svg>
        </div>
      `;
    }

    return `
      <div class="ev-rp-swal-status-icon" aria-hidden="true">
        <svg viewBox="0 0 64 64" fill="none">
          <path d="M18 33.5L27.5 43L46 23.5" stroke="#84CC16" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
    `;
  }

  function htmlMessage(tipo, subtitulo, texto, extra = '') {
    return `
      <div>
        ${iconSvg(tipo)}
        <div class="ev-rp-swal-subtitle">${escapeHtml(subtitulo)}</div>
        <div class="ev-rp-swal-soft-text">${escapeHtml(texto)}</div>
        ${extra || ''}
      </div>
    `;
  }

  function htmlProductNote(label, value, note = '') {
    return `
      <div class="ev-rp-swal-product-card">
        <span class="ev-rp-swal-product-label">${escapeHtml(label)}</span>
        <div class="ev-rp-swal-product">${escapeHtml(value)}</div>
      </div>
      ${note ? `<div class="ev-rp-swal-note">${note}</div>` : ''}
    `;
  }

  async function notify(tipo, title, subtitle, text, extra = {}) {
    if (!window.Swal?.fire) {
      alert(`${title}\n\n${text}`);
      return { isConfirmed: true };
    }

    return Swal.fire(swalBaseConfig(Object.assign({
      title,
      html: htmlMessage(tipo, subtitle, text, extra.htmlExtra || ''),
      confirmButtonText: extra.confirmButtonText || 'Entendido',
      showCancelButton: !!extra.showCancelButton,
      cancelButtonText: extra.cancelButtonText || 'Cancelar'
    }, extra || {})));
  }

  async function confirmAction({ title, subtitle, text, productText, note, confirmText, cancelText, tipo = 'info' }) {
    if (!window.Swal?.fire) {
      return window.confirm(text);
    }

    const result = await Swal.fire(swalBaseConfig({
      title,
      html: htmlMessage(
        tipo,
        subtitle,
        text,
        htmlProductNote('Pedido', productText || 'Solicitud seleccionada', note || '')
      ),
      showCancelButton: true,
      confirmButtonText: confirmText || 'Sí, continuar',
      cancelButtonText: cancelText || 'Cancelar'
    }));

    return !!result.isConfirmed;
  }

  async function promptReject(item) {
    if (!window.Swal?.fire) return { isConfirmed: false, value: '' };

    return Swal.fire(swalBaseConfig({
      title: 'Rechazar solicitud',
      html: `
        ${htmlMessage(
          'warning',
          'Indica el motivo del rechazo',
          'Este mensaje se mostrará al comprador para que entienda por qué no continuó el pedido.',
          htmlProductNote(
            'Solicitud',
            item?.titulo_publicacion || 'Pedido seleccionado',
            'Es recomendable ser claro y cordial para mantener una buena experiencia entre vecinos.'
          )
        )}
      `,
      input: 'textarea',
      inputPlaceholder: 'Escribe el motivo del rechazo...',
      inputAttributes: {
        'aria-label': 'Motivo de rechazo',
        maxlength: '500'
      },
      showCancelButton: true,
      confirmButtonText: 'Rechazar solicitud',
      cancelButtonText: 'Cancelar',
      preConfirm: (value) => {
        const txt = String(value || '').trim();
        if (!txt) {
          Swal.showValidationMessage('Debes indicar el motivo de rechazo.');
          return false;
        }
        return txt;
      }
    }));
  }

  function badgeEstado(estado) {
    const mapa = {
      pendiente_vendedor: { texto: 'Pendiente', clase: 'ev-status-off' },
      cola_aceptada: { texto: 'En cola', clase: 'ev-status-off' },
      cola_pendiente_confirmacion: { texto: 'Esperando confirmación', clase: 'ev-status-off' },
      en_preparacion: { texto: 'En preparación', clase: 'ev-status-on' },
      despachando: { texto: 'Despachando', clase: 'ev-status-on' },
      listo_para_entrega: { texto: 'Listo para entrega', clase: 'ev-status-on' },
      en_camino: { texto: 'En camino', clase: 'ev-status-on' },
      en_punto_entrega: { texto: 'En punto de entrega', clase: 'ev-status-on' },
      entregado_vendedor: { texto: 'Entregado por vendedor', clase: 'ev-status-on' },
      rechazo_vendedor: { texto: 'Rechazado', clase: 'ev-status-off' },
      rechazado_vendedor: { texto: 'Rechazado', clase: 'ev-status-off' },
      cancelado_vendedor: { texto: 'Cancelado por vendedor', clase: 'ev-status-off' },
      cancelado_comprador: { texto: 'Cancelado por comprador', clase: 'ev-status-off' },
      sin_respuesta_vendedor: { texto: 'Sin respuesta', clase: 'ev-status-off' },
      entrega_confirmada_comprador: { texto: 'Entrega confirmada', clase: 'ev-status-on' }
    };

    return mapa[estado] || { texto: estado || 'Sin estado', clase: 'ev-status-off' };
  }

  async function manejarRespuestaAuth(response, data) {
    const error = String(data?.error || '').trim();

    if (response.status === 401) {
      await notify(
        'info',
        'Sesión finalizada',
        'Tu sesión ya no está activa',
        data?.mensaje || 'Vuelve a iniciar sesión para continuar.'
      );
      window.location.href = data?.redirect || `${BASE_URL}/login`;
      return true;
    }

    if (response.status === 403 && error === 'CUENTA_BLOQUEADA') {
      await notify(
        'warning',
        'Cuenta bloqueada',
        'Tu cuenta ya no está disponible',
        data?.mensaje || 'Por seguridad, debes volver a iniciar sesión.'
      );
      window.location.href = data?.redirect || `${BASE_URL}/login`;
      return true;
    }

    if (response.status === 409 && error === 'CUENTA_OBSERVADA') {
      await notify(
        'warning',
        'Cuenta observada',
        'Debes revisar el estado de tu cuenta',
        data?.mensaje || 'Tu cuenta se encuentra observada.'
      );
      window.location.href = data?.redirect || `${BASE_URL}/cuenta-observada`;
      return true;
    }

    return false;
  }

  function aplicarEstadoUI(estaConectado) {
    const {
      toggle,
      sliderLabel,
      estadoBadge,
      estadoBadgeText,
      estadoDot,
      estadoTextoSecundario,
      pedidosDesconectado,
      pedidosBloque
    } = state.refs;

    state.disponibilidadActual = estaConectado ? 1 : 0;

    if (toggle) toggle.checked = !!estaConectado;

    if (estaConectado) {
      if (sliderLabel) sliderLabel.textContent = 'Estás conectado';

      if (estadoBadge) {
        estadoBadge.classList.remove('ev-status-off');
        estadoBadge.classList.add('ev-status-on');
      }

      if (estadoDot) {
        estadoDot.classList.remove('ev-status-dot-off');
        estadoDot.classList.add('ev-status-dot-on');
      }

      if (estadoBadgeText) estadoBadgeText.textContent = 'Conectado';

      if (estadoTextoSecundario) {
        estadoTextoSecundario.innerHTML = 'Actualmente: <strong>Conectado</strong>';
      }

      if (pedidosDesconectado) pedidosDesconectado.classList.add('d-none');
      if (pedidosBloque) pedidosBloque.classList.remove('d-none');

      iniciarPolling();
    } else {
      if (sliderLabel) sliderLabel.textContent = 'Desliza para conectarte';

      if (estadoBadge) {
        estadoBadge.classList.remove('ev-status-on');
        estadoBadge.classList.add('ev-status-off');
      }

      if (estadoDot) {
        estadoDot.classList.remove('ev-status-dot-on');
        estadoDot.classList.add('ev-status-dot-off');
      }

      if (estadoBadgeText) estadoBadgeText.textContent = 'Desconectado';

      if (estadoTextoSecundario) {
        estadoTextoSecundario.innerHTML = 'Actualmente: <strong>Desconectado</strong>';
      }

      if (pedidosDesconectado) pedidosDesconectado.classList.remove('d-none');
      if (pedidosBloque) pedidosBloque.classList.add('d-none');

      detenerPolling();
    }
  }

  async function cargarEstadoInicial() {
    try {
      const { response, data } = await fetchJsonRobusto(`${BASE_URL}/api/usuario/disponibilidad-pedidos`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          Accept: 'application/json'
        },
        cache: 'no-store'
      });

      if (await manejarRespuestaAuth(response, data)) return;

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const disponibilidad = Number(data?.data?.disponibilidad ?? 0);

      aplicarEstadoUI(disponibilidad === 1);

      if (disponibilidad === 1) {
        await cargarMisPedidos({ force: true });
      } else {
        limpiarListas();
      }
    } catch (error) {
      console.error('[RecibirPedidos] No se pudo cargar disponibilidad:', error);
      aplicarEstadoUI(false);
      limpiarListas();
    }
  }

  async function actualizarEstadoBackend(nuevoEstado) {
    try {
      const { response, data } = await fetchJsonRobusto(`${BASE_URL}/api/usuario/disponibilidad-pedidos`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        cache: 'no-store',
        body: JSON.stringify({
          disponibilidad: nuevoEstado ? 1 : 0
        })
      });

      if (await manejarRespuestaAuth(response, data)) return;

      if (!response.ok || data?.ok === false) {
        throw new Error(data?.mensaje || `HTTP ${response.status}`);
      }

      await notify(
        'success',
        nuevoEstado ? 'Ahora estás disponible' : 'Te has desconectado',
        nuevoEstado ? 'Ya puedes recibir solicitudes' : 'Ya no recibirás nuevas solicitudes',
        data?.mensaje || (nuevoEstado
          ? 'Ahora puedes recibir solicitudes.'
          : 'Ya no recibirás nuevas solicitudes.'),
        {
          htmlExtra: htmlProductNote(
            'Estado actual',
            nuevoEstado ? 'Conectado' : 'Desconectado'
          )
        }
      );

      if (nuevoEstado) {
        await cargarMisPedidos({ force: true });
        iniciarPolling();
      } else {
        limpiarListas();
        detenerPolling();
      }

      if (window.EVMarketplace && typeof window.EVMarketplace.refreshDisponibilidad === 'function') {
        window.EVMarketplace.refreshDisponibilidad({ force: true });
      }
    } catch (error) {
      console.error('[RecibirPedidos] Error al actualizar disponibilidad:', error);
      aplicarEstadoUI(!nuevoEstado);

      await notify(
        'error',
        'No se pudo actualizar tu estado',
        'Ocurrió un problema al guardar el cambio',
        error.message || 'Inténtalo nuevamente en unos segundos.'
      );
    }
  }

  function limpiarContenedor(el) {
    if (el) el.innerHTML = '';
  }

  function limpiarListas() {
    const {
      pendientesLista,
      procesoLista,
      finalizadosLista,
      pendientesCounter,
      procesoCounter,
      finalizadosCounter,
      pedidosCounter,
      pendientesEmpty,
      procesoEmpty,
      finalizadosEmpty,
      pedidosError
    } = state.refs;

    state.cachePedidos = {
      pendientes: [],
      en_proceso: [],
      finalizados: []
    };

    limpiarContenedor(pendientesLista);
    limpiarContenedor(procesoLista);
    limpiarContenedor(finalizadosLista);

    if (pendientesCounter) pendientesCounter.textContent = '0';
    if (procesoCounter) procesoCounter.textContent = '0';
    if (finalizadosCounter) finalizadosCounter.textContent = '0';
    if (pedidosCounter) pedidosCounter.textContent = '0 pedidos';

    if (pendientesEmpty) pendientesEmpty.classList.add('d-none');
    if (procesoEmpty) procesoEmpty.classList.add('d-none');
    if (finalizadosEmpty) finalizadosEmpty.classList.add('d-none');

    if (pedidosError) pedidosError.classList.add('d-none');
  }

  function obtenerTextoEntrega(item) {
    const raw = String(item?.tipo_entrega_raw || '').toLowerCase();
    if (raw === 'programada' && item?.fecha_hora_programada) return 'Programado';
    return item?.tipo_entrega || 'Inmediato';
  }

  function construirPillCola(item) {
    const pos = Number(item?.posicion_cola || 0);
    if (pos <= 1) return '';
    return `<span class="ev-status-pill ev-status-off">Cola #${escapeHtml(pos)}</span>`;
  }

  function construirBloqueDetalle(item) {
    const programado = item.tipo_entrega_raw === 'programada' && item.fecha_hora_programada
      ? `<div class="ev-pedido-detalle-line"><strong>Entrega programada:</strong> ${escapeHtml(formatearFechaProgramada(item.fecha_hora_programada))}</div>`
      : '';

    const mensaje = item.mensaje_comprador
      ? `<div class="ev-pedido-comentario"><strong>Mensaje:</strong> ${escapeHtml(item.mensaje_comprador)}</div>`
      : '';

    const motivo = item.motivo_estado
      ? `<div class="ev-pedido-comentario"><strong>Detalle de estado:</strong> ${escapeHtml(item.motivo_estado)}</div>`
      : '';

    const cola = Number(item.posicion_cola || 0) > 1
      ? `<div class="ev-pedido-detalle-line"><strong>Posición en cola:</strong> ${escapeHtml(item.posicion_cola)}</div>`
      : '';

    const fechaRegistro = item.fecha_hora
      ? `<div class="ev-pedido-detalle-line"><strong>Registrado:</strong> ${escapeHtml(formatearFechaRegistro(item.fecha_hora))}</div>`
      : '';

    return `
      ${fechaRegistro}
      <div class="ev-pedido-detalle-line"><strong>Vecino:</strong> ${escapeHtml(item.nombre_vecino || item.nombre_comprador || 'Vecino')}</div>
      <div class="ev-pedido-detalle-line"><strong>Cantidad:</strong> ${escapeHtml(item.cantidad)}</div>
      <div class="ev-pedido-detalle-line"><strong>Precio unitario:</strong> S/ ${escapeHtml(formatoMoneda(item.precio_unitario))}</div>
      <div class="ev-pedido-detalle-line"><strong>Total:</strong> S/ ${escapeHtml(formatoMoneda(item.monto_total))}</div>
      <div class="ev-pedido-detalle-line"><strong>Entrega:</strong> ${escapeHtml(obtenerTextoEntrega(item))}</div>
      ${programado}
      ${cola}
      <div class="ev-pedido-detalle-line"><strong>Dirección:</strong> ${escapeHtml(item.direccion_entrega || '-')}</div>
      ${mensaje}
      ${motivo}
    `;
  }

  function construirAcciones(item) {
    const estado = String(item.estado_actual || '');
    const codigoPedido = Number(item.codigo_pedido || 0);
    const botones = [];

    if (estado === 'pendiente_vendedor') {
      botones.push(`
        <button type="button" class="btn ev-btn-aceptar" data-action="aceptar" data-id="${codigoPedido}">
          <i class="bi bi-check2-circle me-1"></i>Aceptar
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-rechazar" data-action="rechazar" data-id="${codigoPedido}">
          <i class="bi bi-x-circle me-1"></i>Rechazar
        </button>
      `);
    }

    if (estado === 'en_preparacion') {
      botones.push(`
        <button type="button" class="btn ev-btn-detalle" data-action="estado" data-id="${codigoPedido}" data-estado="listo_para_entrega">
          <i class="bi bi-box-seam me-1"></i>Listo
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-detalle" data-action="estado" data-id="${codigoPedido}" data-estado="en_camino">
          <i class="bi bi-truck me-1"></i>En camino
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-rechazar" data-action="estado" data-id="${codigoPedido}" data-estado="cancelado_vendedor">
          <i class="bi bi-slash-circle me-1"></i>Cancelar
        </button>
      `);
    }

    if (estado === 'despachando') {
      botones.push(`
        <button type="button" class="btn ev-btn-detalle" data-action="estado" data-id="${codigoPedido}" data-estado="en_camino">
          <i class="bi bi-truck me-1"></i>En camino
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-detalle" data-action="estado" data-id="${codigoPedido}" data-estado="en_punto_entrega">
          <i class="bi bi-geo-alt me-1"></i>Punto de entrega
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-aceptar" data-action="estado" data-id="${codigoPedido}" data-estado="entregado_vendedor">
          <i class="bi bi-check2-square me-1"></i>Entregado
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-rechazar" data-action="estado" data-id="${codigoPedido}" data-estado="cancelado_vendedor">
          <i class="bi bi-slash-circle me-1"></i>Cancelar
        </button>
      `);
    }

    if (estado === 'listo_para_entrega') {
      botones.push(`
        <button type="button" class="btn ev-btn-detalle" data-action="estado" data-id="${codigoPedido}" data-estado="en_camino">
          <i class="bi bi-truck me-1"></i>En camino
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-detalle" data-action="estado" data-id="${codigoPedido}" data-estado="en_punto_entrega">
          <i class="bi bi-geo-alt me-1"></i>Punto de entrega
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-aceptar" data-action="estado" data-id="${codigoPedido}" data-estado="entregado_vendedor">
          <i class="bi bi-check2-square me-1"></i>Entregado
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-rechazar" data-action="estado" data-id="${codigoPedido}" data-estado="cancelado_vendedor">
          <i class="bi bi-slash-circle me-1"></i>Cancelar
        </button>
      `);
    }

    if (estado === 'en_camino') {
      botones.push(`
        <button type="button" class="btn ev-btn-detalle" data-action="estado" data-id="${codigoPedido}" data-estado="en_punto_entrega">
          <i class="bi bi-geo-alt me-1"></i>Punto de entrega
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-aceptar" data-action="estado" data-id="${codigoPedido}" data-estado="entregado_vendedor">
          <i class="bi bi-check2-square me-1"></i>Entregado
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-rechazar" data-action="estado" data-id="${codigoPedido}" data-estado="cancelado_vendedor">
          <i class="bi bi-slash-circle me-1"></i>Cancelar
        </button>
      `);
    }

    if (estado === 'en_punto_entrega') {
      botones.push(`
        <button type="button" class="btn ev-btn-aceptar" data-action="estado" data-id="${codigoPedido}" data-estado="entregado_vendedor">
          <i class="bi bi-check2-square me-1"></i>Entregado
        </button>
      `);
      botones.push(`
        <button type="button" class="btn ev-btn-rechazar" data-action="estado" data-id="${codigoPedido}" data-estado="cancelado_vendedor">
          <i class="bi bi-slash-circle me-1"></i>Cancelar
        </button>
      `);
    }

    botones.push(`
      <button type="button" class="btn ev-btn-detalle" data-action="ver" data-id="${codigoPedido}">
        <i class="bi bi-eye me-1"></i>Detalle
      </button>
    `);

    return botones.join('');
  }

  function crearCardPedido(item) {
    const estado = badgeEstado(item.estado_actual);
    const tiempo = Number(item.tiempo_restante_segundos ?? 0);

    const tiempoHtml = (item.estado_actual === 'pendiente_vendedor' && tiempo > 0)
      ? `<span class="ev-pedido-tiempo"><i class="bi bi-clock-history"></i>${escapeHtml(formatearTiempo(tiempo))}</span>`
      : '';

    const colaHtml = construirPillCola(item);

    const img = item.imagen_portada_url
      ? `<img src="${escapeHtml(item.imagen_portada_url)}" alt="${escapeHtml(item.titulo_publicacion || 'Producto')}">`
      : `<div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="bi bi-image"></i></div>`;

    return `
      <div class="ev-pedido-card" data-pedido-id="${Number(item.codigo_pedido || 0)}">
        <div class="ev-pedido-main">
          <div class="ev-pedido-img-wrapper">${img}</div>
          <div class="ev-pedido-info">
            <div class="ev-pedido-header-row">
              <div>
                <div class="ev-pedido-producto">${escapeHtml(item.titulo_publicacion || item.titulo_producto || 'Publicación')}</div>
                <div class="ev-pedido-vecino">${escapeHtml(item.nombre_vecino || item.nombre_comprador || 'Vecino')}</div>
              </div>
              <div class="text-end">
                <div class="ev-pedido-precio">S/ ${escapeHtml(formatoMoneda(item.monto_total))}</div>
                ${tiempoHtml}
              </div>
            </div>

            <div class="mt-2 d-flex flex-wrap gap-2">
              <span class="ev-status-pill ${escapeHtml(estado.clase)}">${escapeHtml(estado.texto)}</span>
              ${colaHtml}
            </div>

            <div class="mt-2">
              ${construirBloqueDetalle(item)}
            </div>

            <div class="ev-pedido-actions">
              ${construirAcciones(item)}
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function pintarGrupo(listaEl, emptyEl, counterEl, items) {
    if (!listaEl || !emptyEl || !counterEl) return;

    const registros = Array.isArray(items) ? items : [];
    counterEl.textContent = String(registros.length);
    listaEl.innerHTML = '';

    if (!registros.length) {
      emptyEl.classList.remove('d-none');
      return;
    }

    emptyEl.classList.add('d-none');
    listaEl.innerHTML = registros.map(crearCardPedido).join('');
  }

  function totalPedidos(data) {
    const pendientes = Array.isArray(data?.pendientes) ? data.pendientes.length : 0;
    const enProceso = Array.isArray(data?.en_proceso) ? data.en_proceso.length : 0;
    const finalizados = Array.isArray(data?.finalizados) ? data.finalizados.length : 0;
    return pendientes + enProceso + finalizados;
  }

  function buscarPedidoEnCache(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (id <= 0) return null;

    const grupos = ['pendientes', 'en_proceso', 'finalizados'];
    for (const grupo of grupos) {
      const items = Array.isArray(state.cachePedidos[grupo]) ? state.cachePedidos[grupo] : [];
      const encontrado = items.find(item => Number(item.codigo_pedido || 0) === id);
      if (encontrado) return encontrado;
    }

    return null;
  }

  function resaltarPedido(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (id <= 0) return false;

    const selector = `.ev-pedido-card[data-pedido-id="${id}"]`;
    const card = document.querySelector(selector);
    if (!card) return false;

    document.querySelectorAll('.ev-pedido-card.ev-rp-card-highlight').forEach((el) => {
      el.classList.remove('ev-rp-card-highlight');
    });

    card.classList.add('ev-rp-card-highlight');

    try {
      card.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    } catch (_) {
      card.scrollIntoView();
    }

    window.setTimeout(() => {
      card.classList.remove('ev-rp-card-highlight');
    }, 7000);

    return true;
  }

  async function cargarMisPedidos(opciones = {}) {
    const force = opciones.force === true;

    if (!force && !vistaActiva()) {
      detenerPolling();
      return;
    }

    if (!force && document.hidden) return;
    if (!force && estaPausadoPorUi()) return;
    if (state.loadingPedidos) return;

    state.loadingPedidos = true;
    state.ultimoPollingAt = nowMs();

    const {
      pedidosError,
      pendientesLista,
      pendientesEmpty,
      pendientesCounter,
      procesoLista,
      procesoEmpty,
      procesoCounter,
      finalizadosLista,
      finalizadosEmpty,
      finalizadosCounter,
      pedidosCounter
    } = state.refs;

    if (pedidosError) pedidosError.classList.add('d-none');

    try {
      const { response, data } = await fetchJsonRobusto(`${BASE_URL}/api/pedidos/mis`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          Accept: 'application/json'
        },
        cache: 'no-store'
      });

      if (await manejarRespuestaAuth(response, data)) return;

      if (!response.ok || data?.ok === false) {
        throw new Error(data?.mensaje || `HTTP ${response.status}`);
      }

      const payload = data?.data || {};
      const pendientes = Array.isArray(payload.pendientes) ? payload.pendientes : [];
      const enProceso = Array.isArray(payload.en_proceso) ? payload.en_proceso : [];
      const finalizados = Array.isArray(payload.finalizados) ? payload.finalizados : [];

      state.cachePedidos = {
        pendientes,
        en_proceso: enProceso,
        finalizados
      };

      pintarGrupo(pendientesLista, pendientesEmpty, pendientesCounter, pendientes);
      pintarGrupo(procesoLista, procesoEmpty, procesoCounter, enProceso);
      pintarGrupo(finalizadosLista, finalizadosEmpty, finalizadosCounter, finalizados);

      if (pedidosCounter) {
        const total = totalPedidos(payload);
        pedidosCounter.textContent = `${total} pedido${total === 1 ? '' : 's'}`;
      }
    } catch (error) {
      console.error('[RecibirPedidos] Error al cargar pedidos:', error);

      if (force) {
        limpiarListas();
        if (pedidosError) pedidosError.classList.remove('d-none');
      }
    } finally {
      state.loadingPedidos = false;
    }
  }

  async function aceptarSolicitud(codigoPedido) {
    if (state.accionEnCurso) return;

    const item = buscarPedidoEnCache(codigoPedido);
    if (!item) return;

    const ok = await confirmAction({
      title: 'Aceptar solicitud',
      subtitle: 'Confirmar recepción del pedido',
      text: 'Al aceptarlo, este pedido pasará al flujo de atención y seguirá ocupando tu turno actual.',
      productText: item.titulo_publicacion || item.titulo_producto || `Pedido #${codigoPedido}`,
      note: 'Mientras este pedido siga activo, las siguientes solicitudes permanecerán en cola hasta que el turno se libere.',
      confirmText: 'Sí, aceptar',
      cancelText: 'Cancelar'
    });

    if (!ok) return;

    state.accionEnCurso = true;

    try {
      await ejecutarAccion(
        `${BASE_URL}/api/pedidos/${codigoPedido}/aceptar`,
        {},
        {
          title: 'Solicitud aceptada',
          subtitle: 'El pedido ya está en atención',
          text: 'La solicitud fue aceptada correctamente.',
          productText: item.titulo_publicacion || item.titulo_producto || `Pedido #${codigoPedido}`
        }
      );
    } finally {
      state.accionEnCurso = false;
    }
  }

  async function rechazarSolicitud(codigoPedido) {
    if (state.accionEnCurso) return;

    const item = buscarPedidoEnCache(codigoPedido);
    if (!item) return;

    const result = await promptReject(item);
    if (!result.isConfirmed || !result.value) return;

    state.accionEnCurso = true;

    try {
      await ejecutarAccion(
        `${BASE_URL}/api/pedidos/${codigoPedido}/rechazar`,
        { motivo_rechazo: result.value },
        {
          title: 'Solicitud rechazada',
          subtitle: 'El pedido fue cerrado correctamente',
          text: 'La solicitud fue rechazada correctamente.',
          productText: item.titulo_publicacion || item.titulo_producto || `Pedido #${codigoPedido}`
        }
      );
    } finally {
      state.accionEnCurso = false;
    }
  }

  function obtenerMetaCambioEstado(nuevoEstado) {
    const etiquetas = {
      listo_para_entrega: {
        title: 'Marcar listo para entrega',
        subtitle: 'Confirmar nuevo avance',
        text: 'Usa esta opción solo cuando el pedido realmente ya esté listo para ser entregado.',
        confirmText: 'Sí, marcar listo'
      },
      en_camino: {
        title: 'Marcar en camino',
        subtitle: 'Confirmar salida del pedido',
        text: 'Usa esta opción cuando el pedido ya salió hacia el punto de entrega.',
        confirmText: 'Sí, marcar en camino'
      },
      en_punto_entrega: {
        title: 'Marcar punto de entrega',
        subtitle: 'Confirmar llegada al destino',
        text: 'Usa esta opción cuando ya llegaste al punto acordado con el comprador.',
        confirmText: 'Sí, marcar punto'
      },
      entregado_vendedor: {
        title: 'Marcar entregado',
        subtitle: 'Confirmar entrega realizada',
        text: 'Después de esto, el comprador deberá confirmar la recepción del pedido.',
        confirmText: 'Sí, marcar entregado'
      },
      cancelado_vendedor: {
        title: 'Cancelar pedido',
        subtitle: 'Confirmar cancelación',
        text: 'Esta acción cerrará el pedido actual y puede liberar el siguiente turno en cola.',
        confirmText: 'Sí, cancelar pedido',
        tipo: 'warning'
      }
    };

    return etiquetas[nuevoEstado] || {
      title: 'Actualizar estado',
      subtitle: 'Confirmar cambio',
      text: '¿Deseas continuar con este cambio de estado?',
      confirmText: 'Sí, continuar'
    };
  }

  async function actualizarEstadoPedido(codigoPedido, nuevoEstado) {
    if (state.accionEnCurso) return;

    const item = buscarPedidoEnCache(codigoPedido);
    if (!item) return;

    const meta = obtenerMetaCambioEstado(nuevoEstado);

    const ok = await confirmAction({
      title: meta.title,
      subtitle: meta.subtitle,
      text: meta.text,
      productText: item.titulo_publicacion || item.titulo_producto || `Pedido #${codigoPedido}`,
      note: 'Mantén el estado alineado con el avance real del pedido para evitar confusión al comprador.',
      confirmText: meta.confirmText,
      cancelText: 'Cancelar',
      tipo: meta.tipo || 'info'
    });

    if (!ok) return;

    state.accionEnCurso = true;

    try {
      await ejecutarAccion(
        `${BASE_URL}/api/pedidos/${codigoPedido}/estado`,
        { nuevo_estado: nuevoEstado },
        {
          title: 'Estado actualizado',
          subtitle: 'El avance del pedido fue registrado',
          text: 'El estado del pedido fue actualizado correctamente.',
          productText: item.titulo_publicacion || item.titulo_producto || `Pedido #${codigoPedido}`
        }
      );
    } finally {
      state.accionEnCurso = false;
    }
  }

  function construirHtmlDetalle(item) {
    const estado = badgeEstado(item.estado_actual);

    const imagen = item.imagen_portada_url
      ? `
        <div style="margin-bottom:16px;text-align:center;">
          <img
            src="${escapeHtml(item.imagen_portada_url)}"
            alt="${escapeHtml(item.titulo_publicacion || 'Producto')}"
            style="max-width:220px;width:100%;border-radius:16px;object-fit:cover;"
          >
        </div>
      `
      : '';

    const programado = item.tipo_entrega_raw === 'programada' && item.fecha_hora_programada
      ? `
        <div style="margin-bottom:8px;">
          <strong>Entrega programada:</strong> ${escapeHtml(formatearFechaProgramada(item.fecha_hora_programada))}
        </div>
      `
      : '';

    const cola = Number(item.posicion_cola || 0) > 1
      ? `
        <div style="margin-bottom:8px;">
          <strong>Posición en cola:</strong> ${escapeHtml(item.posicion_cola)}
        </div>
      `
      : '';

    const mensaje = item.mensaje_comprador
      ? `
        <div style="margin-top:12px;text-align:left;">
          <strong>Mensaje del vecino:</strong><br>
          ${escapeHtml(item.mensaje_comprador)}
        </div>
      `
      : '';

    const motivo = item.motivo_estado
      ? `
        <div style="margin-top:12px;text-align:left;">
          <strong>Detalle de estado:</strong><br>
          ${escapeHtml(item.motivo_estado)}
        </div>
      `
      : '';

    return `
      <div class="text-start">
        ${imagen}

        <div style="margin-bottom:10px;">
          <span class="ev-status-pill ${escapeHtml(estado.clase)}">${escapeHtml(estado.texto)}</span>
        </div>

        <div style="margin-bottom:8px;"><strong>Producto:</strong> ${escapeHtml(item.titulo_publicacion || item.titulo_producto || 'Publicación')}</div>
        <div style="margin-bottom:8px;"><strong>Vecino:</strong> ${escapeHtml(item.nombre_vecino || item.nombre_comprador || 'Vecino')}</div>
        <div style="margin-bottom:8px;"><strong>Cantidad:</strong> ${escapeHtml(item.cantidad)}</div>
        <div style="margin-bottom:8px;"><strong>Precio unitario:</strong> S/ ${escapeHtml(formatoMoneda(item.precio_unitario))}</div>
        <div style="margin-bottom:8px;"><strong>Total:</strong> S/ ${escapeHtml(formatoMoneda(item.monto_total))}</div>
        <div style="margin-bottom:8px;"><strong>Entrega:</strong> ${escapeHtml(obtenerTextoEntrega(item))}</div>
        ${programado}
        ${cola}
        <div style="margin-bottom:8px;"><strong>Dirección:</strong> ${escapeHtml(item.direccion_entrega || '-')}</div>
        <div style="margin-bottom:8px;"><strong>Registrado:</strong> ${escapeHtml(formatearFechaRegistro(item.fecha_hora || item.created_at || ''))}</div>
        ${mensaje}
        ${motivo}
      </div>
    `;
  }

  async function verDetalle(codigoPedido) {
    const item = buscarPedidoEnCache(codigoPedido);
    if (!item || !window.Swal) return;

    await Swal.fire(swalBaseConfig({
      title: 'Detalle del pedido',
      html: construirHtmlDetalle(item),
      width: 760,
      showConfirmButton: true,
      confirmButtonText: 'Cerrar'
    }));
  }

  async function ejecutarAccion(url, body, successMeta) {
    try {
      const { response, data } = await fetchJsonRobusto(url, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        cache: 'no-store',
        body: JSON.stringify(body || {})
      });

      if (await manejarRespuestaAuth(response, data)) return;

      if (!response.ok || data?.ok === false) {
        await notify(
          'warning',
          'No se pudo completar la acción',
          'El pedido mantiene su estado actual',
          data?.mensaje || 'Inténtalo nuevamente.'
        );
        await cargarMisPedidos({ force: true });
        return;
      }

      await notify(
        'success',
        successMeta?.title || 'Listo',
        successMeta?.subtitle || 'Acción completada',
        data?.mensaje || successMeta?.text || 'La acción se completó correctamente.',
        {
          htmlExtra: successMeta?.productText
            ? htmlProductNote('Pedido', successMeta.productText)
            : ''
        }
      );

      await cargarMisPedidos({ force: true });

      if (window.EVPollingControl && typeof window.EVPollingControl.revisarPedidosVendedor === 'function') {
        window.EVPollingControl.revisarPedidosVendedor({ silent: true, force: true });
      }
    } catch (error) {
      console.error('[RecibirPedidos] Error en acción:', error);

      await notify(
        'error',
        'No se pudo completar la acción',
        'Ocurrió un problema al procesar la solicitud',
        error.message || 'Inténtalo nuevamente.'
      );
    }
  }

  function iniciarPolling() {
    detenerPolling();

    state.pollingId = window.setInterval(() => {
      if (!vistaActiva()) {
        detenerPolling();
        return;
      }

      if (document.hidden) return;
      if (state.disponibilidadActual !== 1) return;

      const intervalo = estaPausadoPorUi()
        ? POLLING_PEDIDOS_IDLE_MS
        : POLLING_PEDIDOS_MS;

      if ((nowMs() - state.ultimoPollingAt) < intervalo) return;

      cargarMisPedidos();
    }, 1000);
  }

  function detenerPolling() {
    if (state.pollingId) {
      window.clearInterval(state.pollingId);
      state.pollingId = null;
    }
  }

  function bindEventosGlobales() {
    if (document.body.dataset.evRecibirPedidosGlobalBound === '1') return;
    document.body.dataset.evRecibirPedidosGlobalBound = '1';

    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;

      if (!vistaActiva()) return;

      const action = btn.getAttribute('data-action');
      const id = Number(btn.getAttribute('data-id') || 0);
      const estado = btn.getAttribute('data-estado') || '';

      if (id <= 0) return;

      if (action === 'aceptar') {
        await aceptarSolicitud(id);
        return;
      }

      if (action === 'rechazar') {
        await rechazarSolicitud(id);
        return;
      }

      if (action === 'estado') {
        await actualizarEstadoPedido(id, estado);
        return;
      }

      if (action === 'ver') {
        await verDetalle(id);
      }
    });

    document.addEventListener('click', (e) => {
      if (e.target.closest('#sidebar')) marcarInteraccionUi();
    }, true);

    document.addEventListener('pointerdown', (e) => {
      if (e.target.closest('#sidebar')) marcarInteraccionUi();
    }, true);

    document.addEventListener('transitionstart', (e) => {
      if (e.target.closest && e.target.closest('#sidebar')) marcarInteraccionUi();
    }, true);

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        detenerPolling();
        return;
      }

      if (vistaActiva() && state.disponibilidadActual === 1) {
        cargarMisPedidos({ force: true });
        iniciarPolling();
      }
    });

    document.addEventListener('ev:nav-start', marcarInteraccionUi);
    document.addEventListener('ev:nav-end', marcarInteraccionUi);
    document.addEventListener('ev:content-loaded', () => {
      marcarInteraccionUi();
      window.setTimeout(init, 80);
    });
  }

  function bindEventosVista() {
    const { toggle, btnRefrescarPedidos } = state.refs;

    if (toggle && toggle.dataset.evRpBound !== '1') {
      toggle.dataset.evRpBound = '1';

      toggle.addEventListener('change', async () => {
        const nuevoEstado = toggle.checked;

        marcarInteraccionUi();
        aplicarEstadoUI(nuevoEstado);
        await actualizarEstadoBackend(nuevoEstado);
      });
    }

    if (btnRefrescarPedidos && btnRefrescarPedidos.dataset.evRpBound !== '1') {
      btnRefrescarPedidos.dataset.evRpBound = '1';

      btnRefrescarPedidos.addEventListener('click', async () => {
        marcarInteraccionUi();
        await cargarMisPedidos({ force: true });
      });
    }
  }

  async function init() {
    if (!capturarRefs()) {
      detenerPolling();
      return;
    }

    bindEventosGlobales();
    bindEventosVista();

    await cargarEstadoInicial();

    state.inicializado = true;
  }

  document.addEventListener('DOMContentLoaded', init);

  window.EVRecibirPedidos = Object.assign(window.EVRecibirPedidos || {}, {
    init,
    refresh: () => cargarMisPedidos({ force: true }),
    resaltarPedido,
    detenerPolling,
    iniciarPolling,
    pauseBriefly: marcarInteraccionUi
  });
})();
