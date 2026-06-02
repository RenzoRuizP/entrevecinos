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

  <!-- MODAL: NUEVA / EDITAR PUBLICACIÓN -->
  <div
    class="modal fade ev-com-editor-modal"
    id="modalPublicacionCom"
    tabindex="-1"
    aria-labelledby="evComFormTitle"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
  >
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down">
      <div class="modal-content ev-com-modal ev-com-modal-editor">
        <div class="modal-header ev-com-modal-editor-head">
          <div class="ev-com-modal-heading">
            <span class="ev-com-modal-kicker"><i class="bi bi-megaphone"></i> Comunidad</span>
            <h2 class="modal-title" id="evComFormTitle">Nueva publicación</h2>
            <p>Completa el contenido y decide si deseas guardarlo como borrador o publicarlo ahora.</p>
          </div>
          <button type="button" class="btn-close btn-close-white" id="btnCerrarFormularioCom" aria-label="Cerrar"></button>
        </div>

        <form id="formComunidadPublicacion" enctype="multipart/form-data" autocomplete="off" novalidate>
          <input type="hidden" id="codigoPublicacionCom" value="">
          <input type="hidden" id="tipoConjuntoCom" name="tipo_conjunto" value="">
          <input type="hidden" id="codigoComunidadCom" name="codigo_comunidad" value="">

          <div class="modal-body ev-com-modal-editor-body">
            <div class="ev-com-assigned-pill">
              <i class="bi bi-house-heart-fill"></i>
              <span><?= htmlspecialchars($nombreComunidadVisual, ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <div class="ev-com-form-meta">
              <div class="ev-com-field">
                <label for="tipoPublicacionCom">Tipo de publicación <span>*</span></label>
                <select id="tipoPublicacionCom" name="tipo_publicacion" required>
                  <option value="comunicado">Comunicado</option>
                  <option value="noticia">Noticia</option>
                  <option value="evento">Evento</option>
                </select>
              </div>

              <div class="ev-com-field" id="campoDestinoCom" <?= $esAdminSistemaVista ? '' : 'hidden' ?>>
                <label for="destinoCom">Comunidad destino <span>*</span></label>
                <select id="destinoCom">
                  <option value="">Seleccionar comunidad</option>
                </select>
              </div>

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
                <small>Opcional. Dejará de priorizarse después de esta fecha.</small>
              </div>
            </div>

            <div class="ev-com-form-grid ev-com-form-content-grid">
              <div class="ev-com-field ev-com-field-full">
                <label for="tituloCom">Título <span>*</span></label>
                <input type="text" id="tituloCom" name="titulo" maxlength="140" placeholder="Ej. Mantenimiento temporal del acceso principal" required>
                <small><span id="tituloCharsCom">0</span>/140 caracteres</small>
              </div>

              <div class="ev-com-field ev-com-field-full">
                <label for="resumenCom">Resumen corto <span>*</span></label>
                <textarea id="resumenCom" name="resumen" rows="2" maxlength="240" placeholder="Texto breve que se mostrará en las tarjetas de Comunidad y en el futuro inicio del vecino." required></textarea>
                <small><span id="resumenCharsCom">0</span>/240 caracteres</small>
              </div>

              <div class="ev-com-field ev-com-field-full">
                <label for="contenidoCom">Contenido <span>*</span></label>
                <textarea id="contenidoCom" name="contenido" rows="6" placeholder="Redacta la información completa que recibirán los vecinos." required></textarea>
              </div>
            </div>

            <fieldset class="ev-com-event-fields d-none" id="camposEventoCom">
              <legend><i class="bi bi-calendar-event"></i> Datos del evento</legend>
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

            <div class="ev-com-media-row">
              <div class="ev-com-field ev-com-upload">
                <label for="imagenPortadaCom">Imagen de portada</label>
                <label class="ev-com-file-drop" for="imagenPortadaCom">
                  <i class="bi bi-image"></i>
                  <strong>Seleccionar imagen de portada</strong>
                  <small>JPG, PNG o WEBP · Máximo 2 MB</small>
                </label>
                <input class="ev-com-file-input" type="file" id="imagenPortadaCom" name="imagen_portada" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp">
              </div>

              <div class="ev-com-preview" id="portadaPreviewWrapCom" hidden>
                <img id="portadaPreviewCom" src="" alt="Vista previa de portada">
                <span>Portada seleccionada</span>
              </div>

              <label class="ev-com-check">
                <input type="checkbox" id="destacadoCom" name="destacado_dashboard" value="1">
                <span>
                  <strong>Mostrar como destacado en el inicio</strong>
                  <small>Este contenido tendrá mayor visibilidad para los vecinos.</small>
                </span>
              </label>
            </div>
          </div>

          <div class="modal-footer ev-com-modal-editor-footer">
            <button type="button" class="ev-com-btn ev-com-btn-light" id="btnCancelarFormularioCom">Cancelar</button>
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

<div class="modal fade" id="modalHistorialCom" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content ev-com-modal">
      <div class="modal-header">
        <div>
          <h5 class="modal-title"><i class="bi bi-clock-history"></i> Historial de publicación</h5>
          <small id="tituloHistorialCom">—</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="listaHistorialCom" class="ev-com-history"></div>
      </div>
    </div>
  </div>
</div>

<script src="<?= htmlspecialchars($baseUrlVista . '/views/js/comunidadGestion.js?v=' . $jsVersion, ENT_QUOTES, 'UTF-8') ?>"></script>
