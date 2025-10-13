
<!-- login.estilo.php -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

<style>
  /* 🌿 --- Fondo general --- */
  body {
    background: radial-gradient(circle at 30% 20%, #FFF9F0 0%, #FFFFFF 50%, #F4FBF5 100%);
    font-family: 'Poppins', 'Inter', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0D0D0D;
  }
  

  /* 🌿 --- Tarjeta principal --- */
  .login-card {
    background-color: #ffffff;
    border-radius: 20px;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
  }
  .login-card:hover {
    transform: translateY(-2px);
  }

  /* 🌿 --- Encabezado --- */
  .login-card .card-header {
    background: transparent;
    border: none;
    margin-bottom: 1rem;
  }
  .login-card img {
    max-width: 350px;
    margin-bottom: 0.8rem;
  }
  .login-card h5 {
    font-weight: 600;
    color: #0F592F;
  }
  .login-card small {
    color: #6B7280;
  }

  /* 🌿 --- Inputs e íconos --- */
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
    border-color: #0F592F;
    box-shadow: 0 0 0 2px rgba(15, 89, 47, 0.25);
  }

  /* 🌿 --- Botones --- */
  .btn-success {
    background-color: #E4691B;
    border: none;
    transition: all 0.3s ease;
  }
  .btn-success:hover {
    background-color: #CC6018;
  }
  .btn-outline-secondary:hover {
    background-color: #494F5B;
  }

  /* 🌿 --- Enlaces --- */
  .text-center a {
    color: #0F592F;
    transition: color 0.3s ease;
  }
  .text-center a:hover {
    color: #E4691B;
  }

  /* 🌿 --- Footer --- */
  .login-footer {
    background: transparent;
    border-top: none;
    color: #494F5B;
    font-size: 0.85rem;
    margin-top: 0.5rem;
  }

  /* 🌿 --- Modales --- */
  .modal-content {
    border-radius: 15px;
  }
  .modal-header.bg-success {
    background-color: #0F592F !important;
  }
  .modal-header h5 {
    font-weight: 600;
  }

  /* 🌿 --- Spinner --- */
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
    border-top: 4px solid #0F592F;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    animation: spin 0.8s linear infinite;
  }
  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  /* 🌿 --- Responsive --- */
  @media (max-width: 768px) {
    .login-card {
      padding: 1.5rem;
    }
    .login-card img {
      max-width: 130px;
    }
  }
</style>
