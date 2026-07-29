(() => {
  'use strict';

  const state = {
    root: null,
    tipoAlcance: 'global',
    codigoAlcance: 0,
    selectedScope: {
      tipo_alcance: 'global',
      codigo_alcance: 0,
      nombre: 'Todo Entre Vecinos',
    },
    scopeResults: [],
    scopeOpen: false,
    scopeDirty: false,
    suppressScopeFocusOnce: false,
    activeScopeIndex: -1,
    scopeSearchSeq: 0,
    scopeSearchTimer: null,
    scopeSearchAbortController: null,
    data: null,
    requestSeq: 0,
    abortController: null,
    observerStarted: false,
  };

  const base = () => String(
    window.EV?.baseUrl
      ?? window.EV_CONFIG?.baseUrl
      ?? window.EV_BASE_URL
      ?? window.BASE_URL
      ?? ''
  ).replace(/\/+$/, '');

  const esc = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    "'": '&#039;',
    '"': '&quot;',
  }[char]));

  const toBool = (value) => value === true || Number(value) === 1 || String(value).toLowerCase() === 'true';
  const toNumber = (value) => Number.isFinite(Number(value)) ? Number(value) : 0;

  async function jsonFetch(url, options = {}) {
    const headers = {'Accept': 'application/json', ...(options.headers || {})};
    if (String(options.method || 'GET').toUpperCase() !== 'GET') {
      const csrf = String(state.root?.dataset.csrf || '').trim();
      if (csrf) headers['X-EV-CSRF'] = csrf;
    }

    const response = await fetch(url, {
      ...options,
      credentials: options.credentials || 'same-origin',
      headers,
    });

    const raw = await response.text();
    let data = {};
    if (raw.trim() !== '') {
      try {
        data = JSON.parse(raw);
      } catch (_error) {
        throw new Error('El servidor devolvió una respuesta no válida. Revisa el registro de errores de PHP.');
      }
    }

    if (response.status === 401) {
      window.location.href = `${base()}/login`;
      throw new Error('Tu sesión venció.');
    }

    return {response, data};
  }

  function notify(icon, title, text) {
    if (window.Swal) {
      return Swal.fire({
        icon,
        title,
        text,
        confirmButtonText: 'Entendido',
        buttonsStyling: false,
        customClass: {
          popup: 'ev-config-swal',
          title: 'ev-config-swal-title',
          confirmButton: 'ev-config-swal-confirm',
        },
      });
    }

    window.alert(`${title}\n\n${text}`);
    return Promise.resolve();
  }

  function parseScope(value) {
    const [tipo = 'global', codigo = '0'] = String(value || 'global:0').split(':');
    return {tipoAlcance: tipo, codigoAlcance: Number(codigo || 0)};
  }

  function scopeValue(tipo, codigo) {
    return `${tipo}:${Number(codigo || 0)}`;
  }

  function formatoFechaInput(value) {
    if (!value) return '';
    return String(value).replace(' ', 'T').slice(0, 16);
  }

  function formatoFechaHistorial(value) {
    if (!value) return '—';
    const normalized = String(value).replace(' ', 'T');
    const date = new Date(normalized);
    if (Number.isNaN(date.getTime())) return String(value);
    return new Intl.DateTimeFormat('es-PE', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(date);
  }

  function configDirecta(item) {
    return item?.configuracion_directa || null;
  }

  function originInfo(item) {
    if (configDirecta(item)) {
      return {label: 'Configuración propia', className: 'is-direct', icon: 'bi-sliders'};
    }

    const origin = String(item?.resuelta?.origen || 'valor_defecto');
    if (origin === 'global') {
      return {label: 'Hereda la configuración global', className: '', icon: 'bi-diagram-3'};
    }
    if (origin === 'valor_defecto' || origin === 'fallback_compatibilidad') {
      return {label: 'Valor predeterminado', className: '', icon: 'bi-box-arrow-in-down'};
    }

    return {label: `Origen: ${origin}`, className: '', icon: 'bi-info-circle'};
  }

  function featureIcon(key) {
    const icons = {
      PUBLICAR_PRODUCTOS: 'bi-box-seam',
      PUBLICAR_SERVICIOS: 'bi-tools',
      MARKETPLACE: 'bi-shop-window',
      COMPRAR_PRODUCTOS: 'bi-cart-check',
      SOLICITAR_SERVICIOS: 'bi-clipboard2-check',
      BILLETERA: 'bi-wallet2',
      PROMOCIONES: 'bi-megaphone',
    };
    return icons[String(key || '').toUpperCase()] || 'bi-grid';
  }

  function monetizationIcon(key) {
    const icons = {
      COMISION_VENTA_PRODUCTO: 'bi-percent',
      COSTO_PUBLICACION_PRODUCTO: 'bi-box-seam',
      COSTO_PUBLICACION_SERVICIO_DIA: 'bi-calendar2-check',
      COMISION_SERVICIO: 'bi-receipt',
      PUBLICACIONES_DESTACADAS: 'bi-stars',
      DESCUENTO_BILLETERA_PEDIDO: 'bi-wallet2',
      RECARGAS_HABILITADAS: 'bi-cash-coin',
      BILLETERA_VISIBLE: 'bi-eye',
      BONO_BIENVENIDA_HABILITADO: 'bi-gift',
      BONO_BIENVENIDA_MONTO: 'bi-coin',
    };
    return icons[String(key || '').toUpperCase()] || 'bi-cash-stack';
  }

  function directOrResolvedBoolean(item, directKey, resolvedKey = directKey) {
    const direct = configDirecta(item);
    if (direct && direct[directKey] !== null && direct[directKey] !== undefined) {
      return toBool(direct[directKey]);
    }
    return toBool(item?.resuelta?.[resolvedKey]);
  }

  function directOrResolvedDecimal(item) {
    const direct = configDirecta(item);
    if (direct && direct.valor_decimal !== null && direct.valor_decimal !== undefined) {
      return toNumber(direct.valor_decimal);
    }
    return toNumber(item?.resuelta?.valor_decimal);
  }

  function scopeTypeLabel(type) {
    const labels = {
      global: 'Todo Entre Vecinos',
      condominio: 'Condominio',
      urbanizacion: 'Urbanización',
    };
    return labels[String(type || '')] || 'Comunidad';
  }

  function selectedScopeName() {
    return String(state.selectedScope?.nombre || 'Todo Entre Vecinos');
  }

  function scopeOptionId(index) {
    return `evConfigScopeOption-${index}`;
  }

  function closeScopeCombobox({restore = true, focus = false} = {}) {
    const input = state.root?.querySelector('#evConfigScopeSearch');
    const toggle = state.root?.querySelector('[data-action="toggle-scope-combobox"]');
    const menu = state.root?.querySelector('#evConfigScopeListbox');
    if (!input || !menu) return;

    state.scopeOpen = false;
    state.scopeDirty = false;
    state.activeScopeIndex = -1;
    menu.hidden = true;
    input.setAttribute('aria-expanded', 'false');
    input.removeAttribute('aria-activedescendant');
    toggle?.setAttribute('aria-expanded', 'false');
    state.root?.querySelector('#evConfigScopeCombobox')?.classList.remove('is-open');

    if (restore) input.value = selectedScopeName();
    if (focus) input.focus();
  }

  function openScopeCombobox({selectText = false} = {}) {
    const input = state.root?.querySelector('#evConfigScopeSearch');
    const toggle = state.root?.querySelector('[data-action="toggle-scope-combobox"]');
    const menu = state.root?.querySelector('#evConfigScopeListbox');
    if (!input || !menu) return;

    state.scopeOpen = true;
    menu.hidden = false;
    input.setAttribute('aria-expanded', 'true');
    toggle?.setAttribute('aria-expanded', 'true');
    state.root?.querySelector('#evConfigScopeCombobox')?.classList.add('is-open');
    if (selectText) input.select();
  }

  function setActiveScopeIndex(index, {scroll = true} = {}) {
    const options = Array.from(state.root?.querySelectorAll('[data-scope-option]') || []);
    if (!options.length) {
      state.activeScopeIndex = -1;
      state.root?.querySelector('#evConfigScopeSearch')?.removeAttribute('aria-activedescendant');
      return;
    }

    const normalized = Math.max(0, Math.min(index, options.length - 1));
    state.activeScopeIndex = normalized;
    options.forEach((option, optionIndex) => option.classList.toggle('is-active', optionIndex === normalized));

    const active = options[normalized];
    const input = state.root?.querySelector('#evConfigScopeSearch');
    if (active && input) {
      input.setAttribute('aria-activedescendant', active.id);
      if (scroll) active.scrollIntoView({block: 'nearest'});
    }
  }

  function renderScopeResults({loading = false, error = ''} = {}) {
    const wrap = state.root?.querySelector('#evConfigScopeOptions');
    const status = state.root?.querySelector('#evConfigScopeStatus');
    if (!wrap || !status) return;

    if (loading) {
      status.textContent = 'Buscando condominios y urbanizaciones…';
      wrap.innerHTML = '<div class="ev-config-combobox-loading"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Buscando alcances…</span></div>';
      return;
    }

    if (error) {
      status.textContent = 'No se pudo completar la búsqueda.';
      wrap.innerHTML = `<div class="ev-config-combobox-empty is-error"><i class="bi bi-exclamation-triangle"></i><span>${esc(error)}</span></div>`;
      return;
    }

    const results = Array.isArray(state.scopeResults) ? state.scopeResults : [];
    status.textContent = results.length
      ? `${results.length} ${results.length === 1 ? 'resultado disponible' : 'resultados disponibles'}`
      : 'Sin coincidencias';

    if (!results.length) {
      wrap.innerHTML = `
        <div class="ev-config-combobox-empty">
          <i class="bi bi-search"></i>
          <strong>No encontramos coincidencias</strong>
          <span>Prueba con otra parte del nombre del condominio o urbanización.</span>
        </div>`;
      state.activeScopeIndex = -1;
      return;
    }

    const currentValue = scopeValue(state.tipoAlcance, state.codigoAlcance);
    wrap.innerHTML = results.map((scope, index) => {
      const value = scopeValue(scope.tipo_alcance, scope.codigo_alcance);
      const selected = value === currentValue;
      const type = scopeTypeLabel(scope.tipo_alcance);
      const icon = scope.tipo_alcance === 'global'
        ? 'bi-globe-americas'
        : (scope.tipo_alcance === 'condominio' ? 'bi-buildings' : 'bi-houses');

      return `
        <button
          type="button"
          id="${scopeOptionId(index)}"
          class="ev-config-combobox-option ${selected ? 'is-selected' : ''}"
          role="option"
          aria-selected="${selected ? 'true' : 'false'}"
          data-scope-option
          data-index="${index}"
          data-value="${esc(value)}"
        >
          <span class="ev-config-combobox-option-icon"><i class="bi ${esc(icon)}"></i></span>
          <span class="ev-config-combobox-option-copy">
            <strong>${esc(scope.nombre)}</strong>
            <small>${esc(type)}</small>
          </span>
          <i class="bi bi-check2 ev-config-combobox-check" aria-hidden="true"></i>
        </button>`;
    }).join('');

    const selectedIndex = results.findIndex((scope) => scopeValue(scope.tipo_alcance, scope.codigo_alcance) === currentValue);
    if (selectedIndex >= 0) {
      setActiveScopeIndex(selectedIndex, {scroll: false});
    } else {
      state.activeScopeIndex = -1;
      state.root?.querySelector('#evConfigScopeSearch')?.removeAttribute('aria-activedescendant');
    }
  }

  async function searchScopes(query = '') {
    if (!state.root || !document.documentElement.contains(state.root)) return;

    const requestId = ++state.scopeSearchSeq;
    state.scopeSearchAbortController?.abort();
    state.scopeSearchAbortController = new AbortController();
    renderScopeResults({loading: true});

    try {
      const params = new URLSearchParams({q: String(query || '').trim(), limit: '25'});
      const {response, data} = await jsonFetch(
        `${base()}/api/admin/configuracion-plataforma/alcances?${params}`,
        {signal: state.scopeSearchAbortController.signal}
      );

      if (requestId !== state.scopeSearchSeq) return;
      if (!response.ok || !data.ok) throw new Error(data.mensaje || 'No se pudieron buscar los alcances.');
      state.scopeResults = Array.isArray(data.resultados) ? data.resultados : [];
      renderScopeResults();
    } catch (error) {
      if (error?.name === 'AbortError' || requestId !== state.scopeSearchSeq) return;
      state.scopeResults = [];
      renderScopeResults({error: error?.message || 'Ocurrió un error inesperado.'});
    }
  }

  function scheduleScopeSearch(query) {
    window.clearTimeout(state.scopeSearchTimer);
    state.scopeSearchTimer = window.setTimeout(() => searchScopes(query), 220);
  }

  function selectScope(scope) {
    if (!scope) return;

    const previous = scopeValue(state.tipoAlcance, state.codigoAlcance);
    state.tipoAlcance = String(scope.tipo_alcance || 'global');
    state.codigoAlcance = Number(scope.codigo_alcance || 0);
    state.selectedScope = {
      tipo_alcance: state.tipoAlcance,
      codigo_alcance: state.codigoAlcance,
      nombre: String(scope.nombre || 'Todo Entre Vecinos'),
    };

    const hidden = state.root?.querySelector('#evConfigScopeValue');
    if (hidden) hidden.value = scopeValue(state.tipoAlcance, state.codigoAlcance);
    state.suppressScopeFocusOnce = true;
    closeScopeCombobox({restore: true, focus: true});
    renderScope();

    if (previous !== scopeValue(state.tipoAlcance, state.codigoAlcance)) load();
  }

  function renderScope() {
    const selected = state.selectedScope || {
      tipo_alcance: 'global',
      codigo_alcance: 0,
      nombre: 'Todo Entre Vecinos',
    };

    const input = state.root?.querySelector('#evConfigScopeSearch');
    const hidden = state.root?.querySelector('#evConfigScopeValue');
    if (input && !state.scopeDirty) input.value = String(selected.nombre || 'Todo Entre Vecinos');
    if (hidden) hidden.value = scopeValue(state.tipoAlcance, state.codigoAlcance);

    const name = state.root?.querySelector('#evConfigScopeName');
    if (name) name.textContent = String(selected.nombre || 'Todo Entre Vecinos');

    const hint = state.root?.querySelector('#evConfigScopeHint');
    if (hint) {
      hint.textContent = state.tipoAlcance === 'global'
        ? 'La configuración global se utiliza como base para todas las comunidades.'
        : 'Los cambios se aplicarán únicamente a esta comunidad y prevalecerán sobre la configuración global.';
    }
  }

  function controlsSchedule(item, reasonText) {
    const direct = configDirecta(item);
    const mode = String(direct?.modo_activacion || item?.resuelta?.modo_activacion || 'manual');
    const start = formatoFechaInput(direct?.fecha_inicio || '');
    const end = formatoFechaInput(direct?.fecha_fin || '');

    return `
      <div class="full">
        <label class="ev-label">Modo de aplicación</label>
        <select class="form-select" data-field="modo_activacion">
          <option value="manual" ${mode === 'manual' ? 'selected' : ''}>Manual</option>
          <option value="programado" ${mode === 'programado' ? 'selected' : ''}>Programada</option>
        </select>
        <small class="ev-config-field-help" data-schedule-help>Manual: permanece vigente hasta que el Administrador EV vuelva a cambiarla.</small>
      </div>
      <div>
        <label class="ev-label">Fecha de inicio</label>
        <input class="form-control" data-field="fecha_inicio" type="datetime-local" value="${esc(start)}">
        <small class="ev-config-field-help">Opcional si solo necesitas definir una fecha de finalización.</small>
      </div>
      <div>
        <label class="ev-label">Fecha de fin</label>
        <input class="form-control" data-field="fecha_fin" type="datetime-local" value="${esc(end)}">
        <small class="ev-config-field-help">Opcional si la configuración debe mantenerse sin una fecha final.</small>
      </div>
      <div class="full">
        <label class="ev-label">Motivo del cambio</label>
        <input class="form-control" data-field="motivo" maxlength="500" value="${esc(direct?.motivo || reasonText)}" placeholder="Describe brevemente el motivo administrativo.">
      </div>
    `;
  }

  function shouldOpenAdvanced(_item) {
    // El listado inicia compacto. La edición se abre solo cuando el administrador la solicita.
    return false;
  }

  function scheduleInfo(item) {
    const direct = configDirecta(item);
    const mode = String(direct?.modo_activacion || item?.resuelta?.modo_activacion || 'manual');
    const start = direct?.fecha_inicio || item?.resuelta?.fecha_inicio || '';
    const end = direct?.fecha_fin || item?.resuelta?.fecha_fin || '';

    if (mode !== 'programado') {
      return {label: 'Aplicación manual', icon: 'bi-hand-index-thumb'};
    }
    if (start && end) {
      return {label: 'Vigencia programada', icon: 'bi-calendar-range'};
    }
    if (start) {
      return {label: 'Inicio programado', icon: 'bi-calendar-check'};
    }
    if (end) {
      return {label: 'Fin programado', icon: 'bi-calendar-x'};
    }
    return {label: 'Programación pendiente', icon: 'bi-calendar-event'};
  }

  function editorId(kind, key) {
    return `ev-config-editor-${String(kind || 'item').toLowerCase()}-${String(key || '').toLowerCase().replace(/[^a-z0-9_-]/g, '-')}`;
  }

  function rowEditor(item, kind, extraControls, action, buttonText) {
    const open = shouldOpenAdvanced(item);
    const id = editorId(kind, item.clave);
    return `
      <div class="ev-config-editor ${open ? 'is-open' : ''}" id="${esc(id)}">
        <div class="ev-config-editor-inner">
          <div class="ev-config-editor-head">
            <div>
              <strong>Detalle de configuración</strong>
              <span>Define la vigencia y deja trazabilidad del cambio administrativo.</span>
            </div>
            <button type="button" class="ev-config-editor-close" data-action="close-editor" aria-label="Cerrar configuración">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="ev-config-controls">
            ${extraControls || ''}
            ${controlsSchedule(item, kind === 'funcionalidad' ? 'Actualización administrativa de funcionalidad' : 'Actualización administrativa de monetización')}
          </div>
          <div class="ev-config-editor-footer">
            <small><i class="bi bi-shield-check me-1"></i>El cambio se aplicará únicamente al alcance seleccionado y quedará registrado en el historial.</small>
            <button type="button" class="ev-config-save" data-action="${esc(action)}">
              <i class="bi bi-check2-circle"></i> ${esc(buttonText)}
            </button>
          </div>
        </div>
      </div>`;
  }

  function renderFuncionalidades() {
    const wrap = state.root?.querySelector('#evFuncionalidadesLista');
    if (!wrap) return;

    const items = Array.isArray(state.data?.funcionalidades) ? state.data.funcionalidades : [];
    const count = state.root.querySelector('#evFeaturesCount');
    if (count) count.textContent = `${items.length} ${items.length === 1 ? 'funcionalidad' : 'funcionalidades'}`;

    if (!items.length) {
      wrap.innerHTML = `
        <div class="ev-config-empty">
          <div>
            <strong>No se encontraron funcionalidades</strong>
            <span>Verifica que el script SQL de los puntos 14 y 15 se encuentre aplicado.</span>
          </div>
        </div>`;
      return;
    }

    wrap.innerHTML = items.map((item) => {
      const direct = configDirecta(item);
      const enabled = direct ? toBool(direct.habilitada) : toBool(item?.resuelta?.habilitada);
      const message = direct?.mensaje_usuario || item?.resuelta?.mensaje || '';
      const origin = originInfo(item);
      const schedule = scheduleInfo(item);
      const id = editorId('funcionalidad', item.clave);
      const open = shouldOpenAdvanced(item);
      const extraControls = `
        <div class="full">
          <label>Mensaje cuando esté desactivada</label>
          <input class="form-control" data-field="mensaje_usuario" maxlength="500" value="${esc(message)}" placeholder="Esta funcionalidad todavía no está disponible durante el piloto.">
        </div>`;

      return `
        <article class="ev-config-card ev-config-row ${enabled ? 'is-enabled' : 'is-disabled'}" data-kind="funcionalidad" data-clave="${esc(item.clave)}">
          <div class="ev-config-row-main">
            <div class="ev-config-row-identity">
              <span class="ev-config-row-icon"><i class="bi ${featureIcon(item.clave)}"></i></span>
              <div class="ev-config-row-copy">
                <h3>${esc(item.nombre)}</h3>
                <p>${esc(item.descripcion || '')}</p>
                <div class="ev-config-row-meta">
                  <span class="ev-config-origin ${origin.className}"><i class="bi ${origin.icon}"></i>${esc(origin.label)}</span>
                  <span class="ev-config-mode"><i class="bi ${schedule.icon}"></i>${esc(schedule.label)}</span>
                  <span class="ev-config-pending"><i class="bi bi-exclamation-circle"></i>Cambio sin guardar</span>
                </div>
              </div>
            </div>
            <div class="ev-config-row-actions">
              <span class="ev-config-status" data-state-label>${enabled ? 'Activo' : 'Inactivo'}</span>
              <label class="ev-config-switch" title="Activar o desactivar ${esc(item.nombre)}">
                <input type="checkbox" data-field="habilitada" aria-label="Activar o desactivar ${esc(item.nombre)}" ${enabled ? 'checked' : ''}>
                <span></span>
              </label>
              <button type="button" class="ev-config-edit-btn" data-action="toggle-editor" aria-controls="${esc(id)}" aria-expanded="${open ? 'true' : 'false'}">
                <span>Configurar</span><i class="bi bi-chevron-down"></i>
              </button>
            </div>
          </div>
          ${rowEditor(item, 'funcionalidad', extraControls, 'guardar-funcionalidad', 'Guardar cambios')}
        </article>`;
    }).join('');

    wrap.querySelectorAll('.ev-config-card').forEach(syncScheduleFields);
  }

  function formatCommercialValue(item, value) {
    const number = toNumber(value);
    const formatted = new Intl.NumberFormat('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(number);
    const unit = String(item?.unidad || '').trim();
    if (unit === 'S/') return {value: `S/ ${formatted}`, unit: ''};
    return {value: formatted, unit};
  }

  function renderMonetizacion() {
    const wrap = state.root?.querySelector('#evMonetizacionLista');
    if (!wrap) return;

    const items = Array.isArray(state.data?.monetizacion) ? state.data.monetizacion : [];
    const count = state.root.querySelector('#evMonetizationCount');
    if (count) count.textContent = `${items.length} ${items.length === 1 ? 'regla' : 'reglas'}`;

    if (!items.length) {
      wrap.innerHTML = `
        <div class="ev-config-empty">
          <div>
            <strong>No se encontraron reglas de monetización</strong>
            <span>Verifica que el script SQL de los puntos 14 y 15 se encuentre aplicado.</span>
          </div>
        </div>`;
      return;
    }

    wrap.innerHTML = items.map((item) => {
      const direct = configDirecta(item);
      const origin = originInfo(item);
      const schedule = scheduleInfo(item);
      const isBoolean = item.tipo_valor === 'booleano';
      const id = editorId('monetizacion', item.clave);
      const open = shouldOpenAdvanced(item);
      let stateClass = 'is-enabled';
      let statusText = 'Configurada';
      let primaryControl = '';
      let extraControls = '';

      if (isBoolean) {
        const enabled = directOrResolvedBoolean(item, 'valor_booleano');
        stateClass = enabled ? 'is-enabled' : 'is-disabled';
        statusText = enabled ? 'Habilitada' : 'Deshabilitada';
        primaryControl = `
          <label class="ev-config-switch" title="Habilitar o deshabilitar esta regla">
            <input type="checkbox" data-field="valor_booleano" aria-label="Habilitar o deshabilitar ${esc(item.nombre)}" ${enabled ? 'checked' : ''}>
            <span></span>
          </label>`;
      } else {
        const value = directOrResolvedDecimal(item);
        const display = formatCommercialValue(item, value);
        stateClass = value > 0 ? 'is-enabled' : 'is-disabled';
        statusText = value > 0 ? 'Con cobro' : 'Sin cobro';
        primaryControl = `<span class="ev-config-value-display"><strong>${esc(display.value)}</strong>${display.unit ? `<span>${esc(display.unit)}</span>` : ''}</span>`;
        extraControls = `
          <div class="full">
            <label>Valor configurado</label>
            <div class="ev-config-control-with-unit">
              <input class="form-control" data-field="valor_decimal" type="number" min="0" ${item.tipo_valor === 'porcentaje' ? 'max="100"' : ''} step="0.01" value="${esc(value)}">
              <span class="ev-config-unit">${esc(item.unidad || '')}</span>
            </div>
          </div>`;
      }

      return `
        <article class="ev-config-card ev-config-row ${stateClass}" data-kind="monetizacion" data-clave="${esc(item.clave)}" data-tipo-valor="${esc(item.tipo_valor)}">
          <div class="ev-config-row-main">
            <div class="ev-config-row-identity">
              <span class="ev-config-row-icon"><i class="bi ${monetizationIcon(item.clave)}"></i></span>
              <div class="ev-config-row-copy">
                <h3>${esc(item.nombre)}</h3>
                <p>${esc(item.descripcion || '')}</p>
                <div class="ev-config-row-meta">
                  <span class="ev-config-origin ${origin.className}"><i class="bi ${origin.icon}"></i>${esc(origin.label)}</span>
                  <span class="ev-config-mode"><i class="bi ${schedule.icon}"></i>${esc(schedule.label)}</span>
                  <span class="ev-config-pending"><i class="bi bi-exclamation-circle"></i>Cambio sin guardar</span>
                </div>
              </div>
            </div>
            <div class="ev-config-row-actions">
              <span class="ev-config-status" data-state-label data-boolean-label>${esc(statusText)}</span>
              ${primaryControl}
              <button type="button" class="ev-config-edit-btn" data-action="toggle-editor" aria-controls="${esc(id)}" aria-expanded="${open ? 'true' : 'false'}">
                <span>Configurar</span><i class="bi bi-chevron-down"></i>
              </button>
            </div>
          </div>
          ${rowEditor(item, 'monetizacion', extraControls, 'guardar-monetizacion', 'Guardar regla')}
        </article>`;
    }).join('');

    wrap.querySelectorAll('.ev-config-card').forEach(syncScheduleFields);
  }

  function renderHistorial() {
    const tbody = state.root?.querySelector('#evConfigHistorial');
    if (!tbody) return;

    const rows = Array.isArray(state.data?.historial) ? state.data.historial : [];
    tbody.innerHTML = rows.length
      ? rows.map((row) => `
          <tr>
            <td>${esc(formatoFechaHistorial(row.created_at || ''))}</td>
            <td><span class="ev-config-history-badge">${esc(row.tipo || '')}</span></td>
            <td class="fw-semibold">${esc(row.concepto || '')}</td>
            <td>${esc(row.motivo || '—')}</td>
            <td>${esc(row.administrador || 'Administrador EV')}</td>
          </tr>`).join('')
      : '<tr><td colspan="5" class="text-center text-muted py-4">Todavía no hay cambios registrados para este alcance.</td></tr>';
  }

  function findMonetization(key) {
    return (state.data?.monetizacion || []).find((item) => String(item.clave) === key) || null;
  }

  function renderSummary() {
    const scopeValueEl = state.root?.querySelector('#evSummaryScopeValue');
    const scopeMetaEl = state.root?.querySelector('#evSummaryScopeMeta');
    const featureValueEl = state.root?.querySelector('#evSummaryFeaturesValue');
    const featureMetaEl = state.root?.querySelector('#evSummaryFeaturesMeta');
    const monetizationValueEl = state.root?.querySelector('#evSummaryMonetizationValue');
    const monetizationMetaEl = state.root?.querySelector('#evSummaryMonetizationMeta');

    const selected = state.selectedScope || {};
    const scopeLabels = {global: 'Configuración global', condominio: 'Condominio', urbanizacion: 'Urbanización'};
    if (scopeValueEl) scopeValueEl.textContent = selected.nombre || 'Todo Entre Vecinos';
    if (scopeMetaEl) scopeMetaEl.textContent = scopeLabels[state.tipoAlcance] || 'Comunidad';

    const features = Array.isArray(state.data?.funcionalidades) ? state.data.funcionalidades : [];
    const enabledCount = features.filter((item) => {
      const direct = configDirecta(item);
      return direct ? toBool(direct.habilitada) : toBool(item?.resuelta?.habilitada);
    }).length;
    if (featureValueEl) featureValueEl.textContent = `${enabledCount} de ${features.length}`;
    if (featureMetaEl) featureMetaEl.textContent = features.length === enabledCount ? 'Todas disponibles' : 'Existen módulos restringidos';

    const commission = directOrResolvedDecimal(findMonetization('COMISION_VENTA_PRODUCTO'));
    const walletVisible = directOrResolvedBoolean(findMonetization('BILLETERA_VISIBLE'), 'valor_booleano');
    const reloads = directOrResolvedBoolean(findMonetization('RECARGAS_HABILITADAS'), 'valor_booleano');
    const walletDebit = directOrResolvedBoolean(findMonetization('DESCUENTO_BILLETERA_PEDIDO'), 'valor_booleano');
    const pilotFree = commission === 0 && !walletVisible && !reloads && !walletDebit;

    if (monetizationValueEl) monetizationValueEl.textContent = pilotFree ? 'Piloto gratuito' : `${commission.toFixed(2)} %`;
    if (monetizationMetaEl) monetizationMetaEl.textContent = pilotFree ? 'Sin cobros ni billetera visible' : 'Comisión por venta de producto';
  }

  function setLoading() {
    const loading = `
      <div class="ev-config-loading">
        <div class="ev-config-loading-inner">
          <span class="ev-config-loading-icon"><i class="bi bi-sliders2"></i></span>
          <strong>Cargando configuración</strong>
          <span>Estamos consultando las reglas del alcance seleccionado.</span>
          <div class="ev-config-skeleton-lines" aria-hidden="true"><i></i><i></i><i></i></div>
        </div>
      </div>`;

    ['#evFuncionalidadesLista', '#evMonetizacionLista'].forEach((selector) => {
      const element = state.root?.querySelector(selector);
      if (element) element.innerHTML = loading;
    });

    const history = state.root?.querySelector('#evConfigHistorial');
    if (history) history.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Cargando historial…</td></tr>';
  }

  function renderLoadError(message) {
    const errorMarkup = `
      <div class="ev-config-error">
        <span class="ev-config-error-icon"><i class="bi bi-exclamation-triangle"></i></span>
        <strong>No se pudo cargar la configuración</strong>
        <span>${esc(message || 'Ocurrió un error inesperado.')}</span>
        <button type="button" class="ev-config-retry" data-action="reintentar-carga"><i class="bi bi-arrow-clockwise me-1"></i>Reintentar</button>
      </div>`;

    ['#evFuncionalidadesLista', '#evMonetizacionLista'].forEach((selector) => {
      const element = state.root?.querySelector(selector);
      if (element) element.innerHTML = errorMarkup;
    });

    const history = state.root?.querySelector('#evConfigHistorial');
    if (history) history.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${esc(message || 'No se pudo cargar el historial.')}</td></tr>`;
  }

  function setBusy(busy) {
    const refresh = state.root?.querySelector('#btnRefrescarConfiguracion');
    if (refresh) {
      refresh.disabled = busy;
      refresh.classList.toggle('is-spinning', busy);
    }
  }

  async function load() {
    if (!state.root || !document.documentElement.contains(state.root)) return;

    const requestId = ++state.requestSeq;
    state.abortController?.abort();
    state.abortController = new AbortController();

    setBusy(true);
    setLoading();

    try {
      const params = new URLSearchParams({
        tipo_alcance: state.tipoAlcance,
        codigo_alcance: String(state.codigoAlcance),
      });

      const {response, data} = await jsonFetch(
        `${base()}/api/admin/configuracion-plataforma?${params}`,
        {signal: state.abortController.signal}
      );

      if (requestId !== state.requestSeq) return;
      if (!response.ok || !data.ok) {
        throw new Error(data.mensaje || 'No se pudo cargar la configuración.');
      }

      state.data = data.data || {};
      if (data.alcance_seleccionado && typeof data.alcance_seleccionado === 'object') {
        state.selectedScope = {
          tipo_alcance: String(data.alcance_seleccionado.tipo_alcance || state.tipoAlcance),
          codigo_alcance: Number(data.alcance_seleccionado.codigo_alcance || state.codigoAlcance),
          nombre: String(data.alcance_seleccionado.nombre || selectedScopeName()),
        };
        state.tipoAlcance = state.selectedScope.tipo_alcance;
        state.codigoAlcance = state.selectedScope.codigo_alcance;
      }

      renderScope();
      renderFuncionalidades();
      renderMonetizacion();
      renderHistorial();
      renderSummary();
    } catch (error) {
      if (error?.name === 'AbortError' || requestId !== state.requestSeq) return;
      const message = error?.message || 'Ocurrió un error inesperado.';
      renderLoadError(message);
      await notify('error', 'No se pudo cargar', message);
    } finally {
      if (requestId === state.requestSeq) setBusy(false);
    }
  }

  function collectCard(card) {
    const payload = {
      clave: card.dataset.clave,
      tipo_alcance: state.tipoAlcance,
      codigo_alcance: state.codigoAlcance,
    };

    card.querySelectorAll('[data-field]').forEach((field) => {
      const key = field.dataset.field;
      payload[key] = field.type === 'checkbox' ? (field.checked ? 1 : 0) : field.value;
    });

    // La modalidad manual no conserva fechas antiguas que puedan generar
    // confusión cuando el administrador vuelva a consultar la configuración.
    if (payload.modo_activacion === 'manual') {
      payload.fecha_inicio = '';
      payload.fecha_fin = '';
    }

    return payload;
  }

  function validateCardPayload(payload) {
    if (payload.modo_activacion !== 'programado') return '';

    const start = String(payload.fecha_inicio || '').trim();
    const end = String(payload.fecha_fin || '').trim();
    if (!start && !end) {
      return 'Para utilizar la modalidad programada debes indicar al menos una fecha de inicio o de fin.';
    }

    if (start && end) {
      const startDate = new Date(start);
      const endDate = new Date(end);
      if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) {
        return 'Revisa el formato de las fechas programadas.';
      }
      if (endDate < startDate) {
        return 'La fecha de fin no puede ser anterior a la fecha de inicio.';
      }
    }

    return '';
  }

  async function saveCard(card, endpoint) {
    if (!card) return;
    const button = card.querySelector('.ev-config-save');
    const original = button?.innerHTML;

    card.classList.add('is-saving');
    if (button) {
      button.disabled = true;
      button.classList.add('is-loading');
      button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Guardando…';
    }

    try {
      const payload = collectCard(card);
      const validationMessage = validateCardPayload(payload);
      if (validationMessage) {
        await notify('warning', 'Completa la programación', validationMessage);
        return;
      }

      const {response, data} = await jsonFetch(`${base()}${endpoint}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload),
      });

      if (!response.ok || !data.ok) throw new Error(data.mensaje || 'No se pudo guardar la configuración.');
      card.classList.remove('has-pending');
      card.classList.add('is-saved');
      if (button && document.documentElement.contains(button)) {
        button.classList.remove('is-loading');
        button.classList.add('is-success');
        button.innerHTML = '<i class="bi bi-check2-circle" aria-hidden="true"></i> Guardado';
      }
      await notify('success', 'Configuración guardada', data.mensaje || 'El cambio se aplicó correctamente.');
      await load();
    } catch (error) {
      card.classList.add('is-save-error');
      if (button && document.documentElement.contains(button)) {
        button.classList.remove('is-loading');
        button.classList.add('is-error');
        button.innerHTML = '<i class="bi bi-exclamation-circle" aria-hidden="true"></i> Reintentar';
      }
      await notify('error', 'No se pudo guardar', error?.message || 'Ocurrió un error inesperado.');
    } finally {
      card.classList.remove('is-saving');
      window.setTimeout(() => {
        if (button && document.documentElement.contains(button)) {
          button.disabled = false;
          button.classList.remove('is-loading', 'is-success', 'is-error');
          button.innerHTML = original;
        }
        if (document.documentElement.contains(card)) {
          card.classList.remove('is-saved', 'is-save-error');
        }
      }, 700);
    }
  }

  function setEditor(card, open) {
    if (!card) return;
    const editor = card.querySelector('.ev-config-editor');
    const button = card.querySelector('[data-action="toggle-editor"]');
    if (!editor || !button) return;
    editor.classList.toggle('is-open', open);
    card.classList.toggle('is-selected', open);
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      syncScheduleFields(card);
    }
  }

  function markPending(card) {
    if (!card) return;
    card.classList.add('has-pending');
  }

  function syncScheduleFields(card) {
    if (!card) return;
    const mode = card.querySelector('[data-field="modo_activacion"]');
    const scheduled = mode?.value === 'programado';
    card.querySelectorAll('[data-field="fecha_inicio"], [data-field="fecha_fin"]').forEach((field) => {
      field.disabled = !scheduled;
      field.title = scheduled ? '' : 'Selecciona la modalidad programada para utilizar fechas.';
    });

    const help = card.querySelector('[data-schedule-help]');
    if (help) {
      help.textContent = scheduled
        ? 'Programada: indica al menos una fecha. Fuera de la vigencia se aplicará la siguiente configuración disponible para el alcance.'
        : 'Manual: permanece vigente hasta que el Administrador EV vuelva a cambiarla.';
    }
  }

  function syncCardState(card) {
    if (!card) return;

    if (card.dataset.kind === 'funcionalidad') {
      const checked = Boolean(card.querySelector('[data-field="habilitada"]')?.checked);
      card.classList.toggle('is-enabled', checked);
      card.classList.toggle('is-disabled', !checked);
      const label = card.querySelector('[data-state-label]');
      if (label) label.textContent = checked ? 'Activo' : 'Inactivo';
      return;
    }

    if (card.dataset.kind === 'monetizacion' && card.dataset.tipoValor === 'booleano') {
      const checked = Boolean(card.querySelector('[data-field="valor_booleano"]')?.checked);
      card.classList.toggle('is-enabled', checked);
      card.classList.toggle('is-disabled', !checked);
      const label = card.querySelector('[data-boolean-label]');
      if (label) label.textContent = checked ? 'Habilitada' : 'Deshabilitada';
    }
  }

  function bind() {
    if (!state.root || state.root.dataset.evConfigBound === '1') return;
    state.root.dataset.evConfigBound = '1';

    state.root.addEventListener('click', (event) => {
      const scopeToggle = event.target.closest('[data-action="toggle-scope-combobox"]');
      if (scopeToggle) {
        const input = state.root.querySelector('#evConfigScopeSearch');
        if (state.scopeOpen) {
          closeScopeCombobox({restore: true, focus: true});
        } else {
          openScopeCombobox({selectText: true});
          input?.focus();
          searchScopes('');
        }
        return;
      }

      const scopeOption = event.target.closest('[data-scope-option]');
      if (scopeOption) {
        const index = Number(scopeOption.dataset.index || -1);
        selectScope(state.scopeResults[index]);
        return;
      }

      const tab = event.target.closest('[data-ev-config-tab]');
      if (tab) {
        const name = tab.dataset.evConfigTab;
        state.root.querySelectorAll('[data-ev-config-tab]').forEach((element) => {
          const active = element === tab;
          element.classList.toggle('is-active', active);
          element.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        state.root.querySelectorAll('[data-ev-config-panel]').forEach((element) => {
          element.classList.toggle('is-active', element.dataset.evConfigPanel === name);
        });
        return;
      }

      const toggleEditor = event.target.closest('[data-action="toggle-editor"]');
      if (toggleEditor) {
        const card = toggleEditor.closest('.ev-config-card');
        const open = toggleEditor.getAttribute('aria-expanded') !== 'true';
        state.root.querySelectorAll('.ev-config-card').forEach((item) => {
          if (item !== card) setEditor(item, false);
        });
        setEditor(card, open);
        return;
      }

      const closeEditor = event.target.closest('[data-action="close-editor"]');
      if (closeEditor) {
        setEditor(closeEditor.closest('.ev-config-card'), false);
        return;
      }

      const saveFeature = event.target.closest('[data-action="guardar-funcionalidad"]');
      if (saveFeature) {
        saveCard(saveFeature.closest('.ev-config-card'), '/api/admin/configuracion-plataforma/funcionalidad');
        return;
      }

      const saveMonetization = event.target.closest('[data-action="guardar-monetizacion"]');
      if (saveMonetization) {
        saveCard(saveMonetization.closest('.ev-config-card'), '/api/admin/configuracion-plataforma/monetizacion');
        return;
      }

      if (event.target.closest('#btnRefrescarConfiguracion') || event.target.closest('[data-action="reintentar-carga"]')) {
        load();
      }
    });

    state.root.addEventListener('mouseover', (event) => {
      const option = event.target.closest('[data-scope-option]');
      if (option) setActiveScopeIndex(Number(option.dataset.index || 0), {scroll: false});
    });

    state.root.addEventListener('focusin', (event) => {
      if (!event.target.matches('#evConfigScopeSearch')) return;
      if (state.suppressScopeFocusOnce) {
        state.suppressScopeFocusOnce = false;
        return;
      }
      if (!state.scopeOpen) {
        openScopeCombobox();
        searchScopes('');
      }
      window.setTimeout(() => event.target.select(), 0);
    });

    state.root.addEventListener('input', (event) => {
      if (!event.target.matches('#evConfigScopeSearch')) return;
      state.scopeDirty = true;
      if (!state.scopeOpen) openScopeCombobox();
      scheduleScopeSearch(event.target.value);
    });

    state.root.addEventListener('keydown', (event) => {
      if (!event.target.matches('#evConfigScopeSearch')) return;

      if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (!state.scopeOpen) {
          openScopeCombobox();
          searchScopes('');
          return;
        }
        setActiveScopeIndex(state.activeScopeIndex + 1);
        return;
      }

      if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (!state.scopeOpen) {
          openScopeCombobox();
          searchScopes('');
          return;
        }
        setActiveScopeIndex(state.activeScopeIndex <= 0 ? state.scopeResults.length - 1 : state.activeScopeIndex - 1);
        return;
      }

      if (event.key === 'Enter' && state.scopeOpen) {
        event.preventDefault();
        selectScope(state.scopeResults[state.activeScopeIndex]);
        return;
      }

      if (event.key === 'Escape') {
        event.preventDefault();
        closeScopeCombobox({restore: true, focus: true});
        return;
      }

      if (event.key === 'Tab') closeScopeCombobox({restore: true});
    });

    state.root.addEventListener('change', (event) => {
      const card = event.target.closest('.ev-config-card');
      if (!card) return;

      if (event.target.matches('[data-field="modo_activacion"]')) syncScheduleFields(card);
      if (event.target.type === 'checkbox') {
        syncCardState(card);
        setEditor(card, true);
      }
      markPending(card);
    });

    if (document.documentElement.dataset.evConfigScopeOutsideBound !== '1') {
      document.documentElement.dataset.evConfigScopeOutsideBound = '1';
      document.addEventListener('pointerdown', (event) => {
        if (!state.scopeOpen || !state.root) return;
        const combobox = state.root.querySelector('#evConfigScopeCombobox');
        if (combobox && !combobox.contains(event.target)) closeScopeCombobox({restore: true});
      });
    }
  }

  function init() {
    const root = document.querySelector('#evConfiguracionPlataforma');
    if (!root) return;

    // Evita reiniciar el módulo cuando el propio render agrega cards al DOM.
    if (state.root === root && root.dataset.evConfigInitialized === '1') return;

    state.abortController?.abort();
    state.root = root;
    state.tipoAlcance = 'global';
    state.codigoAlcance = 0;
    state.selectedScope = {
      tipo_alcance: 'global',
      codigo_alcance: 0,
      nombre: 'Todo Entre Vecinos',
    };
    state.scopeResults = [];
    state.scopeOpen = false;
    state.scopeDirty = false;
    state.suppressScopeFocusOnce = false;
    state.activeScopeIndex = -1;
    state.scopeSearchAbortController?.abort();
    state.data = null;
    root.dataset.evConfigInitialized = '1';

    bind();
    load();
  }

  function startObserver() {
    if (state.observerStarted || !document.body) return;
    state.observerStarted = true;

    const observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
          if (!(node instanceof Element)) continue;
          if (node.matches?.('#evConfiguracionPlataforma') || node.querySelector?.('#evConfiguracionPlataforma')) {
            init();
            return;
          }
        }
      }
    });

    observer.observe(document.body, {childList: true, subtree: true});
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      init();
      startObserver();
    }, {once: true});
  } else {
    init();
    startObserver();
  }

  document.addEventListener('ev:partial-loaded', init);
})();
