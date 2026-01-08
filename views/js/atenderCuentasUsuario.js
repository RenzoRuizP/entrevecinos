// views/js/atenderCuentasUsuario.js
(function () {
  "use strict";

  if (window.__EV_ATENDER_CUENTAS_INIT__) return;
  window.__EV_ATENDER_CUENTAS_INIT__ = true;

  const base = (window.BASE_URL || window.EV_BASE_URL || "").toString().replace(/\/+$/, "");
  const $ = (id) => document.getElementById(id);

  // filtros
  const fModo  = $("fModo");        // NUEVO
  const fEstado = $("fEstado");
  const fTipo   = $("fTipo");
  const fCodigo = $("fCodigo");
  const fQ      = $("fQ");

  const btnRefrescar = $("btnRefrescar");
  const btnLimpiar   = $("btnLimpiarFiltros");
  const btnModoResidencias = $("btnModoResidencias"); // NUEVO (hero shortcut)
  const tbody        = $("tbodyUsuarios");

  const metaInfo = $("metaInfo");
  const btnPrev  = $("btnPrev");
  const btnNext  = $("btnNext");
  const pageInfo = $("pageInfo");
  const lblPagina = $("lblPagina");
  const lblFooterLeft = $("lblFooterLeft");

  // Modal refs
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
  const mNoDoc  = $("mNoDoc");
  const mDocLink = $("mDocLink");

  // Residencia: acciones admin
  const wrapDecisionResidencia = $("wrapDecisionResidencia");
  const mComentarioResidencia = $("mComentarioResidencia");
  const wrapAccionesResidencia = $("wrapAccionesResidencia");
  const btnAprobarResidencia = $("btnAprobarResidencia");
  const btnObservarResidencia = $("btnObservarResidencia");
  const btnRechazarResidencia = $("btnRechazarResidencia");

  if (!fEstado || !fTipo || !fCodigo || !fQ || !tbody || !modal) {
    window.__EV_ATENDER_CUENTAS_INIT__ = false;
    return;
  }

  if (!fModo) {
    // fallback: si no agregaste el select, modo usuarios por defecto
    console.warn("[EV][ATENDER_CUENTAS] fModo no existe; se usará 'usuarios'.");
  }

  let page = 1;
  const size = 10;
  let total = 0;
  let loading = false;

  // Cache para abrir modal sin pedir otro endpoint
  const itemsById = new Map(); // usuarios: codigo_usuario / residencias: codigo_solicitud
  let seleccionado = null;
  let modoActual = (fModo?.value || "usuarios");

  /* ----------------- helpers ----------------- */
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
      body: opts.body || null
    });

    const ct = (res.headers.get("content-type") || "").toLowerCase();
    if (!ct.includes("application/json")) {
      const txt = await res.text().catch(() => "");
      throw new Error("Respuesta no JSON. " + (txt ? txt.slice(0, 120) : ""));
    }

    const data = await res.json().catch(() => null);
    if (!res.ok || data?.ok === false) {
      throw new Error(data?.mensaje || data?.message || `HTTP ${res.status}`);
    }
    return data;
  }

  /* ----------------- render helpers ----------------- */
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
    pageInfo.textContent = `${page} / ${maxPage}`;
    lblPagina.textContent = String(page);

    btnPrev.disabled = page <= 1;
    btnNext.disabled = page >= maxPage;

    metaInfo.textContent = String(total);
    lblFooterLeft.textContent = `Mostrando ${showing} de ${total}`;
  }

  function resetDocPreview() {
    if (mDocImg) { mDocImg.src = ""; mDocImg.classList.add("d-none"); }
    if (mDocPdf) { mDocPdf.src = ""; mDocPdf.classList.add("d-none"); }
    if (mNoDoc)  { mNoDoc.classList.remove("d-none"); }
    if (mDocLink){ mDocLink.classList.add("d-none"); mDocLink.setAttribute("href", "#"); }
  }

  function showResidenciaActions(show) {
    if (wrapDecisionResidencia) wrapDecisionResidencia.classList.toggle("d-none", !show);
    if (wrapAccionesResidencia) wrapAccionesResidencia.classList.toggle("d-none", !show);
    if (mComentarioResidencia) mComentarioResidencia.value = "";
  }

  function buildResidenciaText(row) {
    // sirve tanto para usuario como para solicitud
    const dir = row?.direccion_residencia || row?.direccion || "—";

    if (row?.tipo_conjunto === "condominio") {
      const nom = row?.nombre_condominio || "—";
      const dpto = row?.codigo_departamento ? ` | Dpto ${row.codigo_departamento}` : "";
      return `Condominio: ${nom}${dpto} | ${dir}`;
    }
    if (row?.tipo_conjunto === "urbanizacion") {
      const nom = row?.nombre_urbanizacion || "—";
      return `Urbanización: ${nom} | ${dir}`;
    }
    return dir;
  }

  function openModalWithRow(row) {
    if (!modal) return;

    // Datos básicos (vienen en ambos listados)
    if (mNombre) mNombre.textContent = row?.nombre || "—";
    if (mEmail) mEmail.textContent = row?.email || "—";
    if (mTelefono) mTelefono.textContent = row?.telefono || "—";
    if (mDocumento) mDocumento.textContent = row?.documento || "—";
    if (mResidencia) mResidencia.textContent = buildResidenciaText(row);

    // Estado badge
    if (modoActual === "residencias") {
      if (mEstadoBadge) {
        mEstadoBadge.className = "ev-badge ev-review";
        mEstadoBadge.innerHTML = badgeEstadoSolicitud(row?.estado || "pendiente");
      }
      showResidenciaActions(true);
    } else {
      const estado = Number(row?.estado || 0);
      if (mEstadoBadge) {
        mEstadoBadge.className = "ev-badge " + (estado === 1 ? "ev-review" : (estado === 2 ? "ev-ok" : "ev-off"));
        mEstadoBadge.innerHTML = (estado === 1)
          ? `<i class="bi bi-hourglass-split"></i> En revisión`
          : (estado === 2)
            ? `<i class="bi bi-check-circle"></i> Habilitado`
            : `<i class="bi bi-slash-circle"></i> Inactivo`;
      }
      showResidenciaActions(false);
    }

    // Documento
    resetDocPreview();
    const docUrl = pickDocUrl(row);

    if (docUrl) {
      if (mNoDoc) mNoDoc.classList.add("d-none");

      if (isPdf(docUrl)) {
        if (mDocPdf) {
          mDocPdf.src = docUrl;
          mDocPdf.classList.remove("d-none");
        }
      } else {
        if (mDocImg) {
          mDocImg.src = docUrl;
          mDocImg.classList.remove("d-none");
        }
      }

      if (mDocLink) {
        mDocLink.href = docUrl;
        mDocLink.classList.remove("d-none");
      }
    }

    modal.show();
  }

  /* ----------------- data loaders ----------------- */
  async function cargarOpcionesConjunto() {
    const tipo = fTipo.value.trim();
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
    const qs = new URLSearchParams({
      page: String(page),
      size: String(size),
    });

    // Estado: para usuarios es 0/1/2/all; para residencias usaremos: pendiente/observada/aprobada/rechazada/all
    if (fEstado.value !== "all") qs.set("estado", fEstado.value);
    if (fTipo.value) qs.set("tipo", fTipo.value);
    if (fTipo.value && fCodigo.value) qs.set("codigo", fCodigo.value);
    if (fQ.value.trim()) qs.set("q", fQ.value.trim());

    if (modoActual === "residencias") {
      return buildURL("api/soporte/residencias?" + qs.toString());
    }
    return buildURL("api/soporte/usuarios?" + qs.toString());
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
      const id = modoActual === "residencias" ? Number(r.codigo_solicitud || 0) : Number(r.codigo_usuario || 0);
      if (id > 0) itemsById.set(id, r);
    });

    tbody.innerHTML = rows.map((r) => {
      if (modoActual === "residencias") {
        const id = Number(r.codigo_solicitud || 0);

        const residencia = (r.tipo_conjunto === "condominio")
          ? `<strong>Condominio:</strong> ${escapeHtml(r.nombre_condominio || "—")}<br>${escapeHtml(r.direccion || "—")}`
          : `<strong>Urbanización:</strong> ${escapeHtml(r.nombre_urbanizacion || "—")}<br>${escapeHtml(r.direccion || "—")}`;

        const verBtn = `
          <button class="btn ev-btn-light btn-sm btnVer" data-id="${id}" type="button">
            <i class="bi bi-eye me-1"></i> Ver
          </button>`;

        const badge = badgeEstadoSolicitud(r.estado);

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
            <td>${badge}</td>
            <td class="text-end">
              <div class="d-inline-flex gap-2">
                ${verBtn}
              </div>
            </td>
          </tr>`;
      }

      // Modo usuarios (tu lógica original)
      const id = Number(r.codigo_usuario || 0);
      const estado = Number(r.estado);
      const puedeHabilitar = estado === 1;

      const residencia =
        r.tipo_conjunto === "condominio"
          ? `<strong>Condominio:</strong> ${escapeHtml(r.nombre_condominio || "—")}<br>${escapeHtml(r.direccion_residencia || "—")}`
          : `<strong>Urbanización:</strong> ${escapeHtml(r.nombre_urbanizacion || "—")}<br>${escapeHtml(r.direccion_residencia || "—")}`;

      const verBtn = `
        <button class="btn ev-btn-light btn-sm btnVer" data-id="${id}" type="button">
          <i class="bi bi-eye me-1"></i> Ver
        </button>`;

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
              ${verBtn}
              ${habBtn}
            </div>
          </td>
        </tr>`;
    }).join("");
  }

  async function cargarLista() {
    if (loading) return;
    loading = true;
    renderSkeleton();

    try {
      const res = await fetchJSON(buildListUrl());
      const rows = Array.isArray(res.data) ? res.data : [];
      total = Number(res.meta?.total || 0);

      updatePager(rows.length);
      renderTabla(rows);
    } catch (e) {
      console.error("[EV][ATENDER_CUENTAS]", e);
      renderEmpty("Error al cargar datos.");
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

  /* ----------------- eventos ----------------- */

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

    // solo modo usuarios
    const hab = e.target.closest(".btnHabilitar");
    if (hab && modoActual === "usuarios") {
      const id = Number(hab.dataset.id || 0);
      if (id > 0) habilitarUsuario(id, hab);
    }
  });

  // Acciones residencia en modal
  btnAprobarResidencia?.addEventListener("click", () => actualizarSolicitudResidencia("aprobada"));
  btnObservarResidencia?.addEventListener("click", () => actualizarSolicitudResidencia("observada"));
  btnRechazarResidencia?.addEventListener("click", () => actualizarSolicitudResidencia("rechazada"));

  // Modo select
  fModo?.addEventListener("change", async () => {
    modoActual = fModo.value || "usuarios";
    page = 1;

    // Ajustar estados disponibles según modo sin romper nada
    if (modoActual === "residencias") {
      // reemplaza opciones de estado (sin depender del HTML original)
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
  });

  // Shortcut hero “Cambios de residencia”
  btnModoResidencias?.addEventListener("click", () => {
    if (!fModo) return;
    fModo.value = "residencias";
    fModo.dispatchEvent(new Event("change", { bubbles: true }));
  });

  fTipo.addEventListener("change", async () => {
    page = 1;
    await cargarOpcionesConjunto();
    cargarLista();
  });

  [fEstado, fCodigo].forEach((el) =>
    el.addEventListener("change", () => {
      page = 1;
      cargarLista();
    })
  );

  let t = null;
  fQ.addEventListener("input", () => {
    clearTimeout(t);
    t = setTimeout(() => {
      page = 1;
      cargarLista();
    }, 350);
  });

  btnRefrescar?.addEventListener("click", () => cargarLista());

  btnLimpiar?.addEventListener("click", async () => {
    // reset modo
    if (fModo) {
      fModo.value = "usuarios";
      modoActual = "usuarios";
    }

    // reset estado a usuarios por defecto
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
  });

  btnPrev?.addEventListener("click", () => {
    if (page > 1) { page--; cargarLista(); }
  });

  btnNext?.addEventListener("click", () => {
    const max = Math.ceil(total / size);
    if (page < max) { page++; cargarLista(); }
  });

  // Init (modo inicial)
  modoActual = (fModo?.value || "usuarios");
  if (modoActual === "residencias") {
    fEstado.innerHTML = `
      <option value="pendiente" selected>Pendiente</option>
      <option value="observada">Observada</option>
      <option value="aprobada">Aprobada</option>
      <option value="rechazada">Rechazada</option>
      <option value="all">Todos</option>`;
  }
  cargarLista();

})();
