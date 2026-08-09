// views/js/menuPrincipalContenido.js
// Dashboard principal del vecino - Entre Vecinos
// Versión final premium:
// - Hero dinámico por tipo de comunidad.
// - Sidebar sincronizado con residencia real.
// - Comunidad conectada al Dashboard.
// - Renderizado seguro de métricas, actividad, comunidad y publicaciones.
(function () {
  'use strict';

  const BASE = (window.EV?.baseUrl ?? window.BASE_URL ?? window.EV_BASE_URL ?? '').toString().replace(/\/+$/, '');

  const FETCH_TIMEOUT_MS = 6500;

  function qs(selector, root = document) {
    return root.querySelector(selector);
  }

  function qsa(selector, root = document) {
    return Array.from(root.querySelectorAll(selector));
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function money(value) {
    const n = Number(value || 0);

    return `S/ ${n.toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })}`;
  }

  function textoCantidad(n, singular, plural) {
    const total = Number(n || 0);
    return `${total} ${total === 1 ? singular : plural}`;
  }

  function truncar(value, max = 92) {
    const txt = String(value || '').replace(/\s+/g, ' ').trim();

    if (txt.length <= max) {
      return txt;
    }

    return `${txt.slice(0, max - 3).trim()}...`;
  }

  function normalizarTextoSeguro(value, fallback = '') {
    const txt = String(value ?? '').replace(/\s+/g, ' ').trim();
    return txt || fallback;
  }

  function primerNombre(value, fallback = 'Vecino(a)') {
    const nombre = normalizarTextoSeguro(value, fallback);
    const partes = nombre.split(/\s+/).filter(Boolean);
    return partes[0] || fallback;
  }

  function tipoComunidadSeguro(value) {
    const tipo = String(value || '').trim().toLowerCase();

    if (tipo === 'urbanizacion' || tipo === 'condominio') {
      return tipo;
    }

    return 'generico';
  }

  function iconoComunidad(tipo) {
    if (tipo === 'urbanizacion') {
      return 'bi bi-houses';
    }

    if (tipo === 'condominio') {
      return 'bi bi-buildings';
    }

    return 'bi bi-house-heart';
  }

  async function fetchJson(url, options = {}, timeoutMs = FETCH_TIMEOUT_MS) {
    const controller = new AbortController();
    const timer = window.setTimeout(() => controller.abort(), timeoutMs);

    try {
      const resp = await fetch(url, {
        ...options,
        signal: controller.signal,
        credentials: 'include',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...(options.headers || {})
        }
      });

      const json = await resp.json().catch(() => ({}));
      return { resp, json };
    } finally {
      window.clearTimeout(timer);
    }
  }

  async function navegar(route) {
    const ruta = String(route || '').trim();

    if (!ruta) {
      return;
    }

    if (window.EVNav && typeof window.EVNav.loadPage === 'function') {
      await window.EVNav.loadPage(ruta, {
        pushState: true,
        replaceState: false
      });
      return;
    }

    const rutaSinQuery = ruta.split('?', 1)[0];

    const link = Array.from(document.querySelectorAll('.submenu-link[data-vista]'))
      .find((el) => String(el.getAttribute('data-vista') || '').trim() === rutaSinQuery);

    if (link && ruta === rutaSinQuery) {
      link.click();
      return;
    }

    window.location.href = `${BASE}/MenuPrincipal?ev_goto=${encodeURIComponent(ruta)}`;
  }

  function guardarPublicacionComunidadSeleccionada(codigoPublicacion) {
    const id = Number(codigoPublicacion || 0);

    if (id <= 0) {
      return 0;
    }

    try {
      sessionStorage.setItem('ev_comunidad_publicacion_seleccionada', String(id));
      sessionStorage.setItem('ev_comunidad_publicacion_seleccionada_at', String(Date.now()));
    } catch (_) {}

    return id;
  }

  async function irAComunidadSeleccionada(codigoPublicacion) {
    const id = guardarPublicacionComunidadSeleccionada(codigoPublicacion);
    const ruta = id > 0
      ? `/comunidad?publicacion=${encodeURIComponent(String(id))}`
      : '/comunidad';

    await navegar(ruta);
  }

  function bindNavegacion(root) {
    qsa('[data-ev-comunidad-publicacion]', root).forEach((el) => {
      if (el.dataset.evCommunityBound === '1') {
        return;
      }

      el.dataset.evCommunityBound = '1';

      el.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        await irAComunidadSeleccionada(el.dataset.evComunidadPublicacion || '');
      });

      el.addEventListener('keydown', async (e) => {
        if (e.key !== 'Enter' && e.key !== ' ') {
          return;
        }

        e.preventDefault();
        await irAComunidadSeleccionada(el.dataset.evComunidadPublicacion || '');
      });
    });

    qsa('[data-ev-route]', root).forEach((btn) => {
      if (btn.dataset.evBound === '1') {
        return;
      }

      btn.dataset.evBound = '1';

      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        await navegar(btn.dataset.evRoute || '');
      });
    });
  }

  function construirNombreComunidadSidebar(label, nombre) {
    const labelBase = normalizarTextoSeguro(label, 'Comunidad')
      .replace(/\s+actual$/i, '')
      .trim();

    const nombreBase = normalizarTextoSeguro(nombre, '');

    if (!nombreBase) {
      return '';
    }

    if (nombreBase.toLocaleLowerCase('es-PE') === 'tu comunidad') {
      return 'Tu comunidad';
    }

    if (
      !labelBase ||
      labelBase.toLocaleLowerCase('es-PE') === 'comunidad' ||
      labelBase.toLocaleLowerCase('es-PE') === 'tu comunidad'
    ) {
      return nombreBase;
    }

    const labelLower = labelBase.toLocaleLowerCase('es-PE');
    const nombreLower = nombreBase.toLocaleLowerCase('es-PE');

    if (nombreLower === labelLower || nombreLower.startsWith(`${labelLower} `)) {
      return nombreBase;
    }

    return `${labelBase} ${nombreBase}`.trim();
  }

  function removerDuplicadosSidebar() {
    const sidebar = document.getElementById('sidebar');

    if (!sidebar) {
      return;
    }

    const footers = Array.from(
      sidebar.querySelectorAll('.ev-sidebar-footer, .ev-sidebar-home-extras')
    );

    const principal = document.getElementById('evSidebarHomeExtras') || footers[0] || null;

    footers.forEach((el) => {
      if (principal && el !== principal) {
        el.remove();
      }
    });

    const cards = Array.from(sidebar.querySelectorAll('.ev-sidebar-community-card'));
    const cardPrincipal = principal
      ? principal.querySelector('.ev-sidebar-community-card')
      : cards[0] || null;

    cards.forEach((card) => {
      if (cardPrincipal && card !== cardPrincipal) {
        card.remove();
      }
    });
  }

  function asegurarEventosFooterSidebar(extra) {
    if (!extra || extra.dataset.evSidebarFooterBound === '1') {
      return;
    }

    extra.dataset.evSidebarFooterBound = '1';

    const btnAyuda = extra.querySelector('#btnEvAyudaSidebar, [data-ev-extra-route="/ayuda"]');

    if (btnAyuda && btnAyuda.dataset.evBound !== '1') {
      btnAyuda.dataset.evBound = '1';

      btnAyuda.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (window.Swal?.fire) {
          await Swal.fire({
            title: 'Ayuda EV',
            html: `
              <div class="ev-help-modal-icon" aria-hidden="true"><i class="bi bi-headset"></i></div>
              <div class="ev-help-modal-copy">
                <strong>Estamos para ayudarte</strong>
                <p>Escríbenos por WhatsApp y cuéntanos brevemente qué necesitas. El equipo de Soporte EV te orientará.</p>
                <a class="ev-help-modal-contact" href="https://wa.me/51956969182" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp"></i><span>956 969 182</span></a>
              </div>
            `,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#EA7C12',
            showConfirmButton: true,
            showCancelButton: false,
            showDenyButton: false,
            showCloseButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
              popup: 'ev-help-modal-popup',
              title: 'ev-help-modal-title',
              htmlContainer: 'ev-help-modal-html',
              confirmButton: 'ev-help-modal-confirm',
              closeButton: 'ev-swal-close'
            }
          });
          return;
        }

        alert('Ayuda EV\n\nEscríbenos por WhatsApp al 956 969 182 y cuéntanos brevemente qué necesitas. Soporte EV te orientará.');
      });
    }
  }

  function actualizarTipoSidebar(extra, residencia = {}) {
    if (!extra) {
      return;
    }

    const tipo = tipoComunidadSeguro(residencia.tipo_conjunto);
    const card = extra.querySelector('.ev-sidebar-community-card');
    const icon = extra.querySelector('#evSidebarCommunityIcon, .ev-sidebar-community-icon i');

    if (card) {
      card.dataset.communityType = tipo;
    }

    if (icon) {
      icon.className = iconoComunidad(tipo);
    }
  }

  function inyectarSidebarExtras(data = {}) {
    const sidebar = document.getElementById('sidebar');

    if (!sidebar) {
      return;
    }

    removerDuplicadosSidebar();

    let extra = document.getElementById('evSidebarHomeExtras') ||
      sidebar.querySelector('.ev-sidebar-footer');

    /*
     * Fallback defensivo: solo opera si una versión antigua del sidebar
     * todavía no trae el footer definitivo.
     */
    if (!extra) {
      extra = document.createElement('div');
      extra.id = 'evSidebarHomeExtras';
      extra.className = 'ev-sidebar-footer';
      extra.setAttribute('aria-label', 'Accesos secundarios');

      extra.innerHTML = `
        <button type="button" class="ev-sidebar-footer-link" id="btnEvAyudaSidebar">
          <i class="bi bi-question-circle"></i>
          <span>Ayuda</span>
        </button>

        <button type="button"
                class="ev-sidebar-footer-link ev-sidebar-footer-link-logout"
                data-ev-logout="1"
                data-logout-url="${BASE}/logout">
          <i class="bi bi-box-arrow-right"></i>
          <span>Cerrar sesión</span>
        </button>

        <article class="ev-sidebar-community-card"
                 data-community-type="generico"
                 aria-label="Comunidad actual">
          <div class="ev-sidebar-community-icon" aria-hidden="true">
            <i class="bi bi-house-heart" id="evSidebarCommunityIcon"></i>
          </div>

          <div class="ev-sidebar-community-label">Tu comunidad</div>

          <div class="ev-sidebar-community-name" id="evSidebarCommunityName">
            Tu comunidad
          </div>

          <a href="${BASE}/mi-perfil"
             data-vista="/mi-perfil"
             class="ev-sidebar-community-btn">
            Cambiar comunidad
          </a>
        </article>
      `;

      sidebar.appendChild(extra);
    }

    asegurarEventosFooterSidebar(extra);

    const residencia = data.residencia || null;

    if (!residencia || typeof residencia !== 'object') {
      return;
    }

    actualizarTipoSidebar(extra, residencia);

    const label = normalizarTextoSeguro(residencia.conjunto_label, 'Comunidad actual');
    const nombre = normalizarTextoSeguro(residencia.conjunto_nombre, '');
    const nombreFinal = construirNombreComunidadSidebar(label, nombre);

    if (nombreFinal) {
      const communityName = extra.querySelector(
        '#evSidebarCommunityName, .ev-sidebar-community-name'
      );

      if (communityName) {
        communityName.textContent = nombreFinal;
      }
    }
  }

  function aplicarHeroPorComunidad(root, residencia = {}) {
    const hero = qs('#evHomeHero', root) || qs('.ev-home-hero', root);

    if (!hero) {
      return;
    }

    const tipo = tipoComunidadSeguro(residencia.tipo_conjunto);

    hero.classList.remove(
      'ev-home-hero--urbanizacion',
      'ev-home-hero--condominio',
      'ev-home-hero--generico'
    );

    hero.classList.add(`ev-home-hero--${tipo}`);
    hero.dataset.communityType = tipo;
  }

  function actualizarSaludo(root, usuario = {}) {
    const saludo = qs('#evDashSaludoNombre', root);

    if (!saludo) {
      return;
    }

    const nombre = primerNombre(usuario.nombre || '', '');

    if (nombre) {
      saludo.textContent = nombre;
    }
  }

  function colorClass(color) {
    const value = String(color || '').trim().toLowerCase();

    if (['verde', 'naranja', 'morado', 'azul', 'rojo', 'gris'].includes(value)) {
      return value;
    }

    return 'verde';
  }

  function renderActividad(items) {
    const lista = Array.isArray(items) ? items : [];

    if (!lista.length) {
      return `
        <div class="ev-home-empty-state">
          <div class="ev-home-empty-icon">
            <i class="bi bi-clock-history"></i>
          </div>

          <div>
            <strong>Aún no hay actividad reciente</strong>
            <p>Cuando compres, vendas o recibas notificaciones, aparecerán aquí.</p>
          </div>
        </div>
      `;
    }

    return lista.map((item) => {
      const color = colorClass(item.color);
      const icono = item.icono || 'bi-bell';
      const titulo = item.titulo || 'Movimiento reciente';
      const detalle = item.detalle || '';
      const tiempo = item.tiempo || '';

      return `
        <article class="ev-home-activity-item">
          <div class="ev-home-activity-icon is-${color}">
            <i class="bi ${escapeHtml(icono)}"></i>
          </div>

          <div class="ev-home-activity-copy">
            <strong>${escapeHtml(titulo)}</strong>
            <p>${escapeHtml(truncar(detalle, 110))}</p>
          </div>

          <time>${escapeHtml(tiempo)}</time>
        </article>
      `;
    }).join('');
  }

  function imageUrl(value) {
    const path = String(value || '').trim();

    if (!path) {
      return `${BASE}/resources/images/no-image-ev.png`;
    }

    if (/^https?:\/\//i.test(path)) {
      return path;
    }

    /*
     * El API ya puede retornar rutas absolutas respecto al host,
     * por ejemplo /entrevecinos/resources/...
     * No deben volver a concatenarse con BASE.
     */
    if (path.startsWith('/')) {
      return path;
    }

    return `${BASE}/${path}`;
  }

  function tipoNovedadLabel(tipo) {
    const value = String(tipo || '').trim().toLowerCase();

    if (value === 'noticia') return 'Noticia';
    if (value === 'evento') return 'Evento';

    return 'Comunicado';
  }

  function iconoNovedad(tipo) {
    const value = String(tipo || '').trim().toLowerCase();

    if (value === 'noticia') return 'bi-newspaper';
    if (value === 'evento') return 'bi-calendar-event';

    return 'bi-megaphone';
  }

  function prioridadClass(value) {
    const prioridad = String(value || '').trim().toLowerCase();

    if (prioridad === 'urgente') return 'urgente';
    if (prioridad === 'importante') return 'importante';

    return 'normal';
  }

  function renderComunidad(payload) {
    const data = payload && typeof payload === 'object' ? payload : {};
    const items = Array.isArray(data.items) ? data.items : [];
    const counts = data.counts && typeof data.counts === 'object' ? data.counts : {};

    if (data.habilitado === false) {
      return `
        <article class="ev-home-community-empty">
          <div class="ev-home-empty-icon">
            <i class="bi bi-house-heart"></i>
          </div>

          <div>
            <strong>Tu comunidad aún no está habilitada</strong>
            <p>
              Cuando tu residencia esté validada, aquí verás comunicados,
              noticias y eventos oficiales.
            </p>
          </div>
        </article>
      `;
    }

    if (!items.length) {
      const totalPublicadasComunidad = Number(data.total_publicadas_comunidad || 0);
      const tienePublicacionesSinDestacar = totalPublicadasComunidad > 0;

      return `
        <article class="ev-home-community-empty">
          <div class="ev-home-empty-icon">
            <i class="bi ${tienePublicacionesSinDestacar ? 'bi-star' : 'bi-newspaper'}"></i>
          </div>

          <div>
            <strong>${tienePublicacionesSinDestacar
              ? 'No hay novedades destacadas en el inicio'
              : 'No hay novedades destacadas por ahora'
            }</strong>
            <p>${tienePublicacionesSinDestacar
              ? 'Hay publicaciones visibles en Comunidad, pero ninguna está marcada como destacada para el dashboard.'
              : 'Cuando la administración marque una publicación como destacada, aparecerá aquí.'
            }</p>

            <button type="button" class="ev-home-mini-action" data-ev-route="/comunidad">
              Ir a Comunidad
            </button>
          </div>
        </article>
      `;
    }

    const total = Number(counts.total || data.total_activos || items.length || 0);
    const comunicados = Number(counts.comunicados || 0);
    const eventos = Number(counts.eventos || 0);

    return `
      <div class="ev-home-community-summary">
        <div class="ev-home-community-summary-copy">
          <span>Novedades oficiales</span>
          <strong>${escapeHtml(total)}</strong>
          <small>
            ${escapeHtml(comunicados)} comunicados · ${escapeHtml(eventos)} eventos
          </small>
        </div>

        <button type="button" class="ev-home-mini-action" data-ev-route="/comunidad">
          Ver Comunidad
        </button>
      </div>

      <div class="ev-home-community-list">
        ${items.map((item) => {
          const tipo = String(item.tipo_publicacion || 'comunicado').trim().toLowerCase();
          const prioridad = prioridadClass(item.prioridad);
          const img = imageUrl(item.imagen_portada_url || item.imagen_portada);
          const titulo = item.titulo || tipoNovedadLabel(tipo);
          const resumen = item.resumen || '';
          const tiempo = item.tiempo || item.fecha_label || '';

          const codigoPublicacion = Number(item.codigo_publicacion || 0);

          return `
            <article
              class="ev-home-community-card is-${escapeHtml(prioridad)}"
              role="button"
              tabindex="0"
              data-ev-comunidad-publicacion="${escapeHtml(codigoPublicacion)}"
              aria-label="Ver novedad: ${escapeHtml(titulo)}"
            >
              <div class="ev-home-community-thumb">
                <img
                  src="${escapeHtml(img)}"
                  alt="${escapeHtml(titulo)}"
                  loading="lazy"
                >
              </div>

              <div class="ev-home-community-body">
                <div class="ev-home-community-meta">
                  <span>
                    <i class="bi ${escapeHtml(iconoNovedad(tipo))}"></i>
                    ${escapeHtml(tipoNovedadLabel(tipo))}
                  </span>

                  ${prioridad !== 'normal'
                    ? `<em>${prioridad === 'urgente' ? 'Urgente' : 'Importante'}</em>`
                    : ''
                  }
                </div>

                <h3>${escapeHtml(truncar(titulo, 68))}</h3>
                <p>${escapeHtml(truncar(resumen, 96))}</p>

                <div class="ev-home-community-foot">
                  <time>${escapeHtml(tiempo)}</time>
                </div>
              </div>
            </article>
          `;
        }).join('')}
      </div>
    `;
  }

  function renderPublicaciones(items) {
    const lista = Array.isArray(items) ? items : [];

    if (!lista.length) {
      return `
        <article class="ev-home-empty-state ev-home-publications-empty">
          <div class="ev-home-empty-icon">
            <i class="bi bi-shop-window"></i>
          </div>

          <div>
            <strong>Aún no hay publicaciones recientes disponibles</strong>
            <p>Explora el Marketplace o revisa más tarde cuando tus vecinos publiquen productos o servicios.</p>

            <button type="button" class="ev-home-mini-action" data-ev-route="/marketplace">
              Ir al Marketplace
            </button>
          </div>
        </article>
      `;
    }

    return lista.map((p) => {
      const img = imageUrl(p.imagen_portada_url || p.imagen_portada);
      const tipo = String(p.tipo_publicacion || 'producto').toLowerCase() === 'servicio'
        ? 'Servicio'
        : 'Producto';

      const precioLabel = tipo === 'Servicio'
        ? `Desde ${money(p.precio)}`
        : money(p.precio);

      const reputacion = p.reputacion_texto || 'Nuevo vendedor';

      return `
        <article class="ev-home-publication-card">
          <div class="ev-home-publication-img">
            <img
              src="${escapeHtml(img)}"
              alt="${escapeHtml(p.titulo || 'Publicación')}"
              loading="lazy"
            >
            <span>${escapeHtml(tipo)}</span>
          </div>

          <div class="ev-home-publication-body">
            <h3>${escapeHtml(truncar(p.titulo || 'Publicación', 54))}</h3>
            <strong>${escapeHtml(precioLabel)}</strong>
            <p>${escapeHtml(truncar(p.descripcion || '', 70))}</p>

            <div class="ev-home-publication-foot">
              <span>
                <i class="bi bi-person-circle"></i>
                ${escapeHtml(truncar(p.nombre_vendedor || 'Vecino', 22))}
              </span>

              <small>
                <i class="bi bi-star-fill"></i>
                ${escapeHtml(reputacion)}
              </small>
            </div>
          </div>
        </article>
      `;
    }).join('');
  }

  function pintarDashboard(root, data) {
    const usuario = data.usuario || {};
    const residencia = data.residencia || {};
    const resumen = data.resumen || {};

    actualizarSaludo(root, usuario);
    aplicarHeroPorComunidad(root, residencia);
    inyectarSidebarExtras({ residencia });

    const compras = Number(resumen.compras_activas || 0);
    const ventas = Number(resumen.ventas_pendientes || 0);
    const calificaciones = Number(resumen.calificaciones_pendientes || 0);

    const comprasEl = qs('#evDashComprasActivas', root);
    const ventasEl = qs('#evDashVentasPendientes', root);
    const calificacionesEl = qs('#evDashCalificacionesPendientes', root);
    const saldoEl = qs('#evDashSaldoBilletera', root);

    if (comprasEl) {
      comprasEl.textContent = String(compras);
    }

    if (ventasEl) {
      ventasEl.textContent = String(ventas);
    }

    if (calificacionesEl) {
      calificacionesEl.textContent = String(calificaciones);
    }

    if (saldoEl) {
      saldoEl.textContent = money(resumen.saldo_billetera || 0);
    }

    const comprasText = qs('#evDashComprasTexto', root);
    const ventasText = qs('#evDashVentasTexto', root);
    const calificacionesText = qs('#evDashCalificacionesTexto', root);

    if (comprasText) {
      comprasText.textContent = textoCantidad(
        compras,
        'pedido en proceso',
        'pedidos en proceso'
      );
    }

    if (ventasText) {
      ventasText.textContent = textoCantidad(
        ventas,
        'pedido por atender',
        'pedidos por atender'
      );
    }

    if (calificacionesText) {
      calificacionesText.textContent = textoCantidad(
        calificaciones,
        'opinión por registrar',
        'opiniones por registrar'
      );
    }

    const actividad = qs('#evDashActividadLista', root);

    if (actividad) {
      actividad.innerHTML = renderActividad(data.actividad_reciente || []);
    }

    const comunidad = qs('#evDashComunidadLista', root);

    if (comunidad) {
      comunidad.innerHTML = renderComunidad(data.novedades_comunidad || {});
    }

    const publicaciones = qs('#evDashPublicacionesLista', root);

    if (publicaciones) {
      publicaciones.innerHTML = renderPublicaciones(data.publicaciones_recientes || []);
    }

    bindNavegacion(root);
  }

  async function cargarDashboard(root) {
    if (!root || root.dataset.evDashboardLoading === '1') {
      return;
    }

    root.dataset.evDashboardLoading = '1';

    const errorBox = qs('#evDashError', root);
    errorBox?.classList.add('d-none');

    try {
      const { resp, json } = await fetchJson(`${BASE}/api/dashboard/vecino`);

      if (resp.status === 401 || resp.status === 403 || resp.status === 409) {
        throw new Error(json?.mensaje || 'Sesión o cuenta no disponible.');
      }

      if (!resp.ok || json?.ok === false) {
        throw new Error(json?.mensaje || 'No se pudo cargar el dashboard.');
      }

      pintarDashboard(root, json.data || {});
    } catch (e) {
      console.warn('[EV][Dashboard] No se pudo cargar el dashboard:', e);

      errorBox?.classList.remove('d-none');

      const actividad = qs('#evDashActividadLista', root);

      if (actividad) {
        actividad.innerHTML = renderActividad([]);
      }

      const comunidad = qs('#evDashComunidadLista', root);

      if (comunidad) {
        comunidad.innerHTML = renderComunidad({
          habilitado: true,
          items: [],
          counts: {}
        });
      }

      const publicaciones = qs('#evDashPublicacionesLista', root);

      if (publicaciones) {
        publicaciones.innerHTML = renderPublicaciones([]);
      }

      bindNavegacion(root);
    } finally {
      delete root.dataset.evDashboardLoading;
    }
  }

  function initDashboard() {
    const root = document.getElementById('evHomeDashboardV2');

    if (!root) {
      return;
    }

    bindNavegacion(root);
    inyectarSidebarExtras({});
    cargarDashboard(root);
  }

  document.addEventListener('DOMContentLoaded', initDashboard);
  document.addEventListener('ev:content-loaded', initDashboard);

  window.EVHomeDashboard = Object.assign(window.EVHomeDashboard || {}, {
    init: initDashboard,
    refresh: function () {
      const root = document.getElementById('evHomeDashboardV2');

      if (root) {
        cargarDashboard(root);
      }
    }
  });
})();
