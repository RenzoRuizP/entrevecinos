// --- 🔹 Mensaje de bienvenida tras login exitoso ---
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
    // Limpiar el query string para no repetir el mensaje
    window.history.replaceState({}, document.title, window.location.pathname);
  }
}

// --- 🔹 Configuración del Scrollbar lateral ---
const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
const Default = {
  scrollbarTheme: 'os-theme-light',
  scrollbarAutoHide: 'leave',
  scrollbarClickScroll: true,
};

document.addEventListener('DOMContentLoaded', () => {
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

  // --- 🔹 Base URL del proyecto ---
  const baseURL = window.BASE_URL || '/entrevecinos';

  // --- 🔹 Carga dinámica de vistas ---
  const enlaces = document.querySelectorAll('.submenu-link');
  const contenedor = document.getElementById('contenido-principal');

  enlaces.forEach(link => {
    link.addEventListener('click', async e => {
      e.preventDefault();

      let vistaRuta = link.dataset.vista || link.getAttribute('href');
      if (!vistaRuta || vistaRuta === '#') return;

      // 🔹 Normalizar ruta para evitar "//"
      if (!vistaRuta.startsWith(baseURL)) {
        vistaRuta = `${baseURL}/${vistaRuta.replace(/^\/+/, '')}`;
      }
      vistaRuta = vistaRuta.replace(/([^:]\/)\/+/g, '$1');

      console.log('📄 Cargando vista:', vistaRuta);

      // 🔹 Mostrar spinner mientras carga
      contenedor.innerHTML = `
        <div class="text-center p-5">
          <div class="spinner-border text-success" role="status"></div>
          <p class="mt-3">Cargando...</p>
        </div>
      `;

      try {
        const response = await fetch(vistaRuta);
        if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

        const html = await response.text();
        contenedor.innerHTML = html;

        // ✅ Marcar enlace activo visualmente
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
});

document.addEventListener("DOMContentLoaded", () => {
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