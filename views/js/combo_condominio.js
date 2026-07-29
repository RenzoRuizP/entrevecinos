// views/js/combo_condominio.js  (EV) — SOLO Condominio (sin torre/departamento)
(function () {
  function buildURL(path) {
  const base = (window.EV?.baseUrl ?? window.BASE_URL ?? window.EV_BASE_URL ?? '')
    .toString()
    .replace(/\/+$/, '');

  const cleanPath = String(path || '').replace(/^\/+/, '');
  return `${base}/${cleanPath}`;
}

  async function cargarCombo(url, combo, placeholder, mapFn, valorSeleccionado = null) {
    try {
      const res = await fetch(url, { credentials: "include", cache: "no-store" });
      if (!res.ok) throw new Error(`Error al cargar datos desde ${url}`);
      const data = await res.json();

      combo.innerHTML = `<option value="">${placeholder}</option>`;
      (data || []).forEach((item) => {
        const opt = document.createElement("option");
        const { value, text, extra } = mapFn(item);
        opt.value = value;
        opt.textContent = text;

        // extra data attributes (ej: dirección)
        if (extra && typeof extra === "object") {
          Object.keys(extra).forEach((k) => {
            opt.dataset[k] = String(extra[k] ?? "");
          });
        }

        if (valorSeleccionado && String(valorSeleccionado) === String(value)) {
          opt.selected = true;
        }
        combo.appendChild(opt);
      });
    } catch (error) {
      console.error(`Error al cargar ${placeholder}:`, error);
      combo.innerHTML = `<option value="">No se pudo cargar</option>`;
    }
  }

  function inicializarComboCondominio() {
    const comboCondominio = document.getElementById("comboCondominio");
    if (!comboCondominio) return;

    if (comboCondominio.dataset.evCombosInit === "1") return;
    comboCondominio.dataset.evCombosInit = "1";

    const valorRegistradoCondominio = comboCondominio.dataset.valorRegistrado || "";

    // ✅ Lista general ahora incluye direccion_condominio
    cargarCombo(
      buildURL("condominios"),
      comboCondominio,
      "--Seleccione Condominio--",
      (c) => ({
        value: c.codigo_condominio,
        text: c.nombre_condominio,
        extra: { direccion: c.direccion_condominio || "" }
      }),
      valorRegistradoCondominio
    );
  }

  function esperarComboYInicializar() {
    const combo = document.getElementById("comboCondominio");
    if (combo) inicializarComboCondominio();
    else setTimeout(esperarComboYInicializar, 120);
  }

  document.addEventListener("DOMContentLoaded", esperarComboYInicializar);
  window.EV_initCombosCondominio = inicializarComboCondominio;
})();
