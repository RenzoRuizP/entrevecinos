// combo_tipo.js
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
    selectEl.disabled = disabled;
  };

  const renderCategorias = (comboCategoria, data, selectedValue = "") => {
    if (!comboCategoria) return;

    resetSelect(comboCategoria, "Selecciona una categoría", false);

    const grupos = {};
    data.forEach(row => {
      const g = row.grupo || row.nombre_grupo || "Otros";
      if (!grupos[g]) grupos[g] = [];
      grupos[g].push({
        value: row.codigo_categoria,
        text:  row.categoria || row.nombre
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

  async function cargarJSON(url) {
    const res = await fetch(url, { headers: { "Accept": "application/json" } });
    if (!res.ok) throw new Error(`HTTP ${res.status} al cargar ${url}`);
    return await res.json();
  }

  // ==========================
  // Inicialización de un par Tipo/Categoría
  // ids: { tipoId, categoriaId }
  // ==========================
  async function inicializarParTipoCategoria(tipoId, categoriaId) {
    const comboTipo = document.getElementById(tipoId);
    const comboCategoria = document.getElementById(categoriaId);

    if (!comboTipo || !comboCategoria) {
      // Aún no existen en el DOM
      return false;
    }

    const valorRegistradoTipo      = comboTipo.dataset.valorRegistrado      || "";
    const valorRegistradoCategoria = comboCategoria.dataset.valorRegistrado || "";

    // ---- cargar tipos ----
    const cargarTipos = async () => {
      resetSelect(comboTipo, "Seleccione Tipos", false);
      try {
        const data = await cargarJSON(buildURL("tipos"));
        comboTipo.innerHTML = `<option value="" selected disabled>-- Seleccione Tipos --</option>`;
        data.forEach(t => {
          const op = document.createElement("option");
          op.value = t.codigo_tipo;
          op.textContent = t.nombre;
          comboTipo.appendChild(op);
        });

        // Preselección si viene desde dataset
        if (valorRegistradoTipo) {
          comboTipo.value = String(valorRegistradoTipo);
        }

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

    // ---- cargar categorías ----
    const cargarCategoriasPorTipo = async (codigoTipo, preselect = "") => {
      if (!codigoTipo) {
        resetSelect(comboCategoria, "Selecciona un tipo primero", true);
        return;
      }
      resetSelect(comboCategoria, "Cargando categorías...", true);
      try {
        const data = await cargarJSON(buildURL(`tipos/${encodeURIComponent(codigoTipo)}/categoria_grupo`));
        renderCategorias(comboCategoria, data, preselect);
        comboCategoria.disabled = false;
      } catch (e) {
        console.error("Error cargando Categorías:", e);
        resetSelect(comboCategoria, "No se pudo cargar categorías", true);
      }
    };

    // Evento change del tipo
    comboTipo.addEventListener("change", () => {
      const tipo = comboTipo.value;
      const pre = comboCategoria.dataset.valorRegistrado || valorRegistradoCategoria || "";
      cargarCategoriasPorTipo(tipo, pre);
    });

    // Carga inicial de tipos
    await cargarTipos();

    return true;
  }

  // ==========================
  // Esperar a que existan los combos (SPA)
  // ==========================
  function esperarPar(tipoId, categoriaId) {
    const intentar = async () => {
      const ok = await inicializarParTipoCategoria(tipoId, categoriaId);
      if (!ok) {
        // No existen todavía; probamos de nuevo
        setTimeout(intentar, 120);
      }
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

    comboTipo.dataset.valorRegistrado      = codTipo ? String(codTipo) : "";
    comboCategoria.dataset.valorRegistrado = codCategoria ? String(codCategoria) : "";

    // Si ya tiene opciones cargadas, forzamos la selección ahora
    if (comboTipo.options.length > 1) {
      if (comboTipo.dataset.valorRegistrado) {
        comboTipo.value = comboTipo.dataset.valorRegistrado;
        comboTipo.dispatchEvent(new Event("change"));
      }
    }
    // Si todavía no está cargado, cuando se cargue leerá dataset.valorRegistrado
  };

  // ==========================
  // Inicio
  // ==========================
  document.addEventListener("DOMContentLoaded", () => {
    // Modal "Nueva publicación"
    esperarPar("comboTipo", "comboCategoria");

    // Modal "Editar publicación" (puede aparecer más tarde)
    esperarPar("edit_comboTipo", "edit_comboCategoria");
  });

})();
