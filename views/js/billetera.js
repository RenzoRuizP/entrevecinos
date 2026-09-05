// views/js/billetera.js
(function () {
  'use strict';

  const BASE = (window.EV?.baseUrl ?? window.BASE_URL ?? '').replace(/\/$/, '');
  const NOTIF_WALLET_TARGET_KEY = 'ev_notificacion_billetera_destino';
  const COMPRA_PREPARADA_PENDIENTE_KEY = 'ev_compra_preparada_pendiente_v1';
  const COMPRA_PREPARADA_TTL_MS = 48 * 60 * 60 * 1000;
  const LOG_PREFIX = '[BILLETERA]';
  // IMPORTANTE: billetera.js se carga con el shell de MenuPrincipal, antes de que
  // la vista /billetera inyecte EV_WALLET_CONFIG. Por eso esta configuración debe
  // leerse en tiempo de uso y no congelarse al cargar el archivo.
  function walletConfig() { return window.EV_WALLET_CONFIG || {}; }
  function walletSection() { return String(walletConfig().section || 'resumen').toLowerCase(); }
  function recargasDisponibles() { return walletConfig().recargasDisponibles !== false; }
  function retirosDisponibles() { return walletConfig().retirosDisponibles === true; }
  function walletCsrf() { return String(walletConfig().csrf || ''); }

  const BC_NAME = 'EV_CHANNEL';
  let bc = null;
  try { bc = ('BroadcastChannel' in window) ? new BroadcastChannel(BC_NAME) : null; } catch (_) { bc = null; }
  let compraPendientePollingTimer = null;
  const COMPRA_PENDIENTE_POLLING_MS = 15000;

  let refs = {
    wrapper: null,
    saldo: null,
    emptyState: null,
    movimientos: null,
    totalMovimientos: null,
    totalRecargas: null,

    recargasEmpty: null,
    recargasTable: null,
    btnRefrescarRecargas: null,
    btnAbrirNuevaRecarga: null,
    btnLimpiarRecarga: null,

    recargaForm: null,
    recargaCodigo: null,
    recargaModo: null,
    recargaTipo: null,
    recargaMonto: null,
    recargaOperacion: null,
    recargaImagen: null,
    recargaImagenHelp: null,
    recargaAlertaSubsanacion: null,
    recargaAlertaSubsanacionTexto: null,
    modalRecargaTitulo: null,
    btnEnviarRecarga: null,

    qrImg: null,
    qrTitle: null,
    qrText: null,
    qrCard: null,

    retiroSaldo: null,
    retiroEnProceso: null,
    retiroProximoPago: null,
    retiroCorteDetalle: null,
    retiroEstadoCorte: null,
    retiroCuentaResumen: null,
    retiroCuentaEstado: null,
    retiroMensaje: null,
    retirosHistorial: null,
    btnCuentaRetiro: null,
    btnRetirarSaldo: null,
    formCuentaRetiro: null,
    retiroBanco: null,
    retiroTipoCuenta: null,
    retiroTipoCuentaError: null,
    retiroNumeroCuenta: null,
    retiroNumeroCuentaHelp: null,
    retiroNumeroCuentaError: null,
    retiroCci: null,
    retiroCciHelp: null,
    retiroCciError: null,
    retiroBancoError: null,
    retiroDeclara: null,
    retiroTitularNombre: null,
    retiroTitularDocumento: null,
    retiroCuentaObservacion: null,
    btnGuardarCuentaRetiro: null,
  };

  let state = {
    misRecargas: [],
    retiro: null,
    saldoActual: 0,
    saldoCargado: false,
    compraPendienteModalAbierto: false
  };

  const QR_CONFIG = {
    yape: {
      img: `${BASE}/resources/images/yape.jpg`,
      title: 'Paga tu recarga con Yape',
      text: 'Escanea este código desde Yape, ingresa el monto que deseas recargar y luego sube el comprobante en este formulario.'
    },
    plin: {
      img: `${BASE}/resources/images/plin.jpeg`,
      title: 'Paga tu recarga con Plin',
      text: 'Escanea este código desde tu app bancaria, ingresa el monto que deseas recargar y luego sube el comprobante en este formulario.'
    }
  };

  function log() { if (window.console && console.log) console.log(LOG_PREFIX, ...arguments); }
  function error() { if (window.console && console.error) console.error(LOG_PREFIX, ...arguments); }

  function capturarRefs() {
    refs.wrapper = document.querySelector('.ev-wallet-wrapper');
    if (!refs.wrapper) return false;

    refs.recargasEmpty = document.getElementById('ev_recargas_empty');
    refs.recargasTable = document.getElementById('ev_recargas_table');
    refs.btnRefrescarRecargas = document.getElementById('btnRefrescarRecargas');
    refs.btnAbrirNuevaRecarga = document.getElementById('btnAbrirNuevaRecarga');
    refs.btnLimpiarRecarga = document.getElementById('btnLimpiarRecarga');

    refs.saldo = document.getElementById('ev_wallet_saldo');
    refs.emptyState = document.getElementById('ev_wallet_empty_state');
    refs.movimientos = document.getElementById('ev_wallet_movimientos');
    refs.totalMovimientos = document.getElementById('ev_wallet_total_movimientos');
    refs.totalRecargas = document.getElementById('ev_wallet_total_recargas');

    refs.recargaForm = document.getElementById('formRecargaSaldo');
    refs.recargaCodigo = document.getElementById('recarga_codigo');
    refs.recargaModo = document.getElementById('recarga_modo');
    refs.recargaTipo = document.getElementById('recarga_tipo');
    refs.recargaMonto = document.getElementById('recarga_monto');
    refs.recargaOperacion = document.getElementById('recarga_operacion');
    refs.recargaImagen = document.getElementById('recarga_imagen');
    refs.recargaImagenHelp = document.getElementById('recarga_imagen_help');
    refs.recargaAlertaSubsanacion = document.getElementById('recarga_alerta_subsanacion');
    refs.recargaAlertaSubsanacionTexto = document.getElementById('recarga_alerta_subsanacion_texto');
    refs.modalRecargaTitulo = document.getElementById('modalRecargaTitulo');
    refs.btnEnviarRecarga = document.getElementById('btnEnviarRecarga');

    refs.qrImg = document.getElementById('ev_qr_img');
    refs.qrTitle = document.getElementById('ev_qr_title');
    refs.qrText = document.getElementById('ev_qr_text');
    refs.qrCard = document.getElementById('ev_qr_card');

    refs.retiroSaldo = document.getElementById('ev_retiro_saldo_actual');
    refs.retiroEnProceso = document.getElementById('ev_retiro_en_proceso');
    refs.retiroProximoPago = document.getElementById('ev_retiro_proximo_pago');
    refs.retiroCorteDetalle = document.getElementById('ev_retiro_corte_detalle');
    refs.retiroEstadoCorte = document.getElementById('ev_retiro_estado_corte');
    refs.retiroCuentaResumen = document.getElementById('ev_retiro_cuenta_resumen');
    refs.retiroCuentaEstado = document.getElementById('ev_retiro_cuenta_estado');
    refs.retiroMensaje = document.getElementById('ev_retiro_mensaje');
    refs.retirosHistorial = document.getElementById('ev_retiros_historial');
    refs.btnCuentaRetiro = document.getElementById('btnCuentaRetiro');
    refs.btnRetirarSaldo = document.getElementById('btnRetirarSaldo');
    refs.formCuentaRetiro = document.getElementById('formCuentaRetiro');
    refs.retiroBanco = document.getElementById('retiro_banco');
    refs.retiroTipoCuenta = document.getElementById('retiro_tipo_cuenta');
    refs.retiroTipoCuentaError = document.getElementById('retiro_tipo_cuenta_error');
    refs.retiroNumeroCuenta = document.getElementById('retiro_numero_cuenta');
    refs.retiroNumeroCuentaHelp = document.getElementById('retiro_numero_cuenta_help');
    refs.retiroNumeroCuentaError = document.getElementById('retiro_numero_cuenta_error');
    refs.retiroCci = document.getElementById('retiro_cci');
    refs.retiroCciHelp = document.getElementById('retiro_cci_help');
    refs.retiroCciError = document.getElementById('retiro_cci_error');
    refs.retiroBancoError = document.getElementById('retiro_banco_error');
    refs.retiroDeclara = document.getElementById('retiro_declara_titularidad');
    refs.retiroTitularNombre = document.getElementById('retiro_titular_nombre');
    refs.retiroTitularDocumento = document.getElementById('retiro_titular_documento');
    refs.retiroCuentaObservacion = document.getElementById('retiro_cuenta_observacion');
    refs.btnGuardarCuentaRetiro = document.getElementById('btnGuardarCuentaRetiro');

    return true;
  }


  function redondearMonto(valor) {
    return Math.round((Number(valor || 0) + Number.EPSILON) * 100) / 100;
  }

  function leerCompraPreparadaPendiente() {
    try {
      const raw = sessionStorage.getItem(COMPRA_PREPARADA_PENDIENTE_KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      const age = Date.now() - Number(data?.updated_at || data?.created_at || 0);
      if (Number(data?.codigo_producto || 0) <= 0 || age < 0 || age > COMPRA_PREPARADA_TTL_MS) {
        sessionStorage.removeItem(COMPRA_PREPARADA_PENDIENTE_KEY);
        return null;
      }
      return data;
    } catch (_) {
      return null;
    }
  }

  function actualizarCompraPreparadaPendiente(patch = {}) {
    const actual = leerCompraPreparadaPendiente();
    if (!actual) return null;
    const nuevo = { ...actual, ...patch, updated_at: Date.now() };
    try { sessionStorage.setItem(COMPRA_PREPARADA_PENDIENTE_KEY, JSON.stringify(nuevo)); } catch (_) {}
    return nuevo;
  }

  function aplicarContextoCompraPendienteRecarga() {
    if (walletSection() !== 'recargar') return false;
    const contexto = leerCompraPreparadaPendiente();
    if (!contexto || !['recarga_requerida', 'validacion_soporte'].includes(String(contexto.etapa || ''))) return false;

    const saldoReferencia = state.saldoCargado
      ? Number(state.saldoActual || 0)
      : Number(contexto.saldo_actual || 0);
    const faltanteCalculado = redondearMonto(Math.max(0, Number(contexto.monto_requerido || 0) - saldoReferencia));
    const montoFaltante = state.saldoCargado
      ? faltanteCalculado
      : (faltanteCalculado > 0
          ? faltanteCalculado
          : redondearMonto(Number(contexto.monto_faltante || 0)));
    const etapa = String(contexto.etapa || '');
    const card = refs.recargaForm?.closest('.ev-wallet-recharge-form-card');

    if (card) {
      card.classList.add('is-purchase-recharge-target');
      let banner = card.querySelector('.ev-wallet-purchase-recharge-context');
      if (!banner) {
        banner = document.createElement('div');
        banner.className = 'ev-wallet-purchase-recharge-context';
        const heading = card.querySelector('.ev-wallet-section-heading');
        if (heading?.parentNode) heading.insertAdjacentElement('afterend', banner);
        else card.prepend(banner);
      }
      banner.innerHTML = etapa === 'validacion_soporte'
        ? `
          <i class="bi bi-hourglass-split" aria-hidden="true"></i>
          <div>
            <strong>Tu pago está en validación</strong>
            <span>Soporte EV revisará el comprobante de <b>${esc(contexto.titulo_producto || 'tu compra')}</b>. No necesitas registrar otra recarga.</span>
          </div>
        `
        : `
          <i class="bi bi-bag-check" aria-hidden="true"></i>
          <div>
            <strong>Completa el saldo para continuar tu compra</strong>
            <span>Te faltan <b>${formatearMonto(montoFaltante)}</b> para <b>${esc(contexto.titulo_producto || 'el producto seleccionado')}</b>.</span>
          </div>
        `;
      window.requestAnimationFrame(() => card.scrollIntoView({ behavior: 'smooth', block: 'center' }));
    }

    if (refs.recargaMonto && etapa === 'recarga_requerida' && montoFaltante > 0) {
      refs.recargaMonto.value = montoFaltante.toFixed(2);
      refs.recargaMonto.setAttribute('data-ev-prefill-compra', '1');
    }

    return true;
  }

  function limpiarResaltadoCompraPendiente() {
    const card = refs.recargaForm?.closest('.ev-wallet-recharge-form-card');
    card?.classList.remove('is-purchase-recharge-target');
    card?.querySelector('.ev-wallet-purchase-recharge-context')?.remove();
    refs.recargaMonto?.removeAttribute('data-ev-prefill-compra');
  }

  async function evaluarCompraPendienteParaContinuar() {
    if (walletSection() !== 'recargar' || state.compraPendienteModalAbierto) return false;
    const contexto = leerCompraPreparadaPendiente();
    const etapa = String(contexto?.etapa || '');
    if (!contexto || !['recarga_requerida', 'validacion_soporte'].includes(etapa)) return false;

    const recargaId = Number(contexto.codigo_recarga || 0);
    const recarga = recargaId > 0
      ? (state.misRecargas || []).find((item) => Number(item.id || 0) === recargaId)
      : null;
    const recargaAprobada = String(recarga?.estado || '').toLowerCase() === 'aprobada';
    const saldoSuficiente = Number(state.saldoActual || 0) + 0.00001 >= Number(contexto.monto_requerido || 0);
    const puedeContinuar = saldoSuficiente && (etapa === 'recarga_requerida' || recargaAprobada);

    if (!puedeContinuar) return false;

    detenerPollingCompraPendiente();
    state.compraPendienteModalAbierto = true;
    limpiarResaltadoCompraPendiente();
    actualizarCompraPreparadaPendiente({ etapa: 'lista_para_continuar', saldo_actual: state.saldoActual });

    try {
      const result = window.Swal?.fire
        ? await swalFireEV({
            icon: 'success',
            title: 'Saldo disponible',
            html: `Tu recarga fue validada y ya cuentas con el saldo necesario para continuar con <strong>${esc(contexto.titulo_producto || 'tu compra')}</strong>.`,
            confirmButtonText: 'Continuar compra',
            showCancelButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
          })
        : { isConfirmed: window.confirm('Tu saldo ya está disponible. ¿Deseas continuar tu compra?') };

      if (result?.isConfirmed) {
        navegarWallet('/marketplace');
      }
    } finally {
      state.compraPendienteModalAbierto = false;
    }

    return true;
  }


  function detenerPollingCompraPendiente() {
    if (compraPendientePollingTimer) {
      window.clearInterval(compraPendientePollingTimer);
      compraPendientePollingTimer = null;
    }
  }

  function iniciarPollingCompraPendiente() {
    detenerPollingCompraPendiente();
    const contexto = leerCompraPreparadaPendiente();
    if (walletSection() !== 'recargar' || String(contexto?.etapa || '') !== 'validacion_soporte') return;

    compraPendientePollingTimer = window.setInterval(async () => {
      if (document.hidden || walletSection() !== 'recargar' || !document.querySelector('.ev-wallet-wrapper')) return;
      await Promise.all([cargarSaldo(), cargarMisRecargas()]);
    }, COMPRA_PENDIENTE_POLLING_MS);
  }

  function formatearMonto(monto) {
    const n = Number(monto || 0);
    return 'S/ ' + n.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
  }

  function swalFireEV(opts) {
    if (!window.Swal?.fire) return null;

    const baseCustomClass = {
      popup: 'ev-swal-popup',
      title: 'ev-swal-title',
      htmlContainer: 'ev-swal-html',
      icon: 'ev-swal-icon',
      confirmButton: 'ev-swal-confirm btn-ev-orange',
      cancelButton: 'ev-swal-cancel btn-ev-outline'
    };

    const userCC = (opts && opts.customClass) ? opts.customClass : {};
    const mergedCustomClass = Object.assign({}, baseCustomClass, userCC);

    const base = {
      title: 'Entre Vecinos',
      customClass: mergedCustomClass,
      buttonsStyling: false,
      focusConfirm: true,
      showCancelButton: false,
      heightAuto: false
    };

    return Swal.fire(Object.assign({}, base, opts || {}));
  }

  function swalInfo(msg) {
    if (window.Swal?.fire) {
      return swalFireEV({
        icon: 'info',
        text: msg,
        confirmButtonText: 'Entendido',
        showCancelButton: false,
        customClass: { popup: 'ev-swal-popup ev-swal-nocancel' }
      });
    }
    alert(msg);
  }

  function swalOk(msg) {
    if (window.Swal?.fire) {
      return swalFireEV({
        icon: 'success',
        title: 'Listo',
        text: msg,
        timer: 1700,
        showConfirmButton: false,
        showCancelButton: false,
        customClass: { popup: 'ev-swal-popup ev-swal-nocancel' }
      });
    }
    alert(msg);
  }

  function swalErr(msg) {
    if (window.Swal?.fire) {
      return swalFireEV({
        icon: 'error',
        title: 'Ocurrió un problema',
        text: msg,
        confirmButtonText: 'Entendido',
        showCancelButton: false,
        customClass: { popup: 'ev-swal-popup ev-swal-nocancel' }
      });
    }
    alert(msg);
  }

  async function swalBlockedAndRedirect(msg, redirect) {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;
    window.__EV_AUTH_REDIRECTING__ = true;

    const mensaje = msg || 'Tu cuenta fue bloqueada. Se cerró tu sesión por seguridad.';
    const target = redirect || `${BASE}/login`;

    if (window.Swal?.fire) {
      await swalFireEV({
        icon: 'warning',
        title: 'Cuenta bloqueada',
        text: mensaje,
        confirmButtonText: 'Ir al login',
        showCancelButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: { popup: 'ev-swal-popup ev-swal-nocancel' }
      });
    } else {
      alert(mensaje);
    }

    window.location.assign(target);
  }

  async function swalSessionAndRedirect(msg, redirect) {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;
    window.__EV_AUTH_REDIRECTING__ = true;

    const mensaje = msg || 'Tu sesión expiró. Vuelve a iniciar sesión.';
    const target = redirect || `${BASE}/login`;

    if (window.Swal?.fire) {
      await swalFireEV({
        icon: 'info',
        title: 'Sesión finalizada',
        text: mensaje,
        confirmButtonText: 'Ir al login',
        showCancelButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: { popup: 'ev-swal-popup ev-swal-nocancel' }
      });
    } else {
      alert(mensaje);
    }

    window.location.assign(target);
  }

  async function leerRespuestaSeguro(resp) {
    const ct = (resp.headers.get('content-type') || '').toLowerCase();
    if (ct.includes('application/json')) {
      return await resp.json().catch(() => ({}));
    }
    const txt = await resp.text().catch(() => '');
    try { return JSON.parse(txt); } catch (_) {}
    return { ok: false, mensaje: txt || 'Respuesta no válida del servidor.' };
  }

  async function manejarAuthEspecial(resp, data) {
    if (resp.status === 403 && data && data.error === 'CUENTA_BLOQUEADA') {
      await swalBlockedAndRedirect(data.mensaje, data.redirect);
      return true;
    }

    if (resp.status === 401 || (data && data.error === 'UNAUTHORIZED')) {
      await swalSessionAndRedirect(data.mensaje, data.redirect);
      return true;
    }

    return false;
  }

  function actualizarContadores({ movimientos = null, recargas = null } = {}) {
    if (refs.totalMovimientos && movimientos !== null) {
      refs.totalMovimientos.textContent = String(movimientos);
    }
    if (refs.totalRecargas && recargas !== null) {
      refs.totalRecargas.textContent = String(recargas);
    }
  }

  function obtenerNombreOrigen(origen) {
    const v = String(origen || '').toUpperCase();
    const mapa = {
      PEDIDO_PREPARADO_DEBITO: 'Pago de producto preparado',
      PEDIDO_SOLICITUD_PREPARADA: 'Pago de producto preparado',
      PEDIDO_PREPARADO_DEVOLUCION: 'Devolución de pedido',
      PEDIDO_SOLICITUD_DEVOLUCION: 'Devolución de pedido',
      DEVOLUCION_PEDIDO_SOLICITUD: 'Devolución de pedido',
      BONO_BIENVENIDA: 'Bono de bienvenida',
      RECARGA_MANUAL: 'Recarga manual',
      PRODUCTO_DESTACADO: 'Producto destacado',
      PUBLICACION_DESTACADA: 'Publicación destacada',
      VENTA_PREPARADA_ACREDITADA: 'Venta de producto preparado',
      RETIRO_RESERVA: 'Saldo reservado para retiro',
      RETIRO_REINTEGRO: 'Reintegro de retiro'
    };
    return mapa[v] || (origen || 'Movimiento');
  }

  function obtenerIconoMovimiento(esDebito) {
    return esDebito
      ? 'bi-arrow-down-right-circle-fill ev-wallet-mov-icon ev-wallet-mov-icon--debito'
      : 'bi-arrow-up-right-circle-fill ev-wallet-mov-icon ev-wallet-mov-icon--credito';
  }

  function actualizarQRDesdeSelect() {
    if (!refs.recargaTipo) return;
    const tipo = (refs.recargaTipo.value || '').toLowerCase();

    if (!tipo) {
      if (refs.qrCard) refs.qrCard.classList.add('d-none');
      return;
    }

    const cfg = QR_CONFIG[tipo] || QR_CONFIG['yape'];

    if (refs.qrCard) refs.qrCard.classList.remove('d-none');
    if (refs.qrImg) refs.qrImg.src = cfg.img;
    if (refs.qrTitle) refs.qrTitle.textContent = cfg.title;
    if (refs.qrText) refs.qrText.textContent = cfg.text;
  }

  function inicializarQR() {
    if (!refs.recargaTipo || !refs.qrImg || !refs.qrTitle || !refs.qrText || !refs.qrCard) return;

    if (refs.recargaTipo.dataset.evWalletQrHooked === '1') {
      actualizarQRDesdeSelect();
      return;
    }

    refs.recargaTipo.dataset.evWalletQrHooked = '1';
    refs.recargaTipo.addEventListener('change', actualizarQRDesdeSelect);
    actualizarQRDesdeSelect();
  }

  function resetModalRecarga() {
    if (!refs.recargaForm) return;

    refs.recargaForm.reset();
    if (refs.recargaCodigo) refs.recargaCodigo.value = '';
    if (refs.recargaModo) refs.recargaModo.value = 'crear';

    if (refs.modalRecargaTitulo) {
      const enModal = !!refs.modalRecargaTitulo.closest('.modal');
      refs.modalRecargaTitulo.innerHTML = enModal
        ? '<i class="bi bi-plus-circle me-2"></i> Recargar saldo'
        : 'Registrar recarga';
    }

    if (refs.btnEnviarRecarga) {
      refs.btnEnviarRecarga.textContent = 'Guardar';
    }

    if (refs.recargaImagenHelp) {
      refs.recargaImagenHelp.textContent = 'Sube una foto clara del voucher o comprobante de la recarga.';
    }

    if (refs.recargaAlertaSubsanacion) refs.recargaAlertaSubsanacion.classList.add('d-none');
    if (refs.recargaAlertaSubsanacionTexto) {
      refs.recargaAlertaSubsanacionTexto.textContent = 'Corrige los datos observados por soporte y vuelve a enviarla.';
    }

    if (refs.qrCard) refs.qrCard.classList.add('d-none');
  }

  function abrirModalNuevaRecarga() {
    if (!recargasDisponibles()) {
      swalInfo('Las recargas no están disponibles para tu comunidad en este momento.');
      return;
    }
    resetModalRecarga();
    actualizarQRDesdeSelect();
  }

  function abrirModalSubsanar(id) {
    if (!recargasDisponibles()) {
      swalInfo('Las recargas no están disponibles para tu comunidad en este momento.');
      return;
    }
    const rec = (state.misRecargas || []).find((x) => String(x.id) === String(id));
    if (!rec) {
      swalErr('No se pudo cargar la recarga observada.');
      return;
    }

    resetModalRecarga();

    if (refs.recargaCodigo) refs.recargaCodigo.value = String(rec.id || '');
    if (refs.recargaModo) refs.recargaModo.value = 'subsanar';
    if (refs.recargaMonto) refs.recargaMonto.value = rec.monto || '';
    if (refs.recargaTipo) refs.recargaTipo.value = String(rec.metodo || '').toLowerCase();
    if (refs.recargaOperacion) refs.recargaOperacion.value = rec.id_operacion || '';

    if (refs.modalRecargaTitulo) {
      const enModal = !!refs.modalRecargaTitulo.closest('.modal');
      refs.modalRecargaTitulo.innerHTML = enModal
        ? '<i class="bi bi-pencil-square me-2"></i> Subsanar recarga observada'
        : 'Subsanar recarga observada';
    }

    if (refs.btnEnviarRecarga) {
      refs.btnEnviarRecarga.textContent = 'Reenviar recarga';
    }

    if (refs.recargaImagenHelp) {
      refs.recargaImagenHelp.textContent = 'Sube un nuevo comprobante si necesitas corregirlo. Si no cambió, puedes dejar este campo vacío.';
    }

    if (refs.recargaAlertaSubsanacion) refs.recargaAlertaSubsanacion.classList.remove('d-none');
    if (refs.recargaAlertaSubsanacionTexto) {
      refs.recargaAlertaSubsanacionTexto.textContent =
        (rec.comentario_soporte || 'Corrige los datos observados por soporte y vuelve a enviarla.');
    }

    actualizarQRDesdeSelect();

    const modalEl = document.getElementById('modalRecargarSaldo');
    if (modalEl && window.bootstrap?.Modal) {
      const mi = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
      mi.show();
    } else {
      const panel = refs.recargaForm?.closest('.ev-wallet-recharge-form-card') || refs.recargaForm;
      if (panel) {
        window.requestAnimationFrame(() => panel.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      }
      window.setTimeout(() => refs.recargaTipo?.focus(), 220);
    }
  }

  async function cargarSaldo() {
    if (!refs.saldo) return;

    const url = `${BASE}/api/billetera/saldo`;

    try {
      const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'include' });
      const json = await leerRespuestaSeguro(resp);

      if (await manejarAuthEspecial(resp, json)) return;
      if (!resp.ok || !json.ok) return;

      const saldo = Number(json.saldo_actual ?? json.saldo ?? 0);
      state.saldoActual = saldo;
      state.saldoCargado = true;
      refs.saldo.textContent = formatearMonto(saldo);

      const compraPendiente = leerCompraPreparadaPendiente();
      if (compraPendiente && String(compraPendiente.etapa || '') === 'recarga_requerida') {
        const faltante = redondearMonto(Math.max(0, Number(compraPendiente.monto_requerido || 0) - saldo));
        actualizarCompraPreparadaPendiente({ saldo_actual: saldo, monto_faltante: faltante });
        aplicarContextoCompraPendienteRecarga();
      }

      window.setTimeout(() => { evaluarCompraPendienteParaContinuar(); }, 40);
      return saldo;

    } catch (err) {
      error('Excepción al cargar saldo:', err);
    }
  }

  function renderizarMovimientos(lista) {
    const items = Array.isArray(lista) ? lista : [];
    actualizarContadores({ movimientos: items.length });
    if (!refs.movimientos || !refs.emptyState) return;

    if (!items.length) {
      refs.emptyState.classList.remove('d-none');
      refs.movimientos.classList.add('d-none');
      refs.movimientos.innerHTML = '';
      return;
    }

    refs.emptyState.classList.add('d-none');
    refs.movimientos.classList.remove('d-none');

    const cards = items.map((m) => {
      const tipoRaw = (m.tipo_movimiento || m.tipo || '').toUpperCase();
      const esDebito = (tipoRaw === 'D' || tipoRaw === 'CARGO');
      const signo = esDebito ? '-' : '+';

      const claseMonto = esDebito ? 'ev-wallet-monto--debito' : 'ev-wallet-monto--credito';
      const claseCard = esDebito ? 'ev-wallet-mov-card--debito' : 'ev-wallet-mov-card--credito';
      const iconClass = obtenerIconoMovimiento(esDebito);

      const desc = (m.descripcion || 'Movimiento en billetera');
      const origen = obtenerNombreOrigen(m.origen || '');
      const refTxt = m.codigo_referencia ? `Ref. ${String(m.codigo_referencia)}` : 'Sin referencia';

      const monto = (typeof m.monto !== 'undefined') ? m.monto : 0;

      const saldoDespues = (typeof m.saldo_despues !== 'undefined' && m.saldo_despues !== null)
        ? formatearMonto(m.saldo_despues)
        : '—';

      const fechaTxt = m.fecha || 'Fecha no disponible';

      return `
        <article class="ev-wallet-mov-card ${claseCard}">
          <div class="ev-wallet-mov-icon-wrap">
            <i class="${iconClass}"></i>
          </div>

          <div class="ev-wallet-mov-main">
            <div class="ev-wallet-mov-titulo">${esc(desc)}</div>

            <div class="ev-wallet-mov-meta">
              <span class="ev-wallet-mov-chip">
                <i class="bi bi-tag"></i>
                <span>${esc(origen)}</span>
              </span>
              <span class="ev-wallet-mov-chip">
                <i class="bi bi-hash"></i>
                <span>${esc(refTxt)}</span>
              </span>
              <span class="ev-wallet-mov-chip">
                <i class="bi bi-clock"></i>
                <span>${esc(fechaTxt)}</span>
              </span>
            </div>
          </div>

          <div class="ev-wallet-mov-side">
            <div class="ev-wallet-mov-monto ${claseMonto}">
              ${signo} ${formatearMonto(monto)}
            </div>
            <span class="ev-wallet-mov-saldo-label">Saldo después</span>
            <div class="ev-wallet-mov-saldo">${esc(saldoDespues)}</div>
          </div>
        </article>
      `;
    }).join('');

    refs.movimientos.innerHTML = `<div class="ev-wallet-mov-list">${cards}</div>`;
  }

  function badgeEstadoRecarga(estado) {
    const e = String(estado || '').toLowerCase();
    const map = {
      pendiente: 'badge rounded-pill text-bg-warning',
      observada: 'badge rounded-pill text-bg-info',
      aprobada: 'badge rounded-pill text-bg-success',
      rechazada: 'badge rounded-pill text-bg-danger'
    };
    return map[e] || 'badge rounded-pill text-bg-secondary';
  }

  function leerRecargaDestinoNotificacion() {
    try {
      const raw = sessionStorage.getItem(NOTIF_WALLET_TARGET_KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      const codigoRecarga = Number(data?.codigo_recarga || 0);
      const createdAt = Number(data?.created_at || 0);
      const age = Date.now() - createdAt;
      if (codigoRecarga <= 0 || age < 0 || age > 5 * 60 * 1000) {
        sessionStorage.removeItem(NOTIF_WALLET_TARGET_KEY);
        return null;
      }
      return { codigo_recarga: codigoRecarga };
    } catch (_) {
      return null;
    }
  }

  function enfocarRecargaDestinoNotificacion() {
    const pending = leerRecargaDestinoNotificacion();
    if (!pending) return false;
    const row = document.querySelector(`[data-ev-recarga-id="${Number(pending.codigo_recarga)}"]`);
    if (!row) return false;

    sessionStorage.removeItem(NOTIF_WALLET_TARGET_KEY);
    row.classList.add('is-notification-target');
    row.setAttribute('tabindex', '-1');
    window.requestAnimationFrame(() => {
      row.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
      try { row.focus({ preventScroll: true }); } catch (_) {}
    });
    window.setTimeout(() => row.classList.remove('is-notification-target'), 5200);
    return true;
  }

  function renderizarRecargas(items) {
    state.misRecargas = Array.isArray(items) ? items : [];
    actualizarContadores({ recargas: state.misRecargas.length });
    if (!refs.recargasEmpty || !refs.recargasTable) return;

    if (!state.misRecargas.length) {
      refs.recargasEmpty.classList.remove('d-none');
      refs.recargasTable.classList.add('d-none');
      refs.recargasTable.innerHTML = '';
      return;
    }

    refs.recargasEmpty.classList.add('d-none');
    refs.recargasTable.classList.remove('d-none');

    const rows = state.misRecargas.map((r) => {
      const est = String(r.estado || '').toLowerCase();
      const comentario = (r.comentario_soporte || '').trim();
      const puedeSubsanar = recargasDisponibles() && (est === 'observada');

      const comentarioHtml = (est === 'observada' || est === 'rechazada')
        ? `
          <tr>
            <td colspan="5" class="p-3 ev-wallet-recarga-observacion">
              <div class="mt-1 p-3 rounded bg-light border">
                <div class="fw-semibold small mb-1">
                  <i class="bi bi-chat-left-text me-1"></i> Mensaje de soporte
                </div>
                <div class="small text-muted">${comentario ? esc(comentario) : '—'}</div>
                ${puedeSubsanar ? `
                  <div class="mt-2">
                    <button type="button"
                            class="btn btn-sm btn-ev-orange"
                            data-ev-action="subsanar-recarga"
                            data-id="${esc(r.id)}">
                      <i class="bi bi-pencil-square me-1"></i> Subsanar recarga
                    </button>
                  </div>
                ` : `
                  <div class="small text-muted mt-1">
                    Esta recarga ya no puede reenviarse desde esta vista.
                  </div>
                `}
              </div>
            </td>
          </tr>
        `
        : '';

      return `
        <tr data-ev-recarga-id="${esc(r.id)}">
          <td data-label="Fecha">
            <div class="small fw-semibold">${esc(r.fecha || '—')}</div>
            <div class="small text-muted">${esc(r.hora || '')}</div>
          </td>
          <td data-label="Monto" class="text-end fw-semibold">${formatearMonto(r.monto || 0)}</td>
          <td data-label="Método" class="text-center">${esc(String(r.metodo || '').toUpperCase())}</td>
          <td data-label="ID operación"><span class="ev-mono small">${esc(r.id_operacion || '—')}</span></td>
          <td data-label="Estado" class="text-center"><span class="${badgeEstadoRecarga(est)}">${esc(est)}</span></td>
        </tr>
        ${comentarioHtml}
      `;
    }).join('');

    refs.recargasTable.innerHTML = `
      <div class="table-responsive ev-wallet-table-shell">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Fecha</th>
              <th class="text-end">Monto</th>
              <th class="text-center">Método</th>
              <th>ID operación</th>
              <th class="text-center">Estado</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    `;
    window.setTimeout(enfocarRecargaDestinoNotificacion, 90);
    window.setTimeout(() => { evaluarCompraPendienteParaContinuar(); }, 60);
  }

  async function cargarMisRecargas() {
    if (!refs.recargasTable && !refs.totalRecargas) return;

    const url = `${BASE}/api/recargas/mis?limit=20`;

    try {
      const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'include' });
      const json = await leerRespuestaSeguro(resp);

      if (await manejarAuthEspecial(resp, json)) return;

      if (!resp.ok || !json.ok) {
        renderizarRecargas([]);
        return;
      }

      renderizarRecargas(json.data || []);

    } catch (e) {
      error('Excepción al cargar mis recargas:', e);
      renderizarRecargas([]);
    }
  }

  async function cargarMovimientos() {
    if (!refs.movimientos && !refs.totalMovimientos) return;

    const url = `${BASE}/api/billetera/movimientos`;

    try {
      const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'include' });
      const json = await leerRespuestaSeguro(resp);

      if (await manejarAuthEspecial(resp, json)) return;

      if (!resp.ok || !json.ok) {
        renderizarMovimientos([]);
        return;
      }

      const lista = json.data || json.movimientos || [];
      renderizarMovimientos(lista);

    } catch (err) {
      error('Excepción al cargar movimientos:', err);
      renderizarMovimientos([]);
    }
  }

  function fechaHoraPE(valor) {
    if (!valor) return '—';
    const d = new Date(String(valor).replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return String(valor);
    return d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  }

  function fechaPagoPE(valor) {
    if (!valor) return '—';
    const d = new Date(`${String(valor)}T12:00:00`);
    if (Number.isNaN(d.getTime())) return String(valor);
    return d.toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric', month: 'long' });
  }

  function estadoRetiroTexto(estado) {
    const e = String(estado || '').toLowerCase();
    return ({
      solicitado: 'Solicitado',
      programado: 'Programado',
      pagado: 'Pagado',
      observado: 'Observado',
      cancelado: 'Cancelado',
      sin_saldo: 'Sin saldo para liquidar'
    })[e] || '—';
  }

  function mostrarMensajeRetiro(texto, tipo = '') {
    if (!refs.retiroMensaje) return;
    refs.retiroMensaje.classList.toggle('d-none', !texto);
    refs.retiroMensaje.classList.remove('is-success', 'is-warning');
    if (tipo) refs.retiroMensaje.classList.add(`is-${tipo}`);
    refs.retiroMensaje.textContent = texto || '';
  }

  function renderizarHistorialRetiros(items) {
    if (!refs.retirosHistorial) return;
    const lista = Array.isArray(items) ? items : [];
    if (!lista.length) {
      refs.retirosHistorial.innerHTML = `
        <div class="ev-wallet-empty ev-wallet-empty--compact">
          <i class="bi bi-cash-stack"></i>
          <div><strong>Aún no tienes retiros.</strong><span>Cuando solicites uno, aparecerá aquí.</span></div>
        </div>`;
      return;
    }

    const rows = lista.map((r) => {
      const estado = String(r.estado || '').toLowerCase();
      const monto = (r.monto_final !== null && r.monto_final !== undefined) ? r.monto_final : r.monto_estimado;
      return `
        <tr>
          <td><strong>${esc(r.codigo || '—')}</strong><div class="small text-muted">${esc(r.jornada_nombre || '')}</div></td>
          <td>${esc(fechaHoraPE(r.fecha_solicitud))}</td>
          <td><strong>${esc(formatearMonto(monto || 0))}</strong><div class="small text-muted">${r.fecha_pago ? `Pagado: ${esc(fechaHoraPE(r.fecha_pago))}` : `Pago: ${esc(fechaPagoPE(r.fecha_pago_programada))}`}</div></td>
          <td><span class="ev-wallet-withdraw-state ev-wallet-withdraw-state--${esc(estado)}">${esc(estadoRetiroTexto(estado))}</span>${r.observacion ? `<div class="small text-muted mt-1">${esc(r.observacion)}</div>` : ''}${r.comprobante_path ? `<div class="small mt-1"><a href="${esc(BASE+'/'+String(r.comprobante_path).replace(/^\/+/,''))}" target="_blank" rel="noopener">Ver comprobante</a></div>` : ''}</td>
        </tr>`;
    }).join('');

    refs.retirosHistorial.innerHTML = `
      <div class="table-responsive ev-wallet-withdraw-table">
        <table class="table align-middle">
          <thead><tr><th>Retiro</th><th>Solicitud</th><th>Monto</th><th>Estado</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>`;
  }

  function renderizarRetiros(data) {
    if (!retirosDisponibles()) return;
    const d = data || {};
    state.retiro = d;
    const saldo = Number(d.saldo_actual || 0);
    const enRetiro = Number(d.saldo_en_retiro || 0);
    const corte = d.corte_actual || null;
    const solicitud = d.solicitud_actual || null;
    const cuenta = d.cuenta || null;
    const titular = d.titular_usuario || {};

    if (refs.retiroSaldo) refs.retiroSaldo.textContent = formatearMonto(saldo);
    if (refs.retiroEnProceso) refs.retiroEnProceso.textContent = formatearMonto(enRetiro);

    if (refs.retiroTitularNombre) refs.retiroTitularNombre.textContent = titular.nombre || cuenta?.titular_nombre || '—';
    if (refs.retiroTitularDocumento) refs.retiroTitularDocumento.textContent = `Documento: ${titular.documento || cuenta?.titular_documento || '—'}`;

    if (corte) {
      if (refs.retiroProximoPago) refs.retiroProximoPago.textContent = fechaPagoPE(corte.fecha_pago_programada);
      if (refs.retiroCorteDetalle) refs.retiroCorteDetalle.textContent = `Corte: ${fechaHoraPE(corte.corte_inicio)} hasta ${fechaHoraPE(corte.corte_fin)}.`;
      if (refs.retiroEstadoCorte) {
        refs.retiroEstadoCorte.textContent = solicitud ? 'Retiro solicitado' : 'Corte abierto';
        refs.retiroEstadoCorte.classList.toggle('is-open', !solicitud);
        refs.retiroEstadoCorte.classList.toggle('is-requested', !!solicitud);
      }
    } else {
      if (refs.retiroProximoPago) refs.retiroProximoPago.textContent = 'Sin corte abierto';
      if (refs.retiroCorteDetalle) refs.retiroCorteDetalle.textContent = 'El próximo corte se habilitará según la programación de EV.';
      if (refs.retiroEstadoCorte) {
        refs.retiroEstadoCorte.textContent = 'Corte cerrado';
        refs.retiroEstadoCorte.classList.remove('is-open', 'is-requested');
      }
    }

    if (cuenta) {
      if (refs.retiroCuentaResumen) refs.retiroCuentaResumen.textContent = `${cuenta.banco} · ${cuenta.numero_cuenta}`;
      if (refs.retiroCuentaEstado) {
        const estado = String(cuenta.estado || '').toLowerCase();
        refs.retiroCuentaEstado.textContent = estado === 'validada'
          ? 'Cuenta validada por Administrador EV.'
          : estado === 'observada'
            ? `Cuenta observada${cuenta.observacion ? `: ${cuenta.observacion}` : '.'}`
            : 'Cuenta pendiente de validación por Administrador EV.';
      }
      if (refs.btnCuentaRetiro) refs.btnCuentaRetiro.textContent = 'Editar cuenta';
      if (refs.retiroBanco) refs.retiroBanco.value = cuenta.banco || '';
      if (refs.retiroTipoCuenta) refs.retiroTipoCuenta.value = cuenta.tipo_cuenta || '';
      if (refs.retiroNumeroCuenta) refs.retiroNumeroCuenta.value = cuenta.numero_cuenta || '';
      if (refs.retiroCci) refs.retiroCci.value = cuenta.cci || '';
      if (refs.retiroDeclara) refs.retiroDeclara.checked = !!cuenta.declaracion_titularidad;
      if (refs.retiroCuentaObservacion) {
        const tieneObs = String(cuenta.estado || '').toLowerCase() === 'observada' && !!cuenta.observacion;
        refs.retiroCuentaObservacion.classList.toggle('d-none', !tieneObs);
        refs.retiroCuentaObservacion.textContent = tieneObs ? cuenta.observacion : '';
      }
    } else {
      if (refs.retiroCuentaResumen) refs.retiroCuentaResumen.textContent = 'Aún no registrada';
      if (refs.retiroCuentaEstado) refs.retiroCuentaEstado.textContent = 'Registra una cuenta bancaria a tu nombre.';
      if (refs.btnCuentaRetiro) refs.btnCuentaRetiro.textContent = 'Registrar cuenta';
      if (refs.formCuentaRetiro) refs.formCuentaRetiro.reset();
      if (refs.retiroCuentaObservacion) refs.retiroCuentaObservacion.classList.add('d-none');
    }

    const estadoSolicitud = String(solicitud?.estado || '').toLowerCase();
    const bloqueaCuenta = !!d.cuenta_bloqueada_por_retiro || ['solicitado', 'programado', 'observado'].includes(estadoSolicitud);
    if (refs.btnCuentaRetiro) {
      refs.btnCuentaRetiro.disabled = bloqueaCuenta;
      refs.btnCuentaRetiro.title = bloqueaCuenta ? 'No puedes cambiar la cuenta mientras este retiro esté pendiente.' : '';
    }

    if (refs.btnRetirarSaldo) {
      refs.btnRetirarSaldo.disabled = !d.puede_solicitar;
      refs.btnRetirarSaldo.textContent = solicitud ? 'Retiro solicitado' : 'Retirar saldo';
    }

    if (solicitud) {
      mostrarMensajeRetiro(`Tu solicitud ya está registrada. EV calculará el monto final con todo el saldo liquidable al cierre y conservará ${formatearMonto(solicitud.saldo_minimo || 20)} en tu billetera.`, 'success');
    } else if (!cuenta) {
      mostrarMensajeRetiro('Para retirar saldo, primero registra una cuenta bancaria a tu nombre.', 'warning');
    } else if (String(cuenta.estado || '').toLowerCase() === 'pendiente') {
      mostrarMensajeRetiro('Tu cuenta bancaria está pendiente de validación por el Administrador EV. El retiro de saldo estará disponible cuando la cuenta sea validada.', 'warning');
    } else if (String(cuenta.estado || '').toLowerCase() === 'observada') {
      mostrarMensajeRetiro('Corrige la cuenta bancaria observada y vuelve a enviarla para validación.', 'warning');
    } else if (!corte) {
      mostrarMensajeRetiro('En este momento no hay una ventana de retiro abierta.', '');
    } else if (!d.puede_solicitar) {
      mostrarMensajeRetiro(`El retiro se habilita cuando tu saldo disponible es mayor a ${formatearMonto(corte.saldo_minimo || 20)}.`, '');
    } else {
      mostrarMensajeRetiro(`Puedes solicitar el retiro una sola vez en este corte. El monto final se calculará al cierre y siempre quedarán ${formatearMonto(corte.saldo_minimo || 20)} en tu billetera.`, '');
    }

    renderizarHistorialRetiros(d.retiros || []);
  }

  async function cargarRetiros() {
    if (!retirosDisponibles() || !refs.retiroSaldo) return;
    try {
      const resp = await fetch(`${BASE}/api/retiros/resumen`, {
        method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'include'
      });
      const json = await leerRespuestaSeguro(resp);
      if (await manejarAuthEspecial(resp, json)) return;
      if (!resp.ok || !json.ok) {
        if (refs.retiroEstadoCorte) refs.retiroEstadoCorte.textContent = 'No disponible';
        if (refs.retiroProximoPago) refs.retiroProximoPago.textContent = '—';
        if (refs.retiroCorteDetalle) refs.retiroCorteDetalle.textContent = 'No fue posible consultar la ventana de retiro.';
        mostrarMensajeRetiro(json.mensaje || 'No se pudo cargar la información de retiros.', 'warning');
        return;
      }
      renderizarRetiros(json.data || {});
    } catch (e) {
      error('Excepción al cargar retiros:', e);
      if (refs.retiroEstadoCorte) refs.retiroEstadoCorte.textContent = 'No disponible';
      if (refs.retiroProximoPago) refs.retiroProximoPago.textContent = '—';
      if (refs.retiroCorteDetalle) refs.retiroCorteDetalle.textContent = 'No fue posible consultar la ventana de retiro.';
      mostrarMensajeRetiro('No se pudo cargar la información de retiros.', 'warning');
    }
  }

  function reglasBancosRetiro() {
    const reglas = walletConfig().bancosRetiro;
    return (reglas && typeof reglas === 'object') ? reglas : {};
  }

  function reglaBancoRetiro(banco) {
    return reglasBancosRetiro()[String(banco || '').trim()] || null;
  }

  function longitudesCuentaRetiro(banco, tipo) {
    const regla = reglaBancoRetiro(banco);
    const arr = regla?.cuenta_longitudes?.[String(tipo || '').trim()] || [];
    return [...new Set((Array.isArray(arr) ? arr : []).map(Number).filter((n) => Number.isInteger(n) && n > 0))].sort((a, b) => a - b);
  }

  function textoLongitudesCuentaRetiro(longitudes) {
    const vals = [...longitudes];
    if (!vals.length) return 'la longitud definida por el banco';
    if (vals.length === 1) return `${vals[0]} dígitos`;
    const ultimo = vals.pop();
    return `${vals.join(', ')} o ${ultimo} dígitos`;
  }

  function limpiarEstadoCampo(el, errorEl) {
    if (el) el.classList.remove('is-invalid');
    if (errorEl) errorEl.textContent = '';
  }

  function marcarCampoInvalido(el, errorEl, mensaje) {
    if (el) el.classList.add('is-invalid');
    if (errorEl) errorEl.textContent = mensaje || '';
  }

  function actualizarAyudaCuentaRetiro() {
    const banco = String(refs.retiroBanco?.value || '').trim();
    const tipo = String(refs.retiroTipoCuenta?.value || '').trim();
    const longitudes = longitudesCuentaRetiro(banco, tipo);
    const max = longitudes.length ? Math.max(...longitudes) : 20;
    if (refs.retiroNumeroCuenta) refs.retiroNumeroCuenta.maxLength = max;
    if (refs.retiroNumeroCuentaHelp) {
      refs.retiroNumeroCuentaHelp.textContent = banco && tipo && longitudes.length
        ? `Para ${banco}, esta cuenta debe tener ${textoLongitudesCuentaRetiro(longitudes)}.`
        : 'Selecciona el banco y tipo de cuenta para validar la longitud.';
    }
    const codigo = String(reglaBancoRetiro(banco)?.codigo_cci || '');
    if (refs.retiroCciHelp) {
      refs.retiroCciHelp.textContent = codigo
        ? `20 dígitos. Para ${banco}, el CCI debe comenzar con ${codigo}.`
        : 'Ingresa los 20 dígitos, sin espacios ni guiones.';
    }
  }

  function validarFormularioCuentaRetiro({ mostrarErrores = true } = {}) {
    const banco = String(refs.retiroBanco?.value || '').trim();
    const tipo = String(refs.retiroTipoCuenta?.value || '').trim();
    const numero = String(refs.retiroNumeroCuenta?.value || '').trim();
    const cci = String(refs.retiroCci?.value || '').trim();
    const declara = !!refs.retiroDeclara?.checked;
    const regla = reglaBancoRetiro(banco);

    limpiarEstadoCampo(refs.retiroBanco, refs.retiroBancoError);
    limpiarEstadoCampo(refs.retiroTipoCuenta, refs.retiroTipoCuentaError);
    limpiarEstadoCampo(refs.retiroNumeroCuenta, refs.retiroNumeroCuentaError);
    limpiarEstadoCampo(refs.retiroCci, refs.retiroCciError);

    let primerCampo = null;
    let mensaje = '';
    const invalidar = (el, errorEl, msg) => {
      if (mostrarErrores) marcarCampoInvalido(el, errorEl, msg);
      if (!primerCampo) { primerCampo = el; mensaje = msg; }
    };

    if (!regla) {
      invalidar(refs.retiroBanco, refs.retiroBancoError, 'Selecciona un banco de la lista.');
    }
    if (!['ahorros', 'corriente'].includes(tipo)) {
      if (!primerCampo) { primerCampo = refs.retiroTipoCuenta; mensaje = 'Selecciona el tipo de cuenta.'; }
      if (mostrarErrores) marcarCampoInvalido(refs.retiroTipoCuenta, refs.retiroTipoCuentaError, 'Selecciona el tipo de cuenta.');
    }

    if (!/^\d+$/.test(numero)) {
      invalidar(refs.retiroNumeroCuenta, refs.retiroNumeroCuentaError, 'El número de cuenta debe contener solo dígitos.');
    } else if (/^(\d)\1+$/.test(numero)) {
      invalidar(refs.retiroNumeroCuenta, refs.retiroNumeroCuentaError, 'Revisa el número de cuenta ingresado.');
    } else if (regla && ['ahorros', 'corriente'].includes(tipo)) {
      const longitudes = longitudesCuentaRetiro(banco, tipo);
      if (!longitudes.includes(numero.length)) {
        invalidar(
          refs.retiroNumeroCuenta,
          refs.retiroNumeroCuentaError,
          `La cuenta ${tipo === 'ahorros' ? 'de ahorros' : 'corriente'} de ${banco} debe tener ${textoLongitudesCuentaRetiro(longitudes)}.`
        );
      }
    }

    if (!/^\d{20}$/.test(cci)) {
      invalidar(refs.retiroCci, refs.retiroCciError, 'El CCI debe contener exactamente 20 dígitos.');
    } else if (/^(\d)\1{19}$/.test(cci)) {
      invalidar(refs.retiroCci, refs.retiroCciError, 'Revisa el CCI ingresado.');
    } else if (regla && cci.slice(0, 3) !== String(regla.codigo_cci || '')) {
      invalidar(refs.retiroCci, refs.retiroCciError, `El CCI de ${banco} debe comenzar con ${String(regla.codigo_cci || '')}.`);
    }

    if (!declara && !primerCampo) {
      primerCampo = refs.retiroDeclara;
      mensaje = 'Confirma que la cuenta bancaria se encuentra a tu nombre.';
    }

    return { ok: !primerCampo, primerCampo, mensaje, banco, tipo, numero, cci, declara };
  }

  function sanitizarDigitosCuenta(el, maximo = 20) {
    if (!el) return;
    const limpio = String(el.value || '').replace(/\D+/g, '').slice(0, maximo);
    if (el.value !== limpio) el.value = limpio;
  }

  function validarNumeroCuentaRetiro() {
    const banco = String(refs.retiroBanco?.value || '').trim();
    const tipo = String(refs.retiroTipoCuenta?.value || '').trim();
    const numero = String(refs.retiroNumeroCuenta?.value || '').trim();
    limpiarEstadoCampo(refs.retiroNumeroCuenta, refs.retiroNumeroCuentaError);
    if (!numero) return;
    if (!/^\d+$/.test(numero) || /^(\d)\1+$/.test(numero)) {
      marcarCampoInvalido(refs.retiroNumeroCuenta, refs.retiroNumeroCuentaError, 'Revisa el número de cuenta ingresado.');
      return;
    }
    const regla = reglaBancoRetiro(banco);
    if (!regla || !['ahorros', 'corriente'].includes(tipo)) return;
    const longitudes = longitudesCuentaRetiro(banco, tipo);
    if (!longitudes.includes(numero.length)) {
      marcarCampoInvalido(
        refs.retiroNumeroCuenta,
        refs.retiroNumeroCuentaError,
        `La cuenta ${tipo === 'ahorros' ? 'de ahorros' : 'corriente'} de ${banco} debe tener ${textoLongitudesCuentaRetiro(longitudes)}.`
      );
    }
  }

  function validarCciRetiro() {
    const banco = String(refs.retiroBanco?.value || '').trim();
    const cci = String(refs.retiroCci?.value || '').trim();
    limpiarEstadoCampo(refs.retiroCci, refs.retiroCciError);
    if (!cci) return;
    if (!/^\d{20}$/.test(cci)) {
      marcarCampoInvalido(refs.retiroCci, refs.retiroCciError, 'El CCI debe contener exactamente 20 dígitos.');
      return;
    }
    if (/^(\d)\1{19}$/.test(cci)) {
      marcarCampoInvalido(refs.retiroCci, refs.retiroCciError, 'Revisa el CCI ingresado.');
      return;
    }
    const regla = reglaBancoRetiro(banco);
    if (regla && cci.slice(0, 3) !== String(regla.codigo_cci || '')) {
      marcarCampoInvalido(refs.retiroCci, refs.retiroCciError, `El CCI de ${banco} debe comenzar con ${String(regla.codigo_cci || '')}.`);
    }
  }

  function abrirCuentaRetiro() {
    if (!retirosDisponibles()) return;
    const cuenta = state.retiro?.cuenta || null;
    if (!cuenta && refs.formCuentaRetiro) refs.formCuentaRetiro.reset();
    if (cuenta) {
      if (refs.retiroBanco) refs.retiroBanco.value = cuenta.banco || '';
      if (refs.retiroTipoCuenta) refs.retiroTipoCuenta.value = cuenta.tipo_cuenta || '';
      if (refs.retiroNumeroCuenta) refs.retiroNumeroCuenta.value = cuenta.numero_cuenta || '';
      if (refs.retiroCci) refs.retiroCci.value = cuenta.cci || '';
      if (refs.retiroDeclara) refs.retiroDeclara.checked = !!cuenta.declaracion_titularidad;
    }
    actualizarAyudaCuentaRetiro();
    validarFormularioCuentaRetiro({ mostrarErrores: false });
    const modal = document.getElementById('modalCuentaRetiro');
    if (modal && window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(modal).show();
  }

  async function guardarCuentaRetiro() {
    if (!retirosDisponibles() || !refs.btnGuardarCuentaRetiro) return;
    sanitizarDigitosCuenta(refs.retiroNumeroCuenta, Number(refs.retiroNumeroCuenta?.maxLength || 20));
    sanitizarDigitosCuenta(refs.retiroCci, 20);
    const validacion = validarFormularioCuentaRetiro({ mostrarErrores: true });
    if (!validacion.ok) {
      validacion.primerCampo?.focus?.();
      return swalInfo(validacion.mensaje || 'Revisa los datos bancarios ingresados.');
    }
    const { banco, tipo, numero, cci } = validacion;

    refs.btnGuardarCuentaRetiro.disabled = true;
    try {
      const resp = await fetch(`${BASE}/api/retiros/cuenta`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-EV-CSRF': walletCsrf() },
        body: JSON.stringify({ banco, tipo_cuenta: tipo, numero_cuenta: numero, cci, declara_titularidad: true })
      });
      const json = await leerRespuestaSeguro(resp);
      if (await manejarAuthEspecial(resp, json)) return;
      if (!resp.ok || !json.ok) return swalErr(json.mensaje || 'No se pudo registrar la cuenta bancaria.');

      const modal = document.getElementById('modalCuentaRetiro');
      if (modal && window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(modal).hide();
      await swalOk(json.mensaje || 'Cuenta bancaria registrada.');
      await cargarRetiros();
    } catch (e) {
      error('Excepción al guardar cuenta de retiro:', e);
      swalErr('No se pudo conectar con el servicio de retiros.');
    } finally {
      refs.btnGuardarCuentaRetiro.disabled = false;
    }
  }

  async function solicitarRetiroSaldo() {
    if (!retirosDisponibles() || !state.retiro?.puede_solicitar) return;
    const corte = state.retiro.corte_actual || {};
    const saldo = Number(state.retiro.saldo_actual || 0);
    const minimo = Number(corte.saldo_minimo || 20);
    const estimado = Math.max(0, saldo - minimo);

    const confirmacion = window.Swal?.fire
      ? await swalFireEV({
          icon: 'question',
          title: 'Solicitar retiro',
          html: `Tu retiro queda registrado para este corte.<br><br><strong>Monto estimado ahora: ${esc(formatearMonto(estimado))}</strong><br><span style="font-size:.86rem;color:#6B7280">El monto final se recalculará al cierre e incluirá las ventas liberadas dentro del mismo corte. EV mantendrá ${esc(formatearMonto(minimo))} en tu billetera.</span>`,
          showCancelButton: true,
          confirmButtonText: 'Aceptar',
          cancelButtonText: 'Cancelar'
        })
      : { isConfirmed: confirm('¿Confirmas la solicitud de retiro para este corte?') };
    if (!confirmacion?.isConfirmed) return;

    refs.btnRetirarSaldo.disabled = true;
    try {
      const resp = await fetch(`${BASE}/api/retiros/solicitar`, {
        method: 'POST', credentials: 'include',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-EV-CSRF': walletCsrf() },
        body: '{}'
      });
      const json = await leerRespuestaSeguro(resp);
      if (await manejarAuthEspecial(resp, json)) return;
      if (!resp.ok || !json.ok) return swalErr(json.mensaje || 'No se pudo registrar el retiro.');
      await swalOk(json.mensaje || 'Retiro registrado.');
      await Promise.all([cargarRetiros(), cargarSaldo(), cargarMovimientos()]);
    } catch (e) {
      error('Excepción al solicitar retiro:', e);
      swalErr('No se pudo conectar con el servicio de retiros.');
    } finally {
      if (refs.btnRetirarSaldo) refs.btnRetirarSaldo.disabled = !(state.retiro?.puede_solicitar);
    }
  }

  function engancharEventosRetiro() {
    if (!retirosDisponibles()) return;
    if (refs.btnCuentaRetiro && refs.btnCuentaRetiro.dataset.evHooked !== '1') {
      refs.btnCuentaRetiro.dataset.evHooked = '1';
      refs.btnCuentaRetiro.addEventListener('click', abrirCuentaRetiro);
    }
    if (refs.btnGuardarCuentaRetiro && refs.btnGuardarCuentaRetiro.dataset.evHooked !== '1') {
      refs.btnGuardarCuentaRetiro.dataset.evHooked = '1';
      refs.btnGuardarCuentaRetiro.addEventListener('click', guardarCuentaRetiro);
    }
    if (refs.btnRetirarSaldo && refs.btnRetirarSaldo.dataset.evHooked !== '1') {
      refs.btnRetirarSaldo.dataset.evHooked = '1';
      refs.btnRetirarSaldo.addEventListener('click', solicitarRetiroSaldo);
    }
    if (refs.retiroBanco && refs.retiroBanco.dataset.evBankValidation !== '1') {
      refs.retiroBanco.dataset.evBankValidation = '1';
      refs.retiroBanco.addEventListener('change', () => {
        limpiarEstadoCampo(refs.retiroBanco, refs.retiroBancoError);
        actualizarAyudaCuentaRetiro();
        validarFormularioCuentaRetiro({ mostrarErrores: false });
      });
    }
    if (refs.retiroTipoCuenta && refs.retiroTipoCuenta.dataset.evBankValidation !== '1') {
      refs.retiroTipoCuenta.dataset.evBankValidation = '1';
      refs.retiroTipoCuenta.addEventListener('change', () => {
        limpiarEstadoCampo(refs.retiroTipoCuenta, refs.retiroTipoCuentaError);
        actualizarAyudaCuentaRetiro();
        validarFormularioCuentaRetiro({ mostrarErrores: false });
      });
    }
    if (refs.retiroNumeroCuenta && refs.retiroNumeroCuenta.dataset.evDigits !== '1') {
      refs.retiroNumeroCuenta.dataset.evDigits = '1';
      refs.retiroNumeroCuenta.addEventListener('input', () => {
        sanitizarDigitosCuenta(refs.retiroNumeroCuenta, Number(refs.retiroNumeroCuenta.maxLength || 20));
        limpiarEstadoCampo(refs.retiroNumeroCuenta, refs.retiroNumeroCuentaError);
      });
      refs.retiroNumeroCuenta.addEventListener('blur', validarNumeroCuentaRetiro);
    }
    if (refs.retiroCci && refs.retiroCci.dataset.evDigits !== '1') {
      refs.retiroCci.dataset.evDigits = '1';
      refs.retiroCci.addEventListener('input', () => {
        sanitizarDigitosCuenta(refs.retiroCci, 20);
        limpiarEstadoCampo(refs.retiroCci, refs.retiroCciError);
      });
      refs.retiroCci.addEventListener('blur', validarCciRetiro);
    }
    actualizarAyudaCuentaRetiro();
  }

  async function enviarRecarga() {
    if (!recargasDisponibles()) {
      swalInfo('Las recargas no están disponibles para tu comunidad en este momento.');
      return;
    }
    if (!refs.recargaForm || !refs.btnEnviarRecarga) return;

    const modo = (refs.recargaModo?.value || 'crear').toLowerCase();
    const codigoRecarga = (refs.recargaCodigo?.value || '').trim();
    const tipo = (refs.recargaTipo?.value || '').toLowerCase();
    const monto = Number(refs.recargaMonto?.value || 0);
    const oper = (refs.recargaOperacion?.value || '').trim();
    const file = refs.recargaImagen?.files?.[0] || null;

    if (!tipo) return swalInfo('Selecciona el tipo de billetera (Yape o Plin).');
    if (!monto || monto <= 0) return swalInfo('Ingresa un monto válido mayor a 0.');
    if (!oper || oper.length < 4) return swalInfo('Ingresa un ID de operación válido (mínimo 4 caracteres).');

    if (modo === 'crear' && !file) {
      return swalInfo('Sube una imagen del comprobante.');
    }

    const fd = new FormData(refs.recargaForm);
    fd.set('recarga_tipo', tipo);
    fd.set('recarga_monto', String(monto));
    fd.set('recarga_operacion', oper);

    const esSubsanacion = (modo === 'subsanar' && codigoRecarga !== '');
    const url = esSubsanacion
      ? `${BASE}/api/recargas/${encodeURIComponent(codigoRecarga)}/subsanar`
      : `${BASE}/api/recargas/registrar`;

    const confirmarTexto = esSubsanacion
      ? 'Se reenviará tu recarga corregida para una nueva validación por Soporte.'
      : 'Se registrará tu recarga y quedará pendiente de validación por Soporte.';

    const confirmar = await (window.Swal?.fire
      ? swalFireEV({
          icon: 'question',
          title: esSubsanacion ? 'Reenviar recarga' : 'Guardar recarga',
          text: confirmarTexto,
          showCancelButton: true,
          confirmButtonText: esSubsanacion ? 'Sí, reenviar' : 'Aceptar',
          cancelButtonText: 'Cancelar'
        })
      : Promise.resolve({ isConfirmed: confirm('¿Confirmas la operación?') })
    );

    if (!confirmar || !confirmar.isConfirmed) return;

    refs.btnEnviarRecarga.disabled = true;
    refs.btnEnviarRecarga.classList.add('saving');

    try {
      const resp = await fetch(url, {
        method: 'POST',
        body: fd,
        credentials: 'include'
      });

      const data = await leerRespuestaSeguro(resp);

      if (await manejarAuthEspecial(resp, data)) return;

      if (resp.status === 409) {
        swalErr(data.mensaje || 'No se pudo procesar la recarga.');
        return;
      }

      if (!resp.ok || !data.ok) {
        swalErr(data.mensaje || data.error || 'No se pudo registrar la recarga.');
        return;
      }

      const compraPendiente = leerCompraPreparadaPendiente();
      const esRecargaDeCompra = !esSubsanacion
        && compraPendiente
        && String(compraPendiente.etapa || '') === 'recarga_requerida';

      if (esRecargaDeCompra) {
        actualizarCompraPreparadaPendiente({
          etapa: 'validacion_soporte',
          codigo_recarga: Number(data.id || 0),
          monto_recarga: monto
        });
        await swalFireEV({
          icon: 'info',
          title: 'Pago en validación',
          text: 'Soporte EV está validando tu comprobante. Cuando la recarga sea aprobada y el saldo esté disponible, te avisaremos para que continúes exactamente con la compra que dejaste pendiente.',
          confirmButtonText: 'Aceptar',
          showCancelButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false
        });
      } else {
        await swalOk(data.mensaje || (esSubsanacion ? 'Recarga corregida y reenviada.' : 'Recarga registrada.'));
      }

      resetModalRecarga();
      if (esRecargaDeCompra) {
        aplicarContextoCompraPendienteRecarga();
        iniciarPollingCompraPendiente();
      }

      const modalEl = document.getElementById('modalRecargarSaldo');
      if (modalEl && window.bootstrap?.Modal) {
        const mi = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
        mi.hide();
      }

      cargarSaldo();
      cargarMovimientos();
      cargarMisRecargas();

    } catch (e) {
      error(e);
      swalErr('No se pudo conectar con el servicio de recargas.');
    } finally {
      refs.btnEnviarRecarga.disabled = false;
      refs.btnEnviarRecarga.classList.remove('saving');
    }
  }

  function engancharEventosRecarga() {
    if (refs.btnEnviarRecarga && refs.btnEnviarRecarga.dataset.evHooked !== '1') {
      refs.btnEnviarRecarga.dataset.evHooked = '1';
      refs.btnEnviarRecarga.addEventListener('click', (e) => {
        e.preventDefault();
        enviarRecarga();
      });
    }

    if (refs.btnLimpiarRecarga && refs.btnLimpiarRecarga.dataset.evHooked !== '1') {
      refs.btnLimpiarRecarga.dataset.evHooked = '1';
      refs.btnLimpiarRecarga.addEventListener('click', (e) => {
        e.preventDefault();
        resetModalRecarga();
        actualizarQRDesdeSelect();
        aplicarContextoCompraPendienteRecarga();
        refs.recargaTipo?.focus();
      });
    }

    if (refs.btnAbrirNuevaRecarga && refs.btnAbrirNuevaRecarga.dataset.evHooked !== '1') {
      refs.btnAbrirNuevaRecarga.dataset.evHooked = '1';
      refs.btnAbrirNuevaRecarga.addEventListener('click', () => {
        abrirModalNuevaRecarga();
      });
    }

    if (refs.recargasTable && refs.recargasTable.dataset.evHooked !== '1') {
      refs.recargasTable.dataset.evHooked = '1';
      refs.recargasTable.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-ev-action="subsanar-recarga"]');
        if (!btn) return;
        abrirModalSubsanar(btn.getAttribute('data-id'));
      });
    }

    const modalEl = document.getElementById('modalRecargarSaldo');
    if (modalEl && !modalEl.dataset.evResetHooked) {
      modalEl.dataset.evResetHooked = '1';
      modalEl.addEventListener('hidden.bs.modal', () => {
        resetModalRecarga();
      });
    }
  }

  function navegarWallet(ruta) {
    const destino = String(ruta || '').trim();
    if (!destino) return;
    if (window.EVNav && typeof window.EVNav.loadPage === 'function') {
      window.EVNav.loadPage(destino, { pushState: true, replaceState: false });
      return;
    }
    window.location.href = `${BASE}/MenuPrincipal?ev_goto=${encodeURIComponent(destino)}`;
  }

  function engancharNavegacionWallet() {
    document.querySelectorAll('.ev-wallet-wrapper [data-ev-wallet-route]').forEach((el) => {
      if (el.dataset.evWalletNavHooked === '1') return;
      el.dataset.evWalletNavHooked = '1';
      el.addEventListener('click', (e) => {
        const ruta = el.getAttribute('data-ev-wallet-route');
        if (!ruta) return;
        e.preventDefault();
        navegarWallet(ruta);
      });
    });
  }

  function refrescarAhora(payload) {
    if (!document.querySelector('.ev-wallet-wrapper')) return;
    log('Refrescando billetera por evento:', payload?.motivo || '(sin motivo)');
    const section = walletSection();
    if (section === 'resumen') {
      cargarSaldo();
      cargarMovimientos();
      cargarMisRecargas();
    } else if (section === 'recargar') {
      cargarSaldo();
      cargarMisRecargas();
    } else if (section === 'retirar') {
      cargarRetiros();
    }
  }

  function escucharEventosRefresh() {
    if (window.__EV_WALLET_REFRESH_BOUND__ === true) return;
    window.__EV_WALLET_REFRESH_BOUND__ = true;

    window.addEventListener('EV_BILLETERA_REFRESH', (e) => {
      refrescarAhora(e.detail || {});
    });

    document.addEventListener('EV_BILLETERA_REFRESH', (e) => {
      refrescarAhora(e.detail || {});
    });

    if (bc) {
      bc.onmessage = (ev) => {
        const msg = ev?.data || {};
        if (msg.type === 'EV_BILLETERA_REFRESH') refrescarAhora(msg.detail || {});
      };
    }

    window.addEventListener('storage', (ev) => {
      if (ev.key !== 'EV_BILLETERA_REFRESH') return;
      try {
        const payload = ev.newValue ? JSON.parse(ev.newValue) : null;
        if (payload) refrescarAhora(payload);
      } catch (_) {}
    });

    window.addEventListener('focus', () => {
      if (document.querySelector('.ev-wallet-wrapper')) {
        refrescarAhora({ motivo: 'window_focus' });
      }
    });
  }

  function inicializarVista() {
    if (!capturarRefs()) {
      detenerPollingCompraPendiente();
      return;
    }

    state.retiro = null;
    state.saldoCargado = false;
    const section = walletSection();
    log(`Vista Billetera/${section} detectada. BASE_URL:`, BASE || '(vacía)');

    if (refs.saldo) refs.saldo.textContent = formatearMonto(0);
    actualizarContadores({ movimientos: 0, recargas: 0 });

    if (refs.emptyState) refs.emptyState.classList.remove('d-none');
    if (refs.movimientos) {
      refs.movimientos.classList.add('d-none');
      refs.movimientos.innerHTML = '';
    }

    resetModalRecarga();
    if (section === 'recargar') aplicarContextoCompraPendienteRecarga();
    if (section === 'resumen') {
      cargarSaldo();
      cargarMovimientos();
      cargarMisRecargas();
    } else if (section === 'recargar') {
      cargarSaldo();
      cargarMisRecargas();
    } else if (section === 'retirar') {
      cargarRetiros();
    }

    if (refs.btnRefrescarRecargas && refs.btnRefrescarRecargas.dataset.evWalletRefreshHooked !== '1') {
      refs.btnRefrescarRecargas.dataset.evWalletRefreshHooked = '1';
      refs.btnRefrescarRecargas.addEventListener('click', async () => {
        if (refs.btnRefrescarRecargas.disabled) return;

        refs.btnRefrescarRecargas.disabled = true;
        refs.btnRefrescarRecargas.classList.add('is-loading');

        try {
          if (walletSection() === 'recargar') {
            await Promise.all([cargarSaldo(), cargarMisRecargas()]);
          } else {
            refrescarAhora({ motivo: 'manual_refresh' });
          }
        } finally {
          refs.btnRefrescarRecargas.disabled = false;
          refs.btnRefrescarRecargas.classList.remove('is-loading');
        }
      });
    }

    engancharNavegacionWallet();
    inicializarQR();
    engancharEventosRecarga();
    engancharEventosRetiro();
    escucharEventosRefresh();
    iniciarPollingCompraPendiente();
  }

  document.addEventListener('DOMContentLoaded', () => {
    inicializarVista();
  });

  document.addEventListener('ev:content-loaded', inicializarVista);

  window.EVWallet = { init: inicializarVista };
})();