<?php
// controllers/UbigeoController.php
require_once __DIR__ . '/../models/Ubigeo.php';

class UbigeoController
{
    private function json(int $status, $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    // GET /ubigeo/departamentos
    public function departamentos(): void
    {
        try {
            $m = new Ubigeo();
            $this->json(200, $m->listarDepartamentos());
        } catch (Exception $e) {
            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_UBIGEO_DEPARTAMENTOS',
                'mensaje' => 'No se pudo listar departamentos.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // GET /ubigeo/departamentos/{dep}/provincias
    public function provincias($codigoDepartamento): void
    {
        $dep = (int)$codigoDepartamento;
        if ($dep <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Departamento inválido.']);
            return;
        }

        try {
            $m = new Ubigeo();
            $this->json(200, $m->listarProvinciasPorDepartamento($dep));
        } catch (Exception $e) {
            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_UBIGEO_PROVINCIAS',
                'mensaje' => 'No se pudo listar provincias.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // GET /ubigeo/provincias/{prov}/distritos
    public function distritos($codigoProvincia): void
    {
        $prov = (int)$codigoProvincia;
        if ($prov <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Provincia inválida.']);
            return;
        }

        try {
            $m = new Ubigeo();
            $this->json(200, $m->listarDistritosPorProvincia($prov));
        } catch (Exception $e) {
            $this->json(500, [
                'ok' => false,
                'error' => 'ERROR_UBIGEO_DISTRITOS',
                'mensaje' => 'No se pudo listar distritos.',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}
