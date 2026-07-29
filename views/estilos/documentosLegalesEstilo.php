<?php /* views/estilos/documentosLegalesEstilo.php */ ?>
<style>
:root{
  --ev-legal-verde-900:#0F592F;
  --ev-legal-verde-700:#0E7A43;
  --ev-legal-verde-600:#16A34A;
  --ev-legal-verde-050:#F0FDF4;
  --ev-legal-naranja:#EA7C12;
  --ev-legal-naranja-oscuro:#C46B05;
  --ev-legal-fondo:#F3F4F6;
  --ev-legal-card:#FFFFFF;
  --ev-legal-borde:#E5E7EB;
  --ev-legal-texto:#1F2937;
  --ev-legal-muted:#6B7280;
}

*{box-sizing:border-box}
html{scroll-behavior:smooth}
body.ev-legal-body{
  margin:0;
  min-height:100vh;
  background:var(--ev-legal-fondo);
  color:var(--ev-legal-texto);
  font-family:"Poppins",system-ui,-apple-system,"Segoe UI",sans-serif;
}

.ev-legal-topbar{
  position:sticky;
  top:0;
  z-index:50;
  background:#fff;
  border-bottom:1px solid var(--ev-legal-borde);
  box-shadow:0 4px 18px rgba(15,89,47,.06);
}
.ev-legal-topbar__inner{
  width:min(1180px,calc(100% - 32px));
  min-height:68px;
  margin:0 auto;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
}
.ev-legal-brand{
  display:inline-flex;
  align-items:center;
  gap:10px;
  text-decoration:none;
  color:var(--ev-legal-verde-900);
  font-weight:800;
}
.ev-legal-brand img{height:42px;width:auto;display:block}
.ev-legal-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.ev-legal-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  min-height:40px;
  padding:9px 14px;
  border-radius:12px;
  border:1px solid var(--ev-legal-borde);
  background:#fff;
  color:var(--ev-legal-verde-900);
  font-weight:700;
  font-size:.86rem;
  text-decoration:none;
  cursor:pointer;
  transition:.18s ease;
}
.ev-legal-btn:hover{border-color:#B7D7C4;background:var(--ev-legal-verde-050);transform:translateY(-1px)}
.ev-legal-btn--primary{background:var(--ev-legal-verde-900);border-color:var(--ev-legal-verde-900);color:#fff}
.ev-legal-btn--primary:hover{background:var(--ev-legal-verde-700);color:#fff}

.ev-legal-hero{
  background:linear-gradient(135deg,var(--ev-legal-verde-900),var(--ev-legal-verde-700));
  color:#fff;
  padding:44px 0 54px;
}
.ev-legal-hero__inner{width:min(980px,calc(100% - 32px));margin:0 auto}
.ev-legal-kicker{
  display:inline-flex;
  align-items:center;
  gap:7px;
  padding:6px 11px;
  border-radius:999px;
  background:rgba(255,255,255,.13);
  border:1px solid rgba(255,255,255,.2);
  font-size:.78rem;
  font-weight:700;
  margin-bottom:14px;
}
.ev-legal-hero h1{font-size:clamp(1.75rem,4vw,2.7rem);line-height:1.14;margin:0 0 12px;font-weight:800}
.ev-legal-hero p{max-width:780px;margin:0;color:rgba(255,255,255,.88);line-height:1.7}
.ev-legal-meta{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.ev-legal-meta span{font-size:.78rem;background:rgba(0,0,0,.13);padding:7px 10px;border-radius:10px}

.ev-legal-draft{
  width:min(980px,calc(100% - 32px));
  margin:-24px auto 24px;
  position:relative;
  z-index:2;
  background:#FFF7ED;
  border:1px solid #FED7AA;
  color:#9A3412;
  border-radius:16px;
  padding:15px 17px;
  display:flex;
  gap:12px;
  align-items:flex-start;
  box-shadow:0 10px 28px rgba(154,52,18,.08);
}
.ev-legal-draft i{font-size:1.15rem;margin-top:1px}
.ev-legal-draft strong{display:block;margin-bottom:2px}
.ev-legal-draft p{margin:0;font-size:.86rem;line-height:1.55}

.ev-legal-layout{
  width:min(1180px,calc(100% - 32px));
  margin:0 auto;
  padding:8px 0 44px;
  display:grid;
  grid-template-columns:260px minmax(0,1fr);
  gap:24px;
  align-items:start;
}
.ev-legal-toc{
  position:sticky;
  top:88px;
  background:#fff;
  border:1px solid var(--ev-legal-borde);
  border-radius:18px;
  padding:16px;
  box-shadow:0 10px 28px rgba(15,89,47,.05);
  max-height:calc(100vh - 108px);
  overflow:auto;
}
.ev-legal-toc h2{font-size:.88rem;margin:0 0 10px;color:var(--ev-legal-verde-900);font-weight:800}
.ev-legal-toc a{display:block;padding:7px 9px;border-radius:9px;color:#4B5563;text-decoration:none;font-size:.77rem;line-height:1.35}
.ev-legal-toc a:hover{background:var(--ev-legal-verde-050);color:var(--ev-legal-verde-900)}

.ev-legal-card{
  background:var(--ev-legal-card);
  border:1px solid var(--ev-legal-borde);
  border-radius:22px;
  padding:clamp(22px,4vw,42px);
  box-shadow:0 12px 34px rgba(15,89,47,.06);
}
.ev-legal-card section{scroll-margin-top:92px;padding:0 0 22px;margin:0 0 24px;border-bottom:1px solid #EEF0F2}
.ev-legal-card section:last-of-type{border-bottom:0;margin-bottom:0}
.ev-legal-card h2{font-size:1.18rem;color:var(--ev-legal-verde-900);font-weight:800;margin:0 0 12px;line-height:1.4}
.ev-legal-card h3{font-size:.98rem;color:#374151;font-weight:750;margin:18px 0 8px}
.ev-legal-card p,.ev-legal-card li{font-size:.92rem;line-height:1.78;color:#374151}
.ev-legal-card p{margin:0 0 12px}
.ev-legal-card ul,.ev-legal-card ol{padding-left:22px;margin:8px 0 12px}
.ev-legal-card li{margin-bottom:7px}
.ev-legal-card strong{color:#1F2937}
.ev-legal-intro{padding:17px 18px;border-radius:16px;background:var(--ev-legal-verde-050);border:1px solid #BBF7D0;margin-bottom:28px}
.ev-legal-intro p{margin:0;color:#14532D;font-weight:500}
.ev-legal-data-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:14px 0 16px}
.ev-legal-data-grid>div{padding:13px 14px;border:1px solid var(--ev-legal-borde);border-radius:13px;background:#FAFAFA;min-width:0}
.ev-legal-data-grid span{display:block;font-size:.72rem;color:var(--ev-legal-muted);font-weight:650;margin-bottom:3px}
.ev-legal-data-grid strong{display:block;font-size:.83rem;overflow-wrap:anywhere}
.ev-legal-final-note{margin-top:30px;padding:16px 18px;border-radius:15px;background:#F9FAFB;border:1px solid var(--ev-legal-borde);font-size:.84rem;color:#4B5563}

.ev-legal-footer{padding:28px 16px 38px;text-align:center;color:var(--ev-legal-muted);font-size:.8rem}
.ev-legal-footer a{color:var(--ev-legal-verde-700);font-weight:700;text-decoration:none}
.ev-legal-footer a:hover{text-decoration:underline}

@media (max-width:900px){
  .ev-legal-layout{grid-template-columns:1fr;width:min(820px,calc(100% - 24px))}
  .ev-legal-toc{position:static;max-height:none}
  .ev-legal-toc__links{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:2px}
}
@media (max-width:640px){
  .ev-legal-topbar__inner{width:calc(100% - 20px);min-height:62px}
  .ev-legal-brand span{display:none}
  .ev-legal-brand img{height:36px}
  .ev-legal-actions .ev-legal-btn span{display:none}
  .ev-legal-btn{width:40px;padding:8px}
  .ev-legal-hero{padding:34px 0 45px}
  .ev-legal-hero__inner,.ev-legal-draft{width:calc(100% - 24px)}
  .ev-legal-layout{width:calc(100% - 20px);gap:14px;padding-bottom:24px}
  .ev-legal-card{border-radius:17px;padding:21px 17px}
  .ev-legal-card h2{font-size:1.05rem}
  .ev-legal-card p,.ev-legal-card li{font-size:.87rem;line-height:1.7}
  .ev-legal-data-grid{grid-template-columns:1fr}
  .ev-legal-toc__links{grid-template-columns:1fr}
  .ev-legal-draft{padding:13px 14px}
}

@media print{
  .ev-legal-topbar,.ev-legal-toc,.ev-legal-draft,.ev-legal-footer{display:none!important}
  body.ev-legal-body{background:#fff}
  .ev-legal-hero{background:#fff!important;color:#111;padding:0 0 20px}
  .ev-legal-hero p,.ev-legal-meta span{color:#333;background:none;padding:0}
  .ev-legal-layout{display:block;width:100%;padding:0}
  .ev-legal-card{border:0;box-shadow:none;padding:0}
  .ev-legal-card section{break-inside:avoid}
}

/* Componentes de lectura clara EV */
.ev-legal-summary{
  margin:0 0 30px;
  padding:20px 22px;
  border:1px solid #BBF7D0;
  border-radius:18px;
  background:linear-gradient(135deg,#F0FDF4 0%,#FFFFFF 100%);
  box-shadow:0 8px 22px rgba(15,89,47,.05);
}
.ev-legal-summary h2{
  display:flex;
  align-items:center;
  gap:9px;
  margin:0 0 11px!important;
  color:var(--ev-legal-verde-900)!important;
  font-size:1.05rem!important;
}
.ev-legal-summary h2 i{color:var(--ev-legal-naranja)}
.ev-legal-summary ul{margin:0!important;padding-left:21px!important}
.ev-legal-summary li{margin-bottom:6px!important;color:#28513A!important}
.ev-legal-summary li:last-child{margin-bottom:0!important}

.ev-legal-callout{
  margin:14px 0 17px;
  padding:15px 17px;
  border-radius:15px;
  border:1px solid #D1D5DB;
  background:#F9FAFB;
}
.ev-legal-callout strong{display:block;margin-bottom:4px;color:#1F2937}
.ev-legal-callout p{margin:0!important;font-size:.87rem!important}
.ev-legal-callout--success{background:#F0FDF4;border-color:#86EFAC}
.ev-legal-callout--success strong{color:#166534}
.ev-legal-callout--warning{background:#FFF7ED;border-color:#FDBA74}
.ev-legal-callout--warning strong{color:#9A3412}
.ev-legal-callout--warning p{color:#7C2D12!important}
.ev-legal-callout--info{background:#EFF6FF;border-color:#BFDBFE}
.ev-legal-callout--info strong{color:#1D4ED8}
.ev-legal-callout--info p{color:#1E3A5F!important}

.ev-legal-table-wrap{
  width:100%;
  overflow-x:auto;
  margin:14px 0 18px;
  border:1px solid var(--ev-legal-borde);
  border-radius:15px;
}
.ev-legal-table{
  width:100%;
  min-width:680px;
  border-collapse:collapse;
  background:#fff;
}
.ev-legal-table th,
.ev-legal-table td{
  padding:13px 15px;
  border-bottom:1px solid #E5E7EB;
  text-align:left;
  vertical-align:top;
  font-size:.82rem;
  line-height:1.58;
}
.ev-legal-table th{background:#F0FDF4;color:#14532D;font-weight:800}
.ev-legal-table td:first-child{width:31%;font-weight:700;color:#334155}
.ev-legal-table tr:last-child td{border-bottom:0}

.ev-legal-contact-card{
  display:grid;
  gap:7px;
  margin-top:14px;
  padding:17px 18px;
  border:1px solid #D1FAE5;
  border-left:4px solid var(--ev-legal-verde-600);
  border-radius:15px;
  background:#F8FFFA;
}
.ev-legal-contact-card p{margin:0!important;overflow-wrap:anywhere}

.ev-legal-card a{color:var(--ev-legal-verde-700);font-weight:700}
.ev-legal-card a:hover{color:var(--ev-legal-naranja-oscuro)}

@media(max-width:640px){
  .ev-legal-summary{padding:17px 16px;border-radius:15px}
  .ev-legal-callout{padding:13px 14px}
  .ev-legal-contact-card{padding:14px}
  .ev-legal-table th,.ev-legal-table td{padding:11px 12px}
}

</style>
