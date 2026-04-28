// views/js/menuPrincipalContenido.js
(function () {
  'use strict';

  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  if (!BASE) return;

  const cont = document.getElementById('evDestacadasPagadas');
  const msg  = document.getElementById('evDestacadasPagadasMensaje');
  const btnMarketplace = document.getElementById('btnMarketplace');
  const btnPublicacion = document.getElementById('btnPublicacion');

  function formatPrecio(v) {
    return Number(v).toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  if (btnMarketplace) {
    btnMarketplace.addEventListener('click', (e) => {
      e.preventDefault();
      const link = document.querySelector(`.submenu-link[data-vista="/marketplace"]`);
      if (link) link.click();
      else window.location.href = `${BASE}/marketplace`;
    });
  }

  if (btnPublicacion) {
    btnPublicacion.addEventListener('click', (e) => {
      e.preventDefault();
      const link = document.querySelector(`.submenu-link[data-vista="/publicacion"]`);
      if (link) link.click();
      else window.location.href = `${BASE}/publicacion`;
    });
  }

  function construirUrlImagen(imagenPortada) {
    let path = (imagenPortada || '').trim();
    if (!path) return `${BASE}/resources/images/no-image-ev.png`;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/')) return `${BASE}${path}`;
    return `${BASE}/${path}`;
  }

  // Blindaje: si la vista no tiene el contenedor, no ejecutar la carga.
  if (!cont) return;

  async function cargarDestacadas() {
    try {
      // Mejora opcional aplicada:
      // primero intenta endpoint por router; si no existe, hace fallback al controller directo.
      let json = null;

      try {
        const rApi = await fetch(`${BASE}/api/menu/destacadas-pagadas`, {
          credentials: 'include',
          headers: { 'Accept': 'application/json' },
          cache: 'no-store'
        });

        if (rApi.ok) {
          json = await rApi.json();
        }
      } catch (_) {}

      if (!json) {
        const rLegacy = await fetch(`${BASE}/controllers/menuListarDestacadasPagadasController.php`, {
          credentials: 'include',
          headers: { 'Accept': 'application/json' },
          cache: 'no-store'
        });
        json = await rLegacy.json();
      }

      if (!json?.ok || !Array.isArray(json.data) || json.data.length === 0) {
        cont.innerHTML = `<p class="text-muted small mb-0">No hay publicaciones destacadas.</p>`;
        if (msg) msg.classList.add('d-none');
        return;
      }

      const html = json.data.map((p) => {
        const img = construirUrlImagen(p.imagen_portada);
        const titulo = String(p.titulo || '').replace(/"/g, '&quot;');

        return `
          <article class="ev-card-destacada">
            <div class="ev-card-destacada-img position-relative">
              <img src="${img}" loading="lazy" alt="${titulo}">
              <span class="ev-card-destacada-badge">Destacado</span>
            </div>

            <div class="ev-card-destacada-body">
              <div class="ev-card-destacada-title">${p.titulo || ''}</div>
              <div class="ev-card-destacada-price">S/. ${formatPrecio(p.precio || 0)}</div>
            </div>
          </article>
        `;
      }).join('');

      cont.innerHTML = html;
    } catch (err) {
      console.error('Error destacadas:', err);
      cont.innerHTML = `<p class="text-danger small">Error cargando destacadas.</p>`;
    }
  }

  cargarDestacadas();
})();