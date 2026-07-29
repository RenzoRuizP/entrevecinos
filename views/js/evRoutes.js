// views/js/evRoutes.js
// Construcción centralizada de URLs para Local, QA y Producción.
(() => {
  'use strict';

  function normalizeBase(value) {
    let raw = String(value ?? '').trim();

    if (!raw || raw === '/') return '';

    try {
      if (/^https?:\/\//i.test(raw)) {
        raw = new URL(raw).pathname || '';
      }
    } catch (_) {}

    raw = raw.replace(/\\/g, '/').replace(/\/+$/g, '');
    if (raw && !raw.startsWith('/')) raw = `/${raw}`;
    return raw === '/' ? '' : raw;
  }

  const configuredBase = window.EV_CONFIG?.baseUrl
    ?? window.BASE_URL
    ?? window.EV_BASE_URL
    ?? '';

  const baseUrl = normalizeBase(configuredBase);

  function url(path = '') {
    const raw = String(path ?? '').trim();

    if (!raw) return baseUrl || '/';
    if (/^(?:[a-z][a-z0-9+.-]*:|\/\/|#)/i.test(raw)) return raw;

    const cleanPath = raw.replace(/^\/+/, '');
    return `${baseUrl}/${cleanPath}`;
  }

  window.EV = Object.assign(window.EV || {}, {
    baseUrl,
    url,
    normalizeBase
  });

  window.evUrl = url;
})();
