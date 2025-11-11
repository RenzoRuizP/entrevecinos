/* publicaciones.js */

/* ==============================
   Ajuste de viewport seguro (vh)
============================== */
(function () {
  function setEvVh() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--ev-vh', `${vh}px`);
  }
  setEvVh();
  window.addEventListener('resize', setEvVh);
  document.addEventListener('shown.bs.modal', (e) => {
    if (e.target && e.target.id === 'modalAgregarPublicacion') setEvVh();
  });
})();

/* ==============================
   Modales + acciones básicas
============================== */
(function () {
  function abrirModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();
  }

  function precargarEditar(btn) {
    const $ = (sel) => document.querySelector(sel);
    const setVal = (sel, val) => { const el = $(sel); if (el) el.value = val; };
    setVal('#edit_id',          btn.dataset.id || '');
    setVal('#edit_titulo',      btn.dataset.titulo || '');
    setVal('#edit_categoria',   btn.dataset.categoria || 'Otros');
    setVal('#edit_descripcion', btn.dataset.descripcion || '');
    setVal('#edit_precio',      btn.dataset.precio || '');
    setVal('#edit_estado',      btn.dataset.estado || 'Usado');
    setVal('#edit_stock',       btn.dataset.stock || 1);
  }

  document.addEventListener("click", (e) => {
    if (e.target.closest("#btnBuscarPublicacion")) { abrirModal('modalBuscarPublicacion'); return; }
    if (e.target.closest("#btnAgregarPublicacion")) { abrirModal('modalAgregarPublicacion'); return; }
    const btnEditar = e.target.closest('[data-action="editar"], .btn-editar');
    if (btnEditar) { precargarEditar(btnEditar); abrirModal('modalEditarPublicacion'); }
  });

  document.getElementById('formBuscarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault(); console.log('[BUSCAR]', Object.fromEntries(new FormData(e.target)));
  });
  document.getElementById('formAgregarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault(); console.log('[AGREGAR]', Object.fromEntries(new FormData(e.target)));
  });
  document.getElementById('formEditarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault(); console.log('[EDITAR]', Object.fromEntries(new FormData(e.target)));
  });
})();

/* ==============================
   Uploader + Previsualización
   (inicializa al abrir el modal)
============================== */
(function () {
  const MAX_MB = 5;
  let initialized = false;

  function notify(msg) {
    if (window.Swal?.fire) Swal.fire({icon:'info', title:'Aviso', text: msg});
    else alert(msg);
  }

  function validarArchivo(file) {
    const okTipo = /^image\/(jpeg|png|webp|gif|bmp|svg\+xml)$/i.test(file.type) || file.type.startsWith('image/');
    const okPeso = file.size <= MAX_MB * 1024 * 1024;
    if (!okTipo) { notify('Solo se permiten archivos de imagen.'); return false; }
    if (!okPeso) { notify(`El archivo "${file.name}" supera ${MAX_MB} MB.`); return false; }
    return true;
  }

  function initUploader(modalEl) {
    if (initialized) return;

    const uploader = modalEl.querySelector('#uploaderAgregar');
    const input    = modalEl.querySelector('#inputImagenes');
    const preview  = modalEl.querySelector('#previewImagenes');
    const btnClr   = modalEl.querySelector('#btnLimpiarImagenes');
    const lblCnt   = modalEl.querySelector('#contadorImagenes');
    if (!uploader || !input || !preview) return;

    const MAX_FILES = Number(input.dataset.max || 3);
    let dt = new DataTransfer();
    let selectedIndex = 0;

    const filePicker = modalEl.querySelector('#evFilePicker');
    const fakeBtn    = modalEl.querySelector('#evFileFakeBtn');
    const fileMeta   = modalEl.querySelector('#evFileMeta');

    const refreshMetaHint = () => {
      if (!fileMeta) return;
      fileMeta.textContent = dt.files.length
        ? `${dt.files.length} seleccionado(s) • Máx ${MAX_FILES}`
        : `JPG, PNG o WebP • Máx 5 MB c/u • Máx ${MAX_FILES}`;
    };

    fakeBtn?.addEventListener('click', () => input.click());
    filePicker?.addEventListener('click', (ev) => {
      if (ev.target.closest('.ev-file-btn')) return;
      input.click();
    });

    ['dragenter','dragover'].forEach(evt => {
      filePicker?.addEventListener(evt, (e) => {
        e.preventDefault(); e.stopPropagation();
        filePicker.classList.add('is-dragover');
      });
    });
    ['dragleave','dragend','drop'].forEach(evt => {
      filePicker?.addEventListener(evt, (e) => {
        e.preventDefault(); e.stopPropagation();
        filePicker.classList.remove('is-dragover');
      });
    });
    filePicker?.addEventListener('drop', (e) => {
      const dropped = Array.from(e.dataTransfer?.files || []);
      if (!dropped.length) return;
      for (const file of dropped) {
        if (dt.files.length >= MAX_FILES) { notify(`Máximo ${MAX_FILES} imágenes.`); break; }
        if (!validarArchivo(file)) continue;
        dt.items.add(file);
      }
      input.files = dt.files;
      if (dt.files.length) selectedIndex = 0;
      render();
      input.value = '';
      refreshMetaHint();
    });

    let previewWrapper, previewMainImg, previewThumbs, previewActions;
    let metaCard, metaTitleEl, metaPriceEl, metaDescEl;

    const createBtn = (html, title, onClick, extraClass='btn-outline-success') => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = `btn btn-sm ${extraClass}`;
      b.innerHTML = html;
      b.title = title;
      b.addEventListener('click', onClick);
      return b;
    };

    function ensureMetaCard() {
      const metas = document.querySelectorAll('#evMetaCard');
      if (metas.length > 1) metas.forEach((n, i) => { if (i > 0) n.remove(); });

      let card = document.getElementById('evMetaCard');
      if (!card) {
        card = document.createElement('div');
        card.id = 'evMetaCard';
        card.className = 'card ev-card mt-3';
        card.innerHTML = `
          <div class="card-body p-3">
            <h6 id="evMetaTitle" class="mb-1" style="font-weight:800;color:#0b3d27;">Título</h6>
            <div id="evMetaPrice" class="mb-2" style="color:#0F592F;font-weight:800;">S/ 0.00</div>
            <div style="font-size:.9rem;color:#64748b">Detalles</div>
            <p id="evMetaDesc" class="mb-0" style="color:#475569;">La descripción aparecerá aquí.</p>
          </div>`;
        const mount = document.getElementById('previewMount') || uploader;
        if (mount && mount !== uploader) mount.appendChild(card);
        else uploader.insertAdjacentElement('afterend', card);
      }
      metaCard   = card;
      metaTitleEl = metaCard.querySelector('#evMetaTitle');
      metaPriceEl = metaCard.querySelector('#evMetaPrice');
      metaDescEl  = metaCard.querySelector('#evMetaDesc');
    }

    function ensurePreviewArea() {
      if (!previewWrapper) previewWrapper = document.getElementById('evPreviewWrapper');

      if (!previewWrapper) {
        previewWrapper = document.createElement('div');
        previewWrapper.id = 'evPreviewWrapper';
        previewWrapper.className = 'ev-preview-area';
        previewWrapper.style.display = 'none';

        const title = document.createElement('div');
        title.className = 'ev-preview-title';
        title.innerHTML = `<span><i class="bi bi-images me-1"></i>Previsualización</span>`;

        previewActions = document.createElement('div');
        previewActions.className = 'ev-preview-actions';
        const btnExpand   = createBtn('<i class="bi bi-arrows-fullscreen"></i>', 'Expandir/Contraer', () => previewWrapper.classList.toggle('is-expanded'));
        const btnPrev     = createBtn('<i class="bi bi-chevron-left"></i>', 'Anterior', () => { if(!dt.files.length) return; selectedIndex = (selectedIndex - 1 + dt.files.length) % dt.files.length; updateMain(); renderThumbs(); });
        const btnNext     = createBtn('<i class="bi bi-chevron-right"></i>', 'Siguiente', () => { if(!dt.files.length) return; selectedIndex = (selectedIndex + 1) % dt.files.length; updateMain(); renderThumbs(); });
        const btnClearAll = createBtn('<i class="bi bi-trash"></i>', 'Quitar todas', () => { dt = new DataTransfer(); input.value=''; input.files = dt.files; selectedIndex=0; render(); refreshMetaHint(); }, 'btn-cancelar');
        previewActions.append(btnExpand, btnPrev, btnNext, btnClearAll);
        title.appendChild(previewActions);

        const main = document.createElement('div');
        main.className = 'ev-preview-main';
        previewMainImg = document.createElement('img');
        previewMainImg.alt = 'Vista previa';
        main.appendChild(previewMainImg);
        main.addEventListener('dblclick', () => previewWrapper.classList.toggle('is-expanded'));

        previewThumbs = document.createElement('div');
        previewThumbs.className = 'ev-preview-thumbs';

        previewWrapper.append(title, main, previewThumbs);
      }

      const mount = document.getElementById('previewMount');
      if (mount) {
        if (!mount.contains(previewWrapper)) mount.prepend(previewWrapper);
      } else if (!previewWrapper.parentElement) {
        uploader.insertAdjacentElement('afterend', previewWrapper);
      }

      ensureMetaCard();
      updateMeta();
    }

    function showPreviewArea(show){ ensurePreviewArea(); previewWrapper.style.display = show ? '' : 'none'; }
    function setCount(){ if (lblCnt) lblCnt.textContent = `${dt.files.length} de ${MAX_FILES}`; }
    function limpiarChips(){ preview.innerHTML=''; }

    function addChip(file, idx){
      const col = document.createElement('div'); col.className = 'col-4';
      col.innerHTML = `
        <div class="ev-thumb">
          <img alt="preview" />
          <button type="button" class="btn btn-sm btn-danger ev-remove" data-index="${idx}" title="Quitar">&times;</button>
          <div class="ev-caption">${(file.name||'').slice(0,22)}</div>
        </div>`;
      const img = col.querySelector('img'); const r = new FileReader();
      r.onload = e => img.src = e.target.result; r.readAsDataURL(file);
      preview.appendChild(col);
    }

    function renderThumbs(){
      ensurePreviewArea(); previewThumbs.innerHTML='';
      Array.from(dt.files).forEach((file,i)=>{
        const th = document.createElement('div');
        th.className = 'ev-preview-thumb' + (i===selectedIndex ? ' active':'' );
        th.tabIndex = 0; th.setAttribute('role','button');
        const img = document.createElement('img'); const r = new FileReader();
        r.onload = e => img.src = e.target.result; r.readAsDataURL(file);
        th.appendChild(img);
        const activate = ()=>{ selectedIndex=i; updateMain();
          [...previewThumbs.children].forEach(el=>el.classList.remove('active'));
          th.classList.add('active'); th.scrollIntoView({behavior:'smooth', inline:'center'}); };
        th.addEventListener('click', activate);
        th.addEventListener('keydown', ev=>{ if(ev.key==='Enter'||ev.key===' '){ev.preventDefault(); activate();} });
        previewThumbs.appendChild(th);
      });
    }

    function updateMain(){
      if (!dt.files.length) { previewMainImg.src=''; return; }
      const target = dt.files[selectedIndex] || dt.files[0];
      const r = new FileReader(); r.onload = e => previewMainImg.src = e.target.result; r.readAsDataURL(target);
    }

    function render(){
      limpiarChips(); Array.from(dt.files).forEach((f,i)=>addChip(f,i)); setCount();
      if (dt.files.length){ showPreviewArea(true); if (selectedIndex>=dt.files.length) selectedIndex=0; updateMain(); renderThumbs(); }
      else { showPreviewArea(false); }
    }

    /* ---- Actualiza tarjeta meta según inputs del formulario ---- */
    function updateMeta() {
      if (!metaTitleEl || !metaPriceEl || !metaDescEl) return;
      const title = modalEl.querySelector('input[name="titulo"]')?.value?.trim() || 'Título';
      const priceRaw = modalEl.querySelector('input[name="precio"]')?.value || '';
      const desc  = modalEl.querySelector('textarea[name="descripcion"]')?.value?.trim() || 'La descripción aparecerá aquí.';
      const n = Number(priceRaw || 0);
      const precio = isNaN(n) ? '0.00' : n.toFixed(2);

      metaTitleEl.textContent = title;
      metaPriceEl.textContent = `S/ ${precio}`;
      metaDescEl.textContent  = desc;
    }

    /* ---- Eventos uploader ---- */
    input.addEventListener('change', (e)=>{
      const nuevos = Array.from(e.target.files||[]);
      if (!nuevos.length) { refreshMetaHint(); return; }
      for (const file of nuevos){
        if (dt.files.length >= MAX_FILES){ notify(`Máximo ${MAX_FILES} imágenes.`); break; }
        if (!validarArchivo(file)) continue;
        dt.items.add(file);
      }
      input.files = dt.files;
      if (dt.files.length === nuevos.length) selectedIndex = 0;
      render(); input.value = '';
      refreshMetaHint();
    });

    preview.addEventListener('click', (e)=>{
      const btn = e.target.closest('.ev-remove'); if (!btn) return;
      const idx = Number(btn.dataset.index);
      const ndt = new DataTransfer();
      Array.from(dt.files).forEach((f,i)=>{ if(i!==idx) ndt.items.add(f); });
      dt = ndt; input.files = dt.files;
      if (selectedIndex === idx) selectedIndex = 0; else if (selectedIndex > idx) selectedIndex -= 1;
      render();
      refreshMetaHint();
    });

    btnClr?.addEventListener('click', ()=>{
      dt = new DataTransfer(); input.value=''; input.files = dt.files; selectedIndex=0; render();
      refreshMetaHint();
    });

    modalEl.addEventListener('keydown', (ev)=>{
      if (!dt.files.length) return;
      if (ev.key==='ArrowRight' || ev.key==='ArrowLeft'){
        ev.preventDefault();
        const dir = ev.key==='ArrowRight' ? 1 : -1;
        selectedIndex = (selectedIndex + dir + dt.files.length) % dt.files.length;
        updateMain(); renderThumbs();
      }
    });

    /* Inputs que alimentan la tarjeta meta */
    modalEl.querySelector('input[name="titulo"]')?.addEventListener('input', updateMeta);
    modalEl.querySelector('input[name="precio"]')?.addEventListener('input', updateMeta);
    modalEl.querySelector('textarea[name="descripcion"]')?.addEventListener('input', updateMeta);

    /* Reset al cerrar */
    modalEl.addEventListener('hidden.bs.modal', ()=>{
      dt = new DataTransfer(); input.value=''; input.files = dt.files; selectedIndex=0;
      preview.innerHTML=''; setCount(); refreshMetaHint();
      const wrap = document.getElementById('evPreviewWrapper');
      if (wrap) wrap.style.display = 'none';
      if (metaTitleEl) metaTitleEl.textContent = 'Título';
      if (metaPriceEl) metaPriceEl.textContent = 'S/ 0.00';
      if (metaDescEl)  metaDescEl.textContent  = 'La descripción aparecerá aquí.';
    });

    render();
    refreshMetaHint();
    initialized = true;
  }

  /* Inicializa al abrir el modal */
  document.addEventListener('shown.bs.modal', (ev) => {
    const modal = ev.target;
    if (modal && modal.id === 'modalAgregarPublicacion') {
      initUploader(modal);
    }
  });

  /* Si ya estaba visible (caso raro) */
  window.addEventListener('DOMContentLoaded', () => {
    const m = document.getElementById('modalAgregarPublicacion');
    if (m && m.classList.contains('show')) initUploader(m);
  });
})();

/* ==============================
   UX extra: selects Tipo/Categoría
============================== */
(function () {
  const tipo = document.getElementById('comboTipo');
  const cat  = document.getElementById('comboCategoria');
  const markFilled = (el) => {
    if (!el) return;
    el.classList.remove('ev-pulse');
    void el.offsetWidth;
    el.classList.add('ev-pulse');
    if (el.value && el.value !== '') el.classList.add('is-filled');
    else el.classList.remove('is-filled');
  };
  tipo?.addEventListener('change', () => markFilled(tipo));
  cat?.addEventListener('change',  () => markFilled(cat));
})();

/* ===== Fallback irrompible: calcula alto del modal-body y bloquea X ===== */
(function fixModalBodyHeight(){
  function tuneModal(id){
    const modal = document.getElementById(id);
    if(!modal) return;
    const content = modal.querySelector('.modal-content');
    const body    = modal.querySelector('.modal-body');
    const header  = modal.querySelector('.modal-header');
    const footer  = modal.querySelector('.modal-footer');
    if(!content || !body) return;

    const viewport = Math.min(window.innerHeight || 0, screen.height || window.innerHeight || 0);
    const root = getComputedStyle(modal);
    const modalMargin = parseFloat(root.getPropertyValue('--bs-modal-margin')) || 8;
    const available = Math.max(200, Math.floor(viewport - (modalMargin*2)));

    const hH = header ? header.offsetHeight : 0;
    const fH = footer ? footer.offsetHeight : 0;

    const cs  = getComputedStyle(body);
    const pvt = parseFloat(cs.paddingTop||'0') + parseFloat(cs.paddingBottom||'0');

    content.style.maxHeight = `${available}px`;
    const bodyH = Math.max(160, available - hH - fH - pvt);
    body.style.height = `${bodyH}px`;
    body.style.overflowY = 'auto';
    body.style.overflowX = 'hidden';   // 🔒 evita barra horizontal por JS
    body.style.minHeight = '0';
    body.style.webkitOverflowScrolling = 'touch';
  }

  function handleShown(e){
    const id = e.target?.id;
    if(!id) return;
    if(id==='modalAgregarPublicacion' || id==='modalBuscarPublicacion'){
      setTimeout(()=>tuneModal(id), 0);
    }
  }

  function handleResize(){
    ['modalAgregarPublicacion','modalBuscarPublicacion'].forEach(tuneModal);
  }

  document.addEventListener('shown.bs.modal', handleShown);
  window.addEventListener('resize', handleResize);
  window.addEventListener('orientationchange', handleResize);
})();
