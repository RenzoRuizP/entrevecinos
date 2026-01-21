// views/js/atenderCuentasUsuario.js
(function () {
  "use strict";

  const baseUrl = (window.BASE_URL || "").replace(/\/+$/, "");
  if (!baseUrl) return;

  let observer = null;
  let modalInstance = null;
  let currentId = null;

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

  function $(sel) {
    return document.querySelector(sel);
  }

  function getTbody() {
    return (
      $("#evUsuariosBody") ||
      $("#tablaUsuariosBody") ||
      document.querySelector("table tbody")
    );
  }

  // Controles (según tu view actual)
  function getControls() {
    return {
      selEstado: $("#filtroEstado"),
      selModo: $("#filtroModo"),
      selConjunto: $("#filtroConjunto"),
      selCondominio: $("#filtroCondominio"),
      inpBuscar: $("#filtroBuscar"),
      btnAplicar: $("#btnBuscarAplicar"),
      btnLimpiar: $("#btnBuscarLimpiar"),
      pagPrev: $("#btnPagPrev"),
      pagNext: $("#btnPagNext"),
      pagNum: $("#lblPagNum"),
      lblTotal: $("#lblTotal"),
      chips: document.querySelectorAll(".js-ev-chip"),
    };
  }

  function normalizarEstado(v) {
    const s = String(v ?? "").trim().toLowerCase();

    // soporta opciones numéricas
    if (s === "1") return "revision";
    if (s === "2") return "habilitado";
    if (s === "0") return "inactivo";

    // soporta texto
    if (["revision", "en_revision", "en revisión"].includes(s)) return "revision";
    if (["habilitado", "habilitados"].includes(s)) return "habilitado";
    if (["inactivo", "inactivos"].includes(s)) return "inactivo";
    if (["todos", "all"].includes(s)) return "todos";

    return "revision";
  }

  function badgeEstadoUsuario(v) {
    const n = Number(v);
    // estilos: ev-badge ev-review / ev-ok / ev-off (según tu CSS)
    if (n === 2) return `<span class="ev-badge ev-ok"><i class="bi bi-check2-circle"></i> Habilitado</span>`;
    if (n === 0) return `<span class="ev-badge ev-off"><i class="bi bi-slash-circle"></i> Inactivo</span>`;
    return `<span class="ev-badge ev-review"><i class="bi bi-hourglass-split"></i> En revisión</span>`;
  }

  function residenciaTxt(it) {
    const tipo = String(it.tipo_conjunto || it.tipoConjunto || "").toLowerCase();
    if (!tipo) return `<span class="text-muted">—</span>`;

    const dir = it.direccion ? esc(it.direccion) : "—";
    const t = tipo === "condominio" ? "Condominio" : "Urbanización";
    return `<div class="fw-semibold">${t}</div><div class="text-muted small">${dir}</div>`;
  }

  function setLoading(tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 ev-empty">
          <div class="ev-empty-wrap">
            <div class="ev-empty-ico"><i class="bi bi-cloud-arrow-down"></i></div>
            <div class="ev-empty-text">Cargando...</div>
          </div>
        </td>
      </tr>`;
  }

  function setError(tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 ev-empty">Error al cargar datos.</td>
      </tr>`;
  }

  function setEmpty(tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 ev-empty">No hay registros para mostrar.</td>
      </tr>`;
  }

  // =========================
  // Modal: Revisar cuenta
  // =========================
  function ensureModal() {
    const el = $("#modalRevisarCuenta");
    if (!el) return null;

    if (modalInstance) return modalInstance;

    // bootstrap viene por bundle global
    modalInstance = new bootstrap.Modal(el, { backdrop: "static" });
    return modalInstance;
  }

  function hideComprobante() {
    const img = $("#mImgComprobante");
    const pdf = $("#mPdfComprobante");
    const empty = $("#mNoComprobante");
    const link = $("#mLinkComprobante");

    if (img) { img.style.display = "none"; img.src = ""; }
    if (pdf) { pdf.style.display = "none"; pdf.src = ""; }
    if (empty) empty.style.display = "block";
    if (link) { link.style.display = "none"; link.href = "#"; }
  }

  function showComprobante(url) {
    const u = String(url || "").trim();
    if (!u) {
      hideComprobante();
      return;
    }

    const full = u.startsWith("http") ? u : (baseUrl + "/" + u.replace(/^\/+/, ""));

    const img = $("#mImgComprobante");
    const pdf = $("#mPdfComprobante");
    const empty = $("#mNoComprobante");
    const link = $("#mLinkComprobante");

    if (link) { link.style.display = "inline"; link.href = full; }

    const isPdf = full.toLowerCase().includes(".pdf");
    if (empty) empty.style.display = "none";

    if (isPdf) {
      if (img) { img.style.display = "none"; img.src = ""; }
      if (pdf) { pdf.style.display = "block"; pdf.src = full; }
    } else {
      if (pdf) { pdf.style.display = "none"; pdf.src = ""; }
      if (img) { img.style.display = "block"; img.src = full; }
    }
  }

  function fillModalFromItem(it) {
    // Campos del modal
    const mNombre = $("#mNombre");
    const mEmail = $("#mEmail");
    const mBadge = $("#mBadgeEstado");
    const mDoc = $("#mDoc");
    const mTel = $("#mTel");
    const mTipo = $("#mTipoConjunto");
    const mDir = $("#mDireccion");

    if (mNombre) mNombre.textContent = it.nombre || "—";
    if (mEmail) mEmail.textContent = it.email || "—";
    if (mBadge) mBadge.innerHTML = badgeEstadoUsuario(it.estado);

    if (mDoc) mDoc.textContent = it.documento || "—";
    if (mTel) mTel.textContent = it.telefono || "—";
    if (mTipo) mTipo.textContent = it.tipo_conjunto || it.tipoConjunto || "—";
    if (mDir) mDir.textContent = it.direccion || "—";

    // Comprobante (varios nombres posibles)
    const comp =
      it.comprobante_domicilio ||
      it.comprobante ||
      it.comprobante_url ||
      it.url_comprobante ||
      it.ruta_comprobante ||
      "";

    showComprobante(comp);
  }

  async function tryFetchDetalleUsuario(id) {
    // Si tu backend tiene endpoint detalle, lo usamos.
    // Si no existe, fallará y se hará fallback al item de la tabla.
    const url = new URL(`${baseUrl}/api/soporte/usuarios/${id}`, window.location.origin);
    url.searchParams.set("_", String(Date.now()));

    const resp = await fetch(url.toString(), {
      method: "GET",
      headers: { "X-Partial": "1" },
      credentials: "include",
      cache: "no-store",
    });

    const json = await resp.json().catch(() => null);
    if (!resp.ok || !json || json.ok !== true) throw new Error("detalle no disponible");
    return json.data || null;
  }

  async function openRevisarFromRow(btn) {
    const id = Number(btn.getAttribute("data-id") || 0);
    if (!id) return;

    currentId = id;

    // Fallback rápido: datos embebidos en data-*
    const it = {
      codigo_usuario: id,
      nombre: btn.getAttribute("data-nombre") || "",
      email: btn.getAttribute("data-email") || "",
      documento: btn.getAttribute("data-doc") || "",
      telefono: btn.getAttribute("data-tel") || "",
      tipo_conjunto: btn.getAttribute("data-tipo_conjunto") || "",
      direccion: btn.getAttribute("data-direccion") || "",
      estado: Number(btn.getAttribute("data-estado") || 1),
      comprobante_domicilio: btn.getAttribute("data-comprobante") || "",
    };

    // Pintar algo ya (para UX) y luego intentar enriquecer
    fillModalFromItem(it);

    const modal = ensureModal();
    if (modal) modal.show();

    // Intento opcional: detalle backend
    try {
      const det = await tryFetchDetalleUsuario(id);
      if (det) {
        // Normaliza: si backend devuelve otras keys, las mapeamos
        const merged = {
          ...it,
          ...det,
          // por si viene con nombres alternativos:
          nombre: det.nombre || det.usuario_nombre || it.nombre,
          email: det.email || det.usuario_email || it.email,
          documento: det.documento || det.usuario_documento || it.documento,
          telefono: det.telefono || det.usuario_telefono || it.telefono,
          tipo_conjunto: det.tipo_conjunto || det.tipoConjunto || it.tipo_conjunto,
          direccion: det.direccion || det.direccion_residencia || it.direccion,
          comprobante_domicilio:
            det.comprobante_domicilio ||
            det.comprobante ||
            det.comprobante_url ||
            it.comprobante_domicilio,
        };
        fillModalFromItem(merged);
      }
    } catch (_) {
      // Silencioso: nos quedamos con el fallback del row
    }
  }

  // =========================
  // Render tabla
  // =========================
  function renderRows(tbody, items) {
    if (!items || !items.length) {
      setEmpty(tbody);
      return;
    }

    tbody.innerHTML = items
      .map((it) => {
        const id = Number(it.codigo_usuario || it.id || it.usuario_id);
        const nombre = esc(it.nombre || it.usuario_nombre || "—");
        const email = esc(it.email || it.usuario_email || "—");
        const doc = esc(it.documento || it.usuario_documento || "—");
        const tel = esc(it.telefono || it.usuario_telefono || "—");

        const tipoConjunto = esc(it.tipo_conjunto || it.tipoConjunto || "");
        const direccion = esc(it.direccion || it.direccion_residencia || "");
        const estado = Number(it.estado ?? 1);

        const comp =
          esc(
            it.comprobante_domicilio ||
              it.comprobante ||
              it.comprobante_url ||
              it.url_comprobante ||
              it.ruta_comprobante ||
              ""
          );

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

            <td>${residenciaTxt({
              tipo_conjunto: tipoConjunto,
              direccion: direccion
            })}</td>

            <td class="text-center">
              ${badgeEstadoUsuario(estado)}
            </td>

            <td class="text-end">
              <div class="d-inline-flex gap-2">
                <button
                  type="button"
                  class="btn btn-sm ev-btn-atender js-ev-revisar"
                  data-id="${id}"
                  data-nombre="${nombre}"
                  data-email="${email}"
                  data-doc="${doc}"
                  data-tel="${tel}"
                  data-tipo_conjunto="${tipoConjunto}"
                  data-direccion="${direccion}"
                  data-estado="${estado}"
                  data-comprobante="${comp}"
                >Revisar</button>

                <button
                  type="button"
                  class="btn btn-sm btn-outline-danger js-ev-set-estado"
                  data-id="${id}"
                  data-estado="0"
                >Inactivar</button>
              </div>
            </td>
          </tr>
        `;
      })
      .join("");
  }

  // =========================
  // API endpoints
  // =========================
  function endpointList(modo) {
    if ((modo || "").toLowerCase().includes("res")) {
      return `${baseUrl}/api/soporte/residencias`;
    }
    return `${baseUrl}/api/soporte/usuarios`;
  }

  async function load(state) {
    const tbody = getTbody();
    if (!tbody) return;

    setLoading(tbody);

    const { pagNum, lblTotal, pagPrev, pagNext } = getControls();

    try {
      const url = new URL(endpointList(state.modo), window.location.origin);

      url.searchParams.set("estado", normalizarEstado(state.estado));
      url.searchParams.set("q", state.q || "");
      url.searchParams.set("page", String(state.page));
      url.searchParams.set("limit", String(state.limit));

      // Filtros opcionales: backend puede ignorarlos si no los usa (no rompe)
      if (state.conjunto && state.conjunto !== "todos") {
        url.searchParams.set("conjunto", String(state.conjunto));
      }
      if (state.condominio) {
        url.searchParams.set("condominio", String(state.condominio));
      }

      url.searchParams.set("_", String(Date.now())); // cache-bust

      const resp = await fetch(url.toString(), {
        method: "GET",
        headers: { "X-Partial": "1" },
        credentials: "include",
        cache: "no-store",
      });

      const json = await resp.json().catch(() => null);
      if (!resp.ok || !json || json.ok !== true) {
        throw new Error("API not ok / HTTP " + resp.status);
      }

      const items = json?.data?.items || [];
      const total = Number(json?.data?.total || 0);

      renderRows(tbody, items);

      // UI paginado
      if (pagNum) pagNum.textContent = String(state.page);
      if (lblTotal) lblTotal.textContent = String(total);

      if (pagPrev) pagPrev.disabled = state.page <= 1;
      if (pagNext) {
        const maxPage = Math.max(1, Math.ceil(total / state.limit));
        pagNext.disabled = state.page >= maxPage;
      }
    } catch (e) {
      console.error("[EV][AtenderCuentas] load error:", e);
      setError(tbody);
    }
  }

  async function postEstado(id, estado) {
    try {
      const url = `${baseUrl}/api/soporte/usuarios/${id}/estado`;
      const resp = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-Partial": "1" },
        credentials: "include",
        cache: "no-store",
        body: JSON.stringify({ estado: Number(estado) }),
      });

      const json = await resp.json().catch(() => null);
      if (!resp.ok || !json || json.ok !== true) {
        throw new Error("No se pudo actualizar estado.");
      }

      // refresca lista
      window.EV_AtenderCuentasUsuario?.refresh?.();
    } catch (e) {
      console.error("[EV][AtenderCuentas] postEstado error:", e);
      alert("No se pudo actualizar el estado. Revisa consola/logs.");
    }
  }

  // =========================
  // Chips UI
  // =========================
  function syncChips(estado) {
    const { chips } = getControls();
    if (!chips || !chips.length) return;

    chips.forEach((b) => {
      const e = normalizarEstado(b.getAttribute("data-estado"));
      const active = e === normalizarEstado(estado);

      b.classList.toggle("ev-chip-active", active);
      b.setAttribute("aria-pressed", active ? "true" : "false");
    });
  }

  // =========================
  // Binding de eventos
  // =========================
  function bind(state) {
    const {
      selEstado,
      selModo,
      selConjunto,
      selCondominio,
      inpBuscar,
      btnAplicar,
      btnLimpiar,
      pagPrev,
      pagNext,
      chips,
    } = getControls();

    // Delegación general
    if (!document.body.dataset.evAtenderBound) {
      document.body.dataset.evAtenderBound = "1";

      document.addEventListener("click", (ev) => {
        // Revisar (abre modal)
        const btnRev = ev.target.closest(".js-ev-revisar");
        if (btnRev) {
          ev.preventDefault();
          openRevisarFromRow(btnRev);
          return;
        }

        // Cambiar estado (inactivar rápido)
        const btn = ev.target.closest(".js-ev-set-estado");
        if (btn) {
          ev.preventDefault();
          const id = btn.getAttribute("data-id");
          const est = btn.getAttribute("data-estado");
          if (!id || !est) return;
          postEstado(Number(id), Number(est));
          return;
        }

        // Chips
        const chip = ev.target.closest(".js-ev-chip");
        if (chip) {
          ev.preventDefault();
          const est = normalizarEstado(chip.getAttribute("data-estado"));
          state.estado = est;
          state.page = 1;

          if (selEstado) selEstado.value = est;
          syncChips(est);

          load(state);
          return;
        }
      });
    }

    // Modal buttons
    const btnAprobar = $("#btnModalAprobar");
    const btnInactivar = $("#btnModalInactivar");

    if (btnAprobar && !btnAprobar.dataset.evBound) {
      btnAprobar.dataset.evBound = "1";
      btnAprobar.addEventListener("click", () => {
        if (!currentId) return;
        postEstado(currentId, 2); // Aprobar / Habilitar
      });
    }

    if (btnInactivar && !btnInactivar.dataset.evBound) {
      btnInactivar.dataset.evBound = "1";
      btnInactivar.addEventListener("click", () => {
        if (!currentId) return;
        postEstado(currentId, 0); // Inactivar
      });
    }

    // Selects
    if (selEstado && !selEstado.dataset.evBound) {
      selEstado.dataset.evBound = "1";
      selEstado.addEventListener("change", () => {
        state.estado = selEstado.value;
        state.page = 1;
        syncChips(state.estado);
      });
    }

    if (selModo && !selModo.dataset.evBound) {
      selModo.dataset.evBound = "1";
      selModo.addEventListener("change", () => {
        state.modo = selModo.value;
        state.page = 1;
      });
    }

    if (selConjunto && !selConjunto.dataset.evBound) {
      selConjunto.dataset.evBound = "1";
      selConjunto.addEventListener("change", () => {
        state.conjunto = selConjunto.value;
        state.page = 1;

        // Si el conjunto cambia a "todos", resetea condominio
        if (state.conjunto === "todos" && selCondominio) {
          selCondominio.value = "";
          state.condominio = "";
        }
      });
    }

    if (selCondominio && !selCondominio.dataset.evBound) {
      selCondominio.dataset.evBound = "1";
      selCondominio.addEventListener("change", () => {
        state.condominio = selCondominio.value || "";
        state.page = 1;
      });
    }

    // Buscar (solo setea state; aplicar lo hace el botón)
    if (inpBuscar && !inpBuscar.dataset.evBound) {
      inpBuscar.dataset.evBound = "1";
      inpBuscar.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          state.q = inpBuscar.value.trim();
          state.page = 1;
          load(state);
        }
      });
    }

    // Aplicar / Limpiar
    if (btnAplicar && !btnAplicar.dataset.evBound) {
      btnAplicar.dataset.evBound = "1";
      btnAplicar.addEventListener("click", () => {
        state.q = inpBuscar ? inpBuscar.value.trim() : "";
        state.page = 1;
        // sincronia chips/estado
        syncChips(state.estado);
        load(state);
      });
    }

    if (btnLimpiar && !btnLimpiar.dataset.evBound) {
      btnLimpiar.dataset.evBound = "1";
      btnLimpiar.addEventListener("click", () => {
        if (inpBuscar) inpBuscar.value = "";
        state.q = "";
        state.page = 1;

        // reset filtros a defaults razonables sin romper
        if (selModo) selModo.value = "usuarios";
        if (selEstado) selEstado.value = "revision";
        if (selConjunto) selConjunto.value = "todos";
        if (selCondominio) selCondominio.value = "";

        state.modo = "usuarios";
        state.estado = "revision";
        state.conjunto = "todos";
        state.condominio = "";

        syncChips(state.estado);
        load(state);
      });
    }

    // Paginación
    if (pagPrev && !pagPrev.dataset.evBound) {
      pagPrev.dataset.evBound = "1";
      pagPrev.addEventListener("click", () => {
        if (state.page > 1) {
          state.page -= 1;
          load(state);
        }
      });
    }

    if (pagNext && !pagNext.dataset.evBound) {
      pagNext.dataset.evBound = "1";
      pagNext.addEventListener("click", () => {
        state.page += 1;
        load(state);
      });
    }

    // Si existe NodeList de chips, inicializa aria/active
    if (chips && chips.length) syncChips(state.estado);
  }

  // =========================
  // Init
  // =========================
  function init() {
    const tbody = getTbody();
    if (!tbody) return false;

    const { selEstado, selModo, selConjunto, selCondominio, inpBuscar } = getControls();

    const state = {
      modo: selModo ? selModo.value : "usuarios",
      estado: selEstado ? selEstado.value : "revision",
      conjunto: selConjunto ? selConjunto.value : "todos",
      condominio: selCondominio ? (selCondominio.value || "") : "",
      q: inpBuscar ? inpBuscar.value.trim() : "",
      page: 1,
      limit: 10,
    };

    bind(state);
    load(state);

    // API pública
    window.EV_AtenderCuentasUsuario = window.EV_AtenderCuentasUsuario || {};
    window.EV_AtenderCuentasUsuario.refresh = () => load(state);
    window.EV_AtenderCuentasUsuario.init = init;

    return true;
  }

  function startObserver() {
    if (observer) return;

    observer = new MutationObserver(() => {
      const ok = init();
      if (ok && observer) {
        observer.disconnect();
        observer = null;
      }
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
  }

  const okNow = init();
  if (!okNow) startObserver();

  // bfcache
  window.addEventListener("pageshow", () => {
    if (getTbody()) window.EV_AtenderCuentasUsuario?.refresh?.();
  });
})();
