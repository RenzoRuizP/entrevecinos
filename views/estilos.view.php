<?php
// ✅ estilos.view.php — versión optimizada y 100% responsiva
?>
<!-- ============================================================
     Estilos base optimizados para Entre Vecinos
     Compatible con AdminLTE 4 + Bootstrap 5 + tus estilos UX/UI personalizados
============================================================== -->

<!-- Tipografía base -->
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.8/index.css"
  crossorigin="anonymous"
  media="print"
  onload="this.media='all'"
/>

<!-- Librerías principales -->
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
  crossorigin="anonymous"
/>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
  crossorigin="anonymous"
/>

<!-- Plugin gráficos -->
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
  crossorigin="anonymous"
/>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
  crossorigin="anonymous"
/>

<!-- AdminLTE principal -->
<link rel="stylesheet" href="<?= BASE_URL ?>/resources/util/lte4/dist/css/adminlte.css" />

<style>
  /* 🌿 Paleta base */
  :root {
    --verde-principal: #115C41;
    --verde-oscuro: #0A422D;
    --verde-claro: #18A869;
    --blanco: #FFFFFF;
    --gris-claro: #F4F6F9;
    --gris-texto: #6C757D;
  }

  html, body {
    height: 100%;
    margin: 0;
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    background-color: var(--gris-claro);
    color: #333;
  }

  /* Layout base */
  .wrapper {
    display: flex;
    width: 100%;
    min-height: 100vh;
    overflow-x: hidden;
    background-color: var(--gris-claro);
  }

  /* 🔹 Contenido principal */
  .content-wrapper {
    flex-grow: 1;
    margin-left: 260px;
    transition: margin-left 0.3s ease-in-out;
    padding: 1rem;
  }

  @media (max-width: 991.98px) {
    .content-wrapper {
      margin-left: 0;
    }
  }

  /* 🔹 Estilos del menú superior */
  .main-header {
    background-color: var(--verde-principal);
    color: var(--blanco);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    position: sticky;
    top: 0;
    z-index: 1040;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }

  .main-header .navbar-brand {
    font-weight: 600;
    letter-spacing: 0.5px;
    color: var(--blanco);
  }

  .main-header .navbar-toggler {
    background: none;
    border: none;
    color: var(--blanco);
    font-size: 1.4rem;
    cursor: pointer;
  }

  .main-header .navbar-toggler:focus {
    outline: none;
  }

  /* 🔹 Botón de perfil o cerrar sesión */
  .navbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .navbar-right button {
    background-color: var(--verde-claro);
    color: var(--blanco);
    border: none;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s;
  }

  .navbar-right button:hover {
    background-color: #0E7B53;
  }

  /* 🔹 Backdrop del sidebar */
  #sidebar-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(17, 92, 65, 0.4);
    z-index: 1025;
    backdrop-filter: blur(2px);
  }

  #sidebar-backdrop.active {
    display: block;
  }

  /* 🔹 Scrollbar general */
  ::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }

  ::-webkit-scrollbar-thumb {
    background-color: rgba(17, 92, 65, 0.4);
    border-radius: 10px;
  }

  ::-webkit-scrollbar-track {
    background: transparent;
  }

  /* 🔹 Tarjetas de contenido */
  .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    background-color: var(--blanco);
  }

  .card-header {
    background-color: var(--verde-principal);
    color: var(--blanco);
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
  }

  /* 🔹 Botones principales */
  .btn-primary {
    background-color: var(--verde-principal);
    border: none;
  }

  .btn-primary:hover {
    background-color: var(--verde-claro);
  }

  /* 🔹 Enlaces */
  a {
    color: var(--verde-principal);
    text-decoration: none;
  }

  a:hover {
    color: var(--verde-claro);
  }

  /* 🔹 Títulos */
  h1, h2, h3, h4, h5 {
    color: var(--verde-oscuro);
    font-weight: 600;
  }

  /* 🔹 Responsivo y mejoras de UX */
  .fade-in {
    animation: fadeIn 0.4s ease-in-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .cursor-pointer {
    cursor: pointer;
  }

  /* 🔹 SweetAlert2 personalizado */
  .swal2-title {
    font-family: 'Poppins', sans-serif !important;
  }

  .swal2-styled.swal2-confirm {
    background-color: var(--verde-principal) !important;
  }

  /* 🔹 Ajuste para pantallas pequeñas */
  @media (max-width: 767px) {
    .main-header .navbar-right button {
      font-size: 0.85rem;
      padding: 0.35rem 0.6rem;
    }
  }
</style>

