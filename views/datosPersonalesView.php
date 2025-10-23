<?php 
require_once __DIR__ . '/../Config/config.php';
?>

<!-- Vista: DatosPersonalesView.php -->
<div class="container-datos-personales fade-in">
  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-header bg-success text-white d-flex align-items-center justify-content-between rounded-top-4">
      <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Mis Datos Personales</h5>
      <button id="btnEditar" class="btn btn-light btn-sm rounded-pill px-3">
        <i class="fas fa-pen"></i> Editar
      </button>
    </div>

    <div class="card-body p-4">
      <form id="formDatosPersonales" class="row g-3" autocomplete="off">

        <!-- Nombre completo -->
        <div class="col-md-6">
          <label for="nombre_completo" class="form-label fw-semibold">Nombre completo</label>
          <input type="text" id="nombre_completo" class="form-control rounded-3"
                 value="<?= htmlspecialchars($datosUsuario['nombre_completo'] ?? '') ?>" disabled>
        </div>

        <!-- Email -->
        <div class="col-md-6">
          <label for="email" class="form-label fw-semibold">Correo electrónico</label>
          <input type="email" id="email" class="form-control rounded-3"
                 value="<?= htmlspecialchars($datosUsuario['email'] ?? '') ?>" disabled>
        </div>

        <!-- Documento -->
        <div class="col-md-6">
          <label for="documento" class="form-label fw-semibold">Documento</label>
          <input type="text" id="documento" class="form-control rounded-3"
                 value="<?= htmlspecialchars($datosUsuario['documento'] ?? '') ?>" disabled>
        </div>

        <!-- Teléfono -->
        <div class="col-md-6">
          <label for="telefono" class="form-label fw-semibold">Teléfono</label>
          <input type="text" id="telefono" class="form-control rounded-3"
                 value="<?= htmlspecialchars($datosUsuario['telefono'] ?? '') ?>" disabled>
        </div>

        <!-- Dirección del condominio -->
        <div class="col-md-6">
          <label for="direccion_condominio" class="form-label fw-semibold">Dirección del condominio</label>
          <input type="text" id="direccion_condominio" class="form-control rounded-3"
                 value="<?= htmlspecialchars($datosUsuario['direccion_condominio'] ?? '') ?>" disabled>
        </div>

        <!-- Nombre del condominio -->
        <div class="col-md-6">
          <label for="nombre_condominio" class="form-label fw-semibold">Nombre del condominio</label>
          <input type="text" id="nombre_condominio" class="form-control rounded-3"
                 value="<?= htmlspecialchars($datosUsuario['nombre_condominio'] ?? '') ?>" disabled>
        </div>

        <!-- Nombre de la torre -->
        <div class="col-md-6">
          <label for="nombre_torre" class="form-label fw-semibold">Nombre de la torre</label>
          <input type="text" id="nombre_torre" class="form-control rounded-3"
                 value="<?= htmlspecialchars($datosUsuario['nombre_torre'] ?? '') ?>" disabled>
        </div>

        <!-- Número de departamento -->
        <div class="col-md-6">
          <label for="numero_departamento" class="form-label fw-semibold">Número de departamento</label>
          <input type="text" id="numero_departamento" class="form-control rounded-3"
                 value="<?= htmlspecialchars($datosUsuario['numero_departamento'] ?? '') ?>" disabled>
        </div>

        <!-- Botones -->
        <div class="col-12 text-end mt-3">
          <button type="button" id="btnGuardar" class="btn btn-success rounded-pill px-4 me-2" style="display:none;">
            <i class="fas fa-save me-1"></i> Guardar cambios
          </button>
          <button type="button" id="btnCancelar" class="btn btn-secondary rounded-pill px-4" style="display:none;">
            <i class="fas fa-times me-1"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JS y estilos específicos -->
<script>window.BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>views/js/DatosPersonales.js"></script>
<link rel="stylesheet" href="<?= BASE_URL ?>views/estilos/DatosPersonalesEstilo.php">
