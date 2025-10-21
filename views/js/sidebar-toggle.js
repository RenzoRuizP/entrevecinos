
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const toggleBtn = document.getElementById("btnToggleSidebar");
  const backdrop = document.getElementById("sidebar-backdrop");

  if (!sidebar || !toggleBtn || !backdrop) {
    console.warn("sidebar-toggle.js: Elementos no encontrados");
    return;
  }

  const openSidebar = () => {
    sidebar.classList.add("active");
    backdrop.classList.add("show");
    document.body.style.overflow = "hidden";
  };

  const closeSidebar = () => {
    sidebar.classList.remove("active");
    backdrop.classList.remove("show");
    document.body.style.overflow = "";
  };

  toggleBtn.addEventListener("click", (e) => {
    e.preventDefault();
    const isOpen = sidebar.classList.contains("active");
    isOpen ? closeSidebar() : openSidebar();
  });

  backdrop.addEventListener("click", closeSidebar);

  window.addEventListener("resize", () => {
    if (window.innerWidth >= 992) closeSidebar();
  });
});
