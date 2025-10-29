<?php 
require_once __DIR__ . '/../Config/config.php';
?>

<div class="ev-publist fade-in">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-header d-flex align-items-center justify-content-between ev-pub-header rounded-top-4">
      <h5 class="mb-0 d-flex align-items-center gap-2 text-white">
        <i class="bi bi-basket2-fill"></i>
        Publicaciones
      </h5>
      <div class="d-flex align-items-center gap-2">
        <button type="button" id="btnBuscarPublicacion" class="btn btn-ev-outline d-flex align-items-center gap-2">
          <i class="bi bi-search"></i>
          <span class="d-none d-sm-inline">Buscar</span>
        </button>
        <button type="button" id="btnAgregarPublicacion" class="btn btn-ev-primary d-flex align-items-center gap-2">
          <i class="bi bi-plus-lg"></i>
          <span class="d-none d-sm-inline">Agregar</span>
        </button>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 ev-pub-table">
          <thead>
            <tr>
              <th style="width:120px">Código</th>
              <th style="width:220px">Código Genérico</th>
              <th>Descripción</th>
              <th style="width:130px" class="text-center">Unidades</th>
              <th style="width:200px" class="text-center">Opciones</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td data-label="Código"><span class="ev-code">000001</span></td>
              <td data-label="Código Genérico"><span class="ev-badge">ATB000001</span></td>
              <td data-label="Descripción">Amoxicilina 500 mg cápsula</td>
              <td data-label="Unidades" class="text-center"><span class="ev-chip">CAPS</span></td>
              <td data-label="Opciones" class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <button class="btn btn-sm btn-ev-primary"><i class="bi bi-pencil-square me-1"></i>Editar</button>
                  <button class="btn btn-sm btn-ev-danger"><i class="bi bi-x-octagon me-1"></i>Anular</button>
                </div>
              </td>
            </tr>
            <tr>
              <td data-label="Código"><span class="ev-code">000002</span></td>
              <td data-label="Código Genérico"><span class="ev-badge">ANA000001</span></td>
              <td data-label="Descripción">Paracetamol 500 mg tableta</td>
              <td data-label="Unidades" class="text-center"><span class="ev-chip">TABL</span></td>
              <td data-label="Opciones" class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <button class="btn btn-sm btn-ev-primary"><i class="bi bi-pencil-square me-1"></i>Editar</button>
                  <button class="btn btn-sm btn-ev-danger"><i class="bi bi-x-octagon me-1"></i>Anular</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3">
        <div class="d-flex align-items-center gap-2 small text-muted">
          Registros por página:
          <select class="form-select form-select-sm ev-select">
            <option>10</option><option>20</option><option>50</option>
          </select>
        </div>

        <div class="small text-muted">1–2 de 2</div>

        <div class="btn-group">
          <button class="btn btn-sm btn-ev-ghost" title="Primero"><i class="bi bi-chevron-bar-left"></i></button>
          <button class="btn btn-sm btn-ev-ghost" title="Anterior"><i class="bi bi-chevron-left"></i></button>
          <button class="btn btn-sm btn-ev-ghost" title="Siguiente"><i class="bi bi-chevron-right"></i></button>
          <button class="btn btn-sm btn-ev-ghost" title="Último"><i class="bi bi-chevron-bar-right"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/estilos/PublicacionesEstilo.php'; ?>
