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

  function accionHistorialInfo(accion) {
    const key = String(accion || '').trim().toLowerCase();
    const items = {
      creacion: {
        label: 'Creación',
        description: 'La publicación fue registrada.',
        icon: 'bi-plus-circle',
        css: 'creacion'
      },
      publicacion: {
        label: 'Publicación',
        description: 'El contenido fue publicado para los vecinos.',
        icon: 'bi-megaphone',
        css: 'publicacion'
      },
      edicion: {
        label: 'Edición',
        description: 'Se actualizaron los datos de la publicación.',
        icon: 'bi-pencil-square',
        css: 'edicion'
      },
      desactivacion: {
        label: 'Desactivación',
        description: 'El contenido dejó de estar visible para los vecinos.',
        icon: 'bi-eye-slash',
        css: 'desactivacion'
      },
      ocultamiento_moderacion: {
        label: 'Ocultamiento por moderación',
        description: 'EV ocultó el contenido por revisión.',
        icon: 'bi-shield-exclamation',
        css: 'ocultamiento_moderacion'
      },
      reactivacion: {
        label: 'Reactivación',
        description: 'La publicación fue activada nuevamente.',
        icon: 'bi-arrow-repeat',
        css: 'reactivacion'
      }
    };

    return items[key] || {
      label: texto(key.replaceAll('_', ' '), 'Movimiento'),
      description: 'Se registró una acción sobre la publicación.',
      icon: 'bi-clock-history',
      css: 'edicion'
    };
  }

  function renderCambioHistorial(item) {
    const accion = String(item.accion || '').trim().toLowerCase();
    const anteriorRaw = String(item.estado_anterior ?? '').trim().toLowerCase();
    const nuevoRaw = String(item.estado_nuevo ?? '').trim().toLowerCase();
    const nuevo = estadoLabel(nuevoRaw);
    const anterior = anteriorRaw ? estadoLabel(anteriorRaw) : '';

    if (accion === 'creacion') {
      return `
        <div class="ev-com-history-change">
          <i class="bi bi-file-earmark-plus" aria-hidden="true"></i>
          <span>Estado inicial:</span>
          <span class="ev-com-history-state">${escapeHtml(nuevo)}</span>
        </div>`;
    }

    if (accion === 'edicion' && anteriorRaw !== '' && anteriorRaw === nuevoRaw) {
      return `
        <div class="ev-com-history-change">
          <i class="bi bi-check2-circle" aria-hidden="true"></i>
          <span>La publicación se mantiene en estado</span>
          <span class="ev-com-history-state">${escapeHtml(nuevo)}</span>
        </div>`;
    }

    return `
      <div class="ev-com-history-change">
        <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
        ${anterior
          ? `<span class="ev-com-history-state">${escapeHtml(anterior)}</span>
             <i class="bi bi-arrow-right ev-com-history-arrow" aria-hidden="true"></i>`
          : ''}
        <span class="ev-com-history-state">${escapeHtml(nuevo)}</span>
      </div>`;
  }

  function renderHistorialItem(item, index = 0) {
    const info = accionHistorialInfo(item.accion);
    const motivo = String(item.motivo || '').trim();
    const esUltimoCambio = index === 0;

    return `
      <article class="ev-com-history-item ev-com-history-item--${info.css}${esUltimoCambio ? ' is-latest' : ''}">
        <span class="ev-com-history-icon" aria-hidden="true">
          <i class="bi ${info.icon}"></i>
        </span>
        <div class="ev-com-history-content">
          <div class="ev-com-history-top">
            <span class="ev-com-history-action">${escapeHtml(info.label)}</span>
            ${esUltimoCambio ? '<span class="ev-com-history-latest"><i class="bi bi-stars"></i> Último cambio</span>' : ''}
          </div>
          <p class="ev-com-history-description">${escapeHtml(info.description)}</p>
          <div class="ev-com-history-meta">
            <i class="bi bi-calendar3" aria-hidden="true"></i>
            <span>${escapeHtml(fecha(item.created_at))}</span>
            <span class="ev-com-history-meta-divider">•</span>
            <i class="bi bi-person-circle" aria-hidden="true"></i>
            <span>${escapeHtml(texto(item.usuario_accion))}</span>
          </div>
          ${renderCambioHistorial(item)}
          ${motivo
            ? `<p class="ev-com-history-reason"><strong>Motivo:</strong> ${escapeHtml(motivo)}</p>`
            : ''}
        </div>
      </article>`;
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
      previewObjectUrl: null,
      scrollDestacadoAntes: null
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
    const tipoOptions = Array.from(root.querySelectorAll('[data-com-tipo]'));
    const prioridadInput = $('#prioridadCom', root);
    const tituloInput = $('#tituloCom', root);
    const resumenInput = $('#resumenCom', root);
    const contenidoInput = $('#contenidoCom', root);
    const destacadoInput = $('#destacadoCom', root);
    const destacadoSwitch = destacadoInput?.closest('.ev-com-highlight-switch') || null;
    const editorScroll = $('.ev-com-editor-scroll', root);
    const zonaPortada = $('#zonaPortadaCom', root);
    const pasoPortada = zonaPortada?.closest('.ev-com-step-card') || null;
    const textoAyudaPortada = $('#textoAyudaPortadaCom', root);
    const nombrePortada = $('#nombrePortadaCom', root);
    const btnCambiarPortada = $('#btnCambiarPortadaCom', root);
    const vistaTipo = $('#vistaTipoCom', root);
    const vistaPrioridad = $('#vistaPrioridadCom', root);
    const vistaDestacado = $('#vistaDestacadoCom', root);
    const vistaTitulo = $('#vistaTituloCom', root);
    const vistaResumen = $('#vistaResumenCom', root);
    const vistaEvento = $('#vistaEventoCom', root);
    const vistaEventoDetalle = $('#vistaEventoDetalleCom', root);
    const vistaImagenBox = $('#vistaImagenBoxCom', root);
    const vistaImagen = $('#vistaImagenCom', root);
    const vistaImagenEmpty = $('#vistaImagenEmptyCom', root);
    const vistaComunidad = $('#vistaComunidadCom', root);

    const modalFormularioEl = $('#modalPublicacionCom', root);
    const modalHistorialEl = document.getElementById('modalHistorialCom');
    const modalFormulario = modalFormularioEl && window.bootstrap?.Modal
      ? new bootstrap.Modal(modalFormularioEl, { backdrop: 'static', keyboard: false, focus: true })
      : null;
    const modalHistorial = modalHistorialEl && window.bootstrap?.Modal
      ? new bootstrap.Modal(modalHistorialEl, { backdrop: 'static', keyboard: false, focus: true })
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
      desactivar: id => `${BASE}/api/comunidad/publicaciones/${id}/desactivar`,
      reactivar: id => `${BASE}/api/comunidad/publicaciones/${id}/reactivar`
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

    function seleccionarTipo(tipo, registrarCambio = true) {
      const permitido = ['comunicado', 'noticia', 'evento'].includes(String(tipo));
      tipoInput.value = permitido ? String(tipo) : 'comunicado';

      tipoOptions.forEach(option => {
        const activo = option.dataset.comTipo === tipoInput.value;
        option.classList.toggle('is-selected', activo);
        option.setAttribute('aria-pressed', activo ? 'true' : 'false');
      });

      mostrarEvento();
      if (registrarCambio) markDirty();
    }

    function ayudaPortadaPorTipo(tipo) {
      return ({
        comunicado: 'Una imagen clara refuerza el mensaje oficial del comunicado.',
        noticia: 'Una imagen atractiva ayuda a presentar mejor la noticia.',
        evento: 'Una imagen clara ayuda a invitar a los vecinos al evento.'
      })[tipo] || 'Una imagen clara acompaña mejor el contenido.';
    }

    function comunidadDestinoActual() {
      const visible = String(vistaComunidad?.textContent || '').trim();
      return visible || comunidadVisible;
    }

    function sincronizarDestacadoVisual() {
      const activo = Boolean(destacadoInput?.checked);

      destacadoSwitch?.classList.toggle('is-active', activo);
      destacadoSwitch?.setAttribute('data-active', activo ? '1' : '0');

      if (vistaDestacado) {
        vistaDestacado.hidden = !activo;
      }
    }

    function actualizarVistaPrevia() {
      const tipo = tipoInput.value || 'comunicado';
      const prioridad = prioridadInput?.value || 'normal';
      if (textoAyudaPortada) textoAyudaPortada.textContent = ayudaPortadaPorTipo(tipo);
      if (vistaTipo) vistaTipo.textContent = tipoLabel(tipo);
      if (vistaPrioridad) {
        const icono = prioridad === 'urgente'
          ? '<i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>'
          : prioridad === 'importante'
            ? '<i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>'
            : '';
        vistaPrioridad.innerHTML = `${icono}${prioridadLabel(prioridad)}`;
        vistaPrioridad.className = `ev-com-live-priority ev-com-live-priority--${prioridad}`;
      }
      sincronizarDestacadoVisual();
      if (vistaTitulo) vistaTitulo.textContent = tituloInput?.value.trim() || 'Título de la publicación';
      if (vistaResumen) vistaResumen.textContent = resumenInput?.value.trim() || 'El resumen breve que verán los vecinos aparecerá aquí.';

      const esEvento = tipo === 'evento';
      if (vistaEvento) vistaEvento.classList.toggle('d-none', !esEvento);
      if (esEvento && vistaEventoDetalle) {
        const inicio = $('#fechaEventoInicioCom', root)?.value || '';
        const lugar = $('#ubicacionEventoCom', root)?.value.trim() || 'Lugar por definir';
        vistaEventoDetalle.textContent = inicio ? `${fecha(inicio)} · ${lugar}` : `Fecha por definir · ${lugar}`;
      }
    }

    function mostrarImagenEnVistas(src = '', nombre = '') {
      const hayImagen = String(src || '').trim() !== '';
      const nombreSeguro = String(nombre || '').trim();

      previewWrap.hidden = !hayImagen;
      if (zonaPortada) zonaPortada.hidden = hayImagen;
      pasoPortada?.classList.toggle('has-portada', hayImagen);

      previewImg.src = hayImagen ? src : '';
      if (nombrePortada) {
        nombrePortada.textContent = nombreSeguro || 'Archivo de portada seleccionado';
        nombrePortada.title = nombreSeguro || '';
      }

      vistaImagenBox?.classList.toggle('has-image', hayImagen);
      if (vistaImagen) {
        vistaImagen.hidden = !hayImagen;
        vistaImagen.src = hayImagen ? src : '';
      }
      if (vistaImagenEmpty) vistaImagenEmpty.hidden = hayImagen;
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
      actualizarVistaPrevia();
    }

    function actualizarContadores() {
      tituloChars.textContent = String(tituloInput.value.length);
      resumenChars.textContent = String(resumenInput.value.length);
      actualizarVistaPrevia();
    }

    function sincronizarDestino() {
      if (!destinoSelect) return;
      const option = destinoSelect.selectedOptions[0];
      tipoConjuntoInput.value = option?.dataset.tipo || '';
      codigoComunidadInput.value = option?.dataset.codigo || '';
      if (vistaComunidad) {
        vistaComunidad.textContent = esAdminSistema && option?.dataset.codigo
          ? texto(option.textContent, comunidadVisible)
          : comunidadVisible;
      }
    }

    function limpiarFormulario() {
      state.suspendDirty = true;
      limpiarPreviewTemporal();
      form.reset();
      codigoInput.value = '';
      tipoInput.value = 'comunicado';
      state.editando = null;
      const titleNode = $('#evComFormTitle span', root);
      if (titleNode) titleNode.textContent = 'Nueva publicación';
      btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar borrador';
      btnPublicar.classList.remove('d-none');
      btnGuardar.disabled = false;
      btnPublicar.disabled = false;
      sincronizarDestino();
      seleccionarTipo('comunicado', false);
      actualizarContadores();
      mostrarImagenEnVistas('', '');
      imagenInput.value = '';
      state.scrollDestacadoAntes = null;
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

        if (estado === 'borrador' || estado === 'publicado' || estado === 'inactivo') {
          actions.unshift(`<button type="button" class="ev-com-mini-btn" data-action="editar" data-id="${id}"><i class="bi bi-pencil"></i> Editar</button>`);
        }
        if (estado === 'borrador') {
          actions.unshift(`<button type="button" class="ev-com-mini-btn publish" data-action="publicar" data-id="${id}"><i class="bi bi-send-check"></i> Publicar</button>`);
        }
        if (estado === 'publicado') {
          actions.unshift(`<button type="button" class="ev-com-mini-btn off" data-action="desactivar" data-id="${id}"><i class="bi bi-eye-slash"></i> Desactivar</button>`);
        }
        if (estado === 'inactivo') {
          actions.unshift(`<button type="button" class="ev-com-mini-btn publish" data-action="reactivar" data-id="${id}"><i class="bi bi-arrow-repeat"></i> Reactivar</button>`);
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
        mostrarImagenEnVistas('', '');
        return;
      }
      const clean = String(path).replace(/^\/+/, '');
      mostrarImagenEnVistas(`${BASE}/${clean}`, 'Portada actual');
    }

    async function editar(id) {
      try {
        const data = await requestJSON(url.detalle(id));
        const item = data.item || {};
        limpiarFormulario();
        state.suspendDirty = true;
        state.editando = id;
        codigoInput.value = String(id);
        const titleNode = $('#evComFormTitle span', root);
        if (titleNode) titleNode.textContent = 'Editar publicación';
        seleccionarTipo(item.tipo_publicacion || 'comunicado', false);
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

        if (item.estado === 'publicado' || item.estado === 'inactivo') {
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
          `El contenido será visible para los vecinos de ${comunidadDestinoActual()}.`,
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

    async function reactivar(id) {
      if (!await confirmar('La publicación volverá a mostrarse a los vecinos de la comunidad.', 'Sí, reactivar')) return;
      try {
        const data = await requestJSON(url.reactivar(id), { method: 'POST' });
        await notify('success', 'Publicación reactivada', data.mensaje);
        await listar();
      } catch (e) {
        await notify('error', 'No se pudo reactivar', e.message);
      }
    }

    async function historial(id) {
      try {
        const [detalleData, historyData] = await Promise.all([
          requestJSON(url.detalle(id)),
          requestJSON(url.historial(id))
        ]);

        const itemActual = detalleData.item || {};
        const titulo = itemActual.titulo || 'Publicación';
        const tipoKey = String(itemActual.tipo_publicacion || 'comunicado').trim().toLowerCase();
        const estadoKey = String(itemActual.estado || 'borrador').trim().toLowerCase();
        const items = Array.isArray(historyData.items) ? historyData.items : [];

        const tituloHistorial = $('#tituloHistorialCom');
        const tipoHistorial = $('#tipoHistorialCom');
        const estadoHistorial = $('#estadoHistorialCom');
        const totalHistorial = $('#totalMovimientosHistorialCom');
        const textoMovimientos = $('#textoMovimientosHistorialCom');
        const listaHistorial = $('#listaHistorialCom');

        if (tituloHistorial) {
          tituloHistorial.textContent = titulo;
          tituloHistorial.title = titulo;
        }

        if (tipoHistorial) {
          tipoHistorial.textContent = tipoLabel(tipoKey);
          tipoHistorial.className = `ev-com-history-type ev-com-history-type--${tipoKey}`;
        }

        if (estadoHistorial) {
          estadoHistorial.textContent = estadoLabel(estadoKey);
          estadoHistorial.className = `ev-com-history-current ev-com-history-current--${estadoKey}`;
        }

        if (totalHistorial) totalHistorial.textContent = String(items.length);
        if (textoMovimientos) {
          textoMovimientos.textContent = items.length === 1
            ? 'movimiento registrado'
            : 'movimientos registrados';
        }

        if (listaHistorial) {
          listaHistorial.innerHTML = items.length
            ? items.map((item, index) => renderHistorialItem(item, index)).join('')
            : `
              <div class="ev-com-empty">
                <i class="bi bi-clock-history"></i>
                <div>Sin movimientos registrados.</div>
                <small>Las acciones realizadas sobre esta publicación aparecerán aquí.</small>
              </div>`;
        }

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
    tipoOptions.forEach(option => {
      option.addEventListener('click', () => seleccionarTipo(option.dataset.comTipo || 'comunicado'));
    });
    destinoSelect?.addEventListener('change', sincronizarDestino);
    tituloInput.addEventListener('input', actualizarContadores);
    resumenInput.addEventListener('input', actualizarContadores);
    contenidoInput.addEventListener('input', actualizarVistaPrevia);
    prioridadInput.addEventListener('change', actualizarVistaPrevia);

    /*
       El switch conserva su posición visual dentro del panel izquierdo.
       Así, al activarlo/desactivarlo, el navegador no desplaza el editor
       por enfocar el checkbox oculto.
    */
    destacadoSwitch?.addEventListener('pointerdown', () => {
      state.scrollDestacadoAntes = editorScroll ? editorScroll.scrollTop : null;
    });

    destacadoInput?.addEventListener('change', () => {
      actualizarVistaPrevia();

      if (editorScroll && Number.isFinite(state.scrollDestacadoAntes)) {
        const top = Number(state.scrollDestacadoAntes);
        window.requestAnimationFrame(() => {
          editorScroll.scrollTop = top;
          state.scrollDestacadoAntes = null;
        });
      }
    });

    $('#fechaEventoInicioCom', root).addEventListener('change', actualizarVistaPrevia);
    $('#ubicacionEventoCom', root).addEventListener('input', actualizarVistaPrevia);

    modalFormularioEl.addEventListener('hidden.bs.modal', () => {
      limpiarFormulario();
    });

    imagenInput.addEventListener('change', () => {
      const file = imagenInput.files?.[0];
      limpiarPreviewTemporal();
      if (!file) {
        mostrarImagenEnVistas('', '');
        return;
      }
      const tiposImagenPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
      if (file.type && !tiposImagenPermitidos.includes(file.type)) {
        imagenInput.value = '';
        mostrarImagenEnVistas('', '');
        notify('info', 'Formato no permitido', 'Solo puedes seleccionar imágenes JPG, PNG o WEBP.');
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        imagenInput.value = '';
        mostrarImagenEnVistas('', '');
        notify('info', 'Imagen muy grande', 'La portada debe pesar como máximo 2 MB.');
        return;
      }
      state.previewObjectUrl = URL.createObjectURL(file);
      mostrarImagenEnVistas(state.previewObjectUrl, file.name || 'Imagen seleccionada');
    });

    btnCambiarPortada?.addEventListener('click', () => imagenInput.click());

    ['dragenter', 'dragover'].forEach(type => {
      zonaPortada?.addEventListener(type, event => {
        event.preventDefault();
        zonaPortada.classList.add('is-dragging');
      });
    });
    ['dragleave', 'drop'].forEach(type => {
      zonaPortada?.addEventListener(type, event => {
        event.preventDefault();
        zonaPortada.classList.remove('is-dragging');
      });
    });
    zonaPortada?.addEventListener('drop', event => {
      const file = event.dataTransfer?.files?.[0];
      if (!file) return;
      const data = new DataTransfer();
      data.items.add(file);
      imagenInput.files = data.files;
      imagenInput.dispatchEvent(new Event('change', { bubbles: true }));
      markDirty();
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
      if (action === 'reactivar') reactivar(id);
      if (action === 'historial') historial(id);
    });

    seleccionarTipo('comunicado', false);
    actualizarContadores();
    mostrarImagenEnVistas('', '');
    cargarDestinos().then(listar).catch(error => notify('error', 'No se pudo iniciar el módulo', error.message));
  }

  if (!shared.bound) {
    shared.bound = true;
    document.addEventListener('ev:content-loaded', init);
    document.addEventListener('DOMContentLoaded', init);
  }

  init();
})();
