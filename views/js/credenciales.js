// views/js/credenciales.js
// ================================================
// UX de la vista Credenciales (cambiar contraseña)
// Compatible con carga dinámica de vistas (AJAX)
// ================================================

(function () {
  // Contenedor principal donde se inyectan las vistas
  const mainContainer = document.getElementById("contenido-principal") || document.body;

  // Función que adjunta eventos a la vista de credenciales
  function attachCredencialesHandlers() {
    const form = document.getElementById("formCambiarContrasena");
    if (!form) return;

    // Evitar adjuntar handlers más de una vez
    if (form.dataset.evHandlersAttached === "1") return;
    form.dataset.evHandlersAttached = "1";

    const inputActual = document.getElementById("password_actual");
    const inputNueva = document.getElementById("password_nueva");
    const inputConfirmar = document.getElementById("password_confirmar");
    const barStrength = document.getElementById("password_strength_bar");

    const btnGuardar = document.getElementById("btnGuardarContrasena");
    const btnCancelar = document.getElementById("btnCancelarContrasena");

    // ------------------------------
    // Evaluar fuerza de contraseña
    // ------------------------------
    function evaluarFuerza(password) {
      let score = 0;
      if (password.length >= 8) score++;
      if (/[A-Z]/.test(password)) score++;
      if (/[0-9]/.test(password)) score++;
      if (/[^A-Za-z0-9]/.test(password)) score++;

      const porcentaje = (score / 4) * 100;
      if (barStrength) {
        barStrength.style.width = `${porcentaje}%`;
      }
    }

    if (inputNueva && barStrength) {
      inputNueva.addEventListener("input", (e) => {
        evaluarFuerza(e.target.value || "");
      });
    }

    // ------------------------------
    // Botón Cancelar
    // ------------------------------
    if (btnCancelar) {
      btnCancelar.addEventListener("click", () => {
        form.reset();
        if (barStrength) barStrength.style.width = "0%";
      });
    }

    // ------------------------------
    // Botón Guardar (solo UX de momento)
    // ------------------------------
    if (btnGuardar) {
      btnGuardar.addEventListener("click", async () => {
        const actual = (inputActual?.value || "").trim();
        const nueva = (inputNueva?.value || "").trim();
        const confirmar = (inputConfirmar?.value || "").trim();

        if (!actual || !nueva || !confirmar) {
          Swal.fire({
            icon: "warning",
            title: "Campos incompletos",
            text: "Por favor, completa todos los campos."
          });
          return;
        }

        if (nueva.length < 8) {
          Swal.fire({
            icon: "warning",
            title: "Contraseña muy corta",
            text: "La nueva contraseña debe tener al menos 8 caracteres."
          });
          return;
        }

        if (nueva !== confirmar) {
          Swal.fire({
            icon: "error",
            title: "No coinciden",
            text: "La confirmación de contraseña no coincide."
          });
          return;
        }

        // Aquí se conectará al endpoint real cuando lo implementes:
        // const baseURL = (window.BASE_URL || "/entrevecinos").replace(/\/$/, "");
        // const resp = await fetch(`${baseURL}/api/usuario/cambiar-contrasena`, { ... });

        Swal.fire({
          icon: "success",
          title: "Vista lista",
          text: "El diseño y las validaciones de la vista de credenciales están listas. Solo falta conectar con el backend."
        });
      });
    }
  }

  // Inicializar si la vista ya está presente al cargar el JS
  document.addEventListener("DOMContentLoaded", () => {
    attachCredencialesHandlers();
  });

  // Observar cambios en el contenedor principal para detectar
  // cuando se inyecta la vista de credenciales vía AJAX
  if (mainContainer && window.MutationObserver) {
    const observer = new MutationObserver(() => {
      attachCredencialesHandlers();
    });
    observer.observe(mainContainer, { childList: true, subtree: true });
  }
})();
