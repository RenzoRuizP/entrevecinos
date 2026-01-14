<?php
// controllers/api/apiSoporteResidenciasController.php

declare(strict_types=1);

require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';

class apiSoporteResidenciasController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    private function isAdmin(): bool
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $rol  = (int)($auth['codigo_rol'] ?? 0);
        return $rol === (int)EV_ADMIN_ROLE_ID;
    }

    public function listar(): void
    {
        if (!$this->isAdmin()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $model = new UsuarioResidenciaSolicitud();

        $filtros = [
            'estado' => $_GET['estado'] ?? 'pendiente', // pendiente|observada|aprobada|rechazada|all
            'tipo'   => $_GET['tipo'] ?? '',            // condominio|urbanizacion|''
            'codigo' => $_GET['codigo'] ?? 0,
            'q'      => $_GET['q'] ?? '',
            'page'   => $_GET['page'] ?? 1,
            'size'   => $_GET['size'] ?? 10,
        ];

        $res = $model->listarSoporte($filtros);
        $this->json(200, $res);
    }

    public function actualizarEstado($codigoSolicitud): void
    {
        if (!$this->isAdmin()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $id = (int)$codigoSolicitud;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }

        $body = json_decode(file_get_contents('php://input') ?: '[]', true);
        if (!is_array($body)) $body = [];

        $estado = strtolower(trim((string)($body['estado'] ?? '')));

        // ✅ Compatible: acepta comentario o comentario_admin
        $comentario = trim((string)($body['comentario'] ?? ($body['comentario_admin'] ?? '')));

        if (!in_array($estado, ['pendiente','observada','aprobada','rechazada'], true)) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Estado inválido.']);
            return;
        }

        $model = new UsuarioResidenciaSolicitud();
        $ok = $model->actualizarEstadoSoporte($id, $estado, $comentario);

        $this->json(200, [
            'ok' => $ok,
            'mensaje' => $ok ? 'Solicitud actualizada.' : 'No se pudo actualizar.'
        ]);
    }
}
