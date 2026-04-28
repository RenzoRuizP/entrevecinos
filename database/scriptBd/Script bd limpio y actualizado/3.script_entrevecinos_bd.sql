USE entre_vecinos;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- 1. PEDIDO / SOLICITUD
-- =========================================================
CREATE TABLE pedido (
  codigo_pedido INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_producto INT UNSIGNED NOT NULL,
  codigo_usuario_comprador INT UNSIGNED NOT NULL,
  codigo_usuario_vendedor INT UNSIGNED NOT NULL,
  codigo_usuario_residencia_comprador INT UNSIGNED NULL,
  codigo_usuario_residencia_vendedor INT UNSIGNED NULL,

  cantidad INT NOT NULL DEFAULT 1,
  precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  monto_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,

  tipo_entrega ENUM('inmediata','programada') NOT NULL DEFAULT 'inmediata',
  fecha_programada DATETIME NULL,
  direccion_entrega VARCHAR(255) NULL,
  mensaje_comprador TEXT NULL,

  requiere_preparacion TINYINT(1) NOT NULL DEFAULT 0,

  estado VARCHAR(50) NOT NULL DEFAULT 'pendiente_vendedor',
  estado_anterior VARCHAR(50) NULL,

  posicion_cola INT NULL,
  confirmado_cola TINYINT(1) NOT NULL DEFAULT 0,
  fecha_confirmacion_cola DATETIME NULL,

  fecha_limite_respuesta DATETIME NULL,

  entrega_confirmada_comprador TINYINT(1) NOT NULL DEFAULT 0,
  fecha_confirmacion_entrega DATETIME NULL,

  motivo_rechazo VARCHAR(255) NULL,
  motivo_cancelacion VARCHAR(255) NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (codigo_pedido),

  KEY idx_pedido_producto (codigo_producto),
  KEY idx_pedido_comprador (codigo_usuario_comprador),
  KEY idx_pedido_vendedor (codigo_usuario_vendedor),
  KEY idx_pedido_estado (estado),
  KEY idx_pedido_fecha_programada (fecha_programada),
  KEY idx_pedido_created_at (created_at),
  KEY idx_pedido_comprador_estado (codigo_usuario_comprador, estado),
  KEY idx_pedido_vendedor_estado (codigo_usuario_vendedor, estado),
  KEY idx_pedido_publicacion_estado (codigo_producto, estado),
  KEY idx_pedido_cola_vendedor (codigo_usuario_vendedor, posicion_cola),

  CONSTRAINT fk_pedido_producto
    FOREIGN KEY (codigo_producto) REFERENCES producto(codigo_producto)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  CONSTRAINT fk_pedido_comprador
    FOREIGN KEY (codigo_usuario_comprador) REFERENCES usuario(codigo_usuario)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  CONSTRAINT fk_pedido_vendedor
    FOREIGN KEY (codigo_usuario_vendedor) REFERENCES usuario(codigo_usuario)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  CONSTRAINT fk_pedido_residencia_comprador
    FOREIGN KEY (codigo_usuario_residencia_comprador) REFERENCES usuario_residencia(codigo_usuario_residencia)
    ON DELETE SET NULL ON UPDATE CASCADE,

  CONSTRAINT fk_pedido_residencia_vendedor
    FOREIGN KEY (codigo_usuario_residencia_vendedor) REFERENCES usuario_residencia(codigo_usuario_residencia)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 2. HISTORIAL PEDIDO
-- =========================================================
CREATE TABLE pedido_historial (
  codigo_pedido_historial INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_pedido INT UNSIGNED NOT NULL,
  estado VARCHAR(50) NOT NULL,
  detalle TEXT NULL,
  codigo_usuario_actor INT UNSIGNED NULL,
  actor_tipo VARCHAR(30) NULL,
  metadata_json LONGTEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_pedido_historial),
  KEY idx_pedido_historial_pedido (codigo_pedido),
  KEY idx_pedido_historial_estado (estado),
  KEY idx_pedido_historial_actor (codigo_usuario_actor),
  KEY idx_pedido_historial_created (created_at),
  CONSTRAINT fk_pedido_historial_pedido
    FOREIGN KEY (codigo_pedido) REFERENCES pedido(codigo_pedido)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_historial_actor
    FOREIGN KEY (codigo_usuario_actor) REFERENCES usuario(codigo_usuario)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 3. TIPOS DE INCIDENCIA
-- =========================================================
CREATE TABLE pedido_incidencia_tipo (
  codigo_tipo_incidencia INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (codigo_tipo_incidencia),
  UNIQUE KEY uk_pedido_incidencia_tipo_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 4. INCIDENCIA PEDIDO
-- =========================================================
CREATE TABLE pedido_incidencia (
  codigo_pedido_incidencia INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_pedido INT UNSIGNED NOT NULL,
  tipo_incidencia INT UNSIGNED NOT NULL,
  codigo_usuario_reporta INT UNSIGNED NULL,
  codigo_usuario_afectado INT UNSIGNED NULL,
  descripcion TEXT NOT NULL,
  estado VARCHAR(40) NOT NULL DEFAULT 'abierta',
  resolucion TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_pedido_incidencia),
  KEY idx_pedido_incidencia_pedido (codigo_pedido),
  KEY idx_pedido_incidencia_tipo (tipo_incidencia),
  KEY idx_pedido_incidencia_estado (estado),
  KEY idx_pedido_incidencia_reporta (codigo_usuario_reporta),
  KEY idx_pedido_incidencia_afectado (codigo_usuario_afectado),
  CONSTRAINT fk_pedido_incidencia_pedido
    FOREIGN KEY (codigo_pedido) REFERENCES pedido(codigo_pedido)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_incidencia_tipo
    FOREIGN KEY (tipo_incidencia) REFERENCES pedido_incidencia_tipo(codigo_tipo_incidencia)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_incidencia_reporta
    FOREIGN KEY (codigo_usuario_reporta) REFERENCES usuario(codigo_usuario)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_incidencia_afectado
    FOREIGN KEY (codigo_usuario_afectado) REFERENCES usuario(codigo_usuario)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 5. NOTA DE SOPORTE
-- =========================================================
CREATE TABLE pedido_nota_soporte (
  codigo_pedido_nota INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_pedido INT UNSIGNED NOT NULL,
  codigo_usuario_soporte INT UNSIGNED NOT NULL,
  nota TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (codigo_pedido_nota),
  KEY idx_pedido_nota_pedido (codigo_pedido),
  KEY idx_pedido_nota_soporte (codigo_usuario_soporte),
  KEY idx_pedido_nota_fecha (created_at),
  CONSTRAINT fk_pedido_nota_pedido
    FOREIGN KEY (codigo_pedido) REFERENCES pedido(codigo_pedido)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_nota_soporte
    FOREIGN KEY (codigo_usuario_soporte) REFERENCES usuario(codigo_usuario)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;