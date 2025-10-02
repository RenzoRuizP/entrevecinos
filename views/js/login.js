document.addEventListener("DOMContentLoaded", () => {
  let pasoActual = 1;
  const totalPasos = 3;

  const btnAnterior = document.getElementById("btnAnterior");
  const btnSiguiente = document.getElementById("btnSiguiente");
  const btnRegistrar = document.getElementById("btnRegistrar");

  function mostrarPaso(paso) {
    for (let i = 1; i <= totalPasos; i++) {
      const formStep = document.getElementById(`formStep${i}`);
      const progress = document.getElementById(`step${i}`);

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

    // botones
    btnAnterior.disabled = paso === 1;
    btnSiguiente.classList.toggle("d-none", paso === totalPasos);
    btnRegistrar.classList.toggle("d-none", paso !== totalPasos);
  }

  btnSiguiente.addEventListener("click", () => {
    if (pasoActual < totalPasos) {
      pasoActual++;
      mostrarPaso(pasoActual);
    }
  });


  btnAnterior.addEventListener("click", () => {
    if (pasoActual > 1) {
      pasoActual--;
      mostrarPaso(pasoActual);
    }
  });

  // mostrar el primer paso al iniciar
  mostrarPaso(pasoActual);
});