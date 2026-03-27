/* views/js/misPedidosVendedor.js */
(function () {
  'use strict';

  if (window.__EV_MIS_PEDIDOS_VENDEDOR_V8__ === true) {
    if (window.EVMisPedidosVendedor && typeof window.EVMisPedidosVendedor.init === 'function') {
      window.EVMisPedidosVendedor.init();
    }
    return;
  }
  window.__EV_MIS_PEDIDOS_VENDEDOR_V8__ = true;

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const POLLING_MS = 5000;
  const PLACEHOLDER = `${BASE}/public/img/placeholder-ev.png`;

  let pollingTimer = null;
  let ultimoSnapshotPendientes = new Set();
  let alertasMostradas = new Set();
  let vistaActiva = false;
  let tabActiva = 'pendientes';
  let cachePedidos = new Map();

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

  function formatFechaSolo(v) {
    if (!v) return '—';
    const raw = String(v).trim();
    const d = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return raw;
    return d.toLocaleDateString('es-PE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    });
  }

  function formatHoraSolo(v) {
    if (!v) return '—';
    const raw = String(v).trim();
    const d = new Date(raw.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return raw;
    return d.toLocaleTimeString('es-PE', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
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
      return { texto: 'Pendiente', clase: 'ev-mpv-badge ev-mpv-badge-pendiente' };
    }

    if (
      e === 'en_preparacion' ||
      e === 'despachando' ||
      e === 'listo_para_entrega' ||
      e === 'en_camino' ||
      e === 'en_punto_entrega' ||
      e === 'entregado_vendedor'
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

    const flujo = requierePreparacion
      ? ['en_preparacion', 'listo_para_entrega', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador']
      : ['despachando', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador'];

    const finales = ['rechazado_vendedor', 'cancelado_vendedor', 'cancelado_comprador', 'sin_respuesta_vendedor'];

    if (finales.includes(estado)) {
      return `
        <div class="ev-mpv-stepper ev-mpv-stepper-final">
          <div class="ev-mpv-step is-final">${escapeHtml(estadoLegible(estado))}</div>
        </div>
      `;
    }

    const html = flujo.map((step, index) => {
      const currentIndex = flujo.indexOf(estado);
      const isDone = currentIndex > index;
      const isCurrent = currentIndex === index;

      return `
        <div class="ev-mpv-step ${isDone ? 'is-done' : ''} ${isCurrent ? 'is-current' : ''}">
          <span class="ev-mpv-step-dot"></span>
          <span class="ev-mpv-step-text">${escapeHtml(estadoLegible(step))}</span>
        </div>
      `;
    }).join('');

    return `<div class="ev-mpv-stepper">${html}</div>`;
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
            Atiende esta solicitud antes de que finalice el tiempo de espera.
          </div>
          ${
            item.tiempo_restante_segundos !== null
              ? `<div class="ev-mpv-time-pill"><i class="bi bi-clock-history"></i>${escapeHtml(formatTiempoCorto(item.tiempo_restante_segundos))}</div>`
              : ''
          }
        </div>
      `;
    }

    if (estado === 'entregado_vendedor') {
      return `
        <div class="ev-mpv-state-box ev-mpv-state-box-info">
          <div class="ev-mpv-state-title">Esperando confirmación del comprador</div>
          <div class="ev-mpv-state-text">
            Ya marcaste el pedido como entregado. Ahora el comprador debe confirmar la recepción.
          </div>
        </div>
      `;
    }

    if (
      estado === 'rechazado_vendedor' ||
      estado === 'cancelado_vendedor' ||
      estado === 'cancelado_comprador' ||
      estado === 'sin_respuesta_vendedor' ||
      estado === 'entrega_confirmada_comprador'
    ) {
      return `
        <div class="ev-mpv-state-box ev-mpv-state-box-final">
          <div class="ev-mpv-state-title">${escapeHtml(estadoLegible(estado))}</div>
          <div class="ev-mpv-state-text">
            ${escapeHtml(item.motivo_estado || 'Este pedido ya se encuentra cerrado.')}
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
    const pills = [];

    pills.push(`
      <span class="ev-mpv-pill">
        <i class="bi bi-box-seam"></i>
        Cant. ${escapeHtml(item.cantidad || 0)}
      </span>
    `);

    pills.push(`
      <span class="ev-mpv-pill">
        <i class="bi bi-lightning-charge"></i>
        ${escapeHtml(item.tipo_entrega || 'Inmediata')}
      </span>
    `);

    return pills.join('');
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
              <div>
                <div class="ev-mpv-order-title">${escapeHtml(item.titulo_publicacion || 'Pedido')}</div>
                <div class="ev-mpv-order-meta">
                  Pedido #${Number(item.codigo_pedido || 0)} · ${escapeHtml(formatFechaSolo(fechaBase))} ${escapeHtml(formatHoraSolo(fechaBase))}
                </div>
              </div>
              <span class="${badge.clase}">${escapeHtml(badge.texto)}</span>
            </div>

            <div class="ev-mpv-order-quick">
              ${renderQuickPills(item)}
            </div>

            <div class="ev-mpv-order-data">
              <div class="ev-mpv-data-box ev-mpv-data-box-buyer">
                <span>Comprador</span>
                <strong>${escapeHtml(item.nombre_vecino || 'Vecino')}</strong>
              </div>

              <div class="ev-mpv-data-box ev-mpv-data-box-total">
                <span>Total</span>
                <strong>S/ ${escapeHtml(formatMoney(item.monto_total || item.total || 0))}</strong>
              </div>

              <div class="ev-mpv-data-box ev-mpv-data-box-date">
                <span>Fecha</span>
                <strong>${escapeHtml(formatFechaSolo(fechaBase))}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="ev-mpv-order-body">
          <div class="ev-mpv-section-title">
            <i class="bi bi-diagram-3"></i>
            Flujo del pedido
          </div>

          ${getLineaEstado(item)}

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

  async function fetchPedidos() {
    const resp = await fetch(`${BASE}/api/pedidos/mis`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    });

    const json = await resp.json().catch(() => ({}));

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

    await Swal.fire({
      icon: 'info',
      title: 'Nueva solicitud recibida',
      text: `Tienes una nueva solicitud para: ${item.titulo_publicacion || 'tu publicación'}.`,
      confirmButtonText: 'Ver ahora',
      confirmButtonColor: '#EA7C12'
    });

    const refs = getRefs();
    showTab(refs, 'pendientes');
  }

  async function cargarPedidos(opciones = {}) {
    const refs = getRefs();
    if (!refs.root) return;

    const silent = opciones.silent === true;

    try {
      refs.errorBox?.classList.add('d-none');

      const data = await fetchPedidos();
      refrescarCache(data);

      const pendientes = Array.isArray(data.pendientes) ? data.pendientes : [];
      const proceso = Array.isArray(data.en_proceso) ? data.en_proceso : [];
      const finalizadosRaw = Array.isArray(data.finalizados) ? data.finalizados : [];

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

      const snapshotNuevo = new Set(pendientes.map((x) => Number(x.codigo_pedido || 0)).filter(Boolean));

      if (!silent && ultimoSnapshotPendientes.size === 0) {
        ultimoSnapshotPendientes = snapshotNuevo;
        return;
      }

      const nuevos = pendientes.filter((item) => {
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
    }
  }

  async function aceptar(id) {
    if (!window.Swal) return;

    const c = await Swal.fire({
      icon: 'question',
      title: 'Aceptar solicitud',
      text: '¿Deseas aceptar esta solicitud?',
      showCancelButton: true,
      confirmButtonText: 'Sí, aceptar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12'
    });

    if (!c.isConfirmed) return;

    const resp = await fetch(`${BASE}/api/pedidos/${id}/aceptar`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    });

    const json = await resp.json().catch(() => ({}));

    if (!resp.ok || json?.ok === false) {
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo aceptar',
        text: json?.mensaje || 'No se pudo aceptar la solicitud.',
        confirmButtonColor: '#EA7C12'
      });
      return;
    }

    await Swal.fire({
      icon: 'success',
      title: 'Solicitud aceptada',
      text: json?.mensaje || 'La solicitud fue aceptada correctamente.',
      confirmButtonColor: '#EA7C12'
    });

    tabActiva = 'proceso';
    await cargarPedidos({ silent: true });
  }

  async function rechazar(id) {
    if (!window.Swal) return;

    const r = await Swal.fire({
      title: 'Rechazar solicitud',
      input: 'textarea',
      inputLabel: 'Motivo',
      inputPlaceholder: 'Escribe el motivo del rechazo...',
      showCancelButton: true,
      confirmButtonText: 'Rechazar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12',
      preConfirm: (v) => {
        const txt = String(v || '').trim();
        if (!txt) {
          Swal.showValidationMessage('Debes indicar un motivo.');
          return false;
        }
        return txt;
      }
    });

    if (!r.isConfirmed || !r.value) return;

    const resp = await fetch(`${BASE}/api/pedidos/${id}/rechazar`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ motivo_rechazo: r.value })
    });

    const json = await resp.json().catch(() => ({}));

    if (!resp.ok || json?.ok === false) {
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo rechazar',
        text: json?.mensaje || 'No se pudo rechazar la solicitud.',
        confirmButtonColor: '#EA7C12'
      });
      return;
    }

    await Swal.fire({
      icon: 'success',
      title: 'Solicitud rechazada',
      text: json?.mensaje || 'La solicitud fue rechazada correctamente.',
      confirmButtonColor: '#EA7C12'
    });

    tabActiva = 'rechazadas';
    await cargarPedidos({ silent: true });
  }

  async function cambiarEstado(id, estado) {
    if (!window.Swal) return;

    const etiquetas = {
      listo_para_entrega: 'marcar como listo para entrega',
      en_camino: 'marcar como en camino',
      en_punto_entrega: 'marcar como en punto de entrega',
      entregado_vendedor: 'marcar como entregado'
    };

    const textoAccion = etiquetas[String(estado || '').trim()] || 'actualizar el estado';

    const r = await Swal.fire({
      icon: 'question',
      title: 'Actualizar estado',
      text: `¿Deseas ${textoAccion}?`,
      showCancelButton: true,
      confirmButtonText: 'Sí, continuar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12'
    });

    if (!r.isConfirmed) return;

    const resp = await fetch(`${BASE}/api/pedidos/${id}/estado`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ nuevo_estado: estado })
    });

    const json = await resp.json().catch(() => ({}));

    if (!resp.ok || json?.ok === false) {
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo actualizar',
        text: json?.mensaje || 'No se pudo actualizar el estado del pedido.',
        confirmButtonColor: '#EA7C12'
      });
      return;
    }

    await Swal.fire({
      icon: 'success',
      title: 'Estado actualizado',
      text: json?.mensaje || 'El estado del pedido fue actualizado correctamente.',
      confirmButtonColor: '#EA7C12'
    });

    await cargarPedidos({ silent: true });
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
                <span>Comprador</span>
                <strong>${escapeHtml(item.nombre_vecino || 'Vecino')}</strong>
              </div>
              <div class="ev-mpv-modal-item">
                <span>Total</span>
                <strong>S/ ${escapeHtml(formatMoney(item.monto_total || item.total || 0))}</strong>
              </div>
              <div class="ev-mpv-modal-item">
                <span>Cantidad</span>
                <strong>${escapeHtml(item.cantidad || 0)}</strong>
              </div>
              <div class="ev-mpv-modal-item">
                <span>Entrega</span>
                <strong>${escapeHtml(item.tipo_entrega || 'Inmediata')}</strong>
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

    await Swal.fire({
      title: 'Detalle del pedido',
      html: buildDetalleHtml(item),
      width: 860,
      confirmButtonText: 'Cerrar',
      confirmButtonColor: '#EA7C12',
      customClass: {
        popup: 'ev-mpv-swal-popup'
      }
    });
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