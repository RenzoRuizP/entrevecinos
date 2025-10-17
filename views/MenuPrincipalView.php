<?php
require_once __DIR__ . '/../Config/config.php';
$menusParaMenuIzquierda = $menus ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Entre Vecinos - Menú Principal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#22c55e">
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>resources/images/logo/logo8.png">
   <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- ✅ Estilos generales -->
  <?php include_once __DIR__ . '/estilos.view.php'; ?>
  <?php include_once __DIR__ . '/estilos/menuPrincipalEstilo.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">

    <!-- 🔹 Menú Superior -->
    <?php include __DIR__ . '/menuArribaView.php'; ?>

    <!-- 🔹 Menú Izquierdo -->
    <?php include __DIR__ . '/menuIzquierdaView.php'; ?>

    <!-- 🔹 Contenido principal -->
    <?php include __DIR__ . '/menuPrincipalContenido.php'; ?>

    <!-- 🔹 Footer -->
    <?php include __DIR__ . '/pieView.php'; ?>

  </div>
  <!-- Backdrop para móvil -->
  <div id="sidebar-backdrop"></div>

  <!-- ✅ Scripts -->
  <?php include_once __DIR__ . '/scripts/menuPrincipalScripts.php'; ?>
</body>
</html>
