<?php
// views/estilos/credencialesEstilo.php
// Estilos específicos para la vista de Credenciales (cambiar contraseña)
?>
<style>
/* =========================================
   ENTRE VECINOS - CREDENCIALES (Premium)
   Vista de cambio de contraseña
========================================= */

.ev-credenciales-wrapper {
  padding: 24px 18px;
  background-color: #F3F4F6;
  min-height: calc(100vh - 70px);
}

@media (min-width: 992px) {
  .ev-credenciales-wrapper {
    padding: 32px 32px;
  }
}

/* ---------------------------
   Encabezado principal
---------------------------- */
.ev-credenciales-header h2 {
  font-size: 1.7rem;
  font-weight: 700;
  color: #0B3D26;
  margin-bottom: 4px;
}

.ev-credenciales-header p {
  margin: 0;
  font-size: 0.96rem;
  color: #6B7280;
}

/* ---------------------------
   Alert de consejo de seguridad
---------------------------- */
.ev-credenciales-alert {
  border-radius: 14px;
  font-size: 0.88rem;
  background-color: #E0F2FE;
  border-color: #BFDBFE;
  color: #0F172A;
  padding-block: 10px;
}

/* ---------------------------
   Card principal
---------------------------- */
.ev-credenciales-card {
  position: relative;
  border-radius: 20px;
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
  border: 1px solid rgba(148, 163, 184, 0.25);
  background-color: #FFFFFF;
  margin-top: 20px;
  overflow: hidden;
}

/* Banda superior con gradiente EV */
.ev-credenciales-card::before {
  content: "";
  position: absolute;
  inset: 0;
  height: 5px;
  background: linear-gradient(135deg, #0F592F, #198754);
}

/* Header interno del card: BLANCO (sobreescribe estilo global de .card-header) */
.ev-credenciales-card .card-header {
  border: 0;
  border-radius: 0;
  background: #FFFFFF !important;
  background-color: #FFFFFF !important;
  color: #0F592F;
  padding: 18px 24px 12px 24px;
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 5px; /* deja visible la banda superior */
}

/* Refuerzo extra por si hay selectores más específicos */
.ev-credenciales-card > .card-header {
  background: #FFFFFF !important;
  background-color: #FFFFFF !important;
}

/* Icono circular del header */
.ev-credenciales-card .ev-credenciales-icon {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  background: rgba(15, 89, 47, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0F592F;
}

.ev-credenciales-card .card-header h5 {
  margin: 0;
  font-size: 1.12rem;
  font-weight: 600;
  color: #0F592F;
}

.ev-credenciales-card .card-header small {
  font-size: 0.86rem;
  color: #6B7280;
}

/* Cuerpo del card */
.ev-credenciales-card .card-body {
  padding: 20px 24px 24px 24px;
}

/* Separador sutil entre bloque de datos y formulario */
.ev-credenciales-divider {
  border-bottom: 1px solid #E5E7EB;
  margin: 14px 0 18px 0;
}

/* ---------------------------
   Tipografías de formulario específicas de esta vista
---------------------------- */
.ev-credenciales-form-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #111827;
}

.ev-credenciales-form-text {
  font-size: 0.82rem;
  color: #6B7280;
}

/* ---------------------------
   Barra de fuerza de contraseña
---------------------------- */
.ev-password-strength {
  height: 6px;
  border-radius: 999px;
  background-color: #E5E7EB;
  overflow: hidden;
}

.ev-password-strength-bar {
  height: 100%;
  width: 0%;
  background: linear-gradient(90deg, #F97316, #10B981);
  transition: width 0.25s ease;
}

/* ---------------------------
   Responsivo específico
---------------------------- */
@media (max-width: 991.98px) {
  .ev-credenciales-card .card-body {
    padding-inline: 16px;
  }
}

/* === Ajustes extra para modo móvil (Opción A tipo app) === */
@media (max-width: 768px) {
  .ev-credenciales-wrapper {
    padding: 18px 12px;
    min-height: auto;
  }

  .ev-credenciales-header h2 {
    font-size: 1.35rem;
  }

  .ev-credenciales-header p {
    font-size: 0.9rem;
  }

  .ev-credenciales-card {
    border-radius: 18px;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.16);
    margin-top: 16px;
  }

  .ev-credenciales-card .card-header {
    padding: 14px 16px 10px 16px;
    gap: 10px;
  }

  .ev-credenciales-card .card-header h5 {
    font-size: 1rem;
  }

  .ev-credenciales-card .card-body {
    padding: 16px 14px 18px 14px;
  }
}

@media (max-width: 576px) {
  /* Botones apilados y full width dentro de la tarjeta */
  .ev-credenciales-card .card-body .btn {
    width: 100%;
    justify-content: center;
    margin-bottom: 8px;
  }

  .ev-credenciales-card .card-body .btn:last-child {
    margin-bottom: 0;
  }

  .ev-credenciales-form-label {
    font-size: 0.85rem;
  }

  .ev-credenciales-form-text {
    font-size: 0.78rem;
  }
}
</style>
