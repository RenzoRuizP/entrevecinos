// views/js/menuIzquierda.js
// Navegación AJAX única y centralizada del sidebar EV
// Versión validada:
// - Mantiene navegación AJAX, ev_goto, popstate y carga de módulos.
// - Reemplaza Bootstrap Collapse SOLO para el acordeón del sidebar.
// - Conserva footer Ayuda / Cerrar sesión / Comunidad.
// - Evita duplicados visuales generados por versiones anteriores.

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  if (window.__EV_NAV_INIT__ === true) return;
  window.__EV_NAV_INIT__ = true;

  const BASE = (window.EV?.baseUrl ?? window.BASE_URL ?? window.EV_BASE_URL ?? '').toString().replace(/\/+$/, '');
  const SHELL_URL = `${BASE}/MenuPrincipal`;

  const main = document.getElementById('contenido-principal');
  const sidebar = document.getElementById('sidebar');

  let communityRefreshPromise = null;
  let communityLastRefreshAt = 0;

  function nombreComunidadVisible(tipo, nombre) {
    const raw = String(nombre || '').trim();
    if (!raw) return '';

    const label = tipo === 'urbanizacion'
      ? 'Urbanización'
      : (tipo === 'condominio' ? 'Condominio' : 'Comunidad');

    const rawLower = raw.toLocaleLowerCase('es-PE');
    const labelLower = label.toLocaleLowerCase('es-PE');

    return rawLower === labelLower || rawLower.startsWith(`${labelLower} `)
      ? raw
      : `${label} ${raw}`;
  }

  async function refreshSidebarCommunity(options = {}) {
    const card = document.querySelector('.ev-sidebar-community-card');
    const nameEl = document.getElementById('evSidebarCommunityName');
    const iconEl = document.getElementById('evSidebarCommunityIcon');

    if (!card || !nameEl || !iconEl) return null;

    const minInterval = options.force === true ? 0 : 5000;
    if (Date.now() - communityLastRefreshAt < minInterval) return null;
    if (communityRefreshPromise) return communityRefreshPromise;

    communityRefreshPromise = (async () => {
      try {
        const response = await fetch(`${BASE}/api/usuario/comunidad-actual?_=${Date.now()}`, {
          credentials: 'include',
          cache: 'no-store',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const json = await response.json().catch(() => ({}));
        if (!response.ok || json?.ok !== true) return null;

        const data = json?.data || {};
        const tipo = String(data?.tipo_conjunto || '').trim().toLowerCase();
        const nombre = String(
          data?.conjunto_nombre
          || data?.nombre_conjunto
          || data?.condominio_nombre
          || data?.urbanizacion_nombre
          || ''
        ).trim();

        if (!nombre || !['condominio', 'urbanizacion'].includes(tipo)) {
          return data;
        }

        card.dataset.communityType = tipo;
        nameEl.textContent = nombreComunidadVisible(tipo, nombre);
        iconEl.className = tipo === 'urbanizacion'
          ? 'bi bi-houses'
          : 'bi bi-buildings';

        document.dispatchEvent(new CustomEvent('ev:community-updated', {
          detail: { ...data, nombre_visible: nameEl.textContent }
        }));

        return data;
      } catch (error) {
        if (options.silent !== true) {
          console.warn('[EV][Sidebar][comunidad_actual]', error);
        }
        return null;
      } finally {
        communityLastRefreshAt = Date.now();
        communityRefreshPromise = null;
      }
    })();

    return communityRefreshPromise;
  }

  if (!main) {
    console.warn('[EV][NAV] Falta #contenido-principal. No se inicializa navegación AJAX.');
    return;
  }

  let backdrop = document.getElementById('sidebar-backdrop');
  if (!backdrop) {
    backdrop = document.createElement('div');
    backdrop.id = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
  }

  function closeSidebarMobile() {
    if (!sidebar) return;

    sidebar.classList.remove('open', 'active');
    document.body.classList.remove('ev-sidebar-open');

    if (backdrop) {
      backdrop.classList.remove('show', 'active');
    }
  }

  let overlayRefCount = 0;
  let overlayWatchdog = null;
  let overlayShowTimer = null;

  function ensureEvOverlay() {
    let ov = document.getElementById('ev-nav-overlay');
    if (ov) return ov;

    ov = document.createElement('div');
    ov.id = 'ev-nav-overlay';
    ov.className = 'ev-global-loading-overlay';
    ov.setAttribute('aria-hidden', 'true');
    ov.setAttribute('role', 'status');
    ov.setAttribute('aria-live', 'polite');

    const box = document.createElement('div');
    box.className = 'ev-global-loading-compact';

    const spinner = document.createElement('span');
    spinner.className = 'ev-global-loading-spinner';
    spinner.setAttribute('aria-hidden', 'true');

    const txt = document.createElement('strong');
    txt.className = 'ev-global-loading-text';
    txt.textContent = 'Cargando...';

    box.appendChild(spinner);
    box.appendChild(txt);
    ov.appendChild(box);
    document.body.appendChild(ov);

    return ov;
  }

  function showEvOverlay() {
    overlayRefCount = Math.max(0, overlayRefCount) + 1;
    const ov = ensureEvOverlay();

    if (overlayShowTimer) clearTimeout(overlayShowTimer);
    overlayShowTimer = setTimeout(() => {
      overlayShowTimer = null;
      if (overlayRefCount > 0) ov.style.display = 'flex';
    }, 120);

    if (overlayWatchdog) clearTimeout(overlayWatchdog);
    overlayWatchdog = setTimeout(() => {
      overlayRefCount = 0;
      if (overlayShowTimer) {
        clearTimeout(overlayShowTimer);
        overlayShowTimer = null;
      }
      ov.style.display = 'none';
    }, 20000);
  }

  function hideEvOverlay(force = false) {
    if (force) {
      overlayRefCount = 0;
    } else {
      overlayRefCount = Math.max(0, overlayRefCount - 1);
    }

    if (overlayRefCount === 0) {
      if (overlayShowTimer) {
        clearTimeout(overlayShowTimer);
        overlayShowTimer = null;
      }

      const ov = document.getElementById('ev-nav-overlay');
      if (ov) ov.style.display = 'none';

      if (overlayWatchdog) {
        clearTimeout(overlayWatchdog);
        overlayWatchdog = null;
      }
    }
  }

  function killLegacyLoaders() {
    const legacySelector = [
      '#spinner-overlay', '#loading-overlay', '#loader-overlay', '#global-loader', '#ev-loading',
      '.spinner-overlay', '.loading-overlay', '.loader-overlay', '.global-loader', '.preloader',
      '#preloader', '.page-loader', '#page-loader', '.overlay-loading', '#overlay-loading',
      '.ajax-loading', '#ajax-loading'
    ].join(',');

    document.querySelectorAll(legacySelector).forEach((el) => {
      if (el.id === 'ev-nav-overlay') return;
      el.style.display = 'none';
      el.classList.add('d-none');
      el.classList.remove('show');
    });

    document.body.classList.remove('loading', 'is-loading');
    document.documentElement.classList.remove('loading', 'is-loading');
  }

  async function alertAndRedirectBlocked(payload) {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;
    window.__EV_AUTH_REDIRECTING__ = true;

    const mensaje = (payload && (payload.mensaje || payload.error))
      ? (payload.mensaje || payload.error)
      : 'Tu cuenta fue bloqueada. Se cerró tu sesión por seguridad.';

    const redirect = (payload && payload.redirect) ? payload.redirect : `${BASE}/login`;

    try {
      hideEvOverlay(true);
      killLegacyLoaders();
    } catch (_) {}

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

  async function alertAndRedirectUnauthorized(payload) {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;
    window.__EV_AUTH_REDIRECTING__ = true;

    const mensaje = (payload && (payload.mensaje || payload.error))
      ? (payload.mensaje || payload.error)
      : 'Tu sesión ha finalizado. Vuelve a iniciar sesión.';

    const redirect = (payload && payload.redirect) ? payload.redirect : `${BASE}/login`;

    try {
      hideEvOverlay(true);
      killLegacyLoaders();
    } catch (_) {}

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

  function normalizeInternalPath(input) {
    if (!input) return '/';

    let path = String(input).trim();

    try {
      if (/^https?:\/\//i.test(path)) {
        const u = new URL(path);
        path = u.pathname + (u.search || '');
      }
    } catch (_) {}

    if (!path.startsWith('/')) {
      path = '/' + path;
    }

    const basePath = BASE.replace(/^https?:\/\/[^/]+/i, '');

    if (basePath && basePath !== '/' && path.startsWith(basePath + '/')) {
      path = path.slice(basePath.length);
      if (!path.startsWith('/')) path = '/' + path;
    }

    path = path.replace(/\/{2,}/g, '/');
    path = path.split('?')[0];
    path = path.replace(/\/+$/, '') || '/';

    return path;
  }

  function resolvePathFromAnchor(a) {
    if (!a) return null;

    const dataVista = a.getAttribute('data-vista') || a.dataset.vista || '';
    const dataRuta = a.getAttribute('data-ruta') || a.dataset.ruta || '';
    const href = a.getAttribute('href') || '';

    const raw = dataVista || dataRuta || href;

    if (!raw || raw === '#' || raw.startsWith('#menu')) {
      return null;
    }

    return normalizeInternalPath(raw);
  }

  function samePath(a, b) {
    let pa = normalizeInternalPath(a || '/');
    let pb = normalizeInternalPath(b || '/');

    pa = pa.replace(/\/+$/, '') || '/';
    pb = pb.replace(/\/+$/, '') || '/';

    if (pa === pb) return true;

    return (
      (pa === '/MenuPrincipal' && pb === '/') ||
      (pa === '/' && pb === '/MenuPrincipal')
    );
  }

  function isShellHomePath(path) {
    return samePath(path, '/MenuPrincipal') || samePath(path, '/');
  }

  function currentUrlHasEvGoto() {
    try {
      const qs = new URLSearchParams(window.location.search);
      return qs.has('ev_goto');
    } catch (_) {
      return false;
    }
  }

  function getCurrentSidebarPath() {
    try {
      const qs = new URLSearchParams(window.location.search);
      const goto = qs.get('ev_goto');

      if (goto) return normalizeInternalPath(goto);

      const path = normalizeInternalPath(window.location.pathname);

      if (path === '/' || path === '/login') return '/MenuPrincipal';

      return path;
    } catch (_) {
      return '/MenuPrincipal';
    }
  }

  /* ============================================================
     ACORDEÓN EV PERSONALIZADO
     No usa Bootstrap Collapse, por eso no se siente pesado.
  ============================================================ */

  function cssEscape(value) {
    const txt = String(value || '');
    if (window.CSS && typeof window.CSS.escape === 'function') {
      return window.CSS.escape(txt);
    }
    return txt.replace(/[^a-zA-Z0-9_-]/g, '\\$&');
  }

  function getGroupByParent(parent) {
    if (!sidebar || !parent) return null;
    const id = parent.getAttribute('data-menu-target') || parent.getAttribute('aria-controls') || '';
    if (!id) return null;
    return sidebar.querySelector(`#${cssEscape(id)}`);
  }

  function getParentByGroup(group) {
    if (!sidebar || !group || !group.id) return null;
    return sidebar.querySelector(`.menu-parent-link[aria-controls="${cssEscape(group.id)}"], .menu-parent-link[data-menu-target="${cssEscape(group.id)}"]`);
  }

  function setGroupOpen(group, open) {
    if (!group) return;

    const parent = getParentByGroup(group);

    group.classList.toggle('is-open', open);
    group.setAttribute('aria-hidden', open ? 'false' : 'true');

    if (parent) {
      parent.classList.toggle('is-open', open);
      parent.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
  }

  function toggleMenuParent(parent) {
    const group = getGroupByParent(parent);
    if (!group) return;

    const willOpen = !group.classList.contains('is-open');
    setGroupOpen(group, willOpen);
  }

  function openMenuGroupForLink(link) {
    if (!sidebar || !link) return;

    const group = link.closest('.ev-menu-group, .nav-treeview');
    if (!group) return;

    setGroupOpen(group, true);

    const parent = getParentByGroup(group);
    if (parent) parent.classList.add('active-parent');
  }

  function clearSidebarActiveState() {
    if (!sidebar) return;

    sidebar.querySelectorAll('.submenu-link').forEach((link) => {
      link.classList.remove('submenu-active', 'active');
      link.removeAttribute('aria-current');
    });

    sidebar.querySelectorAll('.menu-parent-link').forEach((link) => {
      link.classList.remove('active-parent', 'active-menu');
    });
  }

  function setActiveSidebarByPath(path) {
    if (!sidebar) return;

    const cleanPath = normalizeInternalPath(path || getCurrentSidebarPath());
    const links = Array.from(sidebar.querySelectorAll('.submenu-link[data-vista]'));

    let activeLink = null;

    for (const link of links) {
      const linkPath = resolvePathFromAnchor(link);
      if (samePath(linkPath, cleanPath)) {
        activeLink = link;
        break;
      }
    }

    if (!activeLink) {
      clearSidebarActiveState();
      return;
    }

    clearSidebarActiveState();

    activeLink.classList.add('submenu-active', 'active');
    activeLink.setAttribute('aria-current', 'page');

    openMenuGroupForLink(activeLink);
  }

  function goToShellHome(options = {}) {
    const forceReload = options.forceReload === true;

    setActiveSidebarByPath('/MenuPrincipal');
    closeSidebarMobile();

    try {
      hideEvOverlay(true);
      killLegacyLoaders();
    } catch (_) {}

    const currentPath = normalizeInternalPath(window.location.pathname);
    const alreadyCleanHome = isShellHomePath(currentPath) && !currentUrlHasEvGoto();

    if (alreadyCleanHome) {
      if (forceReload) window.location.reload();
      return;
    }

    window.location.assign(SHELL_URL);
  }

  function buildModuleUrl(path) {
    const clean = normalizeInternalPath(path);
    return `${BASE}${clean}`;
  }

  function buildShellUrl(path) {
    const clean = normalizeInternalPath(path);

    if (isShellHomePath(clean)) return SHELL_URL;

    return `${SHELL_URL}?ev_goto=${encodeURIComponent(clean)}`;
  }

  function addPartial(url) {
    const u = new URL(url, window.location.origin);

    if (!u.searchParams.has('partial')) {
      u.searchParams.set('partial', '1');
    }

    return u.pathname + '?' + u.searchParams.toString();
  }

  const LOADED = new Set(
    Array.from(document.scripts)
      .map(s => (s.src || '').trim())
      .filter(Boolean)
      .map(src => new URL(src, window.location.origin).href)
  );

  function runInline(code) {
    if (!code) return;
    try {
      new Function(code)();
    } catch (e) {
      console.error('[EV][NAV] Error en script inline:', e);
    }
  }

  function loadScriptWithTimeout(src, { signal, timeoutMs = 8000 } = {}) {
    return new Promise((resolve) => {
      if (!src) return resolve(false);

      const abs = new URL(src, window.location.origin).href;

      if (LOADED.has(abs)) return resolve(true);

      const s = document.createElement('script');
      s.src = abs;
      s.defer = true;

      let doneCalled = false;

      const done = (ok) => {
        if (doneCalled) return;
        doneCalled = true;

        try { s.onload = null; s.onerror = null; } catch (_) {}
        try { if (s.parentNode) s.parentNode.removeChild(s); } catch (_) {}

        if (ok) LOADED.add(abs);
        resolve(ok);
      };

      const t = setTimeout(() => {
        console.warn('[EV][NAV] Timeout cargando script:', abs);
        done(false);
      }, timeoutMs);

      s.onload = () => {
        clearTimeout(t);
        done(true);
      };

      s.onerror = () => {
        clearTimeout(t);
        done(false);
      };

      if (signal) {
        signal.addEventListener('abort', () => {
          clearTimeout(t);
          done(false);
        }, { once: true });
      }

      document.body.appendChild(s);
    });
  }

  async function processScripts(root, signal) {
    const scripts = Array.from(root.querySelectorAll('script'));
    if (!scripts.length) return;

    scripts.forEach(s => s.parentNode && s.parentNode.removeChild(s));

    const inline = scripts.filter(s => !s.src);
    const external = scripts.filter(s => !!s.src);

    inline.forEach(s => runInline(s.textContent || ''));

    for (const s of external) {
      await loadScriptWithTimeout(s.src, { signal, timeoutMs: 8000 });
    }
  }

  async function fetchWithTimeout(url, { timeoutMs = 15000, ...opts } = {}) {
    const ctrl = new AbortController();
    const id = setTimeout(() => ctrl.abort(), timeoutMs);

    try {
      const res = await fetch(url, { ...opts, signal: ctrl.signal });
      return { res, signal: ctrl.signal };
    } finally {
      clearTimeout(id);
    }
  }

  function initLoadedModules(path) {
    try {
      if (window.EVMarketplace && typeof window.EVMarketplace.init === 'function') {
        window.EVMarketplace.init();
      }

      if (window.EVMarketplace && typeof window.EVMarketplace.restoreSolicitudActiva === 'function') {
        window.EVMarketplace.restoreSolicitudActiva();
      }

      if (window.EVMisPedidosComprador && typeof window.EVMisPedidosComprador.init === 'function') {
        window.EVMisPedidosComprador.init();
      }

      if (window.EVMisPedidosVendedor && typeof window.EVMisPedidosVendedor.init === 'function') {
        window.EVMisPedidosVendedor.init();
      }

      if (typeof window.initRecibirPedidos === 'function') {
        window.initRecibirPedidos();
      }

      if (typeof window.initPedidosEntrantes === 'function') {
        window.initPedidosEntrantes();
      }

      document.dispatchEvent(new CustomEvent('ev:module-initialized', {
        detail: { path }
      }));
    } catch (e) {
      console.error('[EV][NAV] Error inicializando módulos cargados:', e);
    }
  }

  let currentLoadId = 0;

  async function loadPage(path, { pushState = true, replaceState = false } = {}) {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;

    const cleanPath = normalizeInternalPath(path);

    if (isShellHomePath(cleanPath)) {
      goToShellHome();
      return;
    }

    const moduleUrl = buildModuleUrl(cleanPath);
    const finalUrl = addPartial(moduleUrl);

    setActiveSidebarByPath(cleanPath);

    const myId = ++currentLoadId;

    killLegacyLoaders();
    showEvOverlay();

    const localWatchdog = setTimeout(() => {
      if (myId === currentLoadId) {
        console.warn('[EV][NAV] Watchdog: forzando hide overlay por cuelgue.');
        hideEvOverlay(true);
        killLegacyLoaders();
      }
    }, 22000);

    try {
      const { res, signal } = await fetchWithTimeout(finalUrl, {
        timeoutMs: 15000,
        method: 'GET',
        cache: 'no-cache',
        credentials: 'include',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-Partial': '1',
          'Accept': 'text/html,application/json'
        }
      });

      if (myId !== currentLoadId) return;

      const ct = (res.headers.get('content-type') || '').toLowerCase();
      const text = await res.text().catch(() => '');

      if (ct.includes('application/json')) {
        let payload = null;
        try { payload = text ? JSON.parse(text) : null; } catch (_) { payload = null; }

        if (res.status === 403 && payload && payload.error === 'CUENTA_BLOQUEADA') {
          await alertAndRedirectBlocked(payload);
          return;
        }

        if (res.status === 401 || (payload && payload.error === 'UNAUTHORIZED')) {
          await alertAndRedirectUnauthorized(payload);
          return;
        }

        if (payload && payload.error === 'CUENTA_OBSERVADA' && payload.redirect) {
          if (window.__EV_AUTH_REDIRECTING__ === true) return;
          window.__EV_AUTH_REDIRECTING__ = true;
          window.location.href = payload.redirect;
          return;
        }

        main.innerHTML = `
          <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <div class="fw-bold mb-1">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>Error
            </div>
            <div>La vista devolvió JSON en lugar de HTML.</div>
            <div class="small text-muted mt-2">${finalUrl}</div>
          </div>
        `;
        return;
      }

      if (!res.ok) {
        if (res.status === 403) {
          let payload403 = null;
          try { payload403 = text ? JSON.parse(text) : null; } catch (_) { payload403 = null; }
          if (payload403 && payload403.error === 'CUENTA_BLOQUEADA') {
            await alertAndRedirectBlocked(payload403);
            return;
          }
        }

        if (res.status === 401) {
          let payload401 = null;
          try { payload401 = text ? JSON.parse(text) : null; } catch (_) { payload401 = null; }
          await alertAndRedirectUnauthorized(payload401);
          return;
        }

        main.innerHTML = `
          <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <div class="fw-bold mb-1">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>Error
            </div>
            <div>No se pudo cargar el contenido solicitado.</div>
            <div class="small text-muted mt-2">HTTP ${res.status}</div>
          </div>
        `;
        return;
      }

      if (window.__EV_AUTH_REDIRECTING__ === true) return;

      main.innerHTML = text;

      await processScripts(main, signal);
      initLoadedModules(cleanPath);

      document.dispatchEvent(new CustomEvent('ev:content-loaded', {
        detail: { url: moduleUrl, path: cleanPath }
      }));

      setActiveSidebarByPath(cleanPath);

      const targetShellUrl = buildShellUrl(cleanPath);

      if (replaceState) {
        history.replaceState({ ev_goto: cleanPath }, '', targetShellUrl);
      } else if (pushState) {
        history.pushState({ ev_goto: cleanPath }, '', targetShellUrl);
      }

    } catch (e) {
      if (window.__EV_AUTH_REDIRECTING__ === true) return;

      const isAbort = String(e && e.name).toLowerCase().includes('abort');

      main.innerHTML = `
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
          <div class="fw-bold mb-1">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Error
          </div>
          <div>${isAbort ? 'La carga tardó demasiado y se canceló (timeout).' : 'No se pudo cargar el contenido solicitado.'}</div>
          <div class="small text-muted mt-2">${String(e?.message || e)}</div>
        </div>
      `;

      console.error('[EV][NAV] Error:', e);

    } finally {
      clearTimeout(localWatchdog);
      hideEvOverlay(true);
      killLegacyLoaders();
      closeSidebarMobile();
    }
  }

  document.addEventListener('click', async (e) => {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;

    const parent = e.target.closest('#sidebar .menu-parent-link');
    if (parent) {
      e.preventDefault();
      e.stopPropagation();
      toggleMenuParent(parent);
      return;
    }

    const logoutButton = e.target.closest('[data-ev-logout], .ev-sidebar-footer-link-logout');
    if (logoutButton) {
      e.preventDefault();
      e.stopPropagation();

      if (logoutButton.dataset.evLogoutBusy === '1') return;

      const confirmar = window.Swal?.fire
        ? await Swal.fire({
            icon: 'question',
            title: '¿Deseas cerrar sesión?',
            text: 'Tu sesión se cerrará de forma segura y tendrás que ingresar nuevamente para volver a Entre Vecinos.',
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#EA7C12',
            cancelButtonColor: '#6B7280',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCloseButton: false,
            customClass: { closeButton: 'ev-swal-close' },
            reverseButtons: true
          })
        : { isConfirmed: window.confirm('¿Deseas cerrar sesión?') };

      if (!confirmar?.isConfirmed) return;

      logoutButton.dataset.evLogoutBusy = '1';
      logoutButton.disabled = true;
      try {
        const url = logoutButton.dataset.logoutUrl || `${BASE}/logout`;
        const response = await fetch(url, {
          method: 'POST',
          credentials: 'include',
          cache: 'no-store',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data?.ok === false) {
          throw new Error(data?.mensaje || data?.message || 'No se pudo cerrar sesión.');
        }
        window.location.replace(data?.redirect || `${BASE}/login`);
      } catch (error) {
        logoutButton.dataset.evLogoutBusy = '0';
        logoutButton.disabled = false;
        if (window.Swal?.fire) {
          await Swal.fire({
            icon: 'error',
            title: 'No se pudo cerrar sesión',
            text: error?.message || 'Inténtalo nuevamente.',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#EA7C12',
            allowOutsideClick: false,
            allowEscapeKey: false
          });
        } else {
          window.alert(error?.message || 'No se pudo cerrar sesión.');
        }
      }
      return;
    }

    const ayuda = e.target.closest('#btnEvAyudaSidebar');
    if (ayuda) {
      e.preventDefault();
      if (window.Swal?.fire) {
        Swal.fire({
          title: 'Ayuda EV',
          html: `
            <div class="ev-help-modal-icon" aria-hidden="true"><i class="bi bi-headset"></i></div>
            <div class="ev-help-modal-copy">
              <strong>Estamos para ayudarte</strong>
              <p>Escríbenos por WhatsApp y cuéntanos brevemente qué necesitas. El equipo de Soporte EV te orientará.</p>
              <a class="ev-help-modal-contact" href="https://wa.me/51956969182" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp"></i><span>956 969 182</span></a>
            </div>
          `,
          confirmButtonText: 'Aceptar',
          confirmButtonColor: '#EA7C12',
          showConfirmButton: true,
          showCancelButton: false,
          showDenyButton: false,
          showCloseButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false,
          customClass: { popup: 'ev-help-modal-popup', title: 'ev-help-modal-title', htmlContainer: 'ev-help-modal-html', confirmButton: 'ev-help-modal-confirm', closeButton: 'ev-swal-close' }
        });
      } else {
        alert('Ayuda EV\n\nEscríbenos por WhatsApp al 956 969 182 y cuéntanos brevemente qué necesitas. Soporte EV te orientará.');
      }
      return;
    }

    const a = e.target.closest('a');
    if (!a) return;

    const inSidebar = a.closest('#sidebar') || a.closest('.main-sidebar') || a.closest('.ev-sidebar');
    if (!inSidebar) return;

    const path = resolvePathFromAnchor(a);
    if (!path) return;

    e.preventDefault();

    if (isShellHomePath(path)) {
      goToShellHome();
      return;
    }

    setActiveSidebarByPath(path);

    loadPage(path, {
      pushState: true,
      replaceState: false
    });
  }, true);

  window.addEventListener('popstate', () => {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;

    const p = new URLSearchParams(window.location.search);
    const goto = p.get('ev_goto');

    if (!goto) {
      goToShellHome({ forceReload: true });
      return;
    }

    if (isShellHomePath(goto)) {
      goToShellHome();
      return;
    }

    setActiveSidebarByPath(goto);

    loadPage(goto, {
      pushState: false,
      replaceState: true
    });
  });

  // Inicializa estado activo, abre el grupo del item activo y sincroniza
  // la comunidad vigente desde BD (el JWT puede contener una residencia anterior).
  setActiveSidebarByPath(getCurrentSidebarPath());
  refreshSidebarCommunity({ silent: true, force: true });

  try {
    const qs = new URLSearchParams(window.location.search);
    const goto = qs.get('ev_goto');

    if (goto) {
      if (isShellHomePath(goto)) {
        goToShellHome();
        return;
      }

      setActiveSidebarByPath(goto);

      loadPage(goto, {
        pushState: false,
        replaceState: true
      });
    }
  } catch (e) {
    console.warn('[EV][NAV] ev_goto no procesado:', e);
  }

  document.addEventListener('ev:content-loaded', () => {
    refreshSidebarCommunity({ silent: true });
  });

  document.addEventListener('ev:sidebar-community-refresh', () => {
    refreshSidebarCommunity({ silent: true, force: true });
  });

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) refreshSidebarCommunity({ silent: true });
  });

  window.addEventListener('pageshow', () => {
    refreshSidebarCommunity({ silent: true, force: true });
  });

  window.EVSidebarCommunity = {
    refresh: refreshSidebarCommunity
  };

  window.EVNav = {
    loadPage,
    setActiveSidebarByPath,
    goToShellHome
  };
});
