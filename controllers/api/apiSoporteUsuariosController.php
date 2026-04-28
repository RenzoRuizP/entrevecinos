<?php
// controllers/api/apiSoporteUsuariosController.php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/SoporteUsuarios.php';
require_once __DIR__ . '/../../models/Billetera.php';

final class apiSoporteUsuariosController
{
    /* =========================
     * Helpers de sesión / rol
     * ========================= */
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
        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        $rol = $this->rolActual();
        return ($rol === $adminId || $rol === $soporteId);
    }

    private function json(int $code, array $payload): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function normalizarEstado($raw): string
    {
        $v = strtolower(trim((string)$raw));

        return match ($v) {
            '1', 'revision', 'en_revision', 'en revisión' => 'revision',
            '2', 'habilitado', 'habilitados'              => 'habilitado',
            '3', 'observado', 'observados'                => 'observado',
            '0', 'inactivo', 'inactivos'                  => 'inactivo',
            'todos', 'all'                                => 'todos',
            default                                       => 'revision',
        };
    }

    private function normalizarConjunto($raw): string
    {
        $v = strtolower(trim((string)$raw));
        if ($v === '') return '';
        if (str_contains($v, 'cond')) return 'condominio';
        if (str_contains($v, 'urban')) return 'urbanizacion';
        return '';
    }

    /* =========================
     * GET /api/soporte/usuarios
     * ========================= */
    public function listar(): void
    {
        if (!$this->puedeAccederSoporte()) {
            $this->json(403, [
                'ok'      => false,
                'error'   => 'FORBIDDEN',
                'mensaje' => 'Acceso restringido.'
            ]);
            return;
        }

        $estado = $this->normalizarEstado($_GET['estado'] ?? 'revision');
        $q      = trim((string)($_GET['q'] ?? ''));
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = (int)($_GET['limit'] ?? 10);
        $limit  = ($limit <= 0) ? 10 : min($limit, 100);

        $conjunto   = $this->normalizarConjunto($_GET['conjunto'] ?? '');
        $conjuntoId = (int)($_GET['conjunto_id'] ?? 0);

        try {
            $m = new SoporteUsuarios();

            $res = $m->listar([
                'estado'      => $estado,
                'q'           => $q,
                'page'        => $page,
                'limit'       => $limit,
                'conjunto'    => $conjunto,
                'conjunto_id' => $conjuntoId,
            ]);

            $this->json(200, [
                'ok'   => true,
                'data' => $res
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteUsuariosController::listar] ' . $e->getMessage());
            $this->json(500, [
                'ok'      => false,
                'error'   => 'SERVER_ERROR',
                'mensaje' => 'Error interno del servidor.'
            ]);
        }
    }

    /* =========================
     * POST /api/soporte/usuarios/{id}/estado
     * ========================= */
    public function actualizarEstado($codigoUsuario): void
    {
        $codigoUsuario = (int)$codigoUsuario;

        if (!$this->puedeAccederSoporte()) {
            $this->json(403, [
                'ok'      => false,
                'error'   => 'FORBIDDEN',
                'mensaje' => 'Acceso restringido.'
            ]);
            return;
        }

        if ($codigoUsuario <= 0) {
            $this->json(400, [
                'ok'      => false,
                'error'   => 'CODIGO_INVALIDO',
                'mensaje' => 'Código de usuario inválido.'
            ]);
            return;
        }

        $raw = file_get_contents('php://input');
        $in  = json_decode($raw ?: '[]', true);
        if (!is_array($in)) {
            $in = [];
        }

        $estadoNuevo = isset($in['estado']) ? (int)$in['estado'] : -1;
        $observacion = trim((string)($in['observacion'] ?? ''));

        if (!in_array($estadoNuevo, [0, 1, 2], true)) {
            $this->json(400, [
                'ok'      => false,
                'error'   => 'ESTADO_INVALIDO',
                'mensaje' => 'Estado inválido.'
            ]);
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
                $this->json(404, [
                    'ok'      => false,
                    'error'   => 'USUARIO_NO_ENCONTRADO_O_SIN_CAMBIOS',
                    'mensaje' => 'Usuario no encontrado o sin cambios.'
                ]);
                return;
            }

            // =========================================================
            // Si se inactiva:
            // - quitar estado observado
            // - si viene observación, guardarla como referencia
            // =========================================================
            if ($estadoNuevo === 0) {
                $m->quitarObservado($codigoUsuario);

                if ($observacion !== '') {
                    $m->guardarObservacionRevision($codigoUsuario, $observacion);
                }
            }

            // =========================================================
            // Si se aprueba:
            // - limpiar observaciones/revisión previa
            // - aplicar bono bienvenida
            // =========================================================
            $bono = null;

            if ($estadoNuevo === 2) {
                $m->limpiarRevision($codigoUsuario);

                $wallet = new Billetera();
                $bono = $wallet->aplicarBonoBienvenida($codigoUsuario, 15.00);

                if (empty($bono['ok'])) {
                    error_log(
                        '[EV][apiSoporteUsuariosController::actualizarEstado] ' .
                        'Usuario aprobado pero falló bono. u=' . $codigoUsuario .
                        ' err=' . ($bono['error'] ?? ($bono['mensaje'] ?? 'DESCONOCIDO'))
                    );
                }
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje' => 'Estado actualizado.',
                'bono'    => $bono
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiSoporteUsuariosController::actualizarEstado] ' . $e->getMessage());
            $this->json(500, [
                'ok'      => false,
                'error'   => 'SERVER_ERROR',
                'mensaje' => 'Error interno del servidor.'
            ]);
        }
    }
}