<?php /* views/estilos/misSolicitudesServicioCompradorEstilo.php */ ?>
<style>
:root{
  --ev-ssc-verde-900:#0F592F;
  --ev-ssc-verde-700:#15803D;
  --ev-ssc-verde-600:#16A34A;
  --ev-ssc-verde-050:#F0FDF4;
  --ev-ssc-naranja:#EA7C12;
  --ev-ssc-naranja-050:#FFF7ED;
  --ev-ssc-azul:#2563EB;
  --ev-ssc-azul-050:#EFF6FF;
  --ev-ssc-rojo:#DC2626;
  --ev-ssc-rojo-050:#FEF2F2;
  --ev-ssc-texto:#111827;
  --ev-ssc-muted:#64748B;
  --ev-ssc-borde:#E5E7EB;
  --ev-ssc-sombra:0 16px 38px rgba(15,23,42,.07);
  --ev-ssc-sombra-hover:0 22px 46px rgba(15,23,42,.11);
}

.ev-ssc-page{max-width:100%;padding:14px 14px 28px;color:var(--ev-ssc-texto)}
.ev-ssc-hero,.ev-ssc-panel{border-radius:24px;border:1px solid rgba(148,163,184,.17);overflow:hidden;background:#fff;box-shadow:var(--ev-ssc-sombra)}
.ev-ssc-hero{background:radial-gradient(circle at 86% 16%,rgba(22,163,74,.13),transparent 34%),radial-gradient(circle at 12% 86%,rgba(234,124,18,.12),transparent 30%),linear-gradient(135deg,#fffdfa 0%,#f8fcf9 50%,#f2fbf5 100%)}
.ev-ssc-hero-content{padding:20px;display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap}
.ev-ssc-title-wrap{display:flex;gap:14px;align-items:flex-start;flex:1 1 520px;min-width:0}
.ev-ssc-title-icon{width:54px;height:54px;display:grid;place-items:center;border-radius:18px;background:linear-gradient(135deg,rgba(187,247,208,.96),#fff);border:1px solid rgba(22,163,74,.22);box-shadow:0 12px 24px rgba(15,23,42,.08);font-size:1.25rem;color:var(--ev-ssc-verde-900);flex:0 0 auto}
.ev-ssc-kicker{font-size:.75rem;font-weight:900;letter-spacing:.14em;color:var(--ev-ssc-naranja);margin:1px 0 5px;text-transform:uppercase}
.ev-ssc-title{font-size:2.12rem;letter-spacing:-.035em;line-height:1.04;color:var(--ev-ssc-verde-900);margin:0 0 5px;font-weight:900}
.ev-ssc-subtitle{margin:0;color:var(--ev-ssc-muted);font-size:.95rem;line-height:1.48;max-width:760px}
.ev-ssc-summary-grid{display:grid;grid-template-columns:repeat(3,minmax(120px,1fr));gap:11px;width:min(100%,465px);flex:0 1 465px}
.ev-ssc-summary-card{position:relative;border:1px solid rgba(148,163,184,.16);border-radius:18px;background:rgba(255,255,255,.91);box-shadow:0 8px 22px rgba(15,23,42,.05);padding:14px 15px;overflow:hidden}
.ev-ssc-summary-card:after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:linear-gradient(90deg,var(--ev-ssc-verde-600),var(--ev-ssc-naranja));opacity:.25}
.ev-ssc-summary-card span{display:block;color:var(--ev-ssc-muted);font-size:.78rem;font-weight:800;line-height:1.2;margin-bottom:4px}
.ev-ssc-summary-card strong{display:block;color:var(--ev-ssc-verde-900);font-size:1.45rem;line-height:1;font-weight:900}

.ev-ssc-panel-head{padding:18px;border-bottom:1px solid var(--ev-ssc-borde);display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;background:linear-gradient(180deg,#fff,#fbfdfb)}
.ev-ssc-panel-head h5{margin:0 0 4px;font-size:1.1rem;font-weight:900;color:var(--ev-ssc-verde-900)}
.ev-ssc-panel-head p{margin:0;max-width:740px;color:var(--ev-ssc-muted);font-size:.9rem;line-height:1.45}
.ev-ssc-head-actions{display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-wrap:wrap}
.ev-ssc-tabs{display:flex;gap:8px;flex-wrap:wrap;padding:7px 9px;border:1px solid rgba(148,163,184,.16);border-radius:18px;background:#fff;box-shadow:0 8px 18px rgba(15,23,42,.04)}
.ev-ssc-tab{border:1px solid rgba(22,163,74,.18);background:#fff;color:var(--ev-ssc-verde-900);font-size:.86rem;font-weight:850;border-radius:999px;padding:.63rem .86rem;transition:.16s ease}
.ev-ssc-tab span{display:inline-flex;min-width:23px;justify-content:center;margin-left:5px;padding:1px 7px;border-radius:999px;background:#fff;border:1px solid rgba(22,163,74,.17);font-size:.72rem;font-weight:900}
.ev-ssc-tab:hover{transform:translateY(-1px);background:linear-gradient(90deg,rgba(187,247,208,.55),rgba(255,255,255,.95));box-shadow:0 10px 20px rgba(15,23,42,.07)}
.ev-ssc-tab.active{background:linear-gradient(90deg,rgba(187,247,208,.78),rgba(255,255,255,.95));border-color:rgba(22,163,74,.35);box-shadow:0 10px 20px rgba(15,23,42,.07)}
.ev-ssc-btn-refresh{border:1px solid rgba(22,163,74,.2);border-radius:999px;background:#fff;color:var(--ev-ssc-verde-900);font-weight:850;padding:.72rem 1rem;box-shadow:0 8px 18px rgba(15,23,42,.05);transition:.16s ease}
.ev-ssc-btn-refresh:hover{transform:translateY(-1px);background:var(--ev-ssc-verde-050);box-shadow:0 12px 24px rgba(15,23,42,.08);color:var(--ev-ssc-verde-900)}
.ev-ssc-panel-body{padding:18px;background:linear-gradient(180deg,#FCFDFC,#F8FAF9)}
.ev-ssc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(400px,1fr));gap:18px}
.ev-ssc-alert,.ev-ssc-empty{padding:15px 16px;border-radius:18px;margin-bottom:16px;font-weight:750;text-align:center}
.ev-ssc-alert-error{background:var(--ev-ssc-rojo-050);border:1px solid #FECACA;color:#991B1B}
.ev-ssc-empty{min-height:102px;display:flex;align-items:center;justify-content:center;gap:10px;border:1px dashed #CBD5E1;background:#fff;color:#64748B}
.ev-ssc-empty i{font-size:1.25rem;color:var(--ev-ssc-verde-900)}

.ev-ssc-card{position:relative;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,#fff,#FBFCFB);border-radius:24px;overflow:hidden;box-shadow:var(--ev-ssc-sombra);transition:.17s ease}
.ev-ssc-card:before{content:"";position:absolute;top:0;left:0;bottom:0;width:4px;background:linear-gradient(180deg,var(--ev-ssc-verde-600),var(--ev-ssc-naranja));opacity:.22}
.ev-ssc-card:hover{transform:translateY(-2px);box-shadow:var(--ev-ssc-sombra-hover);border-color:rgba(22,163,74,.2)}
.ev-ssc-card-head{display:grid;grid-template-columns:88px minmax(0,1fr);gap:12px;padding:14px;border-bottom:1px solid var(--ev-ssc-borde)}
.ev-ssc-card-media{width:88px;height:88px;border-radius:18px;overflow:hidden;background:#F1F5F9;box-shadow:0 10px 22px rgba(15,23,42,.1)}
.ev-ssc-card-media img{display:block;width:100%;height:100%;object-fit:cover}
.ev-ssc-card-head-main{min-width:0}
.ev-ssc-card-title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:6px}
.ev-ssc-card-title{font-size:1.03rem;line-height:1.17;color:var(--ev-ssc-verde-900);font-weight:900;min-width:0;overflow-wrap:anywhere}
.ev-ssc-card-meta{color:var(--ev-ssc-muted);font-size:.79rem;line-height:1.35}
.ev-ssc-badge{display:inline-flex;align-items:center;flex:0 0 auto;border-radius:999px;padding:6px 10px;border:1px solid transparent;font-size:.72rem;font-weight:900;white-space:nowrap}
.ev-ssc-badge-pending{background:#FFF5D6;border-color:#F5D18D;color:#9A5B03}
.ev-ssc-badge-wait{background:var(--ev-ssc-azul-050);border-color:#BFDBFE;color:#1D4ED8}
.ev-ssc-badge-success{background:var(--ev-ssc-verde-050);border-color:#BBF7D0;color:#166534}
.ev-ssc-badge-negative{background:var(--ev-ssc-rojo-050);border-color:#FECACA;color:#991B1B}
.ev-ssc-pills{display:flex;flex-wrap:wrap;gap:7px;margin-top:8px}
.ev-ssc-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid rgba(22,163,74,.15);border-radius:999px;background:linear-gradient(90deg,#F0FDF4,#fff);color:#355B4A;font-size:.74rem;font-weight:800}
.ev-ssc-pill i{color:var(--ev-ssc-verde-600)}
.ev-ssc-card-body{padding:13px 14px 14px}
.ev-ssc-card-data{display:grid;grid-template-columns:minmax(0,1fr) minmax(115px,.85fr);gap:9px}
.ev-ssc-data{border:1px solid #E9EEF5;border-radius:15px;background:#fff;padding:10px 11px;min-height:64px}
.ev-ssc-data span{display:block;font-size:.72rem;color:var(--ev-ssc-muted);font-weight:800;margin-bottom:3px}
.ev-ssc-data strong{display:block;font-size:.9rem;color:var(--ev-ssc-texto);line-height:1.25;overflow-wrap:anywhere}
.ev-ssc-data-price{background:linear-gradient(180deg,#F0FDF4,#fff);border-color:rgba(22,163,74,.16)}
.ev-ssc-data-price strong{font-size:1.1rem;font-weight:900;color:var(--ev-ssc-verde-900)}
.ev-ssc-info{margin-top:11px;border:1px solid #E9EEF5;border-radius:17px;background:#fff;padding:11px}
.ev-ssc-line{display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-bottom:1px solid #F1F5F9;font-size:.85rem}
.ev-ssc-line:last-child{border-bottom:0}
.ev-ssc-line-label{color:var(--ev-ssc-muted);font-weight:800}
.ev-ssc-line-value{color:var(--ev-ssc-texto);font-weight:800;text-align:right;max-width:65%;overflow-wrap:anywhere}
.ev-ssc-note{margin-top:10px;padding:10px 11px;border:1px solid #E5E7EB;border-radius:15px;background:#F8FAFC}
.ev-ssc-note-label{display:block;color:var(--ev-ssc-verde-900);font-size:.71rem;font-weight:900;margin-bottom:4px}
.ev-ssc-note-text{font-size:.83rem;color:#475569;line-height:1.45;white-space:pre-wrap;overflow-wrap:anywhere}
.ev-ssc-proposal{margin-top:11px;border:1px solid rgba(234,124,18,.24);border-radius:18px;background:linear-gradient(180deg,#FFF9EF,#fff);padding:12px}
.ev-ssc-proposal-title{display:flex;align-items:center;gap:7px;color:#9A3412;font-size:.84rem;font-weight:900;margin-bottom:8px}
.ev-ssc-proposal-title i{color:var(--ev-ssc-naranja)}
.ev-ssc-proposal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.ev-ssc-proposal-box{border:1px solid rgba(234,124,18,.16);border-radius:14px;background:#fff;padding:9px}
.ev-ssc-proposal-box span{display:block;font-size:.7rem;color:#8A5C26;font-weight:850;margin-bottom:3px}
.ev-ssc-proposal-box strong{display:block;font-size:.84rem;line-height:1.35;color:#3E2D17;overflow-wrap:anywhere}
.ev-ssc-proposal-text{margin-top:9px;font-size:.83rem;color:#5B4630;line-height:1.48;white-space:pre-wrap;overflow-wrap:anywhere}
.ev-ssc-state{margin-top:11px;border-radius:17px;border:1px solid transparent;padding:11px 12px}
.ev-ssc-state-title{font-size:.88rem;font-weight:900;margin-bottom:3px}
.ev-ssc-state-text{font-size:.83rem;line-height:1.45;color:#475569;white-space:pre-wrap;overflow-wrap:anywhere}
.ev-ssc-state-pending{background:#FFF9EC;border-color:#FCD9BD}
.ev-ssc-state-wait{background:#F0F9FF;border-color:#BAE6FD}
.ev-ssc-state-success{background:#F6FBF8;border-color:#D7F0E0}
.ev-ssc-state-negative{background:var(--ev-ssc-rojo-050);border-color:#FECACA}
.ev-ssc-state-negative .ev-ssc-state-title{color:#991B1B}
.ev-ssc-time{display:inline-flex;align-items:center;gap:7px;margin-top:8px;padding:6px 10px;border-radius:999px;background:#fff;border:1px solid #FCD9BD;color:#B45309;font-weight:900;font-size:.75rem;font-variant-numeric:tabular-nums}
.ev-ssc-actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:13px}
.ev-ssc-actions .btn{border-radius:14px;padding:.72rem .9rem;font-size:.85rem;font-weight:850;transition:.16s ease}
.ev-ssc-btn-accept{border:0;background:linear-gradient(135deg,var(--ev-ssc-verde-700),#22C55E);box-shadow:0 12px 24px rgba(22,163,74,.18);color:#fff}
.ev-ssc-btn-answer{border:0;background:linear-gradient(135deg,var(--ev-ssc-naranja),#F59E0B);box-shadow:0 12px 24px rgba(234,124,18,.2);color:#fff}
.ev-ssc-btn-outline{background:#fff;border:1px solid rgba(22,163,74,.2);color:var(--ev-ssc-verde-900)}
.ev-ssc-btn-adjust{background:var(--ev-ssc-azul-050);border:1px solid #BFDBFE;color:#1D4ED8}
.ev-ssc-btn-danger{background:#FFF1F2;border:1px solid #FECACA;color:#B91C1C}
.ev-ssc-actions .btn:hover{transform:translateY(-1px);filter:brightness(1.02)}
.ev-ssc-btn-outline:hover{background:var(--ev-ssc-verde-050);color:var(--ev-ssc-verde-900)}
.ev-ssc-btn-danger:hover{background:linear-gradient(135deg,#DC2626,#B91C1C);border-color:#DC2626;color:#fff}

.ev-ssc-swal-popup{border-radius:28px!important;padding:26px 22px 20px!important;border:1px solid #E5E7EB!important;box-shadow:0 28px 70px rgba(15,23,42,.2)!important;background:#fff!important}
.ev-ssc-swal-title{color:var(--ev-ssc-verde-900)!important;font-weight:900!important;font-size:1.95rem!important;letter-spacing:-.03em!important}
.ev-ssc-swal-html{color:var(--ev-ssc-muted)!important;font-size:.96rem!important}
.ev-ssc-swal-confirm{background:linear-gradient(135deg,var(--ev-ssc-naranja),#F59E0B)!important;color:#fff!important;border:0!important;border-radius:15px!important;padding:12px 21px!important;font-weight:900!important;box-shadow:0 12px 25px rgba(234,124,18,.28)!important}
.ev-ssc-swal-cancel{background:#fff!important;color:#374151!important;border:1px solid #E5E7EB!important;border-radius:15px!important;padding:12px 21px!important;font-weight:900!important}
.ev-ssc-swal-loader{width:58px;height:58px;margin:5px auto 15px;border:5px solid rgba(22,163,74,.16);border-top-color:var(--ev-ssc-verde-900);border-radius:50%;animation:evSscSpin .85s linear infinite}
@keyframes evSscSpin{to{transform:rotate(360deg)}}
.ev-ssc-form{display:grid;gap:12px;text-align:left}
.ev-ssc-form-note{border:1px solid rgba(234,124,18,.22);border-radius:16px;background:var(--ev-ssc-naranja-050);color:#9A3412;padding:10px 12px;font-size:.82rem;line-height:1.45}
.ev-ssc-form textarea.form-control{min-height:126px;resize:vertical;border-color:#DCE4EE;border-radius:13px;font-size:.9rem}
.ev-ssc-detail{display:grid;gap:13px;text-align:left}
.ev-ssc-detail-top{display:grid;grid-template-columns:120px minmax(0,1fr);gap:14px}
.ev-ssc-detail-media{width:120px;height:120px;border:1px solid #E5E7EB;border-radius:20px;overflow:hidden;background:#F8FAFC}
.ev-ssc-detail-media img{width:100%;height:100%;object-fit:cover}
.ev-ssc-detail-title{font-size:1.15rem;font-weight:900;color:var(--ev-ssc-verde-900);line-height:1.2}
.ev-ssc-detail-sub{font-size:.85rem;color:var(--ev-ssc-muted);margin-top:3px}
.ev-ssc-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
.ev-ssc-detail-box{border:1px solid #E9EEF5;border-radius:15px;padding:10px;background:#fff}
.ev-ssc-detail-box span{display:block;font-size:.72rem;color:var(--ev-ssc-muted);font-weight:800;margin-bottom:3px}
.ev-ssc-detail-box strong{font-size:.87rem;overflow-wrap:anywhere;color:var(--ev-ssc-texto)}
.ev-ssc-detail-section{border:1px solid #E9EEF5;border-radius:17px;padding:12px;background:#fff}
.ev-ssc-detail-section h6{font-size:.82rem;font-weight:900;color:var(--ev-ssc-verde-900);margin:0 0 6px}
.ev-ssc-detail-section p{font-size:.87rem;line-height:1.5;margin:0;color:#475569;white-space:pre-wrap;overflow-wrap:anywhere}


.ev-ssc-update-alert{
  display:inline-flex;
  align-items:center;
  gap:7px;
  max-width:100%;
  margin-top:9px;
  padding:7px 10px;
  border-radius:999px;
  border:1px solid #BFDBFE;
  background:linear-gradient(90deg,#EFF6FF,#fff);
  color:#1D4ED8;
  font-size:.75rem;
  font-weight:900;
  line-height:1.15;
  box-shadow:0 7px 16px rgba(15,23,42,.045);
}
.ev-ssc-update-alert i{font-size:.84rem;flex:0 0 auto}
.ev-ssc-update-alert span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ev-ssc-update-alert strong{
  min-width:21px;height:21px;padding:0 5px;display:inline-flex;align-items:center;justify-content:center;
  border-radius:999px;background:#2563EB;color:#fff;font-size:.67rem;font-weight:950;flex:0 0 auto;
}
.ev-ssc-update-alert.is-warning{border-color:#FED7AA;background:linear-gradient(90deg,#FFF7ED,#fff);color:#9A3412}
.ev-ssc-update-alert.is-warning strong{background:#EA7C12}
.ev-ssc-update-alert.is-danger{border-color:#FECACA;background:linear-gradient(90deg,#FEF2F2,#fff);color:#991B1B}
.ev-ssc-update-alert.is-danger strong{background:#DC2626}
.ev-ssc-update-alert.is-success{border-color:#BBF7D0;background:linear-gradient(90deg,#F0FDF4,#fff);color:#166534}
.ev-ssc-update-alert.is-success strong{background:#16A34A}
.ev-ssc-update-alert.is-neutral{border-color:#E2E8F0;background:linear-gradient(90deg,#F8FAFC,#fff);color:#475569}
.ev-ssc-update-alert.is-neutral strong{background:#64748B}

@media(max-width:1199.98px){.ev-ssc-head-actions{justify-content:flex-start;width:100%}.ev-ssc-grid{grid-template-columns:repeat(auto-fill,minmax(360px,1fr))}}
@media(max-width:991.98px){.ev-ssc-summary-grid{width:100%;flex:1 1 100%}.ev-ssc-grid{grid-template-columns:1fr}}
@media(max-width:767.98px){.ev-ssc-page{padding:10px 10px 22px}.ev-ssc-hero-content,.ev-ssc-panel-head,.ev-ssc-panel-body{padding:14px}.ev-ssc-title{font-size:1.72rem}.ev-ssc-summary-grid{grid-template-columns:1fr}.ev-ssc-head-actions{align-items:stretch}.ev-ssc-tabs{width:100%}.ev-ssc-tab{flex:1 1 160px}.ev-ssc-btn-refresh{width:100%}.ev-ssc-card-head{grid-template-columns:78px minmax(0,1fr)}.ev-ssc-card-media{width:78px;height:78px}.ev-ssc-card-title-row{flex-direction:column}.ev-ssc-card-data,.ev-ssc-proposal-grid{grid-template-columns:1fr}.ev-ssc-line{flex-direction:column;gap:4px}.ev-ssc-line-value{text-align:left;max-width:100%}.ev-ssc-actions{flex-direction:column}.ev-ssc-actions .btn{width:100%}.ev-ssc-detail-grid{grid-template-columns:1fr}.ev-ssc-detail-top{grid-template-columns:1fr}.ev-ssc-detail-media{width:100%;height:190px}}
@media(max-width:575.98px){.ev-ssc-swal-popup{padding:20px 15px 17px!important;border-radius:22px!important}.ev-ssc-swal-title{font-size:1.65rem!important}.ev-ssc-swal-confirm,.ev-ssc-swal-cancel{width:100%!important}}
</style>
