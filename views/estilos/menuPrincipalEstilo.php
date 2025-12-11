<style>
/* =========================================
   ENTRE VECINOS - HOME DASHBOARD (Optimizado)
   Versión final limpia y unificada
========================================= */

/* Fondo del dashboard */
.ev-home-dashboard {
  background-color: #F3F4F6; /* gris suave */
}

/* Estilo base de todas las cards (coherente con login) */
.ev-home-dashboard .card {
  border-radius: 18px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
  background-color: #FFFFFF;
}


/* -------------------------------------------------
   CARD DE BIENVENIDA
-------------------------------------------------- */

.ev-home-dashboard .card:first-of-type {
  border: 0 !important;
  box-shadow: none !important; /* bienvenida más ligera */
}

.ev-home-dashboard .card:first-of-type .d-flex {
  justify-content: space-between;
  align-items: center;
}

.ev-home-dashboard h3.fw-bold {
  font-size: 1.55rem;
  letter-spacing: 0.3px;
  color: #0F592F;
}

.ev-home-dashboard .card:first-of-type p {
  font-size: 0.97rem;
  color: #6B7280;
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

/* Contenedor donde el JS pinta las tarjetas */
.ev-home-dashboard .ev-destacadas-wrapper {
  display: flex;
  gap: 1rem;
  padding: 0.25rem 0 0.5rem 0;
  overflow-x: auto;
  scroll-behavior: smooth;
}

.ev-home-dashboard .ev-destacadas-wrapper::-webkit-scrollbar {
  height: 6px;
}

.ev-home-dashboard .ev-destacadas-wrapper::-webkit-scrollbar-track {
  background: transparent;
}

.ev-home-dashboard .ev-destacadas-wrapper::-webkit-scrollbar-thumb {
  background: #D1D5DB;
  border-radius: 999px;
}

/* Tarjeta individual destacada */
.ev-home-dashboard .ev-card-destacada {
  min-width: 220px;
  max-width: 260px;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.ev-home-dashboard .ev-card-destacada:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
}

/* Imagen de portada */
.ev-home-dashboard .ev-card-destacada-img {
  position: relative;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  overflow: hidden;
}

.ev-home-dashboard .ev-card-destacada-img img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  display: block;
}

/* Badge "Destacado" */
.ev-home-dashboard .ev-card-destacada-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: #F97316;
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 600;
  padding: 0.18rem 0.6rem;
  border-radius: 999px;
  box-shadow: 0 4px 10px rgba(249, 115, 22, 0.45);
}

/* Cuerpo de la tarjeta */
.ev-home-dashboard .ev-card-destacada-body {
  padding: 0.65rem 0.85rem 0.85rem 0.85rem;
}

.ev-home-dashboard .ev-card-destacada-title {
  font-size: 0.92rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.25rem;
  line-height: 1.3;
}

.ev-home-dashboard .ev-card-destacada-price {
  font-size: 0.9rem;
  font-weight: 700;
  color: #0F592F;
}

/* Carrusel tipo Bootstrap (compatible) */
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
  border: 1px solid #E5E7EB;
  padding: 1.5rem !important;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

/* Fondos */
.ev-home-dashboard .card-accion-comprar {
  background-color: #FFF9F0;
}

.ev-home-dashboard .card-accion-vender {
  background-color: #FFF7F5;
}

/* Botones (unificados con estilo EV) */
.ev-home-dashboard .card-accion-comprar .btn,
.ev-home-dashboard .card-accion-vender .btn {
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  padding: 0.55rem 1.4rem;
}

/* Botón COMPRAR */
.ev-home-dashboard .btn-ev-comprar {
  background: linear-gradient(135deg, #0F592F, #166534);
  border-color: #0F592F;
  color: #FFFFFF;
}

.ev-home-dashboard .btn-ev-comprar:hover {
  background: linear-gradient(135deg, #0b4122, #14532d);
  border-color: #0b4122;
  color: #FFFFFF;
}

/* Botón VENDER (coherente con btn-login) */
.ev-home-dashboard .btn-ev-vender {
  background: linear-gradient(135deg, #D97706, #EA7C12);
  border-color: #D97706;
  color: #FFFFFF;
}

.ev-home-dashboard .btn-ev-vender:hover {
  background: linear-gradient(135deg, #C46B05, #D46F0F);
  border-color: #C46B05;
  color: #FFFFFF;
}

/* Títulos */
.ev-home-dashboard .card-accion-comprar h5,
.ev-home-dashboard .card-accion-vender h5 {
  letter-spacing: 0.4px;
  color: #0F592F;
}


/* -------------------------------------------------
   CONSEJOS DE SEGURIDAD
-------------------------------------------------- */

.ev-home-dashboard .card-consejos h6 {
  font-size: 0.95rem;
  letter-spacing: 0.2px;
  color: #0F592F;
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

  /* Carrusel / tarjetas destacadas */
  .ev-home-dashboard .ev-destacadas-wrapper {
    padding-bottom: 0.3rem;
  }

  .ev-home-dashboard .ev-card-destacada {
    min-width: 200px;
  }

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
