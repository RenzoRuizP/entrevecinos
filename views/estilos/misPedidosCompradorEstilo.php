<style>
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#16A34A;
  --ev-verde-suave:#ECFDF3;
  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;
  --ev-texto:#111827;
  --ev-texto-suave:#6B7280;
  --ev-borde:#E5E7EB;
  --ev-fondo:#F8FAFC;
  --ev-sombra:0 18px 40px rgba(15,23,42,.08);
}

.ev-mpc-page{
  max-width:1320px;
  margin:0 auto;
}

.ev-mpc-hero{
  background:linear-gradient(135deg, #ffffff 0%, #F7FBF8 100%);
  border:1px solid rgba(15,89,47,.08);
}

.ev-mpc-kicker{
  font-size:.78rem;
  font-weight:800;
  letter-spacing:.12em;
  color:var(--ev-naranja);
  margin-bottom:6px;
}

.ev-mpc-title{
  color:var(--ev-verde-oscuro);
  font-weight:800;
  letter-spacing:-.02em;
}

.ev-mpc-subtitle,
.ev-mpc-block-subtitle{
  color:var(--ev-texto-suave);
  font-size:.96rem;
}

.ev-mpc-summary-grid{
  display:grid;
  grid-template-columns:repeat(3, minmax(120px, 1fr));
  gap:12px;
  width:min(100%, 420px);
}

.ev-mpc-summary-card{
  background:#fff;
  border:1px solid rgba(15,89,47,.08);
  border-radius:18px;
  padding:14px 16px;
  box-shadow:0 10px 26px rgba(15,23,42,.05);
}

.ev-mpc-summary-label{
  display:block;
  color:var(--ev-texto-suave);
  font-size:.82rem;
  margin-bottom:4px;
}

.ev-mpc-summary-card strong{
  color:var(--ev-verde-oscuro);
  font-size:1.3rem;
  font-weight:800;
}

.ev-mpc-card{
  border:1px solid rgba(15,89,47,.08);
}

.ev-mpc-block-title{
  color:var(--ev-verde-oscuro);
  font-weight:800;
}

.ev-mpc-tab,
.ev-mpc-btn-refresh{
  border-radius:999px;
  padding:.70rem 1rem;
  font-weight:800;
  border:1px solid #D1D5DB;
  background:#fff;
  color:#374151;
}

.ev-mpc-tab.active{
  background:linear-gradient(135deg, var(--ev-verde-oscuro), var(--ev-verde));
  color:#fff;
  border-color:transparent;
  box-shadow:0 12px 24px rgba(22,163,74,.22);
}

.ev-mpc-btn-refresh{
  background:#F9FAFB;
  color:var(--ev-verde-oscuro);
}

.ev-mpc-alert,
.ev-mpc-empty{
  border-radius:16px;
  padding:14px 16px;
  text-align:center;
  margin-bottom:16px;
  font-weight:700;
}

.ev-mpc-alert-error{
  background:#FEF2F2;
  color:#991B1B;
  border:1px solid #FECACA;
}

.ev-mpc-empty{
  background:#F9FAFB;
  color:#4B5563;
  border:1px solid #E5E7EB;
}

.ev-mpc-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(390px, 1fr));
  gap:18px;
}

.ev-mpc-order{
  border:1px solid rgba(15,89,47,.10);
  border-radius:24px;
  background:#fff;
  box-shadow:var(--ev-sombra);
  overflow:hidden;
}

.ev-mpc-order-top{
  display:grid;
  grid-template-columns:108px 1fr;
  gap:16px;
  padding:18px 18px 14px;
  border-bottom:1px solid #EEF2F7;
}

.ev-mpc-order-media{
  width:108px;
  height:108px;
  border-radius:18px;
  overflow:hidden;
  background:#F3F4F6;
  box-shadow:0 8px 20px rgba(15,23,42,.08);
}

.ev-mpc-order-media img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-mpc-order-head-main{
  min-width:0;
}

.ev-mpc-order-head-row{
  display:flex;
  justify-content:space-between;
  gap:12px;
  align-items:flex-start;
  margin-bottom:14px;
}

.ev-mpc-order-title{
  color:var(--ev-verde-oscuro);
  font-weight:800;
  font-size:1.05rem;
  margin-bottom:2px;
  line-height:1.2;
}

.ev-mpc-order-meta{
  color:var(--ev-texto-suave);
  font-size:.84rem;
}

.ev-mpc-badge{
  border-radius:999px;
  padding:7px 12px;
  font-size:.76rem;
  font-weight:800;
  white-space:nowrap;
}

.ev-mpc-badge-pendiente{
  background:#FEF3C7;
  color:#92400E;
}

.ev-mpc-badge-proceso{
  background:#DCFCE7;
  color:#166534;
}

.ev-mpc-badge-final{
  background:#E0F2FE;
  color:#075985;
}

.ev-mpc-order-mini-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0, 1fr));
  gap:10px;
}

.ev-mpc-mini-item{
  border:1px solid #EEF2F7;
  background:#FBFDFC;
  border-radius:14px;
  padding:10px 12px;
}

.ev-mpc-mini-item span{
  display:block;
  font-size:.76rem;
  color:var(--ev-texto-suave);
  margin-bottom:4px;
}

.ev-mpc-mini-item strong{
  color:var(--ev-texto);
  font-size:.94rem;
  font-weight:800;
}

.ev-mpc-order-body{
  padding:16px 18px 18px;
}

.ev-mpc-stepper{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  margin-bottom:14px;
}

.ev-mpc-stepper-final{
  margin-bottom:14px;
}

.ev-mpc-step{
  display:inline-flex;
  align-items:center;
  gap:8px;
  border-radius:999px;
  padding:7px 12px;
  font-size:.78rem;
  font-weight:700;
  border:1px solid #E5E7EB;
  background:#fff;
  color:#64748B;
}

.ev-mpc-step-dot{
  width:8px;
  height:8px;
  border-radius:50%;
  background:#CBD5E1;
  flex-shrink:0;
}

.ev-mpc-step.is-done{
  background:#ECFDF3;
  border-color:#BBF7D0;
  color:#166534;
}

.ev-mpc-step.is-done .ev-mpc-step-dot{
  background:#16A34A;
}

.ev-mpc-step.is-current{
  background:#FFF7ED;
  border-color:#FCD9BD;
  color:#C46B05;
}

.ev-mpc-step.is-current .ev-mpc-step-dot{
  background:#EA7C12;
}

.ev-mpc-step.is-final{
  background:#F8FAFC;
  color:#334155;
}

.ev-mpc-info-card{
  border:1px solid #EEF2F7;
  border-radius:18px;
  background:#fff;
  padding:14px;
}

.ev-mpc-line{
  display:flex;
  justify-content:space-between;
  gap:12px;
  margin-bottom:8px;
  font-size:.92rem;
}

.ev-mpc-line:last-child{
  margin-bottom:0;
}

.ev-mpc-line-label{
  color:var(--ev-texto-suave);
}

.ev-mpc-line-value{
  color:var(--ev-texto);
  font-weight:700;
  text-align:right;
  max-width:58%;
}

.ev-mpc-note{
  margin-top:12px;
  border-radius:14px;
  background:#F8FAFC;
  border:1px solid #E5E7EB;
  padding:12px 14px;
}

.ev-mpc-note-label{
  display:block;
  color:var(--ev-verde-oscuro);
  font-size:.78rem;
  font-weight:800;
  margin-bottom:4px;
}

.ev-mpc-note-text{
  color:#475569;
  font-size:.88rem;
  line-height:1.5;
}

.ev-mpc-state-box{
  margin-top:14px;
  border-radius:18px;
  padding:14px 15px;
  border:1px solid transparent;
}

.ev-mpc-state-box-pending{
  background:#FFF9EC;
  border-color:#FCD9BD;
}

.ev-mpc-state-box-process{
  background:#F6FBF8;
  border-color:#D7F0E0;
}

.ev-mpc-state-box-info{
  background:#F0F9FF;
  border-color:#BAE6FD;
}

.ev-mpc-state-box-final{
  background:#F8FAFC;
  border-color:#E2E8F0;
}

.ev-mpc-state-title{
  color:var(--ev-verde-oscuro);
  font-weight:800;
  font-size:.92rem;
  margin-bottom:4px;
}

.ev-mpc-state-text{
  color:#475569;
  font-size:.87rem;
  line-height:1.5;
}

.ev-mpc-actions{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin-top:16px;
}

.ev-mpc-btn-primary,
.ev-mpc-btn-outline{
  border-radius:14px;
  padding:.78rem 1rem;
  font-weight:800;
  font-size:.92rem;
}

.ev-mpc-btn-primary{
  background:linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border:none;
  color:#fff;
  box-shadow:0 12px 26px rgba(234,124,18,.20);
}

.ev-mpc-btn-outline{
  border:1px solid #D1D5DB;
  background:#fff;
  color:#374151;
}

.ev-mpc-btn-primary:hover,
.ev-mpc-btn-outline:hover,
.ev-mpc-btn-refresh:hover,
.ev-mpc-tab:hover{
  transform:translateY(-1px);
}

.ev-mpc-swal-popup{
  border-radius:24px !important;
}

.ev-mpc-modal-detail{
  text-align:left;
}

.ev-mpc-modal-top{
  display:grid;
  grid-template-columns:160px 1fr;
  gap:18px;
  margin-bottom:18px;
}

.ev-mpc-modal-media{
  width:160px;
  height:160px;
  border-radius:20px;
  overflow:hidden;
  background:#F3F4F6;
  box-shadow:0 12px 28px rgba(15,23,42,.10);
}

.ev-mpc-modal-media img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-mpc-modal-head{
  display:flex;
  justify-content:space-between;
  gap:12px;
  align-items:flex-start;
  margin-bottom:14px;
}

.ev-mpc-modal-title{
  color:var(--ev-verde-oscuro);
  font-size:1.2rem;
  font-weight:800;
  line-height:1.2;
}

.ev-mpc-modal-subtitle{
  color:var(--ev-texto-suave);
  font-size:.9rem;
  margin-top:4px;
}

.ev-mpc-modal-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0, 1fr));
  gap:10px;
}

.ev-mpc-modal-item{
  border:1px solid #EEF2F7;
  background:#FBFDFC;
  border-radius:14px;
  padding:12px;
}

.ev-mpc-modal-item span{
  display:block;
  color:var(--ev-texto-suave);
  font-size:.78rem;
  margin-bottom:4px;
}

.ev-mpc-modal-item strong{
  color:var(--ev-texto);
  font-weight:800;
}

.ev-mpc-modal-section{
  margin-top:16px;
}

.ev-mpc-modal-stack{
  border:1px solid #EEF2F7;
  border-radius:18px;
  padding:14px;
  background:#fff;
}

.ev-mpc-modal-row{
  display:flex;
  justify-content:space-between;
  gap:12px;
  padding:8px 0;
  border-bottom:1px solid #F1F5F9;
}

.ev-mpc-modal-row:last-child{
  border-bottom:none;
}

.ev-mpc-modal-row span{
  color:var(--ev-texto-suave);
}

.ev-mpc-modal-row strong{
  color:var(--ev-texto);
  text-align:right;
  max-width:60%;
}

.ev-mpc-modal-note-title{
  color:var(--ev-verde-oscuro);
  font-size:.86rem;
  font-weight:800;
  margin-bottom:8px;
}

.ev-mpc-modal-note{
  border-radius:16px;
  background:#F8FAFC;
  border:1px solid #E5E7EB;
  padding:14px;
  color:#475569;
  line-height:1.6;
}

@media (max-width: 991.98px){
  .ev-mpc-grid{
    grid-template-columns:1fr;
  }
}

@media (max-width: 767.98px){
  .ev-mpc-summary-grid{
    grid-template-columns:1fr;
    width:100%;
  }

  .ev-mpc-order-top{
    grid-template-columns:1fr;
  }

  .ev-mpc-order-media{
    width:100%;
    height:180px;
  }

  .ev-mpc-order-head-row{
    flex-direction:column;
    align-items:flex-start;
  }

  .ev-mpc-order-mini-grid{
    grid-template-columns:1fr;
  }

  .ev-mpc-line{
    flex-direction:column;
    gap:4px;
  }

  .ev-mpc-line-value{
    text-align:left;
    max-width:100%;
  }

  .ev-mpc-actions{
    flex-direction:column;
  }

  .ev-mpc-modal-top{
    grid-template-columns:1fr;
  }

  .ev-mpc-modal-media{
    width:100%;
    height:220px;
  }

  .ev-mpc-modal-head{
    flex-direction:column;
    align-items:flex-start;
  }

  .ev-mpc-modal-grid{
    grid-template-columns:1fr;
  }

  .ev-mpc-modal-row{
    flex-direction:column;
    gap:4px;
  }

  .ev-mpc-modal-row strong{
    text-align:left;
    max-width:100%;
  }
}
</style>