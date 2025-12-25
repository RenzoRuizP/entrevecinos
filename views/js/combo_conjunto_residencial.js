// views/js/combo_conjunto_residencial.js
(function () {
  'use strict';

  function buildURL(path) {
    if (!window.BASE_URL) return path;
    return window.BASE_URL.replace(/\/+$/, "") + "/" + path.replace(/^\/+/, "");
  }

  function setHidden(el, hidden) {
    if (!el) return;
    el.classList.toggle('d-none', !!hidden);
  }

  function resetSelect(sel, placeholderText, { disabled = false } = {}) {
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholderText}</option>`;
    sel.value = "";
    sel.disabled = !!disabled;
  }

  function resetInput(inp) {
    if (!inp) return;
    inp.value = "";
  }

  function resetFileInput(fileInp) {
    if (!fileInp) return;
    fileInp.value = "";
  }

  function fillSelectFromArray(combo, placeholder, arr, mapFn) {
    combo.innerHTML = `<option value="">${placeholder}</option>`;
    (arr || []).forEach((item) => {
      const opt = document.createElement("option");
      const { value, text } = mapFn(item);
      opt.value = value;
      opt.textContent = text;
      combo.appendChild(opt);
    });
  }

  async function fetchJSON(url) {
    const res = await fetch(url, { cache: "no-store" });
    if (!res.ok) throw new Error(`Error HTTP ${res.status} al cargar ${url}`);
    return await res.json();
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

    if (!comboTipo || !wrapCondominio || !wrapUrbanizacion || !wrapDireccion ||
        !comboCondominio || !comboUrbanizacion || !inputDireccion || !inputComprobante) {
      return;
    }

    // ✅ Evitar múltiples inicializaciones
    if (comboTipo.dataset.evInit === "1") return;
    comboTipo.dataset.evInit = "1";

    // Estado UI inicial
    setHidden(wrapCondominio, true);
    setHidden(wrapUrbanizacion, true);
    setHidden(wrapDireccion, true);

    resetSelect(comboCondominio, "Selecciona condominio", { disabled: true });
    resetSelect(comboUrbanizacion, "Selecciona urbanización", { disabled: true });
    resetInput(inputDireccion);
    resetFileInput(inputComprobante);

    // ✅ Cache de data en memoria (para alternar sin romper el select)
    let cacheCondominios = null;     // array
    let cacheUrbanizaciones = null;  // array

    // ✅ Token anti-race-condition (si cambian rápido)
    let reqId = 0;

    async function ensureCondominiosLoaded(currentReq) {
      if (cacheCondominios && Array.isArray(cacheCondominios)) return cacheCondominios;

      const data = await fetchJSON(buildURL("condominios"));
      if (currentReq !== reqId) return null; // respuesta vieja, se ignora

      cacheCondominios = Array.isArray(data) ? data : (data ? [data] : []);
      return cacheCondominios;
    }

    async function ensureUrbanizacionesLoaded(currentReq) {
      if (cacheUrbanizaciones && Array.isArray(cacheUrbanizaciones)) return cacheUrbanizaciones;

      const data = await fetchJSON(buildURL("urbanizaciones"));
      if (currentReq !== reqId) return null; // respuesta vieja, se ignora

      cacheUrbanizaciones = Array.isArray(data) ? data : (data ? [data] : []);
      return cacheUrbanizaciones;
    }

    async function onTipoChange() {
      reqId++;
      const currentReq = reqId;

      const tipo = comboTipo.value;

      // Reseteos base (siempre)
      resetInput(inputDireccion);
      resetFileInput(inputComprobante);
      setHidden(wrapDireccion, true);

      // OJO: aquí sí reseteamos selects, pero si hay cache, repintamos
      resetSelect(comboCondominio, "Selecciona condominio", { disabled: true });
      resetSelect(comboUrbanizacion, "Selecciona urbanización", { disabled: true });

      if (tipo === "condominio") {
        setHidden(wrapCondominio, false);
        setHidden(wrapUrbanizacion, true);

        // UX: loading state
        resetSelect(comboCondominio, "Cargando condominios...", { disabled: true });

        try {
          const arr = await ensureCondominiosLoaded(currentReq);
          if (!arr) return; // fue reemplazado por otro cambio

          fillSelectFromArray(
            comboCondominio,
            "Selecciona condominio",
            arr,
            (c) => ({ value: c.codigo_condominio, text: c.nombre_condominio })
          );
          comboCondominio.disabled = false;

          // Importante: si tu otro script (combo_condominio.js) depende de este select,
          // aquí ya está poblado. Los listeners ya deberían estar enganchados.
        } catch (e) {
          console.error("[EV][Residencia] Error cargando condominios:", e);
          resetSelect(comboCondominio, "No se pudo cargar. Reintenta.", { disabled: true });
        }

        return;
      }

      if (tipo === "urbanizacion") {
        setHidden(wrapUrbanizacion, false);
        setHidden(wrapCondominio, true);

        resetSelect(comboUrbanizacion, "Cargando urbanizaciones...", { disabled: true });

        try {
          const arr = await ensureUrbanizacionesLoaded(currentReq);
          if (!arr) return;

          fillSelectFromArray(
            comboUrbanizacion,
            "Selecciona urbanización",
            arr,
            (u) => ({ value: u.codigo_urbanizacion, text: u.nombre_urbanizacion })
          );
          comboUrbanizacion.disabled = false;
        } catch (e) {
          console.error("[EV][Residencia] Error cargando urbanizaciones:", e);
          resetSelect(comboUrbanizacion, "No se pudo cargar. Reintenta.", { disabled: true });
        }

        return;
      }

      // Si no eligió nada
      setHidden(wrapCondominio, true);
      setHidden(wrapUrbanizacion, true);
      setHidden(wrapDireccion, true);
    }

    function onDestinoChange() {
      const tipo = comboTipo.value;
      const selected = (tipo === "condominio")
        ? comboCondominio.value
        : comboUrbanizacion.value;

      resetInput(inputDireccion);
      resetFileInput(inputComprobante);

      setHidden(wrapDireccion, !selected);
    }

    comboTipo.addEventListener("change", () => {
      onTipoChange().catch((e) => console.error("[EV][Residencia] error:", e));
    });

    comboCondominio.addEventListener("change", onDestinoChange);
    comboUrbanizacion.addEventListener("change", onDestinoChange);
  }

  document.addEventListener("DOMContentLoaded", init);
  window.EV_initConjuntoResidencial = init;
})();
