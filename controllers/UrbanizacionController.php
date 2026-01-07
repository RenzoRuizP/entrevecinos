<?php
// controllers/UrbanizacionController.php
require_once __DIR__ . '/../models/Urbanizacion.php';

class UrbanizacionController
{
    public function listar()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $model = new Urbanizacion();

            $dist = isset($_GET['distrito']) ? (int)$_GET['distrito'] : 0;

            if ($dist > 0) {
                $lista = $model->listarPorDistrito($dist);
            } else {
                $lista = $model->listarActivas();
            }

            echo json_encode($lista ?: []);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => 'ERROR_URBANIZACIONES',
                'mensaje' => 'No se pudo listar urbanizaciones.',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}
