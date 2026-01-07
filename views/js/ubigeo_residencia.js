(function () {
  'use strict';

  function buildURL(path) {
    if (!window.BASE_URL) return path;
    return window.BASE_URL.replace(/\/+$/, "") + "/" + path.replace(/^\/+/, "");
  }

  async function fetchJSON(url) {
    const res = await fetch(url, { cache: "no-store" });
    if (!res.ok) throw new Error(`HTTP ${res.status} - ${url}`);
    return await res.json();
  }

  function setOptions(select, placeholder, items, mapFn) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
    (items || []).forEach((it) => {
      const opt = document.createElement("option");
      const { value, text } = mapFn(it);
      opt.value = value;
      opt.textContent = text;
      select.appendChild(opt);
    });
  }

  function resetSelect(select, placeholder, disabled = true) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
    select.value = "";
    select.disabled = !!disabled;
    select.classList.remove("is-invalid");
  }

  function init() {
    const dep = document.getElementById("comboDepartamento");
    const prov = document.getElementById("comboProvincia");
    const dist = document.getElementById("comboDistrito");
    const tipo = document.getElementById("comboConjuntoResidencial");

    const wrapCondominio = document.getElementById("wrapCondominio");
    const wrapUrbanizacion = document.getElementById("wrapUrbanizacion");
    const wrapDireccion = document.getElementById("wrapDireccion");
    const comboCondominio = document.getElementById("comboCondominio");
    const comboUrbanizacion = document.getElementById("comboUrbanizacion");
    const inputDireccion = document.getElementById("direccion");
    const inputComprobante = document.getElementById("comprobante_domicilio");

    if (!dep || !prov || !dist || !tipo) return;

    if (dep.dataset.evUbigeoInit === "1") return;
    dep.dataset.evUbigeoInit = "1";

    function resetResidencia() {
      tipo.value = "";
      tipo.disabled = true;

      if (wrapCondominio) wrapCondominio.classList.add("d-none");
      if (wrapUrbanizacion) wrapUrbanizacion.classList.add("d-none");
      if (wrapDireccion) wrapDireccion.classList.add("d-none");

      if (comboCondominio) {
        comboCondominio.innerHTML = `<option value="">Selecciona condominio</option>`;
        comboCondominio.value = "";
        comboCondominio.disabled = true;
      }
      if (comboUrbanizacion) {
        comboUrbanizacion.innerHTML = `<option value="">Selecciona urbanización</option>`;
        comboUrbanizacion.value = "";
        comboUrbanizacion.disabled = true;
      }

      if (inputDireccion) inputDireccion.value = "";
      if (inputComprobante) inputComprobante.value = "";

      window.EV_SELECTED_DISTRITO = "";
      window.dispatchEvent(new CustomEvent("EV:DISTRITO_CHANGE", { detail: { codigo_distrito: "" } }));
    }

    async function cargarDepartamentos() {
      const data = await fetchJSON(buildURL("ubigeo/departamentos"));
      setOptions(dep, "Selecciona departamento", data, (x) => ({ value: x.codigo, text: x.nombre }));
    }

    resetSelect(prov, "Selecciona provincia", true);
    resetSelect(dist, "Selecciona distrito", true);
    resetResidencia();

    dep.addEventListener("change", async () => {
      resetSelect(prov, "Cargando provincias...", true);
      resetSelect(dist, "Selecciona distrito", true);
      resetResidencia();

      const depId = dep.value;
      if (!depId) {
        resetSelect(prov, "Selecciona provincia", true);
        return;
      }

      try {
        const data = await fetchJSON(buildURL(`ubigeo/provincias/${encodeURIComponent(depId)}`));
        setOptions(prov, "Selecciona provincia", data, (x) => ({ value: x.codigo, text: x.nombre }));
        prov.disabled = false;
      } catch (e) {
        console.error("[EV][UBIGEO] provincias:", e);
        resetSelect(prov, "No se pudo cargar", true);
      }
    });

    prov.addEventListener("change", async () => {
      resetSelect(dist, "Cargando distritos...", true);
      resetResidencia();

      const provId = prov.value;
      if (!provId) {
        resetSelect(dist, "Selecciona distrito", true);
        return;
      }

      try {
        const data = await fetchJSON(buildURL(`ubigeo/distritos/${encodeURIComponent(provId)}`));
        setOptions(dist, "Selecciona distrito", data, (x) => ({ value: x.codigo, text: x.nombre }));
        dist.disabled = false;
      } catch (e) {
        console.error("[EV][UBIGEO] distritos:", e);
        resetSelect(dist, "No se pudo cargar", true);
      }
    });

    dist.addEventListener("change", () => {
      resetResidencia();

      const distId = dist.value;
      if (!distId) return;

      tipo.disabled = false;

      window.EV_SELECTED_DISTRITO = distId;
      window.dispatchEvent(new CustomEvent("EV:DISTRITO_CHANGE", { detail: { codigo_distrito: distId } }));
    });

    cargarDepartamentos().catch((e) => console.error("[EV][UBIGEO] departamentos:", e));
  }

  document.addEventListener("DOMContentLoaded", init);
  window.EV_initUbigeoResidencia = init;
})();
