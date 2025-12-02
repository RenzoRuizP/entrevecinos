<style>
/* ===========================================
   ESTILO UNIFICADO ENTRE CREDENCIALES Y DATOS PERSONALES
=========================================== */

:root {
  --verde-ev: #0F592F;
  --verde-ev-claro: #138f57;
  --gris-borde: #d9e3dc;
  --gris-fondo: #f5f6f8;
  --gris-texto: #555;
}

/* CONTENEDOR GENERAL */
.container-datos-personales {
  max-width: 1150px;
  margin: 25px auto;
  padding: 0 15px;
  animation: fadeIn .4s ease-in-out;
}

/* CARD BASE */
.container-datos-personales .card {
  border-radius: 16px;
  border: none;
  background: #ffffff;
  box-shadow: 0 10px 26px rgba(0,0,0,0.08);
  overflow: hidden;
}

/* =======================
   HEADER (UNIFICADO)
======================= */
.container-datos-personales .card-header {
  background: var(--verde-ev);
  padding: 22px 28px;
  border: none;
}

/* Título principal */
.container-datos-personales .card-header h5 {
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

/* Icono opcional dentro del título */
.container-datos-personales .card-header h5 i {
  font-size: 1.35rem;
}

/* Subtítulo debajo del título */
.container-datos-personales .card-header small {
  display: block;
  margin-top: 4px;
  font-size: 0.86rem;
  color: #e8f5ed;
}

/* =======================
   INPUTS ESTÁNDAR EV
======================= */
.input-premium,
.form-select.input-premium {
  border-radius: 14px;
  border: 1px solid var(--gris-borde);
  padding: 11px 14px;
  background: #ffffff;
  font-size: 0.95rem;
  transition: all .25s ease;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
}

.input-premium:hover,
.form-select.input-premium:hover {
  border-color: #bcd4c7;
}

.input-premium:focus,
.form-select.input-premium:focus {
  border-color: var(--verde-ev);
  outline: none;
  box-shadow: 0 0 0 4px rgba(15,89,47,.17);
}

/* SELECT con flecha personalizada */
.form-select.input-premium {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg fill='%230F592F' height='16' viewBox='0 0 16 16' width='16' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M4 6l4 4 4-4z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  background-size: 14px 14px;
}

/* LABELS */
.form-label {
  font-weight: 700;
  color: var(--verde-ev);
  margin-bottom: 4px;
}

/* =======================
   BOTÓN GUARDAR
======================= */
.btn-guardar {
  background: var(--verde-ev);
  color: #ffffff;
  padding: 10px 26px;
  font-weight: 600;
  border-radius: 10px;
  border: none;
  font-size: 1rem;
  transition: all .25s ease;
  box-shadow: 0 4px 12px rgba(15,89,47,0.25);
}

.btn-guardar:hover {
  background: var(--verde-ev-claro);
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(15,89,47,0.32);
}

/* ============== CANCELAR (por si lo usas luego) */
.btn-cancelar {
  background: #e9ecef;
  border: 1px solid #ced4da;
  padding: 10px 22px;
  font-weight: 600;
  border-radius: 10px;
  color: #444;
  transition: all .25s ease;
}

.btn-cancelar:hover {
  background: #d5d5d5;
  transform: translateY(-2px);
}

/* ANIMACIÓN GENERAL */
@keyframes fadeIn {
  from { opacity:0; transform: translateY(10px); }
  to { opacity:1; transform: translateY(0); }
}

/* =======================================
   📱 RESPONSIVE – MODO APP (Opción A)
======================================= */
@media (max-width: 768px) {
  .container-datos-personales {
    max-width: 100%;
    margin: 18px auto;
    padding: 0 12px;
  }

  .container-datos-personales .card {
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.10);
  }

  .container-datos-personales .card-header {
    padding: 16px 18px;
  }

  .container-datos-personales .card-header h5 {
    font-size: 1.1rem;
    gap: 8px;
  }

  .container-datos-personales .card-header small {
    font-size: 0.8rem;
  }

  /* Reforzamos que el body sea más compacto en móvil si Bootstrap no lo define */
  .container-datos-personales .card-body {
    padding: 18px 16px 20px 16px;
  }

  /* Campos en una sola columna (col-md-6 → 100%) */
  .container-datos-personales .row.g-3 > [class^="col-md-6"],
  .container-datos-personales .row.g-3 > [class*=" col-md-6"] {
    flex: 0 0 100%;
    max-width: 100%;
  }

  .container-datos-personales .form-label {
    font-size: 0.88rem;
  }

  .container-datos-personales .input-premium,
  .container-datos-personales .form-select.input-premium {
    font-size: 0.9rem;
    padding: 9px 12px;
  }

  /* Botones full-width y apilados para mejor UX táctil */
  .container-datos-personales .btn-guardar,
  .container-datos-personales .btn-cancelar {
    width: 100%;
    justify-content: center;
  }

  .container-datos-personales #btnCancelar {
    margin-top: 8px;
  }
}

@media (max-width: 576px) {
  .container-datos-personales .card-body {
    padding: 16px 12px 18px 12px;
  }
}
</style>
