
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
    // Buscar
    if (e.target.closest("#btnBuscarPublicacion")) {
      abrirModal('modalBuscarPublicacion');
      return;
    }

    // Agregar
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
    console.log('[AGREGAR] datos:', Object.fromEntries(new FormData(e.target)));
    // TODO: fetch(...) a tu endpoint; al éxito: cerrar modal y recargar listado
  });

  document.getElementById('formEditarPublicacion')?.addEventListener('submit', (e) => {
    e.preventDefault();
    console.log('[EDITAR] datos:', Object.fromEntries(new FormData(e.target)));
    // TODO: fetch(...) al endpoint de actualización; al éxito: cerrar modal y recargar listado
  });
})();
