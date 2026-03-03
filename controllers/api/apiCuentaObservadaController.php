<?php
// controllers/api/apiCuentaObservadaController.php

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/UsuarioRevision.php';
require_once __DIR__ . '/../../models/CuentaObservada.php';
require_once __DIR__ . '/../../models/Usuario.php';

final class apiCuentaObservadaController
{
    public function observar($codigoUsuario): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $codigoUsuario = (int)$codigoUsuario;

        if ($codigoUsuario <= 0) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Usuario inválido.'
            ], JSON_UNESCAPED_UNICODE);
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
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $mensaje = trim((string)($input['observacion'] ?? ''));

        if ($mensaje === '') {
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'La observación es obligatoria.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // 1) Marca OBSERVADO en usuario_revision (estado_revision=3 + mensaje)
            $model = new UsuarioRevision();
            $res   = $model->observarDesdeSoporte($codigoUsuario, $mensaje);

            if (empty($res['ok'])) {
                http_response_code(400);
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                return;
            }

            // =========================================================
            // ✅ CORRECCIÓN DE RAÍZ:
            // Si Soporte OBSERVA, el vecino debe poder iniciar sesión
            // para ver el mensaje y reenviar el comprobante.
            // Por eso lo dejamos en estado=1 (En revisión).
            // =========================================================
            try {
                $u = new Usuario();
                $estadoActual = (int)$u->obtenerEstado($codigoUsuario);

                // Si está inactivo (0) o habilitado (2), lo pasamos a revisión (1)
                // (No afecta roles soporte/admin porque solo se usa con el usuario observado)
                if ($estadoActual !== 1) {
                    $u->actualizarEstado($codigoUsuario, 1);
                }
            } catch (Throwable $e2) {
                // No tumbamos el flujo principal, pero lo dejamos en log
                error_log('[EV][apiCuentaObservada::observar] No se pudo setear estado=1: ' . $e2->getMessage());
            }

            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiCuentaObservada::observar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error interno.'
            ], JSON_UNESCAPED_UNICODE);
            return;
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
            echo json_encode(['ok' => false, 'mensaje' => 'No autorizado.'], JSON_UNESCAPED_UNICODE);
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
            ], JSON_UNESCAPED_UNICODE);
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
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // =========================
        // Modelo
        // =========================
        try {
            $model = new CuentaObservada();
            $res   = $model->subsanar($codigoUsuario, $_FILES['comprobante']);

            if (empty($res['ok'])) {
                http_response_code(422);
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiCuentaObservada::reenviar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error interno al procesar el comprobante.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
    }
}