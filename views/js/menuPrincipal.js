// views/js/menuPrincipal.js
(function () {
  'use strict';

  if (window.__EV_MENU_PRINCIPAL_INIT__ === true) return;
  window.__EV_MENU_PRINCIPAL_INIT__ = true;

  const params = new URLSearchParams(window.location.search);

  function aplicarSweetAlertGlobalFix() {
    if (!window.Swal || typeof window.Swal.fire !== 'function') return;
    if (window.__EV_SWAL_GLOBAL_FIX__ === true) return;

    window.__EV_SWAL_GLOBAL_FIX__ = true;

    const fireOriginal = window.Swal.fire.bind(window.Swal);

    function aplicarRebotePopup() {
      const popup = window.Swal.getPopup ? window.Swal.getPopup() : null;
      if (!popup) return;

      popup.classList.remove('ev-swal-bounce-premium');
      void popup.offsetWidth;
      popup.classList.add('ev-swal-bounce-premium');

      window.setTimeout(() => {
        popup.classList.remove('ev-swal-bounce-premium');
      }, 520);
    }

    window.Swal.fire = function (opts) {
      const config = (opts && typeof opts === 'object') ? { ...opts } : opts;

      if (config && typeof config === 'object') {
        const userAllowOutsideClick = config.allowOutsideClick;
        const userDidOpen = config.didOpen;

        config.allowOutsideClick = () => {
          aplicarRebotePopup();

          if (typeof userAllowOutsideClick === 'function') {
            try {
              return !!userAllowOutsideClick();
            } catch (_) {
              return false;
            }
          }

          if (typeof userAllowOutsideClick === 'boolean') {
            return userAllowOutsideClick;
          }

          return false;
        };

        config.didOpen = (popup) => {
          if (popup) {
            popup.classList.add('ev-swal-ev-theme');
          }

          if (typeof userDidOpen === 'function') {
            userDidOpen(popup);
          }
        };
      }

      return fireOriginal(config);
    };
  }

  function inyectarEstilosSweetAlertGlobales() {
    if (document.getElementById('ev-swal-global-fix-style')) return;

    const style = document.createElement('style');
    style.id = 'ev-swal-global-fix-style';
    style.textContent = `
      .swal2-popup.ev-swal-bounce-premium{
        transform-origin:center center;
        animation: evSwalBouncePremium .48s cubic-bezier(.22,.9,.3,1);
        will-change: transform, box-shadow;
      }

      @keyframes evSwalBouncePremium{
        0%{
          transform: translate3d(0,0,0) scale(1);
        }
        18%{
          transform: translate3d(-10px,0,0) scale(1.008);
        }
        34%{
          transform: translate3d(9px,0,0) scale(1.01);
        }
        50%{
          transform: translate3d(-6px,0,0) scale(1.006);
        }
        66%{
          transform: translate3d(5px,0,0) scale(1.003);
        }
        82%{
          transform: translate3d(-2px,0,0) scale(1.001);
        }
        100%{
          transform: translate3d(0,0,0) scale(1);
        }
      }
    `;
    document.head.appendChild(style);
  }

  async function mostrarLoginExitosoSiAplica() {
    if (!params.has('success')) return;

    const success = params.get('success');
    if (success !== 'login_exitoso') return;

    if (window.Swal?.fire) {
      await Swal.fire({
        icon: 'success',
        title: 'Bienvenido',
        text: 'Inicio de sesión exitoso',
        timer: 1800,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    }

    const cleanUrl = `${window.location.origin}${window.location.pathname}`;
    window.history.replaceState({}, document.title, cleanUrl);
  }

  function initOverlayScrollbars() {
    const sidebarWrapper = document.querySelector('.sidebar-wrapper');
    if (!sidebarWrapper) return;

    const os = window.OverlayScrollbarsGlobal?.OverlayScrollbars;
    if (!os) return;

    os(sidebarWrapper, {
      scrollbars: {
        theme: 'os-theme-light',
        autoHide: 'leave',
        clickScroll: true
      }
    });
  }

  async function restaurarSolicitudActivaGlobal() {
    try {
      if (window.EVMarketplace && typeof window.EVMarketplace.restoreSolicitudActiva === 'function') {
        await window.EVMarketplace.restoreSolicitudActiva();
      }
    } catch (e) {
      console.warn('[EV][Shell] No se pudo restaurar la solicitud activa:', e);
    }
  }

  async function initShell() {
    inyectarEstilosSweetAlertGlobales();
    aplicarSweetAlertGlobalFix();
    initOverlayScrollbars();
    await mostrarLoginExitosoSiAplica();
    await restaurarSolicitudActivaGlobal();
  }

  document.addEventListener('DOMContentLoaded', function () {
    initShell();
  });

  window.EVShell = {
    init: initShell,
    restaurarSolicitudActiva: restaurarSolicitudActivaGlobal
  };
})();