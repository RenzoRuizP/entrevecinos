// ✅ views/js/menuPrincipal.js — versión mejorada con credenciales y sesión segura
const params = new URLSearchParams(window.location.search);

// --- 🔹 Mensaje de bienvenida tras login exitoso ---
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
    // Limpiar el query string
    window.history.replaceState({}, document.title, window.location.pathname);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
  const Default = {
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'leave',
    scrollbarClickScroll: true,
  };

  const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
  if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
      scrollbars: {
        theme: Default.scrollbarTheme,
        autoHide: Default.scrollbarAutoHide,
        clickScroll: Default.scrollbarClickScroll,
      },
    });
  }

  const baseURL = window.BASE_URL || '/entrevecinos';
  const enlaces = document.querySelectorAll('.submenu-link');
  const contenedor = document.getElementById('contenido-principal');

  enlaces.forEach(link => {
    link.addEventListener('click', async e => {
      e.preventDefault();

      let vistaRuta = link.dataset.vista || link.getAttribute('href');
      if (!vistaRuta || vistaRuta === '#') return;

      if (!vistaRuta.startsWith(baseURL)) {
        vistaRuta = `${baseURL}/${vistaRuta.replace(/^\/+/, '')}`;
      }
      vistaRuta = vistaRuta.replace(/([^:]\/)\/+/g, '$1');

      contenedor.innerHTML = `
        <div class="text-center p-5">
          <div class="spinner-border text-success" role="status"></div>
          <p class="mt-3">Cargando...</p>
        </div>
      `;

      try {
        const response = await fetch(vistaRuta, {
          method: 'GET',
          credentials: 'include' // ✅ Enviar cookie auth_token
        });

        if (response.status === 401) {
          Swal.fire({
            icon: 'warning',
            title: 'Sesión expirada',
            text: 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.',
            confirmButtonText: 'Aceptar'
          }).then(() => {
            window.location.href = `${baseURL}/views/login.php?error=token_expirado`;
          });
          return;
        }

        if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

        const html = await response.text();

        if (html.includes("<title>Entre vecinos |") || html.includes("formLogin")) {
          Swal.fire({
            icon: "warning",
            title: "Sesión finalizada",
            text: "Tu sesión ha caducado. Por favor vuelve a iniciar sesión.",
            confirmButtonText: "Aceptar"
          }).then(() => {
            window.location.href = `${baseURL}/`;
          });
          return;
        }

        contenedor.innerHTML = html;

        document.querySelectorAll('.submenu-link').forEach(el => el.classList.remove('active'));
        link.classList.add('active');
      } catch (error) {
        console.error('❌ Error al cargar vista:', error);
        contenedor.innerHTML = `
          <div class="alert alert-danger m-5 shadow-sm rounded-3">
            <h5 class="mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Error</h5>
            <p>No se pudo cargar el contenido solicitado.</p>
            <small class="text-muted">${error.message}</small>
          </div>
        `;
      }
    });
  });

  // --- 🔹 Sidebar Toggle ---
  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebar-backdrop");
  const toggleBtn = document.getElementById("btnToggleSidebar");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("active");
      backdrop.style.display = sidebar.classList.contains("active") ? "block" : "none";
    });
  }

  if (backdrop) {
    backdrop.addEventListener("click", () => {
      sidebar.classList.remove("active");
      backdrop.style.display = "none";
    });
  }
});
