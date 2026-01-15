<?php
// controllers/api/apiNotificacionesResidenciaController.php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/config.php';
require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';
require_once __DIR__ . '/../../models/Notificacion.php';

final class apiNotificacionesResidenciaController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    private function codigoUsuarioAuth(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function guardarArchivoComprobante(array $file, int $codigoUsuario): string
    {
        $uploadRel = 'resources/uploads/comprobantes';
        $uploadAbs = __DIR__ . '/../../' . $uploadRel;

        if (!is_dir($uploadAbs)) {
            @mkdir($uploadAbs, 0775, true);
        }

        $name = (string)($file['name'] ?? '');
        $tmp  = (string)($file['tmp_name'] ?? '');
        $err  = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $size = (int)($file['size'] ?? 0);

        if ($err !== UPLOAD_ERR_OK || !$tmp) {
            throw new RuntimeException('No se pudo leer el archivo subido.');
        }

        if ($size <= 0 || $size > (5 * 1024 * 1024)) {
            throw new RuntimeException('El archivo supera 5MB o es inválido.');
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf','jpg','jpeg','png'], true)) {
            throw new RuntimeException('Archivo no permitido. Solo PDF, JPG o PNG.');
        }

        $stamp = date('Ymd_His');
        $rand  = bin2hex(random_bytes(4));
        $final = "comp_res_{$codigoUsuario}_{$stamp}_{$rand}.{$ext}";
        $dest  = rtrim($uploadAbs, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $final;

        if (!move_uploaded_file($tmp, $dest)) {
            throw new RuntimeException('No se pudo guardar el archivo.');
        }

        return $uploadRel . '/' . $final; // ruta relativa guardada en BD
    }

    public function reenviar($codigoSolicitud): void
    {
        $u  = $this->codigoUsuarioAuth();
        $id = (int)$codigoSolicitud; // ✅ ESTE ES EL ID ORIGINAL observado/rechazado

        if ($u <= 0) {
            $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
            return;
        }
        if ($id <= 0) {
            $this->json(422, ['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }

        $file = $_FILES['documento_domicilio'] ?? null;
        if (!$file || !is_array($file)) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Adjunta el comprobante para reenviar.']);
            return;
        }

        $model = new UsuarioResidenciaSolicitud();
        $sol = $model->obtenerSolicitud($id);

        if (!$sol) {
            $this->json(404, ['ok' => false, 'mensaje' => 'Solicitud no encontrada.']);
            return;
        }

        if ((int)$sol['codigo_usuario'] !== $u) {
            $this->json(403, ['ok' => false, 'mensaje' => 'Acceso restringido.']);
            return;
        }

        $estado = strtolower((string)($sol['estado'] ?? ''));
        if (!in_array($estado, ['observada','rechazada'], true)) {
            $this->json(422, ['ok' => false, 'mensaje' => 'Solo puedes reenviar solicitudes observadas o rechazadas.']);
            return;
        }

        try {
            $ruta  = $this->guardarArchivoComprobante($file, $u);
            $newId = $model->reenviarDesdeObservadaRechazada($id, $ruta);

            if ($newId <= 0) {
                $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo reenviar.']);
                return;
            }

            // ✅ FIX RAÍZ: cerrar la notificación ligada a la solicitud original
            $n = new Notificacion();
            $cerradas = $n->marcarLeidasPorReferencia($u, 'residencia', $id);

            $this->json(200, [
                'ok' => true,
                'mensaje' => 'Solicitud reenviada. Queda pendiente de revisión.',
                'data' => [
                    'codigo_solicitud' => $newId,
                    'notificaciones_cerradas' => $cerradas
                ]
            ]);
        } catch (Throwable $e) {
            $this->json(422, ['ok' => false, 'mensaje' => $e->getMessage()]);
        }
    }
}
