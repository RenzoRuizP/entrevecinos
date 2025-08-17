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

            // var_dump($usuario);

            if (!$usuario) 
                return ['status' => 'NE']; // No existe
            if (!password_verify($this->clave, $usuario['clave'])) 
                return ['status' => 'CI']; // Contraseña incorrecta
            if ($usuario['estado'] !== 'A') 
                return ['status' => 'IN']; // Usuario inactivo

            // Obtener roles del usuario
            $sqlRoles = "
                SELECT 
                    r.nombre_rol
                FROM usuario u  INNER JOIN rol r
                ON
                    u.codigo_rol = r.codigo_rol
                WHERE
                    u.codigo_usuario = :codigo_usuario
            ";
            $stmtRoles = $this->dblink->prepare($sqlRoles);
            $stmtRoles->bindParam(':codigo_usuario', $usuario['codigo_usuario']);
            $stmtRoles->execute();

            $roles = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);

            // Datos del token
            $datosToken = [
                'codigo_usuario' => $usuario['codigo_usuario'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'roles' => $roles
            ];

            // Generar JWT
            $token = JwtConfig::generarToken($datosToken);

            return [
                'status' => 'SI',
                'token' => $token
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

    public function obtenerOpcionesMenu($codigoCargo) {
        try {
            $sql = "
                select
                        distinct 
                        m.codigo_menu,
                        m.nombre
                from
                        menu m
                        inner join menu_item_accesos a on ( m.codigo_menu = a.codigo_menu )
                where
                        a.codigo_cargo = :p_codigo_cargo
                        and a.acceso = '1'
                order by
                        1
                ";
            $sentencia = $this->dblink->prepare($sql);
            $sentencia->bindParam(":p_codigo_cargo", $codigoCargo);
            $sentencia->execute();
            $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (Exception $exc) {
            throw $exc;
        }
    }

    public function obtenerOpcionesMenuItem($codigoCargo, $codigoMenu) {
        try {
            $sql = "
                    select
                            m.nombre,
                            m.archivo
                    from
                            menu_item m
                            inner join menu_item_accesos a 
                            on 
                            ( 
                                    m.codigo_menu = a.codigo_menu and 
                                    m.codigo_menu_item = a.codigo_menu_item 
                            )

                    where
                            a.codigo_cargo = :p_codigo_cargo
                            and a.codigo_menu = :p_codigo_menu
                            and a.acceso = '1'
                    order by
                            a.codigo_menu_item
                ";
            
//            $sentencia = $this->dbLink->prepare($sql);
            $sentencia = $this->dblink->prepare($sql);
            $sentencia->bindParam(":p_codigo_cargo", $codigoCargo);
            $sentencia->bindParam(":p_codigo_menu", $codigoMenu);
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