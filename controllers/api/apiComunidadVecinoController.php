<?php
// controllers/api/apiComunidadVecinoController.php
// Entre Vecinos - API de solo lectura para publicaciones visibles del vecino.

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/ComunidadVecino.php';

final class apiComunidadVecinoController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function auth(): ?array
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

    private function exigirVecino(): ?array
    {
        $auth = $this->auth();

        if (!$auth || (int)($auth['codigo_usuario'] ?? 0) <= 0) {
            $this->json(401, [
                'ok' => false,
                'error' => 'UNAUTHORIZED',
                'mensaje' => 'Tu sesión ha finalizado. Vuelve a iniciar sesión.',
                'redirect' => rtrim(BASE_URL, '/') . '/login',
            ]);
            return null;
        }

        $rol = strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
        if ($rol !== 'vecino') {
            $this->json(403, [
                'ok' => false,
                'error' => 'FORBIDDEN',
                'mensaje' => 'Esta consulta está disponible únicamente para vecinos.',
            ]);
            return null;
        }

        return $auth;
    }

    public function listar(): void
    {
        $auth = $this->exigirVecino();
        if (!$auth) {
            return;
        }

        try {
            $modelo = new ComunidadVecino();

            $resultado = $modelo->listarPublicaciones($auth, [
                'tipo' => $_GET['tipo'] ?? 'all',
                'q' => $_GET['q'] ?? '',
                'page' => $_GET['page'] ?? 1,
                'size' => $_GET['size'] ?? 9,
            ]);

            $this->json(200, ['ok' => true] + $resultado);
        } catch (Throwable $e) {
            $this->error($e, 'listar');
        }
    }

    public function detalle(string|int $codigoPublicacion): void
    {
        $auth = $this->exigirVecino();
        if (!$auth) {
            return;
        }

        try {
            $id = (int)$codigoPublicacion;
            if ($id <= 0) {
                throw new InvalidArgumentException('Identificador de publicación inválido.');
            }

            $modelo = new ComunidadVecino();
            $item = $modelo->obtenerPublicacion($auth, $id);

            if (!$item) {
                $this->json(404, [
                    'ok' => false,
                    'mensaje' => 'Esta publicación ya no se encuentra visible en tu comunidad.',
                ]);
                return;
            }

            $this->json(200, ['ok' => true, 'item' => $item]);
        } catch (Throwable $e) {
            $this->error($e, 'detalle');
        }
    }

    private function error(Throwable $e, string $metodo): void
    {
        if ($e instanceof InvalidArgumentException) {
            $this->json(422, ['ok' => false, 'mensaje' => $e->getMessage()]);
            return;
        }

        if ($e instanceof DomainException) {
            $this->json(409, ['ok' => false, 'mensaje' => $e->getMessage()]);
            return;
        }

        if ($e instanceof RuntimeException && str_contains(strtolower($e->getMessage()), 'permis')) {
            $this->json(403, ['ok' => false, 'mensaje' => $e->getMessage()]);
            return;
        }

        error_log('[EV][apiComunidadVecinoController::' . $metodo . '] ' . $e->getMessage());
        $this->json(500, [
            'ok' => false,
            'mensaje' => 'No se pudieron cargar las novedades de tu comunidad.',
        ]);
    }
}
