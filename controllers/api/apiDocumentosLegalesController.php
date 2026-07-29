<?php
// controllers/api/apiDocumentosLegalesController.php

declare(strict_types=1);

require_once __DIR__ . '/../../models/DocumentoLegal.php';

final class apiDocumentosLegalesController
{
    public function pendientes(): void
    {
        try {
            $codigoUsuario = $this->codigoUsuario();
            $model = new DocumentoLegal();
            $pendientes = $model->obtenerPendientesUsuario($codigoUsuario);

            $this->json(200, [
                'ok' => true,
                'requiere_aceptacion' => count($pendientes) > 0,
                'pendientes' => array_map([$this, 'resumirDocumento'], $pendientes),
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiDocumentosLegalesController][pendientes] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'No se pudo consultar el estado de los documentos legales.',
            ]);
        }
    }

    public function aceptarVigentes(): void
    {
        try {
            $codigoUsuario = $this->codigoUsuario();
            $data = $this->payload();

            $aceptaTerminos = $this->toBool($data['acepta_terminos'] ?? false);
            $aceptaPrivacidad = $this->toBool($data['acepta_privacidad'] ?? false);

            if (!$aceptaTerminos || !$aceptaPrivacidad) {
                $this->json(422, [
                    'ok' => false,
                    'error' => 'CONSENTIMIENTOS_REQUERIDOS',
                    'mensaje' => 'Debes aceptar los Términos y Condiciones y la Política de Privacidad para continuar.',
                ]);
            }

            $model = new DocumentoLegal();
            $origen = $model->usuarioTieneAlgunaAceptacion($codigoUsuario)
                ? 'nueva_version'
                : 'primer_ingreso';

            $registradas = $model->registrarAceptacionesVigentes(
                $codigoUsuario,
                $origen,
                $this->ipCliente(),
                (string)($_SERVER['HTTP_USER_AGENT'] ?? '')
            );

            $this->json(200, [
                'ok' => true,
                'mensaje' => 'Tus aceptaciones fueron registradas correctamente.',
                'registradas' => $registradas,
                'redirect' => rtrim((string)BASE_URL, '/') . '/MenuPrincipal',
            ]);
        } catch (Throwable $e) {
            error_log('[EV][apiDocumentosLegalesController][aceptarVigentes] ' . $e->getMessage());
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'No se pudieron registrar tus aceptaciones. Intenta nuevamente.',
            ]);
        }
    }

    private function codigoUsuario(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        $codigo = (int)($auth['codigo_usuario'] ?? 0);

        if ($codigo <= 0) {
            $this->json(401, [
                'ok' => false,
                'error' => 'UNAUTHORIZED',
                'mensaje' => 'Tu sesión no es válida.',
            ]);
        }

        return $codigo;
    }

    private function payload(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        $value = strtolower(trim((string)$value));
        return in_array($value, ['1', 'true', 'on', 'si', 'sí', 'yes'], true);
    }

    private function ipCliente(): ?string
    {
        $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
        return $ip !== '' ? substr($ip, 0, 45) : null;
    }

    private function resumirDocumento(array $doc): array
    {
        $tipo = (string)($doc['tipo'] ?? '');
        $ruta = $tipo === 'terminos_condiciones'
            ? '/legal/terminos-y-condiciones'
            : '/legal/politica-de-privacidad';

        return [
            'codigo_documento_legal' => (int)($doc['codigo_documento_legal'] ?? 0),
            'tipo' => $tipo,
            'titulo' => (string)($doc['titulo'] ?? ''),
            'version' => (string)($doc['version'] ?? ''),
            'texto_consentimiento' => (string)($doc['texto_consentimiento'] ?? ''),
            'ruta' => $ruta,
        ];
    }

    private function json(int $status, array $payload): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
