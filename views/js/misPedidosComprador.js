/* views/js/misPedidosComprador.js */
(function () {
  'use strict';

  if (window.__EV_MIS_PEDIDOS_COMPRADOR_V4__ === true) {
    if (window.EVMisPedidosComprador && typeof window.EVMisPedidosComprador.init === 'function') {
      window.EVMisPedidosComprador.init();
    }
    return;
  }
  window.__EV_MIS_PEDIDOS_COMPRADOR_V4__ = true;

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const PLACEHOLDER = `${BASE}/public/img/placeholder-ev.png`;
  const POLLING_MS = 8000;

  let tabActiva = 'pendientes';
  let cachePedidos = new Map();
  let pollingTimer = null;
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

  function esEstadoCola(estado) {
    return [
      'cola_aceptada',
      'cola_pendiente_confirmacion'
    ].includes(String(estado || '').trim());
  }

  function esEstadoPendiente(estado) {
    return [
      'pendiente_vendedor',
      'cola_aceptada',
      'cola_pendiente_confirmacion'
    ].includes(String(estado || '').trim());
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
      cola_aceptada: 'En cola',
      cola_pendiente_confirmacion: 'Pendiente de confirmación de cola',
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

    if (e === 'cola_aceptada' || e === 'cola_pendiente_confirmacion') {
      return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-info' };
    }

    if (esEstadoNegativo(e)) {
      return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-negative' };
    }

    if (esEstadoInfo(e)) {
      return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-info' };
    }

    if (
      e === 'en_preparacion' ||
      e === 'despachando' ||
      e === 'listo_para_entrega' ||
      e === 'en_camino' ||
      e === 'en_punto_entrega' ||
      e === 'entrega_confirmada_comprador'
    ) {
      return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-proceso' };
    }

    return { texto: estadoLegible(e), clase: 'ev-mpc-badge ev-mpc-badge-final' };
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

  function getLineaEstado(item) {
    const estado = String(item.estado_actual || '').trim();
    const requierePreparacion = Number(item.requiere_preparacion || 0) === 1;

    if (esEstadoCola(estado)) {
      return `
        <div class="ev-mpc-stepper ev-mpc-stepper-final">
          <div class="ev-mpc-step is-final is-current">
            <span class="ev-mpc-step-dot"></span>
            <span class="ev-mpc-step-text">${escapeHtml(estadoLegible(estado))}</span>
          </div>
        </div>
      `;
    }

    const flujo = requierePreparacion
      ? ['pendiente_vendedor', 'en_preparacion', 'listo_para_entrega', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador']
      : ['pendiente_vendedor', 'despachando', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador'];

    if (esEstadoNegativo(estado)) {
      return `
        <div class="ev-mpc-stepper ev-mpc-stepper-final">
          <div class="ev-mpc-step is-final is-negative">
            <span class="ev-mpc-step-dot"></span>
            <span class="ev-mpc-step-text">${escapeHtml(estadoLegible(estado))}</span>
          </div>
        </div>
      `;
    }

    const currentIndex = flujo.indexOf(estado);

    return `
      <div class="ev-mpc-stepper">
        ${flujo.map((step, index) => {
          const isDone = currentIndex > index;
          const isCurrent = currentIndex === index;

          return `
            <div class="ev-mpc-step ${isDone ? 'is-done' : ''} ${isCurrent ? 'is-current' : ''}">
              <span class="ev-mpc-step-dot"></span>
              <span class="ev-mpc-step-text">${escapeHtml(estadoLegible(step))}</span>
            </div>
          `;
        }).join('')}
      </div>
    `;
  }

  function renderResumenEstado(item) {
    const estado = String(item.estado_actual || '').trim();
    const posicionCola = Number(item.posicion_cola || 0);

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

    if (estado === 'cola_pendiente_confirmacion') {
      return `
        <div class="ev-mpc-state-box ev-mpc-state-box-info">
          <div class="ev-mpc-state-title">Esperando confirmación de cola</div>
          <div class="ev-mpc-state-text">
            ${escapeHtml(item.mensaje_estado || item.motivo_estado || 'Debes decidir si deseas continuar esperando en la cola de atención.')}
          </div>
        </div>
      `;
    }

    if (estado === 'cola_aceptada') {
      return `
        <div class="ev-mpc-state-box ev-mpc-state-box-info">
          <div class="ev-mpc-state-title">Solicitud en cola</div>
          <div class="ev-mpc-state-text">
            ${escapeHtml(item.mensaje_estado || item.motivo_estado || 'Tu solicitud está en cola y avanzará cuando el vendedor termine el pedido anterior.')}
            ${posicionCola > 1 ? ` Actualmente estás en la posición ${posicionCola}.` : ''}
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

    if (Number(item.posicion_cola || 0) > 1) {
      pills.push(`
        <span class="ev-mpc-pill">
          <i class="bi bi-list-ol"></i>
          Cola #${escapeHtml(item.posicion_cola)}
        </span>
      `);
    }

    return pills.join('');
  }

  function renderFlujo(item) {
    if (esEstadoNegativo(item.estado_actual) || esEstadoCola(item.estado_actual)) {
      return `
        <div class="ev-mpc-section-title">
          <i class="bi bi-diagram-3"></i>
          Estado del pedido
        </div>
        ${getLineaEstado(item)}
      `;
    }

    return `
      <div class="ev-mpc-section-title">
        <i class="bi bi-diagram-3"></i>
        Flujo del pedido
      </div>
      ${getLineaEstado(item)}
    `;
  }

  function renderCard(item) {
    const badge = badgeEstado(item.estado_actual);
    const imagen = normalizarUrlImagen(item.imagen_portada_url || item.imagen_portada);
    const acciones = [];
    const estado = String(item.estado_actual || '').trim();
    const tieneProgramacion = String(item.tipo_entrega_raw || '').trim() === 'programada' && !!item.fecha_hora_programada;
    const puedeCancelar = Number(item.puede_cancelar || 0) === 1;
    const puedeConfirmarCola = Number(item.puede_confirmar_cola || 0) === 1;

    if (estado === 'entregado_vendedor') {
      acciones.push(`
        <button type="button" class="btn ev-mpc-btn-primary" data-action="confirmar-entrega" data-id="${Number(item.codigo_pedido || 0)}">
          <i class="bi bi-check2-circle me-1"></i>Confirmar entrega
        </button>
      `);
    }

    if (puedeConfirmarCola) {
      acciones.push(`
        <button type="button" class="btn ev-mpc-btn-primary" data-action="confirmar-cola" data-id="${Number(item.codigo_pedido || 0)}">
          <i class="bi bi-check2-circle me-1"></i>Aceptar cola
        </button>
      `);
    }

    if (puedeCancelar) {
      acciones.push(`
        <button type="button" class="btn ev-mpc-btn-outline" data-action="cancelar-solicitud" data-id="${Number(item.codigo_pedido || 0)}">
          <i class="bi bi-x-circle me-1"></i>Cancelar
        </button>
      `);
    }

    acciones.push(`
      <button type="button" class="btn ev-mpc-btn-outline" data-action="detalle" data-id="${Number(item.codigo_pedido || 0)}">
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
            <div class="ev-mpc-order-head-row">
              <div class="ev-mpc-order-head-main">
                <div class="ev-mpc-order-title">${escapeHtml(item.titulo_publicacion || 'Pedido')}</div>
                <div class="ev-mpc-order-meta">
                  Pedido #${Number(item.codigo_pedido || 0)} · ${escapeHtml(formatFecha(item.fecha_hora || item.created_at || null))}
                </div>
              </div>
              <span class="${badge.clase}">${escapeHtml(badge.texto)}</span>
            </div>

            <div class="ev-mpc-order-quick">
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
              Number(item.posicion_cola || 0) > 1
                ? `
                <div class="ev-mpc-line">
                  <span class="ev-mpc-line-label">Posición en cola</span>
                  <span class="ev-mpc-line-value">#${escapeHtml(item.posicion_cola)}</span>
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

  async function cargarPedidos(opciones = {}) {
    const refs = getRefs();
    if (!refs.root || cargando) return;

    cargando = true;

    try {
      if (!opciones.silent) {
        refs.errorBox?.classList.add('d-none');
      }

      const data = await fetchPedidos();
      refrescarCache(data);

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

    if (!resp.ok || json?.ok === false) {
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo confirmar',
        text: json?.mensaje || 'No se pudo confirmar la entrega.',
        confirmButtonColor: '#EA7C12'
      });
      return;
    }

    await Swal.fire({
      icon: 'success',
      title: 'Entrega confirmada',
      text: json?.mensaje || 'La entrega fue confirmada correctamente.',
      confirmButtonColor: '#EA7C12'
    });

    tabActiva = 'finalizados';
    await cargarPedidos({ silent: true });
  }

  async function confirmarCola(codigoPedido) {
    if (!window.Swal) return;

    const r = await Swal.fire({
      icon: 'question',
      title: 'Aceptar espera en cola',
      text: '¿Deseas continuar esperando en la cola de atención del vendedor?',
      showCancelButton: true,
      confirmButtonText: 'Sí, aceptar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12'
    });

    if (!r.isConfirmed) return;

    const resp = await fetch(`${BASE}/api/pedidos/${codigoPedido}/confirmar-cola`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({})
    });

    const json = await resp.json().catch(() => ({}));

    if (!resp.ok || json?.ok === false) {
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo confirmar',
        text: json?.mensaje || 'No se pudo confirmar la cola.',
        confirmButtonColor: '#EA7C12'
      });
      return;
    }

    await Swal.fire({
      icon: 'success',
      title: 'Cola aceptada',
      text: json?.mensaje || 'Aceptaste continuar en la cola.',
      confirmButtonColor: '#EA7C12'
    });

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

    if (!resp.ok || json?.ok === false) {
      await Swal.fire({
        icon: 'error',
        title: 'No se pudo cancelar',
        text: json?.mensaje || 'No se pudo cancelar la solicitud.',
        confirmButtonColor: '#EA7C12'
      });
      return;
    }

    await Swal.fire({
      icon: 'success',
      title: 'Solicitud cancelada',
      text: json?.mensaje || 'Tu solicitud fue cancelada correctamente.',
      confirmButtonColor: '#EA7C12'
    });

    await cargarPedidos({ silent: true });
  }

  function buildDetalleHtml(item) {
    const imagen = normalizarUrlImagen(item.imagen_portada_url || item.imagen_portada);
    const badge = badgeEstado(item.estado_actual);
    const tieneProgramacion = String(item.tipo_entrega_raw || '').trim() === 'programada' && !!item.fecha_hora_programada;

    return `
      <div class="ev-mpc-modal-detail">
        <div class="ev-mpc-modal-top">
          <div class="ev-mpc-modal-media">
            <img src="${escapeHtml(imagen)}" alt="${escapeHtml(item.titulo_publicacion || 'Pedido')}">
          </div>

          <div class="ev-mpc-modal-main">
            <div class="ev-mpc-modal-head">
              <div>
                <div class="ev-mpc-modal-title">${escapeHtml(item.titulo_publicacion || 'Pedido')}</div>
                <div class="ev-mpc-modal-subtitle">
                  Pedido #${Number(item.codigo_pedido || 0)} · ${escapeHtml(formatFecha(item.fecha_hora || item.created_at || null))}
                </div>
              </div>
              <span class="${badge.clase}">${escapeHtml(badge.texto)}</span>
            </div>

            <div class="ev-mpc-modal-grid">
              <div class="ev-mpc-modal-item">
                <span>Fecha</span>
                <strong>${escapeHtml(formatFecha(item.fecha_hora || item.created_at || null))}</strong>
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

        <div class="ev-mpc-modal-section">
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

          ${
            Number(item.posicion_cola || 0) > 1
              ? `
              <div class="ev-mpc-modal-row">
                <span>Posición en cola</span>
                <strong>#${escapeHtml(item.posicion_cola)}</strong>
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
    const item = cachePedidos.get(Number(id || 0));
    if (!window.Swal || !item) return;

    await Swal.fire({
      title: 'Detalle del pedido',
      html: buildDetalleHtml(item),
      width: 880,
      confirmButtonText: 'Cerrar',
      confirmButtonColor: '#EA7C12',
      customClass: {
        popup: 'ev-mpc-swal-popup'
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
      if (!document.querySelector('.ev-mpc-page')) return;
      cargarPedidos({ silent: true });
    }, POLLING_MS);
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

    if (action === 'confirmar-cola') {
      await confirmarCola(id);
      return;
    }

    if (action === 'cancelar-solicitud') {
      await cancelarSolicitud(id);
      return;
    }

    if (action === 'detalle') {
      await verDetalle(id);
    }
  });

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) return;
    if (document.querySelector('.ev-mpc-page')) {
      cargarPedidos({ silent: true });
    }
  });

  document.addEventListener('ev:content-loaded', () => {
    if (document.querySelector('.ev-mpc-page')) {
      initMisPedidosComprador();
    } else {
      vistaActiva = false;
      detenerPolling();
    }
  });

  function initMisPedidosComprador() {
    const refs = getRefs();
    if (!refs.root) {
      vistaActiva = false;
      detenerPolling();
      return;
    }

    vistaActiva = true;
    bindTabs();
    bindRefresh();
    showTab(refs, tabActiva || 'pendientes');
    cargarPedidos({ silent: false });
    iniciarPolling();
  }

  window.EVMisPedidosComprador = {
    init: initMisPedidosComprador,
    refresh: () => cargarPedidos({ silent: true })
  };

  if (document.querySelector('.ev-mpc-page')) {
    initMisPedidosComprador();
  }
})();