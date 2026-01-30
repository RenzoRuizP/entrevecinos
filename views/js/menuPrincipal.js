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
  // OverlayScrollbars sidebar (si lo usas)
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

  // ============================
  // NAVEGACIÓN AJAX (Shell)
  // ============================

  const baseUrl = (window.BASE_URL || '/').replace(/\/+$/, ''); // "/entrevecinos"
  const basePath = baseUrl === '' ? '/' : baseUrl;            // "/entrevecinos" o "/"

  const $main = document.getElementById('contenido-principal');

  function normalizeInternalPath(input) {
    if (!input) return '/';

    let path = String(input).trim();

    // Si viene URL absoluta, quedarse con pathname+search
    try {
      if (/^https?:\/\//i.test(path)) {
        const u = new URL(path);
        path = u.pathname + (u.search || '');
      }
    } catch (_) {}

    // Asegurar que empiece con "/"
    if (path[0] !== '/') path = '/' + path;

    // ✅ FIX CLAVE: si viene con basePath incluido, lo recortamos
    // ej: "/entrevecinos/atender-cuentas" => "/atender-cuentas"
    if (basePath !== '/' && path.startsWith(basePath + '/')) {
      path = path.slice(basePath.length);
      if (path === '') path = '/';
    }

    // compactar slashes
    path = path.replace(/\/{2,}/g, '/');

    return path;
  }

  function showShellLoader() {
    if (!$main) return;
    $main.innerHTML = `
      <div class="ev-shell-loading" aria-busy="true" aria-live="polite">
        <div class="ev-box">
          <div class="ev-spin" aria-hidden="true"></div>
          <div>Cargando módulo...</div>
        </div>
      </div>
    `;
  }

  async function fetchPartial(path) {
    const clean = normalizeInternalPath(path);

    // Construir URL real de fetch: BASE_URL + clean + ?partial=1
    // baseUrl: "/entrevecinos"
    // clean: "/atender-cuentas"
    const url = `${baseUrl}${clean}${clean.includes('?') ? '&' : '?'}partial=1`;

    const res = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-PARTIAL': '1',
      },
      credentials: 'same-origin',
    });

    // Si el backend devuelve JSON (errores), intentar leerlo
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

    // Caso JSON error/control
    if (!r.ok && r.json) {
      // ✅ cuenta observada
      if (r.json.error === 'CUENTA_OBSERVADA' && r.json.redirect) {
        window.location.href = r.json.redirect;
        return;
      }

      // ✅ ruta no encontrada u otros
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

    // Actualiza URL del shell (ev_goto SIN basePath)
    if (pushState) {
      const newUrl = `${baseUrl}/MenuPrincipal?ev_goto=${encodeURIComponent(clean)}`;
      window.history.pushState({ ev_goto: clean }, '', newUrl);
    }
  }

  // Interceptar clicks internos (solo los marcados o todos los de tu dominio)
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a');
    if (!a) return;

    // permitir nuevos tabs, descargas, externos, anchors
    if (a.target === '_blank' || a.hasAttribute('download')) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    const href = a.getAttribute('href') || '';
    if (!href) return;
    if (href.startsWith('#')) return;

    // Solo interceptar internos del sistema
    const clean = normalizeInternalPath(href);

    // No interceptar logout/login raíz si quieres que sean full reload (opcional)
    // if (clean === '/logout' || clean === '/login') return;

    // Solo interceptar si es ruta que empieza con "/" (ya normalizada)
    if (!clean.startsWith('/')) return;

    // ✅ aquí hacemos navegación AJAX
    e.preventDefault();
    navigateTo(clean, true);
  });

  // Back/forward
  window.addEventListener('popstate', () => {
    const p = new URLSearchParams(window.location.search);
    const ev = p.get('ev_goto');
    if (ev) {
      navigateTo(ev, false);
    }
  });

  // Carga inicial si viene ev_goto
  const evGoto = params.get('ev_goto');
  if (evGoto) {
    navigateTo(evGoto, false);
  }
});
