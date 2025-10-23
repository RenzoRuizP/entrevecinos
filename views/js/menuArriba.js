// ============================================================
// 🎯 Script para el menú superior (navbar) - Entre Vecinos
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ menuArriba.js cargado correctamente");

  const btnToggleSidebar = document.getElementById("btnToggleSidebar");
  const sidebar = document.querySelector(".main-sidebar");
  const layout = document.querySelector(".main-layout") || document.body;
  const backdrop = document.getElementById("sidebar-backdrop");
  const btnCerrarSesion = document.getElementById("btnCerrarSesion");
  const btnPerfil = document.getElementById("btnPerfil");
  const userDropdown = document.getElementById("userDropdown");
  const dropdownMenu = userDropdown?.nextElementSibling;

  // 🟢 Mostrar / ocultar menú lateral
  if (btnToggleSidebar && sidebar) {
    btnToggleSidebar.addEventListener("click", () => {
      layout.classList.toggle("sidebar-open");
      backdrop.classList.toggle("active");
    });
  }

  // 🔹 Cerrar el menú al tocar el fondo oscuro
  if (backdrop) {
    backdrop.addEventListener("click", () => {
      layout.classList.remove("sidebar-open");
      backdrop.classList.remove("active");
    });
  }

  // 🔹 Botón de perfil
  if (btnPerfil) {
    btnPerfil.addEventListener("click", (e) => {
      e.preventDefault();
      window.location.href = `${window.location.origin}/entrevecinos/views/miPerfilView.php`;
    });
  }

  // 🔹 Botón de cerrar sesión
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
        if (result.isConfirmed) {
          try {
            const response = await fetch(
              `${window.location.origin}/entrevecinos/controllers/logoutController.php`,
              { method: "GET", credentials: "include" }
            );
            const data = await response.json();

            if (data.success) {
              Swal.fire({
                icon: "success",
                title: "Sesión cerrada",
                text: "Has cerrado sesión correctamente.",
                timer: 1500,
                showConfirmButton: false
              }).then(() => {
                window.location.replace(`${window.location.origin}/entrevecinos/views/login.php`);
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
        }
      });
    });
  }

  // 🔹 Cerrar automáticamente el menú del usuario al hacer clic fuera
  document.addEventListener("click", function (event) {
    if (!dropdownMenu) return;

    const isClickInside = userDropdown.contains(event.target) || dropdownMenu.contains(event.target);
    if (!isClickInside && dropdownMenu.classList.contains("show")) {
      const dropdown = bootstrap.Dropdown.getInstance(userDropdown);
      dropdown.hide();
    }
  });

  // 🔹 Función para ajustar el dropdown según tamaño de pantalla
  function ajustarDropdownUsuario() {
    const menu = document.querySelector('#userDropdown + .dropdown-menu');
    if (!menu) return;

    if (window.innerWidth < 992) {
      // Móvil: centrar y ancho mayor
      menu.classList.remove('dropdown-menu-end');
      menu.style.left = '50%';
      menu.style.transform = 'translateX(-50%)';
      menu.style.minWidth = '90%';
    } else {
      // Desktop: alinear a la derecha del botón
      menu.classList.add('dropdown-menu-end');
      menu.style.left = '';
      menu.style.transform = '';
      menu.style.minWidth = '230px';
    }
  }

  // 🔹 Ajustar al cargar y al redimensionar
  ajustarDropdownUsuario();
  window.addEventListener('resize', ajustarDropdownUsuario);
});
