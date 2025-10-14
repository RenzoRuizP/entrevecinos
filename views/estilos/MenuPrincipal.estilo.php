
<style>
/* --------------------------
   Paleta (brand)
---------------------------*/
:root{
  --verde-oscuro: #0F592F;
  --verde-claro:  #078C03;
  --naranja:      #D96704;
  --naranja-dark: #BF3604;
  --negro:        #0D0D0D;
  --vainilla:     #FFF9F0;
  --gris-200:     #E5E7EB;
  --text-dark:    #111827;
}

/* --------------------------
   Layout general (coherente con login)
---------------------------*/
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, var(--vainilla) 0%, #FFFFFF 50%);
  color: var(--text-dark);
  
}

/* --------------------------
   NAVBAR (superior)
---------------------------*/
.main-header .navbar {
  background: linear-gradient(90deg, rgba(15,89,47,1) 0%, rgba(7,140,3,0.95) 100%);
  border-bottom: 1px solid rgba(255,255,255,0.06);
  box-shadow: 0 4px 18px rgba(15,89,47,0.06);
}

/* Navbar: logo y items */
.main-header .navbar .navbar-brand,
.main-header .navbar .nav-link {
  color: #ffffff !important;
  font-weight: 600;
}
.main-header .navbar .nav-link:hover {
  color: var(--naranja) !important;
}

/* Navbar: botones (iconos) */
.main-header .navbar .nav-icon {
  color: rgba(255,255,255,0.95);
}
.main-header .navbar .nav-icon:hover {
  color: var(--naranja);
}

/* --------------------------
   SIDEBAR (izquierdo) - color sólido recomendado
---------------------------*/
.main-sidebar {
  background: linear-gradient(180deg, var(--verde-oscuro) 0%, var(--verde-claro) 100%);
  color: #fff;
  border-right: 0;
  box-shadow: 2px 0 18px rgba(0,0,0,0.08);
}

/* Sidebar logo / title */
.brand-link {
  background: transparent;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.brand-link .brand-image {
  filter: drop-shadow(0 2px 6px rgba(0,0,0,0.2));
}

/* Sidebar: links */
.main-sidebar .nav-sidebar .nav-link {
  color: rgba(255,255,255,0.92);
  padding: 12px 18px;
  border-radius: 8px;
  margin: 4px 8px;
  transition: background 0.18s, color 0.18s, transform 0.12s;
}
.main-sidebar .nav-sidebar .nav-link .nav-icon {
  color: rgba(255,255,255,0.95);
}
.main-sidebar .nav-sidebar .nav-link:hover {
  background: rgba(255,255,255,0.06);
  transform: translateX(4px);
  color: #fff;
}

/* Active / selected */
.main-sidebar .nav-sidebar .nav-item > .nav-link.active {
  background: linear-gradient(90deg, rgba(217,103,4,0.95), rgba(191,54,4,0.95));
  color: #fff !important;
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

/* Submenu items */
.nav-treeview .nav-link {
  padding-left: 36px;
  color: rgba(255,255,255,0.88);
  font-size: 0.95rem;
}

/* Badges en sidebar */
.main-sidebar .badge {
  background: var(--naranja);
  color: #fff;
  font-weight: 600;
  border-radius: 8px;
}

/* --------------------------
   TOPBAR / Breadcrumb / Page header
---------------------------*/
.content-wrapper .content-header {
  background: transparent;
  padding: 1rem 1.25rem;
}
.content-wrapper .content {
  padding: 1.25rem;
  background: transparent;
}

/* Page header title */
.content-header .container-fluid .row .col h1 {
  color: var(--negro);
  font-weight: 700;
}

/* --------------------------
   Cards y componentes - look coherente con login
---------------------------*/
.card {
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(16,24,32,0.06);
  border: 1px solid rgba(15,89,47,0.03);
}

.card .card-header {
  background: linear-gradient(90deg, rgba(15,89,47,0.05), rgba(7,140,3,0.02));
  font-weight: 600;
  color: var(--text-dark);
}

/* Buttons */
.btn-primary, .btn-success {
  background: var(--verde-oscuro);
  border-color: var(--verde-oscuro);
  color: #fff;
}
.btn-primary:hover, .btn-success:hover {
  background: var(--verde-claro);
  border-color: var(--verde-claro);
}
.btn-cta { /* call-to-action button */
  background: var(--naranja);
  border-color: var(--naranja);
  color: #fff;
}
.btn-cta:hover {
  background: var(--naranja-dark);
  border-color: var(--naranja-dark);
}

/* Links */
a {
  color: var(--verde-oscuro);
}
a:hover {
  color: var(--naranja);
}

/* --------------------------
   Responsive adjustments
---------------------------*/
@media (max-width: 768px) {
  .main-sidebar { width: 60px !important; } /* compact */
  .brand-link .brand-text { display: none; }
}

/* --------------------------
   Accessibility helpers
---------------------------*/
/* ensure contrast for important elements */
.btn-cta, .main-sidebar .nav-link.active, .main-header .navbar { outline-color: rgba(0,0,0,0.12); }

/* --------------------------
   Optional: soporte modo oscuro
---------------------------*/
@media (prefers-color-scheme: dark) {
  body {
    background: linear-gradient(135deg, #07180B 0%, #081810 100%);
    color: #E6F6E9;
  }
  .main-sidebar {
    background: linear-gradient(180deg, #063616 0%, #054815 100%);
  }
  .card {
    background: #072016;
    border: 1px solid rgba(255,255,255,0.03);
    box-shadow: none;
  }
  .btn-cta { filter: brightness(0.95); }
}

</style>
