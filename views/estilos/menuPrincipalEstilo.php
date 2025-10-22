<style>
  /* =========================================================
     🎨 ESTILOS BASE DEL MENÚ PRINCIPAL — ENTRE VECINOS
     ========================================================= */

  /* Contenedor general de la vista */
  .wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: #f6f8fa;
  }

  /* Contenedor que agrupa el menú lateral y el contenido principal */
  .main-layout {
    flex-grow: 1;
    display: flex;
    min-height: calc(100vh - 60px); /* resta la altura del menú superior */
    overflow: hidden;
  }

  /* Menú lateral izquierdo */
  .sidebar {
    width: 250px; /* ajusta al ancho real de tu menú */
    background-color: #0F592F;
    flex-shrink: 0;
    z-index: 1000;
  }

  /* Contenedor principal del contenido */
  .content-wrapper {
    flex-grow: 1;
    padding: 2rem;
    background-color: #f6f8fa;
    overflow-y: auto;
  }

  /* Barra superior */
  .navbar-top {
    height: 60px;
    background-color: #0F592F;
    color: #fff;
    display: flex;
    align-items: center;
    padding: 0 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 1100;
  }

  /* Para mantener consistencia visual */
  .navbar-top .user-info {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  /* =========================================
     🌟 Tarjetas con efecto hover elegante
     ========================================= */
  .small-box {
    border-radius: 1rem;
    transition: all 0.25s ease;
    transform: translateY(0);
    background-color: #fff;
    border: 1px solid #eee;
  }

  .small-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    filter: brightness(1.05);
  }

  .small-box svg {
    transition: transform 0.3s ease;
  }

  .small-box:hover svg {
    transform: scale(1.1);
  }

  @media (max-width: 992px) {
    .main-layout {
      flex-direction: column;
    }

    .sidebar {
      width: 100%;
      height: auto;
    }

    .content-wrapper {
      padding: 1.2rem;
    }

    .small-box {
      margin-bottom: 1rem;
    }
  }
</style>
