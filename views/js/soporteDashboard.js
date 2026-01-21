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
    if (v.includes("alta")) return `<span class="ev-badge ev-badge-alta">Alta</span>`;
    if (v.includes("media")) return `<span class="ev-badge ev-badge-media">Media</span>`;
    if (v.includes("baja")) return `<span class="ev-badge ev-badge-baja">Baja</span>`;
    return "";
  }

  // ✅ NUEVO: arma sublínea compacta (sin verse “pesada”)
  function buildSubline(it) {
    const email = (it.email || it.usuario_email || "").trim();
    const tipoConjunto = (it.tipo_conjunto || "").trim();
    const dir = (it.direccion || "").trim();

    const parts = [];
    if (email) parts.push(email);
    if (tipoConjunto) parts.push(tipoConjunto);
    if (dir) parts.push(dir);

    return parts.join(" · ");
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
        // Normalización tolerante
        const fecha = esc(it.fecha || it.fecha_creacion || it.created_at || "—");
        const tipoRaw = (it.tipo || it.tipo_atencion || it.modulo || "Cuenta en revisión");
        const tipo = esc(tipoRaw);

        const prioridadHtml = badgePrioridad(it.prioridad || it.nivel || it.badge);

        const nombre = (it.nombre || it.usuario_nombre || "").trim();
        const email = (it.email || it.usuario_email || "").trim();

        const nombreMostrar = esc(nombre || email || "");

        const subline = esc(buildSubline(it));

        const href = baseUrl + routeByTipo(tipoRaw);

        // ✅ Render con 2 filas centradas:
        // Fila 1: Tipo + Prioridad + Nombre (centrado como grupo)
        // Fila 2: Subline (centrada y compacta)
        return `
          <tr>
            <td class="ev-col-fecha">
              <div class="fw-semibold">${fecha}</div>
            </td>

            <td class="ev-col-tipo">
              <div class="ev-att-cell">
                <div class="ev-att-top">
                  <span class="ev-att-tipo">${tipo}</span>
                  ${prioridadHtml ? `<span class="ev-att-badge">${prioridadHtml}</span>` : ``}
                  ${nombreMostrar ? `<span class="ev-att-nombre">${nombreMostrar}</span>` : ``}
                </div>

                ${subline ? `<div class="ev-att-sub" title="${subline}">${subline}</div>` : ``}
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
    if (!tbody) return;

    setLoadingTabla();

    const tiempo = getTiempoSeleccionado();
    const limit = 10;

    // ✅ Evitar cache de fetch
    const url = new URL(`${baseUrl}/api/soporte/dashboard`, window.location.origin);
    url.searchParams.set("tiempo", tiempo);
    url.searchParams.set("limit", String(limit));
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
    if (!baseUrl) return false;

    const tbody = $("evAtenderAhoraBody");
    if (!tbody) return false;

    bindEventos();
    cargarDashboard();

    booted = true;
    return true;
  }

  window.EV_SoporteDashboard = window.EV_SoporteDashboard || {};
  window.EV_SoporteDashboard.init = init;
  window.EV_SoporteDashboard.refresh = cargarDashboard;

  function startObserver() {
    if (observer) return;

    observer = new MutationObserver(() => {
      const ok = init();
      if (ok && observer) {
        observer.disconnect();
        observer = null;
      }
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
  }

  const okNow = init();
  if (!okNow) startObserver();

  window.addEventListener("pageshow", function () {
    if ($("evAtenderAhoraBody")) cargarDashboard();
  });
})();
