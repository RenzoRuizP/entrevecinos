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
   Utilidad general: BASE_URL + SweetAlert helper
============================== */
const EV_API_BASE = (function () {
  try {
    if (window.BASE_URL) {
      return String(window.BASE_URL).replace(/\/+$/, '');
    }
  } catch (_) {}
  return '';
})();

function evNotify(tipo, titulo, texto) {
  if (window.Swal?.fire) {
    Swal.fire({
      icon: tipo,
      title: titulo,
      text: texto,
      confirmButtonText: 'Aceptar'
    });
  } else {
    alert(`${titulo} - ${texto}`);
  }
}

/* ==============================
   Modales + acciones básicas
============================== */
//$('#modalAgregarPublicacion').modal({backdrop: 'static', keyboard: false})
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
    // Ajustado a tus IDs reales
    if (e.target.closest("#btnBuscar")) {
      abrirModal('modalBuscarPublicacion');
      return;
    }
    if (e.target.closest("#btnAgregar")) {
      abrirModal('modalAgregarPublicacion');
      return;
    }

    const btnEditar = e.target.closest('[data-action="editar"], .btn-editar');
    if (btnEditar) {
      precargarEditar(btnEditar);
      abrirModal('modalEditarPublicacion');
    }
  });

  // Buscar (se queda en consola por ahora)
  document.getElementById('formBuscarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault();
    console.log('[BUSCAR]', Object.fromEntries(new FormData(e.target)));
  });

  // Editar (placeholder)
  document.getElementById('formEditarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault();
    console.log('[EDITAR]', Object.fromEntries(new FormData(e.target)));
  });

  // NO manejamos aquí el submit de AGREGAR: se gestiona en el módulo de API
})();


/* ==============================
   Uploader + Previsualización — dropZone central
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
      if (form)          form.dataset.evFotosCount = String(count); // ← clave para la validación
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
        const mkBtn = (html, titleText, cb, extra = 'btn-outline-success') => {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = `btn btn-sm ${extra}`;
          b.innerHTML = html;
          b.title = titleText;
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

      // Tile "Agregar" (se mantiene aunque esté oculto por CSS)
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
      const title = modalEl.querySelector('input[name="titulo"]')?.value?.trim() || 'Título';
      const priceRaw = modalEl.querySelector('input[name="precio"]')?.value || '';
      const desc = modalEl.querySelector('textarea[name="descripcion"]')?.value?.trim() || 'La descripción aparecerá aquí.';
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

    // Validación básica en front
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

    const fotosCount = Number(form.dataset.evFotosCount || '0');
    const hasFiles   = inputImagenes && inputImagenes.files && inputImagenes.files.length > 0;

    if (!hasFiles && !fotosCount) {
      evNotify('warning', 'Validación', 'Debes agregar al menos una imagen.');
      return;
    }


    const fd = new FormData(form);

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
        return;
      }

      // Éxito
      evNotify('success', 'Publicación registrada', 'Tu publicación se ha registrado correctamente.');

      // Reset del formulario
      form.reset();

      // Limpiar visualmente el uploader
      const btnLimpiar = document.getElementById('btnLimpiarImagenes');
      btnLimpiar?.click();

      // Cerrar modal
      const modalEl = document.getElementById('modalAgregarPublicacion');
      if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.hide();
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
