/* views/js/misPedidosVendedor.js */
(function () {
  'use strict';

  if (window.__EV_MIS_PEDIDOS_VENDEDOR_V16__ === true) {
    if (window.EVMisPedidosVendedor && typeof window.EVMisPedidosVendedor.init === 'function') {
      window.EVMisPedidosVendedor.init();
    }
    return;
  }
  window.__EV_MIS_PEDIDOS_VENDEDOR_V16__ = true;

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const POLLING_MS = 5000;
  const PLACEHOLDER = `${BASE}/public/img/placeholder-ev.png`;

  let pollingTimer = null;
  let ultimoSnapshotPendientes = new Set();
  let alertasMostradas = new Set();
  let vistaActiva = false;
  let tabActiva = 'pendientes';
  let cachePedidos = new Map();
  let cargando = false;
  let accionEnCurso = false;
  let recojoCountdownTimer = null;
  let recojoRefreshProgramado = false;

  function escapeHtml(v) {
    return String(v ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatMoney(v) {
    return Number(v || 0).toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function formatFecha(v) {
    if (!v) return '—';
    const raw = String(v).trim();
    const d = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return raw;
    return d.toLocaleString('es-PE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function formatTiempoCorto(segundos) {
    const s = Math.max(0, Number(segundos || 0));
    const min = Math.floor(s / 60);
    const sec = s % 60;
    return `${String(min).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
  }

  function normalizarUrlImagen(url) {
    const raw = String(url || '').trim();
    return raw !== '' ? raw : PLACEHOLDER;
  }

  function textoEntrega(item) {
    const tipoRaw = String(item.tipo_entrega_raw || item.tipo_entrega || '').trim().toLowerCase();
    if (tipoRaw === 'programada' || tipoRaw === 'programado') return 'Programada';
    return 'Inmediata';
  }

  function esEstadoCola(estado) {
    return ['cola_aceptada', 'cola_pendiente_confirmacion'].includes(String(estado || '').trim());
  }

  function esEstadoNegativo(estado) {
    return [
      'rechazado_vendedor',
      'cancelado_vendedor',
      'cancelado_comprador',
      'sin_respuesta_vendedor'
    ].includes(String(estado || '').trim());
  }

  function esEstadoInfo(estado) {
    return String(estado || '').trim() === 'entregado_vendedor';
  }

  function estadoLegible(estado) {
    const mapa = {
      pendiente_vendedor: 'Pendiente',
      cola_aceptada: 'En cola',
      cola_pendiente_confirmacion: 'Pendiente de confirmación',
      en_preparacion: 'En preparación',
      despachando: 'Despachando',
      listo_para_entrega: 'Listo para entrega',
      en_camino: 'En camino',
      en_punto_entrega: 'En punto de entrega',
      entregado_vendedor: 'Entregado por vendedor',
      entrega_confirmada_comprador: 'Entrega confirmada',
      rechazado_vendedor: 'Rechazado por vendedor',
      cancelado_vendedor: 'Cancelado por vendedor',
      cancelado_comprador: 'Cancelado por comprador',
      sin_respuesta_vendedor: 'Sin respuesta del vendedor'
    };
    return mapa[String(estado || '').trim()] || estado || 'Sin estado';
  }

  function badgeEstado(estado) {
    const e = String(estado || '').trim();

    if (e === 'pendiente_vendedor') {
      return { texto: 'Pendiente', clase: 'ev-mpv-badge ev-mpv-badge-pendiente' };
    }

    if (e === 'cola_aceptada' || e === 'cola_pendiente_confirmacion') {
      return { texto: estadoLegible(e), clase: 'ev-mpv-badge ev-mpv-badge-info' };
    }

    if (esEstadoNegativo(e)) {
      return { texto: estadoLegible(e), clase: 'ev-mpv-badge ev-mpv-badge-negative' };
    }

    if (esEstadoInfo(e)) {
      return { texto: estadoLegible(e), clase: 'ev-mpv-badge ev-mpv-badge-info' };
    }

    if (e === 'entrega_confirmada_comprador') {
      return { texto: estadoLegible(e), clase: 'ev-mpv-badge ev-mpv-badge-final' };
    }

    if (
      e === 'en_preparacion' ||
      e === 'despachando' ||
      e === 'listo_para_entrega' ||
      e === 'en_camino' ||
      e === 'en_punto_entrega'
    ) {
      return { texto: estadoLegible(e), clase: 'ev-mpv-badge ev-mpv-badge-proceso' };
    }

    return { texto: estadoLegible(e), clase: 'ev-mpv-badge ev-mpv-badge-final' };
  }

  function getRefs() {
    return {
      root: document.querySelector('.ev-mpv-page'),

      countPendientes: document.getElementById('mpvCountPendientes'),
      countProceso: document.getElementById('mpvCountProceso'),
      countFinalizados: document.getElementById('mpvCountFinalizados'),

      badgePendientes: document.getElementById('mpvBadgePendientes'),
      badgeProceso: document.getElementById('mpvBadgeProceso'),
      badgeFinalizados: document.getElementById('mpvBadgeFinalizados'),
      badgeRechazadas: document.getElementById('mpvBadgeRechazadas'),
      badgeSinRespuesta: document.getElementById('mpvBadgeSinRespuesta'),

      tabButtons: Array.from(document.querySelectorAll('.ev-mpv-tab')),
      tabPendientes: document.getElementById('mpvTabPendientes'),
      tabProceso: document.getElementById('mpvTabProceso'),
      tabFinalizados: document.getElementById('mpvTabFinalizados'),
      tabRechazadas: document.getElementById('mpvTabRechazadas'),
      tabSinRespuesta: document.getElementById('mpvTabSinRespuesta'),

      listaPendientes: document.getElementById('mpvListaPendientes'),
      listaProceso: document.getElementById('mpvListaProceso'),
      listaFinalizados: document.getElementById('mpvListaFinalizados'),
      listaRechazadas: document.getElementById('mpvListaRechazadas'),
      listaSinRespuesta: document.getElementById('mpvListaSinRespuesta'),

      emptyPendientes: document.getElementById('mpvEmptyPendientes'),
      emptyProceso: document.getElementById('mpvEmptyProceso'),
      emptyFinalizados: document.getElementById('mpvEmptyFinalizados'),
      emptyRechazadas: document.getElementById('mpvEmptyRechazadas'),
      emptySinRespuesta: document.getElementById('mpvEmptySinRespuesta'),

      errorBox: document.getElementById('mpvError'),
      btnRefresh: document.getElementById('btnRefrescarMisPedidosVendedor')
    };
  }

  function ensureSwalStyles() {
    const ID = 'ev-mpv-swal-premium-style';
    if (document.getElementById(ID)) return;

    const css = `
      .ev-mpv-swal-container{
        backdrop-filter: blur(2px);
      }

      .ev-mpv-swal-popup-premium{
        border-radius: 28px !important;
        padding: 28px 24px 22px !important;
        box-shadow:
          0 28px 70px rgba(15,23,42,.20),
          0 10px 24px rgba(15,89,47,.08) !important;
        border: 1px solid rgba(229,231,235,.96) !important;
        background:
          radial-gradient(circle at top, rgba(230,244,236,.65) 0%, rgba(255,255,255,1) 26%, rgba(255,255,255,1) 100%) !important;
      }

      .ev-mpv-swal-title{
        color: #0F592F !important;
        font-weight: 900 !important;
        letter-spacing: -.03em !important;
        font-size: 2rem !important;
        line-height: 1.05 !important;
        margin: 0 0 8px 0 !important;
      }

      .ev-mpv-swal-html{
        color: #6B7280 !important;
        font-size: 1rem !important;
        line-height: 1.55 !important;
        margin-top: 0 !important;
      }

      .ev-mpv-swal-confirm{
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

      .ev-mpv-swal-cancel{
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

      .ev-mpv-swal-loader{
        width: 62px;
        height: 62px;
        border-radius: 50%;
        border: 5px solid rgba(22,163,74,.16);
        border-top-color: rgba(15,89,47,.96);
        margin: 4px auto 16px auto;
        animation: evMpvSpin .85s linear infinite;
      }

      @keyframes evMpvSpin{
        to{ transform: rotate(360deg); }
      }

      .ev-mpv-swal-status-icon{
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

      .ev-mpv-swal-status-icon--info{
        border-color: rgba(59,130,246,.18);
        background: linear-gradient(180deg, rgba(239,246,255,.92), rgba(255,255,255,.98));
      }

      .ev-mpv-swal-status-icon svg{
        width: 52px;
        height: 52px;
      }

      .ev-mpv-swal-subtitle{
        font-weight: 900;
        font-size: 1.1rem;
        color: #0F592F;
        margin-bottom: 8px;
        letter-spacing: -.02em;
        text-align: center;
      }

      .ev-mpv-swal-soft-text{
        font-size: 14px;
        color: #6B7280;
        line-height: 1.6;
        text-align: center;
      }

      .ev-mpv-swal-note{
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

      .ev-mpv-swal-note strong{
        font-weight: 900;
      }

      .ev-mpv-swal-product-card{
        margin-top: 16px;
        padding: 13px 16px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid rgba(229,231,235,.95);
        box-shadow: 0 8px 22px rgba(15,23,42,.05);
        text-align: left;
      }

      .ev-mpv-swal-product-label{
        display: block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9CA3AF;
        margin-bottom: 5px;
      }

      .ev-mpv-swal-product{
        font-size: 15px;
        color: #1A1F36;
        font-weight: 800;
        word-break: break-word;
      }

      .ev-mpv-swal-danger-note{
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

      @media (max-width: 575.98px){
        .ev-mpv-swal-popup-premium{
          padding: 22px 16px 18px !important;
          border-radius: 22px !important;
        }

        .ev-mpv-swal-title{
          font-size: 1.7rem !important;
        }

        .ev-mpv-swal-confirm,
        .ev-mpv-swal-cancel{
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


  function ensureRecojoStyles() {
    const ID = 'ev-mpv-recojo-countdown-style';
    if (document.getElementById(ID)) return;

    const css = `
      .ev-mpv-recojo-box{margin-top:11px;border-radius:20px;padding:12px 13px;border:1px solid rgba(234,124,18,.22);background:linear-gradient(180deg,#FFF7ED 0%,#FFFDF9 100%);box-shadow:0 10px 22px rgba(234,124,18,.07);}
      .ev-mpv-recojo-box.is-expired{border-color:#FECACA;background:linear-gradient(180deg,#FEF2F2 0%,#FFF7F7 100%);box-shadow:0 10px 22px rgba(220,38,38,.06);}
      .ev-mpv-recojo-head{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:7px;}
      .ev-mpv-recojo-title{display:flex;align-items:center;gap:8px;color:#0F592F;font-size:.90rem;font-weight:900;letter-spacing:-.01em;}
      .ev-mpv-recojo-box.is-expired .ev-mpv-recojo-title{color:#991B1B;}
      .ev-mpv-recojo-timer{display:inline-flex;align-items:center;gap:7px;border-radius:999px;padding:7px 11px;background:#fff;border:1px solid #FDBA74;color:#9A3412;font-size:.83rem;font-weight:950;font-variant-numeric:tabular-nums;box-shadow:0 7px 14px rgba(15,23,42,.05);}
      .ev-mpv-recojo-box.is-expired .ev-mpv-recojo-timer{border-color:#FCA5A5;color:#991B1B;}
      .ev-mpv-recojo-text{color:#475569;font-size:.84rem;line-height:1.45;}
      .ev-mpv-recojo-box.is-expired .ev-mpv-recojo-text{color:#7F1D1D;}
      .ev-mpv-btn-cancel-recojo{border:1px solid #FECACA;background:linear-gradient(135deg,#DC2626,#B91C1C);color:#fff;box-shadow:0 12px 24px rgba(220,38,38,.18);border-radius:14px;padding:.74rem .98rem;font-weight:850;font-size:.89rem;transition:transform .16s ease,filter .16s ease,box-shadow .16s ease;}
      .ev-mpv-btn-cancel-recojo:hover,.ev-mpv-btn-cancel-recojo:focus{transform:translateY(-1px);filter:brightness(1.02);color:#fff;box-shadow:0 14px 28px rgba(220,38,38,.24);}
      .ev-mpv-recojo-motivos{display:grid;gap:9px;text-align:left;margin-top:12px;}
      .ev-mpv-recojo-motivo{display:flex;align-items:flex-start;gap:9px;border:1px solid #E5E7EB;background:#fff;border-radius:15px;padding:10px 11px;color:#374151;font-weight:800;font-size:.88rem;}
      .ev-mpv-recojo-motivo input{margin-top:3px;accent-color:#DC2626;}
      .ev-mpv-recojo-textarea{width:100%;min-height:86px;margin-top:11px;border-radius:16px;border:1px solid #E5E7EB;background:#fff;padding:11px 12px;outline:none;resize:vertical;color:#111827;}
      .ev-mpv-recojo-textarea:focus{border-color:#DC2626;box-shadow:0 0 0 4px rgba(220,38,38,.10);}
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
        container: 'ev-mpv-swal-container',
        popup: 'ev-mpv-swal-popup-premium',
        title: 'ev-mpv-swal-title',
        htmlContainer: 'ev-mpv-swal-html',
        confirmButton: 'ev-mpv-swal-confirm',
        cancelButton: 'ev-mpv-swal-cancel'
      }
    }, opts || {});
  }

  function iconSvg(tipo) {
    if (tipo === 'info') {
      return `
        <div class="ev-mpv-swal-status-icon ev-mpv-swal-status-icon--info" aria-hidden="true">
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
        <div class="ev-mpv-swal-status-icon" aria-hidden="true">
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
        <div class="ev-mpv-swal-status-icon" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="32" r="28" stroke="#DC2626" stroke-width="4" fill="rgba(220,38,38,.06)"></circle>
            <path d="M24 24L40 40M40 24L24 40" stroke="#DC2626" stroke-width="5" stroke-linecap="round"></path>
          </svg>
        </div>
      `;
    }

    return `
      <div class="ev-mpv-swal-status-icon" aria-hidden="true">
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
        <div class="ev-mpv-swal-subtitle">${escapeHtml(subtitulo)}</div>
        <div class="ev-mpv-swal-soft-text">${escapeHtml(texto)}</div>
        ${extra || ''}
      </div>
    `;
  }

  function htmlProductNote(label, value, note = '') {
    return `
      <div class="ev-mpv-swal-product-card">
        <span class="ev-mpv-swal-product-label">${escapeHtml(label)}</span>
        <div class="ev-mpv-swal-product">${escapeHtml(value)}</div>
      </div>
      ${note ? `<div class="ev-mpv-swal-note">${note}</div>` : ''}
    `;
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

  function segundosRecojoRestantesItem(item) {
    if (!item || typeof item !== 'object') return 0;

    const directo = Number(item.segundos_recojo_restantes);
    if (Number.isFinite(directo) && directo > 0) {
      return Math.max(0, Math.floor(directo));
    }

    const limiteRaw = String(item.fecha_limite_recojo || '').trim();
    if (limiteRaw !== '') {
      const d = new Date(limiteRaw.replace(' ', 'T'));
      if (!Number.isNaN(d.getTime())) {
        return Math.max(0, Math.ceil((d.getTime() - Date.now()) / 1000));
      }
    }

    return 0;
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

  async function confirmAction({ title, subtitle, text, productText, note, confirmText, cancelText }) {
    if (!window.Swal?.fire) {
      return window.confirm(text);
    }

    const result = await Swal.fire(swalBaseConfig({
      title,
      html: htmlMessage(
        'info',
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
      inputPlaceholder: 'Ejemplo: En este momento no tengo disponibilidad para atender el pedido.',
      inputAttributes: {
        'aria-label': 'Motivo del rechazo',
        maxlength: '500'
      },
      showCancelButton: true,
      confirmButtonText: 'Rechazar solicitud',
      cancelButtonText: 'Cancelar',
      preConfirm: (value) => {
        const txt = String(value || '').trim();
        if (!txt) {
          Swal.showValidationMessage('Debes indicar un motivo de rechazo.');
          return false;
        }
        return txt;
      }
    }));
  }

  function showTab(refs, tab) {
    tabActiva = tab;

    refs.tabButtons.forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.tab === tab);
    });

    refs.tabPendientes?.classList.toggle('d-none', tab !== 'pendientes');
    refs.tabProceso?.classList.toggle('d-none', tab !== 'proceso');
    refs.tabFinalizados?.classList.toggle('d-none', tab !== 'finalizados');
    refs.tabRechazadas?.classList.toggle('d-none', tab !== 'rechazadas');
    refs.tabSinRespuesta?.classList.toggle('d-none', tab !== 'sin-respuesta');
  }

  function limpiarListas(refs) {
    [
      refs.listaPendientes,
      refs.listaProceso,
      refs.listaFinalizados,
      refs.listaRechazadas,
      refs.listaSinRespuesta
    ].forEach((el) => {
      if (el) el.innerHTML = '';
    });
  }

  function getLineaEstado(item) {
    const estado = String(item.estado_actual || '').trim();
    const requierePreparacion = Number(item.requiere_preparacion || 0) === 1;

    if (esEstadoCola(estado)) {
      return `
        <div class="ev-mpv-stepper ev-mpv-stepper-final">
          <div class="ev-mpv-step is-final is-current">
            <span class="ev-mpv-step-dot"></span>
            <span class="ev-mpv-step-text">${escapeHtml(estadoLegible(estado))}</span>
          </div>
        </div>
      `;
    }

    const flujo = requierePreparacion
      ? ['en_preparacion', 'listo_para_entrega', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador']
      : ['despachando', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador'];

    if (esEstadoNegativo(estado)) {
      return `
        <div class="ev-mpv-stepper ev-mpv-stepper-final">
          <div class="ev-mpv-step is-final is-negative">
            <span class="ev-mpv-step-dot"></span>
            <span class="ev-mpv-step-text">${escapeHtml(estadoLegible(estado))}</span>
          </div>
        </div>
      `;
    }

    const currentIndex = flujo.indexOf(estado);

    return `
      <div class="ev-mpv-stepper">
        ${flujo.map((step, index) => {
          const isDone = currentIndex > index;
          const isCurrent = currentIndex === index;

          return `
            <div class="ev-mpv-step ${isDone ? 'is-done' : ''} ${isCurrent ? 'is-current' : ''}">
              <span class="ev-mpv-step-dot"></span>
              <span class="ev-mpv-step-text">${escapeHtml(estadoLegible(step))}</span>
            </div>
          `;
        }).join('')}
      </div>
    `;
  }

  function getSiguienteAccion(item) {
    const estado = String(item.estado_actual || '').trim();
    const map = {
      en_preparacion: {
        label: 'Marcar listo para entrega',
        icon: 'bi-box-seam',
        estado: 'listo_para_entrega',
        variant: 'primary'
      },
      despachando: {
        label: 'Marcar en camino',
        icon: 'bi-truck',
        estado: 'en_camino',
        variant: 'primary'
      },
      listo_para_entrega: {
        label: 'Marcar en camino',
        icon: 'bi-truck',
        estado: 'en_camino',
        variant: 'primary'
      },
      en_camino: {
        label: 'Marcar punto de entrega',
        icon: 'bi-geo-alt',
        estado: 'en_punto_entrega',
        variant: 'primary'
      },
      en_punto_entrega: {
        label: 'Marcar entregado',
        icon: 'bi-check2-square',
        estado: 'entregado_vendedor',
        variant: 'success'
      }
    };

    return map[estado] || null;
  }

  function renderAcciones(item) {
    const estado = String(item.estado_actual || '').trim();
    const id = Number(item.codigo_pedido || 0);
    const acciones = [];

    if (estado === 'pendiente_vendedor') {
      acciones.push(`
        <button type="button" class="btn ev-mpv-btn-accept" data-action="aceptar" data-id="${id}">
          <i class="bi bi-check2-circle me-1"></i>Aceptar solicitud
        </button>
      `);

      acciones.push(`
        <button type="button" class="btn ev-mpv-btn-danger-soft" data-action="rechazar" data-id="${id}">
          <i class="bi bi-x-circle me-1"></i>Rechazar
        </button>
      `);
    } else {
      const puedeCancelarRecojo = estado === 'en_punto_entrega' && Number(item.puede_cancelar_vendedor || 0) === 1;

      if (puedeCancelarRecojo) {
        acciones.push(`
          <button type="button" class="btn ev-mpv-btn-cancel-recojo" data-action="cancelar-recojo" data-id="${id}">
            <i class="bi bi-x-octagon me-1"></i>Cancelar por no recepción
          </button>
        `);
      } else {
        const siguiente = getSiguienteAccion(item);

        if (siguiente) {
          acciones.push(`
            <button
              type="button"
              class="btn ${siguiente.variant === 'success' ? 'ev-mpv-btn-success' : 'ev-mpv-btn-action'}"
              data-action="estado"
              data-id="${id}"
              data-estado="${escapeHtml(siguiente.estado)}">
              <i class="bi ${escapeHtml(siguiente.icon)} me-1"></i>${escapeHtml(siguiente.label)}
            </button>
          `);
        }
      }
    }

    const calificacionPendiente = obtenerCalificacionPendiente(item);

    if (calificacionPendiente) {
      acciones.push(`
        <button type="button" class="btn ev-mpv-btn-rating" data-action="calificar" data-id="${id}">
          <i class="bi bi-star-fill me-1"></i>Calificar comprador
        </button>
      `);
    }

    acciones.push(`
      <button type="button" class="btn ev-mpv-btn-outline" data-action="detalle" data-id="${id}">
        <i class="bi bi-eye me-1"></i>Ver detalle
      </button>
    `);

    return acciones.join('');
  }

  function renderResumenEstado(item) {
    const estado = String(item.estado_actual || '').trim();

    if (estado === 'pendiente_vendedor') {
      return `
        <div class="ev-mpv-state-box ev-mpv-state-box-pending">
          <div class="ev-mpv-state-title">Solicitud pendiente de atención</div>
          <div class="ev-mpv-state-text">
            Atiende esta solicitud antes de que termine el tiempo de espera para evitar que quede sin respuesta.
          </div>
          ${
            item.tiempo_restante_segundos !== null && item.tiempo_restante_segundos !== undefined
              ? `<div class="ev-mpv-time-pill"><i class="bi bi-clock-history"></i>${escapeHtml(formatTiempoCorto(item.tiempo_restante_segundos))}</div>`
              : ''
          }
        </div>
      `;
    }

    if (estado === 'cola_aceptada') {
      return `
        <div class="ev-mpv-state-box ev-mpv-state-box-info">
          <div class="ev-mpv-state-title">Solicitud en cola</div>
          <div class="ev-mpv-state-text">
            Este pedido aún no está aceptado por el vendedor. Quedó en espera y avanzará cuando se libere el turno actual.
          </div>
        </div>
      `;
    }

    if (estado === 'cola_pendiente_confirmacion') {
      return `
        <div class="ev-mpv-state-box ev-mpv-state-box-info">
          <div class="ev-mpv-state-title">Cola pendiente de confirmación</div>
          <div class="ev-mpv-state-text">
            El comprador todavía no confirma si desea mantenerse en la cola. Hasta entonces no pasa al turno de atención.
          </div>
        </div>
      `;
    }

    if (estado === 'en_punto_entrega') {
      const segundos = segundosRecojoRestantesItem(item);
      const puedeCancelar = Number(item.puede_cancelar_vendedor || 0) === 1;
      const expirado = puedeCancelar || segundos <= 0;

      return `
        <div class="ev-mpv-recojo-box ${expirado ? 'is-expired' : ''}">
          <div class="ev-mpv-recojo-head">
            <div class="ev-mpv-recojo-title">
              <i class="bi ${expirado ? 'bi-exclamation-triangle' : 'bi-hourglass-split'}"></i>
              ${expirado ? 'Tiempo de recepción vencido' : 'Tiempo para recepción'}
            </div>
            <div
              class="ev-mpv-recojo-timer"
              data-recojo-countdown="1"
              data-recojo-restantes="${escapeHtml(segundos)}"
              data-recojo-expira-ms="${Date.now() + (segundos * 1000)}">
              <i class="bi bi-clock-history"></i>
              <span>${escapeHtml(formatTiempoCorto(segundos))}</span>
            </div>
          </div>
          <div class="ev-mpv-recojo-text">
            ${
              expirado
                ? 'Ya pasaron los 6 minutos desde que llegaste al punto de entrega. Si el comprador no recibió el pedido, puedes cancelarlo por no recepción.'
                : 'El comprador tiene 6 minutos para recibir el pedido. Si lo recibe, marca la entrega. Si el tiempo vence y no se concreta, se habilitará la cancelación.'
            }
          </div>
        </div>
      `;
    }

    if (estado === 'entregado_vendedor') {
      return `
        <div class="ev-mpv-state-box ev-mpv-state-box-info">
          <div class="ev-mpv-state-title">Esperando confirmación del comprador</div>
          <div class="ev-mpv-state-text">
            Ya registraste la entrega. Ahora el comprador debe confirmar la recepción del pedido.
          </div>
        </div>
      `;
    }

    if (esEstadoNegativo(estado)) {
      return `
        <div class="ev-mpv-state-box ev-mpv-state-box-negative">
          <div class="ev-mpv-state-title">${escapeHtml(estadoLegible(estado))}</div>
          <div class="ev-mpv-state-text">
            ${escapeHtml(item.motivo_estado || 'Este pedido se cerró sin concretarse.')}
          </div>
        </div>
      `;
    }

    if (estado === 'entrega_confirmada_comprador') {
      return `
        <div class="ev-mpv-state-box ev-mpv-state-box-final">
          <div class="ev-mpv-state-title">Entrega confirmada</div>
          <div class="ev-mpv-state-text">
            ${escapeHtml(item.motivo_estado || 'El comprador confirmó la recepción del pedido.')}
          </div>
        </div>
      `;
    }

    return `
      <div class="ev-mpv-state-box ev-mpv-state-box-process">
        <div class="ev-mpv-state-title">Siguiente paso operativo</div>
        <div class="ev-mpv-state-text">
          ${escapeHtml(item.motivo_estado || 'Continúa con el siguiente avance del pedido.')}
        </div>
      </div>
    `;
  }

  function renderQuickPills(item) {
    const pills = [
      `
      <span class="ev-mpv-pill">
        <i class="bi bi-box-seam"></i>
        Cant. ${escapeHtml(item.cantidad || 0)}
      </span>
      `,
      `
      <span class="ev-mpv-pill">
        <i class="bi bi-lightning-charge"></i>
        ${escapeHtml(textoEntrega(item))}
      </span>
      `
    ];

    if (Number(item.posicion_cola || 0) > 1 || String(item.estado_actual || '').trim() === 'cola_aceptada') {
      pills.push(`
        <span class="ev-mpv-pill">
          <i class="bi bi-list-ol"></i>
          Cola #${escapeHtml(item.posicion_cola || 0)}
        </span>
      `);
    }

    return pills.join('');
  }

  function renderFlujo(item) {
    if (esEstadoNegativo(item.estado_actual) || esEstadoCola(item.estado_actual)) {
      return `
        <div class="ev-mpv-section-title">
          <i class="bi bi-diagram-3"></i>
          Estado del pedido
        </div>
        ${getLineaEstado(item)}
      `;
    }

    return `
      <div class="ev-mpv-section-title">
        <i class="bi bi-diagram-3"></i>
        Flujo del pedido
      </div>
      ${getLineaEstado(item)}
    `;
  }

  function renderCard(item) {
    const badge = badgeEstado(item.estado_actual);
    const imagen = normalizarUrlImagen(item.imagen_portada_url || item.imagen_portada);
    const fechaBase = item.fecha_hora || item.created_at || null;
    const tieneProgramacion = String(item.tipo_entrega_raw || '').trim() === 'programada' && !!item.fecha_hora_programada;

    return `
      <article class="ev-mpv-order" data-id="${Number(item.codigo_pedido || 0)}">
        <div class="ev-mpv-order-top">
          <div class="ev-mpv-order-media">
            <img src="${escapeHtml(imagen)}" alt="${escapeHtml(item.titulo_publicacion || 'Pedido')}">
          </div>

          <div class="ev-mpv-order-head">
            <div class="ev-mpv-order-head-row">
              <div class="ev-mpv-order-head-main">
                <div class="ev-mpv-order-title">${escapeHtml(item.titulo_publicacion || 'Pedido')}</div>
                <div class="ev-mpv-order-meta">
                  Pedido #${Number(item.codigo_pedido || 0)} · ${escapeHtml(formatFecha(fechaBase))}
                </div>
              </div>
              <span class="${badge.clase}">${escapeHtml(badge.texto)}</span>
            </div>

            <div class="ev-mpv-order-quick">
              ${renderQuickPills(item)}
            </div>
          </div>

          <div class="ev-mpv-order-top-data">
            <div class="ev-mpv-order-data">
              <div class="ev-mpv-data-box ev-mpv-data-box-date">
                <span>Fecha</span>
                <strong>${escapeHtml(formatFecha(fechaBase))}</strong>
              </div>

              <div class="ev-mpv-data-box ev-mpv-data-box-total">
                <span>Total</span>
                <strong>S/ ${escapeHtml(formatMoney(item.monto_total || item.total || 0))}</strong>
              </div>

              <div class="ev-mpv-data-box ev-mpv-data-box-buyer">
                <span>Comprador</span>
                <strong>${escapeHtml(item.nombre_vecino || 'Vecino')}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="ev-mpv-order-body">
          ${renderFlujo(item)}

          <div class="ev-mpv-info-card">
            <div class="ev-mpv-line">
              <span class="ev-mpv-line-label">Dirección</span>
              <span class="ev-mpv-line-value">${escapeHtml(item.direccion_entrega || '—')}</span>
            </div>

            ${
              tieneProgramacion
                ? `
                <div class="ev-mpv-line">
                  <span class="ev-mpv-line-label">Entrega programada</span>
                  <span class="ev-mpv-line-value">${escapeHtml(formatFecha(item.fecha_hora_programada))}</span>
                </div>
              `
                : ''
            }

            ${
              Number(item.posicion_cola || 0) > 1 || String(item.estado_actual || '').trim() === 'cola_aceptada'
                ? `
                <div class="ev-mpv-line">
                  <span class="ev-mpv-line-label">Posición en cola</span>
                  <span class="ev-mpv-line-value">#${escapeHtml(item.posicion_cola || 0)}</span>
                </div>
              `
                : ''
            }

            ${
              String(item.mensaje_comprador || '').trim() !== ''
                ? `
                <div class="ev-mpv-note">
                  <span class="ev-mpv-note-label">Mensaje del comprador</span>
                  <div class="ev-mpv-note-text">${escapeHtml(item.mensaje_comprador)}</div>
                </div>
              `
                : ''
            }
          </div>

          ${renderResumenEstado(item)}

          <div class="ev-mpv-actions">
            ${renderAcciones(item)}
          </div>
        </div>
      </article>
    `;
  }

  function pintarGrupo(lista, target, emptyBox) {
    if (!target || !emptyBox) return;

    const items = Array.isArray(lista) ? lista : [];
    target.innerHTML = '';

    if (!items.length) {
      emptyBox.classList.remove('d-none');
      return;
    }

    emptyBox.classList.add('d-none');
    target.innerHTML = items.map(renderCard).join('');
  }

  async function handleAuthFromResponse(resp, json) {
    if (resp.status === 401) {
      await notify(
        'info',
        'Sesión finalizada',
        'Tu sesión ya no está activa',
        json?.mensaje || 'Vuelve a iniciar sesión para continuar.'
      );
      window.location.href = json?.redirect || `${BASE}/login`;
      return true;
    }

    if (resp.status === 403 && String(json?.error || '').trim() === 'CUENTA_BLOQUEADA') {
      await notify(
        'warning',
        'Cuenta bloqueada',
        'Tu cuenta ya no está disponible',
        json?.mensaje || 'Por seguridad, debes volver a iniciar sesión.'
      );
      window.location.href = json?.redirect || `${BASE}/login`;
      return true;
    }

    if (resp.status === 409 && String(json?.error || '').trim() === 'CUENTA_OBSERVADA') {
      await notify(
        'warning',
        'Cuenta observada',
        'Debes revisar el estado de tu cuenta',
        json?.mensaje || 'Tu cuenta se encuentra observada.'
      );
      window.location.href = json?.redirect || `${BASE}/cuenta-observada`;
      return true;
    }

    return false;
  }

  function obtenerCalificacionPendiente(item) {
    const pendiente = item?.calificacion_pendiente || null;
    if (!pendiente || Number(pendiente.codigo_calificacion || 0) <= 0) return null;
    if (String(pendiente.estado || '').trim() !== 'pendiente') return null;
    return pendiente;
  }

  function ensureCalificacionStyles() {
    const ID = 'ev-calificacion-premium-style';
    if (document.getElementById(ID)) return;

    const style = document.createElement('style');
    style.id = ID;
    style.type = 'text/css';
    style.textContent = `
      .ev-rating-box{text-align:left;display:flex;flex-direction:column;gap:13px;color:#111827;}
      .ev-rating-sub{color:#6B7280;font-size:.94rem;line-height:1.55;text-align:center;margin-top:-4px;max-width:460px;margin-left:auto;margin-right:auto;}
      .ev-rating-target{border:1px solid rgba(229,231,235,.95);border-radius:20px;background:linear-gradient(180deg,#FFFFFF 0%,#FCFDFC 100%);padding:13px 15px;box-shadow:0 10px 22px rgba(15,23,42,.055);}
      .ev-rating-target span{display:block;color:#6B7280;font-size:.73rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;}
      .ev-rating-target strong{display:block;color:#0F592F;font-size:1rem;font-weight:900;line-height:1.25;}
      .ev-rating-stars{display:flex;justify-content:center;gap:8px;margin:2px 0 0;}
      .ev-rating-star{width:44px;height:44px;border-radius:15px;border:1px solid #E5E7EB;background:#fff;color:#CBD5E1;font-size:1.5rem;line-height:1;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 8px 16px rgba(15,23,42,.05);transition:transform .15s ease,box-shadow .15s ease,color .15s ease,background .15s ease,border-color .15s ease;}
      .ev-rating-star:hover,.ev-rating-star.is-active{color:#F59E0B;background:#FFF7ED;border-color:#FCD9BD;transform:translateY(-1px);box-shadow:0 14px 24px rgba(234,124,18,.14);}
      .ev-rating-label{text-align:center;color:#0F592F;font-weight:900;font-size:.96rem;min-height:22px;margin-top:1px;}
      .ev-rating-helper{text-align:center;color:#6B7280;font-size:.82rem;font-weight:750;line-height:1.35;margin-top:-7px;}
      .ev-rating-chips{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;min-height:42px;}
      .ev-rating-chip{border:1px solid rgba(22,163,74,.18);background:#fff;color:#0F592F;border-radius:999px;padding:8px 11px;font-size:.82rem;font-weight:850;box-shadow:0 6px 14px rgba(15,23,42,.04);transition:transform .15s ease,box-shadow .15s ease,background .15s ease,border-color .15s ease,color .15s ease;}
      .ev-rating-chip:hover{transform:translateY(-1px);box-shadow:0 10px 18px rgba(15,23,42,.07);}
      .ev-rating-chip.is-active{background:#ECFDF3;border-color:#BBF7D0;color:#166534;}
      .ev-rating-box.is-low .ev-rating-chip{border-color:#FECACA;color:#991B1B;}
      .ev-rating-box.is-low .ev-rating-chip.is-active{background:#FEF2F2;border-color:#FCA5A5;color:#991B1B;}
      .ev-rating-box.is-mid .ev-rating-chip{border-color:#FCD9BD;color:#9A3412;}
      .ev-rating-box.is-mid .ev-rating-chip.is-active{background:#FFF7ED;border-color:#FDBA74;color:#9A3412;}
      .ev-rating-chip-hint{width:100%;text-align:center;border:1px dashed #D1D5DB;border-radius:16px;background:#F9FAFB;color:#6B7280;font-size:.84rem;font-weight:750;padding:11px 12px;}
      .ev-rating-comment{width:100%;min-height:92px;border-radius:17px;border:1px solid #E5E7EB;background:#fff;padding:12px 13px;color:#111827;outline:none;resize:vertical;line-height:1.45;}
      .ev-rating-comment:focus{border-color:#EA7C12;box-shadow:0 0 0 4px rgba(234,124,18,.10);}
      .ev-rating-warning{border:1px solid #FECACA;background:linear-gradient(180deg,#FEF2F2 0%,#FFF7F7 100%);color:#991B1B;border-radius:16px;padding:11px 12px;font-size:.86rem;line-height:1.45;}
      .ev-rating-check{display:flex;align-items:flex-start;gap:9px;margin-top:8px;color:#7F1D1D;font-weight:850;}
      .ev-rating-check input{margin-top:3px;accent-color:#DC2626;}
      .ev-mpv-btn-rating{background:linear-gradient(135deg,#0F592F,#16A34A);border:none;color:#fff;box-shadow:0 12px 24px rgba(22,163,74,.20);border-radius:14px;padding:.74rem .98rem;font-weight:850;font-size:.89rem;transition:transform .16s ease,filter .16s ease;}
      .ev-mpv-btn-rating:hover{transform:translateY(-1px);filter:brightness(1.02);color:#fff;}
      @media(max-width:575.98px){.ev-rating-stars{gap:6px}.ev-rating-star{width:39px;height:39px;border-radius:13px;font-size:1.35rem}.ev-rating-chips{justify-content:flex-start}.ev-rating-chip{font-size:.80rem}}
    `;
    document.head.appendChild(style);
  }


  function etiquetasPorPuntajeRol(rolCalificado, puntaje) {
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

  function textoPuntaje(puntaje) {
    const mapa = {
      1: 'Muy mala experiencia',
      2: 'Mala experiencia',
      3: 'Experiencia regular',
      4: 'Buena experiencia',
      5: 'Excelente experiencia'
    };
    return mapa[Number(puntaje || 0)] || 'Selecciona una calificación';
  }

  function ayudaPuntaje(puntaje) {
    const mapa = {
      1: 'Selecciona el motivo principal. Puedes reportarlo a soporte.',
      2: 'Cuéntanos qué falló para mejorar la experiencia.',
      3: 'Marca lo que mejor describa esta experiencia.',
      4: 'Selecciona lo que salió bien.',
      5: 'Marca los puntos fuertes de esta experiencia.'
    };
    return mapa[Number(puntaje || 0)] || 'Primero selecciona una cantidad de estrellas.';
  }

  function placeholderComentario(rolCalificado, puntaje) {
    const score = Number(puntaje || 0);
    const rol = String(rolCalificado || '').trim();

    if (score <= 0) {
      return 'Comentario opcional.';
    }

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

  function nivelPuntaje(puntaje) {
    const score = Number(puntaje || 0);
    if (score <= 0) return 'empty';
    if (score <= 2) return 'low';
    if (score === 3) return 'mid';
    return 'high';
  }

  function renderEtiquetasRating(popup, rolCalificado, puntaje) {
    const chipsBox = popup.querySelector('#evRatingChips');
    const helper = popup.querySelector('#evRatingHelper');
    const comment = popup.querySelector('#evRatingComment');
    const box = popup.querySelector('.ev-rating-box');

    if (helper) helper.textContent = ayudaPuntaje(puntaje);
    if (comment) comment.placeholder = placeholderComentario(rolCalificado, puntaje);

    if (box) {
      box.classList.remove('is-empty', 'is-low', 'is-mid', 'is-high');
      box.classList.add(`is-${nivelPuntaje(puntaje)}`);
    }

    if (!chipsBox) return;

    const etiquetas = etiquetasPorPuntajeRol(rolCalificado, puntaje);
    if (!etiquetas.length) {
      chipsBox.innerHTML = '<div class="ev-rating-chip-hint">Selecciona estrellas para ver opciones rápidas.</div>';
      return;
    }

    chipsBox.innerHTML = etiquetas
      .map(e => `<button type="button" class="ev-rating-chip" data-etiqueta="${escapeHtml(e)}">${escapeHtml(e)}</button>`)
      .join('');

    chipsBox.querySelectorAll('.ev-rating-chip').forEach((btn) => {
      btn.addEventListener('click', () => btn.classList.toggle('is-active'));
    });
  }

  async function abrirModalCalificacion(item, calificacionManual = null) {
    if (!window.Swal?.fire) return;

    const calificacion = calificacionManual || obtenerCalificacionPendiente(item);
    const codigoCalificacion = Number(calificacion?.codigo_calificacion || 0);
    if (!codigoCalificacion) return;

    ensureCalificacionStyles();

    const rolCalificado = String(calificacion.rol_calificado || 'comprador').trim();
    const nombreCalificado = String(calificacion.nombre_calificado || item?.nombre_comprador || item?.nombre_vecino || 'Vecino').trim();
    const tituloPedido = String(calificacion.titulo_publicacion || item?.titulo_publicacion || item?.titulo_producto || 'Pedido EV').trim();
    const titulo = rolCalificado === 'comprador' ? 'Califica al comprador' : 'Califica al vendedor';
    const subtitulo = rolCalificado === 'comprador'
      ? 'Tu opinión ayuda a mantener una comunidad seria y respetuosa.'
      : 'Tu opinión ayuda a que otros vecinos compren con más confianza.';
    const html = `
      <div class="ev-rating-box">
        <div class="ev-rating-sub">${escapeHtml(subtitulo)}</div>
        <div class="ev-rating-target">
          <span>Pedido</span>
          <strong>${escapeHtml(tituloPedido)}</strong>
          <span style="margin-top:8px">Vecino a calificar</span>
          <strong>${escapeHtml(nombreCalificado)}</strong>
        </div>
        <div class="ev-rating-stars" role="group" aria-label="Calificación de 1 a 5 estrellas">
          ${[1,2,3,4,5].map(n => `<button type="button" class="ev-rating-star" data-rating="${n}" aria-label="${n} estrellas">★</button>`).join('')}
        </div>
        <div class="ev-rating-label" id="evRatingLabel">Selecciona una calificación</div>
        <div class="ev-rating-helper" id="evRatingHelper">Primero selecciona una cantidad de estrellas.</div>
        <div class="ev-rating-chips" id="evRatingChips">
          <div class="ev-rating-chip-hint">Selecciona estrellas para ver opciones rápidas.</div>
        </div>
        <textarea id="evRatingComment" class="ev-rating-comment" maxlength="800" placeholder="Comentario opcional."></textarea>
        <div id="evRatingLow" class="ev-rating-warning d-none">
          Calificaste con una experiencia baja. Puedes marcar esta experiencia para que soporte la revise.
          <label class="ev-rating-check"><input type="checkbox" id="evRatingReport"> Reportar esta experiencia a soporte</label>
        </div>
      </div>
    `;

    const result = await Swal.fire(swalBaseConfig({
      title: titulo,
      html,
      width: 620,
      showCancelButton: true,
      confirmButtonText: 'Enviar calificación',
      cancelButtonText: 'Ahora no',
      didOpen: () => {
        window.__evRatingValue = 0;
        const popup = Swal.getPopup();
        const label = popup.querySelector('#evRatingLabel');
        const lowBox = popup.querySelector('#evRatingLow');

        renderEtiquetasRating(popup, rolCalificado, 0);

        popup.querySelectorAll('.ev-rating-star').forEach((btn) => {
          btn.addEventListener('click', () => {
            const value = Number(btn.dataset.rating || 0);
            window.__evRatingValue = value;

            popup.querySelectorAll('.ev-rating-star').forEach((star) => {
              star.classList.toggle('is-active', Number(star.dataset.rating || 0) <= value);
            });

            if (label) label.textContent = textoPuntaje(value);
            if (lowBox) lowBox.classList.toggle('d-none', value > 2 || value <= 0);
            renderEtiquetasRating(popup, rolCalificado, value);
          });
        });
      },
      preConfirm: () => {
        const popup = Swal.getPopup();
        const puntaje = Number(window.__evRatingValue || 0);
        if (puntaje < 1 || puntaje > 5) {
          Swal.showValidationMessage('Selecciona una calificación del 1 al 5.');
          return false;
        }

        const etiquetasSeleccionadas = Array.from(popup.querySelectorAll('.ev-rating-chip.is-active')).map((btn) => btn.dataset.etiqueta || '').filter(Boolean);
        const comentario = String(popup.querySelector('#evRatingComment')?.value || '').trim();
        const reportar = popup.querySelector('#evRatingReport')?.checked === true;

        return { puntaje, etiquetas: etiquetasSeleccionadas, comentario, reportar_soporte: reportar ? 1 : 0 };
      }
    }));

    if (!result.isConfirmed || !result.value) return;

    const resp = await fetch(`${BASE}/api/calificaciones/${codigoCalificacion}/enviar`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(result.value)
    });

    const json = await resp.json().catch(() => ({}));
    if (await handleAuthFromResponse(resp, json)) return;

    if (!resp.ok || json?.ok === false) {
      await notify('error', 'No se pudo calificar', 'La calificación no fue registrada', json?.mensaje || 'No se pudo registrar la calificación.');
      return;
    }

    await notify('success', 'Calificación enviada', 'Gracias por tu opinión', json?.mensaje || 'Gracias por ayudar a construir confianza en Entre Vecinos.');
    await cargarPedidos({ silent: true });
  }

  async function fetchPedidos() {
    const resp = await fetch(`${BASE}/api/pedidos/mis`, {
      method: 'GET',
      credentials: 'include',
      headers: { Accept: 'application/json' }
    });

    const json = await resp.json().catch(() => ({}));

    if (await handleAuthFromResponse(resp, json)) {
      return { __authHandled: true, data: {} };
    }

    if (!resp.ok || json?.ok === false) {
      throw new Error(json?.mensaje || 'No se pudieron cargar los pedidos.');
    }

    return json?.data || {};
  }

  function refrescarCache(data) {
    cachePedidos = new Map();

    ['pendientes', 'en_proceso', 'finalizados'].forEach((grupo) => {
      const items = Array.isArray(data[grupo]) ? data[grupo] : [];
      items.forEach((item) => {
        const id = Number(item.codigo_pedido || 0);
        if (id > 0) cachePedidos.set(id, item);
      });
    });
  }

  function dividirPendientes(lista) {
    const items = Array.isArray(lista) ? lista : [];

    return {
      pendientesAtendibles: items.filter((item) => String(item.estado_actual || '').trim() === 'pendiente_vendedor'),
      enCola: items.filter((item) => String(item.estado_actual || '').trim() === 'cola_aceptada')
    };
  }

  function dividirFinalizados(lista) {
    const items = Array.isArray(lista) ? lista : [];

    const rechazadas = items.filter((item) =>
      String(item.estado_actual || '').trim() === 'rechazado_vendedor'
    );

    const sinRespuesta = items.filter((item) =>
      String(item.estado_actual || '').trim() === 'sin_respuesta_vendedor'
    );

    const finalizadas = items.filter((item) => {
      const estado = String(item.estado_actual || '').trim();
      return estado !== 'rechazado_vendedor' && estado !== 'sin_respuesta_vendedor';
    });

    return { finalizadas, rechazadas, sinRespuesta };
  }

  async function mostrarAlertaNuevaSolicitud(item) {
    if (!window.Swal || !item) return;

    const id = Number(item.codigo_pedido || 0);
    if (!id || alertasMostradas.has(id)) return;
    if (!reclamarAlertaSolicitudCompartida(id)) return;

    alertasMostradas.add(id);

    await notify(
      'info',
      'Nueva solicitud recibida',
      'Tienes una nueva solicitud pendiente',
      `Ya puedes revisar y atender el pedido de ${item.titulo_publicacion || 'tu publicación'}.`,
      {
        htmlExtra: htmlProductNote(
          'Publicación',
          item.titulo_publicacion || 'Solicitud recibida',
          'Recuerda: solo puedes atender una solicitud activa a la vez. Las demás se mantendrán en cola.'
        ),
        confirmButtonText: 'Ver ahora'
      }
    );

    showTab(getRefs(), 'pendientes');
  }

  async function cargarPedidos(opciones = {}) {
    const refs = getRefs();
    if (!refs.root || cargando) return;

    cargando = true;
    const silent = opciones.silent === true;

    try {
      refs.errorBox?.classList.add('d-none');

      const data = await fetchPedidos();
      if (data && data.__authHandled) {
        return;
      }

      refrescarCache(data);

      const pendientesRaw = Array.isArray(data.pendientes) ? data.pendientes : [];
      const proceso = Array.isArray(data.en_proceso) ? data.en_proceso : [];
      const finalizadosRaw = Array.isArray(data.finalizados) ? data.finalizados : [];

      const { pendientesAtendibles, enCola } = dividirPendientes(pendientesRaw);
      const pendientes = [...pendientesAtendibles, ...enCola];

      const { finalizadas, rechazadas, sinRespuesta } = dividirFinalizados(finalizadosRaw);

      if (refs.countPendientes) refs.countPendientes.textContent = String(pendientes.length);
      if (refs.countProceso) refs.countProceso.textContent = String(proceso.length);
      if (refs.countFinalizados) refs.countFinalizados.textContent = String(finalizadas.length);

      if (refs.badgePendientes) refs.badgePendientes.textContent = String(pendientes.length);
      if (refs.badgeProceso) refs.badgeProceso.textContent = String(proceso.length);
      if (refs.badgeFinalizados) refs.badgeFinalizados.textContent = String(finalizadas.length);
      if (refs.badgeRechazadas) refs.badgeRechazadas.textContent = String(rechazadas.length);
      if (refs.badgeSinRespuesta) refs.badgeSinRespuesta.textContent = String(sinRespuesta.length);

      pintarGrupo(pendientes, refs.listaPendientes, refs.emptyPendientes);
      pintarGrupo(proceso, refs.listaProceso, refs.emptyProceso);
      pintarGrupo(finalizadas, refs.listaFinalizados, refs.emptyFinalizados);
      pintarGrupo(rechazadas, refs.listaRechazadas, refs.emptyRechazadas);
      pintarGrupo(sinRespuesta, refs.listaSinRespuesta, refs.emptySinRespuesta);

      showTab(refs, tabActiva);
      iniciarCountdownRecojo();

      const snapshotNuevo = new Set(
        pendientesAtendibles.map((x) => Number(x.codigo_pedido || 0)).filter(Boolean)
      );

      if (!silent && ultimoSnapshotPendientes.size === 0) {
        ultimoSnapshotPendientes = snapshotNuevo;
        return;
      }

      const nuevos = pendientesAtendibles.filter((item) => {
        const id = Number(item.codigo_pedido || 0);
        return id > 0 && !ultimoSnapshotPendientes.has(id);
      });

      ultimoSnapshotPendientes = snapshotNuevo;

      if (nuevos.length > 0 && vistaActiva) {
        await mostrarAlertaNuevaSolicitud(nuevos[0]);
      }
    } catch (e) {
      console.error('[MIS_PEDIDOS_VENDEDOR]', e);
      limpiarListas(refs);
      refs.errorBox?.classList.remove('d-none');
      [
        refs.emptyPendientes,
        refs.emptyProceso,
        refs.emptyFinalizados,
        refs.emptyRechazadas,
        refs.emptySinRespuesta
      ].forEach((el) => el?.classList.add('d-none'));
    } finally {
      cargando = false;
    }
  }

  async function aceptar(id) {
    if (accionEnCurso) return;
    const item = cachePedidos.get(Number(id || 0));
    if (!item) return;

    const ok = await confirmAction({
      title: 'Aceptar solicitud',
      subtitle: 'Confirmar recepción del pedido',
      text: 'Al aceptarlo, este pedido pasará al flujo de atención y seguirá ocupando tu turno actual.',
      productText: item.titulo_publicacion || `Pedido #${id}`,
      note: 'Mientras este pedido siga activo, las siguientes solicitudes permanecerán en cola hasta que el turno se libere.',
      confirmText: 'Sí, aceptar',
      cancelText: 'Cancelar'
    });

    if (!ok) return;

    accionEnCurso = true;

    try {
      const resp = await fetch(`${BASE}/api/pedidos/${id}/aceptar`, {
        method: 'POST',
        credentials: 'include',
        headers: { Accept: 'application/json' }
      });

      const json = await resp.json().catch(() => ({}));

      if (await handleAuthFromResponse(resp, json)) return;

      if (!resp.ok || json?.ok === false) {
        await notify(
          'warning',
          'No se pudo aceptar',
          'La solicitud no pudo pasar a atención',
          json?.mensaje || 'Valida el estado actual del pedido e inténtalo nuevamente.',
          {
            htmlExtra: htmlProductNote('Pedido', item.titulo_publicacion || `Pedido #${id}`)
          }
        );
        return;
      }

      await notify(
        'success',
        'Solicitud aceptada',
        'El pedido ya está en atención',
        json?.mensaje || 'La solicitud fue aceptada correctamente.',
        {
          htmlExtra: htmlProductNote(
            'Pedido',
            item.titulo_publicacion || `Pedido #${id}`,
            'Ahora puedes continuar con los cambios de estado según el avance real del pedido.'
          )
        }
      );

      tabActiva = 'proceso';
      await cargarPedidos({ silent: true });
    } finally {
      accionEnCurso = false;
    }
  }

  async function rechazar(id) {
    if (accionEnCurso) return;
    const item = cachePedidos.get(Number(id || 0));
    if (!item) return;

    const r = await promptReject(item);
    if (!r.isConfirmed || !r.value) return;

    accionEnCurso = true;

    try {
      const resp = await fetch(`${BASE}/api/pedidos/${id}/rechazar`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ motivo_rechazo: r.value })
      });

      const json = await resp.json().catch(() => ({}));

      if (await handleAuthFromResponse(resp, json)) return;

      if (!resp.ok || json?.ok === false) {
        await notify(
          'error',
          'No se pudo rechazar',
          'La solicitud sigue pendiente',
          json?.mensaje || 'No se pudo registrar el rechazo de la solicitud.',
          {
            htmlExtra: htmlProductNote('Pedido', item.titulo_publicacion || `Pedido #${id}`)
          }
        );
        return;
      }

      await notify(
        'success',
        'Solicitud rechazada',
        'El pedido fue cerrado correctamente',
        json?.mensaje || 'La solicitud fue rechazada correctamente.',
        {
          htmlExtra: htmlProductNote(
            'Pedido',
            item.titulo_publicacion || `Pedido #${id}`,
            'Si había otras solicitudes en cola, el sistema avanzará la siguiente según el orden de atención.'
          )
        }
      );

      tabActiva = 'rechazadas';
      await cargarPedidos({ silent: true });
    } finally {
      accionEnCurso = false;
    }
  }

  function obtenerMetaCambioEstado(estado) {
    const mapa = {
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
        confirmText: 'Sí, marcar punto de entrega'
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
        confirmText: 'Sí, cancelar pedido'
      }
    };

    return mapa[String(estado || '').trim()] || {
      title: 'Confirmar acción',
      subtitle: 'Validar cambio de estado',
      text: '¿Deseas continuar con este cambio?',
      confirmText: 'Sí, continuar'
    };
  }

  async function promptCancelacionNoRecojo(item) {
    if (!window.Swal?.fire) {
      const ok = window.confirm('¿Cancelar el pedido porque no se concretó la recepción?');
      return ok
        ? { isConfirmed: true, value: { motivo_cancelacion_clave: 'comprador_no_se_presento', motivo_cancelacion_detalle: '' } }
        : { isConfirmed: false };
    }

    const motivos = [
      { value: 'comprador_no_se_presento', label: 'El comprador no se presentó' },
      { value: 'comprador_no_responde', label: 'El comprador no responde' },
      { value: 'comprador_rechazo_recepcion', label: 'El comprador rechazó recibir el pedido' },
      { value: 'no_se_pudo_concretar', label: 'No se pudo concretar la entrega' },
      { value: 'otro', label: 'Otro motivo' }
    ];

    const html = `
      ${htmlMessage(
        'warning',
        'Cancelar por no recepción',
        'Usa esta opción solo si el tiempo de recepción venció y no se pudo concretar la entrega.',
        htmlProductNote(
          'Pedido',
          item?.titulo_publicacion || 'Pedido seleccionado',
          '<strong>Importante:</strong> esta acción cerrará el pedido y quedará registrada en el historial EV.'
        )
      )}
      <div class="ev-mpv-recojo-motivos">
        ${motivos.map((m, idx) => `
          <label class="ev-mpv-recojo-motivo">
            <input type="radio" name="evMotivoRecojo" value="${escapeHtml(m.value)}" ${idx === 0 ? 'checked' : ''}>
            <span>${escapeHtml(m.label)}</span>
          </label>
        `).join('')}
      </div>
      <textarea id="evMotivoRecojoDetalle" class="ev-mpv-recojo-textarea" maxlength="400" placeholder="Detalle opcional. Ejemplo: Esperé en el punto acordado, pero el comprador no llegó."></textarea>
    `;

    return Swal.fire(swalBaseConfig({
      title: 'Cancelar pedido',
      html,
      width: 620,
      showCancelButton: true,
      confirmButtonText: 'Sí, cancelar pedido',
      cancelButtonText: 'Volver',
      preConfirm: () => {
        const popup = Swal.getPopup();
        const selected = popup.querySelector('input[name="evMotivoRecojo"]:checked');
        const detalle = String(popup.querySelector('#evMotivoRecojoDetalle')?.value || '').trim();

        if (!selected || !selected.value) {
          Swal.showValidationMessage('Selecciona el motivo de cancelación.');
          return false;
        }

        return {
          motivo_cancelacion_clave: selected.value,
          motivo_cancelacion_detalle: detalle
        };
      }
    }));
  }

  async function cancelarPorNoRecojo(id) {
    if (accionEnCurso) return;
    const item = cachePedidos.get(Number(id || 0));
    if (!item) return;

    const r = await promptCancelacionNoRecojo(item);
    if (!r.isConfirmed || !r.value) return;

    accionEnCurso = true;

    try {
      const resp = await fetch(`${BASE}/api/pedidos/${id}/estado`, {
        method: 'POST',
        credentials: 'include',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({
          nuevo_estado: 'cancelado_vendedor',
          motivo_cancelacion_clave: r.value.motivo_cancelacion_clave,
          motivo_cancelacion_detalle: r.value.motivo_cancelacion_detalle
        })
      });

      const json = await resp.json().catch(() => ({}));
      if (await handleAuthFromResponse(resp, json)) return;

      if (!resp.ok || json?.ok === false) {
        await notify('warning', 'No se pudo cancelar', 'El pedido mantiene su estado actual', json?.mensaje || 'No se pudo cancelar el pedido.', {
          htmlExtra: htmlProductNote('Pedido', item.titulo_publicacion || `Pedido #${id}`)
        });
        return;
      }

      await notify('success', 'Pedido cancelado', 'La cancelación fue registrada', json?.mensaje || 'El pedido fue cancelado por no concretarse la recepción.', {
        htmlExtra: htmlProductNote('Pedido', item.titulo_publicacion || `Pedido #${id}`)
      });

      tabActiva = 'finalizados';
      await cargarPedidos({ silent: true });
    } finally {
      accionEnCurso = false;
    }
  }

  async function cambiarEstado(id, estado) {
    if (accionEnCurso) return;
    const item = cachePedidos.get(Number(id || 0));
    if (!item) return;

    const meta = obtenerMetaCambioEstado(estado);

    const ok = await confirmAction({
      title: meta.title,
      subtitle: meta.subtitle,
      text: meta.text,
      productText: item.titulo_publicacion || `Pedido #${id}`,
      note: 'Mantén el estado alineado con el avance real del pedido para evitar confusión al comprador.',
      confirmText: meta.confirmText,
      cancelText: 'Cancelar'
    });

    if (!ok) return;

    accionEnCurso = true;

    try {
      const resp = await fetch(`${BASE}/api/pedidos/${id}/estado`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ nuevo_estado: estado })
      });

      const json = await resp.json().catch(() => ({}));

      if (await handleAuthFromResponse(resp, json)) return;

      if (!resp.ok || json?.ok === false) {
        await notify(
          'warning',
          'No se pudo actualizar',
          'El pedido mantiene su estado actual',
          json?.mensaje || 'No se pudo actualizar el estado del pedido.',
          {
            htmlExtra: htmlProductNote('Pedido', item.titulo_publicacion || `Pedido #${id}`)
          }
        );
        return;
      }

      await notify(
        'success',
        'Estado actualizado',
        'El avance del pedido fue registrado',
        json?.mensaje || 'El estado del pedido fue actualizado correctamente.',
        {
          htmlExtra: htmlProductNote(
            'Pedido',
            item.titulo_publicacion || `Pedido #${id}`
          )
        }
      );

      await cargarPedidos({ silent: true });
    } finally {
      accionEnCurso = false;
    }
  }


  function ensureDetallePremiumStyles() {
    const ID = 'ev-mpv-detalle-premium-style';
    if (document.getElementById(ID)) return;

    const style = document.createElement('style');
    style.id = ID;
    style.type = 'text/css';
    style.textContent = `
      .ev-mpv-btn-danger-soft:hover,
      .ev-mpv-btn-danger-soft:focus{
        background:linear-gradient(135deg,#DC2626,#B91C1C) !important;
        border-color:#DC2626 !important;
        color:#ffffff !important;
        box-shadow:0 14px 28px rgba(220,38,38,.20) !important;
        transform:translateY(-1px) !important;
      }

      .swal2-popup.ev-mpv-swal-popup-premium{
        background:#ffffff !important;
        background-image:none !important;
      }

      .ev-mpv-modal-detail-v2{
        text-align:left;
        max-width:100%;
      }

      .ev-mpv-modal-hero{
        display:grid;
        grid-template-columns:minmax(180px, 220px) minmax(0, 1fr);
        gap:16px;
        align-items:stretch;
        margin-bottom:14px;
      }

      .ev-mpv-modal-media-card{
        border:1px solid rgba(229,231,235,.94);
        border-radius:24px;
        background:#ffffff;
        box-shadow:0 12px 26px rgba(15,23,42,.06);
        padding:10px;
        display:flex;
        flex-direction:column;
        gap:10px;
        min-width:0;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-media{
        width:100% !important;
        height:172px !important;
        border-radius:18px !important;
        background:#F8FAFC !important;
        box-shadow:none !important;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-media img{
        object-fit:contain !important;
        padding:4px !important;
      }

      .ev-mpv-modal-mini-pills{
        display:flex;
        flex-wrap:wrap;
        gap:6px;
      }

      .ev-mpv-modal-mini-pill{
        display:inline-flex;
        align-items:center;
        gap:6px;
        min-height:30px;
        padding:6px 9px;
        border-radius:999px;
        background:#F8FAFC;
        border:1px solid #E5E7EB;
        color:#334155;
        font-size:12px;
        font-weight:850;
      }

      .ev-mpv-modal-main-card{
        border:1px solid rgba(229,231,235,.94);
        border-radius:24px;
        background:#ffffff;
        box-shadow:0 12px 26px rgba(15,23,42,.05);
        padding:14px;
        min-width:0;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-head{
        margin-bottom:12px !important;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-title{
        font-size:1.18rem !important;
        line-height:1.16 !important;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-grid{
        grid-template-columns:repeat(2, minmax(0,1fr)) !important;
        gap:9px !important;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-item{
        background:#ffffff !important;
        border-color:#E9EEF5 !important;
        min-height:78px;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-item span{
        white-space:normal !important;
        word-break:normal !important;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-item strong{
        white-space:normal !important;
        word-break:normal !important;
        overflow-wrap:break-word !important;
        line-height:1.22 !important;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-stack{
        margin-top:14px !important;
      }

      .ev-mpv-modal-detail-v2 .ev-mpv-modal-stack,
      .ev-mpv-modal-detail-v2 .ev-mpv-modal-note{
        background:#ffffff !important;
        background-image:none !important;
      }

      @media (max-width: 767.98px){
        .ev-mpv-modal-hero{
          grid-template-columns:1fr;
        }

        .ev-mpv-modal-detail-v2 .ev-mpv-modal-media{
          height:210px !important;
        }

        .ev-mpv-modal-detail-v2 .ev-mpv-modal-grid{
          grid-template-columns:repeat(2, minmax(0,1fr)) !important;
        }
      }

      @media (max-width: 480px){
        .ev-mpv-modal-detail-v2 .ev-mpv-modal-grid{
          grid-template-columns:1fr !important;
        }
      }
    `;
    document.head.appendChild(style);
  }

  function buildDetalleHtml(item) {
    const imagen = normalizarUrlImagen(item.imagen_portada_url || item.imagen_portada);
    const badge = badgeEstado(item.estado_actual);
    const tieneProgramacion = String(item.tipo_entrega_raw || '').trim() === 'programada' && !!item.fecha_hora_programada;
    const fechaBase = item.fecha_hora || item.created_at || null;

    return `
      <div class="ev-mpv-modal-detail ev-mpv-modal-detail-v2">
        <div class="ev-mpv-modal-hero">
          <div class="ev-mpv-modal-media-card">
            <div class="ev-mpv-modal-media">
              <img src="${escapeHtml(imagen)}" alt="${escapeHtml(item.titulo_publicacion || 'Pedido')}">
            </div>
            <div class="ev-mpv-modal-mini-pills">
              <span class="ev-mpv-modal-mini-pill"><i class="bi bi-box-seam"></i>Cant. ${escapeHtml(item.cantidad || 0)}</span>
              <span class="ev-mpv-modal-mini-pill"><i class="bi bi-lightning-charge"></i>${escapeHtml(textoEntrega(item))}</span>
            </div>
          </div>

          <div class="ev-mpv-modal-main-card">
            <div class="ev-mpv-modal-head">
              <div>
                <div class="ev-mpv-modal-title">${escapeHtml(item.titulo_publicacion || 'Pedido')}</div>
                <div class="ev-mpv-modal-subtitle">
                  Pedido #${Number(item.codigo_pedido || 0)} · ${escapeHtml(formatFecha(fechaBase))}
                </div>
              </div>
              <span class="${badge.clase}">${escapeHtml(badge.texto)}</span>
            </div>

            <div class="ev-mpv-modal-grid">
              <div class="ev-mpv-modal-item">
                <span>Fecha</span>
                <strong>${escapeHtml(formatFecha(fechaBase))}</strong>
              </div>
              <div class="ev-mpv-modal-item">
                <span>Total</span>
                <strong>S/ ${escapeHtml(formatMoney(item.monto_total || item.total || 0))}</strong>
              </div>
              <div class="ev-mpv-modal-item">
                <span>Comprador</span>
                <strong>${escapeHtml(item.nombre_vecino || 'Vecino')}</strong>
              </div>
              <div class="ev-mpv-modal-item">
                <span>Entrega</span>
                <strong>${escapeHtml(textoEntrega(item))}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="ev-mpv-modal-section">
          ${getLineaEstado(item)}
        </div>

        <div class="ev-mpv-modal-section ev-mpv-modal-stack">
          <div class="ev-mpv-modal-row">
            <span>Dirección</span>
            <strong>${escapeHtml(item.direccion_entrega || '—')}</strong>
          </div>

          ${
            tieneProgramacion
              ? `
              <div class="ev-mpv-modal-row">
                <span>Entrega programada</span>
                <strong>${escapeHtml(formatFecha(item.fecha_hora_programada))}</strong>
              </div>
            `
              : ''
          }

          ${
            Number(item.posicion_cola || 0) > 1 || String(item.estado_actual || '').trim() === 'cola_aceptada'
              ? `
              <div class="ev-mpv-modal-row">
                <span>Posición en cola</span>
                <strong>#${escapeHtml(item.posicion_cola || 0)}</strong>
              </div>
            `
              : ''
          }

          <div class="ev-mpv-modal-row">
            <span>Estado</span>
            <strong>${escapeHtml(estadoLegible(item.estado_actual || ''))}</strong>
          </div>
        </div>

        ${
          String(item.mensaje_comprador || '').trim() !== ''
            ? `
            <div class="ev-mpv-modal-section">
              <div class="ev-mpv-modal-note-title">Mensaje del comprador</div>
              <div class="ev-mpv-modal-note">${escapeHtml(item.mensaje_comprador)}</div>
            </div>
          `
            : ''
        }

        <div class="ev-mpv-modal-section">
          <div class="ev-mpv-modal-note-title">Detalle operativo</div>
          <div class="ev-mpv-modal-note">
            ${escapeHtml(item.motivo_estado || 'Solicitud registrada por comprador.')}
          </div>
        </div>
      </div>
    `;
  }

  async function verDetalle(id) {
    ensureDetallePremiumStyles();
    const item = cachePedidos.get(Number(id || 0));
    if (!window.Swal || !item) return;

    await Swal.fire(swalBaseConfig({
      title: 'Detalle del pedido',
      html: buildDetalleHtml(item),
      width: 860,
      confirmButtonText: 'Cerrar',
      customClass: {
        container: 'ev-mpv-swal-container ev-swal-container',
        popup: 'ev-mpv-swal-popup-premium ev-mpv-swal-popup-detail ev-swal-popup ev-swal-popup-detail',
        title: 'ev-mpv-swal-title ev-swal-title',
        htmlContainer: 'ev-mpv-swal-html ev-swal-html',
        confirmButton: 'ev-mpv-swal-confirm ev-swal-confirm',
        cancelButton: 'ev-mpv-swal-cancel ev-swal-cancel'
      }
    }));
  }

  function detenerCountdownRecojo() {
    if (recojoCountdownTimer) {
      clearInterval(recojoCountdownTimer);
      recojoCountdownTimer = null;
    }
  }

  function actualizarCountdownRecojo() {
    const nodes = Array.from(document.querySelectorAll('[data-recojo-countdown="1"]'));

    if (!nodes.length) {
      detenerCountdownRecojo();
      return;
    }

    let debeRefrescar = false;

    nodes.forEach((node) => {
      const expiraMs = Number(node.dataset.recojoExpiraMs || 0);
      const span = node.querySelector('span');
      const restante = expiraMs > 0 ? Math.max(0, Math.ceil((expiraMs - Date.now()) / 1000)) : 0;

      if (span) span.textContent = formatTiempoCorto(restante);
      if (restante <= 0) debeRefrescar = true;
    });

    if (debeRefrescar && !recojoRefreshProgramado) {
      recojoRefreshProgramado = true;
      window.setTimeout(async () => {
        recojoRefreshProgramado = false;
        if (vistaActiva && document.querySelector('.ev-mpv-page')) {
          await cargarPedidos({ silent: true });
        }
      }, 750);
    }
  }

  function iniciarCountdownRecojo() {
    detenerCountdownRecojo();
    if (!document.querySelector('[data-recojo-countdown="1"]')) return;
    actualizarCountdownRecojo();
    recojoCountdownTimer = window.setInterval(actualizarCountdownRecojo, 1000);
  }

  function detenerPolling() {
    if (pollingTimer) {
      clearInterval(pollingTimer);
      pollingTimer = null;
    }
  }

  function iniciarPolling() {
    detenerPolling();
    pollingTimer = setInterval(() => {
      if (!vistaActiva || document.hidden) return;
      if (!document.querySelector('.ev-mpv-page')) return;
      cargarPedidos({ silent: true });
    }, POLLING_MS);
  }

  function bindEventosTabs() {
    const refs = getRefs();

    refs.tabButtons.forEach((btn) => {
      if (btn.dataset.evBound === '1') return;
      btn.dataset.evBound = '1';

      btn.addEventListener('click', () => {
        showTab(getRefs(), btn.dataset.tab || 'pendientes');
      });
    });
  }

  function bindRefresh() {
    const refs = getRefs();

    if (!refs.btnRefresh || refs.btnRefresh.dataset.evBound === '1') return;
    refs.btnRefresh.dataset.evBound = '1';

    refs.btnRefresh.addEventListener('click', () => {
      cargarPedidos({ silent: true });
    });
  }

  async function manejarClickDocumento(e) {
    const root = document.querySelector('.ev-mpv-page');
    if (!root) return;

    const btn = e.target.closest('[data-action]');
    if (!btn) return;

    const action = btn.dataset.action;
    const id = Number(btn.dataset.id || 0);
    const estado = btn.dataset.estado || '';

    if (!id) return;

    if (action === 'aceptar') {
      await aceptar(id);
      return;
    }

    if (action === 'rechazar') {
      await rechazar(id);
      return;
    }

    if (action === 'estado') {
      await cambiarEstado(id, estado);
      return;
    }

    if (action === 'cancelar-recojo') {
      await cancelarPorNoRecojo(id);
      return;
    }

    if (action === 'calificar') {
      await abrirModalCalificacion(cachePedidos.get(id));
      return;
    }

    if (action === 'detalle') {
      await verDetalle(id);
    }
  }

  document.addEventListener('click', manejarClickDocumento);

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) return;
    if (document.querySelector('.ev-mpv-page')) {
      cargarPedidos({ silent: true });
    }
  });

  document.addEventListener('ev:content-loaded', () => {
    if (document.querySelector('.ev-mpv-page')) {
      initMisPedidosVendedor();
    } else {
      vistaActiva = false;
      detenerPolling();
      detenerCountdownRecojo();
    }
  });

  function initMisPedidosVendedor() {
    const refs = getRefs();

    if (!refs.root) {
      vistaActiva = false;
      detenerPolling();
      detenerCountdownRecojo();
      return;
    }

    ensureSwalStyles();
    ensureRecojoStyles();
    ensureDetallePremiumStyles();

    vistaActiva = true;
    bindEventosTabs();
    bindRefresh();
    showTab(refs, tabActiva || 'pendientes');
    cargarPedidos({ silent: false });
    iniciarPolling();
  }

  window.EVMisPedidosVendedor = {
    init: initMisPedidosVendedor,
    refresh: () => cargarPedidos({ silent: true })
  };

  if (document.querySelector('.ev-mpv-page')) {
    initMisPedidosVendedor();
  }
})();
