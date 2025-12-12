<style>
/* ================================
   🎨 CONTENEDOR & TARJETAS
================================ */
.container-publicaciones{
  width: 100%;
  max-width: calc(100% - 40px);  /* ocupa casi todo el ancho, dejando 20px por lado */
  margin: 32px 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  animation: fadeIn 0.5s ease-out;
}

.ev-card,
.card{
  border-radius: 18px;
  box-shadow: 0 14px 40px rgba(15, 23, 42, 0.14);
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  background-color: #ffffff;
  border: 1px solid rgba(148, 163, 184, 0.22);
}
.ev-card:hover,
.card:hover{
  transform: translateY(-2px);
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
}

/* ================================
   🏷️ CABECERA (hereda el gradiente global)
================================ */
.card-header{
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
  color: #fff;
  background: linear-gradient(135deg, #0F592F, #198754);
  font-weight: 600;
  letter-spacing: 0.4px;
}
.card-header h5{
  color:#ffffff !important;
  margin:0;
  font-weight:600;
}

/* ================================
   🔧 SUBHEADER / KPIs / CHIPS
================================ */
.sticky-toolbar{
  position: sticky;
  top: 0;
  z-index: 2;
  background: linear-gradient(to bottom, #f9fafb, #ffffff);
  border-bottom: 1px solid #e5e7eb;
  padding: .65rem 1.1rem;
}

.ev-kpis{
  display:flex;
  gap:.75rem;
  flex-wrap:wrap;
  align-items:center;
}
.ev-kpi{
  background: #f1f5f9;
  border-radius: 999px;
  padding: .32rem .7rem;
  display:flex;
  align-items:center;
  gap:.35rem;
  border:1px solid rgba(148, 163, 184, 0.55);
}
.ev-kpi-label{
  color:#475569;
  font-size:.78rem;
  text-transform:uppercase;
  letter-spacing:.08em;
}
.ev-kpi-value{
  font-weight:750;
  font-size:.9rem;
  color:#0f172a;
}

.ev-filters-chips{
  display:flex;
  flex-wrap:wrap;
  gap:.45rem;
  margin-top:.45rem;
}
.ev-chip-filter{
  display:inline-flex;
  align-items:center;
  gap:.25rem;
  background:#e6f4ef;
  color:#0f592f;
  border-radius:999px;
  padding:.25rem .6rem;
  font-weight:600;
  font-size:.8rem;
  border:1px solid #c7e2d5;
}
.ev-chip-filter .btn-close{
  width:.5rem;
  height:.5rem;
  filter:invert(1) saturate(0);
  opacity:.7;
}
.ev-chip-filter .btn-close:hover{
  opacity:1;
}

/* ================================
   🔘 BOTONES CABECERA
================================ */
.btn-ev-outline{
  color: #e5f9ee;
  border: 1.5px solid rgba(226, 232, 240, .9);
  background: rgba(15, 23, 42, 0.08);
  border-radius: 999px;
  padding: 7px 18px;
  font-weight: 600;
  font-size:.88rem;
  transition: all .2s ease;
  backdrop-filter: blur(4px);
}
.btn-ev-outline i{ font-size:.9rem; }
.btn-ev-outline:hover{
  background: rgba(15, 23, 42, 0.22);
  transform: translateY(-1px);
}

.btn-ev-primary{
  color: #0f592f;
  background: #ffffff;
  border: 1px solid rgba(226, 232, 240, .9);
  border-radius: 999px;
  padding: 7px 18px;
  font-weight: 700;
  font-size:.88rem;
  transition: all .2s ease;
}
.btn-ev-primary i{ font-size:.9rem; }
.btn-ev-primary:hover{
  filter: brightness(0.98);
  transform: translateY(-1px);
  box-shadow:0 6px 16px rgba(22, 163, 74, .24);
}

.btn-ev-soft{
  background: rgba(15, 89, 47, .08);
  border: 1px solid rgba(15, 89, 47, .16);
  color:#0f592f;
  border-radius:999px;
  padding:.25rem .7rem;
  font-size:.78rem;
  font-weight:600;
}
.btn-ev-soft:hover{
  background:#0f592f;
  color:#ecfdf3;
}

/* ================================
   🧾 FORM (LABELS / INPUTS)
================================ */
.form-label{
  font-weight: 700;
  font-size: 0.94rem;
  color: #0b3d27;
}
.input-premium{
  border-radius: 12px;
  border: 1px solid #d9e3dc;
  padding: 10px 14px;
  transition: box-shadow .2s ease, border-color .2s ease, transform .05s ease;
  background-color: #ffffff;
  box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
  font-size: 0.94rem;
  width: 100%;
  min-width: 0;
}
.input-premium:hover{ border-color: #bcd4c7; }
.input-premium:focus{
  border-color: #0F592F;
  box-shadow: 0 0 0 4px rgba(15, 89, 47, .14);
  outline: none;
}
textarea.input-premium{ min-height: 120px; }
.form-select.input-premium{
  appearance: none;
  background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath fill='%230F592F' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  background-size: 10px 6px;
}

/* Helpers UX */
.ev-section-title{
  font-size:.8rem;
  font-weight:800;
  letter-spacing:.15em;
  text-transform:uppercase;
  color:#6b7280;
}
.ev-form-hint{
  font-size:.8rem;
  color:#6b7280;
}
.ev-required::after{
  content:" *";
  color:#dc2626;
  font-weight:700;
}

/* ================================
   📊 TABLA PRINCIPAL
================================ */
.ev-table-wrap{
  max-height: calc(70vh - 140px);
  overflow:auto;
  margin-top:.6rem;
  border-radius:16px 16px 0 0;
  border:1px solid #e5e7eb;
  background: radial-gradient(circle at top, #f9fafb 0, #ffffff 55%);
  box-shadow:0 10px 28px rgba(15,89,47,.07);
}

.ev-table{
  width:100%;
  border-collapse: separate;
  border-spacing:0;
}

/* Cabecera sticky */
.ev-table thead th{
  position: sticky;
  top: 0;
  z-index: 1;
  background: linear-gradient(135deg,#f4faf7,#edf2ff);
  color: #111827;
  font-weight: 600;
  font-size: .86rem;
  border-top: 0;
  border-bottom: 1px solid #e5e7eb;
  padding: .8rem .9rem;
  white-space: nowrap;
  letter-spacing:.04em;
  text-transform:uppercase;
}

/* Redondeo esquinas superiores */
.ev-table thead th:first-child{
  border-top-left-radius:16px;
}
.ev-table thead th:last-child{
  border-top-right-radius:16px;
}

/* Cabecera ordenable */
.ev-table thead th[data-sort]{
  cursor: pointer;
  transition: background .13s ease, color .13s ease, transform .05s ease;
}
.ev-table thead th[data-sort]::after{
  content:"";
  display:inline-block;
  width:9px;
  height:9px;
  margin-left:6px;
  opacity:.5;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='9'%3E%3Cpath fill='%230f172a' d='M4.5 0L0 4h9L4.5 0zm0 9L0 5h9L4.5 9z'/%3E%3C/svg%3E");
  background-position:center;
  background-repeat:no-repeat;
}
.ev-table thead th[data-sort]:hover{
  background: linear-gradient(135deg,#e7f4ec,#e0ebff);
  color:#0F592F;
  transform: translateY(-1px);
}

/* Cuerpo */
.ev-table tbody td{
  color: #1F2937;
  vertical-align: middle;
  border-bottom: 1px solid #e5e7eb;
  padding: .72rem .9rem;
  font-size:.9rem;
}

/* Zebra + hover */
.ev-table tbody tr:nth-child(odd){
  background:#ffffff;
}
.ev-table tbody tr:nth-child(even){
  background:#f9fafb;
}
.ev-table tbody tr:hover{
  background:#ecfdf5;
}

/* Código monoespaciado */
.ev-code{
  font-variant-numeric: tabular-nums;
  letter-spacing: .3px;
  color:#111827;
  font-weight:600;
}

/* truncado de título */
.td-trunc{
  max-width: 360px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Densidades opcionales */
.ev-table[data-density="comfortable"] thead th,
.ev-table[data-density="comfortable"] tbody td{
  padding: 1.05rem 1rem;
}
.ev-table[data-density="compact"] thead th,
.ev-table[data-density="compact"] tbody td{
  padding: .55rem .6rem;
}

/* Badges de estado dentro de la tabla */
.ev-table .badge{
  border-radius:999px;
  font-weight:600;
  font-size:.75rem;
  padding:.25rem .65rem;
}
.ev-table .badge.bg-success{
  background:rgba(22, 163, 74, 0.12) !important;
  color:#166534 !important;
  border:1px solid rgba(22,163,74,.28);
}
.ev-table .badge.bg-warning{
  background:rgba(245, 158, 11, 0.12) !important;
  color:#92400e !important;
  border:1px solid rgba(245, 158, 11, .28);
}
.ev-table .badge.bg-light{
  background:rgba(148, 163, 184, 0.16) !important;
  color:#4b5563 !important;
  border:1px solid rgba(148, 163, 184, .40);
}

/* ================================
   🧰 ACCIONES & FOOTER
================================ */
.ev-actions{
  display:flex;
  gap:.45rem;
  justify-content:center;
  flex-wrap:wrap;
}

/* chips acción */
.ev-chip{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border:0;
  color:#fff;
  font-weight:700;
  letter-spacing:.2px;
  padding:.35rem .8rem;
  border-radius:.8rem;
  cursor:pointer;
  transition: filter .15s ease, transform .05s ease, box-shadow .12s ease;
  min-width: 86px;
  font-size:.78rem;
}
.ev-chip:active{
  transform: translateY(1px);
}
.ev-chip-amber{ background:#f59e0b; }
.ev-chip-green{ background:#16a34a; }
.ev-chip-teal{  background:#0F592F; }
.ev-chip-red{   background:#e11d48; }

.ev-chip-amber:hover,
.ev-chip-green:hover,
.ev-chip-teal:hover,
.ev-chip-red:hover{
  filter:brightness(1.06);
  box-shadow:0 4px 10px rgba(15,23,42,.22);
}

/* Footer */
.ev-foot{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:1rem;
  padding:.8rem 1.1rem;
  color:#6B7280;
  border-left:1px solid #e5e7eb;
  border-right:1px solid #e5e7eb;
  border-bottom:1px solid #e5e7eb;
  border-top:none;
  border-radius:0 0 16px 16px;
  background:linear-gradient(to top,#ffffff,#f9fafb);
  box-shadow:0 8px 25px rgba(15,89,47,.08);
  margin-top:0;
}

.ev-select{
  max-width: 120px;
}

/* paginador */
.ev-pagination{
  display:flex;
  list-style:none;
  gap:.35rem;
  margin:0;
  padding:0;
}
.ev-page-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:30px;
  height:28px;
  border:1px solid #E5E7EB;
  background:#ffffff;
  color:#4B5563;
  border-radius:.75rem;
  cursor:pointer;
  transition: background .13s ease, color .13s ease, box-shadow .13s ease, transform .05s ease;
  padding:0 .55rem;
  font-size:.78rem;
}
.ev-page-btn i{ font-size:.75rem; }
.ev-page-btn:hover{
  background:#ecfdf5;
  box-shadow:0 2px 6px rgba(15,23,42,.18);
}
.ev-page-btn.active{
  background:#0F592F;
  color:#ffffff;
  border-color:#0F592F;
  box-shadow:0 4px 12px rgba(22,163,74,.45);
}
.ev-page-btn:disabled{
  opacity:.45;
  cursor:not-allowed;
  box-shadow:none;
}

/* Footer responsive */
@media (max-width: 768px){
  .ev-foot{
    flex-direction:column;
    align-items:flex-start;
  }
}

/* ================================
   ✨ BOTONES REUTILIZABLES
================================ */
.btn-outline-success,
.btn-cancelar{
  border-radius: 999px;
  padding: 8px 20px;
  font-weight: 600;
  font-size: 0.93rem;
  transition: all 0.22s ease;
}
.btn-outline-success{
  color:#0F592F;
  border-color:#0F592F;
  background:transparent;
}
.btn-outline-success:hover{
  background:#0F592F;
  color:#fff;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(15, 89, 47, 0.28);
}
.btn-cancelar{
  color:#374151;
  background:#e5e7eb;
  border:1px solid #d1d5db;
}
.btn-cancelar:hover{
  background:#d1d5db;
  color:#111827;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(15,23,42,0.15);
}

.btn-guardar{
  position:relative;
  overflow:hidden;
  transition:all .25s ease;
}
.btn-guardar.saving{
  pointer-events:none;
  color:transparent !important;
}
.btn-guardar.saving::after{
  content:'';  
  position:absolute;
  top:50%; left:50%;
  width:18px; height:18px;
  margin-top:-9px; margin-left:-9px;
  border:2px solid #fff;
  border-top-color:transparent;
  border-radius:50%;
  animation: spin .8s linear infinite;
}
@keyframes spin{ from{ transform:rotate(0)} to{ transform:rotate(360deg)} }

/* ================================
   🪟 MODALES (estilo visual general)
================================ */
.modal .modal-content{
  border-radius: 18px;
  overflow: hidden;
  border:1px solid rgba(148,163,184,.5);
}
.modal .modal-header{
  background: radial-gradient(circle at 0 0, #22c55e 0, #0F592F 45%, #052e16 100%);
  color:#fff;
  border-bottom: 0;
  border-top-left-radius: 18px;
  border-top-right-radius: 18px;
  box-shadow: 0 4px 16px rgba(15,23,42,.35);
}
.modal .modal-title{
  color:#f9fafb;
  font-weight:620;
}
.modal .btn-close{
  filter: invert(1) contrast(120%);
  opacity:.85;
}
.modal .btn-close:hover{ opacity:1; }

/* Ajuste específico para modales fullscreen en móviles:
   sin bordes blancos laterales */
@media (max-width: 576px){
  .modal-fullscreen-sm-down .modal-content,
  .modal-fullscreen-md-down .modal-content{
    border-radius: 0;
  }

  .modal-fullscreen-sm-down .modal-dialog,
  .modal-fullscreen-md-down .modal-dialog{
    margin: 0;
    max-width: 100%;
  }
}

/* ================================
   📸 UPLOADER
================================ */
.ev-uploader{
  margin-top:6px;
}

/* Dropzone */
.ev-dropzone{
  border:2px dashed #cbd5e1;
  border-radius:16px;
  padding:28px 20px;
  text-align:center;
  background:linear-gradient(135deg,#f8fafc,#f9fafb);
  cursor:pointer;
  transition:border-color .2s, background .2s, box-shadow .2s;
}
.ev-dropzone:hover{
  border-color:#0F592F;
  background:#f3faf6;
  box-shadow:0 6px 16px rgba(15,23,42,.08);
}
.ev-dropzone .dz-icon{
  font-size:40px;
  color:#0F592F;
  margin-bottom:6px;
}
.ev-dropzone .dz-text{
  font-size:1rem;
  font-weight:650;
  color:#0F592F;
}
.ev-dropzone .dz-subtext{
  font-size:.82rem;
  color:#64748b;
}
.ev-dropzone.drag-over{
  background:#e6f7ee !important;
  border-color:#16a34a !important;
}

/* Grid miniaturas */
.ev-tiles{
  margin-top:12px;
}
.ev-tiles.ev-tiles-grid{
  display:grid;
  grid-template-columns: repeat(auto-fill, 120px);
  gap:12px;
}

/* Miniatura */
.ev-tile{
  position:relative;
  width:120px;
  height:120px;
  border-radius:14px;
  overflow:hidden;
  background:#ffffff;
  border:1px solid #e5e7eb;
  display:flex;
  align-items:center;
  justify-content:center;
  transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
  box-shadow:0 3px 8px rgba(15,23,42,.08);
}
.ev-tile:hover{
  transform: translateY(-2px);
  border-color:#cbd5e1;
  box-shadow:0 6px 14px rgba(15,23,42,.18);
}
.ev-tile img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

/* Ocultar tile-agregar: usamos dropZone como entrada principal */
.ev-tiles .ev-tile-add{
  display:none !important;
}

/* Badge “Principal” */
.ev-tiles-grid .ev-tile:first-child::after{
  content:"Principal";
  position:absolute;
  bottom:6px;
  left:6px;
  background:#0F592F;
  color:#fff;
  font-size:.7rem;
  padding:2px 6px;
  border-radius:6px;
}

/* Botón eliminar */
.ev-tile-remove{
  position:absolute;
  top:6px;
  right:6px;
  background:#ef4444;
  color:#fff;
  border:0;
  border-radius:999px;
  width:26px;
  height:26px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:16px;
  cursor:pointer;
  box-shadow:0 2px 6px rgba(239,68,68,.35);
}
.ev-tile-remove:hover{
  filter:brightness(1.07);
}

/* ================================
   🖼️ PREVISUALIZACIÓN
================================ */
.ev-preview-area{
  margin-top:16px;
  border:1px dashed #cbd5e1;
  border-radius:14px;
  background:#f8fafc;
  padding:14px;
}
.ev-preview-title{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:.5rem;
  font-weight:700;
  color:#0F592F;
  margin-bottom:10px;
}
.ev-preview-actions{
  display:flex;
  gap:.45rem;
}
.ev-preview-actions .btn{
  padding:.2rem .55rem;
  line-height:1;
  border-radius:999px;
}

.ev-preview-main{
  width:100%;
  min-height:260px;
  max-height:56vh;
  background:linear-gradient(#f3f4f6,#eef2f7);
  border-radius:12px;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:8px;
  overflow:hidden;
}
.ev-preview-main img{
  max-width:100%;
  max-height:56vh;
  object-fit:contain;
  border-radius:8px;
  border: 1px solid #e5e7eb;
  background: conic-gradient(#f5f5f5 25%, transparent 0 50%, #f5f5f5 0 75%, transparent 0) 50%/20px 20px content-box;
}
.ev-preview-thumbs{
  display:flex;
  gap:8px;
  overflow-x:auto;
  padding-top:10px;
  scroll-snap-type:x mandatory;
}
.ev-preview-thumb{
  flex:0 0 auto;
  width:88px;
  height:66px;
  border:2px solid transparent;
  border-radius:10px;
  background:#fff;
  box-shadow:0 2px 6px rgba(15,23,42,.1);
  padding:2px;
  cursor:pointer;
  transition: transform .12s ease, border-color .12s ease, box-shadow .12s ease;
  scroll-snap-align:start;
}
.ev-preview-thumb:hover{
  transform: translateY(-1px);
}
.ev-preview-thumb img{
  width:100%;
  height:100%;
  object-fit:cover;
  border-radius:8px;
}
.ev-preview-thumb.active{
  border-color:#0F592F;
  box-shadow:0 0 0 .15rem rgba(15,89,47,.22);
}

/* Expandido */
.ev-preview-area.is-expanded .ev-preview-main{
  max-height:76vh;
}
.ev-preview-area.is-expanded .ev-preview-main img{
  max-height:76vh;
}
#contadorImagenes{
  font-weight:600;
  color:#1b3d2f;
  opacity:.8;
}

/* ================================
   🌀 ANIMACIONES / RESPONSIVE
================================ */
.fade-in{
  animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn{
  from{opacity:0; transform: translateY(6px);}
  to{opacity:1; transform:none;}
}

@media (max-width: 768px){
  .ev-chip{
    min-width:auto;
    padding:.38rem .55rem;
    font-size:.82rem;
  }
  .ev-table-wrap{
    max-height: unset;
  }
}

@media (max-width: 576px){
  .card-body{
    padding:18px 14px;
  }

  .ev-table thead{
    display:none;
  }
  .ev-table tbody tr{
    display:grid;
    grid-template-columns:1fr;
    gap:.35rem;
    border-bottom:1px solid #e5e7eb;
    padding:.6rem .75rem;
  }
  .ev-table tbody td{
    display:flex;
    justify-content:space-between;
    gap:.75rem;
    border-bottom:0;
    padding:.3rem 0;
  }
  .ev-table tbody td::before{
    content: attr(data-label);
    font-weight:600;
    color:#64748b;
    font-size:.8rem;
  }
  .ev-foot{
    flex-wrap:wrap;
  }
  .ev-preview-thumb{
    width:72px;
    height:54px;
  }
  .ev-preview-main{
    min-height:200px;
  }
}

/* Modal XL en pantallas grandes */
@media (min-width: 1200px){
  .modal-dialog.modal-xl{
    --bs-modal-width: 1160px;
  }
}
@media (min-width: 1400px){
  .modal-dialog.modal-xl{
    --bs-modal-width: 1280px;
  }
}

/* Grid interno modal izq/der */
.mpm-grid{
  display:grid;
  grid-template-columns: 520px 1fr;
  gap: 16px;
}
.mpm-left{ min-width: 0; }
.mpm-right{ min-width: 0; }
.mpm-preview-wrap{
  position: sticky;
  top: 12px;
}

@media (max-width: 991.98px){
  .mpm-grid{
    grid-template-columns: 1fr;
    gap:14px;
  }
  .mpm-preview-wrap{
    position: static;
  }
  #modalAgregarPublicacion .ev-preview-main,
  #modalEditarPublicacion .ev-preview-main{
    max-height: 40vh !important;
  }
}

/* Ocultar previsualización solo móvil */
@media (max-width: 576px){
  #previewMount,
  #evPreviewWrapper,
  #evMetaCard{
    display:none !important;
  }
  .mpm-right{
    display:none !important;
  }
  .mpm-grid{
    grid-template-columns: 1fr !important;
  }
}

/* ================================
   🧰 TOOLBAR UPLOADER
================================ */
.ev-toolbar-uploads{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:.75rem;
  margin-top:10px;
}
.ev-toolbar-uploads-count{
  font-size:.84rem;
  color:#6b7280;
}
.btn-clear-images{
  border-radius:999px;
  background:#f3f4f6;
  border:1px solid #e5e7eb;
  color:#374151;
  font-size:.83rem;
  padding:6px 14px;
  display:inline-flex;
  align-items:center;
  gap:.4rem;
  font-weight:500;
  transition:all .18s ease;
}
.btn-clear-images i{
  font-size:.9rem;
}
.btn-clear-images:hover{
  background:#e5e7eb;
  border-color:#d1d5db;
  color:#111827;
  transform:translateY(-1px);
  box-shadow:0 2px 6px rgba(15,23,42,.16);
}

.ev-imagenes-actuales{
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
  padding: 10px 0;
}

.ev-edit-img{
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: 12px;
  box-shadow: 0 3px 12px rgba(0,0,0,.15);
}

.ev-img-wrapper{
  position: relative;
}
</style>
