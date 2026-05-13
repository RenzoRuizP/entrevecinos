<style>
/* ============================================================
   ENTRE VECINOS - SIDEBAR
   Degradado EV + activo persistente + responsive premium
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

/* Sidebar */
#sidebar.app-sidebar{
  width: var(--ev-sidebar-w);
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 1030;

  background:
    radial-gradient(circle at 75% 30%, rgba(255,255,255,0.08), transparent 60%),
    radial-gradient(circle at 20% 80%, rgba(0,0,0,0.08), transparent 70%),
    linear-gradient(145deg, #0F592F 0%, #0E7A43 45%, #16A34A 85%);

  color: rgba(255,255,255,.92);
  box-shadow: 10px 0 24px rgba(0,0,0,.10);
  overflow: hidden;
  transition:
    transform .28s cubic-bezier(.22,.9,.32,1),
    box-shadow .28s ease;
}

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

/* Brand */
#sidebar .sidebar-brand{
  height: var(--ev-topbar-h);
  display:flex;
  align-items:center;
  justify-content:center;
  border-bottom: 1px solid rgba(255,255,255,.14);
  background: rgba(255,255,255,.04);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

/* Scroll */
.sidebar-wrapper{
  max-height: calc(100vh - var(--ev-topbar-h));
  overflow-y: auto;
  padding: .65rem .35rem .85rem .35rem;
  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,.25) rgba(255,255,255,.08);
}

.sidebar-wrapper::-webkit-scrollbar{
  width: 6px;
}

.sidebar-wrapper::-webkit-scrollbar-thumb{
  background-color: rgba(255,255,255,.22);
  border-radius: 999px;
}

/* ============================================================
   LINKS BASE
============================================================ */

.app-sidebar .nav-link{
  position: relative;
  color: rgba(255,255,255,.92) !important;
  font-weight: 700;
  border-radius: 15px;
  margin: .18rem .5rem;
  min-height: 42px;
  transition:
    background .24s cubic-bezier(.22,.9,.32,1),
    transform .24s cubic-bezier(.22,.9,.32,1),
    box-shadow .24s cubic-bezier(.22,.9,.32,1),
    border-color .24s cubic-bezier(.22,.9,.32,1),
    opacity .24s ease;
}

.app-sidebar .nav-link .nav-icon,
.app-sidebar .nav-link i{
  color: rgba(255,255,255,.92) !important;
  transition:
    color .22s ease,
    transform .22s cubic-bezier(.22,.9,.32,1),
    opacity .22s ease;
}

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

/* ============================================================
   MENÚ PADRE
============================================================ */

.app-sidebar .menu-parent-link{
  border: 1px solid transparent;
  letter-spacing: -.01em;
}

.app-sidebar .menu-parent-link:hover{
  background:
    linear-gradient(135deg, rgba(255,255,255,.13), rgba(255,255,255,.07));
  border-color: rgba(255,255,255,.16);
  color: #fff !important;
  transform: translateX(1px);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.10),
    0 10px 24px rgba(15,23,42,.10);
}

.app-sidebar .menu-parent-link:hover .nav-icon,
.app-sidebar .menu-parent-link:hover i{
  color:#fff !important;
}

.app-sidebar .menu-parent-link.active-parent,
.app-sidebar .menu-parent-link[aria-expanded="true"]{
  background:
    linear-gradient(135deg, rgba(255,255,255,.16), rgba(255,255,255,.075));
  border: 1px solid rgba(255,255,255,.17);
  color: #fff !important;
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.12),
    0 10px 22px rgba(15,23,42,.08);
  transform: translateX(0);
}

.app-sidebar .menu-parent-link.active-parent::before,
.app-sidebar .menu-parent-link[aria-expanded="true"]::before{
  content:"";
  position:absolute;
  left: 9px;
  top: 50%;
  width: 3px;
  height: 18px;
  border-radius: 999px;
  background: rgba(255,255,255,.72);
  transform: translateY(-50%);
  opacity: .9;
}

.app-sidebar .menu-parent-link.active-parent .nav-icon,
.app-sidebar .menu-parent-link.active-parent i,
.app-sidebar .menu-parent-link[aria-expanded="true"] .nav-icon,
.app-sidebar .menu-parent-link[aria-expanded="true"] i{
  color:#fff !important;
}

.app-sidebar .bi-chevron-down{
  transition:
    transform .32s cubic-bezier(.22,.9,.32,1),
    opacity .22s ease;
  opacity: .82;
}

.app-sidebar .nav-link[aria-expanded="true"] .bi-chevron-down,
.app-sidebar .menu-parent-link.active-parent .bi-chevron-down{
  transform: rotate(180deg);
  opacity: 1;
}

.app-sidebar .nav-link.active-menu{
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  color: #fff !important;
  box-shadow: 0 12px 26px rgba(234,124,18,0.30);
}

.app-sidebar .nav-link.active-menu .nav-icon,
.app-sidebar .nav-link.active-menu i{
  color:#fff !important;
}

/* ============================================================
   SUBMENÚ
============================================================ */

.nav-treeview{
  margin: .16rem .55rem .50rem .55rem;
  padding: .34rem .34rem .30rem .34rem;
  border-radius: 16px;

  background:
    linear-gradient(180deg, rgba(255,255,255,.075), rgba(255,255,255,.045));
  border: 1px solid rgba(255,255,255,.10);

  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.08),
    0 10px 20px rgba(15,23,42,.045);

  transform-origin: top center;
}

.nav-treeview.collapse.show{
  opacity: 1;
  transform: translateY(0) scaleY(1);
}

.nav-treeview.collapsing{
  overflow: hidden;
  opacity: .55;
  transform: translateY(-4px) scaleY(.985);
  transition:
    height .34s cubic-bezier(.22,.9,.32,1),
    opacity .24s ease,
    transform .34s cubic-bezier(.22,.9,.32,1);
}

.nav-treeview.collapse:not(.show){
  opacity: 0;
}

.nav-treeview .nav-link{
  font-weight: 600;
  font-size: .94rem;
  margin: .14rem .25rem;
  border-radius: 13px;
  color: rgba(255,255,255,.90) !important;
  min-height: 40px;
  border: 1px solid transparent;
  transition:
    background .22s cubic-bezier(.22,.9,.32,1),
    transform .22s cubic-bezier(.22,.9,.32,1),
    box-shadow .22s cubic-bezier(.22,.9,.32,1),
    border-color .22s ease,
    color .22s ease;
}

.nav-treeview .nav-link:hover{
  background: linear-gradient(135deg, rgba(234,124,18,.92), rgba(245,158,11,.88));
  color:#fff !important;
  box-shadow: 0 10px 22px rgba(234,124,18,0.22);
  transform: translateX(2px);
}

.nav-treeview .nav-link.submenu-active,
.nav-treeview .nav-link.active,
.nav-treeview .submenu-link.submenu-active,
.nav-treeview .submenu-link.active{
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B) !important;
  color: #fff !important;
  border: 1px solid rgba(255,255,255,.22);
  font-weight: 800;
  box-shadow:
    0 12px 24px rgba(234,124,18,.34),
    inset 0 1px 0 rgba(255,255,255,.18);
  transform: translateX(2px);
}

.nav-treeview .nav-link.submenu-active::before,
.nav-treeview .nav-link.active::before,
.nav-treeview .submenu-link.submenu-active::before,
.nav-treeview .submenu-link.active::before{
  content:"";
  position:absolute;
  left: 8px;
  top: 50%;
  width: 4px;
  height: 20px;
  border-radius: 999px;
  background: rgba(255,255,255,.90);
  transform: translateY(-50%);
}

.nav-treeview .nav-link.submenu-active i,
.nav-treeview .nav-link.active i,
.nav-treeview .submenu-link.submenu-active i,
.nav-treeview .submenu-link.active i{
  color:#fff !important;
}

/* ============================================================
   MÓVIL / TABLET
============================================================ */

@media (max-width: 991.98px){
  #sidebar.app-sidebar{
    top: var(--ev-topbar-h);
    left: 0;
    height: calc(100vh - var(--ev-topbar-h));
    width: min(82vw, 286px);
    z-index: 1040;
    transform: translateX(-106%);
    border-top-right-radius: 0;
    border-bottom-right-radius: 24px;
    box-shadow:
      18px 0 42px rgba(15,23,42,.26),
      4px 0 12px rgba(15,23,42,.12);
  }

  #sidebar.active,
  #sidebar.open,
  body.ev-sidebar-open #sidebar.app-sidebar{
    transform: translateX(0);
  }

  #sidebar .sidebar-brand{
    display:none;
  }

  .sidebar-wrapper{
    max-height: calc(100vh - var(--ev-topbar-h));
    padding: .85rem .42rem 1.25rem .42rem;
  }

  .app-sidebar .nav-link{
    min-height: 44px;
    margin: .20rem .45rem;
    border-radius: 15px;
  }

  .nav-treeview{
    margin: .18rem .55rem .60rem .55rem;
  }

  body.ev-sidebar-open{
    overflow:hidden;
    touch-action:none;
  }
}

/* Backdrop */
#sidebar-backdrop{
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.54);
  backdrop-filter: blur(3px);
  -webkit-backdrop-filter: blur(3px);
  z-index: 1035;
  opacity: 0;
  pointer-events: none;
  transition: opacity .22s ease;
}

#sidebar-backdrop.show,
#sidebar-backdrop.active,
body.ev-sidebar-open #sidebar-backdrop{
  opacity: 1;
  pointer-events: auto;
}

@media (min-width: 992px){
  #sidebar-backdrop{
    display:none;
  }
}

@media (max-width: 991.98px){
  body{
    overscroll-behavior: contain;
  }
}
</style>