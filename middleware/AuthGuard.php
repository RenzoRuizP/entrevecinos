<?php
// middlewares/AuthGuard.php
// Guard único para páginas HTML (vecino y administrador)

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';

final class AuthGuard
{
    /**
     * Valida sesión para páginas HTML.
     * - Si es parcial/AJAX: responde 401 JSON.
     * - Si es full page: redirige a views/login.php con ?error=
     * Retorna el array de datos del token (decoded->data).
     */
    public static function check(bool $isPartial = false): array
    {
        $token = $_COOKIE['auth_token'] ?? null;

        // Si existe método detallado, úsalo (recomendado)
        if (method_exists('SesionJWT', 'verificarTokenDetallado')) {
            $r = SesionJWT::verificarTokenDetallado($token);

            if (!$r['ok']) {
                self::deny($r['error'] ?? 'TOKEN_INVALIDO', $isPartial);
            }

            return is_array($r['data'] ?? null) ? $r['data'] : [];
        }

        // Fallback si aún no existe verificarTokenDetallado()
        if (!$token || trim((string)$token) === '') {
            self::deny('TOKEN_AUSENTE', $isPartial);
        }

        $data = SesionJWT::verificarToken((string)$token);
        if (!$data || !is_array($data)) {
            self::deny('TOKEN_INVALIDO', $isPartial);
        }

        return $data;
    }

    /**
     * Restringe por rol si lo necesitas.
     */
    public static function requireRole(string $rolEsperado, bool $isPartial = false): void
    {
        $u = self::check($isPartial);
        $rol = $u['rol'] ?? null;

        if ($rol !== $rolEsperado) {
            if ($isPartial) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'error' => 'FORBIDDEN', 'motivo' => 'rol_no_autorizado']);
                exit;
            }
            header('Location: ' . rtrim(BASE_URL, '/') . '/MenuPrincipal');
            exit;
        }
    }

    private static function deny(string $err, bool $isPartial): void
    {
        $motivo = match ($err) {
            'TOKEN_EXPIRADO' => 'token_expirado',
            'TOKEN_AUSENTE'  => 'sin_token',
            'TOKEN_INVALIDO' => 'token_invalido',
            default          => 'token_invalido',
        };

        if ($isPartial) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED', 'motivo' => $motivo]);
            exit;
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/views/login.php?error=' . urlencode($motivo));
        exit;
    }
}
