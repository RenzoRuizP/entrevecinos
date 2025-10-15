<!-- login.estilo.php -->
<!-- 🔹 Tipografía -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<!-- 🔹 Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- 🔹 Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- 🔹 SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* =====================================================
   🌿 ENTRE VECINOS - LOGIN PROFESIONAL (UX/UI)
===================================================== */

/* --- Fondo general --- */
body {
  background: radial-gradient(circle at 20% 20%, #FFF8EC 0%, #FFFDF8 40%, #F5FBF6 100%);
  font-family: 'Poppins', 'Inter', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0D0D0D;
}

/* --- Tarjeta principal --- */
.login-card {
  background-color: #ffffff;
  border-radius: 18px;
  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
}
.login-card:hover {
  transform: translateY(-3px);
}

/* --- Logo y encabezado --- */
.login-card .card-header {
  background: transparent;
  border: none;
}
.login-card img {
  max-width: 280px;
  margin-bottom: 0.8rem;
}
.login-card h5 {
  font-weight: 600;
  color: #115C41;
  margin-bottom: 0.3rem;
}
.login-card small {
  color: #6B7280;
  font-size: 0.9rem;
}

/* --- Inputs e íconos --- */
.input-icon {
  position: absolute;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  color: #9CA3AF;
}
input.form-control {
  padding-left: 38px;
  border-radius: 10px;
  border: 1px solid #73E7A6;
  transition: all 0.2s;
}
input.form-control:focus {
  border-color: #157251;
  box-shadow: 0 0 0 2px rgba(7, 140, 3, 0.25);
}

/* --- Botones --- */
.btn-login {
  background-color: #D96704;
  border: none;
  color: #fff;
  transition: all 0.3s ease;
}
.btn-login:hover {
  background-color: #E4691B;
  color: #fff;
  transform: translateY(-2px);
}
.btn-outline-secondary:hover {
  background-color: #494F5B;
  color: #fff;
}

/* --- Enlaces --- */
.text-center a {
  color: #115C41;
  transition: color 0.3s ease;
}
.text-center a:hover {
  color: #D96704;
}

/* --- Footer --- */
.login-footer {
  background: transparent;
  border-top: none;
  color: #6B7280;
  font-size: 0.85rem;
  margin-top: 1rem;
}

/* --- Spinner --- */
.spinner-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255,255,255,0.85);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  display: none;
}
.spinner {
  border: 4px solid #D1FAE5;
  border-top: 4px solid #157251;
  border-radius: 50%;
  width: 45px;
  height: 45px;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* --- Modales --- */
.modal-content {
  border-radius: 15px;
  border: none;
}
.modal-header.bg-success {
 
  color: #115C41;
}
.modal-header h5 {
  font-weight: 600;
}

/* --- Responsive --- */
@media (max-width: 768px) {
  .login-card {
    padding: 1.5rem;
  }
  .login-card img {
    max-width: 200px;
  }
}
</style>
