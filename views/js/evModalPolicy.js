// views/js/evModalPolicy.js
// Política global EV: cierre explícito, interacción homogénea y bajo costo de ejecución.
(function () {
  'use strict';

  if (window.__EV_MODAL_POLICY_LOADED__ === true) return;
  window.__EV_MODAL_POLICY_LOADED__ = true;

  const MODAL_SELECTOR = '.modal';
  const CLOSE_STYLE_ID = 'ev-modal-close-standard-style';

  function injectCloseStandard(forceLast = false) {
    const current = document.getElementById(CLOSE_STYLE_ID);
    if (current) {
      if (forceLast && document.body && current.parentNode !== document.body) {
        document.body.appendChild(current);
      } else if (forceLast && document.body) {
        document.body.appendChild(current);
      }
      return;
    }

    const style = document.createElement('style');
    style.id = CLOSE_STYLE_ID;
    style.textContent = `
      .modal .modal-header .btn-close,
      .ev-modal-header .btn-close,
      .ev-login-modal-header .btn-close,
      .ev-mp-modal-header .btn-close,
      .ev-ap-modal-header .btn-close,
      .ev-modal-close-icon,
      .ev-modal-close,
      .ev-register-modal__close,
      .ev-dg-modal-close,
      .ev-avatar-modal-close,
      .ev-com-history-close,
      .ev-com-modal-close,
      .ev-config-editor-close,
      .ev-cv-modal-close,
      .ev-sc-close,
      .ev-so-close{
        appearance:none !important;
        -webkit-appearance:none !important;
        box-sizing:border-box !important;
        width:38px !important;
        height:38px !important;
        min-width:38px !important;
        min-height:38px !important;
        flex:0 0 38px !important;
        display:inline-grid !important;
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
        transform:none !important;
        cursor:pointer !important;
        -webkit-tap-highlight-color:transparent !important;
        transition:background-color .13s ease, opacity .13s ease !important;
      }

      .modal .modal-header .btn-close,
      .ev-modal-header .btn-close,
      .ev-login-modal-header .btn-close,
      .ev-mp-modal-header .btn-close,
      .ev-ap-modal-header .btn-close,
      .ev-modal-close-icon,
      .ev-sc-close{
        filter:invert(1) grayscale(1) brightness(2) !important;
      }

      .ev-register-modal__close i,
      .ev-dg-modal-close i,
      .ev-avatar-modal-close i,
      .ev-com-history-close i,
      .ev-com-modal-close i,
      .ev-config-editor-close i,
      .ev-cv-modal-close i,
      .ev-so-close i{
        color:#fff !important;
        line-height:1 !important;
        pointer-events:none !important;
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
      .ev-modal-close-icon:hover,
      .ev-modal-close-icon:focus-visible,
      .ev-modal-close:hover,
      .ev-modal-close:focus-visible,
      .ev-register-modal__close:hover,
      .ev-register-modal__close:focus-visible,
      .ev-dg-modal-close:hover,
      .ev-dg-modal-close:focus-visible,
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
        background-color:rgba(15,89,47,.44) !important;
        border:0 !important;
        box-shadow:none !important;
        opacity:1 !important;
        transform:none !important;
        outline:0 !important;
      }

      .modal .modal-header .btn-close:active,
      .ev-modal-close-icon:active,
      .ev-modal-close:active,
      .ev-register-modal__close:active,
      .ev-dg-modal-close:active,
      .ev-avatar-modal-close:active,
      .ev-com-history-close:active,
      .ev-com-modal-close:active,
      .ev-config-editor-close:active,
      .ev-cv-modal-close:active,
      .ev-sc-close:active,
      .ev-so-close:active{
        background-color:rgba(15,89,47,.60) !important;
      }

      @media (max-width:575.98px){
        .modal .modal-header .btn-close,
        .ev-modal-close-icon,
        .ev-modal-close,
        .ev-register-modal__close,
        .ev-dg-modal-close,
        .ev-avatar-modal-close,
        .ev-com-history-close,
        .ev-com-modal-close,
        .ev-config-editor-close,
        .ev-cv-modal-close,
        .ev-sc-close,
        .ev-so-close{
          width:36px !important;
          height:36px !important;
          min-width:36px !important;
          min-height:36px !important;
          flex-basis:36px !important;
        }
      }
    `;
    (document.head || document.documentElement).appendChild(style);
  }

  function hardenModal(modal) {
    if (!(modal instanceof HTMLElement) || !modal.matches(MODAL_SELECTOR)) return;
    if (modal.dataset.evModalPolicyApplied !== '1') {
      modal.setAttribute('data-bs-backdrop', 'static');
      modal.setAttribute('data-bs-keyboard', 'false');
      modal.dataset.evExplicitCloseOnly = '1';
      modal.dataset.evModalPolicyApplied = '1';
    }

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

  document.addEventListener('show.bs.modal', (event) => {
    injectCloseStandard();
    hardenModal(event.target);
  }, true);

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !document.querySelector('.modal.show')) return;
    event.preventDefault();
    event.stopPropagation();
  }, true);

  function scanDynamicContent() {
    injectCloseStandard(true);
    scan(document.getElementById('contenido-principal') || document);
  }

  document.addEventListener('ev:content-loaded', scanDynamicContent);
  document.addEventListener('ev:partial-loaded', scanDynamicContent);

  function init() {
    injectCloseStandard();
    scan(document);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
