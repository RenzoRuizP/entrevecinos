-- ============================================================================
-- ENTRE VECINOS - Migración pedidos concurrentes / limpieza del flujo anterior
-- Fecha: 2026-08-26
-- Objetivo:
--   1) Normalizar solicitudes creadas con el flujo secuencial anterior.
--   2) Limpiar el historial técnico asociado a ese flujo.
--   3) Eliminar del esquema los campos obsoletos de secuenciamiento.
--
-- IMPORTANTE:
--   - Ejecutar primero en DEV y luego en QA.
--   - Tomar backup antes de ejecutar en cualquier ambiente con datos valiosos.
--   - El proyecto PHP entregado junto con esta migración ya no lee ni escribe
--     ninguno de los campos que se eliminan aquí.
-- ============================================================================

USE entre_vecinos;

-- --------------------------------------------------------------------------
-- A. Normalización de datos antes del cambio estructural
-- --------------------------------------------------------------------------
START TRANSACTION;

-- Solicitudes antiguas pasan al único estado pendiente vigente. Se les otorga
-- una nueva ventana de respuesta de 4 minutos para no cerrarlas de forma
-- abrupta durante la migración.
UPDATE pedido
SET
    fase = 'solicitud',
    estado_actual = 'pendiente_vendedor',
    estado = 'pendiente_vendedor',
    motivo_estado = 'Solicitud pendiente de atención del vendedor.',
    fecha_limite_respuesta = DATE_ADD(NOW(), INTERVAL 240 SECOND),
    updated_at = CURRENT_TIMESTAMP
WHERE estado_actual IN ('cola_pendiente_confirmacion', 'cola_aceptada')
   OR estado IN ('cola_pendiente_confirmacion', 'cola_aceptada');

-- Los eventos cuya única finalidad era registrar la confirmación del flujo
-- anterior dejan de tener valor funcional en DEV/QA y se eliminan.
DELETE FROM pedido_historial_estado
WHERE motivo = 'confirmacion_cola';

-- Conserva el resto del historial, normalizando cualquier estado antiguo que
-- pudiera permanecer por datos de prueba o versiones previas.
UPDATE pedido_historial_estado
SET
    estado_anterior = CASE
        WHEN estado_anterior IN ('cola_pendiente_confirmacion', 'cola_aceptada')
            THEN 'pendiente_vendedor'
        ELSE estado_anterior
    END,
    estado_nuevo = CASE
        WHEN estado_nuevo IN ('cola_pendiente_confirmacion', 'cola_aceptada')
            THEN 'pendiente_vendedor'
        ELSE estado_nuevo
    END,
    observacion = CASE
        WHEN observacion = 'El comprador aceptó permanecer en la cola.'
            THEN 'Solicitud registrada para atención del vendedor.'
        WHEN observacion = 'La solicitud dejó la cola y quedó disponible para atención independiente.'
            THEN 'Solicitud disponible para atención independiente.'
        ELSE observacion
    END,
    motivo = CASE
        WHEN motivo = 'migracion_flujo_concurrente'
            THEN 'normalizacion_flujo_pedidos'
        ELSE motivo
    END
WHERE estado_anterior IN ('cola_pendiente_confirmacion', 'cola_aceptada')
   OR estado_nuevo IN ('cola_pendiente_confirmacion', 'cola_aceptada')
   OR motivo IN ('migracion_flujo_concurrente')
   OR observacion IN (
        'El comprador aceptó permanecer en la cola.',
        'La solicitud dejó la cola y quedó disponible para atención independiente.'
   );

COMMIT;

-- --------------------------------------------------------------------------
-- B. Limpieza estructural idempotente
-- --------------------------------------------------------------------------
-- Se usa INFORMATION_SCHEMA para que el script pueda ejecutarse de nuevo sin
-- fallar si un ambiente ya fue migrado.

SET @ev_schema := DATABASE();

-- Índice histórico compuesto, si existiera en una instalación antigua.
SET @ev_idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @ev_schema
      AND TABLE_NAME = 'pedido'
      AND INDEX_NAME = 'idx_pedido_cola_vendedor'
);
SET @ev_sql := IF(
    @ev_idx_exists > 0,
    'ALTER TABLE pedido DROP INDEX idx_pedido_cola_vendedor',
    'SELECT 1'
);
PREPARE ev_stmt FROM @ev_sql;
EXECUTE ev_stmt;
DEALLOCATE PREPARE ev_stmt;

-- Campo principal del flujo anterior.
SET @ev_col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @ev_schema
      AND TABLE_NAME = 'pedido'
      AND COLUMN_NAME = 'posicion_cola'
);
SET @ev_sql := IF(
    @ev_col_exists > 0,
    'ALTER TABLE pedido DROP COLUMN posicion_cola',
    'SELECT 1'
);
PREPARE ev_stmt FROM @ev_sql;
EXECUTE ev_stmt;
DEALLOCATE PREPARE ev_stmt;

-- Campos de instalaciones históricas, por seguridad.
SET @ev_col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @ev_schema
      AND TABLE_NAME = 'pedido'
      AND COLUMN_NAME = 'confirmado_cola'
);
SET @ev_sql := IF(
    @ev_col_exists > 0,
    'ALTER TABLE pedido DROP COLUMN confirmado_cola',
    'SELECT 1'
);
PREPARE ev_stmt FROM @ev_sql;
EXECUTE ev_stmt;
DEALLOCATE PREPARE ev_stmt;

SET @ev_col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @ev_schema
      AND TABLE_NAME = 'pedido'
      AND COLUMN_NAME = 'fecha_confirmacion_cola'
);
SET @ev_sql := IF(
    @ev_col_exists > 0,
    'ALTER TABLE pedido DROP COLUMN fecha_confirmacion_cola',
    'SELECT 1'
);
PREPARE ev_stmt FROM @ev_sql;
EXECUTE ev_stmt;
DEALLOCATE PREPARE ev_stmt;

-- Procedimientos históricos, si existieran en algún ambiente.
DROP PROCEDURE IF EXISTS sp_pedido_reordenar_cola_vendedor;
DROP PROCEDURE IF EXISTS sp_pedido_liberar_siguiente_cola;

-- --------------------------------------------------------------------------
-- C. Verificación post-migración
-- Todos los resultados deben devolver 0.
-- --------------------------------------------------------------------------
SELECT COUNT(*) AS estados_antiguos_en_pedido
FROM pedido
WHERE estado_actual IN ('cola_pendiente_confirmacion', 'cola_aceptada')
   OR estado IN ('cola_pendiente_confirmacion', 'cola_aceptada');

SELECT COUNT(*) AS estados_antiguos_en_historial
FROM pedido_historial_estado
WHERE estado_anterior IN ('cola_pendiente_confirmacion', 'cola_aceptada')
   OR estado_nuevo IN ('cola_pendiente_confirmacion', 'cola_aceptada')
   OR motivo IN ('confirmacion_cola', 'migracion_flujo_concurrente');

SELECT COUNT(*) AS columnas_obsoletas
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'pedido'
  AND COLUMN_NAME IN ('posicion_cola', 'confirmado_cola', 'fecha_confirmacion_cola');

SELECT COUNT(*) AS indices_obsoletos
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'pedido'
  AND INDEX_NAME = 'idx_pedido_cola_vendedor';
