<style>
:root{--dg-green:#0F592F;--dg-green2:#0E7A43;--dg-light:#16A34A;--dg-orange:#EA7C12;--dg-text:#111827;--dg-muted:#64748B;--dg-border:#E2E8F0;--dg-bg:#F8FAFC;--dg-white:#fff;--dg-shadow:0 16px 42px rgba(15,23,42,.075)}
.ev-dg-page,.ev-dg-page *{box-sizing:border-box}.ev-dg-page{width:100%;padding:18px;color:var(--dg-text)}
.ev-dg-hero{position:relative;overflow:hidden;display:flex;justify-content:space-between;gap:22px;align-items:center;padding:24px 26px;border:1px solid rgba(148,163,184,.2);border-radius:24px;background:radial-gradient(circle at 88% 18%,rgba(22,163,74,.14),transparent 34%),radial-gradient(circle at 5% 100%,rgba(234,124,18,.11),transparent 30%),#fff;box-shadow:var(--dg-shadow)}
.ev-dg-hero:before{content:"";position:absolute;inset:0 0 auto;height:4px;background:linear-gradient(90deg,var(--dg-green),var(--dg-light),var(--dg-orange))}.ev-dg-hero-copy{display:flex;align-items:center;gap:15px;min-width:0}.ev-dg-icon{width:54px;height:54px;display:grid;place-items:center;flex:0 0 auto;border-radius:18px;background:#DCFCE7;color:var(--dg-green);border:1px solid rgba(22,163,74,.18);font-size:1.45rem}.ev-dg-kicker{color:var(--dg-orange);font-size:.72rem;font-weight:900;letter-spacing:.1em}.ev-dg-hero h1{margin:3px 0 5px;color:var(--dg-green);font-size:clamp(1.65rem,2.6vw,2.2rem);line-height:1.05;font-weight:950;letter-spacing:-.04em}.ev-dg-hero p{margin:0;color:var(--dg-muted);max-width:760px;line-height:1.5}.ev-dg-period-badge{min-width:215px;padding:13px 16px;border-radius:17px;background:rgba(255,255,255,.88);border:1px solid rgba(22,163,74,.14);box-shadow:0 10px 24px rgba(15,23,42,.05)}.ev-dg-period-badge span,.ev-dg-period-badge small{display:block;color:var(--dg-muted);font-size:.7rem;font-weight:800}.ev-dg-period-badge strong{display:block;margin:3px 0;color:var(--dg-green);font-weight:950}
.ev-dg-filter-card,.ev-dg-card{margin-top:14px;border-radius:20px;background:#fff;border:1px solid rgba(148,163,184,.18);box-shadow:0 12px 32px rgba(15,23,42,.055)}.ev-dg-filter-card{padding:17px}.ev-dg-filter-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.ev-dg-filter-head>div{display:flex;align-items:center;gap:8px}.ev-dg-filter-head i{color:var(--dg-green)}.ev-dg-filter-head strong{color:var(--dg-green);font-weight:950}.ev-dg-filter-head span{color:var(--dg-muted);font-size:.8rem}.ev-dg-filter-grid{display:grid;grid-template-columns:repeat(6,minmax(125px,1fr));gap:11px;align-items:end}.ev-dg-filter-grid label,.ev-dg-goal-dialog label{display:grid;gap:6px;min-width:0}.ev-dg-filter-grid label>span,.ev-dg-goal-dialog label>span{font-size:.75rem;font-weight:900;color:#334155}.ev-dg-filter-grid select,.ev-dg-filter-grid input,.ev-dg-goal-dialog input{width:100%;min-height:42px;padding:8px 11px;border:1px solid #CFE9D9;border-radius:12px;background:#fff;color:var(--dg-text);outline:0}.ev-dg-filter-grid select:focus,.ev-dg-filter-grid input:focus,.ev-dg-goal-dialog input:focus{border-color:var(--dg-light);box-shadow:0 0 0 3px rgba(22,163,74,.12)}.ev-dg-btn{min-height:40px;padding:9px 14px;display:inline-flex;align-items:center;justify-content:center;gap:7px;border-radius:13px;border:1px solid transparent;font-weight:900;white-space:nowrap;transition:.16s ease}.ev-dg-btn-primary{color:#fff;background:linear-gradient(135deg,var(--dg-orange),#F59E0B);box-shadow:0 10px 22px rgba(234,124,18,.18)}.ev-dg-btn-primary:hover{transform:translateY(-1px);filter:brightness(1.02)}.ev-dg-btn-light{color:var(--dg-green);background:#fff;border-color:#D7E2DB}.ev-dg-btn-light:hover{border-color:rgba(234,124,18,.35);color:var(--dg-orange)}
.ev-dg-loading{display:flex;align-items:center;justify-content:center;gap:11px;min-height:120px;margin-top:14px;color:var(--dg-green);font-weight:900}.ev-dg-loading span{width:24px;height:24px;border:3px solid #DCFCE7;border-top-color:var(--dg-green);border-radius:50%;animation:dgSpin .8s linear infinite}@keyframes dgSpin{to{transform:rotate(360deg)}}.ev-dg-error{margin-top:14px;padding:16px;display:flex;gap:12px;border:1px solid #FECACA;border-radius:17px;background:#FEF2F2;color:#991B1B}.ev-dg-error p{margin:3px 0 0}
.ev-dg-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:14px}.ev-dg-kpi{min-height:118px;padding:15px;display:flex;gap:12px;align-items:flex-start;border-radius:18px;background:#fff;border:1px solid rgba(148,163,184,.18);box-shadow:0 10px 28px rgba(15,23,42,.05);transition:.18s ease}.ev-dg-kpi:hover{transform:translateY(-2px);border-color:rgba(234,124,18,.28);box-shadow:0 16px 34px rgba(234,124,18,.09)}.ev-dg-kpi-icon{width:42px;height:42px;display:grid;place-items:center;flex:0 0 auto;border-radius:14px;background:#ECFDF3;color:var(--dg-green);font-size:1.05rem}.ev-dg-kpi-income .ev-dg-kpi-icon{background:#FFF7ED;color:var(--dg-orange)}.ev-dg-kpi small{display:block;color:var(--dg-muted);font-size:.72rem;font-weight:850}.ev-dg-kpi strong{display:block;margin:4px 0;color:var(--dg-green);font-size:1.35rem;line-height:1;font-weight:950}.ev-dg-kpi em{display:block;color:#94A3B8;font-size:.68rem;font-style:normal;line-height:1.35}
.ev-dg-grid{display:grid;gap:14px;margin-top:14px}.ev-dg-grid-charts{grid-template-columns:1.25fr 1fr}.ev-dg-grid-goal{grid-template-columns:1.2fr .8fr}.ev-dg-card{padding:17px}.ev-dg-card>header{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}.ev-dg-card>header>div{display:flex;align-items:center;gap:10px}.ev-dg-card-icon{width:38px;height:38px;display:grid;place-items:center;border-radius:13px;background:#ECFDF3;color:var(--dg-green)}.ev-dg-card-icon-orange{background:#FFF7ED;color:var(--dg-orange)}.ev-dg-card h2{margin:0;color:var(--dg-green);font-size:1rem;font-weight:950}.ev-dg-card header p{margin:3px 0 0;color:var(--dg-muted);font-size:.73rem}.ev-dg-chart{position:relative;height:300px}.ev-dg-chart canvas{max-height:300px}.ev-dg-chart-empty{position:absolute;inset:0;display:grid;place-items:center;color:var(--dg-muted);font-size:.82rem;background:#fff}
.ev-dg-goal-main{display:flex;align-items:center;gap:28px}.ev-dg-goal-ring{--pct:0;position:relative;width:142px;height:142px;flex:0 0 auto;display:grid;place-content:center;text-align:center;border-radius:50%;background:conic-gradient(var(--dg-orange) calc(var(--pct)*1%),#F1F5F9 0)}.ev-dg-goal-ring:before{content:"";position:absolute;inset:11px;border-radius:50%;background:#fff}.ev-dg-goal-ring strong,.ev-dg-goal-ring span{position:relative;z-index:1}.ev-dg-goal-ring strong{color:var(--dg-green);font-size:1.65rem;font-weight:950}.ev-dg-goal-ring span{color:var(--dg-muted);font-size:.68rem}.ev-dg-goal-values{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;flex:1}.ev-dg-goal-values>div{padding:11px;border-radius:14px;background:#F8FAFC;border:1px solid #EEF2F7}.ev-dg-goal-values small{display:block;color:var(--dg-muted);font-size:.67rem}.ev-dg-goal-values strong{display:block;margin-top:4px;color:var(--dg-green);font-size:.95rem;font-weight:950}.ev-dg-progress{height:10px;margin-top:16px;border-radius:999px;overflow:hidden;background:#F1F5F9}.ev-dg-progress span{display:block;width:0;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--dg-green2),var(--dg-light),var(--dg-orange));transition:width .4s ease}.ev-dg-setup-note{margin:12px 0 0;padding:9px 11px;border-radius:11px;background:#FFF7ED;color:#9A3412;font-size:.73rem}.ev-dg-status-columns{display:grid;grid-template-columns:1fr 1fr;gap:15px}.ev-dg-status-columns h3{margin:0 0 8px;color:#334155;font-size:.78rem;font-weight:950}.ev-dg-status-list{display:grid;gap:7px}.ev-dg-status-row{display:flex;justify-content:space-between;gap:8px;padding:8px 10px;border-radius:11px;background:#F8FAFC;color:#475569;font-size:.72rem}.ev-dg-status-row strong{color:var(--dg-green)}
.ev-dg-table-wrap{overflow:auto;border:1px solid #E9EEF4;border-radius:15px}.ev-dg-table-wrap table{width:100%;min-width:980px;border-collapse:collapse}.ev-dg-table-wrap th,.ev-dg-table-wrap td{padding:11px 12px;text-align:center;border-bottom:1px solid #EEF2F7;font-size:.73rem}.ev-dg-table-wrap th{position:sticky;top:0;background:#F8FAFC;color:#334155;font-weight:950;z-index:1}.ev-dg-table-wrap td:first-child,.ev-dg-table-wrap th:first-child{text-align:left}.ev-dg-community-name{display:grid}.ev-dg-community-name strong{color:var(--dg-green)}.ev-dg-community-name small{color:var(--dg-muted)}.ev-dg-table-count{padding:6px 10px;border-radius:999px;background:#ECFDF3;color:var(--dg-green);font-size:.7rem;font-weight:900}.ev-dg-empty-cell{text-align:center!important;color:var(--dg-muted)!important;padding:26px!important}
.ev-dg-goal-dialog{max-width:560px}.ev-dg-goal-dialog .modal-content{overflow:hidden;border:0;border-radius:22px;box-shadow:0 30px 80px rgba(15,23,42,.22)}.ev-dg-goal-dialog .modal-header{padding:14px 16px;background:linear-gradient(135deg,var(--dg-green),var(--dg-green2));color:#fff;border:0}.ev-dg-modal-title{display:flex;align-items:center;gap:10px}.ev-dg-modal-title>span{width:38px;height:38px;display:grid;place-items:center;border-radius:12px;background:rgba(255,255,255,.12)}.ev-dg-modal-title small{display:block;font-size:.62rem;font-weight:900;letter-spacing:.08em}.ev-dg-modal-title h5{margin:1px 0 0;font-weight:950}.ev-dg-goal-dialog .btn-close{width:38px;height:38px;margin:0;padding:0;border-radius:12px;background:transparent;filter:none;opacity:1;position:relative}.ev-dg-goal-dialog .btn-close:before,.ev-dg-goal-dialog .btn-close:after{content:"";position:absolute;left:11px;top:18px;width:16px;height:2px;background:#fff;border-radius:3px}.ev-dg-goal-dialog .btn-close:before{transform:rotate(45deg)}.ev-dg-goal-dialog .btn-close:after{transform:rotate(-45deg)}.ev-dg-goal-dialog .modal-body{padding:20px}.ev-dg-goal-dialog .modal-body>p{color:var(--dg-muted);font-size:.82rem}.ev-dg-goal-context{display:flex;gap:8px;margin-top:12px;padding:10px;border-radius:12px;background:#F0FDF4;color:var(--dg-green);font-size:.75rem}.ev-dg-goal-dialog .modal-footer{border-top:1px solid #EEF2F7;padding:12px 16px}
@media(max-width:1200px){.ev-dg-filter-grid{grid-template-columns:repeat(3,1fr)}.ev-dg-kpis{grid-template-columns:repeat(2,1fr)}.ev-dg-grid-charts,.ev-dg-grid-goal{grid-template-columns:1fr}}
@media(max-width:700px){.ev-dg-page{padding:12px}.ev-dg-hero{align-items:stretch;flex-direction:column;padding:20px}.ev-dg-hero-copy{align-items:flex-start}.ev-dg-period-badge{min-width:0}.ev-dg-filter-head{align-items:flex-start}.ev-dg-filter-head>div{align-items:flex-start;flex-wrap:wrap}.ev-dg-filter-head span{width:100%}.ev-dg-filter-grid{grid-template-columns:1fr}.ev-dg-kpis{grid-template-columns:1fr}.ev-dg-card>header{align-items:flex-start;flex-direction:column}.ev-dg-card>header .ev-dg-btn{width:100%}.ev-dg-goal-main{align-items:stretch;flex-direction:column}.ev-dg-goal-ring{align-self:center}.ev-dg-goal-values{grid-template-columns:1fr}.ev-dg-status-columns{grid-template-columns:1fr}.ev-dg-chart{height:260px}.ev-dg-filter-grid .ev-dg-btn{width:100%}}

/* EV — refinamiento de meta gerencial y modal consistente */
.ev-dg-btn-goal{background:linear-gradient(135deg,#EA7C12,#F59E0B)!important;color:#fff!important;border-color:transparent!important;box-shadow:0 12px 26px rgba(234,124,18,.24)!important}
.ev-dg-btn-goal:hover,.ev-dg-btn-goal:focus{color:#fff!important;transform:translateY(-1px);box-shadow:0 16px 32px rgba(234,124,18,.30)!important}
.ev-dg-goal-dialog .modal-content{border:0!important;background-clip:border-box!important;overflow:hidden!important;border-radius:22px!important}
.ev-dg-goal-dialog .modal-header{margin:0!important;border:0!important;border-radius:0!important;background:linear-gradient(135deg,#0F592F 0%,#0E7A43 58%,#16A34A 100%)!important}
.ev-dg-goal-dialog .ev-dg-modal-title h5,.ev-dg-goal-dialog .ev-dg-modal-title small,.ev-dg-goal-dialog .ev-dg-modal-title i{color:#fff!important}
.ev-dg-goal-dialog .btn-close{box-sizing:border-box!important;border:1px solid rgba(255,255,255,.18)!important;background-color:rgba(255,255,255,.08)!important;background-image:none!important;transition:background-color .16s ease,border-color .16s ease!important}
.ev-dg-goal-dialog .btn-close:hover,.ev-dg-goal-dialog .btn-close:focus{background-color:rgba(255,255,255,.16)!important;border-color:rgba(255,255,255,.30)!important;box-shadow:none!important;transform:none!important}

</style>
<style>.ev-dg-chart-legend{display:flex;flex-wrap:wrap;gap:12px;margin:-3px 0 8px}.ev-dg-chart-legend span{display:inline-flex;align-items:center;gap:6px;color:#64748B;font-size:.68rem;font-weight:800}.ev-dg-chart-legend i{width:9px;height:9px;border-radius:50%;background:var(--legend)}</style>
<style>
/* EV — cierre consistente del modal gerencial */
.ev-dg-goal-dialog .ev-dg-modal-close{
  width:40px;
  height:40px;
  margin:0;
  padding:0;
  display:grid;
  place-items:center;
  flex:0 0 40px;
  border:1px solid rgba(255,255,255,.22);
  border-radius:12px;
  background:rgba(255,255,255,.10);
  color:#fff;
  font-size:1.12rem;
  line-height:1;
  cursor:pointer;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.10);
  transition:background-color .16s ease,border-color .16s ease,box-shadow .16s ease;
}
.ev-dg-goal-dialog .ev-dg-modal-close i{color:#fff;line-height:1}
.ev-dg-goal-dialog .ev-dg-modal-close:hover,
.ev-dg-goal-dialog .ev-dg-modal-close:focus-visible{
  color:#fff;
  background:rgba(255,255,255,.18);
  border-color:rgba(255,255,255,.34);
  box-shadow:0 0 0 3px rgba(255,255,255,.10);
  outline:0;
  transform:none;
}
</style>
<style>
/* EV 2026-08 — gráficos responsivos y modal de meta alineado a Recargar saldo */
.ev-dg-page,.ev-dg-grid,.ev-dg-card,.ev-dg-chart-card,.ev-dg-card>header,.ev-dg-card>header>div,.ev-dg-card>header>div>div{min-width:0}
.ev-dg-chart-card{overflow:hidden}
.ev-dg-chart-card header h2,.ev-dg-chart-card header p{max-width:100%;white-space:normal;overflow-wrap:anywhere}
.ev-dg-chart{width:100%;max-width:100%;height:300px;overflow:hidden}
.ev-dg-chart-stage{position:relative;width:100%;height:100%;min-width:0}
.ev-dg-chart-stage canvas{display:block!important;width:100%!important;max-width:100%!important;height:100%!important;max-height:none!important}
.ev-dg-goal-dialog .modal-content{overflow:hidden!important;border:0!important;border-radius:22px!important;background:#fff!important;box-shadow:0 34px 72px rgba(15,23,42,.22)!important}
.ev-dg-goal-dialog .modal-header{min-height:62px;padding:15px 22px!important;background:linear-gradient(135deg,#0F592F 0%,#0E7A43 58%,#16A34A 100%)!important;border:0!important}
.ev-dg-goal-dialog .modal-header .btn-close{width:32px!important;height:32px!important;margin:0!important;padding:0!important;border:0!important;border-radius:0!important;background-color:transparent!important;background-size:18px!important;filter:invert(1) grayscale(100%) brightness(200%)!important;opacity:1!important;box-shadow:none!important;transform:none!important}
.ev-dg-goal-dialog .modal-header .btn-close:hover,.ev-dg-goal-dialog .modal-header .btn-close:focus{opacity:.78!important;background-color:transparent!important;box-shadow:none!important;transform:none!important}
.ev-dg-goal-dialog .modal-body label>span{display:block;margin:0 0 7px;color:#263238;font-size:.78rem;font-weight:850}
.ev-dg-goal-dialog .modal-body input{width:100%;min-height:48px;padding:10px 14px;border:1px solid #BFE8CF;border-radius:14px;outline:0}
.ev-dg-goal-dialog .modal-body input:focus{border-color:#16A34A;box-shadow:0 0 0 4px rgba(22,163,74,.11)}
.ev-dg-goal-dialog .modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:12px 18px 16px!important;border-top:1px solid #E9EEF4!important;background:#fff}
.ev-dg-goal-dialog .modal-footer .ev-dg-btn{min-height:44px;border-radius:14px;padding:10px 18px}
@media(max-width:700px){
 .ev-dg-page{max-width:100%;overflow-x:hidden}
 .ev-dg-grid{width:100%;max-width:100%}
 .ev-dg-card{width:100%;max-width:100%;overflow:hidden}
 .ev-dg-chart-card{padding:15px 12px}
 .ev-dg-chart{height:260px;max-width:100%;overflow:hidden}
 .ev-dg-chart-stage{width:100%;max-width:100%;padding:0 3px 5px;overflow:hidden}
 .ev-dg-chart-stage canvas{display:block!important;width:100%!important;max-width:100%!important;height:100%!important}
 .ev-dg-chart-legend{gap:8px 11px}.ev-dg-chart-legend span{font-size:.63rem}
 .ev-dg-goal-dialog{width:calc(100% - 24px);margin:12px auto}
 .ev-dg-goal-dialog .modal-footer{flex-wrap:nowrap}.ev-dg-goal-dialog .modal-footer .ev-dg-btn{flex:1}
}

/* Modal de meta: misma interacción visual que Recargar saldo. */
.ev-dg-goal-dialog .modal-header .btn-close{
 width:1.25rem!important;height:1.25rem!important;padding:.25em!important;margin:0!important;
 border:0!important;border-radius:.375rem!important;
 background:transparent var(--bs-btn-close-bg) center/1em auto no-repeat!important;
 filter:invert(1) grayscale(100%) brightness(200%)!important;
 opacity:1!important;box-shadow:none!important;transform:none!important;
 transition:opacity .15s ease!important;
}
.ev-dg-goal-dialog .modal-header .btn-close::before,
.ev-dg-goal-dialog .modal-header .btn-close::after{content:none!important;display:none!important}
.ev-dg-goal-dialog .modal-header .btn-close:hover,
.ev-dg-goal-dialog .modal-header .btn-close:focus{opacity:.76!important;background-color:transparent!important;box-shadow:none!important;transform:none!important}
.ev-dg-goal-dialog .modal-footer .ev-dg-btn-light{
 min-height:44px;padding:9px 16px;border:1px solid #D1D5DB;border-radius:14px;
 background:#fff;color:#4B5563;box-shadow:none;font-size:.9rem;font-weight:800;
}
.ev-dg-goal-dialog .modal-footer .ev-dg-btn-light:hover,
.ev-dg-goal-dialog .modal-footer .ev-dg-btn-light:focus{background:#F3F4F6;color:#111827;border-color:#D1D5DB;transform:none;box-shadow:none}
.ev-dg-goal-dialog .modal-footer .ev-dg-btn-primary{
 min-height:44px;padding:9px 16px;border:0;border-radius:14px;
 background:linear-gradient(135deg,#EA7C12,#F59E0B);color:#fff;
 box-shadow:0 12px 26px rgba(234,124,18,.28);font-size:.92rem;font-weight:850;
}
.ev-dg-goal-dialog .modal-footer .ev-dg-btn-primary:hover,
.ev-dg-goal-dialog .modal-footer .ev-dg-btn-primary:focus{background:linear-gradient(135deg,#C46B05,#EA580C);color:#fff;transform:translateY(-1px);box-shadow:0 14px 32px rgba(234,124,18,.36)}
</style>


<style>
/* ============================================================
   EV 2026-08 — cierre definitivo del modal de meta
   Misma X limpia del modal Nueva publicación.
============================================================ */
.ev-dg-goal-dialog .modal-header .btn-close{
  box-sizing:content-box!important;
  width:1em!important;
  height:1em!important;
  margin:0!important;
  padding:.25em!important;
  border:0!important;
  border-radius:.375rem!important;
  background:transparent var(--bs-btn-close-bg) center/1em auto no-repeat!important;
  filter:invert(1) grayscale(100%) brightness(200%)!important;
  opacity:.95!important;
  box-shadow:none!important;
  transform:none!important;
  transition:opacity .16s ease,filter .16s ease!important;
}
.ev-dg-goal-dialog .modal-header .btn-close:hover,
.ev-dg-goal-dialog .modal-header .btn-close:focus-visible{
  opacity:1!important;
  filter:invert(1) grayscale(100%) brightness(225%)!important;
  background-color:transparent!important;
  box-shadow:none!important;
  transform:none!important;
  outline:0!important;
}
</style>

<style>
/* EV 2026-08-05 — cierre interactivo homologado con Revisar registro. */
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close{
  box-sizing:border-box!important;
  width:40px!important;
  height:40px!important;
  flex:0 0 40px!important;
  margin:0!important;
  padding:0!important;
  border:1px solid rgba(255,255,255,.18)!important;
  border-radius:12px!important;
  background-color:rgba(255,255,255,.09)!important;
  background-image:var(--bs-btn-close-bg)!important;
  background-position:center!important;
  background-size:16px!important;
  background-repeat:no-repeat!important;
  filter:invert(1) grayscale(100%) brightness(205%)!important;
  opacity:1!important;
  box-shadow:none!important;
  transform:none!important;
  cursor:pointer!important;
  transition:background-color .16s ease,border-color .16s ease,box-shadow .16s ease!important;
}
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close:hover,
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close:focus-visible{
  background-color:rgba(255,255,255,.18)!important;
  border-color:rgba(255,255,255,.34)!important;
  box-shadow:0 0 0 4px rgba(255,255,255,.09)!important;
  filter:invert(1) grayscale(100%) brightness(235%)!important;
  opacity:1!important;
  outline:0!important;
}
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close:active{
  background-color:rgba(255,255,255,.24)!important;
}
@media(max-width:575.98px){
  .ev-dg-goal-dialog .modal-header .ev-dg-modal-close{width:38px!important;height:38px!important;flex-basis:38px!important}
}
</style>


<style>
/* EV 2026-08-05 — interacción inequívoca del cierre del modal gerencial. */
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close{
  box-sizing:border-box!important;
  display:inline-grid!important;
  place-items:center!important;
  width:40px!important;
  height:40px!important;
  flex:0 0 40px!important;
  margin:0!important;
  padding:0!important;
  border:1px solid rgba(255,255,255,.20)!important;
  border-radius:12px!important;
  background:rgba(255,255,255,.09)!important;
  color:#fff!important;
  filter:none!important;
  opacity:1!important;
  box-shadow:none!important;
  transform:none!important;
  cursor:pointer!important;
  transition:background-color .16s ease,border-color .16s ease,box-shadow .16s ease,transform .16s ease!important;
}
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close i{
  color:#fff!important;
  font-size:1.08rem!important;
  line-height:1!important;
  pointer-events:none;
}
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close:hover,
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close:focus-visible{
  background:rgba(255,255,255,.20)!important;
  border-color:rgba(255,255,255,.38)!important;
  box-shadow:0 0 0 4px rgba(255,255,255,.10)!important;
  color:#fff!important;
  transform:translateY(-1px)!important;
  outline:0!important;
}
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close:active{
  background:rgba(255,255,255,.26)!important;
  transform:translateY(0)!important;
}
@media(max-width:575.98px){
  .ev-dg-goal-dialog .modal-header .ev-dg-modal-close{
    width:38px!important;height:38px!important;flex-basis:38px!important;
  }
}
</style>
<style>
/* ============================================================
   EV — cierre final del modal Definir meta de ingresos
   Estado normal: X blanca y limpia. Hover/focus: botón perceptible.
============================================================ */
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close{
  box-sizing:border-box!important;
  display:inline-grid!important;
  place-items:center!important;
  width:40px!important;
  height:40px!important;
  flex:0 0 40px!important;
  margin:0!important;
  padding:0!important;
  border:1px solid transparent!important;
  border-radius:12px!important;
  background:transparent!important;
  color:#FFFFFF!important;
  filter:none!important;
  opacity:1!important;
  box-shadow:none!important;
  transform:none!important;
  cursor:pointer!important;
  -webkit-tap-highlight-color:transparent;
  transition:background-color .16s ease,border-color .16s ease,box-shadow .16s ease,transform .16s ease!important;
}
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close i{
  display:block!important;
  color:#FFFFFF!important;
  font-size:1.08rem!important;
  line-height:1!important;
  pointer-events:none!important;
}
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close:hover,
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close:focus-visible{
  background:rgba(15,89,47,.34)!important;
  border-color:rgba(255,255,255,.30)!important;
  box-shadow:0 0 0 4px rgba(255,255,255,.10),inset 0 1px 0 rgba(255,255,255,.14)!important;
  color:#FFFFFF!important;
  transform:translateY(-1px)!important;
  outline:0!important;
}
.ev-dg-goal-dialog .modal-header .ev-dg-modal-close:active{
  background:rgba(15,89,47,.48)!important;
  transform:translateY(0)!important;
}
@media(max-width:575.98px){
  .ev-dg-goal-dialog .modal-header .ev-dg-modal-close{
    width:38px!important;
    height:38px!important;
    flex-basis:38px!important;
  }
}
</style>

<style>
/* EV — cierre homologado con Nueva publicación. */
#evDgGoalModal .modal-header .btn-close.ev-modal-close-icon{
  width:38px!important;height:38px!important;min-width:38px!important;min-height:38px!important;
  margin:0!important;padding:0!important;border:0!important;border-radius:10px!important;
  background-color:transparent!important;background-image:var(--bs-btn-close-bg)!important;
  background-position:center!important;background-repeat:no-repeat!important;background-size:14px 14px!important;
  filter:invert(1) grayscale(1) brightness(2)!important;opacity:1!important;
  box-shadow:none!important;transform:none!important;transition:background-color .13s ease!important;
}
#evDgGoalModal .modal-header .btn-close.ev-modal-close-icon:hover,
#evDgGoalModal .modal-header .btn-close.ev-modal-close-icon:focus-visible{
  background-color:rgba(15,89,47,.44)!important;border:0!important;box-shadow:none!important;
  opacity:1!important;transform:none!important;outline:0!important;
}
#evDgGoalModal .modal-header .btn-close.ev-modal-close-icon:active{background-color:rgba(15,89,47,.60)!important}
</style>
