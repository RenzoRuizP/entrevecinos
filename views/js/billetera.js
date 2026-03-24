// views/js/billetera.js
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  const LOG_PREFIX = '[BILLETERA]';

  const BC_NAME = 'EV_CHANNEL';
  let bc = null;
  try { bc = ('BroadcastChannel' in window) ? new BroadcastChannel(BC_NAME) : null; } catch (_) { bc = null; }

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
  };

  let state = {
    misRecargas: []
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

    return true;
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
      PEDIDO_PREPARADO_DEBITO: 'Solicitud preparada',
      PEDIDO_PREPARADO_DEVOLUCION: 'Devolución',
      BONO_BIENVENIDA: 'Bono de bienvenida',
      RECARGA_MANUAL: 'Recarga manual',
      PRODUCTO_DESTACADO: 'Producto destacado',
      PUBLICACION_DESTACADA: 'Publicación destacada'
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
      refs.modalRecargaTitulo.innerHTML = '<i class="bi bi-plus-circle me-2"></i> Recargar saldo';
    }

    if (refs.btnEnviarRecarga) {
      refs.btnEnviarRecarga.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Confirmar recarga';
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
    resetModalRecarga();
    actualizarQRDesdeSelect();
  }

  function abrirModalSubsanar(id) {
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
      refs.modalRecargaTitulo.innerHTML = '<i class="bi bi-pencil-square me-2"></i> Subsanar recarga observada';
    }

    if (refs.btnEnviarRecarga) {
      refs.btnEnviarRecarga.innerHTML = '<i class="bi bi-send-check me-1"></i> Reenviar recarga';
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

      const saldo = (json.saldo_actual ?? json.saldo ?? 0);
      refs.saldo.textContent = formatearMonto(saldo);

    } catch (err) {
      error('Excepción al cargar saldo:', err);
    }
  }

  function renderizarMovimientos(lista) {
    if (!refs.movimientos || !refs.emptyState) return;

    const items = Array.isArray(lista) ? lista : [];
    actualizarContadores({ movimientos: items.length });

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

  function renderizarRecargas(items) {
    if (!refs.recargasEmpty || !refs.recargasTable) return;

    state.misRecargas = Array.isArray(items) ? items : [];
    actualizarContadores({ recargas: state.misRecargas.length });

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
      const puedeSubsanar = (est === 'observada');

      const comentarioHtml = (est === 'observada' || est === 'rechazada')
        ? `
          <tr>
            <td colspan="5" class="p-3">
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
        <tr>
          <td>
            <div class="small fw-semibold">${esc(r.fecha || '—')}</div>
            <div class="small text-muted">${esc(r.hora || '')}</div>
          </td>
          <td class="text-end fw-semibold">${formatearMonto(r.monto || 0)}</td>
          <td class="text-center">${esc(String(r.metodo || '').toUpperCase())}</td>
          <td><span class="ev-mono small">${esc(r.id_operacion || '—')}</span></td>
          <td class="text-center"><span class="${badgeEstadoRecarga(est)}">${esc(est)}</span></td>
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
  }

  async function cargarMisRecargas() {
    if (!refs.recargasTable) return;

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
    if (!refs.movimientos) return;

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

  async function enviarRecarga() {
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
          title: esSubsanacion ? 'Reenviar recarga' : 'Confirmar recarga',
          text: confirmarTexto,
          showCancelButton: true,
          confirmButtonText: esSubsanacion ? 'Sí, reenviar' : 'Sí, confirmar',
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

      swalOk(data.mensaje || (esSubsanacion ? 'Recarga corregida y reenviada.' : 'Recarga registrada.'));

      resetModalRecarga();

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

  function refrescarAhora(payload) {
    if (!document.querySelector('.ev-wallet-wrapper')) return;
    log('Refrescando billetera por evento:', payload?.motivo || '(sin motivo)');
    cargarSaldo();
    cargarMovimientos();
    cargarMisRecargas();
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
        cargarSaldo();
        cargarMovimientos();
        cargarMisRecargas();
      }
    });
  }

  function inicializarVista() {
    if (!capturarRefs()) return;

    log('Vista Mi Billetera detectada. BASE_URL:', BASE || '(vacía)');

    if (refs.saldo) refs.saldo.textContent = formatearMonto(0);
    actualizarContadores({ movimientos: 0, recargas: 0 });

    if (refs.emptyState) refs.emptyState.classList.remove('d-none');
    if (refs.movimientos) {
      refs.movimientos.classList.add('d-none');
      refs.movimientos.innerHTML = '';
    }

    resetModalRecarga();
    cargarSaldo();
    cargarMovimientos();
    cargarMisRecargas();

    if (refs.btnRefrescarRecargas && refs.btnRefrescarRecargas.dataset.evWalletRefreshHooked !== '1') {
      refs.btnRefrescarRecargas.dataset.evWalletRefreshHooked = '1';
      refs.btnRefrescarRecargas.addEventListener('click', () => {
        cargarMisRecargas();
      });
    }

    inicializarQR();
    engancharEventosRecarga();
    escucharEventosRefresh();
  }

  document.addEventListener('DOMContentLoaded', () => {
    inicializarVista();
  });

  const observer = new MutationObserver(() => {
    const wrapperActual = document.querySelector('.ev-wallet-wrapper');
    if (wrapperActual && wrapperActual !== refs.wrapper) {
      inicializarVista();
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });

  window.EVWallet = { init: inicializarVista };
})();