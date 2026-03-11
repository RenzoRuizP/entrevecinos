// views/js/menuPrincipal.js
// Shell principal EV
// RESPONSABILIDAD:
// - Alertas globales (ej. login exitoso)
// - Inicialización visual del shell
// - NO manejar navegación AJAX del sidebar
//   (eso queda centralizado en menuIzquierda.js para evitar dobles cargas)

(function () {
  'use strict';

  if (window.__EV_MENU_PRINCIPAL_INIT__ === true) return;
  window.__EV_MENU_PRINCIPAL_INIT__ = true;

  const params = new URLSearchParams(window.location.search);

  function mostrarLoginExitosoSiAplica() {
    if (!params.has('success')) return;

    const success = params.get('success');
    if (success !== 'login_exitoso') return;

    if (window.Swal?.fire) {
      Swal.fire({
        icon: 'success',
        title: 'Bienvenido',
        text: 'Inicio de sesión exitoso',
        timer: 2000,
        showConfirmButton: false
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

  function initShell() {
    mostrarLoginExitosoSiAplica();
    initOverlayScrollbars();
  }

  document.addEventListener('DOMContentLoaded', initShell);

  window.EVShell = {
    init: initShell
  };
})();