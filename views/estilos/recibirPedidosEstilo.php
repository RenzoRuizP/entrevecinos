<style>
/* =========================================
   ENTRE VECINOS - RECIBIR PEDIDOS
   Estilo unificado con Login / Datos personales / Billetera
========================================= */

:root{
  --ev-verde-oscuro: var(--verde-oscuro);
  --ev-verde:        var(--verde-claro);
  --ev-verde-suave:  #E6F4EC;
  --ev-naranja:      var(--naranja-ev, #FF7A1A);
  --ev-gris-fondo:   var(--gris-claro);
  --ev-gris-borde:   var(--gris-borde);
  --ev-texto:        #111827;
  --ev-texto-suave:  var(--gris-texto);
}

/* Wrapper general */
.ev-recibir-wrapper{
  max-width: 1100px;
  margin: 0 auto;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* =========================================
   CARD PRINCIPAL: RECIBIR PEDIDOS
========================================= */

.ev-recibir-card{
  border-radius: 18px;
  border: 1px solid var(--ev-gris-borde);
  box-shadow: 0 10px 30px rgba(15, 89, 47, 0.06);
  overflow: hidden;
  background-color: #ffffff;
}

/* Header */
.ev-recibir-card .ev-recibir-header{
  background-color: #ffffff;
  border-bottom: 1px solid #E5E7EB;
  padding: 20px 24px 18px 24px; /* un poco más de aire arriba */
}

.ev-recibir-icon-wrapper{
  width: 40px;
  height: 40px;
  border-radius: 999px;
  background-color: #ECFDF3;
  color: var(--ev-verde);
  font-size: 1.2rem;
}

.ev-recibir-title{
  font-weight: 600;
  color: var(--ev-verde-oscuro);
}

.ev-recibir-subtitle{
  font-size: 0.82rem;
  color: var(--ev-texto-suave);
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

/* Estado conectado (la lógica la maneja tu JS cambiando clases) */
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
  padding: 22px 24px 22px 24px;
}

/* =========================================
   Toggle principal - SWITCH PREMIUM EV
========================================= */

.ev-toggle-row{
  margin-bottom: 4px;
}

.ev-switch-wrapper{
  min-width: 260px;
}

/* Contenedor del switch + texto */
.ev-switch{
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}

/* Input oculto */
.ev-switch input{
  opacity: 0;
  width: 0;
  height: 0;
  position: absolute;
}

/* Track */
.ev-switch-track{
  position: relative;
  width: 54px;
  height: 30px;
  border-radius: 999px;
  background-color: #E5E7EB;
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.10);
  transition: background-color 0.25s ease, box-shadow 0.25s ease;
  flex-shrink: 0;
}

/* Thumb */
.ev-switch-thumb{
  position: absolute;
  top: 3px;
  left: 4px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: #FFFFFF;
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.15);
  transition: transform 0.25s ease;
}

/* Texto a la derecha del switch */
.ev-switch-text{
  font-size: 0.96rem;
  font-weight: 600;
  color: #374151;
}

/* Estado ON (checked) */
.ev-switch input:checked + .ev-switch-track{
  background-color: var(--ev-verde);
  box-shadow: 0 10px 22px rgba(22, 163, 74, 0.35);
}

.ev-switch input:checked + .ev-switch-track .ev-switch-thumb{
  transform: translateX(22px);
}

.ev-switch input:checked ~ .ev-switch-text{
  color: var(--ev-verde-oscuro);
}

/* Hover */
.ev-switch:hover .ev-switch-track{
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

/* =========================================
   SECCIÓN: PEDIDOS ENTRANTES
========================================= */

.ev-pedidos-section{
  margin-top: 36px;
  padding-top: 12px;
  border-top: 1px solid rgba(229, 231, 235, 0.8);
}

/* Card de pedidos */
.ev-pedidos-card{
  border-radius: 18px;
  border: 1px solid var(--ev-gris-borde);
  box-shadow: 0 9px 26px rgba(15, 23, 42, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  background-color: #ffffff;
}

.ev-pedidos-card:hover{
  transform: translateY(-1px);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
}

.ev-pedidos-card .card-header{
  background-color: #ffffff;
  border-bottom: 1px solid #E5E7EB;
  padding: 16px 24px 12px 24px;
}

.ev-pedidos-card .card-body{
  padding: 16px 24px 18px 24px;
}

/* Icono del bloque de pedidos */
.ev-pedidos-icon{
  width: 34px;
  height: 34px;
  border-radius: 999px;
  background-color: #ECFDF3;
  color: var(--ev-verde);
  font-size: 1rem;
}

.ev-pedidos-title{
  font-weight: 600;
  color: var(--ev-verde-oscuro);
  margin-bottom: 0;
}

.ev-pedidos-subtitle{
  display: block;
  margin-top: 2px;
  font-size: 0.85rem;
  color: var(--ev-texto-suave);
}

/* Contador pill */
.ev-pedidos-counter{
  font-size: 0.78rem;
  border-radius: 999px;
  padding: 4px 12px;
  background-color: #F3F4F6;
  color: #4B5563;
  font-weight: 600;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
}

/* Mensajes informativos */
.ev-pedidos-info-alert{
  font-size: 0.87rem;
  border-radius: 12px;
  padding: 12px 16px;
  margin-top: 8px;
  margin-bottom: 12px;
  max-width: 80%;
  margin-left: auto;
  margin-right: auto;
  text-align: center;
}

/* Estado: desconectado */
.ev-pedidos-info-alert-off{
  background-color: #F8F9FA;
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
  margin-top: 4px;
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
  background-color: var(--ev-verde);
  border-color: var(--ev-verde);
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
