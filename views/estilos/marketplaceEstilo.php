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
  padding:24px 32px 22px 32px;
}

.ev-mp-title{
  font-size:32px;
  font-weight:700;
  color:var(--ev-verde-oscuro);
}

.ev-mp-subtitle{
  font-size:15px;
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
  gap:16px;
  margin-top:18px;
}

.ev-mp-search-input-wrapper{
  flex:1;
  min-width:260px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px 16px;
  border-radius:999px;
  border:1px solid var(--ev-gris-borde);
  background:#fff;
  height:50px;
  box-sizing:border-box;
}

.ev-mp-search-input-wrapper i{
  font-size:18px;
  color:var(--ev-texto-suave);
}

.ev-mp-search-input{
  border:none!important;
  box-shadow:none!important;
  font-size:15px;
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
  height:50px;
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
  height:50px;
  border-radius:12px;
  font-size:13px;
}

/* =======================================
   CHIPS DE CATEGORÍAS
======================================= */
.ev-mp-chips{
  margin-top:16px;
  display:flex;
  flex-wrap:wrap;
  gap:10px;
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
  margin-top:8px;
}

/* =======================================
   GRID – 4 / 3 / 2 / 1 COLUMNAS
======================================= */
.ev-mp-grid{
  margin-top:20px;
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:22px;
}

/* ============================================================
   ⭐ REDUCCIÓN DE CARDS 25% (Opción A confirmada)
============================================================ */
.ev-mp-card{
  transform:scale(0.75);
  transform-origin:top left;
  margin-bottom:-38px; /* compensa espacio visual */
}

/* Imagen más compacta */
.ev-mp-card-media{
  height:170px !important;
}

/* Texto más compacto */
.ev-mp-card-title{
  font-size:14px !important;
}

.ev-mp-card-price{
  font-size:15px !important;
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
  box-shadow:0 4px 14px rgba(0,0,0,0.04);
}

.ev-mp-card:hover{
  transform:translateY(-5px) scale(0.75);
  box-shadow:0 18px 32px rgba(0,0,0,0.1);
  border-color:var(--ev-verde);
}

/* Imagen */
.ev-mp-card-media{
  position:relative;
  width:100%;
  height:230px;
}

.ev-mp-card-media img{
  width:100%;
  height:100%;
  object-fit:cover;
}

/* Badges */
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

.ev-mp-badge-potenciado{
  background:#FF7A1Acc;
}

.ev-mp-badge-nuevo{
  background:#22c55ecc;
}

.ev-mp-badge-category{
  background:rgba(0,0,0,0.6);
}

/* Body */
.ev-mp-card-body{
  padding:16px 18px;
  display:flex;
  flex-direction:column;
  gap:8px;
}

.ev-mp-card-title{
  margin:0;
  font-weight:600;
  color:var(--ev-texto);
}

.ev-mp-card-price{
  margin:0;
  font-weight:700;
  color:var(--ev-verde);
}

.ev-mp-card-meta{
  margin-top:8px;
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
  width:32px;
  height:32px;
  border-radius:50%;
  background:var(--ev-verde-suave);
  color:var(--ev-verde-oscuro);
  font-size:14px;
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
  margin-top:10px;
  display:flex;
  justify-content:space-between;
  gap:8px;
}

.ev-mp-card-actions .btn{
  flex:1;
  border-radius:999px;
}

/* Estado vacío */
#mp_empty_state{
  display:none;
  margin-top:24px;
  text-align:center;
  color:var(--ev-texto-suave);
}

/* ============================================================
   MODAL DETALLE PUBLICACIÓN – Estilo igual a previsualización
============================================================ */

/* Caja principal del modal */
.ev-mp-modal-body {
  padding: 20px 26px;
}

/* Card interna que replica la previsualización */
.ev-mp-preview-card{
  background:#ffffff;
  border-radius:16px;
  border:1px solid var(--ev-gris-borde);
  padding:16px;
  box-shadow:0 4px 12px rgba(0,0,0,0.04);
  max-width: 780px;        /* ancho razonable en escritorio */
  margin: 0 auto;          /* centrado dentro del modal */
}

/* CONTENEDOR de la imagen principal
   ✅ Sin altura fija: deja que la imagen mande
*/
.ev-mp-modal-media {
  width: 100%;
  background: #ffffff;
  border: 1px solid #E3E8EF;
  border-radius: 16px;
  overflow: hidden;
  padding: 0;
}

/* Imagen principal
   ✅ Ocupa todo el ancho, altura en proporción (como en previsualización)
   ✅ No se recorta porque height es auto
*/
.ev-mp-modal-media img {
  width: 100%;
  height: auto;
  display: block;
  border-radius: 12px;
}

/* Miniaturas */
.ev-mp-modal-thumbs {
  display: flex;
  gap: 10px;
  margin: 15px 0 25px;
  padding-left: 4px;
}

.ev-mp-modal-thumb {
  width: 62px;
  height: 62px;
  border-radius: 8px;
  border: 2px solid transparent;
  overflow: hidden;
  cursor: pointer;
  flex-shrink: 0;
}

.ev-mp-modal-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.ev-mp-modal-thumb.active {
  border-color: var(--ev-verde);
}

/* Títulos / textos */
.ev-mp-modal-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--ev-verde-oscuro);
  margin-bottom: 4px;
}

.ev-mp-modal-price {
  font-size: 18px;
  font-weight: 700;
  color: var(--ev-verde);
  margin-bottom: 14px;
}

.ev-mp-modal-desc {
  font-size: 14px;
  color: #555;
  line-height: 1.5;
}

/* Responsive modal */
@media(max-width:575px){
  .ev-mp-modal-body{
    padding-inline:14px;
  }
  .ev-mp-preview-card{
    max-width: 100%;
  }
}

/* =======================================
   RESPONSIVE GRID
======================================= */
@media (max-width: 1199.98px){
  .ev-mp-grid{
    grid-template-columns:repeat(3,1fr);
  }
}

@media (max-width: 991.98px){
  .ev-mp-header .card-body{
    padding:18px 18px 16px 18px;
  }
  .ev-mp-title{
    font-size:24px;
  }
  .ev-mp-grid{
    grid-template-columns:repeat(2,1fr);
  }
}

@media (max-width: 575.98px){
  .ev-mp-search-row{
    flex-direction:column;
  }
  .ev-mp-search-input-wrapper{
    width:100%;
  }
  .ev-mp-grid{
    grid-template-columns:1fr;
  }
}
</style>
