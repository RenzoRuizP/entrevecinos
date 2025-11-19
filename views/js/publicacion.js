/* publicaciones.js */

/* ==============================
   Config base + helper de alertas
============================== */
const EV_API_BASE = (window.BASE_URL || '').replace(/\/$/, '');

function evNotify(icon, title, text) {
  if (window.Swal?.fire) {
    Swal.fire({
      icon,
      title,
      text,
      confirmButtonText: 'Aceptar',
      customClass: {
        confirmButton: 'btn btn-outline-success'
      },
      buttonsStyling: false
    });
  } else {
    alert(title ? `${title}\n\n${text}` : text);
  }
}

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

  // ==========================
  // EDITAR: leer datos desde API y precargar formulario
  // ==========================
  async function precargarEditar(btn) {
    const id = btn.dataset.id;
    if (!id) return;

    const modalId = 'modalEditarPublicacion';
    const modalEl = document.getElementById(modalId);
    const form = document.getElementById('formEditarPublicacion');

    if (!modalEl || !form) {
      console.warn('[EDITAR] Modal o formulario de edición no encontrado.');
      return;
    }

    // Reset de formulario
    form.reset();
    const hiddenId = form.querySelector('#edit_id');
    if (hiddenId) hiddenId.value = id;

    // Contenedor de imágenes
    const imgContainer = document.getElementById('editImagenesContainer');
    if (imgContainer) {
      imgContainer.innerHTML = '<small class="text-muted">Cargando imágenes…</small>';
    }

    try {
      const resp = await fetch(`${EV_API_BASE}/api/publicacion/${encodeURIComponent(id)}`, {
        method: 'GET'
      });

      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => {
          window.location.href = `${EV_API_BASE}/`;
        }, 1500);
        return;
      }

      if (!resp.ok || !data.ok || !data.data) {
        const msg = data.mensaje || data.error || 'No se pudo obtener los datos de la publicación.';
        evNotify('error', 'Error', msg);
        if (imgContainer) imgContainer.innerHTML = '<small class="text-danger">Error al cargar imágenes.</small>';
        return;
      }

      // Normalizar estructura:
      //  A) data.data = { codigo_tipo, codigo_categoria, ... }
      //  B) data.data = { publicacion: {...}, imagenes: [...] }
      let pub = data.data;
      let imagenes = [];

      if (pub.publicacion || pub.imagenes) {
        imagenes = Array.isArray(pub.imagenes) ? pub.imagenes : [];
        pub = pub.publicacion || {};
      } else {
        imagenes = Array.isArray(pub.imagenes) ? pub.imagenes : (Array.isArray(data.imagenes) ? data.imagenes : []);
      }

      console.log('[EDITAR][PUB]', pub);
      console.log('[EDITAR][IMAGENES]', imagenes);

      // ===== Campos principales =====
      const inputId          = form.querySelector('#edit_id');
      const inputTitulo      = form.querySelector('#edit_titulo');
      const inputPrecio      = form.querySelector('#edit_precio');
      const inputDescripcion = form.querySelector('#edit_descripcion');
      const selEstado        = form.querySelector('#edit_estado');

      if (inputId)          inputId.value          = pub.codigo_publicacion || id;
      if (inputTitulo)      inputTitulo.value      = pub.titulo || '';
      if (inputPrecio)      inputPrecio.value      = pub.precio ?? '';
      if (inputDescripcion) inputDescripcion.value = pub.descripcion || '';

      if (selEstado && pub.estado) {
        const norm = String(pub.estado).toLowerCase();
        if (norm === 'nuevo')      selEstado.value = 'Nuevo';
        else if (norm === 'usado') selEstado.value = 'Usado';
        else                       selEstado.value = 'NoAplica';
      }

      // ===== Tipo / Categoría =====
      const codTipo      = pub.codigo_tipo ?? '';
      const codCategoria = pub.codigo_categoria ?? '';

      console.log('[EDITAR][TIPO/CAT]', codTipo, codCategoria);

      if (window.evInitComboTipoCategoriaEdit) {
        window.evInitComboTipoCategoriaEdit(codTipo, codCategoria);
      } else {
        // Fallback: al menos fijar data-valor-registrado si el combo se inicializa después
        const comboTipo = document.getElementById('edit_comboTipo');
        const comboCat  = document.getElementById('edit_comboCategoria');
        if (comboTipo) comboTipo.dataset.valorRegistrado = codTipo ? String(codTipo) : '';
        if (comboCat)  comboCat.dataset.valorRegistrado  = codCategoria ? String(codCategoria) : '';
      }

      // ===== Imágenes =====
      if (imgContainer) {
        if (!imagenes.length) {
          imgContainer.innerHTML = '<small class="text-muted">No hay imágenes registradas para esta publicación.</small>';
        } else {
          imgContainer.innerHTML = '';
          imagenes.forEach((img) => {
            const url = img.url || img.ruta || '';
            if (!url) return;
            const item  = document.createElement('div');
            item.className = 'ev-edit-img-item';
            const image = document.createElement('img');
            image.src = url;
            image.alt = 'Imagen de publicación';
            item.appendChild(image);
            imgContainer.appendChild(item);
          });
        }
      }

      // Mostrar modal edit
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'No se pudo obtener los datos de la publicación.');
      if (imgContainer) {
        imgContainer.innerHTML = '<small class="text-danger">Error inesperado al cargar imágenes.</small>';
      }
    }
  }

  document.addEventListener('click', (e) => {
    // Botones de cabecera
    if (e.target.closest('#btnBuscar, #btnBuscarPublicacion')) {
      abrirModal('modalBuscarPublicacion');
      return;
    }
    if (e.target.closest('#btnAgregar, #btnAgregarPublicacion')) {
      abrirModal('modalAgregarPublicacion');
      return;
    }

    // Botón Editar en la tabla
    const btnEditar = e.target.closest('[data-action="editar"], .btn-editar');
    if (btnEditar) {
      precargarEditar(btnEditar);
      return;
    }
  });

  // Buscar (por ahora solo log)
  document.getElementById('formBuscarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault();
    console.log('[BUSCAR]', Object.fromEntries(new FormData(e.target)));
  });

  // Submit de edición (por ahora no guarda en backend, solo log para no romper nada)
  document.getElementById('formEditarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault();
    console.log('[EDITAR][PENDIENTE_API]', Object.fromEntries(new FormData(e.target)));
    evNotify('info', 'Editar publicación', 'La lógica para guardar cambios se implementará en una siguiente iteración. Los datos ya se cargan correctamente.');
  });
})();

/* ==============================
   Uploader + Previsualización — dropZone central
   (exponemos evGetFotosAgregar() para el submit)
============================== */
(function () {
  const MAX_MB = 5;
  let initialized = false;

  const $ = (s, root = document) => root.querySelector(s);

  function notify(msg) {
    evNotify('info', 'Aviso', msg);
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

    const form    = modalEl.querySelector('#formAgregarPublicacion');
    const input   = $('#inputImagenes', modalEl);
    const tiles   = $('#evTiles', modalEl);
    const tileAdd = $('#tileAgregar', modalEl); // se mantiene por compatibilidad, pero va oculto por CSS
    const btnClr  = $('#btnLimpiarImagenes', modalEl);

    const lblCntHeader  = $('#contadorImagenes', modalEl);          // arriba, al lado de "Fotos •"
    const lblCntToolbar = $('#contadorImagenesToolbar', modalEl);   // abajo, en "0/10 fotos cargadas"

    const dropZone = document.getElementById('dropZone');

    if (!input || !tiles) return;

    const MAX_FILES = Number(input.dataset.max || 10);

    /** @type {{id:string,file:File,url:string}[]} */
    let fotos = [];
    let selectedIndex = 0;

    // Exponer las fotos al resto del JS (para el submit)
    window.evGetFotosAgregar = function () {
      return fotos.map(f => f.file);
    };

    const rebuildFileList = () => {
      const dt = new DataTransfer();
      fotos.forEach(f => dt.items.add(f.file));
      input.files = dt.files;
    };

    const revokeAll = () => fotos.forEach(f => f.url && URL.revokeObjectURL(f.url));

    const setCount = () => {
      const count = fotos.length;
      if (lblCntHeader)  lblCntHeader.textContent  = String(count);
      if (lblCntToolbar) lblCntToolbar.textContent = String(count);
      if (form)          form.dataset.evFotosCount = String(count); // ← usado en la validación
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
        const mkBtn = (html, titleTxt, cb, extra = 'btn-outline-success') => {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = `btn btn-sm ${extra}`;
          b.innerHTML = html;
          b.title = titleTxt;
          b.addEventListener('click', cb);
          return b;
        };
        actions.append(
          mkBtn('<i class="bi bi-arrows-fullscreen"></i>', 'Expandir/Contraer', () => previewWrapper.classList.toggle('is-expanded')),
          mkBtn('<i class="bi bi-chevron-left"></i>', 'Anterior', () => {
            if (!fotos.length) return;
            selectedIndex = (selectedIndex - 1 + fotos.length) % fotos.length;
            updateMain(); renderThumbs();
          }),
          mkBtn('<i class="bi bi-chevron-right"></i>', 'Siguiente', () => {
            if (!fotos.length) return;
            selectedIndex = (selectedIndex + 1) % fotos.length;
            updateMain(); renderThumbs();
          }),
          mkBtn('<i class="bi bi-trash"></i>', 'Quitar todas', () => {
            revokeAll(); fotos = []; rebuildFileList(); selectedIndex = 0; paint();
          }, 'btn-cancelar')
        );
        title.appendChild(actions);

        const main = document.createElement('div');
        main.className = 'ev-preview-main';
        previewMainImg = document.createElement('img');
        previewMainImg.alt = 'Vista previa';
        main.appendChild(previewMainImg);

        previewThumbs = document.createElement('div');
        previewThumbs.className = 'ev-preview-thumbs';

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

    function updateMain() {
      if (!fotos.length) { if (previewMainImg) previewMainImg.src = ''; return; }
      const target = fotos[selectedIndex] || fotos[0];
      previewMainImg.src = target.url;
    }

    function renderThumbs() {
      if (!previewThumbs) return;
      previewThumbs.innerHTML = '';
      fotos.forEach((f, i) => {
        const th = document.createElement('div');
        th.className = 'ev-preview-thumb' + (i === selectedIndex ? ' active' : '');
        const img = document.createElement('img'); img.src = f.url;
        th.appendChild(img);
        th.addEventListener('click', () => { selectedIndex = i; updateMain(); renderThumbs(); });
        previewThumbs.appendChild(th);
      });
    }

    function showPreviewArea(show) { ensurePreviewArea(); previewWrapper.style.display = show ? '' : 'none'; }

    // --------- Render miniaturas ----------
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

      // Tile "Agregar" se sigue creando para compatibilidad, aunque esté oculto por CSS
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

    function paint() {
      renderTiles();
      if (fotos.length) {
        showPreviewArea(true);
        if (selectedIndex >= fotos.length) selectedIndex = 0;
        updateMain(); renderThumbs();
      } else {
        showPreviewArea(false);
      }
    }

    // --------- Gestión común de archivos ----------
    function agregarArchivos(fileList) {
      const nuevos = Array.from(fileList || []);
      if (!nuevos.length) return;

      for (const file of nuevos) {
        if (fotos.length >= MAX_FILES) { notify(`Máximo ${MAX_FILES} imágenes.`); break; }
        if (!validarArchivo(file)) continue;

        const dup = fotos.some(f => f.file.name === file.name && f.file.size === file.size && f.file.lastModified === file.lastModified);
        if (dup) continue;

        fotos.push({ id: crypto.randomUUID(), file, url: URL.createObjectURL(file) });
      }
      rebuildFileList();
      if (fotos.length) selectedIndex = 0;
      paint();
    }

    // Input file
    input.addEventListener('change', (e) => {
      agregarArchivos(e.target.files || []);
      input.value = '';
    });

    // TileAdd (aunque esté oculto)
    tileAdd?.addEventListener('click', () => input.click());

    // DropZone: click + drag&drop
    dropZone?.addEventListener('click', () => input.click());

    if (dropZone) {
      ['dragenter','dragover'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => {
          e.preventDefault(); e.stopPropagation();
          dropZone.classList.add('drag-over');
        });
      });
      ['dragleave','dragend','drop'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => {
          e.preventDefault(); e.stopPropagation();
          dropZone.classList.remove('drag-over');
        });
      });
      dropZone.addEventListener('drop', (e) => {
        agregarArchivos(e.dataTransfer?.files || []);
      });
    }

    // También soportamos soltar sobre el área de miniaturas
    ['dragenter','dragover'].forEach(evt => {
      tiles.addEventListener(evt, (e) => {
        e.preventDefault(); e.stopPropagation();
      });
    });
    ['dragleave','dragend','drop'].forEach(evt => {
      tiles.addEventListener(evt, (e) => {
        e.preventDefault(); e.stopPropagation();
      });
    });
    tiles.addEventListener('drop', (e) => {
      agregarArchivos(e.dataTransfer?.files || []);
    });

    // Limpiar todo
    btnClr?.addEventListener('click', () => {
      revokeAll(); fotos = []; rebuildFileList(); selectedIndex = 0;
      tiles.innerHTML = '';
      setCount();
      const wrap = document.getElementById('evPreviewWrapper');
      if (wrap) wrap.style.display = 'none';
      const t = document.getElementById('evMetaTitle'); if (t) t.textContent = 'Título';
      const p = document.getElementById('evMetaPrice'); if (p) p.textContent = 'S/ 0.00';
      const d = document.getElementById('evMetaDesc');  if (d) d.textContent = 'La descripción aparecerá aquí.';
    });

    // Navegación con flechas
    modalEl.addEventListener('keydown', (ev) => {
      if (!fotos.length) return;
      if (ev.key === 'ArrowRight' || ev.key === 'ArrowLeft') {
        ev.preventDefault();
        const dir = ev.key === 'ArrowRight' ? 1 : -1;
        selectedIndex = (selectedIndex + dir + fotos.length) % fotos.length;
        updateMain(); renderThumbs();
      }
    });

    // Meta (título/precio/desc)
    function updateMeta() {
      if (!metaTitleEl || !metaPriceEl || !metaDescEl) return;
      const title   = modalEl.querySelector('input[name="titulo"]')?.value?.trim() || 'Título';
      const priceRaw= modalEl.querySelector('input[name="precio"]')?.value || '';
      const desc    = modalEl.querySelector('textarea[name="descripcion"]')?.value?.trim() || 'La descripción aparecerá aquí.';
      const n = Number(priceRaw || 0);
      const precio = isNaN(n) ? '0.00' : n.toFixed(2);
      metaTitleEl.textContent = title;
      metaPriceEl.textContent = `S/ ${precio}`;
      metaDescEl.textContent  = desc;
    }
    modalEl.querySelector('input[name="titulo"]')?.addEventListener('input', updateMeta);
    modalEl.querySelector('input[name="precio"]')?.addEventListener('input', updateMeta);
    modalEl.querySelector('textarea[name="descripcion"]')?.addEventListener('input', updateMeta);

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
   UX extra: selects Tipo/Categoría (alta)
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

/* ===== Fallback irrompible: altura del modal ===== */
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

    content.style.setProperty('--ev-footer-h', `${fH}px`);

    const cs  = getComputedStyle(body);
    const pvt = parseFloat(cs.paddingTop||'0') + parseFloat(cs.paddingBottom||'0');

    content.style.maxHeight = `${available}px`;
    const bodyH = Math.max(160, available - hH - fH - pvt);
    body.style.height = `${bodyH}px`;
    body.style.overflowY = 'auto';
    body.style.overflowX = 'hidden';
    body.style.minHeight = '0';
    body.style.webkitOverflowScrolling = 'touch';
  }

  function handleShown(e){
    const id = e.target?.id;
    if(!id) return;
    if(id==='modalAgregarPublicacion' || id==='modalBuscarPublicacion' || id==='modalEditarPublicacion'){
      setTimeout(()=>tuneModal(id), 0);
    }
  }

  function handleResize(){
    ['modalAgregarPublicacion','modalBuscarPublicacion','modalEditarPublicacion'].forEach(tuneModal);
  }

  document.addEventListener('shown.bs.modal', handleShown);
  window.addEventListener('resize', handleResize);
  window.addEventListener('orientationchange', handleResize);
})();

/* ==============================
   Form: Registrar publicación (API)
   (delegado para vistas cargadas dinámicamente)
============================== */
(function () {

  async function registrarPublicacion(e) {
    e.preventDefault();

    const form = e.target;
    if (!form || form.id !== 'formAgregarPublicacion') return;

    const btnGuardar     = form.querySelector('.btn-guardar');
    const inputImagenes  = form.querySelector('#inputImagenes');

    // ===== 1) Validación básica en front =====
    const titulo      = form.titulo?.value?.trim();
    const precio      = form.precio?.value;
    const descripcion = form.descripcion?.value?.trim();

    if (!titulo) {
      evNotify('warning', 'Validación', 'Debes ingresar un título para la publicación.');
      return;
    }
    if (!precio || Number(precio) <= 0) {
      evNotify('warning', 'Validación', 'El precio debe ser mayor a 0.');
      return;
    }
    if (!descripcion) {
      evNotify('warning', 'Validación', 'Debes ingresar una descripción.');
      return;
    }

    const fotosCountAttr = Number(form.dataset.evFotosCount || '0');

    let filesSeleccionados = [];
    if (typeof window.evGetFotosAgregar === 'function') {
      filesSeleccionados = window.evGetFotosAgregar() || [];
    } else if (inputImagenes && inputImagenes.files && inputImagenes.files.length) {
      filesSeleccionados = Array.from(inputImagenes.files);
    }

    const tieneFotos = filesSeleccionados.length > 0;

    if (!tieneFotos && !fotosCountAttr) {
      evNotify('warning', 'Validación', 'Debes agregar al menos una imagen.');
      return;
    }

    // ===== 2) Construir FormData a mano (texto + archivos) =====
    const fd = new FormData();
    fd.append('titulo', titulo);
    fd.append('precio', precio);
    fd.append('estado', form.estado?.value || 'NoAplica');
    fd.append('comboTipo', form.comboTipo?.value || '');
    fd.append('categoria', form.categoria?.value || '');
    fd.append('descripcion', descripcion);

    filesSeleccionados.forEach((file) => {
      fd.append('imagenes[]', file);
    });

    const setSaving = (saving) => {
      if (!btnGuardar) return;
      if (saving) {
        btnGuardar.classList.add('saving');
        btnGuardar.disabled = true;
      } else {
        btnGuardar.classList.remove('saving');
        btnGuardar.disabled = false;
      }
    };

    try {
      setSaving(true);

      const resp = await fetch(`${EV_API_BASE}/api/publicacion/registrar`, {
        method: 'POST',
        body: fd
      });

      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => {
          window.location.href = `${EV_API_BASE}/`;
        }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        const msg = data.mensaje || data.error || 'No se pudo registrar la publicación.';
        evNotify('error', 'Error', msg);
        console.error('[AGREGAR][ERROR]', data);
        return;
      }

      // Éxito
      evNotify('success', 'Publicación registrada', 'Tu publicación se ha registrado correctamente.');

      // Reset del formulario
      form.reset();
      form.dataset.evFotosCount = '0';

      // Limpiar visualmente el uploader
      const btnLimpiar = document.getElementById('btnLimpiarImagenes');
      btnLimpiar?.click();

      // Cerrar modal
      const modalEl = document.getElementById('modalAgregarPublicacion');
      if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.hide();
      }

      // Recargar tabla de publicaciones si la función está disponible
      if (window.evCargarPublicaciones) {
        window.evCargarPublicaciones();
      }

      console.log('[AGREGAR][OK]', data);

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al registrar la publicación.');
    } finally {
      setSaving(false);
    }
  }

  // Delegado: sirve aunque el formulario se inyecte dinámicamente
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form && form.id === 'formAgregarPublicacion') {
      registrarPublicacion(e);
    }
  });
})();


/* ==============================
   Listar publicaciones en tabla
============================== */
(function () {

  async function cargarPublicaciones() {
    const table = document.getElementById('tablaPublicaciones');
    const tbody = table?.querySelector('tbody');
    if (!table || !tbody) return;

    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center py-4 text-muted">
          Cargando publicaciones…
        </td>
      </tr>`;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/publicacion/listar`, { method: 'GET' });
      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        const msg = data.mensaje || data.error || 'No se pudo obtener el listado de publicaciones.';
        tbody.innerHTML = `
          <tr>
            <td colspan="6" class="text-center py-4 text-danger">
              ${msg}
            </td>
          </tr>`;
        return;
      }

      const items = Array.isArray(data.data) ? data.data : [];

      if (!items.length) {
        tbody.innerHTML = `
          <tr>
            <td colspan="6" class="text-center py-4 text-muted">
              Aún no tienes publicaciones registradas.
            </td>
          </tr>`;
        return;
      }

      const rowsHtml = items.map((pub) => {
        const cod     = String(pub.codigo_publicacion ?? '').padStart(6, '0');
        const titulo  = (pub.titulo || '').substring(0, 80);
        const precio  = Number(pub.precio || 0).toFixed(2);
        const estado  = (pub.estado || '').toUpperCase();
        const fecha   = pub.fecha_creacion || '';

        let estadoClass = 'badge bg-secondary';
        if (estado === 'NUEVO')         estadoClass = 'badge bg-success';
        else if (estado === 'USADO')    estadoClass = 'badge bg-warning text-dark';
        else if (estado === 'NOAPLICA') estadoClass = 'badge bg-light text-muted';

        return `
          <tr>
            <td data-label="Código">
              <span class="ev-code">${cod}</span>
            </td>
            <td data-label="Título" class="td-trunc" title="${titulo}">
              ${titulo || '-'}
            </td>
            <td data-label="Precio">
              S/ ${precio}
            </td>
            <td data-label="Estado">
              <span class="${estadoClass}">${estado || '-'}</span>
            </td>
            <td data-label="Fecha">
              ${fecha}
            </td>
            <td data-label="Opciones" class="text-center">
              <div class="ev-actions">
                <button class="ev-chip ev-chip-green" data-action="editar" data-id="${pub.codigo_publicacion}">
                  Editar
                </button>
                <button class="ev-chip ev-chip-amber" data-action="ver" data-id="${pub.codigo_publicacion}">
                  Ver
                </button>
                <button class="ev-chip ev-chip-red" data-action="anular" data-id="${pub.codigo_publicacion}">
                  Anular
                </button>
              </div>
            </td>
          </tr>`;
      }).join('');

      tbody.innerHTML = rowsHtml;

    } catch (err) {
      console.error(err);
      tbody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-4 text-danger">
            Ocurrió un error al cargar las publicaciones.
          </td>
        </tr>`;
    }
  }

  window.evCargarPublicaciones = cargarPublicaciones;

  let lastTable = null;

  function tryInitListado() {
    const table = document.getElementById('tablaPublicaciones');
    if (table && table !== lastTable) {
      lastTable = table;
      cargarPublicaciones();
    }
  }

  document.addEventListener('DOMContentLoaded', tryInitListado);

  const target = document.getElementById('contenido-principal') || document.body;
  const observer = new MutationObserver(() => {
    tryInitListado();
  });
  observer.observe(target, { childList: true, subtree: true });

})();
