
<style>
/* 🎨 Estilos generales */
  body {
    background-color: #f7f9f8;
    color: #333;
    font-family: "Poppins", sans-serif;
  }

  /* --- 🔹 Contenedor principal --- */
  #contenido-principal {
    padding: 2rem;
    background-color: #ffffff;
    border-radius: 1rem;
    min-height: 75vh;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
  }

  /* --- 🔹 Enlaces del menú lateral --- */
  .sidebar-wrapper {
    background: linear-gradient(180deg, #0d9b6b, #0a6d50);
    color: #fff;
    min-height: 100vh;
    padding-top: 1rem;
    border-top-right-radius: 1rem;
    border-bottom-right-radius: 1rem;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  }

  .sidebar-wrapper .submenu-link {
    display: block;
    padding: 0.75rem 1rem;
    color: #e7f6ef;
    text-decoration: none;
    border-radius: 0.5rem;
    margin: 0.25rem 0.75rem;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
  }

  .sidebar-wrapper .submenu-link:hover {
    background-color: rgba(255, 255, 255, 0.15);
    color: #fff;
    transform: translateX(3px);
  }

  /* Enlace activo */
  .sidebar-wrapper .submenu-link.active {
    background-color: #ffffff;
    color: #0b8059;
    font-weight: 600;
    box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
  }

  /* --- 🔹 Cabecera superior --- */
  .navbar-top {
    background-color: #ffffff;
    border-bottom: 2px solid #e2e8f0;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05);
    padding: 0.75rem 1.5rem;
  }

  .navbar-top .navbar-brand {
    font-weight: 600;
    color: #0d9b6b;
    font-size: 1.25rem;
    letter-spacing: 0.5px;
  }

  .navbar-top .user-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .navbar-top .user-info img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #0d9b6b;
  }

  /* --- 🔹 Spinner de carga --- */
  .spinner-border.text-success {
    width: 3rem;
    height: 3rem;
    color: #0d9b6b !important;
  }

  /* --- 🔹 Alertas dentro del contenido --- */
  .alert-danger {
    border-left: 4px solid #e74c3c;
    background: #fef2f2;
  }

  /* --- 🔹 Animaciones suaves --- */
  .fade-in {
    animation: fadeIn 0.4s ease-in-out forwards;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* --- 🔹 Responsividad --- */
  @media (max-width: 768px) {
    #contenido-principal {
      padding: 1rem;
      border-radius: 0.5rem;
    }

    .sidebar-wrapper {
      border-radius: 0;
    }
  }


</style>