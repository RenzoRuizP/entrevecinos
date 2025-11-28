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

SELECT * FROM rol;
SELECT * FROM usuario_departamento;
SELECT * FROM usuario
SELECT * FROM menu;
SELECT * FROM menu_item;
SELECT * FROM menu_item_accesos;
SELECT * FROM publicacion;
SELECT * FROM publicacion_imagen;


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