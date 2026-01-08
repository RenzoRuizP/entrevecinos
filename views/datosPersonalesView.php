<?php
require_once __DIR__ . '/../Config/config.php';
?>

<?php include_once __DIR__ . '/estilos/DatosPersonalesEstilo.php'; ?>

<?php
  $tipoConjunto        = strtolower(trim((string)($datosUsuario['tipo_conjunto'] ?? '')));
  $codigoCondominio    = (string)($datosUsuario['codigo_condominio'] ?? '');
  $codigoUrbanizacion  = (string)($datosUsuario['codigo_urbanizacion'] ?? '');
  $direccionResidencia = (string)($datosUsuario['direccion'] ?? '');
  $comprobante         = (string)($datosUsuario['comprobante_domicilio'] ?? '');

  // Ubigeo (si no lo tienes persistido, quedará vacío)
  $ub_depto = (string)($datosUsuario['ubigeo_departamento'] ?? '');
  $ub_prov  = (string)($datosUsuario['ubigeo_provincia'] ?? '');
  $ub_dist  = (string)($datosUsuario['ubigeo_distrito'] ?? '');
?>

<!-- Estado base (para comparar cambios desde JS) -->
<div
  id="dp-state"
  data-tipo="<?= htmlspecialchars($tipoConjunto, ENT_QUOTES, 'UTF-8'); ?>"
  data-codigo-condominio="<?= htmlspecialchars($codigoCondominio, ENT_QUOTES, 'UTF-8'); ?>"
  data-codigo-urbanizacion="<?= htmlspecialchars($codigoUrbanizacion, ENT_QUOTES, 'UTF-8'); ?>"
  data-direccion="<?= htmlspecialchars($direccionResidencia, ENT_QUOTES, 'UTF-8'); ?>"
  data-comprobante="<?= htmlspecialchars($comprobante, ENT_QUOTES, 'UTF-8'); ?>"
  data-ub-depto="<?= htmlspecialchars($ub_depto, ENT_QUOTES, 'UTF-8'); ?>"
  data-ub-prov="<?= htmlspecialchars($ub_prov, ENT_QUOTES, 'UTF-8'); ?>"
  data-ub-dist="<?= htmlspecialchars($ub_dist, ENT_QUOTES, 'UTF-8'); ?>"
></div>

<div class="container-datos-personales fade-in">
  <div class="card shadow-lg border-0 rounded-4 ev-datos-card">

    <!-- HEADER -->
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-3">
        <span class="ev-datos-icon">
          <i class="bi bi-person-badge-fill"></i>
        </span>
        <div>
          <h5 class="mb-1">Mi perfil</h5>
          <small class="ev-datos-subtitle">
            Actualiza tu información. Los cambios de residencia requieren verificación.
          </small>
        </div>
      </div>
    </div>

    <div class="card-body p-4">

      <!-- STEPPER -->
      <div class="ev-stepper mb-4" id="dp-stepper">
        <div class="ev-step active" data-step="1">
          <div class="ev-step-dot">1</div>
          <div class="ev-step-label">Datos personales</div>
        </div>
        <div class="ev-step-line"></div>
        <div class="ev-step" data-step="2">
          <div class="ev-step-dot">2</div>
          <div class="ev-step-label">Residencia</div>
        </div>
        <div class="ev-step-line"></div>
        <div class="ev-step" data-step="3">
          <div class="ev-step-dot">3</div>
          <div class="ev-step-label">Cuenta</div>
        </div>
      </div>

      <form id="formDatosPersonales" class="ev-wizard" autocomplete="off" enctype="multipart/form-data">

        <!-- =========================
             PASO 1: DATOS PERSONALES
        ========================== -->
        <section class="ev-step-panel" data-panel="1">

          <div class="row g-3">
            <!-- Nombre completo (disabled) -->
            <div class="col-md-6">
              <label for="nombre_completo" class="form-label ev-form-label">Nombre completo</label>
              <input
                type="text"
                id="nombre_completo"
                class="form-control ev-input-rounded"
                value="<?= htmlspecialchars($datosUsuario['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                disabled
              >
            </div>

            <!-- Documento (disabled) -->
            <div class="col-md-6">
              <label for="documento" class="form-label ev-form-label">Documento de identidad</label>
              <input
                type="text"
                id="documento"
                class="form-control ev-input-rounded"
                value="<?= htmlspecialchars($datosUsuario['documento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                disabled
              >
            </div>

            <!-- Email (disabled) -->
            <div class="col-md-6">
              <label for="email" class="form-label ev-form-label">Correo electrónico</label>
              <input
                type="email"
                id="email"
                class="form-control ev-input-rounded"
                value="<?= htmlspecialchars($datosUsuario['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                disabled
              >
            </div>

            <!-- Teléfono -->
            <div class="col-md-6">
              <label for="telefono" class="form-label ev-form-label">Teléfono</label>
              <input
                type="text"
                id="telefono"
                class="form-control ev-input-rounded"
                value="<?= htmlspecialchars($datosUsuario['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="Ej.: 9XXXXXXXX"
              >
            </div>

            <div class="col-12">
              <div class="ev-hint">
                <i class="bi bi-info-circle me-2"></i>
                Tu nombre, documento y correo no se pueden editar desde aquí.
              </div>
            </div>
          </div>
        </section>

        <!-- =========================
             PASO 2: RESIDENCIA
        ========================== -->
        <section class="ev-step-panel d-none" data-panel="2">
          <div class="row g-3">

            <div class="col-12">
              <div class="ev-hint">
                <i class="bi bi-shield-check me-2"></i>
                Si cambias tu residencia, se generará una <strong>solicitud</strong> para aprobación del administrador y deberás adjuntar un comprobante.
              </div>
            </div>

            <!-- Ubigeo -->
            <div class="col-md-4">
              <label for="dpUbDepto" class="form-label ev-form-label">Departamento (Ubigeo)</label>
              <select id="dpUbDepto" class="form-select ev-input-rounded"
                data-valor-registrado="<?= htmlspecialchars($ub_depto, ENT_QUOTES, 'UTF-8'); ?>">
                <option value="">-- Seleccione --</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="dpUbProv" class="form-label ev-form-label">Provincia</label>
              <select id="dpUbProv" class="form-select ev-input-rounded"
                data-valor-registrado="<?= htmlspecialchars($ub_prov, ENT_QUOTES, 'UTF-8'); ?>" disabled>
                <option value="">-- Seleccione --</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="dpUbDist" class="form-label ev-form-label">Distrito</label>
              <select id="dpUbDist" class="form-select ev-input-rounded"
                data-valor-registrado="<?= htmlspecialchars($ub_dist, ENT_QUOTES, 'UTF-8'); ?>" disabled>
                <option value="">-- Seleccione --</option>
              </select>
            </div>

            <!-- Tipo de conjunto -->
            <div class="col-12">
              <label class="form-label ev-form-label mb-2">Conjunto residencial</label>
              <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="dpTipoResidencia" id="dpTipoCondominio" value="condominio"
                    <?= ($tipoConjunto === 'condominio') ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="dpTipoCondominio">Condominio</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="dpTipoResidencia" id="dpTipoUrbanizacion" value="urbanizacion"
                    <?= ($tipoConjunto === 'urbanizacion') ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="dpTipoUrbanizacion">Urbanización</label>
                </div>
              </div>
            </div>

            <!-- Condominio -->
            <div id="wrapCondominio" class="row g-3">
              <div class="col-md-6">
                <label for="comboCondominio" class="form-label ev-form-label">Condominio</label>
                <select
                  id="comboCondominio"
                  name="comboCondominio"
                  class="form-select ev-input-rounded"
                  data-valor-registrado="<?= htmlspecialchars($codigoCondominio, ENT_QUOTES, 'UTF-8'); ?>"
                >
                  <option value="">-- Seleccione condominio --</option>
                </select>
              </div>
            </div>

            <!-- Urbanización -->
            <div id="wrapUrbanizacion" class="row g-3 d-none">
              <div class="col-md-6">
                <label for="comboUrbanizacion" class="form-label ev-form-label">Urbanización</label>
                <select
                  id="comboUrbanizacion"
                  name="comboUrbanizacion"
                  class="form-select ev-input-rounded"
                  data-valor-registrado="<?= htmlspecialchars($codigoUrbanizacion, ENT_QUOTES, 'UTF-8'); ?>"
                >
                  <option value="">-- Seleccione urbanización --</option>
                </select>
              </div>
            </div>

            <!-- Dirección (disabled, se autocompleta al elegir condominio/urbanización) -->
            <div class="col-12">
              <label for="direccion" class="form-label ev-form-label">Dirección</label>
              <input
                type="text"
                id="direccion"
                class="form-control ev-input-rounded"
                value="<?= htmlspecialchars($direccionResidencia, ENT_QUOTES, 'UTF-8'); ?>"
                disabled
              >
              <div class="form-text">
                La dirección se toma del conjunto residencial seleccionado.
              </div>
            </div>

            <!-- Comprobante actual (link) -->
            <div class="col-12" id="wrapComprobanteActual" <?= $comprobante ? '' : 'style="display:none;"' ?>>
              <label class="form-label ev-form-label mb-2">Comprobante actual</label>
              <div class="ev-file-row">
                <div class="ev-file-info">
                  <i class="bi bi-paperclip"></i>
                  <a
                    id="dpLinkComprobanteActual"
                    href="<?= $comprobante ? htmlspecialchars(rtrim(BASE_URL,'/').'/'.$comprobante, ENT_QUOTES, 'UTF-8') : '#'; ?>"
                    target="_blank"
                    rel="noopener"
                  >
                    Ver comprobante adjunto
                  </a>
                  <small class="text-muted ms-2" id="dpComprobantePath"><?= htmlspecialchars($comprobante, ENT_QUOTES, 'UTF-8'); ?></small>
                </div>
              </div>
            </div>

            <!-- Upload SOLO si cambia residencia -->
            <div class="col-12 d-none" id="wrapUploadDomicilio">
              <label for="dpDocDomicilio" class="form-label ev-form-label">
                Nuevo comprobante de domicilio (obligatorio si cambias residencia)
              </label>

              <input
                type="file"
                id="dpDocDomicilio"
                name="dpDocDomicilio"
                class="form-control ev-input-rounded"
                accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
              />

              <!-- Preview archivo seleccionado -->
              <div class="ev-file-row mt-2 d-none" id="wrapFileSelected">
                <div class="ev-file-info">
                  <i class="bi bi-file-earmark-text"></i>
                  <a href="#" id="dpFileSelectedName" onclick="return false;">archivo</a>
                  <small class="text-muted ms-2" id="dpFileSelectedMeta"></small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveSelectedFile" title="Quitar archivo">
                  <i class="bi bi-trash"></i>
                </button>
              </div>

              <div class="form-text">
                Tipos permitidos: PDF, JPG, PNG. Tamaño máximo: 5MB.
              </div>
            </div>

          </div>
        </section>

        <!-- =========================
             PASO 3: CUENTA
        ========================== -->
        <section class="ev-step-panel d-none" data-panel="3">
          <div class="row g-3">

            <div class="col-12">
              <div class="ev-hint">
                <i class="bi bi-lock me-2"></i>
                Para cambiar tu contraseña, ingresa tu contraseña actual y define una nueva.
              </div>
            </div>

            <div class="col-md-6">
              <label for="password_actual" class="form-label ev-form-label">Contraseña actual</label>
              <input type="password" id="password_actual" class="form-control ev-input-rounded" autocomplete="current-password">
            </div>

            <div class="col-md-6">
              <label for="password_nueva" class="form-label ev-form-label">Nueva contraseña</label>
              <input type="password" id="password_nueva" class="form-control ev-input-rounded" autocomplete="new-password">
            </div>

            <div class="col-md-6">
              <label for="password_confirmar" class="form-label ev-form-label">Confirmar nueva contraseña</label>
              <input type="password" id="password_confirmar" class="form-control ev-input-rounded" autocomplete="new-password">
            </div>

            <div class="col-12">
              <div class="form-text">
                Recomendación: mínimo 8 caracteres.
              </div>
            </div>

          </div>
        </section>

        <!-- FOOTER -->
        <div class="ev-wizard-footer mt-4">
          <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <button type="button" class="btn btn-ev-neutral" id="btnAnterior">
              <i class="bi bi-arrow-left me-1"></i> Anterior
            </button>

            <div class="d-flex gap-2">
              <button type="button" class="btn btn-ev-primary" id="btnActualizar">
                <i class="fas fa-save me-1"></i> Actualizar
              </button>
            </div>
          </div>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- Script wizard -->
<script src="<?= rtrim(BASE_URL,'/') ?>/views/js/datosPersonales.js?v=<?= time() ?>"></script>
