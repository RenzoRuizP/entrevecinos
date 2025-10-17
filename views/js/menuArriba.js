document.addEventListener("DOMContentLoaded", () => {
  const btnCerrarSesion = document.getElementById("btnCerrarSesion");
  const btnPerfil = document.getElementById("btnPerfil");
  const contenedor = document.getElementById("contenido-principal");

  const sidebar = document.getElementById("sidebar");
  const toggleSidebar = document.getElementById("btnToggleSidebar");
  const userToggleMobile = document.getElementById("btnUserToggleMobile");
  const dropdownMenu = document.getElementById("userDropdownMenu");

  /* ============================================================
     🔹 CERRAR SESIÓN (con confirmación)
  ============================================================ */
  if (btnCerrarSesion) {
    btnCerrarSesion.addEventListener("click", (e) => {
      e.preventDefault();

      Swal.fire({
        title: "¿Cerrar sesión?",
        text: "Se cerrará tu sesión actual.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, salir",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (!result.isConfirmed) return;

        fetch("/entrevecinos/logout", {
          method: "GET",
          headers: { "X-Requested-With": "XMLHttpRequest" },
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              Swal.fire({
                icon: "success",
                title: "Sesión cerrada",
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
              }).then(() => {
                window.location.href = "/entrevecinos/";
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Error",
                text:
                  data.message || "No se pudo cerrar sesión correctamente.",
              });
            }
          })
          .catch(() => {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "No se pudo cerrar sesión.",
            });
          });
      });
    });
  }

  /* ============================================================
     🔹 CARGAR PERFIL DINÁMICAMENTE
  ============================================================ */
  if (btnPerfil && contenedor) {
    btnPerfil.addEventListener("click", async (e) => {
      e.preventDefault();

      const ruta = "/entrevecinos/mi-perfil";
      contenedor.innerHTML = `
        <div class="text-center p-5">
          <div class="spinner-border text-success" role="status"></div>
          <p class="mt-3">Cargando perfil...</p>
        </div>
      `;

      try {
        const response = await fetch(ruta);
        if (!response.ok) throw new Error("Error al cargar la vista de perfil.");
        const html = await response.text();
        contenedor.innerHTML = html;
      } catch {
        contenedor.innerHTML = `
          <div class="alert alert-danger m-5 text-center">
            <strong>Error:</strong> No se pudo cargar la vista de perfil.
          </div>
        `;
      }
    });
  }

  /* ============================================================
     🔹 MENÚ LATERAL (Hamburguesa Responsiva)
  ============================================================ */
  // 🔹 Sincroniza el botón hamburguesa del topbar con el menú lateral
    document.addEventListener("DOMContentLoaded", () => {
      const sidebar = document.querySelector(".main-sidebar");
      const toggleBtn = document.getElementById("toggle-sidebar");
      const backdrop = document.getElementById("sidebar-backdrop");

      if (!sidebar || !toggleBtn) return;

      const toggleSidebar = () => {
        sidebar.classList.toggle("active");
        backdrop.classList.toggle("active");
      };

      toggleBtn.addEventListener("click", toggleSidebar);
      backdrop.addEventListener("click", toggleSidebar);
    });

  /* ============================================================
     🔹 DROPDOWN DEL USUARIO (modo móvil y escritorio)
  ============================================================ */
  if (userToggleMobile && dropdownMenu) {
    userToggleMobile.addEventListener("click", (e) => {
      e.preventDefault();
      dropdownMenu.classList.toggle("show");
      dropdownMenu.classList.toggle("fade-in");
    });

    document.addEventListener("click", (e) => {
      if (
        !userToggleMobile.contains(e.target) &&
        !dropdownMenu.contains(e.target)
      ) {
        dropdownMenu.classList.remove("show", "fade-in");
      }
    });
  }
});
