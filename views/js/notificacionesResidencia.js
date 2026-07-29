// views/js/notificacionesResidencia.js
// Módulo: Notificaciones > Residencia (Vecino)
// Lista observadas/rechazadas + Modal detalle + Reenviar solicitud
// FIXES:
// - “Ver” solo si estado = no_leida (bloquea reenvío múltiple)
// - Al reenviar: backend marca notificación como leída por referencia_id (solicitud original)
// - Botón Guardar: a la derecha + hover premium + bloqueo UI tras éxito

(function () {
  "use strict";

  const NS = "__EV_NOTI_RESIDENCIA__";
  if (!window[NS]) window[NS] = { controller: null, bound: false };
  const state = window[NS];

  function initNotificacionesResidencia() {
    try { if (state.controller) state.controller.abort(); } catch (_) {}
    state.controller = new AbortController();
    const { signal } = state.controller;

    const base = (window.EV?.baseUrl ?? window.BASE_URL ?? window.EV_BASE_URL ?? "").toString().replace(/\/+$/, "");
    const buildURL = (p) => base + "/" + String(p || "").replace(/^\/+/, "");
    const $ = (id) => document.getElementById(id);

    // DOM
    const list = $("listNotif");
    const fEstado = $("fEstadoNotif");
    const btnRefrescar = $("btnRefrescarNotif");
    const counter = $("evNotifCounter");
    const footerLeft = $("evFooterLeft");
    const btnPrev = $("btnPrevNotif");
    const btnNext = $("btnNextNotif");
    const pageInfo = $("pageInfoNotif");

    // Modal
    const modalEl = $("modalNotifResidencia");
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

    const mState = $("mState");
    const mFecha = $("mFecha");
    const mTitulo = $("mTitulo");
    const mMensaje = $("mMensaje");
    const mFile = $("mFile");
    const btnGuardar = $("btnGuardarReenvio");
    const lockMsg = $("evReenvioLocked");

    if (!list || !modal) return;

    // Local state
    let page = 1;
    const size = 10;
    let total = 0;
    let loading = false;
    let seleccionado = null;

    const itemsById = new Map();

    // Endpoints
    const API_LISTAR = buildURL("api/notificaciones"); // GET ?categoria=residencia&estado=...&page&size
    const API_REENVIAR = (id) => buildURL(`api/notificaciones/residencia/${id}/reenviar`);

    const escapeHtml = (s) =>
      String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");

    function swalOk(msg) {
      if (window.Swal?.fire) return Swal.fire({ icon: "success", title: "Listo", text: msg, timer: 1400, showConfirmButton: false });
      alert(msg);
    }
    function swalErr(msg) {
      if (window.Swal?.fire) return Swal.fire({ icon: "error", title: "Ocurrió un problema", text: msg });
      alert(msg);
    }
    function swalInfo(msg) {
      if (window.Swal?.fire) return Swal.fire({ icon: "info", title: "Entre Vecinos", text: msg });
      alert(msg);
    }

    function badgeEstado(estado) {
      const e = String(estado || "").toLowerCase();
      if (e === "rechazada") return `<span class="ev-badge-state ev-badge-rej"><i class="bi bi-x-circle"></i> Rechazada</span>`;
      return `<span class="ev-badge-state ev-badge-obs"><i class="bi bi-exclamation-circle"></i> Observada</span>`;
    }

    function dotClass(noti) {
      return String(noti?.estado || "") === "leida" ? "ev-dot read" : "ev-dot";
    }

    async function fetchJSON(url, opts = {}) {
      const res = await fetch(url, {
        method: opts.method || "GET",
        cache: "no-store",
        credentials: "include",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
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

      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.mensaje || data?.message || data?.error || `HTTP ${res.status}`);
      if (data && typeof data === "object" && (data.ok === false || data.success === false)) {
        throw new Error(data?.mensaje || data?.message || "Operación no exitosa.");
      }
      return data;
    }

    function parseRows(res) {
      if (res && typeof res === "object" && Array.isArray(res.data)) {
        return { rows: res.data, total: Number(res.meta?.total || 0) };
      }
      if (Array.isArray(res)) return { rows: res, total: res.length };
      const alt = res?.items || res?.rows || res?.notificaciones || null;
      if (Array.isArray(alt)) return { rows: alt, total: Number(res.total || alt.length || 0) };
      return { rows: [], total: 0 };
    }

    function renderEmpty(msg) {
      list.innerHTML = `
        <div class="ev-item">
          <div class="ev-item-left">
            <div class="ev-dot read"></div>
            <div>
              <div class="ev-item-title">Sin resultados</div>
              <div class="ev-item-msg">${escapeHtml(msg || "No tienes solicitudes observadas o rechazadas.")}</div>
              <div class="ev-item-meta">—</div>
            </div>
          </div>
        </div>`;
    }

    function updateFooter(showing) {
      const maxPage = Math.max(1, Math.ceil(total / size));
      if (pageInfo) pageInfo.textContent = `${page} / ${maxPage}`;
      if (btnPrev) btnPrev.disabled = page <= 1;
      if (btnNext) btnNext.disabled = page >= maxPage;
      if (footerLeft) footerLeft.textContent = `Mostrando ${showing} de ${total}`;
      if (counter) counter.textContent = `${total} total`;
    }

    function onlyObsRej(rows) {
      return (rows || []).filter(r => {
        const payload = safeParsePayload(r?.payload_json);
        const est = String(payload?.estado || "").toLowerCase();
        // Solo obs/rej
        return est === "observada" || est === "rechazada";
      });
    }

    function safeParsePayload(payloadJson) {
      try {
        if (!payloadJson) return {};
        if (typeof payloadJson === "object") return payloadJson;
        return JSON.parse(String(payloadJson));
      } catch (_) {
        return {};
      }
    }

    function renderList(rows) {
      const filtradas = onlyObsRej(rows);

      if (!filtradas.length) {
        renderEmpty("No tienes solicitudes observadas o rechazadas.");
        total = 0;
        updateFooter(0);
        return;
      }

      itemsById.clear();
      filtradas.forEach(n => itemsById.set(Number(n.codigo_notificacion), n));

      // Total real mostrado
      total = filtradas.length;
      updateFooter(filtradas.length);

      list.innerHTML = filtradas.map(n => {
        const payload = safeParsePayload(n.payload_json);
        const solicitudId = Number(payload?.codigo_solicitud || n.referencia_id || 0);
        const estadoSol = String(payload?.estado || "").toLowerCase();
        const fecha = n.created_at || "—";
        const msg = payload?.comentario_admin || n.mensaje || "—";

        // ✅ FIX RAÍZ UI: “Ver” solo si la notificación sigue NO LEÍDA
        const puedeVer = String(n.estado) === "no_leida" && (estadoSol === "observada" || estadoSol === "rechazada");

        return `
          <div class="ev-item" id="notif_${n.codigo_notificacion}">
            <div class="ev-item-left">
              <div class="${dotClass(n)}"></div>
              <div>
                <div class="ev-item-title">${escapeHtml(n.titulo || "Notificación de residencia")}</div>
                <div class="ev-item-msg">${escapeHtml(String(msg).slice(0, 140))}${String(msg).length > 140 ? "…" : ""}</div>
                <div class="ev-item-meta">${escapeHtml(fecha)} · Solicitud #${solicitudId}</div>
              </div>
            </div>

            <div class="ev-item-actions">
              ${badgeEstado(estadoSol)}
              ${puedeVer ? `
                <button class="btn ev-btn ev-btn-light btnVer" type="button"
                        data-noti="${n.codigo_notificacion}"
                        data-sol="${solicitudId}">
                  <i class="bi bi-eye me-1"></i> Ver
                </button>` : ``}
            </div>
          </div>`;
      }).join("");
    }

    function openModal(noti, solicitudId) {
      seleccionado = { noti, solicitudId };

      const payload = safeParsePayload(noti.payload_json);
      const estadoSol = String(payload?.estado || "").toLowerCase();
      const fecha = noti.created_at || "—";
      const comentario = payload?.comentario_admin || noti.mensaje || "—";

      if (mState) mState.innerHTML = badgeEstado(estadoSol);
      if (mFecha) mFecha.textContent = fecha;
      if (mTitulo) mTitulo.textContent = noti.titulo || "Detalle de notificación";
      if (mMensaje) mMensaje.textContent = comentario;

      // Reset
      if (mFile) mFile.value = "";
      if (lockMsg) lockMsg.classList.add("d-none");

      // Si ya está leída, no debe permitir guardar
      const reenviable = String(noti.estado) === "no_leida" && (estadoSol === "observada" || estadoSol === "rechazada");

      if (btnGuardar) {
        btnGuardar.classList.toggle("d-none", !reenviable);
        btnGuardar.disabled = !reenviable;
      }

      if (!reenviable && lockMsg) lockMsg.classList.remove("d-none");

      modal.show();
    }

    async function cargarLista() {
      if (loading) return;
      loading = true;

      try {
        const estado = (fEstado?.value || "no_leida").trim(); // no_leida|leida|all
        const qs = new URLSearchParams({
          categoria: "residencia",
          estado,
          page: String(page),
          size: String(size)
        });

        const res = await fetchJSON(API_LISTAR + "?" + qs.toString());
        const parsed = parseRows(res);
        renderList(parsed.rows || []);
      } catch (e) {
        if (String(e?.name || "").toLowerCase() === "aborterror") return;
        console.error("[EV][NOTI_RESIDENCIA]", e);
        renderEmpty("Error al cargar notificaciones.");
      } finally {
        loading = false;
      }
    }

    async function reenviarSolicitud() {
      if (!seleccionado?.noti) return swalInfo("Selecciona una notificación.");
      const noti = seleccionado.noti;

      // ✅ usamos el ID ORIGINAL de la solicitud (referencia_id / payload.codigo_solicitud)
      const payload = safeParsePayload(noti.payload_json);
      const solicitudId = Number(payload?.codigo_solicitud || noti.referencia_id || 0);
      if (!solicitudId) return swalErr("No se pudo identificar la solicitud.");

      // bloqueo por estado leído
      if (String(noti.estado) !== "no_leida") {
        return swalInfo("Esta notificación ya fue atendida.");
      }

      const f = mFile?.files?.[0] || null;
      if (!f) return swalInfo("Adjunta un comprobante para reenviar.");

      const okType = /^(application\/pdf|image\/jpeg|image\/png)$/i.test(f.type);
      const okExt = /\.(pdf|jpg|jpeg|png)$/i.test(f.name || "");
      if (!(okType || okExt)) return swalInfo("Solo se permite PDF, JPG o PNG.");
      if (f.size > 5 * 1024 * 1024) return swalInfo("Máximo 5MB.");

      const original = btnGuardar ? btnGuardar.innerHTML : "";
      if (btnGuardar) {
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Guardando...`;
      }

      try {
        const fd = new FormData();
        fd.append("documento_domicilio", f);

        const res = await fetch(API_REENVIAR(solicitudId), {
          method: "POST",
          credentials: "include",
          headers: { "X-Requested-With": "XMLHttpRequest" },
          body: fd,
          signal
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.ok === false) {
          throw new Error(data.mensaje || data.message || `HTTP ${res.status}`);
        }

        swalOk(data.mensaje || "Solicitud reenviada.");

        // ✅ premium UX: cerrar modal
        try { modal.hide(); } catch (_) {}

        // ✅ marcar en UI como atendida: no debe tener “Ver” más (y opcionalmente remover item)
        // Como backend la marcó leída por referencia_id, en el siguiente refresh ya no tendrá “Ver”.
        // Si estás filtrando por "No leídas", va a desaparecer.
        await cargarLista();

      } catch (e) {
        console.error("[EV][REENVIAR]", e);
        swalErr(e.message || "No se pudo reenviar la solicitud.");
      } finally {
        if (btnGuardar) {
          btnGuardar.disabled = false;
          btnGuardar.innerHTML = original;
        }
      }
    }

    // Events
    list.addEventListener("click", (ev) => {
      const btn = ev.target.closest(".btnVer");
      if (!btn) return;

      const notiId = Number(btn.dataset.noti || 0);
      const solId = Number(btn.dataset.sol || 0);

      const noti = itemsById.get(notiId);
      if (noti) openModal(noti, solId);
    }, { signal });

    btnGuardar?.addEventListener("click", (e) => {
      e.preventDefault();
      reenviarSolicitud();
    }, { signal });

    fEstado?.addEventListener("change", () => {
      page = 1;
      cargarLista();
    }, { signal });

    btnRefrescar?.addEventListener("click", () => cargarLista(), { signal });

    btnPrev?.addEventListener("click", () => {
      if (page > 1) { page--; cargarLista(); }
    }, { signal });

    btnNext?.addEventListener("click", () => {
      const max = Math.max(1, Math.ceil(total / size));
      if (page < max) { page++; cargarLista(); }
    }, { signal });

    // Init
    cargarLista();
  }

  if (!state.bound) {
    state.bound = true;
    document.addEventListener("DOMContentLoaded", initNotificacionesResidencia);
    document.addEventListener("ev:content-loaded", initNotificacionesResidencia);
  } else {
    initNotificacionesResidencia();
  }
})();
