/* =========================================================
   EV - Atender Recargas (Soporte)
   - Lista recargas
   - Abre modal de revisión
   - Aprueba / Observa / Rechaza
   Nota: Endpoints son placeholders; ajusta rutas cuando
   implementes controllers/api.
========================================================= */

(() => {
  "use strict";

  const $ = (sel) => document.querySelector(sel);

  // UI refs
  const lblPendientes = $("#lblPendientes");
  const lblMeta = $("#lblMeta");
  const tbody = $("#tbodyRecargas");

  const btnPrev = $("#btnPrev");
  const btnNext = $("#btnNext");
  const lblPagina = $("#lblPagina");

  const formFiltros = $("#formFiltros");
  const fEstado = $("#fEstado");
  const fRango = $("#fRango");
  const fTexto = $("#fTexto");

  const btnRefrescar = $("#btnRefrescar");
  const btnExportar = $("#btnExportar");
  const btnVerPendientes = $("#btnVerPendientes");
  const btnVerObservadas = $("#btnVerObservadas");
  const btnVerAprobadas = $("#btnVerAprobadas");
  const btnVerRechazadas = $("#btnVerRechazadas");

  // Modal refs
  const modalEl = $("#modalRecarga");
  const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

  const mUsuario = $("#mUsuario");
  const mDni = $("#mDni");
  const mResidencia = $("#mResidencia");
  const mCondominio = $("#mCondominio");
  const mMonto = $("#mMonto");
  const mMetodo = $("#mMetodo");
  const mOperacion = $("#mOperacion");
  const mImagen = $("#mImagen");
  const mNoImagen = $("#mNoImagen");
  const mComentario = $("#mComentario");
  const mEstadoBadge = $("#mEstadoBadge");

  const btnAprobar = $("#btnAprobar");
  const btnObservar = $("#btnObservar");
  const btnRechazar = $("#btnRechazar");

  // State
  let currentPage = 1;
  const pageSize = 10;
  let currentFilters = {
    estado: "pendiente",
    rango: "7",
    q: ""
  };

  let currentModalRecarga = null;

  // ----------------------------
  // Helpers
  // ----------------------------
  function money(n) {
    // Render 2 decimals
    const num = Number(n || 0);
    return `S/ ${num.toFixed(2)}`;
  }

  function badgeClass(estado) {
    switch ((estado || "").toLowerCase()) {
      case "aprobada": return "ev-badge ev-badge-aprobada";
      case "observada": return "ev-badge ev-badge-observada";
      case "rechazada": return "ev-badge ev-badge-rechazada";
      default: return "ev-badge ev-badge-pendiente";
    }
  }

  function safeText(s) {
    const div = document.createElement("div");
    div.textContent = s ?? "";
    return div.innerHTML;
  }

  async function apiGet(url) {
    const res = await fetch(url, { credentials: "include" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }

  async function apiPost(url, payload) {
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify(payload || {})
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }

  function notifyInfo(msg) {
    Swal.fire({ icon: "info", title: "Entre Vecinos", text: msg });
  }

  function notifyOk(msg) {
    Swal.fire({ icon: "success", title: "Listo", text: msg, timer: 1600, showConfirmButton: false });
  }

  function notifyErr(msg) {
    Swal.fire({ icon: "error", title: "Ocurrió un problema", text: msg });
  }

  // ----------------------------
  // Endpoints (ajusta cuando tengas controller)
  // ----------------------------
  function endpointList() {
    const base = window.BASE_URL || "/";
    const qs = new URLSearchParams({
      page: String(currentPage),
      size: String(pageSize),
      estado: currentFilters.estado,
      rango: currentFilters.rango,
      q: currentFilters.q || ""
    });
    return `${base}api/soporte/recargas?${qs.toString()}`;
  }

  function endpointUpdateEstado(id) {
    const base = window.BASE_URL || "/";
    return `${base}api/soporte/recargas/${id}/estado`;
  }

  function endpointExportCsv() {
    const base = window.BASE_URL || "/";
    const qs = new URLSearchParams({
      estado: currentFilters.estado,
      rango: currentFilters.rango,
      q: currentFilters.q || ""
    });
    return `${base}api/soporte/recargas/export.csv?${qs.toString()}`;
  }

  // ----------------------------
  // Render table
  // ----------------------------
  function renderRows(items) {
    if (!items || items.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7" class="text-center py-4 text-muted">
            No hay solicitudes para los filtros seleccionados.
          </td>
        </tr>`;
      return;
    }

    tbody.innerHTML = items.map((r) => {
      const avatarLetter = (r?.usuario_nombre || "U").trim().charAt(0).toUpperCase();
      const residencia = `${safeText(r?.torre || "—")} - ${safeText(r?.departamento || "—")}`;
      const fecha = safeText(r?.fecha ?? "—");
      const metodo = safeText(r?.metodo ?? "—");
      const oper = safeText(r?.id_operacion ?? "—");
      const estado = safeText(r?.estado ?? "pendiente");

      return `
        <tr>
          <td>
            <div class="fw-semibold">${fecha}</div>
            <div class="text-muted small">${safeText(r?.hora ?? "")}</div>
          </td>

          <td>
            <div class="ev-usuario">
              <div class="ev-avatar">${avatarLetter}</div>
              <div>
                <div class="fw-bold">${safeText(r?.usuario_nombre ?? "—")}</div>
                <div class="ev-usuario-sub">${residencia}</div>
              </div>
            </div>
          </td>

          <td class="fw-bold">${money(r?.monto)}</td>

          <td>
            <span class="fw-semibold">${metodo}</span>
          </td>

          <td class="font-monospace">${oper}</td>

          <td><span class="${badgeClass(estado)}">${estado}</span></td>

          <td class="text-end">
            <button class="btn ev-btn-light btn-sm btn-ver"
              data-id="${safeText(r?.id)}"
              type="button">
              Ver
            </button>
          </td>
        </tr>
      `;
    }).join("");

    // Bind view buttons
    tbody.querySelectorAll(".btn-ver").forEach(btn => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-id");
        const found = items.find(x => String(x.id) === String(id));
        if (!found) return;
        openModal(found);
      });
    });
  }

  function setPendingCount(n) {
    lblPendientes.textContent = String(n ?? 0);
  }

  function setMeta(total, from, to) {
    const t = Number(total || 0);
    lblMeta.textContent = t > 0 ? `Mostrando ${from}-${to} de ${t} registros` : `Mostrando 0 registros`;
    $("#lblFooterLeft").textContent = t > 0 ? `Mostrando ${to - from + 1} de ${t}` : `Mostrando 0 de 0`;
  }

  function setPagination(hasPrev, hasNext, page) {
    btnPrev.disabled = !hasPrev;
    btnNext.disabled = !hasNext;
    lblPagina.textContent = String(page);
  }

  // ----------------------------
  // Modal
  // ----------------------------
  function openModal(recarga) {
    currentModalRecarga = recarga;

    mUsuario.textContent = recarga?.usuario_nombre ?? "—";
    mDni.textContent = recarga?.dni ?? "—";
    mResidencia.textContent = `${recarga?.torre ?? "—"} - ${recarga?.departamento ?? "—"}`;
    mCondominio.textContent = recarga?.condominio ?? "—";
    mMonto.textContent = money(recarga?.monto);
    mMetodo.textContent = recarga?.metodo ?? "—";
    mOperacion.textContent = recarga?.id_operacion ?? "—";

    const estado = (recarga?.estado ?? "pendiente").toLowerCase();
    mEstadoBadge.className = badgeClass(estado);
    mEstadoBadge.textContent = estado;

    mComentario.value = "";

    const imgUrl = recarga?.comprobante_url || "";
    if (imgUrl) {
      mImagen.src = imgUrl;
      mImagen.classList.remove("d-none");
      mNoImagen.classList.add("d-none");
    } else {
      mImagen.src = "";
      mImagen.classList.add("d-none");
      mNoImagen.classList.remove("d-none");
    }

    modal?.show();
  }

  function requireCommentIfNeeded(action) {
    // Aprobar: comentario opcional
    // Observar / Rechazar: comentario obligatorio
    if (action === "aprobada") return true;

    const txt = (mComentario.value || "").trim();
    if (!txt) {
      notifyInfo("Por favor, ingresa un comentario para continuar.");
      return false;
    }
    return true;
  }

  async function updateEstado(action) {
    if (!currentModalRecarga?.id) return;

    if (!requireCommentIfNeeded(action)) return;

    const id = currentModalRecarga.id;
    const payload = {
      estado: action,
      comentario: (mComentario.value || "").trim()
    };

    const confirm = await Swal.fire({
      icon: "question",
      title: "Confirmar acción",
      text:
        action === "aprobada"
          ? "¿Confirmas aprobar esta recarga y acreditar el saldo?"
          : action === "observada"
            ? "¿Confirmas marcar como observada? Se notificará al usuario."
            : "¿Confirmas rechazar esta recarga? Se notificará al usuario.",
      showCancelButton: true,
      confirmButtonText: "Sí, continuar",
      cancelButtonText: "Cancelar"
    });

    if (!confirm.isConfirmed) return;

    try {
      const url = endpointUpdateEstado(id);
      const resp = await apiPost(url, payload);

      if (resp?.ok === false) {
        notifyErr(resp?.mensaje || "No se pudo actualizar el estado.");
        return;
      }

      notifyOk(resp?.mensaje || "Actualizado correctamente.");
      modal?.hide();
      await loadList(); // refresh
    } catch (e) {
      // Si aún no existe el endpoint, evitamos romper el flujo.
      notifyErr("No se pudo conectar con el servicio. Verifica que el endpoint de Soporte esté implementado.");
      console.error(e);
    }
  }

  // ----------------------------
  // Load list
  // ----------------------------
  async function loadList() {
    try {
      const url = endpointList();
      const data = await apiGet(url);

      // Estructura esperada (recomendada):
      // { ok:true, pendientes:5, total:20, page:1, size:10, items:[...] }
      if (data?.ok === false) {
        notifyErr(data?.mensaje || "No se pudo obtener la lista.");
        return;
      }

      const items = data?.items || [];
      renderRows(items);

      setPendingCount(data?.pendientes ?? 0);

      const total = data?.total ?? items.length;
      const page = data?.page ?? currentPage;
      const size = data?.size ?? pageSize;

      const from = total === 0 ? 0 : ((page - 1) * size + 1);
      const to = total === 0 ? 0 : Math.min(page * size, total);

      setMeta(total, from, to);

      const hasPrev = page > 1;
      const hasNext = to < total;

      currentPage = page;
      setPagination(hasPrev, hasNext, page);

    } catch (e) {
      // Modo fallback (sin endpoint): renderiza demo, no rompe UI
      console.warn("API no disponible, mostrando demo:", e);

      const demo = [
        {
          id: 1,
          fecha: "23/06/2025",
          hora: "10:39 PM",
          usuario_nombre: "Gerardo Salas",
          dni: "12345678",
          torre: "Torre B",
          departamento: "Dpto. 304",
          condominio: "Los Faisanes",
          monto: 50.00,
          metodo: "Yape",
          id_operacion: "AJ5075653",
          estado: currentFilters.estado,
          comprobante_url: ""
        }
      ];

      renderRows(demo);
      setPendingCount(currentFilters.estado === "pendiente" ? 1 : 0);
      setMeta(demo.length, 1, demo.length);
      setPagination(false, false, 1);

      notifyInfo("Aún no se detecta el endpoint de Soporte. La vista está lista; falta conectar la API.");
    }
  }

  // ----------------------------
  // Events
  // ----------------------------
  formFiltros?.addEventListener("submit", (e) => {
    e.preventDefault();
    currentFilters.estado = fEstado.value;
    currentFilters.rango = fRango.value;
    currentFilters.q = (fTexto.value || "").trim();
    currentPage = 1;
    loadList();
  });

  btnPrev?.addEventListener("click", () => {
    if (currentPage <= 1) return;
    currentPage -= 1;
    loadList();
  });

  btnNext?.addEventListener("click", () => {
    currentPage += 1;
    loadList();
  });

  btnRefrescar?.addEventListener("click", () => loadList());

  btnExportar?.addEventListener("click", () => {
    try {
      const url = endpointExportCsv();
      window.open(url, "_blank");
    } catch (e) {
      notifyErr("No se pudo exportar.");
    }
  });

  btnVerPendientes?.addEventListener("click", () => { fEstado.value = "pendiente"; formFiltros.dispatchEvent(new Event("submit")); });
  btnVerObservadas?.addEventListener("click", () => { fEstado.value = "observada"; formFiltros.dispatchEvent(new Event("submit")); });
  btnVerAprobadas?.addEventListener("click", () => { fEstado.value = "aprobada"; formFiltros.dispatchEvent(new Event("submit")); });
  btnVerRechazadas?.addEventListener("click", () => { fEstado.value = "rechazada"; formFiltros.dispatchEvent(new Event("submit")); });

  btnAprobar?.addEventListener("click", () => updateEstado("aprobada"));
  btnObservar?.addEventListener("click", () => updateEstado("observada"));
  btnRechazar?.addEventListener("click", () => updateEstado("rechazada"));

  // Init
  document.addEventListener("DOMContentLoaded", () => {
    // defaults
    lblPendientes.textContent = "0";
    loadList();
  });

})();
