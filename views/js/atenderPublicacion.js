// views/js/atenderPublicacion.js
(function () {
  "use strict";

  // ✅ Blindaje: si NO estamos en la vista At. Publicación, no inicializar
  // (usa un ancla real de tu vista; aquí lo más crítico es tbodyItems)
  const $ = (s) => document.querySelector(s);
  const tbody = $("#tbodyItems");
  if (!tbody) return;

  // ===== BASE robusto (compatibilidad con tu app) =====
  const rawBase =
    (window.BASE_URL || window.EV_BASE_URL || "/entrevecinos").toString().trim();

  const BASE = rawBase.replace(/\/+$/, ""); // sin slash final

  // Endpoints
  const API_LIST = `${BASE}/api/soporte/productos`;
  const API_DET = (id) => `${BASE}/api/soporte/productos/${id}`;
  const API_ESTADO = (id) => `${BASE}/api/soporte/productos/${id}/estado`;

  // Otros refs (opcionales)
  const lblMeta = $("#lblMeta");
  const lblPend = $("#lblPendientes");
  const lblFooterLeft = $("#lblFooterLeft");
  const lblPagina = $("#lblPagina");
  const btnPrev = $("#btnPrev");
  const btnNext = $("#btnNext");

  const form = $("#formFiltros");
  const fEstado = $("#fEstado");
  const fTexto = $("#fTexto");

  const modalEl = $("#modalPub");
  const modal = (modalEl && window.bootstrap) ? new bootstrap.Modal(modalEl) : null;

  const mTitulo = $("#mTitulo");
  const mPrecio = $("#mPrecio");
  const mEstadoBadge = $("#mEstadoBadge");
  const mUsuario = $("#mUsuario");
  const mEmail = $("#mEmail");
  const mDescripcion = $("#mDescripcion");
  const mComentario = $("#mComentario");
  const mGaleria = $("#mGaleria");
  const mNoImgs = $("#mNoImgs");

  const btnAprobar = $("#btnAprobar");
  const btnRechazar = $("#btnRechazar");

  let state = {
    page: 1,
    size: 10,
    total: 0,
    currentId: null,
  };

  const badgeClass = (visible) => {
    // 0 borrador, 1 pendiente, 2 aprobada, 3 rechazada
    if (visible === 2) return ["aprobada", "ev-badge-aprobada"];
    if (visible === 3) return ["rechazada", "ev-badge-rechazada"];
    if (visible === 0) return ["borrador", "ev-badge-borrador"];
    return ["pendiente", "ev-badge-pendiente"];
  };

  const money = (n) => {
    const v = Number(n || 0);
    return v.toLocaleString("es-PE", { style: "currency", currency: "PEN" });
  };

  const fmtFecha = (iso) => {
    if (!iso) return "—";
    const d = new Date(String(iso).replace(" ", "T"));
    if (isNaN(d.getTime())) return iso;
    return d.toLocaleString("es-PE");
  };

  function escapeHtml(str) {
    return String(str ?? "").replace(/[&<>"']/g, (m) => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    }[m]));
  }

  function renderEmpty() {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center py-4 ev-empty">
          <div class="ev-empty-wrap">
            <i class="bi bi-inbox ev-empty-ico"></i>
            <div class="ev-empty-text">No hay publicaciones para los filtros seleccionados.</div>
          </div>
        </td>
      </tr>
    `;
  }

  // ===== API helpers (exigir JSON) =====
  async function apiGet(url) {
    const r = await fetch(url, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "X-Partial": "1",
        "Accept": "application/json",
      },
      credentials: "include",
    });

    const ct = (r.headers.get("content-type") || "").toLowerCase();

    if (!ct.includes("application/json")) {
      const txt = await r.text().catch(() => "");
      const snippet = txt ? txt.slice(0, 180) : "";
      throw new Error(
        `La API no devolvió JSON (Content-Type: ${ct || "N/A"}). ` +
        `Probable BASE_URL mal armado o respuesta HTML. ` +
        (snippet ? `Snippet: ${snippet}` : "")
      );
    }

    const data = await r.json().catch(() => null);

    if (!r.ok) {
      const msg =
        (data && (data.mensaje || data.message || data.error))
          ? (data.mensaje || data.message || data.error)
          : `HTTP ${r.status}`;
      throw new Error(msg);
    }

    if (data && data.ok === false) {
      const msg = data.mensaje || data.message || data.error || "Error de API";
      throw new Error(msg);
    }

    return data;
  }

  async function apiPost(url, bodyObj) {
    const fd = new FormData();
    Object.entries(bodyObj || {}).forEach(([k, v]) => fd.append(k, v));

    const r = await fetch(url, {
      method: "POST",
      body: fd,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "X-Partial": "1",
        "Accept": "application/json",
      },
      credentials: "include",
    });

    const ct = (r.headers.get("content-type") || "").toLowerCase();

    if (!ct.includes("application/json")) {
      const txt = await r.text().catch(() => "");
      const snippet = txt ? txt.slice(0, 180) : "";
      throw new Error(
        `La API no devolvió JSON (Content-Type: ${ct || "N/A"}). ` +
        (snippet ? `Snippet: ${snippet}` : "")
      );
    }

    const data = await r.json().catch(() => null);

    if (!r.ok || !data || data.ok === false) {
      const msg =
        (data && (data.mensaje || data.message || data.error))
          ? (data.mensaje || data.message || data.error)
          : `HTTP ${r.status}`;
      throw new Error(msg);
    }

    return data;
  }

  function buildListUrl() {
    const params = new URLSearchParams();
    params.set("estado", ((fEstado && fEstado.value) ? fEstado.value : "pendiente").toLowerCase());
    params.set("q", (fTexto && fTexto.value ? fTexto.value : "").trim());
    params.set("page", String(state.page));
    params.set("size", String(state.size));
    return `${API_LIST}?${params.toString()}`;
  }

  function renderRows(items) {
    if (!items || !items.length) return renderEmpty();

    tbody.innerHTML = items.map((it) => {
      const [label, cls] = badgeClass(Number(it.visible));
      const user = it.usuario_nombre || "—";
      const title = it.titulo || "—";
      const price = money(it.precio);
      const fecha = fmtFecha(it.updated_at || it.created_at);

      return `
        <tr>
          <td>${escapeHtml(fecha)}</td>
          <td class="text-truncate" style="max-width:520px">${escapeHtml(title)}</td>
          <td class="text-end"><strong>${escapeHtml(price)}</strong></td>
          <td class="text-truncate" style="max-width:360px">${escapeHtml(user)}</td>
          <td><span class="ev-badge ${cls}">${escapeHtml(label)}</span></td>
          <td class="text-end">
            <button class="btn btn-sm ev-btn-light" data-action="ver" data-id="${it.codigo_producto}">
              <i class="bi bi-eye me-1"></i> Ver
            </button>
          </td>
        </tr>
      `;
    }).join("");
  }

  // ===== Cargar lista (acepta 2 formatos de respuesta) =====
  async function cargarLista() {
    try {
      const data = await apiGet(buildListUrl());

      const payload = (data && data.data && typeof data.data === "object")
        ? data.data
        : data;

      const total = Number(payload?.total || 0);
      const page = Number(payload?.page || 1);
      const size = Number(payload?.size || 10);
      const pendientes = Number(payload?.pendientes || 0);
      const items = Array.isArray(payload?.items) ? payload.items : [];

      state.total = total;
      state.page = page;
      state.size = size;

      if (lblPend) lblPend.textContent = String(pendientes);
      if (lblMeta) lblMeta.textContent = `Mostrando ${items.length} registros`;
      if (lblFooterLeft) lblFooterLeft.textContent = `Mostrando ${items.length} de ${total}`;
      if (lblPagina) lblPagina.textContent = String(page);

      const maxPage = Math.max(1, Math.ceil(total / size));
      if (btnPrev) btnPrev.disabled = page <= 1;
      if (btnNext) btnNext.disabled = page >= maxPage;

      renderRows(items);

    } catch (e) {
      console.error("[EV][ATENDER_PUBLICACION]", e);

      tbody.innerHTML = `
        <tr>
          <td colspan="6">
            <div class="alert alert-danger border-0 shadow-sm rounded-4 m-3">
              <b>Error:</b> No se pudo cargar la lista.<br>
              <small class="text-muted">${escapeHtml(e.message || "Error desconocido")}</small>
              <div class="mt-2"><small class="text-muted">API_LIST: ${escapeHtml(API_LIST)}</small></div>
            </div>
          </td>
        </tr>
      `;
    }
  }

  async function abrirDetalle(id) {
    state.currentId = Number(id);
    if (mComentario) mComentario.value = "";
    if (mGaleria) mGaleria.innerHTML = "";
    if (mNoImgs) mNoImgs.style.display = "block";

    const data = await apiGet(API_DET(state.currentId));
    const det = (data && data.data) ? data.data : data;

    if (!det) throw new Error("No se encontró la publicación.");

    if (mTitulo) mTitulo.textContent = det.titulo || "—";
    if (mPrecio) mPrecio.textContent = money(det.precio);
    if (mUsuario) mUsuario.textContent = det.usuario_nombre || "—";
    if (mEmail) mEmail.textContent = det.usuario_email || "—";
    if (mDescripcion) mDescripcion.textContent = det.descripcion || "—";

    const [label, cls] = badgeClass(Number(det.visible));
    if (mEstadoBadge) {
      mEstadoBadge.className = `ev-badge ${cls}`;
      mEstadoBadge.textContent = label;
    }

    const imgs = Array.isArray(det.imagenes) ? det.imagenes : [];
    if (imgs.length && mGaleria) {
      if (mNoImgs) mNoImgs.style.display = "none";
      mGaleria.innerHTML = imgs.map((x) => {
        const src = x.ruta || "";
        return `<img src="${escapeHtml(src)}" alt="Imagen">`;
      }).join("");
    }

    modal && modal.show();
  }

  async function cambiarEstado(nuevo) {
    const id = state.currentId;
    if (!id) return;

    const comentario = (mComentario?.value || "").trim();

    if (nuevo === "rechazada" && comentario.length < 3) {
      if (window.Swal) {
        Swal.fire({
          icon: "warning",
          title: "Comentario requerido",
          text: "Debes ingresar un comentario para rechazar.",
          confirmButtonText: "Entendido",
          confirmButtonColor: "#0F592F",
        });
      }
      return;
    }

    await apiPost(API_ESTADO(id), { estado: nuevo, comentario });
    modal && modal.hide();

    if (window.Swal) {
      Swal.fire({
        icon: "success",
        title: "Listo",
        text: "Estado actualizado correctamente.",
        confirmButtonText: "Entendido",
        confirmButtonColor: "#0F592F",
      });
    }

    await cargarLista();
  }

  // ===== Eventos =====
  form?.addEventListener("submit", (e) => {
    e.preventDefault();
    state.page = 1;
    cargarLista();
  });

  $("#btnRefrescar")?.addEventListener("click", () => cargarLista());

  $("#btnVerPendientes")?.addEventListener("click", () => {
    if (fEstado) fEstado.value = "pendiente";
    state.page = 1;
    cargarLista();
  });

  $("#btnVerAprobadas")?.addEventListener("click", () => {
    if (fEstado) fEstado.value = "aprobada";
    state.page = 1;
    cargarLista();
  });

  $("#btnVerRechazadas")?.addEventListener("click", () => {
    if (fEstado) fEstado.value = "rechazada";
    state.page = 1;
    cargarLista();
  });

  $("#btnVerBorradores")?.addEventListener("click", () => {
    if (fEstado) fEstado.value = "borrador";
    state.page = 1;
    cargarLista();
  });

  btnPrev?.addEventListener("click", () => {
    if (state.page > 1) {
      state.page--;
      cargarLista();
    }
  });

  btnNext?.addEventListener("click", () => {
    state.page++;
    cargarLista();
  });

  tbody.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-action='ver']");
    if (!btn) return;
    const id = btn.getAttribute("data-id");
    abrirDetalle(id).catch((err) => {
      if (window.Swal) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: err.message || "No se pudo abrir el detalle.",
          confirmButtonText: "Entendido",
          confirmButtonColor: "#0F592F",
        });
      }
    });
  });

  btnAprobar?.addEventListener("click", () =>
    cambiarEstado("aprobada").catch(console.error)
  );

  btnRechazar?.addEventListener("click", () =>
    cambiarEstado("rechazada").catch(console.error)
  );

  // Init
  cargarLista();
})();
