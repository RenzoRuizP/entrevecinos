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
   Uploader + Previsualización — versión “tiles”
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
    const tiles    = modalEl.querySelector('#evTiles');
    const btnClr   = modalEl.querySelector('#btnLimpiarImagenes');
    const lblCnt   = modalEl.querySelector('#contadorImagenes');
    if (!uploader || !input || !tiles) return;

    const MAX_FILES = Number(input.dataset.max || 3);
    let dt = new DataTransfer();
    let selectedIndex = 0;

    /* ---------- Drag & Drop sobre el grid ---------- */
    ['dragenter','dragover'].forEach(evt => {
      tiles.addEventListener(evt, (e) => {
        e.preventDefault(); e.stopPropagation();
        tiles.style.outline = '2px dashed #7dd3a9';
        tiles.style.outlineOffset = '4px';
      });
    });
    ['dragleave','dragend','drop'].forEach(evt => {
      tiles.addEventListener(evt, (e) => {
        e.preventDefault(); e.stopPropagation();
        tiles.style.outline = '';
        tiles.style.outlineOffset = '';
      });
    });
    tiles.addEventListener('drop', (e) => {
      const dropped = Array.from(e.dataTransfer?.files || []);
      if (!dropped.length) return;
      for (const file of dropped) {
        if (dt.files.length >= MAX_FILES) { notify(`Máximo ${MAX_FILES} imágenes.`); break; }
        if (!validarArchivo(file)) continue;
        dt.items.add(file);
      }
      input.files = dt.files;
      if (dt.files.length) selectedIndex = 0;
      render(); input.value = '';
    });

    /* ---------- Utilidades ---------- */
    const setCount = () => { if (lblCnt) lblCnt.textContent = `${dt.files.length} de ${MAX_FILES}`; };

    function createAddTile(){
      const add = document.createElement('div');
      add.className = 'ev-tile ev-tile-add';
      add.innerHTML = `
        <div class="ico"><i class="bi bi-plus-lg"></i></div>
        <div class="t1">Agregar fotos</div>
        <div class="t2">o arrastra y suelta</div>
      `;
      add.addEventListener('click', ()=> input.click());
      return add;
    }


    function createImgTile(file, idx){
      const wrap = document.createElement('div');
      wrap.className = 'ev-tile';
      const img = document.createElement('img');
      const r = new FileReader(); r.onload = e => img.src = e.target.result; r.readAsDataURL(file);
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ev-tile-remove';
      btn.title = 'Quitar';
      btn.innerHTML = '✕';
      btn.addEventListener('click', ()=>{
        const ndt = new DataTransfer();
        Array.from(dt.files).forEach((f,i)=>{ if(i!==idx) ndt.items.add(f); });
        dt = ndt; input.files = dt.files;
        if (selectedIndex === idx) selectedIndex = 0; else if (selectedIndex > idx) selectedIndex -= 1;
        render();
      });
      wrap.append(img, btn);

      // al hacer click en la imagen, la mostramos en el preview grande
      wrap.addEventListener('click', ()=>{
        selectedIndex = idx;
        updateMain(); renderThumbs();
      });

      return wrap;
    }

    /* ---------- Preview derecha (se mantiene) ---------- */
    let previewWrapper, previewMainImg, previewThumbs, previewActions, metaTitleEl, metaPriceEl, metaDescEl;

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

        const actions = document.createElement('div');
        actions.className = 'ev-preview-actions';
        const mkBtn = (html, title, cb, extra='btn-outline-success')=>{
          const b = document.createElement('button'); b.type='button'; b.className=`btn btn-sm ${extra}`;
          b.innerHTML=html; b.title=title; b.addEventListener('click', cb); return b;
        };
        actions.append(
          mkBtn('<i class="bi bi-arrows-fullscreen"></i>','Expandir/Contraer',()=>previewWrapper.classList.toggle('is-expanded')),
          mkBtn('<i class="bi bi-chevron-left"></i>','Anterior',()=>{ if(!dt.files.length) return; selectedIndex=(selectedIndex-1+dt.files.length)%dt.files.length; updateMain(); renderThumbs(); }),
          mkBtn('<i class="bi bi-chevron-right"></i>','Siguiente',()=>{ if(!dt.files.length) return; selectedIndex=(selectedIndex+1)%dt.files.length; updateMain(); renderThumbs(); }),
          mkBtn('<i class="bi bi-trash"></i>','Quitar todas',()=>{ dt=new DataTransfer(); input.value=''; input.files=dt.files; selectedIndex=0; render(); }, 'btn-cancelar')
        );
        title.appendChild(actions);

        const main = document.createElement('div');
        main.className = 'ev-preview-main';
        previewMainImg = document.createElement('img'); previewMainImg.alt='Vista previa';
        main.appendChild(previewMainImg);

        previewThumbs = document.createElement('div'); previewThumbs.className = 'ev-preview-thumbs';

        previewWrapper.append(title, main, previewThumbs);
      }
      const mount = document.getElementById('previewMount');
      if (mount && !mount.contains(previewWrapper)) mount.prepend(previewWrapper);

      // Tarjeta meta ya existe de tu versión anterior:
      let meta = document.getElementById('evMetaCard');
      if (!meta) {
        meta = document.createElement('div');
        meta.id = 'evMetaCard';
        meta.className = 'card ev-card mt-3';
        meta.innerHTML = `
          <div class="card-body p-3">
            <h6 id="evMetaTitle" class="mb-1" style="font-weight:800;color:#0b3d27;">Título</h6>
            <div id="evMetaPrice" class="mb-2" style="color:#0F592F;font-weight:800;">S/ 0.00</div>
            <div style="font-size:.9rem;color:#64748b">Detalles</div>
            <p id="evMetaDesc" class="mb-0" style="color:#475569;">La descripción aparecerá aquí.</p>
          </div>`;
        mount.appendChild(meta);
      }
      metaTitleEl = document.getElementById('evMetaTitle');
      metaPriceEl = document.getElementById('evMetaPrice');
      metaDescEl  = document.getElementById('evMetaDesc');
      updateMeta();
    }

    function updateMain(){
      if (!dt.files.length) { if(previewMainImg) previewMainImg.src=''; return; }
      const target = dt.files[selectedIndex] || dt.files[0];
      const r = new FileReader(); r.onload = e => previewMainImg.src = e.target.result; r.readAsDataURL(target);
    }
    function renderThumbs(){
      if (!previewThumbs) return;
      previewThumbs.innerHTML='';
      Array.from(dt.files).forEach((file,i)=>{
        const th = document.createElement('div');
        th.className = 'ev-preview-thumb' + (i===selectedIndex ? ' active':'' );
        const img = document.createElement('img'); const r = new FileReader();
        r.onload = e => img.src = e.target.result; r.readAsDataURL(file);
        th.appendChild(img);
        th.addEventListener('click', ()=>{ selectedIndex=i; updateMain(); renderThumbs(); });
        previewThumbs.appendChild(th);
      });
    }

    function showPreviewArea(show){ ensurePreviewArea(); previewWrapper.style.display = show ? '' : 'none'; }

    /* ---------- Render del grid de tiles ---------- */
    function renderTiles(){
      tiles.innerHTML = '';
      Array.from(dt.files).forEach((file,i)=>{
        const t = document.createElement('div');
        t.className = 'ev-tile';
        const img = document.createElement('img');
        const r = new FileReader();
        r.onload = e => img.src = e.target.result;
        r.readAsDataURL(file);

        const del = document.createElement('button');
        del.className = 'ev-tile-remove';
        del.innerHTML = '×';
        del.onclick = ()=>{
          const ndt = new DataTransfer();
          Array.from(dt.files).forEach((f,j)=>{ if(j!==i) ndt.items.add(f); });
          dt = ndt; input.files = dt.files; renderTiles();
        };

        t.append(img, del);
        tiles.appendChild(t);
      });
      if (dt.files.length < MAX_FILES) tiles.appendChild(createAddTile());
      setCount();
    }

    function render(){
      setCount();
      renderTiles();
      if (dt.files.length){ showPreviewArea(true); if (selectedIndex>=dt.files.length) selectedIndex=0; updateMain(); renderThumbs(); }
      else { showPreviewArea(false); }
    }

    /* ---------- Eventos básicos ---------- */
    input.addEventListener('change', (e)=>{
      const nuevos = Array.from(e.target.files||[]);
      if (!nuevos.length) return;
      for (const file of nuevos){
        if (dt.files.length >= MAX_FILES){ notify(`Máximo ${MAX_FILES} imágenes.`); break; }
        if (!validarArchivo(file)) continue;
        dt.items.add(file);
      }
      input.files = dt.files;
      if (dt.files.length === nuevos.length) selectedIndex = 0;
      render(); input.value = '';
    });

    btnClr?.addEventListener('click', ()=>{
      dt = new DataTransfer(); input.value=''; input.files = dt.files; selectedIndex=0; render();
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

    /* ---------- Meta (título/precio/desc) ---------- */
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
    modalEl.querySelector('input[name="titulo"]')?.addEventListener('input', updateMeta);
    modalEl.querySelector('input[name="precio"]')?.addEventListener('input', updateMeta);
    modalEl.querySelector('textarea[name="descripcion"]')?.addEventListener('input', updateMeta);

    /* ---------- Reset al cerrar ---------- */
    modalEl.addEventListener('hidden.bs.modal', ()=>{
      dt = new DataTransfer(); input.value=''; input.files = dt.files; selectedIndex=0;
      tiles.innerHTML = ''; setCount();
      const wrap = document.getElementById('evPreviewWrapper');
      if (wrap) wrap.style.display = 'none';
      const t = document.getElementById('evMetaTitle'); if (t) t.textContent='Título';
      const p = document.getElementById('evMetaPrice'); if (p) p.textContent='S/ 0.00';
      const d = document.getElementById('evMetaDesc');  if (d) d.textContent='La descripción aparecerá aquí.';
    });

    render();
    initialized = true;
  }

  document.addEventListener('shown.bs.modal', (ev) => {
    const modal = ev.target;
    if (modal && modal.id === 'modalAgregarPublicacion') {
      initUploader(modal);
    }
  });

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
