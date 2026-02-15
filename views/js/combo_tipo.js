// combo_tipo.js (EV) — robusto con API {ok,data} + SPA safe
(function () {
  // Evita doble carga del mismo script
  if (window.__EV_TIPO_INITED__) return;
  window.__EV_TIPO_INITED__ = true;

  // ==========================
  // Helpers comunes
  // ==========================
  const buildURL = (path) => {
    const base = (window.BASE_URL || "").replace(/\/+$/, "");
    return base + "/" + String(path).replace(/^\/+/, "");
  };

  const resetSelect = (selectEl, placeholder = "Seleccione…", disabled = false) => {
    if (!selectEl) return;
    selectEl.innerHTML = `<option value="" selected disabled>-- ${placeholder} --</option>`;
    selectEl.disabled = !!disabled;
  };

  // Normaliza cualquier respuesta a "array"
  function unwrapArray(payload) {
    // Caso A: API devuelve array directo
    if (Array.isArray(payload)) return payload;

    // Caso B: API devuelve {ok:true, data:[...]}
    if (payload && typeof payload === "object") {
      const inner = payload.data ?? payload.items ?? payload.result ?? null;
      if (Array.isArray(inner)) return inner;
    }
    return null; // inválido
  }

  async function cargarJSON(url) {
    const res = await fetch(url, {
      headers: { "Accept": "application/json" },
      credentials: "same-origin"
    });

    // Manejo de sesiones/cuenta observada (tu router responde JSON)
    if (res.status === 401) {
      const j = await res.json().catch(() => ({}));
      const msg = j?.mensaje || "Tu sesión ha expirado. Vuelve a iniciar sesión.";
      // Puedes cambiar esto por Swal si quieres, pero no rompo nada acá.
      console.warn("[EV] 401:", msg, j);
      throw new Error("UNAUTHORIZED");
    }

    if (res.status === 409) {
      const j = await res.json().catch(() => ({}));
      const msg = j?.mensaje || "Tu cuenta requiere atención.";
      console.warn("[EV] 409:", msg, j);
      // Si el backend manda redirect, lo respetamos
      if (j?.redirect) {
        try { window.location.href = j.redirect; } catch (_) {}
      }
      throw new Error("CUENTA_OBSERVADA");
    }

    if (!res.ok) throw new Error(`HTTP ${res.status} al cargar ${url}`);
    return await res.json();
  }

  const renderCategorias = (comboCategoria, data, selectedValue = "") => {
    if (!comboCategoria) return;

    resetSelect(comboCategoria, "Selecciona una categoría", false);

    const grupos = {};
    data.forEach(row => {
      const g = row.grupo || row.nombre_grupo || "Otros";
      if (!grupos[g]) grupos[g] = [];
      grupos[g].push({
        value: row.codigo_categoria,
        text: row.categoria || row.nombre
      });
    });

    Object.keys(grupos).forEach(nombreGrupo => {
      const og = document.createElement("optgroup");
      og.label = nombreGrupo;
      grupos[nombreGrupo].forEach(it => {
        const op = document.createElement("option");
        op.value = it.value;
        op.textContent = it.text;
        if (selectedValue && String(selectedValue) === String(op.value)) op.selected = true;
        og.appendChild(op);
      });
      comboCategoria.appendChild(og);
    });
  };

  // ==========================
  // Inicialización de un par Tipo/Categoría
  // ids: { tipoId, categoriaId }
  // ==========================
  async function inicializarParTipoCategoria(tipoId, categoriaId) {
    const comboTipo = document.getElementById(tipoId);
    const comboCategoria = document.getElementById(categoriaId);

    if (!comboTipo || !comboCategoria) return false;

    // Evita re-binds por reintentos en SPA
    const bindKey = `__ev_bound_${tipoId}_${categoriaId}`;
    if (comboTipo.dataset[bindKey] === "1") return true;
    comboTipo.dataset[bindKey] = "1";

    const valorRegistradoTipo = comboTipo.dataset.valorRegistrado || "";
    const valorRegistradoCategoria = comboCategoria.dataset.valorRegistrado || "";

    // ---- cargar categorías ----
    const cargarCategoriasPorTipo = async (codigoTipo, preselect = "") => {
      if (!codigoTipo) {
        resetSelect(comboCategoria, "Selecciona un tipo primero", true);
        return;
      }
      resetSelect(comboCategoria, "Cargando categorías...", true);

      try {
        const payload = await cargarJSON(buildURL(`tipos/${encodeURIComponent(codigoTipo)}/categoria_grupo`));
        const arr = unwrapArray(payload);

        if (!arr) {
          console.error("Respuesta inválida categorías:", payload);
          resetSelect(comboCategoria, "Respuesta inválida", true);
          return;
        }

        renderCategorias(comboCategoria, arr, preselect);
        comboCategoria.disabled = false;
      } catch (e) {
        console.error("Error cargando Categorías:", e);
        resetSelect(comboCategoria, "No se pudo cargar categorías", true);
      }
    };

    // Evento change del tipo (una sola vez)
    comboTipo.addEventListener("change", () => {
      const tipo = comboTipo.value;
      const pre = comboCategoria.dataset.valorRegistrado || valorRegistradoCategoria || "";
      cargarCategoriasPorTipo(tipo, pre);
    });

    // ---- cargar tipos ----
    const cargarTipos = async () => {
      resetSelect(comboTipo, "Seleccione Tipos", false);
      try {
        const payload = await cargarJSON(buildURL("tipos"));
        const arr = unwrapArray(payload);

        if (!arr) {
          console.error("Respuesta inválida tipos:", payload);
          resetSelect(comboTipo, "Respuesta inválida", true);
          resetSelect(comboCategoria, "Respuesta inválida", true);
          return;
        }

        comboTipo.innerHTML = `<option value="" selected disabled>-- Seleccione Tipos --</option>`;
        arr.forEach(t => {
          const op = document.createElement("option");
          op.value = t.codigo_tipo;
          op.textContent = t.nombre;
          comboTipo.appendChild(op);
        });

        // Preselección si viene desde dataset
        if (valorRegistradoTipo) comboTipo.value = String(valorRegistradoTipo);

        if (comboTipo.value) {
          comboTipo.dispatchEvent(new Event("change"));
        } else {
          resetSelect(comboCategoria, "Selecciona un tipo primero", true);
        }
      } catch (e) {
        console.error("Error cargando Tipos:", e);
        resetSelect(comboTipo, "Error al cargar Tipos", true);
        resetSelect(comboCategoria, "Error al cargar", true);
      }
    };

    await cargarTipos();
    return true;
  }

  // ==========================
  // Esperar a que existan los combos (SPA)
  // ==========================
  function esperarPar(tipoId, categoriaId) {
    const intentar = async () => {
      const ok = await inicializarParTipoCategoria(tipoId, categoriaId);
      if (!ok) setTimeout(intentar, 120);
    };
    intentar();
  }

  // ==========================
  // Hook global para edición
  // ==========================
  window.evInitComboTipoCategoriaEdit = function (codTipo, codCategoria) {
    const comboTipo = document.getElementById("edit_comboTipo");
    const comboCategoria = document.getElementById("edit_comboCategoria");
    if (!comboTipo || !comboCategoria) return;

    comboTipo.dataset.valorRegistrado = codTipo ? String(codTipo) : "";
    comboCategoria.dataset.valorRegistrado = codCategoria ? String(codCategoria) : "";

    // Si ya tiene opciones cargadas, forzamos la selección ahora
    if (comboTipo.options.length > 1 && comboTipo.dataset.valorRegistrado) {
      comboTipo.value = comboTipo.dataset.valorRegistrado;
      comboTipo.dispatchEvent(new Event("change"));
    }
  };

  // ==========================
  // Inicio
  // ==========================
  document.addEventListener("DOMContentLoaded", () => {
    esperarPar("comboTipo", "comboCategoria");
    esperarPar("edit_comboTipo", "edit_comboCategoria");
  });
})();
