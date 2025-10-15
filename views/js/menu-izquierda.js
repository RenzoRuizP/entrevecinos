// views/js/menu-izquierda.js
document.addEventListener("DOMContentLoaded", () => {
  // ================================
  // 🔹 CONFIGURACIÓN BASE Y UTILIDAD
  // ================================
  const baseURL = (window.BASE_URL || "/entrevecinos").replace(/\/$/, "");
  const contenedor = document.getElementById("contenido-principal");
  const sidebar = document.getElementById("sidebar");
  const toggleButtons = document.querySelectorAll("[data-lte-toggle='sidebar'], .sidebar-toggle");

  // Crear backdrop si no existe
  let backdrop = document.getElementById("sidebar-backdrop");
  if (!backdrop) {
    backdrop = document.createElement("div");
    backdrop.id = "sidebar-backdrop";
    document.body.appendChild(backdrop);
  }

  // ================================
  // 🔹 FUNCIÓN PARA CONSTRUIR URLS
  // ================================
  const buildPathUrl = (ruta) => {
    if (!ruta) return null;
    ruta = ruta.toString().trim();
    if (/^https?:\/\//i.test(ruta)) return ruta;
    if (ruta.startsWith(baseURL)) return ruta;
    if (ruta.startsWith("/")) return `${baseURL}${ruta}`;
    return `${baseURL}/${ruta}`;
  };

  // ==========================================
  // 🔹 CARGA DINÁMICA DE VISTAS (Tu versión)
  // ==========================================
  const attachListeners = () => {
    document.querySelectorAll(".submenu-link").forEach(link => {
      if (link.dataset.listenerAttached) return;
      link.dataset.listenerAttached = "1";

      link.addEventListener("click", async (e) => {
        e.preventDefault();
        const href = link.getAttribute("href") || link.dataset.vista || "";
        if (!href || href === "#" || href.startsWith("#menu")) return;

        const url = buildPathUrl(href);
        if (!url) return;

        if (contenedor) {
          contenedor.innerHTML = `
            <div class="text-center p-5">
              <div class="spinner-border text-success" role="status"></div>
              <p class="mt-3">Cargando...</p>
            </div>
          `;
        }

        try {
          const response = await fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
          });
          if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

          const html = await response.text();
          if (contenedor) contenedor.innerHTML = html;

          document.querySelectorAll(".submenu-link").forEach(el => el.classList.remove("active"));
          link.classList.add("active");

          // Cerrar menú en móvil después de hacer clic
          sidebar.classList.remove("active");
          backdrop.classList.remove("show");
          document.body.style.overflow = "";

        } catch (err) {
          console.error("❌ Error al cargar vista:", err);
          if (contenedor) {
            contenedor.innerHTML = `
              <div class="alert alert-danger m-5 shadow-sm rounded-3">
                <h5 class="mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Error</h5>
                <p>No se pudo cargar el contenido solicitado.</p>
                <small class="text-muted">${err.message}</small>
              </div>
            `;
          }
        }
      });
    });
  };

  attachListeners();

  // Observa cambios en el menú (si se genera dinámicamente)
  const nav = document.getElementById("navigation");
  if (nav) {
    const observer = new MutationObserver(() => attachListeners());
    observer.observe(nav, { childList: true, subtree: true });
  }

  // ==========================================
  // 🔹 RESPONSIVIDAD DEL SIDEBAR
  // ==========================================
  toggleButtons.forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      sidebar.classList.toggle("active");
      backdrop.classList.toggle("show", sidebar.classList.contains("active"));
      document.body.style.overflow = sidebar.classList.contains("active") ? "hidden" : "";
    });
  });

  backdrop.addEventListener("click", () => {
    sidebar.classList.remove("active");
    backdrop.classList.remove("show");
    document.body.style.overflow = "";
  });

  // Cerrar sidebar si se cambia a escritorio
  window.addEventListener("resize", () => {
    if (window.innerWidth > 992) {
      sidebar.classList.remove("active");
      backdrop.classList.remove("show");
      document.body.style.overflow = "";
    }
  });

  // ==========================================
  // 🔹 SUBMENÚS TIPO ACORDEÓN
  // ==========================================
  document.querySelectorAll("#sidebar .nav-link[data-bs-toggle='collapse']").forEach(link => {
    link.addEventListener("click", function () {
      const parent = this.closest("li");
      const submenu = parent ? parent.querySelector(".collapse") : null;

      if (submenu) {
        document.querySelectorAll("#sidebar .collapse.show").forEach(openMenu => {
          if (openMenu !== submenu) openMenu.classList.remove("show");
        });
        submenu.classList.toggle("show");
      }
    });
  });
});
