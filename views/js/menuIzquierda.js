// views/js/menuIzquierda.js — navegación AJAX ÚNICA y estable (EV)
document.addEventListener("DOMContentLoaded", () => {
  const baseURL = (window.BASE_URL || "/entrevecinos").toString().replace(/\/+$/, "");
  const contenedor = document.getElementById("contenido-principal");
  const sidebar = document.getElementById("sidebar");

  // Backdrop
  let backdrop = document.getElementById("sidebar-backdrop");
  if (!backdrop) {
    backdrop = document.createElement("div");
    backdrop.id = "sidebar-backdrop";
    document.body.appendChild(backdrop);
  }

  const buildUrl = (ruta) => {
    if (!ruta) return null;
    const r = ruta.toString().trim();
    if (!r || r === "#" || r.startsWith("#menu")) return null;

    if (/^https?:\/\//i.test(r)) return r;
    if (r.startsWith(baseURL)) return r;
    if (r.startsWith("/")) return `${baseURL}${r}`;
    return `${baseURL}/${r}`;
  };

  const showLoader = () => {
    if (!contenedor) return;
    contenedor.innerHTML = `
      <div class="text-center p-5">
        <div class="spinner-border text-success" role="status"></div>
        <p class="mt-3">Cargando...</p>
      </div>
    `;
  };

  const gotoLogin = () => {
    window.location.href = `${baseURL}/views/login.php?error=token_expirado`;
  };

  // Delegación: un solo listener para todos los submenu links
  document.addEventListener("click", async (e) => {
    const link = e.target.closest(".submenu-link");
    if (!link) return;

    e.preventDefault();

    const href = link.getAttribute("href") || link.dataset.vista || "";
    let url = buildUrl(href);
    if (!url) return;

    // Forzar modo parcial para que el backend NO devuelva layout completo
    url += (url.includes("?") ? "&" : "?") + "partial=1";

    showLoader();

    try {
      const response = await fetch(url, {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-Partial": "1"
        },
        credentials: "include"
      });

      // 401 real => sesión/token inválido
      if (response.status === 401) {
        Swal.fire({
          icon: "warning",
          title: "Sesión expirada",
          text: "Tu sesión ha expirado. Por favor, inicia sesión nuevamente.",
          confirmButtonText: "Ir al login",
          confirmButtonColor: "#0F592F"
        }).then(gotoLogin);
        return;
      }

      const contentType = (response.headers.get("content-type") || "").toLowerCase();

      // Si vino JSON, validar si backend envió UNAUTHORIZED como payload
      if (contentType.includes("application/json")) {
        const json = await response.json().catch(() => null);

        if (json && (json.error === "UNAUTHORIZED" || json.ok === false && json.error === "UNAUTHORIZED")) {
          Swal.fire({
            icon: "warning",
            title: "Acceso no autorizado",
            text: "No se pudo cargar la vista solicitada.",
            confirmButtonText: "Aceptar",
            confirmButtonColor: "#0F592F"
          }).then(gotoLogin);
          return;
        }

        // JSON no esperado: mostrar error genérico
        throw new Error("La vista devolvió JSON en lugar de HTML.");
      }

      if (!response.ok) {
        throw new Error(`Error HTTP ${response.status}`);
      }

      const html = await response.text();

      // Evitar inyectar documento completo o login
      if (
        html.includes("formLogin") ||
        html.includes("<title>Entre vecinos") ||
        html.includes("<html") ||
        html.includes("<!doctype html")
      ) {
        Swal.fire({
          icon: "warning",
          title: "Sesión finalizada",
          text: "Tu sesión ha caducado o la vista devolvió la plantilla completa.",
          confirmButtonText: "Aceptar",
          confirmButtonColor: "#0F592F"
        }).then(() => (window.location.href = `${baseURL}/`));
        return;
      }

      if (contenedor) contenedor.innerHTML = html;

      // Activo visual
      document.querySelectorAll(".submenu-link").forEach(el => el.classList.remove("active"));
      link.classList.add("active");

      // Cerrar sidebar móvil
      if (sidebar) sidebar.classList.remove("active");
      backdrop.classList.remove("show");
      document.body.style.overflow = "";

    } catch (err) {
      console.error("[EV][NAV] Error:", err);
      if (contenedor) {
        contenedor.innerHTML = `
          <div class="alert alert-danger m-4 shadow-sm rounded-3">
            <h5 class="mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Error</h5>
            <p>No se pudo cargar el contenido solicitado.</p>
            <small class="text-muted">${(err && err.message) ? err.message : "Error desconocido"}</small>
          </div>
        `;
      }
    }
  });

  // Toggle sidebar (móvil)
  const toggleButtons = document.querySelectorAll("[data-lte-toggle='sidebar'], .sidebar-toggle, #btnToggleSidebar");
  toggleButtons.forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      if (!sidebar) return;
      sidebar.classList.toggle("active");
      backdrop.classList.toggle("show", sidebar.classList.contains("active"));
      document.body.style.overflow = sidebar.classList.contains("active") ? "hidden" : "";
    });
  });

  backdrop.addEventListener("click", () => {
    if (sidebar) sidebar.classList.remove("active");
    backdrop.classList.remove("show");
    document.body.style.overflow = "";
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 992) {
      if (sidebar) sidebar.classList.remove("active");
      backdrop.classList.remove("show");
      document.body.style.overflow = "";
    }
  });

  // Submenús acordeón
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
