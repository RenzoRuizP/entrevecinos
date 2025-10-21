
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entre Vecinos - Panel Principal</title>
    <!-- Librerías -->
    <?php include_once __DIR__ . '/libreria/libreria.php'; ?>
    <!-- ✅ Estilos generales -->
    <?php include_once __DIR__ . '/estilos.view.php'; ?>
    <?php include_once __DIR__ . '/estilos/menuPrincipalEstilo.php'; ?>

</head>

<body class="hold-transition">
    <div class="wrapper">
        <!-- 🔹 Menú Superior -->
        <?php include __DIR__ . '/menuArribaView.php'; ?>

        <!-- 🔹 Menú Izquierdo -->
        <?php include __DIR__ . '/menuIzquierdaView.php'; ?>
        
        <!-- 🔹 Menú Superior -->
        <?php include_once __DIR__ . '/scripts/menuPrincipalScripts.php'; ?>
        <!-- 🔹 Contenido principal -->
        <?php include_once __DIR__ . '/menuPrincipalContenido.php'; ?>
      
    </div>

    <!-- Backdrop para el menú lateral en móvil -->
    <div id="sidebar-backdrop"></div>
    
</body>

</html>