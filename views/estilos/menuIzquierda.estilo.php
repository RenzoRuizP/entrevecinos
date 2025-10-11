

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

/* ======= BASE SIDEBAR ======= */
.sidebar {
  background-color: #1f2937; /* gris oscuro elegante */
  color: #e0f2f1;
  width: 250px;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  font-family: 'Inter', sans-serif;
  transition: all 0.3s ease;
  box-shadow: 4px 0 12px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  position: relative;
}

/* ======= HEADER / LOGO ======= */
.sidebar-header {
  padding: 1.5rem 1rem;
  text-align: center;
  background: linear-gradient(135deg, #22c55e, #15803d);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.sidebar-header img {
  width: 90px;
  height: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.sidebar-header h5 {
  color: #fff;
  font-weight: 600;
  margin-top: 0.75rem;
  letter-spacing: 0.5px;
}

/* ======= MENÚ PRINCIPAL ======= */
.sidebar-menu {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.sidebar-menu ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

/* ======= LINKS ======= */
.sidebar-menu li {
  margin-bottom: 4px;
}

.sidebar-menu a {
  display: flex;
  align-items: center;
  padding: 0.7rem 1.25rem;
  color: #cbd5e1;
  font-weight: 500;
  font-size: 0.95rem;
  border-left: 4px solid transparent;
  transition: all 0.25s ease;
  text-decoration: none;
  border-radius: 0 6px 6px 0;
}

.sidebar-menu a i {
  font-size: 1.2rem;
  margin-right: 10px;
  color: #22c55e;
  transition: color 0.3s ease, transform 0.2s ease;
}

/* ======= HOVER / ACTIVO ======= */
.sidebar-menu a:hover {
  background-color: rgba(34, 197, 94, 0.15);
  border-left: 4px solid #22c55e;
  color: #ffffff;
  transform: translateX(2px);
}

.sidebar-menu a:hover i {
  color: #22c55e;
  transform: scale(1.1);
}

.sidebar-menu a.active {
  background-color: rgba(34, 197, 94, 0.25);
  border-left: 4px solid #22c55e;
  color: #fff;
  font-weight: 600;
}

.sidebar-menu a.active i {
  color: #22c55e;
}

/* ======= SUBMENÚ ======= */
.submenu {
  padding-left: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-left: 2px solid rgba(255, 255, 255, 0.08);
}

.submenu a {
  padding: 0.6rem 1.25rem;
  font-size: 0.9rem;
  color: #a7f3d0;
  border-radius: 0 4px 4px 0;
}

.submenu a:hover {
  background-color: rgba(34, 197, 94, 0.15);
  border-left: 3px solid #22c55e;
  color: #ffffff;
}

/* ======= FOOTER ======= */
.sidebar-footer {
  background: #111827;
  text-align: center;
  padding: 1rem;
  color: #9ca3af;
  font-size: 0.85rem;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* ======= SCROLLBAR ======= */
.sidebar-menu::-webkit-scrollbar {
  width: 6px;
}

.sidebar-menu::-webkit-scrollbar-thumb {
  background: #22c55e;
  border-radius: 10px;
}

.sidebar-menu::-webkit-scrollbar-thumb:hover {
  background: #16a34a;
}

.sidebar-menu::-webkit-scrollbar-track {
  background: transparent;
}

/* ======= ANIMACIONES ======= */
.sidebar-menu a,
.submenu a {
  transition: all 0.3s ease;
}

.submenu {
  transition: all 0.3s ease;
}

/* ======= RESPONSIVE ======= */
@media (max-width: 992px) {
  .sidebar {
    position: fixed;
    left: -260px;
    top: 0;
    height: 100%;
    z-index: 2000;
  }

  .sidebar.active {
    left: 0;
  }
}
</style>
