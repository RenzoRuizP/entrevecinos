<!-- ===============================
     ESTILOS BASE PARA LOGIN ENTRE VECINOS
     =============================== -->

<!-- Tipografía Poppins -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.3/index.min.css">

<!-- Bootstrap 5 (CSS) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ===============================
     ESTILO PERSONALIZADO LOGIN
     =============================== -->
<style>
:root {
  --ev-verde-oscuro: #0F592F;
  --ev-verde: #16A34A;
  --ev-verde-claro: #bbf7d0;
  --ev-naranja: #EA7C12;
  --ev-naranja-oscuro: #C46B05;
  --ev-gris-050: #F9FAFB;
  --ev-gris-100: #F3F4F6;
  --ev-gris-500: #6B7280;
  --ev-gris-600: #4B5563;
}

/* --------------------------
   Fondo general / Layout
-------------------------- */
body.login-body {
  min-height: 100vh;
  margin: 0;
  padding: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
  color: #111827;

  /* Fondo neutro premium */
  background-color: var(--ev-gris-100);
  background-image:
    radial-gradient(circle at 50% 20%, rgba(22,163,74,0.10), transparent 60%);
}

/* Contenedor principal */
.login-shell {
  width: 100%;
  max-width: 980px;
  min-height: 520px;
  background: #ffffff;
  border-radius: 28px;
  overflow: hidden;
  display: flex;
  position: relative;
  box-shadow:
    0 28px 50px rgba(0,0,0,0.12),
    0 4px 12px rgba(0,0,0,0.06);
}

/* --------------------------
   Panel izquierdo (Hero)
-------------------------- */
.login-hero {
  flex: 1.1;
  position: relative;
  overflow: hidden;
  padding: 48px 40px;
  color: #F9FAFB;

  /* Degradado verde corporativo orgánico */
  background:
    radial-gradient(circle at 75% 30%, rgba(255,255,255,0.08), transparent 60%),
    radial-gradient(circle at 20% 80%, rgba(0,0,0,0.08), transparent 70%),
    linear-gradient(145deg, #0F592F 0%, #0E7A43 45%, #16A34A 85%);
}

/* Capa sutil de profundidad */
.login-hero-layer::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 85% 85%, rgba(187,247,208,0.18) 0, transparent 60%);
  opacity: 0.9;
  pointer-events: none;
}

/* Contenido hero */
.login-hero-content {
  position: relative;
  z-index: 2;
  max-width: 420px;
}

.login-hero-title {
  font-size: 2.1rem;
  font-weight: 700;
  margin-bottom: 14px;
  letter-spacing: 0.01em;
  text-shadow: 0 1px 2px rgba(0,0,0,0.20);
}

.login-hero-title span {
  display: block;
  color: #fefce8;
}

.login-hero-text {
  font-size: 0.98rem;
  color: #E5E7EB;
  margin-bottom: 20px;
  line-height: 1.6;
  text-shadow: 0 1px 2px rgba(0,0,0,0.20);
}

.login-hero-list {
  list-style: none;
  padding: 0;
  margin: 0 0 22px 0;
}

.login-hero-list li {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.95rem;
  margin-bottom: 8px;
  color: #F9FAFB;
  text-shadow: 0 1px 2px rgba(0,0,0,0.20);
}

.login-hero-list i {
  font-size: 1.1rem;
  color: #FEF3C7;
}

/* Botón “Si lo tengo, vecina” con efecto glass */
.login-hero-badge .badge-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 18px;
  border-radius: 999px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.28);
  color: #F9FAFB;
  font-size: 0.86rem;
  font-weight: 500;
  backdrop-filter: blur(4px);
  box-shadow: 0 8px 20px rgba(15,23,42,0.25);
}

/* --------------------------
   Panel derecho (Login)
-------------------------- */
.login-panel {
  flex: 0.9;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  padding: 32px 40px 28px;
}

.login-panel-header {
  margin-bottom: 16px;
  position: relative;
}

/* Spotlight suave bajo el logo */
.login-panel-header::before {
  content: "";
  position: absolute;
  width: 120px;
  height: 120px;
  top: -20px;
  left: 50%;
  transform: translateX(-50%);
  background: radial-gradient(circle, rgba(22,163,74,0.10), transparent 70%);
  z-index: -1;
}

.login-logo {
  max-height: 80px;
}

.login-panel-title {
  font-size: 1.45rem;
  font-weight: 700;
  color: var(--ev-verde-oscuro);
  margin-bottom: 4px;
}

.login-panel-subtitle {
  font-size: 0.88rem;
  color: var(--ev-gris-500);
  margin-bottom: 0;
}

.login-panel-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding-top: 8px;
  padding-bottom: 12px;
}

/* --------------------------
   Inputs e iconos
-------------------------- */
.login-form .input-icon {
  position: absolute;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 1rem;
}

.login-form input.form-control {
  padding-left: 38px;
  border-radius: 10px;
  border: 1px solid var(--ev-verde-claro);
  font-size: 0.95rem;
  transition: all 0.18s ease-out;
  box-shadow: 0 0 0 0 rgba(22,163,74,0);
}

.login-form input.form-control::placeholder {
  color: #79808c;
  font-size: 0.93rem;
}

.login-form input.form-control:focus {
  border-color: var(--ev-verde);
  box-shadow: 0 0 0 3px rgba(22,163,74,0.24);
  outline: none;
}

/* Más respiración entre campos */
.login-form .mb-3 {
  margin-bottom: 1.15rem !important;
}

.login-form .mb-2 {
  margin-bottom: 1.05rem !important;
}

/* Recordarme + link olvidar contraseña */
.login-remember-row .form-check-input {
  border-radius: 4px;
}

.login-remember-row .form-check-input:checked {
  background-color: var(--ev-verde);
  border-color: var(--ev-verde);
}

.login-link-forgot {
  font-size: 0.88rem;
  color: var(--ev-verde-oscuro);
  text-decoration: none;
}

.login-link-forgot:hover {
  color: var(--ev-verde);
  text-decoration: underline;
}

/* --------------------------
   Botón principal
-------------------------- */
.btn-login {
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border: none;
  color: #ffffff;
  border-radius: 12px;
  font-size: 1rem;
  box-shadow: 0 12px 26px rgba(234,124,18,0.35);
  transition: all 0.2s ease;
}

.btn-login:hover {
  background: linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C);
  color: #ffffff;
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(234,124,18,0.48);
}

.btn-login:active {
  transform: translateY(0);
  box-shadow: 0 6px 16px rgba(234,124,18,0.30);
}

/* Botón “Crear cuenta” */
.login-actions {
  margin-top: 12px;
}

.login-actions-text {
  font-size: 0.9rem;
  color: var(--ev-gris-600);
}

.btn-outline-register {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 20px;
  border-radius: 999px;
  border: 1.5px solid var(--ev-verde);
  background-color: rgba(255,255,255,0.6);
  color: var(--ev-verde-oscuro);
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.18s ease;
  backdrop-filter: blur(6px);
}

.btn-outline-register:hover {
  background-color: #ECFDF5;
  color: var(--ev-verde);
}

/* Footer panel derecho */
.login-panel-footer small {
  font-size: 0.78rem;
  color: #9ca3af;
}

/* --------------------------
   Spinner
-------------------------- */
.spinner-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255,255,255,0.82);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  display: none;
}

.spinner {
  border: 4px solid #D1FAE5;
  border-top: 4px solid var(--ev-verde-oscuro);
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* --------------------------
   Modales
-------------------------- */
.modal-content {
  border-radius: 12px;
  border: none;
}

.modal-header.bg-success {
  background: linear-gradient(135deg, var(--ev-verde-oscuro) 0%, var(--ev-verde-oscuro) 100%);
}

.modal-footer .btn-outline-secondary:hover {
  background-color: #4B5563;
  color: #ffffff;
}

/* --------------------------
   Responsividad
-------------------------- */
@media (max-width: 992px) {
  .login-shell {
    max-width: 880px;
  }
}

@media (max-width: 768px) {
  body.login-body {
    padding: 16px;
    background-image: none; /* en móvil aún más limpio */
  }

  .login-shell {
    flex-direction: column;
    max-width: 480px;
    min-height: 0;
  }

  .login-hero {
    padding: 32px 24px 24px;
  }

  .login-panel {
    padding: 24px 22px 20px;
  }
}

@media (max-width: 576px) {
  .login-hero {
    padding: 26px 20px 18px;
  }

  .login-hero-title {
    font-size: 1.7rem;
  }

  .login-panel-title {
    font-size: 1.25rem;
  }

  .login-shell {
    border-radius: 20px;
  }
}
</style>
