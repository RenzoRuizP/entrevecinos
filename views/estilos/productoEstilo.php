<?php /* views/estilos/productoEstilo.php — Mis Productos premium + filtros + responsive */ ?>
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

  --ev-shadow-card:  0 14px 40px rgba(15,23,42,0.10);
  --ev-shadow-soft:  0 12px 28px rgba(15,23,42,0.06);

  --ev-radius-card:  18px;
  --ev-radius-modal: 22px;

  --ev-vh: 1vh;
  --ev-header-grad: linear-gradient(140deg,#0F592F 0%,#0E7A43 55%,#16A34A 100%);
}

/* PAGE */
.ev-mp-page{
  color:var(--ev-texto);
  padding:14px 14px 26px;
}

/* CARD */
.ev-card{
  border-radius:var(--ev-radius-card);
  background:#fff;
  border:1px solid rgba(148,163,184,0.22);
  box-shadow:var(--ev-shadow-card);
  overflow:hidden;
}

/* HERO */
.ev-mp-hero{
  background:
    radial-gradient(circle at 80% 20%, rgba(22,163,74,0.08), transparent 55%),
    radial-gradient(circle at 15% 80%, rgba(234,124,18,0.07), transparent 55%),
    #fff;
}
.ev-mp-hero-body{ padding:18px 18px 14px; }
.ev-mp-hero-top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:16px;
  flex-wrap:wrap;
}
.ev-mp-hero-right{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}
.ev-mp-title{
  font-size:2.05rem;
  font-weight:850;
  color:var(--ev-verde-oscuro);
  letter-spacing:0.01em;
  margin:0;
}
.ev-mp-subtitle{ color:var(--ev-gris-500); font-size:.95rem; }

.ev-mp-title-icon{
  width:44px;height:44px;
  border-radius:16px;
  background:rgba(187,247,208,0.55);
  border:1px solid rgba(22,163,74,0.20);
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  box-shadow:0 12px 22px rgba(15,23,42,0.06);
  font-size:1.15rem;
}

.ev-mp-hero-bottom{
  margin-top:14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
}
.ev-mp-meta-row{
  margin-top:10px;
  display:flex;
  justify-content:flex-end;
}
.ev-table-meta{ color:var(--ev-gris-500); font-size:.88rem; }

/* BUTTONS */
.ev-btn-orange{
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  border:none;
  color:#fff;
  border-radius:14px;
  padding:10px 18px;
  box-shadow:0 12px 26px rgba(234,124,18,0.35);
  transition:transform .18s ease, box-shadow .18s ease, filter .18s ease;
  font-weight:750;
}

.ev-btn-orange:hover{
  /* NO cambiar el degradado, solo “brillo + lift” como antes */
  filter: brightness(1.05);
  transform: translateY(-1px);
  box-shadow: 0 18px 32px rgba(255, 122, 26, 0.55);
  color:#fff;
}

/*
.ev-btn-orange:hover{
  filter: brightness(1.05);
  transform: translateY(-1px);
  box-shadow: 0 18px 32px rgba(255, 122, 26, 0.55);
  color:#fff;
}
.ev-btn-orange:active{
  transform: translateY(0);
  filter: brightness(1.00);
  box-shadow: 0 12px 26px rgba(255, 122, 26, 0.45);
}
.ev-btn-orange:focus{
  outline:none;
  box-shadow:
    0 0 0 3px rgba(17,24,39,.10),
    0 0 0 6px rgba(234,124,18,.18),
    0 18px 32px rgba(255, 122, 26, 0.50);
}
*/

.ev-btn-light{
  border-radius:999px;
  border:1px solid rgba(22,163,74,0.18);            /* ✅ borde más EV */
  background:rgba(255,255,255,0.92);                /* ✅ más limpio */
  color:var(--ev-verde-oscuro);
  font-weight:850;                                   /* ✅ más sólido */
  box-shadow:0 10px 20px rgba(15,23,42,0.06);        /* ✅ presencia */
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease, border-color .16s ease;
}

.ev-btn-light:hover{
  background:linear-gradient(90deg, rgba(187,247,208,0.55), rgba(187,247,208,0.18)); /* ✅ glow suave */
  border-color:rgba(22,163,74,0.32);
  transform:translateY(-1px);
  box-shadow:0 14px 26px rgba(15,23,42,0.10);
  filter:brightness(1.02);
}
.ev-tab.active{
  background:linear-gradient(90deg, rgba(187,247,208,0.55), rgba(187,247,208,0.20));
  border-color:rgba(22,163,74,0.30);
}

.ev-btn-outline{
  border:1px solid #D1D5DB;
  background:#fff;
  color:#4B5563;
  font-weight:850;
  border-radius:16px;
  padding:8px 12px;
  transition:transform .16s ease, box-shadow .16s ease, background .16s ease;
}
.ev-btn-outline:hover{
  background:#F3F4F6;
  color:#111827;
  transform:translateY(-1px);
  box-shadow:0 12px 22px rgba(15,23,42,0.08);
}

.ev-icon-btn{
  width:42px;height:42px;
  border-radius:14px;
  border:1px solid rgba(22,163,74,0.18);
  background:rgba(255,255,255,0.92);
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  box-shadow:0 10px 20px rgba(15,23,42,0.06);
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease, border-color .16s ease;
}
.ev-icon-btn:hover{
  background:linear-gradient(90deg, rgba(187,247,208,0.55), rgba(187,247,208,0.18));
  border-color:rgba(22,163,74,0.32);
  transform:translateY(-1px);
  box-shadow:0 14px 26px rgba(15,23,42,0.10);
  filter:brightness(1.02);
}

/* SUMMARY */
.ev-summary-pill{
  display:inline-flex;
  align-items:center;
  gap:10px;
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
.ev-pill{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:28px;
  padding:1px 10px;
  border-radius:999px;
  background:rgba(255,255,255,0.92);
  border:1px solid rgba(22,163,74,0.18);
  font-weight:900;
  margin-left:8px;
}

/* HEADER/BODY/FOOTER */
.ev-card-header{ padding:14px 16px; border-bottom:1px solid var(--ev-gris-200); background:#fff; }
.ev-card-header-row{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
.ev-card-title{ margin:0; font-size:1.05rem; font-weight:900; color:var(--ev-verde-oscuro); }
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
.ev-hint{ color:var(--ev-gris-600); font-size:.90rem; }

/* INPUTS */
.ev-input-icon{ position:absolute; top:50%; left:14px; transform:translateY(-50%); color:#9ca3af; }
.ev-input{
  border-radius:12px;
  border:1px solid rgba(22,163,74,0.20);
  transition:all .18s ease-out;
}
.ev-input:focus{
  border-color:var(--ev-verde);
  box-shadow:0 0 0 3px rgba(22,163,74,0.18);
  outline:none;
}

/* TABLE */
.ev-table-wrap{ padding:0 14px 14px; background:#fff; }
.ev-table-frame{
  border:1px solid rgba(148,163,184,0.22);
  border-radius:16px;
  overflow:hidden;
  background:linear-gradient(180deg,#ffffff 0%, #fbfbfc 100%);
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
  background:linear-gradient(180deg,var(--ev-gris-050) 0%, #ffffff 100%);
  color:#0f172a;
  font-weight:900;
  border-bottom:1px solid var(--ev-gris-200) !important;
  white-space:nowrap;
  padding:14px 16px !important;
}
.ev-table tbody td{
  vertical-align:middle;
  border-bottom:1px solid rgba(229,231,235,0.9);
  padding:14px 16px !important;
  background:#fff;
}
.ev-table tbody tr:nth-child(even) td{ background:rgba(249,250,251,0.55); }
.ev-table tbody tr:hover td{ background:rgba(236,253,245,0.65); }
.ev-table th + th, .ev-table td + td{ border-left:1px solid rgba(229,231,235,0.55); }

.ev-col-codigo{ width:110px; }
.ev-col-titulo{ width:260px; }
.ev-col-precio{ width:140px; }
.ev-col-estado{ width:140px; }
.ev-col-tipo{ width:170px; }
.ev-col-categoria{ width:190px; }
.ev-col-desc{ width:auto; }
.ev-col-acciones{ width:260px; }

.ev-empty{ color:var(--ev-gris-500); font-weight:800; background:#fff !important; }
.ev-empty-wrap{ display:flex; flex-direction:column; align-items:center; gap:8px; }
.ev-empty-ico{ font-size:1.8rem; color:rgba(15,89,47,0.35); }
.ev-empty-text{ color:var(--ev-gris-600); font-weight:700; }

.td-trunc{ white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ev-code{ font-weight:900; color:var(--ev-verde-oscuro); letter-spacing:.04em; }

/* BADGES (producto estado: Nuevo/Usado/NoAplica) */
.ev-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:6px 10px;
  border-radius:999px;
  font-size:.74rem;
  font-weight:900;
  border:1px solid rgba(148,163,184,.24);
  background:linear-gradient(180deg, rgba(248,250,252,.98) 0%, rgba(241,245,249,.88) 100%);
}
.ev-badge--nuevo{ color:rgba(15,89,47,.92); }
.ev-badge--usado{ color:rgba(122,90,0,.92); }
.ev-badge--noaplica{ color:rgba(71,85,105,.92); background:rgba(243,244,246,.85); }

/* ACTIONS + CHIPS */
.ev-actions{
  display:inline-flex;
  align-items:center;
  justify-content:flex-end;
  gap:10px;
  flex-wrap:nowrap;
  min-height:40px;
  width:100%;
}
.ev-chip{
  min-width:98px;
  justify-content:center;
  border-radius:999px;
  padding:0.44rem 0.95rem;
  font-weight:900;
  font-size:.86rem;
  background:#fff;
  border:1px solid rgba(148,163,184,.34);
  box-shadow:0 10px 18px rgba(15,23,42,0.06);
  transition:transform .15s ease, box-shadow .15s ease;
  white-space:nowrap;
  display:inline-flex;
  align-items:center;
}
.ev-chip:hover{ transform:translateY(-1px); box-shadow:0 14px 22px rgba(15,23,42,0.08); }
.ev-chip:disabled, .ev-chip[disabled]{ opacity:.78; transform:none; cursor:default; }

.ev-chip-green{
  color:rgba(15,89,47,.95);
  border:1px solid rgba(15,89,47,.26);
  background:linear-gradient(180deg, rgba(230,244,236,.95) 0%, rgba(255,255,255,.92) 100%);
}
.ev-chip-red{ border-color:rgba(220,38,38,.34); color:#DC2626; }
.ev-chip-amber{ border-color:rgba(255,122,26,.36); color:var(--ev-naranja); }
.ev-chip-orange{ border-color:rgba(234,124,18,.35); color:#9A3412; background:rgba(234,124,18,.08); }
.ev-chip-gray{ border-color:rgba(107,114,128,.28); color:rgba(55,65,81,.92); background:rgba(243,244,246,.78); }

/* MODAL */
.ev-modal{ --bs-modal-margin: 12px; }
.ev-modal .modal-dialog{
  width:calc(100% - (var(--bs-modal-margin) * 2));
  margin:var(--bs-modal-margin) auto;
}
.ev-modal-xl{ max-width:980px; }
@media (min-width: 992px){ .ev-modal-xl{ max-width:1040px; } }

.ev-modal-content{
  border-radius:var(--ev-radius-modal);
  overflow:hidden;
  border:0;
  box-shadow:0 22px 60px rgba(15,23,42,0.35);
}
.ev-modal-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:16px 24px;
  background-image:var(--ev-header-grad);
  color:#fff;
}
.ev-modal-title{
  font-size:1.1rem;
  font-weight:700;
  color:#fff !important;
  display:flex;
  align-items:center;
  gap:8px;
}
.ev-modal-body{ padding:22px 26px; background:#fff; }
.ev-modal-footer{
  padding:14px 26px 20px 26px;
  background:#fff;
  border-top:1px solid rgba(229,231,235,0.9);
  display:flex;
  justify-content:flex-end;
  gap:.75rem;
}
.ev-modal-flex{ display:flex; flex-direction:column; min-height:0; }
.ev-modal-body-scroll{ overflow:auto; -webkit-overflow-scrolling:touch; min-height:0; }

.ev-modal .modal-dialog{
  max-height:calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2));
}
.ev-modal .modal-content{
  max-height:calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2));
}
.ev-modal .ev-modal-body-scroll{
  max-height:calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2) - 64px - 72px);
}

/* SECTIONS / DROPZONE / TILES / PREVIEW (tu base) */
.ev-section{
  border:1px solid rgba(229,231,235,.9);
  border-radius:16px;
  background:#fff;
  box-shadow:var(--ev-shadow-soft);
  padding:16px;
}
.ev-section-title{ font-weight:800; color:var(--ev-texto); margin-bottom:4px; }
.ev-section-subtitle{ color:var(--ev-gris-500); font-size:.9rem; }

.ev-dropzone{
  border:2px dashed rgba(148,163,184,.55);
  border-radius:16px;
  padding:18px 14px;
  text-align:center;
  cursor:pointer;
  background:#F9FAFB;
  transition:border-color .15s ease, background-color .15s ease, transform .15s ease;
  position:relative;
  z-index:2;
  pointer-events:auto;
}
.ev-dropzone .ico{ font-size:1.6rem; color:var(--ev-verde); margin-bottom:6px; }
.ev-dropzone .t1{ font-weight:800; color:var(--ev-verde-oscuro); }
.ev-dropzone .t2{ color:var(--ev-gris-500); font-size:.86rem; }
.ev-dropzone.drag-over{
  border-color:rgba(25,135,84,.65);
  background:rgba(230,244,236,.9);
  transform:translateY(-1px);
}

.ev-tiles{ display:flex; flex-wrap:wrap; gap:10px; }
#evTiles:empty, #evTilesEdit:empty{ display:none; }

@supports selector(:has(*)) {
  .ev-section:has(#evTiles:not(:empty)) .ev-dropzone,
  .ev-section:has(#evTilesEdit:not(:empty)) .ev-dropzone{ display:none !important; }
}
.ev-section.ev-has-tiles .ev-dropzone{ display:none !important; }

.ev-tile{
  width:86px; height:86px;
  border-radius:14px;
  border:1px solid rgba(148,163,184,.35);
  overflow:hidden;
  position:relative;
  background:#fff;
  box-shadow:0 8px 18px rgba(15,23,42,0.06);
}
.ev-tile img{ width:100%; height:100%; object-fit:cover; display:block; }
.ev-tile-remove{
  position:absolute;
  top:6px; right:6px;
  width:26px; height:26px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.42);
  background:rgba(15,23,42,0.58);
  color:#fff;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  font-weight:900;
  font-size:16px;
  line-height:1;
  box-shadow:0 8px 18px rgba(15,23,42,0.22);
  backdrop-filter:blur(6px);
  opacity:.92;
  transition:transform .15s ease, opacity .15s ease;
}
.ev-tile:hover .ev-tile-remove{ opacity:1; transform:translateY(-1px); }

.ev-preview-area{
  border:1px dashed rgba(148,163,184,.55);
  border-radius:16px;
  background:#F9FAFB;
  padding:12px;
}
.ev-preview-title{
  font-weight:900;
  color:var(--ev-verde-oscuro);
  display:flex;
  align-items:center;
  gap:8px;
  margin-bottom:10px;
}
.ev-preview-main{
  border-radius:14px;
  overflow:hidden;
  border:1px solid rgba(148,163,184,.22);
  background:#fff;
}
.ev-preview-main img{ width:100%; height:auto; display:block; }
.ev-preview-thumbs{ display:flex; gap:10px; margin-top:10px; }
.ev-preview-thumb{
  width:64px; height:48px;
  border-radius:12px;
  overflow:hidden;
  border:1px solid rgba(148,163,184,.3);
  cursor:pointer;
  background:#fff;
}
.ev-preview-thumb.active{ outline:2px solid rgba(25,135,84,.55); }
.ev-preview-thumb img{ width:100%; height:100%; object-fit:cover; display:block; }

/* RESPONSIVE: TABLE -> CARDS */
@media (max-width: 768px){
  .ev-mp-title{ font-size:1.65rem; }
  .ev-table thead{ display:none; }
  .ev-table, .ev-table tbody, .ev-table tr, .ev-table td{ display:block; width:100%; }

  .ev-table tbody tr{
    margin:10px 10px 12px;
    border:1px solid rgba(148,163,184,0.22);
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 10px 22px rgba(15,23,42,0.06);
    background:#fff;
  }
  .ev-table tbody td{
    border-left:none !important;
    border-bottom:1px dashed rgba(229,231,235,0.9);
    padding:12px 14px !important;
    background:#fff !important;
  }
  .ev-table tbody td:last-child{ border-bottom:none; }

  .ev-table tbody td::before{
    content: attr(data-label);
    display:block;
    font-size:.78rem;
    font-weight:900;
    color:var(--ev-gris-500);
    text-transform:uppercase;
    letter-spacing:.03em;
    margin-bottom:4px;
  }
  .ev-actions{ justify-content:flex-start; flex-wrap:wrap; }
}

@media (max-width: 575.98px){
  .ev-modal-body{ padding:18px 16px; }
  .ev-modal-footer{
    padding:12px 16px 16px 16px;
    flex-direction:column;
    align-items:stretch;
  }
  .ev-modal-footer .btn{ width:100%; justify-content:center; }
}
</style>