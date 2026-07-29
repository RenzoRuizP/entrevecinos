// views/js/combo_ubigeo.js
(function () {
  'use strict';

  function buildURL(path) {
    const b = (window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/+$/, '');
    const p = String(path || '').replace(/^\/+/, '');
    return b ? (b + '/' + p) : ('/' + p);
  }

  function $(id) { return document.getElementById(id); }

  function setHidden(el, hidden) {
    if (!el) return;
    el.classList.toggle('d-none', !!hidden);
  }

  function resetSelect(sel, placeholder, { disabled = false } = {}) {
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    sel.value = '';
    sel.disabled = !!disabled;
  }

  async function fetchJSON(url) {
    const res = await fetch(url, { cache: 'no-store' });
    if (!res.ok) throw new Error(`HTTP ${res.status} -> ${url}`);
    return await res.json();
  }

  function fillSelect(sel, placeholder, arr, mapFn) {
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    (arr || []).forEach((item) => {
      const opt = document.createElement('option');
      const { value, text } = mapFn(item);
      opt.value = value;
      opt.textContent = text;
      sel.appendChild(opt);
    });
  }

  function init() {
    const dep = $('comboDepartamento');
    const prov = $('comboProvincia');
    const dist = $('comboDistrito');

    if (!dep || !prov || !dist) return;

    if (dep.dataset.evInit === '1') return;
    dep.dataset.evInit = '1';

    // estado inicial
    resetSelect(dep, 'Cargando departamentos...', { disabled: true });
    resetSelect(prov, 'Selecciona provincia', { disabled: true });
    resetSelect(dist, 'Selecciona distrito', { disabled: true });

    // anti-race
    let reqId = 0;

    async function cargarDepartamentos() {
      reqId++;
      const my = reqId;

      try {
        const data = await fetchJSON(buildURL('ubigeo/departamentos'));
        if (my !== reqId) return;

        fillSelect(dep, 'Selecciona departamento', data, (d) => ({
          value: d.codigo_departamento,
          text: d.nombre_departamento
        }));
        dep.disabled = false;
      } catch (e) {
        console.error('[EV][UBIGEO] departamentos:', e);
        resetSelect(dep, 'No se pudo cargar. Reintenta.', { disabled: true });
      }
    }

    async function onDepChange() {
      reqId++;
      const my = reqId;

      const depId = parseInt(dep.value || '0', 10);

      // reset cascada
      resetSelect(prov, depId ? 'Cargando provincias...' : 'Selecciona provincia', { disabled: true });
      resetSelect(dist, 'Selecciona distrito', { disabled: true });

      // evento para que otros scripts sepan (reseteo de residencia)
      document.dispatchEvent(new CustomEvent('EV:UBIGEO_RESET_RESIDENCIA', { detail: {} }));

      if (!depId) return;

      try {
        const data = await fetchJSON(buildURL(`ubigeo/departamentos/${depId}/provincias`));
        if (my !== reqId) return;

        fillSelect(prov, 'Selecciona provincia', data, (p) => ({
          value: p.codigo_provincia,
          text: p.nombre_provincia
        }));
        prov.disabled = false;
      } catch (e) {
        console.error('[EV][UBIGEO] provincias:', e);
        resetSelect(prov, 'No se pudo cargar. Reintenta.', { disabled: true });
      }
    }

    async function onProvChange() {
      reqId++;
      const my = reqId;

      const provId = parseInt(prov.value || '0', 10);

      resetSelect(dist, provId ? 'Cargando distritos...' : 'Selecciona distrito', { disabled: true });

      document.dispatchEvent(new CustomEvent('EV:UBIGEO_RESET_RESIDENCIA', { detail: {} }));

      if (!provId) return;

      try {
        const data = await fetchJSON(buildURL(`ubigeo/provincias/${provId}/distritos`));
        if (my !== reqId) return;

        fillSelect(dist, 'Selecciona distrito', data, (d) => ({
          value: d.codigo_distrito,
          text: d.nombre_distrito
        }));
        dist.disabled = false;
      } catch (e) {
        console.error('[EV][UBIGEO] distritos:', e);
        resetSelect(dist, 'No se pudo cargar. Reintenta.', { disabled: true });
      }
    }

    function onDistChange() {
      const distId = parseInt(dist.value || '0', 10);
      document.dispatchEvent(new CustomEvent('EV:UBIGEO_DISTRITO_CHANGE', { detail: { codigo_distrito: distId || null } }));
    }

    dep.addEventListener('change', () => { onDepChange().catch(console.error); });
    prov.addEventListener('change', () => { onProvChange().catch(console.error); });
    dist.addEventListener('change', onDistChange);

    cargarDepartamentos().catch(console.error);
  }

  document.addEventListener('DOMContentLoaded', init);
  window.EV_initUbigeo = init;
})();
