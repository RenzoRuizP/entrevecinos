<?php
// controllers/api/apiSoporteResidenciasController.php
declare(strict_types=1);

require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';
require_once __DIR__ . '/../../models/Notificacion.php';

class apiSoporteResidenciasController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function puedeAcceder(): bool
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $rol  = (int)($auth['codigo_rol'] ?? 0);

        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        return in_array($rol, [$adminId, $soporteId], true);
    }

    public function listar(): void
    {
        if (!$this->puedeAcceder()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $model = new UsuarioResidenciaSolicitud();

        $filtros = [
            'estado' => $_GET['estado'] ?? 'pendiente',
            'tipo'   => $_GET['tipo'] ?? ($_GET['conjunto'] ?? ''),
            'codigo' => $_GET['codigo'] ?? ($_GET['conjunto_id'] ?? 0),
            'q'      => $_GET['q'] ?? '',
            'page'   => $_GET['page'] ?? 1,
            'size'   => $_GET['size'] ?? ($_GET['limit'] ?? 10),
        ];

        $res = $model->listarSoporte($filtros);

        $this->json(200, [
            'ok' => true,
            'data' => [
                'items' => $res['data'] ?? [],
                'total' => (int)($res['meta']['total'] ?? 0),
                'page'  => (int)($res['meta']['page'] ?? 1),
                'size'  => (int)($res['meta']['size'] ?? 10),
            ]
        ]);
    }

    public function actualizarEstado($codigoSolicitud): void
    {
        if (!$this->puedeAcceder()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $id = (int)$codigoSolicitud;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }

        $body = json_decode(file_get_contents('php://input') ?: '[]', true);
        if (!is_array($body)) $body = [];

        $estado = strtolower(trim((string)($body['estado'] ?? '')));
        $comentario = trim((string)($body['comentario_admin'] ?? ($body['comentario'] ?? '')));

        if (!in_array($estado, ['pendiente','observada','aprobada','rechazada'], true)) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Estado inválido.']);
            return;
        }

        $model = new UsuarioResidenciaSolicitud();

        $sol = $model->obtenerSolicitud($id);
        if (!$sol) {
            $this->json(404, ['ok' => false, 'mensaje' => 'Solicitud no encontrada.']);
            return;
        }

        $ok = $model->actualizarEstadoSoporte($id, $estado, $comentario);

        $notificacionesResueltas = 0;

        if ($ok && in_array($estado, ['observada', 'aprobada', 'rechazada'], true)) {
            $codigoUsuario = (int)($sol['codigo_usuario'] ?? 0);

            try {
                $adminId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
                $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;
                $notifEquipo = new Notificacion($model->getDblink());
                $notificacionesResueltas = $notifEquipo->marcarLeidasPorReferenciaRoles(
                    [$adminId, $soporteId],
                    Notificacion::CAT_SOPORTE,
                    $id,
                    'residencia_pendiente_soporte'
                );
            } catch (Throwable $eNotifEquipo) {
                error_log('[EV][apiSoporteResidenciasController::actualizarEstado][resolver_notificacion_soporte] ' . $eNotifEquipo->getMessage());
            }

            if ($codigoUsuario > 0) {
                $titulos = [
                    'observada' => 'Tu solicitud de residencia fue observada',
                    'aprobada' => 'Tu nueva residencia fue aprobada',
                    'rechazada' => 'Tu solicitud de residencia fue rechazada',
                ];
                $mensajes = [
                    'observada' => $comentario !== ''
                        ? $comentario
                        : 'Revisa el detalle y reenvía el comprobante con la corrección solicitada.',
                    'aprobada' => 'Tu cambio de residencia fue validado correctamente en Entre Vecinos.',
                    'rechazada' => $comentario !== ''
                        ? $comentario
                        : 'Revisa el detalle de la solicitud y, si corresponde, registra una nueva solicitud.',
                ];

                try {
                    $notif = new Notificacion($model->getDblink());
                    $notif->crearOActualizarNoLeida([
                        'codigo_usuario' => $codigoUsuario,
                        'categoria' => Notificacion::CAT_RESIDENCIA,
                        'subcategoria' => 'residencia_' . $estado,
                        'referencia_id' => $id,
                        'titulo' => $titulos[$estado],
                        'mensaje' => $mensajes[$estado],
                        'payload' => [
                            'codigo_solicitud' => $id,
                            'estado' => $estado,
                            'comentario_admin' => $comentario,
                            'ruta' => '/notificaciones-residencia',
                        ],
                    ]);
                } catch (Throwable $eNotif) {
                    error_log('[EV][apiSoporteResidenciasController::actualizarEstado][notificacion] ' . $eNotif->getMessage());
                }
            }
        }

        $this->json(200, [
            'ok' => $ok,
            'mensaje' => $ok ? 'Solicitud actualizada.' : 'No se pudo actualizar.',
            'notificaciones_resueltas' => $notificacionesResueltas,
        ]);
    }
}