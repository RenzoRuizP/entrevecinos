<?php
// controllers/api/apiNotificacionesController.php
declare(strict_types=1);

require_once __DIR__ . '/../../models/Notificacion.php';

final class apiNotificacionesController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function codigoUsuarioAuth(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    public function listar(): void
    {
        $u = $this->codigoUsuarioAuth();
        if ($u <= 0) {
            $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
            return;
        }

        try {
            $m = new Notificacion();
            $res = $m->listarPorUsuario($u, [
                'categoria' => $_GET['categoria'] ?? 'all',
                'estado' => $_GET['estado'] ?? 'no_leida',
                'page' => $_GET['page'] ?? 1,
                'size' => $_GET['size'] ?? 10,
            ]);
            $this->json(200, $res);
        } catch (Throwable $e) {
            error_log('[EV][apiNotificacionesController::listar] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudieron cargar las notificaciones.']);
        }
    }

    public function resumen(): void
    {
        $u = $this->codigoUsuarioAuth();
        if ($u <= 0) {
            $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
            return;
        }

        $incluirItems = filter_var($_GET['incluir_items'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $limite = max(1, min(20, (int)($_GET['limite'] ?? 8)));

        try {
            $m = new Notificacion();
            $this->json(200, [
                'ok' => true,
                'data' => $m->resumen($u, $incluirItems, $limite),
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiNotificacionesController::resumen] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo obtener el resumen de notificaciones.']);
        }
    }

    public function marcarLeida($codigoNotificacion): void
    {
        $u = $this->codigoUsuarioAuth();
        $id = (int)$codigoNotificacion;

        if ($u <= 0) {
            $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
            return;
        }
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }

        try {
            $m = new Notificacion();
            $resultado = $m->marcarLeidaConResultado($id, $u);

            if ($resultado === Notificacion::NO_ENCONTRADA) {
                $this->json(404, [
                    'ok' => false,
                    'mensaje' => 'La notificación no existe o no está disponible.',
                ]);
                return;
            }

            $this->json(200, [
                'ok' => true,
                'mensaje' => $resultado === Notificacion::YA_LEIDA
                    ? 'La notificación ya estaba marcada como leída.'
                    : 'Notificación marcada como leída.',
                'data' => ['resultado' => $resultado],
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiNotificacionesController::marcarLeida] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo actualizar la notificación.']);
        }
    }

    public function marcarTodasLeidas(): void
    {
        $u = $this->codigoUsuarioAuth();
        if ($u <= 0) {
            $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
            return;
        }

        $categoria = 'all';
        if (isset($_POST['categoria'])) {
            $categoria = strtolower(trim((string)$_POST['categoria']));
        } else {
            $raw = file_get_contents('php://input');
            if (is_string($raw) && trim($raw) !== '') {
                $input = json_decode($raw, true);
                if (is_array($input) && isset($input['categoria'])) {
                    $categoria = strtolower(trim((string)$input['categoria']));
                }
            }
        }

        if (!Notificacion::esCategoriaFiltroValida($categoria)) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Categoría de notificación inválida.']);
            return;
        }

        try {
            $m = new Notificacion();
            $total = $m->marcarTodasLeidas($u, $categoria);

            $this->json(200, [
                'ok' => true,
                'mensaje' => $total > 0
                    ? 'Notificaciones marcadas como leídas.'
                    : 'No había notificaciones pendientes para este filtro.',
                'data' => [
                    'actualizadas' => $total,
                    'categoria' => $categoria,
                ],
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiNotificacionesController::marcarTodasLeidas] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudieron marcar las notificaciones.']);
        }
    }

    /**
     * Compatibilidad con el endpoint anterior. Ahora utiliza una sola consulta.
     */
    public function counts(): void
    {
        $u = $this->codigoUsuarioAuth();
        if ($u <= 0) {
            $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
            return;
        }

        try {
            $m = new Notificacion();
            $resumen = $m->resumenNoLeidas($u);
            $categorias = $resumen['categorias'];

            $this->json(200, [
                'ok' => true,
                'data' => [
                    'total' => $resumen['total'],
                    'cuenta' => $categorias[Notificacion::CAT_CUENTA],
                    'residencia' => $categorias[Notificacion::CAT_RESIDENCIA],
                    'publicacion' => $categorias[Notificacion::CAT_PUBLICACION],
                    'billetera' => $categorias[Notificacion::CAT_BILLETERA],
                    'pedido' => $categorias[Notificacion::CAT_PEDIDO],
                    'pedidos' => $categorias[Notificacion::CAT_PEDIDO],
                    'servicio' => $categorias[Notificacion::CAT_SERVICIO],
                    'comunidad' => $categorias[Notificacion::CAT_COMUNIDAD],
                    'soporte' => $categorias[Notificacion::CAT_SOPORTE],
                ],
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiNotificacionesController::counts] ' . $e->getMessage());
            $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo obtener el contador.']);
        }
    }
}
