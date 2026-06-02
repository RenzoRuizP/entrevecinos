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
   Modal editor: no se cierra por backdrop ni por tecla Escape
============================================================ */
.ev-com-editor-modal .modal-dialog{
  max-width:min(1120px, calc(100vw - 42px));
}
.ev-com-modal{
  border:0;
  overflow:hidden;
  border-radius:22px;
  box-shadow:0 28px 70px rgba(15,23,42,.24), 0 8px 24px rgba(15,23,42,.10);
}
.ev-com-modal .modal-header{
  background:linear-gradient(135deg,var(--ev-com-verde-oscuro),var(--ev-com-verde));
  color:#fff;
  border:0;
  padding:16px 18px;
}
.ev-com-modal .modal-title{
  font-weight:900;
  font-size:1rem;
  display:flex;
  align-items:center;
  gap:8px;
}
.ev-com-modal .modal-header small{ color:rgba(255,255,255,.82); }
.ev-com-modal .modal-body{ padding:18px; }

.ev-com-modal-editor{
  max-height:calc(100vh - 46px);
  background:#fff;
}
.ev-com-modal-editor-head{
  flex:0 0 auto;
  align-items:flex-start;
  padding:18px 22px !important;
}
.ev-com-modal-heading{ display:flex; flex-direction:column; gap:4px; }
.ev-com-modal-kicker{
  display:inline-flex;
  align-items:center;
  gap:7px;
  width:max-content;
  font-size:.7rem;
  text-transform:uppercase;
  letter-spacing:.08em;
  font-weight:900;
  color:rgba(255,255,255,.86);
}
.ev-com-modal-editor-head .modal-title{
  font-size:1.32rem;
  line-height:1.18;
  margin:1px 0 0;
}
.ev-com-modal-editor-head p{
  color:rgba(255,255,255,.83);
  font-size:.85rem;
  line-height:1.45;
  margin:0;
}
.ev-com-modal-editor-head .btn-close{
  margin:0 0 0 12px;
  opacity:.86;
}
.ev-com-modal-editor form{
  display:flex;
  flex-direction:column;
  min-height:0;
  flex:1 1 auto;
}
.ev-com-modal-editor-body{
  flex:1 1 auto;
  min-height:0;
  overflow-y:auto;
  padding:18px 22px 20px !important;
  background:#fff;
}
.ev-com-assigned-pill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:9px 13px;
  margin:0 0 16px;
  border-radius:999px;
  border:1px solid rgba(22,163,74,.15);
  background:#F0FDF4;
  color:#166534;
  font-size:.82rem;
  font-weight:850;
}
.ev-com-form-meta{
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(190px, 1fr));
  gap:14px;
  margin-bottom:14px;
}
.ev-com-form-grid{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:14px;
}
.ev-com-form-content-grid{ margin-top:2px; }
.ev-com-field{
  display:flex;
  flex-direction:column;
  gap:6px;
}
.ev-com-field-full{ grid-column:1 / -1; }
.ev-com-field label{
  color:#374151;
  font-size:.85rem;
  font-weight:850;
}
.ev-com-field label span{ color:var(--ev-com-naranja); }
.ev-com-field input:not([type="checkbox"]):not([type="file"]),
.ev-com-field select,
.ev-com-field textarea{
  width:100%;
  border:1px solid #D1D5DB;
  border-radius:13px;
  background:#fff;
  color:var(--ev-com-texto);
  font-size:.91rem;
  padding:11px 12px;
  outline:0;
  transition:border-color .17s ease,box-shadow .17s ease;
}
.ev-com-field textarea{ resize:vertical; min-height:56px; }
.ev-com-field input:focus,
.ev-com-field select:focus,
.ev-com-field textarea:focus{
  border-color:var(--ev-com-verde);
  box-shadow:0 0 0 3px rgba(22,163,74,.14);
}
.ev-com-field small{
  color:var(--ev-com-suave);
  font-size:.76rem;
  line-height:1.35;
}
.ev-com-event-fields{
  margin:17px 0 0;
  border:1px solid rgba(22,163,74,.15);
  border-radius:17px;
  padding:14px;
  background:linear-gradient(180deg,#F7FEF9,#fff);
}
.ev-com-event-fields legend{
  float:none;
  width:auto;
  padding:0 8px;
  margin:0 0 12px -4px;
  color:var(--ev-com-verde-oscuro);
  font-size:.9rem;
  font-weight:900;
}
.ev-com-event-fields legend i{ margin-right:7px; }
.ev-com-media-row{
  display:grid;
  grid-template-columns:minmax(300px,1fr) 132px minmax(285px,1fr);
  gap:16px;
  align-items:center;
  margin-top:18px;
  padding-top:18px;
  border-top:1px solid #F0F2F4;
}
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
.ev-com-file-drop{
  min-height:84px;
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  gap:3px;
  border:1.5px dashed #BBF7D0;
  background:#F7FEF9;
  border-radius:15px;
  padding:12px;
  cursor:pointer;
  text-align:center;
  transition:border-color .17s ease, background .17s ease, box-shadow .17s ease;
}
.ev-com-file-drop:hover{
  border-color:rgba(22,163,74,.48);
  background:#F0FDF4;
  box-shadow:0 0 0 3px rgba(22,163,74,.07);
}
.ev-com-file-drop i{
  font-size:1.25rem;
  color:var(--ev-com-verde-mid);
}
.ev-com-file-drop strong{
  color:var(--ev-com-verde-oscuro);
  font-size:.84rem;
}
.ev-com-file-drop small{
  color:var(--ev-com-suave);
  font-size:.74rem;
}
.ev-com-preview{
  display:flex;
  flex-direction:column;
  gap:5px;
  align-items:center;
  color:var(--ev-com-suave);
  font-size:.72rem;
  font-weight:750;
}
.ev-com-preview img{
  width:112px;
  height:76px;
  object-fit:cover;
  border-radius:12px;
  border:1px solid #E5E7EB;
}
.ev-com-check{
  display:flex;
  align-items:flex-start;
  gap:11px;
  padding:15px 15px;
  border:1px solid rgba(22,163,74,.15);
  background:#F0FDF4;
  border-radius:15px;
  cursor:pointer;
}
.ev-com-check input{
  flex:none;
  margin-top:4px;
  accent-color:var(--ev-com-verde);
}
.ev-com-check strong{
  display:block;
  color:var(--ev-com-verde-oscuro);
  font-size:.86rem;
}
.ev-com-check small{
  display:block;
  color:var(--ev-com-suave);
  font-size:.75rem;
  line-height:1.38;
  margin-top:3px;
}
.ev-com-modal-editor-footer{
  flex:0 0 auto;
  justify-content:flex-end;
  flex-wrap:wrap;
  gap:10px;
  padding:14px 22px !important;
  border-top:1px solid #E5E7EB !important;
  background:#fff;
}

/* Historial */
.ev-com-history{ display:flex; flex-direction:column; gap:10px; }
.ev-com-history-item{
  display:flex;
  gap:12px;
  border:1px solid #E5E7EB;
  border-radius:14px;
  padding:12px;
}
.ev-com-history-dot{
  width:10px;
  height:10px;
  border-radius:50%;
  background:var(--ev-com-verde);
  margin-top:6px;
  flex:none;
}
.ev-com-history-item strong{
  display:block;
  color:var(--ev-com-texto);
  font-size:.87rem;
}
.ev-com-history-item p{
  margin:3px 0 0;
  color:var(--ev-com-suave);
  font-size:.78rem;
}
.d-none{ display:none !important; }

@media (max-width:1199.98px){
  .ev-com-stats{ grid-template-columns:repeat(2,minmax(0,1fr)); }
  .ev-com-filters{ grid-template-columns:1fr 1fr; }
  .ev-com-search{ grid-column:1 / -1; }
  .ev-com-media-row{ grid-template-columns:1fr 132px; }
  .ev-com-check{ grid-column:1 / -1; }
}

@media (max-width:767.98px){
  .ev-com-shell{ padding:12px 12px 24px; }
  .ev-com-hero{ padding:19px 16px; border-radius:20px; }
  .ev-com-pill{ width:100%; white-space:normal; }
  .ev-com-stats,
  .ev-com-form-grid,
  .ev-com-form-meta,
  .ev-com-filters{ grid-template-columns:1fr; }
  .ev-com-media-row{ grid-template-columns:1fr; }
  .ev-com-list-head{ flex-direction:column; }
  .ev-com-list-head .ev-com-btn{ width:100%; }
  .ev-com-editor-modal .modal-dialog{ max-width:none; }
  .ev-com-modal-editor{ max-height:none; border-radius:0; }
  .ev-com-modal-editor-head{ padding:16px !important; }
  .ev-com-modal-editor-body{ padding:16px !important; }
  .ev-com-modal-editor-footer{
    padding:12px 16px !important;
    flex-direction:column-reverse;
  }
  .ev-com-modal-editor-footer .ev-com-btn{ width:100%; }
}
</style>
