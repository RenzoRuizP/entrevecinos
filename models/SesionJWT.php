<?php
/*
    imprime un json: echo json_encode($usuario);            
    compara e imprime, si sale false no son iguales: var_dump(password_verify($this->clave, $usuario['clave'])); // ¿true o false?
    encripta: $nuevoHash = password_hash('123456', PASSWORD_BCRYPT);
    echo $nuevoHash; 

*/
require_once __DIR__ . '/../Config/EnvConfig.php';
require_once __DIR__ . '/../Config/JwtConfig.php';
require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class SesionJWT extends Conexion {

    private $email;
    private $clave;

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setClave($clave) {
        $this->clave = $clave;
    }

    public function iniciarSesionJWT() {
        try {
            $sql = "SELECT * FROM usuario WHERE email = :email";
            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) 
                return ['status' => 'NE']; // No existe
            if (!password_verify($this->clave, $usuario['clave'])) 
                return ['status' => 'CI']; // Contraseña incorrecta
            if ($usuario['estado'] !== '1') 
                return ['status' => 'IN']; // Usuario inactivo

            // Obtener roles del usuario
            $sqlRoles = "
                SELECT 
                    r.nombre as nombre_rol
                FROM usuario u  INNER JOIN rol r
                ON
                    u.codigo_rol = r.codigo_rol
                WHERE
                    u.codigo_usuario = :codigo_usuario
            ";
            $stmtRoles = $this->dblink->prepare($sqlRoles);
            $stmtRoles->bindParam(':codigo_usuario', $usuario['codigo_usuario']);
            $stmtRoles->execute();

            $rol = $stmtRoles->fetch(PDO::FETCH_COLUMN);

            // Datos del token
            $datosToken = [
                'codigo_usuario' => $usuario['codigo_usuario'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                //'nombre_rol' => $rol['nombre_rol']
                 'rol' => $rol
            ];
            // Generar JWT
            $token = JwtConfig::generarToken($datosToken);
           
            return [
                'status' => 'SI',
                'token' => $token,
                'rol' => $rol
            ];
            

        } catch (Exception $e) {
            error_log($e->getMessage());
            return ['status' => 'ER']; // Error interno
        }
    }

    public static function verificarToken(string $token) {
        $claveSecreta = $_ENV['JWT_SECRET_KEY'];

        try {
            $decoded = JWT::decode($token, new Key($claveSecreta, 'HS256'));
            return $decoded->data;
        } catch (ExpiredException $e) {
            // Token expirado
            return null;
        } catch (Exception $e) {
            // Token inválido
            return null;
        }
    }

    public function obtenerOpcionesMenu($nombre_rol) {
        try {
            $sql = "
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
                    r.nombre like :p_nombre_rol;
                ";
            $sentencia = $this->dblink->prepare($sql);
            $sentencia->bindParam(":p_nombre_rol", $nombre_rol);
            $sentencia->execute();
            $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (Exception $exc) {
            throw $exc;
        }
    }

    public function obtenerOpcionesMenuItem($nombreRol, $codigo_menu) {
        try {
            $sql = "
                    SELECT 
                        m_i.codigo_menu_item ,m_i.nombre, m_i.icono, m_i.ruta
                    FROM 
                        rol r INNER JOIN menu_item_accesos m_i_a
                    ON 
                        r.codigo_rol = m_i_a.codigo_rol INNER JOIN menu_item m_i
                    ON
                        m_i.codigo_menu_item = m_i_a.codigo_menu_item INNER JOIN menu m
                    ON
                        m.codigo_menu = m_i.codigo_menu
                    WHERE 
                        r.nombre LIKE :p_nombreRol and m.codigo_menu = :p_codigo_menu;
                ";
            
//            $sentencia = $this->dbLink->prepare($sql);
            $sentencia = $this->dblink->prepare($sql);
            $sentencia->bindParam(":p_nombreRol", $nombreRol);
            $sentencia->bindParam(":p_codigo_menu", $codigo_menu);
            $sentencia->execute();
            $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
            
        } catch (Exception $exc) {
            throw $exc;
        }
    }

}
/*
    $test = new SesionJWT();
    $test->setEmail('juan@example.com');
    $test->setClave('123456');

    $test->iniciarSesionJWT();

    */