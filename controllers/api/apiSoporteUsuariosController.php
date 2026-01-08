<?php
// controllers/api/apiSoporteUsuariosController.php

require_once __DIR__ . '/../../models/UsuarioSoporte.php';

class apiSoporteUsuariosController
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

    public function listar()
    {
        if (!$this->isAdmin()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $model = new UsuarioSoporte();

        $filtros = [
            'estado' => $_GET['estado'] ?? null,
            'tipo'   => $_GET['tipo'] ?? '',
            'codigo' => $_GET['codigo'] ?? 0,
            'q'      => $_GET['q'] ?? '',
            'page'   => $_GET['page'] ?? 1,
            'size'   => $_GET['size'] ?? 10,
        ];

        $res = $model->listar($filtros);
        $this->json(200, $res);
    }

    public function actualizarEstado($codigoUsuario)
    {
        if (!$this->isAdmin()) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $id = (int)$codigoUsuario;
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true);
        $estado = (int)($body['estado'] ?? 0);

        if (!in_array($estado, [0,1,2], true)) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Estado inválido.']);
            return;
        }

        $model = new UsuarioSoporte();
        $ok = $model->actualizarEstado($id, $estado);

        $this->json(200, [
            'ok' => $ok,
            'mensaje' => $ok ? 'Estado actualizado.' : 'No se pudo actualizar.'
        ]);
    }
}
