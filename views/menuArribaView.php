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

          <span class="fw-semibold d-none d-lg-inline">
            <?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?>
          </span>
        </a>

        <ul class="dropdown-menu border-0 shadow-lg mt-3 rounded-4 overflow-hidden" style="min-width: 230px;">

          <li class="text-center p-3 bg-success text-white">
            <img
              src="<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>"
              class="rounded-circle shadow-sm mb-2 border border-white"
              style="width:70px; height:70px; object-fit:cover;"
              alt="Usuario"
            />

            <p class="mb-0 fw-semibold">
              <?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?>
            </p>

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

  function textoEstado(activo) {
    return activo
      ? 'Disponible para recibir pedidos'
      : 'No disponible para recibir pedidos';
  }

  function renderSkeleton() {
    mount.innerHTML = `
      <div class="ev-disp-control-skeleton" aria-hidden="true"></div>
    `;
  }

  function aplicarEstadoVisual(control, switchLabel, input, activo) {
    const estado = textoEstado(activo);

    if (control) {
      control.classList.toggle('is-on', activo);
      control.classList.toggle('is-off', !activo);
      control.setAttribute('title', estado);
      control.setAttribute('aria-label', estado);
      control.setAttribute('data-estado', activo ? 'disponible' : 'no-disponible');
    }

    if (switchLabel) {
      switchLabel.classList.toggle('is-on', activo);
      switchLabel.classList.toggle('is-off', !activo);
      switchLabel.setAttribute('title', estado);
    }

    if (input) {
      input.checked = activo;
      input.setAttribute('aria-label', estado);
      input.setAttribute('aria-checked', activo ? 'true' : 'false');
    }
  }

  function renderControl(disponibilidad) {
    const isOn = Number(disponibilidad) === 1;
    const estado = textoEstado(isOn);

    mount.innerHTML = `
      <div class="ev-disp-control ${isOn ? 'is-on' : 'is-off'}"
           title="${estado}"
           aria-label="${estado}"
           data-estado="${isOn ? 'disponible' : 'no-disponible'}">
        <label class="ev-switch ${isOn ? 'is-on' : 'is-off'}" title="${estado}">
          <input
            type="checkbox"
            role="switch"
            id="evDispSwitch"
            ${isOn ? 'checked' : ''}
            aria-checked="${isOn ? 'true' : 'false'}"
            aria-label="${estado}"
          >
          <span class="ev-switch-slider"></span>
        </label>
      </div>
    `;

    const control = mount.querySelector('.ev-disp-control');
    const sw = document.getElementById('evDispSwitch');
    const switchLabel = mount.querySelector('.ev-switch');

    if (!control || !sw || !switchLabel) return;

    sw.addEventListener('change', async () => {
      const nuevo = sw.checked ? 1 : 0;
      const anterior = nuevo === 1 ? 0 : 1;

      sw.disabled = true;
      control.classList.add('is-updating');

      try {
        const fd = new FormData();
        fd.append('disponibilidad', String(nuevo));

        const resp = await fetch(`${BASE}/api/usuario/disponibilidad-pedidos`, {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json'
          }
        });

        const data = await resp.json().catch(() => ({}));

        if (!resp.ok || !data.ok) {
          aplicarEstadoVisual(control, switchLabel, sw, Number(anterior) === 1);

          if (window.Swal?.fire) {
            Swal.fire({
              icon: 'warning',
              title: 'No se pudo actualizar',
              text: data.mensaje || 'Ocurrió un problema al cambiar tu disponibilidad.',
              confirmButtonText: 'Aceptar',
              confirmButtonColor: '#EA7C12'
            });
          }

          return;
        }

        aplicarEstadoVisual(control, switchLabel, sw, nuevo === 1);

      } catch (e) {
        aplicarEstadoVisual(control, switchLabel, sw, Number(anterior) === 1);

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
        control.classList.remove('is-updating');
        sw.disabled = false;
      }
    });
  }

  async function cargarDisponibilidad() {
    renderSkeleton();

    try {
      const resp = await fetch(`${BASE}/api/usuario/disponibilidad-pedidos`, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json'
        }
      });

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