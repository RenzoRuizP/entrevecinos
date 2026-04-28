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

DROP PROCEDURE IF EXISTS sp_pedido_reordenar_cola_vendedor;
DELIMITER $$
CREATE PROCEDURE sp_pedido_reordenar_cola_vendedor(
  IN p_codigo_usuario_vendedor INT UNSIGNED
)
BEGIN
  SET @fila := 0;

  UPDATE pedido p
     JOIN (
       SELECT codigo_pedido, (@fila := @fila + 1) AS nueva_posicion
         FROM pedido
        WHERE codigo_usuario_vendedor = p_codigo_usuario_vendedor
          AND estado = 'cola_aceptada'
        ORDER BY posicion_cola ASC, created_at ASC, codigo_pedido ASC
     ) x ON x.codigo_pedido = p.codigo_pedido
     SET p.posicion_cola = x.nueva_posicion;
END $$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedido_liberar_siguiente_cola;
DELIMITER $$
CREATE PROCEDURE sp_pedido_liberar_siguiente_cola(
  IN p_codigo_usuario_vendedor INT UNSIGNED
)
BEGIN
  DECLARE v_codigo_pedido INT UNSIGNED DEFAULT NULL;

  SELECT codigo_pedido
    INTO v_codigo_pedido
    FROM pedido
   WHERE codigo_usuario_vendedor = p_codigo_usuario_vendedor
     AND estado = 'cola_aceptada'
   ORDER BY posicion_cola ASC, created_at ASC, codigo_pedido ASC
   LIMIT 1;

  IF v_codigo_pedido IS NOT NULL THEN
    UPDATE pedido
       SET estado = 'pendiente_vendedor',
           estado_anterior = 'cola_aceptada',
           posicion_cola = 1,
           fecha_limite_respuesta = DATE_ADD(NOW(), INTERVAL 4 MINUTE),
           updated_at = CURRENT_TIMESTAMP
     WHERE codigo_pedido = v_codigo_pedido;

    CALL sp_pedido_historial_insertar(
      v_codigo_pedido,
      'pendiente_vendedor',
      'La solicitud pasó de cola a atención del vendedor.',
      NULL,
      'sistema',
      NULL
    );

    CALL sp_pedido_reordenar_cola_vendedor(p_codigo_usuario_vendedor);
  END IF;
END $$
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;