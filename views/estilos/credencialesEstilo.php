<style>
/* =========================================
   ENTRE VECINOS - CREDENCIALES (Premium EV Classic)
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
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.12); /* sombra más suave */
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

/* Header interno del card: BLANCO (sobreescribe estilo global) */
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
   Tipografías de formulario
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
   Inputs redondeados
---------------------------- */
.ev-input-rounded {
  border-radius: 999px;
  padding-left: 42px;
  border-color: #E5E7EB;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.ev-input-rounded:focus {
  border-color: #0F592F;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.18);
}

/* Icono dentro del input */
.ev-input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #9CA3AF;
  font-size: 1.1rem;
}

/* ---------------------------
   Botones (Premium EV Classic)
---------------------------- */

/* Botón primario: sólido, corporativo, sombra suave */
.ev-btn-primary {
  background-color: #0F592F;         /* verde EV sólido */
  border-color: #0F592F;
  border-radius: 999px;
  padding-inline: 30px;
  font-weight: 600;
  color: #FFFFFF;
  box-shadow: 0 10px 20px rgba(15, 89, 47, 0.30); /* sombra más controlada */
  transition: background-color 0.18s ease, box-shadow 0.18s ease,
              transform 0.12s ease;
}

.ev-btn-primary:hover {
  background-color: #0C4524;        /* un poco más oscuro en hover */
  border-color: #0C4524;
  color: #FFFFFF;
  box-shadow: 0 12px 24px rgba(15, 89, 47, 0.35);
  transform: translateY(-1px);
}

.ev-btn-primary:active {
  background-color: #09321A;
  border-color: #09321A;
  box-shadow: 0 6px 14px rgba(15, 89, 47, 0.35);
  transform: translateY(0);
}

/* Botón secundario: limpio, ligero, jerarquía clara */
.ev-btn-outline {
  border-radius: 999px;
  font-weight: 500;
  border: 1px solid #15803D;        /* verde un poco más suave */
  color: #166534;                   /* texto verde medio */
  background-color: #FFFFFF;
  padding-inline: 22px;
  transition: background-color 0.18s ease, color 0.18s ease,
              box-shadow 0.18s ease, border-color 0.18s ease;
}

.ev-btn-outline:hover {
  background-color: #ECFDF3;        /* verde muy suave */
  color: #0B3D26;
  border-color: #15803D;
  box-shadow: 0 6px 14px rgba(21, 128, 61, 0.20);
}

.ev-btn-outline:active {
  background-color: #DCFCE7;
  color: #064E3B;
  box-shadow: 0 3px 8px rgba(21, 128, 61, 0.25);
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
   Responsivo
---------------------------- */
@media (max-width: 991.98px) {
  .ev-credenciales-card .card-body {
    padding-inline: 16px;
  }
}
</style>
