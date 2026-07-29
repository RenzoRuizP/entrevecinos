<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/ConfiguracionPlataforma.php';

class FuncionalidadGuard
{
    public static function usuarioActual(): array
    {
        $global = $GLOBALS['EV_AUTH'] ?? null;
        if (is_array($global) && !empty($global['codigo_usuario'])) {
            return $global;
        }

        $token = $_COOKIE['auth_token'] ?? null;
        $usuario = $token ? SesionJWT::verificarToken((string)$token) : null;
        return is_array($usuario) ? $usuario : [];
    }

    public static function verificar(string $clave, bool $adminBypass = true): array
    {
        $usuario = self::usuarioActual();
        if (empty($usuario['codigo_usuario'])) {
            return ['ok' => false, 'status' => 401, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Tu sesión no es válida.'];
        }

        $config = new ConfiguracionPlataforma();
        $regla = $config->obtenerFuncionalidad($clave, $usuario, $adminBypass);

        if (!empty($regla['habilitada'])) {
            return ['ok' => true, 'usuario' => $usuario, 'regla' => $regla];
        }

        $mensaje = trim((string)($regla['mensaje'] ?? ''));
        if ($mensaje === '') {
            $mensaje = 'Esta funcionalidad no está disponible durante la fase actual del piloto.';
        }

        return [
            'ok' => false,
            'status' => 403,
            'error' => 'FUNCIONALIDAD_NO_DISPONIBLE',
            'mensaje' => $mensaje,
            'usuario' => $usuario,
            'regla' => $regla,
        ];
    }

    public static function exigirJson(string $clave, bool $adminBypass = true): array
    {
        $resultado = self::verificar($clave, $adminBypass);
        if (!($resultado['ok'] ?? false)) {
            http_response_code((int)($resultado['status'] ?? 403));
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'error' => $resultado['error'] ?? 'FUNCIONALIDAD_NO_DISPONIBLE',
                'mensaje' => $resultado['mensaje'] ?? 'Funcionalidad no disponible.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        return $resultado;
    }

    public static function exigirMonetizacionBooleanaJson(string $clave, bool $adminBypass = false): array
    {
        $usuario = self::usuarioActual();
        if (empty($usuario['codigo_usuario'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Tu sesión no es válida.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($adminBypass && ConfiguracionPlataforma::esAdmin($usuario)) {
            return ['ok' => true, 'usuario' => $usuario, 'regla' => ['valor_booleano' => true, 'origen' => 'bypass_admin']];
        }

        $config = new ConfiguracionPlataforma();
        $regla = $config->obtenerMonetizacion($clave, $usuario);
        if (!empty($regla['valor_booleano'])) {
            return ['ok' => true, 'usuario' => $usuario, 'regla' => $regla];
        }

        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => 'REGLA_MONETIZACION_DESACTIVADA',
            'mensaje' => 'Esta operación está desactivada durante la modalidad actual del piloto.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function exigirMonetizacionBooleanaHtml(string $clave, bool $adminBypass = false): bool
    {
        $usuario = self::usuarioActual();
        if (empty($usuario['codigo_usuario'])) {
            http_response_code(401);
            header('X-Partial-Ok: 1');
            $mensajeFuncionalidad = 'Tu sesión no es válida.';
            require __DIR__ . '/../views/funcionalidadNoDisponibleView.php';
            return false;
        }

        if ($adminBypass && ConfiguracionPlataforma::esAdmin($usuario)) {
            return true;
        }

        $config = new ConfiguracionPlataforma();
        $regla = $config->obtenerMonetizacion($clave, $usuario);
        if (!empty($regla['valor_booleano'])) {
            return true;
        }

        http_response_code(403);
        header('X-Partial-Ok: 1');
        $mensajeFuncionalidad = 'Esta operación está desactivada durante la modalidad actual del piloto.';
        require __DIR__ . '/../views/funcionalidadNoDisponibleView.php';
        return false;
    }

    public static function exigirHtml(string $clave, bool $adminBypass = true): bool
    {
        $resultado = self::verificar($clave, $adminBypass);
        if ($resultado['ok'] ?? false) {
            return true;
        }

        http_response_code((int)($resultado['status'] ?? 403));
        header('X-Partial-Ok: 1');
        $mensajeFuncionalidad = (string)($resultado['mensaje'] ?? 'Funcionalidad no disponible.');
        require __DIR__ . '/../views/funcionalidadNoDisponibleView.php';
        return false;
    }
}
