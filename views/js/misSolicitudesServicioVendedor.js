/* views/js/misSolicitudesServicioVendedor.js */
(function () {
  'use strict';

  const MODULE_KEY = '__EV_SOLICITUDES_SERVICIO_VENDEDOR_V1__';
  if (window[MODULE_KEY] === true) {
    window.EVSolicitudesServicioVendedor?.init?.();
    return;
  }
  window[MODULE_KEY] = true;

  const BASE = (window.BASE_URL || '').toString().replace(/\/+$/, '');
  const POLLING_MS = 10000;
  const PLACEHOLDER = `${BASE}/public/img/placeholder-ev.png`;

  let tabActiva = 'pendientes';
  let pollingTimer = null;
  let cargando = false;
  let accionEnCurso = false;
  let vistaActiva = false;
  let cache = new Map();

  function escapeHtml(v) {
    return String(v ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatMoney(v) {
    const n = Number(v || 0);
    if (!Number.isFinite(n)) return 'S/ —';
    return `S/ ${n.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  }

  function formatFecha(v, includeTime = false) {
    const raw = String(v || '').trim();
    if (!raw) return '—';
    const source = raw.includes('T') ? raw : `${raw}${raw.length <= 10 ? 'T00:00:00' : ''}`;
    const d = new Date(source.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return raw;
    return d.toLocaleString('es-PE', includeTime
      ? { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }
      : { year: 'numeric', month: '2-digit', day: '2-digit' });
  }

  function formatTiempo(segundos) {
    const s = Math.max(0, Math.floor(Number(segundos || 0)));
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return h > 0
      ? `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
      : `${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
  }

  function imageUrl(value) {
    const raw = String(value || '').trim();
    if (!raw) return PLACEHOLDER;
    if (/^https?:\/\//i.test(raw)) return raw;
    if (raw.startsWith('/')) return `${BASE}${raw}`;
    return `${BASE}/${raw.replace(/^\/+/, '')}`;
  }

  function getRefs() {
    return {
      root: document.querySelector('.ev-ssv-page'),
      countPendientes: document.getElementById('ssvCountPendientes'),
      countEsperando: document.getElementById('ssvCountEsperando'),
      countCerradas: document.getElementById('ssvCountCerradas'),
      badgePendientes: document.getElementById('ssvBadgePendientes'),
      badgeEsperando: document.getElementById('ssvBadgeEsperando'),
      badgeCerradas: document.getElementById('ssvBadgeCerradas'),
      tabButtons: Array.from(document.querySelectorAll('.ev-ssv-tab')),
      tabPendientes: document.getElementById('ssvTabPendientes'),
      tabEsperando: document.getElementById('ssvTabEsperando'),
      tabCerradas: document.getElementById('ssvTabCerradas'),
      listaPendientes: document.getElementById('ssvListaPendientes'),
      listaEsperando: document.getElementById('ssvListaEsperando'),
      listaCerradas: document.getElementById('ssvListaCerradas'),
      emptyPendientes: document.getElementById('ssvEmptyPendientes'),
      emptyEsperando: document.getElementById('ssvEmptyEsperando'),
      emptyCerradas: document.getElementById('ssvEmptyCerradas'),
      error: document.getElementById('ssvError'),
      refresh: document.getElementById('btnRefrescarSolicitudesServicio')
    };
  }

  function ensureSwalStyles() {
    const id = 'ev-ssv-swal-style';
    if (document.getElementById(id)) return;
    const style = document.createElement('style');
    style.id = id;
    style.textContent = `
      .ev-ssv-swal-container{backdrop-filter:blur(2px)}
      .ev-ssv-status-icon{width:88px;height:88px;margin:0 auto 13px;border:2px solid rgba(22,163,74,.2);border-radius:50%;display:grid;place-items:center;background:linear-gradient(180deg,#F0FDF4,#fff);box-shadow:0 10px 28px rgba(15,89,47,.08)}
      .ev-ssv-status-icon i{font-size:2.35rem;color:#16A34A}.ev-ssv-status-icon.warning{border-color:rgba(234,124,18,.26);background:linear-gradient(180deg,#FFF7ED,#fff)}.ev-ssv-status-icon.warning i{color:#EA7C12}.ev-ssv-status-icon.error{border-color:rgba(220,38,38,.22);background:linear-gradient(180deg,#FEF2F2,#fff)}.ev-ssv-status-icon.error i{color:#DC2626}
      .ev-ssv-msg-subtitle{font-weight:900;font-size:1.08rem;color:#0F592F;text-align:center;margin-bottom:7px}.ev-ssv-msg-text{font-size:.9rem;line-height:1.55;color:#64748B;text-align:center}.ev-ssv-msg-card{margin-top:15px;text-align:left;padding:12px 14px;border:1px solid #E5E7EB;border-radius:17px;background:#fff;box-shadow:0 8px 20px rgba(15,23,42,.04)}.ev-ssv-msg-card span{display:block;font-size:.7rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:#94A3B8;margin-bottom:4px}.ev-ssv-msg-card strong{display:block;color:#111827;font-size:.92rem}
    `;
    document.head.appendChild(style);
  }

  function swalConfig(opts = {}) {
    ensureSwalStyles();
    return Object.assign({
      buttonsStyling: false,
      allowOutsideClick: false,
      allowEscapeKey: true,
      customClass: {
        container: 'ev-ssv-swal-container',
        popup: 'ev-ssv-swal-popup',
        title: 'ev-ssv-swal-title',
        htmlContainer: 'ev-ssv-swal-html',
        confirmButton: 'ev-ssv-swal-confirm',
        cancelButton: 'ev-ssv-swal-cancel'
      }
    }, opts || {});
  }

  function iconHtml(kind = 'success') {
    const cls = kind === 'warning' ? 'warning' : kind === 'error' ? 'error' : '';
    const icon = kind === 'warning' ? 'bi-exclamation-triangle' : kind === 'error' ? 'bi-x-lg' : kind === 'info' ? 'bi-info-lg' : 'bi-check2';
    return `<div class="ev-ssv-status-icon ${cls}"><i class="bi ${icon}"></i></div>`;
  }

  function messageHtml(kind, subtitle, text, cardLabel = '', cardText = '') {
    return `
      <div>
        ${iconHtml(kind)}
        <div class="ev-ssv-msg-subtitle">${escapeHtml(subtitle)}</div>
        <div class="ev-ssv-msg-text">${escapeHtml(text)}</div>
        ${cardText ? `<div class="ev-ssv-msg-card"><span>${escapeHtml(cardLabel)}</span><strong>${escapeHtml(cardText)}</strong></div>` : ''}
      </div>
    `;
  }

  async function notify(kind, title, subtitle, text, extra = {}) {
    if (!window.Swal?.fire) {
      alert(`${title}\n\n${text}`);
      return { isConfirmed: true };
    }
    return Swal.fire(swalConfig(Object.assign({
      title,
      html: messageHtml(kind, subtitle, text, extra.cardLabel || '', extra.cardText || ''),
      confirmButtonText: extra.confirmText || 'Entendido',
      showCancelButton: !!extra.showCancelButton,
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

  function badge(item) {
    const estado = String(item?.estado || '').trim();
    if (['pendiente_proveedor', 'ajuste_solicitado', 'ajuste_cotizacion_solicitado', 'cotizacion_vencida'].includes(estado)) return { cls: 'ev-ssv-badge ev-ssv-badge-pending', text: item.estado_texto || 'Pendiente' };
    if (['informacion_adicional_solicitada', 'propuesta_enviada_solicitante', 'cotizacion_final_enviada', 'servicio_realizado_proveedor'].includes(estado)) return { cls: 'ev-ssv-badge ev-ssv-badge-wait', text: item.estado_texto || 'Esperando respuesta' };
    if (['coordinacion_confirmada', 'servicio_confirmado_solicitante'].includes(estado)) return { cls: 'ev-ssv-badge ev-ssv-badge-success', text: item.estado_texto || 'Confirmada' };
    return { cls: 'ev-ssv-badge ev-ssv-badge-negative', text: item.estado_texto || 'Cerrada' };
  }

  function rangoDeseado(item) {
    const fecha = item?.fecha_deseada ? formatFecha(item.fecha_deseada) : 'A coordinar';
    const rango = item?.rango_horario_texto || 'A coordinar';
    return `${fecha} · ${rango}`;
  }

  function propuestaHtml(propuesta) {
    if (!propuesta) return '';
    const valor = propuesta.monto_propuesto !== null && propuesta.monto_propuesto !== undefined
      ? `${formatMoney(propuesta.monto_propuesto)}${propuesta.unidad_precio ? ` ${escapeHtml(propuesta.unidad_precio)}` : ''}`
      : escapeHtml(propuesta.tipo_precio_texto || 'Por coordinar');
    return `
      <div class="ev-ssv-note">
        <span class="ev-ssv-note-label">Última propuesta enviada</span>
        <div class="ev-ssv-note-text">
          ${escapeHtml(propuesta.modalidad_texto || 'Por coordinar')} · ${escapeHtml(propuesta.momento_texto || 'A coordinar')}<br>
          <strong>${valor}</strong>
        </div>
      </div>
    `;
  }

  function stateHtml(item) {
    const estado = String(item?.estado || '');
    if (estado === 'pendiente_proveedor') {
      const restantes = item?.segundos_restantes;
      return `
        <div class="ev-ssv-state ev-ssv-state-pending">
          <div class="ev-ssv-state-title">Solicitud pendiente de tu respuesta</div>
          <div class="ev-ssv-state-text">Revisa el detalle y responde con una propuesta, una solicitud de información o un rechazo cordial.</div>
          ${restantes !== null && restantes !== undefined ? `<div class="ev-ssv-time"><i class="bi bi-clock-history"></i> ${escapeHtml(formatTiempo(restantes))} restantes</div>` : ''}
        </div>
      `;
    }
    if (estado === 'informacion_adicional_solicitada') {
      return `<div class="ev-ssv-state ev-ssv-state-wait"><div class="ev-ssv-state-title">Información solicitada al vecino</div><div class="ev-ssv-state-text">Tu solicitud quedó registrada. En el punto 10 el vecino podrá responder con el detalle necesario.</div></div>`;
    }
    if (estado === 'propuesta_enviada_solicitante') {
      return `<div class="ev-ssv-state ev-ssv-state-wait"><div class="ev-ssv-state-title">Esperando respuesta del solicitante</div><div class="ev-ssv-state-text">La propuesta fue enviada. La coordinación solo quedará confirmada cuando el vecino la acepte en su vista.</div></div>`;
    }
    if (estado === 'ajuste_solicitado') {
      return `<div class="ev-ssv-state ev-ssv-state-pending"><div class="ev-ssv-state-title">El solicitante pidió un ajuste</div><div class="ev-ssv-state-text">Revisa la solicitud y envía una propuesta actualizada.</div></div>`;
    }
    if (estado === 'coordinacion_confirmada') {
      return `<div class="ev-ssv-state ev-ssv-state-success"><div class="ev-ssv-state-title">Coordinación confirmada</div><div class="ev-ssv-state-text">Ambas partes ya tienen una propuesta registrada. La ejecución y confirmación final corresponden al punto 11.</div></div>`;
    }
    return `<div class="ev-ssv-state ev-ssv-state-negative"><div class="ev-ssv-state-title">${escapeHtml(item?.estado_texto || 'Solicitud cerrada')}</div><div class="ev-ssv-state-text">${escapeHtml(item?.motivo_estado || 'Esta solicitud ya no requiere una acción de tu parte.')}</div></div>`;
  }

  function actionsHtml(item) {
    const id = Number(item?.codigo_solicitud_servicio || 0);
    return `
      <button type="button" class="btn ev-ssv-btn-proposal" data-ssv-action="conversacion" data-id="${id}">
        <i class="bi bi-chat-square-text me-1"></i>Abrir conversación
      </button>
    `;
  }

  function renderCard(item) {
    const b = badge(item);
    const titulo = item?.titulo_servicio || 'Servicio';
    const fechaRegistro = formatFecha(item?.created_at, true);
    return `
      <article class="ev-ssv-card" data-id="${Number(item?.codigo_solicitud_servicio || 0)}">
        <div class="ev-ssv-card-head">
          <div class="ev-ssv-card-media"><img src="${escapeHtml(imageUrl(item?.imagen_portada))}" alt="${escapeHtml(titulo)}"></div>
          <div class="ev-ssv-card-head-main">
            <div class="ev-ssv-card-title-row">
              <div>
                <div class="ev-ssv-card-title">${escapeHtml(titulo)}</div>
                <div class="ev-ssv-card-meta">Solicitud #${Number(item?.codigo_solicitud_servicio || 0)} · ${escapeHtml(fechaRegistro)}</div>
              </div>
              <span class="${b.cls}">${escapeHtml(b.text)}</span>
            </div>
            <div class="ev-ssv-pills">
              <span class="ev-ssv-pill"><i class="bi bi-person"></i>${escapeHtml(item?.nombre_solicitante || 'Vecino')}</span>
              ${item?.categoria_nombre ? `<span class="ev-ssv-pill"><i class="bi bi-tags"></i>${escapeHtml(item.categoria_nombre)}</span>` : ''}
            </div>
          </div>
        </div>
        <div class="ev-ssv-card-body">
          <div class="ev-ssv-card-data">
            <div class="ev-ssv-data"><span>Fecha deseada</span><strong>${escapeHtml(rangoDeseado(item))}</strong></div>
            <div class="ev-ssv-data ev-ssv-data-price"><span>Precio referencial</span><strong>${escapeHtml(formatMoney(item?.precio_referencial))}</strong></div>
          </div>
          <div class="ev-ssv-info">
            <div class="ev-ssv-line"><span class="ev-ssv-line-label">Ubicación</span><span class="ev-ssv-line-value">${Number(item?.ubicacion_compartida || 0) === 1 ? 'Compartida en conversación' : 'El vecino la compartirá cuando sea necesaria para cotizar'}</span></div>
            <div class="ev-ssv-note"><span class="ev-ssv-note-label">Lo que necesita el vecino</span><div class="ev-ssv-note-text">${escapeHtml(item?.mensaje_solicitante || 'Sin detalle adicional.')}</div></div>
            ${propuestaHtml(item?.propuesta)}
          </div>
          ${stateHtml(item)}
          <div class="ev-ssv-actions">${actionsHtml(item)}</div>
        </div>
      </article>
    `;
  }

  function pintar(lista, target, empty) {
    if (!target || !empty) return;
    const items = Array.isArray(lista) ? lista : [];
    target.innerHTML = '';
    if (!items.length) { empty.classList.remove('d-none'); return; }
    empty.classList.add('d-none');
    target.innerHTML = items.map(renderCard).join('');
  }

  function mostrarTab(refs, tab) {
    tabActiva = tab;
    refs.tabButtons.forEach((b) => b.classList.toggle('active', b.dataset.tab === tab));
    refs.tabPendientes?.classList.toggle('d-none', tab !== 'pendientes');
    refs.tabEsperando?.classList.toggle('d-none', tab !== 'esperando');
    refs.tabCerradas?.classList.toggle('d-none', tab !== 'cerradas');
  }

  async function fetchLista() {
    const resp = await fetch(`${BASE}/api/servicios/solicitudes/proveedor`, { method: 'GET', credentials: 'include', headers: { Accept: 'application/json' }, cache: 'no-store' });
    const json = await resp.json().catch(() => ({}));
    if (await handleAuth(resp, json)) return { __authHandled: true };
    if (!resp.ok || json?.ok === false) throw new Error(json?.mensaje || 'No se pudieron cargar las solicitudes.');
    return json?.data || {};
  }

  async function cargar(opciones = {}) {
    const refs = getRefs();
    if (!refs.root || cargando) return;
    cargando = true;
    try {
      refs.error?.classList.add('d-none');
      const data = await fetchLista();
      if (data?.__authHandled) return;
      const pendientes = Array.isArray(data?.pendientes) ? data.pendientes : [];
      const esperando = Array.isArray(data?.esperando) ? data.esperando : [];
      const cerradas = Array.isArray(data?.cerradas) ? data.cerradas : [];
      cache = new Map([...pendientes, ...esperando, ...cerradas].map((it) => [Number(it.codigo_solicitud_servicio || 0), it]));
      const resumen = data?.resumen || {};
      const cp = Number(resumen.pendientes ?? pendientes.length);
      const ce = Number(resumen.esperando ?? esperando.length);
      const cc = Number(resumen.cerradas ?? cerradas.length);
      if (refs.countPendientes) refs.countPendientes.textContent = String(cp);
      if (refs.countEsperando) refs.countEsperando.textContent = String(ce);
      if (refs.countCerradas) refs.countCerradas.textContent = String(cc);
      if (refs.badgePendientes) refs.badgePendientes.textContent = String(cp);
      if (refs.badgeEsperando) refs.badgeEsperando.textContent = String(ce);
      if (refs.badgeCerradas) refs.badgeCerradas.textContent = String(cc);
      pintar(pendientes, refs.listaPendientes, refs.emptyPendientes);
      pintar(esperando, refs.listaEsperando, refs.emptyEsperando);
      pintar(cerradas, refs.listaCerradas, refs.emptyCerradas);
      mostrarTab(refs, tabActiva);
    } catch (e) {
      console.error('[SOLICITUDES_SERVICIO_VENDEDOR]', e);
      refs.error?.classList.remove('d-none');
    } finally {
      cargando = false;
    }
  }

  function cargarModal() {
    if (!window.Swal?.fire) return;
    Swal.fire(swalConfig({
      title: 'Procesando',
      html: '<div class="ev-ssv-swal-loader"></div><div class="ev-ssv-msg-text">Estamos registrando tu respuesta. Espera un momento.</div>',
      showConfirmButton: false,
      allowOutsideClick: false,
      allowEscapeKey: false
    }));
  }

  async function postJson(url, payload) {
    const resp = await fetch(url, {
      method: 'POST',
      credentials: 'include',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(payload || {})
    });
    const json = await resp.json().catch(() => ({}));
    if (await handleAuth(resp, json)) return { __authHandled: true, resp, json };
    return { resp, json };
  }

  async function pedirInformacion(item) {
    if (!window.Swal?.fire) return;
    const result = await Swal.fire(swalConfig({
      title: 'Solicitar información',
      html: messageHtml('info', 'Indica qué necesitas confirmar', 'El vecino verá esta solicitud en su próxima vista de coordinación.', 'Servicio', item?.titulo_servicio || 'Servicio seleccionado'),
      input: 'textarea',
      inputPlaceholder: 'Ejemplo: Indícame la cantidad, medidas, archivos, fotos, modelo o detalle adicional que necesitas.',
      inputAttributes: { maxlength: 1500, 'aria-label': 'Información solicitada' },
      showCancelButton: true,
      confirmButtonText: 'Enviar solicitud',
      cancelButtonText: 'Cancelar',
      preConfirm: (value) => {
        const text = String(value || '').trim();
        if (text.length < 8) { Swal.showValidationMessage('Describe la información que necesitas para continuar.'); return false; }
        return text;
      }
    }));
    if (!result.isConfirmed || !result.value) return;
    accionEnCurso = true;
    try {
      cargarModal();
      const { resp, json, __authHandled } = await postJson(`${BASE}/api/servicios/solicitudes/${Number(item.codigo_solicitud_servicio)}/solicitar-informacion`, { mensaje: result.value });
      if (__authHandled) return;
      if (!resp.ok || json?.ok === false) { await notify('error', 'No se pudo enviar', 'La solicitud sigue pendiente', json?.mensaje || 'Inténtalo nuevamente.', { cardLabel: 'Servicio', cardText: item.titulo_servicio }); return; }
      await notify('success', 'Información solicitada', 'El vecino fue notificado', json?.mensaje || 'Tu solicitud de información fue enviada.', { cardLabel: 'Servicio', cardText: item.titulo_servicio });
      tabActiva = 'esperando';
      await cargar({ silent: true });
    } finally { accionEnCurso = false; }
  }

  function propuestaFormHtml(item) {
    return `
      <div class="ev-ssv-form">
        <div class="ev-ssv-form-note"><strong>Propuesta flexible:</strong> completa únicamente las condiciones que realmente aplican al servicio. El vecino deberá confirmar o pedir un ajuste en el siguiente paso.</div>
        <div class="ev-ssv-form-grid">
          <div class="ev-ssv-field"><label>Modalidad</label><select id="ssvModalidad" class="form-select"><option value="a_coordinar">Por coordinar</option><option value="domicilio_solicitante">Domicilio del solicitante</option><option value="domicilio_proveedor">Domicilio o local del proveedor</option><option value="punto_encuentro">Punto de encuentro</option><option value="remoto">Atención remota o digital</option><option value="recojo_entrega">Recojo o entrega</option></select></div>
          <div class="ev-ssv-field"><label>Momento de atención</label><select id="ssvMomento" class="form-select"><option value="a_coordinar">A coordinar</option><option value="fecha_hora">Fecha y horario propuestos</option><option value="lo_antes_posible">Lo antes posible</option><option value="fecha_limite">Fecha límite</option></select></div>
        </div>
        <div class="ev-ssv-form-grid">
          <div id="ssvFechaWrap" class="ev-ssv-field d-none"><label id="ssvFechaLabel">Fecha propuesta</label><input id="ssvFecha" type="date" class="form-control"></div>
          <div id="ssvHorarioWrap" class="ev-ssv-field"><label>Horario o rango <small>(opcional)</small></label><input id="ssvHorario" type="text" maxlength="120" class="form-control" placeholder="Ej. 10:00 a. m. a 12:00 p. m."></div>
        </div>
        <div class="ev-ssv-field"><label>Alcance confirmado</label><textarea id="ssvAlcance" class="form-control" maxlength="1500" placeholder="Describe qué incluye la propuesta, cantidad, trabajo o entregable."></textarea></div>
        <div class="ev-ssv-form-grid">
          <div class="ev-ssv-field"><label>Condición de precio</label><select id="ssvTipoPrecio" class="form-select"><option value="a_cotizar">Cotización por coordinar</option><option value="fijo">Precio fijo</option><option value="por_hora">Precio por hora</option><option value="por_sesion">Precio por sesión</option><option value="por_unidad">Precio por unidad</option><option value="sin_costo">Sin costo</option><option value="pendiente_diagnostico">Cotización después del diagnóstico</option></select></div>
          <div id="ssvMontoWrap" class="ev-ssv-field d-none"><label>Monto propuesto</label><input id="ssvMonto" type="number" min="0" max="999999.99" step="0.01" class="form-control" placeholder="0.00"></div>
        </div>
        <div id="ssvUnidadWrap" class="ev-ssv-field d-none"><label>Etiqueta del precio <small>(opcional)</small></label><input id="ssvUnidad" type="text" maxlength="80" class="form-control" placeholder="Ej. por hora, por sesión o por unidad"></div>
        <div class="ev-ssv-form-grid">
          <div class="ev-ssv-field"><label>Duración estimada <small>(opcional)</small></label><input id="ssvDuracion" type="text" maxlength="160" class="form-control" placeholder="Ej. 2 horas / 3 sesiones"></div>
          <div class="ev-ssv-field"><label>Requisitos <small>(opcional)</small></label><input id="ssvRequisitos" type="text" maxlength="1500" class="form-control" placeholder="Ej. archivos, fotos, materiales o medidas"></div>
        </div>
        <div class="ev-ssv-field"><label>Mensaje para el solicitante</label><textarea id="ssvMensaje" class="form-control" maxlength="1500" placeholder="Explica brevemente cómo atenderás la solicitud."></textarea></div>
      </div>
    `;
  }

  function syncPropuestaForm(popup) {
    if (!popup) return;
    const momento = popup.querySelector('#ssvMomento')?.value || 'a_coordinar';
    const tipoPrecio = popup.querySelector('#ssvTipoPrecio')?.value || 'a_cotizar';
    const fechaWrap = popup.querySelector('#ssvFechaWrap');
    const fechaLabel = popup.querySelector('#ssvFechaLabel');
    const montoWrap = popup.querySelector('#ssvMontoWrap');
    const unidadWrap = popup.querySelector('#ssvUnidadWrap');
    const fechaNeeded = ['fecha_hora', 'fecha_limite'].includes(momento);
    const montoNeeded = ['fijo', 'por_hora', 'por_sesion', 'por_unidad'].includes(tipoPrecio);
    const unitNeeded = ['por_hora', 'por_sesion', 'por_unidad'].includes(tipoPrecio);
    fechaWrap?.classList.toggle('d-none', !fechaNeeded);
    montoWrap?.classList.toggle('d-none', !montoNeeded);
    unidadWrap?.classList.toggle('d-none', !unitNeeded);
    if (fechaLabel) fechaLabel.textContent = momento === 'fecha_limite' ? 'Fecha límite' : 'Fecha propuesta';
  }

  async function abrirPropuesta(item) {
    if (await asegurarConversacionServicio()) {
      window.EVServicioConversacion.open(Number(item?.codigo_solicitud_servicio || 0), { openProposal: true });
      return;
    }

    if (!window.Swal?.fire) return;
    const result = await Swal.fire(swalConfig({
      title: 'Enviar propuesta',
      html: propuestaFormHtml(item),
      width: 760,
      showCancelButton: true,
      confirmButtonText: 'Enviar propuesta',
      cancelButtonText: 'Cancelar',
      didOpen: () => {
        const popup = Swal.getPopup();
        const min = new Date();
        min.setHours(0, 0, 0, 0);
        const fecha = popup.querySelector('#ssvFecha');
        if (fecha) fecha.min = min.toISOString().slice(0, 10);
        popup.querySelector('#ssvMomento')?.addEventListener('change', () => syncPropuestaForm(popup));
        popup.querySelector('#ssvTipoPrecio')?.addEventListener('change', () => syncPropuestaForm(popup));
        syncPropuestaForm(popup);
      },
      preConfirm: () => {
        const popup = Swal.getPopup();
        const get = (sel) => String(popup.querySelector(sel)?.value || '').trim();
        const momento = get('#ssvMomento');
        const fecha = get('#ssvFecha');
        const tipoPrecio = get('#ssvTipoPrecio');
        const alcance = get('#ssvAlcance');
        const mensaje = get('#ssvMensaje');
        const monto = get('#ssvMonto');
        if (['fecha_hora', 'fecha_limite'].includes(momento) && !fecha) { Swal.showValidationMessage('Indica la fecha propuesta o fecha límite.'); return false; }
        if (alcance.length < 8) { Swal.showValidationMessage('Describe el alcance confirmado del servicio.'); return false; }
        if (mensaje.length < 8) { Swal.showValidationMessage('Escribe un mensaje claro para el solicitante.'); return false; }
        if (['fijo', 'por_hora', 'por_sesion', 'por_unidad'].includes(tipoPrecio) && (!monto || Number(monto) <= 0)) { Swal.showValidationMessage('Indica un monto válido para la propuesta.'); return false; }
        return {
          modalidad: get('#ssvModalidad'),
          momento_tipo: momento,
          fecha_propuesta: fecha,
          horario_propuesto: get('#ssvHorario'),
          alcance_confirmado: alcance,
          tipo_precio: tipoPrecio,
          monto_propuesto: monto,
          unidad_precio: get('#ssvUnidad'),
          duracion_estimada: get('#ssvDuracion'),
          requisitos: get('#ssvRequisitos'),
          mensaje_proveedor: mensaje
        };
      }
    }));
    if (!result.isConfirmed || !result.value) return;
    accionEnCurso = true;
    try {
      cargarModal();
      const { resp, json, __authHandled } = await postJson(`${BASE}/api/servicios/solicitudes/${Number(item.codigo_solicitud_servicio)}/propuesta`, result.value);
      if (__authHandled) return;
      if (!resp.ok || json?.ok === false) { await notify('error', 'No se pudo enviar', 'La propuesta no fue registrada', json?.mensaje || 'Revisa los datos e inténtalo nuevamente.', { cardLabel: 'Servicio', cardText: item.titulo_servicio }); return; }
      await notify('success', 'Propuesta enviada', 'El solicitante fue notificado', json?.mensaje || 'La propuesta fue enviada correctamente.', { cardLabel: 'Servicio', cardText: item.titulo_servicio });
      tabActiva = 'esperando';
      await cargar({ silent: true });
    } finally { accionEnCurso = false; }
  }

  async function rechazar(item) {
    if (!window.Swal?.fire) return;
    const result = await Swal.fire(swalConfig({
      title: 'Rechazar solicitud',
      html: messageHtml('warning', 'Indica un motivo claro y cordial', 'El vecino verá este motivo para entender por qué no podrás atender el servicio.', 'Servicio', item?.titulo_servicio || 'Servicio seleccionado'),
      input: 'textarea',
      inputPlaceholder: 'Ejemplo: Esta semana no tengo disponibilidad para atender este servicio.',
      inputAttributes: { maxlength: 500, 'aria-label': 'Motivo de rechazo' },
      showCancelButton: true,
      confirmButtonText: 'Rechazar solicitud',
      cancelButtonText: 'Volver',
      preConfirm: (value) => {
        const text = String(value || '').trim();
        if (text.length < 5) { Swal.showValidationMessage('Indica un motivo de rechazo.'); return false; }
        return text;
      }
    }));
    if (!result.isConfirmed || !result.value) return;
    accionEnCurso = true;
    try {
      cargarModal();
      const { resp, json, __authHandled } = await postJson(`${BASE}/api/servicios/solicitudes/${Number(item.codigo_solicitud_servicio)}/rechazar`, { motivo_rechazo: result.value });
      if (__authHandled) return;
      if (!resp.ok || json?.ok === false) { await notify('error', 'No se pudo rechazar', 'La solicitud conserva su estado actual', json?.mensaje || 'Inténtalo nuevamente.', { cardLabel: 'Servicio', cardText: item.titulo_servicio }); return; }
      await notify('success', 'Solicitud rechazada', 'El vecino fue notificado', json?.mensaje || 'La solicitud fue cerrada correctamente.', { cardLabel: 'Servicio', cardText: item.titulo_servicio });
      tabActiva = 'cerradas';
      await cargar({ silent: true });
    } finally { accionEnCurso = false; }
  }

  function detalleHtml(item) {
    const propuesta = item?.propuesta;
    const precioPropuesta = propuesta
      ? (propuesta.monto_propuesto !== null ? `${formatMoney(propuesta.monto_propuesto)}${propuesta.unidad_precio ? ` ${escapeHtml(propuesta.unidad_precio)}` : ''}` : escapeHtml(propuesta.tipo_precio_texto || 'Por coordinar'))
      : '';
    return `
      <div class="ev-ssv-detail">
        <div class="ev-ssv-detail-top">
          <div class="ev-ssv-detail-media"><img src="${escapeHtml(imageUrl(item?.imagen_portada))}" alt="${escapeHtml(item?.titulo_servicio || 'Servicio')}"></div>
          <div><div class="ev-ssv-detail-title">${escapeHtml(item?.titulo_servicio || 'Servicio')}</div><div class="ev-ssv-detail-sub">Solicitud #${Number(item?.codigo_solicitud_servicio || 0)} · ${escapeHtml(formatFecha(item?.created_at, true))}</div><div class="ev-ssv-pills"><span class="ev-ssv-pill"><i class="bi bi-person"></i>${escapeHtml(item?.nombre_solicitante || 'Vecino')}</span>${item?.categoria_nombre ? `<span class="ev-ssv-pill"><i class="bi bi-tags"></i>${escapeHtml(item.categoria_nombre)}</span>` : ''}</div></div>
        </div>
        <div class="ev-ssv-detail-grid">
          <div class="ev-ssv-detail-box"><span>Precio referencial</span><strong>${escapeHtml(formatMoney(item?.precio_referencial))}</strong></div>
          <div class="ev-ssv-detail-box"><span>Fecha deseada</span><strong>${escapeHtml(rangoDeseado(item))}</strong></div>
          <div class="ev-ssv-detail-box"><span>Ubicación</span><strong>${Number(item?.ubicacion_compartida || 0) === 1 ? 'Compartida dentro de la conversación' : 'Se comparte cuando sea necesaria para cotizar'}</strong></div>
          <div class="ev-ssv-detail-box"><span>Estado</span><strong>${escapeHtml(item?.estado_texto || '—')}</strong></div>
        </div>
        <div class="ev-ssv-detail-section"><h6>Solicitud del vecino</h6><p>${escapeHtml(item?.mensaje_solicitante || 'Sin detalle adicional.')}</p></div>
        ${propuesta ? `<div class="ev-ssv-detail-section"><h6>Propuesta enviada · Versión ${Number(propuesta.version || 1)}</h6><p><strong>Modalidad:</strong> ${escapeHtml(propuesta.modalidad_texto || 'Por coordinar')}\n<strong>Momento:</strong> ${escapeHtml(propuesta.momento_texto || 'A coordinar')}\n${propuesta.fecha_propuesta ? `<strong>Fecha:</strong> ${escapeHtml(formatFecha(propuesta.fecha_propuesta))}\n` : ''}${propuesta.horario_propuesto ? `<strong>Horario:</strong> ${escapeHtml(propuesta.horario_propuesto)}\n` : ''}<strong>Precio:</strong> ${precioPropuesta}\n<strong>Alcance:</strong> ${escapeHtml(propuesta.alcance_confirmado || '—')}${propuesta.duracion_estimada ? `\n<strong>Duración:</strong> ${escapeHtml(propuesta.duracion_estimada)}` : ''}${propuesta.requisitos ? `\n<strong>Requisitos:</strong> ${escapeHtml(propuesta.requisitos)}` : ''}\n<strong>Mensaje:</strong> ${escapeHtml(propuesta.mensaje_proveedor || '—')}</p></div>` : ''}
        ${item?.motivo_estado ? `<div class="ev-ssv-detail-section"><h6>Última actualización</h6><p>${escapeHtml(item.motivo_estado)}</p></div>` : ''}
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
    await Swal.fire(swalConfig({ title: 'Detalle de solicitud', html: detalleHtml(item), width: 800, confirmButtonText: 'Cerrar' }));
  }

  async function manejarClick(e) {
    const root = document.querySelector('.ev-ssv-page');
    if (!root || accionEnCurso) return;
    const btn = e.target.closest('[data-ssv-action]');
    if (!btn) return;
    const id = Number(btn.dataset.id || 0);
    const action = btn.dataset.ssvAction;
    const item = cache.get(id);
    if (!id || !item) return;
    if (action === 'conversacion' || action === 'detalle') await detalle(item);
    if (action === 'informacion') await pedirInformacion(item);
    if (action === 'propuesta') await abrirPropuesta(item);
    if (action === 'rechazar') await rechazar(item);
  }

  function bind() {
    const refs = getRefs();
    refs.tabButtons.forEach((btn) => {
      if (btn.dataset.evBound === '1') return;
      btn.dataset.evBound = '1';
      btn.addEventListener('click', () => mostrarTab(getRefs(), btn.dataset.tab || 'pendientes'));
    });
    if (refs.refresh && refs.refresh.dataset.evBound !== '1') {
      refs.refresh.dataset.evBound = '1';
      refs.refresh.addEventListener('click', () => cargar({ silent: true }));
    }
  }

  function stopPolling() { if (pollingTimer) { clearInterval(pollingTimer); pollingTimer = null; } }
  function startPolling() {
    stopPolling();
    pollingTimer = setInterval(() => {
      if (!vistaActiva || document.hidden || !document.querySelector('.ev-ssv-page')) return;
      cargar({ silent: true });
    }, POLLING_MS);
  }

  function init() {
    const refs = getRefs();
    if (!refs.root) { vistaActiva = false; stopPolling(); return; }
    vistaActiva = true;
    bind();
    mostrarTab(refs, tabActiva);
    cargar({ silent: false });
    startPolling();
  }

  document.addEventListener('click', manejarClick);
  document.addEventListener('visibilitychange', () => { if (!document.hidden && document.querySelector('.ev-ssv-page')) cargar({ silent: true }); });
  document.addEventListener('ev:content-loaded', () => { if (document.querySelector('.ev-ssv-page')) init(); else { vistaActiva = false; stopPolling(); } });

  window.EVSolicitudesServicioVendedor = { init, refresh: () => cargar({ silent: true }) };
  if (document.querySelector('.ev-ssv-page')) init();
})();
