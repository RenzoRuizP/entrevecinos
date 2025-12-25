(function () {
  const BASE = (window.BASE_URL || '').replace(/\/$/, '');
  const cont = document.getElementById('evDestacadasPagadas');
  const msg  = document.getElementById('evDestacadasPagadasMensaje');
  const btnMarketplace = document.getElementById("btnMarketplace");
  const btnPublicacion = document.getElementById("btnPublicacion");

  function formatPrecio(v) {
    return Number(v).toLocaleString("es-PE", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  if (btnMarketplace) {
    btnMarketplace.addEventListener("click", (e) => {
      e.preventDefault();
      const link = document.querySelector(`.submenu-link[data-vista="/marketplace"]`);
      if (link) link.click();
      else window.location.href = `${BASE}/marketplace`;
    });
  }

  if (btnPublicacion) {
    btnPublicacion.addEventListener("click", (e) => {
      e.preventDefault();
      const link = document.querySelector(`.submenu-link[data-vista="/publicacion"]`);
      if (link) link.click();
      else window.location.href = `${BASE}/publicacion`;
    });
  }

  function construirUrlImagen(imagen_portada) {
    let path = (imagen_portada || '').trim();
    if (!path) return `${BASE}/resources/images/no-image-ev.png`;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/')) return `${BASE}${path}`;
    return `${BASE}/${path}`;
  }

  fetch(`${BASE}/controllers/menuListarDestacadasPagadasController.php`)
    .then(r => r.json())
    .then(json => {
      if (!json.ok || !json.data || !json.data.length) {
        cont.innerHTML = `<p class="text-muted small mb-0">No hay publicaciones destacadas.</p>`;
        msg && msg.classList.add("d-none");
        return;
      }

      const html = json.data.map(p => {
        const img = construirUrlImagen(p.imagen_portada);
        return `
        <article class="ev-card-destacada">
          <div class="ev-card-destacada-img position-relative">
            <img src="${img}" loading="lazy" alt="${(p.titulo || '').replace(/"/g, '&quot;')}">
            <span class="ev-card-destacada-badge">Destacado</span>
          </div>

          <div class="ev-card-destacada-body">
            <div class="ev-card-destacada-title">${p.titulo || ''}</div>
            <div class="ev-card-destacada-price">S/. ${formatPrecio(p.precio || 0)}</div>
          </div>
        </article>`;
      }).join("");

      cont.innerHTML = html;
    })
    .catch(err => {
      console.error("Error destacadas:", err);
      cont.innerHTML = `<p class="text-danger small">Error cargando destacadas.</p>`;
    });
})();
