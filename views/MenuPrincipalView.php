<?php
require_once __DIR__ . '/../models/SesionJWT.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$token = $_COOKIE['auth_token'] ?? null;

if (!$token) {
    header("Location: login-v2.php");
    exit;
}

try {
    $usuario = SesionJWT::verificarToken($token);

    if (!$usuario) {
        header("Location: login-v2.php?error=token_expirado");
        exit;
    }

    $nombreUsuario = $usuario->nombre ?? 'Usuario';

} catch (Exception $e) {
    header("Location: login-v2.php?error=token_error");
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Entre Vecinos | Inicio</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#22c55e">
  <link rel="icon" type="image/png" href="../resources/images/logo/logo.png">
  <?php include_once 'estilos.view.php'; ?>

  <!-- Estilos UX/UI personalizados -->
  <style>
    .small-box {
      border-radius: 0.75rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s ease-in-out, box-shadow 0.2s;
      min-height: 200px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 1rem;
    }

    .small-box:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }

    .small-box-icon {
      width: 48px;
      height: 48px;
      margin-top: 1rem;
      color: #ffffff80;
    }

    .small-box-footer {
      font-weight: 500;
      font-size: 0.95rem;
      padding-top: 0.5rem;
      display: inline-block;
      transition: opacity 0.2s ease-in-out;
    }

    .small-box-footer:hover {
      opacity: 0.85;
    }

    .callout-primary {
      background-color: #22c55e1a;
      border-left: 5px solid #22c55e;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1rem;
    }

    .callout-primary h5 {
      color: #198754;
      font-weight: 600;
      margin: 0;
    }

    .breadcrumb a {
      color: #198754;
      text-decoration: none;
    }

    .breadcrumb a:hover {
      text-decoration: underline;
    }

    @media (max-width: 767.98px) {
      .small-box {
        margin-bottom: 1rem;
      }
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">
    <?php include_once 'menuArribaView.php'; ?>
    <?php include_once 'menuIzquierdaView.php';?>

    <main class="app-main">
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <h3 class="mb-0">Hola <?php echo htmlspecialchars($nombreUsuario); ?></h3>
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
                        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
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
                        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
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
        </div>
      </div> <!-- /.app-content -->
    </main>

    <footer class="app-footer">
      <div class="float-end d-none d-sm-inline">Anything you want</div>
      <strong>Copyright &copy; 2025-2030 <a href="#" class="text-decoration-none">Entrevecinos</a>.</strong>
      All rights reserved.
    </footer>
  </div>

  <!-- Scripts necesarios -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="../resources/util/lte4/dist/js/adminlte.js"></script>
  <script src="js/menuPrincipal.js"></script>
</body>
</html>
