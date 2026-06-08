<style>
/* ============================================================
   ENTRE VECINOS - TOPBAR PREMIUM V5
   Foto de perfil editable + dropdown limpio sin acciones duplicadas
============================================================ */
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde-mid:#0E7A43;
  --ev-verde:#16A34A;
  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#BF3604;
  --ev-gris-900:#111827;
  --ev-gris-700:#374151;
  --ev-gris-600:#4B5563;
  --ev-gris-500:#6B7280;
  --ev-gris-400:#9CA3AF;
  --ev-gris-300:#D1D5DB;
  --ev-gris-200:#E5E7EB;
  --ev-topbar-h:56px;
  --ev-radius:18px;
  --ev-radius-sm:12px;
  --ev-shadow-soft:0 10px 26px rgba(0,0,0,.14);
  --ev-switch-green:linear-gradient(135deg,#34D66F 0%,#16A34A 48%,#0E7A43 100%);
  --ev-switch-gray:linear-gradient(135deg,#E5E7EB 0%,#B7BEC8 48%,#8B95A3 100%);
}

.app-header.navbar{
  height:var(--ev-topbar-h);
  position:fixed;
  top:0;
  left:0;
  right:0;
  z-index:1050;
  display:flex;
  align-items:center;
  padding:.5rem 1rem;
  color:#fff;
  background:
    radial-gradient(circle at 75% 30%,rgba(255,255,255,.08),transparent 60%),
    radial-gradient(circle at 20% 80%,rgba(0,0,0,.08),transparent 70%),
    linear-gradient(145deg,#0F592F 0%,#0E7A43 45%,#16A34A 85%);
  box-shadow:var(--ev-shadow-soft);
  overflow:visible !important;
}

.app-header.navbar::before{
  content:"";
  position:absolute;
  inset:0;
  background:radial-gradient(circle at 85% 85%,rgba(187,247,208,.18),transparent 60%);
  opacity:.95;
  pointer-events:none;
}

.app-header.navbar > .container-fluid{
  position:relative;
  z-index:2;
  width:100%;
  min-width:0;
  display:flex;
  align-items:center;
  gap:.45rem;
  padding-left:0;
  padding-right:0;
}

#btnToggleSidebar,
.navbar-toggler{
  border:none !important;
  background:transparent !important;
  color:#fff !important;
  padding:.35rem .4rem;
  border-radius:12px;
  line-height:1;
  transition:background .18s ease,transform .18s ease;
}

#btnToggleSidebar:hover,
.navbar-toggler:hover{
  background:rgba(255,255,255,.10) !important;
}

#btnToggleSidebar:active,
.navbar-toggler:active{
  transform:translateY(.5px);
}

#btnToggleSidebar:focus,
.navbar-toggler:focus{
  outline:none;
  box-shadow:0 0 0 .18rem rgba(187,247,208,.45);
}

/* ============================================================
   MARCA SUPERIOR EV
============================================================ */
.ev-topbar-brand{
  height:40px;
  display:inline-flex;
  align-items:center;
  justify-content:flex-start;
  gap:10px;
  min-width:0;
  flex:0 0 auto;
  padding:0 8px 0 0;
  border-radius:999px;
  color:#fff;
  text-decoration:none;
  line-height:1;
  transition:background .18s ease,transform .18s ease,opacity .18s ease;
}

.ev-topbar-brand:hover{
  color:#fff;
  text-decoration:none;
  background:rgba(255,255,255,.08);
}

.ev-topbar-brand:active{
  transform:translateY(.5px);
}

.ev-topbar-brand:focus-visible{
  outline:none;
  box-shadow:0 0 0 .18rem rgba(187,247,208,.45);
}

.ev-topbar-brand-logo{
  width:32px;
  height:32px;
  min-width:32px;
  border-radius:50%;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  overflow:hidden;
  background:radial-gradient(circle at 30% 18%,#fff 0%,rgba(255,255,255,.98) 48%,rgba(243,244,246,.96) 100%);
  box-shadow:0 3px 8px rgba(15,23,42,.14),inset 0 1px 0 rgba(255,255,255,.95);
}

.ev-topbar-brand-logo img{
  width:24px;
  height:24px;
  max-width:24px;
  max-height:24px;
  display:block;
  object-fit:contain;
  transform:translateY(.2px);
}

.ev-topbar-brand-text{
  display:inline-flex;
  align-items:center;
  color:#fff;
  font-size:1rem;
  font-weight:750;
  line-height:1;
  letter-spacing:.1px;
  white-space:nowrap;
  text-shadow:0 1px 2px rgba(0,0,0,.16);
  transform:translateY(.2px);
}

/* ============================================================
   DISPONIBILIDAD PEDIDOS
============================================================ */
.ev-topbar-tools{
  display:flex;
  align-items:center;
  gap:.75rem;
  margin-left:auto;
  margin-right:1rem;
  min-width:0;
}

.ev-disp-wrap{
  display:flex;
  align-items:center;
}

.ev-disp-control{
  position:relative;
  display:flex;
  align-items:center;
  justify-content:center;
  width:64px;
  height:34px;
  padding:4px;
  border-radius:999px;
  background:linear-gradient(180deg,rgba(255,255,255,.13),rgba(255,255,255,.075));
  border:1px solid rgba(255,255,255,.20);
  box-shadow:0 4px 10px rgba(0,0,0,.10),inset 0 1px 0 rgba(255,255,255,.18),inset 0 -1px 0 rgba(0,0,0,.06);
  backdrop-filter:blur(9px);
  -webkit-backdrop-filter:blur(9px);
  transition:transform .18s ease,background .18s ease,border-color .18s ease,box-shadow .18s ease,opacity .18s ease;
}

.ev-disp-control::before{
  content:"";
  position:absolute;
  inset:1px;
  border-radius:999px;
  background:radial-gradient(circle at 25% 8%,rgba(255,255,255,.15),transparent 42%),linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,0));
  pointer-events:none;
}

.ev-disp-control:hover{
  transform:translateY(-1px);
  background:linear-gradient(180deg,rgba(255,255,255,.17),rgba(255,255,255,.09));
  border-color:rgba(255,255,255,.27);
}

.ev-disp-control:active{
  transform:translateY(0);
}

.ev-disp-control.is-updating{
  opacity:.72;
  transform:none;
  cursor:progress;
}

.ev-switch{
  position:relative;
  z-index:2;
  width:52px;
  height:26px;
  flex:0 0 52px;
  margin:0;
  border-radius:999px;
}

.ev-switch input{
  opacity:0;
  width:0;
  height:0;
  position:absolute;
}

.ev-switch-slider{
  position:absolute;
  inset:0;
  cursor:pointer;
  border-radius:999px;
  background:var(--ev-switch-gray);
  border:1px solid rgba(255,255,255,.34);
  box-shadow:inset 0 2px 5px rgba(17,24,39,.18),inset 0 -1px 2px rgba(255,255,255,.18),0 1px 0 rgba(255,255,255,.14);
  transition:background .22s cubic-bezier(.2,.8,.2,1),border-color .22s cubic-bezier(.2,.8,.2,1),box-shadow .22s cubic-bezier(.2,.8,.2,1);
}

.ev-switch-slider::before{
  content:"";
  position:absolute;
  left:3px;
  top:3px;
  width:20px;
  height:20px;
  border-radius:50%;
  background:radial-gradient(circle at 34% 24%,#fff 0%,#fff 42%,#F3F4F6 100%);
  box-shadow:0 3px 8px rgba(17,24,39,.23),0 1px 2px rgba(0,0,0,.11),inset 0 1px 0 rgba(255,255,255,.98);
  transition:transform .22s cubic-bezier(.2,.8,.2,1),background .22s cubic-bezier(.2,.8,.2,1);
}

.ev-switch-slider::after{
  content:"";
  position:absolute;
  left:7px;
  top:5px;
  width:9px;
  height:4px;
  border-radius:999px;
  background:rgba(255,255,255,.68);
  opacity:.78;
  transition:transform .22s cubic-bezier(.2,.8,.2,1);
}

.ev-switch.is-on .ev-switch-slider,
.ev-switch input:checked + .ev-switch-slider{
  background:var(--ev-switch-green);
  border-color:rgba(236,253,245,.46);
  box-shadow:inset 0 2px 5px rgba(15,89,47,.22),inset 0 -1px 2px rgba(255,255,255,.16),0 0 0 1px rgba(255,255,255,.10),0 3px 8px rgba(52,214,111,.18);
}

.ev-switch.is-on .ev-switch-slider::before,
.ev-switch input:checked + .ev-switch-slider::before{
  transform:translateX(26px);
  background:radial-gradient(circle at 34% 24%,#fff 0%,#fff 42%,#ECFDF5 100%);
}

.ev-switch.is-on .ev-switch-slider::after,
.ev-switch input:checked + .ev-switch-slider::after{
  transform:translateX(26px);
}

.ev-switch.is-off .ev-switch-slider{
  background:var(--ev-switch-gray);
}

.ev-switch.is-off .ev-switch-slider::before{
  transform:translateX(0);
}

.ev-switch input:disabled + .ev-switch-slider{
  opacity:.70;
  cursor:not-allowed;
}

.ev-switch input:focus-visible + .ev-switch-slider{
  outline:none;
  box-shadow:0 0 0 .16rem rgba(255,255,255,.24),inset 0 2px 5px rgba(0,0,0,.10);
}

.ev-disp-control-skeleton{
  position:relative;
  overflow:hidden;
  width:64px;
  height:34px;
  border-radius:999px;
  background:linear-gradient(180deg,rgba(255,255,255,.13),rgba(255,255,255,.075));
  border:1px solid rgba(255,255,255,.20);
  animation:evPulse .95s ease-in-out infinite;
}

.ev-disp-control-skeleton::after{
  content:"";
  position:absolute;
  top:0;
  bottom:0;
  width:42%;
  left:-42%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.26),transparent);
  animation:evSkeletonSweep 1.15s ease-in-out infinite;
}

@keyframes evPulse{
  0%,100%{opacity:.70}
  50%{opacity:1}
}

@keyframes evSkeletonSweep{
  from{left:-42%}
  to{left:110%}
}

/* ============================================================
   USUARIO / FOTO PERFIL
============================================================ */
.user-menu .nav-link{
  color:#fff !important;
  display:flex;
  align-items:center;
  gap:.55rem;
  padding:.25rem .35rem;
  border-radius:14px;
  transition:background .18s ease;
}

.user-menu .nav-link:hover{
  background:rgba(255,255,255,.10);
}

.ev-user-topbar-link{
  min-width:0;
}

.ev-user-topbar-name{
  max-width:min(34vw, 360px);
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}

.ev-avatar-file-input{
  display:none !important;
}

.ev-avatar-upload-trigger{
  position:relative;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  flex:0 0 auto;
  border-radius:999px;
  cursor:pointer;
  outline:0;
}

.ev-avatar-upload-trigger img{
  display:block;
  border-radius:50%;
  object-fit:cover;
  transition:filter .18s ease,transform .18s ease,box-shadow .18s ease;
}

.ev-avatar-upload-trigger:hover img{
  filter:brightness(.88);
  transform:translateY(-1px);
  box-shadow:0 8px 18px rgba(15,23,42,.18);
}

.ev-avatar-upload-trigger:focus-visible{
  box-shadow:0 0 0 .18rem rgba(187,247,208,.45);
}

.ev-avatar-camera{
  position:absolute;
  right:-2px;
  bottom:0;
  width:19px;
  height:19px;
  display:grid;
  place-items:center;
  border-radius:999px;
  color:#fff;
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  border:2px solid rgba(255,255,255,.98);
  font-size:.58rem;
  box-shadow:0 6px 12px rgba(234,124,18,.24);
  opacity:0;
  transform:translateY(2px) scale(.92);
  transition:opacity .18s ease,transform .18s ease;
  pointer-events:none;
}

.ev-avatar-upload-trigger:hover .ev-avatar-camera,
.ev-avatar-upload-trigger.is-uploading .ev-avatar-camera{
  opacity:1;
  transform:translateY(0) scale(1);
}

.ev-avatar-upload-trigger.is-uploading img{
  filter:brightness(.74);
}

.ev-avatar-upload-trigger.is-uploading::after{
  content:"";
  position:absolute;
  inset:5px;
  border-radius:50%;
  border:2px solid rgba(255,255,255,.40);
  border-top-color:#fff;
  animation:evAvatarSpin .72s linear infinite;
}

@keyframes evAvatarSpin{
  to{transform:rotate(360deg)}
}

.ev-avatar-upload-trigger-sm{
  width:38px;
  height:38px;
}

.ev-avatar-upload-trigger-sm img{
  width:38px !important;
  height:38px !important;
  margin:0 !important;
  border:2px solid rgba(255,255,255,.92) !important;
}

.user-menu .dropdown-menu{
  border:1px solid rgba(229,231,235,.85);
  border-radius:18px;
  box-shadow:0 18px 45px rgba(0,0,0,.22),0 6px 12px rgba(0,0,0,.12);
  overflow:hidden;
  padding:0;
  margin-top:.75rem;
}

.ev-user-dropdown-card{
  padding:18px 18px 16px;
  background:
    radial-gradient(circle at 75% 30%,rgba(255,255,255,.08),transparent 60%),
    radial-gradient(circle at 20% 80%,rgba(0,0,0,.08),transparent 70%),
    linear-gradient(145deg,var(--ev-verde-oscuro),var(--ev-verde-mid) 55%,var(--ev-verde));
  border-bottom:1px solid rgba(255,255,255,.20);
}

.ev-avatar-upload-trigger-lg{
  width:78px;
  height:78px;
  margin:0 auto 10px;
}

.ev-avatar-upload-trigger-lg img{
  width:76px !important;
  height:76px !important;
  margin:0 !important;
  border:2px solid rgba(255,255,255,.95) !important;
}

.ev-avatar-upload-trigger-lg .ev-avatar-camera{
  width:25px;
  height:25px;
  right:0;
  bottom:2px;
  font-size:.72rem;
}

.ev-user-dropdown-name{
  max-width:230px;
  margin:0 auto;
  color:#fff;
  font-weight:950;
  line-height:1.24;
  overflow-wrap:anywhere;
}

.ev-user-dropdown-role{
  display:block;
  margin-top:4px;
  color:rgba(255,255,255,.88);
  font-weight:800;
}

.ev-user-dropdown-hint{
  margin:13px auto 0;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  max-width:100%;
  min-height:34px;
  padding:8px 12px;
  border-radius:999px;
  color:#ECFDF5;
  background:rgba(255,255,255,.12);
  border:1px solid rgba(255,255,255,.20);
  font-size:.76rem;
  font-weight:850;
  line-height:1.15;
}

/* ============================================================
   BACKDROP PERFIL MÓVIL
============================================================ */
.ev-user-menu-backdrop{
  display:none;
}

.dropdown-menu.show{
  animation:evFadeUp .22s ease;
}

@keyframes evFadeUp{
  from{opacity:0; transform:translateY(12px)}
  to{opacity:1; transform:translateY(0)}
}

/* ============================================================
   MÓVIL / TABLET
============================================================ */
@media (max-width:991.98px){
  :root{ --ev-topbar-h:52px; }

  .app-header.navbar{
    height:var(--ev-topbar-h);
    padding:.34rem .62rem;
    box-shadow:0 7px 18px rgba(15,23,42,.09);
  }

  .app-header.navbar > .container-fluid{
    gap:.28rem;
  }

  #btnToggleSidebar{
    width:40px;
    height:40px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    margin-right:0 !important;
    padding:0;
    border-radius:12px;
    background:rgba(255,255,255,.07) !important;
  }

  #btnToggleSidebar.is-open{
    background:rgba(255,255,255,.15) !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.11);
  }

  #btnToggleSidebar i{
    width:20px;
    display:inline-flex;
    justify-content:center;
    font-size:1.22rem !important;
    line-height:1;
  }

  #btnToggleSidebar.is-open i{
    font-size:1.08rem !important;
  }

  .ev-topbar-brand{
    display:none !important;
  }

  .ev-topbar-tools{
    margin-left:auto;
    margin-right:.38rem;
    gap:.4rem;
  }

  .ev-disp-control,
  .ev-disp-control-skeleton{
    width:56px;
    height:30px;
    padding:4px;
    background:linear-gradient(180deg,rgba(255,255,255,.095),rgba(255,255,255,.045));
    border-color:rgba(255,255,255,.15);
    box-shadow:0 2px 7px rgba(15,23,42,.08),inset 0 1px 0 rgba(255,255,255,.13);
    backdrop-filter:blur(5px);
    -webkit-backdrop-filter:blur(5px);
  }

  .ev-disp-control:hover{
    transform:none;
    background:linear-gradient(180deg,rgba(255,255,255,.12),rgba(255,255,255,.055));
    border-color:rgba(255,255,255,.19);
    box-shadow:0 3px 8px rgba(15,23,42,.09),inset 0 1px 0 rgba(255,255,255,.15);
  }

  .ev-switch{
    width:46px;
    height:22px;
    flex:0 0 46px;
  }

  .ev-switch-slider::before{
    width:16px;
    height:16px;
  }

  .ev-switch-slider::after{
    left:6px;
    top:5px;
    width:8px;
    height:3px;
  }

  .ev-switch.is-on .ev-switch-slider,
  .ev-switch input:checked + .ev-switch-slider{
    box-shadow:inset 0 2px 4px rgba(15,89,47,.18),inset 0 -1px 2px rgba(255,255,255,.13),0 1px 4px rgba(52,214,111,.10);
  }

  .ev-switch.is-on .ev-switch-slider::before,
  .ev-switch input:checked + .ev-switch-slider::before{
    transform:translateX(24px);
  }

  .ev-switch.is-on .ev-switch-slider::after,
  .ev-switch input:checked + .ev-switch-slider::after{
    transform:translateX(24px);
  }

  .user-menu span.ev-user-topbar-name{
    display:none !important;
  }

  .user-menu .nav-link{
    width:36px;
    height:36px;
    padding:0;
    justify-content:center;
    border-radius:999px;
    background:rgba(255,255,255,.06);
  }

  .user-menu .dropdown-toggle::after{
    display:none !important;
  }

  .ev-avatar-upload-trigger-sm,
  .ev-avatar-upload-trigger-sm img{
    width:30px !important;
    height:30px !important;
  }

  .ev-avatar-upload-trigger-sm img{
    border:2px solid rgba(255,255,255,.95) !important;
    box-shadow:0 3px 9px rgba(15,23,42,.16);
  }

  .ev-avatar-upload-trigger-sm .ev-avatar-camera{
    display:none;
  }

  .user-menu .dropdown-menu{
    position:fixed !important;
    top:calc(var(--ev-topbar-h) + 4px) !important;
    left:50% !important;
    margin-top:0 !important;
    transform:translateX(-50%) !important;
    width:min(92vw,360px) !important;
    min-width:0 !important;
    border-radius:18px !important;
    z-index:2000 !important;
    box-shadow:0 24px 54px rgba(15,23,42,.21),0 8px 18px rgba(15,23,42,.10);
  }

  .user-menu .dropdown-menu.show{
    animation:evUserMenuMobileIn .18s ease;
  }

  .ev-user-menu-backdrop{
    display:block;
    position:fixed;
    top:var(--ev-topbar-h);
    left:0;
    right:0;
    bottom:0;
    z-index:1045;
    background:rgba(15,23,42,.32);
    backdrop-filter:blur(2px);
    -webkit-backdrop-filter:blur(2px);
    opacity:0;
    visibility:hidden;
    pointer-events:none;
    transition:opacity .18s ease,visibility .18s ease;
  }

  .ev-user-menu-backdrop.is-visible{
    opacity:1;
    visibility:visible;
    pointer-events:auto;
  }

  body.ev-user-menu-open{
    overflow:hidden;
    touch-action:none;
  }
}

@keyframes evUserMenuMobileIn{
  from{opacity:0; transform:translateX(-50%) translateY(8px)}
  to{opacity:1; transform:translateX(-50%) translateY(0)}
}
</style>
