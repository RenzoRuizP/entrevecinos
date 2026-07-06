<?php
// views/AtenderPublicacionView.php
require_once __DIR__ . '/../Config/config.php';
?>

<?php include_once __DIR__ . '/estilos/atenderPublicacionEstilo.php'; ?>

<div class="container-fluid px-3 px-md-4 py-3 ev-ap-page">

  <!-- HERO -->
  <div class="ev-card ev-hero mb-3">
    <div class="ev-hero-body">
      <div class="ev-hero-top">
        <div class="ev-hero-left">
          <h1 class="ev-title mb-1">Atender publicaciones</h1>
          <div class="ev-subtitle">
            Aprueba, observa o rechaza productos y servicios antes de mostrarlos en el Marketplace.
          </div>
        </div>

        <div class="ev-hero-right">
          <button class="ev-icon-btn" type="button" title="Notificaciones" aria-label="Notificaciones">
            <i class="bi bi-bell"></i>
          </button>

          <div class="dropdown">
            <button class="btn ev-btn-orange dropdown-toggle" data-bs-toggle="dropdown" type="button">
              Acciones
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <button class="dropdown-item" type="button" id="btnRefrescar">
                  <i class="bi bi-arrow-clockwise me-2"></i>Refrescar
                </button>
              </li>
              <li>
                <button class="dropdown-item" type="button" id="btnExportar">
                  <i class="bi bi-download me-2"></i>Exportar CSV
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="ev-hero-bottom">
        <div class="ev-summary-pill">
          <span class="ev-summary-label">Pendientes:</span>
          <span class="ev-summary-count" id="lblPendientes">0</span>
        </div>

        <div class="ev-quick-actions">
          <button type="button" class="btn ev-btn-light btn-sm" id="btnVerPendientes">
            <i class="bi bi-hourglass-split me-1"></i> Pendientes
          </button>
          <button type="button" class="btn ev-btn-light btn-sm" id="btnVerAprobadas">
            <i class="bi bi-check-circle me-1"></i> Aprobadas
          </button>
          <button type="button" class="btn ev-btn-light btn-sm" id="btnVerRechazadas">
            <i class="bi bi-x-circle me-1"></i> Rechazadas
          </button>
          <button type="button" class="btn ev-btn-light btn-sm" id="btnVerBorradores">
            <i class="bi bi-pencil-square me-1"></i> Borradores
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- FILTROS -->
  <div class="ev-card ev-filters mb-3">
    <div class="ev-card-header">
      <h2 class="ev-card-title">Filtros</h2>
    </div>

    <div class="ev-card-body">
      <form id="formFiltros" class="row g-3 align-items-end">
        <div class="col-12 col-lg-3">
          <label class="form-label">Estado</label>
          <select class="form-select ev-input" id="fEstado" name="estado">
            <option value="pendiente" selected>Pendiente</option>
            <option value="aprobada">Aprobada</option>
            <option value="rechazada">Rechazada</option>
            <option value="borrador">Borrador</option>
            <option value="todas">Todas</option>
          </select>
        </div>

        <div class="col-12 col-lg-7">
          <label class="form-label">Buscar</label>
          <div class="position-relative">
            <i class="bi bi-search ev-input-icon"></i>
            <input
              type="text"
              class="form-control ev-input ps-5"
              id="fTexto"
              name="q"
              placeholder="Buscar por título, descripción, vecino, producto o servicio..."
              autocomplete="off"
            />
          </div>
        </div>

        <div class="col-12 col-lg-2 d-grid">
          <button class="btn ev-btn-orange" type="submit">
            <i class="bi bi-search me-1"></i> Buscar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- TABLA -->
  <div class="ev-card">
    <div class="ev-card-header ev-card-header-row">
      <h2 class="ev-card-title mb-0">Publicaciones</h2>
      <div class="ev-table-meta" id="lblMeta">Mostrando 0 registros</div>
    </div>

    <div class="ev-table-wrap">
      <div class="ev-table-frame">
        <div class="table-responsive">
          <table class="table ev-table mb-0">
            <thead>
              <tr>
                <th class="ev-col-fecha">Fecha</th>
                <th class="ev-col-publicacion">Publicación</th>
                <th class="ev-col-titulo">Título</th>
                <th class="text-end ev-col-precio">Precio</th>
                <th class="ev-col-usuario">Usuario</th>
                <th class="ev-col-estado">Estado</th>
                <th class="text-end ev-col-acciones">Acciones</th>
              </tr>
            </thead>
            <tbody id="tbodyItems">
              <tr>
                <td colspan="7" class="text-center py-4 ev-empty">
                  <div class="ev-empty-wrap">
                    <i class="bi bi-inbox ev-empty-ico"></i>
                    <div class="ev-empty-text">No hay publicaciones para los filtros seleccionados.</div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="ev-card-footer">
      <div class="ev-footer-left" id="lblFooterLeft">Mostrando 0 de 0</div>
      <div class="ev-footer-right">
        <button class="btn ev-btn-light btn-sm" id="btnPrev" type="button" disabled>Anterior</button>
        <span class="ev-page-pill" id="lblPagina">1</span>
        <button class="btn ev-btn-light btn-sm" id="btnNext" type="button" disabled>Siguiente</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: REVISAR PUBLICACIÓN -->
<div
  class="modal fade ev-ap-modal"
  id="modalPub"
  tabindex="-1"
  aria-hidden="true"
  data-bs-backdrop="static"
  data-bs-keyboard="false"
>
  <div class="modal-dialog modal-dialog-centered modal-xl ev-ap-modal-dialog">
    <div class="modal-content ev-ap-modal-content">

      <div class="modal-header ev-ap-modal-header">
        <h5 class="modal-title ev-ap-modal-title">
          <i class="bi bi-clipboard-check"></i>
          <span>Revisar publicación</span>
        </h5>

        <button
          type="button"
          class="btn-close btn-close-white ev-ap-modal-close"
          data-bs-dismiss="modal"
          aria-label="Cerrar"
        ></button>
      </div>

      <div class="ev-ap-modal-flex">
        <div class="modal-body ev-ap-modal-body-scroll">
          <div class="ev-ap-review-grid">

            <!-- COLUMNA IZQUIERDA: REVISIÓN -->
            <div class="ev-ap-review-form-col">

              <section class="ev-ap-section ev-ap-data-section">
                <span class="ev-ap-step-badge">Revisión</span>
                <div class="ev-ap-section-heading">
                  <h6 class="ev-ap-section-title">Datos principales</h6>
                  <span id="mEstadoBadge" class="ev-badge ev-badge-pendiente">pendiente</span>
                </div>

                <div class="ev-ap-kv-list">
                  <div class="ev-ap-kv-item">
                    <span>Tipo</span>
                    <strong id="mTipoPublicacion">—</strong>
                  </div>
                  <div class="ev-ap-kv-item">
                    <span>Título</span>
                    <strong id="mTitulo">—</strong>
                  </div>
                  <div class="ev-ap-kv-item">
                    <span>Precio</span>
                    <strong id="mPrecio">—</strong>
                  </div>
                  <div class="ev-ap-kv-item">
                    <span>Usuario</span>
                    <strong id="mUsuario">—</strong>
                  </div>
                  <div class="ev-ap-kv-item">
                    <span>Email</span>
                    <strong id="mEmail">—</strong>
                  </div>
                </div>
              </section>

              <section class="ev-ap-section">
                <span class="ev-ap-step-badge">Comunicación</span>
                <h6 class="ev-ap-section-title">Comentario para el vecino</h6>

                <textarea
                  class="form-control ev-ap-input"
                  id="mComentario"
                  rows="4"
                  placeholder="Ej. Hola, revisamos tu publicación y necesitamos que corrijas la imagen principal porque no se aprecia bien."
                ></textarea>

                <div class="ev-ap-form-hint">
                  Este mensaje se mostrará al vecino. Usa un tono claro, respetuoso y específico.
                </div>
              </section>

              <section class="ev-ap-section">
                <span class="ev-ap-step-badge">Contenido</span>
                <h6 class="ev-ap-section-title">Descripción</h6>
                <div class="ev-ap-content-box" id="mDescripcion">—</div>
              </section>

              <section class="ev-ap-section">
                <span class="ev-ap-step-badge">Historial</span>
                <h6 class="ev-ap-section-title">Último mensaje registrado</h6>
                <div class="ev-ap-content-box ev-ap-content-box-soft" id="mUltimoComentario">
                  Sin mensaje registrado.
                </div>
              </section>
            </div>

            <!-- COLUMNA DERECHA: IMÁGENES -->
            <aside class="ev-ap-review-preview-col">
              <div class="ev-ap-review-preview-sticky">
                <section class="ev-ap-preview-panel">
                  <div class="ev-ap-preview-panel-head">
                    <div>
                      <div class="ev-ap-preview-kicker">Vista previa</div>
                      <div class="ev-ap-preview-heading">
                        <i class="bi bi-image me-1"></i> Imágenes de la publicación
                      </div>
                    </div>
                  </div>

                  <div class="ev-ap-preview-area">
                    <div id="mGaleria" class="ev-ap-galeria"></div>
                    <div id="mNoImgs" class="ev-ap-proof-empty">No hay imágenes disponibles.</div>
                  </div>

                  <div class="ev-ap-preview-tip">
                    <i class="bi bi-shield-check"></i>
                    <span>
                      Verifica que el contenido sea claro, corresponda a la publicación y cumpla las políticas de Entre Vecinos.
                    </span>
                  </div>
                </section>
              </div>
            </aside>
          </div>
        </div>

        <div class="modal-footer ev-ap-modal-footer">
          <button type="button" class="btn ev-ap-btn-outline" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cerrar
          </button>

          <div class="ev-ap-modal-actions">
            <button type="button" class="btn ev-ap-btn-danger" id="btnRechazar">
              <i class="bi bi-x-circle me-1"></i> Rechazar
            </button>
            <button type="button" class="btn ev-ap-btn-warning" id="btnObservar">
              <i class="bi bi-exclamation-circle me-1"></i> Observar
            </button>
            <button type="button" class="btn ev-ap-btn-success" id="btnAprobar">
              <i class="bi bi-check-circle me-1"></i> Aprobar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
