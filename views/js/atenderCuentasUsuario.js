// views/js/atenderCuentasUsuario.js
(function () {
  "use strict";

  const baseUrl = (window.EV?.baseUrl ?? window.BASE_URL ?? "").replace(/\/+$/, "");

  let observer = null;
  let modalInstance = null;
  let currentId = null;
  let currentKind = "usuario"; // usuario | residencia

  let lastInitKey = "";
  let searchActive = false;

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

  function isAtenderCuentasView() {
    const c = getControls();
    return !!(c.selModo && c.selEstado && c.inpBuscar);
  }

  function getTbody() {
    return byId("evUsuariosBody") || null;
  }

  function debounce(fn, wait) {
    let t = null;
    return function (...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  function normalizarEstado(v) {
    const s = String(v ?? "").trim().toLowerCase();

    if (s === "1") return "revision";
    if (s === "2") return "habilitado";
    if (s === "3") return "observado";
    if (s === "0") return "inactivo";
    if (["todos", "all"].includes(s)) return "todos";

    if (["revision", "en_revision", "en revisión"].includes(s)) return "revision";
    if (["habilitado", "habilitados", "aprobada", "aprobado"].includes(s)) return "habilitado";
    if (["observado", "observados", "observada"].includes(s)) return "observado";
    if (["inactivo", "inactivos", "rechazada", "rechazado"].includes(s)) return "inactivo";

    return "revision";
  }

  function estadoToApiValue(estado, modo) {
    const isResidencias = String(modo || "").toLowerCase().includes("res");

    if (isResidencias) {
      switch (estado) {
        case "revision": return "pendiente";
        case "habilitado": return "aprobada";
        case "observado": return "observada";
        case "inactivo": return "rechazada";
        case "todos": return "all";
        default: return "pendiente";
      }
    }

    switch (estado) {
      case "revision": return "1";
      case "habilitado": return "2";
      case "observado": return "3";
      case "inactivo": return "0";
      case "todos": return "todos";
      default: return "1";
    }
  }

  function badgeEstadoUsuario(estado, esCambioResidencia = false, estadoSolicitud = "") {
    const n = Number(estado);
    const estSol = String(estadoSolicitud || "").toLowerCase();

    if (esCambioResidencia) {
      if (estSol === "observada") {
        return `<div class="d-flex flex-column align-items-center gap-1">
          <span class="ev-badge ev-off"><i class="bi bi-exclamation-triangle"></i> Observada</span>
          <span class="ev-badge ev-res"><i class="bi bi-house-door"></i> Cambio de residencia</span>
        </div>`;
      }
      if (estSol === "pendiente") {
        return `<div class="d-flex flex-column align-items-center gap-1">
          <span class="ev-badge ev-review"><i class="bi bi-hourglass-split"></i> En revisión</span>
          <span class="ev-badge ev-res"><i class="bi bi-house-door"></i> Cambio de residencia</span>
        </div>`;
      }
    }

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
    const tipoRaw = it.tipo_conjunto || "";
    const tipo = String(tipoRaw).toLowerCase();
    if (!tipo) return `<span class="text-muted">—</span>`;

    const dir = it.direccion || "";
    const t = tipo.includes("cond") ? "Condominio" : "Urbanización";
    const extra = Number(it.es_cambio_residencia || 0) === 1
      ? `<div class="small text-warning fw-semibold mt-1">Solicitud de cambio</div>`
      : "";

    return `<div class="fw-semibold">${esc(t)}</div>
            <div class="text-muted small">${esc(dir || "—")}</div>
            ${extra}`;
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

  function endpointList(modo) {
    const m = String(modo || "").toLowerCase();
    if (m.includes("res")) return `${baseUrl}/api/soporte/residencias`;
    return `${baseUrl}/api/soporte/usuarios`;
  }

  function normalizarConjuntoUI(v) {
    const s = String(v ?? "").trim().toLowerCase();
    if (!s || s === "todos") return "";
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

  function setModalMode(kind, modeLabel) {
    const lbl = byId("mModalTipoRevision");
    const btnObs = byId("btnModalObservar");
    const btnApr = byId("btnModalAprobar");
    const btnIna = byId("btnModalInactivar");
    const hint = byId("mHintRevision");

    const isResidencia = kind === "residencia";

    if (lbl) {
      lbl.textContent = isResidencia ? "Cambio de residencia" : "Cuenta de usuario";
    }

    if (btnObs) btnObs.textContent = "Observar";
    if (btnApr) btnApr.textContent = isResidencia ? "Aprobar cambio" : "Activar";
    if (btnIna) btnIna.textContent = isResidencia ? "Rechazar" : "Desactivar";

    if (hint) {
      hint.textContent = isResidencia
        ? "Verifica que el comprobante coincida con la nueva residencia solicitada."
        : "Verifica que el comprobante coincida con la residencia.";
    }
  }

  function renderRows(tbody, items, modo) {
    if (!Array.isArray(items) || items.length === 0) {
      setEmpty(tbody);
      return;
    }

    const isResidencias = String(modo || "").toLowerCase().includes("res");

    tbody.innerHTML = items.map((it) => {
      const esCambioResidencia = isResidencias || Number(it.es_cambio_residencia || 0) === 1;
      const id = Number(esCambioResidencia ? (it.codigo_solicitud ?? 0) : (it.codigo_usuario ?? it.id ?? 0));
      const actionKind = esCambioResidencia ? "residencia" : "usuario";

      const estadoRevision = Number(it.estado_revision ?? 0);
      const estadoUsuario = Number(it.usuario_estado ?? it.estado ?? 1);
      const estadoSolicitud = String(it.estado_solicitud_residencia || it.estado || "").toLowerCase();

      let estadoVisual = estadoRevision === 3 ? 3 : estadoUsuario;
      if (esCambioResidencia) {
        if (estadoSolicitud === "observada") estadoVisual = 3;
        else if (estadoSolicitud === "aprobada") estadoVisual = 2;
        else if (estadoSolicitud === "rechazada") estadoVisual = 0;
        else estadoVisual = 1;
      }

      const nombre = esc(it.nombre || "—");
      const email = esc(it.email || "—");
      const doc = esc(it.documento || "—");
      const tel = esc(it.telefono || "—");

      const tipoConjunto = it.tipo_conjunto || "";
      const direccion = it.direccion || "";
      const comprobante = it.comprobante_domicilio || "";
      const observacion = it.comentario_admin_solicitud || it.mensaje_observacion || "";

      const btnText = esCambioResidencia
        ? "Revisar cambio"
        : (Number(estadoVisual) === 2 ? "Ver" : "Revisar");

      const btnClass = Number(estadoVisual) === 2 ? "ev-btn-outline" : "ev-btn-orange";

      return `
        <tr>
          <td><div class="fw-bold">${nombre}</div><div class="text-muted small">${doc}</div></td>
          <td><div class="fw-semibold">${email}</div><div class="text-muted small">${tel}</div></td>
          <td>${residenciaTxt({ ...it, tipo_conjunto: tipoConjunto, direccion })}</td>
          <td class="text-center">${badgeEstadoUsuario(estadoVisual, esCambioResidencia, estadoSolicitud)}</td>
          <td class="text-end">
            <button type="button" class="btn btn-sm ${btnClass} js-ev-revisar"
              data-kind="${actionKind}"
              data-id="${id}"
              data-nombre="${nombre}"
              data-email="${email}"
              data-doc="${doc}"
              data-tel="${tel}"
              data-tipo_conjunto="${esc(tipoConjunto)}"
              data-direccion="${esc(direccion)}"
              data-estado="${estadoVisual}"
              data-estado_solicitud="${esc(estadoSolicitud)}"
              data-es_cambio_residencia="${esCambioResidencia ? '1' : '0'}"
              data-comprobante="${esc(comprobante)}"
              data-observacion="${esc(observacion)}">
              ${btnText}
            </button>
          </td>
        </tr>`;
    }).join("");
  }

  function snapshotStateFromUI() {
    const c = getControls();
    return {
      modo: c.selModo?.value || "usuarios",
      estado: normalizarEstado(c.selEstado?.value || "revision"),
      q: (c.inpBuscar?.value || "").trim(),
      conjunto: normalizarConjuntoUI(c.selConjunto?.value || ""),
      conjunto_id: c.selCondominio?.value || "",
      page: 1,
      limit: 10,
    };
  }

  function makeGlobalSearchState(txt) {
    return {
      modo: "usuarios",
      estado: "todos",
      q: txt,
      conjunto: "",
      conjunto_id: "",
      page: 1,
      limit: 10,
      __globalSearch: true,
    };
  }

  async function load(state) {
    const tbody = getTbody();
    if (!tbody) return;

    setLoading(tbody);

    try {
      const url = new URL(endpointList(state.modo), window.location.origin);
      const isResidencias = String(state.modo || "").toLowerCase().includes("res");

      url.searchParams.set("estado", estadoToApiValue(state.estado, state.modo));
      url.searchParams.set("q", state.q || "");
      url.searchParams.set("page", state.page);

      if (isResidencias) {
        url.searchParams.set("size", state.limit);
        if (state.conjunto) url.searchParams.set("tipo", state.conjunto);
        if (state.conjunto_id) url.searchParams.set("codigo", state.conjunto_id);
      } else {
        url.searchParams.set("limit", state.limit);
        if (state.conjunto) url.searchParams.set("conjunto", state.conjunto);
        if (state.conjunto_id) url.searchParams.set("conjunto_id", state.conjunto_id);
      }

      url.searchParams.set("_", Date.now());

      const resp = await fetch(url, {
        headers: { "X-Partial": "1" },
        credentials: "include",
      });

      const json = await resp.json();
      if (!resp.ok || json.ok !== true) throw new Error("API");

      const items = json?.data?.items || [];
      const total = Number(json?.data?.total || 0);

      renderRows(tbody, items, state.modo);

      const c = getControls();
      if (c.lblTotal) c.lblTotal.textContent = String(total);
      if (c.pagNum) c.pagNum.textContent = String(state.page || 1);
    } catch (e) {
      setError(tbody);
    }
  }

  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-ev-revisar");
    if (!btn) return;

    const modalEl = document.getElementById("modalRevisarCuenta");
    if (!modalEl) return;

    modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
      backdrop: "static",
    });

    currentId = Number(btn.dataset.id || 0);
    currentKind = btn.dataset.kind || "usuario";

    byId("mNombre").textContent = btn.dataset.nombre || "—";
    byId("mEmail").textContent = btn.dataset.email || "—";
    byId("mDoc").textContent = btn.dataset.doc || "—";
    byId("mTel").textContent = btn.dataset.tel || "—";
    byId("mTipoConjunto").textContent = btn.dataset.tipo_conjunto || "—";
    byId("mDireccion").textContent = btn.dataset.direccion || "—";
    byId("mBadgeEstado").innerHTML = badgeEstadoUsuario(
      btn.dataset.estado,
      btn.dataset.es_cambio_residencia === "1",
      btn.dataset.estado_solicitud || ""
    );

    setModalMode(currentKind, btn.dataset.mode || "review");

    const obsTextarea = byId("mObsTexto");
    if (obsTextarea) obsTextarea.value = btn.dataset.observacion || "";

    const img = byId("mImgComprobante");
    const pdf = byId("mPdfComprobante");
    const empty = byId("mNoComprobante");
    const link = byId("mLinkComprobante");

    if (img) { img.style.display = "none"; img.src = ""; }
    if (pdf) { pdf.style.display = "none"; pdf.src = ""; }
    if (empty) empty.style.display = "none";
    if (link) {
      link.style.display = "none";
      link.href = "#";
    }

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

    modalInstance.show();
  });

  document.addEventListener("click", async (e) => {
    const btn = e.target.closest("#btnModalInactivar");
    if (!btn) return;

    if (!currentId) {
      Swal.fire({ icon: "warning", title: "Registro no identificado", text: "No se pudo determinar el registro." });
      return;
    }

    const obs = String(byId("mObsTexto")?.value || "").trim();

    try {
      btn.disabled = true;

      if (currentKind === "residencia") {
        const confirm = await Swal.fire({
          icon: "warning",
          title: "Rechazar solicitud",
          text: "La solicitud de cambio de residencia será rechazada.",
          showCancelButton: true,
          confirmButtonText: "Sí, rechazar",
          cancelButtonText: "Cancelar",
          confirmButtonColor: "#DC2626",
        });

        if (!confirm.isConfirmed) return;

        const resp = await fetch(`${baseUrl}/api/soporte/residencias/${currentId}/estado`, {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-Partial": "1" },
          credentials: "include",
          body: JSON.stringify({ estado: "rechazada", comentario: obs }),
        });

        const json = await resp.json();
        if (!resp.ok || json.ok !== true) throw new Error(json.mensaje || "No se pudo rechazar la solicitud.");

        Swal.fire({ icon: "success", title: "Solicitud rechazada", timer: 1400, showConfirmButton: false });
      } else {
        const confirm = await Swal.fire({
          icon: "warning",
          title: "Inactivar cuenta",
          text: "La cuenta quedará inactiva y el usuario no podrá ingresar.",
          showCancelButton: true,
          confirmButtonText: "Sí, inactivar",
          cancelButtonText: "Cancelar",
          confirmButtonColor: "#DC2626",
        });

        if (!confirm.isConfirmed) return;

        const payload = { estado: 0 };
        if (obs) payload.observacion = obs;

        const resp = await fetch(`${baseUrl}/api/soporte/usuarios/${currentId}/estado`, {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-Partial": "1" },
          credentials: "include",
          body: JSON.stringify(payload),
        });

        const json = await resp.json();
        if (!resp.ok || json.ok !== true) throw new Error(json.mensaje || "No se pudo inactivar la cuenta.");

        Swal.fire({ icon: "success", title: "Cuenta inactivada", timer: 1400, showConfirmButton: false });
      }

      modalInstance.hide();

      const c = getControls();
      const q = (c.inpBuscar?.value || "").trim();
      if (q.length >= 3) {
        await load(makeGlobalSearchState(q));
      } else {
        await load(snapshotStateFromUI());
      }
    } catch (err) {
      Swal.fire({ icon: "error", title: "Error", text: err.message || "Error en la operación." });
    } finally {
      btn.disabled = false;
    }
  });

  document.addEventListener("click", async (e) => {
    const btn = e.target.closest("#btnModalObservar");
    if (!btn) return;

    if (!currentId) {
      Swal.fire({ icon: "warning", title: "Registro no identificado", text: "No se pudo determinar el registro." });
      return;
    }

    const obs = byId("mObsTexto")?.value || "";
    if (!obs.trim()) {
      Swal.fire({ icon: "warning", title: "Observación requerida", text: "Debes ingresar una observación." });
      return;
    }

    try {
      btn.disabled = true;

      let resp;
      if (currentKind === "residencia") {
        resp = await fetch(`${baseUrl}/api/soporte/residencias/${currentId}/estado`, {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-Partial": "1" },
          credentials: "include",
          body: JSON.stringify({ estado: "observada", comentario: obs }),
        });
      } else {
        resp = await fetch(`${baseUrl}/api/cuenta-observada/${currentId}/observar`, {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-Partial": "1" },
          credentials: "include",
          body: JSON.stringify({ observacion: obs }),
        });
      }

      const json = await resp.json();
      if (!resp.ok || json.ok !== true) throw new Error(json.mensaje || "No se pudo registrar la observación.");

      Swal.fire({ icon: "success", title: "Observación registrada", timer: 1500, showConfirmButton: false });
      modalInstance.hide();

      const c = getControls();
      const q = (c.inpBuscar?.value || "").trim();
      if (q.length >= 3) {
        await load(makeGlobalSearchState(q));
      } else {
        await load(snapshotStateFromUI());
      }
    } catch (err) {
      Swal.fire({ icon: "error", title: "Error", text: err.message || "Error al registrar observación." });
    } finally {
      btn.disabled = false;
    }
  });

  document.addEventListener("click", async (e) => {
    const btn = e.target.closest("#btnModalAprobar");
    if (!btn) return;

    if (!currentId) {
      Swal.fire({ icon: "warning", title: "Registro no identificado", text: "No se pudo determinar el registro." });
      return;
    }

    try {
      btn.disabled = true;

      let resp;
      if (currentKind === "residencia") {
        const confirm = await Swal.fire({
          icon: "question",
          title: "Aprobar cambio de residencia",
          text: "Se aprobará la nueva residencia solicitada por el usuario.",
          showCancelButton: true,
          confirmButtonText: "Sí, aprobar",
          cancelButtonText: "Cancelar",
          confirmButtonColor: "#EA7C12",
        });

        if (!confirm.isConfirmed) return;

        resp = await fetch(`${baseUrl}/api/soporte/residencias/${currentId}/estado`, {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-Partial": "1" },
          credentials: "include",
          body: JSON.stringify({ estado: "aprobada" }),
        });
      } else {
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

        resp = await fetch(`${baseUrl}/api/soporte/usuarios/${currentId}/estado`, {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-Partial": "1" },
          credentials: "include",
          body: JSON.stringify({ estado: 2 }),
        });
      }

      const json = await resp.json();
      if (!resp.ok || json.ok !== true) throw new Error(json.mensaje || "No se pudo aprobar.");

      Swal.fire({ icon: "success", title: "Operación exitosa", timer: 1400, showConfirmButton: false });
      modalInstance.hide();

      const c = getControls();
      const q = (c.inpBuscar?.value || "").trim();
      if (q.length >= 3) {
        await load(makeGlobalSearchState(q));
      } else {
        await load(snapshotStateFromUI());
      }
    } catch (err) {
      Swal.fire({ icon: "error", title: "Error", text: err.message || "Error al aprobar." });
    } finally {
      btn.disabled = false;
    }
  });

  function init() {
    if (!isAtenderCuentasView()) return false;

    const key = "atender-cuentas|" + window.location.pathname;
    if (key === lastInitKey) return true;
    lastInitKey = key;

    const c = getControls();
    wireConjuntoDependienteOnce();

    c.chips.forEach((chip) => {
      if (chip.dataset.evWired === "1") return;
      chip.dataset.evWired = "1";

      chip.addEventListener("click", () => {
        const q = (c.inpBuscar?.value || "").trim();
        if (q.length >= 3) {
          load(makeGlobalSearchState(q));
          return;
        }

        c.chips.forEach(x => x.classList.remove("ev-chip-active"));
        chip.classList.add("ev-chip-active");

        if (c.selEstado) c.selEstado.value = normalizarEstado(chip.dataset.estado);
        load(snapshotStateFromUI());
      });
    });

    if (c.inpBuscar && c.inpBuscar.dataset.evWired !== "1") {
      c.inpBuscar.dataset.evWired = "1";

      const onLiveSearch = debounce(() => {
        const txt = String(c.inpBuscar.value || "").trim();

        if (txt.length >= 3) {
          searchActive = true;
          load(makeGlobalSearchState(txt));
          return;
        }

        if (txt.length === 0) {
          searchActive = false;
          load(snapshotStateFromUI());
        }
      }, 260);

      c.inpBuscar.addEventListener("input", onLiveSearch);
    }

    if (c.btnAplicar && c.btnAplicar.dataset.evWired !== "1") {
      c.btnAplicar.dataset.evWired = "1";

      c.btnAplicar.addEventListener("click", () => {
        const q = (c.inpBuscar?.value || "").trim();
        if (q.length >= 3) {
          load(makeGlobalSearchState(q));
          return;
        }
        load(snapshotStateFromUI());
      });
    }

    if (c.btnLimpiar && c.btnLimpiar.dataset.evWired !== "1") {
      c.btnLimpiar.dataset.evWired = "1";

      c.btnLimpiar.addEventListener("click", () => {
        searchActive = false;

        if (c.inpBuscar) c.inpBuscar.value = "";
        if (c.selEstado) c.selEstado.value = "revision";
        if (c.selModo) c.selModo.value = "usuarios";
        if (c.selConjunto) c.selConjunto.value = "todos";

        if (c.selCondominio) {
          c.selCondominio.innerHTML = `<option value="">Selecciona...</option>`;
          c.selCondominio.disabled = true;
          c.selCondominio.value = "";
        }

        load(snapshotStateFromUI());
      });
    }

    const initialQ = (c.inpBuscar?.value || "").trim();
    if (initialQ.length >= 3) {
      searchActive = true;
      load(makeGlobalSearchState(initialQ));
    } else {
      load(snapshotStateFromUI());
    }

    return true;
  }

  function refresh() {
    const c = getControls();
    if (!isAtenderCuentasView()) return;
    const q = (c.inpBuscar?.value || "").trim();
    if (q.length >= 3) load(makeGlobalSearchState(q));
    else load(snapshotStateFromUI());
  }

  function bootObserver() {
    if (observer) return;
    observer = new MutationObserver(() => init());
    observer.observe(document.body, { childList: true, subtree: true });
  }

  window.EV_AtenderCuentasUsuario = {
    init,
    refresh
  };

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