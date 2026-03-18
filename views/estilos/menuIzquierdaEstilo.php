<style>
/* ============================================================
   ENTRE VECINOS - SIDEBAR (mismo degradado del Login HERO)
   + Hover naranja EV para menú y submenú
============================================================ */

:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde-mid:#0E7A43;
  --ev-verde:#16A34A;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;

  --ev-topbar-h:56px;
  --ev-sidebar-w:260px;

  --ev-radius:18px;
}

/* Sidebar: ocupa toda la altura y se alinea con topbar */
#sidebar.app-sidebar{
  width: var(--ev-sidebar-w);
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 1030;

  /* MISMO degradado del login-hero */
  background:
    radial-gradient(circle at 75% 30%, rgba(255,255,255,0.08), transparent 60%),
    radial-gradient(circle at 20% 80%, rgba(0,0,0,0.08), transparent 70%),
    linear-gradient(145deg, #0F592F 0%, #0E7A43 45%, #16A34A 85%);
    

  color: rgba(255,255,255,.92);

  box-shadow: 10px 0 24px rgba(0,0,0,.10);
  overflow: hidden;
  transition: transform .26s ease-in-out;
}

/* Overlay extra como el hero */
#sidebar.app-sidebar::before{
  content:"";
  position:absolute;
  inset:0;
  background: radial-gradient(circle at 85% 85%, rgba(187,247,208,0.18) 0, transparent 60%);
  opacity:.95;
  pointer-events:none;
}

#sidebar.app-sidebar > *{
  position: relative;
  z-index: 2;
}

/* Brand: misma altura de topbar para que no se vea “franja blanca” */
#sidebar .sidebar-brand{
  height: var(--ev-topbar-h);
  display:flex;
  align-items:center;
  justify-content:center;

  border-bottom: 1px solid rgba(255,255,255,.14);
  background: rgba(255,255,255,.04);
  backdrop-filter: blur(6px);
}

/* Wrapper scroll (debajo de brand) */
.sidebar-wrapper{
  max-height: calc(100vh - var(--ev-topbar-h));
  overflow-y: auto;
  padding: .65rem .35rem .85rem .35rem;

  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,.25) rgba(255,255,255,.08);
}
.sidebar-wrapper::-webkit-scrollbar{ width: 6px; }
.sidebar-wrapper::-webkit-scrollbar-thumb{
  background-color: rgba(255,255,255,.22);
  border-radius: 999px;
}

/* -----------------------------
   Links principales
------------------------------ */
.app-sidebar .nav-link{
  color: rgba(255,255,255,.92) !important;
  font-weight: 700;
  border-radius: 14px;
  margin: .18rem .5rem;
  transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
}

.app-sidebar .nav-link .nav-icon,
.app-sidebar .nav-link i{
  color: rgba(255,255,255,.92) !important;
  transition: color .18s ease;
}

/* Hover naranja (lo que pediste) */
.app-sidebar .nav-link:hover{
  background: linear-gradient(135deg, rgba(234,124,18,.95), rgba(245,158,11,.92));
  color: #fff !important;
  transform: translateX(2px);
  box-shadow: 0 12px 26px rgba(234,124,18,0.28);
}
.app-sidebar .nav-link:hover .nav-icon,
.app-sidebar .nav-link:hover i{
  color:#fff !important;
}

/* Activo principal */
.app-sidebar .nav-link.active-menu{
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  color: #fff !important;
  box-shadow: 0 12px 26px rgba(234,124,18,0.30);
}
.app-sidebar .nav-link.active-menu .nav-icon,
.app-sidebar .nav-link.active-menu i{
  color:#fff !important;
}

/* Flecha */
.app-sidebar .bi-chevron-down{
  transition: transform .22s ease;
  opacity: .95;
}
.app-sidebar .nav-link[aria-expanded="true"] .bi-chevron-down{
  transform: rotate(180deg);
}

/* -----------------------------
   Submenús
------------------------------ */
.nav-treeview{
  margin: .15rem .55rem .45rem .55rem;
  padding: .35rem .35rem .25rem .35rem;
  border-radius: 14px;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.10);
}

.nav-treeview .nav-link{
  font-weight: 600;
  font-size: .94rem;
  margin: .14rem .25rem;
  border-radius: 12px;
  color: rgba(255,255,255,.90) !important;
}

/* Hover naranja en submenú */
.nav-treeview .nav-link:hover{
  background: linear-gradient(135deg, rgba(234,124,18,.92), rgba(245,158,11,.88));
  color:#fff !important;
  box-shadow: 0 10px 22px rgba(234,124,18,0.22);
}

/* Submenu activo */
.nav-treeview .nav-link.submenu-active,
.submenu-link.active{
  background: rgba(255,255,255,.16);
  border: 1px solid rgba(255,255,255,.22);
  font-weight: 800;
}

/* -----------------------------
   Móvil: offcanvas
------------------------------ */
@media (max-width: 991.98px){
  #sidebar.app-sidebar{
    transform: translateX(-105%);
  }
  #sidebar.active{
    transform: translateX(0);
  }
}

/* Backdrop */
#sidebar-backdrop{
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(2px);
  z-index: 1025;
  opacity: 0;
  pointer-events: none;
  transition: opacity .2s ease;
}
#sidebar-backdrop.show,
#sidebar-backdrop.active{
  opacity: 1;
  pointer-events: auto;
}

@media (max-width: 991.98px){
  body{ overscroll-behavior: contain; }
}

</style>
