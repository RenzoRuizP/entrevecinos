USE entre_vecinos;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- 1. ROLES
-- =========================================================
INSERT INTO rol (codigo_rol, nombre, descripcion, estado) VALUES
(1, 'admin', 'Administrador del sistema', 1),
(2, 'vecino', 'Usuario vecino comprador/vendedor', 1),
(3, 'soporte', 'Usuario de soporte', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion),
  estado = VALUES(estado);

-- =========================================================
-- 2. UBIGEO BASE
-- =========================================================
INSERT INTO ubigeo_departamento (codigo_departamento, nombre, estado) VALUES
(1, 'Lima', 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), estado = VALUES(estado);

INSERT INTO ubigeo_provincia (codigo_provincia, codigo_departamento, nombre, estado) VALUES
(1, 1, 'Lima', 1)
ON DUPLICATE KEY UPDATE
  codigo_departamento = VALUES(codigo_departamento),
  nombre = VALUES(nombre),
  estado = VALUES(estado);

INSERT INTO ubigeo_distrito (codigo_distrito, codigo_provincia, nombre, estado) VALUES
(1, 1, 'San Juan de Miraflores', 1),
(2, 1, 'Santiago de Surco', 1),
(3, 1, 'Miraflores', 1)
ON DUPLICATE KEY UPDATE
  codigo_provincia = VALUES(codigo_provincia),
  nombre = VALUES(nombre),
  estado = VALUES(estado);

-- =========================================================
-- 3. CONDOMINIOS / URBANIZACIONES DEMO
-- =========================================================
INSERT INTO condominio (codigo_condominio, nombre_condominio, direccion, codigo_distrito, estado) VALUES
(1, 'Condominio Los Pinos', 'Av. Los Pinos 123', 1, 1),
(2, 'Condominio Vista Verde', 'Jr. Vista Verde 456', 2, 1)
ON DUPLICATE KEY UPDATE
  nombre_condominio = VALUES(nombre_condominio),
  direccion = VALUES(direccion),
  codigo_distrito = VALUES(codigo_distrito),
  estado = VALUES(estado);

INSERT INTO torre (codigo_torre, codigo_condominio, nombre, estado) VALUES
(1, 1, 'Torre A', 1),
(2, 1, 'Torre B', 1),
(3, 2, 'Torre Única', 1)
ON DUPLICATE KEY UPDATE
  codigo_condominio = VALUES(codigo_condominio),
  nombre = VALUES(nombre),
  estado = VALUES(estado);

INSERT INTO departamento (codigo_departamento_interno, codigo_torre, nombre, estado) VALUES
(1, 1, '101', 1),
(2, 1, '102', 1),
(3, 2, '201', 1),
(4, 3, '301', 1)
ON DUPLICATE KEY UPDATE
  codigo_torre = VALUES(codigo_torre),
  nombre = VALUES(nombre),
  estado = VALUES(estado);

INSERT INTO urbanizacion (codigo_urbanizacion, nombre_urbanizacion, direccion, codigo_distrito, estado) VALUES
(1, 'Urbanización Santa Rosa', 'Mz A Lt 10', 1, 1),
(2, 'Urbanización Los Jardines', 'Mz C Lt 5', 3, 1)
ON DUPLICATE KEY UPDATE
  nombre_urbanizacion = VALUES(nombre_urbanizacion),
  direccion = VALUES(direccion),
  codigo_distrito = VALUES(codigo_distrito),
  estado = VALUES(estado);

-- =========================================================
-- 4. CATEGORÍAS / TIPOS
-- =========================================================
INSERT INTO categoria_grupo (codigo_categoria_grupo, nombre, descripcion, estado) VALUES
(1, 'Productos', 'Venta de productos entre vecinos', 1),
(2, 'Servicios', 'Servicios entre vecinos', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion),
  estado = VALUES(estado);

INSERT INTO tipo (codigo_tipo, codigo_categoria_grupo, nombre, descripcion, estado) VALUES
(1, 1, 'Abarrotes', 'Productos de consumo', 1),
(2, 1, 'Comida preparada', 'Alimentos listos o por preparar', 1),
(3, 1, 'Mascotas', 'Accesorios y alimentos para mascotas', 1),
(4, 2, 'Limpieza', 'Servicios de limpieza', 1),
(5, 2, 'Soporte técnico', 'Soporte tecnológico básico', 1)
ON DUPLICATE KEY UPDATE
  codigo_categoria_grupo = VALUES(codigo_categoria_grupo),
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion),
  estado = VALUES(estado);

-- =========================================================
-- 5. MENÚS
-- =========================================================
INSERT INTO menu (codigo_menu, nombre, icono, orden, estado) VALUES
(1, 'Principal', 'bi bi-grid', 1, 1),
(2, 'Marketplace', 'bi bi-shop', 2, 1),
(3, 'Cuenta', 'bi bi-person', 3, 1),
(4, 'Soporte', 'bi bi-shield-check', 4, 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  icono = VALUES(icono),
  orden = VALUES(orden),
  estado = VALUES(estado);

INSERT INTO menu_item (codigo_menu_item, codigo_menu, nombre, icono, ruta, orden, estado) VALUES
(1, 1, 'Inicio', 'bi bi-house', '/MenuPrincipal', 1, 1),

(2, 2, 'Marketplace', 'bi bi-bag', '/marketplace', 1, 1),
(3, 2, 'Publicación', 'bi bi-megaphone', '/publicacion', 2, 1),
(4, 2, 'Mis pedidos comprador', 'bi bi-cart-check', '/mis-pedidos-comprador', 3, 1),
(5, 2, 'Mis pedidos vendedor', 'bi bi-box-seam', '/mis-pedidos-vendedor', 4, 1),
(6, 2, 'Recibir', 'bi bi-inbox', '/recibir', 5, 1),

(7, 3, 'Mi perfil', 'bi bi-person-circle', '/mi-perfil', 1, 1),
(8, 3, 'Billetera', 'bi bi-wallet2', '/billetera', 2, 1),
(9, 3, 'Credencial', 'bi bi-person-vcard', '/credencial', 3, 1),
(10, 3, 'Notificaciones residencia', 'bi bi-bell', '/notificaciones-residencia', 4, 1),

(11, 4, 'Dashboard soporte', 'bi bi-speedometer2', '/MenuPrincipal', 1, 1),
(12, 4, 'Atender recargas', 'bi bi-cash-coin', '/atender-recargas', 2, 1),
(13, 4, 'Atender publicación', 'bi bi-card-checklist', '/atender-publicacion', 3, 1),
(14, 4, 'Atender cuentas', 'bi bi-person-check', '/atender-cuentas', 4, 1)
ON DUPLICATE KEY UPDATE
  codigo_menu = VALUES(codigo_menu),
  nombre = VALUES(nombre),
  icono = VALUES(icono),
  ruta = VALUES(ruta),
  orden = VALUES(orden),
  estado = VALUES(estado);

-- =========================================================
-- 6. ASIGNACIÓN MENÚS POR ROL
-- =========================================================
INSERT IGNORE INTO rol_menu_item (codigo_rol, codigo_menu_item)
SELECT 2, codigo_menu_item
FROM menu_item
WHERE codigo_menu_item IN (1,2,3,4,5,6,7,8,9,10);

INSERT IGNORE INTO rol_menu_item (codigo_rol, codigo_menu_item)
SELECT 3, codigo_menu_item
FROM menu_item
WHERE codigo_menu_item IN (1,11,12,13,14);

INSERT IGNORE INTO rol_menu_item (codigo_rol, codigo_menu_item)
SELECT 1, codigo_menu_item
FROM menu_item;

-- =========================================================
-- 7. USUARIOS DEMO
-- claves:
-- admin@ev.com    => Admin123*
-- soporte@ev.com  => Soporte123*
-- vecino@ev.com   => Vecino123*
-- =========================================================
INSERT INTO usuario (
  codigo_usuario, codigo_rol, nombre, email, clave, documento, telefono, estado
) VALUES
(
  1, 1, 'Administrador EV', 'admin@ev.com',
  '$2y$12$c2mi7ydNdfKgdFzm6.yCVeuqomj.GsjXFLY64G1fVuwBzKIqjZZVW',
  '70000001', '999111111', 2
),
(
  2, 3, 'Soporte EV', 'soporte@ev.com',
  '$2y$12$BFSJ6oCiBTVN9trRW6wVn.7S8xnivwvEBeOCCFdfB0HOJPu0.MYAu',
  '70000002', '999222222', 2
),
(
  3, 2, 'Vecino Demo', 'vecino@ev.com',
  '$2y$12$aSFR7QbaAPhNjV35AgU6HepjMwYGFYRgPDJ7Tfn2jTWRVbzZAFeuy',
  '70000003', '999333333', 2
)
ON DUPLICATE KEY UPDATE
  codigo_rol = VALUES(codigo_rol),
  nombre = VALUES(nombre),
  clave = VALUES(clave),
  documento = VALUES(documento),
  telefono = VALUES(telefono),
  estado = VALUES(estado);

-- =========================================================
-- 8. BILLETERAS BASE
-- =========================================================
INSERT INTO billetera (codigo_billetera, codigo_usuario, saldo, estado) VALUES
(1, 1, 0.00, 1),
(2, 2, 0.00, 1),
(3, 3, 100.00, 1)
ON DUPLICATE KEY UPDATE
  saldo = VALUES(saldo),
  estado = VALUES(estado);

-- =========================================================
-- 9. DISPONIBILIDAD BASE
-- =========================================================
CALL sp_guardar_disponibilidad_pedidos(3, 1);

-- =========================================================
-- 10. RESIDENCIA VIGENTE DEMO
-- =========================================================
INSERT INTO usuario_residencia (
  codigo_usuario_residencia,
  codigo_usuario,
  tipo_conjunto,
  codigo_condominio,
  codigo_urbanizacion,
  ubigeo_departamento,
  ubigeo_provincia,
  ubigeo_distrito,
  direccion,
  comprobante_domicilio
) VALUES
(
  1, 3, 'condominio', 1, NULL, 1, 1, 1,
  'Av. Los Pinos 123 - Torre A - Dpto 101',
  'resources/uploads/comprobantes/demo_residencia_vecino.pdf'
)
ON DUPLICATE KEY UPDATE
  tipo_conjunto = VALUES(tipo_conjunto),
  codigo_condominio = VALUES(codigo_condominio),
  codigo_urbanizacion = VALUES(codigo_urbanizacion),
  ubigeo_departamento = VALUES(ubigeo_departamento),
  ubigeo_provincia = VALUES(ubigeo_provincia),
  ubigeo_distrito = VALUES(ubigeo_distrito),
  direccion = VALUES(direccion),
  comprobante_domicilio = VALUES(comprobante_domicilio);

-- =========================================================
-- 11. PRODUCTOS DEMO
-- =========================================================
INSERT INTO producto (
  codigo_producto,
  codigo_usuario,
  codigo_tipo,
  titulo,
  descripcion,
  precio,
  stock,
  visible,
  destacado,
  fecha_destacado,
  requiere_preparacion,
  comentario_revision,
  codigo_usuario_soporte_revision,
  fecha_revision
) VALUES
(
  1, 3, 2,
  'Arroz con pollo',
  'Porción lista para entrega dentro del condominio.',
  12.50,
  10,
  2,
  1,
  NOW(),
  1,
  'Aprobado para publicación.',
  2,
  NOW()
),
(
  2, 3, 1,
  'Gaseosa 1.5L',
  'Producto sellado y disponible para entrega inmediata.',
  7.50,
  20,
  2,
  0,
  NULL,
  0,
  'Aprobado para publicación.',
  2,
  NOW()
)
ON DUPLICATE KEY UPDATE
  codigo_usuario = VALUES(codigo_usuario),
  codigo_tipo = VALUES(codigo_tipo),
  titulo = VALUES(titulo),
  descripcion = VALUES(descripcion),
  precio = VALUES(precio),
  stock = VALUES(stock),
  visible = VALUES(visible),
  destacado = VALUES(destacado),
  fecha_destacado = VALUES(fecha_destacado),
  requiere_preparacion = VALUES(requiere_preparacion),
  comentario_revision = VALUES(comentario_revision),
  codigo_usuario_soporte_revision = VALUES(codigo_usuario_soporte_revision),
  fecha_revision = VALUES(fecha_revision);

INSERT INTO producto_imagen (codigo_producto_imagen, codigo_producto, ruta, es_portada, orden) VALUES
(1, 1, 'resources/images/demo/arroz_con_pollo.jpg', 1, 1),
(2, 2, 'resources/images/demo/gaseosa_15l.jpg', 1, 1)
ON DUPLICATE KEY UPDATE
  codigo_producto = VALUES(codigo_producto),
  ruta = VALUES(ruta),
  es_portada = VALUES(es_portada),
  orden = VALUES(orden);

-- =========================================================
-- 12. TIPOS INCIDENCIA PEDIDO
-- =========================================================
INSERT INTO pedido_incidencia_tipo (codigo_tipo_incidencia, nombre, descripcion, estado) VALUES
(1, 'No confirmó entrega', 'El comprador no confirmó la entrega final', 1),
(2, 'Demora excesiva', 'Se reportó demora en la atención o entrega', 1),
(3, 'Producto incorrecto', 'El producto recibido no coincide con lo solicitado', 1),
(4, 'Otro', 'Incidencia general', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion),
  estado = VALUES(estado);

-- =========================================================
-- 13. NORMALIZACIÓN SEGURA PEDIDOS
-- =========================================================
UPDATE pedido
   SET confirmado_cola = 1
 WHERE estado IN (
   'cola_aceptada',
   'pendiente_vendedor',
   'en_preparacion',
   'despachando',
   'listo_para_entrega',
   'en_camino',
   'en_punto_entrega',
   'entregado_vendedor',
   'entrega_confirmada_comprador'
 );

UPDATE pedido
   SET entrega_confirmada_comprador = 1
 WHERE estado = 'entrega_confirmada_comprador';

-- =========================================================
-- 14. VALIDACIÓN RÁPIDA
-- =========================================================
SELECT 'rol' AS tabla, COUNT(*) AS total FROM rol
UNION ALL
SELECT 'usuario', COUNT(*) FROM usuario
UNION ALL
SELECT 'usuario_residencia', COUNT(*) FROM usuario_residencia
UNION ALL
SELECT 'producto', COUNT(*) FROM producto
UNION ALL
SELECT 'billetera', COUNT(*) FROM billetera
UNION ALL
SELECT 'pedido', COUNT(*) FROM pedido;

SET FOREIGN_KEY_CHECKS = 1;