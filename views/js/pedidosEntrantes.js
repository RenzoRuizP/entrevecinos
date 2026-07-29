// views/js/pedidosEntrantes.js

document.addEventListener('DOMContentLoaded', () => {
  const BASE_URL = window.EV?.baseUrl ?? window.BASE_URL ?? '';

  const toggle = document.getElementById('toggleRecibirPedidos');
  const listaPedidos = document.getElementById('evPedidosLista');
  const lblCounter = document.getElementById('evPedidosCounter');
  const msgDesconectado = document.getElementById('evPedidosDesconectado');
  const msgEmpty = document.getElementById('evPedidosEmpty');

  if (!listaPedidos || !toggle) {
    console.warn('[PedidosEntrantes] Falta contenedor o toggle; no se inicializa.');
    return;
  }

  let eventSource = null;
  let countdownInterval = null;

  /**
   * Actualiza el contador de pedidos mostrados
   */
  function actualizarContador() {
    const count = listaPedidos.querySelectorAll('.ev-pedido-card').length;
    if (lblCounter) {
      lblCounter.textContent = `${count} pedido${count !== 1 ? 's' : ''}`;
    }
    if (count === 0 && toggle.checked) {
      msgEmpty && msgEmpty.classList.remove('d-none');
    } else {
      msgEmpty && msgEmpty.classList.add('d-none');
    }
  }

  /**
   * Inicializa el intervalo que actualiza todos los temporizadores
   */
  function iniciarCountdownGlobal() {
    if (countdownInterval) return;

    countdownInterval = setInterval(() => {
      const ahora = Date.now();
      const cards = listaPedidos.querySelectorAll('.ev-pedido-card');

      cards.forEach(card => {
        const expiraEn = parseInt(card.dataset.expiraEn, 10);
        const pedidoId = card.dataset.pedidoId;

        if (!expiraEn || !pedidoId) return;

        const diffMs = expiraEn - ahora;
        const tiempoSpan = card.querySelector('.ev-pedido-tiempo-text');
        const iconoReloj = card.querySelector('.ev-pedido-tiempo i');

        if (diffMs <= 0) {
          // Marcar como expirado solo una vez
          if (card.dataset.expirado === '1') return;
          card.dataset.expirado = '1';

          // Llamar al backend para marcar expiración
          expirarPedidoServidor(pedidoId);

          // Mostrar feedback ligero al vendedor
          if (window.Swal) {
            Swal.fire({
              icon: 'info',
              title: 'Pedido expirado',
              text: 'Este pedido estuvo disponible 2 minutos y no fue tomado. Se retiró de la lista.',
              timer: 3000,
              showConfirmButton: false
            });
          }

          // Remover del DOM
          card.remove();
          actualizarContador();
          return;
        }

        const segundosRestantes = Math.floor(diffMs / 1000);
        const minutos = Math.floor(segundosRestantes / 60);
        const segundos = segundosRestantes % 60;

        const texto = `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;

        if (tiempoSpan) {
          tiempoSpan.textContent = `Tiempo restante: ${texto}`;
        }

        if (iconoReloj) {
          // Podrías cambiar color si queda menos de 30s, etc.
        }
      });

      // Si ya no hay cards, puedes opcionalmente limpiar el intervalo:
      // if (!listaPedidos.querySelector('.ev-pedido-card')) { detenerCountdownGlobal(); }
    }, 1000);
  }

  function detenerCountdownGlobal() {
    if (countdownInterval) {
      clearInterval(countdownInterval);
      countdownInterval = null;
    }
  }

  /**
   * Inicializa el EventSource (SSE) para escuchar pedidos nuevos
   */
  function iniciarSSE() {
    if (eventSource) return;

    try {
      eventSource = new EventSource(`${BASE_URL}/sse/pedidos-entrantes`);

      eventSource.onopen = () => {
        console.info('[PedidosEntrantes] Conectado al SSE de pedidos.');
      };

      eventSource.onerror = (err) => {
        console.error('[PedidosEntrantes] Error en SSE:', err);
        // Podrías reconectar más adelante si lo deseas
      };

      eventSource.onmessage = (event) => {
        // Asumimos que el servidor envía data JSON con un pedido
        try {
          const data = JSON.parse(event.data);
          if (!data || !data.id) {
            console.warn('[PedidosEntrantes] Evento sin id válido:', data);
            return;
          }
          agregarOActualizarPedido(data);
        } catch (e) {
          console.error('[PedidosEntrantes] Error parseando data SSE:', e);
        }
      };
    } catch (e) {
      console.error('[PedidosEntrantes] Este navegador no soporta EventSource o falló la creación:', e);
    }
  }

  function cerrarSSE() {
    if (eventSource) {
      eventSource.close();
      eventSource = null;
      console.info('[PedidosEntrantes] SSE cerrado.');
    }
  }

  /**
   * Renderiza o actualiza una card de pedido
   * Estructura esperada de data (ajusta a tu backend):
   * {
   *   id: 123,
   *   producto: "Pizza familiar",
   *   imagen_url: "...",
   *   vecino_nombre: "Juan Pérez",
   *   torre: "3",
   *   departamento: "402",
   *   cantidad: 1,
   *   precio: 25.00,
   *   creado_en: "2025-12-03T18:23:00Z",
   *   comentario_comprador: "Por favor sin cebolla"
   * }
   */
  function agregarOActualizarPedido(data) {
    let card = listaPedidos.querySelector(`.ev-pedido-card[data-pedido-id="${data.id}"]`);
    const ahora = Date.now();
    const expiraEn = ahora + (2 * 60 * 1000); // 2 minutos

    const imagenUrl = data.imagen_url || `${BASE_URL}/assets/img/producto-placeholder.png`;
    const producto = data.producto || 'Pedido sin nombre';
    const vecinoNombre = data.vecino_nombre || 'Vecino';
    const torre = data.torre ? `Torre ${data.torre}` : '';
    const depto = data.departamento ? `Dpto ${data.departamento}` : '';
    const vecindad = (torre || depto) ? ` · ${[torre, depto].filter(Boolean).join(' - ')}` : '';
    const cantidad = data.cantidad != null ? data.cantidad : '-';
    const precio = data.precio != null ? `S/ ${Number(data.precio).toFixed(2)}` : '--';
    const comentario = data.comentario_comprador || 'Sin comentarios adicionales.';

    const tiempoRestanteInicial = '02:00'; // siempre parte en 2 min al llegar

    if (!card) {
      card = document.createElement('div');
      card.className = 'ev-pedido-card';
      card.dataset.pedidoId = data.id;
      card.dataset.expiraEn = String(expiraEn);

      card.innerHTML = `
        <div class="ev-pedido-main">
          <div class="ev-pedido-img-wrapper">
            <img src="${imagenUrl}" alt="${producto}">
          </div>
          <div class="ev-pedido-info">
            <div class="ev-pedido-header-row">
              <div>
                <div class="ev-pedido-producto">${producto}</div>
                <div class="ev-pedido-vecino">
                  ${vecinoNombre}${vecindad}
                </div>
              </div>
              <div class="text-end">
                <div class="ev-pedido-precio">${precio}</div>
                <div class="ev-pedido-detalle-line">
                  Cantidad: ${cantidad}
                </div>
              </div>
            </div>
            <div class="mt-1">
              <span class="ev-pedido-tiempo">
                <i class="bi bi-clock-fill"></i>
                <span class="ev-pedido-tiempo-text">Tiempo restante: ${tiempoRestanteInicial}</span>
              </span>
            </div>
            <div class="ev-pedido-comentario">
              <strong>Comentario del vecino:</strong> ${comentario}
            </div>
          </div>
        </div>
        <div class="ev-pedido-actions">
          <button type="button" class="btn ev-btn-aceptar" data-accion="aceptar">
            Aceptar
          </button>
          <button type="button" class="btn ev-btn-rechazar" data-accion="rechazar">
            Rechazar
          </button>
          <button type="button" class="btn ev-btn-detalle" data-accion="detalle">
            Ver detalle
          </button>
          <button type="button" class="btn ev-btn-mensaje" data-accion="mensaje">
            Mensaje al vecino
          </button>
        </div>
      `;

      listaPedidos.appendChild(card);
      wireEventosCard(card, data.id);
    } else {
      // Si el pedido ya existía, podrías actualizar datos
      card.dataset.expiraEn = String(expiraEn);
      const comentarioSpan = card.querySelector('.ev-pedido-comentario');
      if (comentarioSpan) {
        comentarioSpan.innerHTML = `<strong>Comentario del vecino:</strong> ${comentario}`;
      }
    }

    actualizarContador();
    iniciarCountdownGlobal();
  }

  /**
   * Asociar eventos de botones a una card
   */
  function wireEventosCard(card, pedidoId) {
    card.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-accion]');
      if (!btn) return;

      const accion = btn.dataset.accion;
      if (accion === 'aceptar') {
        confirmarAccionPedido(pedidoId, true);
      } else if (accion === 'rechazar') {
        confirmarAccionPedido(pedidoId, false);
      } else if (accion === 'detalle') {
        verDetallePedido(pedidoId);
      } else if (accion === 'mensaje') {
        enviarMensajeVecino(pedidoId);
      }
    });
  }

  /**
   * Confirmar aceptar/rechazar pedido
   */
  function confirmarAccionPedido(pedidoId, esAceptar) {
    const accionTexto = esAceptar ? 'aceptar' : 'rechazar';
    const url = esAceptar 
      ? `${BASE_URL}/api/pedidos/aceptar` 
      : `${BASE_URL}/api/pedidos/rechazar`;

    const ejecutar = () => {
      fetch(url, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ id_pedido: pedidoId })
      })
      .then(r => {
        if (!r.ok) throw new Error('Error HTTP ' + r.status);
        return r.json();
      })
      .then(data => {
        if (data && data.ok === false) {
          throw new Error(data.error || 'Error desde la API');
        }
        // Remover la card al aceptar o rechazar
        const card = listaPedidos.querySelector(`.ev-pedido-card[data-pedido-id="${pedidoId}"]`);
        if (card) card.remove();
        actualizarContador();

        if (window.Swal) {
          Swal.fire({
            icon: 'success',
            title: esAceptar ? 'Pedido aceptado' : 'Pedido rechazado',
            timer: 2000,
            showConfirmButton: false
          });
        }
      })
      .catch(err => {
        console.error('[PedidosEntrantes] Error al ' + accionTexto + ' pedido:', err);
        if (window.Swal) {
          Swal.fire({
            icon: 'error',
            title: 'No se pudo ' + accionTexto + ' el pedido',
            text: 'Inténtalo nuevamente.',
            confirmButtonText: 'Entendido'
          });
        }
      });
    };

    if (window.Swal) {
      Swal.fire({
        icon: 'question',
        title: `¿Seguro que deseas ${accionTexto} este pedido?`,
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
      }).then(res => {
        if (res.isConfirmed) ejecutar();
      });
    } else {
      if (confirm(`¿Seguro que deseas ${accionTexto} este pedido?`)) {
        ejecutar();
      }
    }
  }

  /**
   * Ver detalle del pedido (puedes conectar con un modal real)
   */
  function verDetallePedido(pedidoId) {
    // Aquí puedes abrir un modal y pedir más datos al backend.
    // Endpoint sugerido: GET /api/pedidos/detalle?id=...
    if (window.Swal) {
      Swal.fire({
        icon: 'info',
        title: 'Detalle del pedido',
        text: `Aquí se mostraría un modal con el detalle completo del pedido #${pedidoId}.`,
        confirmButtonText: 'Cerrar'
      });
    } else {
      alert(`Detalle del pedido #${pedidoId} (conecta aquí tu modal real).`);
    }
  }

  /**
   * Enviar mensaje al vecino (comprador)
   * El comprador ya dejó su comentario; aquí el vendedor responde.
   */
  function enviarMensajeVecino(pedidoId) {
    const preguntarYEnviar = (mensaje) => {
      if (!mensaje || !mensaje.trim()) return;

      fetch(`${BASE_URL}/api/pedidos/mensaje`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          id_pedido: pedidoId,
          mensaje: mensaje.trim()
        })
      })
      .then(r => {
        if (!r.ok) throw new Error('Error HTTP ' + r.status);
        return r.json();
      })
      .then(data => {
        if (data && data.ok === false) {
          throw new Error(data.error || 'Error en la API de mensaje');
        }
        if (window.Swal) {
          Swal.fire({
            icon: 'success',
            title: 'Mensaje enviado',
            text: 'Tu mensaje fue enviado al vecino.',
            timer: 2000,
            showConfirmButton: false
          });
        } else {
          alert('Mensaje enviado al vecino.');
        }
      })
      .catch(err => {
        console.error('[PedidosEntrantes] Error al enviar mensaje:', err);
        if (window.Swal) {
          Swal.fire({
            icon: 'error',
            title: 'No se pudo enviar el mensaje',
            text: 'Inténtalo nuevamente.',
            confirmButtonText: 'Entendido'
          });
        } else {
          alert('No se pudo enviar el mensaje. Inténtalo nuevamente.');
        }
      });
    };

    if (window.Swal) {
      Swal.fire({
        icon: 'question',
        title: 'Mensaje al vecino',
        input: 'textarea',
        inputLabel: 'Escribe tu mensaje para el vecino:',
        inputPlaceholder: 'Ejemplo: Estoy preparando tu pedido, te aviso cuando esté listo.',
        showCancelButton: true,
        confirmButtonText: 'Enviar mensaje',
        cancelButtonText: 'Cancelar'
      }).then(res => {
        if (res.isConfirmed) {
          preguntarYEnviar(res.value);
        }
      });
    } else {
      const mensaje = prompt('Escribe tu mensaje para el vecino:');
      preguntarYEnviar(mensaje);
    }
  }

  /**
   * Notificar al backend que el pedido expiró sin ser tomado.
   * Aquí el backend debe, entre otras cosas, enviar al comprador el mensaje amigable:
   * "No encontramos un vecino disponible para atender tu pedido en este momento.
   *  Puedes volver a intentarlo en unos minutos. Gracias por confiar en Entre Vecinos."
   */
  function expirarPedidoServidor(pedidoId) {
    fetch(`${BASE_URL}/api/pedidos/expirar`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ id_pedido: pedidoId })
    })
    .then(r => {
      if (!r.ok) throw new Error('Error HTTP ' + r.status);
      return r.json();
    })
    .then(data => {
      if (data && data.ok === false) {
        throw new Error(data.error || 'Error en expiración');
      }
      console.info('[PedidosEntrantes] Pedido expirado notificado al servidor:', pedidoId);
    })
    .catch(err => {
      console.error('[PedidosEntrantes] Error al notificar expiración:', err);
    });
  }

  /**
   * Manejo del estado conectado / desconectado según el toggle
   */
  function actualizarUIConexion() {
    if (toggle.checked) {
      // Conectado
      msgDesconectado && msgDesconectado.classList.add('d-none');
      // Si no hay pedidos, mostramos mensaje vacío
      if (!listaPedidos.querySelector('.ev-pedido-card')) {
        msgEmpty && msgEmpty.classList.remove('d-none');
      }
      iniciarSSE();
      iniciarCountdownGlobal();
    } else {
      // Desconectado
      msgDesconectado && msgDesconectado.classList.remove('d-none');
      msgEmpty && msgEmpty.classList.add('d-none');

      // Limpiar pedidos en pantalla (opcional, pero recomendable)
      listaPedidos.innerHTML = '';
      actualizarContador();
      cerrarSSE();
      detenerCountdownGlobal();
    }
  }

  // Escuchar cambios del toggle
  toggle.addEventListener('change', actualizarUIConexion);

  // Inicializar según estado actual (por si el JS de recibirPedidos ya marcó el toggle)
  actualizarUIConexion();
});
