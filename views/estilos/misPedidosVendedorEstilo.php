<style>
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#16A34A;
  --ev-verde-claro:#BBF7D0;
  --ev-verde-suave:#ECFDF3;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;
  --ev-naranja-suave:#FFF7ED;

  --ev-rojo:#DC2626;
  --ev-rojo-oscuro:#991B1B;
  --ev-rojo-suave:#FEF2F2;
  --ev-rojo-borde:#FECACA;

  --ev-azul:#2563EB;
  --ev-azul-oscuro:#1D4ED8;
  --ev-azul-suave:#EFF6FF;
  --ev-azul-borde:#BFDBFE;

  --ev-texto:#111827;
  --ev-texto-suave:#6B7280;

  --ev-gris-025:#FCFDFC;
  --ev-gris-050:#F9FAFB;
  --ev-gris-100:#F3F4F6;
  --ev-gris-150:#EEF2F7;
  --ev-gris-200:#E5E7EB;
  --ev-gris-250:#DDE5EE;
  --ev-gris-300:#D1D5DB;
  --ev-gris-500:#6B7280;
  --ev-gris-600:#4B5563;
  --ev-gris-700:#374151;

  --ev-sombra-card:0 16px 38px rgba(15,23,42,.07);
  --ev-sombra-card-hover:0 22px 46px rgba(15,23,42,.10);
  --ev-sombra-soft:0 8px 22px rgba(15,23,42,.05);
  --ev-sombra-chip:0 8px 16px rgba(15,23,42,.05);
  --ev-sombra-hero:0 22px 56px rgba(15,23,42,.08);

  --ev-radius-card:20px;
  --ev-radius-soft:16px;
  --ev-radius-order:24px;
  --ev-radius-xl:28px;
}

.ev-mpv-page{
  max-width:100%;
  margin:0 auto;
  padding:14px 14px 26px;
  color:var(--ev-texto);
}

.ev-mpv-card-shell,
.ev-mpv-panel{
  border-radius:var(--ev-radius-card);
  background:#fff;
  border:1px solid rgba(148,163,184,.18);
  box-shadow:var(--ev-sombra-card);
  overflow:hidden;
}

.ev-mpv-hero{
  background:
    radial-gradient(circle at 86% 22%, rgba(22,163,74,.11), transparent 34%),
    radial-gradient(circle at 14% 82%, rgba(234,124,18,.10), transparent 32%),
    linear-gradient(135deg, #fffdfa 0%, #f8fcf9 40%, #f4fbf6 100%);
  box-shadow:var(--ev-sombra-hero);
}

.ev-mpv-hero-body{
  padding:20px 20px 16px;
}

.ev-mpv-hero-top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:18px;
  flex-wrap:wrap;
}

.ev-mpv-title-wrap{
  display:flex;
  align-items:flex-start;
  gap:14px;
  min-width:0;
  flex:1 1 520px;
}

.ev-mpv-title-icon{
  width:52px;
  height:52px;
  border-radius:18px;
  background:linear-gradient(135deg, rgba(187,247,208,.92), rgba(255,255,255,.96));
  border:1px solid rgba(22,163,74,.22);
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  box-shadow:0 12px 24px rgba(15,23,42,.08);
  font-size:1.18rem;
  flex-shrink:0;
}

.ev-mpv-kicker{
  font-size:.77rem;
  font-weight:900;
  letter-spacing:.14em;
  text-transform:uppercase;
  color:var(--ev-naranja);
  margin:1px 0 4px;
}

.ev-mpv-title{
  color:var(--ev-verde-oscuro);
  font-weight:900;
  letter-spacing:-.03em;
  font-size:2.15rem;
  margin:0 0 4px;
  line-height:1.02;
}

.ev-mpv-subtitle,
.ev-mpv-block-subtitle{
  color:var(--ev-gris-500);
  font-size:.95rem;
  margin:0;
  line-height:1.45;
}

.ev-mpv-summary-grid{
  display:grid;
  grid-template-columns:repeat(3, minmax(130px, 1fr));
  gap:12px;
  width:min(100%, 448px);
  flex:0 1 448px;
}

.ev-mpv-summary-card{
  position:relative;
  background:rgba(255,255,255,.92);
  border:1px solid rgba(148,163,184,.16);
  border-radius:18px;
  padding:14px 16px;
  box-shadow:var(--ev-sombra-soft);
  backdrop-filter:blur(4px);
}

.ev-mpv-summary-card::after{
  content:"";
  position:absolute;
  inset:auto 0 0 0;
  height:3px;
  background:linear-gradient(90deg, rgba(22,163,74,.85), rgba(234,124,18,.75));
  opacity:.18;
}

.ev-mpv-summary-label{
  display:block;
  color:var(--ev-gris-500);
  font-size:.82rem;
  margin-bottom:4px;
  font-weight:800;
}

.ev-mpv-summary-card strong{
  color:var(--ev-verde-oscuro);
  font-size:1.42rem;
  font-weight:900;
  letter-spacing:-.02em;
}

.ev-mpv-panel-head{
  padding:18px 18px 16px;
  border-bottom:1px solid var(--ev-gris-200);
  background:linear-gradient(180deg, rgba(255,255,255,.98) 0%, rgba(249,250,251,.98) 100%);
}

.ev-mpv-panel-head-row{
  display:flex;
  flex-direction:column;
  gap:14px;
}

.ev-mpv-block-title{
  color:var(--ev-verde-oscuro);
  font-size:1.08rem;
  font-weight:900;
  margin:0 0 4px;
  letter-spacing:-.01em;
}

.ev-mpv-actions-top{
  display:grid;
  grid-template-columns:minmax(0, 1fr) auto;
  align-items:center;
  gap:12px 14px;
}

.ev-mpv-tab-groups{
  display:flex;
  gap:12px;
  flex-wrap:wrap;
  align-items:center;
  min-width:0;
}

.ev-mpv-tab-group{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
  padding:8px 10px;
  border-radius:18px;
  background:linear-gradient(180deg,#ffffff 0%, #fbfdfb 100%);
  border:1px solid rgba(148,163,184,.16);
  box-shadow:var(--ev-sombra-soft);
  min-width:0;
}

.ev-mpv-tab-group-label{
  font-size:.78rem;
  font-weight:900;
  color:var(--ev-gris-500);
  margin-right:2px;
  white-space:nowrap;
}

.ev-mpv-tabs{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
}

.ev-mpv-tab{
  border-radius:999px;
  border:1px solid rgba(22,163,74,.18);
  background:rgba(255,255,255,.95);
  color:var(--ev-verde-oscuro);
  font-weight:850;
  box-shadow:0 8px 16px rgba(15,23,42,.05);
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease, border-color .16s ease;
  padding:.68rem .98rem;
  font-size:.89rem;
}

.ev-mpv-tab:hover{
  background:linear-gradient(90deg, rgba(187,247,208,.58), rgba(187,247,208,.18));
  border-color:rgba(22,163,74,.30);
  transform:translateY(-1px);
  box-shadow:0 14px 24px rgba(15,23,42,.08);
  filter:brightness(1.02);
}

.ev-mpv-tab.active{
  background:linear-gradient(90deg, rgba(187,247,208,.68), rgba(255,255,255,.86));
  border-color:rgba(22,163,74,.34);
  box-shadow:0 14px 24px rgba(15,23,42,.08);
}

.ev-mpv-tab-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:26px;
  padding:1px 8px;
  margin-left:8px;
  border-radius:999px;
  background:#fff;
  border:1px solid rgba(22,163,74,.16);
  font-weight:900;
  font-size:.77rem;
}

.ev-mpv-btn-refresh{
  border-radius:999px;
  border:1px solid rgba(22,163,74,.18);
  background:rgba(255,255,255,.96);
  color:var(--ev-verde-oscuro);
  font-weight:850;
  box-shadow:0 8px 18px rgba(15,23,42,.05);
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease, border-color .16s ease;
  padding:.78rem 1.12rem;
  white-space:nowrap;
  justify-self:end;
  align-self:start;
}

.ev-mpv-btn-refresh:hover{
  background:linear-gradient(90deg, rgba(187,247,208,.58), rgba(187,247,208,.18));
  border-color:rgba(22,163,74,.32);
  transform:translateY(-1px);
  box-shadow:0 14px 24px rgba(15,23,42,.08);
  filter:brightness(1.02);
}

.ev-mpv-panel-body{
  padding:18px;
  background:linear-gradient(180deg, rgba(252,253,252,.98) 0%, rgba(248,250,249,.98) 100%);
}

.ev-mpv-alert,
.ev-mpv-empty{
  border-radius:18px;
  padding:14px 16px;
  text-align:center;
  margin-bottom:16px;
  font-weight:700;
}

.ev-mpv-alert-error{
  background:#FEF2F2;
  color:#991B1B;
  border:1px solid #FECACA;
}

.ev-mpv-empty{
  background:#F9FAFB;
  color:#4B5563;
  border:1px solid #E5E7EB;
}

.ev-mpv-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(420px, 1fr));
  gap:18px;
}

.ev-mpv-order{
  position:relative;
  border:1px solid rgba(148,163,184,.16);
  border-radius:var(--ev-radius-order);
  background:linear-gradient(180deg,#ffffff 0%, #fbfcfb 100%);
  box-shadow:var(--ev-sombra-card);
  overflow:hidden;
  transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}

.ev-mpv-order::before{
  content:"";
  position:absolute;
  inset:0 auto 0 0;
  width:4px;
  background:linear-gradient(180deg, rgba(22,163,74,.72), rgba(234,124,18,.62));
  opacity:.18;
}

.ev-mpv-order:hover{
  transform:translateY(-2px);
  box-shadow:var(--ev-sombra-card-hover);
  border-color:rgba(22,163,74,.18);
}

.ev-mpv-order-top{
  display:grid;
  grid-template-columns:88px 1fr;
  grid-template-areas:
    "media head"
    "data  data";
  gap:12px;
  padding:14px 14px 12px;
  border-bottom:1px solid var(--ev-gris-200);
  align-items:start;
}

.ev-mpv-order-media{
  grid-area:media;
  width:88px;
  height:88px;
  border-radius:18px;
  overflow:hidden;
  background:#F3F4F6;
  box-shadow:0 10px 22px rgba(15,23,42,.10);
  align-self:start;
  flex-shrink:0;
}

.ev-mpv-order-media img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-mpv-order-head{
  grid-area:head;
  min-width:0;
}

.ev-mpv-order-top-data{
  grid-area:data;
}

.ev-mpv-order-head-row{
  display:flex;
  justify-content:space-between;
  gap:10px;
  align-items:flex-start;
  margin-bottom:8px;
  flex-wrap:wrap;
}

.ev-mpv-order-head-main{
  min-width:0;
  flex:1 1 180px;
}

.ev-mpv-order-title{
  color:var(--ev-verde-oscuro);
  font-weight:900;
  font-size:1.03rem;
  line-height:1.16;
  margin-bottom:3px;
  letter-spacing:-.01em;
  word-break:break-word;
}

.ev-mpv-order-meta{
  color:var(--ev-gris-500);
  font-size:.82rem;
  line-height:1.35;
  word-break:break-word;
}

.ev-mpv-badge{
  border-radius:999px;
  padding:7px 12px;
  font-size:.75rem;
  font-weight:900;
  white-space:nowrap;
  box-shadow:var(--ev-sombra-chip);
  border:1px solid transparent;
  max-width:100%;
}

.ev-mpv-badge-pendiente{
  background:#FFF5D6;
  color:#9A5B03;
  border-color:#F5D18D;
}

.ev-mpv-badge-proceso{
  background:#EAFBF0;
  color:#166534;
  border-color:#BFE7CB;
}

.ev-mpv-badge-final{
  background:#EAF4FF;
  color:#0B5E93;
  border-color:#B9DBF5;
}

.ev-mpv-badge-negative{
  background:var(--ev-rojo-suave);
  color:var(--ev-rojo-oscuro);
  border-color:var(--ev-rojo-borde);
}

.ev-mpv-badge-info{
  background:var(--ev-azul-suave);
  color:var(--ev-azul-oscuro);
  border-color:var(--ev-azul-borde);
}

.ev-mpv-order-quick{
  display:flex;
  gap:7px;
  flex-wrap:wrap;
  margin-bottom:6px;
}

.ev-mpv-pill{
  display:inline-flex;
  align-items:center;
  gap:7px;
  padding:6px 10px;
  border-radius:999px;
  background:linear-gradient(90deg, rgba(236,253,243,.94), rgba(255,255,255,.96));
  border:1px solid rgba(22,163,74,.16);
  color:#355B4A;
  font-size:.75rem;
  font-weight:850;
  box-shadow:0 6px 14px rgba(15,23,42,.04);
}

.ev-mpv-pill i{
  color:var(--ev-verde);
}

.ev-mpv-order-data{
  display:grid;
  grid-template-columns:minmax(0,1.1fr) minmax(120px,.9fr);
  grid-template-areas:
    "date total"
    "buyer buyer";
  gap:9px;
  align-items:stretch;
}

.ev-mpv-data-box{
  border:1px solid rgba(229,231,235,.92);
  background:#fff;
  border-radius:16px;
  padding:10px 12px;
  min-height:68px;
}

.ev-mpv-data-box span{
  display:block;
  font-size:.72rem;
  color:var(--ev-gris-500);
  margin-bottom:4px;
  font-weight:800;
}

.ev-mpv-data-box strong{
  display:block;
  color:var(--ev-texto);
  font-size:.92rem;
  font-weight:800;
  line-height:1.24;
  word-break:break-word;
}

.ev-mpv-data-box-date{
  grid-area:date;
}

.ev-mpv-data-box-buyer{
  grid-area:buyer;
}

.ev-mpv-data-box-buyer strong{
  font-size:.95rem;
  line-height:1.2;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
}

.ev-mpv-data-box-total{
  grid-area:total;
  background:linear-gradient(180deg, rgba(236,253,245,.80) 0%, rgba(255,255,255,.96) 100%);
  border:1px solid rgba(22,163,74,.16);
  display:flex;
  flex-direction:column;
  justify-content:center;
}

.ev-mpv-data-box-total strong{
  color:var(--ev-verde-oscuro);
  font-size:1.14rem;
  font-weight:900;
  line-height:1.08;
  letter-spacing:-.01em;
}

.ev-mpv-order-body{
  padding:12px 14px 14px;
}

.ev-mpv-section-title{
  display:flex;
  align-items:center;
  gap:8px;
  font-size:.81rem;
  font-weight:900;
  color:var(--ev-verde-oscuro);
  margin-bottom:10px;
}

.ev-mpv-section-title i{
  color:var(--ev-verde);
}

.ev-mpv-stepper{
  display:flex;
  flex-wrap:wrap;
  gap:7px;
  margin-bottom:12px;
}

.ev-mpv-stepper-final{
  margin-bottom:12px;
}

.ev-mpv-step{
  display:inline-flex;
  align-items:center;
  gap:7px;
  border-radius:999px;
  padding:6px 10px;
  font-size:.74rem;
  font-weight:850;
  border:1px solid #E5E7EB;
  background:#fff;
  color:#64748B;
}

.ev-mpv-step-dot{
  width:7px;
  height:7px;
  border-radius:50%;
  background:#CBD5E1;
  flex-shrink:0;
}

.ev-mpv-step.is-done{
  background:#ECFDF3;
  border-color:#BBF7D0;
  color:#166534;
}

.ev-mpv-step.is-done .ev-mpv-step-dot{
  background:#16A34A;
}

.ev-mpv-step.is-current{
  background:#FFF7ED;
  border-color:#FCD9BD;
  color:#C46B05;
  box-shadow:0 6px 14px rgba(234,124,18,.10);
}

.ev-mpv-step.is-current .ev-mpv-step-dot{
  background:#EA7C12;
}

.ev-mpv-step.is-final{
  background:#F8FAFC;
  color:#334155;
}

.ev-mpv-step.is-negative{
  background:var(--ev-rojo-suave);
  border-color:var(--ev-rojo-borde);
  color:var(--ev-rojo-oscuro);
}

.ev-mpv-step.is-negative .ev-mpv-step-dot{
  background:var(--ev-rojo);
}

.ev-mpv-info-card{
  border:1px solid rgba(229,231,235,.94);
  border-radius:18px;
  background:#fff;
  padding:11px 12px;
}

.ev-mpv-line{
  display:flex;
  justify-content:space-between;
  gap:12px;
  margin-bottom:8px;
  font-size:.88rem;
}

.ev-mpv-line:last-child{
  margin-bottom:0;
}

.ev-mpv-line-label{
  color:var(--ev-gris-500);
  font-weight:800;
}

.ev-mpv-line-value{
  color:var(--ev-texto);
  font-weight:800;
  text-align:right;
  max-width:60%;
  word-break:break-word;
}

.ev-mpv-note{
  margin-top:10px;
  border-radius:15px;
  background:linear-gradient(180deg, rgba(249,250,251,.98) 0%, rgba(243,244,246,.95) 100%);
  border:1px solid rgba(229,231,235,.94);
  padding:9px 11px;
}

.ev-mpv-note-label{
  display:block;
  color:var(--ev-verde-oscuro);
  font-size:.72rem;
  font-weight:900;
  margin-bottom:4px;
}

.ev-mpv-note-text{
  color:#475569;
  font-size:.84rem;
  line-height:1.42;
  word-break:break-word;
}

.ev-mpv-state-box{
  margin-top:11px;
  border-radius:18px;
  padding:11px 12px;
  border:1px solid transparent;
}

.ev-mpv-state-box-pending{
  background:#FFF9EC;
  border-color:#FCD9BD;
}

.ev-mpv-state-box-process{
  background:#F6FBF8;
  border-color:#D7F0E0;
}

.ev-mpv-state-box-info{
  background:#F0F9FF;
  border-color:#BAE6FD;
}

.ev-mpv-state-box-final{
  background:#F8FAFC;
  border-color:#E2E8F0;
}

.ev-mpv-state-box-negative{
  background:var(--ev-rojo-suave);
  border-color:var(--ev-rojo-borde);
}

.ev-mpv-state-title{
  color:var(--ev-verde-oscuro);
  font-weight:900;
  font-size:.89rem;
  margin-bottom:3px;
}

.ev-mpv-state-box-negative .ev-mpv-state-title{
  color:var(--ev-rojo-oscuro);
}

.ev-mpv-state-text{
  color:#475569;
  font-size:.84rem;
  line-height:1.42;
}

.ev-mpv-time-pill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  margin-top:9px;
  border-radius:999px;
  padding:6px 11px;
  background:#fff;
  border:1px solid #FCD9BD;
  color:#B45309;
  font-weight:850;
  font-size:.76rem;
}

.ev-mpv-actions{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin-top:13px;
}

.ev-mpv-btn-accept,
.ev-mpv-btn-action,
.ev-mpv-btn-outline,
.ev-mpv-btn-success,
.ev-mpv-btn-danger-soft{
  border-radius:14px;
  padding:.74rem .98rem;
  font-weight:850;
  font-size:.89rem;
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease;
}

.ev-mpv-btn-accept{
  background:linear-gradient(135deg, var(--ev-verde), #22C55E);
  border:none;
  color:#fff;
  box-shadow:0 12px 24px rgba(22,163,74,.20);
}

.ev-mpv-btn-action{
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border:none;
  color:#fff;
  box-shadow:0 12px 24px rgba(234,124,18,.20);
}

.ev-mpv-btn-success{
  background:linear-gradient(135deg, #16A34A, #22C55E);
  border:none;
  color:#fff;
  box-shadow:0 12px 24px rgba(22,163,74,.20);
}

.ev-mpv-btn-outline{
  border:1px solid rgba(22,163,74,.18);
  background:rgba(255,255,255,.96);
  color:var(--ev-verde-oscuro);
  box-shadow:0 8px 16px rgba(15,23,42,.05);
}

.ev-mpv-btn-outline:hover{
  background:linear-gradient(90deg, rgba(187,247,208,.55), rgba(187,247,208,.18));
  border-color:rgba(22,163,74,.32);
  transform:translateY(-1px);
  box-shadow:0 14px 24px rgba(15,23,42,.08);
  filter:brightness(1.02);
  color:var(--ev-verde-oscuro);
}

.ev-mpv-btn-danger-soft{
  border:1px solid #FECACA;
  background:#FFF1F2;
  color:#B91C1C;
}

.ev-mpv-btn-accept:hover,
.ev-mpv-btn-action:hover,
.ev-mpv-btn-success:hover,
.ev-mpv-btn-danger-soft:hover{
  transform:translateY(-1px);
  filter:brightness(1.02);
}

.ev-mpv-swal-popup{
  border-radius:30px !important;
  padding:1rem 1.1rem 1.2rem !important;
}

.ev-mpv-modal-detail{
  text-align:left;
}

.ev-mpv-modal-top{
  display:grid;
  grid-template-columns:150px 1fr;
  gap:18px;
  margin-bottom:18px;
  align-items:start;
}

.ev-mpv-modal-media{
  width:150px;
  height:150px;
  border-radius:22px;
  overflow:hidden;
  background:#F3F4F6;
  box-shadow:0 14px 28px rgba(15,23,42,.10);
}

.ev-mpv-modal-media img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-mpv-modal-main{
  min-width:0;
}

.ev-mpv-modal-head{
  display:flex;
  justify-content:space-between;
  gap:12px;
  align-items:flex-start;
  margin-bottom:16px;
  flex-wrap:wrap;
}

.ev-mpv-modal-title{
  color:var(--ev-verde-oscuro);
  font-size:1.24rem;
  font-weight:900;
  line-height:1.18;
  letter-spacing:-.01em;
}

.ev-mpv-modal-subtitle{
  color:var(--ev-gris-500);
  font-size:.90rem;
  margin-top:4px;
  line-height:1.4;
}

.ev-mpv-modal-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0, 1fr));
  gap:10px;
}

.ev-mpv-modal-item{
  border:1px solid #EEF2F7;
  background:linear-gradient(180deg,#FBFDFC 0%, #FFFFFF 100%);
  border-radius:16px;
  padding:12px;
}

.ev-mpv-modal-item span{
  display:block;
  color:var(--ev-gris-500);
  font-size:.78rem;
  margin-bottom:4px;
  font-weight:800;
}

.ev-mpv-modal-item strong{
  color:var(--ev-texto);
  font-weight:850;
}

.ev-mpv-modal-section{
  margin-top:16px;
}

.ev-mpv-modal-stack{
  border:1px solid #EEF2F7;
  border-radius:20px;
  padding:14px;
  background:#fff;
}

.ev-mpv-modal-row{
  display:flex;
  justify-content:space-between;
  gap:12px;
  padding:10px 0;
  border-bottom:1px solid #F1F5F9;
}

.ev-mpv-modal-row:last-child{
  border-bottom:none;
}

.ev-mpv-modal-row span{
  color:var(--ev-gris-500);
  font-weight:700;
}

.ev-mpv-modal-row strong{
  color:var(--ev-texto);
  text-align:right;
  max-width:62%;
  word-break:break-word;
}

.ev-mpv-modal-note-title{
  color:var(--ev-verde-oscuro);
  font-size:.86rem;
  font-weight:900;
  margin-bottom:8px;
}

.ev-mpv-modal-note{
  border-radius:16px;
  background:#F8FAFC;
  border:1px solid #E5E7EB;
  padding:14px;
  color:#475569;
  line-height:1.6;
  word-break:break-word;
}

@media (max-width: 1399.98px){
  .ev-mpv-grid{
    grid-template-columns:repeat(auto-fill, minmax(400px, 1fr));
  }
}

@media (max-width: 1199.98px){
  .ev-mpv-grid{
    grid-template-columns:repeat(auto-fill, minmax(380px, 1fr));
  }

  .ev-mpv-actions-top{
    grid-template-columns:1fr;
    align-items:start;
  }

  .ev-mpv-tab-groups{
    justify-content:flex-start;
  }

  .ev-mpv-btn-refresh{
    justify-self:start;
  }
}

@media (max-width: 991.98px){
  .ev-mpv-grid{
    grid-template-columns:1fr;
  }

  .ev-mpv-summary-grid{
    width:100%;
    flex:1 1 100%;
  }

  .ev-mpv-order-data{
    grid-template-columns:minmax(0,1fr) minmax(115px,.9fr);
  }
}

@media (max-width: 767.98px){
  .ev-mpv-page{
    padding:10px 10px 22px;
  }

  .ev-mpv-hero-body,
  .ev-mpv-panel-head,
  .ev-mpv-panel-body{
    padding-left:14px;
    padding-right:14px;
  }

  .ev-mpv-title-wrap{
    flex:1 1 100%;
  }

  .ev-mpv-summary-grid{
    grid-template-columns:1fr;
  }

  .ev-mpv-title{
    font-size:1.72rem;
  }

  .ev-mpv-actions-top{
    grid-template-columns:1fr;
    justify-items:stretch;
  }

  .ev-mpv-tab-groups{
    width:100%;
    justify-content:flex-start;
  }

  .ev-mpv-btn-refresh{
    width:100%;
    justify-self:stretch;
  }

  .ev-mpv-order-top{
    grid-template-columns:84px 1fr;
    grid-template-areas:
      "media head"
      "data data";
    gap:11px;
  }

  .ev-mpv-order-media{
    width:84px;
    height:84px;
  }

  .ev-mpv-order-head-row{
    flex-direction:column;
    align-items:flex-start;
  }

  .ev-mpv-order-data{
    grid-template-columns:minmax(0,1fr) minmax(98px,.85fr);
  }

  .ev-mpv-line{
    flex-direction:column;
    gap:4px;
  }

  .ev-mpv-line-value{
    text-align:left;
    max-width:100%;
  }

  .ev-mpv-actions{
    flex-direction:column;
  }

  .ev-mpv-modal-top{
    grid-template-columns:1fr;
  }

  .ev-mpv-modal-media{
    width:100%;
    height:220px;
  }

  .ev-mpv-modal-head{
    flex-direction:column;
    align-items:flex-start;
  }

  .ev-mpv-modal-grid{
    grid-template-columns:1fr;
  }

  .ev-mpv-modal-row{
    flex-direction:column;
    gap:4px;
  }

  .ev-mpv-modal-row strong{
    text-align:left;
    max-width:100%;
  }
}

@media (max-width: 575.98px){
  .ev-mpv-order-top{
    grid-template-columns:1fr;
    grid-template-areas:
      "media"
      "head"
      "data";
    gap:12px;
  }

  .ev-mpv-order-media{
    width:100%;
    height:190px;
  }

  .ev-mpv-order-head-row{
    flex-direction:column;
    align-items:flex-start;
    gap:8px;
  }

  .ev-mpv-order-data{
    grid-template-columns:1fr;
    grid-template-areas:
      "date"
      "total"
      "buyer";
  }

  .ev-mpv-tab{
    width:100%;
    justify-content:space-between;
  }
}


/* ==========================================================
   EV QA 2026-08 — MIS PEDIDOS VENDEDOR RESPONSIVE PREMIUM
   - Cabecera compacta sin espacios muertos.
   - Progreso resumido en cards y flujo completo en detalle.
========================================================== */
.ev-mpv-order-head,
.ev-mpv-order-head-row,
.ev-mpv-order-quick{
  min-height:0 !important;
  height:auto !important;
}

.ev-mpv-order-head{
  align-self:start;
}

.ev-mpv-order-head-row{
  margin-bottom:7px;
}

.ev-mpv-order-quick{
  margin:0;
}

.ev-mpv-progress-compact{
  padding:11px 12px 10px;
  margin:0 0 12px;
  border:1px solid rgba(22,163,74,.14);
  border-radius:16px;
  background:linear-gradient(180deg,#FFFFFF 0%,#F8FCF9 100%);
  box-shadow:0 8px 18px rgba(15,23,42,.035);
}

.ev-mpv-progress-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  margin-bottom:9px;
}

.ev-mpv-progress-caption{
  color:#6B7280;
  font-size:.72rem;
  font-weight:750;
  white-space:nowrap;
}

.ev-mpv-progress-current{
  min-width:0;
  display:inline-flex;
  align-items:center;
  gap:7px;
  color:#0F592F;
  font-size:.79rem;
  font-weight:800;
  line-height:1.25;
}

.ev-mpv-progress-dot{
  width:8px;
  height:8px;
  border-radius:50%;
  background:#EA7C12;
  box-shadow:0 0 0 4px rgba(234,124,18,.10);
  flex:0 0 auto;
}

.ev-mpv-progress-track{
  height:6px;
  overflow:hidden;
  border-radius:999px;
  background:#E8EEE9;
}

.ev-mpv-progress-track > span{
  display:block;
  height:100%;
  min-width:8px;
  border-radius:inherit;
  background:linear-gradient(90deg,#0E7A43 0%,#16A34A 72%,#EA7C12 100%);
}

.ev-mpv-progress-ends{
  display:flex;
  justify-content:space-between;
  gap:12px;
  margin-top:6px;
  color:#94A3B8;
  font-size:.66rem;
  font-weight:700;
}

.ev-mpv-progress-compact.is-special,
.ev-mpv-progress-compact.is-negative{
  display:flex;
  align-items:center;
  min-height:44px;
}

.ev-mpv-progress-compact.is-negative{
  border-color:#FECACA;
  background:#FEF2F2;
}
.ev-mpv-progress-compact.is-negative .ev-mpv-progress-current{color:#991B1B;}
.ev-mpv-progress-compact.is-negative .ev-mpv-progress-dot{background:#DC2626;box-shadow:0 0 0 4px rgba(220,38,38,.10);}

/* Flujo completo del modal: estados compactos, legibles y sin "nube" de pills. */
.ev-mpv-stepper-detail{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:8px;
  margin:0;
}

.ev-mpv-stepper-detail .ev-mpv-step{
  min-width:0;
  min-height:58px;
  padding:9px 10px;
  border-radius:14px;
  display:flex;
  align-items:center;
  justify-content:flex-start;
  gap:8px;
  background:#F8FAFC;
  border:1px solid #E5E7EB;
  color:#64748B;
  box-shadow:none;
}

.ev-mpv-stepper-detail .ev-mpv-step-dot{
  width:18px;
  height:18px;
  display:grid;
  place-items:center;
  font-size:.72rem;
}

.ev-mpv-stepper-detail .ev-mpv-step.is-done{
  background:#F0FDF4;
  border-color:#BBF7D0;
  color:#166534;
}
.ev-mpv-stepper-detail .ev-mpv-step.is-current{
  background:#FFF7ED;
  border-color:#FED7AA;
  color:#C46B05;
  box-shadow:0 8px 18px rgba(234,124,18,.08);
}
.ev-mpv-stepper-detail .ev-mpv-step-text{
  min-width:0;
  line-height:1.2;
  overflow-wrap:anywhere;
}

/* Valores del detalle: jerarquía ligera, sin negro en negrita. */
.ev-mpv-modal-detail-v2 .ev-mpv-modal-item strong,
.ev-mpv-modal-detail-v2 .ev-mpv-modal-row strong{
  color:#111827 !important;
  font-weight:500 !important;
}

.swal2-confirm.ev-mpv-swal-confirm{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:7px !important;
  line-height:1 !important;
}
.swal2-confirm.ev-mpv-swal-confirm i{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  margin:0 !important;
  line-height:1 !important;
  font-size:1rem;
}
.swal2-confirm.ev-mpv-swal-confirm span{line-height:1.05;}

@media (max-width: 767.98px){
  .ev-mpv-stepper-detail{grid-template-columns:repeat(2,minmax(0,1fr));}
}

@media (max-width: 575.98px){
  .ev-mpv-order-top{
    grid-template-columns:96px minmax(0,1fr) !important;
    grid-template-areas:
      "media head"
      "data data" !important;
    gap:10px !important;
    padding:12px !important;
  }
  .ev-mpv-order-media{
    width:96px !important;
    height:96px !important;
    border-radius:16px;
  }
  .ev-mpv-order-head{
    display:flex;
    flex-direction:column;
    justify-content:flex-start;
    gap:7px;
    align-self:start;
  }
  .ev-mpv-order-head-row{
    margin:0 !important;
    gap:7px !important;
  }
  .ev-mpv-order-title{font-size:.98rem;}
  .ev-mpv-order-meta{font-size:.76rem;}
  .ev-mpv-badge{padding:6px 9px;font-size:.7rem;}
  .ev-mpv-pill{padding:5px 8px;font-size:.7rem;}
  .ev-mpv-order-data{margin-top:1px;}
  .ev-mpv-progress-head{align-items:flex-start;}
  .ev-mpv-progress-current{justify-content:flex-end;text-align:right;}
  .ev-mpv-stepper-detail{grid-template-columns:1fr;}
  .ev-mpv-stepper-detail .ev-mpv-step{min-height:48px;}
}

@media (max-width: 390px){
  .ev-mpv-order-top{grid-template-columns:84px minmax(0,1fr) !important;}
  .ev-mpv-order-media{width:84px !important;height:84px !important;}
  .ev-mpv-order-quick{gap:5px;}
}


.ev-mpv-stepper-detail .ev-mpv-step.is-done .ev-mpv-step-dot{color:#fff;}
.ev-mpv-modal-flow-title{display:flex;align-items:center;gap:7px;}
.ev-mpv-modal-flow-title i{color:#16A34A;line-height:1;}

/* ==========================================================
   EV QA 2026-08-24 — INTERACCIÓN + CABECERA COMPACTA
   - Hover/selección anaranjado EV sin saturar el card.
   - Estado + atributos agrupados, sin espacios muertos.
   - "Ver detalle" secundario en reposo y anaranjado al interactuar.
========================================================== */
.ev-mpv-order{
  transition:
    transform .18s ease,
    box-shadow .18s ease,
    border-color .18s ease,
    background .18s ease;
}

.ev-mpv-order:hover,
.ev-mpv-order:focus-within{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.52);
  background:linear-gradient(180deg,#FFFFFF 0%,#FFFBF6 100%);
  box-shadow:
    0 22px 46px rgba(234,124,18,.13),
    0 10px 24px rgba(15,23,42,.07);
}

.ev-mpv-order:hover::before,
.ev-mpv-order:focus-within::before{
  background:linear-gradient(180deg,#EA7C12 0%,#F59E0B 100%);
  opacity:.95;
}

.ev-mpv-order:active{
  transform:translateY(0) scale(.996);
}

.ev-mpv-order-head{
  display:flex !important;
  flex-direction:column !important;
  justify-content:flex-start !important;
  align-items:stretch !important;
  align-self:start !important;
  gap:8px !important;
  min-width:0 !important;
  min-height:0 !important;
  height:auto !important;
}

.ev-mpv-order-head-main{
  flex:0 0 auto !important;
  min-width:0;
  margin:0;
}

.ev-mpv-order-title,
.ev-mpv-order-meta{
  margin-top:0;
}

.ev-mpv-order-tags{
  display:flex;
  align-items:center;
  align-content:flex-start;
  justify-content:flex-start;
  flex-wrap:wrap;
  gap:6px;
  margin:0;
  padding:0;
  min-height:0;
}

.ev-mpv-order-tags > .ev-mpv-badge,
.ev-mpv-order-tags > .ev-mpv-pill{
  min-height:29px;
  margin:0;
  flex:0 0 auto;
}

.ev-mpv-order-tags > .ev-mpv-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:5px 9px;
  font-size:.70rem;
  line-height:1.1;
}

.ev-mpv-order-tags > .ev-mpv-pill{
  padding:5px 8px;
  font-size:.70rem;
  line-height:1.1;
}

.ev-mpv-btn-outline[data-action="detalle"]{
  border:1px solid rgba(22,163,74,.24);
  background:linear-gradient(180deg,#FFFFFF 0%,#F7FCF9 100%);
  color:var(--ev-verde-oscuro);
  box-shadow:0 8px 18px rgba(15,23,42,.06);
}

.ev-mpv-btn-outline[data-action="detalle"]:hover,
.ev-mpv-btn-outline[data-action="detalle"]:focus-visible{
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  border-color:var(--ev-naranja);
  color:#fff;
  box-shadow:0 15px 30px rgba(234,124,18,.28);
  transform:translateY(-1px);
  filter:none;
}

.ev-mpv-btn-outline[data-action="detalle"]:active{
  background:linear-gradient(135deg,var(--ev-naranja-oscuro),var(--ev-naranja));
  border-color:var(--ev-naranja-oscuro);
  color:#fff;
  transform:translateY(0) scale(.985);
  box-shadow:0 9px 20px rgba(234,124,18,.22);
}

@media (hover:none){
  .ev-mpv-order:hover{
    transform:none;
  }

  .ev-mpv-order:active,
  .ev-mpv-order:focus-within{
    border-color:rgba(234,124,18,.52);
    background:linear-gradient(180deg,#FFFFFF 0%,#FFFBF6 100%);
    box-shadow:0 14px 28px rgba(234,124,18,.12);
  }
}

@media (max-width:575.98px){
  .ev-mpv-order-head{
    gap:7px !important;
  }

  .ev-mpv-order-tags{
    gap:5px;
  }

  .ev-mpv-order-tags > .ev-mpv-badge,
  .ev-mpv-order-tags > .ev-mpv-pill{
    min-height:27px;
  }

  .ev-mpv-order-title{
    font-size:.96rem;
    line-height:1.15;
  }

  .ev-mpv-order-meta{
    font-size:.74rem;
    line-height:1.30;
  }
}

@media (max-width:390px){
  .ev-mpv-order-tags > .ev-mpv-badge,
  .ev-mpv-order-tags > .ev-mpv-pill{
    padding:5px 7px;
    font-size:.67rem;
  }
}


/* EV — acción secundaria de cancelación del vendedor */
.swal2-deny.ev-mpv-swal-deny-cancel{
  display:inline-flex !important;
  align-items:center;
  justify-content:center;
  gap:7px;
  min-width:154px;
  border-radius:14px !important;
  padding:.78rem 1rem !important;
  border:1px solid #FCA5A5 !important;
  background:linear-gradient(180deg,#FFF7F7 0%,#FEF2F2 100%) !important;
  color:#B91C1C !important;
  font-family:inherit !important;
  font-weight:850 !important;
  box-shadow:0 9px 20px rgba(185,28,28,.08) !important;
  transition:transform .16s ease,box-shadow .16s ease,background .16s ease !important;
}

.swal2-deny.ev-mpv-swal-deny-cancel:hover,
.swal2-deny.ev-mpv-swal-deny-cancel:focus-visible{
  background:#FEE2E2 !important;
  transform:translateY(-1px) !important;
  box-shadow:0 13px 24px rgba(185,28,28,.13) !important;
  outline:none !important;
}

@media(max-width:575.98px){
  .swal2-deny.ev-mpv-swal-deny-cancel{
    width:100% !important;
    min-width:0 !important;
  }
}


/* ==========================================================
   EV — DESTINO DE NOTIFICACIÓN
   Resalta temporalmente el pedido exacto abierto desde una notificación.
========================================================== */
.ev-mpv-order.is-notification-target{
  border-color:rgba(234,124,18,.86) !important;
  background:linear-gradient(180deg,#FFFFFF 0%,#FFF7ED 100%) !important;
  box-shadow:
    0 0 0 4px rgba(234,124,18,.14),
    0 22px 46px rgba(234,124,18,.20),
    0 10px 24px rgba(15,23,42,.08) !important;
  transform:translateY(-2px);
  animation:evNotificationOrderPulse 1.15s ease-in-out 2;
}
.ev-mpv-order.is-notification-target::before{
  background:linear-gradient(180deg,#EA7C12 0%,#F59E0B 100%) !important;
  opacity:1 !important;
}
@keyframes evNotificationOrderPulse{
  0%,100%{box-shadow:0 0 0 4px rgba(234,124,18,.14),0 22px 46px rgba(234,124,18,.20),0 10px 24px rgba(15,23,42,.08);}
  50%{box-shadow:0 0 0 7px rgba(234,124,18,.08),0 25px 50px rgba(234,124,18,.24),0 10px 24px rgba(15,23,42,.08);}
}
@media (prefers-reduced-motion:reduce){
  .ev-mpv-order.is-notification-target{animation:none;}
}
</style>
