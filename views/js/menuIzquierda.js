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
    if (r.startsWith("#menu")) return null;

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
    window.location.href = `${baseURL}/login`;
  };

  /**
   * ✅ IMPORTANTE:
   * Cuando inyectas HTML con innerHTML, los <script> NO se ejecutan.
   * Esta función re-carga scripts externos e inline presentes en el HTML inyectado.
   * - Ejecuta en orden (secuencial) los scripts con src
   * - Ejecuta scripts inline recreándolos
   */
  const runInjectedScripts = async (rootEl) => {
    if (!rootEl) return;

    const scripts = Array.from(rootEl.querySelectorAll("script"));
    if (!scripts.length) return;

    for (const oldScript of scripts) {
      const newScript = document.createElement("script");

      // Copiar atributos
      for (const attr of oldScript.attributes) {
        newScript.setAttribute(attr.name, attr.value);
      }

      // Si es externo
      const src = oldScript.getAttribute("src");
      if (src) {
        // Evitar duplicar el mismo script muchas veces (opcional pero recomendado)
        // Si quieres permitir recarga siempre, comenta este bloque.
        const already = document.querySelector(`script[data-ev-loaded="1"][src="${src}"]`);
        if (already) {
          oldScript.remove();
          continue;
        }

        newScript.setAttribute("data-ev-loaded", "1");

        // Cargar secuencial para respetar dependencias
        await new Promise((resolve, reject) => {
          newScript.onload = resolve;
          newScript.onerror = () => reject(new Error(`No se pudo cargar script: ${src}`));
          document.body.appendChild(newScript);
        });

      } else {
        // Inline
        newScript.text = oldScript.textContent || "";
        document.body.appendChild(newScript);
      }

      oldScript.remove();
    }
  };

  // ========= 1) NAVEGACIÓN AJAX (delegación única) =========
  document.addEventListener("click", async (e) => {
    const link = e.target.closest(".submenu-link");
    if (!link) return;

    e.preventDefault();

    const dataVista = (link.dataset && link.dataset.vista) ? link.dataset.vista : "";
    const hrefAttr = link.getAttribute("href") || "";
    const destino = dataVista || hrefAttr;

    let url = buildUrl(destino);
    if (!url) return;

    url += (url.includes("?") ? "&" : "?") + "partial=1";

    showLoader();

    try {
      const response = await fetch(url, {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-Partial": "1",
          "Accept": "text/html,application/json"
        },
        credentials: "include"
      });

      // 401 => sesión/token inválido
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

      // 403 => autenticado pero sin permisos
      if (response.status === 403) {
        let msg = "No tienes permisos para acceder a esta opción.";
        const ct = (response.headers.get("content-type") || "").toLowerCase();

        if (ct.includes("application/json")) {
          const j = await response.json().catch(() => null);
          if (j && (j.mensaje || j.message)) msg = j.mensaje || j.message;
        } else {
          await response.text().catch(() => "");
        }

        Swal.fire({
          icon: "error",
          title: "Acceso denegado",
          text: msg,
          confirmButtonText: "Entendido",
          confirmButtonColor: "#0F592F"
        });

        if (contenedor) {
          contenedor.innerHTML = `
            <div class="alert alert-warning m-4 shadow-sm rounded-3">
              <h5 class="mb-2"><i class="bi bi-shield-lock-fill"></i> Acceso denegado</h5>
              <p class="mb-0">${msg}</p>
            </div>
          `;
        }
        return;
      }

      const contentType = (response.headers.get("content-type") || "").toLowerCase();

      // Si vino JSON, puede ser error
      if (contentType.includes("application/json")) {
        const json = await response.json().catch(() => null);

        if (json && json.error === "UNAUTHORIZED") {
          // Si backend manda motivo=solo_admin con UNAUTHORIZED
          if ((json.motivo || "").toLowerCase() === "solo_admin") {
            Swal.fire({
              icon: "error",
              title: "Acceso denegado",
              text: json.mensaje || "Solo el administrador puede acceder a esta opción.",
              confirmButtonText: "Entendido",
              confirmButtonColor: "#0F592F"
            });
            if (contenedor) contenedor.innerHTML = "";
            return;
          }

          Swal.fire({
            icon: "warning",
            title: "Sesión expirada",
            text: "Tu sesión ha expirado. Por favor, inicia sesión nuevamente.",
            confirmButtonText: "Ir al login",
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
      if (html.includes("formLogin") || html.includes("<html") || html.includes("<!doctype html")) {
        Swal.fire({
          icon: "warning",
          title: "Sesión finalizada",
          text: "Tu sesión ha caducado o la vista devolvió la plantilla completa.",
          confirmButtonText: "Aceptar",
          confirmButtonColor: "#0F592F"
        }).then(() => (window.location.href = `${baseURL}/`));
        return;
      }

      // Inyectar HTML
      if (contenedor) contenedor.innerHTML = html;

      // ✅ Ejecutar scripts dentro del HTML inyectado
      await runInjectedScripts(contenedor);

      // Activo del menú
      document.querySelectorAll(".submenu-link").forEach((el) => el.classList.remove("active"));
      link.classList.add("active");

      // Sidebar móvil
      if (isMobile()) closeSidebar();
      else {
        backdrop.classList.remove("show");
        lockBody(false);
      }

      // Evento global de "vista cargada"
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
  const toggleButtons = document.querySelectorAll(
    "[data-lte-toggle='sidebar'], .sidebar-toggle, #btnToggleSidebar"
  );

  toggleButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      toggleSidebar();
    });
  });

  backdrop.addEventListener("click", () => closeSidebar());

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeSidebar();
  });

  window.addEventListener("resize", () => {
    if (!isMobile()) {
      if (sidebar) sidebar.classList.remove("active");
      backdrop.classList.remove("show");
      lockBody(false);
    }
  });

  // ========= 3) SUBMENÚS (acordeón) =========
  document.querySelectorAll("#sidebar .nav-link[data-bs-toggle='collapse']").forEach((link) => {
    link.addEventListener("click", function () {
      const parent = this.closest("li");
      const submenu = parent ? parent.querySelector(".collapse") : null;
      if (!submenu) return;

      document.querySelectorAll("#sidebar .collapse.show").forEach((openMenu) => {
        if (openMenu !== submenu) openMenu.classList.remove("show");
      });

      submenu.classList.toggle("show");
    });
  });

});
