-- ******************* PROCEDURES ******************* 

-- Plantilla 
DELIMITER $$
CREATE PROCEDURE `sp_actualizar_publicacion`(
                                    in p_ano_actual int,
                                    in p_codigo_pedido varchar(15)
										)
BEGIN

	declare p_codigo_pedido date ;
    
        set data_ano_actual		= (select * from SOLICITUD_SUSPENSION_APC where codigo_pedido = '481345' and (YEAR(fecha_inicio_suspension)) = 2023 and estado_solicitud = 0 and estado_reconexion = 1);
        
        
        if data_ano_actual then
			select * from SOLICITUD_SUSPENSION_APC where codigo_pedido = '481345' and (YEAR(fecha_inicio_suspension)) = 2023 and estado_solicitud = 0 and estado_reconexion = 1;
        end if;
		if data_ano_siguiente then
			select * from SOLICITUD_SUSPENSION_APC where codigo_pedido = '481345' and (YEAR(fecha_inicio_suspension)) = 2024 and estado_solicitud = 0 and estado_reconexion = 1;
        end if;
END $$

--

 
DELIMITER $$
CREATE PROCEDURE `sp_registrar_usuario`(
                                    in p_nombre varchar(100),
                                    in p_documento varchar(15),
                                    in p_telefono varchar(50),
                                    in p_email varchar(100),
                                    in p_clave varchar(255),
                                    in p_codigo_rol int(11),
                                    in p_codigo_departamento int(11),
                                    in p_fecha_inicio DATE
										)
BEGIN
			
				
	DECLARE p_codigo_usuario INT;
				
				INSERT INTO usuario (nombre, email, clave, codigo_rol, documento, telefono, fecha_creacion)
				VALUES (p_nombre, p_email, p_clave, 2, p_documento, p_telefono, (SELECT NOW())); -- 123456
				
			SET p_codigo_usuario = 	(SELECT codigo_usuario FROM usuario WHERE documento = p_documento);
				
				IF p_codigo_usuario IS NOT NULL THEN
				
					INSERT INTO usuario_departamento(fecha_inicio, codigo_usuario, codigo_departamento, fecha_creacion)
					VALUES (p_fecha_inicio, p_codigo_usuario, p_codigo_departamento, (SELECT NOW()));
					
				ELSE
					SELECT 'No se pudo registrar. Codigo usuario no encontrado';
					
				END IF;
END $$

DROP PROCEDURE IF EXISTS sp_registrar_usuario;


-- 


DELIMITER $$

DROP PROCEDURE IF EXISTS sp_actualizar_usuario $$
CREATE PROCEDURE sp_actualizar_usuario(
    IN p_nombre              VARCHAR(150),
    IN p_documento           VARCHAR(20),
    IN p_telefono            VARCHAR(20),
    IN p_codigo_departamento INT,
    IN p_codigo_usuario      INT
)
BEGIN
    -- 1) Actualizar datos básicos del usuario
    UPDATE usuario
    SET nombre    = p_nombre,
        documento = p_documento,
        telefono  = p_telefono
    WHERE codigo_usuario = p_codigo_usuario;

    -- 2) ¿Ya tiene fila en usuario_departamento?
    IF EXISTS (
        SELECT 1
        FROM usuario_departamento
        WHERE codigo_usuario = p_codigo_usuario
    ) THEN

        UPDATE usuario_departamento
        SET codigo_departamento = p_codigo_departamento
        WHERE codigo_usuario = p_codigo_usuario;
    ELSE
        INSERT INTO usuario_departamento (codigo_usuario, codigo_departamento)
        VALUES (p_codigo_usuario, p_codigo_departamento);
    END IF;
END $$

DELIMITER ;

-- 
SELECT * FROM rol;
SELECT * FROM usuario_departamento;
SELECT * FROM usuario;
SELECT NOW();

SELECT * FROM menu;
SELECT * FROM menu_item;
SELECT * FROM menu_item_accesos;
SELECT * FROM publicacion;
SELECT * FROM rol;
bi-person-circle


SELECT 
                    m_i.codigo_menu_item, m_i.nombre, m_i.icono, m_i.ruta
                FROM rol r 
                INNER JOIN menu_item_accesos m_i_a ON r.codigo_rol = m_i_a.codigo_rol 
                INNER JOIN menu_item m_i          ON m_i.codigo_menu_item = m_i_a.codigo_menu_item 
                INNER JOIN menu m                 ON m.codigo_menu = m_i.codigo_menu
                WHERE r.nombre LIKE 'vecino' 
                  AND m.codigo_menu = 1;
                  

SELECT
                p.codigo_publicacion,
                p.titulo,
                p.descripcion,
                p.estado,
                p.precio,
                p.visible,
                p.codigo_usuario,
                p.codigo_tipo,
                p.codigo_categoria,
                p.imagen_portada,
                DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS fecha_creacion
            FROM publicacion p
            WHERE p.visible = 2
            ORDER BY p.created_at DESC
            
            
DELIMITER $$