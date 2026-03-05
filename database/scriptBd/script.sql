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
ALTER TABLE menu
ADD COLUMN icono VARCHAR(100);

CREATE TABLE menu_item  (
    codigo_menu_item INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    archivo VARCHAR(255) NOT NULL,
    codigo_menu INT NOT NULL,
    FOREIGN KEY(codigo_menu) REFERENCES menu(codigo_menu)
);
ALTER TABLE menu_item
ADD COLUMN icono VARCHAR(100);

SELECT * FROM menu;


UPDATE menu SET icono = 'fas fa-shopping-cart' WHERE nombre = 'Vender';
UPDATE menu SET icono = 'fas fa-shopping-bag' WHERE nombre = 'Comprar';
UPDATE menu SET icono = 'fas fa-id-card' WHERE nombre = 'Mis Datos';

CREATE TABLE menu_item_accesos (
    codigo_menu_item_accesos INT AUTO_INCREMENT PRIMARY KEY,
    codigo_menu_item INT NOT NULL,
	 -- codigo_menu INT NOT NULL,
    codigo_rol INT NOT NULL,
    acceso CHAR(1) NULL, -- A: activo, I= inactivo
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
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso ) 
VALUES (1, 1, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso ) 
VALUES (2, 1, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso ) 
VALUES (3, 1, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso ) 
VALUES (4, 1, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso )
VALUES (5, 1, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso )
VALUES (6, 1, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso )
VALUES (7, 1, 'A');

-- vecino
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso )
VALUES (1, 2, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso )
VALUES (2, 2, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso )
VALUES (3, 2, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso )
VALUES (4, 2, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso )
VALUES (5, 2, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso )
VALUES (6, 2, 'A');
INSERT INTO menu_item_accesos( codigo_menu_item, codigo_rol, acceso ) 
VALUES (7, 2, 'A');



SELECT * FROM usuario WHERE email = 'juan@example.com';

-- roles

SELECT 
                    r.nombre as nombre_rol
                FROM usuario u  INNER JOIN rol r
                ON
                    u.codigo_rol = r.codigo_rol
                WHERE
                    u.codigo_usuario = 1


-- Solo muestra menú según su rol
SELECT 
	distinct m.codigo_menu, 
	m.nombre
FROM 
	rol r INNER JOIN menu_item_accesos m_i_a
ON 
	r.codigo_rol = m_i_a.codigo_rol INNER JOIN menu_item m_i
ON
	m_i.codigo_menu_item = m_i_a.codigo_menu_item INNER JOIN menu m
ON
	m.codigo_menu = m_i.codigo_menu
WHERE 
	r.nombre like 'vecino' and m_i_a.acceso = 'A';
	

-- solo muestra los menu items con accesos

SELECT 
	m_i.codigo_menu_item ,m_i.nombre
FROM 
	rol r INNER JOIN menu_item_accesos m_i_a
ON 
	r.codigo_rol = m_i_a.codigo_rol INNER JOIN menu_item m_i
ON
	m_i.codigo_menu_item = m_i_a.codigo_menu_item INNER JOIN menu m
ON
	m.codigo_menu = m_i.codigo_menu
WHERE 
	r.nombre LIKE 'vecino' and m.codigo_menu = 1 and m_i_a.acceso = 'A';
	
	
	
	
	
	
	
	
	
	
	
SELECT 
                        m_i.codigo_menu_item ,m_i.nombre
                    FROM 
                        rol r INNER JOIN menu_item_accesos m_i_a
                    ON 
                        r.codigo_rol = m_i_a.codigo_rol INNER JOIN menu_item m_i
                    ON
                        m_i.codigo_menu_item = m_i_a.codigo_menu_item INNER JOIN menu m
                    ON
                        m.codigo_menu = m_i.codigo_menu
                    WHERE 
                        r.nombre LIKE 'vecino' and m.codigo_menu = 1 and m_i_a.acceso = 'A';

	


	
	





SELECT 
    m.codigo_menu,
    m.nombre AS nombre_menu,
    GROUP_CONCAT(m_i.nombre ORDER BY m_i.codigo_menu_item ASC SEPARATOR ', ') AS menu_items
FROM 
    rol r
INNER JOIN menu_item_accesos m_i_a ON r.codigo_rol = m_i_a.codigo_rol
INNER JOIN menu_item m_i ON m_i.codigo_menu_item = m_i_a.codigo_menu_item
INNER JOIN menu m ON m.codigo_menu = m_i.codigo_menu
WHERE 
    r.codigo_rol = 2 
    AND m_i_a.acceso = 'A'
GROUP BY 
    m.codigo_menu, m.nombre
ORDER BY 
    m.codigo_menu ASC;


SELECT * FROM menu;
SELECT * FROM menu_item;
SELECT * FROM menu_item_accesos;



select
   distinct 
            m.codigo_menu,
            m.nombre,
            m.icono
from
   menu m INNER JOIN menu_item mi
ON
	( m.codigo_menu = mi.codigo_menu ) INNER JOIN menu_item_accesos mia 
ON 
	( mi.codigo_menu_item = mia.codigo_menu_item )
where
   mia.codigo_rol = 2
   and mia.acceso = 'A'
ORDER by
   1
   
   
   
   SELECT * FROM menu_item
   
   bi bi-person-badge-fill
   
   
   
SELECT 
                    distinct m.codigo_menu, 
                    m.nombre,
                    m.icono
                FROM 
                    rol r INNER JOIN menu_item_accesos m_i_a
                ON 
                    r.codigo_rol = m_i_a.codigo_rol INNER JOIN menu_item m_i
                ON
                    m_i.codigo_menu_item = m_i_a.codigo_menu_item INNER JOIN menu m
                ON
                    m.codigo_menu = m_i.codigo_menu
                WHERE 
                    r.nombre LIKE 'vecino' and m_i_a.acceso = 'A';
                    
                    
                    
                    
                    
                    
                    
                    
-- Verifica que condominio tiene codigo_distrito
SHOW COLUMNS FROM condominio LIKE 'codigo_distrito';

-- Verifica FK y el índice
SHOW CREATE TABLE condominio;

-- Verifica que urbanizacion existe
SHOW TABLES LIKE 'urbanizacion';

-- Verifica ubigeo
SHOW TABLES LIKE 'ubigeo_%';








INSERT INTO ubigeo_departamento (codigo_departamento, nombre_departamento, estado)
VALUES (15, 'Lima', 'A');

INSERT INTO ubigeo_provincia (codigo_provincia, codigo_departamento, nombre_provincia, estado)
VALUES (1501, 15, 'Lima', 'A');

INSERT INTO ubigeo_distrito (codigo_distrito, codigo_provincia, nombre_distrito, estado)
VALUES
(150113, 1501, 'Chorrillos', 'A'),
(150104, 1501, 'Barranco', 'A');


UPDATE condominio
SET codigo_distrito = 150113
WHERE codigo_condominio IN (1,2);

INSERT INTO urbanizacion (nombre_urbanizacion, direccion_urbanizacion, codigo_distrito, estado)
VALUES ('Urbanización Los Álamos', 'Av. Principal 123', 150113, 'A');


DESCRIBE usuario_residencia_solicitud;
DESCRIBE producto;

DESCRIBE usuario;
DESCRIBE provincia;
DESCRIBE distrito;




SELECT
 u.codigo_usuario,
 u.estado AS usuario_estado,
 ur.estado_revision
FROM usuario u
LEFT JOIN usuario_revision ur ON ur.codigo_usuario = u.codigo_usuario
WHERE
 u.estado = 1 AND ur.estado_revision = 3;
 
 
 SELECT * FROM usuario;
 SELECT * FROM usuario_revision;
 
 
 
 
 
 
 
 
 
 
 
SELECT u.codigo_usuario, u.nombre, u.estado
FROM usuario u
WHERE EXISTS (
  SELECT 1
  FROM usuario_revision ur2
  WHERE ur2.codigo_usuario = u.codigo_usuario
    AND ur2.estado_revision = 3
);

