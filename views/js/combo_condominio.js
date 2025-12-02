// views/js/combo_condominio.js
(function () {

  // 🔹 Normaliza URL evitando doble slash
  function buildURL(path) {
    if (!window.BASE_URL) {
      console.error("window.BASE_URL no está definido");
      return path;
    }
    return window.BASE_URL.replace(/\/+$/, "") + "/" + path.replace(/^\/+/, "");
  }

  // 🔹 Función genérica para cargar combos
  async function cargarCombo(url, combo, placeholder, mapFn, valorSeleccionado = null) {
    try {
      const res = await fetch(url);
      if (!res.ok) throw new Error(`Error al cargar datos desde ${url}`);
      const data = await res.json();

      combo.innerHTML = `<option value="">${placeholder}</option>`;
      data.forEach((item) => {
        const opt = document.createElement("option");
        const { value, text } = mapFn(item);
        opt.value = value;
        opt.textContent = text;
        if (valorSeleccionado && valorSeleccionado == value) {
          opt.selected = true;
        }
        combo.appendChild(opt);
      });
    } catch (error) {
      console.error(`Error al cargar ${placeholder}:`, error);
    }
  }

  // 🔹 Inicializar combos de Condominio / Torre / Departamento
  function inicializarComboCondominio() {
    const comboCondominio = document.getElementById("comboCondominio");
    const comboTorre = document.getElementById("comboTorre");
    const comboDepartamento = document.getElementById("comboDepartamento");

    if (!comboCondominio || !comboTorre || !comboDepartamento) {
      console.warn(
        "No se encontraron combos de condominio/torre/departamento en el DOM."
      );
      return;
    }

    // ✅ Evitar volver a enganchar listeners sobre los mismos selects
    if (comboCondominio.dataset.evCombosInit === "1") {
      return;
    }
    comboCondominio.dataset.evCombosInit = "1";

    const valorRegistradoCondominio = comboCondominio.dataset.valorRegistrado;
    const valorRegistradoTorre = comboTorre.dataset.valorRegistrado;
    const valorRegistradoDepartamento = comboDepartamento.dataset.valorRegistrado;

    // 🔹 Cargar condominios y seleccionar valor registrado
    cargarCombo(
      buildURL("condominios"),
      comboCondominio,
      "--Seleccione Condominio--",
      (c) => ({ value: c.codigo_condominio, text: c.nombre_condominio }),
      valorRegistradoCondominio
    ).then(() => {
      // Si hay condominio registrado, cargar torres automáticamente
      if (valorRegistradoCondominio) {
        comboCondominio.dispatchEvent(new Event("change"));
      }
    });

    // 🔹 Evento cambio condominio → cargar torres
    comboCondominio.addEventListener("change", function () {
      const selectedCondominio = this.value;
      comboTorre.innerHTML = "<option value=''>--Seleccione Torre--</option>";
      comboDepartamento.innerHTML =
        "<option value=''>--Seleccione Departamento--</option>";

      if (!selectedCondominio) return;

      cargarCombo(
        buildURL(`condominios/${selectedCondominio}/torres`),
        comboTorre,
        "--Seleccione Torre--",
        (t) => ({ value: t.codigo_torre, text: t.nombre_torre }),
        valorRegistradoTorre
      ).then(() => {
        // Si hay torre registrada, cargar departamentos automáticamente
        if (valorRegistradoTorre) {
          comboTorre.dispatchEvent(new Event("change"));
        }
      });
    });

    // 🔹 Evento cambio torre → cargar departamentos
    comboTorre.addEventListener("change", function () {
      const selectedTorre = this.value;
      comboDepartamento.innerHTML =
        "<option value=''>--Seleccione Departamento--</option>";

      if (!selectedTorre) return;

      cargarCombo(
        buildURL(`torres/${selectedTorre}/departamentos`),
        comboDepartamento,
        "--Seleccione Departamento--",
        (d) => ({ value: d.codigo_departamento, text: d.numero_departamento }),
        valorRegistradoDepartamento
      );
    });
  }

  // 🔹 Esperar a que exista el combo en una carga completa de página
  function esperarComboYInicializar() {
    const combo = document.getElementById("comboCondominio");
    if (combo) {
      inicializarComboCondominio();
    } else {
      setTimeout(esperarComboYInicializar, 120);
    }
  }

  // ✅ Seguir funcionando cuando se recarga toda la página
  document.addEventListener("DOMContentLoaded", esperarComboYInicializar);

  // ✅ Exponer una función global para las vistas cargadas por AJAX
  window.EV_initCombosCondominio = inicializarComboCondominio;
})();
