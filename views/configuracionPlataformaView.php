<?php
require_once __DIR__ . '/../Config/config.php';
?>
<?php include_once __DIR__ . '/estilos/configuracionPlataformaEstilo.php'; ?>

<section
  class="container-fluid ev-config-page fade-in"
  id="evConfiguracionPlataforma"
  data-csrf="<?= htmlspecialchars((string)($evConfigCsrf ?? ''), ENT_QUOTES, 'UTF-8') ?>"
  aria-labelledby="evConfigTitle"
>
  <header class="ev-config-hero">
    <div class="ev-config-hero-copy">
      <div class="ev-config-hero-icon" aria-hidden="true"><i class="bi bi-sliders2"></i></div>
      <div>
        <div class="ev-config-kicker">ADMINISTRACIÓN · PLATAFORMA</div>
        <h2 id="evConfigTitle">Configuración de plataforma</h2>
        <p>Administra la disponibilidad de módulos y las condiciones comerciales de Entre Vecinos sin modificar el código.</p>
      </div>
    </div>
  </header>

  <section class="ev-config-summary" aria-label="Resumen de configuración">
    <article class="ev-config-summary-card ev-config-summary-green">
      <span class="ev-config-summary-icon" aria-hidden="true"><i class="bi bi-buildings"></i></span>
      <div class="ev-config-summary-body">
        <span>Alcance actual</span>
        <strong id="evSummaryScopeValue">Global</strong>
        <small id="evSummaryScopeMeta">Configuración general</small>
      </div>
    </article>
    <article class="ev-config-summary-card ev-config-summary-green">
      <span class="ev-config-summary-icon" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span>
      <div class="ev-config-summary-body">
        <span>Funcionalidades activas</span>
        <strong id="evSummaryFeaturesValue">—</strong>
        <small id="evSummaryFeaturesMeta">Esperando información</small>
      </div>
    </article>
    <article class="ev-config-summary-card ev-config-summary-orange">
      <span class="ev-config-summary-icon" aria-hidden="true"><i class="bi bi-wallet2"></i></span>
      <div class="ev-config-summary-body">
        <span>Condición comercial</span>
        <strong id="evSummaryMonetizationValue">—</strong>
        <small id="evSummaryMonetizationMeta">Esperando información</small>
      </div>
    </article>
  </section>

  <section class="ev-config-workspace">
    <header class="ev-config-workspace-head">
      <div class="ev-config-workspace-copy">
        <div class="ev-config-workspace-title">
          <h5>Configuración operativa</h5>
          <span class="ev-config-admin-badge"><i class="bi bi-shield-lock"></i> Solo Administrador EV</span>
        </div>
        <p>Controla qué pueden utilizar los vecinos y qué reglas comerciales se aplican en cada comunidad.</p>
      </div>

      <div class="ev-config-scope-control">
        <label for="evConfigScopeSearch">Alcance de configuración</label>
        <div class="ev-config-scope-control-row">
          <div class="ev-config-combobox" id="evConfigScopeCombobox">
            <div class="ev-config-combobox-field">
              <i class="bi bi-search" aria-hidden="true"></i>
              <input
                type="text"
                id="evConfigScopeSearch"
                class="form-control"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="evConfigScopeListbox"
                aria-haspopup="listbox"
                autocomplete="off"
                spellcheck="false"
                placeholder="Buscar condominio o urbanización"
              >
              <input type="hidden" id="evConfigScopeValue" value="global:0">
              <button
                type="button"
                class="ev-config-combobox-toggle"
                data-action="toggle-scope-combobox"
                aria-label="Mostrar alcances disponibles"
                aria-controls="evConfigScopeListbox"
                aria-expanded="false"
              >
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
              </button>
            </div>

            <div class="ev-config-combobox-menu" id="evConfigScopeListbox" role="listbox" aria-label="Condominios y urbanizaciones" hidden>
              <div class="ev-config-combobox-status" id="evConfigScopeStatus">Escribe para buscar una comunidad.</div>
              <div class="ev-config-combobox-options" id="evConfigScopeOptions"></div>
            </div>
          </div>

          <button type="button" class="ev-config-refresh" id="btnRefrescarConfiguracion" title="Actualizar configuración" aria-label="Actualizar configuración">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
      </div>
    </header>

    <div class="ev-config-workspace-nav">
      <nav class="ev-config-tabs" role="tablist" aria-label="Secciones de configuración">
        <button type="button" class="is-active" data-ev-config-tab="funcionalidades" role="tab" aria-selected="true">
          <i class="bi bi-grid-1x2"></i><span>Funcionalidades</span>
        </button>
        <button type="button" data-ev-config-tab="monetizacion" role="tab" aria-selected="false">
          <i class="bi bi-cash-coin"></i><span>Monetización</span>
        </button>
        <button type="button" data-ev-config-tab="historial" role="tab" aria-selected="false">
          <i class="bi bi-clock-history"></i><span>Historial</span>
        </button>
      </nav>

      <div class="ev-config-scope-context">
        <i class="bi bi-buildings" aria-hidden="true"></i>
        <div>
          <span id="evConfigScopeName">Todo Entre Vecinos</span>
          <small id="evConfigScopeHint">La configuración global se utiliza como base para todas las comunidades.</small>
        </div>
      </div>
    </div>

    <div class="ev-config-workspace-body">
      <div class="ev-config-panel is-active" data-ev-config-panel="funcionalidades" role="tabpanel">
        <div class="ev-config-section-head">
          <div>
            <span class="ev-config-section-kicker">VISIBILIDAD Y OPERACIÓN</span>
            <h2>Control de funcionalidades</h2>
            <p>Activa, restringe o programa los módulos disponibles para el alcance seleccionado.</p>
          </div>
          <span class="ev-config-count" id="evFeaturesCount">0 funcionalidades</span>
        </div>
        <div id="evFuncionalidadesLista" class="ev-config-list" aria-live="polite"></div>
      </div>

      <div class="ev-config-panel" data-ev-config-panel="monetizacion" role="tabpanel">
        <div class="ev-config-section-head">
          <div>
            <span class="ev-config-section-kicker">REGLAS COMERCIALES</span>
            <h2>Configuración de monetización</h2>
            <p>Define comisiones, importes y operaciones de billetera para el alcance seleccionado.</p>
          </div>
          <span class="ev-config-count" id="evMonetizationCount">0 reglas</span>
        </div>

        <div id="evMonetizacionLista" class="ev-config-list" aria-live="polite"></div>
      </div>

      <div class="ev-config-panel" data-ev-config-panel="historial" role="tabpanel">
        <div class="ev-config-section-head">
          <div>
            <span class="ev-config-section-kicker">AUDITORÍA ADMINISTRATIVA</span>
            <h2>Historial de cambios</h2>
            <p>Consulta las últimas modificaciones registradas para el alcance seleccionado.</p>
          </div>
        </div>

        <div class="ev-config-history-wrap">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Concepto</th>
                <th>Motivo</th>
                <th>Administrador</th>
              </tr>
            </thead>
            <tbody id="evConfigHistorial">
              <tr><td colspan="5" class="text-center text-muted py-4">Cargando historial…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</section>
<script src="<?= rtrim(BASE_URL, '/') ?>/views/js/configuracionPlataforma.js?v=<?= rawurlencode((string)(defined('EV_APP_VER') ? EV_APP_VER : '1.0.0')) ?>"></script>
