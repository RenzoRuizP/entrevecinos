<?php
require_once __DIR__ . '/../Config/config.php';
$menusParaMenuIzquierda = $menus ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Entre Vecinos - Menú Principal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#0F592F">
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>resources/images/logo/logo8.png">

  <!-- ✅ Estilos base -->
  <?php include_once __DIR__ . '/estilos.view.php'; ?>

  
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open">
  <div class="app-wrapper">

    <!-- 🔹 Menú Superior -->
    <?php include __DIR__ . '/menuArribaView.php'; ?>

    <!-- 🔹 Menú Izquierdo -->
    <?php include __DIR__ . '/menuIzquierdaView.php'; ?>

    <!-- 🔹 Contenido principal -->
    <main class="content-wrapper">
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <h3 class="fw-bold" style="color:#0F592F;">Panel Principal</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="MenuPrincipalView.php" style="color:#078C03;">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <div class="app-content">
        <div class="container-fluid">

          <!-- 🔹 Contenido -->
          <div id="contenido-principal">
            <div class="row">
              <div class="col-lg-12 col-12">
                <div class="card mb-4 border-0 shadow-sm rounded-4">
                  <div class="callout callout-primary">
                    <h5 class="fw-semibold">¿Qué te gustaría hacer hoy, vecino?</h5>
                  </div>
                  <div class="card-body">
                    <div class="row justify-content-center g-4">

                      <!-- 🛒 Tarjeta COMPRAR -->
                      <div class="col-lg-3 col-md-4 col-sm-6">
                        <a href="#" class="link-light">
                          <div class="small-box" style="background-color: #FFF9F0; color: #0F592F; border: 1px solid #E5E7EB;">
                            <div class="inner">
                              <h3 class="text-uppercase fw-bold fs-4">COMPRAR</h3>
                              <p>Productos y/o Servicios</p>
                            </div>
                            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M3 3a1 1 0 011-1h1.22a1 1 0 01.97.757L6.89 5H21a1 1 0 01.96 1.274l-2 7A1 1 0 0119 14H8.28l-.94 3.764A1 1 0 016.36 19H5a1 1 0 110-2h.64l1.6-6.4L4.28 5H3a1 1 0 01-1-1zM9 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm8 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                            </svg>
                          </div>
                        </a>
                      </div>

                      <!-- 💰 Tarjeta VENDER -->
                      
                          
                      <div class="col-lg-3 col-md-4 col-sm-6">
                        <a href="#" class="link-light">
                          <div class="small-box" style="background-color: #FFF9F0; color: #BF3604; border:1px solid #E5E7EB;">
                            <div class="inner">
                              <h3 class="text-uppercase fw-bold fs-4">VENDER</h3>
                              <p>Productos y/o Servicios</p>
                            </div>
                            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24">
                              <text x="0" y="18" font-size="18" font-family="Arial, sans-serif" opacity="0.5">S/.</text>
                              <text x="0.3" y="18" font-size="18" font-family="Arial, sans-serif">S/.</text>
                            </svg>
                          </div>
                        </a>
                      </div>
                      


                      <!-- 🧾 Tarjeta COMUNICADOS -->
                      <!--<div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="small-box" style="background-color: #BF3604; color:#fff;">
                          <div class="inner">
                            <h3 class="text-uppercase fw-bold fs-4">COMUNICADOS</h3>
                            <p>Anuncios y avisos</p>
                          </div>
                          <i class="bi bi-chat-dots small-box-icon"></i>
                          <a href="#" class="small-box-footer link-light" style="background: rgba(255,255,255,0.1);">
                            Ver <i class="bi bi-arrow-right-circle-fill"></i>
                          </a>
                        </div>
                      </div>-->
                      <!-- 📘 Tarjeta TUTORIALES / AYUDA -->
                        <!--<div class="col-lg-3 col-md-4 col-sm-6">
                          <div class="small-box" style="background-color: #FFF9F0; color: #0F592F; border:1px solid #E5E7EB;">
                            <div class="inner">
                              <h3 class="text-uppercase fw-bold fs-4">TUTORIALES</h3>
                              <p>Guías y ayuda visual</p>
                            </div>
                            <i class="bi bi-info-circle small-box-icon" style="color:#0F592F; opacity:0.3;"></i>
                            <a href="#" class="small-box-footer link-dark" style="background:rgba(15,89,47,0.05);">
                              Ver <i class="bi bi-arrow-right-circle-fill"></i>
                            </a>
                          </div>
                        </div>-->

                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- /#contenido-principal -->

        </div>
      </div> <!-- /.app-content -->
    </main>

    <footer class="app-footer text-center small py-3">
      <strong>© <?= date('Y') ?> <a href="#" class="text-decoration-none">Entre Vecinos</a></strong>  
      <span class="text-muted">| Conectando tu comunidad</span>
    </footer>
  </div>

  <!-- ✅ Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="<?= BASE_URL ?>resources/util/lte4/dist/js/adminlte.js"></script>
  <script src="<?= BASE_URL ?>views/js/menuPrincipal.js"></script>
</body>
</html>
