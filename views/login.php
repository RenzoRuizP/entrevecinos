<?php
require_once __DIR__ . '/../Config/config.php'; // cargamos BASE_URL

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
  <title>Entre Vecinos | Si lo tengo, vecina</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="title" content="Entre vecinos | Si lo tengo, vecina" />

  <!-- Bootstrap JS + Popper -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="<?= BASE_URL ?>resources/util/bootstrap5/js/bootstrap.min.js"></script>
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- 🔹 Estilos globales y del login -->
  <?php //include_once VIEW_PATH . 'estilos.view.php'; ?>
  <?php include_once VIEW_STYLE_PATH . 'login.estilo.php'; ?>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">

  <!-- 🔹 Spinner -->
  <div class="spinner-overlay" id="spinnerOverlay">
    <div class="spinner"></div>
  </div>

  <!-- 🔹 Tarjeta principal -->
  <div class="card login-card shadow-lg border-0 animate__animated animate__fadeIn col-11 col-sm-8 col-md-6 col-lg-3">
    <div class="card-header text-center bg-transparent border-0">
      <img src="<?= BASE_URL ?>resources/images/logo/logo8.png" alt="Logo" class="img-fluid mb-2" style="max-height: 300px;">
      <h4 class="fw-bold text-success mb-0">Entre Vecinos</h4>
      <small class="text-muted">Conectando tu comunidad</small>
    </div>

    <div class="card-body p-4">
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
          <button type="submit" class="btn btn-login btn-lg fw-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
          </button>
        </div>
      </form>

      <div class="text-center mt-3">
        <a href="#" data-bs-toggle="modal" data-bs-target="#recuperar_cuenta" class="d-block mb-2 text-decoration-none text-success">
          ¿Olvidaste tu contraseña?
        </a>
        <a href="#" data-bs-toggle="modal" data-bs-target="#crear_usuario" class="text-decoration-none text-success fw-bold">
          Crea una cuenta nueva
        </a>
      </div>
    </div>

    <div class="card-footer text-center login-footer bg-transparent border-0">
      <small class="text-muted">
        &copy; <?= date('Y'); ?> <strong>Entre Vecinos</strong>. Todos los derechos reservados.
      </small>
    </div>
  </div>

  <!-- 🔹 Modal recuperar cuenta -->
  <div class="modal fade" id="recuperar_cuenta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow border-0 rounded-3">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="bi bi-life-preserver me-2"></i> Recuperar cuenta</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center p-4">
          <p class="mb-3 text-muted">Si tienes problemas para acceder a tu cuenta, contáctanos:</p>
          <div class="p-3 border rounded bg-light">
            <p class="fw-bold mb-1 text-dark">Soporte técnico</p>
            <p class="mb-1">Lunes a Viernes: <strong>8:00 AM – 8:00 PM</strong></p>
            <p class="fs-5 text-success mb-0"><i class="bi bi-whatsapp me-1"></i> 956 969 182</p>
            <p class="fs-5 text-success mb-0"><i class="bi bi-telephone-fill me-1"></i> 956 969 182</p>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cerrar
          </button>
          <a href="tel:+51956969182" class="btn btn-login btn-xs fw-semibold">
            <i class="bi bi-telephone me-1"></i> Llamar ahora
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- 🔹 Modal crear usuario -->
  <div class="modal fade" id="crear_usuario" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow-lg border-0 rounded-3">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i> Crear mi usuario</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form id="formCrearUsuario">
          <div class="modal-body">
            <div class="progress mb-4" style="height: 28px;">
              <div class="progress-bar bg-success fw-bold" id="step1" style="width: 33%;">1. Datos personales</div>
              <div class="progress-bar bg-secondary fw-bold" id="step2" style="width: 33%;">2. Residencia</div>
              <div class="progress-bar bg-secondary fw-bold" id="step3" style="width: 34%;">3. Cuenta</div>
            </div>

            <!-- Paso 1 -->
            <div class="step" id="formStep1">
              <h6 class="fw-bold text-success mb-3">👤 Datos personales</h6>
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

            <!-- Paso 2 -->
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

            <!-- Paso 3 -->
            <div class="step d-none" id="formStep3">
              <h6 class="fw-bold text-success mb-3">🔑 Datos de la cuenta</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label for="email" class="form-label">Correo electrónico</label>
                  <input type="email" class="form-control" id="rEmail" name="rEmail" required>
                </div>
                <div class="col-md-6">
                  <label for="clave" class="form-label">Contraseña</label>
                  <input type="password" class="form-control" id="rClave" name="rClave" required>
                  <div class="form-text">Mínimo 8 caracteres, con mayúscula, número y símbolo.</div>
                </div>
                <div class="col-md-6">
                  <label for="confirmar_clave" class="form-label">Confirmar contraseña</label>
                  <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" required>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-outline-secondary" id="btnAnterior" disabled>
              <i class="bi bi-arrow-left me-1"></i> Anterior
            </button>
            <button type="button" class="btn btn-success" id="btnSiguiente">
              Siguiente <i class="bi bi-arrow-right ms-1"></i>
            </button>
            <button type="submit" class="btn btn-success d-none" id="btnRegistrar">
              <i class="bi bi-check-circle me-1"></i> Registrar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
  <script src="<?= BASE_URL ?>views/js/vistaRegistrarUser.js"></script>
  <script src="<?= BASE_URL ?>views/js/combo_condominio.js"></script>
  <script src="<?= BASE_URL ?>views/js/registrarUser.js"></script>
  <script src="<?= BASE_URL ?>views/js/iniciarSesion.js"></script>

  <script>
    // ============================================================
    // 🧩 Evitar spinner o pantalla congelada al retroceder (cache del navegador)
    // ============================================================
    window.addEventListener("pageshow", function (event) {
      // Si el navegador muestra la página desde su caché
      if (event.persisted) {
        // 🔹 Forzar recarga limpia del login
        window.location.reload();
      }
    });

    // ============================================================
    // 🧩 Ocultar spinner por seguridad al cargar el login
    // ============================================================
    document.addEventListener("DOMContentLoaded", () => {
      const spinner = document.querySelector(".spinner-border") || document.getElementById("spinner");
      if (spinner) spinner.style.display = "none";
    });
    </script>
</body>
</html>
