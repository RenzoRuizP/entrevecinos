
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
  /* ======= BASE ======= */
  .app-navbar {
    background: linear-gradient(135deg, #4CAF50, #2E7D32);
    height: 60px;
    display: flex;
    align-items: center;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s ease;
    z-index: 1050;
  }

  /* ======= NAV LINKS ======= */
  .nav-link-main {
    color: #ffffff !important;
    transition: color 0.3s ease;
  }

  .nav-link-main:hover {
    color: #C8E6C9 !important;
  }

  /* ======= TOGGLE SIDEBAR ======= */
  .toggle-sidebar {
    color: #ffffff !important;
    transition: transform 0.2s ease;
  }

  .toggle-sidebar:hover {
    transform: scale(1.1);
    color: #C8E6C9 !important;
  }

  /* ======= USER MENU ======= */
  .user-name {
    color: #ffffff !important;
  }

  .user-menu .dropdown-menu {
    border: none;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    overflow: hidden;
    animation: fadeInDown 0.25s ease;
  }

  .user-menu .user-header {
    background: linear-gradient(135deg, #43A047, #2E7D32);
    border-bottom: 1px solid rgba(255,255,255,0.15);
  }

  /* ======= BOTONES ======= */
  .user-menu .btn {
    border-radius: 0.6rem;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .user-menu .btn-outline-success {
    border-color: #4CAF50;
    color: #4CAF50;
  }

  .user-menu .btn-outline-success:hover {
    background-color: #4CAF50;
    color: #fff;
  }

  .user-menu .btn-danger:hover {
    background-color: #C62828;
  }

  /* ======= ANIMACIONES ======= */
  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* ======= RESPONSIVE ======= */
  @media (max-width: 768px) {
    .user-name {
      display: none !important;
    }
  }
  
</style>