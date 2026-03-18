<?php /* views/estilos/marketplaceEstilo.php */ ?>

<style>
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#198754;
  --ev-verde-suave:#E6F4EC;
  --ev-naranja:#FF7A1A;

  --ev-gris-fondo:#F3F4F6;
  --ev-gris-borde:#E5E7EB;

  --ev-texto:#1A1F36;
  --ev-texto-suave:#6B7280;

  --ev-shadow-soft: 0 12px 40px rgba(15,23,42,0.08);
  --ev-shadow-card: 0 10px 28px rgba(15,23,42,0.10);
  --ev-radius-lg: 18px;
}

/* Layout */
.ev-mp-wrapper{
  background-color:var(--ev-gris-fondo);
  padding-bottom:40px;
  width:100%;
}

.ev-mp-wrapper .container-fluid{
  padding-left:0 !important;
  padding-right:0 !important;
}

.ev-mp-content{
  margin:0 !important;
  padding-left:6px !important;
  padding-right:12px !important;
  max-width:1320px;
}

@media (min-width:992px){
  .ev-mp-content{
    padding-left:4px !important;
    padding-right:14px !important;
  }
}

@media (min-width:1400px){
  .ev-mp-content{ max-width:1400px; }
}

/* Header */
.ev-mp-header{
  border-radius:var(--ev-radius-lg);
  border:1px solid var(--ev-gris-borde);
  background:#ffffff;
  box-shadow:var(--ev-shadow-soft);
}

.ev-mp-header .card-body{
  padding:16px 24px 12px 24px;
}

.ev-mp-title{
  font-size:28px;
  font-weight:800;
  color:var(--ev-verde-oscuro);
  letter-spacing:-0.02em;
}

.ev-mp-subtitle{
  font-size:14px;
  color:var(--ev-texto-suave);
}

/* Condominio chip */
.ev-mp-condominio{
  display:flex;
  align-items:center;
  gap:12px;
  padding:9px 16px;
  background:var(--ev-verde-suave);
  border-radius:999px;
  width:fit-content;
}

.ev-mp-condominio-icon{
  width:36px;
  height:36px;
  border-radius:50%;
  background:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
  color:var(--ev-verde-oscuro);
  font-size:18px;
}

.ev-mp-condominio-text{ display:flex; flex-direction:column; }
.ev-mp-condominio-label{
  font-size:11px;
  text-transform:uppercase;
  letter-spacing:0.05em;
  color:var(--ev-texto-suave);
}
.ev-mp-condominio-name{
  font-size:14px;
  font-weight:700;
  color:var(--ev-verde-oscuro);
}

/* Buscar + ordenar */
.ev-mp-search-row{
  display:flex;
  flex-wrap:wrap;
  gap:14px;
  margin-top:14px;
}

.ev-mp-search-input-wrapper{
  flex:1;
  min-width:260px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:8px 14px;
  border-radius:999px;
  border:1px solid var(--ev-gris-borde);
  background:#fff;
  height:46px;
  box-sizing:border-box;
}

.ev-mp-search-input-wrapper i{
  font-size:18px;
  color:var(--ev-texto-suave);
}

.ev-mp-search-input{
  border:none!important;
  box-shadow:none!important;
  font-size:14px;
  width:100%;
}
.ev-mp-search-input:focus{ outline:none; }

.ev-mp-search-actions{
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  gap:10px;
}

.ev-mp-sort-wrapper{ display:flex; align-items:center; gap:6px; }
.ev-mp-sort-label{ font-size:13px; color:var(--ev-texto-suave); }

.ev-mp-sort-select{
  height:46px;
  border-radius:12px;
  font-size:13px;
}

/* Advanced filters */
.ev-mp-filters-advanced{
  margin-top:12px;
  display:flex;
  flex-wrap:wrap;
  gap:14px;
  align-items:center;
  justify-content:space-between;
}

.ev-mp-scope{ display:flex; align-items:center; gap:10px; }
.ev-mp-scope-label{ font-size:13px; color:var(--ev-texto-suave); }

.ev-mp-seg{
  display:flex;
  background:#fff;
  border:1px solid var(--ev-gris-borde);
  border-radius:999px;
  padding:4px;
  box-shadow: 0 6px 18px rgba(15,23,42,0.05);
}

.ev-mp-seg-btn{
  border:none;
  background:transparent;
  padding:8px 14px;
  border-radius:999px;
  font-size:13px;
  color:var(--ev-texto-suave);
  cursor:pointer;
  transition:all .15s ease;
  font-weight:600;
}

.ev-mp-seg-btn:hover{ color:var(--ev-texto); background:#F3F4F6; }
.ev-mp-seg-btn.active{
  background:var(--ev-verde-oscuro);
  color:#fff;
}

.ev-mp-cat-producto{
  display:flex;
  align-items:center;
  gap:10px;
}

.ev-mp-cat-select{
  min-width:260px;
  height:42px;
  border-radius:12px;
  font-size:13px;
}

/* Resumen */
.ev-mp-resumen{
  font-size:13px;
  color:var(--ev-texto-suave);
  margin-top:10px;
}

/* Empty */
#mp_empty_state{
  display:none;
  margin-top:24px;
  text-align:center;
  color:var(--ev-texto-suave);
}

/* Secciones */
.ev-mp-split{ display:flex; flex-direction:column; gap:18px; }

.ev-mp-section{
  background:transparent;
  border-radius:var(--ev-radius-lg);
}

.ev-mp-section-head{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:12px;
  padding:6px 2px 10px 2px;
}

.ev-mp-section-kicker{
  font-size:11px;
  text-transform:uppercase;
  letter-spacing:.08em;
  color:var(--ev-texto-suave);
  margin-bottom:2px;
}

.ev-mp-section-title{
  margin:0;
  font-weight:800;
  color:var(--ev-verde-oscuro);
  font-size:18px;
  display:flex;
  align-items:center;
  gap:8px;
}

.ev-mp-section-sub{
  margin-top:2px;
  font-size:13px;
  color:var(--ev-texto-suave);
}

.ev-mp-section-pill{
  min-width:44px;
  height:28px;
  padding:0 10px;
  display:flex;
  align-items:center;
  justify-content:center;
  border-radius:999px;
  background:#fff;
  border:1px solid var(--ev-gris-borde);
  font-weight:800;
  color:var(--ev-texto);
  box-shadow: 0 8px 20px rgba(15,23,42,0.06);
}

/* GRID premium responsive */
.ev-mp-grid{
  margin-top:8px;
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));
  gap:18px;
  width:100%;
}

/* Card */
.ev-mp-card{
  border-radius:var(--ev-radius-lg);
  overflow:hidden;
  border:1px solid var(--ev-gris-borde);
  background:#fff;
  display:flex;
  flex-direction:column;
  transition:all .22s ease;
  box-shadow:var(--ev-shadow-card);
  gap:0;
}

.ev-mp-card:hover{
  transform:translateY(-4px);
  box-shadow:0 18px 40px rgba(15,23,42,0.14);
  border-color:var(--ev-verde);
}

/* Barra superior de disponibilidad */
.ev-mp-card-top-status{
  width:100%;
  display:flex;
  align-items:center;
  justify-content:center;
  min-height:34px;
  padding:8px 12px;
  margin:0;
  border:none;
  border-radius:0;
  font-size:13px;
  font-weight:800;
  line-height:1.1;
  letter-spacing:.01em;
  text-align:center;
}

.ev-mp-card-top-status-text{
  display:block;
  width:100%;
  margin:0;
  padding:0;
}

.ev-mp-card-top-status-on{
  background:#DCFCE7;
  color:#166534;
  border-bottom:1px solid rgba(22,101,52,0.10);
}

.ev-mp-card-top-status-off{
  background:#E5E7EB;
  color:#4B5563;
  border-bottom:1px solid rgba(75,85,99,0.08);
}

.ev-mp-card-media{
  position:relative;
  width:100%;
  height:220px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:#f5f7fa;
  margin:0;
}

.ev-mp-card-media img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

.ev-mp-card-badges{
  position:absolute;
  top:10px;
  left:10px;
  display:flex;
  gap:6px;
}

.ev-mp-badge{
  padding:3px 9px;
  border-radius:25px;
  font-size:12px;
  backdrop-filter:blur(3px);
  color:#fff;
}

.ev-mp-badge-potenciado{ background:#FF7A1Acc; }
.ev-mp-badge-nuevo{ background:#22c55ecc; display:none !important; }

.ev-mp-card-body{
  padding:12px 14px 12px 14px;
  display:flex;
  flex-direction:column;
  gap:6px;
}

.ev-mp-card-title{
  margin:0;
  font-weight:700;
  color:var(--ev-texto);
  font-size:15px;
}

.ev-mp-card-price{
  margin:0;
  font-weight:800;
  color:var(--ev-verde);
  font-size:17px;
}

.ev-mp-card-actions{
  margin-top:8px;
  display:flex;
  justify-content:space-between;
  gap:8px;
}

.ev-mp-card-actions .btn{
  flex:1;
  border-radius:999px;
  transition:all .16s ease;
}

.ev-mp-card-actions .btn:hover{
  transform:translateY(-2px);
  box-shadow:0 4px 12px rgba(0,0,0,0.12);
}

.ev-mp-card-actions .ev-mp-btn-detalle{
  border:1px solid var(--ev-gris-borde);
  background:#ffffff;
  color:var(--ev-texto-suave);
  font-weight:700;
}

.ev-mp-card-actions .ev-mp-btn-detalle:hover{
  background:#F3F4F6;
  color:var(--ev-texto);
}

.ev-mp-card-actions .ev-mp-btn-pedir{
  border:none;
  background:linear-gradient(135deg, #D97706, #EA7C12);
  color:#ffffff;
  font-weight:800;
  box-shadow:0 10px 24px rgba(217,119,6,0.30);
}

.ev-mp-card-actions .ev-mp-btn-pedir:hover{
  background:linear-gradient(135deg, #C46B05, #D46F0F);
  color:#ffffff;
  box-shadow:0 12px 28px rgba(217,119,6,0.40);
}

/* Modal detalle */
.ev-mp-modal-dialog{
  width: min(980px, calc(100% - 24px));
  max-width: 980px !important;
  margin-left: auto;
  margin-right: auto;
}

.ev-mp-modal-content{
  border-radius:18px;
  overflow:hidden;
  border:1px solid var(--ev-gris-borde);
  box-shadow:0 18px 60px rgba(15,23,42,0.25);
}

.ev-mp-modal-header{
  border-bottom:1px solid var(--ev-gris-borde);
  background:#ffffff;
}

.ev-mp-modal-header .modal-title{
  font-weight:700;
  color:var(--ev-verde-oscuro);
  font-size:1rem;
}

.ev-mp-modal-header i{ color:var(--ev-verde-oscuro); }

.ev-mp-modal-body{
  padding:18px 22px;
  background:#F3F4F6;
}

.ev-mp-preview-card{
  background:#ffffff;
  border-radius:18px;
  border:1px solid var(--ev-gris-borde);
  padding:16px;
  box-shadow:0 8px 24px rgba(15,23,42,0.06);
  max-width: 900px;
  margin: 0 auto;
}

.ev-mp-modal-media{
  width:100%;
  max-height:520px;
  border-radius:18px;
  border:1px solid #E3E8EF;
  background:#ffffff;
  display:flex;
  justify-content:center;
  align-items:center;
  overflow:hidden;
  padding:8px;
  margin-bottom:14px;
}

.ev-mp-modal-media-inner{
  width:100%;
  height:100%;
  display:flex;
  align-items:center;
  justify-content:center;
  overflow:hidden;
}

.ev-mp-modal-media img{
  width:auto !important;
  height:auto !important;
  max-width:100% !important;
  max-height:500px !important;
  object-fit:contain !important;
  border-radius:12px;
  display:block;
}

.ev-mp-modal-thumbs{
  display:flex;
  gap:10px;
  margin:15px 0 20px;
  padding-left:4px;
  overflow:auto;
}

.ev-mp-modal-thumb{
  width:62px;
  height:62px;
  border-radius:8px;
  border:2px solid transparent;
  overflow:hidden;
  cursor:pointer;
  flex-shrink:0;
  background:#f9fafb;
}

.ev-mp-modal-thumb img{
  width:100%;
  height:100%;
  object-fit:cover;
  transform:scale(1.02);
  transform-origin:center center;
  transition:transform 0.2s ease;
}

.ev-mp-modal-thumb:hover img{ transform:scale(1.06); }
.ev-mp-modal-thumb.active{ border-color:var(--ev-verde); }

.ev-mp-modal-title{
  font-size:20px;
  font-weight:800;
  color:var(--ev-verde-oscuro);
  margin-bottom:4px;
}

.ev-mp-modal-price{
  font-size:18px;
  font-weight:800;
  color:var(--ev-verde);
  margin-bottom:14px;
}

.ev-mp-modal-desc{
  font-size:14px;
  color:#555;
  line-height:1.5;
}

.ev-mp-modal-footer{
  border-top:none;
  padding:16px 24px 20px 24px;
  background:#fff;
}

.ev-mp-modal-footer .btn-ev-neutral{
  min-width:150px;
  background:#ffffff;
  border:1px solid var(--ev-gris-borde);
  color:var(--ev-texto-suave);
  border-radius:999px;
  font-weight:700;
}

.ev-mp-modal-footer .btn-ev-neutral:hover{
  background:#F3F4F6;
  color:var(--ev-texto);
}

.ev-mp-modal-footer .btn-ev-primary{
  min-width:150px;
  border:none;
  border-radius:999px;
  background:linear-gradient(135deg, #D97706, #EA7C12);
  color:#ffffff;
  font-weight:800;
  box-shadow:0 8px 20px rgba(217,119,6,0.35);
}

.ev-mp-modal-footer .btn-ev-primary:hover{
  background:linear-gradient(135deg, #C46B05, #D46F0F);
  color:#ffffff;
  box-shadow:0 10px 24px rgba(217,119,6,0.45);
}

@media(max-width:575.98px){
  .ev-mp-modal-body{ padding:14px; }
  .ev-mp-preview-card{ border-radius:16px; }
  .ev-mp-modal-media{ max-height:360px; }
  .ev-mp-modal-media img{ max-height:340px !important; }
  .ev-mp-cat-select{ min-width: 100%; }

  .ev-mp-card-top-status{
    min-height:32px;
    padding:7px 10px;
    font-size:12px;
  }
}

@media (max-width:991.98px){
  .ev-mp-header .card-body{ padding:14px 16px 10px 16px; }
  .ev-mp-title{ font-size:24px; }
  .ev-mp-content{
    padding-left:6px !important;
    padding-right:8px !important;
  }
}
</style>