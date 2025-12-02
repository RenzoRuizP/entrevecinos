// ✅ views/js/datosPersonales.js
// Manejo de la vista "Datos personales" con carga parcial (AJAX) en Entre Vecinos

(function () {
  const baseURL = (window.BASE_URL || "/entrevecinos").replace(/\/$/, "");
  let initializedOnce = false;

  /**
   * Toma un "snapshot" del formulario para poder detectar cambios
   */
  function getFormState(form) {
    const data = {};
    form
      .querySelectorAll("input, select, textarea")
      .forEach((el) => {
        if (!el.id) return;
        data[el.id] = el.value;
      });
    return data;
  }

  /**
   * Restaura el formulario a un estado dado
   */
  function setFormState(form, state) {
    form
      .querySelectorAll("input, select, textarea")
      .forEach((el) => {
        if (!el.id || !(el.id in state)) return;
        el.value = state[el.id];
      });
  }

  /**
   * Compara estado actual vs. inicial
   */
  function hasChanges(form, initialState) {
    return Object.keys(initialState).some((id) => {
      const el = form.querySelector("#" + id);
      if (!el) return false;
      return (el.value || "") !== (initialState[id] || "");
    });
  }

  /**
   * Muestra u oculta el botón Cancelar según existan cambios
   */
  function toggleCancelar(form, initialState, btnCancelar) {
    if (!btnCancelar) return;
    if (hasChanges(form, initialState)) {
      btnCancelar.style.display = "inline-flex";
    } else {
      btnCancelar.style.display = "none";
    }
  }

  /**
   * Inicializa toda la lógica de Datos personales.
   * Se llama tanto en DOMContentLoaded como cada vez que se inserta la vista por AJAX.
   */
  function initDatosPersonales() {
    const container = document.querySelector(".container-datos-personales");
    if (!container) return; // la vista no está en pantalla

    // ✅ Si existe un inicializador global para los combos, lo ejecutamos.
    // Esto NO rompe nada si no existe (simplemente no hace nada).
    if (window.EV_initCombosCondominio) {
      try {
        window.EV_initCombosCondominio();
      } catch (e) {
        console.warn("EV_initCombosCondominio lanzó un error:", e);
      }
    }

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
    form.addEventListener("input", () => {
      toggleCancelar(form, initialState, btnCancelar);
    });
    form.addEventListener("change", () => {
      toggleCancelar(form, initialState, btnCancelar);
    });

    // Handler Guardar
    if (btnGuardar) {
      btnGuardar.addEventListener("click", async (e) => {
        e.preventDefault();

        // Obtenemos valores
        const nombre = form.querySelector("#nombre_completo")?.value.trim() || "";
        const email = form.querySelector("#email")?.value.trim() || "";
        const documento = form.querySelector("#documento")?.value.trim() || "";
        const telefono = form.querySelector("#telefono")?.value.trim() || "";
        const condominio = form.querySelector("#comboCondominio")?.value || "";
        const torre = form.querySelector("#comboTorre")?.value || "";
        const departamento = form.querySelector("#comboDepartamento")?.value || "";

        // Validación básica en front
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

        if (!departamento) {
          Swal.fire({
            icon: "warning",
            title: "Selecciona tu departamento",
            text: "Debes seleccionar un departamento para guardar los cambios.",
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
          const response = await fetch(`${baseURL}/api/usuario/actualizar`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "include",
            body: JSON.stringify({
              // 🔹 Claves que espera el backend (devueltas en UsuarioDatosController / Usuario.php)
              email: email,
              nombre_completo: nombre,
              documento: documento,
              telefono: telefono,
              comboDepartamento: departamento,

              // 🔹 Extra, por si luego lo usas en el backend
              codigo_condominio: condominio,
              codigo_torre: torre,
            }),
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

            // Actualizamos el estado inicial (ya no hay cambios)
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

  // 1) Intentar inicializar cuando carga toda la página
  document.addEventListener("DOMContentLoaded", () => {
    initDatosPersonales();

    // 2) Observar cambios en el contenedor principal para cuando la vista entra por AJAX
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
