document.addEventListener('DOMContentLoaded', function () {
  const urlParams = new URLSearchParams(window.location.search);
  
  // Manejar errores
  const error = urlParams.get('error');
  if (error) {
    let mensaje = '';
    switch (error) {
      case 'CI':
        mensaje = 'La contraseña es incorrecta';
        break;
      case 'NE':
        mensaje = 'El usuario no existe';
        break;
      case 'IN':
        mensaje = 'El usuario está inactivo. Consulte con su administrador';
        break;
      case 'sin_token':
      case 'token_expirado':
        mensaje = 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.';
        break;
      case 'token_error':
        mensaje = 'Hubo un problema con tu sesión. Intenta nuevamente.';
        break;
    }
    if (mensaje !== '') {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        confirmButtonColor: '#d33'
      });
    }
  }

  // Manejar éxito
  const success = urlParams.get('success');
  if (success) {
    Swal.fire({
      icon: 'success',
      title: '¡Bienvenido!',
      text: 'Has iniciado sesión correctamente.',
      confirmButtonColor: '#3085d6'
    });
  }
});

/*
$("#miBotonCerrarSesion").click(function() {
    $.ajax({
        url: 'ruta/al/controlador/cerrarSesion', // Cambia esto por la URL real de tu controlador
        method: 'POST', // o GET, según cómo manejes tu controlador
        success: function(response) {
            // Aquí puedes verificar la respuesta si quieres
            // Luego rediriges al login o a donde quieras
            window.location.href = 'login.html'; 
        },
        error: function() {
            alert('Error al cerrar sesión');
        }
    });
});

*/