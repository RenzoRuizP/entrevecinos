// views/js/menuArriba.js
// ============================================================
// Entre Vecinos - Menú superior premium
// Sidebar responsive + perfil móvil enfocado + foto de perfil controlada
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  if (window.__EV_MENU_ARRIBA_INIT__ === true) return;
  window.__EV_MENU_ARRIBA_INIT__ = true;

  const btnToggleSidebar = document.getElementById('btnToggleSidebar');
  const btnToggleIcon = btnToggleSidebar?.querySelector('i') || null;

  const sidebar =
    document.getElementById('sidebar') ||
    document.querySelector('.app-sidebar') ||
    document.querySelector('.main-sidebar');

  const sidebarBackdrop = document.getElementById('sidebar-backdrop');
  const btnCerrarSesion = document.getElementById('btnCerrarSesion');
  const btnPerfil = document.getElementById('btnPerfil');
  const userDropdown = document.getElementById('userDropdown');
  const dropdownMenu = userDropdown?.nextElementSibling || null;

  const baseUrl = String(window.EV?.baseUrl ?? window.BASE_URL ?? window.EV_BASE_URL ?? '').replace(/\/+$/, '');
  const mediaMobile = window.matchMedia('(max-width: 991.98px)');

  const AVATAR_ENDPOINT = `${baseUrl}/api/usuario/foto-perfil`;
  const AVATAR_MAX_BYTES = 2 * 1024 * 1024;
  const AVATAR_ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

  let avatarFile = null;
  let avatarPreviewUrl = '';
  let avatarModalInstance = null;

  function esMobile() {
    return mediaMobile.matches;
  }

  function dropdownUsuarioInstance() {
    if (!userDropdown || !window.bootstrap?.Dropdown) return null;
    return window.bootstrap.Dropdown.getOrCreateInstance(userDropdown);
  }

  function modalUsuarioInstance() {
    const modal = crearModalAvatar();

    if (!window.bootstrap?.Modal) {
      return null;
    }

    if (!avatarModalInstance) {
      avatarModalInstance = window.bootstrap.Modal.getOrCreateInstance(modal, {
        backdrop: 'static',
        keyboard: true
      });
    }

    return avatarModalInstance;
  }

  function obtenerBackdropPerfil() {
    let elemento = document.getElementById('evUserMenuBackdrop');

    if (!elemento) {
      elemento = document.createElement('div');
      elemento.id = 'evUserMenuBackdrop';
      elemento.className = 'ev-user-menu-backdrop';
      elemento.setAttribute('aria-hidden', 'true');
      document.body.appendChild(elemento);
    }

    return elemento;
  }

  const userMenuBackdrop = obtenerBackdropPerfil();

  function perfilEstaAbierto() {
    return !!(dropdownMenu && dropdownMenu.classList.contains('show'));
  }

  function mostrarBackdropPerfil() {
    if (!esMobile() || !userMenuBackdrop) return;

    userMenuBackdrop.classList.add('is-visible');
    userMenuBackdrop.setAttribute('aria-hidden', 'false');
    document.body.classList.add('ev-user-menu-open');
  }

  function ocultarBackdropPerfil() {
    if (!userMenuBackdrop) return;

    userMenuBackdrop.classList.remove('is-visible');
    userMenuBackdrop.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('ev-user-menu-open');
  }

  function cerrarPerfil() {
    if (!perfilEstaAbierto()) {
      ocultarBackdropPerfil();
      return;
    }

    dropdownUsuarioInstance()?.hide();
    ocultarBackdropPerfil();
  }

  function sidebarEstaAbierto() {
    return !!(
      sidebar &&
      (
        sidebar.classList.contains('active') ||
        sidebar.classList.contains('open') ||
        document.body.classList.contains('ev-sidebar-open')
      )
    );
  }

  function actualizarBotonSidebar(abierto) {
    if (!btnToggleSidebar) return;

    btnToggleSidebar.classList.toggle('is-open', abierto);
    btnToggleSidebar.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    btnToggleSidebar.setAttribute('aria-controls', 'sidebar');
    btnToggleSidebar.setAttribute('aria-label', abierto ? 'Cerrar menú lateral' : 'Mostrar menú lateral');

    if (btnToggleIcon) {
      btnToggleIcon.className = abierto
        ? 'bi bi-x-lg text-white'
        : 'bi bi-list text-white';
    }
  }

  function sincronizarAccesibilidadSidebar(abierto) {
    actualizarBotonSidebar(abierto);

    if (!sidebar) return;

    if (esMobile()) {
      sidebar.setAttribute('aria-hidden', abierto ? 'false' : 'true');
      return;
    }

    sidebar.removeAttribute('aria-hidden');
  }

  function abrirSidebar() {
    if (!sidebar) return;

    cerrarPerfil();

    sidebar.classList.add('active', 'open');
    sidebarBackdrop?.classList.add('active', 'show');
    document.body.classList.add('ev-sidebar-open');

    sincronizarAccesibilidadSidebar(true);
  }

  function cerrarSidebar() {
    if (!sidebar) return;

    sidebar.classList.remove('active', 'open');
    sidebarBackdrop?.classList.remove('active', 'show');
    document.body.classList.remove('ev-sidebar-open');

    sincronizarAccesibilidadSidebar(false);
  }

  function alternarSidebar() {
    if (sidebarEstaAbierto()) {
      cerrarSidebar();
      return;
    }

    abrirSidebar();
  }

  if (btnToggleSidebar && sidebar) {
    sincronizarAccesibilidadSidebar(false);

    btnToggleSidebar.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      alternarSidebar();
    });
  }

  sidebarBackdrop?.addEventListener('click', cerrarSidebar);

  userMenuBackdrop?.addEventListener('click', () => {
    cerrarPerfil();
  });

  if (sidebar) {
    sidebar.addEventListener('click', (event) => {
      const link = event.target.closest('a.submenu-link');
      if (!link) return;

      if (esMobile()) {
        cerrarSidebar();
      }
    });
  }

  if (userDropdown && dropdownMenu) {
    userDropdown.addEventListener('show.bs.dropdown', () => {
      if (esMobile()) {
        cerrarSidebar();
      }
    });

    userDropdown.addEventListener('shown.bs.dropdown', () => {
      mostrarBackdropPerfil();
    });

    userDropdown.addEventListener('hide.bs.dropdown', () => {
      ocultarBackdropPerfil();
    });

    userDropdown.addEventListener('hidden.bs.dropdown', () => {
      ocultarBackdropPerfil();
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    if (perfilEstaAbierto()) {
      cerrarPerfil();
      return;
    }

    if (sidebarEstaAbierto()) {
      cerrarSidebar();
    }
  });

  mediaMobile.addEventListener?.('change', (event) => {
    cerrarSidebar();

    if (!event.matches) {
      ocultarBackdropPerfil();
    } else if (perfilEstaAbierto()) {
      mostrarBackdropPerfil();
    }

    sincronizarAccesibilidadSidebar(false);
    ajustarDropdownUsuario();
  });

  if (btnPerfil) {
    btnPerfil.addEventListener('click', (event) => {
      event.preventDefault();

      dropdownUsuarioInstance()?.hide();
      ocultarBackdropPerfil();

      const linkPerfil =
        document.querySelector('.submenu-link[data-vista="/mi-perfil"]') ||
        document.querySelector(`.submenu-link[href="${baseUrl}/mi-perfil"]`) ||
        document.querySelector('.submenu-link[href$="/mi-perfil"]');

      if (linkPerfil) {
        linkPerfil.click();
      } else {
        window.location.href = `${baseUrl}/mi-perfil`;
      }
    });
  }

  async function confirmarCierreSesion() {
    if (!window.Swal?.fire) {
      return window.confirm('¿Deseas cerrar sesión?');
    }

    const result = await Swal.fire({
      title: '¿Deseas cerrar sesión?',
      text: 'Tu disponibilidad para recibir pedidos se apagará automáticamente.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Aceptar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#BF3604',
      cancelButtonColor: '#6c757d',
      allowOutsideClick: false,
      allowEscapeKey: true
    });

    return !!result.isConfirmed;
  }

  function detenerProcesosEVAntesDeSalir() {
    try {
      if (window.EVPollingControl && typeof window.EVPollingControl.detenerPedidosVendedor === 'function') {
        window.EVPollingControl.detenerPedidosVendedor();
      }
    } catch (_) {}

    try {
      if (window.EVMarketplace && typeof window.EVMarketplace.stopPollingDisponibilidad === 'function') {
        window.EVMarketplace.stopPollingDisponibilidad();
      }
    } catch (_) {}

    try {
      if (window.EVRecibirPedidos && typeof window.EVRecibirPedidos.detenerPolling === 'function') {
        window.EVRecibirPedidos.detenerPolling();
      }
    } catch (_) {}
  }

  async function mostrarCierreCorrecto() {
    if (!window.Swal?.fire) return;

    await Swal.fire({
      icon: 'success',
      title: 'Sesión cerrada',
      text: 'Has cerrado sesión correctamente.',
      timer: 1400,
      showConfirmButton: false,
      allowOutsideClick: false,
      allowEscapeKey: false
    });
  }

  async function mostrarErrorCierre(mensaje) {
    if (!window.Swal?.fire) {
      alert(mensaje || 'No se pudo cerrar sesión.');
      return;
    }

    await Swal.fire({
      icon: 'error',
      title: 'No se pudo cerrar sesión',
      text: mensaje || 'Ocurrió un problema al cerrar tu sesión.',
      confirmButtonText: 'Aceptar',
      confirmButtonColor: '#DC2626'
    });
  }

  if (btnCerrarSesion) {
    btnCerrarSesion.addEventListener('click', async (event) => {
      event.preventDefault();

      const confirmado = await confirmarCierreSesion();
      if (!confirmado) return;

      btnCerrarSesion.classList.add('disabled');
      btnCerrarSesion.setAttribute('aria-disabled', 'true');

      detenerProcesosEVAntesDeSalir();

      try {
        const response = await fetch(`${baseUrl}/logout`, {
          method: 'POST',
          credentials: 'include',
          cache: 'no-store',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await response.json().catch(() => ({}));

        const ok = response.ok && (
          data.ok === true ||
          data.success === true ||
          String(data.status || '').toLowerCase() === 'success'
        );

        if (!ok) {
          await mostrarErrorCierre(data.message || data.mensaje || 'No se pudo cerrar sesión.');
          btnCerrarSesion.classList.remove('disabled');
          btnCerrarSesion.removeAttribute('aria-disabled');
          return;
        }

        await mostrarCierreCorrecto();
        window.location.replace(data.redirect || `${baseUrl}/login`);
      } catch (_) {
        await mostrarErrorCierre('No se pudo conectar con el servidor.');
        btnCerrarSesion.classList.remove('disabled');
        btnCerrarSesion.removeAttribute('aria-disabled');
      }
    });
  }

  document.addEventListener('click', (event) => {
    if (!userDropdown || !dropdownMenu) return;

    const clickDentro =
      userDropdown.contains(event.target) ||
      dropdownMenu.contains(event.target);

    if (!clickDentro && perfilEstaAbierto() && !userMenuBackdrop?.contains(event.target)) {
      dropdownUsuarioInstance()?.hide();
    }
  });

  function ajustarDropdownUsuario() {
    if (!dropdownMenu) return;

    if (esMobile()) {
      dropdownMenu.classList.remove('dropdown-menu-end');
      dropdownMenu.style.left = '50%';
      dropdownMenu.style.transform = 'translateX(-50%)';
      dropdownMenu.style.minWidth = 'min(92vw, 340px)';
      return;
    }

    dropdownMenu.classList.add('dropdown-menu-end');
    dropdownMenu.style.left = '';
    dropdownMenu.style.transform = '';
    dropdownMenu.style.minWidth = '230px';
  }

  // ============================================================
  // FOTO DE PERFIL
  // Regla UX:
  // Click en avatar -> abre modal.
  // Click en "Seleccionar nueva foto" -> abre archivos.
  // Click en "Guardar" -> recién sube y actualiza.
  // ============================================================

  function avatarActualUrl() {
    const img =
      document.querySelector('#userDropdown img') ||
      document.querySelector('.user-menu img');

    return img?.getAttribute('src') || `${baseUrl}/views/fotos/00000000.png`;
  }

  function nombreArchivoSeguro(file) {
    if (!file) return '';
    return String(file.name || 'imagen seleccionada').replace(/\s+/g, ' ').trim();
  }

  function escapeAvatarHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatearPeso(bytes) {
    const n = Number(bytes || 0);

    if (n < 1024) return `${n} B`;
    if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;

    return `${(n / 1024 / 1024).toFixed(2)} MB`;
  }

  function validarAvatar(file) {
    if (!file) {
      return 'Selecciona una imagen.';
    }

    if (!AVATAR_ALLOWED.includes(String(file.type || '').toLowerCase())) {
      return 'Solo se permiten imágenes JPG, PNG o WEBP.';
    }

    if (file.size <= 0 || file.size > AVATAR_MAX_BYTES) {
      return 'La imagen debe pesar como máximo 2 MB.';
    }

    return '';
  }

  function extraerFotoUrl(data) {
    return String(
      data?.foto_url ||
      data?.foto_perfil_url ||
      data?.avatar_url ||
      data?.url ||
      data?.data?.foto_url ||
      data?.data?.foto_perfil_url ||
      data?.data?.avatar_url ||
      data?.data?.url ||
      data?.data?.usuario?.foto_url ||
      data?.data?.usuario?.foto_perfil_url ||
      ''
    ).trim();
  }

  function actualizarAvataresEnVista(url) {
    const finalUrl = String(url || '').trim();
    if (!finalUrl) return;

    const srcConCache = finalUrl.includes('?')
      ? `${finalUrl}&v=${Date.now()}`
      : `${finalUrl}?v=${Date.now()}`;

    document
      .querySelectorAll('.user-menu img, #userDropdown img, img[data-ev-avatar-img], .ev-avatar-img')
      .forEach((img) => {
        img.src = srcConCache;
      });

    const actual = document.getElementById('evAvatarActual');
    const preview = document.getElementById('evAvatarPreview');

    if (actual) actual.src = srcConCache;
    if (preview) preview.src = srcConCache;

    window.EV_FOTO_USUARIO = srcConCache;
  }

  function inyectarEstilosAvatarModal() {
    if (document.getElementById('evAvatarModalStyles')) return;

    const style = document.createElement('style');
    style.id = 'evAvatarModalStyles';
    style.textContent = `
      .ev-avatar-modal .modal-dialog{
        max-width:min(690px, calc(100vw - 22px));
      }

      .ev-avatar-modal-content{
        border:0;
        border-radius:26px;
        overflow:hidden;
        background:#fff;
        box-shadow:0 32px 80px rgba(15,23,42,.24), 0 10px 26px rgba(15,23,42,.12);
      }

      .ev-avatar-modal-head{
        position:relative;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:14px;
        padding:18px 20px;
        color:#fff;
        background:
          radial-gradient(circle at 86% 16%, rgba(255,255,255,.18), transparent 34%),
          linear-gradient(135deg,#0F592F,#0E7A43,#16A34A);
      }

      .ev-avatar-modal-title{
        min-width:0;
        display:flex;
        align-items:center;
        gap:12px;
      }

      .ev-avatar-modal-title span{
        width:44px;
        height:44px;
        flex:0 0 auto;
        display:grid;
        place-items:center;
        border-radius:16px;
        color:#0F592F;
        background:rgba(255,255,255,.94);
        box-shadow:0 10px 22px rgba(15,23,42,.13);
      }

      .ev-avatar-modal-title small{
        display:block;
        margin-bottom:3px;
        color:rgba(255,255,255,.82);
        font-size:.72rem;
        font-weight:950;
        letter-spacing:.08em;
        text-transform:uppercase;
      }

      .ev-avatar-modal-title h2{
        margin:0;
        color:#fff;
        font-size:1.22rem;
        line-height:1.12;
        font-weight:950;
        letter-spacing:-.025em;
      }

      .ev-avatar-modal-close{
        width:40px;
        height:40px;
        flex:0 0 auto;
        display:grid;
        place-items:center;
        border-radius:14px;
        border:1px solid rgba(255,255,255,.22);
        color:#fff;
        background:rgba(255,255,255,.11);
        transition:background .16s ease, transform .16s ease;
      }

      .ev-avatar-modal-close:hover{
        background:rgba(255,255,255,.18);
        transform:translateY(-1px);
      }

      .ev-avatar-modal-body{
        padding:22px;
        background:linear-gradient(180deg,#FFFFFF 0%,#F8FAFC 100%);
      }

      .ev-avatar-workspace{
        display:grid;
        grid-template-columns:minmax(210px,.82fr) minmax(0,1.18fr);
        gap:18px;
        align-items:stretch;
      }

      .ev-avatar-preview-shell,
      .ev-avatar-upload-panel{
        border:1px solid #E5E7EB;
        border-radius:20px;
        background:#fff;
        box-shadow:0 12px 30px rgba(15,23,42,.055);
      }

      .ev-avatar-preview-shell{
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        text-align:center;
        gap:12px;
        padding:22px 16px;
      }

      .ev-avatar-upload-panel{
        display:flex;
        flex-direction:column;
        justify-content:center;
        padding:20px;
      }

      .ev-avatar-upload-eyebrow{
        display:block;
        margin-bottom:5px;
        color:#EA7C12;
        font-size:.68rem;
        font-weight:950;
        letter-spacing:.08em;
        text-transform:uppercase;
      }

      .ev-avatar-upload-panel h3{
        margin:0;
        color:#0F592F;
        font-size:1.08rem;
        line-height:1.2;
        font-weight:950;
      }

      .ev-avatar-upload-copy{
        margin:7px 0 0;
        color:#64748B;
        font-size:.82rem;
        line-height:1.5;
        font-weight:700;
      }

      .ev-avatar-preview-ring{
        width:146px;
        height:146px;
        border-radius:999px;
        padding:5px;
        background:
          linear-gradient(#fff,#fff) padding-box,
          linear-gradient(135deg,#0F592F,#16A34A,#EA7C12) border-box;
        border:2px solid transparent;
        box-shadow:0 16px 36px rgba(15,23,42,.13);
      }

      .ev-avatar-preview-ring img{
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
        border-radius:999px;
        background:#F3F4F6;
      }

      .ev-avatar-help{
        max-width:230px;
        margin:0;
        color:#64748B;
        font-size:.80rem;
        line-height:1.45;
        font-weight:700;
      }

      .ev-avatar-file-card{
        width:100%;
        margin-top:16px;
        display:grid;
        grid-template-columns:38px minmax(0,1fr);
        gap:10px;
        align-items:center;
        padding:13px;
        border:1px dashed rgba(15,89,47,.28);
        border-radius:17px;
        background:linear-gradient(135deg,#F0FDF4,#FFFFFF);
      }

      .ev-avatar-file-card-icon{
        width:38px;
        height:38px;
        display:grid;
        place-items:center;
        border-radius:13px;
        color:#0F592F;
        background:#fff;
        border:1px solid #D8F1E1;
      }

      .ev-avatar-file-card-copy{min-width:0;max-width:100%;overflow:hidden}

      .ev-avatar-file-card strong{
        display:block;
        max-width:100%;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
        color:#0F592F;
        font-size:.9rem;
        font-weight:950;
      }

      .ev-avatar-file-card small{
        display:block;
        margin-top:3px;
        color:#6B7280;
        font-size:.78rem;
        font-weight:750;
      }

      .ev-avatar-actions{
        display:flex;
        flex-wrap:wrap;
        justify-content:flex-end;
        gap:10px;
        margin-top:18px;
      }

      .ev-avatar-btn{
        min-height:40px;
        border-radius:999px;
        padding:9px 16px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        font-size:.86rem;
        font-weight:950;
        transition:transform .16s ease, box-shadow .16s ease, background .16s ease, color .16s ease, border-color .16s ease;
      }

      .ev-avatar-btn-select{
        color:#0F592F;
        background:#fff;
        border:1px solid rgba(15,89,47,.20);
        box-shadow:0 10px 22px rgba(15,23,42,.055);
      }

      .ev-avatar-btn-select:hover{
        color:#0F592F;
        background:#ECFDF3;
        border-color:rgba(22,163,74,.32);
        transform:translateY(-1px);
      }

      .ev-avatar-btn-save{
        color:#fff;
        border:0;
        background:linear-gradient(135deg,#EA7C12,#F59E0B);
        box-shadow:0 14px 28px rgba(234,124,18,.28);
      }

      .ev-avatar-btn-save:hover{
        color:#fff;
        transform:translateY(-1px);
        box-shadow:0 18px 34px rgba(234,124,18,.34);
      }

      .ev-avatar-btn-save:disabled{
        opacity:.55;
        cursor:not-allowed;
        transform:none;
        box-shadow:none;
      }

      .ev-avatar-modal-footer{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        padding:14px 20px;
        border-top:1px solid #EEF2F7;
        background:#fff;
      }

      .ev-avatar-modal-footer span{
        color:#6B7280;
        font-size:.78rem;
        font-weight:800;
      }

      .ev-avatar-btn-cancel{
        min-height:40px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:14px;
        padding:8px 16px;
        color:#475569;
        background:#fff;
        border:1px solid #CBD5E1;
        box-shadow:0 8px 18px rgba(15,23,42,.06);
        font-size:.84rem;
        font-weight:950;
        transition:transform .16s ease, box-shadow .16s ease, background .16s ease, border-color .16s ease, color .16s ease;
      }

      .ev-avatar-btn-cancel:hover,
      .ev-avatar-btn-cancel:focus-visible{
        color:#111827;
        background:#F8FAFC;
        border-color:#94A3B8;
        box-shadow:0 12px 24px rgba(15,23,42,.10);
        transform:translateY(-1px);
      }

      .ev-avatar-btn-cancel:active{
        transform:translateY(0) scale(.985);
        box-shadow:0 6px 14px rgba(15,23,42,.08);
      }

      .user-menu img,
      #userDropdown img{
        cursor:pointer;
      }

      @media (max-width:767.98px){
        .ev-avatar-workspace{grid-template-columns:1fr}
        .ev-avatar-preview-shell{padding:18px 14px}
        .ev-avatar-preview-ring{width:124px;height:124px}
        .ev-avatar-help{max-width:320px}
      }

      @media (max-width:575.98px){
        .ev-avatar-modal-body{
          padding:16px;
        }

        .ev-avatar-modal-footer{
          align-items:stretch;
          flex-direction:column;
        }

        .ev-avatar-btn-cancel{
          width:100%;
        }

        .ev-avatar-actions{
          flex-direction:column;
        }

        .ev-avatar-btn{
          width:100%;
        }

        .ev-avatar-file-card strong{
          font-size:.84rem;
        }
      }
    `;

    document.head.appendChild(style);
  }

  function crearModalAvatar() {
    let modal = document.getElementById('evAvatarPerfilModal');

    if (modal) return modal;

    inyectarEstilosAvatarModal();

    modal = document.createElement('div');
    modal.className = 'modal fade ev-avatar-modal';
    modal.id = 'evAvatarPerfilModal';
    modal.tabIndex = -1;
    modal.setAttribute('aria-labelledby', 'evAvatarPerfilTitle');
    modal.setAttribute('aria-hidden', 'true');

    modal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <article class="modal-content ev-avatar-modal-content">
          <header class="ev-avatar-modal-head">
            <div class="ev-avatar-modal-title">
              <span aria-hidden="true">
                <i class="bi bi-camera"></i>
              </span>
              <div>
                <small>Foto de perfil</small>
                <h2 id="evAvatarPerfilTitle">Actualizar foto</h2>
              </div>
            </div>

            <button type="button" class="ev-avatar-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
              <i class="bi bi-x-lg"></i>
            </button>
          </header>

          <div class="modal-body ev-avatar-modal-body">
            <div class="ev-avatar-workspace">
              <section class="ev-avatar-preview-shell" aria-label="Vista previa de la foto">
                <div class="ev-avatar-preview-ring">
                  <img id="evAvatarPreview" src="${avatarActualUrl()}" alt="Vista previa de foto de perfil">
                </div>

                <p class="ev-avatar-help">
                  Procura que tu rostro esté centrado y se vea con claridad.
                </p>
              </section>

              <section class="ev-avatar-upload-panel">
                <span class="ev-avatar-upload-eyebrow">Tu perfil EV</span>
                <h3>Elige una foto clara y actual</h3>
                <p class="ev-avatar-upload-copy">
                  Selecciona una imagen, revisa la vista previa y guarda el cambio cuando estés conforme.
                </p>

                <div class="ev-avatar-file-card" id="evAvatarFileInfo">
                  <span class="ev-avatar-file-card-icon" aria-hidden="true"><i class="bi bi-image"></i></span>
                  <span class="ev-avatar-file-card-copy">
                    <strong>Ninguna imagen seleccionada</strong>
                    <small>JPG, PNG o WEBP · Máximo 2 MB.</small>
                  </span>
                </div>

                <input
                  type="file"
                  id="evAvatarInput"
                  accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                  hidden
                >

                <div class="ev-avatar-actions">
                  <button type="button" class="ev-avatar-btn ev-avatar-btn-select" id="evAvatarBtnSelect">
                    Seleccionar foto
                  </button>

                  <button type="button" class="ev-avatar-btn ev-avatar-btn-save" id="evAvatarBtnSave" disabled>
                    Guardar
                  </button>
                </div>
              </section>
            </div>
          </div>

          <footer class="ev-avatar-modal-footer">
            <span>
              <i class="bi bi-shield-check"></i>
              Tu imagen será visible en tu cuenta EV.
            </span>

            <button type="button" class="ev-avatar-btn-cancel" data-bs-dismiss="modal">
              Cancelar
            </button>
          </footer>
        </article>
      </div>
    `;

    document.body.appendChild(modal);

    const input = modal.querySelector('#evAvatarInput');
    const btnSelect = modal.querySelector('#evAvatarBtnSelect');
    const btnSave = modal.querySelector('#evAvatarBtnSave');

    btnSelect?.addEventListener('click', () => {
      input?.click();
    });

    input?.addEventListener('change', () => {
      const file = input.files && input.files[0] ? input.files[0] : null;
      prepararPreviewAvatar(file);
    });

    btnSave?.addEventListener('click', subirAvatar);

    modal.addEventListener('hidden.bs.modal', () => {
      resetAvatarModal();
    });

    return modal;
  }

  function resetAvatarModal() {
    const input = document.getElementById('evAvatarInput');
    const preview = document.getElementById('evAvatarPreview');
    const info = document.getElementById('evAvatarFileInfo');
    const btnSave = document.getElementById('evAvatarBtnSave');

    if (avatarPreviewUrl) {
      URL.revokeObjectURL(avatarPreviewUrl);
    }

    avatarFile = null;
    avatarPreviewUrl = '';

    if (input) input.value = '';
    if (preview) preview.src = avatarActualUrl();

    if (info) {
      info.innerHTML = `
        <span class="ev-avatar-file-card-icon" aria-hidden="true"><i class="bi bi-image"></i></span>
        <span class="ev-avatar-file-card-copy">
          <strong>Ninguna imagen seleccionada</strong>
          <small>JPG, PNG o WEBP · Máximo 2 MB.</small>
        </span>
      `;
    }

    if (btnSave) {
      btnSave.disabled = true;
      btnSave.classList.remove('is-loading');
      btnSave.textContent = 'Guardar';
    }
  }

  async function notificarAvatar(icon, title, text) {
    if (icon === 'success' && window.EVSwal?.success) {
      await window.EVSwal.success(title, text, {
        subtitle: 'Cambio guardado',
        confirmButtonText: 'Aceptar',
        showConfirmButton: true,
        showCancelButton: false,
        showDenyButton: false,
        showCloseButton: false,
        customClass: { closeButton: 'ev-swal-close' }
      });
      return;
    }

    if (window.Swal?.fire) {
      await Swal.fire({
        icon,
        title,
        text,
        showCancelButton: false,
        showDenyButton: false,
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: icon === 'error' ? '#DC2626' : '#EA7C12',
        allowOutsideClick: false
      });
      return;
    }

    alert(`${title}\n\n${text || ''}`);
  }

  function prepararPreviewAvatar(file) {
    const preview = document.getElementById('evAvatarPreview');
    const info = document.getElementById('evAvatarFileInfo');
    const btnSave = document.getElementById('evAvatarBtnSave');

    const error = validarAvatar(file);

    if (error) {
      avatarFile = null;

      if (avatarPreviewUrl) {
        URL.revokeObjectURL(avatarPreviewUrl);
        avatarPreviewUrl = '';
      }

      if (preview) preview.src = avatarActualUrl();

      if (info) {
        info.innerHTML = `
          <span class="ev-avatar-file-card-icon" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></span>
          <span class="ev-avatar-file-card-copy">
            <strong>No se pudo usar esta imagen</strong>
            <small>${error}</small>
          </span>
        `;
      }

      if (btnSave) btnSave.disabled = true;

      notificarAvatar('warning', 'Imagen no válida', error);
      return;
    }

    if (avatarPreviewUrl) {
      URL.revokeObjectURL(avatarPreviewUrl);
    }

    avatarFile = file;
    avatarPreviewUrl = URL.createObjectURL(file);

    if (preview) preview.src = avatarPreviewUrl;

    if (info) {
      info.innerHTML = `
        <span class="ev-avatar-file-card-icon" aria-hidden="true"><i class="bi bi-check2"></i></span>
        <span class="ev-avatar-file-card-copy">
          <strong title="${escapeAvatarHtml(nombreArchivoSeguro(file))}">${escapeAvatarHtml(nombreArchivoSeguro(file))}</strong>
          <small>${formatearPeso(file.size)} · Lista para guardar.</small>
        </span>
      `;
    }

    if (btnSave) btnSave.disabled = false;
  }

  async function manejarAuthAvatar(response, data) {
    if (response.status === 401 || response.status === 403) {
      await notificarAvatar(
        'info',
        'Sesión finalizada',
        data?.mensaje || data?.message || 'Tu sesión ya no está activa. Vuelve a iniciar sesión.'
      );

      window.location.href = data?.redirect || `${baseUrl}/login`;
      return true;
    }

    if (response.status === 409 && String(data?.error || '').trim() === 'CUENTA_OBSERVADA') {
      await notificarAvatar(
        'warning',
        'Cuenta observada',
        data?.mensaje || 'Debes revisar el estado de tu cuenta.'
      );

      window.location.href = data?.redirect || `${baseUrl}/cuenta-observada`;
      return true;
    }

    return false;
  }

  async function subirAvatar() {
    if (!avatarFile) {
      await notificarAvatar('warning', 'Selecciona una foto', 'Primero selecciona una imagen para actualizar tu perfil.');
      return;
    }

    const error = validarAvatar(avatarFile);

    if (error) {
      await notificarAvatar('warning', 'Imagen no válida', error);
      return;
    }

    const btnSave = document.getElementById('evAvatarBtnSave');
    const btnSelect = document.getElementById('evAvatarBtnSelect');

    if (btnSave) {
      btnSave.disabled = true;
      btnSave.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Guardando...';
    }

    if (btnSelect) {
      btnSelect.disabled = true;
    }

    try {
      const fd = new FormData();
      fd.append('foto_perfil', avatarFile);

      const response = await fetch(AVATAR_ENDPOINT, {
        method: 'POST',
        body: fd,
        credentials: 'include',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const data = await response.json().catch(() => ({}));

      if (await manejarAuthAvatar(response, data)) {
        return;
      }

      if (!response.ok || !(data.ok === true || data.success === true)) {
        throw new Error(data.mensaje || data.message || 'No se pudo actualizar la foto de perfil.');
      }

      const fotoUrl = extraerFotoUrl(data);

      if (fotoUrl) {
        actualizarAvataresEnVista(fotoUrl);
      } else if (avatarPreviewUrl) {
        actualizarAvataresEnVista(avatarPreviewUrl);
      }

      modalUsuarioInstance()?.hide();

      await notificarAvatar(
        'success',
        'Foto actualizada',
        data.mensaje || data.message || 'Tu foto de perfil fue actualizada correctamente.'
      );

    } catch (errorUpload) {
      await notificarAvatar(
        'error',
        'No se pudo actualizar',
        errorUpload?.message || 'Ocurrió un problema al subir tu foto.'
      );

      if (btnSave) {
        btnSave.disabled = false;
        btnSave.textContent = 'Guardar';
      }
    } finally {
      if (btnSelect) {
        btnSelect.disabled = false;
      }
    }
  }

  function abrirModalAvatar() {
    cerrarPerfil();
    cerrarSidebar();

    crearModalAvatar();

    const preview = document.getElementById('evAvatarPreview');
    if (preview) preview.src = avatarActualUrl();

    const modal = modalUsuarioInstance();

    if (modal) {
      modal.show();
      return;
    }

    notificarAvatar('warning', 'No se pudo abrir', 'No se pudo abrir la ventana para actualizar la foto.');
  }

  function vincularClickAvatares() {
    const avatares = document.querySelectorAll('.user-menu img, #userDropdown img, img[data-ev-avatar-img], .ev-avatar-img, [data-ev-avatar-action="1"]');

    avatares.forEach((img) => {
      if (img.dataset.evAvatarBound === '1') return;

      img.dataset.evAvatarBound = '1';
      img.setAttribute('title', 'Actualizar foto de perfil');
      if (img.tagName !== 'BUTTON') {
        img.setAttribute('role', 'button');
        img.setAttribute('tabindex', '0');
      }

      img.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        abrirModalAvatar();
      });

      img.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') return;

        event.preventDefault();
        event.stopPropagation();
        abrirModalAvatar();
      });
    });
  }

  ajustarDropdownUsuario();
  vincularClickAvatares();

  window.addEventListener('resize', ajustarDropdownUsuario);

  window.EVTopbar = Object.assign(window.EVTopbar || {}, {
    refreshAvatarBindings: vincularClickAvatares,
    openAvatarModal: abrirModalAvatar,
    updateAvatar: actualizarAvataresEnVista
  });
});