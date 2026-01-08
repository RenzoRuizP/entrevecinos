<?php
// controllers/api/UsuarioResidenciaSolicitudController.php

require_once __DIR__ . '/../../models/SesionJWT.php';
require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';

class UsuarioResidenciaSolicitudController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    public function solicitarCambioResidencia()
    {
        try {
            if (empty($_COOKIE['auth_token'])) {
                $this->json(401, ['ok' => false, 'mensaje' => 'TOKEN_NO_ENCONTRADO']);
                return;
            }

            $rTok = SesionJWT::verificarTokenDetallado($_COOKIE['auth_token']);
            if (!$rTok['ok'] || empty($rTok['data']['codigo_usuario'])) {
                $this->json(401, ['ok' => false, 'mensaje' => 'TOKEN_INVALIDO_O_EXPIRADO']);
                return;
            }

            $codigoUsuario = (int)$rTok['data']['codigo_usuario'];

            $tipo = strtolower(trim((string)($_POST['tipo_conjunto'] ?? '')));
            $direccion = trim((string)($_POST['direccion'] ?? ''));

            if (!in_array($tipo, ['condominio','urbanizacion'], true)) {
                $this->json(422, ['ok' => false, 'mensaje' => 'TIPO_CONJUNTO_INVALIDO']);
                return;
            }
            if ($direccion === '') {
                $this->json(422, ['ok' => false, 'mensaje' => 'DIRECCION_REQUERIDA']);
                return;
            }

            $codCondominio   = $_POST['codigo_condominio'] ?? null;
            $codUrbanizacion = $_POST['codigo_urbanizacion'] ?? null;
            $codDepartamento = $_POST['codigo_departamento'] ?? null;

            if ($tipo === 'condominio') {
                if (!$codCondominio || !ctype_digit((string)$codCondominio)) {
                    $this->json(422, ['ok' => false, 'mensaje' => 'CODIGO_CONDOMINIO_INVALIDO']);
                    return;
                }
                if (!$codDepartamento || !ctype_digit((string)$codDepartamento)) {
                    $this->json(422, ['ok' => false, 'mensaje' => 'DEPARTAMENTO_INVALIDO']);
                    return;
                }
            } else {
                if (!$codUrbanizacion || !ctype_digit((string)$codUrbanizacion)) {
                    $this->json(422, ['ok' => false, 'mensaje' => 'CODIGO_URBANIZACION_INVALIDO']);
                    return;
                }
            }

            // Upload obligatorio
            if (empty($_FILES['comprobante']) || !isset($_FILES['comprobante']['tmp_name'])) {
                $this->json(422, ['ok' => false, 'mensaje' => 'COMPROBANTE_REQUERIDO']);
                return;
            }

            $f = $_FILES['comprobante'];
            if ((int)$f['error'] !== UPLOAD_ERR_OK) {
                $this->json(422, ['ok' => false, 'mensaje' => 'ERROR_UPLOAD', 'detalle' => (int)$f['error']]);
                return;
            }

            $max = 5 * 1024 * 1024; // 5MB
            if ((int)$f['size'] > $max) {
                $this->json(422, ['ok' => false, 'mensaje' => 'ARCHIVO_SUPERA_5MB']);
                return;
            }

            $original = (string)($f['name'] ?? 'archivo');
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $allowed = ['pdf','jpg','jpeg','png'];
            if (!in_array($ext, $allowed, true)) {
                $this->json(422, ['ok' => false, 'mensaje' => 'TIPO_ARCHIVO_NO_PERMITIDO']);
                return;
            }

            // Guardar archivo
            $folderAbs = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'comprobantes_domicilio';
            if (!is_dir($folderAbs)) {
                @mkdir($folderAbs, 0775, true);
            }

            if (!is_dir($folderAbs)) {
                $this->json(500, ['ok' => false, 'mensaje' => 'NO_SE_PUDO_CREAR_CARPETA_UPLOAD']);
                return;
            }

            $safeBase = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', pathinfo($original, PATHINFO_FILENAME));
            $stamp = date('Ymd_His');
            $rand  = bin2hex(random_bytes(4));
            $fileName = "UR_{$codigoUsuario}_{$stamp}_{$rand}_{$safeBase}.{$ext}";

            $destAbs = $folderAbs . DIRECTORY_SEPARATOR . $fileName;

            if (!move_uploaded_file($f['tmp_name'], $destAbs)) {
                $this->json(500, ['ok' => false, 'mensaje' => 'NO_SE_PUDO_GUARDAR_ARCHIVO']);
                return;
            }

            // Ruta relativa (para consumir desde web)
            $rutaRel = 'resources/uploads/comprobantes_domicilio/' . $fileName;

            $model = new UsuarioResidenciaSolicitud();
            $idSol = $model->crear($codigoUsuario, [
                'tipo_conjunto' => $tipo,
                'codigo_condominio' => $tipo === 'condominio' ? (int)$codCondominio : null,
                'codigo_urbanizacion' => $tipo === 'urbanizacion' ? (int)$codUrbanizacion : null,
                'codigo_departamento' => $tipo === 'condominio' ? (int)$codDepartamento : null,
                'direccion' => $direccion,
            ], $rutaRel);

            $this->json(200, [
                'ok' => true,
                'mensaje' => 'Solicitud enviada. Un administrador debe aprobar tu cambio de residencia.',
                'codigo_solicitud' => $idSol
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
                'mensaje' => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}
