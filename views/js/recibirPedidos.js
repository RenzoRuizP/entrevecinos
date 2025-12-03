/* recibirPedidos.js
   UX para módulo "Recibir pedidos" en Entre Vecinos
   - Maneja estados Conectado / Desconectado
   - Actualiza la UI (slider + badge + panel)
   - Cuando está conectado, consulta periódicamente los pedidos
*/

(function () {
  'use strict';

  console.log('[RECIBIR PEDIDOS] JS cargado');

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  const EV_API_PEDIDOS = BASE + '/api/pedidos/recibir'; // Ajusta esta ruta a tu backend real

  // Helper de alertas
  function evNotify(icon, title, text) {
    if (window.Swal?.fire) {
      Swal.fire({
        icon,
        title,
        text,
        confirmButtonText: 'Aceptar',
        customClass: {
          confirmButton: 'btn btn-outline-success'
        },
        buttonsStyling: false
      });
    } else {
      alert(title ? (title + '\n\n' + text) : text);
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('rpToggleEstado');
    const textoSlider = document.getElementById('rpTextoSlider');
    const badgeEstado = document.getElementById('rpBadgeEstadoTexto');
    const panelEstado = document.getElementById('rpPanelEstado');
    const listaWrapper = document.getElementById('rpListaPedidosWrapper');
    const listaPedidos = document.getElementById('rpPedidosList');
    const emptyState = document.getElementById('rpEmptyState');
    const lastUpdate = document.getElementById('rpLastUpdate');

    if (!toggle || !panelEstado) {
      console.warn('[RECIBIR PEDIDOS] Elementos base no encontrados. No se inicializa.');
      return;
    }

    let estaConectado = false;
    let pollingTimer = null;

    function setEstadoUI() {
      if (!estaConectado) {
        // DESCONECTADO
        toggle.classList.remove('rp-on');
        toggle.classList.add('rp-off');
        toggle.setAttribute('aria-pressed', 'false');
        textoSlider.textContent = 'Desliza para conectarte';
        badgeEstado.innerHTML = '<i class="bi bi-toggle-off"></i> Desconectado';

        listaWrapper.classList.add('d-none');
        panelEstado.classList.remove('d-none');

        if (pollingTimer) {
          clearInterval(pollingTimer);
          pollingTimer = null;
        }

        if (lastUpdate) {
          lastUpdate.textContent = 'Actualizado: —';
        }
      } else {
        // CONECTADO
        toggle.classList.remove('rp-off');
        toggle.classList.add('rp-on');
        toggle.setAttribute('aria-pressed', 'true');
        textoSlider.textContent = 'Estás en línea y recibiendo pedidos';
        badgeEstado.innerHTML = '<i class="bi bi-toggle-on"></i> Conectado';

        panelEstado.classList.add('d-none');
        listaWrapper.classList.remove('d-none');

        if (!pollingTimer) {
          cargarPedidos(); // primera carga
          pollingTimer = setInterval(cargarPedidos, 7000); // cada 7 segundos
        }
      }
    }

    async function cargarPedidos() {
      console.log('[RECIBIR PEDIDOS] Consultando pedidos en', EV_API_PEDIDOS);

      try {
        const resp = await fetch(EV_API_PEDIDOS, {
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          },
          credentials: 'include' // si usas cookies/JWT en cookie
        });

        if (!resp.ok) {
          console.error('[RECIBIR PEDIDOS] Error HTTP', resp.status);
          return;
        }

        const data = await resp.json();
        console.log('[RECIBIR PEDIDOS] Respuesta API:', data);

        if (!data.ok) {
          console.warn('[RECIBIR PEDIDOS] API respondió ok = false:', data.error || '');
          return;
        }

        const pedidos = Array.isArray(data.pedidos) ? data.pedidos : [];

        pintarPedidos(pedidos);

        if (lastUpdate) {
          const now = new Date();
          const hh = String(now.getHours()).padStart(2, '0');
          const mm = String(now.getMinutes()).padStart(2, '0');
          lastUpdate.textContent = 'Actualizado: ' + hh + ':' + mm;
        }
      } catch (err) {
        console.error('[RECIBIR PEDIDOS] Error al consultar pedidos:', err);
      }
    }

    function pintarPedidos(pedidos) {
      if (!listaPedidos) return;

      listaPedidos.innerHTML = '';

      if (!pedidos.length) {
        if (emptyState) emptyState.classList.remove('d-none');
        return;
      }

      if (emptyState) emptyState.classList.add('d-none');

      pedidos.forEach(function (p) {
        /*
          Estructura esperada:
          {
            id_pedido,
            titulo_publicacion,
            nombre_vecino,
            torre,
            departamento,
            fecha_hora,
            monto_total
          }
          Ajusta los nombres de campos a tu API real.
        */

        const item = document.createElement('div');
        item.className = 'rp-pedido-item';

        const left = document.createElement('div');
        const right = document.createElement('div');
        right.className = 'rp-pedido-actions';

        left.innerHTML = `
          <div class="rp-pedido-header">
            <span class="rp-pedido-title">${p.titulo_publicacion || 'Pedido de tu vecino'}</span>
            <span class="badge bg-success-subtle text-success-emphasis">
              ${p.torre || ''} ${p.departamento || ''}
            </span>
          </div>
          <div class="rp-pedido-meta">
            <span><i class="bi bi-person"></i> ${p.nombre_vecino || 'Vecino'}</span>
            &nbsp;·&nbsp;
            <span><i class="bi bi-clock"></i> ${p.fecha_hora || ''}</span>
            ${p.monto_total ? `&nbsp;·&nbsp;<span><i class="bi bi-cash-coin"></i> S/ ${p.monto_total}</span>` : ''}
          </div>
        `;

        const btnAceptar = document.createElement('button');
        btnAceptar.type = 'button';
        btnAceptar.className = 'btn btn-sm rp-btn-accept';
        btnAceptar.innerHTML = '<i class="bi bi-check-lg me-1"></i>Aceptar';

        const btnRechazar = document.createElement('button');
        btnRechazar.type = 'button';
        btnRechazar.className = 'btn btn-sm rp-btn-reject';
        btnRechazar.innerHTML = '<i class="bi bi-x-lg me-1"></i>Rechazar';

        // Hooks para luego conectar con tu API real
        btnAceptar.addEventListener('click', function () {
          evNotify('success', 'Pedido aceptado', 'Conecta este botón con tu endpoint para aceptar pedidos.');
        });

        btnRechazar.addEventListener('click', function () {
          evNotify('warning', 'Pedido rechazado', 'Conecta este botón con tu endpoint para rechazar pedidos.');
        });

        right.appendChild(btnAceptar);
        right.appendChild(btnRechazar);

        item.appendChild(left);
        item.appendChild(right);

        listaPedidos.appendChild(item);
      });
    }

    // Toggle conectar / desconectar
    toggle.addEventListener('click', function () {
      estaConectado = !estaConectado;
      setEstadoUI();
    });

    // Estado inicial
    setEstadoUI();
  });
})();
