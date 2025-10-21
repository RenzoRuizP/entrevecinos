<style>

 /* Colores base */
  .app-sidebar {
    background-color: #0F592F !important; /* Verde institucional */
    color: #FFF5D9;
  }

  /* Enlaces principales */
  .app-sidebar .nav-link {
    color: #FFF5D9;
    font-weight: 600;
    border-radius: 0.375rem;
  }
  .app-sidebar .nav-link:hover,
  .app-sidebar .nav-link.active-menu {
    background-color: #F16C20;
    color: #ffffff !important;
  }
  .app-sidebar .nav-link .nav-icon {
    color: #FFF5D9;
  }
  .app-sidebar .nav-link.active-menu .nav-icon.icon-active {
    color: #FFF5D9;
  }
  .app-sidebar .nav-link:hover .nav-icon,
  .app-sidebar .nav-link.active-menu .nav-icon {
    color: #FFF5D9;
  }

  /* Icono flecha */
  .app-sidebar .nav-link .bi-chevron-down {
    transition: transform 0.3s ease;
  }
  .app-sidebar .nav-link[aria-expanded="true"] .bi-chevron-down {
    transform: rotate(180deg);
  }

  /* Submenus */
  .nav-treeview .nav-link {
    padding-left: 2.5rem;
    font-weight: 500;
    color: #FFF5D9;
    border-radius: 0.375rem;
  }
  .nav-treeview .nav-link:hover,
  .nav-treeview .nav-link.submenu-active {
    background-color: #F16C20;
    color: white !important;
  }
  .nav-treeview .nav-link .fas,
  .nav-treeview .nav-link .icon-sub-active {
    color: #FFF5D9;
  }
  .nav-treeview .nav-link.submenu-active .icon-sub-active {
    color: #FFF5D9;
  }


/* ✅ Estilo limpio sin scroll feo */
  #sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    overflow: hidden;
    transition: transform 0.3s ease-in-out;
    z-index: 1030;
  }

  #sidebar.active {
    transform: translateX(0);
  }

  /* Oculto en móvil por defecto */
  @media (max-width: 991.98px) {
    #sidebar {
      transform: translateX(-100%);
    }
  }

  /* Scroll elegante interno solo si hay overflow */
  .sidebar-wrapper {
    max-height: calc(100vh - 100px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #0A422D #115C41;
  }

  .sidebar-wrapper::-webkit-scrollbar {
    width: 6px;
  }

  .sidebar-wrapper::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
  }

  /* Hover efecto */
  .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.15);
  }

  /* Submenús activos */
  .submenu-link.active {
    background-color: rgba(255, 255, 255, 0.25);
    font-weight: bold;
  }
</style>