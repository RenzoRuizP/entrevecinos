<?php
try {
    $usuario = SesionJWT::verificarToken($token);
    if (!$usuario) {
        header("Location: login.php?error=token_expirado");
        exit;
    }
    $usuarioRol = $usuario->rol;
    $_POST["nombreRol"] = $usuarioRol;
} catch (Exception $e) {
    header("Location: login.php?error=token_error");
    exit;
}
?>
<?php include_once 'estilos/menuIzquierda.estilo.php'; ?>
<aside class="app-sidebar shadow" style="background-color:#1f2937;">
  <!-- Sidebar Brand -->
  <div class="sidebar-brand d-flex align-items-center p-3 border-bottom">
    <a href="./MenuPrincipalView.php" class="d-flex align-items-center text-decoration-none">
       <img
        src="../resources/images/logo/logo2.png"
        alt="Entre Vecinos"
        class="brand-image"
        style="height:120px;"
      />
      <!--<span class="brand-text fw-bold text-white">Entre Vecinos</span>-->
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
            $nombreMenu = $resultadoOpcionesMenuBD[$i]["nombre"];
            $codigoMenu = $resultadoOpcionesMenuBD[$i]["codigo_menu"];

            // Menú principal
            echo '<li class="nav-item">';
            echo '  <a href="#menu'.$codigoMenu.'" class="nav-link d-flex align-items-center px-3 py-2" data-bs-toggle="collapse">';
            echo '    <i class="nav-icon '.$iconoMenu.' me-2"></i>';
            echo '    <span>'.strtoupper($nombreMenu).'</span>';
            echo '    <i class="bi bi-chevron-down ms-auto"></i>';
            echo '  </a>';

            // Submenús
            $_POST["codigo_menu"] = $codigoMenu;
            require '../controllers/obtenerOpcionesMenuItemController.php';

            echo '<ul class="nav nav-treeview collapse ms-3" id="menu'.$codigoMenu.'">';
            for ($j = 0; $j < count($resultadoOpcionesMenuItemBD); $j++) {
                $iconoMenuItem = $resultadoOpcionesMenuItemBD[$j]["icono"];
                $nombreItem    = $resultadoOpcionesMenuItemBD[$j]["nombre"];

                echo '<li class="nav-item">';
                echo '  <a href="./docs/color-mode.html" class="nav-link d-flex align-items-center px-3 py-2">';
                echo '    <i class="'.$iconoMenuItem.' me-2"></i>';
                echo '    <span>'.$nombreItem.'</span>';
                echo '  </a>';
                echo '</li>';
            }
            echo '</ul>';
            echo '</li>';
        }
        ?>
      </ul>
    </nav>
  </div>
</aside>


