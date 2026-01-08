<?php
// controllers/api/apiSoporteResidenciasController.php
// EV — Soporte: solicitudes de cambio de residencia

declare(strict_types=1);

require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';

class apiSoporteResidenciasController
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
        return defined('EV_ADMIN_ROLE_ID') && $rol === (int)EV_ADMIN_ROLE_ID;
    }

    /**
     * GET /api/soporte/residencias
     * Query:
     *  - estado: pendiente|observada|aprobada|rechazada|all
     *  - tipo: condominio|urbanizacion|""
     *  - codigo: int
     *  - q: string
     *  - page: int
     *  - size: int
     */
    public function listar(): void
    {
        if (!$this->isAdmin()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        try {
            $model = new UsuarioResidenciaSolicitud();

            $filtros = [
                'estado' => $_GET['estado'] ?? 'pendiente',
                'tipo'   => $_GET['tipo'] ?? '',
                'codigo' => $_GET['codigo'] ?? 0,
                'q'      => $_GET['q'] ?? '',
                'page'   => $_GET['page'] ?? 1,
                'size'   => $_GET['size'] ?? 10,
            ];

            $res = $model->listarSoporte($filtros);
            $this->json(200, $res);
        } catch (\Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/soporte/residencias/{id}/estado
     * Body JSON: { "estado": "aprobada|observada|rechazada", "comentario": "..." }
     */
    public function actualizarEstado($id): void
    {
        if (!$this->isAdmin()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $id = (int)$id;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true) ?: [];

        $estado = strtolower(trim((string)($body['estado'] ?? '')));
        $comentario = trim((string)($body['comentario'] ?? ''));

        if (!in_array($estado, ['aprobada', 'rechazada', 'observada'], true)) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Estado inválido.']);
            return;
        }

        // Reglas mínimas: comentario requerido para observada/rechazada
        if (in_array($estado, ['observada', 'rechazada'], true) && mb_strlen($comentario) < 3) {
            $this->json(422, [
                'ok' => false,
                'mensaje' => 'Comentario obligatorio para Observada/Rechazada.',
            ]);
            return;
        }

        try {
            $model = new UsuarioResidenciaSolicitud();

            // Validar existencia solicitud (evita aprobar/actualizar ids inexistentes)
            $sol = $model->obtenerPorId($id);
            if (!$sol) {
                $this->json(404, ['ok' => false, 'mensaje' => 'Solicitud no encontrada.']);
                return;
            }

            // Aprobada: aplicar cambio + marcar aprobada (transacción vive en el modelo)
            if ($estado === 'aprobada') {
                $ok = $model->aprobarSolicitud($id, $comentario !== '' ? $comentario : null);

                $this->json(200, [
                    'ok' => (bool)$ok,
                    'mensaje' => $ok
                        ? 'Cambio de residencia aprobado y aplicado.'
                        : 'No se pudo aprobar (estado no permitido o solicitud inválida).'
                ]);
                return;
            }

            // Observada/Rechazada: solo cambia estado + comentario
            $ok = $model->actualizarEstado($id, $estado, $comentario);

            $this->json(200, [
                'ok' => (bool)$ok,
                'mensaje' => $ok ? 'Estado actualizado.' : 'No se pudo actualizar.',
            ]);
        } catch (\Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}
