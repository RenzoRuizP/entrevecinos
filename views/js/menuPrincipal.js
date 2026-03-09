// views/js/menuPrincipal.js — utilidades + navegación AJAX (Shell MenuPrincipal)

const params = new URLSearchParams(window.location.search);

if (params.has('success')) {
  const success = params.get('success');
  if (success === 'login_exitoso') {
    Swal.fire({
      icon: 'success',
      title: 'Bienvenido',
      text: 'Inicio de sesión exitoso',
      timer: 2000,
      showConfirmButton: false,
    });
    window.history.replaceState({}, document.title, window.location.pathname);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
  const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

  if (sidebarWrapper && window.OverlayScrollbarsGlobal?.OverlayScrollbars) {
    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
      scrollbars: {
        theme: 'os-theme-light',
        autoHide: 'leave',
        clickScroll: true,
      },
    });
  }

  const baseUrl = (window.BASE_URL || '/').replace(/\/+$/, '');
  const basePath = baseUrl === '' ? '/' : baseUrl;

  const $main = document.getElementById('contenido-principal');

  function normalizeInternalPath(input) {
    if (!input) return '/';

    let path = String(input).trim();

    try {
      if (/^https?:\/\//i.test(path)) {
        const u = new URL(path);
        path = u.pathname + (u.search || '');
      }
    } catch (_) {}

    if (path[0] !== '/') path = '/' + path;

    if (basePath !== '/' && path.startsWith(basePath + '/')) {
      path = path.slice(basePath.length);
      if (path === '') path = '/';
    }

    path = path.replace(/\/{2,}/g, '/');

    return path;
  }

  function showShellLoader() {
    if (!$main) return;
    $main.innerHTML = `
      <div class="ev-shell-loading" aria-busy="true" aria-live="polite">
        <div class="ev-box">
          <div class="ev-spin"></div>
          <div>Cargando módulo...</div>
        </div>
      </div>
    `;
  }

  async function mostrarAlertaYRedirigirBloqueo(payload) {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;
    window.__EV_AUTH_REDIRECTING__ = true;

    const mensaje = (payload && (payload.mensaje || payload.error))
      ? (payload.mensaje || payload.error)
      : 'Tu cuenta fue bloqueada. Se cerró tu sesión por seguridad.';

    const redirect = (payload && payload.redirect) ? payload.redirect : `${baseUrl}/login`;

    if (window.Swal?.fire) {
      await Swal.fire({
        icon: 'warning',
        title: 'Cuenta bloqueada',
        text: mensaje,
        confirmButtonText: 'Ir al login',
        confirmButtonColor: '#EA7C12',
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    } else {
      alert(mensaje);
    }

    window.location.assign(redirect);
  }

  async function mostrarAlertaYRedirigirSesion(payload) {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;
    window.__EV_AUTH_REDIRECTING__ = true;

    const mensaje = (payload && (payload.mensaje || payload.error))
      ? (payload.mensaje || payload.error)
      : 'Tu sesión expiró. Vuelve a iniciar sesión.';

    const redirect = (payload && payload.redirect) ? payload.redirect : `${baseUrl}/login`;

    if (window.Swal?.fire) {
      await Swal.fire({
        icon: 'info',
        title: 'Sesión finalizada',
        text: mensaje,
        confirmButtonText: 'Ir al login',
        confirmButtonColor: '#EA7C12',
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    } else {
      alert(mensaje);
    }

    window.location.assign(redirect);
  }

  async function fetchPartial(path) {
    const clean = normalizeInternalPath(path);
    const url = `${baseUrl}${clean}${clean.includes('?') ? '&' : '?'}partial=1`;

    const res = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-PARTIAL': '1',
        'Accept': 'text/html,application/json'
      },
      credentials: 'same-origin',
    });

    const ct = (res.headers.get('Content-Type') || '').toLowerCase();

    if (ct.includes('application/json')) {
      const j = await res.json().catch(() => null);
      return { ok: false, json: j, status: res.status };
    }

    const html = await res.text();
    return { ok: res.ok, html, status: res.status };
  }

  async function navigateTo(path, pushState = true) {
    const clean = normalizeInternalPath(path);

    showShellLoader();

    const r = await fetchPartial(clean);

    if (!r.ok && r.json) {
      if (r.status === 403 && r.json.error === 'CUENTA_BLOQUEADA') {
        await mostrarAlertaYRedirigirBloqueo(r.json);
        return;
      }

      if (r.status === 401 || r.json.error === 'UNAUTHORIZED') {
        await mostrarAlertaYRedirigirSesion(r.json);
        return;
      }

      if (r.json.error === 'CUENTA_OBSERVADA' && r.json.redirect) {
        if (window.__EV_AUTH_REDIRECTING__ === true) return;
        window.__EV_AUTH_REDIRECTING__ = true;
        window.location.href = r.json.redirect;
        return;
      }

      const msg = r.json.mensaje || r.json.error || 'Error al cargar módulo';
      if ($main) {
        $main.innerHTML = `<div style="padding:18px;font-family:system-ui">
          <h4 style="margin:0 0 8px">No se pudo cargar</h4>
          <pre style="background:#f6f6f6;border:1px solid #eee;padding:12px;border-radius:10px;overflow:auto">${JSON.stringify(r.json, null, 2)}</pre>
          <div style="color:#666;margin-top:8px">${msg}</div>
        </div>`;
      }
      return;
    }

    if (!r.ok) {
      if ($main) {
        $main.innerHTML = `<div style="padding:18px;font-family:system-ui">
          <h4 style="margin:0 0 8px">No se pudo cargar</h4>
          <div style="color:#666">HTTP ${r.status}</div>
        </div>`;
      }
      return;
    }

    if ($main) $main.innerHTML = r.html;

    if (pushState) {
      const newUrl = `${baseUrl}/MenuPrincipal?ev_goto=${encodeURIComponent(clean)}`;
      window.history.pushState({ ev_goto: clean }, '', newUrl);
    }
  }

  document.addEventListener('click', (e) => {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;

    const a = e.target.closest('a');
    if (!a) return;

    if (a.target === '_blank' || a.hasAttribute('download')) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    const href = a.getAttribute('href') || '';
    if (!href) return;
    if (href.startsWith('#')) return;

    const clean = normalizeInternalPath(href);

    if (!clean.startsWith('/')) return;

    e.preventDefault();
    navigateTo(clean, true);
  });

  window.addEventListener('popstate', () => {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;

    const p = new URLSearchParams(window.location.search);
    const ev = p.get('ev_goto');
    if (ev) {
      navigateTo(ev, false);
    }
  });

  const evGoto = params.get('ev_goto');
  if (evGoto) {
    navigateTo(evGoto, false);
  }
});