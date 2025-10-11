
document.addEventListener("DOMContentLoaded", () => {
  const editarBtn = document.getElementById("editarPerfilBtn");

  if (editarBtn) {
    editarBtn.addEventListener("click", () => {
      Swal.fire({
        icon: 'info',
        title: 'Editar perfil',
        text: 'Funcionalidad en desarrollo...',
        confirmButtonColor: '#22c55e'
      });
    });
  }
});
