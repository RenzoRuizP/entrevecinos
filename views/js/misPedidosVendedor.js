/* views/js/misPedidosVendedor.js */
(function () {
  'use strict';

  if (window.__EV_MIS_PEDIDOS_VENDEDOR_V14__ === true) {
    if (window.EVMisPedidosVendedor && typeof window.EVMisPedidosVendedor.init === 'function') {
      window.EVMisPedidosVendedor.init();
    }
    return;
  }
  window.__EV_MIS_PEDIDOS_VENDEDOR_V14__ = true;

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
      en_punto_entrega: 'En punto de recojo',
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

    if (
      e === 'en_preparacion' ||
      e === 'despachando' ||
      e === 'listo_para_entrega' ||
      e === 'en_camino' ||
      e === 'en_punto_entrega' ||
      e === 'entrega_confirmada_comprador'
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

    if (Number(item.puede_cancelar_vendedor || 0) === 1) {
      acciones.push(`
        <button type="button" class="btn ev-mpv-btn-danger-soft" data-action="estado" data-id="${id}" data-estado="cancelado_vendedor">
          <i class="bi bi-x-octagon me-1"></i>Cancelar pedido
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


  function renderRecojoCountdown(item) {
    const estado = String(item.estado_actual || '').trim();
    if (estado !== 'en_punto_entrega') return '';

    const restantes = Number(item.segundos_recojo_restantes || 0);
    if (restantes > 0) {
      return `<div class="ev-mpv-time-pill"><i class="bi bi-hourglass-split"></i>Tiempo de espera: ${escapeHtml(formatTiempoCorto(restantes))}</div>`;
    }

    return `<div class="ev-mpv-time-pill"><i class="bi bi-exclamation-circle"></i>Tiempo de recojo vencido</div>`;
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
      return `
        <div class="ev-mpv-state-box ev-mpv-state-box-info">
          <div class="ev-mpv-state-title">Pedido en punto de recojo</div>
          <div class="ev-mpv-state-text">
            Espera al comprador hasta 6 minutos. Si no recoge o no responde, podrás cancelar seleccionando un motivo.
          </div>
          ${renderRecojoCountdown(item)}
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


  async function promptCancelacionVendedor(item) {
    if (!window.Swal?.fire) return { isConfirmed: false, value: null };

    const motivos = {
      comprador_no_se_presento: 'El comprador no se presentó',
      comprador_no_responde: 'El comprador no respondió',
      comprador_rechazo_recepcion: 'El comprador rechazó recibir el pedido',
      no_se_pudo_concretar: 'No se pudo concretar la entrega',
      otro: 'Otro motivo'
    };

    return Swal.fire(swalBaseConfig({
      title: 'Cancelar pedido',
      html: htmlMessage(
        'warning',
        'Selecciona el motivo de cancelación',
        'Esta acción cerrará el pedido y liberará tu turno de atención.',
        htmlProductNote('Pedido', item?.titulo_publicacion || 'Pedido seleccionado')
      ),
      input: 'select',
      inputOptions: motivos,
      inputPlaceholder: 'Selecciona un motivo',
      showCancelButton: true,
      confirmButtonText: 'Continuar',
      cancelButtonText: 'Volver',
      preConfirm: (value) => {
        const clave = String(value || '').trim();
        if (!clave) {
          Swal.showValidationMessage('Debes seleccionar un motivo.');
          return false;
        }
        return { clave, detalle: '' };
      }
    })).then(async (r) => {
      if (!r.isConfirmed || !r.value) return r;
      if (r.value.clave !== 'otro') return r;

      const detalle = await Swal.fire(swalBaseConfig({
        title: 'Detalle del motivo',
        html: htmlMessage(
          'warning',
          'Describe brevemente lo ocurrido',
          'Este detalle quedará registrado en el historial del pedido.'
        ),
        input: 'textarea',
        inputPlaceholder: 'Escribe el detalle...',
        inputAttributes: { maxlength: '500' },
        showCancelButton: true,
        confirmButtonText: 'Cancelar pedido',
        cancelButtonText: 'Volver',
        preConfirm: (value) => {
          const txt = String(value || '').trim();
          if (!txt) {
            Swal.showValidationMessage('Debes ingresar un detalle.');
            return false;
          }
          return txt;
        }
      }));

      if (!detalle.isConfirmed) return { isConfirmed: false, value: null };
      return { isConfirmed: true, value: { clave: 'otro', detalle: detalle.value || '' } };
    });
  }

  async function cambiarEstado(id, estado) {
    if (accionEnCurso) return;
    const item = cachePedidos.get(Number(id || 0));
    if (!item) return;

    const meta = obtenerMetaCambioEstado(estado);
    let motivoCancelacion = null;

    if (String(estado || '').trim() === 'cancelado_vendedor') {
      const motivo = await promptCancelacionVendedor(item);
      if (!motivo.isConfirmed || !motivo.value) return;
      motivoCancelacion = motivo.value;
    }

    const ok = String(estado || '').trim() === 'cancelado_vendedor' ? true : await confirmAction({
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
        body: JSON.stringify({
          nuevo_estado: estado,
          motivo_cancelacion_clave: motivoCancelacion?.clave || '',
          motivo_cancelacion_detalle: motivoCancelacion?.detalle || ''
        })
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

  function buildDetalleHtml(item) {
    const imagen = normalizarUrlImagen(item.imagen_portada_url || item.imagen_portada);
    const badge = badgeEstado(item.estado_actual);
    const tieneProgramacion = String(item.tipo_entrega_raw || '').trim() === 'programada' && !!item.fecha_hora_programada;

    return `
      <div class="ev-mpv-modal-detail">
        <div class="ev-mpv-modal-top">
          <div class="ev-mpv-modal-media">
            <img src="${escapeHtml(imagen)}" alt="${escapeHtml(item.titulo_publicacion || 'Pedido')}">
          </div>

          <div class="ev-mpv-modal-main">
            <div class="ev-mpv-modal-head">
              <div>
                <div class="ev-mpv-modal-title">${escapeHtml(item.titulo_publicacion || 'Pedido')}</div>
                <div class="ev-mpv-modal-subtitle">
                  Pedido #${Number(item.codigo_pedido || 0)} · ${escapeHtml(formatFecha(item.fecha_hora || item.created_at || null))}
                </div>
              </div>
              <span class="${badge.clase}">${escapeHtml(badge.texto)}</span>
            </div>

            <div class="ev-mpv-modal-grid">
              <div class="ev-mpv-modal-item">
                <span>Fecha</span>
                <strong>${escapeHtml(formatFecha(item.fecha_hora || item.created_at || null))}</strong>
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
            ${escapeHtml(item.motivo_estado || 'Estado actualizado del pedido.')}
          </div>
        </div>
      </div>
    `;
  }

  async function verDetalle(id) {
    const item = cachePedidos.get(Number(id || 0));
    if (!window.Swal || !item) return;

    await Swal.fire(swalBaseConfig({
      title: 'Detalle del pedido',
      html: buildDetalleHtml(item),
      width: 880,
      confirmButtonText: 'Cerrar'
    }));
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
    }
  });

  function initMisPedidosVendedor() {
    const refs = getRefs();

    if (!refs.root) {
      vistaActiva = false;
      detenerPolling();
      return;
    }

    ensureSwalStyles();

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
