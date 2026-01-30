<?php
// views/soporteDashboardView.php
// Vista parcial (contenido) para el dashboard del rol "soporte"
// Compatible con tu Shell MenuPrincipalView.php (AJAX / include)

if (!defined('BASE_URL')) {
  // fallback por si se abre aislado (no debería en tu flujo)
  define('BASE_URL', '/');
}

$baseUrl = rtrim(BASE_URL, '/');

// Fallback si no viene $nombreUsuario desde MenuPrincipalView.php
$nombreUsuarioSafe = 'Soporte';
if (isset($nombreUsuario) && is_string($nombreUsuario) && trim($nombreUsuario) !== '') {
  $nombreUsuarioSafe = $nombreUsuario; // ya viene escapado en MenuPrincipalView.php
}
?>

<div class="container-fluid py-4 ev-soporte-dashboard">

  <!-- HERO -->
  <div class="card ev-card ev-soporte-hero mb-4">
    <div class="ev-hero-body">
      <div class="ev-hero-top">
        <div class="d-flex align-items-center gap-3">
          <span class="ev-icon-btn" aria-hidden="true">
            <i class="bi bi-tools"></i>
          </span>

          <div>
            <h2 class="ev-recargas-title mb-1">Panel de Soporte</h2>
            <div class="ev-recargas-subtitle">
              Hola, <strong><?= $nombreUsuarioSafe ?></strong>. Gestión de solicitudes y atención a vecinos.
            </div>
          </div>
        </div>

        <div class="ev-hero-right">
          <!-- Badge rol (sin barra) -->
          <span class="ev-role-badge" title="Rol activo">
            <i class="bi bi-shield-check"></i>
            Rol: Soporte
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row g-4 mb-4">

    <!-- Cuentas -->
    <div class="col-12 col-lg-4">
      <div class="ev-card p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <span class="ev-kpi-ico" aria-hidden="true">
              <i class="bi bi-person-check"></i>
            </span>
            <h6 class="ev-card-title mb-0">Cuentas</h6>
          </div>

          <a data-ev-nav="1" href="<?= $baseUrl ?>/atender-cuentas" class="ev-icon-btn" aria-label="Ir a Atención de Cuentas">
            <i class="bi bi-arrow-right"></i>
          </a>
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

        <div class="mt-3">
          <a data-ev-nav="1" class="ev-btn-orange w-100 text-center d-block" href="<?= $baseUrl ?>/atender-cuentas">
            Ver solicitudes
          </a>
        </div>
      </div>
    </div>

    <!-- Publicaciones -->
    <div class="col-12 col-lg-4">
      <div class="ev-card p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <span class="ev-kpi-ico" aria-hidden="true">
              <i class="bi bi-megaphone"></i>
            </span>
            <h6 class="ev-card-title mb-0">Publicaciones</h6>
          </div>

          <a data-ev-nav="1" href="<?= $baseUrl ?>/atender-publicacion" class="ev-icon-btn" aria-label="Ir a Atención de Publicación">
            <i class="bi bi-arrow-right"></i>
          </a>
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

        <div class="mt-3">
          <a data-ev-nav="1" class="ev-btn-orange w-100 text-center d-block" href="<?= $baseUrl ?>/atender-publicacion">
            Ver solicitudes
          </a>
        </div>
      </div>
    </div>

    <!-- Recargas -->
    <div class="col-12 col-lg-4">
      <div class="ev-card p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <span class="ev-kpi-ico" aria-hidden="true">
              <i class="bi bi-wallet2"></i>
            </span>
            <h6 class="ev-card-title mb-0">Recargas</h6>
          </div>

          <a data-ev-nav="1" href="<?= $baseUrl ?>/atender-recargas" class="ev-icon-btn" aria-label="Ir a Atención de Recargas">
            <i class="bi bi-arrow-right"></i>
          </a>
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

        <div class="mt-3">
          <a data-ev-nav="1" class="ev-btn-orange w-100 text-center d-block" href="<?= $baseUrl ?>/atender-recargas">
            Ver solicitudes
          </a>
        </div>
      </div>
    </div>

  </div>

  <!-- ATENDER AHORA -->
  <div class="ev-card overflow-hidden mb-4">
    <div class="ev-atender-header d-flex align-items-center justify-content-between p-3 border-bottom">
      <h6 class="ev-card-title mb-0">Atender ahora</h6>

      <div class="d-flex align-items-center gap-2">
        <select class="form-select form-select-sm ev-select" id="evFiltroTiempo" style="max-width:190px;">
          <option value="hoy" selected>Hoy</option>
          <option value="7d">Últimos 7 días</option>
          <option value="30d">Últimos 30 días</option>
        </select>
      </div>
    </div>

    <div class="table-responsive ev-table-wrap">
      <table class="table ev-table align-middle mb-0">
        <thead>
          <tr>
            <th class="ev-col-fecha">Fecha</th>
            <th class="ev-col-tipo">Tipo de atención</th>
            <th class="ev-col-accion">Acción</th>
          </tr>
        </thead>

        <tbody id="evAtenderAhoraBody">
          <tr>
            <td colspan="3" class="text-center py-4 ev-empty">
              Cargando solicitudes...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ACCESOS RÁPIDOS -->
  <div class="ev-card p-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h6 class="ev-card-title mb-0">Accesos rápidos</h6>
      <small class="text-muted">Entradas directas a módulos de atención</small>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-6 col-lg-3">
        <a data-ev-nav="1" class="ev-quick" href="<?= $baseUrl ?>/atender-cuentas">
          <span class="ev-quick-ico" aria-hidden="true"><i class="bi bi-person-check"></i></span>
          <div>
            <div class="ev-quick-title">At. Cuentas</div>
            <small class="text-muted">Verificación y estados</small>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <a data-ev-nav="1" class="ev-quick" href="<?= $baseUrl ?>/atender-publicacion">
          <span class="ev-quick-ico" aria-hidden="true"><i class="bi bi-megaphone"></i></span>
          <div>
            <div class="ev-quick-title">At. Publicación</div>
            <small class="text-muted">Reportes y moderación</small>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <a data-ev-nav="1" class="ev-quick" href="<?= $baseUrl ?>/atender-recargas">
          <span class="ev-quick-ico" aria-hidden="true"><i class="bi bi-wallet2"></i></span>
          <div>
            <div class="ev-quick-title">At. Recargas</div>
            <small class="text-muted">Validación de vouchers</small>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <a data-ev-nav="1" class="ev-quick" href="<?= $baseUrl ?>/notificaciones-residencia">
          <span class="ev-quick-ico" aria-hidden="true"><i class="bi bi-bell"></i></span>
          <div>
            <div class="ev-quick-title">Notificaciones</div>
            <small class="text-muted">Solicitudes de residencia</small>
          </div>
        </a>
      </div>
    </div>
  </div>

</div>

<script>
window.BASE_URL = window.BASE_URL || "<?= $baseUrl ?>";
</script>
