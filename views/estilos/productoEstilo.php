<?php /* publicacionesEstilo.php – UX/UI Publicaciones (armonizado con Mi Billetera) */ ?>
<style>
:root{
  --ev-verde-oscuro: var(--verde-oscuro, #0F592F);
  --ev-verde:        var(--verde-claro, #198754);
  --ev-verde-suave:  #E6F4EC;
  --ev-gris-fondo:   var(--gris-claro, #F3F4F6);
  --ev-gris-borde:   var(--gris-borde, #E5E7EB);
  --ev-texto:        #1A1F36;
  --ev-texto-suave:  var(--gris-texto, #6B7280);
  --ev-rojo:         #DC2626;
  --ev-naranja:      #FF7A1A;

  --ev-shadow-card:  0 14px 40px rgba(15, 23, 42, 0.14);
  --ev-shadow-soft:  0 10px 24px rgba(15, 23, 42, 0.06);
  --ev-radius-card:  18px;
  --ev-radius-modal: 22px;

  --ev-vh: 1vh;
  --ev-header-grad: linear-gradient(90deg, #0F592F 0%, #137A43 55%, #0F592F 100%);

  /* ✅ Premium tokens */
  --ev-glass: rgba(255,255,255,.72);
  --ev-glass-strong: rgba(255,255,255,.86);
  --ev-stroke: rgba(15,23,42,.10);
  --ev-stroke-soft: rgba(15,23,42,.08);
}

/* WRAPPER / CARD */
.ev-pubs-wrapper{ max-width: 1100px; margin: 0 auto; }
.ev-pubs-card{
  border-radius: var(--ev-radius-card);
  border: 1px solid var(--ev-gris-borde);
  background: #fff;
  box-shadow: var(--ev-shadow-card);
  margin: 24px auto 40px auto;
  overflow: hidden;
}
.ev-pubs-card .card-body{ padding: 24px 32px; }

.ev-pubs-title{
  font-size: 1.65rem;
  font-weight: 800;
  color: var(--ev-verde-oscuro);
  letter-spacing: -0.01em;
}
.ev-pubs-title-icon{
  width: 34px;
  height: 34px;
  border-radius: 999px;
  background: var(--ev-verde-suave);
  color: var(--ev-verde-oscuro);
  font-size: 1.1rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
}
.ev-pubs-subtitle{
  font-size: 0.92rem;
  color: var(--ev-texto-suave);
  line-height: 1.35;
}
.ev-pubs-divider{
  border-top: 1px solid rgba(148, 163, 184, 0.35);
  margin-left: -32px;
  margin-right: -32px;
}

/* TABLE */
.ev-pubs-table-wrapper{
  border: 1px solid rgba(229, 231, 235, 0.9);
  border-radius: 16px;
  overflow: hidden;
  background: #ffffff;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
}
.ev-pubs-table{ margin: 0; }
.ev-pubs-table thead th{
  border-bottom: 1px solid var(--ev-gris-borde);
  font-weight: 800;
  color: rgba(100,116,139,.92);
  text-transform: uppercase;
  font-size: 0.78rem;
  letter-spacing: 0.06em;
  background: linear-gradient(180deg, #FCFDFE 0%, #F9FAFB 100%);
}
.ev-pubs-table tbody td{
  border-color: rgba(229, 231, 235, 0.9);
  vertical-align: middle;
}
.ev-pubs-table tbody tr:hover{ background-color: #F9FAFB; }

.ev-code{
  font-weight: 900;
  color: var(--ev-verde-oscuro);
  letter-spacing: .04em;
}
.td-trunc{
  max-width: 520px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ✅ Columna final (PUBLICACIÓN) */
.ev-pubs-table thead th:last-child,
.ev-pubs-table tbody td:last-child{
  text-align: center;
  width: 210px;
  white-space: nowrap;
}
@media (max-width: 992px){
  .ev-pubs-table thead th:last-child,
  .ev-pubs-table tbody td:last-child{ width: 180px; }
}

/* =========================================================
   BADGES (ESTADO) — ahora más “soft” y elegante
========================================================= */
.ev-badge{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: .74rem;
  font-weight: 900;
  letter-spacing: .02em;

  /* 🔥 premium */
  border:1px solid rgba(148,163,184,.24);
  box-shadow: inset 0 1px 0 rgba(255,255,255,.78);
  backdrop-filter: blur(6px);
}


/* ✅ NUEVO: más mate + menos “verde idéntico” al aprobado */
.ev-badge--nuevo{
  /* ✅ neutro premium, NO “verde relleno” para no competir con Aprobado */
  background: linear-gradient(180deg, rgba(248,250,252,.98) 0%, rgba(241,245,249,.88) 100%);
  color: rgba(15,89,47,.92);
  border:1px solid rgba(148,163,184,.24);
}


/* USADO: cálido premium */
.ev-badge--usado{
  /* ✅ neutro premium con acento ámbar */
  background: linear-gradient(180deg, rgba(248,250,252,.98) 0%, rgba(241,245,249,.88) 100%);
  color: rgba(122,90,0,.92);
  border:1px solid rgba(148,163,184,.24);
}


/* NO APLICA */
.ev-badge--noaplica{
  background: linear-gradient(180deg, rgba(243,244,246,.95) 0%, rgba(243,244,246,.78) 100%);
  color: rgba(71,85,105,.92);
  border-color: rgba(148,163,184,.22);
}

/* BOTONES */
.btn-ev-orange{
  background-image: linear-gradient(180deg, #FF9B3A, #FF7A1A);
  border: none;
  color: #ffffff;
  font-weight: 800;
  border-radius: 999px;
  padding: 0.48rem 1.9rem;
  font-size: 0.96rem;
  box-shadow: 0 14px 28px rgba(255, 122, 26, 0.45);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}
.btn-ev-orange:hover{
  filter: brightness(1.05);
  transform: translateY(-1px);
  box-shadow: 0 18px 32px rgba(255, 122, 26, 0.55);
  color: #ffffff;
}
.btn-ev-outline{
  background-color: #ffffff;
  border-radius: 999px;
  border: 1px solid var(--ev-gris-borde);
  color: var(--ev-texto);
  font-weight: 700;
  padding: 0.45rem 1.4rem;
  font-size: 0.93rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background-color 0.15s ease, transform 0.15s ease;
}
.btn-ev-outline:hover{
  background-color: #F9FAFB;
  transform: translateY(-1px);
}

/* =========================================================
   ACCIONES TABLA (MEJORADO)
========================================================= */
.ev-actions{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
  min-height: 40px;
}

/* CHIP (botón/pill) */
.ev-chip{
  min-width: 104px;
  width: auto;
  justify-content: center;
  text-align: center;
  border-radius: 999px;
  padding: 0.42rem 0.98rem;
  font-weight: 900;
  font-size: .86rem;
  line-height: 1;
  background: #fff;
  border: 1px solid rgba(148,163,184,.34);
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
  transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease, border-color .15s ease;
  user-select: none;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
}
.ev-chip:hover{
  transform: translateY(-1px);
  box-shadow: 0 14px 22px rgba(15, 23, 42, 0.08);
}
.ev-chip:focus-visible{
  outline: 0;
  box-shadow: 0 0 0 .2rem rgba(25,135,84,.18), 0 14px 22px rgba(15, 23, 42, 0.08);
}

/* Deshabilitado (estado) */
.ev-chip:disabled,
.ev-chip[disabled]{
  cursor: default;
  transform: none;
}

/* Variantes */
.ev-chip-green{
  color: rgba(15,89,47,.95);
  border: 1px solid rgba(15,89,47,.26);
  background: linear-gradient(180deg, rgba(230,244,236,.95) 0%, rgba(255,255,255,.92) 100%);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.80),
    0 10px 22px rgba(15, 23, 42, 0.08);
}

.ev-chip-green:hover{ background: rgba(230,244,236,.55); border-color: rgba(15,89,47,.42); }

.ev-chip-red{ border-color: rgba(220,38,38,.34); color: var(--ev-rojo); }
.ev-chip-red:hover{ background: rgba(220,38,38,.06); border-color: rgba(220,38,38,.48); }

.ev-chip-amber{ border-color: rgba(255,122,26,.36); color: var(--ev-naranja); }
.ev-chip-amber:hover{ background: rgba(255,122,26,.08); border-color: rgba(255,122,26,.52); }

.ev-chip-amber[disabled],
.ev-chip[data-status="publicado"]{
  background: rgba(255,122,26,.06);
  border-color: rgba(255,122,26,.22);
  color: rgba(255,122,26,.55);
  box-shadow: inset 0 1px 0 rgba(255,255,255,.75);
}

/* =========================================================
   ✅ PUBLICACIÓN: APROBADO — glass premium (diferente a “Nuevo”)
   - Más jerarquía
   - Más “pro”
========================================================= */
.ev-chip.ev-chip-green:disabled,
.ev-chip.ev-chip-green[disabled]{
  background:
    linear-gradient(180deg, rgba(255,255,255,.92) 0%, rgba(230,244,236,.82) 55%, rgba(230,244,236,.70) 100%);
  border: 1px solid rgba(15,89,47,.18);
  color: rgba(15,89,47,.92);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.85),
    0 14px 26px rgba(15, 23, 42, 0.06);
  position: relative;
}

/* brillo sutil (sheen) */
.ev-chip.ev-chip-green:disabled::after,
.ev-chip.ev-chip-green[disabled]::after{
  content: "";
  position: absolute;
  inset: 2px;
  border-radius: 999px;
  pointer-events: none;
  background: linear-gradient(90deg, rgba(255,255,255,.0) 0%, rgba(255,255,255,.38) 45%, rgba(255,255,255,.0) 100%);
  opacity: .45;
  filter: blur(.2px);
}

/* SECCIONES */
.ev-section{
  border: 1px solid rgba(229,231,235,.9);
  border-radius: 16px;
  background: #fff;
  box-shadow: var(--ev-shadow-soft);
  padding: 16px;
}
.ev-section-title{ font-weight: 800; color: var(--ev-texto); margin-bottom: 4px; }
.ev-section-subtitle{ color: var(--ev-texto-suave); font-size: .9rem; }

/* Dropzone */
.ev-dropzone{
  border: 2px dashed rgba(148,163,184,.55);
  border-radius: 16px;
  padding: 18px 14px;
  text-align:center;
  cursor:pointer;
  background: #F9FAFB;
  transition: border-color .15s ease, background-color .15s ease, transform .15s ease;
  position: relative;
  z-index: 2;
  pointer-events: auto;
}
.ev-dropzone .ico{ font-size: 1.6rem; color: var(--ev-verde); margin-bottom: 6px; }
.ev-dropzone .t1{ font-weight: 800; color: var(--ev-verde-oscuro); }
.ev-dropzone .t2{ color: var(--ev-texto-suave); font-size: .86rem; }
.ev-dropzone.drag-over{
  border-color: rgba(25,135,84,.65);
  background: rgba(230,244,236,.9);
  transform: translateY(-1px);
}

/* Tiles */
.ev-tiles{ display:flex; flex-wrap:wrap; gap:10px; }

/* ✅ No mostrar contenedor si está vacío */
#evTiles:empty,
#evTilesEdit:empty{ display:none; }

/* Moderno */
@supports selector(:has(*)) {
  .ev-section:has(#evTiles:not(:empty)) .ev-dropzone,
  .ev-section:has(#evTilesEdit:not(:empty)) .ev-dropzone{
    display: none !important;
  }
}
/* Fallback */
.ev-section.ev-has-tiles .ev-dropzone{
  display:none !important;
}

.ev-tile{
  width: 86px;
  height: 86px;
  border-radius: 14px;
  border: 1px solid rgba(148,163,184,.35);
  overflow:hidden;
  position:relative;
  background:#fff;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
}
.ev-tile img{ width:100%; height:100%; object-fit:cover; display:block; }

.ev-tile-remove{
  position: absolute;
  top: 6px;
  right: 6px;
  width: 26px;
  height: 26px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.42);
  background: rgba(15, 23, 42, 0.58);
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  font-size: 16px;
  line-height: 1;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.22);
  backdrop-filter: blur(6px);
  transition: transform .15s ease, background-color .15s ease, box-shadow .15s ease, border-color .15s ease, opacity .15s ease;
  opacity: .92;
}
.ev-tile:hover .ev-tile-remove{
  opacity: 1;
  background: rgba(15, 23, 42, 0.74);
  border-color: rgba(255,255,255,.60);
  box-shadow: 0 12px 22px rgba(15, 23, 42, 0.30);
  transform: translateY(-1px);
}
.ev-tile-remove:focus-visible{
  outline: 0;
  box-shadow:
    0 0 0 .2rem rgba(25,135,84,.22),
    0 12px 22px rgba(15, 23, 42, 0.30);
}

/* Tile agregar */
.ev-tile-add{
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  text-align:center;
  gap: 4px;
  background:#F9FAFB;
  border: 2px dashed rgba(148,163,184,.55);
  border-radius: 14px;
  cursor:pointer;
  padding: 8px 6px;
  line-height: 1.15;
  transition: background-color .15s ease, transform .15s ease, border-color .15s ease;
}
.ev-tile-add:hover{
  background: rgba(230,244,236,.85);
  border-color: rgba(25,135,84,.55);
  transform: translateY(-1px);
}
.ev-tile-add .ico{
  display:flex;
  align-items:center;
  justify-content:center;
  width: 30px;
  height: 30px;
  border-radius: 999px;
  background: rgba(25,135,84,.10);
  color: var(--ev-verde);
  font-size: 1.1rem;
}
.ev-tile-add .t1,
.ev-tile-add .t2{ width: 100%; text-align:center; }
.ev-tile-add .t1{ font-weight:800; color: var(--ev-texto); font-size:.86rem; }
.ev-tile-add .t2{
  color: var(--ev-texto-suave);
  font-size:.78rem;
  line-height: 1.15;
  white-space: normal;
}

/* Preview derecha */
.ev-preview-area{
  border: 1px dashed rgba(148,163,184,.55);
  border-radius: 16px;
  background:#F9FAFB;
  padding: 12px;
}
.ev-preview-title{
  font-weight: 900;
  color: var(--ev-verde-oscuro);
  display:flex;
  align-items:center;
  gap:8px;
  margin-bottom: 10px;
}
.ev-preview-main{
  border-radius: 14px;
  overflow:hidden;
  border:1px solid rgba(148,163,184,.22);
  background:#fff;
}
.ev-preview-main img{ width:100%; height: auto; display:block; }
.ev-preview-thumbs{ display:flex; gap:10px; margin-top: 10px; }
.ev-preview-thumb{
  width: 64px;
  height: 48px;
  border-radius: 12px;
  overflow:hidden;
  border:1px solid rgba(148,163,184,.3);
  cursor:pointer;
  background:#fff;
}
.ev-preview-thumb.active{ outline: 2px solid rgba(25,135,84,.55); }
.ev-preview-thumb img{ width:100%; height:100%; object-fit:cover; display:block; }

/* MODALES */
.ev-modal{ --bs-modal-margin: 12px; }
.ev-modal .modal-dialog{
  width: calc(100% - (var(--bs-modal-margin) * 2));
  margin: var(--bs-modal-margin) auto;
}
.ev-modal-xl{ max-width: 980px; }
@media (min-width: 992px){ .ev-modal-xl{ max-width: 1040px; } }

.ev-modal-content{
  border-radius: var(--ev-radius-modal);
  overflow: hidden;
  border: 0;
  box-shadow: 0 22px 60px rgba(15, 23, 42, 0.35);
}
.ev-modal-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding: 16px 24px;
  background-image: var(--ev-header-grad);
  color:#fff;
}
.ev-modal-title{
  font-size: 1.1rem;
  font-weight: 700;
  color:#fff !important;
  display:flex;
  align-items:center;
  gap:8px;
}
.ev-modal-body{ padding: 22px 26px; background:#fff; }
.ev-modal-footer{
  padding: 14px 26px 20px 26px;
  background:#fff;
  border-top: 1px solid rgba(229, 231, 235, 0.9);
  display:flex;
  justify-content:flex-end;
  gap:.75rem;
}
.ev-modal-flex{ display:flex; flex-direction:column; min-height: 0; }
.ev-modal-body-scroll{
  overflow:auto;
  -webkit-overflow-scrolling: touch;
  min-height: 0;
}
.ev-modal .modal-dialog{
  max-height: calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2));
}
.ev-modal .modal-content{
  max-height: calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2));
}
.ev-modal .ev-modal-body-scroll{
  max-height: calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2) - 64px - 72px);
}
.ev-modal-body .form-label{ font-weight: 700; color: var(--ev-texto); }
.ev-modal-body .form-control,
.ev-modal-body .form-select{
  border-radius: 14px;
  border: 1px solid rgba(148,163,184,.35);
  box-shadow: none;
  padding: 0.62rem 0.85rem;
  transition: border-color .15s ease, box-shadow .15s ease;
}
.ev-modal-body .form-control:focus,
.ev-modal-body .form-select:focus{
  border-color: rgba(25,135,84,.55);
  box-shadow: 0 0 0 .2rem rgba(25,135,84,.15);
}
.ev-modal-body .form-text{ color: var(--ev-texto-suave); }

/* Footer card */
.ev-pubs-footer{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap: 12px;
  flex-wrap:wrap;
}
.ev-select-sm{
  border-radius: 14px;
  border: 1px solid rgba(148,163,184,.35);
}

/* RESPONSIVE */
@media (max-width: 575.98px){
  .ev-pubs-wrapper{ padding-left:12px !important; padding-right:12px !important; }
  .ev-pubs-card{ margin:16px auto 28px auto; }
  .ev-pubs-card .card-body{ padding: 18px 14px; }
  .ev-pubs-divider{ margin-left:-14px; margin-right:-14px; }

  .ev-modal-body{ padding: 18px 16px; }
  .ev-modal-footer{
    padding: 12px 16px 16px 16px;
    flex-direction:column;
    align-items:stretch;
  }
  .ev-modal-footer .btn{ width:100%; justify-content:center; }

  .td-trunc{ max-width: 220px; }

  .ev-actions{ display:flex; justify-content:center; }
  .ev-chip{ min-width: 104px; }

  .ev-tile-remove{ width: 28px; height: 28px; font-size: 17px; }
  .ev-tile-add .t2{ font-size: .76rem; }
}

/* ====================================================
   FIX DEFINITIVO – CENTRADO REAL + CARD MÁS ANCHO
==================================================== */
.content-wrapper,
.content,
.container-fluid{
  max-width: 100% !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
}

.ev-pubs-wrapper{
  width: 100%;
  max-width: none;
  display: flex;
  justify-content: center;
  padding: 32px 24px;
  box-sizing: border-box;
  margin: 0 auto;
}

.ev-pubs-card{
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
}

@media (max-width: 992px){
  .ev-pubs-wrapper{ padding: 20px 16px; }
}
@media (max-width: 576px){
  .ev-pubs-wrapper{ padding: 16px 12px; }
  .ev-pubs-card{ max-width: 100%; }
}
</style>
