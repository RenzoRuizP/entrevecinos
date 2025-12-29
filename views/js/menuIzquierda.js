// views/js/menuIzquierda.js — navegación AJAX ÚNICA y estable (EV) + Sidebar móvil robusta
document.addEventListener("DOMContentLoaded", () => {
  "use strict";

  const LOG = "[EV][NAV]";
  const baseURL = (window.BASE_URL || "/entrevecinos").toString().replace(/\/+$/, "");
  const contenedor = document.getElementById("contenido-principal");
  const sidebar = document.getElementById("sidebar");

  // Backdrop (garantizar que exista)
  let backdrop = document.getElementById("sidebar-backdrop");
  if (!backdrop) {
    backdrop = document.createElement("div");
    backdrop.id = "sidebar-backdrop";
    document.body.appendChild(backdrop);
  }

  // Helpers responsive
  const isMobile = () => window.matchMedia("(max-width: 991.98px)").matches;

  const lockBody = (lock) => {
    // Solo bloquear en móvil (desktop no debe bloquear)
    if (!isMobile()) {
      document.body.style.overflow = "";
      return;
    }
    document.body.style.overflow = lock ? "hidden" : "";
  };

  const openSidebar = () => {
    if (!sidebar) return;
    sidebar.classList.add("active");
    backdrop.classList.add("show");
    lockBody(true);
  };

  const closeSidebar = () => {
    if (!sidebar) return;
    sidebar.classList.remove("active");
    backdrop.classList.remove("show");
    lockBody(false);
  };

  const toggleSidebar = () => {
    if (!sidebar) return;
    const open = sidebar.classList.contains("active");
    if (open) closeSidebar();
    else openSidebar();
  };

  // Construcción URL robusta
  const buildUrl = (ruta) => {
    if (!ruta) return null;
    const r = ruta.toString().trim();
    if (!r || r === "#") return null;
    if (r.startsWith("#menu")) return null; // acordeón

    // absoluto externo
    if (/^https?:\/\//i.test(r)) return r;

    // ya trae baseURL completo
    if (r.startsWith(baseURL)) return r;

    // relativo app
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

  // ========= 1) NAVEGACIÓN AJAX (delegación única) =========
  // Nota: mantenemos tu selector ".submenu-link" para NO romper lo que ya funciona
  document.addEventListener("click", async (e) => {
    const link = e.target.closest(".submenu-link");
    if (!link) return;

    // Si el link pertenece a un dropdown de bootstrap u otro comportamiento, respetamos
    // (tu selector está pensado para menú lateral; igual lo dejamos seguro)
    e.preventDefault();

    // Prioridad: data-vista (tu arquitectura), luego href
    const dataVista = link.dataset && link.dataset.vista ? link.dataset.vista : "";
    const hrefAttr = link.getAttribute("href") || "";
    const destino = dataVista || hrefAttr;

    let url = buildUrl(destino);
    if (!url) return;

    // Forzar modo parcial
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

      // Si vino JSON, validar payload UNAUTHORIZED
      if (contentType.includes("application/json")) {
        const json = await response.json().catch(() => null);

        if (json && (json.error === "UNAUTHORIZED" || (json.ok === false && json.error === "UNAUTHORIZED"))) {
          Swal.fire({
            icon: "warning",
            title: "Acceso no autorizado",
            text: "No se pudo cargar la vista solicitada.",
            confirmButtonText: "Aceptar",
            confirmButtonColor: "#0F592F"
          }).then(gotoLogin);
          return;
        }

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

      // Activo visual (solo en submenús)
      document.querySelectorAll(".submenu-link").forEach((el) => el.classList.remove("active"));
      link.classList.add("active");

      // Cerrar sidebar móvil (solo si estamos en móvil)
      if (isMobile()) closeSidebar();
      else {
        // en desktop, por seguridad, solo limpiar backdrop/scroll
        backdrop.classList.remove("show");
        lockBody(false);
      }

      // Hook: permitir que otros scripts re-inicialicen
      document.dispatchEvent(new CustomEvent("ev:vistaCargada", { detail: { url } }));

    } catch (err) {
      console.error(LOG, "Error:", err);
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

  // ========= 2) TOGGLE SIDEBAR (móvil) =========
  // Unificamos el toggle para evitar dobles listeners.
  const toggleButtons = document.querySelectorAll(
    "[data-lte-toggle='sidebar'], .sidebar-toggle, #btnToggleSidebar"
  );

  toggleButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      toggleSidebar();
    });
  });

  // Click fuera (backdrop) cierra
  backdrop.addEventListener("click", () => closeSidebar());

  // ESC cierra
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeSidebar();
  });

  // Resize: cuando pasas a desktop, limpiar estado móvil
  window.addEventListener("resize", () => {
    if (!isMobile()) {
      // En desktop no debe quedar activo/backdrop
      if (sidebar) sidebar.classList.remove("active");
      backdrop.classList.remove("show");
      lockBody(false);
    }
  });

  // ========= 3) SUBMENÚS (acordeón) =========
  // IMPORTANTE: No forzamos toggle manual de ".collapse" con show/hide si ya está Bootstrap,
  // pero sí cerramos otros submenús abiertos para el efecto acordeón.
  document.querySelectorAll("#sidebar .nav-link[data-bs-toggle='collapse']").forEach((link) => {
    link.addEventListener("click", function () {
      const parent = this.closest("li");
      const submenu = parent ? parent.querySelector(".collapse") : null;
      if (!submenu) return;

      // Cerrar otros submenús abiertos
      document.querySelectorAll("#sidebar .collapse.show").forEach((openMenu) => {
        if (openMenu !== submenu) {
          openMenu.classList.remove("show");
        }
      });

      // Toggle del submenu actual:
      // Si Bootstrap lo maneja, esto no lo rompe, porque la clase se sincroniza.
      submenu.classList.toggle("show");
    });
  });

});
