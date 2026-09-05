-- Entre Vecinos - Parche R2
-- Corrige la jerarquía de Billetera en una BD ya importada con el script 20260905.
-- Ejecutar sobre la base existente entre_vecinos. Es idempotente para esta corrección.

USE `entre_vecinos`;
START TRANSACTION;

INSERT INTO `menu` (`codigo_menu`,`nombre`,`icono`,`orden`,`estado`)
VALUES (7,'Billetera','bi-wallet2',4,1)
ON DUPLICATE KEY UPDATE
  `nombre`=VALUES(`nombre`),
  `icono`=VALUES(`icono`),
  `orden`=VALUES(`orden`),
  `estado`=VALUES(`estado`);

UPDATE `menu` SET `orden`=1 WHERE `codigo_menu`=1;
UPDATE `menu` SET `orden`=2 WHERE `codigo_menu`=2;
UPDATE `menu` SET `orden`=3 WHERE `codigo_menu`=3;
UPDATE `menu` SET `orden`=5 WHERE `codigo_menu`=4;
UPDATE `menu` SET `orden`=6 WHERE `codigo_menu`=5;
UPDATE `menu` SET `orden`=7 WHERE `codigo_menu`=6;

UPDATE `menu_item`
SET `codigo_menu`=7,
    `nombre`='Resumen',
    `ruta`='/billetera',
    `icono`='bi-grid',
    `orden`=1,
    `estado`=1
WHERE `codigo_menu_item`=8;

INSERT INTO `menu_item`
(`codigo_menu_item`,`codigo_menu`,`nombre`,`ruta`,`icono`,`orden`,`estado`)
VALUES
(20,7,'Recargar saldo','/billetera/recargar','bi-plus-circle',2,1),
(21,7,'Retirar saldo','/billetera/retirar','bi-bank',3,1)
ON DUPLICATE KEY UPDATE
  `codigo_menu`=VALUES(`codigo_menu`),
  `nombre`=VALUES(`nombre`),
  `ruta`=VALUES(`ruta`),
  `icono`=VALUES(`icono`),
  `orden`=VALUES(`orden`),
  `estado`=VALUES(`estado`);

INSERT INTO `rol_menu_item`
(`codigo_rol`,`codigo_menu_item`,`puede_crear`,`puede_leer`,`puede_actualizar`,`puede_eliminar`)
VALUES
(2,20,1,1,1,0),
(2,21,1,1,1,0)
ON DUPLICATE KEY UPDATE
  `puede_crear`=VALUES(`puede_crear`),
  `puede_leer`=VALUES(`puede_leer`),
  `puede_actualizar`=VALUES(`puede_actualizar`),
  `puede_eliminar`=VALUES(`puede_eliminar`);

COMMIT;

-- Verificación esperada para rol vecino:
-- Vender: Mis publicaciones / Pedidos recibidos / Solicitudes de servicio
-- Billetera: Resumen / Recargar saldo / Retirar saldo
SELECT m.`nombre` AS menu, mi.`nombre` AS opcion, mi.`ruta`, m.`orden` AS orden_menu, mi.`orden` AS orden_opcion
FROM `rol_menu_item` rmi
INNER JOIN `menu_item` mi ON mi.`codigo_menu_item`=rmi.`codigo_menu_item`
INNER JOIN `menu` m ON m.`codigo_menu`=mi.`codigo_menu`
WHERE rmi.`codigo_rol`=2 AND rmi.`puede_leer`=1
ORDER BY m.`orden`, mi.`orden`;
