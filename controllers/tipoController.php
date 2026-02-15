<?php
require_once __DIR__ . '/../models/TipoModel.php';

class tipoController
{
    private TipoModel $model;

    public function __construct()
    {
        $this->model = new TipoModel();
    }

    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // GET /tipos
    public function listar(): void
    {
        try {
            $tipo = $this->model->listarTipo();
            $this->json(200, ['ok' => true, 'data' => $tipo]);
        } catch (Exception $e) {
            $this->json(500, ['ok' => false, 'mensaje' => 'Error al listar tipos', 'error' => $e->getMessage()]);
        }
    }

    // GET /tipos/{id}/categoria_grupo
    public function listarCategoria_grupo($tipoId): void
    {
        try {
            $tipoId = (int)$tipoId;
            if ($tipoId <= 0) {
                $this->json(400, ['ok' => false, 'mensaje' => 'Tipo inválido']);
                return;
            }

            $data = $this->model->listarCategoria_grupo($tipoId);
            $this->json(200, ['ok' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->json(500, ['ok' => false, 'mensaje' => 'Error al listar categorías', 'error' => $e->getMessage()]);
        }
    }
}
