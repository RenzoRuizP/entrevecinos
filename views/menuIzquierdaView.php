<?php
try {
    $usuario = SesionJWT::verificarToken($token);
    if (!$usuario) {
        header("Location: login-v2.php?error=token_expirado");
        exit;
    }
    $usuarioRol = $usuario->rol;
    $_POST["nombreRol"] = $usuarioRol;
} catch (Exception $e) {
    header("Location: login-v2.php?error=token_error");
    exit;
}
?>

<aside class="app-sidebar shadow" style="background-color:#1f2937;">
  <!-- Sidebar Brand -->
  <div class="sidebar-brand d-flex align-items-center p-3 border-bottom">
    <a href="./index.html" class="d-flex align-items-center text-decoration-none">
      <img
        src="../resources/images/logo/logo.png"
        alt="Entre Vecinos"
        class="brand-image me-2"
        style="height:40px;"
      />
      <span class="brand-text fw-bold text-white">Entre Vecinos</span>
    </a>
  </div>

  <!-- Sidebar Menu -->
  <div class="sidebar-wrapper">
    <nav class="mt-3">
      <ul class="nav flex-column" id="navigation">
        <?php
        require_once '../controllers/obtenerOpcionesMenuController.php';
        
        for ($i = 0; $i < count($resultadoOpcionesMenuBD); $i++) {
          $iconoMenu = $resultadoOpcionesMenuBD[$i]["icono"];
          echo '<li class="nav-header text-uppercase text-muted small px-3 mt-2">'.
                strtoupper($resultadoOpcionesMenuBD[$i]["nombre"]).'</li>';

          $_POST["codigo_menu"] = $resultadoOpcionesMenuBD[$i]["codigo_menu"];
          require '../controllers/obtenerOpcionesMenuItemController.php';

          for ($j = 0; $j < count($resultadoOpcionesMenuItemBD); $j++) {
            $iconoMenuItem = $resultadoOpcionesMenuItemBD[$j]["icono"];
            $nombreItem = $resultadoOpcionesMenuItemBD[$j]["nombre"];

            echo '<li class="nav-item">';
            echo '  <a href="./docs/color-mode.html" class="nav-link d-flex align-items-center px-3 py-2">';
            echo '    <i class="nav-icon '.$iconoMenuItem.' me-2"></i>';
            echo '    <span>'.$nombreItem.'</span>';
            echo '  </a>';
            echo '</li>';
          }
        }
        ?>
      </ul>
    </nav>
  </div>
</aside>

<!-- Sidebar Styles -->
<style>
  .app-sidebar {
    width: 250px;
    min-height: 100vh;
  }

  .app-sidebar .nav-link {
    color: #e5e7eb; /* gris claro */
    transition: all 0.2s ease-in-out;
    border-radius: .375rem;
    margin: 2px 8px;
  }

  .app-sidebar .nav-link:hover {
    background-color: #22c55e20; /* verde con transparencia */
    color: #22c55e;
  }

  .app-sidebar .nav-link.active {
    background-color: #ff6b00; /* anaranjado activo */
    color: #fff !important;
    font-weight: 600;
  }

  .app-sidebar .nav-header {
    font-size: 0.75rem;
    letter-spacing: 1px;
  }
</style>

