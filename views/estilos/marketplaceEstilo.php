<?php /* views/estilos/marketplaceEstilo.php – UX/UI Marketplace Entre Vecinos */ ?>

<style>
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#198754;
  --ev-verde-suave:#E6F4EC;
  --ev-naranja:#FF7A1A;
  --ev-gris-fondo:#F5F7FA;
  --ev-gris-borde:#E3E8EF;
  --ev-texto:#1A1F36;
  --ev-texto-suave:#6B7280;
}

/* Fondo suave solo para el área donde se monta el marketplace */
.ev-mp-wrapper{
  background-color:var(--ev-gris-fondo);
  padding-bottom:40px;
}

/* =======================================
   HEADER – CARD PRINCIPAL
======================================= */
.ev-mp-header{
  border-radius:20px;
  border:1px solid var(--ev-gris-borde);
  background:#ffffff;
  box-shadow:0 6px 18px rgba(0,0,0,0.05);
}

.ev-mp-header .card-body{
  padding:16px 24px 12px 24px; /* un poco menos padding abajo */
}

.ev-mp-title{
  font-size:28px;
  font-weight:700;
  color:var(--ev-verde-oscuro);
}

.ev-mp-subtitle{
  font-size:14px;
  color:var(--ev-texto-suave);
}

/* =======================================
   CONDOMINIO ACTUAL – CHIP
======================================= */
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
  font-weight:600;
  color:var(--ev-verde-oscuro);
}

/* =======================================
   BUSCADOR + FILTROS
======================================= */
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

.ev-mp-search-input:focus{
  outline:none;
}

.ev-mp-search-actions{
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  gap:10px;
}

.ev-mp-btn-filtros{
  height:46px;
  border-radius:12px;
  font-weight:500;
}

.ev-mp-btn-filtros i{
  margin-right:4px;
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
  border-radius:12px;
  font-size:13px;
}

/* =======================================
   CHIPS DE CATEGORÍAS
======================================= */
.ev-mp-chips{
  margin-top:12px;
  display:flex;
  flex-wrap:wrap;
  gap:8px;
}

.ev-mp-chip{
  padding:7px 18px;
  border-radius:25px;
  border:1px solid var(--ev-gris-borde);
  background:#fff;
  font-size:14px;
  color:var(--ev-texto-suave);
  cursor:pointer;
  transition:all .18s ease;
}

.ev-mp-chip:hover{
  background:var(--ev-verde-suave);
  color:var(--ev-verde-oscuro);
}

.ev-mp-chip.active{
  background:var(--ev-verde-oscuro);
  color:#fff;
  border-color:var(--ev-verde-oscuro);
}

/* Resumen resultados */
.ev-mp-resumen{
  font-size:13px;
  color:var(--ev-texto-suave);
  margin-top:6px;
}

/* =======================================
   GRID – columnas entre 280 y 340px alineadas a la izquierda
======================================= */
.ev-mp-grid{
  margin-top:10px; /* menos espacio entre header y resultados */
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(280px, 340px));
  column-gap:22px;  /* separación horizontal un poco mayor */
  row-gap:18px;
  justify-content:start;
  margin-left:auto;
  margin-right:auto;
}

/* =======================================
   CARD – PUBLICACIÓN
======================================= */
.ev-mp-card{
  border-radius:20px;
  overflow:hidden;
  border:1px solid var(--ev-gris-borde);
  background:#fff;
  display:flex;
  flex-direction:column;
  transition:all .22s ease;
  box-shadow:0 3px 10px rgba(0,0,0,0.04);
}

.ev-mp-card:hover{
  transform:translateY(-4px);
  box-shadow:0 16px 30px rgba(0,0,0,0.12);
  border-color:var(--ev-verde);
}

/* Imagen de la card – portada completa */
.ev-mp-card-media{
  position:relative;
  width:100%;
  height:220px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:#f5f7fa;
}

.ev-mp-card-media img{
  width:100%;
  height:100%;
  object-fit:cover; /* cubre todo el contenedor */
  border-top-left-radius:20px;   /* integración visual con la card */
  border-top-right-radius:20px;
}

/* Badges */
.ev-mp-card-badges{
  position:absolute;
  top:10px;
  left:10px;
  display:flex;
  gap:6px;
}

/* Badge genérico */
.ev-mp-badge{
  padding:3px 9px;
  border-radius:25px;
  font-size:12px;
  backdrop-filter:blur(3px);
  color:#fff;
}

.ev-mp-badge-potenciado{
  background:#FF7A1Acc;
}

/* Ocultamos el badge verde (donde se mostraba "Publicado") */
.ev-mp-badge-nuevo{
  background:#22c55ecc;
  display:none !important;
}

.ev-mp-badge-category{
  background:rgba(0,0,0,0.6);
}

/* Body */
.ev-mp-card-body{
  padding:10px 14px 10px 14px; /* más compacto en alto */
  display:flex;
  flex-direction:column;
  gap:6px;
}

.ev-mp-card-title{
  margin:0;
  font-weight:600;
  color:var(--ev-texto);
  font-size:15px; /* un poco más grande */
}

.ev-mp-card-price{
  margin:0;
  font-weight:700;
  color:var(--ev-verde);
  font-size:17px; /* destaca más el precio */
}

.ev-mp-card-meta{
  margin-top:6px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}

.ev-mp-card-vecino{
  display:flex;
  align-items:center;
  gap:8px;
}

.ev-mp-avatar{
  width:30px;
  height:30px;
  border-radius:50%;
  background:var(--ev-verde-suave);
  color:var(--ev-verde-oscuro);
  font-size:13px;
  font-weight:700;
  display:flex;
  align-items:center;
  justify-content:center;
}

.ev-mp-vecino-nombre{
  font-size:13px;
  font-weight:600;
}

.ev-mp-vecino-condominio{
  font-size:12px;
  color:var(--ev-texto-suave);
}

.ev-mp-card-rating{
  font-size:12px;
  color:var(--ev-texto-suave);
  display:flex;
  align-items:center;
  gap:4px;
}

.ev-mp-card-rating i{
  color:#f59e0b;
}

/* Acciones */
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

/* Estado vacío */
#mp_empty_state{
  display:none;
  margin-top:24px;
  text-align:center;
  color:var(--ev-texto-suave);
}

/* ============================================================
   MODAL DETALLE PUBLICACIÓN
============================================================ */

.ev-mp-modal-body {
  padding:20px 26px;
}

.ev-mp-preview-card{
  background:#ffffff;
  border-radius:18px;
  border:1px solid var(--ev-gris-borde);
  padding:16px;
  box-shadow:0 4px 12px rgba(0,0,0,0.04);
  max-width:860px;
  margin:0 auto;
}

.ev-mp-modal-media {
  width:100%;
  background:#ffffff;
  border:1px solid #E3E8EF;
  border-radius:16px;
  overflow:hidden;
  padding:0;
  display:flex;
  justify-content:center;
  align-items:center;
  aspect-ratio:4 / 3;
  max-height:520px;
  margin-bottom:14px;
}

.ev-mp-modal-media img {
  width:100%;
  height:100%;
  object-fit:cover;
  border-radius:12px;
  transform:scale(1.08);
  transform-origin:center center;
  transition:transform 0.25s ease;
}

.ev-mp-modal-thumbs {
  display:flex;
  gap:10px;
  margin:15px 0 25px;
  padding-left:4px;
}

.ev-mp-modal-thumb {
  width:62px;
  height:62px;
  border-radius:8px;
  border:2px solid transparent;
  overflow:hidden;
  cursor:pointer;
  flex-shrink:0;
}

.ev-mp-modal-thumb img {
  width:100%;
  height:100%;
  object-fit:cover;
  transform:scale(1.03);
  transform-origin:center center;
  transition:transform 0.2s ease;
}

.ev-mp-modal-thumb.active {
  border-color:var(--ev-verde);
}

.ev-mp-modal-title {
  font-size:20px;
  font-weight:700;
  color:var(--ev-verde-oscuro);
  margin-bottom:4px;
}

.ev-mp-modal-price {
  font-size:18px;
  font-weight:700;
  color:var(--ev-verde);
  margin-bottom:14px;
}

.ev-mp-modal-desc {
  font-size:14px;
  color:#555;
  line-height:1.5;
}

.modal-footer {
  border-top:none;
  padding:16px 24px 20px 24px;
}

.modal-footer .btn-ev-neutral,
.modal-footer .btn-ev-primary{
  min-width:150px;
}

/* Ajuste de tamaño general del modal en escritorio */
@media (min-width:768px){
  .modal-dialog.modal-xl{
    max-width:980px;
  }
}

/* Responsive modal */
@media(max-width:575.98px){
  .ev-mp-modal-body{
    padding-inline:14px;
  }
  .ev-mp-preview-card{
    border-radius:16px;
  }
  .ev-mp-modal-media{
    max-height:360px;
  }
  .ev-mp-modal-media img{
    transform:scale(1.03);
  }
  .ev-mp-modal-thumb img{
    transform:scale(1.0);
  }
}

/* =======================================
   RESPONSIVE GRID
======================================= */
@media (max-width:991.98px){
  .ev-mp-header .card-body{
    padding:14px 16px 10px 16px;
  }
  .ev-mp-title{
    font-size:24px;
  }
}

@media (max-width:575.98px){
  .ev-mp-search-row{
    flex-direction:column;
  }
  .ev-mp-search-input-wrapper{
    width:100%;
  }
  .ev-mp-grid{
    grid-template-columns:minmax(0, 1fr);
    column-gap:0;
    row-gap:16px;
  }
}
</style>
