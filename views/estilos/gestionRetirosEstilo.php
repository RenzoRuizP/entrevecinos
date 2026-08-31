<style>
#evGestionRetiros{
  --g:#0F592F;
  --g2:#0E7A43;
  --g3:#16A34A;
  --o:#EA7C12;
  --od:#C46B05;
  --os:#FFF7ED;
  --tx:#111827;
  --mu:#6B7280;
  --bd:#E5E7EB;
  --soft:#F8FAF9;
  padding:16px 16px 30px;
  font-family:Poppins,system-ui,sans-serif;
  color:var(--tx)
}

.ev-withdraw-admin-hero,.ev-withdraw-admin-workspace{
  background:#fff;
  border:1px solid rgba(148,163,184,.18);
  border-radius:25px;
  box-shadow:0 16px 40px rgba(15,23,42,.08)
}
.ev-withdraw-admin-hero{
  min-height:132px;
  padding:20px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:18px;
  background:radial-gradient(circle at 90% 10%,rgba(22,163,74,.11),transparent 34%),linear-gradient(135deg,#fff,#f8fcf9)
}
.ev-withdraw-admin-title-wrap{display:flex;gap:14px;align-items:flex-start}
.ev-withdraw-admin-icon{
  width:56px;height:56px;border-radius:19px;display:grid;place-items:center;
  background:#fff;border:1px solid rgba(22,163,74,.22);color:var(--g);font-size:1.3rem;
  box-shadow:0 12px 24px rgba(15,23,42,.08)
}
.ev-withdraw-admin-kicker{color:var(--o);font-size:.72rem;font-weight:900;letter-spacing:.14em}
.ev-withdraw-admin-hero h2{margin:3px 0 0;color:var(--g);font-size:1.95rem;font-weight:950;letter-spacing:-.035em}
.ev-withdraw-admin-hero p{margin:5px 0 0;color:var(--mu);font-size:.9rem;line-height:1.5}
.ev-withdraw-admin-readonly{display:inline-flex;gap:7px;align-items:center;padding:9px 12px;border-radius:999px;background:#F3F4F6;color:#4B5563;font-size:.76rem;font-weight:800}

.ev-withdraw-admin-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin:20px 0}
.ev-withdraw-admin-summary article{
  position:relative;overflow:hidden;display:flex;align-items:center;gap:13px;
  min-height:100px;padding:16px 17px;border:1px solid var(--bd);border-radius:20px;background:#fff;
  box-shadow:0 10px 26px rgba(15,23,42,.05);transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease
}
.ev-withdraw-admin-summary article::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:linear-gradient(90deg,var(--g2),var(--g3));opacity:.85}
.ev-withdraw-admin-summary article.is-money::after{background:linear-gradient(90deg,var(--o),var(--od))}
.ev-withdraw-admin-summary article:hover{transform:translateY(-2px);box-shadow:0 14px 32px rgba(15,23,42,.075);border-color:#D8E3DC}
.ev-withdraw-summary-icon{width:42px;height:42px;flex:0 0 42px;border-radius:14px;display:grid!important;place-items:center;background:#F0FDF4;color:var(--g2)!important;border:1px solid #D7EADF;font-size:1rem!important}
.ev-withdraw-admin-summary .is-money .ev-withdraw-summary-icon{background:#FFF7ED;color:var(--o)!important;border-color:#FED7AA}
.ev-withdraw-admin-summary article>div{min-width:0}
.ev-withdraw-admin-summary article>div>span{display:block;color:var(--mu);font-size:.76rem;font-weight:800}
.ev-withdraw-admin-summary strong{display:block;margin:4px 0;color:var(--g);font-size:1.4rem;font-weight:950;line-height:1.1}
.ev-withdraw-admin-summary small{display:block;color:#94A3B8;font-size:.72rem;line-height:1.35}
.ev-withdraw-admin-summary .is-money strong{color:var(--od)}

.ev-withdraw-admin-workspace{overflow:hidden}
.ev-withdraw-admin-workspace-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 16px;border-bottom:1px solid var(--bd);background:linear-gradient(180deg,#fff,#FCFDFC)}
.ev-withdraw-admin-tabs{display:flex;gap:8px;flex-wrap:wrap}
.ev-withdraw-admin-tabs button{
  display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;
  border:1px solid transparent;background:transparent;color:#4B5563;border-radius:13px;padding:9px 14px;
  font-weight:800;font-size:.8rem;transition:all .18s ease
}
.ev-withdraw-admin-tabs button i{font-size:.88rem}
.ev-withdraw-admin-tabs button:hover{background:#FFF9F2;color:var(--od);border-color:#FDE4C8}
.ev-withdraw-admin-tabs button.is-active{
  background:linear-gradient(135deg,#FFF7ED,#FFF3E4);color:var(--od);border-color:#F3C793;
  box-shadow:0 7px 18px rgba(234,124,18,.12),inset 0 -2px 0 rgba(234,124,18,.15)
}
.ev-withdraw-refresh{width:40px;height:40px;border:1px solid var(--bd);border-radius:12px;background:#fff;color:var(--g);display:grid;place-items:center;transition:all .18s ease}
.ev-withdraw-refresh:hover{background:#F0FDF4;border-color:#BFE3CC;color:var(--g2);transform:rotate(12deg)}

.ev-withdraw-admin-body{padding:18px}
.ev-withdraw-panel{display:none;animation:evRetPanelIn .18s ease}
.ev-withdraw-panel.is-active{display:block}
@keyframes evRetPanelIn{from{opacity:.35;transform:translateY(3px)}to{opacity:1;transform:none}}

.ev-withdraw-section-head{display:flex;justify-content:space-between;align-items:flex-start;gap:15px;margin-bottom:14px}
.ev-withdraw-section-head--feature{padding:14px 15px;border:1px solid #E8EEE9;border-radius:17px;background:linear-gradient(135deg,#FBFDFC,#FFF9F3)}
.ev-withdraw-section-title{display:flex;align-items:flex-start;gap:11px;min-width:0}
.ev-withdraw-section-icon{width:38px;height:38px;flex:0 0 38px;display:grid;place-items:center;border-radius:12px;background:#F0FDF4;color:var(--g2);border:1px solid #D7EADF}
.ev-withdraw-section-head h3{margin:0;color:var(--g);font-size:1rem;font-weight:900}
.ev-withdraw-section-head p{margin:4px 0 0;color:var(--mu);font-size:.78rem;line-height:1.5}
.ev-withdraw-section-chip{display:inline-flex;align-items:center;white-space:nowrap;padding:7px 10px;border-radius:999px;background:#FFF7ED;border:1px solid #FED7AA;color:var(--od);font-size:.68rem;font-weight:850}

.ev-withdraw-toolbar{
  display:grid;grid-template-columns:minmax(0,1fr) 190px 220px;gap:10px;align-items:end;
  margin-bottom:14px;padding:12px;border:1px solid #EBEEF0;border-radius:17px;background:#FAFBFA
}
.ev-withdraw-search{position:relative}
.ev-withdraw-filter-field{display:grid;gap:6px}
.ev-withdraw-filter-field label{font-size:.7rem;font-weight:850;color:#64748B;letter-spacing:.02em;margin-left:2px}
.ev-withdraw-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#9CA3AF}
.ev-withdraw-search .form-control{padding-left:38px}
.ev-withdraw-toolbar .form-control,.ev-withdraw-toolbar .form-select,.ev-withdraw-cut-card .form-control,.ev-withdraw-cut-card .form-select,#modalPagoRetiro .form-control{
  min-height:42px;border-radius:12px;border-color:var(--bd);box-shadow:none
}
.ev-withdraw-toolbar .form-control:focus,.ev-withdraw-toolbar .form-select:focus,.ev-withdraw-cut-card .form-control:focus,.ev-withdraw-cut-card .form-select:focus,#modalPagoRetiro .form-control:focus{
  border-color:#F0B775;box-shadow:0 0 0 3px rgba(234,124,18,.10)
}

.ev-withdraw-table-wrap{border:1px solid var(--bd);border-radius:18px;overflow:hidden;background:#fff}
.ev-withdraw-table-wrap table{margin:0}
.ev-withdraw-table-wrap th{background:#F9FAFB;color:#64748B;font-size:.69rem;text-transform:uppercase;letter-spacing:.035em;border-bottom-color:var(--bd);white-space:nowrap}
.ev-withdraw-table-wrap td{font-size:.79rem;color:#374151;border-bottom-color:#EEF0F2;vertical-align:middle}
.ev-withdraw-table-wrap tbody tr{transition:background .15s ease}
.ev-withdraw-table-wrap tbody tr:hover{background:#FCFDFC}
.ev-withdraw-table-wrap tbody tr:last-child td{border-bottom:0}
.ev-withdraw-loading,.ev-withdraw-empty{padding:38px 20px;text-align:center;color:#94A3B8;font-size:.82rem;background:linear-gradient(180deg,#fff,#FBFCFB)}

.ev-ret-state{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:.69rem;font-weight:850;background:#F3F4F6;color:#4B5563;white-space:nowrap}
.ev-ret-state--solicitado{background:#FFF7ED;color:#9A4C08}.ev-ret-state--programado{background:#EFF6FF;color:#1D4ED8}.ev-ret-state--pagado,.ev-ret-state--validada{background:#ECFDF3;color:#0F592F}.ev-ret-state--observado,.ev-ret-state--observada{background:#FEF2F2;color:#B42318}.ev-ret-state--pendiente{background:#FFF7ED;color:#9A4C08}.ev-ret-state--cancelado,.ev-ret-state--sin_saldo{background:#F3F4F6;color:#6B7280}
.ev-ret-review{border:1px solid #D7EADF;border-radius:11px;background:#fff;color:var(--g2);padding:7px 11px;font-size:.75rem;font-weight:850;transition:all .18s ease}
.ev-ret-review:hover{background:#F0FDF4;border-color:#A8D8B9;box-shadow:0 6px 14px rgba(14,122,67,.08)}
.ev-ret-review--orange{border-color:#F3C793;color:var(--od);background:#FFF9F2}.ev-ret-review--orange:hover{background:#FFF1E2;border-color:#EFB26D;color:#A95808}
.ev-ret-account{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.77rem;overflow-wrap:anywhere}
.ev-ret-sub{display:block;margin-top:2px;color:#94A3B8;font-size:.7rem;line-height:1.4}
.ev-ret-actions-cell{white-space:nowrap}

.ev-ret-batch{border:1px solid #E5E7EB;border-radius:18px;overflow:hidden;background:#fff;margin-bottom:16px;box-shadow:0 8px 22px rgba(15,23,42,.035)}
.ev-ret-batch:last-child{margin-bottom:0}
.ev-ret-batch-head{position:relative;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 16px 14px 19px;background:linear-gradient(135deg,#FAFCFB,#FFF9F2);border-bottom:1px solid #E5E7EB}
.ev-ret-batch-head::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,var(--o),var(--od))}
.ev-ret-batch-head>div{display:flex;flex-direction:column;gap:2px}.ev-ret-batch-head span{font-size:.72rem;color:#6B7280;font-weight:700}.ev-ret-batch-head strong{font-size:.93rem;color:#111827}
.ev-ret-batch-meta{text-align:right}.ev-ret-batch-meta strong{color:var(--od);font-size:1rem}
.ev-ret-batch .table-responsive{margin:0}.ev-ret-batch .table{margin-bottom:0}

.ev-withdraw-cut-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.ev-withdraw-cut-card{position:relative;overflow:hidden;border:1px solid var(--bd);border-radius:20px;padding:18px;background:#fff;box-shadow:0 9px 24px rgba(15,23,42,.04)}
.ev-withdraw-cut-card::before{content:"";position:absolute;left:0;right:0;top:0;height:4px;background:linear-gradient(90deg,var(--o),var(--od))}
.ev-withdraw-cut-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:15px}
.ev-withdraw-cut-card-title{display:flex;align-items:center;gap:10px;min-width:0}
.ev-withdraw-cut-card-icon{width:38px;height:38px;flex:0 0 38px;border-radius:12px;display:grid;place-items:center;background:#FFF7ED;border:1px solid #FED7AA;color:var(--o)}
.ev-withdraw-cut-card h4{margin:0;color:var(--g);font-size:1rem;font-weight:900}
.ev-withdraw-cut-card p{margin:2px 0 0;color:var(--mu);font-size:.72rem}
.ev-withdraw-cut-day{display:inline-flex;align-items:center;padding:6px 9px;border-radius:999px;background:#F0FDF4;border:1px solid #D7EADF;color:var(--g2);font-size:.68rem;font-weight:850;white-space:nowrap}
.ev-withdraw-cut-fields{display:grid;grid-template-columns:1fr 1fr;gap:11px}
.ev-withdraw-cut-field label{display:block;margin-bottom:6px;color:#4B5563;font-size:.72rem;font-weight:800}
.ev-withdraw-readonly-field{background:#F9FAFB;color:#4B5563;font-weight:700;display:flex;align-items:center;min-height:42px;cursor:default}
.ev-withdraw-cut-actions{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:15px;padding-top:14px;border-top:1px solid #EEF0F2}
.ev-withdraw-cut-switch{display:flex;gap:8px;align-items:center;color:#4B5563;font-size:.76rem;font-weight:700}
.ev-withdraw-cut-switch input{accent-color:var(--g2);width:15px;height:15px}
.ev-withdraw-save{
  border:1px solid var(--o);border-radius:12px;background:var(--o);color:#fff;padding:9px 14px;min-height:40px;
  font-size:.78rem;font-weight:850;box-shadow:0 8px 18px rgba(234,124,18,.18);transition:all .18s ease
}
.ev-withdraw-save:hover,.ev-withdraw-save:focus{background:var(--od);border-color:var(--od);color:#fff;box-shadow:0 10px 22px rgba(196,107,5,.24);transform:translateY(-1px)}
.ev-withdraw-save:active{transform:translateY(0)}

/* Detalle del retiro */
.ev-ret-detail-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:17px 18px;margin-bottom:14px;border:1px solid rgba(22,163,74,.16);border-radius:19px;background:linear-gradient(135deg,#F7FCF9,#FFF9F2)}
.ev-ret-detail-hero-main{min-width:0}.ev-ret-detail-code{display:block;color:var(--o);font-size:.7rem;font-weight:950;letter-spacing:.08em;text-transform:uppercase}.ev-ret-detail-person{margin-top:4px;color:var(--g);font-size:1.08rem;font-weight:950;line-height:1.3;overflow-wrap:anywhere}.ev-ret-detail-meta{margin-top:4px;color:var(--mu);font-size:.76rem;line-height:1.45}.ev-ret-detail-hero-amount{display:flex;flex-direction:column;align-items:flex-end;flex:0 0 auto}.ev-ret-detail-hero-amount>span{color:#64748B;font-size:.7rem;font-weight:800}.ev-ret-detail-hero-amount>strong{color:var(--od);font-size:1.45rem;font-weight:950;letter-spacing:-.03em}.ev-ret-detail-hero-amount>em{margin-top:5px;font-style:normal}
.ev-ret-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.ev-ret-detail-card{border:1px solid var(--bd);border-radius:18px;padding:15px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.035)}.ev-ret-detail-card--wide{grid-column:1/-1}.ev-ret-detail-card-head{display:flex;align-items:center;gap:10px;padding-bottom:10px;margin-bottom:6px;border-bottom:1px solid #EEF0F2}.ev-ret-detail-card-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:11px;background:#F0FDF4;color:var(--g2);border:1px solid #D7EADF;flex:0 0 34px}.ev-ret-detail-card h6{margin:0;color:var(--g);font-size:.8rem;font-weight:900;text-transform:uppercase;letter-spacing:.035em}.ev-ret-detail-card-head p{margin:2px 0 0;color:#94A3B8;font-size:.68rem}.ev-ret-detail-row{display:flex;justify-content:space-between;gap:14px;padding:6px 0;font-size:.79rem}.ev-ret-detail-row span{color:var(--mu)}.ev-ret-detail-row strong{text-align:right;color:#111827;overflow-wrap:anywhere}.ev-ret-detail-finance-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;margin-top:11px}.ev-ret-detail-finance-grid>div{padding:12px;border:1px solid #EEF0F2;border-radius:14px;background:#F9FAFB}.ev-ret-detail-finance-grid span{display:block;color:#64748B;font-size:.68rem;font-weight:800}.ev-ret-detail-finance-grid strong{display:block;margin-top:4px;color:#111827;font-size:.93rem;font-weight:900}.ev-ret-detail-finance-grid .is-primary{border-color:#F3D7B8;background:#FFF8F0}.ev-ret-detail-finance-grid .is-primary strong{color:var(--od);font-size:1.03rem}.ev-ret-detail-payment{margin-top:12px;padding-top:12px;border-top:1px solid #EEF0F2}.ev-ret-detail-payment-title{color:var(--g);font-size:.76rem;font-weight:900;margin-bottom:2px}.ev-ret-proof-link{color:var(--g2);font-weight:850;text-decoration:none}.ev-ret-proof-link:hover{text-decoration:underline}.ev-ret-detail-alert{display:grid;gap:4px;margin-top:13px;padding:13px 15px;border:1px solid #FDE68A;border-radius:16px;background:#FFFBEB;color:#92400E}.ev-ret-detail-alert strong{font-size:.78rem}.ev-ret-detail-alert span{font-size:.76rem;line-height:1.5}.ev-ret-detail-actions{display:flex;gap:9px;flex-wrap:wrap;width:100%;justify-content:flex-end}.ev-ret-action{border-radius:13px;padding:10px 14px;font-size:.77rem;font-weight:850;min-height:42px}.ev-ret-action-pay{border:0;background:var(--o);color:#fff}.ev-ret-action-pay:hover{background:var(--od);color:#fff}.ev-ret-action-observe{border:1px solid #F3D7B8;background:#FFF7ED;color:#9A4C08}.ev-ret-action-observe:hover{background:#FFEDD5;color:#8A3F06}.ev-ret-action-cancel{border:1px solid #FECACA;background:#fff;color:#B42318}.ev-ret-action-cancel:hover{background:#FEF2F2;color:#991B1B}
#modalDetalleRetiro .modal-dialog{max-width:900px}#modalDetalleRetiro .modal-footer.d-none{display:none!important}

@media(max-width:991.98px){
  .ev-withdraw-admin-summary{grid-template-columns:1fr 1fr}
  .ev-withdraw-cut-grid{grid-template-columns:1fr}
  .ev-withdraw-toolbar{grid-template-columns:minmax(0,1fr) 180px 200px}
  .ev-ret-detail-finance-grid{grid-template-columns:1fr 1fr}
}

@media(max-width:767.98px){
  #evGestionRetiros{padding:10px}
  .ev-withdraw-admin-hero{align-items:flex-start;flex-direction:column;padding:17px}
  .ev-withdraw-admin-title-wrap{align-items:center}
  .ev-withdraw-admin-icon{width:48px;height:48px;border-radius:16px}
  .ev-withdraw-toolbar{grid-template-columns:1fr;padding:11px}
  .ev-withdraw-admin-workspace-head{align-items:stretch;flex-direction:column;padding:12px}
  .ev-withdraw-admin-tabs{display:grid;grid-template-columns:1fr;width:100%;gap:7px}
  .ev-withdraw-admin-tabs button{width:100%;justify-content:flex-start;padding:10px 12px}
  .ev-withdraw-refresh{align-self:flex-end;margin-top:2px}
  .ev-withdraw-section-head--feature{flex-direction:column;padding:13px}
  .ev-withdraw-section-chip{align-self:flex-start}
  .ev-withdraw-cut-fields{grid-template-columns:1fr}
  .ev-ret-detail-grid{grid-template-columns:1fr}.ev-ret-detail-card--wide{grid-column:auto}.ev-ret-detail-hero{align-items:flex-start}.ev-ret-detail-hero-amount{align-items:flex-start}.ev-ret-detail-actions{justify-content:stretch}.ev-ret-action{flex:1 1 150px}

  /* Tablas convertidas a cards en móvil */
  .ev-ret-responsive-table thead{display:none}
  .ev-ret-responsive-table,.ev-ret-responsive-table tbody,.ev-ret-responsive-table tr,.ev-ret-responsive-table td{display:block;width:100%}
  .ev-ret-responsive-table tbody{padding:8px}
  .ev-ret-responsive-table tr{padding:12px;margin-bottom:9px;border:1px solid #E8ECE9;border-radius:15px;background:#fff;box-shadow:0 5px 14px rgba(15,23,42,.035)}
  .ev-ret-responsive-table tr:last-child{margin-bottom:0}
  .ev-ret-responsive-table td{border:0!important;padding:7px 2px!important;text-align:left!important}
  .ev-ret-responsive-table td::before{content:attr(data-label);display:block;margin-bottom:3px;color:#94A3B8;font-size:.64rem;font-weight:850;text-transform:uppercase;letter-spacing:.035em}
  .ev-ret-responsive-table .ev-ret-actions-cell{display:flex;gap:8px;flex-wrap:wrap;padding-top:10px!important;border-top:1px solid #EEF0F2!important;margin-top:3px}
  .ev-ret-responsive-table .ev-ret-actions-cell::before{width:100%}
  .ev-ret-responsive-table .ev-ret-actions-cell .ev-ret-review{flex:1 1 120px;min-height:39px}
  .ev-ret-batch .table-responsive,.ev-withdraw-table-wrap .table-responsive{overflow:visible}
  .ev-ret-batch{overflow:visible;border-radius:17px}
}

@media(max-width:575.98px){
  .ev-withdraw-admin-summary{grid-template-columns:1fr;gap:10px}
  .ev-withdraw-admin-summary article{min-height:88px;padding:14px}
  .ev-withdraw-admin-hero h2{font-size:1.55rem}
  .ev-withdraw-admin-body{padding:12px}
  .ev-withdraw-section-title{gap:9px}.ev-withdraw-section-icon{width:34px;height:34px;flex-basis:34px}
  .ev-withdraw-cut-card{padding:15px}.ev-withdraw-cut-card-head{flex-direction:column}.ev-withdraw-cut-day{align-self:flex-start}
  .ev-withdraw-cut-actions{align-items:stretch;flex-direction:column}.ev-withdraw-save{width:100%}
  .ev-ret-batch-head{align-items:flex-start;flex-direction:column}.ev-ret-batch-meta{text-align:left}
  .ev-ret-detail-hero{flex-direction:column;padding:14px}.ev-ret-detail-hero-amount{width:100%}.ev-ret-detail-finance-grid{grid-template-columns:1fr}.ev-ret-detail-row{align-items:flex-start;flex-direction:column;gap:2px}.ev-ret-detail-row strong{text-align:left}.ev-ret-detail-actions{flex-direction:column}.ev-ret-action{width:100%;flex:auto}#modalDetalleRetiro .modal-body{padding:14px!important}
}
</style>
