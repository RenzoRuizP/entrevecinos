<?php 
require_once __DIR__ . '/../Config/config.php'; // cargamos BASE_URL
?>

<!-- Vista: DatosPersonalesView.php -->
<?php include_once __DIR__ . '/estilos/DatosPersonalesEstilo.php'; ?>

<div class="container-datos-personales fade-in">
  <div class="card shadow-lg border-0 rounded-4 ev-datos-card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <span class="ev-datos-icon">
          <i class="fas fa-user-circle"></i>
        </span>
        <div>
          <h5 class="mb-1">Datos personales</h5>
          <!--<small class="ev-datos-subtitle">
            Mantén tu información actualizada para mejorar la experiencia dentro del condominio.
          </small>-->
        </div>
      </div>
    </div>

    <div class="card-body p-4">
      <form id="formDatosPersonales" class="row g-3" autocomplete="off">

        <!-- Nombre completo -->
        <div class="col-md-6">
          <label for="nombre_completo" class="form-label ev-form-label">Nombre completo</label>
          <div class="position-relative">
            <input 
              type="text" 
              id="nombre_completo" 
              class="form-control ev-input-rounded" 
              value="<?= htmlspecialchars($datosUsuario['nombre_completo'] ?? '') ?>"
            >
          </div>
        </div>

        <!-- Email -->
        <div class="col-md-6">
          <label for="email" class="form-label ev-form-label">Correo electrónico</label>
          <div class="position-relative">
            <input 
              type="email" 
              id="email" 
              class="form-control ev-input-rounded" 
              value="<?= htmlspecialchars($datosUsuario['email'] ?? '') ?>" 
              disabled
            >
          </div>
          <div class="ev-form-help mt-1">
            Este correo está asociado a tu cuenta y no puede modificarse.
          </div>
        </div>

        <!-- Documento -->
        <div class="col-md-6">
          <label for="documento" class="form-label ev-form-label">Documento de identidad</label>
          <div class="position-relative">
            <input 
              type="text" 
              id="documento" 
              class="form-control ev-input-rounded" 
              value="<?= htmlspecialchars($datosUsuario['documento'] ?? '') ?>"
            >
          </div>
        </div>

        <!-- Teléfono -->
        <div class="col-md-6">
          <label for="telefono" class="form-label ev-form-label">Teléfono</label>
          <div class="position-relative">
            <input 
              type="text" 
              id="telefono" 
              class="form-control ev-input-rounded" 
              value="<?= htmlspecialchars($datosUsuario['telefono'] ?? '') ?>"
            >
          </div>
          <div class="ev-form-help mt-1">
            Tus vecinos solo verán este número cuando concreten una compra o servicio.
          </div>
        </div>

        <!-- Condominio -->
        <div class="col-md-4">
          <label for="comboCondominio" class="form-label ev-form-label">Condominio</label>
          <select 
            id="comboCondominio" 
            name="comboCondominio" 
            class="form-select ev-input-rounded"
            data-valor-registrado="<?= htmlspecialchars($datosUsuario['codigo_condominio'] ?? '') ?>"
          >
            <option value="">-- Seleccione condominio --</option>
          </select>
        </div>

        <!-- Torre -->
        <div class="col-md-4">
          <label for="comboTorre" class="form-label ev-form-label">Torre</label>
          <select 
            id="comboTorre" 
            class="form-select ev-input-rounded"
            data-valor-registrado="<?= htmlspecialchars($datosUsuario['codigo_torre'] ?? '') ?>"
          >
            <option value="">-- Seleccione torre --</option>
          </select>
        </div>

        <!-- Departamento -->
        <div class="col-md-4">
          <label for="comboDepartamento" class="form-label ev-form-label">Departamento</label>
          <select 
            id="comboDepartamento" 
            class="form-select ev-input-rounded"
            data-valor-registrado="<?= htmlspecialchars($datosUsuario['codigo_departamento'] ?? '') ?>"
          >
            <option value="">-- Seleccione departamento --</option>
          </select>
        </div>

        <!-- Línea divisoria suave -->
        <div class="col-12">
          <hr class="ev-datos-divider">
        </div>

        <!-- Botones de acción -->
        <div class="col-12 text-end mt-2 d-flex flex-wrap gap-2 justify-content-end">
          <button type="button" id="btnGuardar" class="btn btn-ev-primary btn-guardar">
            <i class="fas fa-save me-1"></i> Guardar cambios
          </button>

          <button type="button" id="btnCancelar" class="btn btn-ev-neutral btn-cancelar" style="display:none;">
            <i class="fas fa-times me-1"></i> Cancelar
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- JS específico -->
<script>window.BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>js/combo_condominio.js"></script>
