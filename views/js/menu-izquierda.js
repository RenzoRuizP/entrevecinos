document.addEventListener("DOMContentLoaded", () => {
  const baseURL = window.BASE_URL || "/entrevecinos";
  const contenedor = document.getElementById("contenido-principal");

  // Delegación de eventos para submenús
  document.querySelectorAll(".submenu-link").forEach(link => {
    link.addEventListener("click", async e => {
      e.preventDefault();

      const ruta = link.getAttribute("href");
      if (!ruta || ruta === "#") return;

      const url = `${baseURL}/index.php?r=${ruta.replace(/^\//, "")}`;
      console.log("📄 Cargando vista desde:", url);

      contenedor.innerHTML = `
        <div class="text-center p-5">
          <div class="spinner-border text-success" role="status"></div>
          <p class="mt-3">Cargando...</p>
        </div>
      `;

      try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

        const html = await response.text();
        contenedor.innerHTML = html;

        // ✅ Marcar activo
        document.querySelectorAll(".submenu-link").forEach(el => el.classList.remove("active"));
        link.classList.add("active");

      } catch (error) {
        console.error("❌ Error al cargar vista:", error);
        contenedor.innerHTML = `
          <div class="alert alert-danger m-5 shadow-sm rounded-3">
            <h5 class="mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Error</h5>
            <p>No se pudo cargar el contenido solicitado.</p>
            <small class="text-muted">${error.message}</small>
          </div>
        `;
      }
    });
  });
});
