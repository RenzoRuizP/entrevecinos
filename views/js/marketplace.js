/* marketplace.js
   Marketplace Entre Vecinos
   - Consume API de publicaciones publicadas (visible = 2)
   - Pinta cards con la UI actual
   - Filtro por categoría
   - Búsqueda por título / descripción
   - Ordenamiento
*/

(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  const LOG_PREFIX = '[MARKETPLACE]';

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
  let yaInicializado  = false;

  // ------------------------------------
  // Helpers
  // ------------------------------------
  function log() {
    console.log(LOG_PREFIX, ...arguments);
  }
  function warn() {
    console.warn(LOG_PREFIX, ...arguments);
  }
  function error() {
    console.error(LOG_PREFIX, ...arguments);
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
    if (!relPath) {
      // Ajusta este placeholder si deseas
      return BASE + '/public/img/placeholder-ev.png';
    }
    if (/^https?:\/\//i.test(relPath)) {
      return relPath;
    }
    return BASE + '/' + String(relPath).replace(/^\/+/, '');
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
  // Filtro + pintado
  // ------------------------------------
  function aplicarFiltrosYRedibujar() {
    if (!refs.grid) {
      warn('aplicarFiltrosYRedibujar llamado sin grid.');
      return;
    }

    let lista = Array.isArray(publicaciones) ? [...publicaciones] : [];

    // Filtro por categoría
    if (filtroCategoria && filtroCategoria !== 'todos') {
      lista = lista.filter((pub) => {
        const catSlug = String(
          pub.categoria_slug ||
          pub.tipo_slug ||
          pub.categoria_nombre ||
          pub.tipo_nombre ||
          ''
        ).toLowerCase();

        return catSlug.includes(filtroCategoria.slice(0, 4)); // "prod", "serv", etc.
      });
    }

    // Búsqueda por título + descripción
    if (textoBusqueda.trim() !== '') {
      const needle = normalizar(textoBusqueda);
      lista = lista.filter((pub) => {
        const haystack = normalizar(
          (pub.titulo || '') + ' ' + (pub.descripcion || '')
        );
        return haystack.includes(needle);
      });
    }

    // Ordenamiento
    lista.sort((a, b) => {
      const precioA  = Number(a.precio || 0);
      const precioB  = Number(b.precio || 0);
      const ratingA  = Number(a.rating || 0);
      const ratingB  = Number(b.rating || 0);

      const recA = a.orden_reciente || a.codigo_publicacion || 0;
      const recB = b.orden_reciente || b.codigo_publicacion || 0;

      switch (criterioOrden) {
        case 'precio_menor':
          return precioA - precioB;
        case 'precio_mayor':
          return precioB - precioA;
        case 'mejor_valorados':
          return ratingB - ratingA;
        case 'recientes':
        default:
          return recB - recA;
      }
    });

    pintarGrid(lista);
  }

  function pintarGrid(lista) {
    if (!refs.grid) return;

    if (!Array.isArray(lista) || lista.length === 0) {
      refs.grid.innerHTML = '';
      if (refs.emptyState) refs.emptyState.style.display = '';
      if (refs.resumenResultados) {
        refs.resumenResultados.textContent = 'Mostrando 0 resultados en El Pilar';
      }
      return;
    }

    if (refs.emptyState) refs.emptyState.style.display = 'none';

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

      return `
        <div class="ev-mp-card"
             data-category="${catSlug}"
             data-precio="${precioNum}"
             data-reciente="${ordenReciente}">
          <div class="ev-mp-card-media">
            <img src="${imgUrl}" alt="${titulo}">
            <div class="ev-mp-card-badges">
              <span class="ev-mp-badge ev-mp-badge-nuevo">Publicado</span>
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
                  <div class="ev-mp-vecino-condominio">
                    Tu condominio
                  </div>
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

    if (refs.resumenResultados) {
      const n = lista.length;
      refs.resumenResultados.textContent =
        `Mostrando ${n} resultado${n === 1 ? '' : 's'} en El Pilar`;
    }
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
    if (refs.emptyState) refs.emptyState.style.display = 'none';
    if (refs.resumenResultados) {
      refs.resumenResultados.textContent = 'Cargando publicaciones…';
    }

    try {
      const resp = await fetch(`${BASE}/api/publicacion/listar-publicadas`, {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      });

      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        notify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => {
          window.location.href = `${BASE}/`;
        }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        const msg = data.mensaje || data.error || 'No se pudo cargar el Marketplace.';
        error('ERROR API', data);
        notify('error', 'Error', msg);
        publicaciones = [];
        aplicarFiltrosYRedibujar();
        return;
      }

      publicaciones = Array.isArray(data.data) ? data.data : [];
      log('publicaciones recibidas:', publicaciones);
      aplicarFiltrosYRedibujar();

    } catch (err) {
      error('EXCEPTION', err);
      notify('error', 'Error inesperado', 'Ocurrió un problema al cargar el Marketplace.');
      publicaciones = [];
      aplicarFiltrosYRedibujar();
    }
  }

  // ------------------------------------
  // Eventos de UI
  // ------------------------------------
  function bindEvents() {
    // Chips
    refs.chips.forEach((chip) => {
      chip.addEventListener('click', () => {
        refs.chips.forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        filtroCategoria = chip.dataset.filtro || 'todos';
        aplicarFiltrosYRedibujar();
      });
    });

    // Búsqueda
    if (refs.searchInput) {
      refs.searchInput.addEventListener('input', () => {
        textoBusqueda = refs.searchInput.value || '';
        aplicarFiltrosYRedibujar();
      });
    }

    // Ordenar
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
    if (!capturarRefs()) {
      // No está montada la vista Marketplace en este momento.
      return;
    }

    // Permitir re-inicializar si se reemplazó el contenido
    log('Inicializando Marketplace…');
    yaInicializado = true;
    bindEvents();
    cargarPublicaciones();
  }

  // 1) Intento normal: cuando la página termina de cargar
  document.addEventListener('DOMContentLoaded', () => {
    log('DOMContentLoaded');
    initMarketplace();
  });

  // 2) Soporte para carga dinámica: cada vez que el contenido cambie
  const observer = new MutationObserver(() => {
    // Solo intentamos si aún no tenemos grid o si se reemplazó el nodo
    const gridActual = document.getElementById('mp_grid_publicaciones');
    if (gridActual && gridActual !== refs.grid) {
      log('Detectado mp_grid_publicaciones vía MutationObserver.');
      capturarRefs();
      bindEvents();
      cargarPublicaciones();
    }
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });

  // 3) Exponer init explícito por si algún JS quiere llamarlo luego de cargar la vista
  window.EVMarketplace = {
    init: initMarketplace
  };

  log('JS cargado. BASE_URL:', BASE || '(vacía)');
})();
