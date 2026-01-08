<?php
require_once __DIR__ . '/../Config/config.php';
?>

<?php include_once __DIR__ . '/estilos/DatosPersonalesEstilo.php'; ?>

<?php
  $tipoConjunto        = $datosUsuario['tipo_conjunto'] ?? ''; // 'condominio' | 'urbanizacion'
  $codigoCondominio    = $datosUsuario['codigo_condominio'] ?? '';
  $codigoUrbanizacion  = $datosUsuario['codigo_urbanizacion'] ?? '';
  $direccionResidencia = $datosUsuario['direccion'] ?? '';

  // Ubigeo actuales si ya los tienes en BD (si no existen, quedan vacíos)
  $ub_depto = $datosUsuario['ubigeo_departamento'] ?? '';
  $ub_prov  = $datosUsuario['ubigeo_provincia'] ?? '';
  $ub_dist  = $datosUsuario['ubigeo_distrito'] ?? '';
?>

<!-- Estado base (para comparar cambios desde JS) -->
<div
  id="dp-residencia"
  data-tipo="<?= htmlspecialchars($tipoConjunto, ENT_QUOTES, 'UTF-8'); ?>"
  data-codigo-condominio="<?= htmlspecialchars($codigoCondominio, ENT_QUOTES, 'UTF-8'); ?>"
  data-codigo-urbanizacion="<?= htmlspecialchars($codigoUrbanizacion, ENT_QUOTES, 'UTF-8'); ?>"
  data-direccion="<?= htmlspecialchars($direccionResidencia, ENT_QUOTES, 'UTF-8'); ?>"
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
          <h5 class="mb-1">Datos personales</h5>
          <small class="ev-datos-subtitle">
            Mantén tu información actualizada para mejorar tu experiencia.
          </small>
        </div>
      </div>
    </div>

    <!-- BODY -->
    <div class="card-body p-4">
      <form id="formDatosPersonales" class="row g-3 ev-datos-form" autocomplete="off" enctype="multipart/form-data">

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

        <!-- Documento (DISABLED) -->
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
             RESIDENCIA (editable)
        =========================== -->

        <div class="col-12">
          <div class="ev-hint">
            <i class="bi bi-shield-check me-2"></i>
            Si cambias tu residencia, se generará una <strong>solicitud</strong>
            para aprobación del administrador y deberás adjuntar un recibo.
          </div>
        </div>

        <!-- Selector tipo de conjunto (permite cambiar) -->
        <div class="col-12">
          <label class="form-label ev-form-label mb-2">Tipo de residencia</label>
          <div class="d-flex flex-wrap gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="dpTipoResidencia" id="dpTipoCondominio" value="condominio"
                <?= (strtolower($tipoConjunto) === 'condominio') ? 'checked' : ''; ?>>
              <label class="form-check-label" for="dpTipoCondominio">Condominio</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="dpTipoResidencia" id="dpTipoUrbanizacion" value="urbanizacion"
                <?= (strtolower($tipoConjunto) === 'urbanizacion') ? 'checked' : ''; ?>>
              <label class="form-check-label" for="dpTipoUrbanizacion">Urbanización</label>
            </div>
          </div>
        </div>

        <!-- Wrapper: Condominio + Departamento (EV existente) -->
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

          <div class="col-md-6">
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

        <!-- Wrapper: Urbanización (EV existente) -->
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

        <!-- NUEVO: Ubigeo (para cambio de domicilio) -->
        <div id="wrapUbigeo" class="row g-3">
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
          <div class="col-12">
            <div class="form-text">
              Este ubigeo se usa para validar y auditar cambios de domicilio.
            </div>
          </div>
        </div>

        <!-- Wrapper: Dirección (EV existente) -->
        <div id="wrapDireccion" class="row g-3 d-none">
          <div class="col-12">
            <label for="direccion" class="form-label ev-form-label">Dirección</label>
            <input
              type="text"
              id="direccion"
              class="form-control ev-input-rounded"
              value="<?= htmlspecialchars($direccionResidencia, ENT_QUOTES, 'UTF-8'); ?>"
              placeholder="Ej.: Dpto 1203 / Calle - Mz - Lt..."
            >
          </div>
        </div>

        <!-- NUEVO: Upload obligatorio si cambia residencia -->
        <div id="wrapUploadDomicilio" class="row g-3 d-none">
          <div class="col-12">
            <label for="dpDocDomicilio" class="form-label ev-form-label">
              Recibo / Comprobante de domicilio (obligatorio si cambias residencia)
            </label>

            <input
              type="file"
              id="dpDocDomicilio"
              name="dpDocDomicilio"
              class="form-control ev-input-rounded"
              accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
            />

            <div class="form-text">
              Tipos permitidos: PDF, JPG, PNG. Peso máximo: 5MB.
            </div>
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

<!-- Script existente -->
<script src="<?= rtrim(BASE_URL,'/') ?>/views/js/datosPersonales.js"></script>

<!-- Micro-script UI (no rompe tu JS): solo alterna vistas y muestra upload si hay cambio -->
<script>
(function(){
  const root = document.getElementById('dp-residencia');
  const wrapCondo = document.getElementById('wrapCondominio');
  const wrapUrb = document.getElementById('wrapUrbanizacion');
  const wrapDir = document.getElementById('wrapDireccion');
  const wrapUp = document.getElementById('wrapUploadDomicilio');

  const rCondo = document.getElementById('dpTipoCondominio');
  const rUrb = document.getElementById('dpTipoUrbanizacion');

  const selCondo = document.getElementById('comboCondominio');
  const selUrb = document.getElementById('comboUrbanizacion');
  const selDpto = document.getElementById('comboDepartamento');
  const txtDir = document.getElementById('direccion');

  const ubD = document.getElementById('dpUbDepto');
  const ubP = document.getElementById('dpUbProv');
  const ubDi = document.getElementById('dpUbDist');
  const file = document.getElementById('dpDocDomicilio');

  if(!root || !wrapCondo || !wrapUrb || !wrapDir || !wrapUp) return;

  const base = {
    tipo: (root.dataset.tipo || '').trim(),
    codCondo: (root.dataset.codigoCondominio || '').trim(),
    codUrb: (root.dataset.codigoUrbanizacion || '').trim(),
    dir: (root.dataset.direccion || '').trim(),
    ub_depto: (root.dataset.ubDepto || '').trim(),
    ub_prov: (root.dataset.ubProv || '').trim(),
    ub_dist: (root.dataset.ubDist || '').trim(),
  };

  function setHidden(el, hidden){ if(!el) return; el.classList.toggle('d-none', !!hidden); }

  function currentTipo(){
    if(rCondo && rCondo.checked) return 'condominio';
    if(rUrb && rUrb.checked) return 'urbanizacion';
    return base.tipo || '';
  }

  function anyResidenciaChanged(){
    const t = currentTipo();
    const cCondo = (selCondo?.value || '').trim();
    const cDpto = (selDpto?.value || '').trim();
    const cUrb = (selUrb?.value || '').trim();
    const cDir = (txtDir?.value || '').trim();

    const cUbD = (ubD?.value || '').trim();
    const cUbP = (ubP?.value || '').trim();
    const cUbDi = (ubDi?.value || '').trim();

    // Cambio de tipo o códigos/dirección/ubigeo
    if(String(t) !== String(base.tipo)) return true;

    if(t === 'condominio'){
      if(String(cCondo) !== String(base.codCondo)) return true;
      // si quieres validar depto: aquí compara con tu campo real si lo tienes disponible
      if(cDpto && cDpto !== (String(<?= json_encode((string)($datosUsuario['codigo_departamento'] ?? '')); ?>) )) return true;
    }
    if(t === 'urbanizacion'){
      if(String(cUrb) !== String(base.codUrb)) return true;
    }

    if(cDir && String(cDir) !== String(base.dir)) return true;

    if(cUbD && String(cUbD) !== String(base.ub_depto)) return true;
    if(cUbP && String(cUbP) !== String(base.ub_prov)) return true;
    if(cUbDi && String(cUbDi) !== String(base.ub_dist)) return true;

    return false;
  }

  function refreshUI(){
    const t = currentTipo();
    setHidden(wrapDir, !(t === 'condominio' || t === 'urbanizacion'));
    setHidden(wrapCondo, t !== 'condominio');
    setHidden(wrapUrb, t !== 'urbanizacion');

    const changed = anyResidenciaChanged();
    setHidden(wrapUp, !changed);

    // Si cambió residencia, el upload será requerido (para que tu JS lo valide)
    if(file){
      file.required = !!changed;
    }
  }

  [rCondo, rUrb, selCondo, selUrb, selDpto, txtDir, ubD, ubP, ubDi].forEach((el)=>{
    if(!el) return;
    el.addEventListener('change', refreshUI);
    el.addEventListener('input', refreshUI);
  });

  refreshUI();
})();
</script>
