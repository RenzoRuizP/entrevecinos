<?php
// controllers/api/apiSoporteRecargasController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/RecargaSaldo.php';

class apiSoporteRecargasController
{
    public function listar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // (Opcional) Puedes validar rol soporte aquí si ya lo manejas por roles/menú
            $usuarioAuth = $this->obtenerUsuarioAuth();
            if (!$usuarioAuth || empty($usuarioAuth['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode([
                    'ok' => false,
                    'error' => 'USUARIO_NO_ENCONTRADO',
                    'mensaje' => 'No se pudo identificar al usuario. Vuelve a iniciar sesión.'
                ]);
                return;
            }

            $filtros = [
                'estado' => $_GET['estado'] ?? 'pendiente',
                'rango'  => $_GET['rango'] ?? '7',
                'q'      => $_GET['q'] ?? '',
                'page'   => $_GET['page'] ?? 1,
                'size'   => $_GET['size'] ?? 10,
            ];

            $model = new RecargaSaldo();
            $data = $model->listarSoporte($filtros);

            echo json_encode([
                'ok' => true,
                'data' => $data
            ]);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiSoporteRecargasController::listar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => 'ERROR_SERVIDOR',
                'mensaje' => 'No se pudo listar recargas.',
            ]);
            return;
        }
    }

    public function actualizarEstado($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $usuarioAuth = $this->obtenerUsuarioAuth();
            if (!$usuarioAuth || empty($usuarioAuth['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode([
                    'ok' => false,
                    'error' => 'USUARIO_NO_ENCONTRADO',
                    'mensaje' => 'No se pudo identificar al usuario. Vuelve a iniciar sesión.'
                ]);
                return;
            }

            $id = (int)$id;
            $estado = strtolower(trim($_POST['estado'] ?? ''));
            $comentario = trim($_POST['comentario'] ?? '');

            if (!in_array($estado, ['observada', 'aprobada', 'rechazada'], true)) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'mensaje' => 'Estado inválido.']);
                return;
            }

            if (($estado === 'observada' || $estado === 'rechazada') && strlen($comentario) < 3) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'mensaje' => 'El comentario es obligatorio para Observada o Rechazada.']);
                return;
            }

            $model = new RecargaSaldo();
            $ok = $model->actualizarEstado(
                $id,
                $estado,
                ($comentario !== '' ? $comentario : null),
                (int)$usuarioAuth['codigo_usuario']
            );

            if (!$ok) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'mensaje' => 'No se pudo actualizar el estado.']);
                return;
            }

            echo json_encode([
                'ok' => true,
                'mensaje' => 'Estado actualizado correctamente.'
            ]);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiSoporteRecargasController::actualizarEstado] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error interno al actualizar estado.'
            ]);
            return;
        }
    }

    private function obtenerUsuarioAuth(): ?array
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) return null;

        $payload = SesionJWT::verificarToken($token);
        if (!$payload) return null;

        $email = '';
        if (!empty($payload['email'])) $email = (string)$payload['email'];
        elseif (!empty($payload['sub'])) $email = (string)$payload['sub'];
        elseif (!empty($payload['usuario']['email'])) $email = (string)$payload['usuario']['email'];

        $email = trim($email);
        if ($email === '') return null;

        $u = new User();
        $datos = $u->DatosUsuario($email);
        if (!$datos) return null;

        return $datos;
    }
}
