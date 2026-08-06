<style>
/* ============================================================
   ENTRE VECINOS - DASHBOARD PRINCIPAL DEL VECINO V4
   Cierre desktop + responsive premium
============================================================ */

:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#0E7A43;
  --ev-verde-claro:#16A34A;
  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;
  --ev-morado:#9333EA;
  --ev-azul:#2563EB;
  --ev-rojo:#DC2626;
  --ev-gris-fondo:#F3F4F6;
  --ev-gris-025:#FCFDFC;
  --ev-gris-050:#F9FAFB;
  --ev-gris-100:#F3F4F6;
  --ev-gris-150:#EEF2F7;
  --ev-gris-200:#E5E7EB;
  --ev-gris-300:#D1D5DB;
  --ev-gris-400:#9CA3AF;
  --ev-gris-500:#6B7280;
  --ev-gris-700:#374151;
  --ev-texto:#111827;
  --ev-texto-suave:#6B7280;
  --ev-borde:#E5E7EB;
  --ev-radius:20px;
  --ev-radius-lg:26px;
  --ev-radius-sm:14px;
  --ev-shadow:0 18px 46px rgba(15,23,42,.08);
  --ev-shadow-soft:0 10px 26px rgba(15,23,42,.06);
  --ev-shadow-hover:0 24px 58px rgba(15,23,42,.11);
  --ev-shadow-orange:0 18px 38px rgba(234,124,18,.16);
  --ev-topbar-h:56px;
  --ev-sidebar-w:260px;
}

body{ background:var(--ev-gris-fondo); }
.wrapper{ min-height:100vh; }
.main-container{
  margin-left:0;
  padding-top:var(--ev-topbar-h);
  min-height:100vh;
  min-width:0;
  overflow-x:hidden;
}

/* ============================================================
   DASHBOARD BASE
============================================================ */
.ev-home-dashboard-v2{
  width:100%;
  max-width:100%;
  min-width:0;
  padding:18px 18px 30px;
  color:var(--ev-texto);
  overflow-x:hidden;
}
.ev-home-dashboard-v2 *{ box-sizing:border-box; }
.ev-home-hero,
.ev-home-panel,
.ev-home-summary-card{
  background:#fff;
  border:1px solid rgba(148,163,184,.16);
  box-shadow:var(--ev-shadow);
}

/* ============================================================
   HERO
============================================================ */
.ev-home-hero{
  position:relative;
  isolation:isolate;
  min-height:142px;
  border-radius:var(--ev-radius-lg);
  padding:24px 26px;
  overflow:hidden;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:20px;
  transition:background .22s ease;
}
.ev-home-hero--generico{
  background:
    radial-gradient(circle at 86% 24%, rgba(22,163,74,.115), transparent 35%),
    linear-gradient(135deg,#FFFFFF 0%,#FFFFFF 54%,#F7FEFA 100%);
}
.ev-home-hero--urbanizacion{
  background:
    radial-gradient(circle at 88% 22%, rgba(22,163,74,.14), transparent 34%),
    radial-gradient(circle at 68% 82%, rgba(234,124,18,.045), transparent 28%),
    linear-gradient(135deg,#FFFFFF 0%,#FFFFFF 53%,#F5FDF8 100%);
}
.ev-home-hero--condominio{
  background:
    radial-gradient(circle at 88% 22%, rgba(37,99,235,.055), transparent 30%),
    radial-gradient(circle at 78% 75%, rgba(22,163,74,.10), transparent 34%),
    linear-gradient(135deg,#FFFFFF 0%,#FFFFFF 54%,#F7FBF9 100%);
}
.ev-home-hero::before{
  content:"";
  position:absolute;
  inset:0 0 auto 0;
  height:4px;
  background:linear-gradient(90deg,var(--ev-verde-oscuro),var(--ev-verde-claro),var(--ev-naranja));
}
.ev-home-hero-copy{ position:relative; z-index:2; max-width:640px; }
.ev-home-kicker{
  margin-bottom:4px;
  color:var(--ev-naranja);
  font-size:.78rem;
  font-weight:900;
  letter-spacing:.14em;
  text-transform:uppercase;
}
.ev-home-hero h1{
  margin:0 0 6px;
  color:var(--ev-verde-oscuro);
  font-size:clamp(1.62rem,2.6vw,2.18rem);
  line-height:1.08;
  font-weight:900;
  letter-spacing:-.035em;
}
.ev-home-hero p{
  margin:0;
  color:var(--ev-gris-500);
  line-height:1.52;
  font-size:.98rem;
}
.ev-home-hero-side{
  position:relative;
  z-index:1;
  min-width:min(44vw,640px);
  min-height:128px;
  display:flex;
  align-items:flex-start;
  justify-content:flex-end;
}
.ev-home-hero-art{
  position:absolute;
  inset:auto 0 -12px auto;
  width:min(42vw,560px);
  height:142px;
  pointer-events:none;
  opacity:.97;
  overflow:hidden;
}
.ev-home-hero-art::before{
  content:"";
  position:absolute;
  inset:auto 8px 0 8px;
  height:42px;
  border-radius:999px 999px 0 0;
  background:linear-gradient(180deg,rgba(187,247,208,.52),rgba(220,252,231,.18));
}
.ev-hero-cloud{
  position:absolute;
  top:16px;
  width:66px;
  height:22px;
  border-radius:999px;
  background:rgba(255,255,255,.86);
  box-shadow:16px -8px 0 2px rgba(255,255,255,.82),36px 0 0 -3px rgba(255,255,255,.75);
}
.ev-hero-cloud-1{ left:70px; transform:scale(.88); }
.ev-hero-cloud-2{ right:70px; top:30px; transform:scale(.74); }
.ev-hero-ground{
  position:absolute;
  left:18px;
  right:18px;
  bottom:4px;
  height:14px;
  border-radius:999px;
  background:linear-gradient(90deg,rgba(15,89,47,.16),rgba(22,163,74,.22),rgba(234,124,18,.12));
}
.ev-hero-tree{
  position:absolute;
  bottom:14px;
  width:14px;
  height:42px;
  border-radius:999px;
  background:linear-gradient(180deg,#16A34A,#0F592F);
  box-shadow:0 10px 0 -6px rgba(15,89,47,.32);
}
.ev-hero-tree::after{
  content:"";
  position:absolute;
  left:5px;
  bottom:-10px;
  width:4px;
  height:18px;
  border-radius:999px;
  background:#8B5E34;
}
.ev-hero-tree-1{ left:118px; }
.ev-hero-tree-2{ right:132px; height:50px; }
.ev-hero-building{
  position:absolute;
  bottom:18px;
  border-radius:7px 7px 2px 2px;
  background:linear-gradient(180deg,rgba(148,163,184,.23),rgba(148,163,184,.12));
  box-shadow:0 10px 18px rgba(15,23,42,.04);
}
.ev-hero-building::before{
  content:"";
  position:absolute;
  inset:12px 10px auto 10px;
  height:64px;
  background:
    linear-gradient(90deg,rgba(255,255,255,.74) 0 8px,transparent 8px 18px) 0 0/24px 18px,
    linear-gradient(90deg,rgba(255,255,255,.50) 0 8px,transparent 8px 18px) 0 22px/24px 18px,
    linear-gradient(90deg,rgba(255,255,255,.58) 0 8px,transparent 8px 18px) 0 44px/24px 18px;
}
.ev-hero-building-1{ width:76px; height:92px; right:184px; }
.ev-hero-building-2{ width:62px; height:74px; right:112px; opacity:.72; }
.ev-hero-building-3{ display:none; width:68px; height:106px; right:272px; opacity:.8; }
.ev-hero-common-area{
  display:none;
  position:absolute;
  right:34px;
  bottom:18px;
  width:62px;
  height:26px;
  border-radius:14px 14px 4px 4px;
  border:1px solid rgba(15,89,47,.14);
  background:linear-gradient(180deg,rgba(255,255,255,.94),rgba(236,253,245,.82));
}
.ev-hero-common-area::before{
  content:"";
  position:absolute;
  left:11px;
  right:11px;
  top:-9px;
  height:12px;
  border-radius:999px 999px 3px 3px;
  background:linear-gradient(135deg,rgba(15,89,47,.72),rgba(22,163,74,.62));
}
.ev-hero-common-area::after{
  content:"";
  position:absolute;
  left:26px;
  bottom:0;
  width:11px;
  height:15px;
  border-radius:4px 4px 0 0;
  background:rgba(15,89,47,.20);
}
.ev-hero-house{
  position:absolute;
  bottom:16px;
  width:88px;
  height:58px;
  border-radius:8px 8px 4px 4px;
  background:linear-gradient(180deg,#FFFDF8,#F1F8EF);
  border:1px solid rgba(15,89,47,.16);
  box-shadow:0 10px 18px rgba(15,23,42,.08);
}
.ev-hero-house::before{
  content:"";
  position:absolute;
  left:50%;
  top:-28px;
  width:76px;
  height:42px;
  border-radius:7px 7px 3px 3px;
  background:linear-gradient(135deg,#0F592F,#16A34A);
  box-shadow:0 7px 12px rgba(15,23,42,.08);
  clip-path:polygon(50% 0%,100% 68%,88% 68%,88% 100%,12% 100%,12% 68%,0% 68%);
  transform:translateX(-50%);
}
.ev-hero-house::after{
  content:"";
  position:absolute;
  left:16px;
  top:18px;
  width:18px;
  height:18px;
  border-radius:4px;
  background:rgba(22,163,74,.18);
  box-shadow:32px 0 0 rgba(234,124,18,.16);
}
.ev-hero-house i{
  position:absolute;
  right:16px;
  bottom:0;
  width:18px;
  height:28px;
  border-radius:6px 6px 0 0;
  background:rgba(15,89,47,.24);
}
.ev-hero-house-1{ left:34px; transform:scale(.92); }
.ev-hero-house-2{ left:150px; bottom:18px; transform:scale(1.02); }
.ev-hero-house-3{ right:28px; transform:scale(.86); }
.ev-hero-access{
  display:none;
  position:absolute;
  right:25px;
  bottom:16px;
  width:78px;
  height:50px;
  border-radius:8px 8px 3px 3px;
  border:1px solid rgba(15,89,47,.16);
  background:linear-gradient(180deg,#FFFDF8,#EDF8EF);
  box-shadow:0 10px 18px rgba(15,23,42,.07);
}
.ev-hero-access-roof{
  position:absolute;
  left:-5px; right:-5px; top:-12px; height:15px;
  border-radius:8px 8px 3px 3px;
  background:linear-gradient(135deg,#0F592F,#16A34A);
}
.ev-hero-access-door{
  position:absolute;
  left:13px; bottom:0; width:15px; height:26px;
  border-radius:5px 5px 0 0;
  background:rgba(15,89,47,.24);
}
.ev-hero-access-barrier{
  position:absolute;
  left:-34px; bottom:15px; width:40px; height:4px;
  border-radius:999px;
  transform:rotate(-8deg);
  transform-origin:right center;
  background:linear-gradient(90deg,var(--ev-naranja),#F59E0B);
}
.ev-home-hero--urbanizacion .ev-hero-building,
.ev-home-hero--urbanizacion .ev-hero-common-area{ display:none; }
.ev-home-hero--urbanizacion .ev-hero-access{ display:block; }
.ev-home-hero--urbanizacion .ev-hero-house-1{ left:22px; transform:scale(.88); }
.ev-home-hero--urbanizacion .ev-hero-house-2{ left:135px; transform:scale(.96); }
.ev-home-hero--urbanizacion .ev-hero-house-3{ left:252px; right:auto; transform:scale(.88); }
.ev-home-hero--urbanizacion .ev-hero-tree-1{ left:116px; }
.ev-home-hero--urbanizacion .ev-hero-tree-2{ right:112px; }
.ev-home-hero--condominio .ev-hero-house,
.ev-home-hero--condominio .ev-hero-access{ display:none; }
.ev-home-hero--condominio .ev-hero-building-1{ right:156px; height:98px; width:80px; }
.ev-home-hero--condominio .ev-hero-building-2{ right:72px; height:84px; width:68px; opacity:.82; }
.ev-home-hero--condominio .ev-hero-building-3{ display:block; }
.ev-home-hero--condominio .ev-hero-common-area{ display:block; }
.ev-home-hero--condominio .ev-hero-tree-1{ left:auto; right:246px; height:44px; }
.ev-home-hero--condominio .ev-hero-tree-2{ right:18px; height:42px; }
.ev-home-hero-community,#evHomeCommunityHero{ display:none !important; }

/* ============================================================
   MÉTRICAS
============================================================ */
.ev-home-summary-grid{
  display:grid;
  grid-template-columns:repeat(4,minmax(180px,1fr));
  gap:14px;
  margin-top:14px;
}
.ev-home-summary-card{
  position:relative;
  min-height:164px;
  border-radius:var(--ev-radius);
  padding:18px;
  display:flex;
  gap:14px;
  align-items:flex-start;
  overflow:hidden;
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease;
}
.ev-home-summary-card::after{
  content:"";
  position:absolute;
  left:18px; right:18px; bottom:0; height:3px;
  border-radius:999px 999px 0 0;
  background:linear-gradient(90deg,rgba(15,89,47,.42),rgba(234,124,18,.34));
  opacity:0;
  transform:scaleX(.82);
  transform-origin:center;
  transition:opacity .16s ease,transform .16s ease;
}
.ev-home-summary-card:hover{
  transform:translateY(-2px);
  box-shadow:0 22px 48px rgba(15,23,42,.10);
  border-color:rgba(234,124,18,.22);
}
.ev-home-summary-card:hover::after{ opacity:1; transform:scaleX(1); }
.ev-home-summary-card:hover .ev-home-summary-icon{ transform:scale(1.035); }
.ev-home-summary-icon{
  width:58px; height:58px; border-radius:22px;
  display:grid; place-items:center; flex:0 0 auto;
  font-size:1.42rem;
  transition:transform .16s ease,box-shadow .16s ease;
}
.ev-home-summary-green .ev-home-summary-icon,
.ev-home-summary-wallet .ev-home-summary-icon{
  background:linear-gradient(135deg,rgba(187,247,208,.72),rgba(236,253,245,.96));
  color:var(--ev-verde-oscuro);
}
.ev-home-summary-orange .ev-home-summary-icon{
  background:linear-gradient(135deg,rgba(255,237,213,.95),rgba(255,247,237,.98));
  color:var(--ev-naranja);
}
.ev-home-summary-purple .ev-home-summary-icon{
  background:linear-gradient(135deg,rgba(243,232,255,.95),rgba(250,245,255,.98));
  color:var(--ev-morado);
}
.ev-home-summary-body{ min-width:0; flex:1 1 auto; }
.ev-home-summary-body span{
  display:block;
  color:var(--ev-gris-500);
  font-size:.77rem;
  font-weight:850;
  line-height:1.2;
}
.ev-home-summary-body strong{
  display:block;
  margin:7px 0 4px;
  color:var(--ev-verde-oscuro);
  font-size:2rem;
  line-height:.98;
  font-weight:950;
  letter-spacing:-.035em;
}
.ev-home-summary-orange .ev-home-summary-body strong{ color:var(--ev-naranja); }
.ev-home-summary-purple .ev-home-summary-body strong{ color:var(--ev-morado); }
.ev-home-summary-body small{
  display:block;
  min-height:34px;
  color:var(--ev-gris-500);
  font-size:.82rem;
  line-height:1.28;
}
.ev-home-link-btn,.ev-home-panel-action,.ev-home-mini-action{
  border:none;
  background:transparent;
  color:var(--ev-verde-oscuro);
  font-weight:900;
  font-size:.82rem;
  padding:0;
  display:inline-flex;
  align-items:center;
  gap:8px;
  margin-top:12px;
  transition:gap .16s ease,color .16s ease,transform .16s ease;
}
.ev-home-summary-orange .ev-home-link-btn{ color:var(--ev-naranja-oscuro); }
.ev-home-summary-purple .ev-home-link-btn{ color:var(--ev-morado); }
.ev-home-link-btn:hover,.ev-home-panel-action:hover,.ev-home-mini-action:hover{
  gap:11px;
  color:var(--ev-naranja);
  transform:translateY(-1px);
}

/* ============================================================
   PANELES
============================================================ */
.ev-home-main-grid{
  display:grid;
  grid-template-columns:minmax(0,1.05fr) minmax(360px,.95fr);
  gap:14px;
  margin-top:14px;
}
.ev-home-panel{
  border-radius:var(--ev-radius);
  padding:16px;
  overflow:hidden;
}
.ev-home-panel-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin-bottom:14px;
}
.ev-home-panel-head > div{
  display:flex;
  align-items:center;
  gap:10px;
  min-width:0;
}
.ev-home-panel-head > div > i{
  width:34px; height:34px; display:grid; place-items:center;
  border-radius:13px;
  color:var(--ev-verde-oscuro);
  background:rgba(236,253,245,.95);
  border:1px solid rgba(22,163,74,.14);
}
.ev-home-panel-head h2{
  margin:0;
  color:var(--ev-verde-oscuro);
  font-size:1.05rem;
  font-weight:950;
  letter-spacing:-.02em;
}
.ev-home-panel-action{ margin:0; white-space:nowrap; }
.ev-home-panel-action--pending{
  gap:7px; padding:7px 12px; margin:0;
  border:1px solid rgba(148,163,184,.22);
  border-radius:999px;
  background:var(--ev-gris-050);
  color:var(--ev-gris-500);
  font-size:.76rem;
}
.ev-home-panel-action--pending:hover{
  gap:7px; color:var(--ev-naranja-oscuro); background:#FFF7ED;
  border-color:rgba(234,124,18,.22); transform:none;
}

/* Actividad */
.ev-home-activity-list{
  overflow:hidden;
  border:1px solid rgba(229,231,235,.86);
  border-radius:18px;
  background:linear-gradient(180deg,#fff,#fbfcfb);
}
.ev-home-activity-item{
  display:grid;
  grid-template-columns:42px minmax(0,1fr) auto;
  gap:12px;
  align-items:center;
  padding:13px 14px;
  border-bottom:1px solid rgba(229,231,235,.72);
}
.ev-home-activity-item:last-child{ border-bottom:none; }
.ev-home-activity-icon{
  width:36px; height:36px; border-radius:50%;
  display:grid; place-items:center; color:#fff; font-size:.98rem;
  box-shadow:0 8px 16px rgba(15,23,42,.08);
}
.ev-home-activity-icon.is-verde{ background:linear-gradient(135deg,#16A34A,#0F8E48); }
.ev-home-activity-icon.is-naranja{ background:linear-gradient(135deg,#EA7C12,#F59E0B); }
.ev-home-activity-icon.is-morado{ background:linear-gradient(135deg,#9333EA,#A855F7); }
.ev-home-activity-icon.is-azul{ background:linear-gradient(135deg,#2563EB,#38BDF8); }
.ev-home-activity-icon.is-rojo{ background:linear-gradient(135deg,#DC2626,#EF4444); }
.ev-home-activity-icon.is-gris{ background:linear-gradient(135deg,#9CA3AF,#6B7280); }
.ev-home-activity-copy{ min-width:0; }
.ev-home-activity-copy strong{
  display:block; margin-bottom:3px; color:#182033;
  font-weight:900; font-size:.86rem; line-height:1.2;
}
.ev-home-activity-copy p{
  margin:0; color:var(--ev-gris-500); font-size:.80rem; line-height:1.28;
}
.ev-home-activity-item time{
  color:var(--ev-gris-500); font-size:.76rem; font-weight:750; white-space:nowrap;
}

/* Acciones rápidas */
.ev-home-actions-grid{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:10px;
}
.ev-home-action-card{
  position:relative;
  min-height:76px;
  padding:12px 40px 12px 52px;
  border:1px solid rgba(229,231,235,.92);
  border-radius:16px;
  background:linear-gradient(180deg,#fff,#fbfdfb);
  text-align:left;
  box-shadow:0 8px 18px rgba(15,23,42,.04);
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease;
}
.ev-home-action-card:hover,.ev-home-action-card:focus-visible{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.46);
  box-shadow:var(--ev-shadow-orange);
  background:radial-gradient(circle at 12% 18%,rgba(255,237,213,.92),transparent 34%),linear-gradient(135deg,#FFF,#FFF7ED);
  outline:0;
}
.ev-home-action-card > span{
  position:absolute; left:12px; top:14px;
  width:30px; height:30px; border-radius:12px;
  display:grid; place-items:center;
  color:var(--ev-verde-oscuro); background:#ECFDF3;
  border:1px solid rgba(22,163,74,.14);
  transition:background .16s ease,color .16s ease,transform .16s ease,border-color .16s ease;
}
.ev-home-action-card:hover > span,.ev-home-action-card:focus-visible > span{
  color:#fff; background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  border-color:rgba(234,124,18,.36); transform:scale(1.045);
}
.ev-home-action-card strong{
  display:block; color:#182033; font-size:.84rem; font-weight:950; line-height:1.18;
}
.ev-home-action-card:hover strong,.ev-home-action-card:focus-visible strong{ color:var(--ev-naranja-oscuro); }
.ev-home-action-card small{
  display:block; margin-top:3px; color:var(--ev-gris-500); font-size:.75rem; line-height:1.22;
}
.ev-home-action-chevron{
  position:absolute; right:13px; top:50%; transform:translateY(-50%);
  color:var(--ev-verde-oscuro); transition:color .16s ease,transform .16s ease;
}
.ev-home-action-card:hover .ev-home-action-chevron,.ev-home-action-card:focus-visible .ev-home-action-chevron{
  color:var(--ev-naranja); transform:translate(3px,-50%);
}
.ev-home-action-card--pending{
  border-style:dashed;
  border-color:rgba(148,163,184,.38);
  background:linear-gradient(180deg,#FCFDFC,#F9FAFB);
  box-shadow:none;
}
.ev-home-action-card--pending > span{
  color:var(--ev-gris-500); background:var(--ev-gris-100); border-color:rgba(148,163,184,.24);
}
.ev-home-action-heading{ display:flex; align-items:center; flex-wrap:wrap; gap:6px; min-width:0; }
.ev-home-action-heading strong{ display:inline-block; }
.ev-home-action-heading em{
  display:inline-flex; align-items:center; border-radius:999px; padding:3px 7px;
  font-style:normal; font-size:.62rem; line-height:1; font-weight:900;
  letter-spacing:.04em; text-transform:uppercase; color:#92400E;
  background:#FFF7ED; border:1px solid rgba(234,124,18,.18);
}
.ev-home-action-card--pending .ev-home-action-chevron{ color:var(--ev-gris-400); }
.ev-home-action-card--pending:hover,.ev-home-action-card--pending:focus-visible{
  transform:none; border-color:rgba(234,124,18,.28); box-shadow:0 12px 26px rgba(15,23,42,.05);
  background:linear-gradient(180deg,#FFF,#FFFDF9);
}
.ev-home-action-card--pending:hover > span,.ev-home-action-card--pending:focus-visible > span{
  transform:none; color:var(--ev-naranja-oscuro); background:#FFF7ED; border-color:rgba(234,124,18,.18);
}
.ev-home-action-card--pending:hover strong,.ev-home-action-card--pending:focus-visible strong{ color:#182033; }
.ev-home-action-card--pending:hover .ev-home-action-chevron,.ev-home-action-card--pending:focus-visible .ev-home-action-chevron{
  color:var(--ev-naranja); transform:translateY(-50%);
}

/* Comunidad / publicaciones */
.ev-home-community-panel,.ev-home-publications-panel{ margin-top:14px; }
.ev-home-community-strip{ display:grid; grid-template-columns:1fr; gap:12px; }
.ev-home-community-empty,.ev-home-empty-state{
  display:flex; gap:13px; align-items:center; min-height:96px;
  border-radius:18px; padding:16px;
  background:linear-gradient(135deg,rgba(236,253,245,.92),rgba(255,255,255,.98));
  border:1px dashed rgba(15,89,47,.28);
}
.ev-home-empty-icon{
  width:54px; height:54px; border-radius:20px;
  display:grid; place-items:center; flex:0 0 auto;
  color:var(--ev-verde-oscuro);
  background:linear-gradient(135deg,rgba(187,247,208,.72),rgba(255,255,255,.98));
  border:1px solid rgba(22,163,74,.15); font-size:1.3rem;
}
.ev-home-community-empty strong,.ev-home-empty-state strong{
  display:block; margin-bottom:4px; color:var(--ev-verde-oscuro); font-size:.94rem; font-weight:950;
}
.ev-home-community-empty p,.ev-home-empty-state p{
  margin:0; color:var(--ev-gris-500); font-size:.84rem; line-height:1.42;
}
.ev-home-publications-grid{
  display:grid; grid-template-columns:repeat(5,minmax(160px,1fr)); gap:12px;
}
.ev-home-publication-card{
  min-width:0; overflow:hidden; border:1px solid rgba(229,231,235,.92);
  border-radius:18px; background:#fff; box-shadow:0 10px 22px rgba(15,23,42,.05);
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease;
}
.ev-home-publication-card:hover{
  transform:translateY(-2px); border-color:rgba(22,163,74,.22); box-shadow:0 16px 32px rgba(15,23,42,.09);
}
.ev-home-publication-img{ position:relative; height:116px; background:#F3F4F6; overflow:hidden; }
.ev-home-publication-img img{ width:100%; height:100%; object-fit:cover; display:block; }
.ev-home-publication-img span{
  position:absolute; top:10px; left:10px; border-radius:999px; padding:4px 9px;
  color:#fff; font-size:.68rem; font-weight:900;
  background:linear-gradient(135deg,#16A34A,#0E7A43); box-shadow:0 8px 16px rgba(15,23,42,.14);
}
.ev-home-publication-body{ padding:11px 12px 12px; }
.ev-home-publication-body h3{
  min-height:40px; margin:0 0 7px; color:#182033; font-size:.86rem; line-height:1.18; font-weight:950;
}
.ev-home-publication-body > strong{
  display:block; color:var(--ev-verde-oscuro); font-size:.96rem; font-weight:950; margin-bottom:4px;
}
.ev-home-publication-body p{
  min-height:34px; margin:0 0 8px; color:var(--ev-gris-500); font-size:.75rem; line-height:1.32;
}
.ev-home-publication-foot{
  display:flex; align-items:center; justify-content:space-between; gap:8px;
  color:var(--ev-gris-500); font-size:.72rem; line-height:1.1;
}
.ev-home-publication-foot span,.ev-home-publication-foot small{
  display:inline-flex; align-items:center; gap:4px; min-width:0;
}
.ev-home-publication-foot small i{ color:var(--ev-naranja); }
.ev-home-publications-empty{ grid-column:1/-1; }
.ev-home-mini-action{
  margin-top:9px; padding:8px 13px; border-radius:999px;
  background:linear-gradient(135deg,var(--ev-verde-oscuro),var(--ev-verde)); color:#fff;
  box-shadow:0 10px 20px rgba(15,89,47,.16);
}
.ev-home-mini-action:hover{ color:#fff; background:linear-gradient(135deg,var(--ev-naranja),#F59E0B); }

/* Skeleton + error */
.ev-home-skeleton-line,.ev-home-skeleton-card{
  border-radius:16px;
  background:linear-gradient(90deg,#F3F4F6 0%,#FFF 50%,#F3F4F6 100%);
  background-size:200% 100%;
  animation:evHomeSkeleton 1.2s ease-in-out infinite;
}
.ev-home-skeleton-line{ height:58px; margin:10px; }
.ev-home-skeleton-card{ height:210px; }
@keyframes evHomeSkeleton{ 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.ev-home-error{
  margin-top:14px; border-radius:18px; padding:14px 16px;
  color:#991B1B; background:#FEF2F2; border:1px solid #FECACA; font-weight:800;
}

/* ============================================================
   RESPONSIVE
============================================================ */
@media (max-width:1399.98px){
  .ev-home-publications-grid{ grid-template-columns:repeat(4,minmax(160px,1fr)); }
}
@media (max-width:1199.98px){
  .ev-home-summary-grid{ grid-template-columns:repeat(2,minmax(220px,1fr)); }
  .ev-home-main-grid{ grid-template-columns:1fr; }
  .ev-home-publications-grid{ grid-template-columns:repeat(3,minmax(160px,1fr)); }
  .ev-home-hero-side{ min-width:360px; }
  .ev-home-hero-art{ width:360px; }
  .ev-home-hero--urbanizacion .ev-hero-house-3{ display:none; }
  .ev-home-hero--urbanizacion .ev-hero-house-1{ left:18px; }
  .ev-home-hero--urbanizacion .ev-hero-house-2{ left:124px; }
  .ev-home-hero--condominio .ev-hero-building-3{
    right:224px; transform:scale(.88); transform-origin:bottom right;
  }
}
@media (max-width:991.98px){
  .main-container{ margin-left:0; padding-top:var(--ev-topbar-h); min-width:0; }
  .ev-home-dashboard-v2{ padding:12px 8px 22px; }
  .ev-home-hero{ align-items:flex-start; flex-direction:column; padding:20px 18px; }
  .ev-home-hero-side{ width:100%; min-width:0; min-height:128px; justify-content:flex-start; }
  .ev-home-hero-art{ width:min(100%,440px); right:auto; left:0; opacity:.72; }
}
@media (max-width:767.98px){
  .ev-home-summary-grid{ grid-template-columns:1fr; gap:10px; margin-top:10px; }
  .ev-home-summary-card{ min-height:0; }
  .ev-home-main-grid{ gap:10px; margin-top:10px; }
  .ev-home-actions-grid{ grid-template-columns:1fr; gap:9px; }
  .ev-home-publications-grid{ grid-template-columns:repeat(2,minmax(140px,1fr)); }
  .ev-home-activity-item{ grid-template-columns:38px minmax(0,1fr); }
  .ev-home-activity-item time{ grid-column:2; justify-self:start; margin-top:-4px; }
  .ev-home-community-panel,.ev-home-publications-panel{ margin-top:10px; }
}
@media (max-width:575.98px){
  .ev-home-dashboard-v2{ padding:8px 0 18px; }
  .ev-home-hero,.ev-home-panel,.ev-home-summary-card{ border-radius:17px; }
  .ev-home-hero{ min-height:0; padding:18px 16px; }
  .ev-home-kicker{ font-size:.69rem; margin-bottom:5px; }
  .ev-home-hero h1{ margin-bottom:7px; font-size:1.44rem; line-height:1.1; }
  .ev-home-hero p{ font-size:.88rem; line-height:1.46; }
  .ev-home-hero-side{ display:none; }

  /* Métricas realmente móviles: misma jerarquía, menor desplazamiento vertical */
  .ev-home-summary-card{
    padding:12px 13px;
    gap:11px;
    align-items:center;
    box-shadow:0 10px 28px rgba(15,23,42,.06);
  }
  .ev-home-summary-card::after{ left:13px; right:13px; height:2px; }
  .ev-home-summary-icon{ width:46px; height:46px; border-radius:16px; font-size:1.18rem; }
  .ev-home-summary-body span{ font-size:.72rem; line-height:1.12; }
  .ev-home-summary-body strong{ margin:3px 0 2px; font-size:1.60rem; line-height:1; }
  .ev-home-summary-body small{ min-height:0; font-size:.75rem; line-height:1.24; }
  .ev-home-summary-body .ev-home-link-btn{
    min-height:26px; margin-top:7px; font-size:.76rem; gap:6px;
  }

  .ev-home-panel{ padding:12px; box-shadow:0 12px 30px rgba(15,23,42,.065); }
  .ev-home-panel-head{ align-items:flex-start; gap:8px; margin-bottom:10px; }
  .ev-home-panel-head > div{ gap:8px; }
  .ev-home-panel-head > div > i{ width:30px; height:30px; border-radius:11px; font-size:.88rem; }
  .ev-home-panel-head h2{ font-size:.96rem; line-height:1.2; }
  .ev-home-panel-action{ font-size:.74rem; gap:6px; }
  .ev-home-panel-action--pending{ padding:6px 9px; font-size:.68rem; }

  .ev-home-activity-list{ border-radius:15px; }
  .ev-home-activity-item{ padding:10px 10px; gap:9px; }
  .ev-home-activity-icon{ width:34px; height:34px; font-size:.88rem; }
  .ev-home-activity-copy strong{ font-size:.81rem; line-height:1.22; }
  .ev-home-activity-copy p{ font-size:.75rem; line-height:1.3; }
  .ev-home-activity-item time{ font-size:.71rem; }

  .ev-home-action-card{
    min-height:64px;
    padding:10px 35px 10px 47px;
    border-radius:14px;
    box-shadow:0 6px 15px rgba(15,23,42,.035);
  }
  .ev-home-action-card > span{ left:10px; top:11px; width:28px; height:28px; border-radius:10px; font-size:.9rem; }
  .ev-home-action-card strong{ font-size:.80rem; }
  .ev-home-action-card small{ font-size:.70rem; }
  .ev-home-action-chevron{ right:11px; font-size:.84rem; }
  .ev-home-action-heading{ gap:5px; }
  .ev-home-action-heading em{ font-size:.57rem; padding:3px 6px; }

  .ev-home-community-empty,.ev-home-empty-state{
    min-height:0;
    align-items:flex-start;
    gap:10px;
    border-radius:15px;
    padding:12px;
  }
  .ev-home-empty-icon{ width:45px; height:45px; border-radius:15px; font-size:1.05rem; }
  .ev-home-community-empty strong,.ev-home-empty-state strong{
    margin-bottom:3px; font-size:.84rem; line-height:1.3;
  }
  .ev-home-community-empty p,.ev-home-empty-state p{ font-size:.75rem; line-height:1.38; }
  .ev-home-mini-action{ margin-top:7px; padding:7px 12px; font-size:.73rem; }

  .ev-home-publications-grid{ grid-template-columns:1fr; }
  .ev-home-publication-img{ height:160px; }
}
@media (max-width:379.98px){
  .ev-home-hero{ padding:17px 14px; }
  .ev-home-panel{ padding:11px; }
  .ev-home-panel-head h2{ font-size:.92rem; }
  .ev-home-panel-action--pending{ padding:5px 8px; }
}


/* ============================================================
   COMUNIDAD EN DASHBOARD - PREVIEW COMPACTO
============================================================ */
.ev-home-action-card--community span{
  background:linear-gradient(135deg,rgba(187,247,208,.78),rgba(255,247,237,.94));
  color:var(--ev-verde-oscuro);
}

.ev-home-community-panel{
  margin-top:14px;
}

.ev-home-community-strip{
  display:grid;
  gap:10px;
}

.ev-home-community-summary{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:14px;
  padding:12px 14px;
  border-radius:17px;
  background:
    radial-gradient(circle at top left, rgba(22,163,74,.10), transparent 34%),
    linear-gradient(135deg,#FFFFFF 0%,#F7FEFA 100%);
  border:1px solid rgba(22,163,74,.15);
}

.ev-home-community-summary-copy{
  min-width:0;
}

.ev-home-community-summary-copy span{
  display:block;
  color:var(--ev-naranja);
  font-size:.70rem;
  font-weight:950;
  letter-spacing:.08em;
  text-transform:uppercase;
}

.ev-home-community-summary-copy strong{
  display:block;
  margin:2px 0;
  color:var(--ev-verde-oscuro);
  font-size:1.55rem;
  line-height:1;
  font-weight:950;
  letter-spacing:-.035em;
}

.ev-home-community-summary-copy small{
  display:block;
  color:var(--ev-gris-500);
  font-size:.78rem;
  font-weight:750;
}

.ev-home-community-list{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:10px;
  align-items:stretch;
}

.ev-home-community-card{
  position:relative;
  min-width:0;
  min-height:118px;
  max-height:132px;
  overflow:hidden;
  display:flex;
  border-radius:16px;
  background:#fff;
  border:1px solid rgba(148,163,184,.18);
  box-shadow:0 8px 20px rgba(15,23,42,.055);
  transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}

.ev-home-community-card::before{
  content:"";
  position:absolute;
  top:0;
  left:0;
  bottom:0;
  width:4px;
  background:linear-gradient(180deg,var(--ev-verde),var(--ev-verde-claro));
  z-index:2;
}

.ev-home-community-card.is-importante::before{
  background:linear-gradient(180deg,var(--ev-naranja),#F59E0B);
}

.ev-home-community-card.is-urgente::before{
  background:linear-gradient(180deg,var(--ev-rojo),#F97316);
}

.ev-home-community-card:hover{
  transform:translateY(-1px);
  box-shadow:0 14px 30px rgba(15,23,42,.08);
  border-color:rgba(234,124,18,.22);
}

.ev-home-community-thumb{
  width:104px;
  min-width:104px;
  height:auto;
  background:linear-gradient(135deg,#ECFDF5,#FFF7ED);
  overflow:hidden;
  margin-left:4px;
}

.ev-home-community-thumb img{
  width:100%;
  height:100%;
  display:block;
  object-fit:cover;
}

.ev-home-community-body{
  flex:1 1 auto;
  min-width:0;
  padding:10px 12px 10px 12px;
  display:flex;
  flex-direction:column;
}

.ev-home-community-meta{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  margin-bottom:5px;
}

.ev-home-community-meta span{
  display:inline-flex;
  align-items:center;
  gap:5px;
  color:var(--ev-verde-oscuro);
  font-size:.70rem;
  font-weight:950;
  line-height:1.1;
}

.ev-home-community-meta em{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:3px 7px;
  border-radius:999px;
  background:#FFF7ED;
  color:var(--ev-naranja-oscuro);
  font-size:.62rem;
  font-style:normal;
  font-weight:950;
  white-space:nowrap;
}

.ev-home-community-card.is-urgente .ev-home-community-meta em{
  background:#FEF2F2;
  color:#B91C1C;
}

.ev-home-community-body h3{
  margin:0 0 4px;
  color:var(--ev-texto);
  font-size:.86rem;
  line-height:1.18;
  font-weight:950;
  letter-spacing:-.015em;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
}

.ev-home-community-body p{
  margin:0;
  color:var(--ev-gris-500);
  font-size:.74rem;
  line-height:1.32;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
}

.ev-home-community-foot{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  margin-top:auto;
  padding-top:8px;
}

.ev-home-community-foot time{
  color:var(--ev-gris-400);
  font-size:.70rem;
  font-weight:850;
  white-space:nowrap;
}

.ev-home-community-foot .ev-home-mini-action{
  min-height:28px;
  padding:6px 10px;
  border-radius:999px;
  font-size:.70rem;
  font-weight:900;
}

.ev-home-community-empty{
  display:flex;
  align-items:center;
  gap:14px;
  padding:15px;
  border-radius:18px;
  background:linear-gradient(135deg,#FFFFFF 0%,#F9FAFB 100%);
  border:1px dashed rgba(148,163,184,.36);
}

.ev-home-community-empty strong{
  display:block;
  margin-bottom:4px;
  color:var(--ev-verde-oscuro);
  font-weight:950;
}

.ev-home-community-empty p{
  margin:0;
  color:var(--ev-gris-500);
  line-height:1.45;
  font-size:.88rem;
}

@media (max-width:1199.98px){
  .ev-home-community-list{
    grid-template-columns:repeat(2,minmax(0,1fr));
  }
}

@media (max-width:767.98px){
  .ev-home-community-summary{
    align-items:flex-start;
    flex-direction:column;
  }

  .ev-home-community-list{
    grid-template-columns:1fr;
  }

  .ev-home-community-card{
    max-height:none;
    min-height:118px;
  }

  .ev-home-community-thumb{
    width:96px;
    min-width:96px;
  }

  .ev-home-community-empty{
    align-items:flex-start;
  }
}



/* ============================================================
   AJUSTE FINAL EV - ACCIONES ANARANJADAS DASHBOARD
============================================================ */
.ev-home-panel-action{
  color:var(--ev-naranja-oscuro);
  font-weight:950;
  transition:color .16s ease, gap .16s ease, transform .16s ease, text-shadow .16s ease;
}

.ev-home-panel-action:hover,
.ev-home-panel-action:focus-visible{
  color:var(--ev-naranja);
  gap:11px;
  transform:translateY(-1px);
  text-shadow:0 8px 18px rgba(234,124,18,.14);
  outline:0;
}

.ev-home-panel-action i{
  color:currentColor;
}

.ev-home-mini-action,
.ev-home-community-panel .ev-home-mini-action,
.ev-home-publications-panel .ev-home-mini-action{
  border:0;
  color:#fff;
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  box-shadow:0 10px 22px rgba(234,124,18,.22);
  transition:transform .16s ease, box-shadow .16s ease, background .16s ease, filter .16s ease;
}

.ev-home-mini-action:hover,
.ev-home-mini-action:focus-visible,
.ev-home-community-panel .ev-home-mini-action:hover,
.ev-home-community-panel .ev-home-mini-action:focus-visible,
.ev-home-publications-panel .ev-home-mini-action:hover,
.ev-home-publications-panel .ev-home-mini-action:focus-visible{
  color:#fff;
  background:linear-gradient(135deg,var(--ev-naranja-oscuro),var(--ev-naranja));
  box-shadow:0 14px 28px rgba(234,124,18,.30);
  transform:translateY(-1px);
  filter:brightness(1.02);
  outline:0;
}

.ev-home-community-summary .ev-home-mini-action{
  min-height:36px;
  padding:9px 15px;
  font-size:.78rem;
  font-weight:950;
}

.ev-home-community-foot .ev-home-mini-action{
  min-height:28px;
  padding:6px 11px;
  font-size:.70rem;
  font-weight:950;
}

.ev-home-actions-panel .ev-home-action-card strong{
  color:var(--ev-naranja-oscuro);
}

.ev-home-actions-panel .ev-home-action-card .ev-home-action-chevron{
  color:var(--ev-naranja-oscuro);
}

.ev-home-actions-panel .ev-home-action-card:hover strong,
.ev-home-actions-panel .ev-home-action-card:focus-visible strong,
.ev-home-actions-panel .ev-home-action-card:hover .ev-home-action-chevron,
.ev-home-actions-panel .ev-home-action-card:focus-visible .ev-home-action-chevron{
  color:var(--ev-naranja);
}

.ev-home-community-card{
  border-color:rgba(148,163,184,.16);
  box-shadow:0 8px 18px rgba(15,23,42,.045);
}

.ev-home-community-card:hover,
.ev-home-community-card:focus-within{
  border-color:rgba(234,124,18,.28);
  box-shadow:0 14px 30px rgba(15,23,42,.075);
}


/* ============================================================
   AJUSTE FINAL EV - DASHBOARD LIMPIO + CARD COMUNIDAD CLICKEABLE
============================================================ */
.ev-home-community-card[role="button"]{
  cursor:pointer;
}

.ev-home-community-card[role="button"] .ev-home-community-thumb,
.ev-home-community-card[role="button"] .ev-home-community-body{
  pointer-events:none;
}

.ev-home-community-card[role="button"] .ev-home-mini-action{
  pointer-events:none;
}

.ev-home-community-card[role="button"]:hover,
.ev-home-community-card[role="button"]:focus-visible{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.36);
  box-shadow:
    0 16px 34px rgba(15,23,42,.09),
    0 10px 22px rgba(234,124,18,.10);
  outline:0;
}

.ev-home-community-card[role="button"]:hover .ev-home-mini-action,
.ev-home-community-card[role="button"]:focus-visible .ev-home-mini-action{
  background:linear-gradient(135deg,var(--ev-naranja-oscuro),var(--ev-naranja));
  box-shadow:0 14px 28px rgba(234,124,18,.30);
  transform:translateY(-1px);
}

.ev-home-community-card[role="button"]:focus-visible{
  box-shadow:
    0 0 0 3px rgba(234,124,18,.18),
    0 16px 34px rgba(15,23,42,.09),
    0 10px 22px rgba(234,124,18,.10);
}

/* Clase lista para el módulo Comunidad: usarla al leer
   sessionStorage.ev_comunidad_publicacion_seleccionada o ?publicacion=ID. */
.ev-comunidad-publicacion--seleccionada,
.ev-comunidad-card--seleccionada,
[data-publicacion-seleccionada="1"]{
  border-color:rgba(234,124,18,.70) !important;
  box-shadow:
    0 0 0 4px rgba(234,124,18,.14),
    0 18px 40px rgba(234,124,18,.16),
    0 16px 36px rgba(15,23,42,.08) !important;
  animation:evComunidadSeleccionadaPulse 1.35s ease-in-out 2;
}

@keyframes evComunidadSeleccionadaPulse{
  0%{ transform:translateY(0); }
  45%{ transform:translateY(-3px); }
  100%{ transform:translateY(0); }
}



/* ============================================================
   AJUSTE EV - CARD COMUNIDAD SIN BOTÓN INTERNO
   La card completa es la acción hacia /comunidad?publicacion=ID.
============================================================ */
.ev-home-community-card[role="button"]{
  cursor:pointer;
}

.ev-home-community-card[role="button"]::after{
  content:"";
  position:absolute;
  inset:0;
  border-radius:inherit;
  pointer-events:none;
  opacity:0;
  background:linear-gradient(135deg,rgba(234,124,18,.055),rgba(255,255,255,0));
  transition:opacity .16s ease;
}

.ev-home-community-card[role="button"]:hover::after,
.ev-home-community-card[role="button"]:focus-visible::after{
  opacity:1;
}

.ev-home-community-card[role="button"]:hover,
.ev-home-community-card[role="button"]:focus-visible{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.36);
  box-shadow:
    0 16px 34px rgba(15,23,42,.09),
    0 10px 22px rgba(234,124,18,.10);
  outline:0;
}

.ev-home-community-card[role="button"]:focus-visible{
  box-shadow:
    0 0 0 3px rgba(234,124,18,.18),
    0 16px 34px rgba(15,23,42,.09),
    0 10px 22px rgba(234,124,18,.10);
}

.ev-home-community-card[role="button"] .ev-home-community-foot{
  justify-content:flex-start;
}


/* ============================================================
   POLÍTICA VISUAL GLOBAL DE MODALES EV
   Evita bordes blancos en las esquinas superiores y unifica el recorte.
============================================================ */
.modal .modal-content{
  border:0!important;
  background-clip:border-box!important;
  overflow:hidden!important;
}
.modal .modal-header{
  background-clip:border-box!important;
  border-top-left-radius:inherit!important;
  border-top-right-radius:inherit!important;
}

/* Ayuda EV */
.ev-help-modal-popup{
  width:min(92vw,560px)!important;
  border:0!important;
  border-radius:26px!important;
  padding:30px 28px 24px!important;
  overflow:hidden!important;
  box-shadow:0 34px 76px rgba(15,23,42,.24),0 10px 24px rgba(15,23,42,.10)!important;
  background:linear-gradient(180deg,#FFFFFF 0%,#FBFDFB 100%)!important;
}
.ev-help-modal-popup::before{
  content:"";
  position:absolute;
  inset:0 0 auto;
  height:5px;
  background:linear-gradient(90deg,#0F592F,#16A34A,#EA7C12);
}
.ev-help-modal-title{color:#0F592F!important;font-weight:900!important;letter-spacing:-.025em!important;font-size:clamp(1.75rem,4vw,2.15rem)!important}
.ev-help-modal-html{margin-top:8px!important;color:#4B5563!important}
.ev-help-modal-icon{width:76px;height:76px;margin:4px auto 16px;display:grid;place-items:center;border-radius:24px;background:linear-gradient(145deg,#ECFDF3,#FFFFFF);border:1px solid rgba(22,163,74,.24);color:#0F592F;font-size:2rem;box-shadow:0 16px 32px rgba(15,89,47,.11)}
.ev-help-modal-copy{display:grid;justify-items:center;gap:10px;line-height:1.55;text-align:center}
.ev-help-modal-copy>strong{color:#0F592F;font-size:1rem;font-weight:900}
.ev-help-modal-copy>p{max-width:420px;margin:0;color:#5B6474}
.ev-help-modal-contact{display:inline-flex;align-items:center;gap:9px;padding:10px 16px;border-radius:999px;background:#F0FDF4;border:1px solid rgba(22,163,74,.22);color:#0E7A43!important;font-weight:900;text-decoration:none!important;transition:.16s ease}
.ev-help-modal-contact:hover{transform:translateY(-1px);border-color:rgba(234,124,18,.40);color:#C46B05!important;box-shadow:0 10px 22px rgba(234,124,18,.12)}
.ev-help-modal-confirm{min-width:150px!important;border-radius:14px!important;font-weight:900!important;box-shadow:0 12px 24px rgba(234,124,18,.22)!important}

</style>
