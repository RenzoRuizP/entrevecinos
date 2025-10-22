<style>
/* ============================================================
   🌿 Estilos para el menú superior (navbar)
   Entre Vecinos - versión optimizada UX/UI
============================================================ */

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
}

/* 🔹 Botón hamburguesa */
.navbar-toggler {
  border: none;
  background: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
}
.navbar-toggler:focus {
  outline: none;
}

/* 🔹 Usuario */
.user-menu .nav-link {
  color: white;
  display: flex;
  align-items: center;
  font-weight: 500;
  gap: 0.5rem;
}

.user-menu .dropdown-menu {
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  overflow: hidden;
}

/* 🔹 Cabecera del dropdown */
.user-menu .dropdown-menu li.bg-success {
  background-color: #0F592F !important;
}

/* 🔹 Botones de perfil y cerrar sesión */
.user-menu .btn {
  font-weight: 500;
  border-radius: 8px;
  padding: 0.35rem 0.75rem;
}

.user-menu .btn-outline-success {
  border-color: #0F592F;
  color: #0F592F;
}
.user-menu .btn-outline-success:hover {
  background-color: #0F592F;
  color: white;
}

.user-menu .btn-danger {
  background-color: #BF3604;
  border: none;
}
.user-menu .btn-danger:hover {
  background-color: #A12E03;
}

/* 🔹 Ajustes responsivos */
@media (max-width: 768px) {
  .app-header.navbar {
    padding: 0.5rem 0.75rem;
  }

  .user-menu span {
    display: none;
  }
}

/* 🔹 Imagen de usuario */
.user-menu img {
  width: 35px;
  height: 35px;
  object-fit: cover;
  border-radius: 50%;
}

.user-menu .dropdown-menu img {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 50%;
}
</style>
