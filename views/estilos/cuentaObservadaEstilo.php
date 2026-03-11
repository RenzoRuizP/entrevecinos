<style>
:root{
  --ev-verde:#16A34A;
  --ev-verde-oscuro:#0F592F;
  --ev-verde-claro:#DCFCE7;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;
  --ev-naranja-claro:#FFF7ED;

  --ev-gris-050:#F8FAFC;
  --ev-gris-100:#F1F5F9;
  --ev-gris-200:#E5E7EB;
  --ev-gris-400:#94A3B8;
  --ev-gris-500:#6B7280;
  --ev-gris-700:#374151;
  --ev-texto:#111827;

  --ev-shadow-card:0 24px 60px rgba(15,23,42,.10);
  --ev-shadow-soft:0 14px 34px rgba(15,23,42,.07);

  --ev-radius-card:28px;
  --ev-radius-soft:18px;
}

html, body{
  margin:0;
  padding:0;
}

body.ev-co-page{
  min-height:100vh;
  color:var(--ev-texto);
  background:
    radial-gradient(circle at 12% 18%, rgba(22,163,74,.08), transparent 22%),
    radial-gradient(circle at 88% 20%, rgba(234,124,18,.08), transparent 20%),
    linear-gradient(180deg, #F4FBF6 0%, #EEF8F2 34%, #FFFFFF 100%);
}

.ev-shell{
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:28px 14px;
}

.ev-wrap{
  width:100%;
  max-width:920px;
}

.ev-brand{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  margin-bottom:18px;
  color:var(--ev-verde-oscuro);
  font-weight:900;
  letter-spacing:.01em;
}

.ev-brand-badge{
  width:42px;
  height:42px;
  border-radius:14px;
  display:grid;
  place-items:center;
  background:linear-gradient(180deg, rgba(220,252,231,.95) 0%, rgba(187,247,208,.92) 100%);
  border:1px solid rgba(22,163,74,.20);
  box-shadow:0 10px 22px rgba(15,23,42,.06);
}

.ev-card{
  position:relative;
  overflow:hidden;
  border-radius:var(--ev-radius-card);
  background:
    linear-gradient(180deg, rgba(255,255,255,.98) 0%, rgba(255,255,255,.96) 100%);
  border:1px solid rgba(15,89,47,.10);
  box-shadow:var(--ev-shadow-card);
}

.ev-card::before{
  content:"";
  position:absolute;
  inset:0 0 auto 0;
  height:5px;
  background:linear-gradient(90deg, var(--ev-verde-oscuro) 0%, var(--ev-verde) 55%, var(--ev-naranja) 100%);
}

.ev-card-body{
  position:relative;
  padding:34px 34px 30px;
}

.ev-head{
  display:flex;
  align-items:flex-start;
  gap:18px;
  margin-bottom:20px;
}

.ev-ico{
  width:68px;
  height:68px;
  flex:0 0 68px;
  border-radius:20px;
  display:grid;
  place-items:center;
  font-size:1.8rem;
  box-shadow:var(--ev-shadow-soft);
}

.ev-ico.is-review{
  background:linear-gradient(180deg, rgba(220,252,231,.98) 0%, rgba(187,247,208,.92) 100%);
  border:1px solid rgba(22,163,74,.20);
  color:var(--ev-verde-oscuro);
}

.ev-ico.is-warning{
  background:linear-gradient(180deg, rgba(255,247,237,.98) 0%, rgba(254,215,170,.92) 100%);
  border:1px solid rgba(234,124,18,.20);
  color:#9A3412;
}

.ev-title{
  margin:0 0 8px;
  color:var(--ev-verde-oscuro);
  font-weight:900;
  font-size:2.1rem;
  line-height:1.08;
  letter-spacing:-.02em;
}

.ev-subtitle{
  margin:0;
  color:var(--ev-gris-500);
  font-size:1rem;
  line-height:1.55;
}

.ev-status-band{
  display:grid;
  gap:12px;
  margin:22px 0 0;
}

.ev-info{
  display:flex;
  gap:12px;
  align-items:flex-start;
  padding:16px 18px;
  border-radius:var(--ev-radius-soft);
  background:linear-gradient(180deg, rgba(240,253,244,.98) 0%, rgba(220,252,231,.92) 100%);
  border:1px solid rgba(22,163,74,.18);
  color:#14532D;
}

.ev-info i{
  font-size:1.2rem;
  margin-top:1px;
}

.ev-info-title{
  font-weight:900;
  margin-bottom:2px;
}

.ev-info-text{
  color:#166534;
  line-height:1.5;
}

.ev-alert{
  border-radius:var(--ev-radius-soft);
  padding:18px 18px;
  background:linear-gradient(180deg, rgba(255,247,237,.98) 0%, rgba(255,237,213,.94) 100%);
  border:1px solid rgba(234,124,18,.22);
  box-shadow:var(--ev-shadow-soft);
}

.ev-alert-head{
  display:flex;
  align-items:center;
  gap:10px;
  margin-bottom:8px;
  color:#9A3412;
  font-weight:900;
}

.ev-alert-head i{
  font-size:1.1rem;
}

.ev-observacion-text{
  color:#7C2D12;
  line-height:1.6;
  font-weight:600;
}

.ev-form-wrap{
  margin-top:20px;
  padding:22px;
  border-radius:22px;
  background:linear-gradient(180deg, #FFFFFF 0%, #FBFDFB 100%);
  border:1px solid rgba(15,89,47,.10);
  box-shadow:var(--ev-shadow-soft);
}

.ev-section-title{
  margin:0 0 6px;
  color:var(--ev-verde-oscuro);
  font-weight:900;
  font-size:1.08rem;
}

.ev-section-subtitle{
  margin:0 0 18px;
  color:var(--ev-gris-500);
  line-height:1.55;
}

.ev-label{
  font-weight:800;
  color:var(--ev-gris-700);
  margin-bottom:8px;
}

.ev-file{
  border-radius:16px;
  border:1px solid rgba(15,89,47,.16);
  padding:12px 14px;
  box-shadow:none;
}

.ev-file:focus{
  border-color:var(--ev-verde);
  box-shadow:0 0 0 4px rgba(22,163,74,.14);
  outline:none;
}

.ev-help{
  color:var(--ev-gris-500);
  font-size:.92rem;
  line-height:1.5;
}

.ev-actions{
  display:flex;
  gap:12px;
  flex-wrap:wrap;
  margin-top:18px;
}

.ev-btn-orange{
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  color:#fff;
  border:none;
  border-radius:15px;
  padding:12px 18px;
  font-weight:900;
  box-shadow:0 14px 28px rgba(234,124,18,.28);
  transition:transform .18s ease, filter .18s ease, box-shadow .18s ease;
}

.ev-btn-orange:hover{
  color:#fff;
  filter:brightness(1.03);
  transform:translateY(-1px);
  box-shadow:0 18px 32px rgba(234,124,18,.34);
}

.ev-btn-light{
  background:#fff;
  color:var(--ev-gris-700);
  border:1px solid rgba(148,163,184,.28);
  border-radius:15px;
  padding:12px 18px;
  font-weight:800;
  transition:all .18s ease;
}

.ev-btn-light:hover{
  background:var(--ev-gris-050);
  color:#111827;
  transform:translateY(-1px);
}

.ev-success{
  display:flex;
  gap:12px;
  align-items:flex-start;
  padding:16px 18px;
  border-radius:16px;
  background:linear-gradient(180deg, rgba(240,253,244,.98) 0%, rgba(220,252,231,.92) 100%);
  border:1px solid rgba(22,163,74,.22);
  color:#14532D;
  box-shadow:var(--ev-shadow-soft);
}

.ev-success i{
  font-size:1.35rem;
}

.ev-success-title{
  font-weight:900;
  margin-bottom:2px;
}

.ev-success-text{
  line-height:1.5;
}

.ev-footnote{
  margin-top:18px;
  color:var(--ev-gris-500);
  font-size:.92rem;
  line-height:1.55;
}

@media (max-width: 991.98px){
  .ev-card-body{
    padding:28px 24px 24px;
  }

  .ev-title{
    font-size:1.8rem;
  }
}

@media (max-width: 575.98px){
  .ev-shell{
    padding:16px 10px;
  }

  .ev-brand{
    margin-bottom:14px;
    font-size:.98rem;
  }

  .ev-card-body{
    padding:22px 16px 18px;
  }

  .ev-head{
    gap:14px;
    align-items:flex-start;
  }

  .ev-ico{
    width:56px;
    height:56px;
    flex-basis:56px;
    border-radius:18px;
    font-size:1.45rem;
  }

  .ev-title{
    font-size:1.48rem;
    line-height:1.12;
  }

  .ev-subtitle{
    font-size:.96rem;
  }

  .ev-form-wrap{
    padding:16px;
    border-radius:18px;
  }

  .ev-actions{
    flex-direction:column;
  }

  .ev-actions .btn{
    width:100%;
  }
}
</style>