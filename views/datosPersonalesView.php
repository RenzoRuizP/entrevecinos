<?php 
require_once __DIR__ . '/../Config/config.php'; // cargamos BASE_URL
?>

<!-- Vista: DatosPersonalesView.php -->
<?php include_once __DIR__ . '/estilos/DatosPersonalesEstilo.php'; ?>

<?php
  // Nuevo modelo: usuario_residencia.tipo_conjunto + codigos + direccion
  $tipoConjunto        = $datosUsuario['tipo_conjunto'] ?? ''; // 'condominio' | 'urbanizacion'
  $codigoCondominio    = $datosUsuario['codigo_condominio'] ?? '';
  $codigoUrbanizacion  = $datosUsuario['codigo_urbanizacion'] ?? '';
  $direccionResidencia = $datosUsuario['direccion'] ?? '';
?>

<!-- Estado de residencia para JS (no visible) -->
<div
  id="dp-residencia"
  data-tipo="<?= htmlspecialchars($tipoConjunto, ENT_QUOTES, 'UTF-8'); ?>"
  data-codigo-condominio="<?= htmlspecialchars($codigoCondominio, ENT_QUOTES, 'UTF-8'); ?>"
  data-codigo-urbanizacion="<?= htmlspecialchars($codigoUrbanizacion, ENT_QUOTES, 'UTF-8'); ?>"
  data-direccion="<?= htmlspecialchars($direccionResidencia, ENT_QUOTES, 'UTF-8'); ?>"
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
          <h5 class="mb-1">Datos personales</h5>
          <small class="ev-datos-subtitle">
            Mantén tu información actualizada para mejorar tu experiencia dentro del condominio.
          </small>
        </div>
      </div>
    </div>

    <!-- BODY -->
    <div class="card-body p-4">
      <form id="formDatosPersonales" class="row g-3 ev-datos-form" autocomplete="off">

        <!-- Nombre completo -->
        <div class="col-md-6">
          <label for="nombre_completo" class="form-label ev-form-label">Nombre completo</label>
          <div class="position-relative">
            <input 
              type="text" 
              id="nombre_completo" 
              class="form-control ev-input-rounded" 
              value="<?= htmlspecialchars($datosUsuario['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
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
              value="<?= htmlspecialchars($datosUsuario['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
              disabled
            >
          </div>
        </div>

        <!-- Documento (ahora DISABLED por requerimiento) -->
        <div class="col-md-6">
          <label for="documento" class="form-label ev-form-label">Documento de identidad</label>
          <div class="position-relative">
            <input 
              type="text" 
              id="documento" 
              class="form-control ev-input-rounded" 
              value="<?= htmlspecialchars($datosUsuario['documento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
              disabled
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
              value="<?= htmlspecialchars($datosUsuario['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
          </div>
        </div>

        <!-- ==========================
             RESIDENCIA (nuevo)
             - Mantiene IDs existentes
        =========================== -->

        <!-- Wrapper: Condominio (incluye Torre/Departamento como ya tienes) -->
        <div id="wrapCondominio" class="row g-3">
          <!-- Condominio -->
          <div class="col-md-4">
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

          <!-- Torre -->
          <div class="col-md-4">
            <label for="comboTorre" class="form-label ev-form-label">Torre</label>
            <select 
              id="comboTorre" 
              class="form-select ev-input-rounded"
              data-valor-registrado="<?= htmlspecialchars($datosUsuario['codigo_torre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
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
              data-valor-registrado="<?= htmlspecialchars($datosUsuario['codigo_departamento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
              <option value="">-- Seleccione departamento --</option>
            </select>
          </div>
        </div>

        <!-- Wrapper: Urbanización (nuevo) -->
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

        <!-- Wrapper: Dirección (nuevo) -->
        <div id="wrapDireccion" class="row g-3 d-none">
          <div class="col-12">
            <label for="direccion" class="form-label ev-form-label">Dirección</label>
            <input 
              type="text" 
              id="direccion" 
              class="form-control ev-input-rounded" 
              value="<?= htmlspecialchars($direccionResidencia, ENT_QUOTES, 'UTF-8'); ?>"
              placeholder="Ej.: Torre A, Dpto 1203 / Calle - Mz - Lt..."
            >
          </div>
        </div>

        <!-- Footer de acciones -->
        <div class="col-12 ev-datos-footer">
          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button type="button" id="btnGuardar" class="btn btn-ev-primary btn-guardar">
              <i class="fas fa-save me-1"></i> Guardar
            </button>

            <button type="button" id="btnCancelar" class="btn btn-ev-neutral btn-cancelar" style="display:none;">
              <i class="fas fa-times me-1"></i> Cancelar
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

