// views/js/vistaRegistrarUser.js
document.addEventListener("DOMContentLoaded", () => {
  let pasoActual = 1;
  const totalPasos = 3;

  const btnAnterior = document.getElementById("btnAnterior");
  const btnSiguiente = document.getElementById("btnSiguiente");
  const btnRegistrar = document.getElementById("btnRegistrar");

  // Colores EV (fallback por si no existieran variables CSS)
  const EV_CONFIRM_COLOR = "#EA7C12"; // Naranja EV

  function markValidity(stepEl) {
    const fields = stepEl.querySelectorAll("input, select, textarea");
    let ok = true;

    fields.forEach((el) => {
      if (el.disabled) return;

      // Solo validamos required
      if (el.hasAttribute("required")) {
        const v = String(el.value || "").trim();
        const invalid = !v;

        el.classList.toggle("is-invalid", invalid);
        if (invalid) ok = false;
      }
    });

    return ok;
  }

  function validarPaso(paso) {
    const stepEl = document.getElementById(`formStep${paso}`);
    if (!stepEl) return true;

    const ok = markValidity(stepEl);

    if (!ok && window.Swal) {
      Swal.fire({
        icon: "warning",
        title: "Campos requeridos",
        text: "Completa los campos obligatorios para continuar.",
        confirmButtonText: "OK",
        confirmButtonColor: EV_CONFIRM_COLOR,
        allowOutsideClick: false,
        allowEscapeKey: true
      });
    }

    return ok;
  }

  function mostrarPaso(paso) {
    for (let i = 1; i <= totalPasos; i++) {
      const formStep = document.getElementById(`formStep${i}`);
      const progress = document.getElementById(`step${i}`);
      if (!formStep || !progress) continue;

      if (i === paso) {
        formStep.classList.remove("d-none");
        progress.classList.remove("bg-secondary");
        progress.classList.add("bg-success");
      } else {
        formStep.classList.add("d-none");
        progress.classList.remove("bg-success");
        progress.classList.add("bg-secondary");
      }
    }

    btnAnterior.disabled = paso === 1;
    btnSiguiente.classList.toggle("d-none", paso === totalPasos);
    btnRegistrar.classList.toggle("d-none", paso !== totalPasos);
  }

  // UX: quitar invalid al tipear/cambiar
  function engancharLimpiezaInvalidPorPaso(paso) {
    const stepEl = document.getElementById(`formStep${paso}`);
    if (!stepEl) return;

    // Evitar múltiples listeners si se reusa
    if (stepEl.dataset.evInvalidListener === "1") return;
    stepEl.dataset.evInvalidListener = "1";

    const handler = (e) => {
      const el = e.target;
      if (!el || el.disabled) return;
      if (!el.hasAttribute("required")) return;

      const v = String(el.value || "").trim();
      if (v) el.classList.remove("is-invalid");
    };

    stepEl.addEventListener("input", handler, true);
    stepEl.addEventListener("change", handler, true);
  }

  btnSiguiente.addEventListener("click", () => {
    if (!validarPaso(pasoActual)) return;

    if (pasoActual < totalPasos) {
      pasoActual++;
      mostrarPaso(pasoActual);
      engancharLimpiezaInvalidPorPaso(pasoActual);
    }
  });

  btnAnterior.addEventListener("click", () => {
    if (pasoActual > 1) {
      pasoActual--;
      mostrarPaso(pasoActual);
      engancharLimpiezaInvalidPorPaso(pasoActual);
    }
  });

  // Inicial
  mostrarPaso(pasoActual);
  engancharLimpiezaInvalidPorPaso(pasoActual);
});
