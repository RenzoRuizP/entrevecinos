<?php
// controllers/api/apiSoporteLibroReclamacionesController.php

declare(strict_types=1);

require_once __DIR__ . '/../../models/LibroReclamacion.php';

final class apiSoporteLibroReclamacionesController
{
    private function json(int $status, array $payload): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function exigirSoporte(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $usuario = (int)($auth['codigo_usuario'] ?? 0);
        $rolId = (int)($auth['codigo_rol'] ?? 0);
        $rolNombre = strtolower(trim((string)($auth['rol'] ?? $auth['nombre_rol'] ?? '')));
        $adminId = defined('EV_ADMIN_ROLE_ID') ? (int)EV_ADMIN_ROLE_ID : 1;
        $soporteId = defined('EV_SOPORTE_ROLE_ID') ? (int)EV_SOPORTE_ROLE_ID : 3;

        if ($usuario <= 0) {
            $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED', 'mensaje' => 'Tu sesión no es válida.']);
        }
        if (!in_array($rolId, [$adminId, $soporteId], true)
            && !in_array($rolNombre, ['admin', 'administrador', 'soporte'], true)) {
            $this->json(403, ['ok' => false, 'error' => 'ROL_NO_AUTORIZADO', 'mensaje' => 'Acceso restringido al equipo de soporte.']);
        }
        return $usuario;
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
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function status(array $res): int
    {
        if (($res['ok'] ?? false) === true) {
            return 200;
        }
        return match ((string)($res['error'] ?? '')) {
            'PARAMETROS_INVALIDOS', 'RESPUESTA_REQUERIDA', 'RESPUESTA_MUY_LARGA' => 422,
            'NO_ENCONTRADO' => 404,
            default => 500,
        };
    }

    public function resumen(): void
    {
        $this->exigirSoporte();
        $res = (new LibroReclamacion())->resumen();
        $this->json($this->status($res), $res);
    }

    public function listar(): void
    {
        $this->exigirSoporte();
        $res = (new LibroReclamacion())->listar([
            'estado' => $_GET['estado'] ?? 'pendientes',
            'tipo' => $_GET['tipo'] ?? 'all',
            'buscar' => $_GET['buscar'] ?? '',
            'page' => $_GET['page'] ?? 1,
            'size' => $_GET['size'] ?? 20,
        ]);
        $this->json($this->status($res), $res);
    }

    public function detalle(int $codigo): void
    {
        $this->exigirSoporte();
        $res = (new LibroReclamacion())->detalle($codigo);
        $this->json($this->status($res), $res);
    }

    public function atender(int $codigo): void
    {
        $usuario = $this->exigirSoporte();
        $input = $this->input();
        $res = (new LibroReclamacion())->atender(
            $codigo,
            $usuario,
            (string)($input['estado'] ?? ''),
            (string)($input['respuesta'] ?? ''),
            (string)($input['medio_respuesta'] ?? 'correo')
        );

        if (($res['ok'] ?? false) === true) {
            $mail = $this->enviarRespuestaPorCorreo($res['data'] ?? []);
            $res['correo_enviado'] = $mail;
            if (!$mail && in_array((string)($res['data']['estado'] ?? ''), ['respondido', 'cerrado'], true)) {
                $res['advertencia'] = 'La respuesta quedó publicada para la consulta en línea, pero el servidor no confirmó el envío por correo. Verifica la configuración de correo y realiza el envío manual si corresponde.';
            }
        }

        $this->json($this->status($res), $res);
    }

    private function enviarRespuestaPorCorreo(array $data): bool
    {
        $estado = (string)($data['estado'] ?? '');
        $respuesta = trim((string)($data['respuesta'] ?? ''));
        $destino = trim((string)($data['correo'] ?? ''));
        if (!in_array($estado, ['respondido', 'cerrado'], true)
            || $respuesta === ''
            || !filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $legalConfig = require __DIR__ . '/../../Config/documentos_legales.php';
        $r = $legalConfig['responsable'] ?? [];
        $from = trim((string)($r['correo_reclamos'] ?? 'reclamos@entrevecinos.pe'));
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $numero = (string)($data['numero_hoja'] ?? '');
        $consumidor = (string)($data['consumidor'] ?? '');
        $subject = 'Respuesta del Libro de Reclamaciones - ' . $numero;
        $body = "Hola {$consumidor},\n\n"
            . "Entre Vecinos registró una respuesta para la hoja {$numero}:\n\n"
            . $respuesta . "\n\n"
            . "También puedes consultar el estado en https://www.entrevecinos.pe/libro-de-reclamaciones/consulta.php\n\n"
            . "Atentamente,\nEntre Vecinos\n";
        $headers = [
            'From: Entre Vecinos <' . $from . '>',
            'Reply-To: ' . $from,
            'Content-Type: text/plain; charset=UTF-8',
            'MIME-Version: 1.0',
        ];

        return @mail(
            $destino,
            '=?UTF-8?B?' . base64_encode($subject) . '?=',
            $body,
            implode("\r\n", $headers)
        );
    }
}
