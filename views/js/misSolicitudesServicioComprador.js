/* views/js/misSolicitudesServicioComprador.js */
(function () {
  'use strict';

  const MODULE_KEY = '__EV_SOLICITUDES_SERVICIO_COMPRADOR_V1__';

  if (window[MODULE_KEY] === true) {
    window.EVSolicitudesServicioComprador?.init?.();
    return;
  }
  window[MODULE_KEY] = true;

  const BASE = (window.BASE_URL || '').toString().replace(/\/+$/, '');
  const POLLING_MS = 10000;
  const PLACEHOLDER = `${BASE}/public/img/placeholder-ev.png`;

  let tabActiva = 'responder';
  let pollingTimer = null;
  let cargando = false;
  let accionEnCurso = false;
  let vistaActiva = false;
  let cache = new Map();

  function escapeHtml(valor) {
    return String(valor ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatMoney(valor) {
    const monto = Number(valor || 0);
    if (!Number.isFinite(monto)) return 'S/ —';

    return `S/ ${monto.toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })}`;
  }

  function formatFecha(valor, includeTime = false) {
    const raw = String(valor || '').trim();
    if (!raw) return '—';

    const source = raw.includes('T') ? raw : `${raw}${raw.length <= 10 ? 'T00:00:00' : ''}`;
    const fecha = new Date(source.replace(' ', 'T'));

    if (Number.isNaN(fecha.getTime())) return raw;

    return fecha.toLocaleString('es-PE', includeTime
      ? { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }
      : { year: 'numeric', month: '2-digit', day: '2-digit' }
    );
  }

  function formatTiempo(segundos) {
    const total = Math.max(0, Math.floor(Number(segundos || 0)));
    const horas = Math.floor(total / 3600);
    const minutos = Math.floor((total % 3600) / 60);
    const segundosRestantes = total % 60;

    return horas > 0
      ? `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundosRestantes).padStart(2, '0')}`
      : `${String(minutos).padStart(2, '0')}:${String(segundosRestantes).padStart(2, '0')}`;
  }

  function imageUrl(valor) {
    const raw = String(valor || '').trim();
    if (!raw) return PLACEHOLDER;
    if (/^https?:\/\//i.test(raw)) return raw;
    if (raw.startsWith('/')) return `${BASE}${raw}`;
    return `${BASE}/${raw.replace(/^\/+/, '')}`;
  }

  function estadoLegible(estado) {
    const mapa = {
      pendiente_proveedor: 'Esperando inicio de conversación',
      informacion_adicional_solicitada: 'Información solicitada',
      propuesta_enviada_solicitante: 'Propuesta recibida',
      ajuste_solicitado: 'Ajuste solicitado',
      ajuste_cotizacion_solicitado: 'Nueva cotización requerida',
      cotizacion_final_enviada: 'Cotización final por revisar',
      cotizacion_vencida: 'Cotización vencida',
      coordinacion_confirmada: 'Coordinación confirmada',
      servicio_realizado_proveedor: 'Pendiente de tu confirmación',
      servicio_confirmado_solicitante: 'Servicio confirmado',
      observacion_reportada: 'Observación reportada',
      cotizacion_rechazada_solicitante: 'Cotización rechazada',
      rechazada_proveedor: 'Rechazada por proveedor',
      cancelada_solicitante: 'Cancelada por ti',
      cancelada_proveedor: 'Cancelada por proveedor',
      sin_respuesta_proveedor: 'Sin respuesta del proveedor'
    };

    return mapa[String(estado || '').trim()] || 'Sin estado';
  }

  function etiquetaRango(item) {
    const fecha = item?.fecha_deseada ? formatFecha(item.fecha_deseada) : 'A coordinar';
    const rango = item?.rango_horario_texto || 'A coordinar';
    return `${fecha} · ${rango}`;
  }

  function badge(item) {
    const estado = String(item?.estado || '').trim();

    if (estado === 'informacion_adicional_solicitada' || estado === 'propuesta_enviada_solicitante' || estado === 'cotizacion_final_enviada' || estado === 'servicio_realizado_proveedor') {
      return { cls: 'ev-ssc-badge ev-ssc-badge-pending', text: estadoLegible(estado) };
    }

    if (estado === 'pendiente_proveedor' || estado === 'ajuste_solicitado' || estado === 'ajuste_cotizacion_solicitado' || estado === 'cotizacion_vencida') {
      return { cls: 'ev-ssc-badge ev-ssc-badge-wait', text: estadoLegible(estado) };
    }

    if (estado === 'coordinacion_confirmada' || estado === 'servicio_confirmado_solicitante') {
      return { cls: 'ev-ssc-badge ev-ssc-badge-success', text: estadoLegible(estado) };
    }

    return { cls: 'ev-ssc-badge ev-ssc-badge-negative', text: estadoLegible(estado) };
  }

  function getRefs() {
    return {
      root: document.querySelector('.ev-ssc-page'),

      countResponder: document.getElementById('sscCountResponder'),
      countCoordinacion: document.getElementById('sscCountCoordinacion'),
      countCerradas: document.getElementById('sscCountCerradas'),

      badgeResponder: document.getElementById('sscBadgeResponder'),
      badgeCoordinacion: document.getElementById('sscBadgeCoordinacion'),
      badgeCerradas: document.getElementById('sscBadgeCerradas'),

      tabButtons: Array.from(document.querySelectorAll('.ev-ssc-tab')),
      tabResponder: document.getElementById('sscTabResponder'),
      tabCoordinacion: document.getElementById('sscTabCoordinacion'),
      tabCerradas: document.getElementById('sscTabCerradas'),

      listaResponder: document.getElementById('sscListaResponder'),
      listaCoordinacion: document.getElementById('sscListaCoordinacion'),
      listaCerradas: document.getElementById('sscListaCerradas'),

      emptyResponder: document.getElementById('sscEmptyResponder'),
      emptyCoordinacion: document.getElementById('sscEmptyCoordinacion'),
      emptyCerradas: document.getElementById('sscEmptyCerradas'),

      error: document.getElementById('sscError'),
      refresh: document.getElementById('btnRefrescarSolicitudesServicioComprador')
    };
  }

  function ensureSwalStyles() {
    const id = 'ev-ssc-swal-runtime-style';
    if (document.getElementById(id)) return;

    const style = document.createElement('style');
    style.id = id;
    style.textContent = `
      .ev-ssc-status-icon{width:84px;height:84px;margin:0 auto 13px;border:2px solid rgba(22,163,74,.2);border-radius:50%;display:grid;place-items:center;background:linear-gradient(180deg,#F0FDF4,#fff);box-shadow:0 10px 28px rgba(15,89,47,.08)}
      .ev-ssc-status-icon i{font-size:2.2rem;color:#16A34A}.ev-ssc-status-icon.warning{border-color:rgba(234,124,18,.26);background:linear-gradient(180deg,#FFF7ED,#fff)}.ev-ssc-status-icon.warning i{color:#EA7C12}.ev-ssc-status-icon.error{border-color:rgba(220,38,38,.22);background:linear-gradient(180deg,#FEF2F2,#fff)}.ev-ssc-status-icon.error i{color:#DC2626}
      .ev-ssc-msg-subtitle{font-weight:900;font-size:1.06rem;color:#0F592F;text-align:center;margin-bottom:7px}.ev-ssc-msg-text{font-size:.9rem;line-height:1.55;color:#64748B;text-align:center}.ev-ssc-msg-card{margin-top:15px;text-align:left;padding:12px 14px;border:1px solid #E5E7EB;border-radius:17px;background:#fff;box-shadow:0 8px 20px rgba(15,23,42,.04)}.ev-ssc-msg-card span{display:block;font-size:.7rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:#94A3B8;margin-bottom:4px}.ev-ssc-msg-card strong{display:block;color:#111827;font-size:.92rem}
    `;
    document.head.appendChild(style);
  }

  function swalConfig(options = {}) {
    ensureSwalStyles();

    return Object.assign({
      buttonsStyling: false,
      allowOutsideClick: false,
      allowEscapeKey: true,
      customClass: {
        popup: 'ev-ssc-swal-popup',
        title: 'ev-ssc-swal-title',
        htmlContainer: 'ev-ssc-swal-html',
        confirmButton: 'ev-ssc-swal-confirm',
        cancelButton: 'ev-ssc-swal-cancel'
      }
    }, options || {});
  }

  function iconHtml(kind = 'success') {
    const className = kind === 'warning' ? 'warning' : kind === 'error' ? 'error' : '';
    const icon = kind === 'warning'
      ? 'bi-exclamation-triangle'
      : kind === 'error'
        ? 'bi-x-lg'
        : kind === 'info'
          ? 'bi-info-lg'
          : 'bi-check2';

    return `<div class="ev-ssc-status-icon ${className}"><i class="bi ${icon}"></i></div>`;
  }

  function messageHtml(kind, subtitle, text, cardLabel = '', cardText = '') {
    return `
      <div>
        ${iconHtml(kind)}
        <div class="ev-ssc-msg-subtitle">${escapeHtml(subtitle)}</div>
        <div class="ev-ssc-msg-text">${escapeHtml(text)}</div>
        ${cardText ? `<div class="ev-ssc-msg-card"><span>${escapeHtml(cardLabel)}</span><strong>${escapeHtml(cardText)}</strong></div>` : ''}
      </div>
    `;
  }

  async function notify(kind, title, subtitle, text, extra = {}) {
    if (!window.Swal?.fire) {
      window.alert(`${title}\n\n${text}`);
      return { isConfirmed: true };
    }

    return Swal.fire(swalConfig(Object.assign({
      title,
      html: messageHtml(kind, subtitle, text, extra.cardLabel || '', extra.cardText || ''),
      confirmButtonText: extra.confirmText || 'Entendido',
      showCancelButton: Boolean(extra.showCancelButton),
      cancelButtonText: extra.cancelText || 'Cancelar'
    }, extra || {})));
  }

  async function handleAuth(resp, json) {
    if (resp.status === 401) {
      await notify('info', 'Sesión finalizada', 'Debes iniciar sesión nuevamente', json?.mensaje || 'Tu sesión ya no está activa.');
      window.location.href = json?.redirect || `${BASE}/login`;
      return true;
    }

    if (resp.status === 403 && String(json?.error || '') === 'CUENTA_BLOQUEADA') {
      await notify('warning', 'Cuenta bloqueada', 'No puedes continuar en este momento', json?.mensaje || 'Tu cuenta fue bloqueada.');
      window.location.href = json?.redirect || `${BASE}/login`;
      return true;
    }

    if (resp.status === 409 && String(json?.error || '') === 'CUENTA_OBSERVADA') {
      await notify('warning', 'Cuenta observada', 'Debes revisar el estado de tu cuenta', json?.mensaje || 'Tu cuenta está en revisión.');
      window.location.href = json?.redirect || `${BASE}/cuenta-observada`;
      return true;
    }

    return false;
  }

  function propuestaHtml(propuesta) {
    if (!propuesta) return '';

    let precio = propuesta.monto_propuesto !== null && propuesta.monto_propuesto !== undefined
      ? formatMoney(propuesta.monto_propuesto)
      : (propuesta.tipo_precio_texto || 'Por coordinar');

    if (propuesta.unidad_precio) {
      precio += ` ${propuesta.unidad_precio}`;
    }

    return `
      <section class="ev-ssc-proposal">
        <div class="ev-ssc-proposal-title">
          <i class="bi bi-file-earmark-text"></i>
          Propuesta del proveedor · Versión ${Number(propuesta.version || 1)}
        </div>
        <div class="ev-ssc-proposal-grid">
          <div class="ev-ssc-proposal-box">
            <span>Modalidad</span>
            <strong>${escapeHtml(propuesta.modalidad_texto || 'Por coordinar')}</strong>
          </div>
          <div class="ev-ssc-proposal-box">
            <span>Momento</span>
            <strong>${escapeHtml(propuesta.momento_texto || 'A coordinar')}</strong>
          </div>
          <div class="ev-ssc-proposal-box">
            <span>Precio</span>
            <strong>${escapeHtml(precio)}</strong>
          </div>
          <div class="ev-ssc-proposal-box">
            <span>Duración estimada</span>
            <strong>${escapeHtml(propuesta.duracion_estimada || 'Por coordinar')}</strong>
          </div>
        </div>
        ${propuesta.fecha_propuesta ? `<div class="ev-ssc-proposal-text"><strong>Fecha:</strong> ${escapeHtml(formatFecha(propuesta.fecha_propuesta))}${propuesta.horario_propuesto ? ` · ${escapeHtml(propuesta.horario_propuesto)}` : ''}</div>` : ''}
        <div class="ev-ssc-proposal-text"><strong>Alcance:</strong> ${escapeHtml(propuesta.alcance_confirmado || '—')}</div>
        ${propuesta.requisitos ? `<div class="ev-ssc-proposal-text"><strong>Requisitos:</strong> ${escapeHtml(propuesta.requisitos)}</div>` : ''}
        ${propuesta.mensaje_proveedor ? `<div class="ev-ssc-proposal-text"><strong>Mensaje del proveedor:</strong> ${escapeHtml(propuesta.mensaje_proveedor)}</div>` : ''}
      </section>
    `;
  }

  function stateHtml(item) {
    const estado = String(item?.estado || '').trim();

    if (estado === 'pendiente_proveedor') {
      const segundos = item?.segundos_restantes;

      return `
        <div class="ev-ssc-state ev-ssc-state-wait">
          <div class="ev-ssc-state-title">Solicitud enviada</div>
          <div class="ev-ssc-state-text">Tu solicitud está esperando una respuesta del proveedor.</div>
          ${segundos !== null && segundos !== undefined ? `<div class="ev-ssc-time"><i class="bi bi-clock-history"></i> ${escapeHtml(formatTiempo(segundos))} restantes</div>` : ''}
        </div>
      `;
    }

    if (estado === 'informacion_adicional_solicitada') {
      return `
        <div class="ev-ssc-state ev-ssc-state-pending">
          <div class="ev-ssc-state-title">El proveedor necesita información adicional</div>
          <div class="ev-ssc-state-text">${escapeHtml(item?.motivo_estado || 'Responde con el detalle solicitado para que el proveedor pueda continuar.')}</div>
        </div>
      `;
    }

    if (estado === 'propuesta_enviada_solicitante') {
      return `
        <div class="ev-ssc-state ev-ssc-state-pending">
          <div class="ev-ssc-state-title">Tienes una propuesta por revisar</div>
          <div class="ev-ssc-state-text">Revísala con calma. Puedes aceptarla o pedir un ajuste antes de confirmar la coordinación.</div>
        </div>
      `;
    }

    if (estado === 'ajuste_solicitado') {
      return `
        <div class="ev-ssc-state ev-ssc-state-wait">
          <div class="ev-ssc-state-title">Ajuste enviado al proveedor</div>
          <div class="ev-ssc-state-text">${escapeHtml(item?.motivo_estado || 'El proveedor revisará tu solicitud de ajuste y responderá con una nueva propuesta.')}</div>
        </div>
      `;
    }

    if (estado === 'coordinacion_confirmada') {
      return `
        <div class="ev-ssc-state ev-ssc-state-success">
          <div class="ev-ssc-state-title">Coordinación confirmada</div>
          <div class="ev-ssc-state-text">Ambos vecinos tienen una propuesta aceptada. La ejecución y confirmación final se trabajarán en el siguiente punto.</div>
        </div>
      `;
    }

    if (estado === 'servicio_realizado_proveedor') {
      return `
        <div class="ev-ssc-state ev-ssc-state-success">
          <div class="ev-ssc-state-title">Servicio marcado como realizado</div>
          <div class="ev-ssc-state-text">${escapeHtml(item?.motivo_estado || 'El proveedor marcó la atención como realizada.')}</div>
        </div>
      `;
    }

    return `
      <div class="ev-ssc-state ev-ssc-state-negative">
        <div class="ev-ssc-state-title">${escapeHtml(estadoLegible(estado))}</div>
        <div class="ev-ssc-state-text">${escapeHtml(item?.motivo_estado || 'Esta solicitud se cerró sin concretarse.')}</div>
      </div>
    `;
  }

  function actionsHtml(item) {
    const id = Number(item?.codigo_solicitud_servicio || 0);
    return `
      <button type="button" class="btn ev-ssc-btn-accept" data-ssc-action="conversacion" data-id="${id}">
        <i class="bi bi-chat-square-text me-1"></i>Abrir conversación
      </button>
    `;
  }

  function renderCard(item) {
    const b = badge(item);
    const titulo = item?.titulo_servicio || 'Servicio';

    return `
      <article class="ev-ssc-card" data-id="${Number(item?.codigo_solicitud_servicio || 0)}">
        <div class="ev-ssc-card-head">
          <div class="ev-ssc-card-media">
            <img src="${escapeHtml(imageUrl(item?.imagen_portada))}" alt="${escapeHtml(titulo)}">
          </div>

          <div class="ev-ssc-card-head-main">
            <div class="ev-ssc-card-title-row">
              <div>
                <div class="ev-ssc-card-title">${escapeHtml(titulo)}</div>
                <div class="ev-ssc-card-meta">
                  Solicitud #${Number(item?.codigo_solicitud_servicio || 0)} · ${escapeHtml(formatFecha(item?.created_at, true))}
                </div>
              </div>
              <span class="${b.cls}">${escapeHtml(b.text)}</span>
            </div>

            <div class="ev-ssc-pills">
              <span class="ev-ssc-pill"><i class="bi bi-person"></i>${escapeHtml(item?.nombre_proveedor || 'Vecino')}</span>
              ${item?.categoria_nombre ? `<span class="ev-ssc-pill"><i class="bi bi-tags"></i>${escapeHtml(item.categoria_nombre)}</span>` : ''}
            </div>
          </div>
        </div>

        <div class="ev-ssc-card-body">
          <div class="ev-ssc-card-data">
            <div class="ev-ssc-data">
              <span>Fecha deseada</span>
              <strong>${escapeHtml(etiquetaRango(item))}</strong>
            </div>
            <div class="ev-ssc-data ev-ssc-data-price">
              <span>Precio referencial</span>
              <strong>${escapeHtml(formatMoney(item?.precio_referencial))}</strong>
            </div>
          </div>

          <div class="ev-ssc-info">
            <div class="ev-ssc-line">
              <span class="ev-ssc-line-label">Proveedor</span>
              <span class="ev-ssc-line-value">${escapeHtml(item?.nombre_proveedor || 'Vecino')}</span>
            </div>
            <div class="ev-ssc-line">
              <span class="ev-ssc-line-label">Ubicación</span>
              <span class="ev-ssc-line-value">${Number(item?.ubicacion_compartida || 0) === 1 ? 'Compartida en conversación' : 'Se comparte solo cuando sea necesaria'}</span>
            </div>
            <div class="ev-ssc-note">
              <span class="ev-ssc-note-label">Tu solicitud</span>
              <div class="ev-ssc-note-text">${escapeHtml(item?.mensaje_solicitante || 'Sin detalle adicional.')}</div>
            </div>
          </div>

          ${propuestaHtml(item?.propuesta)}
          ${stateHtml(item)}

          <div class="ev-ssc-actions">${actionsHtml(item)}</div>
        </div>
      </article>
    `;
  }

  function pintar(lista, target, empty) {
    if (!target || !empty) return;

    const items = Array.isArray(lista) ? lista : [];
    target.innerHTML = '';

    if (!items.length) {
      empty.classList.remove('d-none');
      return;
    }

    empty.classList.add('d-none');
    target.innerHTML = items.map(renderCard).join('');
  }

  function mostrarTab(refs, tab) {
    tabActiva = tab;

    refs.tabButtons.forEach((button) => {
      button.classList.toggle('active', button.dataset.tab === tab);
    });

    refs.tabResponder?.classList.toggle('d-none', tab !== 'responder');
    refs.tabCoordinacion?.classList.toggle('d-none', tab !== 'coordinacion');
    refs.tabCerradas?.classList.toggle('d-none', tab !== 'cerradas');
  }

  async function fetchLista() {
    const resp = await fetch(`${BASE}/api/servicios/solicitudes/solicitante`, {
      method: 'GET',
      credentials: 'include',
      headers: { Accept: 'application/json' },
      cache: 'no-store'
    });

    const json = await resp.json().catch(() => ({}));

    if (await handleAuth(resp, json)) {
      return { __authHandled: true };
    }

    if (!resp.ok || json?.ok === false) {
      throw new Error(json?.mensaje || 'No se pudieron cargar las solicitudes.');
    }

    return json?.data || {};
  }

  async function cargar() {
    const refs = getRefs();
    if (!refs.root || cargando) return;

    cargando = true;

    try {
      refs.error?.classList.add('d-none');

      const data = await fetchLista();
      if (data?.__authHandled) return;

      const porResponder = Array.isArray(data?.por_responder) ? data.por_responder : [];
      const enCoordinacion = Array.isArray(data?.en_coordinacion) ? data.en_coordinacion : [];
      const cerradas = Array.isArray(data?.cerradas) ? data.cerradas : [];

      cache = new Map(
        [...porResponder, ...enCoordinacion, ...cerradas].map((item) => [
          Number(item.codigo_solicitud_servicio || 0),
          item
        ])
      );

      const resumen = data?.resumen || {};
      const responderCount = Number(resumen.por_responder ?? porResponder.length);
      const coordinacionCount = Number(resumen.en_coordinacion ?? enCoordinacion.length);
      const cerradasCount = Number(resumen.cerradas ?? cerradas.length);

      if (refs.countResponder) refs.countResponder.textContent = String(responderCount);
      if (refs.countCoordinacion) refs.countCoordinacion.textContent = String(coordinacionCount);
      if (refs.countCerradas) refs.countCerradas.textContent = String(cerradasCount);

      if (refs.badgeResponder) refs.badgeResponder.textContent = String(responderCount);
      if (refs.badgeCoordinacion) refs.badgeCoordinacion.textContent = String(coordinacionCount);
      if (refs.badgeCerradas) refs.badgeCerradas.textContent = String(cerradasCount);

      pintar(porResponder, refs.listaResponder, refs.emptyResponder);
      pintar(enCoordinacion, refs.listaCoordinacion, refs.emptyCoordinacion);
      pintar(cerradas, refs.listaCerradas, refs.emptyCerradas);

      mostrarTab(refs, tabActiva);
    } catch (error) {
      console.error('[SOLICITUDES_SERVICIO_COMPRADOR]', error);
      refs.error?.classList.remove('d-none');
    } finally {
      cargando = false;
    }
  }

  function cargarModal() {
    if (!window.Swal?.fire) return;

    Swal.fire(swalConfig({
      title: 'Procesando',
      html: '<div class="ev-ssc-swal-loader"></div><div class="ev-ssc-msg-text">Estamos registrando tu respuesta. Espera un momento.</div>',
      showConfirmButton: false,
      allowOutsideClick: false,
      allowEscapeKey: false
    }));
  }

  async function postJson(url, payload) {
    const resp = await fetch(url, {
      method: 'POST',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload || {})
    });

    const json = await resp.json().catch(() => ({}));

    if (await handleAuth(resp, json)) {
      return { __authHandled: true, resp, json };
    }

    return { resp, json };
  }

  async function responderInformacion(item) {
    if (!window.Swal?.fire) return;

    const result = await Swal.fire(swalConfig({
      title: 'Responder información',
      html: messageHtml(
        'info',
        'El proveedor necesita un detalle adicional',
        item?.motivo_estado || 'Brinda la información necesaria para que pueda continuar.',
        'Servicio',
        item?.titulo_servicio || 'Servicio seleccionado'
      ),
      input: 'textarea',
      inputPlaceholder: 'Escribe el detalle solicitado: medidas, cantidad, fotos, modelo, dirección, horario u otra precisión.',
      inputAttributes: {
        maxlength: 1500,
        'aria-label': 'Información adicional'
      },
      showCancelButton: true,
      confirmButtonText: 'Enviar información',
      cancelButtonText: 'Cancelar',
      preConfirm: (valor) => {
        const mensaje = String(valor || '').trim();
        if (mensaje.length < 8) {
          Swal.showValidationMessage('Describe la información solicitada para que el proveedor pueda continuar.');
          return false;
        }
        return mensaje;
      }
    }));

    if (!result.isConfirmed || !result.value) return;

    accionEnCurso = true;
    try {
      cargarModal();

      const { resp, json, __authHandled } = await postJson(
        `${BASE}/api/servicios/solicitudes/${Number(item.codigo_solicitud_servicio)}/responder-informacion`,
        { mensaje: result.value }
      );

      if (__authHandled) return;

      if (!resp.ok || json?.ok === false) {
        await notify('error', 'No se pudo enviar', 'Tu información no fue registrada', json?.mensaje || 'Inténtalo nuevamente.', {
          cardLabel: 'Servicio',
          cardText: item.titulo_servicio
        });
        return;
      }

      await notify('success', 'Información enviada', 'El proveedor fue notificado', json?.mensaje || 'Tu información adicional fue enviada.', {
        cardLabel: 'Servicio',
        cardText: item.titulo_servicio
      });

      tabActiva = 'coordinacion';
      await cargar();
    } finally {
      accionEnCurso = false;
    }
  }

  async function aceptarPropuesta(item) {
    if (!window.Swal?.fire) return;

    const propuesta = item?.propuesta;
    const precio = propuesta?.monto_propuesto !== null && propuesta?.monto_propuesto !== undefined
      ? formatMoney(propuesta.monto_propuesto)
      : (propuesta?.tipo_precio_texto || 'Por coordinar');

    const result = await Swal.fire(swalConfig({
      title: 'Aceptar propuesta',
      html: messageHtml(
        'warning',
        'Confirma solo si las condiciones son correctas',
        'Al aceptar, la coordinación quedará registrada entre ambos vecinos.',
        'Propuesta',
        `${item?.titulo_servicio || 'Servicio'} · ${precio}`
      ),
      showCancelButton: true,
      confirmButtonText: 'Sí, aceptar propuesta',
      cancelButtonText: 'Revisar nuevamente'
    }));

    if (!result.isConfirmed) return;

    accionEnCurso = true;
    try {
      cargarModal();

      const { resp, json, __authHandled } = await postJson(
        `${BASE}/api/servicios/solicitudes/${Number(item.codigo_solicitud_servicio)}/aceptar-propuesta`,
        {}
      );

      if (__authHandled) return;

      if (!resp.ok || json?.ok === false) {
        await notify('error', 'No se pudo aceptar', 'La propuesta conserva su estado actual', json?.mensaje || 'Inténtalo nuevamente.', {
          cardLabel: 'Servicio',
          cardText: item.titulo_servicio
        });
        await cargar();
        return;
      }

      await notify('success', 'Coordinación confirmada', 'El proveedor fue notificado', json?.mensaje || 'La propuesta fue aceptada correctamente.', {
        cardLabel: 'Servicio',
        cardText: item.titulo_servicio
      });

      tabActiva = 'coordinacion';
      await cargar();
    } finally {
      accionEnCurso = false;
    }
  }

  async function solicitarAjuste(item) {
    if (!window.Swal?.fire) return;

    const result = await Swal.fire(swalConfig({
      title: 'Solicitar ajuste',
      html: messageHtml(
        'info',
        'Indica qué condición deseas ajustar',
        'El proveedor revisará tu mensaje y podrá enviarte una nueva propuesta.',
        'Servicio',
        item?.titulo_servicio || 'Servicio seleccionado'
      ),
      input: 'textarea',
      inputPlaceholder: 'Ejemplo: ¿Podrías atender mañana por la tarde? ¿El precio incluye materiales? ¿Se puede ajustar la fecha?',
      inputAttributes: {
        maxlength: 1500,
        'aria-label': 'Solicitud de ajuste'
      },
      showCancelButton: true,
      confirmButtonText: 'Enviar ajuste',
      cancelButtonText: 'Cancelar',
      preConfirm: (valor) => {
        const mensaje = String(valor || '').trim();
        if (mensaje.length < 8) {
          Swal.showValidationMessage('Explica con claridad qué condición necesitas ajustar.');
          return false;
        }
        return mensaje;
      }
    }));

    if (!result.isConfirmed || !result.value) return;

    accionEnCurso = true;
    try {
      cargarModal();

      const { resp, json, __authHandled } = await postJson(
        `${BASE}/api/servicios/solicitudes/${Number(item.codigo_solicitud_servicio)}/solicitar-ajuste`,
        { mensaje: result.value }
      );

      if (__authHandled) return;

      if (!resp.ok || json?.ok === false) {
        await notify('error', 'No se pudo enviar', 'La propuesta conserva su estado actual', json?.mensaje || 'Inténtalo nuevamente.', {
          cardLabel: 'Servicio',
          cardText: item.titulo_servicio
        });
        await cargar();
        return;
      }

      await notify('success', 'Ajuste enviado', 'El proveedor fue notificado', json?.mensaje || 'Tu solicitud de ajuste fue enviada.', {
        cardLabel: 'Servicio',
        cardText: item.titulo_servicio
      });

      tabActiva = 'coordinacion';
      await cargar();
    } finally {
      accionEnCurso = false;
    }
  }

  async function cancelar(item) {
    if (!window.Swal?.fire) return;

    const result = await Swal.fire(swalConfig({
      title: 'Cancelar solicitud',
      html: messageHtml(
        'warning',
        'Indica un motivo claro y breve',
        'El proveedor será notificado para que no continúe con esta coordinación.',
        'Servicio',
        item?.titulo_servicio || 'Servicio seleccionado'
      ),
      input: 'textarea',
      inputPlaceholder: 'Ejemplo: Ya no necesito el servicio o coordiné una solución diferente.',
      inputAttributes: {
        maxlength: 500,
        'aria-label': 'Motivo de cancelación'
      },
      showCancelButton: true,
      confirmButtonText: 'Cancelar solicitud',
      cancelButtonText: 'Volver',
      preConfirm: (valor) => {
        const motivo = String(valor || '').trim();
        if (motivo.length < 5) {
          Swal.showValidationMessage('Indica un motivo de cancelación.');
          return false;
        }
        return motivo;
      }
    }));

    if (!result.isConfirmed || !result.value) return;

    accionEnCurso = true;
    try {
      cargarModal();

      const { resp, json, __authHandled } = await postJson(
        `${BASE}/api/servicios/solicitudes/${Number(item.codigo_solicitud_servicio)}/cancelar`,
        { motivo_cancelacion: result.value }
      );

      if (__authHandled) return;

      if (!resp.ok || json?.ok === false) {
        await notify('error', 'No se pudo cancelar', 'La solicitud conserva su estado actual', json?.mensaje || 'Inténtalo nuevamente.', {
          cardLabel: 'Servicio',
          cardText: item.titulo_servicio
        });
        await cargar();
        return;
      }

      await notify('success', 'Solicitud cancelada', 'El proveedor fue notificado', json?.mensaje || 'La solicitud fue cancelada correctamente.', {
        cardLabel: 'Servicio',
        cardText: item.titulo_servicio
      });

      tabActiva = 'cerradas';
      await cargar();
    } finally {
      accionEnCurso = false;
    }
  }

  function detalleHtml(item) {
    const propuesta = item?.propuesta;
    const b = badge(item);

    let precio = propuesta?.monto_propuesto !== null && propuesta?.monto_propuesto !== undefined
      ? formatMoney(propuesta.monto_propuesto)
      : (propuesta?.tipo_precio_texto || 'Por coordinar');

    if (propuesta?.unidad_precio) {
      precio += ` ${propuesta.unidad_precio}`;
    }

    return `
      <div class="ev-ssc-detail">
        <div class="ev-ssc-detail-top">
          <div class="ev-ssc-detail-media">
            <img src="${escapeHtml(imageUrl(item?.imagen_portada))}" alt="${escapeHtml(item?.titulo_servicio || 'Servicio')}">
          </div>
          <div>
            <div class="ev-ssc-detail-title">${escapeHtml(item?.titulo_servicio || 'Servicio')}</div>
            <div class="ev-ssc-detail-sub">Solicitud #${Number(item?.codigo_solicitud_servicio || 0)} · ${escapeHtml(formatFecha(item?.created_at, true))}</div>
            <div class="ev-ssc-pills">
              <span class="ev-ssc-pill"><i class="bi bi-person"></i>${escapeHtml(item?.nombre_proveedor || 'Vecino')}</span>
              ${item?.categoria_nombre ? `<span class="ev-ssc-pill"><i class="bi bi-tags"></i>${escapeHtml(item.categoria_nombre)}</span>` : ''}
            </div>
          </div>
        </div>

        <div class="ev-ssc-detail-grid">
          <div class="ev-ssc-detail-box">
            <span>Precio referencial</span>
            <strong>${escapeHtml(formatMoney(item?.precio_referencial))}</strong>
          </div>
          <div class="ev-ssc-detail-box">
            <span>Fecha deseada</span>
            <strong>${escapeHtml(etiquetaRango(item))}</strong>
          </div>
          <div class="ev-ssc-detail-box">
            <span>Ubicación</span>
            <strong>${Number(item?.ubicacion_compartida || 0) === 1 ? 'Compartida dentro de la conversación' : 'Se comparte cuando sea necesaria para cotizar'}</strong>
          </div>
          <div class="ev-ssc-detail-box">
            <span>Estado</span>
            <strong>${escapeHtml(b.text)}</strong>
          </div>
        </div>

        <div class="ev-ssc-detail-section">
          <h6>Tu solicitud</h6>
          <p>${escapeHtml(item?.mensaje_solicitante || 'Sin detalle adicional.')}</p>
        </div>

        ${propuesta ? `
          <div class="ev-ssc-detail-section">
            <h6>Propuesta del proveedor · Versión ${Number(propuesta.version || 1)}</h6>
            <p><strong>Modalidad:</strong> ${escapeHtml(propuesta.modalidad_texto || 'Por coordinar')}
<strong>Momento:</strong> ${escapeHtml(propuesta.momento_texto || 'A coordinar')}
${propuesta.fecha_propuesta ? `<strong>Fecha:</strong> ${escapeHtml(formatFecha(propuesta.fecha_propuesta))}\n` : ''}${propuesta.horario_propuesto ? `<strong>Horario:</strong> ${escapeHtml(propuesta.horario_propuesto)}\n` : ''}<strong>Precio:</strong> ${escapeHtml(precio)}
<strong>Alcance:</strong> ${escapeHtml(propuesta.alcance_confirmado || '—')}${propuesta.duracion_estimada ? `\n<strong>Duración:</strong> ${escapeHtml(propuesta.duracion_estimada)}` : ''}${propuesta.requisitos ? `\n<strong>Requisitos:</strong> ${escapeHtml(propuesta.requisitos)}` : ''}\n<strong>Mensaje:</strong> ${escapeHtml(propuesta.mensaje_proveedor || '—')}</p>
          </div>
        ` : ''}

        ${item?.motivo_estado ? `
          <div class="ev-ssc-detail-section">
            <h6>Última actualización</h6>
            <p>${escapeHtml(item.motivo_estado)}</p>
          </div>
        ` : ''}
      </div>
    `;
  }


  async function asegurarConversacionServicio() {
    if (window.EVServicioConversacion?.open) return true;
    const id = 'ev-servicio-conversacion-script';
    let script = document.getElementById(id);
    if (!script) {
      script = document.createElement('script');
      script.id = id;
      script.src = `${BASE}/views/js/servicioConversacion.js`;
      document.head.appendChild(script);
    }
    await new Promise((resolve) => {
      if (window.EVServicioConversacion?.open) return resolve();
      script.addEventListener('load', resolve, { once: true });
      script.addEventListener('error', resolve, { once: true });
      window.setTimeout(resolve, 3500);
    });
    return !!window.EVServicioConversacion?.open;
  }

  async function detalle(item) {
    if (await asegurarConversacionServicio()) {
      window.EVServicioConversacion.open(Number(item?.codigo_solicitud_servicio || 0));
      return;
    }

    if (!window.Swal?.fire) return;

    await Swal.fire(swalConfig({
      title: 'Detalle de solicitud',
      html: detalleHtml(item),
      width: 800,
      confirmButtonText: 'Cerrar'
    }));
  }

  async function manejarClick(event) {
    const root = document.querySelector('.ev-ssc-page');
    if (!root || accionEnCurso) return;

    const button = event.target.closest('[data-ssc-action]');
    if (!button) return;

    const id = Number(button.dataset.id || 0);
    const action = button.dataset.sscAction;
    const item = cache.get(id);

    if (!id || !item) return;

    if (action === 'conversacion' || action === 'detalle') await detalle(item);
    if (action === 'responder-informacion') await responderInformacion(item);
    if (action === 'aceptar-propuesta') await aceptarPropuesta(item);
    if (action === 'solicitar-ajuste') await solicitarAjuste(item);
    if (action === 'cancelar') await cancelar(item);
  }

  function bind() {
    const refs = getRefs();

    refs.tabButtons.forEach((button) => {
      if (button.dataset.evBound === '1') return;

      button.dataset.evBound = '1';
      button.addEventListener('click', () => {
        mostrarTab(getRefs(), button.dataset.tab || 'responder');
      });
    });

    if (refs.refresh && refs.refresh.dataset.evBound !== '1') {
      refs.refresh.dataset.evBound = '1';
      refs.refresh.addEventListener('click', () => cargar());
    }
  }

  function stopPolling() {
    if (pollingTimer) {
      clearInterval(pollingTimer);
      pollingTimer = null;
    }
  }

  function startPolling() {
    stopPolling();

    pollingTimer = setInterval(() => {
      if (!vistaActiva || document.hidden || !document.querySelector('.ev-ssc-page')) return;
      cargar();
    }, POLLING_MS);
  }

  function init() {
    const refs = getRefs();

    if (!refs.root) {
      vistaActiva = false;
      stopPolling();
      return;
    }

    vistaActiva = true;
    bind();
    mostrarTab(refs, tabActiva);
    cargar();
    startPolling();
  }

  document.addEventListener('click', manejarClick);

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && document.querySelector('.ev-ssc-page')) {
      cargar();
    }
  });

  document.addEventListener('ev:content-loaded', () => {
    if (document.querySelector('.ev-ssc-page')) {
      init();
    } else {
      vistaActiva = false;
      stopPolling();
    }
  });

  window.EVSolicitudesServicioComprador = {
    init,
    refresh: () => cargar()
  };

  if (document.querySelector('.ev-ssc-page')) {
    init();
  }
})();
