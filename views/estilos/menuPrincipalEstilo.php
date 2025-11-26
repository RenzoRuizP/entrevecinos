<style>
/* =========================================
   ENTRE VECINOS - HOME DASHBOARD (Optimizado)
   Versión final limpia y unificada
========================================= */

/* Fondo del dashboard */
.ev-home-dashboard {
  background-color: #F3F4F6; /* gris suave */
}

/* Estilo base de todas las cards */
.ev-home-dashboard .card {
  border-radius: 18px;
  border: 0;
  box-shadow: 0 10px 30px rgba(15, 89, 47, 0.06);
}


/* -------------------------------------------------
   CARD DE BIENVENIDA
-------------------------------------------------- */

.ev-home-dashboard .card:first-of-type {
  border: 0 !important;
}

.ev-home-dashboard .card:first-of-type .d-flex {
  justify-content: space-between;
  align-items: center;
}

.ev-home-dashboard h3.fw-bold {
  font-size: 1.55rem;
  letter-spacing: 0.3px;
}

.ev-home-dashboard .card:first-of-type p {
  font-size: 0.97rem;
}

/* Logo circular */
.ev-home-dashboard .card:first-of-type .rounded-circle {
  width: 110px;
  height: 110px;
  background: #FFF7F2;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 18px rgba(0, 0, 0, 0.04);
}


/* -------------------------------------------------
   PUBLICACIONES DESTACADAS (CARRUSEL)
-------------------------------------------------- */

.ev-home-dashboard #destacadosCarousel .carousel-inner {
  padding: 4px 0;
}

.ev-home-dashboard #destacadosCarousel .carousel-item {
  padding-bottom: 12px;
}

.ev-home-dashboard #destacadosCarousel .rounded-4 {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.ev-home-dashboard #destacadosCarousel .rounded-4:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(15, 89, 47, 0.10);
}

.ev-home-dashboard #destacadosCarousel h6 {
  font-size: 0.92rem;
}

.ev-home-dashboard #destacadosCarousel p {
  font-size: 0.9rem;
  margin-bottom: 0.3rem;
}

.ev-home-dashboard #destacadosCarousel .badge {
  font-size: 0.68rem;
  padding: 0.22rem 0.7rem;
}

/* Indicadores tipo puntitos */
.ev-home-dashboard .ev-carousel-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #D1D5DB;
}

.ev-home-dashboard .ev-carousel-dot--active {
  background: rgba(15, 89, 47, 0.65);
}

/* Texto debajo del carrusel */
.ev-home-dashboard .card p.text-muted.small {
  font-size: 0.8rem;
}

/* Flecha del título */
.ev-home-dashboard .bi-chevron-right {
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.ev-home-dashboard .bi-chevron-right:hover {
  opacity: 0.7;
}


/* -------------------------------------------------
   ACCIONES RÁPIDAS: COMPRAR / VENDER
-------------------------------------------------- */

.ev-home-dashboard .card-accion-comprar,
.ev-home-dashboard .card-accion-vender {
  border-radius: 18px;
  border: 0;
  padding: 1.5rem !important;
}

/* Fondos */
.ev-home-dashboard .card-accion-comprar {
  background-color: #FFF9F0;
}

.ev-home-dashboard .card-accion-vender {
  background-color: #FFF7F5;
}

/* Botones */
.ev-home-dashboard .card-accion-comprar .btn,
.ev-home-dashboard .card-accion-vender .btn {
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  padding: 0.55rem 1.4rem;
}

/* Botón COMPRAR */
.ev-home-dashboard .btn-ev-comprar {
  background-color: #0F592F;
  border-color: #0F592F;
}

.ev-home-dashboard .btn-ev-comprar:hover {
  background-color: #0b4122;
  border-color: #0b4122;
}

/* Botón VENDER */
.ev-home-dashboard .btn-ev-vender {
  background-color: #F97316;
  border-color: #F97316;
}

.ev-home-dashboard .btn-ev-vender:hover {
  background-color: #dd5e06;
  border-color: #dd5e06;
}

/* Títulos */
.ev-home-dashboard .card-accion-comprar h5,
.ev-home-dashboard .card-accion-vender h5 {
  letter-spacing: 0.4px;
}


/* -------------------------------------------------
   CONSEJOS DE SEGURIDAD
-------------------------------------------------- */

.ev-home-dashboard .card-consejos h6 {
  font-size: 0.95rem;
  letter-spacing: 0.2px;
}

.ev-home-dashboard .card-consejos ul li {
  margin-bottom: 0.25rem;
}


/* -------------------------------------------------
   RESPONSIVE OPTIMIZADO
-------------------------------------------------- */

@media (max-width: 991.98px) {

  .ev-home-dashboard .card {
    border-radius: 16px;
  }

  /* Bienvenida */
  .ev-home-dashboard .card:first-of-type .d-flex {
    flex-direction: column;
    align-items: flex-start !important;
  }

  .ev-home-dashboard .card:first-of-type .rounded-circle {
    margin-top: 1rem;
  }

  /* Carrusel */
  .ev-home-dashboard #destacadosCarousel .d-flex.gap-3 {
    justify-content: flex-start;
    overflow-x: auto;
    padding-bottom: 4px;
  }

  .ev-home-dashboard #destacadosCarousel .rounded-4 {
    min-width: 170px;
    flex-shrink: 0;
  }
}

@media (max-width: 575.98px) {
  .ev-home-dashboard {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }

  .ev-home-dashboard h3.fw-bold {
    font-size: 1.35rem;
  }
}
</style>
