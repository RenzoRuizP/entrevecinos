// views/js/evSweetAlert.js
// EV SweetAlert global: estándar visual único, blanco limpio y botones premium EV.
(function () {
  'use strict';

  if (window.__EV_SWEET_ALERT_STANDARD__ === true) return;
  window.__EV_SWEET_ALERT_STANDARD__ = true;

  // Evita que menuPrincipal.js reactive el rebote lateral anterior.
  window.__EV_SWAL_GLOBAL_FIX__ = true;

  const STYLE_ID = 'ev-sweetalert-standard-style';

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function classJoin() {
    return Array.from(arguments)
      .flatMap((v) => String(v || '').split(/\s+/))
      .map((v) => v.trim())
      .filter(Boolean)
      .filter((v, i, arr) => arr.indexOf(v) === i)
      .join(' ');
  }

  function plainButtonLabel(value) {
    const html = String(value ?? '');
    if (!html) return '';

    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return String(tmp.textContent || tmp.innerText || '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function aplicarEstandarAceptarCancelar(config) {
    const confirmLabel = plainButtonLabel(config.confirmButtonText).toLocaleLowerCase('es');
    const cancelLabel = plainButtonLabel(config.cancelButtonText).toLocaleLowerCase('es');

    // Estándar EV: los botones Aceptar / Cancelar nunca llevan iconos.
    if (confirmLabel === 'aceptar') config.confirmButtonText = 'Aceptar';
    if (cancelLabel === 'cancelar') config.cancelButtonText = 'Cancelar';

    // Cuando ambas acciones conviven en el mismo modal, Cancelar va primero
    // y Aceptar queda como acción primaria a la derecha.
    if (config.showCancelButton === true && confirmLabel === 'aceptar' && cancelLabel === 'cancelar') {
      config.reverseButtons = true;
    }

    return config;
  }

  function injectStyles() {
    if (document.getElementById(STYLE_ID)) return;

    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.type = 'text/css';
    style.textContent = `
      :root{
        --ev-swal-green:#0F592F;
        --ev-swal-green-2:#16A34A;
        --ev-swal-orange:#EA7C12;
        --ev-swal-orange-2:#F59E0B;
        --ev-swal-text:#111827;
        --ev-swal-muted:#6B7280;
        --ev-swal-border:#E5E7EB;
        --ev-swal-red:#DC2626;
        --ev-swal-blue:#38BDF8;
      }

      .swal2-container{
        z-index:30000 !important;
      }

      .swal2-container.ev-swal-container,
      .swal2-container.ev-mp-swal-container,
      .swal2-container.ev-mpv-swal-container,
      .swal2-container.ev-mpc-swal-container,
      .swal2-container.ev-rp-swal-container{
        backdrop-filter: blur(2.5px);
      }

      .swal2-popup.ev-swal-popup,
      .swal2-popup.ev-mp-swal-popup,
      .swal2-popup.ev-mp-swal-popup-seguimiento,
      .swal2-popup.ev-mpv-swal-popup-premium,
      .swal2-popup.ev-mpc-swal-popup,
      .swal2-popup.ev-mpc-swal-popup-premium,
      .swal2-popup.ev-wallet-swal-popup{
        width: min(92vw, 560px) !important;
        border-radius: 28px !important;
        padding: 28px 24px 22px !important;
        border: 1px solid rgba(229,231,235,.96) !important;
        background: #ffffff !important;
        background-image: none !important;
        overflow: hidden !important;
        box-shadow: 0 30px 72px rgba(15,23,42,.20), 0 10px 24px rgba(15,23,42,.08) !important;
      }

      .swal2-popup.ev-swal-popup-detail,
      .swal2-popup.ev-mpc-swal-popup-detail,
      .swal2-popup.ev-mpv-swal-popup-detail{
        width: min(94vw, 860px) !important;
        max-width: 860px !important;
      }

      /*
       * Política global EV: ningún SweetAlert muestra botón de cierre (X).
       * SweetAlert2 mantiene el elemento .swal2-close en el DOM aun cuando
       * showCloseButton=false. Por eso la regla debe ocultarlo también por CSS;
       * nunca se debe forzar su display desde el tema visual.
       */
      .swal2-popup .swal2-close,
      .swal2-popup .swal2-close.ev-swal-close{
        display:none !important;
        visibility:hidden !important;
        opacity:0 !important;
        pointer-events:none !important;
      }

      .swal2-popup.ev-swal-popup::before,
      .swal2-popup.ev-mp-swal-popup::before,
      .swal2-popup.ev-mp-swal-popup-seguimiento::before,
      .swal2-popup.ev-mpv-swal-popup-premium::before,
      .swal2-popup.ev-mpc-swal-popup::before,
      .swal2-popup.ev-mpc-swal-popup-premium::before,
      .swal2-popup.ev-wallet-swal-popup::before{
        content:'';
        position:absolute;
        inset:0 0 auto 0;
        height:5px;
        background:linear-gradient(90deg, var(--ev-swal-green) 0%, var(--ev-swal-green-2) 58%, var(--ev-swal-orange) 100%);
      }

      .swal2-title.ev-swal-title,
      .swal2-title.ev-mp-swal-title,
      .swal2-title.ev-mpv-swal-title,
      .swal2-title.ev-mpc-swal-title{
        color: var(--ev-swal-green) !important;
        font-weight: 900 !important;
        letter-spacing: -.03em !important;
        font-size: clamp(1.72rem, 2.6vw, 2.18rem) !important;
        line-height: 1.08 !important;
        margin: 0 0 8px 0 !important;
        padding: 0 !important;
      }

      .swal2-html-container.ev-swal-html,
      .swal2-html-container.ev-mp-swal-html,
      .swal2-html-container.ev-mpv-swal-html,
      .swal2-html-container.ev-mpc-swal-html{
        color: var(--ev-swal-muted) !important;
        font-size: .98rem !important;
        line-height: 1.6 !important;
        margin: 0 !important;
      }

      .ev-swal-status-icon,
      .ev-mp-swal-status-icon,
      .ev-mpv-swal-status-icon{
        width:92px;
        height:92px;
        border-radius:999px;
        margin:2px auto 16px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:#ffffff !important;
        background-image:none !important;
        border:2px solid rgba(22,163,74,.22);
        box-shadow:0 10px 26px rgba(15,23,42,.06), inset 0 0 0 1px rgba(255,255,255,.96) !important;
      }

      .ev-swal-status-icon svg,
      .ev-mp-swal-status-icon svg,
      .ev-mpv-swal-status-icon svg{
        width:50px;
        height:50px;
      }

      .ev-mp-swal-status-icon--success,
      .ev-mpv-swal-status-icon:not(.ev-mpv-swal-status-icon--info){
        background:#ffffff !important;
        border-color:rgba(22,163,74,.24) !important;
      }

      .ev-mp-swal-status-icon--info,
      .ev-mpv-swal-status-icon--info{
        background:#ffffff !important;
        border-color:rgba(56,189,248,.28) !important;
      }

      .ev-swal-subtitle,
      .ev-mp-swal-subtitle,
      .ev-mpv-swal-subtitle{
        font-weight:900;
        font-size:1.08rem;
        color:var(--ev-swal-green);
        margin-bottom:8px;
        letter-spacing:-.02em;
        text-align:center;
      }

      .ev-swal-soft-text,
      .ev-mp-swal-soft-text,
      .ev-mpv-swal-soft-text{
        color:var(--ev-swal-muted);
        font-size:.96rem;
        line-height:1.62;
        max-width:430px;
        margin:0 auto;
        text-align:center;
      }

      .ev-mp-swal-product-card,
      .ev-mpv-swal-product-card,
      .ev-swal-product-card{
        margin-top:16px;
        padding:13px 16px;
        border-radius:18px;
        background:#ffffff !important;
        border:1px solid rgba(229,231,235,.96) !important;
        box-shadow:0 8px 22px rgba(15,23,42,.045) !important;
        text-align:left;
      }

      .ev-mp-swal-note,
      .ev-mpv-swal-note,
      .ev-swal-note{
        background:#ffffff !important;
        background-image:none !important;
        border:1px solid rgba(234,124,18,.20) !important;
        box-shadow:0 10px 24px rgba(234,124,18,.06) !important;
      }

      .swal2-actions{ gap: 12px !important; }

      .swal2-confirm.ev-swal-confirm,
      .swal2-confirm.ev-mp-swal-confirm,
      .swal2-confirm.ev-mpv-swal-confirm,
      .swal2-confirm.ev-mpc-swal-confirm,
      .swal2-confirm.btn-ev-orange{
        background: linear-gradient(135deg, var(--ev-swal-orange), var(--ev-swal-orange-2)) !important;
        border: none !important;
        color: #fff !important;
        border-radius: 15px !important;
        min-width: 150px !important;
        padding: 13px 24px !important;
        font-weight: 900 !important;
        font-size: .98rem !important;
        box-shadow: 0 14px 30px rgba(234,124,18,.30) !important;
        transition: transform .16s ease, box-shadow .16s ease, filter .16s ease !important;
      }

      .swal2-confirm.ev-swal-confirm:hover,
      .swal2-confirm.ev-mp-swal-confirm:hover,
      .swal2-confirm.ev-mpv-swal-confirm:hover,
      .swal2-confirm.ev-mpc-swal-confirm:hover,
      .swal2-confirm.btn-ev-orange:hover{
        transform: translateY(-1px) !important;
        filter: brightness(1.03) !important;
        box-shadow: 0 18px 36px rgba(234,124,18,.38) !important;
      }

      .swal2-cancel.ev-swal-cancel,
      .swal2-cancel.ev-mp-swal-cancel,
      .swal2-cancel.ev-mpv-swal-cancel,
      .swal2-cancel.ev-mpc-swal-cancel,
      .swal2-cancel.btn-ev-outline{
        background: #ffffff !important;
        color: #4B5563 !important;
        border: 1.5px solid #D1D5DB !important;
        border-radius: 15px !important;
        min-width: 150px !important;
        padding: 13px 24px !important;
        font-weight: 900 !important;
        font-size: .98rem !important;
        box-shadow: 0 8px 18px rgba(15,23,42,.06) !important;
        transition: transform .16s ease, background .16s ease, box-shadow .16s ease !important;
      }

      .swal2-cancel.ev-swal-cancel:hover,
      .swal2-cancel.ev-mp-swal-cancel:hover,
      .swal2-cancel.ev-mpv-swal-cancel:hover,
      .swal2-cancel.ev-mpc-swal-cancel:hover,
      .swal2-cancel.btn-ev-outline:hover{
        background:#F9FAFB !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 12px 24px rgba(15,23,42,.09) !important;
      }

      /* Alineación precisa de icono y texto en acciones EV. */
      .swal2-confirm.ev-swal-confirm,
      .swal2-confirm.ev-mp-swal-confirm,
      .swal2-confirm.ev-mpv-swal-confirm,
      .swal2-confirm.ev-mpc-swal-confirm,
      .swal2-confirm.btn-ev-orange,
      .swal2-cancel.ev-swal-cancel,
      .swal2-cancel.ev-mp-swal-cancel,
      .swal2-cancel.ev-mpv-swal-cancel,
      .swal2-cancel.ev-mpc-swal-cancel,
      .swal2-cancel.btn-ev-outline{
        display:inline-flex;
        align-items:center !important;
        justify-content:center !important;
        gap:8px !important;
        line-height:1.1 !important;
        white-space:nowrap !important;
      }
      .swal2-confirm.ev-swal-confirm i,
      .swal2-cancel.ev-swal-cancel i{
        display:inline-grid !important;
        place-items:center !important;
        flex:0 0 auto !important;
        margin:0 !important;
        font-size:1rem !important;
        line-height:1 !important;
      }
      .swal2-confirm.ev-swal-confirm span,
      .swal2-cancel.ev-swal-cancel span{
        display:inline-block !important;
        margin:0 !important;
        line-height:1.1 !important;
      }

      /*
       * SweetAlert2 oculta acciones con style="display: none".
       * Nunca debemos reabrirlas desde el tema EV. Esta salvaguarda evita
       * que un !important de alineación vuelva a mostrar Cancel/Denegar.
       */
      .swal2-popup .swal2-cancel[style*="display: none"],
      .swal2-popup .swal2-deny[style*="display: none"],
      .swal2-popup .swal2-confirm[style*="display: none"],
      .swal2-popup.ev-swal-nocancel .swal2-cancel,
      .swal2-popup.ev-swal-nocancel .swal2-deny{
        display:none !important;
      }

      .swal2-select,
      .swal2-textarea,
      .swal2-input{
        width: min(100%, 420px) !important;
        border: 1.5px solid #DDE5EE !important;
        border-radius: 14px !important;
        background: #ffffff !important;
        color: var(--ev-swal-text) !important;
        box-shadow: 0 8px 18px rgba(15,23,42,.04) !important;
        font-weight: 650 !important;
        outline: none !important;
      }

      .swal2-select:focus,
      .swal2-textarea:focus,
      .swal2-input:focus{
        border-color: rgba(22,163,74,.55) !important;
        box-shadow: 0 0 0 4px rgba(22,163,74,.14), 0 8px 18px rgba(15,23,42,.04) !important;
      }

      .swal2-validation-message{
        border-radius: 14px !important;
        background: #FFF7ED !important;
        color: #9A3412 !important;
        font-weight: 800 !important;
        margin: 12px auto 0 !important;
      }

      .ev-swal-select-wrap{ margin-top:16px; text-align:left; }
      .ev-swal-select-label{
        display:block; color:#6B7280; font-size:.78rem; font-weight:900; margin-bottom:8px;
        text-transform:uppercase; letter-spacing:.08em;
      }
      .ev-swal-select-list{ display:grid; gap:9px; }
      .ev-swal-option{
        display:flex; align-items:center; gap:10px; cursor:pointer;
        border:1.5px solid #E5E7EB; border-radius:16px; padding:12px 14px;
        background:#fff; color:#111827; font-weight:850; transition:all .16s ease;
      }
      .ev-swal-option input{ position:absolute; opacity:0; pointer-events:none; }
      .ev-swal-option-dot{ width:13px; height:13px; border-radius:999px; border:2px solid #CBD5E1; flex:0 0 13px; }
      .ev-swal-option:hover,
      .ev-swal-option.is-active{ border-color:rgba(22,163,74,.45); background:#F7FEFA; box-shadow:0 10px 22px rgba(15,23,42,.05); }
      .ev-swal-option.is-active .ev-swal-option-dot{ border-color:#16A34A; background:#16A34A; box-shadow:inset 0 0 0 3px #fff; }

      .swal2-popup.ev-swal-popup .swal2-icon,
      .swal2-popup.ev-mp-swal-popup .swal2-icon,
      .swal2-popup.ev-mpv-swal-popup-premium .swal2-icon,
      .swal2-popup.ev-mpc-swal-popup .swal2-icon{
        background:#fff !important;
        box-shadow:0 10px 26px rgba(15,23,42,.06) !important;
      }

      @media (max-width:575.98px){
        .swal2-popup.ev-swal-popup,
        .swal2-popup.ev-mp-swal-popup,
        .swal2-popup.ev-mp-swal-popup-seguimiento,
        .swal2-popup.ev-mpv-swal-popup-premium,
        .swal2-popup.ev-mpc-swal-popup,
        .swal2-popup.ev-mpc-swal-popup-premium{
          width:min(94vw,94vw) !important;
          padding:22px 16px 18px !important;
          border-radius:22px !important;
        }

        .swal2-popup.ev-swal-popup-detail,
        .swal2-popup.ev-mpc-swal-popup-detail,
        .swal2-popup.ev-mpv-swal-popup-detail{
          width:min(96vw,96vw) !important;
          max-width:96vw !important;
        }

        .swal2-confirm.ev-swal-confirm,
        .swal2-cancel.ev-swal-cancel,
        .swal2-confirm.ev-mp-swal-confirm,
        .swal2-cancel.ev-mp-swal-cancel,
        .swal2-confirm.ev-mpv-swal-confirm,
        .swal2-cancel.ev-mpv-swal-cancel,
        .swal2-confirm.ev-mpc-swal-confirm,
        .swal2-cancel.ev-mpc-swal-cancel{
          width:100% !important;
          min-width:0 !important;
        }
      }
    `;
    document.head.appendChild(style);
  }

  function iconHtml(type) {
    const t = String(type || 'success').toLowerCase();

    if (t === 'info') {
      return `
        <div class="ev-swal-status-icon" aria-hidden="true" style="border-color:rgba(56,189,248,.28)">
          <svg viewBox="0 0 64 64" fill="none">
            <path d="M32 18.5C34.5 18.5 36.3 20.2 36.3 22.6C36.3 25 34.5 26.8 32 26.8C29.5 26.8 27.7 25 27.7 22.6C27.7 20.2 29.5 18.5 32 18.5Z" fill="#38BDF8"/>
            <path d="M32 31.5V45.5" stroke="#38BDF8" stroke-width="5" stroke-linecap="round"/>
          </svg>
        </div>`;
    }

    if (t === 'warning' || t === 'question') {
      return `
        <div class="ev-swal-status-icon" aria-hidden="true" style="border-color:rgba(234,124,18,.28)">
          <svg viewBox="0 0 64 64" fill="none">
            <path d="M32 12L53 49H11L32 12Z" stroke="#EA7C12" stroke-width="4" fill="none"></path>
            <path d="M32 24V36" stroke="#EA7C12" stroke-width="5" stroke-linecap="round"></path>
            <circle cx="32" cy="43.5" r="2.8" fill="#EA7C12"></circle>
          </svg>
        </div>`;
    }

    if (t === 'error') {
      return `
        <div class="ev-swal-status-icon" aria-hidden="true" style="border-color:rgba(220,38,38,.22)">
          <svg viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="32" r="28" stroke="#DC2626" stroke-width="4" fill="none"></circle>
            <path d="M24 24L40 40M40 24L24 40" stroke="#DC2626" stroke-width="5" stroke-linecap="round"></path>
          </svg>
        </div>`;
    }

    return `
      <div class="ev-swal-status-icon" aria-hidden="true">
        <svg viewBox="0 0 64 64" fill="none">
          <path d="M18 33.5L27.5 43L46 23.5" stroke="#16A34A" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>`;
  }

  function messageHtml(opts = {}) {
    const type = opts.type || 'success';
    const subtitle = opts.subtitle || '';
    const text = opts.text || '';
    const extra = opts.extra || '';

    return `
      <div style="text-align:center">
        ${iconHtml(type)}
        ${subtitle ? `<div class="ev-swal-subtitle">${escapeHtml(subtitle)}</div>` : ''}
        ${text ? `<div class="ev-swal-soft-text">${escapeHtml(text)}</div>` : ''}
        ${extra}
      </div>`;
  }

  const defaultClasses = {
    container: 'ev-swal-container',
    popup: 'ev-swal-popup',
    title: 'ev-swal-title',
    htmlContainer: 'ev-swal-html',
    confirmButton: 'ev-swal-confirm',
    cancelButton: 'ev-swal-cancel',
    closeButton: 'ev-swal-close'
  };

  function mergeCustomClass(userClasses) {
    const user = (userClasses && typeof userClasses === 'object') ? userClasses : {};
    const merged = Object.assign({}, user);
    Object.keys(defaultClasses).forEach((key) => {
      merged[key] = classJoin(defaultClasses[key], user[key]);
    });
    return merged;
  }

  function normalizeFireConfig(args) {
    let config;

    if (args.length === 1 && args[0] && typeof args[0] === 'object') {
      config = Object.assign({}, args[0]);
    } else if (args.length > 0) {
      config = {
        title: args[0],
        text: args[1],
        icon: args[2]
      };
    } else {
      config = {};
    }

    if (config.toast === true) return config;

    config.customClass = mergeCustomClass(config.customClass);

    if (typeof config.buttonsStyling === 'undefined') {
      config.buttonsStyling = false;
    }

    const hasInput = Boolean(config.input);
    const hasHtml = typeof config.html !== 'undefined' && config.html !== null && String(config.html).trim() !== '';
    const hasIcon = typeof config.icon !== 'undefined' && config.icon !== null && String(config.icon).trim() !== '';

    if (hasIcon && !hasInput && !hasHtml) {
      const iconType = String(config.icon || 'info').toLowerCase();
      const title = String(config.title || '').trim();
      const text = String(config.text || '').trim();
      config.html = messageHtml({
        type: iconType,
        subtitle: title,
        text
      });
      delete config.icon;
      delete config.text;
    } else if (hasIcon && hasHtml && !hasInput) {
      // Evita duplicar el ícono nativo de SweetAlert2 cuando el módulo ya genera HTML EV.
      delete config.icon;
    }

    // Política global EV para SweetAlert:
    // - los mensajes se cierran únicamente mediante acciones explícitas;
    // - ningún SweetAlert muestra botón X;
    // - los modales de formulario conservan su propio cierre estándar EV.
    config.allowOutsideClick = false;
    config.allowEscapeKey = false;
    config.showCloseButton = false;

    // Elimina variantes antiguas que convertían algunos SweetAlert en una
    // cabecera verde tipo modal. El SweetAlert conserva el diseño premium
    // EV de mensaje (blanco + línea cromática superior).
    if (config.customClass && typeof config.customClass.popup === 'string') {
      config.customClass.popup = config.customClass.popup
        .split(/\s+/)
        .filter((cls) => cls && cls !== 'ev-swal-headered' && cls !== 'ev-swal-headered--dedupe')
        .join(' ');
    }

    /*
     * Reglas de consistencia para mensajes transversales.
     * Se fuerzan aquí para evitar que un módulo vuelva a introducir botones
     * o textos heredados de SweetAlert2 (OK / Cancel) en futuras llamadas.
     */
    const normalizedTitle = String(config.title || '').trim().toLocaleLowerCase('es');

    if (normalizedTitle === 'sesión finalizada' || normalizedTitle === 'tu sesión ha finalizado') {
      config.showCancelButton = false;
      config.showDenyButton = false;
      config.showConfirmButton = true;
      config.confirmButtonText = 'Aceptar';
      config.showCloseButton = false;
      config.customClass.popup = classJoin(config.customClass.popup, 'ev-swal-nocancel');
    }

    if (normalizedTitle === 'ayuda ev' || normalizedTitle === 'foto actualizada') {
      config.showCancelButton = false;
      config.showDenyButton = false;
      config.showConfirmButton = true;
      config.confirmButtonText = 'Aceptar';
      config.customClass.popup = classJoin(config.customClass.popup, 'ev-swal-nocancel');
    }

    if (normalizedTitle === '¿deseas cerrar sesión?') {
      config.showCloseButton = false;
    }

    aplicarEstandarAceptarCancelar(config);
    return config;
  }

  function patchSweetAlertFire() {
    if (!window.Swal || typeof window.Swal.fire !== 'function') return;
    if (window.__EV_SWAL_FIRE_STANDARD_PATCHED__ === true) return;

    window.__EV_SWAL_FIRE_STANDARD_PATCHED__ = true;
    const originalFire = window.Swal.fire.bind(window.Swal);

    window.Swal.fire = function () {
      injectStyles();
      const config = normalizeFireConfig(Array.from(arguments));
      return originalFire(config);
    };
  }

  function swalBase(opts = {}) {
    injectStyles();
    const config = Object.assign({
      buttonsStyling:false,
      customClass: defaultClasses
    }, opts || {});

    config.allowOutsideClick = false;
    config.allowEscapeKey = false;
    return config;
  }

  async function success(title, text, opts = {}) {
    if (!window.Swal?.fire) {
      alert(`${title}\n\n${text}`);
      return { isConfirmed:true };
    }
    return Swal.fire(swalBase(Object.assign({
      title,
      html: messageHtml({ type:'success', subtitle: opts.subtitle || title, text, extra: opts.extra || '' }),
      confirmButtonText: opts.confirmButtonText || 'Entendido',
      showConfirmButton: opts.showConfirmButton !== false,
      timer: opts.timer || undefined
    }, opts || {})));
  }

  async function welcome(nombre) {
    if (!window.Swal?.fire) return;
    const cleanName = String(nombre || '').trim();
    return Swal.fire(swalBase({
      title: 'Bienvenido a Entre Vecinos',
      html: messageHtml({
        type:'success',
        subtitle: cleanName ? `Hola, ${cleanName}` : 'Inicio de sesión correcto',
        text:'Tu sesión se inició correctamente. Estamos preparando tu panel.'
      }),
      timer: 1450,
      showConfirmButton:false,
      allowOutsideClick:false,
      allowEscapeKey:false
    }));
  }

  function select(options = {}) {
    if (!window.Swal?.fire) return Promise.resolve({ isConfirmed:false, value:null });

    const entries = Object.entries(options.options || {});
    const htmlOptions = entries.map(([value, label]) => `
      <label class="ev-swal-option">
        <input type="radio" name="ev_swal_select" value="${escapeHtml(value)}">
        <span class="ev-swal-option-dot"></span>
        <span>${escapeHtml(label)}</span>
      </label>
    `).join('');

    const html = messageHtml({
      type: options.icon === 'warning' ? 'warning' : (options.icon || 'info'),
      subtitle: options.subtitle || '',
      text: options.text || '',
      extra: `
        <div class="ev-swal-select-wrap">
          ${options.label ? `<span class="ev-swal-select-label">${escapeHtml(options.label)}</span>` : ''}
          <div class="ev-swal-select-list">${htmlOptions}</div>
        </div>
      `
    });

    return Swal.fire(swalBase({
      title: options.title || 'Selecciona una opción',
      html,
      showCancelButton:true,
      confirmButtonText: options.confirmButtonText || 'Continuar',
      cancelButtonText: options.cancelButtonText || 'Cancelar',
      preConfirm: () => {
        const checked = document.querySelector('input[name="ev_swal_select"]:checked');
        if (!checked) {
          Swal.showValidationMessage(options.validationMessage || 'Debes seleccionar una opción.');
          return false;
        }
        return checked.value;
      },
      didOpen: (popup) => {
        popup.querySelectorAll('.ev-swal-option').forEach((label) => {
          label.addEventListener('click', () => {
            popup.querySelectorAll('.ev-swal-option').forEach(x => x.classList.remove('is-active'));
            label.classList.add('is-active');
          });
        });
      }
    }));
  }

  injectStyles();
  patchSweetAlertFire();

  window.EVSwal = Object.assign(window.EVSwal || {}, {
    base: swalBase,
    htmlMessage: messageHtml,
    success,
    welcome,
    select
  });
})();
