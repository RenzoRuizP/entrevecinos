-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para entre_vecinos_bd
CREATE DATABASE IF NOT EXISTS `entre_vecinos_bd` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `entre_vecinos_bd`;

-- Volcando estructura para tabla entre_vecinos_bd.categoria
CREATE TABLE IF NOT EXISTS `categoria` (
  `codigo_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `codigo_tipo` int(11) NOT NULL,
  `codigo_grupo` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_categoria`),
  UNIQUE KEY `uq_categoria_tipo_nombre` (`codigo_tipo`,`nombre`),
  KEY `fk_categoria_grupo_mismo_tipo` (`codigo_grupo`,`codigo_tipo`),
  KEY `idx_categoria_listado` (`codigo_tipo`,`codigo_grupo`,`orden`,`nombre`),
  CONSTRAINT `fk_categoria_grupo_mismo_tipo` FOREIGN KEY (`codigo_grupo`, `codigo_tipo`) REFERENCES `categoria_grupo` (`codigo_grupo`, `codigo_tipo`) ON UPDATE CASCADE,
  CONSTRAINT `fk_categoria_tipo` FOREIGN KEY (`codigo_tipo`) REFERENCES `tipo` (`codigo_tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.categoria: ~67 rows (aproximadamente)
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
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
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.categoria_grupo
CREATE TABLE IF NOT EXISTS `categoria_grupo` (
  `codigo_grupo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `codigo_tipo` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_grupo`),
  UNIQUE KEY `uq_catgrupo_tipo_nombre` (`codigo_tipo`,`nombre`),
  UNIQUE KEY `uq_catgrupo_id_tipo` (`codigo_grupo`,`codigo_tipo`),
  KEY `idx_catgrupo_listado` (`codigo_tipo`,`orden`,`nombre`),
  CONSTRAINT `fk_catgrupo_tipo` FOREIGN KEY (`codigo_tipo`) REFERENCES `tipo` (`codigo_tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.categoria_grupo: ~18 rows (aproximadamente)
/*!40000 ALTER TABLE `categoria_grupo` DISABLE KEYS */;
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
/*!40000 ALTER TABLE `categoria_grupo` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.condominio
CREATE TABLE IF NOT EXISTS `condominio` (
  `codigo_condominio` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_condominio` varchar(200) NOT NULL,
  `direccion_condominio` varchar(300) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` char(1) DEFAULT NULL,
  PRIMARY KEY (`codigo_condominio`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.condominio: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `condominio` DISABLE KEYS */;
INSERT INTO `condominio` (`codigo_condominio`, `nombre_condominio`, `direccion_condominio`, `fecha_creacion`, `fecha_actualizacion`, `estado`) VALUES
	(1, 'Condominio los Faisanes', 'Av. Los Faisanes 335', '2025-11-03 18:43:19', '2025-11-03 20:08:48', 'A'),
	(2, 'Condominio el Pilar', 'Av. Guardia civil 953', '2025-11-03 18:43:19', '2025-11-03 20:08:48', 'A');
/*!40000 ALTER TABLE `condominio` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.departamento
CREATE TABLE IF NOT EXISTS `departamento` (
  `codigo_departamento` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_torre` int(11) DEFAULT NULL,
  `numero_departamento` varchar(20) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_departamento`),
  KEY `codigo_torre` (`codigo_torre`),
  CONSTRAINT `departamento_ibfk_1` FOREIGN KEY (`codigo_torre`) REFERENCES `torre` (`codigo_torre`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.departamento: ~35 rows (aproximadamente)
/*!40000 ALTER TABLE `departamento` DISABLE KEYS */;
INSERT INTO `departamento` (`codigo_departamento`, `codigo_torre`, `numero_departamento`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, 1, '101', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(2, 1, '102', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(3, 1, '103', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(4, 1, '104', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(5, 1, '105', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(6, 2, '101', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(7, 2, '102', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(8, 2, '103', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(9, 2, '104', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(10, 2, '105', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(11, 3, '101', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(12, 3, '102', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(13, 3, '103', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(14, 3, '104', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(15, 3, '105', 1, '2025-11-03 18:43:48', '2025-11-03 18:43:48'),
	(16, 4, '101', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(17, 4, '102', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(18, 4, '103', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(19, 4, '104', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(20, 4, '105', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(21, 5, '101', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(22, 5, '102', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(23, 5, '103', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(24, 5, '104', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(25, 5, '105', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(26, 6, '101', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(27, 6, '102', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(28, 6, '103', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(29, 6, '104', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(30, 6, '105', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(31, 7, '101', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(32, 7, '102', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(33, 7, '103', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(34, 7, '104', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54'),
	(35, 7, '105', 1, '2025-11-03 18:43:54', '2025-11-03 18:43:54');
/*!40000 ALTER TABLE `departamento` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.menu
CREATE TABLE IF NOT EXISTS `menu` (
  `codigo_menu` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `icono` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`codigo_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.menu: ~3 rows (aproximadamente)
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` (`codigo_menu`, `nombre`, `icono`) VALUES
	(1, 'Mis Datos', 'bi-person-circle'),
	(2, 'Comprar', 'bi-cart2'),
	(3, 'Vender', 'bi-shop-window');
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.menu_item
CREATE TABLE IF NOT EXISTS `menu_item` (
  `codigo_menu_item` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `codigo_menu` int(11) NOT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `ruta` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`codigo_menu_item`),
  KEY `codigo_menu` (`codigo_menu`),
  CONSTRAINT `menu_item_ibfk_1` FOREIGN KEY (`codigo_menu`) REFERENCES `menu` (`codigo_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.menu_item: ~7 rows (aproximadamente)
/*!40000 ALTER TABLE `menu_item` DISABLE KEYS */;
INSERT INTO `menu_item` (`codigo_menu_item`, `nombre`, `archivo`, `codigo_menu`, `icono`, `ruta`) VALUES
	(1, 'Datos personales', '', 1, 'bi-person-lines-fill', '/mi-perfil'),
	(2, 'Solicitar Pedido', '', 2, NULL, '/pedidos'),
	(3, 'Marketplace', '', 2, 'bi bi-shop', '/marketplace'),
	(4, 'Reportes Compras', '', 2, NULL, NULL),
	(5, 'Pedidos recibidos', '', 3, 'bi-bell', NULL),
	(6, 'Mis publicaciones', '', 3, 'bi-megaphone', '/publicacion'),
	(7, 'Mi billetera', '', 3, 'bi-cash-coin', NULL);
/*!40000 ALTER TABLE `menu_item` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.menu_item_accesos
CREATE TABLE IF NOT EXISTS `menu_item_accesos` (
  `codigo_menu_item_accesos` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_menu_item` int(11) NOT NULL,
  `codigo_rol` int(11) NOT NULL,
  `puede_crear` tinyint(1) NOT NULL DEFAULT 0,
  `puede_leer` tinyint(1) NOT NULL DEFAULT 1,
  `puede_actualizar` tinyint(1) NOT NULL DEFAULT 0,
  `puede_eliminar` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`codigo_menu_item_accesos`),
  KEY `codigo_menu_item` (`codigo_menu_item`),
  KEY `codigo_rol` (`codigo_rol`),
  CONSTRAINT `menu_item_accesos_ibfk_1` FOREIGN KEY (`codigo_menu_item`) REFERENCES `menu_item` (`codigo_menu_item`),
  CONSTRAINT `menu_item_accesos_ibfk_2` FOREIGN KEY (`codigo_rol`) REFERENCES `rol` (`codigo_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.menu_item_accesos: ~16 rows (aproximadamente)
/*!40000 ALTER TABLE `menu_item_accesos` DISABLE KEYS */;
INSERT INTO `menu_item_accesos` (`codigo_menu_item_accesos`, `codigo_menu_item`, `codigo_rol`, `puede_crear`, `puede_leer`, `puede_actualizar`, `puede_eliminar`) VALUES
	(1, 1, 1, 0, 1, 0, 0),
	(2, 2, 1, 0, 1, 0, 0),
	(3, 3, 1, 0, 1, 0, 0),
	(4, 4, 1, 0, 1, 0, 0),
	(5, 5, 1, 0, 1, 0, 0),
	(6, 6, 1, 0, 1, 0, 0),
	(7, 7, 1, 0, 1, 0, 0),
	(8, 3, 1, 0, 1, 0, 0),
	(9, 1, 2, 0, 1, 0, 0),
	(10, 2, 2, 0, 1, 0, 0),
	(11, 3, 2, 0, 1, 0, 0),
	(12, 4, 2, 0, 1, 0, 0),
	(13, 5, 2, 0, 1, 0, 0),
	(14, 6, 2, 0, 1, 0, 0),
	(15, 7, 2, 0, 1, 0, 0),
	(16, 3, 2, 0, 1, 0, 0);
/*!40000 ALTER TABLE `menu_item_accesos` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.publicacion
CREATE TABLE IF NOT EXISTS `producto` (
  `codigo_producto` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('Nuevo','Usado','NoAplica') DEFAULT 'NoAplica',
  `precio` decimal(10,2) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 0, -- 0: NO; 1: SI
  `destacado` tinyint(1) NOT NULL, -- 0: NO; 1: SI
  `codigo_usuario` int(11) DEFAULT NULL,
  `codigo_tipo` int(11) DEFAULT NULL,
  `codigo_categoria` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_producto`),
  KEY `codigo_usuario` (`codigo_usuario`),
  KEY `fk_producto_tipo` (`codigo_tipo`),
  KEY `fk_producto_categoria` (`codigo_categoria`),
  CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`codigo_categoria`) REFERENCES `categoria` (`codigo_categoria`),
  CONSTRAINT `fk_producto_tipo` FOREIGN KEY (`codigo_tipo`) REFERENCES `tipo` (`codigo_tipo`),
  CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=UTF8MB4_GENERAL_CI;

-- ============================================
-- AGREGAR CAMPO PARA FECHA DE DESTACADO
-- ============================================
ALTER TABLE producto
ADD COLUMN fecha_destacado DATETIME NULL
AFTER visible;

-- ============================================
-- OPCIONAL: ÍNDICE PARA CONSULTAS MÁS RÁPIDAS
-- (especialmente para expiración)
-- ============================================
ALTER TABLE producto
ADD INDEX idx_fecha_destacado (fecha_destacado);


-- Volcando datos para la tabla entre_vecinos_bd.publicacion: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `publicacion` DISABLE KEYS */;
INSERT INTO `producto` (`codigo_publicacion`, `titulo`, `imagen_portada`, `descripcion`, `estado`, `precio`, `visible`, `codigo_usuario`, `codigo_tipo`, `codigo_categoria`, `created_at`, `updated_at`) VALUES
	(8, 'INKA CHIP', NULL, '90 gr', 'Nuevo', 10.00, 1, 2, 1, 1, '2025-11-19 00:02:51', '2025-11-19 00:02:51');
/*!40000 ALTER TABLE `publicacion` ENABLE KEYS */;


-- Volcando estructura para tabla entre_vecinos_bd.publicacion_imagen
CREATE TABLE IF NOT EXISTS `producto_imagen` (
  `codigo_producto_imagen` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_producto` int(11) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `es_portada` tinyint(1) NOT NULL DEFAULT 0,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 1,
  `ancho` int(11) DEFAULT NULL,
  `alto` int(11) DEFAULT NULL,
  `peso_bytes` int(11) DEFAULT NULL,
  `mime` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_producto_imagen`),
  UNIQUE KEY `uq_prodimg_pub_orden` (`codigo_producto`,`orden`),
  KEY `ix_prodimg_prod` (`codigo_producto`,`es_portada`),
  CONSTRAINT `producto_imagen_ibfk_1` FOREIGN KEY (`codigo_producto`) REFERENCES `producto` (`codigo_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.publicacion_imagen: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `publicacion_imagen` DISABLE KEYS */;
/*!40000 ALTER TABLE `publicacion_imagen` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.rol
CREATE TABLE IF NOT EXISTS `rol` (
  `codigo_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.rol: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` (`codigo_rol`, `nombre`, `descripcion`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, 'administrador', 'Acceso total al sistema', 1, '2025-11-03 18:42:38', '2025-11-03 18:42:38'),
	(2, 'vecino', 'Usuario con acceso básico', 1, '2025-11-03 18:42:38', '2025-11-03 18:42:38');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.tipo
CREATE TABLE IF NOT EXISTS `tipo` (
  `codigo_tipo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo_tipo`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.tipo: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `tipo` DISABLE KEYS */;
INSERT INTO `tipo` (`codigo_tipo`, `nombre`, `estado`) VALUES
	(1, 'Producto', 1),
	(2, 'Servicio', 1);
/*!40000 ALTER TABLE `tipo` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.torre
CREATE TABLE IF NOT EXISTS `torre` (
  `codigo_torre` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_torre` varchar(100) NOT NULL,
  `codigo_condominio` int(11) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_torre`),
  KEY `codigo_condominio` (`codigo_condominio`),
  CONSTRAINT `torre_ibfk_1` FOREIGN KEY (`codigo_condominio`) REFERENCES `condominio` (`codigo_condominio`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.torre: ~7 rows (aproximadamente)
/*!40000 ALTER TABLE `torre` DISABLE KEYS */;
INSERT INTO `torre` (`codigo_torre`, `nombre_torre`, `codigo_condominio`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, 'A', 1, 1, '2025-11-03 18:43:26', '2025-11-03 18:43:26'),
	(2, 'B', 1, 1, '2025-11-03 18:43:26', '2025-11-03 18:43:26'),
	(3, 'C', 1, 1, '2025-11-03 18:43:26', '2025-11-03 18:43:26'),
	(4, 'A1', 2, 1, '2025-11-03 18:43:32', '2025-11-03 18:43:32'),
	(5, 'A2', 2, 1, '2025-11-03 18:43:32', '2025-11-03 18:43:32'),
	(6, 'B1', 2, 1, '2025-11-03 18:43:32', '2025-11-03 18:43:32'),
	(7, 'B2', 2, 1, '2025-11-03 18:43:32', '2025-11-03 18:43:32');
/*!40000 ALTER TABLE `torre` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `codigo_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  `codigo_rol` int(11) NOT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `codigo_rol` (`codigo_rol`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`codigo_rol`) REFERENCES `rol` (`codigo_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.usuario: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` (`codigo_usuario`, `nombre`, `email`, `clave`, `estado`, `codigo_rol`, `documento`, `telefono`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, 'Juan Pérez', 'juan@example.com', '$2y$10$/B9/83Ch5OG0/wGK/y3iYenuEjIHmUJbSAwj9UZtJwNX8E2ZgfoKm', 1, 2, NULL, NULL, '2025-11-03 18:42:38', '2025-11-03 18:42:38'),
	(2, 'Marco Renzo Francesco Ruiz Pastor', 'renzorp_14@hotmail.com', '$2y$10$bfCjuj1nsRIJS0KmGiLtVeK4P3ra5/3ksH8.Wtp9/wKpYemCLx4xy', 1, 2, '45977448', '956969182', '2025-11-03 20:11:23', '2025-11-03 20:25:16');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;

-- Volcando estructura para tabla entre_vecinos_bd.usuario_departamento
CREATE TABLE IF NOT EXISTS `usuario_departamento` (
  `codigo_usuario_departamento` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `codigo_usuario` int(11) NOT NULL,
  `codigo_departamento` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_usuario_departamento`),
  KEY `codigo_usuario` (`codigo_usuario`),
  KEY `codigo_departamento` (`codigo_departamento`),
  CONSTRAINT `usuario_departamento_ibfk_1` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `usuario_departamento_ibfk_2` FOREIGN KEY (`codigo_departamento`) REFERENCES `departamento` (`codigo_departamento`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla entre_vecinos_bd.usuario_departamento: ~1 rows (aproximadamente)
/*!40000 ALTER TABLE `usuario_departamento` DISABLE KEYS */;
INSERT INTO `usuario_departamento` (`codigo_usuario_departamento`, `fecha_inicio`, `fecha_fin`, `codigo_usuario`, `codigo_departamento`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, '2025-11-04', NULL, 2, 7, '2025-11-03 20:11:23', '2025-11-03 20:25:16');
/*!40000 ALTER TABLE `usuario_departamento` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;


CREATE TABLE billetera (
    codigo_billetera    INT AUTO_INCREMENT PRIMARY KEY,
    codigo_usuario      INT NOT NULL,
    
    -- Saldo disponible en la billetera del usuario
    saldo_actual        DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    
    -- 1 = Activa, 0 = Inactiva (por si en algún momento se quiere bloquear)
    estado              TINYINT(1) NOT NULL DEFAULT 1,
    
    fecha_creacion      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                                      ON UPDATE CURRENT_TIMESTAMP,
    
    -- Un usuario solo puede tener UNA billetera
    CONSTRAINT uq_billetera_usuario UNIQUE (codigo_usuario),
    
    CONSTRAINT fk_billetera_usuario
        FOREIGN KEY (codigo_usuario)
        REFERENCES usuario(codigo_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE billetera_movimiento (
    codigo_movimiento   INT AUTO_INCREMENT PRIMARY KEY,
    codigo_billetera    INT NOT NULL,
    
    -- C = Crédito (entra dinero), D = Débito (sale dinero)
    tipo_movimiento     ENUM('C','D') NOT NULL,
    
    monto               DECIMAL(10, 2) NOT NULL,
    
    -- Saldo resultante en la billetera DESPUÉS del movimiento
    saldo_despues       DECIMAL(10, 2) NOT NULL,
    
    -- Ej: 'RECARGA_MANUAL', 'PUBLICACION_DESTACADA', 'AJUSTE_ADMIN', etc.
    origen              VARCHAR(50) NOT NULL,
    
    -- Por ejemplo, el código de publicación cuando se cobra S/1 para destacar
    codigo_referencia   INT NULL,
    
    descripcion         VARCHAR(255) NULL,
    
    fecha_movimiento    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_mov_billetera
        FOREIGN KEY (codigo_billetera)
        REFERENCES billetera(codigo_billetera)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);


ALTER TABLE billetera_movimiento
  ADD COLUMN saldo_antes DECIMAL(10,2) NOT NULL AFTER monto,
  ADD COLUMN es_promocional TINYINT(1) NOT NULL DEFAULT 0 AFTER descripcion,
  ADD COLUMN fecha_expira DATETIME NULL AFTER es_promocional;

CREATE INDEX idx_mov_billetera_fecha
  ON billetera_movimiento (codigo_billetera, fecha_movimiento);

CREATE INDEX idx_mov_origen
  ON billetera_movimiento (origen);

CREATE INDEX idx_mov_referencia
  ON billetera_movimiento (codigo_referencia);

-- DROP TABLE recarga_saldo;

CREATE TABLE recarga_saldo (
  codigo_recarga      INT AUTO_INCREMENT PRIMARY KEY,
  codigo_usuario      INT NOT NULL,
  monto               DECIMAL(10,2) NOT NULL,
  metodo              ENUM('yape','plin') NOT NULL,
  id_operacion        VARCHAR(80) NOT NULL,
  comprobante_path    VARCHAR(255) NULL,

  estado              ENUM('pendiente','observada','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  comentario_soporte  VARCHAR(255) NULL,
  codigo_soporte      INT NULL,
  fecha_revision      DATETIME NULL,

  fecha_creacion      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uq_recarga_operacion (metodo, id_operacion),
  KEY idx_recarga_estado (estado),
  KEY idx_recarga_usuario (codigo_usuario),

  CONSTRAINT fk_recarga_usuario
    FOREIGN KEY (codigo_usuario)
    REFERENCES usuario(codigo_usuario)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);



-- 1) Tabla urbanizacion
CREATE TABLE IF NOT EXISTS urbanizacion (
  codigo_urbanizacion INT AUTO_INCREMENT PRIMARY KEY,
  nombre_urbanizacion VARCHAR(150) NOT NULL,
  estado TINYINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2) Relación de residencia por usuario (unificada)
CREATE TABLE IF NOT EXISTS usuario_residencia (
  codigo_usuario_residencia INT AUTO_INCREMENT PRIMARY KEY,
  codigo_usuario INT NOT NULL,
  tipo_conjunto ENUM('condominio','urbanizacion') NOT NULL,
  codigo_condominio INT NULL,
  codigo_urbanizacion INT NULL,
  direccion VARCHAR(250) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_ur_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario),

  CONSTRAINT fk_ur_condominio
    FOREIGN KEY (codigo_condominio) REFERENCES condominio(codigo_condominio),

  CONSTRAINT fk_ur_urbanizacion
    FOREIGN KEY (codigo_urbanizacion) REFERENCES urbanizacion(codigo_urbanizacion)
) ENGINE=InnoDB;

-- Reglas de integridad “lógica” (no todas las BD aplican CHECK de forma estricta):
-- Si tu MySQL/MariaDB no respeta CHECK, valida en SP.

ALTER TABLE usuario_residencia
  ADD COLUMN comprobante_domicilio VARCHAR(255) NULL AFTER direccion;



CREATE TABLE `producto_revision` (
  `codigo_revision` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_producto` int(11) NOT NULL,
  `estado_anterior` tinyint(2) NOT NULL,
  `estado_nuevo` tinyint(2) NOT NULL,
  `comentario` varchar(500) DEFAULT NULL,
  `codigo_soporte` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_revision`),
  KEY `ix_rev_producto` (`codigo_producto`,`created_at`),
  KEY `ix_rev_soporte` (`codigo_soporte`,`created_at`),
  CONSTRAINT `fk_rev_producto` FOREIGN KEY (`codigo_producto`) REFERENCES `producto` (`codigo_producto`),
  CONSTRAINT `fk_rev_soporte` FOREIGN KEY (`codigo_soporte`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=UTF8MB4_GENERAL_CI;


ALTER TABLE menu
  ADD COLUMN orden INT NOT NULL DEFAULT 1 AFTER icono,
  ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER orden;

DROP TABLE ubigeo_departamento;
DROP TABLE ubigeo_provincia;
DROP TABLEubigeo_distrito;
DROP TABLE urbanizacion


-- =========================
-- 1) UBIGEO (mínimo) - estado 'A'
-- =========================

CREATE TABLE IF NOT EXISTS ubigeo_departamento (
  codigo_departamento INT NOT NULL,
  nombre_departamento VARCHAR(150) NOT NULL,
  estado CHAR(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (codigo_departamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ubigeo_provincia (
  codigo_provincia INT NOT NULL,
  codigo_departamento INT NOT NULL,
  nombre_provincia VARCHAR(150) NOT NULL,
  estado CHAR(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (codigo_provincia),
  KEY idx_dep (codigo_departamento),
  CONSTRAINT fk_prov_dep
    FOREIGN KEY (codigo_departamento) REFERENCES ubigeo_departamento(codigo_departamento)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ubigeo_distrito (
  codigo_distrito INT NOT NULL,
  codigo_provincia INT NOT NULL,
  nombre_distrito VARCHAR(150) NOT NULL,
  estado CHAR(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (codigo_distrito),
  KEY idx_prov (codigo_provincia),
  CONSTRAINT fk_dist_prov
    FOREIGN KEY (codigo_provincia) REFERENCES ubigeo_provincia(codigo_provincia)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- 2) Condominio: agregar distrito + índice + FK
-- =========================

ALTER TABLE condominio
  ADD COLUMN codigo_distrito INT NULL AFTER direccion_condominio;

ALTER TABLE condominio
  ADD KEY idx_cond_distrito (codigo_distrito);

ALTER TABLE condominio
  ADD CONSTRAINT fk_condominio_distrito
    FOREIGN KEY (codigo_distrito) REFERENCES ubigeo_distrito(codigo_distrito)
    ON UPDATE CASCADE
    ON DELETE RESTRICT;

-- =========================
-- 3) Urbanización (nueva tabla) - estado 'A'
-- =========================

CREATE TABLE IF NOT EXISTS urbanizacion (
  codigo_urbanizacion INT NOT NULL AUTO_INCREMENT,
  nombre_urbanizacion VARCHAR(200) NOT NULL,
  direccion_urbanizacion VARCHAR(300) NOT NULL,
  codigo_distrito INT NOT NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  estado CHAR(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (codigo_urbanizacion),
  KEY idx_urb_dist (codigo_distrito),
  CONSTRAINT fk_urbanizacion_distrito
    FOREIGN KEY (codigo_distrito) REFERENCES ubigeo_distrito(codigo_distrito)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4;


ALTER TABLE usuario
  MODIFY estado TINYINT(1) NOT NULL DEFAULT 1
  COMMENT '0=Inactivo, 1=En revisión, 2=Habilitado';



--

CREATE TABLE `usuario_residencia_solicitud` (
  `codigo_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(11) NOT NULL,

  `tipo_conjunto` enum('condominio','urbanizacion') NOT NULL,
  `codigo_condominio` int(11) DEFAULT NULL,
  `codigo_urbanizacion` int(11) DEFAULT NULL,
  `codigo_departamento` int(11) DEFAULT NULL,

  `direccion` varchar(250) NOT NULL,
  `comprobante_domicilio` varchar(255) NOT NULL,

  `estado` enum('pendiente','observada','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `comentario_admin` varchar(500) DEFAULT NULL,

  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),

  PRIMARY KEY (`codigo_solicitud`),
  KEY `idx_urs_usuario` (`codigo_usuario`),
  KEY `idx_urs_estado` (`estado`),
  KEY `idx_urs_condominio` (`codigo_condominio`),
  KEY `idx_urs_urbanizacion` (`codigo_urbanizacion`),

  CONSTRAINT `fk_urs_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_urs_condominio` FOREIGN KEY (`codigo_condominio`) REFERENCES `condominio` (`codigo_condominio`),
  CONSTRAINT `fk_urs_urbanizacion` FOREIGN KEY (`codigo_urbanizacion`) REFERENCES `urbanizacion` (`codigo_urbanizacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=UTF8MB4_GENERAL_CI;


ALTER TABLE usuario_residencia_solicitud
  DROP FOREIGN KEY fk_urs_condominio,
  DROP FOREIGN KEY fk_urs_urbanizacion,
  DROP FOREIGN KEY fk_urs_usuario;

-- Si existe FK hacia departamento, bórralo también (depende de tu BD real)
-- SHOW CREATE TABLE usuario_residencia_solicitud;  -- úsalo para ver el nombre exacto del FK

ALTER TABLE usuario_residencia_solicitud
  DROP COLUMN codigo_departamento;

-- Vuelve a crear FKs (sin departamento)
ALTER TABLE usuario_residencia_solicitud
  ADD CONSTRAINT fk_urs_usuario FOREIGN KEY (codigo_usuario) REFERENCES usuario (codigo_usuario),
  ADD CONSTRAINT fk_urs_condominio FOREIGN KEY (codigo_condominio) REFERENCES condominio (codigo_condominio),
  ADD CONSTRAINT fk_urs_urbanizacion FOREIGN KEY (codigo_urbanizacion) REFERENCES urbanizacion (codigo_urbanizacion);

ALTER TABLE usuario_residencia_solicitud
  DROP COLUMN codigo_departamento;





CREATE TABLE `notificacion` (
  `codigo_notificacion` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo_usuario` INT(11) NOT NULL,
  `canal` VARCHAR(30) NOT NULL DEFAULT 'app',              -- app | email | push (futuro)
  `categoria` VARCHAR(30) NOT NULL,                        -- residencia | soporte | pedidos (futuro)
  `subcategoria` VARCHAR(50) NOT NULL DEFAULT '',          -- residencia_cambio
  `referencia_id` INT(11) DEFAULT NULL,                    -- ej: codigo_solicitud
  `titulo` VARCHAR(120) NOT NULL,
  `mensaje` VARCHAR(500) NOT NULL,
  `payload_json` LONGTEXT DEFAULT NULL,                    -- JSON (string) con datos extra
  `estado` ENUM('no_leida','leida') NOT NULL DEFAULT 'no_leida',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`codigo_notificacion`),
  KEY `idx_notif_usuario_estado` (`codigo_usuario`,`estado`),
  KEY `idx_notif_categoria` (`categoria`),
  KEY `idx_notif_ref` (`referencia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=UTF8MB4_GENERAL_CI;


-- -------

ALTER TABLE usuario
  ADD COLUMN comentario_soporte TEXT NULL AFTER telefono,
  ADD COLUMN comprobante_observacion_url VARCHAR(255) NULL AFTER comentario_soporte,
  ADD COLUMN fecha_reenvio_observacion TIMESTAMP NULL AFTER comprobante_observacion_url;
  
  
-- -------

CREATE TABLE IF NOT EXISTS usuario_revision (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo_usuario INT NOT NULL,
  estado_revision TINYINT NOT NULL DEFAULT 1 COMMENT '1=En revision, 2=Habilitado, 3=Observado',
  mensaje_observacion VARCHAR(500) DEFAULT NULL,
  comprobante_path VARCHAR(255) DEFAULT NULL,
  fecha_observacion TIMESTAMP NULL DEFAULT NULL,
  fecha_reenvio TIMESTAMP NULL DEFAULT NULL,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuario_revision (codigo_usuario),
  CONSTRAINT fk_usuario_revision_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4;



ALTER TABLE producto_revision
  MODIFY created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;


--



ALTER TABLE producto
ADD COLUMN codigo_usuario_residencia INT(11) NULL AFTER codigo_usuario,
ADD COLUMN tipo_conjunto_publicacion ENUM('condominio','urbanizacion') NULL AFTER codigo_usuario_residencia,
ADD COLUMN codigo_condominio_publicacion INT(11) NULL AFTER tipo_conjunto_publicacion,
ADD COLUMN codigo_urbanizacion_publicacion INT(11) NULL AFTER codigo_condominio_publicacion,
ADD COLUMN estado_residencial_publicacion ENUM('activa','bloqueado_por_cambio','migrada') NOT NULL DEFAULT 'activa' AFTER codigo_urbanizacion_publicacion;


ALTER TABLE producto
ADD INDEX idx_producto_usuario_residencia (codigo_usuario_residencia),
ADD INDEX idx_producto_condominio_publicacion (codigo_condominio_publicacion),
ADD INDEX idx_producto_urbanizacion_publicacion (codigo_urbanizacion_publicacion),
ADD INDEX idx_producto_estado_residencial (estado_residencial_publicacion);



ALTER TABLE producto
ADD CONSTRAINT fk_producto_usuario_residencia
    FOREIGN KEY (codigo_usuario_residencia)
    REFERENCES usuario_residencia (codigo_usuario_residencia),
ADD CONSTRAINT fk_producto_condominio_publicacion
    FOREIGN KEY (codigo_condominio_publicacion)
    REFERENCES condominio (codigo_condominio),
ADD CONSTRAINT fk_producto_urbanizacion_publicacion
    FOREIGN KEY (codigo_urbanizacion_publicacion)
    REFERENCES urbanizacion (codigo_urbanizacion);
    
    
    
    
ALTER TABLE recarga_saldo
ADD COLUMN reenviada_usuario TINYINT(1) NOT NULL DEFAULT 0
AFTER fecha_revision;
