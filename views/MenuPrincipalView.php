<?php 
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// 🔹 Evitar que el navegador use versiones en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// 🔹 Validar token (redirige si no hay sesión)
$usuario = AuthMiddleware::validarToken();
if (!$usuario) {
    header("Location: " . BASE_URL . "views/login.php?error=sesion_expirada");
    exit;
}

// 🔹 Obtener nombre o rol si deseas mostrarlo
$nombreUsuario = htmlspecialchars($usuario['nombre'] ?? 'Vecino(a)');
$rolUsuario    = htmlspecialchars($usuario['rol'] ?? 'vecino');

$menusParaMenuIzquierda = $menus ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entre Vecinos - Inicio</title>

  <!-- 🔹 Estilos base -->
  <?php include_once __DIR__ . '/libreria/libreria.php'; ?>

  <!-- 🔹 Estilos personalizados -->
  <?php include_once __DIR__ . '/estilos.view.php'; ?>
  <?php include_once __DIR__ . '/estilos/menuPrincipalEstilo.php'; ?>
  <?php include_once __DIR__ . '/estilos/menuArribaEstilo.php'; ?>
</head>

<body class="hold-transition">
  <div class="wrapper d-flex">

    <!-- 🔹 MENÚ IZQUIERDO -->
    <?php include __DIR__ . '/menuIzquierdaView.php'; ?>

    <!-- 🔹 CONTENEDOR PRINCIPAL -->
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

  <!-- 🔹 Scripts -->
  <?php include_once __DIR__ . '/scripts/menuPrincipalScripts.php'; ?>
</body>
</html>
