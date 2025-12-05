<?php
// controllers/api/apiBilleteraController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Billetera.php';

class apiBilleteraController
{
    /**
     * Devuelve el código de usuario autenticado o corta con 401.
     */
    private function obtenerUsuarioAuth(): int
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'mensaje' => 'Token no encontrado.']);
            exit;
        }

        $usuario = SesionJWT::verificarToken($token);
        if (!$usuario || empty($usuario['codigo_usuario'])) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'mensaje' => 'Token inválido o usuario no encontrado.']);
            exit;
        }

        return (int)$usuario['codigo_usuario'];
    }

    /**
     * GET /api/billetera/saldo
     * (El router la llama como obtenerSaldo según tu error de PHP)
     */
    public function obtenerSaldo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new Billetera();
            $saldo = $model->obtenerSaldoActual($codigoUsuario);

            echo json_encode([
                'ok'           => true,
                'saldo_actual' => $saldo
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error al obtener saldo.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Alias opcional por si en algún momento usas /api/billetera/saldo -> saldo
     */
    public function saldo()
    {
        $this->obtenerSaldo();
    }

    /**
     * GET /api/billetera/movimientos
     */
    public function obtenerMovimientos()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new Billetera();
            $movs  = $model->listarMovimientos($codigoUsuario);

            echo json_encode([
                'ok'          => true,
                'movimientos' => $movs
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error al obtener movimientos.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Alias por si tu router usa otro nombre.
     */
    public function movimientos()
    {
        $this->obtenerMovimientos();
    }

    /**
     * POST /api/billetera/debitar-publicacion
     * Body JSON: { "codigo_publicacion": 123 }
     * Usado por publicacionDestacar.js
     */
    public function debitarPublicacion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $raw  = file_get_contents('php://input');
            $json = json_decode($raw, true);
            $codigoPublicacion = isset($json['codigo_publicacion'])
                ? (int)$json['codigo_publicacion']
                : 0;

            if ($codigoPublicacion <= 0) {
                http_response_code(400);
                echo json_encode([
                    'ok'      => false,
                    'mensaje' => 'Código de publicación inválido.'
                ]);
                return;
            }

            $model = new Billetera();
            $res   = $model->debitarPorPublicacionDestacada($codigoUsuario, $codigoPublicacion, 1.00);

            if (!$res['ok']) {
                // Puede ser SALDO_INSUFICIENTE u otro error de negocio
                echo json_encode($res);
                return;
            }

            echo json_encode([
                'ok'           => true,
                'mensaje'      => 'Se debitó S/ 1.00 por destacar la publicación.',
                'saldo_actual' => $res['saldo_actual']
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error al debitar la publicación.',
                'error'   => $e->getMessage()
            ]);
        }
    }
}
