<style>
/* ============================================================
   🌿 Estilos para el menú superior (navbar)
   Entre Vecinos - versión mejorada y adaptada a móvil
============================================================ */

/* 🔹 Navbar general */
.app-header.navbar {
  background-color: #0F592F;
  color: white;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 1rem;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1040;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  overflow: visible !important;
}

/* 🔹 Botón hamburguesa lateral */
.navbar-toggler, 
#btnToggleSidebar {
  border: none;
  background: none;
  color: white;
  font-size: 1.6rem;
  cursor: pointer;
}
.navbar-toggler:focus,
#btnToggleSidebar:focus {
  outline: none;
}

/* 🔹 Usuario en la barra */
.user-menu .nav-link {
  color: white;
  display: flex;
  align-items: center;
  font-weight: 500;
  gap: 0.5rem;
  padding: 0;
}

.user-menu .nav-link img {
  width: 38px;
  height: 38px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid white;
  transition: transform 0.2s ease;
}
.user-menu .nav-link img:hover {
  transform: scale(1.05);
}

/* 🔹 Dropdown del usuario */
.user-menu .dropdown-menu {
  border: none;
  border-radius: 14px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.2);
  overflow: hidden;
  padding: 0;
  transition: all 0.25s ease;
}

/* Alineación del dropdown a la derecha en pantallas grandes */
@media (min-width: 992px) {
  .user-menu .dropdown-menu.dropdown-menu-end {
    right: 0 !important; /* correcto */
    left: auto !important; /* limpiar valores inválidos */
  }
}

/* 🔹 Cabecera del dropdown */
.user-menu .dropdown-menu li.bg-success {
  background-color: #0F592F !important;
}

/* 🔹 Imagen grande en el menú */
.user-menu .dropdown-menu img {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 50%;
}

/* 🔹 Botones dentro del menú */
.user-menu .btn {
  font-weight: 500;
  border-radius: 8px;
  padding: 0.35rem 0.75rem;
}

.user-menu .btn-outline-success {
  border-color: #0F592F;
  color: #0F592F;
  transition: all 0.2s ease;
}
.user-menu .btn-outline-success:hover {
  background-color: #0F592F;
  color: white;
}

.user-menu .btn-danger {
  background-color: #BF3604;
  border: none;
  transition: background 0.2s ease;
}
.user-menu .btn-danger:hover {
  background-color: #A12E03;
}

/* ============================================================
   📱 Estilos responsivos y móviles
============================================================ */
@media (max-width: 991.98px) {
  .app-header.navbar {
    padding: 0.5rem 0.75rem;
  }

  .user-menu span {
    display: none !important;
  }

  /* Centrar y ampliar el menú del usuario */
  .user-menu .dropdown-menu {
    position: fixed !important;   /* fijo respecto a la ventana */
    top: 70px !important;         /* un poco debajo de la barra */
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: 90% !important;
    border-radius: 1rem !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25) !important;
    z-index: 2000 !important;     /* encima de todo */
  }

  .user-menu .dropdown-menu img {
    width: 80px;
    height: 80px;
  }

  .user-menu .dropdown-menu p {
    font-size: 1rem;
  }
  .user-menu .dropdown-menu small {
    font-size: 0.9rem;
    opacity: 0.9;
  }
  .user-menu .btn {
    flex: 1;
    margin: 0 0.25rem;
  }
}

/* ============================================================
   ✨ Animaciones suaves
============================================================ */
.dropdown-menu.show {
  animation: fadeInUp 0.25s ease;
}
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
