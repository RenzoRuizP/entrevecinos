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
        echo json_encode($payload);
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

        $filtros = [
            'categoria' => $_GET['categoria'] ?? 'residencia',
            'estado'    => $_GET['estado'] ?? 'no_leida', // no_leida|leida|all
            'page'      => $_GET['page'] ?? 1,
            'size'      => $_GET['size'] ?? 10,
        ];

        $m = new Notificacion();
        $res = $m->listarPorUsuario($u, $filtros);
        $this->json(200, $res);
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

        $m = new Notificacion();
        $ok = $m->marcarLeida($id, $u);

        $this->json(200, [
            'ok' => $ok,
            'mensaje' => $ok ? 'Notificación marcada como leída.' : 'No se pudo marcar.'
        ]);
    }

    public function marcarTodasLeidas(): void
    {
        $u = $this->codigoUsuarioAuth();
        if ($u <= 0) {
            $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
            return;
        }

        $categoria = 'all';
        if (!empty($_POST['categoria'])) {
            $categoria = strtolower(trim((string)$_POST['categoria']));
        } else {
            $raw = file_get_contents('php://input');
            if (is_string($raw) && trim($raw) !== '') {
                $json = json_decode($raw, true);
                if (is_array($json) && isset($json['categoria'])) {
                    $categoria = strtolower(trim((string)$json['categoria']));
                }
            }
        }

        $permitidas = ['all', 'residencia', 'soporte', 'pedidos', 'pedido', 'servicio', 'comunidad'];
        if (!in_array($categoria, $permitidas, true)) {
            $categoria = 'all';
        }

        // Normalización defensiva: si el sistema antiguo usa pedido/pedidos, se respeta el valor recibido.
        $m = new Notificacion();
        $total = $m->marcarTodasLeidas($u, $categoria);

        $this->json(200, [
            'ok' => true,
            'mensaje' => 'Notificaciones marcadas como leídas.',
            'data' => [
                'actualizadas' => $total,
                'categoria' => $categoria,
            ],
        ]);
    }

    public function counts(): void
    {
        $u = $this->codigoUsuarioAuth();
        if ($u <= 0) {
            $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
            return;
        }

        $m = new Notificacion();

        $this->json(200, [
            'ok' => true,
            'data' => [
                'total'      => $m->contarNoLeidas($u, 'all'),
                'residencia' => $m->contarNoLeidas($u, 'residencia'),
                // futuro:
                'soporte'    => $m->contarNoLeidas($u, 'soporte'),
                'pedidos'    => $m->contarNoLeidas($u, 'pedidos'),
                'servicio'   => $m->contarNoLeidas($u, 'servicio'),
            ]
        ]);
    }
}
