<?php /* views/estilos/productoEstilo.php — Mis Publicaciones limpio, consolidado y sin parches */ ?>
<style>
/* ==========================================================
   1. VARIABLES EV
========================================================== */
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#16A34A;
  --ev-verde-medio:#198754;
  --ev-verde-suave:#E6F4EC;
  --ev-verde-claro:#BBF7D0;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;
  --ev-naranja-suave:#FFF7ED;

  --ev-rojo:#DC2626;
  --ev-ambar:#F59E0B;
  --ev-purple:#6D28D9;

  --ev-gris-025:#FCFDFC;
  --ev-gris-050:#F9FAFB;
  --ev-gris-100:#F3F4F6;
  --ev-gris-150:#EEF2F7;
  --ev-gris-200:#E5E7EB;
  --ev-gris-300:#D1D5DB;
  --ev-gris-400:#9CA3AF;
  --ev-gris-500:#6B7280;
  --ev-gris-600:#4B5563;
  --ev-gris-700:#374151;

  --ev-texto:#111827;
  --ev-texto-suave:#6B7280;

  --ev-shadow-card:0 16px 44px rgba(15,23,42,.10);
  --ev-shadow-soft:0 12px 28px rgba(15,23,42,.06);
  --ev-shadow-chip:0 10px 18px rgba(15,23,42,.06);
  --ev-shadow-modal:0 26px 70px rgba(15,23,42,.34);

  --ev-radius-card:18px;
  --ev-radius-modal:22px;
  --ev-radius-soft:16px;

  --ev-vh:1vh;
  --ev-header-grad:linear-gradient(140deg,#0F592F 0%,#0E7A43 55%,#16A34A 100%);
}

/* ==========================================================
   2. PÁGINA, CARDS Y HERO
========================================================== */
.ev-mp-page{
  color:var(--ev-texto);
  padding:14px 14px 26px;
}

.ev-card{
  border-radius:var(--ev-radius-card);
  background:#fff;
  border:1px solid rgba(148,163,184,.22);
  box-shadow:var(--ev-shadow-card);
  overflow:hidden;
}

.ev-mp-hero{
  background:
    radial-gradient(circle at 82% 20%, rgba(22,163,74,.08), transparent 55%),
    radial-gradient(circle at 14% 80%, rgba(234,124,18,.07), transparent 55%),
    linear-gradient(180deg,#fff 0%,#fbfdfb 100%);
}

.ev-mp-hero-body{
  padding:18px 18px 14px;
}

.ev-mp-hero-top,
.ev-mp-hero-bottom,
.ev-card-header-row{
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
  letter-spacing:.01em;
  margin:0;
}

.ev-mp-subtitle{
  color:var(--ev-texto-suave);
  font-size:.95rem;
}

.ev-mp-title-icon{
  width:44px;
  height:44px;
  border-radius:16px;
  background:rgba(187,247,208,.55);
  border:1px solid rgba(22,163,74,.20);
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  box-shadow:0 12px 22px rgba(15,23,42,.06);
  font-size:1.15rem;
}

.ev-mp-hero-bottom{
  align-items:center;
  margin-top:14px;
  gap:12px;
}

.ev-mp-meta-row{
  margin-top:10px;
  display:flex;
  justify-content:flex-end;
}

.ev-table-meta{
  color:var(--ev-gris-500);
  font-size:.88rem;
}

.ev-card-header{
  padding:14px 16px;
  border-bottom:1px solid var(--ev-gris-200);
  background:#fff;
}

.ev-card-title{
  margin:0;
  font-size:1.05rem;
  font-weight:900;
  color:var(--ev-verde-oscuro);
}

.ev-card-body{
  padding:16px;
}

.ev-card-footer{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:12px 16px;
  border-top:1px solid var(--ev-gris-200);
  background:#fff;
}

.ev-footer-left{
  color:var(--ev-gris-500);
  font-weight:650;
  font-size:.9rem;
}

.ev-footer-right{
  display:flex;
  align-items:center;
  gap:10px;
}

.ev-hint{
  color:var(--ev-gris-600);
  font-size:.90rem;
}

/* ==========================================================
   3. BOTONES, TABS Y RESÚMENES
========================================================== */
.ev-btn-orange,
.btn-ev-orange{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  border:none;
  color:#fff;
  border-radius:14px;
  padding:10px 18px;
  box-shadow:0 12px 26px rgba(234,124,18,.35);
  transition:transform .18s ease, box-shadow .18s ease, filter .18s ease;
  font-weight:850;
}

.ev-btn-orange:hover,
.btn-ev-orange:hover{
  filter:brightness(1.05);
  transform:translateY(-1px);
  box-shadow:0 18px 32px rgba(255,122,26,.50);
  color:#fff;
}

.ev-btn-light{
  border-radius:999px;
  border:1px solid rgba(22,163,74,.18);
  background:rgba(255,255,255,.92);
  color:var(--ev-verde-oscuro);
  font-weight:850;
  box-shadow:0 10px 20px rgba(15,23,42,.06);
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease, border-color .16s ease;
}

.ev-btn-light:hover,
.ev-tab.active{
  background:linear-gradient(90deg,rgba(187,247,208,.58),rgba(187,247,208,.20));
  border-color:rgba(22,163,74,.32);
  color:var(--ev-verde-oscuro);
  transform:translateY(-1px);
  box-shadow:0 14px 26px rgba(15,23,42,.10);
  filter:brightness(1.02);
}

.ev-btn-outline,
.btn-ev-outline{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  border:1px solid rgba(148,163,184,.50);
  background:linear-gradient(180deg,#fff 0%,#F9FAFB 100%);
  color:var(--ev-gris-700);
  font-weight:850;
  border-radius:16px;
  padding:8px 12px;
  box-shadow:0 10px 22px rgba(15,23,42,.06);
  transition:transform .16s ease, box-shadow .16s ease, background .16s ease, border-color .16s ease, color .16s ease;
}

.ev-btn-outline:hover,
.btn-ev-outline:hover,
.ev-btn-outline:focus,
.btn-ev-outline:focus{
  background:linear-gradient(180deg,#F9FAFB 0%,#F3F4F6 100%);
  border-color:rgba(107,114,128,.46);
  color:#1F2937;
  transform:translateY(-1px);
  box-shadow:
    0 12px 24px rgba(15,23,42,.08),
    0 0 0 3px rgba(107,114,128,.10);
}

.ev-icon-btn{
  width:42px;
  height:42px;
  border-radius:14px;
  border:1px solid rgba(22,163,74,.18);
  background:rgba(255,255,255,.92);
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  box-shadow:0 10px 20px rgba(15,23,42,.06);
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease, border-color .16s ease;
}

.ev-icon-btn:hover{
  background:linear-gradient(90deg,rgba(187,247,208,.55),rgba(187,247,208,.18));
  border-color:rgba(22,163,74,.32);
  transform:translateY(-1px);
  box-shadow:0 14px 26px rgba(15,23,42,.10);
  filter:brightness(1.02);
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

.ev-summary-label{
  color:#14532D;
  font-weight:850;
}

.ev-summary-count,
.ev-pill{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border-radius:999px;
  background:rgba(255,255,255,.92);
  border:1px solid rgba(22,163,74,.18);
  font-weight:900;
  color:var(--ev-verde-oscuro);
}

.ev-summary-count{
  min-width:34px;
  padding:2px 10px;
}

.ev-pill{
  min-width:28px;
  padding:1px 10px;
  margin-left:8px;
}

/* ==========================================================
   4. FORMULARIOS Y FILTROS
========================================================== */
.ev-input-icon{
  position:absolute;
  top:50%;
  left:14px;
  transform:translateY(-50%);
  color:#9CA3AF;
}

.ev-input,
.ev-modal .form-control,
.ev-modal .form-select{
  border-radius:12px;
  border:1px solid rgba(22,163,74,.20);
  transition:border-color .18s ease, box-shadow .18s ease, background .18s ease;
}

.ev-input:focus,
.ev-modal .form-control:focus,
.ev-modal .form-select:focus{
  border-color:var(--ev-verde);
  box-shadow:0 0 0 3px rgba(22,163,74,.18);
  outline:none;
}

.ev-modal .form-label,
.ev-filters .form-label{
  font-size:.93rem;
  font-weight:600;
  color:#1F2937;
  letter-spacing:-.005em;
}

/* ==========================================================
   5. TABLA DE PUBLICACIONES
========================================================== */
.ev-table-wrap{
  padding:0 14px 14px;
  background:#fff;
}

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
  color:#0F172A;
  font-weight:900;
  border-bottom:1px solid var(--ev-gris-200) !important;
  white-space:normal;
  word-break:break-word;
  line-height:1.18;
  padding:14px 12px !important;
  text-align:center !important;
  vertical-align:middle;
}

.ev-table tbody td{
  vertical-align:middle;
  border-bottom:1px solid rgba(229,231,235,.9);
  padding:14px 14px !important;
  background:#fff;
}

.ev-table tbody tr:nth-child(even) td{
  background:rgba(249,250,251,.58);
}

.ev-table tbody tr:hover td{
  background:linear-gradient(180deg,rgba(236,253,245,.72) 0%,rgba(249,250,251,.92) 100%);
}

.ev-table th + th,
.ev-table td + td{
  border-left:1px solid rgba(229,231,235,.55);
}

.ev-table tbody td.text-end{
  text-align:right !important;
}

.ev-table tbody td.text-center{
  text-align:center !important;
}

.ev-col-codigo{ width:86px; }
.ev-col-publicacion{ width:118px; }
.ev-col-titulo{ width:180px; }
.ev-col-precio{ width:104px; }
.ev-col-tipo{ width:122px; }
.ev-col-categoria{ width:136px; }
.ev-col-desc{ width:180px; }
.ev-col-mensaje{ width:220px; }
.ev-col-estado-publicacion{ width:150px; }
.ev-col-acciones{ width:210px; }

.td-trunc{
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.ev-code{
  font-weight:900;
  color:var(--ev-verde-oscuro);
  letter-spacing:.04em;
}

.ev-empty{
  color:var(--ev-gris-500);
  font-weight:800;
  background:#fff !important;
}

.ev-empty-wrap{
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:8px;
}

.ev-empty-ico{
  font-size:1.8rem;
  color:rgba(15,89,47,.35);
}

.ev-empty-text{
  color:var(--ev-gris-600);
  font-weight:700;
}

/* ==========================================================
   6. CHIPS, ACCIONES Y MENSAJE DE SOPORTE
========================================================== */
.ev-actions{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:8px;
  flex-wrap:nowrap;
  min-height:42px;
  width:100%;
}

.ev-chip{
  min-width:90px;
  justify-content:center;
  border-radius:999px;
  padding:.48rem .90rem;
  font-weight:900;
  font-size:.83rem;
  background:#fff;
  border:1px solid rgba(148,163,184,.34);
  box-shadow:var(--ev-shadow-chip);
  transition:transform .15s ease, box-shadow .15s ease, filter .15s ease;
  white-space:nowrap;
  display:inline-flex;
  align-items:center;
  gap:6px;
}

.ev-chip:hover{
  transform:translateY(-1px);
  box-shadow:0 14px 22px rgba(15,23,42,.08);
}

.ev-chip:disabled,
.ev-chip[disabled]{
  opacity:.88;
  transform:none;
  cursor:default;
  box-shadow:none;
}

.ev-chip-green{
  color:rgba(15,89,47,.95);
  border-color:rgba(15,89,47,.24);
  background:linear-gradient(180deg,rgba(230,244,236,.96) 0%,rgba(255,255,255,.94) 100%);
}

.ev-chip-red{
  border-color:rgba(220,38,38,.28);
  color:var(--ev-rojo);
  background:linear-gradient(180deg,rgba(254,242,242,.96) 0%,rgba(255,255,255,.94) 100%);
}

.ev-chip-amber,
.ev-chip-orange{
  border-color:rgba(234,124,18,.30);
  color:#9A3412;
  background:linear-gradient(180deg,rgba(255,247,237,.98) 0%,rgba(255,255,255,.94) 100%);
}

.ev-chip-gray{
  border-color:rgba(107,114,128,.22);
  color:rgba(55,65,81,.92);
  background:linear-gradient(180deg,rgba(243,244,246,.88) 0%,rgba(255,255,255,.94) 100%);
}

.ev-chip-status{
  min-width:118px;
  padding:.48rem .90rem;
  border-radius:999px;
  font-weight:900;
  font-size:.82rem;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  white-space:nowrap;
  border:1px dashed rgba(148,163,184,.42);
  box-shadow:none;
  cursor:default;
  pointer-events:none;
  opacity:1;
}

.ev-msg-support{
  display:block;
  line-height:1.38;
  font-size:.87rem;
  white-space:normal;
  word-break:break-word;
}

.ev-msg-support-box{
  display:flex;
  flex-direction:column;
  justify-content:center;
  min-height:78px;
  padding:10px 12px;
  border-radius:15px;
  background:linear-gradient(180deg,rgba(249,250,251,.96) 0%,rgba(243,244,246,.94) 100%);
  border:1px solid rgba(229,231,235,.95);
  box-shadow:0 10px 24px rgba(15,23,42,.05);
  transition:transform .16s ease, box-shadow .16s ease;
}

.ev-table tbody tr:hover .ev-msg-support-box{
  transform:translateY(-1px);
  box-shadow:0 14px 28px rgba(15,23,42,.06);
}

.ev-msg-support-box.is-alert{
  background:linear-gradient(180deg,rgba(255,247,237,.98) 0%,rgba(255,237,213,.95) 100%);
  border-color:rgba(234,124,18,.20);
}

.ev-msg-support-box.is-empty{
  background:linear-gradient(180deg,rgba(252,253,252,.98) 0%,rgba(247,249,248,.95) 100%);
  border-color:rgba(209,213,219,.85);
}

.ev-msg-support-title{
  display:block;
  font-size:.66rem;
  font-weight:900;
  letter-spacing:.06em;
  text-transform:uppercase;
  color:#9A3412;
  margin-bottom:5px;
}

.ev-msg-support-box.is-empty .ev-msg-support-title{
  color:var(--ev-gris-500);
}

.ev-msg-support-text{
  display:block;
  color:var(--ev-gris-700);
  white-space:normal;
  word-break:break-word;
  line-height:1.42;
}

.ev-msg-support-box.is-empty .ev-msg-support-text{
  color:#8B95A7;
  font-style:italic;
}

/* ==========================================================
   7. MODAL BASE
   Versión consolidada: header verde EV sin línea superior
========================================================== */
.ev-modal{
  --bs-modal-margin:10px;
}

.ev-modal .modal-dialog{
  width:calc(100% - (var(--bs-modal-margin) * 2));
  margin:var(--bs-modal-margin) auto;
  border:0;
  outline:0;
  background:transparent;
}

.ev-modal-xl{
  max-width:980px;
}

@media (min-width: 992px){
  .ev-modal-xl{
    max-width:1080px;
  }
}

/*
  La clave para eliminar la línea superior:
  el contenedor completo usa fondo verde EV.
  Así, si el header deja ver 1px por el border-radius/render del navegador,
  se ve verde y no una línea blanca/gris.
*/
.ev-modal .modal-content.ev-modal-content,
.ev-modal-content{
  --bs-modal-bg:transparent;
  --bs-modal-border-width:0;
  --bs-modal-border-color:transparent;
  --bs-modal-border-radius:var(--ev-radius-modal);

  border:0;
  outline:0;
  padding:0;
  border-radius:var(--ev-radius-modal);
  overflow:hidden;
  background:var(--ev-header-grad);
  box-shadow:var(--ev-shadow-modal);
}

/* Header EV completo, limpio y pegado al borde superior */
.ev-modal-header{
  flex:0 0 auto;
  display:flex;
  align-items:center;
  justify-content:space-between;
  min-height:56px;
  padding:14px 24px;
  margin:0;
  border:0;
  outline:0;
  border-radius:var(--ev-radius-modal) var(--ev-radius-modal) 0 0;
  background:var(--ev-header-grad);
  color:#fff;
  box-shadow:none;
}

.ev-modal-header::before,
.ev-modal-header::after,
.ev-modal-content::before,
.ev-modal-content::after{
  content:none;
  display:none;
}

.ev-modal-title{
  font-size:1.08rem;
  font-weight:850;
  color:#fff !important;
  display:flex;
  align-items:center;
  gap:8px;
  letter-spacing:-.01em;
}

.ev-modal-close-icon{
  opacity:.95;
  transition:transform .16s ease, opacity .16s ease, filter .16s ease;
}

.ev-modal-close-icon:hover{
  opacity:1;
  filter:brightness(1.05);
  transform:rotate(90deg) scale(1.04);
}

/* El formulario ocupa el área blanca debajo del header */
.ev-modal .ev-modal-flex{
  display:flex;
  flex-direction:column;
  min-height:0;
  background:#fff;
  border-radius:0 0 var(--ev-radius-modal) var(--ev-radius-modal);
}

.ev-modal-body{
  padding:18px 24px;
  background:#fff;
}

.ev-modal-footer{
  flex:0 0 auto;
  min-height:66px;
  padding:12px 24px 16px;
  background:#fff;
  border-top:1px solid rgba(229,231,235,.92);
  display:flex;
  justify-content:flex-end;
  align-items:center;
  gap:.75rem;
  box-shadow:0 -10px 24px rgba(15,23,42,.035);
}

.ev-modal-body-scroll{
  overflow:auto;
  -webkit-overflow-scrolling:touch;
  min-height:0;
  padding-bottom:36px;
  scrollbar-width:thin;
  scrollbar-color:rgba(107,114,128,.42) transparent;
}

.ev-modal-body-scroll::-webkit-scrollbar{
  width:8px;
}

.ev-modal-body-scroll::-webkit-scrollbar-track{
  background:transparent;
}

.ev-modal-body-scroll::-webkit-scrollbar-thumb{
  background:rgba(107,114,128,.34);
  border-radius:999px;
  border:2px solid #fff;
}

.ev-modal-body-scroll::-webkit-scrollbar-thumb:hover{
  background:rgba(107,114,128,.48);
}

.ev-modal .modal-dialog,
.ev-modal .modal-content{
  max-height:calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2));
}

.ev-modal .ev-modal-body-scroll{
  max-height:calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2) - 56px - 66px);
}

/* Cancelar: neutral EV, sin rojo */
.ev-modal-footer .btn-ev-outline,
.ev-modal-footer .btn-ev-outline:visited{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  border-color:rgba(148,163,184,.50);
  color:#374151;
  background:linear-gradient(180deg,#FFFFFF 0%,#F9FAFB 100%);
  box-shadow:0 10px 22px rgba(15,23,42,.06);
}

.ev-modal-footer .btn-ev-outline:hover,
.ev-modal-footer .btn-ev-outline:focus,
.ev-modal-footer .btn-ev-outline:active{
  border-color:rgba(107,114,128,.46);
  color:#1F2937;
  background:linear-gradient(180deg,#F9FAFB 0%,#F3F4F6 100%);
  box-shadow:
    0 12px 24px rgba(15,23,42,.08),
    0 0 0 3px rgba(107,114,128,.10);
  transform:translateY(-1px);
}


/* ==========================================================
   8. MODAL PUBLICACIÓN: LAYOUT, PASOS Y SELECTOR
========================================================== */
.ev-section{
  border:1px solid rgba(209,213,219,.78);
  border-radius:17px;
  background:#fff;
  box-shadow:0 14px 34px rgba(15,23,42,.055);
  padding:15px;
}

.ev-section-title{
  font-size:1.01rem;
  font-weight:700;
  color:var(--ev-texto);
  margin-bottom:4px;
  letter-spacing:-.01em;
}

.ev-section-subtitle{
  color:var(--ev-gris-500);
  font-size:.9rem;
  line-height:1.35;
}

.ev-step-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  margin-bottom:6px;
  padding:4px 9px;
  border-radius:999px;
  background:rgba(230,244,236,.78);
  border:1px solid rgba(22,163,74,.16);
  color:var(--ev-verde-oscuro);
  font-size:.72rem;
  font-weight:900;
  text-transform:uppercase;
  letter-spacing:.04em;
}

.ev-kind-section{
  background:#fff;
}

.ev-publicacion-choice-grid{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:12px;
}

.ev-publicacion-option{
  position:relative;
  min-height:76px;
  border:1.6px solid rgba(234,124,18,.26);
  background:linear-gradient(180deg,#FFFFFF 0%,#FFFDFC 100%);
  border-radius:18px;
  padding:13px 42px 13px 14px;
  display:flex;
  align-items:center;
  gap:12px;
  cursor:pointer;
  box-shadow:
    0 14px 30px rgba(15,23,42,.045),
    inset 0 1px 0 rgba(255,255,255,.92);
  transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease, background .16s ease;
}

.ev-publicacion-option:hover{
  transform:translateY(-1px);
  border-color:rgba(234,124,18,.55);
  background:
    radial-gradient(circle at 92% 14%,rgba(245,158,11,.10),transparent 34%),
    linear-gradient(180deg,#FFFFFF 0%,#FFF9F2 100%);
  box-shadow:
    0 16px 34px rgba(234,124,18,.10),
    0 8px 18px rgba(15,23,42,.045);
}

.ev-publicacion-option-ico{
  flex:0 0 38px;
  width:38px;
  height:38px;
  border-radius:14px;
  display:grid;
  place-items:center;
  background:linear-gradient(180deg,#FFFFFF 0%,#F9FAFB 100%);
  color:#64748B;
  border:1px solid rgba(203,213,225,.80);
  font-size:1.05rem;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.90);
}

.ev-publicacion-option-copy{
  display:flex;
  flex-direction:column;
  min-width:0;
}

.ev-publicacion-option-copy strong{
  color:#111827;
  font-size:.98rem;
  font-weight:900;
  line-height:1.15;
}

.ev-publicacion-option-copy small{
  margin-top:3px;
  color:#6B7280;
  font-weight:700;
  line-height:1.24;
}

.ev-publicacion-option-check{
  position:absolute;
  top:10px;
  right:10px;
  width:24px;
  height:24px;
  border-radius:999px;
  display:grid;
  place-items:center;
  border:1px solid rgba(203,213,225,.95);
  color:transparent;
  background:#fff;
  transition:all .16s ease;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.95);
}

.btn-check:checked + .ev-publicacion-option,
.ev-publicacion-option.is-active{
  border-color:rgba(234,124,18,.98);
  background:
    radial-gradient(circle at 92% 14%,rgba(245,158,11,.26),transparent 36%),
    linear-gradient(135deg,rgba(255,247,237,1) 0%,rgba(255,237,213,.88) 58%,rgba(255,255,255,.98) 100%);
  box-shadow:
    0 20px 42px rgba(234,124,18,.18),
    inset 0 1px 0 rgba(255,255,255,.95);
}

.btn-check:checked + .ev-publicacion-option .ev-publicacion-option-ico,
.ev-publicacion-option.is-active .ev-publicacion-option-ico{
  background:#fff;
  border-color:rgba(234,124,18,.28);
  color:var(--ev-naranja);
}

.btn-check:checked + .ev-publicacion-option .ev-publicacion-option-copy strong,
.ev-publicacion-option.is-active .ev-publicacion-option-copy strong{
  color:#9A3412;
}

.btn-check:checked + .ev-publicacion-option .ev-publicacion-option-copy small,
.ev-publicacion-option.is-active .ev-publicacion-option-copy small{
  color:#7C2D12;
}

.btn-check:checked + .ev-publicacion-option .ev-publicacion-option-check,
.ev-publicacion-option.is-active .ev-publicacion-option-check{
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  color:#fff;
  border-color:rgba(234,124,18,.95);
  box-shadow:0 8px 18px rgba(234,124,18,.28);
}

[data-ev-product-only].d-none,
[data-ev-service-only].d-none{
  display:none !important;
}

.ev-service-hint{
  color:var(--ev-verde-oscuro);
  font-weight:650;
}

/* Layout 2 columnas controlado por CSS Grid */
@media (min-width: 860px){
  .ev-modal .ev-publicacion-modal-grid{
    display:grid !important;
    grid-template-columns:minmax(0,58%) minmax(320px,42%) !important;
    gap:18px !important;
    align-items:start !important;
    margin-left:0 !important;
    margin-right:0 !important;
  }

  .ev-modal .ev-publicacion-modal-grid > .ev-publicacion-form-col,
  .ev-modal .ev-publicacion-modal-grid > .ev-publicacion-preview-col{
    width:auto !important;
    max-width:none !important;
    flex:0 0 auto !important;
    padding-left:0 !important;
    padding-right:0 !important;
  }

  .ev-modal .ev-preview-sticky{
    position:sticky;
    top:0;
    width:100%;
  }
}

/* ==========================================================
   9. UPLOADER E IMÁGENES
========================================================== */
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

.ev-dropzone .ico{
  font-size:1.6rem;
  color:var(--ev-verde);
  margin-bottom:6px;
}

.ev-dropzone .t1{
  font-weight:700;
  color:var(--ev-verde-oscuro);
}

.ev-dropzone .t2{
  color:var(--ev-gris-500);
  font-size:.86rem;
}

.ev-dropzone.drag-over{
  border-color:rgba(25,135,84,.65);
  background:rgba(230,244,236,.9);
  transform:translateY(-1px);
}

.ev-tiles{
  display:flex;
  flex-wrap:wrap;
  align-items:flex-start;
  gap:10px;
}

#evTiles:empty,
#evTilesEdit:empty{
  display:none;
}

@supports selector(:has(*)){
  .ev-section:has(#evTiles:not(:empty)) .ev-dropzone,
  .ev-section:has(#evTilesEdit:not(:empty)) .ev-dropzone{
    display:none !important;
  }
}

.ev-section.ev-has-tiles .ev-dropzone{
  display:none !important;
}

.ev-tile,
.ev-tile-add{
  flex:0 0 auto;
  width:86px;
  height:86px;
  border-radius:14px;
  border:1px solid rgba(148,163,184,.35);
  overflow:hidden;
  position:relative;
  background:#fff;
  box-shadow:0 8px 18px rgba(15,23,42,.06);
}

.ev-tile img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-tile-add{
  border:1.5px dashed rgba(148,163,184,.55);
  color:#475569;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  text-align:center;
  padding:8px;
  font-weight:600;
  line-height:1.15;
  cursor:pointer;
  transition:border-color .16s ease, background .16s ease, transform .16s ease, box-shadow .16s ease;
}

.ev-tile-add:hover{
  border-color:rgba(234,124,18,.48);
  background:#FFF7ED;
  transform:translateY(-1px);
  box-shadow:0 12px 22px rgba(234,124,18,.10);
}

.ev-tile-add .ico{
  width:28px;
  height:28px;
  border-radius:999px;
  display:grid;
  place-items:center;
  background:#fff;
  color:var(--ev-naranja);
  border:1px solid rgba(234,124,18,.24);
  margin-bottom:4px;
}

.ev-tile-add .t1{
  font-size:.78rem;
  font-weight:700;
  color:#374151;
}

.ev-tile-add .t2{
  display:none;
}

.ev-tile-remove{
  position:absolute;
  top:6px;
  right:6px;
  width:26px;
  height:26px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.42);
  background:rgba(15,23,42,.58);
  color:#fff;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  font-weight:900;
  font-size:16px;
  line-height:1;
  box-shadow:0 8px 18px rgba(15,23,42,.22);
  backdrop-filter:blur(6px);
  opacity:.92;
  transition:transform .15s ease, opacity .15s ease;
}

.ev-tile:hover .ev-tile-remove{
  opacity:1;
  transform:translateY(-1px);
}

/* ==========================================================
   10. VISTA PREVIA
========================================================== */
.ev-preview-panel{
  width:100%;
  max-width:100%;
  padding:14px;
  border-radius:20px;
  background:
    radial-gradient(circle at 90% 8%,rgba(22,163,74,.10),transparent 36%),
    linear-gradient(180deg,#fff 0%,#fbfdfb 100%);
  border:1px solid rgba(148,163,184,.26);
  box-shadow:0 18px 42px rgba(15,23,42,.08);
}

.ev-preview-panel-head{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:12px;
  padding-bottom:10px;
  margin-bottom:12px;
  border-bottom:1px solid rgba(229,231,235,.90);
}

.ev-preview-kicker{
  font-size:.70rem;
  line-height:1;
  font-weight:900;
  letter-spacing:.07em;
  text-transform:uppercase;
  color:#9CA3AF;
  margin-bottom:4px;
}

.ev-preview-heading{
  color:var(--ev-verde-oscuro);
  font-weight:900;
  line-height:1.18;
}

.ev-preview-kind{
  flex:0 0 auto;
  min-width:82px;
  text-align:center;
  padding:8px 13px;
  border-radius:999px;
  font-size:.82rem;
  font-weight:900;
  border:1px solid rgba(15,89,47,.14);
  background:rgba(230,244,236,.86);
  color:var(--ev-verde-oscuro);
}

.ev-preview-kind-servicio{
  color:#9A3412;
  border-color:rgba(234,124,18,.24);
  background:rgba(255,247,237,.96);
}

.ev-preview-area{
  border:1px dashed rgba(148,163,184,.55);
  border-radius:16px;
  background:#F9FAFB;
  padding:12px;
  margin-bottom:12px;
}

.ev-preview-media-card:empty{
  display:none;
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

.ev-preview-main img{
  width:100%;
  height:auto;
  display:block;
  object-fit:contain;
  max-height:300px;
}

.ev-preview-thumbs{
  display:flex;
  gap:10px;
  margin-top:10px;
  overflow:auto;
}

.ev-preview-thumb{
  flex:0 0 auto;
  width:64px;
  height:48px;
  border-radius:12px;
  overflow:hidden;
  border:1px solid rgba(148,163,184,.30);
  cursor:pointer;
  background:#fff;
}

.ev-preview-thumb.active{
  outline:2px solid rgba(25,135,84,.55);
}

.ev-preview-thumb img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-preview-summary-card{
  padding:14px;
  border-radius:16px;
  border:1px solid rgba(229,231,235,.95);
  background:#fff;
  box-shadow:0 10px 22px rgba(15,23,42,.04);
  margin-bottom:12px;
}

.ev-preview-summary-title{
  margin:0 0 4px;
  font-weight:900;
  color:#111827;
}

.ev-preview-summary-price{
  color:var(--ev-verde-oscuro);
  font-weight:950;
  font-size:1.28rem;
  line-height:1.15;
}

.ev-preview-summary-label{
  margin-top:10px;
  color:#9CA3AF;
  font-size:.72rem;
  font-weight:700;
  letter-spacing:.06em;
  text-transform:uppercase;
}

.ev-preview-summary-desc{
  margin:3px 0 0;
  color:#4B5563;
  line-height:1.4;
}

.ev-preview-tips{
  display:flex;
  flex-direction:column;
  gap:10px;
}

.ev-preview-tip{
  display:flex;
  align-items:flex-start;
  gap:10px;
  padding:10px 12px;
  border-radius:14px;
  border:1px solid rgba(229,231,235,.95);
  background:rgba(255,255,255,.92);
  color:#64748B;
  font-size:.84rem;
  font-weight:600;
  line-height:1.25;
}

.ev-preview-tip i{
  color:var(--ev-verde-oscuro);
  margin-top:1px;
}


/* ==========================================================
   11. AJUSTE FINO UX/UI PREMIUM
========================================================== */
.ev-modal .form-control,
.ev-modal .form-select{
  min-height:42px;
  background:rgba(255,255,255,.96);
  color:#1F2937;
}

.ev-modal textarea.form-control{
  min-height:104px;
  line-height:1.45;
}

.ev-kind-section .ev-section-subtitle{
  margin-bottom:14px;
}

.ev-publicacion-option-copy strong{
  letter-spacing:-.01em;
}

.ev-publicacion-option-copy small{
  max-width:180px;
}

.ev-step-badge{
  font-weight:800;
}

.ev-modal-footer .btn{
  min-height:44px;
}



/* ==========================================================
   12. CIERRE UX/UI AMIGABLE EV
========================================================== */
.ev-modal .ev-field-invalid{
  border-color:rgba(234,124,18,.70) !important;
  box-shadow:
    0 0 0 3px rgba(234,124,18,.12),
    0 10px 22px rgba(234,124,18,.06) !important;
  background:#FFFDF8 !important;
}

.ev-field-error{
  margin-top:6px;
  color:#B45309;
  font-size:.80rem;
  font-weight:500;
  line-height:1.25;
}

.ev-preview-tips{
  gap:8px;
}

.ev-preview-tip{
  padding:9px 11px;
}

.ev-preview-tip span{
  display:block;
}

.ev-preview-media-card{
  padding:10px;
}

.ev-preview-summary-card{
  padding:13px;
}

.ev-preview-main img{
  max-height:260px;
}

.ev-modal .form-control::placeholder,
.ev-modal textarea.form-control::placeholder{
  color:#6B7280;
  opacity:.92;
}


/* ==========================================================
   11. RESPONSIVE
========================================================== */

/* ==========================================================
   SERVICIOS DEL PILOTO
========================================================== */
.ev-service-pilot-card{
  min-width:min(100%, 340px);
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px 12px;
  border:1px solid rgba(22,163,74,.20);
  border-radius:16px;
  background:linear-gradient(135deg,#F0FDF4,#FFFFFF);
  box-shadow:0 10px 22px rgba(15,23,42,.05);
}

.ev-service-pilot-card.is-full{
  border-color:rgba(234,124,18,.36);
  background:linear-gradient(135deg,#FFF7ED,#FFFFFF);
}

.ev-service-pilot-icon{
  width:36px;
  height:36px;
  flex:0 0 auto;
  display:grid;
  place-items:center;
  border-radius:13px;
  color:var(--ev-verde-oscuro);
  background:#DCFCE7;
  border:1px solid rgba(22,163,74,.18);
}

.ev-service-pilot-card.is-full .ev-service-pilot-icon{
  color:var(--ev-naranja-oscuro);
  background:#FFEDD5;
  border-color:rgba(234,124,18,.22);
}

.ev-service-pilot-copy{
  min-width:0;
  flex:1 1 auto;
  display:grid;
  gap:1px;
}

.ev-service-pilot-copy strong{
  color:var(--ev-verde-oscuro);
  font-size:.82rem;
  font-weight:900;
}

.ev-service-pilot-copy span,
.ev-service-pilot-copy small{
  color:var(--ev-gris-600);
  font-size:.74rem;
  line-height:1.28;
}

.ev-service-pilot-copy b{
  color:var(--ev-verde-oscuro);
  font-weight:950;
}

.ev-service-pilot-meter{
  width:56px;
  height:7px;
  overflow:hidden;
  border-radius:999px;
  background:#DCFCE7;
}

.ev-service-pilot-meter > span{
  display:block;
  width:0;
  height:100%;
  border-radius:inherit;
  background:linear-gradient(90deg,var(--ev-verde),#34D66F);
  transition:width .22s ease;
}

.ev-service-pilot-card.is-full .ev-service-pilot-meter{
  background:#FFEDD5;
}

.ev-service-pilot-card.is-full .ev-service-pilot-meter > span{
  background:linear-gradient(90deg,var(--ev-naranja),#F59E0B);
}

.ev-service-pilot-notice{
  display:flex;
  align-items:flex-start;
  gap:9px;
  margin-top:12px;
  padding:10px 12px;
  border:1px solid rgba(22,163,74,.18);
  border-radius:14px;
  background:linear-gradient(135deg,#F0FDF4,#FFFFFF);
  color:var(--ev-verde-oscuro);
}

.ev-service-pilot-notice[hidden]{
  display:none !important;
}

.ev-service-pilot-notice > i{
  margin-top:1px;
  color:var(--ev-verde);
  font-size:1rem;
}

.ev-service-pilot-notice > div{
  min-width:0;
  display:grid;
  gap:2px;
}

.ev-service-pilot-notice strong{
  font-size:.80rem;
  line-height:1.3;
  font-weight:900;
}

.ev-service-pilot-notice small{
  color:var(--ev-gris-600);
  font-size:.75rem;
  line-height:1.35;
}

.ev-service-pilot-notice.is-full{
  color:var(--ev-naranja-oscuro);
  border-color:rgba(234,124,18,.30);
  background:linear-gradient(135deg,#FFF7ED,#FFFFFF);
}

.ev-service-pilot-notice.is-full > i{
  color:var(--ev-naranja);
}

@media (min-width: 1400px){
  .ev-modal-xl{
    max-width:1120px;
  }

  .ev-col-titulo{ width:190px; }
  .ev-col-desc{ width:190px; }
  .ev-col-mensaje{ width:230px; }
  .ev-col-acciones{ width:218px; }
}

@media (max-width: 1199.98px){
  .ev-table{
    font-size:.89rem;
  }

  .ev-table thead th{
    padding:13px 10px !important;
    font-size:.82rem;
  }

  .ev-table tbody td{
    padding:13px 10px !important;
  }

  .ev-col-codigo{ width:82px; }
  .ev-col-publicacion{ width:108px; }
  .ev-col-titulo{ width:160px; }
  .ev-col-precio{ width:94px; }
  .ev-col-tipo{ width:108px; }
  .ev-col-categoria{ width:118px; }
  .ev-col-desc{ width:160px; }
  .ev-col-mensaje{ width:190px; }
  .ev-col-estado-publicacion{ width:136px; }
  .ev-col-acciones{ width:190px; }

  .ev-actions{ gap:7px; }
  .ev-chip{ min-width:84px; font-size:.79rem; }
  .ev-chip-status{ min-width:104px; font-size:.78rem; }
}

@media (max-width: 859.98px){
  .ev-modal .ev-publicacion-modal-grid{
    display:block !important;
    margin-left:0 !important;
    margin-right:0 !important;
  }

  .ev-modal .ev-publicacion-form-col,
  .ev-modal .ev-publicacion-preview-col{
    width:100% !important;
    max-width:100% !important;
    padding-left:0 !important;
    padding-right:0 !important;
  }

  .ev-modal .ev-publicacion-preview-col{
    margin-top:14px;
  }

  .ev-modal .ev-preview-sticky{
    position:relative;
    top:auto;
  }
}

@media (max-width: 768px){
  .ev-mp-title{
    font-size:1.65rem;
  }

  .ev-table thead{
    display:none;
  }

  .ev-table,
  .ev-table tbody,
  .ev-table tr,
  .ev-table td{
    display:block;
    width:100%;
  }

  .ev-table tbody tr{
    margin:10px 10px 12px;
    border:1px solid rgba(148,163,184,.20);
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 12px 26px rgba(15,23,42,.07);
    background:linear-gradient(180deg,#fff 0%,#fbfbfc 100%);
  }

  .ev-table tbody td{
    border-left:none !important;
    border-bottom:1px dashed rgba(229,231,235,.9);
    padding:12px 14px !important;
    background:transparent !important;
    text-align:left !important;
  }

  .ev-table tbody td:last-child{
    border-bottom:none;
  }

  .ev-table tbody td::before{
    content:attr(data-label);
    display:block;
    font-size:.76rem;
    font-weight:900;
    color:var(--ev-gris-500);
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:6px;
  }

  .td-trunc{
    white-space:normal;
    overflow:visible;
    text-overflow:unset;
  }

  .ev-chip-status{
    min-width:unset;
    width:100%;
  }

  .ev-actions{
    justify-content:flex-start;
    align-items:stretch;
    flex-direction:column;
    flex-wrap:nowrap;
    gap:8px;
  }

  .ev-actions .ev-chip,
  .ev-actions .ev-chip-status{
    width:100%;
    min-width:unset;
  }

  .ev-publicacion-choice-grid{
    grid-template-columns:1fr;
  }
}

@media (max-width: 575.98px){
  .ev-mp-page{
    padding:10px 10px 22px;
  }

  .ev-mp-hero-body{
    padding:16px 14px 12px;
  }

  .ev-card-header,
  .ev-card-body,
  .ev-card-footer{
    padding-left:14px;
    padding-right:14px;
  }

  .ev-card-footer{
    flex-direction:column;
    align-items:flex-start;
    gap:8px;
  }

  .ev-table-wrap{
    padding:0 8px 10px;
  }

  .ev-modal{
    --bs-modal-margin:8px;
  }

  .ev-modal-header{
    padding:13px 16px;
    min-height:54px;
  }

  .ev-modal-body{
    padding:14px;
  }

  .ev-modal-footer{
    padding:12px 14px 14px;
    flex-direction:column;
    align-items:stretch;
  }

  .ev-modal-footer .btn{
    width:100%;
    justify-content:center;
  }

  .ev-section{
    padding:14px;
  }

  .ev-publicacion-option{
    min-height:70px;
    padding:12px 40px 12px 12px;
  }

  .ev-preview-panel{
    padding:12px;
    border-radius:18px;
  }

  .ev-preview-panel-head{
    flex-direction:column;
  }

  .ev-preview-kind{
    width:100%;
  }
}


/* ==========================================================
   AJUSTE TIPOGRÁFICO FINO — Labels del formulario
   Objetivo: títulos de campos menos pesados / más premium.
========================================================== */
.ev-modal label.form-label,
.ev-modal .form-label{
  font-weight:500 !important;
  color:#1F2937 !important;
  font-size:.925rem !important;
  letter-spacing:0 !important;
  line-height:1.28 !important;
}

.ev-modal .form-label .text-danger{
  font-weight:600 !important;
}

/* Títulos de secciones: mantienen jerarquía, pero sin verse gruesos */
.ev-modal .ev-section-title{
  font-weight:650 !important;
  color:#111827 !important;
  letter-spacing:-.01em !important;
}

/* Texto de ayuda y subtítulos más ligeros */
.ev-modal .form-text,
.ev-modal .ev-section-subtitle{
  font-weight:400 !important;
  color:#6B7280 !important;
}

/* Campo seleccionado / inputs: mantener lectura limpia */
.ev-modal .form-control,
.ev-modal .form-select{
  font-weight:400 !important;
}

/* Botón limpiar imágenes: menos pesado */
.ev-modal #btnLimpiarImagenes,
.ev-modal #btnLimpiarImagenesEdit{
  font-weight:600 !important;
}



/* ==========================================================
   EV MODAL DESKTOP — Scroll solo en columna de pasos
   La vista previa se mantiene visible.
========================================================== */
@media (min-width: 860px){
  .ev-modal .modal-content.ev-modal-content,
  .ev-modal-content{
    height:calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2));
  }

  .ev-modal .ev-modal-flex{
    flex:1 1 auto;
    min-height:0;
  }

  .ev-modal .ev-modal-body-scroll{
    flex:1 1 auto;
    overflow:hidden !important;
    min-height:0;
    padding:16px 18px 18px !important;
    max-height:none !important;
  }

  .ev-modal .ev-publicacion-modal-grid{
    height:100%;
    min-height:0;
    align-items:stretch !important;
  }

  .ev-modal .ev-publicacion-form-col{
    min-height:0;
    max-height:100%;
    overflow-y:auto;
    overflow-x:hidden;
    padding-right:10px !important;
    scrollbar-width:thin;
    scrollbar-color:rgba(107,114,128,.34) transparent;
  }

  .ev-modal .ev-publicacion-form-col::-webkit-scrollbar{
    width:8px;
  }

  .ev-modal .ev-publicacion-form-col::-webkit-scrollbar-track{
    background:transparent;
  }

  .ev-modal .ev-publicacion-form-col::-webkit-scrollbar-thumb{
    background:rgba(107,114,128,.30);
    border-radius:999px;
    border:2px solid #fff;
  }

  .ev-modal .ev-publicacion-form-col::-webkit-scrollbar-thumb:hover{
    background:rgba(107,114,128,.46);
  }

  .ev-modal .ev-publicacion-preview-col{
    min-height:0;
    max-height:100%;
    overflow:hidden;
    padding-left:2px !important;
  }

  .ev-modal .ev-preview-sticky{
    position:sticky;
    top:0;
    max-height:100%;
    overflow:auto;
    padding-right:2px;
    scrollbar-width:thin;
    scrollbar-color:rgba(107,114,128,.24) transparent;
  }

  .ev-modal .ev-preview-sticky::-webkit-scrollbar{
    width:6px;
  }

  .ev-modal .ev-preview-sticky::-webkit-scrollbar-track{
    background:transparent;
  }

  .ev-modal .ev-preview-sticky::-webkit-scrollbar-thumb{
    background:rgba(107,114,128,.24);
    border-radius:999px;
  }

  .ev-modal .ev-preview-panel{
    margin-bottom:0;
  }
}

/* En móvil/tablet angosta se mantiene el flujo normal apilado */
@media (max-width: 859.98px){
  .ev-modal .modal-content.ev-modal-content,
  .ev-modal-content{
    height:auto;
  }

  .ev-modal .ev-modal-body-scroll{
    overflow:auto !important;
  }

  .ev-modal .ev-publicacion-form-col,
  .ev-modal .ev-publicacion-preview-col,
  .ev-modal .ev-preview-sticky{
    max-height:none;
    overflow:visible;
  }
}








/* ==========================================================
   EV CIERRE FINAL 100% — Preview con placeholder + responsive
========================================================== */

/* Placeholder premium cuando todavía no hay imágenes */
.ev-preview-media-card.is-empty{
  display:block !important;
}

.ev-preview-empty{
  min-height:148px;
  display:flex;
  align-items:center;
  gap:12px;
  padding:16px;
  border-radius:16px;
  border:1px dashed rgba(15,89,47,.22);
  background:
    radial-gradient(circle at 90% 8%, rgba(230,244,236,.78), transparent 42%),
    linear-gradient(180deg, #FFFFFF 0%, #F8FCFA 100%);
}

.ev-preview-empty-icon{
  flex:0 0 46px;
  width:46px;
  height:46px;
  border-radius:16px;
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  background:rgba(230,244,236,.95);
  border:1px solid rgba(15,89,47,.12);
  font-size:1.25rem;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.90);
}

.ev-preview-empty-title{
  color:var(--ev-verde-oscuro);
  font-weight:750;
  line-height:1.20;
  margin-bottom:3px;
}

.ev-preview-empty-text{
  color:#64748B;
  font-size:.84rem;
  line-height:1.30;
}

/* Desktop / laptop: solo scrollea la columna de pasos y preview queda visible */
@media (min-width: 860px){
  .ev-modal .modal-content.ev-modal-content,
  .ev-modal-content{
    height:calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2));
  }

  .ev-modal .ev-modal-flex{
    flex:1 1 auto;
    min-height:0;
  }

  .ev-modal .ev-modal-body-scroll{
    flex:1 1 auto;
    overflow:hidden !important;
    min-height:0;
    padding:16px 18px 18px !important;
    max-height:none !important;
  }

  .ev-modal .ev-publicacion-modal-grid{
    height:100%;
    min-height:0;
    display:grid !important;
    grid-template-columns:minmax(0,58%) minmax(320px,42%) !important;
    gap:22px !important;
    align-items:stretch !important;
    margin-left:0 !important;
    margin-right:0 !important;
    position:relative;
  }

  .ev-modal .ev-publicacion-modal-grid > .ev-publicacion-form-col,
  .ev-modal .ev-publicacion-modal-grid > .ev-publicacion-preview-col{
    width:auto !important;
    max-width:none !important;
    flex:0 0 auto !important;
  }

  .ev-modal .ev-publicacion-modal-grid::after{
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

  .ev-modal .ev-publicacion-form-col{
    min-height:0;
    max-height:100%;
    overflow-y:auto;
    overflow-x:hidden;
    padding-right:14px !important;
    padding-left:0 !important;
    scrollbar-width:thin;
    scrollbar-color:rgba(148,163,184,.10) transparent;
  }

  .ev-modal .ev-publicacion-form-col::-webkit-scrollbar{
    width:4px;
  }

  .ev-modal .ev-publicacion-form-col::-webkit-scrollbar-track{
    background:transparent;
  }

  .ev-modal .ev-publicacion-form-col::-webkit-scrollbar-thumb{
    background:rgba(148,163,184,.10);
    border-radius:999px;
  }

  .ev-modal .ev-publicacion-form-col:hover::-webkit-scrollbar-thumb,
  .ev-modal .ev-publicacion-form-col:focus-within::-webkit-scrollbar-thumb{
    background:rgba(148,163,184,.28);
  }

  .ev-modal .ev-publicacion-form-col::after{
    content:'';
    position:sticky;
    display:block;
    bottom:-1px;
    height:14px;
    margin-top:-14px;
    pointer-events:none;
    background:linear-gradient(180deg, rgba(255,255,255,0), rgba(255,255,255,.94));
    z-index:2;
  }

  .ev-modal .ev-publicacion-preview-col{
    min-height:0;
    max-height:100%;
    overflow:hidden;
    padding-left:4px !important;
    padding-right:0 !important;
    border-radius:20px;
    background:
      radial-gradient(circle at 80% 0%, rgba(230,244,236,.36), transparent 34%),
      linear-gradient(180deg, rgba(250,252,251,.72) 0%, rgba(255,255,255,.96) 48%, rgba(255,255,255,1) 100%);
  }

  .ev-modal .ev-preview-sticky{
    position:sticky;
    top:0;
    max-height:100%;
    overflow:auto;
    padding:0 2px 2px 0;
    scrollbar-width:thin;
    scrollbar-color:rgba(148,163,184,.12) transparent;
  }

  .ev-modal .ev-preview-sticky::-webkit-scrollbar{
    width:4px;
  }

  .ev-modal .ev-preview-sticky::-webkit-scrollbar-track{
    background:transparent;
  }

  .ev-modal .ev-preview-sticky::-webkit-scrollbar-thumb{
    background:rgba(148,163,184,.12);
    border-radius:999px;
  }

  .ev-modal .ev-preview-panel{
    margin-bottom:0;
    border-color:rgba(203,213,225,.42);
    background:
      radial-gradient(circle at 90% 8%, rgba(22,163,74,.08), transparent 34%),
      linear-gradient(180deg, #FFFFFF 0%, #FBFDFB 100%);
    box-shadow:
      0 18px 42px rgba(15,23,42,.060),
      inset 0 1px 0 rgba(255,255,255,.94);
  }

  .ev-modal .ev-section{
    background:rgba(255,255,255,.98);
    border-color:rgba(203,213,225,.58);
    box-shadow:
      0 14px 34px rgba(15,23,42,.048),
      inset 0 1px 0 rgba(255,255,255,.92);
  }
}

/* Tablet y móvil: flujo apilado, legible y sin paneles forzados */
@media (max-width: 859.98px){
  .ev-modal .modal-content.ev-modal-content,
  .ev-modal-content{
    height:auto;
  }

  .ev-modal .ev-modal-body-scroll{
    overflow:auto !important;
    padding:14px !important;
  }

  .ev-modal .ev-publicacion-modal-grid{
    display:block !important;
    height:auto;
  }

  .ev-modal .ev-publicacion-modal-grid::after,
  .ev-modal .ev-publicacion-form-col::after{
    display:none !important;
  }

  .ev-modal .ev-publicacion-form-col,
  .ev-modal .ev-publicacion-preview-col,
  .ev-modal .ev-preview-sticky{
    max-height:none !important;
    overflow:visible !important;
    padding-left:0 !important;
    padding-right:0 !important;
    background:transparent !important;
  }

  .ev-modal .ev-publicacion-preview-col{
    margin-top:14px;
  }

  .ev-modal .ev-preview-panel{
    box-shadow:0 14px 32px rgba(15,23,42,.055);
  }

  .ev-preview-empty{
    min-height:118px;
    padding:14px;
  }

  .ev-preview-main img{
    max-height:240px;
  }
}

/* Móvil pequeño: preview más compacto y botones cómodos */
@media (max-width: 575.98px){
  .ev-preview-panel{
    padding:12px;
  }

  .ev-preview-panel-head{
    gap:8px;
    margin-bottom:10px;
    padding-bottom:9px;
  }

  .ev-preview-empty{
    flex-direction:column;
    text-align:center;
    min-height:112px;
    gap:8px;
  }

  .ev-preview-empty-icon{
    width:42px;
    height:42px;
    flex-basis:42px;
  }

  .ev-preview-summary-card{
    padding:12px;
  }

  .ev-preview-summary-price{
    font-size:1.18rem;
  }

  .ev-preview-tip{
    padding:9px 10px;
    font-size:.80rem;
  }

  .ev-preview-thumbs{
    gap:8px;
  }

  .ev-preview-thumb{
    width:58px;
    height:44px;
  }
}



@media (max-width: 768px){
  .ev-service-pilot-card{
    width:100%;
    min-width:0;
  }

  .ev-service-pilot-meter{
    width:48px;
  }
}


/* ==========================================================
   18. UX/UI — ESTADO, FILTROS Y LISTADO
========================================================== */
.ev-mp-hero-bottom{
  display:grid;
  grid-template-columns:minmax(520px,1fr) auto;
  align-items:center;
  gap:14px;
  margin-top:16px;
}

.ev-status-filter-icon,
.ev-filter-heading-icon,
.ev-filter-secondary-icon{
  flex:0 0 auto;
  display:grid;
  place-items:center;
  color:var(--ev-verde-oscuro);
  background:linear-gradient(145deg,#ECFDF5,#DCFCE7);
  border:1px solid rgba(22,163,74,.18);
}

.ev-status-filter{
  min-height:66px;
  display:grid;
  grid-template-columns:42px minmax(190px,1fr) minmax(210px,270px);
  align-items:center;
  gap:12px;
  padding:10px 12px;
  border:1px solid rgba(148,163,184,.22);
  border-radius:17px;
  background:#fff;
  box-shadow:0 12px 26px rgba(15,23,42,.06);
}

.ev-status-filter-icon{
  width:42px;
  height:42px;
  border-radius:14px;
}

.ev-status-filter-copy{
  display:flex;
  flex-direction:column;
  min-width:0;
}

.ev-status-filter-label{
  font-weight:850;
  color:var(--ev-verde-oscuro);
  line-height:1.15;
}

.ev-status-filter-help{
  color:var(--ev-gris-500);
  font-size:.76rem;
  line-height:1.25;
  margin-top:3px;
}

.ev-status-select{
  min-height:44px;
  border-radius:13px;
  border:1px solid rgba(22,163,74,.25);
  color:#173D29;
  font-weight:750;
  background-color:#fff;
  box-shadow:0 8px 18px rgba(15,23,42,.04);
}

.ev-status-select:hover{ border-color:rgba(234,124,18,.50); }
.ev-status-select:focus{
  border-color:var(--ev-verde);
  box-shadow:0 0 0 3px rgba(22,163,74,.14);
}

.ev-result-summary{
  justify-self:end;
  color:var(--ev-gris-600);
  font-size:.86rem;
  font-weight:750;
  white-space:nowrap;
}

.ev-filter-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  padding:15px 16px;
  border-bottom:1px solid var(--ev-gris-200);
  background:linear-gradient(180deg,#fff 0%,#FCFDFC 100%);
}

.ev-filter-heading{
  display:flex;
  align-items:center;
  gap:12px;
  min-width:0;
}

.ev-filter-heading-icon{
  width:42px;
  height:42px;
  border-radius:14px;
}

.ev-filter-description{
  color:var(--ev-gris-500);
  font-size:.86rem;
  line-height:1.35;
}

.ev-filter-body{ padding:16px; }
.ev-filter-form{
  display:flex;
  flex-direction:column;
  gap:14px;
}

.ev-filter-primary{
  display:grid;
  grid-template-columns:minmax(280px,1.7fr) repeat(3,minmax(165px,1fr));
  gap:12px;
  align-items:end;
}

.ev-filter-field{ min-width:0; }
.ev-filter-field .form-label{
  margin-bottom:6px;
  color:#26352D;
  font-size:.82rem;
  font-weight:800;
}

.ev-filter-field .ev-input{
  min-height:44px;
  background-color:#fff;
  box-shadow:0 7px 18px rgba(15,23,42,.035);
}

.ev-filter-field .ev-input:hover:not(:disabled){ border-color:rgba(234,124,18,.46); }
.ev-filter-field .ev-input:disabled{
  color:#8B95A7;
  background:#F7F9F8;
  cursor:not-allowed;
}

.ev-filter-actions-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding-top:2px;
}

.ev-filter-advanced-toggle{
  min-height:42px;
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:9px 13px;
  border:1px solid rgba(22,163,74,.20);
  border-radius:14px;
  background:#fff;
  color:var(--ev-verde-oscuro);
  font-weight:850;
  box-shadow:0 8px 18px rgba(15,23,42,.045);
  transition:all .18s ease;
}

.ev-filter-advanced-toggle:hover,
.ev-filter-advanced-toggle:not(.collapsed){
  color:#9A4A05;
  border-color:rgba(234,124,18,.44);
  background:linear-gradient(180deg,#FFF 0%,#FFF7ED 100%);
  box-shadow:0 12px 24px rgba(234,124,18,.11);
}

.ev-filter-toggle-chevron{ transition:transform .18s ease; }
.ev-filter-advanced-toggle:not(.collapsed) .ev-filter-toggle-chevron{ transform:rotate(180deg); }

.ev-filter-submit{
  min-height:44px;
  min-width:158px;
  white-space:nowrap;
}

.ev-filter-advanced-inner{
  display:grid;
  grid-template-columns:minmax(170px,.65fr) minmax(360px,1.35fr) minmax(210px,.8fr);
  align-items:end;
  gap:13px;
  margin-top:2px;
  padding:14px;
  border:1px solid rgba(148,163,184,.18);
  border-radius:16px;
  background:linear-gradient(135deg,rgba(249,250,251,.92),rgba(255,255,255,.98));
}

.ev-filter-advanced-heading{
  align-self:center;
  display:flex;
  align-items:center;
  gap:10px;
  min-width:0;
}

.ev-filter-secondary-icon{
  width:38px;
  height:38px;
  border-radius:12px;
}

.ev-filter-advanced-heading div{ display:flex; flex-direction:column; }
.ev-filter-advanced-heading strong{ color:var(--ev-verde-oscuro); font-size:.88rem; }
.ev-filter-advanced-heading span{ color:var(--ev-gris-500); font-size:.73rem; }

.ev-filter-price-range{
  display:grid;
  grid-template-columns:1fr auto 1fr;
  align-items:end;
  gap:8px;
}

.ev-filter-range-separator{
  padding-bottom:12px;
  color:var(--ev-gris-400);
  font-weight:800;
}

.ev-publications-card .ev-card-header{ align-items:center; }
.ev-publications-card .ev-table-frame{ overflow:hidden; }
.ev-publications-card .table-responsive{
  overflow-x:auto;
  scrollbar-width:thin;
  scrollbar-color:rgba(15,89,47,.35) transparent;
}

.ev-publications-card .ev-table{
  min-width:1420px;
  table-layout:fixed;
}

.ev-publications-card .ev-table thead th{
  padding:13px 11px !important;
  font-size:.82rem;
  letter-spacing:.005em;
}

.ev-publications-card .ev-table tbody td{
  padding:13px 12px !important;
  line-height:1.38;
}

.ev-publications-card .ev-table th + th,
.ev-publications-card .ev-table td + td{
  border-left:1px solid rgba(229,231,235,.42);
}

.ev-col-codigo{ width:82px; }
.ev-col-publicacion{ width:112px; }
.ev-col-titulo{ width:196px; }
.ev-col-precio{ width:96px; }
.ev-col-tipo{ width:112px; }
.ev-col-categoria{ width:122px; }
.ev-col-desc{ width:190px; }
.ev-col-mensaje{ width:218px; }
.ev-col-estado-publicacion{ width:154px; }
.ev-col-acciones{ width:190px; }

.ev-publication-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:100%;
  max-width:106px;
  min-width:0 !important;
  box-sizing:border-box;
  padding:.43rem .55rem;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}

.ev-cell-title,
.ev-cell-description,
.ev-cell-type,
.ev-cell-category{
  text-align:left !important;
  color:#17211B;
  overflow-wrap:anywhere;
}

.ev-cell-primary-text{
  display:-webkit-box;
  -webkit-box-orient:vertical;
  -webkit-line-clamp:2;
  overflow:hidden;
  font-weight:750;
  line-height:1.35;
}

.ev-cell-description > span{
  display:-webkit-box;
  -webkit-box-orient:vertical;
  -webkit-line-clamp:2;
  overflow:hidden;
  color:var(--ev-gris-600);
  line-height:1.38;
}

.ev-cell-price{
  white-space:nowrap;
  font-weight:800;
  color:#1D3426;
}

.ev-cell-publication{ overflow:hidden; }
.ev-publications-card .ev-actions{
  justify-content:center;
  flex-wrap:wrap;
  gap:7px;
}

.ev-publications-card .ev-actions .ev-chip{
  min-width:76px;
  padding:.43rem .70rem;
}

@media (max-width:1199.98px){
  .ev-mp-hero-bottom{ grid-template-columns:1fr auto; }
  .ev-filter-primary{ grid-template-columns:minmax(250px,1.35fr) repeat(2,minmax(165px,1fr)); }
  .ev-filter-primary .ev-filter-field:last-child{ grid-column:2 / 4; }
  .ev-filter-advanced-inner{ grid-template-columns:minmax(150px,.6fr) minmax(320px,1.4fr); }
  .ev-filter-order-field{ grid-column:2; }
}

@media (max-width:991.98px){
  .ev-mp-hero-bottom{ grid-template-columns:1fr; }
  .ev-result-summary{ justify-self:start; }
  .ev-filter-primary{ grid-template-columns:1fr 1fr; }
  .ev-filter-primary .ev-filter-search-field{ grid-column:1 / -1; }
  .ev-filter-primary .ev-filter-field:last-child{ grid-column:auto; }
  .ev-filter-advanced-inner{ grid-template-columns:1fr; }
  .ev-filter-advanced-heading,
  .ev-filter-price-range,
  .ev-filter-order-field{ grid-column:auto; }
}

@media (max-width:768px){
  .ev-mp-hero-bottom{ gap:10px; }
  .ev-status-filter{
    width:100%;
    grid-template-columns:40px 1fr;
    padding:11px;
  }
  .ev-status-select{ grid-column:1 / -1; width:100%; }
  .ev-filter-header{ align-items:flex-start; }
  .ev-filter-heading-icon{ width:38px; height:38px; }
  .ev-filter-primary{ grid-template-columns:1fr; }
  .ev-filter-primary .ev-filter-search-field,
  .ev-filter-primary .ev-filter-field:last-child{ grid-column:auto; }
  .ev-filter-actions-row{ align-items:stretch; }
  .ev-filter-advanced-toggle,
  .ev-filter-submit{ flex:1 1 0; }
  .ev-filter-price-range{ grid-template-columns:1fr 1fr; }
  .ev-filter-range-separator{ display:none; }

  .ev-publications-card .ev-table{ min-width:0; }
  .ev-publications-card .ev-table tbody tr{
    display:grid !important;
    grid-template-columns:1fr 1fr;
    margin:10px 8px 14px;
    border-radius:18px;
    overflow:hidden;
  }
  .ev-publications-card .ev-table tbody td{
    display:block !important;
    width:auto !important;
    min-width:0;
    padding:12px 13px !important;
  }
  .ev-publications-card .ev-table tbody td[data-label="Título"],
  .ev-publications-card .ev-table tbody td[data-label="Descripción"],
  .ev-publications-card .ev-table tbody td[data-label="Mensaje de soporte"],
  .ev-publications-card .ev-table tbody td[data-label="Estado de publicación"],
  .ev-publications-card .ev-table tbody td[data-label="Acciones"]{ grid-column:1 / -1; }
  .ev-publications-card .ev-table tbody td[data-label="Precio"]{ text-align:right !important; }
  .ev-publications-card .ev-table tbody td[data-label="Precio"]::before{ text-align:right; }
  .ev-publications-card .ev-table tbody td[data-label="Publicación"]{ text-align:left !important; }
  .ev-publication-badge{ width:auto; max-width:100%; min-width:112px !important; }
  .ev-cell-primary-text,
  .ev-cell-description > span{ -webkit-line-clamp:unset; }
  .ev-publications-card .ev-actions{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    align-items:stretch;
  }
  .ev-publications-card .ev-actions .ev-chip{ width:100%; min-width:0; }
  .ev-publications-card .ev-actions > :only-child{ grid-column:1 / -1; }
}

@media (max-width:575.98px){
  .ev-filter-header{ flex-direction:column; }
  .ev-filter-header .ev-btn-outline{ width:100%; }
  .ev-filter-description{ font-size:.80rem; }
  .ev-filter-actions-row{ flex-direction:column; }
  .ev-filter-advanced-toggle,
  .ev-filter-submit{ width:100%; }
  .ev-filter-price-range{ grid-template-columns:1fr; }
  .ev-publications-card .ev-table tbody tr{
    grid-template-columns:1fr 1fr;
    margin-left:6px;
    margin-right:6px;
  }
  .ev-publications-card .ev-table tbody td[data-label="Código"],
  .ev-publications-card .ev-table tbody td[data-label="Publicación"]{ min-height:82px; }
}

</style>
