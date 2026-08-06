// views/js/combo_conjunto_residencial.js
(function () {
  'use strict';

  function buildURL(path) {
    const b = (window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/+$/, '');
    const p = String(path || '').replace(/^\/+/, '');
    return b ? (b + '/' + p) : ('/' + p);
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

    const comboDistrito = document.getElementById("comboDistrito");

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

    if (comboTipo.dataset.evInit === "1") return;
    comboTipo.dataset.evInit = "1";

    // UX: dirección siempre disabled (solo visualización)
    inputDireccion.setAttribute('disabled', 'disabled');

    // Estado
    let distritoActual = null;

    // Cache por distrito
    const cache = {
      condominios: new Map(),     // key: distId, value: array
      urbanizaciones: new Map()   // key: distId, value: array
    };

    // anti-race
    let reqId = 0;

    function resetResidenciaUITotal() {
      // Ocultar wrappers secundarios
      setHidden(wrapCondominio, true);
      setHidden(wrapUrbanizacion, true);
      setHidden(wrapDireccion, true);

      // limpiar selects
      resetSelect(comboCondominio, "Selecciona condominio", { disabled: true });
      resetSelect(comboUrbanizacion, "Selecciona urbanización", { disabled: true });

      // limpiar direccion/file
      resetInput(inputDireccion);
      resetFileInput(inputComprobante);

      // reset tipo
      comboTipo.value = "";
    }

    function resetDependientesTipo() {
      // al cambiar tipo, limpiar cond/urb + direccion/file
      resetSelect(comboCondominio, "Selecciona condominio", { disabled: true });
      resetSelect(comboUrbanizacion, "Selecciona urbanización", { disabled: true });

      resetInput(inputDireccion);
      resetFileInput(inputComprobante);

      setHidden(wrapDireccion, true);
    }

    // Estado inicial
    resetResidenciaUITotal();

    // Cuando ubigeo cambia (dep/prov), resetea residencia
    document.addEventListener('EV:UBIGEO_RESET_RESIDENCIA', () => {
      distritoActual = null;
      resetResidenciaUITotal();
    });

    // Cuando distrito cambia, habilitar tipo y resetear listas
    document.addEventListener('EV:UBIGEO_DISTRITO_CHANGE', (ev) => {
      distritoActual = ev?.detail?.codigo_distrito || null;

      // Resetear todo el bloque (tipo, combos, direccion, file)
      resetResidenciaUITotal();

      // Si no hay distrito, no hacemos nada más
      if (!distritoActual) return;

      // Mantener tipo vacío, pero ahora el usuario ya puede elegir con sentido.
      // (No bloqueamos el tipo, pero el filtrado depende del distritoActual)
    });

    async function ensureCondominiosByDistrito(currentReq, distId) {
      if (!distId) return [];
      if (cache.condominios.has(distId)) return cache.condominios.get(distId);

      const data = await fetchJSON(buildURL(`condominios?distrito=${encodeURIComponent(distId)}`));
      if (currentReq !== reqId) return null;

      const arr = Array.isArray(data) ? data : [];
      cache.condominios.set(distId, arr);
      return arr;
    }

    async function ensureUrbanizacionesByDistrito(currentReq, distId) {
      if (!distId) return [];
      if (cache.urbanizaciones.has(distId)) return cache.urbanizaciones.get(distId);

      const data = await fetchJSON(buildURL(`urbanizaciones?distrito=${encodeURIComponent(distId)}`));
      if (currentReq !== reqId) return null;

      const arr = Array.isArray(data) ? data : [];
      cache.urbanizaciones.set(distId, arr);
      return arr;
    }

    async function onTipoChange() {
      reqId++;
      const currentReq = reqId;

      const tipo = comboTipo.value;

      resetDependientesTipo();

      // si no hay distrito, no permitimos continuar (UX defensivo)
      const distId = distritoActual || (comboDistrito ? parseInt(comboDistrito.value || '0', 10) : 0);
      if (!distId) {
        setHidden(wrapCondominio, true);
        setHidden(wrapUrbanizacion, true);
        setHidden(wrapDireccion, true);
        return;
      }

      if (tipo === "condominio") {
        setHidden(wrapCondominio, false);
        setHidden(wrapUrbanizacion, true);

        resetSelect(comboCondominio, "Cargando condominios...", { disabled: true });

        try {
          const arr = await ensureCondominiosByDistrito(currentReq, distId);
          if (!arr) return;

          fillSelectFromArray(
            comboCondominio,
            arr.length ? "Selecciona condominio" : "No hay condominios en este distrito",
            arr,
            (c) => ({ value: c.codigo_condominio, text: c.nombre_condominio })
          );
          comboCondominio.disabled = arr.length === 0;
        } catch (e) {
          console.error("[EV][Residencia] Error cargando condominios por distrito:", e);
          resetSelect(comboCondominio, "No se pudo cargar. Reintenta.", { disabled: true });
        }

        return;
      }

      if (tipo === "urbanizacion") {
        setHidden(wrapUrbanizacion, false);
        setHidden(wrapCondominio, true);

        resetSelect(comboUrbanizacion, "Cargando urbanizaciones...", { disabled: true });

        try {
          const arr = await ensureUrbanizacionesByDistrito(currentReq, distId);
          if (!arr) return;

          fillSelectFromArray(
            comboUrbanizacion,
            arr.length ? "Selecciona urbanización" : "No hay urbanizaciones en este distrito",
            arr,
            (u) => ({ value: u.codigo_urbanizacion, text: u.nombre_urbanizacion })
          );
          comboUrbanizacion.disabled = arr.length === 0;
        } catch (e) {
          console.error("[EV][Residencia] Error cargando urbanizaciones por distrito:", e);
          resetSelect(comboUrbanizacion, "No se pudo cargar. Reintenta.", { disabled: true });
        }

        return;
      }

      // Si no eligió nada
      setHidden(wrapCondominio, true);
      setHidden(wrapUrbanizacion, true);
      setHidden(wrapDireccion, true);
    }

    function findInCache(distId, tipo, codigo) {
      if (!distId || !codigo) return null;
      const id = String(codigo);

      if (tipo === 'condominio') {
        const arr = cache.condominios.get(distId) || [];
        return arr.find(x => String(x.codigo_condominio) === id) || null;
      }
      if (tipo === 'urbanizacion') {
        const arr = cache.urbanizaciones.get(distId) || [];
        return arr.find(x => String(x.codigo_urbanizacion) === id) || null;
      }
      return null;
    }

    function onDestinoChange() {
      const tipo = comboTipo.value;
      const distId = distritoActual || (comboDistrito ? parseInt(comboDistrito.value || '0', 10) : 0);

      const selected = (tipo === "condominio")
        ? comboCondominio.value
        : comboUrbanizacion.value;

      resetInput(inputDireccion);
      resetFileInput(inputComprobante);

      if (!selected) {
        setHidden(wrapDireccion, true);
        return;
      }

      const item = findInCache(distId, tipo, selected);

      if (tipo === 'condominio') {
        inputDireccion.value = item?.direccion_condominio ? String(item.direccion_condominio) : '';
      } else {
        inputDireccion.value = item?.direccion_urbanizacion ? String(item.direccion_urbanizacion) : '';
      }

      // Mostrar dirección + comprobante
      setHidden(wrapDireccion, false);
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
