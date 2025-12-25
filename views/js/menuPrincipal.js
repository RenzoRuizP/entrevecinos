// views/js/menuPrincipal.js — SOLO utilidades (sin navegación AJAX)
const params = new URLSearchParams(window.location.search);

if (params.has('success')) {
  const success = params.get('success');
  if (success === 'login_exitoso') {
    Swal.fire({
      icon: 'success',
      title: 'Bienvenido',
      text: 'Inicio de sesión exitoso',
      timer: 2000,
      showConfirmButton: false,
    });
    window.history.replaceState({}, document.title, window.location.pathname);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // OverlayScrollbars sidebar (si lo usas)
  const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
  const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

  if (sidebarWrapper && window.OverlayScrollbarsGlobal?.OverlayScrollbars) {
    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
      scrollbars: {
        theme: 'os-theme-light',
        autoHide: 'leave',
        clickScroll: true,
      },
    });
  }
});
