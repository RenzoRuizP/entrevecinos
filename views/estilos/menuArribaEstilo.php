<style>
/* ============================================================
   ENTRE VECINOS - TOPBAR (mismo degradado del Login HERO)
   + Hover naranja EV
============================================================ */

:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde-mid:#0E7A43;
  --ev-verde:#16A34A;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;

  --ev-gris-borde:#E5E7EB;

  --ev-topbar-h:56px;

  --ev-radius:18px;
  --ev-radius-sm:12px;

  --ev-shadow-soft:0 10px 26px rgba(0,0,0,0.14);
}

/* Navbar base */
.app-header.navbar{
  height: var(--ev-topbar-h);
  color:#fff;

  position: fixed;
  top:0; left:0; right:0;
  z-index: 1050;

  display:flex;
  align-items:center;
  padding: .5rem 1rem;

  /* MISMO STACK que login-hero */
  background:
    radial-gradient(circle at 75% 30%, rgba(255,255,255,0.08), transparent 60%),
    radial-gradient(circle at 20% 80%, rgba(0,0,0,0.08), transparent 70%),
    linear-gradient(145deg, var(--ev-verde-oscuro) 0%, var(--ev-verde-mid) 45%, var(--ev-verde) 85%);

  box-shadow: var(--ev-shadow-soft);
  overflow: visible !important;
}

/* Overlay extra (como ::before del hero) */
.app-header.navbar::before{
  content:"";
  position:absolute;
  inset:0;
  background: radial-gradient(circle at 85% 85%, rgba(187,247,208,0.18) 0, transparent 60%);
  opacity:.95;
  pointer-events:none;
}

.app-header.navbar > .container-fluid{
  position: relative;
  z-index: 2;
}

/* Botón hamburguesa */
#btnToggleSidebar, .navbar-toggler{
  border:none !important;
  background: transparent !important;
  color:#fff !important;
  padding: .35rem .4rem;
  border-radius: 12px;
  line-height:1;
  transition: background .18s ease, transform .18s ease;
}
#btnToggleSidebar:hover, .navbar-toggler:hover{
  background: rgba(255,255,255,.10) !important;
}
#btnToggleSidebar:active, .navbar-toggler:active{
  transform: translateY(0.5px);
}
#btnToggleSidebar:focus, .navbar-toggler:focus{
  outline:none;
  box-shadow: 0 0 0 .18rem rgba(187,247,208,.45);
}

/* Marca */
.app-header .navbar-brand{
  font-weight: 700;
  letter-spacing: .2px;
}

/* Usuario */
.user-menu .nav-link{
  color:#fff !important;
  display:flex;
  align-items:center;
  gap:.55rem;
  padding: .25rem .35rem;
  border-radius: 14px;
  transition: background .18s ease;
}
.user-menu .nav-link:hover{
  background: rgba(255,255,255,.10);
}

.user-menu .nav-link img{
  width: 38px;
  height: 38px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,.92);
}

/* Dropdown */
.user-menu .dropdown-menu{
  border: 1px solid rgba(229,231,235,.85);
  border-radius: 16px;
  box-shadow: 0 18px 45px rgba(0,0,0,0.22), 0 6px 12px rgba(0,0,0,0.12);
  overflow: hidden;
  padding: 0;
  margin-top: .75rem;
}

.user-menu .dropdown-menu li.bg-success{
  /* Header del dropdown con el mismo degradado EV */
  background:
    radial-gradient(circle at 75% 30%, rgba(255,255,255,0.08), transparent 60%),
    radial-gradient(circle at 20% 80%, rgba(0,0,0,0.08), transparent 70%),
    linear-gradient(145deg, var(--ev-verde-oscuro) 0%, var(--ev-verde-mid) 55%, var(--ev-verde) 100%) !important;
  border-bottom: 1px solid rgba(255,255,255,0.20);
}

.user-menu .dropdown-menu img{
  width: 72px;
  height: 72px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,.95);
}

/* Botones dropdown */
.user-menu .btn{
  font-weight: 600;
  border-radius: 999px;
  padding: .45rem .85rem;
}

/* Verde outline */
.user-menu .btn-outline-success{
  border-color: var(--ev-verde);
  color: var(--ev-verde-oscuro);
  background: rgba(255,255,255,0.65);
  transition: all .18s ease;
}
.user-menu .btn-outline-success:hover{
  background: #ECFDF5;
  color: var(--ev-verde);
}

/* Salir (rojo) */
.user-menu .btn-danger{
  background: #BF3604;
  border: none;
  transition: background .18s ease;
}
.user-menu .btn-danger:hover{
  background: #A12E03;
}

/* -----------------------------
   Responsive
------------------------------ */
@media (max-width: 991.98px){
  .app-header.navbar{ padding: .5rem .75rem; }

  .user-menu span{ display:none !important; }

  .user-menu .dropdown-menu{
    position: fixed !important;
    top: calc(var(--ev-topbar-h) + 14px) !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: min(92vw, 420px) !important;
    border-radius: 16px !important;
    z-index: 2000 !important;
  }

  .user-menu .btn{ flex:1; margin: 0 .25rem; }
}

/* Animación dropdown */
.dropdown-menu.show{ animation: evFadeUp .22s ease; }
@keyframes evFadeUp{
  from{ opacity:0; transform: translateY(12px); }
  to{ opacity:1; transform: translateY(0); }
}
</style>
