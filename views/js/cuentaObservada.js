// views/js/cuentaObservada.js
(function () {
  'use strict';

  // =========================
  // Blindaje de contexto
  // =========================
  if (typeof window.EV_MODO_VISTA === 'undefined') return;
  if (window.EV_MODO_VISTA !== 'observado') return;
  if (!window.BASE_URL) return;

  const form   = document.getElementById('evFormReenviar');
  const boxObs = document.getElementById('evObservacionBox');
  const boxOk  = document.getElementById('evGraciasBox');
  const input  = document.getElementById('evComprobante');

  if (!form || !input) return;

  const btnSubmit = form.querySelector('button[type="submit"]');

  const MAX_MB = 5;
  const MAX_BYTES = MAX_MB * 1024 * 1024;
  const ALLOWED_EXT = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

  let isSubmitting = false;

  // =========================
  // Helpers UI
  // =========================
  function showError(msg) {
    if (window.Swal) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: msg,
        confirmButtonColor: '#EA7C12'
      });
    } else {
      alert(msg);
    }
  }

  function setLoading(loading) {
    if (!btnSubmit) return;
    btnSubmit.disabled = loading;
    btnSubmit.innerHTML = loading
      ? '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...'
      : '<i class="bi bi-upload me-1"></i> Enviar comprobante';
  }

  function getFileExtension(name) {
    return name.split('.').pop().toLowerCase();
  }

  // =========================
  // Submit
  // =========================
  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (isSubmitting) return;

    if (!input.files || !input.files.length) {
      showError('Selecciona un archivo.');
      return;
    }

    const file = input.files[0];
    const ext = getFileExtension(file.name);

    if (!ALLOWED_EXT.includes(ext)) {
      showError('Formato no permitido. Usa PDF, JPG, PNG o WEBP.');
      return;
    }

    if (file.size > MAX_BYTES) {
      showError(`El archivo supera el límite de ${MAX_MB}MB.`);
      return;
    }

    const fd = new FormData();
    fd.append('comprobante', file);

    isSubmitting = true;
    setLoading(true);

    try {
      const resp = await fetch(
        `${window.BASE_URL}/api/cuenta-observada/reenviar`,
        {
          method: 'POST',
          body: fd,
          credentials: 'include'
        }
      );

      let json;
      try {
        json = await resp.json();
      } catch {
        throw new Error('No se pudo procesar la respuesta del servidor.');
      }

      if (!resp.ok || !json.ok) {

        if (resp.status === 401) {
          window.location.href = `${window.BASE_URL}/login`;
          return;
        }

        if (json.redirect) {
          window.location.href = json.redirect;
          return;
        }

        throw new Error(json.mensaje || 'No se pudo enviar el comprobante.');
      }

      // =========================
      // UI OK
      // =========================
      form.classList.add('d-none');
      if (boxObs) boxObs.classList.add('d-none');
      if (boxOk) boxOk.classList.remove('d-none');

    } catch (err) {
      showError(
        err.message ||
        'Ocurrió un problema al enviar el archivo. Intenta nuevamente.'
      );
      setLoading(false);
      isSubmitting = false;
      return;
    }

    // Éxito: no reactivar botón
  });

})();
