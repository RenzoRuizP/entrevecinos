<?php /* views/estilos/misSolicitudesServicioVendedorEstilo.php */ ?>
<style>
:root{
  --ev-ssv-verde-900:#0F592F;
  --ev-ssv-verde-700:#15803D;
  --ev-ssv-verde-600:#16A34A;
  --ev-ssv-verde-100:#DCFCE7;
  --ev-ssv-verde-050:#F0FDF4;
  --ev-ssv-naranja:#EA7C12;
  --ev-ssv-naranja-oscuro:#C46B05;
  --ev-ssv-naranja-050:#FFF7ED;
  --ev-ssv-rojo:#DC2626;
  --ev-ssv-rojo-050:#FEF2F2;
  --ev-ssv-azul:#2563EB;
  --ev-ssv-azul-050:#EFF6FF;
  --ev-ssv-texto:#111827;
  --ev-ssv-muted:#64748B;
  --ev-ssv-borde:#E5E7EB;
  --ev-ssv-fondo:#F8FAFC;
  --ev-ssv-shadow:0 16px 38px rgba(15,23,42,.07);
  --ev-ssv-shadow-hover:0 22px 46px rgba(15,23,42,.11);
}

.ev-ssv-page{max-width:100%;padding:14px 14px 28px;color:var(--ev-ssv-texto)}
.ev-ssv-hero,.ev-ssv-panel{border-radius:24px;border:1px solid rgba(148,163,184,.17);overflow:hidden;background:#fff;box-shadow:var(--ev-ssv-shadow)}
.ev-ssv-hero{background:radial-gradient(circle at 86% 16%,rgba(22,163,74,.13),transparent 34%),radial-gradient(circle at 12% 86%,rgba(234,124,18,.12),transparent 30%),linear-gradient(135deg,#fffdfa 0%,#f8fcf9 50%,#f2fbf5 100%)}
.ev-ssv-hero-content{padding:20px;display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap}
.ev-ssv-title-wrap{display:flex;gap:14px;align-items:flex-start;flex:1 1 520px;min-width:0}
.ev-ssv-title-icon{width:54px;height:54px;display:grid;place-items:center;border-radius:18px;background:linear-gradient(135deg,rgba(187,247,208,.96),#fff);border:1px solid rgba(22,163,74,.22);box-shadow:0 12px 24px rgba(15,23,42,.08);font-size:1.25rem;color:var(--ev-ssv-verde-900);flex:0 0 auto}
.ev-ssv-kicker{font-size:.75rem;font-weight:900;letter-spacing:.14em;color:var(--ev-ssv-naranja);margin:1px 0 5px;text-transform:uppercase}
.ev-ssv-title{font-size:2.12rem;letter-spacing:-.035em;line-height:1.04;color:var(--ev-ssv-verde-900);margin:0 0 5px;font-weight:900}
.ev-ssv-subtitle{margin:0;color:var(--ev-ssv-muted);font-size:.95rem;line-height:1.48;max-width:760px}
.ev-ssv-summary-grid{display:grid;grid-template-columns:repeat(3,minmax(120px,1fr));gap:11px;width:min(100%,465px);flex:0 1 465px}
.ev-ssv-summary-card{position:relative;border:1px solid rgba(148,163,184,.16);border-radius:18px;background:rgba(255,255,255,.91);box-shadow:0 8px 22px rgba(15,23,42,.05);padding:14px 15px;overflow:hidden}
.ev-ssv-summary-card:after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:linear-gradient(90deg,var(--ev-ssv-verde-600),var(--ev-ssv-naranja));opacity:.25}
.ev-ssv-summary-card span{display:block;color:var(--ev-ssv-muted);font-size:.78rem;font-weight:800;line-height:1.2;margin-bottom:4px}
.ev-ssv-summary-card strong{display:block;color:var(--ev-ssv-verde-900);font-size:1.45rem;line-height:1;font-weight:900}

.ev-ssv-panel-head{padding:18px;border-bottom:1px solid var(--ev-ssv-borde);display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;background:linear-gradient(180deg,#fff,#fbfdfb)}
.ev-ssv-panel-head h5{margin:0 0 4px;font-size:1.1rem;font-weight:900;color:var(--ev-ssv-verde-900)}
.ev-ssv-panel-head p{margin:0;max-width:740px;color:var(--ev-ssv-muted);font-size:.9rem;line-height:1.45}
.ev-ssv-head-actions{display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-wrap:wrap}
.ev-ssv-tabs{display:flex;gap:8px;flex-wrap:wrap;padding:7px 9px;border:1px solid rgba(148,163,184,.16);border-radius:18px;background:#fff;box-shadow:0 8px 18px rgba(15,23,42,.04)}
.ev-ssv-tab{border:1px solid rgba(22,163,74,.18);background:#fff;color:var(--ev-ssv-verde-900);font-size:.86rem;font-weight:850;border-radius:999px;padding:.63rem .86rem;transition:.16s ease}
.ev-ssv-tab span{display:inline-flex;min-width:23px;justify-content:center;margin-left:5px;padding:1px 7px;border-radius:999px;background:#fff;border:1px solid rgba(22,163,74,.17);font-size:.72rem;font-weight:900}
.ev-ssv-tab:hover{transform:translateY(-1px);background:linear-gradient(90deg,rgba(187,247,208,.55),rgba(255,255,255,.95));box-shadow:0 10px 20px rgba(15,23,42,.07)}
.ev-ssv-tab.active{background:linear-gradient(90deg,rgba(187,247,208,.78),rgba(255,255,255,.95));border-color:rgba(22,163,74,.35);box-shadow:0 10px 20px rgba(15,23,42,.07)}
.ev-ssv-btn-refresh{border:1px solid rgba(22,163,74,.2);border-radius:999px;background:#fff;color:var(--ev-ssv-verde-900);font-weight:850;padding:.72rem 1rem;box-shadow:0 8px 18px rgba(15,23,42,.05);transition:.16s ease}
.ev-ssv-btn-refresh:hover{transform:translateY(-1px);background:var(--ev-ssv-verde-050);box-shadow:0 12px 24px rgba(15,23,42,.08);color:var(--ev-ssv-verde-900)}
.ev-ssv-panel-body{padding:18px;background:linear-gradient(180deg,#FCFDFC,#F8FAF9)}
.ev-ssv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(400px,1fr));gap:18px}
.ev-ssv-alert,.ev-ssv-empty{padding:15px 16px;border-radius:18px;margin-bottom:16px;font-weight:750;text-align:center}
.ev-ssv-alert-error{background:var(--ev-ssv-rojo-050);border:1px solid #FECACA;color:#991B1B}
.ev-ssv-empty{min-height:102px;display:flex;align-items:center;justify-content:center;gap:10px;border:1px dashed #CBD5E1;background:#fff;color:#64748B}
.ev-ssv-empty i{font-size:1.25rem;color:var(--ev-ssv-verde-900)}

.ev-ssv-card{position:relative;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,#fff,#FBFCFB);border-radius:24px;overflow:hidden;box-shadow:var(--ev-ssv-shadow);transition:.17s ease}
.ev-ssv-card:before{content:"";position:absolute;top:0;left:0;bottom:0;width:4px;background:linear-gradient(180deg,var(--ev-ssv-verde-600),var(--ev-ssv-naranja));opacity:.22}
.ev-ssv-card:hover{transform:translateY(-2px);box-shadow:var(--ev-ssv-shadow-hover);border-color:rgba(22,163,74,.2)}
.ev-ssv-card-head{display:grid;grid-template-columns:88px minmax(0,1fr);gap:12px;padding:14px;border-bottom:1px solid var(--ev-ssv-borde)}
.ev-ssv-card-media{width:88px;height:88px;border-radius:18px;overflow:hidden;background:#F1F5F9;box-shadow:0 10px 22px rgba(15,23,42,.1)}
.ev-ssv-card-media img{display:block;width:100%;height:100%;object-fit:cover}
.ev-ssv-card-head-main{min-width:0}
.ev-ssv-card-title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:6px}
.ev-ssv-card-title{font-size:1.03rem;line-height:1.17;color:var(--ev-ssv-verde-900);font-weight:900;min-width:0;overflow-wrap:anywhere}
.ev-ssv-card-meta{color:var(--ev-ssv-muted);font-size:.79rem;line-height:1.35}
.ev-ssv-badge{display:inline-flex;align-items:center;flex:0 0 auto;border-radius:999px;padding:6px 10px;border:1px solid transparent;font-size:.72rem;font-weight:900;white-space:nowrap}
.ev-ssv-badge-pending{background:#FFF5D6;border-color:#F5D18D;color:#9A5B03}.ev-ssv-badge-wait{background:var(--ev-ssv-azul-050);border-color:#BFDBFE;color:#1D4ED8}.ev-ssv-badge-success{background:var(--ev-ssv-verde-050);border-color:#BBF7D0;color:#166534}.ev-ssv-badge-negative{background:var(--ev-ssv-rojo-050);border-color:#FECACA;color:#991B1B}
.ev-ssv-pills{display:flex;flex-wrap:wrap;gap:7px;margin-top:8px}.ev-ssv-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid rgba(22,163,74,.15);border-radius:999px;background:linear-gradient(90deg,#F0FDF4,#fff);color:#355B4A;font-size:.74rem;font-weight:800}.ev-ssv-pill i{color:var(--ev-ssv-verde-600)}
.ev-ssv-card-body{padding:13px 14px 14px}.ev-ssv-card-data{display:grid;grid-template-columns:minmax(0,1fr) minmax(115px,.85fr);gap:9px}.ev-ssv-data{border:1px solid #E9EEF5;border-radius:15px;background:#fff;padding:10px 11px;min-height:64px}.ev-ssv-data span{display:block;font-size:.72rem;color:var(--ev-ssv-muted);font-weight:800;margin-bottom:3px}.ev-ssv-data strong{display:block;font-size:.9rem;color:var(--ev-ssv-texto);line-height:1.25;overflow-wrap:anywhere}.ev-ssv-data-price{background:linear-gradient(180deg,#F0FDF4,#fff);border-color:rgba(22,163,74,.16)}.ev-ssv-data-price strong{font-size:1.1rem;font-weight:900;color:var(--ev-ssv-verde-900)}
.ev-ssv-info{margin-top:11px;border:1px solid #E9EEF5;border-radius:17px;background:#fff;padding:11px}.ev-ssv-line{display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-bottom:1px solid #F1F5F9;font-size:.85rem}.ev-ssv-line:last-child{border-bottom:0}.ev-ssv-line-label{color:var(--ev-ssv-muted);font-weight:800}.ev-ssv-line-value{color:var(--ev-ssv-texto);font-weight:800;text-align:right;max-width:65%;overflow-wrap:anywhere}
.ev-ssv-note{margin-top:10px;padding:10px 11px;border:1px solid #E5E7EB;border-radius:15px;background:#F8FAFC}.ev-ssv-note-label{display:block;color:var(--ev-ssv-verde-900);font-size:.71rem;font-weight:900;margin-bottom:4px}.ev-ssv-note-text{font-size:.83rem;color:#475569;line-height:1.45;overflow-wrap:anywhere}
.ev-ssv-state{margin-top:11px;border-radius:17px;border:1px solid transparent;padding:11px 12px}.ev-ssv-state-title{font-size:.88rem;font-weight:900;margin-bottom:3px}.ev-ssv-state-text{font-size:.83rem;line-height:1.45;color:#475569}.ev-ssv-state-pending{background:#FFF9EC;border-color:#FCD9BD}.ev-ssv-state-wait{background:#F0F9FF;border-color:#BAE6FD}.ev-ssv-state-success{background:#F6FBF8;border-color:#D7F0E0}.ev-ssv-state-negative{background:var(--ev-ssv-rojo-050);border-color:#FECACA}.ev-ssv-state-negative .ev-ssv-state-title{color:#991B1B}
.ev-ssv-time{display:inline-flex;align-items:center;gap:7px;margin-top:8px;padding:6px 10px;border-radius:999px;background:#fff;border:1px solid #FCD9BD;color:#B45309;font-weight:900;font-size:.75rem;font-variant-numeric:tabular-nums}
.ev-ssv-actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:13px}.ev-ssv-actions .btn{border-radius:14px;padding:.72rem .9rem;font-size:.85rem;font-weight:850;transition:.16s ease}.ev-ssv-btn-proposal{border:0;background:linear-gradient(135deg,var(--ev-ssv-naranja),#F59E0B);box-shadow:0 12px 24px rgba(234,124,18,.2);color:#fff}.ev-ssv-btn-info{border:0;background:linear-gradient(135deg,var(--ev-ssv-verde-600),#22C55E);box-shadow:0 12px 24px rgba(22,163,74,.18);color:#fff}.ev-ssv-btn-outline{background:#fff;border:1px solid rgba(22,163,74,.2);color:var(--ev-ssv-verde-900)}.ev-ssv-btn-danger{background:#FFF1F2;border:1px solid #FECACA;color:#B91C1C}.ev-ssv-actions .btn:hover{transform:translateY(-1px);filter:brightness(1.02)}.ev-ssv-btn-outline:hover{background:var(--ev-ssv-verde-050);color:var(--ev-ssv-verde-900)}.ev-ssv-btn-danger:hover{background:linear-gradient(135deg,#DC2626,#B91C1C);border-color:#DC2626;color:#fff}

.ev-ssv-swal-popup{border-radius:28px!important;padding:26px 22px 20px!important;border:1px solid #E5E7EB!important;box-shadow:0 28px 70px rgba(15,23,42,.2)!important;background:#fff!important}.ev-ssv-swal-title{color:var(--ev-ssv-verde-900)!important;font-weight:900!important;font-size:1.95rem!important;letter-spacing:-.03em!important}.ev-ssv-swal-html{color:var(--ev-ssv-muted)!important;font-size:.96rem!important}.ev-ssv-swal-confirm{background:linear-gradient(135deg,var(--ev-ssv-naranja),#F59E0B)!important;color:#fff!important;border:0!important;border-radius:15px!important;padding:12px 21px!important;font-weight:900!important;box-shadow:0 12px 25px rgba(234,124,18,.28)!important}.ev-ssv-swal-cancel{background:#fff!important;color:#374151!important;border:1px solid #E5E7EB!important;border-radius:15px!important;padding:12px 21px!important;font-weight:900!important}.ev-ssv-swal-loader{width:58px;height:58px;margin:5px auto 15px;border:5px solid rgba(22,163,74,.16);border-top-color:var(--ev-ssv-verde-900);border-radius:50%;animation:evSsvSpin .85s linear infinite}@keyframes evSsvSpin{to{transform:rotate(360deg)}}
.ev-ssv-form{display:grid;gap:12px;text-align:left}.ev-ssv-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.ev-ssv-field{display:grid;gap:5px}.ev-ssv-field label{color:#334155;font-size:.81rem;font-weight:900}.ev-ssv-field label small{font-weight:700;color:#94A3B8}.ev-ssv-field .form-control,.ev-ssv-field .form-select{border-color:#DCE4EE;border-radius:13px;min-height:41px;font-size:.9rem}.ev-ssv-field textarea.form-control{min-height:84px;resize:vertical}.ev-ssv-form-note{border:1px solid rgba(234,124,18,.22);border-radius:16px;background:var(--ev-ssv-naranja-050);color:#9A3412;padding:10px 12px;font-size:.82rem;line-height:1.45}.ev-ssv-detail{display:grid;gap:13px;text-align:left}.ev-ssv-detail-top{display:grid;grid-template-columns:120px minmax(0,1fr);gap:14px}.ev-ssv-detail-media{width:120px;height:120px;border:1px solid #E5E7EB;border-radius:20px;overflow:hidden;background:#F8FAFC}.ev-ssv-detail-media img{width:100%;height:100%;object-fit:cover}.ev-ssv-detail-title{font-size:1.15rem;font-weight:900;color:var(--ev-ssv-verde-900);line-height:1.2}.ev-ssv-detail-sub{font-size:.85rem;color:var(--ev-ssv-muted);margin-top:3px}.ev-ssv-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.ev-ssv-detail-box{border:1px solid #E9EEF5;border-radius:15px;padding:10px;background:#fff}.ev-ssv-detail-box span{display:block;font-size:.72rem;color:var(--ev-ssv-muted);font-weight:800;margin-bottom:3px}.ev-ssv-detail-box strong{font-size:.87rem;overflow-wrap:anywhere;color:var(--ev-ssv-texto)}.ev-ssv-detail-section{border:1px solid #E9EEF5;border-radius:17px;padding:12px;background:#fff}.ev-ssv-detail-section h6{font-size:.82rem;font-weight:900;color:var(--ev-ssv-verde-900);margin:0 0 6px}.ev-ssv-detail-section p{font-size:.87rem;line-height:1.5;margin:0;color:#475569;white-space:pre-wrap;overflow-wrap:anywhere}

@media(max-width:1199.98px){.ev-ssv-head-actions{justify-content:flex-start;width:100%}.ev-ssv-grid{grid-template-columns:repeat(auto-fill,minmax(360px,1fr))}}
@media(max-width:991.98px){.ev-ssv-summary-grid{width:100%;flex:1 1 100%}.ev-ssv-grid{grid-template-columns:1fr}}
@media(max-width:767.98px){.ev-ssv-page{padding:10px 10px 22px}.ev-ssv-hero-content,.ev-ssv-panel-head,.ev-ssv-panel-body{padding:14px}.ev-ssv-title{font-size:1.72rem}.ev-ssv-summary-grid{grid-template-columns:1fr}.ev-ssv-head-actions{align-items:stretch}.ev-ssv-tabs{width:100%}.ev-ssv-tab{flex:1 1 160px}.ev-ssv-btn-refresh{width:100%}.ev-ssv-card-head{grid-template-columns:78px minmax(0,1fr)}.ev-ssv-card-media{width:78px;height:78px}.ev-ssv-card-title-row{flex-direction:column}.ev-ssv-card-data{grid-template-columns:1fr}.ev-ssv-line{flex-direction:column;gap:4px}.ev-ssv-line-value{text-align:left;max-width:100%}.ev-ssv-actions{flex-direction:column}.ev-ssv-actions .btn{width:100%}.ev-ssv-form-grid,.ev-ssv-detail-grid{grid-template-columns:1fr}.ev-ssv-detail-top{grid-template-columns:1fr}.ev-ssv-detail-media{width:100%;height:190px}}
@media(max-width:575.98px){.ev-ssv-swal-popup{padding:20px 15px 17px!important;border-radius:22px!important}.ev-ssv-swal-title{font-size:1.65rem!important}.ev-ssv-swal-confirm,.ev-ssv-swal-cancel{width:100%!important}}
</style>
