<!-- ===============================
     ESTILOS BASE PARA LOGIN ENTRE VECINOS
     =============================== -->

<!-- 🔹 Tipografía profesional (Poppins) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.3/index.min.css">

<!-- 🔹 Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- 🔹 Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- 🔹 SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- ===============================
     ESTILO PERSONALIZADO LOGIN
     =============================== -->
<style>
/* --------------------------
   Fondo general
-------------------------- */
body.login-body {
  min-height: 100vh;
  background: linear-gradient(135deg, #f9fafb 0%, #f0fdf4 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Poppins', sans-serif;
  color: #111827;
}

/* --------------------------
   Tarjeta principal de login
-------------------------- */
.login-card {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid #e5e7eb;
  backdrop-filter: blur(12px);
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
  text-align: center;
  padding-top: 2rem;
}

.login-card h5 {
  color: #0F592F;
  font-weight: 600;
  margin-top: 0.5rem;
}

.login-card small {
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
}

input.form-control {
  padding-left: 36px;
  border-radius: 10px;
  border: 1px solid #d1fae5;
  transition: all 0.2s;
}

input.form-control:focus {
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34,197,94,0.25);
}

/* --------------------------
   Botón principal
-------------------------- */
.btn-login {
  background-color: #E4691B;
  border: none;
  color: #fff;
  transition: all 0.3s ease;
}

.btn-login:hover {
  background-color: #CC6018;
  color: #fff;
  transform: translateY(-2px);
}

/* --------------------------
   Footer
-------------------------- */
.login-footer {
  font-size: 0.9rem;
  color: #9ca3af;
  background: transparent;
  border-top: none;
}

/* --------------------------
   Spinner
-------------------------- */
.spinner-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255,255,255,0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  display: none;
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
}

/* --------------------------
   Responsividad
-------------------------- */
@media (max-width: 576px) {
  .login-card {
    width: 95%;
  }
}
</style>
