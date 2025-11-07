<?php
require_once __DIR__ . '/../models/TipoModel.php';

class tipoController {
    private $model;

    public function __construct() {
        $this->model = new TipoModel();
    }

    // GET /tipos
    public function listar() {
        $tipo = $this->model->listarTipo();
        header('Content-Type: application/json');
        echo json_encode($tipo);
    }

    // GET /tipos/{id}/categoria_grupo
    public function listarCategoria_grupo($tipoId) {
        $data = $this->model->listarCategoria_grupo((int)$tipoId);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
