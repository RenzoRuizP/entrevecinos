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
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Entre vecinos | Si lo tengo, vecina</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="title" content="Entre vecinos | Si lo tengo, vecina" />
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php include_once 'estilos.view.php'; ?>
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <img src="../resources/images/logo/logo2.png" alt="Logo" class="img-fluid mb-0" />

            <form action="../controllers/loginController.php" method="post">
              <div class="form-floating mb-3">
                <input id="loginEmail" name="loginEmail" type="email" class="form-control" placeholder="Correo electrónico" required />
                <label for="loginEmail">Correo electrónico</label>
              </div>
              <div class="form-floating mb-3">
                <input id="loginPassword" name="loginPassword" type="password" class="form-control" placeholder="Contraseña" required />
                <label for="loginPassword">Contraseña</label>
              </div>
              <div class="d-grid mb-2">
                <button type="submit" class="btn btn-success">Iniciar sesión</button>
              </div>
            </form>
            <footer class="text-center mt-4 mb-2">
              <small class="text-muted">
                &copy; <?php echo date('Y'); ?> Entre Vecinos. Todos los derechos reservados.
              </small>
            </footer>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="js/login-v2.js"></script>
  
</body>
</html>

