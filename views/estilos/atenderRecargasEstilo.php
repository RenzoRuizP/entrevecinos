<!-- views/estilos/atenderRecargasEstilo.php -->
<style>
/* =========================================================
   EV - ATENDER RECARGAS (Soporte) - ESTILO FINAL
   Header dentro de Card (estilo "Mi billetera")
========================================================= */

:root {
  --ev-verde-oscuro: #0F592F;
  --ev-verde: #16A34A;
  --ev-verde-claro: #bbf7d0;
  --ev-naranja: #EA7C12;
  --ev-naranja-oscuro: #C46B05;
  --ev-gris-050: #F9FAFB;
  --ev-gris-100: #F3F4F6;
  --ev-gris-200: #E5E7EB;
  --ev-gris-500: #6B7280;
  --ev-gris-600: #4B5563;
  --ev-texto: #111827;
}

/* Página */
.ev-recargas-page{
  color: var(--ev-texto);
}

/* Cards base */
.ev-card{
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid rgba(148,163,184,0.22);
  box-shadow: 0 14px 40px rgba(15, 23, 42, 0.10);
  overflow: hidden;
}

/* HERO CARD */
.ev-hero{
  background:
    radial-gradient(circle at 80% 20%, rgba(22,163,74,0.08), transparent 55%),
    radial-gradient(circle at 15% 80%, rgba(234,124,18,0.07), transparent 55%),
    #ffffff;
}
.ev-hero-body{
  padding: 18px 18px 14px;
}
.ev-hero-top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:16px;
  flex-wrap: wrap;
}
.ev-hero-right{
  display:flex;
  align-items:center;
  gap:10px;
}
.ev-hero-bottom{
  margin-top: 14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap: wrap;
}

/* Header texts */
.ev-recargas-title{
  font-size: 2.05rem;
  font-weight: 850;
  color: var(--ev-verde-oscuro);
  letter-spacing: 0.01em;
  margin: 0;
}
.ev-recargas-subtitle{
  color: var(--ev-gris-500);
  font-size: 0.95rem;
}

/* Botones */
.ev-btn-orange{
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border: none;
  color: #ffffff;
  border-radius: 14px;
  padding: 10px 18px;
  box-shadow: 0 12px 26px rgba(234,124,18,0.35);
  transition: all 0.2s ease;
  font-weight: 750;
}
.ev-btn-orange:hover{
  background: linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C);
  color: #ffffff;
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(234,124,18,0.48);
}
.ev-btn-orange:active{
  transform: translateY(0);
  box-shadow: 0 6px 16px rgba(234,124,18,0.30);
}

.ev-btn-light{
  border-radius: 999px;
  border: 1px solid var(--ev-gris-200);
  background: rgba(255,255,255,0.80);
  color: var(--ev-verde-oscuro);
  font-weight: 750;
  transition: all 0.18s ease;
}
.ev-btn-light:hover{
  background: #ECFDF5;
  border-color: rgba(22,163,74,0.35);
}

.ev-btn-outline{
  border-radius: 999px;
  border: 1px solid #D1D5DB;
  background: #fff;
  color: #4B5563;
  font-weight: 750;
}
.ev-btn-outline:hover{
  background: #F3F4F6;
  color: #111827;
}

.ev-btn-success{
  border-radius: 999px;
  border: none;
  color: #fff;
  font-weight: 850;
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  box-shadow: 0 10px 22px rgba(22,163,74,0.35);
}
.ev-btn-success:hover{
  background: linear-gradient(135deg, #15803D 0%, #16A34A 100%);
  box-shadow: 0 12px 26px rgba(22,163,74,0.45);
}

.ev-btn-danger{
  border-radius: 999px;
  border: none;
  color: #fff;
  font-weight: 850;
  background: linear-gradient(135deg, #EF4444 0%, #F97316 100%);
  box-shadow: 0 10px 22px rgba(239,68,68,0.25);
}

.ev-btn-soft{
  border-radius: 999px;
  border: 1px solid rgba(234,124,18,0.35);
  background: rgba(234,124,18,0.10);
  color: #92400E;
  font-weight: 850;
}

/* Icon button */
.ev-icon-btn{
  width: 42px;
  height: 42px;
  border-radius: 14px;
  border: 1px solid var(--ev-gris-200);
  background: #fff;
  display: grid;
  place-items: center;
  color: var(--ev-verde-oscuro);
  transition: all 0.18s ease;
}
.ev-icon-btn:hover{
  background: #ECFDF5;
  border-color: rgba(22,163,74,0.35);
}

/* Summary pill */
.ev-summary-pill{
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 16px;
  background: linear-gradient(90deg, rgba(187,247,208,0.55), rgba(187,247,208,0.20));
  border: 1px solid rgba(22,163,74,0.20);
}
.ev-summary-label{
  color: #14532D;
  font-weight: 850;
}
.ev-summary-count{
  background: rgba(255,255,255,0.90);
  border: 1px solid rgba(22,163,74,0.18);
  padding: 2px 10px;
  border-radius: 999px;
  font-weight: 900;
  color: var(--ev-verde-oscuro);
  min-width: 34px;
  text-align: center;
}

/* Quick actions */
.ev-quick-actions{
  display:flex;
  gap:8px;
  flex-wrap: wrap;
}

/* Section cards */
.ev-card-header{
  padding: 14px 16px;
  border-bottom: 1px solid var(--ev-gris-200);
  background: #ffffff;
}
.ev-card-header-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}
.ev-card-title{
  margin: 0;
  font-size: 1.05rem;
  font-weight: 900;
  color: var(--ev-verde-oscuro);
}
.ev-table-meta{
  color: var(--ev-gris-500);
  font-size: 0.88rem;
}
.ev-card-body{
  padding: 16px;
}
.ev-card-footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding: 12px 16px;
  border-top: 1px solid var(--ev-gris-200);
  background: #ffffff;
}
.ev-footer-left{
  color: var(--ev-gris-500);
  font-weight: 650;
  font-size: 0.9rem;
}
.ev-footer-right{
  display:flex;
  align-items:center;
  gap:10px;
}
.ev-page-pill{
  display:inline-flex;
  min-width: 42px;
  justify-content:center;
  padding: 6px 12px;
  border-radius: 12px;
  border: 1px solid var(--ev-gris-200);
  background: #fff;
  font-weight: 900;
  color: var(--ev-verde-oscuro);
}

/* Inputs */
.ev-input-icon{
  position:absolute;
  top:50%;
  left:14px;
  transform: translateY(-50%);
  color:#9ca3af;
}
.ev-input{
  border-radius: 12px;
  border: 1px solid var(--ev-verde-claro);
  transition: all 0.18s ease-out;
  box-shadow: 0 0 0 0 rgba(22,163,74,0);
}
.ev-input:focus{
  border-color: var(--ev-verde);
  box-shadow: 0 0 0 3px rgba(22,163,74,0.18);
  outline: none;
}

/* Table */
.ev-table thead th{
  background: var(--ev-gris-050);
  color: #374151;
  font-weight: 900;
  border-bottom: 1px solid var(--ev-gris-200) !important;
}
.ev-table tbody td{
  vertical-align: middle;
  border-color: var(--ev-gris-200);
}

/* =========================================================
   NUEVO: Método (ícono Yape/Plin) - SOLO ICONO
   - No rompe nada: si no cargan imágenes, cae a texto
========================================================= */
.ev-metodo{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width: 44px;
  height: 32px;
  border-radius: 12px;
  border: 1px solid rgba(148,163,184,0.22);
  background: #fff;
  overflow:hidden;
}
.ev-metodo img{
  width: 28px;
  height: 28px;
  object-fit: contain;
  display:block;
}
.ev-metodo-yape{
  background: rgba(168, 85, 247, 0.10);
  border-color: rgba(168, 85, 247, 0.25);
}
.ev-metodo-plin{
  background: rgba(34, 197, 94, 0.10);
  border-color: rgba(34, 197, 94, 0.25);
}
.ev-metodo-fallback{
  font-weight: 900;
  font-size: 0.78rem;
  color: var(--ev-gris-600);
}

/* Empty state */
.ev-empty{
  color: var(--ev-gris-500);
  font-weight: 700;
}
.ev-empty-wrap{
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:8px;
}
.ev-empty-ico{
  font-size: 1.6rem;
  color: rgba(15,89,47,0.35);
}
.ev-empty-text{
  color: var(--ev-gris-600);
  font-weight: 700;
}

/* Badges */
.ev-badge{
  display:inline-flex;
  align-items:center;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.82rem;
  font-weight: 900;
  border: 1px solid transparent;
}
.ev-badge-pendiente{
  background: rgba(234,124,18,0.12);
  border-color: rgba(234,124,18,0.25);
  color: #92400E;
}
.ev-badge-observada{
  background: rgba(245,158,11,0.14);
  border-color: rgba(245,158,11,0.30);
  color: #92400E;
}
.ev-badge-aprobada{
  background: rgba(22,163,74,0.12);
  border-color: rgba(22,163,74,0.25);
  color: #14532D;
}
.ev-badge-rechazada{
  background: rgba(239,68,68,0.12);
  border-color: rgba(239,68,68,0.22);
  color: #7F1D1D;
}

/* Modal EV */
.ev-modal{
  border-radius: 18px;
  border: none;
  overflow: hidden;
  background: transparent;
  box-shadow: 0 18px 45px rgba(0,0,0,0.22), 0 6px 12px rgba(0,0,0,0.12);
}
.ev-modal-header{
  background: linear-gradient(140deg, #0F592F 0%, #0E7A43 55%, #16A34A 100%);
  padding: 16px 18px;
  border-bottom: 1px solid rgba(255,255,255,0.20);
}
.ev-modal-header .modal-title{
  font-weight: 850;
  font-size: 1rem;
  color: #ffffff;
}
.ev-modal-body{
  background: #ffffff;
  padding: 1.4rem 1.6rem;
  box-shadow: inset 0 1px 0 rgba(0,0,0,0.06);
}
.ev-modal-footer{
  background: #ffffff;
  border-top: 1px solid var(--ev-gris-200);
  padding: 12px 16px;
}

/* Modal blocks */
.ev-kv{
  border: 1px solid rgba(148,163,184,0.22);
  border-radius: 16px;
  padding: 12px 14px;
  background: #ffffff;
}
.ev-kv-item{
  display:flex;
  justify-content:space-between;
  gap:10px;
  padding: 6px 0;
  border-bottom: 1px dashed rgba(229,231,235,0.9);
}
.ev-kv-item:last-child{ border-bottom: none; }
.ev-kv-item span{
  color: var(--ev-gris-500);
  font-weight: 700;
}

.ev-proof{
  border-radius: 16px;
  border: 1px solid rgba(148,163,184,0.22);
  overflow:hidden;
  background:#fff;
}
.ev-proof-title{
  padding: 12px 14px;
  font-weight: 900;
  color: var(--ev-verde-oscuro);
  background: var(--ev-gris-050);
  border-bottom: 1px solid var(--ev-gris-200);
}
.ev-proof-box{
  padding: 12px;
  display:grid;
  place-items:center;
  min-height: 300px;
}
.ev-proof-empty{
  color: var(--ev-gris-500);
  font-weight: 700;
}
.ev-proof-hint{
  padding: 10px 14px 14px;
  color: var(--ev-gris-500);
  font-size: 0.88rem;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 576px){
  .ev-recargas-title{ font-size: 1.65rem; }
  .ev-quick-actions .btn{ width: 100%; }
  .ev-hero-body{ padding: 16px 14px 12px; }
}
</style>
