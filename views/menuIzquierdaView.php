<?php
require_once __DIR__ . '/../Config/config.php';

$menus = $menusParaMenuIzquierda ?? [];
$iconEntreVecinos = rtrim(BASE_URL, '/') . "/resources/images/logo/icon_logo.png";

/**
 * Normaliza una ruta del menú:
 * - Acepta: "marketplace", "/marketplace", "/entrevecinos/marketplace", "views/x.php", "/entrevecinos/views/x.php"
 * - Retorna:
 *   - dataVista: ruta relativa a la app (siempre empieza con "/")
 *   - href:      BASE_URL + dataVista (fallback)
 */
function ev_normalizar_ruta_menu(string $rutaRaw): array {
    $rutaRaw = trim($rutaRaw);

    if ($rutaRaw === '' || $rutaRaw === '#') {
        return ['dataVista' => '#', 'href' => '#'];
    }

    // Quitar querystring
    $rutaRaw = explode('?', $rutaRaw, 2)[0];

    // Asegurar que empiece con "/"
    if ($rutaRaw[0] !== '/') $rutaRaw = '/' . $rutaRaw;

    // Remover prefijo basePath si viene pegado (ej: /entrevecinos/marketplace)
    $basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/'); // "/entrevecinos"
    if ($basePath !== '' && $basePath !== '/') {
        if (stripos($rutaRaw, $basePath . '/') === 0) {
            $rutaRaw = substr($rutaRaw, strlen($basePath));
            if ($rutaRaw === '') $rutaRaw = '/';
        }
    }

    $dataVista = $rutaRaw; // siempre empieza con "/"
    $href      = rtrim(BASE_URL, '/') . $dataVista;

    return ['dataVista' => $dataVista, 'href' => $href];
}
?>

<?php include_once __DIR__ . '/estilos/menuIzquierdaEstilo.php'; ?>

<aside id="sidebar" class="app-sidebar shadow" style="background-color:#115C41;">
  <div class="sidebar-brand d-flex align-items-center justify-content-center p-3 border-bottom">
    <a href="<?= rtrim(BASE_URL, '/') . '/MenuPrincipal' ?>" class="d-flex align-items-center text-decoration-none">
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
    <nav class="mt-3">
      <ul class="nav flex-column" id="navigation">
        <?php foreach ($menus as $menu):
          $codigoMenu = (int)($menu['codigo_menu'] ?? 0);
          $nombreMenu = (string)($menu['nombre'] ?? '');
          $iconoMenu  = (string)($menu['icono'] ?? '');
          $submenus   = $menu['submenus'] ?? [];
        ?>
          <li class="nav-item mb-1">
            <a href="#menu<?= $codigoMenu ?>"
               class="nav-link d-flex align-items-center px-3 py-2 text-white fw-semibold"
               data-bs-toggle="collapse"
               aria-expanded="false"
               aria-controls="menu<?= $codigoMenu ?>"
               style="border-radius:10px; transition:background-color .3s;">
              <i class="nav-icon <?= htmlspecialchars($iconoMenu, ENT_QUOTES, 'UTF-8') ?> me-2"></i>
              <span><?= strtoupper(htmlspecialchars($nombreMenu, ENT_QUOTES, 'UTF-8')) ?></span>
              <?php if (!empty($submenus)): ?>
                <i class="bi bi-chevron-down ms-auto small"></i>
              <?php endif; ?>
            </a>

            <?php if (!empty($submenus)): ?>
              <ul class="nav nav-treeview collapse ms-3" id="menu<?= $codigoMenu ?>">
                <?php foreach ($submenus as $submenu):
                  $ruta = (string)($submenu['ruta'] ?? '#');
                  $r = ev_normalizar_ruta_menu($ruta);
                ?>
                  <li class="nav-item">
                    <a href="<?= htmlspecialchars($r['href'], ENT_QUOTES, 'UTF-8') ?>"
                       data-vista="<?= htmlspecialchars($r['dataVista'], ENT_QUOTES, 'UTF-8') ?>"
                       class="nav-link submenu-link d-flex align-items-center px-3 py-2 text-light"
                       style="font-size:0.95rem;">
                      <i class="<?= htmlspecialchars($submenu['icono'] ?? 'fas fa-circle', ENT_QUOTES, 'UTF-8') ?> me-2 text-secondary"></i>
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
