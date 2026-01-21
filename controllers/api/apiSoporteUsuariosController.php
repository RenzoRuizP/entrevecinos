<?php
// controllers/api/apiSoporteUsuariosController.php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SoporteUsuarios.php';

final class apiSoporteUsuariosController
{
    private function rolActual(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_rol'] ?? 0);
    }

    private function codigoSoporte(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function puedeAccederSoporte(): bool
    {
        // Admin=1 (EV_ADMIN_ROLE_ID) y Soporte=3
        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        $rol = $this->rolActual();
        return ($rol === $adminId || $rol === $soporteId);
    }

    /**
     * Normaliza el filtro estado para que acepte:
     * - "0|1|2" (tu UI actual)
     * - "inactivo|revision|habilitado|todos" (API semántica)
     */
    private function normalizarEstado(?string $estadoRaw): string
    {
        $s = strtolower(trim((string)$estadoRaw));

        // Si llega numérico desde el front:
        if ($s === '0') return 'inactivo';
        if ($s === '1') return 'revision';
        if ($s === '2') return 'habilitado';

        // Aceptar variantes comunes:
        if (in_array($s, ['en revision', 'revision', 'revisión', 'en revisión'], true)) return 'revision';
        if (in_array($s, ['habilitado', 'habilitados'], true)) return 'habilitado';
        if (in_array($s, ['inactivo', 'inactivos'], true)) return 'inactivo';
        if ($s === 'todos' || $s === 'all') return 'todos';

        // Fallback seguro:
        return 'revision';
    }

    private function json(int $code, array $payload): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    // GET /api/soporte/usuarios?estado=0|1|2|todos OR estado=revision|habilitado|inactivo|todos
    // &q=&page=1&limit=10
    public function listar(): void
    {
        if (!$this->puedeAccederSoporte()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $estado = $this->normalizarEstado($_GET['estado'] ?? null);
        $q      = trim((string)($_GET['q'] ?? ''));
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = (int)($_GET['limit'] ?? 10);
        $limit  = ($limit <= 0) ? 10 : min($limit, 100);

        try {
            $m = new SoporteUsuarios();

            $res = $m->listar([
                'estado' => $estado, // <-- ahora siempre llega semántico al modelo
                'q'      => $q,
                'page'   => $page,
                'limit'  => $limit,
            ]);

            $this->json(200, [
                'ok'   => true,
                'data' => $res
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteUsuariosController::listar] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'Error interno del servidor.']);
        }
    }

    // POST /api/soporte/usuarios/{id}/estado
    // body: { "estado": 0|1|2 }
    public function actualizarEstado(int $codigoUsuario): void
    {
        if (!$this->puedeAccederSoporte()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $raw = file_get_contents('php://input');
        $in  = json_decode($raw ?: '[]', true);
        if (!is_array($in)) $in = [];

        $estadoNuevo = isset($in['estado']) ? (int)$in['estado'] : -1;

        // 0=inactivo, 1=en revisión, 2=habilitado
        if (!in_array($estadoNuevo, [0, 1, 2], true)) {
            $this->json(400, ['ok' => false, 'mensaje' => 'Estado inválido.']);
            return;
        }

        try {
            $m = new SoporteUsuarios();

            $ok = $m->actualizarEstadoUsuario([
                'codigo_usuario' => $codigoUsuario,
                'estado'         => $estadoNuevo,
                'codigo_soporte' => $this->codigoSoporte(),
            ]);

            if (!$ok) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Usuario no encontrado o sin cambios.']);
                return;
            }

            $this->json(200, ['ok' => true, 'mensaje' => 'Estado actualizado.']);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteUsuariosController::actualizarEstado] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'Error interno del servidor.']);
        }
    }
}
