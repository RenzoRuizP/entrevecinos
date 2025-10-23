document.addEventListener('DOMContentLoaded', async () => {
  const btnEditar = document.getElementById('btnEditar');
  const btnGuardar = document.getElementById('btnGuardar');
  const btnCancelar = document.getElementById('btnCancelar');
  const inputs = document.querySelectorAll('#formDatosPersonales input, #formDatosPersonales textarea');

  // ✅ Cargar datos del usuario desde la API
  try {
    const response = await fetch(`${window.BASE_URL}api/usuario/datos`, { credentials: 'include' });
    const usuario = await response.json();
    console.log('Datos del usuario:', usuario);

    // Llenar los inputs automáticamente
    for (const key in usuario) {
      const input = document.getElementById(key);
      if (input) input.value = usuario[key];
    }

  } catch (error) {
    console.error('Error al cargar los datos del usuario:', error);
  }

  // Habilitar edición
  btnEditar.addEventListener('click', () => {
    inputs.forEach(input => input.removeAttribute('disabled'));
    btnEditar.style.display = 'none';
    btnGuardar.style.display = 'inline-block';
    btnCancelar.style.display = 'inline-block';
  });

  // Cancelar edición
  btnCancelar.addEventListener('click', () => {
    inputs.forEach(input => input.setAttribute('disabled', true));
    btnEditar.style.display = 'inline-block';
    btnGuardar.style.display = 'none';
    btnCancelar.style.display = 'none';
  });

  // Guardar cambios (simulación, luego conectar con tu API real)
  btnGuardar.addEventListener('click', async () => {
    const datosActualizados = {};
    inputs.forEach(input => datosActualizados[input.id] = input.value);

    Swal.fire({
      title: 'Guardando...',
      text: 'Por favor espera unos segundos.',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    try {
      // Simulación de guardado
      await new Promise(resolve => setTimeout(resolve, 1500));
      Swal.fire({
        icon: 'success',
        title: 'Datos actualizados',
        text: 'Tu información personal ha sido guardada correctamente.',
        timer: 2000,
        showConfirmButton: false
      });

      inputs.forEach(input => input.setAttribute('disabled', true));
      btnEditar.style.display = 'inline-block';
      btnGuardar.style.display = 'none';
      btnCancelar.style.display = 'none';

    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo guardar los datos. Intenta nuevamente.'
      });
    }
  });
});
