<style>
/* ============================================================
   ENTRE VECINOS - COMUNIDAD VECINO
   Estilo limpio final: destacados en grilla + cards uniformes
============================================================ */
:root{
  --ev-cv-verde-oscuro:#0F592F;
  --ev-cv-verde:#0E7A43;
  --ev-cv-verde-claro:#16A34A;
  --ev-cv-naranja:#EA7C12;
  --ev-cv-naranja-oscuro:#C46B05;
  --ev-cv-azul:#2563EB;
  --ev-cv-morado:#7C3AED;
  --ev-cv-rojo:#DC2626;
  --ev-cv-ambar:#B45309;
  --ev-cv-texto:#111827;
  --ev-cv-suave:#6B7280;
  --ev-cv-borde:#E5E7EB;
  --ev-cv-fondo:#F8FAFC;
  --ev-cv-card:#FFFFFF;
  --ev-cv-radius:20px;
  --ev-cv-radius-lg:26px;
  --ev-cv-shadow:0 16px 42px rgba(15,23,42,.075);
  --ev-cv-shadow-soft:0 10px 26px rgba(15,23,42,.055);
  --ev-cv-shadow-orange:0 18px 38px rgba(234,124,18,.16);
}

.ev-cv-shell,
.ev-cv-shell *{
  box-sizing:border-box;
}

.ev-cv-shell{
  width:100%;
  padding:18px 18px 30px;
  color:var(--ev-cv-texto);
}

.ev-cv-shell button{
  font-family:inherit;
}

/* ================================
   HERO
================================ */
.ev-cv-hero{
  position:relative;
  overflow:hidden;
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:18px;
  padding:24px 26px;
  border-radius:var(--ev-cv-radius-lg);
  background:
    radial-gradient(circle at 88% 16%, rgba(22,163,74,.13), transparent 32%),
    radial-gradient(circle at 7% 92%, rgba(234,124,18,.10), transparent 32%),
    #fff;
  border:1px solid rgba(148,163,184,.18);
  box-shadow:var(--ev-cv-shadow);
}

.ev-cv-hero::before{
  content:"";
  position:absolute;
  inset:0 0 auto 0;
  height:4px;
  background:linear-gradient(90deg,var(--ev-cv-verde-oscuro),var(--ev-cv-verde-claro),var(--ev-cv-naranja));
}

.ev-cv-hero-copy{
  min-width:0;
  max-width:720px;
  position:relative;
  z-index:1;
}

.ev-cv-kicker{
  display:inline-flex;
  align-items:center;
  gap:8px;
  margin-bottom:10px;
  padding:7px 12px;
  border-radius:999px;
  color:var(--ev-cv-verde-oscuro);
  background:#ECFDF3;
  border:1px solid rgba(22,163,74,.15);
  font-size:.76rem;
  font-weight:900;
  letter-spacing:.075em;
  text-transform:uppercase;
}

.ev-cv-hero h1{
  margin:0 0 8px;
  color:var(--ev-cv-verde-oscuro);
  font-size:clamp(1.68rem, 2.7vw, 2.28rem);
  line-height:1.08;
  font-weight:950;
  letter-spacing:-.04em;
}

.ev-cv-hero p{
  margin:0;
  max-width:650px;
  color:var(--ev-cv-suave);
  font-size:.97rem;
  line-height:1.58;
  text-align:left;
  text-wrap:pretty;
}

.ev-cv-community-pill{
  position:relative;
  z-index:1;
  display:inline-flex;
  align-items:center;
  gap:11px;
  max-width:min(100%, 360px);
  padding:12px 15px;
  border-radius:18px;
  border:1px solid rgba(22,163,74,.16);
  background:linear-gradient(135deg,#F0FDF4,#FFFFFF);
  box-shadow:0 12px 26px rgba(15,23,42,.055);
}

.ev-cv-community-pill > i{
  width:42px;
  height:42px;
  flex:0 0 auto;
  display:grid;
  place-items:center;
  border-radius:15px;
  color:var(--ev-cv-verde-oscuro);
  background:#DCFCE7;
  border:1px solid rgba(22,163,74,.16);
  font-size:1.18rem;
}

.ev-cv-community-pill small{
  display:block;
  color:var(--ev-cv-suave);
  font-size:.72rem;
  font-weight:850;
  line-height:1.1;
}

.ev-cv-community-pill > div{
  min-width:0;
  flex:1 1 auto;
}

.ev-cv-community-pill strong{
  display:block;
  min-width:0;
  color:var(--ev-cv-verde-oscuro);
  font-size:.92rem;
  font-weight:950;
  line-height:1.22;
  overflow-wrap:break-word;
  word-break:normal;
  hyphens:none;
  text-wrap:balance;
}

/* ================================
   TOOLBAR
================================ */
.ev-cv-toolbar{
  display:grid;
  grid-template-columns:minmax(0,1fr) minmax(320px,420px);
  gap:14px;
  align-items:center;
  margin-top:14px;
}

.ev-cv-tabs{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  min-width:0;
}

.ev-cv-tabs button{
  border:1px solid rgba(148,163,184,.24);
  border-radius:999px;
  background:#fff;
  color:#374151;
  min-height:40px;
  padding:8px 12px;
  display:inline-flex;
  align-items:center;
  gap:8px;
  font-size:.82rem;
  font-weight:900;
  box-shadow:0 8px 18px rgba(15,23,42,.035);
  transition:background .16s ease,border-color .16s ease,color .16s ease,transform .16s ease,box-shadow .16s ease;
}

.ev-cv-tabs button span{
  min-width:24px;
  height:24px;
  display:inline-grid;
  place-items:center;
  border-radius:999px;
  background:#F3F4F6;
  color:#6B7280;
  font-size:.72rem;
  font-weight:950;
}

.ev-cv-tabs button:hover,
.ev-cv-tabs button.is-active{
  color:#fff;
  border-color:rgba(234,124,18,.32);
  background:linear-gradient(135deg,var(--ev-cv-naranja),#F59E0B);
  box-shadow:0 12px 24px rgba(234,124,18,.20);
  transform:translateY(-1px);
}

.ev-cv-tabs button:hover span,
.ev-cv-tabs button.is-active span{
  color:#92400E;
  background:#FFF7ED;
}

.ev-cv-search{
  min-width:0;
  display:grid;
  grid-template-columns:1fr auto;
  gap:8px;
  position:relative;
}

.ev-cv-search > i{
  position:absolute;
  left:13px;
  top:50%;
  transform:translateY(-50%);
  color:#9CA3AF;
  pointer-events:none;
}

.ev-cv-search input{
  min-width:0;
  width:100%;
  min-height:42px;
  border:1px solid rgba(148,163,184,.26);
  border-radius:15px;
  padding:10px 12px 10px 38px;
  background:#fff;
  color:var(--ev-cv-texto);
  font-weight:700;
  outline:0;
  box-shadow:0 8px 18px rgba(15,23,42,.035);
}

.ev-cv-search input:focus{
  border-color:rgba(22,163,74,.48);
  box-shadow:0 0 0 4px rgba(22,163,74,.13);
}

.ev-cv-search button{
  min-height:42px;
  border:0;
  border-radius:15px;
  padding:10px 16px;
  color:#fff;
  background:linear-gradient(135deg,var(--ev-cv-verde-oscuro),var(--ev-cv-verde));
  font-weight:950;
  box-shadow:0 12px 24px rgba(15,89,47,.16);
  transition:transform .16s ease,box-shadow .16s ease,filter .16s ease;
}

.ev-cv-search button:hover{
  transform:translateY(-1px);
  filter:brightness(1.03);
}

/* ================================
   SECTION HEADERS
================================ */
.ev-cv-feature,
.ev-cv-content{
  margin-top:14px;
}

.ev-cv-section-head{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:12px;
  margin-bottom:12px;
}

.ev-cv-section-heading{
  display:flex;
  align-items:center;
  gap:10px;
  margin-bottom:12px;
}

.ev-cv-section-head .ev-cv-section-heading{
  margin-bottom:0;
}

.ev-cv-section-heading > i{
  width:36px;
  height:36px;
  display:grid;
  place-items:center;
  border-radius:14px;
  color:var(--ev-cv-verde-oscuro);
  background:#ECFDF3;
  border:1px solid rgba(22,163,74,.14);
}

.ev-cv-section-heading h2{
  margin:0;
  color:var(--ev-cv-verde-oscuro);
  font-size:1.08rem;
  font-weight:950;
  letter-spacing:-.02em;
}

#evCvMeta{
  margin:0;
  color:var(--ev-cv-suave);
  font-size:.82rem;
  font-weight:800;
}

/* ================================
   BADGES
================================ */
.ev-cv-badges{
  display:flex;
  flex-wrap:wrap;
  gap:6px;
  min-width:0;
}

.ev-cv-badge{
  display:inline-flex;
  align-items:center;
  gap:5px;
  min-height:25px;
  padding:5px 9px;
  border-radius:999px;
  background:#ECFDF3;
  color:#166534;
  border:1px solid rgba(22,163,74,.13);
  font-size:.68rem;
  line-height:1;
  font-weight:950;
  max-width:100%;
}

.ev-cv-badge--normal{
  background:#F3F4F6;
  color:#4B5563;
  border-color:#E5E7EB;
}

.ev-cv-badge--importante{
  background:#FFF7ED;
  color:#B45309;
  border-color:rgba(234,124,18,.18);
}

.ev-cv-badge--urgente{
  background:#FEF2F2;
  color:#B91C1C;
  border-color:rgba(220,38,38,.16);
}

.ev-cv-badge--featured{
  background:linear-gradient(135deg,#FFF7ED,#FFEDD5);
  color:#92400E;
  border-color:rgba(234,124,18,.22);
}

/* ================================
   DESTACADOS - GRID MAX 3
================================ */
#evCvDestacada{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:14px;
  align-items:stretch;
}

.ev-cv-feature-card{
  position:relative;
  min-width:0;
  height:100%;
  display:flex;
  flex-direction:column;
  overflow:hidden;
  border-radius:22px;
  background:#fff;
  border:1px solid rgba(234,124,18,.18);
  box-shadow:var(--ev-cv-shadow);
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease;
}

.ev-cv-feature-card:hover{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.34);
  box-shadow:0 22px 50px rgba(15,23,42,.10), var(--ev-cv-shadow-orange);
}

.ev-cv-feature-img{
  flex:0 0 178px;
  height:178px;
  display:flex;
  align-items:center;
  justify-content:center;
  overflow:hidden;
  background:
    radial-gradient(circle at 22% 12%, rgba(255,237,213,.90), transparent 34%),
    linear-gradient(135deg,#F9FAFB,#F3F4F6);
}

.ev-cv-feature-img img,
.ev-cv-card-img img{
  width:100%;
  height:100%;
  object-fit:contain;
  object-position:center;
  display:block;
  background:#F9FAFB;
}

.ev-cv-feature-body{
  min-width:0;
  flex:1 1 auto;
  display:flex;
  flex-direction:column;
  padding:14px;
}

.ev-cv-feature-body h3{
  margin:10px 0 7px;
  color:#182033;
  font-size:1rem;
  line-height:1.18;
  font-weight:950;
  letter-spacing:-.02em;
  overflow:hidden;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow-wrap:anywhere;
}

.ev-cv-feature-body > p{
  min-height:42px;
  margin:0 0 10px;
  color:var(--ev-cv-suave);
  font-size:.82rem;
  line-height:1.38;
  overflow:hidden;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow-wrap:anywhere;
}

.ev-cv-meta-row{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  margin-top:auto;
  color:#6B7280;
  font-size:.74rem;
  font-weight:800;
}

.ev-cv-meta-row span{
  display:inline-flex;
  align-items:center;
  gap:5px;
  min-width:0;
}

.ev-cv-read{
  width:100%;
  min-height:40px;
  margin-top:12px;
  border:0;
  border-radius:14px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  color:#fff;
  background:linear-gradient(135deg,var(--ev-cv-naranja),#F59E0B);
  font-size:.83rem;
  font-weight:950;
  box-shadow:0 12px 24px rgba(234,124,18,.24);
  transition:transform .16s ease,box-shadow .16s ease,filter .16s ease;
  white-space:nowrap;
}

.ev-cv-read:hover{
  transform:translateY(-1px);
  filter:brightness(1.03);
  box-shadow:0 16px 30px rgba(234,124,18,.30);
}

/* ================================
   PUBLICACIONES RECIENTES
================================ */
.ev-cv-grid{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:14px;
  align-items:stretch;
}

.ev-cv-card{
  min-width:0;
  height:100%;
  min-height:394px;
  display:flex;
  flex-direction:column;
  overflow:hidden;
  border-radius:20px;
  background:#fff;
  border:1px solid rgba(148,163,184,.18);
  box-shadow:var(--ev-cv-shadow-soft);
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease;
}

.ev-cv-card:hover{
  transform:translateY(-2px);
  border-color:rgba(22,163,74,.22);
  box-shadow:0 18px 36px rgba(15,23,42,.09);
}

.ev-cv-card-img{
  flex:0 0 166px;
  height:166px;
  display:flex;
  align-items:center;
  justify-content:center;
  overflow:hidden;
  background:linear-gradient(135deg,#F9FAFB,#F3F4F6);
}

.ev-cv-card-body{
  min-width:0;
  flex:1 1 auto;
  display:flex;
  flex-direction:column;
  padding:13px;
}

.ev-cv-card-body h3{
  min-height:42px;
  margin:9px 0 7px;
  color:#182033;
  font-size:.92rem;
  line-height:1.22;
  font-weight:950;
  letter-spacing:-.015em;
  overflow:hidden;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow-wrap:anywhere;
}

.ev-cv-card-body > p{
  min-height:42px;
  margin:0 0 10px;
  color:var(--ev-cv-suave);
  font-size:.79rem;
  line-height:1.38;
  overflow:hidden;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow-wrap:anywhere;
}

.ev-cv-card-footer{
  margin-top:auto;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  padding-top:10px;
  border-top:1px solid rgba(229,231,235,.74);
}

.ev-cv-card-footer time{
  color:#6B7280;
  font-size:.74rem;
  font-weight:800;
  white-space:nowrap;
}

.ev-cv-card-footer button{
  border:0;
  background:transparent;
  color:var(--ev-cv-verde-oscuro);
  padding:0;
  display:inline-flex;
  align-items:center;
  gap:6px;
  font-size:.78rem;
  font-weight:950;
  white-space:nowrap;
  transition:color .16s ease,gap .16s ease,transform .16s ease;
}

.ev-cv-card-footer button:hover{
  color:var(--ev-cv-naranja);
  gap:8px;
  transform:translateY(-1px);
}

.ev-cv-no-img{
  width:100%;
  height:100%;
  display:grid;
  place-items:center;
  gap:8px;
  text-align:center;
  color:#0F592F;
  padding:18px;
}

.ev-cv-no-img i{
  width:54px;
  height:54px;
  display:grid;
  place-items:center;
  border-radius:18px;
  background:#ECFDF3;
  border:1px solid rgba(22,163,74,.14);
  font-size:1.45rem;
}

.ev-cv-no-img span{
  color:#6B7280;
  font-size:.78rem;
  font-weight:900;
}

.ev-cv-publicacion-seleccionada{
  border-color:rgba(234,124,18,.72);
  box-shadow:
    0 0 0 4px rgba(234,124,18,.12),
    0 22px 50px rgba(234,124,18,.16),
    0 12px 28px rgba(15,23,42,.08);
}

/* ================================
   EMPTY / SKELETON / PAGER / ERROR
================================ */
.ev-cv-empty{
  grid-column:1 / -1;
  min-height:190px;
  border:1px dashed rgba(15,89,47,.25);
  border-radius:20px;
  background:linear-gradient(135deg,#F0FDF4,#FFFFFF);
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  text-align:center;
  gap:8px;
  padding:24px;
}

.ev-cv-empty i{
  color:rgba(15,89,47,.42);
  font-size:2rem;
}

.ev-cv-empty strong{
  color:var(--ev-cv-verde-oscuro);
  font-weight:950;
}

.ev-cv-empty p{
  max-width:440px;
  margin:0;
  color:var(--ev-cv-suave);
  font-size:.86rem;
  line-height:1.45;
}

.ev-cv-skeleton{
  min-height:394px;
  border-radius:20px;
  background:linear-gradient(90deg,#F3F4F6 0%,#FFFFFF 50%,#F3F4F6 100%);
  background-size:200% 100%;
  animation:evCvSkeleton 1.2s ease-in-out infinite;
}

@keyframes evCvSkeleton{
  from{ background-position:200% 0; }
  to{ background-position:-200% 0; }
}

.ev-cv-pager{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  margin-top:14px;
}

.ev-cv-pager button{
  width:38px;
  height:38px;
  display:grid;
  place-items:center;
  border:1px solid rgba(148,163,184,.24);
  border-radius:14px;
  background:#fff;
  color:var(--ev-cv-verde-oscuro);
  font-weight:900;
  box-shadow:0 8px 18px rgba(15,23,42,.045);
}

.ev-cv-pager button:disabled{
  opacity:.45;
  cursor:not-allowed;
}

.ev-cv-pager span{
  color:#374151;
  font-size:.82rem;
  font-weight:900;
}

.ev-cv-error{
  margin-top:14px;
  border-radius:18px;
  padding:14px 16px;
  display:flex;
  align-items:center;
  gap:10px;
  color:#991B1B;
  background:#FEF2F2;
  border:1px solid #FECACA;
  font-weight:850;
}

.ev-cv-error.d-none{
  display:none;
}

/* ================================
   MODAL DETALLE
================================ */
.ev-cv-modal .modal-dialog{
  max-width:min(920px, calc(100vw - 24px));
}

.ev-cv-modal .modal-content.ev-cv-modal-content,
.ev-cv-modal-content{
  position:relative;
  overflow:hidden;
  border:0;
  outline:0;
  padding:0;
  border-radius:26px;
  background:
    radial-gradient(circle at 88% 16%, rgba(255,255,255,.16), transparent 34%),
    linear-gradient(135deg,var(--ev-cv-verde-oscuro),var(--ev-cv-verde),var(--ev-cv-verde-claro));
  background-clip:padding-box;
  box-shadow:0 34px 86px rgba(15,23,42,.24),0 12px 28px rgba(15,23,42,.12);
  transform:translateZ(0);
}

.ev-cv-modal-head{
  position:relative;
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:14px;
  padding:18px 20px;
  color:#fff;
  border:0;
  margin:0;
  background:
    radial-gradient(circle at 88% 16%, rgba(255,255,255,.16), transparent 34%),
    linear-gradient(135deg,var(--ev-cv-verde-oscuro),var(--ev-cv-verde),var(--ev-cv-verde-claro));
  box-shadow:none;
  isolation:isolate;
}

.ev-cv-modal-head::after{
  content:"";
  position:absolute;
  left:0;
  right:0;
  bottom:-1px;
  height:2px;
  background:linear-gradient(90deg,var(--ev-cv-verde-oscuro),var(--ev-cv-verde),var(--ev-cv-verde-claro));
  pointer-events:none;
  z-index:0;
}

.ev-cv-modal-head > *{
  position:relative;
  z-index:1;
}

.ev-cv-modal-heading{
  min-width:0;
  display:flex;
  align-items:flex-start;
  gap:12px;
}

.ev-cv-modal-heading-icon{
  width:46px;
  height:46px;
  flex:0 0 auto;
  display:grid;
  place-items:center;
  border-radius:17px;
  color:#0F592F;
  background:rgba(255,255,255,.92);
  box-shadow:0 10px 20px rgba(15,23,42,.12);
}

.ev-cv-modal-heading-copy{
  min-width:0;
}

.ev-cv-modal-eyebrow{
  display:block;
  margin-bottom:3px;
  color:rgba(255,255,255,.82);
  font-size:.72rem;
  font-weight:950;
  letter-spacing:.08em;
  text-transform:uppercase;
}

.ev-cv-modal-head h2{
  margin:0 0 7px;
  color:#fff;
  font-size:clamp(1.18rem,2.2vw,1.65rem);
  line-height:1.12;
  font-weight:950;
  letter-spacing:-.025em;
  overflow-wrap:anywhere;
}

.ev-cv-modal-head-meta{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
}

.ev-cv-modal-type,
.ev-cv-modal-community,
.ev-cv-modal-priority,
.ev-cv-modal-featured{
  display:inline-flex;
  align-items:center;
  gap:6px;
  min-height:28px;
  padding:6px 10px;
  border-radius:999px;
  font-size:.72rem;
  line-height:1;
  font-weight:950;
}

.ev-cv-modal-type,
.ev-cv-modal-community{
  color:#fff;
  background:rgba(255,255,255,.14);
  border:1px solid rgba(255,255,255,.22);
}

.ev-cv-modal-close{
  width:40px;
  height:40px;
  border:1px solid rgba(255,255,255,.22);
  border-radius:14px;
  display:grid;
  place-items:center;
  flex:0 0 auto;
  color:#fff;
  background:rgba(255,255,255,.10);
  transition:background .16s ease,transform .16s ease;
}

.ev-cv-modal-close:hover{
  background:rgba(255,255,255,.18);
  transform:translateY(-1px);
}

.ev-cv-modal-surface{
  position:relative;
  z-index:2;
  margin-top:-1px;
  border:0;
  background:#fff;
}

.ev-cv-modal-body{
  max-height:min(68vh, 680px);
  overflow:auto;
  padding:18px 20px 20px;
  border:0;
  background:#fff;
}

.ev-cv-modal-topline{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin-bottom:14px;
}

.ev-cv-modal-badges{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
}

.ev-cv-modal-priority--normal{
  color:#4B5563;
  background:#F3F4F6;
}

.ev-cv-modal-priority--importante{
  color:#B45309;
  background:#FFF7ED;
}

.ev-cv-modal-priority--urgente{
  color:#B91C1C;
  background:#FEF2F2;
}

.ev-cv-modal-featured{
  color:#92400E;
  background:#FFF7ED;
  border:1px solid rgba(234,124,18,.18);
}

.ev-cv-modal-date{
  display:inline-flex;
  align-items:center;
  gap:7px;
  color:#6B7280;
  font-size:.82rem;
  font-weight:850;
  white-space:nowrap;
}

.ev-cv-modal-media{
  border-radius:20px;
  overflow:hidden;
  background:#F9FAFB;
  border:1px solid #EEF2F7;
  display:flex;
  align-items:center;
  justify-content:center;
  margin-bottom:10px;
}

.ev-cv-modal-image{
  max-width:100%;
  max-height:430px;
  width:auto;
  height:auto;
  display:block;
  object-fit:contain;
}

.ev-cv-modal-image-open{
  width:fit-content;
  max-width:100%;
  margin:12px auto 18px;
  min-height:40px;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  padding:10px 16px;
  border-radius:999px;
  color:#fff;
  background:linear-gradient(135deg,var(--ev-cv-naranja),#F59E0B);
  font-size:.83rem;
  font-weight:950;
  text-decoration:none;
  box-shadow:0 12px 24px rgba(234,124,18,.24);
  transition:
    transform .16s ease,
    box-shadow .16s ease,
    filter .16s ease,
    background .16s ease;
}

.ev-cv-modal-image-open:hover{
  color:#fff;
  background:linear-gradient(135deg,var(--ev-cv-naranja-oscuro),#EA580C);
  box-shadow:0 16px 30px rgba(234,124,18,.34);
  transform:translateY(-1px);
  filter:brightness(1.03);
}

.ev-cv-modal-image-open:focus-visible{
  outline:0;
  box-shadow:
    0 0 0 4px rgba(234,124,18,.18),
    0 16px 30px rgba(234,124,18,.30);
}

.ev-cv-modal-image-open:active{
  transform:translateY(0);
  box-shadow:0 8px 18px rgba(234,124,18,.24);
}

.ev-cv-modal-block{
  margin-top:14px;
  padding:14px;
  border-radius:18px;
  background:#FCFDFC;
  border:1px solid #EEF2F7;
}

.ev-cv-modal-block--summary{
  background:linear-gradient(135deg,#F0FDF4,#FFFFFF);
  border-color:rgba(22,163,74,.15);
}

.ev-cv-modal-block-label{
  display:block;
  margin-bottom:7px;
  color:var(--ev-cv-verde-oscuro);
  font-size:.72rem;
  font-weight:950;
  letter-spacing:.08em;
  text-transform:uppercase;
}

.ev-cv-modal-summary,
.ev-cv-modal-text{
  margin:0;
  color:#374151;
  line-height:1.62;
  font-size:.94rem;
  white-space:pre-wrap;
  overflow-wrap:anywhere;
  word-break:break-word;
}

.ev-cv-modal-event{
  margin-top:14px;
  min-height:44px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:11px 13px;
  border-radius:16px;
  color:#1D4ED8;
  background:#EFF6FF;
  border:1px solid rgba(37,99,235,.14);
  font-weight:850;
  overflow-wrap:anywhere;
}

.ev-cv-modal-footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:14px 20px;
  border-top:1px solid #EEF2F7;
  background:#F9FAFB;
}

.ev-cv-modal-footer span{
  min-width:0;
  display:inline-flex;
  align-items:center;
  gap:8px;
  color:#6B7280;
  font-size:.82rem;
  font-weight:850;
}

.ev-cv-modal-footer button{
  min-height:38px;
  border:1px solid rgba(148,163,184,.32);
  border-radius:999px;
  padding:9px 18px;
  color:#374151;
  background:#fff;
  font-weight:950;
  box-shadow:0 8px 18px rgba(15,23,42,.055);
  transition:
    background .16s ease,
    border-color .16s ease,
    color .16s ease,
    transform .16s ease,
    box-shadow .16s ease;
}

.ev-cv-modal-footer button:hover{
  color:var(--ev-cv-verde-oscuro);
  background:#ECFDF3;
  border-color:rgba(22,163,74,.22);
  transform:translateY(-1px);
  box-shadow:0 10px 22px rgba(15,23,42,.075);
}

/* ================================
   RESPONSIVE
================================ */
@media (max-width:1199.98px){
  #evCvDestacada,
  .ev-cv-grid{
    grid-template-columns:repeat(2,minmax(0,1fr));
  }
}

@media (max-width:991.98px){
  .ev-cv-shell{
    padding:14px 12px 24px;
  }

  .ev-cv-hero{
    padding:22px 18px;
  }

  .ev-cv-toolbar{
    grid-template-columns:1fr;
  }
}

@media (max-width:767.98px){
  .ev-cv-hero{
    align-items:stretch;
    flex-direction:column;
    gap:16px;
  }

  .ev-cv-hero-copy{
    width:100%;
    max-width:none;
  }

  .ev-cv-hero p{
    width:100%;
    max-width:none;
  }

  .ev-cv-community-pill{
    width:100%;
    max-width:none;
    align-self:stretch;
    padding:13px 14px;
  }

  .ev-cv-community-pill strong{
    font-size:.94rem;
    line-height:1.24;
  }

  #evCvDestacada,
  .ev-cv-grid{
    grid-template-columns:1fr;
  }

  .ev-cv-section-head{
    align-items:flex-start;
    flex-direction:column;
  }

  .ev-cv-card{
    min-height:0;
  }

  .ev-cv-feature-img,
  .ev-cv-card-img{
    flex-basis:176px;
    height:176px;
  }

  .ev-cv-modal .modal-dialog{
    max-width:calc(100vw - 12px);
    margin:.5rem auto;
  }

  .ev-cv-modal-content{
    border-radius:22px;
  }

  .ev-cv-modal-head{
    padding:16px;
  }

  .ev-cv-modal-heading-icon{
    width:42px;
    height:42px;
    border-radius:15px;
  }

  .ev-cv-modal-body{
    max-height:70vh;
    padding:16px;
  }

  .ev-cv-modal-topline,
  .ev-cv-modal-footer{
    align-items:flex-start;
    flex-direction:column;
  }

  .ev-cv-modal-footer button{
    width:100%;
  }
}

@media (max-width:575.98px){
  .ev-cv-shell{
    padding:10px 10px 22px;
  }

  .ev-cv-hero,
  .ev-cv-feature-card,
  .ev-cv-card{
    border-radius:18px;
  }

  .ev-cv-community-pill{
    width:100%;
    display:grid;
    grid-template-columns:44px minmax(0,1fr);
    align-items:center;
    gap:11px;
  }

  .ev-cv-community-pill > i{
    width:44px;
    height:44px;
    border-radius:14px;
  }

  .ev-cv-community-pill small{
    font-size:.70rem;
  }

  .ev-cv-community-pill strong{
    font-size:.91rem;
    text-wrap:pretty;
  }

  .ev-cv-tabs{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
  }

  .ev-cv-tabs button{
    justify-content:center;
  }

  .ev-cv-search{
    grid-template-columns:1fr;
  }

  .ev-cv-search button{
    width:100%;
  }

  .ev-cv-feature-body,
  .ev-cv-card-body{
    padding:13px;
  }

  .ev-cv-card-footer{
    align-items:flex-start;
    flex-direction:column;
  }
}


/* ============================================================
   CIERRE UX/UI EV — NOVEDADES DE LA COMUNIDAD
============================================================ */
.ev-cv-shell{
  width:min(100%,1540px);
  margin:0 auto;
}
.ev-cv-hero{
  align-items:center;
  min-height:174px;
}
.ev-cv-hero-copy{
  max-width:780px;
}
.ev-cv-community-pill{
  flex:0 1 360px;
  min-width:250px;
}
.ev-cv-community-pill > div{
  min-width:0;
}
.ev-cv-toolbar{
  padding:13px 14px;
  border:1px solid rgba(148,163,184,.18);
  border-radius:18px;
  background:#fff;
  box-shadow:var(--ev-cv-shadow-soft);
}
.ev-cv-content,
.ev-cv-feature{
  padding:14px;
  border:1px solid rgba(148,163,184,.18);
  border-radius:20px;
  background:#fff;
  box-shadow:var(--ev-cv-shadow-soft);
}
.ev-cv-section-head{
  padding-bottom:10px;
  border-bottom:1px solid rgba(229,231,235,.72);
}
.ev-cv-grid:has(.ev-cv-empty),
#evCvDestacada:has(.ev-cv-empty){
  grid-template-columns:1fr;
}

@media (max-width:991.98px){
  .ev-cv-hero{
    align-items:flex-start;
  }
  .ev-cv-community-pill{
    flex-basis:310px;
  }
}

@media (max-width:767.98px){
  .ev-cv-hero{
    min-height:0;
  }
  .ev-cv-hero h1{
    font-size:clamp(1.65rem,8vw,2rem);
    line-height:1.08;
    text-wrap:balance;
  }
  .ev-cv-hero p{
    font-size:.94rem;
    line-height:1.55;
  }
  .ev-cv-toolbar,
  .ev-cv-content,
  .ev-cv-feature{
    padding:12px;
    border-radius:18px;
  }
}

@media (max-width:575.98px){
  .ev-cv-shell{
    padding:10px 8px 22px;
  }
  .ev-cv-hero{
    padding:20px 16px 18px;
  }
  .ev-cv-kicker{
    margin-bottom:9px;
  }
  .ev-cv-community-pill{
    min-width:0;
    padding:12px;
  }
  .ev-cv-community-pill strong{
    display:block;
    max-width:100%;
    font-size:.90rem;
    line-height:1.22;
    overflow-wrap:normal;
    word-break:normal;
  }
  .ev-cv-toolbar{
    gap:12px;
  }
  .ev-cv-search input,
  .ev-cv-search button{
    min-height:44px;
  }
  .ev-cv-section-head{
    gap:7px;
  }
  #evCvMeta{
    width:100%;
  }
}

/* ============================================================
   EV V4 — ANCHO OPERATIVO Y FILTRO ÚNICO DE NOVEDADES
============================================================ */
.ev-cv-shell{
  width:100%;
  max-width:none;
  margin:0 auto;
  padding:14px 14px 26px;
}
.ev-cv-toolbar{
  grid-template-columns:minmax(220px,280px) minmax(0,1fr);
  align-items:end;
}
.ev-cv-filter{
  min-width:0;
  display:flex;
  flex-direction:column;
  gap:7px;
}
.ev-cv-filter label{
  color:var(--ev-cv-verde-oscuro);
  font-size:.78rem;
  font-weight:900;
  letter-spacing:.02em;
}
.ev-cv-filter-select{
  width:100%;
  min-height:44px;
  border:1px solid rgba(22,163,74,.24);
  border-radius:14px;
  padding:10px 38px 10px 13px;
  color:#24372B;
  background:#fff;
  font:inherit;
  font-size:.88rem;
  font-weight:800;
  outline:0;
  box-shadow:0 8px 18px rgba(15,23,42,.035);
  transition:border-color .16s ease,box-shadow .16s ease;
}
.ev-cv-filter-select:hover{
  border-color:rgba(234,124,18,.48);
}
.ev-cv-filter-select:focus{
  border-color:var(--ev-cv-naranja);
  box-shadow:0 0 0 4px rgba(234,124,18,.12);
}
.ev-cv-search button{
  background:linear-gradient(135deg,var(--ev-cv-naranja),#F59E0B);
  box-shadow:0 12px 26px rgba(234,124,18,.26);
}
.ev-cv-search button:hover,
.ev-cv-search button:focus-visible{
  background:linear-gradient(135deg,var(--ev-cv-naranja-oscuro),var(--ev-cv-naranja));
  box-shadow:0 16px 30px rgba(234,124,18,.34);
  outline:0;
}
@media(max-width:767.98px){
  .ev-cv-toolbar{
    grid-template-columns:1fr;
    align-items:stretch;
  }
  .ev-cv-filter,
  .ev-cv-search{
    width:100%;
  }
}
@media(max-width:575.98px){
  .ev-cv-shell{
    padding:10px 8px 22px;
  }
  .ev-cv-search{
    grid-template-columns:1fr;
  }
  .ev-cv-search > i{
    top:22px;
  }
  .ev-cv-search button{
    width:100%;
  }
}



/* ============================================================
   EV V5 — TARJETA DE COMUNIDAD COMPACTA EN MÓVIL
============================================================ */
@media(max-width:767.98px){
  .ev-cv-hero{
    display:flex;
    flex-direction:column;
    align-items:stretch;
  }

  .ev-cv-community-pill{
    flex:none !important;
    flex-basis:auto !important;
    width:100%;
    min-width:0;
    min-height:0;
    align-self:auto;
    padding:11px 13px;
  }
}

@media(max-width:575.98px){
  .ev-cv-community-pill{
    grid-template-columns:40px minmax(0,1fr);
    gap:10px;
    padding:10px 12px;
    border-radius:15px;
  }

  .ev-cv-community-pill > i{
    width:40px;
    height:40px;
    border-radius:13px;
    font-size:1.05rem;
  }

  .ev-cv-community-pill small{
    font-size:.68rem;
  }

  .ev-cv-community-pill strong{
    font-size:.88rem;
    line-height:1.2;
  }
}

</style>
<style>
/* Administrador EV: selector global y acabado premium de Comunidad. */
.ev-cv-admin-selector{display:grid;grid-template-columns:minmax(0,1fr) minmax(320px,520px);gap:16px;align-items:center;margin-top:14px;padding:15px 17px;border:1px solid rgba(148,163,184,.18);border-radius:20px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.055)}
.ev-cv-admin-selector-copy{display:flex;align-items:center;gap:11px;min-width:0}.ev-cv-admin-selector-copy>span{width:42px;height:42px;display:grid;place-items:center;flex:0 0 auto;border-radius:14px;color:#0F592F;background:#ECFDF3;border:1px solid rgba(22,163,74,.16)}.ev-cv-admin-selector-copy strong{display:block;color:#0F592F;font-weight:950}.ev-cv-admin-selector-copy small{display:block;margin-top:2px;color:#6B7280;line-height:1.4}.ev-cv-admin-selector label{display:grid;gap:6px}.ev-cv-admin-selector label>span{color:#334155;font-size:.75rem;font-weight:900}.ev-cv-admin-selector select{width:100%;min-height:44px;padding:9px 12px;border:1px solid #CFE9D9;border-radius:13px;background:#fff;color:#111827;font:inherit;font-weight:750;outline:0}.ev-cv-admin-selector select:focus{border-color:#16A34A;box-shadow:0 0 0 4px rgba(22,163,74,.12)}
.ev-cv-toolbar{padding:14px 16px;border:1px solid rgba(148,163,184,.18);border-radius:20px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.05)}
.ev-cv-filter{display:grid;gap:6px}.ev-cv-filter label{color:#334155;font-size:.75rem;font-weight:900}.ev-cv-filter-select{min-height:42px;padding:9px 12px;border:1px solid #CFE9D9;border-radius:13px;background:#fff;color:#111827;font:inherit;font-weight:750;outline:0}.ev-cv-filter-select:focus{border-color:#16A34A;box-shadow:0 0 0 4px rgba(22,163,74,.12)}
.ev-cv-search button{background:linear-gradient(135deg,#EA7C12,#F59E0B)!important;box-shadow:0 12px 24px rgba(234,124,18,.19)!important}
@media(max-width:800px){.ev-cv-admin-selector{grid-template-columns:1fr}.ev-cv-toolbar{grid-template-columns:1fr!important}.ev-cv-admin-selector select{font-size:.86rem}}
</style>
