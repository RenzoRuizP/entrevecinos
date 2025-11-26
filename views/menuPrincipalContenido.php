<div class="container-fluid py-4">

  <div class="row g-4">

    <!-- =========================
         COLUMNA IZQUIERDA: ACCIONES RÁPIDAS
    ========================== -->
    <div class="col-lg-8">

      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 pb-2">

          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
              <h5 class="mb-1 fw-bold" style="color:#0F592F;">
                <i class="bi bi-house-door me-1"></i>
                Hola, <?= $nombreUsuario ?> 👋
              </h5>
              <p class="text-muted small mb-0">
                ¿Qué deseas hacer hoy dentro de tu condominio?
              </p>
            </div>

            <div class="d-flex align-items-center gap-2">
              <span class="badge rounded-pill px-3 py-2"
                    style="background-color:#0F592F; color:#ffffff;">
                Rol: <?= ucfirst($rolUsuario) ?>
              </span>
            </div>
          </div>

        </div>

        <div class="card-body pt-3">

          <!-- 🔹 Acciones rápidas -->
          <div class="row g-3">

            <!-- 🛒 COMPRAR -->
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
              <a href="#" class="text-decoration-none">
                <div class="h-100 border-0 shadow-sm rounded-4 p-3"
                     style="background-color:#FFF9F0; border:1px solid #E5E7EB;">
                  <div class="d-flex flex-column align-items-center text-center">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle"
                         style="width:52px;height:52px;background:#0F592F10;color:#0F592F;">
                      <i class="bi bi-cart3 fs-4"></i>
                    </div>
                    <h3 class="fw-bold fs-6 text-uppercase mb-1" style="color:#0F592F;">COMPRAR</h3>
                    <p class="mb-0 text-muted small">Explora productos y servicios publicados por tus vecinos.</p>
                  </div>
                </div>
              </a>
            </div>

            <!-- 💰 VENDER -->
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
              <a href="#" class="text-decoration-none">
                <div class="h-100 border-0 shadow-sm rounded-4 p-3"
                     style="background-color:#FFF9F0; border:1px solid #E5E7EB;">
                  <div class="d-flex flex-column align-items-center text-center">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle"
                         style="width:52px;height:52px;background:#F9731610;color:#BF3604;">
                      <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <h3 class="fw-bold fs-6 text-uppercase mb-1" style="color:#BF3604;">VENDER</h3>
                    <p class="mb-0 text-muted small">Publica lo que tienes y llega rápido a tus vecinos.</p>
                  </div>
                </div>
              </a>
            </div>

            <!-- 📦 MIS PEDIDOS / PUBLICACIONES (placeholder para futuro) -->
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
              <a href="#" class="text-decoration-none">
                <div class="h-100 border-0 shadow-sm rounded-4 p-3"
                     style="background-color:#F9FAFB; border:1px dashed #D1D5DB;">
                  <div class="d-flex flex-column align-items-center text-center">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle"
                         style="width:52px;height:52px;background:#0F592F08;color:#0F592F;">
                      <i class="bi bi-receipt-cutoff fs-4"></i>
                    </div>
                    <h3 class="fw-bold fs-6 text-uppercase mb-1" style="color:#111827;">Mis movimientos</h3>
                    <p class="mb-0 text-muted small">
                      Consulta tus publicaciones, pedidos y estado de tus transacciones.
                    </p>
                  </div>
                </div>
              </a>
            </div>

          </div>

          <!-- 🔹 Línea divisoria suave -->
          <hr class="mt-4 mb-3" />

          <!-- 🔹 Sección futura de publicaciones destacadas -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 fw-semibold" style="color:#0F592F;">
              Últimas publicaciones en tu condominio
            </h6>
            <span class="badge bg-light text-muted border"
                  style="font-size:0.75rem;">
              Próximamente
            </span>
          </div>
          <p class="text-muted small mb-0">
            Aquí verás las publicaciones más recientes de tus vecinos para comprar más rápido.
          </p>

        </div>
      </div>
    </div>

    <!-- =========================
         COLUMNA DERECHA: MARCA + AYUDA
    ========================== -->
    <div class="col-lg-4">

      <!-- Logo / Marca -->
      <div class="card shadow-sm border-0 rounded-4 mb-3 text-center py-4">
        <div class="card-body">
          <img src="<?= BASE_URL ?>resources/images/logo/logo8.png"
               alt="Logo Entre Vecinos"
               class="img-fluid mb-3"
               style="max-height: 120px;">
          <p class="text-muted small mb-0">
            El marketplace seguro para comprar y vender dentro de tu condominio.
          </p>
        </div>
      </div>

      <!-- Consejos de seguridad -->
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
          <h6 class="fw-semibold mb-2" style="color:#0F592F;">
            Consejos rápidos de seguridad
          </h6>
          <ul class="text-muted small ps-3 mb-0">
            <li>Verifica siempre el vecino y su número de departamento.</li>
            <li>Coordina entregas en zonas comunes seguras del condominio.</li>
            <li>No compartas datos sensibles por el chat.</li>
            <li>Reporta cualquier comportamiento sospechoso al administrador.</li>
          </ul>
        </div>
      </div>

    </div>

  </div>

</div>
