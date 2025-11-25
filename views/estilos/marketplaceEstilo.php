<?php /* marketplaceEstilo.php – UX/UI Marketplace Entre Vecinos */ ?>

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
  min-width:280px;
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
  transform:translateY(-5px);
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

/* Badges sobre la imagen */
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

/* Body card */
.ev-mp-card-body{
  padding:16px 18px;
  display:flex;
  flex-direction:column;
  gap:8px;
}

.ev-mp-card-title{
  font-size:17px;
  font-weight:600;
  color:var(--ev-texto);
  margin:0;
}

.ev-mp-card-price{
  font-size:18px;
  font-weight:700;
  color:var(--ev-verde-oscuro);
  margin:0;
}

/* Meta (vecino + rating) */
.ev-mp-card-meta{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:8px;
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
  color:var(--ev-texto);
}

.ev-mp-vecino-condominio{
  font-size:11px;
  color:var(--ev-texto-suave);
}

.ev-mp-card-rating{
  display:flex;
  align-items:center;
  gap:4px;
  font-size:12px;
  color:var(--ev-texto-suave);
}

.ev-mp-card-rating i{
  color:#F59E0B;
}

.ev-mp-rating-votos{
  font-size:11px;
}

/* Acciones */
.ev-mp-card-actions{
  margin-top:8px;
  display:flex;
  gap:10px;
}

.ev-mp-btn-detalle,
.ev-mp-btn-pedir{
  border-radius:50px;
  padding:6px 14px;
  font-size:13px;
  font-weight:600;
}

/* =======================================
   RESPONSIVE
======================================= */

/* Laptops / pantallas medianas: 3 columnas */
@media (max-width:1400px){
  .ev-mp-grid{
    grid-template-columns:repeat(3,1fr);
  }
}

/* Tablets horizontales: 2 columnas */
@media (max-width:991.98px){
  .ev-mp-header .card-body{
    padding:20px 18px;
  }

  .ev-mp-grid{
    grid-template-columns:repeat(2,1fr);
  }

  .ev-mp-search-row{
    flex-direction:column;
  }

  .ev-mp-search-actions{
    justify-content:space-between;
  }

  .ev-mp-search-input-wrapper{
    min-width:0;
    width:100%;
  }
}

/* Móvil: 1 columna */
@media (max-width:575.98px){
  .ev-mp-title{
    font-size:24px;
  }

  .ev-mp-grid{
    grid-template-columns:1fr;
  }

  .ev-mp-card-media{
    height:210px;
  }

  .ev-mp-search-row{
    gap:10px;
  }

  .ev-mp-search-input-wrapper{
    min-width:0;
    width:100%;
  }
}
</style>
