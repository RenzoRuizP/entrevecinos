<?php /* views/estilos/DatosPersonalesEstilo.php — UX/UI Mi Perfil + foto de perfil EV */ ?>
<style>
/* ===================================================
   TOKENS EV
=================================================== */
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#16A34A;
  --ev-verde-claro:#bbf7d0;

  --ev-naranja:#EA7C12;
  --ev-naranja-oscuro:#C46B05;

  --ev-gris-050:#F9FAFB;
  --ev-gris-100:#F3F4F6;
  --ev-gris-200:#E5E7EB;
  --ev-gris-500:#6B7280;
  --ev-gris-700:#374151;

  --ev-shadow-soft: 0 14px 30px rgba(15,23,42,0.06), 0 2px 8px rgba(15,23,42,0.04);
  --ev-shadow-cta: 0 12px 26px rgba(234,124,18,0.35);
}

/* ===================================================
   WRAPPER
=================================================== */
.container-datos-personales{
  padding: 18px 10px 26px;
}

.ev-datos-card{
  border-radius: 18px !important;
  overflow: hidden;
  background: #fff;
  box-shadow: var(--ev-shadow-soft);
}

/* ===================================================
   HEADER
=================================================== */
.ev-datos-card .card-header{
  background: #ffffff !important;
  border-bottom: 1px solid rgba(229,231,235,0.95) !important;
  padding: 18px 18px !important;
}

.ev-datos-icon{
  width: 40px;
  height: 40px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(22,163,74,0.10);
  border: 1px solid rgba(209,250,229,0.9);
  box-shadow: 0 8px 18px rgba(15,23,42,0.06);
  flex: 0 0 auto;
}

.ev-datos-icon i{
  color: var(--ev-verde-oscuro);
  font-size: 1.05rem;
  line-height: 1;
}

.ev-datos-card .card-header h5{
  margin: 0;
  font-weight: 800;
  font-size: 1.55rem;
  color: #0B1F13;
  letter-spacing: 0.01em;
}

.ev-datos-subtitle{
  display: block;
  margin-top: 2px;
  color: var(--ev-gris-500);
  font-size: 0.92rem;
  line-height: 1.3;
}

.ev-datos-card .card-body{
  background: linear-gradient(180deg, #FFFFFF 0%, #FAFBFC 100%);
  padding: 18px 18px 16px !important;
}

/* ===================================================
   FOTO DE PERFIL
=================================================== */
.ev-profile-photo-panel{
  display:flex;
  align-items:center;
  gap:18px;
  padding:16px;
  border-radius:18px;
  background:
    radial-gradient(circle at 12% 18%, rgba(22,163,74,.12), transparent 34%),
    linear-gradient(135deg,#F0FDF4,#FFFFFF);
  border:1px solid rgba(22,163,74,.16);
  box-shadow:0 12px 26px rgba(15,23,42,.045);
}

.ev-profile-photo-trigger{
  width:96px;
  height:96px;
  min-width:96px;
  position:relative;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border:0;
  border-radius:999px;
  padding:0;
  background:transparent;
  cursor:pointer;
}

.ev-profile-photo-trigger img{
  width:96px;
  height:96px;
  display:block;
  object-fit:cover;
  border-radius:999px;
  border:3px solid #fff;
  box-shadow:0 14px 28px rgba(15,23,42,.16);
  transition:filter .18s ease, transform .18s ease, box-shadow .18s ease;
}

.ev-profile-photo-trigger:hover img{
  filter:brightness(.88);
  transform:translateY(-1px);
  box-shadow:0 18px 34px rgba(15,23,42,.20);
}

.ev-profile-photo-trigger:focus-visible{
  outline:0;
  box-shadow:0 0 0 .22rem rgba(187,247,208,.72);
}

.ev-profile-photo-camera{
  position:absolute;
  right:2px;
  bottom:5px;
  width:30px;
  height:30px;
  display:grid;
  place-items:center;
  border-radius:999px;
  color:#fff;
  background:linear-gradient(135deg,var(--ev-naranja),#F59E0B);
  border:3px solid #fff;
  box-shadow:0 10px 20px rgba(234,124,18,.24);
  font-size:.86rem;
}

.ev-profile-photo-copy{
  min-width:0;
}

.ev-profile-photo-copy strong{
  display:block;
  color:var(--ev-verde-oscuro);
  font-size:1.08rem;
  font-weight:950;
  letter-spacing:-.02em;
}

.ev-profile-photo-copy p{
  margin:4px 0 3px;
  color:#374151;
  line-height:1.42;
  font-size:.93rem;
  font-weight:600;
}

.ev-profile-photo-copy small{
  color:var(--ev-gris-500);
  font-size:.82rem;
  font-weight:700;
}

/* ===================================================
   STEPPER
=================================================== */
.ev-stepper{
  background: rgba(15, 89, 47, 0.04);
  border: 1px solid rgba(229,231,235,0.95);
  border-radius: 16px;
  padding: 12px 12px;
  box-shadow: 0 10px 22px rgba(15,23,42,0.04);
  display: flex;
  align-items: center;
  gap: 12px;
}

.ev-step{
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 999px;
  background: #ffffff;
  border: 1px solid rgba(229,231,235,0.95);
  cursor: pointer;
  user-select: none;
  transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
  box-shadow: 0 10px 22px rgba(15,23,42,0.04);
  min-width: 160px;
  justify-content: center;
}

.ev-step:hover{
  transform: translateY(-1px);
  border-color: rgba(22,163,74,0.28);
  box-shadow: 0 14px 28px rgba(15,23,42,0.06);
}

.ev-step-dot{
  width: 26px;
  height: 26px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.86rem;
  background: #E5E7EB;
  color: #374151;
  border: 1px solid rgba(209,213,219,0.9);
}

.ev-step-label{
  font-weight: 700;
  color: #111827;
  font-size: 0.95rem;
}

.ev-step.active{
  border-color: rgba(22,163,74,0.45);
  background: rgba(22,163,74,0.08);
}

.ev-step.active .ev-step-dot{
  background: rgba(22,163,74,0.12);
  border-color: rgba(22,163,74,0.35);
  color: var(--ev-verde-oscuro);
}

.ev-step.done{
  border-color: rgba(22,163,74,0.28);
}

.ev-step.done .ev-step-dot{
  background: rgba(22,163,74,0.14);
  color: var(--ev-verde-oscuro);
  border-color: rgba(22,163,74,0.30);
}

.ev-step-line{
  flex: 1 1 auto;
  height: 2px;
  background: rgba(15,89,47,0.12);
  border-radius: 999px;
}

/* ===================================================
   PANEL
=================================================== */
.ev-step-panel{
  background: #ffffff;
  border: 1px solid rgba(229,231,235,0.95);
  border-radius: 16px;
  padding: 16px 16px;
  box-shadow: var(--ev-shadow-soft);
}

.ev-form-label{
  font-weight: 700;
  font-size: 0.92rem;
  color: var(--ev-gris-700);
  margin-bottom: 8px;
}

.ev-input-rounded{
  border-radius: 12px !important;
  border: 1px solid #D1FAE5 !important;
  height: 46px;
  padding-left: 14px;
  padding-right: 14px;
  font-size: 0.95rem;
  transition: all .18s ease-out;
  background: #fff;
}

.ev-input-rounded:focus{
  border-color: var(--ev-verde) !important;
  box-shadow: 0 0 0 4px rgba(22,163,74,0.18) !important;
  outline: none !important;
}

.ev-step-panel input[disabled],
.ev-step-panel select[disabled]{
  background: #F9FAFB !important;
  color: #6B7280 !important;
}

.ev-hint{
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid rgba(229,231,235,0.95);
  background: #F9FAFB;
  color: #111827;
}

.ev-hint i{
  color: var(--ev-naranja);
  margin-top: 2px;
}

.ev-file-row{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid rgba(229,231,235,0.95);
  background: #FFFFFF;
  box-shadow: 0 10px 22px rgba(15,23,42,0.04);
}

.ev-file-info{
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.ev-file-info i{
  color: var(--ev-verde-oscuro);
}

/* ===================================================
   FOOTER / BOTONES
=================================================== */
.ev-wizard-footer{
  padding: 14px 4px 2px;
  border-top: 1px solid rgba(229,231,235,0.95);
  margin-top: 14px;
}

.btn-ev-neutral{
  background: #ffffff !important;
  border: 1px solid rgba(209,213,219,0.95) !important;
  color: #111827 !important;
  border-radius: 999px !important;
  padding: 10px 18px !important;
  box-shadow: 0 10px 22px rgba(15,23,42,0.05);
}

.btn-ev-neutral:hover{
  background: #F3F4F6 !important;
}

.btn-ev-primary{
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B) !important;
  border: none !important;
  color: #ffffff !important;
  border-radius: 999px !important;
  padding: 10px 22px !important;
  box-shadow: var(--ev-shadow-cta) !important;
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}

.btn-ev-primary:hover{
  background: linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C) !important;
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(234,124,18,0.48) !important;
}

.btn-ev-primary:active{
  transform: translateY(0);
  box-shadow: 0 6px 16px rgba(234,124,18,0.30) !important;
}

.btn-ev-primary.saving{
  opacity: .92;
  pointer-events: none;
}



/* ===================================================
   INFORMACIÓN LEGAL Y LIBRO DE RECLAMACIONES
=================================================== */
.ev-profile-legal{
  border:1px solid rgba(15,89,47,.14);
  border-radius:20px;
  padding:20px;
  background:
    radial-gradient(circle at 92% 8%, rgba(22,163,74,.10), transparent 30%),
    linear-gradient(180deg,#FFFFFF 0%,#F8FCF9 100%);
  box-shadow:var(--ev-shadow-soft);
}

.ev-profile-legal__header{
  display:flex;
  align-items:flex-start;
  gap:14px;
  margin-bottom:16px;
}

.ev-profile-legal__icon{
  width:46px;
  height:46px;
  min-width:46px;
  display:grid;
  place-items:center;
  border-radius:15px;
  color:#fff;
  background:linear-gradient(135deg,var(--ev-verde-oscuro),var(--ev-verde));
  box-shadow:0 12px 24px rgba(15,89,47,.18);
  font-size:1.08rem;
}

.ev-profile-legal__eyebrow{
  display:block;
  margin-bottom:3px;
  color:var(--ev-naranja-oscuro);
  font-size:.72rem;
  font-weight:900;
  letter-spacing:.09em;
}

.ev-profile-legal__header h3{
  margin:0;
  color:var(--ev-verde-oscuro);
  font-size:1.2rem;
  font-weight:900;
  letter-spacing:-.02em;
}

.ev-profile-legal__header p{
  margin:4px 0 0;
  color:var(--ev-gris-500);
  font-size:.9rem;
  line-height:1.45;
}

.ev-profile-legal__grid{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:12px;
}

.ev-profile-legal__card{
  min-width:0;
  display:flex;
  align-items:center;
  gap:12px;
  padding:15px;
  border:1px solid rgba(15,89,47,.13);
  border-radius:16px;
  background:#fff;
  color:inherit;
  text-decoration:none !important;
  box-shadow:0 10px 24px rgba(15,23,42,.045);
  transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease,background .18s ease;
}

.ev-profile-legal__card:hover{
  transform:translateY(-2px);
  border-color:rgba(22,163,74,.38);
  background:#FBFFFC;
  box-shadow:0 16px 30px rgba(15,89,47,.10);
}

.ev-profile-legal__card:focus-visible{
  outline:0;
  box-shadow:0 0 0 4px rgba(22,163,74,.18),0 16px 30px rgba(15,89,47,.10);
}

.ev-profile-legal__card--book{
  border-color:rgba(234,124,18,.22);
}

.ev-profile-legal__card--book:hover{
  border-color:rgba(234,124,18,.48);
  background:#FFFCF7;
  box-shadow:0 16px 30px rgba(196,107,5,.11);
}

.ev-profile-legal__card-icon{
  width:42px;
  height:42px;
  min-width:42px;
  display:grid;
  place-items:center;
  border-radius:13px;
  font-size:1rem;
}

.ev-profile-legal__card-icon--green{
  color:var(--ev-verde-oscuro);
  background:#F0FDF4;
  border:1px solid #BBF7D0;
}

.ev-profile-legal__card-icon--orange{
  color:var(--ev-naranja-oscuro);
  background:#FFF7ED;
  border:1px solid #FED7AA;
}

.ev-profile-legal__card-copy{
  min-width:0;
  flex:1 1 auto;
}

.ev-profile-legal__card-copy strong{
  display:block;
  color:#163B26;
  font-size:.91rem;
  font-weight:900;
  line-height:1.25;
}

.ev-profile-legal__card-copy small{
  display:block;
  margin-top:4px;
  color:var(--ev-gris-500);
  font-size:.78rem;
  line-height:1.35;
}

.ev-profile-legal__open{
  color:#6B7280;
  font-size:.94rem;
  transition:color .18s ease,transform .18s ease;
}

.ev-profile-legal__card:hover .ev-profile-legal__open{
  color:var(--ev-verde-oscuro);
  transform:translate(1px,-1px);
}

.ev-profile-legal__card--book:hover .ev-profile-legal__open{
  color:var(--ev-naranja-oscuro);
}

.ev-profile-legal__note{
  display:flex;
  align-items:flex-start;
  gap:9px;
  margin-top:14px;
  padding:11px 13px;
  border-radius:13px;
  color:#365344;
  background:rgba(240,253,244,.82);
  border:1px solid rgba(187,247,208,.82);
  font-size:.8rem;
  line-height:1.42;
}

.ev-profile-legal__note i{
  color:var(--ev-verde-oscuro);
  margin-top:1px;
}


/* ===================================================
   RESPONSIVO
=================================================== */
@media (max-width: 992px){
  .ev-step{
    min-width: 140px;
  }
}

@media (max-width: 768px){
  .container-datos-personales{
    padding: 12px 8px 18px;
  }

  .ev-datos-card .card-header{
    padding: 16px 14px !important;
  }

  .ev-datos-card .card-body{
    padding: 14px 14px 12px !important;
  }

  .ev-profile-photo-panel{
    align-items:flex-start;
    gap:14px;
  }

  .ev-profile-photo-trigger,
  .ev-profile-photo-trigger img{
    width:82px;
    height:82px;
    min-width:82px;
  }

  .ev-profile-photo-camera{
    width:27px;
    height:27px;
    font-size:.78rem;
  }

  .ev-stepper{
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .ev-step-line{
    display: none;
  }

  .ev-step{
    width: 100%;
    justify-content: flex-start;
  }
}

@media (max-width: 576px){
  .ev-datos-card .card-header h5{
    font-size: 1.3rem;
  }

  .ev-step-panel{
    padding: 14px 12px;
  }

  .ev-profile-photo-panel{
    flex-direction:column;
    text-align:center;
    align-items:center;
  }

  .ev-profile-photo-copy p{
    margin-left:auto;
    margin-right:auto;
  }

  .btn-ev-neutral,
  .btn-ev-primary{
    width: auto;
  }
}

@media (max-width: 1100px){
  .ev-profile-legal__grid{
    grid-template-columns:1fr;
  }
}

@media (max-width: 576px){
  .ev-profile-legal{
    padding:15px 13px;
    border-radius:17px;
  }

  .ev-profile-legal__header{
    gap:11px;
  }

  .ev-profile-legal__icon{
    width:42px;
    height:42px;
    min-width:42px;
    border-radius:13px;
  }

  .ev-profile-legal__header h3{
    font-size:1.08rem;
  }

  .ev-profile-legal__card{
    padding:13px 12px;
  }

  .ev-profile-legal__card-icon{
    width:39px;
    height:39px;
    min-width:39px;
  }
}

</style>
