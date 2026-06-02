// views/js/comunidadGestion.js
// Módulo Comunidad · Gestión institucional de publicaciones
// Modal premium estático: no cierra con backdrop ni con tecla Escape.
(function () {
  'use strict';

  const NS = '__EV_COMUNIDAD_GESTION__';
  if (!window[NS]) window[NS] = { bound: false };
  const shared = window[NS];

  const BASE = String(window.BASE_URL || window.EV_BASE_URL || '').replace(/\/+$/, '');
  if (!BASE) return;

  const $ = (selector, root = document) => root.querySelector(selector);

  function escapeHtml(value) {
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function texto(value, fallback = '—') {
    const valueText = String(value ?? '').trim();
    return valueText || fallback;
  }

  function fecha(value) {
    if (!value) return '—';
    const raw = String(value).replace(' ', 'T');
    const dt = new Date(raw);
    if (Number.isNaN(dt.getTime())) return String(value);
    return new Intl.DateTimeFormat('es-PE', {
      dateStyle: 'medium',
      timeStyle: 'short'
    }).format(dt);
  }

  function aDatetimeLocal(value) {
    if (!value) return '';
    return String(value).replace(' ', 'T').slice(0, 16);
  }

  function tipoLabel(tipo) {
    return ({ comunicado: 'Comunicado', noticia: 'Noticia', evento: 'Evento' })[tipo] || 'Publicación';
  }

  function estadoLabel(estado) {
    return ({
      borrador: 'Borrador',
      publicado: 'Publicado',
      inactivo: 'Inactivo',
      ocultado_moderacion: 'Ocultado'
    })[estado] || texto(estado);
  }

  function prioridadLabel(prioridad) {
    return ({ normal: 'Normal', importante: 'Importante', urgente: 'Urgente' })[prioridad] || texto(prioridad);
  }

  function notify(icon, title, message) {
    if (window.Swal?.fire) {
      return Swal.fire({ icon, title, text: message, confirmButtonColor: '#EA7C12' });
    }
    alert(message);
    return Promise.resolve();
  }

  async function confirmar(message, confirmText, title = 'Confirmar acción') {
    if (!window.Swal?.fire) return window.confirm(message);
    const result = await Swal.fire({
      icon: 'question',
      title,
      text: message,
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#EA7C12',
      reverseButtons: true
    });
    return result.isConfirmed;
  }

  async function requestJSON(url, options = {}) {
    const response = await fetch(url, {
      cache: 'no-store',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers || {})
      },
      ...options
    });

    const payload = await response.json().catch(() => ({}));

    if (response.status === 401) {
      await notify('info', 'Sesión finalizada', payload.mensaje || 'Vuelve a iniciar sesión.');
      window.location.assign(payload.redirect || `${BASE}/login`);
      throw new Error('Sesión finalizada.');
    }

    if (!response.ok || payload.ok === false) {
      throw new Error(payload.mensaje || 'No se pudo procesar la solicitud.');
    }

    return payload;
  }

  function init() {
    const root = $('.ev-com-management');
    if (!root || root.dataset.evComInit === '1') return;
    root.dataset.evComInit = '1';

    const esAdminSistema = root.dataset.esAdminSistema === '1';
    const comunidadVisible = String(root.dataset.comunidadVisible || 'la comunidad');
    const totalColumnas = esAdminSistema ? 6 : 5;
    const state = {
      page: 1,
      size: 10,
      total: 0,
      editando: null,
      loading: false,
      dirty: false,
      suspendDirty: false,
      previewObjectUrl: null
    };

    const form = $('#formComunidadPublicacion', root);
    const tbody = $('#tbodyComunidadPublicaciones', root);
    const codigoInput = $('#codigoPublicacionCom', root);
    const tipoInput = $('#tipoPublicacionCom', root);
    const destinoSelect = $('#destinoCom', root);
    const tipoConjuntoInput = $('#tipoConjuntoCom', root);
    const codigoComunidadInput = $('#codigoComunidadCom', root);
    const camposEvento = $('#camposEventoCom', root);
    const imagenInput = $('#imagenPortadaCom', root);
    const previewWrap = $('#portadaPreviewWrapCom', root);
    const previewImg = $('#portadaPreviewCom', root);
    const btnPublicar = $('#btnPublicarCom', root);
    const btnGuardar = $('#btnGuardarBorradorCom', root);
    const tituloChars = $('#tituloCharsCom', root);
    const resumenChars = $('#resumenCharsCom', root);

    const modalFormularioEl = $('#modalPublicacionCom', root);
    const modalHistorialEl = document.getElementById('modalHistorialCom');
    const modalFormulario = modalFormularioEl && window.bootstrap?.Modal
      ? new bootstrap.Modal(modalFormularioEl, { backdrop: 'static', keyboard: false, focus: true })
      : null;
    const modalHistorial = modalHistorialEl && window.bootstrap?.Modal
      ? new bootstrap.Modal(modalHistorialEl)
      : null;

    if (!form || !tbody || !modalFormulario) return;

    const url = {
      destinos: `${BASE}/api/comunidad/destinos`,
      listar: `${BASE}/api/comunidad/publicaciones`,
      detalle: id => `${BASE}/api/comunidad/publicaciones/${id}`,
      historial: id => `${BASE}/api/comunidad/publicaciones/${id}/historial`,
      crear: `${BASE}/api/comunidad/publicaciones`,
      actualizar: id => `${BASE}/api/comunidad/publicaciones/${id}/actualizar`,
      publicar: id => `${BASE}/api/comunidad/publicaciones/${id}/publicar`,
      desactivar: id => `${BASE}/api/comunidad/publicaciones/${id}/desactivar`
    };

    function markDirty() {
      if (!state.suspendDirty) state.dirty = true;
    }

    function limpiarPreviewTemporal() {
      if (state.previewObjectUrl) {
        URL.revokeObjectURL(state.previewObjectUrl);
        state.previewObjectUrl = null;
      }
    }

    function mostrarEvento() {
      const esEvento = tipoInput.value === 'evento';
      camposEvento.classList.toggle('d-none', !esEvento);
      $('#fechaEventoInicioCom', root).required = esEvento;
      $('#ubicacionEventoCom', root).required = esEvento;

      if (!esEvento) {
        $('#fechaEventoInicioCom', root).value = '';
        $('#fechaEventoFinCom', root).value = '';
        $('#ubicacionEventoCom', root).value = '';
      }
    }

    function actualizarContadores() {
      tituloChars.textContent = String($('#tituloCom', root).value.length);
      resumenChars.textContent = String($('#resumenCom', root).value.length);
    }

    function sincronizarDestino() {
      if (!destinoSelect) return;
      const option = destinoSelect.selectedOptions[0];
      tipoConjuntoInput.value = option?.dataset.tipo || '';
      codigoComunidadInput.value = option?.dataset.codigo || '';
    }

    function limpiarFormulario() {
      state.suspendDirty = true;
      limpiarPreviewTemporal();
      form.reset();
      codigoInput.value = '';
      state.editando = null;
      $('#evComFormTitle', root).textContent = 'Nueva publicación';
      btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar borrador';
      btnPublicar.classList.remove('d-none');
      btnGuardar.disabled = false;
      btnPublicar.disabled = false;
      sincronizarDestino();
      mostrarEvento();
      actualizarContadores();
      previewWrap.hidden = true;
      previewImg.src = '';
      imagenInput.value = '';
      state.dirty = false;
      state.suspendDirty = false;
    }

    function abrirFormulario() {
      modalFormulario.show();
    }

    function cerrarFormularioForzado() {
      state.dirty = false;
      modalFormulario.hide();
    }

    async function solicitarCierreFormulario() {
      if (!state.dirty) {
        modalFormulario.hide();
        return;
      }

      const salir = await confirmar(
        'Los cambios que realizaste no se han guardado. ¿Deseas salir de todas formas?',
        'Sí, salir',
        'Salir sin guardar'
      );

      if (salir) {
        cerrarFormularioForzado();
      }
    }

    async function cargarDestinos() {
      const data = await requestJSON(url.destinos);
      const items = Array.isArray(data.items) ? data.items : [];

      destinoSelect.innerHTML = esAdminSistema
        ? '<option value="">Seleccionar comunidad</option>'
        : '';

      items.forEach(item => {
        const option = document.createElement('option');
        option.value = `${item.tipo_conjunto}:${item.codigo_urbanizacion || item.codigo_condominio}`;
        option.dataset.tipo = item.tipo_conjunto;
        option.dataset.codigo = String(item.codigo_urbanizacion || item.codigo_condominio || '');
        option.textContent = texto(item.nombre_comunidad);
        destinoSelect.appendChild(option);
      });

      if (!esAdminSistema && items.length === 1) destinoSelect.selectedIndex = 0;
      sincronizarDestino();
    }

    function renderCounts(counts = {}) {
      $('#evComCountPublicadas', root).textContent = String(Number(counts.publicadas || 0));
      $('#evComCountBorradores', root).textContent = String(Number(counts.borradores || 0));
      $('#evComCountEventos', root).textContent = String(Number(counts.eventos_proximos || 0));
      $('#evComCountDestacadas', root).textContent = String(Number(counts.destacadas || 0));
    }

    function renderVacio() {
      tbody.innerHTML = `
        <tr><td colspan="${totalColumnas}">
          <div class="ev-com-empty">
            <i class="bi bi-megaphone"></i>
            <div>Aún no existen publicaciones con los filtros seleccionados.</div>
            <small>Crea un comunicado, noticia o evento para comenzar.</small>
          </div>
        </td></tr>`;
    }

    function renderRows(items) {
      if (!items.length) {
        renderVacio();
        return;
      }

      tbody.innerHTML = items.map(item => {
        const id = Number(item.codigo_publicacion);
        const estado = String(item.estado || 'borrador');
        const actions = [
          `<button type="button" class="ev-com-mini-btn" data-action="historial" data-id="${id}"><i class="bi bi-clock-history"></i> Historial</button>`
        ];

        if (estado === 'borrador' || estado === 'publicado') {
          actions.unshift(`<button type="button" class="ev-com-mini-btn" data-action="editar" data-id="${id}"><i class="bi bi-pencil"></i> Editar</button>`);
        }
        if (estado === 'borrador') {
          actions.unshift(`<button type="button" class="ev-com-mini-btn publish" data-action="publicar" data-id="${id}"><i class="bi bi-send-check"></i> Publicar</button>`);
        }
        if (estado === 'publicado') {
          actions.unshift(`<button type="button" class="ev-com-mini-btn off" data-action="desactivar" data-id="${id}"><i class="bi bi-eye-slash"></i> Desactivar</button>`);
        }

        const fechaMostrar = item.tipo_publicacion === 'evento' && item.fecha_evento_inicio
          ? `Evento: ${fecha(item.fecha_evento_inicio)}`
          : fecha(item.fecha_publicacion || item.created_at);

        const comunidadCell = esAdminSistema
          ? `<td>${escapeHtml(texto(item.nombre_comunidad))}</td>`
          : '';

        return `
          <tr>
            <td>
              <span class="ev-com-badge ev-com-badge--${escapeHtml(item.tipo_publicacion)}">${escapeHtml(tipoLabel(item.tipo_publicacion))}</span>
              <div class="ev-com-row-title">${escapeHtml(item.titulo)}</div>
              <div class="ev-com-row-sub">${escapeHtml(item.resumen)}</div>
            </td>
            ${comunidadCell}
            <td><span class="ev-com-badge ev-com-badge--${escapeHtml(item.prioridad)}">${escapeHtml(prioridadLabel(item.prioridad))}</span></td>
            <td><span class="ev-com-badge ev-com-badge--${escapeHtml(estado)}">${escapeHtml(estadoLabel(estado))}</span></td>
            <td>${escapeHtml(fechaMostrar)}</td>
            <td><div class="ev-com-row-actions">${actions.join('')}</div></td>
          </tr>`;
      }).join('');
    }

    function renderPaginacion() {
      const pages = Math.max(1, Math.ceil(state.total / state.size));
      $('#paginaCom', root).textContent = `${state.page} / ${pages}`;
      $('#btnAnteriorCom', root).disabled = state.page <= 1;
      $('#btnSiguienteCom', root).disabled = state.page >= pages;
      const from = state.total === 0 ? 0 : ((state.page - 1) * state.size + 1);
      const to = Math.min(state.total, state.page * state.size);
      $('#evComFooterLeft', root).textContent = `Mostrando ${from} a ${to} de ${state.total}`;
    }

    async function listar() {
      if (state.loading) return;
      state.loading = true;
      tbody.innerHTML = `<tr><td colspan="${totalColumnas}"><div class="ev-com-loading"><span></span> Cargando publicaciones...</div></td></tr>`;

      const query = new URLSearchParams({
        estado: $('#estadoCom', root).value,
        tipo: $('#tipoFiltroCom', root).value,
        q: $('#buscarCom', root).value.trim(),
        page: String(state.page),
        size: String(state.size)
      });

      try {
        const data = await requestJSON(`${url.listar}?${query.toString()}`);
        state.total = Number(data.total || 0);
        renderCounts(data.counts || {});
        renderRows(Array.isArray(data.items) ? data.items : []);
        renderPaginacion();
        $('#evComMeta', root).textContent = state.total === 1
          ? '1 publicación registrada.'
          : `${state.total} publicaciones registradas.`;
      } catch (e) {
        renderVacio();
        await notify('error', 'No se pudo cargar', e.message || 'Error al listar publicaciones.');
      } finally {
        state.loading = false;
      }
    }

    function cargarPortadaExistente(path) {
      limpiarPreviewTemporal();
      if (!path) {
        previewWrap.hidden = true;
        previewImg.src = '';
        return;
      }
      const clean = String(path).replace(/^\/+/, '');
      previewImg.src = `${BASE}/${clean}`;
      previewWrap.hidden = false;
    }

    async function editar(id) {
      try {
        const data = await requestJSON(url.detalle(id));
        const item = data.item || {};
        limpiarFormulario();
        state.suspendDirty = true;
        state.editando = id;
        codigoInput.value = String(id);
        $('#evComFormTitle', root).textContent = 'Editar publicación';
        tipoInput.value = item.tipo_publicacion || 'comunicado';
        $('#prioridadCom', root).value = item.prioridad || 'normal';
        $('#tituloCom', root).value = item.titulo || '';
        $('#resumenCom', root).value = item.resumen || '';
        $('#contenidoCom', root).value = item.contenido || '';
        $('#fechaExpiracionCom', root).value = aDatetimeLocal(item.fecha_expiracion);
        $('#fechaEventoInicioCom', root).value = aDatetimeLocal(item.fecha_evento_inicio);
        $('#fechaEventoFinCom', root).value = aDatetimeLocal(item.fecha_evento_fin);
        $('#ubicacionEventoCom', root).value = item.ubicacion_evento || '';
        $('#destacadoCom', root).checked = Number(item.destacado_dashboard || 0) === 1;

        if (esAdminSistema && destinoSelect) {
          const codigo = item.tipo_conjunto === 'urbanizacion' ? item.codigo_urbanizacion : item.codigo_condominio;
          destinoSelect.value = `${item.tipo_conjunto}:${codigo}`;
          sincronizarDestino();
        }

        mostrarEvento();
        actualizarContadores();
        cargarPortadaExistente(item.imagen_portada);

        if (item.estado === 'publicado') {
          btnGuardar.innerHTML = '<i class="bi bi-check2-circle"></i> Guardar cambios';
          btnPublicar.classList.add('d-none');
        }

        state.dirty = false;
        state.suspendDirty = false;
        abrirFormulario();
      } catch (e) {
        state.suspendDirty = false;
        await notify('error', 'No se pudo abrir', e.message);
      }
    }

    async function guardar(accion) {
      if (!form.reportValidity()) return;
      if (esAdminSistema && !codigoComunidadInput.value) {
        await notify('info', 'Comunidad requerida', 'Selecciona la comunidad destino de esta publicación.');
        return;
      }

      if (accion === 'publicar') {
        const confirmado = await confirmar(
          `El contenido será visible para los vecinos de ${comunidadVisible}.`,
          'Sí, publicar',
          'Publicar contenido'
        );
        if (!confirmado) return;
      }

      const fd = new FormData(form);
      fd.set('destacado_dashboard', $('#destacadoCom', root).checked ? '1' : '0');
      fd.set('accion', accion);
      fd.set('tipo_conjunto', tipoConjuntoInput.value);
      fd.set('codigo_comunidad', codigoComunidadInput.value);

      const editando = Number(codigoInput.value || 0);
      const endpoint = editando > 0 ? url.actualizar(editando) : url.crear;
      const button = accion === 'publicar' ? btnPublicar : btnGuardar;
      const oldHtml = button.innerHTML;
      button.disabled = true;
      button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

      try {
        const data = await requestJSON(endpoint, { method: 'POST', body: fd });
        await notify('success', 'Listo', data.mensaje || 'La publicación fue guardada.');
        cerrarFormularioForzado();
        state.page = 1;
        await listar();
      } catch (e) {
        await notify('error', 'No se pudo guardar', e.message);
      } finally {
        button.disabled = false;
        button.innerHTML = oldHtml;
      }
    }

    async function publicar(id) {
      if (!await confirmar('La publicación será visible para los vecinos de la comunidad.', 'Sí, publicar')) return;
      try {
        const data = await requestJSON(url.publicar(id), { method: 'POST' });
        await notify('success', 'Publicación activa', data.mensaje);
        await listar();
      } catch (e) {
        await notify('error', 'No se pudo publicar', e.message);
      }
    }

    async function desactivar(id) {
      if (!await confirmar('La publicación dejará de mostrarse a los vecinos.', 'Sí, desactivar')) return;
      try {
        const data = await requestJSON(url.desactivar(id), { method: 'POST' });
        await notify('success', 'Publicación desactivada', data.mensaje);
        await listar();
      } catch (e) {
        await notify('error', 'No se pudo desactivar', e.message);
      }
    }

    async function historial(id) {
      try {
        const [detalleData, historyData] = await Promise.all([
          requestJSON(url.detalle(id)),
          requestJSON(url.historial(id))
        ]);
        $('#tituloHistorialCom').textContent = detalleData.item?.titulo || 'Publicación';
        const items = Array.isArray(historyData.items) ? historyData.items : [];
        $('#listaHistorialCom').innerHTML = items.length ? items.map(item => `
          <article class="ev-com-history-item">
            <span class="ev-com-history-dot"></span>
            <div>
              <strong>${escapeHtml(texto(item.accion).replaceAll('_', ' '))}</strong>
              <p>${escapeHtml(fecha(item.created_at))} · ${escapeHtml(texto(item.usuario_accion))}</p>
              <p>Estado: ${escapeHtml(estadoLabel(item.estado_anterior))} → ${escapeHtml(estadoLabel(item.estado_nuevo))}</p>
              ${item.motivo ? `<p>Motivo: ${escapeHtml(item.motivo)}</p>` : ''}
            </div>
          </article>`).join('') : '<div class="ev-com-empty"><i class="bi bi-clock-history"></i><div>Sin movimientos registrados.</div></div>';
        modalHistorial?.show();
      } catch (e) {
        await notify('error', 'No se pudo cargar el historial', e.message);
      }
    }

    $('#btnNuevaPublicacionCom', root).addEventListener('click', () => {
      limpiarFormulario();
      abrirFormulario();
    });
    $('#btnCerrarFormularioCom', root).addEventListener('click', solicitarCierreFormulario);
    $('#btnCancelarFormularioCom', root).addEventListener('click', solicitarCierreFormulario);
    $('#btnGuardarBorradorCom', root).addEventListener('click', () => guardar('guardar_borrador'));
    $('#btnPublicarCom', root).addEventListener('click', () => guardar('publicar'));

    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    tipoInput.addEventListener('change', mostrarEvento);
    destinoSelect?.addEventListener('change', sincronizarDestino);
    $('#tituloCom', root).addEventListener('input', actualizarContadores);
    $('#resumenCom', root).addEventListener('input', actualizarContadores);

    modalFormularioEl.addEventListener('hidden.bs.modal', () => {
      limpiarFormulario();
    });

    imagenInput.addEventListener('change', () => {
      const file = imagenInput.files?.[0];
      limpiarPreviewTemporal();
      if (!file) {
        previewWrap.hidden = true;
        previewImg.src = '';
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        imagenInput.value = '';
        previewWrap.hidden = true;
        previewImg.src = '';
        notify('info', 'Imagen muy grande', 'La portada debe pesar como máximo 2 MB.');
        return;
      }
      state.previewObjectUrl = URL.createObjectURL(file);
      previewImg.src = state.previewObjectUrl;
      previewWrap.hidden = false;
    });

    $('#filtrosComunidadForm', root).addEventListener('submit', event => {
      event.preventDefault();
      state.page = 1;
      listar();
    });
    $('#estadoCom', root).addEventListener('change', () => { state.page = 1; listar(); });
    $('#tipoFiltroCom', root).addEventListener('change', () => { state.page = 1; listar(); });
    $('#btnAnteriorCom', root).addEventListener('click', () => { if (state.page > 1) { state.page -= 1; listar(); } });
    $('#btnSiguienteCom', root).addEventListener('click', () => {
      const pages = Math.max(1, Math.ceil(state.total / state.size));
      if (state.page < pages) { state.page += 1; listar(); }
    });

    tbody.addEventListener('click', event => {
      const button = event.target.closest('button[data-action]');
      if (!button) return;
      const id = Number(button.dataset.id || 0);
      if (!id) return;
      const action = button.dataset.action;
      if (action === 'editar') editar(id);
      if (action === 'publicar') publicar(id);
      if (action === 'desactivar') desactivar(id);
      if (action === 'historial') historial(id);
    });

    mostrarEvento();
    actualizarContadores();
    cargarDestinos().then(listar).catch(error => notify('error', 'No se pudo iniciar el módulo', error.message));
  }

  if (!shared.bound) {
    shared.bound = true;
    document.addEventListener('ev:content-loaded', init);
    document.addEventListener('DOMContentLoaded', init);
  }

  init();
})();
