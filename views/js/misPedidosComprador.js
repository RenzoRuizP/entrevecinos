/* views/js/misPedidosComprador.js */
(function () {
  'use strict';

  if (window.__EV_MIS_PEDIDOS_COMPRADOR_V2__ === true) {
    if (window.EVMisPedidosComprador && typeof window.EVMisPedidosComprador.init === 'function') {
      window.EVMisPedidosComprador.init();
    }
    return;
  }
  window.__EV_MIS_PEDIDOS_COMPRADOR_V2__ = true;

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
  const PLACEHOLDER = `${BASE}/public/img/placeholder-ev.png`;

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
      return { texto: 'Pendiente', clase: 'ev-mpc-badge ev-mpc-badge-pendiente' };
    }

    if (
      e === 'en_preparacion' ||
      e === 'despachando' ||
      e === 'listo_para_entrega' ||
      e === 'en_camino' ||
      e === 'en_punto_entrega' ||
      e === 'entregado_vendedor'
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

    const flujo = requierePreparacion
      ? ['pendiente_vendedor', 'en_preparacion', 'listo_para_entrega', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador']
      : ['pendiente_vendedor', 'despachando', 'en_camino', 'en_punto_entrega', 'entregado_vendedor', 'entrega_confirmada_comprador'];

    const finales = ['rechazado_vendedor', 'cancelado_vendedor', 'cancelado_comprador', 'sin_respuesta_vendedor'];

    if (finales.includes(estado)) {
      return `
        <div class="ev-mpc-stepper ev-mpc-stepper-final">
          <div class="ev-mpc-step is-final">${escapeHtml(estadoLegible(estado))}</div>
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

    if (estado === 'entregado_vendedor') {
      return `
        <div class="ev-mpc-state-box ev-mpc-state-box-process">
          <div class="ev-mpc-state-title">Pedido marcado como entregado</div>
          <div class="ev-mpc-state-text">
            Verifica que recibiste correctamente tu pedido antes de confirmarlo.
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
        <div class="ev-mpc-state-box ev-mpc-state-box-final">
          <div class="ev-mpc-state-title">${escapeHtml(estadoLegible(estado))}</div>
          <div class="ev-mpc-state-text">
            ${escapeHtml(item.motivo_estado || item.mensaje_estado || 'Este pedido ya se encuentra cerrado.')}
          </div>
        </div>
      `;
    }

    return `
      <div class="ev-mpc-state-box ev-mpc-state-box-info">
        <div class="ev-mpc-state-title">Pedido en avance</div>
        <div class="ev-mpc-state-text">
          ${escapeHtml(item.motivo_estado || item.mensaje_estado || 'Tu pedido continúa avanzando.')}
        </div>
      </div>
    `;
  }

  function renderCard(item) {
    const badge = badgeEstado(item.estado_actual);
    const imagen = normalizarUrlImagen(item.imagen_portada_url || item.imagen_portada);
    const acciones = [];
    const tieneProgramacion = String(item.tipo_entrega_raw || '').trim() === 'programada' && !!item.fecha_hora_programada;

    if (String(item.estado_actual || '').trim() === 'entregado_vendedor') {
      acciones.push(`
        <button type="button" class="btn ev-mpc-btn-primary" data-action="confirmar-entrega" data-id="${Number(item.codigo_pedido || 0)}">
          <i class="bi bi-check2-circle me-1"></i>Confirmar entrega
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

          <div class="ev-mpc-order-head-main">
            <div class="ev-mpc-order-head-row">
              <div>
                <div class="ev-mpc-order-title">${escapeHtml(item.titulo_publicacion || 'Pedido')}</div>
                <div class="ev-mpc-order-meta">
                  Pedido #${Number(item.codigo_pedido || 0)} · ${escapeHtml(formatFecha(item.fecha_hora || item.created_at || null))}
                </div>
              </div>
              <span class="${badge.clase}">${escapeHtml(badge.texto)}</span>
            </div>

            <div class="ev-mpc-order-mini-grid">
              <div class="ev-mpc-mini-item">
                <span>Vendedor</span>
                <strong>${escapeHtml(item.nombre_vecino || item.nombre_vendedor || 'Vecino')}</strong>
              </div>
              <div class="ev-mpc-mini-item">
                <span>Total</span>
                <strong>S/ ${escapeHtml(formatMoney(item.monto_total || item.total || 0))}</strong>
              </div>
              <div class="ev-mpc-mini-item">
                <span>Cantidad</span>
                <strong>${escapeHtml(item.cantidad || 0)}</strong>
              </div>
              <div class="ev-mpc-mini-item">
                <span>Entrega</span>
                <strong>${escapeHtml(item.tipo_entrega || 'Inmediata')}</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="ev-mpc-order-body">
          ${getLineaEstado(item)}

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
    if (!refs.root) return;

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
                <span>Vendedor</span>
                <strong>${escapeHtml(item.nombre_vecino || item.nombre_vendedor || 'Vecino')}</strong>
              </div>
              <div class="ev-mpc-modal-item">
                <span>Total</span>
                <strong>S/ ${escapeHtml(formatMoney(item.monto_total || item.total || 0))}</strong>
              </div>
              <div class="ev-mpc-modal-item">
                <span>Cantidad</span>
                <strong>${escapeHtml(item.cantidad || 0)}</strong>
              </div>
              <div class="ev-mpc-modal-item">
                <span>Entrega</span>
                <strong>${escapeHtml(item.tipo_entrega || 'Inmediata')}</strong>
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
      width: 860,
      confirmButtonText: 'Cerrar',
      confirmButtonColor: '#EA7C12',
      customClass: {
        popup: 'ev-mpc-swal-popup'
      }
    });
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

    if (action === 'detalle') {
      await verDetalle(id);
    }
  });

  document.addEventListener('ev:content-loaded', () => {
    if (document.querySelector('.ev-mpc-page')) {
      initMisPedidosComprador();
    }
  });

  function initMisPedidosComprador() {
    const refs = getRefs();
    if (!refs.root) return;

    bindTabs();
    bindRefresh();
    showTab(refs, tabActiva || 'pendientes');
    cargarPedidos({ silent: false });
  }

  window.EVMisPedidosComprador = {
    init: initMisPedidosComprador,
    refresh: () => cargarPedidos({ silent: true })
  };

  if (document.querySelector('.ev-mpc-page')) {
    initMisPedidosComprador();
  }
})();