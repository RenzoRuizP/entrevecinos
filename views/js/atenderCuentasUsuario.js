// views/js/atenderCuentasUsuario.js
// Módulo Admin: Atender cuentas + cambios de residencia
// FIX RAÍZ: modo "residencias" tolerante (residencias/solicitudes/cambios/residencia)
// + inyecta opción si no existe + payload backend robusto

(function () {
  "use strict";

  const NS = "__EV_ATENDER_CUENTAS__";

  if (!window[NS]) {
    window[NS] = { controller: null, bound: false };
  }
  const state = window[NS];

  function initAtenderCuentas() {
    try { if (state.controller) state.controller.abort(); } catch (_) {}
    state.controller = new AbortController();
    const { signal } = state.controller;

    const base = (window.BASE_URL || window.EV_BASE_URL || "").toString().replace(/\/+$/, "");
    const $ = (id) => document.getElementById(id);

    // refs
    const fModo = $("fModo");
    const fEstado = $("fEstado");
    const fTipo = $("fTipo");
    const fCodigo = $("fCodigo");
    const fQ = $("fQ");

    const btnRefrescar = $("btnRefrescar");
    const btnLimpiar = $("btnLimpiarFiltros");
    const btnModoResidencias = $("btnModoResidencias");
    const tbody = $("tbodyUsuarios");

    const metaInfo = $("metaInfo");
    const btnPrev = $("btnPrev");
    const btnNext = $("btnNext");
    const pageInfo = $("pageInfo");
    const lblPagina = $("lblPagina");
    const lblFooterLeft = $("lblFooterLeft");

    const tblTitle = $("tblTitle");

    // Modal
    const modalEl = $("modalUsuario");
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

    const mNombre = $("mNombre");
    const mEmail = $("mEmail");
    const mTelefono = $("mTelefono");
    const mDocumento = $("mDocumento");
    const mResidencia = $("mResidencia");
    const mEstadoBadge = $("mEstadoBadge");

    const mDocImg = $("mDocImg");
    const mDocPdf = $("mDocPdf");
    const mNoDoc = $("mNoDoc");
    const mDocLink = $("mDocLink");

    const wrapDecisionResidencia = $("wrapDecisionResidencia");
    const mComentarioResidencia = $("mComentarioResidencia");
    const wrapAccionesResidencia = $("wrapAccionesResidencia");
    const btnAprobarResidencia = $("btnAprobarResidencia");
    const btnObservarResidencia = $("btnObservarResidencia");
    const btnRechazarResidencia = $("btnRechazarResidencia");

    // Si no estamos en esta vista, salimos
    if (!fEstado || !fTipo || !fCodigo || !fQ || !tbody || !modal) return;

    // -----------------------------
    // ✅ Normalización de "Modo"
    // -----------------------------
    function normalizeModo(val) {
      const v = String(val || "").toLowerCase().trim();
      if (!v) return "usuarios";
      // Acepta múltiples variantes típicas
      if (v === "usuarios" || v === "user" || v === "cuentas") return "usuarios";
      if (v === "residencias" || v === "residencia" || v === "solicitudes" || v === "cambios" || v === "cambio_residencia") return "residencias";
      // Heurística: si contiene "resid" o "solic" => residencias
      if (v.includes("resid") || v.includes("solic")) return "residencias";
      return "usuarios";
    }

    function ensureModoOptionResidencias() {
      if (!fModo) return;
      const opts = Array.from(fModo.options || []);
      const has = opts.some(o => normalizeModo(o.value) === "residencias");
      if (has) return;

      // Inyectar opción (no rompe nada)
      const opt = document.createElement("option");
      opt.value = "residencias";
      opt.textContent = "Residencias";
      fModo.appendChild(opt);
    }

    ensureModoOptionResidencias();

    // local state
    let page = 1;
    const size = 10;
    let total = 0;
    let loading = false;

    const itemsById = new Map();
    let seleccionado = null;
    let modoActual = normalizeModo(fModo?.value || "usuarios");

    // helpers
    const buildURL = (p) => base + "/" + String(p || "").replace(/^\/+/, "");

    const escapeHtml = (s) =>
      String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");

    function swalInfo(msg) {
      if (window.Swal?.fire) return Swal.fire({ icon: "info", title: "Entre Vecinos", text: msg });
      alert(msg);
    }
    function swalOk(msg) {
      if (window.Swal?.fire) return Swal.fire({ icon: "success", title: "Listo", text: msg, timer: 1400, showConfirmButton: false });
      alert(msg);
    }
    function swalErr(msg) {
      if (window.Swal?.fire) return Swal.fire({ icon: "error", title: "Ocurrió un problema", text: msg });
      alert(msg);
    }

    const badgeEstadoUsuario = (v) => {
      const n = Number(v);
      if (n === 1) return `<span class="ev-badge ev-review"><i class="bi bi-hourglass-split"></i> En revisión</span>`;
      if (n === 2) return `<span class="ev-badge ev-ok"><i class="bi bi-check-circle"></i> Habilitado</span>`;
      return `<span class="ev-badge ev-off"><i class="bi bi-slash-circle"></i> Inactivo</span>`;
    };

    const badgeEstadoSolicitud = (estado) => {
      const e = String(estado || "pendiente").toLowerCase();
      const map = {
        pendiente: `<span class="ev-badge ev-review"><i class="bi bi-hourglass-split"></i> Pendiente</span>`,
        observada: `<span class="ev-badge ev-warn"><i class="bi bi-exclamation-circle"></i> Observada</span>`,
        aprobada: `<span class="ev-badge ev-ok"><i class="bi bi-check-circle"></i> Aprobada</span>`,
        rechazada: `<span class="ev-badge ev-off"><i class="bi bi-x-circle"></i> Rechazada</span>`,
      };
      return map[e] || map.pendiente;
    };

    function normalizeUrl(url) {
      const u = String(url || "").trim();
      if (!u) return "";
      if (/^https?:\/\//i.test(u)) return u;
      if (u.startsWith(base)) return u;
      if (u.startsWith("/")) return base + u;
      return base + "/" + u.replace(/^\/+/, "");
    }

    function pickDocUrl(row) {
      const candidates = [
        row?.comprobante_domicilio,
        row?.comprobante_url,
        row?.documento_url,
        row?.ruta_documento,
      ];
      const found = candidates.find((x) => String(x || "").trim().length > 0);
      return normalizeUrl(found || "");
    }

    function isPdf(url) {
      return /\.pdf(\?|#|$)/i.test(String(url || ""));
    }

    async function fetchJSON(url, opts = {}) {
      const res = await fetch(url, {
        method: opts.method || "GET",
        cache: "no-store",
        credentials: "include",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-Partial": "1",
          "Accept": "application/json",
          ...(opts.headers || {})
        },
        body: opts.body || null,
        signal
      });

      const ct = (res.headers.get("content-type") || "").toLowerCase();
      if (!ct.includes("application/json")) {
        const txt = await res.text().catch(() => "");
        throw new Error("Respuesta no JSON. " + (txt ? txt.slice(0, 160) : ""));
      }

      const data = await res.json().catch(() => null);
      if (!res.ok) {
        throw new Error(data?.mensaje || data?.message || data?.error || `HTTP ${res.status}`);
      }

      // Compatible con ok/success
      if (data && typeof data === "object") {
        if (data.ok === false || data.success === false) {
          throw new Error(data.mensaje || data.message || "Operación no exitosa.");
        }
      }
      return data;
    }

    // render helpers
    function renderSkeleton() {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="py-4">
            <div class="ev-empty-wrap">
              <i class="bi bi-arrow-repeat ev-empty-ico"></i>
              <div class="ev-empty-text">Cargando…</div>
            </div>
          </td>
        </tr>`;
    }

    function renderEmpty(msg) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="py-4">
            <div class="ev-empty-wrap">
              <i class="bi bi-inbox ev-empty-ico"></i>
              <div class="ev-empty-text">${escapeHtml(msg || "No hay registros para los filtros seleccionados.")}</div>
            </div>
          </td>
        </tr>`;
    }

    function updatePager(showing = 0) {
      const maxPage = Math.max(1, Math.ceil(total / size));
      if (pageInfo) pageInfo.textContent = `${page} / ${maxPage}`;
      if (lblPagina) lblPagina.textContent = String(page);

      if (btnPrev) btnPrev.disabled = page <= 1;
      if (btnNext) btnNext.disabled = page >= maxPage;

      if (metaInfo) metaInfo.textContent = String(total);
      if (lblFooterLeft) lblFooterLeft.textContent = `Mostrando ${showing} de ${total}`;
    }

    function resetDocPreview() {
      if (mDocImg) { mDocImg.src = ""; mDocImg.classList.add("d-none"); }
      if (mDocPdf) { mDocPdf.src = ""; mDocPdf.classList.add("d-none"); }
      if (mNoDoc) { mNoDoc.classList.remove("d-none"); }
      if (mDocLink) { mDocLink.classList.add("d-none"); mDocLink.setAttribute("href", "#"); }
    }

    function showResidenciaActions(show) {
      if (wrapDecisionResidencia) wrapDecisionResidencia.classList.toggle("d-none", !show);
      if (wrapAccionesResidencia) wrapAccionesResidencia.classList.toggle("d-none", !show);
      if (mComentarioResidencia) mComentarioResidencia.value = "";
    }

    function buildResidenciaText(row) {
      const dir = row?.direccion || row?.direccion_residencia || "—";
      if (row?.tipo_conjunto === "condominio") {
        const nom = row?.nombre_condominio || "—";
        return `Condominio: ${nom} | ${dir}`;
      }
      if (row?.tipo_conjunto === "urbanizacion") {
        const nom = row?.nombre_urbanizacion || "—";
        return `Urbanización: ${nom} | ${dir}`;
      }
      return dir;
    }

    function openModalWithRow(row) {
      if (!modal) return;

      if (mNombre) mNombre.textContent = row?.nombre || row?.nombre_completo || "—";
      if (mEmail) mEmail.textContent = row?.email || "—";
      if (mTelefono) mTelefono.textContent = row?.telefono || "—";
      if (mDocumento) mDocumento.textContent = row?.documento || "—";
      if (mResidencia) mResidencia.textContent = buildResidenciaText(row);

      if (modoActual === "residencias") {
        if (mEstadoBadge) mEstadoBadge.innerHTML = badgeEstadoSolicitud(row?.estado || "pendiente");
        showResidenciaActions(true);
      } else {
        const estado = Number(row?.estado || 0);
        if (mEstadoBadge) mEstadoBadge.innerHTML =
          (estado === 1) ? `<i class="bi bi-hourglass-split"></i> En revisión`
          : (estado === 2) ? `<i class="bi bi-check-circle"></i> Habilitado`
          : `<i class="bi bi-slash-circle"></i> Inactivo`;
        showResidenciaActions(false);
      }

      resetDocPreview();
      const docUrl = pickDocUrl(row);

      if (docUrl) {
        if (mNoDoc) mNoDoc.classList.add("d-none");
        if (isPdf(docUrl)) {
          if (mDocPdf) { mDocPdf.src = docUrl; mDocPdf.classList.remove("d-none"); }
        } else {
          if (mDocImg) { mDocImg.src = docUrl; mDocImg.classList.remove("d-none"); }
        }
        if (mDocLink) { mDocLink.href = docUrl; mDocLink.classList.remove("d-none"); }
      }

      modal.show();
    }

    async function cargarOpcionesConjunto() {
      const tipo = (fTipo.value || "").trim();
      fCodigo.innerHTML = `<option value="">Selecciona…</option>`;
      fCodigo.disabled = true;
      if (!tipo) return;

      const endpoint = tipo === "condominio" ? "condominios" : "urbanizaciones";
      const items = await fetchJSON(buildURL(endpoint));

      fCodigo.innerHTML =
        `<option value="">Todos</option>` +
        (Array.isArray(items) ? items : []).map((it) =>
          tipo === "condominio"
            ? `<option value="${it.codigo_condominio}">${escapeHtml(it.nombre_condominio)}</option>`
            : `<option value="${it.codigo_urbanizacion}">${escapeHtml(it.nombre_urbanizacion)}</option>`
        ).join("");

      fCodigo.disabled = false;
    }

    function buildListUrl() {
      const qs = new URLSearchParams({ page: String(page), size: String(size) });

      if (fEstado.value !== "all") qs.set("estado", fEstado.value);
      if (fTipo.value) qs.set("tipo", fTipo.value);
      if (fTipo.value && fCodigo.value) qs.set("codigo", fCodigo.value);
      if (fQ.value.trim()) qs.set("q", fQ.value.trim());

      // ✅ El endpoint depende del modo normalizado
      return (modoActual === "residencias")
        ? buildURL("api/soporte/residencias?" + qs.toString())
        : buildURL("api/soporte/usuarios?" + qs.toString());
    }

    function renderTabla(rows) {
      if (!rows.length) {
        renderEmpty(modoActual === "residencias"
          ? "No hay solicitudes de cambio de residencia para los filtros seleccionados."
          : "No hay usuarios para los filtros seleccionados."
        );
        return;
      }

      itemsById.clear();
      rows.forEach((r) => {
        const id = (modoActual === "residencias")
          ? Number(r.codigo_solicitud || 0)
          : Number(r.codigo_usuario || 0);
        if (id > 0) itemsById.set(id, r);
      });

      tbody.innerHTML = rows.map((r) => {
        if (modoActual === "residencias") {
          const id = Number(r.codigo_solicitud || 0);
          const residencia = (r.tipo_conjunto === "condominio")
            ? `<strong>Condominio:</strong> ${escapeHtml(r.nombre_condominio || "—")}<br>${escapeHtml(r.direccion || "—")}`
            : `<strong>Urbanización:</strong> ${escapeHtml(r.nombre_urbanizacion || "—")}<br>${escapeHtml(r.direccion || "—")}`;

          return `
            <tr>
              <td>
                <strong>${escapeHtml(r.nombre || "—")}</strong><br>
                <small class="text-muted">Doc: ${escapeHtml(r.documento || "—")}</small>
              </td>
              <td>
                ${escapeHtml(r.email || "—")}<br>
                <small class="text-muted">Tel: ${escapeHtml(r.telefono || "—")}</small>
              </td>
              <td>${residencia}</td>
              <td>${badgeEstadoSolicitud(r.estado)}</td>
              <td class="text-end">
                <button class="btn ev-btn-light btn-sm btnVer" data-id="${id}" type="button">
                  <i class="bi bi-eye me-1"></i> Ver
                </button>
              </td>
            </tr>`;
        }

        // usuarios
        const id = Number(r.codigo_usuario || 0);
        const estado = Number(r.estado);
        const puedeHabilitar = estado === 1;

        const residencia =
          r.tipo_conjunto === "condominio"
            ? `<strong>Condominio:</strong> ${escapeHtml(r.nombre_condominio || "—")}<br>${escapeHtml(r.direccion || r.direccion_residencia || "—")}`
            : `<strong>Urbanización:</strong> ${escapeHtml(r.nombre_urbanizacion || "—")}<br>${escapeHtml(r.direccion || r.direccion_residencia || "—")}`;

        const habBtn = puedeHabilitar
          ? `<button class="btn ev-btn-orange btn-sm btnHabilitar" data-id="${id}" type="button">
               <i class="bi bi-check2-circle me-1"></i> Habilitar
             </button>`
          : `<button class="btn btn-outline-secondary btn-sm" type="button" disabled>
               <i class="bi bi-lock"></i>
             </button>`;

        return `
          <tr>
            <td>
              <strong>${escapeHtml(r.nombre || "—")}</strong><br>
              <small class="text-muted">Doc: ${escapeHtml(r.documento || "—")}</small>
            </td>
            <td>
              ${escapeHtml(r.email || "—")}<br>
              <small class="text-muted">Tel: ${escapeHtml(r.telefono || "—")}</small>
            </td>
            <td>${residencia}</td>
            <td>${badgeEstadoUsuario(estado)}</td>
            <td class="text-end">
              <div class="d-inline-flex gap-2">
                <button class="btn ev-btn-light btn-sm btnVer" data-id="${id}" type="button">
                  <i class="bi bi-eye me-1"></i> Ver
                </button>
                ${habBtn}
              </div>
            </td>
          </tr>`;
      }).join("");
    }

    function parseRowsAndMeta(res) {
      if (res && typeof res === "object" && Array.isArray(res.data)) {
        return { rows: res.data, total: Number(res.meta?.total || 0) };
      }
      if (res && typeof res === "object") {
        const alt = res.solicitudes || res.items || res.rows || res.usuarios || null;
        if (Array.isArray(alt)) return { rows: alt, total: Number(res.total || res.meta?.total || alt.length || 0) };
      }
      if (Array.isArray(res)) return { rows: res, total: res.length };
      return { rows: [], total: 0 };
    }

    async function cargarLista() {
      if (loading) return;
      loading = true;
      renderSkeleton();

      try {
        const res = await fetchJSON(buildListUrl());
        const parsed = parseRowsAndMeta(res);
        const rows = parsed.rows;
        total = parsed.total;

        if (tblTitle) tblTitle.textContent = (modoActual === "residencias") ? "Cambios de residencia" : "Usuarios";

        updatePager(rows.length);
        renderTabla(rows);
      } catch (e) {
        if (String(e?.name || "").toLowerCase() === "aborterror") return;
        console.error("[EV][ATENDER_CUENTAS]", e);
        renderEmpty("Error al cargar datos.");
        total = 0;
        updatePager(0);
      } finally {
        loading = false;
      }
    }

    async function habilitarUsuario(id, btn) {
      btn.disabled = true;
      const original = btn.innerHTML;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;

      try {
        await fetchJSON(buildURL(`api/soporte/usuarios/${id}/estado`), {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ estado: 2 })
        });

        await swalOk("Cuenta habilitada.");
        cargarLista();
      } catch (e) {
        await swalErr(e.message || "No se pudo habilitar.");
        btn.disabled = false;
        btn.innerHTML = original;
      }
    }

    async function actualizarSolicitudResidencia(nuevoEstado) {
      if (!seleccionado?.codigo_solicitud) return;

      const id = Number(seleccionado.codigo_solicitud);

      // ✅ este input se llama "mComentarioResidencia" y el backend acepta comentario o comentario_admin
      const comentario = (mComentarioResidencia?.value || "").trim();

      if ((nuevoEstado === "observada" || nuevoEstado === "rechazada") && comentario.length < 3) {
        swalInfo("Debes ingresar un comentario para Observada o Rechazada.");
        return;
      }

      const url = buildURL(`api/soporte/residencias/${id}/estado`);

      try {
        const resp = await fetchJSON(url, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ estado: nuevoEstado, comentario })
        });

        await swalOk(resp.mensaje || "Estado actualizado.");
        modal.hide();
        cargarLista();
      } catch (e) {
        swalErr(e.message || "No se pudo actualizar la solicitud.");
      }
    }

    // eventos
    tbody.addEventListener("click", (e) => {
      const ver = e.target.closest(".btnVer");
      if (ver) {
        const id = Number(ver.dataset.id || 0);
        const row = itemsById.get(id);
        if (row) {
          seleccionado = row;
          openModalWithRow(row);
        }
        return;
      }

      const hab = e.target.closest(".btnHabilitar");
      if (hab && modoActual === "usuarios") {
        const id = Number(hab.dataset.id || 0);
        if (id > 0) habilitarUsuario(id, hab);
      }
    }, { signal });

    btnAprobarResidencia?.addEventListener("click", () => actualizarSolicitudResidencia("aprobada"), { signal });
    btnObservarResidencia?.addEventListener("click", () => actualizarSolicitudResidencia("observada"), { signal });
    btnRechazarResidencia?.addEventListener("click", () => actualizarSolicitudResidencia("rechazada"), { signal });

    fModo?.addEventListener("change", async () => {
      modoActual = normalizeModo(fModo.value);
      page = 1;

      if (modoActual === "residencias") {
        fEstado.innerHTML = `
          <option value="pendiente" selected>Pendiente</option>
          <option value="observada">Observada</option>
          <option value="aprobada">Aprobada</option>
          <option value="rechazada">Rechazada</option>
          <option value="all">Todos</option>`;
      } else {
        fEstado.innerHTML = `
          <option value="1" selected>En revisión</option>
          <option value="2">Habilitado</option>
          <option value="0">Inactivo</option>
          <option value="all">Todos</option>`;
      }

      await cargarOpcionesConjunto();
      cargarLista();
    }, { signal });

    btnModoResidencias?.addEventListener("click", () => {
      if (!fModo) return;

      // buscar una opción existente que normalice a residencias
      const opts = Array.from(fModo.options || []);
      const opt = opts.find(o => normalizeModo(o.value) === "residencias") || null;

      fModo.value = opt ? opt.value : "residencias";
      fModo.dispatchEvent(new Event("change", { bubbles: true }));
    }, { signal });

    fTipo.addEventListener("change", async () => {
      page = 1;
      await cargarOpcionesConjunto();
      cargarLista();
    }, { signal });

    [fEstado, fCodigo].forEach((el) =>
      el.addEventListener("change", () => {
        page = 1;
        cargarLista();
      }, { signal })
    );

    let t = null;
    fQ.addEventListener("input", () => {
      clearTimeout(t);
      t = setTimeout(() => {
        page = 1;
        cargarLista();
      }, 350);
    }, { signal });

    btnRefrescar?.addEventListener("click", () => cargarLista(), { signal });

    btnLimpiar?.addEventListener("click", async () => {
      if (fModo) {
        // volver a usuarios (usando el primer value que normalice a usuarios)
        const opts = Array.from(fModo.options || []);
        const optU = opts.find(o => normalizeModo(o.value) === "usuarios") || null;
        fModo.value = optU ? optU.value : "usuarios";
        modoActual = "usuarios";
      }

      fEstado.innerHTML = `
        <option value="1" selected>En revisión</option>
        <option value="2">Habilitado</option>
        <option value="0">Inactivo</option>
        <option value="all">Todos</option>`;

      fTipo.value = "";
      fQ.value = "";
      fCodigo.innerHTML = `<option value="">Selecciona…</option>`;
      fCodigo.disabled = true;
      page = 1;
      cargarLista();
    }, { signal });

    btnPrev?.addEventListener("click", () => {
      if (page > 1) { page--; cargarLista(); }
    }, { signal });

    btnNext?.addEventListener("click", () => {
      const max = Math.ceil(total / size);
      if (page < max) { page++; cargarLista(); }
    }, { signal });

    // Init
    modoActual = normalizeModo(fModo?.value || "usuarios");
    if (modoActual === "residencias") {
      fEstado.innerHTML = `
        <option value="pendiente" selected>Pendiente</option>
        <option value="observada">Observada</option>
        <option value="aprobada">Aprobada</option>
        <option value="rechazada">Rechazada</option>
        <option value="all">Todos</option>`;
    }

    cargarLista();
  }

  if (!state.bound) {
    state.bound = true;
    document.addEventListener("DOMContentLoaded", initAtenderCuentas);
    document.addEventListener("ev:content-loaded", initAtenderCuentas);
  } else {
    initAtenderCuentas();
  }
})();
