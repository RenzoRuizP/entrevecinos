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
   Uploader + Previsualización — robusto por-item
   (usa arreglo de estado + DataTransfer)
============================== */
(function () {
  const MAX_MB = 5;
  let initialized = false;

  const $ = (s, root=document) => root.querySelector(s);

  function notify(msg) {
    if (window.Swal?.fire) Swal.fire({icon:'info', title:'Aviso', text: msg});
    else alert(msg);
  }

  function validarArchivo(file) {
    const okTipo = /^image\/(jpeg|png|webp|gif|bmp|svg\+xml)$/i.test(file.type) || file.type.startsWith('image/');
    const okPeso = file.size <= MAX_MB * 1024 * 1024;
    if (!okTipo) { notify('Solo se permiten archivos de imagen.'); return false; }
    if (!okPeso) { notify(`"${file.name}" supera ${MAX_MB} MB.`); return false; }
    return true;
  }

  function initUploader(modalEl) {
    if (initialized) return;

    const input    = $('#inputImagenes', modalEl);
    const tiles    = $('#evTiles', modalEl);
    const tileAdd  = $('#tileAgregar', modalEl);
    const btnClr   = $('#btnLimpiarImagenes', modalEl);
    const lblCnt   = $('#contadorImagenes', modalEl);
    if (!input || !tiles) return;

    const MAX_FILES = Number(input.dataset.max || 10);

    // --------- Estado interno ----------
    /** @type {{id:string,file:File,url:string}[]} */
    let fotos = [];
    let selectedIndex = 0;

    // --------- Utilidades ----------
    const rebuildFileList = () => {
      const dt = new DataTransfer();
      fotos.forEach(f => dt.items.add(f.file));
      input.files = dt.files;
    };

    const revokeAll = () => fotos.forEach(f => f.url && URL.revokeObjectURL(f.url));

    const setCount = () => { if (lblCnt) lblCnt.textContent = String(fotos.length); };

    const renderTiles = () => {
      tiles.innerHTML = '';
      fotos.forEach((f, i) => {
        const t = document.createElement('div');
        t.className = 'ev-tile';
        const img = document.createElement('img'); img.src = f.url; img.alt = f.file.name;
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ev-tile-remove';
        del.title = 'Quitar';
        del.textContent = '×';
        del.addEventListener('click', (ev) => {
          ev.stopPropagation();
          // elimina solo ese índice
          const removed = fotos.splice(i, 1)[0];
          if (removed?.url) URL.revokeObjectURL(removed.url);
          if (selectedIndex >= fotos.length) selectedIndex = Math.max(0, fotos.length - 1);
          rebuildFileList();
          paint();
        });
        t.append(img, del);
        t.addEventListener('click', () => { selectedIndex = i; updateMain(); renderThumbs(); });
        tiles.appendChild(t);
      });

      // tile para agregar
      if (fotos.length < MAX_FILES) {
        const add = document.createElement('div');
        add.className = 'ev-tile ev-tile-add';
        add.innerHTML = `
          <div class="ico"><i class="bi bi-plus-lg"></i></div>
          <div class="t1">Agregar fotos</div>
          <div class="t2">o arrastra y suelta</div>
        `;
        add.addEventListener('click', () => input.click());
        tiles.appendChild(add);
      }
      setCount();
    };

    // --------- Preview derecha ----------
    let previewWrapper, previewMainImg, previewThumbs, metaTitleEl, metaPriceEl, metaDescEl;

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
          
          mkBtn('<i class="bi bi-chevron-left"></i>','Anterior',()=>{ if(!fotos.length) return; selectedIndex=(selectedIndex-1+fotos.length)%fotos.length; updateMain(); renderThumbs(); }),
          mkBtn('<i class="bi bi-chevron-right"></i>','Siguiente',()=>{ if(!fotos.length) return; selectedIndex=(selectedIndex+1)%fotos.length; updateMain(); renderThumbs(); }),
          mkBtn('<i class="bi bi-trash"></i>','Quitar todas',()=>{ revokeAll(); fotos = []; rebuildFileList(); selectedIndex=0; paint(); }, 'btn-cancelar')
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

      // Tarjeta meta
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
      if (!fotos.length) { if(previewMainImg) previewMainImg.src=''; return; }
      const target = fotos[selectedIndex] || fotos[0];
      previewMainImg.src = target.url;
    }

    function renderThumbs(){
      if (!previewThumbs) return;
      previewThumbs.innerHTML='';
      fotos.forEach((f,i)=>{
        const th = document.createElement('div');
        th.className = 'ev-preview-thumb' + (i===selectedIndex ? ' active':'' );
        const img = document.createElement('img'); img.src = f.url;
        th.appendChild(img);
        th.addEventListener('click', ()=>{ selectedIndex=i; updateMain(); renderThumbs(); });
        previewThumbs.appendChild(th);
      });
    }

    function showPreviewArea(show){ ensurePreviewArea(); previewWrapper.style.display = show ? '' : 'none'; }

    function paint(){
      renderTiles();
      if (fotos.length){ showPreviewArea(true); if (selectedIndex>=fotos.length) selectedIndex=0; updateMain(); renderThumbs(); }
      else { showPreviewArea(false); }
    }

    // --------- Entrada archivos ----------
    input.addEventListener('change', (e)=>{
      const nuevos = Array.from(e.target.files||[]);
      if (!nuevos.length) return;

      for (const file of nuevos){
        if (fotos.length >= MAX_FILES){ notify(`Máximo ${MAX_FILES} imágenes.`); break; }
        if (!validarArchivo(file)) continue;

        // evitar duplicados por nombre + tamaño + lastModified
        const dup = fotos.some(f => f.file.name===file.name && f.file.size===file.size && f.file.lastModified===file.lastModified);
        if (dup) continue;

        fotos.push({ id: crypto.randomUUID(), file, url: URL.createObjectURL(file) });
      }
      rebuildFileList();
      if (fotos.length) selectedIndex = 0;
      paint();
      // permitir seleccionar el mismo archivo otra vez si lo quitó
      input.value = '';
    });

    // Tile estático inicial
    tileAdd?.addEventListener('click', () => input.click());

    // Drag & Drop
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
      dropped.forEach(file => {
        if (fotos.length >= MAX_FILES) return;
        if (!validarArchivo(file)) return;
        const dup = fotos.some(f => f.file.name===file.name && f.file.size===file.size && f.file.lastModified===file.lastModified);
        if (dup) return;
        fotos.push({ id: crypto.randomUUID(), file, url: URL.createObjectURL(file) });
      });
      rebuildFileList();
      if (fotos.length) selectedIndex = 0;
      paint();
    });

    // Limpiar todo
    btnClr?.addEventListener('click', ()=>{
      revokeAll(); fotos = []; rebuildFileList(); selectedIndex=0; paint();
    });

    // Navegación con flechas
    modalEl.addEventListener('keydown', (ev)=>{
      if (!fotos.length) return;
      if (ev.key==='ArrowRight' || ev.key==='ArrowLeft'){
        ev.preventDefault();
        const dir = ev.key==='ArrowRight' ? 1 : -1;
        selectedIndex = (selectedIndex + dir + fotos.length) % fotos.length;
        updateMain(); renderThumbs();
      }
    });

    // --------- Meta (título/precio/desc) ----------
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

    // --------- Reset al cerrar ----------
    modalEl.addEventListener('hidden.bs.modal', ()=>{
      revokeAll(); fotos = []; rebuildFileList(); selectedIndex=0;
      tiles.innerHTML = '';
      const add = document.createElement('div');
      add.className = 'ev-tile ev-tile-add';
      add.innerHTML = `
        <div class="ico"><i class="bi bi-plus-lg"></i></div>
        <div class="t1">Agregar fotos</div>
        <div class="t2">o arrastra y suelta</div>
      `;
      add.addEventListener('click', () => input.click());
      tiles.appendChild(add);
      setCount();

      const wrap = document.getElementById('evPreviewWrapper');
      if (wrap) wrap.style.display = 'none';
      const t = document.getElementById('evMetaTitle'); if (t) t.textContent='Título';
      const p = document.getElementById('evMetaPrice'); if (p) p.textContent='S/ 0.00';
      const d = document.getElementById('evMetaDesc');  if (d) d.textContent='La descripción aparecerá aquí.';
    });

    // Primera pintura
    paint();
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

/* Registrar publicación */

  // 🧩 Delegación de eventos como respaldo adicional
  document.addEventListener("click", async (e) => {
    // Detectar clic en el botón guardar, incluso si se cargó dinámicamente
    if (e.target && e.target.id === "btnGuardarPublicacion") {
      const form = document.getElementById("formDatosPersonales");
      if (!form) return; // si aún no se cargó, no hace nada

      console.log("🟢 (Delegación) Click detectado en btnGuardarPublicacion");

      const nombre = document.getElementById("nombre_completo")?.value.trim() || "";
      const email = document.getElementById("email")?.value.trim() || "";

      if (!nombre || !email) {
        Swal.fire({
          icon: "warning",
          title: "Campos requeridos",
          text: "Por favor ingresa al menos tu nombre y correo electrónico.",
        });
        return;
      }

      Swal.fire({
        title: "Guardando cambios...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      try {
        const response = await fetch(`${window.BASE_URL}api/publicacion/registrar`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({
            
            inputImagenes: document.getElementById("inputImagenes")?.value.trim() || "",
            titulo: document.getElementById("titulo")?.value.trim() || "",
            precio: document.getElementById("precio")?.value.trim() || "",
            comboEstado: document.getElementById("comboEstado")?.value || "",
            comboTipo: document.getElementById("comboTipo")?.value || "",
            comboCategoria: document.getElementById("comboCategoria")?.value || "",
            descripcion: document.getElementById("descripcion")?.value.trim() || "",
          }),
        });

        const result = await response.json();
        console.log("📬 (Delegación) Respuesta del servidor:", result);

        if (!response.ok || !result.success) throw new Error(result.error || "No se pudo guardar la información");

        Swal.fire({
          icon: "success",
          title: "Datos actualizados correctamente",
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (err) {
        console.error("❌ (Delegación) Error al guardar:", err);
        Swal.fire({
          icon: "error",
          title: "Error al guardar",
          text: err.message || "Ocurrió un error al guardar los datos.",
        });
      }
    }
  });
