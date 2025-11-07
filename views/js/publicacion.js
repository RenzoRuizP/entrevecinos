(function () {
  
  // Abre un modal por su ID
  function abrirModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();
  }

  // Precarga el modal de edición con los data-* del botón Editar
  function precargarEditar(btn) {
    const $ = (sel) => document.querySelector(sel);
    $('#edit_id').value = btn.dataset.id || '';
    $('#edit_titulo').value = btn.dataset.titulo || '';
    $('#edit_categoria').value = btn.dataset.categoria || 'Otros';
    $('#edit_descripcion').value = btn.dataset.descripcion || '';
    $('#edit_precio').value = btn.dataset.precio || '';
    $('#edit_estado').value = btn.dataset.estado || 'Usado';
    $('#edit_stock').value = btn.dataset.stock || 1;
  }

  // Delegación global para clicks
  document.addEventListener("click", (e) => {
    // Buscar (si usas botón sin data-bs, abre por JS)
    if (e.target.closest("#btnBuscarPublicacion")) {
      abrirModal('modalBuscarPublicacion');
      return;
    }

    // Agregar (si usas botón sin data-bs, abre por JS)
    if (e.target.closest("#btnAgregarPublicacion")) {
      abrirModal('modalAgregarPublicacion');
      return;
    }

    // Editar
    const btnEditar = e.target.closest('[data-action="editar"], .btn-editar');
    if (btnEditar) {
      precargarEditar(btnEditar);
      abrirModal('modalEditarPublicacion');
      return;
    }
  });

  // (Opcional) manejadores de envío de formularios – por ahora solo prevenimos
  document.getElementById('formBuscarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault();
    console.log('[BUSCAR] parámetros:', Object.fromEntries(new FormData(e.target)));
    // TODO: fetch(...) a tu endpoint y refrescar tabla
  });

  document.getElementById('formAgregarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    console.log('[AGREGAR] datos:', Object.fromEntries(fd));
    // TODO: fetch(...) a tu endpoint; al éxito: cerrar modal y recargar listado
  });

  document.getElementById('formEditarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault();
    console.log('[EDITAR] datos:', Object.fromEntries(new FormData(e.target)));
    // TODO: fetch(...) al endpoint de actualización; al éxito: cerrar modal y recargar listado
  });
})();


// ==============================
// Uploader imágenes: máx 3 + preview + quitar + limpiar
// ==============================
(function () {
  const input   = document.getElementById('inputImagenes');
  const preview = document.getElementById('previewImagenes');
  const btnClr  = document.getElementById('btnLimpiarImagenes');
  const lblCnt  = document.getElementById('contadorImagenes');
  const modalEl = document.getElementById('modalAgregarPublicacion');

  if (!input || !preview) return;

  const MAX_FILES = Number(input.dataset.max || 3);
  const MAX_MB = 5;

  // DataTransfer permite manipular los archivos antes de enviar
  let dt = new DataTransfer();

  function notify(msg) {
    if (window.Swal && Swal.fire) Swal.fire({icon:'info', title:'Aviso', text: msg});
    else alert(msg);
  }

  function actualizarContador() {
    lblCnt && (lblCnt.textContent = `${dt.files.length} de ${MAX_FILES}`);
  }

  function limpiarPreview() {
    preview.innerHTML = '';
  }

  function agregarMiniatura(file, idx) {
    const col = document.createElement('div');
    col.className = 'col-4';
    col.innerHTML = `
      <div class="ev-thumb">
        <img alt="preview" />
        <button type="button" class="btn btn-sm btn-danger ev-remove" data-index="${idx}" title="Quitar">
          &times;
        </button>
        <div class="ev-caption">${(file.name || '').slice(0, 22)}</div>
      </div>
    `;
    const img = col.querySelector('img');
    const reader = new FileReader();
    reader.onload = (e) => (img.src = e.target.result);
    reader.readAsDataURL(file);
    preview.appendChild(col);
  }

  function renderizarPreview() {
    limpiarPreview();
    Array.from(dt.files).forEach((f, i) => agregarMiniatura(f, i));
    actualizarContador();
  }

  function validarArchivo(file) {
    const okTipo = /^image\/(jpeg|png|webp|gif|bmp|svg\+xml)$/i.test(file.type) || file.type.startsWith('image/');
    const okPeso = file.size <= MAX_MB * 1024 * 1024;
    if (!okTipo) { notify('Solo se permiten archivos de imagen.'); return false; }
    if (!okPeso) { notify(`El archivo "${file.name}" supera ${MAX_MB} MB.`); return false; }
    return true;
  }

  input.addEventListener('change', (e) => {
    const nuevos = Array.from(e.target.files || []);
    if (!nuevos.length) return;

    for (const file of nuevos) {
      if (dt.files.length >= MAX_FILES) { notify(`Máximo ${MAX_FILES} imágenes.`); break; }
      if (!validarArchivo(file)) continue;
      dt.items.add(file);
    }

    // Reflejar en el input para que el FormData lo tome
    input.files = dt.files;
    renderizarPreview();

    // Limpia selección del sistema
    input.value = '';
  });

  // Quitar una imagen (delegación)
  preview.addEventListener('click', (e) => {
    const btn = e.target.closest('.ev-remove');
    if (!btn) return;
    const idx = Number(btn.dataset.index);
    const nuevoDT = new DataTransfer();
    Array.from(dt.files).forEach((f, i) => { if (i !== idx) nuevoDT.items.add(f); });
    dt = nuevoDT;
    input.files = dt.files;
    renderizarPreview();
  });

  // Limpiar todo
  btnClr?.addEventListener('click', () => {
    dt = new DataTransfer();
    input.value = '';
    input.files = dt.files;
    renderizarPreview();
  });

  // Reset al abrir/cerrar el modal
  modalEl?.addEventListener('shown.bs.modal', () => {
    dt = new DataTransfer();
    input.value = '';
    input.files = dt.files;
    renderizarPreview();
  });
  modalEl?.addEventListener('hidden.bs.modal', () => {
    dt = new DataTransfer();
    input.value = '';
    input.files = dt.files;
    limpiarPreview();
    actualizarContador();
  });

  // Inicial
  renderizarPreview();
})();
