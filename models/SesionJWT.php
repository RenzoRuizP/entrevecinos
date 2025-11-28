<?php
/*
    imprime un json: echo json_encode($usuario);            
    compara e imprime, si sale false no son iguales: var_dump(password_verify($this->clave, $usuario['clave'])); 
    encripta: $nuevoHash = password_hash('123456', PASSWORD_BCRYPT);
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
            // 1) Validar email
            $sql = "SELECT * FROM usuario WHERE email = :email";
            $stmt = $this->dblink->prepare($sql);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) 
                return ['status' => 'NE']; // No existe
            
            if (!password_verify($this->clave, $usuario['clave'])) 
                return ['status' => 'CI']; // Contraseña incorrecta
            
            if ($usuario['estado'] == '0') 
                return ['status' => 'IN']; // Usuario inactivo

            // 2) Obtener rol
            $sqlRoles = "
                SELECT r.nombre AS nombre_rol
                FROM usuario u  
                INNER JOIN rol r ON u.codigo_rol = r.codigo_rol
                WHERE u.codigo_usuario = :codigo_usuario
            ";
            $stmtRoles = $this->dblink->prepare($sqlRoles);
            $stmtRoles->bindParam(':codigo_usuario', $usuario['codigo_usuario']);
            $stmtRoles->execute();
            $rol = $stmtRoles->fetch(PDO::FETCH_COLUMN);

            // ============================================================
            // 3) Obtener datos de condominio + torre + departamento
            // ============================================================
            $sqlResidencia = "
                SELECT 
                    c.nombre_condominio,
                    t.nombre_torre,
                    d.numero_departamento
                FROM usuario_departamento ud
                INNER JOIN departamento d 
                    ON d.codigo_departamento = ud.codigo_departamento
                INNER JOIN torre t 
                    ON t.codigo_torre = d.codigo_torre
                INNER JOIN condominio c 
                    ON c.codigo_condominio = t.codigo_condominio
                WHERE 
                    ud.codigo_usuario = :codigo_usuario
                    AND (ud.fecha_fin IS NULL OR ud.fecha_fin >= CURRENT_DATE())
                ORDER BY ud.fecha_inicio DESC
                LIMIT 1
            ";

            $stmtRes = $this->dblink->prepare($sqlResidencia);
            $stmtRes->bindParam(':codigo_usuario', $usuario['codigo_usuario']);
            $stmtRes->execute();
            $residencia = $stmtRes->fetch(PDO::FETCH_ASSOC);

            // Sanitizar valores
            $condominioNombre = $residencia['nombre_condominio'] ?? null;
            $torreNombre      = $residencia['nombre_torre'] ?? null;
            $depaNumero       = $residencia['numero_departamento'] ?? null;

            // ============================================================
            // 4) Generar datos del token
            // ============================================================
            $datosToken = [
                'codigo_usuario'       => $usuario['codigo_usuario'],
                'nombre'               => $usuario['nombre'],
                'email'                => $usuario['email'],
                'rol'                  => $rol,

                // Nuevos datos del condominio
                'condominio_nombre'    => $condominioNombre,
                'torre_nombre'         => $torreNombre,
                'departamento_numero'  => $depaNumero
            ];

            // 5) Generar token
            $token = JwtConfig::generarToken($datosToken);

            return [
                'status' => 'SI',
                'token'  => $token,
                'rol'    => $rol
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return ['status' => 'ER']; // Error interno
        }
    }

    // ============================================================
    // VERIFICAR TOKEN
    // ============================================================
    public static function verificarToken(string $token) {
        $claveSecreta = $_ENV['JWT_SECRET_KEY'];

        try {
            $decoded = JWT::decode($token, new Key($claveSecreta, 'HS256'));

            $data = isset($decoded->data)
                ? json_decode(json_encode($decoded->data), true)
                : [];

            return $data;

        } catch (ExpiredException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    // ============================================================
    // MENÚS
    // ============================================================
    public function obtenerOpcionesMenu($nombre_rol) {
        try {
            $sql = "
                SELECT DISTINCT 
                    m.codigo_menu, m.nombre, m.icono
                FROM rol r 
                INNER JOIN menu_item_accesos m_i_a ON r.codigo_rol = m_i_a.codigo_rol 
                INNER JOIN menu_item m_i          ON m_i.codigo_menu_item = m_i_a.codigo_menu_item 
                INNER JOIN menu m                 ON m.codigo_menu = m_i.codigo_menu
                WHERE r.nombre LIKE :p_nombre_rol;
            ";
            $sentencia = $this->dblink->prepare($sql);
            $sentencia->bindParam(":p_nombre_rol", $nombre_rol);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $exc) {
            throw $exc;
        }
    }

    public function obtenerOpcionesMenuItem($nombreRol, $codigo_menu) {
        try {
            $sql = "
                SELECT 
                    m_i.codigo_menu_item, m_i.nombre, m_i.icono, m_i.ruta
                FROM rol r 
                INNER JOIN menu_item_accesos m_i_a ON r.codigo_rol = m_i_a.codigo_rol 
                INNER JOIN menu_item m_i          ON m_i.codigo_menu_item = m_i_a.codigo_menu_item 
                INNER JOIN menu m                 ON m.codigo_menu = m_i.codigo_menu
                WHERE r.nombre LIKE :p_nombreRol 
                  AND m.codigo_menu = :p_codigo_menu;
            ";
            $sentencia = $this->dblink->prepare($sql);
            $sentencia->bindParam(":p_nombreRol", $nombreRol);
            $sentencia->bindParam(":p_codigo_menu", $codigo_menu);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $exc) {
            throw $exc;
        }
    }

    public function eliminarToken() {
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', time() - 3600, '/', '', true, true);
            return true;
        }
        return false;
    }
}
