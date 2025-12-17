/* publicacion.js – EV (robusto + sin rebote + no rompe menú) */

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

  function evPintarPreviewEditar(pub, fotos) {
    const st = evEnsurePreviewEditar();
    if (!st) return;

    const titulo = (pub?.titulo || '').trim() || 'Título';
    const n      = Number(pub?.precio || 0);
    const precio = isNaN(n) ? '0.00' : n.toFixed(2);
    const desc   = (pub?.descripcion || '').trim() || 'La descripción aparecerá aquí.';

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

    function renderTiles(pub) {
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
          paint(pub);
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
          paint(pub);
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

      paint(pub);
    }

    function bindEvents() {
      if (state.inited || !els.modal) return;

      els.input?.addEventListener('change', (e) => {
        const pub = els.modal?._evPubActual || {};
        agregarArchivos(e.target.files || [], pub);
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
          const pub = els.modal?._evPubActual || {};
          agregarArchivos(e.dataTransfer?.files || [], pub);
        });
        node.addEventListener('click', () => els.input?.click());
      };

      bindDrop(els.dropZone);

      if (els.tiles) {
        ['dragenter','dragover','dragleave','dragend','drop'].forEach(evt => {
          els.tiles.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); });
        });
        els.tiles.addEventListener('drop', (e) => {
          const pub = els.modal?._evPubActual || {};
          agregarArchivos(e.dataTransfer?.files || [], pub);
        });
      }

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

      window.evGetEstadoImagenesEditar = function () {
        return {
          existentes: state.fotosExistentes,
          eliminadas: state.eliminadas.slice(),
          nuevas: state.fotosNuevas
        };
      };
    }

    window.evInitUploaderEditar = function(pub, fotosBackend) {
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
      resetStateFromBackend(pub, fotosBackend);
    };
  })();

  /* ==============================
     API: Cargar edición
  ============================== */
  async function cargarPublicacionEditar(codPublicacion) {
    try {
      const resp = await fetch(`${EV_API_BASE}/api/publicacion/${codPublicacion}`);
      const data = await resp.json();

      if (!data.ok) {
        evNotify("error", "Error", data.mensaje || "Error al cargar la publicación.");
        return;
      }

      const pub   = data.data.publicacion;
      const fotos = data.data.imagenes || [];

      // Completar campos
      document.querySelector("#edit_id").value          = pub.codigo_publicacion;
      document.querySelector("#edit_titulo").value      = pub.titulo || "";
      document.querySelector("#edit_precio").value      = pub.precio || "";
      document.querySelector("#edit_estado").value      = pub.estado || "Nuevo";
      document.querySelector("#edit_descripcion").value = pub.descripcion || "";

      const comboTipo = document.getElementById("edit_comboTipo");
      const comboCat  = document.getElementById("edit_comboCategoria");
      if (comboTipo) comboTipo.dataset.valorRegistrado = pub.codigo_tipo || "";
      if (comboCat)  comboCat.dataset.valorRegistrado  = pub.codigo_categoria || "";

      if (window.evInitUploaderEditar) window.evInitUploaderEditar(pub, fotos);
      else evPintarPreviewEditar(pub, fotos);

      // Mostrar modal (requestAnimationFrame ayuda a evitar “salto”)
      const modalEl = document.getElementById("modalEditarPublicacion");
      if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        requestAnimationFrame(() => {
          modal.show();
          requestAnimationFrame(setEvVh);
        });
      }

      if (window.evInitComboTipoCategoriaEdit) {
        window.evInitComboTipoCategoriaEdit(pub.codigo_tipo, pub.codigo_categoria);
      }

    } catch (e) {
      console.error("Error cargando publicación:", e);
      evNotify("error", "Error", "No se pudo cargar los datos.");
    }
  }

  /* ==============================
     Acciones: anular / publicar
  ============================== */
  async function confirmarYAnular(id) {
    if (!id) return;

    const { isConfirmed } = await Swal.fire({
      icon: 'warning',
      title: 'Anular publicación',
      text: '¿Seguro que deseas anular esta publicación? Ya no será visible para los vecinos.',
      showCancelButton: true,
      confirmButtonText: 'Sí, anular',
      cancelButtonText: 'Cancelar',
      customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    });

    if (!isConfirmed) return;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/publicacion/${id}/anular`, { method: 'POST' });
      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        evNotify('error', 'Error', data.mensaje || data.error || 'No se pudo anular la publicación.');
        return;
      }

      evNotify('success', 'Publicación anulada', 'La publicación ha sido anulada correctamente.');
      window.evCargarPublicaciones?.();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al anular la publicación.');
    }
  }

  async function confirmarYPublicar(id) {
    if (!id) return;

    if (window.EVBilletera && typeof window.EVBilletera.publicarConCobro === 'function') {
      window.EVBilletera.publicarConCobro(id);
      return;
    }

    const { isConfirmed } = await Swal.fire({
      icon: 'question',
      title: 'Publicar en el Marketplace',
      text: '¿Deseas publicar esta publicación en el Marketplace para que la vean tus vecinos?',
      showCancelButton: true,
      confirmButtonText: 'Sí, publicar',
      cancelButtonText: 'Cancelar',
      customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    });

    if (!isConfirmed) return;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/publicacion/${id}/publicar`, { method: 'POST' });
      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        evNotify('error', 'Error', data.mensaje || data.error || 'No se pudo publicar la publicación.');
        return;
      }

      evNotify('success', 'Publicación publicada', 'Tu publicación ahora está visible en el Marketplace.');
      window.evCargarPublicaciones?.();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al publicar la publicación.');
    }
  }

  /* ==============================
     Submit EDITAR (API)
  ============================== */
  async function actualizarPublicacion(form) {
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

    if (!id) { evNotify('error','Error','No se encontró el código de la publicación.'); return; }
    if (!titulo) { evNotify('warning','Validación','Debes ingresar un título para la publicación.'); return; }

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

      const resp = await fetch(`${EV_API_BASE}/api/publicacion/${id}/actualizar`, { method:'POST', body: fd });
      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error', 'Sesión expirada', 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        evNotify('error', 'Error', data.mensaje || data.error || 'No se pudo actualizar la publicación.');
        return;
      }

      evNotify('success', 'Publicación actualizada', 'Los cambios se guardaron correctamente.');

      const modalEl = document.getElementById('modalEditarPublicacion');
      if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
      }

      window.evCargarPublicaciones?.();

    } catch (err) {
      console.error(err);
      evNotify('error', 'Error inesperado', 'Ocurrió un problema al actualizar la publicación.');
    } finally {
      setSaving(false);
    }
  }

  /* ==============================
     Listado tabla
  ============================== */
  async function cargarPublicaciones() {
    const table = document.getElementById('tablaPublicaciones');
    const tbody = table?.querySelector('tbody');
    if (!table || !tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Cargando publicaciones…</td></tr>`;

    try {
      const resp = await fetch(`${EV_API_BASE}/api/publicacion/listar`, { method: 'GET' });
      const data = await resp.json().catch(() => ({}));

      if (resp.status === 401) {
        evNotify('error','Sesión expirada','Tu sesión ha expirado. Vuelve a iniciar sesión.');
        setTimeout(() => { window.location.href = `${EV_API_BASE}/`; }, 1500);
        return;
      }

      if (!resp.ok || !data.ok) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${data.mensaje || data.error || 'No se pudo obtener el listado.'}</td></tr>`;
        return;
      }

      const items = Array.isArray(data.data) ? data.data : [];
      if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Aún no tienes publicaciones registradas.</td></tr>`;
        return;
      }

      tbody.innerHTML = items.map((pub) => {
        const cod     = String(pub.codigo_publicacion ?? '').padStart(6, '0');
        const titulo  = (pub.titulo || '').substring(0, 80).replace(/"/g,'&quot;');
        const precio  = Number(pub.precio || 0).toFixed(2);
        const estado  = (pub.estado || '').toUpperCase();
        const fecha   = pub.created_at || '';
        const visible = Number(pub.visible ?? 1);

        const publicado = visible === 2;

        let badge = 'ev-badge ev-badge--noaplica';
        if (estado === 'NUEVO') badge = 'ev-badge ev-badge--nuevo';
        else if (estado === 'USADO') badge = 'ev-badge ev-badge--usado';

        const textoPublicar = publicado ? 'Publicado' : 'Publicar';
        const extraAttrsPub = publicado ? ' disabled data-status="publicado"' : '';

        return `
          <tr>
            <td><span class="ev-code">${cod}</span></td>
            <td class="td-trunc" title="${titulo}">${titulo || '-'}</td>
            <td>S/ ${precio}</td>
            <td><span class="${badge}">${estado || '-'}</span></td>
            <td>${fecha}</td>
            <td class="text-center">
              <div class="ev-actions">
                <button type="button" class="ev-chip ev-chip-green" data-action="editar" data-id="${pub.codigo_publicacion}">Editar</button>
                <button type="button" class="ev-chip ev-chip-red" data-action="anular" data-id="${pub.codigo_publicacion}">Anular</button>
                <button type="button" class="ev-chip ev-chip-amber" data-action="publicar" data-id="${pub.codigo_publicacion}" ${extraAttrsPub}>${textoPublicar}</button>
              </div>
            </td>
          </tr>
        `;
      }).join('');

    } catch (err) {
      console.error(err);
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Ocurrió un error al cargar las publicaciones.</td></tr>`;
    }
  }

  window.evCargarPublicaciones = cargarPublicaciones;

  /* ==============================
     INIT seguro para vista inyectada
  ============================== */
  function isPublicacionesViewPresent() {
    return !!document.getElementById('tablaPublicaciones');
  }

  function bindOnceGlobalEvents() {
    if (document.body.dataset.evPubsBound === '1') return;
    document.body.dataset.evPubsBound = '1';

    // Clicks (delegado)
    document.addEventListener('click', (e) => {
      // Botones header
      if (e.target.closest('#btnBuscarPublicacion')) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBuscarPublicacion')).show();
        return;
      }
      if (e.target.closest('#btnAgregarPublicacion')) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAgregarPublicacion')).show();
        return;
      }

      // Acciones tabla
      const btnEditar = e.target.closest('[data-action="editar"][data-id]');
      if (btnEditar) { cargarPublicacionEditar(btnEditar.getAttribute('data-id')); return; }

      const btnAnular = e.target.closest('[data-action="anular"][data-id]');
      if (btnAnular) { confirmarYAnular(btnAnular.getAttribute('data-id')); return; }

      const btnPublicar = e.target.closest('[data-action="publicar"][data-id]');
      if (btnPublicar && !btnPublicar.disabled) { confirmarYPublicar(btnPublicar.getAttribute('data-id')); return; }
    });

    // Submit editar
    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (form && form.id === 'formEditarPublicacion') {
        e.preventDefault();
        actualizarPublicacion(form);
      }
    });

    // Buscar (placeholder)
    document.getElementById('formBuscarPublicacion')?.addEventListener('submit', (e) => {
      e.preventDefault();
      console.log('[BUSCAR]', Object.fromEntries(new FormData(e.target)));
    });
  }

  function initIfNeeded() {
    bindOnceGlobalEvents();
    if (isPublicacionesViewPresent() && !document.getElementById('tablaPublicaciones').dataset.evLoaded) {
      document.getElementById('tablaPublicaciones').dataset.evLoaded = '1';
      cargarPublicaciones();
    }
  }

  // DOM ready
  document.addEventListener('DOMContentLoaded', initIfNeeded);

  // Observa inyección de vista (menú dinámico)
  const target = document.getElementById('contenido-principal') || document.body;
  const obs = new MutationObserver(() => {
    // si se inyecta la tabla, inicializa
    initIfNeeded();
  });
  obs.observe(target, { childList: true, subtree: true });

})();
