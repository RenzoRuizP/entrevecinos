<?php
require_once __DIR__ . '/../Config/config.php';
?>
<?php include_once __DIR__ . '/estilos/gestionRetirosEstilo.php'; ?>

<section
  class="container-fluid ev-withdraw-admin fade-in"
  id="evGestionRetiros"
  data-admin="<?= (($evRetirosEsAdmin ?? false) === true) ? '1' : '0' ?>"
  data-soporte="<?= (($evRetirosEsSoporte ?? false) === true) ? '1' : '0' ?>"
  data-csrf="<?= htmlspecialchars((string)($evRetirosCsrf ?? ''), ENT_QUOTES, 'UTF-8') ?>"
  aria-labelledby="evWithdrawAdminTitle"
>
  <header class="ev-withdraw-admin-hero">
    <div class="ev-withdraw-admin-title-wrap">
      <span class="ev-withdraw-admin-icon" aria-hidden="true"><i class="bi bi-bank"></i></span>
      <div>
        <div class="ev-withdraw-admin-kicker"><?= (($evRetirosEsAdmin ?? false) === true) ? 'ADMINISTRACIÓN · RETIROS' : 'SOPORTE · RETIROS' ?></div>
        <h2 id="evWithdrawAdminTitle"><?= (($evRetirosEsAdmin ?? false) === true) ? 'Gestión de retiros' : 'Consulta de retiros' ?></h2>
        <p><?= (($evRetirosEsAdmin ?? false) === true)
          ? 'Controla cuentas bancarias, cortes y pagos de los retiros solicitados por los vecinos.'
          : 'Consulta el estado de los retiros para atender incidencias. Esta vista es de solo lectura.' ?></p>
      </div>
    </div>
    <?php if (($evRetirosEsSoporte ?? false) === true && ($evRetirosEsAdmin ?? false) !== true): ?>
      <span class="ev-withdraw-admin-readonly"><i class="bi bi-eye"></i> Solo lectura</span>
    <?php endif; ?>
  </header>

  <section class="ev-withdraw-admin-summary" aria-label="Resumen de retiros">
    <article class="is-requested">
      <span class="ev-withdraw-summary-icon" aria-hidden="true"><i class="bi bi-hourglass-split"></i></span>
      <div><span>Solicitados</span><strong id="evRetSumSolicitados">0</strong><small>Esperando cierre de corte</small></div>
    </article>
    <article class="is-scheduled">
      <span class="ev-withdraw-summary-icon" aria-hidden="true"><i class="bi bi-calendar2-check"></i></span>
      <div><span>Programados</span><strong id="evRetSumProgramados">0</strong><small>Listos para liquidación</small></div>
    </article>
    <article class="is-observed">
      <span class="ev-withdraw-summary-icon" aria-hidden="true"><i class="bi bi-exclamation-circle"></i></span>
      <div><span>Observados</span><strong id="evRetSumObservados">0</strong><small>Requieren revisión</small></div>
    </article>
    <article class="is-money">
      <span class="ev-withdraw-summary-icon" aria-hidden="true"><i class="bi bi-cash-stack"></i></span>
      <div><span>Monto pendiente</span><strong id="evRetSumMonto">S/ 0.00</strong><small>Reservado para pago</small></div>
    </article>
  </section>

  <section class="ev-withdraw-admin-workspace">
    <header class="ev-withdraw-admin-workspace-head">
      <nav class="ev-withdraw-admin-tabs" aria-label="Secciones de retiros">
        <button type="button" class="is-active" data-ret-tab="retiros"><i class="bi bi-wallet2" aria-hidden="true"></i><span>Retiros</span></button>
        <?php if (($evRetirosEsAdmin ?? false) === true): ?>
          <button type="button" data-ret-tab="cuentas"><i class="bi bi-bank" aria-hidden="true"></i><span>Cuentas bancarias</span></button>
          <button type="button" data-ret-tab="cortes"><i class="bi bi-calendar3" aria-hidden="true"></i><span>Configuración de cortes</span></button>
        <?php endif; ?>
      </nav>
      <button type="button" class="ev-withdraw-refresh" id="btnRefrescarRetiros" aria-label="Actualizar"><i class="bi bi-arrow-clockwise"></i></button>
    </header>

    <div class="ev-withdraw-admin-body">
      <section class="ev-withdraw-panel is-active" data-ret-panel="retiros">
        <div class="ev-withdraw-section-head ev-withdraw-section-head--feature">
          <div class="ev-withdraw-section-title">
            <span class="ev-withdraw-section-icon" aria-hidden="true"><i class="bi bi-cash-coin"></i></span>
            <div>
              <h3>Liquidación de retiros</h3>
              <p>Consulta solicitudes por jornada, fecha de pago y estado. Los importes programados ya tienen saldo reservado para su liquidación.</p>
            </div>
          </div>
          <span class="ev-withdraw-section-chip">Pagos martes y viernes</span>
        </div>
        <div class="ev-withdraw-toolbar">
          <div class="ev-withdraw-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="evRetSearch" class="form-control" placeholder="Buscar vendedor, DNI o código de retiro">
          </div>
          <div class="ev-withdraw-filter-field">
            <label for="evRetFechaPago">Fecha de pago</label>
            <input type="date" id="evRetFechaPago" class="form-control" aria-label="Filtrar por fecha de pago">
          </div>
          <div class="ev-withdraw-filter-field">
            <label for="evRetEstado">Estado</label>
            <select id="evRetEstado" class="form-select" aria-label="Filtrar estado">
              <option value="">Todos los estados</option>
              <option value="solicitado">Solicitado</option>
              <option value="programado">Programado</option>
              <option value="observado">Observado</option>
              <option value="pagado">Pagado</option>
              <option value="cancelado">Cancelado</option>
              <option value="sin_saldo">Sin saldo</option>
            </select>
          </div>
        </div>
        <div id="evRetLista" class="ev-withdraw-table-wrap">
          <div class="ev-withdraw-loading">Cargando retiros...</div>
        </div>
      </section>

      <?php if (($evRetirosEsAdmin ?? false) === true): ?>
      <section class="ev-withdraw-panel" data-ret-panel="cuentas">
        <div class="ev-withdraw-section-head ev-withdraw-section-head--feature">
          <div class="ev-withdraw-section-title">
            <span class="ev-withdraw-section-icon" aria-hidden="true"><i class="bi bi-person-check"></i></span>
            <div><h3>Validación de cuentas bancarias</h3><p>Revisa titularidad, banco, cuenta y CCI antes de habilitar retiros para el vecino.</p></div>
          </div>
          <span class="ev-withdraw-section-chip">Validación administrativa</span>
        </div>
        <div id="evRetCuentas" class="ev-withdraw-table-wrap"><div class="ev-withdraw-loading">Cargando cuentas...</div></div>
      </section>

      <section class="ev-withdraw-panel" data-ret-panel="cortes">
        <div class="ev-withdraw-section-head ev-withdraw-section-head--feature">
          <div class="ev-withdraw-section-title">
            <span class="ev-withdraw-section-icon" aria-hidden="true"><i class="bi bi-calendar2-week"></i></span>
            <div><h3>Ventanas de corte</h3><p>Define el inicio y fin de cada ventana. En el piloto, los pagos se mantienen los martes y viernes y S/ 20.00 permanece en la billetera.</p></div>
          </div>
          <span class="ev-withdraw-section-chip">Zona horaria: America/Lima</span>
        </div>
        <div id="evRetCortes" class="ev-withdraw-cut-grid"><div class="ev-withdraw-loading">Cargando configuración...</div></div>
      </section>
      <?php endif; ?>
    </div>
  </section>
</section>

<div class="modal fade ev-modal ev-modal-login" id="modalDetalleRetiro" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content ev-modal-content">
      <div class="modal-header ev-login-modal-header">
        <h5 class="modal-title mb-0">Detalle del retiro</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body ev-login-modal-body" id="evRetDetalleBody"></div>
      <div class="modal-footer ev-login-modal-footer d-none" id="evRetDetalleFooter"></div>
    </div>
  </div>
</div>

<?php if (($evRetirosEsAdmin ?? false) === true): ?>
<div class="modal fade ev-modal ev-modal-login" id="modalPagoRetiro" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content ev-modal-content">
      <div class="modal-header ev-login-modal-header">
        <h5 class="modal-title mb-0">Registrar pago</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body ev-login-modal-body">
        <form id="formPagoRetiro">
          <input type="hidden" id="pagoRetiroId">
          <div class="mb-3"><label class="form-label" for="pagoRetiroOperacion">Número de operación</label><input class="form-control" id="pagoRetiroOperacion" maxlength="100" required></div>
          <div><label class="form-label" for="pagoRetiroComprobante">Comprobante</label><input type="file" class="form-control" id="pagoRetiroComprobante" accept="image/jpeg,image/png,image/webp,application/pdf" required><div class="form-text">JPG, PNG, WEBP o PDF. Máximo 5 MB.</div></div>
        </form>
      </div>
      <div class="modal-footer ev-login-modal-footer">
        <button type="button" class="btn ev-btn-modal-outline" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn ev-btn-modal-primary" id="btnConfirmarPagoRetiro">Registrar pago</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="<?= rtrim(BASE_URL, '/') ?>/views/js/gestionRetiros.js?v=<?= rawurlencode((string)(defined('EV_APP_VER') ? EV_APP_VER : '1.0.0')) ?>"></script>
