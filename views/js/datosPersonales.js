// ✅ DatosPersonales.js Premium
document.addEventListener('DOMContentLoaded', () => {
  const btnEditar = document.getElementById('btnEditar');
  const btnGuardar = document.getElementById('btnGuardar');
  const btnCancelar = document.getElementById('btnCancelar');
  const form = document.getElementById('formDatosPersonales');
  const inputs = form.querySelectorAll('input, textarea');

  // Función para habilitar edición con animación suave
  const habilitarEdicion = () => {
    inputs.forEach(input => {
      input.removeAttribute('disabled');
      input.classList.add('input-editando');
      input.style.transition = 'all 0.3s ease';
      input.focus();
    });
    btnEditar.style.display = 'none';
    btnGuardar.style.display = 'inline-block';
    btnCancelar.style.display = 'inline-block';
  };

  // Función para deshabilitar edición
  const deshabilitarEdicion = () => {
    inputs.forEach(input => {
      input.setAttribute('disabled', true);
      input.classList.remove('input-editando');
    });
    btnEditar.style.display = 'inline-block';
    btnGuardar.style.display = 'none';
    btnCancelar.style.display = 'none';
  };

  // Validación básica de campos
  const validarCampos = () => {
    let valido = true;
    inputs.forEach(input => {
      input.classList.remove('is-invalid');
      if (input.hasAttribute('required') && !input.value.trim()) {
        input.classList.add('is-invalid');
        valido = false;
      }
      if (input.type === 'email' && input.value) {
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regexEmail.test(input.value)) {
          input.classList.add('is-invalid');
          valido = false;
        }
      }
    });
    return valido;
  };

  // Cancelar edición
  btnCancelar.addEventListener('click', () => {
    deshabilitarEdicion();
    // Recargar datos de PHP directamente
    inputs.forEach(input => {
      input.value = input.getAttribute('value') || '';
    });
  });

  // Habilitar edición
  btnEditar.addEventListener('click', habilitarEdicion);

  // Guardar cambios
  btnGuardar.addEventListener('click', async () => {
    if (!validarCampos()) {
      Swal.fire({
        icon: 'warning',
        title: 'Campos inválidos',
        text: 'Por favor revisa los campos resaltados.'
      });
      return;
    }

    const datosActualizados = {};
    inputs.forEach(input => datosActualizados[input.id] = input.value);

    Swal.fire({
      title: 'Guardando...',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const response = await fetch(`${window.BASE_URL}api/usuario/actualizar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(datosActualizados)
      });

      if (!response.ok) throw new Error('Error al guardar datos');

      Swal.fire({
        icon: 'success',
        title: 'Datos guardados',
        timer: 2000,
        showConfirmButton: false
      });

      // Actualizar los atributos value de los inputs para mantenerlos sincronizados
      inputs.forEach(input => input.setAttribute('value', input.value));

      deshabilitarEdicion();

    } catch (error) {
      console.error(error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudieron guardar los datos. Intenta nuevamente.'
      });
    }
  });
});
