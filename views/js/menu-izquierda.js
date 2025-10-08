$(document).ready(function () {
  $.ajax({
    url: "../controllers/obtenerOpcionesMenuController.php",
    method: "POST",
    data: { codigo_cargo_usuario: CODIGO_CARGO },
    dataType: "json",
    success: function (menus) {
      let $nav = $("#navigation");
      $nav.empty();

      if (!menus || menus.length === 0) {
        $nav.append('<li class="nav-header">Sin opciones</li>');
        return;
      }

      menus.forEach(menu => {
        let $menuItem = $(`
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon ${menu.icono || 'bi bi-folder'}"></i>
              <p>
                ${menu.nombre}
                <i class="nav-arrow bi bi-chevron-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" id="submenu-${menu.codigo_menu}"></ul>
          </li>
        `);

        $nav.append(`<li class="nav-header">${menu.seccion || ''}</li>`);
        $nav.append($menuItem);

        // Cargar ítems del menú
        $.ajax({
          url: "../controllers/obtenerOpcionesMenuItemController.php",
          method: "POST",
          data: {
            codigo_cargo_usuario: CODIGO_CARGO,
            codigo_menu: menu.codigo_menu
          },
          dataType: "json",
          success: function (items) {
            let $submenu = $(`#submenu-${menu.codigo_menu}`);
            items.forEach(item => {
              let $subItem = $(`
                <li class="nav-item">
                  <a href="${item.archivo}" class="nav-link">
                    <i class="nav-icon ${item.icono || 'bi bi-circle'}"></i>
                    <p>${item.nombre}</p>
                  </a>
                </li>
              `);
              $submenu.append($subItem);
            });
          }
        });
      });
    },
    error: function () {
      alert("Error al cargar el menú");
    }
  });
});


  document.addEventListener("DOMContentLoaded", () => {
    const currentUrl = window.location.pathname.split("/").pop(); 
    const links = document.querySelectorAll(".app-sidebar .nav-link");

    links.forEach(link => {
      const linkUrl = link.getAttribute("href").split("/").pop();

      if (linkUrl === currentUrl) {
        link.classList.add("active");
      }
    });
  });