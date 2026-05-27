<?php
// views/menuArribaView.php
require_once __DIR__ . '/../Config/config.php';

$nombreUsuario = $nombreUsuario ?? 'Vecino';
$rolUsuario    = $rolUsuario ?? 'vecino';
$rolUsuarioRaw = $rolUsuarioRaw ?? strtolower(trim((string)$rolUsuario));

$baseUrl = rtrim(BASE_URL, '/');

$fotoUsuario = $baseUrl . "/views/fotos/00000000.png";
$iconEntreVecinos = $baseUrl . "/resources/images/logo/logo_ev_transparente_corregido_recortado.png";
$homeUrl = $baseUrl . "/MenuPrincipal";
?>

<nav class="app-header navbar navbar-expand-lg navbar-dark shadow-sm px-3">
  <div class="container-fluid">

    <button class="btn border-0 d-lg-none me-2" type="button" id="btnToggleSidebar" aria-label="Mostrar menú lateral">
      <i class="bi bi-list text-white fs-3"></i>
    </button>

    <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>"
       class="ev-topbar-brand"
       aria-label="Ir al panel principal de Entre Vecinos">

      <span class="ev-topbar-brand-logo" aria-hidden="true">
        <img
          src="<?= htmlspecialchars($iconEntreVecinos, ENT_QUOTES, 'UTF-8') ?>"
          alt="">
      </span>

      <span class="ev-topbar-brand-text">Entre Vecinos</span>
    </a>

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
              <a href="<?= $baseUrl ?>/mi-perfil"
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
  'use strict';

  const BASE = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  const mount = document.getElementById('evDisponibilidadMount');

  if (!mount) return;

  if (window.__EV_DISP_PEDIDOS_INIT__ === true) {
    if (window.EVDisponibilidadPedidosTopbar && typeof window.EVDisponibilidadPedidosTopbar.refresh === 'function') {
      window.EVDisponibilidadPedidosTopbar.refresh();
    }
    return;
  }

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
      input.checked = !!activo;
      input.setAttribute('aria-label', estado);
      input.setAttribute('aria-checked', activo ? 'true' : 'false');
    }
  }

  async function notificar(tipo, titulo, texto) {
    if (window.Swal?.fire) {
      await Swal.fire({
        icon: tipo,
        title: titulo,
        text: texto,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: tipo === 'error' ? '#DC2626' : '#EA7C12',
        allowOutsideClick: false
      });
      return;
    }

    alert(`${titulo}\n\n${texto}`);
  }

  async function manejarAuth(resp, data) {
    if (resp.status === 401 || resp.status === 403) {
      const redirect = data?.redirect || `${BASE}/login`;

      await notificar(
        'info',
        'Sesión finalizada',
        data?.mensaje || data?.message || 'Tu sesión ya no está activa. Vuelve a iniciar sesión.'
      );

      window.location.href = redirect;
      return true;
    }

    if (resp.status === 409 && String(data?.error || '').trim() === 'CUENTA_OBSERVADA') {
      const redirect = data?.redirect || `${BASE}/cuenta-observada`;

      await notificar(
        'warning',
        'Cuenta observada',
        data?.mensaje || 'Debes revisar el estado de tu cuenta.'
      );

      window.location.href = redirect;
      return true;
    }

    return false;
  }

  function sincronizarModulosEV(disponible) {
    try {
      if (window.EVMarketplace && typeof window.EVMarketplace.refreshDisponibilidad === 'function') {
        window.EVMarketplace.refreshDisponibilidad({ force: true });
      }
    } catch (_) {}

    try {
      if (window.EVRecibirPedidos && typeof window.EVRecibirPedidos.refresh === 'function') {
        window.EVRecibirPedidos.refresh();
      }
    } catch (_) {}

    try {
      if (window.EVMisPedidosVendedor && typeof window.EVMisPedidosVendedor.refresh === 'function') {
        window.EVMisPedidosVendedor.refresh();
      }
    } catch (_) {}

    try {
      if (window.EVPollingControl) {
        if (typeof window.EVPollingControl.pauseBriefly === 'function') {
          window.EVPollingControl.pauseBriefly();
        }

        if (disponible && typeof window.EVPollingControl.revisarPedidosVendedor === 'function') {
          window.EVPollingControl.revisarPedidosVendedor({ silent: true, force: true });
        }
      }
    } catch (_) {}
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
          credentials: 'include',
          cache: 'no-store',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await resp.json().catch(() => ({}));

        if (await manejarAuth(resp, data)) {
          return;
        }

        if (!resp.ok || !data.ok) {
          aplicarEstadoVisual(control, switchLabel, sw, Number(anterior) === 1);

          await notificar(
            'warning',
            'No se pudo actualizar',
            data.mensaje || 'Ocurrió un problema al cambiar tu disponibilidad.'
          );

          return;
        }

        aplicarEstadoVisual(control, switchLabel, sw, nuevo === 1);
        sincronizarModulosEV(nuevo === 1);

      } catch (e) {
        aplicarEstadoVisual(control, switchLabel, sw, Number(anterior) === 1);

        await notificar(
          'error',
          'Error',
          'No se pudo conectar con el servidor.'
        );
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
        credentials: 'include',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const data = await resp.json().catch(() => ({}));

      if (await manejarAuth(resp, data)) {
        return;
      }

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

  window.EVDisponibilidadPedidosTopbar = {
    refresh: cargarDisponibilidad
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', cargarDisponibilidad);
  } else {
    cargarDisponibilidad();
  }
})();
</script>
<?php endif; ?>