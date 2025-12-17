// views/js/atenderRecargas.js
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  const LOG_PREFIX = '[ATENDER_RECARGAS]';

  const refs = {
    form: null,
    fEstado: null,
    fRango: null,
    fTexto: null,
    tbody: null,
    lblMeta: null,
    lblPendientes: null,
    lblFooterLeft: null,
    btnPrev: null,
    btnNext: null,
    lblPagina: null,
    btnRefrescar: null,
    btnExportar: null,
    btnVerPendientes: null,
    btnVerObservadas: null,
    btnVerAprobadas: null,
    btnVerRechazadas: null,

    // Modal
    modalEl: null,
    modal: null,
    mUsuario: null,
    mDni: null,
    mResidencia: null,
    mCondominio: null,
    mMonto: null,
    mMetodo: null,
    mOperacion: null,
    mEstadoBadge: null,
    mComentario: null,
    mImagen: null,
    mNoImagen: null,
    btnAprobar: null,
    btnObservar: null,
    btnRechazar: null,
  };

  let state = {
    page: 1,
    size: 10,
    total: 0,
    pendientes: 0,
    items: [],
    seleccionado: null,
  };

  function log() { if (console && console.log) console.log(LOG_PREFIX, ...arguments); }
  function error() { if (console && console.error) console.error(LOG_PREFIX, ...arguments); }

  function swalInfo(msg) {
    if (window.Swal?.fire) return Swal.fire({ icon: 'info', title: 'Entre Vecinos', text: msg });
    alert(msg);
  }
  function swalOk(msg) {
    if (window.Swal?.fire) return Swal.fire({ icon: 'success', title: 'Listo', text: msg, timer: 1400, showConfirmButton: false });
    alert(msg);
  }
  function swalErr(msg) {
    if (window.Swal?.fire) return Swal.fire({ icon: 'error', title: 'Ocurrió un problema', text: msg });
    alert(msg);
  }

  function capturarRefs() {
    refs.form = document.getElementById('formFiltros');
    refs.fEstado = document.getElementById('fEstado');
    refs.fRango = document.getElementById('fRango');
    refs.fTexto = document.getElementById('fTexto');
    refs.tbody = document.getElementById('tbodyRecargas');
    refs.lblMeta = document.getElementById('lblMeta');
    refs.lblPendientes = document.getElementById('lblPendientes');
    refs.lblFooterLeft = document.getElementById('lblFooterLeft');
    refs.btnPrev = document.getElementById('btnPrev');
    refs.btnNext = document.getElementById('btnNext');
    refs.lblPagina = document.getElementById('lblPagina');
    refs.btnRefrescar = document.getElementById('btnRefrescar');
    refs.btnExportar = document.getElementById('btnExportar');
    refs.btnVerPendientes = document.getElementById('btnVerPendientes');
    refs.btnVerObservadas = document.getElementById('btnVerObservadas');
    refs.btnVerAprobadas = document.getElementById('btnVerAprobadas');
    refs.btnVerRechazadas = document.getElementById('btnVerRechazadas');

    refs.modalEl = document.getElementById('modalRecarga');
    refs.mUsuario = document.getElementById('mUsuario');
    refs.mDni = document.getElementById('mDni');
    refs.mResidencia = document.getElementById('mResidencia');
    refs.mCondominio = document.getElementById('mCondominio');
    refs.mMonto = document.getElementById('mMonto');
    refs.mMetodo = document.getElementById('mMetodo');
    refs.mOperacion = document.getElementById('mOperacion');
    refs.mEstadoBadge = document.getElementById('mEstadoBadge');
    refs.mComentario = document.getElementById('mComentario');
    refs.mImagen = document.getElementById('mImagen');
    refs.mNoImagen = document.getElementById('mNoImagen');
    refs.btnAprobar = document.getElementById('btnAprobar');
    refs.btnObservar = document.getElementById('btnObservar');
    refs.btnRechazar = document.getElementById('btnRechazar');

    if (refs.modalEl && window.bootstrap?.Modal) {
      refs.modal = bootstrap.Modal.getOrCreateInstance(refs.modalEl);
    }

    return !!refs.form && !!refs.tbody;
  }

  function formatearMonto(monto) {
    const n = Number(monto || 0);
    return 'S/ ' + n.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
  }

  function badgeEstado(estado) {
    const e = (estado || '').toLowerCase();
    const map = {
      pendiente: 'ev-badge ev-badge-pendiente',
      observada: 'ev-badge ev-badge-observada',
      aprobada: 'ev-badge ev-badge-aprobada',
      rechazada: 'ev-badge ev-badge-rechazada',
    };
    return map[e] || 'ev-badge ev-badge-pendiente';
  }

  function endpointListar() {
    const estado = refs.fEstado?.value || 'pendiente';
    const rango = refs.fRango?.value || '7';
    const q = (refs.fTexto?.value || '').trim();

    const params = new URLSearchParams();
    params.set('estado', estado);
    params.set('rango', rango);
    if (q) params.set('q', q);
    params.set('page', String(state.page));
    params.set('size', String(state.size));

    return `${BASE}/api/soporte/recargas?${params.toString()}`;
  }

  async function leerRespuestaSeguro(resp) {
    const ct = (resp.headers.get('content-type') || '').toLowerCase();
    if (ct.includes('application/json')) return await resp.json().catch(() => ({}));
    const txt = await resp.text().catch(() => '');
    try { return JSON.parse(txt); } catch (_) {}
    return { ok: false, mensaje: txt || 'Respuesta no válida del servidor.' };
  }

  function renderEmpty() {
    refs.tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-4 ev-empty">
          <div class="ev-empty-wrap">
            <i class="bi bi-inbox ev-empty-ico"></i>
            <div class="ev-empty-text">
              No hay solicitudes de recarga para los filtros seleccionados.
            </div>
          </div>
        </td>
      </tr>
    `;
  }

  function renderTabla(items) {
    if (!items || !items.length) {
      renderEmpty();
      return;
    }

    const filas = items.map((r) => {
      const id = r.id;
      const fecha = `${escapeHtml(r.fecha)} ${escapeHtml(r.hora)}`;
      const usuario = escapeHtml(r.usuario_nombre || '—');
      const monto = formatearMonto(r.monto);
      const metodo = escapeHtml((r.metodo || '').toUpperCase());
      const op = escapeHtml(r.id_operacion || '—');
      const est = escapeHtml(r.estado || 'pendiente');

      return `
        <tr>
          <td>${fecha}</td>
          <td>${usuario}</td>
          <td>${monto}</td>
          <td>${metodo}</td>
          <td><span class="ev-mono">${op}</span></td>
          <td><span class="${badgeEstado(est)}">${est}</span></td>
          <td class="text-end">
            <button class="btn ev-btn-light btn-sm" data-ev-action="revisar" data-id="${id}">
              <i class="bi bi-eye me-1"></i> Revisar
            </button>
          </td>
        </tr>
      `;
    }).join('');

    refs.tbody.innerHTML = filas;
  }

  function renderMeta() {
    const total = Number(state.total || 0);
    const page = Number(state.page || 1);
    const size = Number(state.size || 10);

    const shown = state.items?.length || 0;
    refs.lblMeta.textContent = `Mostrando ${shown} registros`;
    refs.lblPendientes.textContent = String(state.pendientes || 0);

    const from = total === 0 ? 0 : ((page - 1) * size + 1);
    const to = total === 0 ? 0 : ((page - 1) * size + shown);

    refs.lblFooterLeft.textContent = `Mostrando ${to} de ${total}`;
    refs.lblPagina.textContent = String(page);

    refs.btnPrev.disabled = (page <= 1);
    refs.btnNext.disabled = (to >= total);
  }

  async function loadList() {
    const url = endpointListar();
    log('GET', url);

    try {
      const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'include' });
      const json = await leerRespuestaSeguro(resp);

      if (resp.status === 401) {
        swalErr(json.mensaje || 'Tu sesión expiró. Vuelve a iniciar sesión.');
        return;
      }

      if (!resp.ok || !json.ok) {
        renderEmpty();
        refs.lblMeta.textContent = 'Mostrando 0 registros';
        return;
      }

      const data = json.data || {};
      state.total = data.total || 0;
      state.page = data.page || 1;
      state.size = data.size || 10;
      state.pendientes = data.pendientes || 0;
      state.items = data.items || [];

      renderTabla(state.items);
      renderMeta();

    } catch (e) {
      error(e);
      renderEmpty();
      swalErr('No se pudo cargar la lista de recargas.');
    }
  }

  function abrirModalById(id) {
    const rec = (state.items || []).find(x => String(x.id) === String(id));
    if (!rec) return;

    state.seleccionado = rec;

    refs.mUsuario.textContent = rec.usuario_nombre || '—';
    refs.mDni.textContent = rec.dni || '—';

    const residencia = `${rec.torre || '—'} · Dpto ${rec.departamento || '—'}`;
    refs.mResidencia.textContent = residencia;
    refs.mCondominio.textContent = rec.condominio || '—';

    refs.mMonto.textContent = formatearMonto(rec.monto);
    refs.mMetodo.textContent = (rec.metodo || '—').toUpperCase();
    refs.mOperacion.textContent = rec.id_operacion || '—';

    const est = (rec.estado || 'pendiente').toLowerCase();
    refs.mEstadoBadge.className = badgeEstado(est);
    refs.mEstadoBadge.textContent = est;

    refs.mComentario.value = '';

    // Imagen
    const path = rec.comprobante_path ? `${BASE}/${String(rec.comprobante_path).replace(/^\/+/, '')}` : '';
    if (path) {
      refs.mImagen.src = path;
      refs.mImagen.classList.remove('d-none');
      refs.mNoImagen.classList.add('d-none');
    } else {
      refs.mImagen.src = '';
      refs.mImagen.classList.add('d-none');
      refs.mNoImagen.classList.remove('d-none');
    }

    refs.modal?.show();
  }

  async function updateEstado(nuevoEstado) {
    if (!state.seleccionado?.id) return;

    const id = state.seleccionado.id;
    const comentario = (refs.mComentario?.value || '').trim();

    if ((nuevoEstado === 'observada' || nuevoEstado === 'rechazada') && comentario.length < 3) {
      swalInfo('Debes ingresar un comentario para Observada o Rechazada.');
      return;
    }

    const url = `${BASE}/api/soporte/recargas/${id}/estado`;
    const fd = new FormData();
    fd.set('estado', nuevoEstado);
    fd.set('comentario', comentario);

    try {
      const resp = await fetch(url, { method: 'POST', body: fd, credentials: 'include' });
      const json = await leerRespuestaSeguro(resp);

      if (resp.status === 401) {
        swalErr(json.mensaje || 'Tu sesión expiró. Vuelve a iniciar sesión.');
        return;
      }

      if (!resp.ok || !json.ok) {
        swalErr(json.mensaje || 'No se pudo actualizar el estado.');
        return;
      }

      swalOk(json.mensaje || 'Estado actualizado.');
      refs.modal?.hide();

      // Recargar lista
      loadList();

    } catch (e) {
      error(e);
      swalErr('No se pudo conectar para actualizar estado.');
    }
  }

  function bindEvents() {
    refs.form.addEventListener('submit', (e) => {
      e.preventDefault();
      state.page = 1;
      loadList();
    });

    refs.btnPrev?.addEventListener('click', () => {
      if (state.page > 1) { state.page -= 1; loadList(); }
    });

    refs.btnNext?.addEventListener('click', () => {
      state.page += 1;
      loadList();
    });

    refs.btnRefrescar?.addEventListener('click', () => loadList());

    // Quick filters
    refs.btnVerPendientes?.addEventListener('click', () => { refs.fEstado.value = 'pendiente'; state.page = 1; loadList(); });
    refs.btnVerObservadas?.addEventListener('click', () => { refs.fEstado.value = 'observada'; state.page = 1; loadList(); });
    refs.btnVerAprobadas?.addEventListener('click', () => { refs.fEstado.value = 'aprobada'; state.page = 1; loadList(); });
    refs.btnVerRechazadas?.addEventListener('click', () => { refs.fEstado.value = 'rechazada'; state.page = 1; loadList(); });

    // Delegación: revisar
    refs.tbody.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-ev-action="revisar"]');
      if (!btn) return;
      abrirModalById(btn.getAttribute('data-id'));
    });

    refs.btnAprobar?.addEventListener('click', () => updateEstado('aprobada'));
    refs.btnObservar?.addEventListener('click', () => updateEstado('observada'));
    refs.btnRechazar?.addEventListener('click', () => updateEstado('rechazada'));
  }

  function init() {
    if (!capturarRefs()) return;
    bindEvents();
    loadList();
  }

  document.addEventListener('DOMContentLoaded', init);

})();
