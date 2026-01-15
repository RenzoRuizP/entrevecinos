// views/js/notificacionesResidencia.js
// Notificaciones · Residencia (Vecino)
// - Lista: /api/notificaciones?categoria=residencia&estado=...&page=...&size=...
// - Marcar leída: POST /api/notificaciones/{codigo_notificacion}/leida
// - Reenviar: POST /api/notificaciones/residencia/{referencia_id}/reenviar
//
// FIX:
// 1) Header modal blanco -> ya se resuelve 100% por CSS (ev-notif-modal-title)
// 2) Tras reenviar: desaparece de la lista (se remueve + refresh + opcional marcar leída)
// 3) Hover premium -> CSS (ev-btn-orange:hover)

(function () {
  "use strict";

  const NS = "__EV_NOTIF_RESIDENCIA_UI__";
  if (!window[NS]) window[NS] = { controller: null, bound: false };
  const globalState = window[NS];

  function init() {
    // abort anterior
    try { if (globalState.controller) globalState.controller.abort(); } catch (_) {}
    globalState.controller = new AbortController();
    const { signal } = globalState.controller;

    const base = (window.BASE_URL || window.EV_BASE_URL || "").toString().replace(/\/+$/, "");
    const buildURL = (p) => base + "/" + String(p || "").replace(/^\/+/, "");

    const $ = (id) => document.getElementById(id);

    // --- DOM (según tu view actual) ---
    const list = $("listNotif");
    const selEstado = $("fEstadoNotif");
    const btnRefresh = $("btnRefrescarNotif");
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
    const btnReenviar = $("btnReenviar");

    // Si no estamos en esta vista, salir
    if (!list || !modal) return;

    // --- Estado ---
    let loading = false;
    let page = 1;
    const size = 8;

    // Notificación seleccionada
    let selected = null;

    // --- API ---
    const API_LISTAR = buildURL("api/notificaciones");
    const API_LEIDA = (idNoti) => buildURL(`api/notificaciones/${idNoti}/leida`);
    const API_REENVIAR = (refId) => buildURL(`api/notificaciones/residencia/${refId}/reenviar`);

    // --- Helpers ---
    const escapeHtml = (s) =>
      String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");

    function tryParseJSON(str) {
      try { return JSON.parse(String(str || "")); } catch (_) { return null; }
    }

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

    function badgeEstadoSolicitud(row) {
      // En tu tabla notificacion existe: subcategoria (y es el mejor lugar para esto)
      const sub = String(row?.subcategoria || "").toLowerCase().trim();

      if (sub === "observada") return `<span class="ev-badge-state ev-badge-obs"><i class="bi bi-exclamation-circle"></i> Observada</span>`;
      if (sub === "rechazada") return `<span class="ev-badge-state ev-badge-rej"><i class="bi bi-x-circle"></i> Rechazada</span>`;

      // fallback: inferencia por título
      const t = String(row?.titulo || "").toLowerCase();
      if (t.includes("rechaz")) return `<span class="ev-badge-state ev-badge-rej"><i class="bi bi-x-circle"></i> Rechazada</span>`;
      return `<span class="ev-badge-state ev-badge-obs"><i class="bi bi-exclamation-circle"></i> Observada</span>`;
    }

    function isReenviable(row) {
      // Reenviar solo si subcategoria es observada o rechazada (tu regla de negocio)
      const sub = String(row?.subcategoria || "").toLowerCase().trim();
      return (sub === "observada" || sub === "rechazada");
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

    function renderSkeleton() {
      list.innerHTML = `
        <div class="ev-item" aria-busy="true">
          <div class="ev-item-left">
            <div class="ev-dot"></div>
            <div>
              <p class="ev-item-title mb-1">Cargando…</p>
              <p class="ev-item-msg mb-0">Obteniendo tus notificaciones…</p>
            </div>
          </div>
        </div>`;
    }

    function renderEmpty(msg) {
      list.innerHTML = `
        <div class="ev-item">
          <div class="ev-item-left">
            <div class="ev-dot read"></div>
            <div>
              <p class="ev-item-title mb-1">Sin resultados</p>
              <p class="ev-item-msg mb-0">${escapeHtml(msg || "No hay notificaciones para mostrar.")}</p>
            </div>
          </div>
        </div>`;
    }

    function setMeta(meta) {
      const total = Number(meta?.total || 0);
      const p = Number(meta?.page || 1);
      const s = Number(meta?.size || size);
      const pages = Math.max(1, Math.ceil(total / Math.max(1, s)));

      if (counter) counter.textContent = `${total} en total`;
      if (footerLeft) {
        const from = total === 0 ? 0 : ((p - 1) * s + 1);
        const to = Math.min(total, p * s);
        footerLeft.textContent = `Mostrando ${from}-${to} de ${total}`;
      }
      if (pageInfo) pageInfo.textContent = `${p} / ${pages}`;

      if (btnPrev) btnPrev.disabled = (p <= 1);
      if (btnNext) btnNext.disabled = (p >= pages);

      return pages;
    }

    function renderList(rows) {
      if (!Array.isArray(rows) || rows.length === 0) {
        renderEmpty("No tienes notificaciones de residencia para este filtro.");
        return;
      }

      list.innerHTML = rows.map((r) => {
        const idNoti = Number(r.codigo_notificacion || 0);
        const estadoLectura = String(r.estado || "").toLowerCase().trim(); // no_leida / leida
        const dotClass = (estadoLectura === "leida") ? "ev-dot read" : "ev-dot";

        // Para el “reenviar”, el ID correcto es referencia_id
        const refId = Number(r.referencia_id || 0);

        // Mensaje corto
        const msg = String(r.mensaje || "").trim();
        const msgShort = msg.length > 120 ? (msg.slice(0, 120) + "…") : (msg || "—");

        const fecha = (r.created_at || r.read_at || "").toString();

        // El botón "Ver" debe existir, pero reenviar solo se habilita en el modal si es reenviable.
        // Y tras reenviar, el item se eliminará del DOM + refresh, evitando reenvíos múltiples.
        return `
          <div class="ev-item" id="evNoti_${idNoti}" data-id="${idNoti}" data-ref="${refId}">
            <div class="ev-item-left">
              <div class="${dotClass}"></div>
              <div>
                <p class="ev-item-title mb-1">${escapeHtml(r.titulo || "Notificación")}</p>
                <p class="ev-item-msg mb-0">${escapeHtml(msgShort)}</p>
                <div class="ev-item-meta">
                  <span class="me-2">${escapeHtml(fecha || "—")}</span>
                  ${badgeEstadoSolicitud(r)}
                </div>
              </div>
            </div>

            <div class="ev-item-actions">
              <button class="btn ev-btn ev-btn-light ev-btn-ver" type="button" data-open="${idNoti}">
                <i class="bi bi-eye me-1"></i> Ver
              </button>
            </div>
          </div>
        `;
      }).join("");
    }

    function openModalById(idNoti) {
      const el = document.getElementById(`evNoti_${idNoti}`);
      if (!el) return;

      const refId = Number(el.dataset.ref || 0);

      // Obtener el row desde dataset no es suficiente; lo guardaremos en memoria buscando en la última carga
      // Para ello usamos un mapa local:
      const row = lastRowsById.get(idNoti);
      if (!row) return;

      selected = {
        row,
        idNoti,
        refId
      };

      const estadoLectura = String(row.estado || "").toLowerCase().trim();
      const reenviable = isReenviable(row);

      if (mTitulo) mTitulo.textContent = row.titulo || "Notificación";
      if (mMensaje) mMensaje.textContent = row.mensaje || "—";
      if (mFecha) mFecha.textContent = row.created_at || row.read_at || "—";
      if (mState) mState.innerHTML = badgeEstadoSolicitud(row);

      // Reset file
      if (mFile) mFile.value = "";

      // Botón reenviar solo si corresponde
      if (btnReenviar) {
        btnReenviar.classList.toggle("d-none", !reenviable);
        btnReenviar.disabled = false;
      }

      // Si está no_leida y el usuario abre el modal, marcamos leída (premium UX) SOLO si el filtro permite
      // Esto no cambia la regla de reenvío: reenviar depende de subcategoria, no de estado leída.
      if (estadoLectura === "no_leida") {
        marcarLeidaSilencioso(idNoti).catch(() => {});
      }

      modal.show();
    }

    async function marcarLeidaSilencioso(idNoti) {
      try {
        await fetchJSON(API_LEIDA(idNoti), { method: "POST" });

        // Actualiza dot en UI si sigue visible
        const item = document.getElementById(`evNoti_${idNoti}`);
        const dot = item?.querySelector(".ev-dot");
        if (dot) dot.classList.add("read");

        // Si el usuario está filtrando "No leídas", al marcar leída debe desaparecer.
        const est = String(selEstado?.value || "no_leida");
        if (est === "no_leida") {
          item?.remove();
        }

        // refresh suave para cuadrar contadores/paginación
        await cargarLista({ keepPage: true, silent: true });
      } catch (_) {
        // silencioso
      }
    }

    const lastRowsById = new Map();

    async function cargarLista(opts = {}) {
      if (loading) return;
      loading = true;

      const keepPage = !!opts.keepPage;
      const silent = !!opts.silent;

      if (!keepPage) page = 1;
      if (!silent) renderSkeleton();

      try {
        const estado = String(selEstado?.value || "no_leida");
        const url =
          `${API_LISTAR}?categoria=residencia&estado=${encodeURIComponent(estado)}&page=${encodeURIComponent(page)}&size=${encodeURIComponent(size)}`;

        const res = await fetchJSON(url);
        const rows = Array.isArray(res.data) ? res.data : [];
        const meta = res.meta || { page, size, total: rows.length };

        lastRowsById.clear();
        rows.forEach((r) => {
          const id = Number(r.codigo_notificacion || 0);
          if (id > 0) lastRowsById.set(id, r);
        });

        const pages = setMeta(meta);

        // Ajuste si page queda fuera de rango tras cambios
        if (page > pages) {
          page = pages;
          loading = false;
          return cargarLista({ keepPage: true, silent: true });
        }

        renderList(rows);
      } catch (e) {
        if (String(e?.name || "").toLowerCase() === "aborterror") return;
        console.error("[EV][NOTIF_RESIDENCIA]", e);
        if (counter) counter.textContent = "0 en total";
        if (footerLeft) footerLeft.textContent = "Mostrando 0 de 0";
        if (pageInfo) pageInfo.textContent = "1 / 1";
        renderEmpty("Error al cargar notificaciones.");
      } finally {
        loading = false;
      }
    }

    async function reenviarSolicitud() {
      if (!selected?.row) return swalInfo("Selecciona una notificación.");
      const refId = Number(selected.refId || 0);
      const idNoti = Number(selected.idNoti || 0);

      if (!refId) return swalErr("No se encontró referencia de solicitud (referencia_id).");

      // Bloqueo por subcategoria (regla de negocio)
      if (!isReenviable(selected.row)) {
        return swalInfo("Esta notificación ya no permite reenvío.");
      }

      const f = mFile?.files?.[0] || null;
      if (!f) return swalInfo("Adjunta un comprobante para reenviar.");

      const okType = /^(application\/pdf|image\/jpeg|image\/png)$/i.test(f.type);
      const okExt = /\.(pdf|jpg|jpeg|png)$/i.test(f.name || "");
      if (!(okType || okExt)) return swalInfo("Solo se permite PDF, JPG o PNG.");
      if (f.size > 5 * 1024 * 1024) return swalInfo("Máximo 5MB.");

      const original = btnReenviar ? btnReenviar.innerHTML : "";
      if (btnReenviar) {
        btnReenviar.disabled = true;
        btnReenviar.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Reenviando...`;
      }

      try {
        const fd = new FormData();
        fd.append("documento_domicilio", f);

        const res = await fetch(API_REENVIAR(refId), {
          method: "POST",
          cache: "no-store",
          credentials: "include",
          headers: { "X-Requested-With": "XMLHttpRequest" },
          body: fd,
          signal
        });

        const ct = (res.headers.get("content-type") || "").toLowerCase();
        const data = ct.includes("application/json")
          ? await res.json().catch(() => ({}))
          : { ok: res.ok };

        if (!res.ok || data.ok === false) {
          throw new Error(data.mensaje || data.message || `HTTP ${res.status}`);
        }

        // Premium UX:
        // 1) marcar leída (si existe idNoti)
        if (idNoti) {
          try { await fetchJSON(API_LEIDA(idNoti), { method: "POST" }); } catch (_) {}
        }

        // 2) cerrar modal
        try { modal.hide(); } catch (_) {}

        // 3) remover de la lista inmediatamente (evita reenvíos múltiples aunque no refresque)
        const item = document.getElementById(`evNoti_${idNoti}`);
        if (item) item.remove();

        swalOk(data.mensaje || "Solicitud reenviada.");

        // 4) refresh para recalcular contadores/paginación
        await cargarLista({ keepPage: true, silent: true });

      } catch (e) {
        console.error("[EV][REENVIAR_RESIDENCIA]", e);
        swalErr(e.message || "No se pudo reenviar la solicitud.");
      } finally {
        if (btnReenviar) {
          btnReenviar.disabled = false;
          btnReenviar.innerHTML = original;
        }
      }
    }

    // --- Eventos ---
    list.addEventListener("click", (ev) => {
      const btn = ev.target.closest("[data-open]");
      if (!btn) return;
      const idNoti = Number(btn.getAttribute("data-open") || 0);
      if (!idNoti) return;
      openModalById(idNoti);
    }, { signal });

    if (btnReenviar) {
      btnReenviar.addEventListener("click", (ev) => {
        ev.preventDefault();
        reenviarSolicitud();
      }, { signal });
    }

    if (btnRefresh) {
      btnRefresh.addEventListener("click", (ev) => {
        ev.preventDefault();
        cargarLista({ keepPage: false });
      }, { signal });
    }

    if (selEstado) {
      selEstado.addEventListener("change", () => {
        cargarLista({ keepPage: false });
      }, { signal });
    }

    if (btnPrev) {
      btnPrev.addEventListener("click", () => {
        if (page > 1) {
          page -= 1;
          cargarLista({ keepPage: true });
        }
      }, { signal });
    }

    if (btnNext) {
      btnNext.addEventListener("click", () => {
        page += 1;
        cargarLista({ keepPage: true });
      }, { signal });
    }

    // Init
    cargarLista({ keepPage: false });
  }

  if (!globalState.bound) {
    globalState.bound = true;
    document.addEventListener("DOMContentLoaded", init);
    document.addEventListener("ev:content-loaded", init);
  } else {
    init();
  }
})();
