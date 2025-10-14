<?php
// estilos.view.php
?>

<!-- ============================================================
     Estilos base optimizados para Entre Vecinos
     Compatible con AdminLTE 4 + Bootstrap 5 + tus estilos UX/UI personalizados
============================================================== -->


<!-- Tipografía base (coherente con login y menú principal) -->
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

<!-- Bootstrap adicional (solo si AdminLTE no lo incluye ya) -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->

<!-- ============================================================
     Estilos personalizados de Entre Vecinos
     Cargar siempre al final para sobrescribir los temas base
============================================================== -->
<!--<link rel="stylesheet" href="<?= BASE_URL ?>views/estilos/MenuPrincipal.estilo.php"> -->

<style>
    /* 🎨 Fondo en armonía con el logo */
    body {
      background: linear-gradient(135deg, #FFF8F0 0%, #FFFFFF 100%);
      font-family: 'Poppins', 'Inter', sans-serif;
      min-height: 100vh;
    }

    /* 🎨 Estilo de tarjetas pequeñas */
    .small-box {
      background-color: #FFF9F0;
      color: #0F592F;
      border: 1px solid #E5E7EB;
      border-radius: 1rem;
      transition: all 0.3s ease-in-out;
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
    }

    .small-box:hover {
      background-color: #F0FDF4; /* un verde muy suave de tu paleta */
      transform: translateY(-4px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }


    .small-box-icon {
      position: absolute;
      top: 0.5rem;
      right: 1rem;
      font-size: 3rem;
      opacity: 0.2;
    }

    .small-box-footer {
      background: rgba(15, 89, 47, 0.05);
      color: #0F592F;
      border-top: 1px solid #E5E7EB;
      transition: background 0.3s;
    }
    .small-box-footer:hover {
      background: rgba(15, 89, 47, 0.1);
      text-decoration: none;
    }


    /* 🎨 Colores personalizados */
    .text-bg-success {
      background-color: #0F592F !important;
      color: white;
    }

    .text-bg-warning {
      background-color: #D96704 !important;
      color: #fff !important;
    }

    .callout-primary {
      border-left: 5px solid #0F592F;
      background: #F0FDF4;
      color: #0D0D0D;
      border-radius: 0.75rem;
      padding: 1rem;
    }

    footer.app-footer {
      background: #FFFFFF;
      border-top: 2px solid #0F592F;
      color: #0D0D0D;
    }

    footer a {
      color: #0F592F;
    }
  </style>
