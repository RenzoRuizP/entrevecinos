/* marketplace.js
   Marketplace Entre Vecinos
   - Consume API de publicaciones publicadas
   - Pinta cards con la UI actual
   - Filtros por categoría
   - Búsqueda por título / descripción
   - Ordenamiento
   - Recomendados (potenciadas)
   - VER DETALLE: modal
   ✅ FIX de raíz:
     - fetch con credentials para enviar cookie auth_token
     - parsing robusto (API puede devolver HTML o diferente shape)
     - logs claros + resumen de error visible
*/

(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
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

  // ------------------------------------
  // Helpers
  // ------------------------------------
  function log() {
    if (window.console && console.log) console.log(LOG_PREFIX, ...arguments);
  }
  function warn() {
    if (window.console && console.warn) console.warn(LOG_PREFIX, ...arguments);
  }
  function error() {
    if (window.console && console.error) console.error(LOG_PREFIX, ...arguments);
  }

  const notify = (icon, title, text) => {
    if (typeof window.evNotify === 'function') {
      window.evNotify(icon, title, text);
    } else if (window.Swal?.fire) {
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
    if (isNaN(n)) return 'S/ 0.00';
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

  function buildImgUrl(relPath) {
    if (!relPath) return BASE + '/public/img/placeholder-ev.png';
    if (/^https?:\/\//i.test(relPath)) return relPath;
    return BASE + '/' + String(relPath).replace(/^\/+/, '');
  }

  async function fetchJsonRobusto(url, opts) {
    const resp = await fetch(url, opts);

    const text = await resp.text();
    let json = null;

    try {
      json = text ? JSON.parse(text) : {};
    } catch (_) {
      json = null;
    }

    return { resp, text, json };
  }

  function normalizarListaDesdeAPI(payload) {
    // Esperado: { ok:true, data:[...] }
    if (!payload || typeof payload !== 'object') return [];

    const d = payload.data;

    if (Array.isArray(d)) return d;

    // Variantes comunes
    if (Array.isArray(payload.publicaciones)) return payload.publicaciones;
    if (Array.isArray(payload.items)) return payload.items;
    if (d && Array.isArray(d.items)) return d.items;
    if (d && Array.isArray(d.publicaciones)) return d.publicaciones;

    return [];
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

  // ------------------------------------
  // Captura de referencias DOM
  // ------------------------------------
  function capturarRefs() {
    refs.grid              = document.getElementById('mp_grid_publicaciones');
    refs.resumenResultados = document.getElementById('mp_resumen_resultados');
    refs.searchInput       = document.getElementById('mp_busqueda');
    refs.emptyState        = document.getElementById('mp_empty_state');
    refs.selectOrdenar     = document.getElementById('mp_orden');
    refs.chips             = Array.from(document.querySelectorAll('.ev-mp-chip'));

    log('grid existe?', !!refs.grid);
    return !!refs.grid;
  }

  // ------------------------------------
  // Modal detalle
  // ------------------------------------
  async function abrirModalDetalle(codigoPublicacion) {
    if (!codigoPublicacion) {
      warn('abrirModalDetalle llamado sin código de publicación');
      return;
    }

    try {
      const url = `${BASE}/api/publicacion/detalle/${encodeURIComponent(codigoPublicacion)}`;

      const { resp, text, json } = await fetchJsonRobusto(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin' // ✅ CRÍTICO: manda cookie auth_token
      });

      if (resp.status === 401) {
        notify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${BASE}/`; }, 1500);
        return;
      }

      if (!json) {
        error('DETALLE no devolvió JSON. Body:', text?.slice(0, 300));
        notify('error', 'Error', 'La API devolvió una respuesta no válida (no JSON). Revisa consola.');
        return;
      }

      if (!resp.ok || !json.ok) {
        const msg = json.mensaje || json.error || 'No se pudo obtener el detalle de la publicación.';
        error('ERROR API DETALLE', json);
        notify('error', 'Error', msg);
        return;
      }

      const pub = json.data || {};

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
        warn('No se encontraron elementos del modal de detalle en el DOM.');
        return;
      }

      const titulo   = pub.titulo || '';
      const precio   = pub.precio || 0;
      const catName  = pub.categoria_nombre || '';
      const tipoName = pub.tipo_nombre || '';
      const desc     = pub.descripcion || '';

      const imagenes = Array.isArray(pub.imagenes) ? pub.imagenes : [];
      let portada  = pub.imagen_portada || '';

      if (!portada && imagenes.length > 0) {
        const first = imagenes[0];
        portada = first.url || buildImgUrl(first.ruta);
      }

      tituloTxtEl.textContent = titulo;
      precioEl.textContent    = formatPrecio(precio);
      catEl.textContent       = catName;
      tipoEl.textContent      = tipoName;
      descEl.textContent      = desc;

      imgPrincipalEl.src = portada || (BASE + '/public/img/placeholder-ev.png');
      imgPrincipalEl.alt = titulo || 'Imagen de publicación';

      thumbsWrapper.innerHTML = '';
      imagenes.forEach((imgObj, index) => {
        const url = imgObj.url || buildImgUrl(imgObj.ruta);

        const thumbWrapper = document.createElement('div');
        thumbWrapper.className = 'ev-mp-modal-thumb';

        const thumbImg = document.createElement('img');
        thumbImg.src   = url;
        thumbImg.alt   = `Imagen ${index + 1} de ${titulo || 'publicación'}`;

        thumbWrapper.appendChild(thumbImg);

        thumbWrapper.addEventListener('click', () => {
          imgPrincipalEl.src = url;
          document
            .querySelectorAll('.ev-mp-modal-thumb')
            .forEach(el => el.classList.remove('active'));
          thumbWrapper.classList.add('active');
        });

        thumbsWrapper.appendChild(thumbWrapper);
      });

      const firstThumb = thumbsWrapper.querySelector('.ev-mp-modal-thumb');
      if (firstThumb) firstThumb.classList.add('active');

      if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      } else if (typeof $ === 'function' && typeof $(modalEl).modal === 'function') {
        $(modalEl).modal('show');
      } else {
        warn('Bootstrap Modal no disponible; no se puede mostrar el modal de detalle.');
      }

    } catch (err) {
      error('EXCEPTION DETALLE', err);
      notify('error', 'Error inesperado', 'Ocurrió un problema al cargar el detalle de la publicación.');
    }
  }

  // ------------------------------------
  // Filtro + pintado
  // ------------------------------------
  function aplicarFiltrosYRedibujar() {
    if (!refs.grid) {
      warn('aplicarFiltrosYRedibujar llamado sin grid.');
      return;
    }

    let lista = Array.isArray(publicaciones) ? [...publicaciones] : [];

    if (filtroCategoria && filtroCategoria !== 'todos') {
      if (filtroCategoria === 'recomendados') {
        lista = lista.filter((pub) => Number(pub.es_potenciado || 0) === 1);
      } else if (filtroCategoria === 'productos') {
        lista = lista.filter((pub) => {
          const t = normalizar(pub.tipo_nombre || pub.tipo_slug || '');
          const c = normalizar(pub.categoria_nombre || pub.categoria_slug || '');
          return t.includes('producto') || c.includes('producto');
        });
      } else if (filtroCategoria === 'servicios') {
        lista = lista.filter((pub) => {
          const t = normalizar(pub.tipo_nombre || pub.tipo_slug || '');
          const c = normalizar(pub.categoria_nombre || pub.categoria_slug || '');
          return t.includes('servicio') || c.includes('servicio');
        });
      }
    }

    if (textoBusqueda.trim() !== '') {
      const needle = normalizar(textoBusqueda);
      lista = lista.filter((pub) => {
        const haystack = normalizar((pub.titulo || '') + ' ' + (pub.descripcion || ''));
        return haystack.includes(needle);
      });
    }

    lista.sort((a, b) => {
      const precioA  = Number(a.precio || 0);
      const precioB  = Number(b.precio || 0);
      const ratingA  = Number(a.rating || 0);
      const ratingB  = Number(b.rating || 0);
      const recA     = a.orden_reciente || a.codigo_publicacion || 0;
      const recB     = b.orden_reciente || b.codigo_publicacion || 0;

      switch (criterioOrden) {
        case 'precio_menor':    return precioA - precioB;
        case 'precio_mayor':    return precioB - precioA;
        case 'mejor_valorados': return ratingB - ratingA;
        case 'recientes':
        default:                return recB - recA;
      }
    });

    pintarGrid(lista);
  }

  function pintarGrid(lista) {
    if (!refs.grid) return;

    if (!Array.isArray(lista) || lista.length === 0) {
      showEmpty(`No encontramos publicaciones con los filtros actuales.`);
      setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
      return;
    }

    hideEmpty();

    const cardsHtml = lista.map((pub, idx) => {
      const titulo = escapeHtml(pub.titulo || '');
      const desc   = escapeHtml(pub.descripcion || '');
      const precio = formatPrecio(pub.precio);
      const imgUrl = buildImgUrl(pub.imagen_portada);

      const rating     = Number(pub.rating || 0);
      const ratingText = rating > 0 ? rating.toFixed(1) : '';
      const ventasText = pub.ventas_texto || '';

      const catSlug = String(
        pub.categoria_slug ||
        pub.tipo_slug ||
        pub.categoria_nombre ||
        pub.tipo_nombre ||
        'todos'
      ).toLowerCase();

      const precioNum     = Number(pub.precio || 0) || 0;
      const ordenReciente =
        pub.orden_reciente ||
        pub.codigo_publicacion ||
        (idx + 1);

      const esPotenciado = Number(pub.es_potenciado || 0) === 1;

      let badgesHtml = `
        <span class="ev-mp-badge ev-mp-badge-nuevo">Publicado</span>
      `;
      if (esPotenciado) {
        badgesHtml += `
        <span class="ev-mp-badge ev-mp-badge-potenciado">Recomendado</span>
        `;
      }

      return `
        <div class="ev-mp-card"
             data-category="${catSlug}"
             data-precio="${precioNum}"
             data-reciente="${ordenReciente}"
             data-id="${pub.codigo_publicacion || ''}">
          <div class="ev-mp-card-media">
            <img src="${imgUrl}" alt="${titulo}">
            <div class="ev-mp-card-badges">
              ${badgesHtml}
            </div>
          </div>
          <div class="ev-mp-card-body">
            <h5 class="ev-mp-card-title">${titulo}</h5>
            <p class="ev-mp-card-price">${precio}</p>

            <p style="font-size:13px;color:var(--ev-texto-suave);margin-bottom:6px;">
              ${desc}
            </p>

            <div class="ev-mp-card-meta">
              <div class="ev-mp-card-vecino">
                <div class="ev-mp-avatar">
                  ${(titulo || '?').charAt(0).toUpperCase()}
                </div>
                <div>
                  <div class="ev-mp-vecino-nombre">Vecino</div>
                  <div class="ev-mp-vecino-condominio">Tu condominio</div>
                </div>
              </div>
              <div class="ev-mp-card-rating">
                ${ratingText ? `<i class="bi bi-star-fill"></i><span>${ratingText}</span>` : ''}
                ${ventasText ? `<span class="ev-mp-rating-votos">(${ventasText})</span>` : ''}
              </div>
            </div>

            <div class="ev-mp-card-actions">
              <button type="button" class="btn btn-outline-success ev-mp-btn-detalle">
                Ver detalle
              </button>
              <button type="button" class="btn btn-success ev-mp-btn-pedir">
                Pedir ahora
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');

    refs.grid.innerHTML = cardsHtml;

    const n = lista.length;
    setResumen(`Mostrando ${n} resultado${n === 1 ? '' : 's'} en ${CONDO_NOMBRE_RESUMEN}`);

    const cards = Array.from(refs.grid.querySelectorAll('.ev-mp-card'));
    cards.forEach((card) => {
      const pubId = card.getAttribute('data-id');
      const btnDetalle = card.querySelector('.ev-mp-btn-detalle');

      if (btnDetalle && pubId) {
        btnDetalle.addEventListener('click', () => abrirModalDetalle(pubId));
      }
    });
  }

  // ------------------------------------
  // Cargar desde la API
  // ------------------------------------
  async function cargarPublicaciones() {
    if (!refs.grid) {
      warn('cargarPublicaciones llamado sin grid.');
      return;
    }

    refs.grid.innerHTML = '';
    hideEmpty();
    setResumen('Cargando publicaciones…');

    const url = `${BASE}/api/publicacion/listar-publicadas`;

    try {
      const { resp, text, json } = await fetchJsonRobusto(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin' // ✅ CRÍTICO: manda cookie auth_token
      });

      // Caso: backend devolvió HTML (login/redirect) u otro contenido
      if (!json) {
        error('API no devolvió JSON. URL:', url);
        error('Status:', resp.status);
        error('Body (primeros 400 chars):', (text || '').slice(0, 400));

        publicaciones = [];
        showEmpty('No se pudo cargar el Marketplace (respuesta no válida). Revisa consola.');
        setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        return;
      }

      if (resp.status === 401) {
        notify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !json.ok) {
        const msg = json.mensaje || json.error || 'No se pudo cargar el Marketplace.';
        error('ERROR API listar-publicadas', json);
        publicaciones = [];
        showEmpty(msg);
        setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        return;
      }

      publicaciones = normalizarListaDesdeAPI(json);
      log('publicaciones recibidas:', publicaciones);

      if (!Array.isArray(publicaciones) || publicaciones.length === 0) {
        // ✅ Esto ya es “negocio”: o no está PUBLICADO, o no coincide condominio, o el API filtra.
        warn('API OK pero lista vacía. Posibles causas: publicación no está en estado PUBLICADO o filtro por condominio.');
        showEmpty(`No hay publicaciones publicadas en ${CONDO_NOMBRE_RESUMEN} todavía.`);
        setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
        return;
      }

      aplicarFiltrosYRedibujar();

    } catch (err) {
      error('EXCEPTION cargarPublicaciones', err);
      publicaciones = [];
      showEmpty('Ocurrió un problema al cargar el Marketplace.');
      setResumen(`Mostrando 0 resultados en ${CONDO_NOMBRE_RESUMEN}`);
    }
  }

  // ------------------------------------
  // Eventos de UI
  // ------------------------------------
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

  // ------------------------------------
  // Inicialización
  // ------------------------------------
  function initMarketplace() {
    if (!capturarRefs()) return;
    log('Inicializando Marketplace…');
    bindEvents();
    cargarPublicaciones();
  }

  document.addEventListener('DOMContentLoaded', () => {
    log('DOMContentLoaded');
    initMarketplace();
  });

  const observer = new MutationObserver(() => {
    const gridActual = document.getElementById('mp_grid_publicaciones');
    if (gridActual && gridActual !== refs.grid) {
      log('Detectado mp_grid_publicaciones vía MutationObserver.');
      capturarRefs();
      bindEvents();
      cargarPublicaciones();
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });

  window.EVMarketplace = { init: initMarketplace };

  log('JS cargado. BASE_URL:', BASE || '(vacía)', '| Condominio:', CONDO_NOMBRE_RESUMEN);
})();
