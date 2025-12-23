// views/js/combo_conjunto_residencial.js
(function () {
  'use strict';

  function buildURL(path) {
    if (!window.BASE_URL) return path;
    return window.BASE_URL.replace(/\/+$/, "") + "/" + path.replace(/^\/+/, "");
  }

  async function cargarCombo(url, combo, placeholder, mapFn) {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`Error HTTP ${res.status} al cargar ${url}`);
    const data = await res.json();

    combo.innerHTML = `<option value="">${placeholder}</option>`;
    (data || []).forEach((item) => {
      const opt = document.createElement("option");
      const { value, text } = mapFn(item);
      opt.value = value;
      opt.textContent = text;
      combo.appendChild(opt);
    });
  }

  function setHidden(el, hidden) {
    if (!el) return;
    el.classList.toggle('d-none', !!hidden);
  }

  function resetSelect(sel, placeholderText) {
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholderText}</option>`;
    sel.value = "";
  }

  function resetInput(inp) {
    if (!inp) return;
    inp.value = "";
  }

  function resetFileInput(fileInp) {
    if (!fileInp) return;
    fileInp.value = "";
  }

  function init() {
    const comboTipo = document.getElementById("comboConjuntoResidencial");

    const wrapCondominio = document.getElementById("wrapCondominio");
    const wrapUrbanizacion = document.getElementById("wrapUrbanizacion");
    const wrapDireccion = document.getElementById("wrapDireccion");

    const comboCondominio = document.getElementById("comboCondominio");
    const comboUrbanizacion = document.getElementById("comboUrbanizacion");
    const inputDireccion = document.getElementById("direccion");
    const inputComprobante = document.getElementById("comprobante_domicilio");

    if (!comboTipo || !wrapCondominio || !wrapUrbanizacion || !wrapDireccion || !comboCondominio || !comboUrbanizacion || !inputDireccion || !inputComprobante) {
      return;
    }

    if (comboTipo.dataset.evInit === "1") return;
    comboTipo.dataset.evInit = "1";

    setHidden(wrapCondominio, true);
    setHidden(wrapUrbanizacion, true);
    setHidden(wrapDireccion, true);

    resetSelect(comboCondominio, "Selecciona condominio");
    resetSelect(comboUrbanizacion, "Selecciona urbanización");
    resetInput(inputDireccion);
    resetFileInput(inputComprobante);

    let condominiosCargados = false;
    let urbanizacionesCargadas = false;

    async function onTipoChange() {
      const tipo = comboTipo.value;

      resetSelect(comboCondominio, "Selecciona condominio");
      resetSelect(comboUrbanizacion, "Selecciona urbanización");
      resetInput(inputDireccion);
      resetFileInput(inputComprobante);
      setHidden(wrapDireccion, true);

      if (tipo === "condominio") {
        setHidden(wrapCondominio, false);
        setHidden(wrapUrbanizacion, true);

        if (!condominiosCargados) {
          await cargarCombo(
            buildURL("condominios"),
            comboCondominio,
            "Selecciona condominio",
            (c) => ({ value: c.codigo_condominio, text: c.nombre_condominio })
          );
          condominiosCargados = true;
        }
        return;
      }

      if (tipo === "urbanizacion") {
        setHidden(wrapUrbanizacion, false);
        setHidden(wrapCondominio, true);

        if (!urbanizacionesCargadas) {
          await cargarCombo(
            buildURL("urbanizaciones"),
            comboUrbanizacion,
            "Selecciona urbanización",
            (u) => ({ value: u.codigo_urbanizacion, text: u.nombre_urbanizacion })
          );
          urbanizacionesCargadas = true;
        }
        return;
      }

      setHidden(wrapCondominio, true);
      setHidden(wrapUrbanizacion, true);
      setHidden(wrapDireccion, true);
    }

    function onDestinoChange() {
      const tipo = comboTipo.value;
      const selected = (tipo === "condominio") ? comboCondominio.value : comboUrbanizacion.value;

      resetInput(inputDireccion);
      resetFileInput(inputComprobante);

      setHidden(wrapDireccion, !selected);
    }

    comboTipo.addEventListener("change", () => onTipoChange().catch((e) => console.error("[EV][Residencia] error:", e)));
    comboCondominio.addEventListener("change", onDestinoChange);
    comboUrbanizacion.addEventListener("change", onDestinoChange);
  }

  document.addEventListener("DOMContentLoaded", init);
  window.EV_initConjuntoResidencial = init;
})();
