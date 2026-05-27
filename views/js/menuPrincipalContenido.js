// views/js/menuPrincipalContenido.js
// Dashboard principal del vecino - Entre Vecinos
// Versión validada: no duplica ni pisa el footer del sidebar.
(function () {
  'use strict';

  const BASE = (window.BASE_URL || window.EV_BASE_URL || '').toString().replace(/\/+$/, '');
  if (!BASE) return;

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
    if (txt.length <= max) return txt;
    return `${txt.slice(0, max - 3).trim()}...`;
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
    if (!ruta) return;

    if (window.EVNav && typeof window.EVNav.loadPage === 'function') {
      await window.EVNav.loadPage(ruta, { pushState: true, replaceState: false });
      return;
    }

    const link = Array.from(document.querySelectorAll('.submenu-link[data-vista]'))
      .find((el) => String(el.getAttribute('data-vista') || '').trim() === ruta);
    if (link) {
      link.click();
      return;
    }

    window.location.href = `${BASE}/MenuPrincipal?ev_goto=${encodeURIComponent(ruta)}`;
  }

  async function comunidadProximamente() {
    if (window.Swal?.fire) {
      await Swal.fire({
        icon: 'info',
        title: 'Comunidad estará en la siguiente fase',
        text: 'Aquí se mostrarán comunicados, eventos y noticias de tu condominio o urbanización.',
        confirmButtonColor: '#EA7C12'
      });
      return;
    }

    alert('Comunidad estará en la siguiente fase.');
  }

  function bindNavegacion(root) {
    qsa('[data-ev-route]', root).forEach((btn) => {
      if (btn.dataset.evBound === '1') return;
      btn.dataset.evBound = '1';

      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        await navegar(btn.dataset.evRoute || '');
      });
    });

    qsa('[data-ev-action="comunidad-proximamente"]', root).forEach((btn) => {
      if (btn.dataset.evBound === '1') return;
      btn.dataset.evBound = '1';

      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        await comunidadProximamente();
      });
    });
  }

  function normalizarTextoSeguro(value, fallback = '') {
    const txt = String(value ?? '').replace(/\s+/g, ' ').trim();
    return txt || fallback;
  }

  function construirNombreComunidadSidebar(label, nombre) {
    const labelBase = normalizarTextoSeguro(label, 'Comunidad').replace(/\s+actual$/i, '').trim();
    const nombreBase = normalizarTextoSeguro(nombre, '');

    if (!nombreBase) return '';

    if (!labelBase || labelBase.toLowerCase() === 'tu comunidad') {
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
    if (!sidebar) return;

    const footers = Array.from(sidebar.querySelectorAll('.ev-sidebar-footer, .ev-sidebar-home-extras'));
    const principal = document.getElementById('evSidebarHomeExtras') || footers[0] || null;

    footers.forEach((el) => {
      if (principal && el !== principal) {
        el.remove();
      }
    });

    const cards = Array.from(sidebar.querySelectorAll('.ev-sidebar-community-card'));
    const cardPrincipal = principal ? principal.querySelector('.ev-sidebar-community-card') : cards[0] || null;
    cards.forEach((card) => {
      if (cardPrincipal && card !== cardPrincipal) {
        card.remove();
      }
    });
  }

  function asegurarEventosFooterSidebar(extra) {
    if (!extra || extra.dataset.evSidebarFooterBound === '1') return;
    extra.dataset.evSidebarFooterBound = '1';

    const btnAyuda = extra.querySelector('#btnEvAyudaSidebar, [data-ev-extra-route="/ayuda"]');
    if (btnAyuda && btnAyuda.dataset.evBound !== '1') {
      btnAyuda.dataset.evBound = '1';
      btnAyuda.addEventListener('click', async (e) => {
        e.preventDefault();
        if (window.Swal?.fire) {
          await Swal.fire({
            icon: 'info',
            title: 'Ayuda EV',
            text: 'La vista de ayuda y reglas de uso se implementará en una próxima fase.',
            confirmButtonColor: '#EA7C12'
          });
          return;
        }
        alert('La vista de ayuda y reglas de uso se implementará en una próxima fase.');
      });
    }
  }

  function inyectarSidebarExtras(data = {}) {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    removerDuplicadosSidebar();

    let extra = document.getElementById('evSidebarHomeExtras') || sidebar.querySelector('.ev-sidebar-footer');

    // Fallback defensivo: solo se usa si la vista antigua no trae footer.
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

        <button type="button" class="ev-sidebar-footer-link ev-sidebar-footer-link-logout" onclick="window.location.href='${BASE}/logout'">
          <i class="bi bi-box-arrow-right"></i>
          <span>Cerrar sesión</span>
        </button>

        <article class="ev-sidebar-community-card" aria-label="Comunidad actual">
          <div class="ev-sidebar-community-icon" aria-hidden="true">
            <i class="bi bi-buildings"></i>
          </div>
          <div class="ev-sidebar-community-label">Tu comunidad</div>
          <div class="ev-sidebar-community-name" id="evSidebarCommunityName">Tu comunidad</div>
          <a href="${BASE}/mi-perfil" data-vista="/mi-perfil" class="ev-sidebar-community-btn">Cambiar comunidad</a>
        </article>
      `;
      sidebar.appendChild(extra);
    }

    asegurarEventosFooterSidebar(extra);

    const residencia = data.residencia || null;
    if (!residencia || typeof residencia !== 'object') return;

    const label = normalizarTextoSeguro(residencia.conjunto_label, 'Tu comunidad');
    const nombre = normalizarTextoSeguro(residencia.conjunto_nombre, '');
    const nombreFinal = construirNombreComunidadSidebar(label, nombre);

    // Importante: no pisar el nombre renderizado por PHP con textos genéricos
    // cuando aún no llegó la data del endpoint.
    if (nombreFinal) {
      const communityName = extra.querySelector('#evSidebarCommunityName, .ev-sidebar-community-name');
      if (communityName) communityName.textContent = nombreFinal;
    }
  }

  function colorClass(color) {
    const value = String(color || '').trim().toLowerCase();
    if (['verde', 'naranja', 'morado', 'azul', 'rojo'].includes(value)) return value;
    return 'verde';
  }

  function renderActividad(items) {
    const lista = Array.isArray(items) ? items : [];

    if (!lista.length) {
      return `
        <div class="ev-home-empty-state">
          <div class="ev-home-empty-icon"><i class="bi bi-clock-history"></i></div>
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
    if (!path) return `${BASE}/resources/images/no-image-ev.png`;
    if (/^https?:\/\//i.test(path)) return path;
    if (path.startsWith('/')) return `${BASE}${path}`;
    return `${BASE}/${path}`;
  }

  function renderPublicaciones(items) {
    const lista = Array.isArray(items) ? items : [];

    if (!lista.length) {
      return `
        <article class="ev-home-empty-state ev-home-publications-empty">
          <div class="ev-home-empty-icon"><i class="bi bi-shop-window"></i></div>
          <div>
            <strong>Aún no hay publicaciones recientes disponibles</strong>
            <p>Explora el Marketplace o revisa más tarde cuando tus vecinos publiquen productos o servicios.</p>
            <button type="button" class="ev-home-mini-action" data-ev-route="/marketplace">Ir al Marketplace</button>
          </div>
        </article>
      `;
    }

    return lista.map((p) => {
      const img = imageUrl(p.imagen_portada_url || p.imagen_portada);
      const tipo = String(p.tipo_publicacion || 'producto').toLowerCase() === 'servicio' ? 'Servicio' : 'Producto';
      const precioLabel = tipo === 'Servicio' ? `Desde ${money(p.precio)}` : money(p.precio);
      const reputacion = p.reputacion_texto || 'Nuevo vendedor';

      return `
        <article class="ev-home-publication-card">
          <div class="ev-home-publication-img">
            <img src="${escapeHtml(img)}" alt="${escapeHtml(p.titulo || 'Publicación')}" loading="lazy">
            <span>${escapeHtml(tipo)}</span>
          </div>
          <div class="ev-home-publication-body">
            <h3>${escapeHtml(truncar(p.titulo || 'Publicación', 54))}</h3>
            <strong>${escapeHtml(precioLabel)}</strong>
            <p>${escapeHtml(truncar(p.descripcion || '', 70))}</p>
            <div class="ev-home-publication-foot">
              <span><i class="bi bi-person-circle"></i> ${escapeHtml(truncar(p.nombre_vendedor || 'Vecino', 22))}</span>
              <small><i class="bi bi-star-fill"></i> ${escapeHtml(reputacion)}</small>
            </div>
          </div>
        </article>
      `;
    }).join('');
  }

  function pintarDashboard(root, data) {
    const residencia = data.residencia || {};
    const resumen = data.resumen || {};

    // La comunidad ya no se muestra dentro del hero para evitar duplicidad visual.
    // Se conserva únicamente en el sidebar.
    inyectarSidebarExtras({ residencia });

    const compras = Number(resumen.compras_activas || 0);
    const ventas = Number(resumen.ventas_pendientes || 0);
    const calificaciones = Number(resumen.calificaciones_pendientes || 0);

    const comprasEl = qs('#evDashComprasActivas', root);
    const ventasEl = qs('#evDashVentasPendientes', root);
    const calificacionesEl = qs('#evDashCalificacionesPendientes', root);
    const saldoEl = qs('#evDashSaldoBilletera', root);

    if (comprasEl) comprasEl.textContent = String(compras);
    if (ventasEl) ventasEl.textContent = String(ventas);
    if (calificacionesEl) calificacionesEl.textContent = String(calificaciones);
    if (saldoEl) saldoEl.textContent = money(resumen.saldo_billetera || 0);

    const comprasText = qs('#evDashComprasTexto', root);
    const ventasText = qs('#evDashVentasTexto', root);
    const calificacionesText = qs('#evDashCalificacionesTexto', root);

    if (comprasText) comprasText.textContent = textoCantidad(compras, 'pedido en proceso', 'pedidos en proceso');
    if (ventasText) ventasText.textContent = textoCantidad(ventas, 'pedido por atender', 'pedidos por atender');
    if (calificacionesText) calificacionesText.textContent = textoCantidad(calificaciones, 'opinión por registrar', 'opiniones por registrar');

    const actividad = qs('#evDashActividadLista', root);
    if (actividad) actividad.innerHTML = renderActividad(data.actividad_reciente || []);

    const publicaciones = qs('#evDashPublicacionesLista', root);
    if (publicaciones) publicaciones.innerHTML = renderPublicaciones(data.publicaciones_recientes || []);

    bindNavegacion(root);
  }

  async function cargarDashboard(root) {
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

      inyectarSidebarExtras({});

      const actividad = qs('#evDashActividadLista', root);
      if (actividad) actividad.innerHTML = renderActividad([]);

      const publicaciones = qs('#evDashPublicacionesLista', root);
      if (publicaciones) publicaciones.innerHTML = renderPublicaciones([]);
    }
  }

  function initDashboard() {
    const root = document.getElementById('evHomeDashboardV2');
    if (!root) return;

    bindNavegacion(root);
    inyectarSidebarExtras({});
    cargarDashboard(root);
  }

  document.addEventListener('DOMContentLoaded', initDashboard);
  document.addEventListener('ev:content-loaded', initDashboard);

  window.EVHomeDashboard = Object.assign(window.EVHomeDashboard || {}, {
    init: initDashboard,
    refresh: initDashboard
  });
})();
