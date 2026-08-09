<?php /* views/estilos/aceptacionLegalEstilo.php */ ?>
<style>
:root{
  --ev-al-verde-900:#0F592F;
  --ev-al-verde-700:#0E7A43;
  --ev-al-verde-600:#16A34A;
  --ev-al-verde-050:#F0FDF4;
  --ev-al-naranja:#EA7C12;
  --ev-al-naranja-oscuro:#C46B05;
  --ev-al-fondo:#F3F4F6;
  --ev-al-borde:#E5E7EB;
  --ev-al-texto:#1F2937;
  --ev-al-muted:#64748B;
  --ev-al-shadow:0 30px 72px rgba(15,23,42,.14);
}
*{box-sizing:border-box}
html{min-height:100%;background:var(--ev-al-fondo)}
body.ev-al-body{
  margin:0;
  min-height:100vh;
  font-family:"Poppins",system-ui,-apple-system,"Segoe UI",sans-serif;
  color:var(--ev-al-texto);
  background:
    radial-gradient(circle at 10% 8%,rgba(22,163,74,.10),transparent 28%),
    radial-gradient(circle at 92% 88%,rgba(234,124,18,.08),transparent 28%),
    var(--ev-al-fondo);
}
.ev-al-page{min-height:100vh;padding:28px 18px 24px;display:flex;align-items:center;justify-content:center}
.ev-al-shell{width:min(1040px,100%)}
.ev-al-brand{
  width:max-content;max-width:100%;margin:0 auto 16px;padding:8px 16px 8px 10px;
  display:inline-flex;align-items:center;gap:9px;border:1px solid rgba(148,163,184,.18);
  border-radius:999px;background:rgba(255,255,255,.94);box-shadow:0 10px 28px rgba(15,23,42,.07);
  color:var(--ev-al-verde-900);font-size:.86rem;font-weight:800;text-decoration:none;
}
.ev-al-brand img{width:38px;height:38px;object-fit:contain;display:block}
.ev-al-card{
  position:relative;overflow:hidden;background:#fff;border:1px solid rgba(148,163,184,.18);
  border-radius:26px;box-shadow:var(--ev-al-shadow);
}
.ev-al-card::before{
  content:"";position:absolute;inset:0 0 auto;height:4px;z-index:2;
  background:linear-gradient(90deg,var(--ev-al-verde-900),var(--ev-al-verde-600),var(--ev-al-naranja));
}
.ev-al-header{
  position:relative;padding:30px 34px 27px;display:flex;align-items:center;justify-content:space-between;gap:24px;
  background:
    radial-gradient(circle at 88% 15%,rgba(22,163,74,.13),transparent 36%),
    radial-gradient(circle at 4% 100%,rgba(234,124,18,.11),transparent 35%),
    linear-gradient(135deg,#FFFDF9 0%,#FFFFFF 48%,#F3FBF6 100%);
  border-bottom:1px solid rgba(148,163,184,.14);
}
.ev-al-header__copy{min-width:0;display:flex;align-items:flex-start;gap:16px}
.ev-al-header__icon{
  width:58px;height:58px;display:grid;place-items:center;flex:0 0 58px;border-radius:19px;
  color:var(--ev-al-verde-900);font-size:1.45rem;background:linear-gradient(145deg,#DCFCE7,#fff);
  border:1px solid rgba(22,163,74,.22);box-shadow:0 14px 28px rgba(15,89,47,.09);
}
.ev-al-kicker{display:block;margin:1px 0 5px;color:var(--ev-al-naranja);font-size:.69rem;font-weight:900;letter-spacing:.12em}
.ev-al-header h1{margin:0 0 8px;color:var(--ev-al-verde-900);font-size:clamp(1.65rem,3.4vw,2.35rem);line-height:1.08;letter-spacing:-.035em;font-weight:800}
.ev-al-header p{max-width:720px;margin:0;color:var(--ev-al-muted);font-size:.91rem;line-height:1.62}
.ev-al-header__status{
  min-width:146px;padding:14px 16px;display:flex;align-items:center;justify-content:center;gap:11px;flex:0 0 auto;
  border:1px solid rgba(22,163,74,.18);border-radius:18px;background:rgba(255,255,255,.88);
  box-shadow:0 12px 26px rgba(15,23,42,.06);
}
.ev-al-header__status strong{color:var(--ev-al-verde-900);font-size:1.7rem;line-height:1;font-weight:900}
.ev-al-header__status span{color:#475569;font-size:.72rem;line-height:1.35;font-weight:750}
.ev-al-body-content{padding:26px 30px 28px}
.ev-al-section{margin:0 0 18px}
.ev-al-section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px}
.ev-al-section-head h2{margin:2px 0 4px;color:var(--ev-al-verde-900);font-size:1.05rem;font-weight:800;letter-spacing:-.015em}
.ev-al-section-head p{margin:0;color:var(--ev-al-muted);font-size:.8rem;line-height:1.48}
.ev-al-section-kicker{display:block;color:var(--ev-al-naranja);font-size:.63rem;font-weight:900;letter-spacing:.1em}
.ev-al-docs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.ev-al-doc{
  min-width:0;min-height:218px;padding:18px;display:flex;flex-direction:column;border:1px solid var(--ev-al-borde);
  border-radius:18px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.045);
  transition:transform .17s ease,border-color .17s ease,box-shadow .17s ease;
}
.ev-al-doc:hover{transform:translateY(-2px);border-color:rgba(234,124,18,.32);box-shadow:0 16px 34px rgba(234,124,18,.08)}
.ev-al-doc__top{display:flex;align-items:flex-start;gap:12px;margin-bottom:11px}
.ev-al-doc__icon{width:42px;height:42px;display:grid;place-items:center;flex:0 0 42px;border-radius:14px;color:var(--ev-al-verde-900);background:var(--ev-al-verde-050);border:1px solid #BBF7D0;font-size:1.05rem}
.ev-al-doc__heading{min-width:0}
.ev-al-version{display:block;margin-bottom:3px;color:var(--ev-al-naranja);font-size:.61rem;font-weight:900;letter-spacing:.08em}
.ev-al-doc h3{margin:0;color:var(--ev-al-verde-900);font-size:.91rem;line-height:1.38;font-weight:800}
.ev-al-doc p{margin:0 0 15px;color:var(--ev-al-muted);font-size:.78rem;line-height:1.58}
.ev-al-doc>a{
  margin-top:auto;min-height:41px;padding:9px 12px;display:flex;align-items:center;justify-content:space-between;gap:10px;
  border:1px solid rgba(22,163,74,.17);border-radius:12px;color:var(--ev-al-verde-900);background:#F8FCF9;
  font-size:.76rem;font-weight:850;text-decoration:none;transition:.16s ease;
}
.ev-al-doc>a:hover,.ev-al-doc>a:focus{color:#fff;background:linear-gradient(135deg,var(--ev-al-verde-900),var(--ev-al-verde-600));border-color:transparent;outline:0}
.ev-al-privacy-summary{margin:0 0 20px;border:1px solid #CFE9D9;border-radius:16px;background:#FAFEFB;overflow:hidden}
.ev-al-privacy-summary summary{list-style:none;cursor:pointer;padding:13px 15px;display:flex;justify-content:space-between;align-items:center;gap:10px;color:var(--ev-al-verde-900);font-size:.8rem;font-weight:850}
.ev-al-privacy-summary summary::-webkit-details-marker{display:none}
.ev-al-privacy-summary summary span{display:flex;align-items:center;gap:8px}.ev-al-privacy-summary summary>i{transition:transform .18s ease}.ev-al-privacy-summary[open] summary>i{transform:rotate(180deg)}
.ev-al-privacy-grid{padding:14px 15px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;border-top:1px solid #DDEFE4;background:#fff}
.ev-al-privacy-grid p{min-width:0;margin:0;padding:11px 12px;border:1px solid #EEF2F7;border-radius:12px;background:#F8FAFC}
.ev-al-privacy-grid strong,.ev-al-privacy-grid span{display:block}.ev-al-privacy-grid strong{margin-bottom:3px;color:#334155;font-size:.7rem}.ev-al-privacy-grid span{color:var(--ev-al-muted);font-size:.73rem;line-height:1.45;overflow-wrap:anywhere}
.ev-al-privacy-grid a{color:var(--ev-al-verde-700);font-weight:750}
.ev-al-consent-panel{padding:17px;border:1px solid rgba(148,163,184,.18);border-radius:19px;background:linear-gradient(180deg,#FFFFFF,#FBFDFC)}
.ev-al-section-head--consent{align-items:center;margin-bottom:13px}
.ev-al-secure-badge{min-height:34px;padding:7px 11px;display:inline-flex;align-items:center;gap:6px;flex:0 0 auto;border-radius:999px;background:#F0FDF4;border:1px solid #BBF7D0;color:var(--ev-al-verde-900);font-size:.69rem;font-weight:850}
.ev-al-consents{display:grid;gap:11px}
.ev-al-check{position:relative;padding:14px 15px 14px 45px;border:1px solid var(--ev-al-borde);border-radius:14px;background:#fff;transition:background .17s ease,border-color .17s ease,box-shadow .17s ease}
.ev-al-check:hover{border-color:#CFE9D9;box-shadow:0 8px 18px rgba(15,23,42,.04)}
.ev-al-check:has(input:checked){background:var(--ev-al-verde-050);border-color:#86EFAC}
.ev-al-check input{position:absolute;left:15px;top:16px;width:18px;height:18px;margin:0;accent-color:var(--ev-al-verde-700);cursor:pointer}
.ev-al-check label{display:block;color:#374151;font-size:.8rem;line-height:1.55;cursor:pointer}.ev-al-check a{color:var(--ev-al-verde-700);font-weight:800;text-decoration:underline;text-underline-offset:2px}
.ev-al-check.is-invalid{border-color:#DC2626;background:#FEF2F2;box-shadow:0 0 0 3px rgba(220,38,38,.08)}
.ev-al-help{margin:12px 0 0;display:flex;gap:7px;align-items:flex-start;color:var(--ev-al-muted);font-size:.72rem;line-height:1.45}
.ev-al-actions{margin-top:18px;display:flex;align-items:center;justify-content:space-between;gap:11px}
.ev-al-btn{min-height:46px;padding:10px 18px;display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid transparent;border-radius:14px;font:inherit;font-size:.82rem;font-weight:850;text-decoration:none;cursor:pointer;transition:transform .16s ease,box-shadow .16s ease,background .16s ease,border-color .16s ease,filter .16s ease}
.ev-al-btn--ghost{color:#475569;background:#fff;border-color:#D8E0E8}.ev-al-btn--ghost:hover{color:var(--ev-al-verde-900);border-color:#B7D7C4;background:#F8FCF9}
.ev-al-btn--primary{min-width:230px;color:#fff;background:linear-gradient(135deg,var(--ev-al-naranja),#F59E0B);box-shadow:0 13px 28px rgba(234,124,18,.28)}
.ev-al-btn--primary:hover:not(:disabled),.ev-al-btn--primary:focus-visible:not(:disabled){color:#fff;transform:translateY(-1px);filter:brightness(1.02);box-shadow:0 17px 34px rgba(234,124,18,.34);outline:0}
.ev-al-btn--primary:disabled{opacity:.48;cursor:not-allowed;box-shadow:none;filter:saturate(.65)}
.ev-al-footer{text-align:center;color:#94A3B8;font-size:.69rem;margin-top:14px}
.ev-al-spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:evAlSpin .7s linear infinite}@keyframes evAlSpin{to{transform:rotate(360deg)}}
@media(max-width:760px){
  .ev-al-page{padding:14px 10px;align-items:flex-start}.ev-al-brand{margin-bottom:11px}.ev-al-card{border-radius:20px}
  .ev-al-header{padding:24px 20px 22px;align-items:flex-start}.ev-al-header__copy{gap:12px}.ev-al-header__icon{width:50px;height:50px;flex-basis:50px;border-radius:16px}.ev-al-header__status{display:none}
  .ev-al-body-content{padding:20px 16px 22px}.ev-al-docs{grid-template-columns:1fr}.ev-al-doc{min-height:auto}.ev-al-privacy-grid{grid-template-columns:1fr}
}
@media(max-width:520px){
  .ev-al-page{padding:9px 7px 14px}.ev-al-brand{padding:7px 13px 7px 8px}.ev-al-brand img{width:34px;height:34px}.ev-al-header{padding:22px 16px 19px}.ev-al-header__copy{display:block}.ev-al-header__icon{margin-bottom:12px}.ev-al-header h1{font-size:1.55rem}.ev-al-header p{font-size:.82rem}.ev-al-body-content{padding:17px 12px 19px}.ev-al-consent-panel{padding:14px 12px}.ev-al-section-head--consent{align-items:flex-start}.ev-al-secure-badge{display:none}.ev-al-actions{flex-direction:column-reverse;align-items:stretch}.ev-al-btn{width:100%}.ev-al-btn--primary{min-width:0}.ev-al-check{padding-right:12px}.ev-al-footer{padding:0 8px}
}
</style>
