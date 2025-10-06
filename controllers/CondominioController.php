<?php
require_once __DIR__ . '/../models/CondominioModel.php';

class CondominioController {
    private $model;

    public function __construct() {
        $this->model = new CondominioModel();
    }

    // GET /condominios
    public function listar() {
        $data = $this->model->listarCondominios();
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // GET /condominios/{id}/torres
    public function listarTorres($condominioId) {
        $data = $this->model->listarTorres($condominioId);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // GET /torres/{id}/departamentos
    public function listarDepartamentos($torreId) {
        $data = $this->model->listarDepartamentos($torreId);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
