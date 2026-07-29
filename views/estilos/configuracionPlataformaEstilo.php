<style>
/* =========================================================
   EV · CONFIGURACIÓN DE PLATAFORMA
   Estándar visual de Soporte y Administración
========================================================= */
#evConfiguracionPlataforma{
  --ev-cp-green-900:#0F592F;
  --ev-cp-green-700:#0E7A43;
  --ev-cp-green:#16A34A;
  --ev-cp-green-050:#F0FDF4;
  --ev-cp-orange:#EA7C12;
  --ev-cp-orange-dark:#C46B05;
  --ev-cp-orange-050:#FFF7ED;
  --ev-cp-red:#DC2626;
  --ev-cp-red-050:#FEF2F2;
  --ev-cp-text:#111827;
  --ev-cp-muted:#64748B;
  --ev-cp-border:#E5E7EB;
  --ev-cp-surface:#FFFFFF;
  --ev-cp-canvas:#F8FAF9;
  --ev-cp-shadow:0 16px 40px rgba(15,23,42,.08);
  --ev-cp-shadow-soft:0 8px 22px rgba(15,23,42,.05);
  padding:16px 16px 30px;
  color:var(--ev-cp-text);
  font-family:Poppins,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}

/* Hero EV */
.ev-config-hero,
.ev-config-workspace{
  background:var(--ev-cp-surface);
  border:1px solid rgba(148,163,184,.18);
  border-radius:25px;
  box-shadow:var(--ev-cp-shadow);
  overflow:hidden;
}
.ev-config-hero{
  min-height:136px;
  padding:20px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:18px;
  background:
    radial-gradient(circle at 88% 12%,rgba(22,163,74,.13),transparent 34%),
    radial-gradient(circle at 12% 88%,rgba(234,124,18,.11),transparent 30%),
    linear-gradient(135deg,#fff,#f7fcf9);
}
.ev-config-hero-copy{display:flex;align-items:flex-start;gap:14px;min-width:0}
.ev-config-hero-icon{
  width:56px;height:56px;display:grid;place-items:center;flex:0 0 auto;
  border-radius:19px;background:#fff;border:1px solid rgba(22,163,74,.22);
  color:var(--ev-cp-green-900);font-size:1.35rem;
  box-shadow:0 12px 24px rgba(15,23,42,.08);
}
.ev-config-kicker{
  margin-bottom:4px;color:var(--ev-cp-orange);font-size:.73rem;
  letter-spacing:.14em;font-weight:900;
}
.ev-config-hero h2{
  margin:0;color:var(--ev-cp-green-900);font-size:2rem;line-height:1.14;
  font-weight:950;letter-spacing:-.035em;
}
.ev-config-hero p{
  margin:5px 0 0;max-width:800px;color:var(--ev-cp-muted);
  font-size:.93rem;line-height:1.5;
}
.ev-config-pilot{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  border:0;border-radius:999px;padding:.75rem 1.05rem;white-space:nowrap;
  background:linear-gradient(135deg,var(--ev-cp-orange),#F59E0B);
  color:#fff;font-weight:900;box-shadow:0 12px 25px rgba(234,124,18,.25);
  transition:transform .16s ease,box-shadow .16s ease,opacity .16s ease;
}
.ev-config-pilot:hover:not(:disabled){color:#fff;transform:translateY(-1px);box-shadow:0 16px 30px rgba(234,124,18,.31)}
.ev-config-pilot:disabled{cursor:not-allowed;opacity:.45;box-shadow:none}

/* Indicadores */
.ev-config-summary{
  display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;
  margin:24px 0;
}
.ev-config-summary article{
  position:relative;min-width:0;overflow:hidden;padding:17px 18px;
  background:#fff;border:1px solid rgba(148,163,184,.17);border-radius:21px;
  box-shadow:0 10px 26px rgba(15,23,42,.06);
}
.ev-config-summary article::after{
  content:"";position:absolute;left:0;right:0;bottom:0;height:4px;
  background:linear-gradient(90deg,var(--ev-cp-green),var(--ev-cp-orange));opacity:.35;
}
.ev-config-summary span{display:block;color:var(--ev-cp-muted);font-size:.78rem;font-weight:850}
.ev-config-summary strong{
  display:block;margin:5px 0;color:var(--ev-cp-green-900);font-size:1.42rem;
  line-height:1.08;font-weight:950;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.ev-config-summary small{display:block;color:#94A3B8;font-size:.74rem;font-weight:750;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* Contenedor operativo */
.ev-config-workspace-head{
  padding:18px;border-bottom:1px solid var(--ev-cp-border);
  display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap;
}
.ev-config-workspace-copy{min-width:260px;flex:1}
.ev-config-workspace-title{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.ev-config-workspace-head h5{margin:0;color:var(--ev-cp-green-900);font-weight:950}
.ev-config-workspace-head p{margin:4px 0 0;max-width:740px;color:var(--ev-cp-muted);font-size:.87rem;line-height:1.45}
.ev-config-admin-badge{
  display:inline-flex;align-items:center;gap:5px;padding:5px 9px;border-radius:999px;
  background:var(--ev-cp-green-050);border:1px solid #BBF7D0;color:#166534;
  font-size:.68rem;font-weight:900;
}
.ev-config-scope-control{width:min(100%,440px)}
.ev-config-scope-control>label{display:block;margin:0 0 5px;color:var(--ev-cp-muted);font-size:.69rem;font-weight:850}
.ev-config-scope-control-row{display:flex;align-items:flex-start;gap:8px}
.ev-config-combobox{position:relative;min-width:0;flex:1}
.ev-config-combobox-field{
  position:relative;display:flex;align-items:center;min-height:42px;
  border:1px solid var(--ev-cp-border);border-radius:14px;background:#fff;
  transition:border-color .16s ease,box-shadow .16s ease;
}
.ev-config-combobox.is-open .ev-config-combobox-field,
.ev-config-combobox-field:focus-within{border-color:rgba(22,163,74,.55);box-shadow:0 0 0 4px rgba(22,163,74,.1)}
.ev-config-combobox-field>i{position:absolute;left:13px;color:#94A3B8;font-size:.9rem;pointer-events:none}
.ev-config-combobox-field .form-control{
  min-width:0;min-height:40px;padding:8px 42px 8px 38px;border:0;border-radius:14px;
  color:#334155;background:transparent;font-size:.8rem;font-weight:800;box-shadow:none;
}
.ev-config-combobox-field .form-control::placeholder{color:#94A3B8;font-weight:650}
.ev-config-combobox-field .form-control:focus{box-shadow:none}
.ev-config-combobox-toggle{
  position:absolute;right:4px;top:4px;width:34px;height:34px;display:grid;place-items:center;
  border:0;border-radius:10px;background:transparent;color:#64748B;transition:.16s ease;
}
.ev-config-combobox-toggle:hover{background:var(--ev-cp-orange-050);color:var(--ev-cp-orange-dark)}
.ev-config-combobox-toggle i{transition:transform .16s ease}
.ev-config-combobox-toggle[aria-expanded="true"] i{transform:rotate(180deg)}
.ev-config-combobox-menu{
  position:absolute;z-index:80;left:0;right:0;top:calc(100% + 7px);overflow:hidden;
  max-height:360px;border:1px solid rgba(148,163,184,.25);border-radius:16px;background:#fff;
  box-shadow:0 22px 48px rgba(15,23,42,.16);animation:evCpCombo .15s ease;
}
.ev-config-combobox-menu[hidden]{display:none!important}
@keyframes evCpCombo{from{opacity:.45;transform:translateY(-4px)}to{opacity:1;transform:none}}
.ev-config-combobox-status{
  padding:9px 12px;border-bottom:1px solid #EEF2F7;background:#FAFCFB;
  color:#94A3B8;font-size:.64rem;font-weight:800;
}
.ev-config-combobox-options{max-height:310px;overflow:auto;padding:6px}
.ev-config-combobox-option{
  width:100%;display:grid;grid-template-columns:36px minmax(0,1fr) 20px;align-items:center;gap:9px;
  padding:9px;border:0;border-radius:12px;background:#fff;color:#334155;text-align:left;
  font-family:inherit;transition:background .14s ease,color .14s ease;
}
.ev-config-combobox-option:hover,.ev-config-combobox-option.is-active{background:var(--ev-cp-green-050);color:var(--ev-cp-green-900)}
.ev-config-combobox-option.is-selected{background:#F8FAFC}
.ev-config-combobox-option-icon{
  width:36px;height:36px;display:grid;place-items:center;border-radius:11px;
  background:#F1F8F4;color:var(--ev-cp-green-700);font-size:.9rem;
}
.ev-config-combobox-option-copy{min-width:0}
.ev-config-combobox-option-copy strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.74rem;font-weight:900}
.ev-config-combobox-option-copy small{display:block;margin-top:2px;color:#94A3B8;font-size:.61rem;font-weight:750}
.ev-config-combobox-check{opacity:0;color:var(--ev-cp-green);font-size:1rem}
.ev-config-combobox-option.is-selected .ev-config-combobox-check{opacity:1}
.ev-config-combobox-loading,.ev-config-combobox-empty{
  min-height:78px;display:flex;align-items:center;justify-content:center;gap:8px;padding:14px;
  color:#64748B;font-size:.7rem;font-weight:800;text-align:center;
}
.ev-config-combobox-empty{flex-direction:column;gap:3px}
.ev-config-combobox-empty>i{margin-bottom:3px;color:#94A3B8;font-size:1rem}
.ev-config-combobox-empty strong{color:#475569;font-size:.73rem;font-weight:900}
.ev-config-combobox-empty span{max-width:290px;color:#94A3B8;font-size:.64rem;line-height:1.45}
.ev-config-combobox-empty.is-error>i,.ev-config-combobox-empty.is-error span{color:#B91C1C}
.ev-config-refresh{
  width:42px;height:42px;display:grid;place-items:center;flex:0 0 42px;
  border:1px solid var(--ev-cp-border);border-radius:14px;background:#fff;
  color:var(--ev-cp-green-900);transition:.16s ease;
}
.ev-config-refresh:hover:not(:disabled){border-color:#FDBA74;color:var(--ev-cp-orange-dark);background:var(--ev-cp-orange-050);transform:translateY(-1px)}
.ev-config-refresh.is-spinning i{animation:evCpSpin .75s linear infinite}
@keyframes evCpSpin{to{transform:rotate(360deg)}}

.ev-config-workspace-nav{
  padding:12px 18px;display:flex;align-items:center;justify-content:space-between;gap:14px;
  border-bottom:1px solid var(--ev-cp-border);background:#FCFDFC;
}
.ev-config-tabs{
  display:flex;align-items:center;gap:6px;padding:6px;
  border:1px solid var(--ev-cp-border);border-radius:999px;background:#F8FAFC;
}
.ev-config-tabs button{
  display:inline-flex;align-items:center;justify-content:center;gap:7px;
  border:0;border-radius:999px;padding:8px 12px;background:transparent;
  color:#475569;font-family:inherit;font-size:.8rem;font-weight:900;transition:.16s ease;
}
.ev-config-tabs button:hover{color:var(--ev-cp-orange-dark)}
.ev-config-tabs button.is-active{background:#fff;color:var(--ev-cp-green-900);box-shadow:0 5px 14px rgba(15,23,42,.08)}
.ev-config-scope-context{display:flex;align-items:center;justify-content:flex-end;gap:9px;min-width:0;color:var(--ev-cp-muted)}
.ev-config-scope-context>i{color:var(--ev-cp-green-700);font-size:1rem}
.ev-config-scope-context>div{min-width:0;text-align:right}
.ev-config-scope-context span{display:block;color:#334155;font-size:.78rem;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ev-config-scope-context small{display:block;margin-top:1px;max-width:470px;color:#94A3B8;font-size:.67rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.ev-config-workspace-body{padding:18px;background:linear-gradient(180deg,#FCFDFC,#F8FAF9)}
.ev-config-panel{display:none}
.ev-config-panel.is-active{display:block;animation:evCpFade .18s ease}
@keyframes evCpFade{from{opacity:.55;transform:translateY(3px)}to{opacity:1;transform:none}}
.ev-config-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin:0 0 14px}
.ev-config-section-kicker{display:block;margin-bottom:3px;color:var(--ev-cp-orange);font-size:.68rem;font-weight:900;letter-spacing:.095em}
.ev-config-section-head h2{margin:0;color:var(--ev-cp-green-900);font-size:1.12rem;font-weight:950;letter-spacing:-.015em}
.ev-config-section-head p{margin:4px 0 0;color:var(--ev-cp-muted);font-size:.82rem;line-height:1.5}
.ev-config-count{padding:6px 10px;border-radius:999px;background:#fff;border:1px solid var(--ev-cp-border);color:#475569;font-size:.69rem;font-weight:900;white-space:nowrap}
.ev-config-info{
  display:flex;align-items:flex-start;gap:10px;margin:0 0 13px;padding:11px 13px;
  border:1px solid #FED7AA;border-radius:15px;background:var(--ev-cp-orange-050);color:#9A3412;
}
.ev-config-info>i{margin-top:1px}.ev-config-info strong,.ev-config-info span{display:block}
.ev-config-info strong{font-size:.76rem;font-weight:950}.ev-config-info span{margin-top:2px;color:#7C4A20;font-size:.71rem;line-height:1.45}
.ev-config-field-help{display:block;margin-top:5px;color:#718096;font-size:.67rem;line-height:1.4}

/* Listado administrativo */
.ev-config-list{display:grid;gap:12px}
.ev-config-row{
  overflow:hidden;background:#fff;border:1px solid rgba(148,163,184,.19);
  border-radius:20px;box-shadow:var(--ev-cp-shadow-soft);
  transition:transform .16s ease,border-color .16s ease,box-shadow .16s ease;
}
.ev-config-row:hover{transform:translateY(-1px);border-color:rgba(234,124,18,.35);box-shadow:0 14px 30px rgba(15,23,42,.075)}
.ev-config-row.has-pending{border-color:#FDBA74;box-shadow:0 0 0 3px rgba(234,124,18,.08),var(--ev-cp-shadow-soft)}
.ev-config-row-main{
  display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:18px;
  min-height:92px;padding:15px 16px;
}
.ev-config-row-identity{display:flex;align-items:flex-start;gap:12px;min-width:0}
.ev-config-row-icon{
  width:42px;height:42px;display:grid;place-items:center;flex:0 0 42px;
  border-radius:14px;background:var(--ev-cp-green-050);border:1px solid #D1FAE5;
  color:var(--ev-cp-green-900);font-size:1rem;
}
.ev-config-row-copy{min-width:0}
.ev-config-row-copy h3{margin:0;color:var(--ev-cp-text);font-size:.9rem;font-weight:950;line-height:1.35}
.ev-config-row-copy p{margin:3px 0 0;color:var(--ev-cp-muted);font-size:.74rem;line-height:1.48}
.ev-config-row-meta{display:flex;align-items:center;flex-wrap:wrap;gap:6px 10px;margin-top:7px}
.ev-config-origin,.ev-config-mode,.ev-config-pending{
  display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:4px 8px;
  font-size:.63rem;font-weight:850;
}
.ev-config-origin{background:#F8FAFC;border:1px solid #E2E8F0;color:#64748B}
.ev-config-origin.is-direct{background:var(--ev-cp-orange-050);border-color:#FED7AA;color:#9A4F08}
.ev-config-mode{padding:0;color:#64748B}
.ev-config-pending{display:none;background:#FFF7ED;border:1px solid #FDBA74;color:#9A3412}
.ev-config-row.has-pending .ev-config-pending{display:inline-flex}
.ev-config-row-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap}
.ev-config-status{
  display:inline-flex;align-items:center;gap:5px;padding:5px 9px;border-radius:999px;
  background:#F1F5F9;color:#64748B;font-size:.67rem;font-weight:950;white-space:nowrap;
}
.ev-config-status::before{content:"";width:6px;height:6px;border-radius:50%;background:#94A3B8}
.ev-config-row.is-enabled .ev-config-status{background:var(--ev-cp-green-050);color:#166534}
.ev-config-row.is-enabled .ev-config-status::before{background:var(--ev-cp-green)}
.ev-config-row.is-disabled .ev-config-status{background:#F8FAFC;color:#64748B}
.ev-config-value-display{
  display:flex;align-items:baseline;gap:4px;min-width:92px;justify-content:flex-end;
  color:var(--ev-cp-green-900);font-weight:950;white-space:nowrap;
}
.ev-config-value-display strong{font-size:1rem}.ev-config-value-display span{font-size:.69rem;color:#64748B;font-weight:800}
.ev-config-switch{position:relative;width:44px;height:25px;flex:0 0 44px;margin:0}
.ev-config-switch input{position:absolute;opacity:0;pointer-events:none}
.ev-config-switch>span{position:absolute;inset:0;border-radius:999px;background:#CBD5E1;cursor:pointer;transition:.2s ease}
.ev-config-switch>span::before{content:"";position:absolute;width:19px;height:19px;left:3px;top:3px;border-radius:50%;background:#fff;box-shadow:0 2px 7px rgba(15,23,42,.22);transition:.2s ease}
.ev-config-switch input:checked+span{background:var(--ev-cp-green-700)}
.ev-config-switch input:checked+span::before{transform:translateX(19px)}
.ev-config-switch input:focus-visible+span{outline:3px solid rgba(22,163,74,.16);outline-offset:2px}
.ev-config-edit-btn{
  display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:38px;
  padding:8px 11px;border:1px solid #DCE4EE;border-radius:12px;background:#fff;
  color:#475569;font-family:inherit;font-size:.71rem;font-weight:900;transition:.16s ease;
}
.ev-config-edit-btn:hover,.ev-config-edit-btn[aria-expanded="true"]{border-color:#FDBA74;background:var(--ev-cp-orange-050);color:var(--ev-cp-orange-dark)}
.ev-config-edit-btn i{transition:transform .16s ease}.ev-config-edit-btn[aria-expanded="true"] i{transform:rotate(180deg)}

.ev-config-editor{display:none;border-top:1px solid var(--ev-cp-border);background:linear-gradient(180deg,#FCFDFC,#fff)}
.ev-config-editor.is-open{display:block;animation:evCpEditor .18s ease}
@keyframes evCpEditor{from{opacity:.35;transform:translateY(-3px)}to{opacity:1;transform:none}}
.ev-config-editor-inner{padding:15px 16px 16px}
.ev-config-editor-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px}
.ev-config-editor-head strong{display:block;color:var(--ev-cp-green-900);font-size:.8rem;font-weight:950}
.ev-config-editor-head span{display:block;margin-top:2px;color:#94A3B8;font-size:.68rem}
.ev-config-editor-close{border:0;background:transparent;color:#94A3B8;padding:3px;font-size:1rem}.ev-config-editor-close:hover{color:var(--ev-cp-orange-dark)}
.ev-config-controls{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.ev-config-controls .full{grid-column:1/-1}
.ev-config-controls label{display:block;margin:0 0 5px;color:#64748B;font-size:.66rem;font-weight:850}
.ev-config-controls .form-control,.ev-config-controls .form-select{
  min-height:41px;border:1px solid #DCE4EE;border-radius:12px;color:#334155;
  font-size:.76rem;box-shadow:none;
}
.ev-config-controls .form-control:focus,.ev-config-controls .form-select:focus{border-color:rgba(22,163,74,.5);box-shadow:0 0 0 4px rgba(22,163,74,.09)}
.ev-config-control-with-unit{display:flex;align-items:center;gap:8px}
.ev-config-control-with-unit .form-control{min-width:0;flex:1}
.ev-config-unit{display:grid;place-items:center;min-width:43px;min-height:41px;padding:0 9px;border-radius:12px;background:#fff;border:1px solid #DCE4EE;color:var(--ev-cp-green-900);font-size:.75rem;font-weight:950}
.ev-config-editor-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:13px;padding-top:12px;border-top:1px dashed #E2E8F0}
.ev-config-editor-footer small{color:#94A3B8;font-size:.67rem;line-height:1.4}
.ev-config-save{
  display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;
  padding:9px 14px;border:0;border-radius:13px;
  background:linear-gradient(135deg,var(--ev-cp-green-900),#15803D);color:#fff;
  font-family:inherit;font-size:.73rem;font-weight:900;box-shadow:0 10px 22px rgba(15,89,47,.18);transition:.16s ease;
}
.ev-config-save:hover:not(:disabled){color:#fff;transform:translateY(-1px)}
.ev-config-save:disabled{cursor:not-allowed;opacity:.55;box-shadow:none}

/* Historial */
.ev-config-history-wrap{overflow:auto;background:#fff;border:1px solid rgba(148,163,184,.19);border-radius:18px;box-shadow:var(--ev-cp-shadow-soft)}
.ev-config-history-wrap table{min-width:820px}
.ev-config-history-wrap thead th{padding:13px 15px;background:#F8FAFC;border-bottom:1px solid var(--ev-cp-border);color:#64748B;font-size:.68rem;font-weight:950;white-space:nowrap}
.ev-config-history-wrap tbody td{padding:13px 15px;border-color:#E8EDF3;color:#374151;font-size:.73rem;vertical-align:middle}
.ev-config-history-wrap tbody tr:hover{background:#FFFCF8}
.ev-config-history-badge{display:inline-flex;padding:4px 8px;border-radius:999px;background:#F1F5F9;color:#475569;font-size:.63rem;font-weight:900;text-transform:capitalize}

/* Estados de carga */
.ev-config-loading,.ev-config-empty,.ev-config-error{
  min-height:165px;padding:28px;border:1px solid rgba(148,163,184,.19);
  border-radius:18px;background:#fff;text-align:center;
}
.ev-config-loading{display:grid;place-items:center}
.ev-config-loading-inner{width:min(100%,420px)}
.ev-config-loading-icon,.ev-config-error-icon{
  width:44px;height:44px;display:grid;place-items:center;margin:0 auto 12px;
  border-radius:14px;background:var(--ev-cp-green-050);color:var(--ev-cp-green-900);
}
.ev-config-loading strong,.ev-config-empty strong,.ev-config-error strong{display:block;color:var(--ev-cp-green-900);font-size:.85rem;font-weight:950}
.ev-config-loading span,.ev-config-empty span,.ev-config-error span{display:block;margin-top:5px;color:var(--ev-cp-muted);font-size:.73rem;line-height:1.5}
.ev-config-skeleton-lines{display:grid;gap:7px;margin-top:15px}
.ev-config-skeleton-lines i{display:block;height:9px;border-radius:999px;background:linear-gradient(90deg,#F1F5F9,#E2E8F0,#F1F5F9);background-size:200% 100%;animation:evCpSkeleton 1.1s linear infinite}
.ev-config-skeleton-lines i:nth-child(2){width:82%;margin:auto}.ev-config-skeleton-lines i:nth-child(3){width:62%;margin:auto}
@keyframes evCpSkeleton{to{background-position:-200% 0}}
.ev-config-error{border-color:#FECACA;background:#FFFCFC}.ev-config-error-icon{background:var(--ev-cp-red-050);color:#B91C1C}
.ev-config-retry{margin-top:14px;padding:8px 13px;border:1px solid #FDBA74;border-radius:11px;background:#fff;color:var(--ev-cp-orange-dark);font-family:inherit;font-size:.72rem;font-weight:900}
.ev-config-empty{display:grid;place-items:center;border-style:dashed}

/* SweetAlert EV */
.ev-config-swal{border-radius:27px!important;border:1px solid #E5E7EB!important;box-shadow:0 30px 80px rgba(15,23,42,.23)!important}
.ev-config-swal-title{color:var(--ev-cp-green-900)!important;font-weight:950!important}
.ev-config-swal-confirm{background:linear-gradient(135deg,var(--ev-cp-orange),#F59E0B)!important;border:0!important;border-radius:14px!important;padding:11px 19px!important;font-weight:900!important}
.ev-config-swal-cancel{background:#fff!important;color:#475569!important;border:1px solid #D1D5DB!important;border-radius:14px!important;padding:11px 19px!important;font-weight:900!important}
.ev-config-pilot-summary{text-align:left;font-size:.84rem;line-height:1.55}
.ev-config-pilot-summary strong{color:var(--ev-cp-green-900)}
.ev-config-pilot-summary ul{margin:12px 0 0;padding:0;list-style:none;display:grid;gap:7px}
.ev-config-pilot-summary li{display:flex;align-items:flex-start;gap:8px;color:#475569}
.ev-config-pilot-summary li::before{content:"\F26A";font-family:"bootstrap-icons";color:var(--ev-cp-green);font-size:.78rem;margin-top:1px}

@media(max-width:991.98px){
  .ev-config-summary{grid-template-columns:1fr}
  .ev-config-workspace-nav{align-items:flex-start;flex-direction:column}
  .ev-config-scope-context{justify-content:flex-start;width:100%}
  .ev-config-scope-context>div{text-align:left}
  .ev-config-row-main{grid-template-columns:1fr}
  .ev-config-row-actions{justify-content:flex-start;padding-left:54px}
}
@media(max-width:767.98px){
  #evConfiguracionPlataforma{padding:10px 8px 22px}
  .ev-config-hero{align-items:flex-start;flex-direction:column;padding:15px}
  .ev-config-hero h2{font-size:1.65rem}
  .ev-config-pilot{width:100%}
  .ev-config-workspace-head,.ev-config-workspace-body{padding:14px}
  .ev-config-scope-control{width:100%}
  .ev-config-tabs{width:100%;border-radius:16px}
  .ev-config-tabs button{flex:1;padding:8px 7px}
  .ev-config-scope-context small{white-space:normal}
  .ev-config-section-head{align-items:flex-start;flex-direction:column;gap:8px}
  .ev-config-row-actions{padding-left:0}
  .ev-config-controls{grid-template-columns:1fr}
  .ev-config-controls .full{grid-column:auto}
  .ev-config-editor-footer{align-items:stretch;flex-direction:column}
  .ev-config-save{width:100%}
}
@media(max-width:520px){
  .ev-config-hero-copy{gap:10px}.ev-config-hero-icon{width:48px;height:48px;border-radius:16px}
  .ev-config-tabs button i{display:none}
  .ev-config-row-main{padding:14px}
  .ev-config-row-actions{gap:8px}
  .ev-config-edit-btn{flex:1}
  .ev-config-scope-context{display:none}
}

.ev-config-pilot-schedule{margin-top:15px;padding:13px;border:1px solid #E2E8F0;border-radius:14px;background:#F8FAFC;text-align:left}
.ev-config-pilot-schedule>label,.ev-config-pilot-dates label{display:block;margin-bottom:5px;color:#154D32;font-size:.72rem;font-weight:900}
.ev-config-pilot-schedule .swal2-select{width:100%;height:42px;margin:0;padding:0 12px;border:1px solid #CBD5E1;border-radius:11px;background:#fff;color:#1F2937;font-family:Poppins,sans-serif;font-size:.78rem}
.ev-config-pilot-dates{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
.ev-config-pilot-dates[hidden]{display:none!important}
.ev-config-pilot-dates .swal2-input{width:100%;height:42px;margin:0;padding:0 10px;border:1px solid #CBD5E1;border-radius:11px;font-family:Poppins,sans-serif;font-size:.74rem;box-shadow:none}
.ev-config-pilot-dates small{grid-column:1/-1;color:#718096;font-size:.66rem;line-height:1.4}
@media(max-width:640px){.ev-config-pilot-dates{grid-template-columns:1fr}}


/* =========================================================
   ALINEACIÓN UX/UI CON EL PANEL PRINCIPAL DEL VECINO
   Cards, selección, hover y acciones EV
========================================================= */
#evConfiguracionPlataforma{
  --ev-cp-radius:20px;
  --ev-cp-radius-lg:26px;
  --ev-cp-shadow:0 18px 46px rgba(15,23,42,.08);
  --ev-cp-shadow-soft:0 10px 26px rgba(15,23,42,.06);
  --ev-cp-shadow-hover:0 24px 58px rgba(15,23,42,.11);
  --ev-cp-shadow-orange:0 18px 38px rgba(234,124,18,.16);
  padding:18px 18px 32px;
}

/* Hero con el mismo remate superior del dashboard */
.ev-config-hero{
  position:relative;
  isolation:isolate;
  min-height:142px;
  padding:24px 26px;
  border-radius:var(--ev-cp-radius-lg);
  box-shadow:var(--ev-cp-shadow);
}
.ev-config-hero::before{
  content:"";
  position:absolute;
  inset:0 0 auto 0;
  height:4px;
  background:linear-gradient(90deg,var(--ev-cp-green-900),var(--ev-cp-green),var(--ev-cp-orange));
}
.ev-config-hero-icon{
  width:58px;
  height:58px;
  border-radius:22px;
  background:linear-gradient(135deg,rgba(187,247,208,.72),rgba(255,255,255,.98));
  border:1px solid rgba(22,163,74,.15);
  box-shadow:none;
  transition:transform .16s ease,box-shadow .16s ease;
}
.ev-config-hero:hover .ev-config-hero-icon{transform:scale(1.035)}
.ev-config-pilot{
  min-height:44px;
  padding:11px 18px;
  border-radius:16px;
  background:linear-gradient(135deg,var(--ev-cp-orange),#F59E0B);
  box-shadow:var(--ev-cp-shadow-orange);
  transition:transform .16s ease,box-shadow .16s ease,filter .16s ease;
}
.ev-config-pilot:hover:not(:disabled),
.ev-config-pilot:focus-visible:not(:disabled){
  transform:translateY(-2px);
  box-shadow:0 22px 44px rgba(234,124,18,.23);
  filter:saturate(1.04);
  outline:0;
}
.ev-config-pilot:focus-visible{box-shadow:0 0 0 4px rgba(234,124,18,.16),var(--ev-cp-shadow-orange)}

/* Indicadores: misma construcción de las tarjetas resumen */
.ev-config-summary{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:14px;
  margin:14px 0;
}
.ev-config-summary article.ev-config-summary-card{
  position:relative;
  min-height:128px;
  padding:18px;
  display:flex;
  align-items:flex-start;
  gap:14px;
  overflow:hidden;
  border:1px solid rgba(148,163,184,.16);
  border-radius:var(--ev-cp-radius);
  background:#fff;
  box-shadow:var(--ev-cp-shadow);
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease;
}
.ev-config-summary article.ev-config-summary-card::after{
  content:"";
  position:absolute;
  left:18px;
  right:18px;
  bottom:0;
  height:3px;
  border-radius:999px 999px 0 0;
  background:linear-gradient(90deg,rgba(15,89,47,.42),rgba(234,124,18,.34));
  opacity:0;
  transform:scaleX(.82);
  transform-origin:center;
  transition:opacity .16s ease,transform .16s ease;
}
.ev-config-summary article.ev-config-summary-card:hover{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.22);
  box-shadow:var(--ev-cp-shadow-hover);
}
.ev-config-summary article.ev-config-summary-card:hover::after{opacity:1;transform:scaleX(1)}
.ev-config-summary article.ev-config-summary-card:hover .ev-config-summary-icon{transform:scale(1.035)}
.ev-config-summary-icon{
  width:54px;
  height:54px;
  flex:0 0 54px;
  display:grid;
  place-items:center;
  border-radius:20px;
  color:var(--ev-cp-green-900);
  background:linear-gradient(135deg,rgba(187,247,208,.72),rgba(236,253,245,.96));
  border:1px solid rgba(22,163,74,.12);
  font-size:1.25rem;
  transition:transform .16s ease;
}
.ev-config-summary-orange .ev-config-summary-icon{
  color:var(--ev-cp-orange);
  background:linear-gradient(135deg,rgba(255,237,213,.95),rgba(255,247,237,.98));
  border-color:rgba(234,124,18,.13);
}
.ev-config-summary-body{min-width:0;flex:1 1 auto}
.ev-config-summary-body>span{display:block;color:#6B7280;font-size:.77rem;font-weight:850;line-height:1.2}
.ev-config-summary-body>strong{
  display:block;
  margin:7px 0 5px;
  color:var(--ev-cp-green-900);
  font-size:1.35rem;
  line-height:1.06;
  font-weight:950;
  letter-spacing:-.03em;
}
.ev-config-summary-orange .ev-config-summary-body>strong{color:var(--ev-cp-orange-dark)}
.ev-config-summary-body>small{display:block;color:#6B7280;font-size:.76rem;line-height:1.3}

/* Contenedor operativo */
.ev-config-workspace{
  border-radius:var(--ev-cp-radius);
  box-shadow:var(--ev-cp-shadow);
}
.ev-config-workspace-head{padding:17px 18px}
.ev-config-workspace-nav{padding:11px 18px;border-top:1px solid rgba(229,231,235,.72)}
.ev-config-workspace-body{padding:18px;background:linear-gradient(180deg,#FCFDFC,#F7F9F8)}

/* Tabs: la sección seleccionada adopta el efecto EV naranja */
.ev-config-tabs{
  padding:4px;
  gap:4px;
  border:1px solid rgba(229,231,235,.9);
  background:#F8FAFC;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.85);
}
.ev-config-tabs button{
  position:relative;
  border:1px solid transparent;
  border-radius:13px;
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease,color .16s ease;
}
.ev-config-tabs button:hover{
  color:var(--ev-cp-orange-dark);
  background:#FFF7ED;
  border-color:rgba(234,124,18,.16);
}
.ev-config-tabs button.is-active{
  color:var(--ev-cp-orange-dark);
  border-color:rgba(234,124,18,.30);
  background:radial-gradient(circle at 12% 18%,rgba(255,237,213,.92),transparent 38%),linear-gradient(135deg,#FFF,#FFF7ED);
  box-shadow:0 10px 24px rgba(234,124,18,.12);
}
.ev-config-tabs button.is-active i{color:var(--ev-cp-orange)}
.ev-config-tabs button:focus-visible{outline:0;box-shadow:0 0 0 4px rgba(234,124,18,.13)}

/* Filas administrativas con el comportamiento de Acciones rápidas */
.ev-config-row{
  position:relative;
  isolation:isolate;
  border-radius:18px;
  background:linear-gradient(180deg,#fff,#fbfdfb);
  box-shadow:0 8px 18px rgba(15,23,42,.04);
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease;
}
.ev-config-row::after{
  content:"";
  position:absolute;
  left:18px;
  right:18px;
  bottom:0;
  height:3px;
  z-index:2;
  border-radius:999px 999px 0 0;
  background:linear-gradient(90deg,var(--ev-cp-orange),#F59E0B);
  opacity:0;
  transform:scaleX(.82);
  transform-origin:center;
  transition:opacity .16s ease,transform .16s ease;
  pointer-events:none;
}
.ev-config-row:hover{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.46);
  box-shadow:var(--ev-cp-shadow-orange);
  background:radial-gradient(circle at 7% 18%,rgba(255,237,213,.72),transparent 28%),linear-gradient(135deg,#FFF,#FFF9F1);
}
.ev-config-row:hover::after,
.ev-config-row.is-selected::after,
.ev-config-row.has-pending::after{opacity:1;transform:scaleX(1)}
.ev-config-row:hover .ev-config-row-icon,
.ev-config-row.is-selected .ev-config-row-icon,
.ev-config-row.has-pending .ev-config-row-icon{
  color:#fff;
  border-color:rgba(234,124,18,.36);
  background:linear-gradient(135deg,var(--ev-cp-orange),#F59E0B);
  transform:scale(1.045);
  box-shadow:0 10px 22px rgba(234,124,18,.18);
}
.ev-config-row:hover .ev-config-row-copy h3,
.ev-config-row.is-selected .ev-config-row-copy h3,
.ev-config-row.has-pending .ev-config-row-copy h3{color:var(--ev-cp-orange-dark)}
.ev-config-row.is-selected,
.ev-config-row.has-pending{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.52);
  background:radial-gradient(circle at 7% 18%,rgba(255,237,213,.90),transparent 31%),linear-gradient(135deg,#FFF,#FFF7ED);
  box-shadow:var(--ev-cp-shadow-orange);
}
.ev-config-row.is-saving{pointer-events:none;opacity:.88}
.ev-config-row.is-saved{border-color:rgba(22,163,74,.42);box-shadow:0 18px 38px rgba(22,163,74,.12)}
.ev-config-row.is-save-error{border-color:rgba(220,38,38,.36);box-shadow:0 18px 38px rgba(220,38,38,.10)}
.ev-config-row-main{min-height:94px;padding:15px 16px}
.ev-config-row-icon{
  width:42px;
  height:42px;
  border-radius:15px;
  background:linear-gradient(135deg,rgba(187,247,208,.58),rgba(236,253,245,.95));
  border-color:rgba(22,163,74,.14);
  transition:background .16s ease,color .16s ease,transform .16s ease,border-color .16s ease,box-shadow .16s ease;
}
.ev-config-row-copy h3{font-size:.88rem;transition:color .16s ease}
.ev-config-row-copy p{font-size:.74rem}

/* Estado, interruptor y acción Configurar */
.ev-config-status{border:1px solid transparent}
.ev-config-row.is-enabled .ev-config-status{border-color:rgba(22,163,74,.12)}
.ev-config-row.is-disabled .ev-config-status{border-color:rgba(148,163,184,.16)}
.ev-config-switch>span{box-shadow:inset 0 0 0 1px rgba(148,163,184,.18)}
.ev-config-switch:hover>span{box-shadow:inset 0 0 0 1px rgba(234,124,18,.24),0 5px 12px rgba(15,23,42,.08)}
.ev-config-switch input:checked+span{background:linear-gradient(135deg,var(--ev-cp-green-900),var(--ev-cp-green))}
.ev-config-switch input:focus-visible+span{outline:0;box-shadow:0 0 0 4px rgba(22,163,74,.13)}
.ev-config-edit-btn{
  min-height:40px;
  padding:9px 13px;
  border-radius:13px;
  border-color:rgba(148,163,184,.25);
  background:#fff;
  color:#475569;
  box-shadow:0 5px 14px rgba(15,23,42,.035);
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease,color .16s ease;
}
.ev-config-edit-btn:hover{
  transform:translateY(-1px);
  border-color:rgba(234,124,18,.38);
  color:var(--ev-cp-orange-dark);
  background:#FFF7ED;
  box-shadow:0 12px 24px rgba(234,124,18,.10);
}
.ev-config-edit-btn[aria-expanded="true"]{
  color:#fff;
  border-color:transparent;
  background:linear-gradient(135deg,var(--ev-cp-orange),#F59E0B);
  box-shadow:var(--ev-cp-shadow-orange);
}
.ev-config-edit-btn:focus-visible{outline:0;box-shadow:0 0 0 4px rgba(234,124,18,.14)}

/* Editor seleccionado */
.ev-config-editor{
  border-top-color:rgba(234,124,18,.18);
  background:linear-gradient(180deg,#FFFDF9,#FFFFFF);
}
.ev-config-editor.is-open{animation:evCpEditorPremium .2s ease}
@keyframes evCpEditorPremium{from{opacity:.4;transform:translateY(-5px)}to{opacity:1;transform:none}}
.ev-config-editor-inner{padding:17px 16px 16px}
.ev-config-editor-head strong{font-size:.82rem}
.ev-config-editor-close{
  width:32px;
  height:32px;
  display:grid;
  place-items:center;
  border-radius:11px;
  background:#F8FAFC;
  transition:background .16s ease,color .16s ease,transform .16s ease;
}
.ev-config-editor-close:hover{color:#fff;background:linear-gradient(135deg,var(--ev-cp-orange),#F59E0B);transform:rotate(4deg)}
.ev-config-controls .form-control,
.ev-config-controls .form-select{
  min-height:43px;
  border-radius:13px;
  border-color:rgba(148,163,184,.30);
  background:#fff;
  transition:border-color .16s ease,box-shadow .16s ease,background .16s ease;
}
.ev-config-controls .form-control:hover,
.ev-config-controls .form-select:hover{border-color:rgba(234,124,18,.30)}
.ev-config-controls .form-control:focus,
.ev-config-controls .form-select:focus{
  border-color:rgba(22,163,74,.56);
  background:#fff;
  box-shadow:0 0 0 4px rgba(22,163,74,.09),0 8px 18px rgba(15,23,42,.04);
}
.ev-config-unit{min-height:43px;border-radius:13px}
.ev-config-editor-footer{margin-top:15px;padding-top:14px}

/* Guardar: mismo CTA naranja del Panel principal */
.ev-config-save{
  min-height:43px;
  padding:10px 17px;
  border-radius:14px;
  background:linear-gradient(135deg,var(--ev-cp-orange),#F59E0B);
  color:#fff;
  box-shadow:var(--ev-cp-shadow-orange);
  transition:transform .16s ease,box-shadow .16s ease,filter .16s ease,background .16s ease;
}
.ev-config-save:hover:not(:disabled),
.ev-config-save:focus-visible:not(:disabled){
  transform:translateY(-2px);
  color:#fff;
  box-shadow:0 22px 42px rgba(234,124,18,.23);
  filter:saturate(1.04);
  outline:0;
}
.ev-config-save:active:not(:disabled){transform:translateY(0)}
.ev-config-save.is-loading{background:linear-gradient(135deg,#D97706,var(--ev-cp-orange));box-shadow:0 14px 28px rgba(234,124,18,.17)}
.ev-config-save.is-success{background:linear-gradient(135deg,var(--ev-cp-green-900),var(--ev-cp-green));box-shadow:0 16px 32px rgba(22,163,74,.18)}
.ev-config-save.is-error{background:linear-gradient(135deg,#B91C1C,#EF4444);box-shadow:0 16px 32px rgba(220,38,38,.16)}
.ev-config-save:disabled{opacity:.72}

/* Combobox y recarga con foco/hover EV */
.ev-config-combobox-field,
.ev-config-refresh{transition:border-color .16s ease,box-shadow .16s ease,transform .16s ease,background .16s ease,color .16s ease}
.ev-config-combobox.is-open .ev-config-combobox-field,
.ev-config-combobox:focus-within .ev-config-combobox-field{border-color:rgba(22,163,74,.52);box-shadow:0 0 0 4px rgba(22,163,74,.09)}
.ev-config-refresh:hover,
.ev-config-refresh:focus-visible{transform:translateY(-1px);color:var(--ev-cp-orange-dark);border-color:rgba(234,124,18,.38);background:#FFF7ED;box-shadow:0 12px 24px rgba(234,124,18,.10);outline:0}

@media(max-width:991.98px){
  .ev-config-summary{grid-template-columns:1fr}
  .ev-config-summary article.ev-config-summary-card{min-height:112px}
}
@media(max-width:767.98px){
  #evConfiguracionPlataforma{padding:10px 8px 24px}
  .ev-config-hero{padding:18px 16px;border-radius:19px}
  .ev-config-summary{gap:10px;margin:10px 0}
  .ev-config-summary article.ev-config-summary-card{min-height:100px;padding:14px;border-radius:17px}
  .ev-config-summary-icon{width:46px;height:46px;flex-basis:46px;border-radius:17px}
  .ev-config-summary-body>strong{font-size:1.16rem}
  .ev-config-workspace{border-radius:17px}
  .ev-config-row{border-radius:17px}
}
@media(prefers-reduced-motion:reduce){
  #evConfiguracionPlataforma *,
  #evConfiguracionPlataforma *::before,
  #evConfiguracionPlataforma *::after{scroll-behavior:auto!important;transition-duration:.01ms!important;animation-duration:.01ms!important;animation-iteration-count:1!important}
}


/* =========================================================
   EV · CIERRE VISUAL
   Paridad con Panel principal / Acciones rápidas
========================================================= */
/* El piloto se configura con las reglas individuales por alcance. */
#evConfiguracionPlataforma .ev-config-pilot,
#evConfiguracionPlataforma .ev-config-info{display:none!important}

/* La cabecera ya no reserva una columna para una acción eliminada. */
#evConfiguracionPlataforma .ev-config-hero{
  justify-content:flex-start;
}
#evConfiguracionPlataforma .ev-config-hero-copy{
  max-width:980px;
}

/* Resumen: mismas proporciones, centrado y microinteracciones del dashboard. */
#evConfiguracionPlataforma .ev-config-summary{
  gap:14px;
  margin:14px 0;
}
#evConfiguracionPlataforma .ev-config-summary article.ev-config-summary-card{
  position:relative;
  min-height:118px;
  padding:18px;
  display:flex;
  align-items:center;
  gap:14px;
  overflow:hidden;
  border:1px solid rgba(229,231,235,.92);
  border-radius:20px;
  background:linear-gradient(180deg,#fff,#fbfdfb);
  box-shadow:0 8px 18px rgba(15,23,42,.04);
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease;
}
#evConfiguracionPlataforma .ev-config-summary article.ev-config-summary-card::after{
  content:"";
  position:absolute;
  left:18px;
  right:18px;
  bottom:0;
  height:3px;
  border-radius:999px 999px 0 0;
  background:linear-gradient(90deg,rgba(15,89,47,.42),rgba(234,124,18,.34));
  opacity:0;
  transform:scaleX(.82);
  transform-origin:center;
  transition:opacity .16s ease,transform .16s ease;
}
#evConfiguracionPlataforma .ev-config-summary article.ev-config-summary-card:hover{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.24);
  box-shadow:0 22px 48px rgba(15,23,42,.10);
  background:linear-gradient(180deg,#fff,#fffdfa);
}
#evConfiguracionPlataforma .ev-config-summary article.ev-config-summary-card:hover::after{
  opacity:1;
  transform:scaleX(1);
}
#evConfiguracionPlataforma .ev-config-summary-icon{
  width:58px!important;
  height:58px!important;
  min-width:58px;
  flex:0 0 58px!important;
  padding:0!important;
  margin:0!important;
  border-radius:22px!important;
  display:grid!important;
  place-items:center!important;
  align-self:center!important;
  line-height:1!important;
  color:var(--ev-cp-green-900);
  background:linear-gradient(135deg,rgba(187,247,208,.72),rgba(236,253,245,.96));
  border:1px solid rgba(22,163,74,.14);
  transition:transform .16s ease,box-shadow .16s ease,background .16s ease,color .16s ease;
}
#evConfiguracionPlataforma .ev-config-summary-icon i{
  display:block;
  margin:0;
  font-size:1.42rem!important;
  line-height:1!important;
}
#evConfiguracionPlataforma .ev-config-summary-orange .ev-config-summary-icon{
  color:var(--ev-cp-orange);
  background:linear-gradient(135deg,rgba(255,237,213,.95),rgba(255,247,237,.98));
  border-color:rgba(234,124,18,.16);
}
#evConfiguracionPlataforma .ev-config-summary-card:hover .ev-config-summary-icon{
  transform:scale(1.035);
  box-shadow:0 10px 22px rgba(15,23,42,.06);
}
#evConfiguracionPlataforma .ev-config-summary-body{
  min-width:0;
  flex:1 1 auto;
  align-self:center;
}
#evConfiguracionPlataforma .ev-config-summary-body>span{
  color:#6B7280;
  font-size:.77rem;
  font-weight:850;
  line-height:1.2;
}
#evConfiguracionPlataforma .ev-config-summary-body>strong{
  margin:7px 0 4px;
  color:var(--ev-cp-green-900);
  font-size:1.32rem;
  line-height:1.04;
  font-weight:950;
  letter-spacing:-.03em;
}
#evConfiguracionPlataforma .ev-config-summary-orange .ev-config-summary-body>strong{
  color:var(--ev-cp-orange-dark);
}
#evConfiguracionPlataforma .ev-config-summary-body>small{
  min-height:0;
  color:#6B7280;
  font-size:.76rem;
  line-height:1.3;
}

/* Funcionalidades y monetización: lenguaje exacto de Acciones rápidas EV. */
#evConfiguracionPlataforma .ev-config-list{
  gap:10px;
}
#evConfiguracionPlataforma .ev-config-row{
  position:relative;
  border:1px solid rgba(229,231,235,.92);
  border-radius:16px;
  background:linear-gradient(180deg,#fff,#fbfdfb);
  box-shadow:0 8px 18px rgba(15,23,42,.04);
  overflow:hidden;
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease;
}
#evConfiguracionPlataforma .ev-config-row::after{display:none!important}
#evConfiguracionPlataforma .ev-config-row:hover,
#evConfiguracionPlataforma .ev-config-row:focus-within,
#evConfiguracionPlataforma .ev-config-row.is-selected,
#evConfiguracionPlataforma .ev-config-row.has-pending{
  transform:translateY(-2px);
  border-color:rgba(234,124,18,.46);
  box-shadow:var(--ev-cp-shadow-orange);
  background:radial-gradient(circle at 6% 18%,rgba(255,237,213,.92),transparent 30%),linear-gradient(135deg,#fff,#fff7ed);
}
#evConfiguracionPlataforma .ev-config-row-main{
  min-height:82px;
  padding:12px 14px;
}
#evConfiguracionPlataforma .ev-config-row-identity{
  align-items:center;
  gap:12px;
}
#evConfiguracionPlataforma .ev-config-row-icon{
  width:36px;
  height:36px;
  min-width:36px;
  flex:0 0 36px;
  border-radius:13px;
  display:grid;
  place-items:center;
  color:var(--ev-cp-green-900);
  background:#ECFDF3;
  border:1px solid rgba(22,163,74,.14);
  font-size:1rem;
  line-height:1;
  transition:background .16s ease,color .16s ease,transform .16s ease,border-color .16s ease,box-shadow .16s ease;
}
#evConfiguracionPlataforma .ev-config-row-icon i{
  display:block;
  margin:0;
  line-height:1;
}
#evConfiguracionPlataforma .ev-config-row:hover .ev-config-row-icon,
#evConfiguracionPlataforma .ev-config-row:focus-within .ev-config-row-icon,
#evConfiguracionPlataforma .ev-config-row.is-selected .ev-config-row-icon,
#evConfiguracionPlataforma .ev-config-row.has-pending .ev-config-row-icon{
  color:#fff;
  background:linear-gradient(135deg,var(--ev-cp-orange),#F59E0B);
  border-color:rgba(234,124,18,.36);
  transform:scale(1.045);
  box-shadow:none;
}
#evConfiguracionPlataforma .ev-config-row-copy h3{
  color:var(--ev-cp-orange-dark);
  font-size:.84rem;
  font-weight:950;
  line-height:1.18;
  transition:color .16s ease;
}
#evConfiguracionPlataforma .ev-config-row:hover .ev-config-row-copy h3,
#evConfiguracionPlataforma .ev-config-row:focus-within .ev-config-row-copy h3,
#evConfiguracionPlataforma .ev-config-row.is-selected .ev-config-row-copy h3,
#evConfiguracionPlataforma .ev-config-row.has-pending .ev-config-row-copy h3{
  color:var(--ev-cp-orange);
}
#evConfiguracionPlataforma .ev-config-row-copy p{
  margin-top:3px;
  color:#6B7280;
  font-size:.75rem;
  line-height:1.22;
}
#evConfiguracionPlataforma .ev-config-row-meta{
  margin-top:6px;
  gap:6px 9px;
}
#evConfiguracionPlataforma .ev-config-origin,
#evConfiguracionPlataforma .ev-config-mode,
#evConfiguracionPlataforma .ev-config-pending{
  font-size:.66rem;
}
#evConfiguracionPlataforma .ev-config-row-actions{
  gap:9px;
}
#evConfiguracionPlataforma .ev-config-status{
  min-height:27px;
  padding:5px 9px;
  border-radius:999px;
  font-size:.67rem;
  font-weight:900;
}
#evConfiguracionPlataforma .ev-config-edit-btn{
  min-height:36px;
  padding:8px 11px;
  border:1px solid rgba(229,231,235,.92);
  border-radius:12px;
  background:#fff;
  color:var(--ev-cp-orange-dark);
  font-size:.72rem;
  font-weight:900;
  box-shadow:none;
  transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease,color .16s ease;
}
#evConfiguracionPlataforma .ev-config-edit-btn i{
  color:var(--ev-cp-orange-dark);
  transition:color .16s ease,transform .16s ease;
}
#evConfiguracionPlataforma .ev-config-edit-btn:hover,
#evConfiguracionPlataforma .ev-config-edit-btn:focus-visible,
#evConfiguracionPlataforma .ev-config-edit-btn[aria-expanded="true"]{
  transform:translateY(-1px);
  border-color:rgba(234,124,18,.46);
  color:var(--ev-cp-orange);
  background:#FFF7ED;
  box-shadow:0 10px 22px rgba(234,124,18,.10);
  outline:0;
}
#evConfiguracionPlataforma .ev-config-edit-btn:hover i,
#evConfiguracionPlataforma .ev-config-edit-btn:focus-visible i,
#evConfiguracionPlataforma .ev-config-edit-btn[aria-expanded="true"] i{
  color:var(--ev-cp-orange);
}
#evConfiguracionPlataforma .ev-config-edit-btn[aria-expanded="true"] i{
  transform:rotate(180deg);
}

/* Editor y guardado: superficie blanca, CTA naranja y feedback EV. */
#evConfiguracionPlataforma .ev-config-editor{
  border-top:1px solid rgba(229,231,235,.82);
  background:linear-gradient(180deg,#fff,#fffdfa);
}
#evConfiguracionPlataforma .ev-config-editor-inner{
  padding:16px 14px 14px;
}
#evConfiguracionPlataforma .ev-config-save{
  min-height:40px;
  padding:9px 16px;
  border:0;
  border-radius:13px;
  background:linear-gradient(135deg,var(--ev-cp-orange),#F59E0B);
  color:#fff;
  font-size:.76rem;
  font-weight:950;
  box-shadow:0 12px 24px rgba(234,124,18,.18);
  transition:transform .16s ease,box-shadow .16s ease,filter .16s ease,background .16s ease;
}
#evConfiguracionPlataforma .ev-config-save:hover:not(:disabled),
#evConfiguracionPlataforma .ev-config-save:focus-visible:not(:disabled){
  transform:translateY(-2px);
  color:#fff;
  box-shadow:0 18px 34px rgba(234,124,18,.24);
  filter:saturate(1.04);
  outline:0;
}
#evConfiguracionPlataforma .ev-config-save:active:not(:disabled){transform:translateY(0)}

@media(max-width:767.98px){
  #evConfiguracionPlataforma .ev-config-summary article.ev-config-summary-card{
    min-height:96px;
    padding:14px;
    border-radius:17px;
  }
  #evConfiguracionPlataforma .ev-config-summary-icon{
    width:48px!important;
    height:48px!important;
    min-width:48px;
    flex-basis:48px!important;
    border-radius:17px!important;
  }
  #evConfiguracionPlataforma .ev-config-summary-icon i{font-size:1.18rem!important}
  #evConfiguracionPlataforma .ev-config-row-main{
    min-height:0;
    padding:13px;
  }
}

</style>
