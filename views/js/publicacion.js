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

  /* ---------------------------------------
     Helpers para imágenes (EDITAR)
  ----------------------------------------*/
  function evGetFotoSrc(f) {
    if (!f) return '';
    if (typeof f === 'string') return f;
    return f.url || f.ruta || f.ruta_imagen || f.imagen || f.path || f.path_imagen || '';
  }

  function evGetFotoId(f, idx) {
    if (!f || typeof f !== 'object') return 'old_' + idx;
    return f.id_imagen || f.codigo_imagen || f.id || f.codigo || ('old_' + idx);
  }

  /* ---------------------------------------
     Previsualización en EDITAR
  ----------------------------------------*/
  const evEditPreview = {
    inited: false,
    wrapper: null,
    mainImg: null,
    thumbs: null,
    metaTitle: null,
    metaPrice: null,
    metaDesc: null
  };

  function evEnsurePreviewEditar() {
    if (evEditPreview.inited && evEditPreview.wrapper) return evEditPreview;

    const container = document.getElementById('evPreviewWrapperEditContainer');
    if (!container) return null;

    container.innerHTML = `
      <div id="evPreviewWrapperEdit" class="ev-preview-area">
        <div class="ev-preview-title">
          <span><i class="bi bi-images me-1"></i>Previsualización</span>
        </div>
        <div class="ev-preview-main">
          <img id="evPreviewMainImgEdit" alt="Vista previa">
        </div>
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

    // Enlazar actualización en vivo de título, precio y descripción
    const modal = document.getElementById('modalEditarPublicacion');
    if (modal && !modal.dataset.evMetaBound) {
      modal.dataset.evMetaBound = '1';

      const updateMetaLive = () => {
        const title   = modal.querySelector('#edit_titulo')?.value?.trim() || 'Título';
        const priceRaw= modal.querySelector('#edit_precio')?.value || '';
        const desc    = modal.querySelector('#edit_descripcion')?.value?.trim() || 'La descripción aparecerá aquí.';
        const n       = Number(priceRaw || 0);
        const precio  = isNaN(n) ? '0.00' : n.toFixed(2);

        if (evEditPreview.metaTitle) evEditPreview.metaTitle.textContent = title;
        if (evEditPreview.metaPrice) evEditPreview.metaPrice.textContent = `S/ ${precio}`;
        if (evEditPreview.metaDesc)  evEditPreview.metaDesc.textContent  = desc;
      };

      modal.querySelector('#edit_titulo')?.addEventListener('input', updateMetaLive);
      modal.querySelector('#edit_precio')?.addEventListener('input', updateMetaLive);
      modal.querySelector('#edit_descripcion')?.addEventListener('input', updateMetaLive);
    }

    return evEditPreview;
  }

  function evPintarPreviewEditar(pub, fotos) {
    const st = evEnsurePreviewEditar();
    if (!st) return;

    const { wrapper, mainImg, thumbs, metaTitle, metaPrice, metaDesc } = st;

    const titulo = (pub?.titulo || '').trim() || 'Título';
    const n      = Number(pub?.precio || 0);
    const precio = isNaN(n) ? '0.00' : n.toFixed(2);
    const desc   = (pub?.descripcion || '').trim() || 'La descripción aparecerá aquí.';

    if (metaTitle) metaTitle.textContent = titulo;
    if (metaPrice) metaPrice.textContent = `S/ ${precio}`;
    if (metaDesc)  metaDesc.textContent  = desc;

    if (!Array.isArray(fotos) || !fotos.length) {
      if (wrapper) wrapper.style.display = 'none';
      if (thumbs) thumbs.innerHTML = '';
      if (mainImg) mainImg.src = '';
      return;
    }

    wrapper.style.display = '';

    const firstSrc = evGetFotoSrc(fotos[0]);
    if (mainImg && firstSrc) mainImg.src = firstSrc;

    if (thumbs) {
      thumbs.innerHTML = '';
      fotos.forEach((f, idx) => {
        const src = evGetFotoSrc(f);
        if (!src) return;
        const d = document.createElement('div');
        d.className = 'ev-preview-thumb' + (idx === 0 ? ' active' : '');
        const im = document.createElement('img');
        im.src = src;
        d.appendChild(im);
        d.addEventListener('click', () => {
          if (mainImg) mainImg.src = src;
          const all = thumbs.querySelectorAll('.ev-preview-thumb');
          all.forEach((node, i) => {
            if (i === idx) node.classList.add('active');
            else node.classList.remove('active');
          });
        });
        thumbs.appendChild(d);
      });
    }
  }

  /* ---------------------------------------
     Uploader EDITAR (opción A)
  ----------------------------------------*/
  (function initUploaderEditarModule(){
    const MAX_MB = 5;
    const MAX_FILES = 10;

    const state = {
      inited: false,
      fotosExistentes: [],  // {id, src}
      fotosNuevas: [],      // {id, file, url}
      eliminadas: []        // ids de existentes
    };

    const els = {
      modal: null,
      form: null,
      input: null,
      tiles: null,
      dropZone: null,
      btnClear: null,
      cntHeader: null,
      cntToolbar: null
    };

    const $ = (s, root=document) => root.querySelector(s);

    function notify(msg){ evNotify('info','Aviso',msg); }

    function validarArchivo(file) {
      const okTipo = /^image\/(jpeg|png|webp|gif|bmp|svg\+xml)$/i.test(file.type) || file.type.startsWith('image/');
      const okPeso = file.size <= MAX_MB * 1024 * 1024;
      if (!okTipo) { notify('Solo se permiten archivos de imagen.'); return false; }
      if (!okPeso) { notify(`"${file.name}" supera ${MAX_MB} MB.`); return false; }
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

    function renderTiles(pub) {
      if (!els.tiles) return;
      els.tiles.innerHTML = '';

      const todas = buildTodas();
      const existentesActivos = buildActivasExistentes();

      // Existentes
      existentesActivos.forEach((f) => {
        const tile = document.createElement('div');
        tile.className = 'ev-tile';

        const img = document.createElement('img');
        img.src = f.src;
        img.alt = 'Imagen actual';

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ev-tile-remove';
        del.title = 'Quitar imagen';
        del.textContent = '×';
        del.addEventListener('click', (ev) => {
          ev.stopPropagation();
          if (!state.eliminadas.includes(f.id)) {
            state.eliminadas.push(f.id);
          }
          paint(pub);
        });

        tile.append(img, del);
        els.tiles.appendChild(tile);
      });

      // Nuevas
      state.fotosNuevas.forEach((f) => {
        const tile = document.createElement('div');
        tile.className = 'ev-tile';

        const img = document.createElement('img');
        img.src = f.url;
        img.alt = f.file?.name || 'Imagen nueva';

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ev-tile-remove';
        del.title = 'Quitar imagen';
        del.textContent = '×';
        del.addEventListener('click', (ev) => {
          ev.stopPropagation();
          const idx = state.fotosNuevas.findIndex(x => x.id === f.id);
          if (idx !== -1) {
            const removed = state.fotosNuevas.splice(idx,1)[0];
            if (removed?.url) URL.revokeObjectURL(removed.url);
          }
          paint(pub);
        });

        tile.append(img, del);
        els.tiles.appendChild(tile);
      });

      // Tile "Agregar" si aún hay espacio
      if (todas.length < MAX_FILES) {
        const add = document.createElement('div');
        add.className = 'ev-tile ev-tile-add';
        add.innerHTML = `
          <div class="ico"><i class="bi bi-plus-lg"></i></div>
          <div class="t1">Agregar fotos</div>
          <div class="t2">o arrastra y suelta</div>
        `;
        add.addEventListener('click', () => {
          if (els.input) els.input.click();
        });
        els.tiles.appendChild(add);
      }

      setCount();
    }

    function paint(pub) {
      const todas = buildTodas();
      renderTiles(pub);
      evPintarPreviewEditar(pub, todas);
    }

    function agregarArchivos(fileList, pub) {
      const nuevos = Array.from(fileList || []);
      if (!nuevos.length) return;

      const actuales = buildTodas().length;

      for (const file of nuevos) {
        if (actuales + state.fotosNuevas.length >= MAX_FILES) {
          notify(`Máximo ${MAX_FILES} imágenes.`);
          break;
        }
        if (!validarArchivo(file)) continue;

        const dup = state.fotosNuevas.some(f =>
          f.file.name === file.name &&
          f.file.size === file.size &&
          f.file.lastModified === file.lastModified
        );
        if (dup) continue;

        state.fotosNuevas.push({
          id: 'new_' + crypto.randomUUID(),
          file,
          url: URL.createObjectURL(file)
        });
      }

      paint(pub);
    }

    function bindEvents() {
      if (state.inited || !els.modal) return;

      // input
      els.input?.addEventListener('change', (e) => {
        const modal = els.modal;
        if (!modal) return;
        const pub = modal._evPubActual || {};
        agregarArchivos(e.target.files || [], pub);
        e.target.value = '';
      });

      // dropzone
      if (els.dropZone) {
        ['dragenter','dragover'].forEach(evt => {
          els.dropZone.addEventListener(evt, (e) => {
            e.preventDefault(); e.stopPropagation();
            els.dropZone.classList.add('drag-over');
          });
        });
        ['dragleave','dragend','drop'].forEach(evt => {
          els.dropZone.addEventListener(evt, (e) => {
            e.preventDefault(); e.stopPropagation();
            els.dropZone.classList.remove('drag-over');
          });
        });
        els.dropZone.addEventListener('drop', (e) => {
          const modal = els.modal;
          if (!modal) return;
          const pub = modal._evPubActual || {};
          agregarArchivos(e.dataTransfer?.files || [], pub);
        });

        els.dropZone.addEventListener('click', () => {
          if (els.input) els.input.click();
        });
      }

      // tiles también aceptan drop
      if (els.tiles) {
        ['dragenter','dragover'].forEach(evt => {
          els.tiles.addEventListener(evt, (e) => {
            e.preventDefault(); e.stopPropagation();
          });
        });
        ['dragleave','dragend','drop'].forEach(evt => {
          els.tiles.addEventListener(evt, (e) => {
            e.preventDefault(); e.stopPropagation();
          });
        });
        els.tiles.addEventListener('drop', (e) => {
          const modal = els.modal;
          if (!modal) return;
          const pub = modal._evPubActual || {};
          agregarArchivos(e.dataTransfer?.files || [], pub);
        });
      }

      // Limpiar todo
      els.btnClear?.addEventListener('click', () => {
        state.fotosExistentes = [];
        state.fotosNuevas.forEach(f => f.url && URL.revokeObjectURL(f.url));
        state.fotosNuevas = [];
        state.eliminadas = [];
        paint(els.modal?._evPubActual || {});
      });

      state.inited = true;
    }

    function resetStateFromBackend(pub, fotosBackend) {
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

      if (els.modal) els.modal._evPubActual = pub || {};
      paint(pub || {});
    }

    // función pública para usar desde cargarPublicacionEditar
    window.evInitUploaderEditar = function(pub, fotosBackend) {
      const modal = document.getElementById('modalEditarPublicacion');
      if (!modal) return;

      els.modal    = modal;
      els.form     = modal.querySelector('#formEditarPublicacion');
      els.input    = modal.querySelector('#inputImagenesEdit');
      els.tiles    = modal.querySelector('#evTilesEdit');
      els.dropZone = modal.querySelector('#dropZoneEdit');
      els.btnClear = modal.querySelector('#btnLimpiarImagenesEdit');
      els.cntHeader  = modal.querySelector('#contadorImagenesEdit');
      els.cntToolbar = modal.querySelector('#contadorImagenesToolbarEdit');

      bindEvents();
      resetStateFromBackend(pub, fotosBackend);

      // Exponer estado para futura API de actualización
      window.evGetEstadoImagenesEditar = function () {
        return {
          existentes: state.fotosExistentes,
          eliminadas: state.eliminadas.slice(),
          nuevas: state.fotosNuevas
        };
      };
    };
  })();

  // ========================
  // Cargar datos al modal Editar
  // ========================
  async function cargarPublicacionEditar(codPublicacion) {
    try {
      const resp = await fetch(`${EV_API_BASE}/api/publicacion/${codPublicacion}`);
      const data = await resp.json();

      console.log("[EDITAR][PUB] ", data);

      if (!data.ok) {
        evNotify("error", "Error", data.mensaje || "Error al cargar la publicación.");
        return;
      }

      const pub   = data.data.publicacion;
      const fotos = data.data.imagenes || [];

      // ====== Completar campos ======
      document.querySelector("#edit_id").value          = pub.codigo_publicacion;
      document.querySelector("#edit_titulo").value      = pub.titulo || "";
      document.querySelector("#edit_precio").value      = pub.precio || "";
      document.querySelector("#edit_estado").value      = pub.estado || "Nuevo";
      document.querySelector("#edit_descripcion").value = pub.descripcion || "";

      // Guardar los valores que luego combo_tipo.js usará
      const comboTipo = document.getElementById("edit_comboTipo");
      const comboCat  = document.getElementById("edit_comboCategoria");

      if (comboTipo) comboTipo.dataset.valorRegistrado = pub.codigo_tipo || "";
      if (comboCat)  comboCat.dataset.valorRegistrado  = pub.codigo_categoria || "";

      // ====== Inicializar uploader de edición (imágenes existentes + nuevas) ======
      if (window.evInitUploaderEditar) {
        window.evInitUploaderEditar(pub, fotos);
      } else {
        // fallback mínimo: al menos mostrar en preview
        evPintarPreviewEditar(pub, fotos);
      }

      // ====== Mostrar modal ======
      const modalEl = document.getElementById("modalEditarPublicacion");
      if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      }

      // Disparar carga de combos (Tipo → Categoría) si está disponible
      if (window.evInitComboTipoCategoriaEdit) {
        window.evInitComboTipoCategoriaEdit(pub.codigo_tipo, pub.codigo_categoria);
      }

    } catch (e) {
      console.error("Error cargando publicación:", e);
      evNotify("error", "Error", "No se pudo cargar los datos.");
    }
  }

  // Listener ÚNICO de clicks (no anidado)
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

    // Botón Editar dentro de la tabla (usa data-action="editar")
    const btnEditar = e.target.closest('[data-action="editar"][data-id]');
    if (btnEditar) {
      const id = btnEditar.getAttribute('data-id');
      if (id) {
        cargarPublicacionEditar(id);
      }
      return;
    }
  });

  // Buscar (por ahora solo log)
  document.getElementById('formBuscarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault();
    console.log('[BUSCAR]', Object.fromEntries(new FormData(e.target)));
  });

  // ================================
  // Submit de edición (delegado)
  // ================================
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!form || form.id !== 'formEditarPublicacion') return;

    e.preventDefault();

    const datos = Object.fromEntries(new FormData(form));
    const estadoImgs = typeof window.evGetEstadoImagenesEditar === 'function'
      ? window.evGetEstadoImagenesEditar()
      : null;

    console.log('[EDITAR][PENDIENTE_API]', {
      formulario: datos,
      imagenes: estadoImgs
    });
    
    evNotify(
      'info',
      'Editar publicación',
      'La lógica para guardar cambios en el backend se implementará en una siguiente iteración. ' +
      'Por ahora, el formulario ya no redirige y las imágenes se gestionan correctamente en memoria.'
    );
  });

    // ================================
    // Submit de edición (delegado + API real)
    // ================================
    async function actualizarPublicacion(form) {
      const btnGuardar = form.querySelector('.btn-guardar') || form.querySelector('button[type="submit"]');

      const setSaving = (saving) => {
        if (!btnGuardar) return;
        btnGuardar.disabled = saving;
        if (saving) {
          btnGuardar.classList.add('saving');
        } else {
          btnGuardar.classList.remove('saving');
        }
      };

      const id          = form.querySelector('#edit_id')?.value || '';
      const titulo      = form.querySelector('#edit_titulo')?.value?.trim() || '';
      const precioRaw   = form.querySelector('#edit_precio')?.value || '';
      const estado      = form.querySelector('#edit_estado')?.value || 'NoAplica';
      const descripcion = form.querySelector('#edit_descripcion')?.value?.trim() || '';

      const comboTipo   = form.querySelector('#edit_comboTipo')?.value || '';
      const categoria   = form.querySelector('#edit_comboCategoria')?.value || '';

      if (!id) {
        evNotify('error', 'Error', 'No se encontró el código de la publicación.');
        return;
      }
      if (!titulo) {
        evNotify('warning', 'Validación', 'Debes ingresar un título para la publicación.');
        return;
      }
      const precio = Number(precioRaw || 0);
      if (!precio || precio <= 0) {
        evNotify('warning', 'Validación', 'El precio debe ser mayor a 0.');
        return;
      }
      if (!descripcion) {
        evNotify('warning', 'Validación', 'Debes ingresar una descripción.');
        return;
      }

      // Estado de imágenes (existentes / eliminadas / nuevas) desde el uploader
      const estadoImgs = typeof window.evGetEstadoImagenesEditar === 'function'
        ? window.evGetEstadoImagenesEditar()
        : { existentes: [], eliminadas: [], nuevas: [] };

      const fd = new FormData();
      // Estos nombres coinciden con lo que espera tu controlador
      fd.append('titulo', titulo);
      fd.append('precio', precio.toString());
      fd.append('estado', estado);
      fd.append('comboTipo', comboTipo);
      fd.append('categoria', categoria);
      fd.append('descripcion', descripcion);

      // IDs de imágenes eliminadas
      const arrEliminadas = Array.isArray(estadoImgs.eliminadas) ? estadoImgs.eliminadas : [];
      fd.append('imagenes_eliminadas', JSON.stringify(arrEliminadas));

      // Imágenes nuevas (files) -> tu PHP espera "imagenes_nuevas"
      const nuevas = Array.isArray(estadoImgs.nuevas) ? estadoImgs.nuevas : [];
      nuevas.forEach((item) => {
        if (item && item.file instanceof File) {
          fd.append('imagenes_nuevas[]', item.file);
        }
      });

      try {
        setSaving(true);

        // OJO: aquí va el ID en la URL, como lo define tu controlador
        const resp = await fetch(`${EV_API_BASE}/api/publicacion/${id}/actualizar`, {
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
          const msg = data.mensaje || data.error || 'No se pudo actualizar la publicación.';
          evNotify('error', 'Error', msg);
          console.error('[EDITAR][ERROR]', data);
          return;
        }

        evNotify('success', 'Publicación actualizada', 'Los cambios se guardaron correctamente.');

        // Cerrar modal
        const modalEl = document.getElementById('modalEditarPublicacion');
        if (modalEl) {
          const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
          modal.hide();
        }

        // Recargar tabla si existe función global
        if (window.evCargarPublicaciones) {
          window.evCargarPublicaciones();
        }

        console.log('[EDITAR][OK]', data);

      } catch (err) {
        console.error('[EDITAR][EXCEPTION]', err);
        evNotify('error', 'Error inesperado', 'Ocurrió un problema al actualizar la publicación.');
      } finally {
        setSaving(false);
      }
    }

    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (!form || form.id !== 'formEditarPublicacion') return;
      e.preventDefault();
      actualizarPublicacion(form);
    });

})();

/* ==============================
   Uploader + Previsualización — AGREGAR
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
    const tileAdd = $('#tileAgregar', modalEl);
    const btnClr  = $('#btnLimpiarImagenes', modalEl);

    const lblCntHeader  = $('#contadorImagenes', modalEl);
    const lblCntToolbar = $('#contadorImagenesToolbar', modalEl);

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
      if (form)          form.dataset.evFotosCount = String(count);
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

      // Tile "Agregar"
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

    // TileAdd
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

/* ===== Fallback irrompible: altura del modal =====
   → ahora solo aplica a Agregar y Buscar, no a Editar */
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
                <button class="ev-chip ev-chip-green" data-action="editar" data-id="${pub.codigo_publicacion}">Editar</button>
                <button class="ev-chip ev-chip-red" data-action="anular" data-id="${pub.codigo_publicacion}">Anular</button>
                <button class="ev-chip ev-chip-amber" data-action="ver" data-id="${pub.codigo_publicacion}">Publicar</button>
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
