/* EV — Select buscable reutilizable para combos administrativos. */
(function () {
  'use strict';

  const STYLE_ID = 'ev-searchable-select-styles';
  let openInstance = null;

  function injectStyles() {
    if (document.getElementById(STYLE_ID)) return;
    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = `
      .ev-search-select{position:relative;width:100%;min-width:0;font-family:inherit}
      .ev-search-select-native{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}
      .ev-search-select-control{display:grid;grid-template-columns:minmax(0,1fr) 42px;align-items:center;min-height:46px;border:1px solid #CFE9D9;border-radius:14px;background:#fff;box-shadow:0 7px 18px rgba(15,23,42,.035);transition:border-color .16s ease,box-shadow .16s ease,transform .16s ease}
      .ev-search-select.is-open .ev-search-select-control,.ev-search-select-control:focus-within{border-color:#16A34A;box-shadow:0 0 0 4px rgba(22,163,74,.12),0 10px 24px rgba(15,23,42,.06)}
      .ev-search-select-input{width:100%;min-width:0;height:44px;padding:0 8px 0 14px;border:0!important;outline:0!important;background:transparent!important;color:#111827;font:inherit;font-size:.88rem;font-weight:750;box-shadow:none!important}
      .ev-search-select-input::placeholder{color:#94A3B8;font-weight:650}
      .ev-search-select-toggle{width:42px;height:44px;display:grid;place-items:center;padding:0;border:0;background:transparent;color:#0F592F;cursor:pointer;border-radius:0 13px 13px 0}
      .ev-search-select-toggle:hover{background:#F0FDF4}
      .ev-search-select-toggle i{transition:transform .16s ease}.ev-search-select.is-open .ev-search-select-toggle i{transform:rotate(180deg)}
      .ev-search-select-panel{position:absolute;z-index:1095;left:0;right:0;top:calc(100% + 7px);max-height:270px;overflow:auto;padding:7px;border:1px solid #DCE7E0;border-radius:15px;background:#fff;box-shadow:0 22px 50px rgba(15,23,42,.18);scrollbar-width:thin;scrollbar-color:rgba(15,89,47,.28) transparent}
      .ev-search-select-panel[hidden]{display:none!important}
      .ev-search-select-option{width:100%;display:flex;align-items:flex-start;gap:9px;padding:10px 11px;border:0;border-radius:11px;background:#fff;color:#334155;text-align:left;font:inherit;font-size:.82rem;font-weight:720;line-height:1.35;cursor:pointer;transition:background .14s ease,color .14s ease}
      .ev-search-select-option:hover,.ev-search-select-option.is-active{background:#F0FDF4;color:#0F592F}
      .ev-search-select-option.is-selected{background:#EAF8EF;color:#0F592F;font-weight:900}
      .ev-search-select-option i{margin-top:1px;color:#16A34A;flex:0 0 auto}
      .ev-search-select-empty{padding:16px 12px;text-align:center;color:#64748B;font-size:.82rem;font-weight:700}
      @media(max-width:575.98px){.ev-search-select-panel{max-height:235px}.ev-search-select-input{font-size:.84rem}.ev-search-select-option{font-size:.8rem}}
    `;
    document.head.appendChild(style);
  }

  function normalize(value) {
    return String(value || '').toLocaleLowerCase('es').normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
  }

  function optionData(select) {
    return Array.from(select.options)
      .filter((option) => !option.disabled && String(option.value || '').trim() !== '')
      .map((option) => ({ value: String(option.value), text: String(option.textContent || '').trim(), option }));
  }

  function initOne(select) {
    if (!select || select.dataset.evSearchableReady === '1') return null;
    select.dataset.evSearchableReady = '1';
    injectStyles();

    const wrapper = document.createElement('div');
    wrapper.className = 'ev-search-select';
    wrapper.dataset.evSearchSelect = '1';

    const control = document.createElement('div');
    control.className = 'ev-search-select-control';

    const input = document.createElement('input');
    input.type = 'search';
    input.className = 'ev-search-select-input';
    input.autocomplete = 'off';
    input.spellcheck = false;
    input.placeholder = select.dataset.searchPlaceholder || 'Buscar y seleccionar...';
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'ev-search-select-toggle';
    toggle.setAttribute('aria-label', 'Mostrar opciones');
    toggle.innerHTML = '<i class="bi bi-chevron-down" aria-hidden="true"></i>';

    const panel = document.createElement('div');
    panel.className = 'ev-search-select-panel';
    panel.hidden = true;
    panel.setAttribute('role', 'listbox');

    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
    wrapper.appendChild(control);
    control.appendChild(input);
    control.appendChild(toggle);
    wrapper.appendChild(panel);
    select.classList.add('ev-search-select-native');

    let filtered = [];
    let activeIndex = -1;

    function selectedText() {
      const selected = select.options[select.selectedIndex];
      return selected && String(selected.value || '').trim() !== '' ? String(selected.textContent || '').trim() : '';
    }

    function syncInput() {
      input.value = selectedText();
    }

    function close({ restore = true } = {}) {
      panel.hidden = true;
      wrapper.classList.remove('is-open');
      input.setAttribute('aria-expanded', 'false');
      activeIndex = -1;
      if (restore) syncInput();
      if (openInstance === api) openInstance = null;
    }

    function selectValue(value) {
      if (select.value === value) {
        syncInput();
        close({ restore: false });
        return;
      }
      select.value = value;
      syncInput();
      close({ restore: false });
      select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function render(query = '') {
      const q = normalize(query);
      filtered = optionData(select).filter((item) => !q || normalize(item.text).includes(q));
      panel.innerHTML = '';
      activeIndex = -1;

      if (!filtered.length) {
        panel.innerHTML = '<div class="ev-search-select-empty">No encontramos coincidencias.</div>';
        return;
      }

      filtered.forEach((item, index) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ev-search-select-option';
        if (item.value === select.value) button.classList.add('is-selected');
        button.dataset.value = item.value;
        button.innerHTML = `<i class="bi bi-buildings" aria-hidden="true"></i><span></span>`;
        button.querySelector('span').textContent = item.text;
        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => selectValue(item.value));
        panel.appendChild(button);
        if (item.value === select.value) activeIndex = index;
      });
    }

    function setActive(nextIndex) {
      const buttons = Array.from(panel.querySelectorAll('.ev-search-select-option'));
      if (!buttons.length) return;
      activeIndex = Math.max(0, Math.min(buttons.length - 1, nextIndex));
      buttons.forEach((button, index) => button.classList.toggle('is-active', index === activeIndex));
      buttons[activeIndex]?.scrollIntoView({ block: 'nearest' });
    }

    function open({ keepValue = false } = {}) {
      if (openInstance && openInstance !== api) openInstance.close();
      openInstance = api;
      wrapper.classList.add('is-open');
      panel.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      if (!keepValue) input.select();
      render(keepValue ? input.value : '');
    }

    input.addEventListener('focus', () => open({ keepValue: false }));
    input.addEventListener('input', () => {
      if (panel.hidden) open({ keepValue: true });
      render(input.value);
    });
    input.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (panel.hidden) open({ keepValue: true });
        setActive(activeIndex < 0 ? 0 : activeIndex + 1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (panel.hidden) open({ keepValue: true });
        setActive(activeIndex < 0 ? filtered.length - 1 : activeIndex - 1);
      } else if (event.key === 'Enter' && !panel.hidden) {
        event.preventDefault();
        const item = filtered[activeIndex >= 0 ? activeIndex : 0];
        if (item) selectValue(item.value);
      } else if (event.key === 'Escape') {
        event.preventDefault();
        close();
      }
    });
    toggle.addEventListener('click', () => {
      if (panel.hidden) {
        input.focus();
        open({ keepValue: false });
      } else {
        close();
      }
    });
    select.addEventListener('change', syncInput);
    select.addEventListener('ev:options-updated', () => {
      syncInput();
      if (!panel.hidden) render(input.value);
    });

    const api = { select, input, panel, wrapper, open, close, refresh: syncInput };
    wrapper.__evSearchSelect = api;
    syncInput();
    return api;
  }

  function init(root = document) {
    const scope = root?.querySelectorAll ? root : document;
    scope.querySelectorAll('select.ev-searchable-select').forEach(initOne);
  }

  document.addEventListener('pointerdown', (event) => {
    if (openInstance && !openInstance.wrapper.contains(event.target)) openInstance.close();
  }, true);
  document.addEventListener('DOMContentLoaded', () => init(document));
  document.addEventListener('ev:content-loaded', (event) => init(event.detail?.container || document));

  window.EVSearchableSelect = { init, initOne };
})();
