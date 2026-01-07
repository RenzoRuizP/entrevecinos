<?php
// controllers/CondominioController.php
require_once __DIR__ . '/../models/CondominioModel.php';

class CondominioController
{
    private $model;

    public function __construct()
    {
        $this->model = new CondominioModel();
    }

    // GET /condominios  (opcional ?distrito=ID)
    public function listar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $dist = isset($_GET['distrito']) ? (int)$_GET['distrito'] : 0;

            if ($dist > 0) {
                $data = $this->model->listarPorDistrito($dist);
            } else {
                $data = $this->model->listarCondominios();
            }

            echo json_encode($data ?: []);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => 'ERROR_CONDOMINIOS',
                'mensaje' => 'No se pudo listar condominios.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // GET /condominios/{id}/torres
    public function listarTorres($condominioId)
    {
        header('Content-Type: application/json; charset=utf-8');
        $data = $this->model->listarTorres((int)$condominioId);
        echo json_encode($data ?: []);
    }

    // GET /torres/{id}/departamentos
    public function listarDepartamentos($torreId)
    {
        header('Content-Type: application/json; charset=utf-8');
        $data = $this->model->listarDepartamentos((int)$torreId);
        echo json_encode($data ?: []);
    }
}
