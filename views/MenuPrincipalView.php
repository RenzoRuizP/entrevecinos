<?php
require_once __DIR__ . '/../Config/config.php';
?>

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
  <?php include __DIR__ . '/estilos.view.php'; ?>
</head>

<body class="hold-transition">
  <div class="wrapper">

    <!-- 🔹 Menú Izquierdo -->
    <?php include __DIR__ . '/menuIzquierdaView.php'; ?>

    <!-- 🔹 Contenedor principal -->
    <div class="main-container flex-grow-1 d-flex flex-column" style="min-height: 100vh; overflow: hidden;">

      <!-- 🔹 Barra superior -->
      <?php include __DIR__ . '/menuArribaView.php'; ?>

      <!-- 🔹 Contenido dinámico -->
      <main class="content-wrapper fade-in" id="contenido-principal">
        <div class="container-fluid py-4">
          <div class="card shadow-sm">
            <div class="card-header">
              <h5 class="mb-0"><i class="bi bi-house-door"></i> Bienvenido a Entre Vecinos</h5>
            </div>
            <div class="card-body">
              <p class="mb-3">Selecciona una opción del menú izquierdo para comenzar.</p>
              <div class="text-center">
                <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo Entre Vecinos" class="img-fluid" style="max-height: 180px;">
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
  <script src="<?= BASE_URL ?>/views/js/menu-izquierda.js"></script>

  <script>
    // 🔹 Sincroniza el botón hamburguesa del topbar con el menú lateral
    document.addEventListener("DOMContentLoaded", () => {
      const sidebar = document.querySelector(".main-sidebar");
      const toggleBtn = document.getElementById("toggle-sidebar");
      const backdrop = document.getElementById("sidebar-backdrop");

      if (!sidebar || !toggleBtn) return;

      const toggleSidebar = () => {
        sidebar.classList.toggle("active");
        backdrop.classList.toggle("active");
      };

      toggleBtn.addEventListener("click", toggleSidebar);
      backdrop.addEventListener("click", toggleSidebar);
    });
  </script>
</body>
</html>
