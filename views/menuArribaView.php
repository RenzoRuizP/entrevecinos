<?php 
// Se asegura de tener los datos del usuario (por seguridad adicional)
$nombreUsuario = $nombreUsuario ?? 'Vecino';
$rolUsuario = $rolUsuario ?? 'vecino';
?>
<!-- 🔹 Barra superior -->
<nav class="app-header navbar navbar-expand-lg navbar-dark shadow-sm px-3" style="background-color: #0F592F;">
  <div class="container-fluid">

    <!-- 🔹 Botón hamburguesa lateral -->
    <button class="btn border-0 d-lg-none me-2" type="button" id="btnToggleSidebar" aria-label="Mostrar menú lateral">
      <i class="bi bi-list text-white fs-3"></i>
    </button>

    <!-- 🔹 Marca o título opcional -->
    <span class="navbar-brand mb-0 h5 text-white d-none d-md-inline">Entre Vecinos</span>

    <!-- 🔹 Botón para mostrar menú de usuario en móvil -->
    <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTop" aria-controls="navbarTop" aria-expanded="false" aria-label="Mostrar menú superior">
      <i class="bi bi-person-circle fs-3"></i>
    </button>

    <!-- 🔹 Contenedor colapsable -->
    <div class="collapse navbar-collapse justify-content-end mt-2 mt-lg-0" id="navbarTop">
      <ul class="navbar-nav align-items-center ms-auto">
        <!-- 🔹 Menú usuario -->
        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle d-flex align-items-center text-white" data-bs-toggle="dropdown">
            <img
              src="/entrevecinos/views/fotos/00000000.png"
              alt="Usuario"
              class="rounded-circle me-2"
              style="width:35px; height:35px; object-fit:cover;"
            />
            <span class="fw-semibold"><?= $nombreUsuario ?></span>
          </a>

          <!-- 🔹 Dropdown -->
          <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
            <li class="text-center p-3 bg-success text-white rounded-top">
              <img
                src="/entrevecinos/views/fotos/00000000.png"
                class="rounded-circle shadow-sm mb-2"
                style="width:70px; height:70px; object-fit:cover;"
                alt="Usuario"
              />
              <p class="mb-0 fw-semibold"><?= $nombreUsuario ?></p>
              <small><?= $rolUsuario ?></small>
            </li>
            <li class="d-flex justify-content-between px-3 py-2 bg-light rounded-bottom">
              <a href="#" id="btnPerfil" class="btn btn-outline-success btn-sm">
                <i class="bi bi-person-circle me-1"></i> Perfil
              </a>
              <a href="#" id="btnCerrarSesion" class="btn btn-danger btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Salir
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
