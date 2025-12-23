// views/js/billetera.js
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  const LOG_PREFIX = '[BILLETERA]';

  // BroadcastChannel (cross-tab)
  const BC_NAME = 'EV_CHANNEL';
  let bc = null;
  try { bc = ('BroadcastChannel' in window) ? new BroadcastChannel(BC_NAME) : null; } catch (_) { bc = null; }

  let refs = {
    wrapper: null,
    saldo: null,
    emptyState: null,
    movimientos: null,

    // Refs para el modal de recarga
    recargaForm: null,
    recargaTipo: null,
    recargaMonto: null,
    recargaOperacion: null,
    recargaImagen: null,
    btnEnviarRecarga: null,

    qrImg: null,
    qrTitle: null,
    qrText: null,
    qrCard: null,
  };

  // Configuración de imágenes y textos para QR
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
  function warn() { if (window.console && console.warn) console.warn(LOG_PREFIX, ...arguments); }
  function error() { if (window.console && console.error) console.error(LOG_PREFIX, ...arguments); }

  function capturarRefs() {
    refs.wrapper = document.querySelector('.ev-wallet-wrapper');
    if (!refs.wrapper) return false;

    refs.saldo = document.getElementById('ev_wallet_saldo');
    refs.emptyState = document.getElementById('ev_wallet_empty_state');
    refs.movimientos = document.getElementById('ev_wallet_movimientos');

    // Modal recarga
    refs.recargaForm = document.getElementById('formRecargaSaldo');
    refs.recargaTipo = document.getElementById('recarga_tipo');
    refs.recargaMonto = document.getElementById('recarga_monto');
    refs.recargaOperacion = document.getElementById('recarga_operacion');
    refs.recargaImagen = document.getElementById('recarga_imagen');
    refs.btnEnviarRecarga = document.getElementById('btnEnviarRecarga');

    refs.qrImg = document.getElementById('ev_qr_img');
    refs.qrTitle = document.getElementById('ev_qr_title');
    refs.qrText = document.getElementById('ev_qr_text');
    refs.qrCard = document.getElementById('ev_qr_card');

    return true;
  }

  function formatearMonto(monto) {
    const n = Number(monto || 0);
    return 'S/ ' + n.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // ==========================================================
  // SWEETALERT2 – WRAPPER TEMA EV (FIX: botón Entendido con estilo)
  // ==========================================================
  function swalFireEV(opts) {
    if (!window.Swal?.fire) return null;

    // Clase base: aseguramos que SIEMPRE tenga el estilo EV,
    // incluso si por algún motivo no toma "customClass.confirmButton".
    const baseCustomClass = {
      popup: 'ev-swal-popup',
      title: 'ev-swal-title',
      htmlContainer: 'ev-swal-html',
      icon: 'ev-swal-icon',
      // IMPORTANTE: le metemos clases EV completas al botón
      confirmButton: 'ev-swal-confirm btn-ev-orange',
      cancelButton: 'ev-swal-cancel btn-ev-outline'
    };

    // Merge seguro de customClass
    const userCC = (opts && opts.customClass) ? opts.customClass : {};
    const mergedCustomClass = Object.assign({}, baseCustomClass, userCC);

    const base = {
      title: 'Entre Vecinos',
      customClass: mergedCustomClass,
      buttonsStyling: false,
      focusConfirm: true,
      // Por defecto NO mostramos cancel
      showCancelButton: false,
      // Mantiene el look consistente
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

  async function leerRespuestaSeguro(resp) {
    const ct = (resp.headers.get('content-type') || '').toLowerCase();
    if (ct.includes('application/json')) {
      return await resp.json().catch(() => ({}));
    }
    const txt = await resp.text().catch(() => '');
    try { return JSON.parse(txt); } catch (_) {}
    return { ok: false, mensaje: txt || 'Respuesta no válida del servidor.' };
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

  async function cargarSaldo() {
    if (!refs.saldo) return;

    const url = `${BASE}/api/billetera/saldo`;

    try {
      const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'include' });
      if (resp.status === 401) return;

      if (!resp.ok) {
        const txt = await resp.text().catch(() => '');
        error('Error HTTP al obtener saldo:', resp.status, txt);
        return;
      }

      const json = await resp.json().catch(() => ({}));
      if (!json.ok) return;

      const saldo = (json.saldo_actual ?? json.saldo ?? 0);
      refs.saldo.textContent = formatearMonto(saldo);

    } catch (err) {
      error('Excepción al cargar saldo:', err);
    }
  }

  function renderizarMovimientos(lista) {
    if (!refs.movimientos || !refs.emptyState) return;

    if (!lista || !lista.length) {
      refs.emptyState.classList.remove('d-none');
      refs.movimientos.classList.add('d-none');
      refs.movimientos.innerHTML = '';
      return;
    }

    refs.emptyState.classList.add('d-none');
    refs.movimientos.classList.remove('d-none');

    const filas = lista.map((m) => {
      const tipoRaw = (m.tipo_movimiento || m.tipo || '').toUpperCase();
      const esDebito = (tipoRaw === 'D' || tipoRaw === 'CARGO');
      const signo = esDebito ? '-' : '+';

      const claseMonto = esDebito ? 'ev-wallet-monto--debito' : 'ev-wallet-monto--credito';

      const iconClass = esDebito
        ? 'bi-arrow-down-right-circle-fill ev-wallet-mov-icon ev-wallet-mov-icon--debito'
        : 'bi-arrow-up-right-circle-fill ev-wallet-mov-icon ev-wallet-mov-icon--credito';

      const desc = (m.descripcion || 'Movimiento en billetera');
      const origen = (m.origen || '');
      const ref = m.codigo_referencia ? ` · Ref: ${String(m.codigo_referencia)}` : '';

      const monto = (typeof m.monto !== 'undefined') ? m.monto : 0;

      const saldoDespues = (typeof m.saldo_despues !== 'undefined' && m.saldo_despues !== null)
        ? formatearMonto(m.saldo_despues)
        : '';

      return `
        <tr>
          <td>
            <div class="ev-wallet-mov-concepto">
              <div class="ev-wallet-mov-header">
                <i class="${iconClass}"></i>
                <span class="ev-wallet-mov-titulo">${desc}</span>
              </div>
              <div class="ev-wallet-mov-detalle text-muted small">
                ${origen}${ref}
              </div>
            </div>
          </td>
          <td class="text-end">
            <span class="ev-wallet-mov-monto ${claseMonto}">
              ${signo} ${formatearMonto(monto)}
            </span>
          </td>
          <td class="text-end">
            <span class="ev-wallet-mov-saldo text-muted">
              ${saldoDespues}
            </span>
          </td>
        </tr>
      `;
    }).join('');

    refs.movimientos.innerHTML = `
      <div class="table-responsive ev-wallet-table-wrapper">
        <table class="table align-middle ev-wallet-table">
          <thead>
            <tr>
              <th>Movimiento</th>
              <th class="text-end">Monto</th>
              <th class="text-end">Saldo después</th>
            </tr>
          </thead>
          <tbody>${filas}</tbody>
        </table>
      </div>
    `;
  }

  async function cargarMovimientos() {
    if (!refs.movimientos) return;

    const url = `${BASE}/api/billetera/movimientos`;

    try {
      const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'include' });

      if (resp.status === 401) {
        renderizarMovimientos([]);
        return;
      }

      if (!resp.ok) {
        renderizarMovimientos([]);
        return;
      }

      const json = await resp.json().catch(() => ({}));
      if (!json.ok) {
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

  async function enviarRecarga() {
    if (!refs.recargaForm || !refs.btnEnviarRecarga) return;

    const tipo = (refs.recargaTipo?.value || '').toLowerCase();
    const monto = Number(refs.recargaMonto?.value || 0);
    const oper = (refs.recargaOperacion?.value || '').trim();
    const file = refs.recargaImagen?.files?.[0];

    if (!tipo) return swalInfo('Selecciona el tipo de billetera (Yape o Plin).');
    if (!monto || monto <= 0) return swalInfo('Ingresa un monto válido mayor a 0.');
    if (!oper || oper.length < 4) return swalInfo('Ingresa un ID de operación válido (mínimo 4 caracteres).');
    if (!file) return swalInfo('Sube una imagen del comprobante.');

    const fd = new FormData(refs.recargaForm);
    fd.set('recarga_tipo', tipo);
    fd.set('recarga_monto', String(monto));
    fd.set('recarga_operacion', oper);

    const url = `${BASE}/api/recargas/registrar`;

    const confirmar = await (window.Swal?.fire
      ? swalFireEV({
          icon: 'question',
          title: 'Confirmar recarga',
          text: 'Se registrará tu recarga y quedará pendiente de validación por Soporte.',
          showCancelButton: true,
          confirmButtonText: 'Sí, confirmar',
          cancelButtonText: 'Cancelar'
        })
      : Promise.resolve({ isConfirmed: confirm('¿Confirmas registrar tu recarga?') })
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

      if (resp.status === 401) {
        swalErr(data.mensaje || 'Tu sesión expiró. Vuelve a iniciar sesión.');
        return;
      }

      if (resp.status === 409) {
        swalErr(data.mensaje || 'Ya registraste una recarga con ese ID de operación.');
        return;
      }

      if (!resp.ok || !data.ok) {
        swalErr(data.mensaje || data.error || 'No se pudo registrar la recarga.');
        return;
      }

      swalOk(data.mensaje || 'Recarga registrada.');

      refs.recargaForm.reset();
      if (refs.qrCard) refs.qrCard.classList.add('d-none');

      const modalEl = document.getElementById('modalRecargarSaldo');
      if (modalEl && window.bootstrap?.Modal) {
        const mi = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
        mi.hide();
      }

      // No actualizar saldo aquí (queda pendiente)
      cargarMovimientos();

    } catch (e) {
      error(e);
      swalErr('No se pudo conectar con el servicio. Verifica el endpoint de recargas.');
    } finally {
      refs.btnEnviarRecarga.disabled = false;
      refs.btnEnviarRecarga.classList.remove('saving');
    }
  }

  function engancharEventosRecarga() {
    if (!refs.btnEnviarRecarga) return;

    if (refs.btnEnviarRecarga.dataset.evHooked === '1') return;
    refs.btnEnviarRecarga.dataset.evHooked = '1';

    refs.btnEnviarRecarga.addEventListener('click', (e) => {
      e.preventDefault();
      enviarRecarga();
    });
  }

  // ===========================
  // REFRESH AUTOMÁTICO (cross-tab)
  // ===========================
  function refrescarAhora(payload) {
    if (!document.querySelector('.ev-wallet-wrapper')) return;
    log('Refrescando billetera por evento:', payload?.motivo || '(sin motivo)');
    cargarSaldo();
    cargarMovimientos();
  }

  function escucharEventosRefresh() {
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
        cargarSaldo();
        cargarMovimientos();
      }
    });
  }

  function inicializarVista() {
    if (!capturarRefs()) return;

    log('Vista Mi Billetera detectada. BASE_URL:', BASE || '(vacía)');

    if (refs.saldo) refs.saldo.textContent = formatearMonto(0);

    if (refs.emptyState) refs.emptyState.classList.remove('d-none');
    if (refs.movimientos) {
      refs.movimientos.classList.add('d-none');
      refs.movimientos.innerHTML = '';
    }

    cargarSaldo();
    cargarMovimientos();

    inicializarQR();
    engancharEventosRecarga();
    escucharEventosRefresh();
  }

  async function cargarVistaParcialBilletera(contentWrapper) {
    const url = `${BASE}/billetera?partial=1`;

    try {
      const resp = await fetch(url, {
        method: 'GET',
        headers: { 'X-Partial': '1', 'Accept': 'text/html' },
        credentials: 'include',
      });

      if (resp.status === 401) {
        if (window.Swal?.fire) {
          swalFireEV({ icon: 'info', title: 'Sesión expirada', text: 'Tu sesión ha expirado. Vuelve a iniciar sesión.' })
            .then(() => window.location.href = `${BASE}/login`);
        } else {
          window.location.href = `${BASE}/login`;
        }
        return;
      }

      if (!resp.ok) {
        if (window.Swal?.fire) swalFireEV({ icon: 'error', title: 'Error', text: 'No se pudo cargar tu billetera. Intenta nuevamente.' });
        return;
      }

      const html = await resp.text();
      contentWrapper.innerHTML = html;
      inicializarVista();

    } catch (err) {
      error('Excepción al cargar billetera:', err);
      if (window.Swal?.fire) {
        swalFireEV({ icon: 'error', title: 'Error de conexión', text: 'No pudimos cargar tu billetera. Revisa tu conexión.' });
      }
    }
  }

  function engancharMenuBilletera() {
    const contentWrapper = document.querySelector('.content-wrapper');
    if (!contentWrapper) return;

    const enlaces = Array.from(document.querySelectorAll('a'));
    const linkBilletera = enlaces.find((a) => ((a.textContent || '').trim().toLowerCase() === 'mi billetera'));

    if (!linkBilletera) return;
    if (linkBilletera.dataset.evWalletHooked === '1') return;

    linkBilletera.dataset.evWalletHooked = '1';
    linkBilletera.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      cargarVistaParcialBilletera(contentWrapper);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    engancharMenuBilletera();
    inicializarVista();
  });

  const observer = new MutationObserver(() => {
    const wrapperActual = document.querySelector('.ev-wallet-wrapper');
    if (wrapperActual && wrapperActual !== refs.wrapper) inicializarVista();
    engancharMenuBilletera();
  });

  observer.observe(document.body, { childList: true, subtree: true });

  window.EVWallet = { init: inicializarVista };
})();
