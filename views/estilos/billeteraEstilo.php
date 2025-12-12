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
}

/* Fondo suave alineado al dashboard / datos personales */
.ev-wallet-wrapper{
  background-color: var(--ev-gris-fondo);
  max-width: 1100px;
  margin: 0 auto;
}

/* Card principal */
.ev-wallet-card{
  border-radius: 18px;
  border: 1px solid var(--ev-gris-borde);
  background: #ffffff;
  box-shadow: 0 14px 40px rgba(15, 23, 42, 0.14);
  margin: 24px auto 40px auto;
  overflow: hidden;
}

.ev-wallet-card .card-body{
  padding: 24px 32px;
}

/* Header billetera */
.ev-wallet-header{
  min-height: 56px;
}

/* Título */
.ev-wallet-title{
  font-size: 1.65rem;
  font-weight: 700;
  color: var(--ev-verde-oscuro);
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
}

.ev-wallet-divider{
  border-top: 1px solid rgba(148, 163, 184, 0.35);
  margin-left: -32px;
  margin-right: -32px;
}

/* Badge de saldo */
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
}

.ev-wallet-badge-amount{
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--ev-verde-oscuro);
}

/* Estado vacío */
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

/* Movimientos */
.ev-wallet-movimientos{
  margin-top: 12px;
}

.ev-wallet-table thead th{
  border-bottom: 1px solid var(--ev-gris-borde);
  font-weight: 600;
  color: var(--ev-texto-suave);
  text-transform: uppercase;
  font-size: 0.78rem;
}

.ev-wallet-table tbody tr:hover{
  background-color: #F9FAFB;
}

.ev-wallet-mov-titulo{
  font-weight: 600;
  color: var(--ev-texto);
}

.ev-wallet-monto--credito{
  color: var(--ev-verde);
}

.ev-wallet-monto--debito{
  color: var(--ev-rojo);
}

/* ==========================================================
   BOTONES EV
========================================================== */

/* CTA naranja tipo "Llamar ahora" */
.btn-ev-orange{
  background-image: linear-gradient(180deg, #FF9B3A, #FF7A1A);
  border: none;
  color: #ffffff;
  font-weight: 600;
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
  font-weight: 500;
  padding: 0.45rem 1.4rem;
  font-size: 0.93rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-ev-outline:hover{
  background-color: #F9FAFB;
}

/* Botón Cerrar con X circular */
.ev-btn-cerrar i{
  width: 22px;
  height: 22px;
  border-radius: 999px;
  border: 1px solid #D1D5DB;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.82rem;
}

/* ==========================================================
   MODALES EV – UNIFICADOS CON "RECUPERAR CUENTA"
========================================================== */
.ev-modal .modal-dialog{
  max-width: 720px;
}

.ev-modal-content{
  border-radius: 22px;
  overflow: hidden;
  border: 0;
  box-shadow: 0 22px 60px rgba(15, 23, 42, 0.35);
}

/* HEADER del modal */
.ev-modal-header{
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background-color: #0F592F;   /* mismo verde sólido que login */
  color: #ffffff;
}

/* Título */
.ev-modal-title{
  font-size: 1.1rem;
  font-weight: 600;
  color: #ffffff !important;
  display: flex;
  align-items: center;
  gap: 8px;
}

.ev-modal-title i{
  color: #ffffff;
}

/* X del header (btn-close) pequeña y alineada */
.ev-modal-close-icon{
  opacity: 1;
  transform: translateY(1px);
}

/* BODY */
.ev-modal-body{
  padding: 22px 26px;
  background-color: #ffffff;
}

/* FOOTER */
.ev-modal-footer{
  padding: 14px 26px 20px 26px;
  background-color: #ffffff;
  border-top: 1px solid rgba(229, 231, 235, 0.9);
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

/* TARJETA DE SOPORTE */
.ev-support-card{
  border-radius: 16px;
  border: 1px solid var(--ev-gris-borde);
  background-color: #F9FAFB;
  padding: 16px 20px;
  max-width: 360px;
}

.ev-support-title{
  font-weight: 600;
  color: var(--ev-texto);
}

.ev-support-subtitle{
  color: var(--ev-texto-suave);
}

.ev-support-icon{
  color: var(--ev-verde);
  margin-right: 4px;
}

.ev-support-phone{
  font-weight: 600;
  color: var(--ev-verde-oscuro);
}

/* ==========================================================
   TARJETA QR – RECARGAR SALDO
========================================================== */
.ev-wallet-qr-card{
  border-radius: 16px;
  border: 1px solid var(--ev-gris-borde);
  background-color: #F9FAFB;
  padding: 16px 16px 18px 16px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
  margin-right: 4px;
}

.ev-wallet-qr-img{
  max-width: 210px;
  width: 100%;
  border-radius: 14px;
  display: block;
  margin: 0 auto 10px auto;
}

.ev-wallet-qr-title{
  font-weight: 600;
  color: var(--ev-texto);
  font-size: 0.98rem;
  margin-top: 8px;
}

.ev-wallet-qr-text{
  font-size: 0.9rem;
  color: var(--ev-texto-suave);
  line-height: 1.35;
}

/* RESPONSIVE */
@media (max-width: 575.98px){
  .ev-wallet-wrapper{
    padding-left: 12px !important;
    padding-right: 12px !important;
  }

  .ev-wallet-card .card-body{
    padding: 18px 14px;
  }

  .ev-modal .modal-dialog{
    max-width: 100%;
    margin: 0 12px;
  }
}

@media (max-width: 991.98px){
  .ev-wallet-qr-card{
    margin-top: 8px;
  }
}
</style>
