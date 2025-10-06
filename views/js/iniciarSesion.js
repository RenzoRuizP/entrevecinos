document.addEventListener('DOMContentLoaded', () => {
  const formLogin = document.getElementById('formLogin');

  if (formLogin) {
    formLogin.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(formLogin);

      fetch('/entrevecinos/login', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'SI') {
          // ✅ Redirige al menú principal
          window.location.href = data.redirect;
        } else {
          // ⚠️ Muestra error con SweetAlert
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: data.message || 'Ocurrió un error'
          });
        }
      })
      .catch(err => {
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
