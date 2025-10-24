<?php 
require_once __DIR__ . '/../Config/config.php'; // cargamos BASE_URL
?>

<!-- Vista: DatosPersonalesView.php -->
<div class="container-datos-personales fade-in">
  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-header bg-success">
      <h5><i class="fas fa-user-circle me-2"></i>MIS DATOS PERSONALES</h5>
    </div>

    <div class="card-body p-4">
      <form id="formDatosPersonales" class="row g-3" autocomplete="off">

        <!-- Nombre completo -->
        <div class="col-md-6">
          <label for="nombre_completo" class="form-label fw-semibold">NOMBRE COMPLETO</label>
          <input type="text" id="nombre_completo" class="form-control input-premium" 
                 value="<?= htmlspecialchars($datosUsuario['nombre_completo'] ?? '') ?>">
        </div>

        <!-- Email -->
        <div class="col-md-6">
          <label for="email" class="form-label fw-semibold">CORREO ELECTRÓNICO</label>
          <input type="email" id="email" class="form-control input-premium" 
                 value="<?= htmlspecialchars($datosUsuario['email'] ?? '') ?>">
        </div>

        <!-- Documento -->
        <div class="col-md-6">
          <label for="documento" class="form-label fw-semibold">DOCUMENTO DE IDENTIDAD</label>
          <input type="text" id="documento" class="form-control input-premium" 
                 value="<?= htmlspecialchars($datosUsuario['documento'] ?? '') ?>" disabled>
        </div>

        <!-- Teléfono -->
        <div class="col-md-6">
          <label for="telefono" class="form-label fw-semibold">TELÉFONO</label>
          <input type="text" id="telefono" class="form-control input-premium" 
                 value="<?= htmlspecialchars($datosUsuario['telefono'] ?? '') ?>">
        </div>

        <!-- Dirección del condominio -->
        <div class="col-md-6">
          <label for="direccion_condominio" class="form-label fw-semibold">DIRECCIÓN</label>
          <input type="text" id="direccion_condominio" class="form-control input-premium" 
                 value="<?= htmlspecialchars($datosUsuario['direccion_condominio'] ?? '') ?>">
        </div>

        <!-- Condominio -->
        <div class="col-md-6">
          <label for="comboCondominio" class="form-label fw-semibold">CONDOMINIO</label>
          <select id="comboCondominio" name="comboCondominio" class="form-select input-premium"
                  data-valor-registrado="<?= htmlspecialchars($datosUsuario['codigo_condominio'] ?? '') ?>">
            <option value="">--Seleccione Condominio--</option>
          </select>
        </div>

        <!-- Torre -->
        <div class="col-md-6">
          <label for="comboTorre" class="form-label fw-semibold">TORRE</label>
          <select id="comboTorre" class="form-select input-premium"
                  data-valor-registrado="<?= htmlspecialchars($datosUsuario['codigo_torre'] ?? '') ?>">
            <option value="">--Seleccione Torre--</option>
          </select>
        </div>

        <!-- Departamento -->
        <div class="col-md-6">
          <label for="comboDepartamento" class="form-label fw-semibold">DEPARTAMENTO</label>
          <select id="comboDepartamento" class="form-select input-premium"
                  data-valor-registrado="<?= htmlspecialchars($datosUsuario['codigo_departamento'] ?? '') ?>">
            <option value="">--Seleccione Departamento--</option>
          </select>
        </div>

        <!-- Botones de acción -->
        <div class="col-12 text-end mt-3 d-flex flex-wrap gap-2 justify-content-end">
          <button type="button" id="btnEditar" class="btn btn-editar"  style="display:none;">
            <i class="fas fa-pen me-1"></i> Editar
          </button>
          <button type="button" id="btnGuardar" class="btn btn-outline-success btn-lg">
            <i class="fas fa-save me-1"></i> GUARDAR
          </button>
          <button type="button" id="btnCancelar" class="btn btn-cancelar" style="display:none;">
            <i class="fas fa-times me-1"></i> Cancelar
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- JS y estilos específicos -->
<script>window.BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>js/combo_condominio.js"></script>
<?php include_once __DIR__ . '/estilos/DatosPersonalesEstilo.php'; ?>
