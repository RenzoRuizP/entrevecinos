<?php
// controllers/api/apiBilleteraController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Billetera.php';

class apiBilleteraController
{
    /**
     * POST /api/billetera/debitar-publicacion
     *
     * Entrada:
     *  - codigo_publicacion (POST o JSON)
     *
     * Usa el usuario autenticado (JWT)
     * y descuenta S/ 1.00 de su billetera.
     */
    public function debitarPublicacion()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // 1) Validar token
            $token = $_COOKIE['auth_token'] ?? null;
            if (!$token) {
                http_response_code(401);
                echo json_encode([
                    'ok'      => false,
                    'codigo'  => 'NO_TOKEN',
                    'mensaje' => 'Tu sesión ha expirado. Vuelve a iniciar sesión.'
                ]);
                return;
            }

            $datosToken = SesionJWT::verificarToken($token);
            if (!$datosToken || empty($datosToken['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode([
                    'ok'      => false,
                    'codigo'  => 'TOKEN_INVALIDO',
                    'mensaje' => 'Token inválido. Vuelve a iniciar sesión.'
                ]);
                return;
            }

            $codigo_usuario = (int)$datosToken['codigo_usuario'];

            // 2) Leer payload POST + JSON
            $payload = $_POST;

            if (empty($payload)) {
                $raw = file_get_contents('php://input');
                if ($raw) {
                    $json = json_decode($raw, true);
                    if (is_array($json)) {
                        $payload = $json;
                    }
                }
            }

            $codigo_publicacion = isset($payload['codigo_publicacion'])
                ? (int)$payload['codigo_publicacion']
                : 0;

            if ($codigo_publicacion <= 0) {
                http_response_code(400);
                echo json_encode([
                    'ok'      => false,
                    'codigo'  => 'SIN_PUBLICACION',
                    'mensaje' => 'No se recibió la publicación a destacar.'
                ]);
                return;
            }

            // 3) Lógica de negocio de billetera
            $billeteraModel = new Billetera();

            $resultado = $billeteraModel->debitarPorPublicacionDestacada(
                $codigo_usuario,
                $codigo_publicacion,
                1.00
            );

            if (!$resultado['ok']) {
                if (($resultado['codigo'] ?? '') === 'SALDO_INSUFICIENTE') {
                    echo json_encode([
                        'ok'      => false,
                        'codigo'  => 'SALDO_INSUFICIENTE',
                        'mensaje' => 'Tu billetera no tiene saldo suficiente para publicar. Recarga tu billetera y vuelve a intentarlo.'
                    ]);
                    return;
                }

                http_response_code(500);
                echo json_encode([
                    'ok'      => false,
                    'codigo'  => 'ERROR',
                    'mensaje' => $resultado['mensaje']
                        ?? 'Ocurrió un problema al procesar el cargo en tu billetera.'
                ]);
                return;
            }

            // 4) OK
            echo json_encode([
                'ok'           => true,
                'codigo'       => 'OK',
                'mensaje'      => 'Se ha descontado S/ 1.00 de tu billetera para publicar la publicación.',
                'saldo_actual' => $resultado['saldo_actual']
            ]);

        } catch (Throwable $e) {
            error_log('apiBilleteraController::debitarPublicacion -> ' . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'codigo'  => 'ERROR_SERVIDOR',
                'mensaje' => 'Ocurrió un error interno al procesar tu solicitud.'
            ]);
        }
    }
}
