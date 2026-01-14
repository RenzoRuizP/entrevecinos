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

// deep-link (viene del router): /MenuPrincipal?ev_goto=/mi-perfil
$evGoto = trim((string)($_GET['ev_goto'] ?? ''));
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

  <!-- Placeholder UX (solo cuando hay ev_goto) -->
  <style>
    .ev-shell-loading {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 28px 16px;
      min-height: 240px;
    }
    .ev-shell-loading .ev-box{
      display:flex;
      align-items:center;
      gap:12px;
      padding: 14px 18px;
      border-radius: 999px;
      background: rgba(255,255,255,0.96);
      border: 1px solid rgba(15,89,47,0.10);
      box-shadow: 0 18px 45px rgba(0,0,0,0.10), 0 6px 12px rgba(0,0,0,0.05);
      color: #0F592F;
      font-weight: 600;
      font-family: Poppins, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ev-shell-loading .ev-spin{
      width: 28px; height: 28px;
      border-radius: 50%;
      border: 4px solid rgba(22,163,74,0.18);
      border-top-color: rgba(15,89,47,0.95);
      animation: evShellSpin .8s linear infinite;
    }
    @keyframes evShellSpin { to { transform: rotate(360deg); } }
  </style>
</head>

<body class="hold-transition">
  <div class="wrapper d-flex">

    <?php include __DIR__ . '/menuIzquierdaView.php'; ?>

    <div class="main-container flex-grow-1 d-flex flex-column">
      <?php include __DIR__ . '/menuArribaView.php'; ?>

      <main class="content-wrapper fade-in" id="contenido-principal">
        <?php if ($evGoto !== ''): ?>
          <!-- Estado de carga: evita “pantalla vacía” mientras se carga el parcial via AJAX -->
          <div class="ev-shell-loading" aria-busy="true" aria-live="polite">
            <div class="ev-box">
              <div class="ev-spin" aria-hidden="true"></div>
              <div>Cargando módulo...</div>
            </div>
          </div>
        <?php else: ?>
          <?php include __DIR__ . '/menuPrincipalContenido.php'; ?>
        <?php endif; ?>
      </main>
    </div>

  </div>

  <div id="sidebar-backdrop"></div>

  <?php include_once __DIR__ . '/scripts/menuPrincipalScripts.php'; ?>
</body>
</html>
