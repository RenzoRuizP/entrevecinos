<style>
:root{
  --ev-verde:#16A34A;
  --ev-verde-oscuro:#0F592F;
  --ev-naranja:#EA7C12;
  --ev-gris:#6B7280;
}

.ev-co-page{
  background: linear-gradient(180deg, #F0FDF4, #FFFFFF);
  min-height: 100vh;
}

.ev-card{
  border-radius: 20px;
  background:#fff;
  border:1px solid rgba(15,89,47,.15);
  box-shadow:0 18px 45px rgba(0,0,0,.10);
}

.ev-ico{
  width:52px;height:52px;
  border-radius:14px;
  background:rgba(234,124,18,.15);
  display:grid;place-items:center;
  color:#92400E;
  font-size:1.4rem;
}

.ev-title{
  color:var(--ev-verde-oscuro);
  font-weight:900;
}

.ev-subtitle{
  color:var(--ev-gris);
}

.ev-alert{
  border-radius:14px;
  padding:14px 16px;
  background:rgba(234,124,18,.12);
  border:1px solid rgba(234,124,18,.25);
  color:#92400E;
}

.ev-btn-orange{
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  color:#fff;border:none;
  border-radius:14px;
  padding:10px 18px;
  font-weight:800;
}

.ev-btn-orange:hover{ filter:brightness(.95); }

.ev-success{
  display:flex;gap:12px;
  align-items:flex-start;
  padding:16px;
  border-radius:14px;
  background:rgba(22,163,74,.12);
  border:1px solid rgba(22,163,74,.25);
  color:#14532D;
}

.ev-success i{ font-size:1.4rem; }
</style>
