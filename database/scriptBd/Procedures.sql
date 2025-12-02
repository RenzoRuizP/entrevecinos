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
			
				INSERT INTO usuario (nombre, email, clave, codigo_rol, documento, telefono, fecha_creacion)
				VALUES (p_nombre, p_email, p_clave, 2, p_documento, p_telefono, (SELECT NOW())); -- 123456
				
				INSERT INTO usuario_departamento(fecha_inicio, codigo_usuario, codigo_departamento, fecha_creacion)
				VALUES (p_fecha_inicio, (SELECT NOW()));
END $$


-- 
SELECT * FROM rol;
SELECT * FROM usuario_departamento;
SELECT * FROM usuario;
SELECT NOW();

SELECT * FROM menu;
SELECT * FROM menu_item;
SELECT * FROM menu_item_accesos;
SELECT * FROM publicacion;
SELECT * FROM publicacion_imagen;

SELECT NOW()

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