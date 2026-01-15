<?php /* views/estilos/notificacionesResidenciaEstilo.php */ ?>
<style>
/* Notificaciones Residencia — EV (premium, minimal) */
.ev-notif-wrap{ padding:18px; }
.ev-notif-card{ border:0; border-radius:18px; box-shadow:0 10px 25px rgba(0,0,0,.06); }

.ev-notif-header{
  display:flex; gap:14px; align-items:flex-start; justify-content:space-between;
  padding:18px 18px 10px 18px;
}
.ev-notif-titlebox{ display:flex; gap:12px; align-items:center; }
.ev-notif-ico{
  width:46px;height:46px;border-radius:14px;
  background:linear-gradient(135deg,#0F592F,#16A34A);
  display:flex;align-items:center;justify-content:center;color:#fff;
  box-shadow:0 8px 18px rgba(15,89,47,.25);
}
.ev-notif-title{ font-weight:800; font-size:1.15rem; color:#0F592F; margin:0; }
.ev-notif-sub{ margin:2px 0 0 0; color:#6B7280; font-size:.95rem; }

.ev-notif-toolbar{
  display:flex; gap:10px; flex-wrap:wrap; align-items:center;
  padding:0 18px 12px 18px;
}
.ev-pill{
  border:1px solid #E5E7EB; border-radius:999px; padding:8px 12px;
  background:#fff; display:flex; gap:8px; align-items:center;
}
.ev-pill label{ font-size:.85rem; color:#6B7280; margin:0; }
.ev-pill select{ border:0; outline:none; font-weight:700; color:#111827; background:transparent; }

.ev-notif-list{ padding:0 18px 8px 18px; }
.ev-item{
  border:1px solid #E5E7EB; border-radius:16px; padding:14px;
  display:flex; gap:12px; align-items:flex-start; justify-content:space-between;
  background:#fff; margin-bottom:10px;
  transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}
.ev-item:hover{ transform:translateY(-1px); border-color:#D1D5DB; box-shadow:0 10px 22px rgba(0,0,0,.06); }

.ev-item-left{ display:flex; gap:12px; align-items:flex-start; }
.ev-dot{
  width:12px;height:12px;border-radius:99px;margin-top:6px; flex:0 0 auto;
  background:#EA7C12;
  box-shadow:0 0 0 6px rgba(234,124,18,.12);
}
.ev-dot.read{ background:#9CA3AF; box-shadow:none; }
.ev-item-title{ font-weight:800; color:#111827; margin:0; }
.ev-item-msg{ color:#6B7280; margin:4px 0 0 0; max-width:820px; }
.ev-item-meta{ color:#9CA3AF; font-size:.85rem; margin-top:6px; }

.ev-item-actions{ display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }

.ev-btn{
  border-radius:12px; font-weight:800; padding:8px 12px;
  transition:transform .12s ease, box-shadow .12s ease, filter .12s ease, background-color .12s ease, border-color .12s ease;
}
.ev-btn:disabled{ opacity:.65; cursor:not-allowed; transform:none !important; box-shadow:none !important; }

.ev-btn-light{ background:#F3F4F6; border:1px solid #E5E7EB; color:#111827; }
.ev-btn-light:hover{ filter:brightness(.98); transform:translateY(-1px); box-shadow:0 10px 18px rgba(0,0,0,.06); }

.ev-btn-orange{ background:#EA7C12; border:1px solid #EA7C12; color:#fff; }
.ev-btn-orange:hover{
  filter:brightness(.93);
  transform:translateY(-1px);
  box-shadow:0 12px 22px rgba(234,124,18,.25);
}
.ev-btn-orange:active{ transform:translateY(0px); filter:brightness(.90); }

.ev-badge-state{
  display:inline-flex; align-items:center; gap:6px; padding:6px 10px;
  border-radius:999px; font-weight:800; font-size:.82rem;
}
.ev-badge-obs{ background:#FFF7ED; color:#9A3412; border:1px solid #FED7AA; }
.ev-badge-rej{ background:#FEF2F2; color:#991B1B; border:1px solid #FECACA; }
.ev-badge-read{ background:#F3F4F6; color:#374151; border:1px solid #E5E7EB; }

.ev-notif-footer{
  display:flex; justify-content:space-between; align-items:center; gap:10px;
  padding:10px 18px 16px 18px;
  color:#6B7280;
}
.ev-pager{ display:flex; gap:10px; align-items:center; }
.ev-pager button{ border-radius:12px; }

/* ---------- Modal (FIX #1) ---------- */
.ev-notif-modal-content{
  border-radius:18px;
  overflow:hidden;
}
.ev-notif-modal-header{
  background:linear-gradient(135deg,#0F592F,#16A34A);
  color:#fff;
}
/* Texto e ícono blancos sí o sí */
.ev-notif-modal-title,
.ev-notif-modal-title i{
  color:#fff !important;
  font-weight:800;
}
.ev-notif-reenvio-box{
  background:#F9FAFB;
  border:1px solid #E5E7EB;
  border-radius:14px;
  padding:16px;
}

@media (max-width: 576px){
  .ev-item{ flex-direction:column; }
  .ev-item-actions{ justify-content:flex-start; }
}
</style>
