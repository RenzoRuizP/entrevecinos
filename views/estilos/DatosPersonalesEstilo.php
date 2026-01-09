<?php /* views/estilos/DatosPersonalesEstilo.php — UX/UI Mi Perfil (armonizado con Mis Productos) */ ?>
<style>
/* ===================================================
   TOKENS EV (mantener consistencia)
=================================================== */
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#16A34A;
  --ev-verde-claro:#bbf7d0;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;

  --ev-gris-050:#F9FAFB;
  --ev-gris-100:#F3F4F6;
  --ev-gris-200:#E5E7EB;
  --ev-gris-500:#6B7280;
  --ev-gris-700:#374151;

  --ev-shadow-soft: 0 14px 30px rgba(15,23,42,0.06), 0 2px 8px rgba(15,23,42,0.04);
  --ev-shadow-cta: 0 12px 26px rgba(234,124,18,0.35);
}

/* ===================================================
   WRAPPER
=================================================== */
.container-datos-personales{
  padding: 18px 10px 26px;
}

.ev-datos-card{
  border-radius: 18px !important;
  overflow: hidden;
  background: #fff;
  box-shadow: var(--ev-shadow-soft);
}

/* ===================================================
   HEADER (blanco como "Mis Productos")
=================================================== */
.ev-datos-card .card-header{
  background: #ffffff !important;
  border-bottom: 1px solid rgba(229,231,235,0.95) !important;
  padding: 18px 18px !important;
}

.ev-datos-icon{
  width: 40px;
  height: 40px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(22,163,74,0.10);
  border: 1px solid rgba(209,250,229,0.9);
  box-shadow: 0 8px 18px rgba(15,23,42,0.06);
  flex: 0 0 auto;
}
.ev-datos-icon i{
  color: var(--ev-verde-oscuro);
  font-size: 1.05rem;
  line-height: 1;
}

.ev-datos-card .card-header h5{
  margin: 0;
  font-weight: 800;
  font-size: 1.55rem;      /* similar sensación "Mis Productos" */
  color: #0B1F13;
  letter-spacing: 0.01em;
}

.ev-datos-subtitle{
  display: block;
  margin-top: 2px;
  color: var(--ev-gris-500);
  font-size: 0.92rem;
  line-height: 1.3;
}

/* Body */
.ev-datos-card .card-body{
  background: linear-gradient(180deg, #FFFFFF 0%, #FAFBFC 100%);
  padding: 18px 18px 16px !important;
}

/* ===================================================
   STEPPER (premium pill)
=================================================== */
.ev-stepper{
  background: rgba(15, 89, 47, 0.04);
  border: 1px solid rgba(229,231,235,0.95);
  border-radius: 16px;
  padding: 12px 12px;
  box-shadow: 0 10px 22px rgba(15,23,42,0.04);
  display: flex;
  align-items: center;
  gap: 12px;
}

.ev-step{
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 999px;
  background: #ffffff;
  border: 1px solid rgba(229,231,235,0.95);
  cursor: pointer;
  user-select: none;
  transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
  box-shadow: 0 10px 22px rgba(15,23,42,0.04);
  min-width: 160px;
  justify-content: center;
}

.ev-step:hover{
  transform: translateY(-1px);
  border-color: rgba(22,163,74,0.28);
  box-shadow: 0 14px 28px rgba(15,23,42,0.06);
}

.ev-step-dot{
  width: 26px;
  height: 26px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.86rem;
  background: #E5E7EB;
  color: #374151;
  border: 1px solid rgba(209,213,219,0.9);
}

.ev-step-label{
  font-weight: 700;
  color: #111827;
  font-size: 0.95rem;
}

/* active + done */
.ev-step.active{
  border-color: rgba(22,163,74,0.45);
  background: rgba(22,163,74,0.08);
}
.ev-step.active .ev-step-dot{
  background: rgba(22,163,74,0.12);
  border-color: rgba(22,163,74,0.35);
  color: var(--ev-verde-oscuro);
}

.ev-step.done{
  border-color: rgba(22,163,74,0.28);
}
.ev-step.done .ev-step-dot{
  background: rgba(22,163,74,0.14);
  color: var(--ev-verde-oscuro);
  border-color: rgba(22,163,74,0.30);
}

.ev-step-line{
  flex: 1 1 auto;
  height: 2px;
  background: rgba(15,89,47,0.12);
  border-radius: 999px;
}

/* ===================================================
   PANEL (card interno tipo Mis Productos)
=================================================== */
.ev-step-panel{
  background: #ffffff;
  border: 1px solid rgba(229,231,235,0.95);
  border-radius: 16px;
  padding: 16px 16px;
  box-shadow: var(--ev-shadow-soft);
}

/* Labels e inputs */
.ev-form-label{
  font-weight: 700;
  font-size: 0.92rem;
  color: var(--ev-gris-700);
  margin-bottom: 8px;
}

.ev-input-rounded{
  border-radius: 12px !important;
  border: 1px solid #D1FAE5 !important;
  height: 46px;
  padding-left: 14px;
  padding-right: 14px;
  font-size: 0.95rem;
  transition: all .18s ease-out;
  background: #fff;
}

.ev-input-rounded:focus{
  border-color: var(--ev-verde) !important;
  box-shadow: 0 0 0 4px rgba(22,163,74,0.18) !important;
  outline: none !important;
}

.ev-step-panel input[disabled],
.ev-step-panel select[disabled]{
  background: #F9FAFB !important;
  color: #6B7280 !important;
}

/* Hint */
.ev-hint{
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid rgba(229,231,235,0.95);
  background: #F9FAFB;
  color: #111827;
}
.ev-hint i{ color: var(--ev-naranja); margin-top: 2px; }

/* File row */
.ev-file-row{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid rgba(229,231,235,0.95);
  background: #FFFFFF;
  box-shadow: 0 10px 22px rgba(15,23,42,0.04);
}
.ev-file-info{
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.ev-file-info i{ color: var(--ev-verde-oscuro); }

/* ===================================================
   FOOTER / BOTONES
=================================================== */
.ev-wizard-footer{
  padding: 14px 4px 2px;
  border-top: 1px solid rgba(229,231,235,0.95);
  margin-top: 14px;
}

.btn-ev-neutral{
  background: #ffffff !important;
  border: 1px solid rgba(209,213,219,0.95) !important;
  color: #111827 !important;
  border-radius: 999px !important;
  padding: 10px 18px !important;
  box-shadow: 0 10px 22px rgba(15,23,42,0.05);
}
.btn-ev-neutral:hover{
  background: #F3F4F6 !important;
}

.btn-ev-primary{
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B) !important;
  border: none !important;
  color: #ffffff !important;
  border-radius: 999px !important;
  padding: 10px 22px !important;
  box-shadow: var(--ev-shadow-cta) !important;
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}
.btn-ev-primary:hover{
  background: linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C) !important;
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(234,124,18,0.48) !important;
}
.btn-ev-primary:active{
  transform: translateY(0);
  box-shadow: 0 6px 16px rgba(234,124,18,0.30) !important;
}
.btn-ev-primary.saving{
  opacity: .92;
  pointer-events: none;
}

/* ===================================================
   RESPONSIVO
=================================================== */
@media (max-width: 992px){
  .ev-step{ min-width: 140px; }
}

@media (max-width: 768px){
  .container-datos-personales{ padding: 12px 8px 18px; }
  .ev-datos-card .card-header{ padding: 16px 14px !important; }
  .ev-datos-card .card-body{ padding: 14px 14px 12px !important; }

  .ev-stepper{
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }
  .ev-step-line{ display: none; }
  .ev-step{ width: 100%; justify-content: flex-start; }
}

@media (max-width: 576px){
  .ev-datos-card .card-header h5{ font-size: 1.3rem; }
  .ev-step-panel{ padding: 14px 12px; }
  .btn-ev-neutral, .btn-ev-primary{ width: auto; }
}
</style>
