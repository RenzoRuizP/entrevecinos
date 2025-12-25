// ✅ views/js/datosPersonales.js
// Manejo de la vista "Datos personales" con carga parcial (AJAX) en Entre Vecinos
(function () {
  const baseURL = (window.BASE_URL || "/entrevecinos").replace(/\/$/, "");
  let initializedOnce = false;

  /* =========================
     HELPERS (no rompen nada)
  ========================== */
  function buildURL(path) {
    if (!window.BASE_URL) return path;
    return window.BASE_URL.replace(/\/+$/, "") + "/" + path.replace(/^\/+/, "");
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
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    (arr || []).forEach((item) => {
      const opt = document.createElement("option");
      const { value, text } = mapFn(item);
      opt.value = value;
      opt.textContent = text;
      if (selectedValue && String(selectedValue) === String(value)) opt.selected = true;
      sel.appendChild(opt);
    });
  }

  async function fetchJSON(url) {
    const res = await fetch(url, { cache: "no-store" });
    if (!res.ok) throw new Error(`Error HTTP ${res.status} al cargar ${url}`);
    return await res.json();
  }

  function getResidenciaState() {
    const root = document.getElementById("dp-residencia");
    if (!root) return { tipo: "", codCondominio: "", codUrbanizacion: "", direccion: "" };

    return {
      tipo: (root.dataset.tipo || "").trim(), // condominio | urbanizacion
      codCondominio: (root.dataset.codigoCondominio || root.dataset.codigoCondominio === "" ? root.dataset.codigoCondominio : "").trim() || (root.dataset.codigoCondominio ?? ""),
      // OJO: dataset convierte data-codigo-condominio => dataset.codigoCondominio
      codUrbanizacion: (root.dataset.codigoUrbanizacion || "").trim(),
      direccion: (root.dataset.direccion || "").trim(),
    };
  }

  /* =========================
     SNAPSHOT / CHANGES
  ========================== */
  function getFormState(form) {
    const data = {};
    form.querySelectorAll("input, select, textarea").forEach((el) => {
      if (!el.id) return;
      data[el.id] = el.value;
    });
    return data;
  }

  function setFormState(form, state) {
    form.querySelectorAll("input, select, textarea").forEach((el) => {
      if (!el.id || !(el.id in state)) return;
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

  /* =========================
     ✅ CARGA / LECTURA (Residencia)
     - Corrige de raíz: condominio vs urbanización
     - No toca lo que ya funciona (EV_initCombosCondominio)
  ========================== */
  function initResidenciaDatosPersonales(container) {
    const wrapCondominio = container.querySelector("#wrapCondominio");
    const wrapUrbanizacion = container.querySelector("#wrapUrbanizacion");
    const wrapDireccion = container.querySelector("#wrapDireccion");

    const comboUrbanizacion = container.querySelector("#comboUrbanizacion");
    const inputDireccion = container.querySelector("#direccion");

    // Si por algún motivo no existe, no hacemos nada (no rompemos).
    if (!wrapCondominio || !wrapUrbanizacion || !wrapDireccion || !inputDireccion) return;

    // Evitar reinit en la misma inserción
    if (container.dataset.dpResidenciaInit === "1") return;
    container.dataset.dpResidenciaInit = "1";

    const st = getResidenciaState();
    const tipo = (st.tipo || "").toLowerCase();

    // Dirección: siempre visible para ambos casos según requerimiento (cuando corresponde)
    inputDireccion.value = inputDireccion.value || st.direccion || "";

    // Estado base: ocultar todo y luego mostrar lo correcto
    setHidden(wrapCondominio, true);
    setHidden(wrapUrbanizacion, true);
    setHidden(wrapDireccion, true);

    // Cache y anti-race (simple y suficiente)
    let reqId = 0;
    let cacheUrbanizaciones = null;

    async function cargarUrbanizacionesYPreselect() {
      if (!comboUrbanizacion) return;

      reqId++;
      const myReq = reqId;

      const preselect = comboUrbanizacion.dataset.valorRegistrado || st.codUrbanizacion || "";

      resetSelect(comboUrbanizacion, "Cargando urbanizaciones...", { disabled: true });

      try {
        const data = cacheUrbanizaciones ?? await fetchJSON(buildURL("urbanizaciones"));
        if (myReq !== reqId) return;

        cacheUrbanizaciones = Array.isArray(data) ? data : [];
        fillSelect(
          comboUrbanizacion,
          "-- Seleccione urbanización --",
          cacheUrbanizaciones,
          (u) => ({ value: u.codigo_urbanizacion, text: u.nombre_urbanizacion }),
          preselect
        );

        comboUrbanizacion.disabled = false;
      } catch (e) {
        console.error("[EV][DatosPersonales] Error cargando urbanizaciones:", e);
        resetSelect(comboUrbanizacion, "No se pudo cargar. Reintenta.", { disabled: true });
      }
    }

    // Render según tipo
    if (tipo === "condominio") {
      setHidden(wrapCondominio, false);
      setHidden(wrapUrbanizacion, true);
      setHidden(wrapDireccion, false);

      // ✅ Mantener tu inicializador existente (no se altera)
      if (window.EV_initCombosCondominio) {
        try {
          window.EV_initCombosCondominio();
        } catch (e) {
          console.warn("EV_initCombosCondominio lanzó un error:", e);
        }
      }
      return;
    }

    if (tipo === "urbanizacion") {
      setHidden(wrapUrbanizacion, false);
      setHidden(wrapCondominio, true);
      setHidden(wrapDireccion, false);

      cargarUrbanizacionesYPreselect();
      return;
    }

    // Caso borde: si no hay tipo, no mostrar residencia
    setHidden(wrapCondominio, true);
    setHidden(wrapUrbanizacion, true);
    setHidden(wrapDireccion, true);
  }

  /* =========================
     INIT PRINCIPAL
  ========================== */
  function initDatosPersonales() {
    const container = document.querySelector(".container-datos-personales");
    if (!container) return;

    // ✅ Cargar/leer la residencia (nuevo requerimiento)
    initResidenciaDatosPersonales(container);

    // Evitar duplicar listeners si se vuelve a abrir la vista
    if (container.dataset.dpInitialized === "1") return;
    container.dataset.dpInitialized = "1";

    const form = container.querySelector("#formDatosPersonales");
    if (!form) return;

    const btnGuardar = container.querySelector("#btnGuardar");
    const btnCancelar = container.querySelector("#btnCancelar");

    // Tomamos el estado inicial del formulario
    let initialState = getFormState(form);

    // Escuchar cambios en los campos para mostrar/ocultar "Cancelar"
    form.addEventListener("input", () => toggleCancelar(form, initialState, btnCancelar));
    form.addEventListener("change", () => toggleCancelar(form, initialState, btnCancelar));

    // Handler Guardar
    if (btnGuardar) {
      btnGuardar.addEventListener("click", async (e) => {
        e.preventDefault();

        const residencia = getResidenciaState();
        const tipoConjunto = (residencia.tipo || "").toLowerCase(); // condominio | urbanizacion

        // Obtenemos valores base
        const nombre = form.querySelector("#nombre_completo")?.value.trim() || "";
        const email = form.querySelector("#email")?.value.trim() || "";
        const telefono = form.querySelector("#telefono")?.value.trim() || "";

        // Residencia
        const direccion = form.querySelector("#direccion")?.value.trim() || "";

        const condominio = form.querySelector("#comboCondominio")?.value || "";
        const torre = form.querySelector("#comboTorre")?.value || "";
        const departamento = form.querySelector("#comboDepartamento")?.value || "";

        const urbanizacion = form.querySelector("#comboUrbanizacion")?.value || "";

        // Validación base
        if (!nombre) {
          Swal.fire({
            icon: "warning",
            title: "Completa tu nombre",
            text: "El campo nombre completo es obligatorio.",
            confirmButtonColor: "#115C41",
          });
          return;
        }

        if (!email) {
          Swal.fire({
            icon: "warning",
            title: "Correo requerido",
            text: "No se encontró el correo asociado a tu cuenta.",
            confirmButtonColor: "#115C41",
          });
          return;
        }

        // ✅ Validación por tipo (corrección de raíz)
        if (tipoConjunto === "condominio") {
          if (!departamento) {
            Swal.fire({
              icon: "warning",
              title: "Selecciona tu departamento",
              text: "Debes seleccionar un departamento para guardar los cambios.",
              confirmButtonColor: "#115C41",
            });
            return;
          }
          if (!direccion) {
            Swal.fire({
              icon: "warning",
              title: "Dirección requerida",
              text: "Completa tu dirección para guardar los cambios.",
              confirmButtonColor: "#115C41",
            });
            return;
          }
        } else if (tipoConjunto === "urbanizacion") {
          if (!urbanizacion) {
            Swal.fire({
              icon: "warning",
              title: "Selecciona tu urbanización",
              text: "Debes seleccionar una urbanización para guardar los cambios.",
              confirmButtonColor: "#115C41",
            });
            return;
          }
          if (!direccion) {
            Swal.fire({
              icon: "warning",
              title: "Dirección requerida",
              text: "Completa tu dirección para guardar los cambios.",
              confirmButtonColor: "#115C41",
            });
            return;
          }
        } else {
          // Si no hay tipo, evitamos guardar residencia inconsistente
          Swal.fire({
            icon: "warning",
            title: "Residencia no definida",
            text: "No se pudo determinar si vives en condominio o urbanización. Vuelve a iniciar sesión o contacta soporte.",
            confirmButtonColor: "#115C41",
          });
          return;
        }

        // Estado de "guardando"
        btnGuardar.disabled = true;
        btnGuardar.classList.add("saving");
        const originalText = btnGuardar.innerHTML;
        btnGuardar.innerHTML = "Guardando...";

        try {
          // ✅ Payload compatible con el nuevo modelo (sin quitar lo existente)
          const payload = {
            email: email,
            nombre_completo: nombre,
            telefono: telefono,

            // Nuevo modelo residencia
            tipo_conjunto: tipoConjunto,
            direccion: direccion,

            // Condominio
            codigo_condominio: tipoConjunto === "condominio" ? condominio : null,
            codigo_torre: tipoConjunto === "condominio" ? torre : null,
            comboDepartamento: tipoConjunto === "condominio" ? departamento : null,

            // Urbanización
            codigo_urbanizacion: tipoConjunto === "urbanizacion" ? urbanizacion : null,
          };

          const response = await fetch(`${baseURL}/api/usuario/actualizar`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "include",
            body: JSON.stringify(payload),
          });

          const data = await response.json().catch(() => ({}));

          if (!response.ok) {
            throw new Error(data.mensaje || data.error || `Error HTTP ${response.status}`);
          }

          const ok = data.ok === true || data.success === true || !data.error;

          if (ok) {
            Swal.fire({
              icon: "success",
              title: "Datos actualizados",
              text: data.mensaje || "Tus datos personales se guardaron correctamente.",
              confirmButtonColor: "#115C41",
            });

            // Actualizar snapshot
            initialState = getFormState(form);
            toggleCancelar(form, initialState, btnCancelar);
          } else {
            Swal.fire({
              icon: "error",
              title: "No se pudo guardar",
              text: data.mensaje || data.error || "Ocurrió un problema al guardar tus datos.",
              confirmButtonColor: "#BF3604",
            });
          }
        } catch (err) {
          console.error("❌ Error al actualizar datos personales:", err);
          Swal.fire({
            icon: "error",
            title: "Error del servidor",
            text: err.message || "No se pudo conectar con el servidor.",
            confirmButtonColor: "#BF3604",
          });
        } finally {
          btnGuardar.disabled = false;
          btnGuardar.classList.remove("saving");
          btnGuardar.innerHTML = originalText;
        }
      });
    }

    // Handler Cancelar → volver al estado inicial
    if (btnCancelar) {
      btnCancelar.addEventListener("click", (e) => {
        e.preventDefault();
        setFormState(form, initialState);
        toggleCancelar(form, initialState, btnCancelar);
      });
    }
  }

  // 1) Inicializar en carga completa
  document.addEventListener("DOMContentLoaded", () => {
    initDatosPersonales();

    // 2) Observar cambios para cuando la vista entra por AJAX
    const main = document.getElementById("contenido-principal");
    if (main && !initializedOnce) {
      initializedOnce = true;
      const observer = new MutationObserver(() => {
        initDatosPersonales();
      });
      observer.observe(main, { childList: true, subtree: true });
    }
  });
})();
