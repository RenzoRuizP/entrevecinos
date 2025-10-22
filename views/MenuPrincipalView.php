<?php 
require_once __DIR__ . '/../Config/config.php';
$menusParaMenuIzquierda = $menus ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entre Vecinos - Panel Principal</title>

  <!-- 🔹 Dependencias principales -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" rel="stylesheet">

  <!-- 🔹 Tipografía -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.8/index.css"
    crossorigin="anonymous"
    media="print"
    onload="this.media='all'"
  />

  <!-- 🔹 Estilos base y personalizados -->
  <link rel="stylesheet" href="<?= BASE_URL ?>resources/util/lte4/dist/css/adminlte.css">
  <?php include_once __DIR__ . '/estilos.view.php'; ?>
  <?php include_once __DIR__ . '/estilos/menuPrincipalEstilo.php'; ?>
  <?php include_once __DIR__ . '/estilos/menuArribaEstilo.php'; ?>

</head>

<body class="hold-transition">
  <div class="wrapper">

    <!-- ============================================================
         🔹 MENÚ IZQUIERDO
    ============================================================ -->
    <?php include __DIR__ . '/menuIzquierdaView.php'; ?>


    <!-- ============================================================
         🔹 CONTENEDOR PRINCIPAL
    ============================================================ -->
    <div class="main-container flex-grow-1 d-flex flex-column" style="min-height: 100vh; overflow: hidden;">

      <!-- 🔹 MENÚ SUPERIOR -->
      <?php include __DIR__ . '/menuArribaView.php'; ?>

      <!-- 🔹 CONTENIDO PRINCIPAL -->
      <main class="content-wrapper fade-in" id="contenido-principal">
        <?php include __DIR__ . '/menuPrincipalContenido.php'; ?>
      </main>

    </div>
  </div>

  <!-- 🔹 Fondo oscuro para menú lateral móvil -->
  <div id="sidebar-backdrop"></div>

  <!-- ============================================================
       🔹 SCRIPTS
  ============================================================ -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <?php include_once __DIR__ . '/scripts/menuPrincipalScripts.php'; ?>
  <script src="<?= BASE_URL ?>views/scripts/menuArriba.js"></script>

</body>
</html>
