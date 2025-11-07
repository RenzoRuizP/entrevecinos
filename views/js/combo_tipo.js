// combo_tipo.js
(function () {
  // 🔒 Evita doble inicialización si el script se incluye 2 veces
  if (window.__EV_TIPO_INITED__) return;
  window.__EV_TIPO_INITED__ = true;

  function inicializarComboTipo() {
    const comboTipo = document.getElementById("comboTipo");
    const comboCategoria = document.getElementById("comboCategoria");

    if (!comboTipo || !comboCategoria) {
      console.warn("No se encontraron comboTipo y/o comboCategoria en el DOM.");
      return;
    }

    const valorRegistradoTipo = comboTipo.dataset.valorRegistrado || "";
    const valorRegistradoCategoria = comboCategoria.dataset.valorRegistrado || "";

    // Normaliza URL evitando doble slash y usando BASE_URL del servidor
    const buildURL = (path) => {
      const base = (window.BASE_URL || "").replace(/\/+$/, "");
      return base + "/" + String(path).replace(/^\/+/, "");
    };

    const resetSelect = (selectEl, placeholder = "Seleccione…", disabled = false) => {
      selectEl.innerHTML = `<option value="" selected disabled>-- ${placeholder} --</option>`;
      selectEl.disabled = disabled;
    };

    const renderCategorias = (data, selectedValue = "") => {
      resetSelect(comboCategoria, "Selecciona una categoría", false);

      // Formato PLANO esperado: [{ grupo, codigo_categoria, categoria }, ...]
      const grupos = {};
      data.forEach(row => {
        const g = row.grupo || "Otros";
        if (!grupos[g]) grupos[g] = [];
        grupos[g].push({
          value: row.codigo_categoria,
          text: row.categoria
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

    // Cargar Tipos
    const cargarTipos = async () => {
      resetSelect(comboTipo, "Seleccione Tipos", false);
      try {
        const data = await cargarJSON(buildURL("tipos")); // ← /{base}/tipos
        data.forEach(t => {
          const op = document.createElement("option");
          op.value = t.codigo_tipo;
          op.textContent = t.nombre;
          comboTipo.appendChild(op);
        });
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

    // Cargar Categorías según tipo
    const cargarCategoriasPorTipo = async (codigoTipo, preselect = "") => {
      if (!codigoTipo) {
        resetSelect(comboCategoria, "Selecciona un tipo primero", true);
        return;
      }
      resetSelect(comboCategoria, "Cargando categorías...", true);
      try {
        // Ruta real definida en tu index.php
        const data = await cargarJSON(buildURL(`tipos/${encodeURIComponent(codigoTipo)}/categoria_grupo`));
        renderCategorias(data, preselect);
        comboCategoria.disabled = false;
      } catch (e) {
        console.error("Error cargando Categorías:", e);
        resetSelect(comboCategoria, "No se pudo cargar categorías", true);
      }
    };

    // Eventos
    comboTipo.addEventListener("change", () => {
      const tipo = comboTipo.value;
      const pre = valorRegistradoCategoria || "";
      cargarCategoriasPorTipo(tipo, pre);
    });

    // Inicio
    cargarTipos();
  }

  function esperarComboYInicializar() {
    const combo = document.getElementById("comboTipo");
    if (combo) {
      inicializarComboTipo();
    } else {
      setTimeout(esperarComboYInicializar, 100);
    }
  }

  document.addEventListener("DOMContentLoaded", esperarComboYInicializar);
})();
