
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entre Vecinos - Panel Principal</title>

  <!-- Bootstrap y dependencias -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Estilos personalizados -->
  <!-- ============================================================
     Estilos base optimizados para Entre Vecinos
     Compatible con AdminLTE 4 + Bootstrap 5 + tus estilos UX/UI personalizados
============================================================== -->

<!-- Tipografía base -->
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.8/index.css"
  crossorigin="anonymous"
  media="print"
  onload="this.media='all'"
/>

<!-- Librerías principales -->
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
  crossorigin="anonymous"
/>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
  crossorigin="anonymous"
/>

<!-- Plugin gráficos -->
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
  crossorigin="anonymous"
/>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
  crossorigin="anonymous"
/>

<!-- AdminLTE principal -->
<link rel="stylesheet" href="/entrevecinos//resources/util/lte4/dist/css/adminlte.css" />
<?php include_once __DIR__ . '/estilos.view.php'; ?>

  <style>
   
  </style>
</head>

<body class="hold-transition">
  <div class="wrapper">

<aside id="sidebar" class="app-sidebar shadow" style="background-color:#115C41;">
  <!-- 🔹 Encabezado del sidebar -->
  <div class="sidebar-brand d-flex align-items-center justify-content-center p-3 border-bottom">
    <a href="/entrevecinos/index.php" class="d-flex align-items-center text-decoration-none">
      
        <img src="<?= BASE_URL ?>resources/images/logo/logo8.png" alt="Logo Entre Vecinos" class="img-fluid" style="max-height: 160px;">
    </a>
  </div>

  <!-- 🔹 Menú lateral -->
  <div class="sidebar-wrapper overflow-hidden">
    <nav class="mt-3">
      <ul class="nav flex-column" id="navigation">
                  <li class="nav-item mb-1">
            <a href="#menu1"
               class="nav-link d-flex align-items-center px-3 py-2 text-white fw-semibold"
               data-bs-toggle="collapse"
               aria-expanded="false"
               aria-controls="menu1"
               style="border-radius:10px; transition:background-color .3s;">
              <i class="nav-icon bi-person-circle me-2"></i>
              <span>MI PERFIL</span>
                <i class="bi bi-chevron-down ms-auto small"></i>
            </a>
            <ul class="nav nav-treeview collapse ms-3" id="menu1">
              <li class="nav-item">
                    <a href="/mi-perfil"
                       class="nav-link submenu-link d-flex align-items-center px-3 py-2 text-light"
                       style="font-size:0.95rem;">
                      <i class="bi-card-text me-2 text-secondary"></i>
                      <span>Datos Personales</span>
                    </a>
                  </li>
                              </ul>
                      </li>
                  <li class="nav-item mb-1">
            <a href="#menu2"
               class="nav-link d-flex align-items-center px-3 py-2 text-white fw-semibold"
               data-bs-toggle="collapse"
               aria-expanded="false"
               aria-controls="menu2"
               style="border-radius:10px; transition:background-color .3s;">
              <i class="nav-icon bi bi-cart-fill me-2"></i>
              <span>COMPRAR</span>
                              <i class="bi bi-chevron-down ms-auto small"></i>
                          </a>

                          <ul class="nav nav-treeview collapse ms-3" id="menu2">
                                  <li class="nav-item">
                    <a href="#"
                       class="nav-link submenu-link d-flex align-items-center px-3 py-2 text-light"
                       style="font-size:0.95rem;">
                      <i class="bi bi-receipt me-2 text-secondary"></i>
                      <span>Mis Pedidos</span>
                    </a>
                  </li>
                              </ul>
                      </li>
                  <li class="nav-item mb-1">
            <a href="#menu3"
               class="nav-link d-flex align-items-center px-3 py-2 text-white fw-semibold"
               data-bs-toggle="collapse"
               aria-expanded="false"
               aria-controls="menu3"
               style="border-radius:10px; transition:background-color .3s;">
              <i class="nav-icon bi bi-box-fill me-2"></i>
              <span>VENDER</span>
                              <i class="bi bi-chevron-down ms-auto small"></i>
                          </a>

                          <ul class="nav nav-treeview collapse ms-3" id="menu3">
                                  <li class="nav-item">
                    <a href="#"
                       class="nav-link submenu-link d-flex align-items-center px-3 py-2 text-light"
                       style="font-size:0.95rem;">
                      <i class="bi bi-clock me-2 text-secondary"></i>
                      <span>Atender Pedido</span>
                    </a>
                  </li>
                                  <li class="nav-item">
                    <a href="#"
                       class="nav-link submenu-link d-flex align-items-center px-3 py-2 text-light"
                       style="font-size:0.95rem;">
                      <i class="bi bi-box me-2 text-secondary"></i>
                      <span>Pedidos Atendidos</span>
                    </a>
                  </li>
                              </ul>
                      </li>
              </ul>
    </nav>
  </div>
</aside>

<!-- 🔹 Script del menú izquierdo -->
<script src="/entrevecinos/views/js/menu-izquierda.js"></script>

<style>
  /* ✅ Estilo limpio sin scroll feo */
  #sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    overflow: hidden;
    transition: transform 0.3s ease-in-out;
    z-index: 1030;
  }

  #sidebar.active {
    transform: translateX(0);
  }

  /* Oculto en móvil por defecto */
  @media (max-width: 991.98px) {
    #sidebar {
      transform: translateX(-100%);
    }
  }

  /* Scroll elegante interno solo si hay overflow */
  .sidebar-wrapper {
    max-height: calc(100vh - 100px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #0A422D #115C41;
  }

  .sidebar-wrapper::-webkit-scrollbar {
    width: 6px;
  }

  .sidebar-wrapper::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
  }

  /* Hover efecto */
  .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.15);
  }

  /* Submenús activos */
  .submenu-link.active {
    background-color: rgba(255, 255, 255, 0.25);
    font-weight: bold;
  }
</style>
    <!-- 🔹 Contenedor principal -->
    <div class="main-container flex-grow-1 d-flex flex-column" style="min-height: 100vh; overflow: hidden;">

      <!-- 🔹 Barra superior -->
      <nav class="app-header navbar navbar-expand-lg navbar-dark shadow-sm px-3" style="background-color:#0F592F;">
  <div class="container-fluid">
    
    <!-- 🔹 Botón hamburguesa para mostrar/ocultar menú lateral -->
    <button class="navbar-toggler border-0" type="button" id="btnToggleSidebar" aria-label="Mostrar menú">
      <i class="bi bi-list text-white fs-3"></i>
    </button>

    <!-- 🔹 Marca / Logo (solo visible en mobile) -->
    <!-- <a class="navbar-brand ms-2 d-lg-none fw-bold text-white" href="#">
      <img src="<?= BASE_URL ?>resources/images/logo/logo8.png" alt="Logo" style="height:40px;">
    </a>-->

    <!-- 🔹 Contenedor colapsable (solo si agregas otros elementos arriba en el futuro) -->
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

<script>
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebar-backdrop");
  const toggleBtn = document.getElementById("btnToggleSidebar");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("active");
      backdrop.style.display = sidebar.classList.contains("active") ? "block" : "none";
    });
  }

  if (backdrop) {
    backdrop.addEventListener("click", () => {
      sidebar.classList.remove("active");
      backdrop.style.display = "none";
    });
  }
});
</script>
      <!-- 🔹 Contenido dinámico -->
      <main class="content-wrapper fade-in" id="contenido-principal">
        <div class="container-fluid py-4">

          <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0 pb-0">
              <h5 class="mb-0 fw-bold" style="color:#0F592F;">
                <i class="bi bi-house-door"></i> Bienvenido a Entre Vecinos
              </h5>
              <p class="text-muted small mt-1">Selecciona una opción o explora las opciones rápidas a continuación.</p>
            </div>

            <div class="card-body">
              <div class="row justify-content-center g-4 mt-2">

                <!-- 🛒 Tarjeta COMPRAR -->
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                  <a href="#" class="text-decoration-none">
                    <div class="small-box text-center shadow-sm" 
                         style="background-color: #FFF9F0; color: #0F592F; border: 1px solid #E5E7EB;">
                      <div class="p-4">
                        <svg class="mb-3" fill="currentColor" width="48" height="48" viewBox="0 0 24 24">
                          <path d="M3 3a1 1 0 011-1h1.22a1 1 0 01.97.757L6.89 5H21a1 1 0 01.96 1.274l-2 7A1 1 0 0119 14H8.28l-.94 3.764A1 1 0 016.36 19H5a1 1 0 110-2h.64l1.6-6.4L4.28 5H3a1 1 0 01-1-1zM9 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm8 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                        </svg>
                        <h3 class="fw-bold fs-5 mb-1 text-uppercase">COMPRAR</h3>
                        <p class="mb-0 text-muted">Productos y/o Servicios</p>
                      </div>
                    </div>
                  </a>
                </div>

                <!-- 💰 Tarjeta VENDER -->
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                  <a href="#" class="text-decoration-none">
                    <div class="small-box text-center shadow-sm" 
                         style="background-color: #FFF9F0; color: #BF3604; border: 1px solid #E5E7EB;">
                      <div class="p-4">
                        <svg class="mb-3" fill="currentColor" width="48" height="48" viewBox="0 0 24 24">
                          <text x="0" y="18" font-size="18" font-family="Arial, sans-serif" opacity="0.5">S/.</text>
                          <text x="0.3" y="18" font-size="18" font-family="Arial, sans-serif">S/.</text>
                        </svg>
                        <h3 class="fw-bold fs-5 mb-1 text-uppercase">VENDER</h3>
                        <p class="mb-0 text-muted">Productos y/o Servicios</p>
                      </div>
                    </div>
                  </a>
                </div>

              </div>

              <div class="text-center mt-5">
                <img src="<?= BASE_URL ?>resources/images/logo/logo8.png" alt="Logo Entre Vecinos" class="img-fluid" style="max-height: 200px;">
              </div>

            </div>
          </div>

        </div>
      </main>

    </div>
  </div>

  <!-- Backdrop para el menú lateral en móvil -->
  <div id="sidebar-backdrop"></div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="<?= BASE_URL ?>views/js/menu-izquierda.js"></script>
  <script src="<?= BASE_URL ?>views/js/menuArriba.js"></script>

  <script>
    
  </script>
</body>
</html>