<?php
declare(strict_types=1);

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/ConfiguracionPlataforma.php';

class apiConfiguracionPlataformaController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function admin(): array
    {
        $auth = $GLOBALS['EV_AUTH'] ?? null;
        if (!is_array($auth) || empty($auth['codigo_usuario'])) {
            $token = $_COOKIE['auth_token'] ?? null;
            $auth = $token ? SesionJWT::verificarToken((string)$token) : null;
        }

        $rol = strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
        if (!is_array($auth) || (int)($auth['codigo_usuario'] ?? 0) <= 0) {
            $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Tu sesión no es válida.']);
            exit;
        }
        if ($rol !== 'admin') {
            $this->json(403, ['ok' => false, 'error' => 'FORBIDDEN', 'mensaje' => 'Solo el administrador general puede modificar esta configuración.']);
            exit;
        }

        return $auth;
    }

    private function exigirCsrf(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $esperado = (string)($_SESSION['ev_config_csrf'] ?? '');
        $recibido = trim((string)($_SERVER['HTTP_X_EV_CSRF'] ?? ''));
        if ($esperado === '' || $recibido === '' || !hash_equals($esperado, $recibido)) {
            $this->json(419, [
                'ok' => false,
                'error' => 'CSRF_INVALIDO',
                'mensaje' => 'La sesión de configuración venció. Vuelve a abrir el módulo e inténtalo nuevamente.',
            ]);
            exit;
        }
    }

    private function input(): array
    {
        if (!empty($_POST) && is_array($_POST)) {
            return $_POST;
        }
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '[]', true);
        return is_array($data) ? $data : [];
    }

    public function obtener(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        $this->admin();

        try {
            $tipo = (string)($_GET['tipo_alcance'] ?? 'global');
            $codigo = (int)($_GET['codigo_alcance'] ?? 0);
            $model = new ConfiguracionPlataforma();

            $this->json(200, [
                'ok' => true,
                'data' => $model->listarCatalogoAdmin($tipo, $codigo),
                'alcance_seleccionado' => $model->obtenerComunidadPorAlcance($tipo, $codigo),
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiConfiguracionPlataformaController][obtener] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'No se pudo cargar la configuración de la plataforma. Verifica que el script SQL haya sido ejecutado.',
            ]);
        }
    }


    public function buscarAlcances(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        $this->admin();

        try {
            $termino = trim((string)($_GET['q'] ?? ''));
            $limite = (int)($_GET['limit'] ?? 20);
            $resultados = (new ConfiguracionPlataforma())->buscarComunidades($termino, $limite);

            $this->json(200, [
                'ok' => true,
                'resultados' => $resultados,
                'total_mostrado' => count($resultados),
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiConfiguracionPlataformaController][buscarAlcances] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'No se pudieron buscar condominios o urbanizaciones.',
            ]);
        }
    }

    public function guardarFuncionalidad(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        $auth = $this->admin();
        $this->exigirCsrf();
        try {
            $resultado = (new ConfiguracionPlataforma())->guardarFuncionalidad($this->input(), (int)$auth['codigo_usuario']);
            $this->json(($resultado['ok'] ?? false) ? 200 : 400, $resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiConfiguracionPlataformaController][guardarFuncionalidad] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo guardar la funcionalidad.']);
        }
    }

    public function guardarMonetizacion(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        $auth = $this->admin();
        $this->exigirCsrf();
        try {
            $resultado = (new ConfiguracionPlataforma())->guardarMonetizacion($this->input(), (int)$auth['codigo_usuario']);
            $this->json(($resultado['ok'] ?? false) ? 200 : 400, $resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiConfiguracionPlataformaController][guardarMonetizacion] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo guardar la regla de monetización.']);
        }
    }

    public function aplicarPiloto(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        $auth = $this->admin();
        $this->exigirCsrf();
        $input = $this->input();

        try {
            $resultado = (new ConfiguracionPlataforma())->aplicarPerfilPiloto(
                (string)($input['tipo_alcance'] ?? 'global'),
                (int)($input['codigo_alcance'] ?? 0),
                (int)$auth['codigo_usuario'],
                $input
            );
            $this->json(($resultado['ok'] ?? false) ? 200 : 400, $resultado);
        } catch (Throwable $e) {
            error_log('[EV][apiConfiguracionPlataformaController][aplicarPiloto] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'error' => 'SERVER_ERROR', 'mensaje' => 'No se pudo aplicar el perfil del piloto.']);
        }
    }
}
