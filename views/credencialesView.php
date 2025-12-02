<?php
// views/CredencialesView.php
require_once __DIR__ . '/../Config/config.php';

/** @var array $datosUsuario viene desde credencialController */
$correoUsuario = htmlspecialchars($datosUsuario['email'] ?? '', ENT_QUOTES, 'UTF-8');
$nombreUsuario = htmlspecialchars(
    $datosUsuario['nombre_completo'] ?? $datosUsuario['nombre'] ?? 'Vecino',
    ENT_QUOTES,
    'UTF-8'
);
?>
<script>
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/credencialesEstilo.php'; ?>

<div class="ev-credenciales-wrapper fade-in">
  <!-- Encabezado -->
  <div class="ev-credenciales-header mb-3">
    <h2>Credenciales y seguridad</h2>
    <p>Gestiona la contraseña de tu cuenta Entre Vecinos y mantén tu acceso seguro.</p>
  </div>

  <!-- Mensaje informativo -->
  <div class="alert alert-info ev-credenciales-alert d-flex align-items-start gap-2">
    <i class="bi bi-shield-check fs-5 me-1"></i>
    <div>
      <strong>Consejo de seguridad:</strong>
      Entre Vecinos nunca te solicitará tu contraseña por WhatsApp, correo o llamadas.
    </div>
  </div>

  <!-- Card principal -->
  <div class="card ev-credenciales-card">
    <div class="card-header">
      <div class="ev-credenciales-icon">
        <i class="bi bi-shield-lock-fill fs-5"></i>
      </div>
      <div class="ms-2">
        <h5 class="mb-1">Cambiar contraseña</h5>
        <small>Actualiza la contraseña de acceso asociada a tu cuenta.</small>
      </div>
    </div>

    <div class="card-body">
      <!-- Información del usuario -->
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2">
        <div>
          <div class="fw-semibold" style="font-size:0.92rem; color:#374151;">
            <?= $nombreUsuario ?>
          </div>
          <div class="text-muted" style="font-size:0.86rem;">
            Correo de acceso: <strong><?= $correoUsuario ?></strong>
          </div>
        </div>
      </div>

      <div class="ev-credenciales-divider"></div>

      <!-- Formulario de cambio de contraseña -->
      <form id="formCambiarContrasena" autocomplete="off">
        <div class="row g-3">
          <div class="col-12 col-md-4">
            <label class="ev-credenciales-form-label mb-1">Contraseña actual</label>
            <div class="position-relative">
              <i class="bi bi-lock ev-input-icon"></i>
              <input type="password"
                     class="form-control ev-input-rounded"
                     name="password_actual"
                     id="password_actual"
                     placeholder="••••••••">
            </div>
          </div>

          <div class="col-12 col-md-4">
            <label class="ev-credenciales-form-label mb-1">Nueva contraseña</label>
            <div class="position-relative mb-1">
              <i class="bi bi-shield-lock ev-input-icon"></i>
              <input type="password"
                     class="form-control ev-input-rounded"
                     name="password_nueva"
                     id="password_nueva"
                     placeholder="Mínimo 8 caracteres">
            </div>
            <div class="ev-password-strength mb-1">
              <div class="ev-password-strength-bar" id="password_strength_bar"></div>
            </div>
            <div class="ev-credenciales-form-text">
              Usa al menos 8 caracteres, incluyendo una mayúscula y un número.
            </div>
          </div>

          <div class="col-12 col-md-4">
            <label class="ev-credenciales-form-label mb-1">Confirmar nueva contraseña</label>
            <div class="position-relative">
              <i class="bi bi-lock-fill ev-input-icon"></i>
              <input type="password"
                     class="form-control ev-input-rounded"
                     name="password_confirmar"
                     id="password_confirmar"
                     placeholder="Repite la nueva contraseña">
            </div>
          </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-end gap-2 gap-md-3 mt-4">
          <button type="button" class="btn btn-ev-neutral">Cancelar</button>
          <button type="button" class="btn btn-ev-primary">Guardar contraseña</button>
        </div>
      </form>
    </div>
  </div>
</div>
