// views/js/atenderCuentasUsuario.js
(function () {
  "use strict";

  if (window.__EV_ATENDER_CUENTAS_INIT__) return;
  window.__EV_ATENDER_CUENTAS_INIT__ = true;

  const base = (window.BASE_URL || window.EV_BASE_URL || "").toString().replace(/\/+$/, "");

  const $ = (id) => document.getElementById(id);

  const fEstado = $("fEstado");
  const fTipo   = $("fTipo");
  const fCodigo = $("fCodigo");
  const fQ      = $("fQ");

  const btnRefrescar = $("btnRefrescar");
  const btnLimpiar   = $("btnLimpiarFiltros");
  const tbody        = $("tbodyUsuarios");

  const metaInfo = $("metaInfo");
  const btnPrev  = $("btnPrev");
  const btnNext  = $("btnNext");
  const pageInfo = $("pageInfo");
  const lblPagina = $("lblPagina");
  const lblFooterLeft = $("lblFooterLeft");

  if (!fEstado || !fTipo || !fCodigo || !fQ || !tbody) {
    window.__EV_ATENDER_CUENTAS_INIT__ = false;
    return;
  }

  let page = 1;
  const size = 10;
  let total = 0;
  let loading = false;

  /* ----------------- helpers ----------------- */

  const buildURL = (p) => base + "/" + String(p || "").replace(/^\/+/, "");

  const escapeHtml = (s) =>
    String(s ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const badgeEstado = (v) => {
    const n = Number(v);
    if (n === 1) return `<span class="ev-badge ev-review"><i class="bi bi-hourglass-split"></i> En revisión</span>`;
    if (n === 2) return `<span class="ev-badge ev-ok"><i class="bi bi-check-circle"></i> Habilitado</span>`;
    return `<span class="ev-badge ev-off"><i class="bi bi-slash-circle"></i> Inactivo</span>`;
  };

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
            <div class="ev-empty-text">Cargando usuarios…</div>
          </div>
        </td>
      </tr>`;
  }

  function renderEmpty() {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="py-4">
          <div class="ev-empty-wrap">
            <i class="bi bi-people ev-empty-ico"></i>
            <div class="ev-empty-text">No hay usuarios para los filtros seleccionados.</div>
          </div>
        </td>
      </tr>`;
  }

  function updatePager() {
    const maxPage = Math.max(1, Math.ceil(total / size));
    pageInfo.textContent = `${page} / ${maxPage}`;
    lblPagina.textContent = String(page);

    btnPrev.disabled = page <= 1;
    btnNext.disabled = page >= maxPage;

    metaInfo.textContent = `Total: ${total}`;
    lblFooterLeft.textContent = `Mostrando ${Math.min(size, total)} de ${total}`;
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
      items.map((it) =>
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

    if (fEstado.value !== "all") qs.set("estado", fEstado.value);
    if (fTipo.value) qs.set("tipo", fTipo.value);
    if (fTipo.value && fCodigo.value) qs.set("codigo", fCodigo.value);
    if (fQ.value.trim()) qs.set("q", fQ.value.trim());

    return buildURL("api/soporte/usuarios?" + qs.toString());
  }

  async function cargarLista() {
    if (loading) return;
    loading = true;
    renderSkeleton();

    try {
      const res = await fetchJSON(buildListUrl());
      const rows = Array.isArray(res.data) ? res.data : [];
      total = Number(res.meta?.total || 0);

      updatePager();

      if (!rows.length) {
        renderEmpty();
        return;
      }

      tbody.innerHTML = rows.map((u) => {
        const estado = Number(u.estado);
        const puedeHabilitar = estado === 1;

        const residencia =
          u.tipo_conjunto === "condominio"
            ? `<strong>Condominio:</strong> ${escapeHtml(u.nombre_condominio)}<br>${escapeHtml(u.direccion_residencia)}`
            : `<strong>Urbanización:</strong> ${escapeHtml(u.nombre_urbanizacion)}<br>${escapeHtml(u.direccion_residencia)}`;

        const btn = puedeHabilitar
          ? `<button class="btn ev-btn-orange btn-sm btnHabilitar" data-id="${u.codigo_usuario}">
               <i class="bi bi-check2-circle me-1"></i> Habilitar
             </button>`
          : `<button class="btn btn-outline-secondary btn-sm" disabled>
               <i class="bi bi-lock"></i>
             </button>`;

        return `
          <tr>
            <td>
              <strong>${escapeHtml(u.nombre)}</strong><br>
              <small class="text-muted">Doc: ${escapeHtml(u.documento)}</small>
            </td>
            <td>
              ${escapeHtml(u.email)}<br>
              <small class="text-muted">Tel: ${escapeHtml(u.telefono)}</small>
            </td>
            <td>${residencia}</td>
            <td>${badgeEstado(estado)}</td>
            <td class="text-end">${btn}</td>
          </tr>`;
      }).join("");

    } catch (e) {
      console.error("[EV][ATENDER_CUENTAS]", e);
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="text-danger text-center py-4">
            Error al cargar usuarios
          </td>
        </tr>`;
    } finally {
      loading = false;
    }
  }

  async function habilitarUsuario(id, btn) {
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;

    try {
      await fetchJSON(buildURL(`api/soporte/usuarios/${id}/estado`), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ estado: 2 })
      });

      await Swal.fire({
        icon: "success",
        title: "Cuenta habilitada",
        confirmButtonColor: "#16A34A"
      });

      cargarLista();

    } catch (e) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: e.message,
        confirmButtonColor: "#EA7C12"
      });
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Habilitar`;
    }
  }

  /* ----------------- eventos ----------------- */

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
    fEstado.value = "1";
    fTipo.value = "";
    fQ.value = "";
    fCodigo.innerHTML = `<option value="">Selecciona…</option>`;
    fCodigo.disabled = true;
    page = 1;
    cargarLista();
  });

  btnPrev?.addEventListener("click", () => {
    if (page > 1) {
      page--;
      cargarLista();
    }
  });

  btnNext?.addEventListener("click", () => {
    const max = Math.ceil(total / size);
    if (page < max) {
      page++;
      cargarLista();
    }
  });

  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".btnHabilitar");
    if (!btn) return;
    const id = Number(btn.dataset.id);
    if (id > 0) habilitarUsuario(id, btn);
  });

  cargarLista();

})();
