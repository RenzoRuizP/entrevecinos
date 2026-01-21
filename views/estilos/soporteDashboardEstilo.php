<?php /* views/estilos/soporteDashboardEstilo.php */ ?>
<style>
/* =========================================================
   EV - SOPORTE DASHBOARD (Soporte) - ESTILO FINAL PREMIUM
========================================================= */

:root {
  --ev-verde-oscuro: #0F592F;
  --ev-verde: #16A34A;
  --ev-verde-claro: #bbf7d0;
  --ev-naranja: #EA7C12;
  --ev-naranja-oscuro: #C46B05;
  --ev-gris-050: #F9FAFB;
  --ev-gris-100: #F3F4F6;
  --ev-gris-200: #E5E7EB;
  --ev-gris-500: #6B7280;
  --ev-gris-600: #4B5563;
  --ev-texto: #111827;
}

/* Página */
.ev-soporte-dashboard{ color: var(--ev-texto); }

/* Cards base */
.ev-card{
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid rgba(148,163,184,0.22);
  box-shadow: 0 14px 40px rgba(15, 23, 42, 0.10);
  overflow: hidden;
}

/* HERO */
.ev-soporte-hero{
  background:
    radial-gradient(circle at 85% 25%, rgba(22,163,74,0.10), transparent 55%),
    radial-gradient(circle at 10% 80%, rgba(234,124,18,0.10), transparent 55%),
    #ffffff;
}
.ev-hero-body{ padding: 18px 18px 14px; }
.ev-hero-top{
  display:flex; align-items:flex-start; justify-content:space-between;
  gap:16px; flex-wrap: wrap;
}
.ev-hero-right{ display:flex; align-items:center; gap:10px; }

/* Header texts */
.ev-recargas-title{
  font-size: 2.05rem;
  font-weight: 850;
  color: var(--ev-verde-oscuro);
  letter-spacing: 0.01em;
  margin: 0;
}
.ev-recargas-subtitle{ color: var(--ev-gris-500); font-size: 0.95rem; }

/* Badge rol */
.ev-role-badge{
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: 999px;
  font-weight: 850;
  font-size: 0.82rem;
  color: #92400E;
  background: rgba(234,124,18,0.12);
  border: 1px solid rgba(234,124,18,0.35);
  white-space: nowrap;
}

/* Botón naranja (principal) */
.ev-btn-orange{
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border: none;
  color: #ffffff;
  border-radius: 14px;
  padding: 10px 18px;
  box-shadow: 0 12px 26px rgba(234,124,18,0.35);
  transition: all 0.2s ease;
  font-weight: 750;
  text-decoration: none;
}
.ev-btn-orange:hover{
  background: linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C);
  color: #ffffff;
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(234,124,18,0.48);
}
.ev-btn-orange:active{
  transform: translateY(0);
  box-shadow: 0 6px 16px rgba(234,124,18,0.30);
}

/* Botón “Atender” (pequeño) — por si alguna vista lo usa */
.ev-btn-atender{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width: 108px;
  padding: 10px 16px;
  border-radius: 999px;
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  color:#fff;
  font-weight: 900;
  text-decoration:none;
  box-shadow: 0 10px 22px rgba(234,124,18,0.32);
  transition: all .18s ease;
}
.ev-btn-atender:hover{
  background: linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C);
  transform: translateY(-1px);
  box-shadow: 0 12px 26px rgba(234,124,18,0.46);
  color:#fff;
}
.ev-btn-atender:active{ transform: translateY(0); box-shadow: 0 6px 16px rgba(234,124,18,0.30); }

/* Icon button */
.ev-icon-btn{
  width: 42px; height: 42px;
  border-radius: 14px;
  border: 1px solid var(--ev-gris-200);
  background: #fff;
  display: grid; place-items: center;
  color: var(--ev-verde-oscuro);
  transition: all 0.18s ease;
  text-decoration:none;
}
.ev-icon-btn:hover{ background: #ECFDF5; border-color: rgba(22,163,74,0.35); }

/* KPI Icon */
.ev-kpi-ico{
  width: 38px; height: 38px;
  display:grid; place-items:center;
  border-radius: 14px;
  border: 1px solid rgba(148,163,184,0.22);
  background: rgba(22,163,74,0.08);
  color: var(--ev-verde-oscuro);
}

/* Titles */
.ev-card-title{
  margin: 0;
  font-size: 1.05rem;
  font-weight: 900;
  color: var(--ev-verde-oscuro);
}

/* Numeros */
.ev-num{ font-weight: 900; }
.ev-num-warn{ color: #EA7C12; }
.ev-num-ok{ color: #16A34A; }
.ev-num-bad{ color: #EF4444; }
.ev-num-muted{ color: var(--ev-gris-500); }
.ev-num-purple{ color: #7C3AED; }

/* Select */
.ev-select{
  border-radius: 12px;
  border: 1px solid var(--ev-gris-200);
}
.ev-select:focus{
  border-color: var(--ev-verde);
  box-shadow: 0 0 0 3px rgba(22,163,74,0.18);
}

/* Header "Atender ahora" */
.ev-atender-header{
  background: linear-gradient(90deg, rgba(234,124,18,0.08), rgba(22,163,74,0.06));
}

/* Table */
.ev-table{ width: 100%; }
.ev-table thead th{
  background: var(--ev-gris-050);
  color: #374151;
  font-weight: 900;
  border-bottom: 1px solid var(--ev-gris-200) !important;
  white-space: nowrap;
  vertical-align: middle;
}

/* Layout filas */
.ev-table tbody td{
  vertical-align: middle;
  border-color: var(--ev-gris-200);
  padding-top: 12px;
  padding-bottom: 12px;
}
.ev-table tbody tr:hover{ background: rgba(234,124,18,0.04); }

/* =========================================================
   ✅ FIX: CENTRADO REAL (porque tu vista usa text-end y ev-col-acciones)
   - Centramos encabezados y celdas en "Atender ahora"
   - Anulamos text-end SOLO dentro del dashboard
========================================================= */
.ev-soporte-dashboard .ev-table thead th,
.ev-soporte-dashboard .ev-table tbody td{
  text-align: center;
}

.ev-soporte-dashboard .ev-table .text-end{
  text-align: center !important;
}

/* Si quieres que la 2da columna sea más legible, puedes activar esto:
.ev-soporte-dashboard .ev-table tbody td:nth-child(2){ text-align:left; }
*/

/* Badges prioridad (Alta/Media/Baja) */
.ev-badge{
  display:inline-flex;
  align-items:center;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 0.82rem;
  font-weight: 900;
  border: 1px solid transparent;
  /* ❌ quitamos lowercase porque tú quieres “Alta/Media/Baja” */
  text-transform: none;
  letter-spacing: 0.01em;
}

.ev-badge-alta{
  background: rgba(234,124,18,0.12);
  border-color: rgba(234,124,18,0.35);
  color: #92400E;
}
.ev-badge-media{
  background: rgba(22,163,74,0.12);
  border-color: rgba(22,163,74,0.25);
  color: #14532D;
}
.ev-badge-baja{
  background: rgba(59,130,246,0.12);
  border-color: rgba(59,130,246,0.22);
  color: #1E3A8A;
}

/* Micro-premium: suavizar cards KPI */
.ev-card.p-3{
  background:
    linear-gradient(180deg, rgba(249,250,251,0.55), rgba(255,255,255,0.0) 45%),
    #ffffff;
}

/* Quick links */
.ev-quick{
  display:flex;
  align-items:center;
  gap:12px;
  padding: 14px 14px;
  border-radius: 16px;
  background:#fff;
  border:1px solid rgba(148,163,184,0.22);
  text-decoration:none;
  transition: all .18s ease;
}
.ev-quick:hover{
  background:#ECFDF5;
  border-color: rgba(22,163,74,0.35);
  transform: translateY(-1px);
}
.ev-quick-ico{
  width: 44px; height: 44px;
  border-radius: 14px;
  display:grid; place-items:center;
  background: rgba(187,247,208,0.55);
  border:1px solid rgba(22,163,74,0.20);
  color: var(--ev-verde-oscuro);
}
.ev-quick-title{
  font-weight: 900;
  color: var(--ev-verde-oscuro);
}

@media (max-width: 576px){
  .ev-recargas-title{ font-size: 1.65rem; }
  .ev-btn-atender{ width: 100%; min-width: unset; }
}
</style>
