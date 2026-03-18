<?php
require_once __DIR__ . '/../Config/config.php';

$nombreUsuario = $nombreUsuario ?? 'Vecino';
$rolUsuario    = $rolUsuario ?? 'vecino';
$rolUsuarioRaw = $rolUsuarioRaw ?? strtolower(trim((string)$rolUsuario));

$fotoUsuario = rtrim(BASE_URL, '/') . "/views/fotos/00000000.png";
$iconEntreVecinos = rtrim(BASE_URL, '/') . "/resources/images/logo/icon_logo.png";
$baseUrl = rtrim(BASE_URL, '/');
?>

<nav class="app-header navbar navbar-expand-lg navbar-dark shadow-sm px-3">
  <div class="container-fluid">

    <button class="btn border-0 d-lg-none me-2" type="button" id="btnToggleSidebar" aria-label="Mostrar menú lateral">
      <i class="bi bi-list text-white fs-3"></i>
    </button>

    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white shadow-sm me-2"
          style="width: 38px; height: 38px;">
      <img src="<?= htmlspecialchars($iconEntreVecinos, ENT_QUOTES, 'UTF-8') ?>"
           alt="Logo Entre Vecinos"
           class="img-fluid"
           style="max-height: 40px;">
    </span>
    <span class="navbar-brand mb-0 h5 text-white d-none d-md-inline">Entre Vecinos</span>

    <div class="ev-topbar-tools">
      <?php if ($rolUsuarioRaw === 'vecino'): ?>
        <div id="evDisponibilidadMount" class="ev-disp-wrap" aria-live="polite"></div>
      <?php endif; ?>
    </div>

    <ul class="navbar-nav align-items-center">
      <li class="nav-item dropdown user-menu position-relative">
        <a href="#"
           class="nav-link dropdown-toggle d-flex align-items-center text-white"
           id="userDropdown"
           data-bs-toggle="dropdown"
           aria-expanded="false">

          <img
            src="<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>"
            alt="Usuario"
            class="rounded-circle me-2 border border-white"
            style="width:38px; height:38px; object-fit:cover;"
          />
          <span class="fw-semibold d-none d-lg-inline"><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?></span>
        </a>

        <ul class="dropdown-menu border-0 shadow-lg mt-3 rounded-4 overflow-hidden" style="min-width: 230px;">

          <li class="text-center p-3 bg-success text-white">
            <img
              src="<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>"
              class="rounded-circle shadow-sm mb-2 border border-white"
              style="width:70px; height:70px; object-fit:cover;"
              alt="Usuario"
            />
            <p class="mb-0 fw-semibold"><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?></p>
            <small><?= ucfirst(htmlspecialchars($rolUsuario, ENT_QUOTES, 'UTF-8')) ?></small>
          </li>

          <li class="bg-white">
            <div class="d-flex justify-content-between px-3 py-3">
              <a href="<?= rtrim(BASE_URL, '/') ?>/mi-perfil"
                 id="btnPerfil"
                 class="btn btn-outline-success btn-sm submenu-link">
                <i class="bi bi-person-circle me-1"></i> Mis datos
              </a>

              <a href="#"
                 id="btnCerrarSesion"
                 class="btn btn-danger btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Salir
              </a>
            </div>
          </li>

        </ul>
      </li>
    </ul>

  </div>
</nav>

<?php if ($rolUsuarioRaw === 'vecino'): ?>
<script>
(() => {
  const BASE = <?= json_encode($baseUrl) ?>;
  const mount = document.getElementById('evDisponibilidadMount');
  if (!mount) return;

  if (window.__EV_DISP_PEDIDOS_INIT__) return;
  window.__EV_DISP_PEDIDOS_INIT__ = true;

  function renderSkeleton() {
    mount.innerHTML = `<div class="ev-disp-skeleton" aria-hidden="true"></div>`;
  }

  function renderControl(disponibilidad) {
    const isOn = Number(disponibilidad) === 1;

    mount.innerHTML = `
      <div class="ev-disp-card">
        <div class="ev-disp-copy">
          <span class="ev-disp-label">Disponibilidad</span>
          <span class="ev-disp-state ${isOn ? 'is-on' : 'is-off'}" id="evDispEstadoTexto">
            ${isOn ? 'Disponible' : 'No disponible'}
          </span>
        </div>

        <label class="ev-switch" title="Cambiar disponibilidad">
          <input type="checkbox" id="evDispSwitch" ${isOn ? 'checked' : ''}>
          <span class="ev-switch-slider"></span>
        </label>
      </div>
    `;

    const sw = document.getElementById('evDispSwitch');
    const tx = document.getElementById('evDispEstadoTexto');

    if (!sw || !tx) return;

    sw.addEventListener('change', async () => {
      const nuevo = sw.checked ? 1 : 0;
      sw.disabled = true;

      try {
        const fd = new FormData();
        fd.append('disponibilidad', String(nuevo));

        const resp = await fetch(`${BASE}/api/usuario/disponibilidad-pedidos`, {
          method: 'POST',
          body: fd
        });

        const data = await resp.json().catch(() => ({}));

        if (!resp.ok || !data.ok) {
          sw.checked = !sw.checked;

          if (window.Swal?.fire) {
            Swal.fire({
              icon: 'warning',
              title: 'No se pudo actualizar',
              text: data.mensaje || 'Ocurrió un problema al cambiar tu disponibilidad.',
              confirmButtonText: 'Aceptar',
              confirmButtonColor: '#EA7C12'
            });
          }
        }

        const activo = sw.checked;
        tx.textContent = activo ? 'Disponible' : 'No disponible';
        tx.classList.toggle('is-on', activo);
        tx.classList.toggle('is-off', !activo);

      } catch (e) {
        sw.checked = !sw.checked;

        if (window.Swal?.fire) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo conectar con el servidor.',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#EA7C12'
          });
        }
      } finally {
        sw.disabled = false;
      }
    });
  }

  async function cargarDisponibilidad() {
    renderSkeleton();

    try {
      const resp = await fetch(`${BASE}/api/usuario/disponibilidad-pedidos`, { method: 'GET' });
      const data = await resp.json().catch(() => ({}));

      if (!resp.ok || !data.ok) {
        mount.innerHTML = '';
        return;
      }

      const info = data.data || {};
      if (!info.mostrar_control) {
        mount.innerHTML = '';
        return;
      }

      renderControl(Number(info.disponibilidad || 0));

    } catch (e) {
      mount.innerHTML = '';
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', cargarDisponibilidad);
  } else {
    cargarDisponibilidad();
  }
})();
</script>
<?php endif; ?>