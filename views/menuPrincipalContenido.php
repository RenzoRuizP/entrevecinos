<div class="container-fluid py-4 ev-home-dashboard">

  <div class="row g-4">

    <!-- ================================
         COLUMNA IZQUIERDA
    ================================= -->
    <div class="col-lg-8">

      <!-- BIENVENIDA -->
      <div class="card shadow-sm border-0 rounded-4 p-3" style="background:#ffffff;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

          <div>
            <h3 class="fw-bold mb-1" style="color:#0F592F;">
              Hola, <?= $nombreUsuario ?> 👋
            </h3>
            <p class="text-muted mb-0 fs-6">
              Este es tu marketplace dentro del condominio.<br>
              ¿Qué quieres hacer hoy?
            </p>
          </div>

          <div class="d-flex align-items-center justify-content-center rounded-circle"
               style="width:110px;height:110px;background:#FFF7F2;">
            <img src="<?= BASE_URL ?>resources/images/logo/logo8.png"
                 alt="logo EV"
                 class="img-fluid"
                 style="max-height:70px;">
          </div>
        </div>
      </div>



      <!-- ================================
           PUBLICACIONES DESTACADAS
      ================================= -->
      <div class="card shadow-sm border-0 rounded-4 mt-4 p-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-semibold mb-0" style="color:#0F592F;">
            Publicaciones destacadas (pagadas)
          </h5>

          <i class="bi bi-chevron-right" style="font-size:1rem;color:#0F592F;"></i>
        </div>

        <!-- NUEVO CONTENEDOR DINÁMICO -->
        <div id="evDestacadasPagadas" class="ev-destacadas-wrapper">
          <!-- Cards se pintan por JS -->
        </div>

        <p class="text-muted small mt-3 mb-0" id="evDestacadasPagadasMensaje">
          Estas publicaciones han sido impulsadas por tus vecinos.
        </p>

      </div>

    </div>



    <!-- ================================
         COLUMNA DERECHA
    ================================= -->
    <div class="col-lg-4">

      <!-- COMPRAR -->
      <div class="card shadow-sm border-0 rounded-4 p-4 mb-3 card-accion-comprar" style="background:#FFF9F0;">
        <div class="d-flex flex-column align-items-start">
          <i class="bi bi-cart3 fs-3 mb-2" style="color:#0F592F;"></i>
          <h5 class="fw-bold mb-1" style="color:#0F592F;">COMPRAR</h5>
          <p class="text-muted mb-3 small">Ir al marketplace de tu condominio</p>

          <a href="<?= BASE_URL ?>views/marketplace.php"
             class="btn px-4 py-2 text-white btn-ev-comprar">
            Ir al Marketplace
          </a>
        </div>
      </div>


      <!-- VENDER -->
      <div class="card shadow-sm border-0 rounded-4 p-4 mb-3 card-accion-vender" style="background:#FFF7F5;">
        <div class="d-flex flex-column align-items-start">
          <i class="bi bi-cash-coin fs-3 mb-2" style="color:#BF3604;"></i>
          <h5 class="fw-bold mb-1" style="color:#BF3604;">VENDER</h5>
          <p class="text-muted mb-3 small">Publica un producto o servicio</p>

          <a href="<?= BASE_URL ?>views/publicaciones.php"
             class="btn px-4 py-2 text-white btn-ev-vender">
            Crear publicación
          </a>
        </div>
      </div>


      <!-- CONSEJOS -->
      <div class="card shadow-sm border-0 rounded-4 p-4 card-consejos">
        <h6 class="fw-semibold mb-2" style="color:#0F592F;">Consejos de seguridad</h6>
        <ul class="text-muted small ps-3 mb-0">
          <li>Verifica los perfiles de los vendedores.</li>
          <li>Prefiere áreas comunes para entregar.</li>
          <li>No compartas datos personales innecesarios.</li>
          <li>Confirma la reputación del comprador.</li>
        </ul>
      </div>

    </div>

  </div>

</div>

