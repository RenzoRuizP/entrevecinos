<?php
require_once __DIR__ . '/../Config/config.php';

$menus = $menusParaMenuIzquierda ?? [];
$iconEntreVecinos = rtrim(BASE_URL, '/') . "/resources/images/logo/icon_logo.png";

$usuarioSidebar = isset($usuario) && is_array($usuario) ? $usuario : [];

$tipoConjuntoSidebar = strtolower(trim((string)(
    $usuarioSidebar['conjunto_tipo']
    ?? $usuarioSidebar['tipo_conjunto']
    ?? ''
)));

$nombreComunidadSidebar = trim((string)(
    $usuarioSidebar['conjunto_nombre']
    ?? $usuarioSidebar['nombre_conjunto']
    ?? $usuarioSidebar['condominio_nombre']
    ?? $usuarioSidebar['nombre_condominio']
    ?? $usuarioSidebar['urbanizacion_nombre']
    ?? $usuarioSidebar['nombre_urbanizacion']
    ?? $usuarioSidebar['condominio']
    ?? $usuarioSidebar['urbanizacion']
    ?? ''
));

if ($nombreComunidadSidebar === '') {
    $nombreComunidadSidebar = 'Tu comunidad';
}

$labelComunidadSidebar = ($tipoConjuntoSidebar === 'urbanizacion') ? 'Urbanización' : 'Condominio';
$hrefInicioSidebar = rtrim(BASE_URL, '/') . '/MenuPrincipal';
$hrefMiPerfilSidebar = rtrim(BASE_URL, '/') . '/mi-perfil';
$hrefLogoutSidebar = rtrim(BASE_URL, '/') . '/logout';

function ev_normalizar_ruta_menu(string $rutaRaw): array {
    $rutaRaw = trim($rutaRaw);

    if ($rutaRaw === '' || $rutaRaw === '#') {
        return ['dataVista' => '#', 'href' => '#'];
    }

    $rutaRaw = explode('?', $rutaRaw, 2)[0];

    if ($rutaRaw === '') {
        return ['dataVista' => '#', 'href' => '#'];
    }

    if ($rutaRaw[0] !== '/') {
        $rutaRaw = '/' . $rutaRaw;
    }

    $basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');

    if ($basePath !== '' && $basePath !== '/') {
        if (stripos($rutaRaw, $basePath . '/') === 0) {
            $rutaRaw = substr($rutaRaw, strlen($basePath));
            if ($rutaRaw === '') {
                $rutaRaw = '/';
            }
        }
    }

    $rutaRaw = preg_replace('#/+#', '/', $rutaRaw);
    $rutaRaw = rtrim($rutaRaw, '/');

    if ($rutaRaw === '') {
        $rutaRaw = '/';
    }

    $dataVista = $rutaRaw;
    $href = rtrim(BASE_URL, '/') . $dataVista;

    return ['dataVista' => $dataVista, 'href' => $href];
}

function ev_obtener_ruta_activa_menu(): string {
    $evGoto = trim((string)($_GET['ev_goto'] ?? ''));

    if ($evGoto !== '') {
        return ev_normalizar_ruta_menu(rawurldecode($evGoto))['dataVista'];
    }

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/MenuPrincipal', PHP_URL_PATH);
    $path = is_string($path) ? $path : '/MenuPrincipal';

    $basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');

    if ($basePath !== '' && $basePath !== '/' && stripos($path, $basePath . '/') === 0) {
        $path = substr($path, strlen($basePath));
    }

    $path = preg_replace('#/+#', '/', $path);
    $path = rtrim($path, '/');

    if ($path === '' || $path === '/' || $path === '/login') {
        $path = '/MenuPrincipal';
    }

    return $path;
}

function ev_menu_rutas_equivalentes(string $rutaItem, string $rutaActiva): bool {
    $rutaItem = rtrim($rutaItem, '/');
    $rutaActiva = rtrim($rutaActiva, '/');

    if ($rutaItem === '') {
        $rutaItem = '/';
    }

    if ($rutaActiva === '') {
        $rutaActiva = '/';
    }

    if ($rutaItem === $rutaActiva) {
        return true;
    }

    return (
        ($rutaItem === '/MenuPrincipal' && ($rutaActiva === '/' || $rutaActiva === '/MenuPrincipal')) ||
        ($rutaActiva === '/MenuPrincipal' && ($rutaItem === '/' || $rutaItem === '/MenuPrincipal'))
    );
}

$rutaActivaMenu = ev_obtener_ruta_activa_menu();
?>

<?php include_once __DIR__ . '/estilos/menuIzquierdaEstilo.php'; ?>

<aside id="sidebar" class="app-sidebar shadow">
  <div class="sidebar-brand d-flex align-items-center justify-content-center p-3">
    <a href="<?= htmlspecialchars($hrefInicioSidebar, ENT_QUOTES, 'UTF-8') ?>"
       class="ev-sidebar-brand-link text-decoration-none"
       aria-label="Ir al inicio de Entre Vecinos">
      <span class="ev-sidebar-brand-logo">
        <img src="<?= htmlspecialchars($iconEntreVecinos, ENT_QUOTES, 'UTF-8') ?>"
             alt="Logo Entre Vecinos"
             class="img-fluid">
      </span>
      <span class="ev-sidebar-brand-text">Entre Vecinos</span>
    </a>
  </div>

  <div class="sidebar-wrapper">
    <nav class="mt-2" aria-label="Menú principal">
      <ul class="nav flex-column" id="navigation">
        <?php foreach ($menus as $menu):
          $codigoMenu = (int)($menu['codigo_menu'] ?? 0);
          $nombreMenu = (string)($menu['nombre'] ?? '');
          $iconoMenu  = (string)($menu['icono'] ?? 'bi bi-grid');
          $submenus   = is_array($menu['submenus'] ?? null) ? $menu['submenus'] : [];

          $submenusProcesados = [];
          $menuActivo = false;

          foreach ($submenus as $submenu) {
              $ruta = (string)($submenu['ruta'] ?? '#');
              $r = ev_normalizar_ruta_menu($ruta);
              $esActivo = ev_menu_rutas_equivalentes($r['dataVista'], $rutaActivaMenu);

              if ($esActivo) {
                  $menuActivo = true;
              }

              $submenusProcesados[] = [
                  'data' => $submenu,
                  'ruta' => $r,
                  'activo' => $esActivo,
              ];
          }
        ?>
          <li class="nav-item ev-menu-item mb-1">
            <?php if (!empty($submenusProcesados)): ?>
              <button type="button"
                      class="nav-link menu-parent-link ev-menu-parent d-flex align-items-center px-3 py-2 fw-semibold <?= $menuActivo ? 'active-parent is-open' : '' ?>"
                      data-menu-id="<?= $codigoMenu ?>"
                      data-menu-target="menu<?= $codigoMenu ?>"
                      aria-expanded="<?= $menuActivo ? 'true' : 'false' ?>"
                      aria-controls="menu<?= $codigoMenu ?>">
                <i class="nav-icon <?= htmlspecialchars($iconoMenu, ENT_QUOTES, 'UTF-8') ?> me-2"></i>
                <span><?= strtoupper(htmlspecialchars($nombreMenu, ENT_QUOTES, 'UTF-8')) ?></span>
                <i class="bi bi-chevron-down ms-auto small"></i>
              </button>
            <?php else: ?>
              <button type="button"
                      class="nav-link menu-parent-link ev-menu-parent d-flex align-items-center px-3 py-2 fw-semibold"
                      disabled>
                <i class="nav-icon <?= htmlspecialchars($iconoMenu, ENT_QUOTES, 'UTF-8') ?> me-2"></i>
                <span><?= strtoupper(htmlspecialchars($nombreMenu, ENT_QUOTES, 'UTF-8')) ?></span>
              </button>
            <?php endif; ?>

            <?php if (!empty($submenusProcesados)): ?>
              <ul class="nav nav-treeview ev-menu-group ms-3 <?= $menuActivo ? 'is-open' : '' ?>"
                  id="menu<?= $codigoMenu ?>"
                  data-menu-group="<?= $codigoMenu ?>"
                  aria-hidden="<?= $menuActivo ? 'false' : 'true' ?>">
                <?php foreach ($submenusProcesados as $item):
                  $submenu = $item['data'];
                  $r = $item['ruta'];
                  $esActivo = (bool)$item['activo'];
                ?>
                  <li class="nav-item">
                    <a href="<?= htmlspecialchars($r['href'], ENT_QUOTES, 'UTF-8') ?>"
                       data-vista="<?= htmlspecialchars($r['dataVista'], ENT_QUOTES, 'UTF-8') ?>"
                       class="nav-link submenu-link d-flex align-items-center px-3 py-2 <?= $esActivo ? 'submenu-active active' : '' ?>"
                       <?= $esActivo ? 'aria-current="page"' : '' ?>>
                      <i class="<?= htmlspecialchars($submenu['icono'] ?? 'fas fa-circle', ENT_QUOTES, 'UTF-8') ?> me-2"
                         style="opacity:.9;"></i>
                      <span><?= htmlspecialchars($submenu['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>

  <div id="evSidebarHomeExtras" class="ev-sidebar-footer" aria-label="Accesos secundarios">
    <button type="button" class="ev-sidebar-footer-link" id="btnEvAyudaSidebar">
      <i class="bi bi-question-circle"></i>
      <span>Ayuda</span>
    </button>

    <button type="button"
            class="ev-sidebar-footer-link ev-sidebar-footer-link-logout"
            onclick="window.location.href='<?= htmlspecialchars($hrefLogoutSidebar, ENT_QUOTES, 'UTF-8') ?>'">
      <i class="bi bi-box-arrow-right"></i>
      <span>Cerrar sesión</span>
    </button>

    <article class="ev-sidebar-community-card" aria-label="Comunidad actual">
      <div class="ev-sidebar-community-icon" aria-hidden="true">
        <i class="bi bi-buildings"></i>
      </div>
      <div class="ev-sidebar-community-label">Tu comunidad</div>
      <div class="ev-sidebar-community-name" id="evSidebarCommunityName">
        <?= htmlspecialchars($nombreComunidadSidebar, ENT_QUOTES, 'UTF-8') ?>
      </div>
      <a href="<?= htmlspecialchars($hrefMiPerfilSidebar, ENT_QUOTES, 'UTF-8') ?>"
         data-vista="/mi-perfil"
         class="ev-sidebar-community-btn">
        Cambiar comunidad
      </a>
    </article>
  </div>
</aside>
