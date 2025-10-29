// ✅ Publicaciones: JS solo de UI (delegación), compatible con carga dinámica
(function () {
  const BASE = (window.BASE_URL || "/entrevecinos").replace(/\/$/, "");

  // Handler central para clicks (delegación)
  document.addEventListener("click", (e) => {
    const btnBuscar = e.target.closest("#btnBuscarPublicacion");
    const btnAgregar = e.target.closest("#btnAgregarPublicacion");

    if (btnBuscar) {
      if (window.Swal) {
        Swal.fire({
          icon: "info",
          title: "Buscar publicaciones",
          text: "Aquí irá el modal o panel de filtros.",
          confirmButtonColor: "#0F592F",
        });
      }
      return;
    }

    if (btnAgregar) {
      if (window.Swal) {
        Swal.fire({
          icon: "success",
          title: "Nueva publicación",
          text: "Aquí abriremos el formulario de alta.",
          confirmButtonColor: "#0F592F",
        });
      }
      return;
    }
  });

  // (Opcional) Si luego necesitas inicializar algo al insertar la vista
  const contenedor = document.getElementById("contenido-principal");
  if (contenedor) {
    const mo = new MutationObserver(() => {
      // aquí podrías detectar si existe .ev-publist para correr algún init
      // const vista = contenedor.querySelector(".ev-publist");
      // if (vista) { ... }
    });
    mo.observe(contenedor, { childList: true, subtree: true });
  }
})();
