/* views/js/marketplace.js */
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').toString().replace(/\/+$/, '');
  const LOG_PREFIX = '[MARKETPLACE]';
  const POLLING_MS = 15000;

  const CONDO_NOMBRE_RESUMEN = (typeof window !== 'undefined' && window.EV_CONDOMINIO_NOMBRE)
    ? window.EV_CONDOMINIO_NOMBRE
    : 'tu condominio';

  let refs = {
    gridAllWrapper: null,
    gridServicios: null,
    gridProductos: null,
    resumenResultados: null,
    searchInput: null,
    emptyState: null,
    selectOrdenar: null,
    scopeButtons: [],
    selectCategoriaProductos: null,
    countServicios: null,
    countProductos: null,
    wrapServicios: null,
    wrapProductos: null,
    wrapCategoriaProductos: null
  };

  let publicaciones = [];
  let textoBusqueda = '';
  let criterioOrden = 'recientes';
  let scope = 'todos';
  let categoriaProductoId = 0;

  let tipoIdProducto = 0;
  let tipoIdServicio = 0;

  let pollingTimer = null;
  let pollingEnCurso = false;
  let marketplaceInicializado = false;

  function log()  { if (console && console.log)  console.log(LOG_PREFIX, ...arguments); }
  function warn() { if (console && console.warn) console.warn(LOG_PREFIX, ...arguments); }
  function err()  { if (console && console.error) console.error(LOG_PREFIX, ...arguments); }

  const notify = (icon, title, text) => {
    if (typeof window.evNotify === 'function') {
      window.evNotify(icon, title, text);
      return;
    }
    if (window.Swal?.fire) {
      Swal.fire({
        icon,
        title,
        text,
        confirmButtonText: 'Aceptar',
        customClass: { confirmButton: 'btn btn-outline-success' },
        buttonsStyling: false
      });
      return;
    }
    alert(title ? `${title}\n\n${text}` : text);
  };

  function normalizar(str) {
    return (str || '')
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }

  function formatPrecio(valor) {
    const n = Number(valor || 0);
    if (!isFinite(n)) return 'S/ 0.00';
    return 'S/ ' + n.toFixed(2);
  }

  function escapeHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function getBasePath() {
    if (!BASE) return '';
    const u = BASE.startsWith('http') ? new URL(BASE) : null;
    const p = u ? (u.pathname || '') : BASE;
    return p.replace(/\/+$/, '');
  }

  function normalizeRelPath(relPath) {
    const basePath = getBasePath();
    let p = (relPath || '').toString().trim();
    if (!p) return '';

    if (/^https?:\/\//i.test(p)) return p;

    p = p.replace(/\\/g, '/').replace(/\/+/g, '/');

    if (basePath && p.startsWith(basePath + '/')) {
      p = p.slice(basePath.length);
    }

    if (!p.startsWith('/')) p = '/' + p;
    return p;
  }

  function buildImgUrl(relPath) {
    const placeholder = (BASE ? (BASE + '/public/img/placeholder-ev.png') : '/public/img/placeholder-ev.png');
    if (!relPath) return placeholder;

    const p = normalizeRelPath(relPath);
    if (!p) return placeholder;
    if (/^https?:\/\//i.test(p)) return p;

    return (BASE || '') + p;
  }

  async function fetchJsonRobusto(url, opts) {
    const resp = await fetch(url, opts);
    const text = await resp.text();
    let json = null;
    try { json = text ? JSON.parse(text) : {}; } catch (_) { json = null; }
    return { resp, text, json };
  }

  function getArrayFromPayload(payload) {
    if (!payload) return [];
    if (Array.isArray(payload)) return payload;

    if (typeof payload === 'object') {
      if (Array.isArray(payload.data)) return payload.data;
      if (Array.isArray(payload.items)) return payload.items;
      if (payload.data && Array.isArray(payload.data.items)) return payload.data.items;
    }
    return [];
  }

  function normalizarListaDesdeAPI(payload) {
    return getArrayFromPayload(payload);
  }

  function normalizarItem(raw) {
    const o = raw && typeof raw === 'object' ? raw : {};

    const id = o.codigo_producto ?? o.id ?? '';
    const titulo = o.titulo ?? o.nombre ?? '';
    const descripcion = o.descripcion ?? o.detalle ?? '';
    const precio = o.precio ?? 0;

    const codigo_tipo = Number(o.codigo_tipo || 0) || 0;
    const codigo_categoria = Number(o.codigo_categoria || 0) || 0;

    const tipo_nombre = o.tipo_nombre ?? o.tipo ?? '';
    const tipo_slug = o.tipo_slug ?? '';
    const categoria_nombre = o.categoria_nombre ?? o.categoria ?? '';
    const categoria_slug = o.categoria_slug ?? '';

    const es_potenciado = o.es_potenciado ?? o.potenciado ?? o.destacado ?? 0;
    const vendedorDisponible = Number(o.vendedor_disponible ?? o.disponibilidad_pedidos_vendedor ?? 0) === 1 ? 1 : 0;

    const imagen_portada =
      o.imagen_portada_url ??
      o.imagen_portada ??
      o.ruta_portada ??
      o.imagen ??
      '';

    const orden_reciente =
      o.created_ts ??
      o.orden_reciente ??
      o.created_at ??
      id ??
      0;

    return {
      ...o,
      __id: id,
      __titulo: titulo,
      __descripcion: descripcion,
      __precio: precio,
      __codigo_tipo: codigo_tipo,
      __codigo_categoria: codigo_categoria,
      __tipo_nombre: tipo_nombre,
      __tipo_slug: tipo_slug,
      __categoria_nombre: categoria_nombre,
      __categoria_slug: categoria_slug,
      __es_potenciado: es_potenciado,
      __imagen_portada: imagen_portada,
      __orden_reciente: orden_reciente,
      __vendedor_disponible: vendedorDisponible
    };
  }

  function setResumen(txt) {
    if (refs.resumenResultados) refs.resumenResultados.textContent = txt;
  }

  function showEmpty(msg) {
    if (refs.gridServicios) refs.gridServicios.innerHTML = '';
    if (refs.gridProductos) refs.gridProductos.innerHTML = '';
    if (refs.emptyState) {
      refs.emptyState.style.display = '';
      refs.emptyState.textContent = msg || 'No encontramos publicaciones con los filtros actuales.';
    }
  }

  function hideEmpty() {
    if (refs.emptyState) refs.emptyState.style.display = 'none';
  }

  function ensureGridCSS() {
    const ID = 'ev-mp-grid-fix';
    if (document.getElementById(ID)) return;

    const css = `
#mp_grid_servicios, #mp_grid_productos{
  display:grid !important;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)) !important;
  gap:16px !important;
  align-items:start !important;
  justify-items:start !important;
  width:100% !important;
}
#mp_grid_servicios .ev-mp-card,
#mp_grid_productos .ev-mp-card{
  width:100% !important;
  max-width:340px !important;
  justify-self:start !important;
}
#mp_grid_servicios .ev-mp-card-media img,
#mp_grid_productos .ev-mp-card-media img{
  width:100% !important;
  height:170px !important;
  object-fit:cover !important;
  display:block !important;
}
#mp_grid_servicios .ev-mp-card-body,
#mp_grid_productos .ev-mp-card-body{
  padding:14px !important;
}
#mp_grid_servicios .ev-mp-card-actions,
#mp_grid_productos .ev-mp-card-actions{
  display:flex !important;
  gap:10px !important;
}
#mp_grid_servicios .ev-mp-card-actions .btn,
#mp_grid_productos .ev-mp-card-actions .btn{
  flex:1 1 auto !important;
  white-space:nowrap !important;
}
    `.trim();

    const style = document.createElement('style');
    style.id = ID;
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);
  }

  function capturarRefs() {
    refs.gridAllWrapper       = document.getElementById('mp_grid_publicaciones');
    refs.gridServicios        = document.getElementById('mp_grid_servicios');
    refs.gridProductos        = document.getElementById('mp_grid_productos');
    refs.countServicios       = document.getElementById('mp_count_servicios');
    refs.countProductos       = document.getElementById('mp_count_productos');

    refs.resumenResultados    = document.getElementById('mp_resumen_resultados');
    refs.searchInput          = document.getElementById('mp_busqueda');
    refs.emptyState           = document.getElementById('mp_empty_state');
    refs.selectOrdenar        = document.getElementById('mp_orden');

    refs.scopeButtons         = Array.from(document.querySelectorAll('.ev-mp-seg-btn'));
    refs.selectCategoriaProductos = document.getElementById('mp_categoria_producto');

    refs.wrapServicios = refs.gridServicios ? (refs.gridServicios.closest('.ev-mp-section') || refs.gridServicios.parentElement) : null;
    refs.wrapProductos = refs.gridProductos ? (refs.gridProductos.closest('.ev-mp-section') || refs.gridProductos.parentElement) : null;

    if (refs.selectCategoriaProductos) {
      refs.wrapCategoriaProductos =
        refs.selectCategoriaProductos.closest('.ev-mp-cat-wrap') ||
        refs.selectCategoriaProductos.closest('.col') ||
        refs.selectCategoriaProductos.parentElement;
    }

    return !!refs.gridAllWrapper;
  }

  function applyScopeVisibility() {
    const showServ = (scope === 'todos' || scope === 'servicios');
    const showProd = (scope === 'todos' || scope === 'productos');

    if (refs.wrapServicios) refs.wrapServicios.style.display = showServ ? '' : 'none';
    if (refs.wrapProductos) refs.wrapProductos.style.display = showProd ? '' : 'none';

    if (refs.wrapCategoriaProductos) {
      refs.wrapCategoriaProductos.style.display = (scope === 'todos' || scope === 'productos') ? '' : 'none';
    }

    if (scope === 'servicios') {
      categoriaProductoId = 0;
      if (refs.selectCategoriaProductos) refs.selectCategoriaProductos.value = '0';
    }
  }

  function extraerDetalleDesdeRespuesta(json) {
    const root = (json && typeof json === 'object') ? json : {};
    const d = root.data && typeof root.data === 'object' ? root.data : null;

    const producto =
      (d && d.producto && typeof d.producto === 'object') ? d.producto :
      (root.producto && typeof root.producto === 'object') ? root.producto :
      (d && !d.producto ? d : null) ||
      {};

    const imagenes =
      (d && Array.isArray(d.imagenes)) ? d.imagenes :
      (root && Array.isArray(root.imagenes)) ? root.imagenes :
      (Array.isArray(producto.imagenes) ? producto.imagenes : []);

    return { producto, imagenes };
  }

  async function obtenerDetalleProducto(idProducto) {
    const url = `${BASE}/api/marketplace/producto/${encodeURIComponent(idProducto)}`;

    const { resp, text, json } = await fetchJsonRobusto(url, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    });

    if (resp.status === 401) {
      notify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
      setTimeout(() => { window.location.href = `${BASE}/`; }, 1200);
      return null;
    }

    if (resp.status === 409) {
      const msg = (json && (json.mensaje || json.error)) ? (json.mensaje || json.error) : 'No tienes residencia activa.';
      notify('warning', 'Residencia requerida', msg);
      const redir = (json && json.redirect) ? json.redirect : `${BASE}/mi-perfil`;
      setTimeout(() => { window.location.href = redir; }, 1200);
      return null;
    }

    if (!json) {
      err('DETALLE no devolvió JSON:', (text || '').slice(0, 400));
      notify('error', 'Error', 'La API devolvió una respuesta no válida.');
      return null;
    }

    if (!resp.ok || !json.ok) {
      const msg = json.mensaje || json.error || 'No se pudo obtener el detalle.';
      notify('error', 'Error', msg);
      return null;
    }

    return extraerDetalleDesdeRespuesta(json);
  }

  async function abrirModalDetalle(idProducto) {
    if (!idProducto) return;

    const modalEl        = document.getElementById('mp_modal_detalle');
    const imgPrincipalEl = document.getElementById('mp_modal_img_principal');
    const thumbsWrapper  = document.getElementById('mp_modal_thumbs');
    const tituloTxtEl    = document.getElementById('mp_modal_titulo_txt');
    const precioEl       = document.getElementById('mp_modal_precio');
    const catEl          = document.getElementById('mp_modal_categoria');
    const tipoEl         = document.getElementById('mp_modal_tipo');
    const descEl         = document.getElementById('mp_modal_descripcion');

    if (!modalEl || !imgPrincipalEl || !thumbsWrapper ||
        !tituloTxtEl || !precioEl || !catEl || !tipoEl || !descEl) {
      notify('error', 'Error UI', 'No se encontró el modal de detalle.');
      return;
    }

    try {
      const detalle = await obtenerDetalleProducto(idProducto);
      if (!detalle) return;

      const { producto, imagenes } = detalle;
      const pub = normalizarItem(producto);

      window.EV_MP_DETALLE_ACTUAL = {
        ...producto,
        imagenes: Array.isArray(imagenes) ? imagenes : [],
        vendedor_disponible: Number(pub.__vendedor_disponible || 0) === 1 ? 1 : 0
      };

      const titulo = pub.__titulo || 'Publicación';
      const precio = pub.__precio || 0;
      const desc   = pub.__descripcion || '—';

      tituloTxtEl.textContent = titulo;
      precioEl.textContent    = formatPrecio(precio);
      descEl.textContent      = desc;

      catEl.textContent  = pub.__categoria_nombre || '—';
      tipoEl.textContent = pub.__tipo_nombre || '—';

      const imgs = (Array.isArray(imagenes) ? imagenes : []).map((x) => {
        if (!x) return null;
        if (typeof x === 'string') return { url: x };
        return { url: x.url || x.ruta || x.path || x.imagen || x.src || '' };
      }).filter(Boolean);

      let portada = pub.__imagen_portada || '';
      if (!portada && imgs.length > 0) portada = imgs[0].url;

      imgPrincipalEl.src = buildImgUrl(portada);
      imgPrincipalEl.alt = titulo;

      thumbsWrapper.innerHTML = '';
      imgs.forEach((imgObj, index) => {
        const urlImg = buildImgUrl(imgObj.url);

        const thumbWrapper = document.createElement('div');
        thumbWrapper.className = 'ev-mp-modal-thumb';

        const thumbImg = document.createElement('img');
        thumbImg.src   = urlImg;
        thumbImg.alt   = `Imagen ${index + 1} de ${titulo}`;

        thumbWrapper.appendChild(thumbImg);

        thumbWrapper.addEventListener('click', () => {
          imgPrincipalEl.src = urlImg;
          document.querySelectorAll('.ev-mp-modal-thumb')
            .forEach(el => el.classList.remove('active'));
          thumbWrapper.classList.add('active');
        });

        thumbsWrapper.appendChild(thumbWrapper);
      });

      const firstThumb = thumbsWrapper.querySelector('.ev-mp-modal-thumb');
      if (firstThumb) firstThumb.classList.add('active');

      if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const modalDetalle = window.bootstrap.Modal.getOrCreateInstance(modalEl, {
          backdrop: 'static',
          keyboard: false
        });
        modalDetalle.show();
      } else if (window.$ && typeof window.$(modalEl).modal === 'function') {
        window.$(modalEl).modal({ backdrop: 'static', keyboard: false });
        window.$(modalEl).modal('show');
      }

    } catch (e) {
      err('EXCEPTION DETALLE', e);
      notify('error', 'Error inesperado', 'Ocurrió un problema al cargar el detalle.');
    }
  }

  function recalcularTotalSolicitud() {
    const cantidadEl = document.getElementById('mp_sp_cantidad');
    const totalEl = document.getElementById('mp_sp_total');
    const precioUnitarioEl = document.getElementById('mp_sp_precio_unitario');

    if (!cantidadEl || !totalEl || !precioUnitarioEl) return;

    const cantidad = Math.max(1, parseInt(cantidadEl.value || '1', 10) || 1);
    const precioUnitario = Number(precioUnitarioEl.value || 0);
    const total = cantidad * precioUnitario;

    cantidadEl.value = String(cantidad);
    totalEl.value = formatPrecio(total);
  }

  function actualizarVisibilidadEntregaProgramada() {
    const tipoEntregaEl = document.getElementById('mp_sp_tipo_entrega');
    const wrapProgramada = document.getElementById('mp_sp_wrap_programada');
    const fechaEl = document.getElementById('mp_sp_fecha_programada');

    if (!tipoEntregaEl || !wrapProgramada || !fechaEl) return;

    const esProgramada = tipoEntregaEl.value === 'programada';
    wrapProgramada.classList.toggle('d-none', !esProgramada);

    if (esProgramada) {
      const ahora = new Date();
      const max = new Date(ahora.getTime() + (48 * 60 * 60 * 1000));

      const toLocalInput = (d) => {
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
      };

      fechaEl.min = toLocalInput(ahora);
      fechaEl.max = toLocalInput(max);
    } else {
      fechaEl.value = '';
    }
  }

  function abrirModalSolicitudDesdeProducto(producto) {
    if (!producto || typeof producto !== 'object') {
      notify('error', 'Error', 'No se pudo preparar la solicitud.');
      return;
    }

    if (Number(producto.es_producto_propio || 0) === 1) {
      notify('warning', 'Acción no permitida', 'No puedes solicitar un pedido sobre tu propia publicación.');
      return;
    }

    if (Number(producto.vendedor_disponible || 0) !== 1) {
      notify('info', 'Vendedor no disponible', 'Este vecino no se encuentra disponible para recibir pedidos en este momento.');
      return;
    }

    const modalSolicitudEl = document.getElementById('mp_modal_solicitud');
    const modalDetalleEl   = document.getElementById('mp_modal_detalle');

    if (!modalSolicitudEl) {
      notify('error', 'Error UI', 'No se encontró el modal de solicitud.');
      return;
    }

    const codigoProductoEl   = document.getElementById('mp_sp_codigo_producto');
    const precioUnitarioEl   = document.getElementById('mp_sp_precio_unitario');
    const requierePrepEl     = document.getElementById('mp_sp_requiere_preparacion');
    const nombreProductoEl   = document.getElementById('mp_sp_nombre_producto');
    const cantidadEl         = document.getElementById('mp_sp_cantidad');
    const tipoEntregaEl      = document.getElementById('mp_sp_tipo_entrega');
    const direccionEl        = document.getElementById('mp_sp_direccion');
    const mensajeEl          = document.getElementById('mp_sp_mensaje');
    const fechaProgramadaEl  = document.getElementById('mp_sp_fecha_programada');

    if (codigoProductoEl) codigoProductoEl.value = producto.codigo_producto || '';
    if (precioUnitarioEl) precioUnitarioEl.value = producto.precio || 0;
    if (requierePrepEl) requierePrepEl.value = Number(producto.requiere_preparacion || 0) === 1 ? '1' : '0';
    if (nombreProductoEl) nombreProductoEl.value = producto.titulo || '';
    if (cantidadEl) cantidadEl.value = '1';
    if (tipoEntregaEl) tipoEntregaEl.value = 'inmediata';
    if (direccionEl) direccionEl.value = '';
    if (mensajeEl) mensajeEl.value = '';
    if (fechaProgramadaEl) fechaProgramadaEl.value = '';

    recalcularTotalSolicitud();
    actualizarVisibilidadEntregaProgramada();

    const abrirSolicitud = () => {
      if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const modalSolicitud = window.bootstrap.Modal.getOrCreateInstance(modalSolicitudEl, {
          backdrop: 'static',
          keyboard: false
        });
        modalSolicitud.show();
      } else if (window.$ && typeof window.$(modalSolicitudEl).modal === 'function') {
        window.$(modalSolicitudEl).modal({
          backdrop: 'static',
          keyboard: false
        });
        window.$(modalSolicitudEl).modal('show');
      }
    };

    if (
      modalDetalleEl &&
      modalDetalleEl.classList.contains('show') &&
      window.bootstrap &&
      typeof window.bootstrap.Modal === 'function'
    ) {
      const modalDetalle = window.bootstrap.Modal.getOrCreateInstance(modalDetalleEl, {
        backdrop: 'static',
        keyboard: false
      });

      const handler = () => {
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        abrirSolicitud();
      };

      modalDetalleEl.addEventListener('hidden.bs.modal', handler, { once: true });
      modalDetalle.hide();
      return;
    }

    abrirSolicitud();
  }

  async function enviarSolicitudPedido() {
    const codigoProductoEl  = document.getElementById('mp_sp_codigo_producto');
    const cantidadEl        = document.getElementById('mp_sp_cantidad');
    const tipoEntregaEl     = document.getElementById('mp_sp_tipo_entrega');
    const fechaProgramadaEl = document.getElementById('mp_sp_fecha_programada');
    const direccionEl       = document.getElementById('mp_sp_direccion');
    const mensajeEl         = document.getElementById('mp_sp_mensaje');
    const btnSubmit         = document.querySelector('#mp_form_solicitud_pedido button[type="submit"]');

    const codigoProducto   = Number(codigoProductoEl?.value || 0);
    const cantidad         = Math.max(1, parseInt(cantidadEl?.value || '1', 10) || 1);
    const tipoEntrega      = (tipoEntregaEl?.value || 'inmediata').trim();
    const fechaProgramada  = (fechaProgramadaEl?.value || '').trim();
    const direccionEntrega = (direccionEl?.value || '').trim();
    const mensajeComprador = (mensajeEl?.value || '').trim();

    if (!codigoProducto) {
      notify('warning', 'Validación', 'No se encontró la publicación seleccionada.');
      return;
    }

    if (!direccionEntrega) {
      notify('warning', 'Validación', 'Debes ingresar la dirección de entrega.');
      return;
    }

    if (tipoEntrega === 'programada' && !fechaProgramada) {
      notify('warning', 'Validación', 'Debes seleccionar la fecha y hora programada.');
      return;
    }

    try {
      if (btnSubmit) btnSubmit.disabled = true;

      const fd = new FormData();
      fd.append('codigo_producto', String(codigoProducto));
      fd.append('cantidad', String(cantidad));
      fd.append('tipo_entrega', tipoEntrega);
      fd.append('direccion_entrega', direccionEntrega);
      fd.append('mensaje_comprador', mensajeComprador);
      if (tipoEntrega === 'programada') {
        fd.append('fecha_hora_programada', fechaProgramada);
      }

      const { resp, json, text } = await fetchJsonRobusto(`${BASE}/api/pedidos/registrar`, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });

      if (resp.status === 401) {
        notify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${BASE}/`; }, 1200);
        return;
      }

      if (resp.status === 409 && json?.redirect) {
        notify('warning', 'Residencia requerida', json.mensaje || 'Debes completar tu residencia.');
        setTimeout(() => { window.location.href = json.redirect; }, 1200);
        return;
      }

      if (!json) {
        err('REGISTRAR PEDIDO no devolvió JSON:', (text || '').slice(0, 400));
        notify('error', 'Error', 'La respuesta del servidor no fue válida.');
        return;
      }

      if (!resp.ok || !json.ok) {
        const apiError = String(json?.error || '').trim();
        const apiMsg = json?.mensaje || json?.error || 'No se pudo registrar la solicitud.';

        if (apiError === 'VENDEDOR_NO_DISPONIBLE') {
          notify('info', 'Vendedor no disponible', apiMsg);
          return;
        }

        if (apiError === 'PRODUCTO_PROPIO') {
          notify('warning', 'Acción no permitida', apiMsg);
          return;
        }

        if (
          apiError === 'PUBLICACION_FUERA_DE_CONJUNTO' ||
          apiError === 'PRODUCTO_NO_APROBADO' ||
          apiError === 'PUBLICACION_NO_VIGENTE' ||
          apiError === 'VENDEDOR_NO_HABILITADO'
        ) {
          notify('warning', 'Publicación no disponible', apiMsg);
          return;
        }

        notify('error', 'Error', apiMsg);
        return;
      }

      notify('success', 'Solicitud registrada', json.mensaje || 'Tu solicitud fue enviada correctamente.');

      const form = document.getElementById('mp_form_solicitud_pedido');
      try { form?.reset(); } catch (_) {}

      const modalSolicitudEl = document.getElementById('mp_modal_solicitud');
      if (modalSolicitudEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const modalSolicitud = window.bootstrap.Modal.getOrCreateInstance(modalSolicitudEl, {
          backdrop: 'static',
          keyboard: false
        });
        modalSolicitud.hide();
      }

      recalcularTotalSolicitud();
      actualizarVisibilidadEntregaProgramada();

    } catch (e) {
      err('EXCEPTION registrar pedido', e);
      notify('error', 'Error inesperado', 'Ocurrió un problema al registrar la solicitud.');
    } finally {
      if (btnSubmit) btnSubmit.disabled = false;
    }
  }

  function bindSolicitudModalEvents() {
    const cantidadEl = document.getElementById('mp_sp_cantidad');
    const tipoEntregaEl = document.getElementById('mp_sp_tipo_entrega');
    const formSolicitud = document.getElementById('mp_form_solicitud_pedido');
    const btnPedirDetalle = document.getElementById('btnPedirAhoraDetalle');

    if (cantidadEl && !cantidadEl.dataset.boundSolicitud) {
      cantidadEl.dataset.boundSolicitud = '1';
      cantidadEl.addEventListener('input', recalcularTotalSolicitud);
      cantidadEl.addEventListener('change', recalcularTotalSolicitud);
    }

    if (tipoEntregaEl && !tipoEntregaEl.dataset.boundSolicitud) {
      tipoEntregaEl.dataset.boundSolicitud = '1';
      tipoEntregaEl.addEventListener('change', actualizarVisibilidadEntregaProgramada);
    }

    if (btnPedirDetalle && !btnPedirDetalle.dataset.boundSolicitud) {
      btnPedirDetalle.dataset.boundSolicitud = '1';
      btnPedirDetalle.addEventListener('click', () => {
        if (!window.EV_MP_DETALLE_ACTUAL) {
          notify('warning', 'Detalle no disponible', 'Primero abre una publicación válida.');
          return;
        }

        abrirModalSolicitudDesdeProducto(window.EV_MP_DETALLE_ACTUAL);
      });
    }

    if (formSolicitud && !formSolicitud.dataset.boundSolicitud) {
      formSolicitud.dataset.boundSolicitud = '1';
      formSolicitud.addEventListener('submit', async (e) => {
        e.preventDefault();
        await enviarSolicitudPedido();
      });
    }
  }

  function cardHtml(p) {
    const id     = p.__id;
    const titulo = escapeHtml(p.__titulo || '');
    const desc   = escapeHtml(p.__descripcion || '');
    const precio = formatPrecio(p.__precio || 0);
    const imgUrl = buildImgUrl(p.__imagen_portada);

    const esPotenciado = Number(p.__es_potenciado || 0) === 1;
    const vendedorDisponible = Number(p.__vendedor_disponible || 0) === 1;

    let badgesHtml = '';
    if (esPotenciado) {
      badgesHtml += `<span class="ev-mp-badge ev-mp-badge-potenciado">Recomendado</span>`;
    }

    const estadoClass = vendedorDisponible
      ? 'ev-mp-card-top-status ev-mp-card-top-status-on'
      : 'ev-mp-card-top-status ev-mp-card-top-status-off';

    const estadoLabel = vendedorDisponible ? 'Disponible' : 'No disponible';

    return `
      <div class="ev-mp-card" data-id="${escapeHtml(String(id))}">
        <div class="${estadoClass}" title="${estadoLabel}" aria-label="${estadoLabel}">
          <span class="ev-mp-card-top-status-text">${estadoLabel}</span>
        </div>

        <div class="ev-mp-card-media">
          <img src="${imgUrl}" alt="${titulo}">
          <div class="ev-mp-card-badges">${badgesHtml}</div>
        </div>

        <div class="ev-mp-card-body">
          <h5 class="ev-mp-card-title">${titulo}</h5>
          <p class="ev-mp-card-price">${precio}</p>
          <p style="font-size:13px;color:var(--ev-texto-suave);margin-bottom:6px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
            ${desc}
          </p>

          <div class="ev-mp-card-actions">
            <button type="button" class="btn btn-outline-success ev-mp-btn-detalle">Ver detalle</button>
            <button type="button" class="btn btn-success ev-mp-btn-pedir">Pedir ahora</button>
          </div>
        </div>
      </div>
    `;
  }

  function bindCardActions(container) {
    if (!container) return;

    Array.from(container.querySelectorAll('.ev-mp-card')).forEach((card) => {
      const id = card.getAttribute('data-id');
      if (!id) return;

      const btnDetalle = card.querySelector('.ev-mp-btn-detalle');
      const btnPedir = card.querySelector('.ev-mp-btn-pedir');

      if (btnDetalle && !btnDetalle.dataset.boundDetalle) {
        btnDetalle.dataset.boundDetalle = '1';
        btnDetalle.addEventListener('click', () => abrirModalDetalle(id));
      }

      if (btnPedir && !btnPedir.dataset.boundPedir) {
        btnPedir.dataset.boundPedir = '1';
        btnPedir.addEventListener('click', async () => {
          const detalle = await obtenerDetalleProducto(id);
          if (!detalle) return;

          const { producto, imagenes } = detalle;

          window.EV_MP_DETALLE_ACTUAL = {
            ...producto,
            imagenes: Array.isArray(imagenes) ? imagenes : [],
            vendedor_disponible: Number(producto?.vendedor_disponible ?? producto?.disponibilidad_pedidos_vendedor ?? 0) === 1 ? 1 : 0
          };

          abrirModalSolicitudDesdeProducto(window.EV_MP_DETALLE_ACTUAL);
        });
      }
    });
  }

  async function cargarTiposYDetectar() {
    try {
      const url = `${BASE}/tipos`;
      const { resp, json, text } = await fetchJsonRobusto(url, {
        method:'GET',
        headers:{'Accept':'application/json'},
        credentials:'same-origin'
      });

      if (!resp.ok || !json) {
        warn('No se pudo cargar /tipos:', (text || '').slice(0, 200));
        return;
      }

      const tipos = getArrayFromPayload(json);
      const mapByName = new Map();

      tipos.forEach(t => {
        const id = Number(t.codigo_tipo || 0) || 0;
        const name = (t.nombre || '').toString();
        mapByName.set(normalizar(name), id);
      });

      tipoIdProducto = mapByName.get('producto') || mapByName.get('productos') || 0;
      tipoIdServicio = mapByName.get('servicio') || mapByName.get('servicios') || 0;

      if (!tipoIdProducto) {
        for (const [k, v] of mapByName.entries()) {
          if (k.includes('product')) { tipoIdProducto = v; break; }
        }
      }
      if (!tipoIdServicio) {
        for (const [k, v] of mapByName.entries()) {
          if (k.includes('servic')) { tipoIdServicio = v; break; }
        }
      }

      log('Tipos detectados:', { tipoIdProducto, tipoIdServicio });

      if (tipoIdProducto) {
        await cargarCategoriasProductos(tipoIdProducto);
      } else {
        if (refs.selectCategoriaProductos) {
          refs.selectCategoriaProductos.innerHTML = `<option value="0">Todas las categorías</option>`;
        }
      }
    } catch (e) {
      warn('Error cargando tipos/categorías:', e);
    }
  }

  async function cargarCategoriasProductos(tipoId) {
    if (!refs.selectCategoriaProductos) return;

    try {
      const url = `${BASE}/tipos/${encodeURIComponent(tipoId)}/categoria_grupo`;
      const { resp, json, text } = await fetchJsonRobusto(url, {
        method:'GET',
        headers:{'Accept':'application/json'},
        credentials:'same-origin'
      });

      if (!resp.ok || !json) {
        warn('No se pudo cargar categorias:', (text || '').slice(0, 200));
        refs.selectCategoriaProductos.innerHTML = `<option value="0">Todas las categorías</option>`;
        return;
      }

      const rows = getArrayFromPayload(json);

      const opt0 = `<option value="0">Todas las categorías</option>`;
      const options = rows.map(r => {
        const id = Number(r.codigo_categoria || 0) || 0;
        const grupo = (r.grupo || '').toString().trim();
        const cat = (r.categoria || '').toString().trim();
        const label = (grupo ? `${grupo} — ${cat}` : cat);
        return `<option value="${id}">${escapeHtml(label)}</option>`;
      }).join('');

      refs.selectCategoriaProductos.innerHTML = opt0 + options;

    } catch (e) {
      warn('Error cargando categorias productos:', e);
      refs.selectCategoriaProductos.innerHTML = `<option value="0">Todas las categorías</option>`;
    }
  }

  function aplicarFiltros(listaBase) {
    let lista = Array.isArray(listaBase) ? [...listaBase] : [];

    if (scope === 'productos') {
      if (tipoIdProducto) lista = lista.filter(p => Number(p.__codigo_tipo || 0) === tipoIdProducto);
      else lista = lista.filter(p => normalizar(p.__tipo_nombre || p.__tipo_slug || '').includes('producto'));
    } else if (scope === 'servicios') {
      if (tipoIdServicio) lista = lista.filter(p => Number(p.__codigo_tipo || 0) === tipoIdServicio);
      else lista = lista.filter(p => normalizar(p.__tipo_nombre || p.__tipo_slug || '').includes('servicio'));
    }

    if (textoBusqueda.trim() !== '') {
      const needle = normalizar(textoBusqueda);
      lista = lista.filter((p) => {
        const hay = normalizar((p.__titulo || '') + ' ' + (p.__descripcion || ''));
        return hay.includes(needle);
      });
    }

    if ((scope === 'todos' || scope === 'productos') && Number(categoriaProductoId || 0) > 0) {
      lista = lista.filter((p) => {
        const isProducto = tipoIdProducto
          ? Number(p.__codigo_tipo || 0) === tipoIdProducto
          : normalizar(p.__tipo_nombre || p.__tipo_slug || '').includes('producto');

        if (!isProducto) return true;

        return Number(p.__codigo_categoria || 0) === Number(categoriaProductoId);
      });
    }

    lista.sort((a, b) => {
      const precioA  = Number(a.__precio || 0);
      const precioB  = Number(b.__precio || 0);
      const recA     = Number(a.__orden_reciente || a.__id || 0);
      const recB     = Number(b.__orden_reciente || b.__id || 0);

      switch (criterioOrden) {
        case 'precio_menor': return precioA - precioB;
        case 'precio_mayor': return precioB - precioA;
        case 'recientes':
        default:             return recB - recA;
      }
    });

    return lista;
  }

  function splitServiciosProductos(lista) {
    const servicios = [];
    const productos = [];

    lista.forEach(p => {
      const t = Number(p.__codigo_tipo || 0);
      const tn = normalizar(p.__tipo_nombre || p.__tipo_slug || '');

      const esProd = tipoIdProducto ? (t === tipoIdProducto) : tn.includes('producto');
      const esServ = tipoIdServicio ? (t === tipoIdServicio) : tn.includes('servicio');

      if (esServ) servicios.push(p);
      else if (esProd) productos.push(p);
      else productos.push(p);
    });

    return { servicios, productos };
  }

  function pintarSecciones(listaFiltrada) {
    if (!refs.gridServicios || !refs.gridProductos) return;

    const { servicios, productos } = splitServiciosProductos(listaFiltrada);

    if (refs.countServicios) refs.countServicios.textContent = String(servicios.length);
    if (refs.countProductos) refs.countProductos.textContent = String(productos.length);

    refs.gridServicios.innerHTML = (scope === 'todos' || scope === 'servicios')
      ? servicios.map(cardHtml).join('')
      : '';

    refs.gridProductos.innerHTML = (scope === 'todos' || scope === 'productos')
      ? productos.map(cardHtml).join('')
      : '';

    bindCardActions(refs.gridServicios);
    bindCardActions(refs.gridProductos);

    const total = (scope === 'servicios') ? servicios.length
               : (scope === 'productos') ? productos.length
               : (servicios.length + productos.length);

    setResumen(`Mostrando ${total} resultado${total === 1 ? '' : 's'} en ${CONDO_NOMBRE_RESUMEN}`);
  }

  function aplicarYRedibujar() {
    applyScopeVisibility();

    const lista = aplicarFiltros(publicaciones);
    const total = Array.isArray(lista) ? lista.length : 0;

    if (!total) {
      showEmpty('No encontramos publicaciones con los filtros actuales.');
      setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
      if (refs.countServicios) refs.countServicios.textContent = '0';
      if (refs.countProductos) refs.countProductos.textContent = '0';
      return;
    }

    hideEmpty();
    pintarSecciones(lista);
  }

  async function cargarPublicaciones(opciones = {}) {
    const esSilent = opciones.silent === true;

    if (!refs.gridAllWrapper) return;

    if (!esSilent) {
      hideEmpty();
      setResumen('Cargando publicaciones…');
    }

    const url = `${BASE}/api/producto/marketplace`;

    try {
      const { resp, text, json } = await fetchJsonRobusto(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
      });

      if (resp.status === 401) {
        notify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${BASE}/`; }, 1200);
        return;
      }

      if (resp.status === 409) {
        const msg = (json && (json.mensaje || json.error)) ? (json.mensaje || json.error) : 'No tienes residencia activa.';
        notify('warning', 'Residencia requerida', msg);

        const redir = (json && json.redirect) ? json.redirect : `${BASE}/mi-perfil`;
        setTimeout(() => { window.location.href = redir; }, 1200);
        return;
      }

      if (!json) {
        err('API marketplace no devolvió JSON:', (text || '').slice(0, 400));
        if (!esSilent) {
          publicaciones = [];
          showEmpty('No se pudo cargar el Marketplace (respuesta no válida).');
          setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        }
        return;
      }

      if (!resp.ok || !json.ok) {
        const msg = json.mensaje || json.error || 'No se pudo cargar el Marketplace.';
        if (!esSilent) {
          publicaciones = [];
          showEmpty(msg);
          setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        }
        return;
      }

      const rawList = normalizarListaDesdeAPI(json);
      publicaciones = Array.isArray(rawList) ? rawList.map(normalizarItem) : [];

      if (!publicaciones.length) {
        if (!esSilent) {
          showEmpty(`No hay publicaciones publicadas en ${CONDO_NOMBRE_RESUMEN} todavía.`);
          setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        } else {
          aplicarYRedibujar();
        }
        return;
      }

      aplicarYRedibujar();

    } catch (e) {
      err('EXCEPTION cargarPublicaciones', e);
      if (!esSilent) {
        publicaciones = [];
        showEmpty('Ocurrió un problema al cargar el Marketplace.');
        setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
      }
    }
  }

  async function refrescarDisponibilidadMarketplace() {
    if (pollingEnCurso) return;
    pollingEnCurso = true;

    try {
      await cargarPublicaciones({ silent: true });
    } catch (e) {
      warn('No se pudo refrescar disponibilidad en segundo plano:', e);
    } finally {
      pollingEnCurso = false;
    }
  }

  function iniciarPollingDisponibilidad() {
    detenerPollingDisponibilidad();
    pollingTimer = window.setInterval(() => {
      refrescarDisponibilidadMarketplace();
    }, POLLING_MS);
  }

  function detenerPollingDisponibilidad() {
    if (pollingTimer) {
      window.clearInterval(pollingTimer);
      pollingTimer = null;
    }
  }

  function bindEvents() {
    if (refs.searchInput && !refs.searchInput.dataset.boundMarketplace) {
      refs.searchInput.dataset.boundMarketplace = '1';
      refs.searchInput.addEventListener('input', () => {
        textoBusqueda = refs.searchInput.value || '';
        aplicarYRedibujar();
      });
    }

    if (refs.selectOrdenar && !refs.selectOrdenar.dataset.boundMarketplace) {
      refs.selectOrdenar.dataset.boundMarketplace = '1';
      criterioOrden = refs.selectOrdenar.value || 'recientes';
      refs.selectOrdenar.addEventListener('change', () => {
        criterioOrden = refs.selectOrdenar.value || 'recientes';
        aplicarYRedibujar();
      });
    }

    refs.scopeButtons.forEach(btn => {
      if (btn.dataset.boundMarketplace === '1') return;
      btn.dataset.boundMarketplace = '1';

      btn.addEventListener('click', () => {
        refs.scopeButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        scope = btn.dataset.scope || 'todos';

        if ((scope === 'productos' || scope === 'todos') && tipoIdProducto && refs.selectCategoriaProductos) {
          const opts = refs.selectCategoriaProductos.querySelectorAll('option');
          if (!opts || opts.length <= 1) cargarCategoriasProductos(tipoIdProducto);
        }

        aplicarYRedibujar();
      });
    });

    if (refs.selectCategoriaProductos && !refs.selectCategoriaProductos.dataset.boundMarketplace) {
      refs.selectCategoriaProductos.dataset.boundMarketplace = '1';
      refs.selectCategoriaProductos.addEventListener('change', () => {
        categoriaProductoId = Number(refs.selectCategoriaProductos.value || 0) || 0;
        aplicarYRedibujar();
      });
    }

    if (!document.body.dataset.boundMarketplaceVisibility) {
      document.body.dataset.boundMarketplaceVisibility = '1';

      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          detenerPollingDisponibilidad();
        } else {
          refrescarDisponibilidadMarketplace();
          iniciarPollingDisponibilidad();
        }
      });
    }
  }

  function initStaticModals() {
    const modalDetalleEl = document.getElementById('mp_modal_detalle');
    const modalSolicitudEl = document.getElementById('mp_modal_solicitud');

    if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
      if (modalDetalleEl) {
        window.bootstrap.Modal.getOrCreateInstance(modalDetalleEl, {
          backdrop: 'static',
          keyboard: false
        });
      }

      if (modalSolicitudEl) {
        window.bootstrap.Modal.getOrCreateInstance(modalSolicitudEl, {
          backdrop: 'static',
          keyboard: false
        });
      }
    }
  }

  async function initMarketplace() {
    if (!capturarRefs()) return;

    if (marketplaceInicializado) {
      iniciarPollingDisponibilidad();
      return;
    }

    marketplaceInicializado = true;

    ensureGridCSS();
    bindEvents();
    bindSolicitudModalEvents();
    initStaticModals();
    await cargarTiposYDetectar();
    await cargarPublicaciones();
    iniciarPollingDisponibilidad();
  }

  document.addEventListener('DOMContentLoaded', initMarketplace);

  const observer = new MutationObserver(() => {
    const gridWrapper = document.getElementById('mp_grid_publicaciones');
    if (gridWrapper && gridWrapper !== refs.gridAllWrapper) {
      refs.gridAllWrapper = gridWrapper;
      initMarketplace();
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });

  window.EVMarketplace = {
    init: initMarketplace,
    refreshDisponibilidad: refrescarDisponibilidadMarketplace
  };

  log('Cargado. BASE:', BASE || '(vacío)', '| Condominio:', CONDO_NOMBRE_RESUMEN);
})();