// ============================================================
// 🎯 Script para el menú superior (navbar) - Entre Vecinos
// Sidebar responsive + usuario + logout
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

  // ============================================================
  // 🔹 Menú lateral responsive
  // ============================================================
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

  // Cerrar sidebar al navegar desde un submenu en móvil.
  if (sidebar) {
    sidebar.addEventListener("click", (e) => {
      const link = e.target.closest("a.submenu-link");
      if (!link) return;

      if (esMobile()) {
        cerrarSidebar();
      }
    });
  }

  // Si pasa de móvil a desktop, limpiar estado offcanvas.
  mediaMobile.addEventListener?.("change", (e) => {
    if (!e.matches) {
      cerrarSidebar();
    }
  });

  // ============================================================
  // 🔹 Botón "Mis datos" → cargar vista /mi-perfil con AJAX
  // ============================================================
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

  // ============================================================
  // 🔹 Botón de cerrar sesión
  // ============================================================
  if (btnCerrarSesion) {
    btnCerrarSesion.addEventListener("click", async (e) => {
      e.preventDefault();

      Swal.fire({
        title: "¿Deseas cerrar sesión?",
        text: "Tu sesión actual se cerrará.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, salir",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#BF3604",
        cancelButtonColor: "#6c757d"
      }).then(async (result) => {
        if (!result.isConfirmed) return;

        try {
          const response = await fetch(
            `${baseUrl}/controllers/logoutController.php`,
            {
              method: "GET",
              credentials: "include",
              headers: {
                "Accept": "application/json"
              }
            }
          );

          const data = await response.json().catch(() => ({}));

          if (data.success) {
            Swal.fire({
              icon: "success",
              title: "Sesión cerrada",
              text: "Has cerrado sesión correctamente.",
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              window.location.replace(`${baseUrl}/views/login.php`);
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: data.message || "No se pudo cerrar sesión."
            });
          }
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Error del servidor",
            text: "No se pudo conectar con el servidor."
          });
        }
      });
    });
  }

  // ============================================================
  // 🔹 Cerrar dropdown del usuario al hacer clic fuera
  // ============================================================
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

  // ============================================================
  // 🔹 Ajustes responsivos del dropdown del usuario
  // ============================================================
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