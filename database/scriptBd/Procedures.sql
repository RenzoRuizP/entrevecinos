-- ******************* PROCEDURES ******************* 

-- Plantilla 
DELIMITER $$
CREATE PROCEDURE `Sp_existencia_solicitud_apc`(
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