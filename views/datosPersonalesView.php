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
        <!-- Nombres -->
        <div class="col-md-6">
          <label for="nombre" class="form-label fw-semibold">Nombre</label>
          <input type="text" id="nombre" class="form-control rounded-3" disabled>
        </div>

        <!-- Apellidos -->
        <div class="col-md-6">
          <label for="apellido" class="form-label fw-semibold">Apellido</label>
          <input type="text" id="apellido" class="form-control rounded-3" disabled>
        </div>

        <!-- Correo -->
        <div class="col-md-6">
          <label for="correo" class="form-label fw-semibold">Correo electrónico</label>
          <input type="email" id="correo" class="form-control rounded-3" disabled>
        </div>

        <!-- Teléfono -->
        <div class="col-md-6">
          <label for="telefono" class="form-label fw-semibold">Teléfono</label>
          <input type="text" id="telefono" class="form-control rounded-3" disabled>
        </div>

        <!-- Dirección -->
        <div class="col-12">
          <label for="direccion" class="form-label fw-semibold">Dirección</label>
          <textarea id="direccion" class="form-control rounded-3" rows="2" disabled></textarea>
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