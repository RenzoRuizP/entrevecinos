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
  margin-bottom: 20px;
}

.rp-slider-toggle {
  border-radius: 999px;
  border: 2px solid #dde2ea;
  padding: 14px 40px;
  background: #f5f6f8;
  color: #4b5563;
  font-weight: 600;
  font-size: 1rem;
  min-width: min(420px, 100%);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 8px 20px rgba(15, 89, 47, 0.08);
  transition: all .25s ease;
}

.rp-slider-toggle:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 26px rgba(15, 89, 47, 0.18);
}

.rp-slider-toggle.rp-on {
  background: var(--verde-ev);
  border-color: var(--verde-ev);
  color: #ffffff;
}

.rp-slider-toggle.rp-off {
  background: #f5f6f8;
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
  padding: 22px 24px;
  display: grid;
  grid-template-columns: minmax(0, 260px) minmax(0, 1fr);
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
  max-width: 220px;
  width: 100%;
  height: auto;
}

/* Texto estado */
.rp-estado-texto {
  text-align: left;
}

.rp-estado-title {
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: 6px;
  color: var(--verde-ev);
}

.rp-estado-subtitle {
  font-size: 0.95rem;
  color: var(--gris-texto);
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

/* ANIMACIÓN */
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
