document.addEventListener('DOMContentLoaded', function () {
  const urlParams = new URLSearchParams(window.location.search);

  // ==========================
  // Manejo de errores
  // ==========================
  const error = urlParams.get('error');

  if (error) {
    const mensajesError = {
      CI: 'La contraseña es incorrecta',
      NE: 'El usuario no existe',
      IN: 'El usuario está inactivo. Consulte con su administrador',
      sin_token: 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.',
      token_expirado: 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.',
      token_error: 'Hubo un problema con tu sesión. Intenta nuevamente.'
    };

    const mensaje = mensajesError[error] || '';

    if (mensaje) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        confirmButtonColor: '#0F592F' // verde Entre Vecinos
      });
    }
  }

  // ==========================
  // Manejo de éxito
  // ==========================
  const success = urlParams.get('success');
  if (success) {
    Swal.fire({
      icon: 'success',
      title: '¡Bienvenido!',
      text: 'Has iniciado sesión correctamente.',
      confirmButtonColor: '#0F592F'
    });
  }
});
