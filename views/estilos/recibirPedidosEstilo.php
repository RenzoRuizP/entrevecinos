<style>
/* =========================================
   ENTRE VECINOS - RECIBIR PEDIDOS
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

/* Header limpio */
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
  padding: 24px 24px 22px 24px;
}

/* =========================================
   Toggle principal
========================================= */

.ev-toggle-row{
  margin-bottom: 4px; /* reducido porque ya no hay info-card debajo */
}

.ev-switch-wrapper{
  min-width: 260px;
}

.ev-switch{
  position: relative;
  display: inline-block;
  width: 260px;
  height: 54px;
}

/* Ocultamos checkbox */
.ev-switch input{
  opacity: 0;
  width: 0;
  height: 0;
}

/* Pista */
.ev-slider{
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: #E5E7EB;
  border-radius: 999px;
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.10);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 18px;
  transition: background-color 0.25s ease, box-shadow 0.25s ease;
}

/* Texto interno */
.ev-slider-label{
  z-index: 2;
  font-size: 0.9rem;
  font-weight: 500;
  color: #4B5563;
}

/* Thumb */
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
.ev-switch input:checked + .ev-slider{
  background-color: #16A34A;
  box-shadow: 0 10px 22px rgba(22, 163, 74, 0.35);
}

.ev-switch input:checked + .ev-slider .ev-slider-label{
  color: #ffffff;
}

.ev-switch input:checked + .ev-slider::before{
  transform: translateX(180px);
}

.ev-switch:hover .ev-slider{
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.22);
}

/* Texto secundario */
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
  margin-top: 2px;
  font-size: 0.85rem;
  color: #6B7280;
}

/* (Quedan por si los reutilizas más adelante) */
.ev-recibir-info-card{
  margin-top: 8px;
  padding: 16px 18px;
  border-radius: 16px;
  background-color: #FFFFFF;
  border: 1px solid rgba(209, 213, 219, 0.8);
  box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
}

.ev-recibir-info-illustration{
  width: 78px;
  height: 78px;
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
   SECCIÓN: PEDIDOS ENTRANTES
========================================= */

.ev-pedidos-section{
  margin-top: 40px;       /* un poco más de aire respecto a la card superior */
  padding-top: 12px;
  border-top: 1px solid #E5E7EB; /* separador suave entre módulos */
}

/* Card de pedidos */
.ev-pedidos-card{
  border-radius: 18px;
  border: 1px solid rgba(209, 213, 219, 0.7);
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.10);
}

.ev-pedidos-card .card-header{
  background-color: #ffffff;
  border-bottom: 1px solid #E5E7EB;
  padding: 16px 24px;
}

.ev-pedidos-card .card-body{
  padding: 20px 24px 18px 24px;
}

.ev-pedidos-title{
  font-weight: 600;
  color: #111827;
  margin-bottom: 2px;
}

.ev-pedidos-subtitle{
  font-size: 0.85rem;
  color: #6B7280;
}

.ev-pedidos-counter{
  font-size: 0.75rem;
  border-radius: 999px;
  padding: 4px 10px;
  background-color: #F3F4F6;
  color: #4B5563;
  font-weight: 600;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06); /* ligera sombra para más presencia */
}

/* Mensajes informativos */
.ev-pedidos-info-alert{
  font-size: 0.87rem;
  border-radius: 12px;
  padding: 14px 16px;        /* un poco más de aire */
  margin-bottom: 8px;
  max-width: 92%;
  margin-left: auto;
  margin-right: auto;
}

/* Estado: desconectado (info neutra, no “alerta fuerte”) */
.ev-pedidos-info-alert-off{
  background-color: #F3F4F6;
  color: #4B5563;
  border: 1px solid #E5E7EB;
}

/* Estado: conectado pero sin pedidos */
.ev-pedidos-info-alert-empty{
  background-color: #F9FAFB;
  color: #4B5563;
  border: 1px solid #E5E7EB;
}

/* Contenedor de pedidos */
.ev-pedidos-lista{
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 8px;
}

/* Card individual de pedido */
.ev-pedido-card{
  border-radius: 16px;
  background-color: #FFFFFF;
  border: 1px solid rgba(209, 213, 219, 0.9);
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.10);
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.ev-pedido-main{
  display: flex;
  gap: 12px;
}

.ev-pedido-img-wrapper{
  width: 64px;
  height: 64px;
  border-radius: 14px;
  background-color: #F3F4F6;
  overflow: hidden;
  flex-shrink: 0;
}

.ev-pedido-img-wrapper img{
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.ev-pedido-info{
  flex: 1;
}

.ev-pedido-header-row{
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
}

.ev-pedido-producto{
  font-weight: 600;
  color: #111827;
  font-size: 0.95rem;
}

.ev-pedido-precio{
  font-weight: 600;
  color: #166534;
  font-size: 0.9rem;
}

.ev-pedido-vecino{
  font-size: 0.85rem;
  color: #4B5563;
}

.ev-pedido-detalle-line{
  font-size: 0.82rem;
  color: #6B7280;
}

.ev-pedido-comentario{
  margin-top: 4px;
  font-size: 0.83rem;
  color: #374151;
}

.ev-pedido-comentario strong{
  font-weight: 600;
}

/* Tiempo restante badge */
.ev-pedido-tiempo{
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.75rem;
  background-color: #FEF3C7;
  color: #92400E;
}

.ev-pedido-tiempo i{
  font-size: 0.8rem;
}

/* Acciones */
.ev-pedido-actions{
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 4px;
}

.ev-pedido-actions .btn{
  font-size: 0.78rem;
  padding: 4px 10px;
  border-radius: 999px;
}

/* Botones específicos */
.ev-btn-aceptar{
  background-color: #16A34A;
  border-color: #16A34A;
  color: #ffffff;
}
.ev-btn-aceptar:hover{
  background-color: #15803D;
  border-color: #15803D;
}

.ev-btn-rechazar{
  border-color: #D1D5DB;
  color: #374151;
  background-color: #ffffff;
}
.ev-btn-rechazar:hover{
  background-color: #F3F4F6;
  border-color: #9CA3AF;
}

.ev-btn-detalle,
.ev-btn-mensaje{
  border-color: #BFDBFE;
  color: #1D4ED8;
  background-color: #EFF6FF;
}
.ev-btn-detalle:hover,
.ev-btn-mensaje:hover{
  background-color: #DBEAFE;
  border-color: #93C5FD;
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

  .ev-pedidos-card .card-header,
  .ev-pedidos-card .card-body{
    padding-inline: 16px;
  }

  .ev-pedido-card{
    padding-inline: 12px;
  }

  .ev-pedido-main{
    flex-direction: row;
  }

  .ev-pedido-header-row{
    flex-direction: column;
  }
}
</style>
