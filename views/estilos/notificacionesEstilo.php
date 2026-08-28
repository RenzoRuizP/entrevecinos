<?php /* views/estilos/notificacionesEstilo.php */ ?>
<style>
:root{
  --ev-notif-verde-900:#0F592F;
  --ev-notif-verde-700:#0E7A43;
  --ev-notif-verde-600:#16A34A;
  --ev-notif-verde-050:#F0FDF4;
  --ev-notif-naranja:#EA7C12;
  --ev-notif-naranja-050:#FFF7ED;
  --ev-notif-azul:#2563EB;
  --ev-notif-azul-050:#EFF6FF;
  --ev-notif-rojo:#DC2626;
  --ev-notif-rojo-050:#FEF2F2;
  --ev-notif-texto:#111827;
  --ev-notif-muted:#64748B;
  --ev-notif-borde:#E5E7EB;
  --ev-notif-shadow:0 16px 38px rgba(15,23,42,.07);
  --ev-notif-shadow-hover:0 22px 46px rgba(15,23,42,.11);
}

.ev-notificaciones-page{max-width:100%;padding:14px 14px 28px;color:var(--ev-notif-texto)}
.ev-notificaciones-hero,.ev-notificaciones-panel{border-radius:24px;border:1px solid rgba(148,163,184,.17);overflow:hidden;background:#fff;box-shadow:var(--ev-notif-shadow)}
.ev-notificaciones-hero{background:radial-gradient(circle at 86% 16%,rgba(22,163,74,.13),transparent 34%),radial-gradient(circle at 12% 86%,rgba(234,124,18,.12),transparent 30%),linear-gradient(135deg,#fffdfa 0%,#f8fcf9 50%,#f2fbf5 100%)}
.ev-notificaciones-hero-content{padding:20px;display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap}
.ev-notificaciones-title-wrap{display:flex;gap:14px;align-items:flex-start;flex:1 1 520px;min-width:0}
.ev-notificaciones-title-icon{width:54px;height:54px;display:grid;place-items:center;border-radius:18px;background:linear-gradient(135deg,rgba(187,247,208,.96),#fff);border:1px solid rgba(22,163,74,.22);box-shadow:0 12px 24px rgba(15,23,42,.08);font-size:1.25rem;color:var(--ev-notif-verde-900);flex:0 0 auto}
.ev-notificaciones-kicker{font-size:.75rem;font-weight:900;letter-spacing:.14em;color:var(--ev-notif-naranja);margin:1px 0 5px;text-transform:uppercase}
.ev-notificaciones-title{font-size:2.12rem;letter-spacing:-.035em;line-height:1.04;color:var(--ev-notif-verde-900);margin:0 0 5px;font-weight:900}
.ev-notificaciones-subtitle{margin:0;color:var(--ev-notif-muted);font-size:.95rem;line-height:1.48;max-width:760px}
.ev-notificaciones-summary-grid{display:grid;grid-template-columns:repeat(3,minmax(120px,1fr));gap:11px;width:min(100%,465px);flex:0 1 465px}
.ev-notificaciones-summary-card{position:relative;border:1px solid rgba(148,163,184,.16);border-radius:18px;background:rgba(255,255,255,.91);box-shadow:0 8px 22px rgba(15,23,42,.05);padding:14px 15px;overflow:hidden}
.ev-notificaciones-summary-card:after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:linear-gradient(90deg,var(--ev-notif-verde-600),var(--ev-notif-naranja));opacity:.25}
.ev-notificaciones-summary-card span{display:block;color:var(--ev-notif-muted);font-size:.78rem;font-weight:800;line-height:1.2;margin-bottom:4px}
.ev-notificaciones-summary-card strong{display:block;color:var(--ev-notif-verde-900);font-size:1.45rem;line-height:1;font-weight:900}

.ev-notificaciones-panel-head{padding:18px;border-bottom:1px solid var(--ev-notif-borde);display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;background:linear-gradient(180deg,#fff,#fbfdfb)}
.ev-notificaciones-panel-head h5{margin:0 0 4px;font-size:1.1rem;font-weight:900;color:var(--ev-notif-verde-900)}
.ev-notificaciones-panel-head p{margin:0;max-width:760px;color:var(--ev-notif-muted);font-size:.9rem;line-height:1.45}
.ev-notificaciones-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap}
.ev-notificaciones-btn-primary,.ev-notificaciones-btn-outline,.ev-notificaciones-btn-page{border-radius:14px;font-weight:850;transition:.16s ease}
.ev-notificaciones-btn-primary{border:0;background:linear-gradient(135deg,var(--ev-notif-naranja),#F59E0B);box-shadow:0 12px 24px rgba(234,124,18,.2);color:#fff;padding:.72rem 1rem}
.ev-notificaciones-btn-primary:hover{transform:translateY(-1px);filter:brightness(1.03);color:#fff}.ev-notificaciones-btn-primary:disabled{transform:none;filter:none;opacity:.48;box-shadow:none;cursor:not-allowed}
.ev-notificaciones-btn-outline{background:#fff;border:1px solid rgba(22,163,74,.2);color:var(--ev-notif-verde-900);padding:.72rem 1rem;box-shadow:0 8px 18px rgba(15,23,42,.04)}
.ev-notificaciones-btn-outline:hover{transform:translateY(-1px);background:var(--ev-notif-verde-050);color:var(--ev-notif-verde-900)}

.ev-notificaciones-toolbar{padding:14px 18px;display:flex;align-items:end;gap:12px;flex-wrap:wrap;background:#fff;border-bottom:1px solid #EEF2F7}
.ev-notificaciones-field{display:grid;gap:5px;min-width:210px}.ev-notificaciones-field-small{min-width:115px}
.ev-notificaciones-field label{font-size:.75rem;font-weight:900;color:var(--ev-notif-verde-900);text-transform:uppercase;letter-spacing:.06em}
.ev-notificaciones-field .form-select{border-color:#DCE4EE;border-radius:13px;min-height:41px;font-size:.9rem;color:#111827}
.ev-notificaciones-field .form-select:focus{border-color:var(--ev-notif-naranja);box-shadow:0 0 0 4px rgba(234,124,18,.10)}

.ev-notificaciones-body{padding:18px;background:linear-gradient(180deg,#FCFDFC,#F8FAF9)}
.ev-notificaciones-alert{padding:14px 16px;border-radius:17px;margin-bottom:16px;background:var(--ev-notif-rojo-050);border:1px solid #FECACA;color:#991B1B;font-weight:800;text-align:center}
.ev-notificaciones-list{display:grid;gap:12px}
.ev-notificaciones-item{position:relative;display:grid;grid-template-columns:52px minmax(0,1fr) auto;gap:13px;align-items:flex-start;border:1px solid rgba(148,163,184,.17);border-radius:20px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.045);padding:14px;transition:.16s ease;overflow:hidden}
.ev-notificaciones-item:hover{transform:translateY(-1px);box-shadow:var(--ev-notif-shadow-hover);border-color:rgba(22,163,74,.2)}
.ev-notificaciones-item.is-unread{background:linear-gradient(90deg,#F0FDF4 0%,#FFFFFF 44%)}
.ev-notificaciones-item.is-unread:before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:linear-gradient(180deg,#16A34A,#EA7C12)}
.ev-notificaciones-icon{width:48px;height:48px;display:grid;place-items:center;border-radius:17px;border:1px solid #E2E8F0;background:#F8FAFC;color:#475569;font-size:1.15rem;box-shadow:0 8px 16px rgba(15,23,42,.04)}
.ev-notificaciones-icon.is-info{color:#1D4ED8;background:#EFF6FF;border-color:#BFDBFE}.ev-notificaciones-icon.is-warning{color:#C46B05;background:#FFF7ED;border-color:#FED7AA}.ev-notificaciones-icon.is-danger{color:#B91C1C;background:#FEF2F2;border-color:#FECACA}.ev-notificaciones-icon.is-success{color:#166534;background:#F0FDF4;border-color:#BBF7D0}.ev-notificaciones-icon.is-neutral{color:#475569;background:#F8FAFC;border-color:#E2E8F0}
.ev-notificaciones-copy{min-width:0}.ev-notificaciones-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:5px}.ev-notificaciones-top strong{display:block;color:var(--ev-notif-verde-900);font-size:.98rem;line-height:1.24;font-weight:950;overflow-wrap:anywhere}.ev-notificaciones-badge{display:inline-flex;align-items:center;gap:6px;flex:0 0 auto;padding:5px 9px;border-radius:999px;font-size:.68rem;font-weight:950}.ev-notificaciones-badge-unread{background:#FFF7ED;border:1px solid #FED7AA;color:#9A3412}.ev-notificaciones-badge-read{background:#F8FAFC;border:1px solid #E2E8F0;color:#64748B}
.ev-notificaciones-subcategory{background:#FFF7ED;border-color:#FED7AA;color:#9A3412}.ev-notificaciones-message{margin:0;color:#475569;font-size:.86rem;line-height:1.45;white-space:pre-wrap;overflow-wrap:anywhere}.ev-notificaciones-context{display:block;margin-top:5px;color:#334155;font-size:.78rem;font-weight:850}.ev-notificaciones-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;color:#94A3B8;font-size:.72rem;font-weight:850}.ev-notificaciones-chip{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:999px;background:#F8FAFC;border:1px solid #E2E8F0;color:#64748B}
.ev-notificaciones-row-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}.ev-notificaciones-open,.ev-notificaciones-read{border-radius:13px;font-size:.82rem;font-weight:900;padding:.62rem .82rem}.ev-notificaciones-open{border:0;background:linear-gradient(135deg,var(--ev-notif-verde-700),#22C55E);color:#fff;box-shadow:0 10px 20px rgba(22,163,74,.14)}.ev-notificaciones-open:hover{color:#fff;filter:brightness(1.03);transform:translateY(-1px)}.ev-notificaciones-read{background:#fff;border:1px solid #E5E7EB;color:#475569}.ev-notificaciones-read:hover{background:#F8FAFC;color:#111827}.ev-notificaciones-read:disabled{opacity:.55;cursor:not-allowed}
.ev-notificaciones-loading,.ev-notificaciones-empty{min-height:230px;display:flex;align-items:center;justify-content:center;gap:10px;padding:24px;border:1px dashed #CBD5E1;border-radius:20px;background:#fff;color:#64748B;font-weight:800;text-align:center}.ev-notificaciones-empty{flex-direction:column}.ev-notificaciones-empty i{font-size:1.7rem;color:var(--ev-notif-verde-900)}
.ev-notificaciones-spinner{width:28px;height:28px;border:4px solid rgba(22,163,74,.16);border-top-color:var(--ev-notif-verde-900);border-radius:50%;animation:evNotifCentroSpin .78s linear infinite}@keyframes evNotifCentroSpin{to{transform:rotate(360deg)}}
.ev-notificaciones-footer{padding:14px 18px;border-top:1px solid #EEF2F7;background:#fff;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;color:#64748B;font-size:.84rem;font-weight:800}.ev-notificaciones-pager{display:flex;align-items:center;gap:10px}.ev-notificaciones-btn-page{width:40px;height:40px;padding:0;display:grid;place-items:center;background:#fff;border:1px solid #E5E7EB;color:var(--ev-notif-verde-900)}.ev-notificaciones-btn-page:hover{background:var(--ev-notif-verde-050)}.ev-notificaciones-btn-page:disabled{opacity:.45;cursor:not-allowed}

@media(max-width:991.98px){.ev-notificaciones-summary-grid{width:100%;flex:1 1 100%}.ev-notificaciones-item{grid-template-columns:48px minmax(0,1fr)}.ev-notificaciones-row-actions{grid-column:1/-1;justify-content:flex-end}.ev-notificaciones-open,.ev-notificaciones-read{flex:0 1 auto}}
@media(max-width:767.98px){.ev-notificaciones-page{padding:10px 10px 22px}.ev-notificaciones-hero-content,.ev-notificaciones-panel-head,.ev-notificaciones-body{padding:14px}.ev-notificaciones-title{font-size:1.72rem}.ev-notificaciones-title-wrap{gap:11px}.ev-notificaciones-title-icon{width:48px;height:48px;border-radius:16px}.ev-notificaciones-summary-grid{grid-template-columns:1fr}.ev-notificaciones-actions{width:100%;align-items:stretch}.ev-notificaciones-actions .btn{width:100%}.ev-notificaciones-toolbar{padding:13px 14px;display:grid;grid-template-columns:1fr;gap:11px}.ev-notificaciones-field,.ev-notificaciones-field-small{min-width:0}.ev-notificaciones-item{grid-template-columns:46px minmax(0,1fr);gap:10px;padding:12px}.ev-notificaciones-icon{width:42px;height:42px;border-radius:14px}.ev-notificaciones-top{flex-direction:column;align-items:flex-start}.ev-notificaciones-row-actions{grid-column:1/-1;display:flex;flex-direction:row;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}.ev-notificaciones-open,.ev-notificaciones-read{width:auto;min-height:38px;padding:.5rem .72rem;font-size:.78rem;flex:0 0 auto}.ev-notificaciones-footer{flex-direction:column;align-items:stretch;text-align:center}.ev-notificaciones-pager{justify-content:center}}

@media(max-width:420px){
  .ev-notificaciones-row-actions{justify-content:stretch;}
  .ev-notificaciones-open,.ev-notificaciones-read{
    flex:1 1 calc(50% - 4px);
    width:auto;
    min-height:38px;
    padding:.48rem .58rem;
    white-space:nowrap;
  }
}


/* ==========================================================
   EV QA 2026-08-24 — INTERACCIÓN PREMIUM CENTRO NOTIFICACIONES
   - "Abrir" usa el anaranjado EV.
   - Cards interactivos responden con hover/focus anaranjado EV.
========================================================== */
.ev-notificaciones-open{
  background:linear-gradient(135deg,var(--ev-notif-naranja),#F59E0B);
  color:#fff;
  border:1px solid transparent;
  box-shadow:0 10px 20px rgba(234,124,18,.18);
  transition:transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease;
}

.ev-notificaciones-open:hover,
.ev-notificaciones-open:focus-visible{
  background:linear-gradient(135deg,#C46B05,var(--ev-notif-naranja));
  color:#fff;
  filter:none;
  transform:translateY(-1px);
  box-shadow:0 14px 28px rgba(234,124,18,.28);
}

.ev-notificaciones-open:active{
  background:linear-gradient(135deg,#B85F03,#C46B05);
  color:#fff;
  transform:translateY(0) scale(.985);
  box-shadow:0 8px 18px rgba(234,124,18,.22);
}

.ev-notificaciones-item:hover,
.ev-notificaciones-item:focus-within{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.50);
  background:linear-gradient(90deg,#FFF7ED 0%,#FFFFFF 42%);
  box-shadow:
    0 18px 38px rgba(234,124,18,.12),
    0 9px 22px rgba(15,23,42,.055);
}

.ev-notificaciones-item:hover::before,
.ev-notificaciones-item:focus-within::before{
  background:linear-gradient(180deg,#EA7C12,#F59E0B);
  opacity:1;
}

.ev-notificaciones-item:active{
  transform:translateY(0) scale(.998);
}

@media (hover:none){
  .ev-notificaciones-item:hover{
    transform:none;
  }

  .ev-notificaciones-item:focus-within,
  .ev-notificaciones-item:active{
    border-color:rgba(234,124,18,.46);
    background:linear-gradient(90deg,#FFF7ED 0%,#FFFFFF 46%);
    box-shadow:0 12px 26px rgba(234,124,18,.10);
  }
}

</style>
