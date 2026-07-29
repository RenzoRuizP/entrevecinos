<?php
// controllers/api/apiCuentaObservadaController.php

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/UsuarioRevision.php';
require_once __DIR__ . '/../../models/CuentaObservada.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Notificacion.php';

final class apiCuentaObservadaController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function rolActual(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_rol'] ?? 0);
    }

    private function codigoUsuarioActual(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function esSoporteOAdmin(): bool
    {
        $rol = $this->rolActual();

        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        return ($rol === $adminId || $rol === $soporteId);
    }

    private function esVecino(): bool
    {
        return !$this->esSoporteOAdmin();
    }

    /**
     * OBSERVAR CUENTA DE USUARIO
     *
     * Uso:
     * POST /api/cuenta-observada/{codigoUsuario}/observar
     *
     * Este endpoint es para observaciones de validación inicial de cuenta:
     * usuario_revision.estado_revision = 3
     *
     * Las observaciones de cambio de residencia NO entran por aquí.
     * Esas se manejan en:
     * /api/soporte/residencias/{codigoSolicitud}/estado
     */
    public function observar($codigoUsuario): void
    {
        $codigoUsuario = (int)$codigoUsuario;

        if ($codigoUsuario <= 0) {
            $this->json(400, [
                'ok'      => false,
                'mensaje' => 'Usuario inválido.'
            ]);
            return;
        }

        if (!$this->esSoporteOAdmin()) {
            $this->json(403, [
                'ok'      => false,
                'mensaje' => 'No autorizado.'
            ]);
            return;
        }

        $raw = file_get_contents('php://input');
        $input = json_decode($raw ?: '[]', true);

        if (!is_array($input)) {
            $input = [];
        }

        $mensaje = trim((string)($input['observacion'] ?? ''));

        if ($mensaje === '') {
            $this->json(422, [
                'ok'      => false,
                'mensaje' => 'La observación es obligatoria.'
            ]);
            return;
        }

        try {
            /*
             * Marca OBSERVADO en usuario_revision:
             * estado_revision = 3
             */
            $revisionModel = new UsuarioRevision();
            $res = $revisionModel->observarDesdeSoporte($codigoUsuario, $mensaje);

            if (empty($res['ok'])) {
                $this->json(400, $res);
                return;
            }

            /*
             * Importante:
             * El usuario observado debe poder iniciar sesión para ver
             * la observación y reenviar su comprobante.
             *
             * Por eso se mantiene usuario.estado = 1.
             */
            try {
                $usuarioModel = new Usuario();

                if (method_exists($usuarioModel, 'obtenerEstado')) {
                    $estadoActual = (int)$usuarioModel->obtenerEstado($codigoUsuario);

                    if ($estadoActual !== 1) {
                        $usuarioModel->actualizarEstado($codigoUsuario, 1);
                    }
                } else {
                    $usuarioModel->actualizarEstado($codigoUsuario, 1);
                }
            } catch (Throwable $e2) {
                error_log('[EV][apiCuentaObservadaController::observar] No se pudo actualizar usuario.estado=1: ' . $e2->getMessage());
            }

            try {
                $notif = new Notificacion();
                $notif->crearOActualizarNoLeida([
                    'codigo_usuario' => $codigoUsuario,
                    'categoria' => Notificacion::CAT_CUENTA,
                    'subcategoria' => 'cuenta_observada',
                    'referencia_id' => $codigoUsuario,
                    'titulo' => 'Tu cuenta necesita una corrección',
                    'mensaje' => $mensaje,
                    'payload' => [
                        'codigo_usuario' => $codigoUsuario,
                        'estado_revision' => 3,
                        'ruta' => '/cuenta-observada',
                    ],
                ]);
            } catch (Throwable $e2) {
                error_log('[EV][apiCuentaObservadaController::observar][notificacion] ' . $e2->getMessage());
            }

            $this->json(200, [
                'ok'      => true,
                'mensaje' => $res['mensaje'] ?? 'Observación registrada correctamente.',
                'data'    => [
                    'codigo_usuario'  => $codigoUsuario,
                    'estado_revision' => 3,
                    'tipo'            => 'cuenta'
                ]
            ]);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiCuentaObservadaController::observar] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error interno al registrar la observación.'
            ]);
            return;
        }
    }

    /**
     * REENVIAR COMPROBANTE OBSERVADO
     *
     * Uso:
     * POST /api/cuenta-observada/reenviar
     *
     * Este endpoint ahora soporta dos casos:
     *
     * 1) Cuenta observada:
     *    usuario_revision.estado_revision = 3
     *    => pasa a estado_revision = 1
     *
     * 2) Cambio de residencia observado:
     *    usuario_residencia_solicitud.estado = 'observada'
     *    => pasa a estado = 'pendiente'
     *
     * La detección real se hace en CuentaObservada::subsanar().
     */
    public function reenviar(): void
    {
        $codigoUsuario = $this->codigoUsuarioActual();

        if ($codigoUsuario <= 0) {
            $this->json(401, [
                'ok'      => false,
                'mensaje' => 'No autorizado.'
            ]);
            return;
        }

        if (!$this->esVecino()) {
            $this->json(403, [
                'ok'      => false,
                'mensaje' => 'Acción no permitida para este rol.'
            ]);
            return;
        }

        if (empty($_FILES['comprobante']) || !is_array($_FILES['comprobante'])) {
            $this->json(400, [
                'ok'      => false,
                'mensaje' => 'Archivo no recibido.'
            ]);
            return;
        }

        try {
            $model = new CuentaObservada();

            /*
             * El modelo decide si se trata de:
             * - cuenta observada
             * - cambio de residencia observado
             */
            $res = $model->subsanar($codigoUsuario, $_FILES['comprobante']);

            if (empty($res['ok'])) {
                $this->json(422, $res);
                return;
            }

            $this->json(200, $res);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiCuentaObservadaController::reenviar] ' . $e->getMessage());

            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error interno al procesar el comprobante.'
            ]);
            return;
        }
    }
}