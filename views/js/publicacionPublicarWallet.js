/*
  publicacionPublicarWallet.js
  --------------------------------------------------------------
  Compatibilidad temporal para el piloto EV.

  Los servicios ya no cobran S/ 1.00 al enviarse a revisión.
  El flujo oficial de publicación y el límite de cinco servicios
  activos se controlan en producto.js + apiProductoController.php.

  Este archivo se mantiene porque menuPrincipalScripts.php todavía
  lo carga globalmente. No registra eventos ni realiza cargos.
*/
(function () {
  'use strict';

  window.EVPublicacionWallet = Object.assign(window.EVPublicacionWallet || {}, {
    modo: 'piloto_servicios_gratis',
    activo: false
  });
})();
