<?php
// controllers/api/apiSoporteServiciosController.php

declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/ServicioSoporte.php';

final class apiSoporteServiciosController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function auth(): array
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return is_array($auth) ? $auth : [];
    }

    private function exigirSoporte(): int
    {
        $auth = $this->auth();
        $usuario = (int)($auth['codigo_usuario'] ?? 0);
        $rolId = (int)($auth['codigo_rol'] ?? 0);
        $rolNombre = strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
        $adminId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        if ($usuario <= 0) {
            $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Tu sesión no es válida.']);
            exit;
        }
        if (!in_array($rolId, [$adminId, $soporteId], true) && !in_array($rolNombre, ['admin','administrador','soporte'], true)) {
            $this->json(403, ['ok' => false, 'error' => 'ROL_NO_AUTORIZADO', 'mensaje' => 'Acceso restringido al equipo de soporte.']);
            exit;
        }
        return $usuario;
    }

    private function input(): array
    {
        if (!empty($_POST) && is_array($_POST)) return $_POST;
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function status(array $res): int
    {
        if (($res['ok'] ?? false) === true) return 200;
        return match ((string)($res['error'] ?? '')) {
            'PARAMETROS_INVALIDOS','ACCION_INVALIDA','RESOLUCION_REQUERIDA' => 400,
            'INCIDENCIA_NO_ENCONTRADA' => 404,
            'CASO_CERRADO' => 409,
            default => 500,
        };
    }

    public function listar(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }
        $this->exigirSoporte();
        $res = (new ServicioSoporte())->listar([
            'estado' => $_GET['estado'] ?? 'abiertas',
            'buscar' => $_GET['buscar'] ?? '',
            'page' => $_GET['page'] ?? 1,
            'size' => $_GET['size'] ?? 20,
            'tipo_conjunto' => $_GET['tipo_conjunto'] ?? '',
            'codigo_comunidad' => $_GET['codigo_comunidad'] ?? 0,
        ]);
        $this->json($this->status($res), $res);
    }

    public function resumen(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }
        $this->exigirSoporte();
        $res = (new ServicioSoporte())->resumen([
            'tipo_conjunto' => $_GET['tipo_conjunto'] ?? '',
            'codigo_comunidad' => $_GET['codigo_comunidad'] ?? 0,
        ]);
        $this->json($this->status($res), $res);
    }

    public function detalle(int $codigoIncidencia): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }
        $this->exigirSoporte();
        $res = (new ServicioSoporte())->detalle($codigoIncidencia);
        $this->json($this->status($res), $res);
    }

    public function resolver(int $codigoIncidencia): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }
        $usuario = $this->exigirSoporte();
        $input = $this->input();
        $res = (new ServicioSoporte())->resolver(
            $codigoIncidencia,
            $usuario,
            (string)($input['accion'] ?? ''),
            (string)($input['comentario'] ?? $input['resolucion'] ?? '')
        );
        $this->json($this->status($res), $res);
    }
}
