-- Entre Vecinos
-- Migración: switch Activo/Inactivo para publicaciones aprobadas
-- Fecha: 2026-08-27
-- Aplicar UNA vez sobre la BD DEV/QA existente antes de probar esta versión.

START TRANSACTION;

ALTER TABLE producto
    ADD COLUMN IF NOT EXISTS activo_publicacion TINYINT(1) NOT NULL DEFAULT 1
    COMMENT '1=activa en Marketplace,0=inactiva por el vecino'
    AFTER visible;

-- Toda publicación existente conserva su comportamiento actual al migrar.
UPDATE producto
SET activo_publicacion = 1
WHERE activo_publicacion IS NULL OR activo_publicacion NOT IN (0,1);

COMMIT;

-- Verificación: debe devolver una fila con Field = activo_publicacion.
SHOW COLUMNS FROM producto LIKE 'activo_publicacion';

-- Resumen informativo.
SELECT
    SUM(CASE WHEN visible = 2 AND activo_publicacion = 1 THEN 1 ELSE 0 END) AS aprobadas_activas,
    SUM(CASE WHEN visible = 2 AND activo_publicacion = 0 THEN 1 ELSE 0 END) AS aprobadas_inactivas
FROM producto;
