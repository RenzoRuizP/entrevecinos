<?php
require_once __DIR__ . '/../Config/config.php';

$menus = $menusParaMenuIzquierda ?? [];
$iconEntreVecinos = rtrim(BASE_URL, '/') . "/resources/images/logo/icon_logo.png";

function ev_normalizar_ruta_menu(string $rutaRaw): array {
    $rutaRaw = trim($rutaRaw);

    if ($rutaRaw === '' || $rutaRaw === '#') {
        return ['dataVista' => '#', 'href' => '#'];
    }

    $rutaRaw = explode('?', $rutaRaw, 2)[0];

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
    $dataVista = $rutaRaw;
    $href      = rtrim(BASE_URL, '/') . $dataVista;

    return ['dataVista' => $dataVista, 'href' => $href];
}

function ev_obtener_ruta_activa_menu(): string {
    $evGoto = trim((string)($_GET['ev_goto'] ?? ''));

    if ($evGoto !== '') {
        return ev_normalizar_ruta_menu($evGoto)['dataVista'];
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
    <a href="<?= rtrim(BASE_URL, '/') . '/MenuPrincipal' ?>"
       class="d-flex align-items-center text-decoration-none"
       aria-label="Ir al inicio de Entre Vecinos">
      <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white shadow-sm me-2"
            style="width: 50px; height: 50px;">
        <img src="<?= htmlspecialchars($iconEntreVecinos, ENT_QUOTES, 'UTF-8') ?>"
             alt="Logo Entre Vecinos"
             class="img-fluid"
             style="max-height: 50px;">
      </span>
    </a>
  </div>

  <div class="sidebar-wrapper overflow-hidden">
    <nav class="mt-2" aria-label="Menú principal">
      <ul class="nav flex-column" id="navigation">
        <?php foreach ($menus as $menu):
          $codigoMenu = (int)($menu['codigo_menu'] ?? 0);
          $nombreMenu = (string)($menu['nombre'] ?? '');
          $iconoMenu  = (string)($menu['icono'] ?? '');
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
          <li class="nav-item mb-1">
            <a href="#menu<?= $codigoMenu ?>"
               class="nav-link menu-parent-link d-flex align-items-center px-3 py-2 fw-semibold <?= $menuActivo ? 'active-parent' : '' ?>"
               data-bs-toggle="collapse"
               data-menu-id="<?= $codigoMenu ?>"
               aria-expanded="<?= $menuActivo ? 'true' : 'false' ?>"
               aria-controls="menu<?= $codigoMenu ?>">
              <i class="nav-icon <?= htmlspecialchars($iconoMenu, ENT_QUOTES, 'UTF-8') ?> me-2"></i>
              <span><?= strtoupper(htmlspecialchars($nombreMenu, ENT_QUOTES, 'UTF-8')) ?></span>
              <?php if (!empty($submenusProcesados)): ?>
                <i class="bi bi-chevron-down ms-auto small"></i>
              <?php endif; ?>
            </a>

            <?php if (!empty($submenusProcesados)): ?>
              <ul class="nav nav-treeview collapse ms-3 <?= $menuActivo ? 'show' : '' ?>"
                  id="menu<?= $codigoMenu ?>"
                  data-menu-group="<?= $codigoMenu ?>">
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
</aside>