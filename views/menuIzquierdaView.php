<?php
require_once __DIR__ . '/../Config/config.php';
$menus = $menusParaMenuIzquierda ?? [];
?>
<?php include_once __DIR__ . '/estilos/menuIzquierda.estilo.php'; ?>

<aside class="app-sidebar shadow" style="background-color:#1f2937;">
  <!-- Sidebar Brand -->
  <div class="sidebar-brand d-flex align-items-center p-3 border-bottom">
    <a href="<?= BASE_URL ?>index.php" class="d-flex align-items-center text-decoration-none">
      <img
        src="<?= BASE_URL ?>/resources/images/logo/logo8.png"
        alt="Entre Vecinos"
        class="brand-image"
        style="height:120px;"
      />
    </a>
  </div>

  <!-- Sidebar Menu -->
  <div class="sidebar-wrapper">
    <nav class="mt-3">
      <ul class="nav flex-column" id="navigation">
        <?php foreach ($menus as $menu): 
          $codigoMenu = $menu['codigo_menu'];
          $nombreMenu = $menu['nombre'];
          $iconoMenu  = $menu['icono'];
          $submenus   = $menu['submenus'] ?? [];
        ?>
          <li class="nav-item">
            <a href="#menu<?= $codigoMenu ?>" 
               class="nav-link d-flex align-items-center px-3 py-2" 
               data-bs-toggle="collapse" 
               aria-expanded="false" 
               aria-controls="menu<?= $codigoMenu ?>">
              <i class="nav-icon <?= htmlspecialchars($iconoMenu) ?> me-2"></i>
              <span><?= strtoupper(htmlspecialchars($nombreMenu)) ?></span>
              <i class="bi bi-chevron-down ms-auto"></i>
            </a>

            <?php if (!empty($submenus)): ?>
              <ul class="nav nav-treeview collapse ms-3" id="menu<?= $codigoMenu ?>">
                <?php foreach ($submenus as $submenu): ?>
                  <li class="nav-item">
                    <a href="<?= htmlspecialchars($submenu['ruta'] ?? '') ?>" 
                       class="nav-link submenu-link d-flex align-items-center px-3 py-2">
                      <i class="<?= htmlspecialchars($submenu['icono'] ?? 'fas fa-circle') ?> me-2"></i>
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

<script src="<?= BASE_URL ?>views/js/menu-izquierda.js"></script>