/* marketplace.js — Marketplace Entre Vecinos (EV)
   ✅ Fix integral:
   - /api/producto/marketplace y /api/producto/{id}
   - Soporta shape real del detalle: {data:{producto:{...}, imagenes:[...]}}
   - Evita duplicar BASE_URL en imágenes (/entrevecinos/entrevecinos)
   - Modal detalle: título / precio / descripción con fallbacks
*/

(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').toString().replace(/\/+$/, ''); // "/entrevecinos"
  const LOG_PREFIX = '[MARKETPLACE]';

  const CONDO_NOMBRE_RESUMEN = (typeof window !== 'undefined' && window.EV_CONDOMINIO_NOMBRE)
    ? window.EV_CONDOMINIO_NOMBRE
    : 'tu condominio';

  let refs = {
    grid: null,
    resumenResultados: null,
    searchInput: null,
    emptyState: null,
    selectOrdenar: null,
    chips: []
  };

  let publicaciones = [];
  let filtroCategoria = 'todos';
  let textoBusqueda   = '';
  let criterioOrden   = 'recientes';

  // -------------------------
  // Logs
  // -------------------------
  function log()  { if (console && console.log)  console.log(LOG_PREFIX, ...arguments); }
  function warn() { if (console && console.warn) console.warn(LOG_PREFIX, ...arguments); }
  function err()  { if (console && console.error) console.error(LOG_PREFIX, ...arguments); }

  // -------------------------
  // Notificaciones
  // -------------------------
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

  // -------------------------
  // Helpers
  // -------------------------
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
    const basePath = getBasePath(); // "/entrevecinos"
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

  function normalizarListaDesdeAPI(payload) {
    if (!payload || typeof payload !== 'object') return [];

    const d = payload.data;
    if (Array.isArray(d)) return d;

    if (Array.isArray(payload.productos)) return payload.productos;
    if (Array.isArray(payload.publicaciones)) return payload.publicaciones;
    if (Array.isArray(payload.items)) return payload.items;

    if (d && Array.isArray(d.items)) return d.items;
    if (d && Array.isArray(d.productos)) return d.productos;
    if (d && Array.isArray(d.publicaciones)) return d.publicaciones;

    return [];
  }

  function normalizarItem(raw) {
    const o = raw && typeof raw === 'object' ? raw : {};

    const id =
      o.codigo_producto ??
      o.codigo_publicacion ??
      o.id_producto ??
      o.id_publicacion ??
      o.id ??
      '';

    const titulo =
      o.titulo ??
      o.nombre ??
      o.nombre_producto ??
      o.titulo_producto ??
      '';

    const descripcion =
      o.descripcion ??
      o.descripcion_producto ??
      o.detalle ??
      '';

    const precio =
      o.precio ??
      o.precio_producto ??
      o.monto ??
      0;

    const categoria_nombre =
      o.categoria_nombre ??
      o.nombre_categoria ??
      o.categoria ??
      '';

    const categoria_slug =
      o.categoria_slug ??
      o.slug_categoria ??
      '';

    const tipo_nombre =
      o.tipo_nombre ??
      o.nombre_tipo ??
      o.tipo ??
      'Producto';

    const tipo_slug =
      o.tipo_slug ??
      o.slug_tipo ??
      '';

    const es_potenciado =
      o.es_potenciado ??
      o.potenciado ??
      o.destacado ??
      0;

    const imagen_portada =
      o.imagen_portada ??
      o.ruta_portada ??
      o.foto_portada ??
      o.imagen ??
      o.ruta ??
      '';

    const orden_reciente =
      o.orden_reciente ??
      o.created_at ??
      o.fecha_publicacion ??
      id ??
      0;

    return {
      ...o,
      __id: id,
      __titulo: titulo,
      __descripcion: descripcion,
      __precio: precio,
      __categoria_nombre: categoria_nombre,
      __categoria_slug: categoria_slug,
      __tipo_nombre: tipo_nombre,
      __tipo_slug: tipo_slug,
      __es_potenciado: es_potenciado,
      __imagen_portada: imagen_portada,
      __orden_reciente: orden_reciente
    };
  }

  function setResumen(txt) {
    if (refs.resumenResultados) refs.resumenResultados.textContent = txt;
  }

  function showEmpty(msg) {
    if (refs.grid) refs.grid.innerHTML = '';
    if (refs.emptyState) {
      refs.emptyState.style.display = '';
      refs.emptyState.textContent = msg || 'No encontramos publicaciones con los filtros actuales.';
    }
  }

  function hideEmpty() {
    if (refs.emptyState) refs.emptyState.style.display = 'none';
  }

  function capturarRefs() {
    refs.grid              = document.getElementById('mp_grid_publicaciones');
    refs.resumenResultados = document.getElementById('mp_resumen_resultados');
    refs.searchInput       = document.getElementById('mp_busqueda');
    refs.emptyState        = document.getElementById('mp_empty_state');
    refs.selectOrdenar     = document.getElementById('mp_orden');
    refs.chips             = Array.from(document.querySelectorAll('.ev-mp-chip'));
    return !!refs.grid;
  }

  // -------------------------
  // ✅ DETALLE (FIX shape real)
  // -------------------------
  function extraerDetalleDesdeRespuesta(json) {
    // Soporta:
    // 1) {ok:true, data:{producto:{...}, imagenes:[...]}}
    // 2) {ok:true, data:{...producto plano...}}
    // 3) {ok:true, producto:{...}, imagenes:[...]}
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

      // ✅ Aquí está el fix
      const { producto, imagenes } = extraerDetalleDesdeRespuesta(json);
      const pub = normalizarItem(producto);

      const titulo = pub.__titulo || 'Producto';
      const precio = pub.__precio || 0;
      const desc   = pub.__descripcion || '—';

      tituloTxtEl.textContent = titulo;
      precioEl.textContent    = formatPrecio(precio);
      descEl.textContent      = desc;

      // Si no tienes categoría/tipo aún, mantenemos placeholders estables
      catEl.textContent  = pub.__categoria_nombre || '—';
      tipoEl.textContent = pub.__tipo_nombre || 'Producto';

      // Normalizar imágenes
      const imgs = (Array.isArray(imagenes) ? imagenes : []).map((x) => {
        if (!x) return null;
        if (typeof x === 'string') return { url: x };
        return { url: x.url || x.ruta || x.path || x.imagen || x.src || x.nombre || '' };
      }).filter(Boolean);

      // Portada: si el producto no trae imagen_portada, usamos primera de imagenes
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

  // -------------------------
  // Filtros + pintado
  // -------------------------
  function aplicarFiltrosYRedibujar() {
    if (!refs.grid) return;

    let lista = Array.isArray(publicaciones) ? [...publicaciones] : [];

    if (filtroCategoria && filtroCategoria !== 'todos') {
      if (filtroCategoria === 'recomendados') {
        lista = lista.filter((p) => Number(p.__es_potenciado || 0) === 1);
      } else if (filtroCategoria === 'productos') {
        lista = lista.filter((p) => normalizar(p.__tipo_nombre || p.__tipo_slug || '').includes('producto'));
      } else if (filtroCategoria === 'servicios') {
        lista = lista.filter((p) => normalizar(p.__tipo_nombre || p.__tipo_slug || '').includes('servicio'));
      }
    }

    if (textoBusqueda.trim() !== '') {
      const needle = normalizar(textoBusqueda);
      lista = lista.filter((p) => {
        const hay = normalizar((p.__titulo || '') + ' ' + (p.__descripcion || ''));
        return hay.includes(needle);
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

    pintarGrid(lista);
  }

  function pintarGrid(lista) {
    if (!refs.grid) return;

    if (!Array.isArray(lista) || lista.length === 0) {
      showEmpty('No encontramos publicaciones con los filtros actuales.');
      setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
      return;
    }

    hideEmpty();

    const cardsHtml = lista.map((p, idx) => {
      const id     = p.__id;
      const titulo = escapeHtml(p.__titulo || '');
      const desc   = escapeHtml(p.__descripcion || '');
      const precio = formatPrecio(p.__precio || 0);
      const imgUrl = buildImgUrl(p.__imagen_portada);

      const catSlug = String(
        p.__categoria_slug ||
        p.__tipo_slug ||
        p.__categoria_nombre ||
        p.__tipo_nombre ||
        'todos'
      ).toLowerCase();

      const precioNum     = Number(p.__precio || 0) || 0;
      const ordenReciente = Number(p.__orden_reciente || p.__id || (idx + 1));

      const esPotenciado = Number(p.__es_potenciado || 0) === 1;

      let badgesHtml = `<span class="ev-mp-badge ev-mp-badge-nuevo">Publicado</span>`;
      if (esPotenciado) badgesHtml += `<span class="ev-mp-badge ev-mp-badge-potenciado">Recomendado</span>`;

      return `
        <div class="ev-mp-card"
             data-category="${escapeHtml(catSlug)}"
             data-precio="${precioNum}"
             data-reciente="${ordenReciente}"
             data-id="${escapeHtml(String(id))}">
          <div class="ev-mp-card-media">
            <img src="${imgUrl}" alt="${titulo}">
            <div class="ev-mp-card-badges">${badgesHtml}</div>
          </div>
          <div class="ev-mp-card-body">
            <h5 class="ev-mp-card-title">${titulo}</h5>
            <p class="ev-mp-card-price">${precio}</p>
            <p style="font-size:13px;color:var(--ev-texto-suave);margin-bottom:6px;">${desc}</p>

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
    }).join('');

    refs.grid.innerHTML = cardsHtml;
    setResumen(`Mostrando ${lista.length} resultado${lista.length === 1 ? '' : 's'} en ${CONDO_NOMBRE_RESUMEN}`);

    Array.from(refs.grid.querySelectorAll('.ev-mp-card')).forEach((card) => {
      const id = card.getAttribute('data-id');
      const btnDetalle = card.querySelector('.ev-mp-btn-detalle');
      if (btnDetalle && id) btnDetalle.addEventListener('click', () => abrirModalDetalle(id));
    });
  }

  // -------------------------
  // Carga API
  // -------------------------
  async function cargarPublicaciones() {
    if (!refs.grid) return;

    refs.grid.innerHTML = '';
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

      aplicarFiltrosYRedibujar();

    } catch (e) {
      err('EXCEPTION cargarPublicaciones', e);
      publicaciones = [];
      showEmpty('Ocurrió un problema al cargar el Marketplace.');
      setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
    }
  }

  function bindEvents() {
    refs.chips.forEach((chip) => {
      chip.addEventListener('click', () => {
        refs.chips.forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        filtroCategoria = chip.dataset.filtro || 'todos';
        aplicarFiltrosYRedibujar();
      });
    });

    if (refs.searchInput) {
      refs.searchInput.addEventListener('input', () => {
        textoBusqueda = refs.searchInput.value || '';
        aplicarFiltrosYRedibujar();
      });
    }

    if (refs.selectOrdenar) {
      criterioOrden = refs.selectOrdenar.value || 'recientes';
      refs.selectOrdenar.addEventListener('change', () => {
        criterioOrden = refs.selectOrdenar.value || 'recientes';
        aplicarFiltrosYRedibujar();
      });
    }
  }

  function initMarketplace() {
    if (!capturarRefs()) return;
    bindEvents();
    cargarPublicaciones();
  }

  document.addEventListener('DOMContentLoaded', initMarketplace);

  const observer = new MutationObserver(() => {
    const gridActual = document.getElementById('mp_grid_publicaciones');
    if (gridActual && gridActual !== refs.grid) {
      capturarRefs();
      bindEvents();
      cargarPublicaciones();
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });

  window.EVMarketplace = { init: initMarketplace };

  log('Cargado. BASE:', BASE || '(vacío)', '| Condominio:', CONDO_NOMBRE_RESUMEN);
})();
