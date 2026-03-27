// views/js/recibirPedidos.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE_URL = (window.BASE_URL || '/').replace(/\/+$/, '');

  const toggle = document.getElementById('toggleRecibirPedidos');
  const sliderLabel = document.getElementById('evSliderLabel');
  const estadoBadge = document.getElementById('estadoBadge');
  const estadoBadgeText = document.getElementById('estadoBadgeText');
  const estadoDot = document.getElementById('estadoDot');
  const estadoTextoSecundario = document.getElementById('estadoTextoSecundario');

  const pedidosCounter = document.getElementById('evPedidosCounter');
  const pedidosDesconectado = document.getElementById('evPedidosDesconectado');
  const pedidosError = document.getElementById('evPedidosError');
  const pedidosBloque = document.getElementById('evPedidosBloque');

  const pendientesLista = document.getElementById('evPendientesLista');
  const procesoLista = document.getElementById('evProcesoLista');
  const finalizadosLista = document.getElementById('evFinalizadosLista');

  const pendientesCounter = document.getElementById('evPendientesCounter');
  const procesoCounter = document.getElementById('evProcesoCounter');
  const finalizadosCounter = document.getElementById('evFinalizadosCounter');

  const pendientesEmpty = document.getElementById('evPendientesEmpty');
  const procesoEmpty = document.getElementById('evProcesoEmpty');
  const finalizadosEmpty = document.getElementById('evFinalizadosEmpty');

  const btnRefrescarPedidos = document.getElementById('btnRefrescarPedidos');

  if (!toggle) {
    console.warn('[RecibirPedidos] No se encontró toggleRecibirPedidos.');
    return;
  }

  let disponibilidadActual = 0;
  let pollingId = null;

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
    if (Number.isNaN(fecha.getTime())) return valor;

    return fecha.toLocaleString('es-PE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function badgeEstado(estado) {
    const mapa = {
      pendiente_vendedor: { texto: 'Pendiente', clase: 'ev-status-off' },
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

  function aplicarEstadoUI(estaConectado) {
    disponibilidadActual = estaConectado ? 1 : 0;
    toggle.checked = !!estaConectado;

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
      const response = await fetch(`${BASE_URL}/api/usuario/disponibilidad-pedidos`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      const disponibilidad = Number(data?.data?.disponibilidad ?? 0);
      aplicarEstadoUI(disponibilidad === 1);

      if (disponibilidad === 1) {
        await cargarMisPedidos();
      } else {
        limpiarListas();
      }
    } catch (err) {
      console.error('[RecibirPedidos] No se pudo cargar disponibilidad:', err);
      aplicarEstadoUI(false);
      limpiarListas();
    }
  }

  async function actualizarEstadoBackend(nuevoEstado) {
    try {
      const response = await fetch(`${BASE_URL}/api/usuario/disponibilidad-pedidos`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          disponibilidad: nuevoEstado ? 1 : 0
        })
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok || data?.ok === false) {
        throw new Error(data?.mensaje || `HTTP ${response.status}`);
      }

      if (window.Swal) {
        Swal.fire({
          icon: 'success',
          title: nuevoEstado ? 'Ahora estás disponible' : 'Te has desconectado',
          text: data?.mensaje || (nuevoEstado
            ? 'Ahora puedes recibir solicitudes.'
            : 'Ya no recibirás nuevas solicitudes.'),
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#EA7C12'
        });
      }

      if (nuevoEstado) {
        await cargarMisPedidos();
      } else {
        limpiarListas();
      }

    } catch (err) {
      console.error('[RecibirPedidos] Error al actualizar disponibilidad:', err);
      aplicarEstadoUI(!nuevoEstado);

      if (window.Swal) {
        Swal.fire({
          icon: 'error',
          title: 'No se pudo actualizar tu estado',
          text: err.message || 'Inténtalo nuevamente en unos segundos.',
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#EA7C12'
        });
      }
    }
  }

  function limpiarContenedor(el) {
    if (el) el.innerHTML = '';
  }

  function limpiarListas() {
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

  function construirBloqueDetalle(item) {
    const programado = item.tipo_entrega_raw === 'programada' && item.fecha_hora_programada
      ? `<div class="ev-pedido-detalle-line"><strong>Entrega programada:</strong> ${escapeHtml(formatearFechaProgramada(item.fecha_hora_programada))}</div>`
      : '';

    const mensaje = item.mensaje_comprador
      ? `<div class="ev-pedido-comentario"><strong>Mensaje:</strong> ${escapeHtml(item.mensaje_comprador)}</div>`
      : '';

    const motivo = item.motivo_estado
      ? `<div class="ev-pedido-comentario"><strong>Estado:</strong> ${escapeHtml(item.motivo_estado)}</div>`
      : '';

    return `
      <div class="ev-pedido-detalle-line"><strong>Vecino:</strong> ${escapeHtml(item.nombre_vecino || 'Vecino')}</div>
      <div class="ev-pedido-detalle-line"><strong>Cantidad:</strong> ${escapeHtml(item.cantidad)}</div>
      <div class="ev-pedido-detalle-line"><strong>Precio unitario:</strong> S/ ${escapeHtml(formatoMoneda(item.precio_unitario))}</div>
      <div class="ev-pedido-detalle-line"><strong>Total:</strong> S/ ${escapeHtml(formatoMoneda(item.monto_total))}</div>
      <div class="ev-pedido-detalle-line"><strong>Entrega:</strong> ${escapeHtml(item.tipo_entrega || 'Inmediato')}</div>
      ${programado}
      <div class="ev-pedido-detalle-line"><strong>Dirección:</strong> ${escapeHtml(item.direccion_entrega || '-')}</div>
      ${mensaje}
      ${motivo}
    `;
  }

  function construirAcciones(item) {
    const estado = item.estado_actual;
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
    }

    if (estado === 'en_punto_entrega') {
      botones.push(`
        <button type="button" class="btn ev-btn-aceptar" data-action="estado" data-id="${codigoPedido}" data-estado="entregado_vendedor">
          <i class="bi bi-check2-square me-1"></i>Entregado
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
                <div class="ev-pedido-producto">${escapeHtml(item.titulo_publicacion || 'Publicación')}</div>
                <div class="ev-pedido-vecino">${escapeHtml(item.nombre_vecino || 'Vecino')}</div>
              </div>
              <div class="text-end">
                <div class="ev-pedido-precio">S/ ${escapeHtml(formatoMoneda(item.monto_total))}</div>
                ${tiempoHtml}
              </div>
            </div>

            <div class="mt-2">
              <span class="ev-status-pill ${escapeHtml(estado.clase)}">${escapeHtml(estado.texto)}</span>
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

  async function cargarMisPedidos() {
    if (pedidosError) pedidosError.classList.add('d-none');

    try {
      const response = await fetch(`${BASE_URL}/api/pedidos/mis`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Accept': 'application/json'
        }
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok || data?.ok === false) {
        throw new Error(data?.mensaje || `HTTP ${response.status}`);
      }

      const payload = data?.data || {};
      const pendientes = Array.isArray(payload.pendientes) ? payload.pendientes : [];
      const enProceso = Array.isArray(payload.en_proceso) ? payload.en_proceso : [];
      const finalizados = Array.isArray(payload.finalizados) ? payload.finalizados : [];

      pintarGrupo(pendientesLista, pendientesEmpty, pendientesCounter, pendientes);
      pintarGrupo(procesoLista, procesoEmpty, procesoCounter, enProceso);
      pintarGrupo(finalizadosLista, finalizadosEmpty, finalizadosCounter, finalizados);

      if (pedidosCounter) {
        const total = totalPedidos(payload);
        pedidosCounter.textContent = `${total} pedido${total === 1 ? '' : 's'}`;
      }

    } catch (err) {
      console.error('[RecibirPedidos] Error al cargar pedidos:', err);
      limpiarListas();
      if (pedidosError) pedidosError.classList.remove('d-none');
    }
  }

  async function aceptarSolicitud(codigoPedido) {
    const ok = await confirmarAccion({
      title: 'Aceptar solicitud',
      text: '¿Deseas aceptar esta solicitud?',
      confirmButtonText: 'Sí, aceptar'
    });
    if (!ok) return;

    await ejecutarAccion(`${BASE_URL}/api/pedidos/${codigoPedido}/aceptar`, {}, 'Solicitud aceptada correctamente.');
  }

  async function rechazarSolicitud(codigoPedido) {
    if (!window.Swal) return;

    const { value: motivo } = await Swal.fire({
      title: 'Rechazar solicitud',
      input: 'textarea',
      inputLabel: 'Motivo de rechazo',
      inputPlaceholder: 'Escribe el motivo del rechazo...',
      inputAttributes: { 'aria-label': 'Motivo de rechazo' },
      showCancelButton: true,
      confirmButtonText: 'Rechazar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12',
      preConfirm: (value) => {
        const txt = String(value || '').trim();
        if (!txt) {
          Swal.showValidationMessage('Debes indicar el motivo de rechazo.');
          return false;
        }
        return txt;
      }
    });

    if (!motivo) return;

    await ejecutarAccion(
      `${BASE_URL}/api/pedidos/${codigoPedido}/rechazar`,
      { motivo_rechazo: motivo },
      'Solicitud rechazada correctamente.'
    );
  }

  async function actualizarEstadoPedido(codigoPedido, nuevoEstado) {
    const etiquetas = {
      listo_para_entrega: 'marcar como listo para entrega',
      en_camino: 'marcar como en camino',
      en_punto_entrega: 'marcar como en punto de entrega',
      entregado_vendedor: 'marcar como entregado',
      cancelado_vendedor: 'cancelar este pedido'
    };

    const ok = await confirmarAccion({
      title: 'Actualizar estado',
      text: `¿Deseas ${etiquetas[nuevoEstado] || 'actualizar este pedido'}?`,
      confirmButtonText: 'Sí, continuar'
    });
    if (!ok) return;

    await ejecutarAccion(
      `${BASE_URL}/api/pedidos/${codigoPedido}/estado`,
      { nuevo_estado: nuevoEstado },
      'Estado actualizado correctamente.'
    );
  }

  async function verDetalle(codigoPedido) {
    const bloques = [
      pendientesLista,
      procesoLista,
      finalizadosLista
    ];

    let card = null;
    for (const bloque of bloques) {
      if (!bloque) continue;
      card = bloque.querySelector(`[data-pedido-id="${codigoPedido}"]`);
      if (card) break;
    }

    if (!card || !window.Swal) return;

    await Swal.fire({
      title: 'Detalle del pedido',
      html: card.innerHTML,
      width: 760,
      showConfirmButton: true,
      confirmButtonText: 'Cerrar',
      confirmButtonColor: '#EA7C12'
    });
  }

  async function confirmarAccion({ title, text, confirmButtonText }) {
    if (!window.Swal) return window.confirm(text);

    const result = await Swal.fire({
      icon: 'question',
      title,
      text,
      showCancelButton: true,
      confirmButtonText,
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12'
    });

    return !!result.isConfirmed;
  }

  async function ejecutarAccion(url, body, successText) {
    try {
      const response = await fetch(url, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(body || {})
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok || data?.ok === false) {
        throw new Error(data?.mensaje || `HTTP ${response.status}`);
      }

      if (window.Swal) {
        await Swal.fire({
          icon: 'success',
          title: 'Listo',
          text: data?.mensaje || successText,
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#EA7C12'
        });
      }

      await cargarMisPedidos();
    } catch (err) {
      console.error('[RecibirPedidos] Error en acción:', err);

      if (window.Swal) {
        Swal.fire({
          icon: 'error',
          title: 'No se pudo completar la acción',
          text: err.message || 'Inténtalo nuevamente.',
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#EA7C12'
        });
      }
    }
  }

  function iniciarPolling() {
    detenerPolling();
    pollingId = window.setInterval(() => {
      if (disponibilidadActual === 1) {
        cargarMisPedidos();
      }
    }, 15000);
  }

  function detenerPolling() {
    if (pollingId) {
      window.clearInterval(pollingId);
      pollingId = null;
    }
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;

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

  toggle.addEventListener('change', async () => {
    const nuevoEstado = toggle.checked;
    aplicarEstadoUI(nuevoEstado);
    await actualizarEstadoBackend(nuevoEstado);
  });

  if (btnRefrescarPedidos) {
    btnRefrescarPedidos.addEventListener('click', async () => {
      await cargarMisPedidos();
    });
  }

  cargarEstadoInicial();
});