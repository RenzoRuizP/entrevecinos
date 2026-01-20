<?php
// views/menuPrincipalContenidoSoporte.php
// Dashboard exclusivo para rol: soporte
// Requiere: $nombreUsuario (ya definido en MenuPrincipalView.php)
?>

<div class="container-fluid py-4 ev-soporte-dashboard">

  <!-- HERO -->
  <div class="card shadow-sm border-0 rounded-4 p-4 mb-4 ev-soporte-hero">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div>
        <div class="d-flex align-items-center gap-3 mb-1">
          <span class="ev-soporte-icon">
            <i class="bi bi-tools"></i>
          </span>
          <h3 class="fw-bold mb-0" style="color:#0F592F;">Panel de Soporte</h3>
        </div>
        <p class="text-muted mb-0">
          Hola, <strong><?= $nombreUsuario ?></strong>. Gestiona solicitudes y atención a vecinos.
        </p>
      </div>

      <div class="ev-soporte-chip">
        <i class="bi bi-shield-check"></i>
        <span>Rol: Soporte</span>
      </div>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row g-4 mb-4">

    <!-- Cuentas -->
    <div class="col-12 col-lg-4">
      <div class="card shadow-sm border-0 rounded-4 p-3 h-100 ev-kpi-card">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <span class="ev-kpi-ico ev-kpi-ico-cuentas"><i class="bi bi-person-check"></i></span>
            <h6 class="fw-bold mb-0" style="color:#0F592F;">Cuentas</h6>
          </div>
          <button type="button" class="btn btn-sm ev-icon-btn" aria-label="Opciones">
            <i class="bi bi-three-dots"></i>
          </button>
        </div>

        <div class="d-flex flex-column gap-2">
          <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="bi bi-hourglass-split me-2"></i>Pendientes</small>
            <span class="fw-bold ev-num ev-num-warn" id="kpiCuentasPend">0</span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="bi bi-check2-circle me-2"></i>Aprobadas hoy</small>
            <span class="fw-bold ev-num ev-num-ok" id="kpiCuentasAprob">0</span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="bi bi-x-circle me-2"></i>Rechazadas</small>
            <span class="fw-bold ev-num ev-num-bad" id="kpiCuentasRech">0</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Publicaciones -->
    <div class="col-12 col-lg-4">
      <div class="card shadow-sm border-0 rounded-4 p-3 h-100 ev-kpi-card">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <span class="ev-kpi-ico ev-kpi-ico-pub"><i class="bi bi-megaphone"></i></span>
            <h6 class="fw-bold mb-0" style="color:#0F592F;">Publicaciones</h6>
          </div>
          <button type="button" class="btn btn-sm ev-icon-btn" aria-label="Opciones">
            <i class="bi bi-three-dots"></i>
          </button>
        </div>

        <div class="d-flex flex-column gap-2">
          <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="bi bi-search me-2"></i>En revisión</small>
            <span class="fw-bold ev-num ev-num-warn" id="kpiPubRevision">0</span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="bi bi-exclamation-triangle me-2"></i>Reportadas</small>
            <span class="fw-bold ev-num ev-num-bad" id="kpiPubReport">0</span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="bi bi-slash-circle me-2"></i>Suspendidas</small>
            <span class="fw-bold ev-num ev-num-purple" id="kpiPubSusp">0</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recargas -->
    <div class="col-12 col-lg-4">
      <div class="card shadow-sm border-0 rounded-4 p-3 h-100 ev-kpi-card">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <span class="ev-kpi-ico ev-kpi-ico-rec"><i class="bi bi-wallet2"></i></span>
            <h6 class="fw-bold mb-0" style="color:#0F592F;">Recargas</h6>
          </div>
          <button type="button" class="btn btn-sm ev-icon-btn" aria-label="Opciones">
            <i class="bi bi-three-dots"></i>
          </button>
        </div>

        <div class="d-flex flex-column gap-2">
          <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="bi bi-hourglass-split me-2"></i>Pendientes validación</small>
            <span class="fw-bold ev-num ev-num-warn" id="kpiRecPend">0</span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="bi bi-check2-circle me-2"></i>Validadas hoy</small>
            <span class="fw-bold ev-num ev-num-ok" id="kpiRecVal">0</span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted"><i class="bi bi-chat-left-text me-2"></i>Observadas</small>
            <span class="fw-bold ev-num ev-num-muted" id="kpiRecObs">0</span>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- ATENDER AHORA -->
  <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
      <h6 class="fw-bold mb-0" style="color:#0F592F;">Atender ahora</h6>

      <select class="form-select form-select-sm ev-select" id="evFiltroTiempo" style="max-width:180px;">
        <option value="hoy" selected>Hoy</option>
        <option value="7d">Últimos 7 días</option>
        <option value="30d">Últimos 30 días</option>
      </select>
    </div>

    <div class="table-responsive">
      <table class="table align-middle mb-0 ev-table">
        <thead>
          <tr>
            <th style="width:160px;">Fecha</th>
            <th>Tipo de atención</th>
            <th class="text-end" style="width:180px;">Acción</th>
          </tr>
        </thead>
        <tbody id="evAtenderAhoraBody">
          <!-- Se llena por JS. Si no hay data, el JS mostrará “No hay solicitudes”. -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- ACCESOS RÁPIDOS -->
  <div class="card shadow-sm border-0 rounded-4 p-3 mt-4">
    <h6 class="fw-bold mb-3" style="color:#0F592F;">Accesos rápidos</h6>

    <div class="row g-3">
      <div class="col-12 col-md-6 col-lg-3">
        <a class="ev-quick" href="<?= rtrim(BASE_URL,'/') ?>/atender-cuentas">
          <span class="ev-quick-ico"><i class="bi bi-person-check"></i></span>
          <div>
            <div class="ev-quick-title">At. Cuentas</div>
            <small class="text-muted">Verificación y estados</small>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <a class="ev-quick" href="<?= rtrim(BASE_URL,'/') ?>/atender-publicacion">
          <span class="ev-quick-ico"><i class="bi bi-megaphone"></i></span>
          <div>
            <div class="ev-quick-title">At. Publicación</div>
            <small class="text-muted">Reportes y moderación</small>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <a class="ev-quick" href="<?= rtrim(BASE_URL,'/') ?>/atender-recargas">
          <span class="ev-quick-ico"><i class="bi bi-wallet2"></i></span>
          <div>
            <div class="ev-quick-title">At. Recargas</div>
            <small class="text-muted">Validación de vouchers</small>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <a class="ev-quick" href="<?= rtrim(BASE_URL,'/') ?>/notificaciones-residencia">
          <span class="ev-quick-ico"><i class="bi bi-bell"></i></span>
          <div>
            <div class="ev-quick-title">Notificaciones</div>
            <small class="text-muted">Solicitudes de residencia</small>
          </div>
        </a>
      </div>
    </div>
  </div>

</div>
