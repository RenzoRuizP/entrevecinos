document.addEventListener("DOMContentLoaded", () => {
  const btnCerrarSesion = document.getElementById("btnCerrarSesion");
  const btnPerfil = document.getElementById("btnPerfil");
  const contenedor = document.getElementById("contenido-principal");

  // 🔹 Cerrar sesión con confirmación
  if (btnCerrarSesion) {
    btnCerrarSesion.addEventListener("click", (e) => {
      e.preventDefault();

      Swal.fire({
        title: "¿Cerrar sesión?",
        text: "Se cerrará tu sesión actual",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, salir",
        cancelButtonText: "Cancelar"
      }).then((result) => {
        if (!result.isConfirmed) return;

        // 🔹 Usar fetch POST al logout centralizado
        fetch("/entrevecinos/logout", {
          method: "GET", // puede ser GET o POST según tu AuthController
          headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            Swal.fire({
              icon: "success",
              title: "Sesión cerrada",
              text: data.message,
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              // 🔹 Redirigir al login
              window.location.href = "/entrevecinos/";
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: data.message || "No se pudo cerrar sesión correctamente."
            });
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo cerrar sesión."
          });
        });
      });
    });
  }

  // 🔹 Cargar perfil ("Datos Personales") dentro del contenedor principal
  if (btnPerfil && contenedor) {
    btnPerfil.addEventListener("click", async (e) => {
      e.preventDefault();

      const ruta = "/entrevecinos/mi-perfil"; // ✅ Ruta centralizada
      contenedor.innerHTML = `
        <div class="text-center p-5">
          <div class="spinner-border text-success" role="status"></div>
          <p class="mt-3">Cargando perfil...</p>
        </div>
      `;

      try {
        const response = await fetch(ruta);
        if (!response.ok) throw new Error("Error al cargar la vista de perfil.");

        const html = await response.text();
        contenedor.innerHTML = html;

        Swal.fire({
          icon: "info",
          title: "Perfil cargado",
          text: "Datos personales disponibles.",
          timer: 1200,
          showConfirmButton: false
        });

      } catch (error) {
        console.error(error);
        contenedor.innerHTML = `
          <div class="alert alert-danger m-5 text-center">
            <strong>Error:</strong> No se pudo cargar la vista de perfil.
          </div>
        `;
      }
    });
  }
});
