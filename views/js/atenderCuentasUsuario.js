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

  function byId(id) {
    return document.getElementById(id);
  }

  function getTbody() {
    return (
      byId("evUsuariosBody") ||
      byId("tablaUsuariosBody") ||
      document.querySelector("table tbody")
    );
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

  function normalizarEstado(v) {
    const s = String(v ?? "").trim().toLowerCase();

    // Soporta números (por si en algún lado llega)
    if (s === "1") return "revision";
    if (s === "2") return "habilitado";
    if (s === "0") return "inactivo";

    if (["revision", "en_revision", "en revisión", "en revision"].includes(s)) return "revision";
    if (["habilitado", "habilitados"].includes(s)) return "habilitado";
    if (["inactivo", "inactivos"].includes(s)) return "inactivo";
    if (["todos", "all"].includes(s)) return "todos";
    return "revision";
  }

  // Badges según tu CSS: ev-badge + ev-review/ev-ok/ev-off
  function badgeEstadoUsuario(estado) {
    const n = Number(estado);
    if (n === 2) return `<span class="ev-badge ev-ok"><i class="bi bi-check2-circle"></i> Habilitado</span>`;
    if (n === 0) return `<span class="ev-badge ev-off"><i class="bi bi-slash-circle"></i> Inactivo</span>`;
    return `<span class="ev-badge ev-review"><i class="bi bi-hourglass-split"></i> En revisión</span>`;
  }

  function residenciaTxt(it) {
    const tipoRaw =
      it.tipo_conjunto ||
      it.tipoConjunto ||
      it.conjunto_tipo ||
      it.tipo ||
      "";

    const tipo = String(tipoRaw).toLowerCase();
    if (!tipo) return `<span class="text-muted">—</span>`;

    const dir = it.direccion || it.direccion_residencia || it.dir || "";
    const t = tipo.includes("cond") ? "Condominio" : "Urbanización";

    return `<div class="fw-semibold">${esc(t)}</div><div class="text-muted small">${esc(dir || "—")}</div>`;
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
  // Endpoints
  // =========================
  function endpointList(modo) {
    const m = String(modo || "").toLowerCase();
    if (m.includes("res")) return `${baseUrl}/api/soporte/residencias`;
    return `${baseUrl}/api/soporte/usuarios`;
  }

  // =========================
  // Render tabla
  // =========================
  function renderRows(tbody, items, state) {
    if (!Array.isArray(items) || items.length === 0) {
      setEmpty(tbody);
      return;
    }

    tbody.innerHTML = items
      .map((it) => {
        const id =
          Number(it.codigo_usuario ?? it.id ?? it.usuario_id ?? 0);

        const nombre = esc(it.nombre || it.usuario_nombre || "—");
        const email = esc(it.email || it.usuario_email || "—");
        const doc = esc(it.documento || it.usuario_documento || "—");
        const tel = esc(it.telefono || it.usuario_telefono || "—");

        const tipoConjunto = esc(it.tipo_conjunto || it.tipoConjunto || "");
        const direccion = esc(it.direccion || it.direccion_residencia || "");
        const estado = Number(it.estado ?? it.usuario_estado ?? 1);

        // Comprobante (varios posibles nombres)
        const comprobante =
          it.comprobante_domicilio ||
          it.comprobante ||
          it.comprobante_url ||
          it.url_comprobante ||
          it.comprobanteRuta ||
          "";

        // Botón Revisar (abre modal)
        const btnRevisar = `
          <button
            type="button"
            class="btn btn-sm ev-btn-orange js-ev-revisar"
            data-id="${id}"
            data-nombre="${esc(it.nombre || it.usuario_nombre || "")}"
            data-email="${esc(it.email || it.usuario_email || "")}"
            data-doc="${esc(it.documento || it.usuario_documento || "")}"
            data-tel="${esc(it.telefono || it.usuario_telefono || "")}"
            data-tipo_conjunto="${tipoConjunto}"
            data-direccion="${direccion}"
            data-estado="${estado}"
            data-comprobante="${esc(comprobante || "")}"
          >Revisar</button>
        `;

        const btnInactivar = `
          <button class="btn btn-sm btn-outline-danger js-ev-set-estado" data-id="${id}" data-estado="0">Inactivar</button>
        `;

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

            <td>${residenciaTxt(it)}</td>

            <td class="text-center">
              ${badgeEstadoUsuario(estado)}
            </td>

            <td class="text-end">
              <div class="d-inline-flex gap-2">
                ${btnRevisar}
                ${btnInactivar}
              </div>
            </td>
          </tr>
        `;
      })
      .join("");
  }

  // =========================
  // Modal
  // =========================
  function ensureModal() {
    const el = byId("modalRevisarCuenta");
    if (!el) return null;

    if (modalInstance) return modalInstance;

    // bootstrap global ya existe en tu shell
    modalInstance = new bootstrap.Modal(el, { backdrop: "static" });
    return modalInstance;
  }

  function isPdf(url) {
    return /\.pdf(\?|#|$)/i.test(String(url || ""));
  }

  function absUrlMaybe(u) {
    const url = String(u || "").trim();
    if (!url) return "";
    // Si ya es absoluta
    if (/^https?:\/\//i.test(url)) return url;
    // Si es relativa: la llevamos a BASE_URL
    if (url.startsWith("/")) return baseUrl + url;
    return baseUrl + "/" + url;
  }

  function fillModalFromButton(btn) {
    currentId = Number(btn.getAttribute("data-id") || 0);

    const nombre = btn.getAttribute("data-nombre") || "—";
    const email = btn.getAttribute("data-email") || "—";
    const doc = btn.getAttribute("data-doc") || "—";
    const tel = btn.getAttribute("data-tel") || "—";
    const tipoConjunto = btn.getAttribute("data-tipo_conjunto") || "—";
    const direccion = btn.getAttribute("data-direccion") || "—";
    const estado = Number(btn.getAttribute("data-estado") || 1);
    const comprobanteRaw = btn.getAttribute("data-comprobante") || "";

    const mNombre = byId("mNombre");
    const mEmail = byId("mEmail");
    const mDoc = byId("mDoc");
    const mTel = byId("mTel");
    const mTipoConjunto = byId("mTipoConjunto");
    const mDireccion = byId("mDireccion");
    const mBadgeEstado = byId("mBadgeEstado");
    const mObsTexto = byId("mObsTexto");

    if (mNombre) mNombre.textContent = nombre;
    if (mEmail) mEmail.textContent = email;
    if (mDoc) mDoc.textContent = doc || "—";
    if (mTel) mTel.textContent = tel || "—";
    if (mTipoConjunto) mTipoConjunto.textContent = tipoConjunto || "—";
    if (mDireccion) mDireccion.textContent = direccion || "—";
    if (mBadgeEstado) mBadgeEstado.innerHTML = badgeEstadoUsuario(estado);

    // limpiar observación
    if (mObsTexto) mObsTexto.value = "";

    // comprobante
    const mLink = byId("mLinkComprobante");
    const mImg = byId("mImgComprobante");
    const mPdf = byId("mPdfComprobante");
    const mEmpty = byId("mNoComprobante");

    const compUrl = absUrlMaybe(comprobanteRaw);

    // reset
    if (mLink) { mLink.style.display = "none"; mLink.setAttribute("href", "#"); }
    if (mImg) { mImg.style.display = "none"; mImg.setAttribute("src", ""); }
    if (mPdf) { mPdf.style.display = "none"; mPdf.setAttribute("src", ""); }
    if (mEmpty) mEmpty.style.display = "block";

    if (compUrl) {
      if (mLink) { mLink.style.display = "inline"; mLink.setAttribute("href", compUrl); }
      if (isPdf(compUrl)) {
        if (mPdf) { mPdf.style.display = "block"; mPdf.setAttribute("src", compUrl); }
      } else {
        if (mImg) { mImg.style.display = "block"; mImg.setAttribute("src", compUrl); }
      }
      if (mEmpty) mEmpty.style.display = "none";
    }
  }

  // =========================
  // API actions
  // =========================
  async function postEstado(id, estado) {
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
    return json;
  }

  async function postObservacion(id, observacion) {
    const obs = String(observacion || "").trim();
    if (!obs) {
      alert("Ingresa una observación.");
      return;
    }

    const url = `${baseUrl}/api/cuenta-observada/${id}/observar`;

    const resp = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Partial": "1"
      },
      credentials: "include",
      cache: "no-store",
      body: JSON.stringify({ observacion: obs }),
    });

    const json = await resp.json().catch(() => null);

    if (!resp.ok || !json || json.ok !== true) {
      throw new Error(json?.mensaje || "No se pudo registrar observación.");
    }

    return json;
  }


  // =========================
  // Carga de data
  // =========================
  async function load(state) {
    const tbody = getTbody();
    if (!tbody) return;

    const { pagNum, lblTotal, pagPrev, pagNext } = getControls();
    setLoading(tbody);

    try {
      const url = new URL(endpointList(state.modo), window.location.origin);
      url.searchParams.set("estado", normalizarEstado(state.estado));
      url.searchParams.set("q", state.q || "");
      url.searchParams.set("page", String(state.page));
      url.searchParams.set("limit", String(state.limit));

      // filtros extra (backend puede ignorar si no aplica)
      if (state.conjunto && state.conjunto !== "todos") url.searchParams.set("conjunto", state.conjunto);
      if (state.condominio) url.searchParams.set("condominio", state.condominio);

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

      renderRows(tbody, items, state);

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

  // =========================
  // UI: chips y filtros
  // =========================
  function syncChipsUI(estadoNormalized) {
    const { chips } = getControls();
    if (!chips) return;

    chips.forEach((b) => {
      const e = normalizarEstado(b.getAttribute("data-estado"));
      const active = e === estadoNormalized;

      b.classList.toggle("ev-chip-active", active);
      b.setAttribute("aria-pressed", active ? "true" : "false");
    });
  }

  function setupCondominioEnable() {
    const { selConjunto, selCondominio } = getControls();
    if (!selConjunto || !selCondominio) return;

    const v = String(selConjunto.value || "").toLowerCase();
    const enable = v === "condominio" || v === "urbanizacion";

    selCondominio.disabled = !enable;
    if (!enable) selCondominio.value = "";
  }

  // =========================
  // Bind
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

    // Delegación (una sola vez global)
    if (!document.body.dataset.evAtenderUsuariosBound) {
      document.body.dataset.evAtenderUsuariosBound = "1";

      document.addEventListener("click", async (ev) => {
        // Revisar (modal)
        const btnRev = ev.target.closest(".js-ev-revisar");
        if (btnRev) {
          const m = ensureModal();
          if (!m) return;

          fillModalFromButton(btnRev);
          m.show();
          return;
        }

        // Inactivar desde tabla
        const btnSet = ev.target.closest(".js-ev-set-estado");
        if (btnSet) {
          const id = Number(btnSet.getAttribute("data-id") || 0);
          const est = Number(btnSet.getAttribute("data-estado") || 0);
          if (!id) return;

          try {
            await postEstado(id, est);
            await load(state);
          } catch (e) {
            console.error("[EV][AtenderCuentas] postEstado error:", e);
            alert("No se pudo actualizar el estado. Revisa consola/logs.");
          }
          return;
        }
      });
    }

    // Chips
    if (chips && chips.length) {
      chips.forEach((b) => {
        if (b.dataset.evBound) return;
        b.dataset.evBound = "1";

        b.addEventListener("click", () => {
          const e = normalizarEstado(b.getAttribute("data-estado"));
          state.estado = e;
          state.page = 1;

          if (selEstado) selEstado.value = e; // sync select
          syncChipsUI(e);

          load(state);
        });
      });
    }

    // Selects
    if (selEstado && !selEstado.dataset.evBound) {
      selEstado.dataset.evBound = "1";
      selEstado.addEventListener("change", () => {
        state.estado = normalizarEstado(selEstado.value);
        state.page = 1;
        syncChipsUI(state.estado);
        load(state);
      });
    }

    if (selModo && !selModo.dataset.evBound) {
      selModo.dataset.evBound = "1";
      selModo.addEventListener("change", () => {
        state.modo = selModo.value;
        state.page = 1;
        load(state);
      });
    }

    if (selConjunto && !selConjunto.dataset.evBound) {
      selConjunto.dataset.evBound = "1";
      selConjunto.addEventListener("change", () => {
        state.conjunto = selConjunto.value || "todos";
        state.condominio = "";
        setupCondominioEnable();
        state.page = 1;
        // No auto-load (opcional). Tú pediste aplicar con botón:
        // load(state);
      });
    }

    if (selCondominio && !selCondominio.dataset.evBound) {
      selCondominio.dataset.evBound = "1";
      selCondominio.addEventListener("change", () => {
        state.condominio = selCondominio.value || "";
        // no auto-load (se aplica con botón)
      });
    }

    // Buscar: Enter aplica filtros
    if (inpBuscar && !inpBuscar.dataset.evBound) {
      inpBuscar.dataset.evBound = "1";
      inpBuscar.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          state.q = (inpBuscar.value || "").trim();
          state.page = 1;
          load(state);
        }
      });
    }

    // Botones aplicar / limpiar
    if (btnAplicar && !btnAplicar.dataset.evBound) {
      btnAplicar.dataset.evBound = "1";
      btnAplicar.addEventListener("click", () => {
        state.q = (inpBuscar ? inpBuscar.value : "").trim();
        state.estado = normalizarEstado(selEstado ? selEstado.value : state.estado);
        state.modo = selModo ? selModo.value : state.modo;
        state.conjunto = selConjunto ? selConjunto.value : state.conjunto;
        state.condominio = selCondominio ? selCondominio.value : state.condominio;

        state.page = 1;
        syncChipsUI(state.estado);
        setupCondominioEnable();
        load(state);
      });
    }

    if (btnLimpiar && !btnLimpiar.dataset.evBound) {
      btnLimpiar.dataset.evBound = "1";
      btnLimpiar.addEventListener("click", () => {
        if (selModo) selModo.value = "usuarios";
        if (selEstado) selEstado.value = "revision";
        if (selConjunto) selConjunto.value = "todos";
        if (selCondominio) selCondominio.value = "";
        if (inpBuscar) inpBuscar.value = "";

        state.modo = "usuarios";
        state.estado = "revision";
        state.conjunto = "todos";
        state.condominio = "";
        state.q = "";
        state.page = 1;

        setupCondominioEnable();
        syncChipsUI(state.estado);
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

    // Modal actions (una sola vez)
    const btnModalAprobar = byId("btnModalAprobar");
    const btnModalInactivar = byId("btnModalInactivar");
    const btnModalObservar = byId("btnModalObservar");
    const txtObs = byId("mObsTexto");

    if (btnModalAprobar && !btnModalAprobar.dataset.evBound) {
      btnModalAprobar.dataset.evBound = "1";
      btnModalAprobar.addEventListener("click", async () => {
        if (!currentId) return;

        try {
          await postEstado(currentId, 2); // aprobar => habilitar
          ensureModal()?.hide();
          await load(state);
        } catch (e) {
          console.error("[EV][AtenderCuentas] aprobar error:", e);
          alert("No se pudo aprobar. Revisa consola/logs.");
        }
      });
    }

    if (btnModalInactivar && !btnModalInactivar.dataset.evBound) {
      btnModalInactivar.dataset.evBound = "1";
      btnModalInactivar.addEventListener("click", async () => {
        if (!currentId) return;

        try {
          await postEstado(currentId, 0); // inactivar
          ensureModal()?.hide();
          await load(state);
        } catch (e) {
          console.error("[EV][AtenderCuentas] inactivar error:", e);
          alert("No se pudo inactivar. Revisa consola/logs.");
        }
      });
    }

    // ✅ OBSERVAR (RESTAURADO)
    if (btnModalObservar && !btnModalObservar.dataset.evBound) {
      btnModalObservar.dataset.evBound = "1";
      btnModalObservar.addEventListener("click", async () => {
        if (!currentId) return;

        try {
          const obs = txtObs ? txtObs.value : "";
          await postObservacion(currentId, obs);
          ensureModal()?.hide();
          await load(state);
        } catch (e) {
          console.error("[EV][AtenderCuentas] observar error:", e);
          alert("No se pudo observar. Revisa consola/logs.");
        }
      });
    }

    setupCondominioEnable();
    syncChipsUI(normalizarEstado(state.estado));
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
      estado: selEstado ? normalizarEstado(selEstado.value) : "revision",
      conjunto: selConjunto ? selConjunto.value : "todos",
      condominio: selCondominio ? selCondominio.value : "",
      q: inpBuscar ? (inpBuscar.value || "").trim() : "",
      page: 1,
      limit: 10,
    };

    // Evitar re-init agresivo, pero permitir refresh
    bind(state);
    load(state);

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

  // bfcache (volver atrás/adelante)
  window.addEventListener("pageshow", () => {
    if (getTbody()) window.EV_AtenderCuentasUsuario?.refresh?.();
  });
})();
