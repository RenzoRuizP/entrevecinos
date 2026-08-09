<?php
declare(strict_types=1);
require_once __DIR__ . '/../Config/config.php';
include_once __DIR__ . '/estilos/dashboardGerencialEstilo.php';
?>
<section class="ev-dg-page fade-in" id="evDashboardGerencial" data-csrf="<?= htmlspecialchars((string)($evDashboardCsrf ?? ''), ENT_QUOTES, 'UTF-8') ?>" aria-labelledby="evDgTitle">
  <header class="ev-dg-hero">
    <div class="ev-dg-hero-copy">
      <span class="ev-dg-icon"><i class="bi bi-graph-up-arrow"></i></span>
      <div>
        <div class="ev-dg-kicker">INICIO · INTELIGENCIA DE NEGOCIO</div>
        <h1 id="evDgTitle">Dashboard gerencial</h1>
        <p>Supervisa crecimiento, operaciones, ingresos y cumplimiento de metas de Entre Vecinos con información real de la plataforma.</p>
      </div>
    </div>
    <div class="ev-dg-period-badge">
      <span>Periodo analizado</span>
      <strong id="evDgPeriodoLabel">Cargando…</strong>
      <small id="evDgScopeLabel">Todo Entre Vecinos</small>
    </div>
  </header>

  <section class="ev-dg-filter-card" aria-label="Filtros del dashboard">
    <div class="ev-dg-filter-head">
      <div><i class="bi bi-funnel"></i><strong>Filtros gerenciales</strong><span>Todos los indicadores se actualizan con el mismo alcance.</span></div>
      <button type="button" class="ev-dg-btn ev-dg-btn-light" id="evDgReset"><i class="bi bi-arrow-counterclockwise"></i>Restablecer</button>
    </div>
    <div class="ev-dg-filter-grid">
      <label><span>Periodo</span><select id="evDgPeriodo"><option value="dia">Día</option><option value="mes" selected>Mes</option><option value="semestre">Semestre</option><option value="anio">Año</option><option value="personalizado">Rango personalizado</option></select></label>
      <label id="evDgFechaRefWrap"><span>Fecha de referencia</span><input type="date" id="evDgFechaRef" value="<?= date('Y-m-d') ?>"></label>
      <label class="ev-dg-custom-date" hidden><span>Desde</span><input type="date" id="evDgDesde" value="<?= date('Y-m-01') ?>"></label>
      <label class="ev-dg-custom-date" hidden><span>Hasta</span><input type="date" id="evDgHasta" value="<?= date('Y-m-d') ?>"></label>
      <label><span>Alcance</span><select id="evDgAlcance"><option value="global">Todo Entre Vecinos</option><option value="departamento">Departamento</option><option value="provincia">Provincia</option><option value="distrito">Distrito</option><option value="condominio">Condominio</option><option value="urbanizacion">Urbanización</option></select></label>
      <label id="evDgValorWrap" hidden><span id="evDgValorLabel">Seleccionar</span><select id="evDgValor"><option value="0">-- Seleccionar --</option></select></label>
      <button type="button" class="ev-dg-btn ev-dg-btn-primary" id="evDgAplicar"><i class="bi bi-bar-chart-line"></i>Aplicar filtros</button>
    </div>
  </section>

  <div class="ev-dg-loading" id="evDgLoading"><span></span><strong>Preparando indicadores gerenciales…</strong></div>
  <div class="ev-dg-error" id="evDgError" hidden><i class="bi bi-exclamation-triangle"></i><div><strong>No se pudo generar el dashboard</strong><p id="evDgErrorText">Inténtalo nuevamente.</p></div></div>

  <section class="ev-dg-kpis" id="evDgKpis" aria-label="Indicadores principales">
    <article class="ev-dg-kpi"><span class="ev-dg-kpi-icon"><i class="bi bi-people"></i></span><div><small>Usuarios registrados</small><strong data-kpi="usuarios_registrados">0</strong><em>Altas en el periodo</em></div></article>
    <article class="ev-dg-kpi"><span class="ev-dg-kpi-icon"><i class="bi bi-bag-check"></i></span><div><small>Ventas completadas</small><strong data-kpi="ventas_productos">0</strong><em data-kpi-money="monto_ventas_productos">S/ 0.00 vendidos</em></div></article>
    <article class="ev-dg-kpi"><span class="ev-dg-kpi-icon"><i class="bi bi-briefcase"></i></span><div><small>Servicios contratados</small><strong data-kpi="servicios_contratados">0</strong><em data-kpi-money="monto_servicios">S/ 0.00 acordados</em></div></article>
    <article class="ev-dg-kpi ev-dg-kpi-income"><span class="ev-dg-kpi-icon"><i class="bi bi-cash-stack"></i></span><div><small>Ingreso EV</small><strong data-kpi-currency="ingreso_total">S/ 0.00</strong><em>Comisiones e ingresos por servicios</em></div></article>
    <article class="ev-dg-kpi"><span class="ev-dg-kpi-icon"><i class="bi bi-percent"></i></span><div><small>Comisiones de productos</small><strong data-kpi-currency="comisiones_productos">S/ 0.00</strong><em>Comisiones cobradas</em></div></article>
    <article class="ev-dg-kpi"><span class="ev-dg-kpi-icon"><i class="bi bi-megaphone"></i></span><div><small>Publicación de servicios</small><strong data-kpi-currency="ingresos_publicacion_servicios">S/ 0.00</strong><em>Ingresos registrados</em></div></article>
    <article class="ev-dg-kpi"><span class="ev-dg-kpi-icon"><i class="bi bi-buildings"></i></span><div><small>Comunidades activas</small><strong data-kpi="comunidades_activas">0</strong><em>Según el alcance seleccionado</em></div></article>
    <article class="ev-dg-kpi"><span class="ev-dg-kpi-icon"><i class="bi bi-receipt"></i></span><div><small>Ticket promedio</small><strong data-kpi-currency="ticket_promedio_producto">S/ 0.00</strong><em data-kpi-money="ticket_promedio_servicio">Servicio: S/ 0.00</em></div></article>
  </section>

  <section class="ev-dg-grid ev-dg-grid-charts">
    <article class="ev-dg-card ev-dg-chart-card">
      <header><div><span class="ev-dg-card-icon"><i class="bi bi-activity"></i></span><div><h2>Evolución operativa</h2><p>Usuarios, ventas completadas y servicios contratados.</p></div></div></header>
      <div class="ev-dg-chart-legend"><span><i style="--legend:#0E7A43"></i>Usuarios</span><span><i style="--legend:#EA7C12"></i>Ventas</span><span><i style="--legend:#7C3AED"></i>Servicios</span></div>
      <div class="ev-dg-chart"><div class="ev-dg-chart-stage"><canvas id="evDgOperationsChart"></canvas></div><div class="ev-dg-chart-empty" hidden>No hay operaciones en el periodo seleccionado.</div></div>
    </article>
    <article class="ev-dg-card ev-dg-chart-card">
      <header><div><span class="ev-dg-card-icon ev-dg-card-icon-orange"><i class="bi bi-coin"></i></span><div><h2>Ingresos de EV</h2><p>Comisiones de productos e ingresos por publicación de servicios.</p></div></div></header>
      <div class="ev-dg-chart-legend"><span><i style="--legend:#0E7A43"></i>Comisiones de productos</span><span><i style="--legend:#EA7C12"></i>Publicación de servicios</span></div>
      <div class="ev-dg-chart"><div class="ev-dg-chart-stage"><canvas id="evDgIncomeChart"></canvas></div><div class="ev-dg-chart-empty" hidden>No hay ingresos registrados en el periodo.</div></div>
    </article>
  </section>

  <section class="ev-dg-grid ev-dg-grid-goal">
    <article class="ev-dg-card ev-dg-goal-card">
      <header><div><span class="ev-dg-card-icon ev-dg-card-icon-orange"><i class="bi bi-bullseye"></i></span><div><h2>Meta de ingresos</h2><p>Compara el ingreso real con el objetivo del mismo periodo y alcance.</p></div></div><button type="button" class="ev-dg-btn ev-dg-btn-primary ev-dg-btn-goal" id="evDgEditGoal"><i class="bi bi-pencil-square"></i>Definir meta</button></header>
      <div class="ev-dg-goal-main">
        <div class="ev-dg-goal-ring" id="evDgGoalRing"><strong id="evDgGoalPct">0%</strong><span>cumplido</span></div>
        <div class="ev-dg-goal-values">
          <div><small>Ingreso actual</small><strong id="evDgGoalActual">S/ 0.00</strong></div>
          <div><small>Meta</small><strong id="evDgGoalTarget">Sin meta</strong></div>
          <div><small>Falta para alcanzar</small><strong id="evDgGoalMissing">S/ 0.00</strong></div>
        </div>
      </div>
      <div class="ev-dg-progress"><span id="evDgGoalBar"></span></div>
      <p class="ev-dg-setup-note" id="evDgSetupNote" hidden><i class="bi bi-database-exclamation"></i>Ejecuta el script SQL entregado para registrar metas gerenciales.</p>
    </article>
    <article class="ev-dg-card ev-dg-status-card">
      <header><div><span class="ev-dg-card-icon"><i class="bi bi-diagram-3"></i></span><div><h2>Situación operativa</h2><p>Distribución de estados dentro del periodo.</p></div></div></header>
      <div class="ev-dg-status-columns"><div><h3>Pedidos</h3><div id="evDgPedidosEstados" class="ev-dg-status-list"></div></div><div><h3>Servicios</h3><div id="evDgServiciosEstados" class="ev-dg-status-list"></div></div></div>
    </article>
  </section>

  <section class="ev-dg-card ev-dg-community-card">
    <header><div><span class="ev-dg-card-icon"><i class="bi bi-buildings-fill"></i></span><div><h2>Uso por comunidad</h2><p>Rendimiento de condominios y urbanizaciones según actividad e ingresos registrados.</p></div></div><span class="ev-dg-table-count" id="evDgCommunityCount">0 comunidades</span></header>
    <div class="ev-dg-table-wrap"><table><thead><tr><th>Comunidad</th><th>Ubicación</th><th>Usuarios registrados</th><th>Publicaciones</th><th>Ventas</th><th>Servicios</th><th>Ingresos EV</th><th>Última actividad</th></tr></thead><tbody id="evDgCommunityBody"><tr><td colspan="8" class="ev-dg-empty-cell">Cargando información…</td></tr></tbody></table></div>
  </section>
</section>

<div class="modal fade" id="evDgGoalModal" tabindex="-1" aria-labelledby="evDgGoalModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered ev-dg-goal-dialog"><div class="modal-content">
    <div class="modal-header"><div class="ev-dg-modal-title"><span><i class="bi bi-bullseye"></i></span><div><small>PLANIFICACIÓN GERENCIAL</small><h5 id="evDgGoalModalTitle">Definir meta de ingresos</h5></div></div><button type="button" class="btn-close btn-close-white ev-modal-close-icon" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
    <form id="evDgGoalForm"><div class="modal-body"><p>La meta se guardará para el periodo y alcance que tienes seleccionados en el dashboard.</p><label><span>Monto objetivo (S/)</span><input type="number" id="evDgGoalInput" min="0.01" step="0.01" required placeholder="Ej. 5000.00"></label><div class="ev-dg-goal-context"><i class="bi bi-info-circle"></i><span id="evDgGoalContext">Periodo actual · Todo Entre Vecinos</span></div></div><div class="modal-footer"><button type="button" class="ev-dg-btn ev-dg-btn-light" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i>Cancelar</button><button type="submit" class="ev-dg-btn ev-dg-btn-primary"><i class="bi bi-check-circle"></i>Guardar</button></div></form>
  </div></div>
</div>
<script src="<?= rtrim(BASE_URL, '/') ?>/resources/util/plugins/chartjs/Chart.min.js"></script>
<script src="<?= rtrim(BASE_URL, '/') ?>/views/js/dashboardGerencial.js?v=<?= rawurlencode((string)(defined('EV_APP_VER') ? EV_APP_VER : '1.0.0')) ?>"></script>
