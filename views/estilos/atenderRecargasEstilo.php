<!-- views/estilos/atenderRecargasEstilo.php -->
<style>
/* =========================================================
   EV - ATENDER RECARGAS (Soporte)
   Versión corregida: modal robusto para comprobantes grandes
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
  --ev-gris-300: #D1D5DB;
  --ev-gris-500: #6B7280;
  --ev-gris-600: #4B5563;
  --ev-texto: #111827;
  --ev-rojo: #EF4444;
}

.ev-recargas-page{ color: var(--ev-texto); }

.ev-card{
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid rgba(148,163,184,0.22);
  box-shadow: 0 14px 40px rgba(15, 23, 42, 0.10);
  overflow: hidden;
}

.ev-hero{
  background:
    radial-gradient(circle at 80% 20%, rgba(22,163,74,0.08), transparent 55%),
    radial-gradient(circle at 15% 80%, rgba(234,124,18,0.07), transparent 55%),
    #ffffff;
}
.ev-hero-body{ padding: 18px 18px 14px; }
.ev-hero-top{
  display:flex; align-items:flex-start; justify-content:space-between;
  gap:16px; flex-wrap: wrap;
}
.ev-hero-right{ display:flex; align-items:center; gap:10px; }
.ev-hero-bottom{
  margin-top: 14px;
  display:flex; align-items:center; justify-content:space-between;
  gap:12px; flex-wrap: wrap;
}

.ev-recargas-title{
  font-size: 2.05rem;
  font-weight: 850;
  color: var(--ev-verde-oscuro);
  letter-spacing: -0.02em;
  margin: 0;
}
.ev-recargas-subtitle{ color: var(--ev-gris-500); font-size: 0.95rem; }

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
.ev-btn-orange:active{ transform: translateY(0); box-shadow: 0 6px 16px rgba(234,124,18,0.30); }

.ev-btn-light{
  border-radius: 999px;
  border: 1px solid var(--ev-gris-200);
  background: rgba(255,255,255,0.90);
  color: var(--ev-verde-oscuro);
  font-weight: 750;
  transition: all 0.18s ease;
}
.ev-btn-light:hover{ background: #ECFDF5; border-color: rgba(22,163,74,0.35); color: var(--ev-verde-oscuro); }

.ev-btn-outline{
  border-radius: 999px;
  border: 1px solid #D1D5DB;
  background: #fff;
  color: #4B5563;
  font-weight: 750;
  padding: .55rem 1rem;
}
.ev-btn-outline:hover{ background: #F3F4F6; color: #111827; }

.ev-btn-success{
  border-radius: 999px;
  border: none;
  color: #fff;
  font-weight: 850;
  background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
  box-shadow: 0 10px 22px rgba(22,163,74,0.35);
  padding: .55rem 1rem;
}
.ev-btn-success:hover{
  color:#fff;
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
  padding: .55rem 1rem;
}
.ev-btn-danger:hover{ color:#fff; filter:brightness(1.03); }

.ev-btn-soft{
  border-radius: 999px;
  border: 1px solid rgba(234,124,18,0.35);
  background: rgba(234,124,18,0.10);
  color: #92400E;
  font-weight: 850;
  padding: .55rem 1rem;
}
.ev-btn-soft:hover{ background: rgba(234,124,18,0.15); color:#92400E; }

.ev-icon-btn{
  width: 42px; height: 42px;
  border-radius: 14px;
  border: 1px solid var(--ev-gris-200);
  background: #fff;
  display: grid; place-items: center;
  color: var(--ev-verde-oscuro);
  transition: all 0.18s ease;
}
.ev-icon-btn:hover{ background: #ECFDF5; border-color: rgba(22,163,74,0.35); }

.ev-summary-pill{
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 16px;
  background: linear-gradient(90deg, rgba(187,247,208,0.55), rgba(187,247,208,0.20));
  border: 1px solid rgba(22,163,74,0.20);
}
.ev-summary-label{ color: #14532D; font-weight: 850; }
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
.ev-quick-actions{ display:flex; gap:8px; flex-wrap: wrap; }

.ev-card-header{
  padding: 14px 16px;
  border-bottom: 1px solid var(--ev-gris-200);
  background: #ffffff;
}
.ev-card-header-row{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
.ev-card-title{ margin: 0; font-size: 1.05rem; font-weight: 900; color: var(--ev-verde-oscuro); }
.ev-table-meta{ color: var(--ev-gris-500); font-size: 0.88rem; }
.ev-card-body{ padding: 16px; }
.ev-card-footer{
  display:flex; align-items:center; justify-content:space-between;
  padding: 12px 16px;
  border-top: 1px solid var(--ev-gris-200);
  background: #ffffff;
}
.ev-footer-left{ color: var(--ev-gris-500); font-weight: 650; font-size: 0.9rem; }
.ev-footer-right{ display:flex; align-items:center; gap:10px; }
.ev-page-pill{
  display:inline-flex; min-width: 42px; justify-content:center;
  padding: 6px 12px; border-radius: 12px;
  border: 1px solid var(--ev-gris-200);
  background: #fff; font-weight: 900; color: var(--ev-verde-oscuro);
}

.ev-input-icon{ position:absolute; top:50%; left:14px; transform: translateY(-50%); color:#9ca3af; }
.ev-input{
  border-radius: 12px;
  border: 1px solid var(--ev-verde-claro);
  transition: all 0.18s ease-out;
  box-shadow: 0 0 0 0 rgba(22,163,74,0);
}
.ev-input:focus{ border-color: var(--ev-verde); box-shadow: 0 0 0 3px rgba(22,163,74,0.18); outline: none; }

.ev-table-wrap{ padding: 0 14px 14px; background: #fff; }
.ev-table{ width: 100%; }
.ev-table thead th{
  background: var(--ev-gris-050);
  color: #374151;
  font-weight: 900;
  border-bottom: 1px solid var(--ev-gris-200) !important;
  white-space: nowrap;
}
.ev-table tbody td{ vertical-align: middle; border-color: var(--ev-gris-200); padding-top: 12px; padding-bottom: 12px; }
.ev-col-fecha{ width: 170px; }
.ev-col-monto{ width: 140px; }
.ev-col-metodo{ width: 90px; }
.ev-col-operacion{ width: 140px; }
.ev-col-estado{ width: 130px; }
.ev-col-acciones{ width: 130px; }
.ev-col-usuario{ width: auto; min-width: 260px; max-width: 420px; }
.ev-td-usuario{ max-width: 420px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ev-fecha{ display:flex; flex-direction:column; line-height: 1.1; }
.ev-fecha .ev-fecha-dia{ font-weight: 900; color: #111827; }
.ev-fecha .ev-fecha-hora{ font-size: 0.78rem; color: var(--ev-gris-500); margin-top: 4px; }
.ev-mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-weight: 800; font-size: 0.92rem; }

.ev-metodo{ display:inline-flex; align-items:center; justify-content:center; width: 44px; height: 32px; border-radius: 12px; border: 1px solid rgba(148,163,184,0.22); background: #fff; overflow:hidden; }
.ev-metodo img{ width: 28px; height: 28px; object-fit: contain; display:block; }
.ev-metodo-yape{ background: rgba(168, 85, 247, 0.10); border-color: rgba(168, 85, 247, 0.25); }
.ev-metodo-plin{ background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.25); }
.ev-metodo-fallback{ font-weight: 900; font-size: 0.78rem; color: var(--ev-gris-600); }

.ev-empty{ color: var(--ev-gris-500); font-weight: 700; }
.ev-empty-wrap{ display:flex; flex-direction:column; align-items:center; gap:8px; }
.ev-empty-ico{ font-size: 1.6rem; color: rgba(15,89,47,0.35); }
.ev-empty-text{ color: var(--ev-gris-600); font-weight: 700; }

.ev-badge{
  display:inline-flex; align-items:center;
  padding: 4px 10px; border-radius: 999px;
  font-size: 0.82rem; font-weight: 900;
  border: 1px solid transparent;
  text-transform: lowercase;
}
.ev-badge-pendiente{ background: rgba(234,124,18,0.12); border-color: rgba(234,124,18,0.25); color: #92400E; }
.ev-badge-observada{ background: rgba(245,158,11,0.14); border-color: rgba(245,158,11,0.30); color: #92400E; }
.ev-badge-aprobada{ background: rgba(22,163,74,0.12); border-color: rgba(22,163,74,0.25); color: #14532D; }
.ev-badge-rechazada{ background: rgba(239,68,68,0.12); border-color: rgba(239,68,68,0.22); color: #7F1D1D; }
.ev-badge-reenviada{ background: rgba(59,130,246,0.12); border-color: rgba(59,130,246,0.22); color: #1D4ED8; margin-top: 6px; font-size: 0.76rem; }

/* =========================================================
   MODAL REVISAR RECARGA - FIX DE RAÍZ
   - No se rompe con imágenes grandes.
   - Estructura semántica sin columnas anidadas incorrectamente.
   - El comprobante queda contenido y con scroll propio si es enorme.
========================================================= */
.ev-modal-recarga-dialog{
  width: min(1180px, calc(100vw - 28px));
  max-width: min(1180px, calc(100vw - 28px));
  margin-left: auto;
  margin-right: auto;
}

.ev-modal{
  border-radius: 18px;
  border: none;
  overflow: hidden;
  background: #ffffff;
  box-shadow: 0 18px 45px rgba(0,0,0,0.22), 0 6px 12px rgba(0,0,0,0.12);
}
.ev-modal-header{
  background: linear-gradient(140deg, #0F592F 0%, #0E7A43 55%, #16A34A 100%);
  padding: 16px 18px;
  border-bottom: 1px solid rgba(255,255,255,0.20);
  flex-shrink:0;
}
.ev-modal-header .modal-title{ font-weight: 850; font-size: 1rem; color: #ffffff; }
.ev-modal-body{
  background: #ffffff;
  padding: 1.25rem 1.35rem;
  max-height: calc(100vh - 190px);
  overflow: auto;
  overscroll-behavior: contain;
}
.ev-modal-footer{
  background: #ffffff;
  border-top: 1px solid var(--ev-gris-200);
  padding: 12px 16px;
  display:flex;
  align-items:center;
  gap:12px;
  flex-wrap:wrap;
  flex-shrink:0;
}
.ev-recarga-footer-actions{
  margin-left:auto;
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:10px;
  flex-wrap:wrap;
}

.ev-recarga-review-grid{
  display:grid;
  grid-template-columns: minmax(300px, 390px) minmax(0, 1fr);
  gap:18px;
  align-items:start;
}
.ev-recarga-review-info,
.ev-recarga-review-proof{
  min-width:0;
}

.ev-kv{
  border: 1px solid rgba(148,163,184,0.22);
  border-radius: 16px;
  padding: 12px 14px;
  background: #ffffff;
}
.ev-kv-item{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:12px;
  padding: 7px 0;
  border-bottom: 1px dashed rgba(229,231,235,0.95);
}
.ev-kv-item:last-child{ border-bottom: none; }
.ev-kv-item span{ color: var(--ev-gris-500); font-weight: 800; }
.ev-kv-item strong{ color: var(--ev-texto); font-weight: 900; text-align:right; word-break:break-word; }

.ev-proof{
  border-radius: 16px;
  border: 1px solid rgba(148,163,184,0.22);
  overflow:hidden;
  background:#fff;
  min-width:0;
}
.ev-proof-title{
  padding: 12px 14px;
  font-weight: 900;
  color: var(--ev-verde-oscuro);
  background: var(--ev-gris-050);
  border-bottom: 1px solid var(--ev-gris-200);
}
.ev-proof-box{
  padding: 14px;
  display:flex;
  align-items:center;
  justify-content:center;
  min-height: 420px;
  max-height: min(62vh, 640px);
  background:
    linear-gradient(45deg, rgba(249,250,251,.75) 25%, transparent 25%),
    linear-gradient(-45deg, rgba(249,250,251,.75) 25%, transparent 25%),
    linear-gradient(45deg, transparent 75%, rgba(249,250,251,.75) 75%),
    linear-gradient(-45deg, transparent 75%, rgba(249,250,251,.75) 75%),
    #ffffff;
  background-size:22px 22px;
  background-position:0 0, 0 11px, 11px -11px, -11px 0;
  overflow:auto;
}
.ev-proof-box img{
  display:block;
  max-width:100%;
  max-height: calc(min(62vh, 640px) - 32px);
  width:auto;
  height:auto;
  object-fit:contain;
  border-radius:12px;
  box-shadow:0 14px 30px rgba(15,23,42,.10);
  background:#fff;
}
.ev-proof-empty{ color: var(--ev-gris-500); font-weight: 800; text-align:center; }
.ev-proof-hint{ padding: 10px 14px 14px; color: var(--ev-gris-500); font-size: 0.88rem; font-weight: 650; }

.ev-alert-reenviada{
  display: flex;
  align-items: flex-start;
  gap: 12px;
  border-radius: 16px;
  padding: 12px 14px;
  margin-bottom: 14px;
  background: linear-gradient(180deg, rgba(239,246,255,1) 0%, rgba(248,250,252,1) 100%);
  border: 1px solid rgba(59,130,246,0.18);
}
.ev-alert-reenviada-icon{
  width: 36px; height: 36px;
  border-radius: 12px;
  display: inline-flex; align-items: center; justify-content: center;
  background: rgba(59,130,246,0.12);
  color: #1D4ED8;
  flex: 0 0 36px;
}
.ev-alert-reenviada-title{ font-weight: 900; color: #1E3A8A; line-height: 1.15; margin-bottom: 3px; }
.ev-alert-reenviada-text{ color: #475569; font-size: 0.9rem; line-height: 1.35; }

@media (max-width: 991.98px){
  .ev-modal-recarga-dialog{ width: min(760px, calc(100vw - 20px)); max-width: min(760px, calc(100vw - 20px)); }
  .ev-recarga-review-grid{ grid-template-columns:1fr; }
  .ev-proof-box{ min-height: 320px; max-height: 56vh; }
  .ev-proof-box img{ max-height: calc(56vh - 32px); }
}

@media (max-width: 576px){
  .ev-recargas-title{ font-size: 1.65rem; }
  .ev-quick-actions .btn{ width: 100%; }
  .ev-hero-body{ padding: 16px 14px 12px; }
  .ev-col-fecha{ width: 150px; }
  .ev-col-usuario{ min-width: 220px; max-width: 320px; }
  .ev-card-footer{ align-items:flex-start; flex-direction:column; }
  .ev-footer-right{ width:100%; justify-content:space-between; }
  .ev-modal-body{ padding: 1rem; max-height: calc(100vh - 170px); }
  .ev-modal-footer{ flex-direction:column; align-items:stretch; }
  .ev-modal-footer .btn{ width:100%; justify-content:center; }
  .ev-recarga-footer-actions{ width:100%; margin-left:0; flex-direction:column; }
  .ev-proof-box{ min-height: 260px; max-height: 52vh; }
  .ev-proof-box img{ max-height: calc(52vh - 28px); }
}
</style>
