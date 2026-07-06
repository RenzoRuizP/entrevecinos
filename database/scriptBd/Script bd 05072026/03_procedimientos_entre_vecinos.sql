-- ============================================================
-- ENTRE VECINOS (EV) - PROCEDIMIENTOS
-- Ejecutar DESPUÉS de 01_recrear_estructura_entre_vecinos.sql
-- y 02_datos_iniciales_entre_vecinos.sql
-- ============================================================
USE `entre_vecinos`;

DROP PROCEDURE IF EXISTS sp_registrar_usuario_v2;

DELIMITER $$

CREATE PROCEDURE sp_registrar_usuario_v2(
  IN p_nombre VARCHAR(200),
  IN p_documento VARCHAR(50),
  IN p_telefono VARCHAR(50),
  IN p_email VARCHAR(150),
  IN p_clave VARCHAR(255),
  IN p_codigo_rol INT,
  IN p_tipo_conjunto VARCHAR(20),
  IN p_codigo_condominio INT,
  IN p_codigo_urbanizacion INT,
  IN p_direccion VARCHAR(250),
  IN p_comprobante_domicilio VARCHAR(255)
)
BEGIN
  DECLARE v_usuario_id INT;

  IF p_tipo_conjunto NOT IN ('condominio', 'urbanizacion') THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Tipo de conjunto residencial inválido';
  END IF;

  IF p_tipo_conjunto = 'condominio'
     AND (p_codigo_condominio IS NULL OR p_codigo_condominio = 0) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Código de condominio requerido';
  END IF;

  IF p_tipo_conjunto = 'urbanizacion'
     AND (p_codigo_urbanizacion IS NULL OR p_codigo_urbanizacion = 0) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Código de urbanización requerido';
  END IF;

  IF p_direccion IS NULL OR CHAR_LENGTH(TRIM(p_direccion)) < 5 THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Dirección inválida';
  END IF;

  INSERT INTO usuario (
    nombre, documento, telefono, email, clave, estado, codigo_rol
  ) VALUES (
    TRIM(p_nombre),
    NULLIF(TRIM(p_documento), ''),
    NULLIF(TRIM(p_telefono), ''),
    LOWER(TRIM(p_email)),
    p_clave,
    1,
    p_codigo_rol
  );

  SET v_usuario_id = LAST_INSERT_ID();

  INSERT INTO usuario_residencia (
    codigo_usuario,
    tipo_conjunto,
    codigo_condominio,
    codigo_urbanizacion,
    direccion,
    comprobante_domicilio,
    estado
  ) VALUES (
    v_usuario_id,
    p_tipo_conjunto,
    CASE WHEN p_tipo_conjunto = 'condominio' THEN p_codigo_condominio ELSE NULL END,
    CASE WHEN p_tipo_conjunto = 'urbanizacion' THEN p_codigo_urbanizacion ELSE NULL END,
    TRIM(p_direccion),
    NULLIF(TRIM(p_comprobante_domicilio), ''),
    1
  );

  INSERT INTO usuario_revision (
    codigo_usuario,
    estado_revision,
    comprobante_path
  ) VALUES (
    v_usuario_id,
    1,
    NULLIF(TRIM(p_comprobante_domicilio), '')
  );

  INSERT INTO billetera (codigo_usuario, saldo, saldo_actual, estado)
  VALUES (v_usuario_id, 0.00, 0.00, 1);
END$$

DELIMITER ;
