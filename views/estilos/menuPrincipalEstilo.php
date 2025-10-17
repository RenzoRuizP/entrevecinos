<style>
/* Sidebar */
#sidebar {
  width: 260px;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  background-color: #115C41;
  transform: translateX(-100%);
  transition: transform 0.3s ease-in-out;
  z-index: 1050;
}
#sidebar.active {
  transform: translateX(0);
}

/* Backdrop */
#sidebar-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0,0,0,0.4);
  display: none;
  z-index: 1040;
}

/* Botón hamburguesa */
#btnToggleSidebar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  position: relative;
  background: transparent;
  border: none;
  color: white;
}

/* Sidebar scroll interno */
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
  background-color: rgba(255,255,255,0.2);
  border-radius: 3px;
}

/* Hover de links */
.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.15);
}

/* Submenús activos */
.submenu-link.active {
  background-color: rgba(255, 255, 255, 0.25);
  font-weight: bold;
}

/* Mostrar sidebar en desktop */
@media (min-width: 992px) {
  #sidebar {
    transform: translateX(0);
  }
  #sidebar-backdrop {
    display: none !important;
  }
}
</style>
