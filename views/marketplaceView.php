<?php 
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/User.php';

// ==========================================
// 1) Valores por defecto (fallback)
// ==========================================
$condominioNombre      = 'Tu condominio';
$condominioNombreCorto = 'Tu condominio';
$torreNombre           = null;
$condominioTextoChip   = 'Tu condominio';

try {
    // ==========================================
    // 2) Leer JWT desde cookie auth_token
    // ==========================================
    if (!empty($_COOKIE['auth_token'])) {
        $datosToken = SesionJWT::verificarToken($_COOKIE['auth_token']);

        if (is_array($datosToken) && !empty($datosToken)) {
            $emailUsuario = $datosToken['email'] ?? null;

            // ==========================================
            // 3) Si tenemos email, buscamos datos completos
            //    usando User::DatosUsuario($email)
            // ==========================================
            if ($emailUsuario) {
                $userModel    = new User();
                $datosUsuario = $userModel->DatosUsuario($emailUsuario);

                if ($datosUsuario) {
                    // nombre_condominio viene del SELECT de DatosUsuario()
                    if (!empty($datosUsuario['nombre_condominio'])) {
                        $condominioNombre = $datosUsuario['nombre_condominio'];

                        // Versión corta sin prefijo "Condominio "
                        $condominioNombreCorto = preg_replace(
                            '/^Condominio\s+/i',
                            '',
                            $condominioNombre
                        );
                    }

                    // nombre_torre viene como nombre_torre
                    if (!empty($datosUsuario['nombre_torre'])) {
                        $torreNombre = $datosUsuario['nombre_torre'];
                    }

                    // Texto que se muestra en el chip (parte superior derecha)
                    if ($torreNombre) {
                        $condominioTextoChip = sprintf(
                            '%s · Torre %s',
                            $condominioNombre,
                            $torreNombre
                        );
                    } else {
                        $condominioTextoChip = $condominioNombre;
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    // Si algo falla con token o consulta, mantenemos los defaults
    $condominioNombre      = 'Tu condominio';
    $condominioNombreCorto = 'Tu condominio';
    $condominioTextoChip   = 'Tu condominio';
}

?>
<script>
  // Exponer BASE_URL y datos de condominio para el front
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
  window.EV_CONDOMINIO_TEXTO  = <?= json_encode($condominioTextoChip,  JSON_UNESCAPED_UNICODE); ?>;
  window.EV_CONDOMINIO_NOMBRE = <?= json_encode($condominioNombreCorto, JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php include_once __DIR__ . '/estilos/marketplaceEstilo.php'; ?>

<!-- IMPORTANTE: aquí NO usamos content-wrapper.
     El main ya es .content-wrapper en menuPrincipalView.php -->
<div class="container-fluid py-4 ev-mp-wrapper">

  <!-- =======================================
       ENCABEZADO MARKETPLACE
  ======================================== -->
  <div class="card ev-mp-header mb-3">
    <div class="card-body">

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
          <h2 class="ev-mp-title mb-1">Marketplace</h2>
          <p class="ev-mp-subtitle mb-0">
            Compra y vende productos y servicios con tus vecinos, sin salir de casa.
          </p>
        </div>

        <!-- Condominio actual -->
        <div class="ev-mp-condominio mt-3 mt-md-0">
          <div class="ev-mp-condominio-icon">
            <i class="bi bi-buildings"></i>
          </div>
          <div class="ev-mp-condominio-text">
            <span class="ev-mp-condominio-label">Condominio actual</span>
            <span class="ev-mp-condominio-name">
              <?= htmlspecialchars($condominioTextoChip, ENT_QUOTES, 'UTF-8'); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Buscador + filtros -->
      <div class="ev-mp-search-row">
        <!-- Buscador -->
        <div class="ev-mp-search-input-wrapper">
          <i class="bi bi-search"></i>
          <input
            type="text"
            class="form-control ev-mp-search-input"
            id="mp_busqueda"
            placeholder="¿Qué estás buscando hoy? (ej. pollo a la brasa, gas, manicure)"
          >
        </div>

        <!-- Filtros y orden -->
        <div class="ev-mp-search-actions">
          <button type="button" class="btn btn-outline-success ev-mp-btn-filtros" id="mp_btn_filtros">
            <i class="bi bi-sliders"></i> Filtros
          </button>

          <div class="ev-mp-sort-wrapper">
            <span class="ev-mp-sort-label">Ordenar por</span>
            <select class="form-select ev-mp-sort-select" id="mp_orden">
              <option value="recientes">Más recientes</option>
              <option value="precio_menor">Precio: menor a mayor</option>
              <option value="precio_mayor">Precio: mayor a menor</option>
              <option value="mejor_valorados">Mejor valorados</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Chips de categorías -->
      <div class="ev-mp-chips">
        <button type="button" class="ev-mp-chip active" data-filtro="todos">Todos</button>
        <!-- luego agregaremos aquí la pestaña Recomendados -->
        <button type="button" class="ev-mp-chip" data-filtro="productos">Productos</button>
        <button type="button" class="ev-mp-chip" data-filtro="servicios">Servicios</button>
        <button type="button" class="ev-mp-chip" data-filtro="alimentos">Alimentos</button>
        <button type="button" class="ev-mp-chip" data-filtro="mascotas">Mascotas</button>
        <button type="button" class="ev-mp-chip" data-filtro="hogar">Hogar</button>
      </div>

      <!-- Resumen -->
      <p class="ev-mp-resumen mb-0" id="mp_resumen_resultados">
        Mostrando 0 resultados en <?= htmlspecialchars($condominioNombreCorto, ENT_QUOTES, 'UTF-8'); ?>
      </p>
    </div>
  </div>
  <!-- /card header -->

  <!-- Estado vacío / mensaje general -->
  <div id="mp_empty_state" class="text-center text-muted mt-3" style="display:none;">
    No se encontraron publicaciones publicadas en este momento.
  </div>

  <!-- =======================================
       GRID DE PUBLICACIONES
       (se rellena dinámicamente por marketplace.js)
  ======================================== -->
  <div class="ev-mp-grid" id="mp_grid_publicaciones">
    <!-- Aquí el JS inyecta las .ev-mp-card según las publicaciones visibles -->
  </div><!-- /.ev-mp-grid -->

</div><!-- /.container-fluid -->
