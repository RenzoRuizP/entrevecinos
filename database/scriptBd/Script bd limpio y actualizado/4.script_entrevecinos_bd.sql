USE entre_vecinos;
SET FOREIGN_KEY_CHECKS = 0;

DROP PROCEDURE IF EXISTS sp_pedido_historial_insertar;
DELIMITER $$
CREATE PROCEDURE sp_pedido_historial_insertar(
  IN p_codigo_pedido INT UNSIGNED,
  IN p_estado VARCHAR(50),
  IN p_detalle TEXT,
  IN p_codigo_usuario_actor INT UNSIGNED,
  IN p_actor_tipo VARCHAR(30),
  IN p_metadata_json LONGTEXT
)
BEGIN
  INSERT INTO pedido_historial (
    codigo_pedido,
    estado,
    detalle,
    codigo_usuario_actor,
    actor_tipo,
    metadata_json
  )
  VALUES (
    p_codigo_pedido,
    p_estado,
    p_detalle,
    p_codigo_usuario_actor,
    p_actor_tipo,
    p_metadata_json
  );
END $$
DELIMITER ;


SET FOREIGN_KEY_CHECKS = 1;