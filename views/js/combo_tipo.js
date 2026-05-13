// combo_tipo.js (EV) — Tipo/Categoría filtrado por Producto/Servicio + preparación automática
(function () {
  if (window.__EV_TIPO_INITED__) return;
  window.__EV_TIPO_INITED__ = true;

  const buildURL = (path, params = {}) => {
    const base = (window.BASE_URL || "").replace(/\/+$/, "");
    const cleanPath = String(path).replace(/^\/+/, "");
    const url = new URL(base + "/" + cleanPath, window.location.origin);

    Object.entries(params).forEach(([k, v]) => {
      if (v !== undefined && v !== null && String(v).trim() !== "") {
        url.searchParams.set(k, String(v));
      }
    });

    return url.pathname + url.search;
  };

  const resetSelect = (selectEl, placeholder = "Seleccione…", disabled = false) => {
    if (!selectEl) return;
    selectEl.innerHTML = `<option value="" selected disabled>-- ${placeholder} --</option>`;
    selectEl.disabled = !!disabled;
  };

  function unwrapArray(payload) {
    if (Array.isArray(payload)) return payload;
    if (payload && typeof payload === "object") {
      const inner = payload.data ?? payload.items ?? payload.result ?? null;
      if (Array.isArray(inner)) return inner;
    }
    return null;
  }

  async function cargarJSON(url) {
    const res = await fetch(url, {
      headers: { "Accept": "application/json" },
      credentials: "same-origin"
    });

    if (res.status === 401) {
      const j = await res.json().catch(() => ({}));
      console.warn("[EV] 401:", j?.mensaje || "Sesión expirada", j);
      throw new Error("UNAUTHORIZED");
    }

    if (res.status === 409) {
      const j = await res.json().catch(() => ({}));
      console.warn("[EV] 409:", j?.mensaje || "Cuenta requiere atención", j);
      if (j?.redirect) {
        try { window.location.href = j.redirect; } catch (_) {}
      }
      throw new Error("CUENTA_OBSERVADA");
    }

    if (!res.ok) throw new Error(`HTTP ${res.status} al cargar ${url}`);
    return await res.json();
  }

  function normalizarTipoPublicacion(valor, allowEmpty = false) {
    const v = String(valor ?? "").trim().toLowerCase();
    if (allowEmpty && v === "") return "";
    return v === "servicio" ? "servicio" : "producto";
  }

  function getTipoPublicacionPar(comboTipo) {
    const isEdit = comboTipo?.id === "edit_comboTipo";
    const name = isEdit ? "edit_tipo_publicacion" : "tipo_publicacion";
    const checked = document.querySelector(`input[name="${name}"]:checked`);
    return normalizarTipoPublicacion(checked?.value || "producto");
  }

  function getParIds(modo) {
    return modo === "edit"
      ? { tipoId: "edit_comboTipo", categoriaId: "edit_comboCategoria" }
      : { tipoId: "comboTipo", categoriaId: "comboCategoria" };
  }

  function getModoByRadioName(name) {
    return name === "edit_tipo_publicacion" ? "edit" : "add";
  }

  function normalizarTexto(v) {
    return String(v || "")
      .trim()
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
  }

  function reglaPreparacionFallback(tipoNombre, categoriaNombre) {
    const tipo = normalizarTexto(tipoNombre);
    const cat = normalizarTexto(categoriaNombre);

    if (["comida preparada", "panaderia, reposteria y postres"].includes(tipo)) return true;

    const preparadasPorTipo = {
      "bebidas sin alcohol": [
        "jugos", "refrescos caseros", "bebidas calientes", "cafe preparado", "infusiones",
        "smoothies y batidos", "chicha, emoliente y bebidas tradicionales", "otras bebidas sin alcohol"
      ],
      "escolar, oficina y libros": [
        "material didactico", "impresiones", "fotocopias", "anillados y plastificados", "manualidades escolares"
      ],
      "regalos, eventos y personalizados": [
        "regalos personalizados", "cuadros personalizados", "invitaciones digitales", "stickers y etiquetas",
        "souvenirs", "decoracion para eventos", "arreglos florales", "desayunos sorpresa",
        "detalles para fechas especiales", "cumpleanos", "baby shower", "dia de la madre", "dia del padre",
        "otros regalos y personalizados"
      ],
      "soporte tecnico y servicios digitales": [
        "diseno grafico", "diseno para redes sociales", "edicion de documentos", "cv, cartas y presentaciones",
        "servicios escolares digitales"
      ]
    };

    return Array.isArray(preparadasPorTipo[tipo]) && preparadasPorTipo[tipo].includes(cat);
  }

  function getCampoTipoAtencion(comboCategoria) {
    if (!comboCategoria) return null;
    return comboCategoria.id === "edit_comboCategoria"
      ? document.getElementById("edit_tipoAtencionProducto")
      : document.getElementById("tipoAtencionProducto");
  }

  function getHintTipoAtencion(comboCategoria) {
    if (!comboCategoria) return null;
    return comboCategoria.id === "edit_comboCategoria"
      ? document.getElementById("hintTipoAtencionProductoEdit")
      : document.getElementById("hintTipoAtencionProductoAdd");
  }

  function aplicarTipoAtencionAutomatico(comboTipo, comboCategoria) {
    const campo = getCampoTipoAtencion(comboCategoria);
    if (!campo) return;

    const tipoPublicacion = getTipoPublicacionPar(comboTipo);

    // Por ahora los servicios no usan preparación de producto.
    if (tipoPublicacion === "servicio") {
      campo.value = "no_requiere_preparacion";
      campo.dataset.evAutoTipoAtencion = "no_requiere_preparacion";
      campo.disabled = true;

      const hint = getHintTipoAtencion(comboCategoria);
      if (hint) hint.textContent = "EV detectó que esta publicación es un servicio; no usa preparación de producto.";
      return;
    }

    const selected = comboCategoria?.selectedOptions?.[0] || null;
    const tipoNombre = comboTipo?.selectedOptions?.[0]?.textContent || "";
    const categoriaNombre = selected?.textContent || "";

    let requiere = false;
    if (selected && selected.value) {
      const raw = selected.dataset.requierePreparacion;
      if (raw === "1" || raw === "0") {
        requiere = raw === "1";
      } else {
        requiere = reglaPreparacionFallback(tipoNombre, categoriaNombre);
      }
    }

    const valor = requiere ? "requiere_preparacion" : "no_requiere_preparacion";
    campo.value = valor;
    campo.dataset.evAutoTipoAtencion = valor;
    campo.disabled = true;

    const hint = getHintTipoAtencion(comboCategoria);
    if (hint) {
      hint.textContent = requiere
        ? "EV detectó que esta categoría requiere preparación antes de la entrega."
        : "EV detectó que esta categoría no requiere preparación previa.";
    }
  }

  const renderCategorias = (comboTipo, comboCategoria, data, selectedValue = "") => {
    if (!comboCategoria) return;

    resetSelect(comboCategoria, "Selecciona una categoría", false);

    const grupos = {};
    data.forEach(row => {
      const g = row.grupo || row.nombre_grupo || "Categorías";
      if (!grupos[g]) grupos[g] = [];

      const categoriaNombre = row.categoria || row.nombre || "";
      const tipoNombre = comboTipo?.selectedOptions?.[0]?.textContent || "";
      const rawPreparacion = row.requiere_preparacion_default ?? row.requiere_preparacion ?? row.preparado ?? null;
      const requiere = rawPreparacion === null || rawPreparacion === undefined || rawPreparacion === ""
        ? reglaPreparacionFallback(tipoNombre, categoriaNombre)
        : Number(rawPreparacion) === 1;

      grupos[g].push({
        value: row.codigo_categoria,
        text: categoriaNombre,
        requiere
      });
    });

    Object.keys(grupos).forEach(nombreGrupo => {
      const og = document.createElement("optgroup");
      og.label = nombreGrupo;
      grupos[nombreGrupo].forEach(it => {
        const op = document.createElement("option");
        op.value = it.value;
        op.textContent = it.text;
        op.dataset.requierePreparacion = it.requiere ? "1" : "0";
        if (selectedValue && String(selectedValue) === String(op.value)) op.selected = true;
        og.appendChild(op);
      });
      comboCategoria.appendChild(og);
    });

    aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);
  };

  async function cargarCategoriasPorTipo(comboTipo, comboCategoria, codigoTipo, preselect = "") {
    const tipoPublicacion = getTipoPublicacionPar(comboTipo);

    if (!codigoTipo) {
      resetSelect(comboCategoria, "Selecciona un tipo primero", true);
      aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);
      return;
    }

    resetSelect(comboCategoria, "Cargando categorías...", true);
    aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);

    try {
      const payload = await cargarJSON(buildURL(`tipos/${encodeURIComponent(codigoTipo)}/categoria_grupo`, {
        tipo_publicacion: tipoPublicacion
      }));
      const arr = unwrapArray(payload);

      if (!arr) {
        console.error("Respuesta inválida categorías:", payload);
        resetSelect(comboCategoria, "Respuesta inválida", true);
        return;
      }

      if (!arr.length) {
        resetSelect(comboCategoria, tipoPublicacion === "servicio" ? "Sin servicios para este tipo" : "Sin categorías para este tipo", true);
        return;
      }

      renderCategorias(comboTipo, comboCategoria, arr, preselect);
      comboCategoria.disabled = false;
    } catch (e) {
      console.error("Error cargando Categorías:", e);
      resetSelect(comboCategoria, "No se pudo cargar categorías", true);
    }
  }

  async function cargarTipos(comboTipo, comboCategoria, preselectTipo = "", preselectCategoria = "") {
    const tipoPublicacion = getTipoPublicacionPar(comboTipo);

    resetSelect(comboTipo, "Cargando tipos...", true);
    resetSelect(comboCategoria, "Selecciona un tipo primero", true);
    aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);

    try {
      const payload = await cargarJSON(buildURL("tipos", { tipo_publicacion: tipoPublicacion }));
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
      comboTipo.disabled = false;

      if (preselectTipo) comboTipo.value = String(preselectTipo);

      if (comboTipo.value) {
        await cargarCategoriasPorTipo(comboTipo, comboCategoria, comboTipo.value, preselectCategoria);
      } else {
        resetSelect(comboCategoria, "Selecciona un tipo primero", true);
        aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);
      }
    } catch (e) {
      console.error("Error cargando Tipos:", e);
      resetSelect(comboTipo, "Error al cargar Tipos", true);
      resetSelect(comboCategoria, "Error al cargar", true);
    }
  }

  async function inicializarParTipoCategoria(tipoId, categoriaId) {
    const comboTipo = document.getElementById(tipoId);
    const comboCategoria = document.getElementById(categoriaId);

    if (!comboTipo || !comboCategoria) return false;

    const bindKey = `__ev_bound_${tipoId}_${categoriaId}`;
    if (comboTipo.dataset[bindKey] === "1") return true;
    comboTipo.dataset[bindKey] = "1";

    const valorRegistradoTipo = comboTipo.dataset.valorRegistrado || "";
    const valorRegistradoCategoria = comboCategoria.dataset.valorRegistrado || "";

    comboTipo.addEventListener("change", () => {
      comboCategoria.dataset.valorRegistrado = "";
      cargarCategoriasPorTipo(comboTipo, comboCategoria, comboTipo.value, "");
    });

    comboCategoria.addEventListener("change", () => {
      aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);
    });

    await cargarTipos(comboTipo, comboCategoria, valorRegistradoTipo, valorRegistradoCategoria);
    return true;
  }

  function esperarPar(tipoId, categoriaId) {
    const intentar = async () => {
      const ok = await inicializarParTipoCategoria(tipoId, categoriaId);
      if (!ok) setTimeout(intentar, 120);
    };
    intentar();
  }

  window.evRecargarComboTipoCategoria = async function (modo = "add", codTipo = "", codCategoria = "") {
    const ids = getParIds(modo === "edit" ? "edit" : "add");
    const comboTipo = document.getElementById(ids.tipoId);
    const comboCategoria = document.getElementById(ids.categoriaId);
    if (!comboTipo || !comboCategoria) return;

    if (codTipo !== undefined && codTipo !== null) comboTipo.dataset.valorRegistrado = codTipo ? String(codTipo) : "";
    if (codCategoria !== undefined && codCategoria !== null) comboCategoria.dataset.valorRegistrado = codCategoria ? String(codCategoria) : "";

    await cargarTipos(comboTipo, comboCategoria, comboTipo.dataset.valorRegistrado || "", comboCategoria.dataset.valorRegistrado || "");
  };

  window.evInitComboTipoCategoriaEdit = function (codTipo, codCategoria) {
    const comboTipo = document.getElementById("edit_comboTipo");
    const comboCategoria = document.getElementById("edit_comboCategoria");
    if (!comboTipo || !comboCategoria) return;

    comboTipo.dataset.valorRegistrado = codTipo ? String(codTipo) : "";
    comboCategoria.dataset.valorRegistrado = codCategoria ? String(codCategoria) : "";

    window.evRecargarComboTipoCategoria("edit", codTipo, codCategoria);
  };

  document.addEventListener("change", (e) => {
    const t = e.target;
    if (!t || !t.matches) return;

    if (t.matches('input[name="tipo_publicacion"], input[name="edit_tipo_publicacion"]')) {
      const modo = getModoByRadioName(t.name);
      window.evRecargarComboTipoCategoria(modo, "", "");
    }
  });

  document.addEventListener("shown.bs.modal", (e) => {
    if (e.target?.id === "modalAgregarPublicacion") {
      window.evRecargarComboTipoCategoria("add", "", "");
    }
    if (e.target?.id === "modalEditarPublicacion") {
      const comboTipo = document.getElementById("edit_comboTipo");
      const comboCategoria = document.getElementById("edit_comboCategoria");
      window.evRecargarComboTipoCategoria("edit", comboTipo?.dataset?.valorRegistrado || "", comboCategoria?.dataset?.valorRegistrado || "");
    }
  });

  document.addEventListener("DOMContentLoaded", () => {
    esperarPar("comboTipo", "comboCategoria");
    esperarPar("edit_comboTipo", "edit_comboCategoria");
  });
})();
