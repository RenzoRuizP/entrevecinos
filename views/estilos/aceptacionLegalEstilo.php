<?php /* views/estilos/aceptacionLegalEstilo.php */ ?>
<style>
:root{
  --ev-al-verde-900:#0F592F;
  --ev-al-verde-700:#0E7A43;
  --ev-al-verde-600:#16A34A;
  --ev-al-verde-050:#F0FDF4;
  --ev-al-naranja:#EA7C12;
  --ev-al-borde:#E5E7EB;
  --ev-al-texto:#1F2937;
  --ev-al-muted:#6B7280;
}
*{box-sizing:border-box}
body.ev-al-body{
  margin:0;
  min-height:100vh;
  font-family:"Poppins",system-ui,-apple-system,"Segoe UI",sans-serif;
  background:radial-gradient(circle at top left,rgba(22,163,74,.12),transparent 34%),#F3F4F6;
  color:var(--ev-al-texto);
}
.ev-al-page{min-height:100vh;padding:30px 16px;display:flex;align-items:center;justify-content:center}
.ev-al-shell{width:min(920px,100%)}
.ev-al-brand{display:flex;align-items:center;justify-content:center;margin-bottom:18px}
.ev-al-brand img{height:58px;width:auto}
.ev-al-card{background:#fff;border:1px solid var(--ev-al-borde);border-radius:24px;box-shadow:0 24px 60px rgba(15,89,47,.12);overflow:hidden}
.ev-al-header{padding:31px 34px 25px;background:linear-gradient(135deg,var(--ev-al-verde-900),var(--ev-al-verde-700));color:#fff;text-align:center}
.ev-al-header__icon{width:52px;height:52px;border-radius:16px;margin:0 auto 13px;display:grid;place-items:center;background:rgba(255,255,255,.16);font-size:1.45rem}
.ev-al-header h1{font-size:clamp(1.45rem,4vw,2rem);margin:0 0 8px;font-weight:800}
.ev-al-header p{margin:0 auto;max-width:690px;color:rgba(255,255,255,.87);font-size:.91rem;line-height:1.65}
.ev-al-body-content{padding:28px 32px 30px}
.ev-al-notice{display:flex;gap:11px;align-items:flex-start;padding:14px 15px;border-radius:14px;background:#FFF7ED;border:1px solid #FED7AA;color:#9A3412;font-size:.84rem;line-height:1.55;margin-bottom:19px}
.ev-al-notice i{font-size:1.1rem;margin-top:1px}
.ev-al-docs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:20px}
.ev-al-doc{border:1px solid var(--ev-al-borde);border-radius:17px;padding:17px;background:#fff;display:flex;flex-direction:column;min-height:170px}
.ev-al-doc__top{display:flex;gap:11px;align-items:flex-start;margin-bottom:10px}
.ev-al-doc__icon{width:39px;height:39px;border-radius:12px;display:grid;place-items:center;background:var(--ev-al-verde-050);color:var(--ev-al-verde-900);font-size:1.05rem;flex:0 0 auto}
.ev-al-doc h2{font-size:.93rem;margin:0 0 4px;font-weight:800;color:var(--ev-al-verde-900);line-height:1.4}
.ev-al-version{font-size:.7rem;color:var(--ev-al-muted)}
.ev-al-doc p{font-size:.78rem;color:var(--ev-al-muted);line-height:1.55;margin:0 0 13px}
.ev-al-doc a{margin-top:auto;display:inline-flex;align-items:center;gap:6px;font-size:.8rem;color:var(--ev-al-verde-700);font-weight:750;text-decoration:none}
.ev-al-doc a:hover{text-decoration:underline}
.ev-al-consents{border-top:1px solid #EEF0F2;padding-top:18px;display:grid;gap:13px}
.ev-al-check{position:relative;padding:15px 15px 15px 46px;border:1px solid var(--ev-al-borde);border-radius:15px;background:#FAFAFA;transition:.18s ease}
.ev-al-check:has(input:checked){background:var(--ev-al-verde-050);border-color:#86EFAC}
.ev-al-check input{position:absolute;left:16px;top:17px;width:18px;height:18px;accent-color:var(--ev-al-verde-700);cursor:pointer}
.ev-al-check label{display:block;font-size:.84rem;line-height:1.55;cursor:pointer;color:#374151}
.ev-al-check a{color:var(--ev-al-verde-700);font-weight:750;text-decoration:underline;text-underline-offset:2px}
.ev-al-check.is-invalid{border-color:#DC2626;background:#FEF2F2}
.ev-al-help{font-size:.76rem;color:var(--ev-al-muted);margin:13px 0 20px;display:flex;gap:7px;align-items:flex-start}
.ev-al-actions{display:flex;gap:10px;justify-content:space-between;align-items:center;flex-wrap:wrap}
.ev-al-btn{min-height:44px;border-radius:13px;padding:10px 17px;font-weight:750;font-size:.84rem;display:inline-flex;align-items:center;justify-content:center;gap:7px;text-decoration:none;border:1px solid transparent;cursor:pointer;transition:.18s ease}
.ev-al-btn--ghost{background:#fff;border-color:var(--ev-al-borde);color:#4B5563}
.ev-al-btn--ghost:hover{background:#F9FAFB;color:#111827}
.ev-al-btn--primary{background:var(--ev-al-verde-900);color:#fff;min-width:220px;box-shadow:0 8px 18px rgba(15,89,47,.16)}
.ev-al-btn--primary:hover:not(:disabled){background:var(--ev-al-verde-700);transform:translateY(-1px)}
.ev-al-btn--primary:disabled{opacity:.5;cursor:not-allowed;box-shadow:none}
.ev-al-footer{text-align:center;color:var(--ev-al-muted);font-size:.73rem;margin-top:15px}
.ev-al-spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:evAlSpin .7s linear infinite}
@keyframes evAlSpin{to{transform:rotate(360deg)}}
@media(max-width:700px){
  .ev-al-page{padding:14px 10px;align-items:flex-start}
  .ev-al-brand{margin:8px 0 12px}.ev-al-brand img{height:49px}
  .ev-al-card{border-radius:18px}
  .ev-al-header{padding:25px 18px 21px}
  .ev-al-body-content{padding:20px 15px 22px}
  .ev-al-docs{grid-template-columns:1fr;gap:10px}
  .ev-al-doc{min-height:auto}
  .ev-al-actions{flex-direction:column-reverse;align-items:stretch}
  .ev-al-btn{width:100%}
  .ev-al-btn--primary{min-width:0}
}
.ev-al-privacy-summary{margin:0 0 20px;border:1px solid #BFDBFE;border-radius:15px;background:#F8FBFF;overflow:hidden}
.ev-al-privacy-summary summary{list-style:none;cursor:pointer;padding:13px 15px;display:flex;justify-content:space-between;align-items:center;gap:10px;color:#1E3A5F;font-size:.82rem;font-weight:800}
.ev-al-privacy-summary summary::-webkit-details-marker{display:none}
.ev-al-privacy-summary summary span{display:flex;align-items:center;gap:8px}
.ev-al-privacy-summary summary>i{transition:transform .18s ease}
.ev-al-privacy-summary[open] summary>i{transform:rotate(180deg)}
.ev-al-privacy-summary>div{padding:13px 15px;border-top:1px solid #DBEAFE;color:#475569;font-size:.78rem;line-height:1.55}
.ev-al-privacy-summary p{margin:0 0 7px}
.ev-al-privacy-summary p:last-child{margin-bottom:0}
.ev-al-privacy-summary a{color:var(--ev-al-verde-700);font-weight:750;overflow-wrap:anywhere}

</style>
