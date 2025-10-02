<?php
require_once __DIR__ . '/../models/CondominioModel.php';

class CondominioController {

    private $model;

    public function __construct() {
        $this->model = new CondominioModel();
    }

    public function listar() {
        try {
            header('Content-Type: application/json; charset=utf-8');
            $data = $this->model->listarCondominios();
            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        } catch (Exception $e) {
            error_log("Error en listar: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Ocurrió un error al obtener los condominios."
            ]);
        }
    }

}
