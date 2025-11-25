<?php 
require_once __DIR__ . '/../Config/config.php';
?>
<script>
  // Exponer BASE_URL para los fetch del front
  window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>";
</script>

<?php include_once __DIR__ . '/estilos/marketplaceEstilo.php'; ?>

<!-- Contenido principal -->
<div class="content-wrapper ev-mp-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">

            <!-- CONTENEDOR CENTRAL -->
            <div class="ev-mp-container">

                <!-- ENCABEZADO -->
                <div class="card ev-mp-header shadow-sm mb-3">
                    <div class="card-body">
                        <div class="ev-mp-header-top">
                            <div>
                                <h1 class="ev-mp-title mb-1">Marketplace</h1>
                                <p class="ev-mp-subtitle mb-0">
                                    Compra y vende productos y servicios con tus vecinos, sin salir de casa.
                                </p>
                            </div>
                        </div>

                        <!-- Condominio -->
                        <div class="ev-mp-condominio mt-3">
                            <span class="ev-mp-condominio-icon">
                                <i class="bi bi-buildings"></i>
                            </span>
                            <div class="ev-mp-condominio-text">
                                <span class="ev-mp-condominio-label">Condominio actual</span>
                                <span class="ev-mp-condominio-name">
                                    Condominio El Pilar · Torre C
                                </span>
                            </div>
                        </div>

                        <!-- Búsqueda + ordenar / filtros -->
                        <div class="ev-mp-search-row mt-3">
                            <div class="ev-mp-search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input
                                    type="text"
                                    id="mp-buscar"
                                    class="form-control ev-mp-search-input"
                                    placeholder="¿Qué estás buscando hoy? (ej. pollo a la brasa, gas, manicure)">
                            </div>

                            <div class="ev-mp-search-actions">
                                <button type="button" class="btn btn-outline-success ev-mp-btn-filtros" id="mp-btn-filtros">
                                    <i class="bi bi-sliders"></i> Filtros
                                </button>

                                <div class="ev-mp-sort-wrapper">
                                    <span class="ev-mp-sort-label">Ordenar por</span>
                                    <select id="mp-ordenar" class="form-select ev-mp-sort-select">
                                        <option value="recientes">Más recientes</option>
                                        <option value="menor_precio">Menor precio</option>
                                        <option value="mayor_precio">Mayor precio</option>
                                        <option value="mejor_valorados">Mejor valorados</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Chips de categoría -->
                        <div class="ev-mp-chips mt-3">
                            <button class="ev-mp-chip active" data-category="todos">Todos</button>
                            <button class="ev-mp-chip" data-category="productos">Productos</button>
                            <button class="ev-mp-chip" data-category="servicios">Servicios</button>
                            <button class="ev-mp-chip" data-category="alimentos">Alimentos</button>
                            <button class="ev-mp-chip" data-category="mascotas">Mascotas</button>
                            <button class="ev-mp-chip" data-category="hogar">Hogar</button>
                        </div>

                        <!-- Resumen -->
                        <p class="ev-mp-resumen mt-3 mb-0" id="mp-resumen-resultados">
                            Mostrando 6 resultados en El Pilar
                        </p>
                    </div>
                </div>

                <!-- GRID DE PUBLICACIONES -->
                <div class="ev-mp-grid">

                    <!-- CARD 1 – Potenciado / Alimentos -->
                    <article
                        class="ev-mp-card"
                        data-category="alimentos productos"
                        data-titulo="Pollo a la brasa familiar"
                        data-precio="35.00"
                        data-rating="4.8"
                        data-reciente="10"
                    >
                        <div class="ev-mp-card-media">
                            <img src="../assets/img/mk_pollo.jpg" alt="Pollo a la brasa" class="img-fluid">
                            <div class="ev-mp-card-badges">
                                <span class="ev-mp-badge ev-mp-badge-potenciado">Potenciado</span>
                                <span class="ev-mp-badge ev-mp-badge-category">Alimentos</span>
                            </div>
                        </div>
                        <div class="ev-mp-card-body">
                            <h3 class="ev-mp-card-title">Pollo a la brasa familiar</h3>
                            <p class="ev-mp-card-price">S/ 35.00</p>

                            <div class="ev-mp-card-meta">
                                <div class="ev-mp-card-vecino">
                                    <div class="ev-mp-avatar">A</div>
                                    <div>
                                        <span class="ev-mp-vecino-nombre">Ana</span>
                                        <span class="ev-mp-vecino-condominio">
                                            Torre B · a 1 torre de ti
                                        </span>
                                    </div>
                                </div>
                                <div class="ev-mp-card-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <span>4.8</span>
                                    <span class="ev-mp-rating-votos">(23 ventas)</span>
                                </div>
                            </div>

                            <div class="ev-mp-card-actions">
                                <button class="btn btn-outline-success btn-sm ev-mp-btn-detalle">Ver detalle</button>
                                <button class="btn btn-success btn-sm ev-mp-btn-pedir">Pedir ahora</button>
                            </div>
                        </div>
                    </article>

                    <!-- CARD 2 – Electrónica -->
                    <article
                        class="ev-mp-card"
                        data-category="productos electronica"
                        data-titulo="Smartphone Android"
                        data-precio="550.00"
                        data-rating="4.6"
                        data-reciente="8"
                    >
                        <div class="ev-mp-card-media">
                            <img src="../assets/img/mk_smartphone.jpg" alt="Smartphone Android" class="img-fluid">
                            <div class="ev-mp-card-badges">
                                <span class="ev-mp-badge ev-mp-badge-category">Electrónica</span>
                            </div>
                        </div>
                        <div class="ev-mp-card-body">
                            <h3 class="ev-mp-card-title">Smartphone Android</h3>
                            <p class="ev-mp-card-price">S/ 550.00</p>

                            <div class="ev-mp-card-meta">
                                <div class="ev-mp-card-vecino">
                                    <div class="ev-mp-avatar">M</div>
                                    <div>
                                        <span class="ev-mp-vecino-nombre">Marco</span>
                                        <span class="ev-mp-vecino-condominio">
                                            Torre C · a 2 torres de ti
                                        </span>
                                    </div>
                                </div>
                                <div class="ev-mp-card-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <span>4.6</span>
                                    <span class="ev-mp-rating-votos">(12 ventas)</span>
                                </div>
                            </div>

                            <div class="ev-mp-card-actions">
                                <button class="btn btn-outline-success btn-sm ev-mp-btn-detalle">Ver detalle</button>
                                <button class="btn btn-success btn-sm ev-mp-btn-pedir">Pedir ahora</button>
                            </div>
                        </div>
                    </article>

                    <!-- CARD 3 – Servicio -->
                    <article
                        class="ev-mp-card"
                        data-category="servicios"
                        data-titulo="Corte de cabello a domicilio"
                        data-precio="25.00"
                        data-rating="4.9"
                        data-reciente="7"
                    >
                        <div class="ev-mp-card-media">
                            <img src="../assets/img/mk_peluqueria.jpg" alt="Corte de cabello" class="img-fluid">
                            <div class="ev-mp-card-badges">
                                <span class="ev-mp-badge ev-mp-badge-category">Servicio</span>
                            </div>
                        </div>
                        <div class="ev-mp-card-body">
                            <h3 class="ev-mp-card-title">Corte de cabello a domicilio</h3>
                            <p class="ev-mp-card-price">S/ 25.00</p>

                            <div class="ev-mp-card-meta">
                                <div class="ev-mp-card-vecino">
                                    <div class="ev-mp-avatar">J</div>
                                    <div>
                                        <span class="ev-mp-vecino-nombre">Jorge</span>
                                        <span class="ev-mp-vecino-condominio">
                                            Torre A · a 1 torre de ti
                                        </span>
                                    </div>
                                </div>
                                <div class="ev-mp-card-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <span>4.9</span>
                                    <span class="ev-mp-rating-votos">(18 servicios)</span>
                                </div>
                            </div>

                            <div class="ev-mp-card-actions">
                                <button class="btn btn-outline-success btn-sm ev-mp-btn-detalle">Ver detalle</button>
                                <button class="btn btn-success btn-sm ev-mp-btn-pedir">Pedir ahora</button>
                            </div>
                        </div>
                    </article>

                    <!-- CARD 4 – Mascotas -->
                    <article
                        class="ev-mp-card"
                        data-category="mascotas servicios"
                        data-titulo="Paseo de mascotas"
                        data-precio="20.00"
                        data-rating="4.5"
                        data-reciente="6"
                    >
                        <div class="ev-mp-card-media">
                            <img src="../assets/img/mk_mascotas.jpg" alt="Paseo de mascotas" class="img-fluid">
                            <div class="ev-mp-card-badges">
                                <span class="ev-mp-badge ev-mp-badge-category">Mascotas</span>
                            </div>
                        </div>
                        <div class="ev-mp-card-body">
                            <h3 class="ev-mp-card-title">Paseo de mascotas</h3>
                            <p class="ev-mp-card-price">S/ 20.00</p>

                            <div class="ev-mp-card-meta">
                                <div class="ev-mp-card-vecino">
                                    <div class="ev-mp-avatar">L</div>
                                    <div>
                                        <span class="ev-mp-vecino-nombre">Laura</span>
                                        <span class="ev-mp-vecino-condominio">
                                            Torre D · a 3 torres de ti
                                        </span>
                                    </div>
                                </div>
                                <div class="ev-mp-card-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <span>4.5</span>
                                    <span class="ev-mp-rating-votos">(9 servicios)</span>
                                </div>
                            </div>

                            <div class="ev-mp-card-actions">
                                <button class="btn btn-outline-success btn-sm ev-mp-btn-detalle">Ver detalle</button>
                                <button class="btn btn-success btn-sm ev-mp-btn-pedir">Pedir ahora</button>
                            </div>
                        </div>
                    </article>

                    <!-- CARD 5 – Hogar -->
                    <article
                        class="ev-mp-card"
                        data-category="hogar productos"
                        data-titulo="Mesa de centro"
                        data-precio="210.00"
                        data-rating="4.7"
                        data-reciente="5"
                    >
                        <div class="ev-mp-card-media">
                            <img src="../assets/img/mk_mesa.jpg" alt="Mesa de centro" class="img-fluid">
                            <div class="ev-mp-card-badges">
                                <span class="ev-mp-badge ev-mp-badge-category">Hogar</span>
                            </div>
                        </div>
                        <div class="ev-mp-card-body">
                            <h3 class="ev-mp-card-title">Mesa de centro</h3>
                            <p class="ev-mp-card-price">S/ 210.00</p>

                            <div class="ev-mp-card-meta">
                                <div class="ev-mp-card-vecino">
                                    <div class="ev-mp-avatar">R</div>
                                    <div>
                                        <span class="ev-mp-vecino-nombre">Ricardo</span>
                                        <span class="ev-mp-vecino-condominio">
                                            Torre B · a 1 torre de ti
                                        </span>
                                    </div>
                                </div>
                                <div class="ev-mp-card-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <span>4.7</span>
                                    <span class="ev-mp-rating-votos">(15 ventas)</span>
                                </div>
                            </div>

                            <div class="ev-mp-card-actions">
                                <button class="btn btn-outline-success btn-sm ev-mp-btn-detalle">Ver detalle</button>
                                <button class="btn btn-success btn-sm ev-mp-btn-pedir">Pedir ahora</button>
                            </div>
                        </div>
                    </article>

                    <!-- CARD 6 – Alimentos / Nuevo -->
                    <article
                        class="ev-mp-card"
                        data-category="alimentos productos"
                        data-titulo="Torta de cumpleaños"
                        data-precio="50.00"
                        data-rating="4.9"
                        data-reciente="9"
                    >
                        <div class="ev-mp-card-media">
                            <img src="../assets/img/mk_torta.jpg" alt="Torta de cumpleaños" class="img-fluid">
                            <div class="ev-mp-card-badges">
                                <span class="ev-mp-badge ev-mp-badge-nuevo">Nuevo</span>
                                <span class="ev-mp-badge ev-mp-badge-category">Alimentos</span>
                            </div>
                        </div>
                        <div class="ev-mp-card-body">
                            <h3 class="ev-mp-card-title">Torta de cumpleaños</h3>
                            <p class="ev-mp-card-price">S/ 50.00</p>

                            <div class="ev-mp-card-meta">
                                <div class="ev-mp-card-vecino">
                                    <div class="ev-mp-avatar">L</div>
                                    <div>
                                        <span class="ev-mp-vecino-nombre">Lauro</span>
                                        <span class="ev-mp-vecino-condominio">
                                            Torre B · a 1 torre de ti
                                        </span>
                                    </div>
                                </div>
                                <div class="ev-mp-card-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <span>4.9</span>
                                    <span class="ev-mp-rating-votos">(32 ventas)</span>
                                </div>
                            </div>

                            <div class="ev-mp-card-actions">
                                <button class="btn btn-outline-success btn-sm ev-mp-btn-detalle">Ver detalle</button>
                                <button class="btn btn-success btn-sm ev-mp-btn-pedir">Pedir ahora</button>
                            </div>
                        </div>
                    </article>

                </div>

                <!-- Estado vacío -->
                <div class="ev-mp-empty text-center d-none" id="mp-empty-state">
                    <div class="ev-mp-empty-icon mb-3">
                        <i class="bi bi-search"></i>
                    </div>
                    <h3>No encontramos publicaciones con esos filtros</h3>
                    <p>Prueba ajustando la búsqueda o cambiando de categoría.</p>
                </div>

            </div> <!-- /.ev-mp-container -->

        </div>
    </section>
</div>
