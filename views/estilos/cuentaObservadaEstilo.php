<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.3/index.min.css">

<style>
/* ===================================================
   ENTRE VECINOS - CUENTA OBSERVADA / VALIDACIÓN
   Diseño premium limpio + fondo blanco + copy amigable
=================================================== */

:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#0E7A43;
  --ev-verde-claro:#16A34A;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;

  --ev-gris-050:#F9FAFB;
  --ev-gris-100:#F3F4F6;
  --ev-gris-200:#E5E7EB;
  --ev-gris-300:#D1D5DB;
  --ev-gris-400:#9CA3AF;
  --ev-gris-500:#6B7280;
  --ev-gris-600:#4B5563;
  --ev-gris-700:#374151;

  --ev-texto:#111827;

  --ev-shadow-page:
    0 18px 42px rgba(15,23,42,0.055),
    0 4px 14px rgba(15,23,42,0.028);

  --ev-shadow-card:
    0 10px 22px rgba(15,23,42,0.038),
    0 1px 3px rgba(15,23,42,0.022);

  --ev-shadow-soft:
    0 6px 16px rgba(15,23,42,0.03),
    0 1px 2px rgba(15,23,42,0.02);

  --ev-shadow-orange:
    0 12px 26px rgba(234,124,18,0.34);

  --ev-shadow-green:
    0 10px 22px rgba(14,122,67,0.18);

  --ev-radius-xl:28px;
  --ev-radius-lg:22px;
  --ev-radius-md:18px;
  --ev-radius-sm:14px;
  --ev-radius-btn:12px;
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
  padding:24px;
  background:#ffffff !important;
  color:var(--ev-texto);
  font-family:'Poppins', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
  overflow-x:hidden;
}

body.ev-co-page::before,
body.ev-co-page::after{
  display:none !important;
  content:none !important;
}

/* ===================================================
   LAYOUT
=================================================== */

.ev-co-shell{
  min-height:calc(100vh - 48px);
  display:flex;
  align-items:center;
  justify-content:center;
}

.ev-co-wrap{
  width:100%;
  max-width:1160px;
}

/* ===================================================
   BRAND SUPERIOR
=================================================== */

.ev-brand-pill{
  width:max-content;
  margin:0 auto 16px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px 22px 10px 12px;
  border-radius:999px;
  background:#ffffff;
  border:1px solid rgba(229,231,235,.92);
  color:var(--ev-verde-oscuro);
  font-weight:700;
  box-shadow:var(--ev-shadow-soft);
  user-select:none;
}

.ev-brand-icon{
  width:40px;
  height:40px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:#ffffff;
  border:1px solid rgba(22,163,74,.16);
  color:var(--ev-verde-oscuro);
  font-size:1.08rem;
  overflow:hidden;
}

.ev-brand-logo{
  width:30px;
  height:30px;
  display:block;
  object-fit:contain;
  border-radius:8px;
}

/* ===================================================
   CARD PRINCIPAL
=================================================== */

.ev-co-card{
  background:#ffffff;
  border:1px solid rgba(229,231,235,.78);
  border-radius:var(--ev-radius-xl);
  box-shadow:var(--ev-shadow-page);
  overflow:hidden;
}

.ev-co-card::before,
.ev-co-card::after{
  display:none !important;
  content:none !important;
}

.ev-co-card-inner{
  padding:58px 58px 28px;
  background:#ffffff;
}

/* ===================================================
   HERO
=================================================== */

.ev-co-hero{
  display:grid;
  grid-template-columns:140px minmax(0, 1fr);
  align-items:center;
  gap:28px;
  margin-bottom:28px;
}

.ev-hero-visual{
  position:relative;
  width:126px;
  height:126px;
  display:grid;
  place-items:center;
}

.ev-hero-visual::before{
  content:"";
  position:absolute;
  inset:0;
  border-radius:50%;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.16);
}

.ev-hero-orb{
  position:relative;
  z-index:2;
  width:70px;
  height:70px;
  display:grid;
  place-items:center;
  border-radius:22px;
  background:#ffffff;
  color:var(--ev-verde-oscuro);
  border:1px solid rgba(22,163,74,.22);
  font-size:1.95rem;
  box-shadow:0 6px 14px rgba(15,89,47,.04);
}

.ev-hero-orb.is-warning{
  color:#9A3412;
  border-color:rgba(234,124,18,.24);
}

.ev-hero-check{
  position:absolute;
  top:10px;
  right:12px;
  z-index:3;
  width:34px;
  height:34px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  color:#ffffff;
  border:3px solid #ffffff;
  box-shadow:var(--ev-shadow-green);
  font-size:.92rem;
}

.ev-hero-check.is-warning{
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  box-shadow:0 10px 22px rgba(234,124,18,.22);
}

.ev-eyebrow{
  display:inline-flex;
  align-items:center;
  padding:7px 13px;
  margin-bottom:10px;
  border-radius:999px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.14);
  color:var(--ev-verde-oscuro);
  font-size:.79rem;
  font-weight:800;
  letter-spacing:.03em;
  text-transform:uppercase;
}

.ev-eyebrow.is-warning{
  background:#FFF7ED;
  border-color:rgba(234,124,18,.16);
  color:#9A3412;
}

.ev-title{
  margin:0 0 10px;
  font-size:clamp(2.1rem, 4vw, 3rem);
  line-height:1.06;
  font-weight:800;
  letter-spacing:-.045em;
  color:var(--ev-verde-oscuro);
}

/* Oculta cualquier punto/acento naranja dentro del título */
.ev-title span{
  display:none !important;
}

.ev-subtitle{
  max-width:820px;
  margin:0;
  color:#5F6F8A;
  font-size:.98rem;
  line-height:1.72;
}

/* ===================================================
   STATUS BANNER
=================================================== */

.ev-status-banner{
  display:flex;
  align-items:center;
  gap:16px;
  margin-bottom:20px;
  padding:17px 20px;
  border-radius:20px;
  background:#ffffff;
  border:1px solid rgba(22,163,74,.20);
  box-shadow:var(--ev-shadow-soft);
}

.ev-status-icon{
  width:46px;
  height:46px;
  flex:0 0 46px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  color:#ffffff;
  font-size:1.12rem;
  box-shadow:var(--ev-shadow-green);
}

.ev-status-banner h2{
  margin:0 0 4px;
  font-size:.98rem;
  font-weight:800;
  color:var(--ev-verde-oscuro);
}

.ev-status-banner p{
  margin:0;
  color:#166534;
  font-size:.91rem;
  line-height:1.5;
}

/* ===================================================
   TIMELINE
   Verde = completado
   Naranja = activo
   Gris = pendiente
=================================================== */

.ev-timeline{
  position:relative;
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:16px;
  margin-bottom:20px;
  padding:24px 26px;
  border-radius:22px;
  background:#ffffff;
  border:1px solid rgba(229,231,235,.82);
  box-shadow:var(--ev-shadow-card);
}

.ev-timeline::before{
  content:"";
  position:absolute;
  left:calc(16.666% + 30px);
  right:calc(16.666% + 30px);
  top:41px;
  height:3px;
  border-radius:999px;
  background:#E5E7EB;
  z-index:0;
}

.ev-timeline::after{
  content:"";
  position:absolute;
  left:calc(16.666% + 30px);
  top:41px;
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
  margin:0 auto 12px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:#ffffff;
  color:#A0AEC0;
  border:3px solid #ffffff;
  font-size:.98rem;
  box-shadow:
    0 6px 14px rgba(15,23,42,.05),
    inset 0 0 0 1px rgba(203,213,225,.82);
}

.ev-step.is-done .ev-step-dot{
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  color:#ffffff;
  box-shadow:var(--ev-shadow-green);
}

.ev-step.is-active .ev-step-dot{
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  color:#ffffff;
  box-shadow:
    0 10px 22px rgba(234,124,18,.28),
    0 0 0 6px rgba(234,124,18,.11);
}

.ev-step-title{
  margin-bottom:3px;
  color:#111827;
  font-size:.94rem;
  font-weight:800;
}

.ev-step.is-done .ev-step-title{
  color:var(--ev-verde-oscuro);
}

.ev-step.is-active .ev-step-title{
  color:var(--ev-naranja-oscuro);
}

.ev-step-text{
  color:#64748B;
  font-size:.84rem;
}

/* ===================================================
   INFO CARDS
=================================================== */

.ev-info-grid{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:12px;
  margin-bottom:20px;
}

.ev-info-card{
  display:flex;
  gap:14px;
  min-height:118px;
  padding:18px 18px 16px;
  border-radius:20px;
  background:#ffffff;
  border:1px solid rgba(229,231,235,.82);
  box-shadow:var(--ev-shadow-soft);
}

.ev-info-card > span{
  width:42px;
  height:42px;
  flex:0 0 42px;
  display:grid;
  place-items:center;
  border-radius:14px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.14);
  color:var(--ev-verde-oscuro);
  font-size:1.02rem;
}

.ev-info-card h3{
  margin:0 0 4px;
  font-size:.92rem;
  font-weight:800;
  color:#111827;
}

.ev-info-card strong{
  display:block;
  margin-bottom:6px;
  color:var(--ev-verde);
  font-size:.89rem;
  font-weight:800;
}

.ev-info-card p{
  margin:0;
  color:#5F6F8A;
  font-size:.86rem;
  line-height:1.52;
}

/* ===================================================
   FOOTER
=================================================== */

.ev-co-footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:18px;
  padding:18px 20px;
  border-radius:22px;
  background:#ffffff;
  border:1px solid rgba(229,231,235,.82);
  box-shadow:var(--ev-shadow-soft);
}

.ev-support{
  display:flex;
  align-items:center;
  gap:14px;
}

.ev-support > span{
  width:48px;
  height:48px;
  flex:0 0 48px;
  display:grid;
  place-items:center;
  border-radius:16px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.14);
  color:var(--ev-verde-oscuro);
  font-size:1.16rem;
}

.ev-support h4{
  margin:0 0 4px;
  color:var(--ev-verde-oscuro);
  font-size:.94rem;
  font-weight:800;
}

.ev-support p{
  margin:0 0 6px;
  color:#5F6F8A;
  font-size:.86rem;
  line-height:1.45;
}

.ev-support-link{
  display:inline-flex;
  align-items:center;
  gap:4px;
  color:var(--ev-verde-oscuro);
  font-weight:800;
  font-size:.86rem;
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
  padding:0 22px;
  border:none;
  border-radius:12px;
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  color:#ffffff;
  font-size:.92rem;
  font-weight:800;
  line-height:1;
  box-shadow:var(--ev-shadow-orange);
  transition:all .2s ease;
}

.ev-btn-primary:hover,
.ev-btn-primary:focus{
  background:linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C);
  color:#ffffff;
  transform:translateY(-1px);
  box-shadow:0 14px 30px rgba(234,124,18,.44);
}

.ev-btn-primary:active{
  transform:translateY(0);
  box-shadow:0 6px 16px rgba(234,124,18,.28);
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
  padding:0 18px;
  border-radius:12px;
  border:1.2px solid rgba(14,122,67,.26);
  background:#ffffff;
  color:var(--ev-verde-oscuro);
  font-size:.9rem;
  font-weight:700;
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

.ev-btn-secondary i{
  font-size:.95rem;
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
   OBSERVADO
=================================================== */

.ev-observation-card{
  margin-bottom:22px;
  padding:22px;
  border-radius:22px;
  background:#ffffff;
  border:1px solid rgba(254,215,170,.88);
  box-shadow:var(--ev-shadow-soft);
}

.ev-observation-head{
  display:flex;
  align-items:flex-start;
  gap:14px;
  margin-bottom:14px;
}

.ev-observation-head > span{
  width:46px;
  height:46px;
  flex:0 0 46px;
  display:grid;
  place-items:center;
  border-radius:14px;
  background:#FFF7ED;
  border:1px solid rgba(234,124,18,.18);
  color:#9A3412;
  font-size:1.12rem;
}

.ev-observation-head h2{
  margin:0 0 4px;
  font-size:1rem;
  font-weight:800;
  color:#9A3412;
}

.ev-observation-head p{
  margin:0;
  color:#B45309;
  font-size:.86rem;
}

.ev-observation-message{
  padding:16px 18px;
  border-radius:16px;
  background:#ffffff;
  border:1px solid rgba(254,215,170,.85);
  color:#7C2D12;
  font-size:.93rem;
  font-weight:600;
  line-height:1.6;
}

.ev-correction-layout{
  display:grid;
  grid-template-columns:340px minmax(0, 1fr);
  gap:18px;
}

.ev-correction-info,
.ev-form-card{
  background:#ffffff;
  border:1px solid rgba(229,231,235,.82);
  border-radius:22px;
  box-shadow:var(--ev-shadow-soft);
}

.ev-correction-info{
  padding:20px;
  display:grid;
  gap:12px;
}

.ev-mini-step{
  display:flex;
  gap:14px;
  align-items:flex-start;
  padding:15px;
  border-radius:16px;
  background:#ffffff;
  border:1px solid rgba(229,231,235,.82);
}

.ev-mini-step span{
  width:42px;
  height:42px;
  flex:0 0 42px;
  display:grid;
  place-items:center;
  border-radius:14px;
  background:#ffffff;
  border:1px solid rgba(229,231,235,.88);
  color:#6B7280;
  font-size:1rem;
}

.ev-mini-step.is-done span{
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  border-color:transparent;
  color:#ffffff;
  box-shadow:var(--ev-shadow-green);
}

.ev-mini-step.is-active{
  border-color:rgba(234,124,18,.22);
}

.ev-mini-step.is-active span{
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border-color:transparent;
  color:#ffffff;
  box-shadow:0 10px 22px rgba(234,124,18,.22);
}

.ev-mini-step h3{
  margin:0 0 4px;
  font-size:.9rem;
  font-weight:800;
  color:#111827;
}

.ev-mini-step p{
  margin:0;
  color:#5F6F8A;
  font-size:.84rem;
  line-height:1.48;
}

.ev-support-mini{
  display:flex;
  gap:12px;
  align-items:center;
  padding:15px;
  border-radius:16px;
  background:#ffffff;
  border:1px solid rgba(229,231,235,.82);
}

.ev-support-mini > i{
  width:42px;
  height:42px;
  flex:0 0 42px;
  display:grid;
  place-items:center;
  border-radius:14px;
  background:#F0FDF4;
  border:1px solid rgba(22,163,74,.14);
  color:var(--ev-verde-oscuro);
  font-size:1.05rem;
}

.ev-support-mini strong{
  display:block;
  margin-bottom:3px;
  color:var(--ev-verde-oscuro);
  font-size:.9rem;
  font-weight:800;
}

.ev-form-card{
  padding:24px;
}

.ev-form-card h2{
  margin:0 0 8px;
  color:var(--ev-verde-oscuro);
  font-size:1.15rem;
  font-weight:800;
}

.ev-form-card > p{
  margin:0 0 18px;
  color:#5F6F8A;
  font-size:.92rem;
  line-height:1.6;
}

.ev-label{
  color:var(--ev-gris-700);
  font-weight:700;
  margin-bottom:8px;
  font-size:.9rem;
}

.ev-file{
  min-height:48px;
  border-radius:12px;
  border:1px solid #D1FAE5;
  padding:11px 14px;
  color:var(--ev-gris-700);
  font-size:.94rem;
  box-shadow:none;
  transition:all .18s ease-out;
}

.ev-file:focus{
  border-color:var(--ev-verde-claro);
  box-shadow:0 0 0 4px rgba(22,163,74,.16);
  outline:none;
}

.ev-help{
  color:#6B7280;
  font-size:.84rem;
  line-height:1.45;
}

.ev-actions{
  display:flex;
  justify-content:flex-end;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
  margin-top:18px;
}

.ev-success{
  display:flex;
  gap:14px;
  align-items:flex-start;
  margin-top:20px;
  padding:18px;
  border-radius:18px;
  background:#ffffff;
  border:1px solid rgba(22,163,74,.20);
  box-shadow:var(--ev-shadow-soft);
  color:#14532D;
}

.ev-success span{
  width:42px;
  height:42px;
  flex:0 0 42px;
  display:grid;
  place-items:center;
  border-radius:50%;
  background:linear-gradient(135deg, var(--ev-verde), var(--ev-verde-claro));
  color:#ffffff;
  font-size:1rem;
  box-shadow:var(--ev-shadow-green);
}

.ev-success h3{
  margin:0 0 4px;
  color:var(--ev-verde-oscuro);
  font-size:.96rem;
  font-weight:800;
}

.ev-success p{
  margin:0;
  color:#166534;
  font-size:.88rem;
  line-height:1.45;
}

/* ===================================================
   SWEETALERT
=================================================== */

.ev-swal-popup{
  border-radius:24px !important;
  padding:30px 30px 24px !important;
  background:#ffffff !important;
  box-shadow:
    0 20px 55px rgba(15,23,42,0.18),
    0 4px 12px rgba(15,23,42,0.08) !important;
}

.ev-swal-title{
  color:var(--ev-verde-oscuro) !important;
  font-weight:800 !important;
  letter-spacing:-.02em !important;
}

.ev-swal-html{
  color:#5F6F8A !important;
  line-height:1.6 !important;
  font-size:.97rem !important;
}

.ev-swal-confirm{
  border:none !important;
  border-radius:12px !important;
  padding:12px 24px !important;
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B) !important;
  color:#ffffff !important;
  font-weight:800 !important;
  box-shadow:var(--ev-shadow-orange) !important;
  transition:all .2s ease !important;
}

.ev-swal-confirm:hover{
  background:linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C) !important;
  transform:translateY(-1px) !important;
  box-shadow:0 14px 30px rgba(234,124,18,.44) !important;
}

/* ===================================================
   RESPONSIVE
=================================================== */

@media (max-width: 991.98px){
  body.ev-co-page{
    padding:18px;
  }

  .ev-co-shell{
    min-height:calc(100vh - 36px);
    align-items:flex-start;
  }

  .ev-co-card-inner{
    padding:42px 28px 24px;
  }

  .ev-co-hero{
    grid-template-columns:110px minmax(0,1fr);
    gap:22px;
  }

  .ev-hero-visual{
    width:106px;
    height:106px;
  }

  .ev-hero-orb{
    width:60px;
    height:60px;
    font-size:1.7rem;
    border-radius:20px;
  }

  .ev-correction-layout,
  .ev-info-grid{
    grid-template-columns:1fr;
  }

  .ev-correction-info{
    grid-template-columns:repeat(3, 1fr);
  }

  .ev-support-mini{
    grid-column:1 / -1;
  }

  .ev-mini-step{
    flex-direction:column;
  }
}

@media (max-width: 767.98px){
  .ev-co-card-inner{
    padding:34px 20px 20px;
  }

  .ev-co-hero{
    grid-template-columns:1fr;
    text-align:center;
  }

  .ev-hero-visual{
    margin:0 auto;
  }

  .ev-eyebrow{
    margin-left:auto;
    margin-right:auto;
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

  .ev-footer-actions,
  .ev-actions{
    justify-content:stretch;
  }

  .ev-footer-actions .btn,
  .ev-actions .btn{
    width:100%;
  }

  .ev-correction-info{
    grid-template-columns:1fr;
  }

  .ev-mini-step{
    flex-direction:row;
  }
}

@media (max-width: 575.98px){
  body.ev-co-page{
    padding:12px;
  }

  .ev-co-shell{
    min-height:calc(100vh - 24px);
  }

  .ev-brand-pill{
    padding:9px 16px 9px 10px;
    font-size:.92rem;
  }

  .ev-brand-icon{
    width:36px;
    height:36px;
  }

  .ev-brand-logo{
    width:28px;
    height:28px;
  }

  .ev-co-card-inner{
    padding:28px 16px 18px;
  }

  .ev-title{
    font-size:1.86rem;
  }

  .ev-status-banner,
  .ev-timeline,
  .ev-info-card,
  .ev-co-footer,
  .ev-observation-card,
  .ev-correction-info,
  .ev-form-card{
    border-radius:18px;
  }

  .ev-swal-popup{
    width:min(92vw, 460px) !important;
    padding:26px 20px 22px !important;
  }
}
</style>