<?php
// controllers/UserController.php
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private function jsonResponse(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function isMultipart(): bool
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        return stripos($ct, 'multipart/form-data') !== false;
    }

    private function normalizeIntOrNull($v)
    {
        if ($v === null) return null;
        $v = trim((string)$v);
        if ($v === '') return null;
        $n = (int)$v;
        return $n > 0 ? $n : null;
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function normalizeDocumento(string $doc): string
    {
        $doc = trim($doc);
        $doc = preg_replace('/\s+/', '', $doc);
        return $doc;
    }

    private function validarEmail(string $email): bool
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function validarDocumento(string $doc): bool
    {
        // DNI (8) / CE (hasta 12 alfanum). Ajusta si manejas RUC u otros.
        $doc = trim($doc);
        if ($doc === '') return false;
        return (bool)preg_match('/^[0-9A-Za-z]{8,12}$/', $doc);
    }

    private function friendlyConflictMessage(string $errorCode): array
    {
        // Retorna [title, message]
        switch ($errorCode) {
            case 'EMAIL_INACTIVO':
                return [
                    'No se pudo registrar',
                    'Este correo ya está asociado a una cuenta inactiva/bloqueada. Si deseas recuperarla, comunícate con soporte para ayudarte con la reactivación.'
                ];
            case 'DOCUMENTO_INACTIVO':
                return [
                    'No se pudo registrar',
                    'Este documento ya está asociado a una cuenta inactiva/bloqueada. Si deseas recuperarla, comunícate con soporte para ayudarte con la reactivación.'
                ];
            case 'EMAIL_EXISTE':
                return [
                    'No se pudo registrar',
                    'Este correo ya está registrado. Inicia sesión o utiliza otro correo.'
                ];
            case 'DOCUMENTO_EXISTE':
                return [
                    'No se pudo registrar',
                    'Este documento ya está registrado. Verifica tus datos o comunícate con soporte.'
                ];
            case 'EMAIL_Y_DOCUMENTO_INACTIVO':
                return [
                    'No se pudo registrar',
                    'El correo y el documento ya están asociados a una cuenta inactiva/bloqueada. Comunícate con soporte para ayudarte con la reactivación.'
                ];
            case 'EMAIL_Y_DOCUMENTO_EXISTE':
                return [
                    'No se pudo registrar',
                    'El correo y el documento ya están registrados. Verifica tus datos o inicia sesión.'
                ];
            default:
                return [
                    'No se pudo registrar',
                    'No es posible completar el registro con los datos ingresados. Verifica la información e inténtalo nuevamente.'
                ];
        }
    }

    public function registrar()
    {
        $movedFileAbs = null; // para cleanup si falla BD luego del move

        try {
            // 1) Leer data según Content-Type
            if ($this->isMultipart()) {
                $data = [
                    'nombre' => trim($_POST['nombre'] ?? ''),
                    'documento' => $this->normalizeDocumento((string)($_POST['documento'] ?? '')),
                    'telefono' => trim($_POST['telefono'] ?? ''),
                    'email' => $this->normalizeEmail((string)($_POST['email'] ?? '')),
                    'clave' => (string)($_POST['clave'] ?? ''),
                    'codigo_rol' => (int)($_POST['codigo_rol'] ?? 2),

                    'tipo_conjunto' => trim($_POST['tipo_conjunto'] ?? ''),
                    'codigo_condominio' => $this->normalizeIntOrNull($_POST['codigo_condominio'] ?? null),
                    'codigo_urbanizacion' => $this->normalizeIntOrNull($_POST['codigo_urbanizacion'] ?? null),
                    'direccion' => trim($_POST['direccion'] ?? ''),
                ];
            } else {
                $json = json_decode(file_get_contents("php://input"), true);
                $data = is_array($json) ? $json : null;
                if (!$data) {
                    $this->jsonResponse(400, ["success" => false, "message" => "Datos inválidos"]);
                    return;
                }
                $data['email'] = $this->normalizeEmail((string)($data['email'] ?? ''));
                $data['documento'] = $this->normalizeDocumento((string)($data['documento'] ?? ''));
            }

            // 2) Validaciones mínimas server-side (email/documento obligatorios)
            $email = (string)($data['email'] ?? '');
            $documento = (string)($data['documento'] ?? '');

            if (!$this->validarEmail($email)) {
                $this->jsonResponse(422, ["success" => false, "message" => "Correo electrónico inválido"]);
                return;
            }

            if (!$this->validarDocumento($documento)) {
                $this->jsonResponse(422, ["success" => false, "message" => "Documento de identidad inválido"]);
                return;
            }

            // 3) Validaciones de residencia
            $tipo = (string)($data['tipo_conjunto'] ?? '');
            $direccion = trim((string)($data['direccion'] ?? ''));

            if ($tipo !== 'condominio' && $tipo !== 'urbanizacion') {
                $this->jsonResponse(422, ["success" => false, "message" => "Tipo de conjunto residencial inválido"]);
                return;
            }

            if ($tipo === 'condominio' && empty($data['codigo_condominio'])) {
                $this->jsonResponse(422, ["success" => false, "message" => "Debes seleccionar un condominio"]);
                return;
            }

            if ($tipo === 'urbanizacion' && empty($data['codigo_urbanizacion'])) {
                $this->jsonResponse(422, ["success" => false, "message" => "Debes seleccionar una urbanización"]);
                return;
            }

            if (strlen($direccion) < 5) {
                $this->jsonResponse(422, ["success" => false, "message" => "Dirección inválida"]);
                return;
            }

            // 4) Archivo obligatorio (tu caso actual: multipart)
            if (!$this->isMultipart()) {
                $this->jsonResponse(422, ["success" => false, "message" => "Debes enviar el comprobante de domicilio"]);
                return;
            }

            if (!isset($_FILES['comprobante_domicilio']) || ($_FILES['comprobante_domicilio']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $this->jsonResponse(422, ["success" => false, "message" => "Debes subir el comprobante de domicilio (recibo de servicio)"]);
                return;
            }

            $file = $_FILES['comprobante_domicilio'];
            $maxBytes = 2 * 1024 * 1024;

            if ((int)($file['size'] ?? 0) > $maxBytes) {
                $this->jsonResponse(422, ["success" => false, "message" => "El comprobante supera el tamaño máximo permitido (2 MB)"]);
                return;
            }

            $originalName = (string)($file['name'] ?? '');
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];

            if (!in_array($ext, $allowedExt, true)) {
                $this->jsonResponse(422, ["success" => false, "message" => "Formato no permitido. Sube JPG, PNG o PDF"]);
                return;
            }

            // Validación MIME (defensiva)
            $allowedMime = ['image/jpeg', 'image/png', 'application/pdf'];
            $tmpPath = (string)($file['tmp_name'] ?? '');

            $mime = null;
            if ($tmpPath && function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $mime = finfo_file($finfo, $tmpPath);
                    finfo_close($finfo);
                }
            }
            if ($mime && !in_array($mime, $allowedMime, true)) {
                $this->jsonResponse(422, ["success" => false, "message" => "Tipo de archivo inválido. Sube JPG, PNG o PDF"]);
                return;
            }

            // 5) ✅ PREVALIDACIÓN: email/documento (evita 500 por duplicate y evita mover archivo si hay conflicto)
            $userModel = new User();

            if (method_exists($userModel, 'verificarConflictoRegistro')) {
                $conf = $userModel->verificarConflictoRegistro($email, $documento);

                if (!empty($conf['has_conflict'])) {
                    $errorCode = (string)($conf['error'] ?? 'CONFLICTO');
                    $friendly = $this->friendlyConflictMessage($errorCode);
                    $title = $friendly[0];
                    $msg   = $friendly[1];

                    $this->jsonResponse(409, [
                        "success" => false,
                        "error"   => $errorCode,
                        "title"   => $title,
                        "message" => $msg
                    ]);
                    return;
                }
            }

            // 6) Guardar archivo (recién aquí, cuando no hay conflicto)
            $uploadRelDir = 'resources/uploads/comprobantes';
            $projectRoot  = realpath(__DIR__ . '/..'); // /controllers -> / (tu raíz app)
            $uploadAbsDir = $projectRoot . DIRECTORY_SEPARATOR . $uploadRelDir;

            if (!is_dir($uploadAbsDir)) {
                @mkdir($uploadAbsDir, 0775, true);
            }

            if (!is_dir($uploadAbsDir) || !is_writable($uploadAbsDir)) {
                $this->jsonResponse(500, ["success" => false, "message" => "No se pudo guardar el comprobante. Carpeta no disponible."]);
                return;
            }

            $safeName = 'comp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destAbs  = $uploadAbsDir . DIRECTORY_SEPARATOR . $safeName;

            if (!move_uploaded_file($tmpPath, $destAbs)) {
                $this->jsonResponse(500, ["success" => false, "message" => "No se pudo guardar el comprobante. Intenta nuevamente."]);
                return;
            }

            $movedFileAbs = $destAbs;
            $data['comprobante_domicilio'] = $uploadRelDir . '/' . $safeName;

            // 7) Registrar en BD
            $ok = $userModel->registrar($data);

            if ($ok) {
                $this->jsonResponse(200, ["success" => true, "message" => "Usuario registrado con éxito"]);
                return;
            }

            // Si no ok (raro), limpia archivo
            if ($movedFileAbs && is_file($movedFileAbs)) @unlink($movedFileAbs);
            $this->jsonResponse(500, ["success" => false, "message" => "No se pudo registrar el usuario"]);
        }
        catch (PDOException $e) {
            // limpia archivo si ya se movió
            if ($movedFileAbs && is_file($movedFileAbs)) @unlink($movedFileAbs);

            // Fallback por si BD aún lanza duplicate / constraint
            if ((string)$e->getCode() === '23000') {
                $msg = (string)$e->getMessage();
                $errorCode = 'CONFLICTO';

                // MySQL duplicate key hints
                if (stripos($msg, "for key 'email'") !== false || stripos($msg, 'for key `email`') !== false) {
                    $errorCode = 'EMAIL_EXISTE';
                } elseif (stripos($msg, "for key 'documento'") !== false || stripos($msg, 'for key `documento`') !== false) {
                    $errorCode = 'DOCUMENTO_EXISTE';
                }

                $friendly = $this->friendlyConflictMessage($errorCode);
                $this->jsonResponse(409, [
                    "success" => false,
                    "error"   => $errorCode,
                    "title"   => $friendly[0],
                    "message" => $friendly[1]
                ]);
                return;
            }

            $this->jsonResponse(500, ["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
        catch (Throwable $e) {
            if ($movedFileAbs && is_file($movedFileAbs)) @unlink($movedFileAbs);
            $this->jsonResponse(500, ["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }
}
