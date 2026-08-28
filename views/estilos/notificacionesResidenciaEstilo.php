<?php /* views/estilos/notificacionesResidenciaEstilo.php */ ?>
<style>
:root{
  --ev-verde-oscuro: #0F592F;
  --ev-verde: #16A34A;
  --ev-naranja: #EA7C12;
  --ev-gris-borde: #E5E7EB;
  --ev-gris-fondo: #F3F4F6;
  --ev-texto: #111827;
  --ev-muted: #6B7280;
}

/* Layout */
.ev-notif-wrap{ padding:18px; }
.ev-notif-card{
  border:0; border-radius:18px;
  box-shadow:0 10px 25px rgba(0,0,0,.06);
  overflow:hidden;
}
.ev-notif-header{
  display:flex; gap:14px; align-items:flex-start; justify-content:space-between;
  padding:18px 18px 10px 18px;
}
.ev-notif-titlebox{ display:flex; gap:12px; align-items:center; }
.ev-notif-ico{
  width:46px;height:46px;border-radius:14px;
  background:linear-gradient(135deg,#EA7C12,#F59E0B);
  display:flex;align-items:center;justify-content:center;color:#fff;
  box-shadow:0 8px 18px rgba(234,124,18,.24);
}
.ev-notif-title{ font-weight:900; font-size:1.15rem; color:var(--ev-verde-oscuro); margin:0; }
.ev-notif-sub{ margin:2px 0 0 0; color:var(--ev-muted); font-size:.95rem; }

.ev-notif-toolbar{
  display:flex; gap:10px; flex-wrap:wrap; align-items:center;
  padding:0 18px 12px 18px;
}

.ev-pill{
  border:1px solid var(--ev-gris-borde); border-radius:999px; padding:8px 12px;
  background:#fff; display:flex; gap:8px; align-items:center;
}
.ev-pill label{ font-size:.85rem; color:var(--ev-muted); margin:0; }
.ev-pill select{ border:0; outline:none; font-weight:900; color:var(--ev-texto); background:transparent; }

.ev-notif-list{ padding:0 18px 8px 18px; }

/* Item */
.ev-item{
  border:1px solid var(--ev-gris-borde); border-radius:16px; padding:14px;
  display:flex; gap:12px; align-items:flex-start; justify-content:space-between;
  background:#fff; margin-bottom:10px;
  transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}
.ev-item:hover{
  transform:translateY(-1px);
  border-color:#D1D5DB;
  box-shadow:0 10px 22px rgba(0,0,0,.06);
}
.ev-item-left{ display:flex; gap:12px; align-items:flex-start; min-width:0; }
.ev-dot{
  width:12px;height:12px;border-radius:99px;margin-top:6px; flex:0 0 auto;
  background:var(--ev-naranja);
  box-shadow:0 0 0 6px rgba(234,124,18,.12);
}
.ev-dot.read{ background:#9CA3AF; box-shadow:none; }
.ev-item-title{ font-weight:950; color:var(--ev-texto); margin:0; }
.ev-item-msg{
  color:var(--ev-muted); margin:4px 0 0 0;
  max-width:860px; word-break:break-word;
}
.ev-item-meta{ color:#9CA3AF; font-size:.85rem; margin-top:6px; }
.ev-item-actions{ display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }

/* Buttons */
.ev-btn{ border-radius:12px; font-weight:900; padding:8px 12px; }
.ev-btn-light{ background:var(--ev-gris-fondo); border:1px solid var(--ev-gris-borde); color:var(--ev-texto); }
.ev-btn-light:hover{ filter:brightness(.98); }

/* Guardar (armonía con botones premium) */
.ev-btn-guardar{
  background:var(--ev-naranja);
  border:1px solid var(--ev-naranja);
  color:#fff;
  box-shadow:0 8px 18px rgba(234,124,18,.20);
  transition:transform .12s ease, box-shadow .12s ease, filter .12s ease;
}
.ev-btn-guardar:hover{
  transform:translateY(-1px);
  filter:brightness(.95);
  box-shadow:0 12px 26px rgba(234,124,18,.26);
}
.ev-btn-guardar:active{
  transform:translateY(0px);
  filter:brightness(.92);
}

/* Badges */
.ev-badge-state{
  display:inline-flex; align-items:center; gap:6px; padding:6px 10px;
  border-radius:999px; font-weight:900; font-size:.82rem;
}
.ev-badge-obs{ background:#FFF7ED; color:#9A3412; border:1px solid #FED7AA; }
.ev-badge-rej{ background:#FEF2F2; color:#991B1B; border:1px solid #FECACA; }

/* Footer */
.ev-notif-footer{
  display:flex; justify-content:space-between; align-items:center; gap:10px;
  padding:10px 18px 16px 18px;
  color:var(--ev-muted);
}
.ev-pager{ display:flex; gap:10px; align-items:center; }
.ev-pager button{ border-radius:12px; }

/* Empty */
.ev-empty{
  border:1px dashed var(--ev-gris-borde);
  border-radius:16px;
  padding:18px;
  background:#fff;
  color:var(--ev-muted);
}
.ev-empty .t{ font-weight:950; color:var(--ev-texto); }
.ev-empty .s{ margin-top:2px; }

/* Modal */
.ev-modal-content{ border-radius:18px; overflow:hidden; border:0; }
.ev-modal-header{
  background:linear-gradient(135deg,var(--ev-verde-oscuro),var(--ev-verde));
  color:#fff;
  border-bottom:0;
}
.ev-modal-title,
.ev-modal-title i{ color:#fff !important; font-weight:950; } /* FIX: texto + ícono blanco */

.ev-reenvio-box{
  background:#F9FAFB;
  border:1px solid var(--ev-gris-borde);
  border-radius:14px;
  padding:16px;
}

/* ✅ Acciones del modal alineadas a la derecha */
.ev-modal-actions{
  display:flex;
  gap:10px;
  justify-content:flex-end;
  align-items:center;
}

@media (max-width: 576px){
  .ev-item{ flex-direction:column; }
  .ev-item-actions{ justify-content:flex-start; }
  .ev-modal-actions{ justify-content:stretch; }
  .ev-modal-actions .btn{ width:100%; }
}
</style>
