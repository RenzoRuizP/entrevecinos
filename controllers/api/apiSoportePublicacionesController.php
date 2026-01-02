<?php
// controllers/api/apiSoportePublicacionesController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/ProductoSoporte.php';

class apiSoportePublicacionesController
{
    private function obtenerUsuarioAuth(): ?array
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) return null;

        $u = SesionJWT::verificarToken($token);
        return is_array($u) ? $u : null;
    }

    private function esAdmin(array $u): bool
    {
        $rol = strtolower((string)($u['rol'] ?? ''));
        if (in_array($rol, ['admin', 'administrador'], true)) return true;

        $codigoRol = (int)($u['codigo_rol'] ?? 0);
        $adminId   = (int)(defined('EV_ADMIN_ROLE_ID') ? EV_ADMIN_ROLE_ID : 0);

        return ($codigoRol > 0 && $adminId > 0 && $codigoRol === $adminId);
    }




    public function listar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $auth = $this->obtenerUsuarioAuth();
            if (!$auth) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED', 'motivo' => 'sin_token']);
                return;
            }

            if (!$this->esAdmin($auth)) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'FORBIDDEN', 'motivo' => 'solo_admin']);
                return;
            }

            $filtros = [
                'estado' => $_GET['estado'] ?? 'pendiente',
                'q'      => $_GET['q'] ?? '',
                'page'   => $_GET['page'] ?? 1,
                'size'   => $_GET['size'] ?? 10,
            ];

            $m = new ProductoSoporte();
            $data = $m->listarSoporte($filtros);

            echo json_encode(['ok' => true, 'data' => $data]);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiSoportePublicacionesController::listar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'ERROR_SERVIDOR', 'mensaje' => 'Error al listar publicaciones.', 'detalle' => $e->getMessage()]);
            return;
        }
    }

    public function actualizarEstado($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $auth = $this->obtenerUsuarioAuth();
            if (!$auth) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED', 'motivo' => 'sin_token']);
                return;
            }

            if (!$this->esAdmin($auth)) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'FORBIDDEN', 'motivo' => 'solo_admin']);
                return;
            }

            $codigoProducto = (int)$id;
            if ($codigoProducto <= 0) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'ID_INVALIDO', 'mensaje' => 'ID inválido.']);
                return;
            }

            $estado = strtolower(trim((string)($_POST['estado'] ?? '')));
            $permitidos = ['pendiente', 'aprobada', 'rechazada'];
            if (!in_array($estado, $permitidos, true)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'ESTADO_INVALIDO', 'mensaje' => 'Estado inválido.']);
                return;
            }

            $map = [
                'pendiente' => 1,
                'aprobada'  => 2,
                'rechazada' => 3,
            ];
            $nuevoVisible = $map[$estado];

            $model = new ProductoSoporte();
            $prod = $model->obtenerDetalle($codigoProducto);
            if (!$prod) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'NO_ENCONTRADO', 'mensaje' => 'No se encontró la publicación.']);
                return;
            }

            // Blindaje: si ya estaba aprobada y vuelven a aprobar, no hacer nada.
            $actual = (int)($prod['visible'] ?? 0);
            if ($nuevoVisible === 2 && $actual === 2) {
                echo json_encode(['ok' => true, 'mensaje' => 'La publicación ya estaba aprobada.']);
                return;
            }

            $ok = $model->actualizarEstadoSoporte($codigoProducto, $nuevoVisible);
            if (!$ok) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'NO_SE_PUDO_ACTUALIZAR', 'mensaje' => 'No se pudo actualizar el estado.']);
                return;
            }

            echo json_encode(['ok' => true, 'mensaje' => 'Estado actualizado correctamente.']);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiSoportePublicacionesController::actualizarEstado] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'ERROR_SERVIDOR', 'mensaje' => 'Error al actualizar estado.', 'detalle' => $e->getMessage()]);
            return;
        }
    }
}
