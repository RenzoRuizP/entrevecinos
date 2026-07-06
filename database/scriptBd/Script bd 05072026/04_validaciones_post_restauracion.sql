-- ============================================================
-- ENTRE VECINOS (EV) - VALIDACIONES POST-RESTAURACIÓN
-- ============================================================
USE `entre_vecinos`;

SHOW TABLES;

SELECT COUNT(*) AS total_roles FROM rol;
SELECT COUNT(*) AS total_tipos FROM tipo;
SELECT COUNT(*) AS total_grupos_categoria FROM categoria_grupo;
SELECT COUNT(*) AS total_categorias FROM categoria;
SELECT COUNT(*) AS total_condominios FROM condominio;
SELECT COUNT(*) AS total_menu_items FROM menu_item;

SELECT
  ROUTINE_NAME,
  ROUTINE_TYPE
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'entre_vecinos'
ORDER BY ROUTINE_NAME;

SELECT
  u.codigo_usuario,
  u.nombre,
  u.email,
  u.estado,
  r.nombre AS rol
FROM usuario u
INNER JOIN rol r ON r.codigo_rol = u.codigo_rol
ORDER BY u.codigo_usuario;

-- Debe devolver el usuario administrador local:
-- admin@entrevecinos.local
