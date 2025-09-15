<!-- Sidebar Styles -->
<style>
  .app-sidebar {
    width: 250px;
    min-height: 100vh;
  }

  /* Links base */
  .app-sidebar .nav-link {
    color: #e5e7eb; /* gris claro */
    transition: all 0.2s ease-in-out;
    border-radius: .375rem;
    margin: 2px 8px;
    font-size: 0.95rem;
  }

  /* Hover en menú principal */
  .app-sidebar > .sidebar-wrapper .nav > .nav-item > .nav-link:hover {
    background-color: #ff6b00;
    color: #fff;
    font-weight: 600;
  }

  /* Hover en submenús */
  .app-sidebar .nav-treeview .nav-link:hover {
    background-color: rgba(255, 107, 0, 0.1); /* anaranjado suave */
    color: #ff6b00;
    border-radius: .375rem;
    font-weight: 500;
  }

  /* Íconos y flechas */
  .app-sidebar .nav-icon {
    font-size: 1.1rem;
  }
  .app-sidebar .bi-chevron-down {
    font-size: 0.8rem;
  }
</style>