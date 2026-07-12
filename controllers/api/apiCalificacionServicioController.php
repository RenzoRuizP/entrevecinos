<?php
// controllers/api/apiCalificacionServicioController.php

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/CalificacionServicio.php';

final class apiCalificacionServicioController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function usuario(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $id = (int)($auth['codigo_usuario'] ?? 0);
        if ($id <= 0) {
            $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Tu sesión no es válida.']);
            exit;
        }
        return $id;
    }

    private function input(): array
    {
        if (!empty($_POST) && is_array($_POST)) {
            return $_POST;
        }
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function statusError(string $error): int
    {
        return match ($error) {
            'PARAMETROS_INVALIDOS', 'PUNTAJE_INVALIDO', 'COMENTARIO_REQUERIDO', 'COMENTARIO_DEMASIADO_LARGO' => 400,
            'CALIFICACION_NO_ENCONTRADA' => 404,
            'SERVICIO_NO_COMPLETADO', 'CALIFICACION_NO_DISPONIBLE' => 409,
            default => 500,
        };
    }

    public function listarPendientes(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }
        $res = (new CalificacionServicio())->listarPendientesUsuario($this->usuario());
        $this->json(($res['ok'] ?? false) ? 200 : 500, $res);
    }

    public function obtenerPorSolicitud(int $codigoSolicitud): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }
        $res = (new CalificacionServicio())->obtenerPorSolicitudUsuario($codigoSolicitud, $this->usuario());
        $this->json(($res['ok'] ?? false) ? 200 : $this->statusError((string)($res['error'] ?? '')), $res);
    }

    public function enviar(int $codigoCalificacion): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        $input = $this->input();
        $etiquetas = $input['etiquetas'] ?? [];
        if (!is_array($etiquetas)) {
            $etiquetas = [];
        }

        $res = (new CalificacionServicio())->enviar(
            $codigoCalificacion,
            $this->usuario(),
            (int)($input['puntaje'] ?? 0),
            $etiquetas,
            (string)($input['comentario'] ?? '')
        );

        $this->json(($res['ok'] ?? false) ? 200 : $this->statusError((string)($res['error'] ?? '')), $res);
    }
}
