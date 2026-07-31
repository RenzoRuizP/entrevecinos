// views/js/evModalPolicy.js
// Política global EV: los modales solo se cierran mediante controles explícitos.
(function () {
  'use strict';

  if (window.__EV_MODAL_POLICY_LOADED__ === true) return;
  window.__EV_MODAL_POLICY_LOADED__ = true;

  const MODAL_SELECTOR = '.modal';

  function hardenModal(modal) {
    if (!(modal instanceof HTMLElement) || !modal.matches(MODAL_SELECTOR)) return;

    modal.setAttribute('data-bs-backdrop', 'static');
    modal.setAttribute('data-bs-keyboard', 'false');
    modal.dataset.evExplicitCloseOnly = '1';

    try {
      const instance = window.bootstrap?.Modal?.getInstance(modal);
      if (instance && instance._config) {
        instance._config.backdrop = 'static';
        instance._config.keyboard = false;
      }
    } catch (_) {}
  }

  function scan(rootNode = document) {
    if (rootNode instanceof HTMLElement && rootNode.matches(MODAL_SELECTOR)) {
      hardenModal(rootNode);
    }

    rootNode.querySelectorAll?.(MODAL_SELECTOR).forEach(hardenModal);
  }

  // Se ejecuta antes de que Bootstrap complete la apertura.
  document.addEventListener('show.bs.modal', (event) => {
    hardenModal(event.target);
  }, true);

  document.addEventListener('shown.bs.modal', (event) => {
    hardenModal(event.target);
  }, true);

  // Bloquea Escape cuando existe un modal Bootstrap visible.
  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (!document.querySelector('.modal.show')) return;

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
  }, true);

  // Evita el cierre accidental al pulsar el fondo del modal.
  ['mousedown', 'click'].forEach((eventName) => {
    document.addEventListener(eventName, (event) => {
      const modal = event.target instanceof HTMLElement && event.target.matches('.modal.show')
        ? event.target
        : null;

      if (!modal) return;

      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();
    }, true);
  });

  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node instanceof HTMLElement) scan(node);
      });
    });
  });

  function init() {
    scan(document);
    observer.observe(document.documentElement, { childList: true, subtree: true });
  }

  document.addEventListener('ev:content-loaded', () => scan(document));
  document.addEventListener('ev:partial-loaded', () => scan(document));
  document.addEventListener('ev:nav-end', () => scan(document));

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
