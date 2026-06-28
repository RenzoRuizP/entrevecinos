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

  --ev-shadow-soft:0 12px 40px rgba(15,23,42,0.08);
  --ev-shadow-card:0 10px 28px rgba(15,23,42,0.10);
  --ev-radius-lg:18px;
}

/* ==========================================================
   LAYOUT MARKETPLACE — ancho completo como Mis Publicaciones
========================================================== */
.ev-mp-wrapper{
  background-color:var(--ev-gris-fondo);
  padding-bottom:40px;
  width:100%;
  min-height:calc(100vh - 64px);
}

.ev-mp-wrapper .container-fluid{
  width:100% !important;
  max-width:none !important;
  padding-left:0 !important;
  padding-right:0 !important;
}

.ev-mp-content{
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
  padding-left:18px !important;
  padding-right:18px !important;
}

@media (min-width:992px){
  .ev-mp-content{
    padding-left:28px !important;
    padding-right:28px !important;
  }
}

@media (min-width:1400px){
  .ev-mp-content{
    width:100% !important;
    max-width:none !important;
  }
}

/* ==========================================================
   HEADER / BUSCADOR / FILTROS
========================================================== */
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

.ev-mp-condominio-text{
  display:flex;
  flex-direction:column;
}

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

.ev-mp-search-row{
  display:flex;
  flex-wrap:wrap;
  gap:14px;
  margin-top:14px;
}

.ev-mp-search-input-wrapper{
  flex:1 1 540px;
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
  background:transparent;
}

.ev-mp-search-input:focus{ outline:none; }

.ev-mp-search-actions{
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  gap:10px;
}

.ev-mp-sort-wrapper{
  display:flex;
  align-items:center;
  gap:6px;
}

.ev-mp-sort-label{
  font-size:13px;
  color:var(--ev-texto-suave);
}

.ev-mp-sort-select{
  height:46px;
  min-width:210px;
  border-radius:12px;
  font-size:13px;
}

.ev-mp-filters-advanced{
  margin-top:12px;
  display:flex;
  flex-wrap:wrap;
  gap:14px;
  align-items:center;
  justify-content:space-between;
}

.ev-mp-scope{
  display:flex;
  align-items:center;
  gap:10px;
}

.ev-mp-scope-label{
  font-size:13px;
  color:var(--ev-texto-suave);
}

.ev-mp-seg{
  display:flex;
  background:#fff;
  border:1px solid var(--ev-gris-borde);
  border-radius:999px;
  padding:4px;
  box-shadow:0 6px 18px rgba(15,23,42,0.05);
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

.ev-mp-seg-btn:hover{
  color:var(--ev-texto);
  background:#F3F4F6;
}

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

.ev-mp-resumen{
  font-size:13px;
  color:var(--ev-texto-suave);
  margin-top:10px;
}

#mp_empty_state{
  display:none;
  margin-top:24px;
  text-align:center;
  color:var(--ev-texto-suave);
}

/* ==========================================================
   SECCIONES
========================================================== */
.ev-mp-split{
  display:flex;
  flex-direction:column;
  gap:18px;
}

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
  box-shadow:0 8px 20px rgba(15,23,42,0.06);
}

/* ==========================================================
   GRID + CARD UNIFORME EV
========================================================== */
.ev-mp-grid{
  margin-top:8px;
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(250px, 250px));
  gap:18px;
  width:100%;
  align-items:stretch;
  justify-content:start;
}

.ev-mp-card{
  width:250px;
  height:472px;
  min-height:472px;
  max-height:472px;
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

.ev-mp-card-top-status{
  flex:0 0 38px;
  height:38px;
  min-height:38px;
  max-height:38px;
  display:flex;
  align-items:center;
  justify-content:center;
  width:100%;
  padding:8px 12px;
  border-bottom:1px solid rgba(229,231,235,0.85);
  text-align:center;
  line-height:1.15;
  box-sizing:border-box;
}

.ev-mp-card-top-status-text{
  display:block;
  width:100%;
  font-size:13px;
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
  flex:0 0 166px;
  width:100%;
  height:166px;
  min-height:166px;
  max-height:166px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:#F8FAFC;
  margin:0;
  overflow:hidden;
}

.ev-mp-card-media img{
  width:100%;
  height:100%;
  object-fit:contain;
  object-position:center;
  display:block;
}

.ev-mp-card-badges{
  position:absolute;
  top:10px;
  left:10px;
  display:flex;
  gap:6px;
  z-index:2;
}

.ev-mp-badge{
  padding:3px 9px;
  border-radius:25px;
  font-size:12px;
  backdrop-filter:blur(3px);
  color:#fff;
  font-weight:700;
}

.ev-mp-badge-potenciado{ background:#FF7A1Acc; }
.ev-mp-badge-nuevo{ background:#22c55ecc; display:none !important; }

.ev-mp-card-body{
  flex:1 1 auto;
  min-height:0;
  padding:13px 14px 13px 14px;
  display:flex;
  flex-direction:column;
  gap:6px;
}

.ev-mp-card-title{
  margin:0;
  min-height:38px;
  max-height:38px;
  overflow:hidden;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  font-weight:800;
  color:var(--ev-texto);
  font-size:14px;
  line-height:1.32;
}

.ev-mp-card-price{
  margin:0;
  min-height:22px;
  font-weight:900;
  color:var(--ev-verde);
  font-size:17px;
  line-height:1.25;
}

.ev-mp-card-body p:not(.ev-mp-card-price){
  min-height:38px !important;
  max-height:38px !important;
  overflow:hidden !important;
  display:-webkit-box !important;
  -webkit-line-clamp:2 !important;
  -webkit-box-orient:vertical !important;
  margin-bottom:0 !important;
  line-height:1.35 !important;
}

.ev-mp-card-actions{
  margin-top:auto;
  display:flex;
  justify-content:space-between;
  gap:8px;
}

.ev-mp-card-actions .btn{
  flex:1 1 0;
  min-width:0;
  height:38px;
  border-radius:999px;
  transition:all .16s ease;
  white-space:nowrap;
  font-size:13px;
  font-weight:800;
}

.ev-mp-card-actions .btn:hover{
  transform:translateY(-2px);
  box-shadow:0 4px 12px rgba(0,0,0,0.12);
}

.ev-mp-card-actions .ev-mp-btn-detalle{
  border:1px solid var(--ev-gris-borde);
  background:#ffffff;
  color:var(--ev-texto-suave);
  font-weight:800;
}

.ev-mp-card-actions .ev-mp-btn-detalle:hover{
  background:#F3F4F6;
  color:var(--ev-texto);
}

.ev-mp-card-actions .ev-mp-btn-pedir{
  border:none;
  background:linear-gradient(135deg, #D97706, #EA7C12);
  color:#ffffff;
  font-weight:900;
  box-shadow:0 10px 24px rgba(217,119,6,0.30);
}

.ev-mp-card-actions .ev-mp-btn-pedir:hover{
  background:linear-gradient(135deg, #C46B05, #D46F0F);
  color:#ffffff;
  box-shadow:0 12px 28px rgba(217,119,6,0.40);
}

.ev-mp-card-actions .ev-mp-btn-pedir:disabled,
.ev-mp-card-actions .ev-mp-btn-pedir[aria-disabled="true"]{
  opacity:.58;
  cursor:not-allowed;
  filter:saturate(.7);
  box-shadow:none;
}

@media (max-width:991.98px){
  .ev-mp-grid{
    grid-template-columns:repeat(auto-fill, minmax(230px, 230px));
  }

  .ev-mp-card{
    width:230px;
    height:420px;
    min-height:420px;
    max-height:420px;
  }
}

@media (max-width:575.98px){
  .ev-mp-content{
    padding-left:12px !important;
    padding-right:12px !important;
  }

  .ev-mp-header .card-body{
    padding:15px 14px 12px 14px;
  }

  .ev-mp-title{
    font-size:24px;
  }

  .ev-mp-condominio{
    width:100%;
    justify-content:flex-start;
  }

  .ev-mp-search-input-wrapper,
  .ev-mp-sort-wrapper,
  .ev-mp-sort-select,
  .ev-mp-cat-producto,
  .ev-mp-cat-select{
    width:100%;
    min-width:0;
  }

  .ev-mp-sort-wrapper,
  .ev-mp-cat-producto,
  .ev-mp-scope{
    align-items:flex-start;
    flex-direction:column;
  }

  .ev-mp-seg{
    width:100%;
  }

  .ev-mp-seg-btn{
    flex:1;
  }

  .ev-mp-grid{
    grid-template-columns:1fr;
    gap:14px;
  }

  .ev-mp-card{
    width:100%;
    height:auto;
    min-height:420px;
    max-height:none;
  }

  .ev-mp-card-media{
    flex-basis:180px;
    height:180px;
    min-height:180px;
    max-height:180px;
  }
}

/* ==========================================================
   MODAL DETALLE / SOLICITUD
========================================================== */
.ev-mp-modal-dialog{
  width:min(980px, calc(100% - 24px));
  max-width:980px !important;
  margin-left:auto;
  margin-right:auto;
}

.ev-mp-modal-content{
  border-radius:18px;
  overflow:hidden;
  border:1px solid var(--ev-gris-borde);
  box-shadow:0 18px 60px rgba(15,23,42,0.25);
}

.ev-mp-modal-header{
  min-height:56px;
  border-bottom:0;
  background:linear-gradient(135deg, #0F592F 0%, #118544 54%, #16A34A 100%);
  color:#ffffff;
  box-shadow:0 10px 28px rgba(15,89,47,.18);
}

.ev-mp-modal-header .modal-title{
  font-weight:900;
  color:#ffffff;
  font-size:1rem;
  letter-spacing:-.01em;
}

.ev-mp-modal-header i{
  color:#ffffff;
}

.ev-mp-modal-header .btn-close{
  filter:brightness(0) invert(1);
  opacity:.92;
  box-shadow:none !important;
}

.ev-mp-modal-header .btn-close:hover{
  opacity:1;
  transform:scale(1.04);
}

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
  max-width:900px;
  margin:0 auto;
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

.ev-mp-modal-footer .btn-ev-neutral,
.ev-mp-modal-footer .btn-ev-primary{
  min-width:150px;
  border-radius:999px;
  font-weight:800;
  min-height:40px;
}

.ev-mp-modal-footer .btn-ev-neutral{
  background:#ffffff;
  border:1px solid var(--ev-gris-borde);
  color:var(--ev-texto-suave);
}

.ev-mp-modal-footer .btn-ev-neutral:hover{
  background:#F3F4F6;
  color:var(--ev-texto);
}

.ev-mp-modal-footer .btn-ev-primary{
  border:none;
  background:linear-gradient(135deg, #D97706, #EA7C12);
  color:#ffffff;
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
  box-shadow:0 28px 70px rgba(15,23,42,.20), 0 10px 24px rgba(15,89,47,.08) !important;
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

.ev-mp-swal-actions-gap .swal2-actions{
  gap:12px !important;
  margin-top:18px !important;
  flex-wrap:wrap !important;
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

@keyframes evMpSpin{ to{ transform:rotate(360deg); } }

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
  box-shadow:inset 0 1px 0 rgba(255,255,255,.9), 0 10px 28px rgba(15,89,47,.08);
}

.ev-mp-swal-status-icon svg{
  width:56px;
  height:56px;
  display:block;
}

.ev-mp-swal-status-icon--success{
  border-color:rgba(22,163,74,.20);
  background:linear-gradient(180deg, rgba(230,244,236,.88), rgba(255,255,255,.98));
}

.ev-mp-swal-status-icon--info{
  border-color:rgba(56,189,248,.22);
  background:linear-gradient(180deg, rgba(240,249,255,.90), rgba(255,255,255,.98));
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

.ev-mp-swal-timer-wrap{ margin-top:16px; }

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

.ev-mp-swal-note strong{ font-weight:900; }

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
  0%{ transform:scale(1) translateX(0); }
  22%{ transform:scale(1.01) translateX(-8px); }
  46%{ transform:scale(1.01) translateX(8px); }
  68%{ transform:scale(1.005) translateX(-4px); }
  100%{ transform:scale(1) translateX(0); }
}

.ev-mp-swal-danger{ color:#B91C1C !important; }
.ev-mp-swal-success{ color:#0F592F !important; }

.ev-mp-swal-toast-compact{
  width:320px !important;
  max-width:320px !important;
  padding:14px 16px !important;
  border-radius:18px !important;
  box-shadow:0 14px 30px rgba(15, 23, 42, .14) !important;
  border-top:3px solid #EA7C12 !important;
}

/* Toast cola premium */
.ev-mp-toast-popup,
.ev-mp-toast-cola-popup{
  width:360px !important;
  min-width:360px !important;
  max-width:360px !important;
  border-radius:18px !important;
  padding:0 !important;
  overflow:hidden !important;
  border:1px solid rgba(15,89,47,.10) !important;
  box-shadow:0 18px 40px rgba(15,23,42,.16) !important;
  background:linear-gradient(90deg, #0F592F 0%, #16A34A 62%, #EA7C12 100%) top/100% 4px no-repeat, #ffffff !important;
}

.ev-mp-toast-html,
.ev-mp-toast-cola-html{
  margin:0 !important;
  padding:0 !important;
}

.ev-mp-toast-cola{
  padding:14px 16px 14px 14px;
  display:flex;
  align-items:flex-start;
  gap:12px;
  text-align:left;
}

.ev-mp-toast-cola-icon{
  flex:0 0 34px;
  width:34px;
  height:34px;
  min-width:34px;
  border-radius:12px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:#ECFDF3;
  border:1px solid rgba(15,89,47,.08);
  color:#0F592F;
  font-size:15px;
  margin-top:2px;
}

.ev-mp-toast-cola-content,
.ev-mp-toast-cola-copy{
  flex:1;
  min-width:0;
}

.ev-mp-toast-cola-title{
  margin:0 0 4px;
  font-size:15px;
  line-height:1.2;
  font-weight:800;
  color:#0F592F;
}

.ev-mp-toast-cola-text{
  margin:0;
  font-size:13px;
  line-height:1.35;
  color:#5B6470;
}

.ev-mp-toast-cola-text + .ev-mp-toast-cola-text{ margin-top:2px; }
.ev-mp-toast-cola-text--strong,
.ev-mp-toast-cola-posicion{
  margin:2px 0 0;
  font-size:13px;
  font-weight:800;
  line-height:1.35;
  color:#0F592F;
}

.ev-mp-toast-cola-actions{ margin-top:8px; }

.ev-mp-toast-cola-link{
  border:0;
  background:transparent;
  padding:0;
  font-size:13px;
  font-weight:800;
  color:#EA7C12;
  cursor:pointer;
  text-decoration:none;
  line-height:1.2;
  transition:color .18s ease, transform .18s ease;
}

.ev-mp-toast-cola-link::before{
  content:'→ ';
  font-size:13px;
  font-weight:900;
  line-height:1;
}

.ev-mp-toast-cola-link:hover,
.ev-mp-toast-cola-link:focus{
  color:#C96A10;
  text-decoration:underline;
  outline:none;
  box-shadow:none;
}

.ev-mp-toast-cola-link:active{ transform:translateY(1px); }

@media(max-width:575.98px){
  .ev-mp-modal-body{
    padding:14px;
  }

  .ev-mp-modal-footer{
    padding:14px;
  }

  .ev-mp-modal-footer .btn-ev-neutral,
  .ev-mp-modal-footer .btn-ev-primary{
    width:100%;
  }

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

  .ev-mp-swal-status-icon svg{
    width:48px;
    height:48px;
  }

  .ev-mp-swal-subtitle{
    font-size:1.02rem;
  }

  .ev-mp-swal-confirm,
  .ev-mp-swal-cancel{
    width:100% !important;
    min-width:0 !important;
  }

  .ev-mp-toast-popup,
  .ev-mp-toast-cola-popup{
    width:min(92vw, 360px) !important;
    min-width:min(92vw, 360px) !important;
    max-width:min(92vw, 360px) !important;
  }

  .ev-mp-toast-cola{
    padding:13px 14px;
  }
}


/* ==========================================================
   EV MARKETPLACE PREMIUM RESPONSIVE 2.0
   Pulido visual: desktop/laptop/tablet/móvil
========================================================== */
.ev-mp-wrapper{
  background:
    radial-gradient(circle at 12% 0%, rgba(230,244,236,.55), transparent 34%),
    radial-gradient(circle at 90% 10%, rgba(255,122,26,.07), transparent 28%),
    var(--ev-gris-fondo) !important;
}

.ev-mp-content{
  padding-top:14px !important;
}

.ev-mp-header{
  overflow:hidden;
  border-color:rgba(15,89,47,.10) !important;
  background:
    linear-gradient(120deg, rgba(255,255,255,.98) 0%, rgba(255,255,255,.96) 46%, rgba(230,244,236,.82) 100%) !important;
  box-shadow:0 18px 48px rgba(15,23,42,.075) !important;
}

.ev-mp-header::before{
  content:'';
  display:block;
  height:4px;
  background:linear-gradient(90deg, #0F592F 0%, #198754 58%, #FF7A1A 100%);
}

.ev-mp-header .card-body{
  padding:18px 24px 16px 24px !important;
}

.ev-mp-hero-row{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:18px;
}

.ev-mp-title-zone{
  display:flex;
  align-items:flex-start;
  gap:14px;
  min-width:0;
}

.ev-mp-title-icon{
  flex:0 0 44px;
  width:44px;
  height:44px;
  border-radius:16px;
  display:flex;
  align-items:center;
  justify-content:center;
  color:var(--ev-verde-oscuro);
  background:linear-gradient(180deg, #E6F4EC 0%, #FFFFFF 100%);
  border:1px solid rgba(15,89,47,.10);
  box-shadow:0 12px 24px rgba(15,89,47,.08);
  font-size:20px;
}

.ev-mp-kicker{
  font-size:11px;
  font-weight:900;
  letter-spacing:.10em;
  text-transform:uppercase;
  color:#7A8A9A;
  margin-bottom:2px;
}

.ev-mp-title{
  font-size:clamp(25px, 2vw, 32px) !important;
  line-height:1.05;
  letter-spacing:-.035em !important;
}

.ev-mp-subtitle{
  max-width:760px;
  line-height:1.45;
}

.ev-mp-condominio{
  flex:0 0 auto;
  border:1px solid rgba(15,89,47,.08);
  background:linear-gradient(135deg, rgba(230,244,236,.98), rgba(240,253,244,.92)) !important;
  box-shadow:0 10px 22px rgba(15,89,47,.06);
}

.ev-mp-condominio-icon{
  box-shadow:inset 0 1px 0 rgba(255,255,255,.9);
}

.ev-mp-toolbar{
  display:grid;
  grid-template-columns:minmax(280px, 1fr) minmax(380px, 560px);
  gap:14px;
  align-items:center;
}

.ev-mp-toolbar-controls{
  display:grid;
  grid-template-columns: minmax(160px, 210px) minmax(210px, 1fr);
  gap:12px;
  align-items:center;
}

.ev-mp-search-row{
  display:none !important;
}

.ev-mp-search-input-wrapper{
  height:48px !important;
  border-color:rgba(15,89,47,.12) !important;
  box-shadow:0 8px 18px rgba(15,23,42,.04);
  transition:border-color .16s ease, box-shadow .16s ease, transform .16s ease;
}

.ev-mp-search-input-wrapper:focus-within{
  border-color:rgba(25,135,84,.55) !important;
  box-shadow:0 12px 26px rgba(15,89,47,.10);
  transform:translateY(-1px);
}

.ev-mp-sort-wrapper,
.ev-mp-cat-producto{
  display:flex;
  align-items:center;
  gap:8px;
}

.ev-mp-sort-label,
.ev-mp-scope-label{
  font-weight:700;
  color:#5E6A78 !important;
}

.ev-mp-sort-select,
.ev-mp-cat-select{
  height:44px !important;
  min-width:0 !important;
  width:100%;
  border-radius:14px !important;
  border-color:rgba(15,89,47,.12) !important;
  box-shadow:0 8px 18px rgba(15,23,42,.035);
}

.ev-mp-filters-advanced{
  margin-top:14px !important;
  padding-top:2px;
}

.ev-mp-seg{
  border-color:rgba(15,89,47,.10) !important;
  box-shadow:0 10px 24px rgba(15,23,42,.055) !important;
}

.ev-mp-seg-btn{
  min-height:34px;
  font-weight:800 !important;
}

.ev-mp-seg-btn.active{
  background:linear-gradient(135deg, #0F592F, #198754) !important;
  box-shadow:0 8px 16px rgba(15,89,47,.18);
}

.ev-mp-resumen{
  margin-top:0 !important;
  display:inline-flex;
  align-items:center;
  gap:8px;
  min-height:34px;
  padding:7px 12px;
  border-radius:999px;
  background:rgba(255,255,255,.74);
  border:1px solid rgba(15,89,47,.08);
  color:#526071 !important;
  font-weight:700;
  box-shadow:0 8px 18px rgba(15,23,42,.04);
}

.ev-mp-resumen::before{
  content:'';
  width:8px;
  height:8px;
  border-radius:999px;
  background:#22C55E;
  box-shadow:0 0 0 4px rgba(34,197,94,.12);
}

.ev-mp-global-empty{
  display:none;
  margin:18px 0;
  padding:22px;
  border-radius:22px;
  background:#fff;
  border:1px solid rgba(229,231,235,.95);
  box-shadow:0 12px 34px rgba(15,23,42,.055);
  text-align:center;
  color:var(--ev-texto-suave);
  font-weight:700;
}

.ev-mp-split{
  gap:20px !important;
}

.ev-mp-section{
  padding:18px;
  background:rgba(255,255,255,.70) !important;
  border:1px solid rgba(229,231,235,.86);
  border-radius:24px !important;
  box-shadow:0 16px 42px rgba(15,23,42,.055);
  backdrop-filter:blur(4px);
}

.ev-mp-section-head{
  align-items:center !important;
  padding:0 0 14px 0 !important;
  border-bottom:1px solid rgba(229,231,235,.72);
  margin-bottom:14px;
}

.ev-mp-section-title-wrap{
  display:flex;
  align-items:center;
  gap:12px;
  min-width:0;
}

.ev-mp-section-icon{
  flex:0 0 42px;
  width:42px;
  height:42px;
  border-radius:16px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:19px;
  border:1px solid rgba(15,89,47,.10);
  box-shadow:0 10px 22px rgba(15,89,47,.06);
}

.ev-mp-section-icon-serv{
  color:#0284C7;
  background:linear-gradient(180deg, #E0F2FE, #FFFFFF);
}

.ev-mp-section-icon-prod{
  color:var(--ev-verde-oscuro);
  background:linear-gradient(180deg, #E6F4EC, #FFFFFF);
}

.ev-mp-section-kicker{
  font-weight:900 !important;
}

.ev-mp-section-title{
  font-size:19px !important;
  letter-spacing:-.02em;
}

.ev-mp-section-sub{
  line-height:1.35;
}

.ev-mp-section-pill{
  min-width:unset !important;
  height:36px !important;
  padding:0 12px !important;
  gap:7px;
  border-color:rgba(15,89,47,.10) !important;
  color:var(--ev-verde-oscuro) !important;
  box-shadow:0 10px 22px rgba(15,23,42,.055) !important;
}

.ev-mp-section-pill span{
  font-size:15px;
  font-weight:900;
}

.ev-mp-section-pill small{
  font-size:12px;
  color:#64748B;
  font-weight:800;
}

.ev-mp-section-empty{
  display:none;
  align-items:center;
  justify-content:center;
  gap:10px;
  min-height:64px;
  padding:12px 16px;
  border-radius:20px;
  border:1px dashed rgba(15,89,47,.18);
  background:linear-gradient(180deg, rgba(255,255,255,.86), rgba(248,250,252,.88));
  color:#64748B;
  font-weight:700;
}

.ev-mp-section-empty i{
  color:var(--ev-verde-oscuro);
  font-size:18px;
}

.ev-mp-grid{
  grid-template-columns:repeat(auto-fill, minmax(280px, 280px)) !important;
  gap:20px !important;
}

.ev-mp-card{
  width:280px !important;
  height:486px !important;
  min-height:486px !important;
  max-height:486px !important;
  border-radius:22px !important;
  border-color:rgba(15,89,47,.10) !important;
  box-shadow:0 14px 34px rgba(15,23,42,.09) !important;
}

.ev-mp-card:hover{
  transform:translateY(-5px) !important;
  box-shadow:0 22px 48px rgba(15,23,42,.14) !important;
}

.ev-mp-card-top-status{
  height:40px !important;
  min-height:40px !important;
  max-height:40px !important;
  flex-basis:40px !important;
}

.ev-mp-card-top-status-text{
  font-size:13px !important;
}

.ev-mp-card-media{
  height:184px !important;
  min-height:184px !important;
  max-height:184px !important;
  flex-basis:184px !important;
  background:
    linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%) !important;
}

.ev-mp-card-media::after{
  content:'';
  position:absolute;
  left:0;
  right:0;
  bottom:0;
  height:1px;
  background:rgba(229,231,235,.86);
}

.ev-mp-badge{
  box-shadow:0 8px 16px rgba(15,23,42,.12);
  font-size:11px !important;
}

.ev-mp-card-body{
  padding:14px 15px 14px 15px !important;
  gap:7px !important;
}

.ev-mp-card-meta{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  min-height:20px;
  color:#64748B;
  font-size:11.5px;
  font-weight:800;
  text-transform:uppercase;
  letter-spacing:.04em;
}

.ev-mp-card-meta span{
  min-width:0;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}

.ev-mp-card-title{
  min-height:40px !important;
  max-height:40px !important;
  font-size:14.5px !important;
  line-height:1.34 !important;
}

.ev-mp-card-price{
  font-size:18px !important;
  color:#00875A !important;
}

.ev-mp-card-desc{
  font-size:12.5px !important;
  color:var(--ev-texto-suave) !important;
  min-height:36px !important;
  max-height:36px !important;
  line-height:1.42 !important;
  margin:0 !important;
  overflow:hidden !important;
  display:-webkit-box !important;
  -webkit-line-clamp:2 !important;
  -webkit-box-orient:vertical !important;
}

.ev-mp-card-actions{
  gap:10px !important;
}

.ev-mp-card-actions .btn{
  height:40px !important;
  font-size:13px !important;
}

.ev-mp-card-actions .ev-mp-btn-detalle{
  color:#526071 !important;
  border-color:rgba(15,23,42,.11) !important;
}

.ev-mp-card-actions .ev-mp-btn-pedir:disabled,
.ev-mp-card-actions .ev-mp-btn-pedir[aria-disabled="true"]{
  background:#EEF2F7 !important;
  color:#94A3B8 !important;
  opacity:1 !important;
}

@media (min-width:1600px){
  .ev-mp-grid{
    grid-template-columns:repeat(auto-fill, minmax(292px, 292px)) !important;
  }

  .ev-mp-card{
    width:292px !important;
  }
}

@media (max-width:1199.98px){
  .ev-mp-toolbar{
    grid-template-columns:1fr;
  }

  .ev-mp-toolbar-controls{
    grid-template-columns:1fr 1fr;
  }
}

@media (max-width:991.98px){
  .ev-mp-content{
    padding-left:18px !important;
    padding-right:18px !important;
  }

  .ev-mp-hero-row{
    flex-direction:column;
  }

  .ev-mp-condominio{
    width:100%;
  }

  .ev-mp-grid{
    grid-template-columns:repeat(auto-fill, minmax(245px, 1fr)) !important;
  }

  .ev-mp-card{
    width:100% !important;
    height:492px !important;
    min-height:492px !important;
    max-height:492px !important;
  }
}

@media (max-width:767.98px){
  .ev-mp-header .card-body{
    padding:16px 14px 14px 14px !important;
  }

  .ev-mp-title-zone{
    gap:10px;
  }

  .ev-mp-title-icon{
    width:40px;
    height:40px;
    flex-basis:40px;
    border-radius:14px;
  }

  .ev-mp-toolbar-controls{
    grid-template-columns:1fr;
  }

  .ev-mp-sort-wrapper,
  .ev-mp-cat-producto,
  .ev-mp-scope{
    width:100%;
    flex-direction:column;
    align-items:stretch !important;
    gap:7px;
  }

  .ev-mp-seg{
    width:100%;
  }

  .ev-mp-seg-btn{
    flex:1;
    padding-left:8px !important;
    padding-right:8px !important;
  }

  .ev-mp-resumen{
    width:100%;
    justify-content:center;
    text-align:center;
    white-space:normal;
  }

  .ev-mp-section{
    padding:14px;
    border-radius:22px !important;
  }

  .ev-mp-section-head{
    align-items:flex-start !important;
    flex-direction:column;
  }

  .ev-mp-section-pill{
    align-self:flex-start;
  }
}

@media (max-width:575.98px){
  .ev-mp-content{
    padding-left:12px !important;
    padding-right:12px !important;
    padding-top:10px !important;
  }

  .ev-mp-title{
    font-size:25px !important;
  }

  .ev-mp-subtitle{
    font-size:13px !important;
  }

  .ev-mp-condominio{
    border-radius:18px !important;
  }

  .ev-mp-grid{
    grid-template-columns:1fr !important;
    gap:14px !important;
  }

  .ev-mp-card{
    width:100% !important;
    height:auto !important;
    min-height:466px !important;
    max-height:none !important;
  }

  .ev-mp-card-media{
    height:190px !important;
    min-height:190px !important;
    max-height:190px !important;
    flex-basis:190px !important;
  }

  .ev-mp-card-actions .btn{
    height:42px !important;
  }
}

/* ==========================================================
   EV MARKETPLACE — Ajuste final ultra fino
   - Evita ruido visual en cards.
   - Compacta empty states.
   - Mantiene el modal usable en laptop y móvil.
========================================================== */
.ev-mp-card-meta{
  display:none !important;
}

.ev-mp-section[style*="display: none"]{
  margin:0 !important;
}

.ev-mp-modal-content{
  max-height:calc(100vh - 32px);
  display:flex;
  flex-direction:column;
}

.ev-mp-modal-body{
  overflow:auto;
}

.ev-mp-modal-dialog{
  width:min(920px, calc(100% - 24px));
  max-width:920px !important;
}

.ev-mp-modal-media{
  height:min(48vh, 420px);
  max-height:420px;
  padding:10px;
  margin-bottom:12px;
}

.ev-mp-modal-media img{
  max-height:390px !important;
}

.ev-mp-modal-thumbs{
  margin:12px 0 14px;
}

.ev-mp-modal-title{
  font-size:19px;
  line-height:1.2;
}

.ev-mp-modal-price{
  margin-bottom:8px;
}

@media (max-width:575.98px){
  .ev-mp-modal-dialog{
    width:calc(100% - 18px);
  }

  .ev-mp-modal-media{
    height:min(42vh, 320px);
    max-height:320px;
  }

  .ev-mp-modal-media img{
    max-height:292px !important;
  }
}

/* ==========================================================
   EV MARKETPLACE — CIERRE UX/UI 100% PREMIUM
   Dropdowns custom, modal sin línea blanca, skeleton y responsive fino.
========================================================== */
.ev-mp-field{
  display:flex !important;
  align-items:center !important;
  gap:8px !important;
  min-width:0 !important;
}

.ev-mp-native-select{
  position:absolute !important;
  width:1px !important;
  height:1px !important;
  min-width:1px !important;
  padding:0 !important;
  margin:-1px !important;
  overflow:hidden !important;
  clip:rect(0,0,0,0) !important;
  white-space:nowrap !important;
  border:0 !important;
  opacity:0 !important;
  pointer-events:none !important;
}

.ev-mp-select{
  position:relative !important;
  min-width:190px !important;
  max-width:100% !important;
  z-index:20 !important;
}

.ev-mp-select-category{
  min-width:260px !important;
}

.ev-mp-select-trigger{
  width:100% !important;
  min-height:46px !important;
  border:1px solid rgba(15,89,47,.12) !important;
  border-radius:16px !important;
  background:rgba(255,255,255,.96) !important;
  color:var(--ev-texto) !important;
  display:flex !important;
  align-items:center !important;
  justify-content:space-between !important;
  gap:12px !important;
  padding:0 14px !important;
  font-size:13px !important;
  font-weight:700 !important;
  line-height:1.2 !important;
  box-shadow:0 10px 26px rgba(15,23,42,.06) !important;
  transition:border-color .16s ease, box-shadow .16s ease, background .16s ease, transform .16s ease !important;
  cursor:pointer !important;
  text-align:left !important;
}

.ev-mp-select-trigger:hover{
  border-color:rgba(15,89,47,.24) !important;
  background:#fff !important;
  box-shadow:0 14px 30px rgba(15,23,42,.08) !important;
}

.ev-mp-select.open .ev-mp-select-trigger,
.ev-mp-select-trigger:focus-visible{
  outline:none !important;
  border-color:rgba(15,89,47,.78) !important;
  box-shadow:0 0 0 4px rgba(15,89,47,.10), 0 16px 34px rgba(15,23,42,.10) !important;
}

.ev-mp-select-value{
  flex:1 1 auto !important;
  min-width:0 !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
  white-space:nowrap !important;
}

.ev-mp-select-trigger i{
  flex:0 0 auto !important;
  color:#0F592F !important;
  font-size:14px !important;
  transition:transform .16s ease !important;
}

.ev-mp-select.open .ev-mp-select-trigger i{
  transform:rotate(180deg) !important;
}

.ev-mp-select-menu{
  position:absolute !important;
  top:calc(100% + 8px) !important;
  left:0 !important;
  right:0 !important;
  min-width:100% !important;
  max-height:280px !important;
  overflow:auto !important;
  display:none !important;
  padding:8px !important;
  border-radius:18px !important;
  background:#ffffff !important;
  border:1px solid rgba(15,89,47,.14) !important;
  box-shadow:0 24px 56px rgba(15,23,42,.18), 0 8px 18px rgba(15,89,47,.08) !important;
  z-index:9999 !important;
}

.ev-mp-select.open .ev-mp-select-menu{
  display:block !important;
  animation:evMpSelectIn .14s ease-out !important;
}

@keyframes evMpSelectIn{
  from{ opacity:0; transform:translateY(-4px) scale(.98); }
  to{ opacity:1; transform:translateY(0) scale(1); }
}

.ev-mp-select-group{
  padding:8px 10px 5px !important;
  font-size:10px !important;
  line-height:1 !important;
  font-weight:900 !important;
  text-transform:uppercase !important;
  letter-spacing:.08em !important;
  color:#94A3B8 !important;
}

.ev-mp-select-option{
  width:100% !important;
  min-height:40px !important;
  border:0 !important;
  background:transparent !important;
  color:#1A1F36 !important;
  display:flex !important;
  align-items:center !important;
  gap:9px !important;
  padding:9px 10px !important;
  border-radius:13px !important;
  font-size:13px !important;
  font-weight:700 !important;
  text-align:left !important;
  cursor:pointer !important;
  transition:background .14s ease, color .14s ease, transform .14s ease !important;
}

.ev-mp-select-option:hover,
.ev-mp-select-option:focus-visible{
  outline:none !important;
  background:#F0FDF4 !important;
  color:#0F592F !important;
}

.ev-mp-select-option.is-active{
  background:linear-gradient(135deg, #E6F4EC, #F0FDF4) !important;
  color:#0F592F !important;
  font-weight:900 !important;
}

.ev-mp-select-check{
  flex:0 0 18px !important;
  width:18px !important;
  height:18px !important;
  border-radius:999px !important;
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  font-size:12px !important;
  color:#0F592F !important;
  background:rgba(15,89,47,.10) !important;
  opacity:0 !important;
}

.ev-mp-select-option.is-active .ev-mp-select-check{
  opacity:1 !important;
}

.ev-mp-loading-grid{
  display:grid !important;
  grid-template-columns:repeat(auto-fill, minmax(276px, 276px)) !important;
  gap:20px !important;
  width:100% !important;
}

.ev-mp-skeleton-card{
  width:276px !important;
  height:438px !important;
  border-radius:22px !important;
  background:#fff !important;
  border:1px solid rgba(229,231,235,.88) !important;
  box-shadow:0 14px 34px rgba(15,23,42,.08) !important;
  overflow:hidden !important;
}

.ev-mp-skeleton-line,
.ev-mp-skeleton-img{
  background:linear-gradient(90deg, #F1F5F9 0%, #FFFFFF 48%, #F1F5F9 100%) !important;
  background-size:220% 100% !important;
  animation:evMpSkeleton 1.05s ease-in-out infinite !important;
}

.ev-mp-skeleton-img{
  height:210px !important;
}

.ev-mp-skeleton-body{
  padding:16px !important;
}

.ev-mp-skeleton-line{
  height:14px !important;
  border-radius:999px !important;
  margin-bottom:12px !important;
}

.ev-mp-skeleton-line.w70{ width:70% !important; }
.ev-mp-skeleton-line.w45{ width:45% !important; }
.ev-mp-skeleton-line.w90{ width:90% !important; }

@keyframes evMpSkeleton{
  0%{ background-position:120% 0; }
  100%{ background-position:-120% 0; }
}

.ev-mp-modal-content{
  border:none !important;
  border-radius:20px !important;
  overflow:hidden !important;
  background:#fff !important;
  box-shadow:0 28px 78px rgba(15,23,42,.32) !important;
}

.ev-mp-modal-header{
  min-height:58px !important;
  margin:0 !important;
  padding:14px 18px !important;
  border:0 !important;
  border-bottom:none !important;
  border-radius:0 !important;
  background:linear-gradient(90deg, #0B4B28 0%, #0F592F 34%, #118544 70%, #16A34A 100%) !important;
  color:#fff !important;
  box-shadow:none !important;
}

.ev-mp-modal-header::before,
.ev-mp-modal-header::after{
  content:none !important;
  display:none !important;
}

.ev-mp-modal-header .modal-title,
.ev-mp-modal-header .modal-title span,
.ev-mp-modal-header i{
  color:#fff !important;
}

.ev-mp-modal-header .btn-close{
  filter:brightness(0) invert(1) !important;
  opacity:.95 !important;
  box-shadow:none !important;
  background-color:transparent !important;
}

.ev-mp-modal-header .btn-close:hover{
  opacity:1 !important;
  transform:scale(1.05) !important;
}

.ev-mp-modal-body{
  border-top:0 !important;
}

.ev-mp-card-actions .ev-mp-btn-pedir:disabled,
.ev-mp-card-actions .ev-mp-btn-pedir[aria-disabled="true"]{
  background:#E5E7EB !important;
  color:#6B7280 !important;
  box-shadow:none !important;
  opacity:1 !important;
}

@media (max-width:991.98px){
  .ev-mp-loading-grid{
    grid-template-columns:repeat(auto-fill, minmax(238px, 238px)) !important;
  }

  .ev-mp-skeleton-card{
    width:238px !important;
    height:470px !important;
  }
}

@media (max-width:575.98px){
  .ev-mp-toolbar-controls{
    width:100% !important;
  }

  .ev-mp-field,
  .ev-mp-sort-wrapper,
  .ev-mp-cat-producto{
    width:100% !important;
    align-items:stretch !important;
    flex-direction:column !important;
    gap:7px !important;
  }

  .ev-mp-select,
  .ev-mp-select-category{
    width:100% !important;
    min-width:0 !important;
  }

  .ev-mp-select-trigger{
    min-height:44px !important;
    border-radius:15px !important;
  }

  .ev-mp-select-menu{
    left:0 !important;
    right:0 !important;
    width:100% !important;
    max-width:100% !important;
    max-height:240px !important;
    border-radius:16px !important;
  }

  .ev-mp-select-option{
    min-height:42px !important;
    font-size:13px !important;
  }

  .ev-mp-loading-grid{
    grid-template-columns:1fr !important;
  }

  .ev-mp-skeleton-card{
    width:100% !important;
    height:438px !important;
  }
}



/* ==========================================================
   EV MARKETPLACE — CIERRE DEFINITIVO UX/UI
   Objetivo:
   - Dropdowns premium sin cortes en desktop/tablet/móvil.
   - Header de modal 100% verde degradado, sin línea blanca.
   - Menús de filtros por encima de tarjetas/secciones.
========================================================== */
.ev-mp-header{
  overflow:visible !important;
  position:relative !important;
  z-index:60 !important;
}

.ev-mp-header .card-body,
.ev-mp-toolbar,
.ev-mp-toolbar-controls,
.ev-mp-field{
  overflow:visible !important;
}

.ev-mp-split,
.ev-mp-section{
  position:relative !important;
  z-index:1 !important;
}

.ev-mp-select{
  position:relative !important;
  z-index:100 !important;
}

.ev-mp-select.open{
  z-index:99999 !important;
}

.ev-mp-select-trigger{
  min-height:46px !important;
  border:1px solid rgba(15,89,47,.16) !important;
  border-radius:18px !important;
  background:
    linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.92)) !important;
  box-shadow:
    0 1px 0 rgba(255,255,255,.92) inset,
    0 10px 24px rgba(15,23,42,.055) !important;
  color:#1A1F36 !important;
  font-weight:850 !important;
  letter-spacing:-.01em !important;
}

.ev-mp-select.open .ev-mp-select-trigger,
.ev-mp-select-trigger:focus-visible{
  border-color:rgba(15,89,47,.92) !important;
  background:#fff !important;
  box-shadow:
    0 0 0 4px rgba(15,89,47,.11),
    0 18px 38px rgba(15,23,42,.12) !important;
}

.ev-mp-select-menu{
  top:calc(100% + 10px) !important;
  left:0 !important;
  right:auto !important;
  width:100% !important;
  min-width:100% !important;
  max-width:min(420px, calc(100vw - 32px)) !important;
  max-height:min(310px, 52vh) !important;
  overflow-y:auto !important;
  overflow-x:hidden !important;
  padding:8px !important;
  border-radius:20px !important;
  background:#ffffff !important;
  border:1px solid rgba(15,89,47,.14) !important;
  box-shadow:
    0 26px 70px rgba(15,23,42,.22),
    0 10px 26px rgba(15,89,47,.10),
    0 1px 0 rgba(255,255,255,.85) inset !important;
  z-index:100000 !important;
}

.ev-mp-select-category .ev-mp-select-menu{
  width:max(100%, 320px) !important;
}

.ev-mp-select-menu::-webkit-scrollbar{
  width:8px !important;
}

.ev-mp-select-menu::-webkit-scrollbar-thumb{
  background:rgba(15,89,47,.22) !important;
  border-radius:999px !important;
  border:2px solid #fff !important;
}

.ev-mp-select-option{
  min-height:42px !important;
  padding:10px 11px !important;
  border-radius:14px !important;
  gap:10px !important;
  font-size:13px !important;
  font-weight:800 !important;
  line-height:1.2 !important;
  color:#1A1F36 !important;
}

.ev-mp-select-option-text{
  flex:1 1 auto !important;
  min-width:0 !important;
  white-space:normal !important;
  overflow:visible !important;
  text-overflow:clip !important;
}

.ev-mp-select-option:hover,
.ev-mp-select-option:focus-visible{
  background:linear-gradient(135deg, #F0FDF4, #ECFDF3) !important;
  color:#0F592F !important;
}

.ev-mp-select-option.is-active{
  background:linear-gradient(135deg, #E6F4EC, #F0FDF4) !important;
  color:#0F592F !important;
  box-shadow:0 8px 18px rgba(15,89,47,.07) !important;
}

.ev-mp-select-check{
  flex:0 0 20px !important;
  width:20px !important;
  height:20px !important;
  background:rgba(15,89,47,.10) !important;
}

.ev-mp-select-option.is-active .ev-mp-select-check{
  opacity:1 !important;
  background:#DFF5E7 !important;
}

.ev-mp-select-group{
  padding:10px 12px 6px !important;
  color:#64748B !important;
  font-size:10px !important;
  font-weight:950 !important;
  letter-spacing:.09em !important;
}

.ev-mp-field{
  position:relative !important;
}

.ev-mp-field .ev-mp-scope-label,
.ev-mp-field .ev-mp-sort-label{
  white-space:nowrap !important;
  font-weight:800 !important;
  color:#64748B !important;
}

/* Modal detalle: eliminar cualquier borde/línea blanca superior */
#mp_modal_detalle .modal-dialog{
  border:0 !important;
}

#mp_modal_detalle .ev-mp-modal-content{
  position:relative !important;
  border:0 !important;
  outline:0 !important;
  border-radius:22px !important;
  overflow:hidden !important;
  background:#fff !important;
  box-shadow:0 30px 82px rgba(15,23,42,.34) !important;
}

#mp_modal_detalle .ev-mp-modal-content::before{
  content:'' !important;
  position:absolute !important;
  top:-2px !important;
  left:-2px !important;
  right:-2px !important;
  height:66px !important;
  background:linear-gradient(90deg, #0B4B28 0%, #0F6A38 48%, #16A34A 100%) !important;
  z-index:0 !important;
  pointer-events:none !important;
}

#mp_modal_detalle .ev-mp-modal-header{
  position:relative !important;
  z-index:2 !important;
  min-height:64px !important;
  margin:0 !important;
  padding:16px 20px !important;
  border:0 !important;
  border-bottom:0 !important;
  border-radius:22px 22px 0 0 !important;
  background:linear-gradient(90deg, #0B4B28 0%, #0F6A38 48%, #16A34A 100%) !important;
  color:#fff !important;
  box-shadow:none !important;
}

#mp_modal_detalle .ev-mp-modal-header .modal-title,
#mp_modal_detalle .ev-mp-modal-header .modal-title span,
#mp_modal_detalle .ev-mp-modal-header i{
  color:#fff !important;
  text-shadow:0 1px 0 rgba(0,0,0,.08) !important;
}

#mp_modal_detalle .ev-mp-modal-header .btn-close{
  filter:none !important;
  opacity:1 !important;
  width:36px !important;
  height:36px !important;
  border-radius:999px !important;
  background:rgba(255,255,255,.14) !important;
  position:relative !important;
  transition:background .16s ease, transform .16s ease !important;
}

#mp_modal_detalle .ev-mp-modal-header .btn-close::before,
#mp_modal_detalle .ev-mp-modal-header .btn-close::after{
  content:'' !important;
  position:absolute !important;
  left:50% !important;
  top:50% !important;
  width:18px !important;
  height:2px !important;
  border-radius:999px !important;
  background:#fff !important;
  transform-origin:center !important;
}

#mp_modal_detalle .ev-mp-modal-header .btn-close::before{
  transform:translate(-50%, -50%) rotate(45deg) !important;
}

#mp_modal_detalle .ev-mp-modal-header .btn-close::after{
  transform:translate(-50%, -50%) rotate(-45deg) !important;
}

#mp_modal_detalle .ev-mp-modal-header .btn-close:hover{
  background:rgba(255,255,255,.22) !important;
  transform:scale(1.04) !important;
}

#mp_modal_detalle .ev-mp-modal-body{
  position:relative !important;
  z-index:1 !important;
  border-top:0 !important;
}

@media (max-width:991.98px){
  .ev-mp-toolbar-controls{
    overflow:visible !important;
  }

  .ev-mp-select,
  .ev-mp-select-category{
    width:100% !important;
    min-width:0 !important;
  }

  .ev-mp-select-category .ev-mp-select-menu,
  .ev-mp-select-menu{
    width:100% !important;
    min-width:100% !important;
    max-width:100% !important;
  }
}

@media (max-width:575.98px){
  .ev-mp-header{
    overflow:visible !important;
  }

  .ev-mp-select-menu{
    max-height:42vh !important;
    border-radius:18px !important;
  }

  .ev-mp-select-option{
    min-height:44px !important;
    font-size:13px !important;
  }

  #mp_modal_detalle .ev-mp-modal-content{
    border-radius:20px !important;
  }

  #mp_modal_detalle .ev-mp-modal-header{
    border-radius:20px 20px 0 0 !important;
  }
}



/* ==========================================================
   EV MARKETPLACE — CORRECCIÓN FINAL REAL DE FILTROS + MODAL
   Objetivo:
   - Evitar que la palabra Categoría se tape.
   - Reducir el buscador solo cuando sea necesario.
   - Hacer dropdowns compactos, premium y sin desbordes.
   - Eliminar línea blanca del header del modal Detalle.
========================================================== */

/* El header debe permitir que los menús salgan sin recortarse */
.ev-mp-header,
.ev-mp-header .card-body,
.ev-mp-toolbar,
.ev-mp-toolbar-controls,
.ev-mp-field{
  overflow:visible !important;
}

.ev-mp-header{
  position:relative !important;
  z-index:3000 !important;
}

.ev-mp-split,
.ev-mp-section{
  position:relative !important;
  z-index:1 !important;
}

/* Desktop/laptop: buscador más corto y controles con espacio fijo suficiente */
.ev-mp-toolbar{
  display:grid !important;
  grid-template-columns:minmax(360px, 1fr) minmax(640px, 680px) !important;
  gap:16px !important;
  align-items:center !important;
}

.ev-mp-toolbar-controls{
  display:grid !important;
  grid-template-columns:250px 1fr !important;
  gap:14px !important;
  align-items:center !important;
  min-width:0 !important;
  width:100% !important;
}

.ev-mp-field{
  display:grid !important;
  align-items:center !important;
  column-gap:8px !important;
  min-width:0 !important;
}

.ev-mp-sort-wrapper{
  grid-template-columns:auto minmax(0, 1fr) !important;
  width:250px !important;
}

.ev-mp-cat-producto{
  grid-template-columns:auto minmax(0, 1fr) !important;
  width:100% !important;
}

.ev-mp-field .ev-mp-sort-label,
.ev-mp-field .ev-mp-scope-label{
  display:block !important;
  min-width:max-content !important;
  max-width:max-content !important;
  white-space:nowrap !important;
  overflow:visible !important;
  color:#64748B !important;
  font-size:13px !important;
  line-height:1.1 !important;
  font-weight:850 !important;
  letter-spacing:-.01em !important;
  margin:0 !important;
}

.ev-mp-select,
.ev-mp-select-category{
  width:100% !important;
  min-width:0 !important;
  max-width:100% !important;
  position:relative !important;
  z-index:3200 !important;
}

.ev-mp-select.open{
  z-index:999999 !important;
}

.ev-mp-select-trigger{
  width:100% !important;
  min-width:0 !important;
  min-height:44px !important;
  height:44px !important;
  border-radius:16px !important;
  padding:0 13px !important;
  background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.94)) !important;
  border:1px solid rgba(15,89,47,.16) !important;
  box-shadow:0 1px 0 rgba(255,255,255,.9) inset, 0 10px 22px rgba(15,23,42,.055) !important;
}

.ev-mp-select-value{
  min-width:0 !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
  white-space:nowrap !important;
  display:block !important;
}

.ev-mp-select.open .ev-mp-select-trigger,
.ev-mp-select-trigger:focus-visible{
  border-color:rgba(15,89,47,.85) !important;
  box-shadow:0 0 0 4px rgba(15,89,47,.10), 0 18px 38px rgba(15,23,42,.12) !important;
}

/* Dropdown premium: no se corta, no invade raro y mantiene lectura */
.ev-mp-select-menu{
  position:absolute !important;
  top:calc(100% + 8px) !important;
  left:0 !important;
  right:auto !important;
  width:100% !important;
  min-width:100% !important;
  max-width:100% !important;
  max-height:min(300px, 48vh) !important;
  overflow-y:auto !important;
  overflow-x:hidden !important;
  padding:8px !important;
  border-radius:18px !important;
  background:#fff !important;
  border:1px solid rgba(15,89,47,.14) !important;
  box-shadow:0 28px 70px rgba(15,23,42,.22), 0 10px 24px rgba(15,89,47,.10) !important;
  z-index:1000000 !important;
}

.ev-mp-select-category .ev-mp-select-menu{
  width:100% !important;
  min-width:100% !important;
  max-width:100% !important;
}

.ev-mp-select-option{
  width:100% !important;
  min-height:42px !important;
  padding:10px 11px !important;
  border-radius:14px !important;
  display:flex !important;
  align-items:center !important;
  gap:10px !important;
  font-size:13px !important;
  font-weight:800 !important;
  line-height:1.22 !important;
  color:#172033 !important;
}

.ev-mp-select-option-text{
  min-width:0 !important;
  flex:1 1 auto !important;
  overflow:hidden !important;
  text-overflow:ellipsis !important;
  white-space:nowrap !important;
}

.ev-mp-select-option:hover,
.ev-mp-select-option:focus-visible{
  background:linear-gradient(135deg, #F0FDF4, #ECFDF3) !important;
  color:#0F592F !important;
}

.ev-mp-select-option.is-active{
  background:linear-gradient(135deg, #E6F4EC, #F0FDF4) !important;
  color:#0F592F !important;
  box-shadow:0 8px 18px rgba(15,89,47,.07) !important;
}

/* Responsivo: cuando el ancho ya no alcanza, no forzar una sola fila */
@media (max-width:1399.98px){
  .ev-mp-toolbar{
    grid-template-columns:1fr !important;
    gap:12px !important;
  }

  .ev-mp-toolbar-controls{
    grid-template-columns:minmax(230px, 260px) minmax(280px, 1fr) !important;
    justify-content:end !important;
  }
}

@media (max-width:991.98px){
  .ev-mp-toolbar-controls{
    grid-template-columns:1fr 1fr !important;
    justify-content:stretch !important;
  }

  .ev-mp-sort-wrapper,
  .ev-mp-cat-producto{
    width:100% !important;
  }
}

@media (max-width:575.98px){
  .ev-mp-toolbar{
    grid-template-columns:1fr !important;
  }

  .ev-mp-toolbar-controls{
    grid-template-columns:1fr !important;
    gap:12px !important;
  }

  .ev-mp-field{
    grid-template-columns:1fr !important;
    row-gap:7px !important;
  }

  .ev-mp-field .ev-mp-sort-label,
  .ev-mp-field .ev-mp-scope-label{
    max-width:100% !important;
  }

  .ev-mp-select-trigger{
    height:46px !important;
    min-height:46px !important;
  }

  .ev-mp-select-menu{
    max-height:42vh !important;
  }

  .ev-mp-select-option-text{
    white-space:normal !important;
    overflow:visible !important;
    text-overflow:clip !important;
  }
}

/* Modal Detalle: cubrir cualquier línea blanca superior/inferior del header */
#mp_modal_detalle .modal-dialog{
  border:0 !important;
  outline:0 !important;
}

#mp_modal_detalle .ev-mp-modal-content{
  border:0 !important;
  outline:0 !important;
  padding:0 !important;
  overflow:hidden !important;
  border-radius:22px !important;
  background:#fff !important;
  box-shadow:0 30px 82px rgba(15,23,42,.34) !important;
}

#mp_modal_detalle .ev-mp-modal-content::before{
  display:none !important;
  content:none !important;
}

#mp_modal_detalle .ev-mp-modal-header{
  position:relative !important;
  z-index:4 !important;
  min-height:64px !important;
  margin:-1px -1px 0 -1px !important;
  width:calc(100% + 2px) !important;
  padding:17px 20px 16px 20px !important;
  border:0 !important;
  border-bottom:0 !important;
  border-radius:22px 22px 0 0 !important;
  background:linear-gradient(90deg, #0B4B28 0%, #0F6A38 48%, #16A34A 100%) !important;
  color:#fff !important;
  box-shadow:none !important;
}

#mp_modal_detalle .ev-mp-modal-header::after{
  content:'' !important;
  position:absolute !important;
  left:0 !important;
  right:0 !important;
  bottom:-2px !important;
  height:3px !important;
  background:linear-gradient(90deg, #0B4B28 0%, #0F6A38 48%, #16A34A 100%) !important;
  pointer-events:none !important;
}

#mp_modal_detalle .ev-mp-modal-header .modal-title,
#mp_modal_detalle .ev-mp-modal-header .modal-title span,
#mp_modal_detalle .ev-mp-modal-header i{
  color:#fff !important;
}

#mp_modal_detalle .ev-mp-modal-header .btn-close{
  background:rgba(255,255,255,.16) !important;
  border:0 !important;
  box-shadow:none !important;
}

#mp_modal_detalle .ev-mp-modal-body{
  border-top:0 !important;
  margin-top:0 !important;
}



/* ==========================================================
   EV MARKETPLACE — FIX DEFINITIVO DE STACKING / MODAL
   Problema corregido:
   - El header/card del Marketplace quedaba por encima del backdrop/modal
     por z-index excesivo usado para dropdowns.
   Regla:
   - Header y dropdowns por encima de secciones normales.
   - Modal/backdrop siempre por encima de Marketplace.
========================================================== */
.ev-mp-wrapper,
.ev-mp-content{
  position:relative !important;
  z-index:0 !important;
}

.ev-mp-header{
  position:relative !important;
  z-index:20 !important;
  overflow:visible !important;
}

.ev-mp-header .card-body,
.ev-mp-toolbar,
.ev-mp-toolbar-controls,
.ev-mp-field{
  overflow:visible !important;
}

.ev-mp-split,
.ev-mp-section,
.ev-mp-grid,
.ev-mp-card{
  position:relative !important;
  z-index:1 !important;
}

.ev-mp-select{
  position:relative !important;
  z-index:30 !important;
}

.ev-mp-select.open{
  z-index:80 !important;
}

.ev-mp-select-menu{
  z-index:90 !important;
}

body.modal-open .ev-mp-header,
body.modal-open .ev-mp-select,
body.modal-open .ev-mp-select.open,
body.modal-open .ev-mp-select-menu,
body.modal-open .ev-mp-section,
body.modal-open .ev-mp-card{
  z-index:auto !important;
}

.modal-backdrop{
  z-index:1050 !important;
}

#mp_modal_detalle,
#mp_modal_solicitud{
  z-index:1060 !important;
}

#mp_modal_detalle .modal-dialog,
#mp_modal_solicitud .modal-dialog{
  position:relative !important;
  z-index:1061 !important;
}

#mp_modal_detalle .ev-mp-modal-content{
  position:relative !important;
  z-index:1062 !important;
  background:#fff !important;
  opacity:1 !important;
  border:0 !important;
  overflow:hidden !important;
  border-radius:22px !important;
}

#mp_modal_detalle .ev-mp-modal-header{
  isolation:isolate !important;
  position:relative !important;
  z-index:2 !important;
  margin:-1px -1px 0 -1px !important;
  width:calc(100% + 2px) !important;
  border:0 !important;
  border-bottom:0 !important;
  border-radius:22px 22px 0 0 !important;
  background:linear-gradient(90deg, #0B4B28 0%, #0F6A38 52%, #16A34A 100%) !important;
  box-shadow:none !important;
}

#mp_modal_detalle .ev-mp-modal-header::before,
#mp_modal_detalle .ev-mp-modal-header::after{
  display:none !important;
  content:none !important;
}

#mp_modal_detalle .ev-mp-modal-body,
#mp_modal_detalle .ev-mp-modal-footer{
  position:relative !important;
  z-index:1 !important;
  background:#F3F4F6 !important;
}

#mp_modal_detalle .ev-mp-modal-footer{
  background:#fff !important;
}


/* ============================================================
   EV - Reputación del vendedor en Marketplace
   Chip discreto, no invasivo y compatible con cards actuales
============================================================ */
.ev-mp-seller-rating{
  display:inline-flex;
  align-items:center;
  gap:6px;
  width:max-content;
  max-width:100%;
  flex:0 0 auto;
  min-height:26px;
  margin:2px 0 5px;
  padding:5px 9px;
  border-radius:999px;
  border:1px solid rgba(234,124,18,.18);
  background:linear-gradient(90deg, rgba(255,247,237,.96), rgba(255,255,255,.98));
  color:#7C3E08;
  font-size:.76rem;
  font-weight:900;
  line-height:1.1;
  box-shadow:0 7px 14px rgba(15,23,42,.045);
  white-space:nowrap;
}

.ev-mp-seller-rating i{
  color:#F59E0B;
  font-size:.80rem;
  line-height:1;
}

.ev-mp-seller-rating.is-new{
  border-color:rgba(22,163,74,.18);
  background:linear-gradient(90deg, rgba(236,253,243,.96), rgba(255,255,255,.98));
  color:#166534;
}

.ev-mp-seller-rating.is-new i{
  color:#16A34A;
}

.ev-mp-seller-rating-detail{
  margin:7px 0 10px;
  padding:7px 11px;
  font-size:.82rem;
}

@media (max-width:575.98px){
  .ev-mp-seller-rating{
    font-size:.74rem;
    padding:5px 8px;
  }
}

/* ==========================================================
   PUNTO 7 — SERVICIOS VISIBLES SIN DISPONIBILIDAD DE PEDIDOS
   Corrección visual EV:
   - Estado con verde EV.
   - Acción principal con naranja EV.
   - Sin identidad visual azul añadida para servicios.
========================================================== */
.ev-mp-card-top-status-service{
  background:linear-gradient(135deg, rgba(15,89,47,.14), rgba(22,163,74,.20));
  color:#0F592F;
}

.ev-mp-card-servicio{
  border-color:rgba(15,89,47,.16) !important;
}

.ev-mp-card-servicio:hover{
  border-color:rgba(15,89,47,.42) !important;
}

.ev-mp-card-actions.is-service .ev-mp-btn-servicio{
  width:100%;
  border:none;
  color:#fff;
  background:linear-gradient(135deg, #D97706, #EA7C12);
  box-shadow:0 10px 22px rgba(217,119,6,.28);
}

.ev-mp-card-actions.is-service .ev-mp-btn-servicio:hover{
  color:#fff;
  background:linear-gradient(135deg, #C46B05, #D46F0F);
  box-shadow:0 13px 27px rgba(217,119,6,.38);
}

.ev-mp-service-notice{
  display:flex;
  align-items:flex-start;
  gap:10px;
  margin-top:15px;
  padding:13px 14px;
  border-radius:16px;
  border:1px solid rgba(15,89,47,.16);
  background:linear-gradient(135deg, rgba(230,244,236,.94), rgba(255,255,255,.98));
  color:#0F592F;
}

.ev-mp-service-notice i{
  flex:0 0 auto;
  font-size:18px;
  line-height:1.3;
  color:#0E7A43;
}

.ev-mp-service-notice strong,
.ev-mp-service-notice span{
  display:block;
}

.ev-mp-service-notice strong{
  margin-bottom:2px;
  font-size:13.5px;
  font-weight:900;
}

.ev-mp-service-notice span{
  font-size:12.5px;
  line-height:1.45;
  color:#4B5563;
}



/* ==========================================================
   CORRECCIÓN UX/UI — BOTÓN "VER SERVICIO"
   Alineado al estándar visual del botón "Iniciar sesión" EV:
   - Texto blanco legible.
   - Gradiente naranja EV (#EA7C12 → #F59E0B).
   - Hover #C46B05 → #EA580C, elevación sutil de 1px.
   - Sombra y active consistentes con Login.
========================================================== */
.ev-mp-card-actions.is-service .ev-mp-btn-servicio,
.ev-mp-card-actions.is-service .ev-mp-btn-servicio:focus,
.ev-mp-card-actions.is-service .ev-mp-btn-servicio:visited{
  width:100%;
  border:none !important;
  color:#FFFFFF !important;
  background:linear-gradient(135deg, #EA7C12, #F59E0B) !important;
  font-weight:800 !important;
  box-shadow:0 12px 26px rgba(234,124,18,0.35) !important;
  transition:all 0.2s ease !important;
}

.ev-mp-card-actions.is-service .ev-mp-btn-servicio:hover{
  color:#FFFFFF !important;
  background:linear-gradient(135deg, #C46B05, #EA580C) !important;
  transform:translateY(-1px) !important;
  box-shadow:0 14px 32px rgba(234,124,18,0.48) !important;
}

.ev-mp-card-actions.is-service .ev-mp-btn-servicio:active{
  color:#FFFFFF !important;
  transform:translateY(0) !important;
  box-shadow:0 6px 16px rgba(234,124,18,0.30) !important;
}

.ev-mp-card-actions.is-service .ev-mp-btn-servicio:focus-visible{
  outline:3px solid rgba(234,124,18,0.24) !important;
  outline-offset:3px;
}

</style>

