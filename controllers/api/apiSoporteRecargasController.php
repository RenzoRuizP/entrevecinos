<?php
// controllers/api/apiSoporteRecargasController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/RecargaSaldo.php';
require_once __DIR__ . '/../../models/Billetera.php';

class apiSoporteRecargasController
{
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

        // TODO: validar rol soporte (cuando me confirmes el codigo_rol de Soporte)
        return (int)$usuario['codigo_usuario'];
    }

    // GET /api/soporte/recargas
    public function listar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        try {
            $this->obtenerUsuarioAuth();

            $filtros = [
                'page' => $_GET['page'] ?? 1,
                'size' => $_GET['size'] ?? 10,
                'estado' => $_GET['estado'] ?? 'pendiente',
                'rango' => $_GET['rango'] ?? '7',
                'q' => $_GET['q'] ?? '',
            ];

            $model = new RecargaSaldo();
            $data = $model->listarSoporte($filtros);

            // URL pública: BASE_URL + resources/images/recargas/<archivo>
            $base = rtrim(BASE_URL, '/');
            $publicFolder = 'resources/images/recargas/';

            foreach ($data['items'] as &$it) {
                $path = $it['comprobante_path'] ?? '';

                // Soportar si guardas solo filename o ruta completa
                // 1) si ya viene con "resources/", úsalo tal cual
                // 2) si es solo "archivo.jpg", prepéndele la carpeta
                if ($path) {
                    $path = str_replace('\\', '/', $path); // por si viene con backslashes
                    if (stripos($path, 'resources/') === 0) {
                        $it['comprobante_url'] = $base . '/' . ltrim($path, '/');
                    } else {
                        $it['comprobante_url'] = $base . '/' . $publicFolder . ltrim($path, '/');
                    }
                } else {
                    $it['comprobante_url'] = '';
                }
            }

            echo json_encode([
                'ok' => true,
                'pendientes' => $data['pendientes'],
                'total' => $data['total'],
                'page' => $data['page'],
                'size' => $data['size'],
                'items' => $data['items'],
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'mensaje' => 'Error al listar recargas.', 'error' => $e->getMessage()]);
        }
    }

    // POST /api/soporte/recargas/{id}/estado
    public function actualizarEstado($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        $codigoSoporte = $this->obtenerUsuarioAuth();
        $codigoRecarga = (int)$id;

        try {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true) ?: [];

            $estado = strtolower(trim($json['estado'] ?? ''));
            $comentario = trim($json['comentario'] ?? '');

            $permitidos = ['pendiente','observada','aprobada','rechazada'];
            if (!in_array($estado, $permitidos, true)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'mensaje' => 'Estado inválido.']);
                return;
            }

            if (in_array($estado, ['observada','rechazada'], true) && $comentario === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'mensaje' => 'El comentario es obligatorio para Observada/Rechazada.']);
                return;
            }

            $recargaModel = new RecargaSaldo();
            $recarga = $recargaModel->obtenerPorId($codigoRecarga);

            if (!$recarga) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'mensaje' => 'Recarga no encontrada.']);
                return;
            }

            if ($recarga['estado'] === 'aprobada') {
                echo json_encode(['ok' => false, 'mensaje' => 'Esta recarga ya fue aprobada.']);
                return;
            }

            // Si APRUEBA: acreditar billetera + registrar movimiento
            if ($estado === 'aprobada') {
                $codigoUsuario = (int)$recarga['codigo_usuario'];
                $monto = (float)$recarga['monto'];
                $metodo = strtoupper($recarga['metodo'] ?? 'YAPE');

                $billetera = new Billetera();
                $res = $billetera->acreditarPorRecargaManual(
                    $codigoUsuario,
                    $monto,
                    $codigoRecarga,
                    $metodo,
                    false,
                    null
                );

                if (!$res['ok']) {
                    echo json_encode(['ok' => false, 'mensaje' => $res['mensaje'] ?? 'No se pudo acreditar saldo.']);
                    return;
                }
            }

            $ok = $recargaModel->actualizarEstado(
                $codigoRecarga,
                $estado,
                $comentario !== '' ? $comentario : null,
                $codigoSoporte
            );

            if (!$ok) {
                echo json_encode(['ok' => false, 'mensaje' => 'No se pudo actualizar la recarga.']);
                return;
            }

            $msg =
                $estado === 'aprobada' ? 'Recarga aprobada y saldo acreditado.' :
                ($estado === 'observada' ? 'Recarga marcada como observada.' :
                ($estado === 'rechazada' ? 'Recarga rechazada.' : 'Estado actualizado.'));

            echo json_encode(['ok' => true, 'mensaje' => $msg]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'mensaje' => 'Error al actualizar estado.', 'error' => $e->getMessage()]);
        }
    }
}
