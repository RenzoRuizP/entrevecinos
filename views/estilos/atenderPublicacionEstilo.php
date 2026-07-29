<style>
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#16A34A;
  --ev-verde-claro:#bbf7d0;
  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;
  --ev-rojo:#DC2626;
  --ev-gris-025:#FCFDFC;
  --ev-gris-050:#F9FAFB;
  --ev-gris-100:#F3F4F6;
  --ev-gris-150:#EEF2F7;
  --ev-gris-200:#E5E7EB;
  --ev-gris-300:#D1D5DB;
  --ev-gris-500:#6B7280;
  --ev-gris-600:#4B5563;
  --ev-gris-700:#374151;
  --ev-texto:#111827;
  --ev-shadow-card:0 14px 40px rgba(15,23,42,.10);
  --ev-shadow-soft:0 12px 28px rgba(15,23,42,.06);
  --ev-shadow-modal:0 24px 64px rgba(15,23,42,.30);
}

.ev-ap-page{ color:var(--ev-texto); }

.ev-card{
  border-radius:18px;
  background:#fff;
  border:1px solid rgba(148,163,184,.22);
  box-shadow:var(--ev-shadow-card);
  overflow:hidden;
}

.ev-hero{
  background:
    radial-gradient(circle at 80% 20%, rgba(22,163,74,.08), transparent 55%),
    radial-gradient(circle at 15% 80%, rgba(234,124,18,.07), transparent 55%),
    #fff;
}
.ev-hero-body{ padding:18px 18px 14px; }
.ev-hero-top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:16px;
  flex-wrap:wrap;
}
.ev-hero-right{ display:flex; align-items:center; gap:10px; }
.ev-hero-bottom{
  margin-top:14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
}

.ev-title{
  font-size:2.05rem;
  font-weight:850;
  color:var(--ev-verde-oscuro);
  letter-spacing:.01em;
  margin:0;
}
.ev-subtitle{ color:var(--ev-gris-500); font-size:.95rem; }

.ev-btn-orange{
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  border:none;
  color:#fff;
  border-radius:14px;
  padding:10px 18px;
  box-shadow:0 12px 26px rgba(234,124,18,.35);
  transition:transform .18s ease, box-shadow .18s ease, filter .18s ease;
  font-weight:750;
}
.ev-btn-orange:hover{
  background:linear-gradient(135deg,var(--ev-naranja-oscuro),#EA580C);
  transform:translateY(-1px);
  box-shadow:0 14px 32px rgba(234,124,18,.48);
  filter:saturate(1.02);
}

.ev-btn-light{
  border-radius:999px;
  border:1px solid var(--ev-gris-200);
  background:rgba(255,255,255,.80);
  color:var(--ev-verde-oscuro);
  font-weight:750;
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease;
}
.ev-btn-light:hover{
  background:#ECFDF5;
  border-color:rgba(22,163,74,.35);
  transform:translateY(-1px);
  box-shadow:0 12px 22px rgba(15,23,42,.08);
  filter:saturate(1.02);
}

.ev-btn-outline{
  border:1px solid var(--ev-gris-300);
  background:#fff;
  color:var(--ev-gris-600);
  font-weight:850;
  border-radius:16px;
  padding:10px 16px;
  transition:transform .16s ease, box-shadow .16s ease, background .16s ease;
}
.ev-btn-outline:hover{
  background:var(--ev-gris-100);
  color:var(--ev-texto);
  transform:translateY(-1px);
  box-shadow:0 12px 22px rgba(15,23,42,.08);
}

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
  box-shadow:0 12px 26px rgba(22,163,74,.28);
}
.ev-btn-success:hover{
  transform:translateY(-1px);
  box-shadow:0 16px 34px rgba(22,163,74,.36);
  filter:saturate(1.02);
}
.ev-btn-danger{
  background:linear-gradient(135deg,#EF4444 0%,#DC2626 100%);
  box-shadow:0 12px 26px rgba(239,68,68,.22);
}
.ev-btn-danger:hover{
  transform:translateY(-1px);
  box-shadow:0 16px 34px rgba(239,68,68,.30);
  filter:saturate(1.02);
}
.ev-btn-warning{
  background:linear-gradient(135deg,#F59E0B 0%,var(--ev-naranja) 100%);
  box-shadow:0 12px 26px rgba(234,124,18,.22);
}
.ev-btn-warning:hover{
  transform:translateY(-1px);
  box-shadow:0 16px 34px rgba(234,124,18,.30);
  filter:saturate(1.02);
}
.ev-btn-success:focus,
.ev-btn-danger:focus,
.ev-btn-warning:focus,
.ev-btn-outline:focus{
  outline:none;
  box-shadow:0 0 0 3px rgba(17,24,39,.10),0 0 0 6px rgba(22,163,74,.14);
}
.ev-btn-success:disabled,
.ev-btn-danger:disabled,
.ev-btn-warning:disabled{
  opacity:.60;
  transform:none;
  box-shadow:none;
  cursor:not-allowed;
}

.ev-icon-btn{
  width:42px;
  height:42px;
  border-radius:14px;
  border:1px solid var(--ev-gris-200);
  background:#fff;
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  transition:transform .16s ease, box-shadow .16s ease, background .16s ease;
}
.ev-icon-btn:hover{
  background:#ECFDF5;
  border-color:rgba(22,163,74,.35);
  transform:translateY(-1px);
  box-shadow:0 12px 22px rgba(15,23,42,.08);
}

.ev-summary-pill{
  display:inline-flex;
  align-items:center;
  gap:10px;
  padding:12px 14px;
  border-radius:16px;
  background:linear-gradient(90deg,rgba(187,247,208,.55),rgba(187,247,208,.20));
  border:1px solid rgba(22,163,74,.20);
}
.ev-summary-label{ color:#14532D; font-weight:850; }
.ev-summary-count{
  background:rgba(255,255,255,.90);
  border:1px solid rgba(22,163,74,.18);
  padding:2px 10px;
  border-radius:999px;
  font-weight:900;
  color:var(--ev-verde-oscuro);
  min-width:34px;
  text-align:center;
}

.ev-card-header{ padding:14px 16px; border-bottom:1px solid var(--ev-gris-200); background:#fff; }
.ev-card-header-row{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
.ev-card-title{ margin:0; font-size:1.05rem; font-weight:900; color:var(--ev-verde-oscuro); }
.ev-table-meta{ color:var(--ev-gris-500); font-size:.88rem; }
.ev-card-body{ padding:16px; }
.ev-card-footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:12px 16px;
  border-top:1px solid var(--ev-gris-200);
  background:#fff;
}
.ev-footer-left{ color:var(--ev-gris-500); font-weight:650; font-size:.9rem; }
.ev-footer-right{ display:flex; align-items:center; gap:10px; }
.ev-page-pill{
  display:inline-flex;
  min-width:42px;
  justify-content:center;
  padding:6px 12px;
  border-radius:12px;
  border:1px solid var(--ev-gris-200);
  background:#fff;
  font-weight:900;
  color:var(--ev-verde-oscuro);
}

.ev-input-icon{ position:absolute; top:50%; left:14px; transform:translateY(-50%); color:#9ca3af; }
.ev-input{
  border-radius:12px;
  border:1px solid var(--ev-verde-claro);
  transition:all .18s ease-out;
  box-shadow:0 0 0 0 rgba(22,163,74,0);
}
.ev-input:focus{
  border-color:var(--ev-verde);
  box-shadow:0 0 0 3px rgba(22,163,74,.18);
  outline:none;
}

.ev-table-wrap{ padding:0 14px 14px; background:#fff; }
.ev-table-frame{
  border:1px solid rgba(148,163,184,.22);
  border-radius:16px;
  overflow:hidden;
  background:linear-gradient(180deg,#fff 0%,#fbfbfc 100%);
  box-shadow:var(--ev-shadow-soft);
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
  background:linear-gradient(180deg,var(--ev-gris-050) 0%,#fff 100%);
  color:#0f172a;
  font-weight:900;
  letter-spacing:.01em;
  border-bottom:1px solid var(--ev-gris-200) !important;
  white-space:nowrap;
  padding:14px 16px !important;
}
.ev-table tbody td{
  vertical-align:middle;
  border-bottom:1px solid rgba(229,231,235,.9);
  padding:14px 16px !important;
  color:var(--ev-texto);
  background:#fff;
}
.ev-table tbody tr:nth-child(even) td{ background:rgba(249,250,251,.55); }
.ev-table tbody tr:hover td{ background:rgba(236,253,245,.65); }
.ev-table th + th,
.ev-table td + td{ border-left:1px solid rgba(229,231,235,.55); }
.ev-col-fecha{ width:180px; }
.ev-col-titulo{ width:320px; }
.ev-col-precio{ width:140px; }
.ev-col-usuario{ width:360px; }
.ev-col-estado{ width:150px; }
.ev-col-acciones{ width:170px; }
.ev-empty{ color:var(--ev-gris-500); font-weight:800; background:#fff !important; }
.ev-empty-wrap{ display:flex; flex-direction:column; align-items:center; gap:8px; }
.ev-empty-ico{ font-size:1.8rem; color:rgba(15,89,47,.35); }
.ev-empty-text{ color:var(--ev-gris-600); font-weight:700; }

.ev-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:5px 10px;
  border-radius:999px;
  font-size:.78rem;
  font-weight:900;
  border:1px solid transparent;
  text-transform:lowercase;
  box-shadow:0 8px 18px rgba(15,23,42,.06);
  white-space:nowrap;
}
.ev-badge-pendiente{ background:rgba(234,124,18,.12); border-color:rgba(234,124,18,.25); color:#92400E; }
.ev-badge-aprobada{ background:rgba(22,163,74,.12); border-color:rgba(22,163,74,.25); color:#14532D; }
.ev-badge-rechazada{ background:rgba(239,68,68,.12); border-color:rgba(239,68,68,.22); color:#7F1D1D; }
.ev-badge-borrador{ background:rgba(107,114,128,.12); border-color:rgba(107,114,128,.22); color:#374151; }

/* =========================================================
   MODAL DE REVISIÓN — UX/UI FINAL
========================================================= */
#modalPub .ev-review-dialog{
  max-width:1120px;
  margin:1rem auto;
}
#modalPub .modal-content.ev-modal{
  border:0;
  border-radius:22px;
  overflow:hidden;
  background:#fff;
  box-shadow:var(--ev-shadow-modal);
  max-height:calc(100vh - 2rem);
  display:flex;
  flex-direction:column;
}
#modalPub .ev-modal-header{
  flex:0 0 auto;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:14px;
  padding:14px 18px;
  color:#fff;
  border:0;
  background:linear-gradient(140deg,#0F592F 0%,#0E7A43 55%,#16A34A 100%);
}
.ev-review-title-wrap{
  display:flex;
  align-items:center;
  gap:10px;
  min-width:0;
}
.ev-review-title-icon{
  width:36px;
  height:36px;
  flex:0 0 auto;
  display:grid;
  place-items:center;
  border-radius:12px;
  color:#fff;
  background:rgba(255,255,255,.14);
  border:1px solid rgba(255,255,255,.18);
}
#modalPub .ev-modal-header .modal-title{
  margin:0;
  color:#fff;
  font-weight:850;
  font-size:1rem;
  line-height:1.15;
}
.ev-review-title-subtitle{
  display:block;
  margin-top:2px;
  color:rgba(255,255,255,.78);
  font-size:.76rem;
  line-height:1.25;
}
#modalPub .ev-modal-header .btn-close{
  flex:0 0 auto;
  opacity:.94;
}
#modalPub .ev-modal-body{
  flex:1 1 auto;
  min-height:0;
  overflow:auto;
  padding:18px;
  background:
    radial-gradient(circle at 94% 0%,rgba(22,163,74,.05),transparent 28%),
    linear-gradient(180deg,#fff 0%,#fbfdfb 100%);
}
#modalPub .ev-modal-footer{
  flex:0 0 auto;
  display:flex;
  align-items:center;
  gap:12px;
  padding:13px 18px;
  background:#fff;
  border-top:1px solid rgba(229,231,235,.92);
  box-shadow:0 -8px 22px rgba(15,23,42,.035);
}
.ev-review-actions{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:10px;
  flex-wrap:wrap;
  margin-left:auto;
}

.ev-review-grid{
  display:grid;
  grid-template-columns:minmax(0,1fr) minmax(320px,1fr);
  align-items:start;
  gap:18px;
}
.ev-review-column{
  min-width:0;
  display:flex;
  flex-direction:column;
  gap:14px;
}
.ev-review-card,
.ev-proof{
  min-width:0;
  border:1px solid rgba(203,213,225,.72);
  border-radius:17px;
  background:#fff;
  box-shadow:0 10px 26px rgba(15,23,42,.045);
}
.ev-review-card{ padding:14px; }
.ev-review-card-head{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:12px;
  padding-bottom:10px;
  margin-bottom:10px;
  border-bottom:1px solid rgba(229,231,235,.88);
}
.ev-review-card-head-compact{ padding-bottom:8px; margin-bottom:10px; }
.ev-review-card-head h6{
  margin:1px 0 0;
  color:var(--ev-texto);
  font-size:.98rem;
  font-weight:850;
  line-height:1.25;
}
.ev-review-eyebrow{
  display:block;
  color:#94A3B8;
  font-size:.66rem;
  font-weight:900;
  letter-spacing:.075em;
  line-height:1;
  text-transform:uppercase;
}

.ev-kv{
  display:grid;
  gap:0;
  min-width:0;
}
.ev-kv-item{
  min-width:0;
  display:grid;
  grid-template-columns:minmax(92px,.7fr) minmax(0,1.3fr);
  align-items:center;
  gap:12px;
  padding:8px 0;
  border-bottom:1px dashed rgba(229,231,235,.94);
}
.ev-kv-item:last-child{ border-bottom:none; }
.ev-kv-item > span:first-child{
  color:var(--ev-gris-500);
  font-size:.84rem;
  font-weight:750;
}
.ev-kv-item > strong,
.ev-kv-item > .ev-badge{
  min-width:0;
  justify-self:end;
  text-align:right;
  color:#1F2937;
  font-size:.88rem;
  font-weight:850;
  overflow-wrap:anywhere;
  word-break:break-word;
}

#modalPub .form-label{
  color:#1F2937;
  font-size:.88rem;
  font-weight:700;
}
#modalPub .ev-input{
  min-height:104px;
  resize:vertical;
  line-height:1.48;
}
#modalPub .form-text{
  margin-top:7px;
  color:var(--ev-gris-500);
  font-size:.78rem;
  line-height:1.38;
}

.ev-desc{
  min-width:0;
  min-height:104px;
  max-height:188px;
  overflow:auto;
  padding:12px 14px;
  border:1px solid rgba(203,213,225,.72);
  border-radius:14px;
  background:linear-gradient(180deg,#fff 0%,#fbfcfd 100%);
  color:#1F2937;
  font-size:.90rem;
  line-height:1.52;
  white-space:pre-wrap;
  overflow-wrap:anywhere;
  word-break:break-word;
  scrollbar-width:thin;
  scrollbar-color:rgba(148,163,184,.42) transparent;
}
.ev-desc::-webkit-scrollbar{ width:7px; }
.ev-desc::-webkit-scrollbar-track{ background:transparent; }
.ev-desc::-webkit-scrollbar-thumb{
  background:rgba(148,163,184,.40);
  border-radius:999px;
  border:2px solid transparent;
  background-clip:padding-box;
}
.ev-desc:focus{
  outline:0;
  border-color:rgba(22,163,74,.42);
  box-shadow:0 0 0 3px rgba(22,163,74,.10);
}
.ev-desc-soft{
  min-height:82px;
  color:var(--ev-gris-600);
  background:linear-gradient(180deg,#F9FAFB 0%,#FFFFFF 100%);
}

.ev-proof{
  overflow:hidden;
  display:flex;
  flex-direction:column;
  min-height:0;
}
.ev-proof-title{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  padding:13px 14px;
  color:var(--ev-verde-oscuro);
  font-weight:900;
  background:linear-gradient(180deg,var(--ev-gris-050) 0%,#fff 100%);
  border-bottom:1px solid var(--ev-gris-200);
}
.ev-proof-title small{
  color:var(--ev-gris-500);
  font-size:.76rem;
  font-weight:750;
}
.ev-proof-box{
  min-height:218px;
  padding:12px;
  display:flex;
  align-items:center;
  background:linear-gradient(180deg,#fff 0%,#FBFCFB 100%);
}
.ev-proof-empty{
  width:100%;
  min-height:192px;
  display:grid;
  place-items:center;
  align-content:center;
  gap:8px;
  text-align:center;
  color:var(--ev-gris-500);
  font-size:.88rem;
  font-weight:700;
  border:1px dashed rgba(148,163,184,.34);
  border-radius:14px;
  background:rgba(249,250,251,.74);
}
.ev-proof-empty i{
  display:grid;
  place-items:center;
  width:40px;
  height:40px;
  border-radius:13px;
  color:var(--ev-verde-oscuro);
  background:rgba(230,244,236,.85);
  border:1px solid rgba(22,163,74,.16);
  font-size:1.15rem;
}
.ev-proof-hint{
  display:flex;
  align-items:flex-start;
  gap:8px;
  padding:11px 14px 14px;
  border-top:1px solid rgba(229,231,235,.92);
  color:var(--ev-gris-600);
  font-size:.82rem;
  font-weight:600;
  line-height:1.42;
}
.ev-proof-hint i{ color:var(--ev-verde-oscuro); margin-top:1px; }

.ev-galeria{
  width:100%;
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
  gap:12px;
}
.ev-galeria[data-count="1"]{ grid-template-columns:minmax(0,1fr); }
.ev-galeria-item{
  min-width:0;
  margin:0;
  overflow:hidden;
  border:1px solid rgba(203,213,225,.72);
  border-radius:14px;
  background:var(--ev-gris-050);
  box-shadow:0 8px 18px rgba(15,23,42,.05);
}
.ev-galeria[data-count="1"] .ev-galeria-item{ max-width:390px; }
.ev-galeria-item img{
  display:block;
  width:100%;
  height:190px;
  object-fit:contain;
  background:#fff;
}
.ev-galeria[data-count="1"] .ev-galeria-item img{ height:220px; }

@media (max-width: 991.98px){
  #modalPub .ev-review-dialog{ max-width:calc(100% - 1.5rem); }
  .ev-review-grid{ grid-template-columns:1fr; }
  .ev-review-media-column{ order:-1; }
  .ev-proof-box{ min-height:0; }
}

@media (max-width: 768px){
  .ev-title{ font-size:1.65rem; }
  .ev-quick-actions .btn{ width:100%; }
  .ev-col-fecha{ width:160px; }
  .ev-col-titulo{ width:260px; }
  .ev-col-usuario{ width:280px; }
  .ev-table thead th,
  .ev-table tbody td{ padding:12px !important; }
}

@media (max-width: 575.98px){
  #modalPub .ev-review-dialog{ max-width:calc(100% - 1rem); margin:.5rem auto; }
  #modalPub .modal-content.ev-modal{ max-height:calc(100vh - 1rem); border-radius:18px; }
  #modalPub .ev-modal-header{ padding:13px 14px; }
  .ev-review-title-subtitle{ display:none; }
  #modalPub .ev-modal-body{ padding:12px; }
  #modalPub .ev-modal-footer{
    align-items:stretch;
    flex-direction:column;
    padding:12px 14px;
  }
  .ev-review-actions{
    width:100%;
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:8px;
    margin-left:0;
  }
  .ev-review-actions .btn,
  #modalPub .ev-modal-footer > .ev-btn-outline{
    width:100%;
    min-width:0;
    padding:10px 8px;
    font-size:.80rem;
  }
  .ev-review-card{ padding:12px; }
  .ev-kv-item{ grid-template-columns:86px minmax(0,1fr); gap:10px; }
  .ev-kv-item > strong,
  .ev-kv-item > .ev-badge{ font-size:.83rem; }
  .ev-galeria{ grid-template-columns:1fr; }
  .ev-galeria[data-count="1"] .ev-galeria-item{ max-width:none; }
  .ev-galeria-item img,
  .ev-galeria[data-count="1"] .ev-galeria-item img{ height:210px; }
}

/* ==========================================================
   MODAL REVISAR PUBLICACIÓN — MISMO LENGUAJE VISUAL EV
   Agregar este bloque AL FINAL de atenderPublicacionEstilo.php,
   inmediatamente antes de .

   Está aislado con #modalPub.ev-ap-modal para no modificar
   Nueva publicación ni los demás modales.
========================================================== */

#modalPub.ev-ap-modal{
  --ev-ap-verde-oscuro:#0F592F;
  --ev-ap-verde:#16A34A;
  --ev-ap-naranja:#EA7C12;
  --ev-ap-rojo:#DC2626;
  --ev-ap-ambar:#F59E0B;
  --ev-ap-gris-050:#F9FAFB;
  --ev-ap-gris-100:#F3F4F6;
  --ev-ap-gris-200:#E5E7EB;
  --ev-ap-gris-300:#D1D5DB;
  --ev-ap-gris-400:#9CA3AF;
  --ev-ap-gris-500:#6B7280;
  --ev-ap-gris-600:#4B5563;
  --ev-ap-gris-700:#374151;
  --ev-ap-texto:#111827;
  --ev-ap-radius-modal:22px;
  --ev-ap-radius-soft:17px;
  --ev-ap-header-grad:linear-gradient(140deg,#0F592F 0%,#0E7A43 55%,#16A34A 100%);
  --ev-ap-shadow-modal:0 26px 70px rgba(15,23,42,.34);
}

#modalPub.ev-ap-modal .ev-ap-modal-dialog{
  width:calc(100% - 20px);
  max-width:1080px;
  margin:10px auto;
}

#modalPub.ev-ap-modal .ev-ap-modal-content{
  border:0;
  outline:0;
  padding:0;
  overflow:hidden;
  border-radius:var(--ev-ap-radius-modal);
  background:var(--ev-ap-header-grad);
  box-shadow:var(--ev-ap-shadow-modal);
}

#modalPub.ev-ap-modal .ev-ap-modal-header{
  flex:0 0 auto;
  display:flex;
  align-items:center;
  justify-content:space-between;
  min-height:56px;
  padding:14px 24px;
  margin:0;
  border:0;
  border-radius:var(--ev-ap-radius-modal) var(--ev-ap-radius-modal) 0 0;
  background:var(--ev-ap-header-grad);
  color:#fff;
}

#modalPub.ev-ap-modal .ev-ap-modal-title{
  display:flex;
  align-items:center;
  gap:8px;
  margin:0;
  color:#fff;
  font-size:1.08rem;
  font-weight:850;
  letter-spacing:-.01em;
}

#modalPub.ev-ap-modal .ev-ap-modal-header .btn-close{
  flex:0 0 auto;
  opacity:1;
  filter:invert(1);
  transform:none;
  transition:opacity .15s ease;
}

#modalPub.ev-ap-modal .ev-ap-modal-header .btn-close:hover,
#modalPub.ev-ap-modal .ev-ap-modal-header .btn-close:focus-visible{
  opacity:.82;
  filter:invert(1);
  transform:none;
}

#modalPub.ev-ap-modal .ev-ap-modal-flex{
  display:flex;
  flex-direction:column;
  min-height:0;
  background:#fff;
  border-radius:0 0 var(--ev-ap-radius-modal) var(--ev-ap-radius-modal);
}

#modalPub.ev-ap-modal .ev-ap-modal-body-scroll{
  background:#fff;
  min-height:0;
  padding:16px 18px 18px;
}

#modalPub.ev-ap-modal .ev-ap-review-grid{
  display:grid;
  grid-template-columns:minmax(0,58%) minmax(320px,42%);
  gap:22px;
  min-height:0;
  position:relative;
}

#modalPub.ev-ap-modal .ev-ap-review-grid::after{
  content:'';
  position:absolute;
  top:10px;
  bottom:10px;
  left:calc(58% + 10px);
  width:1px;
  pointer-events:none;
  background:linear-gradient(
    180deg,
    rgba(229,231,235,0),
    rgba(229,231,235,.14) 22%,
    rgba(229,231,235,.09) 50%,
    rgba(229,231,235,.14) 78%,
    rgba(229,231,235,0)
  );
}

#modalPub.ev-ap-modal .ev-ap-review-form-col,
#modalPub.ev-ap-modal .ev-ap-review-preview-col{
  min-width:0;
}

#modalPub.ev-ap-modal .ev-ap-review-form-col{
  display:flex;
  flex-direction:column;
  gap:14px;
  padding-right:14px;
}

#modalPub.ev-ap-modal .ev-ap-review-preview-col{
  padding-left:4px;
  border-radius:20px;
  background:
    radial-gradient(circle at 80% 0%, rgba(230,244,236,.36), transparent 34%),
    linear-gradient(180deg, rgba(250,252,251,.72) 0%, rgba(255,255,255,.96) 48%, #fff 100%);
}

#modalPub.ev-ap-modal .ev-ap-review-preview-sticky{
  position:sticky;
  top:0;
}

#modalPub.ev-ap-modal .ev-ap-section{
  padding:15px;
  border:1px solid rgba(203,213,225,.58);
  border-radius:var(--ev-ap-radius-soft);
  background:rgba(255,255,255,.98);
  box-shadow:
    0 14px 34px rgba(15,23,42,.048),
    inset 0 1px 0 rgba(255,255,255,.92);
}

#modalPub.ev-ap-modal .ev-ap-step-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  margin-bottom:6px;
  padding:4px 9px;
  border:1px solid rgba(22,163,74,.16);
  border-radius:999px;
  background:rgba(230,244,236,.78);
  color:var(--ev-ap-verde-oscuro);
  font-size:.70rem;
  font-weight:900;
  letter-spacing:.06em;
  line-height:1;
  text-transform:uppercase;
}

#modalPub.ev-ap-modal .ev-ap-section-heading{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:10px;
  padding-bottom:9px;
  margin-bottom:8px;
  border-bottom:1px solid rgba(229,231,235,.90);
}

#modalPub.ev-ap-modal .ev-ap-section-title{
  margin:0 0 8px;
  color:var(--ev-ap-texto);
  font-size:1.01rem;
  font-weight:650;
  letter-spacing:-.01em;
}

#modalPub.ev-ap-modal .ev-ap-section-heading .ev-ap-section-title{
  margin:0;
}

#modalPub.ev-ap-modal .ev-ap-kv-list{
  display:grid;
  gap:0;
}

#modalPub.ev-ap-modal .ev-ap-kv-item{
  display:grid;
  grid-template-columns:minmax(92px, .42fr) minmax(0, 1fr);
  align-items:center;
  gap:12px;
  padding:8px 0;
  border-bottom:1px dashed rgba(229,231,235,.92);
}

#modalPub.ev-ap-modal .ev-ap-kv-item:last-child{
  border-bottom:0;
  padding-bottom:0;
}

#modalPub.ev-ap-modal .ev-ap-kv-item > span{
  color:var(--ev-ap-gris-500);
  font-size:.88rem;
  font-weight:650;
}

#modalPub.ev-ap-modal .ev-ap-kv-item > strong{
  min-width:0;
  color:#1F2937;
  font-size:.90rem;
  font-weight:750;
  line-height:1.34;
  text-align:right;
  overflow-wrap:anywhere;
  word-break:break-word;
}

#modalPub.ev-ap-modal .ev-ap-input{
  min-height:104px;
  border:1px solid rgba(22,163,74,.20);
  border-radius:12px;
  background:rgba(255,255,255,.96);
  color:#1F2937;
  font-weight:400;
  line-height:1.45;
  resize:vertical;
  transition:border-color .18s ease, box-shadow .18s ease, background .18s ease;
}

#modalPub.ev-ap-modal .ev-ap-input:focus{
  border-color:var(--ev-ap-verde);
  box-shadow:0 0 0 3px rgba(22,163,74,.18);
  outline:none;
}

#modalPub.ev-ap-modal .ev-ap-input::placeholder{
  color:#6B7280;
  opacity:.92;
}

#modalPub.ev-ap-modal .ev-ap-form-hint{
  margin-top:7px;
  color:var(--ev-ap-gris-500);
  font-size:.82rem;
  font-weight:400;
  line-height:1.36;
}

#modalPub.ev-ap-modal .ev-ap-content-box{
  min-height:100px;
  max-height:188px;
  overflow:auto;
  padding:12px 14px;
  border:1px solid rgba(203,213,225,.68);
  border-radius:14px;
  background:linear-gradient(180deg,#fff 0%,#FBFCFD 100%);
  color:#374151;
  font-size:.91rem;
  line-height:1.52;
  white-space:pre-wrap;
  overflow-wrap:anywhere;
  word-break:break-word;
  scrollbar-width:thin;
  scrollbar-color:rgba(148,163,184,.35) transparent;
}

#modalPub.ev-ap-modal .ev-ap-content-box::-webkit-scrollbar{
  width:7px;
}

#modalPub.ev-ap-modal .ev-ap-content-box::-webkit-scrollbar-track{
  background:transparent;
}

#modalPub.ev-ap-modal .ev-ap-content-box::-webkit-scrollbar-thumb{
  border:2px solid transparent;
  border-radius:999px;
  background:rgba(148,163,184,.34);
  background-clip:padding-box;
}

#modalPub.ev-ap-modal .ev-ap-content-box-soft{
  color:#64748B;
  background:linear-gradient(180deg,#FCFDFC 0%,#F8FAF9 100%);
}

#modalPub.ev-ap-modal .ev-ap-preview-panel{
  width:100%;
  padding:14px;
  border:1px solid rgba(203,213,225,.42);
  border-radius:20px;
  background:
    radial-gradient(circle at 90% 8%, rgba(22,163,74,.08), transparent 34%),
    linear-gradient(180deg,#fff 0%,#FBFDFB 100%);
  box-shadow:
    0 18px 42px rgba(15,23,42,.060),
    inset 0 1px 0 rgba(255,255,255,.94);
}

#modalPub.ev-ap-modal .ev-ap-preview-panel-head{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:12px;
  padding-bottom:10px;
  margin-bottom:12px;
  border-bottom:1px solid rgba(229,231,235,.90);
}

#modalPub.ev-ap-modal .ev-ap-preview-kicker{
  margin-bottom:4px;
  color:#9CA3AF;
  font-size:.70rem;
  font-weight:900;
  letter-spacing:.07em;
  line-height:1;
  text-transform:uppercase;
}

#modalPub.ev-ap-modal .ev-ap-preview-heading{
  color:var(--ev-ap-verde-oscuro);
  font-size:1rem;
  font-weight:900;
  line-height:1.18;
}

#modalPub.ev-ap-modal .ev-ap-preview-area{
  min-height:0;
  padding:10px;
  border:1px dashed rgba(148,163,184,.55);
  border-radius:16px;
  background:#F9FAFB;
}

#modalPub.ev-ap-modal .ev-ap-galeria{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(132px,1fr));
  gap:10px;
}

#modalPub.ev-ap-modal .ev-ap-galeria img{
  width:100%;
  height:170px;
  display:block;
  object-fit:contain;
  border:1px solid rgba(148,163,184,.26);
  border-radius:14px;
  background:#fff;
  box-shadow:0 8px 18px rgba(15,23,42,.04);
}

#modalPub.ev-ap-modal .ev-ap-proof-empty{
  display:flex;
  align-items:center;
  justify-content:center;
  min-height:132px;
  padding:16px;
  border:1px dashed rgba(15,89,47,.18);
  border-radius:14px;
  background:
    radial-gradient(circle at 90% 8%,rgba(230,244,236,.78),transparent 42%),
    linear-gradient(180deg,#fff 0%,#F8FCFA 100%);
  color:#64748B;
  font-size:.87rem;
  font-weight:650;
  text-align:center;
}

#modalPub.ev-ap-modal .ev-ap-preview-tip{
  display:flex;
  align-items:flex-start;
  gap:10px;
  margin-top:10px;
  padding:9px 11px;
  border:1px solid rgba(229,231,235,.95);
  border-radius:14px;
  background:rgba(255,255,255,.92);
  color:#64748B;
  font-size:.84rem;
  font-weight:600;
  line-height:1.3;
}

#modalPub.ev-ap-modal .ev-ap-preview-tip i{
  flex:0 0 auto;
  margin-top:1px;
  color:var(--ev-ap-verde-oscuro);
}

#modalPub.ev-ap-modal .ev-ap-modal-footer{
  flex:0 0 auto;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:.75rem;
  min-height:66px;
  padding:12px 24px 16px;
  border-top:1px solid rgba(229,231,235,.92);
  background:#fff;
  box-shadow:0 -10px 24px rgba(15,23,42,.035);
}

#modalPub.ev-ap-modal .ev-ap-modal-actions{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:10px;
  flex-wrap:wrap;
}

#modalPub.ev-ap-modal .ev-ap-modal-footer .btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  min-height:44px;
  border-radius:14px;
  font-weight:850;
  letter-spacing:0;
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease;
}

#modalPub.ev-ap-modal .ev-ap-btn-outline{
  border:1px solid rgba(148,163,184,.50);
  background:linear-gradient(180deg,#fff 0%,#F9FAFB 100%);
  color:#374151;
  box-shadow:0 10px 22px rgba(15,23,42,.06);
}

#modalPub.ev-ap-modal .ev-ap-btn-outline:hover,
#modalPub.ev-ap-modal .ev-ap-btn-outline:focus{
  border-color:rgba(107,114,128,.46);
  background:linear-gradient(180deg,#F9FAFB 0%,#F3F4F6 100%);
  color:#1F2937;
  box-shadow:
    0 12px 24px rgba(15,23,42,.08),
    0 0 0 3px rgba(107,114,128,.10);
  transform:translateY(-1px);
}

#modalPub.ev-ap-modal .ev-ap-btn-success,
#modalPub.ev-ap-modal .ev-ap-btn-danger,
#modalPub.ev-ap-modal .ev-ap-btn-warning{
  border:0;
  color:#fff;
}

#modalPub.ev-ap-modal .ev-ap-btn-success{
  background:linear-gradient(135deg,#16A34A 0%,#22C55E 100%);
  box-shadow:0 12px 26px rgba(22,163,74,.28);
}

#modalPub.ev-ap-modal .ev-ap-btn-danger{
  background:linear-gradient(135deg,#EF4444 0%,#DC2626 100%);
  box-shadow:0 12px 26px rgba(239,68,68,.22);
}

#modalPub.ev-ap-modal .ev-ap-btn-warning{
  background:linear-gradient(135deg,#F59E0B 0%,var(--ev-ap-naranja) 100%);
  box-shadow:0 12px 26px rgba(234,124,18,.22);
}

#modalPub.ev-ap-modal .ev-ap-btn-success:hover,
#modalPub.ev-ap-modal .ev-ap-btn-danger:hover,
#modalPub.ev-ap-modal .ev-ap-btn-warning:hover{
  filter:saturate(1.04);
  transform:translateY(-1px);
}

#modalPub.ev-ap-modal .ev-ap-btn-success:hover{
  box-shadow:0 16px 34px rgba(22,163,74,.36);
}

#modalPub.ev-ap-modal .ev-ap-btn-danger:hover{
  box-shadow:0 16px 34px rgba(239,68,68,.30);
}

#modalPub.ev-ap-modal .ev-ap-btn-warning:hover{
  box-shadow:0 16px 34px rgba(234,124,18,.30);
}

#modalPub.ev-ap-modal .ev-ap-btn-success:focus,
#modalPub.ev-ap-modal .ev-ap-btn-danger:focus,
#modalPub.ev-ap-modal .ev-ap-btn-warning:focus{
  outline:0;
  box-shadow:
    0 0 0 3px rgba(17,24,39,.10),
    0 0 0 6px rgba(22,163,74,.14);
}

#modalPub.ev-ap-modal .ev-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:unset;
  padding:.42rem .80rem;
  border-radius:999px;
  font-size:.80rem;
  font-weight:850;
  box-shadow:none;
  white-space:nowrap;
}

@media (min-width:860px){
  #modalPub.ev-ap-modal .ev-ap-modal-content{
    height:calc(100dvh - 20px);
    max-height:calc(100dvh - 20px);
  }

  #modalPub.ev-ap-modal .ev-ap-modal-flex{
    flex:1 1 auto;
  }

  #modalPub.ev-ap-modal .ev-ap-modal-body-scroll{
    flex:1 1 auto;
    overflow:hidden;
  }

  #modalPub.ev-ap-modal .ev-ap-review-grid{
    height:100%;
  }

  #modalPub.ev-ap-modal .ev-ap-review-form-col{
    max-height:100%;
    overflow-y:auto;
    overflow-x:hidden;
    padding-right:14px;
    scrollbar-width:thin;
    scrollbar-color:rgba(148,163,184,.22) transparent;
  }

  #modalPub.ev-ap-modal .ev-ap-review-form-col::-webkit-scrollbar{
    width:5px;
  }

  #modalPub.ev-ap-modal .ev-ap-review-form-col::-webkit-scrollbar-track{
    background:transparent;
  }

  #modalPub.ev-ap-modal .ev-ap-review-form-col::-webkit-scrollbar-thumb{
    border-radius:999px;
    background:rgba(148,163,184,.22);
  }

  #modalPub.ev-ap-modal .ev-ap-review-preview-col{
    max-height:100%;
    overflow:hidden;
  }

  #modalPub.ev-ap-modal .ev-ap-review-preview-sticky{
    max-height:100%;
    overflow:auto;
    padding-right:2px;
    scrollbar-width:thin;
    scrollbar-color:rgba(148,163,184,.16) transparent;
  }

  #modalPub.ev-ap-modal .ev-ap-review-preview-sticky::-webkit-scrollbar{
    width:4px;
  }

  #modalPub.ev-ap-modal .ev-ap-review-preview-sticky::-webkit-scrollbar-track{
    background:transparent;
  }

  #modalPub.ev-ap-modal .ev-ap-review-preview-sticky::-webkit-scrollbar-thumb{
    border-radius:999px;
    background:rgba(148,163,184,.16);
  }
}

@media (max-width:859.98px){
  #modalPub.ev-ap-modal .ev-ap-modal-dialog{
    width:calc(100% - 16px);
    margin:8px auto;
  }

  #modalPub.ev-ap-modal .ev-ap-modal-content{
    max-height:calc(100dvh - 16px);
  }

  #modalPub.ev-ap-modal .ev-ap-modal-body-scroll{
    overflow:auto;
    padding:14px;
  }

  #modalPub.ev-ap-modal .ev-ap-review-grid{
    display:block;
  }

  #modalPub.ev-ap-modal .ev-ap-review-grid::after{
    display:none;
  }

  #modalPub.ev-ap-modal .ev-ap-review-form-col{
    padding-right:0;
  }

  #modalPub.ev-ap-modal .ev-ap-review-preview-col{
    margin-top:14px;
    padding-left:0;
    background:transparent;
  }

  #modalPub.ev-ap-modal .ev-ap-review-preview-sticky{
    position:static;
  }
}

@media (max-width:575.98px){
  #modalPub.ev-ap-modal .ev-ap-modal-header{
    min-height:54px;
    padding:13px 16px;
  }

  #modalPub.ev-ap-modal .ev-ap-modal-body-scroll{
    padding:12px;
  }

  #modalPub.ev-ap-modal .ev-ap-section{
    padding:14px;
  }

  #modalPub.ev-ap-modal .ev-ap-kv-item{
    grid-template-columns:1fr;
    gap:3px;
  }

  #modalPub.ev-ap-modal .ev-ap-kv-item > strong{
    text-align:left;
  }

  #modalPub.ev-ap-modal .ev-ap-modal-footer{
    flex-direction:column;
    align-items:stretch;
    padding:12px 14px 14px;
  }

  #modalPub.ev-ap-modal .ev-ap-modal-footer > .btn,
  #modalPub.ev-ap-modal .ev-ap-modal-actions .btn{
    width:100%;
  }

  #modalPub.ev-ap-modal .ev-ap-modal-actions{
    display:grid;
    grid-template-columns:1fr;
    width:100%;
  }

  #modalPub.ev-ap-modal .ev-ap-galeria{
    grid-template-columns:1fr;
  }

  #modalPub.ev-ap-modal .ev-ap-galeria img{
    height:210px;
  }
}


/* ============================================================
   AJUSTES DE LEGIBILIDAD — TABLA DE PUBLICACIONES
============================================================ */
.ev-table-frame .table-responsive{
  overflow-x:auto;
  -webkit-overflow-scrolling:touch;
  scrollbar-width:thin;
  scrollbar-color:rgba(15,89,47,.30) rgba(243,244,246,.75);
}
.ev-table-frame .table-responsive::-webkit-scrollbar{ height:9px; }
.ev-table-frame .table-responsive::-webkit-scrollbar-track{ background:#F3F4F6; }
.ev-table-frame .table-responsive::-webkit-scrollbar-thumb{
  border:2px solid #F3F4F6;
  border-radius:999px;
  background:rgba(15,89,47,.34);
}
.ev-table{
  min-width:1260px;
  table-layout:fixed;
}
.ev-col-fecha{ width:170px; }
.ev-col-publicacion{ width:145px; }
.ev-col-titulo{ width:300px; }
.ev-col-precio{ width:130px; }
.ev-col-usuario{ width:300px; }
.ev-col-estado{ width:145px; }
.ev-col-acciones{ width:150px; }
.ev-table th.ev-col-publicacion{
  min-width:145px;
  white-space:nowrap;
}
.ev-cell-publicacion,
.ev-cell-estado,
.ev-cell-acciones{
  white-space:nowrap;
}
.ev-cell-titulo{
  min-width:0;
  font-weight:800;
  color:#111827 !important;
}
.ev-table-title{
  display:block;
  width:100%;
  color:#111827;
  font-size:.90rem;
  font-weight:850;
  line-height:1.34;
  letter-spacing:0;
  white-space:normal;
  overflow-wrap:break-word;
  word-break:normal;
  hyphens:auto;
  text-rendering:optimizeLegibility;
  -webkit-font-smoothing:antialiased;
}
.ev-cell-usuario{
  line-height:1.38;
  white-space:normal;
  overflow-wrap:anywhere;
}

/* ============================================================
   VISOR AMPLIADO DE IMÁGENES — SOPORTE
============================================================ */
body.ev-ap-lightbox-open{
  overflow:hidden !important;
}
#modalPub.ev-ap-modal .ev-ap-image-button{
  position:relative;
  width:100%;
  display:block;
  padding:0;
  border:0;
  border-radius:14px;
  background:#fff;
  cursor:zoom-in;
  overflow:hidden;
  outline:0;
}
#modalPub.ev-ap-modal .ev-ap-image-button::after{
  content:"";
  position:absolute;
  inset:0;
  border:2px solid transparent;
  border-radius:14px;
  pointer-events:none;
  transition:border-color .18s ease,box-shadow .18s ease;
}
#modalPub.ev-ap-modal .ev-ap-image-button:hover::after,
#modalPub.ev-ap-modal .ev-ap-image-button:focus-visible::after{
  border-color:rgba(234,124,18,.72);
  box-shadow:inset 0 0 0 3px rgba(234,124,18,.10);
}
#modalPub.ev-ap-modal .ev-ap-image-button img{
  transition:transform .22s ease,filter .22s ease;
}
#modalPub.ev-ap-modal .ev-ap-image-button:hover img,
#modalPub.ev-ap-modal .ev-ap-image-button:focus-visible img{
  transform:scale(1.025);
  filter:brightness(.92);
}
#modalPub.ev-ap-modal .ev-ap-image-zoom{
  position:absolute;
  left:50%;
  bottom:12px;
  z-index:2;
  display:inline-flex;
  align-items:center;
  gap:7px;
  min-height:34px;
  padding:7px 11px;
  border:1px solid rgba(255,255,255,.30);
  border-radius:999px;
  color:#fff;
  background:rgba(15,89,47,.88);
  box-shadow:0 10px 24px rgba(15,23,42,.24);
  opacity:0;
  transform:translate(-50%,8px);
  transition:opacity .18s ease,transform .18s ease;
  pointer-events:none;
  backdrop-filter:blur(6px);
}
#modalPub.ev-ap-modal .ev-ap-image-zoom small{
  font-size:.72rem;
  font-weight:900;
}
#modalPub.ev-ap-modal .ev-ap-image-button:hover .ev-ap-image-zoom,
#modalPub.ev-ap-modal .ev-ap-image-button:focus-visible .ev-ap-image-zoom{
  opacity:1;
  transform:translate(-50%,0);
}
.ev-ap-lightbox[hidden]{ display:none !important; }
.ev-ap-lightbox{
  position:fixed;
  inset:0;
  z-index:11250;
  display:grid;
  place-items:center;
  padding:18px;
  opacity:0;
  visibility:hidden;
  transition:opacity .17s ease,visibility .17s ease;
}
.ev-ap-lightbox.is-open{
  opacity:1;
  visibility:visible;
}
.ev-ap-lightbox-backdrop{
  position:absolute;
  inset:0;
  background:rgba(3,12,8,.82);
  backdrop-filter:blur(8px);
  cursor:zoom-out;
}
.ev-ap-lightbox-dialog{
  position:relative;
  z-index:1;
  width:min(1120px,100%);
  max-height:calc(100dvh - 36px);
  display:flex;
  flex-direction:column;
  overflow:hidden;
  border:1px solid rgba(255,255,255,.16);
  border-radius:24px;
  background:#0B1F15;
  box-shadow:0 38px 100px rgba(0,0,0,.52);
  transform:translateY(8px) scale(.985);
  transition:transform .18s ease;
}
.ev-ap-lightbox.is-open .ev-ap-lightbox-dialog{
  transform:translateY(0) scale(1);
}
.ev-ap-lightbox-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:14px;
  padding:13px 16px;
  color:#fff;
  background:linear-gradient(135deg,#0F592F,#0E7A43);
}
.ev-ap-lightbox-kicker{
  display:block;
  margin-bottom:2px;
  color:rgba(255,255,255,.72);
  font-size:.66rem;
  font-weight:900;
  letter-spacing:.08em;
  text-transform:uppercase;
}
.ev-ap-lightbox-head h6{
  margin:0;
  color:#fff;
  font-size:.98rem;
  font-weight:900;
}
.ev-ap-lightbox-close{
  width:40px;
  height:40px;
  flex:0 0 auto;
  display:grid;
  place-items:center;
  border:1px solid rgba(255,255,255,.20);
  border-radius:13px;
  color:#fff;
  background:rgba(255,255,255,.10);
  font-size:1.05rem;
  transition:background .16s ease,transform .16s ease;
}
.ev-ap-lightbox-close:hover,
.ev-ap-lightbox-close:focus-visible{
  background:rgba(255,255,255,.20);
  transform:rotate(90deg);
  outline:0;
}
.ev-ap-lightbox-stage{
  position:relative;
  min-height:280px;
  flex:1 1 auto;
  display:grid;
  place-items:center;
  padding:18px 72px;
  overflow:hidden;
  background:
    radial-gradient(circle at center,rgba(255,255,255,.08),transparent 58%),
    #07140E;
  touch-action:pan-y;
}
.ev-ap-lightbox-stage img{
  display:block;
  max-width:100%;
  max-height:calc(100dvh - 190px);
  width:auto;
  height:auto;
  object-fit:contain;
  border-radius:12px;
  background:#fff;
  box-shadow:0 24px 62px rgba(0,0,0,.42);
}
.ev-ap-lightbox-nav{
  position:absolute;
  top:50%;
  z-index:2;
  width:46px;
  height:46px;
  display:grid;
  place-items:center;
  border:1px solid rgba(255,255,255,.20);
  border-radius:15px;
  color:#fff;
  background:rgba(15,89,47,.82);
  box-shadow:0 14px 30px rgba(0,0,0,.28);
  transform:translateY(-50%);
  transition:background .16s ease,transform .16s ease;
}
.ev-ap-lightbox-nav:hover,
.ev-ap-lightbox-nav:focus-visible{
  background:#EA7C12;
  outline:0;
}
.ev-ap-lightbox-prev{ left:14px; }
.ev-ap-lightbox-next{ right:14px; }
.ev-ap-lightbox-foot{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:11px 16px;
  color:rgba(255,255,255,.72);
  background:#0B1F15;
}
.ev-ap-lightbox-foot span{
  color:#fff;
  font-size:.82rem;
  font-weight:900;
}
.ev-ap-lightbox-foot small{
  font-size:.72rem;
  text-align:right;
}

@media(max-width:767.98px){
  .ev-table{ min-width:1080px; }
  .ev-col-fecha{ width:150px; }
  .ev-col-publicacion{ width:135px; }
  .ev-col-titulo{ width:270px; }
  .ev-col-precio{ width:115px; }
  .ev-col-usuario{ width:260px; }
  .ev-col-estado{ width:135px; }
  .ev-col-acciones{ width:135px; }

  #modalPub.ev-ap-modal .ev-ap-image-zoom{
    opacity:1;
    transform:translate(-50%,0);
  }

  .ev-ap-lightbox{
    padding:0;
  }
  .ev-ap-lightbox-dialog{
    width:100%;
    height:100dvh;
    max-height:none;
    border-radius:0;
  }
  .ev-ap-lightbox-stage{
    min-height:0;
    padding:12px 48px;
  }
  .ev-ap-lightbox-stage img{
    max-height:calc(100dvh - 150px);
    border-radius:9px;
  }
  .ev-ap-lightbox-nav{
    width:40px;
    height:40px;
    border-radius:13px;
  }
  .ev-ap-lightbox-prev{ left:6px; }
  .ev-ap-lightbox-next{ right:6px; }
  .ev-ap-lightbox-foot{
    align-items:flex-start;
    flex-direction:column;
    gap:3px;
  }
  .ev-ap-lightbox-foot small{ text-align:left; }
}

</style>
