<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../Config/JwtConfig.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class SesionJWT extends Conexion
{
    private string $email = '';
    private string $clave = '';

    public function setEmail(string $email): void
    {
        $this->email = trim($email);
    }

    public function setClave(string $clave): void
    {
        $this->clave = $clave;
    }

    private function usuarioEstaObservado(int $codigoUsuario): bool
    {
        try {
            $st = $this->dblink->prepare(""
                . "SELECT 1\n"
                . "FROM usuario_revision\n"
                . "WHERE codigo_usuario = :id\n"
                . "  AND estado_revision = 3\n"
                . "LIMIT 1"
            );
            $st->execute([':id' => $codigoUsuario]);

            return (bool)$st->fetchColumn();
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][usuarioEstaObservado] ' . $e->getMessage());
            return false;
        }
    }

    private function forzarEstadoRevision(int $codigoUsuario): void
    {
        try {
            $st = $this->dblink->prepare(""
                . "UPDATE usuario\n"
                . "SET estado = 1\n"
                . "WHERE codigo_usuario = :id"
            );
            $st->execute([':id' => $codigoUsuario]);
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][forzarEstadoRevision] ' . $e->getMessage());
        }
    }

    private function obtenerNombreRol(int $codigoUsuario): string
    {
        $sql = "
            SELECT r.nombre AS nombre_rol
            FROM usuario u
            INNER JOIN rol r
                ON u.codigo_rol = r.codigo_rol
            WHERE u.codigo_usuario = :codigo_usuario
              AND r.estado = 1
            LIMIT 1
        ";

        $stmt = $this->dblink->prepare($sql);
        $stmt->execute([':codigo_usuario' => $codigoUsuario]);

        $rol = $stmt->fetchColumn();

        return is_string($rol) ? trim($rol) : '';
    }

    private function comunidadVacia(): array
    {
        return [
            'tipo_conjunto'         => null,
            'codigo_condominio'     => null,
            'codigo_urbanizacion'   => null,
            'conjunto_nombre'       => null,
            'direccion_residencia'  => null,
            'condominio_nombre'     => null,
            'urbanizacion_nombre'   => null,
            'torre_nombre'          => null,
            'departamento_numero'   => null,
        ];
    }

    private function obtenerResidenciaVigente(int $codigoUsuario): array
    {
        $base = $this->comunidadVacia();

        try {
            $sql = "
                SELECT
                    ur.tipo_conjunto,
                    ur.codigo_condominio,
                    ur.codigo_urbanizacion,
                    ur.direccion,
                    c.nombre_condominio,
                    ub.nombre_urbanizacion
                FROM usuario_residencia ur
                LEFT JOIN condominio c
                    ON c.codigo_condominio = ur.codigo_condominio
                LEFT JOIN urbanizacion ub
                    ON ub.codigo_urbanizacion = ur.codigo_urbanizacion
                WHERE ur.codigo_usuario = :codigo_usuario
                ORDER BY ur.codigo_usuario_residencia DESC
                LIMIT 1
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute([':codigo_usuario' => $codigoUsuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($row)) {
                return $this->obtenerResidenciaLegacy($codigoUsuario);
            }

            $tipo = strtolower(trim((string)($row['tipo_conjunto'] ?? '')));

            if ($tipo === 'urbanizacion' && !empty($row['codigo_urbanizacion'])) {
                $base['tipo_conjunto'] = 'urbanizacion';
                $base['codigo_urbanizacion'] = (int)$row['codigo_urbanizacion'];
                $base['urbanizacion_nombre'] = $row['nombre_urbanizacion'] ?? null;
                $base['conjunto_nombre'] = $row['nombre_urbanizacion'] ?? null;
                $base['direccion_residencia'] = $row['direccion'] ?? null;
                return $base;
            }

            if ($tipo === 'condominio' && !empty($row['codigo_condominio'])) {
                $base['tipo_conjunto'] = 'condominio';
                $base['codigo_condominio'] = (int)$row['codigo_condominio'];
                $base['condominio_nombre'] = $row['nombre_condominio'] ?? null;
                $base['conjunto_nombre'] = $row['nombre_condominio'] ?? null;
                $base['direccion_residencia'] = $row['direccion'] ?? null;
                return $base;
            }

            return $this->obtenerResidenciaLegacy($codigoUsuario);
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][obtenerResidenciaVigente] ' . $e->getMessage());
            return $this->obtenerResidenciaLegacy($codigoUsuario);
        }
    }

    private function obtenerComunidadAdministrada(int $codigoUsuario): array
    {
        $base = $this->comunidadVacia();

        try {
            $sql = "
                SELECT
                    ac.tipo_conjunto,
                    ac.codigo_condominio,
                    ac.codigo_urbanizacion,
                    c.nombre_condominio,
                    ub.nombre_urbanizacion
                FROM administrador_comunidad ac
                LEFT JOIN condominio c
                    ON c.codigo_condominio = ac.codigo_condominio
                LEFT JOIN urbanizacion ub
                    ON ub.codigo_urbanizacion = ac.codigo_urbanizacion
                WHERE ac.codigo_usuario = :codigo_usuario
                  AND ac.estado = 1
                ORDER BY ac.codigo_administrador_comunidad DESC
                LIMIT 1
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute([':codigo_usuario' => $codigoUsuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($row)) {
                return $base;
            }

            $tipo = strtolower(trim((string)($row['tipo_conjunto'] ?? '')));

            if ($tipo === 'urbanizacion' && !empty($row['codigo_urbanizacion'])) {
                $base['tipo_conjunto'] = 'urbanizacion';
                $base['codigo_urbanizacion'] = (int)$row['codigo_urbanizacion'];
                $base['urbanizacion_nombre'] = $row['nombre_urbanizacion'] ?? null;
                $base['conjunto_nombre'] = $row['nombre_urbanizacion'] ?? null;
                return $base;
            }

            if ($tipo === 'condominio' && !empty($row['codigo_condominio'])) {
                $base['tipo_conjunto'] = 'condominio';
                $base['codigo_condominio'] = (int)$row['codigo_condominio'];
                $base['condominio_nombre'] = $row['nombre_condominio'] ?? null;
                $base['conjunto_nombre'] = $row['nombre_condominio'] ?? null;
                return $base;
            }

            return $base;
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][obtenerComunidadAdministrada] ' . $e->getMessage());
            return $base;
        }
    }

    private function obtenerResidenciaLegacy(int $codigoUsuario): array
    {
        $base = $this->comunidadVacia();

        $sql = "
            SELECT
                c.codigo_condominio,
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
            WHERE ud.codigo_usuario = :codigo_usuario
              AND (ud.fecha_fin IS NULL OR ud.fecha_fin >= CURRENT_DATE())
            ORDER BY ud.fecha_inicio DESC
            LIMIT 1
        ";

        try {
            $stmt = $this->dblink->prepare($sql);
            $stmt->execute([':codigo_usuario' => $codigoUsuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($row)) {
                return $base;
            }

            $base['tipo_conjunto'] = 'condominio';
            $base['codigo_condominio'] = isset($row['codigo_condominio'])
                ? (int)$row['codigo_condominio']
                : null;
            $base['conjunto_nombre'] = $row['nombre_condominio'] ?? null;
            $base['condominio_nombre'] = $row['nombre_condominio'] ?? null;
            $base['torre_nombre'] = $row['nombre_torre'] ?? null;
            $base['departamento_numero'] = $row['numero_departamento'] ?? null;

            return $base;
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][obtenerResidenciaLegacy] ' . $e->getMessage());
            return $base;
        }
    }

    public function iniciarSesionJWT(): array
    {
        try {
            $sql = "SELECT * FROM usuario WHERE email = :email LIMIT 1";
            $stmt = $this->dblink->prepare($sql);
            $stmt->execute([':email' => $this->email]);

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['status' => 'NE'];
            }

            if (!password_verify($this->clave, (string)$usuario['clave'])) {
                return ['status' => 'CI'];
            }

            $codigoUsuario = (int)($usuario['codigo_usuario'] ?? 0);
            $estadoUsuario = (int)($usuario['estado'] ?? 0);

            if ($estadoUsuario === 0) {
                $observado = ($codigoUsuario > 0)
                    ? $this->usuarioEstaObservado($codigoUsuario)
                    : false;

                if ($observado) {
                    $this->forzarEstadoRevision($codigoUsuario);
                    $estadoUsuario = 1;
                } else {
                    return ['status' => 'IN'];
                }
            }

            $rolNombre = $this->obtenerNombreRol($codigoUsuario);

            if ($rolNombre === '') {
                return ['status' => 'ER'];
            }

            $comunidad = ($rolNombre === 'administrador_comunidad')
                ? $this->obtenerComunidadAdministrada($codigoUsuario)
                : $this->obtenerResidenciaVigente($codigoUsuario);

            $datosToken = [
                'codigo_usuario'        => $codigoUsuario,
                'nombre'                => (string)($usuario['nombre'] ?? ''),
                'email'                 => (string)($usuario['email'] ?? ''),
                'foto_perfil'           => (string)($usuario['foto_perfil'] ?? ''),
                'codigo_rol'            => (int)($usuario['codigo_rol'] ?? 0),
                'rol'                   => $rolNombre,
                'nombre_rol'            => $rolNombre,

                'tipo_conjunto'         => $comunidad['tipo_conjunto'],
                'conjunto_tipo'         => $comunidad['tipo_conjunto'],
                'codigo_condominio'     => $comunidad['codigo_condominio'],
                'codigo_urbanizacion'   => $comunidad['codigo_urbanizacion'],
                'conjunto_nombre'       => $comunidad['conjunto_nombre'],
                'nombre_conjunto'       => $comunidad['conjunto_nombre'],
                'direccion_residencia'  => $comunidad['direccion_residencia'],

                'condominio_nombre'     => $comunidad['condominio_nombre'],
                'urbanizacion_nombre'   => $comunidad['urbanizacion_nombre'],
                'torre_nombre'          => $comunidad['torre_nombre'],
                'departamento_numero'   => $comunidad['departamento_numero'],
            ];

            $token = JwtConfig::generarToken($datosToken);

            return [
                'status' => 'SI',
                'token'  => $token,
                'rol'    => $rolNombre,
            ];
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][iniciarSesionJWT] ' . $e->getMessage());
            return ['status' => 'ER'];
        }
    }

    public static function verificarTokenDetallado(?string $token): array
    {
        if (!$token || trim($token) === '') {
            return ['ok' => false, 'error' => 'TOKEN_AUSENTE', 'data' => null];
        }

        $claveSecreta = (string)ev_env('JWT_SECRET_KEY', '');

        if ($claveSecreta === '') {
            return ['ok' => false, 'error' => 'JWT_SECRET_NO_CONFIGURADO', 'data' => null];
        }

        try {
            $decoded = JWT::decode($token, new Key($claveSecreta, 'HS256'));

            $data = isset($decoded->data)
                ? json_decode(json_encode($decoded->data, JSON_UNESCAPED_UNICODE), true)
                : [];

            return ['ok' => true, 'error' => null, 'data' => is_array($data) ? $data : []];
        } catch (ExpiredException $e) {
            return ['ok' => false, 'error' => 'TOKEN_EXPIRADO', 'data' => null];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => 'TOKEN_INVALIDO', 'data' => null];
        }
    }

    public static function verificarToken(string $token): ?array
    {
        $r = self::verificarTokenDetallado($token);
        return $r['ok'] ? $r['data'] : null;
    }

    public static function cookieSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    }

    public static function cookiePath(): string
    {
        $p = defined('BASE_URL') ? (string)BASE_URL : '/';
        $p = trim($p);

        if ($p === '') {
            $p = '/';
        }

        if ($p[0] !== '/') {
            $p = '/' . $p;
        }

        $p = rtrim($p, '/');

        return ($p === '') ? '/' : ($p . '/');
    }

    public static function eliminarToken(): bool
    {
        if (!isset($_COOKIE['auth_token'])) {
            return false;
        }

        $params = [
            'expires'  => time() - 3600,
            'path'     => self::cookiePath(),
            'secure'   => self::cookieSecure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        setcookie('auth_token', '', $params);
        unset($_COOKIE['auth_token']);

        return true;
    }

    public function obtenerOpcionesMenu(string $nombreRol): array
    {
        try {
            $sql = "
                SELECT DISTINCT
                    m.codigo_menu,
                    m.nombre,
                    m.icono,
                    m.orden
                FROM rol r
                INNER JOIN rol_menu_item rmi
                    ON rmi.codigo_rol = r.codigo_rol
                INNER JOIN menu_item mi
                    ON mi.codigo_menu_item = rmi.codigo_menu_item
                INNER JOIN menu m
                    ON m.codigo_menu = mi.codigo_menu
                WHERE r.nombre = :p_nombre_rol
                  AND r.estado = 1
                  AND m.estado = 1
                  AND mi.estado = 1
                ORDER BY m.orden ASC
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute([':p_nombre_rol' => $nombreRol]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][obtenerOpcionesMenu] ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerOpcionesMenuItem(string $nombreRol, int $codigoMenu): array
    {
        try {
            $sql = "
                SELECT
                    mi.codigo_menu_item,
                    mi.nombre,
                    mi.icono,
                    mi.ruta,
                    mi.orden
                FROM rol r
                INNER JOIN rol_menu_item rmi
                    ON rmi.codigo_rol = r.codigo_rol
                INNER JOIN menu_item mi
                    ON mi.codigo_menu_item = rmi.codigo_menu_item
                INNER JOIN menu m
                    ON m.codigo_menu = mi.codigo_menu
                WHERE r.nombre = :p_nombre_rol
                  AND m.codigo_menu = :p_codigo_menu
                  AND r.estado = 1
                  AND m.estado = 1
                  AND mi.estado = 1
                ORDER BY mi.orden ASC
            ";

            $stmt = $this->dblink->prepare($sql);
            $stmt->execute([
                ':p_nombre_rol'  => $nombreRol,
                ':p_codigo_menu' => $codigoMenu,
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[EV][SesionJWT][obtenerOpcionesMenuItem] ' . $e->getMessage());
            return [];
        }
    }
}
