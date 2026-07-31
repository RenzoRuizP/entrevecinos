// views/js/evModalPolicy.js
// Política global EV: los modales solo se cierran mediante controles explícitos.
(function () {
  'use strict';

  if (window.__EV_MODAL_POLICY_LOADED__ === true) return;
  window.__EV_MODAL_POLICY_LOADED__ = true;

  const MODAL_SELECTOR = '.modal';
  const CLOSE_STYLE_ID = 'ev-modal-close-standard-style';

  function injectCloseStandard() {
    if (document.getElementById(CLOSE_STYLE_ID)) return;

    const style = document.createElement('style');
    style.id = CLOSE_STYLE_ID;
    style.textContent = `
      .modal .modal-header .btn-close,
      .ev-modal-header .btn-close,
      .ev-login-modal-header .btn-close,
      .ev-mp-modal-header .btn-close,
      .ev-ap-modal-header .btn-close,
      .ev-avatar-modal-close,
      .ev-com-history-close,
      .ev-com-modal-close,
      .ev-config-editor-close,
      .ev-cv-modal-close,
      .ev-sc-close,
      .ev-so-close{
        width:38px !important;
        height:38px !important;
        min-width:38px !important;
        min-height:38px !important;
        flex:0 0 38px !important;
        display:grid !important;
        place-items:center !important;
        margin:0 !important;
        padding:0 !important;
        border:0 !important;
        border-radius:10px !important;
        color:#fff !important;
        background-color:transparent !important;
        background-position:center !important;
        background-repeat:no-repeat !important;
        background-size:14px 14px !important;
        box-shadow:none !important;
        opacity:1 !important;
        filter:none !important;
        transform:none !important;
        transition:background-color .15s ease, opacity .15s ease !important;
      }
      .modal .modal-header .btn-close,
      .ev-modal-header .btn-close,
      .ev-login-modal-header .btn-close,
      .ev-mp-modal-header .btn-close,
      .ev-ap-modal-header .btn-close{
        filter:invert(1) grayscale(1) brightness(2) !important;
      }
      .modal .modal-header .btn-close:hover,
      .modal .modal-header .btn-close:focus-visible,
      .ev-modal-header .btn-close:hover,
      .ev-modal-header .btn-close:focus-visible,
      .ev-login-modal-header .btn-close:hover,
      .ev-login-modal-header .btn-close:focus-visible,
      .ev-mp-modal-header .btn-close:hover,
      .ev-mp-modal-header .btn-close:focus-visible,
      .ev-ap-modal-header .btn-close:hover,
      .ev-ap-modal-header .btn-close:focus-visible,
      .ev-avatar-modal-close:hover,
      .ev-avatar-modal-close:focus-visible,
      .ev-com-history-close:hover,
      .ev-com-history-close:focus-visible,
      .ev-com-modal-close:hover,
      .ev-com-modal-close:focus-visible,
      .ev-config-editor-close:hover,
      .ev-config-editor-close:focus-visible,
      .ev-cv-modal-close:hover,
      .ev-cv-modal-close:focus-visible,
      .ev-sc-close:hover,
      .ev-sc-close:focus-visible,
      .ev-so-close:hover,
      .ev-so-close:focus-visible{
        background-color:rgba(255,255,255,.12) !important;
        box-shadow:none !important;
        opacity:.88 !important;
        transform:none !important;
        outline:0 !important;
      }
    `;
    document.head.appendChild(style);
  }

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
    injectCloseStandard();
    scan(document);
    observer.observe(document.documentElement, { childList: true, subtree: true });
  }

  document.addEventListener('ev:content-loaded', () => { injectCloseStandard(); scan(document); });
  document.addEventListener('ev:partial-loaded', () => scan(document));
  document.addEventListener('ev:nav-end', () => scan(document));

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
