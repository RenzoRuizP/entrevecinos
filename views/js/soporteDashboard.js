// views/js/soporteDashboard.js
// Dashboard Soporte - Auto actualización robusta EV
// Actualiza KPIs + "Atender ahora" sin recargar la página.
// Compatible con Shell MenuPrincipalView.php y carga parcial AJAX.

(function () {
  "use strict";

  if (window.__EV_SOPORTE_DASHBOARD_SCRIPT_LOADED__ === true) return;
  window.__EV_SOPORTE_DASHBOARD_SCRIPT_LOADED__ = true;

  const baseUrl = (window.BASE_URL || window.EV_BASE_URL || "").replace(/\/+$/, "");

  const POLLING_MS = 15000;
  const FETCH_TIMEOUT_MS = 8000;
  const AFTER_AJAX_DELAY_MS = 350;

  let pollingTimer = null;
  let fetchEnCurso = false;
  let eventosGlobalesAsignados = false;
  let ultimoResumenFirma = "";
  let ultimaAtencionFirma = "";
  let bootedOnce = false;

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

  function dashboardExiste() {
    return !!$("evAtenderAhoraBody");
  }

  function getTiempoSeleccionado() {
    const sel = $("evFiltroTiempo");
    return sel ? String(sel.value || "hoy") : "hoy";
  }

  function crearFirma(obj) {
    try {
      return JSON.stringify(obj || {});
    } catch (_) {
      return String(Date.now());
    }
  }

  function setText(id, val, opts = {}) {
    const el = $(id);
    if (!el) return;

    const nuevo = String(val ?? 0);
    const anterior = String(el.textContent ?? "").trim();

    if (anterior === nuevo) return;

    el.textContent = nuevo;

    if (opts.animar === true) {
      el.classList.remove("ev-kpi-pulse");
      void el.offsetWidth;
      el.classList.add("ev-kpi-pulse");

      window.setTimeout(() => {
        el.classList.remove("ev-kpi-pulse");
      }, 650);
    }
  }

  function asegurarEstilosRuntime() {
    if (document.getElementById("ev-soporte-dashboard-runtime-style")) return;

    const style = document.createElement("style");
    style.id = "ev-soporte-dashboard-runtime-style";
    style.textContent = `
      .ev-kpi-pulse{
        animation: evKpiPulse .62s cubic-bezier(.22,.9,.3,1);
      }

      @keyframes evKpiPulse{
        0%{
          transform: scale(1);
          filter: brightness(1);
        }
        35%{
          transform: scale(1.22);
          filter: brightness(1.08);
        }
        100%{
          transform: scale(1);
          filter: brightness(1);
        }
      }

      .ev-dashboard-live-dot{
        width:8px;
        height:8px;
        display:inline-block;
        border-radius:999px;
        background:#22C55E;
        box-shadow:0 0 0 0 rgba(34,197,94,.45);
        animation: evLivePulse 1.8s infinite;
      }

      @keyframes evLivePulse{
        0%{
          box-shadow:0 0 0 0 rgba(34,197,94,.45);
        }
        70%{
          box-shadow:0 0 0 8px rgba(34,197,94,0);
        }
        100%{
          box-shadow:0 0 0 0 rgba(34,197,94,0);
        }
      }

      .ev-dashboard-status{
        display:inline-flex;
        align-items:center;
        gap:8px;
        font-size:.78rem;
        color:#6B7280;
        white-space:nowrap;
      }

      .ev-dashboard-status strong{
        color:#0F592F;
        font-weight:800;
      }

      .ev-dashboard-row-new{
        animation: evRowNew .9s ease;
      }

      @keyframes evRowNew{
        0%{
          background:rgba(255,247,237,.95);
        }
        100%{
          background:transparent;
        }
      }
    `;

    document.head.appendChild(style);
  }

  function asegurarIndicadorLive() {
    const header = document.querySelector(".ev-atender-header");
    if (!header) return;

    if (document.getElementById("evDashboardLiveStatus")) return;

    const right = header.querySelector(".d-flex.align-items-center.gap-2");
    if (!right) return;

    const status = document.createElement("span");
    status.id = "evDashboardLiveStatus";
    status.className = "ev-dashboard-status";
    status.innerHTML = `
      <span class="ev-dashboard-live-dot" aria-hidden="true"></span>
      <span>Actualización <strong>automática</strong></span>
    `;

    right.prepend(status);
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

  function setErrorTabla(modoSilencioso) {
    const tbody = $("evAtenderAhoraBody");
    if (!tbody) return;

    if (modoSilencioso === true) {
      return;
    }

    tbody.innerHTML = `
      <tr>
        <td colspan="3" class="text-center py-4 ev-empty">
          Error al cargar solicitudes.
        </td>
      </tr>`;
  }

  function routeByTipo(tipo, item = {}) {
    const t = String(tipo || "").toLowerCase();

    if (item.url && String(item.url).trim() !== "") {
      return String(item.url).trim();
    }

    if (t.includes("recarg")) return "/atender-recargas";
    if (t.includes("public")) return "/atender-publicacion";
    if (t.includes("resid")) return "/notificaciones-residencia";
    if (t.includes("servicio") || t.includes("incidencia")) return "/atender-servicios";

    return "/atender-cuentas";
  }

  function normalizarHref(ruta) {
    const r = String(ruta || "").trim();

    if (!r) return `${baseUrl}/atender-cuentas`;

    if (/^https?:\/\//i.test(r)) return r;

    if (r.startsWith(baseUrl + "/")) return r;

    if (r.startsWith("/")) return baseUrl + r;

    return `${baseUrl}/${r.replace(/^\/+/, "")}`;
  }

  function badgePrioridad(p) {
    const v = String(p || "").toLowerCase();

    if (v.includes("alta")) {
      return `<span class="ev-badge ev-badge-alta">Alta</span>`;
    }

    if (v.includes("media")) {
      return `<span class="ev-badge ev-badge-media">Media</span>`;
    }

    if (v.includes("baja")) {
      return `<span class="ev-badge ev-badge-baja">Baja</span>`;
    }

    return "";
  }

  function buildSubline(it) {
    const email = String(it.email || it.usuario_email || "").trim();
    const tipoConjunto = String(it.tipo_conjunto || "").trim();
    const direccion = String(it.direccion || "").trim();
    const detalle = String(it.detalle || it.descripcion || "").trim();
    const monto = String(it.monto || it.monto_recarga || "").trim();

    const parts = [];

    if (email) parts.push(email);
    if (tipoConjunto) parts.push(tipoConjunto);
    if (direccion) parts.push(direccion);
    if (monto) parts.push(`S/ ${monto}`);
    if (detalle) parts.push(detalle);

    return parts.join(" · ");
  }

  function renderKPIs(kpis, animarCambios) {
    if (!kpis || typeof kpis !== "object") return;

    const cuentas = kpis.cuentas || {};
    const publicaciones = kpis.publicaciones || {};
    const recargas = kpis.recargas || {};
    const servicios = kpis.servicios || {};

    setText("kpiCuentasPend", cuentas.pendientes, { animar: animarCambios });
    setText("kpiCuentasAprob", cuentas.aprobadas_hoy, { animar: animarCambios });
    setText("kpiCuentasRech", cuentas.rechazadas, { animar: animarCambios });

    setText("kpiPubRevision", publicaciones.en_revision, { animar: animarCambios });
    setText("kpiPubReport", publicaciones.reportadas, { animar: animarCambios });
    setText("kpiPubSusp", publicaciones.suspendidas, { animar: animarCambios });

    setText("kpiRecPend", recargas.pendientes, { animar: animarCambios });
    setText("kpiRecVal", recargas.validadas_hoy, { animar: animarCambios });
    setText("kpiRecObs", recargas.observadas, { animar: animarCambios });

    setText("kpiServiciosAbiertas", servicios.abiertas, { animar: animarCambios });
    setText("kpiServiciosEsperando", servicios.esperando_informacion, { animar: animarCambios });
    setText("kpiServiciosResueltas", servicios.resueltas_hoy, { animar: animarCambios });
  }

  function renderAtender(items, animarCambios) {
    const tbody = $("evAtenderAhoraBody");
    if (!tbody) return;

    if (!Array.isArray(items) || items.length === 0) {
      setEmptyTabla();
      ultimaAtencionFirma = "[]";
      return;
    }

    const nuevaFirma = crearFirma(items);
    if (nuevaFirma === ultimaAtencionFirma) return;

    ultimaAtencionFirma = nuevaFirma;

    tbody.innerHTML = items
      .map((it, idx) => {
        const fecha = esc(it.fecha || it.fecha_creacion || it.created_at || "—");
        const tipoRaw = it.tipo || it.tipo_atencion || it.modulo || "Cuenta en revisión";
        const tipo = esc(tipoRaw);

        const prioridadHtml = badgePrioridad(it.prioridad || it.nivel || it.badge);

        const nombre = String(it.nombre || it.usuario_nombre || it.titulo || "").trim();
        const email = String(it.email || it.usuario_email || "").trim();
        const nombreMostrar = esc(nombre || email || "");

        const subline = esc(buildSubline(it));
        const href = esc(normalizarHref(routeByTipo(tipoRaw, it)));

        const rowClass = animarCambios && idx === 0 ? "ev-dashboard-row-new" : "";

        return `
          <tr class="${rowClass}">
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
              <a data-ev-nav="1" class="ev-btn-atender" href="${href}">Atender</a>
            </td>
          </tr>
        `;
      })
      .join("");
  }

  function actualizarIndicadorHora() {
    const status = document.getElementById("evDashboardLiveStatus");
    if (!status) return;

    const ahora = new Date();
    const hh = String(ahora.getHours()).padStart(2, "0");
    const mm = String(ahora.getMinutes()).padStart(2, "0");
    const ss = String(ahora.getSeconds()).padStart(2, "0");

    status.innerHTML = `
      <span class="ev-dashboard-live-dot" aria-hidden="true"></span>
      <span>Actualizado <strong>${hh}:${mm}:${ss}</strong></span>
    `;
  }

  async function fetchJsonConTimeout(url, options = {}, timeoutMs = FETCH_TIMEOUT_MS) {
    const controller = new AbortController();
    const timeoutId = window.setTimeout(() => controller.abort(), timeoutMs);

    try {
      const resp = await fetch(url, {
        ...options,
        signal: controller.signal
      });

      const json = await resp.json().catch(() => null);

      return { resp, json };
    } finally {
      window.clearTimeout(timeoutId);
    }
  }

  async function cargarDashboard(opts = {}) {
    const silencioso = opts.silencioso === true;
    const forzar = opts.forzar === true;

    if (!baseUrl || !dashboardExiste()) {
      detenerPolling();
      return;
    }

    if (fetchEnCurso && !forzar) return;

    fetchEnCurso = true;

    if (!silencioso) {
      setLoadingTabla();
    }

    const tiempo = getTiempoSeleccionado();
    const limit = 10;

    const url = new URL(`${baseUrl}/api/soporte/dashboard`, window.location.origin);
    url.searchParams.set("tiempo", tiempo);
    url.searchParams.set("limit", String(limit));
    url.searchParams.set("_", String(Date.now()));

    try {
      const { resp, json } = await fetchJsonConTimeout(url.toString(), {
        method: "GET",
        credentials: "include",
        cache: "no-store",
        headers: {
          "Accept": "application/json",
          "X-Partial": "1"
        }
      });

      if (resp.status === 401) {
        detenerPolling();
        console.warn("[EV][SoporteDashboard] Sesión no válida.");
        return;
      }

      if (resp.status === 403) {
        detenerPolling();
        console.warn("[EV][SoporteDashboard] Acceso restringido.");
        return;
      }

      if (!resp.ok || !json || json.ok !== true) {
        setErrorTabla(silencioso);
        return;
      }

      const data = json.data || {};
      const kpis = data.kpis || {};
      const atender = Array.isArray(data.atender) ? data.atender : [];

      const nuevaFirma = crearFirma({
        kpis,
        atender
      });

      const huboCambio = ultimoResumenFirma !== "" && nuevaFirma !== ultimoResumenFirma;
      ultimoResumenFirma = nuevaFirma;

      renderKPIs(kpis, huboCambio);
      renderAtender(atender, huboCambio);
      actualizarIndicadorHora();
    } catch (e) {
      if (String(e?.name || "") === "AbortError") {
        console.warn("[EV][SoporteDashboard] Timeout consultando dashboard.");
      } else {
        console.error("[EV][SoporteDashboard] cargarDashboard error:", e);
      }

      setErrorTabla(silencioso);
    } finally {
      fetchEnCurso = false;
    }
  }

  function bindEventosLocales() {
    const sel = $("evFiltroTiempo");

    if (sel && !sel.dataset.evBound) {
      sel.dataset.evBound = "1";
      sel.addEventListener("change", () => {
        ultimoResumenFirma = "";
        ultimaAtencionFirma = "";
        cargarDashboard({ silencioso: false, forzar: true });
      });
    }
  }

  function iniciarPolling() {
    detenerPolling();

    if (!dashboardExiste()) return;

    pollingTimer = window.setInterval(() => {
      if (document.hidden) return;
      if (!dashboardExiste()) {
        detenerPolling();
        return;
      }

      cargarDashboard({ silencioso: true });
    }, POLLING_MS);
  }

  function detenerPolling() {
    if (pollingTimer) {
      window.clearInterval(pollingTimer);
      pollingTimer = null;
    }
  }

  function init() {
    if (!baseUrl) return false;
    if (!dashboardExiste()) return false;

    asegurarEstilosRuntime();
    asegurarIndicadorLive();
    bindEventosLocales();

    cargarDashboard({
      silencioso: bootedOnce,
      forzar: true
    });

    iniciarPolling();

    bootedOnce = true;
    return true;
  }

  function bindEventosGlobales() {
    if (eventosGlobalesAsignados) return;
    eventosGlobalesAsignados = true;

    document.addEventListener("visibilitychange", () => {
      if (document.hidden) return;

      if (dashboardExiste()) {
        cargarDashboard({ silencioso: true, forzar: true });
        iniciarPolling();
      }
    });

    window.addEventListener("pageshow", () => {
      if (dashboardExiste()) {
        cargarDashboard({ silencioso: true, forzar: true });
        iniciarPolling();
      }
    });

    document.addEventListener("ev:content-loaded", () => {
      window.setTimeout(() => {
        if (dashboardExiste()) {
          init();
        } else {
          detenerPolling();
        }
      }, AFTER_AJAX_DELAY_MS);
    });

    document.addEventListener("ev:nav-end", () => {
      window.setTimeout(() => {
        if (dashboardExiste()) {
          init();
        } else {
          detenerPolling();
        }
      }, AFTER_AJAX_DELAY_MS);
    });

    document.addEventListener("click", (e) => {
      const link = e.target.closest("a[data-ev-nav='1']");
      if (!link) return;

      window.setTimeout(() => {
        if (!dashboardExiste()) {
          detenerPolling();
        }
      }, 700);
    }, true);

    const observer = new MutationObserver(() => {
      if (dashboardExiste()) {
        if (!pollingTimer) init();
      } else {
        detenerPolling();
      }
    });

    observer.observe(document.documentElement, {
      childList: true,
      subtree: true
    });
  }

  window.EV_SoporteDashboard = window.EV_SoporteDashboard || {};
  window.EV_SoporteDashboard.init = init;
  window.EV_SoporteDashboard.refresh = function () {
    return cargarDashboard({ silencioso: false, forzar: true });
  };
  window.EV_SoporteDashboard.stop = detenerPolling;
  window.EV_SoporteDashboard.start = iniciarPolling;

  bindEventosGlobales();

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();