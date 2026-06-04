<?php
// views/comunidadGestionView.php

declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';

$usuarioVista = isset($usuario) && is_array($usuario) ? $usuario : [];
$rolVista = strtolower(trim((string)($usuarioVista['rol'] ?? $usuarioVista['nombre_rol'] ?? '')));
$esAdminSistemaVista = ($rolVista === 'admin');

$nombreComunidadVista = trim((string)(
    $nombreComunidad
    ?? $usuarioVista['conjunto_nombre']
    ?? $usuarioVista['nombre_conjunto']
    ?? $usuarioVista['urbanizacion_nombre']
    ?? $usuarioVista['nombre_urbanizacion']
    ?? $usuarioVista['condominio_nombre']
    ?? $usuarioVista['nombre_condominio']
    ?? ''
));

$tipoConjuntoVista = strtolower(trim((string)(
    $tipoConjunto
    ?? $usuarioVista['tipo_conjunto']
    ?? $usuarioVista['conjunto_tipo']
    ?? ''
)));

if ($nombreComunidadVista === '') {
    $nombreComunidadVista = $esAdminSistemaVista
        ? 'Todas las comunidades'
        : 'Comunidad pendiente de asignación';
}

$etiquetaTipo = match ($tipoConjuntoVista) {
    'urbanizacion' => 'Urbanización',
    'condominio'   => 'Condominio',
    default        => 'Comunidad',
};

$nombreComunidadVisual = $nombreComunidadVista;

if (
    !$esAdminSistemaVista
    && $nombreComunidadVista !== 'Comunidad pendiente de asignación'
    && in_array($tipoConjuntoVista, ['urbanizacion', 'condominio'], true)
) {
    $nombreLower = mb_strtolower($nombreComunidadVista, 'UTF-8');
    $etiquetaLower = mb_strtolower($etiquetaTipo, 'UTF-8');

    if ($nombreLower !== $etiquetaLower && !str_starts_with($nombreLower, $etiquetaLower . ' ')) {
        $nombreComunidadVisual = $etiquetaTipo . ' ' . $nombreComunidadVista;
    }
}

$columnasTablaVista = $esAdminSistemaVista ? 6 : 5;
$baseUrlVista = rtrim(BASE_URL, '/');
$jsPathAbs = __DIR__ . '/js/comunidadGestion.js';
$jsVersion = @filemtime($jsPathAbs) ?: (defined('EV_APP_VER') ? EV_APP_VER : time());
?>

<?php include_once __DIR__ . '/estilos/comunidadBaseEstilo.php'; ?>

<section
  class="ev-com-shell ev-com-management fade-in"
  data-es-admin-sistema="<?= $esAdminSistemaVista ? '1' : '0' ?>"
  data-comunidad-visible="<?= htmlspecialchars($nombreComunidadVisual, ENT_QUOTES, 'UTF-8') ?>"
  aria-label="Gestión de Comunidad"
>
  <header class="ev-com-hero">
    <div>
      <span class="ev-com-kicker">
        <i class="bi bi-people-fill"></i> Comunidad
      </span>

      <h1 class="ev-com-title">Gestionar publicaciones</h1>

      <p class="ev-com-subtitle">
        Crea comunicados, noticias y eventos institucionales para mantener informados
        a los vecinos de la comunidad.
      </p>
    </div>

    <div class="ev-com-pill" aria-label="Comunidad asignada">
      <i class="bi bi-house-heart-fill"></i>
      <span><?= htmlspecialchars($nombreComunidadVisual, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </header>

  <div class="ev-com-stats" aria-label="Resumen de publicaciones">
    <article class="ev-com-stat">
      <div class="ev-com-stat-icon"><i class="bi bi-megaphone"></i></div>
      <div>
        <span>Publicadas</span>
        <strong id="evComCountPublicadas">0</strong>
      </div>
    </article>

    <article class="ev-com-stat">
      <div class="ev-com-stat-icon"><i class="bi bi-pencil-square"></i></div>
      <div>
        <span>Borradores</span>
        <strong id="evComCountBorradores">0</strong>
      </div>
    </article>

    <article class="ev-com-stat">
      <div class="ev-com-stat-icon"><i class="bi bi-calendar-event"></i></div>
      <div>
        <span>Eventos próximos</span>
        <strong id="evComCountEventos">0</strong>
      </div>
    </article>

    <article class="ev-com-stat">
      <div class="ev-com-stat-icon"><i class="bi bi-star"></i></div>
      <div>
        <span>Destacadas</span>
        <strong id="evComCountDestacadas">0</strong>
      </div>
    </article>
  </div>

  <section class="ev-com-list-card" aria-label="Listado de publicaciones">
    <div class="ev-com-list-head">
      <div>
        <h2>Publicaciones de comunidad</h2>
        <p id="evComMeta">Gestiona el contenido oficial visible para tus vecinos.</p>
      </div>
      <button type="button" class="ev-com-btn ev-com-btn-primary" id="btnNuevaPublicacionCom">
        <i class="bi bi-plus-lg"></i> Nueva publicación
      </button>
    </div>

    <form class="ev-com-filters" id="filtrosComunidadForm">
      <div class="ev-com-search">
        <i class="bi bi-search"></i>
        <input type="text" id="buscarCom" placeholder="Buscar por título o contenido..." autocomplete="off">
      </div>
      <select id="estadoCom" aria-label="Filtrar por estado">
        <option value="all">Todos los estados</option>
        <option value="borrador">Borrador</option>
        <option value="publicado">Publicado</option>
        <option value="inactivo">Inactivo</option>
        <option value="ocultado_moderacion">Ocultado por moderación</option>
      </select>
      <select id="tipoFiltroCom" aria-label="Filtrar por tipo">
        <option value="all">Todos los tipos</option>
        <option value="comunicado">Comunicados</option>
        <option value="noticia">Noticias</option>
        <option value="evento">Eventos</option>
      </select>
      <button type="submit" class="ev-com-btn ev-com-btn-outline">
        <i class="bi bi-funnel"></i> Filtrar
      </button>
    </form>

    <div class="ev-com-table-wrap">
      <table class="ev-com-table">
        <thead>
          <tr>
            <th>Publicación</th>
            <?php if ($esAdminSistemaVista): ?>
              <th>Comunidad</th>
            <?php endif; ?>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th class="ev-com-actions-th">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyComunidadPublicaciones">
          <tr>
            <td colspan="<?= $columnasTablaVista ?>">
              <div class="ev-com-loading"><span></span> Cargando publicaciones...</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="ev-com-table-footer">
      <span id="evComFooterLeft">Mostrando 0 de 0</span>
      <div class="ev-com-pager">
        <button type="button" id="btnAnteriorCom" disabled><i class="bi bi-chevron-left"></i></button>
        <span id="paginaCom">1 / 1</span>
        <button type="button" id="btnSiguienteCom" disabled><i class="bi bi-chevron-right"></i></button>
      </div>
    </div>
  </section>

  <!-- MODAL: NUEVA / EDITAR PUBLICACIÓN | ESTÁNDAR VISUAL EV -->
  <div
    class="modal fade ev-com-editor-modal"
    id="modalPublicacionCom"
    tabindex="-1"
    aria-labelledby="evComFormTitle"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
  >
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
      <div class="modal-content ev-com-modal-editor ev-com-publish-modal">
        <div class="modal-header ev-com-publish-head">
          <h2 class="modal-title" id="evComFormTitle">
            <i class="bi bi-plus-circle" aria-hidden="true"></i>
            <span>Nueva publicación</span>
          </h2>
          <button type="button" class="ev-com-modal-close" id="btnCerrarFormularioCom" aria-label="Cerrar">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form id="formComunidadPublicacion" class="ev-com-publish-form" enctype="multipart/form-data" autocomplete="off" novalidate>
          <input type="hidden" id="codigoPublicacionCom" value="">
          <input type="hidden" id="tipoConjuntoCom" name="tipo_conjunto" value="">
          <input type="hidden" id="codigoComunidadCom" name="codigo_comunidad" value="">
          <input type="hidden" id="tipoPublicacionCom" name="tipo_publicacion" value="comunicado">

          <div class="modal-body ev-com-publish-body">
            <div class="ev-com-editor-layout">

              <div class="ev-com-editor-scroll" aria-label="Formulario de publicación">
                <section class="ev-com-step-card" aria-labelledby="evComPasoTipoTitle">
                  <span class="ev-com-step-tag">Paso 1</span>
                  <h3 id="evComPasoTipoTitle">¿Qué deseas publicar?</h3>
                  <p>Selecciona el tipo de contenido institucional para tu comunidad.</p>

                  <div class="ev-com-type-cards" role="radiogroup" aria-label="Tipo de publicación">
                    <button type="button" class="ev-com-type-option is-selected" data-com-tipo="comunicado" aria-pressed="true">
                      <span class="ev-com-type-icon"><i class="bi bi-megaphone"></i></span>
                      <span>
                        <strong>Comunicado</strong>
                        <small>Aviso oficial o urgente para la comunidad.</small>
                      </span>
                      <i class="bi bi-check-circle-fill ev-com-type-check"></i>
                    </button>
                    <button type="button" class="ev-com-type-option" data-com-tipo="noticia" aria-pressed="false">
                      <span class="ev-com-type-icon"><i class="bi bi-newspaper"></i></span>
                      <span>
                        <strong>Noticia</strong>
                        <small>Novedades para vecinos.</small>
                      </span>
                      <i class="bi bi-check-circle-fill ev-com-type-check"></i>
                    </button>
                    <button type="button" class="ev-com-type-option" data-com-tipo="evento" aria-pressed="false">
                      <span class="ev-com-type-icon"><i class="bi bi-calendar-event"></i></span>
                      <span>
                        <strong>Evento</strong>
                        <small>Actividad con fecha y lugar.</small>
                      </span>
                      <i class="bi bi-check-circle-fill ev-com-type-check"></i>
                    </button>
                  </div>
                </section>

                <section class="ev-com-step-card" aria-labelledby="evComPasoPortadaTitle">
                  <span class="ev-com-step-tag">Paso 2</span>
                  <h3 id="evComPasoPortadaTitle">Imagen de portada</h3>
                  <p id="textoAyudaPortadaCom">Una imagen clara refuerza el mensaje oficial del comunicado.</p>

                  <label class="ev-com-dropzone" for="imagenPortadaCom" id="zonaPortadaCom">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <strong>Arrastra tu imagen aquí o haz clic para seleccionarla</strong>
                    <small>JPG · PNG · WEBP · Máximo 2 MB</small>
                  </label>
                  <input class="ev-com-file-input" type="file" id="imagenPortadaCom" name="imagen_portada" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp">

                  <div class="ev-com-upload-selected" id="portadaPreviewWrapCom" hidden>
                    <img id="portadaPreviewCom" src="" alt="Vista previa de portada seleccionada">
                    <div class="ev-com-upload-copy">
                      <strong>Portada seleccionada</strong>
                      <small>Imagen lista para acompañar tu publicación.</small>
                      <span class="ev-com-upload-name" id="nombrePortadaCom"></span>
                    </div>
                    <button type="button" class="ev-com-file-change" id="btnCambiarPortadaCom">Cambiar</button>
                  </div>
                </section>

                <section class="ev-com-step-card" aria-labelledby="evComPasoInfoTitle">
                  <span class="ev-com-step-tag">Paso 3</span>
                  <h3 id="evComPasoInfoTitle">Información principal</h3>

                  <div class="ev-com-field">
                    <label for="tituloCom">Título <span>*</span></label>
                    <input type="text" id="tituloCom" name="titulo" maxlength="140" placeholder="Escribe un título claro para tus vecinos" required>
                    <small><span id="tituloCharsCom">0</span>/140 caracteres</small>
                  </div>

                  <div class="ev-com-field">
                    <label for="resumenCom">Resumen corto <span>*</span></label>
                    <textarea id="resumenCom" name="resumen" rows="2" maxlength="240" placeholder="Resume la información que aparecerá primero en la publicación." required></textarea>
                    <small><span id="resumenCharsCom">0</span>/240 caracteres</small>
                  </div>

                  <div class="ev-com-field">
                    <label for="contenidoCom">Contenido <span>*</span></label>
                    <textarea id="contenidoCom" name="contenido" rows="5" placeholder="Redacta la información completa que recibirán los vecinos." required></textarea>
                  </div>
                </section>

                <section class="ev-com-step-card" aria-labelledby="evComPasoConfigTitle">
                  <span class="ev-com-step-tag">Paso 4</span>
                  <h3 id="evComPasoConfigTitle">Publicación y visibilidad</h3>

                  <div class="ev-com-form-grid ev-com-settings-grid">
                    <div class="ev-com-field">
                      <label for="prioridadCom">Prioridad <span>*</span></label>
                      <select id="prioridadCom" name="prioridad" required>
                        <option value="normal">Normal</option>
                        <option value="importante">Importante</option>
                        <option value="urgente">Urgente</option>
                      </select>
                    </div>

                    <div class="ev-com-field">
                      <label for="fechaExpiracionCom">Fecha de expiración</label>
                      <input type="datetime-local" id="fechaExpiracionCom" name="fecha_expiracion">
                      <small>Opcional. Dejará de priorizarse luego de esta fecha.</small>
                    </div>

                    <div class="ev-com-field ev-com-field-full" id="campoDestinoCom" <?= $esAdminSistemaVista ? '' : 'hidden' ?>>
                      <label for="destinoCom">Comunidad destino <span>*</span></label>
                      <select id="destinoCom">
                        <option value="">Seleccionar comunidad</option>
                      </select>
                    </div>
                  </div>

                  <label class="ev-com-highlight-switch">
                    <input type="checkbox" id="destacadoCom" name="destacado_dashboard" value="1">
                    <span class="ev-com-switch-control" aria-hidden="true"></span>
                    <span>
                      <strong>Mostrar como destacado en el inicio</strong>
                      <small>Este contenido tendrá mayor visibilidad para los vecinos.</small>
                    </span>
                  </label>
                </section>

                <fieldset class="ev-com-step-card ev-com-event-fields d-none" id="camposEventoCom">
                  <legend>
                    <span class="ev-com-step-tag">Paso 5</span>
                    <strong>Datos del evento</strong>
                  </legend>
                  <p>Completa la fecha y el lugar donde se realizará la actividad.</p>
                  <div class="ev-com-form-grid">
                    <div class="ev-com-field">
                      <label for="fechaEventoInicioCom">Inicio del evento <span>*</span></label>
                      <input type="datetime-local" id="fechaEventoInicioCom" name="fecha_evento_inicio">
                    </div>
                    <div class="ev-com-field">
                      <label for="fechaEventoFinCom">Fin del evento</label>
                      <input type="datetime-local" id="fechaEventoFinCom" name="fecha_evento_fin">
                    </div>
                    <div class="ev-com-field ev-com-field-full">
                      <label for="ubicacionEventoCom">Lugar <span>*</span></label>
                      <input type="text" id="ubicacionEventoCom" name="ubicacion_evento" maxlength="180" placeholder="Ej. Local comunal de Villa Flores">
                    </div>
                  </div>
                </fieldset>
              </div>

              <aside class="ev-com-live-preview" aria-label="Vista previa para el vecino">
                <div class="ev-com-live-head">
                  <div>
                    <small>Vista previa</small>
                    <h3>Así lo verán tus vecinos</h3>
                  </div>
                  <span class="ev-com-live-type" id="vistaTipoCom">Comunicado</span>
                </div>

                <div class="ev-com-live-image" id="vistaImagenBoxCom">
                  <img id="vistaImagenCom" src="" alt="Vista previa de la imagen de portada" hidden>
                  <div id="vistaImagenEmptyCom">
                    <i class="bi bi-image"></i>
                    <div>
                      <strong>Tu imagen de portada aparecerá aquí</strong>
                      <p>Agrega una imagen clara para acompañar el contenido.</p>
                    </div>
                  </div>
                </div>

                <div class="ev-com-live-card">
                  <div class="ev-com-live-badges">
                    <span id="vistaPrioridadCom" class="ev-com-live-priority ev-com-live-priority--normal">Normal</span>
                    <span id="vistaDestacadoCom" class="ev-com-live-featured" hidden><i class="bi bi-star-fill"></i> Destacado</span>
                  </div>
                  <h4 id="vistaTituloCom">Título de la publicación</h4>
                  <p id="vistaResumenCom">El resumen breve que verán los vecinos aparecerá aquí.</p>
                  <div class="ev-com-live-event d-none" id="vistaEventoCom">
                    <i class="bi bi-calendar-event"></i>
                    <span id="vistaEventoDetalleCom">Fecha y lugar del evento</span>
                  </div>
                  <div class="ev-com-live-community">
                    <i class="bi bi-house-heart"></i>
                    <span id="vistaComunidadCom"><?= htmlspecialchars($nombreComunidadVisual, ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                </div>

                <div class="ev-com-live-note">
                  <i class="bi bi-shield-check"></i>
                  <span>Contenido oficial de tu comunidad.</span>
                </div>
              </aside>
            </div>
          </div>

          <div class="modal-footer ev-com-publish-footer">
            <button type="button" class="ev-com-btn ev-com-btn-light" id="btnCancelarFormularioCom">
              <i class="bi bi-x-circle"></i> Cancelar
            </button>
            <button type="button" class="ev-com-btn ev-com-btn-outline" id="btnGuardarBorradorCom">
              <i class="bi bi-save"></i> Guardar borrador
            </button>
            <button type="button" class="ev-com-btn ev-com-btn-primary" id="btnPublicarCom">
              <i class="bi bi-send-check"></i> Publicar ahora
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</section>

<div
  class="modal fade ev-com-history-modal"
  id="modalHistorialCom"
  tabindex="-1"
  aria-labelledby="tituloModalHistorialCom"
  aria-hidden="true"
  data-bs-backdrop="static"
  data-bs-keyboard="false"
>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content ev-com-history-modal-content">
      <header class="ev-com-history-header">
        <div class="ev-com-history-header-top">
          <div class="ev-com-history-heading">
            <span class="ev-com-history-heading-icon" aria-hidden="true">
              <i class="bi bi-clock-history"></i>
            </span>
            <div class="ev-com-history-heading-copy">
              <span class="ev-com-history-eyebrow">Trazabilidad administrativa</span>
              <h5 class="modal-title" id="tituloModalHistorialCom">Historial de publicación</h5>
              <!--<p>Registro de cambios y acciones realizadas.</p>-->
            </div>
          </div>

          <button
            type="button"
            class="ev-com-history-close"
            data-bs-dismiss="modal"
            aria-label="Cerrar historial"
          >
            <i class="bi bi-x-lg" aria-hidden="true"></i>
          </button>
        </div>

        <!--<div class="ev-com-history-context" aria-label="Publicación consultada">
          <div class="ev-com-history-context-main">
            <span class="ev-com-history-type ev-com-history-type--comunicado" id="tipoHistorialCom">Comunicado</span>

            <div class="ev-com-history-publication">
              <span>Publicación</span>
              <strong id="tituloHistorialCom" title="">—</strong>
            </div>
          </div>

          <span class="ev-com-history-current ev-com-history-current--borrador" id="estadoHistorialCom">Borrador</span>
        </div>-->
      </header>

      <div class="modal-body ev-com-history-body">
        <div class="ev-com-history-summary">
          <div>
            <strong id="totalMovimientosHistorialCom">0</strong>
            <span id="textoMovimientosHistorialCom">movimientos registrados</span>
          </div>
          <small>Del cambio más reciente al más antiguo</small>
        </div>

        <div id="listaHistorialCom" class="ev-com-history" aria-live="polite"></div>
      </div>
    </div>
  </div>
</div>

<script src="<?= htmlspecialchars($baseUrlVista . '/views/js/comunidadGestion.js?v=' . $jsVersion, ENT_QUOTES, 'UTF-8') ?>"></script>
