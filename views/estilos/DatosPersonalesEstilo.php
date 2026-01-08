<?php /* views/estilos/DatosPersonalesEstilo.php — UX/UI Mi Perfil (EV) */ ?>
<style>
:root{
  /* Tokens EV (hereda si ya existen en estilos globales) */
  --ev-verde-oscuro: var(--verde-oscuro, #0F592F);
  --ev-verde:        var(--verde-claro,  #198754);
  --ev-naranja:      var(--naranja,      #EA7C12);

  --ev-fondo:        var(--gris-claro,   #F3F4F6);
  --ev-borde:        var(--gris-borde,   #E5E7EB);

  --ev-texto:        #1A1F36;
  --ev-texto-suave:  #6B7280;

  --ev-card:         #FFFFFF;
  --ev-shadow:       0 16px 40px rgba(16, 24, 40, .12);
  --ev-shadow-soft:  0 10px 24px rgba(16, 24, 40, .10);

  --ev-radius:       18px;
  --ev-radius-sm:    14px;

  --ev-ring:         0 0 0 4px rgba(25, 135, 84, .18);
  --ev-ring-orange:  0 0 0 4px rgba(234, 124, 18, .18);
}

.container-datos-personales{
  padding: 14px 14px 28px;
}

/* Card principal */
.ev-datos-card{
  border-radius: var(--ev-radius);
  background: var(--ev-card);
  box-shadow: var(--ev-shadow);
  overflow: hidden;
  max-width: 1100px;
  margin: 0 auto;
}

/* Header */
.ev-datos-card .card-header{
  border: 0;
  padding: 16px 18px;
  color: #fff;
  background: linear-gradient(90deg, rgba(15,89,47,1) 0%, rgba(25,135,84,1) 55%, rgba(234,124,18,.80) 120%);
}

.ev-datos-icon{
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,.16);
  box-shadow: 0 10px 20px rgba(0,0,0,.10);
  backdrop-filter: blur(6px);
}

.ev-datos-icon i{
  font-size: 18px;
  color: #fff;
}

.ev-datos-card .card-header h5{
  margin: 0;
  font-weight: 800;
  letter-spacing: .2px;
}

.ev-datos-subtitle{
  display: inline-block;
  color: rgba(255,255,255,.90);
  font-weight: 500;
}

/* Body */
.ev-datos-card .card-body{
  background: #fff;
}

/* Labels e inputs */
.ev-form-label{
  font-weight: 700;
  color: var(--ev-texto);
  font-size: .92rem;
  margin-bottom: 6px;
}

.ev-input-rounded{
  border-radius: var(--ev-radius-sm) !important;
  border: 1px solid var(--ev-borde) !important;
  background: #fff;
  color: var(--ev-texto);
  padding: 11px 12px;
  transition: box-shadow .15s ease, border-color .15s ease, transform .08s ease;
}

.ev-input-rounded:focus{
  outline: none;
  border-color: rgba(25,135,84,.55) !important;
  box-shadow: var(--ev-ring) !important;
}

.ev-input-rounded:disabled,
.ev-input-rounded[disabled]{
  background: #F9FAFB !important;
  color: #6B7280 !important;
  cursor: not-allowed;
}

/* Selects */
.form-select.ev-input-rounded{
  padding-right: 34px;
}

/* Hint box */
.ev-hint{
  border: 1px solid var(--ev-borde);
  background: #F8FAFC;
  color: var(--ev-texto);
  border-radius: var(--ev-radius-sm);
  padding: 10px 12px;
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-weight: 600;
}

.ev-hint i{
  color: var(--ev-naranja);
  margin-top: 2px;
}

/* Stepper */
.ev-stepper{
  width: 100%;
  border-radius: var(--ev-radius-sm);
  background: #FFFFFF;
  border: 1px solid var(--ev-borde);
  box-shadow: var(--ev-shadow-soft);
  padding: 10px 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  overflow-x: auto;
}

.ev-stepper::-webkit-scrollbar{ height: 6px; }
.ev-stepper::-webkit-scrollbar-thumb{ background: rgba(17,92,65,.25); border-radius: 999px; }

.ev-step{
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 999px;
  cursor: pointer;
  user-select: none;
  transition: background .15s ease, transform .08s ease;
  white-space: nowrap;
}

.ev-step:hover{ transform: translateY(-1px); }

.ev-step-dot{
  width: 26px;
  height: 26px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: .85rem;
  background: #F3F4F6;
  color: #6B7280;
  border: 1px solid var(--ev-borde);
}

.ev-step-label{
  font-weight: 800;
  color: #374151;
  font-size: .92rem;
}

.ev-step-line{
  height: 2px;
  flex: 1 1 auto;
  min-width: 40px;
  background: rgba(17,92,65,.18);
  border-radius: 999px;
}

.ev-step.active{
  background: rgba(25,135,84,.10);
}

.ev-step.active .ev-step-dot{
  background: rgba(234,124,18,.12);
  border-color: rgba(234,124,18,.35);
  color: var(--ev-naranja);
}

.ev-step.active .ev-step-label{
  color: var(--ev-verde-oscuro);
}

.ev-step.done .ev-step-dot{
  background: rgba(25,135,84,.12);
  border-color: rgba(25,135,84,.35);
  color: var(--ev-verde);
}

/* Wizard footer */
.ev-wizard-footer{
  border-top: 1px solid var(--ev-borde);
  padding-top: 16px;
  margin-top: 18px;
}

/* Buttons */
.btn-ev-primary{
  background: linear-gradient(90deg, rgba(15,89,47,1) 0%, rgba(25,135,84,1) 70%);
  border: 0;
  color: #fff;
  border-radius: 999px;
  padding: 10px 18px;
  font-weight: 800;
  box-shadow: 0 10px 20px rgba(15,89,47,.22);
  transition: transform .08s ease, box-shadow .15s ease, opacity .15s ease;
}

.btn-ev-primary:hover{
  opacity: .98;
  box-shadow: 0 14px 28px rgba(15,89,47,.26);
  transform: translateY(-1px);
}

.btn-ev-primary:active{
  transform: translateY(0);
}

.btn-ev-primary.saving{
  opacity: .78;
  cursor: wait;
}

.btn-ev-neutral{
  background: #fff;
  border: 1px solid var(--ev-borde);
  color: #111827;
  border-radius: 999px;
  padding: 10px 16px;
  font-weight: 800;
  transition: box-shadow .15s ease, transform .08s ease, border-color .15s ease;
}

.btn-ev-neutral:hover{
  box-shadow: 0 12px 22px rgba(16,24,40,.08);
  transform: translateY(-1px);
  border-color: rgba(17,92,65,.25);
}

/* File row (comprobante) */
.ev-file-row{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  border: 1px dashed rgba(17,92,65,.30);
  background: rgba(25,135,84,.06);
  padding: 10px 12px;
  border-radius: var(--ev-radius-sm);
}

.ev-file-info{
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.ev-file-info i{
  color: var(--ev-verde);
  font-size: 18px;
}

.ev-file-info a{
  font-weight: 800;
  color: var(--ev-verde-oscuro);
  text-decoration: none;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 520px;
}

.ev-file-info a:hover{
  text-decoration: underline;
}

#dpComprobantePath{
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 520px;
}

/* Animación suave */
.fade-in{
  animation: evFadeIn .25s ease-out both;
}
@keyframes evFadeIn{
  from{ opacity:0; transform: translateY(6px); }
  to{ opacity:1; transform: translateY(0); }
}

/* Responsive */
@media (max-width: 992px){
  .ev-datos-card{ max-width: 980px; }
  .ev-file-info a, #dpComprobantePath{ max-width: 360px; }
}

@media (max-width: 576px){
  .container-datos-personales{ padding: 10px 10px 22px; }
  .ev-datos-card .card-body{ padding: 16px !important; }
  .ev-stepper{ padding: 10px; }
  .ev-step{ padding: 10px 10px; }
  .ev-file-row{ flex-direction: column; align-items: flex-start; }
  .ev-file-info a, #dpComprobantePath{ max-width: 100%; }
  .ev-wizard-footer .d-flex{ gap: 10px !important; }
  .btn-ev-primary, .btn-ev-neutral{ width: auto; }
}
</style>
