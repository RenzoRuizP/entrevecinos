// views/js/soporteDashboard.js
// Dashboard Soporte - Inicialización robusta para entorno con carga parcial (AJAX)
// Objetivo: que SIEMPRE pinte KPIs + "Atender ahora" sin depender de limpiar cache.

(function () {
  "use strict";

  const baseUrl = (window.BASE_URL || "").replace(/\/+$/, "");
  if (!baseUrl) return;

  // Evita re-inicializaciones agresivas, pero permite refrescar data.
  let booted = false;
  let observer = null;

  // Helpers
  const esc = (s) =>
    String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  function $(id) {
    return document.getElementById(id);
  }

  function getTiempoSeleccionado() {
    const sel = $("evFiltroTiempo");
    return sel ? (sel.value || "hoy") : "hoy";
  }

  function setLoadingTabla() {
    const tbody = $("evAtenderAhoraBody");
    if (!tbody) return;
    tbody.innerHTML = `
      <tr>
        <td colspan="3" class="text-center py-4 ev-empty">
          Cargando solicitudes...
        </td>
      </tr>`;
  }

  function setEmptyTabla() {
    const tbody = $("evAtenderAhoraBody");
    if (!tbody) return;
    tbody.innerHTML = `
      <tr>
        <td colspan="3" class="text-center py-4 ev-empty">
          No hay solicitudes pendientes en este rango.
        </td>
      </tr>`;
  }

  function setErrorTabla() {
    const tbody = $("evAtenderAhoraBody");
    if (!tbody) return;
    tbody.innerHTML = `
      <tr>
        <td colspan="3" class="text-center py-4 ev-empty">
          Error al cargar solicitudes.
        </td>
      </tr>`;
  }

  function routeByTipo(tipo) {
    const t = String(tipo || "").toLowerCase();
    if (t.includes("recarg")) return "/atender-recargas";
    if (t.includes("public")) return "/atender-publicacion";
    if (t.includes("resid")) return "/notificaciones-residencia";
    // default: cuentas
    return "/atender-cuentas";
  }

  function badgePrioridad(p) {
    const v = String(p || "").toLowerCase();
    if (v.includes("alta")) return `<span class="ev-badge ev-badge-alta">alta</span>`;
    if (v.includes("media")) return `<span class="ev-badge ev-badge-media">media</span>`;
    if (v.includes("baja")) return `<span class="ev-badge ev-badge-baja">baja</span>`;
    return "";
  }

  function renderAtender(items) {
    const tbody = $("evAtenderAhoraBody");
    if (!tbody) return;

    if (!Array.isArray(items) || items.length === 0) {
      setEmptyTabla();
      return;
    }

    tbody.innerHTML = items
      .map((it) => {
        // Normalización tolerante (para evitar “devuelve datos pero no pinta”)
        const fecha = esc(it.fecha || it.fecha_creacion || it.created_at || "");
        const tipo = esc(it.tipo || it.tipo_atencion || it.modulo || "Cuenta en revisión");
        const prioridad = badgePrioridad(it.prioridad || it.nivel || it.badge);

        // Texto descriptivo (cuentas/recargas/publicaciones/residencias)
        const nombre = esc(it.nombre || it.usuario_nombre || "");
        const email = esc(it.email || it.usuario_email || "");
        const doc = esc(it.documento || it.usuario_documento || "");
        const tel = esc(it.telefono || it.usuario_telefono || "");

        const tipoConjunto = esc(it.tipo_conjunto || "");
        const dir = esc(it.direccion || "");
        const residenciaLinea =
          tipoConjunto || dir
            ? `<div class="text-muted small">${tipoConjunto ? esc(tipoConjunto) : ""}${tipoConjunto && dir ? " · " : ""}${dir}</div>`
            : "";

        const href = baseUrl + routeByTipo(tipo);

        // Columna "Tipo de atención": prioridad + resumen
        const resumen =
          nombre || email
            ? `<strong>${nombre || email}</strong>
               <div class="text-muted small">${[email, doc, tel].filter(Boolean).join(" · ")}</div>
               ${residenciaLinea}`
            : `<strong>${tipo}</strong>`;

        return `
          <tr>
            <td class="ev-col-fecha">
              <div class="fw-semibold">${fecha || "—"}</div>
            </td>

            <td class="ev-col-tipo">
              <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                ${prioridad || ""}
                <div class="text-start" style="max-width: 820px;">
                  <div class="fw-semibold">${esc(tipo)}</div>
                  <div>${resumen}</div>
                </div>
              </div>
            </td>

            <td class="ev-col-accion">
              <a class="ev-btn-atender" href="${href}">Atender</a>
            </td>
          </tr>
        `;
      })
      .join("");
  }

  function renderKPIs(kpis) {
    // IDs existentes en tu view:
    // kpiCuentasPend, kpiCuentasAprob, kpiCuentasRech
    // kpiPubRevision, kpiPubReport, kpiPubSusp
    // kpiRecPend, kpiRecVal, kpiRecObs
    const setText = (id, val) => {
      const el = $(id);
      if (el) el.textContent = String(val ?? 0);
    };

    if (!kpis || typeof kpis !== "object") return;

    const cuentas = kpis.cuentas || {};
    const publicaciones = kpis.publicaciones || {};
    const recargas = kpis.recargas || {};

    setText("kpiCuentasPend", cuentas.pendientes);
    setText("kpiCuentasAprob", cuentas.aprobadas_hoy);
    setText("kpiCuentasRech", cuentas.rechazadas);

    setText("kpiPubRevision", publicaciones.en_revision);
    setText("kpiPubReport", publicaciones.reportadas);
    setText("kpiPubSusp", publicaciones.suspendidas);

    setText("kpiRecPend", recargas.pendientes);
    setText("kpiRecVal", recargas.validadas_hoy);
    setText("kpiRecObs", recargas.observadas);
  }

  async function cargarDashboard() {
    const tbody = $("evAtenderAhoraBody");
    // Si el parcial aún no está en DOM, no hagas nada (lo resolverá el observer)
    if (!tbody) return;

    setLoadingTabla();

    const tiempo = getTiempoSeleccionado();
    const limit = 10;

    // ✅ Evitar cache de fetch (clave para F5 intermitente en algunos entornos)
    const url = new URL(`${baseUrl}/api/soporte/dashboard`, window.location.origin);
    url.searchParams.set("tiempo", tiempo);
    url.searchParams.set("limit", String(limit));
    // cache-bust adicional por si el navegador se pone creativo:
    url.searchParams.set("_", String(Date.now()));

    try {
      const resp = await fetch(url.toString(), {
        method: "GET",
        headers: { "X-Partial": "1" },
        credentials: "include",
        cache: "no-store"
      });

      const json = await resp.json().catch(() => null);
      if (!resp.ok || !json || json.ok !== true) {
        setErrorTabla();
        return;
      }

      // Estructura esperada:
      // { ok:true, data:{ kpis:{...}, atender:[...] } }
      renderKPIs(json.data?.kpis || {});
      renderAtender(json.data?.atender || []);
    } catch (e) {
      console.error("[EV][SoporteDashboard] cargarDashboard error:", e);
      setErrorTabla();
    }
  }

  function bindEventos() {
    const sel = $("evFiltroTiempo");
    if (sel && !sel.dataset.evBound) {
      sel.dataset.evBound = "1";
      sel.addEventListener("change", () => {
        cargarDashboard();
      });
    }
  }

  function init() {
    // Se puede llamar múltiples veces; solo “boot” una vez.
    if (!baseUrl) return false;

    const tbody = $("evAtenderAhoraBody");
    if (!tbody) return false;

    bindEventos();
    cargarDashboard();

    booted = true;
    return true;
  }

  // ✅ Exponer init para que puedas llamarlo desde otros módulos si quieres
  window.EV_SoporteDashboard = window.EV_SoporteDashboard || {};
  window.EV_SoporteDashboard.init = init;
  window.EV_SoporteDashboard.refresh = cargarDashboard;

  // ✅ AUTO-SOLUCIÓN: si el dashboard se inserta por AJAX, esperamos a que apare redundantemente.
  function startObserver() {
    if (observer) return;

    observer = new MutationObserver(() => {
      // Cuando el parcial aparece, inicializa (y listo)
      const ok = init();
      if (ok && observer) {
        observer.disconnect();
        observer = null;
      }
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
  }

  // 1) Intento inmediato (si ya está el DOM)
  const okNow = init();

  // 2) Si aún no está, esperamos por inserción AJAX
  if (!okNow) startObserver();

  // 3) En algunos navegadores, al volver atrás/adelante (bfcache), re-cargar:
  window.addEventListener("pageshow", function () {
    // Si ya existe la tabla en DOM, refresca sin depender de cache
    if ($("evAtenderAhoraBody")) cargarDashboard();
  });
})();
