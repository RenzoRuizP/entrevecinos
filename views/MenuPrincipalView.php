<?php
require_once __DIR__ . '/../Config/config.php';
// __DIR__ = C:\xampp\htdocs\entrevecinos\views\
$menusParaMenuIzquierda = $menus ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entre Vecinos - Menú Principal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#22c55e">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>resources/images/logo/logo.png">
    
    <!-- ✅ Estilos -->
    <?php include_once __DIR__ . '/estilos.view.php'; ?>
    <?php include_once __DIR__ . '/estilos/MenuPrincipal.estilo.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">
    
    <!-- 🔹 Menú Superior -->
    <?php include __DIR__ . '/menuArribaView.php'; ?>

    <!-- 🔹 Menú Izquierdo -->
    <?php include __DIR__ . '/menuIzquierdaView.php'; ?>

    <!-- 🔹 Contenido principal -->
    <main class="app-main">
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <h3 class="mb-0"></h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <div class="app-content">
        <div class="container-fluid">
          <!-- 🔹 Aquí se cargará el contenido dinámico -->
          <div id="contenido-principal">
            <div class="row">
              <div class="col-lg-12 col-12">
                <div class="card mb-4">
                  <div class="callout callout-primary">
                    <h5>¿Qué te gustaría hacer hoy, vecino?</h5>
                  </div>
                  <div class="card-body">
                    <div class="row justify-content-center">
                      <!-- Tarjeta COMPRAR -->
                      <div class="col-lg-3 col-md-3 col-sm-6">
                        <div class="small-box text-bg-success">
                          <div class="inner">
                            <h3 class="text-uppercase fw-bold fs-4">COMPRAR</h3>
                            <p>Productos y/o Servicios</p>
                          </div>
                          <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 3a1 1 0 011-1h1.22a1 1 0 01.97.757L6.89 5H21a1 1 0 01.96 1.274l-2 7A1 1 0 0119 14H8.28l-.94 3.764A1 1 0 016.36 19H5a1 1 0 110-2h.64l1.6-6.4L4.28 5H3a1 1 0 01-1-1zM9 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm8 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                          </svg>
                          <a href="#" class="small-box-footer link-light">
                            Entrar <i class="bi bi-arrow-right-circle-fill"></i>
                          </a>
                        </div>
                      </div>

                      <!-- Tarjeta VENDER -->
                      <div class="col-lg-3 col-md-3 col-sm-6">
                        <div class="small-box text-bg-warning text-dark">
                          <div class="inner">
                            <h3 class="text-uppercase fw-bold fs-4">VENDER</h3>
                            <p>Productos y/o Servicios</p>
                          </div>
                          <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24">
                            <text x="0" y="18" font-size="18" font-family="Arial, sans-serif">S/.</text>
                          </svg>
                          <a href="#" class="small-box-footer link-dark">
                            Entrar <i class="bi bi-arrow-right-circle-fill"></i>
                          </a>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div> <!-- /#contenido-principal -->
        </div>
      </div> <!-- /.app-content -->
    </main>

    <footer class="app-footer">
      <div class="float-end d-none d-sm-inline">Anything you want</div>
      <strong>Copyright &copy; 2025-2030 
        <a href="#" class="text-decoration-none">Entrevecinos</a>.
      </strong> All rights reserved.
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
