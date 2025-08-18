<?php
require_once '../models/SesionJWT.php';

// Evitar que el navegador almacene esta página en caché
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Obtener el token JWT desde la cookie
$token = $_COOKIE['auth_token'] ?? null;

// Redirigir si no hay token
if (!$token) {
    header("Location: login-v2.php");
    exit;
}

try {
    // Verificar token (esto lanza excepción si expira o es inválido)
    $usuario = SesionJWT::verificarToken($token);
   // var_dump($usuario->rol);
    //exit;
    if (!$usuario) {
        header("Location: login-v2.php?error=token_expirado");
        exit;
    }

    // Extraer los datos del usuario
    $nombreUsuario = $usuario->nombre ?? 'Usuario';
    

} catch (Exception $e) {
    // Token inválido o expirado
    header("Location: login-v2.php?error=token_error");
    exit;
}
?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Entre Vecinos | Inicio</title>
    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
    <!--begin::Primary Meta Tags-->
    <meta name="title" content="Entre Vecinos | Inicio" />
    <meta name="author" content="ColorlibHQ" />
    <link rel="icon" type="image/png" href="../resources/images/logo/logo.png">
    <!--end::Primary Meta Tags-->
    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../resources/util/lte4/dist/css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->
    <?php include_once 'estilos.view.php'; ?>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <?php include_once 'menuArribaView.php'; ?>
      
      <!--end::Header-->
      <!--begin::Sidebar-->
      <?php include_once 'menuIzquierdaView.php';?>
      
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Hola <?php echo htmlspecialchars($nombreUsuario); ?></h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <!-- Start col -->
              <div class="col-lg-12 col-12">
                <div class="card mb-4">
                  <div class="callout callout-primary">
                  <h5>¿Qué te gustaría hacer hoy, vecino?</h5>
                </div>
                  <div class="card-body">
                    <!-- Agrupación en fila -->
                    <div class="row justify-content-center">
                      <!-- Tarjeta 1 -->
                      <div class="col-lg-3 col-md-3 col-sm-6">
                        <div class="small-box text-bg-success">
                          <div class="inner">
                            <h3>COMPRAR</h3>
                            <p>Productos y/o Servicios</p>
                          </div>
                          <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                              xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M3 3a1 1 0 011-1h1.22a1 1 0 01.97.757L6.89 5H21a1 1 0 01.96 1.274l-2 7A1 1 0 0119 14H8.28l-.94 3.764A1 1 0 016.36 19H5a1 1 0 110-2h.64l1.6-6.4L4.28 5H3a1 1 0 01-1-1zM9 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm8 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                          </svg>
                          <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                            Entrar <i class="bi bi-link-45deg"></i>
                          </a>
                        </div>
                      </div>

                      <!-- Tarjeta 2 -->
                      <div class="col-lg-3 col-md-3 col-sm-6">
                        <div class="small-box text-bg-warning">
                          <div class="inner">
                            <h3>VENDER</h3>
                            <p>Productos y/o Servicios</p>
                          </div>
                          <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <text x="0" y="18" font-size="18" font-family="Arial, sans-serif">S/.</text>
                          </svg>
                          <a href="#" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                            Entrar <i class="bi bi-link-45deg"></i>
                          </a>
                        </div>
                      </div>

                    </div>
                    <!-- Fin agrupación -->
                  </div>
                </div>
                <!-- /.card -->
              </div>
            </div>
            <!-- /.row (main row) -->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>

      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2025-2030&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">Entrevecinos</a>.
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="../resources/util/lte4/dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
   
    <!--end::OverlayScrollbars Configure-->
    
   
   <script src="js/menuPrincipal.js"></script>
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
