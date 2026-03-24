<?php /* billeteraEstilo.php – UX/UI Mi Billetera Entre Vecinos */ ?>

<style>
:root{
  --ev-verde-oscuro: var(--verde-oscuro, #0F592F);
  --ev-verde:        var(--verde-claro, #198754);
  --ev-verde-suave:  #E6F4EC;
  --ev-gris-fondo:   var(--gris-claro, #F3F4F6);
  --ev-gris-borde:   var(--gris-borde, #E5E7EB);
  --ev-texto:        #1A1F36;
  --ev-texto-suave:  var(--gris-texto, #6B7280);
  --ev-rojo:         #DC2626;
  --ev-rojo-suave:   #FEF2F2;
  --ev-azul-suave:   #EEF6FF;
  --ev-naranja:      #FF7A1A;
  --ev-naranja-soft: #FFF7ED;

  --ev-shadow-card:  0 18px 48px rgba(15, 23, 42, 0.12);
  --ev-shadow-soft:  0 10px 24px rgba(15, 23, 42, 0.06);
  --ev-shadow-mini:  0 8px 18px rgba(15, 23, 42, 0.08);

  --ev-radius-card:  22px;
  --ev-radius-modal: 18px;

  --ev-modal-grad-1: #0F592F;
  --ev-modal-grad-2: #0E7A43;
  --ev-modal-grad-3: #16A34A;
}

/* ==========================================================
   WRAPPER / CARD BASE
========================================================== */
.ev-wallet-wrapper{
  width: 100%;
  max-width: 100%;
  padding-left: 18px;
  padding-right: 18px;
  margin: 0;
}

.ev-wallet-card{
  width: 100%;
  border-radius: var(--ev-radius-card);
  border: 1px solid rgba(229, 231, 235, 0.95);
  background:
    radial-gradient(circle at top right, rgba(230, 244, 236, 0.85) 0%, rgba(255,255,255,0) 22%),
    linear-gradient(180deg, #FFFFFF 0%, #FCFDFD 100%);
  box-shadow: var(--ev-shadow-card);
  margin: 0 0 34px 0;
  overflow: hidden;
}

.ev-wallet-card .card-body{
  padding: 28px 28px 30px 28px;
}

/* ==========================================================
   HERO
========================================================== */
.ev-wallet-hero{
  display: grid;
  grid-template-columns: minmax(0, 1.45fr) minmax(360px, 0.95fr);
  gap: 22px;
  align-items: stretch;
  margin-bottom: 24px;
}

.ev-wallet-hero-main,
.ev-wallet-hero-side{
  min-width: 0;
}

.ev-wallet-hero-main{
  padding: 24px;
  border-radius: 22px;
  background:
    linear-gradient(135deg, rgba(15,89,47,0.05) 0%, rgba(22,163,74,0.03) 100%),
    #ffffff;
  border: 1px solid rgba(15, 89, 47, 0.10);
  box-shadow: var(--ev-shadow-soft);
}

.ev-wallet-title{
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--ev-verde-oscuro);
  letter-spacing: -0.02em;
}

.ev-wallet-title-icon{
  width: 40px;
  height: 40px;
  border-radius: 999px;
  background: var(--ev-verde-suave);
  color: var(--ev-verde-oscuro);
  font-size: 1.18rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 18px rgba(15, 89, 47, 0.10);
}

.ev-wallet-subtitle{
  font-size: 0.96rem;
  color: var(--ev-texto-suave);
  line-height: 1.5;
  max-width: 760px;
}

.ev-wallet-hero-chips{
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 18px;
}

.ev-wallet-chip{
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 999px;
  background: #ffffff;
  border: 1px solid rgba(229, 231, 235, 0.95);
  color: var(--ev-texto);
  font-size: 0.88rem;
  font-weight: 600;
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
}

.ev-wallet-chip i{
  color: var(--ev-verde-oscuro);
}

.ev-wallet-hero-side{
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.ev-wallet-actions{
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: flex-end;
}

.ev-wallet-balance-card{
  border-radius: 22px;
  padding: 20px 20px 18px 20px;
  background:
    linear-gradient(135deg, rgba(230,244,236,1) 0%, rgba(245,250,247,1) 100%);
  border: 1px solid rgba(15, 89, 47, 0.10);
  box-shadow: 0 18px 32px rgba(15, 89, 47, 0.12);
}

.ev-wallet-balance-top{
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
}

.ev-wallet-balance-label{
  display: inline-block;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--ev-texto-suave);
  margin-bottom: 6px;
}

.ev-wallet-balance-amount{
  font-size: 2rem;
  line-height: 1;
  font-weight: 900;
  color: var(--ev-verde-oscuro);
  letter-spacing: -0.03em;
}

.ev-wallet-balance-icon{
  width: 48px;
  height: 48px;
  border-radius: 16px;
  background: #ffffff;
  color: var(--ev-verde-oscuro);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  box-shadow: var(--ev-shadow-mini);
}

.ev-wallet-balance-meta{
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 16px;
}

.ev-wallet-balance-meta-item{
  background: rgba(255,255,255,0.92);
  border: 1px solid rgba(15, 89, 47, 0.08);
  border-radius: 16px;
  padding: 12px 14px;
}

.ev-wallet-balance-meta-label{
  display: block;
  font-size: 0.76rem;
  color: var(--ev-texto-suave);
  margin-bottom: 4px;
  text-transform: uppercase;
  letter-spacing: .04em;
}

.ev-wallet-balance-meta-item strong{
  font-size: 1.05rem;
  color: var(--ev-texto);
}

/* ==========================================================
   SECTIONS
========================================================== */
.ev-wallet-section{
  margin-top: 22px;
  padding: 22px;
  border-radius: 22px;
  background: #ffffff;
  border: 1px solid rgba(229, 231, 235, 0.95);
  box-shadow: var(--ev-shadow-soft);
}

.ev-wallet-section-head{
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}

.ev-wallet-section-title{
  font-size: 1.08rem;
  font-weight: 800;
  color: var(--ev-verde-oscuro);
  margin-bottom: 6px;
}

.ev-wallet-section-subtitle{
  font-size: .9rem;
  color: var(--ev-texto-suave);
  line-height: 1.45;
}

.ev-wallet-section-recargas{
  margin-top: 18px;
}

.ev-wallet-section-head-recargas{
  align-items: center;
}

/* ==========================================================
   EMPTY
========================================================== */
.ev-wallet-empty{
  padding: 28px 16px;
  border-radius: 18px;
  background:
    linear-gradient(180deg, rgba(249,250,251,1) 0%, rgba(255,255,255,1) 100%);
  border: 1px dashed rgba(148, 163, 184, 0.55);
}

.ev-wallet-empty-icon i{
  font-size: 2.2rem;
  color: var(--ev-verde);
}

/* ==========================================================
   MOVIMIENTOS
========================================================== */
.ev-wallet-movimientos{
  margin-top: 8px;
}

.ev-wallet-mov-list{
  display: grid;
  gap: 14px;
}

.ev-wallet-mov-card{
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  gap: 16px;
  align-items: center;
  padding: 16px 18px;
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid rgba(229, 231, 235, 0.95);
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.05);
  transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}

.ev-wallet-mov-card:hover{
  transform: translateY(-1px);
  box-shadow: 0 14px 24px rgba(15, 23, 42, 0.08);
}

.ev-wallet-mov-card--debito{
  border-left: 5px solid var(--ev-rojo);
}

.ev-wallet-mov-card--credito{
  border-left: 5px solid var(--ev-verde);
}

.ev-wallet-mov-icon-wrap{
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.ev-wallet-mov-card--debito .ev-wallet-mov-icon-wrap{
  background: var(--ev-rojo-suave);
}

.ev-wallet-mov-card--credito .ev-wallet-mov-icon-wrap{
  background: var(--ev-verde-suave);
}

.ev-wallet-mov-icon{
  font-size: 1.3rem;
}
.ev-wallet-mov-icon--credito{ color: var(--ev-verde); }
.ev-wallet-mov-icon--debito{ color: var(--ev-rojo); }

.ev-wallet-mov-main{
  min-width: 0;
}

.ev-wallet-mov-titulo{
  font-weight: 800;
  color: var(--ev-texto);
  font-size: 1rem;
  line-height: 1.35;
  margin-bottom: 8px;
}

.ev-wallet-mov-meta{
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.ev-wallet-mov-chip{
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border-radius: 999px;
  padding: 6px 10px;
  font-size: .78rem;
  font-weight: 700;
  background: #F9FAFB;
  color: var(--ev-texto-suave);
  border: 1px solid rgba(229, 231, 235, 0.95);
}

.ev-wallet-mov-chip i{
  font-size: .9rem;
}

.ev-wallet-mov-side{
  min-width: 150px;
  text-align: right;
}

.ev-wallet-mov-monto{
  font-weight: 900;
  font-size: 1.1rem;
  line-height: 1;
  margin-bottom: 8px;
}
.ev-wallet-monto--credito{ color: var(--ev-verde); }
.ev-wallet-monto--debito{ color: var(--ev-rojo); }

.ev-wallet-mov-saldo-label{
  display: block;
  font-size: .72rem;
  color: var(--ev-texto-suave);
  text-transform: uppercase;
  letter-spacing: .06em;
  margin-bottom: 3px;
}

.ev-wallet-mov-saldo{
  font-size: .92rem;
  color: var(--ev-texto);
  font-weight: 700;
}

/* ==========================================================
   TABLAS / RECARGAS
========================================================== */
.ev-wallet-table-shell{
  border: 1px solid rgba(229, 231, 235, 0.95);
  border-radius: 18px;
  overflow: hidden;
  background: #ffffff;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
}

.ev-wallet-table-shell table{
  margin: 0;
}

.ev-wallet-table-shell thead th{
  border-bottom: 1px solid var(--ev-gris-borde);
  font-weight: 800;
  color: var(--ev-texto-suave);
  text-transform: uppercase;
  font-size: 0.76rem;
  letter-spacing: 0.05em;
  background: #F9FAFB;
  padding-top: 14px;
  padding-bottom: 14px;
}

.ev-wallet-table-shell tbody td{
  border-color: rgba(229, 231, 235, 0.9);
  vertical-align: middle;
}

.ev-wallet-table-shell tbody tr:hover{
  background-color: #FCFDFD;
}

/* ==========================================================
   BUTTONS
========================================================== */
.btn-ev-orange{
  background-image: linear-gradient(180deg, #FF9B3A, #FF7A1A);
  border: none;
  color: #ffffff;
  font-weight: 700;
  border-radius: 999px;
  padding: 0.52rem 1.9rem;
  font-size: 0.96rem;
  box-shadow: 0 14px 28px rgba(255, 122, 26, 0.40);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}

.btn-ev-orange:hover{
  filter: brightness(1.05);
  transform: translateY(-1px);
  box-shadow: 0 18px 32px rgba(255, 122, 26, 0.50);
  color: #ffffff;
}

.btn-ev-outline{
  background-color: #ffffff;
  border-radius: 999px;
  border: 1px solid var(--ev-gris-borde);
  color: var(--ev-texto);
  font-weight: 700;
  padding: 0.48rem 1.35rem;
  font-size: 0.93rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background-color 0.15s ease, transform 0.15s ease;
}

.btn-ev-outline:hover{
  background-color: #F9FAFB;
  transform: translateY(-1px);
}

#btnEnviarRecarga.saving{
  opacity: .85;
  pointer-events: none;
}

/* ==========================================================
   MODALES
========================================================== */
.ev-modal-login .ev-modal-content{
  border-radius: var(--ev-radius-modal);
  border: none;
  overflow: hidden;
  background: transparent;
  box-shadow:
    0 18px 45px rgba(0,0,0,0.22),
    0 6px 12px rgba(0,0,0,0.12);
}

.ev-modal-login .ev-login-modal-header{
  background: linear-gradient(140deg, var(--ev-modal-grad-1) 0%, var(--ev-modal-grad-2) 55%, var(--ev-modal-grad-3) 100%);
  padding: 16px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.20);
  border-radius: var(--ev-radius-modal) var(--ev-radius-modal) 0 0;
  color: #fff;
}

.ev-modal-login .ev-login-modal-header .modal-title{
  font-weight: 600;
  font-size: 1rem;
  color: #fff;
  display: inline-flex;
  align-items: center;
}

.ev-modal-login .ev-login-modal-header .btn-close{
  filter: invert(1);
  opacity: 1;
}

.ev-modal-login .ev-login-modal-body{
  padding-left: 2rem;
  padding-right: 2rem;
  padding-top: 1.8rem;
  padding-bottom: 1.4rem;
  background: #ffffff;
  box-shadow: inset 0 1px 0 rgba(0,0,0,0.06);
}

.ev-modal-login .ev-login-modal-footer{
  border-top: 1px solid #E5E7EB;
  border-radius: 0 0 var(--ev-radius-modal) var(--ev-radius-modal);
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
  padding-left: 1.25rem;
  padding-right: 1.25rem;
  background: #ffffff;
}

.ev-modal-login .ev-modal-content .form-label{
  font-weight: 500;
  font-size: 0.9rem;
  color: #374151;
}

.ev-modal-login .ev-modal-content .form-control,
.ev-modal-login .ev-modal-content .form-select{
  border-radius: 10px;
  border: 1px solid #D1FAE5;
  font-size: 0.95rem;
  transition: all 0.18s ease-out;
  padding-left: 14px;
  padding-right: 14px;
  box-shadow: none;
}

.ev-modal-login .ev-modal-content .form-control::placeholder{
  color: #A3A3A3;
}

.ev-modal-login .ev-modal-content .form-control:focus,
.ev-modal-login .ev-modal-content .form-select:focus{
  border-color: #16A34A;
  box-shadow: 0 0 0 3px rgba(22,163,74,0.20);
  outline: none;
}

#recarga_operacion{ letter-spacing: .03em; }

.ev-btn-modal-outline{
  border-radius: 999px;
  font-size: 0.9rem;
  border-color: #D1D5DB;
  color: #4B5563;
  background-color: #FFFFFF;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.ev-btn-modal-outline:hover{
  background-color: #F3F4F6;
  color: #111827;
}

.ev-btn-modal-primary{
  background: linear-gradient(135deg, #EA7C12, #F59E0B);
  border: none;
  color: #ffffff;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  box-shadow: 0 12px 26px rgba(234,124,18,0.35);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px 16px;
  transition: all 0.2s ease;
}

.ev-btn-modal-primary:hover{
  background: linear-gradient(135deg, #C46B05, #EA580C);
  color: #ffffff;
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(234,124,18,0.48);
}

.ev-btn-modal-primary:active{
  transform: translateY(0);
  box-shadow: 0 6px 16px rgba(234,124,18,0.30);
}

.ev-btn-modal-cta{
  height: 38px;
  padding: 0 16px;
  line-height: 1;
}

/* ==========================================================
   QR CARD
========================================================== */
.ev-wallet-qr-card{
  border-radius: 16px;
  border: 1px solid var(--ev-gris-borde);
  background-color: #F9FAFB;
  padding: 16px 16px 18px 16px;
  box-shadow: var(--ev-shadow-soft);
  margin-right: 4px;
}

.ev-wallet-qr-img{
  max-width: 220px;
  width: 100%;
  height: auto;
  border-radius: 14px;
  display: block;
  margin: 0 auto 10px auto;
  border: 1px solid rgba(148,163,184,.22);
}

.ev-wallet-qr-title{
  font-weight: 800;
  color: var(--ev-texto);
  font-size: 0.98rem;
  margin-top: 8px;
}

.ev-wallet-qr-text{
  font-size: 0.9rem;
  color: var(--ev-texto-suave);
  line-height: 1.35;
}

/* ==========================================================
   SWEETALERT2
========================================================== */
.ev-swal-popup{
  border-radius: 18px !important;
  padding: 18px 18px 16px 18px !important;
  box-shadow: 0 22px 60px rgba(15, 23, 42, 0.25) !important;
}

.ev-swal-title{
  font-weight: 800 !important;
  color: var(--ev-verde-oscuro) !important;
  letter-spacing: -0.01em !important;
}

.ev-swal-html{
  color: var(--ev-texto-suave) !important;
  font-size: 0.98rem !important;
  line-height: 1.45 !important;
}

.ev-swal-icon{
  transform: translateY(-2px);
}

.ev-swal-confirm{
  background-image: linear-gradient(180deg, #FF9B3A, #FF7A1A) !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 800 !important;
  border-radius: 999px !important;
  padding: 10px 18px !important;
  min-width: 140px;
  box-shadow: 0 14px 28px rgba(255, 122, 26, 0.35) !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 8px !important;
  transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease !important;
}

.ev-swal-confirm:hover{
  filter: brightness(1.04) !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 18px 32px rgba(255, 122, 26, 0.45) !important;
}

.ev-swal-confirm:active{
  transform: translateY(0) !important;
  box-shadow: 0 10px 22px rgba(255, 122, 26, 0.28) !important;
}

.ev-swal-cancel{
  background: #ffffff !important;
  border-radius: 999px !important;
  border: 1px solid var(--ev-gris-borde) !important;
  color: var(--ev-texto) !important;
  font-weight: 700 !important;
  padding: 10px 16px !important;
  min-width: 120px;
}

.ev-swal-cancel:hover{
  background: #F9FAFB !important;
  transform: translateY(-1px) !important;
}

.ev-swal-nocancel .swal2-cancel{
  display: none !important;
}

/* ==========================================================
   EXTRA
========================================================== */
.ev-mono{
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

#recarga_alerta_subsanacion{
  border-radius: 14px;
  border: 1px solid rgba(234,124,18,.22);
  background: linear-gradient(180deg, rgba(255,247,237,1) 0%, rgba(255,251,235,1) 100%);
  color: #9A3412;
}

#recarga_alerta_subsanacion .fw-semibold{
  color: #C2410C;
}

/* ==========================================================
   RESPONSIVE
========================================================== */
@media (max-width: 1399.98px){
  .ev-wallet-wrapper{
    padding-left: 14px;
    padding-right: 14px;
  }
}

@media (max-width: 991.98px){
  .ev-wallet-wrapper{
    padding-left: 12px;
    padding-right: 12px;
  }

  .ev-wallet-hero{
    grid-template-columns: 1fr;
  }

  .ev-wallet-actions{
    justify-content: flex-start;
  }

  .ev-wallet-balance-card{
    width: 100%;
  }

  .ev-wallet-qr-card{
    margin-top: 8px;
  }
}

@media (max-width: 767.98px){
  .ev-wallet-card .card-body{
    padding: 18px 14px 22px 14px;
  }

  .ev-wallet-hero-main,
  .ev-wallet-section,
  .ev-wallet-balance-card{
    padding: 18px 16px;
    border-radius: 18px;
  }

  .ev-wallet-title{
    font-size: 1.45rem;
  }

  .ev-wallet-balance-amount{
    font-size: 1.65rem;
  }

  .ev-wallet-section-head,
  .ev-wallet-section-head-recargas{
    flex-direction: column;
    align-items: stretch;
  }

  .ev-wallet-mov-card{
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .ev-wallet-mov-icon-wrap{
    width: 46px;
    height: 46px;
  }

  .ev-wallet-mov-side{
    text-align: left;
    min-width: 0;
    padding-top: 10px;
    border-top: 1px solid rgba(229,231,235,.9);
  }
}

@media (max-width: 575.98px){
  .ev-wallet-wrapper{
    padding-left: 10px !important;
    padding-right: 10px !important;
  }

  .ev-wallet-card{
    margin: 0 0 24px 0;
  }

  .ev-wallet-chip{
    width: 100%;
    justify-content: center;
  }

  .ev-wallet-actions{
    flex-direction: column;
  }

  .ev-wallet-actions .btn{
    width: 100%;
  }

  .ev-wallet-balance-meta{
    grid-template-columns: 1fr;
  }

  .ev-modal .modal-dialog{
    max-width: 100%;
    margin: 0 12px;
  }

  .ev-modal-login .ev-login-modal-body{
    padding-left: 1.25rem;
    padding-right: 1.25rem;
    padding-top: 1.25rem;
    padding-bottom: 1rem;
  }

  .ev-modal-login .ev-login-modal-footer{
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .ev-modal-login .ev-login-modal-footer .btn,
  .ev-modal-login .ev-login-modal-footer a.btn{
    width: 100%;
    justify-content: center;
  }
}
</style>