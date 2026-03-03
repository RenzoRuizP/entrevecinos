<?php
/*
    SesionJWT.php
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

    /**
     * ✅ Detecta si el usuario está OBSERVADO (estado_revision=3)
     */
    private function usuarioEstaObservado(int $codigoUsuario): bool
    {
        try {
            $st = $this->dblink->prepare("
                SELECT 1
                FROM usuario_revision
                WHERE codigo_usuario = :id
                  AND estado_revision = 3
                LIMIT 1
            ");
            $st->execute([':id' => $codigoUsuario]);
            return (bool)$st->fetchColumn();
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][usuarioEstaObservado] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ Si está OBSERVADO y estaba INACTIVO, lo dejamos en REVISIÓN (estado=1)
     * para que pueda iniciar sesión y ver /cuenta-observada.
     */
    private function forzarEstadoRevision(int $codigoUsuario): void
    {
        try {
            $st = $this->dblink->prepare("
                UPDATE usuario
                SET estado = 1
                WHERE codigo_usuario = :id
                LIMIT 1
            ");
            $st->execute([':id' => $codigoUsuario]);
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][forzarEstadoRevision] ' . $e->getMessage());
        }
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

            $codigoUsuario = (int)($usuario['codigo_usuario'] ?? 0);
            $estadoUsuario = (int)($usuario['estado'] ?? 0);

            // =========================================================
            // ✅ CORRECCIÓN DE RAÍZ:
            // Si está INACTIVO (0) pero está OBSERVADO (estado_revision=3),
            // NO bloqueamos login. Le permitimos entrar para ver el mensaje
            // en /cuenta-observada y forzamos estado=1 para consistencia.
            // =========================================================
            if ($estadoUsuario === 0) {
                $observado = ($codigoUsuario > 0) ? $this->usuarioEstaObservado($codigoUsuario) : false;

                if ($observado) {
                    // Permitir login y dejarlo en revisión (estado=1)
                    $this->forzarEstadoRevision($codigoUsuario);
                    $estadoUsuario = 1;
                } else {
                    return ['status' => 'IN']; // Usuario inactivo real (no observado)
                }
            }

            // ✅ 2) Obtener rol (NOMBRE)
            $sqlRoles = "
                SELECT r.nombre AS nombre_rol
                FROM usuario u
                INNER JOIN rol r ON u.codigo_rol = r.codigo_rol
                WHERE u.codigo_usuario = :codigo_usuario
                LIMIT 1
            ";
            $stmtRoles = $this->dblink->prepare($sqlRoles);
            $stmtRoles->bindParam(':codigo_usuario', $codigoUsuario);
            $stmtRoles->execute();
            $rolNombre = $stmtRoles->fetch(PDO::FETCH_COLUMN);

            // ✅ 3) Residencia (legacy: usuario_departamento)
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
            $stmtRes->bindParam(':codigo_usuario', $codigoUsuario);
            $stmtRes->execute();
            $residencia = $stmtRes->fetch(PDO::FETCH_ASSOC);

            $condominioNombre = $residencia['nombre_condominio'] ?? null;
            $torreNombre      = $residencia['nombre_torre'] ?? null;
            $depaNumero       = $residencia['numero_departamento'] ?? null;

            // ✅ 4) Datos del token
            $datosToken = [
                'codigo_usuario'       => $codigoUsuario,
                'nombre'               => (string)($usuario['nombre'] ?? ''),
                'email'                => (string)($usuario['email'] ?? ''),

                // roles para autorización
                'codigo_rol'           => (int)($usuario['codigo_rol'] ?? 0),
                'rol'                  => (string)($rolNombre ?: ''),
                'nombre_rol'           => (string)($rolNombre ?: ''),

                // residencia (si existe)
                'condominio_nombre'    => $condominioNombre,
                'torre_nombre'         => $torreNombre,
                'departamento_numero'  => $depaNumero
            ];

            // 5) Token
            $token = JwtConfig::generarToken($datosToken);

            return [
                'status' => 'SI',
                'token'  => $token,
                'rol'    => $rolNombre
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return ['status' => 'ER']; // Error interno
        }
    }

    // ============================================================
    // VERIFICAR TOKEN (DETALLADO)
    // ============================================================
    public static function verificarTokenDetallado(?string $token): array {
        if (!$token || trim($token) === '') {
            return ['ok' => false, 'error' => 'TOKEN_AUSENTE', 'data' => null];
        }

        $claveSecreta = $_ENV['JWT_SECRET_KEY'];

        try {
            $decoded = JWT::decode($token, new Key($claveSecreta, 'HS256'));

            $data = isset($decoded->data)
                ? json_decode(json_encode($decoded->data), true)
                : [];

            return ['ok' => true, 'error' => null, 'data' => $data];

        } catch (ExpiredException $e) {
            return ['ok' => false, 'error' => 'TOKEN_EXPIRADO', 'data' => null];
        } catch (Exception $e) {
            return ['ok' => false, 'error' => 'TOKEN_INVALIDO', 'data' => null];
        }
    }

    // Wrapper compatible
    public static function verificarToken(string $token) {
        $r = self::verificarTokenDetallado($token);
        return $r['ok'] ? $r['data'] : null;
    }

    // ============================================================
    // COOKIE HELPERS (CONSISTENTE EN LOCAL/PROD)
    // ============================================================
    public static function cookieSecure(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && intval($_SERVER['SERVER_PORT']) === 443);
    }

    public static function cookiePath(): string {
        $p = defined('BASE_URL') ? (string)BASE_URL : '/';
        $p = trim($p);
        if ($p === '') $p = '/';
        if ($p[0] !== '/') $p = '/' . $p;
        $p = rtrim($p, '/');
        return ($p === '') ? '/' : ($p . '/');
    }

    public static function eliminarToken(): bool {
        if (!isset($_COOKIE['auth_token'])) return false;

        $params = [
            'expires'  => time() - 3600,
            'path'     => self::cookiePath(),
            'secure'   => self::cookieSecure(),
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        setcookie('auth_token', '', $params);
        unset($_COOKIE['auth_token']);
        return true;
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
            $sentencia->bindParam(':p_nombre_rol', $nombre_rol);
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
            $sentencia->bindParam(':p_nombreRol', $nombreRol);
            $sentencia->bindParam(':p_codigo_menu', $codigo_menu);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $exc) {
            throw $exc;
        }
    }
}