<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.3/index.min.css">

<style>
/* ===================================================
   ENTRE VECINOS - CUENTA OBSERVADA / VALIDACIÓN
   UX/UI premium EV - versión final
=================================================== */

:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#0E7A43;
  --ev-verde-claro:#16A34A;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;
  --ev-naranja-suave:#FFF7ED;
  --ev-naranja-borde:#FED7AA;

  --ev-fondo:#F7F8FA;
  --ev-blanco:#FFFFFF;

  --ev-gris-050:#F9FAFB;
  --ev-gris-100:#F3F4F6;
  --ev-gris-200:#E5E7EB;
  --ev-gris-300:#D1D5DB;
  --ev-gris-400:#9CA3AF;
  --ev-gris-500:#6B7280;
  --ev-gris-600:#4B5563;
  --ev-gris-700:#374151;

  --ev-texto:#111827;
  --ev-azul-texto:#536179;

  --ev-shadow-page:
    0 24px 70px rgba(15,23,42,.08),
    0 8px 22px rgba(15,23,42,.04);

  --ev-shadow-card:
    0 14px 34px rgba(15,23,42,.055),
    0 2px 8px rgba(15,23,42,.035);

  --ev-shadow-soft:
    0 8px 20px rgba(15,23,42,.045),
    0 1px 3px rgba(15,23,42,.025);

  --ev-shadow-orange:
    0 14px 30px rgba(234,124,18,.30);

  --ev-shadow-green:
    0 12px 26px rgba(14,122,67,.18);

  --ev-radius-xl:30px;
  --ev-radius-lg:24px;
  --ev-radius-md:18px;
  --ev-radius-sm:14px;
  --ev-radius-btn:13px;
}

*{
  box-sizing:border-box;
}

html,
body{
  margin:0;
  padding:0;
  min-height:100%;
}

body.ev-co-page{
  min-height:100vh;
  padding:22px;
  color:var(--ev-texto);
  font-family:'Poppins', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
  background:#F7F8FA !important;
  overflow-x:hidden;
}

/* ===================================================
   LAYOUT GENERAL
=================================================== */

.ev-co-shell{
  min-height:calc(100vh - 44px);
  display:flex;
  align-items:flex-start;
  justify-content:center;
  padding-top:clamp(64px, 11vh, 118px);
  padding-bottom:44px;
}

.ev-co-wrap{
  width:100%;
  max-width:1120px;
}

.ev-brand-pill{
  width:max-content;
  margin:0 auto 14px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:9px 20px 9px 11px;
  border-radius:999px;
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.95);
  color:var(--ev-verde-oscuro);
  font-weight:800;
  font-size:.92rem;
  box-shadow:var(--ev-shadow-soft);
  user-select:none;
}

.ev-brand-icon{
  width:38px;
  height:38px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:#FFFFFF;
  border:1px solid rgba(22,163,74,.16);
  color:var(--ev-verde-oscuro);
  font-size:1.04rem;
  overflow:hidden;
}

.ev-brand-logo{
  width:29px;
  height:29px;
  display:block;
  object-fit:contain;
  border-radius:8px;
}

/* ===================================================
   CARD PRINCIPAL
=================================================== */

.ev-co-card{
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.90);
  border-radius:var(--ev-radius-xl);
  box-shadow:var(--ev-shadow-page);
  overflow:hidden;
}

.ev-co-card-inner{
  padding:38px;
  background:#FFFFFF;
}

/* ===================================================
   HERO
=================================================== */

.ev-co-hero{
  display:grid;
  grid-template-columns:116px minmax(0, 1fr);
  align-items:center;
  gap:24px;
  margin-bottom:22px;
}

.ev-co-hero-compact{
  grid-template-columns:102px minmax(0, 1fr);
  gap:22px;
  margin-bottom:20px;
}

.ev-hero-visual{
  position:relative;
  width:108px;
  height:108px;
  display:grid;
  place-items:center;
}

.ev-co-hero-compact .ev-hero-visual{
  width:96px;
  height:96px;
}

.ev-hero-visual::before{
  content:"";
  position:absolute;
  inset:0;
  border-radius:50%;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.18);
}

.ev-hero-orb{
  position:relative;
  z-index:2;
  width:64px;
  height:64px;
  display:grid;
  place-items:center;
  border-radius:22px;
  background:#FFFFFF;
  color:var(--ev-verde-oscuro);
  border:1px solid rgba(22,163,74,.24);
  font-size:1.72rem;
  box-shadow:0 8px 18px rgba(15,89,47,.055);
}

.ev-co-hero-compact .ev-hero-orb{
  width:58px;
  height:58px;
  border-radius:20px;
  font-size:1.55rem;
}

.ev-hero-orb.is-warning{
  color:#9A3412;
  border-color:rgba(234,124,18,.28);
}

.ev-hero-check{
  position:absolute;
  top:8px;
  right:10px;
  z-index:3;
  width:32px;
  height:32px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  color:#FFFFFF;
  border:3px solid #FFFFFF;
  box-shadow:var(--ev-shadow-green);
  font-size:.86rem;
}

.ev-hero-check.is-warning{
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  box-shadow:0 10px 22px rgba(234,124,18,.22);
}

.ev-eyebrow{
  display:inline-flex;
  align-items:center;
  width:max-content;
  padding:7px 13px;
  margin-bottom:9px;
  border-radius:999px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.16);
  color:var(--ev-verde-oscuro);
  font-size:.76rem;
  font-weight:800;
  letter-spacing:.035em;
  text-transform:uppercase;
}

.ev-eyebrow.is-warning{
  background:var(--ev-naranja-suave);
  border-color:rgba(234,124,18,.20);
  color:#9A3412;
}

.ev-title{
  margin:0 0 10px;
  color:var(--ev-verde-oscuro);
  font-size:clamp(2rem, 3.4vw, 2.68rem);
  line-height:1.06;
  font-weight:850;
  letter-spacing:-.05em;
}

.ev-observed-main .ev-title{
  max-width:660px;
  font-size:clamp(2rem, 3.1vw, 2.55rem);
}

.ev-subtitle{
  max-width:820px;
  margin:0;
  color:var(--ev-azul-texto);
  font-size:.95rem;
  line-height:1.68;
}

/* ===================================================
   OBSERVADO - LAYOUT
=================================================== */

.ev-observed-grid{
  display:grid;
  grid-template-columns:minmax(0, 1fr) 390px;
  gap:22px;
  align-items:start;
}

.ev-observed-main,
.ev-observed-side{
  min-width:0;
}

.ev-observed-side{
  display:grid;
  gap:14px;
  position:sticky;
  top:18px;
}

/* ===================================================
   OBSERVACIÓN
=================================================== */

.ev-observation-card{
  margin-bottom:16px;
  padding:18px;
  border-radius:22px;
  background:#FFFDFC;
  border:1px solid rgba(254,215,170,.92);
  box-shadow:var(--ev-shadow-soft);
}

.ev-observation-head{
  display:flex;
  align-items:flex-start;
  gap:12px;
  margin-bottom:12px;
}

.ev-observation-head > span{
  width:42px;
  height:42px;
  flex:0 0 42px;
  display:grid;
  place-items:center;
  border-radius:14px;
  background:#FFFFFF;
  border:1px solid rgba(234,124,18,.20);
  color:#9A3412;
  font-size:1.03rem;
}

.ev-observation-head h2{
  margin:0 0 3px;
  color:#9A3412;
  font-size:.96rem;
  font-weight:850;
}

.ev-observation-head p{
  margin:0;
  color:#B45309;
  font-size:.82rem;
  line-height:1.4;
}

.ev-observation-message{
  position:relative;
  padding:14px 16px 14px 18px;
  border-radius:16px;
  background:#FFFFFF;
  border:1px solid rgba(254,215,170,.86);
  color:#7C2D12;
  font-size:.92rem;
  font-weight:700;
  line-height:1.62;
}

.ev-observation-message::before{
  content:"";
  position:absolute;
  left:0;
  top:14px;
  bottom:14px;
  width:3px;
  border-radius:999px;
  background:linear-gradient(180deg, var(--ev-naranja), #F59E0B);
}

/* ===================================================
   PASOS
=================================================== */

.ev-next-card{
  padding:18px;
  border-radius:22px;
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.88);
  box-shadow:var(--ev-shadow-soft);
}

.ev-next-head{
  display:flex;
  align-items:flex-start;
  gap:12px;
  margin-bottom:14px;
}

.ev-next-head > span{
  width:42px;
  height:42px;
  flex:0 0 42px;
  display:grid;
  place-items:center;
  border-radius:14px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.16);
  color:var(--ev-verde-oscuro);
  font-size:1.05rem;
}

.ev-next-head h2{
  margin:0 0 4px;
  color:var(--ev-verde-oscuro);
  font-size:1rem;
  font-weight:850;
}

.ev-next-head p{
  margin:0;
  color:var(--ev-azul-texto);
  font-size:.86rem;
  line-height:1.48;
}

.ev-next-steps{
  display:grid;
  grid-template-columns:repeat(3, minmax(0, 1fr));
  gap:10px;
}

.ev-mini-step{
  display:flex;
  gap:12px;
  align-items:flex-start;
  min-height:98px;
  padding:13px;
  border-radius:17px;
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.88);
}

.ev-mini-step span{
  width:36px;
  height:36px;
  flex:0 0 36px;
  display:grid;
  place-items:center;
  border-radius:13px;
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.92);
  color:#6B7280;
  font-size:.9rem;
}

.ev-mini-step.is-done span{
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  border-color:transparent;
  color:#FFFFFF;
  box-shadow:var(--ev-shadow-green);
}

.ev-mini-step.is-active{
  border-color:rgba(234,124,18,.24);
  background:#FFFCF8;
}

.ev-mini-step.is-active span{
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border-color:transparent;
  color:#FFFFFF;
  box-shadow:0 10px 22px rgba(234,124,18,.22);
}

.ev-mini-step h3{
  margin:0 0 4px;
  color:#111827;
  font-size:.83rem;
  font-weight:850;
  line-height:1.25;
}

.ev-mini-step p{
  margin:0;
  color:#5F6F8A;
  font-size:.77rem;
  line-height:1.45;
}

/* ===================================================
   FORMULARIO
=================================================== */

.ev-form-card{
  padding:20px;
  border-radius:24px;
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.90);
  box-shadow:var(--ev-shadow-card);
}

.ev-form-card h2{
  margin:0 0 7px;
  color:var(--ev-verde-oscuro);
  font-size:1.08rem;
  font-weight:850;
  letter-spacing:-.015em;
}

.ev-form-card > p{
  margin:0 0 14px;
  color:var(--ev-azul-texto);
  font-size:.88rem;
  line-height:1.58;
}

.ev-label{
  color:var(--ev-gris-700);
  font-weight:800;
  margin-bottom:8px;
  font-size:.86rem;
}

.ev-upload-block{
  margin-top:2px;
}

.ev-file-native{
  position:absolute;
  width:1px;
  height:1px;
  padding:0;
  margin:-1px;
  overflow:hidden;
  clip:rect(0, 0, 0, 0);
  white-space:nowrap;
  border:0;
}

.ev-upload-zone{
  cursor:pointer;
  display:grid;
  grid-template-columns:46px minmax(0,1fr) auto;
  align-items:center;
  gap:12px;
  min-height:82px;
  padding:14px;
  border-radius:20px;
  background:#FFFFFF;
  border:1.6px dashed rgba(22,163,74,.34);
  transition:
    transform .18s ease,
    box-shadow .18s ease,
    border-color .18s ease,
    background .18s ease;
}

.ev-upload-zone:hover,
.ev-upload-zone.is-dragover{
  transform:translateY(-1px);
  border-color:rgba(14,122,67,.56);
  box-shadow:0 12px 26px rgba(15,23,42,.06);
  background:#FAFFFC;
}

.ev-upload-zone.is-disabled{
  cursor:not-allowed;
  opacity:.75;
  transform:none;
}

.ev-upload-icon{
  width:44px;
  height:44px;
  display:grid;
  place-items:center;
  border-radius:16px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.16);
  color:var(--ev-verde-oscuro);
  font-size:1.18rem;
}

.ev-upload-copy{
  min-width:0;
  display:grid;
  gap:3px;
}

.ev-upload-copy strong{
  color:#111827;
  font-size:.88rem;
  font-weight:850;
}

.ev-upload-copy small{
  color:#64748B;
  font-size:.76rem;
  line-height:1.35;
}

.ev-upload-action{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-height:32px;
  padding:0 13px;
  border-radius:999px;
  background:#FFFFFF;
  border:1px solid rgba(14,122,67,.25);
  color:var(--ev-verde-oscuro);
  font-size:.77rem;
  font-weight:850;
}

.ev-selected-file{
  margin-top:9px;
  padding:9px 12px;
  border-radius:14px;
  background:#F9FAFB;
  border:1px solid rgba(229,231,235,.90);
  color:#64748B;
  font-size:.8rem;
  line-height:1.35;
  word-break:break-word;
}

.ev-selected-file.has-file{
  background:#F0FDF4;
  border-color:rgba(22,163,74,.20);
  color:var(--ev-verde-oscuro);
  font-weight:750;
}

.ev-file-meta{
  color:#64748B;
  font-weight:600;
}

.ev-help{
  margin-top:9px;
  color:#6B7280;
  font-size:.76rem;
  line-height:1.45;
}

.ev-actions{
  display:flex;
  flex-direction:column;
  align-items:stretch;
  gap:10px;
  margin-top:16px;
}

.ev-actions .ev-btn-primary{
  width:100%;
  min-height:42px;
}

.ev-actions .ev-btn-secondary{
  align-self:center;
  min-height:38px;
  padding:0 18px;
}

/* ===================================================
   SOPORTE
=================================================== */

.ev-support-mini{
  display:flex;
  gap:12px;
  align-items:flex-start;
  padding:16px;
  border-radius:22px;
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.88);
  box-shadow:var(--ev-shadow-soft);
}

.ev-support-mini > i{
  width:40px;
  height:40px;
  flex:0 0 40px;
  display:grid;
  place-items:center;
  border-radius:14px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.14);
  color:var(--ev-verde-oscuro);
  font-size:1.02rem;
}

.ev-support-mini strong{
  display:block;
  margin-bottom:3px;
  color:var(--ev-verde-oscuro);
  font-size:.88rem;
  font-weight:850;
}

.ev-support-mini div div{
  color:var(--ev-azul-texto);
  font-size:.8rem;
  line-height:1.45;
}

/* ===================================================
   ÉXITO
=================================================== */

.ev-success{
  display:flex;
  gap:13px;
  align-items:flex-start;
  margin-top:18px;
  padding:16px;
  border-radius:18px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.20);
  box-shadow:var(--ev-shadow-soft);
  color:#14532D;
}

.ev-success span{
  width:40px;
  height:40px;
  flex:0 0 40px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  color:#FFFFFF;
  font-size:1rem;
  box-shadow:var(--ev-shadow-green);
}

.ev-success h3{
  margin:0 0 4px;
  color:var(--ev-verde-oscuro);
  font-size:.92rem;
  font-weight:850;
}

.ev-success p{
  margin:0;
  color:#166534;
  font-size:.82rem;
  line-height:1.45;
}

/* ===================================================
   ESTADO EN REVISIÓN
=================================================== */

.ev-status-banner{
  display:flex;
  align-items:center;
  gap:15px;
  margin-bottom:18px;
  padding:16px 18px;
  border-radius:21px;
  background:#FFFFFF;
  border:1px solid rgba(22,163,74,.20);
  box-shadow:var(--ev-shadow-soft);
}

.ev-status-icon{
  width:44px;
  height:44px;
  flex:0 0 44px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  color:#FFFFFF;
  font-size:1.08rem;
  box-shadow:var(--ev-shadow-green);
}

.ev-status-banner h2{
  margin:0 0 4px;
  color:var(--ev-verde-oscuro);
  font-size:.96rem;
  font-weight:850;
}

.ev-status-banner p{
  margin:0;
  color:#166534;
  font-size:.88rem;
  line-height:1.48;
}

.ev-timeline{
  position:relative;
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:16px;
  margin-bottom:18px;
  padding:23px 26px;
  border-radius:22px;
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.88);
  box-shadow:var(--ev-shadow-card);
}

.ev-timeline::before{
  content:"";
  position:absolute;
  left:calc(16.666% + 30px);
  right:calc(16.666% + 30px);
  top:40px;
  height:3px;
  border-radius:999px;
  background:#E5E7EB;
  z-index:0;
}

.ev-timeline::after{
  content:"";
  position:absolute;
  left:calc(16.666% + 30px);
  top:40px;
  width:calc(33.333% - 60px);
  height:3px;
  border-radius:999px;
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  z-index:0;
}

.ev-step{
  position:relative;
  z-index:1;
  text-align:center;
}

.ev-step-dot{
  width:40px;
  height:40px;
  margin:0 auto 11px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:#FFFFFF;
  color:#A0AEC0;
  border:3px solid #FFFFFF;
  font-size:.95rem;
  box-shadow:
    0 6px 14px rgba(15,23,42,.05),
    inset 0 0 0 1px rgba(203,213,225,.82);
}

.ev-step.is-done .ev-step-dot{
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  color:#FFFFFF;
  box-shadow:var(--ev-shadow-green);
}

.ev-step.is-active .ev-step-dot{
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  color:#FFFFFF;
  box-shadow:
    0 10px 22px rgba(234,124,18,.28),
    0 0 0 6px rgba(234,124,18,.11);
}

.ev-step-title{
  margin-bottom:3px;
  color:#111827;
  font-size:.9rem;
  font-weight:850;
}

.ev-step.is-done .ev-step-title{
  color:var(--ev-verde-oscuro);
}

.ev-step.is-active .ev-step-title{
  color:var(--ev-naranja-oscuro);
}

.ev-step-text{
  color:#64748B;
  font-size:.8rem;
}

.ev-info-grid{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:12px;
  margin-bottom:18px;
}

.ev-info-card{
  display:flex;
  gap:13px;
  min-height:112px;
  padding:17px;
  border-radius:20px;
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.88);
  box-shadow:var(--ev-shadow-soft);
}

.ev-info-card > span{
  width:40px;
  height:40px;
  flex:0 0 40px;
  display:grid;
  place-items:center;
  border-radius:14px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.14);
  color:var(--ev-verde-oscuro);
  font-size:1rem;
}

.ev-info-card h3{
  margin:0 0 4px;
  color:#111827;
  font-size:.88rem;
  font-weight:850;
}

.ev-info-card strong{
  display:block;
  margin-bottom:6px;
  color:var(--ev-verde);
  font-size:.85rem;
  font-weight:850;
}

.ev-info-card p{
  margin:0;
  color:var(--ev-azul-texto);
  font-size:.8rem;
  line-height:1.52;
}

.ev-co-footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:18px;
  padding:17px 18px;
  border-radius:22px;
  background:#FFFFFF;
  border:1px solid rgba(229,231,235,.88);
  box-shadow:var(--ev-shadow-soft);
}

.ev-support{
  display:flex;
  align-items:center;
  gap:13px;
}

.ev-support > span{
  width:46px;
  height:46px;
  flex:0 0 46px;
  display:grid;
  place-items:center;
  border-radius:16px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.14);
  color:var(--ev-verde-oscuro);
  font-size:1.12rem;
}

.ev-support h4{
  margin:0 0 4px;
  color:var(--ev-verde-oscuro);
  font-size:.9rem;
  font-weight:850;
}

.ev-support p{
  margin:0 0 6px;
  color:var(--ev-azul-texto);
  font-size:.82rem;
  line-height:1.45;
}

.ev-support-link{
  display:inline-flex;
  align-items:center;
  gap:4px;
  color:var(--ev-verde-oscuro);
  font-weight:850;
  font-size:.82rem;
  text-decoration:none;
  transition:all .18s ease;
}

.ev-support-link:hover{
  color:var(--ev-naranja);
  transform:translateX(2px);
  text-decoration:none;
}

.ev-footer-actions{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:10px;
  flex-wrap:wrap;
}

/* ===================================================
   BOTONES
=================================================== */

.ev-btn-primary{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  min-height:42px;
  padding:0 20px;
  border:none;
  border-radius:var(--ev-radius-btn);
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  color:#FFFFFF;
  font-size:.88rem;
  font-weight:850;
  line-height:1;
  box-shadow:var(--ev-shadow-orange);
  transition:
    transform .18s ease,
    box-shadow .18s ease,
    background .18s ease,
    filter .18s ease;
}

.ev-btn-primary:hover,
.ev-btn-primary:focus{
  background:linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C);
  color:#FFFFFF;
  transform:translateY(-1px);
  box-shadow:0 16px 34px rgba(234,124,18,.42);
}

.ev-btn-primary:active{
  transform:translateY(0);
  box-shadow:0 8px 18px rgba(234,124,18,.28);
}

.ev-btn-primary:disabled{
  opacity:.75;
  transform:none;
  box-shadow:0 6px 16px rgba(234,124,18,.20);
}

.ev-btn-secondary{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  min-height:42px;
  padding:0 17px;
  border-radius:var(--ev-radius-btn);
  border:1.2px solid rgba(14,122,67,.26);
  background:#FFFFFF;
  color:var(--ev-verde-oscuro);
  font-size:.86rem;
  font-weight:800;
  line-height:1;
  box-shadow:
    0 4px 12px rgba(15,23,42,.018),
    inset 0 0 0 1px rgba(255,255,255,.72);
  transition:
    transform .18s ease,
    box-shadow .18s ease,
    border-color .18s ease,
    color .18s ease,
    background .18s ease;
}

.ev-btn-secondary i,
.ev-btn-primary i{
  font-size:.92rem;
  line-height:1;
}

.ev-btn-secondary:hover,
.ev-btn-secondary:focus{
  background:#FCFFFD;
  color:var(--ev-verde);
  border-color:rgba(14,122,67,.40);
  transform:translateY(-1px);
  box-shadow:
    0 8px 18px rgba(15,23,42,.04),
    0 0 0 4px rgba(22,163,74,.06);
}

.ev-btn-secondary:active{
  transform:translateY(0);
  box-shadow:0 4px 10px rgba(15,23,42,.03);
}

/* ===================================================
   SWEETALERT
=================================================== */

.swal2-popup{
  border-radius:24px !important;
  padding:30px 30px 24px !important;
  background:#FFFFFF !important;
  box-shadow:
    0 20px 55px rgba(15,23,42,.18),
    0 4px 12px rgba(15,23,42,.08) !important;
}

.swal2-title{
  color:var(--ev-verde-oscuro) !important;
  font-weight:850 !important;
  letter-spacing:-.02em !important;
}

.swal2-html-container{
  color:var(--ev-azul-texto) !important;
  line-height:1.6 !important;
  font-size:.95rem !important;
}

.swal2-confirm{
  border:none !important;
  border-radius:13px !important;
  padding:12px 24px !important;
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B) !important;
  color:#FFFFFF !important;
  font-weight:850 !important;
  box-shadow:var(--ev-shadow-orange) !important;
  transition:all .18s ease !important;
}

.swal2-confirm:hover{
  background:linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C) !important;
  transform:translateY(-1px) !important;
  box-shadow:0 14px 30px rgba(234,124,18,.44) !important;
}

/* ===================================================
   RESPONSIVE
=================================================== */

@media (max-width: 1199.98px){
  .ev-co-wrap{
    max-width:1040px;
  }

  .ev-observed-grid{
    grid-template-columns:minmax(0, 1fr) 360px;
  }

  .ev-next-steps{
    grid-template-columns:1fr;
  }

  .ev-mini-step{
    min-height:0;
  }
}

@media (max-width: 991.98px){
  body.ev-co-page{
    padding:18px;
  }

  .ev-co-shell{
    min-height:calc(100vh - 36px);
    align-items:flex-start;
    padding-top:28px;
    padding-bottom:28px;
  }

  .ev-co-card-inner{
    padding:30px;
  }

  .ev-observed-grid{
    grid-template-columns:1fr;
  }

  .ev-observed-side{
    position:static;
    grid-template-columns:1fr;
  }

  .ev-next-steps{
    grid-template-columns:repeat(3, minmax(0, 1fr));
  }

  .ev-mini-step{
    flex-direction:column;
  }

  .ev-info-grid{
    grid-template-columns:1fr;
  }
}

@media (max-width: 767.98px){
  .ev-co-card-inner{
    padding:24px 20px 20px;
  }

  .ev-co-hero,
  .ev-co-hero-compact{
    grid-template-columns:1fr;
    text-align:center;
    gap:16px;
  }

  .ev-hero-visual{
    margin:0 auto;
  }

  .ev-eyebrow{
    margin-left:auto;
    margin-right:auto;
  }

  .ev-subtitle{
    margin-left:auto;
    margin-right:auto;
  }

  .ev-next-steps{
    grid-template-columns:1fr;
  }

  .ev-mini-step{
    flex-direction:row;
  }

  .ev-upload-zone{
    grid-template-columns:42px minmax(0,1fr);
  }

  .ev-upload-action{
    grid-column:1 / -1;
    width:100%;
  }

  .ev-timeline{
    grid-template-columns:1fr;
    gap:14px;
    padding:18px;
  }

  .ev-timeline::before,
  .ev-timeline::after{
    display:none;
  }

  .ev-step{
    display:flex;
    align-items:center;
    gap:14px;
    text-align:left;
  }

  .ev-step-dot{
    margin:0;
    flex:0 0 40px;
  }

  .ev-co-footer{
    flex-direction:column;
    align-items:stretch;
  }

  .ev-support{
    align-items:flex-start;
  }

  .ev-footer-actions{
    justify-content:stretch;
  }

  .ev-footer-actions .btn{
    width:100%;
  }

  .ev-actions .ev-btn-secondary{
    width:100%;
  }
}

@media (max-width: 575.98px){
  body.ev-co-page{
    padding:12px;
  }

  .ev-co-shell{
    min-height:calc(100vh - 24px);
    padding-top:18px;
    padding-bottom:18px;
  }

  .ev-brand-pill{
    padding:8px 15px 8px 10px;
    font-size:.88rem;
  }

  .ev-brand-icon{
    width:34px;
    height:34px;
  }

  .ev-brand-logo{
    width:26px;
    height:26px;
  }

  .ev-co-card{
    border-radius:24px;
  }

  .ev-co-card-inner{
    padding:22px 15px 16px;
  }

  .ev-title{
    font-size:1.78rem;
  }

  .ev-observed-main .ev-title{
    font-size:1.78rem;
  }

  .ev-observation-card,
  .ev-next-card,
  .ev-form-card,
  .ev-support-mini,
  .ev-status-banner,
  .ev-timeline,
  .ev-info-card,
  .ev-co-footer{
    border-radius:18px;
  }

  .ev-observation-message{
    font-size:.86rem;
  }

  .ev-upload-zone{
    min-height:0;
    padding:14px;
  }

  .swal2-popup{
    width:min(92vw, 460px) !important;
    padding:26px 20px 22px !important;
  }
}


/* ===================================================
   CUENTA OBSERVADA / REVISIÓN — cierre visual EV 2026
=================================================== */
body.ev-co-page{
  background:
    radial-gradient(circle at 12% 10%,rgba(22,163,74,.055),transparent 28%),
    radial-gradient(circle at 90% 8%,rgba(234,124,18,.05),transparent 25%),
    #F6F8F7!important;
}
.ev-co-card{
  position:relative;
  border-color:rgba(15,89,47,.10);
  box-shadow:0 32px 78px rgba(15,23,42,.105),0 8px 24px rgba(15,23,42,.045);
}
.ev-co-card::before{
  content:"";
  position:absolute;
  inset:0 0 auto;
  height:4px;
  background:linear-gradient(90deg,var(--ev-verde-oscuro),var(--ev-verde-claro) 62%,var(--ev-naranja));
  z-index:2;
}
.ev-co-card-inner{
  position:relative;
  background:
    linear-gradient(135deg,rgba(255,247,237,.34),transparent 27%),
    linear-gradient(225deg,rgba(240,253,244,.55),transparent 32%),
    #fff;
}
body.is-review .ev-co-wrap{max-width:1080px}
body.is-review .ev-co-card-inner{padding:40px 42px 34px}
body.is-review .ev-co-hero{
  padding:4px 6px 10px;
  margin-bottom:20px;
}
.ev-title{
  letter-spacing:-.035em;
  text-wrap:balance;
}
.ev-subtitle{
  max-width:760px;
  color:#64748B;
  line-height:1.65;
}
.ev-status-banner{
  border-color:rgba(22,163,74,.20);
  background:linear-gradient(100deg,#F0FDF4 0%,#FFFFFF 72%);
  box-shadow:0 9px 24px rgba(15,89,47,.05);
}
.ev-timeline{
  position:relative;
  border-color:rgba(15,89,47,.10);
  background:rgba(255,255,255,.88);
  box-shadow:0 15px 34px rgba(15,23,42,.045);
}
.ev-step.is-active .ev-step-dot{
  box-shadow:0 0 0 6px rgba(234,124,18,.12),0 10px 22px rgba(234,124,18,.18);
}
.ev-info-card{
  min-height:142px;
  border-color:rgba(15,89,47,.10);
  background:rgba(255,255,255,.92);
  transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease;
}
.ev-info-card:hover{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.24);
  box-shadow:0 14px 30px rgba(15,23,42,.07);
}
.ev-co-footer{
  border-color:rgba(15,89,47,.12);
  background:linear-gradient(100deg,#F7FFF9,#FFFFFF);
  box-shadow:0 12px 30px rgba(15,23,42,.04);
}
.ev-observed-grid{gap:26px}
.ev-observed-main,
.ev-observed-side{min-width:0}
.ev-observation-card{
  border-color:rgba(234,124,18,.24);
  background:linear-gradient(135deg,#FFF9F2,#FFFFFF 78%);
  box-shadow:0 13px 30px rgba(234,124,18,.07);
}
.ev-observation-message{
  border-left:3px solid var(--ev-naranja);
  background:#fff;
}
.ev-next-card,
.ev-form-card,
.ev-support-mini-premium{
  border-color:rgba(15,89,47,.11);
  background:rgba(255,255,255,.94);
  box-shadow:0 15px 34px rgba(15,23,42,.05);
}
.ev-form-card{position:relative;overflow:hidden}
.ev-form-card::before{
  content:"";
  position:absolute;
  inset:0 0 auto;
  height:3px;
  background:linear-gradient(90deg,var(--ev-verde),var(--ev-naranja));
}
.ev-upload-zone{
  border-color:rgba(22,163,74,.24);
  background:linear-gradient(135deg,#FAFFFC,#FFFFFF);
  transition:border-color .18s ease,background .18s ease,box-shadow .18s ease,transform .18s ease;
}
.ev-upload-zone:hover,
.ev-upload-zone:focus-within{
  transform:translateY(-1px);
  border-color:var(--ev-verde-claro);
  background:#F4FFF8;
  box-shadow:0 0 0 4px rgba(22,163,74,.08),0 12px 24px rgba(15,89,47,.06);
}
.ev-actions{gap:10px}
.ev-actions .btn{min-height:46px}
@media(max-width:991.98px){
  body.is-review .ev-co-card-inner{padding:32px 28px 26px}
  .ev-info-card{min-height:0}
}
@media(max-width:767.98px){
  .ev-co-shell{padding-top:24px}
  body.is-review .ev-co-card-inner{padding:27px 20px 20px}
  .ev-co-hero,.ev-co-hero-compact{
    text-align:left;
    justify-items:start;
  }
  .ev-hero-visual{margin:0}
  .ev-eyebrow{margin-left:0;margin-right:0}
  .ev-subtitle{margin-left:0;margin-right:0}
  .ev-timeline{padding:17px 16px}
  .ev-info-grid{gap:12px}
  .ev-info-card{padding:16px}
}
@media(max-width:575.98px){
  body.ev-co-page{padding:8px}
  .ev-co-shell{min-height:calc(100vh - 16px);padding:12px 0 18px}
  .ev-brand-pill{margin-bottom:10px}
  .ev-co-card{border-radius:22px}
  body.is-review .ev-co-card-inner,.ev-co-card-inner{padding:24px 14px 15px}
  .ev-title,.ev-observed-main .ev-title{font-size:1.58rem;line-height:1.18}
  .ev-subtitle{font-size:.86rem;line-height:1.55}
  .ev-co-hero,.ev-co-hero-compact{gap:13px;margin-bottom:14px}
  .ev-hero-visual,.ev-co-hero-compact .ev-hero-visual{width:82px;height:82px}
  .ev-hero-orb,.ev-co-hero-compact .ev-hero-orb{width:52px;height:52px;border-radius:17px;font-size:1.35rem}
  .ev-status-banner,.ev-observation-card,.ev-next-card,.ev-form-card,.ev-support-mini,.ev-timeline,.ev-info-card,.ev-co-footer{border-radius:16px}
  .ev-footer-actions{display:grid;grid-template-columns:1fr;gap:8px}
  .ev-footer-actions .btn,.ev-actions .btn{width:100%}
}
</style>