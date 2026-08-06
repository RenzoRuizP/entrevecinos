<style>
/* ============================================================
   ENTRE VECINOS - SIDEBAR ULTRA FINO V3
   Desktop preservado + drawer móvil sincronizado con topbar
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
.app-header.navbar{
  border-bottom:0 !important;
  box-shadow:0 8px 22px rgba(15,23,42,.10) !important;
}
#sidebar.app-sidebar{
  width:var(--ev-sidebar-w);
  position:fixed;
  top:0; left:0;
  height:100vh;
  z-index:1030;
  display:flex;
  flex-direction:column;
  color:rgba(255,255,255,.92);
  background:
    radial-gradient(circle at 75% 30%,rgba(255,255,255,.08),transparent 60%),
    radial-gradient(circle at 20% 80%,rgba(0,0,0,.08),transparent 70%),
    linear-gradient(145deg,#0F592F 0%,#0E7A43 45%,#16A34A 85%);
  box-shadow:10px 0 24px rgba(0,0,0,.10);
  overflow-x:hidden;
  overflow-y:auto;
  transition:transform .28s cubic-bezier(.22,.9,.32,1),box-shadow .28s ease;
  scrollbar-width:thin;
  scrollbar-color:rgba(255,255,255,.25) rgba(255,255,255,.08);
}
#sidebar.app-sidebar::-webkit-scrollbar{ width:6px; }
#sidebar.app-sidebar::-webkit-scrollbar-thumb{ background-color:rgba(255,255,255,.22); border-radius:999px; }
#sidebar.app-sidebar::before{
  content:""; position:absolute; inset:0;
  background:radial-gradient(circle at 85% 85%,rgba(187,247,208,.18),transparent 60%);
  opacity:.95; pointer-events:none;
}
#sidebar.app-sidebar > *{ position:relative; z-index:2; }
#sidebar .sidebar-brand{
  flex:0 0 var(--ev-topbar-h); height:var(--ev-topbar-h);
  display:flex; align-items:center; justify-content:flex-start !important;
  border-bottom:0; background:rgba(255,255,255,.035);
  backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px);
  box-shadow:inset 0 -1px 0 rgba(15,89,47,.18);
  padding-left:14px !important;
}
.ev-sidebar-brand-link{ display:inline-flex; align-items:center; gap:10px; color:#fff; }
.ev-sidebar-brand-link:hover{ color:#fff; }
.ev-sidebar-brand-logo{
  width:40px; height:40px; min-width:40px; border-radius:50%;
  display:inline-flex; align-items:center; justify-content:center; overflow:hidden;
  background:radial-gradient(circle at 30% 18%,#fff 0%,rgba(255,255,255,.98) 48%,rgba(243,244,246,.96) 100%);
  box-shadow:0 4px 10px rgba(15,23,42,.14),inset 0 1px 0 rgba(255,255,255,.95);
}
.ev-sidebar-brand-logo img{ width:30px; height:30px; object-fit:contain; display:block; }
.ev-sidebar-brand-text{ color:#fff; font-weight:900; font-size:1.05rem; line-height:1; text-shadow:0 1px 2px rgba(0,0,0,.14); }
.sidebar-wrapper{ flex:0 0 auto; max-height:none; overflow:visible; padding:.70rem .35rem .10rem; }
.app-sidebar .nav-link,.app-sidebar button.nav-link{
  width:calc(100% - 1rem); position:relative; display:flex; align-items:center; text-align:left;
  border:1px solid transparent; outline:0; color:rgba(255,255,255,.94) !important;
  font-weight:800; border-radius:15px; margin:.18rem .5rem; min-height:42px;
  background:transparent; box-shadow:none; cursor:pointer;
  transition:background .2s cubic-bezier(.22,.9,.32,1),transform .2s cubic-bezier(.22,.9,.32,1),box-shadow .2s cubic-bezier(.22,.9,.32,1),border-color .2s ease;
}
.app-sidebar .nav-link .nav-icon,.app-sidebar .nav-link i{ color:rgba(255,255,255,.94) !important; }
.app-sidebar .nav-link:hover,.app-sidebar button.nav-link:hover{
  background:linear-gradient(135deg,rgba(255,255,255,.13),rgba(255,255,255,.07));
  border-color:rgba(255,255,255,.16); color:#fff !important; transform:translateX(1px);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.10),0 10px 24px rgba(15,23,42,.10);
}
.app-sidebar button.nav-link:focus-visible,.app-sidebar a.nav-link:focus-visible{ box-shadow:0 0 0 .18rem rgba(187,247,208,.42); }
.app-sidebar .menu-parent-link.active-parent,.app-sidebar .menu-parent-link.is-open,.app-sidebar .menu-parent-link[aria-expanded="true"]{
  background:linear-gradient(135deg,rgba(255,255,255,.16),rgba(255,255,255,.075));
  border:1px solid rgba(255,255,255,.17); color:#fff !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.12),0 10px 22px rgba(15,23,42,.08);
}
.app-sidebar .menu-parent-link.active-parent::before,.app-sidebar .menu-parent-link.is-open::before,.app-sidebar .menu-parent-link[aria-expanded="true"]::before{
  content:""; position:absolute; left:9px; top:50%; width:3px; height:18px; border-radius:999px;
  background:rgba(255,255,255,.72); transform:translateY(-50%);
}
.app-sidebar .bi-chevron-down{ margin-left:auto; transition:transform .2s cubic-bezier(.22,.9,.32,1); opacity:.86; }
.app-sidebar .menu-parent-link.is-open .bi-chevron-down,.app-sidebar .menu-parent-link[aria-expanded="true"] .bi-chevron-down{ transform:rotate(180deg); opacity:1; }
.nav-treeview.ev-menu-group{
  display:block; max-height:0; overflow:hidden; opacity:0;
  margin:.04rem .55rem; padding:0 .34rem; border-radius:16px;
  background:linear-gradient(180deg,rgba(255,255,255,.075),rgba(255,255,255,.045));
  border:1px solid transparent; pointer-events:none; transform:translateY(-2px);
  transition:max-height .12s ease-out,opacity .10s ease,transform .12s ease,margin .12s ease,padding .12s ease,border-color .12s ease;
}
.nav-treeview.ev-menu-group.is-open{
  max-height:760px; opacity:1; margin:.16rem .55rem .5rem; padding:.34rem .34rem .30rem;
  border-color:rgba(255,255,255,.10); pointer-events:auto; transform:translateY(0);
}
.nav-treeview .nav-link{
  width:calc(100% - .5rem); font-weight:850; font-size:.94rem; margin:.14rem .25rem;
  border-radius:13px; min-height:40px; border:1px solid transparent; background:transparent;
}
.nav-treeview .nav-link:hover{
  background:linear-gradient(135deg,rgba(234,124,18,.92),rgba(245,158,11,.88));
  color:#fff !important; box-shadow:0 10px 22px rgba(234,124,18,.22); transform:translateX(2px);
}
.nav-treeview .nav-link.submenu-active,.nav-treeview .nav-link.active{
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B) !important;
  color:#fff !important; border:1px solid rgba(255,255,255,.22); font-weight:900;
  box-shadow:0 12px 24px rgba(234,124,18,.34),inset 0 1px 0 rgba(255,255,255,.18);
  transform:translateX(2px);
}
.ev-sidebar-footer{ flex:0 0 auto; margin:10px 10px 16px; padding-top:14px; border-top:1px solid rgba(255,255,255,.17); }
.ev-sidebar-footer-link{
  width:100%; min-height:40px; display:flex; align-items:center; gap:10px;
  border:1px solid transparent; background:transparent; color:rgba(255,255,255,.94);
  border-radius:14px; padding:9px 12px; margin:0 0 5px; font-weight:900; text-align:left;
  transition:background .2s ease,transform .2s ease,box-shadow .2s ease,border-color .18s ease;
}
.ev-sidebar-footer-link i{ width:18px; text-align:center; color:rgba(255,255,255,.92); }
.ev-sidebar-footer-link:hover{
  background:linear-gradient(135deg,rgba(234,124,18,.96),rgba(245,158,11,.90));
  color:#fff; border-color:rgba(255,255,255,.22); transform:translateX(2px);
  box-shadow:0 12px 24px rgba(234,124,18,.28),inset 0 1px 0 rgba(255,255,255,.18);
}
.ev-sidebar-community-card{
  margin-top:10px; padding:14px 12px 13px; border-radius:20px; text-align:center;
  background:radial-gradient(circle at 25% 10%,rgba(255,255,255,.20),transparent 44%),linear-gradient(145deg,rgba(255,255,255,.17),rgba(255,255,255,.08));
  border:1px solid rgba(255,255,255,.17);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.14),0 12px 26px rgba(15,23,42,.10);
}
.ev-sidebar-community-icon{
  width:50px; height:50px; margin:0 auto 8px; border-radius:17px;
  display:grid; place-items:center; color:#0F592F;
  background:linear-gradient(135deg,rgba(236,253,245,.96),rgba(255,255,255,.92));
  border:1px solid rgba(255,255,255,.44); box-shadow:0 8px 18px rgba(15,23,42,.10); font-size:1.28rem;
}
.ev-sidebar-community-label{ font-size:.72rem; font-weight:850; color:rgba(255,255,255,.76); margin-bottom:3px; }
.ev-sidebar-community-name{
  max-width:190px; margin:0 auto 12px; color:#fff; font-weight:950; font-size:.96rem;
  line-height:1.14; letter-spacing:-.01em; word-break:break-word;
}
.ev-sidebar-community-btn{
  min-height:36px; display:flex; align-items:center; justify-content:center; text-decoration:none;
  color:#fff; border-radius:14px; border:1px solid rgba(255,255,255,.40);
  background:rgba(255,255,255,.075); padding:8px 10px; font-weight:950; line-height:1.1;
  transition:background .2s ease,border-color .18s ease,transform .2s ease,box-shadow .2s ease;
}
.ev-sidebar-community-btn:hover{
  color:#fff; background:linear-gradient(135deg,rgba(234,124,18,.96),rgba(245,158,11,.90));
  border-color:rgba(255,255,255,.22); transform:translateY(-1px) scale(1.01);
  box-shadow:0 12px 24px rgba(234,124,18,.28),inset 0 1px 0 rgba(255,255,255,.18);
}
#sidebar-backdrop{
  display:block;
  position:fixed; inset:0;
  background:rgba(15,23,42,.54);
  backdrop-filter:blur(3px); -webkit-backdrop-filter:blur(3px);
  z-index:1035; opacity:0; pointer-events:none;
  transition:opacity .22s ease;
}
#sidebar-backdrop.show,#sidebar-backdrop.active,body.ev-sidebar-open #sidebar-backdrop{ opacity:1; pointer-events:auto; }
@media (min-width:992px){ #sidebar-backdrop{ display:none; } }
@media (max-width:991.98px){
  :root{ --ev-topbar-h:52px; }

  #sidebar.app-sidebar{
    top:var(--ev-topbar-h);
    height:calc(100vh - var(--ev-topbar-h));
    width:min(82vw,286px);
    z-index:1040;
    transform:translateX(-106%);
    border-bottom-right-radius:24px;
    box-shadow:
      18px 0 42px rgba(15,23,42,.26),
      4px 0 12px rgba(15,23,42,.12);
  }

  #sidebar.active,
  #sidebar.open,
  body.ev-sidebar-open #sidebar.app-sidebar{
    transform:translateX(0);
  }

  #sidebar.app-sidebar .sidebar-brand{
    display:none !important;
    height:0 !important;
    padding:0 !important;
    margin:0 !important;
    overflow:hidden !important;
  }

  /* Navegación arriba; utilidades y comunidad al pie cuando hay altura. */
  .sidebar-wrapper{
    flex:0 0 auto;
    padding:.78rem .42rem .12rem;
  }

  .app-sidebar .nav-link,
  .app-sidebar button.nav-link{
    min-height:44px;
    margin:.20rem .45rem;
  }

  .ev-sidebar-footer{
    flex:0 0 auto;
    margin:auto 12px 18px;
    padding-top:14px;
  }

  body.ev-sidebar-open{
    overflow:hidden;
    touch-action:none;
  }

  body{
    overscroll-behavior:contain;
  }
}
</style>
