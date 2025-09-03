<?php
$mensaje = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'sin_token':
            $mensaje = 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.';
            break;
        case 'token_expirado':
            $mensaje = 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.';
            break;
        case 'token_error':
            $mensaje = 'Hubo un problema con tu sesión. Intenta nuevamente.';
            break;
    }
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Entre vecinos | Si lo tengo, vecina</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="title" content="Entre vecinos | Si lo tengo, vecina" />

  <!-- Bootstrap JS + Popper -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="../resources/util/bootstrap5/js/bootstrap.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <?php include_once 'estilos.view.php'; ?>

  <style>
    body {
      background: linear-gradient(135deg, #4CAF50, #2E7D32);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-card {
      border: none;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .login-card .card-header {
      background: #4CAF50;
      color: #fff;
      text-align: center;
      padding: 1.5rem;
    }
    .login-card .form-control {
      border-radius: 0.75rem;
      padding-left: 2.5rem;
    }
    .login-card .input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }
    .login-card button {
      border-radius: 0.75rem;
    }
    .login-footer {
      font-size: 0.9rem;
      color: #6c757d;
    }
  </style>
</head>
<body>

  <div class="card login-card col-11 col-sm-8 col-md-6 col-lg-4">
    <div class="card-header">
      <img src="../resources/images/logo/logo2.png" alt="Logo" class="img-fluid mb-0" style="max-height: 250px;" />
      <h5 class="mb-0">Bienvenido a Entre Vecinos</h5>
      <small>Inicia sesión para continuar</small>
    </div>
    <div class="card-body p-4">
      <form action="../controllers/loginController.php" method="post" class="text-start">
        <div class="mb-3 position-relative">
          <i class="bi bi-envelope-fill input-icon"></i>
          <input id="loginEmail" name="loginEmail" type="email" class="form-control" placeholder="Correo electrónico" required />
        </div>
        <div class="mb-3 position-relative">
          <i class="bi bi-lock-fill input-icon"></i>
          <input id="loginPassword" name="loginPassword" type="password" class="form-control" placeholder="Contraseña" required />
        </div>
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-success btn-lg">
            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
          </button>
        </div>
      </form>

      <div class="text-center">
        <a href="#" data-bs-toggle="modal" data-bs-target="#recuperar_cuenta" class="d-block mb-2 text-decoration-none">
          ¿Olvidaste tu contraseña?
        </a>
        <a href="#" data-bs-toggle="modal" data-bs-target="#crear_usuario" class="text-decoration-none">
          <strong>Crea una cuenta nueva</strong>
        </a>
      </div>
    </div>
    <div class="card-footer text-center login-footer">
      &copy; <?php echo date('Y'); ?> Entre Vecinos. Todos los derechos reservados.
    </div>
  </div>

  <!-- Modal recuperar cuenta -->
  <div class="modal fade" id="recuperar_cuenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg border-0 rounded-3">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="bi bi-life-preserver me-2"></i> Recuperar cuenta
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body text-center p-4">
          <p class="mb-3 fs-6 text-muted">
            Si tienes problemas para acceder a tu cuenta, contáctanos:
          </p>
          <div class="p-3 border rounded bg-light">
            <p class="fw-bold mb-1 text-dark">Soporte técnico</p>
            <p class="mb-1">Lunes a Viernes: <strong>8:00 AM – 8:00 PM</strong></p>
            <p class="fs-5 text-success mb-0">
              <i class="bi bi-whatsapp me-1"></i> 956 969 182
            </p>
            <p class="fs-5 text-success mb-0">
              <i class="bi bi-telephone-fill me-1"></i> 956 969 182
            </p>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cerrar
          </button>
          <a href="tel:+51956969182" class="btn btn-success">
            <i class="bi bi-telephone me-1"></i> Llamar ahora
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal crear usuario -->
  <div class="modal fade" id="crear_usuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg border-0 rounded-3">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="bi bi-life-preserver me-2"></i> Crear mi usuario
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body text-center p-4">
          <p class="mb-3 fs-6 text-muted">
            Si tienes problemas para acceder a tu cuenta, contáctanos:
          </p>
          <div class="p-3 border rounded bg-light">
            <p class="fw-bold mb-1 text-dark">Soporte técnico</p>
            <p class="mb-1">Lunes a Viernes: <strong>8:00 AM – 8:00 PM</strong></p>
            <p class="fs-5 text-success mb-0">
              <i class="bi bi-whatsapp me-1"></i> 956 969 182
            </p>
            <p class="fs-5 text-success mb-0">
              <i class="bi bi-telephone-fill me-1"></i> 956 969 182
            </p>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cerrar
          </button>
          <a href="tel:+51956969182" class="btn btn-success">
            <i class="bi bi-telephone me-1"></i> Registrar
          </a>
        </div>
      </div>
    </div>
  </div>
  <script src="js/login-v2.js"></script>
</body>
</html>
