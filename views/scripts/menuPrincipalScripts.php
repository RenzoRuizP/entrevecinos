<?php
// views/scripts/menuPrincipalScripts.php

if (!defined('BASE_URL')) {
  define('BASE_URL', '/entrevecinos/');
}

if (!isset($rolUsuario)) {
  $rolUsuario = 'vecino';
}

$rolUsuario = strtolower(trim((string)$rolUsuario));
$baseUrl = rtrim(BASE_URL, '/');
$evAppVer = defined('EV_APP_VER') ? (string)EV_APP_VER : '1.0.0';

function ev_js_ver(string $relativePath): string
{
  $relativePath = ltrim($relativePath, '/');
  $fullPath = __DIR__ . '/../' . $relativePath;
  $mtime = @filemtime($fullPath);
  return $mtime ? (string)$mtime : (defined('EV_APP_VER') ? (string)EV_APP_VER : (string)time());
}

function ev_js_src(string $file): string
{
  $baseUrl = rtrim(BASE_URL, '/');
  $file = ltrim($file, '/');
  return $baseUrl . '/views/' . $file . '?v=' . ev_js_ver($file);
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  window.BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  window.EV_BASE_URL = window.BASE_URL;
  window.EV_APP_VER = <?= json_encode($evAppVer, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>

<script src="<?= htmlspecialchars(ev_js_src('js/evSweetAlert.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

<script src="<?= htmlspecialchars(ev_js_src('js/menuIzquierda.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(ev_js_src('js/menuArriba.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(ev_js_src('js/menuPrincipal.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(ev_js_src('js/notificacionesResidencia.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

<?php if ($rolUsuario === 'soporte' || $rolUsuario === 'admin'): ?>

  <script src="<?= htmlspecialchars(ev_js_src('js/soporteDashboard.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/atenderCuentasUsuario.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/atenderRecargas.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/atenderPublicacion.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

<?php else: ?>

  <script src="<?= htmlspecialchars(ev_js_src('js/combo_condominio.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/datosPersonales.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/producto.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/combo_tipo.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/marketplace.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/billetera.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/publicacionPublicarWallet.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/menuPrincipalContenido.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/publicacionDestacar.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/credenciales.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

  <!-- /recibir ya no se usa: no cargar recibirPedidos.js ni pedidosEntrantes.js -->
  <script src="<?= htmlspecialchars(ev_js_src('js/misPedidosComprador.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/misPedidosVendedor.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <script src="<?= htmlspecialchars(ev_js_src('js/menuPrincipalPedidosAlertas.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

<?php endif; ?>
