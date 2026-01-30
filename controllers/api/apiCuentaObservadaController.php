<?php
// controllers/api/apiCuentaObservadaController.php

declare(strict_types=1);

require_once __DIR__ . '/../../models/CuentaObservada.php';

final class apiCuentaObservadaController
{
    public function observar($codigoUsuario): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // 🔒 Cast explícito (SOLUCIÓN)
        $codigoUsuario = (int)$codigoUsuario;

        if ($codigoUsuario <= 0) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Usuario inválido.'
            ]);
            return;
        }

        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $rol  = (int)($auth['codigo_rol'] ?? 0);

        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        if ($rol !== $adminId && $rol !== $soporteId) {
            http_response_code(403);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'No autorizado.'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $mensaje = trim((string)($input['observacion'] ?? ''));

        if ($mensaje === '') {
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'La observación es obligatoria.'
            ]);
            return;
        }

        try {
            $model = new CuentaObservada();
            $res   = $model->observarDesdeSoporte($codigoUsuario, $mensaje);

            if (!$res['ok']) {
                http_response_code(400);
            }

            echo json_encode($res);

        } catch (Throwable $e) {
            error_log('[EV][apiCuentaObservada::observar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error interno.'
            ]);
        }
    }



    /**
     * REENVIAR COMPROBANTE (vecino)
     * POST /api/cuenta-observada/reenviar
     */
    public function reenviar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // =========================
        // Sesión
        // =========================
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $codigoUsuario = (int)($auth['codigo_usuario'] ?? 0);
        $codigoRol     = (int)($auth['codigo_rol'] ?? 0);

        if ($codigoUsuario <= 0) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'mensaje' => 'No autorizado.']);
            return;
        }

        // =========================
        // Solo vecino
        // =========================
        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        if ($codigoRol === $adminId || $codigoRol === $soporteId) {
            http_response_code(403);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Acción no permitida para este rol.'
            ]);
            return;
        }

        // =========================
        // Archivo
        // =========================
        if (empty($_FILES['comprobante'])) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Archivo no recibido.'
            ]);
            return;
        }

        // =========================
        // Modelo
        // =========================
        try {
            $model = new CuentaObservada();
            $res   = $model->subsanar($codigoUsuario, $_FILES['comprobante']);

            if (!$res['ok']) {
                http_response_code(422);
                echo json_encode($res);
                return;
            }

            echo json_encode($res);

        } catch (Throwable $e) {
            error_log('[EV][apiCuentaObservada::reenviar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error interno al procesar el comprobante.'
            ]);
        }
    }
}
