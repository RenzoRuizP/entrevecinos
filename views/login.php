<?php
require_once __DIR__ . '/../Config/config.php';

$evLegalConfig = require __DIR__ . '/../Config/documentos_legales.php';
$evTerminosConfig = $evLegalConfig['documentos']['terminos_condiciones'] ?? [];
$evPrivacidadConfig = $evLegalConfig['documentos']['politica_privacidad'] ?? [];
$evResponsableLegal = $evLegalConfig['responsable'] ?? [];
$evOperacionLegal = $evLegalConfig['operacion'] ?? [];
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
        <div class="login-legal-links" aria-label="Documentos legales">
          <a href="<?= BASE_URL ?>legal/terminos-y-condiciones" target="_blank" rel="noopener">Términos y Condiciones</a>
          <span aria-hidden="true">·</span>
          <a href="<?= BASE_URL ?>legal/politica-de-privacidad" target="_blank" rel="noopener">Política de Privacidad</a>
        </div>
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
      <div class="modal-content shadow-lg border-0 ev-register-modal">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="bi bi-person-plus me-2" aria-hidden="true"></i>
            Crear mi usuario
          </h5>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <form id="formCrearUsuario" novalidate>
          <div class="modal-body">

            <div class="progress mb-4" aria-label="Progreso del registro">
              <div class="progress-bar bg-success fw-bold" id="step1" style="width: 25%;">1. DATOS</div>
              <div class="progress-bar bg-secondary fw-bold" id="step2" style="width: 25%;">2. RESIDENCIA</div>
              <div class="progress-bar bg-secondary fw-bold" id="step3" style="width: 25%;">3. CUENTA</div>
              <div class="progress-bar bg-secondary fw-bold" id="step4" style="width: 25%;">4. LEGAL</div>
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
                  <input
                    type="text"
                    class="form-control"
                    id="nombre"
                    name="nombre"
                    maxlength="120"
                    autocomplete="name"
                    required>
                  <div class="invalid-feedback">Ingresa tu nombre completo.</div>
                </div>

                <div class="col-md-6">
                  <label for="documento" class="form-label">Documento de identidad</label>
                  <input
                    type="text"
                    class="form-control"
                    id="documento"
                    name="documento"
                    minlength="6"
                    maxlength="20"
                    autocomplete="off"
                    autocapitalize="characters"
                    spellcheck="false"
                    aria-describedby="documentoAyuda documentoError"
                    required>
                  <div class="form-text" id="documentoAyuda">
                    DNI, carné de extranjería, pasaporte u otro documento. Usa solo letras y números.
                  </div>
                  <div class="invalid-feedback" id="documentoError">
                    Ingresa un documento válido de 6 a 20 caracteres usando solo letras y números.
                  </div>
                </div>

                <div class="col-md-6">
                  <label for="telefono" class="form-label">Número de celular</label>
                  <input
                    type="tel"
                    class="form-control"
                    id="telefono"
                    name="telefono"
                    inputmode="numeric"
                    pattern="9[0-9]{8}"
                    minlength="9"
                    maxlength="9"
                    autocomplete="tel"
                    placeholder="Ej. 987654321"
                    aria-describedby="telefonoAyuda telefonoError"
                    required>
                  <div class="form-text" id="telefonoAyuda">
                    Debe tener 9 dígitos y comenzar con 9.
                  </div>
                  <div class="invalid-feedback" id="telefonoError">
                    Ingresa un celular peruano válido de 9 dígitos que comience con 9.
                  </div>
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
                  <div class="invalid-feedback">Selecciona un departamento.</div>
                </div>

                <div class="col-md-4">
                  <label for="comboProvincia" class="form-label">Provincia</label>
                  <select class="form-select" id="comboProvincia" name="comboProvincia" required disabled>
                    <option value="">Selecciona provincia</option>
                  </select>
                  <div class="invalid-feedback">Selecciona una provincia.</div>
                </div>

                <div class="col-md-4">
                  <label for="comboDistrito" class="form-label">Distrito</label>
                  <select class="form-select" id="comboDistrito" name="comboDistrito" required disabled>
                    <option value="">Selecciona distrito</option>
                  </select>
                  <div class="invalid-feedback">Selecciona un distrito.</div>
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
                  <div class="invalid-feedback">Selecciona el tipo de conjunto residencial.</div>
                </div>

                <div class="col-md-6 d-none" id="wrapCondominio">
                  <label for="comboCondominio" class="form-label">Condominio</label>
                  <select class="form-select" id="comboCondominio" name="comboCondominio" disabled>
                    <option value="">Selecciona condominio</option>
                  </select>
                  <div class="invalid-feedback">Selecciona un condominio.</div>
                </div>

                <div class="col-md-6 d-none" id="wrapUrbanizacion">
                  <label for="comboUrbanizacion" class="form-label">Urbanización</label>
                  <select class="form-select" id="comboUrbanizacion" name="comboUrbanizacion" disabled>
                    <option value="">Selecciona urbanización</option>
                  </select>
                  <div class="invalid-feedback">Selecciona una urbanización.</div>
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
                    accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                    aria-describedby="comprobanteAyuda comprobanteError"
                    required>

                  <div class="form-text" id="comprobanteAyuda">
                    Formatos: JPG, PNG o PDF. Tamaño máximo: 2 MB.
                  </div>
                  <div class="invalid-feedback" id="comprobanteError">
                    Adjunta un comprobante de domicilio en formato JPG, PNG o PDF.
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
                  <input
                    type="email"
                    class="form-control"
                    id="rEmail"
                    name="rEmail"
                    inputmode="email"
                    maxlength="254"
                    autocomplete="email"
                    placeholder="nombre@correo.com"
                    aria-describedby="emailError"
                    required>
                  <div class="invalid-feedback" id="emailError">
                    Ingresa un correo electrónico válido.
                  </div>
                </div>

                <div class="col-md-6">
                  <label for="rClave" class="form-label">Contraseña</label>
                  <input
                    type="password"
                    class="form-control"
                    id="rClave"
                    name="rClave"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    aria-describedby="claveAyuda claveError"
                    required>
                  <div class="form-text" id="claveAyuda">
                    Mínimo 8 caracteres, con mayúscula, número y símbolo.
                  </div>
                  <div class="invalid-feedback" id="claveError">
                    La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un símbolo.
                  </div>
                </div>

                <div class="col-md-6">
                  <label for="confirmar_clave" class="form-label">Confirmar contraseña</label>
                  <input
                    type="password"
                    class="form-control"
                    id="confirmar_clave"
                    name="confirmar_clave"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    aria-describedby="confirmarClaveError"
                    required>
                  <div class="invalid-feedback" id="confirmarClaveError">
                    Las contraseñas no coinciden. Verifica ambos campos.
                  </div>
                </div>
              </div>
            </div>

            <!-- Paso 4 -->
            <div class="step d-none" id="formStep4">
              <div class="ev-register-legal">
                <div class="ev-register-legal__heading">
                  <div class="ev-register-legal__icon">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                  </div>
                  <div>
                    <span class="ev-register-legal__eyebrow">Último paso</span>
                    <h6 class="fw-bold text-success mb-1">Revisa y acepta</h6>
                    <p class="mb-0">Lee los documentos vigentes y confirma las dos aceptaciones para enviar tu solicitud.</p>
                  </div>
                </div>

                <section class="ev-register-legal__review" aria-labelledby="evLegalReviewTitle">
                  <div class="ev-register-legal__section-title">
                    <div>
                      <span class="ev-register-legal__section-kicker">Documentos</span>
                      <h6 id="evLegalReviewTitle">Información que debes revisar</h6>
                    </div>
                    <span class="ev-register-legal__section-badge">Versión vigente</span>
                  </div>

                  <div class="ev-register-legal__docs">
                    <a class="ev-register-legal__doc" href="<?= BASE_URL ?>legal/terminos-y-condiciones" target="_blank" rel="noopener">
                      <span class="ev-register-legal__doc-icon"><i class="bi bi-file-text"></i></span>
                      <span>
                        <strong>Términos y Condiciones de Uso</strong>
                        <small>Versión <?= htmlspecialchars((string)($evTerminosConfig['version'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?></small>
                      </span>
                      <span class="ev-register-legal__doc-action">Revisar <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                    </a>

                    <a class="ev-register-legal__doc" href="<?= BASE_URL ?>legal/politica-de-privacidad" target="_blank" rel="noopener">
                      <span class="ev-register-legal__doc-icon"><i class="bi bi-shield-lock"></i></span>
                      <span>
                        <strong>Política de Privacidad y Tratamiento de Datos Personales</strong>
                        <small>Versión <?= htmlspecialchars((string)($evPrivacidadConfig['version'] ?? '1.0'), ENT_QUOTES, 'UTF-8') ?></small>
                      </span>
                      <span class="ev-register-legal__doc-action">Revisar <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                    </a>
                  </div>

                  <details class="ev-register-legal__privacy-summary">
                    <summary>
                      <span><i class="bi bi-info-circle" aria-hidden="true"></i> Ver aviso breve de privacidad</span>
                      <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </summary>
                    <div>
                      <p><strong>Responsable:</strong> <?= htmlspecialchars((string)($evResponsableLegal['nombre_legal'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string)($evResponsableLegal['documento_tributario'] ?? ''), ENT_QUOTES, 'UTF-8') ?>.</p>
                      <p><strong>Finalidades principales:</strong> crear y proteger tu cuenta, validar que perteneces a la comunidad, permitir compras, ventas y servicios, enviar avisos operativos, brindar soporte y mantener la seguridad y trazabilidad de EV.</p>
                      <p><strong>Alojamiento:</strong> la infraestructura principal se encuentra en <?= htmlspecialchars((string)($evOperacionLegal['ubicacion_alojamiento'] ?? 'São Paulo, Brasil'), ENT_QUOTES, 'UTF-8') ?>, por lo que existe un flujo internacional de datos.</p>
                      <p><strong>Tus derechos:</strong> puedes solicitar información, acceso, rectificación, actualización, cancelación u oposición escribiendo a <a href="mailto:<?= htmlspecialchars((string)($evResponsableLegal['correo_privacidad'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($evResponsableLegal['correo_privacidad'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>.</p>
                      <p class="mb-0">La Política completa detalla los datos obligatorios y opcionales, destinatarios, conservación, seguridad y forma de ejercer tus derechos.</p>
                    </div>
                  </details>
                </section>

                <section class="ev-register-legal__consent-panel" aria-labelledby="evConsentTitle">
                  <div class="ev-register-legal__consent-heading">
                    <span class="ev-register-legal__consent-icon"><i class="bi bi-check2-square" aria-hidden="true"></i></span>
                    <div>
                      <h6 id="evConsentTitle">Tus aceptaciones</h6>
                      <p>Marca ambas casillas. Son independientes y obligatorias.</p>
                    </div>
                  </div>

                  <div class="ev-register-legal__consents">
                    <div class="ev-register-legal__check" id="wrapAceptaTerminosRegistro">
                      <input class="form-check-input" type="checkbox" id="acepta_terminos" name="acepta_terminos" value="1" required>
                      <label for="acepta_terminos">
                        <?= htmlspecialchars((string)($evTerminosConfig['texto_consentimiento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <a href="<?= BASE_URL ?>legal/terminos-y-condiciones" target="_blank" rel="noopener">Leer Términos</a>.
                      </label>
                    </div>

                    <div class="ev-register-legal__check" id="wrapAceptaPrivacidadRegistro">
                      <input class="form-check-input" type="checkbox" id="acepta_privacidad" name="acepta_privacidad" value="1" required>
                      <label for="acepta_privacidad">
                        <?= htmlspecialchars((string)($evPrivacidadConfig['texto_consentimiento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <a href="<?= BASE_URL ?>legal/politica-de-privacidad" target="_blank" rel="noopener">Leer Política</a>.
                      </label>
                    </div>
                  </div>

                  <div class="ev-register-legal__note" role="note">
                    <i class="bi bi-lock" aria-hidden="true"></i>
                    <span>El botón <strong>Registrar</strong> se habilitará cuando aceptes ambos documentos.</span>
                  </div>
                </section>
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

            <button type="submit" class="btn btn-success d-none" id="btnRegistrar" disabled>
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