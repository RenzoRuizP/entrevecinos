// ✅ views/js/menuPrincipal.js — estable (delegación + BASE_URL normalizado)
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
    window.history.replaceState({}, document.title, window.location.pathname);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // =============================
  // 1) OverlayScrollbars sidebar
  // =============================
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

  // =============================
  // 2) BASE_URL robusto
  // =============================
  const rawBase = (window.BASE_URL ?? '/entrevecinos').toString().trim();
  const BASE = rawBase.replace(/\/+$/, '') || '/entrevecinos'; // sin slash final

  const contenedor = document.getElementById('contenido-principal');

  function normalizarUrl(vistaRuta) {
    if (!vistaRuta) return null;

    // si viene como '#'
    if (vistaRuta === '#') return null;

    // si viene como URL absoluta, la respetamos
    if (/^https?:\/\//i.test(vistaRuta)) return vistaRuta;

    // limpiar dobles slashes
    const clean = vistaRuta.replace(/([^:]\/)\/+/g, '$1');

    // Si ya empieza con BASE (/entrevecinos/...) => ok
    if (clean.startsWith(BASE + '/')) return clean;

    // Si empieza con '/' (ej: /publicacion o /views/x.php) => lo volvemos relativo a BASE
    if (clean.startsWith('/')) {
      return `${BASE}${clean}`.replace(/([^:]\/)\/+/g, '$1');
    }

    // Caso común: "views/publicacionView.php" o "publicacion"
    return `${BASE}/${clean}`.replace(/([^:]\/)\/+/g, '$1');
  }

  async function cargarVista(vistaRuta, linkActivo = null) {
    if (!contenedor) return;

    const urlFinal = normalizarUrl(vistaRuta);
    if (!urlFinal) return;

    // Loader
    contenedor.innerHTML = `
      <div class="text-center p-5">
        <div class="spinner-border text-success" role="status"></div>
        <p class="mt-3">Cargando...</p>
      </div>
    `;

    try {
      const response = await fetch(urlFinal, {
        method: 'GET',
        credentials: 'include'
      });

      if (response.status === 401) {
        Swal.fire({
          icon: 'warning',
          title: 'Sesión expirada',
          text: 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.',
          confirmButtonText: 'Aceptar'
        }).then(() => {
          window.location.href = `${BASE}/views/login.php?error=token_expirado`;
        });
        return;
      }

      if (!response.ok) {
        throw new Error(`Error HTTP ${response.status} al cargar: ${urlFinal}`);
      }

      const html = await response.text();

      // Fallback: si te devolvió login o layout, asumimos expiración
      if (html.includes("formLogin") || html.includes("<title>Entre vecinos |")) {
        Swal.fire({
          icon: "warning",
          title: "Sesión finalizada",
          text: "Tu sesión ha caducado. Por favor vuelve a iniciar sesión.",
          confirmButtonText: "Aceptar"
        }).then(() => {
          window.location.href = `${BASE}/`;
        });
        return;
      }

      contenedor.innerHTML = html;

      // Marcar activo
      document.querySelectorAll('.submenu-link').forEach(el => el.classList.remove('active'));
      if (linkActivo) linkActivo.classList.add('active');

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
  }

  // ==========================================================
  // 3) Delegación de eventos (SOLUCIONA que no "enganche" click)
  // ==========================================================
  document.addEventListener('click', (e) => {
    const link = e.target.closest('.submenu-link');
    if (!link) return;

    e.preventDefault();

    // Prioridad: data-vista
    let vistaRuta = link.dataset.vista || link.getAttribute('href');

    // Importante: evitar que te lleve a "/publicacion" en la raíz
    // Recomendación: usa data-vista="views/publicacionView.php"
    cargarVista(vistaRuta, link);
  });

  // =============================
  // 4) Sidebar Toggle
  // =============================
  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebar-backdrop");
  const toggleBtn = document.getElementById("btnToggleSidebar");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("active");
      if (backdrop) backdrop.style.display = sidebar.classList.contains("active") ? "block" : "none";
    });
  }

  if (backdrop) {
    backdrop.addEventListener("click", () => {
      sidebar?.classList.remove("active");
      backdrop.style.display = "none";
    });
  }
});
