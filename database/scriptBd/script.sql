CREATE DATABASE entre_vecinos_bd;
USE entre_vecinos_bd;
DROP DATABASE entre_vecinos_bd;

SELECT * FROM rol;
SELECT * FROM usuario;
SELECT * FROM menu;
SELECT * FROM menu_item;
SELECT * FROM menu_item_accesos;

DROP TABLE menu_item_accesos
-- Tabla: rol
CREATE TABLE rol (
 codigo_rol INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(50) NOT NULL UNIQUE,
 descripcion VARCHAR(100) NULL
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

DROP TABLE menu;
DROP TABLE menu_item;
DROP TABLE menu_item_accesos;

CREATE TABLE menu (
    codigo_menu INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL
);

CREATE TABLE menu_item  (
    codigo_menu_item INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    archivo VARCHAR(255) NOT NULL,
    codigo_menu INT NOT NULL,
    FOREIGN KEY(codigo_menu) REFERENCES menu(codigo_menu)
);

CREATE TABLE menu_item_accesos (
    codigo_menu_item_accesos INT AUTO_INCREMENT PRIMARY KEY,
    codigo_menu_item INT NOT NULL,
	 -- codigo_menu INT NOT NULL,
    codigo_rol INT NOT NULL,
    FOREIGN KEY(codigo_menu_item) REFERENCES menu_item(codigo_menu_item),
    -- FOREIGN KEY(codigo_menu) REFERENCES menu(codigo_menu),
    FOREIGN KEY(codigo_rol) REFERENCES rol(codigo_rol)    
);

/*
CREATE TABLE accesos (
	 codigo_accesos INT NOT NULL,
    codigo_rol INT NOT NULL,
    codigo_menu INT NOT NULL,
    PRIMARY KEY (codigo_accesos),
    FOREIGN KEY (codigo_rol) REFERENCES rol(codigo_rol),
    FOREIGN KEY (codigo_menu) REFERENCES menu(codigo_menu)
); 

*/

SELECT * FROM rol;
SELECT * FROM usuario;
SELECT * FROM menu;
SELECT * FROM menu_item;
SELECT * FROM menu_item_accesos;

INSERT INTO rol (nombre, descripcion) VALUES
('administrador', 'Acceso total al sistema'),
('vecino', 'Usuario con acceso básico');

INSERT INTO usuario (nombre, email, clave, codigo_rol)
VALUES ('Juan Pérez', 'juan@example.com', '$2y$10$/B9/83Ch5OG0/wGK/y3iYenuEjIHmUJbSAwj9UZtJwNX8E2ZgfoKm', 2); -- 123456

INSERT INTO menu (nombre) VALUES ('Mis Datos'),('Comprar'),('Vender');

INSERT INTO menu_item (nombre, codigo_menu)
VALUES ('Registrar mis datos', 1);

INSERT INTO menu_item (nombre, codigo_menu)
VALUES ('Gestionar Compras', 2), ('Publicar Compras', 2),('Reportes Compras', 2);

INSERT INTO menu_item (nombre, codigo_menu)
VALUES ('Gestionar Venta', 3), ('Publicar Venta', 3),('Reportes Venta', 3);

/*
Menu
1:Mis Datos
2:Comprar
3:Vender

menu item


rol:
1:admin
2:vecino

*/
-- admin
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol ) 
VALUES (1, 1);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol ) 
VALUES (2, 1);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol ) 
VALUES (3, 1);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol ) 
VALUES (4, 1);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol )
VALUES (5, 1);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol )
VALUES (6, 1);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol )
VALUES (7, 1);

-- vecino
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol )
VALUES (1, 2);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol )
VALUES (2, 2);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol )
VALUES (3, 2);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol )
VALUES (4, 2);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol )
VALUES (5, 2);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol )
VALUES (6, 2);
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol ) 
VALUES (7, 2);



SELECT * FROM usuario WHERE email = 'juan@example.com';

-- roles

SELECT 
                    r.nombre
                FROM usuario u  INNER JOIN rol r
                ON
                    u.codigo_rol = r.codigo_rol
                WHERE
                    u.codigo_usuario = 1


SELECT 
	m_i.codigo_menu_item ,m_i.nombre
FROM 
	rol r INNER JOIN menu_item_accesos m_i_a
ON 
	r.codigo_rol = m_i_a.codigo_rol INNER JOIN menu_item m_i
ON
	m_i.codigo_menu_item = m_i_a.codigo_menu_item
WHERE 
	r.codigo_rol = :rol
ORDER BY 
	m_i.nombre ASC



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
