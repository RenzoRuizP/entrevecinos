/* producto.js – EV (Publicaciones: productos/servicios + filtros + tabs + responsive + no rompe menú) */

(() => {
  'use strict';

  const EV_API_BASE = (window.BASE_URL || '').replace(/\/$/, '');
  if (!EV_API_BASE) return;

  window.evProductosCache  = window.evProductosCache  || [];
  window.evProductosFiltro = window.evProductosFiltro || {
    q: '',
    tab: 'all',
    tipo_publicacion: '',
    tipo: '',
    categoria: '',
    min: '',
    max: '',
    orden: 'recientes'
  };

  const REENVIO_PREFIX = 'REENVIO_CORRECCION|';

  function evNotify(icon, title, text) {
    if (window.Swal?.fire) {
      Swal.fire({
        icon,
        title,
        text,
        confirmButtonText: 'Aceptar',
        customClass: { confirmButton: 'btn btn-outline-success' },
        buttonsStyling: false
      });
    } else {
      alert(title ? `${title}\n\n${text}` : text);
    }
  }

  async function evBlockedRedirect(msg, redirect) {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;
    window.__EV_AUTH_REDIRECTING__ = true;

    const mensaje = msg || 'Tu cuenta fue bloqueada. Se cerró tu sesión por seguridad.';
    const target = redirect || `${EV_API_BASE}/login`;

    if (window.Swal?.fire) {
      await Swal.fire({
        icon: 'warning',
        title: 'Cuenta bloqueada',
        text: mensaje,
        confirmButtonText: 'Ir al login',
        confirmButtonColor: '#EA7C12',
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    } else {
      alert(mensaje);
    }

    window.location.assign(target);
  }

  async function evSessionRedirect(msg, redirect) {
    if (window.__EV_AUTH_REDIRECTING__ === true) return;
    window.__EV_AUTH_REDIRECTING__ = true;

    const mensaje = msg || 'Tu sesión ha expirado. Vuelve a iniciar sesión.';
    const target = redirect || `${EV_API_BASE}/login`;

    if (window.Swal?.fire) {
      await Swal.fire({
        icon: 'info',
        title: 'Sesión finalizada',
        text: mensaje,
        confirmButtonText: 'Ir al login',
        confirmButtonColor: '#EA7C12',
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    } else {
      alert(mensaje);
    }

    window.location.assign(target);
  }

  async function evHandleAuthResponse(resp, data) {
    if (resp.status === 403 && data && data.error === 'CUENTA_BLOQUEADA') {
      await evBlockedRedirect(data.mensaje, data.redirect);
      return true;
    }

    if (resp.status === 401 || (data && data.error === 'UNAUTHORIZED')) {
      await evSessionRedirect(data?.mensaje, data?.redirect);
      return true;
    }

    return false;
  }

  async function evConfirm({
    icon = 'question',
    title = 'Confirmar',
    text = '',
    confirmText = 'Sí',
    cancelText = 'Cancelar',
    confirmBtnClass = 'btn btn-success me-2'
  } = {}) {
    if (window.Swal?.fire) {
      const { isConfirmed } = await Swal.fire({
        icon,
        title,
        text,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        customClass: { confirmButton: confirmBtnClass, cancelButton: 'btn btn-outline-secondary' },
        buttonsStyling: false
      });
      return !!isConfirmed;
    }
    return window.confirm(`${title}\n\n${text}`);
  }

  const EV_SERVICIOS_PILOTO_MAX = 5;
  let evServiciosPiloto = {
    maximo: EV_SERVICIOS_PILOTO_MAX,
    activos: 0,
    disponibles: EV_SERVICIOS_PILOTO_MAX,
    alcanzado: false,
    es_gratis: true
  };

  function normalizarResumenServiciosPiloto(resumen = {}) {
    const maximo = Math.max(1, Number(resumen?.maximo ?? EV_SERVICIOS_PILOTO_MAX) || EV_SERVICIOS_PILOTO_MAX);
    const activos = Math.max(0, Number(resumen?.activos ?? 0) || 0);
    const disponibles = Math.max(0, Number(resumen?.disponibles ?? (maximo - activos)) || 0);

    return {
      maximo,
      activos,
      disponibles,
      alcanzado: Boolean(resumen?.alcanzado ?? activos >= maximo),
      es_gratis: true
    };
  }

  function textoCuposServiciosPiloto(resumen = evServiciosPiloto) {
    const info = normalizarResumenServiciosPiloto(resumen);

    if (info.alcanzado) {
      return `Tienes ${info.activos} de ${info.maximo} cupos activos. Puedes guardar borradores, pero anula un servicio para liberar un cupo antes de enviarlo a revisión.`;
    }

    return `${info.activos} de ${info.maximo} cupos activos en uso · ${info.disponibles} ${info.disponibles === 1 ? 'cupo disponible' : 'cupos disponibles'}.`;
  }

  function actualizarResumenServiciosPiloto(resumen = {}) {
    evServiciosPiloto = normalizarResumenServiciosPiloto(resumen);
    window.EVServiciosPiloto = { ...evServiciosPiloto };

    const root = document.getElementById('evServiciosPiloto');
    if (root) {
      const usados = root.querySelector('[data-ev-service-used]');
      const maximo = root.querySelector('[data-ev-service-max]');
      const texto = root.querySelector('[data-ev-service-text]');
      const barra = root.querySelector('[data-ev-service-meter]');
      const porcentaje = Math.min(100, Math.round((evServiciosPiloto.activos / evServiciosPiloto.maximo) * 100));

      root.classList.toggle('is-full', evServiciosPiloto.alcanzado);
      root.setAttribute('data-estado', evServiciosPiloto.alcanzado ? 'limite' : 'disponible');
      if (usados) usados.textContent = String(evServiciosPiloto.activos);
      if (maximo) maximo.textContent = String(evServiciosPiloto.maximo);
      if (texto) texto.textContent = textoCuposServiciosPiloto(evServiciosPiloto);
      if (barra) {
        barra.style.width = `${porcentaje}%`;
        barra.setAttribute('aria-valuenow', String(evServiciosPiloto.activos));
        barra.setAttribute('aria-valuemax', String(evServiciosPiloto.maximo));
      }
    }

    document.querySelectorAll('[data-ev-service-pilot-notice]').forEach((notice) => {
      const count = notice.querySelector('[data-ev-service-notice-count]');
      const max = notice.querySelector('[data-ev-service-notice-max]');
      const message = notice.querySelector('[data-ev-service-notice-text]');
      notice.classList.toggle('is-full', evServiciosPiloto.alcanzado);
      if (count) count.textContent = String(evServiciosPiloto.activos);
      if (max) max.textContent = String(evServiciosPiloto.maximo);
      if (message) message.textContent = textoCuposServiciosPiloto(evServiciosPiloto);
    });
  }

  function mostrarLimiteServiciosPiloto(resumen = {}) {
    const info = normalizarResumenServiciosPiloto(resumen);
    actualizarResumenServiciosPiloto(info);

    evNotify(
      'info',
      'Límite de servicios alcanzado',
      `Ya tienes ${info.activos} de ${info.maximo} servicios activos o en revisión. Anula uno de ellos para liberar un cupo y luego podrás enviar este servicio a revisión.`
    );
  }

  function setEvVh() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--ev-vh', `${vh}px`);
  }
  setEvVh();
  window.addEventListener('resize', setEvVh);
  window.addEventListener('orientationchange', setEvVh);
  document.addEventListener('shown.bs.modal', setEvVh);

  function evMountModalToBody(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    if (el.parentElement !== document.body) document.body.appendChild(el);
  }

  function evMountAllModalsToBody() {
    evMountModalToBody('modalBuscarPublicacion');
    evMountModalToBody('modalAgregarPublicacion');
    evMountModalToBody('modalEditarPublicacion');
  }

  function evGetStaticModal(modalId) {
    evMountModalToBody(modalId);

    const el = document.getElementById(modalId);
    if (!el || !window.bootstrap?.Modal) return null;

    el.setAttribute('data-bs-backdrop', 'static');
    el.setAttribute('data-bs-keyboard', 'false');

    return bootstrap.Modal.getOrCreateInstance(el, {
      backdrop: 'static',
      keyboard: false,
      focus: true
    });
  }

  function evGetFotoSrc(f) {
    if (!f) return '';
    if (typeof f === 'string') return f;
    return f.url || f.ruta || f.ruta_imagen || f.imagen || f.path || f.path_imagen || '';
  }

  function evGetFotoId(f, idx) {
    if (!f || typeof f !== 'object') return 'old_' + idx;
    return f.id_imagen || f.codigo_imagen || f.id || f.codigo || ('old_' + idx);
  }

  function evSyncDropzoneState(tilesEl) {
    if (!tilesEl) return;
    const section = tilesEl.closest('.ev-section');
    if (!section) return;
    const hasTiles = !!tilesEl.querySelector('.ev-tile');
    section.classList.toggle('ev-has-tiles', hasTiles);
  }

  function escAttr(v) {
    return String(v ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function debounce(fn, wait = 250) {
    let t = null;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), wait);
    };
  }

  function limpiarComentarioSistema(comentario) {
    const c = String(comentario ?? '').trim();
    if (!c) return '';
    if (c.startsWith(REENVIO_PREFIX)) {
      return c.replace(REENVIO_PREFIX, '').trim();
    }
    return c;
  }

  function normalizarTipoPublicacion(valor, allowEmpty = false) {
    const v = String(valor ?? '').trim().toLowerCase();
    if (allowEmpty && v === '') return '';
    return (v === 'servicio') ? 'servicio' : 'producto';
  }

  function tipoPublicacionLabel(valor) {
    return normalizarTipoPublicacion(valor) === 'servicio' ? 'Servicio' : 'Producto';
  }

  function tipoPublicacionLabelPlural(valor) {
    return normalizarTipoPublicacion(valor) === 'servicio' ? 'servicios' : 'productos';
  }

  function getTipoPublicacionItem(item) {
    return normalizarTipoPublicacion(item?.tipo_publicacion || 'producto');
  }

  function getTipoPublicacionFromForm(form, edit = false) {
    if (!form) return 'producto';
    const name = edit ? 'edit_tipo_publicacion' : 'tipo_publicacion';
    const checked = form.querySelector(`input[name="${name}"]:checked`);
    return normalizarTipoPublicacion(checked?.value || 'producto');
  }

  function setTipoPublicacionRadio(form, tipo, edit = false) {
    if (!form) return;
    const name = edit ? 'edit_tipo_publicacion' : 'tipo_publicacion';
    const normalized = normalizarTipoPublicacion(tipo);
    const target = form.querySelector(`input[name="${name}"][value="${normalized}"]`);
    if (target) target.checked = true;
  }

  function tipoPublicacionBadgeClass(tipo) {
    return normalizarTipoPublicacion(tipo) === 'servicio'
      ? 'ev-chip ev-chip-orange ev-chip-status'
      : 'ev-chip ev-chip-green ev-chip-status';
  }

  function actualizarPreviewMetaTipo(modal, tipo) {
    if (!modal) return;
    const isEdit = modal.id === 'modalEditarPublicacion';
    const badge = document.getElementById(isEdit ? 'evMetaKindEdit' : 'evMetaKindAdd');
    if (!badge) return;

    const normalized = normalizarTipoPublicacion(tipo);
    badge.textContent = tipoPublicacionLabel(normalized);

    if (badge.classList.contains('ev-preview-kind')) {
      badge.className = normalized === 'servicio'
        ? 'ev-preview-kind ev-preview-kind-servicio'
        : 'ev-preview-kind ev-preview-kind-producto';
      return;
    }

    badge.className = tipoPublicacionBadgeClass(normalized);
  }

  function dispararMetaPreviewLive(modal) {
    if (!modal) return;
    modal.querySelector('input[name="titulo"], #edit_titulo')?.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function setTextContentById(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  }

  function setHtmlById(id, html) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = html;
  }

  function setPlaceholder(el, text) {
    if (el) el.setAttribute('placeholder', text);
  }

  /*
   * Prepara únicamente el texto que se muestra en la vista previa.
   * No modifica el textarea ni la descripción enviada a la API.
   */
  function prepararDescripcionPreview(texto, limite = 190) {
    const valor = String(texto ?? '').trim();

    if (!valor) {
      return 'La descripción aparecerá aquí.';
    }

    const resumen = valor.length > limite
      ? `${valor.slice(0, limite).trimEnd()}…`
      : valor;

    /*
     * Inserta un salto invisible cada 18 caracteres dentro de una palabra
     * excesivamente larga. Así el card no se desborda sin tocar CSS.
     */
    return resumen.replace(/(\S{18})(?=\S)/g, '$1\u200B');
  }

  function actualizarOpcionesTipoPublicacionVisual(modal, tipo) {
    if (!modal) return;

    const normalized = normalizarTipoPublicacion(tipo);
    const selectorName = modal.id === 'modalEditarPublicacion'
      ? 'edit_tipo_publicacion'
      : 'tipo_publicacion';

    modal.querySelectorAll(`input[name="${selectorName}"]`).forEach((radio) => {
      const label = modal.querySelector(`label[for="${radio.id}"]`);
      const active = normalizarTipoPublicacion(radio.value) === normalized && radio.checked;
      if (label) {
        label.classList.toggle('is-active', active);
      }
    });
  }

  function actualizarTextosTipoPublicacion(modal, tipo) {
    if (!modal) return;

    const esEdit = modal.id === 'modalEditarPublicacion';
    const esServicio = normalizarTipoPublicacion(tipo) === 'servicio';

    const suf = esEdit ? 'Edit' : 'Add';
    const titleInput = esEdit
      ? modal.querySelector('#edit_titulo')
      : modal.querySelector('input[name="titulo"]');
    const descInput = esEdit
      ? modal.querySelector('#edit_descripcion')
      : modal.querySelector('textarea[name="descripcion"]');

    if (esServicio) {
      setTextContentById(`tituloImagenes${suf}`, 'Imágenes del servicio');
      setTextContentById(`dropZoneTitulo${suf}`, 'Sube imágenes referenciales del servicio o haz clic para seleccionarlas');
      setTextContentById(`hintImagenPrincipal${suf}`, 'La primera imagen será la portada de tu servicio.');
      setHtmlById(`labelTitulo${suf}`, 'Nombre del servicio <span class="text-danger">*</span>');
      setHtmlById(`labelPrecio${suf}`, 'Precio base o referencial (S/) <span class="text-danger">*</span>');
      setHtmlById(`labelDescripcion${suf}`, 'Descripción del servicio <span class="text-danger">*</span>');
      setPlaceholder(titleInput, 'Ej. Clases de matemática, manicure, reparación de laptops');
      setPlaceholder(descInput, 'Describe qué incluye el servicio, disponibilidad, forma de atención y condiciones importantes.');
    } else {
      setTextContentById(`tituloImagenes${suf}`, 'Fotos del producto');
      setTextContentById(`dropZoneTitulo${suf}`, 'Arrastra tus fotos aquí o haz clic para seleccionarlas');
      setTextContentById(`hintImagenPrincipal${suf}`, 'La primera foto será la imagen principal de tu publicación.');
      setHtmlById(`labelTitulo${suf}`, 'Título <span class="text-danger">*</span>');
      setHtmlById(`labelPrecio${suf}`, 'Precio (S/) <span class="text-danger">*</span>');
      setHtmlById(`labelDescripcion${suf}`, 'Descripción <span class="text-danger">*</span>');
      setPlaceholder(titleInput, 'Escribe un título claro y atractivo');
      setPlaceholder(descInput, 'Cuenta los detalles más importantes para que tus vecinos se animen a comprar.');
    }
  }

  function aplicarTipoPublicacionUI(modal, tipoRaw) {
    if (!modal) return;

    const form = modal.id === 'modalEditarPublicacion'
      ? modal.querySelector('#formEditarPublicacion')
      : modal.querySelector('#formAgregarPublicacion');

    const tipo = normalizarTipoPublicacion(tipoRaw || (
      modal.id === 'modalEditarPublicacion'
        ? getTipoPublicacionFromForm(form, true)
        : getTipoPublicacionFromForm(form, false)
    ));

    const esServicio = tipo === 'servicio';

    modal.dataset.evTipoPublicacion = tipo;
    modal.classList.toggle('ev-modal-publicacion-servicio', esServicio);
    modal.classList.toggle('ev-modal-publicacion-producto', !esServicio);

    actualizarOpcionesTipoPublicacionVisual(modal, tipo);
    actualizarTextosTipoPublicacion(modal, tipo);

    modal.querySelectorAll('[data-ev-product-only]').forEach((el) => {
      el.classList.toggle('d-none', esServicio);
      el.setAttribute('aria-hidden', esServicio ? 'true' : 'false');
      el.querySelectorAll('input, select, textarea').forEach((node) => {
        node.disabled = esServicio;
      });
    });

    modal.querySelectorAll('[data-ev-service-only]').forEach((el) => {
      el.classList.toggle('d-none', !esServicio);
      el.setAttribute('aria-hidden', !esServicio ? 'true' : 'false');
    });

    const estado = modal.querySelector('select[name="estado"], #edit_estado');
    const tipoAtencion = modal.querySelector('#tipoAtencionProducto, #edit_tipoAtencionProducto');

    if (esServicio) {
      if (estado) {
        estado.value = 'NoAplica';
        estado.disabled = true;
      }
      if (tipoAtencion) {
        tipoAtencion.value = 'no_requiere_preparacion';
        tipoAtencion.dataset.evAutoTipoAtencion = 'no_requiere_preparacion';
        tipoAtencion.disabled = true;
      }
    } else {
      if (estado) {
        estado.disabled = false;
        if (!estado.value || estado.value === 'NoAplica') estado.value = 'Nuevo';
      }
      if (tipoAtencion) {
        // El vendedor no decide este campo: lo define EV según la categoría.
        tipoAtencion.disabled = true;
        if (!tipoAtencion.value) tipoAtencion.value = 'no_requiere_preparacion';
        if (!tipoAtencion.dataset.evAutoTipoAtencion) {
          tipoAtencion.dataset.evAutoTipoAtencion = tipoAtencion.value;
        }
      }
    }

    modal.querySelectorAll('[data-ev-service-pilot-notice]').forEach((notice) => {
      notice.hidden = !esServicio;
      notice.setAttribute('aria-hidden', esServicio ? 'false' : 'true');
    });

    actualizarResumenServiciosPiloto(evServiciosPiloto);
    evShortenCategoriaPlaceholder(modal);
    actualizarPreviewMetaTipo(modal, tipo);
    dispararMetaPreviewLive(modal);
  }


  /*
   * Al cambiar Producto <-> Servicio durante la creación, el borrador
   * anterior deja de ser válido: se limpian campos, imágenes y vista previa.
   * No se aplica al modal de edición para no borrar información existente.
   */
  function limpiarBorradorAgregarPorCambioTipo(modal, tipoRaw) {
    const form = document.getElementById('formAgregarPublicacion');
    if (!modal || !form) return;

    const tipo = normalizarTipoPublicacion(tipoRaw);

    evClearFieldErrors(form);

    const titulo = form.querySelector('input[name="titulo"]');
    const precio = form.querySelector('input[name="precio"]');
    const descripcion = form.querySelector('textarea[name="descripcion"]');
    const estado = form.querySelector('select[name="estado"]');
    const tipoAtencion = form.querySelector('#tipoAtencionProducto');
    const comboTipo = form.querySelector('#comboTipo');
    const comboCategoria = form.querySelector('#comboCategoria');

    if (titulo) titulo.value = '';
    if (precio) precio.value = '';
    if (descripcion) descripcion.value = '';

    if (estado) {
      estado.disabled = tipo === 'servicio';
      estado.value = tipo === 'servicio' ? 'NoAplica' : 'Nuevo';
    }

    if (tipoAtencion) {
      tipoAtencion.value = 'no_requiere_preparacion';
      tipoAtencion.dataset.evAutoTipoAtencion = 'no_requiere_preparacion';
      tipoAtencion.disabled = true;
    }

    /*
     * combo_tipo.js volverá a cargar el tipo automático y las categorías
     * correctas luego del evento change del radio.
     */
    if (comboTipo) {
      comboTipo.innerHTML = '<option value="" selected>-- Cargando tipo --</option>';
      comboTipo.disabled = true;
      comboTipo.dataset.valorRegistrado = '';
    }

    if (comboCategoria) {
      comboCategoria.innerHTML = '<option value="" selected>-- Cargando categorías --</option>';
      comboCategoria.disabled = true;
      comboCategoria.dataset.valorRegistrado = '';
    }

    if (typeof window.evLimpiarImagenesAgregar === 'function') {
      window.evLimpiarImagenesAgregar();
    } else {
      evPintarPreviewAgregar([]);
    }

    actualizarPreviewMetaTipo(modal, tipo);
    dispararMetaPreviewLive(modal);
  }

  function resetTipoPublicacionAgregarUI() {
    const modal = document.getElementById('modalAgregarPublicacion');
    const form = document.getElementById('formAgregarPublicacion');
    if (!modal || !form) return;
    setTipoPublicacionRadio(form, 'producto', false);
    aplicarTipoPublicacionUI(modal, 'producto');
  }

  function bindTipoPublicacionUI() {
    const modalAdd = document.getElementById('modalAgregarPublicacion');
    const formAdd = document.getElementById('formAgregarPublicacion');
    const modalEdit = document.getElementById('modalEditarPublicacion');
    const formEdit = document.getElementById('formEditarPublicacion');

    if (formAdd && !formAdd.dataset.evTipoPublicacionBound) {
      formAdd.dataset.evTipoPublicacionBound = '1';
      formAdd.querySelectorAll('input[name="tipo_publicacion"]').forEach((radio) => {
        radio.addEventListener('change', () => {
          if (!radio.checked) return;

          const tipoNuevo = normalizarTipoPublicacion(radio.value);
          const tipoAnterior = normalizarTipoPublicacion(modalAdd?.dataset?.evTipoPublicacion || 'producto');

          if (tipoAnterior !== tipoNuevo) {
            limpiarBorradorAgregarPorCambioTipo(modalAdd, tipoNuevo);
          }

          aplicarTipoPublicacionUI(modalAdd, tipoNuevo);
        });
      });
    }

    if (formEdit && !formEdit.dataset.evTipoPublicacionBound) {
      formEdit.dataset.evTipoPublicacionBound = '1';
      formEdit.querySelectorAll('input[name="edit_tipo_publicacion"]').forEach((radio) => {
        radio.addEventListener('change', () => aplicarTipoPublicacionUI(modalEdit, radio.value));
      });
    }

    if (modalAdd) aplicarTipoPublicacionUI(modalAdd, getTipoPublicacionFromForm(formAdd, false));
    if (modalEdit) aplicarTipoPublicacionUI(modalEdit, getTipoPublicacionFromForm(formEdit, true));
  }

  async function evFetchJson(url) {
    const resp = await fetch(url, { method: 'GET' });
    const data = await resp.json().catch(() => null);

    if (await evHandleAuthResponse(resp, data || {})) return null;

    return data;
  }

  function mapIdNombre(row, idKeys, nameKeys) {
    let id = '';
    let name = '';
    for (const k of idKeys) {
      if (row && row[k] != null && String(row[k]).trim() !== '') { id = String(row[k]); break; }
    }
    for (const k of nameKeys) {
      if (row && row[k] != null && String(row[k]).trim() !== '') { name = String(row[k]); break; }
    }
    return { id, name };
  }

  async function cargarTiposFiltro() {
    const selTipo = document.getElementById('fTipo');
    if (!selTipo) return;

    const tipoPublicacion = normalizarTipoPublicacion(
      document.getElementById('fTipoPublicacion')?.value || window.evProductosFiltro?.tipo_publicacion || '',
      true
    );

    const url = tipoPublicacion
      ? `${EV_API_BASE}/tipos?tipo_publicacion=${encodeURIComponent(tipoPublicacion)}`
      : `${EV_API_BASE}/tipos`;

    const data = await evFetchJson(url);
    if (!data || !data.ok || !Array.isArray(data.data)) return;

    const prev = String(window.evProductosFiltro?.tipo || '');
    selTipo.innerHTML = `<option value="">Todos</option>`;

    data.data.forEach(r => {
      const m = mapIdNombre(r, ['codigo_tipo', 'id', 'codigo'], ['nombre', 'tipo', 'descripcion']);
      if (!m.id) return;
      const opt = document.createElement('option');
      opt.value = m.id;
      opt.textContent = m.name || `Tipo ${m.id}`;
      selTipo.appendChild(opt);
    });

    if (prev) selTipo.value = prev;
  }

  async function cargarCategoriasFiltro(tipoId) {
    const selCat = document.getElementById('fCategoria');
    if (!selCat) return;

    selCat.innerHTML = `<option value="">Todas</option>`;
    selCat.disabled = true;

    const tid = String(tipoId || '').trim();
    if (!tid) return;

    const tipoPublicacion = normalizarTipoPublicacion(
      document.getElementById('fTipoPublicacion')?.value || window.evProductosFiltro?.tipo_publicacion || '',
      true
    );

    const url = tipoPublicacion
      ? `${EV_API_BASE}/tipos/${encodeURIComponent(tid)}/categoria_grupo?tipo_publicacion=${encodeURIComponent(tipoPublicacion)}`
      : `${EV_API_BASE}/tipos/${encodeURIComponent(tid)}/categoria_grupo`;

    const data = await evFetchJson(url);
    if (!data || !data.ok || !Array.isArray(data.data)) return;

    data.data.forEach(r => {
      const m = mapIdNombre(r, ['codigo_categoria', 'id', 'codigo'], ['nombre', 'categoria', 'descripcion']);
      if (!m.id) return;
      const opt = document.createElement('option');
      opt.value = m.id;
      opt.textContent = m.name || `Categoría ${m.id}`;
      selCat.appendChild(opt);
    });

    selCat.disabled = false;

    const prev = String(window.evProductosFiltro?.categoria || '');
    if (prev) selCat.value = prev;
  }

  function syncFiltrosFromUI() {
    const q = document.getElementById('fTexto')?.value ?? '';
    const tipoPublicacion = document.getElementById('fTipoPublicacion')?.value ?? '';
    const tipo = document.getElementById('fTipo')?.value ?? '';
    const cat = document.getElementById('fCategoria')?.value ?? '';
    const min = document.getElementById('fPrecioMin')?.value ?? '';
    const max = document.getElementById('fPrecioMax')?.value ?? '';
    const orden = document.getElementById('fOrden')?.value ?? 'recientes';

    window.evProductosFiltro.q = String(q).trim();
    window.evProductosFiltro.tipo_publicacion = normalizarTipoPublicacion(tipoPublicacion, true);
    window.evProductosFiltro.tipo = String(tipo).trim();
    window.evProductosFiltro.categoria = String(cat).trim();
    window.evProductosFiltro.min = String(min).trim();
    window.evProductosFiltro.max = String(max).trim();
    window.evProductosFiltro.orden = String(orden).trim() || 'recientes';
  }

  function resetFiltrosUI() {
    const setVal = (id, val) => {
      const el = document.getElementById(id);
      if (!el) return;
      el.value = val;
    };

    setVal('fTexto', '');
    setVal('fTipoPublicacion', '');
    setVal('fTipo', '');
    setVal('fPrecioMin', '');
    setVal('fPrecioMax', '');
    setVal('fOrden', 'recientes');

    const selCat = document.getElementById('fCategoria');
    if (selCat) {
      selCat.innerHTML = `<option value="">Todas</option>`;
      selCat.value = '';
      selCat.disabled = true;
    }

    window.evProductosFiltro.q = '';
    window.evProductosFiltro.tipo_publicacion = '';
    window.evProductosFiltro.tipo = '';
    window.evProductosFiltro.categoria = '';
    window.evProductosFiltro.min = '';
    window.evProductosFiltro.max = '';
    window.evProductosFiltro.orden = 'recientes';
  }

  const evAddPreview = { inited: false };

  function evEnsurePreviewAgregar() {
    if (evAddPreview.inited) return evAddPreview;

    const container = document.getElementById('previewMount');
    if (!container) return null;

    container.innerHTML = `
      <aside class="ev-preview-panel" aria-label="Vista previa de la publicación">
        <div class="ev-preview-panel-head">
          <div>
            <div class="ev-preview-kicker">Vista previa</div>
            <div class="ev-preview-heading">Así lo verá tu vecino</div>
          </div>
          <div id="evMetaKindAdd" class="ev-preview-kind ev-preview-kind-producto">Producto</div>
        </div>

        <div id="evPreviewWrapperAdd" class="ev-preview-area ev-preview-media-card is-empty">
          <div class="ev-preview-title"><span><i class="bi bi-images me-1"></i>Imagen principal</span></div>

          <div id="evPreviewEmptyAdd" class="ev-preview-empty">
            <div class="ev-preview-empty-icon"><i class="bi bi-image"></i></div>
            <div>
              <div class="ev-preview-empty-title">Tu imagen principal aparecerá aquí</div>
              <div class="ev-preview-empty-text">Agrega una foto clara para generar confianza.</div>
            </div>
          </div>

          <div class="ev-preview-main"><img id="evPreviewMainImgAdd" alt="Vista previa"></div>
          <div id="evPreviewThumbsAdd" class="ev-preview-thumbs"></div>
        </div>

        <div class="ev-preview-summary-card">
          <h6 id="evMetaTitleAdd" class="ev-preview-summary-title">Título</h6>
          <div id="evMetaPriceAdd" class="ev-preview-summary-price">S/ 0.00</div>
          <div class="ev-preview-summary-label">Detalles</div>
          <p id="evMetaDescAdd" class="ev-preview-summary-desc">La descripción aparecerá aquí.</p>
        </div>

        <div class="ev-preview-tips">
          <div class="ev-preview-tip"><i class="bi bi-shield-check"></i><span>Se enviará a revisión antes de publicarse.</span></div>
          <div class="ev-preview-tip"><i class="bi bi-stars"></i><span>Usa imágenes claras para generar confianza.</span></div>
        </div>
      </aside>
    `;

    evAddPreview.wrapper   = document.getElementById('evPreviewWrapperAdd');
    evAddPreview.empty     = document.getElementById('evPreviewEmptyAdd');
    evAddPreview.mainImg   = document.getElementById('evPreviewMainImgAdd');
    evAddPreview.thumbs    = document.getElementById('evPreviewThumbsAdd');
    evAddPreview.metaKind  = document.getElementById('evMetaKindAdd');
    evAddPreview.metaTitle = document.getElementById('evMetaTitleAdd');
    evAddPreview.metaPrice = document.getElementById('evMetaPriceAdd');
    evAddPreview.metaDesc  = document.getElementById('evMetaDescAdd');
    evAddPreview.inited    = true;

    const modal = document.getElementById('modalAgregarPublicacion');
    if (modal && !modal.dataset.evMetaBound) {
      modal.dataset.evMetaBound = '1';

      const updateMetaLive = () => {
        const titleInput = modal.querySelector('input[name="titulo"]');
        const priceInput = modal.querySelector('input[name="precio"]');
        const descInput  = modal.querySelector('textarea[name="descripcion"]');

        const title    = titleInput?.value?.trim() || 'Título';
        const priceRaw = priceInput?.value || '';
        const desc     = descInput?.value?.trim() || 'La descripción aparecerá aquí.';

        const n      = Number(priceRaw || 0);
        const precio = isNaN(n) ? '0.00' : n.toFixed(2);

        const tipoPublicacion = getTipoPublicacionFromForm(modal.querySelector('#formAgregarPublicacion'), false);
        actualizarPreviewMetaTipo(modal, tipoPublicacion);

        evAddPreview.metaTitle.textContent = title;
        evAddPreview.metaPrice.textContent = `S/ ${precio}`;
        evAddPreview.metaDesc.textContent = prepararDescripcionPreview(desc);
      };

      modal.querySelector('input[name="titulo"]')?.addEventListener('input', updateMetaLive);
      modal.querySelector('input[name="precio"]')?.addEventListener('input', updateMetaLive);
      modal.querySelector('textarea[name="descripcion"]')?.addEventListener('input', updateMetaLive);
      modal.querySelectorAll('input[name="tipo_publicacion"]').forEach((radio) => {
        radio.addEventListener('change', updateMetaLive);
      });
    }

    return evAddPreview;
  }

  function evPintarPreviewAgregar(fotos) {
    const st = evEnsurePreviewAgregar();
    if (!st) return;

    const mainBox = st.mainImg?.closest('.ev-preview-main');

    if (!Array.isArray(fotos) || !fotos.length) {
      st.wrapper.style.display = '';
      st.wrapper.classList.add('is-empty');
      if (st.empty) st.empty.style.display = '';
      if (mainBox) mainBox.style.display = 'none';
      st.thumbs.innerHTML = '';
      st.thumbs.style.display = 'none';
      st.mainImg.src = '';
      return;
    }

    st.wrapper.style.display = '';
    st.wrapper.classList.remove('is-empty');
    if (st.empty) st.empty.style.display = 'none';
    if (mainBox) mainBox.style.display = '';
    st.thumbs.style.display = '';
    st.mainImg.src = fotos[0]?.url || '';

    st.thumbs.innerHTML = '';
    fotos.forEach((f, idx) => {
      const src = f?.url || '';
      if (!src) return;
      const d = document.createElement('div');
      d.className = 'ev-preview-thumb' + (idx === 0 ? ' active' : '');
      const im = document.createElement('img');
      im.src = src;
      d.appendChild(im);
      d.addEventListener('click', () => {
        st.mainImg.src = src;
        [...st.thumbs.querySelectorAll('.ev-preview-thumb')].forEach((node, i) => {
          node.classList.toggle('active', i === idx);
        });
      });
      st.thumbs.appendChild(d);
    });
  }

  (function initUploaderAgregarModule() {
    const MAX_MB = 5;
    const MAX_FILES = 10;

    const state = { inited:false, fotos:[] };
    const els = { modal:null, form:null, input:null, tiles:null, dropZone:null, btnClear:null, cntHeader:null, cntToolbar:null };

    function validarArchivo(file) {
      const okTipo = (file.type || '').startsWith('image/');
      const okPeso = file.size <= MAX_MB * 1024 * 1024;
      if (!okTipo) { evNotify('info','Aviso','Solo se permiten archivos de imagen.'); return false; }
      if (!okPeso) { evNotify('info','Aviso',`"${file.name}" supera ${MAX_MB} MB.`); return false; }
      return true;
    }

    function setCount() {
      const count = state.fotos.length;
      if (els.cntHeader)  els.cntHeader.textContent  = String(count);
      if (els.cntToolbar) els.cntToolbar.textContent = String(count);
      if (els.form)       els.form.dataset.evFotosAddCount = String(count);
    }

    function revokeAll() { state.fotos.forEach(f => { if (f?.url) URL.revokeObjectURL(f.url); }); }

    function renderTiles() {
      if (!els.tiles) return;
      els.tiles.innerHTML = '';

      if (state.fotos.length === 0) {
        setCount();
        evSyncDropzoneState(els.tiles);
        return;
      }

      state.fotos.forEach((f) => {
        const tile = document.createElement('div');
        tile.className = 'ev-tile';

        const img = document.createElement('img');
        img.src = f.url;

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ev-tile-remove';
        del.textContent = '×';
        del.addEventListener('click', (ev) => {
          ev.stopPropagation();
          const idx = state.fotos.findIndex(x => x.id === f.id);
          if (idx !== -1) {
            const removed = state.fotos.splice(idx, 1)[0];
            if (removed?.url) URL.revokeObjectURL(removed.url);
          }
          paint();
        });

        tile.append(img, del);
        els.tiles.appendChild(tile);
      });

      if (state.fotos.length < MAX_FILES) {
        const add = document.createElement('div');
        add.className = 'ev-tile ev-tile-add';
        add.innerHTML = `
          <div class="ico"><i class="bi bi-plus-lg"></i></div>
          <div class="t1">Agregar</div>
        `;
        add.addEventListener('click', () => els.input?.click());
        els.tiles.appendChild(add);
      }

      setCount();
      evSyncDropzoneState(els.tiles);
    }

    function paint() {
      renderTiles();
      evPintarPreviewAgregar(state.fotos);
    }

    function agregarArchivos(fileList) {
      const nuevos = Array.from(fileList || []);
      if (!nuevos.length) return;

      for (const file of nuevos) {
        if (state.fotos.length >= MAX_FILES) {
          evNotify('info','Aviso',`Máximo ${MAX_FILES} imágenes.`);
          break;
        }
        if (!validarArchivo(file)) continue;

        state.fotos.push({
          id: 'new_' + (crypto?.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random()),
          file,
          url: URL.createObjectURL(file)
        });
      }

      paint();
    }

    function clearAll() {
      revokeAll();
      state.fotos = [];
      if (els.input) els.input.value = '';
      paint();
    }

    /*
     * API interna para producto.js: se utiliza al cambiar el modo de
     * publicación para impedir que imágenes de un servicio se reutilicen
     * accidentalmente en un producto, o viceversa.
     */
    window.evLimpiarImagenesAgregar = clearAll;

    function bindEvents() {
      if (state.inited || !els.modal) return;

      els.dropZone?.addEventListener('click', () => els.input?.click());

      els.input?.addEventListener('change', (e) => {
        agregarArchivos(e.target.files || []);
        e.target.value = '';
      });

      const bindDrop = (node) => {
        if (!node) return;
        ['dragenter','dragover'].forEach(evt => {
          node.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); node.classList.add('drag-over'); });
        });
        ['dragleave','dragend','drop'].forEach(evt => {
          node.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); node.classList.remove('drag-over'); });
        });
        node.addEventListener('drop', (e) => {
          agregarArchivos(e.dataTransfer?.files || []);
        });
      };
      bindDrop(els.dropZone);

      if (els.tiles) {
        ['dragenter','dragover','dragleave','dragend','drop'].forEach(evt => {
          els.tiles.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); });
        });
        els.tiles.addEventListener('drop', (e) => {
          agregarArchivos(e.dataTransfer?.files || []);
        });
      }

      els.btnClear?.addEventListener('click', clearAll);

      els.modal.addEventListener('hidden.bs.modal', () => {
        clearAll();
        try { els.form?.reset(); } catch (_) {}
      });

      state.inited = true;
      paint();
    }

    function hydrateRefs() {
      const modal = document.getElementById('modalAgregarPublicacion');
      if (!modal) return false;

      els.modal      = modal;
      els.form       = modal.querySelector('#formAgregarPublicacion');
      els.input      = modal.querySelector('#inputImagenes');
      els.tiles      = modal.querySelector('#evTiles');
      els.dropZone   = modal.querySelector('#dropZone');
      els.btnClear   = modal.querySelector('#btnLimpiarImagenes');
      els.cntHeader  = modal.querySelector('#contadorImagenes');
      els.cntToolbar = modal.querySelector('#contadorImagenesToolbar');

      return !!(els.form && els.input && els.dropZone && els.tiles);
    }

    window.evGetEstadoImagenesAgregar = function () {
      return { nuevas: state.fotos.slice() };
    };

    document.addEventListener('shown.bs.modal', (e) => {
      if (e.target && e.target.id === 'modalAgregarPublicacion') {
        if (hydrateRefs()) bindEvents();
      }
    });

  })();

  const evEditPreview = { inited:false };

  function evEnsurePreviewEditar() {
    if (evEditPreview.inited) return evEditPreview;

    const container = document.getElementById('evPreviewWrapperEditContainer');
    if (!container) return null;

    container.innerHTML = `
      <aside class="ev-preview-panel" aria-label="Vista previa de la publicación">
        <div class="ev-preview-panel-head">
          <div>
            <div class="ev-preview-kicker">Vista previa</div>
            <div class="ev-preview-heading">Así lo verá tu vecino</div>
          </div>
          <div id="evMetaKindEdit" class="ev-preview-kind ev-preview-kind-producto">Producto</div>
        </div>

        <div id="evPreviewWrapperEdit" class="ev-preview-area ev-preview-media-card is-empty">
          <div class="ev-preview-title"><span><i class="bi bi-images me-1"></i>Imagen principal</span></div>

          <div id="evPreviewEmptyEdit" class="ev-preview-empty">
            <div class="ev-preview-empty-icon"><i class="bi bi-image"></i></div>
            <div>
              <div class="ev-preview-empty-title">Tu imagen principal aparecerá aquí</div>
              <div class="ev-preview-empty-text">Agrega una foto clara para generar confianza.</div>
            </div>
          </div>

          <div class="ev-preview-main"><img id="evPreviewMainImgEdit" alt="Vista previa"></div>
          <div id="evPreviewThumbsEdit" class="ev-preview-thumbs"></div>
        </div>

        <div class="ev-preview-summary-card">
          <h6 id="evMetaTitleEdit" class="ev-preview-summary-title">Título</h6>
          <div id="evMetaPriceEdit" class="ev-preview-summary-price">S/ 0.00</div>
          <div class="ev-preview-summary-label">Detalles</div>
          <p id="evMetaDescEdit" class="ev-preview-summary-desc">La descripción aparecerá aquí.</p>
        </div>

        <div class="ev-preview-tips">
          <div class="ev-preview-tip"><i class="bi bi-shield-check"></i><span>Los cambios pueden volver a revisión.</span></div>
          <div class="ev-preview-tip"><i class="bi bi-stars"></i><span>Usa imágenes claras para generar confianza.</span></div>
        </div>
      </aside>
    `;

    evEditPreview.wrapper   = document.getElementById('evPreviewWrapperEdit');
    evEditPreview.empty     = document.getElementById('evPreviewEmptyEdit');
    evEditPreview.mainImg   = document.getElementById('evPreviewMainImgEdit');
    evEditPreview.thumbs    = document.getElementById('evPreviewThumbsEdit');
    evEditPreview.metaKind  = document.getElementById('evMetaKindEdit');
    evEditPreview.metaTitle = document.getElementById('evMetaTitleEdit');
    evEditPreview.metaPrice = document.getElementById('evMetaPriceEdit');
    evEditPreview.metaDesc  = document.getElementById('evMetaDescEdit');
    evEditPreview.inited    = true;

    const modal = document.getElementById('modalEditarPublicacion');
    if (modal && !modal.dataset.evMetaBound) {
      modal.dataset.evMetaBound = '1';
      const updateMetaLive = () => {
        const title   = modal.querySelector('#edit_titulo')?.value?.trim() || 'Título';
        const priceRaw= modal.querySelector('#edit_precio')?.value || '';
        const desc    = modal.querySelector('#edit_descripcion')?.value?.trim() || 'La descripción aparecerá aquí.';
        const n       = Number(priceRaw || 0);
        const precio  = isNaN(n) ? '0.00' : n.toFixed(2);

        const tipoPublicacion = getTipoPublicacionFromForm(modal.querySelector('#formEditarPublicacion'), true);
        actualizarPreviewMetaTipo(modal, tipoPublicacion);

        evEditPreview.metaTitle.textContent = title;
        evEditPreview.metaPrice.textContent = `S/ ${precio}`;
        evEditPreview.metaDesc.textContent = prepararDescripcionPreview(desc);
      };

      modal.querySelector('#edit_titulo')?.addEventListener('input', updateMetaLive);
      modal.querySelector('#edit_precio')?.addEventListener('input', updateMetaLive);
      modal.querySelector('#edit_descripcion')?.addEventListener('input', updateMetaLive);
      modal.querySelectorAll('input[name="edit_tipo_publicacion"]').forEach((radio) => {
        radio.addEventListener('change', updateMetaLive);
      });
    }

    return evEditPreview;
  }

  function evPintarPreviewEditar(prod, fotos) {
    const st = evEnsurePreviewEditar();
    if (!st) return;

    const titulo = (prod?.titulo || '').trim() || 'Título';
    const n      = Number(prod?.precio || 0);
    const precio = isNaN(n) ? '0.00' : n.toFixed(2);
    const desc   = (prod?.descripcion || '').trim() || 'La descripción aparecerá aquí.';

    const modal = document.getElementById('modalEditarPublicacion');
    actualizarPreviewMetaTipo(modal, getTipoPublicacionItem(prod));

    st.metaTitle.textContent = titulo;
    st.metaPrice.textContent = `S/ ${precio}`;
    st.metaDesc.textContent = prepararDescripcionPreview(desc);

    const mainBox = st.mainImg?.closest('.ev-preview-main');

    if (!Array.isArray(fotos) || !fotos.length) {
      st.wrapper.style.display = '';
      st.wrapper.classList.add('is-empty');
      if (st.empty) st.empty.style.display = '';
      if (mainBox) mainBox.style.display = 'none';
      st.thumbs.innerHTML = '';
      st.thumbs.style.display = 'none';
      st.mainImg.src = '';
      return;
    }

    st.wrapper.style.display = '';
    st.wrapper.classList.remove('is-empty');
    if (st.empty) st.empty.style.display = 'none';
    if (mainBox) mainBox.style.display = '';
    st.thumbs.style.display = '';
    st.mainImg.src = evGetFotoSrc(fotos[0]) || '';

    st.thumbs.innerHTML = '';
    fotos.forEach((f, idx) => {
      const src = evGetFotoSrc(f);
      if (!src) return;
      const d = document.createElement('div');
      d.className = 'ev-preview-thumb' + (idx === 0 ? ' active' : '');
      const im = document.createElement('img');
      im.src = src;
      d.appendChild(im);
      d.addEventListener('click', () => {
        st.mainImg.src = src;
        [...st.thumbs.querySelectorAll('.ev-preview-thumb')].forEach((node, i) => {
          node.classList.toggle('active', i === idx);
        });
      });
      st.thumbs.appendChild(d);
    });
  }

  (function initUploaderEditarModule(){
    const MAX_MB = 5;
    const MAX_FILES = 10;

    const state = { inited:false, fotosExistentes:[], fotosNuevas:[], eliminadas:[] };
    const els = { modal:null, form:null, input:null, tiles:null, dropZone:null, btnClear:null, cntHeader:null, cntToolbar:null };

    function validarArchivo(file) {
      const okTipo = (file.type || '').startsWith('image/');
      const okPeso = file.size <= MAX_MB * 1024 * 1024;
      if (!okTipo) { evNotify('info','Aviso','Solo se permiten archivos de imagen.'); return false; }
      if (!okPeso) { evNotify('info','Aviso',`"${file.name}" supera ${MAX_MB} MB.`); return false; }
      return true;
    }

    function buildActivasExistentes(){
      return state.fotosExistentes.filter(f => !state.eliminadas.includes(f.id));
    }
    function buildTodas() {
      const existentes = buildActivasExistentes().map(f => ({ url: f.src }));
      const nuevas     = state.fotosNuevas.map(f => ({ url: f.url }));
      return existentes.concat(nuevas);
    }

    function setCount() {
      const count = buildTodas().length;
      if (els.cntHeader)  els.cntHeader.textContent  = String(count);
      if (els.cntToolbar) els.cntToolbar.textContent = String(count);
      if (els.form)       els.form.dataset.evFotosEditCount = String(count);
    }

    function renderTiles(prod) {
      if (!els.tiles) return;
      els.tiles.innerHTML = '';

      const todas = buildTodas();
      const existentesActivos = buildActivasExistentes();

      existentesActivos.forEach((f) => {
        const tile = document.createElement('div');
        tile.className = 'ev-tile';

        const img = document.createElement('img');
        img.src = f.src;

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ev-tile-remove';
        del.textContent = '×';
        del.addEventListener('click', (ev) => {
          ev.stopPropagation();
          if (!state.eliminadas.includes(f.id)) state.eliminadas.push(f.id);
          paint(prod);
        });

        tile.append(img, del);
        els.tiles.appendChild(tile);
      });

      state.fotosNuevas.forEach((f) => {
        const tile = document.createElement('div');
        tile.className = 'ev-tile';

        const img = document.createElement('img');
        img.src = f.url;

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ev-tile-remove';
        del.textContent = '×';
        del.addEventListener('click', (ev) => {
          ev.stopPropagation();
          const idx = state.fotosNuevas.findIndex(x => x.id === f.id);
          if (idx !== -1) {
            const removed = state.fotosNuevas.splice(idx,1)[0];
            if (removed?.url) URL.revokeObjectURL(removed.url);
          }
          paint(prod);
        });

        tile.append(img, del);
        els.tiles.appendChild(tile);
      });

      if (todas.length < MAX_FILES) {
        const add = document.createElement('div');
        add.className = 'ev-tile ev-tile-add';
        add.innerHTML = `
          <div class="ico"><i class="bi bi-plus-lg"></i></div>
          <div class="t1">Agregar</div>
        `;
        add.addEventListener('click', () => els.input?.click());
        els.tiles.appendChild(add);
      }

      setCount();
      evSyncDropzoneState(els.tiles);
    }

    function paint(prod) {
      const todas = buildTodas();
      renderTiles(prod);
      evPintarPreviewEditar(prod, todas);
    }

    function agregarArchivos(fileList, prod) {
      const nuevos = Array.from(fileList || []);
      if (!nuevos.length) return;

      const actuales = buildTodas().length;

      for (const file of nuevos) {
        if (actuales + state.fotosNuevas.length >= MAX_FILES) {
          evNotify('info','Aviso',`Máximo ${MAX_FILES} imágenes.`);
          break;
        }
        if (!validarArchivo(file)) continue;

        state.fotosNuevas.push({
          id: 'new_' + (crypto?.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random()),
          file,
          url: URL.createObjectURL(file)
        });
      }

      paint(prod);
    }

    function bindEvents() {
      if (state.inited || !els.modal) return;

      els.input?.addEventListener('change', (e) => {
        const prod = els.modal?._evProdActual || {};
        agregarArchivos(e.target.files || [], prod);
        e.target.value = '';
      });

      const bindDrop = (node) => {
        if (!node) return;
        ['dragenter','dragover'].forEach(evt => {
          node.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); node.classList.add('drag-over'); });
        });
        ['dragleave','dragend','drop'].forEach(evt => {
          node.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); node.classList.remove('drag-over'); });
        });
        node.addEventListener('drop', (e) => {
          const prod = els.modal?._evProdActual || {};
          agregarArchivos(e.dataTransfer?.files || [], prod);
        });
        node.addEventListener('click', () => els.input?.click());
      };

      bindDrop(els.dropZone);

      if (els.tiles) {
        ['dragenter','dragover','dragleave','dragend','drop'].forEach(evt => {
          els.tiles.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); });
        });
        els.tiles.addEventListener('drop', (e) => {
          const prod = els.modal?._evProdActual || {};
          agregarArchivos(e.dataTransfer?.files || [], prod);
        });
      }

      els.btnClear?.addEventListener('click', () => {
        state.fotosExistentes = [];
        state.fotosNuevas.forEach(f => f.url && URL.revokeObjectURL(f.url));
        state.fotosNuevas = [];
        state.eliminadas = [];
        paint(els.modal?._evProdActual || {});
      });

      state.inited = true;
    }

    function resetStateFromBackend(prod, fotosBackend) {
      state.fotosExistentes = [];
      state.fotosNuevas.forEach(f => f.url && URL.revokeObjectURL(f.url));
      state.fotosNuevas = [];
      state.eliminadas = [];

      if (Array.isArray(fotosBackend)) {
        fotosBackend.forEach((f, idx) => {
          const src = evGetFotoSrc(f);
          if (!src) return;
          const id  = evGetFotoId(f, idx);
          state.fotosExistentes.push({ id, src });
        });
      }

      if (els.modal) els.modal._evProdActual = prod || {};
      paint(prod || {});

      window.evGetEstadoImagenesEditar = function () {
        return {
          existentes: state.fotosExistentes,
          eliminadas: state.eliminadas.slice(),
          nuevas: state.fotosNuevas
        };
      };
    }

    window.evInitUploaderEditar = function(prod, fotosBackend) {
      const modal = document.getElementById('modalEditarPublicacion');
      if (!modal) return;

      els.modal      = modal;
      els.form       = modal.querySelector('#formEditarPublicacion');
      els.input      = modal.querySelector('#inputImagenesEdit');
      els.tiles      = modal.querySelector('#evTilesEdit');
      els.dropZone   = modal.querySelector('#dropZoneEdit');
      els.btnClear   = modal.querySelector('#btnLimpiarImagenesEdit');
      els.cntHeader  = modal.querySelector('#contadorImagenesEdit');
      els.cntToolbar = modal.querySelector('#contadorImagenesToolbarEdit');

      bindEvents();
      resetStateFromBackend(prod, fotosBackend);
    };
  })();

  async function cargarProductoEditar(codProducto) {
    try {
      const resp = await fetch(`${EV_API_BASE}/api/producto/${codProducto}`, { method: 'GET' });
      const data = await resp.json().catch(() => ({}));

      if (await evHandleAuthResponse(resp, data)) return;

      if (!data.ok) {
        evNotify("error", "Error", data.mensaje || "Error al cargar la publicación.");
        return;
      }

      const prod  = data.data.producto;
      const fotos = data.data.imagenes || [];
      const tipoPublicacion = getTipoPublicacionItem(prod);

      document.querySelector("#edit_id").value          = prod.codigo_producto;
      document.querySelector("#edit_titulo").value      = prod.titulo || "";
      document.querySelector("#edit_precio").value      = prod.precio || "";
      document.querySelector("#edit_estado").value      = tipoPublicacion === 'servicio' ? 'NoAplica' : (prod.estado || "Nuevo");
      document.querySelector("#edit_descripcion").value = prod.descripcion || "";

      const formEdit = document.getElementById('formEditarPublicacion');
      const modalEdit = document.getElementById('modalEditarPublicacion');
      setTipoPublicacionRadio(formEdit, tipoPublicacion, true);
      aplicarTipoPublicacionUI(modalEdit, tipoPublicacion);

      const editTipoAtencion = document.querySelector("#edit_tipoAtencionProducto");
      if (editTipoAtencion) {
        editTipoAtencion.value = tipoPublicacion === 'servicio'
          ? "no_requiere_preparacion"
          : (prod.tipo_atencion_producto || "no_requiere_preparacion");
      }

      const comboTipo = document.getElementById("edit_comboTipo");
      const comboCat  = document.getElementById("edit_comboCategoria");
      if (comboTipo) comboTipo.dataset.valorRegistrado = prod.codigo_tipo || "";
      if (comboCat)  comboCat.dataset.valorRegistrado  = prod.codigo_categoria || "";

      if (window.evInitUploaderEditar) window.evInitUploaderEditar(prod, fotos);
      else evPintarPreviewEditar(prod, fotos);

      const modalEl = document.getElementById("modalEditarPublicacion");
      if (modalEl) {
        const modal = evGetStaticModal('modalEditarPublicacion');
        requestAnimationFrame(() => {
          modal?.show();
          aplicarTipoPublicacionUI(modalEl, tipoPublicacion);
          requestAnimationFrame(setEvVh);
        });
      }

      if (window.evInitComboTipoCategoriaEdit) {
        window.evInitComboTipoCategoriaEdit(prod.codigo_tipo, prod.codigo_categoria);
      }

    } catch (e) {
      console.error("Error cargando publicación:", e);
      evNotify("error", "Error", "No se pudo cargar los datos.");
    }
  }

  async function confirmarYAnular(id) {
    if (!id) return;

    const ok = await evConfirm({
      icon: 'warning',
      title: 'Anular publicación',
      text: '¿Seguro que deseas anular esta publicación? Ya no estará disponible.',
      confirmText: 'Sí, anular',
      cancelText: 'Cancelar',
      confirmBtnClass: 'btn btn-danger me-2'
    });
    if (!ok) return;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/producto/${id}/anular`, { method: 'POST' });
      const data = await resp.json().catch(() => ({}));

      if (await evHandleAuthResponse(resp, data)) return;

      if (!resp.ok || !data.ok) {
        evNotify('error', 'Error', data.mensaje || data.error || 'No se pudo anular la publicación.');
        return;
      }

      if (data?.servicios_piloto) {
        actualizarResumenServiciosPiloto(data.servicios_piloto);
      }

      evNotify('success', 'Publicación anulada', data.mensaje || 'La publicación ha sido anulada correctamente.');
      window.evCargarProductos?.();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al anular la publicación.');
    }
  }

  async function confirmarYPublicar(id) {
    if (!id) return;

    const publicacion = (window.evProductosCache || []).find((p) => String(p?.codigo_producto ?? '') === String(id));
    const tipoPublicacion = getTipoPublicacionItem(publicacion || {});
    const esServicio = tipoPublicacion === 'servicio';
    const resumen = normalizarResumenServiciosPiloto(evServiciosPiloto);

    if (esServicio && resumen.alcanzado) {
      mostrarLimiteServiciosPiloto(resumen);
      return;
    }

    const ok = await evConfirm({
      icon: esServicio ? 'info' : 'question',
      title: esServicio ? 'Enviar servicio a revisión' : 'Enviar a revisión',
      text: esServicio
        ? `Este servicio se enviará a revisión sin costo durante el piloto. ${textoCuposServiciosPiloto(resumen)} Aún no se mostrará en el marketplace hasta que soporte lo apruebe.`
        : 'Al enviar a revisión, tu publicación quedará en estado Pendiente hasta que soporte la apruebe. Aún no se mostrará en el marketplace.',
      confirmText: esServicio ? 'Sí, enviar servicio' : 'Sí, enviar',
      cancelText: 'Cancelar',
      confirmBtnClass: esServicio ? 'btn btn-success me-2' : 'btn btn-success me-2'
    });
    if (!ok) return;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/producto/${id}/publicar`, { method: 'POST' });
      const data = await resp.json().catch(() => ({}));

      if (await evHandleAuthResponse(resp, data)) return;

      if (!resp.ok || !data.ok) {
        const codigoError = String(data?.error || data?.codigo || '').trim();

        if (codigoError === 'LIMITE_SERVICIOS_ALCANZADO') {
          mostrarLimiteServiciosPiloto(data?.servicios_piloto || evServiciosPiloto);
          return;
        }

        evNotify('error', 'Error', data.mensaje || data.error || 'No se pudo enviar la publicación a revisión.');
        return;
      }

      if (data?.servicios_piloto) {
        actualizarResumenServiciosPiloto(data.servicios_piloto);
      }

      evNotify('success', 'Enviado a revisión', data.mensaje || 'Solicitud enviada. La publicación quedó Pendiente de aprobación.');
      window.evCargarProductos?.();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al enviar la publicación a revisión.');
    }
  }

  function evShortenCategoriaPlaceholder(scope) {
    const root = scope || document;
    const selects = root.querySelectorAll
      ? root.querySelectorAll('#comboCategoria, #edit_comboCategoria, select[name="categoria"], select[name="edit_comboCategoria"]')
      : [];

    selects.forEach((sel) => {
      if (!sel) return;
      const first = sel.querySelector('option[value=""]') || sel.options?.[0];
      if (!first) return;

      const current = String(first.textContent || '').trim().toLowerCase();
      if (
        current === '' ||
        current.includes('selecciona un tipo primero') ||
        current.includes('selecciona un tipo') ||
        current.includes('tipo primero') ||
        current.includes('selecciona una categoría') ||
        current.includes('selecciona una categoria')
      ) {
        first.textContent = 'Primero elige tipo';
      }
    });
  }

  function evClearFieldErrors(form) {
    if (!form) return;
    form.querySelectorAll('.is-invalid, .ev-field-invalid').forEach((el) => {
      el.classList.remove('is-invalid', 'ev-field-invalid');
      el.removeAttribute('aria-invalid');
    });
    form.querySelectorAll('.ev-field-error').forEach((el) => el.remove());
  }

  function evGetFieldForValidation(form, isEdit, key) {
    if (!form) return null;

    const mapAdd = {
      titulo: 'input[name="titulo"]',
      precio: 'input[name="precio"]',
      tipo: '#comboTipo, select[name="comboTipo"]',
      categoria: '#comboCategoria, select[name="categoria"]',
      tipoAtencion: '#tipoAtencionProducto',
      descripcion: 'textarea[name="descripcion"]'
    };

    const mapEdit = {
      titulo: '#edit_titulo',
      precio: '#edit_precio',
      tipo: '#edit_comboTipo',
      categoria: '#edit_comboCategoria',
      tipoAtencion: '#edit_tipoAtencionProducto',
      descripcion: '#edit_descripcion'
    };

    return form.querySelector((isEdit ? mapEdit : mapAdd)[key] || '');
  }

  function evShowFieldError(form, isEdit, key, message) {
    const field = evGetFieldForValidation(form, isEdit, key);
    if (!field) return null;

    field.classList.add('is-invalid', 'ev-field-invalid');
    field.setAttribute('aria-invalid', 'true');

    const holder = field.closest('.mb-3, .col-12, .col-md-6') || field.parentElement || form;
    const err = document.createElement('div');
    err.className = 'ev-field-error';
    err.textContent = message;
    holder.appendChild(err);

    if (!field.dataset.evErrorClearBound) {
      field.dataset.evErrorClearBound = '1';
      ['input', 'change'].forEach((evt) => {
        field.addEventListener(evt, () => {
          field.classList.remove('is-invalid', 'ev-field-invalid');
          field.removeAttribute('aria-invalid');

          const parent = field.closest('.mb-3, .col-12, .col-md-6') || field.parentElement;
          parent?.querySelectorAll('.ev-field-error').forEach((node) => node.remove());
        });
      });
    }

    return field;
  }

  function evValidatePublicacionForm(form, isEdit, data) {
    evClearFieldErrors(form);

    const errores = [];

    const add = (key, message) => {
      const field = evShowFieldError(form, isEdit, key, message);
      errores.push({ key, message, field });
    };

    if (!String(data.titulo || '').trim()) {
      add('titulo', data.tipoPublicacion === 'servicio'
        ? 'Ingresa el nombre del servicio.'
        : 'Ingresa un título para la publicación.');
    }

    if (!Number.isFinite(data.precio) || data.precio <= 0) {
      add('precio', 'Ingresa un precio mayor a 0.');
    }

    if (!String(data.comboTipo || '').trim()) {
      add('tipo', 'Selecciona un tipo.');
    }

    if (!String(data.categoria || '').trim()) {
      add('categoria', 'Selecciona una categoría.');
    }

    if (data.tipoPublicacion === 'producto' && !String(data.tipoAtencionProducto || '').trim()) {
      add('tipoAtencion', 'Selecciona el tipo de atención.');
    }

    if (!String(data.descripcion || '').trim()) {
      add('descripcion', data.tipoPublicacion === 'servicio'
        ? 'Describe qué incluye el servicio.'
        : 'Ingresa una descripción.');
    }

    if (!errores.length) return true;

    const first = errores.find(e => e.field)?.field;
    if (first) {
      try {
        first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.setTimeout(() => first.focus({ preventScroll: true }), 220);
      } catch (_) {
        try { first.focus(); } catch (__) {}
      }
    }

    evNotify('warning', 'Revisa la publicación', 'Completa los campos marcados antes de guardar.');
    return false;
  }


  async function registrarProducto(form) {
    const btnGuardar = form.querySelector('.btn-guardar') || form.querySelector('button[type="submit"]');

    const setSaving = (saving) => {
      if (!btnGuardar) return;
      btnGuardar.disabled = saving;
      btnGuardar.classList.toggle('saving', saving);
    };

    const tipoPublicacion = getTipoPublicacionFromForm(form, false);
    const label = tipoPublicacionLabel(tipoPublicacion).toLowerCase();

    const titulo      = form.querySelector('input[name="titulo"]')?.value?.trim() || '';
    const precioRaw   = form.querySelector('input[name="precio"]')?.value || '';
    const estado      = tipoPublicacion === 'servicio'
      ? 'NoAplica'
      : (form.querySelector('select[name="estado"]')?.value || 'NoAplica');
    const descripcion = form.querySelector('textarea[name="descripcion"]')?.value?.trim() || '';

    const comboTipo   = form.querySelector('#comboTipo')?.value || form.querySelector('select[name="comboTipo"]')?.value || '';
    const categoria   = form.querySelector('#comboCategoria')?.value || form.querySelector('select[name="categoria"]')?.value || '';
    const tipoAtencionSelect = form.querySelector('#tipoAtencionProducto');
    const tipoAtencionProducto = tipoPublicacion === 'servicio'
      ? 'no_requiere_preparacion'
      : (tipoAtencionSelect?.dataset?.evAutoTipoAtencion || tipoAtencionSelect?.value || 'no_requiere_preparacion');

    const precio = Number(precioRaw || 0);
    if (!evValidatePublicacionForm(form, false, {
      tipoPublicacion,
      label,
      titulo,
      precio,
      comboTipo,
      categoria,
      tipoAtencionProducto,
      descripcion
    })) {
      return;
    }

    const estadoImgs = typeof window.evGetEstadoImagenesAgregar === 'function'
      ? window.evGetEstadoImagenesAgregar()
      : { nuevas: [] };

    const nuevas = Array.isArray(estadoImgs.nuevas) ? estadoImgs.nuevas : [];

    const fd = new FormData();
    fd.append('tipo_publicacion', tipoPublicacion);
    fd.append('titulo', titulo);
    fd.append('precio', precio.toString());
    fd.append('estado', estado);
    fd.append('comboTipo', comboTipo);
    fd.append('categoria', categoria);
    fd.append('descripcion', descripcion);
    fd.append('tipo_atencion_producto', tipoAtencionProducto);

    nuevas.forEach((item) => {
      if (item && item.file instanceof File) fd.append('imagenes[]', item.file);
    });

    try {
      setSaving(true);

      const resp = await fetch(`${EV_API_BASE}/api/producto/registrar`, {
        method: 'POST',
        body: fd
      });

      const data = await resp.json().catch(() => ({}));

      if (await evHandleAuthResponse(resp, data)) return;

      if (!resp.ok || !data.ok) {
        const extra = Array.isArray(data.errores) && data.errores.length ? `\n\n• ${data.errores.join('\n• ')}` : '';
        evNotify('error', 'Error', (data.mensaje || data.error || 'No se pudo registrar la publicación.') + extra);
        return;
      }

      evNotify('success', `${tipoPublicacionLabel(tipoPublicacion)} registrado`, data.mensaje || `${tipoPublicacionLabel(tipoPublicacion)} registrado como borrador. Presiona "Publicar" para enviarlo a revisión.`);

      const modal = evGetStaticModal('modalAgregarPublicacion');
      modal?.hide();

      window.evCargarProductos?.();
      try { form.reset(); } catch (_) {}
      resetTipoPublicacionAgregarUI();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al registrar la publicación.');
    } finally {
      setSaving(false);
    }
  }

  async function actualizarProducto(form) {
    const btnGuardar = form.querySelector('.btn-guardar') || form.querySelector('button[type="submit"]');

    const setSaving = (saving) => {
      if (!btnGuardar) return;
      btnGuardar.disabled = saving;
      btnGuardar.classList.toggle('saving', saving);
    };

    const tipoPublicacion = getTipoPublicacionFromForm(form, true);
    const label = tipoPublicacionLabel(tipoPublicacion).toLowerCase();

    const id          = form.querySelector('#edit_id')?.value || '';
    const titulo      = form.querySelector('#edit_titulo')?.value?.trim() || '';
    const precioRaw   = form.querySelector('#edit_precio')?.value || '';
    const estado      = tipoPublicacion === 'servicio'
      ? 'NoAplica'
      : (form.querySelector('#edit_estado')?.value || 'NoAplica');
    const descripcion = form.querySelector('#edit_descripcion')?.value?.trim() || '';

    const comboTipo   = form.querySelector('#edit_comboTipo')?.value || '';
    const categoria   = form.querySelector('#edit_comboCategoria')?.value || '';
    const tipoAtencionSelect = form.querySelector('#edit_tipoAtencionProducto');
    const tipoAtencionProducto = tipoPublicacion === 'servicio'
      ? 'no_requiere_preparacion'
      : (tipoAtencionSelect?.dataset?.evAutoTipoAtencion || tipoAtencionSelect?.value || 'no_requiere_preparacion');

    if (!id) {
      evNotify('error','Error','No se encontró el código de la publicación.');
      return;
    }

    const precio = Number(precioRaw || 0);
    if (!evValidatePublicacionForm(form, true, {
      tipoPublicacion,
      label,
      titulo,
      precio,
      comboTipo,
      categoria,
      tipoAtencionProducto,
      descripcion
    })) {
      return;
    }

    const estadoImgs = typeof window.evGetEstadoImagenesEditar === 'function'
      ? window.evGetEstadoImagenesEditar()
      : { eliminadas: [], nuevas: [] };

    const fd = new FormData();
    fd.append('tipo_publicacion', tipoPublicacion);
    fd.append('titulo', titulo);
    fd.append('precio', precio.toString());
    fd.append('estado', estado);
    fd.append('comboTipo', comboTipo);
    fd.append('categoria', categoria);
    fd.append('descripcion', descripcion);
    fd.append('tipo_atencion_producto', tipoAtencionProducto);
    fd.append('imagenes_eliminadas', JSON.stringify(estadoImgs.eliminadas || []));

    (estadoImgs.nuevas || []).forEach((item) => {
      if (item && item.file instanceof File) fd.append('imagenes_nuevas[]', item.file);
    });

    try {
      setSaving(true);

      const resp = await fetch(`${EV_API_BASE}/api/producto/${id}/actualizar`, { method:'POST', body: fd });
      const data = await resp.json().catch(() => ({}));

      if (await evHandleAuthResponse(resp, data)) return;

      if (!resp.ok || !data.ok) {
        const codigoError = String(data?.error || data?.codigo || '').trim();
        if (codigoError === 'LIMITE_SERVICIOS_ALCANZADO') {
          mostrarLimiteServiciosPiloto(data?.servicios_piloto || evServiciosPiloto);
          return;
        }

        evNotify('error', 'Error', data.mensaje || data.error || 'No se pudo actualizar la publicación.');
        return;
      }

      if (data?.servicios_piloto) {
        actualizarResumenServiciosPiloto(data.servicios_piloto);
      }

      evNotify('success', 'Publicación actualizada', data.mensaje || 'Los cambios se guardaron correctamente.');

      const modal = evGetStaticModal('modalEditarPublicacion');
      modal?.hide();

      window.evCargarProductos?.();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al actualizar la publicación.');
    } finally {
      setSaving(false);
    }
  }

  function uiEstadoVisible(visibleNum, ultimaRevision) {
    const visible = Number(visibleNum ?? -1);
    const rev = ultimaRevision || null;
    const comentario = limpiarComentarioSistema(rev?.comentario || '');
    const estadoNuevoRev = Number(rev?.estado_nuevo ?? -1);

    const hasObs = (
      visible === 1 &&
      comentario.length > 0 &&
      estadoNuevoRev === 1 &&
      !String(rev?.comentario || '').startsWith(REENVIO_PREFIX)
    );

    if (hasObs) return { text: 'Observado', cls: 'ev-chip ev-chip-amber ev-chip-status' };

    if (visible === 0) return { text: 'Borrador', cls: 'ev-chip ev-chip-gray ev-chip-status' };
    if (visible === 1) return { text: 'Pendiente', cls: 'ev-chip ev-chip-amber ev-chip-status' };
    if (visible === 2) return { text: 'Aprobado',  cls: 'ev-chip ev-chip-green ev-chip-status' };
    if (visible === 3) return { text: 'Rechazado', cls: 'ev-chip ev-chip-red ev-chip-status' };
    if (visible === 4) return { text: 'Anulado', cls: 'ev-chip ev-chip-red ev-chip-status' };
    return { text: '—', cls: 'ev-chip ev-chip-gray ev-chip-status' };
  }

  function uiAccionPublicar(visibleNum) {
    if (Number(visibleNum) === 0) {
      return { show: true, text: 'Publicar', cls: 'ev-chip ev-chip-orange', disabled: false };
    }
    return { show: false };
  }

  function evGetStatusKey(p) {
    const visible = Number(p?.visible ?? -1);
    const rev = p?.ultima_revision || null;
    const comentarioOriginal = String(rev?.comentario || '').trim();
    const comentario = limpiarComentarioSistema(comentarioOriginal);

    const isObs = (
      visible === 1 &&
      comentario &&
      Number(rev?.estado_nuevo ?? -1) === 1 &&
      !comentarioOriginal.startsWith(REENVIO_PREFIX)
    );
    if (isObs) return 'observado';

    if (visible === 2) return 'aprobado';
    if (visible === 1) return 'pendiente';
    if (visible === 0) return 'borrador';
    if (visible === 3) return 'rechazado';
    if (visible === 4) return 'anulado';
    return 'all';
  }

  function evGetMensajeSoporte(p) {
    const rev = p?.ultima_revision || null;
    const comentarioOriginal = String(rev?.comentario || '').trim();
    const comentario = limpiarComentarioSistema(comentarioOriginal);
    if (!comentario) return '';

    if (comentarioOriginal.startsWith(REENVIO_PREFIX)) return '';

    const visible = Number(p?.visible ?? -1);
    const estadoNuevoRev = Number(rev?.estado_nuevo ?? -1);

    const esObservado = visible === 1 && estadoNuevoRev === 1;
    const esRechazado = visible === 3;

    if (esObservado || esRechazado) return comentario;
    return '';
  }

  function evMatchTab(p) {
    const tab = String(window.evProductosFiltro?.tab || 'all');
    if (!tab || tab === 'all') return true;
    return evGetStatusKey(p) === tab;
  }

  function evUpdateTabsUI(counts) {
    const set = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = String(val ?? 0);
    };

    set('evTabCountAll', counts.all);
    set('evTabCountAll2', counts.all);
    set('evTabCountAprobado', counts.aprobado);
    set('evTabCountObservado', counts.observado);
    set('evTabCountRechazado', counts.rechazado);
    set('evTabCountPendiente', counts.pendiente);
    set('evTabCountBorrador', counts.borrador);
    set('evTabCountAnulado', counts.anulado);

    const tab = String(window.evProductosFiltro?.tab || 'all');
    document.querySelectorAll('.ev-tab[data-tab]').forEach(btn => {
      const isActive = btn.getAttribute('data-tab') === tab;
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });
  }

  function filtrarItems(items) {
    const f = window.evProductosFiltro || {};
    const q = String(f.q || '').trim().toLowerCase();
    const tipoPublicacion = normalizarTipoPublicacion(f.tipo_publicacion || '', true);
    const tipo = String(f.tipo || '').trim();
    const cat  = String(f.categoria || '').trim();

    const min = Number(String(f.min || '').trim());
    const max = Number(String(f.max || '').trim());
    const hasMin = !Number.isNaN(min) && String(f.min || '').trim() !== '';
    const hasMax = !Number.isNaN(max) && String(f.max || '').trim() !== '';

    let out = items.slice();

    if (q) {
      out = out.filter(p => {
        const t  = String(p.titulo || '').toLowerCase();
        const d  = String(p.descripcion || '').toLowerCase();
        const c  = String(p.categoria_nombre || p.categoria || '').toLowerCase();
        const tp = String(p.tipo_nombre || p.tipo || '').toLowerCase();
        const pub = tipoPublicacionLabel(getTipoPublicacionItem(p)).toLowerCase();
        const ms = String(evGetMensajeSoporte(p) || '').toLowerCase();
        return (t.includes(q) || d.includes(q) || c.includes(q) || tp.includes(q) || pub.includes(q) || ms.includes(q));
      });
    }

    if (tipoPublicacion) out = out.filter(p => getTipoPublicacionItem(p) === tipoPublicacion);
    if (tipo) out = out.filter(p => String(p.codigo_tipo ?? '') === tipo);
    if (cat)  out = out.filter(p => String(p.codigo_categoria ?? '') === cat);

    if (hasMin) out = out.filter(p => Number(p.precio || 0) >= min);
    if (hasMax) out = out.filter(p => Number(p.precio || 0) <= max);

    return out;
  }

  function ordenarItems(items) {
    const ord = String(window.evProductosFiltro?.orden || 'recientes');
    const arr = items.slice();

    const byTitulo = (a, b) => String(a.titulo || '').localeCompare(String(b.titulo || ''), 'es', { sensitivity: 'base' });
    const byPrecio = (a, b) => Number(a.precio || 0) - Number(b.precio || 0);

    const byRecientes = (a, b) => {
      const da = new Date(a.updated_at || a.created_at || 0).getTime();
      const db = new Date(b.updated_at || b.created_at || 0).getTime();
      return db - da;
    };

    if (ord === 'precio_asc') arr.sort(byPrecio);
    else if (ord === 'precio_desc') arr.sort((a, b) => byPrecio(b, a));
    else if (ord === 'titulo_asc') arr.sort(byTitulo);
    else if (ord === 'titulo_desc') arr.sort((a, b) => byTitulo(b, a));
    else arr.sort(byRecientes);

    return arr;
  }

  async function cargarProductos() {
    const table = document.getElementById('tablaPublicaciones');
    const tbody = table?.querySelector('tbody');
    if (!table || !tbody) return;

    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">Cargando publicaciones…</td></tr>`;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/producto/listar`, { method: 'GET' });
      const data = await resp.json().catch(() => ({}));

      if (await evHandleAuthResponse(resp, data)) return;

      if (!resp.ok || !data.ok) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">${escAttr(data.mensaje || data.error || 'No se pudo obtener el listado.')}</td></tr>`;
        return;
      }

      const items = Array.isArray(data.data) ? data.data : [];
      window.evProductosCache = items.slice();

      const resumenServiciosFallback = {
        maximo: EV_SERVICIOS_PILOTO_MAX,
        activos: items.filter((item) => (
          getTipoPublicacionItem(item) === 'servicio'
          && [1, 2].includes(Number(item?.visible ?? -1))
        )).length
      };
      actualizarResumenServiciosPiloto(data?.servicios_piloto || resumenServiciosFallback);

      const counts = { all: items.length, aprobado: 0, observado: 0, rechazado: 0, pendiente: 0, borrador: 0, anulado: 0 };
      items.forEach(p => {
        const k = evGetStatusKey(p);
        if (k === 'aprobado') counts.aprobado++;
        else if (k === 'observado') counts.observado++;
        else if (k === 'rechazado') counts.rechazado++;
        else if (k === 'pendiente') counts.pendiente++;
        else if (k === 'borrador') counts.borrador++;
        else if (k === 'anulado') counts.anulado++;
      });
      evUpdateTabsUI(counts);

      const filtrados = ordenarItems(filtrarItems(items).filter(evMatchTab));

      const lblMeta = document.getElementById('evLblMeta');
      const lblFooterLeft = document.getElementById('evLblFooterLeft');
      if (lblMeta) lblMeta.textContent = `Mostrando ${filtrados.length} registros`;
      if (lblFooterLeft) lblFooterLeft.textContent = `Mostrando ${filtrados.length} de ${items.length}`;

      if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">Aún no tienes publicaciones registradas.</td></tr>`;
        return;
      }

      if (!filtrados.length) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">No hay publicaciones para los filtros seleccionados.</td></tr>`;
        return;
      }

      tbody.innerHTML = filtrados.map((p) => {
        const id = Number(p.codigo_producto ?? 0);
        const cod = String(id || '').padStart(6, '0');

        const tipoPublicacion = getTipoPublicacionItem(p);
        const tipoPublicacionTxt = tipoPublicacionLabel(tipoPublicacion);
        const tipoPublicacionCls = tipoPublicacionBadgeClass(tipoPublicacion);

        const tituloRaw = (p.titulo || '').toString();
        const titulo = escAttr(tituloRaw.substring(0, 80));

        const precio = Number(p.precio || 0).toFixed(2);

        const tipoRaw = (p.tipo_nombre || p.tipo || p.nombre_tipo || '').toString().trim();
        const catRaw  = (p.categoria_nombre || p.categoria || p.nombre_categoria || '').toString().trim();

        const tipo = escAttr(tipoRaw || '-');
        const categoria = escAttr(catRaw || '-');

        const descFull = (p.descripcion || '').toString();
        const descShort = descFull.length > 90 ? (descFull.substring(0, 90) + '…') : descFull;
        const descSafe = escAttr(descShort || '-');

        const visible = Number(p.visible ?? -1);

        const visUI = uiEstadoVisible(visible, p.ultima_revision);
        const pubUI = uiAccionPublicar(visible);

        const statusKey = evGetStatusKey(p);
        const mensajeSoporte = evGetMensajeSoporte(p);

        const mensajeHtml = mensajeSoporte
          ? `
              <span class="ev-msg-support-box ${statusKey === 'observado' || statusKey === 'rechazado' ? 'is-alert' : ''}">
                <span class="ev-msg-support-title">Mensaje de soporte</span>
                <span class="ev-msg-support-text">${escAttr(mensajeSoporte)}</span>
              </span>
            `
          : `
              <span class="ev-msg-support-box is-empty">
                <span class="ev-msg-support-title">Mensaje de soporte</span>
                <span class="ev-msg-support-text">Sin observaciones</span>
              </span>
            `;

        const canEdit   = (statusKey === 'borrador' || statusKey === 'observado');
        const canAnular = (statusKey === 'borrador' || statusKey === 'pendiente' || statusKey === 'aprobado' || statusKey === 'observado');

        const disableEditar = canEdit ? '' : 'disabled';
        const disableAnular = canAnular ? '' : 'disabled';

        const isAprobado  = (visible === 2);
        const isAnulado   = (visible === 4);
        const isRechazado = (visible === 3);

        const trStyle = isAnulado ? 'style="opacity:.62;filter:saturate(.85);"' : '';

        return `
          <tr ${trStyle}>
            <td data-label="Código" class="text-center"><span class="ev-code">${cod}</span></td>
            <td data-label="Publicación" class="text-center"><span class="${tipoPublicacionCls}">${tipoPublicacionTxt}</span></td>
            <td data-label="Título" class="td-trunc" title="${titulo}">${titulo || '-'}</td>
            <td data-label="Precio" class="text-end">S/ ${precio}</td>
            <td data-label="Tipo" class="td-trunc" title="${tipo}">${tipo}</td>
            <td data-label="Categoría" class="td-trunc" title="${categoria}">${categoria}</td>
            <td data-label="Descripción" class="td-trunc" title="${escAttr(descFull)}">${descSafe}</td>
            <td data-label="Mensaje de soporte">${mensajeHtml}</td>
            <td data-label="Estado de publicación" class="text-center">
              <span class="${visUI.cls}">${visUI.text}</span>
            </td>
            <td data-label="Acciones" class="text-end">
              <div class="ev-actions">
                ${
                  isAnulado
                    ? ''
                    : isAprobado
                      ? `
                          <button type="button" class="ev-chip ev-chip-red" data-action="anular" data-id="${id}" ${disableAnular}>Anular</button>
                        `
                      : isRechazado
                        ? ''
                        : `
                            <button type="button" class="ev-chip ev-chip-green" data-action="editar" data-id="${id}" ${disableEditar}>Editar</button>
                            <button type="button" class="ev-chip ev-chip-red" data-action="anular" data-id="${id}" ${disableAnular}>Anular</button>
                            ${pubUI.show ? `<button type="button" class="${pubUI.cls}" data-action="publicar" data-id="${id}">${pubUI.text}</button>` : ''}
                          `
                }
              </div>
            </td>
          </tr>
        `;
      }).join('');

    } catch (err) {
      console.error(err);
      tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">Ocurrió un error al cargar las publicaciones.</td></tr>`;
    }
  }

  window.evCargarProductos = cargarProductos;

  function isProductosViewPresent() {
    return !!document.getElementById('tablaPublicaciones');
  }

  function bindOnceGlobalEvents() {
    if (document.body.dataset.evProductosBound === '1') return;
    document.body.dataset.evProductosBound = '1';

    document.addEventListener('click', (e) => {
      if (window.__EV_AUTH_REDIRECTING__ === true) return;

      const tabBtn = e.target.closest('.ev-tab[data-tab]');
      if (tabBtn) {
        window.evProductosFiltro.tab = tabBtn.getAttribute('data-tab') || 'all';
        window.evCargarProductos?.();
        return;
      }

      if (e.target.closest('#btnAgregarPublicacion')) {
        const modal = evGetStaticModal('modalAgregarPublicacion');
        if (!modal) return;
        resetTipoPublicacionAgregarUI();
        requestAnimationFrame(() => {
          modal.show();
          requestAnimationFrame(setEvVh);
        });
        return;
      }

      if (e.target.closest('#btnRefrescarMisProductos')) {
        window.evCargarProductos?.();
        return;
      }

      if (e.target.closest('#btnLimpiarFiltros')) {
        (async () => {
          resetFiltrosUI();
          await cargarTiposFiltro();
          window.evProductosFiltro.tab = 'all';
          window.evCargarProductos?.();
        })();
        return;
      }

      if (e.target.closest('#btnBuscarPublicacion')) {
        const m = evGetStaticModal('modalBuscarPublicacion');
        m?.show();
        return;
      }

      const btnEditar = e.target.closest('[data-action="editar"][data-id]');
      if (btnEditar && !btnEditar.disabled) { cargarProductoEditar(btnEditar.getAttribute('data-id')); return; }

      const btnAnular = e.target.closest('[data-action="anular"][data-id]');
      if (btnAnular && !btnAnular.disabled) { confirmarYAnular(btnAnular.getAttribute('data-id')); return; }

      const btnPublicar = e.target.closest('[data-action="publicar"][data-id]');
      if (btnPublicar && !btnPublicar.disabled) { confirmarYPublicar(btnPublicar.getAttribute('data-id')); return; }
    });

    document.addEventListener('input', debounce((e) => {
      if (window.__EV_AUTH_REDIRECTING__ === true) return;

      if (e.target && e.target.id === 'fTexto') {
        syncFiltrosFromUI();
        window.evCargarProductos?.();
      }
    }, 250));

    document.addEventListener('change', (e) => {
      if (window.__EV_AUTH_REDIRECTING__ === true) return;

      if (e.target && e.target.matches('input[name="tipo_publicacion"]')) {
        aplicarTipoPublicacionUI(document.getElementById('modalAgregarPublicacion'), e.target.value);
        return;
      }

      if (e.target && e.target.matches('input[name="edit_tipo_publicacion"]')) {
        aplicarTipoPublicacionUI(document.getElementById('modalEditarPublicacion'), e.target.value);
        return;
      }

      if (e.target && e.target.id === 'fTipoPublicacion') {
        (async () => {
          syncFiltrosFromUI();
          window.evProductosFiltro.tipo = '';
          window.evProductosFiltro.categoria = '';

          const selTipo = document.getElementById('fTipo');
          if (selTipo) selTipo.value = '';

          const selCat = document.getElementById('fCategoria');
          if (selCat) {
            selCat.innerHTML = `<option value="">Todas</option>`;
            selCat.value = '';
            selCat.disabled = true;
          }

          await cargarTiposFiltro();
          window.evCargarProductos?.();
        })();
        return;
      }

      if (e.target && e.target.id === 'fTipo') {
        (async () => {
          syncFiltrosFromUI();
          window.evProductosFiltro.categoria = '';
          await cargarCategoriasFiltro(e.target.value);
          syncFiltrosFromUI();
          window.evCargarProductos?.();
        })();
        return;
      }

      if (e.target && e.target.id === 'fCategoria') {
        syncFiltrosFromUI();
        window.evCargarProductos?.();
        return;
      }

      if (e.target && (e.target.id === 'fPrecioMin' || e.target.id === 'fPrecioMax' || e.target.id === 'fOrden')) {
        syncFiltrosFromUI();
        window.evCargarProductos?.();
        return;
      }
    });


    document.addEventListener('change', (e) => {
      const t = e.target;
      if (!t || !t.matches) return;
      if (t.matches('#comboTipo, #edit_comboTipo')) {
        const modal = t.closest('.ev-modal');
        window.setTimeout(() => evShortenCategoriaPlaceholder(modal || document), 250);
      }
    }, true);

    document.addEventListener('submit', (e) => {
      const form = e.target;

      if (window.__EV_AUTH_REDIRECTING__ === true) {
        e.preventDefault();
        return;
      }

      if (form && form.id === 'formFiltrosMisProductos') {
        e.preventDefault();
        syncFiltrosFromUI();
        window.evCargarProductos?.();
        return;
      }

      if (form && form.id === 'formAgregarPublicacion') {
        e.preventDefault();
        registrarProducto(form);
        return;
      }

      if (form && form.id === 'formEditarPublicacion') {
        e.preventDefault();
        actualizarProducto(form);
        return;
      }

      if (form && form.id === 'formBuscarPublicacion') {
        e.preventDefault();
        const fd = new FormData(form);
        window.evProductosFiltro.q = String(fd.get('q') || '').trim();
        window.evProductosFiltro.tab = 'all';
        const m = evGetStaticModal('modalBuscarPublicacion');
        m?.hide();
        window.evCargarProductos?.();
        return;
      }
    });

    document.addEventListener('shown.bs.modal', (e) => {
      if (e.target && e.target.id === 'modalAgregarPublicacion') {
        bindTipoPublicacionUI();
        aplicarTipoPublicacionUI(e.target, getTipoPublicacionFromForm(document.getElementById('formAgregarPublicacion'), false));
        evShortenCategoriaPlaceholder(e.target);
      }
      if (e.target && e.target.id === 'modalEditarPublicacion') {
        bindTipoPublicacionUI();
        aplicarTipoPublicacionUI(e.target, getTipoPublicacionFromForm(document.getElementById('formEditarPublicacion'), true));
        evShortenCategoriaPlaceholder(e.target);
      }
    });

    document.addEventListener('hidden.bs.modal', (e) => {
      if (e.target && e.target.id === 'modalAgregarPublicacion') {
        resetTipoPublicacionAgregarUI();
      }
    });
  }

  async function initIfNeeded() {
    bindOnceGlobalEvents();
    evMountAllModalsToBody();
    bindTipoPublicacionUI();

    const tipoSel = document.getElementById('fTipo');
    if (tipoSel && !tipoSel.dataset.evLoaded) {
      tipoSel.dataset.evLoaded = '1';
      await cargarTiposFiltro();

      const prevTipo = String(window.evProductosFiltro?.tipo || '');
      if (prevTipo) await cargarCategoriasFiltro(prevTipo);
    }

    const tabla = document.getElementById('tablaPublicaciones');
    if (isProductosViewPresent() && tabla && !tabla.dataset.evLoaded) {
      tabla.dataset.evLoaded = '1';
      cargarProductos();
    }
  }

  document.addEventListener('DOMContentLoaded', () => { initIfNeeded(); });

  const target = document.getElementById('contenido-principal') || document.body;
  const obs = new MutationObserver(() => { initIfNeeded(); });
  obs.observe(target, { childList: true, subtree: true });

})();