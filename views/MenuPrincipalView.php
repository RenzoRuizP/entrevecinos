<?php
require_once __DIR__ . '/../Config/config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// $usuario viene desde MenuPrincipalController
$usuario = $usuario ?? [];

// Rol crudo para lógica
$rolUsuarioRaw = strtolower(trim((string)($usuario['rol'] ?? 'vecino')));

// Variables escapadas para UI
$nombreUsuario = htmlspecialchars($usuario['nombre'] ?? 'Vecino(a)', ENT_QUOTES, 'UTF-8');
$rolUsuario    = htmlspecialchars($rolUsuarioRaw, ENT_QUOTES, 'UTF-8');

// Preferir el que manda el controller
$menusParaMenuIzquierda = $menusParaMenuIzquierda ?? ($menus ?? []);

// base href para rutas profundas
$baseHref = rtrim(BASE_URL, '/') . '/';

$baseUrl = rtrim(BASE_URL, '/');

/**
 * Normaliza ev_goto para evitar que el shell /MenuPrincipal
 * se cargue dentro de sí mismo como vista parcial.
 *
 * Caso problemático corregido:
 * /MenuPrincipal?ev_goto=%2FMenuPrincipal
 */
$evGotoRaw = trim((string)($_GET['ev_goto'] ?? ''));
$evGoto = $evGotoRaw;

if ($evGoto !== '') {
  $evGoto = rawurldecode($evGoto);
  $evGoto = explode('?', $evGoto, 2)[0];

  if ($evGoto === '' || $evGoto[0] !== '/') {
    $evGoto = '/' . $evGoto;
  }

  $basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');

  if ($basePath !== '' && $basePath !== '/') {
    if (stripos($evGoto, $basePath . '/') === 0) {
      $evGoto = substr($evGoto, strlen($basePath));
      if ($evGoto === '') {
        $evGoto = '/';
      }
    }
  }

  $evGoto = preg_replace('#/+#', '/', $evGoto);
  $evGoto = rtrim($evGoto, '/');

  if ($evGoto === '' || $evGoto === '/') {
    $evGoto = '/MenuPrincipal';
  }

  if (strcasecmp($evGoto, '/MenuPrincipal') === 0) {
    header('Location: ' . rtrim(BASE_URL, '/') . '/MenuPrincipal', true, 302);
    exit;
  }
}

// cache bust por filemtime
function ev_ver($pathAbs) {
  $t = @filemtime($pathAbs);
  return $t ? (string)$t : (string)time();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="icon" type="image/png" href="<?= rtrim(BASE_URL, '/') ?>/resources/images/logo/logo_ev_transparente_corregido_recortado.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entre Vecinos - Inicio</title>

  <base href="<?= htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8'); ?>">

  <?php include_once __DIR__ . '/libreria/libreria.php'; ?>

  <?php include_once __DIR__ . '/estilos.view.php'; ?>
  <?php include_once __DIR__ . '/estilos/menuPrincipalEstilo.php'; ?>
  <?php include_once __DIR__ . '/estilos/menuArribaEstilo.php'; ?>

  <?php if (in_array($rolUsuarioRaw, ['soporte', 'admin'], true)): ?>
    <?php include_once __DIR__ . '/estilos/soporteDashboardEstilo.php'; ?>
  <?php endif; ?>

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
      width: 28px;
      height: 28px;
      border-radius: 50%;
      border: 4px solid rgba(22,163,74,0.18);
      border-top-color: rgba(15,89,47,0.95);
      animation: evShellSpin .8s linear infinite;
    }
    @keyframes evShellSpin {
      to {
        transform: rotate(360deg);
      }
    }

    :root{
      --ev-verde-oscuro:#0F592F;
      --ev-verde:#198754;
      --ev-verde-suave:#E6F4EC;
      --ev-naranja:#EA7C12;
      --ev-naranja-oscuro:#C46B05;
      --ev-texto:#1A1F36;
      --ev-texto-suave:#6B7280;
      --ev-borde:#E5E7EB;
    }

    .swal2-container.ev-mp-swal-container{
      backdrop-filter: blur(3px);
    }

    .ev-mp-swal-popup{
      width: min(92vw, 560px) !important;
      border-radius: 26px !important;
      padding: 28px 24px 22px !important;
      border: 1px solid rgba(229,231,235,.96) !important;
      box-shadow:
        0 30px 70px rgba(15,23,42,.22),
        0 10px 24px rgba(15,23,42,.10) !important;
      background:
        radial-gradient(circle at top, rgba(230,244,236,.60) 0%, rgba(255,255,255,1) 28%),
        #fff !important;
      overflow: hidden !important;
    }

    .ev-mp-swal-popup::before{
      content:'';
      position:absolute;
      top:0;
      left:0;
      right:0;
      height:7px;
      background:linear-gradient(90deg, #0F592F 0%, #198754 55%, #EA7C12 100%);
    }

    .ev-mp-swal-title{
      color: var(--ev-verde-oscuro) !important;
      font-weight: 800 !important;
      letter-spacing: -.03em !important;
      font-size: clamp(1.85rem, 2.8vw, 2.35rem) !important;
      line-height: 1.05 !important;
      margin-top: 0 !important;
      margin-bottom: 8px !important;
    }

    .ev-mp-swal-html{
      color: var(--ev-texto-suave) !important;
      font-size: 1rem !important;
      line-height: 1.58 !important;
      margin-top: 0 !important;
    }

    .ev-mp-swal-confirm{
      background: linear-gradient(135deg, var(--ev-naranja), #F59E0B) !important;
      border: none !important;
      color: #fff !important;
      border-radius: 14px !important;
      padding: 13px 24px !important;
      min-width: 160px !important;
      font-weight: 800 !important;
      font-size: .98rem !important;
      box-shadow: 0 14px 28px rgba(234,124,18,.34) !important;
      transition: transform .16s ease, box-shadow .16s ease, filter .16s ease !important;
    }

    .ev-mp-swal-confirm:hover{
      transform: translateY(-1px) !important;
      filter: brightness(1.03) !important;
      box-shadow: 0 18px 36px rgba(234,124,18,.42) !important;
    }

    .ev-mp-swal-cancel{
      background: #fff !important;
      border: 1.6px solid #EF4444 !important;
      color: #EF4444 !important;
      border-radius: 14px !important;
      padding: 13px 24px !important;
      min-width: 190px !important;
      font-weight: 800 !important;
      font-size: .98rem !important;
      box-shadow: 0 8px 20px rgba(239,68,68,.06) !important;
      transition: transform .16s ease, background .16s ease, box-shadow .16s ease !important;
    }

    .ev-mp-swal-cancel:hover{
      background: #FEF2F2 !important;
      transform: translateY(-1px) !important;
      box-shadow: 0 12px 24px rgba(239,68,68,.12) !important;
    }

    .ev-mp-swal-loader{
      width: 62px;
      height: 62px;
      border-radius: 50%;
      border: 5px solid rgba(22,163,74,.16);
      border-top-color: rgba(15,89,47,.96);
      margin: 4px auto 18px auto;
      animation: evMpSpin .85s linear infinite;
    }

    @keyframes evMpSpin{
      to{
        transform: rotate(360deg);
      }
    }

    .ev-mp-swal-status-icon{
      width: 92px;
      height: 92px;
      border-radius: 50%;
      margin: 2px auto 18px auto;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      box-shadow: inset 0 0 0 1px rgba(15,89,47,.06);
    }

    .ev-mp-swal-status-icon--success{
      background: radial-gradient(circle at 30% 30%, rgba(230,244,236,.95), rgba(230,244,236,.70));
      border: 2px solid rgba(132,204,22,.26);
    }

    .ev-mp-swal-status-icon--info{
      background: radial-gradient(circle at 30% 30%, rgba(239,246,255,.96), rgba(224,242,254,.78));
      border: 2px solid rgba(56,189,248,.32);
    }

    .ev-mp-swal-status-icon svg{
      width: 48px;
      height: 48px;
      display: block;
    }

    .ev-mp-swal-subtitle{
      font-weight: 800;
      font-size: 1.14rem;
      color: var(--ev-verde-oscuro);
      margin-bottom: 10px;
      letter-spacing: -.01em;
    }

    .ev-mp-swal-soft-text{
      font-size: .97rem;
      color: var(--ev-texto-suave);
      line-height: 1.62;
      max-width: 420px;
      margin: 0 auto;
    }

    .ev-mp-swal-timer-wrap{
      margin-top: 16px;
      margin-bottom: 14px;
    }

    .ev-mp-swal-timer-pill{
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      min-height: 44px;
      padding: 11px 18px;
      border-radius: 999px;
      background: linear-gradient(135deg, #E8F7EE, #DFF2E7);
      color: #0F592F;
      font-weight: 800;
      font-size: 14px;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
    }

    .ev-mp-swal-product{
      margin-top: 6px;
      font-size: .96rem;
      color: var(--ev-texto-suave);
      line-height: 1.5;
    }

    .ev-mp-swal-product strong{
      color: var(--ev-texto);
      font-weight: 800;
    }

    .ev-mp-swal-cancel-hint{
      margin-top: 10px;
      font-size: .93rem;
      color: var(--ev-texto-suave);
      line-height: 1.45;
      min-height: 24px;
    }

    .ev-mp-swal-note{
      margin-top: 16px;
      padding: 15px 16px;
      border-radius: 16px;
      background: linear-gradient(180deg, #FFF8F1 0%, #FFF4E8 100%);
      border: 1px solid rgba(234,124,18,.22);
      color: #B45309;
      font-size: .95rem;
      line-height: 1.55;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.65);
    }

    .ev-mp-swal-note strong{
      font-weight: 800;
    }

    .ev-mp-swal-actions-gap .swal2-actions{
      margin-top: 20px !important;
      gap: 12px !important;
    }

    .ev-mp-swal-bounce{
      animation: evMpBounceModal .34s ease;
      transform-origin: center center;
    }

    @keyframes evMpBounceModal{
      0%   { transform: scale(1); }
      22%  { transform: scale(.975); }
      52%  { transform: scale(1.018); }
      72%  { transform: scale(.992); }
      100% { transform: scale(1); }
    }

    @media (max-width: 575.98px){
      .ev-mp-swal-popup{
        width: min(94vw, 94vw) !important;
        padding: 22px 16px 18px !important;
        border-radius: 22px !important;
      }

      .ev-mp-swal-title{
        font-size: 1.78rem !important;
      }

      .ev-mp-swal-status-icon{
        width: 82px;
        height: 82px;
        margin-bottom: 16px;
      }

      .ev-mp-swal-status-icon svg{
        width: 42px;
        height: 42px;
      }

      .ev-mp-swal-soft-text{
        font-size: .95rem;
      }

      .ev-mp-swal-confirm,
      .ev-mp-swal-cancel{
        width: 100% !important;
        min-width: 0 !important;
      }

      .ev-mp-swal-actions-gap .swal2-actions{
        width: 100% !important;
        flex-direction: column-reverse !important;
      }
    }

    .swal2-popup .swal2-confirm:not(.ev-mp-swal-confirm){
      background: linear-gradient(135deg, var(--ev-naranja), #F59E0B) !important;
      border: none !important;
      color: #fff !important;
      border-radius: 14px !important;
      padding: 12px 22px !important;
      font-weight: 800 !important;
      box-shadow: 0 14px 28px rgba(234,124,18,.28) !important;
    }

    .swal2-popup .swal2-confirm:not(.ev-mp-swal-confirm):hover{
      filter: brightness(1.03) !important;
      transform: translateY(-1px) !important;
    }

    .swal2-popup .swal2-cancel:not(.ev-mp-swal-cancel){
      background: #fff !important;
      color: #6B7280 !important;
      border: 1px solid #D1D5DB !important;
      border-radius: 14px !important;
      padding: 12px 22px !important;
      font-weight: 800 !important;
    }
  </style>
</head>

<body class="hold-transition">
  <div class="wrapper d-flex">

    <?php include __DIR__ . '/menuIzquierdaView.php'; ?>

    <div class="main-container flex-grow-1 d-flex flex-column">
      <?php include __DIR__ . '/menuArribaView.php'; ?>

      <main class="content-wrapper fade-in" id="contenido-principal">

        <?php if ($evGoto !== ''): ?>

          <div class="ev-shell-loading" aria-busy="true" aria-live="polite">
            <div class="ev-box">
              <div class="ev-spin" aria-hidden="true"></div>
              <div>Cargando módulo...</div>
            </div>
          </div>

        <?php else: ?>

          <?php
            if (in_array($rolUsuarioRaw, ['soporte', 'admin'], true)) {
              /*
               * Admin del sistema y Soporte operan sobre un entorno administrativo.
               * Admin deja de cargar el dashboard comercial del vecino.
               * Posteriormente podrá construirse un dashboard exclusivo del superadministrador.
               */
              include __DIR__ . '/soporteDashboardView.php';

            } elseif ($rolUsuarioRaw === 'administrador_comunidad') {
              /*
               * La cuenta institucional de la comunidad inicia directamente
               * en la gestión de comunicados, noticias y eventos.
               */
              include __DIR__ . '/comunidadGestionView.php';

            } else {
              /*
               * El rol vecino mantiene intacto su dashboard comprador/vendedor.
               */
              include __DIR__ . '/menuPrincipalContenido.php';
            }
          ?>

        <?php endif; ?>

      </main>
    </div>

  </div>

  <div id="sidebar-backdrop"></div>

  <script>
    window.EV_CONFIG = Object.freeze({
      environment: <?php echo json_encode(defined('EV_APP_ENV') ? EV_APP_ENV : 'production'); ?>,
      baseUrl: <?php echo json_encode(rtrim(BASE_URL, '/')); ?>,
      appVersion: <?php echo json_encode(defined('EV_APP_VER') ? EV_APP_VER : '1.0.0'); ?>
    });
    window.BASE_URL = window.EV_CONFIG.baseUrl;
    window.EV_BASE_URL = window.EV_CONFIG.baseUrl;
    window.EV_ROL_USUARIO = <?php echo json_encode($rolUsuarioRaw); ?>;
  </script>

  <?php include_once __DIR__ . '/scripts/menuPrincipalScripts.php'; ?>

</body>
</html>