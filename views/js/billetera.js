// views/js/billetera.js
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  const LOG_PREFIX = '[BILLETERA]';

  let refs = {
    wrapper: null,
    saldo: null,
    emptyState: null,
    movimientos: null,
  };

  function log() {
    console.log(LOG_PREFIX, ...arguments);
  }

  function warn() {
    console.warn(LOG_PREFIX, ...arguments);
  }

  function error() {
    console.error(LOG_PREFIX, ...arguments);
  }

  // ------------------------------------
  // Captura de referencias DOM de la vista
  // ------------------------------------
  function capturarRefs() {
    refs.wrapper     = document.querySelector('.ev-wallet-wrapper');
    if (!refs.wrapper) {
      return false; // la vista aún no está montada
    }

    refs.saldo       = document.getElementById('ev_wallet_saldo');
    refs.emptyState  = document.getElementById('ev_wallet_empty_state');
    refs.movimientos = document.getElementById('ev_wallet_movimientos');

    return true;
  }

  // ------------------------------------
  // Inicializar la vista de billetera
  // ------------------------------------
  function inicializarVista() {
    if (!capturarRefs()) {
      return;
    }

    log('Vista Mi Billetera detectada en DOM. BASE_URL:', BASE || '(vacía)');

    // Por ahora: saldo 0.00 y estado vacío visible
    if (refs.saldo) {
      refs.saldo.textContent = 'S/ 0.00';
    }

    if (refs.emptyState) {
      refs.emptyState.classList.remove('d-none');
    }
    if (refs.movimientos) {
      refs.movimientos.classList.add('d-none');
    }
  }

  // ------------------------------------
  // Cargar parcial /billetera en .content-wrapper
  // ------------------------------------
  async function cargarVistaParcialBilletera(contentWrapper) {
    const url = `${BASE}/billetera?partial=1`;

    try {
      const resp = await fetch(url, {
        method: 'GET',
        headers: {
          'X-Partial': '1',
          'Accept': 'text/html',
        },
        credentials: 'include',
      });

      if (resp.status === 401) {
        error('No autorizado al cargar billetera.');
        if (window.Swal?.fire) {
          Swal.fire({
            icon: 'error',
            title: 'Sesión expirada',
            text: 'Tu sesión ha expirado. Vuelve a iniciar sesión.',
          }).then(() => {
            window.location.href = `${BASE}/`;
          });
        } else {
          window.location.href = `${BASE}/`;
        }
        return;
      }

      if (!resp.ok) {
        const txt = await resp.text().catch(() => '');
        error('Error HTTP al cargar billetera:', resp.status, txt);
        if (window.Swal?.fire) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo cargar tu billetera. Intenta nuevamente.',
          });
        }
        return;
      }

      const html = await resp.text();
      contentWrapper.innerHTML = html;

      // Una vez insertado el HTML, inicializamos la vista
      inicializarVista();

    } catch (err) {
      error('Excepción al cargar billetera:', err);
      if (window.Swal?.fire) {
        Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: 'No pudimos cargar tu billetera. Revisa tu conexión e inténtalo otra vez.',
        });
      }
    }
  }

  // ------------------------------------
  // Hook al menú lateral "Mi billetera"
  // ------------------------------------
  function engancharMenuBilletera() {
    const contentWrapper = document.querySelector('.content-wrapper');
    if (!contentWrapper) {
      warn('No se encontró .content-wrapper, no se puede enganchar Mi billetera.');
      return;
    }

    // Buscar el enlace cuyo texto visible sea "Mi billetera"
    const enlaces = Array.from(document.querySelectorAll('a'));
    const linkBilletera = enlaces.find((a) => {
      const txt = (a.textContent || '').trim().toLowerCase();
      return txt === 'mi billetera';
    });

    if (!linkBilletera) {
      warn('No se encontró un enlace de menú con texto "Mi billetera".');
      return;
    }

    // Evitar múltiples registros
    if (linkBilletera.dataset.evWalletHooked === '1') {
      return;
    }
    linkBilletera.dataset.evWalletHooked = '1';

    linkBilletera.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      log('Click en menú Mi billetera, cargando vista parcial…');
      cargarVistaParcialBilletera(contentWrapper);
    });

    log('Hook de menú "Mi billetera" instalado correctamente.');
  }

  // ------------------------------------
  // Inicialización estándar
  // ------------------------------------
  document.addEventListener('DOMContentLoaded', () => {
    log('billetera.js cargado. BASE_URL:', BASE || '(vacía)');
    engancharMenuBilletera();
    // Si la vista ya estuviera montada por acceso directo:
    inicializarVista();
  });

  // ------------------------------------
  // Soporte para carga dinámica (por si otro JS inserta la vista)
// ------------------------------------
  const observer = new MutationObserver(() => {
    const wrapperActual = document.querySelector('.ev-wallet-wrapper');
    if (wrapperActual && wrapperActual !== refs.wrapper) {
      inicializarVista();
    }

    // Por si el menú se redibuja dinámicamente
    engancharMenuBilletera();
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });

  // Exponer para llamadas manuales
  window.EVWallet = {
    init: inicializarVista,
  };
})();
