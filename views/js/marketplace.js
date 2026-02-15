/* views/js/marketplace.js
   Marketplace EV — 2 secciones: Servicios + Productos
   - Scope: todos/servicios/productos
   - Categoría productos: desde /tipos/{id}/categoria_grupo
   - Mantiene modal detalle y robustez de URLs

   ✅ FIX 2026:
   - Cards pequeñas (grid real) aunque solo haya 1 publicación
   - Inyecta CSS defensivo (sin tocar tu CSS global)
   - Combo Categoría carga (fetch con credentials + normalización de payload)
   - Al elegir Productos, se oculta Servicios (y viceversa)
*/
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').toString().replace(/\/+$/, '');
  const LOG_PREFIX = '[MARKETPLACE]';

  const CONDO_NOMBRE_RESUMEN = (typeof window !== 'undefined' && window.EV_CONDOMINIO_NOMBRE)
    ? window.EV_CONDOMINIO_NOMBRE
    : 'tu condominio';

  // Refs
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

    // wrappers (para ocultar secciones completas si existen)
    wrapServicios: null,
    wrapProductos: null,
    wrapCategoriaProductos: null
  };

  // Data
  let publicaciones = []; // normalizadas
  let textoBusqueda = '';
  let criterioOrden = 'recientes';
  let scope = 'todos'; // todos|servicios|productos
  let categoriaProductoId = 0;

  // Tipos IDs reales
  let tipoIdProducto = 0;
  let tipoIdServicio = 0;

  // Logs
  function log()  { if (console && console.log)  console.log(LOG_PREFIX, ...arguments); }
  function warn() { if (console && console.warn) console.warn(LOG_PREFIX, ...arguments); }
  function err()  { if (console && console.error) console.error(LOG_PREFIX, ...arguments); }

  // Notificaciones
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

  // Helpers
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

  // ✅ soporta payloads: array directo, {data:[...]}, {ok:true,data:[...]}, {items:[...]}
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
      __orden_reciente: orden_reciente
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

  // ✅ Inyecta CSS para que SIEMPRE sea grid y cards pequeñas (aunque haya 1)
  function ensureGridCSS() {
    const ID = 'ev-mp-grid-fix';
    if (document.getElementById(ID)) return;

    const css = `
/* ===== EV Marketplace Grid Fix (injected) ===== */
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
  max-width:340px !important;      /* ✅ evita card gigante */
  justify-self:start !important;   /* ✅ no centrado full */
}
#mp_grid_servicios .ev-mp-card-media img,
#mp_grid_productos .ev-mp-card-media img{
  width:100% !important;
  height:170px !important;         /* ✅ mantiene proporción visual tipo tarjeta */
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
/* ===== End Fix ===== */
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

    // wrappers: intenta encontrar contenedores de sección
    refs.wrapServicios = refs.gridServicios ? (refs.gridServicios.closest('.ev-mp-section') || refs.gridServicios.parentElement) : null;
    refs.wrapProductos = refs.gridProductos ? (refs.gridProductos.closest('.ev-mp-section') || refs.gridProductos.parentElement) : null;

    // wrapper del combo categoría (si existe un contenedor cercano)
    if (refs.selectCategoriaProductos) {
      refs.wrapCategoriaProductos =
        refs.selectCategoriaProductos.closest('.ev-mp-cat-wrap') ||
        refs.selectCategoriaProductos.closest('.col') ||
        refs.selectCategoriaProductos.parentElement;
    }

    return !!refs.gridAllWrapper;
  }

  function applyScopeVisibility() {
    // Todos => ambos visibles
    const showServ = (scope === 'todos' || scope === 'servicios');
    const showProd = (scope === 'todos' || scope === 'productos');

    if (refs.wrapServicios) refs.wrapServicios.style.display = showServ ? '' : 'none';
    if (refs.wrapProductos) refs.wrapProductos.style.display = showProd ? '' : 'none';

    // Combo categoría solo cuando aplica productos/todos
    if (refs.wrapCategoriaProductos) {
      refs.wrapCategoriaProductos.style.display = (scope === 'todos' || scope === 'productos') ? '' : 'none';
    }

    // Si estoy en Servicios, el filtro de categoría no debe afectar
    if (scope === 'servicios') {
      categoriaProductoId = 0;
      if (refs.selectCategoriaProductos) refs.selectCategoriaProductos.value = '0';
    }
  }

  // Detalle modal
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
      notify('error', 'Error UI', 'No se encontró el modal de detalle (mp_modal_*).');
      return;
    }

    try {
      const url = `${BASE}/api/producto/${encodeURIComponent(idProducto)}`;

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

      if (!json) {
        err('DETALLE no devolvió JSON:', (text || '').slice(0, 400));
        notify('error', 'Error', 'La API devolvió una respuesta no válida.');
        return;
      }

      if (!resp.ok || !json.ok) {
        const msg = json.mensaje || json.error || 'No se pudo obtener el detalle.';
        notify('error', 'Error', msg);
        return;
      }

      const { producto, imagenes } = extraerDetalleDesdeRespuesta(json);
      const pub = normalizarItem(producto);

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
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
      } else if (window.$ && typeof window.$(modalEl).modal === 'function') {
        window.$(modalEl).modal('show');
      }

    } catch (e) {
      err('EXCEPTION DETALLE', e);
      notify('error', 'Error inesperado', 'Ocurrió un problema al cargar el detalle.');
    }
  }

  // Render cards
  function cardHtml(p) {
    const id     = p.__id;
    const titulo = escapeHtml(p.__titulo || '');
    const desc   = escapeHtml(p.__descripcion || '');
    const precio = formatPrecio(p.__precio || 0);
    const imgUrl = buildImgUrl(p.__imagen_portada);

    const esPotenciado = Number(p.__es_potenciado || 0) === 1;

    let badgesHtml = '';
    if (esPotenciado) badgesHtml += `<span class="ev-mp-badge ev-mp-badge-potenciado">Recomendado</span>`;

    return `
      <div class="ev-mp-card" data-id="${escapeHtml(String(id))}">
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

          <div class="ev-mp-card-meta">
            <div class="ev-mp-card-vecino">
              <div class="ev-mp-avatar">${(titulo || '?').charAt(0).toUpperCase()}</div>
              <div>
                <div class="ev-mp-vecino-nombre">Vecino</div>
                <div class="ev-mp-vecino-condominio">Tu condominio</div>
              </div>
            </div>
          </div>

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
      const btnDetalle = card.querySelector('.ev-mp-btn-detalle');
      if (btnDetalle && id) btnDetalle.addEventListener('click', () => abrirModalDetalle(id));
    });
  }

  // Tipos + Categorías (Productos)
  async function cargarTiposYDetectar() {
    try {
      const url = `${BASE}/tipos`;
      const { resp, json, text } = await fetchJsonRobusto(url, {
        method:'GET',
        headers:{'Accept':'application/json'},
        credentials:'same-origin' // ✅ IMPORTANTE
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
        // si no detecta, deja combo en modo vacío
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
        credentials:'same-origin' // ✅ IMPORTANTE
      });

      if (!resp.ok || !json) {
        warn('No se pudo cargar categorias:', (text || '').slice(0, 200));
        refs.selectCategoriaProductos.innerHTML = `<option value="0">Todas las categorías</option>`;
        return;
      }

      const rows = getArrayFromPayload(json); // ✅ por si mañana lo envuelves en {ok,data}

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

  // Filtros
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

    // ✅ categoría SOLO aplica si scope permite productos (todos o productos)
    if ((scope === 'todos' || scope === 'productos') && Number(categoriaProductoId || 0) > 0) {
      lista = lista.filter((p) => {
        const isProducto = tipoIdProducto
          ? Number(p.__codigo_tipo || 0) === tipoIdProducto
          : normalizar(p.__tipo_nombre || p.__tipo_slug || '').includes('producto');

        // si no es producto, en scope "todos" lo dejamos pasar
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

    // ✅ pintar solo lo que corresponde al scope (pero sin romper "todos")
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

  // Carga API
  async function cargarPublicaciones() {
    if (!refs.gridAllWrapper) return;

    hideEmpty();
    setResumen('Cargando publicaciones…');

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

      if (!json) {
        err('API marketplace no devolvió JSON:', (text || '').slice(0, 400));
        publicaciones = [];
        showEmpty('No se pudo cargar el Marketplace (respuesta no válida).');
        setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        return;
      }

      if (!resp.ok || !json.ok) {
        const msg = json.mensaje || json.error || 'No se pudo cargar el Marketplace.';
        publicaciones = [];
        showEmpty(msg);
        setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        return;
      }

      const rawList = normalizarListaDesdeAPI(json);
      publicaciones = Array.isArray(rawList) ? rawList.map(normalizarItem) : [];

      if (!publicaciones.length) {
        showEmpty(`No hay publicaciones publicadas en ${CONDO_NOMBRE_RESUMEN} todavía.`);
        setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        return;
      }

      aplicarYRedibujar();

    } catch (e) {
      err('EXCEPTION cargarPublicaciones', e);
      publicaciones = [];
      showEmpty('Ocurrió un problema al cargar el Marketplace.');
      setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
    }
  }

  // Events
  function bindEvents() {
    if (refs.searchInput) {
      refs.searchInput.addEventListener('input', () => {
        textoBusqueda = refs.searchInput.value || '';
        aplicarYRedibujar();
      });
    }

    if (refs.selectOrdenar) {
      criterioOrden = refs.selectOrdenar.value || 'recientes';
      refs.selectOrdenar.addEventListener('change', () => {
        criterioOrden = refs.selectOrdenar.value || 'recientes';
        aplicarYRedibujar();
      });
    }

    refs.scopeButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        refs.scopeButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        scope = btn.dataset.scope || 'todos';

        // ✅ si entro a productos y el tipo ya está detectado pero categorías no cargaron aún
        if ((scope === 'productos' || scope === 'todos') && tipoIdProducto && refs.selectCategoriaProductos) {
          // si solo existe la opción 0, reintenta cargar
          const opts = refs.selectCategoriaProductos.querySelectorAll('option');
          if (!opts || opts.length <= 1) cargarCategoriasProductos(tipoIdProducto);
        }

        aplicarYRedibujar();
      });
    });

    if (refs.selectCategoriaProductos) {
      refs.selectCategoriaProductos.addEventListener('change', () => {
        categoriaProductoId = Number(refs.selectCategoriaProductos.value || 0) || 0;
        aplicarYRedibujar();
      });
    }
  }

  async function initMarketplace() {
    if (!capturarRefs()) return;

    ensureGridCSS();        // ✅ fuerza cards pequeñas
    bindEvents();
    await cargarTiposYDetectar();
    await cargarPublicaciones();
  }

  document.addEventListener('DOMContentLoaded', initMarketplace);

  const observer = new MutationObserver(() => {
    const gridWrapper = document.getElementById('mp_grid_publicaciones');
    if (gridWrapper && gridWrapper !== refs.gridAllWrapper) {
      initMarketplace();
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });

  window.EVMarketplace = { init: initMarketplace };

  log('Cargado. BASE:', BASE || '(vacío)', '| Condominio:', CONDO_NOMBRE_RESUMEN);
})();
