<?php
// controllers/api/apiCuentaObservadaController.php

declare(strict_types=1);

require_once __DIR__ . '/../../models/CuentaObservada.php';
require_once __DIR__ . '/../../database/Conexion.php';

final class apiCuentaObservadaController
{
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
        // Verificar estado OBSERVADO
        // =========================
        try {
            $cn = new Conexion();
            if (!method_exists($cn, 'getDblink')) {
                throw new Exception('Conexion inválida.');
            }

            $db = $cn->getDblink();
            if (!$db) {
                throw new Exception('Sin conexión a BD.');
            }

            $sql = "
                SELECT estado_revision
                FROM usuario_revision
                WHERE codigo_usuario = :id
                LIMIT 1
            ";
            $st = $db->prepare($sql);
            $st->execute([':id' => $codigoUsuario]);
            $row = $st->fetch(PDO::FETCH_ASSOC);

            if (!$row || (int)$row['estado_revision'] !== 3) {
                http_response_code(409);
                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Tu cuenta no está en estado OBSERVADO.'
                ]);
                return;
            }

        } catch (Throwable $e) {
            error_log('[EV][apiCuentaObservada][estado] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'No se pudo validar el estado de la cuenta.'
            ]);
            return;
        }

        // =========================
        // Subsanar (modelo)
        // =========================
        try {
            $model = new CuentaObservada();
            $res   = $model->subsanar($codigoUsuario, $_FILES['comprobante']);

            if (!$res['ok']) {
                http_response_code(422);
                echo json_encode($res);
                return;
            }

            http_response_code(200);
            echo json_encode($res);

        } catch (Throwable $e) {
            error_log('[EV][apiCuentaObservada][subsanar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error interno al procesar el comprobante.'
            ]);
        }
    }
}
