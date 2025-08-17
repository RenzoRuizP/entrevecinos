
document.getElementById("btnCerrarSesion").addEventListener("click", function(e) {
    e.preventDefault();
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "Tu sesión se cerrará y deberás iniciar de nuevo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../controllers/logoutController.php';
        }
    });
});

