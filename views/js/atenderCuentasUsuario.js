// views/js/atenderCuentasUsuario.js
(function () {
  "use strict";

  const baseUrl = (window.BASE_URL || "").replace(/\/+$/, "");
  if (!baseUrl) return;

  let observer = null;
  let modalInstance = null;
  let currentId = null;

  // Evita doble inicialización por navegación AJAX
  let lastInitKey = "";

  // =========================
  // Helpers
  // =========================
  function esc(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function byId(id) {
    return document.getElementById(id);
  }

  function getControls() {
    return {
      selEstado: byId("filtroEstado"),
      selModo: byId("filtroModo"),
      selConjunto: byId("filtroConjunto"),
      selCondominio: byId("filtroCondominio"),
      inpBuscar: byId("filtroBuscar"),
      btnAplicar: byId("btnBuscarAplicar"),
      btnLimpiar: byId("btnBuscarLimpiar"),
      pagPrev: byId("btnPagPrev"),
      pagNext: byId("btnPagNext"),
      pagNum: byId("lblPagNum"),
      lblTotal: byId("lblTotal"),
      chips: document.querySelectorAll(".js-ev-chip"),
    };
  }

  /**
   * ✅ Solo consideramos que estamos en el módulo "Atender cuentas"
   * cuando existen los controles principales.
   */
  function isAtenderCuentasView() {
    const c = getControls();
    return !!(c.selModo && c.selEstado && c.inpBuscar);
  }

  /**
   * ✅ Tbody específico del módulo (NO usamos fallback genérico a "table tbody"
   * porque eso engancha el Dashboard y rompe la navegación AJAX)
   */
  function getTbody() {
    return byId("evUsuariosBody") || byId("tablaUsuariosBody") || null;
  }

  // =========================
  // Estados
  // =========================
  function normalizarEstado(v) {
    const s = String(v ?? "").trim().toLowerCase();

    if (s === "1") return "revision";
    if (s === "2") return "habilitado";
    if (s === "3") return "observado";
    if (s === "0") return "inactivo";
    if (["todos", "all"].includes(s)) return "todos";

    if (["revision", "en_revision"].includes(s)) return "revision";
    if (["observado", "observados"].includes(s)) return "observado";

    return "revision";
  }

  function estadoToApiValue(estado) {
    switch (estado) {
      case "revision":
        return "1";
      case "habilitado":
        return "2";
      case "observado":
        return "3";
      case "inactivo":
        return "0";
      case "todos":
        return "todos";
      default:
        return "1";
    }
  }

  function badgeEstadoUsuario(estado) {
    const n = Number(estado);

    if (n === 3) {
      return `<span class="ev-badge ev-off">
        <i class="bi bi-exclamation-triangle"></i> Observado
      </span>`;
    }

    if (n === 2) {
      return `<span class="ev-badge ev-ok">
        <i class="bi bi-check2-circle"></i> Habilitado
      </span>`;
    }

    if (n === 0) {
      return `<span class="ev-badge ev-off">
        <i class="bi bi-slash-circle"></i> Inactivo
      </span>`;
    }

    return `<span class="ev-badge ev-review">
      <i class="bi bi-hourglass-split"></i> En revisión
    </span>`;
  }

  function residenciaTxt(it) {
    const tipoRaw =
      it.tipo_conjunto ||
      it.tipoConjunto ||
      it.conjunto_tipo ||
      it.tipo ||
      "";

    const tipo = String(tipoRaw).toLowerCase();
    if (!tipo) return `<span class="text-muted">—</span>`;

    const dir = it.direccion || it.direccion_residencia || it.dir || "";
    const t = tipo.includes("cond") ? "Condominio" : "Urbanización";

    return `<div class="fw-semibold">${esc(t)}</div>
            <div class="text-muted small">${esc(dir || "—")}</div>`;
  }

  function setLoading(tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 ev-empty">Cargando...</td>
      </tr>`;
  }

  function setEmpty(tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 ev-empty">
          No hay registros para mostrar.
        </td>
      </tr>`;
  }

  function setError(tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 ev-empty">
          Error al cargar datos.
        </td>
      </tr>`;
  }

  // =========================
  // Endpoint
  // =========================
  function endpointList(modo) {
    const m = String(modo || "").toLowerCase();
    if (m.includes("res")) return `${baseUrl}/api/soporte/residencias`;
    return `${baseUrl}/api/soporte/usuarios`;
  }

  // =========================
  // Render tabla
  // =========================
  function renderRows(tbody, items) {
    if (!Array.isArray(items) || items.length === 0) {
      setEmpty(tbody);
      return;
    }

    tbody.innerHTML = items
      .map((it) => {
        const id = Number(it.codigo_usuario ?? it.id ?? 0);

        // ✅ Regla de estado visual:
        // - Si estado_revision = 3 => Observado (aunque usuario_estado sea 1)
        // - Sino usar usuario_estado
        const estadoRevision = Number(it.estado_revision ?? 0);
        const estadoUsuario = Number(it.usuario_estado ?? it.estado ?? 1);
        const estadoVisual = estadoRevision === 3 ? 3 : estadoUsuario;

        const nombre = esc(it.nombre || "—");
        const email = esc(it.email || "—");
        const doc = esc(it.documento || "—");
        const tel = esc(it.telefono || "—");

        const comprobante =
          it.comprobante_domicilio ||
          it.comprobante ||
          it.comprobante_url ||
          it.url_comprobante ||
          "";

        return `
        <tr>
          <td>
            <div class="fw-bold">${nombre}</div>
            <div class="text-muted small">${doc}</div>
          </td>
          <td>
            <div class="fw-semibold">${email}</div>
            <div class="text-muted small">${tel}</div>
          </td>
          <td>${residenciaTxt(it)}</td>
          <td class="text-center">${badgeEstadoUsuario(estadoVisual)}</td>
          <td class="text-end">
            <button
              type="button"
              class="btn btn-sm ev-btn-orange js-ev-revisar"
              data-id="${id}"
              data-nombre="${nombre}"
              data-email="${email}"
              data-doc="${doc}"
              data-tel="${tel}"
              data-tipo_conjunto="${esc(it.tipo_conjunto || "")}"
              data-direccion="${esc(it.direccion || "")}"
              data-estado="${estadoVisual}"
              data-comprobante="${esc(comprobante)}"
            >
              Revisar
            </button>
          </td>
        </tr>`;
      })
      .join("");
  }

  // =========================
  // Modal
  // =========================
  function ensureModal() {
    const el = byId("modalRevisarCuenta");
    if (!el) return null;
    if (!modalInstance) {
      modalInstance = new bootstrap.Modal(el, { backdrop: "static" });
    }
    return modalInstance;
  }

  function fillModalFromButton(btn) {
    currentId = Number(btn.dataset.id || 0);

    if (byId("mNombre")) byId("mNombre").textContent = btn.dataset.nombre || "—";
    if (byId("mEmail")) byId("mEmail").textContent = btn.dataset.email || "—";
    if (byId("mDoc")) byId("mDoc").textContent = btn.dataset.doc || "—";
    if (byId("mTel")) byId("mTel").textContent = btn.dataset.tel || "—";
    if (byId("mTipoConjunto")) byId("mTipoConjunto").textContent = btn.dataset.tipo_conjunto || "—";
    if (byId("mDireccion")) byId("mDireccion").textContent = btn.dataset.direccion || "—";
    if (byId("mBadgeEstado")) byId("mBadgeEstado").innerHTML = badgeEstadoUsuario(btn.dataset.estado);
    if (byId("mObsTexto")) byId("mObsTexto").value = "";
  }

  // =========================
  // API
  // =========================
  async function postEstado(id, estado) {
    const resp = await fetch(`${baseUrl}/api/soporte/usuarios/${id}/estado`, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-Partial": "1" },
      credentials: "include",
      body: JSON.stringify({ estado: Number(estado) }),
    });

    const json = await resp.json();
    if (!resp.ok || json.ok !== true) {
      throw new Error("No se pudo actualizar estado");
    }
  }

  async function postObservacion(id, observacion) {
    const obs = String(observacion || "").trim();
    if (!obs) return alert("Ingresa una observación.");

    const resp = await fetch(`${baseUrl}/api/cuenta-observada/${id}/observar`, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-Partial": "1" },
      credentials: "include",
      body: JSON.stringify({ observacion: obs }),
    });

    const json = await resp.json();
    if (!resp.ok || json.ok !== true) {
      throw new Error("No se pudo registrar observación");
    }
  }

  // =========================
  // Carga
  // =========================
  async function load(state) {
    const tbody = getTbody();
    if (!tbody) return;

    setLoading(tbody);

    try {
      const url = new URL(endpointList(state.modo), window.location.origin);
      url.searchParams.set("estado", estadoToApiValue(state.estado));
      url.searchParams.set("q", state.q || "");
      url.searchParams.set("page", state.page);
      url.searchParams.set("limit", state.limit);
      url.searchParams.set("_", Date.now());

      const resp = await fetch(url, {
        headers: { "X-Partial": "1" },
        credentials: "include",
      });

      const json = await resp.json();
      if (!resp.ok || json.ok !== true) throw new Error("API no ok");

      renderRows(tbody, json.data.items);

      const c = getControls();
      if (c.lblTotal) c.lblTotal.textContent = String(json.data.total ?? 0);
    } catch (e) {
      setError(tbody);
    }
  }

  // =========================
  // Init
  // =========================
  function init() {
    // ✅ Solo inicializar si estamos realmente en la vista de atender cuentas
    if (!isAtenderCuentasView()) return false;

    const tbody = getTbody();
    if (!tbody) return false;

    // key para evitar doble init en mismo DOM
    const key = "atender-cuentas|" + window.location.pathname + "|" + (byId("evUsuariosBody") ? "evUsuariosBody" : "tablaUsuariosBody");
    if (key === lastInitKey) return true;
    lastInitKey = key;

    const c = getControls();

    const state = {
      modo: c.selModo?.value || "usuarios",
      estado: normalizarEstado(c.selEstado?.value || "revision"),
      conjunto: c.selConjunto?.value || "todos",
      condominio: c.selCondominio?.value || "",
      q: c.inpBuscar?.value || "",
      page: 1,
      limit: 10,
    };

    // Click en "Revisar"
    document.addEventListener("click", (e) => {
      const btn = e.target.closest(".js-ev-revisar");
      if (!btn) return;

      const m = ensureModal();
      if (!m) return;

      fillModalFromButton(btn);
      modalInstance.show();
    });

    load(state);
    return true;
  }

  // ✅ Siempre observamos cambios (navegación AJAX cambia el DOM)
  function bootObserver() {
    if (observer) return;

    observer = new MutationObserver(() => {
      init();
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }

  // init en DOM ready + observer permanente
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      init();
      bootObserver();
    });
  } else {
    init();
    bootObserver();
  }
})();
