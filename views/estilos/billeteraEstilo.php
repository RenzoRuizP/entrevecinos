<?php /* billeteraEstilo.php – UX/UI Mi Billetera Entre Vecinos (armonizado con Datos Personales / Login) */ ?>

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
  --ev-naranja:      #FF7A1A;

  --ev-shadow-card:  0 14px 40px rgba(15, 23, 42, 0.14);
  --ev-shadow-soft:  0 10px 24px rgba(15, 23, 42, 0.06);

  /* Radios */
  --ev-radius-card:  18px;
  --ev-radius-modal: 18px; /* alineado con login */

  /* Paleta modal (login) */
  --ev-modal-grad-1: #0F592F;
  --ev-modal-grad-2: #0E7A43;
  --ev-modal-grad-3: #16A34A;
}

/* ==========================================================
   WRAPPER / LAYOUT
========================================================== */
.ev-wallet-wrapper{
  max-width: 1100px;
  margin: 0 auto;
}

.ev-wallet-card{
  border-radius: var(--ev-radius-card);
  border: 1px solid var(--ev-gris-borde);
  background: #ffffff;
  box-shadow: var(--ev-shadow-card);
  margin: 24px auto 40px auto;
  overflow: hidden;
}

.ev-wallet-card .card-body{
  padding: 24px 32px;
}

.ev-wallet-header{ min-height: 56px; }

/* ==========================================================
   TITULOS / SUBTITULOS
========================================================== */
.ev-wallet-title{
  font-size: 1.65rem;
  font-weight: 800;
  color: var(--ev-verde-oscuro);
  letter-spacing: -0.01em;
}

.ev-wallet-title-icon{
  width: 34px;
  height: 34px;
  border-radius: 999px;
  background: var(--ev-verde-suave);
  color: var(--ev-verde-oscuro);
  font-size: 1.1rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
}

.ev-wallet-subtitle{
  font-size: 0.92rem;
  color: var(--ev-texto-suave);
  line-height: 1.35;
}

.ev-wallet-divider{
  border-top: 1px solid rgba(148, 163, 184, 0.35);
  margin-left: -32px;
  margin-right: -32px;
}

/* ==========================================================
   BADGE SALDO
========================================================== */
.ev-wallet-badge{
  padding: 10px 16px;
  border-radius: 16px;
  background: var(--ev-verde-suave);
  display: flex;
  flex-direction: column;
  min-width: 210px;
  box-shadow: 0 8px 24px rgba(15, 89, 47, 0.16);
}

.ev-wallet-badge-label{
  font-size: 0.68rem;
  text-transform: uppercase;
  color: var(--ev-texto-suave);
  letter-spacing: 0.06em;
}

.ev-wallet-badge-amount{
  font-size: 1.4rem;
  font-weight: 800;
  color: var(--ev-verde-oscuro);
}

/* ==========================================================
   EMPTY STATE
========================================================== */
.ev-wallet-empty{
  padding: 24px 12px;
  border-radius: 14px;
  background-color: #ffffff;
  border: 1px dashed rgba(148, 163, 184, 0.55);
}

.ev-wallet-empty-icon i{
  font-size: 2.1rem;
  color: var(--ev-verde);
}

/* ==========================================================
   MOVIMIENTOS / TABLA
========================================================== */
.ev-wallet-movimientos{ margin-top: 12px; }

.ev-wallet-table-wrapper{
  border: 1px solid rgba(229, 231, 235, 0.9);
  border-radius: 16px;
  overflow: hidden;
  background: #ffffff;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
}

.ev-wallet-table{ margin: 0; }

.ev-wallet-table thead th{
  border-bottom: 1px solid var(--ev-gris-borde);
  font-weight: 700;
  color: var(--ev-texto-suave);
  text-transform: uppercase;
  font-size: 0.78rem;
  letter-spacing: 0.05em;
  background: #F9FAFB;
}

.ev-wallet-table tbody td{
  border-color: rgba(229, 231, 235, 0.9);
}

.ev-wallet-table tbody tr:hover{
  background-color: #F9FAFB;
}

/* Estructura usada por tu JS */
.ev-wallet-mov-concepto{
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.ev-wallet-mov-header{
  display: flex;
  align-items: center;
  gap: 10px;
}

.ev-wallet-mov-icon{ font-size: 1.05rem; }
.ev-wallet-mov-icon--credito{ color: var(--ev-verde); }
.ev-wallet-mov-icon--debito{ color: var(--ev-rojo); }

.ev-wallet-mov-titulo{
  font-weight: 700;
  color: var(--ev-texto);
}

.ev-wallet-mov-detalle{
  font-size: 0.85rem;
  color: var(--ev-texto-suave);
}

.ev-wallet-mov-monto{ font-weight: 800; }
.ev-wallet-monto--credito{ color: var(--ev-verde); }
.ev-wallet-monto--debito{ color: var(--ev-rojo); }
.ev-wallet-mov-saldo{ font-size: 0.9rem; }

/* ==========================================================
   BOTONES EV (generales billetera)
========================================================== */
.btn-ev-orange{
  background-image: linear-gradient(180deg, #FF9B3A, #FF7A1A);
  border: none;
  color: #ffffff;
  font-weight: 700;
  border-radius: 999px;
  padding: 0.48rem 1.9rem;
  font-size: 0.96rem;
  box-shadow: 0 14px 28px rgba(255, 122, 26, 0.45);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}

.btn-ev-orange:hover{
  filter: brightness(1.05);
  transform: translateY(-1px);
  box-shadow: 0 18px 32px rgba(255, 122, 26, 0.55);
  color: #ffffff;
}

.btn-ev-outline{
  background-color: #ffffff;
  border-radius: 999px;
  border: 1px solid var(--ev-gris-borde);
  color: var(--ev-texto);
  font-weight: 600;
  padding: 0.45rem 1.4rem;
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

/* Estado “saving” usado por JS */
#btnEnviarRecarga.saving{
  opacity: .85;
  pointer-events: none;
}

/* ==========================================================
   MODALES (ESTILO LOGIN / RECUPERAR CUENTA)
   - Solo aplica a modales con clase: .ev-modal-login
========================================================== */
.ev-modal-login .ev-modal-content{
  border-radius: var(--ev-radius-modal);
  border: none;
  overflow: hidden;
  background: transparent; /* como login */
  box-shadow:
    0 18px 45px rgba(0,0,0,0.22),
    0 6px 12px rgba(0,0,0,0.12);
}

/* Header degradado como login */
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

/* Body y footer igual que login */
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

/* Inputs dentro del modal (matching login) */
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

/* ID operación: lectura más clara */
#recarga_operacion{ letter-spacing: .03em; }

/* Botones modal (matching login) */
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

/* CTA compacta (Llamar ahora) similar a btn-modal-cta del login */
.ev-btn-modal-cta{
  height: 38px;
  padding: 0 16px;
  line-height: 1;
}

/* ==========================================================
   TARJETA QR – RECARGAR SALDO
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
   SWEETALERT2 – THEME EV (billetera)
   - Para que “Entendido/OK” mantenga el estilo EV
   - No afecta otros componentes
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

/* Botón principal (Entendido / Sí, confirmar) */
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

/* Botón cancelar (solo cuando tú lo uses en confirmaciones) */
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

/* FIX: ocultar Cancel SOLO cuando tú agregues la clase "ev-swal-nocancel" */
.ev-swal-nocancel .swal2-cancel{
  display: none !important;
}

/* ==========================================================
   RESPONSIVE
========================================================== */
@media (max-width: 575.98px){
  .ev-wallet-wrapper{
    padding-left: 12px !important;
    padding-right: 12px !important;
  }

  .ev-wallet-card{
    margin: 16px auto 28px auto;
  }

  .ev-wallet-card .card-body{
    padding: 18px 14px;
  }

  .ev-wallet-divider{
    margin-left: -14px;
    margin-right: -14px;
  }

  .ev-modal .modal-dialog{
    max-width: 100%;
    margin: 0 12px;
  }

  /* Body/footer modal como login (móvil) */
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

@media (max-width: 991.98px){
  .ev-wallet-qr-card{ margin-top: 8px; }
}
</style>

<style>
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
</style>