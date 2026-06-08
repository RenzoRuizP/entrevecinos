<style>
/* ============================================================
   ENTRE VECINOS - COMUNIDAD
   Gestión institucional · listado + modal premium de publicación
============================================================ */
:root{
  --ev-com-verde-oscuro:#0F592F;
  --ev-com-verde-mid:#0E7A43;
  --ev-com-verde:#16A34A;
  --ev-com-verde-claro:#ECFDF3;
  --ev-com-naranja:#EA7C12;
  --ev-com-naranja-claro:#FFF7ED;
  --ev-com-rojo:#DC2626;
  --ev-com-amarillo:#B45309;
  --ev-com-azul:#2563EB;
  --ev-com-texto:#111827;
  --ev-com-suave:#6B7280;
  --ev-com-borde:#E5E7EB;
  --ev-com-fondo:#F8FAFC;
  --ev-com-shadow:0 14px 34px rgba(15,23,42,.065);
}

.ev-com-shell{
  padding:18px 18px 30px;
  color:var(--ev-com-texto);
}

.ev-com-hero{
  position:relative;
  overflow:hidden;
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:18px;
  flex-wrap:wrap;
  padding:23px 24px 21px;
  border-radius:24px;
  border:1px solid rgba(15,89,47,.10);
  background:
    radial-gradient(circle at 92% 12%, rgba(22,163,74,.13), transparent 32%),
    radial-gradient(circle at 6% 92%, rgba(234,124,18,.10), transparent 30%),
    #fff;
  box-shadow:var(--ev-com-shadow);
}

.ev-com-kicker{
  display:inline-flex;
  align-items:center;
  gap:8px;
  color:var(--ev-com-verde-oscuro);
  background:#EAF8EF;
  border:1px solid rgba(22,163,74,.14);
  border-radius:999px;
  padding:7px 12px;
  font-size:.76rem;
  font-weight:900;
  letter-spacing:.07em;
  text-transform:uppercase;
  margin-bottom:12px;
}

.ev-com-title{
  color:var(--ev-com-verde-oscuro);
  font-size:clamp(1.65rem,2.5vw,2.16rem);
  font-weight:900;
  letter-spacing:-.035em;
  line-height:1.08;
  margin:0 0 8px;
}

.ev-com-subtitle{
  max-width:670px;
  margin:0;
  color:var(--ev-com-suave);
  font-size:.96rem;
  line-height:1.58;
}

.ev-com-pill{
  display:inline-flex;
  align-items:center;
  gap:9px;
  padding:12px 15px;
  border-radius:16px;
  border:1px solid rgba(22,163,74,.16);
  background:#F0FDF4;
  color:#166534;
  font-size:.88rem;
  font-weight:850;
  white-space:nowrap;
}

.ev-com-stats{
  display:grid;
  grid-template-columns:repeat(4,minmax(0,1fr));
  gap:13px;
  margin-top:15px;
}

.ev-com-stat{
  display:flex;
  align-items:center;
  gap:13px;
  min-height:88px;
  padding:15px 16px;
  border-radius:19px;
  border:1px solid var(--ev-com-borde);
  background:#fff;
  box-shadow:0 9px 24px rgba(15,23,42,.045);
}

.ev-com-stat-icon{
  flex:0 0 47px;
  width:47px;
  height:47px;
  display:grid;
  place-items:center;
  border-radius:15px;
  background:var(--ev-com-verde-claro);
  color:var(--ev-com-verde-oscuro);
  font-size:1.16rem;
}

.ev-com-stat span{
  display:block;
  color:var(--ev-com-suave);
  font-size:.8rem;
  font-weight:750;
  margin-bottom:3px;
}

.ev-com-stat strong{
  display:block;
  color:var(--ev-com-verde-oscuro);
  font-size:1.46rem;
  font-weight:900;
  line-height:1;
}

.ev-com-list-card{
  margin-top:15px;
  padding:19px;
  border-radius:22px;
  border:1px solid var(--ev-com-borde);
  background:#fff;
  box-shadow:var(--ev-com-shadow);
}

.ev-com-list-head{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:14px;
  margin-bottom:16px;
}

.ev-com-list-head h2{
  color:var(--ev-com-verde-oscuro);
  margin:0 0 4px;
  font-size:1.17rem;
  font-weight:900;
}

.ev-com-list-head p{
  color:var(--ev-com-suave);
  margin:0;
  font-size:.88rem;
  line-height:1.45;
}

.ev-com-btn{
  min-height:42px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  padding:10px 16px;
  border-radius:13px;
  border:1px solid transparent;
  font-size:.87rem;
  font-weight:850;
  cursor:pointer;
  transition:transform .17s ease, box-shadow .17s ease, filter .17s ease, background .17s ease;
}

.ev-com-btn:hover:not(:disabled){ transform:translateY(-1px); }
.ev-com-btn:disabled{ opacity:.58; cursor:not-allowed; transform:none; box-shadow:none; }
.ev-com-btn-primary{
  color:#fff;
  background:linear-gradient(135deg,var(--ev-com-naranja),#F59E0B);
  box-shadow:0 11px 22px rgba(234,124,18,.27);
}
.ev-com-btn-primary:hover:not(:disabled){
  box-shadow:0 15px 28px rgba(234,124,18,.36);
  filter:brightness(1.02);
}
.ev-com-btn-outline{
  color:var(--ev-com-verde-oscuro);
  border-color:rgba(15,89,47,.18);
  background:#fff;
}
.ev-com-btn-outline:hover:not(:disabled){
  background:#F0FDF4;
  border-color:rgba(22,163,74,.30);
}
.ev-com-btn-light{
  color:#4B5563;
  border-color:#E5E7EB;
  background:#F9FAFB;
}
.ev-com-btn-light:hover:not(:disabled){ background:#F3F4F6; }
.ev-com-btn-danger{
  color:#B91C1C;
  border-color:#FECACA;
  background:#FEF2F2;
}
.ev-com-btn-danger:hover:not(:disabled){ background:#FEE2E2; }

/* Filtros y listado */
.ev-com-filters{
  display:grid;
  grid-template-columns:minmax(260px,1fr) 205px 180px auto;
  gap:10px;
  margin-bottom:15px;
}
.ev-com-search{ position:relative; }
.ev-com-search i{
  position:absolute;
  top:50%;
  left:13px;
  transform:translateY(-50%);
  color:#9CA3AF;
}
.ev-com-search input,
.ev-com-filters select{
  width:100%;
  min-height:43px;
  border:1px solid #E5E7EB;
  border-radius:13px;
  background:#fff;
  padding:10px 12px;
  font-size:.87rem;
  outline:0;
}
.ev-com-search input{ padding-left:37px; }
.ev-com-search input:focus,
.ev-com-filters select:focus{
  border-color:var(--ev-com-verde);
  box-shadow:0 0 0 3px rgba(22,163,74,.12);
}
.ev-com-table-wrap{
  border:1px solid #E5E7EB;
  border-radius:17px;
  overflow:auto;
}
.ev-com-table{
  width:100%;
  min-width:780px;
  border-collapse:separate;
  border-spacing:0;
  font-size:.86rem;
}
.ev-com-table thead th{
  text-align:left;
  padding:13px 14px;
  background:#F8FAFC;
  color:#374151;
  font-weight:900;
  border-bottom:1px solid #E5E7EB;
  white-space:nowrap;
}
.ev-com-table tbody td{
  padding:13px 14px;
  vertical-align:middle;
  border-bottom:1px solid #EEF2F7;
  color:#374151;
  background:#fff;
}
.ev-com-table tbody tr:last-child td{ border-bottom:0; }
.ev-com-table tbody tr:hover td{ background:#FCFDFD; }
.ev-com-row-title{
  color:var(--ev-com-texto);
  font-weight:850;
  max-width:390px;
  line-height:1.35;
  margin-top:6px;
}
.ev-com-row-sub{
  color:var(--ev-com-suave);
  margin-top:3px;
  font-size:.76rem;
  max-width:390px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.ev-com-badge{
  display:inline-flex;
  align-items:center;
  gap:5px;
  padding:5px 9px;
  border-radius:999px;
  font-weight:850;
  font-size:.73rem;
  white-space:nowrap;
}
.ev-com-badge--comunicado{ background:#ECFDF3; color:#166534; }
.ev-com-badge--noticia{ background:#EFF6FF; color:#1D4ED8; }
.ev-com-badge--evento{ background:#F5F3FF; color:#6D28D9; }
.ev-com-badge--borrador{ background:#F3F4F6; color:#4B5563; }
.ev-com-badge--publicado{ background:#ECFDF3; color:#166534; }
.ev-com-badge--inactivo{ background:#FFF7ED; color:#9A3412; }
.ev-com-badge--ocultado_moderacion{ background:#FEF2F2; color:#B91C1C; }
.ev-com-badge--normal{ background:#F3F4F6; color:#4B5563; }
.ev-com-badge--importante{ background:#FFF7ED; color:#B45309; }
.ev-com-badge--urgente{ background:#FEF2F2; color:#B91C1C; }
.ev-com-row-actions{
  display:flex;
  justify-content:flex-end;
  gap:6px;
  flex-wrap:wrap;
}
.ev-com-mini-btn{
  border:1px solid #E5E7EB;
  background:#fff;
  color:#374151;
  border-radius:10px;
  padding:7px 9px;
  font-size:.76rem;
  font-weight:800;
  transition:background .16s ease,border-color .16s ease,color .16s ease;
}
.ev-com-mini-btn:hover{
  background:#F0FDF4;
  border-color:#BBF7D0;
  color:var(--ev-com-verde-oscuro);
}
.ev-com-mini-btn.publish{ color:#166534; }
.ev-com-mini-btn.off{ color:#B45309; }
.ev-com-actions-th{ text-align:right !important; }
.ev-com-empty,
.ev-com-loading{
  min-height:150px;
  display:flex;
  flex-direction:column;
  gap:8px;
  align-items:center;
  justify-content:center;
  color:var(--ev-com-suave);
  font-weight:750;
}
.ev-com-empty i{ color:rgba(15,89,47,.34); font-size:1.7rem; }
.ev-com-loading{ flex-direction:row; min-height:90px; }
.ev-com-loading span{
  width:20px;
  height:20px;
  border-radius:50%;
  border:3px solid rgba(22,163,74,.18);
  border-top-color:var(--ev-com-verde);
  animation:evComSpin .75s linear infinite;
}
@keyframes evComSpin{ to{ transform:rotate(360deg); } }
.ev-com-table-footer{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:10px;
  margin-top:14px;
  color:var(--ev-com-suave);
  font-size:.82rem;
  font-weight:750;
}
.ev-com-pager{ display:flex; align-items:center; gap:9px; }
.ev-com-pager button{
  width:36px;
  height:36px;
  border-radius:11px;
  border:1px solid #E5E7EB;
  background:#fff;
  color:var(--ev-com-verde-oscuro);
}
.ev-com-pager button:disabled{ opacity:.45; }
.ev-com-pager span{
  border:1px solid #E5E7EB;
  padding:7px 12px;
  border-radius:10px;
  color:var(--ev-com-verde-oscuro);
  background:#fff;
}

/* ============================================================
   MODAL PUBLICACIÓN · ESTÁNDAR VISUAL EV
   - Header verde compacto como Mis publicaciones.
   - Editor a la izquierda + vista previa a la derecha.
   - Backdrop estático y cierre controlado desde JS.
============================================================ */
.ev-com-editor-modal .modal-dialog{
  max-width:min(1240px, calc(100vw - 28px));
  border:0;
  outline:0;
  background:transparent;
}

.ev-com-modal{
  border:0;
  overflow:hidden;
  border-radius:22px;
  background:#fff;
  box-shadow:0 30px 76px rgba(15,23,42,.27), 0 8px 24px rgba(15,23,42,.12);
}

/*
   Patrón validado del modal EV de Mis publicaciones:
   el contenedor exterior utiliza el mismo degradado del header.
   El cuerpo blanco nace recién debajo del encabezado; así no se filtra
   ningún arco blanco en las esquinas superiores redondeadas.
*/
.ev-com-publish-modal{
  --ev-com-modal-radius:22px;
  --ev-com-publish-head-bg:linear-gradient(140deg, var(--ev-com-verde-oscuro) 0%, var(--ev-com-verde-mid) 55%, var(--ev-com-verde) 100%);

  --bs-modal-bg:transparent;
  --bs-modal-border-width:0;
  --bs-modal-border-color:transparent;
  --bs-modal-border-radius:var(--ev-com-modal-radius);

  height:min(92vh, 900px);
  display:flex;
  flex-direction:column;
  position:relative;
  border:0 !important;
  outline:0;
  padding:0;
  border-radius:var(--ev-com-modal-radius);
  overflow:hidden;
  background:var(--ev-com-publish-head-bg);
  box-shadow:0 30px 76px rgba(15,23,42,.27), 0 8px 24px rgba(15,23,42,.12);
}

#modalPublicacionCom .modal-content.ev-com-publish-modal{
  border:0 !important;
  outline:0;
  background:var(--ev-com-publish-head-bg);
}

.ev-com-publish-head{
  flex:0 0 auto;
  min-height:58px;
  padding:13px 19px !important;
  margin:0;
  border:0 !important;
  outline:0;
  border-radius:var(--ev-com-modal-radius) var(--ev-com-modal-radius) 0 0;
  color:#fff;
  display:flex;
  align-items:center;
  justify-content:space-between;
  background:var(--ev-com-publish-head-bg);
  box-shadow:none;
}

.ev-com-publish-head::before,
.ev-com-publish-head::after,
.ev-com-publish-modal::before,
.ev-com-publish-modal::after{
  content:none !important;
  display:none !important;
}
.ev-com-publish-head .modal-title{
  display:inline-flex;
  align-items:center;
  gap:9px;
  margin:0;
  color:#fff;
  font-size:1.12rem;
  font-weight:900;
  letter-spacing:-.015em;
}
.ev-com-publish-head .modal-title i{ font-size:1.02rem; }
.ev-com-modal-close{
  width:38px;
  height:38px;
  display:grid;
  place-items:center;
  border:0;
  border-radius:12px;
  background:transparent;
  color:#fff;
  font-size:1.14rem;
  opacity:.92;
  transition:background .17s ease, opacity .17s ease, transform .17s ease;
}
.ev-com-modal-close:hover{
  background:rgba(255,255,255,.14);
  opacity:1;
  transform:scale(1.03);
}
.ev-com-modal-close:focus-visible{
  outline:3px solid rgba(255,255,255,.32);
  outline-offset:1px;
}
.ev-com-publish-form{
  flex:1 1 auto;
  min-height:0;
  display:flex;
  flex-direction:column;
  overflow:hidden;
  background:#fff;
  border-radius:0 0 var(--ev-com-modal-radius) var(--ev-com-modal-radius);
}
.ev-com-publish-body{
  flex:1 1 auto;
  min-height:0;
  overflow:hidden;
  padding:14px !important;
  background:#fff;
}
.ev-com-editor-layout{
  height:100%;
  min-height:0;
  display:grid;
  grid-template-columns:minmax(485px, 1.12fr) minmax(330px, .82fr);
  gap:14px;
}
.ev-com-editor-scroll{
  min-height:0;
  overflow-y:auto;
  padding:0 5px 1px 0;
  scrollbar-gutter:stable;
  scrollbar-width:thin;
  scrollbar-color:#D1D5DB transparent;
}
.ev-com-editor-scroll::-webkit-scrollbar{ width:6px; }
.ev-com-editor-scroll::-webkit-scrollbar-thumb{ background:#D1D5DB; border-radius:99px; }
.ev-com-step-card{
  border:1px solid #E2E8F0;
  border-radius:18px;
  padding:13px 14px 14px;
  background:#fff;
  margin-bottom:12px;
  box-shadow:0 5px 14px rgba(15,23,42,.028);
}
.ev-com-step-card:last-child{ margin-bottom:0; }
.ev-com-step-tag{
  display:inline-flex;
  align-items:center;
  width:max-content;
  padding:5px 10px;
  margin:0 0 8px;
  border-radius:999px;
  border:1px solid rgba(22,163,74,.17);
  background:#EAF8EF;
  color:#166534;
  font-size:.68rem;
  font-weight:900;
  letter-spacing:.05em;
  text-transform:uppercase;
}
.ev-com-step-card h3{
  margin:0 0 4px;
  color:var(--ev-com-texto);
  font-size:1rem;
  font-weight:900;
  letter-spacing:-.015em;
}
.ev-com-step-card > p{
  margin:0 0 12px;
  color:var(--ev-com-suave);
  font-size:.82rem;
  line-height:1.42;
}
.ev-com-type-cards{
  display:grid;
  grid-template-columns:repeat(3, minmax(0,1fr));
  gap:9px;
}
.ev-com-type-option{
  position:relative;
  min-height:84px;
  display:flex;
  align-items:center;
  gap:9px;
  padding:11px 11px;
  border-radius:15px;
  border:1.5px solid #E5E7EB;
  background:#fff;
  text-align:left;
  color:var(--ev-com-texto);
  transition:border-color .18s ease, background .18s ease, box-shadow .18s ease;
}
.ev-com-type-option:hover{
  border-color:#BBF7D0;
  background:#F7FEF9;
}
.ev-com-type-option.is-selected{
  border-color:var(--ev-com-naranja);
  background:linear-gradient(180deg,#FFF8F1 0%, #FFF3E4 100%);
  box-shadow:0 9px 22px rgba(234,124,18,.10);
}
.ev-com-type-icon{
  flex:0 0 38px;
  width:38px;
  height:38px;
  border-radius:12px;
  display:grid;
  place-items:center;
  color:var(--ev-com-verde-oscuro);
  border:1px solid #DCFCE7;
  background:#F0FDF4;
  font-size:1rem;
}
.ev-com-type-option.is-selected .ev-com-type-icon{
  color:var(--ev-com-naranja);
  border-color:#FED7AA;
  background:#fff;
}
.ev-com-type-option strong{
  display:block;
  padding-right:12px;
  font-size:.84rem;
  line-height:1.18;
  font-weight:900;
}
.ev-com-type-option small{
  display:block;
  padding-right:8px;
  margin-top:3px;
  color:var(--ev-com-suave);
  font-size:.7rem;
  line-height:1.28;
}
.ev-com-type-check{
  position:absolute;
  top:9px;
  right:9px;
  color:var(--ev-com-naranja);
  font-size:.92rem;
  opacity:0;
}
.ev-com-type-option.is-selected .ev-com-type-check{ opacity:1; }
.ev-com-file-input{
  position:absolute !important;
  width:1px !important;
  height:1px !important;
  padding:0 !important;
  margin:-1px !important;
  overflow:hidden !important;
  clip:rect(0,0,0,0) !important;
  white-space:nowrap !important;
  border:0 !important;
}
.ev-com-dropzone{
  min-height:98px;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:4px;
  padding:13px;
  border:1.5px dashed #B8DCC9;
  border-radius:15px;
  background:#FBFEFC;
  text-align:center;
  cursor:pointer;
  transition:border-color .17s ease, background .17s ease, box-shadow .17s ease;
}
.ev-com-dropzone[hidden]{ display:none !important; }
.ev-com-dropzone:hover,
.ev-com-dropzone.is-dragging{
  border-color:var(--ev-com-verde);
  background:#F0FDF4;
  box-shadow:0 0 0 3px rgba(22,163,74,.08);
}
.ev-com-dropzone i{
  color:var(--ev-com-verde);
  font-size:1.3rem;
}
.ev-com-dropzone strong{
  color:var(--ev-com-verde-oscuro);
  font-size:.85rem;
  font-weight:900;
}
.ev-com-dropzone small{ color:var(--ev-com-suave); font-size:.74rem; }
.ev-com-upload-selected{
  margin-top:10px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:9px;
  border:1px solid #D1FAE5;
  border-radius:13px;
  background:#F7FEF9;
}
.ev-com-upload-selected[hidden]{ display:none; }
.ev-com-upload-selected img{
  flex:0 0 66px;
  width:66px;
  height:54px;
  object-fit:contain;
  object-position:center;
  padding:3px;
  border-radius:10px;
  border:1px solid #E5E7EB;
  background:#fff;
}
.ev-com-upload-selected div{ flex:1 1 auto; min-width:0; }
.ev-com-upload-selected strong{
  display:block;
  color:var(--ev-com-texto);
  font-size:.79rem;
  font-weight:900;
}
.ev-com-upload-selected small{
  display:block;
  margin-top:2px;
  color:var(--ev-com-suave);
  font-size:.7rem;
}
.ev-com-upload-name{
  display:block;
  max-width:100%;
  margin-top:3px;
  color:#94A3B8;
  font-size:.67rem;
  line-height:1.25;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.ev-com-file-change{
  border:1px solid #BBF7D0;
  border-radius:9px;
  background:#fff;
  color:var(--ev-com-verde-oscuro);
  padding:7px 9px;
  font-size:.72rem;
  font-weight:850;
}
.ev-com-field{
  display:flex;
  flex-direction:column;
  gap:6px;
  margin-top:12px;
}
.ev-com-field:first-of-type{ margin-top:10px; }
.ev-com-field-full{ grid-column:1 / -1; }
.ev-com-field label{
  color:#374151;
  font-size:.81rem;
  font-weight:850;
}
.ev-com-field label span{ color:var(--ev-com-naranja); }
.ev-com-field input:not([type="checkbox"]):not([type="file"]),
.ev-com-field select,
.ev-com-field textarea{
  width:100%;
  border:1px solid #D1D5DB;
  border-radius:12px;
  background:#fff;
  color:var(--ev-com-texto);
  font-size:.87rem;
  padding:10px 11px;
  outline:0;
  transition:border-color .17s ease, box-shadow .17s ease;
}
.ev-com-field textarea{ resize:vertical; min-height:54px; }
.ev-com-field input:focus,
.ev-com-field select:focus,
.ev-com-field textarea:focus{
  border-color:var(--ev-com-verde);
  box-shadow:0 0 0 3px rgba(22,163,74,.12);
}
.ev-com-field small{
  color:var(--ev-com-suave);
  font-size:.7rem;
  line-height:1.35;
}
.ev-com-form-grid{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:0 12px;
}
.ev-com-settings-grid .ev-com-field{ margin-top:4px; }
.ev-com-highlight-switch{
  position:relative;
  display:flex;
  align-items:flex-start;
  gap:11px;
  margin-top:13px;
  padding:11px 12px;
  border:1px solid #D1FAE5;
  border-radius:14px;
  background:#F7FEF9;
  cursor:pointer;
  user-select:none;
  transition:
    border-color .18s ease,
    background .18s ease,
    box-shadow .18s ease;
}

/*
   Input accesible, sin desplazar el panel al recibir foco.
   El control conserva interacción nativa por teclado y por clic en el label.
*/
.ev-com-highlight-switch input{
  position:absolute;
  width:1px;
  height:1px;
  padding:0;
  margin:-1px;
  overflow:hidden;
  white-space:nowrap;
  border:0;
  clip:rect(0, 0, 0, 0);
  clip-path:inset(50%);
}

.ev-com-highlight-switch:hover{
  border-color:#BBF7D0;
  background:#F0FDF4;
}

.ev-com-highlight-switch.is-active,
.ev-com-highlight-switch:has(input:checked){
  border-color:rgba(22,163,74,.34);
  background:linear-gradient(180deg, #F0FDF4 0%, #E9F9EF 100%);
  box-shadow:0 8px 18px rgba(22,163,74,.07);
}

.ev-com-switch-control{
  position:relative;
  flex:0 0 40px;
  width:40px;
  height:23px;
  margin-top:1px;
  border-radius:999px;
  background:#D1D5DB;
  box-shadow:inset 0 1px 2px rgba(15,23,42,.06);
  transition:background .18s ease, box-shadow .18s ease;
}
.ev-com-switch-control::after{
  content:"";
  position:absolute;
  top:3px;
  left:3px;
  width:17px;
  height:17px;
  border-radius:50%;
  background:#fff;
  box-shadow:0 1px 4px rgba(15,23,42,.16);
  transition:transform .18s ease;
}
.ev-com-highlight-switch input:checked + .ev-com-switch-control,
.ev-com-highlight-switch.is-active .ev-com-switch-control{
  background:var(--ev-com-verde);
  box-shadow:inset 0 1px 2px rgba(15,89,47,.16);
}
.ev-com-highlight-switch input:checked + .ev-com-switch-control::after,
.ev-com-highlight-switch.is-active .ev-com-switch-control::after{
  transform:translateX(17px);
}
.ev-com-highlight-switch input:focus-visible + .ev-com-switch-control{
  box-shadow:0 0 0 3px rgba(22,163,74,.22);
}
.ev-com-highlight-switch strong{
  display:block;
  color:var(--ev-com-verde-oscuro);
  font-size:.83rem;
  font-weight:900;
}
.ev-com-highlight-switch small{
  display:block;
  margin-top:2px;
  color:var(--ev-com-suave);
  font-size:.7rem;
  line-height:1.35;
}
.ev-com-event-fields{
  margin:0 0 12px;
  padding:13px 14px 14px;
}
.ev-com-event-fields legend{
  float:none;
  width:100%;
  display:flex;
  align-items:center;
  gap:10px;
  padding:0;
  margin:0 0 4px;
  color:var(--ev-com-texto);
}
.ev-com-event-fields legend .ev-com-step-tag{ margin:0; }
.ev-com-event-fields legend strong{ font-size:1rem; font-weight:900; }
.ev-com-event-fields > p{ margin:0 0 10px; }
.ev-com-live-preview{
  align-self:start;
  height:fit-content;
  max-height:100%;
  min-height:0;
  overflow-y:auto;
  padding:13px;
  border-radius:18px;
  border:1px solid #E2E8F0;
  background:linear-gradient(180deg,#FCFEFD 0%, #fff 100%);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.85);
  scrollbar-width:thin;
}
.ev-com-live-head{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:10px;
  padding-bottom:12px;
  border-bottom:1px solid #E5E7EB;
}
.ev-com-live-head small{
  display:block;
  text-transform:uppercase;
  letter-spacing:.08em;
  color:#9CA3AF;
  font-size:.65rem;
  font-weight:900;
}
.ev-com-live-head h3{
  margin:3px 0 0;
  color:var(--ev-com-verde-oscuro);
  font-size:1rem;
  font-weight:900;
}
.ev-com-live-type{
  display:inline-flex;
  align-items:center;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid #CDEFD9;
  color:#166534;
  background:#EAF8EF;
  font-size:.76rem;
  font-weight:900;
}
.ev-com-live-image{
  min-height:162px;
  margin-top:12px;
  padding:10px;
  border:1px dashed #C7DCD1;
  border-radius:16px;
  background:linear-gradient(180deg,#FCFEFD 0%, #F7FCF9 100%);
  display:grid;
  place-items:center;
  overflow:hidden;
}
.ev-com-live-image.has-image{
  min-height:0;
  padding:8px;
  background:#F7FCF9;
}
.ev-com-live-image img{
  display:block;
  width:100%;
  height:auto;
  max-height:205px;
  object-fit:contain;
  object-position:center;
  border-radius:12px;
  background:#F7FCF9;
}
.ev-com-live-image img[hidden],
#vistaImagenEmptyCom[hidden]{ display:none !important; }
#vistaImagenEmptyCom{
  display:flex;
  align-items:center;
  gap:12px;
  width:100%;
  padding:16px 8px;
}
#vistaImagenEmptyCom i{
  flex:0 0 43px;
  width:43px;
  height:43px;
  display:grid;
  place-items:center;
  border-radius:13px;
  color:var(--ev-com-verde-oscuro);
  background:#EAF8EF;
  font-size:1.12rem;
}
#vistaImagenEmptyCom strong{
  display:block;
  color:var(--ev-com-verde-oscuro);
  font-size:.89rem;
  line-height:1.28;
}
#vistaImagenEmptyCom p{
  margin:3px 0 0;
  color:var(--ev-com-suave);
  font-size:.75rem;
  line-height:1.35;
}
.ev-com-live-card{
  margin-top:11px;
  padding:13px;
  border:1px solid #E5E7EB;
  border-radius:15px;
  background:#fff;
}
.ev-com-live-badges{
  min-height:25px;
  display:flex;
  flex-wrap:wrap;
  gap:7px;
  margin-bottom:8px;
}
.ev-com-live-priority,
.ev-com-live-featured{
  display:inline-flex;
  align-items:center;
  gap:5px;
  border-radius:999px;
  padding:5px 9px;
  font-size:.7rem;
  font-weight:900;
}
.ev-com-live-priority--normal{ background:#F3F4F6; color:#4B5563; }
.ev-com-live-priority--importante{ background:#FFF7ED; color:#B45309; border:1px solid rgba(234,124,18,.18); }
.ev-com-live-priority--urgente{ background:#FEF2F2; color:#B91C1C; border:1px solid rgba(220,38,38,.15); }
.ev-com-live-featured{ background:#ECFDF3; color:#166534; }
.ev-com-live-card h4{
  margin:0 0 7px;
  color:var(--ev-com-verde-oscuro);
  font-size:1.08rem;
  font-weight:900;
  line-height:1.3;
}
.ev-com-live-card > p{
  min-height:41px;
  margin:0 0 12px;
  color:var(--ev-com-suave);
  font-size:.82rem;
  line-height:1.48;
}
.ev-com-live-event{
  display:flex;
  align-items:flex-start;
  gap:7px;
  margin:0 0 11px;
  padding:9px 10px;
  border-radius:11px;
  background:#F5F3FF;
  color:#5B21B6;
  font-size:.75rem;
  font-weight:750;
}
.ev-com-live-community{
  display:flex;
  align-items:center;
  gap:7px;
  padding-top:10px;
  border-top:1px solid #F1F5F9;
  color:#166534;
  font-size:.76rem;
  font-weight:850;
}
.ev-com-live-note{
  display:flex;
  align-items:center;
  gap:8px;
  margin-top:11px;
  padding:10px 11px;
  border-radius:12px;
  border:1px solid #E5E7EB;
  color:var(--ev-com-suave);
  font-size:.76rem;
  font-weight:750;
}
.ev-com-live-note i{ color:var(--ev-com-verde-mid); }
.ev-com-publish-footer{
  flex:0 0 auto;
  justify-content:flex-end;
  flex-wrap:wrap;
  gap:10px;
  padding:12px 18px !important;
  border-top:1px solid #E5E7EB !important;
  background:#fff;
}
/* ============================================================
   MODAL HISTORIAL · ULTRA FINO PREMIUM EV
   Bitácora institucional de cambios: header contextual + timeline.
============================================================ */
#modalHistorialCom .modal-dialog{
  max-width:min(820px, calc(100vw - 30px));
}

#modalHistorialCom .ev-com-history-modal-content{
  --ev-history-radius:23px;
  --ev-history-head-bg:
    radial-gradient(circle at 88% 14%, rgba(255,255,255,.13), transparent 32%),
    linear-gradient(140deg, var(--ev-com-verde-oscuro) 0%, var(--ev-com-verde-mid) 52%, var(--ev-com-verde) 100%);

  border:0 !important;
  outline:0;
  overflow:hidden;
  border-radius:var(--ev-history-radius);
  background:var(--ev-history-head-bg);
  box-shadow:
    0 34px 82px rgba(15,23,42,.30),
    0 9px 25px rgba(15,23,42,.12);
}

.ev-com-history-header{
  flex:0 0 auto;
  padding:17px 19px 16px;
  color:#fff;
  background:var(--ev-history-head-bg);
}

.ev-com-history-header-top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:16px;
}

.ev-com-history-heading{
  min-width:0;
  display:flex;
  align-items:center;
  gap:12px;
}

.ev-com-history-heading-icon{
  flex:0 0 44px;
  width:44px;
  height:44px;
  display:grid;
  place-items:center;
  border-radius:14px;
  color:#fff;
  font-size:1.18rem;
  background:rgba(255,255,255,.12);
  border:1px solid rgba(255,255,255,.19);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.10);
}

.ev-com-history-heading-copy{
  min-width:0;
}

.ev-com-history-eyebrow{
  display:block;
  margin-bottom:3px;
  color:rgba(255,255,255,.76);
  font-size:.67rem;
  font-weight:900;
  line-height:1;
  text-transform:uppercase;
  letter-spacing:.105em;
}

#modalHistorialCom .modal-title{
  margin:0;
  color:#fff !important;
  font-size:1.18rem;
  font-weight:900;
  line-height:1.25;
  letter-spacing:-.025em;
}

.ev-com-history-heading-copy p{
  margin:3px 0 0;
  color:rgba(255,255,255,.82);
  font-size:.79rem;
  line-height:1.35;
  font-weight:550;
}

.ev-com-history-close{
  flex:none;
  width:40px;
  height:40px;
  display:grid;
  place-items:center;
  border:0;
  border-radius:13px;
  color:#fff;
  background:transparent;
  font-size:1.08rem;
  opacity:.93;
  transition:background .16s ease, transform .16s ease, opacity .16s ease;
}

.ev-com-history-close:hover{
  background:rgba(255,255,255,.15);
  transform:scale(1.035);
  opacity:1;
}

.ev-com-history-close:focus-visible{
  outline:3px solid rgba(255,255,255,.30);
  outline-offset:1px;
}

.ev-com-history-context{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:13px;
  margin-top:15px;
  padding:11px 12px;
  border:1px solid rgba(255,255,255,.18);
  border-radius:16px;
  background:rgba(255,255,255,.105);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.07);
}

.ev-com-history-context-main{
  min-width:0;
  display:flex;
  align-items:center;
  gap:11px;
}

.ev-com-history-type{
  flex:none;
  display:inline-flex;
  align-items:center;
  min-height:29px;
  padding:6px 11px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.18);
  background:rgba(255,255,255,.15);
  color:#fff;
  font-size:.72rem;
  font-weight:900;
  line-height:1;
}

.ev-com-history-publication{
  min-width:0;
  display:flex;
  flex-direction:column;
  gap:2px;
}

.ev-com-history-publication span{
  color:rgba(255,255,255,.69);
  font-size:.62rem;
  font-weight:900;
  line-height:1;
  text-transform:uppercase;
  letter-spacing:.095em;
}

.ev-com-history-publication strong{
  display:block;
  max-width:440px;
  overflow:hidden;
  white-space:nowrap;
  text-overflow:ellipsis;
  color:#fff;
  font-size:.86rem;
  font-weight:800;
  line-height:1.25;
}

.ev-com-history-current{
  flex:none;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-height:30px;
  padding:6px 11px;
  border-radius:999px;
  color:#fff;
  font-size:.73rem;
  font-weight:900;
  line-height:1;
  border:1px solid rgba(255,255,255,.19);
  background:rgba(255,255,255,.13);
}

.ev-com-history-current--publicado{
  background:rgba(236,253,243,.94);
  border-color:rgba(187,247,208,.72);
  color:#166534;
}
.ev-com-history-current--borrador{
  background:rgba(255,255,255,.14);
  color:#fff;
}
.ev-com-history-current--inactivo{
  background:rgba(255,247,237,.97);
  border-color:rgba(253,186,116,.50);
  color:#9A3412;
}
.ev-com-history-current--ocultado_moderacion{
  background:rgba(254,242,242,.97);
  border-color:rgba(252,165,165,.55);
  color:#B91C1C;
}

#modalHistorialCom .ev-com-history-body{
  max-height:min(62vh, 590px);
  overflow-y:auto;
  padding:15px 17px 18px !important;
  border-radius:0 0 var(--ev-history-radius) var(--ev-history-radius);
  background:#F8FAFC;
  scrollbar-width:thin;
  scrollbar-color:rgba(15,89,47,.24) transparent;
}

#modalHistorialCom .ev-com-history-body::-webkit-scrollbar{ width:6px; }
#modalHistorialCom .ev-com-history-body::-webkit-scrollbar-thumb{
  border-radius:999px;
  background:rgba(15,89,47,.23);
}

.ev-com-history-summary{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin:0 0 12px;
  padding:0 2px;
  color:var(--ev-com-suave);
}

.ev-com-history-summary > div{
  display:flex;
  align-items:baseline;
  gap:5px;
  color:#475569;
  font-size:.8rem;
  font-weight:750;
}

.ev-com-history-summary strong{
  color:var(--ev-com-verde-oscuro);
  font-size:1.06rem;
  font-weight:950;
  line-height:1;
}

.ev-com-history-summary small{
  color:#64748B;
  font-size:.73rem;
  font-weight:650;
}

.ev-com-history{
  position:relative;
  display:flex;
  flex-direction:column;
  gap:12px;
  padding:0;
}

.ev-com-history::before{
  content:"";
  position:absolute;
  left:34px;
  top:26px;
  bottom:26px;
  width:2px;
  border-radius:999px;
  background:linear-gradient(180deg, rgba(22,163,74,.26), rgba(148,163,184,.18));
}

.ev-com-history-item{
  --ev-history-accent:var(--ev-com-verde);
  --ev-history-soft:#ECFDF3;

  position:relative;
  z-index:1;
  display:grid;
  grid-template-columns:45px minmax(0, 1fr);
  gap:13px;
  padding:13px 14px 13px 12px;
  border:1px solid #E2E8F0;
  border-radius:18px;
  background:#fff;
  box-shadow:0 7px 19px rgba(15,23,42,.035);
  transition:border-color .17s ease, box-shadow .17s ease, transform .17s ease;
}

.ev-com-history-item.is-latest{
  border-color:rgba(22,163,74,.28);
  box-shadow:
    0 9px 22px rgba(15,23,42,.045),
    inset 0 0 0 1px rgba(22,163,74,.045);
}

.ev-com-history-item:hover{
  border-color:rgba(22,163,74,.23);
  box-shadow:0 12px 27px rgba(15,23,42,.065);
  transform:translateY(-1px);
}

.ev-com-history-item--creacion{
  --ev-history-accent:#16A34A;
  --ev-history-soft:#ECFDF3;
}
.ev-com-history-item--publicacion,
.ev-com-history-item--reactivacion{
  --ev-history-accent:#0E7A43;
  --ev-history-soft:#E9F8F0;
}
.ev-com-history-item--edicion{
  --ev-history-accent:#2563EB;
  --ev-history-soft:#EFF6FF;
}
.ev-com-history-item--desactivacion{
  --ev-history-accent:#EA7C12;
  --ev-history-soft:#FFF7ED;
}
.ev-com-history-item--ocultamiento_moderacion{
  --ev-history-accent:#DC2626;
  --ev-history-soft:#FEF2F2;
}

.ev-com-history-icon{
  width:45px;
  height:45px;
  display:grid;
  place-items:center;
  border-radius:15px;
  color:var(--ev-history-accent);
  font-size:1.08rem;
  background:var(--ev-history-soft);
  border:1px solid rgba(15,89,47,.11);
  box-shadow:0 0 0 5px #fff;
}

.ev-com-history-content{ min-width:0; }

.ev-com-history-top{
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:7px;
}

.ev-com-history-action{
  display:inline-flex;
  align-items:center;
  min-height:25px;
  padding:4px 10px;
  border-radius:999px;
  color:var(--ev-history-accent);
  background:var(--ev-history-soft);
  font-size:.77rem;
  font-weight:900;
  line-height:1;
}

.ev-com-history-latest{
  display:inline-flex;
  align-items:center;
  gap:4px;
  min-height:25px;
  padding:4px 9px;
  border-radius:999px;
  color:#166534;
  background:#F0FDF4;
  border:1px solid #BBF7D0;
  font-size:.68rem;
  font-weight:900;
  line-height:1;
  text-transform:uppercase;
  letter-spacing:.045em;
}

.ev-com-history-description{
  margin:7px 0 0;
  color:var(--ev-com-texto);
  font-size:.88rem;
  font-weight:820;
  line-height:1.4;
}

.ev-com-history-meta{
  margin-top:6px;
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:7px;
  color:#64748B;
  font-size:.77rem;
  font-weight:650;
}

.ev-com-history-meta i{ color:var(--ev-com-verde-mid); }
.ev-com-history-meta-divider{ color:#CBD5E1; }

.ev-com-history-change{
  margin-top:10px;
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:7px;
  padding:8px 10px;
  border-radius:11px;
  color:#475569;
  background:#F8FAFC;
  border:1px solid #E7EDF3;
  font-size:.77rem;
  font-weight:730;
}

.ev-com-history-change i{ color:var(--ev-history-accent); }

.ev-com-history-state{
  display:inline-flex;
  align-items:center;
  min-height:23px;
  padding:4px 9px;
  border-radius:999px;
  color:var(--ev-com-verde-oscuro);
  background:#EAF8F0;
  font-size:.74rem;
  font-weight:900;
}

.ev-com-history-arrow{ color:#94A3B8 !important; }

.ev-com-history-reason{
  margin:9px 0 0;
  padding:9px 10px;
  border-left:3px solid var(--ev-history-accent);
  border-radius:0 10px 10px 0;
  color:var(--ev-com-suave);
  background:#FAFCFD;
  font-size:.78rem;
  line-height:1.44;
}
.ev-com-history-reason strong{
  color:var(--ev-com-texto);
  font-weight:850;
}

.ev-com-history .ev-com-empty{
  min-height:184px;
  border:1px dashed rgba(22,163,74,.24);
  border-radius:17px;
  background:#fff;
}

@media (max-width:575.98px){
  #modalHistorialCom .modal-dialog{
    max-width:none;
    margin:10px;
  }
  .ev-com-history-header{
    padding:14px 13px 13px;
  }
  .ev-com-history-heading{
    gap:9px;
  }
  .ev-com-history-heading-icon{
    width:39px;
    height:39px;
    border-radius:12px;
  }
  #modalHistorialCom .modal-title{
    font-size:1.04rem;
  }
  .ev-com-history-heading-copy p{
    display:none;
  }
  .ev-com-history-context{
    align-items:flex-start;
    flex-direction:column;
    gap:9px;
    margin-top:12px;
  }
  .ev-com-history-publication strong{
    max-width:calc(100vw - 115px);
  }
  #modalHistorialCom .ev-com-history-body{
    padding:12px !important;
    max-height:min(68vh, 590px);
  }
  .ev-com-history-summary{
    flex-direction:column;
    align-items:flex-start;
    gap:3px;
  }
  .ev-com-history::before{
    left:29px;
  }
  .ev-com-history-item{
    grid-template-columns:39px minmax(0,1fr);
    gap:10px;
    padding:11px 10px;
  }
  .ev-com-history-icon{
    width:39px;
    height:39px;
    border-radius:12px;
  }
}
.d-none{ display:none !important; }
@media (max-width:1199.98px){
  .ev-com-stats{ grid-template-columns:repeat(2,minmax(0,1fr)); }
  .ev-com-filters{ grid-template-columns:1fr 1fr; }
  .ev-com-search{ grid-column:1 / -1; }
  .ev-com-editor-layout{ grid-template-columns:minmax(390px,1fr) 320px; }
  .ev-com-type-cards{ grid-template-columns:1fr; }
}
@media (max-width:991.98px){
  .ev-com-editor-modal .modal-dialog{ max-width:none; margin:0; }
  .ev-com-publish-modal{ height:100vh; max-height:none; border-radius:0; }
  .ev-com-publish-head{ border-radius:0; }
  .ev-com-publish-form{ border-radius:0; }
  .ev-com-publish-body{ overflow-y:auto; }
  .ev-com-editor-layout{ display:block; height:auto; }
  .ev-com-editor-scroll{ overflow:visible; padding:0; }
  .ev-com-live-preview{ margin-top:13px; overflow:visible; }
  .ev-com-type-cards{ grid-template-columns:repeat(3,minmax(0,1fr)); }
}
@media (max-width:767.98px){
  .ev-com-shell{ padding:12px 12px 24px; }
  .ev-com-hero{ padding:19px 16px; border-radius:20px; }
  .ev-com-pill{ width:100%; white-space:normal; }
  .ev-com-stats,
  .ev-com-filters,
  .ev-com-form-grid{ grid-template-columns:1fr; }
  .ev-com-list-head{ flex-direction:column; }
  .ev-com-list-head .ev-com-btn{ width:100%; }
  .ev-com-publish-head{ padding:12px 15px !important; }
  .ev-com-publish-body{ padding:11px !important; }
  .ev-com-step-card{ padding:12px; }
  .ev-com-type-cards{ grid-template-columns:1fr; }
  .ev-com-publish-footer{
    padding:10px 12px !important;
    flex-direction:column-reverse;
  }
  .ev-com-publish-footer .ev-com-btn{ width:100%; }
}




/* ============================================================
   EV Comunidad - Fix vista previa: textos largos sin espacios
   Regla: la vista previa nunca debe generar scroll horizontal.
============================================================ */
.ev-com-live-preview,
.ev-com-live-preview *,
.ev-com-live-card,
.ev-com-live-card *,
.ev-com-live-head,
.ev-com-live-head *,
.ev-com-live-community,
.ev-com-live-note{
  min-width:0;
  max-width:100%;
}

.ev-com-live-preview{
  overflow-x:hidden;
}

.ev-com-live-card{
  overflow:hidden;
}

.ev-com-live-card h4,
.ev-com-live-card > p,
.ev-com-live-community span,
.ev-com-live-note span,
#vistaTituloCom,
#vistaResumenCom,
#vistaComunidadCom,
#vistaEventoDetalleCom{
  white-space:normal;
  overflow-wrap:anywhere;
  word-break:break-word;
  hyphens:auto;
}

.ev-com-live-card > p{
  display:-webkit-box;
  -webkit-line-clamp:3;
  -webkit-box-orient:vertical;
  overflow:hidden;
}

.ev-com-live-card h4{
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
}

.ev-com-live-event{
  min-width:0;
  overflow:hidden;
}

.ev-com-live-image,
.ev-com-live-image.has-image{
  max-width:100%;
  overflow:hidden;
}


/* ---------- Base defensiva contra desbordes ---------- */
.ev-cv-shell,
.ev-cv-shell *{
  min-width:0;
  box-sizing:border-box;
}

.ev-cv-feature-card,
.ev-cv-card,
.ev-cv-feature-body,
.ev-cv-card-body,
.ev-cv-modal-content,
.ev-cv-modal-body,
.ev-cv-modal-text,
.ev-cv-modal-summary{
  overflow-wrap:anywhere;
  word-break:break-word;
}

/* ---------- Múltiples destacados con estándar uniforme ---------- */
#evCvDestacada{
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(min(100%, 420px), 1fr));
  gap:14px;
  align-items:stretch;
}

#evCvDestacada .ev-cv-feature-card{
  height:100%;
  min-height:218px;
  display:grid;
  grid-template-columns:minmax(150px, .46fr) minmax(0, 1fr);
  align-items:stretch;
  overflow:hidden;
}

#evCvDestacada .ev-cv-feature-img{
  min-height:180px;
  height:100%;
  display:grid;
  place-items:center;
  overflow:hidden;
  background:linear-gradient(135deg, rgba(236,253,245,.80), rgba(255,255,255,.98));
}

#evCvDestacada .ev-cv-feature-img img{
  width:100%;
  height:100%;
  max-height:210px;
  object-fit:contain;
  object-position:center;
  display:block;
}

#evCvDestacada .ev-cv-feature-body{
  height:100%;
  display:flex;
  flex-direction:column;
  justify-content:center;
}

#evCvDestacada .ev-cv-feature-body h3{
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
  line-height:1.22;
  max-width:100%;
}

#evCvDestacada .ev-cv-feature-body > p{
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
  max-width:100%;
}

#evCvDestacada .ev-cv-read{
  width:max-content;
  max-width:100%;
  margin-top:auto;
}

/* ---------- Publicaciones recientes con cards del mismo alto ---------- */
.ev-cv-grid{
  align-items:stretch;
}

.ev-cv-card{
  height:100%;
  min-height:318px;
  display:flex;
  flex-direction:column;
  overflow:hidden;
}

.ev-cv-card-img{
  flex:0 0 152px;
  height:152px;
  display:grid;
  place-items:center;
  overflow:hidden;
  background:linear-gradient(135deg, rgba(236,253,245,.78), rgba(255,255,255,.98));
}

.ev-cv-card-img img{
  width:100%;
  height:100%;
  object-fit:contain;
  object-position:center;
  display:block;
}

.ev-cv-card-body{
  flex:1 1 auto;
  display:flex;
  flex-direction:column;
}

.ev-cv-card-body h3{
  min-height:42px;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
  line-height:1.25;
  max-width:100%;
}

.ev-cv-card-body > p{
  min-height:40px;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
  max-width:100%;
}

.ev-cv-card-footer{
  margin-top:auto;
}

.ev-cv-card-footer button,
.ev-cv-read{
  white-space:nowrap;
}

/* ---------- Evitar etiqueta confusa de selección temporal ---------- */
.ev-cv-publicacion-seleccionada::after{
  content:none !important;
  display:none !important;
}

.ev-cv-publicacion-seleccionada{
  border-color:rgba(234,124,18,.80) !important;
}

/* ---------- Responsive ---------- */
@media (max-width: 1199.98px){
  #evCvDestacada .ev-cv-feature-card{
    grid-template-columns:180px minmax(0, 1fr);
  }
}

@media (max-width: 767.98px){
  #evCvDestacada{
    grid-template-columns:1fr;
  }

  #evCvDestacada .ev-cv-feature-card{
    grid-template-columns:1fr;
    min-height:0;
  }

  #evCvDestacada .ev-cv-feature-img{
    height:178px;
    min-height:178px;
  }

  .ev-cv-card{
    min-height:0;
  }

  .ev-cv-card-img{
    flex-basis:170px;
    height:170px;
  }
}

</style>
