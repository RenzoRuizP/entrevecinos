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

  // ============================================================
  // 🔹 Botón "btnMarketplace" → cargar vista /marketplace con AJAX
  // ============================================================
  if (btnMarketplace) {
    btnMarketplace.addEventListener("click", (e) => {
      e.preventDefault();

      // Cerrar dropdown del usuario
      const dropdownInstance = bootstrap.Dropdown.getInstance(userDropdown);
      dropdownInstance?.hide();

      // Buscar en el menú lateral el enlace a /marketplace
      const link = document.querySelector(`.submenu-link[href="/marketplace"]`);

      if (link) {
        link.click(); // Dispara el flujo AJAX de menuIzquierda.js
      } else {
        // Fallback seguro si no se encuentra el link (no debería pasar)
        window.location.href = `${window.BASE_URL || '/entrevecinos'}/marketplace`;
      }
    });
  }

    // ============================================================
  // 🔹 Botón "btnPublicacion" → cargar vista /publicacion con AJAX
  // ============================================================
  if (btnPublicacion) {
    btnPublicacion.addEventListener("click", (e) => {
      e.preventDefault();

      // Cerrar dropdown del usuario
      const dropdownInstance = bootstrap.Dropdown.getInstance(userDropdown);
      dropdownInstance?.hide();

      // Buscar en el menú lateral el enlace a /publicacion
      const link = document.querySelector(`.submenu-link[href="/publicacion"]`);

      if (link) {
        link.click(); // Dispara el flujo AJAX de menuIzquierda.js
      } else {
        // Fallback seguro si no se encuentra el link (no debería pasar)
        window.location.href = `${window.BASE_URL || '/entrevecinos'}/publicacion`;
      }
    });
  }

  // Construye la URL de la imagen de forma robusta
  function construirUrlImagen(imagen_portada) {
    let path = (imagen_portada || '').trim();

    if (!path) {
      // Sin imagen -> placeholder
      return `${BASE}/resources/images/no-image-ev.png`;
    }

    // Si ya viene una URL completa (por si en algún momento usas CDN)
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path;
    }

    // Si comienza con "/", solo anteponemos BASE
    if (path.startsWith('/')) {
      return `${BASE}${path}`;
    }

    // Caso típico en tu BD: "uploads/publicaciones/2/16/img_1_xxx.jpg"
    // No agregamos otro "uploads", solo BASE + "/" + path
    return `${BASE}/${path}`;
  }

  fetch(`${BASE}/controllers/menuListarDestacadasPagadasController.php`)
    .then(r => r.json())
    .then(json => {

      if (!json.ok || !json.data || !json.data.length) {
        cont.innerHTML = `
          <p class="text-muted small mb-0">No hay publicaciones destacadas.</p>
        `;
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
