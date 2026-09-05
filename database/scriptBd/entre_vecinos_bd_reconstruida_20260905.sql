-- ============================================================================
-- ENTRE VECINOS (EV) - BASE DE DATOS RECONSTRUIDA DESDE CÓDIGO FUENTE VIGENTE
-- Fuente auditada: entrevecinos(20260905-045905).zip
-- Generado: 2026-09-05
-- Objetivo: instalación limpia DEV/QA sobre MariaDB 10.4.x / XAMPP 8.2.12
--
-- IMPORTANTE: este script ELIMINA y vuelve a crear la base `entre_vecinos`.
-- No reutiliza archivos físicos de la antigua instalación corrupta.
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '-05:00';
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `entre_vecinos`;
CREATE DATABASE `entre_vecinos` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `entre_vecinos`;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `administrador_comunidad` (
  `codigo_administrador_comunidad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `tipo_conjunto` enum('condominio','urbanizacion') NOT NULL,
  `codigo_condominio` int(10) unsigned DEFAULT NULL,
  `codigo_urbanizacion` int(10) unsigned DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_administrador_comunidad`),
  KEY `idx_admin_comunidad_usuario` (`codigo_usuario`,`estado`),
  KEY `idx_admin_comunidad_condominio` (`codigo_condominio`),
  KEY `idx_admin_comunidad_urbanizacion` (`codigo_urbanizacion`),
  CONSTRAINT `fk_admin_comunidad_condominio` FOREIGN KEY (`codigo_condominio`) REFERENCES `condominio` (`codigo_condominio`),
  CONSTRAINT `fk_admin_comunidad_urbanizacion` FOREIGN KEY (`codigo_urbanizacion`) REFERENCES `urbanizacion` (`codigo_urbanizacion`),
  CONSTRAINT `fk_admin_comunidad_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `billetera` (
  `codigo_billetera` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo_actual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_billetera`),
  UNIQUE KEY `uq_billetera_usuario` (`codigo_usuario`),
  CONSTRAINT `fk_billetera_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `billetera_movimiento` (
  `codigo_movimiento` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_billetera` int(10) unsigned NOT NULL,
  `tipo_movimiento` enum('C','D') NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `saldo_antes` decimal(12,2) NOT NULL,
  `saldo_despues` decimal(12,2) NOT NULL,
  `saldo_anterior` decimal(12,2) NOT NULL,
  `saldo_posterior` decimal(12,2) NOT NULL,
  `concepto` varchar(150) DEFAULT NULL,
  `origen` varchar(80) NOT NULL,
  `referencia_tipo` varchar(80) DEFAULT NULL,
  `referencia_id` bigint(20) DEFAULT NULL,
  `codigo_referencia` bigint(20) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `es_promocional` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_expira` datetime DEFAULT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_movimiento`),
  KEY `idx_mov_billetera_fecha` (`codigo_billetera`,`fecha_movimiento`),
  KEY `idx_mov_origen` (`origen`),
  KEY `idx_mov_referencia` (`codigo_referencia`),
  CONSTRAINT `fk_mov_billetera` FOREIGN KEY (`codigo_billetera`) REFERENCES `billetera` (`codigo_billetera`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calificacion` (
  `codigo_calificacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_pedido` int(10) unsigned NOT NULL,
  `codigo_usuario_calificador` int(10) unsigned NOT NULL,
  `codigo_usuario_calificado` int(10) unsigned NOT NULL,
  `rol_calificador` enum('comprador','vendedor') NOT NULL,
  `rol_calificado` enum('comprador','vendedor') NOT NULL,
  `tipo_calificacion` varchar(60) NOT NULL,
  `puntaje` tinyint(4) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `estado` enum('pendiente','enviada','vencida') NOT NULL DEFAULT 'pendiente',
  `fecha_habilitacion` datetime NOT NULL,
  `fecha_limite` datetime NOT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_calificacion`),
  UNIQUE KEY `uq_calificacion_pedido_sentido` (`codigo_pedido`,`codigo_usuario_calificador`,`codigo_usuario_calificado`),
  KEY `idx_calificacion_calificado` (`codigo_usuario_calificado`,`estado`),
  KEY `fk_calificacion_calificador` (`codigo_usuario_calificador`),
  CONSTRAINT `fk_calificacion_calificado` FOREIGN KEY (`codigo_usuario_calificado`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_calificacion_calificador` FOREIGN KEY (`codigo_usuario_calificador`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_calificacion_pedido` FOREIGN KEY (`codigo_pedido`) REFERENCES `pedido` (`codigo_pedido`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calificacion_etiqueta` (
  `codigo_calificacion_etiqueta` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_calificacion` bigint(20) unsigned NOT NULL,
  `etiqueta` varchar(80) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_calificacion_etiqueta`),
  UNIQUE KEY `uq_calificacion_etiqueta` (`codigo_calificacion`,`etiqueta`),
  CONSTRAINT `fk_calificacion_etiqueta_calificacion` FOREIGN KEY (`codigo_calificacion`) REFERENCES `calificacion` (`codigo_calificacion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calificacion_reporte` (
  `codigo_calificacion_reporte` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_calificacion` bigint(20) unsigned NOT NULL,
  `codigo_pedido` int(10) unsigned NOT NULL,
  `codigo_usuario_reporta` int(10) unsigned NOT NULL,
  `codigo_usuario_reportado` int(10) unsigned NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `detalle` text DEFAULT NULL,
  `estado` enum('pendiente','en_revision','resuelto','descartado') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_calificacion_reporte`),
  UNIQUE KEY `uq_calificacion_reporte` (`codigo_calificacion`,`codigo_usuario_reporta`),
  KEY `fk_calif_reporte_pedido` (`codigo_pedido`),
  KEY `fk_calif_reporte_reporta` (`codigo_usuario_reporta`),
  KEY `fk_calif_reporte_reportado` (`codigo_usuario_reportado`),
  CONSTRAINT `fk_calif_reporte_calificacion` FOREIGN KEY (`codigo_calificacion`) REFERENCES `calificacion` (`codigo_calificacion`) ON DELETE CASCADE,
  CONSTRAINT `fk_calif_reporte_pedido` FOREIGN KEY (`codigo_pedido`) REFERENCES `pedido` (`codigo_pedido`),
  CONSTRAINT `fk_calif_reporte_reporta` FOREIGN KEY (`codigo_usuario_reporta`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_calif_reporte_reportado` FOREIGN KEY (`codigo_usuario_reportado`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calificacion_servicio` (
  `codigo_calificacion_servicio` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_solicitud_servicio` bigint(20) unsigned NOT NULL,
  `codigo_usuario_calificador` int(10) unsigned NOT NULL,
  `codigo_usuario_calificado` int(10) unsigned NOT NULL,
  `rol_calificador` varchar(30) NOT NULL,
  `rol_calificado` varchar(30) NOT NULL,
  `puntaje` tinyint(3) unsigned DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  `fecha_habilitacion` datetime NOT NULL,
  `fecha_limite` datetime NOT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_calificacion_servicio`),
  UNIQUE KEY `uq_cs_servicio_sentido` (`codigo_solicitud_servicio`,`codigo_usuario_calificador`,`codigo_usuario_calificado`),
  KEY `idx_cs_calificador_estado` (`codigo_usuario_calificador`,`estado`,`fecha_limite`),
  KEY `idx_cs_calificado_estado` (`codigo_usuario_calificado`,`estado`),
  CONSTRAINT `fk_cs_calificado` FOREIGN KEY (`codigo_usuario_calificado`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_cs_calificador` FOREIGN KEY (`codigo_usuario_calificador`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_cs_solicitud` FOREIGN KEY (`codigo_solicitud_servicio`) REFERENCES `solicitud_servicio` (`codigo_solicitud_servicio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calificacion_servicio_etiqueta` (
  `codigo_calificacion_servicio_etiqueta` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_calificacion_servicio` bigint(20) unsigned NOT NULL,
  `etiqueta` varchar(80) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_calificacion_servicio_etiqueta`),
  UNIQUE KEY `uq_cse_etiqueta` (`codigo_calificacion_servicio`,`etiqueta`),
  CONSTRAINT `fk_cse_calificacion` FOREIGN KEY (`codigo_calificacion_servicio`) REFERENCES `calificacion_servicio` (`codigo_calificacion_servicio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categoria` (
  `codigo_categoria` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `requiere_preparacion_default` tinyint(1) NOT NULL DEFAULT 0,
  `codigo_tipo` int(10) unsigned NOT NULL,
  `codigo_grupo` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_categoria`),
  UNIQUE KEY `uq_categoria_tipo_nombre` (`codigo_tipo`,`nombre`),
  KEY `idx_categoria_listado` (`codigo_tipo`,`codigo_grupo`,`orden`,`nombre`),
  KEY `fk_categoria_grupo_mismo_tipo` (`codigo_grupo`,`codigo_tipo`),
  CONSTRAINT `fk_categoria_grupo_mismo_tipo` FOREIGN KEY (`codigo_grupo`, `codigo_tipo`) REFERENCES `categoria_grupo` (`codigo_grupo`, `codigo_tipo`) ON UPDATE CASCADE,
  CONSTRAINT `fk_categoria_tipo` FOREIGN KEY (`codigo_tipo`) REFERENCES `tipo` (`codigo_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categoria_grupo` (
  `codigo_grupo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `codigo_tipo` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_grupo`),
  UNIQUE KEY `uq_catgrupo_tipo_nombre` (`codigo_tipo`,`nombre`),
  UNIQUE KEY `uq_catgrupo_id_tipo` (`codigo_grupo`,`codigo_tipo`),
  KEY `idx_catgrupo_listado` (`codigo_tipo`,`orden`,`nombre`),
  CONSTRAINT `fk_catgrupo_tipo` FOREIGN KEY (`codigo_tipo`) REFERENCES `tipo` (`codigo_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comunidad_publicacion` (
  `codigo_publicacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_publicacion` enum('comunicado','anuncio','evento','noticia') NOT NULL DEFAULT 'comunicado',
  `origen_publicacion` varchar(80) NOT NULL DEFAULT 'administracion_comunidad',
  `alcance` varchar(50) NOT NULL DEFAULT 'comunidad',
  `tipo_conjunto` enum('condominio','urbanizacion') NOT NULL,
  `codigo_condominio` int(10) unsigned DEFAULT NULL,
  `codigo_urbanizacion` int(10) unsigned DEFAULT NULL,
  `titulo` varchar(200) NOT NULL,
  `resumen` varchar(500) DEFAULT NULL,
  `contenido` longtext DEFAULT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `prioridad` enum('normal','importante','urgente') NOT NULL DEFAULT 'normal',
  `destacado_dashboard` tinyint(1) NOT NULL DEFAULT 0,
  `notificar_vecinos` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_notificacion` datetime DEFAULT NULL,
  `estado` enum('borrador','publicado','ocultado_moderacion','inactivo') NOT NULL DEFAULT 'borrador',
  `fecha_publicacion` datetime DEFAULT NULL,
  `fecha_expiracion` datetime DEFAULT NULL,
  `fecha_evento_inicio` datetime DEFAULT NULL,
  `fecha_evento_fin` datetime DEFAULT NULL,
  `ubicacion_evento` varchar(255) DEFAULT NULL,
  `codigo_usuario_creacion` int(10) unsigned NOT NULL,
  `codigo_usuario_publicacion` int(10) unsigned DEFAULT NULL,
  `codigo_usuario_modificacion` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_publicacion`),
  KEY `idx_cp_destino_estado` (`tipo_conjunto`,`codigo_condominio`,`codigo_urbanizacion`,`estado`),
  KEY `idx_cp_dashboard` (`destacado_dashboard`,`estado`,`fecha_publicacion`),
  KEY `fk_cp_condominio` (`codigo_condominio`),
  KEY `fk_cp_urbanizacion` (`codigo_urbanizacion`),
  KEY `fk_cp_creacion` (`codigo_usuario_creacion`),
  KEY `fk_cp_publicacion` (`codigo_usuario_publicacion`),
  KEY `fk_cp_modificacion` (`codigo_usuario_modificacion`),
  CONSTRAINT `fk_cp_condominio` FOREIGN KEY (`codigo_condominio`) REFERENCES `condominio` (`codigo_condominio`),
  CONSTRAINT `fk_cp_creacion` FOREIGN KEY (`codigo_usuario_creacion`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_cp_modificacion` FOREIGN KEY (`codigo_usuario_modificacion`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_cp_publicacion` FOREIGN KEY (`codigo_usuario_publicacion`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_cp_urbanizacion` FOREIGN KEY (`codigo_urbanizacion`) REFERENCES `urbanizacion` (`codigo_urbanizacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comunidad_publicacion_historial` (
  `codigo_historial` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_publicacion` bigint(20) unsigned NOT NULL,
  `codigo_usuario_accion` int(10) unsigned NOT NULL,
  `accion` varchar(80) NOT NULL,
  `estado_anterior` varchar(80) DEFAULT NULL,
  `estado_nuevo` varchar(80) DEFAULT NULL,
  `motivo` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_historial`),
  KEY `idx_cph_publicacion_fecha` (`codigo_publicacion`,`created_at`),
  KEY `fk_cph_usuario` (`codigo_usuario_accion`),
  CONSTRAINT `fk_cph_publicacion` FOREIGN KEY (`codigo_publicacion`) REFERENCES `comunidad_publicacion` (`codigo_publicacion`) ON DELETE CASCADE,
  CONSTRAINT `fk_cph_usuario` FOREIGN KEY (`codigo_usuario_accion`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `condominio` (
  `codigo_condominio` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_condominio` varchar(200) NOT NULL,
  `direccion_condominio` varchar(300) NOT NULL,
  `codigo_distrito` int(11) DEFAULT NULL,
  `estado` char(1) NOT NULL DEFAULT 'A',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_condominio`),
  KEY `idx_condominio_distrito` (`codigo_distrito`),
  CONSTRAINT `fk_condominio_distrito` FOREIGN KEY (`codigo_distrito`) REFERENCES `ubigeo_distrito` (`codigo_distrito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `departamento` (
  `codigo_departamento` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_torre` int(10) unsigned DEFAULT NULL,
  `numero_departamento` varchar(30) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_departamento`),
  KEY `idx_departamento_torre` (`codigo_torre`),
  CONSTRAINT `fk_departamento_torre` FOREIGN KEY (`codigo_torre`) REFERENCES `torre` (`codigo_torre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `documento_legal` (
  `codigo_documento_legal` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(60) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `titulo` varchar(220) NOT NULL,
  `version` varchar(30) NOT NULL,
  `archivo_contenido` varchar(180) NOT NULL,
  `texto_consentimiento` varchar(1000) NOT NULL,
  `hash_documento` char(64) DEFAULT NULL,
  `estado` enum('borrador','vigente','inactivo') NOT NULL DEFAULT 'borrador',
  `requiere_aceptacion` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_publicacion` datetime DEFAULT NULL,
  `fecha_vigencia` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_documento_legal`),
  UNIQUE KEY `uq_documento_legal_tipo_version` (`tipo`,`version`),
  UNIQUE KEY `uq_documento_legal_slug_version` (`slug`,`version`),
  KEY `idx_documento_legal_vigente` (`tipo`,`estado`,`requiere_aceptacion`,`fecha_vigencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ev_funcionalidad` (
  `codigo_funcionalidad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(80) NOT NULL,
  `nombre` varchar(160) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `valor_defecto` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_funcionalidad`),
  UNIQUE KEY `uq_ev_funcionalidad_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ev_funcionalidad_configuracion` (
  `codigo_funcionalidad_configuracion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_funcionalidad` int(10) unsigned NOT NULL,
  `tipo_alcance` enum('global','condominio','urbanizacion') NOT NULL DEFAULT 'global',
  `codigo_alcance` int(10) unsigned NOT NULL DEFAULT 0,
  `habilitada` tinyint(1) NOT NULL DEFAULT 1,
  `modo_activacion` enum('manual','programado') NOT NULL DEFAULT 'manual',
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `mensaje_usuario` varchar(500) DEFAULT NULL,
  `motivo` varchar(500) DEFAULT NULL,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `estado_registro` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_funcionalidad_configuracion`),
  UNIQUE KEY `uq_ev_funcionalidad_alcance` (`codigo_funcionalidad`,`tipo_alcance`,`codigo_alcance`),
  KEY `idx_ev_funcionalidad_alcance` (`tipo_alcance`,`codigo_alcance`,`estado_registro`),
  KEY `idx_ev_funcionalidad_admin` (`actualizado_por`),
  CONSTRAINT `fk_ev_funcionalidad_config_admin` FOREIGN KEY (`actualizado_por`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_ev_funcionalidad_config_catalogo` FOREIGN KEY (`codigo_funcionalidad`) REFERENCES `ev_funcionalidad` (`codigo_funcionalidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ev_funcionalidad_configuracion_historial` (
  `codigo_historial` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_funcionalidad` int(10) unsigned NOT NULL,
  `tipo_alcance` enum('global','condominio','urbanizacion') NOT NULL,
  `codigo_alcance` int(10) unsigned NOT NULL DEFAULT 0,
  `valor_anterior_json` longtext DEFAULT NULL,
  `valor_nuevo_json` longtext DEFAULT NULL,
  `motivo` varchar(500) DEFAULT NULL,
  `codigo_usuario_admin` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_historial`),
  KEY `idx_ev_func_hist_alcance` (`tipo_alcance`,`codigo_alcance`,`created_at`),
  KEY `idx_ev_func_hist_funcionalidad` (`codigo_funcionalidad`,`created_at`),
  KEY `fk_ev_func_hist_admin` (`codigo_usuario_admin`),
  CONSTRAINT `fk_ev_func_hist_admin` FOREIGN KEY (`codigo_usuario_admin`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_ev_func_hist_catalogo` FOREIGN KEY (`codigo_funcionalidad`) REFERENCES `ev_funcionalidad` (`codigo_funcionalidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ev_meta_gerencial` (
  `codigo_meta` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `periodo_tipo` enum('dia','mes','semestre','anio','personalizado') NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `tipo_alcance` enum('global','departamento','provincia','distrito','condominio','urbanizacion') NOT NULL DEFAULT 'global',
  `codigo_alcance` int(10) unsigned NOT NULL DEFAULT 0,
  `monto_objetivo` decimal(14,2) NOT NULL DEFAULT 0.00,
  `creado_por` int(10) unsigned DEFAULT NULL,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_meta`),
  UNIQUE KEY `uq_ev_meta_periodo_alcance` (`periodo_tipo`,`fecha_inicio`,`fecha_fin`,`tipo_alcance`,`codigo_alcance`),
  KEY `idx_ev_meta_vigente` (`estado`,`periodo_tipo`,`fecha_inicio`,`fecha_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ev_monetizacion_configuracion` (
  `codigo_monetizacion_configuracion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_monetizacion_regla` int(10) unsigned NOT NULL,
  `tipo_alcance` enum('global','condominio','urbanizacion') NOT NULL DEFAULT 'global',
  `codigo_alcance` int(10) unsigned NOT NULL DEFAULT 0,
  `valor_decimal` decimal(12,4) DEFAULT NULL,
  `valor_booleano` tinyint(1) DEFAULT NULL,
  `valor_texto` varchar(255) DEFAULT NULL,
  `modo_activacion` enum('manual','programado') NOT NULL DEFAULT 'manual',
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `motivo` varchar(500) DEFAULT NULL,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `estado_registro` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_monetizacion_configuracion`),
  UNIQUE KEY `uq_ev_monetizacion_alcance` (`codigo_monetizacion_regla`,`tipo_alcance`,`codigo_alcance`),
  KEY `idx_ev_monetizacion_alcance` (`tipo_alcance`,`codigo_alcance`,`estado_registro`),
  KEY `idx_ev_monetizacion_admin` (`actualizado_por`),
  CONSTRAINT `fk_ev_monetizacion_config_admin` FOREIGN KEY (`actualizado_por`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_ev_monetizacion_config_regla` FOREIGN KEY (`codigo_monetizacion_regla`) REFERENCES `ev_monetizacion_regla` (`codigo_monetizacion_regla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ev_monetizacion_configuracion_historial` (
  `codigo_historial` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_monetizacion_regla` int(10) unsigned NOT NULL,
  `tipo_alcance` enum('global','condominio','urbanizacion') NOT NULL,
  `codigo_alcance` int(10) unsigned NOT NULL DEFAULT 0,
  `valor_anterior_json` longtext DEFAULT NULL,
  `valor_nuevo_json` longtext DEFAULT NULL,
  `motivo` varchar(500) DEFAULT NULL,
  `codigo_usuario_admin` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_historial`),
  KEY `idx_ev_mon_hist_alcance` (`tipo_alcance`,`codigo_alcance`,`created_at`),
  KEY `idx_ev_mon_hist_regla` (`codigo_monetizacion_regla`,`created_at`),
  KEY `fk_ev_mon_hist_admin` (`codigo_usuario_admin`),
  CONSTRAINT `fk_ev_mon_hist_admin` FOREIGN KEY (`codigo_usuario_admin`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_ev_mon_hist_regla` FOREIGN KEY (`codigo_monetizacion_regla`) REFERENCES `ev_monetizacion_regla` (`codigo_monetizacion_regla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ev_monetizacion_regla` (
  `codigo_monetizacion_regla` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `nombre` varchar(180) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `tipo_valor` enum('booleano','porcentaje','monto','decimal','texto') NOT NULL,
  `unidad` varchar(30) DEFAULT NULL,
  `valor_decimal_defecto` decimal(12,4) DEFAULT NULL,
  `valor_booleano_defecto` tinyint(1) DEFAULT NULL,
  `valor_texto_defecto` varchar(255) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_monetizacion_regla`),
  UNIQUE KEY `uq_ev_monetizacion_regla_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `libro_reclamacion` (
  `codigo_libro_reclamacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero_hoja` varchar(32) DEFAULT NULL,
  `token_hash` char(64) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `consumidor_nombres` varchar(120) NOT NULL,
  `consumidor_apellidos` varchar(120) NOT NULL,
  `tipo_documento` enum('DNI','CE','PASAPORTE','RUC','OTRO') NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `domicilio` varchar(250) NOT NULL,
  `departamento` varchar(80) DEFAULT NULL,
  `provincia` varchar(80) DEFAULT NULL,
  `distrito` varchar(80) DEFAULT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(180) NOT NULL,
  `es_menor` tinyint(1) NOT NULL DEFAULT 0,
  `representante_nombres` varchar(120) DEFAULT NULL,
  `representante_apellidos` varchar(120) DEFAULT NULL,
  `representante_tipo_documento` enum('DNI','CE','PASAPORTE','RUC','OTRO') DEFAULT NULL,
  `representante_numero_documento` varchar(20) DEFAULT NULL,
  `representante_telefono` varchar(20) DEFAULT NULL,
  `representante_correo` varchar(180) DEFAULT NULL,
  `tipo_bien` enum('producto','servicio') NOT NULL,
  `descripcion_bien` varchar(500) NOT NULL,
  `monto_reclamado` decimal(12,2) DEFAULT NULL,
  `tipo_registro` enum('reclamo','queja') NOT NULL,
  `detalle` text NOT NULL,
  `pedido_concreto` text NOT NULL,
  `constancia_aviso_privacidad` tinyint(1) NOT NULL DEFAULT 1,
  `version_privacidad` varchar(20) NOT NULL,
  `declara_veracidad` tinyint(1) NOT NULL DEFAULT 1,
  `estado` enum('registrado','en_revision','respondido','cerrado') NOT NULL DEFAULT 'registrado',
  `respuesta_publica` text DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  `medio_respuesta` enum('correo','telefono','domicilio','otro') DEFAULT NULL,
  `responsable_atencion` varchar(180) DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `ip_registro` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_libro_reclamacion`),
  UNIQUE KEY `uk_libro_token_hash` (`token_hash`),
  UNIQUE KEY `uk_libro_numero_hoja` (`numero_hoja`),
  KEY `idx_libro_fecha` (`fecha_registro`),
  KEY `idx_libro_estado` (`estado`),
  KEY `idx_libro_tipo_registro` (`tipo_registro`),
  KEY `idx_libro_correo` (`correo`),
  KEY `idx_libro_documento` (`numero_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `libro_reclamacion_historial` (
  `codigo_historial` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_libro_reclamacion` bigint(20) unsigned NOT NULL,
  `evento` varchar(80) NOT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo` varchar(30) DEFAULT NULL,
  `detalle` varchar(1000) DEFAULT NULL,
  `actor` varchar(180) NOT NULL,
  `ip_actor` varchar(45) DEFAULT NULL,
  `fecha_evento` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_historial`),
  KEY `idx_libro_historial_reclamo` (`codigo_libro_reclamacion`,`fecha_evento`),
  CONSTRAINT `fk_libro_historial_reclamo` FOREIGN KEY (`codigo_libro_reclamacion`) REFERENCES `libro_reclamacion` (`codigo_libro_reclamacion`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menu` (
  `codigo_menu` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menu_item` (
  `codigo_menu_item` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_menu` int(10) unsigned NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `ruta` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo_menu_item`),
  KEY `idx_menu_item_menu` (`codigo_menu`,`orden`),
  CONSTRAINT `fk_menu_item_menu` FOREIGN KEY (`codigo_menu`) REFERENCES `menu` (`codigo_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notificacion` (
  `codigo_notificacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `canal` varchar(30) NOT NULL DEFAULT 'app',
  `categoria` varchar(50) NOT NULL,
  `subcategoria` varchar(80) NOT NULL DEFAULT '',
  `referencia_id` bigint(20) DEFAULT NULL,
  `titulo` varchar(160) NOT NULL,
  `mensaje` varchar(1000) NOT NULL,
  `payload_json` longtext DEFAULT NULL,
  `estado` enum('no_leida','leida') NOT NULL DEFAULT 'no_leida',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo_notificacion`),
  KEY `idx_notif_usuario_estado` (`codigo_usuario`,`estado`,`created_at`),
  KEY `idx_notif_categoria` (`categoria`),
  KEY `idx_notif_referencia` (`referencia_id`),
  KEY `idx_notif_usuario_categoria_estado` (`codigo_usuario`,`categoria`,`estado`,`created_at`),
  KEY `idx_notif_usuario_evento` (`codigo_usuario`,`categoria`,`subcategoria`,`referencia_id`,`estado`),
  CONSTRAINT `fk_notificacion_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pedido` (
  `codigo_pedido` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_producto` int(10) unsigned NOT NULL,
  `codigo_usuario_comprador` int(10) unsigned NOT NULL,
  `codigo_usuario_vendedor` int(10) unsigned NOT NULL,
  `fase` enum('solicitud','pedido') NOT NULL DEFAULT 'solicitud',
  `estado_actual` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monto_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tipo_entrega` enum('inmediata','programada') NOT NULL DEFAULT 'inmediata',
  `fecha_hora_programada` datetime DEFAULT NULL,
  `fecha_programada` date DEFAULT NULL,
  `direccion_entrega` text NOT NULL,
  `mensaje_comprador` text DEFAULT NULL,
  `motivo_estado` varchar(500) DEFAULT NULL,
  `motivo_rechazo` varchar(255) DEFAULT NULL,
  `requiere_preparacion` tinyint(1) NOT NULL DEFAULT 0,
  `metodo_pago` varchar(30) NOT NULL DEFAULT 'efectivo',
  `penalidad_comprador_monto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `penalidad_comprador_aplicada` tinyint(1) NOT NULL DEFAULT 0,
  `monto_descontado_billetera` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento_billetera_aplicado` tinyint(1) NOT NULL DEFAULT 0,
  `devolucion_billetera_aplicada` tinyint(1) NOT NULL DEFAULT 0,
  `acreditacion_vendedor_aplicada` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=importe acreditado a la billetera del vendedor',
  `monto_acreditado_vendedor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `comision_ev_aplicada` tinyint(1) NOT NULL DEFAULT 0,
  `comision_ev_pendiente` tinyint(1) NOT NULL DEFAULT 0,
  `comision_ev_monto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `comision_ev_porcentaje` decimal(7,4) NOT NULL DEFAULT 10.0000,
  `codigo_monetizacion_configuracion` bigint(20) unsigned DEFAULT NULL,
  `modalidad_monetizacion` varchar(60) NOT NULL DEFAULT 'estandar',
  `monetizacion_snapshot_json` longtext DEFAULT NULL,
  `cancelado_por` varchar(30) DEFAULT NULL,
  `motivo_cancelacion` varchar(255) DEFAULT NULL,
  `motivo_cancelacion_clave` varchar(80) DEFAULT NULL,
  `sin_reembolso` tinyint(1) NOT NULL DEFAULT 0,
  `oculto_comprador` tinyint(1) NOT NULL DEFAULT 0,
  `oculto_vendedor` tinyint(1) NOT NULL DEFAULT 0,
  `entrega_confirmada_comprador` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_limite_respuesta` datetime DEFAULT NULL,
  `fecha_aceptacion` datetime DEFAULT NULL,
  `fecha_rechazo` datetime DEFAULT NULL,
  `fecha_cancelacion` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `fecha_punto_recojo` datetime DEFAULT NULL,
  `fecha_limite_recojo` datetime DEFAULT NULL,
  `fecha_confirmacion_entrega` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_pedido`),
  KEY `idx_pedido_producto` (`codigo_producto`),
  KEY `idx_pedido_comprador` (`codigo_usuario_comprador`),
  KEY `idx_pedido_vendedor` (`codigo_usuario_vendedor`),
  KEY `idx_pedido_fase_estado` (`fase`,`estado_actual`),
  KEY `idx_pedido_fecha_limite` (`fecha_limite_respuesta`),
  CONSTRAINT `fk_pedido_comprador` FOREIGN KEY (`codigo_usuario_comprador`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_pedido_producto` FOREIGN KEY (`codigo_producto`) REFERENCES `producto` (`codigo_producto`),
  CONSTRAINT `fk_pedido_vendedor` FOREIGN KEY (`codigo_usuario_vendedor`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pedido_historial_estado` (
  `codigo_historial` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_pedido` int(10) unsigned NOT NULL,
  `fase_anterior` varchar(30) DEFAULT NULL,
  `estado_anterior` varchar(100) DEFAULT NULL,
  `fase_nueva` varchar(30) NOT NULL,
  `estado_nuevo` varchar(100) NOT NULL,
  `codigo_usuario_actor` int(10) unsigned DEFAULT NULL,
  `rol_actor` varchar(30) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `fecha_evento` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_historial`),
  KEY `idx_historial_pedido_fecha` (`codigo_pedido`,`fecha_evento`),
  KEY `idx_historial_actor` (`codigo_usuario_actor`),
  CONSTRAINT `fk_historial_actor` FOREIGN KEY (`codigo_usuario_actor`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_historial_pedido` FOREIGN KEY (`codigo_pedido`) REFERENCES `pedido` (`codigo_pedido`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pedido_incidencia` (
  `codigo_incidencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_pedido` int(10) unsigned NOT NULL,
  `codigo_usuario_reportante` int(10) unsigned NOT NULL,
  `codigo_usuario_afectado` int(10) unsigned NOT NULL,
  `codigo_usuario_reportado` int(10) unsigned NOT NULL,
  `codigo_pedido_incidencia_tipo` int(10) unsigned DEFAULT NULL,
  `tipo_incidencia` varchar(120) NOT NULL,
  `descripcion` text NOT NULL,
  `estado_incidencia` enum('registrada','en_revision','cerrada') NOT NULL DEFAULT 'registrada',
  `observacion_soporte` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_incidencia`),
  KEY `idx_incidencia_pedido` (`codigo_pedido`),
  KEY `idx_incidencia_reportante` (`codigo_usuario_reportante`),
  KEY `idx_incidencia_reportado` (`codigo_usuario_reportado`),
  KEY `fk_incidencia_afectado` (`codigo_usuario_afectado`),
  KEY `fk_incidencia_tipo` (`codigo_pedido_incidencia_tipo`),
  CONSTRAINT `fk_incidencia_afectado` FOREIGN KEY (`codigo_usuario_afectado`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_incidencia_pedido` FOREIGN KEY (`codigo_pedido`) REFERENCES `pedido` (`codigo_pedido`) ON DELETE CASCADE,
  CONSTRAINT `fk_incidencia_reportado` FOREIGN KEY (`codigo_usuario_reportado`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_incidencia_reportante` FOREIGN KEY (`codigo_usuario_reportante`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_incidencia_tipo` FOREIGN KEY (`codigo_pedido_incidencia_tipo`) REFERENCES `pedido_incidencia_tipo` (`codigo_pedido_incidencia_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pedido_incidencia_tipo` (
  `codigo_pedido_incidencia_tipo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo_pedido_incidencia_tipo`),
  UNIQUE KEY `uq_pedido_incidencia_tipo_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pedido_nota_soporte` (
  `codigo_pedido_nota_soporte` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_pedido` int(10) unsigned NOT NULL,
  `codigo_soporte` int(10) unsigned NOT NULL,
  `nota` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_pedido_nota_soporte`),
  KEY `idx_pedido_nota_pedido` (`codigo_pedido`),
  KEY `fk_pedido_nota_soporte` (`codigo_soporte`),
  CONSTRAINT `fk_pedido_nota_pedido` FOREIGN KEY (`codigo_pedido`) REFERENCES `pedido` (`codigo_pedido`) ON DELETE CASCADE,
  CONSTRAINT `fk_pedido_nota_soporte` FOREIGN KEY (`codigo_soporte`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `producto` (
  `codigo_producto` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_publicacion` enum('producto','servicio') NOT NULL DEFAULT 'producto',
  `titulo` varchar(160) NOT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('Nuevo','Usado','NoAplica') NOT NULL DEFAULT 'NoAplica',
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tipo_atencion_producto` enum('requiere_preparacion','no_requiere_preparacion') NOT NULL DEFAULT 'no_requiere_preparacion',
  `requiere_preparacion` tinyint(1) NOT NULL DEFAULT 0,
  `visible` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=borrador,1=pendiente,2=aprobado,3=rechazado,4=anulado',
  `activo_publicacion` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=activa en Marketplace,0=inactiva por el vecino',
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `es_destacado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_destacado` datetime DEFAULT NULL,
  `destacado_hasta` datetime DEFAULT NULL,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `codigo_usuario_residencia` int(10) unsigned DEFAULT NULL,
  `tipo_conjunto_publicacion` enum('condominio','urbanizacion') DEFAULT NULL,
  `codigo_condominio_publicacion` int(10) unsigned DEFAULT NULL,
  `codigo_urbanizacion_publicacion` int(10) unsigned DEFAULT NULL,
  `estado_residencial_publicacion` enum('activa','bloqueado_por_cambio','migrada') NOT NULL DEFAULT 'activa',
  `codigo_tipo` int(10) unsigned DEFAULT NULL,
  `codigo_categoria` int(10) unsigned DEFAULT NULL,
  `codigo_soporte` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_producto`),
  KEY `idx_producto_usuario_visible` (`codigo_usuario`,`visible`),
  KEY `idx_producto_marketplace` (`visible`,`tipo_publicacion`,`estado_residencial_publicacion`),
  KEY `idx_producto_residencia` (`codigo_usuario_residencia`),
  KEY `idx_producto_condominio` (`codigo_condominio_publicacion`),
  KEY `idx_producto_urbanizacion` (`codigo_urbanizacion_publicacion`),
  KEY `idx_producto_tipo_categoria` (`codigo_tipo`,`codigo_categoria`),
  KEY `fk_producto_categoria` (`codigo_categoria`),
  KEY `fk_producto_soporte` (`codigo_soporte`),
  CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`codigo_categoria`) REFERENCES `categoria` (`codigo_categoria`),
  CONSTRAINT `fk_producto_condominio` FOREIGN KEY (`codigo_condominio_publicacion`) REFERENCES `condominio` (`codigo_condominio`),
  CONSTRAINT `fk_producto_soporte` FOREIGN KEY (`codigo_soporte`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_producto_tipo` FOREIGN KEY (`codigo_tipo`) REFERENCES `tipo` (`codigo_tipo`),
  CONSTRAINT `fk_producto_urbanizacion` FOREIGN KEY (`codigo_urbanizacion_publicacion`) REFERENCES `urbanizacion` (`codigo_urbanizacion`),
  CONSTRAINT `fk_producto_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_producto_usuario_residencia` FOREIGN KEY (`codigo_usuario_residencia`) REFERENCES `usuario_residencia` (`codigo_usuario_residencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `producto_imagen` (
  `codigo_producto_imagen` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_producto` int(10) unsigned NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `es_portada` tinyint(1) NOT NULL DEFAULT 0,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 1,
  `ancho` int(11) DEFAULT NULL,
  `alto` int(11) DEFAULT NULL,
  `peso_bytes` int(11) DEFAULT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_producto_imagen`),
  UNIQUE KEY `uq_producto_imagen_orden` (`codigo_producto`,`orden`),
  KEY `idx_producto_imagen_portada` (`codigo_producto`,`es_portada`),
  CONSTRAINT `fk_producto_imagen_producto` FOREIGN KEY (`codigo_producto`) REFERENCES `producto` (`codigo_producto`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `producto_revision` (
  `codigo_revision` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_producto` int(10) unsigned NOT NULL,
  `estado_anterior` tinyint(4) NOT NULL,
  `estado_nuevo` tinyint(4) NOT NULL,
  `comentario` varchar(500) DEFAULT NULL,
  `codigo_soporte` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_revision`),
  KEY `idx_producto_revision_producto` (`codigo_producto`,`created_at`),
  KEY `idx_producto_revision_soporte` (`codigo_soporte`,`created_at`),
  CONSTRAINT `fk_producto_revision_producto` FOREIGN KEY (`codigo_producto`) REFERENCES `producto` (`codigo_producto`) ON DELETE CASCADE,
  CONSTRAINT `fk_producto_revision_soporte` FOREIGN KEY (`codigo_soporte`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recarga_saldo` (
  `codigo_recarga` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `metodo` varchar(30) NOT NULL,
  `id_operacion` varchar(100) NOT NULL,
  `comprobante_path` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','observada','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `comentario_soporte` varchar(500) DEFAULT NULL,
  `codigo_soporte` int(10) unsigned DEFAULT NULL,
  `fecha_revision` datetime DEFAULT NULL,
  `reenviada_usuario` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_recarga`),
  UNIQUE KEY `uq_recarga_operacion` (`metodo`,`id_operacion`),
  KEY `idx_recarga_estado` (`estado`),
  KEY `idx_recarga_usuario` (`codigo_usuario`),
  KEY `fk_recarga_soporte` (`codigo_soporte`),
  CONSTRAINT `fk_recarga_soporte` FOREIGN KEY (`codigo_soporte`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_recarga_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rol` (
  `codigo_rol` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_rol`),
  UNIQUE KEY `uq_rol_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rol_menu_item` (
  `codigo_rol_menu_item` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_rol` int(10) unsigned NOT NULL,
  `codigo_menu_item` int(10) unsigned NOT NULL,
  `puede_crear` tinyint(1) NOT NULL DEFAULT 0,
  `puede_leer` tinyint(1) NOT NULL DEFAULT 1,
  `puede_actualizar` tinyint(1) NOT NULL DEFAULT 0,
  `puede_eliminar` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`codigo_rol_menu_item`),
  UNIQUE KEY `uq_rol_menu_item` (`codigo_rol`,`codigo_menu_item`),
  KEY `fk_rmi_menu_item` (`codigo_menu_item`),
  CONSTRAINT `fk_rmi_menu_item` FOREIGN KEY (`codigo_menu_item`) REFERENCES `menu_item` (`codigo_menu_item`),
  CONSTRAINT `fk_rmi_rol` FOREIGN KEY (`codigo_rol`) REFERENCES `rol` (`codigo_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_servicio` (
  `codigo_solicitud_servicio` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_producto` int(10) unsigned NOT NULL,
  `codigo_usuario_solicitante` int(10) unsigned NOT NULL,
  `codigo_usuario_proveedor` int(10) unsigned NOT NULL,
  `codigo_usuario_residencia_solicitante` int(10) unsigned DEFAULT NULL,
  `codigo_usuario_residencia_proveedor` int(10) unsigned DEFAULT NULL,
  `precio_referencial` decimal(12,2) DEFAULT NULL,
  `presupuesto_estimado` decimal(12,2) DEFAULT NULL,
  `fecha_deseada` date DEFAULT NULL,
  `rango_horario` varchar(150) DEFAULT NULL,
  `modalidad_preferida` varchar(80) NOT NULL DEFAULT 'a_coordinar',
  `referencia_ubicacion` varchar(160) DEFAULT NULL,
  `direccion_atencion` varchar(500) DEFAULT NULL,
  `mensaje_solicitante` text DEFAULT NULL,
  `datos_contextuales_json` longtext DEFAULT NULL,
  `estado` varchar(100) NOT NULL DEFAULT 'pendiente_proveedor',
  `estado_anterior` varchar(100) DEFAULT NULL,
  `motivo_estado` varchar(500) DEFAULT NULL,
  `fecha_limite_respuesta` datetime DEFAULT NULL,
  `fecha_aceptacion` datetime DEFAULT NULL,
  `fecha_ejecucion_original` date DEFAULT NULL,
  `hora_inicio_original` time DEFAULT NULL,
  `hora_fin_original` time DEFAULT NULL,
  `fecha_ejecucion_vigente` date DEFAULT NULL,
  `hora_inicio_vigente` time DEFAULT NULL,
  `hora_fin_vigente` time DEFAULT NULL,
  `version_operativa` int(10) unsigned NOT NULL DEFAULT 1,
  `fecha_ultima_reprogramacion` datetime DEFAULT NULL,
  `fecha_inicio_servicio` datetime DEFAULT NULL,
  `fecha_realizado_proveedor` datetime DEFAULT NULL,
  `fecha_limite_confirmacion` datetime DEFAULT NULL,
  `fecha_revision_soporte_sugerida` datetime DEFAULT NULL,
  `recordatorio_confirmacion_24_at` datetime DEFAULT NULL,
  `recordatorio_confirmacion_48_at` datetime DEFAULT NULL,
  `recordatorio_confirmacion_72_at` datetime DEFAULT NULL,
  `fecha_confirmacion_solicitante` datetime DEFAULT NULL,
  `fecha_observacion` datetime DEFAULT NULL,
  `direccion_compartida_at` datetime DEFAULT NULL,
  `ultima_interaccion_at` datetime DEFAULT NULL,
  `fecha_rechazo` datetime DEFAULT NULL,
  `fecha_cancelacion` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_solicitud_servicio`),
  KEY `idx_ss_producto` (`codigo_producto`),
  KEY `idx_ss_solicitante_estado` (`codigo_usuario_solicitante`,`estado`),
  KEY `idx_ss_proveedor_estado` (`codigo_usuario_proveedor`,`estado`),
  KEY `idx_ss_limite` (`fecha_limite_respuesta`),
  KEY `fk_ss_residencia_solicitante` (`codigo_usuario_residencia_solicitante`),
  KEY `fk_ss_residencia_proveedor` (`codigo_usuario_residencia_proveedor`),
  CONSTRAINT `fk_ss_producto` FOREIGN KEY (`codigo_producto`) REFERENCES `producto` (`codigo_producto`),
  CONSTRAINT `fk_ss_proveedor` FOREIGN KEY (`codigo_usuario_proveedor`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ss_residencia_proveedor` FOREIGN KEY (`codigo_usuario_residencia_proveedor`) REFERENCES `usuario_residencia` (`codigo_usuario_residencia`),
  CONSTRAINT `fk_ss_residencia_solicitante` FOREIGN KEY (`codigo_usuario_residencia_solicitante`) REFERENCES `usuario_residencia` (`codigo_usuario_residencia`),
  CONSTRAINT `fk_ss_solicitante` FOREIGN KEY (`codigo_usuario_solicitante`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_servicio_adjunto` (
  `codigo_solicitud_servicio_adjunto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_solicitud_servicio` bigint(20) unsigned NOT NULL,
  `codigo_solicitud_servicio_interaccion` bigint(20) unsigned DEFAULT NULL,
  `codigo_solicitud_servicio_propuesta` bigint(20) unsigned DEFAULT NULL,
  `codigo_usuario_autor` int(10) unsigned NOT NULL,
  `origen` enum('solicitud','mensaje','propuesta') NOT NULL,
  `ruta` varchar(500) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `mime` varchar(100) NOT NULL,
  `peso_bytes` int(10) unsigned NOT NULL,
  `ancho` int(10) unsigned DEFAULT NULL,
  `alto` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_solicitud_servicio_adjunto`),
  KEY `idx_ssa_solicitud_fecha` (`codigo_solicitud_servicio`,`created_at`),
  KEY `idx_ssa_interaccion` (`codigo_solicitud_servicio_interaccion`),
  KEY `idx_ssa_propuesta` (`codigo_solicitud_servicio_propuesta`),
  KEY `idx_ssa_autor` (`codigo_usuario_autor`),
  CONSTRAINT `fk_ssa_autor` FOREIGN KEY (`codigo_usuario_autor`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ssa_interaccion` FOREIGN KEY (`codigo_solicitud_servicio_interaccion`) REFERENCES `solicitud_servicio_interaccion` (`codigo_solicitud_servicio_interaccion`) ON DELETE CASCADE,
  CONSTRAINT `fk_ssa_propuesta` FOREIGN KEY (`codigo_solicitud_servicio_propuesta`) REFERENCES `solicitud_servicio_propuesta` (`codigo_solicitud_servicio_propuesta`) ON DELETE CASCADE,
  CONSTRAINT `fk_ssa_solicitud` FOREIGN KEY (`codigo_solicitud_servicio`) REFERENCES `solicitud_servicio` (`codigo_solicitud_servicio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_servicio_incidencia` (
  `codigo_incidencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_solicitud_servicio` bigint(20) unsigned NOT NULL,
  `numero_incidencia` int(10) unsigned NOT NULL DEFAULT 1,
  `codigo_usuario_reporta` int(10) unsigned NOT NULL,
  `rol_reporta` varchar(30) NOT NULL,
  `categoria` varchar(80) NOT NULL,
  `descripcion` text NOT NULL,
  `estado_solicitud_origen` varchar(100) NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'abierta',
  `codigo_usuario_responde` int(10) unsigned DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  `codigo_usuario_solucion` int(10) unsigned DEFAULT NULL,
  `solucion` text DEFAULT NULL,
  `fecha_solucion` datetime DEFAULT NULL,
  `requiere_soporte` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_escalamiento_soporte` datetime DEFAULT NULL,
  `codigo_usuario_soporte` int(10) unsigned DEFAULT NULL,
  `resultado_soporte` varchar(50) DEFAULT NULL,
  `resolucion_soporte` text DEFAULT NULL,
  `fecha_resolucion_soporte` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_incidencia`),
  UNIQUE KEY `uq_ssi_numero` (`codigo_solicitud_servicio`,`numero_incidencia`),
  KEY `idx_ssi_solicitud_estado` (`codigo_solicitud_servicio`,`estado`,`created_at`),
  KEY `idx_ssi_soporte` (`requiere_soporte`,`estado`,`updated_at`),
  KEY `idx_ssi_reporta` (`codigo_usuario_reporta`),
  KEY `idx_ssi_responde` (`codigo_usuario_responde`),
  KEY `idx_ssi_solucion` (`codigo_usuario_solucion`),
  KEY `idx_ssi_usuario_soporte` (`codigo_usuario_soporte`),
  CONSTRAINT `fk_ssi11_reporta` FOREIGN KEY (`codigo_usuario_reporta`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ssi11_responde` FOREIGN KEY (`codigo_usuario_responde`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ssi11_solicitud` FOREIGN KEY (`codigo_solicitud_servicio`) REFERENCES `solicitud_servicio` (`codigo_solicitud_servicio`) ON DELETE CASCADE,
  CONSTRAINT `fk_ssi11_solucion` FOREIGN KEY (`codigo_usuario_solucion`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ssi11_soporte` FOREIGN KEY (`codigo_usuario_soporte`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_servicio_incidencia_adjunto` (
  `codigo_incidencia_adjunto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_incidencia` bigint(20) unsigned NOT NULL,
  `codigo_usuario_autor` int(10) unsigned NOT NULL,
  `contexto` varchar(40) NOT NULL DEFAULT 'reporte',
  `ruta` varchar(500) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `mime` varchar(100) NOT NULL,
  `peso_bytes` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_incidencia_adjunto`),
  KEY `idx_ssia_incidencia` (`codigo_incidencia`,`contexto`,`created_at`),
  KEY `idx_ssia_autor` (`codigo_usuario_autor`),
  CONSTRAINT `fk_ssia_autor` FOREIGN KEY (`codigo_usuario_autor`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ssia_incidencia` FOREIGN KEY (`codigo_incidencia`) REFERENCES `solicitud_servicio_incidencia` (`codigo_incidencia`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_servicio_interaccion` (
  `codigo_solicitud_servicio_interaccion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_solicitud_servicio` bigint(20) unsigned NOT NULL,
  `codigo_usuario_autor` int(10) unsigned NOT NULL,
  `rol_autor` varchar(30) NOT NULL,
  `tipo_interaccion` varchar(80) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `payload_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_solicitud_servicio_interaccion`),
  KEY `idx_ssi_solicitud_fecha` (`codigo_solicitud_servicio`,`created_at`),
  KEY `fk_ssi_autor` (`codigo_usuario_autor`),
  CONSTRAINT `fk_ssi_autor` FOREIGN KEY (`codigo_usuario_autor`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ssi_solicitud` FOREIGN KEY (`codigo_solicitud_servicio`) REFERENCES `solicitud_servicio` (`codigo_solicitud_servicio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_servicio_propuesta` (
  `codigo_solicitud_servicio_propuesta` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_solicitud_servicio` bigint(20) unsigned NOT NULL,
  `codigo_usuario_proveedor` int(10) unsigned NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `modalidad` varchar(80) DEFAULT NULL,
  `momento_tipo` varchar(80) DEFAULT NULL,
  `fecha_propuesta` datetime DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `horario_propuesto` varchar(150) DEFAULT NULL,
  `alcance_confirmado` text DEFAULT NULL,
  `tipo_precio` varchar(80) DEFAULT NULL,
  `monto_propuesto` decimal(12,2) DEFAULT NULL,
  `movilidad_tipo` enum('incluida','adicional','no_aplica') NOT NULL DEFAULT 'no_aplica',
  `monto_movilidad` decimal(12,2) DEFAULT NULL,
  `condicion_pago` enum('contra_entrega','adelanto_acordado') DEFAULT NULL,
  `monto_adelanto` decimal(12,2) DEFAULT NULL,
  `fecha_vencimiento` datetime DEFAULT NULL,
  `unidad_precio` varchar(80) DEFAULT NULL,
  `duracion_estimada` varchar(150) DEFAULT NULL,
  `requisitos` text DEFAULT NULL,
  `mensaje_proveedor` text DEFAULT NULL,
  `estado` enum('vigente','aceptada','reemplazada','cancelada_solicitante','cancelada_proveedor','requiere_actualizacion','vencida','rechazada_solicitante') NOT NULL DEFAULT 'vigente',
  `motivo_estado` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_solicitud_servicio_propuesta`),
  UNIQUE KEY `uq_ssp_version` (`codigo_solicitud_servicio`,`version`),
  KEY `idx_ssp_estado` (`codigo_solicitud_servicio`,`estado`),
  KEY `fk_ssp_proveedor` (`codigo_usuario_proveedor`),
  KEY `idx_ssp_vencimiento` (`estado`,`fecha_vencimiento`),
  CONSTRAINT `fk_ssp_proveedor` FOREIGN KEY (`codigo_usuario_proveedor`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ssp_solicitud` FOREIGN KEY (`codigo_solicitud_servicio`) REFERENCES `solicitud_servicio` (`codigo_solicitud_servicio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `solicitud_servicio_reprogramacion` (
  `codigo_reprogramacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_solicitud_servicio` bigint(20) unsigned NOT NULL,
  `version_operativa_origen` int(10) unsigned NOT NULL DEFAULT 1,
  `codigo_usuario_propone` int(10) unsigned NOT NULL,
  `rol_propone` varchar(30) NOT NULL,
  `fecha_anterior` date DEFAULT NULL,
  `hora_inicio_anterior` time DEFAULT NULL,
  `hora_fin_anterior` time DEFAULT NULL,
  `fecha_nueva` date NOT NULL,
  `hora_inicio_nueva` time NOT NULL,
  `hora_fin_nueva` time DEFAULT NULL,
  `motivo` varchar(500) NOT NULL,
  `comentario` varchar(1000) DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'pendiente',
  `codigo_usuario_responde` int(10) unsigned DEFAULT NULL,
  `respuesta_comentario` varchar(500) DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_reprogramacion`),
  KEY `idx_ssr_solicitud_estado` (`codigo_solicitud_servicio`,`estado`,`created_at`),
  KEY `idx_ssr_propone` (`codigo_usuario_propone`),
  KEY `idx_ssr_responde` (`codigo_usuario_responde`),
  CONSTRAINT `fk_ssr_propone` FOREIGN KEY (`codigo_usuario_propone`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ssr_responde` FOREIGN KEY (`codigo_usuario_responde`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_ssr_solicitud` FOREIGN KEY (`codigo_solicitud_servicio`) REFERENCES `solicitud_servicio` (`codigo_solicitud_servicio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tipo` (
  `codigo_tipo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_categoria_grupo` int(10) unsigned DEFAULT NULL,
  `nombre` varchar(30) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo_tipo`),
  UNIQUE KEY `uq_tipo_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `torre` (
  `codigo_torre` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_torre` varchar(100) NOT NULL,
  `codigo_condominio` int(10) unsigned NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_torre`),
  KEY `idx_torre_condominio` (`codigo_condominio`),
  CONSTRAINT `fk_torre_condominio` FOREIGN KEY (`codigo_condominio`) REFERENCES `condominio` (`codigo_condominio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ubigeo_departamento` (
  `codigo_departamento` int(11) NOT NULL,
  `nombre_departamento` varchar(150) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo_departamento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ubigeo_distrito` (
  `codigo_distrito` int(11) NOT NULL,
  `codigo_provincia` int(11) NOT NULL,
  `nombre_distrito` varchar(150) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo_distrito`),
  KEY `idx_ubigeo_distrito_provincia` (`codigo_provincia`),
  CONSTRAINT `fk_ubigeo_distrito_provincia` FOREIGN KEY (`codigo_provincia`) REFERENCES `ubigeo_provincia` (`codigo_provincia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ubigeo_provincia` (
  `codigo_provincia` int(11) NOT NULL,
  `codigo_departamento` int(11) NOT NULL,
  `nombre_provincia` varchar(150) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo_provincia`),
  KEY `idx_ubigeo_provincia_departamento` (`codigo_departamento`),
  CONSTRAINT `fk_ubigeo_provincia_departamento` FOREIGN KEY (`codigo_departamento`) REFERENCES `ubigeo_departamento` (`codigo_departamento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `urbanizacion` (
  `codigo_urbanizacion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_urbanizacion` varchar(200) NOT NULL,
  `direccion_urbanizacion` varchar(300) NOT NULL,
  `codigo_distrito` int(11) DEFAULT NULL,
  `estado` char(1) NOT NULL DEFAULT 'A',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_urbanizacion`),
  KEY `idx_urbanizacion_distrito` (`codigo_distrito`),
  CONSTRAINT `fk_urbanizacion_distrito` FOREIGN KEY (`codigo_distrito`) REFERENCES `ubigeo_distrito` (`codigo_distrito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuario` (
  `codigo_usuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `email` varchar(150) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=En revisión, 2=Habilitado',
  `codigo_rol` int(10) unsigned NOT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `disponibilidad_pedidos` tinyint(1) NOT NULL DEFAULT 0,
  `comentario_soporte` text DEFAULT NULL,
  `comprobante_observacion_url` varchar(255) DEFAULT NULL,
  `fecha_reenvio_observacion` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_usuario`),
  UNIQUE KEY `uq_usuario_email` (`email`),
  UNIQUE KEY `uq_usuario_documento` (`documento`),
  KEY `idx_usuario_rol_estado` (`codigo_rol`,`estado`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`codigo_rol`) REFERENCES `rol` (`codigo_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuario_departamento` (
  `codigo_usuario_departamento` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `codigo_departamento` int(10) unsigned NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_usuario_departamento`),
  KEY `idx_usuario_departamento_usuario` (`codigo_usuario`),
  KEY `idx_usuario_departamento_departamento` (`codigo_departamento`),
  CONSTRAINT `fk_usuario_departamento_departamento` FOREIGN KEY (`codigo_departamento`) REFERENCES `departamento` (`codigo_departamento`),
  CONSTRAINT `fk_usuario_departamento_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuario_documento_legal_aceptacion` (
  `codigo_aceptacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `codigo_documento_legal` int(10) unsigned NOT NULL,
  `tipo_documento` varchar(60) NOT NULL,
  `version_documento` varchar(30) NOT NULL,
  `hash_documento` char(64) NOT NULL,
  `texto_consentimiento` varchar(1000) NOT NULL,
  `hash_consentimiento` char(64) NOT NULL,
  `aceptado` tinyint(1) NOT NULL DEFAULT 1,
  `origen` varchar(30) NOT NULL COMMENT 'registro|primer_ingreso|nueva_version',
  `ip_aceptacion` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `fecha_aceptacion` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo_aceptacion`),
  UNIQUE KEY `uq_aceptacion_usuario_documento_hash` (`codigo_usuario`,`codigo_documento_legal`,`hash_documento`),
  KEY `idx_aceptacion_usuario_tipo` (`codigo_usuario`,`tipo_documento`,`fecha_aceptacion`),
  KEY `idx_aceptacion_documento` (`codigo_documento_legal`,`fecha_aceptacion`),
  CONSTRAINT `fk_aceptacion_documento_legal` FOREIGN KEY (`codigo_documento_legal`) REFERENCES `documento_legal` (`codigo_documento_legal`) ON UPDATE CASCADE,
  CONSTRAINT `fk_aceptacion_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuario_penalidad` (
  `codigo_usuario_penalidad` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `codigo_pedido_origen` int(10) unsigned NOT NULL,
  `codigo_pedido_aplicado` int(10) unsigned DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL,
  `estado` enum('pendiente','reservada','aplicada','anulada') NOT NULL DEFAULT 'pendiente',
  `motivo_clave` varchar(80) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_aplicacion` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_usuario_penalidad`),
  UNIQUE KEY `uq_penalidad_origen` (`codigo_usuario`,`codigo_pedido_origen`),
  KEY `idx_penalidad_usuario_estado` (`codigo_usuario`,`estado`),
  KEY `idx_penalidad_pedido_aplicado` (`codigo_pedido_aplicado`),
  KEY `fk_penalidad_pedido_origen` (`codigo_pedido_origen`),
  CONSTRAINT `fk_penalidad_pedido_aplicado` FOREIGN KEY (`codigo_pedido_aplicado`) REFERENCES `pedido` (`codigo_pedido`),
  CONSTRAINT `fk_penalidad_pedido_origen` FOREIGN KEY (`codigo_pedido_origen`) REFERENCES `pedido` (`codigo_pedido`),
  CONSTRAINT `fk_penalidad_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuario_residencia` (
  `codigo_usuario_residencia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `tipo_conjunto` enum('condominio','urbanizacion') NOT NULL,
  `codigo_condominio` int(10) unsigned DEFAULT NULL,
  `codigo_urbanizacion` int(10) unsigned DEFAULT NULL,
  `direccion` varchar(250) NOT NULL,
  `comprobante_domicilio` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_usuario_residencia`),
  KEY `idx_ur_usuario` (`codigo_usuario`,`codigo_usuario_residencia`),
  KEY `idx_ur_condominio` (`codigo_condominio`),
  KEY `idx_ur_urbanizacion` (`codigo_urbanizacion`),
  KEY `idx_ur_usuario_estado_vigente` (`codigo_usuario`,`estado`,`codigo_usuario_residencia`),
  CONSTRAINT `fk_ur_condominio` FOREIGN KEY (`codigo_condominio`) REFERENCES `condominio` (`codigo_condominio`),
  CONSTRAINT `fk_ur_urbanizacion` FOREIGN KEY (`codigo_urbanizacion`) REFERENCES `urbanizacion` (`codigo_urbanizacion`),
  CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuario_residencia_solicitud` (
  `codigo_solicitud` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `tipo_conjunto` enum('condominio','urbanizacion') NOT NULL,
  `codigo_condominio` int(10) unsigned DEFAULT NULL,
  `codigo_urbanizacion` int(10) unsigned DEFAULT NULL,
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
  CONSTRAINT `fk_urs_condominio` FOREIGN KEY (`codigo_condominio`) REFERENCES `condominio` (`codigo_condominio`),
  CONSTRAINT `fk_urs_urbanizacion` FOREIGN KEY (`codigo_urbanizacion`) REFERENCES `urbanizacion` (`codigo_urbanizacion`),
  CONSTRAINT `fk_urs_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuario_revision` (
  `codigo_usuario_revision` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `estado_revision` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=En revisión, 2=Habilitado, 3=Observado',
  `mensaje_observacion` varchar(500) DEFAULT NULL,
  `comprobante_path` varchar(255) DEFAULT NULL,
  `fecha_observacion` datetime DEFAULT NULL,
  `fecha_reenvio` datetime DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_usuario_revision`),
  UNIQUE KEY `uq_usuario_revision_usuario` (`codigo_usuario`),
  CONSTRAINT `fk_usuario_revision_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendedor_comision_ev` (
  `codigo_vendedor_comision_ev` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_pedido` int(10) unsigned NOT NULL,
  `codigo_usuario_vendedor` int(10) unsigned NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `estado` enum('pendiente','cobrada','anulada') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_vendedor_comision_ev`),
  UNIQUE KEY `uq_comision_pedido` (`codigo_pedido`),
  KEY `idx_comision_vendedor_estado` (`codigo_usuario_vendedor`,`estado`),
  CONSTRAINT `fk_comision_pedido` FOREIGN KEY (`codigo_pedido`) REFERENCES `pedido` (`codigo_pedido`),
  CONSTRAINT `fk_comision_vendedor` FOREIGN KEY (`codigo_usuario_vendedor`) REFERENCES `usuario` (`codigo_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuario_cuenta_bancaria` (
  `codigo_cuenta_bancaria` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `banco` varchar(80) NOT NULL,
  `tipo_cuenta` enum('ahorros','corriente') NOT NULL,
  `numero_cuenta` varchar(30) NOT NULL,
  `cci` char(20) NOT NULL,
  `titular_nombre` varchar(200) NOT NULL,
  `titular_documento` varchar(50) NOT NULL,
  `declara_titularidad` tinyint(1) NOT NULL DEFAULT 0,
  `estado_validacion` enum('pendiente','validada','observada') NOT NULL DEFAULT 'pendiente',
  `observacion_admin` varchar(500) DEFAULT NULL,
  `validado_por` int(10) unsigned DEFAULT NULL,
  `fecha_validacion` datetime DEFAULT NULL,
  `estado_registro` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_cuenta_bancaria`),
  KEY `idx_ucb_usuario_activa` (`codigo_usuario`,`estado_registro`,`codigo_cuenta_bancaria`),
  KEY `idx_ucb_cci_activa` (`cci`,`estado_registro`),
  KEY `idx_ucb_banco_cuenta_activa` (`banco`,`numero_cuenta`,`estado_registro`),
  KEY `idx_ucb_validacion` (`estado_validacion`,`estado_registro`),
  KEY `idx_ucb_admin` (`validado_por`),
  CONSTRAINT `fk_ucb_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_ucb_admin` FOREIGN KEY (`validado_por`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `retiro_configuracion` (
  `codigo_retiro_configuracion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_jornada` varchar(80) NOT NULL,
  `dia_pago` tinyint(3) unsigned NOT NULL COMMENT 'ISO-8601: 1=lunes ... 7=domingo',
  `dia_inicio_corte` tinyint(3) unsigned NOT NULL COMMENT 'ISO-8601: 1=lunes ... 7=domingo',
  `hora_inicio_corte` time NOT NULL,
  `dia_fin_corte` tinyint(3) unsigned NOT NULL COMMENT 'ISO-8601: 1=lunes ... 7=domingo',
  `hora_fin_corte` time NOT NULL,
  `saldo_minimo` decimal(12,2) NOT NULL DEFAULT 20.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `actualizado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_retiro_configuracion`),
  UNIQUE KEY `uq_retiro_config_dia_pago` (`dia_pago`),
  KEY `idx_retiro_config_activo` (`activo`,`dia_pago`),
  KEY `idx_retiro_config_admin` (`actualizado_por`),
  CONSTRAINT `fk_retiro_config_admin` FOREIGN KEY (`actualizado_por`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE SET NULL,
  CONSTRAINT `chk_retiro_dia_pago` CHECK (`dia_pago` BETWEEN 1 AND 7),
  CONSTRAINT `chk_retiro_dia_inicio` CHECK (`dia_inicio_corte` BETWEEN 1 AND 7),
  CONSTRAINT `chk_retiro_dia_fin` CHECK (`dia_fin_corte` BETWEEN 1 AND 7),
  CONSTRAINT `chk_retiro_saldo_minimo` CHECK (`saldo_minimo` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `retiro_solicitud` (
  `codigo_retiro` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_usuario` int(10) unsigned NOT NULL,
  `codigo_cuenta_bancaria` bigint(20) unsigned NOT NULL,
  `codigo_retiro_configuracion` int(10) unsigned NOT NULL,
  `jornada_nombre` varchar(80) NOT NULL,
  `corte_inicio` datetime NOT NULL,
  `corte_fin` datetime NOT NULL,
  `fecha_pago_programada` date NOT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `saldo_solicitud` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo_minimo_snapshot` decimal(12,2) NOT NULL DEFAULT 20.00,
  `monto_estimado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo_cierre` decimal(12,2) DEFAULT NULL,
  `monto_final` decimal(12,2) DEFAULT NULL,
  `estado` enum('solicitado','programado','pagado','observado','cancelado','sin_saldo') NOT NULL DEFAULT 'solicitado',
  `fecha_cierre_procesado` datetime DEFAULT NULL,
  `fecha_pago` datetime DEFAULT NULL,
  `numero_operacion` varchar(100) DEFAULT NULL,
  `comprobante_path` varchar(255) DEFAULT NULL,
  `observacion_admin` varchar(500) DEFAULT NULL,
  `procesado_por` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`codigo_retiro`),
  UNIQUE KEY `uq_retiro_usuario_corte` (`codigo_usuario`,`corte_inicio`,`corte_fin`),
  KEY `idx_retiro_estado_corte` (`estado`,`corte_fin`),
  KEY `idx_retiro_pago_programado` (`fecha_pago_programada`,`estado`),
  KEY `idx_retiro_usuario_estado` (`codigo_usuario`,`estado`,`fecha_solicitud`),
  KEY `idx_retiro_cuenta` (`codigo_cuenta_bancaria`),
  KEY `idx_retiro_config` (`codigo_retiro_configuracion`),
  KEY `idx_retiro_procesado_por` (`procesado_por`),
  CONSTRAINT `fk_retiro_usuario` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo_usuario`),
  CONSTRAINT `fk_retiro_cuenta` FOREIGN KEY (`codigo_cuenta_bancaria`) REFERENCES `usuario_cuenta_bancaria` (`codigo_cuenta_bancaria`),
  CONSTRAINT `fk_retiro_config` FOREIGN KEY (`codigo_retiro_configuracion`) REFERENCES `retiro_configuracion` (`codigo_retiro_configuracion`),
  CONSTRAINT `fk_retiro_procesado_por` FOREIGN KEY (`procesado_por`) REFERENCES `usuario` (`codigo_usuario`) ON DELETE SET NULL,
  CONSTRAINT `chk_retiro_corte` CHECK (`corte_fin` >= `corte_inicio`),
  CONSTRAINT `chk_retiro_saldos` CHECK (`saldo_solicitud` >= 0 AND `saldo_minimo_snapshot` >= 0 AND `monto_estimado` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Stored procedure required by models/User.php
DROP PROCEDURE IF EXISTS `sp_registrar_usuario_v2`;
DELIMITER //
CREATE PROCEDURE `sp_registrar_usuario_v2`(
  IN p_nombre VARCHAR(200),
  IN p_documento VARCHAR(50),
  IN p_telefono VARCHAR(50),
  IN p_email VARCHAR(150),
  IN p_clave VARCHAR(255),
  IN p_codigo_rol INT,
  IN p_tipo_conjunto VARCHAR(20),
  IN p_codigo_condominio INT,
  IN p_codigo_urbanizacion INT,
  IN p_direccion VARCHAR(250),
  IN p_comprobante_domicilio VARCHAR(255)
)
BEGIN
  DECLARE v_usuario_id INT;

  IF p_tipo_conjunto NOT IN ('condominio', 'urbanizacion') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tipo de conjunto residencial inválido';
  END IF;
  IF p_tipo_conjunto = 'condominio' AND (p_codigo_condominio IS NULL OR p_codigo_condominio = 0) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Código de condominio requerido';
  END IF;
  IF p_tipo_conjunto = 'urbanizacion' AND (p_codigo_urbanizacion IS NULL OR p_codigo_urbanizacion = 0) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Código de urbanización requerido';
  END IF;
  IF p_direccion IS NULL OR CHAR_LENGTH(TRIM(p_direccion)) < 5 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Dirección inválida';
  END IF;

  INSERT INTO usuario (nombre, documento, telefono, email, clave, estado, codigo_rol)
  VALUES (TRIM(p_nombre), NULLIF(TRIM(p_documento), ''), NULLIF(TRIM(p_telefono), ''), LOWER(TRIM(p_email)), p_clave, 1, p_codigo_rol);
  SET v_usuario_id = LAST_INSERT_ID();

  INSERT INTO usuario_residencia
  (codigo_usuario,tipo_conjunto,codigo_condominio,codigo_urbanizacion,direccion,comprobante_domicilio,estado)
  VALUES
  (v_usuario_id,p_tipo_conjunto,
   CASE WHEN p_tipo_conjunto='condominio' THEN p_codigo_condominio ELSE NULL END,
   CASE WHEN p_tipo_conjunto='urbanizacion' THEN p_codigo_urbanizacion ELSE NULL END,
   TRIM(p_direccion),NULLIF(TRIM(p_comprobante_domicilio), ''),1);

  INSERT INTO usuario_revision (codigo_usuario,estado_revision,comprobante_path)
  VALUES (v_usuario_id,1,NULLIF(TRIM(p_comprobante_domicilio), ''));

  INSERT INTO billetera (codigo_usuario,saldo,saldo_actual,estado)
  VALUES (v_usuario_id,0.00,0.00,1);
END//
DELIMITER ;

-- Reference catalogs / residential test data

INSERT INTO `rol` (`codigo_rol`, `nombre`, `descripcion`, `estado`, `created_at`, `updated_at`) VALUES
	(1, 'admin', 'Administrador del sistema EV', 1, '2026-07-05 07:21:03', '2026-07-05 07:21:03'),
	(2, 'vecino', 'Usuario vecino de una comunidad', 1, '2026-07-05 07:21:03', '2026-07-05 07:21:03'),
	(3, 'soporte', 'Operador de soporte EV', 1, '2026-07-05 07:21:03', '2026-07-05 07:21:03'),
	(4, 'administrador_comunidad', 'Administrador de una comunidad', 1, '2026-07-05 07:21:03', '2026-07-05 07:21:03');

INSERT INTO `menu` (`codigo_menu`, `nombre`, `icono`, `orden`, `estado`) VALUES
	(1, 'Mi cuenta', 'bi-person-circle', 1, 1),
	(2, 'Comprar', 'bi-cart2', 2, 1),
	(3, 'Vender', 'bi-shop-window', 3, 1),
	(4, 'Comunidad', 'bi-people-fill', 4, 1),
	(5, 'Soporte', 'bi-headset', 5, 1),
	(6, 'Administración', 'bi-sliders', 6, 1);

INSERT INTO `tipo` (`codigo_tipo`, `codigo_categoria_grupo`, `nombre`, `descripcion`, `estado`) VALUES
	(1, NULL, 'Producto', NULL, 1),
	(2, NULL, 'Servicio', NULL, 1);

INSERT INTO `categoria_grupo` (`codigo_grupo`, `nombre`, `orden`, `estado`, `codigo_tipo`, `created_at`, `updated_at`) VALUES
	(1, 'Alimentos y bebidas', 1, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(2, 'Abarrotes y limpieza', 2, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(3, 'Electrodomésticos', 3, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(4, 'Electrónica y cómputo', 4, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(5, 'Muebles y hogar', 5, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(6, 'Ropa y calzado', 6, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(7, 'Otros productos', 7, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(8, 'Hogar y mantenimiento', 1, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(9, 'Reparaciones técnicas', 2, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(10, 'Clases y tutorías', 3, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(11, 'Salud y bienestar', 4, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(12, 'Eventos y catering', 5, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(13, 'Transporte y mudanzas', 6, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(14, 'Servicios para mascotas', 7, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(15, 'Cuidado de personas', 8, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(16, 'Soporte tecnológico', 9, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(17, 'Personalizados y manualidades', 10, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(18, 'Otros servicios', 11, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35');

INSERT INTO `categoria` (`codigo_categoria`, `nombre`, `descripcion`, `orden`, `estado`, `requiere_preparacion_default`, `codigo_tipo`, `codigo_grupo`, `created_at`, `updated_at`) VALUES
	(1, 'Golosinas y snacks', NULL, 1, 1, 0, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(2, 'Frutas y verduras', NULL, 2, 1, 0, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(3, 'Almuerzos y menús', NULL, 3, 1, 0, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(4, 'Postres y panes', NULL, 4, 1, 0, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(5, 'Bebidas', NULL, 5, 1, 0, 1, 1, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(6, 'Despensa', NULL, 1, 1, 0, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(7, 'Aseo del hogar', NULL, 2, 1, 0, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(8, 'Cuidado personal', NULL, 3, 1, 0, 1, 2, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(9, 'Cocina', NULL, 1, 1, 0, 1, 3, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(10, 'Lavado', NULL, 2, 1, 0, 1, 3, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(11, 'Climatización', NULL, 3, 1, 0, 1, 3, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(12, 'Pequeños electrodomésticos', NULL, 4, 1, 0, 1, 3, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(13, 'Celulares', NULL, 1, 1, 0, 1, 4, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(14, 'Computadoras', NULL, 2, 1, 0, 1, 4, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(15, 'Audio / Video', NULL, 3, 1, 0, 1, 4, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(16, 'Accesorios electrónicos', NULL, 4, 1, 0, 1, 4, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(17, 'Sillas', NULL, 1, 1, 0, 1, 5, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(18, 'Sala', NULL, 2, 1, 0, 1, 5, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(19, 'Dormitorio', NULL, 3, 1, 0, 1, 5, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(20, 'Cocina / Comedor', NULL, 4, 1, 0, 1, 5, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(21, 'Organización', NULL, 5, 1, 0, 1, 5, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(22, 'Decoración', NULL, 6, 1, 0, 1, 5, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(23, 'Hombre', NULL, 1, 1, 0, 1, 6, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(24, 'Mujer', NULL, 2, 1, 0, 1, 6, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(25, 'Niños', NULL, 3, 1, 0, 1, 6, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(26, 'Accesorios de moda', NULL, 4, 1, 0, 1, 6, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(27, 'Deporte y fitness', NULL, 1, 1, 0, 1, 7, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(28, 'Mascotas', NULL, 2, 1, 0, 1, 7, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(29, 'Libros y papelería', NULL, 3, 1, 0, 1, 7, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(30, 'Jardín y herramientas', NULL, 4, 1, 0, 1, 7, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(31, 'Automotriz', NULL, 5, 1, 0, 1, 7, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(32, 'Arte, ocio y coleccionables', NULL, 6, 1, 0, 1, 7, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(33, 'Varios', NULL, 7, 1, 0, 1, 7, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(34, 'Limpieza', NULL, 1, 1, 0, 2, 8, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(35, 'Gasfitería', NULL, 2, 1, 0, 2, 8, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(36, 'Electricidad', NULL, 3, 1, 0, 2, 8, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(37, 'Carpintería', NULL, 4, 1, 0, 2, 8, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(38, 'Pintura', NULL, 5, 1, 0, 2, 8, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(39, 'Electrodomésticos', NULL, 1, 1, 0, 2, 9, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(40, 'Celulares / PC', NULL, 2, 1, 0, 2, 9, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(41, 'TV / Audio', NULL, 3, 1, 0, 2, 9, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(42, 'Escolares', NULL, 1, 1, 0, 2, 10, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(43, 'Idiomas', NULL, 2, 1, 0, 2, 10, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(44, 'Música', NULL, 3, 1, 0, 2, 10, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(45, 'Tecnología', NULL, 4, 1, 0, 2, 10, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(46, 'Masajes', NULL, 1, 1, 0, 2, 11, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(47, 'Entrenamiento personal', NULL, 2, 1, 0, 2, 11, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(48, 'Belleza / Barbería', NULL, 3, 1, 0, 2, 11, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(49, 'Almuerzos por encargo', NULL, 1, 1, 0, 2, 12, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(50, 'Bocaditos', NULL, 2, 1, 0, 2, 12, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(51, 'Pastelería', NULL, 3, 1, 0, 2, 12, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(52, 'Decoración', NULL, 4, 1, 0, 2, 12, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(53, 'Taxi / Mototaxi', NULL, 1, 1, 0, 2, 13, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(54, 'Fletes / Mudanzas', NULL, 2, 1, 0, 2, 13, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(55, 'Envíos dentro del condominio', NULL, 3, 1, 0, 2, 13, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(56, 'Baño y peluquería', NULL, 1, 1, 0, 2, 14, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(57, 'Paseos', NULL, 2, 1, 0, 2, 14, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(58, 'Cuidado', NULL, 3, 1, 0, 2, 14, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(59, 'Babysitting', NULL, 1, 1, 0, 2, 15, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(60, 'Cuidado de adulto mayor', NULL, 2, 1, 0, 2, 15, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(61, 'Formateo / Instalación', NULL, 1, 1, 0, 2, 16, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(62, 'Redes / WiFi', NULL, 2, 1, 0, 2, 16, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(63, 'Configuración de equipos', NULL, 3, 1, 0, 2, 16, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(64, 'Sublimados', NULL, 1, 1, 0, 2, 17, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(65, 'Vinilos', NULL, 2, 1, 0, 2, 17, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(66, 'Bordados', NULL, 3, 1, 0, 2, 17, '2025-11-07 02:06:35', '2025-11-07 02:06:35'),
	(67, 'Varios', NULL, 1, 1, 0, 2, 18, '2025-11-07 02:06:36', '2025-11-07 02:06:36');

INSERT INTO `ubigeo_departamento` (`codigo_departamento`, `nombre_departamento`, `estado`) VALUES
	(15, 'Lima', 1);

INSERT INTO `ubigeo_provincia` (`codigo_provincia`, `codigo_departamento`, `nombre_provincia`, `estado`) VALUES
	(1501, 15, 'Lima', 1);

INSERT INTO `ubigeo_distrito` (`codigo_distrito`, `codigo_provincia`, `nombre_distrito`, `estado`) VALUES
	(15011, 1501, 'Villa el Salvador', 1),
	(150104, 1501, 'Barranco', 1),
	(150113, 1501, 'Chorrillos', 1);

INSERT INTO `condominio` (`codigo_condominio`, `nombre_condominio`, `direccion_condominio`, `codigo_distrito`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, 'Condominio Los Faisanes', 'Av. Los Faisanes 335', 150113, 'A', '2026-07-05 07:21:03', '2026-07-05 07:21:03'),
	(2, 'Condominio El Pilar', 'Av. Guardia Civil 953', 150113, 'A', '2026-07-05 07:21:03', '2026-07-05 07:21:03');

INSERT INTO `urbanizacion` (`codigo_urbanizacion`, `nombre_urbanizacion`, `direccion_urbanizacion`, `codigo_distrito`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, 'Urbanización Los Álamos', 'Av. Principal 123', 150113, 'A', '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(2, 'Urbanización Villa Flores', 'Avenida Los Algarrobos, 438', 15011, 'A', '2026-07-29 04:14:49', '2026-07-29 04:14:53');

INSERT INTO `torre` (`codigo_torre`, `nombre_torre`, `codigo_condominio`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, 'A', 1, 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(2, 'B', 1, 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(3, 'C', 1, 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(4, 'A1', 2, 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(5, 'A2', 2, 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(6, 'B1', 2, 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(7, 'B2', 2, 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04');

INSERT INTO `departamento` (`codigo_departamento`, `codigo_torre`, `numero_departamento`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, 1, '101', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(2, 1, '102', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(3, 1, '103', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(4, 1, '104', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(5, 1, '105', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(6, 2, '101', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(7, 2, '102', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(8, 2, '103', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(9, 2, '104', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(10, 2, '105', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(11, 3, '101', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(12, 3, '102', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(13, 3, '103', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(14, 3, '104', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(15, 3, '105', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(16, 4, '101', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(17, 4, '102', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(18, 4, '103', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(19, 4, '104', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(20, 4, '105', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(21, 5, '101', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(22, 5, '102', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(23, 5, '103', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(24, 5, '104', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(25, 5, '105', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(26, 6, '101', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(27, 6, '102', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(28, 6, '103', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(29, 6, '104', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(30, 6, '105', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(31, 7, '101', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(32, 7, '102', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(33, 7, '103', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(34, 7, '104', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04'),
	(35, 7, '105', 1, '2026-07-05 07:21:04', '2026-07-05 07:21:04');

INSERT INTO `pedido_incidencia_tipo` (`codigo_pedido_incidencia_tipo`, `nombre`, `descripcion`, `estado`) VALUES
	(1, 'Producto o servicio no conforme', 'El producto o servicio recibido no coincide con lo acordado.', 1),
	(2, 'Incumplimiento de coordinación', 'No se cumplió la coordinación pactada.', 1),
	(3, 'Trato inadecuado', 'Reporte relacionado con trato inadecuado.', 1),
	(4, 'Otro', 'Otro motivo de incidencia.', 1);

INSERT INTO `ev_funcionalidad` (`codigo_funcionalidad`, `clave`, `nombre`, `descripcion`, `valor_defecto`, `orden`, `estado`, `created_at`, `updated_at`) VALUES
	(1, 'PUBLICAR_PRODUCTOS', 'Publicar productos', 'Permite crear y enviar productos a revisión.', 1, 10, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(2, 'PUBLICAR_SERVICIOS', 'Publicar servicios', 'Permite crear y enviar servicios a revisión.', 1, 20, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(3, 'MARKETPLACE', 'Marketplace', 'Permite visualizar el marketplace de la comunidad.', 1, 30, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(4, 'COMPRAR_PRODUCTOS', 'Comprar productos', 'Permite crear solicitudes y pedidos de productos.', 1, 40, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(5, 'SOLICITAR_SERVICIOS', 'Solicitar servicios', 'Permite crear solicitudes de servicios.', 1, 50, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(6, 'BILLETERA', 'Billetera', 'Permite visualizar y operar la billetera del vecino.', 1, 60, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(7, 'PROMOCIONES', 'Promociones', 'Reserva funcional para campañas y promociones segmentadas.', 1, 70, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24');

INSERT INTO `ev_monetizacion_regla` (`codigo_monetizacion_regla`, `clave`, `nombre`, `descripcion`, `tipo_valor`, `unidad`, `valor_decimal_defecto`, `valor_booleano_defecto`, `valor_texto_defecto`, `orden`, `estado`, `created_at`, `updated_at`) VALUES
	(1, 'COMISION_VENTA_PRODUCTO', 'Comisión por venta de producto', 'Porcentaje aplicado al vendedor cuando acepta un pedido.', 'porcentaje', '%', 10.0000, NULL, NULL, 10, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(2, 'COSTO_PUBLICACION_PRODUCTO', 'Publicación de productos', 'Importe configurable por la publicación de productos para el alcance seleccionado.', 'monto', 'S/', 0.0000, NULL, NULL, 20, 1, '2026-07-29 04:06:24', '2026-07-29 07:08:23'),
	(3, 'COSTO_PUBLICACION_SERVICIO_DIA', 'Publicación de servicios por día', 'Importe diario configurable por la publicación de servicios para el alcance seleccionado.', 'monto', 'S/', 0.0000, NULL, NULL, 30, 1, '2026-07-29 04:06:24', '2026-07-29 07:08:23'),
	(4, 'COMISION_SERVICIO', 'Comisión por servicios', 'Porcentaje configurable para operaciones de servicios cuando el modelo comercial del alcance lo requiera.', 'porcentaje', '%', 0.0000, NULL, NULL, 40, 1, '2026-07-29 04:06:24', '2026-07-29 07:08:23'),
	(5, 'PUBLICACIONES_DESTACADAS', 'Publicaciones destacadas', 'Habilita cargos y operaciones para destacar publicaciones.', 'booleano', NULL, NULL, 1, NULL, 50, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(6, 'DESCUENTO_BILLETERA_PEDIDO', 'Descuentos desde billetera', 'Habilita el débito de billetera en solicitudes de productos preparados.', 'booleano', NULL, NULL, 1, NULL, 60, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(7, 'RECARGAS_HABILITADAS', 'Recargas', 'Habilita el registro y la atención de recargas.', 'booleano', NULL, NULL, 1, NULL, 70, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(8, 'BILLETERA_VISIBLE', 'Billetera visible', 'Control comercial adicional para mostrar la billetera.', 'booleano', NULL, NULL, 1, NULL, 80, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(9, 'BONO_BIENVENIDA_HABILITADO', 'Bono de bienvenida', 'Habilita la acreditación automática al aprobar una cuenta.', 'booleano', NULL, NULL, 1, NULL, 90, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24'),
	(10, 'BONO_BIENVENIDA_MONTO', 'Monto del bono de bienvenida', 'Importe acreditado cuando el bono está habilitado.', 'monto', 'S/', 15.0000, NULL, NULL, 100, 1, '2026-07-29 04:06:24', '2026-07-29 04:06:24');

-- Current legal versions

INSERT INTO `documento_legal`
(`codigo_documento_legal`,`tipo`,`slug`,`titulo`,`version`,`archivo_contenido`,`texto_consentimiento`,`hash_documento`,`estado`,`requiere_aceptacion`,`fecha_publicacion`,`fecha_vigencia`)
VALUES
(1,'terminos_condiciones','terminos-y-condiciones','Términos y Condiciones de Uso de Entre Vecinos','1.0','terminos_v1.php','Declaro que he leído, comprendido y acepto los Términos y Condiciones de Uso de Entre Vecinos – Versión 1.0.','bae3c3da51b62957594fd0ee790082e5e863d9faa0634bb6f503c92eff135fb2','vigente',1,'2026-07-12 00:00:00','2026-08-10 00:00:00'),
(7,'politica_privacidad','politica-de-privacidad','Política de Privacidad y Tratamiento de Datos Personales','1.0','privacidad_v1.php','Declaro que he leído la Política de Privacidad y otorgo mi consentimiento libre, previo, expreso e informado para el tratamiento de mis datos personales necesario para el registro, la validación de residencia y el uso de Entre Vecinos, conforme a las finalidades informadas – Versión 1.0.','a1b3e58b8a1baed1a7cd0fdbfdd3a950730323b18ba5dc4d832278ce4b2ec335','vigente',1,'2026-07-12 00:00:00','2026-08-10 00:00:00');

-- Menu items and role permissions

INSERT INTO `menu_item`
(`codigo_menu_item`,`codigo_menu`,`nombre`,`ruta`,`icono`,`orden`,`estado`) VALUES
(1,1,'Datos personales','/mi-perfil','bi-person-lines-fill',1,1),
(2,2,'Marketplace','/marketplace','bi-shop',1,1),
(3,2,'Mis pedidos','/mis-pedidos-comprador','bi-bag-check',2,1),
(4,2,'Mis solicitudes de servicio','/mis-solicitudes-servicio-comprador','bi-chat-square-text',3,1),
(5,3,'Mis publicaciones','/publicacion','bi-megaphone',1,1),
(6,3,'Pedidos recibidos','/mis-pedidos-vendedor','bi-bell',2,1),
(7,3,'Solicitudes de servicio','/mis-solicitudes-servicio-vendedor','bi-briefcase',3,1),
(8,3,'Mi billetera','/billetera','bi-cash-coin',4,1),
(9,4,'Comunidad','/comunidad','bi-people',1,1),
(10,4,'Gestionar publicaciones','/comunidad/gestionar','bi-pencil-square',2,1),
(11,4,'Moderación','/comunidad/moderacion','bi-shield-check',3,1),
(12,5,'Atender cuentas','/atender-cuentas','bi-person-check',1,1),
(13,5,'Atender publicaciones','/atender-publicacion','bi-card-checklist',2,1),
(14,5,'Atender recargas','/atender-recargas','bi-wallet2',3,1),
(15,5,'Atención de servicios','/atender-servicios','bi bi-clipboard2-pulse',4,1),
(16,1,'Notificaciones','/notificaciones','bi-bell',2,1),
(17,6,'Configuración de plataforma','/configuracion-plataforma','bi-toggles2',2,1),
(18,6,'Dashboard gerencial','/dashboard-gerencial','bi-graph-up-arrow',1,1),
(19,5,'Consulta de retiros','/gestion-retiros','bi-bank',5,1);

INSERT INTO `rol_menu_item`
(`codigo_rol_menu_item`,`codigo_rol`,`codigo_menu_item`,`puede_crear`,`puede_leer`,`puede_actualizar`,`puede_eliminar`) VALUES
(1,1,1,1,1,1,1),(2,1,2,1,1,1,1),(3,1,3,1,1,1,1),(4,1,4,1,1,1,1),
(5,1,5,1,1,1,1),(6,1,6,1,1,1,1),(7,1,7,1,1,1,1),(8,1,8,1,1,1,1),
(9,1,9,1,1,1,1),(10,1,10,1,1,1,1),(11,1,11,1,1,1,1),(12,1,12,1,1,1,1),
(13,1,13,1,1,1,1),(14,1,14,1,1,1,1),(15,1,15,1,1,1,1),(16,1,16,0,1,0,0),
(17,1,17,1,1,1,0),(18,1,18,1,1,1,0),(19,1,19,1,1,1,0),
(20,2,1,1,1,1,0),(21,2,2,0,1,0,0),(22,2,3,1,1,1,0),(23,2,4,1,1,1,0),
(24,2,5,1,1,1,1),(25,2,6,1,1,1,0),(26,2,7,1,1,1,0),(27,2,8,1,1,1,0),
(28,2,9,0,1,0,0),(29,2,16,0,1,0,0),
(30,3,12,1,1,1,0),(31,3,13,1,1,1,0),(32,3,14,1,1,1,0),(33,3,15,0,1,1,0),
(34,3,16,0,1,0,0),(35,3,19,0,1,0,0),
(36,4,1,1,1,1,0),(37,4,9,1,1,1,0),(38,4,10,1,1,1,1),(39,4,11,1,1,1,1),
(40,4,16,0,1,0,0);

-- Requested users

INSERT INTO `usuario`
(`codigo_usuario`,`nombre`,`email`,`clave`,`estado`,`codigo_rol`,`documento`,`telefono`,`foto_perfil`,`disponibilidad_pedidos`) VALUES
(1,'Administrador Sistema EV','administrador@entrevecinos.local','$2y$12$4b9aWe/jMtsKXWSta3ldguvR443MWYWivcWXU//bimeIojCVs/0hG',2,1,'00000001','900000001',NULL,0),
(2,'Soporte EV','soporte@entrevecinos.local','$2y$12$RVLZ62UX.NpK5rfLM1p7oeuY30/t0q1vUPS.tRXoaPSNGwetLuIjm',2,3,'00000002','900000002',NULL,0),
(3,'Vecino Prueba','vecino@entrevecinos.local','$2y$12$hIsc38e6xJc.GyzxMevigOkE/YmSCyj7sMOg6SRkrrAIQKWDFcOtm',2,2,'70000001','900000003',NULL,1);

INSERT INTO `usuario_residencia`
(`codigo_usuario_residencia`,`codigo_usuario`,`tipo_conjunto`,`codigo_condominio`,`codigo_urbanizacion`,`direccion`,`comprobante_domicilio`,`estado`) VALUES
(1,3,'urbanizacion',NULL,2,'Avenida Los Algarrobos 438',NULL,1);

INSERT INTO `usuario_revision`
(`codigo_usuario_revision`,`codigo_usuario`,`estado_revision`,`mensaje_observacion`,`comprobante_path`) VALUES
(1,3,2,NULL,NULL);

INSERT INTO `billetera`
(`codigo_billetera`,`codigo_usuario`,`saldo`,`saldo_actual`,`estado`) VALUES
(1,1,0.00,0.00,1),(2,2,0.00,0.00,1),(3,3,0.00,0.00,1);

INSERT INTO `usuario_documento_legal_aceptacion`
(`codigo_usuario`,`codigo_documento_legal`,`tipo_documento`,`version_documento`,`hash_documento`,`texto_consentimiento`,`hash_consentimiento`,`aceptado`,`origen`,`ip_aceptacion`,`user_agent`) VALUES
(1,1,'terminos_condiciones','1.0','bae3c3da51b62957594fd0ee790082e5e863d9faa0634bb6f503c92eff135fb2','Declaro que he leído, comprendido y acepto los Términos y Condiciones de Uso de Entre Vecinos – Versión 1.0.','7108ce6b273f3d484e1ee177f21ccd4de5aca7c163e4e359cffab366c0c9f055',1,'primer_ingreso',NULL,'Seed local EV 2026-09-05'),
(1,7,'politica_privacidad','1.0','a1b3e58b8a1baed1a7cd0fdbfdd3a950730323b18ba5dc4d832278ce4b2ec335','Declaro que he leído la Política de Privacidad y otorgo mi consentimiento libre, previo, expreso e informado para el tratamiento de mis datos personales necesario para el registro, la validación de residencia y el uso de Entre Vecinos, conforme a las finalidades informadas – Versión 1.0.','4fcb8278fd500ec775b31aad217eb45d889cc06ae1f1759f5e3d6f1f4a4176c6',1,'primer_ingreso',NULL,'Seed local EV 2026-09-05'),
(2,1,'terminos_condiciones','1.0','bae3c3da51b62957594fd0ee790082e5e863d9faa0634bb6f503c92eff135fb2','Declaro que he leído, comprendido y acepto los Términos y Condiciones de Uso de Entre Vecinos – Versión 1.0.','7108ce6b273f3d484e1ee177f21ccd4de5aca7c163e4e359cffab366c0c9f055',1,'primer_ingreso',NULL,'Seed local EV 2026-09-05'),
(2,7,'politica_privacidad','1.0','a1b3e58b8a1baed1a7cd0fdbfdd3a950730323b18ba5dc4d832278ce4b2ec335','Declaro que he leído la Política de Privacidad y otorgo mi consentimiento libre, previo, expreso e informado para el tratamiento de mis datos personales necesario para el registro, la validación de residencia y el uso de Entre Vecinos, conforme a las finalidades informadas – Versión 1.0.','4fcb8278fd500ec775b31aad217eb45d889cc06ae1f1759f5e3d6f1f4a4176c6',1,'primer_ingreso',NULL,'Seed local EV 2026-09-05'),
(3,1,'terminos_condiciones','1.0','bae3c3da51b62957594fd0ee790082e5e863d9faa0634bb6f503c92eff135fb2','Declaro que he leído, comprendido y acepto los Términos y Condiciones de Uso de Entre Vecinos – Versión 1.0.','7108ce6b273f3d484e1ee177f21ccd4de5aca7c163e4e359cffab366c0c9f055',1,'primer_ingreso',NULL,'Seed local EV 2026-09-05'),
(3,7,'politica_privacidad','1.0','a1b3e58b8a1baed1a7cd0fdbfdd3a950730323b18ba5dc4d832278ce4b2ec335','Declaro que he leído la Política de Privacidad y otorgo mi consentimiento libre, previo, expreso e informado para el tratamiento de mis datos personales necesario para el registro, la validación de residencia y el uso de Entre Vecinos, conforme a las finalidades informadas – Versión 1.0.','4fcb8278fd500ec775b31aad217eb45d889cc06ae1f1759f5e3d6f1f4a4176c6',1,'primer_ingreso',NULL,'Seed local EV 2026-09-05');

-- Platform configuration

INSERT INTO `ev_funcionalidad_configuracion`
(`codigo_funcionalidad_configuracion`,`codigo_funcionalidad`,`tipo_alcance`,`codigo_alcance`,`habilitada`,`modo_activacion`,`fecha_inicio`,`fecha_fin`,`mensaje_usuario`,`motivo`,`actualizado_por`,`estado_registro`) VALUES
(1,1,'global',0,1,'manual',NULL,NULL,NULL,'Base limpia DEV/QA',1,1),
(2,2,'global',0,1,'manual',NULL,NULL,NULL,'Base limpia DEV/QA',1,1),
(3,3,'global',0,1,'manual',NULL,NULL,NULL,'Base limpia DEV/QA',1,1),
(4,4,'global',0,1,'manual',NULL,NULL,NULL,'Base limpia DEV/QA',1,1),
(5,5,'global',0,1,'manual',NULL,NULL,NULL,'Base limpia DEV/QA',1,1),
(6,6,'global',0,1,'manual',NULL,NULL,NULL,'Base limpia DEV/QA',1,1),
(7,7,'global',0,1,'manual',NULL,NULL,NULL,'Base limpia DEV/QA',1,1);

INSERT INTO `ev_monetizacion_configuracion`
(`codigo_monetizacion_configuracion`,`codigo_monetizacion_regla`,`tipo_alcance`,`codigo_alcance`,`valor_decimal`,`valor_booleano`,`valor_texto`,`modo_activacion`,`fecha_inicio`,`fecha_fin`,`motivo`,`actualizado_por`,`estado_registro`) VALUES
(1,1,'global',0,0.0000,NULL,NULL,'manual',NULL,NULL,'Piloto gratuito: sin comisión por producto',1,1),
(2,2,'global',0,0.0000,NULL,NULL,'manual',NULL,NULL,'Piloto gratuito: publicación de producto sin costo',1,1),
(3,3,'global',0,0.0000,NULL,NULL,'manual',NULL,NULL,'Piloto gratuito: publicación de servicio sin costo',1,1),
(4,4,'global',0,0.0000,NULL,NULL,'manual',NULL,NULL,'Piloto gratuito: sin comisión por servicio',1,1),
(5,5,'global',0,NULL,0,NULL,'manual',NULL,NULL,'Destacados deshabilitados inicialmente',1,1),
(6,6,'global',0,NULL,1,NULL,'manual',NULL,NULL,'Billetera habilitada para productos preparados',1,1),
(7,7,'global',0,NULL,1,NULL,'manual',NULL,NULL,'Recargas habilitadas',1,1),
(8,8,'global',0,NULL,1,NULL,'manual',NULL,NULL,'Billetera visible en DEV/QA',1,1),
(9,9,'global',0,NULL,0,NULL,'manual',NULL,NULL,'Bono de bienvenida deshabilitado',1,1),
(10,10,'global',0,15.0000,NULL,NULL,'manual',NULL,NULL,'Monto de referencia de bono',1,1);

-- Withdrawal windows: payments Tuesday and Friday; S/20 retained in wallet

INSERT INTO `retiro_configuracion`
(`codigo_retiro_configuracion`,`nombre_jornada`,`dia_pago`,`dia_inicio_corte`,`hora_inicio_corte`,`dia_fin_corte`,`hora_fin_corte`,`saldo_minimo`,`activo`,`actualizado_por`) VALUES
(1,'Pago del martes',2,6,'00:00:00',7,'23:59:59',20.00,1,1),
(2,'Pago del viernes',5,2,'00:00:00',3,'23:59:59',20.00,1,1);

-- ============================================================================
-- POST-INSTALL VALIDATION
-- ============================================================================
SELECT COUNT(*) AS total_tablas_ev
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'entre_vecinos' AND TABLE_TYPE = 'BASE TABLE';

SELECT codigo_usuario, nombre, email, estado, codigo_rol
FROM usuario
ORDER BY codigo_usuario;

SELECT r.nombre AS rol, m.nombre AS menu, mi.nombre AS opcion, mi.ruta,
       rmi.puede_crear, rmi.puede_leer, rmi.puede_actualizar, rmi.puede_eliminar
FROM rol_menu_item rmi
INNER JOIN rol r ON r.codigo_rol = rmi.codigo_rol
INNER JOIN menu_item mi ON mi.codigo_menu_item = rmi.codigo_menu_item
INNER JOIN menu m ON m.codigo_menu = mi.codigo_menu
WHERE r.nombre IN ('admin','soporte','vecino')
ORDER BY r.codigo_rol, m.orden, mi.orden;

SELECT COUNT(*) AS columnas_pedido_nuevas
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA='entre_vecinos' AND TABLE_NAME='pedido'
  AND COLUMN_NAME IN ('acreditacion_vendedor_aplicada','monto_acreditado_vendedor');

SELECT COUNT(*) AS tablas_retiro
FROM information_schema.TABLES
WHERE TABLE_SCHEMA='entre_vecinos'
  AND TABLE_NAME IN ('usuario_cuenta_bancaria','retiro_configuracion','retiro_solicitud');

SELECT ROUTINE_NAME AS procedimiento_registro
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA='entre_vecinos' AND ROUTINE_TYPE='PROCEDURE'
  AND ROUTINE_NAME='sp_registrar_usuario_v2';

-- Valores esperados de las verificaciones estructurales:
-- total_tablas_ev = 62
-- columnas_pedido_nuevas = 2
-- tablas_retiro = 3
-- procedimiento_registro = sp_registrar_usuario_v2
