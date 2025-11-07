<style>
/* ===== 🎨 Contenedor principal ===== */
.container-publicaciones{
  max-width: 1200px;
  margin: 30px auto;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  animation: fadeIn 0.6s ease-in-out;
}

/* ===== 🧩 Card ===== */
.ev-card, .card{
  border-radius: 15px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
  transition: all 0.3s ease;
  background-color: #fff;
}
.ev-card:hover, .card:hover{
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(0,0,0,0.18);
}

/* ===== 🏷️ Cabecera (gradiente del sistema) ===== */
.card-header{
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
  color: #fff;
  background: linear-gradient(135deg, #0F592F, #198754);
  font-weight: 600;
  letter-spacing: 0.4px;
}
.card-header h5{
  color:#ffffff !important;
  margin:0;
  font-weight:600;
}

/* ===== Subheader sticky (toolbar + chips) ===== */
.sticky-toolbar{
  position: sticky;
  top: 0; /* ajusta si tienes navbar fija */
  z-index: 2;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  padding: .65rem .95rem;
}
.ev-kpis{ display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
.ev-kpi{ background:#F7FBF9; border:1px solid #E3F0E9; border-radius:12px; padding:.4rem .7rem; }
.ev-kpi-label{ color:#1b3d2f; font-size:.8rem; opacity:.8; margin-right:.35rem; }
.ev-kpi-value{ font-weight:700; }
.ev-filters-chips{ display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.5rem; }
.ev-chip-filter{
  display:inline-flex; align-items:center; gap:.25rem;
  background:#E6F2EB; color:#0F592F; border:1px solid #CFE6DA;
  border-radius:999px; padding:.25rem .6rem; font-weight:600; font-size:.85rem;
}

/* ===== Botones header ===== */
.btn-ev-outline{
  color: #fff;
  border: 1.5px solid rgba(255,255,255,.9);
  background: transparent;
  border-radius: 10px;
  padding: 8px 20px;
  font-weight: 600;
  transition: all .25s ease;
}
.btn-ev-outline:hover{ background: rgba(255,255,255,.15); transform: translateY(-2px); }

.btn-ev-primary{
  color: #0F592F;
  background: #fff;
  border: 1px solid #ffffff;
  border-radius: 10px;
  padding: 8px 20px;
  font-weight: 700;
  transition: all .25s ease;
}
.btn-ev-primary:hover{ filter: brightness(0.98); transform: translateY(-2px); }

/* Botones suaves (densidad/chips) */
.btn-ev-soft{
  background: rgba(255,255,255,.2);
  border: 1px solid rgba(255,255,255,.7);
  color:#fff;
}
.btn-ev-soft.active{ background:#fff; color:#0F592F; border-color:#fff; }

/* ===== 🧾 Labels / Inputs (mismo patrón del sistema) ===== */
.form-label{
  font-weight: 600;
  font-size: 0.95rem;
  color: #1b3d2f;
}
.input-premium{
  border-radius: 10px;
  border: 1px solid #ced4da;
  padding: 10px 12px;
  transition: all 0.3s ease;
  background-color: #fff;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
  font-size: 0.95rem;
}
.input-premium:focus{
  border-color: #0F592F;
  box-shadow: 0 0 8px rgba(15, 89, 47, 0.25);
  outline: none;
  background-color: #fefefe;
}
.form-select.input-premium{
  appearance: none;
  background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5'%3E%3Cpath fill='%230F592F' d='M2 0L0 2h4L2 0zM2 5L0 3h4l-2 2z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 10px 10px;
}

/* ===== 🎯 Mejora visual para #comboTipo y #comboCategoria ===== */
#comboTipo.form-select.input-premium,
#comboCategoria.form-select.input-premium{
  transition: background-color .2s ease, box-shadow .2s ease, transform .08s ease;
  background-image:
    linear-gradient(180deg, rgba(255,255,255,.0), rgba(255,255,255,.0)),
    var(--bs-form-select-bg-img),
    radial-gradient(ellipse at top left, rgba(15,89,47,.06), transparent 60%);
  background-repeat: no-repeat, no-repeat, no-repeat;
  background-position: right .75rem center, right .75rem center, left -20% -40%;
  background-size: 16px 12px, 16px 12px, 160% 160%;
}
#comboTipo.form-select.input-premium:hover,
#comboCategoria.form-select.input-premium:hover{
  box-shadow: 0 6px 18px rgba(0,0,0,.08);
  transform: translateY(-1px);
}
#comboTipo.form-select.input-premium:focus,
#comboCategoria.form-select.input-premium:focus{
  border-color: #138f57;
  box-shadow: 0 0 0 .2rem rgba(19,143,87,.18);
  outline: none;
  transform: translateY(0);
}
#comboTipo option[disabled]:first-child,
#comboCategoria option[disabled]:first-child{
  color: #8e9aa4;
}
/* Estado “cargando” (cuando están deshabilitados por fetch) */
#comboTipo:disabled,
#comboCategoria:disabled{
  cursor: progress;
  color: #94a3b8;
  background:
    url('data:image/svg+xml;utf8, \
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"> \
        <circle cx="50" cy="50" r="32" stroke="%23138957" stroke-width="10" fill="none" opacity=".2"/> \
        <path d="M82 50a32 32 0 0 1-32 32" stroke="%23138957" stroke-width="10" fill="none"> \
          <animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="0.8s" repeatCount="indefinite"/> \
        </path> \
      </svg>') no-repeat right 2.25rem center / 18px 18px,
    var(--bs-form-select-bg-img) no-repeat right .75rem center/16px 12px,
    linear-gradient(180deg, rgba(0,0,0,.02), rgba(0,0,0,.02));
}
/* Pulso breve al seleccionar (se activa desde JS agregando .ev-pulse) */
@keyframes evPulse {
  0%   { box-shadow: 0 0 0 .0rem rgba(19,143,87,.00); }
  50%  { box-shadow: 0 0 0 .35rem rgba(19,143,87,.15); }
  100% { box-shadow: 0 0 0 .0rem rgba(19,143,87,.00); }
}
#comboTipo.ev-pulse,
#comboCategoria.ev-pulse{
  animation: evPulse .55s ease-out;
}
/* Halo sutil cuando tienen valor */
#comboTipo.is-filled,
#comboCategoria.is-filled{
  background-image:
    linear-gradient(180deg, rgba(255,255,255,.0), rgba(255,255,255,.0)),
    var(--bs-form-select-bg-img),
    radial-gradient(ellipse at top left, rgba(15,89,47,.10), transparent 60%);
}
/* Estado error opcional */
#comboTipo.is-invalid,
#comboCategoria.is-invalid{
  border-color: #dc3545;
  box-shadow: 0 0 0 .2rem rgba(220,53,69,.15);
}

/* ===== Tabla ===== */
.ev-table-wrap{ max-height: calc(70vh - 140px); overflow:auto; }
.ev-table{ width:100%; border-collapse: separate; border-spacing:0; }
.ev-table thead th{
  position: sticky; top: 0; z-index: 1;
  background: #F1F3F5;
  color: #374151;
  font-weight: 600;
  border-top: 0;
  border-bottom: 1px solid #e5e7eb;
  padding: .9rem .9rem;
  white-space: nowrap;
}
.ev-table thead th[data-sort]{ cursor: pointer; }
.ev-table tbody td{
  color: #1F2937;
  vertical-align: middle;
  border-bottom: 1px solid #e5e7eb;
  padding: .85rem .9rem;
}
.ev-table tbody tr:hover{ background: #FAFFFB; }
.ev-code{ font-variant-numeric: tabular-nums; letter-spacing: .3px; color:#111827; }

/* Truncado con tooltip nativo */
.td-trunc{ max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Densidades */
.ev-table[data-density="comfortable"] thead th,
.ev-table[data-density="comfortable"] tbody td{ padding: 1.05rem 1rem; }
.ev-table[data-density="compact"] thead th,
.ev-table[data-density="compact"] tbody td{ padding: .55rem .6rem; }

/* ===== Acciones ===== */
.ev-actions{ display:flex; gap:.6rem; justify-content:center; }
.ev-chip{
  display:inline-flex; align-items:center; justify-content:center;
  border:0; color:#fff; font-weight:700; letter-spacing:.2px;
  padding:.45rem .85rem; border-radius:.6rem; cursor:pointer;
  transition: filter .2s ease, transform .06s ease;
  min-width: 96px;
}
.ev-chip:active{ transform: translateY(1px); }
.ev-chip-amber{ background:#FDB515; }
.ev-chip-green{ background:#198754; }
.ev-chip-teal{ background:#0F592F; }
.ev-chip-red{ background:#DE3B3B; }
.ev-chip-amber:hover,
.ev-chip-green:hover,
.ev-chip-teal:hover,
.ev-chip-red:hover{ filter:brightness(1.06); }

/* ===== Footer / Paginación ===== */
.ev-foot{
  display:flex; align-items:center; justify-content:space-between;
  gap:1rem; padding:.75rem .95rem; color:#6B7280;
  border-top:1px solid #e5e7eb;
}
.ev-select{ max-width: 100px; }
.ev-pagination{ display:flex; list-style:none; gap:.35rem; margin:0; padding:0; }
.ev-page-btn{
  display:inline-flex; align-items:center; justify-content:center;
  min-width:32px; height:28px; border:1px solid #E6E8EB;
  background:#fff; color:#4B5563; border-radius:.35rem; cursor:pointer;
  transition: background .15s ease, color .15s ease;
  padding:0 .5rem;
}
.ev-page-btn:hover{ background:#F8FAFC; }
.ev-page-btn.active{ background:#0F592F; color:#fff; border-color:#0F592F; }
.ev-page-btn:disabled{ opacity:.5; cursor:not-allowed; }

/* ===== Estados / Animación ===== */
.fade-in{ animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn{ from{opacity:0; transform: translateY(5px);} to{opacity:1; transform:none;} }

/* ===== Responsive ===== */
@media (max-width: 768px){
  .ev-chip{ min-width:auto; padding:.4rem .6rem; font-size:.9rem; }
  .ev-table-wrap{ max-height: unset; }
}
@media (max-width: 576px){
  .card-body{ padding:20px 15px; }
  /* Cardificar filas usando data-label */
  .ev-table thead{ display:none; }
  .ev-table tbody tr{
    display:grid; grid-template-columns:1fr; gap:.35rem;
    border-bottom:1px solid #f1f5f9; padding:.6rem .75rem;
  }
  .ev-table tbody td{
    display:flex; justify-content:space-between; gap:.75rem; border-bottom:0;
    padding:.35rem 0;
  }
  .ev-table tbody td::before{
    content: attr(data-label);
    font-weight:600; color:#64748b;
  }
  .ev-foot{ flex-wrap:wrap; }
}

/* ===== Botones estilo sistema (reuso) ===== */
.btn-outline-success, .btn-cancelar{
  border-radius: 10px; padding: 8px 20px; font-weight: 600; font-size: 0.95rem; transition: all 0.25s ease;
}
.btn-outline-success{
  color:#0F592F; border-color:#0F592F; background:transparent;
}
.btn-outline-success:hover{
  background:#0F592F; color:#fff; transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(15, 89, 47, 0.25);
}
.btn-cancelar{
  color:#333; background:#e9ecef; border:1px solid #d6d8d9;
}
.btn-cancelar:hover{
  background:#d1d1d1; color:#000; transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Efecto visual guardar (reutilizado) */
.btn-guardar{ position:relative; overflow:hidden; transition:all .3s ease; }
.btn-guardar.saving{ pointer-events:none; color:transparent !important; }
.btn-guardar.saving::after{
  content:''; position:absolute; top:50%; left:50%; width:18px; height:18px;
  margin-top:-9px; margin-left:-9px; border:2px solid #fff; border-top-color:transparent;
  border-radius:50%; animation: spin .8s linear infinite;
}
@keyframes spin{ from{ transform:rotate(0)} to{ transform:rotate(360deg)} }
.btn-guardar.success::after{
  content:'✔'; color:#fff; font-size:16px; font-weight:bold; animation: popIn .3s ease;
}
@keyframes popIn{ from{ transform:scale(.5); opacity:0 } to{ transform:scale(1); opacity:1 } }

/* ===== 🎨 MODALES: Header con color (gradiente institucional) ===== */
.modal .modal-content{
  border-radius: 15px;
  overflow: hidden; /* asegura que el header redondeado se vea limpio */
}
.modal .modal-header{
  background: linear-gradient(135deg, #0F592F, #198754);
  color:#fff;
  border-bottom: 0;
  /* armoniza radios con la card */
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.modal .modal-title{ color:#fff; font-weight:600; }
.modal .btn-close{ filter: invert(1) contrast(120%); opacity:.9; }
.modal .btn-close:hover{ opacity:1; }

/* ===== Uploader imágenes Entre Vecinos ===== */
.ev-uploader .ev-thumb { position: relative; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,.08); }
.ev-uploader .ev-thumb img { width: 100%; height: 120px; object-fit: cover; display: block; }
.ev-uploader .ev-remove {
  position: absolute; top: 6px; right: 6px;
  border-radius: 999px; padding: .15rem .45rem; line-height: 1;
}
.ev-uploader .ev-caption {
  position: absolute; left: 0; right: 0; bottom: 0;
  background: linear-gradient(transparent, rgba(0,0,0,.55));
  color: #fff; font-size: .75rem; padding: .25rem .5rem;
  text-shadow: 0 1px 2px rgba(0,0,0,.5);
}

</style>
