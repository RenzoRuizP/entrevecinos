<?php 
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  // Exponer BASE_URL para los fetch del front
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
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
            <!-- Aquí puedes imprimir dinámicamente el condominio actual -->
            <span class="ev-mp-condominio-name">
              Condominio El Pilar · Torre C
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
        <button type="button" class="ev-mp-chip" data-filtro="productos">Productos</button>
        <button type="button" class="ev-mp-chip" data-filtro="servicios">Servicios</button>
        <button type="button" class="ev-mp-chip" data-filtro="alimentos">Alimentos</button>
        <button type="button" class="ev-mp-chip" data-filtro="mascotas">Mascotas</button>
        <button type="button" class="ev-mp-chip" data-filtro="hogar">Hogar</button>
      </div>

      <!-- Resumen -->
      <p class="ev-mp-resumen mb-0" id="mp_resumen_resultados">
        Mostrando 6 resultados en El Pilar
      </p>
    </div>
  </div>
  <!-- /card header -->

  <!-- =======================================
       GRID DE PUBLICACIONES
  ======================================== -->
  <div class="ev-mp-grid" id="mp_grid_publicaciones">

    <!-- CARD EJEMPLO 1 -->
    <div class="ev-mp-card" data-tipo="producto" data-categoria="alimentos">
      <div class="ev-mp-card-media">
        <img src="assets/img/demo/pollo_brasa.jpg" alt="Pollo a la brasa familiar">
        <div class="ev-mp-card-badges">
          <span class="ev-mp-badge ev-mp-badge-potenciado">Potenciado</span>
          <span class="ev-mp-badge ev-mp-badge-category">Alimentos</span>
        </div>
      </div>
      <div class="ev-mp-card-body">
        <h5 class="ev-mp-card-title">Pollo a la brasa familiar</h5>
        <p class="ev-mp-card-price">S/ 35.00</p>

        <div class="ev-mp-card-meta">
          <div class="ev-mp-card-vecino">
            <div class="ev-mp-avatar">A</div>
            <div>
              <div class="ev-mp-vecino-nombre">Ana</div>
              <div class="ev-mp-vecino-condominio">Torre B · a 1 torre de ti</div>
            </div>
          </div>
          <div class="ev-mp-card-rating">
            <i class="bi bi-star-fill"></i>
            <span>4.8</span>
            <span class="ev-mp-rating-votos">(23 ventas)</span>
          </div>
        </div>

        <div class="ev-mp-card-actions">
          <button type="button" class="btn btn-outline-success ev-mp-btn-detalle">
            Ver detalle
          </button>
          <button type="button" class="btn btn-success ev-mp-btn-pedir">
            Pedir ahora
          </button>
        </div>
      </div>
    </div>

    <!-- CARD EJEMPLO 2 -->
    <div class="ev-mp-card" data-tipo="producto" data-categoria="electronica">
      <div class="ev-mp-card-media">
        <img src="assets/img/demo/smartphone.jpg" alt="Smartphone Android">
        <div class="ev-mp-card-badges">
          <span class="ev-mp-badge ev-mp-badge-category">Electrónica</span>
        </div>
      </div>
      <div class="ev-mp-card-body">
        <h5 class="ev-mp-card-title">Smartphone Android</h5>
        <p class="ev-mp-card-price">S/ 550.00</p>

        <div class="ev-mp-card-meta">
          <div class="ev-mp-card-vecino">
            <div class="ev-mp-avatar">M</div>
            <div>
              <div class="ev-mp-vecino-nombre">Marco</div>
              <div class="ev-mp-vecino-condominio">Torre C · a 2 torres de ti</div>
            </div>
          </div>
          <div class="ev-mp-card-rating">
            <i class="bi bi-star-fill"></i>
            <span>4.6</span>
            <span class="ev-mp-rating-votos">(12 ventas)</span>
          </div>
        </div>

        <div class="ev-mp-card-actions">
          <button type="button" class="btn btn-outline-success ev-mp-btn-detalle">
            Ver detalle
          </button>
          <button type="button" class="btn btn-success ev-mp-btn-pedir">
            Pedir ahora
          </button>
        </div>
      </div>
    </div>

    <!-- CARD EJEMPLO 3 -->
    <div class="ev-mp-card" data-tipo="servicio" data-categoria="servicio">
      <div class="ev-mp-card-media">
        <img src="assets/img/demo/corte_cabello.jpg" alt="Corte de cabello a domicilio">
        <div class="ev-mp-card-badges">
          <span class="ev-mp-badge ev-mp-badge-category">Servicio</span>
        </div>
      </div>
      <div class="ev-mp-card-body">
        <h5 class="ev-mp-card-title">Corte de cabello a domicilio</h5>
        <p class="ev-mp-card-price">S/ 25.00</p>

        <div class="ev-mp-card-meta">
          <div class="ev-mp-card-vecino">
            <div class="ev-mp-avatar">J</div>
            <div>
              <div class="ev-mp-vecino-nombre">Jorge</div>
              <div class="ev-mp-vecino-condominio">Torre A · a 1 torre de ti</div>
            </div>
          </div>
          <div class="ev-mp-card-rating">
            <i class="bi bi-star-fill"></i>
            <span>4.9</span>
            <span class="ev-mp-rating-votos">(18 servicios)</span>
          </div>
        </div>

        <div class="ev-mp-card-actions">
          <button type="button" class="btn btn-outline-success ev-mp-btn-detalle">
            Ver detalle
          </button>
          <button type="button" class="btn btn-success ev-mp-btn-pedir">
            Pedir ahora
          </button>
        </div>
      </div>
    </div>

    <!-- CARD EJEMPLO 4 -->
    <div class="ev-mp-card" data-tipo="servicio" data-categoria="mascotas">
      <div class="ev-mp-card-media">
        <img src="assets/img/demo/paseo_mascotas.jpg" alt="Paseo de mascotas">
        <div class="ev-mp-card-badges">
          <span class="ev-mp-badge ev-mp-badge-category">Mascotas</span>
        </div>
      </div>
      <div class="ev-mp-card-body">
        <h5 class="ev-mp-card-title">Paseo de mascotas</h5>
        <p class="ev-mp-card-price">S/ 20.00</p>

        <div class="ev-mp-card-meta">
          <div class="ev-mp-card-vecino">
            <div class="ev-mp-avatar">L</div>
            <div>
              <div class="ev-mp-vecino-nombre">Laura</div>
              <div class="ev-mp-vecino-condominio">Torre D · a 3 torres de ti</div>
            </div>
          </div>
          <div class="ev-mp-card-rating">
            <i class="bi bi-star-fill"></i>
            <span>4.5</span>
            <span class="ev-mp-rating-votos">(9 servicios)</span>
          </div>
        </div>

        <div class="ev-mp-card-actions">
          <button type="button" class="btn btn-outline-success ev-mp-btn-detalle">
            Ver detalle
          </button>
          <button type="button" class="btn btn-success ev-mp-btn-pedir">
            Pedir ahora
          </button>
        </div>
      </div>
    </div>

  </div><!-- /.ev-mp-grid -->

</div><!-- /.container-fluid -->
