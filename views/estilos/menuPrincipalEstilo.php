<style>
/* =========================================
   ENTRE VECINOS - HOME DASHBOARD + LAYOUT
   Unificado con estilo del Login
========================================= */

:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#0E7A43;
  --ev-verde-suave:#16A34A;
  
  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro: #C46B05;

  --ev-gris-fondo:#F3F4F6;
  --ev-gris-borde:#E5E7EB;
  --ev-texto:#1A1F36;
  --ev-texto-suave:#6B7280;

  --ev-radius:18px;
  --ev-radius-sm:12px;

  --ev-shadow:0 12px 40px rgba(15, 23, 42, 0.08);
  --ev-shadow-soft:0 10px 30px rgba(15, 23, 42, 0.08);

  --ev-topbar-h:56px;
  --ev-sidebar-w:260px;
}

/* -----------------------------
   LAYOUT BASE (clave para alinear)
------------------------------ */
body{
  background: var(--ev-gris-fondo);
}

.wrapper{
  min-height: 100vh;
}

/* =========================================================
   FIX DE RAÍZ:
   Tu DOM ya usa wrapper d-flex (sidebar + main-container).
   Por lo tanto, NO se debe sumar margin-left en .main-container,
   porque eso duplica el espacio del sidebar.
========================================================= */
.main-container{
  /* ❌ antes: margin-left: var(--ev-sidebar-w); */
  margin-left: 0;                 /* ✅ no duplicar espacio */
  padding-top: var(--ev-topbar-h);
  min-height: 100vh;
  overflow-x: hidden;

  /* ✅ importante en layouts flex: permite que el contenido no “empuje” */
  min-width: 0;
}

/* en móvil el contenido ocupa todo y sidebar es offcanvas */
@media (max-width: 991.98px){
  .main-container{
    margin-left: 0;
    padding-top: var(--ev-topbar-h);
    min-width: 0;
  }
}

/* -----------------------------
   Dashboard
------------------------------ */
.ev-home-dashboard{
  background-color: var(--ev-gris-fondo);
}

/* Cards base */
.ev-home-dashboard .card{
  border-radius: var(--ev-radius);
  border: 1px solid var(--ev-gris-borde);
  box-shadow: var(--ev-shadow);
  background-color: #fff;
}

/* Bienvenida más ligera */
.ev-home-dashboard .card:first-of-type{
  border: 0 !important;
  box-shadow: none !important;
}

.ev-home-dashboard .card:first-of-type .d-flex{
  justify-content: space-between;
  align-items: center;
}

.ev-home-dashboard h3.fw-bold{
  font-size: 1.55rem;
  letter-spacing: 0.3px;
  color: var(--ev-verde-oscuro);
}

.ev-home-dashboard .card:first-of-type p{
  font-size: 0.97rem;
  color: var(--ev-texto-suave);
}

/* Logo circular */
.ev-home-dashboard .card:first-of-type .rounded-circle{
  width: 110px;
  height: 110px;
  background: #FFF7F2;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 18px rgba(0, 0, 0, 0.04);
}

/* -----------------------------
   Destacadas wrapper
------------------------------ */
.ev-home-dashboard .ev-destacadas-wrapper{
  display:flex;
  gap:1rem;
  padding:.25rem 0 .5rem 0;
  overflow-x:auto;
  scroll-behavior:smooth;
}
.ev-home-dashboard .ev-destacadas-wrapper::-webkit-scrollbar{ height:6px; }
.ev-home-dashboard .ev-destacadas-wrapper::-webkit-scrollbar-track{ background:transparent; }
.ev-home-dashboard .ev-destacadas-wrapper::-webkit-scrollbar-thumb{
  background:#D1D5DB;
  border-radius:999px;
}

/* Card destacada */
.ev-home-dashboard .ev-card-destacada{
  min-width:220px;
  max-width:260px;
  background:#fff;
  border-radius: 16px;
  border:1px solid var(--ev-gris-borde);
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
  display:flex;
  flex-direction:column;
  cursor:pointer;
  transition: transform .18s ease, box-shadow .18s ease;
}
.ev-home-dashboard .ev-card-destacada:hover{
  transform: translateY(-3px);
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
}

.ev-home-dashboard .ev-card-destacada-img{
  position:relative;
  border-top-left-radius:16px;
  border-top-right-radius:16px;
  overflow:hidden;
}
.ev-home-dashboard .ev-card-destacada-img img{
  width:100%;
  height:150px;
  object-fit:cover;
  display:block;
}
.ev-home-dashboard .ev-card-destacada-badge{
  position:absolute;
  top:10px;
  left:10px;
  background:#F97316;
  color:#fff;
  font-size:.68rem;
  font-weight:700;
  padding:.18rem .6rem;
  border-radius:999px;
  box-shadow:0 4px 10px rgba(249, 115, 22, 0.45);
}
.ev-home-dashboard .ev-card-destacada-body{
  padding:.65rem .85rem .85rem .85rem;
}
.ev-home-dashboard .ev-card-destacada-title{
  font-size:.92rem;
  font-weight:700;
  color:#111827;
  margin-bottom:.25rem;
  line-height:1.3;
}
.ev-home-dashboard .ev-card-destacada-price{
  font-size:.9rem;
  font-weight:800;
  color: var(--ev-verde-oscuro);
}

/* -----------------------------
   Acciones rápidas
------------------------------ */
.ev-home-dashboard .card-accion-comprar,
.ev-home-dashboard .card-accion-vender{
  border-radius: var(--ev-radius);
  border: 1px solid var(--ev-gris-borde);
  padding: 1.5rem !important;
  box-shadow: var(--ev-shadow-soft);
}

.ev-home-dashboard .card-accion-comprar{ background-color:#FFF9F0; }
.ev-home-dashboard .card-accion-vender{ background-color:#FFF7F5; }

.ev-home-dashboard .card-accion-comprar .btn,
.ev-home-dashboard .card-accion-vender .btn{
  border-radius: var(--ev-radius-sm);
  font-size: .9rem;
  font-weight: 700;
  padding: .55rem 1.4rem;
}

.ev-home-dashboard .btn-ev-comprar{
  background: linear-gradient(135deg, var(--ev-verde-oscuro), #166534);
  border-color: var(--ev-verde-oscuro);
  color:#fff;
}
.ev-home-dashboard .btn-ev-comprar:hover{
  background: linear-gradient(135deg, #0b4122, #14532d);
  border-color: #0b4122;
  color:#fff;
}

.ev-home-dashboard .btn-ev-vender{
  background: linear-gradient(135deg, #D97706, #EA7C12);
  border-color: #D97706;
  color:#fff;
}
.ev-home-dashboard .btn-ev-vender:hover{
  background: linear-gradient(135deg, #C46B05, #D46F0F);
  border-color: #C46B05;
  color:#fff;
}

.ev-home-dashboard .card-accion-comprar h5,
.ev-home-dashboard .card-accion-vender h5{
  letter-spacing:.35px;
  color: var(--ev-verde-oscuro);
}

/* -----------------------------
   Consejos
------------------------------ */
.ev-home-dashboard .card-consejos h6{
  font-size:.95rem;
  letter-spacing:.2px;
  color: var(--ev-verde-oscuro);
}
.ev-home-dashboard .card-consejos ul li{ margin-bottom:.25rem; }

/* -----------------------------
   Responsive
------------------------------ */
@media (max-width: 991.98px){
  .ev-home-dashboard .card{ border-radius: 16px; }

  .ev-home-dashboard .card:first-of-type .d-flex{
    flex-direction: column;
    align-items: flex-start !important;
  }

  .ev-home-dashboard .card:first-of-type .rounded-circle{
    margin-top: 1rem;
  }

  .ev-home-dashboard .ev-card-destacada{ min-width: 200px; }
}

@media (max-width: 575.98px){
  .ev-home-dashboard{
    padding-left: .75rem;
    padding-right: .75rem;
  }

  .ev-home-dashboard h3.fw-bold{ font-size: 1.35rem; }
}
</style>
