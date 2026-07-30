<?php
require_once __DIR__ . '/../Config/config.php';

$legalConfigPerfil = require __DIR__ . '/../Config/documentos_legales.php';
$enlacesLegalesPerfil = is_array($legalConfigPerfil['enlaces'] ?? null)
    ? $legalConfigPerfil['enlaces']
    : [];

$urlTerminosPerfil = (string)($enlacesLegalesPerfil['terminos_condiciones']
    ?? 'https://www.entrevecinos.pe/legal/terminos-y-condiciones.php');
$urlPrivacidadPerfil = (string)($enlacesLegalesPerfil['politica_privacidad']
    ?? 'https://www.entrevecinos.pe/legal/politica-de-privacidad.php');
$urlLibroPerfil = (string)($enlacesLegalesPerfil['libro_reclamaciones']
    ?? 'https://www.entrevecinos.pe/libro-de-reclamaciones/');
?>

<?php include_once __DIR__ . '/estilos/DatosPersonalesEstilo.php'; ?>

<?php
  $tipoConjunto        = strtolower(trim((string)($datosUsuario['tipo_conjunto'] ?? '')));
  $codigoCondominio    = (string)($datosUsuario['codigo_condominio'] ?? '');
  $codigoUrbanizacion  = (string)($datosUsuario['codigo_urbanizacion'] ?? '');
  $direccionResidencia = (string)($datosUsuario['direccion'] ?? '');
  $comprobante         = (string)($datosUsuario['comprobante_domicilio'] ?? '');

  $ub_depto = (string)($datosUsuario['ubigeo_departamento'] ?? '');
  $ub_prov  = (string)($datosUsuario['ubigeo_provincia'] ?? '');
  $ub_dist  = (string)($datosUsuario['ubigeo_distrito'] ?? '');

  $nombreConjuntoActual = $tipoConjunto === 'urbanizacion'
      ? trim((string)($datosUsuario['nombre_urbanizacion'] ?? ''))
      : trim((string)($datosUsuario['nombre_condominio'] ?? ''));
  $labelConjuntoActual = $tipoConjunto === 'urbanizacion'
      ? 'Urbanización'
      : ($tipoConjunto === 'condominio' ? 'Condominio' : 'Comunidad');
  $iconoConjuntoActual = $tipoConjunto === 'urbanizacion'
      ? 'bi-houses'
      : 'bi-buildings';
  if ($nombreConjuntoActual !== '') {
      $nombreConjuntoLower = mb_strtolower($nombreConjuntoActual, 'UTF-8');
      $labelConjuntoLower = mb_strtolower($labelConjuntoActual, 'UTF-8');
      $nombreVisibleConjunto = (
          $nombreConjuntoLower === $labelConjuntoLower
          || str_starts_with($nombreConjuntoLower, $labelConjuntoLower . ' ')
      )
          ? $nombreConjuntoActual
          : $labelConjuntoActual . ' ' . $nombreConjuntoActual;
  } else {
      $nombreVisibleConjunto = 'Residencia no registrada';
  }

  $basePerfil = rtrim(BASE_URL, '/');
  $fotoPerfilDefault = $basePerfil . '/views/fotos/00000000.png';
  $fotoPerfilRel = trim((string)($datosUsuario['foto_perfil'] ?? ''));

  if ($fotoPerfilRel === '') {
      $fotoPerfilUrl = $fotoPerfilDefault;
  } elseif (preg_match('#^https?://#i', $fotoPerfilRel)) {
      $fotoPerfilUrl = $fotoPerfilRel;
  } elseif (str_starts_with($fotoPerfilRel, '/')) {
      $fotoPerfilUrl = $fotoPerfilRel;
  } else {
      $fotoPerfilUrl = $basePerfil . '/' . ltrim($fotoPerfilRel, '/');
  }
?>

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

<div class="container-datos-personales ev-profile-page fade-in">
  <div class="card border-0 ev-datos-card ev-profile-shell">

    <header class="card-header ev-profile-hero">
      <div class="ev-profile-hero__main">
        <span class="ev-datos-icon" aria-hidden="true">
          <i class="bi bi-person-badge-fill"></i>
        </span>
        <div>
          <span class="ev-profile-hero__eyebrow">MI CUENTA</span>
          <h2>Mi perfil</h2>
          <p>Administra tus datos, residencia y seguridad desde un solo lugar.</p>
        </div>
      </div>

      <div class="ev-profile-hero__community" aria-label="Residencia actual">
        <span class="ev-profile-hero__community-icon" aria-hidden="true">
          <i class="bi <?= htmlspecialchars($iconoConjuntoActual, ENT_QUOTES, 'UTF-8'); ?>"></i>
        </span>
        <span>
          <small>Residencia actual</small>
          <strong><?= htmlspecialchars($nombreVisibleConjunto, ENT_QUOTES, 'UTF-8'); ?></strong>
        </span>
      </div>
    </header>

    <div class="card-body p-4">

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

        <section class="ev-step-panel" data-panel="1">
          <div class="ev-profile-section-head">
            <span class="ev-profile-section-head__icon"><i class="bi bi-person-vcard"></i></span>
            <div>
              <h3>Información personal</h3>
              <p>Actualiza tu teléfono y mantén vigente tu foto de perfil.</p>
            </div>
          </div>

          <div class="ev-profile-photo-panel mb-4">
            <button
              type="button"
              class="ev-profile-photo-trigger"
              data-ev-avatar-trigger="1"
              aria-label="Cambiar foto de perfil">
              <img
                id="evDpFotoPreview"
                data-ev-avatar-img="1"
                src="<?= htmlspecialchars($fotoPerfilUrl, ENT_QUOTES, 'UTF-8'); ?>"
                alt="Foto de perfil"
              >
              <span class="ev-profile-photo-camera" aria-hidden="true">
                <i class="bi bi-camera-fill"></i>
              </span>
            </button>

            <div class="ev-profile-photo-copy">
              <strong>Foto de perfil</strong>
              <p>Haz clic sobre la foto para cargar una imagen desde tu computadora.</p>
              <small>Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 2 MB.</small>
            </div>
          </div>

          <div class="row g-3 ev-profile-form-grid">
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

            <div class="col-12 d-flex justify-content-end">
              <button type="button" class="btn btn-ev-primary" id="btnGuardarPaso1">
                <i class="fas fa-save me-1"></i> Guardar
              </button>
            </div>
          </div>
        </section>

        <section class="ev-step-panel d-none" data-panel="2">
          <div class="ev-profile-section-head">
            <span class="ev-profile-section-head__icon"><i class="bi bi-geo-alt"></i></span>
            <div>
              <h3>Residencia</h3>
              <p>Selecciona primero el distrito; EV mostrará únicamente los conjuntos disponibles en esa ubicación.</p>
            </div>
          </div>

          <div class="ev-current-residence">
            <span class="ev-current-residence__icon"><i class="bi <?= htmlspecialchars($iconoConjuntoActual, ENT_QUOTES, 'UTF-8'); ?>"></i></span>
            <div>
              <small>Comunidad registrada</small>
              <strong><?= htmlspecialchars($nombreVisibleConjunto, ENT_QUOTES, 'UTF-8'); ?></strong>
              <span><?= htmlspecialchars($direccionResidencia ?: 'Dirección no registrada', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
          </div>

          <div class="row g-3 ev-profile-form-grid">

            <div class="col-12">
              <div class="ev-hint">
                <i class="bi bi-shield-check me-2"></i>
                Si cambias tu residencia, se generará una <strong>solicitud</strong> para aprobación del administrador y deberás adjuntar un comprobante.
              </div>
            </div>

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

            <div class="col-12" id="wrapCondominio">
              <div class="row g-3">
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
            </div>

            <div class="col-12 d-none" id="wrapUrbanizacion">
              <div class="row g-3">
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
            </div>

            <div class="col-12">
              <label for="direccion" class="form-label ev-form-label">Dirección</label>
              <input
                type="text"
                id="direccion"
                class="form-control ev-input-rounded"
                value="<?= htmlspecialchars($direccionResidencia, ENT_QUOTES, 'UTF-8'); ?>"
                disabled
              >
              <div class="form-text">La dirección se toma del conjunto residencial seleccionado.</div>
            </div>

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

              <div class="form-text">Tipos permitidos: PDF, JPG, PNG. Tamaño máximo: 5MB.</div>
            </div>

            <div class="col-12 d-flex justify-content-end">
              <button type="button" class="btn btn-ev-primary" id="btnGuardarPaso2">
                <i class="fas fa-save me-1"></i> Guardar
              </button>
            </div>

          </div>
        </section>

        <section class="ev-step-panel d-none" data-panel="3">
          <div class="ev-profile-section-head">
            <span class="ev-profile-section-head__icon"><i class="bi bi-shield-lock"></i></span>
            <div>
              <h3>Seguridad de la cuenta</h3>
              <p>Renueva tu contraseña con una combinación segura y distinta a la actual.</p>
            </div>
          </div>

          <div class="row g-3 ev-profile-form-grid">
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
              <div class="form-text">Recomendación: mínimo 8 caracteres.</div>
            </div>

            <div class="col-12 d-flex justify-content-end">
              <button type="button" class="btn btn-ev-primary" id="btnGuardarPaso3">
                <i class="fas fa-save me-1"></i> Guardar
              </button>
            </div>
          </div>
        </section>

        <div class="ev-wizard-footer mt-4">
          <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <button type="button" class="btn btn-ev-neutral" id="btnAnterior">
              <i class="bi bi-arrow-left me-1"></i> Atrás
            </button>

            <button type="button" class="btn btn-ev-neutral" id="btnSiguiente">
              Siguiente <i class="bi bi-arrow-right ms-1"></i>
            </button>
          </div>
        </div>

      </form>

      <section class="ev-profile-legal mt-4" aria-labelledby="evProfileLegalTitle">
        <div class="ev-profile-legal__header">
          <span class="ev-profile-legal__icon" aria-hidden="true">
            <i class="bi bi-shield-check"></i>
          </span>
          <div>
            <span class="ev-profile-legal__eyebrow">INFORMACIÓN Y ATENCIÓN</span>
            <h3 id="evProfileLegalTitle">Documentos legales</h3>
            <p>Consulta los documentos vigentes de EV o accede al Libro de Reclamaciones Virtual.</p>
          </div>
        </div>

        <div class="ev-profile-legal__grid">
          <a
            class="ev-profile-legal__card"
            href="<?= htmlspecialchars($urlTerminosPerfil, ENT_QUOTES, 'UTF-8'); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Abrir Términos y Condiciones de Uso"
          >
            <span class="ev-profile-legal__card-icon ev-profile-legal__card-icon--green" aria-hidden="true">
              <i class="bi bi-file-earmark-text"></i>
            </span>
            <span class="ev-profile-legal__card-copy">
              <strong>Términos y Condiciones</strong>
              <small>Conoce las reglas de uso de Entre Vecinos.</small>
            </span>
            <span class="ev-profile-legal__open" aria-hidden="true">
              <i class="bi bi-box-arrow-up-right"></i>
            </span>
          </a>

          <a
            class="ev-profile-legal__card"
            href="<?= htmlspecialchars($urlPrivacidadPerfil, ENT_QUOTES, 'UTF-8'); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Abrir Política de Privacidad y Tratamiento de Datos Personales"
          >
            <span class="ev-profile-legal__card-icon ev-profile-legal__card-icon--green" aria-hidden="true">
              <i class="bi bi-shield-lock"></i>
            </span>
            <span class="ev-profile-legal__card-copy">
              <strong>Privacidad y datos personales</strong>
              <small>Revisa cómo EV utiliza y protege tus datos.</small>
            </span>
            <span class="ev-profile-legal__open" aria-hidden="true">
              <i class="bi bi-box-arrow-up-right"></i>
            </span>
          </a>

          <a
            class="ev-profile-legal__card ev-profile-legal__card--book"
            href="<?= htmlspecialchars($urlLibroPerfil, ENT_QUOTES, 'UTF-8'); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Abrir Libro de Reclamaciones Virtual"
          >
            <span class="ev-profile-legal__card-icon ev-profile-legal__card-icon--orange" aria-hidden="true">
              <i class="bi bi-journal-check"></i>
            </span>
            <span class="ev-profile-legal__card-copy">
              <strong>Libro de Reclamaciones</strong>
              <small>Registra formalmente un reclamo o una queja sobre EV.</small>
            </span>
            <span class="ev-profile-legal__open" aria-hidden="true">
              <i class="bi bi-box-arrow-up-right"></i>
            </span>
          </a>
        </div>

        <div class="ev-profile-legal__note">
          <i class="bi bi-info-circle" aria-hidden="true"></i>
          <span>El Libro de Reclamaciones es único para EV. Tanto la página pública como la app acceden al mismo formulario oficial.</span>
        </div>
      </section>

    </div>
  </div>
</div>
