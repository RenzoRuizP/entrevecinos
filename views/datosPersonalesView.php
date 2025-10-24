<?php 
require_once __DIR__ . '/../Config/config.php';
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
                 value="<?= htmlspecialchars($datosUsuario['nombre_completo'] ?? '') ?>" disabled>
        </div>

        <!-- Email -->
        <div class="col-md-6">
          <label for="email" class="form-label fw-semibold">CORREO ELECTRÓNICO</label>
          <input type="email" id="email" class="form-control input-premium" 
                 value="<?= htmlspecialchars($datosUsuario['email'] ?? '') ?>" disabled>
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
                 value="<?= htmlspecialchars($datosUsuario['telefono'] ?? '') ?>" disabled>
        </div>

        <!-- Dirección del condominio -->
        <div class="col-md-6">
          <label for="direccion_condominio" class="form-label fw-semibold">DIRECCIÓN</label>
          <input type="text" id="direccion_condominio" class="form-control input-premium" 
                 value="<?= htmlspecialchars($datosUsuario['direccion_condominio'] ?? '') ?>" disabled>
        </div>

        <!-- Nombre del condominio -->
        <div class="col-md-6">
  <label for="nombre_condominio" class="form-label fw-semibold">CONDOMINIO</label>
  <select id="nombre_condominio" class="form-select input-premium" disabled>
    <option value="">Seleccione un condominio</option>
    <?php foreach ($condominios as $condominio): ?>
        <option value="<?= $datosUsuario['codigo_condominio'] ?>" <?= ($datosUsuario['codigo_condominio'] == $condominioActual) ? 'selected' : '' ?>>
            <?= htmlspecialchars($datosUsuario['nombre']) ?>
        </option>
    <?php endforeach; ?>
  </select>
</div>

        <!-- Nombre de la torre -->
        <div class="col-md-6">
          <label for="nombre_torre" class="form-label fw-semibold">TORRE</label>
          <select id="nombre_torre" class="form-select input-premium" disabled>
            <option value="">Seleccione una torre</option>
            <?php foreach ($torres as $torre): ?>
                <?php if ($datosUsuario['condominio_id'] == $condominioActual): // mostrar solo torres del condominio seleccionado ?>
                    <option value="<?= $datosUsuario['id'] ?>" <?= ($datosUsuario['id'] == $torreActual) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($datosUsuario['nombre']) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Número de departamento -->
        <div class="col-md-6">
          <label for="numero_departamento" class="form-label fw-semibold">DEPARTAMENTO</label>
          <select id="numero_departamento" class="form-select input-premium" disabled>
            <option value="">Seleccione un departamento</option>
            <?php foreach ($departamentos as $dept): ?>
                <?php if ($datosUsuario['torre_id'] == $torreActual): // mostrar solo departamentos de la torre seleccionada ?>
                    <option value="<?= $datosUsuario['id'] ?>" <?= ($datosUsuario['id'] == $departamentoActual) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($datosUsuario['numero']) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
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
<script src="<?= BASE_URL ?>views/js/DatosPersonales.js"></script>
 <?php include_once __DIR__ . '/estilos/DatosPersonalesEstilo.php'; ?>
