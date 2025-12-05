// views/js/recibirPedidos.js

document.addEventListener('DOMContentLoaded', () => {
  const BASE_URL = window.BASE_URL || '/';

  const toggle = document.getElementById('toggleRecibirPedidos');
  const sliderLabel = document.getElementById('evSliderLabel');
  const estadoBadge = document.getElementById('estadoBadge');
  const estadoBadgeText = document.getElementById('estadoBadgeText');
  const estadoDot = document.getElementById('estadoDot');
  const estadoTextoSecundario = document.getElementById('estadoTextoSecundario');

  if (!toggle) {
    console.warn('[RecibirPedidos] No se encontró el checkbox toggleRecibirPedidos.');
    return;
  }

  /**
   * Aplica el estado visual en toda la vista
   * @param {boolean} estaConectado
   */
  function aplicarEstadoUI(estaConectado) {
    toggle.checked = estaConectado;

    if (estaConectado) {
      // Slider
      if (sliderLabel) {
        sliderLabel.textContent = 'Estás conectado';
      }

      // Badge
      if (estadoBadge) {
        estadoBadge.classList.remove('ev-status-off');
        estadoBadge.classList.add('ev-status-on');
      }
      if (estadoDot) {
        estadoDot.classList.remove('ev-status-dot-off');
        estadoDot.classList.add('ev-status-dot-on');
      }
      if (estadoBadgeText) {
        estadoBadgeText.textContent = 'Conectado';
      }

      // Texto secundario
      if (estadoTextoSecundario) {
        estadoTextoSecundario.innerHTML = 'Actualmente: <strong>Conectado</strong>';
      }

    } else {
      // Slider
      if (sliderLabel) {
        sliderLabel.textContent = 'Desliza para conectarte';
      }

      // Badge
      if (estadoBadge) {
        estadoBadge.classList.remove('ev-status-on');
        estadoBadge.classList.add('ev-status-off');
      }
      if (estadoDot) {
        estadoDot.classList.remove('ev-status-dot-on');
        estadoDot.classList.add('ev-status-dot-off');
      }
      if (estadoBadgeText) {
        estadoBadgeText.textContent = 'Desconectado';
      }

      // Texto secundario
      if (estadoTextoSecundario) {
        estadoTextoSecundario.innerHTML = 'Actualmente: <strong>Desconectado</strong>';
      }
    }
  }

  /**
   * Carga estado inicial desde API
   * Ajusta la URL a tu endpoint real si es necesario.
   */
  function cargarEstadoInicial() {
    fetch(`${BASE_URL}/api/recibir-pedidos/estado`, {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Accept': 'application/json'
      }
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Error al obtener estado inicial');
        }
        return response.json();
      })
      .then(data => {
        // Asumo que el backend responde algo como { ok: true, activo: 1 }
        const activo = data && (data.activo === 1 || data.activo === true || data.activo === '1');
        aplicarEstadoUI(!!activo);
      })
      .catch(err => {
        console.error('[RecibirPedidos] No se pudo cargar el estado inicial:', err);
        // Por defecto, desconectado
        aplicarEstadoUI(false);
      });
  }

  /**
   * Envía el nuevo estado al backend
   * Ajusta la URL/estructura del body a tu API real.
   */
  function actualizarEstadoBackend(nuevoEstado) {
    fetch(`${BASE_URL}/api/recibir-pedidos/estado`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        activo: nuevoEstado ? 1 : 0
      })
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Error HTTP ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        if (data && data.ok === false) {
          throw new Error(data.error || 'Error en la respuesta de la API');
        }
        // Si todo OK, no hacemos nada más (la UI ya se actualizó).
      })
      .catch(err => {
        console.error('[RecibirPedidos] Error al actualizar estado:', err);

        // Si falla el backend, revertimos visualmente el estado
        aplicarEstadoUI(!nuevoEstado);

        // Si usas SweetAlert2 global, puedes descomentar:
        /*
        if (window.Swal) {
          Swal.fire({
            icon: 'error',
            title: 'No se pudo actualizar tu estado',
            text: 'Inténtalo nuevamente en unos segundos.',
            confirmButtonText: 'Entendido'
          });
        }
        */
      });
  }

  // Evento de cambio del toggle
  toggle.addEventListener('change', () => {
    const nuevoEstado = toggle.checked;
    aplicarEstadoUI(nuevoEstado);
    actualizarEstadoBackend(nuevoEstado);
  });

  // Inicializar
  cargarEstadoInicial();
});
