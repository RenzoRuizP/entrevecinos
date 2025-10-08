<nav class="app-header navbar navbar-expand bg-dark shadow-sm px-3">
  <div class="container-fluid">
    <!-- Sidebar toggle -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link text-light" data-lte-toggle="sidebar" href="#" role="button">
          <i class="bi bi-list fs-4"></i>
        </a>
      </li>
      <li class="nav-item d-none d-md-block">
        <a href="#" class="nav-link text-light fw-semibold">Inicio</a>
      </li>
      <li class="nav-item d-none d-md-block">
        <a href="#" class="nav-link text-light">Contacto</a>
      </li>
    </ul>

    <!-- User menu -->
    <ul class="navbar-nav ms-auto">
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
          <img
            src="<?= BASE_URL ?>views/fotos/00000000.png"
            class="user-image rounded-circle shadow-sm me-2"
            alt="User Image"
            style="width:32px; height:32px; object-fit:cover;"
          />
          <span class="d-none d-md-inline text-light fw-medium">
            <?php echo htmlspecialchars($usuario['nombre']); ?>
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
          <!-- User header -->
          <li class="user-header text-center p-3 rounded-top" style="background-color:#198754;">
            <img
              src="<?= BASE_URL ?>views/fotos/00000000.png"
              class="rounded-circle shadow-sm mb-2"
              alt="User Image"
              style="width:70px; height:70px; object-fit:cover;"
            />
            <p class="mb-0 fw-semibold text-white"><?php echo htmlspecialchars($usuario['nombre']); ?></p>
            <small class="text-white-50"><?php echo htmlspecialchars($usuario['rol'] ?? "Usuario"); ?></small>
          </li>

          <!-- User footer -->
          <li class="d-flex justify-content-between px-3 py-2 bg-light rounded-bottom">
            <a href="#" class="btn btn-outline-success btn-sm">
              <i class="bi bi-person-circle me-1"></i> Perfil
            </a>
            <a href="#" id="btnCerrarSesion" class="btn btn-danger btn-sm">
              <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
            </a>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>views/js/menuArriba.js"></script>