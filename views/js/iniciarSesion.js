document.addEventListener('DOMContentLoaded', () => {
  const formLogin = document.getElementById('formLogin');
  const spinnerOverlay = document.getElementById('spinnerOverlay');

  if (formLogin) {
    formLogin.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(formLogin);

      // 🔹 Mostrar spinner mientras se envía
      if (spinnerOverlay) spinnerOverlay.style.display = 'flex';

      fetch('/entrevecinos/login', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        // 🔹 Ocultamos el spinner al recibir respuesta
        if (spinnerOverlay) spinnerOverlay.style.display = 'none';

        if (data.status === 'SI') {
          // ✅ Inicio correcto — SweetAlert2 con transición UX
          Swal.fire({
            title: '¡Bienvenido!',
            text: data.message || 'Inicio de sesión exitoso.',
            icon: 'success',
            showConfirmButton: false,
            timer: 2000,
            didOpen: () => {
              Swal.showLoading();
            }
          }).then(() => {
            // 🔹 Mostrar spinner antes de redirigir
            if (spinnerOverlay) spinnerOverlay.style.display = 'flex';

            // Pequeña pausa visual antes de ir al menú principal
            setTimeout(() => {
              window.location.href = data.redirect;
            }, 800);
          });

        } else {
          // ⚠️ Error con SweetAlert
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: data.message || 'Ocurrió un error al iniciar sesión.'
          });
        }
      })
      .catch(err => {
        // 🔹 En caso de error de red, ocultamos el spinner
        if (spinnerOverlay) spinnerOverlay.style.display = 'none';

        Swal.fire({
          icon: 'error',
          title: 'Error de red',
          text: 'No se pudo conectar al servidor.'
        });
        console.error('Error:', err);
      });
    });
  }
});
