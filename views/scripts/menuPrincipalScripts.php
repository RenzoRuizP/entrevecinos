<?php
// views/scripts/menuPrincipalScripts.php
// Carga scripts globales + scripts por rol para evitar ejecutar JS en vistas donde no aplica.

if (!isset($rolUsuario)) {
  // Fallback por si algún flujo incluye este archivo sin definir $rolUsuario
  $rolUsuario = 'vecino';
}

// Normalizar
$rolUsuario = strtolower(trim((string)$rolUsuario));
$baseUrl = rtrim(BASE_URL, '/');
?>

<!-- ✅ Librerías externas -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- ✅ BASE URL -->
<script>window.BASE_URL = "<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>";</script>

<!-- ✅ Scripts globales (SIEMPRE) -->
<script src="<?= $baseUrl ?>/views/js/menuIzquierda.js"></script>
<script src="<?= $baseUrl ?>/views/js/menuArriba.js"></script>
<script src="<?= $baseUrl ?>/views/js/menuPrincipal.js"></script>

<?php if ($rolUsuario === 'soporte'): ?>

  <!-- ✅ Scripts específicos del rol SOPORTE -->
  <script src="<?= $baseUrl ?>/views/js/atenderRecargas.js"></script>
  <script src="<?= $baseUrl ?>/views/js/atenderPublicacion.js"></script>
  <script src="<?= $baseUrl ?>/views/js/atenderCuentasUsuario.js"></script>
  <script src="<?= $baseUrl ?>/views/js/notificacionesResidencia.js"></script>

  <!-- ✅ Dashboard soporte -->
  <script src="<?= $baseUrl ?>/views/js/soporteDashboard.js"></script>

<?php else: ?>

  <!-- ✅ Scripts del rol VECINO/ADMIN (módulos generales) -->
  <script src="<?= $baseUrl ?>/views/js/combo_condominio.js"></script>
  <script src="<?= $baseUrl ?>/views/js/datosPersonales.js"></script>
  <script src="<?= $baseUrl ?>/views/js/producto.js"></script>
  <script src="<?= $baseUrl ?>/views/js/combo_tipo.js"></script>
  <script src="<?= $baseUrl ?>/views/js/marketplace.js"></script>
  <script src="<?= $baseUrl ?>/views/js/billetera.js"></script>
  <script src="<?= $baseUrl ?>/views/js/publicacionPublicarWallet.js"></script>
  <script src="<?= $baseUrl ?>/views/js/menuPrincipalContenido.js"></script>
  <script src="<?= $baseUrl ?>/views/js/publicacionDestacar.js"></script>
  <script src="<?= $baseUrl ?>/views/js/credenciales.js"></script>
  <script src="<?= $baseUrl ?>/views/js/recibirPedidos.js"></script>
  <script src="<?= $baseUrl ?>/views/js/pedidosEntrantes.js"></script>

<?php endif; ?>
