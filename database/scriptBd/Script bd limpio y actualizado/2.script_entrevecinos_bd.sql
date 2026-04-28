USE entre_vecinos;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- 1. CATEGORÍAS / TIPOS
-- =========================================================
CREATE TABLE categoria_grupo (
  codigo_categoria_grupo INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_categoria_grupo),
  UNIQUE KEY uk_categoria_grupo_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipo (
  codigo_tipo INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_categoria_grupo INT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_tipo),
  KEY idx_tipo_categoria (codigo_categoria_grupo),
  CONSTRAINT fk_tipo_categoria_grupo
    FOREIGN KEY (codigo_categoria_grupo) REFERENCES categoria_grupo(codigo_categoria_grupo)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 2. PRODUCTO / PUBLICACIÓN
-- visible:
-- 0=borrador
-- 1=pendiente_revision
-- 2=aprobado/publicado
-- 3=rechazado
-- 4=anulado
-- =========================================================
CREATE TABLE producto (
  codigo_producto INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  codigo_tipo INT UNSIGNED NOT NULL,
  titulo VARCHAR(180) NOT NULL,
  descripcion TEXT NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock INT NOT NULL DEFAULT 0,
  visible TINYINT(1) NOT NULL DEFAULT 0,
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  fecha_destacado DATETIME NULL,
  requiere_preparacion TINYINT(1) NOT NULL DEFAULT 0,
  comentario_revision TEXT NULL,
  codigo_usuario_soporte_revision INT UNSIGNED NULL,
  fecha_revision DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_producto),
  KEY idx_producto_usuario (codigo_usuario),
  KEY idx_producto_tipo (codigo_tipo),
  KEY idx_producto_visible (visible),
  KEY idx_producto_destacado (destacado),
  CONSTRAINT fk_producto_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_producto_tipo
    FOREIGN KEY (codigo_tipo) REFERENCES tipo(codigo_tipo)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_producto_soporte_revision
    FOREIGN KEY (codigo_usuario_soporte_revision) REFERENCES usuario(codigo_usuario)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE producto_imagen (
  codigo_producto_imagen INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_producto INT UNSIGNED NOT NULL,
  ruta VARCHAR(255) NOT NULL,
  es_portada TINYINT(1) NOT NULL DEFAULT 0,
  orden INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_producto_imagen),
  KEY idx_producto_imagen_producto (codigo_producto),
  KEY idx_producto_imagen_portada (codigo_producto, es_portada),
  CONSTRAINT fk_producto_imagen_producto
    FOREIGN KEY (codigo_producto) REFERENCES producto(codigo_producto)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 3. BILLETERA
-- =========================================================
CREATE TABLE billetera (
  codigo_billetera INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  saldo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_billetera),
  UNIQUE KEY uk_billetera_usuario (codigo_usuario),
  CONSTRAINT fk_billetera_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE billetera_movimiento (
  codigo_movimiento INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_billetera INT UNSIGNED NOT NULL,
  tipo_movimiento ENUM('abono','cargo','devolucion','bono') NOT NULL,
  concepto VARCHAR(150) NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  saldo_anterior DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  saldo_posterior DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  referencia_tipo VARCHAR(50) NULL,
  referencia_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_movimiento),
  KEY idx_bm_billetera (codigo_billetera),
  KEY idx_bm_referencia (referencia_tipo, referencia_id),
  KEY idx_bm_created (created_at),
  CONSTRAINT fk_bm_billetera
    FOREIGN KEY (codigo_billetera) REFERENCES billetera(codigo_billetera)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 4. RECARGA DE SALDO
-- =========================================================
CREATE TABLE recarga_saldo (
  codigo_recarga INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_usuario INT UNSIGNED NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  metodo ENUM('yape','plin','transferencia') NOT NULL,
  id_operacion VARCHAR(100) NOT NULL,
  comprobante_path VARCHAR(255) NOT NULL,
  estado ENUM('pendiente','observada','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  comentario_soporte TEXT NULL,
  reenviada_usuario TINYINT(1) NOT NULL DEFAULT 0,
  codigo_usuario_soporte INT UNSIGNED NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_revision DATETIME NULL,
  fecha_actualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_recarga),
  KEY idx_recarga_usuario (codigo_usuario),
  KEY idx_recarga_estado (estado),
  KEY idx_recarga_operacion (id_operacion),
  KEY idx_recarga_revision (fecha_revision),
  CONSTRAINT fk_recarga_usuario
    FOREIGN KEY (codigo_usuario) REFERENCES usuario(codigo_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_recarga_soporte
    FOREIGN KEY (codigo_usuario_soporte) REFERENCES usuario(codigo_usuario)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;