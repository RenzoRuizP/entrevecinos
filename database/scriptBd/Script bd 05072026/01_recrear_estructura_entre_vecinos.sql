-- ============================================================
-- ENTRE VECINOS (EV) - BASE LOCAL LIMPIA
-- Generado a partir del código fuente vigente revisado.
-- Destino: MariaDB 10.4 / XAMPP.
-- ATENCIÓN: este archivo elimina y recrea SOLO la BD entre_vecinos.
-- Úsalo únicamente en el XAMPP nuevo/local.
-- ============================================================

DROP DATABASE IF EXISTS `entre_vecinos`;
CREATE DATABASE `entre_vecinos`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `entre_vecinos`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. Catálogos, seguridad y comunidades
-- ============================================================

CREATE TABLE rol (
  codigo_rol INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(80) NOT NULL,
  descripcion VARCHAR(255) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_rol),
  UNIQUE KEY uq_rol_nombre (nombre)
) ENGINE=InnoDB;

CREATE TABLE ubigeo_departamento (
  codigo_departamento INT NOT NULL,
  nombre_departamento VARCHAR(150) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_departamento)
) ENGINE=InnoDB;

CREATE TABLE ubigeo_provincia (
  codigo_provincia INT NOT NULL,
  codigo_departamento INT NOT NULL,
  nombre_provincia VARCHAR(150) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_provincia),
  KEY idx_ubigeo_provincia_departamento (codigo_departamento),
  CONSTRAINT fk_ubigeo_provincia_departamento
    FOREIGN KEY (codigo_departamento) REFERENCES ubigeo_departamento(codigo_departamento)
) ENGINE=InnoDB;

CREATE TABLE ubigeo_distrito (
  codigo_distrito INT NOT NULL,
  codigo_provincia INT NOT NULL,
  nombre_distrito VARCHAR(150) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_distrito),
  KEY idx_ubigeo_distrito_provincia (codigo_provincia),
  CONSTRAINT fk_ubigeo_distrito_provincia
    FOREIGN KEY (codigo_provincia) REFERENCES ubigeo_provincia(codigo_provincia)
) ENGINE=InnoDB;

CREATE TABLE condominio (
  codigo_condominio INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre_condominio VARCHAR(200) NOT NULL,
  direccion_condominio VARCHAR(300) NOT NULL,
  codigo_distrito INT NULL,
  estado CHAR(1) NOT NULL DEFAULT 'A',
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_condominio),
  KEY idx_condominio_distrito (codigo_distrito),
  CONSTRAINT fk_condominio_distrito
    FOREIGN KEY (codigo_distrito) REFERENCES ubigeo_distrito(codigo_distrito)
) ENGINE=InnoDB;

CREATE TABLE urbanizacion (
  codigo_urbanizacion INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre_urbanizacion VARCHAR(200) NOT NULL,
  direccion_urbanizacion VARCHAR(300) NOT NULL,
  codigo_distrito INT NULL,
  estado CHAR(1) NOT NULL DEFAULT 'A',
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_urbanizacion),
  KEY idx_urbanizacion_distrito (codigo_distrito),
  CONSTRAINT fk_urbanizacion_distrito
    FOREIGN KEY (codigo_distrito) REFERENCES ubigeo_distrito(codigo_distrito)
) ENGINE=InnoDB;

CREATE TABLE torre (
  codigo_torre INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre_torre VARCHAR(100) NOT NULL,
  codigo_condominio INT UNSIGNED NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_torre),
  KEY idx_torre_condominio (codigo_condominio),
  CONSTRAINT fk_torre_condominio
    FOREIGN KEY (codigo_condominio) REFERENCES condominio(codigo_condominio)
) ENGINE=InnoDB;

CREATE TABLE departamento (
  codigo_departamento INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_torre INT UNSIGNED NULL,
  numero_departamento VARCHAR(30) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_departamento),
  KEY idx_departamento_torre (codigo_torre),
  CONSTRAINT fk_departamento_torre
    FOREIGN KEY (codigo_torre) REFERENCES torre(codigo_torre)
) ENGINE=InnoDB;

CREATE TABLE usuario (
  codigo_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(200) NOT NULL,
  email VARCHAR(150) NOT NULL,
  clave VARCHAR(255) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=En revisión, 2=Habilitado',
  codigo_rol INT UNSIGNED NOT NULL,
  documento VARCHAR(50) NULL,
  telefono VARCHAR(50) NULL,
  foto_perfil VARCHAR(255) NULL,
  disponibilidad_pedidos TINYINT(1) NOT NULL DEFAULT 0,
  comentario_soporte TEXT NULL,
  comprobante_observacion_url VARCHAR(255) NULL,
  fecha_reenvio_observacion DATETIME NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_usuario),
  UNIQUE KEY uq_usuario_email (email),
  UNIQUE KEY uq_usuario_documento (documento),
  KEY idx_usuario_rol_estado (codigo_rol, estado),
  CONSTRAINT fk_usuario_rol
    FOREIGN KEY (codigo_rol) REFERENCES rol(codigo_rol)
) ENGINE=InnoDB;

CREATE TABLE usuario_departamento (
  codigo_usuario_departamento INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NULL,
  codigo_usuario INT UNSIGNED NOT NULL,
  codigo_departamento INT UNSIGNED NOT NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_usuario_departamento),
  KEY idx_usuario_departamento_usuario (codigo_usuario),
  KEY idx_usuario_departamento_departamento (codigo_departamento),
  CONSTRAINT fk_usuario_departamento_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_usuario_departamento_departamento
    FOREIGN KEY (codigo_departamento) REFERENCES departamento(codigo_departamento)
) ENGINE=InnoDB;

CREATE TABLE usuario_residencia (
  codigo_usuario_residencia INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  tipo_conjunto ENUM('condominio','urbanizacion') NOT NULL,
  codigo_condominio INT UNSIGNED NULL,
  codigo_urbanizacion INT UNSIGNED NULL,
  direccion VARCHAR(250) NOT NULL,
  comprobante_domicilio VARCHAR(255) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_usuario_residencia),
  KEY idx_ur_usuario (codigo_usuario, codigo_usuario_residencia),
  KEY idx_ur_condominio (codigo_condominio),
  KEY idx_ur_urbanizacion (codigo_urbanizacion),
  CONSTRAINT fk_ur_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_ur_condominio
    FOREIGN KEY (codigo_condominio) REFERENCES condominio(codigo_condominio),
  CONSTRAINT fk_ur_urbanizacion
    FOREIGN KEY (codigo_urbanizacion) REFERENCES urbanizacion(codigo_urbanizacion)
) ENGINE=InnoDB;

CREATE TABLE usuario_residencia_solicitud (
  codigo_solicitud INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  tipo_conjunto ENUM('condominio','urbanizacion') NOT NULL,
  codigo_condominio INT UNSIGNED NULL,
  codigo_urbanizacion INT UNSIGNED NULL,
  direccion VARCHAR(250) NOT NULL,
  comprobante_domicilio VARCHAR(255) NOT NULL,
  estado ENUM('pendiente','observada','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  comentario_admin VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_solicitud),
  KEY idx_urs_usuario (codigo_usuario),
  KEY idx_urs_estado (estado),
  KEY idx_urs_condominio (codigo_condominio),
  KEY idx_urs_urbanizacion (codigo_urbanizacion),
  CONSTRAINT fk_urs_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_urs_condominio
    FOREIGN KEY (codigo_condominio) REFERENCES condominio(codigo_condominio),
  CONSTRAINT fk_urs_urbanizacion
    FOREIGN KEY (codigo_urbanizacion) REFERENCES urbanizacion(codigo_urbanizacion)
) ENGINE=InnoDB;

CREATE TABLE usuario_revision (
  codigo_usuario_revision INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  estado_revision TINYINT NOT NULL DEFAULT 1 COMMENT '1=En revisión, 2=Habilitado, 3=Observado',
  mensaje_observacion VARCHAR(500) NULL,
  comprobante_path VARCHAR(255) NULL,
  fecha_observacion DATETIME NULL,
  fecha_reenvio DATETIME NULL,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_usuario_revision),
  UNIQUE KEY uq_usuario_revision_usuario (codigo_usuario),
  CONSTRAINT fk_usuario_revision_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE administrador_comunidad (
  codigo_administrador_comunidad INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  tipo_conjunto ENUM('condominio','urbanizacion') NOT NULL,
  codigo_condominio INT UNSIGNED NULL,
  codigo_urbanizacion INT UNSIGNED NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_administrador_comunidad),
  KEY idx_admin_comunidad_usuario (codigo_usuario, estado),
  KEY idx_admin_comunidad_condominio (codigo_condominio),
  KEY idx_admin_comunidad_urbanizacion (codigo_urbanizacion),
  CONSTRAINT fk_admin_comunidad_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_admin_comunidad_condominio
    FOREIGN KEY (codigo_condominio) REFERENCES condominio(codigo_condominio),
  CONSTRAINT fk_admin_comunidad_urbanizacion
    FOREIGN KEY (codigo_urbanizacion) REFERENCES urbanizacion(codigo_urbanizacion)
) ENGINE=InnoDB;

-- ============================================================
-- 2. Catálogo de publicaciones y menú
-- ============================================================

CREATE TABLE tipo (
  codigo_tipo INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_tipo),
  UNIQUE KEY uq_tipo_nombre (nombre)
) ENGINE=InnoDB;

CREATE TABLE categoria_grupo (
  codigo_grupo INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  orden SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  codigo_tipo INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_grupo),
  UNIQUE KEY uq_catgrupo_tipo_nombre (codigo_tipo, nombre),
  UNIQUE KEY uq_catgrupo_id_tipo (codigo_grupo, codigo_tipo),
  KEY idx_catgrupo_listado (codigo_tipo, orden, nombre),
  CONSTRAINT fk_catgrupo_tipo
    FOREIGN KEY (codigo_tipo) REFERENCES tipo(codigo_tipo)
) ENGINE=InnoDB;

CREATE TABLE categoria (
  codigo_categoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  orden SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  codigo_tipo INT UNSIGNED NOT NULL,
  codigo_grupo INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_categoria),
  UNIQUE KEY uq_categoria_tipo_nombre (codigo_tipo, nombre),
  KEY idx_categoria_listado (codigo_tipo, codigo_grupo, orden, nombre),
  CONSTRAINT fk_categoria_tipo
    FOREIGN KEY (codigo_tipo) REFERENCES tipo(codigo_tipo),
  CONSTRAINT fk_categoria_grupo_mismo_tipo
    FOREIGN KEY (codigo_grupo, codigo_tipo)
    REFERENCES categoria_grupo(codigo_grupo, codigo_tipo)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE menu (
  codigo_menu INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(200) NOT NULL,
  icono VARCHAR(100) NULL,
  orden INT NOT NULL DEFAULT 1,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_menu)
) ENGINE=InnoDB;

CREATE TABLE menu_item (
  codigo_menu_item INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_menu INT UNSIGNED NOT NULL,
  nombre VARCHAR(200) NOT NULL,
  ruta VARCHAR(255) NULL,
  icono VARCHAR(100) NULL,
  orden INT NOT NULL DEFAULT 1,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_menu_item),
  KEY idx_menu_item_menu (codigo_menu, orden),
  CONSTRAINT fk_menu_item_menu
    FOREIGN KEY (codigo_menu) REFERENCES menu(codigo_menu)
) ENGINE=InnoDB;

CREATE TABLE rol_menu_item (
  codigo_rol_menu_item INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_rol INT UNSIGNED NOT NULL,
  codigo_menu_item INT UNSIGNED NOT NULL,
  puede_crear TINYINT(1) NOT NULL DEFAULT 0,
  puede_leer TINYINT(1) NOT NULL DEFAULT 1,
  puede_actualizar TINYINT(1) NOT NULL DEFAULT 0,
  puede_eliminar TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (codigo_rol_menu_item),
  UNIQUE KEY uq_rol_menu_item (codigo_rol, codigo_menu_item),
  CONSTRAINT fk_rmi_rol
    FOREIGN KEY (codigo_rol) REFERENCES rol(codigo_rol),
  CONSTRAINT fk_rmi_menu_item
    FOREIGN KEY (codigo_menu_item) REFERENCES menu_item(codigo_menu_item)
) ENGINE=InnoDB;

-- ============================================================
-- 3. Productos, publicaciones, billetera y recargas
-- ============================================================

CREATE TABLE producto (
  codigo_producto INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo_publicacion ENUM('producto','servicio') NOT NULL DEFAULT 'producto',
  titulo VARCHAR(160) NOT NULL,
  imagen_portada VARCHAR(255) NULL,
  descripcion TEXT NULL,
  estado ENUM('Nuevo','Usado','NoAplica') NOT NULL DEFAULT 'NoAplica',
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  tipo_atencion_producto ENUM('requiere_preparacion','no_requiere_preparacion') NOT NULL DEFAULT 'no_requiere_preparacion',
  requiere_preparacion TINYINT(1) NOT NULL DEFAULT 0,
  visible TINYINT NOT NULL DEFAULT 0 COMMENT '0=borrador,1=pendiente,2=aprobado,3=rechazado,4=anulado',
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  es_destacado TINYINT(1) NOT NULL DEFAULT 0,
  fecha_destacado DATETIME NULL,
  destacado_hasta DATETIME NULL,
  codigo_usuario INT UNSIGNED NOT NULL,
  codigo_usuario_residencia INT UNSIGNED NULL,
  tipo_conjunto_publicacion ENUM('condominio','urbanizacion') NULL,
  codigo_condominio_publicacion INT UNSIGNED NULL,
  codigo_urbanizacion_publicacion INT UNSIGNED NULL,
  estado_residencial_publicacion ENUM('activa','bloqueado_por_cambio','migrada') NOT NULL DEFAULT 'activa',
  codigo_tipo INT UNSIGNED NULL,
  codigo_categoria INT UNSIGNED NULL,
  codigo_soporte INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_producto),
  KEY idx_producto_usuario_visible (codigo_usuario, visible),
  KEY idx_producto_marketplace (visible, tipo_publicacion, estado_residencial_publicacion),
  KEY idx_producto_residencia (codigo_usuario_residencia),
  KEY idx_producto_condominio (codigo_condominio_publicacion),
  KEY idx_producto_urbanizacion (codigo_urbanizacion_publicacion),
  KEY idx_producto_tipo_categoria (codigo_tipo, codigo_categoria),
  CONSTRAINT fk_producto_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_producto_usuario_residencia
    FOREIGN KEY (codigo_usuario_residencia) REFERENCES usuario_residencia(codigo_usuario_residencia),
  CONSTRAINT fk_producto_condominio
    FOREIGN KEY (codigo_condominio_publicacion) REFERENCES condominio(codigo_condominio),
  CONSTRAINT fk_producto_urbanizacion
    FOREIGN KEY (codigo_urbanizacion_publicacion) REFERENCES urbanizacion(codigo_urbanizacion),
  CONSTRAINT fk_producto_tipo
    FOREIGN KEY (codigo_tipo) REFERENCES tipo(codigo_tipo),
  CONSTRAINT fk_producto_categoria
    FOREIGN KEY (codigo_categoria) REFERENCES categoria(codigo_categoria),
  CONSTRAINT fk_producto_soporte
    FOREIGN KEY (codigo_soporte) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

CREATE TABLE producto_imagen (
  codigo_producto_imagen BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_producto INT UNSIGNED NOT NULL,
  ruta VARCHAR(255) NOT NULL,
  es_portada TINYINT(1) NOT NULL DEFAULT 0,
  orden SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  ancho INT NULL,
  alto INT NULL,
  peso_bytes INT NULL,
  mime VARCHAR(100) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_producto_imagen),
  UNIQUE KEY uq_producto_imagen_orden (codigo_producto, orden),
  KEY idx_producto_imagen_portada (codigo_producto, es_portada),
  CONSTRAINT fk_producto_imagen_producto
    FOREIGN KEY (codigo_producto) REFERENCES producto(codigo_producto)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE producto_revision (
  codigo_revision BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_producto INT UNSIGNED NOT NULL,
  estado_anterior TINYINT NOT NULL,
  estado_nuevo TINYINT NOT NULL,
  comentario VARCHAR(500) NULL,
  codigo_soporte INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_revision),
  KEY idx_producto_revision_producto (codigo_producto, created_at),
  KEY idx_producto_revision_soporte (codigo_soporte, created_at),
  CONSTRAINT fk_producto_revision_producto
    FOREIGN KEY (codigo_producto) REFERENCES producto(codigo_producto)
    ON DELETE CASCADE,
  CONSTRAINT fk_producto_revision_soporte
    FOREIGN KEY (codigo_soporte) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

CREATE TABLE billetera (
  codigo_billetera INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  saldo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  saldo_actual DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_billetera),
  UNIQUE KEY uq_billetera_usuario (codigo_usuario),
  CONSTRAINT fk_billetera_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE billetera_movimiento (
  codigo_movimiento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_billetera INT UNSIGNED NOT NULL,
  tipo_movimiento ENUM('C','D') NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  saldo_antes DECIMAL(12,2) NOT NULL,
  saldo_despues DECIMAL(12,2) NOT NULL,
  saldo_anterior DECIMAL(12,2) NOT NULL,
  saldo_posterior DECIMAL(12,2) NOT NULL,
  concepto VARCHAR(150) NULL,
  origen VARCHAR(80) NOT NULL,
  referencia_tipo VARCHAR(80) NULL,
  referencia_id BIGINT NULL,
  codigo_referencia BIGINT NULL,
  descripcion VARCHAR(255) NULL,
  es_promocional TINYINT(1) NOT NULL DEFAULT 0,
  fecha_expira DATETIME NULL,
  fecha_movimiento TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_movimiento),
  KEY idx_mov_billetera_fecha (codigo_billetera, fecha_movimiento),
  KEY idx_mov_origen (origen),
  KEY idx_mov_referencia (codigo_referencia),
  CONSTRAINT fk_mov_billetera
    FOREIGN KEY (codigo_billetera) REFERENCES billetera(codigo_billetera)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE recarga_saldo (
  codigo_recarga INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  metodo VARCHAR(30) NOT NULL,
  id_operacion VARCHAR(100) NOT NULL,
  comprobante_path VARCHAR(255) NULL,
  estado ENUM('pendiente','observada','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  comentario_soporte VARCHAR(500) NULL,
  codigo_soporte INT UNSIGNED NULL,
  fecha_revision DATETIME NULL,
  reenviada_usuario TINYINT(1) NOT NULL DEFAULT 0,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_recarga),
  UNIQUE KEY uq_recarga_operacion (metodo, id_operacion),
  KEY idx_recarga_estado (estado),
  KEY idx_recarga_usuario (codigo_usuario),
  CONSTRAINT fk_recarga_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_recarga_soporte
    FOREIGN KEY (codigo_soporte) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

-- ============================================================
-- 4. Pedidos, calificaciones y reglas financieras
-- ============================================================

CREATE TABLE pedido (
  codigo_pedido INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_producto INT UNSIGNED NOT NULL,
  codigo_usuario_comprador INT UNSIGNED NOT NULL,
  codigo_usuario_vendedor INT UNSIGNED NOT NULL,
  fase ENUM('solicitud','pedido') NOT NULL DEFAULT 'solicitud',
  estado_actual VARCHAR(100) NOT NULL,
  estado VARCHAR(100) NOT NULL,
  cantidad INT NOT NULL,
  costo_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  monto_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  tipo_entrega ENUM('inmediata','programada') NOT NULL DEFAULT 'inmediata',
  fecha_hora_programada DATETIME NULL,
  fecha_programada DATE NULL,
  direccion_entrega TEXT NOT NULL,
  mensaje_comprador TEXT NULL,
  posicion_cola INT NULL,
  motivo_estado VARCHAR(500) NULL,
  motivo_rechazo VARCHAR(255) NULL,
  requiere_preparacion TINYINT(1) NOT NULL DEFAULT 0,
  metodo_pago VARCHAR(30) NOT NULL DEFAULT 'efectivo',
  penalidad_comprador_monto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  penalidad_comprador_aplicada TINYINT(1) NOT NULL DEFAULT 0,
  monto_descontado_billetera DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  descuento_billetera_aplicado TINYINT(1) NOT NULL DEFAULT 0,
  devolucion_billetera_aplicada TINYINT(1) NOT NULL DEFAULT 0,
  comision_ev_aplicada TINYINT(1) NOT NULL DEFAULT 0,
  comision_ev_pendiente TINYINT(1) NOT NULL DEFAULT 0,
  comision_ev_monto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  cancelado_por VARCHAR(30) NULL,
  motivo_cancelacion VARCHAR(255) NULL,
  motivo_cancelacion_clave VARCHAR(80) NULL,
  sin_reembolso TINYINT(1) NOT NULL DEFAULT 0,
  oculto_comprador TINYINT(1) NOT NULL DEFAULT 0,
  oculto_vendedor TINYINT(1) NOT NULL DEFAULT 0,
  entrega_confirmada_comprador TINYINT(1) NOT NULL DEFAULT 0,
  fecha_limite_respuesta DATETIME NULL,
  fecha_aceptacion DATETIME NULL,
  fecha_rechazo DATETIME NULL,
  fecha_cancelacion DATETIME NULL,
  fecha_cierre DATETIME NULL,
  fecha_punto_recojo DATETIME NULL,
  fecha_limite_recojo DATETIME NULL,
  fecha_confirmacion_entrega DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_pedido),
  KEY idx_pedido_producto (codigo_producto),
  KEY idx_pedido_comprador (codigo_usuario_comprador),
  KEY idx_pedido_vendedor (codigo_usuario_vendedor),
  KEY idx_pedido_fase_estado (fase, estado_actual),
  KEY idx_pedido_fecha_limite (fecha_limite_respuesta),
  CONSTRAINT fk_pedido_producto
    FOREIGN KEY (codigo_producto) REFERENCES producto(codigo_producto),
  CONSTRAINT fk_pedido_comprador
    FOREIGN KEY (codigo_usuario_comprador) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_pedido_vendedor
    FOREIGN KEY (codigo_usuario_vendedor) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

CREATE TABLE pedido_historial_estado (
  codigo_historial BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_pedido INT UNSIGNED NOT NULL,
  fase_anterior VARCHAR(30) NULL,
  estado_anterior VARCHAR(100) NULL,
  fase_nueva VARCHAR(30) NOT NULL,
  estado_nuevo VARCHAR(100) NOT NULL,
  codigo_usuario_actor INT UNSIGNED NULL,
  rol_actor VARCHAR(30) NULL,
  motivo VARCHAR(255) NULL,
  observacion TEXT NULL,
  fecha_evento TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_historial),
  KEY idx_historial_pedido_fecha (codigo_pedido, fecha_evento),
  KEY idx_historial_actor (codigo_usuario_actor),
  CONSTRAINT fk_historial_pedido
    FOREIGN KEY (codigo_pedido) REFERENCES pedido(codigo_pedido)
    ON DELETE CASCADE,
  CONSTRAINT fk_historial_actor
    FOREIGN KEY (codigo_usuario_actor) REFERENCES usuario(codigo_usuario)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pedido_incidencia_tipo (
  codigo_pedido_incidencia_tipo INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_pedido_incidencia_tipo),
  UNIQUE KEY uq_pedido_incidencia_tipo_nombre (nombre)
) ENGINE=InnoDB;

CREATE TABLE pedido_incidencia (
  codigo_incidencia BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_pedido INT UNSIGNED NOT NULL,
  codigo_usuario_reportante INT UNSIGNED NOT NULL,
  codigo_usuario_afectado INT UNSIGNED NOT NULL,
  codigo_usuario_reportado INT UNSIGNED NOT NULL,
  codigo_pedido_incidencia_tipo INT UNSIGNED NULL,
  tipo_incidencia VARCHAR(120) NOT NULL,
  descripcion TEXT NOT NULL,
  estado_incidencia ENUM('registrada','en_revision','cerrada') NOT NULL DEFAULT 'registrada',
  observacion_soporte TEXT NULL,
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_incidencia),
  KEY idx_incidencia_pedido (codigo_pedido),
  KEY idx_incidencia_reportante (codigo_usuario_reportante),
  KEY idx_incidencia_reportado (codigo_usuario_reportado),
  CONSTRAINT fk_incidencia_pedido
    FOREIGN KEY (codigo_pedido) REFERENCES pedido(codigo_pedido)
    ON DELETE CASCADE,
  CONSTRAINT fk_incidencia_reportante
    FOREIGN KEY (codigo_usuario_reportante) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_incidencia_afectado
    FOREIGN KEY (codigo_usuario_afectado) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_incidencia_reportado
    FOREIGN KEY (codigo_usuario_reportado) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_incidencia_tipo
    FOREIGN KEY (codigo_pedido_incidencia_tipo) REFERENCES pedido_incidencia_tipo(codigo_pedido_incidencia_tipo)
) ENGINE=InnoDB;

CREATE TABLE pedido_nota_soporte (
  codigo_pedido_nota_soporte BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_pedido INT UNSIGNED NOT NULL,
  codigo_soporte INT UNSIGNED NOT NULL,
  nota TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_pedido_nota_soporte),
  KEY idx_pedido_nota_pedido (codigo_pedido),
  CONSTRAINT fk_pedido_nota_pedido
    FOREIGN KEY (codigo_pedido) REFERENCES pedido(codigo_pedido)
    ON DELETE CASCADE,
  CONSTRAINT fk_pedido_nota_soporte
    FOREIGN KEY (codigo_soporte) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

CREATE TABLE usuario_penalidad (
  codigo_usuario_penalidad BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  codigo_pedido_origen INT UNSIGNED NOT NULL,
  codigo_pedido_aplicado INT UNSIGNED NULL,
  monto DECIMAL(12,2) NOT NULL,
  estado ENUM('pendiente','reservada','aplicada','anulada') NOT NULL DEFAULT 'pendiente',
  motivo_clave VARCHAR(80) NOT NULL,
  descripcion VARCHAR(255) NULL,
  fecha_aplicacion DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_usuario_penalidad),
  UNIQUE KEY uq_penalidad_origen (codigo_usuario, codigo_pedido_origen),
  KEY idx_penalidad_usuario_estado (codigo_usuario, estado),
  KEY idx_penalidad_pedido_aplicado (codigo_pedido_aplicado),
  CONSTRAINT fk_penalidad_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_penalidad_pedido_origen
    FOREIGN KEY (codigo_pedido_origen) REFERENCES pedido(codigo_pedido),
  CONSTRAINT fk_penalidad_pedido_aplicado
    FOREIGN KEY (codigo_pedido_aplicado) REFERENCES pedido(codigo_pedido)
) ENGINE=InnoDB;

CREATE TABLE vendedor_comision_ev (
  codigo_vendedor_comision_ev BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_pedido INT UNSIGNED NOT NULL,
  codigo_usuario_vendedor INT UNSIGNED NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  estado ENUM('pendiente','cobrada','anulada') NOT NULL DEFAULT 'pendiente',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_vendedor_comision_ev),
  UNIQUE KEY uq_comision_pedido (codigo_pedido),
  KEY idx_comision_vendedor_estado (codigo_usuario_vendedor, estado),
  CONSTRAINT fk_comision_pedido
    FOREIGN KEY (codigo_pedido) REFERENCES pedido(codigo_pedido),
  CONSTRAINT fk_comision_vendedor
    FOREIGN KEY (codigo_usuario_vendedor) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

CREATE TABLE calificacion (
  codigo_calificacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_pedido INT UNSIGNED NOT NULL,
  codigo_usuario_calificador INT UNSIGNED NOT NULL,
  codigo_usuario_calificado INT UNSIGNED NOT NULL,
  rol_calificador ENUM('comprador','vendedor') NOT NULL,
  rol_calificado ENUM('comprador','vendedor') NOT NULL,
  tipo_calificacion VARCHAR(60) NOT NULL,
  puntaje TINYINT NULL,
  comentario TEXT NULL,
  estado ENUM('pendiente','enviada','vencida') NOT NULL DEFAULT 'pendiente',
  fecha_habilitacion DATETIME NOT NULL,
  fecha_limite DATETIME NOT NULL,
  fecha_envio DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_calificacion),
  UNIQUE KEY uq_calificacion_pedido_sentido (codigo_pedido, codigo_usuario_calificador, codigo_usuario_calificado),
  KEY idx_calificacion_calificado (codigo_usuario_calificado, estado),
  CONSTRAINT fk_calificacion_pedido
    FOREIGN KEY (codigo_pedido) REFERENCES pedido(codigo_pedido)
    ON DELETE CASCADE,
  CONSTRAINT fk_calificacion_calificador
    FOREIGN KEY (codigo_usuario_calificador) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_calificacion_calificado
    FOREIGN KEY (codigo_usuario_calificado) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

CREATE TABLE calificacion_etiqueta (
  codigo_calificacion_etiqueta BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_calificacion BIGINT UNSIGNED NOT NULL,
  etiqueta VARCHAR(80) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_calificacion_etiqueta),
  UNIQUE KEY uq_calificacion_etiqueta (codigo_calificacion, etiqueta),
  CONSTRAINT fk_calificacion_etiqueta_calificacion
    FOREIGN KEY (codigo_calificacion) REFERENCES calificacion(codigo_calificacion)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE calificacion_reporte (
  codigo_calificacion_reporte BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_calificacion BIGINT UNSIGNED NOT NULL,
  codigo_pedido INT UNSIGNED NOT NULL,
  codigo_usuario_reporta INT UNSIGNED NOT NULL,
  codigo_usuario_reportado INT UNSIGNED NOT NULL,
  motivo VARCHAR(100) NOT NULL,
  detalle TEXT NULL,
  estado ENUM('pendiente','en_revision','resuelto','descartado') NOT NULL DEFAULT 'pendiente',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_calificacion_reporte),
  UNIQUE KEY uq_calificacion_reporte (codigo_calificacion, codigo_usuario_reporta),
  CONSTRAINT fk_calif_reporte_calificacion
    FOREIGN KEY (codigo_calificacion) REFERENCES calificacion(codigo_calificacion)
    ON DELETE CASCADE,
  CONSTRAINT fk_calif_reporte_pedido
    FOREIGN KEY (codigo_pedido) REFERENCES pedido(codigo_pedido),
  CONSTRAINT fk_calif_reporte_reporta
    FOREIGN KEY (codigo_usuario_reporta) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_calif_reporte_reportado
    FOREIGN KEY (codigo_usuario_reportado) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

-- ============================================================
-- 5. Solicitudes de servicio
-- ============================================================

CREATE TABLE solicitud_servicio (
  codigo_solicitud_servicio BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_producto INT UNSIGNED NOT NULL,
  codigo_usuario_solicitante INT UNSIGNED NOT NULL,
  codigo_usuario_proveedor INT UNSIGNED NOT NULL,
  codigo_usuario_residencia_solicitante INT UNSIGNED NULL,
  codigo_usuario_residencia_proveedor INT UNSIGNED NULL,
  precio_referencial DECIMAL(12,2) NULL,
  fecha_deseada DATE NULL,
  rango_horario VARCHAR(150) NULL,
  direccion_atencion VARCHAR(500) NOT NULL,
  mensaje_solicitante TEXT NULL,
  estado VARCHAR(100) NOT NULL DEFAULT 'pendiente_proveedor',
  estado_anterior VARCHAR(100) NULL,
  motivo_estado VARCHAR(500) NULL,
  fecha_limite_respuesta DATETIME NULL,
  fecha_aceptacion DATETIME NULL,
  fecha_rechazo DATETIME NULL,
  fecha_cancelacion DATETIME NULL,
  fecha_cierre DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_solicitud_servicio),
  KEY idx_ss_producto (codigo_producto),
  KEY idx_ss_solicitante_estado (codigo_usuario_solicitante, estado),
  KEY idx_ss_proveedor_estado (codigo_usuario_proveedor, estado),
  KEY idx_ss_limite (fecha_limite_respuesta),
  CONSTRAINT fk_ss_producto
    FOREIGN KEY (codigo_producto) REFERENCES producto(codigo_producto),
  CONSTRAINT fk_ss_solicitante
    FOREIGN KEY (codigo_usuario_solicitante) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_ss_proveedor
    FOREIGN KEY (codigo_usuario_proveedor) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_ss_residencia_solicitante
    FOREIGN KEY (codigo_usuario_residencia_solicitante) REFERENCES usuario_residencia(codigo_usuario_residencia),
  CONSTRAINT fk_ss_residencia_proveedor
    FOREIGN KEY (codigo_usuario_residencia_proveedor) REFERENCES usuario_residencia(codigo_usuario_residencia)
) ENGINE=InnoDB;

CREATE TABLE solicitud_servicio_interaccion (
  codigo_solicitud_servicio_interaccion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_solicitud_servicio BIGINT UNSIGNED NOT NULL,
  codigo_usuario_autor INT UNSIGNED NOT NULL,
  rol_autor VARCHAR(30) NOT NULL,
  tipo_interaccion VARCHAR(80) NOT NULL,
  mensaje TEXT NULL,
  payload_json LONGTEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_solicitud_servicio_interaccion),
  KEY idx_ssi_solicitud_fecha (codigo_solicitud_servicio, created_at),
  CONSTRAINT fk_ssi_solicitud
    FOREIGN KEY (codigo_solicitud_servicio) REFERENCES solicitud_servicio(codigo_solicitud_servicio)
    ON DELETE CASCADE,
  CONSTRAINT fk_ssi_autor
    FOREIGN KEY (codigo_usuario_autor) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

CREATE TABLE solicitud_servicio_propuesta (
  codigo_solicitud_servicio_propuesta BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_solicitud_servicio BIGINT UNSIGNED NOT NULL,
  codigo_usuario_proveedor INT UNSIGNED NOT NULL,
  version INT NOT NULL DEFAULT 1,
  modalidad VARCHAR(80) NULL,
  momento_tipo VARCHAR(80) NULL,
  fecha_propuesta DATETIME NULL,
  horario_propuesto VARCHAR(150) NULL,
  alcance_confirmado TEXT NULL,
  tipo_precio VARCHAR(80) NULL,
  monto_propuesto DECIMAL(12,2) NULL,
  unidad_precio VARCHAR(80) NULL,
  duracion_estimada VARCHAR(150) NULL,
  requisitos TEXT NULL,
  mensaje_proveedor TEXT NULL,
  estado ENUM('vigente','aceptada','reemplazada','cancelada_solicitante') NOT NULL DEFAULT 'vigente',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_solicitud_servicio_propuesta),
  UNIQUE KEY uq_ssp_version (codigo_solicitud_servicio, version),
  KEY idx_ssp_estado (codigo_solicitud_servicio, estado),
  CONSTRAINT fk_ssp_solicitud
    FOREIGN KEY (codigo_solicitud_servicio) REFERENCES solicitud_servicio(codigo_solicitud_servicio)
    ON DELETE CASCADE,
  CONSTRAINT fk_ssp_proveedor
    FOREIGN KEY (codigo_usuario_proveedor) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

-- ============================================================
-- 6. Comunidad, notificaciones y trazabilidad
-- ============================================================

CREATE TABLE comunidad_publicacion (
  codigo_publicacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo_publicacion ENUM('comunicado','anuncio','evento','noticia') NOT NULL DEFAULT 'comunicado',
  origen_publicacion VARCHAR(80) NOT NULL DEFAULT 'administracion_comunidad',
  alcance VARCHAR(50) NOT NULL DEFAULT 'comunidad',
  tipo_conjunto ENUM('condominio','urbanizacion') NOT NULL,
  codigo_condominio INT UNSIGNED NULL,
  codigo_urbanizacion INT UNSIGNED NULL,
  titulo VARCHAR(200) NOT NULL,
  resumen VARCHAR(500) NULL,
  contenido LONGTEXT NULL,
  imagen_portada VARCHAR(255) NULL,
  prioridad TINYINT NOT NULL DEFAULT 1,
  destacado_dashboard TINYINT(1) NOT NULL DEFAULT 0,
  estado ENUM('borrador','publicado','ocultado_moderacion','desactivado') NOT NULL DEFAULT 'borrador',
  fecha_publicacion DATETIME NULL,
  fecha_expiracion DATETIME NULL,
  fecha_evento_inicio DATETIME NULL,
  fecha_evento_fin DATETIME NULL,
  ubicacion_evento VARCHAR(255) NULL,
  codigo_usuario_creacion INT UNSIGNED NOT NULL,
  codigo_usuario_publicacion INT UNSIGNED NULL,
  codigo_usuario_modificacion INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_publicacion),
  KEY idx_cp_destino_estado (tipo_conjunto, codigo_condominio, codigo_urbanizacion, estado),
  KEY idx_cp_dashboard (destacado_dashboard, estado, fecha_publicacion),
  CONSTRAINT fk_cp_condominio
    FOREIGN KEY (codigo_condominio) REFERENCES condominio(codigo_condominio),
  CONSTRAINT fk_cp_urbanizacion
    FOREIGN KEY (codigo_urbanizacion) REFERENCES urbanizacion(codigo_urbanizacion),
  CONSTRAINT fk_cp_creacion
    FOREIGN KEY (codigo_usuario_creacion) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_cp_publicacion
    FOREIGN KEY (codigo_usuario_publicacion) REFERENCES usuario(codigo_usuario),
  CONSTRAINT fk_cp_modificacion
    FOREIGN KEY (codigo_usuario_modificacion) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

CREATE TABLE comunidad_publicacion_historial (
  codigo_historial BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_publicacion BIGINT UNSIGNED NOT NULL,
  codigo_usuario_accion INT UNSIGNED NOT NULL,
  accion VARCHAR(80) NOT NULL,
  estado_anterior VARCHAR(80) NULL,
  estado_nuevo VARCHAR(80) NULL,
  motivo VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_historial),
  KEY idx_cph_publicacion_fecha (codigo_publicacion, created_at),
  CONSTRAINT fk_cph_publicacion
    FOREIGN KEY (codigo_publicacion) REFERENCES comunidad_publicacion(codigo_publicacion)
    ON DELETE CASCADE,
  CONSTRAINT fk_cph_usuario
    FOREIGN KEY (codigo_usuario_accion) REFERENCES usuario(codigo_usuario)
) ENGINE=InnoDB;

CREATE TABLE notificacion (
  codigo_notificacion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  canal VARCHAR(30) NOT NULL DEFAULT 'app',
  categoria VARCHAR(50) NOT NULL,
  subcategoria VARCHAR(80) NOT NULL DEFAULT '',
  referencia_id BIGINT NULL,
  titulo VARCHAR(160) NOT NULL,
  mensaje VARCHAR(1000) NOT NULL,
  payload_json LONGTEXT NULL,
  estado ENUM('no_leida','leida') NOT NULL DEFAULT 'no_leida',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME NULL,
  PRIMARY KEY (codigo_notificacion),
  KEY idx_notif_usuario_estado (codigo_usuario, estado, created_at),
  KEY idx_notif_categoria (categoria),
  KEY idx_notif_referencia (referencia_id),
  CONSTRAINT fk_notificacion_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
