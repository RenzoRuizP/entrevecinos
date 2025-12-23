<?php
// controllers/UserController.php
require_once __DIR__ . '/../models/User.php';

class UserController {

    private function jsonResponse(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
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

    public function registrar() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // 1) Leer data según Content-Type
            if ($this->isMultipart()) {
                $data = [
                    'nombre' => trim($_POST['nombre'] ?? ''),
                    'documento' => trim($_POST['documento'] ?? ''),
                    'telefono' => trim($_POST['telefono'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
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
            }

            // 2) Validaciones mínimas server-side
            $tipo = $data['tipo_conjunto'] ?? '';
            $direccion = trim($data['direccion'] ?? '');

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

            // 3) Archivo obligatorio (solo aplica para multipart; que es tu caso actual)
            if (!$this->isMultipart()) {
                $this->jsonResponse(422, ["success" => false, "message" => "Debes enviar el comprobante de domicilio"]);
                return;
            }

            if (!isset($_FILES['comprobante_domicilio']) || $_FILES['comprobante_domicilio']['error'] !== UPLOAD_ERR_OK) {
                $this->jsonResponse(422, ["success" => false, "message" => "Debes subir el comprobante de domicilio (recibo de servicio)"]);
                return;
            }

            $file = $_FILES['comprobante_domicilio'];
            $maxBytes = 2 * 1024 * 1024;

            if ((int)$file['size'] > $maxBytes) {
                $this->jsonResponse(422, ["success" => false, "message" => "El comprobante supera el tamaño máximo permitido (2 MB)"]);
                return;
            }

            $originalName = (string)$file['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExt = ['jpg','jpeg','png','pdf'];
            if (!in_array($ext, $allowedExt, true)) {
                $this->jsonResponse(422, ["success" => false, "message" => "Formato no permitido. Sube JPG, PNG o PDF"]);
                return;
            }

            // Validación MIME (defensiva)
            $allowedMime = ['image/jpeg','image/png','application/pdf'];
            $tmpPath = $file['tmp_name'];

            $mime = null;
            if (function_exists('finfo_open')) {
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

            // 4) Guardar archivo
            $uploadRelDir = 'resources/uploads/comprobantes';
            $uploadAbsDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . $uploadRelDir;

            if (!is_dir($uploadAbsDir)) {
                @mkdir($uploadAbsDir, 0775, true);
            }

            if (!is_dir($uploadAbsDir) || !is_writable($uploadAbsDir)) {
                $this->jsonResponse(500, ["success" => false, "message" => "No se pudo guardar el comprobante. Carpeta no disponible."]);
                return;
            }

            // Nombre seguro
            $safeName = 'comp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destAbs = $uploadAbsDir . DIRECTORY_SEPARATOR . $safeName;

            if (!move_uploaded_file($tmpPath, $destAbs)) {
                $this->jsonResponse(500, ["success" => false, "message" => "No se pudo guardar el comprobante. Intenta nuevamente."]);
                return;
            }

            // Ruta relativa para BD
            $data['comprobante_domicilio'] = $uploadRelDir . '/' . $safeName;

            // 5) Registrar en BD
            $userModel = new User();
            $ok = $userModel->registrar($data);

            if ($ok) {
                $this->jsonResponse(200, ["success" => true, "message" => "Usuario registrado con éxito"]);
            } else {
                $this->jsonResponse(500, ["success" => false, "message" => "No se pudo registrar el usuario"]);
            }

        } catch (Exception $e) {
            $this->jsonResponse(500, ["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }
}
