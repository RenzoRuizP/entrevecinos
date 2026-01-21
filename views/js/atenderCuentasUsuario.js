// views/js/atenderCuentasUsuario.js
(function () {
  "use strict";

  const baseUrl = (window.BASE_URL || "").replace(/\/+$/, "");
  if (!baseUrl) return;

  // Estado vivo (para refresh)
  let currentState = null;

  // Para detectar reinserción del tbody (AJAX parcial)
  let lastTbody = null;
  let observer = null;

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

  function getControls() {
    return {
      selEstado: $("#filtroEstado"),
      inpBuscar: $("#filtroBuscar"),
      selModo: $("#filtroModo"),
      pagPrev: $("#btnPagPrev"),
      pagNext: $("#btnPagNext"),
      pagNum: $("#lblPagNum"),
      lblTotal: $("#lblTotal"),
    };
  }

  function normalizarEstado(v) {
    const s = String(v ?? "").trim().toLowerCase();

    // numéricos (por compatibilidad con UI)
    if (s === "1") return "revision";
    if (s === "2") return "habilitado";
    if (s === "0") return "inactivo";

    // texto
    if (["revision", "en_revision", "en revisión"].includes(s)) return "revision";
    if (["habilitado", "habilitados"].includes(s)) return "habilitado";
    if (["inactivo", "inactivos"].includes(s)) return "inactivo";
    if (["todos", "all"].includes(s)) return "todos";

    return "revision";
  }

  function badgeEstadoUsuario(v) {
    const n = Number(v);
    if (n === 2) return `<span class="ev-badge ev-badge-media">Habilitado</span>`;
    if (n === 0) return `<span class="ev-badge ev-badge-baja">Inactivo</span>`;
    return `<span class="ev-badge ev-badge-alta">En revisión</span>`;
  }

  function residenciaTxt(it) {
    const tipo = (it.tipo_conjunto || "").toLowerCase();
    if (!tipo) return `<span class="text-muted">—</span>`;

    const dir = it.direccion ? esc(it.direccion) : "—";
    const t = tipo === "condominio" ? "Condominio" : "Urbanización";
    return `<div class="fw-semibold">${t}</div><div class="text-muted small">${dir}</div>`;
  }

  function setLoading(tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 ev-empty">Cargando...</td>
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

  function renderRows(tbody, items) {
    if (!items || !items.length) {
      setEmpty(tbody);
      return;
    }

    tbody.innerHTML = items
      .map((it) => {
        const id = Number(it.codigo_usuario);
        const nombre = esc(it.nombre);
        const email = esc(it.email);
        const doc = esc(it.documento || "—");
        const tel = esc(it.telefono || "—");

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
              ${badgeEstadoUsuario(it.estado)}
            </td>

            <td class="text-end">
              <div class="d-inline-flex gap-2">
                <button class="btn btn-sm ev-btn-atender js-ev-set-estado" data-id="${id}" data-estado="2">Habilitar</button>
                <button class="btn btn-sm btn-outline-danger js-ev-set-estado" data-id="${id}" data-estado="0">Inactivar</button>
              </div>
            </td>
          </tr>
        `;
      })
      .join("");
  }

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
      url.searchParams.set("_", String(Date.now())); // cache-bust real

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

      if (pagNum) pagNum.textContent = String(state.page);
      if (lblTotal) lblTotal.textContent = String(total);

      if (pagPrev) pagPrev.disabled = state.page <= 1;
      if (pagNext) {
        const maxPage = Math.max(1, Math.ceil(total / state.limit));
        pagNext.disabled = state.page >= maxPage;
      }
    } catch (e) {
      console.error("[EV][AtenderCuentasUsuario] load error:", e);
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

      refresh();
    } catch (e) {
      console.error("[EV][AtenderCuentasUsuario] postEstado error:", e);
      alert("No se pudo actualizar el estado. Revisa consola/logs.");
    }
  }

  function bind(state) {
    const { selEstado, inpBuscar, selModo, pagPrev, pagNext } = getControls();

    // Delegación global (solo una vez en toda la app)
    if (!document.body.dataset.evAtCtasDelegBound) {
      document.body.dataset.evAtCtasDelegBound = "1";
      document.addEventListener("click", (ev) => {
        const btn = ev.target.closest(".js-ev-set-estado");
        if (!btn) return;

        const id = btn.getAttribute("data-id");
        const est = btn.getAttribute("data-estado");
        if (!id || !est) return;

        postEstado(Number(id), Number(est));
      });
    }

    // Los controles cambian con el parcial (son nodos nuevos), por eso:
    // marcamos el binding en el nodo, no global.
    if (selEstado && !selEstado.dataset.evBound) {
      selEstado.dataset.evBound = "1";
      selEstado.addEventListener("change", () => {
        state.estado = selEstado.value;
        state.page = 1;
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

    if (inpBuscar && !inpBuscar.dataset.evBound) {
      inpBuscar.dataset.evBound = "1";
      let t = null;
      inpBuscar.addEventListener("input", () => {
        clearTimeout(t);
        t = setTimeout(() => {
          state.q = inpBuscar.value.trim();
          state.page = 1;
          load(state);
        }, 250);
      });
    }

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
  }

  function init() {
    const tbody = getTbody();
    if (!tbody) return false;

    // Si el tbody es el mismo, no reinicializar a lo loco
    // (pero igual refresh funciona).
    if (lastTbody === tbody && currentState) return true;

    lastTbody = tbody;

    const { selEstado, selModo } = getControls();

    currentState = {
      modo: selModo ? selModo.value : "usuarios",
      estado: selEstado ? selEstado.value : "revision",
      q: "",
      page: 1,
      limit: 10,
    };

    bind(currentState);
    load(currentState);

    return true;
  }

  function refresh() {
    if (currentState) load(currentState);
    else init();
  }

  // API pública (para que el Shell la invoque si desea)
  window.EV_AtenderCuentasUsuario = window.EV_AtenderCuentasUsuario || {};
  window.EV_AtenderCuentasUsuario.init = init;
  window.EV_AtenderCuentasUsuario.refresh = refresh;

  function startObserver() {
    if (observer) return;

    observer = new MutationObserver(() => {
      // Si aparece/reaparece el tbody por carga AJAX: init() lo levanta.
      init();
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
  }

  // 1) intento inmediato
  init();

  // 2) observa siempre (porque el usuario puede entrar/salir/volver a entrar al módulo)
  startObserver();

  // 3) bfcache (volver atrás/adelante)
  window.addEventListener("pageshow", () => {
    if (getTbody()) refresh();
  });
})();
