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
  display:flex;
  align-items:center;
  justify-content:center;
  width:100%;
  min-height:38px;
  padding:10px 12px;
  border-bottom:1px solid rgba(229,231,235,0.85);
  text-align:center;
  line-height:1.15;
  box-sizing:border-box;
}

.ev-mp-card-top-status-text{
  display:block;
  width:100%;
  font-size:clamp(11px, 2.8vw, 14px);
  font-weight:800;
  letter-spacing:.01em;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.ev-mp-card-top-status-on{
  background:linear-gradient(135deg, rgba(15,89,47,0.14), rgba(22,163,74,0.20));
  color:var(--ev-verde-oscuro);
}

.ev-mp-card-top-status-off{
  background:linear-gradient(135deg, rgba(107,114,128,0.12), rgba(229,231,235,0.70));
  color:#6B7280;
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

/* ==========================================================
   SWEETALERT EV - MARKETPLACE
========================================================== */
.ev-mp-swal-popup{
  border-radius:28px !important;
  padding:28px 24px 22px !important;
  box-shadow:
    0 28px 70px rgba(15,23,42,.20),
    0 10px 24px rgba(15,89,47,.08) !important;
  border:1px solid rgba(229,231,235,.96) !important;
  background:
    radial-gradient(circle at top, rgba(230,244,236,.65) 0%, rgba(255,255,255,1) 26%, rgba(255,255,255,1) 100%) !important;
}

.ev-mp-swal-popup.ev-mp-swal-popup-seguimiento{
  width:min(92vw, 560px) !important;
  overflow:hidden !important;
}

.ev-mp-swal-title{
  color:var(--ev-verde-oscuro) !important;
  font-weight:900 !important;
  letter-spacing:-.03em !important;
  font-size:2.15rem !important;
  line-height:1.04 !important;
  margin:0 0 8px 0 !important;
}

.ev-mp-swal-html{
  color:var(--ev-texto-suave) !important;
  font-size:1rem !important;
  line-height:1.55 !important;
  margin-top:0 !important;
}

.ev-mp-swal-confirm{
  background:linear-gradient(135deg, #EA7C12, #F59E0B) !important;
  border:none !important;
  color:#fff !important;
  border-radius:16px !important;
  padding:13px 24px !important;
  min-width:156px !important;
  font-weight:900 !important;
  font-size:1rem !important;
  box-shadow:0 14px 30px rgba(234,124,18,.32) !important;
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease !important;
}

.ev-mp-swal-confirm:hover{
  filter:brightness(1.03) !important;
  transform:translateY(-1px) !important;
  box-shadow:0 18px 34px rgba(234,124,18,.42) !important;
}

.ev-mp-swal-cancel{
  background:#fff !important;
  border:1.6px solid #EF4444 !important;
  color:#EF4444 !important;
  border-radius:16px !important;
  padding:13px 24px !important;
  min-width:180px !important;
  font-weight:900 !important;
  font-size:1rem !important;
  box-shadow:0 8px 18px rgba(239,68,68,.08) !important;
  transition:transform .16s ease, box-shadow .16s ease, background .16s ease !important;
}

.ev-mp-swal-cancel:hover{
  background:#FEF2F2 !important;
  transform:translateY(-1px) !important;
  box-shadow:0 12px 24px rgba(239,68,68,.14) !important;
}

.ev-mp-swal-loader{
  width:62px;
  height:62px;
  border-radius:50%;
  border:5px solid rgba(22,163,74,.16);
  border-top-color:rgba(15,89,47,.96);
  margin:4px auto 16px auto;
  animation:evMpSpin .85s linear infinite;
}

@keyframes evMpSpin{
  to{ transform:rotate(360deg); }
}

.ev-mp-swal-status-icon{
  width:94px;
  height:94px;
  margin:0 auto 14px auto;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  background:linear-gradient(180deg, rgba(230,244,236,.88), rgba(255,255,255,.98));
  border:2px solid rgba(22,163,74,.20);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.9),
    0 10px 28px rgba(15,89,47,.08);
}

.ev-mp-swal-status-icon i{
  font-size:44px;
  color:var(--ev-verde);
  line-height:1;
}

.ev-mp-swal-subtitle{
  font-weight:900;
  font-size:1.15rem;
  color:var(--ev-verde-oscuro);
  margin-bottom:8px;
  letter-spacing:-.02em;
}

.ev-mp-swal-soft-text{
  font-size:14px;
  color:#6B7280;
  line-height:1.6;
}

.ev-mp-swal-timer-wrap{
  margin-top:16px;
}

.ev-mp-swal-timer-pill{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  padding:12px 18px;
  border-radius:999px;
  background:linear-gradient(135deg, #E6F4EC, #F0FDF4);
  color:#0F592F;
  font-weight:900;
  font-size:14px;
  border:1px solid rgba(22,163,74,.10);
  box-shadow:0 10px 22px rgba(15,89,47,.08);
}

.ev-mp-swal-timer-pill i{
  font-size:15px;
  opacity:.9;
}

.ev-mp-swal-product-card{
  margin-top:16px;
  padding:13px 16px;
  border-radius:18px;
  background:#fff;
  border:1px solid rgba(229,231,235,.95);
  box-shadow:0 8px 22px rgba(15,23,42,.05);
}

.ev-mp-swal-product-label{
  display:block;
  font-size:11px;
  font-weight:800;
  text-transform:uppercase;
  letter-spacing:.08em;
  color:#9CA3AF;
  margin-bottom:5px;
}

.ev-mp-swal-product{
  font-size:15px;
  color:#1A1F36;
  font-weight:800;
  word-break:break-word;
}

.ev-mp-swal-cancel-hint{
  margin-top:12px;
  font-size:12.5px;
  color:#6B7280;
  min-height:18px;
}

.ev-mp-swal-note{
  margin-top:16px;
  padding:14px 16px;
  border-radius:18px;
  background:linear-gradient(180deg, #FFF7ED, #FFFDF9);
  border:1px solid rgba(234,124,18,.22);
  color:#9A3412;
  font-size:13.5px;
  line-height:1.55;
  box-shadow:0 8px 18px rgba(234,124,18,.08);
}

.ev-mp-swal-note strong{
  font-weight:900;
}

.ev-mp-swal-divider{
  width:72px;
  height:4px;
  border-radius:999px;
  margin:14px auto 0 auto;
  background:linear-gradient(90deg, rgba(15,89,47,.10), rgba(22,163,74,.42), rgba(15,89,47,.10));
}

.ev-mp-swal-popup-bounce{
  animation:evMpSwalBounce .34s ease;
}

@keyframes evMpSwalBounce{
  0%   { transform:scale(1) translateX(0); }
  22%  { transform:scale(1.01) translateX(-8px); }
  46%  { transform:scale(1.01) translateX(8px); }
  68%  { transform:scale(1.005) translateX(-4px); }
  100% { transform:scale(1) translateX(0); }
}

.ev-mp-swal-danger{
  color:#B91C1C !important;
}

.ev-mp-swal-success{
  color:#0F592F !important;
}

@media(max-width:575.98px){
  .ev-mp-swal-popup{
    padding:22px 16px 18px !important;
    border-radius:22px !important;
  }

  .ev-mp-swal-title{
    font-size:1.75rem !important;
  }

  .ev-mp-swal-status-icon{
    width:82px;
    height:82px;
    margin-bottom:12px;
  }

  .ev-mp-swal-status-icon i{
    font-size:36px;
  }

  .ev-mp-swal-subtitle{
    font-size:1.02rem;
  }

  .ev-mp-swal-confirm,
  .ev-mp-swal-cancel{
    width:100% !important;
    min-width:0 !important;
  }
}
</style>