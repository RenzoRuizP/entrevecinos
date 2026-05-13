<?php
declare(strict_types=1);

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

    private function obtenerTipoPublicacionQuery(): ?string
    {
        $tipoPublicacion = strtolower(trim((string)($_GET['tipo_publicacion'] ?? '')));
        if (in_array($tipoPublicacion, ['producto', 'servicio'], true)) {
            return $tipoPublicacion;
        }

        $codigoGrupo = isset($_GET['codigo_grupo']) ? (int)$_GET['codigo_grupo'] : 0;
        if ($codigoGrupo === 1) return 'producto';
        if ($codigoGrupo === 2) return 'servicio';

        return null;
    }

    // GET /tipos
    // GET /tipos?tipo_publicacion=producto
    // GET /tipos?tipo_publicacion=servicio
    public function listar(): void
    {
        try {
            $tipoPublicacion = $this->obtenerTipoPublicacionQuery();
            $tipo = $this->model->listarTipo($tipoPublicacion);

            $this->json(200, [
                'ok' => true,
                'tipo_publicacion' => $tipoPublicacion,
                'data' => $tipo,
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'Error al listar tipos',
                'error' => $e->getMessage(),
            ]);
        }
    }

    // GET /tipos/{id}/categoria_grupo
    // GET /tipos/{id}/categoria_grupo?tipo_publicacion=producto
    // GET /tipos/{id}/categoria_grupo?tipo_publicacion=servicio
    public function listarCategoria_grupo($tipoId): void
    {
        try {
            $tipoId = (int)$tipoId;
            if ($tipoId <= 0) {
                $this->json(400, ['ok' => false, 'mensaje' => 'Tipo inválido']);
                return;
            }

            $tipoPublicacion = $this->obtenerTipoPublicacionQuery();
            $data = $this->model->listarCategoria_grupo($tipoId, $tipoPublicacion);

            $this->json(200, [
                'ok' => true,
                'tipo_publicacion' => $tipoPublicacion,
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'Error al listar categorías',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
