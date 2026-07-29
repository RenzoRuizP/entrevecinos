// views/js/publicacionDestacar.js  (EV) - versión robusta por data-attrs
(function () {
  'use strict';

  const BASE = (window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/+$/, '');
  const LOG_PREFIX = '[PRODUCTO_DESTACAR]';

  function warn() { if (console?.warn) console.warn(LOG_PREFIX, ...arguments); }
  function error() { if (console?.error) console.error(LOG_PREFIX, ...arguments); }

  function getCodigoProducto(btn) {
    if (!btn) return null;
    const codigo =
      btn.dataset.codigoProducto ||
      btn.dataset.codigo ||
      btn.dataset.id ||
      btn.dataset.productoId ||
      btn.dataset.prodId;

    const n = parseInt(codigo || '', 10);
    return Number.isFinite(n) && n > 0 ? n : null;
  }

  async function debitarProductoDestacado(codigoProducto) {
    // ✅ Usamos el endpoint real existente en tu index.php
    const url = `${BASE}/api/billetera/debitar-publicacion`;

    try {
      const resp = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        credentials: 'include',
        // ✅ apiBilleteraController espera codigo_publicacion
        body: JSON.stringify({ codigo_publicacion: codigoProducto })
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
        if (window.Swal?.fire) {
          await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo procesar el pago para destacar el producto.'
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
              text: 'No tienes saldo suficiente para destacar este producto.'
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

      return { ok: true, data: json };

    } catch (err) {
      error('Excepción debitarProductoDestacado:', err);
      if (window.Swal?.fire) {
        await Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: 'No pudimos procesar el pago. Intenta nuevamente.'
        });
      }
      return { ok: false };
    }
  }

  async function manejarDestacar(btn) {
    const codigo = getCodigoProducto(btn);
    if (!codigo) {
      warn('No se pudo determinar el código de producto.');
      if (window.Swal?.fire) {
        await Swal.fire({
          icon: 'warning',
          title: 'No se pudo procesar',
          text: 'No pudimos identificar el producto. Recarga la página.'
        });
      }
      return;
    }

    if (!window.Swal?.fire) {
      const seguir = window.confirm('Se descontará S/ 1.00 de tu billetera para destacar este producto durante 24 horas. ¿Deseas continuar?');
      if (!seguir) return;
      const res = await debitarProductoDestacado(codigo);
      if (res.ok) window.location.reload();
      return;
    }

    const result = await Swal.fire({
      icon: 'question',
      title: 'Destacar producto',
      text: 'Se descontará S/ 1.00 de tu billetera para destacar este producto durante 24 horas. ¿Deseas continuar?',
      showCancelButton: true,
      confirmButtonText: 'Sí, destacar',
      cancelButtonText: 'Cancelar',
      reverseButtons: true
    });

    if (!result.isConfirmed) return;

    const deb = await debitarProductoDestacado(codigo);
    if (deb.ok) {
      await Swal.fire({
        icon: 'success',
        title: 'Producto destacado',
        text: 'Se descontó S/ 1.00 de tu billetera y tu producto aparecerá como destacado durante 24 horas.',
        confirmButtonText: 'Entendido'
      });
      window.location.reload();
    }
  }

  function instalarDelegacion() {
    document.addEventListener('click', (ev) => {
      const btn = ev.target?.closest?.('button[data-action]');
      if (!btn) return;

      const action = (btn.dataset.action || '').trim().toLowerCase();
      if (action !== 'destacar') return;

      ev.preventDefault();
      ev.stopPropagation();
      manejarDestacar(btn);
    });
  }

  document.addEventListener('DOMContentLoaded', instalarDelegacion);
})();
