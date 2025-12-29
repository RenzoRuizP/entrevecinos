<?php
// controllers/api/apiBilleteraController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/Billetera.php';

class apiBilleteraController
{
    private function json(int $code, array $payload)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        return;
    }

    /**
     * Devuelve el código de usuario autenticado o corta con 401.
     */
    private function obtenerUsuarioAuth(): int
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) {
            $this->json(401, ['ok' => false, 'mensaje' => 'Token no encontrado.']);
            exit;
        }

        $usuario = SesionJWT::verificarToken($token);
        if (!$usuario || empty($usuario['codigo_usuario'])) {
            $this->json(401, ['ok' => false, 'mensaje' => 'Token inválido o usuario no encontrado.']);
            exit;
        }

        return (int)$usuario['codigo_usuario'];
    }

    private function leerJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw ?: '[]', true);
        return is_array($json) ? $json : [];
    }

    /**
     * GET /api/billetera/saldo
     * (El router la llama como obtenerSaldo según tu error de PHP)
     */
    public function obtenerSaldo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new Billetera();
            $saldo = $model->obtenerSaldoActual($codigoUsuario);

            return $this->json(200, [
                'ok'           => true,
                'saldo_actual' => $saldo
            ]);

        } catch (Exception $e) {
            return $this->json(500, [
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
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $model = new Billetera();
            $movs  = $model->listarMovimientos($codigoUsuario);

            return $this->json(200, [
                'ok'          => true,
                'movimientos' => $movs
            ]);

        } catch (Exception $e) {
            return $this->json(500, [
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
     * Usado por publicacionDestacar.js (legacy)
     */
    public function debitarPublicacion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $json = $this->leerJsonBody();
            $codigoPublicacion = isset($json['codigo_publicacion'])
                ? (int)$json['codigo_publicacion']
                : 0;

            if ($codigoPublicacion <= 0) {
                return $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Código de publicación inválido.'
                ]);
            }

            $model = new Billetera();
            $res   = $model->debitarPorPublicacionDestacada($codigoUsuario, $codigoPublicacion, 1.00);

            if (!$res['ok']) {
                // Puede ser SALDO_INSUFICIENTE u otro error de negocio
                return $this->json(200, $res);
            }

            return $this->json(200, [
                'ok'           => true,
                'mensaje'      => 'Se debitó S/ 1.00 por destacar la publicación.',
                'saldo_actual' => $res['saldo_actual'] ?? null
            ]);

        } catch (Exception $e) {
            return $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al debitar la publicación.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /api/billetera/debitar-producto
     * Body JSON: { "codigo_producto": 123 }
     */
    public function debitarProducto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido']);
        }

        try {
            $codigoUsuario = $this->obtenerUsuarioAuth();

            $json = $this->leerJsonBody();
            $codigoProducto = isset($json['codigo_producto'])
                ? (int)$json['codigo_producto']
                : 0;

            if ($codigoProducto <= 0) {
                return $this->json(400, [
                    'ok'      => false,
                    'mensaje' => 'Código de producto inválido.'
                ]);
            }

            $model = new Billetera();

            // ✅ Preferido: método específico para producto
            if (method_exists($model, 'debitarPorProductoDestacado')) {
                $res = $model->debitarPorProductoDestacado($codigoUsuario, $codigoProducto, 1.00);
            } else {
                // ✅ Fallback para no romper si aún no creaste el método en Billetera.php
                // Recomendación: crear debitarPorProductoDestacado y actualizar tabla/relaciones si aplican.
                $res = $model->debitarPorPublicacionDestacada($codigoUsuario, $codigoProducto, 1.00);
                if (is_array($res)) {
                    $res['warning'] = 'FALLBACK_PUBLICACION_METHOD';
                }
            }

            if (!is_array($res) || empty($res['ok'])) {
                return $this->json(200, is_array($res) ? $res : [
                    'ok'      => false,
                    'codigo'  => 'ERROR_NEGOCIO',
                    'mensaje' => 'No se pudo procesar el débito del producto.'
                ]);
            }

            return $this->json(200, [
                'ok'           => true,
                'mensaje'      => 'Se debitó S/ 1.00 por destacar el producto.',
                'saldo_actual' => $res['saldo_actual'] ?? null
            ]);

        } catch (Exception $e) {
            return $this->json(500, [
                'ok'      => false,
                'mensaje' => 'Error al debitar el producto.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Alias para mantener compatibilidad con el JS que te dejé:
     * POST /api/billetera/debitar-producto-destacado
     * Body JSON: { "codigo_producto": 123 }
     */
    public function debitarProductoDestacado()
    {
        $this->debitarProducto();
    }
}
