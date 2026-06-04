<?php
// controllers/comunidadVecinoController.php
// Entre Vecinos - Vista de novedades oficiales para el vecino.

declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../models/SesionJWT.php';
require_once __DIR__ . '/../models/ComunidadVecino.php';

final class comunidadVecinoController
{
    private function usuarioAuth(): ?array
    {
        $auth = $GLOBALS['EV_AUTH'] ?? null;

        if (is_array($auth) && (int)($auth['codigo_usuario'] ?? 0) > 0) {
            return $auth;
        }

        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token || trim((string)$token) === '') {
            return null;
        }

        $usuario = SesionJWT::verificarToken((string)$token);
        return is_array($usuario) ? $usuario : null;
    }

    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function index(): void
    {
        $usuario = $this->usuarioAuth();

        if (!$usuario) {
            $this->json(401, [
                'ok' => false,
                'error' => 'UNAUTHORIZED',
                'mensaje' => 'Tu sesión ha finalizado. Vuelve a iniciar sesión.',
                'redirect' => rtrim(BASE_URL, '/') . '/login',
            ]);
            return;
        }

        $rol = strtolower(trim((string)($usuario['rol'] ?? $usuario['nombre_rol'] ?? '')));
        if ($rol !== 'vecino') {
            http_response_code(403);
            require __DIR__ . '/../views/comunidadAccesoDenegadoView.php';
            return;
        }

        try {
            $modelo = new ComunidadVecino();
            $comunidadVecino = $modelo->obtenerComunidadActual($usuario);
        } catch (DomainException $e) {
            $comunidadVecino = [
                'tipo_conjunto' => '',
                'codigo_comunidad' => 0,
                'nombre_comunidad' => 'Comunidad no asignada',
                'etiqueta_tipo' => 'Comunidad',
            ];
        }

        require __DIR__ . '/../views/comunidadVecinoView.php';
    }
}
