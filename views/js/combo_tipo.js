// combo_tipo.js (EV)
// Tipo principal automático (Producto/Servicio) + categoría editable.
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
    selectEl.removeAttribute("aria-readonly");
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
      headers: { Accept: "application/json" },
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
    return res.json();
  }

  function normalizarTipoPublicacion(valor, allowEmpty = false) {
    const v = String(valor ?? "").trim().toLowerCase();
    if (allowEmpty && v === "") return "";
    return v === "servicio" ? "servicio" : "producto";
  }

  function normalizarTexto(valor) {
    return String(valor || "")
      .trim()
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
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

  function reglaPreparacionFallback(tipoNombre, categoriaNombre) {
    /*
     * Esta regla se usa solo cuando la columna opcional
     * requiere_preparacion_default no está disponible en la base.
     */
    if (normalizarTexto(tipoNombre) !== "producto") return false;

    const categoria = normalizarTexto(categoriaNombre);
    return [
      "almuerzos y menus",
      "postres y panes"
    ].includes(categoria);
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

    if (tipoPublicacion === "servicio") {
      campo.value = "no_requiere_preparacion";
      campo.dataset.evAutoTipoAtencion = "no_requiere_preparacion";
      campo.disabled = true;

      const hint = getHintTipoAtencion(comboCategoria);
      if (hint) {
        hint.textContent = "EV detectó que esta publicación es un servicio; no usa preparación de producto.";
      }
      return;
    }

    const selected = comboCategoria?.selectedOptions?.[0] || null;
    const tipoNombre = comboTipo?.selectedOptions?.[0]?.textContent || "";
    const categoriaNombre = selected?.textContent || "";

    let requiere = false;
    if (selected && selected.value) {
      const raw = selected.dataset.requierePreparacion;
      requiere = raw === "1"
        ? true
        : raw === "0"
          ? false
          : reglaPreparacionFallback(tipoNombre, categoriaNombre);
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

  function renderCategorias(comboTipo, comboCategoria, data, selectedValue = "") {
    if (!comboCategoria) return;

    resetSelect(comboCategoria, "Selecciona una categoría", false);

    const grupos = new Map();

    data.forEach((row) => {
      const nombreGrupo = String(row.grupo || row.nombre_grupo || "Categorías").trim() || "Categorías";
      const categoriaNombre = String(row.categoria || row.nombre || "").trim();
      if (!categoriaNombre || !row.codigo_categoria) return;

      const rawPreparacion = row.requiere_preparacion_default ?? row.requiere_preparacion ?? row.preparado ?? null;
      const requiere = rawPreparacion === null || rawPreparacion === undefined || rawPreparacion === ""
        ? reglaPreparacionFallback(comboTipo?.selectedOptions?.[0]?.textContent || "", categoriaNombre)
        : Number(rawPreparacion) === 1;

      if (!grupos.has(nombreGrupo)) grupos.set(nombreGrupo, []);
      grupos.get(nombreGrupo).push({
        value: String(row.codigo_categoria),
        text: categoriaNombre,
        requiere
      });
    });

    grupos.forEach((categorias, nombreGrupo) => {
      const optgroup = document.createElement("optgroup");
      optgroup.label = nombreGrupo;

      categorias.forEach((item) => {
        const option = document.createElement("option");
        option.value = item.value;
        option.textContent = item.text;
        option.dataset.requierePreparacion = item.requiere ? "1" : "0";

        if (selectedValue && String(selectedValue) === item.value) {
          option.selected = true;
        }

        optgroup.appendChild(option);
      });

      comboCategoria.appendChild(optgroup);
    });

    aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);
  }

  async function cargarCategoriasPorTipo(comboTipo, comboCategoria, codigoTipo, preselect = "") {
    const tipoPublicacion = getTipoPublicacionPar(comboTipo);

    if (!codigoTipo) {
      resetSelect(comboCategoria, "No se encontró el tipo", true);
      aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);
      return;
    }

    resetSelect(comboCategoria, "Cargando categorías...", true);
    aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);

    try {
      const payload = await cargarJSON(buildURL(
        `tipos/${encodeURIComponent(codigoTipo)}/categoria_grupo`,
        { tipo_publicacion: tipoPublicacion }
      ));
      const categorias = unwrapArray(payload);

      if (!categorias) {
        console.error("[EV] Respuesta inválida de categorías:", payload);
        resetSelect(comboCategoria, "Respuesta inválida", true);
        return;
      }

      if (!categorias.length) {
        resetSelect(
          comboCategoria,
          tipoPublicacion === "servicio" ? "Sin categorías de servicios" : "Sin categorías de productos",
          true
        );
        return;
      }

      renderCategorias(comboTipo, comboCategoria, categorias, preselect);
      comboCategoria.disabled = false;
    } catch (error) {
      console.error("[EV] Error cargando categorías:", error);
      resetSelect(comboCategoria, "No se pudo cargar categorías", true);
    }
  }

  function resolverTipoAutomatico(tipos, tipoPublicacion, preselectTipo = "") {
    const esperado = normalizarTexto(tipoPublicacion);
    const preseleccionado = String(preselectTipo || "").trim();

    const coincideModo = tipos.find((tipo) =>
      normalizarTexto(tipo?.nombre) === esperado
    );

    /*
     * Solo se usa la preselección cuando corresponde al modo actual.
     * Esto evita que una publicación de servicio deje seleccionado Producto
     * al cambiar el Paso 1.
     */
    if (
      preseleccionado &&
      coincideModo &&
      String(coincideModo.codigo_tipo) === preseleccionado
    ) {
      return coincideModo;
    }

    return coincideModo || tipos[0] || null;
  }

  async function cargarTipos(comboTipo, comboCategoria, preselectTipo = "", preselectCategoria = "") {
    const tipoPublicacion = getTipoPublicacionPar(comboTipo);

    resetSelect(comboTipo, "Cargando tipo...", true);
    resetSelect(comboCategoria, "Cargando categorías...", true);
    aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);

    try {
      const payload = await cargarJSON(buildURL("tipos", {
        tipo_publicacion: tipoPublicacion
      }));
      const tipos = unwrapArray(payload);

      if (!tipos) {
        console.error("[EV] Respuesta inválida de tipos:", payload);
        resetSelect(comboTipo, "Respuesta inválida", true);
        resetSelect(comboCategoria, "Respuesta inválida", true);
        return;
      }

      const tipoAutomatico = resolverTipoAutomatico(tipos, tipoPublicacion, preselectTipo);

      if (!tipoAutomatico?.codigo_tipo) {
        resetSelect(
          comboTipo,
          tipoPublicacion === "servicio" ? "Servicio no configurado" : "Producto no configurado",
          true
        );
        resetSelect(comboCategoria, "No se pudo identificar el tipo", true);
        return;
      }

      /*
       * Regla de negocio:
       * El tipo se muestra, pero no es editable. Producto/Servicio se define
       * exclusivamente por el Paso 1.
       */
      comboTipo.innerHTML = "";
      const option = document.createElement("option");
      option.value = String(tipoAutomatico.codigo_tipo);
      option.textContent = String(tipoAutomatico.nombre || (
        tipoPublicacion === "servicio" ? "Servicio" : "Producto"
      ));
      option.selected = true;
      comboTipo.appendChild(option);
      comboTipo.value = option.value;
      comboTipo.disabled = true;
      comboTipo.setAttribute("aria-readonly", "true");

      await cargarCategoriasPorTipo(
        comboTipo,
        comboCategoria,
        comboTipo.value,
        preselectCategoria
      );
    } catch (error) {
      console.error("[EV] Error cargando tipo:", error);
      resetSelect(comboTipo, "Error al cargar tipo", true);
      resetSelect(comboCategoria, "Error al cargar categorías", true);
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

    /*
     * El selector Tipo permanece bloqueado. Conservamos este listener por
     * compatibilidad con el modo edición y scripts existentes.
     */
    comboTipo.addEventListener("change", () => {
      comboCategoria.dataset.valorRegistrado = "";
      cargarCategoriasPorTipo(comboTipo, comboCategoria, comboTipo.value, "");
    });

    comboCategoria.addEventListener("change", () => {
      aplicarTipoAtencionAutomatico(comboTipo, comboCategoria);
    });

    await cargarTipos(
      comboTipo,
      comboCategoria,
      valorRegistradoTipo,
      valorRegistradoCategoria
    );

    return true;
  }

  function esperarPar(tipoId, categoriaId) {
    const intentar = async () => {
      const ok = await inicializarParTipoCategoria(tipoId, categoriaId);
      if (!ok) window.setTimeout(intentar, 120);
    };

    intentar();
  }

  window.evRecargarComboTipoCategoria = async function (modo = "add", codTipo = "", codCategoria = "") {
    const ids = getParIds(modo === "edit" ? "edit" : "add");
    const comboTipo = document.getElementById(ids.tipoId);
    const comboCategoria = document.getElementById(ids.categoriaId);
    if (!comboTipo || !comboCategoria) return;

    /*
     * codTipo se conserva para edición. En agregar, al cambiar Producto /
     * Servicio, el endpoint y resolverTipoAutomatico determinan el tipo
     * correcto automáticamente.
     */
    comboTipo.dataset.valorRegistrado = codTipo ? String(codTipo) : "";
    comboCategoria.dataset.valorRegistrado = codCategoria ? String(codCategoria) : "";

    await cargarTipos(
      comboTipo,
      comboCategoria,
      comboTipo.dataset.valorRegistrado,
      comboCategoria.dataset.valorRegistrado
    );
  };

  window.evInitComboTipoCategoriaEdit = function (codTipo, codCategoria) {
    const comboTipo = document.getElementById("edit_comboTipo");
    const comboCategoria = document.getElementById("edit_comboCategoria");
    if (!comboTipo || !comboCategoria) return;

    comboTipo.dataset.valorRegistrado = codTipo ? String(codTipo) : "";
    comboCategoria.dataset.valorRegistrado = codCategoria ? String(codCategoria) : "";

    window.evRecargarComboTipoCategoria("edit", codTipo, codCategoria);
  };

  document.addEventListener("change", (event) => {
    const target = event.target;
    if (!target?.matches) return;

    if (target.matches('input[name="tipo_publicacion"], input[name="edit_tipo_publicacion"]')) {
      const modo = getModoByRadioName(target.name);
      window.evRecargarComboTipoCategoria(modo, "", "");
    }
  });

  document.addEventListener("shown.bs.modal", (event) => {
    if (event.target?.id === "modalAgregarPublicacion") {
      window.evRecargarComboTipoCategoria("add", "", "");
    }

    if (event.target?.id === "modalEditarPublicacion") {
      const comboTipo = document.getElementById("edit_comboTipo");
      const comboCategoria = document.getElementById("edit_comboCategoria");

      window.evRecargarComboTipoCategoria(
        "edit",
        comboTipo?.dataset?.valorRegistrado || "",
        comboCategoria?.dataset?.valorRegistrado || ""
      );
    }
  });

  document.addEventListener("DOMContentLoaded", () => {
    esperarPar("comboTipo", "comboCategoria");
    esperarPar("edit_comboTipo", "edit_comboCategoria");
  });
})();
