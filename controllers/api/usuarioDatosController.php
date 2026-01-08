<?php
// controllers/api/usuarioDatosController.php
// EV — API: Datos de usuario + Solicitud cambio de residencia (Opción A: sin departamento)

declare(strict_types=1);

require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/UsuarioResidenciaSolicitud.php';
require_once __DIR__ . '/../../models/CondominioModel.php';
require_once __DIR__ . '/../../models/Urbanizacion.php';

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

            // Importante: este endpoint NO debe aplicar cambios de residencia si tú quieres que
            // cambios de residencia vayan por solicitud.
            // Si tu Usuario::actualizarDatos hoy actualiza usuario_residencia, entonces en tu JS
            // debes asegurarte de que cuando hay cambio de residencia NO se llame a /actualizar
            // sino a /solicitar-cambio-residencia (como ya lo estás planteando).
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

    /**
     * POST /api/usuario/solicitar-cambio-residencia
     * FormData:
     * - tipo_conjunto: condominio|urbanizacion
     * - codigo_condominio (si condominio)
     * - codigo_urbanizacion (si urbanizacion)
     * - ubigeo_departamento, ubigeo_provincia, ubigeo_distrito (obligatorios por tu requerimiento actual)
     * - documento_domicilio (file: pdf|jpg|jpeg|png <= 5MB)
     *
     * Dirección:
     * - Se toma desde BD (condominio/urbanizacion) y se guarda en solicitud.
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

            // Ubigeo obligatorio (según tu requerimiento)
            if ($ubDep === '' || $ubProv === '' || $ubDist === '') {
                $this->json(422, ['ok' => false, 'mensaje' => 'Ubigeo requerido.']);
                return;
            }

            if ($tipo === 'condominio') {
                if ($codCon <= 0) {
                    $this->json(422, ['ok' => false, 'mensaje' => 'Condominio obligatorio.']);
                    return;
                }
            } else {
                if ($codUrb <= 0) {
                    $this->json(422, ['ok' => false, 'mensaje' => 'Urbanización obligatoria.']);
                    return;
                }
            }

            // Dirección real desde BD
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

            // Guardado físico (ESTÁNDAR FINAL)
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

            // Ruta relativa a guardar en BD (ESTÁNDAR FINAL)
            $rutaRelativa = 'resources/uploads/comprobantes/' . $safeFile;

            // Registrar solicitud (BD) - sin departamento
            $model = new UsuarioResidenciaSolicitud();

            $idSolicitud = $model->crear($codigoUsuario, [
                'tipo_conjunto'        => $tipo,
                'codigo_condominio'    => ($tipo === 'condominio')   ? $codCon : null,
                'codigo_urbanizacion'  => ($tipo === 'urbanizacion') ? $codUrb : null,
                'direccion'            => $direccion, // <-- desde BD
                // ubigeo_* (no persiste aquí salvo que lo agregues a la tabla)
            ], $rutaRelativa);

            $this->json(200, [
                'ok' => $idSolicitud > 0,
                'id' => $idSolicitud,
                'mensaje' => ($idSolicitud > 0)
                    ? 'Solicitud registrada. Queda en revisión.'
                    : 'No se pudo registrar la solicitud.',
            ]);
        } catch (Throwable $e) {
            $this->json(500, [
                'ok' => false,
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

            // Requiere que existan estos métodos en Usuario.php
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
