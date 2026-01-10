<?php
require_once __DIR__ . '/../Config/config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// $usuario viene desde MenuPrincipalController
$nombreUsuario = htmlspecialchars($usuario['nombre'] ?? 'Vecino(a)', ENT_QUOTES, 'UTF-8');
$rolUsuario    = htmlspecialchars($usuario['rol'] ?? 'vecino', ENT_QUOTES, 'UTF-8');

// Preferir el que manda el controller
$menusParaMenuIzquierda = $menusParaMenuIzquierda ?? ($menus ?? []);

// base href para rutas profundas
$baseHref = rtrim(BASE_URL, '/') . '/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entre Vecinos - Inicio</title>

  <!-- FIX RAÍZ: evita que los assets se rompan en rutas profundas -->
  <base href="<?= htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8'); ?>">

  <?php include_once __DIR__ . '/libreria/libreria.php'; ?>

  <?php include_once __DIR__ . '/estilos.view.php'; ?>
  <?php include_once __DIR__ . '/estilos/menuPrincipalEstilo.php'; ?>
  <?php include_once __DIR__ . '/estilos/menuArribaEstilo.php'; ?>
</head>

<body class="hold-transition">
  <div class="wrapper d-flex">

    <?php include __DIR__ . '/menuIzquierdaView.php'; ?>

    <div class="main-container flex-grow-1 d-flex flex-column">
      <?php include __DIR__ . '/menuArribaView.php'; ?>

      <main class="content-wrapper fade-in" id="contenido-principal">
        <?php include __DIR__ . '/menuPrincipalContenido.php'; ?>
      </main>
    </div>

  </div>

  <div id="sidebar-backdrop"></div>

  <?php include_once __DIR__ . '/scripts/menuPrincipalScripts.php'; ?>
</body>
</html>
