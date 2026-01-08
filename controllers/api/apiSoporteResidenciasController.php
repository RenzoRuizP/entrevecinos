<?php
// controllers/api/apiSoporteResidenciasController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';
require_once __DIR__ . '/../../models/User.php';

class apiSoporteResidenciasController
{
    private function json(int $code, array $payload): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    private function authEmail(): string
    {
        // ✅ Preferir EV_AUTH del router (ya validó el token)
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $email = (string)($auth['email'] ?? '');
        if ($email !== '') return $email;

        // Fallback (si se invoca sin pasar por router)
        $token = $_COOKIE['auth_token'] ?? null;
        if (!$token) return '';

        $data = SesionJWT::verificarToken($token);
        return (string)($data['email'] ?? '');
    }

    private function requireAdmin(): bool
    {
        $email = $this->authEmail();
        if ($email === '') return false;

        // ✅ Con tu implementación actual, esto depende de que DatosUsuario retorne codigo_rol.
        $u = new User();
        $datos = $u->DatosUsuario($email);
        if (!$datos) return false;

        $rol = (int)($datos['codigo_rol'] ?? 0);
        $adminRole = (defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1);

        return $rol === $adminRole;
    }

    // GET /api/soporte/residencias
    public function listar(): void
    {
        try {
            if (!$this->requireAdmin()) {
                $this->json(403, ['ok' => false, 'mensaje' => 'No autorizado.']);
                return;
            }

            $model = new UsuarioResidenciaSolicitud();

            $estado = $_GET['estado'] ?? 'pendiente';
            $tipo   = $_GET['tipo'] ?? '';
            $codigo = $_GET['codigo'] ?? '';
            $q      = $_GET['q'] ?? '';
            $page   = $_GET['page'] ?? 1;
            $size   = $_GET['size'] ?? 10;

            $res = $model->listarSoporte([
                'estado' => $estado,
                'tipo'   => $tipo,
                'codigo' => $codigo,
                'q'      => $q,
                'page'   => $page,
                'size'   => $size,
            ]);

            $this->json(200, $res);

        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_SOPORTE_RESIDENCIAS',
                'mensaje' => 'No se pudo listar solicitudes.',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    // POST /api/soporte/residencias/{id}/estado
    public function actualizarEstado(int $codigoSolicitud): void
    {
        try {
            if (!$this->requireAdmin()) {
                $this->json(403, ['ok' => false, 'mensaje' => 'No autorizado.']);
                return;
            }

            $raw = file_get_contents('php://input');
            $body = json_decode($raw ?: '[]', true);
            if (!is_array($body)) $body = [];

            $estado = strtolower(trim((string)($body['estado'] ?? '')));
            $comentario = isset($body['comentario']) ? trim((string)$body['comentario']) : null;

            if (!in_array($estado, ['aprobada','observada','rechazada'], true)) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Estado inválido.']);
                return;
            }

            $model = new UsuarioResidenciaSolicitud();

            if ($estado === 'aprobada') {
                $ok = $model->aprobarSolicitud((int)$codigoSolicitud, $comentario);
                $this->json($ok ? 200 : 400, [
                    'ok' => $ok,
                    'mensaje' => $ok ? 'Solicitud aprobada y aplicada.' : 'No se pudo aprobar la solicitud.'
                ]);
                return;
            }

            $ok = $model->actualizarEstado((int)$codigoSolicitud, $estado, $comentario);

            $this->json($ok ? 200 : 400, [
                'ok' => $ok,
                'mensaje' => $ok ? 'Estado actualizado.' : 'No se pudo actualizar el estado.'
            ]);

        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_ACTUALIZAR_ESTADO_RESIDENCIA',
                'mensaje' => 'No se pudo actualizar el estado.',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}
