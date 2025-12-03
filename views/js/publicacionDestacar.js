// views/js/publicacionDestacar.js
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  const LOG_PREFIX = '[PUBLICACION_DESTACAR]';

  // ------------------------------------
  // Helpers de log seguros
  // ------------------------------------
  function log() {
    if (window.console && console.log) {
      console.log(LOG_PREFIX, ...arguments);
    }
  }

  function warn() {
    if (window.console && console.warn) {
      console.warn(LOG_PREFIX, ...arguments);
    }
  }

  function error() {
    if (window.console && console.error) {
      console.error(LOG_PREFIX, ...arguments);
    }
  }

  /**
   * Intenta obtener el código de publicación a partir del botón o la fila.
   */
  function obtenerCodigoPublicacionDesdeBoton(btn) {
    if (!btn) return null;

    // 1) Dataset del propio botón
    let codigo = btn.dataset.codigoPublicacion
      || btn.dataset.codigo
      || btn.dataset.id
      || btn.dataset.pubId
      || btn.dataset.pub;

    if (codigo) {
      return parseInt(codigo, 10) || null;
    }

    // 2) Dataset de la fila
    const tr = btn.closest('tr');
    if (!tr) return null;

    codigo = tr.dataset.codigoPublicacion
      || tr.dataset.codigo
      || tr.dataset.id
      || tr.dataset.pubId
      || tr.dataset.pub;

    if (!codigo) {
      // 3) Último intento: primer <td> numérico
      const firstCell = tr.querySelector('td');
      if (firstCell) {
        const txt = (firstCell.textContent || '').trim();
        const num = parseInt(txt, 10);
        if (!isNaN(num)) {
          codigo = num;
        }
      }
    }

    return codigo ? (parseInt(codigo, 10) || null) : null;
  }

  /**
   * Actualiza el botón de la fila para que quede como "Destacar".
   */
  function actualizarBotonADestacar(btn, codigoPublicacion) {
    if (!btn) return;
    // Guardamos el código explícitamente
    if (codigoPublicacion) {
      btn.dataset.codigoPublicacion = codigoPublicacion;
    }
    // Cambiamos el texto visible
    btn.textContent = 'Destacar';
  }

  /**
   * Publicar la publicación en el backend.
   * POST /api/publicacion/{codigo}/publicar
   */
  async function publicarPublicacion(codigoPublicacion) {
    const url = `${BASE}/api/publicacion/${codigoPublicacion}/publicar`;

    try {
      const resp = await fetch(url, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({})
      });

      if (resp.status === 401) {
        if (window.Swal?.fire) {
          await Swal.fire({
            icon: 'error',
            title: 'Sesión expirada',
            text: 'Tu sesión ha expirado. Vuelve a iniciar sesión.'
          });
        }
        window.location.href = `${BASE}/`;
        return { ok: false };
      }

      const json = await resp.json().catch(() => null);

      if (!resp.ok || !json || json.ok === false) {
        error('Error al publicar publicación:', resp.status, json);
        if (window.Swal?.fire) {
          await Swal.fire({
            icon: 'error',
            title: 'No se pudo publicar',
            text: (json && json.mensaje) || 'Ocurrió un problema al publicar la publicación.'
          });
        }
        return { ok: false };
      }

      log('Publicación publicada correctamente:', codigoPublicacion, json);
      return { ok: true, data: json };

    } catch (err) {
      error('Excepción al publicar publicación:', err);
      if (window.Swal?.fire) {
        await Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: 'No pudimos publicar la publicación. Revisa tu conexión e inténtalo otra vez.'
        });
      }
      return { ok: false };
    }
  }

  /**
   * Llama a la API de billetera para debitar S/ 1.00 por destacar la publicación.
   */
  async function debitarPublicacion(codigoPublicacion) {
    const url = `${BASE}/api/billetera/debitar-publicacion`;

    try {
      const resp = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({ codigo_publicacion: codigoPublicacion })
      });

      if (resp.status === 401) {
        if (window.Swal?.fire) {
          await Swal.fire({
            icon: 'error',
            title: 'Sesión expirada',
            text: 'Tu sesión ha expirado. Vuelve a iniciar sesión.'
          });
        }
        window.location.href = `${BASE}/`;
        return { ok: false };
      }

      const json = await resp.json().catch(() => null);

      if (!resp.ok || !json) {
        error('Respuesta HTTP no OK al debitar publicación:', resp.status, json);
        if (window.Swal?.fire) {
          await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo procesar el pago para destacar la publicación. Intenta nuevamente.'
          });
        }
        return { ok: false };
      }

      if (!json.ok) {
        if (json.codigo === 'SALDO_INSUFICIENTE') {
          if (window.Swal?.fire) {
            await Swal.fire({
              icon: 'warning',
              title: 'Saldo insuficiente',
              text: 'Tu billetera no tiene saldo suficiente para destacar esta publicación. La publicación ya está publicada, pero no aparecerá como destacada.'
            });
          }
          return { ok: false };
        }

        if (window.Swal?.fire) {
          await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: json.mensaje || 'Ocurrió un problema al procesar el pago.'
          });
        }
        return { ok: false };
      }

      log('Debito correcto. Saldo actual:', json.saldo_actual);
      return { ok: true, data: json };

    } catch (err) {
      error('Excepción al debitar publicación:', err);
      if (window.Swal?.fire) {
        await Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: 'No pudimos procesar el pago. La publicación ya está publicada, pero no se destacó.'
        });
      }
      return { ok: false };
    }
  }

  /**
   * Flujo cuando se hace click en "Destacar" (solo destacar, sin publicar de nuevo).
   */
  async function manejarClickDestacar(btn) {
    const codigo = obtenerCodigoPublicacionDesdeBoton(btn);
    if (!codigo) {
      warn('No se pudo determinar el código de publicación para destacar.');
      if (window.Swal?.fire) {
        await Swal.fire({
          icon: 'warning',
          title: 'No se pudo procesar',
          text: 'No pudimos identificar la publicación. Intenta recargar la página.'
        });
      }
      return;
    }

    if (!window.Swal?.fire) {
      const seguir = window.confirm(
        'Se descontará S/ 1.00 de tu billetera para destacar esta publicación. ¿Deseas continuar?'
      );
      if (!seguir) return;

      const debRes = await debitarPublicacion(codigo);
      if (!debRes.ok) return;
      window.location.reload();
      return;
    }

    const result = await Swal.fire({
      icon: 'question',
      title: 'Destacar publicación',
      text: 'Se descontará S/ 1.00 de tu billetera para destacar esta publicación en la portada de tu condominio. ¿Deseas continuar?',
      showCancelButton: true,
      confirmButtonText: 'Sí, destacar',
      cancelButtonText: 'Cancelar',
      reverseButtons: true
    });

    if (!result.isConfirmed) {
      return;
    }

    const debRes = await debitarPublicacion(codigo);
    if (debRes.ok) {
      await Swal.fire({
        icon: 'success',
        title: 'Publicación destacada',
        text: 'Se descontó S/ 1.00 de tu billetera y tu publicación ahora aparece como destacada.',
        confirmButtonText: 'Entendido'
      });
      window.location.reload();
    }
  }

  /**
   * Flujo cuando se hace click en "Publicar"/"Publicado".
   */
  async function manejarClickPublicado(btn) {
    const codigo = obtenerCodigoPublicacionDesdeBoton(btn);
    if (!codigo) {
      warn('No se pudo determinar el código de publicación para publicar/destacar.');
      if (window.Swal?.fire) {
        await Swal.fire({
          icon: 'warning',
          title: 'No se pudo procesar',
          text: 'No pudimos identificar la publicación. Intenta recargar la página.'
        });
      }
      return;
    }

    if (!window.Swal?.fire) {
      // Fallback sin SweetAlert: solo publicar sin destacar
      const seguir = window.confirm(
        '¿Deseas publicar esta publicación? (No se destacará en portada en este modo simple).'
      );
      if (!seguir) return;

      const pubRes = await publicarPublicacion(codigo);
      if (!pubRes.ok) return;

      // En modo simple, recargamos
      window.location.reload();
      return;
    }

    // SweetAlert con 3 opciones:
    const result = await Swal.fire({
      icon: 'question',
      title: 'Destacar publicación',
      text: 'Se descontará S/ 1.00 de tu billetera para destacar esta publicación en la portada de tu condominio. ¿Deseas continuar?',
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: 'Sí, destacar',
      denyButtonText: 'No, solo publicar',
      cancelButtonText: 'Cancelar',
      reverseButtons: true
    });

    if (result.isDismissed) {
      // Cancelar
      return;
    }

    // En ambos casos (destacar o no), primero PUBLICAMOS
    const pubRes = await publicarPublicacion(codigo);
    if (!pubRes.ok) return;

    if (result.isDenied) {
      // Publicar sin destacar
      await Swal.fire({
        icon: 'success',
        title: 'Publicación publicada',
        text: 'Tu publicación ya se muestra en el marketplace en la pestaña "Todos".',
        confirmButtonText: 'Ok'
      });

      // ✅ Aquí aplicamos el nuevo flujo:
      // El botón pasa a comportarse como "Destacar"
      actualizarBotonADestacar(btn, codigo);
      return;
    }

    if (result.isConfirmed) {
      // Publicar + destacar (intentar cobrar S/ 1.00)
      const debRes = await debitarPublicacion(codigo);

      if (debRes.ok) {
        await Swal.fire({
          icon: 'success',
          title: 'Publicación destacada',
          text: 'Se ha descontado S/ 1.00 de tu billetera y tu publicación será mostrada como destacada en el inicio.',
          confirmButtonText: 'Entendido'
        });
      }
      // Si el débito falla, ya se mostraron mensajes dentro de debitarPublicacion.
      window.location.reload();
    }
  }

  /**
   * Delegación global de clicks para botones "Publicar"/"Publicado"/"Destacar".
   */
  function configurarDelegacionClicks() {
    document.addEventListener('click', function (ev) {
      const target = ev.target;
      if (!target) return;

      const btn = target.closest('button');
      if (!btn) return;

      const texto = (btn.textContent || '').trim().toLowerCase();

      if (texto === 'publicar' || texto === 'publicado') {
        ev.preventDefault();
        ev.stopPropagation();
        manejarClickPublicado(btn);
        return;
      }

      if (texto === 'destacar') {
        ev.preventDefault();
        ev.stopPropagation();
        manejarClickDestacar(btn);
        return;
      }
    });

    //log('Delegación de clicks para botón Publicar/Publicado/Destacar instalada.');
  }

  document.addEventListener('DOMContentLoaded', () => {
    //log('publicacionDestacar.js cargado. BASE_URL:', BASE || '(vacía)');
    configurarDelegacionClicks();
  });
})();
