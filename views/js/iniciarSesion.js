document.addEventListener('DOMContentLoaded', () => {
  const formLogin = document.getElementById('formLogin');
  if (!formLogin) return;

  const emailInput = document.getElementById('email');
  const claveInput = document.getElementById('clave');

  // BASE_URL robusto
  const BASE_URL = (window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/+$/, '');
  const LOGIN_URL = BASE_URL ? `${BASE_URL}/login` : '/login';

  const isOffline = () =>
    typeof navigator !== 'undefined' && navigator && navigator.onLine === false;

  const SwalButtonOrange = '#EA7C12';

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const swalNetworkError = (retryFn) => {
    Swal.fire({
      icon: 'error',
      title: 'No pudimos conectarnos',
      html: `
        <div style="color:#4B5563; line-height:1.45;">
          Parece que hay un problema de conexión en este momento.<br><br>
          <small style="color:#6B7280;">
            Revisa tu internet o intenta nuevamente en unos segundos.
          </small>
        </div>
      `,
      confirmButtonText: 'Reintentar',
      confirmButtonColor: SwalButtonOrange
    }).then((r) => {
      if (r.isConfirmed && typeof retryFn === 'function') retryFn();
    });
  };

  const swalServerError = () => {
    Swal.fire({
      icon: 'error',
      title: 'Estamos teniendo una demora',
      text: 'Nuestro sistema está presentando una falla temporal. Intenta nuevamente en unos minutos.',
      confirmButtonText: 'Entendido',
      confirmButtonColor: SwalButtonOrange
    });
  };

  const swalBadCredentials = () => {
    Swal.fire({
      icon: 'warning',
      title: 'Datos incorrectos',
      text: 'El correo o la contraseña no coinciden. Verifica tus datos e inténtalo nuevamente.',
      confirmButtonText: 'Entendido',
      confirmButtonColor: SwalButtonOrange
    });
  };

  const swalInactiveUser = (customMsg) => {
    Swal.fire({
      icon: 'info',
      title: 'Cuenta no disponible',
      text: customMsg || 'Tu cuenta está inactiva. Si crees que es un error, contáctanos por Soporte.',
      confirmButtonText: 'Entendido',
      confirmButtonColor: SwalButtonOrange
    });
  };

  const swalValidation = (msg) => {
    Swal.fire({
      icon: 'warning',
      title: 'Revisa tus datos',
      text: msg,
      confirmButtonText: 'Entendido',
      confirmButtonColor: SwalButtonOrange
    });
  };

  // Mapeo definitivo por HTTP status (para concordancia)
  const handleHttpByStatus = (status, backendMsg = '') => {
    if (status === 400) return swalValidation(backendMsg || 'Revisa los datos ingresados.');
    if (status === 401) return swalBadCredentials();         // Contraseña incorrecta (CI)
    if (status === 403) return swalInactiveUser(backendMsg); // Usuario inactivo (IN)
    if (status === 404) return swalBadCredentials();         // Usuario no existe (NE) -> mismo mensaje por seguridad
    if (status >= 500) return swalServerError();
    // Si llega algo raro (302/0), tratamos como falla temporal/conexión
    return swalNetworkError();
  };

  const doLogin = () => {
    const email = (emailInput?.value || '').trim();
    const clave = (claveInput?.value || '').trim();

    if (!email) return swalValidation('Ingresa tu correo electrónico.');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      return swalValidation('Ingresa un correo válido (ej: nombre@correo.com).');
    }
    if (!clave) return swalValidation('Ingresa tu contraseña.');

    if (isOffline()) return swalNetworkError(doLogin);

    const formData = new FormData(formLogin);

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 12000);

    fetch(LOGIN_URL, {
      method: 'POST',
      body: formData,
      credentials: 'include',
      signal: controller.signal
    })
      .then(async (res) => {
        clearTimeout(timeoutId);

        const httpStatus = res.status;
        const text = await res.text();

        // Intento parsear JSON; si no se puede, IGUAL decido por HTTP status
        try {
          const json = JSON.parse(text);
          json._httpStatus = httpStatus;
          return json;
        } catch {
          // Caso típico de tu problema: HTML / warning PHP / redirect / etc.
          // Aquí forzamos el mensaje correcto según el status.
          const err = new Error('INVALID_RESPONSE');
          err.httpStatus = httpStatus;
          err.raw = text;
          throw err;
        }
      })
      .then((data) => {
        // Éxito
        if (data.status === 'SI') {
          Swal.fire({
            title: data.title || '¡Bienvenido!',
            html: `
              <div class="ev-swal-registro__success" aria-hidden="true"><i class="bi bi-check-lg"></i></div>
              <p class="ev-swal-registro__lead">Acceso confirmado</p>
              <p class="ev-swal-registro__text">${escapeHtml(data.message || 'Estamos preparando tu espacio en Entre Vecinos.')}</p>
              <div class="ev-swal-login__loading" role="status" aria-live="polite">
                <span class="ev-swal-login__spinner" aria-hidden="true"></span>
                <strong>Cargando...</strong>
              </div>
            `,
            showConfirmButton: false,
            timer: 1450,
            timerProgressBar: false,
            background: '#FFFFFF',
            customClass: {
              popup: 'ev-swal-registro ev-swal-login'
            },
            showClass: { popup: 'swal2-show ev-swal-registro--show' },
            hideClass: { popup: 'swal2-hide' },
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then(() => {
            window.location.href = data.redirect;
          });
          return;
        }

        // Si no es SI, igual priorizamos HTTP status (concordancia)
        const http = data._httpStatus || 0;
        if (http) return handleHttpByStatus(http, data.message || '');

        // Si por algún motivo no vino status HTTP, fallback a mensaje
        const msg = data.message || '';
        Swal.fire({
          icon: 'warning',
          title: data.title || 'No pudimos iniciar sesión',
          text: msg || 'Revisa tus datos e inténtalo nuevamente.',
          confirmButtonText: 'Entendido',
          confirmButtonColor: SwalButtonOrange
        });
      })
      .catch((err) => {
        if (err && err.name === 'AbortError') {
          return swalNetworkError(doLogin);
        }

        // CLAVE: si no vino JSON, igual mostrar el mensaje correcto por status
        if (err && err.message === 'INVALID_RESPONSE') {
          const s = err.httpStatus || 0;

          // Si fueron credenciales mal, aquí ya no caerá en "demora"
          if (s === 401 || s === 404) return swalBadCredentials();
          if (s === 403) return swalInactiveUser('Tu cuenta está inactiva. Si crees que es un error, contáctanos por Soporte.');
          if (s >= 500) return swalServerError();

          // Si status es 0 o raro, tratamos como conexión
          return swalNetworkError(doLogin);
        }

        if (isOffline()) return swalNetworkError(doLogin);

        return swalNetworkError(doLogin);
      });
  };

  formLogin.addEventListener('submit', (e) => {
    e.preventDefault();
    doLogin();
  });
});
