<?php
require_once __DIR__ . '/../config/config.php'; // cargamos BASE_URL

$mensaje = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'sin_token':
        case 'token_expirado':
            $mensaje = 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.';
            break;
        case 'token_error':
            $mensaje = 'Hubo un problema con tu sesión. Intenta nuevamente.';
            break;
        case 'campos_vacios':
            $mensaje = 'Debes llenar todos los campos.';
            break;
        case 'CI':
            $mensaje = 'La contraseña ingresada es incorrecta.';
            break;
        case 'IN':
            $mensaje = 'Tu usuario está inactivo. Contacta con soporte.';
            break;
        case 'NE':
            $mensaje = 'El usuario no existe en el sistema.';
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
  <script src="<?= BASE_URL ?>resources/util/bootstrap5/js/bootstrap.min.js"></script>
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">



  <?php include_once VIEW_PATH . 'estilos.view.php'; ?>
  <?php include_once VIEW_STYLE_PATH . 'login.estilo.php'; ?>

</head>
<body>
  <!-- 🔹 SPINNER DE CARGA -->
  <div class="spinner-overlay" id="spinnerOverlay">
    <div class="spinner"></div>
  </div>

  <div class="card login-card col-11 col-sm-8 col-md-6 col-lg-4">
    <div class="card-header">
      <img src="<?= BASE_URL ?>resources/images/logo/logo2.png" alt="Logo" class="img-fluid mb-0" style="max-height: 200px;" />
      <h5 class="mb-0">Bienvenido a Entre Vecinos</h5>
      <small>Inicia sesión para continuar</small>
    </div>
    <div class="card-body p-4">
      <!-- ✅ Ahora apuntas al route /login -->
      <form id="formLogin" class="text-start">
        <div class="mb-3 position-relative">
          <i class="bi bi-envelope-fill input-icon"></i>
          <input id="email" name="email" type="email" class="form-control" placeholder="Correo electrónico" required />
        </div>
        <div class="mb-3 position-relative">
          <i class="bi bi-lock-fill input-icon"></i>
          <input id="clave" name="clave" type="password" class="form-control" placeholder="Contraseña" required />
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
      &copy; <?= date('Y'); ?> Entre Vecinos. Todos los derechos reservados.
    </div>
  </div>

  <!-- Modal recuperar cuenta -->
  <div class="modal fade" id="recuperar_cuenta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
  <div class="modal fade" id="crear_usuario" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow-lg border-0 rounded-3">
        
        <!-- Header -->
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="bi bi-person-plus me-2"></i> Crear mi usuario
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <form id="formCrearUsuario">
        <!-- Body -->
          <div class="modal-body">
            <!-- Progress bar -->
            <div class="progress mb-4" style="height: 30px;">
              <div class="progress-bar bg-success fw-bold" id="step1" role="progressbar" style="width: 33%;">
                1. Datos personales
              </div>
              <div class="progress-bar bg-secondary fw-bold" id="step2" role="progressbar" style="width: 33%;">
                2. Residencia
              </div>
              <div class="progress-bar bg-secondary fw-bold" id="step3" role="progressbar" style="width: 34%;">
                3. Cuenta
              </div>
            </div>
              <!-- Paso 1: Datos personales -->
              <div class="step" id="formStep1">
                <h6 class="fw-bold text-success mb-3">👤 Datos Personales</h6>
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                  </div>
                  <div class="col-md-6">
                    <label for="documento" class="form-label">Documento de identidad</label>
                    <input type="text" class="form-control" id="documento" name="documento" required>
                  </div>
                  <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
                  </div>
                </div>
              </div>

              <!-- Paso 2: Residencia -->
              <div class="step d-none" id="formStep2">
                <h6 class="fw-bold text-success mb-3">🏠 Residencia</h6>
                <div class="row g-3 mb-4">
                  <div class="col-md-4">
                    <label for="comboCondominio" class="form-label">Condominio</label>
                    <select class="form-select" id="comboCondominio" name="comboCondominio" required>
                      <option value="">Seleccione un condominio</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label for="comboTorre" class="form-label">Torre</label>
                    <select class="form-select" id="comboTorre" name="comboTorre" required>
                      <option value="">Seleccione torre...</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label for="comboDepartamento" class="form-label">Departamento</label>
                    <select class="form-select" id="comboDepartamento" name="comboDepartamento" required>
                      <option value="">Seleccione departamento...</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Paso 3: Cuenta -->
              <div class="step d-none" id="formStep3">
                <h6 class="fw-bold text-success mb-3">🔑 Datos de la Cuenta</h6>
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                  <div class="col-md-6">
                    <label for="clave" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="clave" name="clave" required>
                    <div class="form-text">Mínimo 8 caracteres, con mayúscula, número y símbolo.</div>
                  </div>
                  <div class="col-md-6">
                    <label for="confirmar_clave" class="form-label">Confirmar contraseña</label>
                    <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" required>
                  </div>
                </div>
              </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-outline-secondary" id="btnAnterior" disabled>
              <i class="bi bi-arrow-left me-1"></i> Anterior
            </button>
            <button type="button" class="btn btn-success" id="btnSiguiente">
              Siguiente <i class="bi bi-arrow-right ms-1"></i>
            </button>
            <button type="submit" form="formCrearUsuario" class="btn btn-success d-none" id="btnRegistrar">
              <i class="bi bi-check-circle me-1"></i> Registrar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    window.BASE_URL = '<?= BASE_URL ?>';
  </script>
  <!-- Scripts de validación UX -->
  <script src="<?= BASE_URL ?>views/js/vistaRegistrarUser.js"></script>
  <script src="<?= BASE_URL ?>views/js/combo_condominio.js"></script>
  <script src="<?= BASE_URL ?>views/js/registrarUser.js"></script>
  <script src="<?= BASE_URL ?>views/js/iniciarSesion.js"></script>
</body>
</html>
