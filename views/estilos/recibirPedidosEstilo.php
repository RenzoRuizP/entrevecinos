<?php
// views/estilos/recibirPedidosEstilo.php
?>
<style>
/* ===========================================
   ESTILO RECIBIR PEDIDOS
   Basado en DatosPersonalesEstilo.php
=========================================== */

:root {
  --verde-ev: #0F592F;
  --verde-ev-claro: #138f57;
  --gris-borde: #d9e3dc;
  --gris-fondo: #f5f6f8;
  --gris-texto: #555;
}

/* CONTENEDOR GENERAL */
.container-recibir-pedidos {
  max-width: 1150px;
  margin: 25px auto;
  padding: 0 15px;
  animation: fadeIn .4s ease-in-out;
}

/* CARD BASE */
.container-recibir-pedidos .card {
  border-radius: 16px;
  border: none;
  background: #ffffff;
  box-shadow: 0 10px 26px rgba(0,0,0,0.08);
  overflow: hidden;
}

/* HEADER */
.container-recibir-pedidos .card-header {
  background: var(--verde-ev);
  padding: 22px 28px;
  border: none;
}

.container-recibir-pedidos .card-header h5 {
  margin: 0;
  font-size: 1.35rem;
  color: #ffffff;
  font-weight: 600;
  line-height: 1.25;
  display: inline-flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}

.container-recibir-pedidos .card-header h5 i {
  font-size: 1.35rem;
}

/* Badge estado */
.badge-estado {
  background: rgba(255,255,255,0.1);
  color: #e8f5ed;
  border-radius: 999px;
  padding: 6px 14px;
  font-size: 0.82rem;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* BODY */
.container-recibir-pedidos .card-body {
  padding: 22px 24px 24px 24px;
}

/* ==========================
   SLIDER PRINCIPAL
========================== */

.rp-slider-banner {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 10px 0 24px 0;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(0,0,0,0.06);
}

.rp-slider-toggle {
  border-radius: 999px;
  border: 2px solid #c5ccda;
  padding: 14px 40px;
  background: linear-gradient(145deg, #c7cedd, #e4e7ef);
  color: #3b4252;
  font-weight: 600;
  font-size: 1rem;
  min-width: min(420px, 100%);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
  box-shadow:
    inset 0 2px 6px rgba(255,255,255,0.75),
    0 8px 18px rgba(15, 89, 47, 0.12);
  transition: all .25s ease;
}

.rp-slider-toggle:hover {
  transform: translateY(-3px);
  box-shadow:
    inset 0 2px 6px rgba(255,255,255,0.85),
    0 12px 26px rgba(15, 89, 47, 0.18);
}

.rp-slider-toggle:active {
  transform: scale(0.985);
  box-shadow:
    inset 0 1px 3px rgba(0,0,0,0.10),
    0 6px 16px rgba(15,89,47,0.16);
}

.rp-slider-toggle.rp-on {
  background: linear-gradient(145deg, #0f592f, #138f57);
  border-color: var(--verde-ev);
  color: #ffffff;
  box-shadow:
    inset 0 2px 6px rgba(255,255,255,0.35),
    0 12px 28px rgba(15,89,47,0.45);
}

.rp-slider-arrow {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 999px;
  background: rgba(255,255,255,0.75);
  color: #3b4252;
  box-shadow: 0 2px 5px rgba(0,0,0,0.12);
  transition: transform .25s ease;
}

.rp-slider-arrow i {
  font-size: 1.3rem;
  transform: translateX(1px);
}

.rp-slider-toggle.rp-on .rp-slider-arrow {
  background: rgba(255,255,255,0.9);
  color: var(--verde-ev);
}

.rp-slider-toggle:hover .rp-slider-arrow {
  transform: translateX(3px);
}

.rp-slider-text {
  letter-spacing: 0.02em;
}

/* Animación de la flecha en estado desconectado */
@keyframes arrowPulse {
  0%   { transform: translateX(1px); }
  50%  { transform: translateX(6px); }
  100% { transform: translateX(1px); }
}

.rp-slider-toggle.rp-off .rp-slider-arrow i {
  animation: arrowPulse 1.4s ease-in-out infinite;
}

/* ==========================
   ESTADO DESCONECTADO
========================== */

.rp-estado-wrapper {
  margin-top: 4px;
}

.rp-estado-card {
  border-radius: 18px;
  background: #ffffff;
  padding: 20px 26px;
  display: grid;
  grid-template-columns: minmax(0, 230px) minmax(0, 1fr);
  align-items: center;
  gap: 22px;
  box-shadow: 0 8px 22px rgba(0,0,0,0.05);
}

.rp-estado-illustration {
  display: flex;
  align-items: center;
  justify-content: center;
}

.rp-estado-img {
  max-width: 170px;
  width: 100%;
  height: auto;
}

/* Texto estado */
.rp-estado-texto {
  text-align: left;
}

.rp-estado-title {
  font-size: 1.28rem;
  font-weight: 700;
  margin-bottom: 6px;
  color: var(--verde-ev);
}

.rp-estado-subtitle {
  font-size: 1rem;
  color: #6c757d;
  margin-bottom: 0;
}

/* ==========================
   LISTA DE PEDIDOS (CONECTADO)
========================== */

.rp-lista-wrapper {
  margin-top: 22px;
  background: #ffffff;
  border-radius: 18px;
  padding: 18px 18px 20px 18px;
  box-shadow: 0 8px 22px rgba(0,0,0,0.05);
}

.rp-pedidos-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.rp-pedido-item {
  border-radius: 12px;
  border: 1px solid var(--gris-borde);
  padding: 10px 12px;
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 10px;
}

.rp-pedido-header {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 10px;
  align-items: center;
}

.rp-pedido-title {
  font-weight: 600;
  color: var(--verde-ev);
}

.rp-pedido-meta {
  font-size: 0.85rem;
  color: var(--gris-texto);
}

.rp-pedido-actions {
  display: flex;
  flex-direction: column;
  gap: 6px;
  align-items: flex-end;
}

.rp-btn-accept,
.rp-btn-reject {
  border-radius: 10px;
  font-size: 0.8rem;
  padding: 5px 14px;
  border-width: 1px;
  font-weight: 600;
}

/* Aceptar */
.rp-btn-accept {
  background: var(--verde-ev);
  color: #ffffff;
  border: none;
  box-shadow: 0 4px 12px rgba(15,89,47,0.25);
}

.rp-btn-accept:hover {
  background: var(--verde-ev-claro);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(15,89,47,0.32);
}

/* Rechazar */
.rp-btn-reject {
  background: #fbe9eb;
  border-color: #dc3545;
  color: #dc3545;
}

.rp-btn-reject:hover {
  background: #dc3545;
  color: #ffffff;
}

/* Estado vacío */
.rp-empty-state {
  border-radius: 12px;
  padding: 14px 12px;
  border: 1px dashed #ced4da;
  text-align: center;
  color: var(--gris-texto);
}

.rp-empty-icon {
  font-size: 1.6rem;
  display: block;
  margin-bottom: 4px;
}

/* ANIMACIÓN GENERAL */
@keyframes fadeIn {
  from { opacity:0; transform: translateY(10px); }
  to   { opacity:1; transform: translateY(0); }
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .container-recibir-pedidos {
    max-width: 100%;
    margin: 18px auto;
    padding: 0 12px;
  }

  .container-recibir-pedidos .card {
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.10);
  }

  .container-recibir-pedidos .card-header {
    padding: 16px 18px;
  }

  .container-recibir-pedidos .card-header h5 {
    font-size: 1.1rem;
  }

  .container-recibir-pedidos .card-body {
    padding: 18px 16px 20px 16px;
  }

  .rp-estado-card {
    grid-template-columns: minmax(0, 1fr);
    text-align: center;
  }

  .rp-estado-texto {
    text-align: center;
  }

  .rp-slider-toggle {
    min-width: 100%;
  }

  .rp-pedido-item {
    grid-template-columns: minmax(0, 1fr);
  }

  .rp-pedido-actions {
    flex-direction: row;
    justify-content: flex-start;
  }
}

@media (max-width: 576px) {
  .container-recibir-pedidos .card-body {
    padding: 16px 12px 18px 12px;
  }
}
</style>
