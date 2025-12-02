/* publicacionPublicarWallet.js
   Integración botón "Publicado" con Billetera:
   - Valida saldo (S/ 1.00) en la billetera del vecino
   - Si alcanza, publica la publicación (visible=2, aparece en Recomendados)
   - Si no alcanza, muestra mensaje amigable pidiendo recargar
*/

(function () {
  'use strict';

  const LOG_PREFIX = '[PUBLI+BILLETERA]';

  const BASE = (window.BASE_URL || '').replace(/\/+$/, '');
/*
  function log() {
    console.log(LOG_PREFIX, ...arguments);
  }
   
  function warn() {
    console.warn(LOG_PREFIX, ...arguments);
  }
  function error() {
    console.error(LOG_PREFIX, ...arguments);
  }
 */
  const notify = (icon, title, text) => {
    if (typeof window.evNotify === 'function') {
      window.evNotify(icon, title, text);
      return;
    }
    if (window.Swal?.fire) {
      Swal.fire({
        icon,
        title,
        text,
        confirmButtonText: 'Aceptar',
        customClass: { confirmButton: 'btn btn-success' },
        buttonsStyling: false
      });
      return;
    }
    alert((title ? (title + '\n\n') : '') + (text || ''));
  };

  async function cobrarEnBilletera(codigoPublicacion) {
    const url = `${BASE}/api/billetera/publicacion/${codigoPublicacion}/cobrar-destacar`;

    const resp = await fetch(url, {
      method: 'POST',
      headers: {
        'Accept': 'application/json'
      },
      credentials: 'include'
    });

    const data = await resp.json().catch(() => ({}));

    // Sesión expirada
    if (resp.status === 401) {
      notify(
        'error',
        'Sesión expirada',
        'Tu sesión ha expirado. Vuelve a iniciar sesión para poder publicar tus avisos.'
      );
      setTimeout(() => {
        window.location.href = `${BASE}/`;
      }, 1500);
      throw new Error('Sesión expirada');
    }

    if (!resp.ok || data.ok === false) {
      const codigo = data.codigo || '';
      // Caso saldo insuficiente: mensaje amigable
      if (codigo === 'SALDO_INSUFICIENTE') {
        notify(
          'warning',
          'Saldo insuficiente en tu billetera',
          'Tu billetera no tiene saldo suficiente para destacar esta publicación. Recarga al menos S/ 1.00 para que tu aviso aparezca en la sección Recomendados y llegue a más vecinos.'
        );
        throw new Error('SALDO_INSUFICIENTE');
      }

      const msg = data.mensaje || data.error || 'No se pudo realizar el cargo en tu billetera.';
      notify('error', 'No pudimos procesar el cargo', msg);
      throw new Error(msg);
    }

    return data; // { ok: true, saldo_actual: ... }
  }

  async function publicarPublicacion(codigoPublicacion) {
    const url = `${BASE}/api/publicacion/${codigoPublicacion}/publicar`;

    const resp = await fetch(url, {
      method: 'POST',
      headers: {
        'Accept': 'application/json'
      },
      credentials: 'include'
    });

    const data = await resp.json().catch(() => ({}));

    if (resp.status === 401) {
      notify(
        'error',
        'Sesión expirada',
        'Tu sesión ha expirado. Vuelve a iniciar sesión para continuar.'
      );
      setTimeout(() => {
        window.location.href = `${BASE}/`;
      }, 1500);
      throw new Error('Sesión expirada');
    }

    if (!resp.ok || data.ok === false) {
      const msg = data.mensaje || data.error || 'No se pudo publicar tu aviso.';
      notify('error', 'No pudimos publicar tu aviso', msg);
      throw new Error(msg);
    }

    return data;
  }

  /**
   * Flujo completo:
   * 1) Confirmar acción con el vecino
   * 2) Cobrar S/ 1.00 en la billetera
   * 3) Publicar la publicación (para que aparezca en Marketplace / Recomendados)
   */
  async function manejarClickPublicado(btn) {
    try {
      // Obtener ID de publicación desde data attributes
      const codigoPublicacion =
        btn.dataset.id ||
        btn.dataset.codigoPublicacion ||
        btn.getAttribute('data-id');

      if (!codigoPublicacion) {
        warn('Botón "Publicado" sin ID de publicación asociado.', btn);
        notify('error', 'No se pudo identificar la publicación', 'Intenta refrescar la página e inténtalo nuevamente.');
        return;
      }

      // Confirmación amigable
      if (window.Swal?.fire) {
        const result = await Swal.fire({
          title: '¿Quieres publicar este aviso?',
          text: 'Se realizará un cargo de S/ 1.00 en tu billetera digital para destacar esta publicación en la sección Recomendados del Marketplace.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Sí, publicar ahora',
          cancelButtonText: 'Cancelar',
          reverseButtons: true,
          customClass: {
            confirmButton: 'btn btn-success me-2',
            cancelButton: 'btn btn-outline-secondary'
          },
          buttonsStyling: false
        });

        if (!result.isConfirmed) {
          return;
        }
      }

      // Opcional: deshabilitar temporalmente el botón
      btn.disabled = true;
      btn.dataset.originalText = btn.dataset.originalText || btn.textContent;
      btn.textContent = 'Publicando…';

      // 1) Cobrar en billetera (S/ 1.00)
      const dataCobro = await cobrarEnBilletera(codigoPublicacion);
      log('Cobro billetera OK', dataCobro);

      // 2) Publicar la publicación
      const dataPub = await publicarPublicacion(codigoPublicacion);
      log('Publicación publicada OK', dataPub);

      // 3) Mensaje de éxito
      notify(
        'success',
        'Tu aviso ya está publicado',
        'Se realizó el cargo de S/ 1.00 en tu billetera y tu publicación ahora aparece en el Marketplace. También podrá destacarse en Recomendados para llegar a más vecinos.'
      );

      // Si tienes una función global para recargar la tabla/listado, la puedes llamar aquí.
      // Por ejemplo: if (window.EVPublicaciones && window.EVPublicaciones.recargarLista) { ... }
      if (window.EVPublicaciones && typeof window.EVPublicaciones.recargarLista === 'function') {
        window.EVPublicaciones.recargarLista();
      } else {
        // Fallback sencillo: recargar la página
        setTimeout(() => {
          window.location.reload();
        }, 600);
      }

    } catch (err) {
      error('Error en flujo Publicar + Billetera:', err);
    } finally {
      // Restaurar botón
      if (btn) {
        btn.disabled = false;
        if (btn.dataset.originalText) {
          btn.textContent = btn.dataset.originalText;
        }
      }
    }
  }

  // Delegación de eventos: detectamos cualquier click en un botón de "Publicado"
  function bindEventos() {
    document.addEventListener('click', (ev) => {
      const btn = ev.target.closest('[data-ev-publicar]');
      if (!btn) return;

      ev.preventDefault();
      manejarClickPublicado(btn);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    //log('publicacionPublicarWallet.js cargado. BASE:', BASE || '(vacía)');
    bindEventos();
  });
})();
