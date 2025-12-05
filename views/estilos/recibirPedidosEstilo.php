<style>
/* =========================================
   ENTRE VECINOS - RECIBIR PEDIDOS (Opción A)
========================================= */

.ev-recibir-wrapper{
  max-width: 1100px;
  margin: 0 auto;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Card principal */
.ev-recibir-card{
  border-radius: 18px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
  overflow: hidden;
}

/* Header limpio, sin franja verde gruesa */
.ev-recibir-card .ev-recibir-header{
  background-color: #ffffff;
  border-bottom: 1px solid #E5E7EB;
  padding: 16px 24px;
}

.ev-recibir-icon-wrapper{
  width: 40px;
  height: 40px;
  border-radius: 999px;
  background-color: #ECFDF3;
  color: #16A34A;
  font-size: 1.2rem;
}

.ev-recibir-title{
  font-weight: 600;
  color: #111827;
}

.ev-recibir-subtitle{
  font-size: 0.82rem;
  color: #6B7280 !important;
}

/* Pill de estado */
.ev-status-pill{
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 0.8rem;
  border: 1px solid transparent;
}

.ev-status-text{
  font-weight: 500;
}

.ev-status-dot{
  width: 9px;
  height: 9px;
  border-radius: 999px;
}

/* Estado desconectado */
.ev-status-off{
  background-color: #F3F4F6;
  color: #6B7280;
  border-color: #E5E7EB;
}
.ev-status-dot-off{
  background-color: #9CA3AF;
}

/* Estado conectado */
.ev-status-on{
  background-color: #DCFCE7;
  color: #15803D;
  border-color: #BBF7D0;
}
.ev-status-dot-on{
  background-color: #16A34A;
}

/* Cuerpo */
.ev-recibir-body{
  padding: 24px 24px 26px 24px;
}

/* =========================================
   Toggle principal (inspirado en Opción A)
========================================= */

.ev-toggle-row{
  margin-bottom: 28px;
}

/* Wrapper para mantener tamaño máximo */
.ev-switch-wrapper{
  min-width: 260px;
}

/* Label general del switch */
.ev-switch{
  position: relative;
  display: inline-block;
  width: 260px;
  height: 54px;
}

/* Ocultamos el checkbox */
.ev-switch input{
  opacity: 0;
  width: 0;
  height: 0;
}

/* Pista del slider */
.ev-slider{
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: #E5E7EB; /* OFF */
  border-radius: 999px;
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.10);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 18px;
  transition: background-color 0.25s ease, box-shadow 0.25s ease;
}

/* Texto dentro del slider */
.ev-slider-label{
  z-index: 2;
  font-size: 0.9rem;
  font-weight: 500;
  color: #4B5563;
}

/* Thumb (círculo) usando ::before */
.ev-slider::before{
  content: "";
  position: absolute;
  left: 10px;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background-color: #FFFFFF;
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.15);
  transition: transform 0.25s ease;
}

/* Estado ON */
/* El checkbox está checked => modificamos color y posición */
.ev-switch input:checked + .ev-slider{
  background-color: #16A34A; /* Verde EV */
  box-shadow: 0 10px 22px rgba(22, 163, 74, 0.35);
}

.ev-switch input:checked + .ev-slider .ev-slider-label{
  color: #ffffff;
}

/* Mover el thumb a la derecha cuando está ON */
.ev-switch input:checked + .ev-slider::before{
  transform: translateX(180px);
}

/* Hover sutil */
.ev-switch:hover .ev-slider{
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.22);
}

/* Texto secundario de estado */
.ev-estado-secundario{
  max-width: 420px;
}

.ev-estado-secundario-label{
  font-size: 0.9rem;
  color: #374151;
}

.ev-estado-secundario-label strong{
  font-weight: 600;
}

.ev-estado-secundario-help{
  margin-top: 4px;
  font-size: 0.85rem;
  color: #6B7280;
}

/* Info card inferior */
.ev-recibir-info-card{
  margin-top: 8px;
  padding: 16px 18px;
  border-radius: 16px;
  background-color: #FFFFFF;
  border: 1px solid rgba(209, 213, 219, 0.8);
  box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
}

.ev-recibir-info-illustration{
  width: 86px;
  height: 86px;
  border-radius: 18px;
  background-color: #ECFDF3;
  flex-shrink: 0;
  overflow: hidden;
}

.ev-recibir-info-illustration img{
  max-width: 100%;
  height: 100%;
  object-fit: contain;
}

.ev-recibir-info-title{
  font-weight: 600;
  color: #166534;
  font-size: 0.98rem;
}

.ev-recibir-info-text{
  font-size: 0.88rem;
  color: #4B5563;
}

/* =========================================
   Responsivo
========================================= */

@media (max-width: 767.98px){
  .ev-recibir-card .ev-recibir-header{
    padding-inline: 16px;
  }

  .ev-recibir-body{
    padding-inline: 16px;
  }

  .ev-toggle-row{
    align-items: flex-start !important;
  }

  .ev-switch-wrapper{
    width: 100%;
    display: flex;
    justify-content: center;
  }

  .ev-estado-secundario{
    max-width: 100%;
  }

  .ev-recibir-info-card{
    flex-direction: row;
  }
}
</style>
