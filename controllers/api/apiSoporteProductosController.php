<?php
// controllers/api/apiSoporteProductosController.php
declare(strict_types=1);

require_once __DIR__ . '/../../models/ProductoSoporte.php';

class apiSoporteProductosController
{
    // =========================
    // Core helpers
    // =========================
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function auth(): array
    {
        $u = $GLOBALS['EV_AUTH'] ?? [];
        return is_array($u) ? $u : [];
    }

    private function requireAuth(): array
    {
        $u = $this->auth();
        $id = (int)($u['codigo_usuario'] ?? 0);
        if ($id <= 0) {
            $this->json(401, [
                'ok' => false,
                'error' => 'UNAUTHORIZED',
                'motivo' => 'sin token'
            ]);
        }
        return $u;
    }

    private function puedeAtenderPublicaciones(array $u): bool
    {
        $rol = (int)($u['codigo_rol'] ?? 0);

        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        return ($rol === $adminId || $rol === $soporteId);
    }

    private function requireSoporte(array $u): void
    {
        if (!$this->puedeAtenderPublicaciones($u)) {
            $this->json(403, [
                'ok' => false,
                'error' => 'FORBIDDEN',
                'motivo' => 'sin permiso',
                'mensaje' => 'No tienes permisos para atender publicaciones.'
            ]);
        }
    }

    private function getString(string $key, string $default = ''): string
    {
        $v = $_GET[$key] ?? $default;
        return trim((string)$v);
    }

    private function getInt(string $key, int $default = 0): int
    {
        $v = $_GET[$key] ?? null;
        if ($v === null || $v === '') return $default;
        return (int)$v;
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '{}', true);
        return is_array($data) ? $data : [];
    }

    private function mapEstadoToVisible(string $estado): ?int
    {
        // mapping según tu ProductoSoporte
        // borrador=0, pendiente=1, aprobada=2, rechazada=3
        $estado = strtolower(trim($estado));

        return match ($estado) {
            'borrador', 'borradores' => 0,
            'pendiente', 'pendientes' => 1,
            'aprobada', 'aprobadas' => 2,
            'rechazada', 'rechazadas' => 3,
            default => null
        };
    }

    // =========================
    // Endpoints
    // =========================

    /**
     * GET /api/soporte/productos?estado=pendiente|aprobada|rechazada|borrador|todas&q=&page=1&size=10
     */
    public function listar(): void
    {
        try {
            $u = $this->requireAuth();
            $this->requireSoporte($u);

            $estado = strtolower($this->getString('estado', 'pendiente'));
            $q      = $this->getString('q', '');
            $page   = max(1, $this->getInt('page', 1));
            $size   = max(1, min(50, $this->getInt('size', 10)));

            // Compat: si en front todavía envían "search", lo aceptamos
            if ($q === '') {
                $q = $this->getString('search', '');
            }

            // Normaliza estado permitido
            $permitidos = ['borrador', 'pendiente', 'aprobada', 'rechazada', 'todas'];
            if (!in_array($estado, $permitidos, true)) {
                $estado = 'pendiente';
            }

            $m = new ProductoSoporte();
            $r = $m->listarSoporte([
                'estado' => $estado,
                'q'      => $q,
                'page'   => $page,
                'size'   => $size,
            ]);

            $this->json(200, [
                'ok'     => true,
                'total'  => (int)($r['total'] ?? 0),
                'page'   => (int)($r['page'] ?? $page),
                'size'   => (int)($r['size'] ?? $size),
                'counts' => $r['counts'] ?? [
                    'borradores' => 0,
                    'pendientes' => 0,
                    'aprobadas'  => 0,
                    'rechazadas' => 0,
                ],
                'items'  => $r['items'] ?? [],
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteProductosController][listar] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'Error interno al listar productos.'
            ]);
        }
    }

    /**
     * GET /api/soporte/productos/{id}
     */
    public function detalle(int $id): void
    {
        try {
            $u = $this->requireAuth();
            $this->requireSoporte($u);

            $id = (int)$id;
            if ($id <= 0) {
                $this->json(400, [
                    'ok' => false,
                    'error' => 'BAD_REQUEST',
                    'mensaje' => 'ID inválido.'
                ]);
            }

            $m = new ProductoSoporte();
            $row = $m->obtenerDetalle($id);

            if (!$row) {
                $this->json(404, [
                    'ok' => false,
                    'error' => 'NOT_FOUND',
                    'mensaje' => 'No existe el producto.'
                ]);
            }

            $this->json(200, [
                'ok' => true,
                'item' => $row
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteProductosController][detalle] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'Error interno al obtener detalle.'
            ]);
        }
    }

    /**
     * POST /api/soporte/productos/{id}/estado
     * Body JSON: { "estado": "aprobada|rechazada|pendiente|borrador" }
     */
    public function actualizarEstado(int $id): void
    {
        try {
            $u = $this->requireAuth();
            $this->requireSoporte($u);

            $id = (int)$id;
            if ($id <= 0) {
                $this->json(400, [
                    'ok' => false,
                    'error' => 'BAD_REQUEST',
                    'mensaje' => 'ID inválido.'
                ]);
            }

            $body = $this->readJsonBody();
            $estado = (string)($body['estado'] ?? '');

            $nuevoVisible = $this->mapEstadoToVisible($estado);
            if ($nuevoVisible === null) {
                $this->json(400, [
                    'ok' => false,
                    'error' => 'BAD_REQUEST',
                    'mensaje' => 'Estado inválido.',
                    'permitidos' => ['borrador', 'pendiente', 'aprobada', 'rechazada']
                ]);
            }

            $m = new ProductoSoporte();
            $ok = $m->actualizarEstadoSoporte($id, $nuevoVisible);

            if (!$ok) {
                $this->json(500, [
                    'ok' => false,
                    'error' => 'UPDATE_FAILED',
                    'mensaje' => 'No se pudo actualizar el estado.'
                ]);
            }

            $this->json(200, [
                'ok' => true,
                'mensaje' => 'Estado actualizado correctamente.',
                'codigo_producto' => $id,
                'visible' => $nuevoVisible
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteProductosController][actualizarEstado] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'error' => 'SERVER_ERROR',
                'mensaje' => 'Error interno al actualizar estado.'
            ]);
        }
    }
}
