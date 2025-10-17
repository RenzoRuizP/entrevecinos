document.addEventListener("DOMContentLoaded", () => {
  const btnCerrarSesion = document.getElementById("btnCerrarSesion");
  const btnPerfil = document.getElementById("btnPerfil");

  // 🔐 Cerrar sesión
  btnCerrarSesion?.addEventListener("click", (e) => {
    e.preventDefault();
    Swal.fire({
      title: "¿Cerrar sesión?",
      text: "Tu sesión se cerrará y volverás al login.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Sí, salir",
      cancelButtonText: "Cancelar"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = "/entrevecinos/controllers/logoutController.php";
      }
    });
  });

  // 👤 Ir a perfil
  btnPerfil?.addEventListener("click", (e) => {
    e.preventDefault();
    window.location.href = "/entrevecinos/views/MiPerfilView.php";
  });
});
