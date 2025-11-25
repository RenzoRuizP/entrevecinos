<?php
require_once __DIR__ . '/../Config/config.php';
$menus = $menusParaMenuIzquierda ?? [];
?>
<?php include_once __DIR__ . '/estilos/menuIzquierdaEstilo.php'; ?>

<aside id="sidebar" class="app-sidebar shadow" style="background-color:#115C41;">
  <!-- 🔹 Encabezado del sidebar -->
  <div class="sidebar-brand d-flex align-items-center justify-content-center p-3 border-bottom">
    <a href="<?= BASE_URL ?>index.php" class="d-flex align-items-center text-decoration-none">
      <img
        src="<?= BASE_URL ?>resources/images/logo/logo.png"
        alt="Entre Vecinos"
        class="brand-image"
        style="height:90px; object-fit:contain;"
      />
    </a>
  </div>

  <!-- 🔹 Menú lateral -->
  <div class="sidebar-wrapper overflow-hidden">
    <nav class="mt-3">
      <ul class="nav flex-column" id="navigation">
        <?php foreach ($menus as $menu):
          $codigoMenu = $menu['codigo_menu'];
          $nombreMenu = $menu['nombre'];
          $iconoMenu  = $menu['icono'];
          $submenus   = $menu['submenus'] ?? [];
        ?>
          <li class="nav-item mb-1">
            <a href="#menu<?= $codigoMenu ?>"
               class="nav-link d-flex align-items-center px-3 py-2 text-white fw-semibold"
               data-bs-toggle="collapse"
               aria-expanded="false"
               aria-controls="menu<?= $codigoMenu ?>"
               style="border-radius:10px; transition:background-color .3s;">
              <i class="nav-icon <?= htmlspecialchars($iconoMenu) ?> me-2"></i>
              <span><?= strtoupper(htmlspecialchars($nombreMenu)) ?></span>
              <?php if (!empty($submenus)): ?>
                <i class="bi bi-chevron-down ms-auto small"></i>
              <?php endif; ?>
            </a>

            <?php if (!empty($submenus)): ?>
              <ul class="nav nav-treeview collapse ms-3" id="menu<?= $codigoMenu ?>">
                <?php foreach ($submenus as $submenu): ?>
                  <li class="nav-item">
                    <a href="<?= htmlspecialchars($submenu['ruta'] ?? '#') ?>"
                       class="nav-link submenu-link d-flex align-items-center px-3 py-2 text-light"
                       style="font-size:0.95rem;">
                      <i class="<?= htmlspecialchars($submenu['icono'] ?? 'fas fa-circle') ?> me-2 text-secondary"></i>
                      <span><?= htmlspecialchars($submenu['nombre'] ?? '') ?></span>
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