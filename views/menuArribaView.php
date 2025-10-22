<!-- 🔹 Barra superior -->
<nav class="app-header navbar navbar-expand-lg navbar-dark shadow-sm px-3">
  <div class="container-fluid">
    
    <!-- 🔹 Botón hamburguesa para mostrar/ocultar menú lateral -->
    <button class="navbar-toggler border-0" type="button" id="btnToggleSidebar" aria-label="Mostrar menú">
      <i class="bi bi-list text-white fs-3"></i>
    </button>

    <!-- 🔹 Contenedor colapsable (futuras opciones si las hay) -->
    <div class="collapse navbar-collapse" id="navbarTop">
      <ul class="navbar-nav me-auto"></ul>

      <!-- 🔹 Menú usuario -->
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle d-flex align-items-center text-white" data-bs-toggle="dropdown">
            <img
              src="/entrevecinos/views/fotos/00000000.png"
              alt="Usuario"
              class="rounded-circle me-2"
              style="width:35px; height:35px; object-fit:cover;"
            />
            <span class="d-none d-md-inline fw-semibold">Renzo</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
            <li class="text-center p-3 bg-success text-white rounded-top">
              <img
                src="/entrevecinos/views/fotos/00000000.png"
                class="rounded-circle shadow-sm mb-2"
                style="width:70px; height:70px; object-fit:cover;"
                alt="Usuario"
              />
              <p class="mb-0 fw-semibold">Renzo</p>
              <small>vecino</small>
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
