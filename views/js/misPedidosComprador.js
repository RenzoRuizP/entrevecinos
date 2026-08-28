/* views/js/misPedidosComprador.js */
(function () {
  'use strict';

  if (window.__EV_MIS_PEDIDOS_COMPRADOR_V6__ === true) {
    if (window.EVMisPedidosComprador && typeof window.EVMisPedidosComprador.init === 'function') {
      window.EVMisPedidosComprador.init();
    }
    return;
  }
  window.__EV_MIS_PEDIDOS_COMPRADOR_V6__ = true;

  const BASE = (window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/+$/, '');
  const PLACEHOLDER = `${BASE}/public/img/placeholder-ev.png`;
  const POLLING_MS = 8000;
  const NOTIF_ORDER_TARGET_KEY = 'ev_notificacion_pedido_destino';

  let tabActiva = 'pendientes';
  let cachePedidos = new Map();
  let pollingTimer = null;
  let recojoCompradorTimer = null;
  let cancelacionCompradorTimer = null;
  let vistaActiva = false;
  let cargando = false;

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


  function formatTiempoCorto(segundos) {
    const s = Math.max(0, Number(segundos || 0));
    const min = Math.floor(s / 60);
    const sec = s % 60;
    return `${String(min).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
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

  function normalizarUrlImagen(url) {
    const raw = String(url || '').trim();
    return raw !== '' ? raw : PLACEHOLDER;
  }

  function textoEntrega(item) {
    const tipoRaw = String(item.tipo_entrega_raw || item.tipo_entrega || '').trim().toLowerCase();
    if (tipoRaw === 'programada' || tipoRaw === 'programado') return 'Programada';
    return 'Inmediata';
  }


  function esEstadoPendiente(estado) {
    return String(estado || '').trim() === 'pendiente_vendedor';
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
    return [
      'entregado_vendedor'
    ].includes(String(estado || '').trim());
  }

  function esEstadoProceso(estado) {
    return [
      'en_preparacion',
      'despachando',
      'listo_para_entrega',
      'en_camino',
      'en_punto_entrega'
    ].includes(String(estado || '').trim());
  }

  function estadoLegible(estado) {
    const mapa = {
      pendiente_vendedor: 'Pendiente',
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
      return { texto: 'Pendiente', clase: 'ev-mpc-badge ev-mpc-badge-pendiente' };
    }


    if (esEstadoNegativo(e)) {
      return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-negative' };
    }

    if (esEstadoInfo(e)) {
      return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-info' };
    }

    if (e === 'entrega_confirmada_comprador') {
      return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-final' };
    }

    if (
      e === 'en_preparacion' ||
      e === 'despachando' ||
      e === 'listo_para_entrega' ||
      e === 'en_camino' ||
      e === 'en_punto_entrega'
    ) {
      return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-proceso' };
    }

    return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-final' };
  }

  async function alerta(icon, title, text) {
    if (!window.Swal?.fire) {
      alert(title ? `${title}\n\n${text}` : text);
      return;
    }

    return Swal.fire({
      icon,
      title,
      text,
      confirmButtonText: 'Aceptar',
      confirmButtonColor: '#EA7C12'
    });
  }

  function ensureRecojoCompradorStyles() {
    const ID = 'ev-mpc-recojo-countdown-style';
    if (document.getElementById(ID)) return;

    const style = document.createElement('style');
    style.id = ID;
    style.type = 'text/css';
    style.textContent = `
      .ev-mpc-recojo-box{margin-top:11px;border-radius:20px;padding:12px 13px;border:1px solid rgba(234,124,18,.24);background:linear-gradient(180deg,#FFF7ED 0%,#FFFDF9 100%);box-shadow:0 10px 22px rgba(234,124,18,.07);}
      .ev-mpc-recojo-box.is-expired{border-color:#FECACA;background:linear-gradient(180deg,#FEF2F2 0%,#FFF7F7 100%);}
      .ev-mpc-recojo-head{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:7px;}
      .ev-mpc-recojo-title{display:flex;align-items:center;gap:8px;color:#0F592F;font-size:.90rem;font-weight:900;letter-spacing:-.01em;}
      .ev-mpc-recojo-box.is-expired .ev-mpc-recojo-title{color:#991B1B;}
      .ev-mpc-recojo-timer{display:inline-flex;align-items:center;gap:7px;border-radius:999px;padding:7px 11px;background:#fff;border:1px solid #FDBA74;color:#9A3412;font-size:.83rem;font-weight:950;font-variant-numeric:tabular-nums;box-shadow:0 7px 14px rgba(15,23,42,.05);}
      .ev-mpc-recojo-box.is-expired .ev-mpc-recojo-timer{border-color:#FCA5A5;color:#991B1B;}
      .ev-mpc-recojo-text{color:#475569;font-size:.84rem;line-height:1.45;}
      .ev-mpc-recojo-box.is-expired .ev-mpc-recojo-text{color:#7F1D1D;}
    `;
    document.head.appendChild(style);
  }

  async function manejarRespuestaSeguridad(resp, json) {
    const error = String(json?.error || '').trim();

    if (resp.status === 401) {
      await alerta(
        'info',
        'Sesión finalizada',
        json?.mensaje || 'Tu sesión expiró. Vuelve a iniciar sesión.'
      );
      window.location.href = json?.redirect || `${BASE}/login`;
      return true;
    }

    if (resp.status === 403 && error === 'CUENTA_BLOQUEADA') {
      await alerta(
        'warning',
        'Cuenta bloqueada',
        json?.mensaje || 'Tu cuenta fue bloqueada. Debes volver a iniciar sesión.'
      );
      window.location.href = json?.redirect || `${BASE}/login`;
      return true;
    }

    if (resp.status === 409 && error === 'CUENTA_OBSERVADA') {
      await alerta(
        'warning',
        'Cuenta observada',
        json?.mensaje || 'Tu cuenta está observada. Debes revisar tu estado.'
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

  async function obtenerCalificacionPendientePedido(codigoPedido) {
    const id = Number(codigoPedido || 0);
    if (!id) return null;

    try {
      const resp = await fetch(`${BASE}/api/calificaciones/pedido/${encodeURIComponent(id)}`, {
        method: 'GET',
        credentials: 'include',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const json = await resp.json().catch(() => ({}));
      if (await manejarRespuestaSeguridad(resp, json)) return null;

      if (!resp.ok || json?.ok === false) return null;

      const pendiente = json?.data || null;
      if (!pendiente || Number(pendiente.codigo_calificacion || 0) <= 0) return null;
      if (String(pendiente.estado || '').trim() !== 'pendiente') return null;

      return pendiente;
    } catch (e) {
      console.warn('[MIS_PEDIDOS_COMPRADOR][CALIFICACION_PENDIENTE]', e);
      return null;
    }
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
      .ev-mpc-btn-rating{background:linear-gradient(135deg,#0F592F,#16A34A);border:none;color:#fff;box-shadow:0 12px 24px rgba(22,163,74,.20);border-radius:14px;padding:.74rem .98rem;font-weight:850;font-size:.89rem;transition:transform .16s ease,filter .16s ease;}
      .ev-mpc-btn-rating:hover{transform:translateY(-1px);filter:brightness(1.02);color:#fff;}
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

    const rolCalificado = String(calificacion.rol_calificado || 'vendedor').trim();
    const nombreCalificado = String(calificacion.nombre_calificado || item?.nombre_vendedor || item?.nombre_vecino || 'Vecino').trim();
    const tituloPedido = String(calificacion.titulo_publicacion || item?.titulo_publicacion || item?.titulo_producto || 'Pedido EV').trim();
    const titulo = rolCalificado === 'vendedor' ? 'Califica al vendedor' : 'Califica al comprador';
    const subtitulo = rolCalificado === 'vendedor'
      ? 'Tu opinión ayuda a que otros vecinos compren con más confianza.'
      : 'Tu opinión ayuda a mantener una comunidad seria y respetuosa.';
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

    const result = await Swal.fire({
      title: titulo,
      html,
      width: 620,
      showCancelButton: true,
      confirmButtonText: 'Enviar calificación',
      cancelButtonText: 'Ahora no',
      buttonsStyling: false,
      customClass: {
        popup: 'ev-mpc-swal-popup ev-mpc-swal-popup-premium',
        title: 'ev-mpc-swal-title',
        htmlContainer: 'ev-mpc-swal-html',
        confirmButton: 'ev-mpc-swal-confirm',
        cancelButton: 'ev-mpc-swal-cancel'
      },
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
    });

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
    if (await manejarRespuestaSeguridad(resp, json)) return;

    if (!resp.ok || json?.ok === false) {
      await alerta('error', 'No se pudo calificar', json?.mensaje || 'No se pudo registrar la calificación.');
      return;
    }

    await alerta('success', 'Calificación enviada', json?.mensaje || 'Gracias por ayudar a construir confianza en Entre Vecinos.');
    await cargarPedidos({ silent: true });
  }

  function getRefs() {
    return {
      root: document.querySelector('.ev-mpc-page'),

      countPendientes: document.getElementById('mpcCountPendientes'),
      countProceso: document.getElementById('mpcCountProceso'),
      countFinalizados: document.getElementById('mpcCountFinalizados'),

      badgePendientes: document.getElementById('mpcBadgePendientes'),
      badgeProceso: document.getElementById('mpcBadgeProceso'),
      badgeFinalizados: document.getElementById('mpcBadgeFinalizados'),

      tabButtons: Array.from(document.querySelectorAll('.ev-mpc-tab')),
      tabPendientes: document.getElementById('mpcTabPendientes'),
      tabProceso: document.getElementById('mpcTabProceso'),
      tabFinalizados: document.getElementById('mpcTabFinalizados'),

      listaPendientes: document.getElementById('mpcListaPendientes'),
      listaProceso: document.getElementById('mpcListaProceso'),
      listaFinalizados: document.getElementById('mpcListaFinalizados'),

      emptyPendientes: document.getElementById('mpcEmptyPendientes'),
      emptyProceso: document.getElementById('mpcEmptyProceso'),
      emptyFinalizados: document.getElementById('mpcEmptyFinalizados'),

      errorBox: document.getElementById('mpcError'),
      btnRefresh: document.getElementById('btnRefrescarMisPedidosComprador')
    };
  }

  function showTab(refs, tab) {
    tabActiva = tab;

    refs.tabButtons.forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.tab === tab);
    });

    refs.tabPendientes?.classList.toggle('d-none', tab !== 'pendientes');
    refs.tabProceso?.classList.toggle('d-none', tab !== 'proceso');
    refs.tabFinalizados?.classList.toggle('d-none', tab !== 'finalizados');
  }

  function limpiarListas(refs) {
    if (refs.listaPendientes) refs.listaPendientes.innerHTML = '';
    if (refs.listaProceso) refs.listaProceso.innerHTML = '';
    if (refs.listaFinalizados) refs.listaFinalizados.innerHTML = '';
  }

  function getFlujoEstados(item) {
    const requierePreparacion = Number(item.requiere_preparacion || 0) === 1;
    return requierePreparacion
      ? ['pendiente_vendedor', 'en_preparacion', 'listo_para_entrega', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador']
      : ['pendiente_vendedor', 'despachando', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador'];
  }

  function renderProgresoCompacto(item) {
    const estado = String(item.estado_actual || '').trim();

    if (esEstadoNegativo(estado)) {
      const negative = esEstadoNegativo(estado);
      return `
        <div class="ev-mpc-progress-compact ${negative ? 'is-negative' : 'is-special'}">
          <div class="ev-mpc-progress-current">
            <span class="ev-mpc-progress-dot"></span>
            <span>${escapeHtml(estadoLegible(estado))}</span>
          </div>
        </div>
      `;
    }

    const flujo = getFlujoEstados(item);
    const indexRaw = flujo.indexOf(estado);
    const index = indexRaw >= 0 ? indexRaw : 0;
    const total = Math.max(1, flujo.length);
    const porcentaje = total <= 1 ? 100 : Math.round((index / (total - 1)) * 100);

    return `
      <div class="ev-mpc-progress-compact" role="group" aria-label="Progreso del pedido">
        <div class="ev-mpc-progress-head">
          <span class="ev-mpc-progress-caption">Paso ${index + 1} de ${total}</span>
          <span class="ev-mpc-progress-current"><span class="ev-mpc-progress-dot"></span>${escapeHtml(estadoLegible(estado || flujo[index]))}</span>
        </div>
        <div class="ev-mpc-progress-track" aria-hidden="true"><span style="width:${porcentaje}%"></span></div>
        <div class="ev-mpc-progress-ends" aria-hidden="true"><span>Inicio</span><span>Entrega</span></div>
      </div>
    `;
  }

  function getLineaEstado(item) {
    const estado = String(item.estado_actual || '').trim();


    if (esEstadoNegativo(estado)) {
      return `
        <div class="ev-mpc-stepper ev-mpc-stepper-final ev-mpc-stepper-detail">
          <div class="ev-mpc-step is-final is-negative">
            <span class="ev-mpc-step-dot"></span>
            <span class="ev-mpc-step-text">${escapeHtml(estadoLegible(estado))}</span>
          </div>
        </div>
      `;
    }

    const flujo = getFlujoEstados(item);
    const currentIndex = flujo.indexOf(estado);

    return `
      <div class="ev-mpc-stepper ev-mpc-stepper-detail">
        ${flujo.map((step, index) => {
          const isDone = currentIndex > index;
          const isCurrent = currentIndex === index;
          return `
            <div class="ev-mpc-step ${isDone ? 'is-done' : ''} ${isCurrent ? 'is-current' : ''}">
              <span class="ev-mpc-step-dot">${isDone ? '<i class="bi bi-check2"></i>' : ''}</span>
              <span class="ev-mpc-step-text">${escapeHtml(estadoLegible(step))}</span>
            </div>
          `;
        }).join('')}
      </div>
    `;
  }

  function renderResumenEstado(item) {
    const estado = String(item.estado_actual || '').trim();
    if (estado === 'pendiente_vendedor') {
      return `
        <div class="ev-mpc-state-box ev-mpc-state-box-pending">
          <div class="ev-mpc-state-title">Solicitud enviada</div>
          <div class="ev-mpc-state-text">
            Tu pedido fue enviado correctamente y está esperando respuesta del vendedor.
          </div>
        </div>
      `;
    }


    if (estado === 'en_punto_entrega') {
      const segundos = segundosRecojoRestantesItem(item);
      const expirado = segundos <= 0;

      return `
        <div class="ev-mpc-recojo-box ${expirado ? 'is-expired' : ''}">
          <div class="ev-mpc-recojo-head">
            <div class="ev-mpc-recojo-title">
              <i class="bi ${expirado ? 'bi-exclamation-triangle' : 'bi-geo-alt'}"></i>
              ${expirado ? 'Tiempo de recepción vencido' : 'Tu pedido llegó al punto de entrega'}
            </div>
            <div
              class="ev-mpc-recojo-timer"
              data-recojo-comprador-countdown="1"
              data-recojo-comprador-expira-ms="${Date.now() + (segundos * 1000)}">
              <i class="bi bi-clock-history"></i>
              <span>${escapeHtml(formatTiempoCorto(segundos))}</span>
            </div>
          </div>
          <div class="ev-mpc-recojo-text">
            ${
              expirado
                ? 'El tiempo de recepción ya venció. Si no recibiste el pedido, espera la actualización del vendedor o comunícate con soporte si corresponde.'
                : 'Recuerda recibir tu pedido dentro de los próximos 6 minutos. Cuando el vendedor marque la entrega, podrás confirmar la recepción.'
            }
          </div>
        </div>
      `;
    }

    if (estado === 'entregado_vendedor') {
      return `
        <div class="ev-mpc-state-box ev-mpc-state-box-info">
          <div class="ev-mpc-state-title">Pedido marcado como entregado</div>
          <div class="ev-mpc-state-text">
            Verifica que recibiste correctamente tu pedido antes de confirmar la entrega.
          </div>
        </div>
      `;
    }

    if (esEstadoNegativo(estado)) {
      return `
        <div class="ev-mpc-state-box ev-mpc-state-box-negative">
          <div class="ev-mpc-state-title">${escapeHtml(estadoLegible(estado))}</div>
          <div class="ev-mpc-state-text">
            ${escapeHtml(item.motivo_estado || item.mensaje_estado || 'Este pedido se cerró sin concretarse.')}
          </div>
        </div>
      `;
    }

    if (estado === 'entrega_confirmada_comprador') {
      return `
        <div class="ev-mpc-state-box ev-mpc-state-box-final">
          <div class="ev-mpc-state-title">Entrega confirmada</div>
          <div class="ev-mpc-state-text">
            ${escapeHtml(item.motivo_estado || item.mensaje_estado || 'Confirmaste correctamente la recepción del pedido.')}
          </div>
        </div>
      `;
    }

    return `
      <div class="ev-mpc-state-box ev-mpc-state-box-process">
        <div class="ev-mpc-state-title">Pedido en avance</div>
        <div class="ev-mpc-state-text">
          ${escapeHtml(item.motivo_estado || item.mensaje_estado || 'Tu pedido continúa avanzando.')}
        </div>
      </div>
    `;
  }

  function renderQuickPills(item) {
    const pills = [
      `
      <span class="ev-mpc-pill">
        <i class="bi bi-box-seam"></i>
        Cant. ${escapeHtml(item.cantidad || 0)}
      </span>
      `,
      `
      <span class="ev-mpc-pill">
        <i class="bi bi-lightning-charge"></i>
        ${escapeHtml(textoEntrega(item))}
      </span>
      `
    ];


    return pills.join('');
  }

  function renderFlujo(item) {
    if (esEstadoNegativo(item.estado_actual)) {
      return `
        <div class="ev-mpc-section-title">
          <i class="bi bi-diagram-3"></i>
          Estado del pedido
        </div>
        ${renderProgresoCompacto(item)}
      `;
    }

    return `
      <div class="ev-mpc-section-title">
        <i class="bi bi-diagram-3"></i>
        Flujo del pedido
      </div>
      ${renderProgresoCompacto(item)}
    `;
  }

  function renderCard(item) {
    const badge = badgeEstado(item.estado_actual);
    const imagen = normalizarUrlImagen(item.imagen_portada_url || item.imagen_portada);
    const acciones = [];
    const estado = String(item.estado_actual || '').trim();
    const tieneProgramacion = String(item.tipo_entrega_raw || '').trim() === 'programada' && !!item.fecha_hora_programada;
    const puedeCancelar = Number(item.puede_cancelar || 0) === 1;

    if (estado === 'entregado_vendedor') {
      acciones.push(`
        <button type="button" class="btn ev-mpc-btn-primary" data-action="confirmar-entrega" data-id="${Number(item.codigo_pedido || 0)}">
          <i class="bi bi-check2-circle me-1"></i>Confirmar entrega
        </button>
      `);
    }


    if (estado === 'pendiente_vendedor') {
      const segundosParaCancelar = Math.max(0, Number(item.segundos_para_cancelar_restantes || 0));
      const habilitaEnMs = Date.now() + (segundosParaCancelar * 1000);

      acciones.push(`
        <button
          type="button"
          class="btn ev-mpc-btn-outline ${puedeCancelar ? '' : 'd-none'}"
          data-action="cancelar-solicitud"
          data-id="${Number(item.codigo_pedido || 0)}"
          data-cancel-enable-ms="${habilitaEnMs}">
          <i class="bi bi-x-circle me-1"></i>Cancelar
        </button>
      `);
    } else if (puedeCancelar) {
      acciones.push(`
        <button type="button" class="btn ev-mpc-btn-outline" data-action="cancelar-solicitud" data-id="${Number(item.codigo_pedido || 0)}">
          <i class="bi bi-x-circle me-1"></i>Cancelar
        </button>
      `);
    }

    const calificacionPendiente = obtenerCalificacionPendiente(item);

    if (calificacionPendiente) {
      acciones.push(`
        <button type="button" class="btn ev-mpc-btn-rating" data-action="calificar" data-id="${Number(item.codigo_pedido || 0)}">
          <i class="bi bi-star-fill me-1"></i>Calificar vendedor
        </button>
      `);
    }

    acciones.push(`
      <button type="button" class="btn ev-mpc-btn-detail" data-action="detalle" data-id="${Number(item.codigo_pedido || 0)}">
        <i class="bi bi-eye me-1"></i>Ver detalle
      </button>
    `);

    return `
      <article class="ev-mpc-order" data-id="${Number(item.codigo_pedido || 0)}">
        <div class="ev-mpc-order-top">
          <div class="ev-mpc-order-media">
            <img src="${escapeHtml(imagen)}" alt="${escapeHtml(item.titulo_publicacion || 'Pedido')}">
          </div>

          <div class="ev-mpc-order-head">
            <div class="ev-mpc-order-head-main">
              <div class="ev-mpc-order-title">${escapeHtml(item.titulo_publicacion || 'Pedido')}</div>
              <div class="ev-mpc-order-meta">
                Pedido #${Number(item.codigo_pedido || 0)} · ${escapeHtml(formatFecha(item.fecha_hora || item.created_at || null))}
              </div>
            </div>

            <div class="ev-mpc-order-tags" aria-label="Resumen del pedido">
              <span class="${badge.clase}">${escapeHtml(badge.texto)}</span>
              ${renderQuickPills(item)}
            </div>
          </div>

          <div class="ev-mpc-order-top-data">
            <div class="ev-mpc-order-data">
              <div class="ev-mpc-data-box ev-mpc-data-box-date">
                <span>Fecha</span>
                <strong>${escapeHtml(formatFecha(item.fecha_hora || item.created_at || null))}</strong>
              </div>

              <div class="ev-mpc-data-box ev-mpc-data-box-total">
                <span>Total</span>
                <strong>S/ ${escapeHtml(formatMoney(item.monto_total || item.total || 0))}</strong>
              </div>

              <div class="ev-mpc-data-box ev-mpc-data-box-seller">
                <span>Vendedor</span>
                <strong>${escapeHtml(item.nombre_vecino || item.nombre_vendedor || 'Vecino')}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="ev-mpc-order-body">
          ${renderFlujo(item)}

          <div class="ev-mpc-info-card">
            <div class="ev-mpc-line">
              <span class="ev-mpc-line-label">Dirección</span>
              <span class="ev-mpc-line-value">${escapeHtml(item.direccion_entrega || '—')}</span>
            </div>

            ${
              tieneProgramacion
                ? `
                <div class="ev-mpc-line">
                  <span class="ev-mpc-line-label">Entrega programada</span>
                  <span class="ev-mpc-line-value">${escapeHtml(formatFecha(item.fecha_hora_programada))}</span>
                </div>
              `
                : ''
            }


            ${
              String(item.mensaje_comprador || '').trim() !== ''
                ? `
                <div class="ev-mpc-note">
                  <span class="ev-mpc-note-label">Tu mensaje</span>
                  <div class="ev-mpc-note-text">${escapeHtml(item.mensaje_comprador)}</div>
                </div>
              `
                : ''
            }
          </div>

          ${renderResumenEstado(item)}

          <div class="ev-mpc-actions">
            ${acciones.join('')}
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

  async function fetchPedidos() {
    const resp = await fetch(`${BASE}/api/pedidos/mis-comprador`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    });

    const json = await resp.json().catch(() => ({}));

    if (await manejarRespuestaSeguridad(resp, json)) {
      return null;
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


  function leerPedidoDestinoNotificacion() {
    try {
      const raw = sessionStorage.getItem(NOTIF_ORDER_TARGET_KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      const codigoPedido = Number(data?.codigo_pedido || 0);
      const rol = String(data?.rol || '').trim().toLowerCase();
      const createdAt = Number(data?.created_at || 0);
      const age = Date.now() - createdAt;

      if (codigoPedido <= 0 || (rol && rol !== 'comprador') || age < 0 || age > 5 * 60 * 1000) {
        sessionStorage.removeItem(NOTIF_ORDER_TARGET_KEY);
        return null;
      }

      return { codigo_pedido: codigoPedido, rol: 'comprador' };
    } catch (_) {
      return null;
    }
  }

  function prepararTabDestinoNotificacion(data) {
    const pending = leerPedidoDestinoNotificacion();
    if (!pending) return null;

    const id = Number(pending.codigo_pedido || 0);
    const grupos = [
      ['pendientes', 'pendientes'],
      ['en_proceso', 'proceso'],
      ['finalizados', 'finalizados']
    ];

    for (const [grupo, tab] of grupos) {
      const items = Array.isArray(data?.[grupo]) ? data[grupo] : [];
      if (items.some((item) => Number(item?.codigo_pedido || 0) === id)) {
        tabActiva = tab;
        return pending;
      }
    }

    return pending;
  }

  function enfocarPedidoDestinoNotificacion() {
    const pending = leerPedidoDestinoNotificacion();
    if (!pending) return false;

    const id = Number(pending.codigo_pedido || 0);
    const card = document.querySelector(`.ev-mpc-order[data-id="${id}"]`);
    if (!card) return false;

    sessionStorage.removeItem(NOTIF_ORDER_TARGET_KEY);
    card.classList.add('is-notification-target');
    card.setAttribute('tabindex', '-1');

    window.requestAnimationFrame(() => {
      card.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
      try { card.focus({ preventScroll: true }); } catch (_) {}
    });

    window.setTimeout(() => {
      card.classList.remove('is-notification-target');
    }, 5200);

    return true;
  }

  async function cargarPedidos(opciones = {}) {
    const refs = getRefs();
    if (!refs.root || cargando) return;

    cargando = true;

    try {
      if (!opciones.silent) {
        refs.errorBox?.classList.add('d-none');
      }

      const data = await fetchPedidos();
      if (data === null) return;

      refrescarCache(data);
      prepararTabDestinoNotificacion(data);

      const pendientes = Array.isArray(data.pendientes) ? data.pendientes : [];
      const proceso = Array.isArray(data.en_proceso) ? data.en_proceso : [];
      const finalizados = Array.isArray(data.finalizados) ? data.finalizados : [];

      if (refs.countPendientes) refs.countPendientes.textContent = String(pendientes.length);
      if (refs.countProceso) refs.countProceso.textContent = String(proceso.length);
      if (refs.countFinalizados) refs.countFinalizados.textContent = String(finalizados.length);

      if (refs.badgePendientes) refs.badgePendientes.textContent = String(pendientes.length);
      if (refs.badgeProceso) refs.badgeProceso.textContent = String(proceso.length);
      if (refs.badgeFinalizados) refs.badgeFinalizados.textContent = String(finalizados.length);

      pintarGrupo(pendientes, refs.listaPendientes, refs.emptyPendientes);
      pintarGrupo(proceso, refs.listaProceso, refs.emptyProceso);
      pintarGrupo(finalizados, refs.listaFinalizados, refs.emptyFinalizados);

      showTab(refs, tabActiva);
      window.setTimeout(enfocarPedidoDestinoNotificacion, 90);
    } catch (e) {
      console.error('[MIS_PEDIDOS_COMPRADOR]', e);
      limpiarListas(refs);
      refs.errorBox?.classList.remove('d-none');
      refs.emptyPendientes?.classList.add('d-none');
      refs.emptyProceso?.classList.add('d-none');
      refs.emptyFinalizados?.classList.add('d-none');
    } finally {
      cargando = false;
    }
  }

  async function confirmarEntrega(codigoPedido) {
    if (!window.Swal) return;

    const r = await Swal.fire({
      icon: 'question',
      title: 'Confirmar entrega',
      text: '¿Confirmas que recibiste correctamente este pedido?',
      showCancelButton: true,
      confirmButtonText: 'Sí, confirmar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12'
    });

    if (!r.isConfirmed) return;

    const resp = await fetch(`${BASE}/api/pedidos/${codigoPedido}/confirmar-entrega`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({})
    });

    const json = await resp.json().catch(() => ({}));

    if (await manejarRespuestaSeguridad(resp, json)) return;

    if (!resp.ok || json?.ok === false) {
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo confirmar',
        text: json?.mensaje || 'No se pudo confirmar la entrega.',
        confirmButtonColor: '#EA7C12'
      });
      await cargarPedidos({ silent: true });
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
      pendiente = await obtenerCalificacionPendientePedido(codigoPedido);
    }

    if (pendiente && Number(pendiente.codigo_calificacion || 0) > 0) {
      const item = cachePedidos.get(Number(codigoPedido || 0)) || {
        codigo_pedido: codigoPedido,
        titulo_publicacion: pendiente.titulo_publicacion || 'Pedido EV',
        nombre_vendedor: pendiente.nombre_calificado || 'Vecino'
      };

      await abrirModalCalificacion(item, pendiente);
    }

    tabActiva = 'finalizados';
    await cargarPedidos({ silent: true });
  }

  async function cancelarSolicitud(codigoPedido) {
    if (!window.Swal) return;

    const r = await Swal.fire({
      title: 'Cancelar solicitud',
      input: 'textarea',
      inputLabel: 'Motivo de cancelación',
      inputPlaceholder: 'Escribe el motivo de la cancelación...',
      showCancelButton: true,
      confirmButtonText: 'Cancelar solicitud',
      cancelButtonText: 'Volver',
      confirmButtonColor: '#EA7C12',
      preConfirm: (v) => {
        return String(v || '').trim() || 'Solicitud cancelada por el comprador.';
      }
    });

    if (!r.isConfirmed) return;

    const resp = await fetch(`${BASE}/api/pedidos/${codigoPedido}/cancelar`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ motivo_cancelacion: r.value || '' })
    });

    const json = await resp.json().catch(() => ({}));

    if (await manejarRespuestaSeguridad(resp, json)) return;

    if (!resp.ok || json?.ok === false) {
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo cancelar',
        text: json?.mensaje || 'No se pudo cancelar la solicitud.',
        confirmButtonColor: '#EA7C12'
      });
      await cargarPedidos({ silent: true });
      return;
    }

    const data = json?.data || {};
    const tuvoDebito = Number(data.descuento_billetera_aplicado || 0) === 1;
    const devolvio = Number(data.devolucion_billetera_aplicada || 0) === 1;
    const montoDevuelto = Number(data.monto_descontado_billetera || 0);
    const textoResultado = tuvoDebito && devolvio && montoDevuelto > 0
      ? `Tu solicitud fue cancelada correctamente. Se devolvió S/ ${formatMoney(montoDevuelto)} a tu billetera y el movimiento quedó registrado.`
      : (json?.mensaje || 'Tu solicitud fue cancelada correctamente.');

    await Swal.fire({
      icon: 'success',
      title: 'Solicitud cancelada',
      text: textoResultado,
      confirmButtonText: 'Aceptar',
      confirmButtonColor: '#EA7C12'
    });

    await cargarPedidos({ silent: true });
  }


  function ensureCompradorPremiumStyles() {
    const ID = 'ev-mpc-detalle-premium-style';
    if (document.getElementById(ID)) return;

    const style = document.createElement('style');
    style.id = ID;
    style.type = 'text/css';
    style.textContent = `
      .swal2-popup.ev-mpc-swal-popup,
      .swal2-popup.ev-mpc-swal-popup-premium{
        border-radius:28px !important;
        padding:28px 24px 22px !important;
        border:1px solid rgba(229,231,235,.96) !important;
        background:#ffffff !important;
        background-image:none !important;
        box-shadow:0 30px 72px rgba(15,23,42,.20), 0 10px 24px rgba(15,23,42,.08) !important;
        overflow:hidden !important;
      }

      .swal2-popup.ev-mpc-swal-popup::before,
      .swal2-popup.ev-mpc-swal-popup-premium::before{
        content:'';
        position:absolute;
        inset:0 0 auto 0;
        height:5px;
        background:linear-gradient(90deg,#0F592F 0%,#16A34A 58%,#EA7C12 100%);
      }

      .swal2-title.ev-mpc-swal-title{
        color:#0F592F !important;
        font-weight:900 !important;
        letter-spacing:-.03em !important;
        font-size:clamp(1.72rem,2.6vw,2.18rem) !important;
        line-height:1.08 !important;
        margin:0 0 8px 0 !important;
      }

      .swal2-html-container.ev-mpc-swal-html{
        color:#6B7280 !important;
        font-size:.98rem !important;
        line-height:1.6 !important;
        margin:0 !important;
      }

      .swal2-confirm.ev-mpc-swal-confirm{
        background:linear-gradient(135deg,#EA7C12,#F59E0B) !important;
        color:#fff !important;
        border:0 !important;
        border-radius:15px !important;
        min-width:150px !important;
        padding:13px 24px !important;
        font-weight:900 !important;
        box-shadow:0 14px 30px rgba(234,124,18,.30) !important;
      }

      .swal2-confirm.ev-mpc-swal-confirm.ev-mpc-swal-confirm-close{
        background:linear-gradient(180deg,#FFFFFF 0%,#F8FAFC 100%) !important;
        color:#475569 !important;
        border:1px solid #CBD5E1 !important;
        box-shadow:0 10px 22px rgba(15,23,42,.10) !important;
        transition:transform .16s ease, box-shadow .16s ease, background .16s ease, border-color .16s ease, color .16s ease !important;
      }

      .swal2-confirm.ev-mpc-swal-confirm.ev-mpc-swal-confirm-close:hover,
      .swal2-confirm.ev-mpc-swal-confirm.ev-mpc-swal-confirm-close:focus-visible{
        background:#F1F5F9 !important;
        color:#111827 !important;
        border-color:#94A3B8 !important;
        box-shadow:0 14px 28px rgba(15,23,42,.14) !important;
        transform:translateY(-1px) !important;
      }

      .swal2-confirm.ev-mpc-swal-confirm.ev-mpc-swal-confirm-close:active{
        transform:translateY(0) scale(.985) !important;
        box-shadow:0 8px 18px rgba(15,23,42,.10) !important;
      }

      .ev-mpc-modal-detail-v2{
        text-align:left;
        max-width:100%;
      }

      .ev-mpc-modal-hero{
        display:grid;
        grid-template-columns:minmax(180px, 220px) minmax(0, 1fr);
        gap:16px;
        align-items:stretch;
        margin-bottom:14px;
      }

      .ev-mpc-modal-media-card{
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

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-media{
        width:100% !important;
        height:172px !important;
        border-radius:18px !important;
        background:#F8FAFC !important;
        box-shadow:none !important;
      }

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-media img{
        object-fit:contain !important;
        padding:4px !important;
      }

      .ev-mpc-modal-mini-pills{
        display:flex;
        flex-wrap:wrap;
        gap:6px;
      }

      .ev-mpc-modal-mini-pill{
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

      .ev-mpc-modal-main-card{
        border:1px solid rgba(229,231,235,.94);
        border-radius:24px;
        background:#ffffff;
        box-shadow:0 12px 26px rgba(15,23,42,.05);
        padding:14px;
        min-width:0;
      }

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-head{
        margin-bottom:12px !important;
      }

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-title{
        font-size:1.18rem !important;
        line-height:1.16 !important;
      }

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-grid{
        grid-template-columns:repeat(2, minmax(0,1fr)) !important;
        gap:9px !important;
      }

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-item{
        background:#ffffff !important;
        border-color:#E9EEF5 !important;
        min-height:78px;
      }

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-item span{
        white-space:normal !important;
        word-break:normal !important;
      }

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-item strong{
        white-space:normal !important;
        word-break:normal !important;
        overflow-wrap:break-word !important;
        line-height:1.22 !important;
      }

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-stack{
        margin-top:14px !important;
      }

      .ev-mpc-modal-detail-v2 .ev-mpc-modal-stack,
      .ev-mpc-modal-detail-v2 .ev-mpc-modal-note{
        background:#ffffff !important;
        background-image:none !important;
      }

      @media (max-width: 767.98px){
        .ev-mpc-modal-hero{
          grid-template-columns:1fr;
        }

        .ev-mpc-modal-detail-v2 .ev-mpc-modal-media{
          height:210px !important;
        }

        .ev-mpc-modal-detail-v2 .ev-mpc-modal-grid{
          grid-template-columns:repeat(2, minmax(0,1fr)) !important;
        }
      }

      @media (max-width: 480px){
        .ev-mpc-modal-detail-v2 .ev-mpc-modal-grid{
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
      <div class="ev-mpc-modal-detail ev-mpc-modal-detail-v2">
        <div class="ev-mpc-modal-hero">
          <div class="ev-mpc-modal-media-card">
            <div class="ev-mpc-modal-media">
              <img src="${escapeHtml(imagen)}" alt="${escapeHtml(item.titulo_publicacion || 'Pedido')}">
            </div>
            <div class="ev-mpc-modal-mini-pills">
              <span class="ev-mpc-modal-mini-pill"><i class="bi bi-box-seam"></i>Cant. ${escapeHtml(item.cantidad || 0)}</span>
              <span class="ev-mpc-modal-mini-pill"><i class="bi bi-lightning-charge"></i>${escapeHtml(textoEntrega(item))}</span>
            </div>
          </div>

          <div class="ev-mpc-modal-main-card">
            <div class="ev-mpc-modal-head">
              <div>
                <div class="ev-mpc-modal-title">${escapeHtml(item.titulo_publicacion || 'Pedido')}</div>
                <div class="ev-mpc-modal-subtitle">
                  Pedido #${Number(item.codigo_pedido || 0)} · ${escapeHtml(formatFecha(fechaBase))}
                </div>
              </div>
              <span class="${badge.clase}">${escapeHtml(badge.texto)}</span>
            </div>

            <div class="ev-mpc-modal-grid">
              <div class="ev-mpc-modal-item">
                <span>Fecha</span>
                <strong>${escapeHtml(formatFecha(fechaBase))}</strong>
              </div>
              <div class="ev-mpc-modal-item">
                <span>Total</span>
                <strong>S/ ${escapeHtml(formatMoney(item.monto_total || item.total || 0))}</strong>
              </div>
              <div class="ev-mpc-modal-item">
                <span>Vendedor</span>
                <strong>${escapeHtml(item.nombre_vecino || item.nombre_vendedor || 'Vecino')}</strong>
              </div>
              <div class="ev-mpc-modal-item">
                <span>Entrega</span>
                <strong>${escapeHtml(textoEntrega(item))}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="ev-mpc-modal-section ev-mpc-modal-flow-section">
          <div class="ev-mpc-modal-note-title ev-mpc-modal-flow-title"><i class="bi bi-diagram-3" aria-hidden="true"></i> Progreso del pedido</div>
          ${getLineaEstado(item)}
        </div>

        <div class="ev-mpc-modal-section ev-mpc-modal-stack">
          <div class="ev-mpc-modal-row">
            <span>Dirección</span>
            <strong>${escapeHtml(item.direccion_entrega || '—')}</strong>
          </div>

          ${
            tieneProgramacion
              ? `
              <div class="ev-mpc-modal-row">
                <span>Entrega programada</span>
                <strong>${escapeHtml(formatFecha(item.fecha_hora_programada))}</strong>
              </div>
            `
              : ''
          }


          <div class="ev-mpc-modal-row">
            <span>Estado</span>
            <strong>${escapeHtml(estadoLegible(item.estado_actual || ''))}</strong>
          </div>
        </div>

        ${
          String(item.mensaje_comprador || '').trim() !== ''
            ? `
            <div class="ev-mpc-modal-section">
              <div class="ev-mpc-modal-note-title">Tu mensaje</div>
              <div class="ev-mpc-modal-note">${escapeHtml(item.mensaje_comprador)}</div>
            </div>
          `
            : ''
        }

        <div class="ev-mpc-modal-section">
          <div class="ev-mpc-modal-note-title">Detalle del estado</div>
          <div class="ev-mpc-modal-note">
            ${escapeHtml(item.motivo_estado || item.mensaje_estado || 'Estado actualizado del pedido.')}
          </div>
        </div>
      </div>
    `;
  }

  async function verDetalle(id) {
    ensureCompradorPremiumStyles();
    ensureRecojoCompradorStyles();
    const item = cachePedidos.get(Number(id || 0));
    if (!window.Swal || !item) return;

    await Swal.fire({
      title: 'Detalle del pedido',
      showCloseButton: false,
      closeButtonAriaLabel: 'Cerrar',
      html: buildDetalleHtml(item),
      width: 880,
      confirmButtonText: '<i class="bi bi-x-circle" aria-hidden="true"></i><span>Cerrar</span>',
      showCancelButton: false,
      showDenyButton: false,
      buttonsStyling: false,
      customClass: {
        container: 'ev-mpc-swal-container ev-swal-container',
        popup: 'ev-mpc-swal-popup ev-mpc-swal-popup-premium ev-mpc-swal-popup-detail ev-swal-popup ev-swal-popup-detail',
        title: 'ev-mpc-swal-title ev-swal-title',
        htmlContainer: 'ev-mpc-swal-html ev-swal-html',
        confirmButton: 'ev-mpc-swal-confirm ev-mpc-swal-confirm-close ev-swal-confirm',
        cancelButton: 'ev-mpc-swal-cancel ev-swal-cancel',
        closeButton: 'ev-swal-close'
      }
    });
  }

  function detenerCountdownRecojoComprador() {
    if (recojoCompradorTimer) {
      clearInterval(recojoCompradorTimer);
      recojoCompradorTimer = null;
    }
  }

  function actualizarCountdownRecojoComprador() {
    const nodes = Array.from(document.querySelectorAll('[data-recojo-comprador-countdown="1"]'));

    if (!nodes.length) {
      detenerCountdownRecojoComprador();
      return;
    }

    let debeRefrescar = false;

    nodes.forEach((node) => {
      const expiraMs = Number(node.dataset.recojoCompradorExpiraMs || 0);
      const span = node.querySelector('span');
      const restante = expiraMs > 0 ? Math.max(0, Math.ceil((expiraMs - Date.now()) / 1000)) : 0;

      if (span) span.textContent = formatTiempoCorto(restante);
      if (restante <= 0) debeRefrescar = true;
    });

    if (debeRefrescar && vistaActiva && document.querySelector('.ev-mpc-page')) {
      window.setTimeout(() => cargarPedidos({ silent: true }), 650);
    }
  }

  function iniciarCountdownRecojoComprador() {
    detenerCountdownRecojoComprador();
    if (!document.querySelector('[data-recojo-comprador-countdown="1"]')) return;
    actualizarCountdownRecojoComprador();
    recojoCompradorTimer = window.setInterval(actualizarCountdownRecojoComprador, 1000);
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
      if (!document.querySelector('.ev-mpc-page')) return;
      cargarPedidos({ silent: true });
    }, POLLING_MS);
  }

  function actualizarBotonesCancelacionPorEspera() {
    if (!vistaActiva) return;

    document.querySelectorAll('.ev-mpc-page [data-action="cancelar-solicitud"][data-cancel-enable-ms]').forEach((btn) => {
      const habilitaEnMs = Number(btn.dataset.cancelEnableMs || 0);
      if (!Number.isFinite(habilitaEnMs) || habilitaEnMs <= 0) return;

      if (Date.now() >= habilitaEnMs) {
        btn.classList.remove('d-none');
        btn.removeAttribute('aria-hidden');
      }
    });
  }

  function detenerCountdownCancelacionComprador() {
    if (cancelacionCompradorTimer) {
      window.clearInterval(cancelacionCompradorTimer);
      cancelacionCompradorTimer = null;
    }
  }

  function iniciarCountdownCancelacionComprador() {
    detenerCountdownCancelacionComprador();
    actualizarBotonesCancelacionPorEspera();
    cancelacionCompradorTimer = window.setInterval(actualizarBotonesCancelacionPorEspera, 1000);
  }

  function bindTabs() {
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

  document.addEventListener('click', async (e) => {
    const root = document.querySelector('.ev-mpc-page');
    if (!root) return;

    const btn = e.target.closest('[data-action]');
    if (!btn) return;

    const action = btn.dataset.action;
    const id = Number(btn.dataset.id || 0);
    if (!id) return;

    if (action === 'confirmar-entrega') {
      await confirmarEntrega(id);
      return;
    }


    if (action === 'cancelar-solicitud') {
      await cancelarSolicitud(id);
      return;
    }

    if (action === 'calificar') {
      await abrirModalCalificacion(cachePedidos.get(id));
      return;
    }

    if (action === 'detalle') {
      await verDetalle(id);
    }
  });

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) return;
    if (document.querySelector('.ev-mpc-page')) {
      actualizarBotonesCancelacionPorEspera();
      cargarPedidos({ silent: true });
    }
  });

  document.addEventListener('ev:content-loaded', () => {
    if (document.querySelector('.ev-mpc-page')) {
      initMisPedidosComprador();
    } else {
      vistaActiva = false;
      detenerPolling();
      detenerCountdownRecojoComprador();
      detenerCountdownCancelacionComprador();
    }
  });

  function initMisPedidosComprador() {
    const refs = getRefs();
    if (!refs.root) {
      vistaActiva = false;
      detenerPolling();
      detenerCountdownRecojoComprador();
      detenerCountdownCancelacionComprador();
      return;
    }

    ensureCompradorPremiumStyles();
    ensureRecojoCompradorStyles();

    vistaActiva = true;
    bindTabs();
    bindRefresh();
    showTab(refs, tabActiva || 'pendientes');
    cargarPedidos({ silent: false });
    iniciarPolling();
    iniciarCountdownCancelacionComprador();
  }

  window.EVMisPedidosComprador = {
    init: initMisPedidosComprador,
    refresh: () => cargarPedidos({ silent: true }),
    focusPedido: (codigoPedido) => {
      const id = Number(codigoPedido || 0);
      if (id <= 0) return false;
      try {
        sessionStorage.setItem(NOTIF_ORDER_TARGET_KEY, JSON.stringify({
          codigo_pedido: id,
          rol: 'comprador',
          created_at: Date.now()
        }));
      } catch (_) {}
      cargarPedidos({ silent: true });
      return true;
    }
  };

  if (document.querySelector('.ev-mpc-page')) {
    initMisPedidosComprador();
  }
})();