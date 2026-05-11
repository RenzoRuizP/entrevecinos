// views/js/cuentaObservada.js
(function () {
  'use strict';

  const page = document.body;
  if (!page) return;

  const baseUrl = String(page.dataset.baseUrl || window.BASE_URL || '').replace(/\/+$/, '');
  const modoVista = String(page.dataset.modoVista || window.EV_MODO_VISTA || '').trim();

  function hasSwal() {
    return (
      typeof window.Swal !== 'undefined' &&
      window.Swal &&
      typeof window.Swal.fire === 'function'
    );
  }

  function fireSwal(icon, title, text) {
    if (!hasSwal()) {
      alert(`${title}\n\n${text}`);
      return Promise.resolve();
    }

    return Swal.fire({
      icon,
      title,
      text,
      confirmButtonText: 'Entendido',
      buttonsStyling: false,
      customClass: {
        popup: 'ev-swal-popup',
        title: 'ev-swal-title',
        htmlContainer: 'ev-swal-html',
        confirmButton: 'ev-swal-confirm'
      }
    });
  }

  function initBotonEntendido() {
    const btnEntendido = document.getElementById('evBtnEntendido');
    if (!btnEntendido || btnEntendido.dataset.evBound === '1') return;

    btnEntendido.dataset.evBound = '1';

    btnEntendido.addEventListener('click', function () {
      fireSwal(
        'info',
        'Revisión en curso',
        'No necesitas hacer nada más por ahora. La revisión continuará automáticamente y te mostraremos el resultado cuando finalice.'
      );
    });
  }

  function initSoporteLinks() {
    const links = document.querySelectorAll('.js-ev-soporte-link, #evBtnInfoSupport');

    links.forEach(function (link) {
      if (!link || link.dataset.evBound === '1') return;

      link.dataset.evBound = '1';

      link.addEventListener('click', function (e) {
        e.preventDefault();

        fireSwal(
          'info',
          'Más información',
          'La revisión ayuda a mantener segura la comunidad. Si el equipo necesita una corrección, verás una observación y podrás reenviar tu comprobante.'
        );
      });
    });
  }

  function initReenvioComprobante() {
    if (modoVista !== 'observado') return;
    if (!baseUrl) return;

    const form = document.getElementById('evFormReenviar');
    const boxObs = document.getElementById('evObservacionBox');
    const boxOk = document.getElementById('evGraciasBox');
    const input = document.getElementById('evComprobante');

    if (!form || !input) return;
    if (form.dataset.evBound === '1') return;

    form.dataset.evBound = '1';

    const btnSubmit = form.querySelector('button[type="submit"]');

    const MAX_MB = 5;
    const MAX_BYTES = MAX_MB * 1024 * 1024;
    const ALLOWED_EXT = ['pdf', 'jpg', 'jpeg', 'png'];

    let isSubmitting = false;

    function showError(msg) {
      fireSwal('error', 'Error', msg);
    }

    function showSuccess(msg) {
      fireSwal('success', 'Comprobante recibido', msg);
    }

    function setLoading(loading) {
      if (!btnSubmit) return;

      btnSubmit.disabled = loading;

      btnSubmit.innerHTML = loading
        ? '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...'
        : '<i class="bi bi-upload"></i> Enviar comprobante';
    }

    function getFileExtension(name) {
      const parts = String(name || '').split('.');
      return parts.length > 1 ? parts.pop().toLowerCase() : '';
    }

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
        showError('Formato no permitido. Usa PDF, JPG, JPEG o PNG.');
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
        const resp = await fetch(`${baseUrl}/api/cuenta-observada/reenviar`, {
          method: 'POST',
          body: fd,
          credentials: 'include',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        let json;

        try {
          json = await resp.json();
        } catch (_) {
          throw new Error('No se pudo procesar la respuesta del servidor.');
        }

        if (!resp.ok || !json.ok) {
          if (resp.status === 401) {
            window.location.href = `${baseUrl}/login`;
            return;
          }

          if (json.redirect) {
            window.location.href = json.redirect;
            return;
          }

          throw new Error(json.mensaje || json.message || 'No se pudo enviar el comprobante.');
        }

        form.classList.add('d-none');

        if (boxObs) {
          boxObs.classList.add('d-none');
        }

        if (boxOk) {
          boxOk.classList.remove('d-none');
        }

        showSuccess('Recibimos tu comprobante corregido. El equipo volverá a revisar tu información.');

      } catch (err) {
        showError(
          err.message ||
          'Ocurrió un problema al enviar el archivo. Intenta nuevamente.'
        );

        setLoading(false);
        isSubmitting = false;
      }
    });
  }

  function boot() {
    initBotonEntendido();
    initSoporteLinks();
    initReenvioComprobante();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();