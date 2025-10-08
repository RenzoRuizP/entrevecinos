<!-- login.estilo.php -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

<style>
  /* ======= BASE GENERAL ======= */
  body {
    background: linear-gradient(135deg, #4CAF50, #2E7D32);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    margin: 0;
  }

  /* ======= TARJETA LOGIN ======= */
  .login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    animation: fadeInUp 0.6s ease-out;
    transition: transform 0.3s ease;
  }

  .login-card:hover {
    transform: translateY(-3px);
  }

  /* ======= HEADER ======= */
  .login-card .card-header {
    background: #4CAF50;
    color: #fff;
    text-align: center;
    padding: 1.8rem 1rem;
  }

  .login-card .card-header img {
    max-height: 120px;
    margin-bottom: 0.5rem;
  }

  .login-card .card-header h5 {
    font-weight: 600;
  }

  /* ======= FORMULARIO ======= */
  .login-card .card-body {
    padding: 2rem;
  }

  .login-card .form-control {
    border-radius: 0.75rem;
    padding-left: 2.5rem;
    height: 48px;
    border: 1px solid #dcdcdc;
    transition: border 0.2s ease, box-shadow 0.2s ease;
  }

  .login-card .form-control:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76,175,80,0.4);
  }

  .login-card .input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
  }

  .login-card button {
    border-radius: 0.75rem;
    height: 48px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .login-card button:hover {
    background: #43a047;
    box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
  }

  /* ======= LINKS Y FOOTER ======= */
  .login-card a {
    color: #388e3c;
    font-weight: 500;
  }

  .login-card a:hover {
    color: #1b5e20;
    text-decoration: underline;
  }

  .login-footer {
    font-size: 0.9rem;
    color: #6c757d;
    background: rgba(255,255,255,0.85);
    padding: 1rem;
  }

  /* ======= ANIMACIONES ======= */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(25px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* ======= RESPONSIVE ======= */
  @media (max-width: 576px) {
    .login-card {
      width: 90%;
    }
    .login-card .card-body {
      padding: 1.5rem;
    }
  }

  /* ======= SPINNER DE CARGA ======= */
  .spinner-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.85);
    justify-content: center;
    align-items: center;
    z-index: 9999;
  }

  .spinner {
    border: 6px solid #e0e0e0;
    border-top: 6px solid #4CAF50;
    border-radius: 50%;
    width: 65px;
    height: 65px;
    animation: spin 0.9s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>
