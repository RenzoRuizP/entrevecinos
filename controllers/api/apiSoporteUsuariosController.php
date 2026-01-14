<?php
// controllers/api/apiSoporteUsuariosController.php
// EV — API Soporte (Admin): Usuarios

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/UsuarioSoporte.php';

class apiSoporteUsuariosController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    private function isAdmin(): bool
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $rol  = (int)($auth['codigo_rol'] ?? 0);
        return $rol === (int)EV_ADMIN_ROLE_ID;
    }

    /**
     * Normaliza la respuesta del modelo al formato esperado por el JS:
     * { ok:true, data:[...], meta:{ total,page,size } }
     */
    private function normalizeListResponse($res, int $page, int $size): array
    {
        // Caso ideal: { ok:true, data:[...], meta:{total} }
        if (is_array($res) && array_key_exists('data', $res) && is_array($res['data'])) {
            $ok = array_key_exists('ok', $res) ? (bool)$res['ok'] : true;

            $meta = [];
            if (isset($res['meta']) && is_array($res['meta'])) $meta = $res['meta'];

            $total = (int)($meta['total'] ?? ($res['total'] ?? count($res['data'])));

            return [
                'ok'   => $ok,
                'data' => $res['data'],
                'meta' => [
                    'total' => $total,
                    'page'  => (int)($meta['page'] ?? $page),
                    'size'  => (int)($meta['size'] ?? $size),
                ],
            ];
        }

        // Caso alterno común: { ok:true, rows:[...], total:123 }
        if (is_array($res) && isset($res['rows']) && is_array($res['rows'])) {
            $total = (int)($res['total'] ?? count($res['rows']));
            return [
                'ok'   => (bool)($res['ok'] ?? true),
                'data' => $res['rows'],
                'meta' => [
                    'total' => $total,
                    'page'  => $page,
                    'size'  => $size,
                ],
            ];
        }

        // Caso array plano: [...]
        if (is_array($res) && array_keys($res) === range(0, count($res) - 1)) {
            return [
                'ok'   => true,
                'data' => $res,
                'meta' => [
                    'total' => count($res),
                    'page'  => $page,
                    'size'  => $size,
                ],
            ];
        }

        // Fallback
        return [
            'ok'   => false,
            'data' => [],
            'meta' => [
                'total' => 0,
                'page'  => $page,
                'size'  => $size,
            ],
            'mensaje' => 'Respuesta inesperada del modelo.',
        ];
    }

    public function listar(): void
    {
        if (!$this->isAdmin()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $size = max(1, min(50, (int)($_GET['size'] ?? 10)));

            $model = new UsuarioSoporte();

            $filtros = [
                'estado' => $_GET['estado'] ?? null,
                'tipo'   => (string)($_GET['tipo'] ?? ''),
                'codigo' => (int)($_GET['codigo'] ?? 0),
                'q'      => (string)($_GET['q'] ?? ''),
                'page'   => $page,
                'size'   => $size,
            ];

            $res = $model->listar($filtros);
            $payload = $this->normalizeListResponse($res, $page, $size);

            // Siempre 200 para listado; el ok indica estado lógico
            $this->json(200, $payload);

        } catch (Throwable $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public function actualizarEstado($codigoUsuario): void
    {
        if (!$this->isAdmin()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $id = (int)$codigoUsuario;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }

        try {
            $bodyRaw = file_get_contents('php://input');
            $body = json_decode($bodyRaw ?: '[]', true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
                $this->json(400, ['ok' => false, 'mensaje' => 'JSON inválido.']);
                return;
            }

            $estado = (int)($body['estado'] ?? -1);

            if (!in_array($estado, [0, 1, 2], true)) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Estado inválido.']);
                return;
            }

            $model = new UsuarioSoporte();
            $ok = $model->actualizarEstado($id, $estado);

            $this->json(200, [
                'ok' => (bool)$ok,
                'mensaje' => $ok ? 'Estado actualizado.' : 'No se pudo actualizar.'
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}
