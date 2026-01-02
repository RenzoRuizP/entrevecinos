<?php
// controllers/api/apiSoporteProductosController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/ProductoSoporte.php';

class apiSoporteProductosController
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
        $codigoRol = (int)($u['codigo_rol'] ?? 0);
        $adminId   = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        return ($codigoRol > 0 && $codigoRol === $adminId);
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $u = $this->obtenerUsuarioAuth();
            if (!$u || empty($u['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED', 'motivo' => 'sin_token']);
                return;
            }

            if (!$this->esAdmin($u)) {
                http_response_code(403);
                echo json_encode([
                    'ok' => false,
                    'error' => 'FORBIDDEN',
                    'motivo' => 'solo_admin',
                    'mensaje' => 'Solo el administrador puede acceder a Atender publicación.'
                ]);
                return;
            }

            $filtros = [
                'estado' => $_GET['estado'] ?? 'pendiente', // borrador|pendiente|aprobada|rechazada|todas
                'q'      => $_GET['q'] ?? '',
                'page'   => $_GET['page'] ?? 1,
                'size'   => $_GET['size'] ?? 10,
            ];

            $m = new ProductoSoporte();
            $data = $m->listarSoporte($filtros);

            echo json_encode(['ok' => true, 'data' => $data]);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiSoporteProductosController::listar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'ERROR_SERVIDOR', 'detalle' => $e->getMessage()]);
            return;
        }
    }

    public function detalle($id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $u = $this->obtenerUsuarioAuth();
            if (!$u || empty($u['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            if (!$this->esAdmin($u)) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'FORBIDDEN', 'motivo' => 'solo_admin']);
                return;
            }

            $codigoProducto = (int)$id;
            $m = new ProductoSoporte();
            $det = $m->obtenerDetalle($codigoProducto);

            if (!$det) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'NO_ENCONTRADO', 'mensaje' => 'No se encontró la publicación.']);
                return;
            }

            echo json_encode(['ok' => true, 'data' => $det]);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiSoporteProductosController::detalle] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'ERROR_SERVIDOR', 'detalle' => $e->getMessage()]);
            return;
        }
    }

    public function actualizarEstado($id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $u = $this->obtenerUsuarioAuth();
            if (!$u || empty($u['codigo_usuario'])) {
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            if (!$this->esAdmin($u)) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'FORBIDDEN', 'motivo' => 'solo_admin']);
                return;
            }

            $codigoProducto = (int)$id;

            $estado = strtolower(trim((string)($_POST['estado'] ?? '')));
            $map = [
                'borrador'  => 0,
                'pendiente' => 1,
                'aprobada'  => 2,
                'rechazada' => 3,
            ];

            if (!array_key_exists($estado, $map)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'ESTADO_INVALIDO']);
                return;
            }

            $nuevoVisible = (int)$map[$estado];

            $m = new ProductoSoporte();
            $ok = $m->actualizarEstadoSoporte($codigoProducto, $nuevoVisible);

            if (!$ok) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'NO_SE_PUDO_ACTUALIZAR']);
                return;
            }

            echo json_encode(['ok' => true, 'mensaje' => 'Estado actualizado.']);
            return;

        } catch (Throwable $e) {
            error_log('[EV][apiSoporteProductosController::actualizarEstado] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'ERROR_SERVIDOR', 'detalle' => $e->getMessage()]);
            return;
        }
    }
}
