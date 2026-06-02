<style>
/* =========================================
   ENTRE VECINOS - LAYOUT + HOME DASHBOARD V3
   Cierre premium:
   - Hero contextual por comunidad
   - Comunidad próximamente coherente
   - Estados semánticos de actividad
   - Responsive validado para desktop/tablet/móvil
========================================= */

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

body{
  background:var(--ev-gris-fondo);
}

.wrapper{
  min-height:100vh;
}

.main-container{
  margin-left:0;
  padding-top:var(--ev-topbar-h);
  min-height:100vh;
  overflow-x:hidden;
  min-width:0;
}

@media (max-width:991.98px){
  .main-container{
    margin-left:0;
    padding-top:var(--ev-topbar-h);
    min-width:0;
  }
}

/* ================================
   HOME DASHBOARD
================================ */
.ev-home-dashboard-v2{
  width:100%;
  padding:18px 18px 30px;
  color:var(--ev-texto);
}

.ev-home-dashboard-v2 *{
  box-sizing:border-box;
}

.ev-home-hero,
.ev-home-panel,
.ev-home-summary-card{
  background:#fff;
  border:1px solid rgba(148,163,184,.16);
  box-shadow:var(--ev-shadow);
}

/* ================================
   HERO PRINCIPAL
================================ */
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
    linear-gradient(135deg, #FFFFFF 0%, #FFFFFF 54%, #F7FEFA 100%);
}

.ev-home-hero--urbanizacion{
  background:
    radial-gradient(circle at 88% 22%, rgba(22,163,74,.14), transparent 34%),
    radial-gradient(circle at 68% 82%, rgba(234,124,18,.045), transparent 28%),
    linear-gradient(135deg, #FFFFFF 0%, #FFFFFF 53%, #F5FDF8 100%);
}

.ev-home-hero--condominio{
  background:
    radial-gradient(circle at 88% 22%, rgba(37,99,235,.055), transparent 30%),
    radial-gradient(circle at 78% 75%, rgba(22,163,74,.10), transparent 34%),
    linear-gradient(135deg, #FFFFFF 0%, #FFFFFF 54%, #F7FBF9 100%);
}

.ev-home-hero::before{
  content:"";
  position:absolute;
  inset:0 0 auto 0;
  height:4px;
  background:linear-gradient(90deg,var(--ev-verde-oscuro),var(--ev-verde-claro),var(--ev-naranja));
}

.ev-home-dashboard-v2 .ev-home-hero::after{
  display:none;
}

.ev-home-hero-copy{
  position:relative;
  z-index:2;
  max-width:640px;
}

.ev-home-kicker{
  font-size:.78rem;
  font-weight:900;
  letter-spacing:.14em;
  text-transform:uppercase;
  color:var(--ev-naranja);
  margin-bottom:4px;
}

.ev-home-hero h1{
  margin:0 0 6px;
  color:var(--ev-verde-oscuro);
  font-size:clamp(1.62rem, 2.6vw, 2.18rem);
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
  min-width:min(44vw, 640px);
  min-height:128px;
  display:flex;
  align-items:flex-start;
  justify-content:flex-end;
}

.ev-home-hero-art{
  position:absolute;
  inset:auto 0 -12px auto;
  width:min(42vw, 560px);
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
  background:linear-gradient(180deg, rgba(187,247,208,.52), rgba(220,252,231,.18));
}

.ev-hero-cloud{
  position:absolute;
  top:16px;
  width:66px;
  height:22px;
  border-radius:999px;
  background:rgba(255,255,255,.86);
  box-shadow:
    16px -8px 0 2px rgba(255,255,255,.82),
    36px 0 0 -3px rgba(255,255,255,.75);
}

.ev-hero-cloud-1{
  left:70px;
  transform:scale(.88);
}

.ev-hero-cloud-2{
  right:70px;
  top:30px;
  transform:scale(.74);
}

.ev-hero-ground{
  position:absolute;
  left:18px;
  right:18px;
  bottom:4px;
  height:14px;
  border-radius:999px;
  background:linear-gradient(90deg, rgba(15,89,47,.16), rgba(22,163,74,.22), rgba(234,124,18,.12));
}

.ev-hero-tree{
  position:absolute;
  bottom:14px;
  width:14px;
  height:42px;
  border-radius:999px;
  background:linear-gradient(180deg, #16A34A, #0F592F);
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

.ev-hero-tree-1{
  left:118px;
}

.ev-hero-tree-2{
  right:132px;
  height:50px;
}

/* Edificios */
.ev-hero-building{
  position:absolute;
  bottom:18px;
  border-radius:7px 7px 2px 2px;
  background:linear-gradient(180deg, rgba(148,163,184,.23), rgba(148,163,184,.12));
  box-shadow:0 10px 18px rgba(15,23,42,.04);
}

.ev-hero-building::before{
  content:"";
  position:absolute;
  inset:12px 10px auto 10px;
  height:64px;
  background:
    linear-gradient(90deg, rgba(255,255,255,.74) 0 8px, transparent 8px 18px) 0 0/24px 18px,
    linear-gradient(90deg, rgba(255,255,255,.50) 0 8px, transparent 8px 18px) 0 22px/24px 18px,
    linear-gradient(90deg, rgba(255,255,255,.58) 0 8px, transparent 8px 18px) 0 44px/24px 18px;
}

.ev-hero-building-1{
  width:76px;
  height:92px;
  right:184px;
}

.ev-hero-building-2{
  width:62px;
  height:74px;
  right:112px;
  opacity:.72;
}

.ev-hero-building-3{
  display:none;
  width:68px;
  height:106px;
  right:272px;
  opacity:.8;
}

.ev-hero-common-area{
  display:none;
  position:absolute;
  right:34px;
  bottom:18px;
  width:62px;
  height:26px;
  border-radius:14px 14px 4px 4px;
  border:1px solid rgba(15,89,47,.14);
  background:linear-gradient(180deg, rgba(255,255,255,.94), rgba(236,253,245,.82));
}

.ev-hero-common-area::before{
  content:"";
  position:absolute;
  left:11px;
  right:11px;
  top:-9px;
  height:12px;
  border-radius:999px 999px 3px 3px;
  background:linear-gradient(135deg, rgba(15,89,47,.72), rgba(22,163,74,.62));
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

/* Casas */
.ev-hero-house{
  position:absolute;
  bottom:16px;
  width:88px;
  height:58px;
  border-radius:8px 8px 4px 4px;
  background:linear-gradient(180deg, #FFFDF8, #F1F8EF);
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
  background:linear-gradient(135deg, #0F592F 0%, #16A34A 100%);
  box-shadow:0 7px 12px rgba(15,23,42,.08);
  clip-path:polygon(50% 0%, 100% 68%, 88% 68%, 88% 100%, 12% 100%, 12% 68%, 0% 68%);
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

.ev-hero-house-1{
  left:34px;
  transform:scale(.92);
}

.ev-hero-house-2{
  left:150px;
  bottom:18px;
  transform:scale(1.02);
}

.ev-hero-house-3{
  right:28px;
  transform:scale(.86);
}

/* Acceso controlado para urbanización */
.ev-hero-access{
  display:none;
  position:absolute;
  right:25px;
  bottom:16px;
  width:78px;
  height:50px;
  border-radius:8px 8px 3px 3px;
  border:1px solid rgba(15,89,47,.16);
  background:linear-gradient(180deg, #FFFDF8, #EDF8EF);
  box-shadow:0 10px 18px rgba(15,23,42,.07);
}

.ev-hero-access-roof{
  position:absolute;
  left:-5px;
  right:-5px;
  top:-12px;
  height:15px;
  border-radius:8px 8px 3px 3px;
  background:linear-gradient(135deg, #0F592F, #16A34A);
}

.ev-hero-access-door{
  position:absolute;
  left:13px;
  bottom:0;
  width:15px;
  height:26px;
  border-radius:5px 5px 0 0;
  background:rgba(15,89,47,.24);
}

.ev-hero-access-barrier{
  position:absolute;
  left:-34px;
  bottom:15px;
  width:40px;
  height:4px;
  border-radius:999px;
  transform:rotate(-8deg);
  transform-origin:right center;
  background:linear-gradient(90deg, var(--ev-naranja), #F59E0B);
  box-shadow:0 0 0 1px rgba(234,124,18,.12);
}

/* Variante urbanización */
.ev-home-hero--urbanizacion .ev-hero-building,
.ev-home-hero--urbanizacion .ev-hero-common-area{
  display:none;
}

.ev-home-hero--urbanizacion .ev-hero-access{
  display:block;
}

.ev-home-hero--urbanizacion .ev-hero-house-1{
  left:22px;
  transform:scale(.88);
}

.ev-home-hero--urbanizacion .ev-hero-house-2{
  left:135px;
  transform:scale(.96);
}

.ev-home-hero--urbanizacion .ev-hero-house-3{
  left:252px;
  right:auto;
  transform:scale(.88);
}

.ev-home-hero--urbanizacion .ev-hero-tree-1{
  left:116px;
}

.ev-home-hero--urbanizacion .ev-hero-tree-2{
  right:112px;
}

/* Variante condominio */
.ev-home-hero--condominio .ev-hero-house,
.ev-home-hero--condominio .ev-hero-access{
  display:none;
}

.ev-home-hero--condominio .ev-hero-building-1{
  right:156px;
  height:98px;
  width:80px;
}

.ev-home-hero--condominio .ev-hero-building-2{
  right:72px;
  height:84px;
  width:68px;
  opacity:.82;
}

.ev-home-hero--condominio .ev-hero-building-3{
  display:block;
}

.ev-home-hero--condominio .ev-hero-common-area{
  display:block;
}

.ev-home-hero--condominio .ev-hero-tree-1{
  left:auto;
  right:246px;
  height:44px;
}

.ev-home-hero--condominio .ev-hero-tree-2{
  right:18px;
  height:42px;
}

/* Contexto de comunidad vive en sidebar */
.ev-home-hero-community,
#evHomeCommunityHero{
  display:none !important;
}

/* ================================
   SUMMARY CARDS
================================ */
.ev-home-summary-grid{
  display:grid;
  grid-template-columns:repeat(4, minmax(180px, 1fr));
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
  background:#fff;
  overflow:hidden;
  transition:
    transform .16s ease,
    box-shadow .16s ease,
    border-color .16s ease;
}

.ev-home-summary-card::after{
  content:"";
  position:absolute;
  left:18px;
  right:18px;
  bottom:0;
  height:3px;
  border-radius:999px 999px 0 0;
  background:linear-gradient(90deg, rgba(15,89,47,.42), rgba(234,124,18,.34));
  opacity:0;
  transform:scaleX(.82);
  transform-origin:center;
  transition:opacity .16s ease, transform .16s ease;
}

.ev-home-summary-card:hover{
  background:#fff;
  transform:translateY(-2px);
  box-shadow:0 22px 48px rgba(15,23,42,.10);
  border-color:rgba(234,124,18,.22);
}

.ev-home-summary-card:hover::after{
  opacity:1;
  transform:scaleX(1);
}

.ev-home-summary-card:hover .ev-home-summary-icon{
  transform:scale(1.035);
}

.ev-home-summary-icon{
  width:58px;
  height:58px;
  border-radius:22px;
  display:grid;
  place-items:center;
  font-size:1.42rem;
  flex:0 0 auto;
  transition:transform .16s ease, box-shadow .16s ease;
}

.ev-home-summary-green .ev-home-summary-icon,
.ev-home-summary-wallet .ev-home-summary-icon{
  background:linear-gradient(135deg, rgba(187,247,208,.72), rgba(236,253,245,.96));
  color:var(--ev-verde-oscuro);
}

.ev-home-summary-orange .ev-home-summary-icon{
  background:linear-gradient(135deg, rgba(255,237,213,.95), rgba(255,247,237,.98));
  color:var(--ev-naranja);
}

.ev-home-summary-purple .ev-home-summary-icon{
  background:linear-gradient(135deg, rgba(243,232,255,.95), rgba(250,245,255,.98));
  color:var(--ev-morado);
}

.ev-home-summary-body{
  min-width:0;
  flex:1 1 auto;
}

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

.ev-home-summary-orange .ev-home-summary-body strong{
  color:var(--ev-naranja);
}

.ev-home-summary-purple .ev-home-summary-body strong{
  color:var(--ev-morado);
}

.ev-home-summary-body small{
  display:block;
  color:var(--ev-gris-500);
  font-size:.82rem;
  line-height:1.28;
  min-height:34px;
}

.ev-home-link-btn,
.ev-home-panel-action,
.ev-home-mini-action{
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
  transition:gap .16s ease, color .16s ease, transform .16s ease;
}

.ev-home-summary-orange .ev-home-link-btn{
  color:var(--ev-naranja-oscuro);
}

.ev-home-summary-purple .ev-home-link-btn{
  color:var(--ev-morado);
}

.ev-home-link-btn:hover,
.ev-home-panel-action:hover,
.ev-home-mini-action:hover{
  gap:11px;
  color:var(--ev-naranja);
  transform:translateY(-1px);
}

/* ================================
   MAIN GRID
================================ */
.ev-home-main-grid{
  display:grid;
  grid-template-columns:minmax(0, 1.05fr) minmax(360px, .95fr);
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
  width:34px;
  height:34px;
  display:grid;
  place-items:center;
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

.ev-home-panel-action{
  margin:0;
  white-space:nowrap;
}

.ev-home-panel-action--pending{
  gap:7px;
  padding:7px 12px;
  border:1px solid rgba(148,163,184,.22);
  border-radius:999px;
  background:var(--ev-gris-050);
  color:var(--ev-gris-500);
  font-size:.76rem;
}

.ev-home-panel-action--pending:hover{
  gap:7px;
  color:var(--ev-naranja-oscuro);
  background:#FFF7ED;
  border-color:rgba(234,124,18,.22);
  transform:none;
}

/* ================================
   ACTIVITY
================================ */
.ev-home-activity-list{
  border:1px solid rgba(229,231,235,.86);
  border-radius:18px;
  background:linear-gradient(180deg,#fff 0%, #fbfcfb 100%);
  overflow:hidden;
}

.ev-home-activity-item{
  display:grid;
  grid-template-columns:42px minmax(0,1fr) auto;
  gap:12px;
  align-items:center;
  padding:13px 14px;
  border-bottom:1px solid rgba(229,231,235,.72);
}

.ev-home-activity-item:last-child{
  border-bottom:none;
}

.ev-home-activity-icon{
  width:36px;
  height:36px;
  border-radius:50%;
  display:grid;
  place-items:center;
  color:#fff;
  font-size:.98rem;
  box-shadow:0 8px 16px rgba(15,23,42,.08);
}

.ev-home-activity-icon.is-verde{
  background:linear-gradient(135deg,#16A34A,#0F8E48);
}

.ev-home-activity-icon.is-naranja{
  background:linear-gradient(135deg,#EA7C12,#F59E0B);
}

.ev-home-activity-icon.is-morado{
  background:linear-gradient(135deg,#9333EA,#A855F7);
}

.ev-home-activity-icon.is-azul{
  background:linear-gradient(135deg,#2563EB,#38BDF8);
}

.ev-home-activity-icon.is-rojo{
  background:linear-gradient(135deg,#DC2626,#EF4444);
}

.ev-home-activity-icon.is-gris{
  background:linear-gradient(135deg,#9CA3AF,#6B7280);
}

.ev-home-activity-copy{
  min-width:0;
}

.ev-home-activity-copy strong{
  display:block;
  color:#182033;
  font-weight:900;
  font-size:.86rem;
  line-height:1.2;
  margin-bottom:3px;
}

.ev-home-activity-copy p{
  margin:0;
  color:var(--ev-gris-500);
  font-size:.80rem;
  line-height:1.28;
}

.ev-home-activity-item time{
  color:var(--ev-gris-500);
  font-size:.76rem;
  font-weight:750;
  white-space:nowrap;
}

/* ================================
   ACTIONS
================================ */
.ev-home-actions-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0, 1fr));
  gap:10px;
}

.ev-home-action-card{
  position:relative;
  min-height:76px;
  border:1px solid rgba(229,231,235,.92);
  border-radius:16px;
  background:linear-gradient(180deg,#fff 0%, #fbfdfb 100%);
  padding:12px 40px 12px 52px;
  text-align:left;
  box-shadow:0 8px 18px rgba(15,23,42,.04);
  transition:
    transform .16s ease,
    box-shadow .16s ease,
    border-color .16s ease,
    background .16s ease,
    color .16s ease;
}

.ev-home-action-card:hover,
.ev-home-action-card:focus-visible{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.46);
  box-shadow:var(--ev-shadow-orange);
  background:
    radial-gradient(circle at 12% 18%, rgba(255,237,213,.92), transparent 34%),
    linear-gradient(135deg, #FFFFFF 0%, #FFF7ED 100%);
  outline:0;
}

.ev-home-action-card > span{
  position:absolute;
  left:12px;
  top:14px;
  width:30px;
  height:30px;
  border-radius:12px;
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  background:#ECFDF3;
  border:1px solid rgba(22,163,74,.14);
  transition:background .16s ease, color .16s ease, transform .16s ease, border-color .16s ease;
}

.ev-home-action-card:hover > span,
.ev-home-action-card:focus-visible > span{
  color:#fff;
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border-color:rgba(234,124,18,.36);
  transform:scale(1.045);
}

.ev-home-action-card strong{
  display:block;
  color:#182033;
  font-size:.84rem;
  font-weight:950;
  line-height:1.18;
  transition:color .16s ease;
}

.ev-home-action-card:hover strong,
.ev-home-action-card:focus-visible strong{
  color:var(--ev-naranja-oscuro);
}

.ev-home-action-card small{
  display:block;
  color:var(--ev-gris-500);
  font-size:.75rem;
  line-height:1.22;
  margin-top:3px;
}

.ev-home-action-chevron{
  position:absolute;
  right:13px;
  top:50%;
  transform:translateY(-50%);
  color:var(--ev-verde-oscuro);
  transition:color .16s ease, transform .16s ease;
}

.ev-home-action-card:hover .ev-home-action-chevron,
.ev-home-action-card:focus-visible .ev-home-action-chevron{
  color:var(--ev-naranja);
  transform:translate(3px,-50%);
}

/* Acción próxima fase */
.ev-home-action-card--pending{
  border-style:dashed;
  border-color:rgba(148,163,184,.38);
  background:linear-gradient(180deg, #FCFDFC 0%, #F9FAFB 100%);
  box-shadow:none;
}

.ev-home-action-card--pending > span{
  color:var(--ev-gris-500);
  background:var(--ev-gris-100);
  border-color:rgba(148,163,184,.24);
}

.ev-home-action-heading{
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:6px;
  min-width:0;
}

.ev-home-action-heading strong{
  display:inline-block;
}

.ev-home-action-heading em{
  display:inline-flex;
  align-items:center;
  border-radius:999px;
  padding:3px 7px;
  font-style:normal;
  font-size:.62rem;
  line-height:1;
  font-weight:900;
  letter-spacing:.04em;
  text-transform:uppercase;
  color:#92400E;
  background:#FFF7ED;
  border:1px solid rgba(234,124,18,.18);
}

.ev-home-action-card--pending .ev-home-action-chevron{
  color:var(--ev-gris-400);
}

.ev-home-action-card--pending:hover,
.ev-home-action-card--pending:focus-visible{
  transform:none;
  border-color:rgba(234,124,18,.28);
  box-shadow:0 12px 26px rgba(15,23,42,.05);
  background:linear-gradient(180deg, #FFFFFF 0%, #FFFDF9 100%);
}

.ev-home-action-card--pending:hover > span,
.ev-home-action-card--pending:focus-visible > span{
  transform:none;
  color:var(--ev-naranja-oscuro);
  background:#FFF7ED;
  border-color:rgba(234,124,18,.18);
}

.ev-home-action-card--pending:hover strong,
.ev-home-action-card--pending:focus-visible strong{
  color:#182033;
}

.ev-home-action-card--pending:hover .ev-home-action-chevron,
.ev-home-action-card--pending:focus-visible .ev-home-action-chevron{
  color:var(--ev-naranja);
  transform:translateY(-50%);
}

/* ================================
   COMMUNITY
================================ */
.ev-home-community-panel,
.ev-home-publications-panel{
  margin-top:14px;
}

.ev-home-community-strip{
  display:grid;
  grid-template-columns:1fr;
  gap:12px;
}

.ev-home-community-empty,
.ev-home-empty-state{
  display:flex;
  gap:13px;
  align-items:center;
  min-height:96px;
  border-radius:18px;
  padding:16px;
  background:linear-gradient(135deg, rgba(236,253,245,.92), rgba(255,255,255,.98));
  border:1px dashed rgba(15,89,47,.28);
}

.ev-home-empty-icon{
  width:54px;
  height:54px;
  border-radius:20px;
  display:grid;
  place-items:center;
  flex:0 0 auto;
  color:var(--ev-verde-oscuro);
  background:linear-gradient(135deg, rgba(187,247,208,.72), rgba(255,255,255,.98));
  border:1px solid rgba(22,163,74,.15);
  font-size:1.3rem;
}

.ev-home-community-empty strong,
.ev-home-empty-state strong{
  display:block;
  color:var(--ev-verde-oscuro);
  font-size:.94rem;
  font-weight:950;
  margin-bottom:4px;
}

.ev-home-community-empty p,
.ev-home-empty-state p{
  margin:0;
  color:var(--ev-gris-500);
  font-size:.84rem;
  line-height:1.42;
}

/* ================================
   PUBLICATIONS
================================ */
.ev-home-publications-grid{
  display:grid;
  grid-template-columns:repeat(5, minmax(160px, 1fr));
  gap:12px;
}

.ev-home-publication-card{
  min-width:0;
  border:1px solid rgba(229,231,235,.92);
  border-radius:18px;
  background:#fff;
  overflow:hidden;
  box-shadow:0 10px 22px rgba(15,23,42,.05);
  transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}

.ev-home-publication-card:hover{
  transform:translateY(-2px);
  border-color:rgba(22,163,74,.22);
  box-shadow:0 16px 32px rgba(15,23,42,.09);
}

.ev-home-publication-img{
  position:relative;
  height:116px;
  background:#F3F4F6;
  overflow:hidden;
}

.ev-home-publication-img img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-home-publication-img span{
  position:absolute;
  top:10px;
  left:10px;
  border-radius:999px;
  padding:4px 9px;
  color:#fff;
  font-size:.68rem;
  font-weight:900;
  background:linear-gradient(135deg,#16A34A,#0E7A43);
  box-shadow:0 8px 16px rgba(15,23,42,.14);
}

.ev-home-publication-body{
  padding:11px 12px 12px;
}

.ev-home-publication-body h3{
  min-height:40px;
  margin:0 0 7px;
  color:#182033;
  font-size:.86rem;
  line-height:1.18;
  font-weight:950;
}

.ev-home-publication-body > strong{
  display:block;
  color:var(--ev-verde-oscuro);
  font-size:.96rem;
  font-weight:950;
  margin-bottom:4px;
}

.ev-home-publication-body p{
  min-height:34px;
  margin:0 0 8px;
  color:var(--ev-gris-500);
  font-size:.75rem;
  line-height:1.32;
}

.ev-home-publication-foot{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  color:var(--ev-gris-500);
  font-size:.72rem;
  line-height:1.1;
}

.ev-home-publication-foot span,
.ev-home-publication-foot small{
  display:inline-flex;
  align-items:center;
  gap:4px;
  min-width:0;
}

.ev-home-publication-foot small i{
  color:var(--ev-naranja);
}

.ev-home-publications-empty{
  grid-column:1 / -1;
}

.ev-home-mini-action{
  margin-top:9px;
  padding:8px 13px;
  border-radius:999px;
  background:linear-gradient(135deg,var(--ev-verde-oscuro),var(--ev-verde));
  color:#fff;
  box-shadow:0 10px 20px rgba(15,89,47,.16);
}

.ev-home-mini-action:hover{
  color:#fff;
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
}

/* ================================
   SKELETON + ERROR
================================ */
.ev-home-skeleton-line,
.ev-home-skeleton-card{
  border-radius:16px;
  background:linear-gradient(90deg, #F3F4F6 0%, #FFFFFF 50%, #F3F4F6 100%);
  background-size:200% 100%;
  animation:evHomeSkeleton 1.2s ease-in-out infinite;
}

.ev-home-skeleton-line{
  height:58px;
  margin:10px;
}

.ev-home-skeleton-card{
  height:210px;
}

@keyframes evHomeSkeleton{
  0%{
    background-position:200% 0;
  }
  100%{
    background-position:-200% 0;
  }
}

.ev-home-error{
  margin-top:14px;
  border-radius:18px;
  padding:14px 16px;
  color:#991B1B;
  background:#FEF2F2;
  border:1px solid #FECACA;
  font-weight:800;
}

/* ================================
   RESPONSIVE
================================ */
@media (max-width:1399.98px){
  .ev-home-publications-grid{
    grid-template-columns:repeat(4, minmax(160px, 1fr));
  }
}

@media (max-width:1199.98px){
  .ev-home-summary-grid{
    grid-template-columns:repeat(2, minmax(220px, 1fr));
  }

  .ev-home-main-grid{
    grid-template-columns:1fr;
  }

  .ev-home-publications-grid{
    grid-template-columns:repeat(3, minmax(160px, 1fr));
  }

  .ev-home-hero-side{
    min-width:360px;
  }

  .ev-home-hero-art{
    width:360px;
  }

  .ev-home-hero--urbanizacion .ev-hero-house-3{
    display:none;
  }

  .ev-home-hero--urbanizacion .ev-hero-house-1{
    left:18px;
  }

  .ev-home-hero--urbanizacion .ev-hero-house-2{
    left:124px;
  }

  .ev-home-hero--condominio .ev-hero-building-3{
    right:224px;
    transform:scale(.88);
    transform-origin:bottom right;
  }
}

@media (max-width:991.98px){
  .ev-home-dashboard-v2{
    padding:14px 12px 24px;
  }

  .ev-home-hero{
    align-items:flex-start;
    flex-direction:column;
    padding:22px 18px;
  }

  .ev-home-hero-side{
    width:100%;
    min-width:0;
    min-height:128px;
    justify-content:flex-start;
  }

  .ev-home-hero-art{
    width:min(100%, 440px);
    right:auto;
    left:0;
    opacity:.72;
  }
}

@media (max-width:767.98px){
  .ev-home-summary-grid{
    grid-template-columns:1fr;
  }

  .ev-home-summary-card{
    min-height:0;
  }

  .ev-home-actions-grid{
    grid-template-columns:1fr;
  }

  .ev-home-publications-grid{
    grid-template-columns:repeat(2, minmax(140px, 1fr));
  }

  .ev-home-activity-item{
    grid-template-columns:38px minmax(0,1fr);
  }

  .ev-home-activity-item time{
    grid-column:2;
    justify-self:start;
    margin-top:-4px;
  }
}

@media (max-width:575.98px){
  .ev-home-dashboard-v2{
    padding:10px 10px 22px;
  }

  .ev-home-hero,
  .ev-home-panel{
    border-radius:18px;
  }

  .ev-home-hero{
    min-height:0;
  }

  .ev-home-hero h1{
    font-size:1.52rem;
  }

  .ev-home-hero-side{
    display:none;
  }

  .ev-home-summary-card{
    padding:15px;
  }

  .ev-home-summary-icon{
    width:52px;
    height:52px;
    border-radius:18px;
  }

  .ev-home-summary-body strong{
    font-size:1.72rem;
  }

  .ev-home-panel-head{
    align-items:flex-start;
  }

  .ev-home-panel-action--pending{
    padding:6px 10px;
  }

  .ev-home-publications-grid{
    grid-template-columns:1fr;
  }

  .ev-home-publication-img{
    height:170px;
  }

  .ev-home-community-empty,
  .ev-home-empty-state{
    align-items:flex-start;
    padding:14px;
  }
}
</style>