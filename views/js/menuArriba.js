// views/js/menuArriba.js
// ============================================================
// Entre Vecinos - Menú superior premium
// Sidebar responsive + perfil móvil enfocado + logout centralizado
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  if (window.__EV_MENU_ARRIBA_INIT__ === true) return;
  window.__EV_MENU_ARRIBA_INIT__ = true;

  const btnToggleSidebar = document.getElementById('btnToggleSidebar');
  const btnToggleIcon = btnToggleSidebar?.querySelector('i') || null;
  const sidebar =
    document.getElementById('sidebar') ||
    document.querySelector('.app-sidebar') ||
    document.querySelector('.main-sidebar');

  const sidebarBackdrop = document.getElementById('sidebar-backdrop');
  const btnCerrarSesion = document.getElementById('btnCerrarSesion');
  const btnPerfil = document.getElementById('btnPerfil');
  const userDropdown = document.getElementById('userDropdown');
  const dropdownMenu = userDropdown?.nextElementSibling || null;

  const baseUrl = String(window.BASE_URL || window.EV_BASE_URL || '/entrevecinos').replace(/\/+$/, '');
  const mediaMobile = window.matchMedia('(max-width: 991.98px)');

  function esMobile() {
    return mediaMobile.matches;
  }

  function dropdownUsuarioInstance() {
    if (!userDropdown || !window.bootstrap?.Dropdown) return null;
    return window.bootstrap.Dropdown.getOrCreateInstance(userDropdown);
  }

  function obtenerBackdropPerfil() {
    let elemento = document.getElementById('evUserMenuBackdrop');

    if (!elemento) {
      elemento = document.createElement('div');
      elemento.id = 'evUserMenuBackdrop';
      elemento.className = 'ev-user-menu-backdrop';
      elemento.setAttribute('aria-hidden', 'true');
      document.body.appendChild(elemento);
    }

    return elemento;
  }

  const userMenuBackdrop = obtenerBackdropPerfil();

  function perfilEstaAbierto() {
    return !!(dropdownMenu && dropdownMenu.classList.contains('show'));
  }

  function mostrarBackdropPerfil() {
    if (!esMobile() || !userMenuBackdrop) return;

    userMenuBackdrop.classList.add('is-visible');
    userMenuBackdrop.setAttribute('aria-hidden', 'false');
    document.body.classList.add('ev-user-menu-open');
  }

  function ocultarBackdropPerfil() {
    if (!userMenuBackdrop) return;

    userMenuBackdrop.classList.remove('is-visible');
    userMenuBackdrop.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('ev-user-menu-open');
  }

  function cerrarPerfil() {
    if (!perfilEstaAbierto()) {
      ocultarBackdropPerfil();
      return;
    }

    dropdownUsuarioInstance()?.hide();
    ocultarBackdropPerfil();
  }

  function sidebarEstaAbierto() {
    return !!(
      sidebar &&
      (
        sidebar.classList.contains('active') ||
        sidebar.classList.contains('open') ||
        document.body.classList.contains('ev-sidebar-open')
      )
    );
  }

  function actualizarBotonSidebar(abierto) {
    if (!btnToggleSidebar) return;

    btnToggleSidebar.classList.toggle('is-open', abierto);
    btnToggleSidebar.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    btnToggleSidebar.setAttribute('aria-controls', 'sidebar');
    btnToggleSidebar.setAttribute('aria-label', abierto ? 'Cerrar menú lateral' : 'Mostrar menú lateral');

    if (btnToggleIcon) {
      btnToggleIcon.className = abierto
        ? 'bi bi-x-lg text-white'
        : 'bi bi-list text-white';
    }
  }

  function sincronizarAccesibilidadSidebar(abierto) {
    actualizarBotonSidebar(abierto);

    if (!sidebar) return;

    if (esMobile()) {
      sidebar.setAttribute('aria-hidden', abierto ? 'false' : 'true');
      return;
    }

    sidebar.removeAttribute('aria-hidden');
  }

  function abrirSidebar() {
    if (!sidebar) return;

    cerrarPerfil();

    sidebar.classList.add('active', 'open');
    sidebarBackdrop?.classList.add('active', 'show');
    document.body.classList.add('ev-sidebar-open');

    sincronizarAccesibilidadSidebar(true);
  }

  function cerrarSidebar() {
    if (!sidebar) return;

    sidebar.classList.remove('active', 'open');
    sidebarBackdrop?.classList.remove('active', 'show');
    document.body.classList.remove('ev-sidebar-open');

    sincronizarAccesibilidadSidebar(false);
  }

  function alternarSidebar() {
    if (sidebarEstaAbierto()) {
      cerrarSidebar();
      return;
    }

    abrirSidebar();
  }

  if (btnToggleSidebar && sidebar) {
    sincronizarAccesibilidadSidebar(false);

    btnToggleSidebar.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      alternarSidebar();
    });
  }

  sidebarBackdrop?.addEventListener('click', cerrarSidebar);

  userMenuBackdrop?.addEventListener('click', () => {
    cerrarPerfil();
  });

  if (sidebar) {
    sidebar.addEventListener('click', (event) => {
      const link = event.target.closest('a.submenu-link');
      if (!link) return;

      if (esMobile()) {
        cerrarSidebar();
      }
    });
  }

  if (userDropdown && dropdownMenu) {
    userDropdown.addEventListener('show.bs.dropdown', () => {
      if (esMobile()) {
        cerrarSidebar();
      }
    });

    userDropdown.addEventListener('shown.bs.dropdown', () => {
      mostrarBackdropPerfil();
    });

    userDropdown.addEventListener('hide.bs.dropdown', () => {
      ocultarBackdropPerfil();
    });

    userDropdown.addEventListener('hidden.bs.dropdown', () => {
      ocultarBackdropPerfil();
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    if (perfilEstaAbierto()) {
      cerrarPerfil();
      return;
    }

    if (sidebarEstaAbierto()) {
      cerrarSidebar();
    }
  });

  mediaMobile.addEventListener?.('change', (event) => {
    cerrarSidebar();

    if (!event.matches) {
      ocultarBackdropPerfil();
    } else if (perfilEstaAbierto()) {
      mostrarBackdropPerfil();
    }

    sincronizarAccesibilidadSidebar(false);
    ajustarDropdownUsuario();
  });

  if (btnPerfil) {
    btnPerfil.addEventListener('click', (event) => {
      event.preventDefault();

      dropdownUsuarioInstance()?.hide();
      ocultarBackdropPerfil();

      const linkPerfil =
        document.querySelector('.submenu-link[data-vista="/mi-perfil"]') ||
        document.querySelector(`.submenu-link[href="${baseUrl}/mi-perfil"]`) ||
        document.querySelector('.submenu-link[href$="/mi-perfil"]');

      if (linkPerfil) {
        linkPerfil.click();
      } else {
        window.location.href = `${baseUrl}/mi-perfil`;
      }
    });
  }

  async function confirmarCierreSesion() {
    if (!window.Swal?.fire) {
      return window.confirm('¿Deseas cerrar sesión?');
    }

    const result = await Swal.fire({
      title: '¿Deseas cerrar sesión?',
      text: 'Tu disponibilidad para recibir pedidos se apagará automáticamente.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, salir',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#BF3604',
      cancelButtonColor: '#6c757d',
      allowOutsideClick: false,
      allowEscapeKey: true
    });

    return !!result.isConfirmed;
  }

  function detenerProcesosEVAntesDeSalir() {
    try {
      if (window.EVPollingControl && typeof window.EVPollingControl.detenerPedidosVendedor === 'function') {
        window.EVPollingControl.detenerPedidosVendedor();
      }
    } catch (_) {}

    try {
      if (window.EVMarketplace && typeof window.EVMarketplace.stopPollingDisponibilidad === 'function') {
        window.EVMarketplace.stopPollingDisponibilidad();
      }
    } catch (_) {}

    try {
      if (window.EVRecibirPedidos && typeof window.EVRecibirPedidos.detenerPolling === 'function') {
        window.EVRecibirPedidos.detenerPolling();
      }
    } catch (_) {}
  }

  async function mostrarCierreCorrecto() {
    if (!window.Swal?.fire) return;

    await Swal.fire({
      icon: 'success',
      title: 'Sesión cerrada',
      text: 'Has cerrado sesión correctamente.',
      timer: 1400,
      showConfirmButton: false,
      allowOutsideClick: false,
      allowEscapeKey: false
    });
  }

  async function mostrarErrorCierre(mensaje) {
    if (!window.Swal?.fire) {
      alert(mensaje || 'No se pudo cerrar sesión.');
      return;
    }

    await Swal.fire({
      icon: 'error',
      title: 'No se pudo cerrar sesión',
      text: mensaje || 'Ocurrió un problema al cerrar tu sesión.',
      confirmButtonText: 'Aceptar',
      confirmButtonColor: '#DC2626'
    });
  }

  if (btnCerrarSesion) {
    btnCerrarSesion.addEventListener('click', async (event) => {
      event.preventDefault();

      const confirmado = await confirmarCierreSesion();
      if (!confirmado) return;

      btnCerrarSesion.classList.add('disabled');
      btnCerrarSesion.setAttribute('aria-disabled', 'true');

      detenerProcesosEVAntesDeSalir();

      try {
        const response = await fetch(`${baseUrl}/logout`, {
          method: 'POST',
          credentials: 'include',
          cache: 'no-store',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await response.json().catch(() => ({}));

        const ok = response.ok && (
          data.ok === true ||
          data.success === true ||
          String(data.status || '').toLowerCase() === 'success'
        );

        if (!ok) {
          await mostrarErrorCierre(data.message || data.mensaje || 'No se pudo cerrar sesión.');
          btnCerrarSesion.classList.remove('disabled');
          btnCerrarSesion.removeAttribute('aria-disabled');
          return;
        }

        await mostrarCierreCorrecto();
        window.location.replace(data.redirect || `${baseUrl}/login`);
      } catch (_) {
        await mostrarErrorCierre('No se pudo conectar con el servidor.');
        btnCerrarSesion.classList.remove('disabled');
        btnCerrarSesion.removeAttribute('aria-disabled');
      }
    });
  }

  document.addEventListener('click', (event) => {
    if (!userDropdown || !dropdownMenu) return;

    const clickDentro =
      userDropdown.contains(event.target) ||
      dropdownMenu.contains(event.target);

    if (!clickDentro && perfilEstaAbierto() && !userMenuBackdrop?.contains(event.target)) {
      dropdownUsuarioInstance()?.hide();
    }
  });

  function ajustarDropdownUsuario() {
    if (!dropdownMenu) return;

    if (esMobile()) {
      dropdownMenu.classList.remove('dropdown-menu-end');
      dropdownMenu.style.left = '50%';
      dropdownMenu.style.transform = 'translateX(-50%)';
      dropdownMenu.style.minWidth = 'min(92vw, 340px)';
      return;
    }

    dropdownMenu.classList.add('dropdown-menu-end');
    dropdownMenu.style.left = '';
    dropdownMenu.style.transform = '';
    dropdownMenu.style.minWidth = '230px';
  }

  ajustarDropdownUsuario();
  window.addEventListener('resize', ajustarDropdownUsuario);
});
