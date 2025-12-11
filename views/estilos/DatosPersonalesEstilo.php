<style>
/* ====================================================
   DATOS PERSONALES - ENTRE VECINOS
   Estilo unificado con Login / Dashboard / Marketplace
==================================================== */

:root {
  --ev-verde-oscuro: #0F592F;
  --ev-verde: #198754;
  --ev-verde-suave: #E6F4EC;
  --ev-naranja: #FF7A1A;
  --ev-gris-fondo: #F3F4F6;
  --ev-gris-borde: #E5E7EB;
  --ev-texto: #1A1F36;
  --ev-texto-suave: #6B7280;
}

/* CONTENEDOR GENERAL */
.container-datos-personales {
  max-width: 1100px;
  margin: 24px auto;
  padding: 0 12px;
  animation: evFadeIn .35s ease-out;
}

/* CARD BASE */
.ev-datos-card {
  border-radius: 18px;
  border: 0;
  background: #ffffff;
  box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
  overflow: hidden;
}

/* =======================
   HEADER
======================= */
.ev-datos-card .card-header {
  background: #ffffff;
  border-bottom: 1px solid var(--ev-gris-borde);
  padding: 18px 22px;
}

.ev-datos-icon {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  background: var(--ev-verde-suave);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--ev-verde-oscuro);
  font-size: 1.3rem;
}

.ev-datos-card .card-header h5 {
  margin: 0;
  color: var(--ev-verde-oscuro);
  font-weight: 700;
  font-size: 1.2rem;
}

.ev-datos-subtitle {
  font-size: 0.9rem;
  color: var(--ev-texto-suave);
  line-height: 1.4;
}

/* =======================
   BODY / FORMULARIO
======================= */
.ev-datos-card .card-body {
  padding: 16px 22px 20px 22px; /* ligeramente más compacto */
}

.ev-datos-form {
  width: 100%;
}

/* Labels */
.ev-form-label {
  font-weight: 600;
  color: var(--ev-verde-oscuro);
  font-size: 0.93rem;
  margin-bottom: 2px; /* micro-alineación con los inputs */
}

/* Inputs y selects */
.ev-input-rounded,
.ev-input-rounded.form-select {
  border-radius: 12px;
  border: 1px solid var(--ev-gris-borde);
  font-size: 0.95rem;
  padding: 10px 12px;
  background-color: #ffffff;
  transition: all 0.18s ease-out;
  height: 48px;
}

.ev-input-rounded:hover,
.ev-input-rounded.form-select:hover {
  border-color: #D1D5DB;
}

.ev-input-rounded:focus,
.ev-input-rounded.form-select:focus {
  border-color: var(--ev-verde);
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.25);
  outline: none;
}

/* Select con flecha personalizada */
.ev-input-rounded.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg fill='%230F592F' height='16' viewBox='0 0 16 16' width='16' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M4 6l4 4 4-4z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 14px 14px;
}

/* Mensajes de ayuda */
.ev-form-help {
  font-size: 0.8rem;
  color: var(--ev-texto-suave);
}

/* Separador */
.ev-datos-divider {
  margin-top: 8px;
  margin-bottom: 0;
  border-color: #e2e8f0;
  opacity: 0.7;
}

/* =======================
   FOOTER DE ACCIONES
======================= */
.ev-datos-footer {
  margin-top: 16px;
  padding: 12px 0 0 0;
  border-top: 1px solid var(--ev-gris-borde);
}

/* Botón principal (Guardar) */
.btn-ev-primary.btn-guardar {
  background: linear-gradient(135deg, #D97706, #EA7C12);
  border: none;
  color: #ffffff;
  border-radius: 10px;
  font-size: 0.96rem;
  padding: 8px 20px; /* un poco más bajo que antes */
  font-weight: 600;
  box-shadow: 0 8px 20px rgba(217, 119, 6, 0.35);
  transition: all 0.2s ease;
}

.btn-ev-primary.btn-guardar:hover {
  background: linear-gradient(135deg, #C46B05, #D46F0F);
  color: #ffffff;
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(217, 119, 6, 0.45);
}

.btn-ev-primary.btn-guardar:active {
  transform: translateY(0);
  box-shadow: 0 4px 10px rgba(217, 119, 6, 0.30);
}

/* Estado "guardando" */
.btn-ev-primary.btn-guardar.saving {
  opacity: 0.85;
  cursor: wait;
}

/* Botón secundario (Cancelar) */
.btn-ev-neutral.btn-cancelar {
  border-radius: 10px;
  border: 1px solid var(--ev-gris-borde);
  background: #ffffff;
  color: var(--ev-texto-suave);
  font-weight: 500;
  font-size: 0.94rem;
  padding: 8px 18px;
  transition: all 0.18s ease;
}

.btn-ev-neutral.btn-cancelar:hover {
  background: #F3F4F6;
  color: var(--ev-texto);
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
}

/* =======================
   ANIMACIÓN
======================= */
@keyframes evFadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* =======================
   RESPONSIVE
======================= */
@media (max-width: 991.98px) {
  .container-datos-personales {
    max-width: 100%;
  }
}

@media (max-width: 768px) {
  .container-datos-personales {
    margin: 18px auto;
    padding: 0 10px;
  }

  .ev-datos-card {
    border-radius: 16px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10);
  }

  .ev-datos-card .card-header {
    padding: 14px 16px;
  }

  .ev-datos-card .card-body {
    padding: 16px 14px 18px 14px;
  }

  /* Campos en una sola columna en móvil */
  .container-datos-personales .row.g-3 > [class^="col-md-"],
  .container-datos-personales .row.g-3 > [class*=" col-md-"] {
    flex: 0 0 100%;
    max-width: 100%;
  }

  .ev-form-label {
    font-size: 0.88rem;
  }

  .ev-input-rounded,
  .ev-input-rounded.form-select {
    font-size: 0.9rem;
    padding: 9px 11px;
    height: 46px;
  }

  .ev-datos-footer {
    padding-top: 10px;
  }

  /* Botones full-width para mejor UX táctil */
  .btn-ev-primary.btn-guardar,
  .btn-ev-neutral.btn-cancelar {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 575.98px) {
  .ev-datos-card .card-body {
    padding-inline: 12px;
  }
}

/* 🔧 Fix: eliminar cualquier pseudo-elemento decorativo antiguo
   (la "mancha" o barra verde al lado del título) */
.ev-datos-card .card-header::before,
.ev-datos-card .card-header::after {
  content: none !important;
  display: none !important;
}
</style>
