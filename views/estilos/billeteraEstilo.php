<?php /* views/estilos/billeteraEstilo.php – Estándar UX/UI EV */ ?>
<style>
:root{
  --ev-wallet-verde-900:#0F592F;
  --ev-wallet-verde-700:#15803D;
  --ev-wallet-verde-600:#16A34A;
  --ev-wallet-verde-100:#DCFCE7;
  --ev-wallet-verde-050:#F0FDF4;
  --ev-wallet-naranja:#EA7C12;
  --ev-wallet-naranja-oscuro:#C46B05;
  --ev-wallet-naranja-050:#FFF7ED;
  --ev-wallet-rojo:#DC2626;
  --ev-wallet-rojo-050:#FEF2F2;
  --ev-wallet-texto:#111827;
  --ev-wallet-muted:#64748B;
  --ev-wallet-borde:#E5E7EB;
  --ev-wallet-fondo:#F8FAFC;
  --ev-wallet-shadow:0 16px 38px rgba(15,23,42,.07);
  --ev-wallet-shadow-hover:0 22px 46px rgba(15,23,42,.11);
  --ev-wallet-modal-grad-1:#0F592F;
  --ev-wallet-modal-grad-2:#0E7A43;
  --ev-wallet-modal-grad-3:#16A34A;
}

.ev-wallet-page{
  max-width:100%;
  padding:14px 14px 28px;
  color:var(--ev-wallet-texto);
}

.ev-wallet-hero,
.ev-wallet-panel{
  border-radius:24px;
  border:1px solid rgba(148,163,184,.17);
  overflow:hidden;
  background:#fff;
  box-shadow:var(--ev-wallet-shadow);
}

.ev-wallet-hero{
  background:
    radial-gradient(circle at 86% 16%,rgba(22,163,74,.13),transparent 34%),
    radial-gradient(circle at 12% 86%,rgba(234,124,18,.12),transparent 30%),
    linear-gradient(135deg,#fffdfa 0%,#f8fcf9 50%,#f2fbf5 100%);
}

.ev-wallet-hero-content{
  padding:20px;
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:20px;
  flex-wrap:wrap;
}

.ev-wallet-title-wrap{
  display:flex;
  gap:14px;
  align-items:flex-start;
  flex:1 1 520px;
  min-width:0;
}

.ev-wallet-title-icon{
  width:54px;
  height:54px;
  display:grid;
  place-items:center;
  border-radius:18px;
  background:linear-gradient(135deg,rgba(187,247,208,.96),#fff);
  border:1px solid rgba(22,163,74,.22);
  box-shadow:0 12px 24px rgba(15,23,42,.08);
  font-size:1.25rem;
  color:var(--ev-wallet-verde-900);
  flex:0 0 auto;
}

.ev-wallet-kicker{
  font-size:.75rem;
  font-weight:900;
  letter-spacing:.14em;
  color:var(--ev-wallet-naranja);
  margin:1px 0 5px;
  text-transform:uppercase;
}

.ev-wallet-title{
  font-size:2.12rem;
  letter-spacing:-.035em;
  line-height:1.04;
  color:var(--ev-wallet-verde-900);
  margin:0 0 5px;
  font-weight:900;
}

.ev-wallet-subtitle{
  margin:0;
  color:var(--ev-wallet-muted);
  font-size:.95rem;
  line-height:1.48;
  max-width:760px;
}

.ev-wallet-summary-grid{
  display:grid;
  grid-template-columns:repeat(3,minmax(120px,1fr));
  gap:11px;
  width:min(100%,500px);
  flex:0 1 500px;
}

.ev-wallet-summary-card{
  position:relative;
  border:1px solid rgba(148,163,184,.16);
  border-radius:18px;
  background:rgba(255,255,255,.92);
  box-shadow:0 8px 22px rgba(15,23,42,.05);
  padding:14px 15px;
  overflow:hidden;
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease;
}

.ev-wallet-summary-card::after{
  content:"";
  position:absolute;
  left:0;
  right:0;
  bottom:0;
  height:3px;
  background:linear-gradient(90deg,var(--ev-wallet-verde-600),var(--ev-wallet-naranja));
  opacity:.25;
}

.ev-wallet-summary-card:hover{
  transform:translateY(-2px);
  box-shadow:0 14px 28px rgba(15,23,42,.09);
  border-color:rgba(234,124,18,.28);
}

.ev-wallet-summary-card > span{
  display:block;
  color:var(--ev-wallet-muted);
  font-size:.78rem;
  font-weight:800;
  line-height:1.2;
  margin-bottom:5px;
}

.ev-wallet-summary-card > strong{
  display:block;
  color:var(--ev-wallet-verde-900);
  font-size:1.45rem;
  line-height:1.05;
  font-weight:900;
  overflow-wrap:anywhere;
}

.ev-wallet-summary-card--balance > strong{
  color:var(--ev-wallet-naranja-oscuro);
}

.ev-wallet-summary-card > small{
  display:block;
  margin-top:5px;
  color:#94A3B8;
  font-size:.7rem;
  line-height:1.3;
  font-weight:750;
}

.ev-wallet-panel-head{
  padding:18px;
  border-bottom:1px solid var(--ev-wallet-borde);
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:18px;
  flex-wrap:wrap;
  background:linear-gradient(180deg,#fff,#fbfdfb);
}

.ev-wallet-panel-head h5{
  margin:0 0 4px;
  font-size:1.1rem;
  font-weight:900;
  color:var(--ev-wallet-verde-900);
}

.ev-wallet-panel-head p{
  margin:0;
  max-width:760px;
  color:var(--ev-wallet-muted);
  font-size:.9rem;
  line-height:1.45;
}

.ev-wallet-head-actions{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:10px;
  flex-wrap:wrap;
}

.ev-wallet-btn-primary,
.ev-wallet-btn-outline,
.ev-wallet-btn-refresh{
  min-height:44px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  border-radius:999px;
  padding:.72rem 1rem;
  font-size:.86rem;
  font-weight:900;
  transition:transform .16s ease,box-shadow .16s ease,background .16s ease,border-color .16s ease;
}

.ev-wallet-btn-primary{
  border:0;
  color:#fff;
  background:linear-gradient(135deg,var(--ev-wallet-naranja),#F59E0B);
  box-shadow:0 12px 24px rgba(234,124,18,.22);
}

.ev-wallet-btn-primary:hover,
.ev-wallet-btn-primary:focus{
  color:#fff;
  transform:translateY(-1px);
  box-shadow:0 16px 30px rgba(234,124,18,.3);
}

.ev-wallet-btn-outline,
.ev-wallet-btn-refresh{
  border:1px solid rgba(22,163,74,.2);
  background:#fff;
  color:var(--ev-wallet-verde-900);
  box-shadow:0 8px 18px rgba(15,23,42,.05);
}

.ev-wallet-btn-outline:hover,
.ev-wallet-btn-outline:focus,
.ev-wallet-btn-refresh:hover,
.ev-wallet-btn-refresh:focus{
  color:var(--ev-wallet-verde-900);
  transform:translateY(-1px);
  background:var(--ev-wallet-verde-050);
  box-shadow:0 12px 24px rgba(15,23,42,.08);
}

.ev-wallet-btn-refresh.is-loading i{
  animation:evWalletSpin .75s linear infinite;
}

@keyframes evWalletSpin{to{transform:rotate(360deg)}}

.ev-wallet-panel-body{
  padding:18px;
  display:grid;
  gap:18px;
  background:linear-gradient(180deg,#FCFDFC,#F8FAF9);
}

.ev-wallet-activity-block{
  border:1px solid rgba(148,163,184,.16);
  border-radius:22px;
  background:linear-gradient(180deg,#fff,#FBFCFB);
  box-shadow:0 10px 26px rgba(15,23,42,.055);
  padding:16px;
  transition:transform .17s ease,box-shadow .17s ease,border-color .17s ease;
}

.ev-wallet-activity-block:hover{
  transform:translateY(-1px);
  box-shadow:var(--ev-wallet-shadow-hover);
  border-color:rgba(234,124,18,.24);
}

.ev-wallet-activity-head{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:14px;
  margin-bottom:14px;
}

.ev-wallet-activity-title-wrap{
  display:flex;
  align-items:flex-start;
  gap:11px;
  min-width:0;
}

.ev-wallet-activity-icon{
  width:42px;
  height:42px;
  display:grid;
  place-items:center;
  flex:0 0 auto;
  border-radius:14px;
  background:linear-gradient(135deg,var(--ev-wallet-verde-100),#fff);
  border:1px solid rgba(22,163,74,.18);
  color:var(--ev-wallet-verde-900);
  box-shadow:0 8px 18px rgba(15,23,42,.05);
}

.ev-wallet-activity-head h3{
  margin:0 0 3px;
  font-size:1rem;
  line-height:1.25;
  font-weight:900;
  color:var(--ev-wallet-verde-900);
}

.ev-wallet-activity-head p{
  margin:0;
  color:var(--ev-wallet-muted);
  font-size:.84rem;
  line-height:1.42;
}

.ev-wallet-empty{
  min-height:108px;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:12px;
  border:1px dashed #CBD5E1;
  border-radius:18px;
  background:#fff;
  color:var(--ev-wallet-muted);
  padding:18px;
  text-align:left;
}

.ev-wallet-empty > i{
  font-size:1.35rem;
  color:var(--ev-wallet-verde-900);
  flex:0 0 auto;
}

.ev-wallet-empty strong{
  display:block;
  color:#475569;
  font-size:.9rem;
  line-height:1.35;
  margin-bottom:3px;
}

.ev-wallet-empty span{
  display:block;
  color:#94A3B8;
  font-size:.8rem;
  line-height:1.4;
}

.ev-wallet-movimientos{margin-top:2px}
.ev-wallet-mov-list{display:grid;gap:12px}
.ev-wallet-mov-card{
  display:grid;
  grid-template-columns:auto minmax(0,1fr) auto;
  gap:14px;
  align-items:center;
  padding:14px 15px;
  border-radius:18px;
  background:#fff;
  border:1px solid rgba(148,163,184,.16);
  box-shadow:0 8px 18px rgba(15,23,42,.045);
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease;
  overflow:hidden;
  position:relative;
}
.ev-wallet-mov-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;opacity:.85}
.ev-wallet-mov-card--credito::before{background:var(--ev-wallet-verde-600)}
.ev-wallet-mov-card--debito::before{background:var(--ev-wallet-rojo)}
.ev-wallet-mov-card:hover{transform:translateY(-1px);box-shadow:0 14px 26px rgba(15,23,42,.08);border-color:rgba(234,124,18,.22)}
.ev-wallet-mov-icon-wrap{width:48px;height:48px;border-radius:15px;display:grid;place-items:center;flex:0 0 auto}
.ev-wallet-mov-card--credito .ev-wallet-mov-icon-wrap{background:var(--ev-wallet-verde-050)}
.ev-wallet-mov-card--debito .ev-wallet-mov-icon-wrap{background:var(--ev-wallet-rojo-050)}
.ev-wallet-mov-icon{font-size:1.25rem}.ev-wallet-mov-icon--credito{color:var(--ev-wallet-verde-600)}.ev-wallet-mov-icon--debito{color:var(--ev-wallet-rojo)}
.ev-wallet-mov-main{min-width:0}.ev-wallet-mov-titulo{font-weight:900;color:var(--ev-wallet-texto);font-size:.94rem;line-height:1.35;margin-bottom:7px}
.ev-wallet-mov-meta{display:flex;flex-wrap:wrap;gap:7px}.ev-wallet-mov-chip{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:5px 9px;font-size:.73rem;font-weight:800;background:#F8FAFC;color:var(--ev-wallet-muted);border:1px solid #E9EEF5}.ev-wallet-mov-chip i{font-size:.82rem;color:var(--ev-wallet-verde-700)}
.ev-wallet-mov-side{min-width:145px;text-align:right}.ev-wallet-mov-monto{font-weight:900;font-size:1.02rem;line-height:1;margin-bottom:7px}.ev-wallet-monto--credito{color:var(--ev-wallet-verde-700)}.ev-wallet-monto--debito{color:var(--ev-wallet-rojo)}
.ev-wallet-mov-saldo-label{display:block;font-size:.67rem;color:var(--ev-wallet-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;font-weight:800}.ev-wallet-mov-saldo{font-size:.86rem;color:var(--ev-wallet-texto);font-weight:850}

.ev-wallet-table-shell{
  border:1px solid rgba(148,163,184,.18);
  border-radius:18px;
  overflow:hidden;
  background:#fff;
  box-shadow:0 8px 18px rgba(15,23,42,.045);
}
.ev-wallet-table-shell table{margin:0}
.ev-wallet-table-shell thead th{border-bottom:1px solid var(--ev-wallet-borde);font-weight:900;color:var(--ev-wallet-muted);text-transform:uppercase;font-size:.71rem;letter-spacing:.05em;background:#F8FAFC;padding:13px 12px}
.ev-wallet-table-shell tbody td{border-color:#EEF2F7;vertical-align:middle;padding:12px}
.ev-wallet-table-shell tbody tr{transition:background .15s ease}.ev-wallet-table-shell tbody tr:hover{background:var(--ev-wallet-naranja-050)}

.btn-ev-orange{
  background:linear-gradient(135deg,var(--ev-wallet-naranja),#F59E0B);
  border:0;
  color:#fff;
  font-weight:850;
  border-radius:14px;
  padding:.62rem .9rem;
  box-shadow:0 10px 20px rgba(234,124,18,.2);
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  transition:transform .15s ease,box-shadow .15s ease,filter .15s ease;
}
.btn-ev-orange:hover{filter:brightness(1.03);transform:translateY(-1px);box-shadow:0 14px 26px rgba(234,124,18,.28);color:#fff}
.btn-ev-outline{background:#fff;border-radius:999px;border:1px solid var(--ev-wallet-borde);color:var(--ev-wallet-texto);font-weight:800;padding:.55rem 1rem;display:inline-flex;align-items:center;justify-content:center;gap:7px;transition:.15s ease}.btn-ev-outline:hover{background:var(--ev-wallet-verde-050);color:var(--ev-wallet-verde-900);transform:translateY(-1px)}
#btnEnviarRecarga.saving{opacity:.85;pointer-events:none}

.ev-modal-login .ev-modal-content{border-radius:22px;border:0;overflow:hidden;background:transparent;box-shadow:0 26px 70px rgba(15,23,42,.24)}
.ev-modal-login .ev-login-modal-header{background:linear-gradient(140deg,var(--ev-wallet-modal-grad-1) 0%,var(--ev-wallet-modal-grad-2) 55%,var(--ev-wallet-modal-grad-3) 100%);padding:16px 22px;border-bottom:1px solid rgba(255,255,255,.2);color:#fff}
.ev-modal-login .ev-login-modal-header .modal-title{font-weight:850;font-size:1rem;color:#fff;display:inline-flex;align-items:center}.ev-modal-login .ev-login-modal-header .btn-close{filter:invert(1);opacity:1}
.ev-modal-login .ev-login-modal-body{padding:1.7rem 2rem 1.4rem;background:#fff}.ev-modal-login .ev-login-modal-footer{border-top:1px solid #E5E7EB;padding:.8rem 1.25rem;background:#fff}
.ev-modal-login .ev-modal-content .form-label{font-weight:800;font-size:.84rem;color:#334155}.ev-modal-login .ev-modal-content .form-control,.ev-modal-login .ev-modal-content .form-select{border-radius:13px;border:1px solid #DCE4EE;font-size:.92rem;transition:.18s ease;padding-left:14px;padding-right:14px;box-shadow:none;min-height:42px}.ev-modal-login .ev-modal-content .form-control:focus,.ev-modal-login .ev-modal-content .form-select:focus{border-color:var(--ev-wallet-verde-600);box-shadow:0 0 0 3px rgba(22,163,74,.17);outline:0}
#recarga_operacion{letter-spacing:.03em}
.ev-btn-modal-outline{border-radius:14px;font-size:.9rem;border:1px solid #D1D5DB;color:#4B5563;background:#fff;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 15px;font-weight:800}.ev-btn-modal-outline:hover{background:#F3F4F6;color:#111827}
.ev-btn-modal-primary{background:linear-gradient(135deg,var(--ev-wallet-naranja),#F59E0B);border:0;color:#fff;border-radius:14px;font-size:.92rem;font-weight:850;box-shadow:0 12px 26px rgba(234,124,18,.28);display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;transition:.18s ease}.ev-btn-modal-primary:hover{background:linear-gradient(135deg,var(--ev-wallet-naranja-oscuro),#EA580C);color:#fff;transform:translateY(-1px);box-shadow:0 14px 32px rgba(234,124,18,.38)}.ev-btn-modal-cta{min-height:40px}

.ev-wallet-qr-card{border-radius:18px;border:1px solid var(--ev-wallet-borde);background:#F8FAFC;padding:16px;box-shadow:0 10px 24px rgba(15,23,42,.06)}
.ev-wallet-qr-img{max-width:220px;width:100%;height:auto;border-radius:14px;display:block;margin:0 auto 10px;border:1px solid rgba(148,163,184,.22)}
.ev-wallet-qr-title{font-weight:900;color:var(--ev-wallet-texto);font-size:.95rem;margin-top:8px}.ev-wallet-qr-text{font-size:.84rem;color:var(--ev-wallet-muted);line-height:1.4}

.ev-swal-popup{border-radius:26px!important;padding:24px 20px 19px!important;border:1px solid #E5E7EB!important;box-shadow:0 28px 70px rgba(15,23,42,.2)!important;background:#fff!important}.ev-swal-title{color:var(--ev-wallet-verde-900)!important;font-weight:900!important;font-size:1.85rem!important;letter-spacing:-.03em!important}.ev-swal-html{color:var(--ev-wallet-muted)!important;font-size:.94rem!important}.ev-swal-confirm{background:linear-gradient(135deg,var(--ev-wallet-naranja),#F59E0B)!important;color:#fff!important;border:0!important;border-radius:14px!important;padding:11px 20px!important;font-weight:900!important;box-shadow:0 12px 25px rgba(234,124,18,.28)!important}.ev-swal-cancel{background:#fff!important;color:#374151!important;border:1px solid #E5E7EB!important;border-radius:14px!important;padding:11px 20px!important;font-weight:900!important}.ev-swal-nocancel .swal2-cancel{display:none!important}

.ev-mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace}
#recarga_alerta_subsanacion{border-radius:14px;border:1px solid rgba(234,124,18,.22);background:linear-gradient(180deg,#FFF7ED,#FFFBEB);color:#9A3412}#recarga_alerta_subsanacion .fw-semibold{color:#C2410C}

@media(max-width:1199.98px){
  .ev-wallet-head-actions{justify-content:flex-start;width:100%}
}

@media(max-width:991.98px){
  .ev-wallet-summary-grid{width:100%;flex:1 1 100%}
}

@media(max-width:767.98px){
  .ev-wallet-page{padding:10px 10px 22px}
  .ev-wallet-hero-content,.ev-wallet-panel-head,.ev-wallet-panel-body{padding:14px}
  .ev-wallet-title{font-size:1.72rem}
  .ev-wallet-summary-grid{grid-template-columns:1fr}
  .ev-wallet-head-actions{align-items:stretch}
  .ev-wallet-btn-primary,.ev-wallet-btn-outline,.ev-wallet-btn-refresh{width:100%}
  .ev-wallet-activity-block{padding:14px}
  .ev-wallet-mov-card{grid-template-columns:auto minmax(0,1fr)}
  .ev-wallet-mov-side{grid-column:2;text-align:left;min-width:0}
  .ev-wallet-empty{justify-content:flex-start}
  .ev-modal-login .ev-login-modal-body{padding:1.35rem 1rem 1.15rem}
}

@media(max-width:575.98px){
  .ev-wallet-title-wrap{gap:10px}
  .ev-wallet-title-icon{width:48px;height:48px;border-radius:16px}
  .ev-wallet-title{font-size:1.55rem}
  .ev-wallet-subtitle{font-size:.88rem}
  .ev-wallet-panel-head h5{font-size:1rem}
  .ev-wallet-activity-title-wrap{gap:9px}
  .ev-wallet-activity-icon{width:38px;height:38px;border-radius:13px}
  .ev-wallet-empty{align-items:flex-start}
  .ev-modal-login .ev-login-modal-footer{gap:8px;flex-direction:column-reverse}
  .ev-modal-login .ev-login-modal-footer .btn,.ev-modal-login .ev-login-modal-footer a{width:100%}
  .ev-swal-popup{padding:20px 15px 17px!important;border-radius:22px!important}.ev-swal-title{font-size:1.6rem!important}.ev-swal-confirm,.ev-swal-cancel{width:100%!important}
}

@media(prefers-reduced-motion:reduce){
  .ev-wallet-summary-card,.ev-wallet-activity-block,.ev-wallet-mov-card,.ev-wallet-btn-primary,.ev-wallet-btn-outline,.ev-wallet-btn-refresh{transition:none!important}
}
</style>
