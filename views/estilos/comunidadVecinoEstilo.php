<?php /* views/estilos/comunidadVecinoEstilo.php */ ?>

<style>
:root{
  --ev-cv-green-dark:#0F592F;
  --ev-cv-green-mid:#0E7A43;
  --ev-cv-green:#16A34A;
  --ev-cv-green-soft:#E9F8EF;
  --ev-cv-orange:#EA7C12;
  --ev-cv-orange-soft:#FFF7ED;
  --ev-cv-blue:#2563EB;
  --ev-cv-red:#DC2626;
  --ev-cv-text:#111827;
  --ev-cv-muted:#667085;
  --ev-cv-border:#E5E7EB;
  --ev-cv-bg:#F5F7FA;
  --ev-cv-shadow:0 14px 38px rgba(15,23,42,.07);
  --ev-cv-radius:20px;
}

/* ============================================================
   CONTENEDOR GENERAL
============================================================ */

.ev-cv-shell{
  padding:22px 24px 34px;
  color:var(--ev-cv-text);
  font-family:Poppins,system-ui,-apple-system,"Segoe UI",sans-serif;
}

/* ============================================================
   HERO
============================================================ */

.ev-cv-hero{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:30px;
  padding:28px 30px;
  border-radius:26px;
  border:1px solid rgba(15,89,47,.10);
  background:
    radial-gradient(circle at 90% 12%, rgba(22,163,74,.13), transparent 34%),
    radial-gradient(circle at 7% 82%, rgba(234,124,18,.10), transparent 28%),
    #fff;
  box-shadow:var(--ev-cv-shadow);
}

.ev-cv-kicker{
  display:inline-flex;
  gap:7px;
  align-items:center;
  min-height:32px;
  padding:6px 14px;
  border-radius:999px;
  color:var(--ev-cv-green-dark);
  background:var(--ev-cv-green-soft);
  border:1px solid rgba(22,163,74,.19);
  font-weight:800;
  font-size:.77rem;
  text-transform:uppercase;
  letter-spacing:.04em;
  margin-bottom:12px;
}

.ev-cv-hero h1{
  margin:0 0 8px;
  color:var(--ev-cv-green-dark);
  font-size:clamp(1.7rem,2.25vw,2.15rem);
  line-height:1.12;
  font-weight:800;
  letter-spacing:-.04em;
}

.ev-cv-hero p{
  margin:0;
  max-width:620px;
  color:var(--ev-cv-muted);
  line-height:1.55;
  font-size:.96rem;
}

.ev-cv-community-pill{
  flex:0 0 auto;
  display:flex;
  align-items:center;
  gap:13px;
  min-width:265px;
  max-width:360px;
  padding:14px 16px;
  border-radius:18px;
  border:1px solid rgba(22,163,74,.18);
  background:rgba(248,255,251,.85);
}

.ev-cv-community-pill i{
  width:44px;
  height:44px;
  display:grid;
  place-items:center;
  border-radius:14px;
  background:#E3F8EB;
  color:var(--ev-cv-green-dark);
  font-size:1.18rem;
}

.ev-cv-community-pill small{
  display:block;
  color:var(--ev-cv-muted);
  font-size:.71rem;
  font-weight:700;
  margin-bottom:2px;
}

.ev-cv-community-pill strong{
  display:block;
  font-size:.89rem;
  color:var(--ev-cv-green-dark);
  line-height:1.35;
}

/* ============================================================
   FILTROS
============================================================ */

.ev-cv-toolbar{
  margin-top:16px;
  padding:12px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  background:#fff;
  border:1px solid rgba(148,163,184,.21);
  border-radius:20px;
  box-shadow:0 8px 26px rgba(15,23,42,.045);
}

.ev-cv-tabs{
  display:flex;
  align-items:center;
  gap:6px;
  overflow:auto;
  scrollbar-width:none;
}

.ev-cv-tabs::-webkit-scrollbar{
  display:none;
}

.ev-cv-tabs button{
  display:inline-flex;
  align-items:center;
  gap:8px;
  border:1px solid transparent;
  background:transparent;
  border-radius:13px;
  color:var(--ev-cv-muted);
  padding:10px 12px;
  font-weight:700;
  white-space:nowrap;
  transition:all .16s ease;
}

.ev-cv-tabs button span{
  min-width:22px;
  height:22px;
  padding:2px 6px;
  border-radius:999px;
  background:#F3F4F6;
  color:#64748B;
  font-size:.73rem;
  font-weight:800;
}

.ev-cv-tabs button:hover,
.ev-cv-tabs button.is-active{
  background:var(--ev-cv-green-soft);
  color:var(--ev-cv-green-dark);
  border-color:rgba(22,163,74,.18);
}

.ev-cv-tabs button.is-active span{
  background:#D7F3E2;
  color:var(--ev-cv-green-dark);
}

.ev-cv-search{
  height:47px;
  flex:0 1 400px;
  display:flex;
  align-items:center;
  gap:10px;
  padding-left:14px;
  background:#fff;
  border:1px solid #DCE3EA;
  border-radius:14px;
  overflow:hidden;
  transition:border-color .16s ease, box-shadow .16s ease;
}

.ev-cv-search:focus-within{
  border-color:rgba(22,163,74,.55);
  box-shadow:0 0 0 4px rgba(22,163,74,.10);
}

.ev-cv-search i{
  color:#94A3B8;
}

.ev-cv-search input{
  min-width:120px;
  flex:1;
  border:0;
  outline:0;
  font-size:.9rem;
  color:var(--ev-cv-text);
  background:transparent;
}

.ev-cv-search button{
  align-self:stretch;
  border:0;
  background:var(--ev-cv-green-dark);
  color:#fff;
  padding:0 17px;
  font-size:.86rem;
  font-weight:700;
  transition:background .16s ease;
}

.ev-cv-search button:hover{
  background:#0C4827;
}

/* ============================================================
   SECCIONES
============================================================ */

.ev-cv-feature,
.ev-cv-content{
  margin-top:16px;
  padding:18px;
  background:#fff;
  border:1px solid rgba(148,163,184,.20);
  border-radius:22px;
  box-shadow:var(--ev-cv-shadow);
}

.ev-cv-section-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:15px;
  margin-bottom:15px;
}

.ev-cv-section-head p{
  margin:0;
  color:var(--ev-cv-muted);
  font-size:.84rem;
}

.ev-cv-section-heading{
  display:flex;
  align-items:center;
  gap:9px;
  margin-bottom:14px;
}

.ev-cv-content .ev-cv-section-heading{
  margin-bottom:0;
}

.ev-cv-section-heading i{
  width:34px;
  height:34px;
  display:grid;
  place-items:center;
  border-radius:11px;
  color:var(--ev-cv-green-dark);
  background:var(--ev-cv-green-soft);
}

.ev-cv-feature .ev-cv-section-heading i{
  color:var(--ev-cv-orange);
  background:var(--ev-cv-orange-soft);
}

.ev-cv-section-heading h2{
  margin:0;
  color:var(--ev-cv-green-dark);
  font-weight:800;
  font-size:1rem;
}

/* ============================================================
   PUBLICACIÓN DESTACADA
============================================================ */

.ev-cv-feature-card{
  position:relative;
  display:grid;
  grid-template-columns:minmax(220px,37%) 1fr;
  gap:20px;
  min-height:252px;
  padding:12px;
  border-radius:18px;
  border:1px solid rgba(22,163,74,.18);
  background:linear-gradient(115deg,#F3FCF7 0%,#FFFFFF 64%);
  overflow:hidden;
}

.ev-cv-feature-img,
.ev-cv-card-img{
  border-radius:14px;
  overflow:hidden;
  background:#F1F7F3;
  display:grid;
  place-items:center;
}

.ev-cv-feature-img{
  height:264px;
  padding:9px;
}

.ev-cv-feature-img img{
  display:block;
  width:100%;
  height:100%;
  object-fit:contain;
  object-position:center center;
  background:#F6FBF8;
}

.ev-cv-no-img{
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:7px;
  height:100%;
  min-height:150px;
  color:rgba(15,89,47,.58);
  font-weight:700;
}

.ev-cv-no-img i{
  font-size:2rem;
}

.ev-cv-feature-body{
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:flex-start;
  padding:10px 14px 10px 0;
  min-width:0;
}

.ev-cv-badges{
  display:flex;
  gap:7px;
  flex-wrap:wrap;
  margin-bottom:12px;
}

.ev-cv-badge{
  display:inline-flex;
  align-items:center;
  gap:5px;
  padding:5px 10px;
  border-radius:999px;
  font-size:.72rem;
  font-weight:800;
  background:#E8F8EF;
  color:var(--ev-cv-green-dark);
}

.ev-cv-badge--featured{
  background:#FFF3E5;
  color:#C76509;
}

.ev-cv-badge--urgente{
  background:#FEF2F2;
  color:#B42318;
}

.ev-cv-badge--importante{
  background:#FFF7ED;
  color:#C76509;
}

.ev-cv-badge--normal{
  background:#F3F4F6;
  color:#475467;
}

.ev-cv-feature-body h3{
  color:var(--ev-cv-green-dark);
  font-size:1.35rem;
  line-height:1.25;
  font-weight:800;
  margin:0 0 8px;
  overflow-wrap:anywhere;
}

.ev-cv-feature-body p{
  color:var(--ev-cv-muted);
  line-height:1.55;
  font-size:.9rem;
  margin:0 0 14px;
  overflow-wrap:anywhere;
}

.ev-cv-meta-row{
  display:flex;
  align-items:center;
  gap:14px;
  color:#667085;
  font-size:.79rem;
  margin-bottom:16px;
  flex-wrap:wrap;
}

.ev-cv-meta-row span{
  display:inline-flex;
  align-items:center;
  gap:5px;
}

.ev-cv-read{
  display:inline-flex;
  align-items:center;
  gap:7px;
  border:0;
  background:linear-gradient(135deg,var(--ev-cv-orange),#F59E0B);
  color:#fff;
  border-radius:12px;
  padding:11px 17px;
  font-size:.84rem;
  font-weight:800;
  box-shadow:0 11px 22px rgba(234,124,18,.26);
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease;
}

.ev-cv-read:hover{
  transform:translateY(-1px);
  box-shadow:0 15px 27px rgba(234,124,18,.35);
  filter:brightness(1.02);
}

/* ============================================================
   TARJETAS DE PUBLICACIONES
============================================================ */

.ev-cv-grid{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:14px;
}

.ev-cv-card{
  min-width:0;
  border:1px solid #E4E9EF;
  border-radius:18px;
  background:#fff;
  overflow:hidden;
  display:flex;
  flex-direction:column;
  transition:transform .17s ease, box-shadow .17s ease, border-color .17s ease;
}

.ev-cv-card:hover{
  transform:translateY(-2px);
  box-shadow:0 18px 38px rgba(15,23,42,.09);
  border-color:rgba(22,163,74,.24);
}

.ev-cv-card-img{
  height:164px;
  margin:10px 10px 0;
  padding:7px;
}

.ev-cv-card-img img{
  display:block;
  width:100%;
  height:100%;
  object-fit:contain;
  object-position:center center;
  background:#F7FBF8;
}

.ev-cv-card-body{
  display:flex;
  flex-direction:column;
  flex:1;
  padding:14px;
}

.ev-cv-card-body h3{
  margin:9px 0 7px;
  color:var(--ev-cv-green-dark);
  font-size:.95rem;
  line-height:1.35;
  font-weight:800;
  overflow-wrap:anywhere;
}

.ev-cv-card-body p{
  color:var(--ev-cv-muted);
  line-height:1.5;
  font-size:.81rem;
  margin:0 0 14px;
  flex:1;
  overflow-wrap:anywhere;
}

.ev-cv-card-footer{
  border-top:1px solid #EEF2F6;
  padding-top:11px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:8px;
}

.ev-cv-card-footer time{
  font-size:.73rem;
  color:#667085;
  font-weight:600;
}

.ev-cv-card-footer button{
  color:var(--ev-cv-green-dark);
  border:0;
  background:transparent;
  font-size:.77rem;
  font-weight:800;
}

/* ============================================================
   VACÍO / CARGANDO / PAGINADOR
============================================================ */

.ev-cv-empty{
  grid-column:1/-1;
  min-height:232px;
  border:1px dashed rgba(15,89,47,.20);
  background:#FAFDFC;
  border-radius:17px;
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
  text-align:center;
  gap:8px;
  padding:26px;
}

.ev-cv-empty i{
  font-size:2rem;
  color:rgba(15,89,47,.36);
}

.ev-cv-empty strong{
  color:var(--ev-cv-green-dark);
}

.ev-cv-empty p{
  color:var(--ev-cv-muted);
  font-size:.88rem;
  margin:0;
}

.ev-cv-skeleton{
  height:300px;
  border-radius:18px;
  background:linear-gradient(90deg,#F2F5F8 25%,#FCFDFD 38%,#F2F5F8 58%);
  background-size:240% 100%;
  animation:evCvPulse 1.15s linear infinite;
}

@keyframes evCvPulse{
  to{
    background-position:-240% 0;
  }
}

.ev-cv-pager{
  justify-content:flex-end;
  align-items:center;
  gap:9px;
  margin-top:16px;
}

.ev-cv-pager:not([hidden]){
  display:flex;
}

.ev-cv-pager button,
.ev-cv-pager span{
  min-width:38px;
  height:38px;
  display:grid;
  place-items:center;
  border:1px solid #E5E7EB;
  border-radius:11px;
  background:#fff;
  color:var(--ev-cv-green-dark);
  font-weight:800;
}

.ev-cv-pager button:disabled{
  opacity:.4;
  cursor:not-allowed;
}

.ev-cv-error{
  margin-top:14px;
  border-radius:15px;
  padding:14px 17px;
  border:1px solid #FECACA;
  color:#991B1B;
  background:#FEF2F2;
  font-weight:700;
}

/* ============================================================
   MODAL DETALLE - ESTÁNDAR EV PREMIUM
============================================================ */

.ev-cv-modal .modal-dialog{
  max-width:790px;
}

.ev-cv-modal-content{
  --ev-cv-modal-radius:24px;
  --ev-cv-modal-head-bg:linear-gradient(
    140deg,
    var(--ev-cv-green-dark) 0%,
    var(--ev-cv-green-mid) 56%,
    var(--ev-cv-green) 100%
  );

  --bs-modal-bg:transparent;
  --bs-modal-border-width:0;
  --bs-modal-border-color:transparent;
  --bs-modal-border-radius:var(--ev-cv-modal-radius);

  position:relative;
  display:flex;
  flex-direction:column;
  max-height:min(92vh,860px);
  border:0 !important;
  outline:0 !important;
  border-radius:var(--ev-cv-modal-radius) !important;
  overflow:hidden;
  background:var(--ev-cv-modal-head-bg) !important;
  box-shadow:
    0 30px 76px rgba(15,23,42,.30),
    0 8px 24px rgba(15,23,42,.14) !important;
}

#modalDetalleComunidadVecino .modal-content.ev-cv-modal-content{
  border:0 !important;
  outline:0 !important;
  background:var(--ev-cv-modal-head-bg) !important;
}

.ev-cv-modal-content::before,
.ev-cv-modal-content::after,
.ev-cv-modal-head::before,
.ev-cv-modal-head::after{
  content:none !important;
  display:none !important;
}

/* ============================================================
   CABECERA DEL MODAL
============================================================ */

.ev-cv-modal-head{
  flex:0 0 auto;
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:16px;
  min-height:106px;
  padding:17px 20px 16px;
  margin:0;
  border:0 !important;
  border-radius:var(--ev-cv-modal-radius) var(--ev-cv-modal-radius) 0 0;
  background:var(--ev-cv-modal-head-bg);
  color:#fff;
}

.ev-cv-modal-heading{
  min-width:0;
  display:flex;
  align-items:flex-start;
  gap:13px;
}

.ev-cv-modal-heading-icon{
  flex:0 0 48px;
  width:48px;
  height:48px;
  display:grid;
  place-items:center;
  border-radius:15px;
  color:#fff;
  background:rgba(255,255,255,.10);
  border:1px solid rgba(255,255,255,.24);
  font-size:1.18rem;
}

.ev-cv-modal-heading-copy{
  min-width:0;
}

.ev-cv-modal-eyebrow{
  display:block;
  margin-bottom:3px;
  color:rgba(255,255,255,.80);
  font-size:.69rem;
  line-height:1;
  font-weight:900;
  text-transform:uppercase;
  letter-spacing:.075em;
}

.ev-cv-modal-head h2{
  margin:0 0 8px;
  color:#fff;
  font-size:1.2rem;
  line-height:1.25;
  font-weight:900;
  letter-spacing:-.022em;
  overflow-wrap:anywhere;
}

.ev-cv-modal-head-meta{
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:8px;
}

.ev-cv-modal-type{
  display:inline-flex;
  align-items:center;
  min-height:24px;
  padding:4px 10px;
  border-radius:999px;
  color:#fff;
  background:rgba(255,255,255,.13);
  border:1px solid rgba(255,255,255,.25);
  font-size:.68rem;
  line-height:1;
  font-weight:900;
  text-transform:uppercase;
  letter-spacing:.045em;
}

.ev-cv-modal-type--noticia{
  background:rgba(219,234,254,.17);
}

.ev-cv-modal-type--evento{
  background:rgba(255,247,237,.17);
}

.ev-cv-modal-community{
  display:inline-flex;
  align-items:center;
  gap:6px;
  color:rgba(255,255,255,.91);
  font-size:.76rem;
  font-weight:700;
}

.ev-cv-modal-close{
  flex:0 0 40px;
  width:40px;
  height:40px;
  display:grid;
  place-items:center;
  border:0;
  border-radius:13px;
  color:#fff;
  background:rgba(255,255,255,.10);
  font-size:1.13rem;
  opacity:.95;
  transition:background .17s ease, transform .17s ease, opacity .17s ease;
}

.ev-cv-modal-close:hover{
  background:rgba(255,255,255,.18);
  transform:scale(1.03);
  opacity:1;
}

.ev-cv-modal-close:focus-visible{
  outline:3px solid rgba(255,255,255,.32);
  outline-offset:1px;
}

/* ============================================================
   SUPERFICIE BLANCA / SCROLL
============================================================ */

.ev-cv-modal-surface{
  flex:1 1 auto;
  min-height:0;
  display:flex;
  flex-direction:column;
  overflow:hidden;
  background:#fff;
  border-radius:0 0 var(--ev-cv-modal-radius) var(--ev-cv-modal-radius);
}

.ev-cv-modal-body{
  flex:1 1 auto;
  min-height:0;
  overflow-y:auto;
  padding:15px 20px 16px !important;
  background:#fff !important;
  scrollbar-width:thin;
  scrollbar-color:#C9D3CF transparent;
}

.ev-cv-modal-body::-webkit-scrollbar{
  width:6px;
}

.ev-cv-modal-body::-webkit-scrollbar-track{
  background:transparent;
}

.ev-cv-modal-body::-webkit-scrollbar-thumb{
  border-radius:99px;
  background:#C9D3CF;
}

/* ============================================================
   ESTADO / FECHA
============================================================ */

.ev-cv-modal-topline{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:12px;
  margin-bottom:14px;
}

.ev-cv-modal-badges{
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:8px;
}

.ev-cv-modal-priority,
.ev-cv-modal-featured{
  display:inline-flex;
  align-items:center;
  gap:5px;
  min-height:27px;
  padding:5px 11px;
  border-radius:999px;
  font-size:.74rem;
  font-weight:850;
}

.ev-cv-modal-priority--normal{
  color:#475467;
  background:#F3F4F6;
}

.ev-cv-modal-priority--importante{
  color:#C76509;
  background:#FFF7ED;
}

.ev-cv-modal-priority--urgente{
  color:#B42318;
  background:#FEF2F2;
}

.ev-cv-modal-featured{
  color:#C76509;
  background:#FFF3E5;
}

.ev-cv-modal-featured[hidden]{
  display:none !important;
}

.ev-cv-modal-date{
  display:inline-flex;
  align-items:center;
  gap:6px;
  color:#64748B;
  font-size:.76rem;
  font-weight:700;
  white-space:nowrap;
}

.ev-cv-modal-date i{
  color:#94A3B8;
}

/* ============================================================
   PORTADA DEL MODAL - CORRECCIÓN DE RAÍZ
   - Nunca recorta imágenes verticales ni horizontales.
   - El marco conserva altura premium.
   - La imagen se ajusta completa usando contain reforzado.
============================================================ */

.ev-cv-modal-media{
  width:100%;
  height:clamp(274px,42vh,394px) !important;
  min-height:274px !important;
  max-height:394px !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  padding:10px !important;
  margin:0 0 15px !important;
  overflow:hidden !important;
  border-radius:17px;
  border:1px solid #DDECE4;
  background:#F7FCF9;
}

.ev-cv-modal-media[hidden]{
  display:none !important;
}

#modalDetalleComunidadVecino .ev-cv-modal-media .ev-cv-modal-image,
#modalDetalleComunidadVecino img#evCvModalImagen{
  display:block !important;
  position:static !important;
  inset:auto !important;
  width:100% !important;
  height:100% !important;
  min-width:0 !important;
  min-height:0 !important;
  max-width:100% !important;
  max-height:100% !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  border-radius:12px !important;
  object-fit:contain !important;
  object-position:center center !important;
  transform:none !important;
  background:transparent !important;
}

#modalDetalleComunidadVecino .ev-cv-modal-media .ev-cv-modal-image[hidden],
#modalDetalleComunidadVecino img#evCvModalImagen[hidden]{
  display:none !important;
}

/* ============================================================
   BLOQUES DE INFORMACIÓN DEL MODAL
============================================================ */

.ev-cv-modal-block{
  margin-top:13px;
  padding:13px 14px 14px;
  border:1px solid #E3E9EF;
  border-radius:16px;
  background:#fff;
}

.ev-cv-modal-block--summary{
  border-color:rgba(22,163,74,.18);
  background:#F7FCF9;
}

.ev-cv-modal-block-label{
  display:block;
  margin-bottom:7px;
  color:#94A3B8;
  font-size:.67rem;
  line-height:1;
  font-weight:900;
  text-transform:uppercase;
  letter-spacing:.065em;
}

.ev-cv-modal-summary{
  margin:0;
  color:var(--ev-cv-green-dark);
  font-size:.94rem;
  font-weight:750;
  line-height:1.6;
  overflow-wrap:anywhere;
}

.ev-cv-modal-event{
  align-items:center;
  gap:9px;
  margin-top:13px;
  padding:11px 13px;
  border-radius:13px;
  border:1px solid rgba(22,163,74,.14);
  color:var(--ev-cv-green-dark);
  background:#EEF9F3;
  font-weight:700;
  font-size:.86rem;
}

.ev-cv-modal-event:not([hidden]){
  display:flex;
}

.ev-cv-modal-text{
  color:#475467;
  font-size:.9rem;
  line-height:1.73;
  white-space:pre-wrap;
  overflow-wrap:anywhere;
}

/* ============================================================
   FOOTER DEL MODAL
============================================================ */

.ev-cv-modal-footer{
  flex:0 0 auto;
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:18px;
  padding:13px 20px 15px;
  border-top:1px solid #ECF0F3;
  background:#fff;
}

.ev-cv-modal-footer span{
  display:flex;
  align-items:center;
  gap:7px;
  color:#667085;
  font-size:.79rem;
  line-height:1.45;
  font-weight:650;
}

.ev-cv-modal-footer span i{
  color:var(--ev-cv-green);
}

.ev-cv-modal-footer button{
  min-width:86px;
  padding:10px 18px;
  border:1px solid rgba(15,89,47,.20);
  border-radius:12px;
  color:var(--ev-cv-green-dark);
  background:#fff;
  font-weight:800;
  transition:background .16s ease, border-color .16s ease, transform .16s ease;
}

.ev-cv-modal-footer button:hover{
  background:#F0FDF4;
  border-color:rgba(22,163,74,.32);
  transform:translateY(-1px);
}

/* ============================================================
   TABLET
============================================================ */

@media (max-width:1100px){
  .ev-cv-grid{
    grid-template-columns:repeat(2,minmax(0,1fr));
  }

  .ev-cv-toolbar{
    align-items:stretch;
    flex-direction:column;
  }

  .ev-cv-search{
    flex-basis:auto;
    width:100%;
  }

  .ev-cv-feature-card{
    grid-template-columns:minmax(210px,41%) 1fr;
  }
}

/* ============================================================
   MÓVIL
============================================================ */

@media (max-width:767.98px){
  .ev-cv-shell{
    padding:14px 12px 25px;
  }

  .ev-cv-hero{
    padding:20px 17px;
    flex-direction:column;
    align-items:stretch;
    gap:18px;
    border-radius:22px;
  }

  .ev-cv-hero h1{
    font-size:1.64rem;
    line-height:1.16;
  }

  .ev-cv-community-pill{
    min-width:0;
    max-width:none;
    padding:12px 13px;
  }

  .ev-cv-toolbar{
    padding:10px;
    gap:12px;
    border-radius:18px;
  }

  .ev-cv-tabs{
    width:100%;
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:7px;
    overflow:visible;
  }

  .ev-cv-tabs button{
    width:100%;
    justify-content:center;
    padding:10px 7px;
    font-size:.86rem;
  }

  .ev-cv-search{
    height:45px;
  }

  .ev-cv-feature,
  .ev-cv-content{
    padding:12px;
    border-radius:19px;
  }

  .ev-cv-section-heading{
    margin-bottom:12px;
  }

  .ev-cv-grid{
    grid-template-columns:1fr;
  }

  .ev-cv-feature-card{
    grid-template-columns:1fr;
    gap:12px;
    padding:9px;
    border-radius:16px;
  }

  .ev-cv-feature-img{
    height:286px;
    padding:8px;
  }

  .ev-cv-feature-img img{
    min-height:0;
    max-height:none;
  }

  .ev-cv-feature-body{
    padding:3px 6px 9px;
  }

  .ev-cv-feature-body h3{
    font-size:1.03rem;
  }

  .ev-cv-modal .modal-dialog{
    margin:10px;
  }

  .ev-cv-modal-content{
    --ev-cv-modal-radius:20px;
    max-height:calc(100svh - 20px);
  }

  .ev-cv-modal-head{
    min-height:0;
    padding:14px 13px 13px;
    gap:9px;
  }

  .ev-cv-modal-heading{
    gap:9px;
  }

  .ev-cv-modal-heading-icon{
    flex-basis:43px;
    width:43px;
    height:43px;
    border-radius:13px;
    font-size:1rem;
  }

  .ev-cv-modal-eyebrow{
    font-size:.63rem;
  }

  .ev-cv-modal-head h2{
    font-size:1rem;
    line-height:1.22;
    margin-bottom:7px;
  }

  .ev-cv-modal-type{
    min-height:22px;
    padding:4px 9px;
    font-size:.64rem;
  }

  .ev-cv-modal-community{
    font-size:.69rem;
  }

  .ev-cv-modal-close{
    flex-basis:36px;
    width:36px;
    height:36px;
    border-radius:11px;
  }

  .ev-cv-modal-body{
    padding:13px 10px 12px !important;
  }

  .ev-cv-modal-topline{
    align-items:center;
    margin-bottom:12px;
  }

  .ev-cv-modal-priority,
  .ev-cv-modal-featured{
    min-height:26px;
    padding:5px 10px;
    font-size:.72rem;
  }

  .ev-cv-modal-date{
    font-size:.71rem;
  }

  /*
     En móvil se conserva una altura suficiente para que la portada
     vertical se vea completa, centrada y sin ampliar de forma agresiva.
  */
  .ev-cv-modal-media{
    height:clamp(254px,39svh,336px) !important;
    min-height:254px !important;
    max-height:336px !important;
    padding:7px !important;
    margin-bottom:12px !important;
    border-radius:15px;
  }

  #modalDetalleComunidadVecino .ev-cv-modal-media .ev-cv-modal-image,
  #modalDetalleComunidadVecino img#evCvModalImagen{
    border-radius:10px !important;
    object-fit:contain !important;
    object-position:center center !important;
  }

  .ev-cv-modal-block{
    margin-top:11px;
    padding:12px;
    border-radius:14px;
  }

  .ev-cv-modal-summary{
    font-size:.9rem;
    line-height:1.52;
  }

  .ev-cv-modal-text{
    font-size:.86rem;
    line-height:1.62;
  }

  .ev-cv-modal-footer{
    flex-direction:column;
    align-items:stretch;
    gap:12px;
    padding:11px 10px 12px;
  }

  .ev-cv-modal-footer span{
    font-size:.74rem;
    padding:0 2px;
  }

  .ev-cv-modal-footer button{
    width:100%;
    min-height:44px;
  }
}

/* ============================================================
   MÓVIL MUY ANGOSTO
============================================================ */

@media (max-width:389.98px){
  .ev-cv-shell{
    padding-left:10px;
    padding-right:10px;
  }

  .ev-cv-hero{
    padding:18px 14px;
  }

  .ev-cv-hero h1{
    font-size:1.5rem;
  }

  .ev-cv-tabs button{
    font-size:.8rem;
    gap:5px;
  }

  .ev-cv-modal .modal-dialog{
    margin:7px;
  }

  .ev-cv-modal-body{
    padding:11px 8px !important;
  }

  .ev-cv-modal-media{
    height:clamp(238px,37svh,305px) !important;
    min-height:238px !important;
    max-height:305px !important;
    padding:6px !important;
  }
}
</style>