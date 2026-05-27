<?php
require_once __DIR__ . '/../Config/config.php';
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Entre Vecinos | Entre vecinos, todo es más fácil</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="title" content="Entre Vecinos | Entre vecinos, todo es más fácil" />

  <!-- Bootstrap JS + Popper -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="<?= BASE_URL ?>resources/util/bootstrap5/js/bootstrap.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Estilos globales y del login -->
  <?php include_once VIEW_STYLE_PATH . 'login.estilo.php'; ?>
</head>

<body class="login-body">

  <!-- Spinner -->
  <div class="spinner-overlay" id="spinnerOverlay">
    <div class="spinner"></div>
  </div>

  <!-- Contenedor principal -->
  <div class="login-shell">

    <!-- HERO IZQUIERDO -->
    <section class="login-hero">
      <div class="login-hero-content">
        <h1 class="login-hero-title">
          Bienvenido a
          <span>Entre Vecinos</span>
        </h1>

        <p class="login-hero-text">
          Compra, vende y ofrece servicios dentro de tu comunidad,
          de forma simple, cercana y segura.
        </p>

        <ul class="login-hero-list" aria-label="Beneficios de Entre Vecinos">
          <li>
            <i class="bi bi-building-check" aria-hidden="true"></i>
            <strong>Solo vecinos verificados de tu comunidad</strong>
          </li>

          <li>
            <i class="bi bi-shield-check" aria-hidden="true"></i>
            <strong>Compra con más confianza entre vecinos</strong>
          </li>

          <li>
            <i class="bi bi-bag-check" aria-hidden="true"></i>
            <strong>Publica tus productos o servicios en pocos pasos</strong>
          </li>

          <li>
            <i class="bi bi-truck" aria-hidden="true"></i>
            <strong>Coordina entregas y servicios en tu comunidad</strong>
          </li>

          <li>
            <i class="bi bi-people" aria-hidden="true"></i>
            <strong>Conecta con vecinos que viven cerca de ti</strong>
          </li>
        </ul>

        <div class="login-hero-badge">
          <span class="badge-pill" role="note" tabindex="0" aria-label="Lema de Entre Vecinos">
            <i class="bi bi-heart-fill" aria-hidden="true"></i>
            Entre vecinos, todo es más fácil
          </span>
        </div>
      </div>
    </section>

    <!-- PANEL DERECHO LOGIN -->
    <section class="login-panel">
      <header class="login-panel-header text-center">
        <div class="login-brand-mark">
          <img
            src="<?= BASE_URL ?>resources/images/logo/logo_ev_transparente_corregido_recortado.png"
            alt="Entre Vecinos"
            class="login-logo">
        </div>

        <h2 class="login-panel-title">Inicia sesión</h2>

        <p class="login-panel-subtitle">
          Ingresa para comprar, vender y conectar con vecinos de tu comunidad.
        </p>
      </header>

      <div class="login-panel-body">
        <form id="formLogin" class="login-form">
          <div class="mb-3 position-relative">
            <i class="bi bi-envelope-fill input-icon" aria-hidden="true"></i>
            <input
              id="email"
              name="email"
              type="email"
              class="form-control"
              placeholder="Correo electrónico"
              autocomplete="email"
              required />
          </div>

          <div class="mb-3 position-relative">
            <i class="bi bi-lock-fill input-icon" aria-hidden="true"></i>
            <input
              id="clave"
              name="clave"
              type="password"
              class="form-control"
              placeholder="Contraseña"
              autocomplete="current-password"
              required />
          </div>

          <div class="d-flex justify-content-between align-items-center login-remember-row">
            <div class="form-check mb-0">
              <input class="form-check-input" type="checkbox" id="recordarme">
              <label class="form-check-label" for="recordarme">Recordarme</label>
            </div>

            <a href="#" data-bs-toggle="modal" data-bs-target="#recuperar_cuenta" class="login-link-forgot">
              ¿Olvidaste tu contraseña?
            </a>
          </div>

          <div class="d-grid mb-2">
            <button type="submit" class="btn btn-login btn-lg fw-semibold">
              <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>
              Iniciar sesión
            </button>
          </div>
        </form>

        <div class="text-center mt-3 login-actions">
          <p class="login-actions-text mb-2">¿Aún no tienes cuenta?</p>

          <button type="button" class="btn btn-outline-register" data-bs-toggle="modal" data-bs-target="#crear_usuario">
            Crear una cuenta nueva
          </button>
        </div>
      </div>

      <footer class="login-panel-footer text-center mt-2">
        <small>
          &copy; <?= date('Y'); ?> <strong>Entre Vecinos</strong>. Todos los derechos reservados.
        </small>
      </footer>
    </section>
  </div>

  <!-- MODAL RECUPERAR CUENTA -->
  <div class="modal fade" id="recuperar_cuenta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow border-0 rounded-3">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="bi bi-life-preserver me-2" aria-hidden="true"></i>
            Recuperar cuenta
          </h5>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body text-center p-4">
          <p class="mb-3 text-muted">
            Si no puedes ingresar a tu cuenta, comunícate con soporte. Te ayudaremos a recuperar tu acceso.
          </p>

          <div class="p-3 border rounded bg-light">
            <p class="fw-bold mb-1 text-dark">Soporte Entre Vecinos</p>
            <p class="mb-1">Atención de Lunes a Viernes: <strong>8:00 AM – 8:00 PM</strong></p>

            <p class="fs-5 text-success mb-0">
              <i class="bi bi-whatsapp me-1" aria-hidden="true"></i>
              956 969 182
            </p>

            <p class="fs-5 text-success mb-0">
              <i class="bi bi-telephone-fill me-1" aria-hidden="true"></i>
              956 969 182
            </p>
          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1" aria-hidden="true"></i>
            Cerrar
          </button>

          <a href="tel:+51956969182" class="btn btn-login btn-modal-cta fw-semibold">
            <i class="bi bi-telephone me-1" aria-hidden="true"></i>
            Llamar ahora
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL CREAR USUARIO -->
  <div class="modal fade" id="crear_usuario" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow-lg border-0 rounded-3">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="bi bi-person-plus me-2" aria-hidden="true"></i>
            Crear mi usuario
          </h5>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <form id="formCrearUsuario">
          <div class="modal-body">

            <div class="progress mb-4">
              <div class="progress-bar bg-success fw-bold" id="step1" style="width: 33%;">1. DATOS PERSONALES</div>
              <div class="progress-bar bg-secondary fw-bold" id="step2" style="width: 33%;">2. RESIDENCIA</div>
              <div class="progress-bar bg-secondary fw-bold" id="step3" style="width: 34%;">3. CUENTA</div>
            </div>

            <!-- Paso 1 -->
            <div class="step" id="formStep1">
              <h6 class="fw-bold text-success mb-3">
                <i class="bi bi-person-circle" aria-hidden="true"></i>
                Datos personales
              </h6>

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
              <h6 class="fw-bold text-success mb-3">
                <i class="bi bi-building" aria-hidden="true"></i>
                Residencia
              </h6>

              <div class="row g-3 mb-4">

                <!-- UBIGEO -->
                <div class="col-md-4">
                  <label for="comboDepartamento" class="form-label">Departamento</label>
                  <select class="form-select" id="comboDepartamento" name="comboDepartamento" required>
                    <option value="">Cargando...</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="comboProvincia" class="form-label">Provincia</label>
                  <select class="form-select" id="comboProvincia" name="comboProvincia" required disabled>
                    <option value="">Selecciona provincia</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="comboDistrito" class="form-label">Distrito</label>
                  <select class="form-select" id="comboDistrito" name="comboDistrito" required disabled>
                    <option value="">Selecciona distrito</option>
                  </select>
                </div>

                <!-- Conjunto Residencial -->
                <div class="col-md-6">
                  <label for="comboConjuntoResidencial" class="form-label">Conjunto residencial</label>
                  <select class="form-select" id="comboConjuntoResidencial" name="comboConjuntoResidencial" required>
                    <option value="">Selecciona una opción</option>
                    <option value="condominio">Condominio</option>
                    <option value="urbanizacion">Urbanización</option>
                  </select>
                  <div class="form-text">Primero selecciona el distrito, luego el tipo de conjunto.</div>
                </div>

                <div class="col-md-6 d-none" id="wrapCondominio">
                  <label for="comboCondominio" class="form-label">Condominio</label>
                  <select class="form-select" id="comboCondominio" name="comboCondominio" disabled>
                    <option value="">Selecciona condominio</option>
                  </select>
                </div>

                <div class="col-md-6 d-none" id="wrapUrbanizacion">
                  <label for="comboUrbanizacion" class="form-label">Urbanización</label>
                  <select class="form-select" id="comboUrbanizacion" name="comboUrbanizacion" disabled>
                    <option value="">Selecciona urbanización</option>
                  </select>
                </div>

                <!-- Dirección + Comprobante -->
                <div class="col-12 d-none" id="wrapDireccion">
                  <label for="direccion" class="form-label">Dirección</label>
                  <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección automática" disabled>

                  <div class="form-text mb-3">
                    La dirección se completa automáticamente según tu selección.
                  </div>

                  <label for="comprobante_domicilio" class="form-label">
                    Comprobante de domicilio (recibo de servicio)
                  </label>

                  <input
                    type="file"
                    class="form-control"
                    id="comprobante_domicilio"
                    name="comprobante_domicilio"
                    accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">

                  <div class="form-text">
                    Formatos: JPG, PNG o PDF. Tamaño máximo: 2 MB.
                  </div>
                </div>

              </div>
            </div>

            <!-- Paso 3 -->
            <div class="step d-none" id="formStep3">
              <h6 class="fw-bold text-success mb-3">
                <i class="bi bi-key-fill" aria-hidden="true"></i>
                Datos de la cuenta
              </h6>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label for="rEmail" class="form-label">Correo electrónico</label>
                  <input type="email" class="form-control" id="rEmail" name="rEmail" required>
                </div>

                <div class="col-md-6">
                  <label for="rClave" class="form-label">Contraseña</label>
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
              <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
              Anterior
            </button>

            <button type="button" class="btn btn-success" id="btnSiguiente">
              Siguiente
              <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
            </button>

            <button type="submit" class="btn btn-success d-none" id="btnRegistrar">
              <i class="bi bi-check-circle me-1" aria-hidden="true"></i>
              Registrar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts de la vista -->
  <script>
    window.BASE_URL = '<?= BASE_URL ?>';
  </script>

  <script src="<?= BASE_URL ?>views/js/vistaRegistrarUser.js"></script>
  <script src="<?= BASE_URL ?>views/js/combo_ubigeo.js"></script>
  <script src="<?= BASE_URL ?>views/js/combo_conjunto_residencial.js"></script>
  <script src="<?= BASE_URL ?>views/js/registrarUser.js"></script>
  <script src="<?= BASE_URL ?>views/js/iniciarSesion.js"></script>

  <script>
    window.addEventListener("pageshow", function (event) {
      if (event.persisted) window.location.reload();
    });

    document.addEventListener("DOMContentLoaded", () => {
      const overlay = document.getElementById("spinnerOverlay");
      if (overlay) overlay.style.display = "none";
    });
  </script>
</body>
</html>