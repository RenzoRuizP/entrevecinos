<style>
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
  --ev-gris-600:#4B5563;
  --ev-texto:#111827;
}

.ev-ap-page{ color:var(--ev-texto); }

.ev-card{
  border-radius:18px;
  background:#fff;
  border:1px solid rgba(148,163,184,0.22);
  box-shadow:0 14px 40px rgba(15,23,42,0.10);
  overflow:hidden;
}

.ev-hero{
  background:
    radial-gradient(circle at 80% 20%, rgba(22,163,74,0.08), transparent 55%),
    radial-gradient(circle at 15% 80%, rgba(234,124,18,0.07), transparent 55%),
    #fff;
}
.ev-hero-body{ padding:18px 18px 14px; }
.ev-hero-top{
  display:flex; align-items:flex-start; justify-content:space-between;
  gap:16px; flex-wrap:wrap;
}
.ev-hero-right{ display:flex; align-items:center; gap:10px; }
.ev-hero-bottom{
  margin-top:14px;
  display:flex; align-items:center; justify-content:space-between;
  gap:12px; flex-wrap:wrap;
}

.ev-title{
  font-size:2.05rem;
  font-weight:850;
  color:var(--ev-verde-oscuro);
  letter-spacing:0.01em;
  margin:0;
}
.ev-subtitle{ color:var(--ev-gris-500); font-size:.95rem; }

/* Botón naranja (acciones) */
.ev-btn-orange{
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  border:none; color:#fff;
  border-radius:14px;
  padding:10px 18px;
  box-shadow:0 12px 26px rgba(234,124,18,0.35);
  transition:transform .18s ease, box-shadow .18s ease, filter .18s ease;
  font-weight:750;
}
.ev-btn-orange:hover{
  background:linear-gradient(135deg,var(--ev-naranja-oscuro),#EA580C);
  transform:translateY(-1px);
  box-shadow:0 14px 32px rgba(234,124,18,0.48);
  filter:saturate(1.02);
}

/* Botones light (chips / paginado) */
.ev-btn-light{
  border-radius:999px;
  border:1px solid var(--ev-gris-200);
  background:rgba(255,255,255,0.80);
  color:var(--ev-verde-oscuro);
  font-weight:750;
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease;
}
.ev-btn-light:hover{
  background:#ECFDF5;
  border-color:rgba(22,163,74,0.35);
  transform:translateY(-1px);
  box-shadow:0 12px 22px rgba(15,23,42,0.08);
  filter:saturate(1.02);
}

/* Botón outline base */
.ev-btn-outline{
  border:1px solid #D1D5DB;
  background:#fff;
  color:#4B5563;
  font-weight:850;
  border-radius:16px;
  padding:10px 16px;
  transition:transform .16s ease, box-shadow .16s ease, background .16s ease;
}
.ev-btn-outline:hover{
  background:#F3F4F6;
  color:#111827;
  transform:translateY(-1px);
  box-shadow:0 12px 22px rgba(15,23,42,0.08);
}

/* ✅ FIX: el punto en ".padding" no debe existir */
.ev-btn-outline{ padding:10px 16px; }

/* =========================
   BOTONES (MODAL) — PREMIUM EV
========================= */

.ev-btn-success,
.ev-btn-danger,
.ev-btn-warning{
  border-radius:16px;
  padding:10px 16px;
  font-weight:900;
  letter-spacing:.01em;
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease;
  border:none;
  color:#fff;
}

.ev-btn-success{
  background:linear-gradient(135deg,#16A34A 0%,#22C55E 100%);
  box-shadow:0 12px 26px rgba(22,163,74,0.28);
}
.ev-btn-success:hover{
  transform:translateY(-1px);
  box-shadow:0 16px 34px rgba(22,163,74,0.36);
  filter:saturate(1.02);
}

.ev-btn-danger{
  background:linear-gradient(135deg,#EF4444 0%,#DC2626 100%);
  box-shadow:0 12px 26px rgba(239,68,68,0.22);
}
.ev-btn-danger:hover{
  transform:translateY(-1px);
  box-shadow:0 16px 34px rgba(239,68,68,0.30);
  filter:saturate(1.02);
}

.ev-btn-warning{
  background:linear-gradient(135deg,#F59E0B 0%, var(--ev-naranja) 100%);
  box-shadow:0 12px 26px rgba(234,124,18,0.22);
}
.ev-btn-warning:hover{
  transform:translateY(-1px);
  box-shadow:0 16px 34px rgba(234,124,18,0.30);
  filter:saturate(1.02);
}

/* Focus ring consistente */
.ev-btn-success:focus,
.ev-btn-danger:focus,
.ev-btn-warning:focus,
.ev-btn-outline:focus{
  outline:none;
  box-shadow:
    0 0 0 3px rgba(17,24,39,.10),
    0 0 0 6px rgba(22,163,74,.14);
}

/* Disabled elegante */
.ev-btn-success:disabled,
.ev-btn-danger:disabled,
.ev-btn-warning:disabled{
  opacity:.60;
  transform:none;
  box-shadow:none;
  cursor:not-allowed;
}

/* Icon button */
.ev-icon-btn{
  width:42px;height:42px;
  border-radius:14px;
  border:1px solid var(--ev-gris-200);
  background:#fff;
  display:grid;place-items:center;
  color:var(--ev-verde-oscuro);
  transition:transform .16s ease, box-shadow .16s ease, background .16s ease;
}
.ev-icon-btn:hover{
  background:#ECFDF5;
  border-color:rgba(22,163,74,0.35);
  transform:translateY(-1px);
  box-shadow:0 12px 22px rgba(15,23,42,0.08);
}

/* Summary pill */
.ev-summary-pill{
  display:inline-flex; align-items:center; gap:10px;
  padding:12px 14px;
  border-radius:16px;
  background:linear-gradient(90deg, rgba(187,247,208,0.55), rgba(187,247,208,0.20));
  border:1px solid rgba(22,163,74,0.20);
}
.ev-summary-label{ color:#14532D; font-weight:850; }
.ev-summary-count{
  background:rgba(255,255,255,0.90);
  border:1px solid rgba(22,163,74,0.18);
  padding:2px 10px;
  border-radius:999px;
  font-weight:900;
  color:var(--ev-verde-oscuro);
  min-width:34px;
  text-align:center;
}

/* Card header/body/footer */
.ev-card-header{ padding:14px 16px; border-bottom:1px solid var(--ev-gris-200); background:#fff; }
.ev-card-header-row{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
.ev-card-title{ margin:0; font-size:1.05rem; font-weight:900; color:var(--ev-verde-oscuro); }
.ev-table-meta{ color:var(--ev-gris-500); font-size:.88rem; }
.ev-card-body{ padding:16px; }
.ev-card-footer{
  display:flex; align-items:center; justify-content:space-between;
  padding:12px 16px;
  border-top:1px solid var(--ev-gris-200);
  background:#fff;
}
.ev-footer-left{ color:var(--ev-gris-500); font-weight:650; font-size:.9rem; }
.ev-footer-right{ display:flex; align-items:center; gap:10px; }
.ev-page-pill{
  display:inline-flex; min-width:42px; justify-content:center;
  padding:6px 12px;
  border-radius:12px;
  border:1px solid var(--ev-gris-200);
  background:#fff;
  font-weight:900;
  color:var(--ev-verde-oscuro);
}

/* Inputs */
.ev-input-icon{ position:absolute; top:50%; left:14px; transform:translateY(-50%); color:#9ca3af; }
.ev-input{
  border-radius:12px;
  border:1px solid var(--ev-verde-claro);
  transition:all .18s ease-out;
  box-shadow:0 0 0 0 rgba(22,163,74,0);
}
.ev-input:focus{
  border-color:var(--ev-verde);
  box-shadow:0 0 0 3px rgba(22,163,74,0.18);
  outline:none;
}

/* =========================
   TABLA — ULTRA PRO PREMIUM
========================= */

.ev-table-wrap{
  padding:0 14px 14px;
  background:#fff;
}

.ev-table-frame{
  border:1px solid rgba(148,163,184,0.22);
  border-radius:16px;
  overflow:hidden;
  background:linear-gradient(180deg,#ffffff 0%, #fbfbfc 100%);
  box-shadow:0 12px 28px rgba(15,23,42,0.06);
}

.ev-table{
  width:100%;
  border-collapse:separate !important;
  border-spacing:0;
  table-layout:fixed;
  font-size:.93rem;
}

.ev-table thead th{
  position:sticky;
  top:0;
  z-index:2;

  background:linear-gradient(180deg,var(--ev-gris-050) 0%, #ffffff 100%);
  color:#0f172a;
  font-weight:900;
  letter-spacing:.01em;

  border-bottom:1px solid var(--ev-gris-200) !important;
  white-space:nowrap;

  padding:14px 16px !important;
}

.ev-table tbody td{
  vertical-align:middle;
  border-bottom:1px solid rgba(229,231,235,0.9);
  padding:14px 16px !important;
  color:#111827;
  background:#fff;
}

.ev-table tbody tr:nth-child(even) td{
  background:rgba(249,250,251,0.55);
}

.ev-table tbody tr:hover td{
  background:rgba(236,253,245,0.65);
}

.ev-table th + th,
.ev-table td + td{
  border-left:1px solid rgba(229,231,235,0.55);
}

.ev-col-fecha{ width:180px; }
.ev-col-titulo{ width:320px; }
.ev-col-precio{ width:140px; }
.ev-col-usuario{ width:360px; }
.ev-col-estado{ width:150px; }
.ev-col-acciones{ width:170px; }

.ev-empty{ color:var(--ev-gris-500); font-weight:800; background:#fff !important; }
.ev-empty-wrap{ display:flex; flex-direction:column; align-items:center; gap:8px; }
.ev-empty-ico{ font-size:1.8rem; color:rgba(15,89,47,0.35); }
.ev-empty-text{ color:var(--ev-gris-600); font-weight:700; }

/* Badges */
.ev-badge{
  display:inline-flex; align-items:center;
  padding:4px 10px;
  border-radius:999px;
  font-size:.82rem;
  font-weight:900;
  border:1px solid transparent;
  text-transform:lowercase;
  box-shadow:0 8px 18px rgba(15,23,42,0.06);
}
.ev-badge-pendiente{ background:rgba(234,124,18,0.12); border-color:rgba(234,124,18,0.25); color:#92400E; }
.ev-badge-aprobada{ background:rgba(22,163,74,0.12); border-color:rgba(22,163,74,0.25); color:#14532D; }
.ev-badge-rechazada{ background:rgba(239,68,68,0.12); border-color:rgba(239,68,68,0.22); color:#7F1D1D; }
.ev-badge-borrador{ background:rgba(107,114,128,0.12); border-color:rgba(107,114,128,0.22); color:#374151; }

/* MODAL */
.ev-modal{
  border-radius:18px;
  border:none;
  overflow:hidden;
  background:transparent;
  box-shadow:0 18px 45px rgba(0,0,0,0.22), 0 6px 12px rgba(0,0,0,0.12);
}
.ev-modal-header{
  background:linear-gradient(140deg,#0F592F 0%,#0E7A43 55%,#16A34A 100%);
  padding:16px 18px;
  border-bottom:1px solid rgba(255,255,255,0.20);
}
.ev-modal-header .modal-title{ font-weight:850; font-size:1rem; color:#fff; }
.ev-modal-body{ background:#fff; padding:1.4rem 1.6rem; box-shadow:inset 0 1px 0 rgba(0,0,0,0.06); }

/* ✅ Footer con más aire premium */
.ev-modal-footer{
  background:#fff;
  border-top:1px solid var(--ev-gris-200);
  padding:14px 18px;
}

.ev-kv{
  border:1px solid rgba(148,163,184,0.22);
  border-radius:16px;
  padding:12px 14px;
  background:#fff;
}
.ev-kv-item{
  display:flex; justify-content:space-between; gap:10px;
  padding:6px 0;
  border-bottom:1px dashed rgba(229,231,235,0.9);
}
.ev-kv-item:last-child{ border-bottom:none; }
.ev-kv-item span{ color:var(--ev-gris-500); font-weight:700; }

.ev-desc{
  border:1px solid rgba(148,163,184,0.22);
  border-radius:16px;
  padding:12px 14px;
  background:#fff;
  color:#111827;
  min-height:120px;
  white-space:pre-wrap;
}

.ev-proof{
  border-radius:16px;
  border:1px solid rgba(148,163,184,0.22);
  overflow:hidden;
  background:#fff;
}
.ev-proof-title{
  padding:12px 14px;
  font-weight:900;
  color:var(--ev-verde-oscuro);
  background:var(--ev-gris-050);
  border-bottom:1px solid var(--ev-gris-200);
}
.ev-proof-box{ padding:12px; min-height:360px; }
.ev-proof-empty{ color:var(--ev-gris-500); font-weight:700; }
.ev-proof-hint{ padding:10px 14px 14px; color:var(--ev-gris-500); font-size:.88rem; font-weight:600; }

.ev-galeria{
  display:grid;
  grid-template-columns: repeat(2, minmax(0,1fr));
  gap:10px;
}
.ev-galeria img{
  width:100%;
  height:170px;
  object-fit:cover;
  border-radius:14px;
  border:1px solid rgba(148,163,184,0.22);
}

@media (max-width: 576px){
  .ev-title{ font-size:1.65rem; }
  .ev-quick-actions .btn{ width:100%; }
  .ev-col-fecha{ width:160px; }
  .ev-col-titulo{ width:260px; }
  .ev-col-usuario{ width:280px; }
  .ev-table thead th,
  .ev-table tbody td{ padding:12px 12px !important; }
  .ev-galeria{ grid-template-columns: 1fr; }
  .ev-galeria img{ height:200px; }
}
</style>
