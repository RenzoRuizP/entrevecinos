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

  /* soporte para “rebote” móvil */
  --ev-vh: 1vh;

  /* Degradado header (mismo concepto que “Recuperar cuenta”) */
  --ev-header-grad: linear-gradient(90deg, #0F592F 0%, #137A43 55%, #0F592F 100%);
}

/* =========================
   WRAPPER / CARD
========================= */
.ev-pubs-wrapper{
  max-width: 1100px;
  margin: 0 auto;
}

.ev-pubs-card{
  border-radius: var(--ev-radius-card);
  border: 1px solid var(--ev-gris-borde);
  background: #fff;
  box-shadow: var(--ev-shadow-card);
  margin: 24px auto 40px auto;
  overflow: hidden;
}

.ev-pubs-card .card-body{
  padding: 24px 32px;
}

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

/* =========================
   TABLE
========================= */
.ev-pubs-table-wrapper{
  border: 1px solid rgba(229, 231, 235, 0.9);
  border-radius: 16px;
  overflow: hidden;
  background: #ffffff;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
}

.ev-pubs-table{
  margin: 0;
}

.ev-pubs-table thead th{
  border-bottom: 1px solid var(--ev-gris-borde);
  font-weight: 700;
  color: var(--ev-texto-suave);
  text-transform: uppercase;
  font-size: 0.78rem;
  letter-spacing: 0.05em;
  background: #F9FAFB;
}

.ev-pubs-table tbody td{
  border-color: rgba(229, 231, 235, 0.9);
  vertical-align: middle;
}

.ev-pubs-table tbody tr:hover{
  background-color: #F9FAFB;
}

.ev-code{
  font-weight: 800;
  color: var(--ev-verde-oscuro);
  letter-spacing: .02em;
}

.td-trunc{
  max-width: 520px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* =========================
   BADGES
========================= */
.ev-badge{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: .74rem;
  font-weight: 800;
  letter-spacing: .02em;
}

.ev-badge--nuevo{
  background: rgba(25,135,84,.14);
  color: var(--ev-verde-oscuro);
  border:1px solid rgba(25,135,84,.22);
}
.ev-badge--usado{
  background: rgba(255, 193, 7, .22);
  color: #7a5a00;
  border:1px solid rgba(255, 193, 7, .35);
}
.ev-badge--noaplica{
  background: #F3F4F6;
  color: #64748b;
  border:1px solid rgba(148,163,184,.35);
}

/* =========================
   BOTONES (IGUAL QUE BILLETERA)
========================= */
.btn-ev-orange{
  background-image: linear-gradient(180deg, #FF9B3A, #FF7A1A);
  border: none;
  color: #ffffff;
  font-weight: 700;
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
  font-weight: 600;
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

/* =========================
   ACCIONES TABLA (ALINEACIÓN PERFECTA)
   - 3 botones con ancho fijo (evita desalineado en "Publicado")
========================= */
.ev-actions{
  display: grid;
  grid-template-columns: repeat(3, 104px);
  gap: 10px;
  justify-content: center;
  align-items: center;
}

/* Chip base */
.ev-chip{
  width: 104px;
  justify-content: center;
  text-align: center;

  border-radius: 999px;
  padding: 0.40rem 0.95rem;
  font-weight: 800;
  font-size: .86rem;
  line-height: 1;

  background:#fff;
  border:1px solid rgba(148,163,184,.45);
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);

  transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease, border-color .15s ease;
  user-select:none;
  white-space:nowrap;

  display: inline-flex;
  align-items:center;
}

.ev-chip:hover{
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
}

.ev-chip:focus-visible{
  outline: 0;
  box-shadow: 0 0 0 .2rem rgba(25,135,84,.18), 0 10px 18px rgba(15, 23, 42, 0.08);
}

/* Deshabilitado: mantiene tamaño, más legible */
.ev-chip:disabled,
.ev-chip[disabled]{
  opacity: .65;
  box-shadow: none;
  transform: none;
  cursor: not-allowed;
}

.ev-chip-green{
  border-color: rgba(15,89,47,.55);
  color: var(--ev-verde-oscuro);
}
.ev-chip-green:hover{
  background: rgba(230,244,236,.8);
  border-color: rgba(15,89,47,.75);
}

.ev-chip-red{
  border-color: rgba(220,38,38,.55);
  color: var(--ev-rojo);
}
.ev-chip-red:hover{
  background: rgba(220,38,38,.06);
  border-color: rgba(220,38,38,.75);
}

.ev-chip-amber{
  border-color: rgba(255,122,26,.65);
  color: var(--ev-naranja);
}
.ev-chip-amber:hover{
  background: rgba(255,122,26,.08);
  border-color: rgba(255,122,26,.85);
}

/* Publicado (apagado, pero consistente) */
.ev-chip-amber[disabled],
.ev-chip[data-status="publicado"]{
  background: rgba(255,122,26,.06);
  border-color: rgba(255,122,26,.22);
  color: rgba(255,122,26,.55);
}

/* =========================
   SECCIONES DENTRO DE MODALES
========================= */
.ev-section{
  border: 1px solid rgba(229,231,235,.9);
  border-radius: 16px;
  background: #fff;
  box-shadow: var(--ev-shadow-soft);
  padding: 16px;
}

.ev-section-title{
  font-weight: 800;
  color: var(--ev-texto);
  margin-bottom: 4px;
}
.ev-section-subtitle{
  color: var(--ev-texto-suave);
  font-size: .9rem;
}

/* Dropzone */
.ev-dropzone{
  border: 2px dashed rgba(148,163,184,.55);
  border-radius: 16px;
  padding: 18px 14px;
  text-align:center;
  cursor:pointer;
  background: #F9FAFB;
  transition: border-color .15s ease, background-color .15s ease, transform .15s ease;
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
  position:absolute;
  top: -8px;
  right: -8px;
  width: 28px;
  height: 28px;
  border-radius: 999px;
  border: 0;
  background: var(--ev-rojo);
  color:#fff;
  font-weight: 900;
  display:flex;
  align-items:center;
  justify-content:center;
  box-shadow: 0 10px 20px rgba(220,38,38,.35);
}
.ev-tile-add{
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  background:#F9FAFB;
  border-style:dashed;
  cursor:pointer;
}
.ev-tile-add .ico{ font-size: 1.2rem; color: var(--ev-verde); }
.ev-tile-add .t1{ font-weight:800; color: var(--ev-texto); font-size:.88rem; }
.ev-tile-add .t2{ color: var(--ev-texto-suave); font-size:.8rem; }

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

/* =========================
   MODALES EV – CENTRADOS + ESTABLES
========================= */
.ev-modal{
  --bs-modal-margin: 12px;
}

/* Centrado horizontal */
.ev-modal .modal-dialog{
  width: calc(100% - (var(--bs-modal-margin) * 2));
  margin: var(--bs-modal-margin) auto;
}

/* Tamaño XL */
.ev-modal-xl{ max-width: 980px; }
@media (min-width: 992px){
  .ev-modal-xl{ max-width: 1040px; }
}

/* Contenido */
.ev-modal-content{
  border-radius: var(--ev-radius-modal);
  overflow: hidden;
  border: 0;
  box-shadow: 0 22px 60px rgba(15, 23, 42, 0.35);
}

/* Header (con degradado) */
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

/* Body */
.ev-modal-body{
  padding: 22px 26px;
  background:#fff;
}

/* Footer */
.ev-modal-footer{
  padding: 14px 26px 20px 26px;
  background:#fff;
  border-top: 1px solid rgba(229, 231, 235, 0.9);
  display:flex;
  justify-content:flex-end;
  gap:.75rem;
}

/* “Sin rebote”: modal content en flex, body con scroll */
.ev-modal-flex{
  display:flex;
  flex-direction:column;
  min-height: 0;
}
.ev-modal-body-scroll{
  overflow:auto;
  -webkit-overflow-scrolling: touch;
  min-height: 0;
}

/* Altura máxima estable */
.ev-modal .modal-dialog{
  max-height: calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2));
}
.ev-modal .modal-content{
  max-height: calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2));
}
.ev-modal .ev-modal-body-scroll{
  max-height: calc(var(--ev-vh, 1vh) * 100 - (var(--bs-modal-margin) * 2) - 64px - 72px);
}

/* Forms */
.ev-modal-body .form-label{
  font-weight: 700;
  color: var(--ev-texto);
}
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

/* =========================
   RESPONSIVE
========================= */
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

  /* Acciones en móvil: 2 columnas ordenadas, misma altura visual */
  .ev-actions{
    grid-template-columns: repeat(2, 1fr);
    justify-content: end;
  }
  .ev-chip{ width: 100%; }
}
</style>
