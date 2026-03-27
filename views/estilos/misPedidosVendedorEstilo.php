<style>
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#16A34A;
  --ev-verde-claro:#bbf7d0;
  --ev-verde-suave:#EAF7EF;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;

  --ev-texto:#111827;
  --ev-texto-suave:#6B7280;

  --ev-gris-025:#FCFDFC;
  --ev-gris-050:#F9FAFB;
  --ev-gris-100:#F3F4F6;
  --ev-gris-150:#EEF2F7;
  --ev-gris-200:#E5E7EB;
  --ev-gris-300:#D1D5DB;
  --ev-gris-500:#6B7280;
  --ev-gris-600:#4B5563;
  --ev-gris-700:#374151;

  --ev-sombra-card:0 16px 44px rgba(15,23,42,.08);
  --ev-sombra-soft:0 12px 28px rgba(15,23,42,.06);
  --ev-sombra-chip:0 10px 18px rgba(15,23,42,.06);

  --ev-radius-card:18px;
  --ev-radius-soft:16px;
  --ev-radius-order:22px;
}

.ev-mpv-page{
  max-width:100%;
  margin:0 auto;
  padding:14px 14px 26px;
  color:var(--ev-texto);
}

.ev-mpv-card-shell{
  border-radius:var(--ev-radius-card);
  background:#fff;
  border:1px solid rgba(148,163,184,.22);
  box-shadow:var(--ev-sombra-card);
  overflow:hidden;
}

.ev-mpv-hero{
  background:
    radial-gradient(circle at 82% 20%, rgba(22,163,74,.08), transparent 55%),
    radial-gradient(circle at 14% 80%, rgba(234,124,18,.07), transparent 55%),
    linear-gradient(180deg, #ffffff 0%, #fbfdfb 100%);
}

.ev-mpv-hero-body{
  padding:18px 18px 14px;
}

.ev-mpv-hero-top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:16px;
  flex-wrap:wrap;
}

.ev-mpv-title-wrap{
  display:flex;
  align-items:flex-start;
  gap:14px;
}

.ev-mpv-title-icon{
  width:44px;
  height:44px;
  border-radius:16px;
  background:rgba(187,247,208,.55);
  border:1px solid rgba(22,163,74,.20);
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  box-shadow:0 12px 22px rgba(15,23,42,.06);
  font-size:1.12rem;
  flex-shrink:0;
}

.ev-mpv-kicker{
  font-size:.76rem;
  font-weight:900;
  letter-spacing:.12em;
  text-transform:uppercase;
  color:var(--ev-naranja);
  margin-bottom:4px;
}

.ev-mpv-title{
  color:var(--ev-verde-oscuro);
  font-weight:850;
  letter-spacing:-.02em;
  font-size:2.05rem;
  margin:0;
}

.ev-mpv-subtitle,
.ev-mpv-block-subtitle{
  color:var(--ev-gris-500);
  font-size:.95rem;
  margin:0;
}

.ev-mpv-summary-grid{
  display:grid;
  grid-template-columns:repeat(3, minmax(120px, 1fr));
  gap:12px;
  width:min(100%, 430px);
}

.ev-mpv-summary-card{
  background:#fff;
  border:1px solid rgba(148,163,184,.18);
  border-radius:16px;
  padding:14px 16px;
  box-shadow:var(--ev-sombra-soft);
}

.ev-mpv-summary-label{
  display:block;
  color:var(--ev-gris-500);
  font-size:.82rem;
  margin-bottom:4px;
  font-weight:700;
}

.ev-mpv-summary-card strong{
  color:var(--ev-verde-oscuro);
  font-size:1.35rem;
  font-weight:900;
}

.ev-mpv-panel{
  border-radius:var(--ev-radius-card);
  background:#fff;
  border:1px solid rgba(148,163,184,.22);
  box-shadow:var(--ev-sombra-card);
  overflow:hidden;
}

.ev-mpv-panel-head{
  padding:16px 18px 14px;
  border-bottom:1px solid var(--ev-gris-200);
  background:#fff;
}

.ev-mpv-panel-head-row{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:14px;
  flex-wrap:wrap;
}

.ev-mpv-block-title{
  color:var(--ev-verde-oscuro);
  font-size:1.05rem;
  font-weight:900;
  margin:0 0 4px;
}

.ev-mpv-actions-top{
  display:flex;
  flex-direction:column;
  align-items:flex-end;
  gap:10px;
  flex-wrap:wrap;
}

.ev-mpv-tab-groups{
  display:flex;
  gap:12px;
  flex-wrap:wrap;
  justify-content:flex-end;
}

.ev-mpv-tab-group{
  display:flex;
  align-items:center;
  gap:8px;
  flex-wrap:wrap;
  padding:8px 10px;
  border-radius:16px;
  background:linear-gradient(180deg,#ffffff 0%, #fbfdfb 100%);
  border:1px solid rgba(148,163,184,.18);
  box-shadow:var(--ev-sombra-soft);
}

.ev-mpv-tab-group-label{
  font-size:.78rem;
  font-weight:900;
  color:var(--ev-gris-500);
  margin-right:2px;
}

.ev-mpv-tabs{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
}

.ev-mpv-tab{
  border-radius:999px;
  border:1px solid rgba(22,163,74,.18);
  background:rgba(255,255,255,.92);
  color:var(--ev-verde-oscuro);
  font-weight:850;
  box-shadow:0 10px 20px rgba(15,23,42,.06);
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease, border-color .16s ease;
  padding:.68rem .95rem;
}

.ev-mpv-tab:hover{
  background:linear-gradient(90deg, rgba(187,247,208,.55), rgba(187,247,208,.18));
  border-color:rgba(22,163,74,.32);
  transform:translateY(-1px);
  box-shadow:0 14px 26px rgba(15,23,42,.10);
  filter:brightness(1.02);
}

.ev-mpv-tab.active{
  background:linear-gradient(90deg, rgba(187,247,208,.60), rgba(187,247,208,.22));
  border-color:rgba(22,163,74,.30);
}

.ev-mpv-tab-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:26px;
  padding:1px 8px;
  margin-left:8px;
  border-radius:999px;
  background:rgba(255,255,255,.92);
  border:1px solid rgba(22,163,74,.18);
  font-weight:900;
  font-size:.78rem;
}

.ev-mpv-btn-refresh{
  border-radius:999px;
  border:1px solid rgba(22,163,74,.18);
  background:rgba(255,255,255,.92);
  color:var(--ev-verde-oscuro);
  font-weight:850;
  box-shadow:0 10px 20px rgba(15,23,42,.06);
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease, border-color .16s ease;
  padding:.72rem 1rem;
}

.ev-mpv-btn-refresh:hover{
  background:linear-gradient(90deg, rgba(187,247,208,.55), rgba(187,247,208,.18));
  border-color:rgba(22,163,74,.32);
  transform:translateY(-1px);
  box-shadow:0 14px 26px rgba(15,23,42,.10);
  filter:brightness(1.02);
}

.ev-mpv-panel-body{
  padding:16px 18px 18px;
}

.ev-mpv-alert,
.ev-mpv-empty{
  border-radius:16px;
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
  grid-template-columns:repeat(auto-fill, minmax(410px, 1fr));
  gap:18px;
}

.ev-mpv-order{
  border:1px solid rgba(148,163,184,.20);
  border-radius:var(--ev-radius-order);
  background:linear-gradient(180deg,#ffffff 0%, #fbfbfc 100%);
  box-shadow:var(--ev-sombra-soft);
  overflow:hidden;
  transition:transform .16s ease, box-shadow .16s ease;
}

.ev-mpv-order:hover{
  transform:translateY(-1px);
  box-shadow:0 18px 34px rgba(15,23,42,.08);
}

.ev-mpv-order-top{
  display:grid;
  grid-template-columns:82px 1fr;
  gap:12px;
  padding:13px 13px 11px;
  border-bottom:1px solid var(--ev-gris-200);
  align-items:start;
}

.ev-mpv-order-media{
  width:82px;
  height:82px;
  border-radius:16px;
  overflow:hidden;
  background:#F3F4F6;
  box-shadow:0 8px 18px rgba(15,23,42,.08);
  align-self:start;
}

.ev-mpv-order-media img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-mpv-order-head{
  min-width:0;
}

.ev-mpv-order-head-row{
  display:flex;
  justify-content:space-between;
  gap:10px;
  align-items:flex-start;
  margin-bottom:7px;
}

.ev-mpv-order-title{
  color:var(--ev-verde-oscuro);
  font-weight:800;
  font-size:1.02rem;
  line-height:1.18;
  margin-bottom:3px;
}

.ev-mpv-order-meta{
  color:var(--ev-gris-500);
  font-size:.83rem;
  line-height:1.3;
}

.ev-mpv-badge{
  border-radius:999px;
  padding:7px 12px;
  font-size:.76rem;
  font-weight:850;
  white-space:nowrap;
  box-shadow:var(--ev-sombra-chip);
}

.ev-mpv-badge-pendiente{
  background:#FEF3C7;
  color:#92400E;
}

.ev-mpv-badge-proceso{
  background:#DCFCE7;
  color:#166534;
}

.ev-mpv-badge-final{
  background:#E0F2FE;
  color:#075985;
}

.ev-mpv-order-quick{
  display:flex;
  gap:7px;
  flex-wrap:wrap;
  margin-bottom:9px;
}

.ev-mpv-pill{
  display:inline-flex;
  align-items:center;
  gap:7px;
  padding:6px 10px;
  border-radius:999px;
  background:linear-gradient(90deg, rgba(187,247,208,.40), rgba(187,247,208,.14));
  border:1px solid rgba(22,163,74,.16);
  color:#355B4A;
  font-size:.76rem;
  font-weight:800;
}

.ev-mpv-pill i{
  color:var(--ev-verde);
}

.ev-mpv-order-data{
  display:grid;
  grid-template-columns:1.55fr .95fr;
  grid-template-areas:
    "buyer total"
    "date  date";
  gap:9px;
  align-items:stretch;
}

.ev-mpv-data-box{
  border:1px solid rgba(229,231,235,.95);
  background:#fff;
  border-radius:15px;
  padding:10px 11px;
  min-height:66px;
}

.ev-mpv-data-box span{
  display:block;
  font-size:.72rem;
  color:var(--ev-gris-500);
  margin-bottom:4px;
  font-weight:700;
}

.ev-mpv-data-box strong{
  display:block;
  color:var(--ev-texto);
  font-size:.92rem;
  font-weight:750;
  line-height:1.28;
  word-break:break-word;
}

.ev-mpv-data-box-buyer{
  grid-area:buyer;
}

.ev-mpv-data-box-buyer strong{
  font-size:.98rem;
  line-height:1.24;
}

.ev-mpv-data-box-date{
  grid-area:date;
}

.ev-mpv-data-box-date strong{
  white-space:nowrap;
  word-break:normal;
  overflow-wrap:normal;
}

.ev-mpv-data-box-total{
  grid-area:total;
  background:linear-gradient(180deg, rgba(236,253,245,.72) 0%, rgba(255,255,255,.95) 100%);
  border:1px solid rgba(22,163,74,.18);
  display:flex;
  flex-direction:column;
  justify-content:center;
  min-height:66px;
}

.ev-mpv-data-box-total strong{
  color:var(--ev-verde-oscuro);
  font-size:1.16rem;
  font-weight:900;
  line-height:1.12;
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
  gap:8px;
  margin-bottom:12px;
}

.ev-mpv-stepper-final{
  margin-bottom:12px;
}

.ev-mpv-step{
  display:inline-flex;
  align-items:center;
  gap:8px;
  border-radius:999px;
  padding:7px 11px;
  font-size:.76rem;
  font-weight:750;
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
}

.ev-mpv-step.is-current .ev-mpv-step-dot{
  background:#EA7C12;
}

.ev-mpv-step.is-final{
  background:#F8FAFC;
  color:#334155;
}

.ev-mpv-info-card{
  border:1px solid rgba(229,231,235,.95);
  border-radius:16px;
  background:#fff;
  padding:12px 13px;
}

.ev-mpv-line{
  display:flex;
  justify-content:space-between;
  gap:12px;
  margin-bottom:8px;
  font-size:.89rem;
}

.ev-mpv-line:last-child{
  margin-bottom:0;
}

.ev-mpv-line-label{
  color:var(--ev-gris-500);
  font-weight:700;
}

.ev-mpv-line-value{
  color:var(--ev-texto);
  font-weight:700;
  text-align:right;
  max-width:58%;
  word-break:break-word;
}

.ev-mpv-note{
  margin-top:10px;
  border-radius:14px;
  background:linear-gradient(180deg, rgba(249,250,251,.96) 0%, rgba(243,244,246,.94) 100%);
  border:1px solid rgba(229,231,235,.95);
  padding:10px 12px;
}

.ev-mpv-note-label{
  display:block;
  color:var(--ev-verde-oscuro);
  font-size:.73rem;
  font-weight:900;
  margin-bottom:4px;
}

.ev-mpv-note-text{
  color:#475569;
  font-size:.86rem;
  line-height:1.45;
  word-break:break-word;
}

.ev-mpv-state-box{
  margin-top:12px;
  border-radius:16px;
  padding:12px 13px;
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

.ev-mpv-state-title{
  color:var(--ev-verde-oscuro);
  font-weight:900;
  font-size:.90rem;
  margin-bottom:3px;
}

.ev-mpv-state-text{
  color:#475569;
  font-size:.85rem;
  line-height:1.45;
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
  font-size:.77rem;
}

.ev-mpv-actions{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin-top:14px;
}

.ev-mpv-btn-accept,
.ev-mpv-btn-action,
.ev-mpv-btn-outline,
.ev-mpv-btn-success,
.ev-mpv-btn-danger-soft{
  border-radius:14px;
  padding:.78rem 1rem;
  font-weight:850;
  font-size:.90rem;
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease;
}

.ev-mpv-btn-accept{
  background:linear-gradient(135deg, var(--ev-verde), #22C55E);
  border:none;
  color:#fff;
  box-shadow:0 12px 26px rgba(22,163,74,.20);
}

.ev-mpv-btn-action{
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border:none;
  color:#fff;
  box-shadow:0 12px 26px rgba(234,124,18,.20);
}

.ev-mpv-btn-success{
  background:linear-gradient(135deg, #16A34A, #22C55E);
  border:none;
  color:#fff;
  box-shadow:0 12px 26px rgba(22,163,74,.20);
}

.ev-mpv-btn-outline{
  border:1px solid rgba(22,163,74,.18);
  background:rgba(255,255,255,.92);
  color:var(--ev-verde-oscuro);
  box-shadow:0 10px 20px rgba(15,23,42,.06);
}

.ev-mpv-btn-outline:hover{
  background:linear-gradient(90deg, rgba(187,247,208,.55), rgba(187,247,208,.18));
  border-color:rgba(22,163,74,.32);
  transform:translateY(-1px);
  box-shadow:0 14px 26px rgba(15,23,42,.10);
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
  border-radius:24px !important;
}

.ev-mpv-modal-detail{
  text-align:left;
}

.ev-mpv-modal-top{
  display:grid;
  grid-template-columns:160px 1fr;
  gap:18px;
  margin-bottom:18px;
}

.ev-mpv-modal-media{
  width:160px;
  height:160px;
  border-radius:20px;
  overflow:hidden;
  background:#F3F4F6;
  box-shadow:0 12px 28px rgba(15,23,42,.10);
}

.ev-mpv-modal-media img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-mpv-modal-head{
  display:flex;
  justify-content:space-between;
  gap:12px;
  align-items:flex-start;
  margin-bottom:14px;
}

.ev-mpv-modal-title{
  color:var(--ev-verde-oscuro);
  font-size:1.2rem;
  font-weight:850;
  line-height:1.2;
}

.ev-mpv-modal-subtitle{
  color:var(--ev-gris-500);
  font-size:.9rem;
  margin-top:4px;
}

.ev-mpv-modal-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0, 1fr));
  gap:10px;
}

.ev-mpv-modal-item{
  border:1px solid #EEF2F7;
  background:#FBFDFC;
  border-radius:14px;
  padding:12px;
}

.ev-mpv-modal-item span{
  display:block;
  color:var(--ev-gris-500);
  font-size:.78rem;
  margin-bottom:4px;
  font-weight:700;
}

.ev-mpv-modal-item strong{
  color:var(--ev-texto);
  font-weight:800;
}

.ev-mpv-modal-section{
  margin-top:16px;
}

.ev-mpv-modal-stack{
  border:1px solid #EEF2F7;
  border-radius:18px;
  padding:14px;
  background:#fff;
}

.ev-mpv-modal-row{
  display:flex;
  justify-content:space-between;
  gap:12px;
  padding:8px 0;
  border-bottom:1px solid #F1F5F9;
}

.ev-mpv-modal-row:last-child{
  border-bottom:none;
}

.ev-mpv-modal-row span{
  color:var(--ev-gris-500);
}

.ev-mpv-modal-row strong{
  color:var(--ev-texto);
  text-align:right;
  max-width:60%;
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

@media (max-width: 1199.98px){
  .ev-mpv-grid{
    grid-template-columns:repeat(auto-fill, minmax(380px, 1fr));
  }
}

@media (max-width: 991.98px){
  .ev-mpv-grid{
    grid-template-columns:1fr;
  }

  .ev-mpv-summary-grid{
    width:100%;
  }

  .ev-mpv-actions-top{
    align-items:flex-start;
  }

  .ev-mpv-tab-groups{
    justify-content:flex-start;
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

  .ev-mpv-summary-grid{
    grid-template-columns:1fr;
  }

  .ev-mpv-title{
    font-size:1.68rem;
  }

  .ev-mpv-order-top{
    grid-template-columns:82px 1fr;
    gap:11px;
    align-items:start;
  }

  .ev-mpv-order-media{
    width:82px;
    height:82px;
  }

  .ev-mpv-order-head-row{
    flex-direction:column;
    align-items:flex-start;
  }

  .ev-mpv-order-data{
    grid-template-columns:1.45fr .95fr;
    grid-template-areas:
      "buyer total"
      "date  date";
    gap:8px;
  }

  .ev-mpv-data-box strong{
    font-size:.88rem;
  }

  .ev-mpv-data-box-buyer strong{
    font-size:.94rem;
  }

  .ev-mpv-data-box-total strong{
    font-size:1.08rem;
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
</style>