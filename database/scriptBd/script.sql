CREATE DATABASE entre_vecinos_bd;
entre_vecinos_bdUSE entre_vecinos_bd;
DROP DATABASE entre_vecinos_bd;

SELECT * FROM rol;
SELECT * FROM usuario;
SELECT * FROM sub_menu;
SELECT * FROM menu;
SELECT * FROM accesos;

DROP TABLE sub_menu


-- Tabla: rol
CREATE TABLE rol (
 codigo_rol INT AUTO_INCREMENT PRIMARY KEY,
 nombre_rol VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla: usuario
CREATE TABLE usuario (
    codigo_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A',
    codigo_rol INT NOT NULL,
    FOREIGN KEY(codigo_rol) REFERENCES rol(codigo_rol)
);

CREATE TABLE menu (
    codigo_menu INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL
);

CREATE TABLE sub_menu (
    codigo_sub_menu INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    codigo_menu INT NOT NULL,
    FOREIGN KEY(codigo_menu) REFERENCES menu(codigo_menu)
);

-- Tabla intermedia: usuario_rol (relación muchos a muchos)
CREATE TABLE accesos (
	 codigo_accesos INT NOT NULL,
    codigo_rol INT NOT NULL,
    codigo_menu INT NOT NULL,
    PRIMARY KEY (codigo_accesos),
    FOREIGN KEY (codigo_rol) REFERENCES rol(codigo_rol),
    FOREIGN KEY (codigo_menu) REFERENCES menu(codigo_menu)
); 


INSERT INTO rol (nombre_rol) VALUES ('administrador'), ('vecino');

INSERT INTO menu (nombre)
VALUES ('Mis datos');

INSERT INTO menu (nombre)
VALUES ('Compras');

INSERT INTO menu (nombre)
VALUES ('Ventas');

/*
Registrar/editar/anular pedidos, listar

Publicar el pedido

Hacer seguimiento al estado del pedido (aceptado, rechazado, entregado)
*/
INSERT INTO sub_menu (nombre, codigo_menu)
VALUES ('Mis pedidos', 2);

INSERT INTO sub_menu (nombre, codigo_menu)
VALUES ('Reportes', 2);

INSERT INTO sub_menu (nombre)
VALUES ('Mis compras', 2);


-- Ejemplo: insertar un usuario "vecino"
INSERT INTO usuario (nombre, email, clave, codigo_rol)
VALUES ('Juan Pérez', 'juan@example.com', '$2y$10$/B9/83Ch5OG0/wGK/y3iYenuEjIHmUJbSAwj9UZtJwNX8E2ZgfoKm', 2); -- 123456

-- Suponiendo que se le asigna ID 1, se le asigna el rol "vecino"
INSERT INTO accesos (codigo_usuario, codigo_rol)
VALUES (1, 2);

-- Si luego elige ser 'comprador', se le asigna rol adicional
INSERT INTO usuario_rol (codigo_usuario, codigo_rol)
VALUES (1, 2); -- 2 = 'comprador'

-- rol:
-- 1: admin
-- 2: vecino

-- menú:
-- 1: Mis datos
-- 2: Compras
-- 3: Ventas
SELECT * FROM accesos

INSERT INTO accesos (codigo_accesos, codigo_rol, codigo_menu)
VALUES (1, 1, 1);

INSERT INTO accesos (codigo_accesos, codigo_rol, codigo_menu)
VALUES (2, 1, 2);

INSERT INTO accesos (codigo_accesos, codigo_rol, codigo_menu)
VALUES (3, 1, 3);

INSERT INTO accesos (codigo_accesos, codigo_rol, codigo_menu)
VALUES (4, 2, 1);

INSERT INTO accesos (codigo_accesos, codigo_rol, codigo_menu)
VALUES (5, 2, 2);

INSERT INTO accesos (codigo_accesos, codigo_rol, codigo_menu)
VALUES (6, 2, 3);

SELECT 
	r.nombre_rol
FROM usuario u  INNER JOIN rol r
ON
	u.codigo_rol = r.codigo_rol
WHERE
	u.codigo_usuario = 1

SELECT r.nombre_rol
                FROM accesos a
                JOIN rol r ON a.codigo_rol = r.codigo_rol
                WHERE a.codigo_usuario = 1
