<?php
// views/scripts/menuPrincipalScripts.php
// Carga scripts globales + scripts por rol
// ✅ Incluye versionado para evitar cache intermitente

if (!isset($rolUsuario)) {
  $rolUsuario = 'vecino';
}

$rolUsuario = strtolower(trim((string)$rolUsuario));
$baseUrl = rtrim(BASE_URL, '/');

// ✅ Versión de app para cache-bust
// - En PROD define en Config/config.php: define('EV_APP_VER','2026.01.20');
// - En LOCAL cae a time()
$evVer = defined('EV_APP_VER') ? (string)EV_APP_VER : (string)time();
?>

<!-- ✅ Librerías externas -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- ✅ BASE URL + APP VER -->
<script>
  window.BASE_URL = "<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>";
  window.EV_APP_VER = "<?= htmlspecialchars($evVer, ENT_QUOTES, 'UTF-8') ?>";
</script>

<!-- ✅ Scripts globales (SIEMPRE) -->
<script src="<?= $baseUrl ?>/views/js/menuIzquierda.js?v=<?= $evVer ?>"></script>
<script src="<?= $baseUrl ?>/views/js/menuArriba.js?v=<?= $evVer ?>"></script>
<script src="<?= $baseUrl ?>/views/js/menuPrincipal.js?v=<?= $evVer ?>"></script>

<?php if ($rolUsuario === 'soporte'): ?>

  <!-- ✅ Scripts específicos del rol SOPORTE -->
  <script src="<?= $baseUrl ?>/views/js/soporteDashboard.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/atenderRecargas.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/atenderPublicacion.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/atenderCuentasUsuario.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/notificacionesResidencia.js?v=<?= $evVer ?>"></script>

<?php else: ?>

  <!-- ✅ Scripts del rol VECINO/ADMIN -->
  <script src="<?= $baseUrl ?>/views/js/combo_condominio.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/datosPersonales.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/producto.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/combo_tipo.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/marketplace.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/billetera.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/publicacionPublicarWallet.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/menuPrincipalContenido.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/publicacionDestacar.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/credenciales.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/recibirPedidos.js?v=<?= $evVer ?>"></script>
  <script src="<?= $baseUrl ?>/views/js/pedidosEntrantes.js?v=<?= $evVer ?>"></script>

<?php endif; ?>
