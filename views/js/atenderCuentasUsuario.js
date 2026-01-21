// views/js/atenderCuentasUsuario.js
(function () {
  const baseUrl = (window.BASE_URL || "").replace(/\/+$/, "");
  if (!baseUrl) return;

  const tbody =
    document.querySelector("#evUsuariosBody") ||
    document.querySelector("#tablaUsuariosBody") ||
    document.querySelector("table tbody");

  if (!tbody) return;

  const selEstado = document.querySelector("#filtroEstado");
  const inpBuscar = document.querySelector("#filtroBuscar");
  const selModo   = document.querySelector("#filtroModo"); // Usuarios / Residencias
  const pagPrev   = document.querySelector("#btnPagPrev");
  const pagNext   = document.querySelector("#btnPagNext");
  const pagNum    = document.querySelector("#lblPagNum");
  const lblTotal  = document.querySelector("#lblTotal");

  let state = {
    modo: (selModo ? selModo.value : "usuarios"),
    estado: (selEstado ? selEstado.value : "1"), // tu UI usa 0/1/2, la API ya lo soporta también
    q: "",
    page: 1,
    limit: 10
  };

  function esc(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function badgeEstadoUsuario(v) {
    const n = Number(v);
    if (n === 2) return `<span class="ev-badge ev-badge-media">habilitado</span>`;
    if (n === 0) return `<span class="ev-badge ev-badge-baja">inactivo</span>`;
    return `<span class="ev-badge ev-badge-alta">en revisión</span>`;
  }

  function residenciaTxt(it) {
    const tipo = (it.tipo_conjunto || "").toLowerCase();
    if (!tipo) return `<span class="text-muted">—</span>`;
    const dir = it.direccion ? esc(it.direccion) : "—";
    const t = tipo === "condominio" ? "Condominio" : "Urbanización";
    return `<div class="fw-semibold">${t}</div><div class="text-muted small">${dir}</div>`;
  }

  function renderRows(items) {
    if (!items || !items.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center py-4 ev-empty">
            No hay registros para mostrar.
          </td>
        </tr>`;
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
        </tr>`;
      })
      .join("");
  }

  function setLoading() {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 ev-empty">
          Cargando...
        </td>
      </tr>`;
  }

  function setError() {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 ev-empty">
          Error al cargar datos.
        </td>
      </tr>`;
  }

  function isResidenciasMode() {
    return ((state.modo || "").toLowerCase().includes("res"));
  }

  function endpointList() {
    return isResidenciasMode()
      ? `${baseUrl}/api/soporte/residencias`
      : `${baseUrl}/api/soporte/usuarios`;
  }

  function endpointUpdate(id) {
    return isResidenciasMode()
      ? `${baseUrl}/api/soporte/residencias/${id}/estado`
      : `${baseUrl}/api/soporte/usuarios/${id}/estado`;
  }

  async function load() {
    setLoading();

    try {
      const url = new URL(endpointList(), window.location.origin);

      // Enviamos lo que venga (0/1/2 o revision/habilitado/etc).
      // El backend ya lo normaliza.
      url.searchParams.set("estado", String(state.estado || "1"));
      url.searchParams.set("q", state.q || "");
      url.searchParams.set("page", String(state.page));
      url.searchParams.set("limit", String(state.limit));

      const resp = await fetch(url.toString(), {
        method: "GET",
        headers: { "X-Partial": "1" },
        credentials: "include"
      });

      const json = await resp.json().catch(() => null);

      if (!resp.ok) {
        // Si es 403, lo verás en consola como corresponde
        throw new Error((json && (json.mensaje || json.error)) ? (json.mensaje || json.error) : ("HTTP " + resp.status));
      }

      if (!json || json.ok !== true) throw new Error("API not ok");

      const items = json?.data?.items || [];
      const total = Number(json?.data?.total || 0);

      renderRows(items);

      if (pagNum) pagNum.textContent = String(state.page);
      if (lblTotal) lblTotal.textContent = String(total);

      if (pagPrev) pagPrev.disabled = state.page <= 1;
      if (pagNext) {
        const maxPage = Math.max(1, Math.ceil(total / state.limit));
        pagNext.disabled = state.page >= maxPage;
      }
    } catch (e) {
      console.error("[EV][AtenderCuentas] load error:", e);
      setError();
    }
  }

  async function postEstado(id, estado) {
    try {
      const url = endpointUpdate(id);

      const resp = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-Partial": "1" },
        credentials: "include",
        body: JSON.stringify({ estado: Number(estado) })
      });

      const json = await resp.json().catch(() => null);
      if (!resp.ok || !json || json.ok !== true) {
        throw new Error((json && json.mensaje) ? json.mensaje : "No se pudo actualizar estado.");
      }

      await load();
    } catch (e) {
      console.error("[EV][AtenderCuentas] postEstado error:", e);
      alert("No se pudo actualizar el estado. Revisa consola/logs.");
    }
  }

  // Events
  document.addEventListener("click", (ev) => {
    const btn = ev.target.closest(".js-ev-set-estado");
    if (!btn) return;

    const id = btn.getAttribute("data-id");
    const est = btn.getAttribute("data-estado");
    if (!id || !est) return;

    postEstado(Number(id), Number(est));
  });

  if (selEstado) {
    selEstado.addEventListener("change", () => {
      state.estado = selEstado.value;
      state.page = 1;
      load();
    });
  }

  if (selModo) {
    selModo.addEventListener("change", () => {
      state.modo = selModo.value;
      state.page = 1;
      load();
    });
  }

  if (inpBuscar) {
    let t = null;
    inpBuscar.addEventListener("input", () => {
      clearTimeout(t);
      t = setTimeout(() => {
        state.q = inpBuscar.value.trim();
        state.page = 1;
        load();
      }, 250);
    });
  }

  if (pagPrev) {
    pagPrev.addEventListener("click", () => {
      if (state.page > 1) {
        state.page -= 1;
        load();
      }
    });
  }

  if (pagNext) {
    pagNext.addEventListener("click", () => {
      state.page += 1;
      load();
    });
  }

  load();
})();
