// views/js/menu-izquierda.js (reemplazar completamente)
document.addEventListener("DOMContentLoaded", () => {
  const baseURL = (window.BASE_URL || "/entrevecinos").replace(/\/$/, ''); // sin slash final
  const contenedor = document.getElementById("contenido-principal");

  if (!contenedor) {
    console.warn("No se encontró #contenido-principal");
    return;
  }

  // Función que genera URL estilo path: /entrevecinos/mi-perfil
  const buildPathUrl = (ruta) => {
    if (!ruta) return null;
    ruta = ruta.toString().trim();
    // si ya viene con base completa, retornar tal cual
    if (/^https?:\/\//i.test(ruta)) return ruta;
    // si ya incluye baseURL
    if (ruta.startsWith(baseURL)) return ruta;
    // si empieza con slash -> baseURL + ruta
    if (ruta.startsWith('/')) return `${baseURL}${ruta}`;
    // ruta sin slash -> baseURL + '/' + ruta
    return `${baseURL}/${ruta}`;
  };

  // Delegación: attach listener a cada submenu-link actual y futuro
  const attachListeners = () => {
    document.querySelectorAll(".submenu-link").forEach(link => {
      // evitar duplicar listeners
      if (link.dataset.listenerAttached) return;
      link.dataset.listenerAttached = "1";

      link.addEventListener("click", async (e) => {
        e.preventDefault();
        const href = link.getAttribute("href") || link.dataset.vista || "";
        if (!href || href === '#' || href.startsWith('#menu')) return;

        const url = buildPathUrl(href);
        if (!url) return;

        console.log("📄 Cargando vista (path):", url);

        // Spinner
        contenedor.innerHTML = `
          <div class="text-center p-5">
            <div class="spinner-border text-success" role="status"></div>
            <p class="mt-3">Cargando...</p>
          </div>
        `;

        try {
          const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            // no need credentials: 'include' para cookies same-origin; si las cookies no se envían, agregar:
            // credentials: 'same-origin'
          });

          if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

          const html = await response.text();
          contenedor.innerHTML = html;

          // marcar activo
          document.querySelectorAll(".submenu-link").forEach(el => el.classList.remove("active"));
          link.classList.add("active");

        } catch (err) {
          console.error("❌ Error al cargar vista:", err);
          contenedor.innerHTML = `
            <div class="alert alert-danger m-5 shadow-sm rounded-3">
              <h5 class="mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Error</h5>
              <p>No se pudo cargar el contenido solicitado.</p>
              <small class="text-muted">${err.message}</small>
            </div>
          `;
        }
      });
    });
  };

  // Si your menu is generated dynamically (via AJAX), run attachListeners after it's built.
  // If menu items are created later, call attachListeners() again.
  attachListeners();

  // Si el menú se rellena por AJAX (tu caso original), escucha cambios en #navigation
  const nav = document.getElementById("navigation");
  if (nav) {
    const observer = new MutationObserver(() => attachListeners());
    observer.observe(nav, { childList: true, subtree: true });
  }
});
