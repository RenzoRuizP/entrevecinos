<!-- ===============================
     ESTILOS BASE PARA LOGIN ENTRE VECINOS
     =============================== -->

<!-- 🔹 Tipografía profesional (Poppins) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.3/index.min.css">

<!-- 🔹 Bootstrap 5 (CSS) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ===============================
     ESTILO PERSONALIZADO LOGIN
     =============================== -->
<style>
/* --------------------------
   Fondo general / Layout
-------------------------- */
body.login-body {
  min-height: 100vh;
  margin: 0;
  padding: 24px;
  background: linear-gradient(135deg, #f9fafb 0%, #f0fdf4 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
  color: #111827;
}

/* --------------------------
   Tarjeta principal de login
-------------------------- */
.login-card {
  width: 100%;
  max-width: 420px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  box-shadow: 0 12px 40px rgba(15, 23, 42, 0.10);
  text-align: center;
}

/* Logo y textos encabezado */
.login-logo {
  max-height: 110px;
}

.login-card h4 {
  color: #0F592F;
  font-weight: 700;
}

.login-subtitle {
  font-size: 0.92rem;
  color: #6b7280;
}

/* --------------------------
   Inputs y iconos
-------------------------- */
.input-icon {
  position: absolute;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 1rem;
}

input.form-control {
  padding-left: 38px;
  border-radius: 10px;
  border: 1px solid #d1fae5;
  font-size: 0.95rem;
  transition: all 0.18s ease-out;
}

input.form-control::placeholder {
  color: #9ca3af;
  font-size: 0.93rem;
}

input.form-control:focus {
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.25);
  outline: none;
}

/* --------------------------
   Botón principal
-------------------------- */
.btn-login {
  background: linear-gradient(135deg, #D97706, #EA7C12);
  border: none;
  color: #ffffff;
  border-radius: 10px;
  font-size: 0.98rem;
  box-shadow: 0 8px 20px rgba(217, 119, 6, 0.35);
  transition: all 0.2s ease;
}

.btn-login:hover {
  background: linear-gradient(135deg, #C46B05, #D46F0F);
  color: #ffffff;
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(217, 119, 6, 0.45);
}

.btn-login:active {
  transform: translateY(0);
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.30);
}

/* --------------------------
   Links secundarios
-------------------------- */
.login-actions a {
  font-size: 0.93rem;
}

.login-link-forgot {
  color: #15803d;
}

.login-link-forgot:hover {
  color: #166534;
  text-decoration: underline;
}

.login-link-register {
  display: inline-block;
  margin-top: 4px;
  border-radius: 999px;
  border: 1px solid #16a34a;
  color: #15803d;
  padding: 6px 16px;
  font-size: 0.92rem;
  text-decoration: none;
}

.login-link-register:hover {
  background-color: #ecfdf5;
  color: #166534;
}

/* --------------------------
   Footer
-------------------------- */
.login-footer small {
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
  display: none; /* se muestra vía JS cuando se necesite */
}

.spinner {
  border: 4px solid #D1FAE5;
  border-top: 4px solid #0F592F;
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
  background: linear-gradient(135deg, #0F592F 0%, #0F592F 100%);
}

.modal-footer .btn-outline-secondary:hover {
  background-color: #494F5B;
  color: #ffffff;
}

/* --------------------------
   Responsividad
-------------------------- */
@media (max-width: 576px) {
  body.login-body {
    padding: 16px;
  }
  .login-card {
    max-width: 100%;
  }
}
</style>
