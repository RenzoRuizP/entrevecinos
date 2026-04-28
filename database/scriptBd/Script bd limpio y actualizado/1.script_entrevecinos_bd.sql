SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS entre_vecinos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE entre_vecinos;

-- =========================================================
-- 1. ROLES
-- =========================================================
CREATE TABLE rol (
  codigo_rol INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  descripcion VARCHAR(150) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_rol),
  UNIQUE KEY uk_rol_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 2. MENÚ
-- =========================================================
CREATE TABLE menu (
  codigo_menu INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  icono VARCHAR(120) NULL,
  orden INT NOT NULL DEFAULT 0,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menu_item (
  codigo_menu_item INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_menu INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  icono VARCHAR(120) NULL,
  ruta VARCHAR(255) NOT NULL,
  orden INT NOT NULL DEFAULT 0,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_menu_item),
  KEY idx_menu_item_menu (codigo_menu),
  CONSTRAINT fk_menu_item_menu
    FOREIGN KEY (codigo_menu) REFERENCES menu(codigo_menu)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rol_menu_item (
  codigo_rol_menu_item INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_rol INT UNSIGNED NOT NULL,
  codigo_menu_item INT UNSIGNED NOT NULL,
  PRIMARY KEY (codigo_rol_menu_item),
  UNIQUE KEY uk_rol_menu_item (codigo_rol, codigo_menu_item),
  KEY idx_rmi_item (codigo_menu_item),
  CONSTRAINT fk_rmi_rol
    FOREIGN KEY (codigo_rol) REFERENCES rol(codigo_rol)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_rmi_item
    FOREIGN KEY (codigo_menu_item) REFERENCES menu_item(codigo_menu_item)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 3. UBIGEO
-- =========================================================
CREATE TABLE ubigeo_departamento (
  codigo_departamento INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_departamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ubigeo_provincia (
  codigo_provincia INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_departamento INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_provincia),
  KEY idx_ubigeo_provincia_departamento (codigo_departamento),
  CONSTRAINT fk_ubigeo_provincia_departamento
    FOREIGN KEY (codigo_departamento) REFERENCES ubigeo_departamento(codigo_departamento)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ubigeo_distrito (
  codigo_distrito INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_provincia INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_distrito),
  KEY idx_ubigeo_distrito_provincia (codigo_provincia),
  CONSTRAINT fk_ubigeo_distrito_provincia
    FOREIGN KEY (codigo_provincia) REFERENCES ubigeo_provincia(codigo_provincia)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 4. CONDOMINIOS / TORRES / DEPARTAMENTOS / URBANIZACIONES
-- =========================================================
CREATE TABLE condominio (
  codigo_condominio INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre_condominio VARCHAR(150) NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  codigo_distrito INT UNSIGNED NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_condominio),
  KEY idx_condominio_distrito (codigo_distrito),
  CONSTRAINT fk_condominio_distrito
    FOREIGN KEY (codigo_distrito) REFERENCES ubigeo_distrito(codigo_distrito)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE torre (
  codigo_torre INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_condominio INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_torre),
  KEY idx_torre_condominio (codigo_condominio),
  CONSTRAINT fk_torre_condominio
    FOREIGN KEY (codigo_condominio) REFERENCES condominio(codigo_condominio)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE departamento (
  codigo_departamento_interno INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_torre INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_departamento_interno),
  KEY idx_departamento_torre (codigo_torre),
  CONSTRAINT fk_departamento_torre
    FOREIGN KEY (codigo_torre) REFERENCES torre(codigo_torre)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE urbanizacion (
  codigo_urbanizacion INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre_urbanizacion VARCHAR(150) NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  codigo_distrito INT UNSIGNED NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_urbanizacion),
  KEY idx_urbanizacion_distrito (codigo_distrito),
  CONSTRAINT fk_urbanizacion_distrito
    FOREIGN KEY (codigo_distrito) REFERENCES ubigeo_distrito(codigo_distrito)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 5. USUARIO
-- estado: 0=inactivo, 1=en_revision, 2=habilitado
-- =========================================================
CREATE TABLE usuario (
  codigo_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_rol INT UNSIGNED NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  clave VARCHAR(255) NOT NULL,
  documento VARCHAR(20) NULL,
  telefono VARCHAR(20) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_usuario),
  UNIQUE KEY uk_usuario_email (email),
  UNIQUE KEY uk_usuario_documento (documento),
  KEY idx_usuario_rol (codigo_rol),
  KEY idx_usuario_estado (estado),
  CONSTRAINT fk_usuario_rol
    FOREIGN KEY (codigo_rol) REFERENCES rol(codigo_rol)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 6. RESIDENCIA VIGENTE DEL USUARIO
-- =========================================================
CREATE TABLE usuario_residencia (
  codigo_usuario_residencia INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  tipo_conjunto ENUM('condominio','urbanizacion') NOT NULL,
  codigo_condominio INT UNSIGNED NULL,
  codigo_urbanizacion INT UNSIGNED NULL,
  ubigeo_departamento INT UNSIGNED NULL,
  ubigeo_provincia INT UNSIGNED NULL,
  ubigeo_distrito INT UNSIGNED NULL,
  direccion VARCHAR(255) NOT NULL,
  comprobante_domicilio VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_usuario_residencia),
  KEY idx_usuario_residencia_usuario (codigo_usuario),
  KEY idx_usuario_residencia_condominio (codigo_condominio),
  KEY idx_usuario_residencia_urbanizacion (codigo_urbanizacion),
  CONSTRAINT fk_usuario_residencia_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_usuario_residencia_condominio
    FOREIGN KEY (codigo_condominio) REFERENCES condominio(codigo_condominio)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_usuario_residencia_urbanizacion
    FOREIGN KEY (codigo_urbanizacion) REFERENCES urbanizacion(codigo_urbanizacion)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_usuario_residencia_dep
    FOREIGN KEY (ubigeo_departamento) REFERENCES ubigeo_departamento(codigo_departamento)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_usuario_residencia_prov
    FOREIGN KEY (ubigeo_provincia) REFERENCES ubigeo_provincia(codigo_provincia)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_usuario_residencia_dist
    FOREIGN KEY (ubigeo_distrito) REFERENCES ubigeo_distrito(codigo_distrito)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 7. REVISIÓN DE USUARIO
-- estado_revision: 1=en_revision, 2=aprobado, 3=observado
-- =========================================================
CREATE TABLE usuario_revision (
  codigo_usuario_revision INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  estado_revision TINYINT(1) NOT NULL DEFAULT 1,
  mensaje_observacion TEXT NULL,
  comprobante_path VARCHAR(255) NULL,
  fecha_observacion DATETIME NULL,
  fecha_reenvio DATETIME NULL,
  fecha_actualizacion DATETIME NULL,
  PRIMARY KEY (codigo_usuario_revision),
  UNIQUE KEY uk_usuario_revision_usuario (codigo_usuario),
  KEY idx_usuario_revision_estado (estado_revision),
  CONSTRAINT fk_usuario_revision_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 8. SOLICITUD CAMBIO DE RESIDENCIA
-- =========================================================
CREATE TABLE usuario_residencia_solicitud (
  codigo_solicitud INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  tipo_conjunto ENUM('condominio','urbanizacion') NOT NULL,
  codigo_condominio INT UNSIGNED NULL,
  codigo_urbanizacion INT UNSIGNED NULL,
  ubigeo_departamento INT UNSIGNED NULL,
  ubigeo_provincia INT UNSIGNED NULL,
  ubigeo_distrito INT UNSIGNED NULL,
  direccion VARCHAR(255) NOT NULL,
  comprobante_domicilio VARCHAR(255) NOT NULL,
  estado ENUM('pendiente','observada','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  comentario_admin TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_solicitud),
  KEY idx_urs_usuario (codigo_usuario),
  KEY idx_urs_estado (estado),
  KEY idx_urs_condominio (codigo_condominio),
  KEY idx_urs_urbanizacion (codigo_urbanizacion),
  CONSTRAINT fk_urs_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_urs_condominio
    FOREIGN KEY (codigo_condominio) REFERENCES condominio(codigo_condominio)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_urs_urbanizacion
    FOREIGN KEY (codigo_urbanizacion) REFERENCES urbanizacion(codigo_urbanizacion)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_urs_dep
    FOREIGN KEY (ubigeo_departamento) REFERENCES ubigeo_departamento(codigo_departamento)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_urs_prov
    FOREIGN KEY (ubigeo_provincia) REFERENCES ubigeo_provincia(codigo_provincia)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_urs_dist
    FOREIGN KEY (ubigeo_distrito) REFERENCES ubigeo_distrito(codigo_distrito)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 9. NOTIFICACIONES
-- =========================================================
CREATE TABLE notificacion (
  codigo_notificacion INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  canal VARCHAR(30) NOT NULL DEFAULT 'app',
  categoria VARCHAR(50) NOT NULL,
  subcategoria VARCHAR(50) NULL,
  referencia_id INT UNSIGNED NULL,
  titulo VARCHAR(180) NOT NULL,
  mensaje TEXT NOT NULL,
  payload_json LONGTEXT NULL,
  estado ENUM('no_leida','leida') NOT NULL DEFAULT 'no_leida',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME NULL,
  PRIMARY KEY (codigo_notificacion),
  KEY idx_notificacion_usuario (codigo_usuario),
  KEY idx_notificacion_categoria (categoria),
  KEY idx_notificacion_estado (estado),
  KEY idx_notificacion_referencia (referencia_id),
  CONSTRAINT fk_notificacion_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 10. DISPONIBILIDAD PEDIDOS
-- =========================================================
CREATE TABLE usuario_disponibilidad_pedidos (
  codigo_usuario_disponibilidad INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  disponibilidad TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_usuario_disponibilidad),
  UNIQUE KEY uk_usuario_disponibilidad (codigo_usuario),
  CONSTRAINT fk_usuario_disponibilidad_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 11. PROCEDURES BASE
-- =========================================================
DROP PROCEDURE IF EXISTS sp_guardar_disponibilidad_pedidos;
DELIMITER $$
CREATE PROCEDURE sp_guardar_disponibilidad_pedidos(
  IN p_codigo_usuario INT UNSIGNED,
  IN p_disponibilidad TINYINT
)
BEGIN
  INSERT INTO usuario_disponibilidad_pedidos (codigo_usuario, disponibilidad)
  VALUES (p_codigo_usuario, IFNULL(p_disponibilidad, 0))
  ON DUPLICATE KEY UPDATE
    disponibilidad = VALUES(disponibilidad),
    updated_at = CURRENT_TIMESTAMP;
END $$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_marcar_notificaciones_leidas_por_referencia;
DELIMITER $$
CREATE PROCEDURE sp_marcar_notificaciones_leidas_por_referencia(
  IN p_codigo_usuario INT UNSIGNED,
  IN p_categoria VARCHAR(50),
  IN p_referencia_id INT UNSIGNED
)
BEGIN
  UPDATE notificacion
     SET estado = 'leida',
         read_at = NOW()
   WHERE codigo_usuario = p_codigo_usuario
     AND categoria = p_categoria
     AND referencia_id = p_referencia_id
     AND estado = 'no_leida';
END $$
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;