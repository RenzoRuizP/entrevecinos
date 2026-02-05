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

  // Estado compartido (para refrescar luego de acciones)
  const state = {
    modo: "usuarios",
    estado: "revision",
    q: "",
    conjunto: "",
    conjunto_id: "",
    page: 1,
    limit: 10,
  };

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
   */
  function isAtenderCuentasView() {
    const c = getControls();
    return !!(c.selModo && c.selEstado && c.inpBuscar);
  }

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

    if (["revision", "en_revision", "en revisión"].includes(s)) return "revision";
    if (["habilitado", "habilitados"].includes(s)) return "habilitado";
    if (["observado", "observados"].includes(s)) return "observado";
    if (["inactivo", "inactivos"].includes(s)) return "inactivo";

    return "revision";
  }

  function estadoToApiValue(estado) {
    switch (estado) {
      case "revision": return "1";
      case "habilitado": return "2";
      case "observado": return "3";
      case "inactivo": return "0";
      case "todos": return "todos";
      default: return "1";
    }
  }

  function badgeEstadoUsuario(estado) {
    const n = Number(estado);

    if (n === 3) {
      return `<span class="ev-badge ev-off"><i class="bi bi-exclamation-triangle"></i> Observado</span>`;
    }
    if (n === 2) {
      return `<span class="ev-badge ev-ok"><i class="bi bi-check2-circle"></i> Habilitado</span>`;
    }
    if (n === 0) {
      return `<span class="ev-badge ev-off"><i class="bi bi-slash-circle"></i> Inactivo</span>`;
    }
    return `<span class="ev-badge ev-review"><i class="bi bi-hourglass-split"></i> En revisión</span>`;
  }

  function residenciaTxt(it) {
    const tipoRaw = it.tipo_conjunto || it.tipoConjunto || it.conjunto_tipo || it.tipo || "";
    const tipo = String(tipoRaw).toLowerCase();
    if (!tipo) return `<span class="text-muted">—</span>`;

    const dir = it.direccion || it.direccion_residencia || it.dir || "";
    const t = tipo.includes("cond") ? "Condominio" : "Urbanización";

    return `<div class="fw-semibold">${esc(t)}</div>
            <div class="text-muted small">${esc(dir || "—")}</div>`;
  }

  function setLoading(tbody) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 ev-empty">Cargando...</td></tr>`;
  }

  function setEmpty(tbody) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 ev-empty">No hay registros para mostrar.</td></tr>`;
  }

  function setError(tbody) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 ev-empty">Error al cargar datos.</td></tr>`;
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
  // Combo Conjunto -> Condominio/Urbanización
  // =========================
  function normalizarConjuntoUI(v) {
    const s = String(v ?? "").trim().toLowerCase();
    if (!s) return "";
    if (s.includes("cond")) return "condominio";
    if (s.includes("urban")) return "urbanizacion";
    return "";
  }

  function mapItemToOption(tipo, it) {
    if (tipo === "condominio") {
      const id = it.codigo_condominio ?? it.codigo ?? it.id ?? it.value ?? "";
      const name = it.nombre_condominio ?? it.nombre ?? it.text ?? "";
      return { id, name };
    }
    if (tipo === "urbanizacion") {
      const id = it.codigo_urbanizacion ?? it.codigo ?? it.id ?? it.value ?? "";
      const name = it.nombre_urbanizacion ?? it.nombre ?? it.text ?? "";
      return { id, name };
    }
    return { id: "", name: "" };
  }

  async function cargarListaConjuntos(tipo) {
    const c = getControls();
    if (!c.selCondominio) return;

    c.selCondominio.innerHTML = `<option value="">Cargando...</option>`;
    c.selCondominio.disabled = true;

    let url = "";
    if (tipo === "condominio") url = `${baseUrl}/condominios`;
    else if (tipo === "urbanizacion") url = `${baseUrl}/urbanizaciones`;
    else {
      c.selCondominio.innerHTML = `<option value="">Selecciona...</option>`;
      c.selCondominio.disabled = true;
      return;
    }

    try {
      const resp = await fetch(url, {
        headers: { "X-Partial": "1" },
        credentials: "include",
      });

      const json = await resp.json();
      if (!resp.ok) throw new Error("HTTP");

      const items = Array.isArray(json?.data) ? json.data : (Array.isArray(json) ? json : []);

      const opts = items
        .map((it) => mapItemToOption(tipo, it))
        .filter((x) => String(x.id ?? "") !== "" && String(x.name ?? "") !== "")
        .map((x) => `<option value="${esc(x.id)}">${esc(x.name)}</option>`)
        .join("");

      c.selCondominio.innerHTML = `<option value="">Selecciona...</option>` + opts;
      c.selCondominio.disabled = false;
    } catch (e) {
      c.selCondominio.innerHTML = `<option value="">Error al cargar</option>`;
      c.selCondominio.disabled = true;
    }
  }

  function wireConjuntoDependienteOnce() {
    const c = getControls();
    if (!c.selConjunto || !c.selCondominio) return;

    if (c.selConjunto.dataset.evWired === "1") return;
    c.selConjunto.dataset.evWired = "1";

    c.selConjunto.addEventListener("change", () => {
      const tipo = normalizarConjuntoUI(c.selConjunto.value);

      c.selCondominio.innerHTML = `<option value="">Selecciona...</option>`;
      c.selCondominio.disabled = true;

      if (!tipo) return;
      cargarListaConjuntos(tipo);
    });

    const tipoInicial = normalizarConjuntoUI(c.selConjunto.value);
    if (tipoInicial) {
      cargarListaConjuntos(tipoInicial);
    } else {
      c.selCondominio.innerHTML = `<option value="">Selecciona...</option>`;
      c.selCondominio.disabled = true;
    }
  }

  // =========================
  // Modal: modo "view" vs "review"
  // =========================
  function setModalReadOnly(isReadOnly) {
    const btnObs = byId("btnModalObservar");
    const btnApr = byId("btnModalAprobar");
    const btnIna = byId("btnModalInactivar");
    const obsTxt = byId("mObsTexto");

    // Botones de acción
    if (btnObs) btnObs.style.display = isReadOnly ? "none" : "";
    if (btnApr) btnApr.style.display = isReadOnly ? "none" : "";
    if (btnIna) btnIna.style.display = isReadOnly ? "none" : "";

    // Observación solo lectura
    if (obsTxt) {
      obsTxt.readOnly = !!isReadOnly;
      obsTxt.disabled = !!isReadOnly;
    }
  }

  function fillModalFromButton(btn) {
    currentId = Number(btn.dataset.id || 0);

    byId("mNombre").textContent = btn.dataset.nombre || "—";
    byId("mEmail").textContent = btn.dataset.email || "—";
    byId("mDoc").textContent = btn.dataset.doc || "—";
    byId("mTel").textContent = btn.dataset.tel || "—";
    byId("mTipoConjunto").textContent = btn.dataset.tipo_conjunto || "—";
    byId("mDireccion").textContent = btn.dataset.direccion || "—";
    byId("mBadgeEstado").innerHTML = badgeEstadoUsuario(btn.dataset.estado);

    const obsTextarea = byId("mObsTexto");
    if (obsTextarea) {
      obsTextarea.value = btn.dataset.observacion || "";
    }

    const img = byId("mImgComprobante");
    const pdf = byId("mPdfComprobante");
    const empty = byId("mNoComprobante");
    const link = byId("mLinkComprobante");

    if (img) img.style.display = "none";
    if (pdf) pdf.style.display = "none";
    if (empty) empty.style.display = "none";
    if (link) link.style.display = "none";

    const path = btn.dataset.comprobante || "";
    if (!path) {
      if (empty) empty.style.display = "block";
    } else {
      const url = `${baseUrl}/${path.replace(/^\/+/, "")}`;
      if (link) {
        link.href = url;
        link.style.display = "inline";
      }

      if (/\.pdf$/i.test(url)) {
        if (pdf) {
          pdf.src = url;
          pdf.style.display = "block";
        }
      } else {
        if (img) {
          img.src = url;
          img.style.display = "block";
        }
      }
    }
  }

  // =========================
  // Render tabla
  // =========================
  function renderRows(tbody, items) {
    if (!Array.isArray(items) || items.length === 0) {
      setEmpty(tbody);
      return;
    }

    tbody.innerHTML = items.map((it) => {
      const id = Number(it.codigo_usuario ?? it.id ?? 0);

      const estadoRevision = Number(it.estado_revision ?? 0);
      const estadoUsuario = Number(it.usuario_estado ?? it.estado ?? 1);
      const estadoVisual = estadoRevision === 3 ? 3 : estadoUsuario;

      const nombre = esc(it.nombre || "—");
      const email = esc(it.email || "—");
      const doc = esc(it.documento || "—");
      const tel = esc(it.telefono || "—");

      const comprobante = it.comprobante_domicilio || it.comprobante || it.comprobante_url || it.url_comprobante || "";

      // ✅ Si está habilitado => botón "Ver" (solo lectura)
      const isHabilitado = Number(estadoVisual) === 2;
      const btnText = isHabilitado ? "Ver" : "Revisar";

      // ✅ MISMO ESTILO (naranja) para ambos
      const btnClass = "ev-btn-orange";

      const btnMode = isHabilitado ? "view" : "review";

      return `
        <tr>
          <td><div class="fw-bold">${nombre}</div><div class="text-muted small">${doc}</div></td>
          <td><div class="fw-semibold">${email}</div><div class="text-muted small">${tel}</div></td>
          <td>${residenciaTxt(it)}</td>
          <td class="text-center">${badgeEstadoUsuario(estadoVisual)}</td>
          <td class="text-end">
            <button type="button" class="btn btn-sm ${btnClass} js-ev-revisar" data-id="${id}"
              data-mode="${btnMode}"
              data-nombre="${nombre}"
              data-email="${email}"
              data-doc="${doc}"
              data-tel="${tel}"
              data-tipo_conjunto="${esc(it.tipo_conjunto || "")}"
              data-direccion="${esc(it.direccion || "")}"
              data-estado="${estadoVisual}"
              data-comprobante="${esc(comprobante)}"
              data-observacion="${esc(it.mensaje_observacion || "")}">
              ${btnText}
            </button>
          </td>
        </tr>`;
    }).join("");
  }

  // ===================================================
  // Modal: abrir con botón Revisar/Ver
  // ===================================================
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-ev-revisar");
    if (!btn) return;

    const modalEl = document.getElementById("modalRevisarCuenta");
    if (!modalEl) return;

    modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
      backdrop: "static"
    });

    // ✅ Modo del modal
    const mode = String(btn.dataset.mode || "review").toLowerCase();
    const isReadOnly = (mode === "view");
    setModalReadOnly(isReadOnly);

    fillModalFromButton(btn);

    modalInstance.show();
  });

  // ===================================================
  // FIX: botón "Observar"
  // ===================================================
  document.addEventListener("click", async (e) => {
    const btn = e.target.closest("#btnModalObservar");
    if (!btn) return;

    if (!currentId) {
      Swal.fire({
        icon: "warning",
        title: "Usuario no identificado",
        text: "No se pudo determinar la cuenta.",
      });
      return;
    }

    const obs = byId("mObsTexto")?.value || "";
    if (!obs.trim()) {
      Swal.fire({
        icon: "warning",
        title: "Observación requerida",
        text: "Debes ingresar una observación.",
      });
      return;
    }

    try {
      btn.disabled = true;

      const resp = await fetch(
        `${baseUrl}/api/cuenta-observada/${currentId}/observar`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Partial": "1",
          },
          credentials: "include",
          body: JSON.stringify({ observacion: obs }),
        }
      );

      const json = await resp.json();
      if (!resp.ok || json.ok !== true) {
        throw new Error(json.mensaje || "No se pudo registrar la observación.");
      }

      Swal.fire({
        icon: "success",
        title: "Observación registrada",
        timer: 1500,
        showConfirmButton: false,
      });

      modalInstance?.hide();

      state.page = 1;
      load(state);

    } catch (err) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: err.message || "Error al registrar observación.",
      });
    } finally {
      btn.disabled = false;
    }
  });

  // ===================================================
  // FIX: botón "Aprobar"
  // ===================================================
  document.addEventListener("click", async (e) => {
    const btn = e.target.closest("#btnModalAprobar");
    if (!btn) return;

    if (!currentId) {
      Swal.fire({
        icon: "warning",
        title: "Usuario no identificado",
        text: "No se pudo determinar la cuenta.",
      });
      return;
    }

    const confirm = await Swal.fire({
      icon: "question",
      title: "Aprobar cuenta",
      text: "Esta acción habilitará la cuenta y eliminará cualquier observación.",
      showCancelButton: true,
      confirmButtonText: "Sí, aprobar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#EA7C12",
    });

    if (!confirm.isConfirmed) return;

    try {
      btn.disabled = true;

      const resp = await fetch(
        `${baseUrl}/api/soporte/usuarios/${currentId}/estado`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Partial": "1",
          },
          credentials: "include",
          body: JSON.stringify({ estado: 2 }),
        }
      );

      const json = await resp.json();
      if (!resp.ok || json.ok !== true) {
        throw new Error(json.mensaje || "No se pudo aprobar la cuenta.");
      }

      Swal.fire({
        icon: "success",
        title: "Cuenta aprobada",
        timer: 1400,
        showConfirmButton: false,
      });

      modalInstance?.hide();

      state.page = 1;
      load(state);

    } catch (err) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: err.message || "Error al aprobar la cuenta.",
      });
    } finally {
      btn.disabled = false;
    }
  });

  // ===================================================
  // ✅ FIX: botón "Inactivar" (faltaba)
  // ===================================================
  document.addEventListener("click", async (e) => {
    const btn = e.target.closest("#btnModalInactivar");
    if (!btn) return;

    if (!currentId) {
      Swal.fire({
        icon: "warning",
        title: "Usuario no identificado",
        text: "No se pudo determinar la cuenta.",
      });
      return;
    }

    const confirm = await Swal.fire({
      icon: "warning",
      title: "Inactivar cuenta",
      text: "Esta acción inactivará la cuenta del usuario.",
      showCancelButton: true,
      confirmButtonText: "Sí, inactivar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#DC2626",
    });

    if (!confirm.isConfirmed) return;

    try {
      btn.disabled = true;

      const resp = await fetch(
        `${baseUrl}/api/soporte/usuarios/${currentId}/estado`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Partial": "1",
          },
          credentials: "include",
          body: JSON.stringify({ estado: 0 }),
        }
      );

      const json = await resp.json();
      if (!resp.ok || json.ok !== true) {
        throw new Error(json.mensaje || "No se pudo inactivar la cuenta.");
      }

      Swal.fire({
        icon: "success",
        title: "Cuenta inactivada",
        timer: 1400,
        showConfirmButton: false,
      });

      modalInstance?.hide();

      state.page = 1;
      load(state);

    } catch (err) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: err.message || "Error al inactivar cuenta.",
      });
    } finally {
      btn.disabled = false;
    }
  });

  // =========================
  // Carga
  // =========================
  async function load(s) {
    const tbody = getTbody();
    if (!tbody) return;

    setLoading(tbody);

    try {
      const url = new URL(endpointList(s.modo), window.location.origin);

      url.searchParams.set("estado", estadoToApiValue(s.estado));
      url.searchParams.set("q", s.q || "");
      url.searchParams.set("page", s.page);
      url.searchParams.set("limit", s.limit);

      if (s.conjunto) url.searchParams.set("conjunto", s.conjunto);
      if (s.conjunto_id) url.searchParams.set("conjunto_id", s.conjunto_id);

      url.searchParams.set("_", Date.now());

      const resp = await fetch(url, {
        headers: { "X-Partial": "1" },
        credentials: "include",
      });

      const json = await resp.json();
      if (!resp.ok || json.ok !== true) throw new Error("API");

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
    if (!isAtenderCuentasView()) return false;

    const key = "atender-cuentas|" + window.location.pathname;
    if (key === lastInitKey) return true;
    lastInitKey = key;

    const c = getControls();

    wireConjuntoDependienteOnce();

    // Inicializa state desde UI
    state.modo = c.selModo?.value || "usuarios";
    state.estado = normalizarEstado(c.selEstado?.value || "revision");
    state.q = c.inpBuscar?.value || "";
    state.conjunto = normalizarConjuntoUI(c.selConjunto?.value || "");
    state.conjunto_id = c.selCondominio?.value || "";
    state.page = 1;
    state.limit = 10;

    // CHIPS
    c.chips.forEach((chip) => {
      chip.addEventListener("click", () => {
        c.chips.forEach(x => x.classList.remove("ev-chip-active"));
        chip.classList.add("ev-chip-active");

        const nuevoEstado = normalizarEstado(chip.dataset.estado);
        state.estado = nuevoEstado;
        if (c.selEstado) c.selEstado.value = nuevoEstado;

        state.page = 1;
        load(state);
      });
    });

    // Aplicar filtros
    if (c.btnAplicar) {
      c.btnAplicar.addEventListener("click", () => {
        state.modo = c.selModo?.value || "usuarios";
        state.estado = normalizarEstado(c.selEstado?.value || "revision");
        state.q = c.inpBuscar?.value || "";
        state.conjunto = normalizarConjuntoUI(c.selConjunto?.value || "");
        state.conjunto_id = c.selCondominio?.value || "";
        state.page = 1;
        load(state);
      });
    }

    // Limpiar
    if (c.btnLimpiar) {
      c.btnLimpiar.addEventListener("click", () => {
        if (c.inpBuscar) c.inpBuscar.value = "";
        if (c.selEstado) c.selEstado.value = "revision";
        if (c.selModo) c.selModo.value = "usuarios";

        if (c.selConjunto) c.selConjunto.value = "";
        if (c.selCondominio) {
          c.selCondominio.innerHTML = `<option value="">Selecciona...</option>`;
          c.selCondominio.disabled = true;
          c.selCondominio.value = "";
        }

        state.modo = "usuarios";
        state.estado = "revision";
        state.q = "";
        state.conjunto = "";
        state.conjunto_id = "";
        state.page = 1;

        load(state);
      });
    }

    load(state);
    return true;
  }

  // =========================
  // Observer (AJAX)
  // =========================
  function bootObserver() {
    if (observer) return;
    observer = new MutationObserver(() => init());
    observer.observe(document.body, { childList: true, subtree: true });
  }

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
