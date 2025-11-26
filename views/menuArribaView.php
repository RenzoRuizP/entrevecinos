<?php 
// ✅ Seguridad ante datos no definidos
$nombreUsuario = $nombreUsuario ?? 'Vecino';
$rolUsuario = $rolUsuario ?? 'vecino';
$fotoUsuario = "/entrevecinos/views/fotos/00000000.png";
$iconEntreVecinos = "/entrevecinos/resources/images/logo/icon_logo.png";
?>

<!-- 🔹 Barra superior -->
<nav class="app-header navbar navbar-expand-lg navbar-dark shadow-sm px-3" style="background-color: #0F592F; position: relative; z-index: 1050; overflow: visible !important;">
  <div class="container-fluid">

    <!-- 🔹 Botón hamburguesa lateral -->
    <button class="btn border-0 d-lg-none me-2" type="button" id="btnToggleSidebar" aria-label="Mostrar menú lateral">
      <i class="bi bi-list text-white fs-3"></i>
    </button>

    <!-- 🔹 Marca -->
    <!-- Brand Entre Vecinos con ícono -->
    
    <!-- 🔹 Marca -->
     <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white shadow-sm me-2"
            style="width: 38px; height: 38px;">
        <img src="<?= $iconEntreVecinos ?>"
             alt="Logo Entre Vecinos"
             class="img-fluid"
             style="max-height: 40px;">
      </span>
    <span class="navbar-brand mb-0 h5 text-white d-none d-md-inline">Entre Vecinos</span>
    <!-- 🔹 Usuario -->
    <ul class="navbar-nav align-items-center ms-auto">
      <li class="nav-item dropdown user-menu position-relative">
        <a href="#" 
           class="nav-link dropdown-toggle d-flex align-items-center text-white" 
           id="userDropdown" 
           data-bs-toggle="dropdown" 
           aria-expanded="false">

          <img
            src="<?= $fotoUsuario ?>"
            alt="Usuario"
            class="rounded-circle me-2 border border-white"
            style="width:38px; height:38px; object-fit:cover;"
          />
          <span class="fw-semibold d-none d-lg-inline"><?= htmlspecialchars($nombreUsuario) ?></span>
        </a>

        <!-- 🔹 Dropdown del usuario -->
        <!-- dropdown-menu-end para alinear a la derecha en escritorio -->
        <ul class="dropdown-menu border-0 shadow-lg mt-3 rounded-4 overflow-hidden" style="min-width: 230px;">

          <li class="text-center p-3 bg-success text-white">
            <img
              src="<?= $fotoUsuario ?>"
              class="rounded-circle shadow-sm mb-2 border border-white"
              style="width:70px; height:70px; object-fit:cover;"
              alt="Usuario"
            />
            <p class="mb-0 fw-semibold"><?= htmlspecialchars($nombreUsuario) ?></p>
            <small><?= ucfirst(htmlspecialchars($rolUsuario)) ?></small>
          </li>
          <li class="bg-white">
            <div class="d-flex justify-content-between px-3 py-3">
              <a href="#" id="btnPerfil" class="btn btn-outline-success btn-sm">
                <i class="bi bi-person-circle me-1"></i> Perfil
              </a>
              <a href="#" id="btnCerrarSesion" class="btn btn-danger btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Salir
              </a>
            </div>
          </li>
        </ul>
      </li>
    </ul>

  </div>
</nav>
