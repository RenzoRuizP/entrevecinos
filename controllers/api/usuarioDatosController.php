<?php
// controllers/api/usuarioDatosController.php
// EV — API: Datos de usuario + Solicitud cambio de residencia + Guardado por secciones

declare(strict_types=1);

require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';
require_once __DIR__ . '/../../models/CondominioModel.php';
require_once __DIR__ . '/../../models/Urbanizacion.php';

// ✅ Reutilizamos el updater ya existente (no tocamos Usuario.php)
require_once __DIR__ . '/../../models/UsuarioSoporte.php';

class usuarioDatosController
{
    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    private function authUserId(): int
    {
        $auth = $GLOBALS['EV_AUTH'] ?? [];
        return (int)($auth['codigo_usuario'] ?? 0);
    }

    private function normalizeTipo(string $tipo): string
    {
        return strtolower(trim($tipo));
    }

    /**
     * Dirección: NO confiar en POST cuando el input es disabled.
     * Regla: se toma desde BD según tipo_conjunto.
     */
    private function obtenerDireccionDesdeBD(string $tipo, int $codCon, int $codUrb): string
    {
        if ($tipo === 'condominio') {
            $m = new CondominioModel();
            return trim((string)$m->obtenerDireccionPorId($codCon));
        }
        $m = new Urbanizacion();
        return trim((string)$m->obtenerDireccionPorId($codUrb));
    }

    public function obtenerDatos(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['success' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->obtenerPorCodigo($codigoUsuario);

            if (!$usuario) {
                $this->json(404, ['success' => false, 'error' => 'USUARIO_NO_ENCONTRADO']);
                return;
            }

            $this->json(200, ['success' => true, 'usuario' => $usuario]);
        } catch (Throwable $e) {
            $this->json(500, [
                'success' => false,
                'error'   => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    /**
     * LEGACY: /api/usuario/actualizar
     * Mantener para compatibilidad.
     */
    public function actualizarDatos(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['success' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput ?: '[]', true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $this->json(400, [
                    'success' => false,
                    'error'   => 'JSON_INVALIDO',
                    'detalle' => json_last_error_msg(),
                ]);
                return;
            }

            $email  = trim((string)($data['email'] ?? ''));
            $nombre = trim((string)($data['nombre_completo'] ?? ''));

            if ($email === '' || $nombre === '') {
                $this->json(400, ['success' => false, 'error' => 'DATOS_INCOMPLETOS']);
                return;
            }

            $usuarioModel = new Usuario();
            $usuarioModel->actualizarDatos($codigoUsuario, $data);

            $this->json(200, [
                'success' => true,
                'message' => 'Datos actualizados correctamente',
            ]);
        } catch (Exception $e) {
            $this->json(400, [
                'success' => false,
                'error'   => 'VALIDACION',
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'success' => false,
                'error'   => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    // =========================================================
    // ✅ Guardado por secciones
    // =========================================================

    public function actualizarTelefono(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['ok' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput ?: '[]', true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $this->json(400, [
                    'ok'      => false,
                    'error'   => 'JSON_INVALIDO',
                    'detalle' => json_last_error_msg(),
                ]);
                return;
            }

            $telefono = trim((string)($data['telefono'] ?? ''));
            if ($telefono === '') {
                $this->json(422, ['ok' => false, 'error' => 'TELEFONO_REQUERIDO', 'mensaje' => 'Ingresa tu teléfono.']);
                return;
            }

            $usuarioModel = new Usuario();
            $ok = $usuarioModel->actualizarTelefono($codigoUsuario, $telefono);

            if (!$ok) {
                $this->json(422, ['ok' => false, 'error' => 'TELEFONO_INVALIDO', 'mensaje' => 'Formato esperado: 9XXXXXXXX.']);
                return;
            }

            $this->json(200, ['ok' => true, 'mensaje' => 'Teléfono actualizado correctamente.']);
        } catch (Throwable $e) {
            $this->json(500, [
                'ok'      => false,
                'error'   => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public function actualizarResidencia(): void
    {
        $this->solicitarCambioResidencia();
    }

    /**
     * POST /api/usuario/solicitar-cambio-residencia
     * FormData:
     * - tipo_conjunto: condominio|urbanizacion
     * - codigo_condominio (si condominio)
     * - codigo_urbanizacion (si urbanizacion)
     * - ubigeo_departamento, ubigeo_provincia, ubigeo_distrito
     * - documento_domicilio (file: pdf|jpg|jpeg|png <= 5MB)
     */
    public function solicitarCambioResidencia(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['ok' => false, 'mensaje' => 'No autenticado.']);
                return;
            }

            $tipo = $this->normalizeTipo((string)($_POST['tipo_conjunto'] ?? ''));

            $codCon = (int)($_POST['codigo_condominio'] ?? 0);
            $codUrb = (int)($_POST['codigo_urbanizacion'] ?? 0);

            $ubDep  = trim((string)($_POST['ubigeo_departamento'] ?? ''));
            $ubProv = trim((string)($_POST['ubigeo_provincia'] ?? ''));
            $ubDist = trim((string)($_POST['ubigeo_distrito'] ?? ''));

            if (!in_array($tipo, ['condominio', 'urbanizacion'], true)) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Tipo de conjunto inválido.']);
                return;
            }

            if ($ubDep === '' || $ubProv === '' || $ubDist === '') {
                $this->json(422, ['ok' => false, 'mensaje' => 'Ubigeo requerido.']);
                return;
            }

            if ($tipo === 'condominio' && $codCon <= 0) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Condominio obligatorio.']);
                return;
            }
            if ($tipo === 'urbanizacion' && $codUrb <= 0) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Urbanización obligatoria.']);
                return;
            }

            $direccion = $this->obtenerDireccionDesdeBD($tipo, $codCon, $codUrb);
            if ($direccion === '') {
                $this->json(422, ['ok' => false, 'mensaje' => 'No se pudo obtener la dirección desde BD.']);
                return;
            }

            // Archivo
            if (!isset($_FILES['documento_domicilio']) || !is_array($_FILES['documento_domicilio'])) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Adjunta un comprobante de domicilio.']);
                return;
            }

            $f = $_FILES['documento_domicilio'];

            if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $this->json(422, ['ok' => false, 'mensaje' => 'No se pudo cargar el archivo.']);
                return;
            }

            $max = 5 * 1024 * 1024;
            if ((int)($f['size'] ?? 0) > $max) {
                $this->json(422, ['ok' => false, 'mensaje' => 'El archivo supera 5MB.']);
                return;
            }

            $name = (string)($f['name'] ?? '');
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                $this->json(422, ['ok' => false, 'mensaje' => 'Tipo de archivo no permitido.']);
                return;
            }

            $uploadsDir = __DIR__ . '/../../resources/uploads/comprobantes';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0775, true);
            }

            $safeFile = 'comp_res_' . $codigoUsuario . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $uploadsDir . '/' . $safeFile;

            if (!move_uploaded_file((string)($f['tmp_name'] ?? ''), $destPath)) {
                $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo guardar el archivo.']);
                return;
            }

            $rutaRelativa = 'resources/uploads/comprobantes/' . $safeFile;

            // ✅ Registrar solicitud (upsert)
            $model = new UsuarioResidenciaSolicitud();
            $idSolicitud = $model->upsertPendiente($codigoUsuario, [
                'tipo_conjunto'        => $tipo,
                'codigo_condominio'    => ($tipo === 'condominio') ? $codCon : null,
                'codigo_urbanizacion'  => ($tipo === 'urbanizacion') ? $codUrb : null,
                'direccion'            => $direccion,
            ], $rutaRelativa);

            if ($idSolicitud <= 0) {
                $this->json(500, ['ok' => false, 'mensaje' => 'No se pudo registrar la solicitud.']);
                return;
            }

            // ✅ Regla: al solicitar cambio, cuenta pasa a "En revisión" (estado=1)
            // (Así aparece en Modo Usuarios / En revisión)
            try {
                $uSoporte = new UsuarioSoporte();
                $uSoporte->actualizarEstado($codigoUsuario, 1);
            } catch (Throwable $e) {
                // No bloquear la solicitud si el update del estado falla
            }

            $this->json(200, [
                'ok'      => true,
                'id'      => $idSolicitud,
                'mensaje' => 'Solicitud registrada. Queda en revisión.',
            ]);

        } catch (Throwable $e) {
            $this->json(500, [
                'ok'      => false,
                'mensaje' => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public function cambiarClave(): void
    {
        try {
            $codigoUsuario = $this->authUserId();
            if ($codigoUsuario <= 0) {
                $this->json(401, ['success' => false, 'error' => 'UNAUTHORIZED']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput ?: '[]', true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $this->json(400, [
                    'success' => false,
                    'error'   => 'JSON_INVALIDO',
                    'detalle' => json_last_error_msg(),
                ]);
                return;
            }

            $actual = trim((string)($data['password_actual'] ?? ''));
            $nueva  = trim((string)($data['password_nueva'] ?? ''));
            $conf   = trim((string)($data['password_confirmar'] ?? ''));

            if ($actual === '' || $nueva === '' || $conf === '') {
                $this->json(422, [
                    'success' => false,
                    'error'   => 'DATOS_INCOMPLETOS',
                    'message' => 'Completa los 3 campos.'
                ]);
                return;
            }

            if ($nueva !== $conf) {
                $this->json(422, [
                    'success' => false,
                    'error'   => 'NO_COINCIDE',
                    'message' => 'La nueva contraseña y la confirmación no coinciden.'
                ]);
                return;
            }

            if (strlen($nueva) < 8) {
                $this->json(422, [
                    'success' => false,
                    'error'   => 'CLAVE_DEBIL',
                    'message' => 'La contraseña debe tener mínimo 8 caracteres.'
                ]);
                return;
            }

            if ($nueva === $actual) {
                $this->json(422, [
                    'success' => false,
                    'error'   => 'CLAVE_IGUAL',
                    'message' => 'La nueva contraseña debe ser distinta a la actual.'
                ]);
                return;
            }

            $usuarioModel = new Usuario();

            $hash = $usuarioModel->obtenerHashClave($codigoUsuario);
            if (!$hash || !password_verify($actual, (string)$hash)) {
                $this->json(403, [
                    'success' => false,
                    'error'   => 'CLAVE_ACTUAL_INVALIDA',
                    'message' => 'La contraseña actual no es correcta.'
                ]);
                return;
            }

            $hashNueva = password_hash($nueva, PASSWORD_BCRYPT);

            $ok = $usuarioModel->actualizarClave($codigoUsuario, $hashNueva);
            if (!$ok) {
                $this->json(500, [
                    'success' => false,
                    'error'   => 'NO_SE_PUDO_ACTUALIZAR',
                    'message' => 'No se pudo actualizar la contraseña.'
                ]);
                return;
            }

            $this->json(200, [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente.'
            ]);

        } catch (Throwable $e) {
            $this->json(500, [
                'success' => false,
                'error'   => 'ERROR_SERVIDOR',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}
