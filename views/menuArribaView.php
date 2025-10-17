<!-- 🔹 Barra superior -->
<nav class="app-header navbar navbar-expand-lg navbar-dark shadow-sm px-3" style="background-color:#0F592F;">
  <div class="container-fluid d-flex align-items-center justify-content-between">
    
    <!-- 🔹 Botón hamburguesa lateral -->
    <button class="btn border-0 d-lg-none" type="button" id="btnToggleSidebar" aria-label="Mostrar menú lateral">
      <i class="bi bi-list text-white fs-3"></i>
    </button>

    <!-- 🔹 Logo / Marca -->
    <a class="navbar-brand fw-bold text-white d-flex align-items-center ms-2" href="#">
      <img src="<?= BASE_URL ?>resources/images/logo/logo8.png" alt="Logo" style="height:38px;" class="me-2">
      <span class="d-none d-sm-inline">Entre Vecinos</span>
    </a>

    <!-- 🔹 Ícono usuario versión móvil -->
    <div class="d-lg-none">
      <div class="dropdown">
        <button class="btn border-0 p-0" id="dropdownUserMobile" data-bs-toggle="dropdown" aria-expanded="false">
          <img
            src="<?= BASE_URL ?>views/fotos/00000000.png"
            alt="Usuario"
            class="rounded-circle border border-light shadow-sm"
            style="width:38px; height:38px; object-fit:cover;"
          />
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
          <li class="text-center p-3 bg-success text-white rounded-top">
            <img
              src="<?= BASE_URL ?>views/fotos/00000000.png"
              class="rounded-circle shadow-sm mb-2 border border-light"
              style="width:70px; height:70px; object-fit:cover;"
              alt="Usuario"
            />
            <p class="mb-0 fw-semibold">Renzo</p>
            <small>Vecino</small>
          </li>
          <li class="d-flex justify-content-between px-3 py-2 bg-light rounded-bottom">
            <a href="#" id="btnPerfilMobile" class="btn btn-outline-success btn-sm">
              <i class="bi bi-person-circle me-1"></i> Perfil
            </a>
            <a href="#" id="btnCerrarSesionMobile" class="btn btn-danger btn-sm">
              <i class="bi bi-box-arrow-right me-1"></i> Salir
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- 🔹 Menú usuario versión escritorio -->
    <ul class="navbar-nav d-none d-lg-flex align-items-center mb-0">
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle d-flex align-items-center text-white" data-bs-toggle="dropdown">
          <img
            src="<?= BASE_URL ?>views/fotos/00000000.png"
            alt="Usuario"
            class="rounded-circle me-2 border border-light shadow-sm"
            style="width:35px; height:35px; object-fit:cover;"
          />
          <span class="fw-semibold">Renzo</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
          <li class="text-center p-3 bg-success text-white rounded-top">
            <img
              src="<?= BASE_URL ?>views/fotos/00000000.png"
              class="rounded-circle shadow-sm mb-2 border border-light"
              style="width:70px; height:70px; object-fit:cover;"
              alt="Usuario"
            />
            <p class="mb-0 fw-semibold">Renzo</p>
            <small>Vecino</small>
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
</nav>

<script>
  document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebar-backdrop");
  const toggleBtn = document.getElementById("btnToggleSidebar");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("active");
      if (backdrop) backdrop.style.display = sidebar.classList.contains("active") ? "block" : "none";
    });
  }

  if (backdrop) {
    backdrop.addEventListener("click", () => {
      sidebar.classList.remove("active");
      backdrop.style.display = "none";
    });
  }

  // 🔹 Eventos cerrar sesión (móvil + escritorio)
  const cerrarSesionBtns = document.querySelectorAll("#btnCerrarSesion, #btnCerrarSesionMobile");
  cerrarSesionBtns.forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      Swal.fire({
        title: "¿Deseas cerrar sesión?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, salir",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "<?= BASE_URL ?>controllers/logoutController.php";
        }
      });
    });
  });
});

</script>