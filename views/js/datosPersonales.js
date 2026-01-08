// ✅ views/js/datosPersonales.js (EV) — residencia + ubigeo + solicitud con upload
(function () {
  "use strict";

  const baseURL = (window.BASE_URL || "/entrevecinos").replace(/\/$/, "");
  let initializedOnce = false;

  // Endpoints existentes
  const API_URBANIZACIONES = `${baseURL}/urbanizaciones`;
  const API_UB_DEPTOS = `${baseURL}/ubigeo/departamentos`;
  const API_UB_PROVS  = (depId) => `${baseURL}/ubigeo/departamentos/${depId}/provincias`;
  const API_UB_DISTS  = (provId) => `${baseURL}/ubigeo/provincias/${provId}/distritos`;

  // Guardado existente
  const API_ACTUALIZAR = `${baseURL}/api/usuario/actualizar`;

  // ✅ Nuevo endpoint (FormData + archivo)
  const API_SOLICITAR_CAMBIO = `${baseURL}/api/usuario/solicitar-cambio-residencia`;

  function $(sel, root = document) {
    return root.querySelector(sel);
  }

  function setHidden(el, hidden) {
    if (!el) return;
    el.classList.toggle("d-none", !!hidden);
  }

  function resetSelect(sel, placeholder, { disabled = false } = {}) {
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    sel.value = "";
    sel.disabled = !!disabled;
  }

  function fillSelect(sel, placeholder, arr, mapFn, selectedValue) {
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    (arr || []).forEach((item) => {
      const opt = document.createElement("option");
      const { value, text } = mapFn(item);
      opt.value = value;
      opt.textContent = text;
      if (selectedValue !== undefined && selectedValue !== null && String(selectedValue) === String(value)) {
        opt.selected = true;
      }
      sel.appendChild(opt);
    });
  }

  async function fetchJSON(url, opts = {}) {
    const res = await fetch(url, {
      method: opts.method || "GET",
      cache: "no-store",
      credentials: "include",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json",
        ...(opts.headers || {})
      },
      body: opts.body || null
    });

    const ct = (res.headers.get("content-type") || "").toLowerCase();
    if (!ct.includes("application/json")) {
      const txt = await res.text().catch(() => "");
      throw new Error("Respuesta no JSON. " + (txt ? txt.slice(0, 120) : ""));
    }

    const data = await res.json().catch(() => ({}));
    if (!res.ok || data?.ok === false) {
      throw new Error(data?.mensaje || data?.message || `HTTP ${res.status}`);
    }
    return data;
  }

  // ---- estado base (para detectar cambio de residencia) ----
  function getResidenciaState() {
    const root = document.getElementById("dp-residencia");
    if (!root) {
      return { tipo: "", codCondominio: "", codUrbanizacion: "", direccion: "", ub_depto: "", ub_prov: "", ub_dist: "" };
    }

    // Nota: dataset convierte kebab-case -> camelCase
    return {
      tipo: (root.dataset.tipo || "").trim(),
      codCondominio: (root.dataset.codigoCondominio || "").trim(),
      codUrbanizacion: (root.dataset.codigoUrbanizacion || "").trim(),
      direccion: (root.dataset.direccion || "").trim(),
      ub_depto: (root.dataset.ubDepto || "").trim(),
      ub_prov: (root.dataset.ubProv || "").trim(),
      ub_dist: (root.dataset.ubDist || "").trim(),
      // departamento EV si existiera
      codDepartamento: (root.dataset.codigoDepartamento || "").trim(),
    };
  }

  function getFormState(form) {
    const data = {};
    form.querySelectorAll("input, select, textarea").forEach((el) => {
      if (!el.id) return;
      if (el.type === "file") return; // file no se compara en cancel
      data[el.id] = el.value;
    });
    return data;
  }

  function setFormState(form, state) {
    form.querySelectorAll("input, select, textarea").forEach((el) => {
      if (!el.id || !(el.id in state)) return;
      if (el.type === "file") return;
      el.value = state[el.id];
    });
  }

  function hasChanges(form, initialState) {
    return Object.keys(initialState).some((id) => {
      const el = form.querySelector("#" + id);
      if (!el) return false;
      return (el.value || "") !== (initialState[id] || "");
    });
  }

  function toggleCancelar(form, initialState, btnCancelar) {
    if (!btnCancelar) return;
    btnCancelar.style.display = hasChanges(form, initialState) ? "inline-flex" : "none";
  }

  // ---- residencia UI + loaders ----
  function currentTipo(container, base) {
    const rCondo = container.querySelector("#dpTipoCondominio");
    const rUrb = container.querySelector("#dpTipoUrbanizacion");
    if (rCondo && rCondo.checked) return "condominio";
    if (rUrb && rUrb.checked) return "urbanizacion";
    return (base.tipo || "").toLowerCase();
  }

  function residenciaChanged(container, base) {
    const tipo = currentTipo(container, base);

    const condominio = container.querySelector("#comboCondominio")?.value || "";
    const departamento = container.querySelector("#comboDepartamento")?.value || "";
    const urbanizacion = container.querySelector("#comboUrbanizacion")?.value || "";
    const direccion = container.querySelector("#direccion")?.value.trim() || "";

    const ubD = container.querySelector("#dpUbDepto")?.value || "";
    const ubP = container.querySelector("#dpUbProv")?.value || "";
    const ubDi = container.querySelector("#dpUbDist")?.value || "";

    if (String(tipo) !== String((base.tipo || "").toLowerCase())) return true;

    if (tipo === "condominio") {
      if (String(condominio) !== String(base.codCondominio)) return true;
      // depto: si no tienes base.codDepartamento, igual detecta cambio cuando usuario selecciona uno distinto a vacío
      if (base.codDepartamento) {
        if (String(departamento) !== String(base.codDepartamento)) return true;
      } else {
        if (String(departamento || "") && String(departamento) !== String(base.codDepartamento || "")) return true;
      }
    }

    if (tipo === "urbanizacion") {
      if (String(urbanizacion) !== String(base.codUrbanizacion)) return true;
    }

    if (String(direccion) !== String(base.direccion)) return true;

    if (String(ubD) !== String(base.ub_depto)) return true;
    if (String(ubP) !== String(base.ub_prov)) return true;
    if (String(ubDi) !== String(base.ub_dist)) return true;

    return false;
  }

  async function initUbigeo(container, base) {
    const selDep = container.querySelector("#dpUbDepto");
    const selProv = container.querySelector("#dpUbProv");
    const selDist = container.querySelector("#dpUbDist");
    if (!selDep || !selProv || !selDist) return;

    if (container.dataset.dpUbigeoInit === "1") return;
    container.dataset.dpUbigeoInit = "1";

    const preDep = selDep.dataset.valorRegistrado || base.ub_depto || "";
    const preProv = selProv.dataset.valorRegistrado || base.ub_prov || "";
    const preDist = selDist.dataset.valorRegistrado || base.ub_dist || "";

    resetSelect(selDep, "Cargando...", { disabled: true });
    resetSelect(selProv, "-- Seleccione --", { disabled: true });
    resetSelect(selDist, "-- Seleccione --", { disabled: true });

    try {
      const deps = await fetchJSON(API_UB_DEPTOS);
      fillSelect(
        selDep,
        "-- Seleccione --",
        Array.isArray(deps) ? deps : [],
        (d) => ({ value: d.codigo_departamento, text: d.nombre_departamento }),
        preDep
      );
      selDep.disabled = false;

      // Si hay preselect, cargar provs
      if (preDep) {
        await loadProvincias(preDep, preProv, preDist);
      }
    } catch (e) {
      console.error("[EV][Ubigeo] Error deps:", e);
      resetSelect(selDep, "No se pudo cargar", { disabled: true });
    }

    async function loadProvincias(depId, preselectProv = "", preselectDist = "") {
      resetSelect(selProv, "Cargando...", { disabled: true });
      resetSelect(selDist, "-- Seleccione --", { disabled: true });
      try {
        const provs = await fetchJSON(API_UB_PROVS(depId));
        fillSelect(
          selProv,
          "-- Seleccione --",
          Array.isArray(provs) ? provs : [],
          (p) => ({ value: p.codigo_provincia, text: p.nombre_provincia }),
          preselectProv
        );
        selProv.disabled = false;

        if (preselectProv) {
          await loadDistritos(preselectProv, preselectDist);
        }
      } catch (e) {
        console.error("[EV][Ubigeo] Error provs:", e);
        resetSelect(selProv, "No se pudo cargar", { disabled: true });
      }
    }

    async function loadDistritos(provId, preselectDist = "") {
      resetSelect(selDist, "Cargando...", { disabled: true });
      try {
        const dists = await fetchJSON(API_UB_DISTS(provId));
        fillSelect(
          selDist,
          "-- Seleccione --",
          Array.isArray(dists) ? dists : [],
          (d) => ({ value: d.codigo_distrito, text: d.nombre_distrito }),
          preselectDist
        );
        selDist.disabled = false;
      } catch (e) {
        console.error("[EV][Ubigeo] Error dists:", e);
        resetSelect(selDist, "No se pudo cargar", { disabled: true });
      }
    }

    selDep.addEventListener("change", async () => {
      const depId = selDep.value;
      if (!depId) {
        resetSelect(selProv, "-- Seleccione --", { disabled: true });
        resetSelect(selDist, "-- Seleccione --", { disabled: true });
        return;
      }
      await loadProvincias(depId, "", "");
    });

    selProv.addEventListener("change", async () => {
      const provId = selProv.value;
      if (!provId) {
        resetSelect(selDist, "-- Seleccione --", { disabled: true });
        return;
      }
      await loadDistritos(provId, "");
    });
  }

  async function initUrbanizaciones(container, base) {
    const comboUrbanizacion = container.querySelector("#comboUrbanizacion");
    if (!comboUrbanizacion) return;

    // solo carga cuando se use
    const preselect = comboUrbanizacion.dataset.valorRegistrado || base.codUrbanizacion || "";

    resetSelect(comboUrbanizacion, "Cargando urbanizaciones...", { disabled: true });

    try {
      const data = await fetchJSON(API_URBANIZACIONES);
      const arr = Array.isArray(data) ? data : [];
      fillSelect(
        comboUrbanizacion,
        "-- Seleccione urbanización --",
        arr,
        (u) => ({ value: u.codigo_urbanizacion, text: u.nombre_urbanizacion }),
        preselect
      );
      comboUrbanizacion.disabled = false;
    } catch (e) {
      console.error("[EV][DatosPersonales] Error cargando urbanizaciones:", e);
      resetSelect(comboUrbanizacion, "No se pudo cargar. Reintenta.", { disabled: true });
    }
  }

  function initResidenciaUI(container) {
    const base = getResidenciaState();

    const wrapCondominio = container.querySelector("#wrapCondominio");
    const wrapUrbanizacion = container.querySelector("#wrapUrbanizacion");
    const wrapDireccion = container.querySelector("#wrapDireccion");
    const wrapUpload = container.querySelector("#wrapUploadDomicilio");

    const inputDireccion = container.querySelector("#direccion");
    const fileDoc = container.querySelector("#dpDocDomicilio");

    if (!wrapCondominio || !wrapUrbanizacion || !wrapDireccion) return;

    // Evita doble init
    if (container.dataset.dpResidenciaInit === "1") return;
    container.dataset.dpResidenciaInit = "1";

    // set dirección desde base si faltara
    if (inputDireccion) {
      inputDireccion.value = inputDireccion.value || base.direccion || "";
    }

    // init ubigeo (si existe)
    initUbigeo(container, base);

    function refresh() {
      const tipo = currentTipo(container, base);

      setHidden(wrapCondominio, tipo !== "condominio");
      setHidden(wrapUrbanizacion, tipo !== "urbanizacion");
      setHidden(wrapDireccion, !(tipo === "condominio" || tipo === "urbanizacion"));

      // Cargar combos según tipo
      if (tipo === "condominio") {
        if (window.EV_initCombosCondominio) {
          try { window.EV_initCombosCondominio(); } catch (e) { console.warn("EV_initCombosCondominio error:", e); }
        }
      } else if (tipo === "urbanizacion") {
        initUrbanizaciones(container, base);
      }

      const changed = residenciaChanged(container, base);
      if (wrapUpload) setHidden(wrapUpload, !changed);

      if (fileDoc) fileDoc.required = !!changed;
    }

    // listeners
    ["#dpTipoCondominio", "#dpTipoUrbanizacion", "#comboCondominio", "#comboDepartamento", "#comboUrbanizacion", "#direccion", "#dpUbDepto", "#dpUbProv", "#dpUbDist"]
      .forEach((sel) => {
        const el = container.querySelector(sel);
        if (!el) return;
        el.addEventListener("change", refresh);
        el.addEventListener("input", refresh);
      });

    refresh();
  }

  // ---- guardado principal ----
  function initDatosPersonales() {
    const container = document.querySelector(".container-datos-personales");
    if (!container) return;

    initResidenciaUI(container);

    if (container.dataset.dpInitialized === "1") return;
    container.dataset.dpInitialized = "1";

    const form = container.querySelector("#formDatosPersonales");
    if (!form) return;

    const btnGuardar = container.querySelector("#btnGuardar");
    const btnCancelar = container.querySelector("#btnCancelar");

    let initialState = getFormState(form);
    form.addEventListener("input", () => toggleCancelar(form, initialState, btnCancelar));
    form.addEventListener("change", () => toggleCancelar(form, initialState, btnCancelar));

    function swalWarn(title, text) {
      Swal.fire({ icon: "warning", title, text, confirmButtonColor: "#115C41" });
    }

    function swalErr(text) {
      Swal.fire({ icon: "error", title: "Error", text, confirmButtonColor: "#BF3604" });
    }

    function swalOk(title, text) {
      Swal.fire({ icon: "success", title, text, confirmButtonColor: "#115C41" });
    }

    if (btnGuardar) {
      btnGuardar.addEventListener("click", async (e) => {
        e.preventDefault();

        const base = getResidenciaState();

        const nombre = container.querySelector("#nombre_completo")?.value.trim() || "";
        const email = container.querySelector("#email")?.value.trim() || "";
        const telefono = container.querySelector("#telefono")?.value.trim() || "";

        const tipo = currentTipo(container, base); // condominio|urbanizacion
        const direccion = container.querySelector("#direccion")?.value.trim() || "";

        const condominio = container.querySelector("#comboCondominio")?.value || "";
        const departamento = container.querySelector("#comboDepartamento")?.value || "";
        const urbanizacion = container.querySelector("#comboUrbanizacion")?.value || "";

        const ub_depto = container.querySelector("#dpUbDepto")?.value || "";
        const ub_prov = container.querySelector("#dpUbProv")?.value || "";
        const ub_dist = container.querySelector("#dpUbDist")?.value || "";

        const fileDoc = container.querySelector("#dpDocDomicilio");

        if (!nombre) return swalWarn("Completa tu nombre", "El campo nombre completo es obligatorio.");
        if (!email) return swalWarn("Correo requerido", "No se encontró el correo asociado a tu cuenta.");

        // Validación residencia mínima
        if (tipo === "condominio") {
          if (!condominio) return swalWarn("Selecciona tu condominio", "Debes seleccionar un condominio para continuar.");
          if (!departamento) return swalWarn("Selecciona tu departamento", "Debes seleccionar un departamento para continuar.");
          if (!direccion) return swalWarn("Dirección requerida", "Completa tu dirección para continuar.");
        } else if (tipo === "urbanizacion") {
          if (!urbanizacion) return swalWarn("Selecciona tu urbanización", "Debes seleccionar una urbanización para continuar.");
          if (!direccion) return swalWarn("Dirección requerida", "Completa tu dirección para continuar.");
        } else {
          return swalWarn("Residencia no definida", "No se pudo determinar tu tipo de residencia. Contacta soporte.");
        }

        // Ubigeo recomendado/obligatorio para auditoría
        if (!ub_depto || !ub_prov || !ub_dist) {
          return swalWarn("Ubigeo requerido", "Selecciona Departamento, Provincia y Distrito para tu domicilio.");
        }

        const cambioResidencia = residenciaChanged(container, base);

        btnGuardar.disabled = true;
        btnGuardar.classList.add("saving");
        const originalText = btnGuardar.innerHTML;
        btnGuardar.innerHTML = "Guardando...";

        try {
          if (!cambioResidencia) {
            // ✅ flujo actual (NO romper): JSON a /api/usuario/actualizar
            const payload = {
              email,
              nombre_completo: nombre,
              telefono,

              tipo_conjunto: tipo,
              direccion,

              codigo_condominio: tipo === "condominio" ? condominio : null,
              comboDepartamento: tipo === "condominio" ? departamento : null,
              codigo_departamento: tipo === "condominio" ? departamento : null,

              codigo_urbanizacion: tipo === "urbanizacion" ? urbanizacion : null,

              ubigeo_departamento: ub_depto,
              ubigeo_provincia: ub_prov,
              ubigeo_distrito: ub_dist,
            };

            const res = await fetch(API_ACTUALIZAR, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
              },
              credentials: "include",
              body: JSON.stringify(payload),
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || data.mensaje || data.error || `Error HTTP ${res.status}`);

            swalOk("Datos actualizados", data.message || data.mensaje || "Tus datos se guardaron correctamente.");
            initialState = getFormState(form);
            toggleCancelar(form, initialState, btnCancelar);
            return;
          }

          // ✅ flujo NUEVO: Solicitud de cambio de residencia (FormData + archivo)
          if (!fileDoc || !fileDoc.files || !fileDoc.files[0]) {
            return swalWarn("Adjunta un recibo", "Para cambiar de domicilio debes adjuntar un recibo o comprobante.");
          }

          const f = fileDoc.files[0];
          const max = 5 * 1024 * 1024;
          const okType = /^(application\/pdf|image\/jpeg|image\/png)$/i.test(f.type);
          const okExt = /\.(pdf|jpg|jpeg|png)$/i.test(f.name || "");
          if (!(okType || okExt)) {
            return swalWarn("Archivo no permitido", "Solo se permite PDF, JPG o PNG.");
          }
          if (f.size > max) {
            return swalWarn("Archivo muy pesado", "El archivo no debe superar 5MB.");
          }

          const fd = new FormData();
          fd.append("email", email);
          fd.append("telefono", telefono);
          fd.append("nombre_completo", nombre);

          fd.append("tipo_conjunto", tipo);
          fd.append("direccion", direccion);

          fd.append("codigo_condominio", tipo === "condominio" ? condominio : "");
          fd.append("codigo_departamento", tipo === "condominio" ? departamento : "");
          fd.append("codigo_urbanizacion", tipo === "urbanizacion" ? urbanizacion : "");

          fd.append("ubigeo_departamento", ub_depto);
          fd.append("ubigeo_provincia", ub_prov);
          fd.append("ubigeo_distrito", ub_dist);

          // archivo
          fd.append("documento_domicilio", f);

          const res2 = await fetch(API_SOLICITAR_CAMBIO, {
            method: "POST",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            credentials: "include",
            body: fd,
          });

          const data2 = await res2.json().catch(() => ({}));
          if (!res2.ok) throw new Error(data2.message || data2.mensaje || data2.error || `Error HTTP ${res2.status}`);

          swalOk(
            "Solicitud enviada",
            data2.mensaje || "Tu solicitud de cambio de domicilio fue enviada. Un administrador la revisará."
          );

          // Importante: NO actualizamos base local automáticamente; se aplica solo tras aprobación admin.
          initialState = getFormState(form);
          toggleCancelar(form, initialState, btnCancelar);

        } catch (err) {
          console.error("[EV][DatosPersonales] Error:", err);
          swalErr(err.message || "No se pudo conectar con el servidor.");
        } finally {
          btnGuardar.disabled = false;
          btnGuardar.classList.remove("saving");
          btnGuardar.innerHTML = originalText;
        }
      });
    }

    if (btnCancelar) {
      btnCancelar.addEventListener("click", (e) => {
        e.preventDefault();
        setFormState(form, initialState);
        toggleCancelar(form, initialState, btnCancelar);
      });
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    initDatosPersonales();

    const main = document.getElementById("contenido-principal");
    if (main && !initializedOnce) {
      initializedOnce = true;
      const observer = new MutationObserver(() => initDatosPersonales());
      observer.observe(main, { childList: true, subtree: true });
    }
  });
})();
