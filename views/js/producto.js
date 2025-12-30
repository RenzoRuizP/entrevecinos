/* producto.js – EV (robusto + sin rebote + no rompe menú) */

(() => {
  'use strict';

  const EV_API_BASE = (window.BASE_URL || '').replace(/\/$/, '');

  function evNotify(icon, title, text) {
    if (window.Swal?.fire) {
      Swal.fire({
        icon,
        title,
        text,
        confirmButtonText: 'Aceptar',
        customClass: { confirmButton: 'btn btn-outline-success' },
        buttonsStyling: false
      });
    } else {
      alert(title ? `${title}\n\n${text}` : text);
    }
  }

  async function evConfirm({
    icon = 'question',
    title = 'Confirmar',
    text = '',
    confirmText = 'Sí',
    cancelText = 'Cancelar',
    confirmBtnClass = 'btn btn-success me-2'
  } = {}) {
    if (window.Swal?.fire) {
      const { isConfirmed } = await Swal.fire({
        icon,
        title,
        text,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        customClass: { confirmButton: confirmBtnClass, cancelButton: 'btn btn-outline-secondary' },
        buttonsStyling: false
      });
      return !!isConfirmed;
    }
    return window.confirm(`${title}\n\n${text}`);
  }

  /* ==============================
     VH estable (anti rebote)
  ============================== */
  function setEvVh() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--ev-vh', `${vh}px`);
  }
  setEvVh();
  window.addEventListener('resize', setEvVh);
  window.addEventListener('orientationchange', setEvVh);
  document.addEventListener('shown.bs.modal', setEvVh);

  /* =========================================================
     FIX RAÍZ: Modales SIEMPRE en <body>
     - Evita que "transform" de contenedores (fade-in / wrappers)
       rompa el centrado de Bootstrap modal.
  ========================================================= */
  function evMountModalToBody(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    if (el.parentElement !== document.body) {
      document.body.appendChild(el);
    }
  }

  function evMountAllModalsToBody() {
    evMountModalToBody('modalBuscarPublicacion');
    evMountModalToBody('modalAgregarPublicacion');
    evMountModalToBody('modalEditarPublicacion');
  }

  /* ==============================
     Helpers fotos
  ============================== */
  function evGetFotoSrc(f) {
    if (!f) return '';
    if (typeof f === 'string') return f;
    return f.url || f.ruta || f.ruta_imagen || f.imagen || f.path || f.path_imagen || '';
  }
  function evGetFotoId(f, idx) {
    if (!f || typeof f !== 'object') return 'old_' + idx;
    return f.id_imagen || f.codigo_imagen || f.id || f.codigo || ('old_' + idx);
  }

  function evSyncDropzoneState(tilesEl) {
    if (!tilesEl) return;
    const section = tilesEl.closest('.ev-section');
    if (!section) return;
    const hasTiles = !!tilesEl.querySelector('.ev-tile');
    section.classList.toggle('ev-has-tiles', hasTiles);
  }

  /* =========================================================
     PREVIEW AGREGAR
  ========================================================= */
  const evAddPreview = { inited: false };

  function evEnsurePreviewAgregar() {
    if (evAddPreview.inited) return evAddPreview;

    const container = document.getElementById('previewMount');
    if (!container) return null;

    container.innerHTML = `
      <div id="evPreviewWrapperAdd" class="ev-preview-area">
        <div class="ev-preview-title"><span><i class="bi bi-images me-1"></i>Previsualización</span></div>
        <div class="ev-preview-main"><img id="evPreviewMainImgAdd" alt="Vista previa"></div>
        <div id="evPreviewThumbsAdd" class="ev-preview-thumbs"></div>
      </div>
      <div class="card ev-card mt-3">
        <div class="card-body p-3">
          <h6 id="evMetaTitleAdd" class="mb-1" style="font-weight:800;color:#0b3d27;">Título</h6>
          <div id="evMetaPriceAdd" class="mb-2" style="color:#0F592F;font-weight:800;">S/ 0.00</div>
          <div style="font-size:.9rem;color:#64748b">Detalles</div>
          <p id="evMetaDescAdd" class="mb-0" style="color:#475569;">La descripción aparecerá aquí.</p>
        </div>
      </div>
    `;

    evAddPreview.wrapper   = document.getElementById('evPreviewWrapperAdd');
    evAddPreview.mainImg   = document.getElementById('evPreviewMainImgAdd');
    evAddPreview.thumbs    = document.getElementById('evPreviewThumbsAdd');
    evAddPreview.metaTitle = document.getElementById('evMetaTitleAdd');
    evAddPreview.metaPrice = document.getElementById('evMetaPriceAdd');
    evAddPreview.metaDesc  = document.getElementById('evMetaDescAdd');
    evAddPreview.inited    = true;

    const modal = document.getElementById('modalAgregarPublicacion');
    if (modal && !modal.dataset.evMetaBound) {
      modal.dataset.evMetaBound = '1';

      const updateMetaLive = () => {
        const titleInput = modal.querySelector('input[name="titulo"]');
        const priceInput = modal.querySelector('input[name="precio"]');
        const descInput  = modal.querySelector('textarea[name="descripcion"]');

        const title    = titleInput?.value?.trim() || 'Título';
        const priceRaw = priceInput?.value || '';
        const desc     = descInput?.value?.trim() || 'La descripción aparecerá aquí.';

        const n      = Number(priceRaw || 0);
        const precio = isNaN(n) ? '0.00' : n.toFixed(2);

        evAddPreview.metaTitle.textContent = title;
        evAddPreview.metaPrice.textContent = `S/ ${precio}`;
        evAddPreview.metaDesc.textContent  = desc;
      };

      modal.querySelector('input[name="titulo"]')?.addEventListener('input', updateMetaLive);
      modal.querySelector('input[name="precio"]')?.addEventListener('input', updateMetaLive);
      modal.querySelector('textarea[name="descripcion"]')?.addEventListener('input', updateMetaLive);
    }

    return evAddPreview;
  }

  function evPintarPreviewAgregar(fotos) {
    const st = evEnsurePreviewAgregar();
    if (!st) return;

    if (!Array.isArray(fotos) || !fotos.length) {
      st.wrapper.style.display = 'none';
      st.thumbs.innerHTML = '';
      st.mainImg.src = '';
      return;
    }

    st.wrapper.style.display = '';
    st.mainImg.src = fotos[0]?.url || '';

    st.thumbs.innerHTML = '';
    fotos.forEach((f, idx) => {
      const src = f?.url || '';
      if (!src) return;
      const d = document.createElement('div');
      d.className = 'ev-preview-thumb' + (idx === 0 ? ' active' : '');
      const im = document.createElement('img');
      im.src = src;
      d.appendChild(im);
      d.addEventListener('click', () => {
        st.mainImg.src = src;
        [...st.thumbs.querySelectorAll('.ev-preview-thumb')].forEach((node, i) => {
          node.classList.toggle('active', i === idx);
        });
      });
      st.thumbs.appendChild(d);
    });
  }

  /* =========================================================
     Uploader AGREGAR
  ========================================================= */
  (function initUploaderAgregarModule() {
    const MAX_MB = 5;
    const MAX_FILES = 10;

    const state = { inited:false, fotos:[] };
    const els = { modal:null, form:null, input:null, tiles:null, dropZone:null, btnClear:null, cntHeader:null, cntToolbar:null };

    function validarArchivo(file) {
      const okTipo = (file.type || '').startsWith('image/');
      const okPeso = file.size <= MAX_MB * 1024 * 1024;
      if (!okTipo) { evNotify('info','Aviso','Solo se permiten archivos de imagen.'); return false; }
      if (!okPeso) { evNotify('info','Aviso',`"${file.name}" supera ${MAX_MB} MB.`); return false; }
      return true;
    }

    function setCount() {
      const count = state.fotos.length;
      if (els.cntHeader)  els.cntHeader.textContent  = String(count);
      if (els.cntToolbar) els.cntToolbar.textContent = String(count);
      if (els.form)       els.form.dataset.evFotosAddCount = String(count);
    }

    function revokeAll() { state.fotos.forEach(f => { if (f?.url) URL.revokeObjectURL(f.url); }); }

    function renderTiles() {
      if (!els.tiles) return;
      els.tiles.innerHTML = '';

      if (state.fotos.length === 0) {
        setCount();
        evSyncDropzoneState(els.tiles);
        return;
      }

      state.fotos.forEach((f) => {
        const tile = document.createElement('div');
        tile.className = 'ev-tile';

        const img = document.createElement('img');
        img.src = f.url;

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ev-tile-remove';
        del.textContent = '×';
        del.addEventListener('click', (ev) => {
          ev.stopPropagation();
          const idx = state.fotos.findIndex(x => x.id === f.id);
          if (idx !== -1) {
            const removed = state.fotos.splice(idx, 1)[0];
            if (removed?.url) URL.revokeObjectURL(removed.url);
          }
          paint();
        });

        tile.append(img, del);
        els.tiles.appendChild(tile);
      });

      if (state.fotos.length < MAX_FILES) {
        const add = document.createElement('div');
        add.className = 'ev-tile ev-tile-add';
        add.innerHTML = `
          <div class="ico"><i class="bi bi-plus-lg"></i></div>
          <div class="t1">Agregar fotos</div>
          <div class="t2">o arrastra y suelta</div>
        `;
        add.addEventListener('click', () => els.input?.click());
        els.tiles.appendChild(add);
      }

      setCount();
      evSyncDropzoneState(els.tiles);
    }

    function paint() {
      renderTiles();
      evPintarPreviewAgregar(state.fotos);
    }

    function agregarArchivos(fileList) {
      const nuevos = Array.from(fileList || []);
      if (!nuevos.length) return;

      for (const file of nuevos) {
        if (state.fotos.length >= MAX_FILES) {
          evNotify('info','Aviso',`Máximo ${MAX_FILES} imágenes.`);
          break;
        }
        if (!validarArchivo(file)) continue;

        state.fotos.push({
          id: 'new_' + (crypto?.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random()),
          file,
          url: URL.createObjectURL(file)
        });
      }

      paint();
    }

    function clearAll() {
      revokeAll();
      state.fotos = [];
      if (els.input) els.input.value = '';
      paint();
    }

    function bindEvents() {
      if (state.inited || !els.modal) return;

      els.dropZone?.addEventListener('click', () => els.input?.click());

      els.input?.addEventListener('change', (e) => {
        agregarArchivos(e.target.files || []);
        e.target.value = '';
      });

      const bindDrop = (node) => {
        if (!node) return;
        ['dragenter','dragover'].forEach(evt => {
          node.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); node.classList.add('drag-over'); });
        });
        ['dragleave','dragend','drop'].forEach(evt => {
          node.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); node.classList.remove('drag-over'); });
        });
        node.addEventListener('drop', (e) => {
          agregarArchivos(e.dataTransfer?.files || []);
        });
      };
      bindDrop(els.dropZone);

      if (els.tiles) {
        ['dragenter','dragover','dragleave','dragend','drop'].forEach(evt => {
          els.tiles.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); });
        });
        els.tiles.addEventListener('drop', (e) => {
          agregarArchivos(e.dataTransfer?.files || []);
        });
      }

      els.btnClear?.addEventListener('click', clearAll);

      els.modal.addEventListener('hidden.bs.modal', () => {
        clearAll();
        try { els.form?.reset(); } catch (_) {}
      });

      state.inited = true;
      paint();
    }

    function hydrateRefs() {
      const modal = document.getElementById('modalAgregarPublicacion');
      if (!modal) return false;

      els.modal      = modal;
      els.form       = modal.querySelector('#formAgregarPublicacion');
      els.input      = modal.querySelector('#inputImagenes');
      els.tiles      = modal.querySelector('#evTiles');
      els.dropZone   = modal.querySelector('#dropZone');
      els.btnClear   = modal.querySelector('#btnLimpiarImagenes');
      els.cntHeader  = modal.querySelector('#contadorImagenes');
      els.cntToolbar = modal.querySelector('#contadorImagenesToolbar');

      return !!(els.form && els.input && els.dropZone && els.tiles);
    }

    window.evGetEstadoImagenesAgregar = function () {
      return { nuevas: state.fotos.slice() };
    };

    document.addEventListener('shown.bs.modal', (e) => {
      if (e.target && e.target.id === 'modalAgregarPublicacion') {
        if (hydrateRefs()) bindEvents();
      }
    });

  })();

  /* ==============================
     PREVIEW EDITAR
  ============================== */
  const evEditPreview = { inited:false };

  function evEnsurePreviewEditar() {
    if (evEditPreview.inited) return evEditPreview;

    const container = document.getElementById('evPreviewWrapperEditContainer');
    if (!container) return null;

    container.innerHTML = `
      <div id="evPreviewWrapperEdit" class="ev-preview-area">
        <div class="ev-preview-title"><span><i class="bi bi-images me-1"></i>Previsualización</span></div>
        <div class="ev-preview-main"><img id="evPreviewMainImgEdit" alt="Vista previa"></div>
        <div id="evPreviewThumbsEdit" class="ev-preview-thumbs"></div>
      </div>
      <div class="card ev-card mt-3">
        <div class="card-body p-3">
          <h6 id="evMetaTitleEdit" class="mb-1" style="font-weight:800;color:#0b3d27;">Título</h6>
          <div id="evMetaPriceEdit" class="mb-2" style="color:#0F592F;font-weight:800;">S/ 0.00</div>
          <div style="font-size:.9rem;color:#64748b">Detalles</div>
          <p id="evMetaDescEdit" class="mb-0" style="color:#475569;">La descripción aparecerá aquí.</p>
        </div>
      </div>
    `;

    evEditPreview.wrapper   = document.getElementById('evPreviewWrapperEdit');
    evEditPreview.mainImg   = document.getElementById('evPreviewMainImgEdit');
    evEditPreview.thumbs    = document.getElementById('evPreviewThumbsEdit');
    evEditPreview.metaTitle = document.getElementById('evMetaTitleEdit');
    evEditPreview.metaPrice = document.getElementById('evMetaPriceEdit');
    evEditPreview.metaDesc  = document.getElementById('evMetaDescEdit');
    evEditPreview.inited    = true;

    const modal = document.getElementById('modalEditarPublicacion');
    if (modal && !modal.dataset.evMetaBound) {
      modal.dataset.evMetaBound = '1';
      const updateMetaLive = () => {
        const title   = modal.querySelector('#edit_titulo')?.value?.trim() || 'Título';
        const priceRaw= modal.querySelector('#edit_precio')?.value || '';
        const desc    = modal.querySelector('#edit_descripcion')?.value?.trim() || 'La descripción aparecerá aquí.';
        const n       = Number(priceRaw || 0);
        const precio  = isNaN(n) ? '0.00' : n.toFixed(2);

        evEditPreview.metaTitle.textContent = title;
        evEditPreview.metaPrice.textContent = `S/ ${precio}`;
        evEditPreview.metaDesc.textContent  = desc;
      };

      modal.querySelector('#edit_titulo')?.addEventListener('input', updateMetaLive);
      modal.querySelector('#edit_precio')?.addEventListener('input', updateMetaLive);
      modal.querySelector('#edit_descripcion')?.addEventListener('input', updateMetaLive);
    }

    return evEditPreview;
  }

  function evPintarPreviewEditar(prod, fotos) {
    const st = evEnsurePreviewEditar();
    if (!st) return;

    const titulo = (prod?.titulo || '').trim() || 'Título';
    const n      = Number(prod?.precio || 0);
    const precio = isNaN(n) ? '0.00' : n.toFixed(2);
    const desc   = (prod?.descripcion || '').trim() || 'La descripción aparecerá aquí.';

    st.metaTitle.textContent = titulo;
    st.metaPrice.textContent = `S/ ${precio}`;
    st.metaDesc.textContent  = desc;

    if (!Array.isArray(fotos) || !fotos.length) {
      st.wrapper.style.display = 'none';
      st.thumbs.innerHTML = '';
      st.mainImg.src = '';
      return;
    }

    st.wrapper.style.display = '';
    st.mainImg.src = evGetFotoSrc(fotos[0]) || '';

    st.thumbs.innerHTML = '';
    fotos.forEach((f, idx) => {
      const src = evGetFotoSrc(f);
      if (!src) return;
      const d = document.createElement('div');
      d.className = 'ev-preview-thumb' + (idx === 0 ? ' active' : '');
      const im = document.createElement('img');
      im.src = src;
      d.appendChild(im);
      d.addEventListener('click', () => {
        st.mainImg.src = src;
        [...st.thumbs.querySelectorAll('.ev-preview-thumb')].forEach((node, i) => {
          node.classList.toggle('active', i === idx);
        });
      });
      st.thumbs.appendChild(d);
    });
  }

  /* ==============================
     Uploader EDITAR (estado)
  ============================== */
  (function initUploaderEditarModule(){
    const MAX_MB = 5;
    const MAX_FILES = 10;

    const state = { inited:false, fotosExistentes:[], fotosNuevas:[], eliminadas:[] };
    const els = { modal:null, form:null, input:null, tiles:null, dropZone:null, btnClear:null, cntHeader:null, cntToolbar:null };

    function validarArchivo(file) {
      const okTipo = (file.type || '').startsWith('image/');
      const okPeso = file.size <= MAX_MB * 1024 * 1024;
      if (!okTipo) { evNotify('info','Aviso','Solo se permiten archivos de imagen.'); return false; }
      if (!okPeso) { evNotify('info','Aviso',`"${file.name}" supera ${MAX_MB} MB.`); return false; }
      return true;
    }

    function buildActivasExistentes(){
      return state.fotosExistentes.filter(f => !state.eliminadas.includes(f.id));
    }
    function buildTodas() {
      const existentes = buildActivasExistentes().map(f => ({ url: f.src }));
      const nuevas     = state.fotosNuevas.map(f => ({ url: f.url }));
      return existentes.concat(nuevas);
    }

    function setCount() {
      const count = buildTodas().length;
      if (els.cntHeader)  els.cntHeader.textContent  = String(count);
      if (els.cntToolbar) els.cntToolbar.textContent = String(count);
      if (els.form)       els.form.dataset.evFotosEditCount = String(count);
    }

    function renderTiles(prod) {
      if (!els.tiles) return;
      els.tiles.innerHTML = '';

      const todas = buildTodas();
      const existentesActivos = buildActivasExistentes();

      existentesActivos.forEach((f) => {
        const tile = document.createElement('div');
        tile.className = 'ev-tile';

        const img = document.createElement('img');
        img.src = f.src;

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ev-tile-remove';
        del.textContent = '×';
        del.addEventListener('click', (ev) => {
          ev.stopPropagation();
          if (!state.eliminadas.includes(f.id)) state.eliminadas.push(f.id);
          paint(prod);
        });

        tile.append(img, del);
        els.tiles.appendChild(tile);
      });

      state.fotosNuevas.forEach((f) => {
        const tile = document.createElement('div');
        tile.className = 'ev-tile';

        const img = document.createElement('img');
        img.src = f.url;

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ev-tile-remove';
        del.textContent = '×';
        del.addEventListener('click', (ev) => {
          ev.stopPropagation();
          const idx = state.fotosNuevas.findIndex(x => x.id === f.id);
          if (idx !== -1) {
            const removed = state.fotosNuevas.splice(idx,1)[0];
            if (removed?.url) URL.revokeObjectURL(removed.url);
          }
          paint(prod);
        });

        tile.append(img, del);
        els.tiles.appendChild(tile);
      });

      if (todas.length < MAX_FILES) {
        const add = document.createElement('div');
        add.className = 'ev-tile ev-tile-add';
        add.innerHTML = `
          <div class="ico"><i class="bi bi-plus-lg"></i></div>
          <div class="t1">Agregar fotos</div>
          <div class="t2">o arrastra y suelta</div>
        `;
        add.addEventListener('click', () => els.input?.click());
        els.tiles.appendChild(add);
      }

      setCount();
      evSyncDropzoneState(els.tiles);
    }

    function paint(prod) {
      const todas = buildTodas();
      renderTiles(prod);
      evPintarPreviewEditar(prod, todas);
    }

    function agregarArchivos(fileList, prod) {
      const nuevos = Array.from(fileList || []);
      if (!nuevos.length) return;

      const actuales = buildTodas().length;

      for (const file of nuevos) {
        if (actuales + state.fotosNuevas.length >= MAX_FILES) {
          evNotify('info','Aviso',`Máximo ${MAX_FILES} imágenes.`);
          break;
        }
        if (!validarArchivo(file)) continue;

        state.fotosNuevas.push({
          id: 'new_' + (crypto?.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random()),
          file,
          url: URL.createObjectURL(file)
        });
      }

      paint(prod);
    }

    function bindEvents() {
      if (state.inited || !els.modal) return;

      els.input?.addEventListener('change', (e) => {
        const prod = els.modal?._evProdActual || {};
        agregarArchivos(e.target.files || [], prod);
        e.target.value = '';
      });

      const bindDrop = (node) => {
        if (!node) return;
        ['dragenter','dragover'].forEach(evt => {
          node.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); node.classList.add('drag-over'); });
        });
        ['dragleave','dragend','drop'].forEach(evt => {
          node.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); node.classList.remove('drag-over'); });
        });
        node.addEventListener('drop', (e) => {
          const prod = els.modal?._evProdActual || {};
          agregarArchivos(e.dataTransfer?.files || [], prod);
        });
        node.addEventListener('click', () => els.input?.click());
      };

      bindDrop(els.dropZone);

      if (els.tiles) {
        ['dragenter','dragover','dragleave','dragend','drop'].forEach(evt => {
          els.tiles.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); });
        });
        els.tiles.addEventListener('drop', (e) => {
          const prod = els.modal?._evProdActual || {};
          agregarArchivos(e.dataTransfer?.files || [], prod);
        });
      }

      els.btnClear?.addEventListener('click', () => {
        state.fotosExistentes = [];
        state.fotosNuevas.forEach(f => f.url && URL.revokeObjectURL(f.url));
        state.fotosNuevas = [];
        state.eliminadas = [];
        paint(els.modal?._evProdActual || {});
      });

      state.inited = true;
    }

    function resetStateFromBackend(prod, fotosBackend) {
      state.fotosExistentes = [];
      state.fotosNuevas.forEach(f => f.url && URL.revokeObjectURL(f.url));
      state.fotosNuevas = [];
      state.eliminadas = [];

      if (Array.isArray(fotosBackend)) {
        fotosBackend.forEach((f, idx) => {
          const src = evGetFotoSrc(f);
          if (!src) return;
          const id  = evGetFotoId(f, idx);
          state.fotosExistentes.push({ id, src });
        });
      }

      if (els.modal) els.modal._evProdActual = prod || {};
      paint(prod || {});

      window.evGetEstadoImagenesEditar = function () {
        return {
          existentes: state.fotosExistentes,
          eliminadas: state.eliminadas.slice(),
          nuevas: state.fotosNuevas
        };
      };
    }

    window.evInitUploaderEditar = function(prod, fotosBackend) {
      const modal = document.getElementById('modalEditarPublicacion');
      if (!modal) return;

      els.modal      = modal;
      els.form       = modal.querySelector('#formEditarPublicacion');
      els.input      = modal.querySelector('#inputImagenesEdit');
      els.tiles      = modal.querySelector('#evTilesEdit');
      els.dropZone   = modal.querySelector('#dropZoneEdit');
      els.btnClear   = modal.querySelector('#btnLimpiarImagenesEdit');
      els.cntHeader  = modal.querySelector('#contadorImagenesEdit');
      els.cntToolbar = modal.querySelector('#contadorImagenesToolbarEdit');

      bindEvents();
      resetStateFromBackend(prod, fotosBackend);
    };
  })();

  /* ==============================
     API: Cargar edición
  ============================== */
  async function cargarProductoEditar(codProducto) {
    try {
      const resp = await fetch(`${EV_API_BASE}/api/producto/${codProducto}`, { method: 'GET' });
      const data = await resp.json();

      if (!data.ok) {
        evNotify("error", "Error", data.mensaje || "Error al cargar el producto.");
        return;
      }

      const prod  = data.data.producto;
      const fotos = data.data.imagenes || [];

      document.querySelector("#edit_id").value          = prod.codigo_producto;
      document.querySelector("#edit_titulo").value      = prod.titulo || "";
      document.querySelector("#edit_precio").value      = prod.precio || "";
      document.querySelector("#edit_estado").value      = prod.estado || "Nuevo";
      document.querySelector("#edit_descripcion").value = prod.descripcion || "";

      const comboTipo = document.getElementById("edit_comboTipo");
      const comboCat  = document.getElementById("edit_comboCategoria");
      if (comboTipo) comboTipo.dataset.valorRegistrado = prod.codigo_tipo || "";
      if (comboCat)  comboCat.dataset.valorRegistrado  = prod.codigo_categoria || "";

      if (window.evInitUploaderEditar) window.evInitUploaderEditar(prod, fotos);
      else evPintarPreviewEditar(prod, fotos);

      const modalEl = document.getElementById("modalEditarPublicacion");
      if (modalEl) {
        evMountAllModalsToBody(); // ✅ aseguramos que esté en body antes de mostrar
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        requestAnimationFrame(() => {
          modal.show();
          requestAnimationFrame(setEvVh);
        });
      }

      if (window.evInitComboTipoCategoriaEdit) {
        window.evInitComboTipoCategoriaEdit(prod.codigo_tipo, prod.codigo_categoria);
      }

    } catch (e) {
      console.error("Error cargando producto:", e);
      evNotify("error", "Error", "No se pudo cargar los datos.");
    }
  }

  /* ==============================
     Acciones: anular
  ============================== */
  async function confirmarYAnular(id) {
    if (!id) return;

    const ok = await evConfirm({
      icon: 'warning',
      title: 'Anular producto',
      text: '¿Seguro que deseas anular este producto? Ya no estará disponible.',
      confirmText: 'Sí, anular',
      cancelText: 'Cancelar',
      confirmBtnClass: 'btn btn-danger me-2'
    });
    if (!ok) return;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/producto/${id}/anular`, { method: 'POST' });
      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        evNotify('error', 'Error', data.mensaje || data.error || 'No se pudo anular el producto.');
        return;
      }

      evNotify('success', 'Producto anulado', data.mensaje || 'El producto ha sido anulado correctamente.');
      window.evCargarProductos?.();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al anular el producto.');
    }
  }

  /* ==============================
     Acciones: publicar (0 -> 1)
  ============================== */
  async function confirmarYPublicar(id) {
    if (!id) return;

    const ok = await evConfirm({
      icon: 'question',
      title: 'Publicar producto',
      text: 'Al publicar, el producto pasará a revisión y quedará en estado Pendiente hasta que el administrador lo apruebe.',
      confirmText: 'Sí, publicar',
      cancelText: 'Cancelar',
      confirmBtnClass: 'btn btn-success me-2'
    });
    if (!ok) return;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/producto/${id}/publicar`, { method: 'POST' });
      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        evNotify('error', 'Error', data.mensaje || data.error || 'No se pudo publicar el producto.');
        return;
      }

      evNotify('success', 'Publicado', data.mensaje || 'Producto enviado a revisión (Pendiente).');
      window.evCargarProductos?.();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al publicar el producto.');
    }
  }

  /* ==============================
     Submit AGREGAR (API)
  ============================== */
  async function registrarProducto(form) {
    const btnGuardar = form.querySelector('.btn-guardar') || form.querySelector('button[type="submit"]');

    const setSaving = (saving) => {
      if (!btnGuardar) return;
      btnGuardar.disabled = saving;
      btnGuardar.classList.toggle('saving', saving);
    };

    const titulo      = form.querySelector('input[name="titulo"]')?.value?.trim() || '';
    const precioRaw   = form.querySelector('input[name="precio"]')?.value || '';
    const estado      = form.querySelector('select[name="estado"]')?.value || 'NoAplica';
    const descripcion = form.querySelector('textarea[name="descripcion"]')?.value?.trim() || '';

    const comboTipo   = form.querySelector('#comboTipo')?.value || form.querySelector('select[name="comboTipo"]')?.value || '';
    const categoria   = form.querySelector('#comboCategoria')?.value || form.querySelector('select[name="categoria"]')?.value || '';

    if (!titulo) { evNotify('warning','Validación','Debes ingresar un título para el producto.'); return; }

    const precio = Number(precioRaw || 0);
    if (!precio || precio <= 0) { evNotify('warning','Validación','El precio debe ser mayor a 0.'); return; }

    if (!comboTipo) { evNotify('warning','Validación','Debes seleccionar un tipo.'); return; }
    if (!categoria) { evNotify('warning','Validación','Debes seleccionar una categoría.'); return; }

    if (!descripcion) { evNotify('warning','Validación','Debes ingresar una descripción.'); return; }

    const estadoImgs = typeof window.evGetEstadoImagenesAgregar === 'function'
      ? window.evGetEstadoImagenesAgregar()
      : { nuevas: [] };

    const nuevas = Array.isArray(estadoImgs.nuevas) ? estadoImgs.nuevas : [];

    const fd = new FormData();
    fd.append('titulo', titulo);
    fd.append('precio', precio.toString());
    fd.append('estado', estado);
    fd.append('comboTipo', comboTipo);
    fd.append('categoria', categoria);
    fd.append('descripcion', descripcion);

    nuevas.forEach((item) => {
      if (item && item.file instanceof File) fd.append('imagenes[]', item.file);
    });

    try {
      setSaving(true);

      const resp = await fetch(`${EV_API_BASE}/api/producto/registrar`, {
        method: 'POST',
        body: fd
      });

      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        const extra = Array.isArray(data.errores) && data.errores.length ? `\n\n• ${data.errores.join('\n• ')}` : '';
        evNotify('error', 'Error', (data.mensaje || data.error || 'No se pudo registrar el producto.') + extra);
        return;
      }

      evNotify('success', 'Producto registrado', data.mensaje || 'Producto registrado como borrador. Presiona "Publicar" para enviarlo a revisión.');

      const modalEl = document.getElementById('modalAgregarPublicacion');
      if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
      }

      window.evCargarProductos?.();
      try { form.reset(); } catch (_) {}

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al registrar el producto.');
    } finally {
      setSaving(false);
    }
  }

  /* ==============================
     Submit EDITAR (API)
  ============================== */
  async function actualizarProducto(form) {
    const btnGuardar = form.querySelector('.btn-guardar') || form.querySelector('button[type="submit"]');

    const setSaving = (saving) => {
      if (!btnGuardar) return;
      btnGuardar.disabled = saving;
      btnGuardar.classList.toggle('saving', saving);
    };

    const id          = form.querySelector('#edit_id')?.value || '';
    const titulo      = form.querySelector('#edit_titulo')?.value?.trim() || '';
    const precioRaw   = form.querySelector('#edit_precio')?.value || '';
    const estado      = form.querySelector('#edit_estado')?.value || 'NoAplica';
    const descripcion = form.querySelector('#edit_descripcion')?.value?.trim() || '';

    const comboTipo   = form.querySelector('#edit_comboTipo')?.value || '';
    const categoria   = form.querySelector('#edit_comboCategoria')?.value || '';

    if (!id) { evNotify('error','Error','No se encontró el código del producto.'); return; }
    if (!titulo) { evNotify('warning','Validación','Debes ingresar un título.'); return; }

    const precio = Number(precioRaw || 0);
    if (!precio || precio <= 0) { evNotify('warning','Validación','El precio debe ser mayor a 0.'); return; }
    if (!descripcion) { evNotify('warning','Validación','Debes ingresar una descripción.'); return; }

    const estadoImgs = typeof window.evGetEstadoImagenesEditar === 'function'
      ? window.evGetEstadoImagenesEditar()
      : { eliminadas: [], nuevas: [] };

    const fd = new FormData();
    fd.append('titulo', titulo);
    fd.append('precio', precio.toString());
    fd.append('estado', estado);
    fd.append('comboTipo', comboTipo);
    fd.append('categoria', categoria);
    fd.append('descripcion', descripcion);

    fd.append('imagenes_eliminadas', JSON.stringify(estadoImgs.eliminadas || []));

    (estadoImgs.nuevas || []).forEach((item) => {
      if (item && item.file instanceof File) fd.append('imagenes_nuevas[]', item.file);
    });

    try {
      setSaving(true);

      const resp = await fetch(`${EV_API_BASE}/api/producto/${id}/actualizar`, { method:'POST', body: fd });
      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        evNotify('error', 'Error', data.mensaje || data.error || 'No se pudo actualizar el producto.');
        return;
      }

      evNotify('success', 'Producto actualizado', 'Los cambios se guardaron correctamente.');

      const modalEl = document.getElementById('modalEditarPublicacion');
      if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
      }

      window.evCargarProductos?.();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al actualizar el producto.');
    } finally {
      setSaving(false);
    }
  }

  /* ==============================
     Listado tabla ✅ 8 columnas
  ============================== */
  function escAttr(v) {
    return String(v ?? '').replace(/"/g, '&quot;');
  }

  function uiEstadoVisible(visibleNum) {
    // 0 borrador, 1 pendiente, 2 aprobado, 3 anulado
    if (visibleNum === 0) return { text: 'Borrador', cls: 'ev-chip ev-chip-gray', disabled: true };
    if (visibleNum === 1) return { text: 'Pendiente', cls: 'ev-chip ev-chip-amber', disabled: true };
    if (visibleNum === 2) return { text: 'Aprobado',  cls: 'ev-chip ev-chip-green', disabled: true };
    return { text: 'Anulado', cls: 'ev-chip ev-chip-red', disabled: true };
  }

  function uiAccionPublicar(visibleNum) {
    if (visibleNum === 0) {
      return { show: true, text: 'Publicar', cls: 'ev-chip ev-chip-orange', disabled: false };
    }
    return { show: false };
  }

  async function cargarProductos() {
    const table = document.getElementById('tablaPublicaciones');
    const tbody = table?.querySelector('tbody');
    if (!table || !tbody) return;

    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">Cargando productos…</td></tr>`;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/producto/listar`, { method: 'GET' });
      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error','Sesión expirada','Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">${data.mensaje || data.error || 'No se pudo obtener el listado.'}</td></tr>`;
        return;
      }

      const items = Array.isArray(data.data) ? data.data : [];
      if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">Aún no tienes productos registrados.</td></tr>`;
        return;
      }

      tbody.innerHTML = items.map((p) => {
        const id = Number(p.codigo_producto ?? 0);
        const cod = String(id || '').padStart(6, '0');

        const tituloRaw = (p.titulo || '').toString();
        const titulo = escAttr(tituloRaw.substring(0, 80));

        const precio = Number(p.precio || 0).toFixed(2);

        const estadoRaw = (p.estado || '').toString();
        const estado = estadoRaw.toUpperCase();

        const tipoRaw = (p.tipo_nombre || p.tipo || p.nombre_tipo || '').toString().trim();
        const catRaw  = (p.categoria_nombre || p.categoria || p.nombre_categoria || '').toString().trim();

        const tipo = escAttr(tipoRaw || '-');
        const categoria = escAttr(catRaw || '-');

        const descFull = (p.descripcion || '').toString();
        const descShort = descFull.length > 90 ? (descFull.substring(0, 90) + '…') : descFull;
        const descSafe = escAttr(descShort || '-');

        const visible = Number(p.visible ?? 0);

        let badge = 'ev-badge ev-badge--noaplica';
        if (estado === 'NUEVO') badge = 'ev-badge ev-badge--nuevo';
        else if (estado === 'USADO') badge = 'ev-badge ev-badge--usado';

        const visUI = uiEstadoVisible(visible);
        const pubUI = uiAccionPublicar(visible);

        const disableEditar = (visible === 1 || visible === 2 || visible === 3) ? 'disabled' : '';
        const disableAnular = (visible === 2 || visible === 3) ? 'disabled' : '';

        return `
          <tr>
            <td><span class="ev-code">${cod}</span></td>
            <td class="td-trunc" title="${titulo}">${titulo || '-'}</td>
            <td>S/ ${precio}</td>
            <td><span class="${badge}">${estado || '-'}</span></td>
            <td class="td-trunc" title="${tipo}">${tipo}</td>
            <td class="td-trunc" title="${categoria}">${categoria}</td>
            <td class="td-trunc" title="${escAttr(descFull)}">${descSafe}</td>
            <td class="text-center">
              <div class="ev-actions">
                <button type="button" class="ev-chip ev-chip-green" data-action="editar" data-id="${id}" ${disableEditar}>Editar</button>
                <button type="button" class="ev-chip ev-chip-red" data-action="anular" data-id="${id}" ${disableAnular}>Anular</button>

                ${
                  pubUI.show
                    ? `<button type="button" class="${pubUI.cls}" data-action="publicar" data-id="${id}">${pubUI.text}</button>`
                    : `<button type="button" class="${visUI.cls}" disabled>${visUI.text}</button>`
                }
              </div>
            </td>
          </tr>
        `;
      }).join('');

    } catch (err) {
      console.error(err);
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Ocurrió un error al cargar los productos.</td></tr>`;
    }
  }

  window.evCargarProductos = cargarProductos;

  /* ==============================
     INIT seguro para vista inyectada
  ============================== */
  function isProductosViewPresent() {
    return !!document.getElementById('tablaPublicaciones');
  }

  function bindOnceGlobalEvents() {
    if (document.body.dataset.evProductosBound === '1') return;
    document.body.dataset.evProductosBound = '1';

    document.addEventListener('click', (e) => {
      if (e.target.closest('#btnBuscarPublicacion')) {
        evMountAllModalsToBody(); // ✅
        const el = document.getElementById('modalBuscarPublicacion');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        return;
      }

      if (e.target.closest('#btnAgregarPublicacion')) {
        evMountAllModalsToBody(); // ✅
        const modalEl = document.getElementById('modalAgregarPublicacion');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        requestAnimationFrame(() => {
          modal.show();
          requestAnimationFrame(setEvVh);
        });
        return;
      }

      const btnEditar = e.target.closest('[data-action="editar"][data-id]');
      if (btnEditar && !btnEditar.disabled) { cargarProductoEditar(btnEditar.getAttribute('data-id')); return; }

      const btnAnular = e.target.closest('[data-action="anular"][data-id]');
      if (btnAnular && !btnAnular.disabled) { confirmarYAnular(btnAnular.getAttribute('data-id')); return; }

      const btnPublicar = e.target.closest('[data-action="publicar"][data-id]');
      if (btnPublicar && !btnPublicar.disabled) { confirmarYPublicar(btnPublicar.getAttribute('data-id')); return; }
    });

    document.addEventListener('submit', (e) => {
      const form = e.target;

      if (form && form.id === 'formAgregarPublicacion') {
        e.preventDefault();
        registrarProducto(form);
        return;
      }

      if (form && form.id === 'formEditarPublicacion') {
        e.preventDefault();
        actualizarProducto(form);
        return;
      }
    });

    document.getElementById('formBuscarPublicacion')?.addEventListener('submit', (e) => {
      e.preventDefault();
      console.log('[BUSCAR]', Object.fromEntries(new FormData(e.target)));
    });
  }

  function initIfNeeded() {
    bindOnceGlobalEvents();

    // ✅ clave: cada vez que la vista está presente, montamos modales al body
    evMountAllModalsToBody();

    const tabla = document.getElementById('tablaPublicaciones');
    if (isProductosViewPresent() && tabla && !tabla.dataset.evLoaded) {
      tabla.dataset.evLoaded = '1';
      cargarProductos();
    }
  }

  document.addEventListener('DOMContentLoaded', initIfNeeded);

  const target = document.getElementById('contenido-principal') || document.body;
  const obs = new MutationObserver(() => { initIfNeeded(); });
  obs.observe(target, { childList: true, subtree: true });

})();
