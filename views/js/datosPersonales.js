// ✅ DatosPersonales.js

document.addEventListener('DOMContentLoaded', async () => {
  const btnEditar = document.getElementById('btnEditar');
  const btnGuardar = document.getElementById('btnGuardar');
  const btnCancelar = document.getElementById('btnCancelar');
  const inputs = document.querySelectorAll('#formDatosPersonales input, #formDatosPersonales textarea');

  // Simulación de carga de datos (reemplázalo por un fetch real más adelante)
  const usuario = {
    nombre: 'Renzo',
    apellido: 'Ruiz Pastor',
    correo: 'renzo@example.com',
    telefono: '987654321',
    direccion: 'Av. Los Olivos 123, Lima'
  };

  // Mostrar los datos
  document.getElementById('nombre').value = usuario.nombre;
  document.getElementById('apellido').value = usuario.apellido;
  document.getElementById('correo').value = usuario.correo;
  document.getElementById('telefono').value = usuario.telefono;
  document.getElementById('direccion').value = usuario.direccion;

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

  // Guardar cambios
  btnGuardar.addEventListener('click', async () => {
    const datosActualizados = {};
    inputs.forEach(input => datosActualizados[input.id] = input.value);

    // Mostrar spinner temporal
    Swal.fire({
      title: 'Guardando...',
      text: 'Por favor espera unos segundos.',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    try {
      // Aquí luego conectarás con tu API REST (POST /api/usuario/actualizar)
      await new Promise(resolve => setTimeout(resolve, 1500)); // Simulación
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
