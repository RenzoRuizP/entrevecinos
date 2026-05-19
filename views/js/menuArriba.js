// views/js/menuArriba.js
// ============================================================
// Entre Vecinos - Menú superior
// Sidebar responsive + usuario + logout centralizado
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  "use strict";

  const btnToggleSidebar = document.getElementById("btnToggleSidebar");
  const sidebar =
    document.getElementById("sidebar") ||
    document.querySelector(".app-sidebar") ||
    document.querySelector(".main-sidebar");

  const backdrop = document.getElementById("sidebar-backdrop");
  const btnCerrarSesion = document.getElementById("btnCerrarSesion");
  const btnPerfil = document.getElementById("btnPerfil");
  const userDropdown = document.getElementById("userDropdown");
  const dropdownMenu = userDropdown?.nextElementSibling || null;

  const baseUrl = String(window.BASE_URL || window.EV_BASE_URL || "/entrevecinos").replace(/\/+$/, "");
  const mediaMobile = window.matchMedia("(max-width: 991.98px)");

  function esMobile() {
    return mediaMobile.matches;
  }

  function sidebarEstaAbierto() {
    return !!(
      sidebar &&
      (
        sidebar.classList.contains("active") ||
        sidebar.classList.contains("open") ||
        document.body.classList.contains("ev-sidebar-open")
      )
    );
  }

  function sincronizarAccesibilidadSidebar(abierto) {
    if (btnToggleSidebar) {
      btnToggleSidebar.setAttribute("aria-expanded", abierto ? "true" : "false");
      btnToggleSidebar.setAttribute("aria-controls", "sidebar");
    }

    if (sidebar) {
      sidebar.setAttribute("aria-hidden", abierto ? "false" : "true");
    }
  }

  function abrirSidebar() {
    if (!sidebar) return;

    sidebar.classList.add("active", "open");
    backdrop?.classList.add("active", "show");
    document.body.classList.add("ev-sidebar-open");

    sincronizarAccesibilidadSidebar(true);
  }

  function cerrarSidebar() {
    if (!sidebar) return;

    sidebar.classList.remove("active", "open");
    backdrop?.classList.remove("active", "show");
    document.body.classList.remove("ev-sidebar-open");

    sincronizarAccesibilidadSidebar(false);
  }

  function alternarSidebar() {
    if (sidebarEstaAbierto()) {
      cerrarSidebar();
    } else {
      abrirSidebar();
    }
  }

  if (btnToggleSidebar && sidebar) {
    sincronizarAccesibilidadSidebar(false);

    btnToggleSidebar.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      alternarSidebar();
    });
  }

  if (backdrop) {
    backdrop.addEventListener("click", cerrarSidebar);
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && sidebarEstaAbierto()) {
      cerrarSidebar();
    }
  });

  if (sidebar) {
    sidebar.addEventListener("click", (e) => {
      const link = e.target.closest("a.submenu-link");
      if (!link) return;

      if (esMobile()) {
        cerrarSidebar();
      }
    });
  }

  mediaMobile.addEventListener?.("change", (e) => {
    if (!e.matches) {
      cerrarSidebar();
    }
  });

  if (btnPerfil) {
    btnPerfil.addEventListener("click", (e) => {
      e.preventDefault();

      const dropdownInstance = userDropdown
        ? bootstrap.Dropdown.getInstance(userDropdown)
        : null;

      dropdownInstance?.hide();

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
      return window.confirm("¿Deseas cerrar sesión?");
    }

    const result = await Swal.fire({
      title: "¿Deseas cerrar sesión?",
      text: "Tu disponibilidad para recibir pedidos se apagará automáticamente.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, salir",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#BF3604",
      cancelButtonColor: "#6c757d",
      allowOutsideClick: false,
      allowEscapeKey: true
    });

    return !!result.isConfirmed;
  }

  function detenerProcesosEVAntesDeSalir() {
    try {
      if (window.EVPollingControl && typeof window.EVPollingControl.detenerPedidosVendedor === "function") {
        window.EVPollingControl.detenerPedidosVendedor();
      }
    } catch (_) {}

    try {
      if (window.EVMarketplace && typeof window.EVMarketplace.stopPollingDisponibilidad === "function") {
        window.EVMarketplace.stopPollingDisponibilidad();
      }
    } catch (_) {}

    try {
      if (window.EVRecibirPedidos && typeof window.EVRecibirPedidos.detenerPolling === "function") {
        window.EVRecibirPedidos.detenerPolling();
      }
    } catch (_) {}
  }

  async function mostrarCierreCorrecto() {
    if (!window.Swal?.fire) {
      return;
    }

    await Swal.fire({
      icon: "success",
      title: "Sesión cerrada",
      text: "Has cerrado sesión correctamente.",
      timer: 1400,
      showConfirmButton: false,
      allowOutsideClick: false,
      allowEscapeKey: false
    });
  }

  async function mostrarErrorCierre(mensaje) {
    if (!window.Swal?.fire) {
      alert(mensaje || "No se pudo cerrar sesión.");
      return;
    }

    await Swal.fire({
      icon: "error",
      title: "No se pudo cerrar sesión",
      text: mensaje || "Ocurrió un problema al cerrar tu sesión.",
      confirmButtonText: "Aceptar",
      confirmButtonColor: "#DC2626"
    });
  }

  if (btnCerrarSesion) {
    btnCerrarSesion.addEventListener("click", async (e) => {
      e.preventDefault();

      const confirmado = await confirmarCierreSesion();
      if (!confirmado) return;

      btnCerrarSesion.classList.add("disabled");
      btnCerrarSesion.setAttribute("aria-disabled", "true");

      detenerProcesosEVAntesDeSalir();

      try {
        const response = await fetch(`${baseUrl}/logout`, {
          method: "POST",
          credentials: "include",
          cache: "no-store",
          headers: {
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
          }
        });

        const data = await response.json().catch(() => ({}));

        const ok =
          response.ok &&
          (
            data.ok === true ||
            data.success === true ||
            String(data.status || "").toLowerCase() === "success"
          );

        if (!ok) {
          await mostrarErrorCierre(data.message || data.mensaje || "No se pudo cerrar sesión.");
          btnCerrarSesion.classList.remove("disabled");
          btnCerrarSesion.removeAttribute("aria-disabled");
          return;
        }

        await mostrarCierreCorrecto();

        const redirect = data.redirect || `${baseUrl}/login`;
        window.location.replace(redirect);
      } catch (error) {
        await mostrarErrorCierre("No se pudo conectar con el servidor.");
        btnCerrarSesion.classList.remove("disabled");
        btnCerrarSesion.removeAttribute("aria-disabled");
      }
    });
  }

  document.addEventListener("click", (event) => {
    if (!userDropdown || !dropdownMenu) return;

    const clickDentro =
      userDropdown.contains(event.target) ||
      dropdownMenu.contains(event.target);

    if (!clickDentro && dropdownMenu.classList.contains("show")) {
      const dropdown = bootstrap.Dropdown.getInstance(userDropdown);
      dropdown?.hide();
    }
  });

  function ajustarDropdownUsuario() {
    const menu = document.querySelector("#userDropdown + .dropdown-menu");
    if (!menu) return;

    if (window.innerWidth < 992) {
      menu.classList.remove("dropdown-menu-end");
      menu.style.left = "50%";
      menu.style.transform = "translateX(-50%)";
      menu.style.minWidth = "min(92vw, 340px)";
    } else {
      menu.classList.add("dropdown-menu-end");
      menu.style.left = "";
      menu.style.transform = "";
      menu.style.minWidth = "230px";
    }
  }

  ajustarDropdownUsuario();
  window.addEventListener("resize", ajustarDropdownUsuario);
});