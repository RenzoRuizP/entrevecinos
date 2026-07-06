-- ============================================================
-- ENTRE VECINOS (EV) - DATOS INICIALES
-- Ejecutar DESPUÉS de 01_recrear_estructura_entre_vecinos.sql
-- ============================================================
USE `entre_vecinos`;
SET NAMES utf8mb4;

-- Roles vigentes en el código fuente.
INSERT INTO rol (codigo_rol, nombre, descripcion, estado) VALUES
  (1, 'admin', 'Administrador del sistema EV', 1),
  (2, 'vecino', 'Usuario vecino de una comunidad', 1),
  (3, 'soporte', 'Operador de soporte EV', 1),
  (4, 'administrador_comunidad', 'Administrador de una comunidad', 1);

-- Ubigeo mínimo para pruebas locales.
INSERT INTO ubigeo_departamento (codigo_departamento, nombre_departamento, estado) VALUES
  (15, 'Lima', 1);

INSERT INTO ubigeo_provincia (codigo_provincia, codigo_departamento, nombre_provincia, estado) VALUES
  (1501, 15, 'Lima', 1);

INSERT INTO ubigeo_distrito (codigo_distrito, codigo_provincia, nombre_distrito, estado) VALUES
  (150113, 1501, 'Chorrillos', 1),
  (150104, 1501, 'Barranco', 1);

-- Comunidades iniciales usadas en EV.
INSERT INTO condominio
  (codigo_condominio, nombre_condominio, direccion_condominio, codigo_distrito, estado)
VALUES
  (1, 'Condominio Los Faisanes', 'Av. Los Faisanes 335', 150113, 'A'),
  (2, 'Condominio El Pilar', 'Av. Guardia Civil 953', 150113, 'A');

INSERT INTO urbanizacion
  (codigo_urbanizacion, nombre_urbanizacion, direccion_urbanizacion, codigo_distrito, estado)
VALUES
  (1, 'Urbanización Los Álamos', 'Av. Principal 123', 150113, 'A');

INSERT INTO torre (codigo_torre, nombre_torre, codigo_condominio, estado) VALUES
  (1, 'A', 1, 1), (2, 'B', 1, 1), (3, 'C', 1, 1),
  (4, 'A1', 2, 1), (5, 'A2', 2, 1), (6, 'B1', 2, 1), (7, 'B2', 2, 1);

INSERT INTO departamento (codigo_departamento, codigo_torre, numero_departamento, estado) VALUES
  (1,1,'101',1),(2,1,'102',1),(3,1,'103',1),(4,1,'104',1),(5,1,'105',1),
  (6,2,'101',1),(7,2,'102',1),(8,2,'103',1),(9,2,'104',1),(10,2,'105',1),
  (11,3,'101',1),(12,3,'102',1),(13,3,'103',1),(14,3,'104',1),(15,3,'105',1),
  (16,4,'101',1),(17,4,'102',1),(18,4,'103',1),(19,4,'104',1),(20,4,'105',1),
  (21,5,'101',1),(22,5,'102',1),(23,5,'103',1),(24,5,'104',1),(25,5,'105',1),
  (26,6,'101',1),(27,6,'102',1),(28,6,'103',1),(29,6,'104',1),(30,6,'105',1),
  (31,7,'101',1),(32,7,'102',1),(33,7,'103',1),(34,7,'104',1),(35,7,'105',1);

INSERT INTO tipo (codigo_tipo, nombre, estado) VALUES
  (1, 'Producto', 1),
  (2, 'Servicio', 1);

INSERT INTO `categoria_grupo` (`codigo_grupo`, `nombre`, `orden`, `estado`, `codigo_tipo`, `created_at`, `updated_at`) VALUES
	(1, 'Alimentos y bebidas', 1, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(2, 'Abarrotes y limpieza', 2, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(3, 'Electrodomésticos', 3, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(4, 'Electrónica y cómputo', 4, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(5, 'Muebles y hogar', 5, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(6, 'Ropa y calzado', 6, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(7, 'Otros productos', 7, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(8, 'Hogar y mantenimiento', 1, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(9, 'Reparaciones técnicas', 2, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(10, 'Clases y tutorías', 3, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(11, 'Salud y bienestar', 4, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(12, 'Eventos y catering', 5, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(13, 'Transporte y mudanzas', 6, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(14, 'Servicios para mascotas', 7, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(15, 'Cuidado de personas', 8, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(16, 'Soporte tecnológico', 9, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(17, 'Personalizados y manualidades', 10, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(18, 'Otros servicios', 11, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35');

INSERT INTO `categoria` (`codigo_categoria`, `nombre`, `orden`, `estado`, `codigo_tipo`, `codigo_grupo`, `created_at`, `updated_at`) VALUES
	(1, 'Golosinas y snacks', 1, 1, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(2, 'Frutas y verduras', 2, 1, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(3, 'Almuerzos y menús', 3, 1, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(4, 'Postres y panes', 4, 1, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(5, 'Bebidas', 5, 1, 1, 1, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(6, 'Despensa', 1, 1, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(7, 'Aseo del hogar', 2, 1, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(8, 'Cuidado personal', 3, 1, 1, 2, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(9, 'Cocina', 1, 1, 1, 3, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(10, 'Lavado', 2, 1, 1, 3, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(11, 'Climatización', 3, 1, 1, 3, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(12, 'Pequeños electrodomésticos', 4, 1, 1, 3, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(13, 'Celulares', 1, 1, 1, 4, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(14, 'Computadoras', 2, 1, 1, 4, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(15, 'Audio / Video', 3, 1, 1, 4, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(16, 'Accesorios electrónicos', 4, 1, 1, 4, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(17, 'Sillas', 1, 1, 1, 5, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(18, 'Sala', 2, 1, 1, 5, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(19, 'Dormitorio', 3, 1, 1, 5, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(20, 'Cocina / Comedor', 4, 1, 1, 5, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(21, 'Organización', 5, 1, 1, 5, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(22, 'Decoración', 6, 1, 1, 5, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(23, 'Hombre', 1, 1, 1, 6, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(24, 'Mujer', 2, 1, 1, 6, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(25, 'Niños', 3, 1, 1, 6, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(26, 'Accesorios de moda', 4, 1, 1, 6, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(27, 'Deporte y fitness', 1, 1, 1, 7, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(28, 'Mascotas', 2, 1, 1, 7, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(29, 'Libros y papelería', 3, 1, 1, 7, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(30, 'Jardín y herramientas', 4, 1, 1, 7, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(31, 'Automotriz', 5, 1, 1, 7, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(32, 'Arte, ocio y coleccionables', 6, 1, 1, 7, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(33, 'Varios', 7, 1, 1, 7, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(34, 'Limpieza', 1, 1, 2, 8, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(35, 'Gasfitería', 2, 1, 2, 8, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(36, 'Electricidad', 3, 1, 2, 8, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(37, 'Carpintería', 4, 1, 2, 8, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(38, 'Pintura', 5, 1, 2, 8, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(39, 'Electrodomésticos', 1, 1, 2, 9, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(40, 'Celulares / PC', 2, 1, 2, 9, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(41, 'TV / Audio', 3, 1, 2, 9, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(42, 'Escolares', 1, 1, 2, 10, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(43, 'Idiomas', 2, 1, 2, 10, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(44, 'Música', 3, 1, 2, 10, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(45, 'Tecnología', 4, 1, 2, 10, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(46, 'Masajes', 1, 1, 2, 11, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(47, 'Entrenamiento personal', 2, 1, 2, 11, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(48, 'Belleza / Barbería', 3, 1, 2, 11, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(49, 'Almuerzos por encargo', 1, 1, 2, 12, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(50, 'Bocaditos', 2, 1, 2, 12, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(51, 'Pastelería', 3, 1, 2, 12, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(52, 'Decoración', 4, 1, 2, 12, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(53, 'Taxi / Mototaxi', 1, 1, 2, 13, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(54, 'Fletes / Mudanzas', 2, 1, 2, 13, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(55, 'Envíos dentro del condominio', 3, 1, 2, 13, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(56, 'Baño y peluquería', 1, 1, 2, 14, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(57, 'Paseos', 2, 1, 2, 14, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(58, 'Cuidado', 3, 1, 2, 14, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(59, 'Babysitting', 1, 1, 2, 15, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(60, 'Cuidado de adulto mayor', 2, 1, 2, 15, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(61, 'Formateo / Instalación', 1, 1, 2, 16, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(62, 'Redes / WiFi', 2, 1, 2, 16, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(63, 'Configuración de equipos', 3, 1, 2, 16, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(64, 'Sublimados', 1, 1, 2, 17, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(65, 'Vinilos', 2, 1, 2, 17, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(66, 'Bordados', 3, 1, 2, 17, '2025-11-06 21:06:35', '2025-11-06 21:06:35'),
	(67, 'Varios', 1, 1, 2, 18, '2025-11-06 21:06:36', '2025-11-06 21:06:36');

-- Menús disponibles para las vistas y rutas actuales.
INSERT INTO menu (codigo_menu, nombre, icono, orden, estado) VALUES
  (1, 'Mi cuenta', 'bi-person-circle', 1, 1),
  (2, 'Comprar', 'bi-cart2', 2, 1),
  (3, 'Vender', 'bi-shop-window', 3, 1),
  (4, 'Comunidad', 'bi-people-fill', 4, 1),
  (5, 'Soporte', 'bi-headset', 5, 1);

INSERT INTO menu_item (codigo_menu_item, codigo_menu, nombre, ruta, icono, orden, estado) VALUES
  (1, 1, 'Datos personales', '/mi-perfil', 'bi-person-lines-fill', 1, 1),
  (2, 2, 'Marketplace', '/marketplace', 'bi-shop', 1, 1),
  (3, 2, 'Mis pedidos', '/mis-pedidos-comprador', 'bi-bag-check', 2, 1),
  (4, 2, 'Mis solicitudes de servicio', '/mis-solicitudes-servicio-comprador', 'bi-chat-square-text', 3, 1),
  (5, 3, 'Mis publicaciones', '/publicacion', 'bi-megaphone', 1, 1),
  (6, 3, 'Pedidos recibidos', '/mis-pedidos-vendedor', 'bi-bell', 2, 1),
  (7, 3, 'Solicitudes de servicio', '/mis-solicitudes-servicio-vendedor', 'bi-briefcase', 3, 1),
  (8, 3, 'Mi billetera', '/billetera', 'bi-cash-coin', 4, 1),
  (9, 4, 'Comunidad', '/comunidad', 'bi-people', 1, 1),
  (10, 4, 'Gestionar publicaciones', '/comunidad/gestionar', 'bi-pencil-square', 2, 1),
  (11, 4, 'Moderación', '/comunidad/moderacion', 'bi-shield-check', 3, 1),
  (12, 5, 'Atender cuentas', '/atender-cuentas', 'bi-person-check', 1, 1),
  (13, 5, 'Atender publicaciones', '/atender-publicacion', 'bi-card-checklist', 2, 1),
  (14, 5, 'Atender recargas', '/atender-recargas', 'bi-wallet2', 3, 1);

-- admin: todo; vecino: opciones de vecino; soporte: cola de soporte;
-- administrador_comunidad: su comunidad y cuenta.
INSERT INTO rol_menu_item (codigo_rol, codigo_menu_item, puede_crear, puede_leer, puede_actualizar, puede_eliminar)
SELECT 1, codigo_menu_item, 1, 1, 1, 1 FROM menu_item;

INSERT INTO rol_menu_item (codigo_rol, codigo_menu_item, puede_crear, puede_leer, puede_actualizar, puede_eliminar) VALUES
  (2,1,1,1,1,0),(2,2,0,1,0,0),(2,3,1,1,1,0),(2,4,1,1,1,0),
  (2,5,1,1,1,1),(2,6,1,1,1,0),(2,7,1,1,1,0),(2,8,1,1,1,0),(2,9,0,1,0,0),
  (3,12,1,1,1,0),(3,13,1,1,1,0),(3,14,1,1,1,0),
  (4,1,1,1,1,0),(4,9,1,1,1,0),(4,10,1,1,1,1),(4,11,1,1,1,1);

-- Usuario local de administración. Cambia esta clave cuando ingreses.
-- Usuario: admin@entrevecinos.local
-- Clave temporal: EVAdmin2026!
INSERT INTO usuario
  (codigo_usuario, nombre, email, clave, estado, codigo_rol, documento, telefono, disponibilidad_pedidos)
VALUES
  (1, 'Administrador EV', 'admin@entrevecinos.local',
   '$2y$12$wkqD44xkfr6KVV8xrU/aDe0ru8uRxnRfTQgBad.SWYgdnKtI8Zymm', 2, 1, '00000000', '000000000', 0);

INSERT INTO billetera (codigo_usuario, saldo, saldo_actual, estado)
VALUES (1, 0.00, 0.00, 1);

INSERT INTO pedido_incidencia_tipo (nombre, descripcion, estado) VALUES
  ('Producto o servicio no conforme', 'El producto o servicio recibido no coincide con lo acordado.', 1),
  ('Incumplimiento de coordinación', 'No se cumplió la coordinación pactada.', 1),
  ('Trato inadecuado', 'Reporte relacionado con trato inadecuado.', 1),
  ('Otro', 'Otro motivo de incidencia.', 1);
